<?php

namespace App\Http\Controllers;

use App\Models\Procedimento;
use App\Jobs\ImportTussCsvJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class ProcedimentoController extends Controller
{
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

    private function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias_procedimento,id'],
            'eh_tratamento' => ['nullable', 'boolean'],
            'quantidade_sessoes' => ['nullable', 'integer', 'min:1'],
            'valor' => ['nullable', 'numeric'],
            'comissao_percentual' => ['nullable', 'numeric'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }

    private function normalizePayload(array $data): array
    {
        $data['eh_tratamento'] = isset($data['eh_tratamento']) ? (bool)$data['eh_tratamento'] : false;
        $data['ativo'] = isset($data['ativo']) ? (bool)$data['ativo'] : true;
        return $data;
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

    private function detectCsvDelimiter(string $line): string
    {
        $c = substr_count($line, ',');
        $s = substr_count($line, ';');
        $t = substr_count($line, "\t");
        if ($s >= $c && $s >= $t) return ';';
        if ($t >= $c && $t >= $s) return "\t";
        return ',';
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

    public function downloadTussTemplate()
    {
        $cols = ['codigo', 'descricao', 'm2_filme', 'auxiliares', 'incidencia', 'porte', 'ch', 'co'];
        $csv = implode(';', $cols) . "\n";
        $sample = ['000000', 'Procedimento Exemplo', '0', '0', '0', 'A', '100', '1'];
        $csv .= implode(';', $sample) . "\n";

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'modelo-tuss.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function startTussImport(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:20480'],
            'tabela_forcada' => ['required', 'string', 'max:20'],
        ], [
            'file.required' => 'Selecione um arquivo CSV.',
            'file.mimes' => 'Envie um arquivo CSV.',
            'tabela_forcada.required' => 'Selecione a tabela suportada.',
        ]);

        $userId = (int)auth()->id();
        if ($userId <= 0) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $importId = (string)Str::uuid();
        $file = $request->file('file');
        $storedPath = $file->storeAs('tuss-imports', 'tuss-' . $importId . '.csv', 'local');

        Cache::put('tuss_import:' . $importId, [
            'user_id' => $userId,
            'status' => 'queued',
            'percent' => 0,
            'message' => 'Aguardando processamento',
        ], now()->addHours(6));

        $job = new ImportTussCsvJob($importId, $userId, (string)$request->input('tabela_forcada'), $storedPath);
        app()->terminating(function () use ($job) {
            $job->handle();
        });

        return response()->json(['id' => $importId]);
    }

    public function tussImportStatus(string $id)
    {
        $userId = (int)auth()->id();
        if ($userId <= 0) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $payload = Cache::get('tuss_import:' . $id);
        if (!$payload || !is_array($payload)) {
            return response()->json(['message' => 'Importação não encontrada.'], 404);
        }
        if ((int)($payload['user_id'] ?? 0) !== $userId) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        return response()->json([
            'status' => (string)($payload['status'] ?? 'unknown'),
            'percent' => (int)($payload['percent'] ?? 0),
            'message' => (string)($payload['message'] ?? ''),
        ]);
    }

    public function completeTussImport(Request $request, string $id)
    {
        $userId = (int)auth()->id();
        $payload = Cache::get('tuss_import:' . $id);
        $wantsJson = $request->expectsJson() || $request->wantsJson();
        if (!$payload || !is_array($payload)) {
            if ($wantsJson) {
                return response()->json(['status' => 'error', 'message' => 'Importação não encontrada.'], 404);
            }
            return redirect()->route('configuracao.index')->with('error', 'Importação não encontrada.');
        }
        if ((int)($payload['user_id'] ?? 0) !== $userId) {
            if ($wantsJson) {
                return response()->json(['status' => 'error', 'message' => 'Acesso negado.'], 403);
            }
            return redirect()->route('configuracao.index')->with('error', 'Acesso negado.');
        }

        $status = (string)($payload['status'] ?? 'unknown');
        $message = trim((string)($payload['message'] ?? ''));
        Cache::forget('tuss_import:' . $id);

        if ($status === 'success') {
            $msg = $message !== '' ? $message : 'Importação TUSS concluída.';
            if ($wantsJson) {
                return response()->json(['status' => 'success', 'message' => $msg]);
            }
            return redirect()->route('configuracao.index')->with('success', $msg);
        }
        if ($status === 'error') {
            $msg = $message !== '' ? $message : 'Falha ao importar.';
            if ($wantsJson) {
                return response()->json(['status' => 'error', 'message' => $msg]);
            }
            return redirect()->route('configuracao.index')->with('error', $msg);
        }
        if ($wantsJson) {
            return response()->json(['status' => 'warning', 'message' => 'Importação ainda em andamento.']);
        }
        return redirect()->route('configuracao.index')->with('warning', 'Importação ainda em andamento.');
    }

    public function importTussProgress(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:20480'],
            'tabela_forcada' => ['required', 'string', 'max:20'],
        ], [
            'file.required' => 'Selecione um arquivo CSV.',
            'file.mimes' => 'Envie um arquivo CSV.',
            'tabela_forcada.required' => 'Selecione a tabela suportada.',
        ]);

        $allowedTabelas = ['AMB1990', 'AMB1992', 'AMB1993', 'AMB1999', 'CBHPM3', 'CBHPM4', 'CBHPM5', 'TUSS'];
        $forcedTabela = trim((string)$request->input('tabela_forcada', ''));
        if (!in_array($forcedTabela, $allowedTabelas, true)) {
            throw ValidationException::withMessages(['tabela_forcada' => ['Tabela inválida.']]);
        }

        $file = $request->file('file');
        $path = $file ? $file->getRealPath() : null;
        if (!$path || !is_file($path)) {
            throw ValidationException::withMessages(['file' => ['Arquivo inválido.']]);
        }

        $userId = (int)auth()->id();
        $importId = (string)Str::uuid();
        Cache::put('tuss_import:' . $importId, [
            'user_id' => $userId,
            'status' => 'running',
            'percent' => 0,
            'message' => '',
        ], now()->addHours(2));

        return response()->stream(function () use ($path, $forcedTabela, $allowedTabelas, $importId, $userId) {
            $emit = function (int $percent, string $status, string $message = '') use ($importId) {
                $payload = [
                    'id' => $importId,
                    'percent' => max(0, min(100, $percent)),
                    'status' => $status,
                    'message' => $message,
                ];
                echo json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n";
                if (function_exists('ob_flush')) @ob_flush();
                if (function_exists('flush')) @flush();
            };

            $emit(5, 'running', 'Lendo arquivo');

            $fh = fopen($path, 'rb');
            if (!$fh) {
                $emit(100, 'error', 'Não foi possível ler o arquivo.');
                return;
            }

            $transactionStarted = false;
            try {
                $totalLines = 0;
                while (fgets($fh) !== false) {
                    $totalLines += 1;
                }
                rewind($fh);
                $totalDataLines = max(1, $totalLines - 1);

                $emit(10, 'running', 'Verificando colunas');

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
                $allowedHeaders = ['tabela', 'codigo', 'cod_tuss', 'descricao', 'm2_filme', 'auxiliares', 'incidencia', 'porte', 'ch', 'co', 'total'];
                if ($hasProcRef) $allowedHeaders[] = 'proc_ref';
                $headerKeys = array_values(array_filter(array_keys($headerMap), fn ($k) => $k !== ''));
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

                $emit(20, 'running', 'Colunas validadas');
                $emit(20, 'running', 'Validando registros');

                DB::beginTransaction();
                $transactionStarted = true;

                $rowsToInsert = [];
                $seenKeys = [];
                $chunkKeys = [];
                $chunkSize = 500;
                $processed = 0;
                $validRows = 0;
                $skippedEmpty = 0;

                $lastPercent = -1;
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

                $validateDecimal = function ($raw, string $field, int $line) {
                    $s = trim((string)($raw ?? ''));
                    if ($s === '') return null;
                    $n = $this->parseDecimal($s);
                    if ($n === null) {
                        throw ValidationException::withMessages(['file' => ["Linha {$line}: {$field} inválido."]]);
                    }
                    return $n;
                };

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
                    if (!in_array($forcedTabela, $allowedTabelas, true)) {
                        throw ValidationException::withMessages(['file' => ["Linha {$processed}: tabela inválida ({$forcedTabela})."]]);
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
                    $m2Filme = $validateDecimal($get($row, 'm2_filme'), 'm2_filme', $processed);
                    $aux = $validateDecimal($get($row, 'auxiliares'), 'auxiliares', $processed);
                    $inc = $validateDecimal($get($row, 'incidencia'), 'incidencia', $processed);
                    $porte = trim((string)($this->ensureUtf8((string)($get($row, 'porte') ?? '')) ?? ''));
                    $ch = $validateDecimal($get($row, 'ch'), 'ch', $processed);
                    $co = $validateDecimal($get($row, 'co'), 'co', $processed);
                    $total = $validateDecimal($get($row, 'total'), 'total', $processed);
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
                        'ch' => $ch,
                        'co' => $co,
                        'total' => $total,
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

                    $percent = 20 + (int)floor(($processed / $totalDataLines) * 70);
                    $percent = max(20, min(90, $percent));
                    if ($percent !== $lastPercent) {
                        $lastPercent = $percent;
                        $emit($percent, 'running', 'Validando registros');
                    }
                }

                fclose($fh);
                $flush();

                if ($validRows === 0) {
                    throw ValidationException::withMessages(['file' => ['CSV inválido ou vazio. Nenhuma linha válida para importar.']]);
                }

                $emit(95, 'running', 'Finalizando');
                DB::commit();
                $transactionStarted = false;

                Cache::put('tuss_import:' . $importId, [
                    'user_id' => $userId,
                    'status' => 'success',
                    'percent' => 100,
                    'message' => 'Importação TUSS concluída.',
                ], now()->addHours(2));
                $emit(100, 'success', 'Importação concluída');
            } catch (ValidationException $e) {
                $msg = (string)($e->errors()['file'][0] ?? 'Falha ao validar o arquivo.');
                Cache::put('tuss_import:' . $importId, [
                    'user_id' => $userId,
                    'status' => 'error',
                    'percent' => 100,
                    'message' => $msg,
                ], now()->addHours(2));
                $emit(100, 'error', $msg);
                if ($transactionStarted) DB::rollBack();
            } catch (\Throwable $e) {
                Cache::put('tuss_import:' . $importId, [
                    'user_id' => $userId,
                    'status' => 'error',
                    'percent' => 100,
                    'message' => 'Falha ao importar.',
                ], now()->addHours(2));
                $emit(100, 'error', 'Falha ao importar.');
                if ($transactionStarted) DB::rollBack();
                return;
            } finally {
                if (is_resource($fh ?? null)) fclose($fh);
            }
        }, 200, [
            'Content-Type' => 'application/x-ndjson; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function importTuss(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:20480'],
            'dry_run' => ['nullable', 'boolean'],
            'tabela_forcada' => ['required', 'string', 'max:20'],
        ], [
            'file.required' => 'Selecione um arquivo CSV.',
            'file.mimes' => 'Envie um arquivo CSV.',
            'tabela_forcada.required' => 'Selecione a tabela suportada.',
        ]);

        $allowedTabelas = ['AMB1990', 'AMB1992', 'AMB1993', 'AMB1999', 'CBHPM3', 'CBHPM4', 'CBHPM5', 'TUSS'];
        $dryRun = $request->boolean('dry_run');
        $forcedTabela = trim((string)$request->input('tabela_forcada', ''));
        if ($forcedTabela === '') {
            throw ValidationException::withMessages(['tabela_forcada' => ['Selecione a tabela suportada.']]);
        }
        if (!in_array($forcedTabela, $allowedTabelas, true)) {
            throw ValidationException::withMessages(['tabela_forcada' => ['Tabela inválida.']]);
        }

        $file = $request->file('file');
        $path = $file ? $file->getRealPath() : null;
        if (!$path || !is_file($path)) {
            throw ValidationException::withMessages(['file' => ['Arquivo inválido.']]);
        }

        $fh = fopen($path, 'rb');
        if (!$fh) {
            throw ValidationException::withMessages(['file' => ['Não foi possível ler o arquivo.']]);
        }

        $firstLine = (string)fgets($fh);
        rewind($fh);
        $delimiter = $this->detectCsvDelimiter($firstLine);

        $headers = fgetcsv($fh, 0, $delimiter);
        if (!$headers || !is_array($headers)) {
            fclose($fh);
            throw ValidationException::withMessages(['file' => ['CSV sem cabeçalho.']]);
        }

        $headerMap = [];
        foreach ($headers as $idx => $h) {
            $headerMap[$this->normalizeHeader((string)($this->ensureUtf8((string)$h) ?? ''))] = $idx;
        }

        $hasProcRef = Schema::hasColumn('tuss', 'proc_ref');
        $allowedHeaders = ['tabela', 'codigo', 'cod_tuss', 'descricao', 'm2_filme', 'auxiliares', 'incidencia', 'porte', 'ch', 'co', 'total'];
        if ($hasProcRef) $allowedHeaders[] = 'proc_ref';
        $headerKeys = array_values(array_filter(array_keys($headerMap), fn ($k) => $k !== ''));
        $unknownHeaders = array_values(array_diff($headerKeys, $allowedHeaders));
        if ($unknownHeaders) {
            fclose($fh);
            throw ValidationException::withMessages(['file' => ['CSV inválido. Colunas não suportadas: ' . implode(', ', $unknownHeaders) . '.']]);
        }

        $codigoKey = null;
        if (array_key_exists('codigo', $headerMap)) $codigoKey = 'codigo';
        if (!$codigoKey && array_key_exists('cod_tuss', $headerMap)) $codigoKey = 'cod_tuss';
        if (!$codigoKey) {
            fclose($fh);
            throw ValidationException::withMessages(['file' => ['CSV inválido. Falta coluna: codigo']]);
        }
        $procRefKey = $hasProcRef ? (array_key_exists('proc_ref', $headerMap) ? 'proc_ref' : null) : null;

        $rowsToInsert = [];
        $seenKeys = [];
        $chunkKeys = [];
        $processed = 0;
        $validRows = 0;
        $skippedEmpty = 0;
        $chunkSize = 500;

        $get = function (array $row, string $key) use ($headerMap) {
            $i = $headerMap[$key] ?? null;
            if ($i === null) return null;
            return array_key_exists($i, $row) ? $row[$i] : null;
        };

        $flush = function () use (&$rowsToInsert, &$chunkKeys, $dryRun) {
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

            if (!$dryRun) {
                DB::table('tuss')->insert($rowsToInsert);
            }

            $rowsToInsert = [];
            $chunkKeys = [];
        };

        $transactionStarted = false;
        try {
            if (!$dryRun) {
                DB::beginTransaction();
                $transactionStarted = true;
            }

            while (($row = fgetcsv($fh, 0, $delimiter)) !== false) {
                $processed += 1;
                $tabela = $forcedTabela;
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
                if (!in_array($tabela, $allowedTabelas, true)) {
                    throw ValidationException::withMessages(['file' => ["Linha {$processed}: tabela inválida ({$tabela})."]]);
                }

                $key = $tabela . '§' . $codigo;
                if (array_key_exists($key, $seenKeys)) {
                    $first = $seenKeys[$key];
                    throw ValidationException::withMessages([
                        'file' => ['CSV inválido. Registro duplicado para tabela/código: ' . $tabela . '/' . $codigo . " (linhas {$first} e {$processed})."],
                    ]);
                }
                $seenKeys[$key] = $processed;
                $chunkKeys[$key] = $processed;

                $descricao = trim((string)($this->ensureUtf8((string)($get($row, 'descricao') ?? '')) ?? ''));
                $m2Filme = $this->parseDecimal($get($row, 'm2_filme'));
                $aux = $this->parseDecimal($get($row, 'auxiliares'));
                $inc = $this->parseDecimal($get($row, 'incidencia'));
                $porte = trim((string)($this->ensureUtf8((string)($get($row, 'porte') ?? '')) ?? ''));
                $ch = $this->parseDecimal($get($row, 'ch'));
                $co = $this->parseDecimal($get($row, 'co'));
                $total = $this->parseDecimal($get($row, 'total'));
                if ($ch !== null && $co !== null) $total = $ch + $co;

                $now = now();
                $payload = [
                    'tabela' => $tabela,
                    'codigo' => $codigo,
                    'descricao' => $descricao !== '' ? $descricao : null,
                    'm2_filme' => $m2Filme,
                    'auxiliares' => $aux,
                    'incidencia' => $inc,
                    'porte' => $porte !== '' ? $porte : null,
                    'ch' => $ch,
                    'co' => $co,
                    'total' => $total,
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
            }
            fclose($fh);
            $flush();

            if (!$dryRun && $validRows === 0) {
                throw ValidationException::withMessages(['file' => ['CSV inválido ou vazio. Nenhuma linha válida para importar.']]);
            }
            if ($transactionStarted) {
                DB::commit();
            }
        } catch (\Throwable $e) {
            fclose($fh);
            if ($transactionStarted) {
                DB::rollBack();
            }
            throw $e;
        }

        if ($validRows === 0) {
            if ($dryRun) {
                return back()->with('success', "Validação concluída. Linhas lidas: {$processed}. Válidas: 0. Vazias ignoradas: {$skippedEmpty}.");
            }
            throw ValidationException::withMessages(['file' => ['CSV inválido ou vazio. Nenhuma linha válida para importar.']]);
        }

        if ($dryRun) {
            return back()->with('success', "Validação concluída. Linhas lidas: {$processed}. Válidas: {$validRows}. Vazias ignoradas: {$skippedEmpty}.");
        }

        return back()->with('success', 'Importação TUSS concluída.');
    }

    public function storeTuss(Request $request)
    {
        $allowedTabelas = ['AMB1990', 'AMB1992', 'AMB1993', 'AMB1999', 'CBHPM3', 'CBHPM4', 'CBHPM5', 'TUSS'];
        $hasProcRef = Schema::hasColumn('tuss', 'proc_ref');

        $rules = [
            'tabela' => ['required', 'string', 'max:20'],
            'codigo' => ['required', 'string', 'max:30'],
            'descricao' => ['nullable', 'string', 'max:255'],
            'm2_filme' => ['nullable', 'string', 'max:50'],
            'auxiliares' => ['nullable', 'string', 'max:50'],
            'incidencia' => ['nullable', 'string', 'max:50'],
            'porte' => ['nullable', 'string', 'max:20'],
            'ch' => ['nullable', 'string', 'max:50'],
            'co' => ['nullable', 'string', 'max:50'],
        ];
        if ($hasProcRef) $rules['proc_ref'] = ['nullable', 'string', 'max:50'];

        $data = $request->validate($rules, [
            'tabela.required' => 'Informe a tabela.',
            'codigo.required' => 'Informe o código.',
        ]);

        $tabela = trim((string)($data['tabela'] ?? ''));
        $codigo = trim((string)($data['codigo'] ?? ''));
        if (!in_array($tabela, $allowedTabelas, true)) {
            throw ValidationException::withMessages(['tabela' => ['Tabela inválida.']]);
        }
        if ($tabela === '' || $codigo === '') {
            throw ValidationException::withMessages([
                'tabela' => $tabela === '' ? ['Informe a tabela.'] : [],
                'codigo' => $codigo === '' ? ['Informe o código.'] : [],
            ]);
        }

        $ch = $this->parseDecimal($data['ch'] ?? null);
        $co = $this->parseDecimal($data['co'] ?? null);
        $total = ($ch !== null && $co !== null) ? ($ch + $co) : null;

        $payload = [
            'tabela' => $tabela,
            'codigo' => $codigo,
            'descricao' => isset($data['descricao']) && trim((string)$data['descricao']) !== '' ? trim((string)$data['descricao']) : null,
            'm2_filme' => $this->parseDecimal($data['m2_filme'] ?? null),
            'auxiliares' => $this->parseDecimal($data['auxiliares'] ?? null),
            'incidencia' => $this->parseDecimal($data['incidencia'] ?? null),
            'porte' => isset($data['porte']) && trim((string)$data['porte']) !== '' ? trim((string)$data['porte']) : null,
            'ch' => $ch,
            'co' => $co,
            'total' => $total,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];
        if ($hasProcRef) {
            $payload['proc_ref'] = isset($data['proc_ref']) && trim((string)$data['proc_ref']) !== '' ? trim((string)$data['proc_ref']) : null;
        }

        $updateCols = ['descricao', 'm2_filme', 'auxiliares', 'incidencia', 'porte', 'ch', 'co', 'total', 'updated_at', 'deleted_at'];
        if ($hasProcRef) array_unshift($updateCols, 'proc_ref');

        DB::table('tuss')->upsert([$payload], ['tabela', 'codigo'], $updateCols);

        return back()->with('success', 'Registro TUSS salvo.');
    }

    public function listTuss(Request $request)
    {
        $data = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'offset' => ['nullable', 'integer', 'min:0'],
            'q' => ['nullable', 'string', 'max:100'],
            'tabela' => ['nullable', 'string', 'max:20'],
        ]);

        $limit = (int)($data['limit'] ?? 10);
        $offset = (int)($data['offset'] ?? 0);
        $q = trim((string)($data['q'] ?? ''));
        $tabela = trim((string)($data['tabela'] ?? ''));

        $query = DB::table('tuss')
            ->whereNull('deleted_at');

        if ($tabela !== '') {
            $query->where('tabela', $tabela);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('codigo', 'like', '%' . $q . '%')
                    ->orWhere('descricao', 'like', '%' . $q . '%')
                    ->orWhere('tabela', 'like', '%' . $q . '%');
            });
        }

        $total = (clone $query)->count();

        $rows = $query
            ->select('id', 'tabela', 'codigo', 'descricao', 'ch', 'co', 'total')
            ->orderBy('tabela')
            ->orderBy('codigo')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $rows,
            'total' => $total,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->normalizePayload($request->validate($this->rules()));
        Procedimento::create($data);
        return back()->with('success', 'Procedimento cadastrado');
    }

    public function update(Request $request, string $id)
    {
        $data = $this->normalizePayload($request->validate($this->rules()));
        $proc = Procedimento::findOrFail($id);
        $proc->update($data);
        return back()->with('success', 'Procedimento atualizado');
    }

    public function destroy(string $id)
    {
        $proc = Procedimento::findOrFail($id);
        $proc->delete();
        return back()->with('success', 'Procedimento excluído');
    }
}
