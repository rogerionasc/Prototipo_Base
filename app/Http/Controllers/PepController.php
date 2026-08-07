<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Atendimento;
use App\Models\Cid;
use App\Models\Pep;
use App\Models\PepAnamnese;
use App\Models\PepEvolucao;
use App\Models\PepPrescricao;
use App\Models\PepPrescricaoItem;
use Illuminate\Support\Facades\DB;

class PepController extends Controller
{
    public function show(Atendimento $atendimento)
    {
        if (auth()->id() !== 1 && auth()->user()->pessoa_id != $atendimento->medico_id) {
            return redirect()->route('atendimentos.index')->with('error', 'Apenas o médico responsável pode acessar este prontuário.');
        }


        $atendimento->load(['paciente', 'medico', 'procedimento', 'agendamento.sessaoTratamento']);
        $paciente = $atendimento->paciente;
        $user = auth()->user();
        
        // Verifica se já existe um PEP para o atendimento. Se não, cria.
        $pep = Pep::firstOrCreate(
            ['atendimento_id' => $atendimento->id],
            [
                'paciente_id' => $paciente->id,
                'profissional_id' => $atendimento->medico_id ?? $user->pessoa_id,
                'status' => 'Aberto',
                'aberto_em' => now(),
                'created_by' => $user->id,
            ]
        );

        // Se o procedimento for um tratamento, auto-gera o plano caso não exista um em andamento
        if ($atendimento->procedimento && $atendimento->procedimento->eh_tratamento) {
            $tratamentoExistente = \App\Models\PepTratamento::where('paciente_id', $paciente->id)
                ->where('nome_tratamento', $atendimento->procedimento->nome)
                ->where('status', 'Em andamento')
                ->first();

            $podeCriar = false;

            if (!$tratamentoExistente) {
                $isSessao1 = true;
                if ($atendimento->agendamento && $atendimento->agendamento->sessaoTratamento) {
                    if ($atendimento->agendamento->sessaoTratamento->numero_sessao > 1) {
                        $isSessao1 = false;
                    }
                }

                if ($isSessao1) {
                    $jaCriouNestePep = \App\Models\PepTratamento::where('pep_id', $pep->id)
                        ->where('nome_tratamento', $atendimento->procedimento->nome)
                        ->exists();

                    if (!$jaCriouNestePep) {
                        $podeCriar = true;
                    }
                }
            }

            if ($podeCriar) {
                \App\Models\PepTratamento::create([
                    'pep_id' => $pep->id,
                    'paciente_id' => $paciente->id,
                    'profissional_id' => $atendimento->medico_id ?? $user->pessoa_id,
                    'nome_tratamento' => $atendimento->procedimento->nome,
                    'quantidade_sessoes_previstas' => $atendimento->procedimento->quantidade_sessoes > 0 ? $atendimento->procedimento->quantidade_sessoes : 1,
                    'quantidade_sessoes_realizadas' => 0,
                    'status' => 'Em andamento',
                    'data_inicio' => now(),
                    'observacao' => 'Plano de tratamento gerado automaticamente a partir do procedimento.'
                ]);
            }
        }

        $pep->load(['anamnese', 'sinaisVitais', 'evolucoes.profissional', 'prescricoes.itens', 'prescricoes.profissional', 'diagnosticos.profissional', 'diagnosticos.cid']);

        $tratamentos = \App\Models\PepTratamento::with(['profissional', 'evolucoes'])
            ->where('paciente_id', $paciente->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Carrega o histórico de PEPs do paciente (incluindo o atual caso já esteja finalizado/encerrado)
        $historico = Pep::with(['anamnese', 'sinaisVitais', 'evolucoes.profissional', 'prescricoes.itens', 'prescricoes.profissional', 'atendimento.medico', 'atendimento.procedimento', 'atendimento.agendamento.sessaoTratamento', 'diagnosticos.profissional', 'diagnosticos.cid'])
            ->where('paciente_id', $paciente->id)
            ->where(function($q) use ($pep) {
                if ($pep->status === 'Aberto') {
                    $q->where('id', '!=', $pep->id);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Verifica se o médico possui algum atendimento em andamento
        $hasAtendimentoEmAndamento = Atendimento::where('medico_id', $user->pessoa_id)
            ->where('status', 'EM ATENDIMENTO')
            ->exists();

        return Inertia::render('Consultorio/Pep/Show', [
            'atendimento' => $atendimento,
            'paciente' => $paciente,
            'pep' => $pep,
            'historico' => $historico,
            'tratamentos' => $tratamentos,
            'auth_profissional_id' => $user->pessoa_id,
            'has_atendimento_em_andamento' => $hasAtendimentoEmAndamento
        ]);
    }

    private function checkAtendimentoEmAndamento(Atendimento $atendimento)
    {
        if ($atendimento->status !== 'EM ATENDIMENTO') {
            abort(403, 'Só é possível inserir ou alterar informações no PEP após iniciar o atendimento.');
        }
    }

    public function saveAnamnese(Request $request, Atendimento $atendimento)
    {
        $this->checkAtendimentoEmAndamento($atendimento);

        $request->validate([
            'queixa_principal' => 'nullable|string',
            'historia_doenca_atual' => 'nullable|string',
            'antecedentes_pessoais' => 'nullable|string',
            'alergias' => 'nullable|string',
            'medicamentos_uso' => 'nullable|string',
        ]);

        $pep = Pep::where('atendimento_id', $atendimento->id)->firstOrFail();
        
        // Na anamnese, como ela pertence ao PEP, apenas o criador do PEP ou o profissional atual pode salvar
        if ($pep->profissional_id != auth()->user()->pessoa_id) {
            abort(403, 'Apenas o médico responsável por este atendimento pode alterar a anamnese.');
        }

        $anamnese = PepAnamnese::updateOrCreate(
            ['pep_id' => $pep->id],
            array_merge($request->only([
                'queixa_principal', 
                'historia_doenca_atual', 
                'antecedentes_pessoais', 
                'alergias', 
                'medicamentos_uso'
            ]), [
                'updated_by' => auth()->id(),
                'created_by' => auth()->id() // Ignorado no update
            ])
        );

        return redirect()->back()->with('success', 'Anamnese salva com sucesso.');
    }

    public function saveSinaisVitais(Request $request, Atendimento $atendimento)
    {
        $this->checkAtendimentoEmAndamento($atendimento);

        $validated = $request->validate([
            'pressao_sistolica' => 'nullable|string|max:20',
            'pressao_diastolica' => 'nullable|string|max:20',
            'frequencia_cardiaca' => 'nullable|string|max:20',
            'frequencia_respiratoria' => 'nullable|string|max:20',
            'temperatura' => 'nullable|string|max:20',
            'saturacao' => 'nullable|string|max:20',
            'peso' => 'nullable|numeric',
            'altura' => 'nullable|numeric',
            'imc' => 'nullable|numeric',
            'glicemia' => 'nullable|string|max:20',
            'circunferencia_abdominal' => 'nullable|string|max:20',
            'observacao' => 'nullable|string'
        ]);

        if (isset($validated['altura']) && $validated['altura'] > 3) {
            $validated['altura'] = round($validated['altura'] / 100, 2);
        }

        $pep = Pep::where('atendimento_id', $atendimento->id)->firstOrFail();

        \App\Models\PepSinaisVitais::updateOrCreate(
            ['pep_id' => $pep->id], // Atualiza a triagem existente do PEP ou cria uma nova
            array_merge($validated, [
                'profissional_id' => auth()->user()->pessoa_id
            ])
        );

        return redirect()->back()->with('success', 'Sinais Vitais salvos com sucesso.');
    }

    public function saveEvolucao(Request $request, Atendimento $atendimento)
    {
        $this->checkAtendimentoEmAndamento($atendimento);

        $request->validate([
            'descricao' => 'required|string',
            'tipo' => 'nullable|string',
            'tratamento_id' => 'nullable|exists:pep_tratamentos,id'
        ]);

        $pep = Pep::where('atendimento_id', $atendimento->id)->firstOrFail();
        
        $evolucao = PepEvolucao::create([
            'pep_id' => $pep->id,
            'profissional_id' => auth()->user()->pessoa_id,
            'tipo' => $request->tipo ?? 'Evolução Clínica',
            'descricao' => $request->descricao,
            'tratamento_id' => $request->tratamento_id
        ]);

        if ($request->tratamento_id) {
            $tratamento = \App\Models\PepTratamento::find($request->tratamento_id);
            if ($tratamento) {
                $tratamento->increment('quantidade_sessoes_realizadas');
                // Se atingiu o total, marcar como concluído
                if ($tratamento->quantidade_sessoes_realizadas >= $tratamento->quantidade_sessoes_previstas) {
                    $tratamento->update(['status' => 'Concluído']);
                }
            }
        }

        return redirect()->back()->with('success', 'Evolução adicionada com sucesso.');
    }

    public function deleteEvolucao(Atendimento $atendimento, PepEvolucao $evolucao)
    {
        $this->checkAtendimentoEmAndamento($atendimento);

        if ($evolucao->profissional_id != auth()->user()->pessoa_id) {
            abort(403, 'Você não pode excluir uma evolução criada por outro profissional.');
        }
        
        if ($evolucao->tratamento_id) {
            $tratamento = \App\Models\PepTratamento::find($evolucao->tratamento_id);
            if ($tratamento && $tratamento->quantidade_sessoes_realizadas > 0) {
                $tratamento->decrement('quantidade_sessoes_realizadas');
                if ($tratamento->status === 'Concluído' && $tratamento->quantidade_sessoes_realizadas < $tratamento->quantidade_sessoes_previstas) {
                    $tratamento->update(['status' => 'Em andamento']);
                }
            }
        }

        $evolucao->delete();
        return redirect()->back()->with('success', 'Evolução removida com sucesso.');
    }


    public function deleteTratamento(Atendimento $atendimento, \App\Models\PepTratamento $tratamento)
    {
        $this->checkAtendimentoEmAndamento($atendimento);

        if ($tratamento->profissional_id != auth()->user()->pessoa_id) {
            abort(403, 'Você não pode excluir um tratamento criado por outro profissional.');
        }

        $tratamento->delete();
        return redirect()->back()->with('success', 'Plano de Tratamento removido com sucesso.');
    }

    public function savePrescricao(Request $request, Atendimento $atendimento)
    {
        $this->checkAtendimentoEmAndamento($atendimento);

        $request->validate([
            'observacao' => 'nullable|string',
            'itens' => 'required|array|min:1',
            'itens.*.medicamento_id' => 'nullable|integer',
            'itens.*.medicamento_nome' => 'nullable|string', // Caso queira salvar só texto no observacao se nao tiver id
            'itens.*.dosagem' => 'required|string',
            'itens.*.frequencia' => 'required|string',
            'itens.*.via' => 'required|string',
            'itens.*.duracao' => 'nullable|string',
            'itens.*.quantidade' => 'nullable|integer'
        ]);

        $pep = Pep::where('atendimento_id', $atendimento->id)->firstOrFail();
        
        DB::transaction(function () use ($pep, $request) {
            $prescricao = PepPrescricao::create([
                'pep_id' => $pep->id,
                'profissional_id' => auth()->user()->pessoa_id,
                'observacao' => $request->observacao
            ]);

            foreach ($request->itens as $item) {
                PepPrescricaoItem::create([
                    'prescricao_id' => $prescricao->id,
                    'medicamento_id' => $item['medicamento_id'] ?? null,
                    'observacao' => $item['medicamento_nome'] ?? null, // Usaremos o campo de obs pra guardar o nome se for texto livre
                    'dosagem' => $item['dosagem'],
                    'frequencia' => $item['frequencia'],
                    'via' => $item['via'],
                    'duracao' => $item['duracao'],
                    'quantidade' => $item['quantidade'] ?? 1
                ]);
            }
        });

        return redirect()->back()->with('success', 'Prescrição adicionada com sucesso.');
    }

    public function deletePrescricao(Atendimento $atendimento, PepPrescricao $prescricao)
    {
        $this->checkAtendimentoEmAndamento($atendimento);

        if ($prescricao->profissional_id != auth()->user()->pessoa_id) {
            abort(403, 'Você não pode excluir uma prescrição criada por outro profissional.');
        }
        
        $prescricao->delete();
        return redirect()->back()->with('success', 'Prescrição removida com sucesso.');
    }

    public function storeDiagnostico(Request $request, Atendimento $atendimento)
    {
        $this->checkAtendimentoEmAndamento($atendimento);

        $request->validate([
            'cid_id' => 'nullable|integer|exists:cids,id',
            'descricao' => 'required|string',
            'principal' => 'boolean',
            'confirmado' => 'boolean',
        ]);

        $pep = Pep::where('atendimento_id', $atendimento->id)->firstOrFail();
        
        $pep->diagnosticos()->create([
            'cid_id' => $request->cid_id,
            'descricao' => $request->descricao,
            'principal' => $request->principal ?? false,
            'confirmado' => $request->confirmado ?? false,
            'profissional_id' => auth()->user()->pessoa_id,
        ]);

        return redirect()->back()->with('success', 'Diagnóstico adicionado com sucesso.');
    }

    public function searchCid(Request $request)
    {
        $query = $request->get('q');
        if (!$query) {
            return response()->json([]);
        }

        $cids = Cid::where('codigo', 'like', "%{$query}%")
            ->orWhere('descricao', 'like', "%{$query}%")
            ->limit(30)
            ->get()
            ->map(function($cid) {
                return [
                    'value' => $cid->id,
                    'label' => $cid->codigo . ' - ' . $cid->descricao
                ];
            });

        return response()->json($cids);
    }
}

