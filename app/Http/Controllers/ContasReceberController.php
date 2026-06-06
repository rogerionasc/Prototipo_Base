<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Pagamento;

class ContasReceberController extends Controller
{
    public function index()
    {
        $rows = DB::table('contas_receber as cr')
            ->leftJoin('faturamentos as f', 'f.id', '=', 'cr.faturamento_id')
            ->leftJoin('orcamentos as o', 'o.id', '=', 'f.orcamento_id')
            ->leftJoin('pacientes as p', 'p.id', '=', 'cr.paciente_id')
            ->leftJoin('convenios as c', 'c.id', '=', 'cr.convenio_id')
            ->select(
                'cr.faturamento_id as id',
                'cr.id as conta_id',
                'cr.faturamento_id',
                'cr.paciente_id',
                'cr.convenio_id',
                DB::raw("COALESCE(p.nome,'') AS paciente"),
                DB::raw("COALESCE(c.descricao,'') AS convenio"),
                DB::raw("COALESCE(o.numero,'') AS numero_orcamento"),
                DB::raw("DATE_FORMAT(cr.vencimento, '%d-%m-%Y') AS vencimento"),
                'cr.valor',
                'cr.status',
                'f.tipo_pagador',
                'f.status as faturamento_status'
            )
            ->whereNull('o.deleted_at')
            ->orderByDesc('cr.updated_at')
            ->orderByDesc('cr.id')
            ->limit(1000)
            ->get();

        return Inertia::render('Financeiro/ContasReceber', [
            'contas' => $rows,
        ]);
    }

    public function receiveConvenio(Request $request, string $id)
    {
        $data = $request->validate([
            'valor' => ['required', 'numeric', 'min:0'],
            'forma_pagamento' => ['nullable', 'string', 'max:50'],
            'data_pagamento' => ['nullable', 'date'],
        ]);

        $fatId = (int)$id;
        $fat = DB::table('faturamentos')
            ->select('id', 'tipo_pagador', 'status')
            ->where('id', $fatId)
            ->first();
        if (!$fat) {
            return back()->with('error', 'Faturamento não encontrado.');
        }
        if (strtoupper((string)$fat->tipo_pagador) !== 'CONVENIO') {
            return back()->with('error', 'Este recebimento é apenas para faturamento de CONVÊNIO.');
        }
        if (strtoupper((string)$fat->status) === 'CANCELADO') {
            return back()->with('error', 'Faturamento cancelado.');
        }

        $dt = !empty($data['data_pagamento']) ? $data['data_pagamento'] : now()->format('Y-m-d H:i:s');
        $fp = $data['forma_pagamento'] ?? 'TRANSFERENCIA';

        DB::transaction(function () use ($fatId, $data, $dt, $fp) {
            Pagamento::create([
                'faturamento_id' => $fatId,
                'caixa_id' => null,
                'movimentacao_id' => null,
                'valor' => (float)$data['valor'],
                'forma_pagamento' => $fp,
                'data_pagamento' => $dt,
                'status' => 'CONFIRMADO',
            ]);

            DB::table('faturamentos')->where('id', $fatId)->update([
                'status' => 'RECEBIDO',
                'updated_at' => now(),
            ]);

            DB::table('contas_receber')->where('faturamento_id', $fatId)->update([
                'status' => 'RECEBIDO',
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'Recebimento registrado no financeiro.');
    }
}
