<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Atendimento;

class AtendimentoController extends Controller
{
    public function index()
    {
        $hoje = \Carbon\Carbon::today()->format('Y-m-d');

        $atendimentos = Atendimento::with(['paciente', 'medico', 'procedimento'])
            ->whereDate('data_atendimento', $hoje)
            ->orderByRaw("
                CASE status 
                    WHEN 'EM ATENDIMENTO' THEN 1 
                    WHEN 'CHAMADO' THEN 2 
                    WHEN 'AGUARDANDO' THEN 3 
                    ELSE 4 
                END ASC
            ")
            ->orderBy('created_at', 'asc')
            ->get();
            
        return Inertia::render('Consultorio/Atendimentos/Index', [
            'atendimentos' => $atendimentos
        ]);
    }

    public function chamar(Atendimento $atendimento)
    {
        // Verifica se o médico já possui um atendimento em andamento
        $emAndamento = Atendimento::where('medico_id', $atendimento->medico_id)
            ->where('status', 'EM ATENDIMENTO')
            ->exists();

        if ($emAndamento) {
            return redirect()->back()->with('error', 'O médico já possui um paciente em atendimento.');
        }

        // Altera o status para CHAMADO, caso ainda esteja AGUARDANDO
        if ($atendimento->status === 'AGUARDANDO') {
            $atendimento->update([
                'status' => 'CHAMADO'
            ]);
        }

        // Aqui também iria o código para disparar o evento no painel (broadcast)
        
        return redirect()->back()->with('success', 'Paciente chamado com sucesso!');
    }

    public function iniciar(Atendimento $atendimento)
    {
        // Verifica se o médico já possui um atendimento em andamento
        $emAndamento = Atendimento::where('medico_id', $atendimento->medico_id)
            ->where('status', 'EM ATENDIMENTO')
            ->exists();

        if ($emAndamento) {
            return redirect()->back()->with('error', 'O médico já possui um paciente em atendimento. Finalize-o antes de iniciar outro.');
        }

        $atendimento->update([
            'status' => 'EM ATENDIMENTO'
        ]);

        return redirect()->route('atendimentos.pep', $atendimento->id)->with('success', 'Atendimento iniciado.');
    }

    public function finalizar(Atendimento $atendimento)
    {
        // Verifica se o médico responsável é o mesmo que está finalizando
        // No momento assumimos que o médico pode finalizar seu próprio atendimento
        
        $atendimento->update([
            'status' => 'FINALIZADO'
        ]);

        // Também encerrar o PEP associado se existir
        $pep = \App\Models\Pep::where('atendimento_id', $atendimento->id)->where('status', 'Aberto')->first();
        if ($pep) {
            $pep->update([
                'status' => 'Encerrado',
                'encerrado_em' => now(),
            ]);
        }

        return redirect()->route('atendimentos.index')->with('success', 'Atendimento finalizado com sucesso.');
    }
}
