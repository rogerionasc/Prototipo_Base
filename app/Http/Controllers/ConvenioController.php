<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Convenio;
use App\Models\Conta;
use App\Models\Pessoa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ConvenioController extends Controller
{
    private function rules(): array
    {
        return [
            'descricao' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'tuss_tabela' => ['nullable', 'exclude_if:tuss_tabela,', 'string', 'max:20', 'exists:tuss,tabela'],
            'tuss_ids' => ['nullable', 'array'],
            'medicos' => ['nullable', 'array'],
            'medicos.*.id' => ['required', 'integer', 'exists:pessoas,id'],
            'medicos.*.tuss_ids' => ['nullable', 'array'],
            'medicos.*.tuss_ids.*' => ['integer', 'exists:tuss,id'],
            'tipo' => ['nullable', 'string', 'max:20'],
            'empresa_id' => ['nullable', 'integer', 'exists:contas,id'],
            'ans' => ['nullable', 'integer'],
            'dias_recebimento' => ['nullable', 'integer'],
            'dias_retorno' => ['nullable', 'integer'],
            'dias_para_faturar' => ['nullable', 'integer'],
            'config_spsadt' => ['nullable', 'array'],
        ];
    }

    public function index()
    {
        $convenios = Convenio::with(['medicos:id,nome,conselho_id,numero_conselho,uf_conselho', 'medicos.conselho', 'medicos.especialidades:id,nome', 'medicoTuss:id', 'medicoTussMapeados:id,origem_procedimento_id'])
            ->select('id', 'descricao', 'logo_path', 'tuss_tabela', 'tipo', 'empresa_id', 'ans', 'dias_recebimento', 'dias_retorno', 'dias_para_faturar', 'config_spsadt')
            ->get();
        $contas = Conta::select('id', 'nome')->orderBy('nome')->get();
        $profissionaisSaude = Pessoa::with(['especialidades:id,nome', 'conselho'])->select('id', 'nome', 'conselho_id', 'numero_conselho', 'uf_conselho')->orderBy('nome')->get();
        $tussTabelas = DB::table('tuss')
            ->whereNotNull('tabela')
            ->where('tabela', '<>', '')
            ->whereNull('deleted_at')
            ->select('tabela')
            ->distinct()
            ->orderBy('tabela')
            ->pluck('tabela')
            ->values()
            ->all();
        return Inertia::render('Convenio/Cadastros/Index', [
            'convenios' => $convenios,
            'contas' => $contas,
            'profissionaisSaude' => $profissionaisSaude,
            'tussTabelas' => $tussTabelas,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'tuss_tabela' => trim((string)$request->input('tuss_tabela', '')) !== '' ? trim((string)$request->input('tuss_tabela')) : null,
            'tuss_ids' => $request->input('tuss_ids', []),
            'medicos' => $request->input('medicos', []),
            'config_spsadt' => array_merge([
                'visivel' => [],
                'obrigatorio' => [],
                'bloqueado' => [],
            ], $request->input('config_spsadt', []) ?: [])
        ]);
        $data = $request->validate($this->rules());
        $data['config_spsadt'] = $request->input('config_spsadt');
        $data['tipo'] = strtoupper(trim((string)($data['tipo'] ?? 'CONVENIO')));
        if (!$data['tipo']) {
            return back()->withErrors(['tipo' => 'Tipo inválido.']);
        }
        $tussInput = (array)($request->input('tuss_ids', []));
        $tussData = []; $tussMapeamentoData = []; $tussGridMap = [];
        foreach ($tussInput as $item) {
            if (is_array($item)) {
                if (!empty($item['is_mapeamento']) && !empty($item['tuss_mapeamento_id'])) {
                    if (isset($item['id'])) {
                        $tussGridMap[(int)$item['id']] = [
                            'is_mapeamento' => true,
                            'tuss_mapeamento_id' => (int)$item['tuss_mapeamento_id']
                        ];
                    }
                    $tussMapeamentoData[(int)$item['tuss_mapeamento_id']] = [
                        'requer_autorizacao' => !empty($item['requer_autorizacao']),
                        'valor_ch' => isset($item['valor_ch']) ? (float)$item['valor_ch'] : 0,
                        'valor_co' => isset($item['valor_co']) ? (float)$item['valor_co'] : 0,
                    ];
                } elseif (isset($item['id'])) {
                    $tussGridMap[(int)$item['id']] = [
                        'is_mapeamento' => false,
                        'tuss_id' => (int)$item['id']
                    ];
                    $tussData[(int)$item['id']] = [
                        'requer_autorizacao' => !empty($item['requer_autorizacao']),
                        'valor_ch' => isset($item['valor_ch']) ? (float)$item['valor_ch'] : 0,
                        'valor_co' => isset($item['valor_co']) ? (float)$item['valor_co'] : 0,
                    ];
                }
            } elseif (is_numeric($item)) {
                $tussGridMap[(int)$item] = [
                    'is_mapeamento' => false,
                    'tuss_id' => (int)$item
                ];
                $tussData[(int)$item] = [
                    'requer_autorizacao' => false,
                    'valor_ch' => 0,
                    'valor_co' => 0,
                ];
            }
        }
        $medicosInput = (array)($request->input('medicos', []));
        unset($data['tuss_ids'], $data['medicos']);

        if ($data['tipo'] === 'CONVENIO') {
            if (empty($tussData) && empty($tussMapeamentoData)) {
                return back()->withErrors(['tuss_ids' => 'Selecione ao menos 1 procedimento da TUSS para este convênio.'])
                    ->with('error', 'Erro de Validação: Selecione ao menos 1 procedimento da TUSS para este convênio.');
            }
            $data['tuss_tabela'] = null;
        } else {
            $data['tuss_tabela'] = null;
            $tussData = []; $tussMapeamentoData = [];
        }

        $logoFile = $request->file('logo');
        unset($data['logo']);
        if ($logoFile) $data['logo_path'] = $logoFile->store('convenios', 'public');

        $accountId = auth()->user()->account_id ?? null;
        $data['account_id'] = $accountId;
        $convenio = null;
        DB::transaction(function () use (&$convenio, $data, $tussData, $tussMapeamentoData, $tussGridMap, $medicosInput, $accountId) {
            $convenio = Convenio::create($data);
            if (!empty($tussData)) {
                $tussIds = array_keys($tussData);
                $tussModels = DB::table('tuss')->whereIn('id', $tussIds)->select('id', 'quantidade_ch', 'quantidade_co')->get()->keyBy('id');
                
                $rows = [];
                foreach ($tussData as $tid => $tData) {
                    $tModel = $tussModels->get($tid);
                    $qtdCh = $tModel ? (float)$tModel->quantidade_ch : 0;
                    $qtdCo = $tModel ? (float)$tModel->quantidade_co : 0;
                    $vProc = ($qtdCh * $tData['valor_ch']) + ($qtdCo * $tData['valor_co']);
                    
                    $rows[] = [
                        'convenio_id' => $convenio->id,
                        'tuss_id' => $tid,
                        'tuss_mapeamento_id' => null,
                        'requer_autorizacao' => $tData['requer_autorizacao'] ? 1 : 0,
                        'valor_ch' => $tData['valor_ch'],
                        'valor_co' => $tData['valor_co'],
                        'valor_procedimento' => $vProc,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'account_id' => $accountId,
                    ];
                }
                DB::table('convenio_tuss')->insert($rows);
            }

            if (!empty($tussMapeamentoData)) {
                $mapIds = array_keys($tussMapeamentoData);
                $mapModels = DB::table('tuss_mapeamentos')->whereIn('id', $mapIds)
                                ->select('id', 'referencia_procedimento_id')
                                ->get()->keyBy('id');
                $refIds = $mapModels->pluck('referencia_procedimento_id')->filter()->unique();
                $refModels = DB::table('tuss')->whereIn('id', $refIds)->select('id', 'quantidade_ch', 'quantidade_co')->get()->keyBy('id');
                
                $rows = [];
                foreach ($tussMapeamentoData as $mapId => $tData) {
                    $mapModel = $mapModels->get($mapId);
                    $refModel = $mapModel ? $refModels->get($mapModel->referencia_procedimento_id) : null;
                    $qtdCh = $refModel ? (float)$refModel->quantidade_ch : 0;
                    $qtdCo = $refModel ? (float)$refModel->quantidade_co : 0;
                    $vProc = ($qtdCh * $tData['valor_ch']) + ($qtdCo * $tData['valor_co']);
                    
                    $rows[] = [
                        'convenio_id' => $convenio->id,
                        'tuss_id' => null,
                        'tuss_mapeamento_id' => $mapId,
                        'requer_autorizacao' => $tData['requer_autorizacao'] ? 1 : 0,
                        'valor_ch' => $tData['valor_ch'],
                        'valor_co' => $tData['valor_co'],
                        'valor_procedimento' => $vProc,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'account_id' => $accountId,
                    ];
                }
                DB::table('convenio_tuss')->insert($rows);
            }
            if (!empty($medicosInput)) {
                $medicoTussRows = [];
                foreach ($medicosInput as $m) {
                    $mTuss = array_values(array_unique(array_map('intval', (array)($m['tuss_ids'] ?? []))));
                    $hasValid = false;
                    foreach ($mTuss as $tid) {
                        if (isset($tussGridMap[$tid])) {
                            $hasValid = true;
                            $mapInfo = $tussGridMap[$tid];
                            $medicoTussRows[] = [
                                'convenio_id' => $convenio->id,
                                'pessoa_id' => $m['id'],
                                'tuss_id' => $mapInfo['is_mapeamento'] ? null : $mapInfo['tuss_id'],
                                'tuss_mapeamento_id' => $mapInfo['is_mapeamento'] ? $mapInfo['tuss_mapeamento_id'] : null,
                                'created_at' => now(),
                                'updated_at' => now(),
                                'account_id' => $accountId,
                            ];
                        }
                    }
                    if (!$hasValid) {
                        $medicoTussRows[] = [
                            'convenio_id' => $convenio->id,
                            'pessoa_id' => $m['id'],
                            'tuss_id' => null,
                            'tuss_mapeamento_id' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                            'account_id' => $accountId,
                        ];
                    }
                }
                if (!empty($medicoTussRows)) {
                    DB::table('convenio_medico_tuss')->insert($medicoTussRows);
                }
            }
        });

        return back()->with('success', 'Convênio cadastrado');
    }

    public function update(Request $request, string $id)
    {
        // dd($request->all());
        \Log::info('Convenio Update Payload:', $request->all());
        $convenio = Convenio::findOrFail($id);
        $request->merge([
            'tuss_tabela' => trim((string)$request->input('tuss_tabela', '')) !== '' ? trim((string)$request->input('tuss_tabela')) : null,
            'tuss_ids' => $request->input('tuss_ids', []),
            'medicos' => $request->input('medicos', []),
            'config_spsadt' => array_merge([
                'visivel' => [],
                'obrigatorio' => [],
                'bloqueado' => [],
            ], $request->input('config_spsadt', []) ?: [])
        ]);
        $data = $request->validate($this->rules());
        $data['config_spsadt'] = $request->input('config_spsadt');
        \Log::info('Convenio Update Data array:', ['data' => $data]);
        $data['tipo'] = strtoupper(trim((string)($data['tipo'] ?? $convenio->tipo ?? 'CONVENIO')));
        if (!$data['tipo']) {
            return back()->withErrors(['tipo' => 'Tipo inválido.']);
        }
        $tussInput = (array)($request->input('tuss_ids', []));
        $tussData = []; $tussMapeamentoData = []; $tussGridMap = [];
        foreach ($tussInput as $item) {
            if (is_array($item)) {
                if (!empty($item['is_mapeamento']) && !empty($item['tuss_mapeamento_id'])) {
                    if (isset($item['id'])) {
                        $tussGridMap[(int)$item['id']] = [
                            'is_mapeamento' => true,
                            'tuss_mapeamento_id' => (int)$item['tuss_mapeamento_id']
                        ];
                    }
                    $tussMapeamentoData[(int)$item['tuss_mapeamento_id']] = [
                        'requer_autorizacao' => !empty($item['requer_autorizacao']),
                        'valor_ch' => isset($item['valor_ch']) ? (float)$item['valor_ch'] : 0,
                        'valor_co' => isset($item['valor_co']) ? (float)$item['valor_co'] : 0,
                    ];
                } elseif (isset($item['id'])) {
                    $tussGridMap[(int)$item['id']] = [
                        'is_mapeamento' => false,
                        'tuss_id' => (int)$item['id']
                    ];
                    $tussData[(int)$item['id']] = [
                        'requer_autorizacao' => !empty($item['requer_autorizacao']),
                        'valor_ch' => isset($item['valor_ch']) ? (float)$item['valor_ch'] : 0,
                        'valor_co' => isset($item['valor_co']) ? (float)$item['valor_co'] : 0,
                    ];
                }
            } elseif (is_numeric($item)) {
                $tussGridMap[(int)$item] = [
                    'is_mapeamento' => false,
                    'tuss_id' => (int)$item
                ];
                $tussData[(int)$item] = [
                    'requer_autorizacao' => false,
                    'valor_ch' => 0,
                    'valor_co' => 0,
                ];
            }
        }
        $medicosInput = (array)($request->input('medicos', []));
        unset($data['tuss_ids'], $data['medicos']);

        if ($data['tipo'] === 'CONVENIO') {
            if (empty($tussData) && empty($tussMapeamentoData)) {
                return back()->withErrors(['tuss_ids' => 'Selecione ao menos 1 procedimento da TUSS para este convênio.'])
                    ->with('error', 'Erro de Validação: Selecione ao menos 1 procedimento da TUSS para este convênio.');
            }
            $data['tuss_tabela'] = null;
        } else {
            $data['tuss_tabela'] = null;
            $tussData = []; $tussMapeamentoData = [];
        }

        $logoFile = $request->file('logo');
        unset($data['logo']);
        if ($logoFile) {
            $old = (string)($convenio->logo_path ?? '');
            if ($old !== '') {
                try {
                    Storage::disk('public')->delete($old);
                } catch (\Throwable $e) {
                }
            }
            $data['logo_path'] = $logoFile->store('convenios', 'public');
        }
        $accountId = auth()->user()->account_id ?? null;
        $data['account_id'] = $accountId;
        DB::transaction(function () use ($convenio, $data, $tussData, $tussMapeamentoData, $tussGridMap, $medicosInput, $accountId) {
            $convenio->update($data);

            DB::table('convenio_tuss')
                ->where('convenio_id', $convenio->id)
                ->where('account_id', $accountId)
                ->delete();

            if (!empty($tussData)) {
                $tussIds = array_keys($tussData);
                $tussModels = DB::table('tuss')->whereIn('id', $tussIds)->select('id', 'quantidade_ch', 'quantidade_co')->get()->keyBy('id');
                
                $rows = [];
                foreach ($tussData as $tid => $tData) {
                    $tModel = $tussModels->get($tid);
                    $qtdCh = $tModel ? (float)$tModel->quantidade_ch : 0;
                    $qtdCo = $tModel ? (float)$tModel->quantidade_co : 0;
                    $vProc = ($qtdCh * $tData['valor_ch']) + ($qtdCo * $tData['valor_co']);
                    
                    $rows[] = [
                        'convenio_id' => $convenio->id,
                        'tuss_id' => $tid,
                        'tuss_mapeamento_id' => null,
                        'requer_autorizacao' => $tData['requer_autorizacao'] ? 1 : 0,
                        'valor_ch' => $tData['valor_ch'],
                        'valor_co' => $tData['valor_co'],
                        'valor_procedimento' => $vProc,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'account_id' => $accountId,
                    ];
                }
                DB::table('convenio_tuss')->insert($rows);
            }

            if (!empty($tussMapeamentoData)) {
                $mapIds = array_keys($tussMapeamentoData);
                $mapModels = DB::table('tuss_mapeamentos')->whereIn('id', $mapIds)
                                ->select('id', 'referencia_procedimento_id')
                                ->get()->keyBy('id');
                $refIds = $mapModels->pluck('referencia_procedimento_id')->filter()->unique();
                $refModels = DB::table('tuss')->whereIn('id', $refIds)->select('id', 'quantidade_ch', 'quantidade_co')->get()->keyBy('id');
                
                $rows = [];
                foreach ($tussMapeamentoData as $mapId => $tData) {
                    $mapModel = $mapModels->get($mapId);
                    $refModel = $mapModel ? $refModels->get($mapModel->referencia_procedimento_id) : null;
                    $qtdCh = $refModel ? (float)$refModel->quantidade_ch : 0;
                    $qtdCo = $refModel ? (float)$refModel->quantidade_co : 0;
                    $vProc = ($qtdCh * $tData['valor_ch']) + ($qtdCo * $tData['valor_co']);
                    
                    $rows[] = [
                        'convenio_id' => $convenio->id,
                        'tuss_id' => null,
                        'tuss_mapeamento_id' => $mapId,
                        'requer_autorizacao' => $tData['requer_autorizacao'] ? 1 : 0,
                        'valor_ch' => $tData['valor_ch'],
                        'valor_co' => $tData['valor_co'],
                        'valor_procedimento' => $vProc,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'account_id' => $accountId,
                    ];
                }
                DB::table('convenio_tuss')->insert($rows);
            }

            DB::table('convenio_medico_tuss')
                ->where('convenio_id', $convenio->id)
                ->delete();

            if (!empty($medicosInput)) {
                $medicoTussRows = [];
                foreach ($medicosInput as $m) {
                    $mTuss = array_values(array_unique(array_map('intval', (array)($m['tuss_ids'] ?? []))));
                    $hasValid = false;
                    foreach ($mTuss as $tid) {
                        if (isset($tussGridMap[$tid])) {
                            $hasValid = true;
                            $mapInfo = $tussGridMap[$tid];
                            $medicoTussRows[] = [
                                'convenio_id' => $convenio->id,
                                'pessoa_id' => $m['id'],
                                'tuss_id' => $mapInfo['is_mapeamento'] ? null : $mapInfo['tuss_id'],
                                'tuss_mapeamento_id' => $mapInfo['is_mapeamento'] ? $mapInfo['tuss_mapeamento_id'] : null,
                                'created_at' => now(),
                                'updated_at' => now(),
                                'account_id' => $accountId,
                            ];
                        }
                    }
                    if (!$hasValid) {
                        $medicoTussRows[] = [
                            'convenio_id' => $convenio->id,
                            'pessoa_id' => $m['id'],
                            'tuss_id' => null,
                            'tuss_mapeamento_id' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                            'account_id' => $accountId,
                        ];
                    }
                }
                if (!empty($medicoTussRows)) {
                    DB::table('convenio_medico_tuss')->insert($medicoTussRows);
                }
            }
        });

        return back()->with('success', 'Convênio atualizado');
    }

    public function tussProcedimentos(Request $request, int $id)
    {
        $convenio = Convenio::select('id', 'tuss_tabela')->findOrFail($id);
        $q = trim((string)$request->query('q', ''));
        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 10);
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));

        $base = DB::table('convenio_tuss as ct')
            ->leftJoin('tuss as t', 't.id', '=', 'ct.tuss_id')
            ->leftJoin('tuss_mapeamentos as tm', 'tm.id', '=', 'ct.tuss_mapeamento_id')
            ->leftJoin('tuss as o', 'o.id', '=', 'tm.origem_procedimento_id')
            ->leftJoin('tuss as r', 'r.id', '=', 'tm.referencia_procedimento_id')
            ->where('ct.convenio_id', $convenio->id)
            ->where('ct.account_id', auth()->user()->account_id ?? null)
            ->whereNull('ct.deleted_at')
            ->where(function ($query) {
                $query->whereNull('t.deleted_at')
                      ->orWhere(function ($q2) {
                          $q2->whereNull('tm.deleted_at')
                             ->whereNull('o.deleted_at');
                      });
            });

        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('t.codigo', 'like', '%' . $q . '%')
                  ->orWhere('t.descricao', 'like', '%' . $q . '%')
                  ->orWhere('o.codigo', 'like', '%' . $q . '%')
                  ->orWhere('o.descricao', 'like', '%' . $q . '%');
            });
        }

        \Log::info('tussProcedimentos Query:', ['sql' => $base->toSql(), 'bindings' => $base->getBindings(), 'account_id' => auth()->user()->account_id ?? null, 'results' => $base->get()]);
        $total = (clone $base)->count();
        $rows = $base
            ->select(
                DB::raw('COALESCE(t.id, o.id) as id'),
                DB::raw('COALESCE(t.tabela, o.tabela) as tabela'),
                DB::raw('COALESCE(t.codigo, o.codigo) as codigo'),
                DB::raw('COALESCE(t.descricao, o.descricao) as descricao'),
                DB::raw('COALESCE(t.quantidade_ch, r.quantidade_ch) as quantidade_ch'),
                DB::raw('COALESCE(t.quantidade_co, r.quantidade_co) as quantidade_co'),
                'ct.requer_autorizacao', 
                'ct.valor_ch', 
                'ct.valor_co', 
                'ct.valor_procedimento',
                'ct.tuss_mapeamento_id',
                DB::raw('IF(ct.tuss_mapeamento_id IS NOT NULL, 1, 0) as is_mapeamento')
            )
            ->orderBy(DB::raw('COALESCE(t.descricao, o.descricao)'))
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'data' => $rows,
            'meta' => [
                'convenio_id' => $convenio->id,
                'tuss_tabela' => $convenio->tuss_tabela,
                'q' => $q,
                'page' => $page,
                'per_page' => $perPage,
                'total' => (int)$total,
                'total_pages' => (int)ceil(((int)$total) / $perPage),
            ],
        ]);
    }

    public function procedimentosOrcamento(Request $request, int $id)
    {
        $convenio = Convenio::select('id', 'tipo')->findOrFail($id);
        $tipo = strtoupper((string)($convenio->tipo ?? ''));
        if ($tipo === 'PARTICULAR') {
            $rows = \App\Models\Procedimento::query()
                ->where('ativo', 1)
                ->select('id', 'nome', 'descricao', 'valor', 'categoria_id', 'eh_tratamento', 'quantidade_sessoes')
                ->orderBy('nome')
                ->get();
            $out = $rows->map(fn($p) => [
                'source' => 'procedimento',
                'id' => (int)($p->id ?? 0),
                'nome' => $p->nome ?? '',
                'descricao' => $p->descricao ?? null,
                'valor' => $p->valor ?? 0,
                'eh_tratamento' => (bool)($p->eh_tratamento ?? false),
                'quantidade_sessoes' => $p->quantidade_sessoes ?? null,
            ])->values();
            return response()->json(['procedimentos' => $out]);
        }

        $select = [
            DB::raw('COALESCE(t.id, o.id) as id'),
            DB::raw('COALESCE(t.tabela, o.tabela) as tabela'),
            DB::raw('COALESCE(t.codigo, o.codigo) as codigo'),
            DB::raw('COALESCE(t.descricao, o.descricao) as descricao'),
            DB::raw('COALESCE(t.quantidade_ch, r.quantidade_ch) as quantidade_ch'),
            DB::raw('COALESCE(t.quantidade_co, r.quantidade_co) as quantidade_co'),
            'ct.requer_autorizacao', 'ct.valor_ch', 'ct.valor_co', 'ct.valor_procedimento',
            'ct.tuss_mapeamento_id',
            DB::raw('IF(ct.tuss_mapeamento_id IS NOT NULL, 1, 0) as is_mapeamento')
        ];
        if (Schema::hasColumn('tuss', 'eh_tratamento')) $select[] = DB::raw('COALESCE(t.eh_tratamento, o.eh_tratamento) as eh_tratamento');
        if (Schema::hasColumn('tuss', 'quantidade_sessoes')) $select[] = DB::raw('COALESCE(t.quantidade_sessoes, o.quantidade_sessoes) as quantidade_sessoes');

        $tussRows = DB::table('convenio_tuss as ct')
            ->leftJoin('tuss as t', 't.id', '=', 'ct.tuss_id')
            ->leftJoin('tuss_mapeamentos as tm', 'tm.id', '=', 'ct.tuss_mapeamento_id')
            ->leftJoin('tuss as o', 'o.id', '=', 'tm.origem_procedimento_id')
            ->leftJoin('tuss as r', 'r.id', '=', 'tm.referencia_procedimento_id')
            ->where('ct.convenio_id', (int)$convenio->id)
            ->where('ct.account_id', auth()->user()->account_id ?? null)
            ->whereNull('ct.deleted_at')
            ->where(function ($query) {
                $query->whereNull('t.deleted_at')
                      ->orWhere(function ($q2) {
                          $q2->whereNull('tm.deleted_at')
                             ->whereNull('o.deleted_at');
                      });
            })
            ->select($select)
            ->distinct()
            ->get();

        if ($tussRows->isEmpty()) {
            return response()->json(['procedimentos' => []]);
        }
        $out = $tussRows->map(fn($t) => [
            'source' => 'tuss',
            'id' => (int)($t->id ?? 0),
            'tabela' => $t->tabela ?? '',
            'codigo' => $t->codigo ?? '',
            'nome' => trim((string)($t->descricao ?? '')),
            'descricao' => (trim((string)($t->tabela ?? '')) !== '' && trim((string)($t->codigo ?? '')) !== '') ? (trim((string)$t->tabela) . ' ' . trim((string)$t->codigo)) : null,
            'valor' => (float)($t->valor_procedimento ?? 0) > 0 ? (float)$t->valor_procedimento : (float)($t->total ?? 0),
            'eh_tratamento' => (bool)($t->eh_tratamento ?? false),
            'quantidade_sessoes' => $t->quantidade_sessoes ?? null,
            'requer_autorizacao' => (bool)($t->requer_autorizacao ?? false),
        ])->values();

        return response()->json(['procedimentos' => $out]);
    }

    public function destroy(string $id)
    {
        $convenio = Convenio::findOrFail($id);
        $convenio->delete();

        return back()->with('success', 'Convênio excluído');
    }
}
