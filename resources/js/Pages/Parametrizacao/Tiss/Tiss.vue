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
                    <BTab title="24. Tabela de Referência">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Lista de Tabelas de Referência</h6>
                        </div>
                        <div class="border rounded p-3 bg-light-subtle mb-4">
                            <form @submit.prevent="saveTabelaReferencia">
                                <BRow class="g-3 align-items-end">
                                    <BCol md="2">
                                        <label class="form-label">Cód. <span class="text-danger">*</span></label>
                                        <input v-model="formTabelaReferencia.codigo" type="text" class="form-control"
                                            :class="{ 'is-invalid': formTabelaReferencia.errors.codigo }"
                                            placeholder="Ex.: 22" maxlength="2" required />
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
                                                <input v-model="editTabelaReferencia.codigo" type="text"
                                                    class="form-control" placeholder="Cód." style="width: 80px;"
                                                    maxlength="2" required />
                                                <input v-model="editTabelaReferencia.descricao" type="text"
                                                    class="form-control" placeholder="Descrição" required />
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
                    <BTab title="32. Tipo de Atendimento">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Lista de Tipo de Atendimento</h6>
                        </div>
                        <div class="border rounded p-3 bg-light-subtle mb-4">
                            <form @submit.prevent="saveTipoAtendimento">
                                <BRow class="g-3 align-items-end">
                                    <BCol md="2">
                                        <label class="form-label">Cód. <span class="text-danger">*</span></label>
                                        <input v-model="formTipoAtendimento.codigo" type="text" class="form-control"
                                            :class="{ 'is-invalid': formTipoAtendimento.errors.codigo }"
                                            placeholder="Ex.: 01" maxlength="2" required />
                                        <div class="invalid-feedback">{{ formTipoAtendimento.errors.codigo }}</div>
                                    </BCol>
                                    <BCol md="7">
                                        <label class="form-label">Descrição <span class="text-danger">*</span></label>
                                        <input v-model="formTipoAtendimento.descricao" type="text" class="form-control"
                                            :class="{ 'is-invalid': formTipoAtendimento.errors.descricao }"
                                            placeholder="Ex.: Remoção" required />
                                        <div class="invalid-feedback">{{ formTipoAtendimento.errors.descricao }}</div>
                                    </BCol>
                                    <BCol md="3">
                                        <button type="submit" class="btn btn-primary w-100"
                                            :disabled="formTipoAtendimento.processing"><i
                                                class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                    </BCol>
                                </BRow>
                            </form>
                        </div>
                        <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0"
                            :items="tipoAtendimentosLocal"
                            :columns="[{ key: 'codigo', label: 'Cód.' }, { key: 'descricao', label: 'Descrição' }, { key: 'acoes', label: 'Ações', width: '120px' }]"
                            emptyTitle="" emptyMessage="Nenhum registro encontrado.">
                            <template #body="{ items }">
                                <tr v-for="t in items" :key="t.id">
                                    <template v-if="editingTipoAtendimentoId !== t.id">
                                        <td>{{ t.codigo }}</td>
                                        <td>{{ t.descricao }}</td>
                                        <td class="text-end" style="width:150px">
                                            <button type="button" class="btn btn-sm btn-soft-info me-2"
                                                @click="startEditTipoAtendimento(t)" title="Editar"><i
                                                    class="ri-pencil-line"></i></button>
                                            <button type="button" class="btn btn-sm btn-soft-danger"
                                                @click="destroyTipoAtendimento(t.id)" title="Excluir"><i
                                                    class="ri-delete-bin-line"></i></button>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <td colspan="3">
                                            <div class="d-flex gap-2">
                                                <input v-model="editTipoAtendimento.codigo" type="text"
                                                    class="form-control" placeholder="Cód." style="width: 80px;"
                                                    maxlength="2" required />
                                                <input v-model="editTipoAtendimento.descricao" type="text"
                                                    class="form-control" placeholder="Descrição" required />
                                                <button type="button" class="btn btn-success"
                                                    @click="updateTipoAtendimento">Salvar</button>
                                                <button type="button" class="btn btn-light"
                                                    @click="cancelEditTipoAtendimento">Cancelar</button>
                                            </div>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </SimpleTable>
                    </BTab>
                    <BTab title="33. Indicação de Incidência">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Lista de Indicação de Incidência</h6>
                        </div>
                        <div class="border rounded p-3 bg-light-subtle mb-4">
                            <form @submit.prevent="saveIndicacaoIncidencia">
                                <BRow class="g-3 align-items-end">
                                    <BCol md="2">
                                        <label class="form-label">Cód. <span class="text-danger">*</span></label>
                                        <input v-model="formIndicacaoIncidencia.codigo" type="text" class="form-control"
                                            :class="{ 'is-invalid': formIndicacaoIncidencia.errors.codigo }"
                                            placeholder="Ex.: 22" maxlength="2" required />
                                        <div class="invalid-feedback">{{ formIndicacaoIncidencia.errors.codigo }}</div>
                                    </BCol>
                                    <BCol md="7">
                                        <label class="form-label">Descrição <span class="text-danger">*</span></label>
                                        <input v-model="formIndicacaoIncidencia.descricao" type="text"
                                            class="form-control"
                                            :class="{ 'is-invalid': formIndicacaoIncidencia.errors.descricao }"
                                            placeholder="Ex.: Acidente de Trânsito" required />
                                        <div class="invalid-feedback">{{ formIndicacaoIncidencia.errors.descricao }}
                                        </div>
                                    </BCol>
                                    <BCol md="3">
                                        <button type="submit" class="btn btn-primary w-100"
                                            :disabled="formIndicacaoIncidencia.processing"><i
                                                class="ri-add-line align-bottom me-1"></i>
                                            Adicionar</button>
                                    </BCol>
                                </BRow>
                            </form>
                        </div>
                        <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0"
                            :items="indicacaoIncidenciasLocal"
                            :columns="[{ key: 'codigo', label: 'Cód.' }, { key: 'descricao', label: 'Descrição' }, { key: 'acoes', label: 'Ações', width: '120px' }]"
                            emptyTitle="" emptyMessage="Nenhum registro encontrado.">
                            <template #body="{ items }">
                                <tr v-for="t in items" :key="t.id">
                                    <template v-if="editingIndicacaoIncidenciaId !== t.id">
                                        <td>{{ t.codigo }}</td>
                                        <td>{{ t.descricao }}</td>
                                        <td class="text-end" style="width:150px">
                                            <button type="button" class="btn btn-sm btn-soft-info me-2"
                                                @click="startEditIndicacaoIncidencia(t)" title="Editar"><i
                                                    class="ri-pencil-line"></i></button>
                                            <button type="button" class="btn btn-sm btn-soft-danger"
                                                @click="destroyIndicacaoIncidencia(t.id)" title="Excluir"><i
                                                    class="ri-delete-bin-line"></i></button>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <td colspan="3">
                                            <div class="d-flex gap-2">
                                                <input v-model="editIndicacaoIncidencia.codigo" type="text"
                                                    class="form-control" placeholder="Cód." style="width: 80px;"
                                                    maxlength="2" required />
                                                <input v-model="editIndicacaoIncidencia.descricao" type="text"
                                                    class="form-control" placeholder="Descrição" required />
                                                <button type="button" class="btn btn-success"
                                                    @click="updateIndicacaoIncidencia">Salvar</button>
                                                <button type="button" class="btn btn-light"
                                                    @click="cancelEditIndicacaoIncidencia">Cancelar</button>
                                            </div>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </SimpleTable>
                    </BTab>
                    <BTab title="34. Tipo de Consulta">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Lista de Tipo de Consulta</h6>
                        </div>
                        <div class="border rounded p-3 bg-light-subtle mb-4">
                            <form @submit.prevent="saveTipoConsulta">
                                <BRow class="g-3 align-items-end">
                                    <BCol md="2">
                                        <label class="form-label">Cód. <span class="text-danger">*</span></label>
                                        <input v-model="formTipoConsulta.codigo" type="text" class="form-control"
                                            :class="{ 'is-invalid': formTipoConsulta.errors.codigo }"
                                            placeholder="Ex.: 1" maxlength="2" required />
                                        <div class="invalid-feedback">{{ formTipoConsulta.errors.codigo }}</div>
                                    </BCol>
                                    <BCol md="7">
                                        <label class="form-label">Descrição <span class="text-danger">*</span></label>
                                        <input v-model="formTipoConsulta.descricao" type="text" class="form-control"
                                            :class="{ 'is-invalid': formTipoConsulta.errors.descricao }"
                                            placeholder="Ex.: Primeira Consulta" required />
                                        <div class="invalid-feedback">{{ formTipoConsulta.errors.descricao }}</div>
                                    </BCol>
                                    <BCol md="3">
                                        <button type="submit" class="btn btn-primary w-100"
                                            :disabled="formTipoConsulta.processing"><i
                                                class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                    </BCol>
                                </BRow>
                            </form>
                        </div>
                        <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0"
                            :items="tipoConsultasLocal"
                            :columns="[{ key: 'codigo', label: 'Cód.' }, { key: 'descricao', label: 'Descrição' }, { key: 'acoes', label: 'Ações', width: '120px' }]"
                            emptyTitle="" emptyMessage="Nenhum registro encontrado.">
                            <template #body="{ items }">
                                <tr v-for="t in items" :key="t.id">
                                    <template v-if="editingTipoConsultaId !== t.id">
                                        <td>{{ t.codigo }}</td>
                                        <td>{{ t.descricao }}</td>
                                        <td class="text-end" style="width:150px">
                                            <button type="button" class="btn btn-sm btn-soft-info me-2"
                                                @click="startEditTipoConsulta(t)" title="Editar"><i
                                                    class="ri-pencil-line"></i></button>
                                            <button type="button" class="btn btn-sm btn-soft-danger"
                                                @click="destroyTipoConsulta(t.id)" title="Excluir"><i
                                                    class="ri-delete-bin-line"></i></button>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <td colspan="3">
                                            <div class="d-flex gap-2">
                                                <input v-model="editTipoConsulta.codigo" type="text"
                                                    class="form-control" placeholder="Cód." style="width: 80px;"
                                                    maxlength="2" required />
                                                <input v-model="editTipoConsulta.descricao" type="text"
                                                    class="form-control" placeholder="Descrição" required />
                                                <button type="button" class="btn btn-success"
                                                    @click="updateTipoConsulta">Salvar</button>
                                                <button type="button" class="btn btn-light"
                                                    @click="cancelEditTipoConsulta">Cancelar</button>
                                            </div>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </SimpleTable>
                    </BTab>
                    <BTab title="35. Motivo de Encerramento">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Lista de Motivos de Encerramento</h6>
                        </div>
                        <div class="border rounded p-3 bg-light-subtle mb-4">
                            <form @submit.prevent="saveMotivoEncerramento">
                                <BRow class="g-3 align-items-end">
                                    <BCol md="2">
                                        <label class="form-label">Cód. <span class="text-danger">*</span></label>
                                        <input v-model="formMotivoEncerramento.codigo" type="text" class="form-control"
                                            :class="{ 'is-invalid': formMotivoEncerramento.errors.codigo }"
                                            placeholder="Ex.: 11" maxlength="2" required />
                                        <div class="invalid-feedback">{{ formMotivoEncerramento.errors.codigo }}</div>
                                    </BCol>
                                    <BCol md="7">
                                        <label class="form-label">Descrição <span class="text-danger">*</span></label>
                                        <input v-model="formMotivoEncerramento.descricao" type="text" class="form-control"
                                            :class="{ 'is-invalid': formMotivoEncerramento.errors.descricao }"
                                            placeholder="Ex.: Alta curado" required />
                                        <div class="invalid-feedback">{{ formMotivoEncerramento.errors.descricao }}</div>
                                    </BCol>
                                    <BCol md="3">
                                        <button type="submit" class="btn btn-primary w-100"
                                            :disabled="formMotivoEncerramento.processing"><i
                                                class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                    </BCol>
                                </BRow>
                            </form>
                        </div>
                        <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0"
                            :items="motivosEncerramentoLocal"
                            :columns="[{ key: 'codigo', label: 'Cód.' }, { key: 'descricao', label: 'Descrição' }, { key: 'acoes', label: 'Ações', width: '120px' }]"
                            emptyTitle="" emptyMessage="Nenhum registro encontrado.">
                            <template #body="{ items }">
                                <tr v-for="t in items" :key="t.id">
                                    <template v-if="editingMotivoEncerramentoId !== t.id">
                                        <td>{{ t.codigo }}</td>
                                        <td>{{ t.descricao }}</td>
                                        <td class="text-end" style="width:150px">
                                            <button type="button" class="btn btn-sm btn-soft-info me-2"
                                                @click="startEditMotivoEncerramento(t)" title="Editar"><i
                                                    class="ri-pencil-line"></i></button>
                                            <button type="button" class="btn btn-sm btn-soft-danger"
                                                @click="destroyMotivoEncerramento(t.id)" title="Excluir"><i
                                                    class="ri-delete-bin-line"></i></button>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <td colspan="3">
                                            <div class="d-flex gap-2">
                                                <input v-model="editMotivoEncerramento.codigo" type="text"
                                                    class="form-control" placeholder="Cód." style="width: 80px;"
                                                    maxlength="2" required />
                                                <input v-model="editMotivoEncerramento.descricao" type="text"
                                                    class="form-control" placeholder="Descrição" required />
                                                <button type="button" class="btn btn-success"
                                                    @click="updateMotivoEncerramento">Salvar</button>
                                                <button type="button" class="btn btn-light"
                                                    @click="cancelEditMotivoEncerramento">Cancelar</button>
                                            </div>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </SimpleTable>
                    </BTab>
                    <BTab title="43. Via de Acesso">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Lista de Vias de Acesso</h6>
                        </div>
                        <div class="border rounded p-3 bg-light-subtle mb-4">
                            <form @submit.prevent="saveViaAcesso">
                                <BRow class="g-3 align-items-end">
                                    <BCol md="2">
                                        <label class="form-label">Cód. <span class="text-danger">*</span></label>
                                        <input v-model="formViaAcesso.codigo" type="text" class="form-control"
                                            :class="{ 'is-invalid': formViaAcesso.errors.codigo }"
                                            placeholder="Ex.: 1" maxlength="2" required />
                                        <div class="invalid-feedback">{{ formViaAcesso.errors.codigo }}</div>
                                    </BCol>
                                    <BCol md="7">
                                        <label class="form-label">Descrição <span class="text-danger">*</span></label>
                                        <input v-model="formViaAcesso.descricao" type="text" class="form-control"
                                            :class="{ 'is-invalid': formViaAcesso.errors.descricao }"
                                            placeholder="Ex.: Única" required />
                                        <div class="invalid-feedback">{{ formViaAcesso.errors.descricao }}</div>
                                    </BCol>
                                    <BCol md="3">
                                        <button type="submit" class="btn btn-primary w-100"
                                            :disabled="formViaAcesso.processing"><i
                                                class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                    </BCol>
                                </BRow>
                            </form>
                        </div>
                        <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0"
                            :items="viasAcessoLocal"
                            :columns="[{ key: 'codigo', label: 'Cód.' }, { key: 'descricao', label: 'Descrição' }, { key: 'acoes', label: 'Ações', width: '120px' }]"
                            emptyTitle="" emptyMessage="Nenhum registro encontrado.">
                            <template #body="{ items }">
                                <tr v-for="t in items" :key="t.id">
                                    <template v-if="editingViaAcessoId !== t.id">
                                        <td>{{ t.codigo }}</td>
                                        <td>{{ t.descricao }}</td>
                                        <td class="text-end" style="width:150px">
                                            <button type="button" class="btn btn-sm btn-soft-info me-2"
                                                @click="startEditViaAcesso(t)" title="Editar"><i
                                                    class="ri-pencil-line"></i></button>
                                            <button type="button" class="btn btn-sm btn-soft-danger"
                                                @click="destroyViaAcesso(t.id)" title="Excluir"><i
                                                    class="ri-delete-bin-line"></i></button>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <td colspan="3">
                                            <div class="d-flex gap-2">
                                                <input v-model="editViaAcesso.codigo" type="text"
                                                    class="form-control" placeholder="Cód." style="width: 80px;"
                                                    maxlength="2" required />
                                                <input v-model="editViaAcesso.descricao" type="text"
                                                    class="form-control" placeholder="Descrição" required />
                                                <button type="button" class="btn btn-success"
                                                    @click="updateViaAcesso">Salvar</button>
                                                <button type="button" class="btn btn-light"
                                                    @click="cancelEditViaAcesso">Cancelar</button>
                                            </div>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </SimpleTable>
                    </BTab>
                    <BTab title="44. Técnica Utilizada">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Lista de Técnicas Utilizadas</h6>
                        </div>
                        <div class="border rounded p-3 bg-light-subtle mb-4">
                            <form @submit.prevent="saveTecnicaUtilizada">
                                <BRow class="g-3 align-items-end">
                                    <BCol md="2">
                                        <label class="form-label">Cód. <span class="text-danger">*</span></label>
                                        <input v-model="formTecnicaUtilizada.codigo" type="text" class="form-control"
                                            :class="{ 'is-invalid': formTecnicaUtilizada.errors.codigo }"
                                            placeholder="Ex.: 1" maxlength="2" required />
                                        <div class="invalid-feedback">{{ formTecnicaUtilizada.errors.codigo }}</div>
                                    </BCol>
                                    <BCol md="7">
                                        <label class="form-label">Descrição <span class="text-danger">*</span></label>
                                        <input v-model="formTecnicaUtilizada.descricao" type="text" class="form-control"
                                            :class="{ 'is-invalid': formTecnicaUtilizada.errors.descricao }"
                                            placeholder="Ex.: Convencional" required />
                                        <div class="invalid-feedback">{{ formTecnicaUtilizada.errors.descricao }}</div>
                                    </BCol>
                                    <BCol md="3">
                                        <button type="submit" class="btn btn-primary w-100"
                                            :disabled="formTecnicaUtilizada.processing"><i
                                                class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                    </BCol>
                                </BRow>
                            </form>
                        </div>
                        <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0"
                            :items="tecnicasUtilizadasLocal"
                            :columns="[{ key: 'codigo', label: 'Cód.' }, { key: 'descricao', label: 'Descrição' }, { key: 'acoes', label: 'Ações', width: '120px' }]"
                            emptyTitle="" emptyMessage="Nenhum registro encontrado.">
                            <template #body="{ items }">
                                <tr v-for="t in items" :key="t.id">
                                    <template v-if="editingTecnicaUtilizadaId !== t.id">
                                        <td>{{ t.codigo }}</td>
                                        <td>{{ t.descricao }}</td>
                                        <td class="text-end" style="width:150px">
                                            <button type="button" class="btn btn-sm btn-soft-info me-2"
                                                @click="startEditTecnicaUtilizada(t)" title="Editar"><i
                                                    class="ri-pencil-line"></i></button>
                                            <button type="button" class="btn btn-sm btn-soft-danger"
                                                @click="destroyTecnicaUtilizada(t.id)" title="Excluir"><i
                                                    class="ri-delete-bin-line"></i></button>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <td colspan="3">
                                            <div class="d-flex gap-2">
                                                <input v-model="editTecnicaUtilizada.codigo" type="text"
                                                    class="form-control" placeholder="Cód." style="width: 80px;"
                                                    maxlength="2" required />
                                                <input v-model="editTecnicaUtilizada.descricao" type="text"
                                                    class="form-control" placeholder="Descrição" required />
                                                <button type="button" class="btn btn-success"
                                                    @click="updateTecnicaUtilizada">Salvar</button>
                                                <button type="button" class="btn btn-light"
                                                    @click="cancelEditTecnicaUtilizada">Cancelar</button>
                                            </div>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </SimpleTable>
                    </BTab>
                    <BTab title="49. Grau de Participação">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Lista de Graus de Participação</h6>
                        </div>
                        <div class="border rounded p-3 bg-light-subtle mb-4">
                            <form @submit.prevent="saveGrauParticipacao">
                                <BRow class="g-3 align-items-end">
                                    <BCol md="2">
                                        <label class="form-label">Cód. <span class="text-danger">*</span></label>
                                        <input v-model="formGrauParticipacao.codigo" type="text" class="form-control"
                                            :class="{ 'is-invalid': formGrauParticipacao.errors.codigo }"
                                            placeholder="Ex.: 1" maxlength="2" required />
                                        <div class="invalid-feedback">{{ formGrauParticipacao.errors.codigo }}</div>
                                    </BCol>
                                    <BCol md="7">
                                        <label class="form-label">Descrição <span class="text-danger">*</span></label>
                                        <input v-model="formGrauParticipacao.descricao" type="text" class="form-control"
                                            :class="{ 'is-invalid': formGrauParticipacao.errors.descricao }"
                                            placeholder="Ex.: Cirurgião" required />
                                        <div class="invalid-feedback">{{ formGrauParticipacao.errors.descricao }}</div>
                                    </BCol>
                                    <BCol md="3">
                                        <button type="submit" class="btn btn-primary w-100"
                                            :disabled="formGrauParticipacao.processing"><i
                                                class="ri-add-line align-bottom me-1"></i> Adicionar</button>
                                    </BCol>
                                </BRow>
                            </form>
                        </div>
                        <SimpleTable variant="borderless" tableClass="table-hover align-middle table-nowrap mb-0"
                            :items="grausParticipacaoLocal"
                            :columns="[{ key: 'codigo', label: 'Cód.' }, { key: 'descricao', label: 'Descrição' }, { key: 'acoes', label: 'Ações', width: '120px' }]"
                            emptyTitle="" emptyMessage="Nenhum registro encontrado.">
                            <template #body="{ items }">
                                <tr v-for="t in items" :key="t.id">
                                    <template v-if="editingGrauParticipacaoId !== t.id">
                                        <td>{{ t.codigo }}</td>
                                        <td>{{ t.descricao }}</td>
                                        <td class="text-end" style="width:150px">
                                            <button type="button" class="btn btn-sm btn-soft-info me-2"
                                                @click="startEditGrauParticipacao(t)" title="Editar"><i
                                                    class="ri-pencil-line"></i></button>
                                            <button type="button" class="btn btn-sm btn-soft-danger"
                                                @click="destroyGrauParticipacao(t.id)" title="Excluir"><i
                                                    class="ri-delete-bin-line"></i></button>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <td colspan="3">
                                            <div class="d-flex gap-2">
                                                <input v-model="editGrauParticipacao.codigo" type="text"
                                                    class="form-control" placeholder="Cód." style="width: 80px;"
                                                    maxlength="2" required />
                                                <input v-model="editGrauParticipacao.descricao" type="text"
                                                    class="form-control" placeholder="Descrição" required />
                                                <button type="button" class="btn btn-success"
                                                    @click="updateGrauParticipacao">Salvar</button>
                                                <button type="button" class="btn btn-light"
                                                    @click="cancelEditGrauParticipacao">Cancelar</button>
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
    tipoAtendimentos: { type: Array, default: () => [] },
    indicacaoIncidencias: { type: Array, default: () => [] },
    tipoConsultas: { type: Array, default: () => [] },
    motivosEncerramento: { type: Array, default: () => [] },
    viasAcesso: { type: Array, default: () => [] },
    tecnicasUtilizadas: { type: Array, default: () => [] },
    grausParticipacao: { type: Array, default: () => [] },
});

const conselhosLocal = ref([...(props.conselhos || [])]);
const caraterLocal = ref([...(props.caraterAtendimentos || [])]);
const tabelasReferenciaLocal = ref([...(props.tabelasReferencia || [])]);
const tipoAtendimentosLocal = ref([...(props.tipoAtendimentos || [])]);
const indicacaoIncidenciasLocal = ref([...(props.indicacaoIncidencias || [])]);
const tipoConsultasLocal = ref([...(props.tipoConsultas || [])]);
const motivosEncerramentoLocal = ref([...(props.motivosEncerramento || [])]);
const viasAcessoLocal = ref([...(props.viasAcesso || [])]);
const tecnicasUtilizadasLocal = ref([...(props.tecnicasUtilizadas || [])]);
const grausParticipacaoLocal = ref([...(props.grausParticipacao || [])]);

watch(() => props.conselhos, (v) => { conselhosLocal.value = [...(v || [])]; });
watch(() => props.caraterAtendimentos, (v) => { caraterLocal.value = [...(v || [])]; });
watch(() => props.tabelasReferencia, (v) => { tabelasReferenciaLocal.value = [...(v || [])]; });
watch(() => props.tipoAtendimentos, (v) => { tipoAtendimentosLocal.value = [...(v || [])]; });
watch(() => props.indicacaoIncidencias, (v) => { indicacaoIncidenciasLocal.value = [...(v || [])]; });
watch(() => props.tipoConsultas, (v) => { tipoConsultasLocal.value = [...(v || [])]; });
watch(() => props.motivosEncerramento, (v) => { motivosEncerramentoLocal.value = [...(v || [])]; });
watch(() => props.viasAcesso, (v) => { viasAcessoLocal.value = [...(v || [])]; });
watch(() => props.tecnicasUtilizadas, (v) => { tecnicasUtilizadasLocal.value = [...(v || [])]; });
watch(() => props.grausParticipacao, (v) => { grausParticipacaoLocal.value = [...(v || [])]; });

const formConselho = useForm({ codigo: "", sigla: "", descricao: "" });
const editConselho = useForm({ codigo: "", sigla: "", descricao: "" });
const editingConselhoId = ref(null);

const formTipoAtendimento = useForm({ codigo: "", descricao: "" });
const editTipoAtendimento = useForm({ codigo: "", descricao: "" });
const editingTipoAtendimentoId = ref(null);

const formIndicacaoIncidencia = useForm({ codigo: "", descricao: "" });
const editIndicacaoIncidencia = useForm({ codigo: "", descricao: "" });
const editingIndicacaoIncidenciaId = ref(null);

const formTipoConsulta = useForm({ codigo: "", descricao: "" });
const editTipoConsulta = useForm({ codigo: "", descricao: "" });
const editingTipoConsultaId = ref(null);

const formMotivoEncerramento = useForm({ codigo: "", descricao: "" });
const editMotivoEncerramento = useForm({ codigo: "", descricao: "" });
const editingMotivoEncerramentoId = ref(null);

const formViaAcesso = useForm({ codigo: "", descricao: "" });
const editViaAcesso = useForm({ codigo: "", descricao: "" });
const editingViaAcessoId = ref(null);

const formTecnicaUtilizada = useForm({ codigo: "", descricao: "" });
const editTecnicaUtilizada = useForm({ codigo: "", descricao: "" });
const editingTecnicaUtilizadaId = ref(null);

const formGrauParticipacao = useForm({ codigo: "", descricao: "" });
const editGrauParticipacao = useForm({ codigo: "", descricao: "" });
const editingGrauParticipacaoId = ref(null);

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

const saveTipoAtendimento = () => {
    formTipoAtendimento.post("/parametros/tipo-atendimento", {
        preserveScroll: true,
        onSuccess: () => {
            formTipoAtendimento.reset();
            router.reload({ only: ['tipoAtendimentos'] });
        }
    });
};

const startEditTipoAtendimento = (t) => {
    editingTipoAtendimentoId.value = t.id;
    editTipoAtendimento.codigo = t.codigo || "";
    editTipoAtendimento.descricao = t.descricao || "";
};

const cancelEditTipoAtendimento = () => {
    editingTipoAtendimentoId.value = null;
    editTipoAtendimento.reset();
};

const updateTipoAtendimento = () => {
    editTipoAtendimento.put(`/parametros/tipo-atendimento/${editingTipoAtendimentoId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingTipoAtendimentoId.value = null;
            editTipoAtendimento.reset();
            router.reload({ only: ['tipoAtendimentos'] });
        }
    });
};

const destroyTipoAtendimento = (id) => {
    Swal.fire({
        title: "Tem certeza?",
        text: "Excluir este tipo de atendimento não poderá ser desfeito.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#f46a6a",
        confirmButtonText: "Sim, excluir",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/parametros/tipo-atendimento/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ['tipoAtendimentos'] });
                }
            });
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

const saveIndicacaoIncidencia = () => {
    formIndicacaoIncidencia.post("/parametros/indicacao-incidencia", {
        preserveScroll: true,
        onSuccess: () => {
            formIndicacaoIncidencia.reset();
            router.reload({ only: ['indicacaoIncidencias'] });
        }
    });
};

const startEditIndicacaoIncidencia = (t) => {
    editingIndicacaoIncidenciaId.value = t.id;
    editIndicacaoIncidencia.codigo = t.codigo || "";
    editIndicacaoIncidencia.descricao = t.descricao || "";
};

const cancelEditIndicacaoIncidencia = () => {
    editingIndicacaoIncidenciaId.value = null;
    editIndicacaoIncidencia.reset();
};

const updateIndicacaoIncidencia = () => {
    editIndicacaoIncidencia.put(`/parametros/indicacao-incidencia/${editingIndicacaoIncidenciaId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingIndicacaoIncidenciaId.value = null;
            editIndicacaoIncidencia.reset();
            router.reload({ only: ['indicacaoIncidencias'] });
        }
    });
};

const destroyIndicacaoIncidencia = (id) => {
    Swal.fire({
        title: "Tem certeza?",
        text: "Excluir esta indicação de incidência não poderá ser desfeito.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#f46a6a",
        confirmButtonText: "Sim, excluir",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/parametros/indicacao-incidencia/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ['indicacaoIncidencias'] });
                }
            });
        }
    });
};

const saveTipoConsulta = () => {
    formTipoConsulta.post("/parametros/tipo-consulta", {
        preserveScroll: true,
        onSuccess: () => {
            formTipoConsulta.reset();
            router.reload({ only: ['tipoConsultas'] });
        }
    });
};

const startEditTipoConsulta = (t) => {
    editingTipoConsultaId.value = t.id;
    editTipoConsulta.codigo = t.codigo || "";
    editTipoConsulta.descricao = t.descricao || "";
};

const cancelEditTipoConsulta = () => {
    editingTipoConsultaId.value = null;
    editTipoConsulta.reset();
};

const updateTipoConsulta = () => {
    editTipoConsulta.put(`/parametros/tipo-consulta/${editingTipoConsultaId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingTipoConsultaId.value = null;
            editTipoConsulta.reset();
            router.reload({ only: ['tipoConsultas'] });
        }
    });
};

const destroyTipoConsulta = (id) => {
    Swal.fire({
        title: "Tem certeza?",
        text: "Excluir este tipo de consulta não poderá ser desfeito.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#f46a6a",
        confirmButtonText: "Sim, excluir",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/parametros/tipo-consulta/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ['tipoConsultas'] });
                }
            });
        }
    });
};

const saveMotivoEncerramento = () => {
    formMotivoEncerramento.post("/parametros/motivo-encerramento", {
        preserveScroll: true,
        onSuccess: () => {
            formMotivoEncerramento.reset();
            router.reload({ only: ['motivosEncerramento'] });
        }
    });
};

const startEditMotivoEncerramento = (t) => {
    editingMotivoEncerramentoId.value = t.id;
    editMotivoEncerramento.codigo = t.codigo || "";
    editMotivoEncerramento.descricao = t.descricao || "";
};

const cancelEditMotivoEncerramento = () => {
    editingMotivoEncerramentoId.value = null;
    editMotivoEncerramento.reset();
};

const updateMotivoEncerramento = () => {
    editMotivoEncerramento.put(`/parametros/motivo-encerramento/${editingMotivoEncerramentoId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingMotivoEncerramentoId.value = null;
            editMotivoEncerramento.reset();
            router.reload({ only: ['motivosEncerramento'] });
        }
    });
};

const destroyMotivoEncerramento = (id) => {
    Swal.fire({
        title: "Tem certeza?",
        text: "Excluir este motivo de encerramento não poderá ser desfeito.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#f46a6a",
        confirmButtonText: "Sim, excluir",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/parametros/motivo-encerramento/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ['motivosEncerramento'] });
                }
            });
        }
    });
};

const saveViaAcesso = () => {
    formViaAcesso.post("/parametros/via-acesso", {
        preserveScroll: true,
        onSuccess: () => {
            formViaAcesso.reset();
            router.reload({ only: ['viasAcesso'] });
        }
    });
};

const startEditViaAcesso = (t) => {
    editingViaAcessoId.value = t.id;
    editViaAcesso.codigo = t.codigo || "";
    editViaAcesso.descricao = t.descricao || "";
};

const cancelEditViaAcesso = () => {
    editingViaAcessoId.value = null;
    editViaAcesso.reset();
};

const updateViaAcesso = () => {
    editViaAcesso.put(`/parametros/via-acesso/${editingViaAcessoId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingViaAcessoId.value = null;
            editViaAcesso.reset();
            router.reload({ only: ['viasAcesso'] });
        }
    });
};

const destroyViaAcesso = (id) => {
    Swal.fire({
        title: "Tem certeza?",
        text: "Excluir esta via de acesso não poderá ser desfeito.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#f46a6a",
        confirmButtonText: "Sim, excluir",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/parametros/via-acesso/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ['viasAcesso'] });
                }
            });
        }
    });
};

const saveTecnicaUtilizada = () => {
    formTecnicaUtilizada.post("/parametros/tecnica-utilizada", {
        preserveScroll: true,
        onSuccess: () => {
            formTecnicaUtilizada.reset();
            router.reload({ only: ['tecnicasUtilizadas'] });
        }
    });
};

const startEditTecnicaUtilizada = (t) => {
    editingTecnicaUtilizadaId.value = t.id;
    editTecnicaUtilizada.codigo = t.codigo || "";
    editTecnicaUtilizada.descricao = t.descricao || "";
};

const cancelEditTecnicaUtilizada = () => {
    editingTecnicaUtilizadaId.value = null;
    editTecnicaUtilizada.reset();
};

const updateTecnicaUtilizada = () => {
    editTecnicaUtilizada.put(`/parametros/tecnica-utilizada/${editingTecnicaUtilizadaId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingTecnicaUtilizadaId.value = null;
            editTecnicaUtilizada.reset();
            router.reload({ only: ['tecnicasUtilizadas'] });
        }
    });
};

const destroyTecnicaUtilizada = (id) => {
    Swal.fire({
        title: "Tem certeza?",
        text: "Excluir esta técnica utilizada não poderá ser desfeito.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#f46a6a",
        confirmButtonText: "Sim, excluir",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/parametros/tecnica-utilizada/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ['tecnicasUtilizadas'] });
                }
            });
        }
    });
};

const saveGrauParticipacao = () => {
    formGrauParticipacao.post("/parametros/grau-participacao", {
        preserveScroll: true,
        onSuccess: () => {
            formGrauParticipacao.reset();
            router.reload({ only: ['grausParticipacao'] });
        }
    });
};

const startEditGrauParticipacao = (t) => {
    editingGrauParticipacaoId.value = t.id;
    editGrauParticipacao.codigo = t.codigo || "";
    editGrauParticipacao.descricao = t.descricao || "";
};

const cancelEditGrauParticipacao = () => {
    editingGrauParticipacaoId.value = null;
    editGrauParticipacao.reset();
};

const updateGrauParticipacao = () => {
    editGrauParticipacao.put(`/parametros/grau-participacao/${editingGrauParticipacaoId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingGrauParticipacaoId.value = null;
            editGrauParticipacao.reset();
            router.reload({ only: ['grausParticipacao'] });
        }
    });
};

const destroyGrauParticipacao = (id) => {
    Swal.fire({
        title: "Tem certeza?",
        text: "Excluir este grau de participação não poderá ser desfeito.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#f46a6a",
        confirmButtonText: "Sim, excluir",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/parametros/grau-participacao/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ['grausParticipacao'] });
                }
            });
        }
    });
};
</script>

<style scoped>
:deep(.nav-tabs-custom .nav-link.active) {
    background-color: transparent !important;
}
</style>
