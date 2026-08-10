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
            ->leftJoin('pacientes as p', 'p.id', '=', 'cr.paciente_id')
            ->leftJoin('convenios as c', 'c.id', '=', 'cr.convenio_id')
            ->select(
                'cr.faturamento_id as id',
                'cr.id as conta_id',
                'cr.faturamento_id',
                'cr.paciente_id',
                'cr.convenio_id',
                DB::raw("COALESCE(p.nome,'') AS paciente"),
                DB::raw("COALESCE((SELECT proc.nome FROM pagamentos pag JOIN agendamentos a ON a.id = pag.agendamento_id JOIN procedimentos proc ON proc.id = a.procedimento_id WHERE pag.faturamento_id = cr.faturamento_id LIMIT 1), '') AS procedimento"),
                DB::raw("COALESCE(c.descricao,'') AS convenio"),
                DB::raw("IFNULL(c.tipo, 'PARTICULAR') AS tipo_convenio"),
                DB::raw("DATE_FORMAT(cr.vencimento, '%d-%m-%Y') AS vencimento"),
                'cr.valor',
                'cr.status',
                DB::raw("(SELECT p.nu_pagamento FROM pagamentos p WHERE p.faturamento_id = cr.faturamento_id ORDER BY p.id DESC LIMIT 1) as nu_pagamento"),
                DB::raw("(SELECT DATE_FORMAT(MAX(p.data_pagamento), '%d/%m/%Y %H:%i') FROM pagamentos p WHERE p.faturamento_id = cr.faturamento_id AND p.status = 'PAGO') as data_pagamento")
            )
            ->orderByDesc('cr.updated_at')
            ->orderByDesc('cr.id')
            ->limit(1000)
            ->get();

        return Inertia::render('Financeiro/ContasReceber/Index', [
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
        $fat = DB::table('faturamentos as f')
            ->leftJoin('convenios as c', 'c.id', '=', 'f.convenio_id')
            ->select('f.id', 'c.tipo as tipo_convenio', 'f.status', 'f.convenio_id')
            ->where('f.id', $fatId)
            ->first();
        if (!$fat) {
            return back()->with('error', 'Faturamento não encontrado.');
        }
        $tipo = $fat->convenio_id ? strtoupper((string)$fat->tipo_convenio) : 'PARTICULAR';
        if ($tipo !== 'CONVENIO') {
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
                'status' => 'PAGO',
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
