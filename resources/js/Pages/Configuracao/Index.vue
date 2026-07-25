<template>
  <Layout>
    <Head title="Configurações" />
    <PageHeader title="Configurações" pageTitle="Menu" />
    <BRow class="g-0">
      <BTabs
        nav-class="custom-verti-nav-pills text-center config-nav"
        nav-wrapper-class="col-lg-3 config-nav-wrapper"
        content-class="text-muted mt-3 mt-lg-0 col-lg-9"
        pills
        vertical
        justified
      >
        <BTab active>
          <template #title>
            <i class="ri-equalizer-line d-block fs-3xl mb-1"></i>Parametrização
          </template>
          <Parametrizacao
            :estadosCivis="props.estadosCivis"
            :tiposSanguineos="props.tiposSanguineos"
            :canaisAviso="props.canaisAviso"
            :parentescos="props.parentescos"
            :categoriasProcedimento="props.categoriasProcedimento"
          />
        </BTab>
        <BTab>
          <template #title>
            <i class="ri-first-aid-kit-line d-block fs-3xl mb-1"></i>Especialidades
          </template>
          <Especialidade :especialidades="props.especialidades" :procedimentos="props.procedimentos" :tuss="props.tuss" />
        </BTab>
        <BTab>
          <template #title>
            <i class="ri-layout-grid-line d-block fs-3xl mb-1"></i>Tabela TUSS
          </template>
          <BCard class="shadow-sm config-card">
            <BCardHeader class="bg-light-subtle p-3 border-0">
              <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                <BCardTitle class="mb-0"><i class="ri-layout-grid-line text-primary me-2"></i>Tabela TUSS</BCardTitle>
                <div class="d-flex align-items-center gap-2">
                  <a
                    href="#"
                    class="link-primary fw-semibold text-nowrap link-offset-2 link-underline-opacity-0 link-underline-opacity-100-hover"
                    title="Importar registros via arquivo CSV"
                    :class="{ 'pe-none opacity-50': tussImportProcessing || tussCreateForm.processing }"
                    :aria-disabled="(tussImportProcessing || tussCreateForm.processing) ? 'true' : 'false'"
                    @click.prevent="(tussImportProcessing || tussCreateForm.processing) ? null : openTussImportModal()"
                  >
                    <i class="ri-upload-2-line me-1"></i>
                    Importar CSV
                  </a>
                </div>
              </div>
            </BCardHeader>
            <BCardBody>
              <p class="text-muted mb-3">Gerencie os procedimentos padronizados pela tabela TUSS.</p>
              <div class="d-flex justify-content-end align-items-center gap-2 mb-2">
                <BDropdown
                  class="position-static"
                  menu-class="shadow-lg dropdown-menu-end tuss-filter-menu"
                  toggle-class="btn btn-sm btn-soft-primary"
                  variant="soft-primary"
                  size="sm"
                >
                  <template #button-content>
                    <i class="ri-filter-3-line me-1"></i>
                    Tabela: <span class="fw-semibold">{{ tussTabelaFilter || 'Todas' }}</span>
                  </template>
                  <BDropdownItem :active="tussTabelaFilter === ''" @click="setTussTabelaFilter('')">Todas</BDropdownItem>
                  <BDropdownItem v-for="t in allowedTabelas" :key="t" :active="tussTabelaFilter === t" @click="setTussTabelaFilter(t)">
                    {{ t }}
                  </BDropdownItem>
                </BDropdown>
              </div>

              <TableGrid
                :key="tussTableKey"
                :columns="tussColumns"
                :data="[]"
                :serverUrl="'/tuss/list'"
                :serverQuery="tussServerQuery"
                :tableTitle="'Registros TUSS'"
                :showStatus="false"
                :searchPlaceholder="'Buscar por código, descrição ou tabela'"
                :showCheckbox="false"
                :showAddButton="true"
                :showActions="true"
                :actionsConfig="{ delete: false, edit: true, show: false, diary: false, print: false, download: false, restore: false, receive: false }"
                :compactSpacing="true"
                @add="openTussCreateModal"
                @edit="openTussEditModal"
              />

              <Modal
                v-model="tussCreateModalOpen"
                title="Adicionar registro"
                size="xl"
                :name-button="'Salvar'"
                :processing="tussCreateForm.processing"
                :disable-close="tussCreateForm.processing"
                @save="storeTuss"
              >
                <div class="text-muted small mb-3">Os campos <span class="text-danger">*</span> são obrigatórios</div>
                <BRow class="g-3">
                  <BCol md="3">
                    <label class="form-label">Tabela <span class="text-danger">*</span></label>
                    <select v-model.trim="tussCreateForm.tabela" data-choices class="form-select" :class="{ 'is-invalid': !!tussCreateForm.errors.tabela }">
                      <option value="">Selecione</option>
                      <option v-for="t in allowedTabelas" :key="t" :value="t">{{ t }}</option>
                    </select>
                    <div class="invalid-feedback">{{ tussCreateForm.errors.tabela }}</div>
                  </BCol>
                  <BCol md="3">
                    <label class="form-label">Código <span class="text-danger">*</span></label>
                    <input v-model.trim="tussCreateForm.codigo" type="text" class="form-control" placeholder="Ex.: 000000" :class="{ 'is-invalid': !!tussCreateForm.errors.codigo }" />
                    <div class="invalid-feedback">{{ tussCreateForm.errors.codigo }}</div>
                  </BCol>
                  <BCol md="6">
                    <label class="form-label">Descrição</label>
                    <input v-model.trim="tussCreateForm.descricao" type="text" class="form-control" placeholder="Ex.: Procedimento Exemplo" :class="{ 'is-invalid': !!tussCreateForm.errors.descricao }" />
                    <div class="invalid-feedback">{{ tussCreateForm.errors.descricao }}</div>
                  </BCol>
                </BRow>

                <details class="mt-3">
                  <summary class="small text-primary">Campos avançados</summary>
                  <BRow class="g-3 mt-1">
                    <BCol md="3">
                      <label class="form-label">É tratamento?</label>
                      <select v-model="tussCreateForm.eh_tratamento" class="form-select" :class="{ 'is-invalid': !!tussCreateForm.errors.eh_tratamento }">
                        <option :value="false">Não</option>
                        <option :value="true">Sim</option>
                      </select>
                      <div class="invalid-feedback">{{ tussCreateForm.errors.eh_tratamento }}</div>
                    </BCol>
                    <BCol md="3" v-if="isTussCreateTratamento">
                      <label class="form-label">Qtd. Sessões</label>
                      <input v-model.number="tussCreateForm.quantidade_sessoes" type="number" min="1" class="form-control" :class="{ 'is-invalid': !!tussCreateForm.errors.quantidade_sessoes }" />
                      <div class="invalid-feedback">{{ tussCreateForm.errors.quantidade_sessoes }}</div>
                    </BCol>
                    <BCol md="2">
                      <label class="form-label">m² Filme</label>
                      <input v-model.trim="tussCreateForm.m2_filme" type="text" class="form-control" placeholder="Ex.: 0" :class="{ 'is-invalid': !!tussCreateForm.errors.m2_filme }" />
                      <div class="invalid-feedback">{{ tussCreateForm.errors.m2_filme }}</div>
                    </BCol>
                    <BCol md="2">
                      <label class="form-label">Auxiliares</label>
                      <input v-model.trim="tussCreateForm.auxiliares" type="text" class="form-control" placeholder="Ex.: 0" :class="{ 'is-invalid': !!tussCreateForm.errors.auxiliares }" />
                      <div class="invalid-feedback">{{ tussCreateForm.errors.auxiliares }}</div>
                    </BCol>
                    <BCol md="2">
                      <label class="form-label">Incidência</label>
                      <input v-model.trim="tussCreateForm.incidencia" type="text" class="form-control" placeholder="Ex.: 0" :class="{ 'is-invalid': !!tussCreateForm.errors.incidencia }" />
                      <div class="invalid-feedback">{{ tussCreateForm.errors.incidencia }}</div>
                    </BCol>
                    <BCol md="2">
                      <label class="form-label">Porte</label>
                      <input v-model.trim="tussCreateForm.porte" type="text" class="form-control" placeholder="Ex.: A" :class="{ 'is-invalid': !!tussCreateForm.errors.porte }" />
                      <div class="invalid-feedback">{{ tussCreateForm.errors.porte }}</div>
                    </BCol>
                    <BCol md="2">
                      <label class="form-label">CH</label>
                      <input v-model.trim="tussCreateForm.ch" type="text" class="form-control" placeholder="Ex.: 100" :class="{ 'is-invalid': !!tussCreateForm.errors.ch }" />
                      <div class="invalid-feedback">{{ tussCreateForm.errors.ch }}</div>
                    </BCol>
                    <BCol md="2">
                      <label class="form-label">CO</label>
                      <input v-model.trim="tussCreateForm.co" type="text" class="form-control" placeholder="Ex.: 1" :class="{ 'is-invalid': !!tussCreateForm.errors.co }" />
                      <div class="invalid-feedback">{{ tussCreateForm.errors.co }}</div>
                    </BCol>
                    <BCol md="3">
                      <label class="form-label">Total</label>
                      <input :value="tussCreateTotalDisplay" type="text" class="form-control" placeholder="Calculado automaticamente: CH + CO" disabled />
                    </BCol>
                    <BCol cols="12">
                      <div class="form-text">Aceita valores com vírgula ou ponto (ex.: 10,5 ou 10.5).</div>
                    </BCol>
                  </BRow>
                </details>
              </Modal>

              <Modal
                v-model="tussEditModalOpen"
                title="Editar registro"
                size="xl"
                :name-button="'Salvar alterações'"
                :processing="tussEditForm.processing"
                :disable-close="tussEditForm.processing"
                @save="updateTuss"
              >
                <div class="text-muted small mb-3">Tabela e código não podem ser alterados.</div>
                <BRow class="g-3">
                  <BCol md="3">
                    <label class="form-label">Tabela</label>
                    <input v-model.trim="tussEditForm.tabela" type="text" class="form-control" disabled />
                  </BCol>
                  <BCol md="3">
                    <label class="form-label">Código</label>
                    <input v-model.trim="tussEditForm.codigo" type="text" class="form-control" disabled />
                  </BCol>
                  <BCol md="6">
                    <label class="form-label">Descrição</label>
                    <input v-model.trim="tussEditForm.descricao" type="text" class="form-control" placeholder="Ex.: Procedimento Exemplo" :class="{ 'is-invalid': !!tussEditForm.errors.descricao }" />
                    <div class="invalid-feedback">{{ tussEditForm.errors.descricao }}</div>
                  </BCol>
                </BRow>

                <details class="mt-3">
                  <summary class="small text-primary">Campos avançados</summary>
                  <BRow class="g-3 mt-1">
                    <BCol md="3">
                      <label class="form-label">É tratamento?</label>
                      <select v-model="tussEditForm.eh_tratamento" class="form-select" :class="{ 'is-invalid': !!tussEditForm.errors.eh_tratamento }">
                        <option :value="false">Não</option>
                        <option :value="true">Sim</option>
                      </select>
                      <div class="invalid-feedback">{{ tussEditForm.errors.eh_tratamento }}</div>
                    </BCol>
                    <BCol md="3" v-if="isTussEditTratamento">
                      <label class="form-label">Qtd. Sessões</label>
                      <input v-model.number="tussEditForm.quantidade_sessoes" type="number" min="1" class="form-control" :class="{ 'is-invalid': !!tussEditForm.errors.quantidade_sessoes }" />
                      <div class="invalid-feedback">{{ tussEditForm.errors.quantidade_sessoes }}</div>
                    </BCol>
                    <BCol md="2">
                      <label class="form-label">m² Filme</label>
                      <input v-model.trim="tussEditForm.m2_filme" type="text" class="form-control" placeholder="Ex.: 0" :class="{ 'is-invalid': !!tussEditForm.errors.m2_filme }" />
                      <div class="invalid-feedback">{{ tussEditForm.errors.m2_filme }}</div>
                    </BCol>
                    <BCol md="2">
                      <label class="form-label">Auxiliares</label>
                      <input v-model.trim="tussEditForm.auxiliares" type="text" class="form-control" placeholder="Ex.: 0" :class="{ 'is-invalid': !!tussEditForm.errors.auxiliares }" />
                      <div class="invalid-feedback">{{ tussEditForm.errors.auxiliares }}</div>
                    </BCol>
                    <BCol md="2">
                      <label class="form-label">Incidência</label>
                      <input v-model.trim="tussEditForm.incidencia" type="text" class="form-control" placeholder="Ex.: 0" :class="{ 'is-invalid': !!tussEditForm.errors.incidencia }" />
                      <div class="invalid-feedback">{{ tussEditForm.errors.incidencia }}</div>
                    </BCol>
                    <BCol md="2">
                      <label class="form-label">Porte</label>
                      <input v-model.trim="tussEditForm.porte" type="text" class="form-control" placeholder="Ex.: A" :class="{ 'is-invalid': !!tussEditForm.errors.porte }" />
                      <div class="invalid-feedback">{{ tussEditForm.errors.porte }}</div>
                    </BCol>
                    <BCol md="2">
                      <label class="form-label">CH</label>
                      <input v-model.trim="tussEditForm.ch" type="text" class="form-control" placeholder="Ex.: 100" :class="{ 'is-invalid': !!tussEditForm.errors.ch }" />
                      <div class="invalid-feedback">{{ tussEditForm.errors.ch }}</div>
                    </BCol>
                    <BCol md="2">
                      <label class="form-label">CO</label>
                      <input v-model.trim="tussEditForm.co" type="text" class="form-control" placeholder="Ex.: 1" :class="{ 'is-invalid': !!tussEditForm.errors.co }" />
                      <div class="invalid-feedback">{{ tussEditForm.errors.co }}</div>
                    </BCol>
                    <BCol md="3">
                      <label class="form-label">Total</label>
                      <input :value="tussEditTotalDisplay" type="text" class="form-control" placeholder="Calculado automaticamente: CH + CO" disabled />
                    </BCol>
                    <BCol cols="12">
                      <div class="form-text">Aceita valores com vírgula ou ponto (ex.: 10,5 ou 10.5).</div>
                    </BCol>
                  </BRow>
                </details>
              </Modal>

              <Modal
                v-model="tussImportModalOpen"
                title="Importar arquivo CSV"
                size="lg"
                :name-button="'Importar'"
                :processing="tussImportProcessing"
                :disable-close="tussImportProcessing"
                @save="importTuss"
              >
                <div class="d-flex align-items-start justify-content-between gap-2 pb-2 mb-3 border-bottom">
                  <div class="text-muted small d-flex align-items-center gap-2">
                    <i class="ri-information-line"></i>
                    <span>Os campos <span class="text-danger">*</span> são obrigatórios.</span>
                  </div>
                  <a href="/tuss/template" class="btn btn-sm btn-soft-primary text-nowrap" :class="{ 'pe-none opacity-50': tussImportProcessing }" :aria-disabled="tussImportProcessing ? 'true' : 'false'">
                    <i class="ri-download-2-line me-1"></i>
                    Baixar modelo CSV
                  </a>
                </div>
                <BRow class="g-3">
                  <BCol md="6">
                    <label class="form-label d-flex align-items-center gap-2">
                      <i class="ri-layout-grid-line text-primary"></i>
                      <span>Tabela suportada <span class="text-danger">*</span></span>
                    </label>
                    <select
                      v-model="tussImportForm.tabela_forcada"
                      required
                      data-choices
                      class="form-select"
                      :class="{ 'is-invalid': !!tussImportForm.errors.tabela_forcada }"
                      :disabled="tussImportProcessing"
                    >
                      <option value="">Selecione</option>
                      <option v-for="t in allowedTabelas" :key="t" :value="t">{{ t }}</option>
                    </select>
                    <div class="invalid-feedback">{{ tussImportForm.errors.tabela_forcada }}</div>
                    <div class="form-text">Obrigatório. Será aplicado em todas as linhas importadas.</div>
                  </BCol>
                  <BCol md="6">
                    <label for="tussCsv" class="form-label d-flex align-items-center gap-2">
                      <i class="ri-file-upload-line text-primary"></i>
                      <span>Arquivo CSV <span class="text-danger">*</span></span>
                    </label>
                    <input
                      id="tussCsv"
                      type="file"
                      accept=".csv,text/csv"
                      class="form-control"
                      :class="{ 'is-invalid': !!tussImportForm.errors.file }"
                      :disabled="tussImportProcessing"
                      @change="onTussFileChange"
                    />
                    <div v-if="tussImportForm.errors.file && tussImportUiStatus !== 'error'" class="invalid-feedback">{{ tussImportForm.errors.file }}</div>
                    <div class="form-text">Aceita .csv (separador ; ou ,). Tamanho máximo: 20MB.</div>
                    <div v-if="tussImportForm.file" class="small text-muted mt-1 d-flex align-items-center justify-content-between gap-2">
                      <div class="text-truncate">
                        Selecionado: <span class="fw-semibold">{{ tussImportForm.file.name }}</span>
                      </div>
                      <div class="text-nowrap">{{ formatBytes(tussImportForm.file.size) }}</div>
                    </div>
                  </BCol>
                </BRow>
                <div v-if="tussImportProgressVisible" class="mt-3 p-3 border rounded" :class="tussImportUiStatus === 'error' ? 'bg-danger-subtle border-danger' : 'bg-light-subtle'">
                  <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                    <div class="small fw-semibold d-flex align-items-center gap-2">
                      <i v-if="tussImportUiStatus === 'error'" class="ri-close-circle-line text-danger"></i>
                      <i v-else class="ri-loader-4-line text-primary"></i>
                      <span>{{ tussImportUiMessage || (tussImportUiStatus === 'error' ? 'Falha ao validar arquivo' : 'Validando arquivo') }}</span>
                      <span v-if="tussImportProcessing" class="tuss-validating-dots" aria-hidden="true">
                        <span>.</span><span>.</span><span>.</span>
                      </span>
                    </div>
                    <div class="text-muted small text-nowrap">{{ tussImportPercent }}%</div>
                  </div>
                  <div class="progress progress-sm">
                    <div
                      class="progress-bar"
                      role="progressbar"
                      :class="tussImportBarClass"
                      :style="{ width: `${tussImportPercent}%` }"
                      :aria-valuenow="tussImportPercent"
                      aria-valuemin="0"
                      aria-valuemax="100"
                    ></div>
                  </div>
                  <div v-if="tussImportUiStatus === 'error' && tussImportUiMessage" class="small text-danger mt-2 mb-0">{{ tussImportUiMessage }}</div>
                </div>
              </Modal>
            </BCardBody>
          </BCard>
        </BTab>
        <BTab>
          <template #title>
            <i class="ri-heart-pulse-line d-block fs-3xl mb-1"></i>Tabela CID
          </template>
          <BCard class="shadow-sm config-card">
            <BCardHeader class="bg-light-subtle p-3 border-0">
              <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                <BCardTitle class="mb-0"><i class="ri-heart-pulse-line text-primary me-2"></i>Tabela CID</BCardTitle>
                <div class="d-flex align-items-center gap-2">
                  <a
                    href="#"
                    class="link-primary fw-semibold text-nowrap link-offset-2 link-underline-opacity-0 link-underline-opacity-100-hover"
                    title="Importar CIDs via arquivo CSV"
                    :class="{ 'pe-none opacity-50': cidImportProcessing }"
                    :aria-disabled="cidImportProcessing ? 'true' : 'false'"
                    @click.prevent="cidImportProcessing ? null : openCidImportModal()"
                  >
                    <i class="ri-upload-2-line me-1"></i>
                    Importar CSV
                  </a>
                </div>
              </div>
            </BCardHeader>
            <BCardBody>
              <p class="text-muted mb-3">Gerencie a tabela de CIDs (Classificação Internacional de Doenças) através da importação de arquivos CSV.</p>
              
              <Modal
                v-model="cidImportModalOpen"
                title="Importar arquivo CSV de CIDs"
                size="lg"
                :name-button="'Importar'"
                :processing="cidImportProcessing"
                :disable-close="cidImportProcessing"
                @save="importCid"
              >
                <div class="d-flex align-items-start justify-content-between gap-2 pb-2 mb-3 border-bottom">
                  <div class="text-muted small d-flex align-items-center gap-2">
                    <i class="ri-information-line"></i>
                    <span>Os campos <span class="text-danger">*</span> são obrigatórios.</span>
                  </div>
                  <a href="/cids/template" class="btn btn-sm btn-soft-primary text-nowrap" :class="{ 'pe-none opacity-50': cidImportProcessing }" :aria-disabled="cidImportProcessing ? 'true' : 'false'">
                    <i class="ri-download-2-line me-1"></i>
                    Baixar modelo CSV
                  </a>
                </div>
                <BRow class="g-3">
                  <BCol md="12">
                    <label for="cidCsv" class="form-label d-flex align-items-center gap-2">
                      <i class="ri-file-upload-line text-primary"></i>
                      <span>Arquivo CSV <span class="text-danger">*</span></span>
                    </label>
                    <input
                      id="cidCsv"
                      type="file"
                      accept=".csv,text/csv"
                      class="form-control"
                      :class="{ 'is-invalid': !!cidImportForm.errors.file }"
                      :disabled="cidImportProcessing"
                      @change="onCidFileChange"
                    />
                    <div v-if="cidImportForm.errors.file && cidImportUiStatus !== 'error'" class="invalid-feedback">{{ cidImportForm.errors.file }}</div>
                    <div class="form-text">Aceita .csv (separador ; ou ,). Colunas necessárias: codigo, descricao.</div>
                    <div v-if="cidImportForm.file" class="small text-muted mt-1 d-flex align-items-center justify-content-between gap-2">
                      <div class="text-truncate">
                        Selecionado: <span class="fw-semibold">{{ cidImportForm.file.name }}</span>
                      </div>
                      <div class="text-nowrap">{{ formatBytes(cidImportForm.file.size) }}</div>
                    </div>
                  </BCol>
                </BRow>
                <div v-if="cidImportProgressVisible" class="mt-3 p-3 border rounded" :class="cidImportUiStatus === 'error' ? 'bg-danger-subtle border-danger' : 'bg-light-subtle'">
                  <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                    <div class="small fw-semibold d-flex align-items-center gap-2">
                      <i v-if="cidImportUiStatus === 'error'" class="ri-close-circle-line text-danger"></i>
                      <i v-else class="ri-loader-4-line text-primary"></i>
                      <span>{{ cidImportUiMessage || (cidImportUiStatus === 'error' ? 'Falha ao validar arquivo' : 'Validando arquivo') }}</span>
                      <span v-if="cidImportProcessing" class="tuss-validating-dots" aria-hidden="true">
                        <span>.</span><span>.</span><span>.</span>
                      </span>
                    </div>
                    <div class="text-muted small text-nowrap">{{ cidImportPercent }}%</div>
                  </div>
                  <div class="progress progress-sm">
                    <div
                      class="progress-bar"
                      role="progressbar"
                      :class="cidImportBarClass"
                      :style="{ width: `${cidImportPercent}%` }"
                      :aria-valuenow="cidImportPercent"
                      aria-valuemin="0"
                      aria-valuemax="100"
                    ></div>
                  </div>
                  <div v-if="cidImportUiStatus === 'error' && cidImportUiMessage" class="small text-danger mt-2 mb-0">{{ cidImportUiMessage }}</div>
                </div>
              </Modal>

              <div class="cid-table-wrapper">
                <TableGrid
                  :key="cidTableKey"
                  :columns="cidColumns"
                  :data="[]"
                  :serverUrl="'/cids/list'"
                  :tableTitle="'Registros CID'"
                  :showStatus="false"
                  :searchPlaceholder="'Buscar por código ou descrição'"
                  :showCheckbox="false"
                  :showAddButton="false"
                  :showActions="false"
                  :actionsConfig="{ delete: false, edit: false, show: false, diary: false, print: false, download: false, restore: false, receive: false }"
                  :compactSpacing="true"
                />
              </div>
            </BCardBody>
          </BCard>
        </BTab>
        <BTab>
          <template #title>
            <i class="ri-file-list-3-line d-block fs-3xl mb-1"></i>Procedimentos
          </template>
          <Procedimento :procedimentos="props.procedimentos" :categoriasProcedimento="props.categoriasProcedimento" />
        </BTab>
        <BTab>
          <template #title>
            <i class="ri-qr-code-line d-block fs-3xl mb-1"></i>PIX
          </template>
          <BCard class="shadow-sm config-card">
            <BCardHeader class="bg-light-subtle p-3 border-0">
              <BCardTitle><i class="ri-qr-code-line text-primary me-2"></i>Configuração de PIX</BCardTitle>
            </BCardHeader>
            <BCardBody>
              <p class="text-muted mb-3">Defina a chave PIX e os dados do recebedor para gerar o QR Code ao receber pagamentos.</p>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Chave PIX</label>
                  <input v-model.trim="pixConfig.chave" type="text" class="form-control" placeholder="e-mail, cpf/cnpj ou chave aleatória" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Descrição</label>
                  <input v-model.trim="pixConfig.descricao" type="text" class="form-control" placeholder="Descrição opcional" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Nome do recebedor</label>
                  <input v-model.trim="pixConfig.recebedor_nome" type="text" class="form-control" placeholder="Nome fantasia" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Cidade do recebedor</label>
                  <input v-model.trim="pixConfig.recebedor_cidade" type="text" class="form-control" placeholder="Cidade" />
                </div>
              </div>
              <div class="mt-3">
                <div class="invalid-feedback d-block" v-if="pixError">{{ pixError }}</div>
                <div class="alert alert-success d-flex align-items-center py-2" role="alert" v-if="pixSuccess">
                  <i class="ri-check-fill text-success me-2"></i>
                  <div>Configurações salvas com sucesso.</div>
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2 mt-2">
                <button class="btn btn-light" type="button" @click="carregarPixConfig" :disabled="pixLoading">
                  <span v-if="pixLoading" class="spinner-border spinner-border-sm me-1"></span>
                  Recarregar
                </button>
                <button class="btn btn-primary" type="button" @click="salvarPixConfig" :disabled="pixSaving">
                  <span v-if="pixSaving" class="spinner-border spinner-border-sm me-1"></span>
                  Salvar
                </button>
              </div>
            </BCardBody>
          </BCard>
        </BTab>
      </BTabs>
    </BRow>
  </Layout>
</template>
<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref, onMounted, watch, computed } from "vue";
import axios from "axios";
import Modal from "@/Components/Modal.vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";
import Parametrizacao from "./Parametrizacao.vue";
import Especialidade from "./Especialidade.vue";
import Procedimento from "./Procedimento.vue";
const props = defineProps({
  estadosCivis: { type: Array, default: () => [] },
  tiposSanguineos: { type: Array, default: () => [] },
  canaisAviso: { type: Array, default: () => [] },
  parentescos: { type: Array, default: () => [] },
  especialidades: { type: Array, default: () => [] },
  procedimentos: { type: Array, default: () => [] },
  categoriasProcedimento: { type: Array, default: () => [] },
});

const pixConfig = ref({ chave: "", recebedor_nome: "", recebedor_cidade: "", descricao: "" });
const pixLoading = ref(false);
const pixSaving = ref(false);
const pixError = ref("");
const pixSuccess = ref(false);

const tussTableKey = ref(0);

const tussImportForm = useForm({
  file: null,
  tabela_forcada: '',
});
const tussImportModalOpen = ref(false);
const tussCreateModalOpen = ref(false);
const tussEditModalOpen = ref(false);
const tussImportUiStatus = ref('idle');
const tussImportUiMessage = ref('');
const tussImportLastPercent = ref(0);
const tussImportProcessing = ref(false);

const cidImportForm = useForm({
  file: null,
});
const cidImportModalOpen = ref(false);
const cidImportUiStatus = ref('idle');
const cidImportUiMessage = ref('');
const cidImportLastPercent = ref(0);
const cidImportProcessing = ref(false);
const cidTableKey = ref(0);

const cidColumns = [
  { id: 'codigo', name: 'Código', sortable: true },
  { id: 'descricao', name: 'Descrição', sortable: true },
];

const tussImportPercent = computed(() => {
  return Math.min(Math.max(tussImportLastPercent.value || 0, 0), 100);
});
const tussImportProgressVisible = computed(() => {
  return tussImportProcessing.value || tussImportUiStatus.value === 'error';
});
const tussImportBarClass = computed(() => {
  if (tussImportUiStatus.value === 'error') return 'bg-danger';
  if (tussImportProcessing.value) return 'bg-primary progress-bar-striped progress-bar-animated';
  return 'bg-primary';
});

function formatCurrencyBR(v) {
  const n = Number(v);
  if (!Number.isFinite(n)) return v ?? '';
  return n.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}
function formatBytes(bytes) {
  const n = Number(bytes);
  if (!Number.isFinite(n) || n <= 0) return '0 B';
  const units = ['B', 'KB', 'MB', 'GB'];
  const idx = Math.min(Math.floor(Math.log(n) / Math.log(1024)), units.length - 1);
  const value = n / Math.pow(1024, idx);
  const digits = idx === 0 ? 0 : 1;
  return `${value.toFixed(digits)} ${units[idx]}`;
}
function parseDecimalBR(v) {
  const s = String(v ?? '').trim();
  if (!s) return null;
  const cleaned = s.replace(/[^\d,.\-]/g, '');
  if (!cleaned) return null;
  const normalized = cleaned.includes(',') && cleaned.includes('.')
    ? cleaned.replace(/\./g, '').replace(',', '.')
    : cleaned.replace(',', '.');
  const n = Number(normalized);
  return Number.isFinite(n) ? n : null;
}
const tussColumns = [
  { id: "id", name: "ID" },
  { id: "tabela", name: "Tabela" },
  { id: "codigo", name: "Código" },
  { id: "descricao", name: "Descrição" },
  { id: "eh_tratamento", name: "Tratamento?", formatter: (cell) => (cell === true || cell === 1 || cell === '1' ? 'Sim' : 'Não') },
  { id: "quantidade_sessoes", name: "Qtd. Sessões" },
  { id: "ch", name: "CH", formatter: (cell) => formatCurrencyBR(cell) },
  { id: "co", name: "CO", formatter: (cell) => formatCurrencyBR(cell) },
  { id: "total", name: "Total", formatter: (cell) => formatCurrencyBR(cell) },
];
const allowedTabelas = ['AMB1990', 'AMB1992', 'AMB1993', 'AMB1999', 'CBHPM3', 'CBHPM4', 'CBHPM5', 'TUSS'];
const tussTabelaFilter = ref('');
function setTussTabelaFilter(v) {
  tussTabelaFilter.value = String(v || '');
}
const tussTableReloadNonce = ref(0);
const tussServerQuery = computed(() => {
  const f = String(tussTabelaFilter.value || '').trim();
  const base = f ? { tabela: f } : {};
  return { ...base, _r: tussTableReloadNonce.value };
});
const tussCreateForm = useForm({
  tabela: '',
  codigo: '',
  descricao: '',
  m2_filme: '',
  auxiliares: '',
  incidencia: '',
  porte: '',
  ch: '',
  co: '',
  eh_tratamento: false,
  quantidade_sessoes: null,
});
const tussCreateTotal = computed(() => {
  const ch = parseDecimalBR(tussCreateForm.ch);
  const co = parseDecimalBR(tussCreateForm.co);
  if (ch === null || co === null) return null;
  return ch + co;
});
const tussCreateTotalDisplay = computed(() => {
  const n = tussCreateTotal.value;
  if (n === null) return '';
  return n.toLocaleString("pt-BR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
});
const isTussCreateTratamento = computed(() => tussCreateForm.eh_tratamento === true || tussCreateForm.eh_tratamento === 1 || tussCreateForm.eh_tratamento === '1');
const tussEditForm = useForm({
  tabela: '',
  codigo: '',
  descricao: '',
  m2_filme: '',
  auxiliares: '',
  incidencia: '',
  porte: '',
  ch: '',
  co: '',
  eh_tratamento: false,
  quantidade_sessoes: null,
});
const isTussEditTratamento = computed(() => tussEditForm.eh_tratamento === true || tussEditForm.eh_tratamento === 1 || tussEditForm.eh_tratamento === '1');
const tussEditTotal = computed(() => {
  const ch = parseDecimalBR(tussEditForm.ch);
  const co = parseDecimalBR(tussEditForm.co);
  if (ch === null || co === null) return null;
  return ch + co;
});
const tussEditTotalDisplay = computed(() => {
  const n = tussEditTotal.value;
  if (n === null) return '';
  return n.toLocaleString("pt-BR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
});
function resetTussCreate() {
  tussCreateForm.reset();
  tussCreateForm.clearErrors();
}
function resetTussEdit() {
  tussEditForm.reset();
  tussEditForm.clearErrors();
}
function openTussCreateModal() {
  tussCreateModalOpen.value = true;
}
function openTussEditModal(id, row) {
  const r = row && typeof row === 'object' ? row : {};
  tussEditForm.clearErrors();
  tussEditForm.tabela = String(r?.tabela ?? '').trim();
  tussEditForm.codigo = String(r?.codigo ?? '').trim();
  tussEditForm.descricao = String(r?.descricao ?? '').trim();
  tussEditForm.m2_filme = String(r?.m2_filme ?? '').trim();
  tussEditForm.auxiliares = String(r?.auxiliares ?? '').trim();
  tussEditForm.incidencia = String(r?.incidencia ?? '').trim();
  tussEditForm.porte = String(r?.porte ?? '').trim();
  tussEditForm.ch = (r?.ch ?? '') === null ? '' : String(r?.ch ?? '').trim();
  tussEditForm.co = (r?.co ?? '') === null ? '' : String(r?.co ?? '').trim();
  tussEditForm.eh_tratamento = (r?.eh_tratamento === true || r?.eh_tratamento === 1 || r?.eh_tratamento === '1');
  tussEditForm.quantidade_sessoes = tussEditForm.eh_tratamento ? (r?.quantidade_sessoes ?? null) : null;
  tussEditModalOpen.value = true;
}
function storeTuss() {
  tussCreateForm.clearErrors();
  if (!String(tussCreateForm.tabela || '').trim()) {
    tussCreateForm.setError('tabela', 'Informe a tabela.');
    return;
  }
  if (!String(tussCreateForm.codigo || '').trim()) {
    tussCreateForm.setError('codigo', 'Informe o código.');
    return;
  }
  if (!(tussCreateForm.eh_tratamento === true || tussCreateForm.eh_tratamento === 1 || tussCreateForm.eh_tratamento === '1')) {
    tussCreateForm.quantidade_sessoes = null;
  }
  tussCreateForm.post('/tuss', {
    preserveScroll: true,
    onSuccess: () => {
      tussCreateForm.reset();
      tussCreateModalOpen.value = false;
      tussTableReloadNonce.value += 1;
    },
  });
}
function updateTuss() {
  tussEditForm.clearErrors();
  if (!String(tussEditForm.tabela || '').trim()) {
    tussEditForm.setError('tabela', 'Informe a tabela.');
    return;
  }
  if (!String(tussEditForm.codigo || '').trim()) {
    tussEditForm.setError('codigo', 'Informe o código.');
    return;
  }
  if (!(tussEditForm.eh_tratamento === true || tussEditForm.eh_tratamento === 1 || tussEditForm.eh_tratamento === '1')) {
    tussEditForm.quantidade_sessoes = null;
  }
  tussEditForm.post('/tuss', {
    preserveScroll: true,
    onSuccess: () => {
      tussEditModalOpen.value = false;
      tussTableReloadNonce.value += 1;
    },
  });
}
watch(tussCreateModalOpen, (v, old) => {
  if (!v && old) resetTussCreate();
});
watch(tussEditModalOpen, (v, old) => {
  if (!v && old) resetTussEdit();
});

function onTussFileChange(e) {
  const f = e?.target?.files?.[0] || null;
  tussImportForm.file = f;
  tussImportForm.clearErrors();
}
function resetTussImportForm() {
  tussImportForm.reset('file', 'tabela_forcada');
  tussImportForm.clearErrors();
  tussImportUiStatus.value = 'idle';
  tussImportUiMessage.value = '';
  tussImportLastPercent.value = 0;
  tussImportProcessing.value = false;
  const el = document.getElementById('tussCsv');
  if (el) el.value = '';
}
function openTussImportModal() {
  tussImportModalOpen.value = true;
}
function closeTussImportModal() {
  tussImportModalOpen.value = false;
  resetTussImportForm();
}
async function importTuss() {
  if (!String(tussImportForm.tabela_forcada || '').trim()) {
    tussImportForm.setError('tabela_forcada', 'Selecione a tabela suportada.');
    return;
  }
  if (!tussImportForm.file) {
    tussImportForm.setError('file', 'Selecione um arquivo CSV.');
    return;
  }
  tussImportForm.clearErrors();
  tussImportUiStatus.value = 'running';
  tussImportUiMessage.value = '';
  tussImportLastPercent.value = 1;
  tussImportProcessing.value = true;
  try {
    const fd = new FormData();
    fd.append('file', tussImportForm.file);
    fd.append('tabela_forcada', tussImportForm.tabela_forcada);

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const resp = await fetch('/tuss/import/progress', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      headers: csrf
        ? { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/x-ndjson, application/json' }
        : { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/x-ndjson, application/json' },
    });

    if (resp.redirected || /\/login\b/i.test(resp.url || '')) {
      tussImportProcessing.value = false;
      tussImportUiStatus.value = 'error';
      tussImportUiMessage.value = 'Sua sessão expirou. Recarregue a página e tente novamente.';
      return;
    }

    if (!resp.ok) {
      let msg = 'Falha ao iniciar importação.';
      if (resp.status === 419) msg = 'Sua sessão expirou. Recarregue a página e tente novamente.';
      try {
        const data = await resp.json();
        msg = data?.message || data?.errors?.file?.[0] || data?.errors?.tabela_forcada?.[0] || msg;
      } catch (e) {
      }
      tussImportProcessing.value = false;
      tussImportUiStatus.value = 'error';
      tussImportUiMessage.value = String(msg);
      return;
    }

    const ct = resp.headers.get('content-type') || '';
    if (!/application\/x-ndjson/i.test(ct)) {
      let msg = 'Falha ao iniciar importação.';
      try {
        const data = await resp.json();
        msg = data?.message || data?.errors?.file?.[0] || data?.errors?.tabela_forcada?.[0] || msg;
      } catch (e) {
        try {
          const t = await resp.text();
          if (t && t.length < 500) msg = t;
        } catch (e2) {
        }
      }
      tussImportProcessing.value = false;
      tussImportUiStatus.value = 'error';
      tussImportUiMessage.value = String(msg);
      return;
    }

    const reader = resp.body?.getReader();
    if (!reader) {
      tussImportProcessing.value = false;
      tussImportUiStatus.value = 'error';
      tussImportUiMessage.value = 'Não foi possível ler o progresso.';
      return;
    }

    const decoder = new TextDecoder('utf-8');
    let buf = '';
    let resultId = '';
    let finished = false;
    while (true) {
      const { value, done } = await reader.read();
      if (done) break;
      buf += decoder.decode(value, { stream: true });
      const lines = buf.split('\n');
      buf = lines.pop() || '';
      for (const line of lines) {
        const trimmed = line.trim();
        if (!trimmed) continue;
        let data;
        try {
          data = JSON.parse(trimmed);
        } catch (e) {
          continue;
        }
        if (!resultId) resultId = String(data?.id || '');
        const percent = Number(data?.percent);
        const status = String(data?.status || '');
        const message = String(data?.message || '');
        if (Number.isFinite(percent)) tussImportLastPercent.value = Math.min(Math.max(percent, 0), 100);
        if (message) tussImportUiMessage.value = message;
        if (status === 'error') {
          tussImportProcessing.value = false;
          tussImportUiStatus.value = 'error';
          finished = true;
          return;
        }
        if (status === 'success') {
          tussImportProcessing.value = false;
          finished = true;
          closeTussImportModal();
          if (resultId) {
            router.visit(`/tuss/import/complete/${resultId}`, {
              method: 'get',
              preserveScroll: true,
              preserveState: true,
              replace: true,
              onFinish: () => {
                tussTableReloadNonce.value += 1;
              },
            });
          } else {
            tussTableReloadNonce.value += 1;
          }
          return;
        }
        try { data = JSON.parse(trimmed); } catch (e) { continue; }
        if (!data || typeof data !== 'object') continue;
        resultId = data.id || resultId;
        const p = Number(data.percent);
        if (!isNaN(p)) tussImportLastPercent.value = p;
        if (data.status) tussImportUiStatus.value = data.status;
        if (data.message) tussImportUiMessage.value = String(data.message);
        if (data.status === 'success' || data.status === 'error') {
          finished = true;
        }
      }
      if (finished) break;
    }
    tussImportProcessing.value = false;
    if (tussImportUiStatus.value === 'success') {
      tussTableKey.value++;
      setTimeout(() => {
        tussImportModalOpen.value = false;
        tussImportForm.reset();
        tussImportForm.clearErrors();
      }, 2000);
    }
  } catch (err) {
    tussImportProcessing.value = false;
    tussImportUiStatus.value = 'error';
    tussImportUiMessage.value = 'Falha ao processar arquivo.';
  }
}

// ======================= CID IMPORT =======================
const cidImportPercent = computed(() => {
  if (cidImportUiStatus.value === 'idle') return 0;
  if (cidImportUiStatus.value === 'error') return 100;
  return Math.min(100, Math.max(0, cidImportLastPercent.value || 0));
});

const cidImportProgressVisible = computed(() => {
  return cidImportUiStatus.value !== 'idle' || cidImportProcessing.value;
});

const cidImportBarClass = computed(() => {
  if (cidImportUiStatus.value === 'error') return 'bg-danger';
  if (cidImportUiStatus.value === 'success') return 'bg-success';
  return 'progress-bar-striped progress-bar-animated bg-primary';
});

function openCidImportModal() {
  cidImportForm.reset();
  cidImportForm.clearErrors();
  cidImportUiStatus.value = 'idle';
  cidImportUiMessage.value = '';
  cidImportLastPercent.value = 0;
  cidImportProcessing.value = false;
  cidImportModalOpen.value = true;
}

function onCidFileChange(e) {
  const f = e.target.files[0];
  cidImportForm.file = f || null;
  cidImportForm.clearErrors('file');
  cidImportUiStatus.value = 'idle';
  cidImportUiMessage.value = '';
  cidImportLastPercent.value = 0;
}

async function importCid() {
  if (!cidImportForm.file) {
    cidImportForm.setError('file', 'O arquivo é obrigatório.');
    return;
  }
  
  cidImportUiStatus.value = 'running';
  cidImportUiMessage.value = '';
  cidImportLastPercent.value = 1;
  cidImportProcessing.value = true;
  try {
    const fd = new FormData();
    fd.append('file', cidImportForm.file);

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const resp = await fetch('/cids/import/progress', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      headers: csrf
        ? { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/x-ndjson, application/json' }
        : { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/x-ndjson, application/json' },
    });

    if (resp.redirected || /\/login\b/i.test(resp.url || '')) {
      cidImportProcessing.value = false;
      cidImportUiStatus.value = 'error';
      cidImportUiMessage.value = 'Sua sessão expirou. Recarregue a página e tente novamente.';
      return;
    }

    if (!resp.ok) {
      let msg = 'Falha ao iniciar importação.';
      if (resp.status === 419) msg = 'Sua sessão expirou. Recarregue a página e tente novamente.';
      try {
        const data = await resp.json();
        msg = data?.message || data?.errors?.file?.[0] || msg;
      } catch (e) {
      }
      cidImportProcessing.value = false;
      cidImportUiStatus.value = 'error';
      cidImportUiMessage.value = String(msg);
      return;
    }

    const ct = resp.headers.get('content-type') || '';
    if (!/application\/x-ndjson/i.test(ct)) {
      let msg = 'Falha ao iniciar importação.';
      try {
        const data = await resp.json();
        msg = data?.message || data?.errors?.file?.[0] || msg;
      } catch (e) {
        try {
          const t = await resp.text();
          if (t && t.length < 500) msg = t;
        } catch (e2) {
        }
      }
      cidImportProcessing.value = false;
      cidImportUiStatus.value = 'error';
      cidImportUiMessage.value = String(msg);
      return;
    }

    const reader = resp.body?.getReader();
    if (!reader) {
      cidImportProcessing.value = false;
      cidImportUiStatus.value = 'error';
      cidImportUiMessage.value = 'Não foi possível ler o progresso.';
      return;
    }

    const decoder = new TextDecoder('utf-8');
    let buf = '';
    let resultId = '';
    let finished = false;
    while (true) {
      const { value, done } = await reader.read();
      if (done) break;
      buf += decoder.decode(value, { stream: true });
      const lines = buf.split('\n');
      buf = lines.pop() || '';
      for (const line of lines) {
        const trimmed = line.trim();
        if (!trimmed) continue;
        let data;
        try { data = JSON.parse(trimmed); } catch (e) { continue; }
        if (!data || typeof data !== 'object') continue;
        resultId = data.id || resultId;
        const p = Number(data.percent);
        if (!isNaN(p)) cidImportLastPercent.value = p;
        if (data.status) cidImportUiStatus.value = data.status;
        if (data.message) cidImportUiMessage.value = String(data.message);
        if (data.status === 'success' || data.status === 'error') {
          finished = true;
        }
      }
      if (finished) break;
    }
    cidImportProcessing.value = false;
    if (cidImportUiStatus.value === 'success') {
      cidTableKey.value++;
      setTimeout(() => {
        cidImportModalOpen.value = false;
        cidImportForm.reset();
        cidImportForm.clearErrors();
      }, 2000);
    }
  } catch (err) {
    cidImportProcessing.value = false;
    cidImportUiStatus.value = 'error';
    cidImportUiMessage.value = 'Falha ao processar arquivo.';
  }
}
watch(tussImportModalOpen, (v, old) => {
  if (!v && old) resetTussImportForm();
});

async function carregarPixConfig() {
  pixLoading.value = true;
  pixError.value = "";
  pixSuccess.value = false;
  try {
    const resp = await axios.get('/config/pix');
    pixConfig.value = {
      chave: resp.data?.chave || "",
      recebedor_nome: resp.data?.recebedor_nome || "",
      recebedor_cidade: resp.data?.recebedor_cidade || "",
      descricao: resp.data?.descricao || "",
    };
  } catch (e) {
    pixError.value = "Não foi possível carregar a configuração.";
  } finally {
    pixLoading.value = false;
  }
}
async function salvarPixConfig() {
  pixError.value = "";
  pixSuccess.value = false;
  if (!String(pixConfig.value.chave || "").trim()) {
    pixError.value = "Informe a chave PIX.";
    return;
  }
  pixSaving.value = true;
  try {
    await axios.put('/config/pix', pixConfig.value);
    pixSuccess.value = true;
  } catch (e) {
    pixError.value = "Falha ao salvar configuração.";
  } finally {
    pixSaving.value = false;
  }
}
onMounted(() => { carregarPixConfig(); });
</script>
<style scoped>
.config-nav .nav-link {
  border-radius: 12px;
  padding: 14px 10px;
  color: var(--vz-body-color);
}
.config-nav .nav-link.active {
  background: var(--vz-primary-bg-subtle);
  color: var(--vz-primary);
}
:deep(.config-card) {
  border: 1px dashed var(--vz-border-color);
  background: var(--vz-light-bg-subtle);
}
:deep(.config-nav-wrapper) {
  background: var(--vz-secondary-bg);
  border: 1px solid var(--vz-border-color);
  border-radius: 12px 0 0 12px;
  padding: 12px;
}
:global([data-bs-theme="light"] .config-nav-wrapper) {
  border-right: 0 !important;
}
:global([data-bs-theme="dark"] .config-nav-wrapper) {
  background: var(--vz-secondary-bg) !important;
  border-color: var(--vz-secondary-bg) !important;
}
:global([data-bs-theme="dark"] .config-card) {
  background: var(--vz-secondary-bg) !important;
  border-color: var(--vz-secondary-bg) !important;
}
:global([data-bs-theme="light"] .config-card) {
  background: #fff !important;
  border-color: #fff !important;
  border-left: 0 !important;
}
:deep(.tuss-filter-menu .dropdown-item:not(.active)) {
  color: var(--vz-body-color) !important;
}
:deep(.tuss-filter-menu .dropdown-item.active),
:deep(.tuss-filter-menu .dropdown-item:active) {
  color: var(--vz-body-color) !important;
  background-color: var(--vz-primary-bg-subtle) !important;
}
:deep(.tuss-filter-toggle) {
  color: var(--vz-body-color) !important;
}
.tuss-validating-dots span {
  display: inline-block;
  opacity: 0.25;
  animation: tussDotPulse 1.1s infinite;
}
.tuss-validating-dots span:nth-child(2) {
  animation-delay: 0.2s;
}
.tuss-validating-dots span:nth-child(3) {
  animation-delay: 0.4s;
}
@keyframes tussDotPulse {
  0% { opacity: 0.25; transform: translateY(0); }
  50% { opacity: 1; transform: translateY(-1px); }
  100% { opacity: 0.25; transform: translateY(0); }
}

/* Tabela CID: coluna Código compacta */
.cid-table-wrapper :deep(table) {
  table-layout: fixed !important;
  width: 100% !important;
}
.cid-table-wrapper :deep(th:first-child),
.cid-table-wrapper :deep(td:first-child) {
  width: 110px !important;
  min-width: 110px !important;
  max-width: 110px !important;
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
}
</style>
