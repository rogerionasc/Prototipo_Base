<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Orcamento;
use App\Models\OrcamentoProcedimento;
use App\Models\Paciente;
use App\Models\ProfissionalSaude;
use App\Models\Convenio;
use App\Models\Procedimento;

class OrcamentoController extends Controller
{
    public function index()
    {
        $pacientes = Paciente::select('id', 'nome', 'cpf', 'email', 'celular')->orderBy('nome')->get();
        $profissionais = ProfissionalSaude::select('id', 'nome', 'crm', 'celular', 'email')->orderBy('nome')->get();
        $convenios = Convenio::select('id', 'descricao')->orderBy('descricao')->get();
        $procedimentos = Procedimento::select('id', 'nome', 'valor', 'categoria_id', 'eh_tratamento', 'quantidade_sessoes')->orderBy('nome')->get();
        $procConvenio = DB::table('procedimento_convenio')
            ->select('procedimento_id', 'convenio_id', 'valor_convenio')
            ->get();
        $ultimos = DB::table('orcamentos as o')
            ->leftJoin('pacientes as p', 'p.id', '=', 'o.paciente_id')
            ->select(
                'o.id',
                'o.numero',
                DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y') AS data_emissao"),
                DB::raw("DATE_FORMAT(o.updated_at, '%d-%m-%Y %H:%i') AS criado_em"),
                'o.valor_total',
                'o.aprovado',
                DB::raw("COALESCE(p.nome,'') AS paciente"),
            )
            ->orderByDesc('o.updated_at')
            ->limit(10)
            ->get();

        return Inertia::render('Atendimento/Orcamentos/Index', [
            'pacientes' => $pacientes,
            'profissionais' => $profissionais,
            'convenios' => $convenios,
            'procedimentos' => $procedimentos,
            'procedimentoConvenio' => $procConvenio,
            'ultimos' => $ultimos,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'profissional_saude_id' => ['required', 'integer', 'exists:profissionais_saude,id'],
            'convenio_id' => ['nullable', 'integer', 'exists:convenios,id'],
            'data_emissao' => ['nullable', 'date_format:d-m-Y'],
            'validade' => ['nullable', 'date_format:d-m-Y'],
            'desconto' => ['nullable', 'numeric', 'min:0'],
            'faturamento_previsto' => ['nullable', 'boolean'],
            'aprovado' => ['nullable', 'boolean'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.procedimento_id' => ['required', 'integer', 'exists:procedimentos,id'],
            'itens.*.quantidade' => ['required', 'integer', 'min:1'],
            'itens.*.observacoes' => ['nullable', 'string'],
        ]);

        $convenioId = $data['convenio_id'] ?? null;
        $valorBruto = 0;
        $itensValores = [];

        $mapConvenio = [];
        if ($convenioId) {
            $rows = DB::table('procedimento_convenio')
                ->where('convenio_id', $convenioId)
                ->select('procedimento_id', 'valor_convenio')
                ->get();
            foreach ($rows as $r) {
                $mapConvenio[(string)$r->procedimento_id] = $r->valor_convenio;
            }
        }

        foreach ($data['itens'] as $idx => $item) {
            $proc = Procedimento::select('id', 'valor')->findOrFail($item['procedimento_id']);
            $valorUnit = $proc->valor ?? 0;
            if ($convenioId && array_key_exists((string)$proc->id, $mapConvenio) && $mapConvenio[(string)$proc->id] !== null) {
                $valorUnit = $mapConvenio[(string)$proc->id];
            }
            $qtd = (int)($item['quantidade'] ?? 1);
            $vTotal = ($valorUnit ?? 0) * $qtd;
            $valorBruto += $vTotal;
            $itensValores[$idx] = [
                'procedimento_id' => $proc->id,
                'quantidade' => $qtd,
                'valor_unitario' => $valorUnit ?? 0,
                'valor_total' => $vTotal,
                'observacoes' => $item['observacoes'] ?? null,
            ];
        }

        $desconto = (float)($data['desconto'] ?? 0);
        $valorTotal = max(0, $valorBruto - $desconto);

        $numero = 'ORC-' . now()->format('YmdHis');
        $deYmd = isset($data['data_emissao'])
            ? Carbon::createFromFormat('d-m-Y', $data['data_emissao'])->format('Y-m-d')
            : now()->toDateString();
        $vaYmd = isset($data['validade'])
            ? Carbon::createFromFormat('d-m-Y', $data['validade'])->format('Y-m-d')
            : now()->addDays(30)->toDateString();
        $orcamento = Orcamento::create([
            'numero' => $numero,
            'data_emissao' => $deYmd,
            'validade' => $vaYmd,
            'paciente_id' => $data['paciente_id'],
            'profissional_saude_id' => $data['profissional_saude_id'],
            'convenio_id' => $convenioId,
            'valor_bruto' => $valorBruto,
            'desconto' => $desconto,
            'valor_total' => $valorTotal,
            'valor_avista' => null,
            'faturamento_previsto' => (bool)($data['faturamento_previsto'] ?? false),
            'aprovado' => (bool)($data['aprovado'] ?? false),
        ]);

        foreach ($itensValores as $iv) {
            $iv['orcamento_id'] = $orcamento->id;
            OrcamentoProcedimento::create($iv);
        }

        return back()->with('success', 'Orçamento criado com sucesso');
    }

    public function byPaciente(string $id)
    {
        $orcamentos = Orcamento::select(
                'id',
                'numero',
                DB::raw("DATE_FORMAT(data_emissao, '%d-%m-%Y') AS data_emissao"),
                DB::raw("DATE_FORMAT(validade, '%d-%m-%Y') AS validade"),
                'valor_bruto',
                'desconto',
                'valor_total',
                'aprovado'
            )
            ->where('paciente_id', $id)
            ->orderByDesc('created_at')
            ->get();
        return response()->json([
            'orcamentos' => $orcamentos,
        ]);
    }

    public function show(string $id)
    {
        $o = Orcamento::select(
                'id',
                'numero',
                DB::raw("DATE_FORMAT(data_emissao, '%d-%m-%Y') AS data_emissao"),
                DB::raw("DATE_FORMAT(validade, '%d-%m-%Y') AS validade"),
                'paciente_id',
                'profissional_saude_id',
                'convenio_id',
                'valor_bruto',
                'desconto',
                'valor_total',
                'faturamento_previsto',
                'aprovado'
            )->findOrFail($id);
        $itens = OrcamentoProcedimento::select(
                'id',
                'procedimento_id',
                'quantidade',
                'valor_unitario',
                'valor_total',
                'observacoes'
            )->where('orcamento_id', $id)->get();
        return response()->json([
            'orcamento' => $o,
            'itens' => $itens,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $orcamento = Orcamento::findOrFail($id);
        $isApproved = !!$orcamento->aprovado;
        $isExpired = false;
        try {
            $vd = (string)$orcamento->validade;
            if ($vd) {
                $d = Carbon::parse($vd)->endOfDay();
                $isExpired = Carbon::now()->gt($d);
            }
        } catch (\Throwable $e) { }
        if ($isApproved || $isExpired) {
            return response()->json([
                'errors' => [
                    'orcamento' => ['Orçamento bloqueado por aprovação ou validade expirada.']
                ]
            ], 422);
        }
        $data = $request->validate([
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'profissional_saude_id' => ['required', 'integer', 'exists:profissionais_saude,id'],
            'convenio_id' => ['nullable', 'integer', 'exists:convenios,id'],
            'data_emissao' => ['nullable', 'date_format:d-m-Y'],
            'validade' => ['nullable', 'date_format:d-m-Y'],
            'desconto' => ['nullable', 'numeric', 'min:0'],
            'faturamento_previsto' => ['nullable', 'boolean'],
            'aprovado' => ['nullable', 'boolean'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.procedimento_id' => ['required', 'integer', 'exists:procedimentos,id'],
            'itens.*.quantidade' => ['required', 'integer', 'min:1'],
            'itens.*.observacoes' => ['nullable', 'string'],
        ]);

        $convenioId = $data['convenio_id'] ?? null;
        $valorBruto = 0;
        $itensValores = [];

        $mapConvenio = [];
        if ($convenioId) {
            $rows = DB::table('procedimento_convenio')
                ->where('convenio_id', $convenioId)
                ->select('procedimento_id', 'valor_convenio')
                ->get();
            foreach ($rows as $r) {
                $mapConvenio[(string)$r->procedimento_id] = $r->valor_convenio;
            }
        }

        foreach ($data['itens'] as $idx => $item) {
            $proc = Procedimento::select('id', 'valor')->findOrFail($item['procedimento_id']);
            $valorUnit = $proc->valor ?? 0;
            if ($convenioId && array_key_exists((string)$proc->id, $mapConvenio) && $mapConvenio[(string)$proc->id] !== null) {
                $valorUnit = $mapConvenio[(string)$proc->id];
            }
            $qtd = (int)($item['quantidade'] ?? 1);
            $vTotal = ($valorUnit ?? 0) * $qtd;
            $valorBruto += $vTotal;
            $itensValores[$idx] = [
                'procedimento_id' => $proc->id,
                'quantidade' => $qtd,
                'valor_unitario' => $valorUnit ?? 0,
                'valor_total' => $vTotal,
                'observacoes' => $item['observacoes'] ?? null,
            ];
        }

        $desconto = (float)($data['desconto'] ?? 0);
        $valorTotal = max(0, $valorBruto - $desconto);

        $deYmd = isset($data['data_emissao'])
            ? Carbon::createFromFormat('d-m-Y', $data['data_emissao'])->format('Y-m-d')
            : $orcamento->data_emissao;
        $vaYmd = isset($data['validade'])
            ? Carbon::createFromFormat('d-m-Y', $data['validade'])->format('Y-m-d')
            : $orcamento->validade;

        DB::transaction(function () use ($orcamento, $data, $deYmd, $vaYmd, $valorBruto, $desconto, $valorTotal, $itensValores) {
            $orcamento->update([
                'data_emissao' => $deYmd,
                'validade' => $vaYmd,
                'paciente_id' => $data['paciente_id'],
                'profissional_saude_id' => $data['profissional_saude_id'],
                'convenio_id' => $data['convenio_id'] ?? null,
                'valor_bruto' => $valorBruto,
                'desconto' => $desconto,
                'valor_total' => $valorTotal,
                'faturamento_previsto' => (bool)($data['faturamento_previsto'] ?? false),
                'aprovado' => (bool)($data['aprovado'] ?? $orcamento->aprovado),
            ]);
            OrcamentoProcedimento::where('orcamento_id', $orcamento->id)->delete();
            foreach ($itensValores as $iv) {
                $iv['orcamento_id'] = $orcamento->id;
                OrcamentoProcedimento::create($iv);
            }
        });

        return back()->with('success', 'Orçamento atualizado com sucesso');
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if ($q === '') {
            return response()->json([
                'orcamentos' => [],
            ]);
        }
        $pacienteId = $request->get('paciente_id');
        $query = DB::table('orcamentos as o')
            ->leftJoin('pacientes as p', 'p.id', '=', 'o.paciente_id')
            ->select(
                'o.id',
                'o.numero',
                DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y') AS data_emissao"),
                DB::raw("DATE_FORMAT(o.validade, '%d-%m-%Y') AS validade"),
                'o.valor_total',
                'o.aprovado',
                DB::raw("COALESCE(p.nome,'') AS paciente"),
                DB::raw("COALESCE(p.cpf,'') AS cpf"),
            );
        if (!empty($pacienteId)) {
            $query->where('o.paciente_id', $pacienteId);
        }
        if ($q !== '') {
            $query->where(function ($qb) use ($q) {
                $qb->where('o.numero', 'like', '%' . $q . '%')
                   ->orWhere('p.nome', 'like', '%' . $q . '%')
                   ->orWhere('p.cpf', 'like', '%' . $q . '%');
            });
        }
        $results = $query->orderByDesc('o.created_at')->limit(100)->get();
        return response()->json([
            'orcamentos' => $results,
        ]);
    }
    public function print(string $id)
    {
        $o = DB::table('orcamentos as o')
            ->leftJoin('pacientes as p', 'p.id', '=', 'o.paciente_id')
            ->select(
                'o.id',
                'o.numero',
                DB::raw("DATE_FORMAT(o.data_emissao, '%d-%m-%Y') AS data_emissao"),
                DB::raw("DATE_FORMAT(o.validade, '%d-%m-%Y') AS validade"),
                'o.paciente_id',
                'o.profissional_saude_id',
                'o.convenio_id',
                'o.valor_bruto',
                'o.desconto',
                'o.valor_total',
                'o.faturamento_previsto',
                'o.aprovado',
                DB::raw("COALESCE(p.nome,'') AS paciente_nome"),
                DB::raw("COALESCE(p.cpf,'') AS paciente_cpf"),
            )
            ->where('o.id', $id)
            ->first();
        if (!$o) {
            abort(404);
        }
        $itens = DB::table('orcamento_procedimentos as op')
            ->leftJoin('procedimentos as pr', 'pr.id', '=', 'op.procedimento_id')
            ->select(
                'op.id',
                'op.procedimento_id',
                'op.quantidade',
                'op.valor_unitario',
                'op.valor_total',
                'op.observacoes',
                DB::raw("COALESCE(pr.nome,'') AS procedimento_nome")
            )
            ->where('op.orcamento_id', $id)
            ->whereNull('op.deleted_at')
            ->get();
        return Inertia::render('Atendimento/Orcamentos/OrcamentoPrint', [
            'orcamento' => $o,
            'itens' => $itens,
        ]);
    }
    public function approve(string $id)
    {
        $orcamento = Orcamento::findOrFail($id);
        if ($orcamento->aprovado) {
            return response()->json(['success' => true, 'message' => 'Orçamento já aprovado']);
        }
        $orcamento->aprovado = true;
        $orcamento->save();
        return response()->json(['success' => true]);
    }

    public function unapprove(string $id)
    {
        $orcamento = Orcamento::findOrFail($id);
        if (!$orcamento->aprovado) {
            return response()->json(['success' => true, 'message' => 'Orçamento já está aguardando aprovação']);
        }
        $orcamento->aprovado = false;
        $orcamento->save();
        return response()->json(['success' => true]);
    }
}
