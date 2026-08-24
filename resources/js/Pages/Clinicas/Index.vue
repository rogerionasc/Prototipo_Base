<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";
import SimpleTable from "@/Components/Tables/SimpleTable.vue";
import Modal from '@/Components/Modal.vue';
import ModalDelete from '@/Components/ModalDelete.vue';
import Swal from "sweetalert2";
import { html } from 'gridjs';
import Multiselect from '@vueform/multiselect';
import "@vueform/multiselect/themes/default.css";

const props = defineProps({
  clinicas: Array,
  profissionais: Array,
  auth: Object
});

const profissionaisOptions = computed(() => {
  const list = props.profissionais.map(p => ({ value: p.id, label: p.nome }));
  list.unshift({ value: null, label: 'Sala Disponível' });
  return list;
});

const showModal = ref(false);
const isEditing = ref(false);
const activeTab = ref(0);
const deleteModal = ref(false);
const clinicaToDelete = ref(null);

const showSubModal = ref(false);
const subModalTitle = ref('');
const subModalType = ref('');

const subForm = useForm({
  id: null,
  nome: '',
  hostname: '',
  status: true
});

const openSubModal = (type, item = null) => {
  subModalType.value = type;

  if (item) {
    subModalTitle.value = `Editar ${type}`;
    subForm.id = item.id;
    subForm.nome = item.nome;
    subForm.hostname = item.hostname || '';
    subForm.status = item.status === 'Ativo' || item.status === true;
  } else {
    subModalTitle.value = `Novo(a) ${type}`;
    subForm.reset();
    subForm.id = null;
  }
  showSubModal.value = true;
};

const saveSubItem = () => {
  let routeName = '';
  switch (subModalType.value) {
    case 'Totem': routeName = subForm.id ? 'totens.update' : 'totens.store'; break;
    case 'Painel': routeName = subForm.id ? 'paineis.update' : 'paineis.store'; break;
    case 'Guichê': routeName = subForm.id ? 'guiches.update' : 'guiches.store'; break;
    case 'Sala': routeName = subForm.id ? 'salas.update' : 'salas.store'; break;
  }

  if (routeName) {
    const action = subForm.id ? subForm.put : subForm.post;
    const params = subForm.id ? [route(routeName, subForm.id)] : [route(routeName)];

    subForm.transform((data) => ({
      ...data,
      account_id: form.id
    }));

    action.call(subForm, params[0], {
      onSuccess: () => {
        showSubModal.value = false;
      }
    });
  }
};

const subDeleteModal = ref(false);
const subItemToDelete = ref(null);

const openSubDeleteModal = (type, item) => {
  subItemToDelete.value = { ...item, type };
  subDeleteModal.value = true;
};

const confirmSubDelete = () => {
  if (!subItemToDelete.value) return;

  let routeName = '';
  switch (subItemToDelete.value.type) {
    case 'Totem': routeName = 'totens.destroy'; break;
    case 'Painel': routeName = 'paineis.destroy'; break;
    case 'Guichê': routeName = 'guiches.destroy'; break;
    case 'Sala': routeName = 'salas.destroy'; break;
  }

  if (routeName) {
    router.delete(route(routeName, subItemToDelete.value.id), {
      onSuccess: () => {
        subDeleteModal.value = false;
        subItemToDelete.value = null;
      }
    });
  }
};

const form = useForm({
  id: null,
  name: '',
  cnpj: '',
  cnes: '',
  endereco: '',
  telefone: '',
  email: '',
  ativo: true
});

const columns = [
  { id: "id", name: "ID" },
  { id: "name", name: "Nome da Clínica" },
  { id: "cnpj", name: "CNPJ" },
  { id: "cnes", name: "CNES" },
  { id: "status", name: "Status" }
];

const genericColumns = [
  { id: "id", name: "ID" },
  { id: "nome", name: "Nome/Identificação" },
  {
    name: 'Status',
    id: 'status',
    formatter: (val) => {
      if (val === 'Ativo' || val === true) return html(`<span class="badge bg-success-subtle text-success">Ativo</span>`);
      return html(`<span class="badge bg-danger-subtle text-danger">Inativo</span>`);
    }
  },
];

const opcoesColumns = [
  { key: 'nome', label: 'Nome' },
  { key: 'codigo', label: 'Código' },
  { key: 'icone', label: 'Ícone' },
  { key: 'cor', label: 'Cor' },
  { key: 'status', label: 'Status' },
  { key: 'acoes', label: 'Ações', thClass: 'text-center' }
];

const salasColumns = [
  { id: "id", name: "ID" },
  { id: "nome", name: "Nome/Identificação" },
  { id: "medico", name: "Médico(a)" },
  {
    name: 'Status',
    id: 'status',
    formatter: (val) => {
      if (val === 'Ocupada') return html(`<span class="badge bg-danger-subtle text-danger"><i class="ri-close-circle-line align-middle"></i> Ocupada</span>`);
      return html(`<span class="badge bg-success-subtle text-success"><i class="ri-checkbox-circle-line align-middle"></i> Disponível</span>`);
    }
  },
];

const guichesColumns = [
  { id: "id", name: "ID" },
  { id: "nome", name: "Nome/Identificação" },
  { id: "hostname", name: "Hostname" },
  { 
      name: 'Status', 
      id: 'status', 
      formatter: (val) => {
          if (val === 'Ativo' || val === true) return html(`<span class="badge bg-success-subtle text-success">Ativo</span>`);
          return html(`<span class="badge bg-danger-subtle text-danger">Inativo</span>`);
      }
  },
];

const opcoesModal = ref(false);
const totemSelecionado = ref(null);
const localOpcoes = ref([]);
const newOpcao = ref({ nome: '', codigo: '', icone: '', cor: '#0ab39c', status: true });

const opcaoForm = useForm({
  opcoes: []
});

const assignMedicoModal = ref(false);
const salaSelecionada = ref(null);
const assignForm = useForm({
  nome: '', // Needed for validation in SalaController@update
  status: true,
  pessoa_id: null
});

const openAssignModal = (item) => {
  const fullItem = currentClinica.value.salas.find(s => s.id === item.id);
  salaSelecionada.value = fullItem;
  assignForm.nome = fullItem.nome;
  assignForm.status = fullItem.status;
  assignForm.pessoa_id = fullItem.pessoa_id || null;
  assignMedicoModal.value = true;
};

const saveAssignMedico = () => {
  // Define o status automaticamente: true (Disponível) se nenhum médico, false (Ocupada) se tiver médico
  assignForm.status = assignForm.pessoa_id ? false : true;

  assignForm.put(route('salas.update', salaSelecionada.value.id), {
    onSuccess: () => {
      assignMedicoModal.value = false;
    }
  });
};

const openModalOpcoes = (item) => {
  const fullItem = currentClinica.value.totens.find(t => t.id === item.id);
  totemSelecionado.value = fullItem;
  localOpcoes.value = JSON.parse(JSON.stringify(fullItem?.opcoes || []));
  opcaoForm.reset();
  opcaoForm.clearErrors();
  opcoesModal.value = true;
};

const addOpcaoLocal = () => {
  if (!newOpcao.value.nome) {
    Swal.fire("Atenção", "O nome da opção é obrigatório.", "warning");
    return;
  }
  localOpcoes.value.push({
    id: null,
    nome: newOpcao.value.nome,
    codigo: newOpcao.value.codigo,
    icone: newOpcao.value.icone,
    cor: newOpcao.value.cor,
    status: newOpcao.value.status
  });
  newOpcao.value = { nome: '', codigo: '', icone: '', cor: '#0ab39c', status: true };
};

const removeOpcaoLocal = (index) => {
  localOpcoes.value.splice(index, 1);
};

const syncOpcoes = () => {
  if (!totemSelecionado.value) return;

  opcaoForm.opcoes = localOpcoes.value;

  opcaoForm.post(route('totens.opcoes.sync', totemSelecionado.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      opcoesModal.value = false;
    }
  });
};

const clinicasFormatadas = computed(() => {
  return props.clinicas.map(c => ({
    ...c,
    status: c.ativo ? 'ativo' : 'inativo'
  }));
});

const currentClinica = computed(() => {
  return props.clinicas.find(c => c.id === form.id) || {};
});

const getItems = (type) => {
  const items = currentClinica.value[type] || [];
  if (type === 'salas') {
    return items.map(item => ({
      id: item.id,
      nome: item.nome || item.name,
      medico: item.profissional_saude ? item.profissional_saude.nome : 'Nenhum',
      status: item.status ? 'Disponível' : 'Ocupada'
    }));
  }
  if (type === 'guiches') {
    return items.map(item => ({
      id: item.id,
      nome: item.nome || item.name,
      hostname: item.hostname || '-',
      status: item.status ? 'Ativo' : 'Inativo'
    }));
  }
  return items.map(item => ({
    id: item.id,
    nome: item.nome || item.name,
    status: item.status ? 'Ativo' : 'Inativo'
  }));
};

const openModalAdd = () => {
  isEditing.value = false;
  form.reset();
  activeTab.value = 0;
  showModal.value = true;
};

const openModalEdit = (id) => {
  const clinica = props.clinicas.find(c => String(c.id) === String(id));
  if (clinica) {
    isEditing.value = true;
    form.id = clinica.id;
    form.name = clinica.name;
    form.cnpj = clinica.cnpj || '';
    form.cnes = clinica.cnes || '';
    form.endereco = clinica.endereco || '';
    form.telefone = clinica.telefone || '';
    form.email = clinica.email || '';
    form.ativo = clinica.ativo === undefined ? true : clinica.ativo;
    activeTab.value = 0;
    showModal.value = true;
  }
};

const saveClinica = () => {
  if (isEditing.value) {
    form.put(route('clinicas.update', form.id), {
      onSuccess: () => {
        showModal.value = false;
      }
    });
  } else {
    form.post(route('clinicas.store'), {
      onSuccess: () => {
        showModal.value = false;
      }
    });
  }
};

const deleteClinica = (id) => {
  const clinica = props.clinicas.find(c => String(c.id) === String(id));
  if (clinica) {
    clinicaToDelete.value = { id: clinica.id, nome: clinica.name };
    deleteModal.value = true;
  }
};

const confirmDelete = () => {
  if (clinicaToDelete.value) {
    router.delete(route('clinicas.destroy', clinicaToDelete.value.id), {
      onSuccess: () => {
        deleteModal.value = false;
        clinicaToDelete.value = null;
      }
    });
  }
};

const switchAccount = (id) => {
  router.post(route('conta.switch', id));
};
</script>

<template>
  <Layout>
    <PageHeader title="Gerenciar Clínicas" pageTitle="Clínicas" />

    <TableGrid :columns="columns" :data="clinicasFormatadas" :tableTitle="'Lista de Clínicas'" :showStatus="true"
      :actionsConfig="{ edit: true, delete: true, show: true }" :actionsLabels="{ show: 'Acessar' }"
      :actionsIcons="{ show: 'ri-login-box-line' }" @add="openModalAdd" @edit="openModalEdit" @delete="deleteClinica"
      @show="switchAccount" />

    <!-- Modal de Cadastro / Edição -->
    <Modal v-model="showModal" size="xl" :title="isEditing ? 'Editar Clínica' : 'Cadastrar Clínica'"
      :name-button="isEditing ? 'Salvar Alterações' : 'Cadastrar Clínica'" :processing="form.processing"
      @save="saveClinica">
      <BTabs v-model="activeTab" nav-class="nav-tabs-custom nav-success mb-3">
        <!-- Aba 1: Informações -->
        <BTab title="Informações" active>
          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">Nome da Clínica <span class="text-danger">*</span></label>
              <input type="text" class="form-control" v-model="form.name" placeholder="Ex: Clínica Saúde e Vida" required />
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">CNPJ</label>
              <input type="text" class="form-control" v-model="form.cnpj" v-mask="'##.###.###/####-##'" maxlength="18" placeholder="00.000.000/0000-00" />
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">CNES</label>
              <input type="text" class="form-control" v-model="form.cnes" v-mask="'#######'" maxlength="7" placeholder="Ex: 1234567" />
            </div>
            <div class="col-md-12 mb-3">
              <label class="form-label">Endereço</label>
              <input type="text" class="form-control" v-model="form.endereco" placeholder="Ex: Rua das Flores, 123, Centro - São Paulo/SP" />
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Telefone</label>
              <input type="text" class="form-control" v-model="form.telefone" v-mask="'(##) #####-####'" placeholder="(11) 99999-9999" />
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">E-mail</label>
              <input type="email" class="form-control" v-model="form.email" placeholder="contato@clinica.com.br" />
            </div>
            <div class="col-md-12 mb-3">
              <div class="form-check form-switch form-switch-success">
                <input class="form-check-input" type="checkbox" id="ativoSwitch" v-model="form.ativo"
                  :checked="form.ativo">
                <label class="form-check-label" for="ativoSwitch">Clínica Ativa</label>
              </div>
            </div>
          </div>
        </BTab>

        <!-- Aba 2: Totens -->
        <BTab title="Totens" :disabled="!isEditing">
          <div v-if="!isEditing" class="alert alert-warning">
            Salve a clínica primeiro para desbloquear o gerenciamento de Totens.
          </div>
          <div v-else>
            <TableGrid :columns="genericColumns" :data="getItems('totens')" :tableTitle="'Totens desta Clínica'"
              :showStatus="false" :compactSpacing="true" :searchPlaceholder="'Buscar totem'"
              :actionsConfig="{ edit: true, delete: true, procedure: true }"
              :actionsLabels="{ procedure: 'Gerenciar Opções' }" :actionsIcons="{ procedure: 'ri-list-settings-line' }"
              @add="openSubModal('Totem')" @edit="(id, item) => openSubModal('Totem', item)"
              @delete="(item) => openSubDeleteModal('Totem', item)" @procedure="(id, item) => openModalOpcoes(item)" />
          </div>
        </BTab>

        <!-- Aba 3: Painéis -->
        <BTab title="Painéis" :disabled="!isEditing">
          <div v-if="!isEditing" class="alert alert-warning">
            Salve a clínica primeiro para desbloquear o gerenciamento de Painéis.
          </div>
          <div v-else>
            <TableGrid :columns="genericColumns" :data="getItems('paineis')" :tableTitle="'Painéis desta Clínica'"
              :showStatus="false" :compactSpacing="true" :searchPlaceholder="'Buscar painel'"
              :actionsConfig="{ edit: true, delete: true }" @add="openSubModal('Painel')"
              @edit="(id, item) => openSubModal('Painel', item)"
              @delete="(item) => openSubDeleteModal('Painel', item)" />
          </div>
        </BTab>

        <!-- Aba 4: Guichês -->
        <BTab title="Guichês" :disabled="!isEditing">
          <div v-if="!isEditing" class="alert alert-warning">
            Salve a clínica primeiro para desbloquear o gerenciamento de Guichês.
          </div>
          <div v-else>
            <TableGrid :columns="guichesColumns" :data="getItems('guiches')" :tableTitle="'Guichês desta Clínica'"
              :showStatus="false" :compactSpacing="true" :searchPlaceholder="'Buscar guichê'"
              :actionsConfig="{ edit: true, delete: true }" @add="openSubModal('Guichê')"
              @edit="(id, item) => openSubModal('Guichê', item)"
              @delete="(item) => openSubDeleteModal('Guichê', item)" />
          </div>
        </BTab>

        <!-- Aba 5: Salas -->
        <BTab title="Salas" :disabled="!isEditing">
          <div v-if="!isEditing" class="alert alert-warning">
            Salve a clínica primeiro para desbloquear o gerenciamento de Salas.
          </div>
          <div v-else>
            <TableGrid :columns="salasColumns" :data="getItems('salas')" :tableTitle="'Salas desta Clínica'"
              :showStatus="false" :compactSpacing="true" :searchPlaceholder="'Buscar sala'"
              :actionsConfig="{ edit: true, delete: true, procedure: true }"
              :actionsLabels="{ procedure: 'Atribuir Médico' }" :actionsIcons="{ procedure: 'ri-user-add-line' }"
              @add="openSubModal('Sala')" @edit="(id, item) => openSubModal('Sala', item)"
              @delete="(item) => openSubDeleteModal('Sala', item)" @procedure="(id, item) => openAssignModal(item)" />
          </div>
        </BTab>
      </BTabs>
    </Modal>

    <!-- Modal de Exclusão -->
    <ModalDelete v-model="deleteModal" :item-delete="clinicaToDelete" @confirm-delete="confirmDelete" />

    <!-- Modal de Adicionar Item (Totem/Painel/Guiche/Sala) -->
    <Modal v-model="showSubModal" size="md" :title="subModalTitle" :name-button="'Adicionar'"
      :processing="subForm.processing" @save="saveSubItem" :z-index="1060" :backdrop-z-index="1055">
      <div class="mb-3">
        <label class="form-label">Nome / Identificação</label>
        <input type="text" class="form-control" v-model="subForm.nome" :placeholder="'Ex: ' + subModalType + ' Principal, ' + subModalType + ' 01'" required />
      </div>
      <div class="mb-3" v-if="subModalType === 'Guichê'">
        <label class="form-label">Hostname</label>
        <input type="text" class="form-control" v-model="subForm.hostname" placeholder="Nome do computador" />
      </div>
      <div class="mb-3">
        <div class="form-check form-switch form-switch-success">
          <input class="form-check-input" type="checkbox" id="subAtivoSwitch" v-model="subForm.status">
          <label class="form-check-label" for="subAtivoSwitch">Ativo</label>
        </div>
      </div>
    </Modal>

    <!-- Modal de Exclusão de Subitens -->
    <ModalDelete v-model="subDeleteModal" :title="`Excluir ${subItemToDelete?.type}`"
      :subTitle="`Tem certeza que deseja excluir '${subItemToDelete?.nome}'?`" :item-delete="subItemToDelete"
      @save="confirmSubDelete" :z-index="1070" :backdrop-z-index="1065" />

    <!-- Modal de Opções do Totem -->
    <Modal v-model="opcoesModal" :title="`Opções do Totem: ${totemSelecionado?.nome}`" size="lg" :show-footer="true"
      name-button="Salvar" :processing="opcaoForm.processing" @save="syncOpcoes" :z-index="1060"
      :backdrop-z-index="1055">
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label">Nome da Opção <span class="text-danger">*</span></label>
          <input type="text" class="form-control" v-model="newOpcao.nome"
            :class="{ 'is-invalid': opcaoForm.errors.nome }" placeholder="Ex: Preferencial" />
        </div>
        <div class="col-md-3">
          <label class="form-label">Código (Sigla)</label>
          <input type="text" class="form-control" v-model="newOpcao.codigo" placeholder="Ex: PREF" maxlength="10" />
        </div>
        <div class="col-md-2">
          <label class="form-label w-100 d-flex justify-content-between align-items-center">
            <span>Ícone</span>
            <a href="https://remixicon.com/" target="_blank" class="text-primary small text-decoration-underline"
              tabindex="-1" title="Ver lista de ícones">Catálogo <i class="ri-external-link-line"></i></a>
          </label>
          <input type="text" class="form-control" v-model="newOpcao.icone" placeholder="Ex: ri-heart-line" />
        </div>
        <div class="col-md-1">
          <label class="form-label">Cor</label>
          <input type="color" class="form-control form-control-color w-100" v-model="newOpcao.cor">
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button type="button" class="btn btn-primary w-100" @click="addOpcaoLocal">Adicionar</button>
        </div>
      </div>

      <SimpleTable variant="borderless" compact tableClass="table-bordered align-middle" :items="localOpcoes"
        :columns="opcoesColumns" emptyTitle="Nenhuma opção" emptyMessage="Nenhuma opção cadastrada para este totem.">
        <template #body="{ items, columns }">
          <tr v-for="(opcao, index) in items" :key="index">
            <td>
              <input type="text" class="form-control form-control-sm" v-model="opcao.nome" />
            </td>
            <td>
              <input type="text" class="form-control form-control-sm" v-model="opcao.codigo" />
            </td>
            <td>
              <input type="text" class="form-control form-control-sm" v-model="opcao.icone"
                placeholder="ri-heart-line" />
            </td>
            <td>
              <input type="color" class="form-control form-control-color form-control-sm p-0 border-0"
                v-model="opcao.cor" style="width: 30px; height: 30px; cursor: pointer;">
            </td>
            <td>
              <div class="form-check form-switch mt-1">
                <input class="form-check-input" type="checkbox" v-model="opcao.status" :id="'status_' + index">
                <label class="form-check-label" :for="'status_' + index">{{ opcao.status ? 'Ativo' : 'Inativo'
                  }}</label>
              </div>
            </td>
            <td class="text-center">
              <button type="button" class="btn btn-sm btn-soft-danger" @click="removeOpcaoLocal(index)"
                title="Excluir Opção">
                <i class="ri-delete-bin-line"></i>
              </button>
            </td>
          </tr>
        </template>
      </SimpleTable>
    </Modal>

    <!-- Modal de Atribuir Médico -->
    <Modal v-model="assignMedicoModal" :title="`Atribuir Médico à Sala: ${salaSelecionada?.nome}`" size="md"
      :show-footer="true" name-button="Salvar" :processing="assignForm.processing" @save="saveAssignMedico"
      :z-index="1060" :backdrop-z-index="1055">
      <div class="mb-3">
        <label class="form-label">Profissional de Saúde</label>
        <Multiselect v-model="assignForm.pessoa_id" :options="profissionaisOptions" :searchable="true"
          placeholder="Selecione um profissional" />
      </div>
    </Modal>
  </Layout>
</template>
