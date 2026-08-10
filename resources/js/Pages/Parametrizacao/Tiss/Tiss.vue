<template>
    <BContainer fluid>
        <BCard class="shadow-sm border-0">
            <BCardHeader class="align-items-center d-flex border-bottom-dashed">
                <BCardTitle class="mb-0 flex-grow-1">Parâmetros TISS</BCardTitle>
            </BCardHeader>
            <BCardBody>
                <BTabs nav-class="nav-tabs-custom text-muted mb-4">
                    <BTab title="16. Conselhos" active>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Lista de Conselhos</h6>
                        </div>
                        <div class="border rounded p-3 bg-light-subtle mb-4">
                            <form @submit.prevent="saveConselho">
                                <BRow class="g-3 align-items-end">
                                    <BCol md="2">
                                        <label class="form-label">Cód.</label>
                                        <input v-model="formConselho.codigo" type="text" class="form-control"
                                            :class="{ 'is-invalid': formConselho.errors.codigo }" placeholder="Ex.: 06"
                                            maxlength="5" />
                                        <div class="invalid-feedback">{{ formConselho.errors.codigo }}</div>
                                    </BCol>
                                    <BCol md="2">
                                        <label class="form-label">Sigla <span class="text-danger">*</span></label>
                                        <input v-model="formConselho.sigla" type="text" class="form-control"
                                            :class="{ 'is-invalid': formConselho.errors.sigla }" placeholder="Ex.: CRM"
                                            maxlength="20" required />
                                        <div class="invalid-feedback">{{ formConselho.errors.sigla }}</div>
                                    </BCol>
                                    <BCol md="5">
                                        <label class="form-label">Descrição do Conselho <span
                                                class="text-danger">*</span></label>
                                        <input v-model="formConselho.descricao" type="text" class="form-control"
                                            :class="{ 'is-invalid': formConselho.errors.descricao }"
                                            placeholder="Ex.: Conselho Regional de Medicina" required />
                                        <div class="invalid-feedback">{{ formConselho.errors.descricao }}</div>
                                    </BCol>
                                    <BCol md="3">
                                        <button type="submit" class="btn btn-primary w-100"
                                            :disabled="formConselho.processing"><i
                                                class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                    </BCol>
                                </BRow>
                            </form>
                        </div>
                        <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0"
                            :items="conselhosLocal"
                            :columns="[{ key: 'codigo', label: 'Cód.' }, { key: 'sigla', label: 'Sigla' }, { key: 'descricao', label: 'Descrição' }, { key: 'acoes', label: 'Ações', width: '120px' }]"
                            emptyTitle="" emptyMessage="Nenhum registro encontrado.">
                            <template #body="{ items }">
                                <tr v-for="c in items" :key="c.id">
                                    <template v-if="editingConselhoId !== c.id">
                                        <td>{{ c.codigo }}</td>
                                        <td>{{ c.sigla }}</td>
                                        <td>{{ c.descricao }}</td>
                                        <td class="text-end" style="width:150px">
                                            <button type="button" class="btn btn-sm btn-soft-info me-2"
                                                @click="startEditConselho(c)" title="Editar"><i
                                                    class="ri-pencil-line"></i></button>
                                            <button type="button" class="btn btn-sm btn-soft-danger"
                                                @click="destroyConselho(c.id)" title="Excluir"><i
                                                    class="ri-delete-bin-line"></i></button>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <td colspan="4">
                                            <div class="d-flex gap-2">
                                                <input v-model="editConselho.codigo" type="text" class="form-control"
                                                    placeholder="Cód." style="width: 80px;" maxlength="5" />
                                                <input v-model="editConselho.sigla" type="text" class="form-control"
                                                    placeholder="Sigla" style="width: 150px;" required />
                                                <input v-model="editConselho.descricao" type="text" class="form-control"
                                                    placeholder="Descrição" required />
                                                <button type="button" class="btn btn-success"
                                                    @click="updateConselho">Salvar</button>
                                                <button type="button" class="btn btn-light"
                                                    @click="cancelEditConselho">Cancelar</button>
                                            </div>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </SimpleTable>
                    </BTab>
                    <BTab title="21. Caráter do Atendimento">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Lista de Caráter do Atendimento</h6>
                        </div>
                        <div class="border rounded p-3 bg-light-subtle mb-4">
                            <form @submit.prevent="saveCarater">
                                <BRow class="g-3 align-items-end">
                                    <BCol md="2">
                                        <label class="form-label">Cód. <span class="text-danger">*</span></label>
                                        <input v-model="formCarater.codigo" type="text" class="form-control"
                                            :class="{ 'is-invalid': formCarater.errors.codigo }" placeholder="Ex.: 1"
                                            maxlength="2" required />
                                        <div class="invalid-feedback">{{ formCarater.errors.codigo }}</div>
                                    </BCol>
                                    <BCol md="7">
                                        <label class="form-label">Descrição <span class="text-danger">*</span></label>
                                        <input v-model="formCarater.descricao" type="text" class="form-control"
                                            :class="{ 'is-invalid': formCarater.errors.descricao }"
                                            placeholder="Ex.: Eletiva" required />
                                        <div class="invalid-feedback">{{ formCarater.errors.descricao }}</div>
                                    </BCol>
                                    <BCol md="3">
                                        <button type="submit" class="btn btn-primary w-100"
                                            :disabled="formCarater.processing"><i
                                                class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                    </BCol>
                                </BRow>
                            </form>
                        </div>
                        <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0"
                            :items="caraterLocal"
                            :columns="[{ key: 'codigo', label: 'Cód.' }, { key: 'descricao', label: 'Descrição' }, { key: 'acoes', label: 'Ações', width: '120px' }]"
                            emptyTitle="" emptyMessage="Nenhum registro encontrado.">
                            <template #body="{ items }">
                                <tr v-for="c in items" :key="c.id">
                                    <template v-if="editingCaraterId !== c.id">
                                        <td>{{ c.codigo }}</td>
                                        <td>{{ c.descricao }}</td>
                                        <td class="text-end" style="width:150px">
                                            <button type="button" class="btn btn-sm btn-soft-info me-2"
                                                @click="startEditCarater(c)" title="Editar"><i
                                                    class="ri-pencil-line"></i></button>
                                            <button type="button" class="btn btn-sm btn-soft-danger"
                                                @click="destroyCarater(c.id)" title="Excluir"><i
                                                    class="ri-delete-bin-line"></i></button>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <td colspan="3">
                                            <div class="d-flex gap-2">
                                                <input v-model="editCarater.codigo" type="text" class="form-control"
                                                    placeholder="Cód." style="width: 80px;" maxlength="2" required />
                                                <input v-model="editCarater.descricao" type="text" class="form-control"
                                                    placeholder="Descrição" required />
                                                <button type="button" class="btn btn-success"
                                                    @click="updateCarater">Salvar</button>
                                                <button type="button" class="btn btn-light"
                                                    @click="cancelEditCarater">Cancelar</button>
                                            </div>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </SimpleTable>
                    </BTab>
                    <BTab title="87. Tabela de Referência">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Lista de Tabelas de Referência</h6>
                        </div>
                        <div class="border rounded p-3 bg-light-subtle mb-4">
                            <form @submit.prevent="saveTabelaReferencia">
                                <BRow class="g-3 align-items-end">
                                    <BCol md="2">
                                        <label class="form-label">Cód. <span class="text-danger">*</span></label>
                                        <input v-model="formTabelaReferencia.codigo" type="text" class="form-control"
                                            :class="{ 'is-invalid': formTabelaReferencia.errors.codigo }" placeholder="Ex.: 22"
                                            maxlength="2" required />
                                        <div class="invalid-feedback">{{ formTabelaReferencia.errors.codigo }}</div>
                                    </BCol>
                                    <BCol md="7">
                                        <label class="form-label">Descrição <span class="text-danger">*</span></label>
                                        <input v-model="formTabelaReferencia.descricao" type="text" class="form-control"
                                            :class="{ 'is-invalid': formTabelaReferencia.errors.descricao }"
                                            placeholder="Ex.: Tabela Própria das Operadoras" required />
                                        <div class="invalid-feedback">{{ formTabelaReferencia.errors.descricao }}</div>
                                    </BCol>
                                    <BCol md="3">
                                        <button type="submit" class="btn btn-primary w-100"
                                            :disabled="formTabelaReferencia.processing"><i
                                                class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                    </BCol>
                                </BRow>
                            </form>
                        </div>
                        <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0"
                            :items="tabelasReferenciaLocal"
                            :columns="[{ key: 'codigo', label: 'Cód.' }, { key: 'descricao', label: 'Descrição' }, { key: 'acoes', label: 'Ações', width: '120px' }]"
                            emptyTitle="" emptyMessage="Nenhum registro encontrado.">
                            <template #body="{ items }">
                                <tr v-for="t in items" :key="t.id">
                                    <template v-if="editingTabelaReferenciaId !== t.id">
                                        <td>{{ t.codigo }}</td>
                                        <td>{{ t.descricao }}</td>
                                        <td class="text-end" style="width:150px">
                                            <button type="button" class="btn btn-sm btn-soft-info me-2"
                                                @click="startEditTabelaReferencia(t)" title="Editar"><i
                                                    class="ri-pencil-line"></i></button>
                                            <button type="button" class="btn btn-sm btn-soft-danger"
                                                @click="destroyTabelaReferencia(t.id)" title="Excluir"><i
                                                    class="ri-delete-bin-line"></i></button>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <td colspan="3">
                                            <div class="d-flex gap-2">
                                                <input v-model="editTabelaReferencia.codigo" type="text" class="form-control"
                                                    placeholder="Cód." style="width: 80px;" maxlength="2" required />
                                                <input v-model="editTabelaReferencia.descricao" type="text" class="form-control"
                                                    placeholder="Descrição" required />
                                                <button type="button" class="btn btn-success"
                                                    @click="updateTabelaReferencia">Salvar</button>
                                                <button type="button" class="btn btn-light"
                                                    @click="cancelEditTabelaReferencia">Cancelar</button>
                                            </div>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </SimpleTable>
                    </BTab>
                </BTabs>
            </BCardBody>
        </BCard>
    </BContainer>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Swal from "sweetalert2";
import SimpleTable from "@/Components/Tables/SimpleTable.vue";

const props = defineProps({
    conselhos: { type: Array, default: () => [] },
    caraterAtendimentos: { type: Array, default: () => [] },
    tabelasReferencia: { type: Array, default: () => [] },
});

const conselhosLocal = ref([...(props.conselhos || [])]);
const caraterLocal = ref([...(props.caraterAtendimentos || [])]);
const tabelasReferenciaLocal = ref([...(props.tabelasReferencia || [])]);

watch(() => props.conselhos, (v) => { conselhosLocal.value = [...(v || [])]; });
watch(() => props.caraterAtendimentos, (v) => { caraterLocal.value = [...(v || [])]; });
watch(() => props.tabelasReferencia, (v) => { tabelasReferenciaLocal.value = [...(v || [])]; });

const formConselho = useForm({ codigo: "", sigla: "", descricao: "" });
const editConselho = useForm({ codigo: "", sigla: "", descricao: "" });
const editingConselhoId = ref(null);

const saveConselho = () => {
    formConselho.post("/parametros/conselho", {
        preserveScroll: true,
        onSuccess: () => {
            formConselho.reset();
            router.reload({ only: ['conselhos'] });
        }
    });
};

const startEditConselho = (c) => {
    editingConselhoId.value = c.id;
    editConselho.codigo = c.codigo || "";
    editConselho.sigla = c.sigla || "";
    editConselho.descricao = c.descricao || "";
};

const cancelEditConselho = () => {
    editingConselhoId.value = null;
    editConselho.reset();
};

const updateConselho = () => {
    editConselho.put(`/parametros/conselho/${editingConselhoId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingConselhoId.value = null;
            editConselho.reset();
            router.reload({ only: ['conselhos'] });
        }
    });
};

const destroyConselho = (id) => {
    Swal.fire({
        title: "Tem certeza?",
        text: "Excluir este conselho não poderá ser desfeito.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#f46a6a",
        confirmButtonText: "Sim, excluir",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/parametros/conselho/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ['conselhos'] });
                }
            });
        }
    });
};

const formCarater = useForm({ codigo: "", descricao: "" });
const editCarater = useForm({ codigo: "", descricao: "" });
const editingCaraterId = ref(null);

const saveCarater = () => {
    formCarater.post("/parametros/carater-atendimento", {
        preserveScroll: true,
        onSuccess: () => {
            formCarater.reset();
            router.reload({ only: ['caraterAtendimentos'] });
        }
    });
};

const startEditCarater = (c) => {
    editingCaraterId.value = c.id;
    editCarater.codigo = c.codigo || "";
    editCarater.descricao = c.descricao || "";
};

const cancelEditCarater = () => {
    editingCaraterId.value = null;
    editCarater.reset();
};

const updateCarater = () => {
    editCarater.put(`/parametros/carater-atendimento/${editingCaraterId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingCaraterId.value = null;
            editCarater.reset();
            router.reload({ only: ['caraterAtendimentos'] });
        }
    });
};

const destroyCarater = (id) => {
    Swal.fire({
        title: "Tem certeza?",
        text: "Excluir este caráter de atendimento não poderá ser desfeito.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#f46a6a",
        confirmButtonText: "Sim, excluir",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/parametros/carater-atendimento/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ['caraterAtendimentos'] });
                }
            });
        }
    });
};

const formTabelaReferencia = useForm({ codigo: "", descricao: "" });
const editTabelaReferencia = useForm({ codigo: "", descricao: "" });
const editingTabelaReferenciaId = ref(null);

const saveTabelaReferencia = () => {
    formTabelaReferencia.post("/parametros/tabela-referencia", {
        preserveScroll: true,
        onSuccess: () => {
            formTabelaReferencia.reset();
            router.reload({ only: ['tabelasReferencia'] });
        }
    });
};

const startEditTabelaReferencia = (t) => {
    editingTabelaReferenciaId.value = t.id;
    editTabelaReferencia.codigo = t.codigo || "";
    editTabelaReferencia.descricao = t.descricao || "";
};

const cancelEditTabelaReferencia = () => {
    editingTabelaReferenciaId.value = null;
    editTabelaReferencia.reset();
};

const updateTabelaReferencia = () => {
    editTabelaReferencia.put(`/parametros/tabela-referencia/${editingTabelaReferenciaId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingTabelaReferenciaId.value = null;
            editTabelaReferencia.reset();
            router.reload({ only: ['tabelasReferencia'] });
        }
    });
};

const destroyTabelaReferencia = (id) => {
    Swal.fire({
        title: "Tem certeza?",
        text: "Excluir esta tabela de referência não poderá ser desfeito.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#f46a6a",
        confirmButtonText: "Sim, excluir",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/parametros/tabela-referencia/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ['tabelasReferencia'] });
                }
            });
        }
    });
};
</script>
