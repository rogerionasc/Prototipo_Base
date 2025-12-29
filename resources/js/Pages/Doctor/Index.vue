<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue';
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Modal from "@/Components/Modal.vue";
import ModalDelete from "@/Components/ModalDelete.vue";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.min.css";
import "flatpickr/dist/l10n/pt.js";
const flatpickrOptions = { altInput: true, altFormat: "d M, Y", dateFormat: "Y-m-d", locale: "pt" };
const props = defineProps({
  profissionais: { type: Array, default: () => [] },
  especialidades: { type: Array, default: () => [] },
  estadosCivis: { type: Array, default: () => [] },
});
const profissionaisLocal = ref([...(props.profissionais || [])]);
watch(() => props.profissionais, (v) => { profissionaisLocal.value = [...(v || [])]; });
const columns = [
  { id: "id", name: "ID" },
  { id: "nome", name: "Nome" },
  { id: "crm", name: "CRM" },
  { id: "contato", name: "Contato" },
  { id: "especialidades", name: "Especialidades" },
];
const tableData = computed(() => {
  return (profissionaisLocal.value || []).map(p => {
    const esp = (p.especialidades || []).map(e => {
      const qre = e?.pivot?.qre ? ` (${e.pivot.qre})` : '';
      return `${e.nome}${qre}`;
    }).join(', ');
    const contato = [p.email, p.celular || p.telefone].filter(Boolean).join(' | ');
    return { id: p.id, nome: p.nome, crm: p.crm || '-', contato: contato || '-', especialidades: esp || '-' };
  });
});
const singleModal = ref(false);
const isCreating = ref(false);
const deleteModal = ref(false);
const deleteItem = ref({});
const saveProcessing = ref(false);
const editProcessing = ref(false);
const deleteSubTitle = computed(() => {
  const name = deleteItem.value && deleteItem.value.nome ? deleteItem.value.nome : null;
  return name ? 'Deseja realmente excluir "' + name + '"?' : 'Deseja realmente excluir';
});
const sexoOptions = [
  { value: 'Masculino', label: 'Masculino' },
  { value: 'Feminino', label: 'Feminino' },
  { value: 'Outro', label: 'Outro' },
];
const sexoSelectCreate = ref(null);
const estadoCivilSelectCreate = ref(null);
const sexoSelectEdit = ref(null);
const estadoCivilSelectEdit = ref(null);
const formCreate = useForm({
  nome: "",
  cpf: "",
  rg: "",
  sexo: "",
  data_nascimento: "",
  naturalidade: "",
  estado_civil_id: null,
  cnes: "",
  crm: "",
  cep: "",
  endereco: "",
  numero: "",
  bairro: "",
  cidade: "",
  complemento: "",
  email: "",
  telefone: "",
  celular: "",
  observacoes: "",
  especialidades: [],
});
const formEdit = useForm({
  nome: "",
  cpf: "",
  rg: "",
  sexo: "",
  data_nascimento: "",
  naturalidade: "",
  estado_civil_id: null,
  cnes: "",
  crm: "",
  cep: "",
  endereco: "",
  numero: "",
  bairro: "",
  cidade: "",
  complemento: "",
  email: "",
  telefone: "",
  celular: "",
  observacoes: "",
  especialidades: [],
});
const editingId = ref(null);
function openAdd() {
  formCreate.reset();
  formCreate.clearErrors();
  formCreate.especialidades = [];
  isCreating.value = true;
  singleModal.value = true;
  nextTick(() => { if (window.initChoices) window.initChoices(); });
}
const FORM_FIELDS = [
  "nome","cpf","rg","sexo","data_nascimento","naturalidade","estado_civil_id","cnes","crm","cep","endereco","numero","bairro","cidade","complemento","email","telefone","celular","observacoes"
];
function buildPayload(src) {
  const o = {};
  FORM_FIELDS.forEach(k => { o[k] = src[k]; });
  o.especialidades = (src.especialidades || []).filter(x => x.id);
  return o;
}
function fillForm(targetForm, row) {
  FORM_FIELDS.forEach(k => {
    const val = row[k];
    targetForm[k] = k === "estado_civil_id" ? (val || null) : (val || "");
  });
  targetForm.especialidades = (row.especialidades || []).map(e => ({ id: e.id, qre: e?.pivot?.qre || "" }));
}
function mapEspData(targetForm) {
  return (targetForm.especialidades || []).map(s => ({
    id: s.id,
    especialidade: espMap.value[s.id] || "-",
    qre: s.qre || "",
  }));
}
function attachChoiceSync(formObj, key, selectRef, mapper) {
  watch(() => formObj[key], async (v) => {
    await nextTick();
    if (window.syncChoiceValue && selectRef.value) {
      const val = mapper ? mapper(v) : (v != null ? String(v) : "");
      window.syncChoiceValue(selectRef.value, val);
    }
  }, { immediate: true });
}
attachChoiceSync(formCreate, "sexo", sexoSelectCreate, v => v || "");
attachChoiceSync(formCreate, "estado_civil_id", estadoCivilSelectCreate, v => v != null ? String(v) : "");
function addEspRow(targetForm) {
  const arr = targetForm.especialidades || [];
  arr.push({ id: null, qre: "" });
  targetForm.especialidades = [...arr];
  nextTick(() => { if (window.initChoices) window.initChoices(); });
}
function removeEspRow(targetForm, idx) {
  const arr = targetForm.especialidades || [];
  arr.splice(idx, 1);
  targetForm.especialidades = [...arr];
}
const espMap = computed(() => {
  const m = {};
  (props.especialidades || []).forEach(e => { m[e.id] = e.nome; });
  return m;
});
const espColumns = [
  { id: "id", name: "ID" },
  { id: "especialidade", name: "Especialidade" },
  { id: "qre", name: "QRE" },
];
const espDataCreate = computed(() => mapEspData(formCreate));
const espDataEdit = computed(() => mapEspData(formEdit));
function removeEspById(targetForm, id) {
  const arr = targetForm.especialidades || [];
  const idx = arr.findIndex(x => String(x.id) === String(id));
  if (idx !== -1) {
    arr.splice(idx, 1);
    targetForm.especialidades = [...arr];
  }
}
function onEspDeleteCreate(rowObj) { removeEspById(formCreate, rowObj?.id); }
function onEspDeleteEdit(rowObj) { removeEspById(formEdit, rowObj?.id); }
const espQreModal = ref(false);
const espQreTargetForm = ref(null);
const espQreId = ref(null);
const espQreValue = ref("");
const espQreNome = ref("");
function openEspQre(targetForm, id) {
  espQreTargetForm.value = targetForm;
  espQreId.value = id;
  const arr = targetForm.especialidades || [];
  const item = arr.find(x => String(x.id) === String(id));
  espQreValue.value = item?.qre || "";
  espQreNome.value = espMap.value[id] || "";
  espQreModal.value = true;
}
function onEspEditCreate(id) { openEspQre(formCreate, id); }
function onEspEditEdit(id) { openEspQre(formEdit, id); }
function saveEspQreEdit() {
  const tf = espQreTargetForm.value;
  const id = espQreId.value;
  if (!tf || !id) { espQreModal.value = false; return; }
  const arr = tf.especialidades || [];
  const idx = arr.findIndex(x => String(x.id) === String(id));
  if (idx !== -1) {
    arr[idx] = { ...arr[idx], qre: espQreValue.value || "" };
    tf.especialidades = [...arr];
  }
  espQreModal.value = false;
}
const espAddModal = ref(false);
const espAddTargetForm = ref(null);
const espAddId = ref(null);
const espAddQre = ref("");
const espAddSelectRef = ref(null);
const availableEspOptions = computed(() => {
  const selectedIds = ((espAddTargetForm.value?.especialidades) || []).map(x => String(x.id));
  return (props.especialidades || []).filter(e => !selectedIds.includes(String(e.id)));
});
watch(() => espAddId.value, async (v) => {
  await nextTick();
  if (window.syncChoiceValue && espAddSelectRef.value) window.syncChoiceValue(espAddSelectRef.value, v != null ? String(v) : "");
}, { immediate: true });
function openEspAdd(targetForm) {
  espAddTargetForm.value = targetForm;
  espAddId.value = null;
  espAddQre.value = "";
  espAddModal.value = true;
  nextTick(() => {
    if (window.initChoices) window.initChoices();
    if (espAddSelectRef.value) {
      espAddSelectRef.value.addEventListener("change", (e) => { espAddId.value = e?.target?.value ?? espAddId.value; });
      if (window.syncChoiceValue) window.syncChoiceValue(espAddSelectRef.value, espAddId.value != null ? String(espAddId.value) : "");
    }
  });
}
function addEspConfirm() {
  const tf = espAddTargetForm.value;
  if (!tf || !espAddId.value) { espAddModal.value = false; return; }
  const arr = tf.especialidades || [];
  if (arr.some(x => String(x.id) === String(espAddId.value))) { espAddModal.value = false; return; }
  arr.push({ id: espAddId.value, qre: espAddQre.value || "" });
  tf.especialidades = [...arr];
  espAddModal.value = false;
}
function submitCreate() {
  const payload = buildPayload(formCreate);
  useForm(payload).post("/profissionais-saude", {
    preserveScroll: true,
    onStart: () => { saveProcessing.value = true; },
    onFinish: () => { saveProcessing.value = false; },
    onSuccess: () => {
      singleModal.value = false;
      router.reload({ only: ['profissionais'] });
    }
  });
}
function startEditById(id) {
  const p = (profissionaisLocal.value || []).find(x => String(x.id) === String(id));
  if (!p) return;
  formEdit.reset();
  formEdit.clearErrors();
  editingId.value = p.id;
  fillForm(formEdit, p);
  isCreating.value = false;
  singleModal.value = true;
  nextTick(() => { if (window.initChoices) window.initChoices(); });
}
attachChoiceSync(formEdit, "sexo", sexoSelectEdit, v => v || "");
attachChoiceSync(formEdit, "estado_civil_id", estadoCivilSelectEdit, v => v != null ? String(v) : "");
function submitEdit() {
  if (!editingId.value) return;
  const payload = buildPayload(formEdit);
  useForm(payload).put(`/profissionais-saude/${editingId.value}`, {
    preserveScroll: true,
    onStart: () => { editProcessing.value = true; },
    onFinish: () => { editProcessing.value = false; },
    onSuccess: () => {
      singleModal.value = false;
      editingId.value = null;
      router.reload({ only: ['profissionais'] });
    }
  });
}
function saveSingle() {
  if (isCreating.value) {
    submitCreate();
  } else {
    submitEdit();
  }
}
function askDelete(id) {
  const rowObj = id;
  const p = rowObj && typeof rowObj === 'object'
    ? rowObj
    : (profissionaisLocal.value || []).find(x => String(x.id) === String(id));
  deleteItem.value = { id: p?.id, nome: p?.nome };
  deleteModal.value = true;
}
function confirmDelete() {
  const id = deleteItem.value?.id;
  if (!id) { deleteModal.value = false; return; }
  const f = useForm({});
  f.delete(`/profissionais-saude/${id}`, {
    preserveScroll: true,
    onSuccess: () => {
      deleteModal.value = false;
      deleteItem.value = {};
      profissionaisLocal.value = (profissionaisLocal.value || []).filter(x => String(x.id) !== String(id));
      router.reload({ only: ['profissionais'] });
    }
  });
}
</script>

<template>
  <Layout>
    <Head title="Médicos" />
    <PageHeader title="Médicos" pageTitle="Menu"/>
    <TableGrid
      :columns="columns"
      :data="tableData"
      :tableTitle="'Lista de Profissionais de Saúde'"
      :search="true"
      :searchPlaceholder="'Buscar por profissional'"
      :showCheckbox="true"
      :showActions="true"
      :showPerPagination="true"
      :showAddButton="true"
      @add="openAdd"
      @delete="askDelete"
      @edit="startEditById"
      @show="() => {}"
    />
    <Modal
      v-model="singleModal"
      :title="isCreating ? 'Adicionar Profissional' : 'Editar Profissional'"
      :name-button="isCreating ? 'Salvar' : 'Atualizar'"
      :processing="isCreating ? saveProcessing : editProcessing"
      @save="saveSingle"
    >
      <template v-if="isCreating">
        <BTabs nav-class="nav-tabs-custom text-muted">
          <BTab title="Dados Pessoais">
            <div class="row g-3 mt-2">
              <div class="col-md-6">
                <label for="psNome" class="form-label">Nome</label>
                <span class="text-danger ms-1">*</span>
                <input v-model="formCreate.nome" type="text" id="psNome" class="form-control" :class="{ 'is-invalid': formCreate.errors.nome }" placeholder="Nome completo" required maxlength="120" />
                <div class="invalid-feedback">{{ formCreate.errors.nome || 'Informe o nome.' }}</div>
              </div>
              <div class="col-md-3">
                <label for="psCpf" class="form-label">CPF</label>
                <input v-model="formCreate.cpf" v-mask="'###.###.###-##'" type="text" id="psCpf" class="form-control" placeholder="000.000.000-00" maxlength="14" />
              </div>
              <div class="col-md-3">
                <label for="psRg" class="form-label">RG</label>
                <input v-model="formCreate.rg" v-mask="'###.###.###'" type="text" id="psRg" class="form-control" placeholder="000.000.000" maxlength="11" />
              </div>
              <div class="col-md-3">
                <label for="psSexo" class="form-label">Sexo</label>
                <select v-model="formCreate.sexo" data-choices class="form-select mb-0" id="psSexo" ref="sexoSelectCreate">
                  <option selected disabled value="">Selecione...</option>
                  <option v-for="s in sexoOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
              </div>
              <div class="col-md-3">
                <label for="psNascimento" class="form-label">Data de Nascimento</label>
                <flatPickr v-model="formCreate.data_nascimento" id="psNascimento" class="form-control" :config="flatpickrOptions" placeholder="Selecione a data" />
              </div>
              <div class="col-md-3">
                <label for="psNaturalidade" class="form-label">Naturalidade</label>
                <input v-model="formCreate.naturalidade" type="text" id="psNaturalidade" class="form-control" placeholder="Brasileira" maxlength="60" />
              </div>
              <div class="col-md-3">
                <label for="psEstadoCivil" class="form-label">Estado Civil</label>
                <select v-model="formCreate.estado_civil_id" class="form-select mb-0" id="psEstadoCivil" data-choices ref="estadoCivilSelectCreate">
                  <option selected disabled :value="null">Selecione...</option>
                  <option v-for="e in props.estadosCivis" :key="e.id" :value="e.id">{{ e.descricao }}</option>
                </select>
              </div>
              <div class="col-md-3">
                <label for="psCrm" class="form-label">CRM</label>
                <input v-mask="'CRM/AA #####'" v-model="formCreate.crm" type="text" id="psCrm" class="form-control" placeholder="CRM/RN #####" maxlength="12" />
              </div>
              <div class="col-md-3">
                <label for="psCnes" class="form-label">CNES</label>
                <input v-model="formCreate.cnes" type="text" id="psCnes" class="form-control" placeholder="CNES" maxlength="7" inputmode="numeric" />
              </div>
              <div class="col-md-6">
                <label for="psEmail" class="form-label">E-mail</label>
                <input v-model="formCreate.email" type="email" id="psEmail" class="form-control" placeholder="email@dominio.com" maxlength="100" />
              </div>
              <div class="col-md-6">
                <label for="psTelefone" class="form-label">Telefone</label>
                <input v-model="formCreate.telefone" v-mask="'(##) ##### ####'" type="text" id="psTelefone" class="form-control" placeholder="(00) 0000-0000" maxlength="15" />
              </div>
              <div class="col-md-6">
                <label for="psCelular" class="form-label">Celular</label>
                <input v-model="formCreate.celular" v-mask="'(##) #####-####'" type="text" id="psCelular" class="form-control" placeholder="(00) 00000-0000" maxlength="15" />
              </div>
              <div class="col-md-12 mt-2">
                <div class="border border-dashed rounded p-3 bg-light-subtle mb-3">
                  <BRow class="g-3 align-items-center mb-2">
                    <BCol md="9">
                      <div class="d-flex align-items-center">
                      </div>
                    </BCol>
                    <BCol md="3" class="text-end">
                      <a href="javascript:void(0);" class="link-primary text-nowrap" @click="openEspAdd(formCreate)">
                        <i class="ri-add-line align-bottom me-1"></i>Adicionar Especialidade
                      </a>
                    </BCol>
                  </BRow>
                  <BRow class="g-3">
                    <BCol md="12">
                      <table v-if="(formCreate.especialidades || []).length > 0" class="table table-hover mb-0">
                        <thead class="table-light">
                          <tr>
                            <th>Especialidade</th>
                            <th>QRE</th>
                            <th class="text-end">Ações</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="s in formCreate.especialidades" :key="String(s.id)">
                            <td>{{ espMap[s.id] || "-" }}</td>
                            <td>{{ s.qre || "" }}</td>
                            <td class="text-end">
                              <button class="btn btn-sm btn-soft-info me-2" type="button" @click="onEspEditCreate(s.id)">
                                <i class="ri-pencil-fill align-bottom me-1"></i> Editar
                              </button>
                              <button class="btn btn-sm btn-soft-danger" type="button" @click="removeEspById(formCreate, s.id)">
                                <i class="ri-delete-bin-5-fill align-bottom me-1"></i> Excluir
                              </button>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </BCol>
                  </BRow>
                </div>
              </div>
              <div class="col-md-12">
                <label for="psObservacoes" class="form-label">Observações</label>
                <textarea v-model="formCreate.observacoes" id="psObservacoes" class="form-control" rows="3" placeholder="Anotações gerais" maxlength="500"></textarea>
              </div>
            </div>
          </BTab>
          <BTab title="Endereço">
            <div class="row g-3 mt-2">
              <div class="col-md-3">
                <label for="psCep" class="form-label">CEP</label>
                <input v-model="formCreate.cep" type="text" id="psCep" class="form-control" placeholder="00000-000" maxlength="9" />
              </div>
              <div class="col-md-6">
                <label for="psEnderecoText" class="form-label">Endereço</label>
                <input v-model="formCreate.endereco" type="text" id="psEnderecoText" class="form-control" placeholder="Rua, Avenida, Travessa" maxlength="120" />
              </div>
              <div class="col-md-3">
                <label for="psNumero" class="form-label">Número</label>
                <input v-model="formCreate.numero" type="text" id="psNumero" class="form-control" placeholder="Número" maxlength="10" />
              </div>
              <div class="col-md-4">
                <label for="psBairro" class="form-label">Bairro</label>
                <input v-model="formCreate.bairro" type="text" id="psBairro" class="form-control" placeholder="Bairro" maxlength="60" />
              </div>
              <div class="col-md-4">
                <label for="psCidade" class="form-label">Cidade</label>
                <input v-model="formCreate.cidade" type="text" id="psCidade" class="form-control" placeholder="Cidade" maxlength="60" />
              </div>
              <div class="col-md-4">
                <label for="psComplemento" class="form-label">Complemento</label>
                <input v-model="formCreate.complemento" type="text" id="psComplemento" class="form-control" placeholder="Apartamento, Bloco, etc." maxlength="60" />
              </div>
            </div>
          </BTab>
        </BTabs>
      </template>
      <template v-else>
        <BTabs nav-class="nav-tabs-custom text-muted">
          <BTab title="Dados Pessoais">
            <div class="row g-3 mt-2">
              <div class="col-md-6">
                <label for="psEditNome" class="form-label">Nome</label>
                <span class="text-danger ms-1">*</span>
                <input v-model="formEdit.nome" type="text" id="psEditNome" class="form-control" :class="{ 'is-invalid': formEdit.errors.nome }" placeholder="Nome completo" required maxlength="120" />
                <div class="invalid-feedback">{{ formEdit.errors.nome || 'Informe o nome.' }}</div>
              </div>
              <div class="col-md-3">
                <label for="psEditCpf" class="form-label">CPF</label>
                <input v-model="formEdit.cpf" v-mask="'###.###.###-##'" type="text" id="psEditCpf" class="form-control" placeholder="000.000.000-00" maxlength="14" />
              </div>
              <div class="col-md-3">
                <label for="psEditRg" class="form-label">RG</label>
                <input v-model="formEdit.rg" v-mask="'###.###.###'" type="text" id="psEditRg" class="form-control" placeholder="000.000.000" maxlength="11" />
              </div>
              <div class="col-md-3">
                <label for="psEditSexo" class="form-label">Sexo</label>
                <select v-model="formEdit.sexo" data-choices class="form-select mb-0" id="psEditSexo" ref="sexoSelectEdit">
                  <option selected disabled value="">Selecione...</option>
                  <option v-for="s in sexoOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
              </div>
              <div class="col-md-3">
                <label for="psEditNascimento" class="form-label">Data de Nascimento</label>
                <flatPickr v-model="formEdit.data_nascimento" id="psEditNascimento" class="form-control" :config="flatpickrOptions" placeholder="Selecione a data" />
              </div>
              <div class="col-md-3">
                <label for="psEditNaturalidade" class="form-label">Naturalidade</label>
                <input v-model="formEdit.naturalidade" type="text" id="psEditNaturalidade" class="form-control" placeholder="Brasileira" maxlength="60" />
              </div>
              <div class="col-md-3">
                <label for="psEditEstadoCivil" class="form-label">Estado Civil</label>
                <select v-model="formEdit.estado_civil_id" class="form-select mb-0" id="psEditEstadoCivil" data-choices ref="estadoCivilSelectEdit">
                  <option selected disabled :value="null">Selecione...</option>
                  <option v-for="e in props.estadosCivis" :key="e.id" :value="e.id">{{ e.descricao }}</option>
                </select>
              </div>
              <div class="col-md-3">
                <label for="psEditCrm" class="form-label">CRM</label>
                <input v-mask="'CRM/AA #####'" v-model="formEdit.crm" type="text" id="psEditCrm" class="form-control" placeholder="CRM/XX #####" maxlength="12" />
              </div>
              <div class="col-md-3">
                <label for="psEditCnes" class="form-label">CNES</label>
                <input v-mask="'#######'" v-model="formEdit.cnes" type="text" id="psEditCnes" class="form-control" placeholder="0000000" maxlength="7" />
              </div>
              <div class="col-md-6">
                <label for="psEditEmail" class="form-label">E-mail</label>
                <input v-model="formEdit.email" type="email" id="psEditEmail" class="form-control" placeholder="email@dominio.com" maxlength="100" />
              </div>
              <div class="col-md-6">
                <label for="psEditTelefone" class="form-label">Telefone</label>
                <input v-model="formEdit.telefone" v-mask="'(##) ##### ####'" type="text" id="psEditTelefone" class="form-control" placeholder="(00) 0000-0000" maxlength="15" />
              </div>
              <div class="col-md-6">
                <label for="psEditCelular" class="form-label">Celular</label>
                <input v-model="formEdit.celular" v-mask="'(##) #####-####'" type="text" id="psEditCelular" class="form-control" placeholder="(00) 00000-0000" maxlength="15" />
              </div>
              <div class="col-md-12 mt-2">
                <div class="border border-dashed rounded p-3 bg-light-subtle mb-3">
                  <BRow class="g-3 align-items-center mb-2">
                    <BCol md="9">
                      <div class="d-flex align-items-center">
                      </div>
                    </BCol>
                    <BCol md="3" class="text-end">
                      <a href="javascript:void(0);" class="link-primary text-nowrap" @click="openEspAdd(formEdit)">
                        <i class="ri-add-line align-bottom me-1"></i>Adicionar Especialidade
                      </a>
                    </BCol>
                  </BRow>
                  <BRow class="g-3">
                    <BCol md="12">
                    <table v-if="(formEdit.especialidades || []).length > 0" class="table table-hover mb-0">
                      <thead class="table-light">
                        <tr>
                          <th>Especialidade</th>
                          <th>QRE</th>
                          <th class="text-end">Ações</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="s in formEdit.especialidades" :key="String(s.id)">
                          <td>{{ espMap[s.id] || "-" }}</td>
                          <td>{{ s.qre || "" }}</td>
                          <td class="text-end">
                            <button class="btn btn-sm btn-soft-info me-2" type="button" @click="onEspEditEdit(s.id)">
                              <i class="ri-pencil-fill align-bottom me-1"></i> Editar
                            </button>
                            <button class="btn btn-sm btn-soft-danger" type="button" @click="removeEspById(formEdit, s.id)">
                              <i class="ri-delete-bin-5-fill align-bottom me-1"></i> Excluir
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    </BCol>
                  </BRow>
                </div>
              </div>
              <div class="col-md-12">
                <label for="psEditObservacoes" class="form-label">Observações</label>
                <textarea v-model="formEdit.observacoes" id="psEditObservacoes" class="form-control" rows="3" placeholder="Anotações gerais" maxlength="500"></textarea>
              </div>
            </div>
          </BTab>
          <BTab title="Endereço">
            <div class="row g-3 mt-2">
              <div class="col-md-3">
                <label for="psEditCep" class="form-label">CEP</label>
                <input v-model="formEdit.cep" type="text" id="psEditCep" class="form-control" placeholder="00000-000" maxlength="9" />
              </div>
              <div class="col-md-6">
                <label for="psEditEnderecoText" class="form-label">Endereço</label>
                <input v-model="formEdit.endereco" type="text" id="psEditEnderecoText" class="form-control" placeholder="Rua, Avenida, Travessa" maxlength="120" />
              </div>
              <div class="col-md-3">
                <label for="psEditNumero" class="form-label">Número</label>
                <input v-model="formEdit.numero" type="text" id="psEditNumero" class="form-control" placeholder="Número" maxlength="10" />
              </div>
              <div class="col-md-4">
                <label for="psEditBairro" class="form-label">Bairro</label>
                <input v-model="formEdit.bairro" type="text" id="psEditBairro" class="form-control" placeholder="Bairro" maxlength="60" />
              </div>
              <div class="col-md-4">
                <label for="psEditCidade" class="form-label">Cidade</label>
                <input v-model="formEdit.cidade" type="text" id="psEditCidade" class="form-control" placeholder="Cidade" maxlength="60" />
              </div>
              <div class="col-md-4">
                <label for="psEditComplemento" class="form-label">Complemento</label>
                <input v-model="formEdit.complemento" type="text" id="psEditComplemento" class="form-control" placeholder="Apartamento, Bloco, etc." maxlength="60" />
              </div>
            </div>
          </BTab>
        </BTabs>
      </template>
    </Modal>
<Modal
  v-model="espAddModal"
  :title="'Adicionar Especialidade'"
  :name-button="'Adicionar'"
  :processing="false"
  :z-index="2000"
  :backdrop-z-index="1990"
  @save="addEspConfirm"
>
  <BRow class="g-3">
    <BCol md="8">
      <label for="espAddSelect" class="form-label">Especialidade</label>
      <select data-choices v-model.number="espAddId" id="espAddSelect" class="form-select" ref="espAddSelectRef">
        <option :value="null" selected disabled>Selecione...</option>
        <option v-for="e in availableEspOptions" :key="e.id" :value="e.id">{{ e.nome }}</option>
      </select>
    </BCol>
    <BCol md="4">
      <label for="espAddQre" class="form-label">QRE</label>
      <input
        v-model="espAddQre"
        v-mask="'######'"
        type="text"
        id="espAddQre"
        class="form-control"
        placeholder="QRE"
        inputmode="numeric"
        maxlength="6"
      />
    </BCol>
  </BRow>
</Modal>
    <ModalDelete
      v-model="deleteModal"
      :title="'Excluir Profissional'"
      :subTitle="deleteSubTitle"
      :item-delete="deleteItem"
      @save="confirmDelete"
    />
  </Layout>
</template>
