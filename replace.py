import re

with open('resources/js/Pages/Configuracao/Parametrizacao.vue', 'r', encoding='utf-8') as f:
    content = f.read()

script_start = content.find('<script setup>')
if script_start == -1:
    script_start = content.find('<script>')
    
script_and_below = content[script_start:]

new_template = """<template>
    <Layout>
        <Head title="Parametrização" />
        <PageHeader title="Parametrização" pageTitle="Configurações" />
        
        <BContainer fluid>
            <BCard class="shadow-sm border-0">
                <BCardBody>
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                        <div class="avatar-sm me-3 flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-24">
                                <i class="ri-equalizer-line"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1 text-primary">Parametrização</h5>
                            <p class="text-muted mb-0">Gerencie listas usadas nos cadastros do sistema.</p>
                        </div>
                    </div>

                    <BTabs nav-class="nav-tabs-custom nav-success mb-4" pills>
                        <!-- ESTADO CIVIL -->
                        <BTab title="Estado Civil" active>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Lista de Estados Civis</h6>
                                <span class="badge bg-info-subtle text-info fs-12">Total: {{ estadosCivisLocal?.length || 0 }}</span>
                            </div>
                            <div class="border rounded p-3 bg-light-subtle mb-4">
                                <form @submit.prevent="saveEstadoCivil">
                                    <BRow class="g-3 align-items-end">
                                        <BCol md="8">
                                            <label class="form-label">Descrição do Estado Civil</label>
                                            <input v-model="formEstadoCivil.descricao" type="text" class="form-control" :class="{ 'is-invalid': formEstadoCivil.errors.descricao }" placeholder="Ex.: Solteiro(a)..." />
                                            <div class="invalid-feedback">{{ formEstadoCivil.errors.descricao }}</div>
                                        </BCol>
                                        <BCol md="4">
                                            <button type="submit" class="btn btn-primary w-100" :disabled="formEstadoCivil.processing"><i class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                        </BCol>
                                    </BRow>
                                </form>
                            </div>
                            <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0" :items="estadosCivisLocal" :columns="parametrosColumns" emptyTitle="" emptyMessage="Nenhum registro encontrado.">
                                <template #body="{ items }">
                                    <tr v-for="ec in items" :key="ec.id">
                                        <template v-if="editingEstadoCivilId !== ec.id">
                                            <td style="width:80px">#{{ ec.id }}</td>
                                            <td>{{ ec.descricao }}</td>
                                            <td class="text-end" style="width:150px">
                                                <button type="button" class="btn btn-sm btn-soft-info me-2" @click="startEditEstadoCivil(ec)" title="Editar"><i class="ri-pencil-line"></i></button>
                                                <button type="button" class="btn btn-sm btn-soft-danger" @click="destroyEstadoCivil(ec.id)" title="Excluir"><i class="ri-delete-bin-line"></i></button>
                                            </td>
                                        </template>
                                        <template v-else>
                                            <td colspan="3">
                                                <div class="d-flex gap-2">
                                                    <input v-model="editEstadoCivil.descricao" type="text" class="form-control" />
                                                    <button type="button" class="btn btn-success" @click="updateEstadoCivil">Salvar</button>
                                                    <button type="button" class="btn btn-light" @click="cancelEditEstadoCivil">Cancelar</button>
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
                                <span class="badge bg-primary-subtle text-primary fs-12">Total: {{ parentescosLocal?.length || 0 }}</span>
                            </div>
                            <div class="border rounded p-3 bg-light-subtle mb-4">
                                <form @submit.prevent="saveParentesco">
                                    <BRow class="g-3 align-items-end">
                                        <BCol md="8">
                                            <label class="form-label">Descrição do Parentesco</label>
                                            <input v-model="formParentesco.descricao" type="text" class="form-control" :class="{ 'is-invalid': formParentesco.errors.descricao }" placeholder="Ex.: Pai, Mãe..." />
                                            <div class="invalid-feedback">{{ formParentesco.errors.descricao }}</div>
                                        </BCol>
                                        <BCol md="4">
                                            <button type="submit" class="btn btn-primary w-100" :disabled="formParentesco.processing"><i class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                        </BCol>
                                    </BRow>
                                </form>
                            </div>
                            <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0" :items="parentescosLocal" :columns="parametrosColumns" emptyTitle="" emptyMessage="Nenhum registro encontrado.">
                                <template #body="{ items }">
                                    <tr v-for="pa in items" :key="pa.id">
                                        <template v-if="editingParentescoId !== pa.id">
                                            <td style="width:80px">#{{ pa.id }}</td>
                                            <td>{{ pa.descricao }}</td>
                                            <td class="text-end" style="width:150px">
                                                <button type="button" class="btn btn-sm btn-soft-info me-2" @click="startEditParentesco(pa)" title="Editar"><i class="ri-pencil-line"></i></button>
                                                <button type="button" class="btn btn-sm btn-soft-danger" @click="destroyParentesco(pa.id)" title="Excluir"><i class="ri-delete-bin-line"></i></button>
                                            </td>
                                        </template>
                                        <template v-else>
                                            <td colspan="3">
                                                <div class="d-flex gap-2">
                                                    <input v-model="editParentesco.descricao" type="text" class="form-control" />
                                                    <button type="button" class="btn btn-success" @click="updateParentesco">Salvar</button>
                                                    <button type="button" class="btn btn-light" @click="cancelEditParentesco">Cancelar</button>
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
                                <span class="badge bg-danger-subtle text-danger fs-12">Total: {{ tiposSanguineosLocal?.length || 0 }}</span>
                            </div>
                            <div class="border rounded p-3 bg-light-subtle mb-4">
                                <form @submit.prevent="saveTipoSang">
                                    <BRow class="g-3 align-items-end">
                                        <BCol md="8">
                                            <label class="form-label">Descrição do Tipo Sanguíneo</label>
                                            <input v-model="formTipoSang.descricao" type="text" class="form-control" :class="{ 'is-invalid': formTipoSang.errors.descricao }" placeholder="Ex.: O+..." />
                                            <div class="invalid-feedback">{{ formTipoSang.errors.descricao }}</div>
                                        </BCol>
                                        <BCol md="4">
                                            <button type="submit" class="btn btn-primary w-100" :disabled="formTipoSang.processing"><i class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                        </BCol>
                                    </BRow>
                                </form>
                            </div>
                            <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0" :items="tiposSanguineosLocal" :columns="parametrosColumns" emptyTitle="" emptyMessage="Nenhum registro encontrado.">
                                <template #body="{ items }">
                                    <tr v-for="ts in items" :key="ts.id">
                                        <template v-if="editingTipoSangId !== ts.id">
                                            <td style="width:80px">#{{ ts.id }}</td>
                                            <td>{{ ts.descricao }}</td>
                                            <td class="text-end" style="width:150px">
                                                <button type="button" class="btn btn-sm btn-soft-info me-2" @click="startEditTipoSang(ts)" title="Editar"><i class="ri-pencil-line"></i></button>
                                                <button type="button" class="btn btn-sm btn-soft-danger" @click="destroyTipoSang(ts.id)" title="Excluir"><i class="ri-delete-bin-line"></i></button>
                                            </td>
                                        </template>
                                        <template v-else>
                                            <td colspan="3">
                                                <div class="d-flex gap-2">
                                                    <input v-model="editTipoSang.descricao" type="text" class="form-control" />
                                                    <button type="button" class="btn btn-success" @click="updateTipoSang">Salvar</button>
                                                    <button type="button" class="btn btn-light" @click="cancelEditTipoSang">Cancelar</button>
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
                                <span class="badge bg-warning-subtle text-warning fs-12">Total: {{ canaisAvisoLocal?.length || 0 }}</span>
                            </div>
                            <div class="border rounded p-3 bg-light-subtle mb-4">
                                <form @submit.prevent="saveCanalAviso">
                                    <BRow class="g-3 align-items-end">
                                        <BCol md="8">
                                            <label class="form-label">Nome do Canal de Aviso</label>
                                            <input v-model="formCanalAviso.nome" type="text" class="form-control" :class="{ 'is-invalid': formCanalAviso.errors.nome }" placeholder="Ex.: E-mail, WhatsApp..." />
                                            <div class="invalid-feedback">{{ formCanalAviso.errors.nome }}</div>
                                        </BCol>
                                        <BCol md="4">
                                            <button type="submit" class="btn btn-primary w-100" :disabled="formCanalAviso.processing"><i class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                        </BCol>
                                    </BRow>
                                </form>
                            </div>
                            <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0" :items="canaisAvisoLocal" :columns="parametrosColumns" emptyTitle="" emptyMessage="Nenhum registro encontrado.">
                                <template #body="{ items }">
                                    <tr v-for="ca in items" :key="ca.id">
                                        <template v-if="editingCanalAvisoId !== ca.id">
                                            <td style="width:80px">#{{ ca.id }}</td>
                                            <td>{{ ca.nome }}</td>
                                            <td class="text-end" style="width:150px">
                                                <button type="button" class="btn btn-sm btn-soft-info me-2" @click="startEditCanalAviso(ca)" title="Editar"><i class="ri-pencil-line"></i></button>
                                                <button type="button" class="btn btn-sm btn-soft-danger" @click="destroyCanalAviso(ca.id)" title="Excluir"><i class="ri-delete-bin-line"></i></button>
                                            </td>
                                        </template>
                                        <template v-else>
                                            <td colspan="3">
                                                <div class="d-flex gap-2">
                                                    <input v-model="editCanalAviso.nome" type="text" class="form-control" />
                                                    <button type="button" class="btn btn-success" @click="updateCanalAviso">Salvar</button>
                                                    <button type="button" class="btn btn-light" @click="cancelEditCanalAviso">Cancelar</button>
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
                                <span class="badge bg-success-subtle text-success fs-12">Total: {{ categoriasLocal?.length || 0 }}</span>
                            </div>
                            <div class="border rounded p-3 bg-light-subtle mb-4">
                                <form @submit.prevent="saveCategoria">
                                    <BRow class="g-3 align-items-end">
                                        <BCol md="8">
                                            <label class="form-label">Nome da Categoria</label>
                                            <input v-model="formCategoria.nome" type="text" class="form-control" :class="{ 'is-invalid': formCategoria.errors.nome }" placeholder="Ex.: Consultas, Exames..." />
                                            <div class="invalid-feedback">{{ formCategoria.errors.nome }}</div>
                                        </BCol>
                                        <BCol md="4">
                                            <button type="submit" class="btn btn-primary w-100" :disabled="formCategoria.processing"><i class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                        </BCol>
                                    </BRow>
                                </form>
                            </div>
                            <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0" :items="categoriasLocal" :columns="parametrosColumns" emptyTitle="" emptyMessage="Nenhum registro encontrado.">
                                <template #body="{ items }">
                                    <tr v-for="cat in items" :key="cat.id">
                                        <template v-if="editingCategoriaId !== cat.id">
                                            <td style="width:80px">#{{ cat.id }}</td>
                                            <td>{{ cat.nome }}</td>
                                            <td class="text-end" style="width:150px">
                                                <button type="button" class="btn btn-sm btn-soft-info me-2" @click="startEditCategoria(cat)" title="Editar"><i class="ri-pencil-line"></i></button>
                                                <button type="button" class="btn btn-sm btn-soft-danger" @click="destroyCategoria(cat.id)" title="Excluir"><i class="ri-delete-bin-line"></i></button>
                                            </td>
                                        </template>
                                        <template v-else>
                                            <td colspan="3">
                                                <div class="d-flex gap-2">
                                                    <input v-model="editCategoria.nome" type="text" class="form-control" />
                                                    <button type="button" class="btn btn-success" @click="updateCategoria">Salvar</button>
                                                    <button type="button" class="btn btn-light" @click="cancelEditCategoria">Cancelar</button>
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
"""

full_content = new_template + "\n" + script_and_below

with open('resources/js/Pages/Configuracao/Parametrizacao.vue', 'w', encoding='utf-8') as f:
    f.write(full_content)
    
print('Replaced successfully')
