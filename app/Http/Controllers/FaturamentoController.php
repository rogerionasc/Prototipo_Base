<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FaturamentoController extends Controller
{
    public function particular()
    {
        $rows = DB::table('faturamentos as f')
            ->leftJoin('agendamentos as a', 'a.id', '=', 'f.agendamento_id')
            ->leftJoin('pacientes as p', 'p.id', '=', 'f.paciente_id')
            ->select(
                'f.id',
                'f.paciente_id',
                'f.agendamento_id',
                DB::raw("COALESCE(p.nome,'') AS paciente"),
                DB::raw("COALESCE(p.cpf,'') AS paciente_documento"),
                DB::raw("DATE_FORMAT(f.data_faturamento, '%d-%m-%Y %H:%i') AS data_faturamento"),
                DB::raw("DATE_FORMAT(f.vencimento, '%d-%m-%Y') AS vencimento"),
                'f.valor_total',
                'f.valor_final',
                'f.status'
            )
            ->leftJoin('convenios as c', 'c.id', '=', 'f.convenio_id')
            ->where(function($q) {
                $q->where('c.tipo', 'Particular')
                  ->orWhereNull('f.convenio_id');
            })
            ->orderByDesc('f.updated_at')
            ->orderByDesc('f.id')
            ->limit(500)
            ->get();

        return Inertia::render('Faturamento/Particular', [
            'faturamentos' => $rows,
        ]);
    }

    public function convenios()
    {
        $rows = DB::table('faturamentos as f')
            ->leftJoin('agendamentos as a', 'a.id', '=', 'f.agendamento_id')
            ->leftJoin('pacientes as p', 'p.id', '=', 'f.paciente_id')
            ->leftJoin('convenios as c', 'c.id', '=', 'f.convenio_id')
            ->select(
                'f.id',
                'f.paciente_id',
                'f.agendamento_id',
                'f.convenio_id',
                DB::raw("COALESCE(c.descricao,'') AS convenio"),
                DB::raw("COALESCE(p.nome,'') AS paciente"),
                DB::raw("DATE_FORMAT(f.data_faturamento, '%d-%m-%Y %H:%i') AS data_faturamento"),
                DB::raw("DATE_FORMAT(f.vencimento, '%d-%m-%Y') AS vencimento"),
                'f.valor_cobrado',
                'f.valor_aprovado',
                'f.valor_glosado',
                'f.status'
            )
            ->where('c.tipo', 'CONVENIO')
            ->orderByDesc('f.updated_at')
            ->orderByDesc('f.id')
            ->limit(500)
            ->get();

        return Inertia::render('Faturamento/Convenios', [
            'faturamentos' => $rows,
        ]);
    }

    public function updateConvenio(Request $request, string $id)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:AGUARDANDO_ENVIO,ENVIADO,EM_ANALISE,APROVADO,GLOSADO,RECEBIDO,CANCELADO'],
            'valor_cobrado' => ['nullable', 'numeric', 'min:0'],
            'valor_aprovado' => ['nullable', 'numeric', 'min:0'],
            'valor_glosado' => ['nullable', 'numeric', 'min:0'],
            'vencimento' => ['nullable', 'date'],
        ]);

        $fatId = (int)$id;
        $fat = DB::table('faturamentos as f')
            ->leftJoin('convenios as c', 'c.id', '=', 'f.convenio_id')
            ->select('f.id', 'c.tipo')
            ->where('f.id', $fatId)
            ->first();
        if (!$fat) {
            return back()->with('error', 'Faturamento não encontrado.');
        }
        if (strtoupper((string)$fat->tipo) !== 'CONVENIO') {
            return back()->with('error', 'Faturamento não é do tipo CONVÊNIO.');
        }

        $vc = array_key_exists('valor_cobrado', $data) ? (float)($data['valor_cobrado'] ?? 0) : null;
        $va = array_key_exists('valor_aprovado', $data) ? (float)($data['valor_aprovado'] ?? 0) : null;
        $vg = array_key_exists('valor_glosado', $data) ? (float)($data['valor_glosado'] ?? 0) : null;

        if ($vg === null && $vc !== null && $va !== null) {
            $vg = max(0, $vc - $va);
        }

        DB::transaction(function () use ($fatId, $data, $vc, $va, $vg) {
            $upd = [
                'status' => $data['status'],
                'updated_at' => now(),
            ];
            if ($vc !== null) $upd['valor_cobrado'] = $vc;
            if ($va !== null) $upd['valor_aprovado'] = $va;
            if ($vg !== null) $upd['valor_glosado'] = $vg;
            if (!empty($data['vencimento'])) $upd['vencimento'] = $data['vencimento'];

            DB::table('faturamentos')->where('id', $fatId)->update($upd);

            $crValor = null;
            if ($va !== null && $va > 0) $crValor = $va;
            elseif ($vc !== null && $vc > 0) $crValor = $vc;

            if ($crValor !== null) {
                DB::table('contas_receber')->where('faturamento_id', $fatId)->update([
                    'valor' => (float)$crValor,
                    'updated_at' => now(),
                ]);
            }

            if ($data['status'] === 'RECEBIDO') {
                DB::table('contas_receber')->where('faturamento_id', $fatId)->update([
                    'status' => 'RECEBIDO',
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Faturamento atualizado.');
    }
    public function detalhes(string $id)
    {
        $faturamento = DB::table('faturamentos as f')
            ->leftJoin('pacientes as p', 'p.id', '=', 'f.paciente_id')
            ->select(
                'f.id',
                'f.valor_total',
                'f.agendamento_id',
                DB::raw("DATE_FORMAT(f.data_faturamento, '%d/%m/%Y') as data_emissao"),
                DB::raw("DATE_FORMAT(f.vencimento, '%d/%m/%Y') as validade"),
                'f.id as numero',
                DB::raw("COALESCE(p.nome,'') AS paciente_nome"),
                DB::raw("COALESCE(p.cpf,'') AS paciente_cpf")
            )
            ->where('f.id', $id)
            ->first();

        if (!$faturamento) {
            abort(404);
        }

        $agendamentos = [];
        if ($faturamento->agendamento_id) {
            $agendamentos = DB::table('agendamentos as a')
                ->leftJoin('pacientes as p', 'p.id', '=', 'a.paciente_id')
                ->leftJoin('procedimentos as pr', 'pr.id', '=', 'a.procedimento_id')
                ->leftJoin('agenda_medica as am', 'am.id', '=', 'a.agenda_medica_id')
                ->leftJoin('pessoas as doc', 'doc.id', '=', 'am.pessoa_id')
                ->leftJoin('status_agendamento as st', 'st.id', '=', 'a.status_id')
                ->select(
                    'a.id',
                    DB::raw("DATE_FORMAT(a.data, '%d/%m/%Y') AS data"),
                    DB::raw("TIME_FORMAT(a.hora, '%H:%i') AS hora"),
                    'a.valor_cobrado',
                    'p.nome as paciente_nome',
                    'pr.nome as procedimento_nome',
                    'doc.nome as medico_nome',
                    'st.descricao as status_nome'
                )
                ->where('a.id', $faturamento->agendamento_id)
                ->whereNull('a.deleted_at')
                ->get();
        }

        return response()->json([
            'faturamento' => $faturamento,
            'agendamentos' => $agendamentos,
        ]);
    }
}
