<template>
    <Layout>

        <Head title="Parametrização" />
        <PageHeader title="Parametrização" pageTitle="Configurações" />

        <BContainer fluid>
            <BCard class="shadow-sm border-0">
                <BCardHeader class="align-items-center d-flex border-bottom-dashed">
                    <BCardTitle class="mb-0 flex-grow-1">Parâmetros do Sistema</BCardTitle>
                </BCardHeader>
                <BCardBody>
                    <BTabs nav-class="nav-tabs-custom text-muted mb-4">
                        <!-- ESTADO CIVIL -->
                        <BTab title="Estado Civil" active>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Lista de Estados Civis</h6>
                            </div>
                            <div class="border rounded p-3 bg-light-subtle mb-4">
                                <form @submit.prevent="saveEstadoCivil">
                                    <BRow class="g-3 align-items-end">
                                        <BCol md="8">
                                            <label class="form-label">Descrição do Estado Civil</label>
                                            <input v-model="formEstadoCivil.descricao" type="text" class="form-control"
                                                :class="{ 'is-invalid': formEstadoCivil.errors.descricao }"
                                                placeholder="Ex.: Solteiro(a)..." />
                                            <div class="invalid-feedback">{{ formEstadoCivil.errors.descricao }}</div>
                                        </BCol>
                                        <BCol md="4">
                                            <button type="submit" class="btn btn-primary w-100"
                                                :disabled="formEstadoCivil.processing"><i
                                                    class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                        </BCol>
                                    </BRow>
                                </form>
                            </div>
                            <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0"
                                :items="estadosCivisLocal" :columns="parametrosColumns" emptyTitle=""
                                emptyMessage="Nenhum registro encontrado.">
                                <template #body="{ items }">
                                    <tr v-for="ec in items" :key="ec.id">
                                        <template v-if="editingEstadoCivilId !== ec.id">
                                            <td style="width:80px">#{{ ec.id }}</td>
                                            <td>{{ ec.descricao }}</td>
                                            <td class="text-end" style="width:150px">
                                                <button type="button" class="btn btn-sm btn-soft-info me-2"
                                                    @click="startEditEstadoCivil(ec)" title="Editar"><i
                                                        class="ri-pencil-line"></i></button>
                                                <button type="button" class="btn btn-sm btn-soft-danger"
                                                    @click="destroyEstadoCivil(ec.id)" title="Excluir"><i
                                                        class="ri-delete-bin-line"></i></button>
                                            </td>
                                        </template>
                                        <template v-else>
                                            <td colspan="3">
                                                <div class="d-flex gap-2">
                                                    <input v-model="editEstadoCivil.descricao" type="text"
                                                        class="form-control" />
                                                    <button type="button" class="btn btn-success"
                                                        @click="updateEstadoCivil">Salvar</button>
                                                    <button type="button" class="btn btn-light"
                                                        @click="cancelEditEstadoCivil">Cancelar</button>
                                                </div>
                                            </td>
                                        </template>
                                    </tr>
                                </template>
                            </SimpleTable>
                        </BTab>

                        <!-- PARENTESCO -->
                        <BTab title="Parentesco">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Lista de Parentescos</h6>
                            </div>
                            <div class="border rounded p-3 bg-light-subtle mb-4">
                                <form @submit.prevent="saveParentesco">
                                    <BRow class="g-3 align-items-end">
                                        <BCol md="8">
                                            <label class="form-label">Descrição do Parentesco</label>
                                            <input v-model="formParentesco.descricao" type="text" class="form-control"
                                                :class="{ 'is-invalid': formParentesco.errors.descricao }"
                                                placeholder="Ex.: Pai, Mãe..." />
                                            <div class="invalid-feedback">{{ formParentesco.errors.descricao }}</div>
                                        </BCol>
                                        <BCol md="4">
                                            <button type="submit" class="btn btn-primary w-100"
                                                :disabled="formParentesco.processing"><i
                                                    class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                        </BCol>
                                    </BRow>
                                </form>
                            </div>
                            <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0"
                                :items="parentescosLocal" :columns="parametrosColumns" emptyTitle=""
                                emptyMessage="Nenhum registro encontrado.">
                                <template #body="{ items }">
                                    <tr v-for="pa in items" :key="pa.id">
                                        <template v-if="editingParentescoId !== pa.id">
                                            <td style="width:80px">#{{ pa.id }}</td>
                                            <td>{{ pa.descricao }}</td>
                                            <td class="text-end" style="width:150px">
                                                <button type="button" class="btn btn-sm btn-soft-info me-2"
                                                    @click="startEditParentesco(pa)" title="Editar"><i
                                                        class="ri-pencil-line"></i></button>
                                                <button type="button" class="btn btn-sm btn-soft-danger"
                                                    @click="destroyParentesco(pa.id)" title="Excluir"><i
                                                        class="ri-delete-bin-line"></i></button>
                                            </td>
                                        </template>
                                        <template v-else>
                                            <td colspan="3">
                                                <div class="d-flex gap-2">
                                                    <input v-model="editParentesco.descricao" type="text"
                                                        class="form-control" />
                                                    <button type="button" class="btn btn-success"
                                                        @click="updateParentesco">Salvar</button>
                                                    <button type="button" class="btn btn-light"
                                                        @click="cancelEditParentesco">Cancelar</button>
                                                </div>
                                            </td>
                                        </template>
                                    </tr>
                                </template>
                            </SimpleTable>
                        </BTab>

                        <!-- TIPO SANGUINEO -->
                        <BTab title="Tipo Sanguíneo">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Lista de Tipos Sanguíneos</h6>
                            </div>
                            <div class="border rounded p-3 bg-light-subtle mb-4">
                                <form @submit.prevent="saveTipoSang">
                                    <BRow class="g-3 align-items-end">
                                        <BCol md="8">
                                            <label class="form-label">Descrição do Tipo Sanguíneo</label>
                                            <input v-model="formTipoSang.descricao" type="text" class="form-control"
                                                :class="{ 'is-invalid': formTipoSang.errors.descricao }"
                                                placeholder="Ex.: O+..." />
                                            <div class="invalid-feedback">{{ formTipoSang.errors.descricao }}</div>
                                        </BCol>
                                        <BCol md="4">
                                            <button type="submit" class="btn btn-primary w-100"
                                                :disabled="formTipoSang.processing"><i
                                                    class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                        </BCol>
                                    </BRow>
                                </form>
                            </div>
                            <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0"
                                :items="tiposSanguineosLocal" :columns="parametrosColumns" emptyTitle=""
                                emptyMessage="Nenhum registro encontrado.">
                                <template #body="{ items }">
                                    <tr v-for="ts in items" :key="ts.id">
                                        <template v-if="editingTipoSangId !== ts.id">
                                            <td style="width:80px">#{{ ts.id }}</td>
                                            <td>{{ ts.descricao }}</td>
                                            <td class="text-end" style="width:150px">
                                                <button type="button" class="btn btn-sm btn-soft-info me-2"
                                                    @click="startEditTipoSang(ts)" title="Editar"><i
                                                        class="ri-pencil-line"></i></button>
                                                <button type="button" class="btn btn-sm btn-soft-danger"
                                                    @click="destroyTipoSang(ts.id)" title="Excluir"><i
                                                        class="ri-delete-bin-line"></i></button>
                                            </td>
                                        </template>
                                        <template v-else>
                                            <td colspan="3">
                                                <div class="d-flex gap-2">
                                                    <input v-model="editTipoSang.descricao" type="text"
                                                        class="form-control" />
                                                    <button type="button" class="btn btn-success"
                                                        @click="updateTipoSang">Salvar</button>
                                                    <button type="button" class="btn btn-light"
                                                        @click="cancelEditTipoSang">Cancelar</button>
                                                </div>
                                            </td>
                                        </template>
                                    </tr>
                                </template>
                            </SimpleTable>
                        </BTab>

                        <!-- CANAL DE AVISO -->
                        <BTab title="Canais de Aviso">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Lista de Canais de Aviso</h6>
                            </div>
                            <div class="border rounded p-3 bg-light-subtle mb-4">
                                <form @submit.prevent="saveCanalAviso">
                                    <BRow class="g-3 align-items-end">
                                        <BCol md="8">
                                            <label class="form-label">Nome do Canal de Aviso</label>
                                            <input v-model="formCanalAviso.nome" type="text" class="form-control"
                                                :class="{ 'is-invalid': formCanalAviso.errors.nome }"
                                                placeholder="Ex.: E-mail, WhatsApp..." />
                                            <div class="invalid-feedback">{{ formCanalAviso.errors.nome }}</div>
                                        </BCol>
                                        <BCol md="4">
                                            <button type="submit" class="btn btn-primary w-100"
                                                :disabled="formCanalAviso.processing"><i
                                                    class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                        </BCol>
                                    </BRow>
                                </form>
                            </div>
                            <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0"
                                :items="canaisAvisoLocal" :columns="parametrosColumns" emptyTitle=""
                                emptyMessage="Nenhum registro encontrado.">
                                <template #body="{ items }">
                                    <tr v-for="ca in items" :key="ca.id">
                                        <template v-if="editingCanalAvisoId !== ca.id">
                                            <td style="width:80px">#{{ ca.id }}</td>
                                            <td>{{ ca.nome }}</td>
                                            <td class="text-end" style="width:150px">
                                                <button type="button" class="btn btn-sm btn-soft-info me-2"
                                                    @click="startEditCanalAviso(ca)" title="Editar"><i
                                                        class="ri-pencil-line"></i></button>
                                                <button type="button" class="btn btn-sm btn-soft-danger"
                                                    @click="destroyCanalAviso(ca.id)" title="Excluir"><i
                                                        class="ri-delete-bin-line"></i></button>
                                            </td>
                                        </template>
                                        <template v-else>
                                            <td colspan="3">
                                                <div class="d-flex gap-2">
                                                    <input v-model="editCanalAviso.nome" type="text"
                                                        class="form-control" />
                                                    <button type="button" class="btn btn-success"
                                                        @click="updateCanalAviso">Salvar</button>
                                                    <button type="button" class="btn btn-light"
                                                        @click="cancelEditCanalAviso">Cancelar</button>
                                                </div>
                                            </td>
                                        </template>
                                    </tr>
                                </template>
                            </SimpleTable>
                        </BTab>

                        <!-- CATEGORIAS -->
                        <BTab title="Categorias de Procedimento">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Lista de Categorias</h6>
                            </div>
                            <div class="border rounded p-3 bg-light-subtle mb-4">
                                <form @submit.prevent="saveCategoria">
                                    <BRow class="g-3 align-items-end">
                                        <BCol md="8">
                                            <label class="form-label">Nome da Categoria</label>
                                            <input v-model="formCategoria.nome" type="text" class="form-control"
                                                :class="{ 'is-invalid': formCategoria.errors.nome }"
                                                placeholder="Ex.: Consultas, Exames..." />
                                            <div class="invalid-feedback">{{ formCategoria.errors.nome }}</div>
                                        </BCol>
                                        <BCol md="4">
                                            <button type="submit" class="btn btn-primary w-100"
                                                :disabled="formCategoria.processing"><i
                                                    class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                        </BCol>
                                    </BRow>
                                </form>
                            </div>
                            <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0"
                                :items="categoriasLocal" :columns="parametrosColumns" emptyTitle=""
                                emptyMessage="Nenhum registro encontrado.">
                                <template #body="{ items }">
                                    <tr v-for="cat in items" :key="cat.id">
                                        <template v-if="editingCategoriaId !== cat.id">
                                            <td style="width:80px">#{{ cat.id }}</td>
                                            <td>{{ cat.nome }}</td>
                                            <td class="text-end" style="width:150px">
                                                <button type="button" class="btn btn-sm btn-soft-info me-2"
                                                    @click="startEditCategoria(cat)" title="Editar"><i
                                                        class="ri-pencil-line"></i></button>
                                                <button type="button" class="btn btn-sm btn-soft-danger"
                                                    @click="destroyCategoria(cat.id)" title="Excluir"><i
                                                        class="ri-delete-bin-line"></i></button>
                                            </td>
                                        </template>
                                        <template v-else>
                                            <td colspan="3">
                                                <div class="d-flex gap-2">
                                                    <input v-model="editCategoria.nome" type="text"
                                                        class="form-control" />
                                                    <button type="button" class="btn btn-success"
                                                        @click="updateCategoria">Salvar</button>
                                                    <button type="button" class="btn btn-light"
                                                        @click="cancelEditCategoria">Cancelar</button>
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

            <ModalDelete v-model="deleteModal" :title="deleteTitle" :message="deleteMessage" @confirm="confirmDelete" />
        </BContainer>
    </Layout>
</template>

<script setup>
import { useForm, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import ModalDelete from "@/Components/ModalDelete.vue";
import SimpleTable from "@/Components/Tables/SimpleTable.vue";

const parametrosColumns = [
    { key: 'id', label: 'ID', width: '80px' },
    { key: 'descricao', label: 'Descrição' },
    { key: 'acoes', label: 'Ações', width: '120px' }
];

const props = defineProps({
    estadosCivis: { type: Array, default: () => [] },
    tiposSanguineos: { type: Array, default: () => [] },
    canaisAviso: { type: Array, default: () => [] },
    parentescos: { type: Array, default: () => [] },
    categoriasProcedimento: { type: Array, default: () => [] },
});

const estadosCivisLocal = ref([...(props.estadosCivis || [])]);
const tiposSanguineosLocal = ref([...(props.tiposSanguineos || [])]);
const canaisAvisoLocal = ref([...(props.canaisAviso || [])]);
const parentescosLocal = ref([...(props.parentescos || [])]);
const categoriasLocal = ref([...(props.categoriasProcedimento || [])]);

watch(() => props.estadosCivis, (v) => { estadosCivisLocal.value = [...(v || [])]; });
watch(() => props.tiposSanguineos, (v) => { tiposSanguineosLocal.value = [...(v || [])]; });
watch(() => props.canaisAviso, (v) => { canaisAvisoLocal.value = [...(v || [])]; });
watch(() => props.parentescos, (v) => { parentescosLocal.value = [...(v || [])]; });
watch(() => props.categoriasProcedimento, (v) => { categoriasLocal.value = [...(v || [])]; });

const formEstadoCivil = useForm({ descricao: "" });
const editEstadoCivil = useForm({ descricao: "" });
const editingEstadoCivilId = ref(null);

const formTipoSang = useForm({ descricao: "" });
const editTipoSang = useForm({ descricao: "" });
const editingTipoSangId = ref(null);

const formCanalAviso = useForm({ nome: "" });
const editCanalAviso = useForm({ nome: "" });
const editingCanalAvisoId = ref(null);

const formParentesco = useForm({ descricao: "" });
const editParentesco = useForm({ descricao: "" });
const editingParentescoId = ref(null);

const formCategoria = useForm({ nome: "" });
const editCategoria = useForm({ nome: "" });
const editingCategoriaId = ref(null);

const deleteModal = ref(false);
const deleteContext = ref({ type: '', id: null, nome: '' });
const deleteTitle = computed(() => {
    const t = deleteContext.value?.type;
    if (t === 'estado_civil') return 'Excluir Estado Civil';
    if (t === 'tipo_sanguineo') return 'Excluir Tipo Sanguíneo';
    if (t === 'canal_aviso') return 'Excluir Canal de Aviso';
    if (t === 'parentesco') return 'Excluir Parentesco';
    if (t === 'categoria_procedimento') return 'Excluir Categoria de Procedimento';
    return 'Excluir';
});
const deleteSubTitleComputed = computed(() => {
    const nome = deleteContext.value?.nome || '';
    return nome ? `Deseja realmente excluir "${nome}"?` : 'Deseja realmente excluir';
});

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

const saveParentesco = () => {
    formParentesco.post("/parametros/parentesco", {
        onSuccess: () => {
            formParentesco.reset();
        },
        preserveScroll: true,
    });
};
const startEditParentesco = (p) => {
    editingParentescoId.value = p.id;
    editParentesco.descricao = p.descricao;
};
const cancelEditParentesco = () => {
    editingParentescoId.value = null;
    editParentesco.reset();
};
const updateParentesco = () => {
    editParentesco.put(`/parametros/parentesco/${editingParentescoId.value}`, {
        onSuccess: () => {
            editingParentescoId.value = null;
            editParentesco.reset();
        },
        preserveScroll: true,
    });
};
const destroyParentesco = (id) => {
    const item = (props.parentescos || []).find(p => p.id === id);
    deleteContext.value = { type: 'parentesco', id, nome: item?.descricao || '' };
    deleteModal.value = true;
};

const saveCategoria = () => {
    formCategoria.post("/parametros/categoria-procedimento", {
        onSuccess: () => {
            formCategoria.reset();
            router.reload({ only: ['categoriasProcedimento'] });
        },
        preserveScroll: true,
    });
};
const startEditCategoria = (c) => {
    editingCategoriaId.value = c.id;
    editCategoria.nome = c.nome || "";
};
const cancelEditCategoria = () => {
    editingCategoriaId.value = null;
    editCategoria.reset();
};
const updateCategoria = () => {
    editCategoria.put(`/parametros/categoria-procedimento/${editingCategoriaId.value}`, {
        onSuccess: () => {
            editingCategoriaId.value = null;
            editCategoria.reset();
            router.reload({ only: ['categoriasProcedimento'] });
        },
        preserveScroll: true,
    });
};
const destroyCategoria = (id) => {
    const item = (props.categoriasProcedimento || []).find(c => c.id === id);
    deleteContext.value = { type: 'categoria_procedimento', id, nome: item?.nome || '' };
    deleteModal.value = true;
};

const confirmDelete = () => {
    const ctx = deleteContext.value || {};
    const f = useForm({});
    if (ctx.type === 'estado_civil') {
        f.delete(`/parametros/estado-civil/${ctx.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                estadosCivisLocal.value = (estadosCivisLocal.value || []).filter(e => String(e.id) !== String(ctx.id));
            }
        });
    } else if (ctx.type === 'tipo_sanguineo') {
        f.delete(`/parametros/tipo-sanguineo/${ctx.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                tiposSanguineosLocal.value = (tiposSanguineosLocal.value || []).filter(e => String(e.id) !== String(ctx.id));
            }
        });
    } else if (ctx.type === 'canal_aviso') {
        f.delete(`/parametros/canal-aviso/${ctx.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                canaisAvisoLocal.value = (canaisAvisoLocal.value || []).filter(e => String(e.id) !== String(ctx.id));
            }
        });
    } else if (ctx.type === 'parentesco') {
        f.delete(`/parametros/parentesco/${ctx.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                parentescosLocal.value = (parentescosLocal.value || []).filter(e => String(e.id) !== String(ctx.id));
            }
        });
    } else if (ctx.type === 'categoria_procedimento') {
        f.delete(`/parametros/categoria-procedimento/${ctx.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                categoriasLocal.value = (categoriasLocal.value || []).filter(e => String(e.id) !== String(ctx.id));
                router.reload({ only: ['categoriasProcedimento'] });
            }
        });
    }
    deleteModal.value = false;
    deleteContext.value = { type: '', id: null, nome: '' };
};
</script>

<style scoped>
:deep(.nav-tabs-custom .nav-link.active) {
    background-color: #ffffff !important;
}
</style>
