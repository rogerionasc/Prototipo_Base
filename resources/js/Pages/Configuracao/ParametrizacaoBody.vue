<template>
  <BCard class="shadow-sm config-card">
    <BCardBody>
      <div class="d-flex align-items-start mb-3">
        <div class="flex-grow-1">
          <h5 class="mb-2">Parametrização</h5>
          <p class="text-muted mb-0">Gerencie listas usadas nos cadastros.</p>
        </div>
        <i class="ri-equalizer-line text-primary fs-20 ms-3"></i>
      </div>
      <BRow class="g-4">
        <BCol lg="6">
          <BCard class="shadow-sm h-100">
            <BCardHeader class="bg-light-subtle p-3 border-0">
              <BCardTitle><i class="ri-user-heart-line text-primary me-2"></i>Estado Civil</BCardTitle>
            </BCardHeader>
            <BCardBody>
              <p class="text-muted mb-3">Opções usadas no cadastro.</p>
              <div class="d-flex justify-content-end align-items-center mb-3">
                <span class="badge bg-primary-subtle text-primary">Total: {{ estadosCivisLocal?.length || 0 }}</span>
              </div>
              <div class="border border-dashed rounded p-3 bg-light-subtle mb-3">
                <BRow class="g-3 align-items-end">
                  <BCol md="8">
                    <label for="estadoCivilDescricao" class="form-label">Descrição</label>
                    <input v-model="formEstadoCivil.descricao" type="text" class="form-control" id="estadoCivilDescricao" :class="{ 'is-invalid': formEstadoCivil.errors.descricao }" placeholder="Ex.: Casado(a)" />
                    <div class="invalid-feedback">{{ formEstadoCivil.errors.descricao }}</div>
                  </BCol>
                  <BCol md="4">
                    <button type="button" class="btn btn-primary w-100" :disabled="formEstadoCivil.processing" @click="saveEstadoCivil">Adicionar</button>
                  </BCol>
                </BRow>
              </div>
              <div class="mt-4 table-responsive">
                <table class="table table-sm table-hover align-middle table-nowrap">
                  <thead class="table-light">
                    <tr>
                      <th style="width: 80px;">ID</th>
                      <th>Descrição</th>
                      <th style="width: 120px;">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="ec in estadosCivisLocal" :key="ec.id">
                      <template v-if="editingEstadoCivilId !== ec.id">
                        <td>#{{ ec.id }}</td>
                        <td>{{ ec.descricao }}</td>
                        <td>
                          <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-soft-info" @click="startEditEstadoCivil(ec)" title="Editar">
                              <i class="ri-pencil-line align-bottom"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-danger" @click="destroyEstadoCivil(ec.id)" title="Excluir">
                              <i class="ri-delete-bin-line align-bottom"></i>
                            </button>
                          </div>
                        </td>
                      </template>
                      <template v-else>
                        <td>#{{ ec.id }}</td>
                        <td>
                          <input v-model="editEstadoCivil.descricao" type="text" class="form-control" :class="{ 'is-invalid': editEstadoCivil.errors.descricao }" placeholder="Descrição" />
                          <div class="invalid-feedback">{{ editEstadoCivil.errors.descricao }}</div>
                        </td>
                        <td>
                          <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success btn-sm" :disabled="editEstadoCivil.processing" @click="updateEstadoCivil">Salvar</button>
                            <button type="button" class="btn btn-light btn-sm" @click="cancelEditEstadoCivil">Cancelar</button>
                          </div>
                        </td>
                      </template>
                    </tr>
                    <tr v-if="!estadosCivisLocal?.length">
                      <td colspan="3" class="text-muted">Nenhum estado civil cadastrado</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </BCardBody>
          </BCard>
        </BCol>

        <BCol lg="6">
          <BCard class="shadow-sm h-100">
            <BCardHeader class="bg-light-subtle p-3 border-0">
              <BCardTitle><i class="ri-heart-pulse-line text-primary me-2"></i>Tipo Sanguíneo</BCardTitle>
            </BCardHeader>
            <BCardBody>
              <p class="text-muted mb-3">Opções usadas no cadastro.</p>
              <div class="d-flex justify-content-end align-items-center mb-3">
                <span class="badge bg-primary-subtle text-primary">Total: {{ tiposSanguineosLocal?.length || 0 }}</span>
              </div>
              <div class="border border-dashed rounded p-3 bg-light-subtle mb-3">
                <BRow class="g-3 align-items-end">
                  <BCol md="8">
                    <label for="tipoSangDescricao" class="form-label">Descrição</label>
                    <input v-model="formTipoSang.descricao" type="text" class="form-control" id="tipoSangDescricao" :class="{ 'is-invalid': formTipoSang.errors.descricao }" placeholder="Ex.: O+" />
                    <div class="invalid-feedback">{{ formTipoSang.errors.descricao }}</div>
                  </BCol>
                  <BCol md="4">
                    <button type="button" class="btn btn-primary w-100" :disabled="formTipoSang.processing" @click="saveTipoSang">Adicionar</button>
                  </BCol>
                </BRow>
              </div>
              <div class="mt-4 table-responsive">
                <table class="table table-sm table-hover align-middle table-nowrap">
                  <thead class="table-light">
                    <tr>
                      <th style="width: 80px;">ID</th>
                      <th>Descrição</th>
                      <th style="width: 120px;">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="ts in tiposSanguineosLocal" :key="ts.id">
                      <template v-if="editingTipoSangId !== ts.id">
                        <td>#{{ ts.id }}</td>
                        <td>{{ ts.descricao }}</td>
                        <td>
                          <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-soft-info" @click="startEditTipoSang(ts)" title="Editar">
                              <i class="ri-pencil-line align-bottom"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-danger" @click="destroyTipoSang(ts.id)" title="Excluir">
                              <i class="ri-delete-bin-line align-bottom"></i>
                            </button>
                          </div>
                        </td>
                      </template>
                      <template v-else>
                        <td>#{{ ts.id }}</td>
                        <td>
                          <input v-model="editTipoSang.descricao" type="text" class="form-control" :class="{ 'is-invalid': editTipoSang.errors.descricao }" placeholder="Descrição" />
                          <div class="invalid-feedback">{{ editTipoSang.errors.descricao }}</div>
                        </td>
                        <td>
                          <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success btn-sm" :disabled="editTipoSang.processing" @click="updateTipoSang">Salvar</button>
                            <button type="button" class="btn btn-light btn-sm" @click="cancelEditTipoSang">Cancelar</button>
                          </div>
                        </td>
                      </template>
                    </tr>
                    <tr v-if="!tiposSanguineosLocal?.length">
                      <td colspan="3" class="text-muted">Nenhum tipo sanguíneo cadastrado</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </BCardBody>
          </BCard>
        </BCol>

        <BCol lg="6">
          <BCard class="shadow-sm h-100">
            <BCardHeader class="bg-light-subtle p-3 border-0">
              <BCardTitle><i class="ri-notification-3-line text-primary me-2"></i>Canal de Aviso</BCardTitle>
            </BCardHeader>
            <BCardBody>
              <p class="text-muted mb-3">Opções usadas no cadastro.</p>
              <div class="d-flex justify-content-end align-items-center mb-3">
                <span class="badge bg-primary-subtle text-primary">Total: {{ canaisAvisoLocal?.length || 0 }}</span>
              </div>
              <div class="border border-dashed rounded p-3 bg-light-subtle mb-3">
                <BRow class="g-3 align-items-end">
                  <BCol md="8">
                    <label for="canalAvisoNome" class="form-label">Nome</label>
                    <input v-model="formCanalAviso.nome" type="text" class="form-control" id="canalAvisoNome" :class="{ 'is-invalid': formCanalAviso.errors.nome }" placeholder="Ex.: WhatsApp" />
                    <div class="invalid-feedback">{{ formCanalAviso.errors.nome }}</div>
                  </BCol>
                  <BCol md="4">
                    <button type="button" class="btn btn-primary w-100" :disabled="formCanalAviso.processing" @click="saveCanalAviso">Adicionar</button>
                  </BCol>
                </BRow>
              </div>
              <div class="mt-4 table-responsive">
                <table class="table table-sm table-hover align-middle table-nowrap">
                  <thead class="table-light">
                    <tr>
                      <th style="width: 80px;">ID</th>
                      <th>Nome</th>
                      <th style="width: 120px;">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="ca in canaisAvisoLocal" :key="ca.id">
                      <template v-if="editingCanalAvisoId !== ca.id">
                        <td>#{{ ca.id }}</td>
                        <td>{{ ca.nome }}</td>
                        <td>
                          <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-soft-info" @click="startEditCanalAviso(ca)" title="Editar">
                              <i class="ri-pencil-line align-bottom"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-danger" @click="destroyCanalAviso(ca.id)" title="Excluir">
                              <i class="ri-delete-bin-line align-bottom"></i>
                            </button>
                          </div>
                        </td>
                      </template>
                      <template v-else>
                        <td>#{{ ca.id }}</td>
                        <td>
                          <input v-model="editCanalAviso.nome" type="text" class="form-control" :class="{ 'is-invalid': editCanalAviso.errors.nome }" placeholder="Nome" />
                          <div class="invalid-feedback">{{ editCanalAviso.errors.nome }}</div>
                        </td>
                        <td>
                          <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success btn-sm" :disabled="editCanalAviso.processing" @click="updateCanalAviso">Salvar</button>
                            <button type="button" class="btn btn-light btn-sm" @click="cancelEditCanalAviso">Cancelar</button>
                          </div>
                        </td>
                      </template>
                    </tr>
                    <tr v-if="!canaisAvisoLocal?.length">
                      <td colspan="3" class="text-muted">Nenhum canal de aviso cadastrado</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </BCardBody>
          </BCard>
        </BCol>
      </BRow>
    </BCardBody>
  </BCard>
  <!-- Observação: lógica de exclusão prepara contexto; exibição do modal permanece responsabilidade externa -->
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const props = defineProps({
  estadosCivis: { type: Array, default: () => [] },
  tiposSanguineos: { type: Array, default: () => [] },
  canaisAviso: { type: Array, default: () => [] },
});

const estadosCivisLocal = ref([...(props.estadosCivis || [])]);
const tiposSanguineosLocal = ref([...(props.tiposSanguineos || [])]);
const canaisAvisoLocal = ref([...(props.canaisAviso || [])]);

watch(() => props.estadosCivis, (v) => { estadosCivisLocal.value = [...(v || [])]; });
watch(() => props.tiposSanguineos, (v) => { tiposSanguineosLocal.value = [...(v || [])]; });
watch(() => props.canaisAviso, (v) => { canaisAvisoLocal.value = [...(v || [])]; });

const formEstadoCivil = useForm({ descricao: "" });
const editEstadoCivil = useForm({ descricao: "" });
const editingEstadoCivilId = ref(null);

const formTipoSang = useForm({ descricao: "" });
const editTipoSang = useForm({ descricao: "" });
const editingTipoSangId = ref(null);

const formCanalAviso = useForm({ nome: "" });
const editCanalAviso = useForm({ nome: "" });
const editingCanalAvisoId = ref(null);

/* Busca removida nos cards de parametrização */

const deleteModal = ref(false);
const deleteContext = ref({ type: '', id: null, nome: '' });

const saveEstadoCivil = () => {
  formEstadoCivil.post("/parametros/estado-civil", {
    onSuccess: () => {
      formEstadoCivil.reset();
    },
    preserveScroll: true,
  });
};

const startEditEstadoCivil = (ec) => {
  editingEstadoCivilId.value = ec.id;
  editEstadoCivil.descricao = ec.descricao;
};
const cancelEditEstadoCivil = () => {
  editingEstadoCivilId.value = null;
  editEstadoCivil.reset();
};
const updateEstadoCivil = () => {
  editEstadoCivil.put(`/parametros/estado-civil/${editingEstadoCivilId.value}`, {
    onSuccess: () => {
      editingEstadoCivilId.value = null;
      editEstadoCivil.reset();
    },
    preserveScroll: true,
  });
};
const destroyEstadoCivil = (id) => {
  const item = (props.estadosCivis || []).find(e => e.id === id);
  deleteContext.value = { type: 'estado_civil', id, nome: item?.descricao || '' };
  deleteModal.value = true;
};

const saveTipoSang = () => {
  formTipoSang.post("/parametros/tipo-sanguineo", {
    onSuccess: () => {
      formTipoSang.reset();
    },
    preserveScroll: true,
  });
};
const startEditTipoSang = (ts) => {
  editingTipoSangId.value = ts.id;
  editTipoSang.descricao = ts.descricao;
};
const cancelEditTipoSang = () => {
  editingTipoSangId.value = null;
  editTipoSang.reset();
};
const updateTipoSang = () => {
  editTipoSang.put(`/parametros/tipo-sanguineo/${editingTipoSangId.value}`, {
    onSuccess: () => {
      editingTipoSangId.value = null;
      editTipoSang.reset();
    },
    preserveScroll: true,
  });
};
const destroyTipoSang = (id) => {
  const item = (props.tiposSanguineos || []).find(t => t.id === id);
  deleteContext.value = { type: 'tipo_sanguineo', id, nome: item?.descricao || '' };
  deleteModal.value = true;
};

const saveCanalAviso = () => {
  formCanalAviso.post("/parametros/canal-aviso", {
    onSuccess: () => {
      formCanalAviso.reset();
    },
    preserveScroll: true,
  });
};
const startEditCanalAviso = (ca) => {
  editingCanalAvisoId.value = ca.id;
  editCanalAviso.nome = ca.nome;
};
const cancelEditCanalAviso = () => {
  editingCanalAvisoId.value = null;
  editCanalAviso.reset();
};
const updateCanalAviso = () => {
  editCanalAviso.put(`/parametros/canal-aviso/${editingCanalAvisoId.value}`, {
    onSuccess: () => {
      editingCanalAvisoId.value = null;
      editCanalAviso.reset();
    },
    preserveScroll: true,
  });
};
const destroyCanalAviso = (id) => {
  const item = (props.canaisAviso || []).find(c => c.id === id);
  deleteContext.value = { type: 'canal_aviso', id, nome: item?.nome || '' };
  deleteModal.value = true;
};

const confirmDelete = () => {
  const ctx = deleteContext.value || {};
  const f = useForm({});
  if (ctx.type === 'estado_civil') {
    f.delete(`/parametros/estado-civil/${ctx.id}`, { preserveScroll: true });
  } else if (ctx.type === 'tipo_sanguineo') {
    f.delete(`/parametros/tipo-sanguineo/${ctx.id}`, { preserveScroll: true });
  } else if (ctx.type === 'canal_aviso') {
    f.delete(`/parametros/canal-aviso/${ctx.id}`, { preserveScroll: true });
  }
  deleteModal.value = false;
  deleteContext.value = { type: '', id: null, nome: '' };
};
</script>
