<template>
  <Layout>

    <Head title="Autorizações" />
    <PageHeader title="Autorizações" pageTitle="Convênios" />
    <TableGrid :columns="columns" :data="mappedAutorizacoes" :tableTitle="'Todas as Autorizações'" :showStatus="false"
      :showCheckbox="false" :showImage="false" :searchPlaceholder="'Buscar autorização'" @add="openModalAdd"
      @delete="openModalDelete" @edit="openModalEdit" @show="openModalShow" />
    <Modal v-model="showModal" :title="modalTitle" size="xl" :name-button="saveButtonText" :processing="saveProcessing"
      @save="onSaveAutorizacao">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Convênio</label>
          <select v-model="form.convenio_id" class="form-select" required>
            <option value="">Selecione um convênio</option>
            <option v-for="conv in convenios" :key="conv.id" :value="conv.id">
              {{ conv.descricao }}
            </option>
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Carteirinha</label>
          <input v-model="form.carteira" type="text" class="form-control" placeholder="Número da carteirinha" />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Protocolo</label>
          <input v-model="form.protocolo" type="text" class="form-control" placeholder="Número do protocolo" />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Número da Autorização</label>
          <input v-model="form.numero_autorizacao" type="text" class="form-control"
            placeholder="Número da autorização" />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Status</label>
          <select v-model="form.status" class="form-select" required>
            <option value="SOLICITADA">Solicitada</option>
            <option value="AUTORIZADA">Autorizada</option>
            <option value="Pendente">Pendente</option>
            <option value="Aprovada">Aprovada</option>
            <option value="Negada">Negada</option>
            <option value="Expirada">Expirada</option>
            <option value="Cancelada">Cancelada</option>
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Validade</label>
          <input v-model="form.validade" type="date" class="form-control" />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Data da Solicitação</label>
          <input v-model="form.data_solicitacao" type="date" class="form-control" />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Data da Resposta</label>
          <input v-model="form.data_resposta" type="date" class="form-control" />
        </div>
        <div class="col-md-12 mb-3">
          <label class="form-label">Observação</label>
          <textarea v-model="form.observacao" class="form-control" rows="3" placeholder="Observações"></textarea>
        </div>
      </div>
    </Modal>
    <ModalDelete v-model="deleteModal" :title="'Excluir Autorização'" :subTitle="deleteSubTitle"
      :item-delete="autorizacaoToDelete" @save="confirmDelete" />
    <Modal v-model="showDetailsModal" title="Detalhes da Autorização" size="xl" :show-save="false" cancel-text="Fechar">
      <div v-if="selectedAutorizacao">
        
        <!-- Bloco 1: Status Principal (Mantido como o usuário gostou) -->
        <div class="card bg-light border-0 mb-4 shadow-none">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <h5 class="fs-15 mb-1 text-dark">Status da Autorização</h5>
              <p class="text-muted mb-0 fs-13">Acompanhe a situação atual desta solicitação</p>
            </div>
            <div>
              <span class="badge bg-primary-subtle text-primary fs-14 px-3 py-2">
                <i class="ri-checkbox-circle-line me-1 align-middle"></i>
                {{ selectedAutorizacao.status }}
              </span>
            </div>
          </div>
        </div>

        <h6 class="fs-14 mb-3 text-uppercase text-muted fw-semibold border-bottom pb-2">
          <i class="ri-file-list-3-line align-middle me-1"></i> Dados da Autorização e Convênio
        </h6>
        
        <div class="row mb-4">
          <div class="col-md-4 mb-3">
            <p class="text-muted mb-1 fs-12">Nº da Autorização (Senha)</p>
            <h6 class="fs-14 mb-0 fw-medium text-dark">{{ selectedAutorizacao.numero_autorizacao || 'Aguardando' }}</h6>
          </div>
          <div class="col-md-4 mb-3">
            <p class="text-muted mb-1 fs-12">Convênio</p>
            <h6 class="fs-14 mb-0 fw-medium text-dark">{{ getConvenioNome(selectedAutorizacao.convenio_id) }}</h6>
          </div>
          <div class="col-md-4 mb-3">
            <p class="text-muted mb-1 fs-12">Carteirinha</p>
            <h6 class="fs-14 mb-0 fw-medium text-dark">{{ selectedAutorizacao.carteira || '-' }}</h6>
          </div>
          <div class="col-md-4 mb-3">
            <p class="text-muted mb-1 fs-12">Procedimento TUSS</p>
            <h6 class="fs-14 mb-0 fw-medium text-dark">
              <span v-if="selectedAutorizacao.tuss">
                {{ selectedAutorizacao.tuss.codigo }} - {{ selectedAutorizacao.tuss.descricao }}
              </span>
              <span v-else>Não informado</span>
            </h6>
          </div>
          <div class="col-md-4 mb-3">
            <p class="text-muted mb-1 fs-12">Valor da Coparticipação</p>
            <h6 class="fs-14 mb-0 fw-medium text-dark">{{ selectedAutorizacao.valor ? 'R$ ' + selectedAutorizacao.valor : 'Sem custo' }}</h6>
          </div>
          <div class="col-md-4 mb-3">
            <p class="text-muted mb-1 fs-12">Validade</p>
            <h6 class="fs-14 mb-0 fw-medium text-dark">{{ formatDate(selectedAutorizacao.validade) || '-' }}</h6>
          </div>
          <div class="col-md-4 mb-3">
            <p class="text-muted mb-1 fs-12">Data da Solicitação</p>
            <h6 class="fs-14 mb-0 fw-medium text-dark">{{ formatDateTime(selectedAutorizacao.data_solicitacao) || '-' }}</h6>
          </div>
          <div class="col-md-4 mb-3">
            <p class="text-muted mb-1 fs-12">Data da Resposta</p>
            <h6 class="fs-14 mb-0 fw-medium text-dark">{{ formatDateTime(selectedAutorizacao.data_resposta) || '-' }}</h6>
          </div>
          <div class="col-md-4 mb-3">
            <p class="text-muted mb-1 fs-12">Solicitado Por</p>
            <h6 class="fs-14 mb-0 fw-medium text-dark">{{ getUsuarioNome(selectedAutorizacao.usuario_id) || '-' }}</h6>
          </div>
        </div>

        <h6 class="fs-14 mb-3 text-uppercase text-muted fw-semibold border-bottom pb-2">
          <i class="ri-calendar-event-line align-middle me-1"></i> Dados do Atendimento Relacionado
        </h6>
        
        <div class="row mb-4">
          <div class="col-md-4 mb-3">
            <p class="text-muted mb-1 fs-12">ID Guia</p>
            <h6 class="fs-14 mb-0 fw-medium text-dark">{{ selectedAutorizacao.guia_id ? '#' + selectedAutorizacao.guia_id : '-' }}</h6>
          </div>
          <div class="col-md-4 mb-3">
            <p class="text-muted mb-1 fs-12">Status da Guia</p>
            <h6 class="fs-14 mb-0 fw-medium text-dark">{{ selectedAutorizacao.guia?.status || '-' }}</h6>
          </div>
          <div class="col-md-4 mb-3">
            <p class="text-muted mb-1 fs-12">Nome do Médico</p>
            <h6 class="fs-14 mb-0 fw-medium text-dark">{{ selectedAutorizacao.guia?.agendamento?.agenda_medica?.profissional_saude?.nome || '-' }}</h6>
          </div>
        </div>

        <h6 class="fs-14 mb-3 text-uppercase text-muted fw-semibold border-bottom pb-2">
          <i class="ri-chat-3-line align-middle me-1"></i> Observações
        </h6>
        <div class="row">
          <div class="col-md-12">
            <p class="fs-14 text-dark mb-0 bg-light p-3 rounded">{{ selectedAutorizacao.observacao || 'Nenhuma observação registrada.' }}</p>
          </div>
        </div>

      </div>
    </Modal>
  </Layout>
</template>
<script setup>
import "gridjs/dist/theme/mermaid.css";
import { html } from "gridjs";
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head, useForm, router } from '@inertiajs/vue3';
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Modal from "@/Components/Modal.vue";
import ModalDelete from "@/Components/ModalDelete.vue";
import { ref, computed } from "vue";

const props = defineProps({
  autorizacoes: { type: Array, default: () => [] },
  convenios: { type: Array, default: () => [] },
  usuarios: { type: Array, default: () => [] },
});

const mappedAutorizacoes = computed(() => {
  return props.autorizacoes.map(a => ({
    ...a,
    medico: a.guia?.agendamento?.agenda_medica?.profissional_saude?.nome || "-"
  }));
});

const columns = [
  { id: "id", name: "ID" },
  { id: "protocolo", name: "Protocolo" },
  { id: "convenio_id", name: "Convênio", formatter: (cell) => props.convenios.find(c => c.id == cell)?.descricao || cell || "" },
  { id: "guia", name: "Paciente", formatter: (cell) => cell?.agendamento?.paciente?.nome || "-" },
  { id: "medico", name: "Médico" },
  { id: "tuss", name: "Procedimento", formatter: (cell) => cell ? `${cell.codigo} - ${cell.descricao}` : "-" },
  { id: "numero_autorizacao", name: "Nº Autorização" },
  { 
    id: "status", 
    name: "Status",
    formatter: (cell) => {
      let cls = 'bg-secondary-subtle text-secondary';
      const s = String(cell || '').toLowerCase();
      if (s === 'autorizada' || s === 'aprovada') cls = 'bg-success-subtle text-success';
      else if (s === 'solicitada' || s === 'pendente') cls = 'bg-warning-subtle text-warning';
      else if (s === 'negada' || s === 'cancelada' || s === 'expirada') cls = 'bg-danger-subtle text-danger';
      
      return html(`<span class="badge ${cls} fs-12 px-2 py-1">${cell || 'N/A'}</span>`);
    }
  },
  { id: "data_solicitacao", name: "Data Solicitação", formatter: (cell) => cell ? new Date(cell).toLocaleDateString('pt-BR') : "-" },
];

const showModal = ref(false);
const modalTitle = ref('Adicionar Autorização');
const isEditing = ref(false);
const editingId = ref(null);
const saveProcessing = ref(false);
const saveButtonText = computed(() => isEditing.value ? 'Atualizar' : 'Salvar');

const form = useForm({
  protocolo: '',
  convenio_id: '',
  carteira: '',
  numero_autorizacao: '',
  status: 'SOLICITADA',
  validade: '',
  data_solicitacao: '',
  data_resposta: '',
  observacao: '',
});

function openModalAdd() {
  isEditing.value = false;
  editingId.value = null;
  modalTitle.value = 'Adicionar Autorização';
  form.reset();
  showModal.value = true;
}

async function onSaveAutorizacao() {
  saveProcessing.value = true;
  if (isEditing.value && editingId.value) {
    form.put(`/convenios/autorizacoes/${editingId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        showModal.value = false;
        router.reload({ only: ['autorizacoes'], preserveScroll: true, preserveState: false });
      },
      onFinish: () => {
        saveProcessing.value = false;
      }
    });
  } else {
    form.post('/convenios/autorizacoes', {
      preserveScroll: true,
      onSuccess: () => {
        showModal.value = false;
        router.reload({ only: ['autorizacoes'], preserveScroll: true, preserveState: false });
      },
      onFinish: () => {
        saveProcessing.value = false;
      }
    });
  }
}

const deleteModal = ref(false);
const autorizacaoToDelete = ref({});
const deleteSubTitle = ref('Deseja realmente excluir');

function openModalDelete(row) {
  autorizacaoToDelete.value = { ...row, nome: `Autorização #${row.id}` };
  deleteSubTitle.value = `Deseja realmente excluir a autorização #${row.id}?`;
  deleteModal.value = true;
}

function confirmDelete() {
  const id = autorizacaoToDelete.value?.id;
  if (!id) { deleteModal.value = false; return; }
  const f = useForm({});
  f.delete(`/convenios/autorizacoes/${id}`, {
    preserveScroll: true,
    onSuccess: () => {
      deleteModal.value = false;
      autorizacaoToDelete.value = {};
    }
  });
}

async function openModalEdit(id) {
  const a = props.autorizacoes.find(au => String(au.id) === String(id));
  if (!a) return;
  isEditing.value = true;
  editingId.value = a.id;
  modalTitle.value = 'Editar Autorização';
  form.protocolo = a.protocolo || '';
  form.convenio_id = a.convenio_id;
  form.carteira = a.carteira || '';
  form.numero_autorizacao = a.numero_autorizacao || '';
  form.status = a.status;
  form.validade = a.validade || '';
  form.data_solicitacao = a.data_solicitacao ? new Date(a.data_solicitacao).toISOString().split('T')[0] : '';
  form.data_resposta = a.data_resposta ? new Date(a.data_resposta).toISOString().split('T')[0] : '';
  form.observacao = a.observacao || '';
  showModal.value = true;
}

const showDetailsModal = ref(false);
const selectedAutorizacao = ref(null);

function getConvenioNome(id) {
  return props.convenios.find(c => String(c.id) === String(id))?.descricao || '-';
}

function getUsuarioNome(id) {
  return props.usuarios.find(u => String(u.id) === String(id))?.nome || '-';
}

function formatDate(dateString) {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('pt-BR');
}

function formatDateTime(dateString) {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleString('pt-BR');
}

function openModalShow(id) {
  const a = props.autorizacoes.find(au => String(au.id) === String(id));
  if (!a) return;
  selectedAutorizacao.value = a;
  showDetailsModal.value = true;
}
</script>
