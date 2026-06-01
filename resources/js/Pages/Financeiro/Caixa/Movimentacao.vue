<template>
  <Layout>
    <Head title="Movimentação de Caixa" />
    <PageHeader title="Movimentação de Caixa" pageTitle="Caixa" />

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
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <h5 class="mb-0">Pagamentos Pendentes</h5>
              <div class="search-box" style="width: 280px;">
                <input v-model="pendentesQuery" type="text" class="form-control search" placeholder="Buscar por paciente, forma ou emissão" />
                <i class="ri-search-line search-icon"></i>
              </div>
            </div>
            <div class="text-muted small mb-3">Itens: {{ pagamentosFiltered.length }} • Total: {{ formatCurrency(pendentesTotal) }}</div>
            <div class="table-responsive">
              <table class="table table-borderless align-middle table-clean mb-0">
                <thead>
                  <tr>
                    <th class="text-muted small">Paciente</th>
                    <th class="text-muted small text-end">Valor</th>
                    <th class="text-muted small">Forma</th>
                    <th class="text-muted small">Emissão</th>
                    <th class="text-muted small text-end">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, idx) in pagamentosFiltered" :key="row.id">
                    <td>{{ row.paciente }}</td>
                    <td class="text-end">{{ formatCurrency(row.valor) }}</td>
                    <td class="text-muted">{{ row.forma_pagamento || "—" }}</td>
                    <td>{{ row.data_orcamento || "—" }}</td>
                    <td class="text-end">
                      <BDropdown
                        class="position-static d-inline-block"
                        dropstart
                        auto-close="outside"
                        :toggle-class="isAguardandoPix(row) ? 'btn btn-sm btn-warning' : 'btn btn-sm btn-success'"
                        menu-class="shadow-lg"
                        :variant="isAguardandoPix(row) ? 'warning' : 'success'"
                        size="sm"
                        :split="true"
                        @click="isAguardandoPix(row) ? (cancelProcessing[row.id] ? null : cancelarPix(row.id)) : abrirReceber(row.id)"
                      >
                        <template #button-content>
                          <span v-if="isAguardandoPix(row) && cancelProcessing[row.id]" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                          <i :class="isAguardandoPix(row) ? 'ri-close-circle-line align-bottom me-1' : 'ri-money-dollar-box-line align-bottom me-1'"></i>{{ isAguardandoPix(row) ? 'Cancelar' : 'Receber' }}
                        </template>
                        <BDropdownItem @click="abrirRecusar(row.id)">
                          <i class="ri-close-circle-line text-danger me-2"></i>Recusar
                        </BDropdownItem>
                        <BDropdownItem @click="mostrarOrcamento(row.id)">
                          <i class="ri-eye-fill text-info me-2"></i>Visualizar
                        </BDropdownItem>
                      </BDropdown>
                    </td>
                  </tr>
                  <tr v-if="!pagamentosFiltered || pagamentosFiltered.length === 0">
                    <td colspan="5" class="text-muted">Sem registros</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="mt-4"></div>
            <TableGrid
              :columns="movCols"
              :data="movsByCaixa"
              :key="`movs-${openForm.caixa_id ?? 'all'}`"
              :tableTitle="'Registros de Movimentações'"
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

  <div class="col-lg-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mb-3">Resumo</h5>
            <div class="row g-3 mb-2">
              <div class="col-6">
                <div class="p-3 bg-success-subtle rounded d-flex align-items-center justify-content-between">
                  <div>
                    <div class="text-muted">Entradas</div>
                    <div class="fw-semibold">{{ formatCurrency(currentMovSummary.total_entradas) }}</div>
                  </div>
                  <i class="ri-arrow-up-circle-line text-success fs-24"></i>
                </div>
              </div>
              <div class="col-6">
                <div class="p-3 bg-danger-subtle rounded d-flex align-items-center justify-content-between">
                  <div>
                    <div class="text-muted">Saídas</div>
                    <div class="fw-semibold">{{ formatCurrency(currentMovSummary.total_saidas) }}</div>
                  </div>
                  <i class="ri-arrow-down-circle-line text-danger fs-24"></i>
                </div>
              </div>
              <div class="col-6">
                <div class="p-3 bg-primary-subtle rounded d-flex align-items-center justify-content-between">
                  <div>
                    <div class="text-muted">Saldo Inicial</div>
                    <div class="fw-semibold">{{ formatCurrency(currentMovSummary.saldo_caixa) }}</div>
                  </div>
                  <i class="ri-wallet-3-line text-primary fs-24"></i>
                </div>
              </div>
              <div class="col-6">
                <div class="p-3 bg-light rounded border d-flex align-items-center justify-content-between">
                  <div>
                    <div class="text-muted">Saldo Movimento</div>
                    <div class="fw-semibold">{{ formatCurrency(currentMovSummary.saldo_movimento) }}</div>
                  </div>
                  <i class="ri-exchange-dollar-line text-muted fs-24"></i>
                </div>
              </div>
              <div class="col-6">
                <div class="p-3 bg-light rounded border d-flex align-items-center justify-content-between">
                  <div>
                    <div class="text-muted">Conferência</div>
                    <div class="fw-semibold">{{ formatCurrency(currentMovSummary.total_conferencia) }}</div>
                  </div>
                  <i class="ri-check-double-line text-muted fs-24"></i>
                </div>
              </div>
              <div class="col-6">
                <div class="p-3 bg-light rounded border d-flex align-items-center justify-content-between">
                  <div>
                    <div class="text-muted">Diferença</div>
                    <div class="fw-semibold" :class="difClass">{{ formatCurrency(currentDif) }}</div>
                  </div>
                  <i class="ri-mist-line text-muted fs-24"></i>
                </div>
              </div>
            </div>
        </div>
      </div>
      <div class="card mt-4">
        <div class="card-body">
          <h5 class="mb-3">Últimos Pagamentos</h5>
          <div class="table-responsive">
            <table class="table table-borderless align-middle table-clean mb-0">
              <thead>
                <tr>
                  <th class="text-muted small">Paciente</th>
                  <th class="text-muted small">Data</th>
                  <th class="text-muted small text-end">Valor</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in ultimosElegant" :key="row.id">
                  <td>{{ row.paciente }}</td>
                  <td>{{ row.data_pagamento || "—" }}</td>
                  <td class="text-end">{{ formatCurrency(row.valor) }}</td>
                </tr>
                <tr v-if="!ultimosElegant || ultimosElegant.length === 0">
                  <td colspan="5" class="text-muted">Sem registros</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      </div>
    </div>
    <Modal v-model="showOrcModal" title="Orçamento Aprovado" name-button="Fechar" :processing="orcLoading" size="xl" @save="showOrcModal=false">
      <div class="row g-3">
        <div class="col-md-6">
          <div class="d-flex flex-column">
            <span class="text-muted">Paciente</span>
            <span class="fw-semibold">{{ selectedPaciente || "—" }}</span>
          </div>
        </div>
        <div class="col-md-6">
          <div class="d-flex flex-column">
            <span class="text-muted">Número</span>
            <span class="fw-semibold">{{ orcamentoView.numero || "—" }}</span>
          </div>
        </div>
        <div class="col-md-6">
          <div class="d-flex flex-column">
            <span class="text-muted">CPF</span>
            <span class="fw-semibold">{{ selectedCpf || "—" }}</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="d-flex flex-column">
            <span class="text-muted">Emissão</span>
            <span class="fw-semibold">{{ orcamentoView.data_emissao || "—" }}</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="d-flex flex-column">
            <span class="text-muted">Validade</span>
            <span class="fw-semibold">{{ orcamentoView.validade || "—" }}</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="d-flex flex-column">
            <span class="text-muted">Valor Total</span>
            <span class="fw-semibold">{{ formatCurrency(orcamentoView.valor_total || 0) }}</span>
          </div>
        </div>
        <div class="col-12">
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th>Procedimento</th>
                  <th>Quantidade</th>
                  <th>Unitário</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="it in orcamentoItensView" :key="it.id">
                  <td>{{ it.procedimento_nome || it.procedimento_id }}</td>
                  <td>{{ it.quantidade }}</td>
                  <td>{{ formatCurrency(it.valor_unitario) }}</td>
                  <td>{{ formatCurrency(it.valor_total) }}</td>
                </tr>
                <tr v-if="!orcamentoItensView || orcamentoItensView.length === 0">
                  <td colspan="4" class="text-muted">Sem itens</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </Modal>
    <Modal v-model="showPixConfigModal" :title="'Configurar PIX'" :name-button="'Salvar'" :processing="pixConfigProcessing" size="md" @save="salvarPixConfig">
      <div class="vstack gap-3">
        <div>
          <label class="form-label">Chave PIX</label>
          <input v-model.trim="pixConfig.chave" type="text" class="form-control" placeholder="e-mail, cpf/cnpj ou chave aleatória" />
        </div>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">Nome do recebedor</label>
            <input v-model.trim="pixConfig.recebedor_nome" type="text" class="form-control" placeholder="Nome Fantasia" />
          </div>
          <div class="col-md-6">
            <label class="form-label">Cidade do recebedor</label>
            <input v-model.trim="pixConfig.recebedor_cidade" type="text" class="form-control" placeholder="Cidade" />
          </div>
        </div>
        <div>
          <label class="form-label">Descrição</label>
          <input v-model.trim="pixConfig.descricao" type="text" class="form-control" placeholder="Descrição opcional" />
        </div>
        <div class="invalid-feedback d-block" v-if="pixConfigError">{{ pixConfigError }}</div>
      </div>
    </Modal>
    <Modal v-model="showReceberModal" :title="'Receber Pagamento'" :name-button="'Prosseguir'" :processing="receberProcessing" size="md" @save="prosseguirRecebimento">
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
              <tr><td>Data do Movimento</td><td>{{ movView.data_movimento || "—" }}</td></tr>
              <tr><td>Abertura</td><td>{{ movView.data_abertura || "—" }} {{ movView.hora_abertura || "" }}</td></tr>
              <tr><td>Fechamento</td><td>{{ movView.data_fechamento || "—" }} {{ movView.hora_fechamento || "" }}</td></tr>
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
            <div class="table-responsive">
              <table class="table table-sm table-bordered">
                <thead>
                  <tr>
                  <th>Paciente</th>
                  <th>Procedimento</th>
                  <th>Data</th>
                  <th class="text-end">Valor</th>
                  <th>Forma</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in movPagamentosView" :key="row.id">
                  <td>{{ row.paciente }}</td>
                  <td>{{ row.procedimentos || "—" }}</td>
                  <td>{{ row.data_pagamento || "—" }}</td>
                  <td class="text-end">{{ formatCurrency(row.valor) }}</td>
                  <td>{{ row.forma_pagamento || "—" }}</td>
                  <td>{{ row.confirmado ? "Confirmado" : "Pendente" }}</td>
                </tr>
                <tr v-if="!movPagamentosView || movPagamentosView.length === 0">
                  <td colspan="6" class="text-muted">Sem pagamentos nesta movimentação</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </Modal>
  </Layout>
</template>

<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, computed, toRef, watch, nextTick, onMounted, onUnmounted } from "vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Modal from "@/Components/Modal.vue";
import axios from "axios";

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

watch(() => props.pagamentosPendentes, (nv) => {
  pagamentosLocal.value = [...(nv || [])];
}, { deep: true });
const ultimosPagamentosLocal = toRef(props, "ultimosPagamentos");
const ultimosPagamentosFiltered = computed(() => {
  const cid = openForm.caixa_id;
  const movId = currentMovId.value;
  if (!cid || !movId) return [];
  return (ultimosPagamentosLocal.value || []).filter(p => String(p.movimentacao_id) === String(movId));
});
const pendentesQuery = ref("");
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
    String(r?.status || '').toLowerCase() === 'pendente' &&
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

const flatpickrOptions = null;

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
const recusarId = ref(null);
const recusaJustificativa = ref("");
const recusaError = ref("");
const recusarInfo = computed(() => {
  const id = recusarId.value;
  if (!id) return {};
  const r = (pagamentosLocal.value || []).find(x => String(x.id) === String(id));
  return {
    paciente: r?.paciente || "—",
    valor: formatCurrency(r?.valor || 0),
    emissao: r?.data_orcamento || "—",
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
  { id: "data_orcamento", name: "Emissão" },
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
  recusarId.value = id;
  recusaJustificativa.value = "";
  recusaError.value = "";
  showRecusarModal.value = true;
}
const showReceberModal = ref(false);
const receberPagamentoId = ref(null);
const formaRecebimento = ref("PIX");
const cancelProcessing = ref({});
const receberProcessing = ref(false);
const receberError = ref("");
const receberInfo = computed(() => {
  const id = receberPagamentoId.value;
  if (!id) return {};
  const r = (pagamentosLocal.value || []).find(x => String(x.id) === String(id));
  return {
    paciente: r?.paciente || "—",
    valor: Number(r?.valor || 0),
    emissao: r?.data_orcamento || "—",
  };
});
function abrirReceber(id) {
  receberPagamentoId.value = id;
  formaRecebimento.value = "PIX";
  receberError.value = "";
  showReceberModal.value = true;
}
function isAguardandoPix(row) {
  return String(row?.status || '').toLowerCase() === 'pendente' && String(row?.forma_pagamento || '').toUpperCase() === 'PIX';
}
function cancelarPix(id) {
  if (!id) return;
  cancelProcessing.value[id] = true;
  const f = useForm({});
  f.put(`/pagamentos/${id}/cancel-pix`, {
    onSuccess: async () => {
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
  const id = receberPagamentoId.value;
  if (!id) { showReceberModal.value = false; return; }
  receberError.value = "";
  if (formaRecebimento.value === "PIX") {
    if (!isCaixaDisponivelReceber.value) { showCaixaModal.value = true; return; }
    const f = useForm({ caixa_id: openForm.caixa_id });
    f.put(`/pagamentos/${id}/prepare-pix`, {
      onSuccess: async () => {
        showReceberModal.value = false;
        await new Promise((resolve) => {
          router.reload({ only: ["pagamentosPendentes","ultimosPagamentos","movs"], onFinish: () => resolve() });
        });
      },
      onError: () => {
        showReceberModal.value = false;
      },
    });
  } else {
    confirmarPagamento(id, formaRecebimento.value);
    showReceberModal.value = false;
  }
}
function confirmarRecusa() {
  const id = recusarId.value;
  if (!id) { showRecusarModal.value = false; return; }
  if (!String(recusaJustificativa.value || "").trim()) {
    recusaError.value = "Informe a justificativa da recusa.";
    return;
  }
  const f = useForm({ recusa_justificativa: recusaJustificativa.value });
  f.put(`/pagamentos/${id}/refuse`, {
    onSuccess: async () => {
      showRecusarModal.value = false;
      recusarId.value = null;
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
function startPendentesPolling() {
  if (pendTimer) clearInterval(pendTimer);
  pendTimer = setInterval(async () => {
    // Polling focado apenas na tabela de pagamentos pendentes via AXIOS
    // Isso evita reloads do Inertia que podem atrapalhar outras tabelas
    try {
      const response = await axios.get('/movimentacoes-caixa/pendentes');
      if (response.data && response.data.pagamentosPendentes) {
        pagamentosLocal.value = response.data.pagamentosPendentes;
      }
    } catch (e) {
      console.error("Erro ao buscar pagamentos pendentes:", e);
    }
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
        startPendentesPolling();
      }
    };
    document.addEventListener("visibilitychange", onVis);
    visibilityCleanup = () => document.removeEventListener("visibilitychange", onVis);

    // Inicia o polling global ao entrar na tela de movimentação
    startPendentesPolling();
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

const showOrcModal = ref(false);
const orcamentoView = ref({});
const orcamentoItensView = ref([]);
const selectedPaciente = ref("");
const selectedCpf = ref("");
const orcLoading = ref(false);

async function mostrarOrcamento(pagamentoId) {
  const row = (pagamentosLocal.value || []).find(r => String(r.id) === String(pagamentoId));
  const oid = row?.orcamento_id;
  selectedPaciente.value = row?.paciente || "";
  if (!oid) return;
  orcLoading.value = true;
  try {
    const resp = await axios.get(`/orcamentos/${oid}`);
    orcamentoView.value = resp.data?.orcamento || {};
    orcamentoItensView.value = resp.data?.itens || [];
    selectedCpf.value = orcamentoView.value?.paciente_cpf || "";
    showOrcModal.value = true;
  } finally {
    orcLoading.value = false;
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
