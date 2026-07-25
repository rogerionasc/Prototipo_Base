<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agendamento;
use App\Models\Atendimento;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecepcaoFilaController extends Controller
{
    public function index()
    {
        $hoje = Carbon::today()->format('Y-m-d');
        
        $agendamentos = Agendamento::with([
            'paciente',
            'agendaMedica.profissionalSaude',
            'procedimento',
            'status',
            'atendimentos' // Para checar se já virou atendimento
        ])
        ->where('data', $hoje)
        ->whereExists(function ($query) {
            $query->select(DB::raw(1))
                  ->from('faturamentos')
                  ->whereColumn('faturamentos.orcamento_id', 'agendamentos.orcamento_id')
                  ->whereIn('faturamentos.status', ['PAGO', 'RECEBIDO']);
        })
        ->orderBy('hora', 'asc')
        ->get()
        ->map(function($ag) {
            // Se já tem atendimento hoje para este agendamento, consideramos que já chegou
            $atendimento = $ag->atendimentos->first();
            $jaChegou = $atendimento ? true : false;
            
            return [
                'id' => $ag->id,
                'hora' => date('H:i', strtotime($ag->hora)),
                'paciente' => $ag->paciente ? $ag->paciente->nome : 'N/A',
                'cpf' => $ag->paciente ? $ag->paciente->cpf : null,
                'procedimento' => $ag->procedimento ? $ag->procedimento->nome : 'N/A',
                'medico' => $ag->agendaMedica && $ag->agendaMedica->profissionalSaude 
                            ? $ag->agendaMedica->profissionalSaude->nome 
                            : 'N/A',
                'medico_id' => $ag->agendaMedica ? $ag->agendaMedica->profissional_saude_id : null,
                'status' => $atendimento ? $atendimento->status : ($ag->status ? $ag->status->nome : 'AGENDADO'),
                'ja_chegou' => $jaChegou,
            ];
        });

        return Inertia::render('Recepcao/Fila/Index', [
            'fila' => $agendamentos
        ]);
    }

    public function confirmar(Request $request, $id)
    {
        $agendamento = Agendamento::with('agendaMedica')->findOrFail($id);

        $existe = Atendimento::where('agendamento_id', $id)->exists();

        if (!$existe) {
            $catId = $agendamento->procedimento ? $agendamento->procedimento->categoria_id : 1; // Default fallback

            Atendimento::create([
                'paciente_id' => $agendamento->paciente_id,
                'medico_id' => $agendamento->agendaMedica->profissional_saude_id ?? null,
                'agendamento_id' => $agendamento->id,
                'procedimento_id' => $agendamento->procedimento_id ?? $agendamento->tuss_id,
                'categoria_procedimento_id' => $catId ?: 1,
                'data_atendimento' => Carbon::today()->format('Y-m-d'),
                'hora_prevista' => Carbon::today()->format('Y-m-d') . ' ' . $agendamento->hora,
                'status' => 'AGUARDANDO',
            ]);
        }

        return redirect()->back()->with('success', 'Presença confirmada. Paciente adicionado ao Pronto Atendimento.');
    }

    public function cancelar(Request $request, $id)
    {
        $atendimento = Atendimento::where('agendamento_id', $id)->first();

        if ($atendimento) {
            if ($atendimento->status !== 'AGUARDANDO') {
                return redirect()->back()->with('error', 'Não é possível cancelar a presença de um paciente que já foi chamado pelo médico.');
            }
            // Deleta fisicamente para sumir da fila do médico e permitir confirmar novamente depois
            $atendimento->forceDelete();
        }

        return redirect()->back()->with('success', 'Presença cancelada com sucesso.');
    }
}
