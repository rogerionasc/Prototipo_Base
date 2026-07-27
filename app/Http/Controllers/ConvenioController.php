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
    private function normalizeTipo(?string $tipo): ?string
    {
        $t = trim((string)($tipo ?? ''));
        if ($t === '') return null;
        $t = strtoupper(Str::ascii($t));
        if ($t === 'CONVENIO') return 'Convenio';
        if ($t === 'PARTICULAR') return 'Particular';
        return null;
    }

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
        ];
    }

    public function index()
    {
        $convenios = Convenio::with(['medicos:id,nome,crm', 'medicos.especialidades:id,nome', 'medicoTuss:id'])
            ->select('id','descricao','logo_path','tuss_tabela','tipo','empresa_id','ans','dias_recebimento','dias_retorno')
            ->get();
        $contas = Conta::select('id','nome')->orderBy('nome')->get();
        $profissionaisSaude = Pessoa::with(['especialidades:id,nome'])->select('id','nome','crm')->orderBy('nome')->get();
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
        return Inertia::render('Convenios/Index', [
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
        ]);
        $data = $request->validate($this->rules());
        $data['tipo'] = $this->normalizeTipo($data['tipo'] ?? 'Convenio');
        if (!$data['tipo']) {
            return back()->withErrors(['tipo' => 'Tipo inválido.']);
        }
        $tussInput = (array)($request->input('tuss_ids', []));
        $tussData = [];
        foreach ($tussInput as $item) {
            if (is_array($item) && isset($item['id'])) {
                $tussData[(int)$item['id']] = !empty($item['requer_autorizacao']);
            } elseif (is_numeric($item)) {
                $tussData[(int)$item] = false;
            }
        }
        $medicosInput = (array)($request->input('medicos', []));
        unset($data['tuss_ids'], $data['medicos']);

        if ($data['tipo'] === 'Convenio') {
            if (empty($tussData)) {
                return back()->withErrors(['tuss_ids' => 'Selecione ao menos 1 procedimento da TUSS para este convênio.']);
            }
            $data['tuss_tabela'] = null;
        } else {
            $data['tuss_tabela'] = null;
            $tussData = [];
        }

        $logoFile = $request->file('logo');
        unset($data['logo']);
        if ($logoFile) $data['logo_path'] = $logoFile->store('convenios', 'public');

        $convenio = null;
        DB::transaction(function () use (&$convenio, $data, $tussData, $medicosInput) {
            $convenio = Convenio::create($data);
            if (!empty($tussData)) {
                $rows = [];
                foreach ($tussData as $tid => $reqAuth) {
                    $rows[] = [
                        'convenio_id' => $convenio->id,
                        'tuss_id' => $tid,
                        'requer_autorizacao' => $reqAuth ? 1 : 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DB::table('convenio_tuss')->insert($rows);
            }
            if (!empty($medicosInput)) {
                $medicoTussRows = [];
                foreach ($medicosInput as $m) {
                    $mTuss = array_values(array_unique(array_map('intval', (array)($m['tuss_ids'] ?? []))));
                    $mTuss = array_values(array_filter($mTuss, function($tid) use ($tussData) {
                        return array_key_exists($tid, $tussData);
                    }));
                    if (empty($mTuss)) {
                        $medicoTussRows[] = [
                            'convenio_id' => $convenio->id,
                            'pessoa_id' => $m['id'],
                            'tuss_id' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    } else {
                        foreach ($mTuss as $tid) {
                            $medicoTussRows[] = [
                                'convenio_id' => $convenio->id,
                                'pessoa_id' => $m['id'],
                                'tuss_id' => $tid,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }
                if (!empty($medicoTussRows)) {
                    DB::table('convenio_medico_tuss')->insert($medicoTussRows);
                }
            }
        });

        return back()->with('success','Convênio cadastrado');
    }

    public function update(Request $request, string $id)
    {
        // dd($request->all());
        $convenio = Convenio::findOrFail($id);
        $request->merge([
            'tuss_tabela' => trim((string)$request->input('tuss_tabela', '')) !== '' ? trim((string)$request->input('tuss_tabela')) : null,
            'tuss_ids' => $request->input('tuss_ids', []),
            'medicos' => $request->input('medicos', []),
        ]);
        $data = $request->validate($this->rules());
        $data['tipo'] = $this->normalizeTipo($data['tipo'] ?? $convenio->tipo ?? 'Convenio');
        if (!$data['tipo']) {
            return back()->withErrors(['tipo' => 'Tipo inválido.']);
        }
        $tussInput = (array)($request->input('tuss_ids', []));
        $tussData = [];
        foreach ($tussInput as $item) {
            if (is_array($item) && isset($item['id'])) {
                $tussData[(int)$item['id']] = !empty($item['requer_autorizacao']);
            } elseif (is_numeric($item)) {
                $tussData[(int)$item] = false;
            }
        }
        $medicosInput = (array)($request->input('medicos', []));
        unset($data['tuss_ids'], $data['medicos']);

        if ($data['tipo'] === 'Convenio') {
            if (empty($tussData)) {
                return back()->withErrors(['tuss_ids' => 'Selecione ao menos 1 procedimento da TUSS para este convênio.']);
            }
            $data['tuss_tabela'] = null;
        } else {
            $data['tuss_tabela'] = null;
            $tussData = [];
        }

        $logoFile = $request->file('logo');
        unset($data['logo']);
        if ($logoFile) {
            $old = (string)($convenio->logo_path ?? '');
            if ($old !== '') {
                try { Storage::disk('public')->delete($old); } catch (\Throwable $e) {}
            }
            $data['logo_path'] = $logoFile->store('convenios', 'public');
        }
        DB::transaction(function () use ($convenio, $data, $tussData, $medicosInput) {
            $convenio->update($data);

            DB::table('convenio_tuss')
                ->where('convenio_id', $convenio->id)
                ->delete();

            if (!empty($tussData)) {
                $rows = [];
                foreach ($tussData as $tid => $reqAuth) {
                    $rows[] = [
                        'convenio_id' => $convenio->id,
                        'tuss_id' => $tid,
                        'requer_autorizacao' => $reqAuth ? 1 : 0,
                        'created_at' => now(),
                        'updated_at' => now(),
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
                    $mTuss = array_values(array_filter($mTuss, function($tid) use ($tussData) {
                        return array_key_exists($tid, $tussData);
                    }));
                    if (empty($mTuss)) {
                        $medicoTussRows[] = [
                            'convenio_id' => $convenio->id,
                            'pessoa_id' => $m['id'],
                            'tuss_id' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    } else {
                        foreach ($mTuss as $tid) {
                            $medicoTussRows[] = [
                                'convenio_id' => $convenio->id,
                                'pessoa_id' => $m['id'],
                                'tuss_id' => $tid,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }
                if (!empty($medicoTussRows)) {
                    DB::table('convenio_medico_tuss')->insert($medicoTussRows);
                }
            }
        });

        return back()->with('success','Convênio atualizado');
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
            ->join('tuss as t', 't.id', '=', 'ct.tuss_id')
            ->where('ct.convenio_id', $convenio->id)
            ->whereNull('ct.deleted_at')
            ->whereNull('t.deleted_at');

        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('t.codigo', 'like', '%' . $q . '%')
                    ->orWhere('t.descricao', 'like', '%' . $q . '%');
            });
        }

        $total = (clone $base)->count();
        $rows = $base
            ->select('t.id', 't.tabela', 't.codigo', 't.descricao', 't.total', 'ct.requer_autorizacao')
            ->orderBy('t.descricao')
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
            $rows = DB::table('procedimentos as p')
                ->whereNull('p.deleted_at')
                ->where('p.ativo', 1)
                ->select('p.id', 'p.nome', 'p.descricao', 'p.valor', 'p.categoria_id', 'p.eh_tratamento', 'p.quantidade_sessoes')
                ->orderBy('p.nome')
                ->get();
            $out = $rows->map(fn ($p) => [
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

        $select = ['t.id', 't.tabela', 't.codigo', 't.descricao', 't.total'];
        if (Schema::hasColumn('tuss', 'eh_tratamento')) $select[] = 't.eh_tratamento';
        if (Schema::hasColumn('tuss', 'quantidade_sessoes')) $select[] = 't.quantidade_sessoes';

        $tussRows = DB::table('convenio_tuss as ct')
            ->join('tuss as t', function ($j) {
                $j->on('t.id', '=', 'ct.tuss_id')
                  ->whereNull('t.deleted_at');
            })
            ->where('ct.convenio_id', (int)$convenio->id)
            ->whereNull('ct.deleted_at')
            ->select($select)
            ->distinct()
            ->get();

        if ($tussRows->isEmpty()) {
            return response()->json(['procedimentos' => []]);
        }
        $out = $tussRows->map(fn ($t) => [
            'source' => 'tuss',
            'id' => (int)($t->id ?? 0),
            'tabela' => $t->tabela ?? '',
            'codigo' => $t->codigo ?? '',
            'nome' => trim((string)($t->descricao ?? '')),
            'descricao' => (trim((string)($t->tabela ?? '')) !== '' && trim((string)($t->codigo ?? '')) !== '') ? (trim((string)$t->tabela) . ' ' . trim((string)$t->codigo)) : null,
            'valor' => $t->total ?? 0,
            'eh_tratamento' => (bool)($t->eh_tratamento ?? false),
            'quantidade_sessoes' => $t->quantidade_sessoes ?? null,
        ])->values();

        return response()->json(['procedimentos' => $out]);
    }

    public function destroy(string $id)
    {
        $convenio = Convenio::findOrFail($id);
        $convenio->delete();

        return back()->with('success','Convênio excluído');
    }
}
