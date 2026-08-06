<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Cid;

class CidController extends Controller
{
    /**
     * Search CIDs.
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        if (strlen($query) < 2) return response()->json([]);

        $cids = Cid::where('codigo', 'like', "%{$query}%")
                   ->orWhere('descricao', 'like', "%{$query}%")
                   ->limit(50)
                   ->get();

        $results = [];
        foreach ($cids as $cid) {
            $label = str_starts_with($cid->descricao, $cid->codigo) 
                ? $cid->descricao 
                : $cid->codigo . ' - ' . $cid->descricao;
            $results[] = [
                'value' => $cid->id,
                'label' => $label
            ];
        }

        return response()->json($results);
    }

    /**
     * Lista CIDs para a TableGrid (DataTables-like).
     */
    public function list(Request $request)
    {
        $query = Cid::query();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('codigo', 'like', "%{$searchTerm}%")
                  ->orWhere('descricao', 'like', "%{$searchTerm}%");
            });
        }

        $sort = $request->input('sort', 'codigo');
        $dir = $request->input('dir', 'asc');
        $allowedSorts = ['codigo', 'descricao'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $dir === 'desc' ? 'desc' : 'asc');
        }

        $perPage = $request->input('per_page', 10);
        $paginator = $query->paginate($perPage);

        return response()->json($paginator);
    }

    /**
     * Baixar template de importação de CID
     */
    public function template()
    {
        $content = "codigo;descricao\nA000;Cólera devida a Vibrio cholerae 01, biótipo cholerae\nA001;Cólera devida a Vibrio cholerae 01, biótipo El Tor";
        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="modelo_cids.csv"',
        ]);
    }

    /**
     * Importação streaming de CIDs
     */
    public function importCidsProgress(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file ? $file->getRealPath() : null;
        if (!$path || !is_file($path)) {
            throw ValidationException::withMessages(['file' => ['Arquivo inválido.']]);
        }

        $userId = (int)auth()->id();
        $importId = (string)Str::uuid();
        
        // Mantemos o mesmo padrão de emissão do TUSS (NDJSON) com os 4096 espaços para flush no PHP/Nginx
        return response()->stream(function () use ($path, $importId, $userId) {
                set_time_limit(0); // Evitar timeout em arquivos grandes
                $emit = function (int $percent, string $status, string $message = '') use ($importId) {
                    $payload = [
                        'id' => $importId,
                        'percent' => max(0, min(100, $percent)),
                        'status' => $status,
                        'message' => mb_convert_encoding($message, 'UTF-8', 'UTF-8, ISO-8859-1'),
                    ];
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    if ($json === false) {
                        $payload['message'] = 'Erro de codificação na mensagem.';
                        $json = json_encode($payload);
                    }
                    echo $json . str_repeat(' ', 4096) . "\n";
                    if (ob_get_level() > 0) @ob_flush();
                    @flush();
                };

            $emit(5, 'running', 'Lendo arquivo');

            $fh = fopen($path, 'rb');
            if (!$fh) {
                $emit(100, 'error', 'Não foi possível ler o arquivo.');
                return;
            }

            $transactionStarted = false;

            try {
                // Descobrir total de linhas para progresso (apenas estimativa de bytes ou contando)
                fseek($fh, 0, SEEK_END);
                $totalBytes = ftell($fh) ?: 1;
                fseek($fh, 0, SEEK_SET);

                // Lê a primeira linha para identificar o delimitador e cabeçalhos
                $firstLine = fgets($fh);
                if ($firstLine === false) {
                    throw ValidationException::withMessages(['file' => ['Arquivo vazio.']]);
                }
                
                $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
                fseek($fh, 0, SEEK_SET);

                $headers = fgetcsv($fh, 4096, $delimiter);
                if (!$headers) {
                    throw ValidationException::withMessages(['file' => ['Arquivo vazio ou formato inválido.']]);
                }

                $headers = array_map(fn($h) => strtolower(trim($h)), $headers);
                
                $idxCodigo = array_search('codigo', $headers);
                $idxDescricao = array_search('descricao', $headers);
                
                if ($idxCodigo === false || $idxDescricao === false) {
                    throw ValidationException::withMessages(['file' => ['Colunas não encontradas. O arquivo deve ter as colunas "codigo" e "descricao".']]);
                }

                $emit(15, 'running', 'Limpando base de CIDs (Truncate)');
                DB::table('cids')->truncate();

                $emit(20, 'running', 'Processando linhas');

                $chunkSize = 1000;
                $rowsToInsert = [];
                $validRows = 0;
                $processedBytes = 0;
                $lastPercent = 20;

                DB::beginTransaction();
                $transactionStarted = true;
                $now = now();

                $flush = function () use (&$rowsToInsert) {
                    if (empty($rowsToInsert)) return;
                    // Usar insertOrIgnore para ignorar linhas duplicadas no arquivo
                    DB::table('cids')->insertOrIgnore($rowsToInsert);
                    $rowsToInsert = [];
                };

                while (($data = fgetcsv($fh, 4096, $delimiter)) !== false) {
                    $processedBytes += (strlen(implode($delimiter, $data)) + 1); // aprox

                    $codigo = trim((string)($data[$idxCodigo] ?? ''));
                    $descricao = trim((string)($data[$idxDescricao] ?? ''));

                    // Forçar conversão para UTF-8 (útil para CSVs vindos do Excel/Windows)
                    $codigo = mb_convert_encoding($codigo, 'UTF-8', 'UTF-8, ISO-8859-1');
                    $descricao = mb_convert_encoding($descricao, 'UTF-8', 'UTF-8, ISO-8859-1');

                    if ($codigo === '' || $descricao === '') {
                        continue;
                    }

                    $rowsToInsert[] = [
                        'codigo' => substr($codigo, 0, 10),
                        'descricao' => $descricao,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $validRows++;

                    if (count($rowsToInsert) >= $chunkSize) {
                        $flush();
                    }

                    $percent = 20 + (int)floor(($processedBytes / $totalBytes) * 70);
                    $percent = max(20, min(90, $percent));
                    if ($percent > $lastPercent) {
                        $lastPercent = $percent;
                        $emit($percent, 'running', 'Inserindo registros...');
                    }
                }

                $flush();

                if ($validRows === 0) {
                    throw ValidationException::withMessages(['file' => ['Nenhuma linha válida encontrada para importar.']]);
                }

                $emit(95, 'running', 'Finalizando');
                DB::commit();
                $transactionStarted = false;

                $emit(100, 'success', 'Importação concluída com sucesso! ' . $validRows . ' CIDs inseridos.');
            } catch (ValidationException $e) {
                $msg = (string)($e->errors()['file'][0] ?? 'Falha ao validar o arquivo.');
                $emit(100, 'error', $msg);
                if ($transactionStarted) DB::rollBack();
            } catch (\Throwable $e) {
                $emit(100, 'error', 'Falha ao importar: ' . $e->getMessage());
                if ($transactionStarted) DB::rollBack();
            } finally {
                if (is_resource($fh ?? null)) fclose($fh);
            }
        }, 200, [
            'Content-Type' => 'application/x-ndjson; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
