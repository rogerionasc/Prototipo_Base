<template>
  <Layout>
    <Head title="Movimentação de Caixa" />
    <PageHeader title="Movimentação" pageTitle="Caixa" />

    <div class="card mb-4 border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex flex-nowrap align-items-center justify-content-between gap-3">
          <div class="d-flex align-items-center gap-3 flex-grow-1">
            <div class="flex-grow-1" style="min-width:0;">
              <div class="input-group flex-nowrap w-100">
                <span class="input-group-text">
                  <i class="ri-money-dollar-box-line text-primary fs-20 me-1"></i>
                  <span>Caixa</span>
                </span>
                <select data-choices v-model="openForm.caixa_id" class="form-select" ref="selCaixa">
                  <option :value="null">Selecione</option>
                  <option v-for="c in caixasLocal" :key="c.id" :value="c.id">{{ c.descricao }}</option>
                </select>
              </div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
              <i class="ri-calendar-line text-muted"></i>
              <span class="text-muted">Data</span>
              <span class="fw-semibold">{{ currentDateText }}</span>
            </div>
            <div class="vr d-none d-md-block"></div>
            <div class="d-flex align-items-center gap-2">
              <i class="ri-time-line text-muted"></i>
              <span class="text-muted">Hora</span>
              <span class="fw-semibold">{{ currentTimeText }}</span>
            </div>
            <div class="vr d-none d-md-block"></div>
            <div class="d-flex align-items-center gap-2">
              <i :class="currentMovId ? 'ri-checkbox-circle-line text-success' : 'ri-close-circle-line text-secondary'"></i>
              <span class="text-muted">Status</span>
              <span class="badge rounded-pill px-3" :class="statusClass">{{ statusText }}</span>
            </div>
            <div class="vr d-none d-md-block"></div>
            <div class="d-flex align-items-center gap-2">
              <i class="ri-hashtag text-muted"></i>
              <span class="text-muted">Número</span>
              <span class="fw-semibold">{{ currentMovNumber }}</span>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-success" type="button" @click="abrirCaixa" :disabled="!openForm.caixa_id || hasMovHoje || openForm.processing">
              <span v-if="openForm.processing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              <i class="ri-door-lock-box-line me-1"></i> Abrir
            </button>
            <button class="btn btn-warning" type="button" @click="fecharCaixa" :disabled="!currentMovId || closeForm.processing">
              <span v-if="closeForm.processing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              <i class="ri-door-open-line me-1"></i> Fechar
            </button>

          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header pt-3 pb-0 border-0 bg-white">
            <ul class="nav nav-tabs nav-tabs-custom nav-success border-bottom-0 mb-0" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#pdv-operacao" role="tab">
                  <i class="ri-store-2-line me-1 align-bottom"></i> Operação (PDV)
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#pdv-historico" role="tab">
                  <i class="ri-history-line me-1 align-bottom"></i> Histórico
                </a>
              </li>
            </ul>
          </div>
          <div class="card-body p-0">
            <div class="tab-content text-muted">
          <!-- Aba: Operação (PDV) -->
          <div class="tab-pane active" id="pdv-operacao" role="tabpanel">
            <div class="row g-4 align-items-stretch p-4">
              <!-- Coluna Esquerda: Fila -->
              <div class="col-lg-7 col-md-7 col-12">
                <div class="d-flex flex-column h-100" style="min-height: 65vh;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                      <h5 class="mb-0 text-primary"><i class="ri-list-check me-2"></i>Pagamentos Pendentes</h5>
                      <div class="d-flex gap-2 align-items-center">
                        <div style="width: 130px; flex-shrink: 0;">
                          <flatPickr v-model="pendentesData" @on-change="fetchPendentes" class="form-control w-100" :config="flatpickrOptions" placeholder="dd/mm/aaaa" />
                        </div>
                        <div class="search-box" style="width: 300px; flex-shrink: 0;">
                          <input v-model="pendentesQuery" type="text" class="form-control search" placeholder="Buscar por paciente, documento ou emissão" />
                          <i class="ri-search-line search-icon"></i>
                        </div>
                      </div>
                    </div>
                    
                    <div class="table-responsive flex-grow-1" style="overflow-y: auto;">
                      <table class="table table-hover table-borderless align-middle mb-0" style="cursor: pointer;">
                        <thead class="table-light text-muted sticky-top">
                          <tr class="border-bottom border-light">
                            <th scope="col" class="py-3">Nº</th>
                            <th scope="col" class="py-3">Nº Pagamento</th>
                            <th scope="col" class="py-3">Paciente</th>
                            <th scope="col" class="py-3">Documento</th>
                            <th scope="col" class="py-3">Emissão</th>
                            <th scope="col" class="py-3 text-end">Valor</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="row in pagamentosFiltered" :key="row.faturamento_id" 
                              @click="selectedPendente = row"
                              :class="{'table-primary border-primary': selectedPendente?.faturamento_id === row.faturamento_id, 'border-bottom border-bottom-dashed': selectedPendente?.faturamento_id !== row.faturamento_id}">
                            <td>{{ row.pagamento_id || "—" }}</td>
                            <td>{{ row.nu_pagamento || "—" }}</td>
                            <td class="fw-medium text-dark">{{ row.paciente }}</td>
                            <td class="text-muted">{{ row.paciente_documento || "—" }}</td>
                            <td>{{ row.data_faturamento || "—" }}</td>
                            <td class="text-end fw-semibold text-success">{{ formatCurrency(row.valor) }}</td>
                          </tr>
                          <tr v-if="!pagamentosFiltered || pagamentosFiltered.length === 0">
                            <td colspan="6" class="text-center text-muted p-5">
                              <i class="ri-inbox-line fs-1 mb-3 d-block text-light"></i>
                              <h5 class="fw-medium">Nenhum pagamento na fila</h5>
                              <p class="mb-0">Todos os atendimentos foram recebidos ou a data selecionada está vazia.</p>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                  </div>
                </div>
              </div>

              <!-- Coluna Direita: Painel de Controle -->
              <div class="col-lg-5 col-md-5 col-12">
                <!-- Se há um paciente selecionado, mostra as Ações -->
                <div v-if="selectedPendente" class="card border border-light shadow-sm bg-white h-100 p-4 position-relative">
                  <div class="text-end mb-2">
                    <button type="button" class="btn-close" @click="selectedPendente = null"></button>
                  </div>
                  <div class="text-center">
                    <div class="avatar-lg mx-auto mb-3">
                      <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-24">
                        {{ selectedPendente.paciente ? selectedPendente.paciente.charAt(0) : 'P' }}
                      </div>
                    </div>
                    <h4 class="mb-1">{{ selectedPendente.paciente }}</h4>
                    <p class="text-muted mb-4">Doc: {{ selectedPendente.paciente_documento || '—' }}</p>

                    <div class="p-3 bg-light rounded mb-4">
                      <div class="text-muted text-uppercase fw-semibold small mb-1">Total a Receber</div>
                      <h2 class="text-success mb-0 fw-bold">{{ formatCurrency(selectedPendente.valor) }}</h2>
                    </div>

                    <div class="d-grid gap-3">
                      <button v-if="isAguardandoPix(selectedPendente)" class="btn btn-warning btn-lg shadow-sm" type="button" 
                              :disabled="cancelProcessing[selectedPendente.pagamento_id]"
                              @click="cancelProcessing[selectedPendente.pagamento_id] ? null : cancelarPix(selectedPendente.pagamento_id)">
                        <span v-if="cancelProcessing[selectedPendente.pagamento_id]" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        <i class="ri-close-circle-line align-bottom me-1"></i> Cancelar PIX Pendente
                      </button>
                      <button v-else-if="waitingPayment[selectedPendente.faturamento_id]" class="btn btn-warning btn-lg shadow-sm" type="button">
                        <span class="spinner-border spinner-border-sm align-middle me-2" role="status" aria-hidden="true"></span>
                        Aguardando pagamento
                      </button>
                      <button v-else class="btn btn-success btn-lg shadow-sm" type="button" @click="abrirReceber(selectedPendente.faturamento_id)">
                        <i class="ri-money-dollar-box-line align-middle me-2 fs-20"></i> PROCESSAR RECEBIMENTO
                      </button>

                      <div class="d-flex gap-2 mt-2">
                        <button class="btn btn-soft-info flex-grow-1" type="button" @click="mostrarDetalhes(selectedPendente.faturamento_id)">
                          <i class="ri-eye-line align-bottom me-1"></i> Detalhes
                        </button>
                        <button class="btn btn-soft-danger flex-grow-1" type="button" :disabled="!selectedPendente.pagamento_id" @click="abrirRecusar(selectedPendente.faturamento_id)">
                          <i class="ri-close-line align-bottom me-1"></i> Recusar
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Se NÃO há paciente selecionado, mostra Resumo Geral -->
                <div v-else class="card border-0 shadow-sm h-100 p-4 p-xl-5 d-flex flex-column align-items-center justify-content-center">
                    <div class="text-center mb-5 mt-4">
                      <div class="avatar-lg mx-auto mb-4">
                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-1">
                          <i class="ri-hand-coin-fill"></i>
                        </div>
                      </div>
                      <h4 class="fw-bold text-dark mb-2">Aguardando Seleção</h4>
                      <p class="text-muted fs-15 mb-0">Selecione um paciente na fila ao lado para iniciar o recebimento.</p>
                    </div>

                    <div class="w-100 mt-auto">
                      <h6 class="text-uppercase text-muted fw-bold mb-3 fs-11" style="letter-spacing: 0.5px;">Resumo do Caixa Atual</h6>
                      <div class="row g-3">
                        <div class="col-6">
                          <div class="card bg-light border-0 shadow-none mb-0 h-100 p-3 rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <span class="text-muted fw-semibold fs-12 text-uppercase">Saldo Inicial</span>
                              <div class="avatar-xs">
                                <div class="avatar-title bg-white text-muted rounded shadow-sm"><i class="ri-wallet-3-line"></i></div>
                              </div>
                            </div>
                            <h5 class="fw-bold text-dark mb-0 fs-16">{{ formatCurrency(currentMovSummary.saldo_caixa) }}</h5>
                          </div>
                        </div>
                        <div class="col-6">
                          <div class="card bg-info-subtle border-0 shadow-none mb-0 h-100 p-3 rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <span class="text-info fw-semibold fs-12 text-uppercase">Saldo Atual</span>
                              <div class="avatar-xs">
                                <div class="avatar-title bg-white text-info rounded shadow-sm"><i class="ri-safe-2-line"></i></div>
                              </div>
                            </div>
                            <h5 class="fw-bold text-info mb-0 fs-16">{{ formatCurrency(currentMovSummary.saldo_movimento) }}</h5>
                          </div>
                        </div>
                        <div class="col-6">
                          <div class="card bg-success-subtle border-0 shadow-none mb-0 h-100 p-3 rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <span class="text-success fw-semibold fs-12 text-uppercase">Total Entradas</span>
                              <div class="avatar-xs">
                                <div class="avatar-title bg-white text-success rounded shadow-sm"><i class="ri-arrow-right-up-line"></i></div>
                              </div>
                            </div>
                            <h5 class="fw-bold text-success mb-0 fs-16">{{ formatCurrency(currentMovSummary.total_entradas) }}</h5>
                          </div>
                        </div>
                        <div class="col-6">
                          <div class="card bg-danger-subtle border-0 shadow-none mb-0 h-100 p-3 rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <span class="text-danger fw-semibold fs-12 text-uppercase">Total Saídas</span>
                              <div class="avatar-xs">
                                <div class="avatar-title bg-white text-danger rounded shadow-sm"><i class="ri-arrow-right-down-line"></i></div>
                              </div>
                            </div>
                            <h5 class="fw-bold text-danger mb-0 fs-16">{{ formatCurrency(currentMovSummary.total_saidas) }}</h5>
                          </div>
                        </div>
                      </div>
                    </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Aba: Histórico Completo -->
          <div class="tab-pane" id="pdv-historico" role="tabpanel">
            <div class="p-4">
                <TableGrid
                  :columns="movCols"
                  :data="movsByCaixa"
                  :key="`movs-${openForm.caixa_id ?? 'all'}`"
                  :tableTitle="'Registros de Movimentações (Histórico Completo)'"
                  :showCheckbox="false"
                  :search="true"
                  :showAddButton="false"
                  :showStatus="false"
                  :showActions="true"
                  :actionsConfig="{ delete: false, edit: false, show: true, diary: false, print: false, download: false, restore: true }"
                  @restore="reabrirMov"
                  @show="mostrarMovimentacao"
                />
            </div>
          </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <Modal v-model="showDetalhesModal" title="Detalhes do Agendamento" name-button="Fechar" :processing="detalhesLoading" size="xl" @save="showDetalhesModal=false">

      <!-- Cabeçalho: Paciente + Valor -->
      <div class="bg-primary-subtle rounded p-3 mb-4">
        <div class="row align-items-center">
          <div class="col">
            <p class="text-muted fw-semibold mb-0 fs-12 text-uppercase">Paciente</p>
            <h5 class="fw-bold mb-0 mt-1">{{ selectedPaciente || "—" }}</h5>
            <p class="text-muted mb-0 fs-13 mt-1">CPF: {{ selectedCpf || "—" }}</p>
          </div>
          <div class="col-auto text-end">
            <p class="text-muted fw-semibold mb-1 fs-12">Valor Total</p>
            <h4 class="fw-bold text-success mb-0">{{ formatCurrency(faturamentoDetalhes.valor_total || 0) }}</h4>
          </div>
        </div>
      </div>

      <!-- Tabela de Agendamentos -->
      <div class="table-responsive">
        <h6 class="text-uppercase text-muted fw-bold mb-2 fs-12">Informações do Agendamento</h6>
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th class="fw-semibold fs-12 text-uppercase text-muted">Data/Hora</th>
              <th class="fw-semibold fs-12 text-uppercase text-muted">Médico</th>
              <th class="fw-semibold fs-12 text-uppercase text-muted">Procedimento</th>
              <th class="fw-semibold fs-12 text-uppercase text-muted">Status</th>
              <th class="fw-semibold fs-12 text-uppercase text-muted text-end">Valor</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="ag in agendamentosView" :key="ag.id">
              <td class="fw-medium">{{ ag.data || "—" }} às {{ ag.hora || "—" }}</td>
              <td>{{ ag.medico_nome || "—" }}</td>
              <td>{{ ag.procedimento_nome || "—" }}</td>
              <td>
                <span class="badge bg-light text-dark border">{{ ag.status_nome || "—" }}</span>
              </td>
              <td class="text-end fw-semibold text-success">{{ formatCurrency(ag.valor_cobrado || 0) }}</td>
            </tr>
            <tr v-if="!agendamentosView || agendamentosView.length === 0">
              <td colspan="5" class="text-center text-muted py-5">
                <i class="ri-inbox-line fs-28 d-block mb-2 opacity-50"></i>
                Nenhum agendamento vinculado.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </Modal>
    <Modal v-model="showPixConfigModal" title="Configurar PIX" name-button="Salvar" :processing="pixConfigProcessing" size="md" @save="salvarPixConfig">
      <div class="vstack gap-4">

        <!-- Erro -->
        <div class="invalid-feedback d-block" v-if="pixConfigError">
          <i class="ri-error-warning-line me-1"></i> {{ pixConfigError }}
        </div>

        <!-- Grupo: Chave -->
        <div>
          <p class="text-uppercase text-muted fw-bold fs-11 mb-2 letter-spacing-1">
            <i class="ri-key-2-line me-1 align-middle"></i> Chave de Recebimento
          </p>
          <label class="form-label text-muted fs-13 mb-1">Chave PIX <span class="text-danger">*</span></label>
          <input v-model.trim="pixConfig.chave" type="text" class="form-control" placeholder="E-mail, CPF/CNPJ, celular ou chave aleatória" />
        </div>

        <hr class="my-0 border-light" />

        <!-- Grupo: Recebedor -->
        <div>
          <p class="text-uppercase text-muted fw-bold fs-11 mb-2 letter-spacing-1">
            <i class="ri-store-2-line me-1 align-middle"></i> Dados do Estabelecimento
          </p>
          <div class="row g-3">
            <div class="col-md-7">
              <label class="form-label text-muted fs-13 mb-1">Nome do Estabelecimento <span class="text-danger">*</span></label>
              <input v-model.trim="pixConfig.recebedor_nome" type="text" class="form-control" placeholder="Nome exibido ao pagador" />
            </div>
            <div class="col-md-5">
              <label class="form-label text-muted fs-13 mb-1">Cidade <span class="text-danger">*</span></label>
              <input v-model.trim="pixConfig.recebedor_cidade" type="text" class="form-control" placeholder="Ex: São Paulo" />
            </div>
          </div>
        </div>

        <hr class="my-0 border-light" />

        <!-- Grupo: Descrição -->
        <div>
          <p class="text-uppercase text-muted fw-bold fs-11 mb-2 letter-spacing-1">
            <i class="ri-file-text-line me-1 align-middle"></i> Identificação da Cobrança
          </p>
          <label class="form-label text-muted fs-13 mb-1">Descrição <span class="text-muted fw-normal">(opcional)</span></label>
          <input v-model.trim="pixConfig.descricao" type="text" class="form-control" placeholder="Ex: Consulta Médica" />
          <p class="text-muted mt-1 mb-0 fs-12">Texto visível no comprovante do cliente.</p>
        </div>

      </div>
    </Modal>
    <Modal v-model="showReceberModal" :title="'Receber Pagamento'" :name-button="formaRecebimento === 'PIX' ? 'Prosseguir' : 'Receber Pagamento'" :processing="receberForm.processing" size="md" @save="prosseguirRecebimento">
      <div class="vstack gap-3">
        <div class="row g-2">
          <div class="col-6">
            <div class="d-flex flex-column">
              <span class="text-muted">Paciente</span>
              <span class="fw-semibold">{{ receberInfo.paciente || "—" }}</span>
            </div>
          </div>
          <div class="col-3">
            <div class="d-flex flex-column">
              <span class="text-muted">Valor</span>
              <span class="fw-semibold">{{ formatCurrency(receberInfo.valor || 0) }}</span>
            </div>
          </div>
          <div class="col-3">
            <div class="d-flex flex-column">
              <span class="text-muted">Emissão</span>
              <span class="fw-semibold">{{ receberInfo.emissao || "—" }}</span>
            </div>
          </div>
        </div>
        <div>
          <label class="form-label">Forma de pagamento</label>
          <div class="hstack gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" id="fpix" value="PIX" v-model="formaRecebimento" />
              <label class="form-check-label" for="fpix">PIX</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" id="fdin" value="DINHEIRO" v-model="formaRecebimento" />
              <label class="form-check-label" for="fdin">Dinheiro</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" id="fcart" value="CARTAO" v-model="formaRecebimento" />
              <label class="form-check-label" for="fcart">Cartão</label>
            </div>
          </div>
          <div class="invalid-feedback d-block" v-if="receberError">{{ receberError }}</div>
        </div>
      </div>
    </Modal>
    <Modal v-model="showCaixaModal" :title="'Caixa indisponível'" :name-button="'Fechar'" :processing="false" size="md" @save="showCaixaModal=false">
      <div class="vstack gap-2">
        <div class="alert alert-warning" role="alert">
          {{ caixaIndMsg }}
        </div>
        <div class="d-flex flex-column">
          <span class="text-muted">Caixa</span>
          <span class="fw-semibold">{{ selectedCaixaName }}</span>
        </div>
        <div class="d-flex flex-column">
          <span class="text-muted">Status</span>
          <span class="fw-semibold">{{ statusText }}</span>
        </div>
      </div>
    </Modal>
    <Modal v-model="showRecusarModal" :title="'Recusar Pagamento'" :name-button="'Recusar'" :processing="false" size="md" @save="confirmarRecusa">
      <div class="vstack gap-3">
        <div class="alert alert-danger" role="alert">
          Esta ação marcará o pagamento como recusado e ele não aparecerá em pendentes.
        </div>
        <div class="row g-2">
          <div class="col-6">
            <div class="d-flex flex-column">
              <span class="text-muted">Paciente</span>
              <span class="fw-semibold">{{ recusarInfo.paciente }}</span>
            </div>
          </div>
          <div class="col-3">
            <div class="d-flex flex-column">
              <span class="text-muted">Valor</span>
              <span class="fw-semibold">{{ recusarInfo.valor }}</span>
            </div>
          </div>
          <div class="col-3">
            <div class="d-flex flex-column">
              <span class="text-muted">Emissão</span>
              <span class="fw-semibold">{{ recusarInfo.emissao }}</span>
            </div>
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label">Justificativa da recusa</label>
          <textarea v-model.trim="recusaJustificativa" class="form-control" rows="3" placeholder="Descreva o motivo da recusa"></textarea>
          <div class="invalid-feedback d-block" v-if="recusaError">{{ recusaError }}</div>
        </div>
      </div>
    </Modal>
    <Modal v-model="showSaldoModal" :title="'Saldo Inicial do Caixa'" :name-button="'Abrir Caixa'" :processing="openForm.processing" size="md" @save="confirmarAbertura">
      <div class="vstack gap-3">
        <div class="d-flex flex-column">
          <span class="text-muted">Caixa</span>
          <span class="fw-semibold">{{ selectedCaixaName }}</span>
        </div>
        <div>
          <label class="form-label">Valor inicial</label>
          <input v-model.trim="saldoInicial" class="form-control" type="text" inputmode="decimal" placeholder="0,00" />
          <div class="invalid-feedback d-block" v-if="saldoError">{{ saldoError }}</div>
        </div>
      </div>
    </Modal>
    <Modal v-model="showMovModal" :key="`mov-${movView.id ?? ''}`" :title="'Relatório de Movimentação'" :name-button="'Fechar'" :processing="movLoading" size="xl" @save="showMovModal=false">
      <div class="d-flex justify-content-end mb-2 no-print">
        <button class="btn btn-outline-secondary" type="button" @click="imprimirMov">
          <i class="ri-printer-line me-1"></i>
          Imprimir
        </button>
      </div>
      <div class="no-print">
        <div class="report-container bg-white p-4 border rounded mx-auto" style="max-width: 1100px;">
          <div class="text-center mb-3">
            <h4 class="mb-1">Relatório de Movimentação</h4>
            <div class="text-muted">Documento gerado para conferência e impressão</div>
          </div>
          <table class="table table-sm table-bordered">
            <tbody>
              <tr><td style="width:220px;">Caixa</td><td>{{ movView.caixa || "—" }}</td></tr>
              <tr><td>Número</td><td>{{ movView.numero || "—" }}</td></tr>
              <tr>
                <td>Abertura / Fechamento</td>
                <td>
                  {{ movView.data_abertura || "—" }} {{ movView.hora_abertura || "" }}
                  <span class="mx-2">|</span>
                  {{ movView.data_fechamento || "—" }} {{ movView.hora_fechamento || "" }}
                </td>
              </tr>
              <tr>
                <td>Aberto por / Fechado por</td>
                <td>
                  {{ movView.aberto_por || "—" }}
                  <span class="mx-2">|</span>
                  {{ movView.fechado_por || "—" }}
                </td>
              </tr>
              <tr><td>Status</td><td>{{ movView.fechado_em ? "Fechado" : "Aberto" }}</td></tr>
            </tbody>
          </table>
          <div class="mb-3">
            <h6 class="mb-2">Resumo</h6>
            <table class="table table-sm table-bordered">
              <thead>
                <tr>
                  <th>Entradas</th>
                <th>Saídas</th>
                <th>Saldo Inicial</th>
                <th>Saldo Movimento</th>
                <th>Conferência</th>
                <th>Diferença</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>{{ formatCurrency(movView.total_entradas || 0) }}</td>
                <td>{{ formatCurrency(movView.total_saidas || 0) }}</td>
                <td>{{ formatCurrency(movView.saldo_caixa || 0) }}</td>
                <td>{{ formatCurrency(movView.saldo_movimento || 0) }}</td>
                <td>{{ formatCurrency(movView.total_conferencia || 0) }}</td>
                <td>{{ formatCurrency(movView.valor_diferenca || 0) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="mb-3" v-if="movView.observacoes_fechamento">
            <h6 class="mb-2">Observações</h6>
            <div class="border p-2">{{ movView.observacoes_fechamento }}</div>
          </div>
          <div>
            <h6 class="mb-2">Pagamentos</h6>
            <SimpleTable
                variant="borderless"
                compact
                tableClass="table-bordered"
                :items="movPagamentosView"
                :columns="pagamentosModalColumns"
                emptyTitle="Sem pagamentos"
                emptyMessage="Sem pagamentos nesta movimentação"
                emptyIcon="ri-inbox-line"
            >
              <template #body="{ items, columns }">
                <tr v-for="row in items" :key="row.id">
                  <td>{{ row.paciente }}</td>
                  <td>{{ row.procedimentos || '—' }}</td>
                  <td>{{ row.data_pagamento || '—' }}</td>
                  <td class="text-end">{{ formatCurrency(row.valor) }}</td>
                  <td>{{ row.forma_pagamento || '—' }}</td>
                  <td>{{ pagamentoStatusLabel(row) }}</td>
                </tr>
              </template>
            </SimpleTable>
          </div>
        </div>
      </div>
    </Modal>
  </Layout>
</template>

<style scoped>
/* Remove o efeito de card aninhado da tabela no Histórico Completo */
#pdv-historico :deep(.card) {
  border: none !important;
  box-shadow: none !important;
  margin-bottom: 0 !important;
}
#pdv-historico :deep(.row) {
  margin: 0 !important;
}
#pdv-historico :deep(.card-body) {
  padding-left: 0 !important;
  padding-right: 0 !important;
  padding-bottom: 0 !important;
}
</style>

<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, computed, toRef, watch, nextTick, onMounted, onUnmounted } from "vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Modal from "@/Components/Modal.vue";
import axios from "axios";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.min.css";
import "flatpickr/dist/l10n/pt.js";
import SimpleTable from "@/Components/Tables/SimpleTable.vue";

const pagamentosModalColumns = [
  { key: 'paciente', label: 'Paciente' },
  { key: 'procedimentos', label: 'Procedimento' },
  { key: 'data_pagamento', label: 'Data' },
  { key: 'valor', label: 'Valor', thClass: 'text-end' },
  { key: 'forma_pagamento', label: 'Forma' },
  { key: 'status', label: 'Status' }
];

const props = defineProps({
  caixas: { type: Array, default: () => [] },
  ultimos: { type: Array, default: () => [] },
  movs: { type: Array, default: () => [] },
  pagamentosPendentes: { type: Array, default: () => [] },
  ultimosPagamentos: { type: Array, default: () => [] },
});
const caixasLocal = toRef(props, "caixas");
const ultimosLocal = toRef(props, "ultimos");
const movsLocal = toRef(props, "movs");
const pagamentosLocal = ref([...(props.pagamentosPendentes || [])]);
const selectedPendente = ref(null);

watch(() => props.pagamentosPendentes, (nv) => {
  pagamentosLocal.value = [...(nv || [])];
  
  // Atualiza a referência do paciente selecionado para refletir as mudanças do banco
  if (selectedPendente.value) {
    const atualizado = pagamentosLocal.value.find(p => p.faturamento_id === selectedPendente.value.faturamento_id);
    if (atualizado) {
      selectedPendente.value = atualizado;
    } else {
      selectedPendente.value = null; // Caso tenha sido pago e saído da fila
    }
  }
}, { deep: true });
const ultimosPagamentosLocal = toRef(props, "ultimosPagamentos");
const ultimosPagamentosFiltered = computed(() => {
  const cid = openForm.caixa_id;
  const movId = currentMovId.value;
  if (!cid || !movId) return [];
  return (ultimosPagamentosLocal.value || []).filter(p => String(p.movimentacao_id) === String(movId));
});
const pendentesQuery = ref(new URLSearchParams(typeof window !== 'undefined' ? window.location.search : '').get('search_pendentes') || "");
let pendentesSearchTimeout = null;
watch(pendentesQuery, (nv) => {
  if (pendentesSearchTimeout) clearTimeout(pendentesSearchTimeout);
  pendentesSearchTimeout = setTimeout(() => {
    fetchPendentes();
  }, 400);
});
function filterRow(row, q) {
  const s = String(q || "").toLowerCase();
  if (!s) return true;
  return Object.values(row || {}).some((v) => String(v).toLowerCase().includes(s));
}
const pagamentosFiltered = computed(() => {
  return (pagamentosLocal.value || []).filter((r) => filterRow(r, pendentesQuery.value));
});
const pendentesTotal = computed(() => {
  try { return (pagamentosFiltered.value || []).reduce((acc, r) => acc + Number(r.valor || 0), 0); } catch (e) { return 0; }
});
const hasPixPendente = computed(() => {
  const cid = openForm.caixa_id;
  return (pagamentosLocal.value || []).some(r =>
    String(r?.pagamento_status || '').toLowerCase() === 'pendente' &&
    String(r?.forma_pagamento || '').toUpperCase() === 'PIX' &&
    (!cid || String(r?.caixa_id) === String(cid))
  );
});
const ultimosElegant = computed(() => (ultimosPagamentosFiltered.value || []));
function isDropUp(idx) {
  try {
    const total = (pagamentosFiltered.value || []).length;
    if (total <= 1) return false;
    return idx >= total - 2;
  } catch (e) { return false; }
}
function formaClass(fp) {
  const s = String(fp || "").toLowerCase();
  if (s.includes("pix") || s.includes("dinheiro") || s.includes("cart")) return "bg-success-subtle text-success";
  if (s.includes("boleto") || s.includes("prazo")) return "bg-warning-subtle text-warning";
  if (s.includes("transf")) return "bg-primary-subtle text-primary";
  return "bg-light text-dark";
}
const movsByCaixa = computed(() => {
  const cid = openForm.caixa_id;
  if (!cid) return [];
  return (movsLocal.value || []).filter((m) => String(m.caixa_id) === String(cid));
});

function todayDMY() {
  const d = new Date();
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const yyyy = d.getFullYear();
  return `${dd}-${mm}-${yyyy}`;
}

function todayYMD() {
  const d = new Date();
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const yyyy = d.getFullYear();
  return `${yyyy}-${mm}-${dd}`;
}

const urlParams = new URLSearchParams(typeof window !== 'undefined' ? window.location.search : '');
const pendentesData = ref(urlParams.get('data_pendentes') || todayYMD());

function fetchPendentes() {
  router.get(
    window.location.pathname,
    { data_pendentes: pendentesData.value, search_pendentes: pendentesQuery.value },
    { preserveState: true, replace: true, preserveScroll: true }
  );
}

const flatpickrOptions = { altInput: true, altFormat: "d M, Y", dateFormat: "Y-m-d", locale: "pt" };

const selCaixa = ref(null);
const currentMovId = ref(null);
const hasMovHoje = ref(false);

const selectedCaixaName = computed(() => {
  const id = openForm.caixa_id;
  const c = (caixasLocal.value || []).find((x) => String(x.id) === String(id));
  return c?.descricao || "—";
});
const selectedCaixa = computed(() => {
  const id = openForm.caixa_id;
  return (caixasLocal.value || []).find((x) => String(x.id) === String(id)) || null;
});
const showCaixaModal = ref(false);
const caixaIndMsg = computed(() => {
  const cx = selectedCaixa.value;
  if (!openForm.caixa_id) return "Selecione um caixa.";
  if (!cx) return "Caixa não encontrado.";
  if (!currentMovId.value) return "Caixa sem movimentação aberta.";
  if (!cx.ativo) return "Caixa inativo.";
  if (cx.bloquear_receber) return "Caixa bloqueado para receber.";
  return "Caixa indisponível.";
});
const showRecusarModal = ref(false);
const recusarFaturamentoId = ref(null);
const recusaJustificativa = ref("");
const recusaError = ref("");
const recusarInfo = computed(() => {
  const id = recusarFaturamentoId.value;
  if (!id) return {};
  const r = (pagamentosLocal.value || []).find(x => String(x.faturamento_id) === String(id));
  return {
    paciente: r?.paciente || "—",
    valor: formatCurrency(r?.valor || 0),
    emissao: r?.data_emissao || "—",
  };
});
const statusText = computed(() => {
  if (currentMovId.value) return "Em aberto";
  return "Sem abertura";
});
const statusClass = computed(() => {
  return currentMovId.value ? "bg-success-subtle text-success" : "bg-secondary-subtle text-secondary";
});
const isCaixaDisponivelReceber = computed(() => {
  const cx = selectedCaixa.value;
  return !!openForm.caixa_id && !!currentMovId.value && !!cx && !!cx.ativo && !cx.bloquear_receber;
});
const isCaixaDisponivelPagar = computed(() => {
  const cx = selectedCaixa.value;
  return !!openForm.caixa_id && !!currentMovId.value && !!cx && !!cx.ativo && !cx.bloquear_pagar;
});

const openForm = useForm({
  caixa_id: null,
  saldo_caixa: 0,
});

const closeForm = useForm({
  total_entradas: 0,
  total_saidas: 0,
  saldo_caixa: 0,
  total_entrada_prazo: 0,
  total_saida_prazo: 0,
  total_transferencia: 0,
  total_conferencia: 0,
  observacoes_fechamento: "",
});

function formatCurrency(n) {
  const v = Number(n || 0);
  try {
    return v.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  } catch (e) {
    return `R$ ${v.toFixed(2)}`.replace(".", ",");
  }
}

function pagamentoStatusLabel(row) {
  const raw = String(row?.status ?? row?.pagamento_status ?? '').trim();
  const s = raw.toUpperCase();
  if (s === 'PAGO') return 'Pago';
  if (s === 'PENDENTE') return 'Pendente';
  if (s === 'RECUSADO') return 'Recusado';
  if (s === 'CANCELADO') return 'Cancelado';
  if (raw) return raw;
  return row?.confirmado ? 'Pago' : 'Pendente';
}

const currentMov = computed(() => {
  const id = currentMovId.value;
  if (!id) return null;
  return (movsLocal.value || []).find((m) => String(m.id) === String(id)) || null;
});
const currentMovSummary = computed(() => {
  const m = currentMov.value || {};
  return {
    total_entradas: Number(m.total_entradas || 0),
    total_saidas: Number(m.total_saidas || 0),
    saldo_caixa: Number(m.saldo_caixa || 0),
    saldo_movimento: Number(m.saldo_movimento || 0),
    total_conferencia: Number(m.total_conferencia || 0),
    valor_diferenca: Number(m.valor_diferenca || 0),
  };
});
const currentDif = computed(() => {
  const entradas = Number(currentMovSummary.value.total_entradas || 0);
  const saidas = Number(currentMovSummary.value.total_saidas || 0);
  const saldoInicial = Number(currentMovSummary.value.saldo_caixa || 0);
  const conf = Number(currentMovSummary.value.total_conferencia || 0);
  return saldoInicial + entradas - saidas - conf;
});
const difClass = computed(() => {
  const v = Number(currentDif.value || 0);
  if (v === 0) return "text-success";
  return v > 0 ? "text-primary" : "text-danger";
});

const payLatestCols = [
  { id: "paciente", name: "Paciente" },
  { id: "caixa", name: "Caixa" },
  { id: "data_pagamento", name: "Data" },
  { id: "valor", name: "Valor", formatter: (cell) => formatCurrency(cell) },
  { id: "forma_pagamento", name: "Forma" },
];

const movCols = [
  { id: "numero", name: "Número" },
  { id: "data_movimento", name: "Data" },
  { id: "total_entradas", name: "Entradas", formatter: (cell) => formatCurrency(cell) },
  { id: "total_conferencia", name: "Conferência", formatter: (cell) => formatCurrency(cell) },
  { id: "fechado_em", name: "Status", formatter: (cell) => (cell ? "Fechado" : "Aberto") },
];

const payCols = [
  { id: "paciente", name: "Paciente" },
  { id: "valor", name: "Valor", formatter: (cell) => formatCurrency(cell) },
  { id: "forma_pagamento", name: "Forma" },
  { id: "data_emissao", name: "Emissão" },
];

const currentDateText = computed(() => {
  const aberta = selectedCaixa.value?.movimentacao_aberta;
  if (aberta) return aberta.data_movimento;
  return todayDMY();
});

const currentTimeText = computed(() => {
  const aberta = selectedCaixa.value?.movimentacao_aberta;
  if (aberta) return aberta.hora_abertura;
  return "—";
});

const currentMovNumber = computed(() => {
  const aberta = selectedCaixa.value?.movimentacao_aberta;
  return aberta?.numero || "—";
});

function recomputeCurrentMov() {
  currentMovId.value = null;
  hasMovHoje.value = false;
  const cx = selectedCaixa.value;
  if (!cx) return;

  const aberta = cx.movimentacao_aberta;
  if (aberta) {
    hasMovHoje.value = true;
    currentMovId.value = aberta.id;
  }
}

watch([() => openForm.caixa_id, caixasLocal], () => {
  recomputeCurrentMov();
});

function abrirCaixa() {
  saldoInicial.value = "";
  saldoError.value = "";
  showSaldoModal.value = true;
}

function abrirRecusar(id) {
  recusarFaturamentoId.value = id;
  recusaJustificativa.value = "";
  recusaError.value = "";
  showRecusarModal.value = true;
}
const showReceberModal = ref(false);
const receberFaturamentoId = ref(null);
const receberPagamentoId = ref(null);
const formaRecebimento = ref("PIX");
const cancelProcessing = ref({});
const receberForm = useForm({
  caixa_id: '',
  forma_pagamento: ''
});
const receberError = ref("");
const receberInfo = computed(() => {
  const id = receberFaturamentoId.value;
  if (!id) return {};
  const r = (pagamentosLocal.value || []).find(x => String(x.faturamento_id) === String(id));
  return {
    paciente: r?.paciente || "—",
    valor: Number(r?.valor || 0),
    emissao: r?.data_emissao || "—",
  };
});
const waitingPayment = ref({});

function abrirReceber(id) {
  if (!isCaixaDisponivelReceber.value) {
    showCaixaModal.value = true;
    return;
  }
  receberFaturamentoId.value = id;
  const r = (pagamentosLocal.value || []).find(x => String(x.faturamento_id) === String(id));
  receberPagamentoId.value = r?.pagamento_id || null;
  formaRecebimento.value = "PIX";
  receberError.value = "";
  showReceberModal.value = true;
}
function isAguardandoPix(row) {
  return String(row?.pagamento_status || '').toLowerCase() === 'pendente' && String(row?.forma_pagamento || '').toUpperCase() === 'PIX';
}
function cancelarPix(id) {
  if (!id) return;
  cancelProcessing.value[id] = true;
  const f = useForm({});
  f.put(`/pagamentos/${id}/cancel-pix`, {
    onSuccess: async () => {
      // Quando cancela, temos que resetar o estado de aguardando se existir
      if (selectedPendente.value && selectedPendente.value.faturamento_id) {
        waitingPayment.value[selectedPendente.value.faturamento_id] = false;
      }
      
      await new Promise((resolve) => {
        router.reload({ only: ["pagamentosPendentes","ultimosPagamentos","movs"], onFinish: () => resolve() });
      });
    },
    onError: () => { /* noop */ },
    onFinish: () => {
      cancelProcessing.value[id] = false;
    },
  });
}
function prosseguirRecebimento() {
  const fatId = receberFaturamentoId.value;
  if (!fatId) { showReceberModal.value = false; return; }
  receberError.value = "";
  waitingPayment.value[fatId] = true; // Ativa o estado amarelo no painel lateral
  
  const run = async () => {
    try {
      let pid = receberPagamentoId.value;
      if (!pid) {
        const resp = await axios.post(`/faturamentos/${fatId}/pagamentos`);
        pid = resp?.data?.pagamento_id || null;
        receberPagamentoId.value = pid;
      }
      if (!pid) {
        waitingPayment.value[fatId] = false;
        return;
      }
      
      receberForm.caixa_id = openForm.caixa_id;
      receberForm.forma_pagamento = formaRecebimento.value;

      if (formaRecebimento.value === "PIX") {
        if (!isCaixaDisponivelReceber.value) {
          waitingPayment.value[fatId] = false;
          showReceberModal.value = false;
          showCaixaModal.value = true; 
          return; 
        }
        receberForm.put(`/pagamentos/${pid}/prepare-pix`, {
          onSuccess: async () => {
            showReceberModal.value = false;
            await new Promise((resolve) => {
              router.reload({ only: ["pagamentosPendentes","ultimosPagamentos","movs"], onFinish: () => resolve() });
            });
          },
          onError: () => {
            showReceberModal.value = false;
          }
        });
      } else {
        // Para dinheiro ou cartão
        if (!isCaixaDisponivelReceber.value) { 
          waitingPayment.value[fatId] = false;
          showReceberModal.value = false;
          showCaixaModal.value = true; 
          return; 
        }
        receberForm.put(`/pagamentos/${pid}/confirm`, {
          onSuccess: async () => {
            showReceberModal.value = false;
            await new Promise((resolve) => {
              router.reload({ only: ["caixas","ultimos","movs","pagamentosPendentes","ultimosPagamentos"], onFinish: () => resolve() });
            });
            recomputeCurrentMov();
          },
          onError: () => {
            showCaixaModal.value = true;
          }
        });
      }
    } catch (e) {
      waitingPayment.value[fatId] = false;
    }
  };
  run();
}
function confirmarRecusa() {
  const fatId = recusarFaturamentoId.value;
  if (!fatId) { showRecusarModal.value = false; return; }
  const row = (pagamentosLocal.value || []).find(r => String(r.faturamento_id) === String(fatId));
  const pid = row?.pagamento_id || null;
  if (!pid) { showRecusarModal.value = false; return; }
  if (!String(recusaJustificativa.value || "").trim()) {
    recusaError.value = "Informe a justificativa da recusa.";
    return;
  }
  const f = useForm({ recusa_justificativa: recusaJustificativa.value });
  f.put(`/pagamentos/${pid}/refuse`, {
    onSuccess: async () => {
      showRecusarModal.value = false;
      recusarFaturamentoId.value = null;
      recusaJustificativa.value = "";
      recusaError.value = "";
      await new Promise((resolve) => {
        router.reload({ only: ["pagamentosPendentes","ultimosPagamentos","movs"], onFinish: () => resolve() });
      });
    },
    onError: () => {
      showRecusarModal.value = false;
    },
  });
}

function fecharCaixa() {
  if (!currentMovId.value) return;
  closeForm.put(`/movimentacoes-caixa/${currentMovId.value}`, {
    onSuccess: async () => {
      await new Promise((resolve) => {
        router.reload({ only: ["caixas","ultimos","movs","ultimosPagamentos"], onFinish: () => resolve() });
      });
      closeForm.reset();
      recomputeCurrentMov();
    },
  });
}



function reabrirMov(id) {
  if (!id) return;
  const f = useForm({});
  f.put(`/movimentacoes-caixa/${id}/reopen`, {
    onSuccess: () => {
      router.reload({ only: ["caixas","ultimos","movs","ultimosPagamentos"] });
    },
  });
}

function confirmarPagamento(id, forma = null) {
  if (!id) return;
  // Requer um caixa selecionado e movimentação aberta
  if (!isCaixaDisponivelReceber.value) { showCaixaModal.value = true; return; }
  const f = useForm({
    caixa_id: openForm.caixa_id,
    forma_pagamento: forma || null,
  });
  f.put(`/pagamentos/${id}/confirm`, {
    onSuccess: async () => {
      await new Promise((resolve) => {
        router.reload({ only: ["caixas","ultimos","movs","pagamentosPendentes","ultimosPagamentos"], onFinish: () => resolve() });
      });
      recomputeCurrentMov();
    },
    onError: (errs) => {
      showCaixaModal.value = true;
    }
  });
}

const showSaldoModal = ref(false);
const saldoInicial = ref("");
const saldoError = ref("");

const showPixConfigModal = ref(false);
const pixConfigProcessing = ref(false);
const pixConfigError = ref("");
const pixConfig = ref({ chave: "", recebedor_nome: "", recebedor_cidade: "", descricao: "" });
async function carregarPixConfig() {
  try {
    const resp = await axios.get('/config/pix');
    pixConfig.value = {
      chave: resp.data?.chave || "",
      recebedor_nome: resp.data?.recebedor_nome || "",
      recebedor_cidade: resp.data?.recebedor_cidade || "",
      descricao: resp.data?.descricao || "",
    };
  } catch (e) { /* noop */ }
}
async function salvarPixConfig() {
  if (!String(pixConfig.value.chave || "").trim()) {
    pixConfigError.value = "Informe a chave PIX.";
    return;
  }
  pixConfigError.value = "";
  pixConfigProcessing.value = true;
  try {
    await axios.put('/config/pix', pixConfig.value);
    showPixConfigModal.value = false;
  } finally {
    pixConfigProcessing.value = false;
  }
}

const pendentesPolling = ref(false);
let pendTimer = null;

function isSelectedDateToday() {
  return !pendentesData.value || pendentesData.value === todayYMD();
}

async function fetchPendentesNow() {
  if (!isSelectedDateToday()) return;
  if (pendentesQuery.value) return; // Não atualiza (polling) enquanto houver busca ativa
  try {
    const response = await axios.get('/movimentacoes-caixa/pendentes');
    if (response.data && response.data.pagamentosPendentes) {
      pagamentosLocal.value = response.data.pagamentosPendentes;
      
      // Se o pagamento selecionado não estiver mais na lista (ex: foi pago via PIX), limpa a seleção para fechar o card
      if (selectedPendente.value && selectedPendente.value.pagamento_id) {
        const stillExists = pagamentosLocal.value.find(p => p.pagamento_id === selectedPendente.value.pagamento_id);
        if (!stillExists) {
          selectedPendente.value = null;
        }
      }
    }
  } catch (e) {
    console.error("Erro ao buscar pagamentos pendentes:", e);
  }
}

function startPendentesPolling() {
  if (!isSelectedDateToday()) return; // Não faz polling em datas passadas
  if (pendTimer) clearInterval(pendTimer);
  
  // Busca imediatamente antes de iniciar o intervalo
  fetchPendentesNow();
  
  pendTimer = setInterval(() => {
    if (!isSelectedDateToday()) { stopPendentesPolling(); return; }
    fetchPendentesNow();
  }, 5000); // 5 segundos para não sobrecarregar
  pendentesPolling.value = true;
}

function stopPendentesPolling() {
  if (pendTimer) { clearInterval(pendTimer); pendTimer = null; }
  pendentesPolling.value = false;
}
onMounted(() => {
  if (typeof document !== "undefined") {
    const onVis = () => {
      if (document.hidden) {
        stopPendentesPolling();
      } else {
        if (isSelectedDateToday()) startPendentesPolling();
      }
    };
    document.addEventListener("visibilitychange", onVis);
    visibilityCleanup = () => document.removeEventListener("visibilitychange", onVis);

    // Inicia o polling apenas se a data for hoje
    if (isSelectedDateToday()) startPendentesPolling();
  }
});

// Para/retoma o polling conforme a data selecionada
watch(pendentesData, (newDate) => {
  if (!newDate || newDate === todayYMD()) {
    startPendentesPolling();
  } else {
    stopPendentesPolling();
  }
});
watch([() => openForm.caixa_id], ([newCaixa]) => {
  // O polling agora é global, mas podemos forçar uma atualização imediata ao trocar de caixa
  if (newCaixa) {
    router.reload({
      only: ["pagamentosPendentes", "ultimosPagamentos", "movs", "caixas", "ultimos"],
      preserveScroll: true,
      preserveState: true
    });
  }
});
let visibilityCleanup = null;
onUnmounted(() => {
  stopPendentesPolling();
  if (typeof visibilityCleanup === "function") {
    try { visibilityCleanup(); } catch (e) {}
    visibilityCleanup = null;
  }
});

function parseValor(val) {
  const s = String(val || "").trim();
  if (!s) return 0;
  const n = Number(s.replace(/\./g, "").replace(",", "."));
  return Number.isNaN(n) ? NaN : n;
}

// PIX helpers
async function getPixConfig() {
  try {
    const resp = await axios.get('/config/pix');
    return resp.data || {};
  } catch (e) {
    return {};
  }
}
function sanitizeText(s, max) {
  const t = (String(s || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '')).toUpperCase();
  return t.slice(0, max || t.length);
}
function sanitizeTxid(s) {
  const t = String(s || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
  return t.slice(0, 25);
}
function emvField(id, value) {
  const v = String(value || '');
  const len = String(v.length).padStart(2, '0');
  return `${id}${len}${v}`;
}
function crc16(payload) {
  let crc = 0xFFFF;
  for (let i = 0; i < payload.length; i++) {
    crc ^= payload.charCodeAt(i) << 8;
    for (let j = 0; j < 8; j++) {
      if ((crc & 0x8000) !== 0) {
        crc = (crc << 1) ^ 0x1021;
      } else {
        crc <<= 1;
      }
      crc &= 0xFFFF;
    }
  }
  return crc.toString(16).toUpperCase().padStart(4, '0');
}
function buildPixPayload({ chave, recebedor_nome, recebedor_cidade, txid, valor }) {
  const merchantAccountInfo = emvField('00', 'BR.GOV.BCB.PIX') + emvField('01', String(chave || ''));
  const mai = emvField('26', merchantAccountInfo);
  const amount = Number(valor || 0).toFixed(2);
  const payloadSemCRC =
    emvField('00','01') +
    emvField('01','11') +
    mai +
    emvField('52','0000') +
    emvField('53','986') +
    emvField('54', amount) +
    emvField('58','BR') +
    emvField('59', sanitizeText(recebedor_nome || 'RECEBEDOR', 25)) +
    emvField('60', sanitizeText(recebedor_cidade || 'CIDADE', 15)) +
    emvField('62', emvField('05', String(txid || 'TX')) ) +
    '6304';
  const crc = crc16(payloadSemCRC);
  return payloadSemCRC + crc;
}
async function abrirPixWindow(pagamentoId) {
  const row = (pagamentosLocal.value || []).find(r => String(r.id) === String(pagamentoId));
  const valor = Number(row?.valor || 0);
  const cfg = await getPixConfig();
  const txid = sanitizeTxid(`PAG${String(pagamentoId)}`);
  const payload = buildPixPayload({
    chave: cfg?.chave || '',
    recebedor_nome: cfg?.recebedor_nome || '',
    recebedor_cidade: cfg?.recebedor_cidade || '',
    txid,
    valor,
  });
  const w = window.open('', '_blank');
  if (!w) return;
  w.document.title = 'Pagamento PIX';
  const link = w.document.createElement('link');
  link.rel = 'stylesheet';
  link.href = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css';
  w.document.head.appendChild(link);
  const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=${encodeURIComponent(payload)}`;
  const container = w.document.createElement('div');
  container.className = 'container py-4';
  container.innerHTML = `
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body text-center">
            <h5 class="card-title mb-3">Pagamento via PIX</h5>
            <p class="text-muted mb-1">Valor</p>
            <div class="fs-4 fw-semibold mb-3">${valor.toLocaleString('pt-BR',{style:'currency',currency:'BRL'})}</div>
            <img src="${qrUrl}" alt="QR Code PIX" class="img-fluid mb-3" style="max-width: 260px;" />
            <div class="text-start">
              <label class="form-label">Copia e Cola</label>
              <textarea class="form-control" rows="4">${payload}</textarea>
            </div>
            <div class="d-grid gap-2 mt-3">
              <button class="btn btn-success" id="btnConfirm">Confirmar Recebimento</button>
              <button class="btn btn-outline-secondary" id="btnClose">Fechar</button>
            </div>
            <div class="alert alert-warning mt-3 d-none" id="msgWarn">Falha ao confirmar. Verifique se o caixa está disponível.</div>
            <div class="alert alert-success mt-3 d-none" id="msgOk">Pagamento confirmado!</div>
          </div>
        </div>
      </div>
    </div>
  `;
  w.document.body.className = 'bg-light';
  w.document.body.appendChild(container);
  const id = pagamentoId;
  const caixaId = openForm.caixa_id;
  const btnConfirm = w.document.getElementById('btnConfirm');
  const btnClose = w.document.getElementById('btnClose');
  const msgOk = w.document.getElementById('msgOk');
  const msgWarn = w.document.getElementById('msgWarn');
  btnConfirm && btnConfirm.addEventListener('click', async () => {
    try {
      const resp = await fetch('/pagamentos/' + id + '/confirm', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With':'XMLHttpRequest' },
        body: JSON.stringify({ caixa_id: caixaId, forma_pagamento: 'PIX' })
      });
      if (!resp.ok) throw new Error('HTTP ' + resp.status);
      msgOk && msgOk.classList.remove('d-none');
      msgWarn && msgWarn.classList.add('d-none');
      btnConfirm.disabled = true;
      setTimeout(() => w.close());
    } catch (e) {
      msgWarn && msgWarn.classList.remove('d-none');
    }
  });
  btnClose && btnClose.addEventListener('click', () => { w.close(); });
}
function confirmarAbertura() {
  const v = parseValor(saldoInicial.value);
  if (!(v >= 0)) {
    saldoError.value = "Informe um valor válido.";
    return;
  }
  saldoError.value = "";
  openForm.saldo_caixa = v;
  openForm.post("/movimentacoes-caixa", {
    onSuccess: async () => {
      showSaldoModal.value = false;
      await new Promise((resolve) => {
        router.reload({ only: ["caixas","ultimos","movs","ultimosPagamentos"], onFinish: () => resolve() });
      });
      saldoInicial.value = "";
      recomputeCurrentMov();
    },
    onError: () => {
      showSaldoModal.value = false;
    },
  });
}

const showDetalhesModal = ref(false);
const faturamentoDetalhes = ref({});
const agendamentosView = ref([]);
const selectedPaciente = ref("");
const selectedFaturamento = ref("");
const selectedCpf = ref("");
const detalhesLoading = ref(false);

async function mostrarDetalhes(faturamentoId) {
  const row = (pagamentosLocal.value || []).find(r => String(r.faturamento_id) === String(faturamentoId));
  selectedPaciente.value = row?.paciente || "";
  selectedFaturamento.value = row?.pagamento_id || "";
  if (!faturamentoId) return;
  detalhesLoading.value = true;
  try {
    const resp = await axios.get(`/faturamentos/${faturamentoId}/detalhes`);
    faturamentoDetalhes.value = resp.data?.faturamento || {};
    agendamentosView.value = resp.data?.agendamentos || [];
    selectedCpf.value = faturamentoDetalhes.value?.paciente_cpf || "";
    selectedPaciente.value = faturamentoDetalhes.value?.paciente_nome || selectedPaciente.value || "";
    showDetalhesModal.value = true;
  } finally {
    detalhesLoading.value = false;
  }
}

const showMovModal = ref(false);
const movView = ref({});
const movPagamentosView = ref([]);
const movLoading = ref(false);

function imprimirMov() {
  const el = document.querySelector('.report-container');
  if (!el) { window.print(); return; }
  const clone = el.cloneNode(true);
  Array.from(clone.querySelectorAll('.table-responsive')).forEach(d => d.classList.remove('table-responsive'));
  const html = clone.innerHTML;
  const w = window.open('', '_blank');
  if (!w) return;
  const headLinks = Array.from(document.querySelectorAll('link[rel="stylesheet"], style')).map(n => n.outerHTML).join('');
  const printCss = `
    @page { size: A4 portrait; margin: 12mm; }
    .report-container { width: 186mm; margin: 0 auto; }
    .table-responsive { overflow: visible !important; }
    table { width: 100%; border-collapse: collapse; }
    table, tbody, thead, tfoot, tr, td, th { font-size: 12px; line-height: 1.25; }
    thead { display: table-header-group; }
    tfoot { display: table-footer-group; }
    tr, td, th { page-break-inside: avoid; }
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  `;
  w.document.open();
  w.document.write(`<html><head><meta charset="utf-8"><title>Relatório de Movimentação</title>${headLinks}<style>${printCss}</style></head><body><div class="report-container">${html}</div></body></html>`);
  w.document.close();
  w.focus();
  setTimeout(() => { try { w.print(); } finally { w.close(); } }, 600);
}

async function mostrarMovimentacao(id) {
  if (!id) return;
  showMovModal.value = false;
  movView.value = {};
  movPagamentosView.value = [];
  movLoading.value = true;
  try {
    const resp = await axios.get(`/movimentacoes-caixa/${id}`, { headers: { 'Cache-Control': 'no-cache' } });
    movView.value = resp.data?.movimentacao || {};
    movPagamentosView.value = resp.data?.pagamentos || [];
    await nextTick();
    showMovModal.value = true;
  } finally {
    movLoading.value = false;
  }
}

onMounted(async () => {
  await nextTick();
  recomputeCurrentMov();
  if (selCaixa.value && window.initChoices) {
    window.initChoices();
  }
  await nextTick();
  if (selCaixa.value) {
    selCaixa.value.addEventListener("change", (e) => {
      openForm.caixa_id = e?.target?.value ?? openForm.caixa_id;
    });
    if (window.syncChoiceValue) {
      window.syncChoiceValue(selCaixa.value, openForm.caixa_id != null ? String(openForm.caixa_id) : "");
    }
  }
});
</script>

<style scoped>
.table td:last-child,
.table th:last-child {
  overflow: visible !important;
  position: relative;
}
.dropdown-menu {
  z-index: 2000;
}
.table-responsive {
  overflow: visible !important;
}
</style>
<style scoped>
:deep(.input-group) { flex-wrap: nowrap; }
:deep(.input-group .choices) { flex: 1 1 auto; min-width: 0; width: auto; }
:deep(.input-group .choices__inner) { width: auto; min-width: 0; height: 40px; min-height: 40px; display: flex; align-items: center; border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important; }
:deep(.input-group .choices.is-open .choices__inner) { border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important; }
:deep(.input-group .choices.is-focused .choices__inner) { border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important; }
:deep(.input-group .form-select) { border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important; }
:deep(.input-group .input-group-text) { height: 40px; border-top-right-radius: 0; border-bottom-right-radius: 0; }
:deep(.input-group .choices__list--single) { padding: 0 !important; }
:deep(.input-group) { flex-wrap: nowrap; }
.table-clean thead th { border-bottom: 1px solid var(--bs-border-color); }
.table-clean tbody tr:not(:last-child) td { border-bottom: 1px solid var(--bs-border-color); }
.table-clean td, .table-clean th { vertical-align: middle; }
</style>
