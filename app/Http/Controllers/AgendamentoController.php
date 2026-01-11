<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Agendamento;
use App\Models\Paciente;
use App\Models\ProfissionalSaude;
use App\Models\Procedimento;
use App\Models\AgendaMedica;
use App\Models\StatusAgendamento;

class AgendamentoController extends Controller
{
    public function index()
    {
        $profissionais = ProfissionalSaude::select('id','nome')->orderBy('nome')->get();
        $procedimentos = Procedimento::select('id','nome','valor')->orderBy('nome')->get();
        $status = StatusAgendamento::select('id','descricao')->orderBy('descricao')->get();
        $weekday = Carbon::now()->dayOfWeek;
        $agendasHoje = DB::table('agenda_medica as a')
            ->join('profissionais_saude as ps', 'ps.id', '=', 'a.profissional_saude_id')
            ->leftJoin('profissional_especialidade as pe', 'pe.profissional_saude_id', '=', 'ps.id')
            ->leftJoin('especialidades as e', 'e.id', '=', 'pe.especialidade_id')
            ->select(
                'a.profissional_saude_id',
                DB::raw("COALESCE(ps.nome,'') AS nome"),
                'a.hora_inicio',
                'a.hora_fim',
                DB::raw("GROUP_CONCAT(DISTINCT e.nome ORDER BY e.nome SEPARATOR ', ') AS especialidades")
            )
            ->where('a.dia_semana', $weekday)
            ->groupBy('a.profissional_saude_id','ps.nome','a.hora_inicio','a.hora_fim')
            ->orderBy('ps.nome')
            ->get();
        return Inertia::render('Atendimento/Agendamentos/Index', [
            'profissionais' => $profissionais,
            'procedimentos' => $procedimentos,
            'status' => $status,
            'agendasHoje' => $agendasHoje,
        ]);
    }

    public function agendasByDate(Request $request)
    {
        $data = $request->validate([
            'data' => ['required','date_format:Y-m-d'],
        ]);
        $weekday = Carbon::createFromFormat('Y-m-d', $data['data'])->dayOfWeek;
        $agendas = DB::table('agenda_medica as a')
            ->join('profissionais_saude as ps', 'ps.id', '=', 'a.profissional_saude_id')
            ->leftJoin('profissional_especialidade as pe', 'pe.profissional_saude_id', '=', 'ps.id')
            ->leftJoin('especialidades as e', 'e.id', '=', 'pe.especialidade_id')
            ->select(
                'a.profissional_saude_id',
                DB::raw("COALESCE(ps.nome,'') AS nome"),
                DB::raw("MIN(a.hora_inicio) AS hora_inicio"),
                DB::raw("MAX(a.hora_fim) AS hora_fim"),
                DB::raw("GROUP_CONCAT(DISTINCT e.nome ORDER BY e.nome SEPARATOR ', ') AS especialidades")
            )
            ->where('a.dia_semana', $weekday)
            ->groupBy('a.profissional_saude_id','ps.nome')
            ->orderBy('ps.nome')
            ->get();
        return response()->json([
            'agendas' => $agendas,
        ]);
    }

    public function countsByWeekday()
    {
        $rows = DB::table('agenda_medica')
            ->select('dia_semana', DB::raw('COUNT(DISTINCT profissional_saude_id) AS cnt'))
            ->groupBy('dia_semana')
            ->get();
        $counts = [];
        foreach ($rows as $r) {
            $counts[(int)$r->dia_semana] = (int)$r->cnt;
        }
        return response()->json([
            'counts' => $counts,
        ]);
    }

    public function weekdayByDoctors(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required','array','min:1'],
            'ids.*' => ['integer','exists:profissionais_saude,id'],
        ]);
        $rows = DB::table('agenda_medica')
            ->select('dia_semana', DB::raw('GROUP_CONCAT(DISTINCT profissional_saude_id) AS prof_ids'))
            ->whereIn('profissional_saude_id', $data['ids'])
            ->groupBy('dia_semana')
            ->get();
        $map = [];
        foreach ($rows as $r) {
            $list = array_values(array_filter(array_map('intval', explode(',', (string)$r->prof_ids))));
            $map[(int)$r->dia_semana] = $list;
        }
        return response()->json([
            'weekday_map' => $map,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'paciente_id' => ['required','integer','exists:pacientes,id'],
            'profissional_saude_id' => ['required','integer','exists:profissionais_saude,id'],
            'procedimento_id' => ['required','integer','exists:procedimentos,id'],
            'data' => ['required','date_format:Y-m-d'],
            'hora' => ['required','regex:/^\d{2}:\d{2}$/'],
            'status_id' => ['nullable','integer','exists:status_agendamento,id'],
            'valor_cobrado' => ['nullable','numeric','min:0'],
            'observacoes' => ['nullable','string'],
            'orcamento_id' => ['nullable','integer','exists:orcamentos,id'],
        ]);

        $dt = Carbon::createFromFormat('Y-m-d', $data['data'])->startOfDay();
        $weekday = $dt->dayOfWeek;
        $agenda = AgendaMedica::where('profissional_saude_id', (int)$data['profissional_saude_id'])
            ->where('dia_semana', $weekday)
            ->first();
        if (!$agenda) {
            return response()->json([
                'errors' => [
                    'agenda' => ['Profissional não possui agenda para o dia selecionado.']
                ]
            ], 422);
        }
        $hora = $data['hora'];
        $hi = substr((string)$agenda->hora_inicio, 0, 5);
        $hf = substr((string)$agenda->hora_fim, 0, 5);
        if (strtotime($hora) < strtotime($hi) || strtotime($hora) > strtotime($hf)) {
            return response()->json([
                'errors' => [
                    'hora' => ['Horário fora do intervalo da agenda médica.']
                ]
            ], 422);
        }

        $hasPaidBudget = null;
        if (!empty($data['orcamento_id'])) {
            $hasPaidBudget = DB::table('orcamentos as o')
                ->join('pagamentos as p', 'p.orcamento_id', '=', 'o.id')
                ->join('orcamento_procedimentos as op', 'op.orcamento_id', '=', 'o.id')
                ->where('o.id', (int)$data['orcamento_id'])
                ->where('o.paciente_id', (int)$data['paciente_id'])
                ->where('o.aprovado', true)
                ->where('p.confirmado', true)
                ->whereNull('o.deleted_at')
                ->whereNull('op.deleted_at')
                ->where('op.procedimento_id', (int)$data['procedimento_id'])
                ->exists();
        } else {
            $hasPaidBudget = DB::table('orcamentos as o')
                ->join('pagamentos as p', 'p.orcamento_id', '=', 'o.id')
                ->join('orcamento_procedimentos as op', 'op.orcamento_id', '=', 'o.id')
                ->where('o.paciente_id', (int)$data['paciente_id'])
                ->where('o.aprovado', true)
                ->where('p.confirmado', true)
                ->whereNull('o.deleted_at')
                ->whereNull('op.deleted_at')
                ->where('op.procedimento_id', (int)$data['procedimento_id'])
                ->exists();
        }
        if (!$hasPaidBudget) {
            return response()->json([
                'errors' => [
                    'orcamento' => ['Paciente não possui orçamento aprovado e pago para o procedimento selecionado.']
                ]
            ], 422);
        }

        if (!empty($data['orcamento_id'])) {
            $qtyRow = DB::table('orcamento_procedimentos as op')
                ->where('op.orcamento_id', (int)$data['orcamento_id'])
                ->where('op.procedimento_id', (int)$data['procedimento_id'])
                ->whereNull('op.deleted_at')
                ->select('op.quantidade')
                ->first();
            $allowedQty = max(1, (int)($qtyRow->quantidade ?? 1));
            $scheduledCount = DB::table('agendamentos as a')
                ->leftJoin('status_agendamento as s', 's.id', '=', 'a.status_id')
                ->whereNull('a.deleted_at')
                ->where('a.orcamento_id', (int)$data['orcamento_id'])
                ->where('a.procedimento_id', (int)$data['procedimento_id'])
                ->where(function ($q) {
                    $q->whereNull('s.id')
                      ->orWhereRaw("LOWER(s.descricao) NOT LIKE '%cancel%'");
                })
                ->count();
            if ($scheduledCount >= $allowedQty) {
                return response()->json([
                    'errors' => [
                        'duplicidade' => ['Limite de agendamentos atingido para este orçamento e procedimento.']
                    ]
                ], 422);
            }
        }

        $ag = Agendamento::create([
            'agenda_medica_id' => $agenda->id,
            'data' => $dt->toDateString(),
            'hora' => $hora,
            'paciente_id' => (int)$data['paciente_id'],
            'procedimento_id' => (int)$data['procedimento_id'],
            'orcamento_id' => $data['orcamento_id'] ?? null,
            'status_id' => $data['status_id'] ?? null,
            'agendamento_origem_id' => null,
            'valor_cobrado' => $data['valor_cobrado'] ?? null,
            'observacoes' => $data['observacoes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'agendamento' => $ag,
        ]);
    }

    public function latest(Request $request)
    {
        $limit = (int)($request->query('limit', 20));
        $rows = DB::table('agendamentos as a')
            ->leftJoin('pacientes as p', 'p.id', '=', 'a.paciente_id')
            ->leftJoin('procedimentos as pr', 'pr.id', '=', 'a.procedimento_id')
            ->leftJoin('status_agendamento as s', 's.id', '=', 'a.status_id')
            ->select(
                'a.id',
                'a.data',
                'a.hora',
                'a.paciente_id',
                'a.procedimento_id',
                DB::raw("COALESCE(p.nome,'') AS paciente"),
                DB::raw("COALESCE(pr.nome,'') AS procedimento"),
                DB::raw("COALESCE(s.descricao,'') AS status"),
                'a.observacoes',
                DB::raw("DATE_FORMAT(a.created_at, '%d/%m %H:%i') AS criado_em")
            )
            ->orderByDesc('a.created_at')
            ->orderByDesc('a.id')
            ->limit($limit)
            ->get();
        return response()->json([
            'agendamentos' => $rows,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $agendamento = Agendamento::findOrFail($id);
        $data = $request->validate([
            'paciente_id' => ['nullable','integer','exists:pacientes,id'],
            'profissional_saude_id' => ['nullable','integer','exists:profissionais_saude,id'],
            'procedimento_id' => ['nullable','integer','exists:procedimentos,id'],
            'data' => ['nullable','date_format:Y-m-d'],
            'hora' => ['nullable','regex:/^\d{2}:\d{2}$/'],
            'status_id' => ['nullable','integer','exists:status_agendamento,id'],
            'valor_cobrado' => ['nullable','numeric','min:0'],
            'observacoes' => ['nullable','string'],
        ]);
        $payload = [];
        foreach (['paciente_id','profissional_saude_id','procedimento_id','data','hora','status_id','valor_cobrado','observacoes'] as $k) {
            if (array_key_exists($k, $data) && $data[$k] !== null) {
                $payload[$k] = $data[$k];
            }
        }
        if (!empty($agendamento->orcamento_id)) {
            $procCheckId = array_key_exists('procedimento_id', $payload) && $payload['procedimento_id'] !== null
                ? (int)$payload['procedimento_id']
                : (int)$agendamento->procedimento_id;
            if ($procCheckId) {
                $qtyRow = DB::table('orcamento_procedimentos as op')
                    ->where('op.orcamento_id', (int)$agendamento->orcamento_id)
                    ->where('op.procedimento_id', $procCheckId)
                    ->whereNull('op.deleted_at')
                    ->select('op.quantidade')
                    ->first();
                $allowedQty = max(1, (int)($qtyRow->quantidade ?? 1));
                $scheduledCount = DB::table('agendamentos as a')
                    ->leftJoin('status_agendamento as s', 's.id', '=', 'a.status_id')
                    ->whereNull('a.deleted_at')
                    ->where('a.orcamento_id', (int)$agendamento->orcamento_id)
                    ->where('a.procedimento_id', $procCheckId)
                    ->where('a.id', '<>', (int)$agendamento->id)
                    ->where(function ($q) {
                        $q->whereNull('s.id')
                          ->orWhereRaw("LOWER(s.descricao) NOT LIKE '%cancel%'");
                    })
                    ->count();
                if ($scheduledCount >= $allowedQty) {
                    return response()->json([
                        'errors' => [
                            'duplicidade' => ['Limite de agendamentos atingido para este orçamento e procedimento.']
                        ]
                    ], 422);
                }
            }
        }
        if (!empty($payload)) {
            $agendamento->update($payload);
        }
        return response()->json([
            'success' => true,
            'agendamento' => $agendamento->fresh(),
        ]);
    }

    public function cancel(Request $request, string $id)
    {
        $agendamento = Agendamento::findOrFail($id);
        $status = StatusAgendamento::where(DB::raw('LOWER(descricao)'), 'like', '%cancel%')->first();
        if (!$status) {
            $status = StatusAgendamento::create(['descricao' => 'Cancelado']);
        }
        $sid = $status->id;
        $obs = $request->input('observacoes');
        $agendamento->update([
            'status_id' => $sid,
            'observacoes' => $obs ?? $agendamento->observacoes,
        ]);
        return response()->json([
            'success' => true,
        ]);
    }
}
