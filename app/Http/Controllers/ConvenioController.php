<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Convenio;
use App\Models\Conta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConvenioController extends Controller
{
    private function rules(): array
    {
        return [
            'descricao' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'tuss_tabela' => ['nullable', 'exclude_if:tuss_tabela,', 'string', 'max:20', 'exists:tuss,tabela'],
            'tuss_ids' => ['nullable', 'array'],
            'tuss_ids.*' => ['integer', 'exists:tuss,id'],
            'tipo' => ['nullable', 'in:Convenio,Particular'],
            'empresa_id' => ['nullable', 'integer', 'exists:contas,id'],
            'ans' => ['nullable', 'integer'],
            'dias_recebimento' => ['nullable', 'integer'],
            'dias_retorno' => ['nullable', 'integer'],
        ];
    }

    public function index()
    {
        $convenios = Convenio::select('id','descricao','logo_path','tuss_tabela','tipo','empresa_id','ans','dias_recebimento','dias_retorno')->get();
        $contas = Conta::select('id','nome')->orderBy('nome')->get();
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
            'tussTabelas' => $tussTabelas,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'tuss_tabela' => trim((string)$request->input('tuss_tabela', '')) !== '' ? trim((string)$request->input('tuss_tabela')) : null,
        ]);
        $data = $request->validate($this->rules());
        $data['tipo'] = $data['tipo'] ?? 'Convenio';
        $tipo = strtoupper((string)$data['tipo']);
        $tussIds = array_values(array_unique(array_map('intval', (array)($data['tuss_ids'] ?? []))));
        unset($data['tuss_ids']);

        if ($tipo === 'CONVENIO') {
            if (empty($tussIds)) {
                return back()->withErrors(['tuss_ids' => 'Selecione ao menos 1 procedimento da TUSS para este convênio.']);
            }
            $data['tuss_tabela'] = null;
        } else {
            $data['tuss_tabela'] = null;
            $tussIds = [];
        }

        $logoFile = $request->file('logo');
        unset($data['logo']);
        if ($logoFile) $data['logo_path'] = $logoFile->store('convenios', 'public');

        $convenio = null;
        DB::transaction(function () use (&$convenio, $data, $tussIds) {
            $convenio = Convenio::create($data);
            if (!empty($tussIds)) {
                $rows = array_map(fn($tid) => [
                    'convenio_id' => $convenio->id,
                    'tuss_id' => $tid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $tussIds);
                DB::table('convenio_tuss')->insert($rows);
            }
        });

        return back()->with('success','Convênio cadastrado');
    }

    public function update(Request $request, string $id)
    {
        $convenio = Convenio::findOrFail($id);
        $request->merge([
            'tuss_tabela' => trim((string)$request->input('tuss_tabela', '')) !== '' ? trim((string)$request->input('tuss_tabela')) : null,
        ]);
        $data = $request->validate($this->rules());
        $data['tipo'] = $data['tipo'] ?? $convenio->tipo ?? 'Convenio';
        $tipo = strtoupper((string)$data['tipo']);
        $tussIds = array_values(array_unique(array_map('intval', (array)($data['tuss_ids'] ?? []))));
        unset($data['tuss_ids']);

        if ($tipo === 'CONVENIO') {
            if (empty($tussIds)) {
                return back()->withErrors(['tuss_ids' => 'Selecione ao menos 1 procedimento da TUSS para este convênio.']);
            }
            $data['tuss_tabela'] = null;
        } else {
            $data['tuss_tabela'] = null;
            $tussIds = [];
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
        DB::transaction(function () use ($convenio, $data, $tussIds) {
            $convenio->update($data);

            DB::table('convenio_tuss')
                ->where('convenio_id', $convenio->id)
                ->delete();

            if (!empty($tussIds)) {
                $rows = array_map(fn($tid) => [
                    'convenio_id' => $convenio->id,
                    'tuss_id' => $tid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $tussIds);
                DB::table('convenio_tuss')->insert($rows);
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
            ->select('t.id', 't.tabela', 't.codigo', 't.descricao', 't.total')
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

    public function destroy(string $id)
    {
        $convenio = Convenio::findOrFail($id);
        $convenio->delete();

        return back()->with('success','Convênio excluído');
    }
}
