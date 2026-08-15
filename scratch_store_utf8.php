    public function store(Request $request)
    {
        $convenioIdInput = $request->input('convenio_id');
        $isConvenio = false;
        if (!empty($convenioIdInput)) {
            $conv = Convenio::select('tipo')->find($convenioIdInput);
            if ($conv && strtoupper((string)$conv->tipo) !== 'PARTICULAR') {
                $isConvenio = true;
            }
        }

        $procRule = $isConvenio ? ['required', 'integer', 'exists:tuss,id'] : ['required', 'integer', 'exists:procedimentos,id'];

        \Illuminate\Support\Facades\Log::info('Agendamento Store:', [
            'convenio_id' => $convenioIdInput,
            'isConvenio' => $isConvenio,
            'procRule' => $procRule,
            'payload' => $request->all(),
        ]);

        $data = $request->validate([
            'paciente_id' => ['required','integer','exists:pacientes,id'],
            'pessoa_id' => ['required','integer','exists:pessoas,id'],
            'procedimento_id' => $procRule,
            'data' => ['required','date_format:Y-m-d'],
            'hora' => ['required','regex:/^\d{2}:\d{2}$/'],
            'status_id' => ['nullable','integer','exists:status_agendamento,id'],
            'valor_cobrado' => ['nullable','numeric','min:0'],
            'observacoes' => ['nullable','string'],
            'convenio_id' => ['nullable','integer','exists:convenios,id'],
            'is_retorno' => ['nullable','boolean'],
            'numero_autorizacao' => ['nullable','string'],
            'validade_autorizacao' => ['nullable','date'],
        ]);

        $isRetorno = $request->input('is_retorno', false);

        $dt = Carbon::createFromFormat('Y-m-d', $data['data'])->startOfDay();
        if ($dt->lessThan(Carbon::today())) {
            return response()->json([
                'errors' => [
                    'data' => ['NÃ£o Ã© permitido agendar em data passada.']
                ]
            ], 422);
        }
        $weekday = $dt->dayOfWeek;
        $agenda = AgendaMedica::where('pessoa_id', (int)$data['pessoa_id'])
            ->where('dia_semana', $weekday)
            ->first();
        if (!$agenda) {
            return response()->json([
                'errors' => [
                    'agenda' => ['Profissional nÃ£o possui agenda para o dia selecionado.']
                ]
            ], 422);
        }
        $hora = $data['hora'];
        $hi = substr((string)$agenda->hora_inicio, 0, 5);
        $hf = substr((string)$agenda->hora_fim, 0, 5);
        if (strtotime($hora) < strtotime($hi) || strtotime($hora) > strtotime($hf)) {
            return response()->json([
                'errors' => [
                    'hora' => ['HorÃ¡rio fora do intervalo da agenda mÃ©dica.']
                ]
            ], 422);
        }

        $agendamentoOrigemId = null;
        if ($isRetorno) {
            $diasRetorno = 0;
            if (!empty($convenioIdInput)) {
                $conv = Convenio::find($convenioIdInput);
                if ($conv) $diasRetorno = (int) $conv->dias_retorno;
            }

            $lastAgendamento = Agendamento::where('paciente_id', (int)$data['paciente_id'])
                ->whereHas('agendaMedica', function($q) use ($data) {
                    $q->where('pessoa_id', (int)$data['pessoa_id']);
                })
                ->where(function($q) use ($isConvenio, $data) {
                    if ($isConvenio) {
                        $q->where('tuss_id', (int)$data['procedimento_id']);
                    } else {
                        $q->where('procedimento_id', (int)$data['procedimento_id']);
                    }
                })
                ->whereNull('agendamento_origem_id')
                ->where(function($q) {
                    $q->whereNull('status_id')
                      ->orWhereHas('status', function($sub) {
                          $sub->whereRaw("LOWER(descricao) NOT LIKE '%cancel%'");
                      });
                })
                ->orderBy('data', 'desc')
                ->first();

            if (!$lastAgendamento) {
                return response()->json([
                    'errors' => [
                        'is_retorno' => ['Nenhum atendimento anterior elegÃ­vel para retorno foi encontrado para este procedimento e profissional.']
                    ]
                ], 422);
            }

            $dataUltimo = Carbon::parse($lastAgendamento->data)->startOfDay();
            if ($dt->diffInDays($dataUltimo) > $diasRetorno) {
                return response()->json([
                    'errors' => [
                        'is_retorno' => ["Prazo para retorno excedido (limite de $diasRetorno dias do convÃªnio)."]
                    ]
                ], 422);
            }

            $hasReturn = Agendamento::where('agendamento_origem_id', $lastAgendamento->id)
                ->where(function($q) {
                    $q->whereNull('status_id')
                      ->orWhereHas('status', function($sub) {
                          $sub->whereRaw("LOWER(descricao) NOT LIKE '%cancel%'");
                      });
                })
                ->exists();

            if ($hasReturn) {
                return response()->json([
                    'errors' => [
                        'is_retorno' => ['Este atendimento jÃ¡ consumiu o seu direito de 1 retorno.']
                    ]
                ], 422);
            }

            $agendamentoOrigemId = $lastAgendamento->id;
        }

        $ag = DB::transaction(function () use ($data, $agenda, $dt, $hora, $isConvenio, $isRetorno, $agendamentoOrigemId) {
            $procId = (int)$data['procedimento_id'];
            $pacId = (int)$data['paciente_id'];
            $valorCobrado = $data['valor_cobrado'] ?? null;
            $convenioId = $data['convenio_id'] ?? null;

            if ($isConvenio) {
                $proc = DB::table('tuss')->select('id', 'total as valor', 'eh_tratamento', 'quantidade_sessoes')->where('id', $procId)->first();
            } else {
                $proc = Procedimento::select('id','valor', 'eh_tratamento', 'quantidade_sessoes')->findOrFail($procId);
            }

            if ($valorCobrado === null) {
                $valorCobrado = (float)($proc->valor ?? 0);
            }

            $sessaoId = null;
            if ($proc && (bool)$proc->eh_tratamento) {
                $lastNumQuery = DB::table('sessoes_tratamento')->where('paciente_id', $pacId);

                if ($isConvenio) {
                    $lastNumQuery->where('tuss_id', $procId);
                } else {
                    $lastNumQuery->where('procedimento_id', $procId);
                }

                $lastNum = $lastNumQuery->max('numero_sessao');
                $nextNum = (int)($lastNum ?? 0) + 1;
                $sessaoId = DB::table('sessoes_tratamento')->insertGetId([
                    'procedimento_id' => $isConvenio ? null : $procId,
                    'tuss_id' => $isConvenio ? $procId : null,
                    'paciente_id' => $pacId,
                    'numero_sessao' => $nextNum,
                    'data_prevista' => $dt->toDateString(),
                    'realizada' => false,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'deleted_at' => null,
                ]);
            }

            $agendamentoStatusId = $data['status_id'] ?? null;
            if (!$agendamentoStatusId) {
                $statusAgendado = \App\Models\StatusAgendamento::firstOrCreate(['descricao' => 'Agendado']);
                $agendamentoStatusId = $statusAgendado->id;
            }

            $agendamento = Agendamento::create([
                'agenda_medica_id' => $agenda->id,
                'data' => $dt->toDateString(),
                'hora' => $hora,
                'paciente_id' => $pacId,
                'procedimento_id' => $isConvenio ? null : $procId,
                'tuss_id' => $isConvenio ? $procId : null,
                'sessao_tratamento_id' => $sessaoId,
                'status_id' => $agendamentoStatusId,
                'agendamento_origem_id' => $agendamentoOrigemId,
                'valor_cobrado' => $valorCobrado,
                'observacoes' => $data['observacoes'] ?? null,
                'convenio_id' => $convenioId,
            ]);

            // Se for agendamento de convÃªnio, verificar fluxo de autorizaÃ§Ã£o vs atendimento
            if ($isConvenio && $convenioId) {
                $convenioTuss = DB::table('convenio_tuss')
                    ->where('convenio_id', $convenioId)
                    ->where('tuss_id', $procId)
                    ->whereNull('deleted_at')
                    ->first();

                $requerAutorizacao = $convenioTuss && $convenioTuss->requer_autorizacao;

                $pacienteConvenio = DB::table('paciente_convenio')
                    ->where('paciente_id', $pacId)
                    ->where('convenio_id', $convenioId)
                    ->where('ativo', 1)
                    ->whereNull('deleted_at')
                    ->first();

                Autorizacao::create([
                    'convenio_id' => $convenioId,
                    'agendamento_id' => $agendamento->id,
                    'tuss_id' => $isConvenio ? $procId : null,
                    'valor' => $valorCobrado,
                    'carteira' => $pacienteConvenio->numero_carteira ?? null,
                    'numero_autorizacao' => $data['numero_autorizacao'] ?? null,
                    'status' => $requerAutorizacao ? 'SOLICITADA' : 'AUTORIZADA',
                    'validade' => $data['validade_autorizacao'] ?? null,
                    'data_solicitacao' => Carbon::now(),
                    'data_resposta' => $requerAutorizacao ? null : Carbon::now(),
                    'observacao' => 'Gerado automaticamente pelo agendamento #' . $agendamento->id,
                    'usuario_id' => Auth::id() ?? 1,
                    'usuario_id_validou' => null,
                ]);
            }

            // Gerar faturamento + pagamento PENDENTE automaticamente para particulares
            if (!$isConvenio) {
                $fatId = (int)DB::table('faturamentos')->insertGetId([
                    'paciente_id'      => $pacId,
                    'agendamento_id'   => $agendamento->id,
                    'valor_final'      => (float)$valorCobrado,
                    'convenio_id'      => $convenioId,
                    'valor_total'      => (float)$valorCobrado,
                    'valor_cobrado'    => (float)$valorCobrado,
                    'valor_aprovado'   => 0,
                    'valor_glosado'    => 0,
                    'status'           => 'AGUARDANDO_PAGAMENTO',
                    'data_faturamento' => Carbon::now()->format('Y-m-d H:i:s'),
                    'vencimento'       => Carbon::today()->toDateString(),
                    'created_at'       => Carbon::now(),
                    'updated_at'       => Carbon::now(),
                ]);

                DB::table('contas_receber')->insert([
                    'faturamento_id' => $fatId,
                    'paciente_id' => $pacId,
                    'convenio_id' => $convenioId,
                    'valor' => (float)$valorCobrado,
                    'vencimento' => Carbon::today()->toDateString(),
                    'status' => 'ABERTO',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                Pagamento::create([
                    'faturamento_id'  => $fatId,
                    'caixa_id'        => null,
                    'movimentacao_id' => null,
                    'valor'           => (float)$valorCobrado,
                    'forma_pagamento' => null,
                    'data_pagamento'  => null,
                    'status'          => 'PENDENTE',
                ]);
            }

            return $agendamento;
        });

        return response()->json([
            'success' => true,
            'agendamento' => $ag,
        ]);
    }

