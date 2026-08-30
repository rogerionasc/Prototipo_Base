<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ImportTussCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private function ensureUtf8(?string $value): ?string
    {
        if ($value === null) return null;
        $s = (string)$value;
        if ($s === '') return '';
        if (mb_check_encoding($s, 'UTF-8')) return $s;
        $converted = @mb_convert_encoding($s, 'UTF-8', 'Windows-1252');
        if (is_string($converted) && mb_check_encoding($converted, 'UTF-8')) return $converted;
        $converted = @mb_convert_encoding($s, 'UTF-8', 'ISO-8859-1');
        if (is_string($converted) && mb_check_encoding($converted, 'UTF-8')) return $converted;
        return $s;
    }

    public string $importId;
    public int $userId;
    public string $forcedTabela;
    public string $storedPath;

    public function __construct(string $importId, int $userId, string $forcedTabela, string $storedPath)
    {
        $this->importId = $importId;
        $this->userId = $userId;
        $this->forcedTabela = $forcedTabela;
        $this->storedPath = $storedPath;
    }

    private function cacheKey(): string
    {
        return 'tuss_import:' . $this->importId;
    }

    private function updateProgress(int $percent, string $status = 'running', ?string $message = null): void
    {
        $percent = max(0, min(100, $percent));
        $payload = Cache::get($this->cacheKey(), []);
        $payload['user_id'] = $this->userId;
        $payload['status'] = $status;
        $payload['percent'] = $percent;
        if ($message !== null) $payload['message'] = $message;
        Cache::put($this->cacheKey(), $payload, now()->addHours(6));
    }

    private function normalizeHeader(string $h): string
    {
        $x = trim((string)$h);
        $x = preg_replace('/\s+/', '_', $x);
        $x = mb_strtolower($x);
        $x = str_replace(['ç', 'ã', 'á', 'à', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú'], ['c', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u'], $x);
        $x = preg_replace('/[^a-z0-9_]/', '', $x);
        return $x;
    }

    private function detectCsvDelimiter(string $line): string
    {
        $c = substr_count($line, ',');
        $s = substr_count($line, ';');
        $t = substr_count($line, "\t");
        if ($s >= $c && $s >= $t) return ';';
        if ($t >= $c && $t >= $s) return "\t";
        return ',';
    }

    private function parseDecimal(?string $value): ?float
    {
        $s = trim((string)($this->ensureUtf8($value) ?? ''));
        if ($s === '') return null;
        $s = preg_replace('/[^\d,.\-]/', '', $s);
        if ($s === '' || $s === '-' || $s === '.' || $s === ',') return null;
        if (str_contains($s, ',') && str_contains($s, '.')) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } elseif (str_contains($s, ',')) {
            $s = str_replace(',', '.', $s);
        }
        if (!is_numeric($s)) return null;
        return (float)$s;
    }

    public function handle(): void
    {
        $allowedTabelas = ['AMB1990', 'AMB1992', 'AMB1993', 'AMB1999', 'CBHPM3', 'CBHPM4', 'CBHPM5', 'TUSS', '18', '19', '20', '22'];
        $forcedTabela = trim((string)$this->forcedTabela);
        if ($forcedTabela === '' || !in_array($forcedTabela, $allowedTabelas, true)) {
            $this->updateProgress(100, 'error', 'Tabela inválida.');
            return;
        }

        if (!Storage::disk('local')->exists($this->storedPath)) {
            $this->updateProgress(100, 'error', 'Arquivo não encontrado para importação.');
            return;
        }

        $fullPath = Storage::disk('local')->path($this->storedPath);
        $size = null;
        try {
            $s = Storage::disk('local')->size($this->storedPath);
            $size = is_int($s) && $s > 0 ? $s : null;
        } catch (\Throwable $e) {
            $size = null;
        }

        $this->updateProgress(0, 'running', 'Iniciando validação');

        $fh = fopen($fullPath, 'rb');
        if (!$fh) {
            $this->updateProgress(100, 'error', 'Não foi possível ler o arquivo.');
            return;
        }

        $transactionStarted = false;
        try {
            $totalLines = 0;
            while (($line = fgets($fh)) !== false) {
                $totalLines += 1;
            }
            rewind($fh);
            $totalDataLines = max(1, $totalLines - 1);

            $firstLine = (string)fgets($fh);
            rewind($fh);
            $delimiter = $this->detectCsvDelimiter($firstLine);

            $headers = fgetcsv($fh, 0, $delimiter);
            if (!$headers || !is_array($headers)) {
                throw ValidationException::withMessages(['file' => ['CSV sem cabeçalho.']]);
            }

            $headerMap = [];
            foreach ($headers as $idx => $h) {
                $headerMap[$this->normalizeHeader((string)($this->ensureUtf8((string)$h) ?? ''))] = $idx;
            }

            $hasProcRef = Schema::hasColumn('tuss', 'proc_ref');
            $allowedHeaders = ['tabela', 'codigo', 'cod_tuss', 'descricao', 'm2_filme', 'auxiliares', 'incidencia', 'porte', 'quantidade_ch', 'quantidade_co'];
            if ($hasProcRef) $allowedHeaders[] = 'proc_ref';
            $headerKeys = array_values(array_filter(array_keys($headerMap), fn($k) => $k !== ''));
            $unknownHeaders = array_values(array_diff($headerKeys, $allowedHeaders));
            if ($unknownHeaders) {
                throw ValidationException::withMessages(['file' => ['CSV inválido. Colunas não suportadas: ' . implode(', ', $unknownHeaders) . '.']]);
            }

            $codigoKey = null;
            if (array_key_exists('codigo', $headerMap)) $codigoKey = 'codigo';
            if (!$codigoKey && array_key_exists('cod_tuss', $headerMap)) $codigoKey = 'cod_tuss';
            if (!$codigoKey) {
                throw ValidationException::withMessages(['file' => ['CSV inválido. Falta coluna: codigo']]);
            }
            $procRefKey = $hasProcRef ? (array_key_exists('proc_ref', $headerMap) ? 'proc_ref' : null) : null;

            $get = function (array $row, string $key) use ($headerMap) {
                $i = $headerMap[$key] ?? null;
                if ($i === null) return null;
                return array_key_exists($i, $row) ? $row[$i] : null;
            };

            DB::beginTransaction();
            $transactionStarted = true;

            $rowsToInsert = [];
            $seenKeys = [];
            $chunkKeys = [];
            $chunkSize = 500;
            $processed = 0;
            $validRows = 0;
            $skippedEmpty = 0;

            $flush = function () use (&$rowsToInsert, &$chunkKeys) {
                if (!$rowsToInsert) return;

                $keys = array_keys($chunkKeys);
                if ($keys) {
                    $existing = DB::table('tuss')
                        ->selectRaw("concat(tabela,'§',codigo) as k")
                        ->whereIn(DB::raw("concat(tabela,'§',codigo)"), $keys)
                        ->pluck('k')
                        ->all();

                    if (!empty($existing)) {
                        $items = [];
                        foreach (array_slice($existing, 0, 20) as $k) {
                            $line = $chunkKeys[$k] ?? null;
                            $pretty = str_replace('§', '/', (string)$k);
                            $items[] = $line ? "{$pretty} (linha {$line})" : $pretty;
                        }
                        $more = count($existing) > 20 ? (' (+' . (count($existing) - 20) . ' outros)') : '';
                        throw ValidationException::withMessages([
                            'file' => ['Registros já existentes para tabela/código: ' . implode(', ', $items) . $more . '.'],
                        ]);
                    }
                }

                DB::table('tuss')->insert($rowsToInsert);
                $rowsToInsert = [];
                $chunkKeys = [];
            };

            $lastPercent = -1;
            $lastUpdateAt = microtime(true);
            while (($row = fgetcsv($fh, 0, $delimiter)) !== false) {
                $processed += 1;

                $procRef = $procRefKey ? trim((string)($this->ensureUtf8((string)$get($row, $procRefKey)) ?? '')) : null;
                $codigo = trim((string)($this->ensureUtf8((string)$get($row, $codigoKey)) ?? ''));

                $allEmpty = ($procRefKey ? ($procRef === '') : true) && $codigo === '';
                if ($allEmpty) {
                    $skippedEmpty += 1;
                    continue;
                }

                if ($codigo === '' || ($procRefKey && $procRef === '')) {
                    $missing = ['codigo'];
                    if ($procRefKey) $missing[] = 'proc_ref';
                    throw ValidationException::withMessages(['file' => ['Linha ' . $processed . ': ' . implode(', ', $missing) . ' são obrigatórios.']]);
                }

                $key = $forcedTabela . '§' . $codigo;
                if (array_key_exists($key, $seenKeys)) {
                    $first = $seenKeys[$key];
                    throw ValidationException::withMessages([
                        'file' => ['CSV inválido. Registro duplicado para tabela/código: ' . $forcedTabela . '/' . $codigo . " (linhas {$first} e {$processed})."],
                    ]);
                }
                $seenKeys[$key] = $processed;
                $chunkKeys[$key] = $processed;

                $descricao = trim((string)($this->ensureUtf8((string)($get($row, 'descricao') ?? '')) ?? ''));
                $m2Filme = $this->parseDecimal($get($row, 'm2_filme'));
                $aux = $this->parseDecimal($get($row, 'auxiliares'));
                $inc = $this->parseDecimal($get($row, 'incidencia'));
                $porte = trim((string)($this->ensureUtf8((string)($get($row, 'porte') ?? '')) ?? ''));
                $ch = $this->parseDecimal($get($row, 'quantidade_ch'));
                $co = $this->parseDecimal($get($row, 'quantidade_co'));
                $total = $this->parseDecimal($get($row, 'total'));
                if ($ch !== null && $co !== null) $total = $ch + $co;

                $now = now();
                $payload = [
                    'tabela' => $forcedTabela,
                    'codigo' => $codigo,
                    'descricao' => $descricao !== '' ? $descricao : null,
                    'm2_filme' => $m2Filme,
                    'auxiliares' => $aux,
                    'incidencia' => $inc,
                    'porte' => $porte !== '' ? $porte : null,
                    'quantidade_ch' => $ch,
                    'quantidade_co' => $co,
                    
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ];
                if ($hasProcRef) $payload['proc_ref'] = ($procRef !== null && $procRef !== '') ? $procRef : null;
                $validRows += 1;
                $rowsToInsert[] = $payload;

                if (count($rowsToInsert) >= $chunkSize) {
                    $flush();
                }

                $percent = (int)floor(($processed / $totalDataLines) * 99);
                $percent = max(0, min(99, $percent));
                $nowTs = microtime(true);
                if ($percent !== $lastPercent && ($nowTs - $lastUpdateAt) >= 0.15) {
                    $lastPercent = $percent;
                    $lastUpdateAt = $nowTs;
                    $this->updateProgress($percent, 'running', 'Validando linhas');
                }
            }

            fclose($fh);
            $flush();

            if ($validRows === 0) {
                throw ValidationException::withMessages(['file' => ['CSV inválido ou vazio. Nenhuma linha válida para importar.']]);
            }

            $this->updateProgress(99, 'running', 'Finalizando');
            DB::commit();
            $transactionStarted = false;

            $this->updateProgress(100, 'success', 'Importação TUSS concluída.');
        } catch (ValidationException $e) {
            if (is_resource($fh)) fclose($fh);
            if ($transactionStarted) DB::rollBack();
            $msg = (string)($e->errors()['file'][0] ?? 'Falha ao validar o arquivo.');
            $this->updateProgress(100, 'error', $msg);
        } catch (\Throwable $e) {
            if (is_resource($fh)) fclose($fh);
            if ($transactionStarted) DB::rollBack();
            $this->updateProgress(100, 'error', 'Falha ao importar: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Erro importacao TUSS: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return;
        } finally {
            Storage::disk('local')->delete($this->storedPath);
        }
    }
}
