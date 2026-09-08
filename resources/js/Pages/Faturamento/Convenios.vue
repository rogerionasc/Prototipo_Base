<template>
    <Layout>

        <Head title="Lotes de Faturamento" />
        <PageHeader title="Lotes de Faturamento" pageTitle="Faturamento" />

        <div class="row">
            <div class="col-lg-9">
                <!-- Filtros -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body py-3">
                        <div class="row g-3 align-items-end">
                            <!-- Buscar -->
                            <div class="col-12 col-md">
                                <label
                                    class="form-label text-muted fs-11 text-uppercase fw-semibold mb-1">Buscar</label>
                                <div class="search-box">
                                    <input type="text" class="form-control search" placeholder="Buscar..."
                                        v-model="filtros.busca">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>

                            <!-- Convênio -->
                            <div class="col-12 col-md">
                                <label
                                    class="form-label text-muted fs-11 text-uppercase fw-semibold mb-1">Convênio</label>
                                <select v-model="filtros.convenio" class="form-select form-select-sm" data-choices
                                    data-choices-search-true>
                                    <option value="">Todos</option>
                                    <option v-for="c in conveniosOptions" :key="c.value" :value="c.label">{{ c.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="col-12 col-md">
                                <label
                                    class="form-label text-muted fs-11 text-uppercase fw-semibold mb-1">Status</label>
                                <select v-model="filtros.status" class="form-select form-select-sm" data-choices
                                    data-choices-search-false>
                                    <option value="">Todos</option>
                                    <option value="ABERTA">ABERTA</option>
                                    <option value="FECHADA">FECHADA</option>
                                    <option value="COM_GLOSA">COM GLOSA</option>
                                </select>
                            </div>

                            <!-- Data Início -->
                            <div class="col-6 col-md">
                                <label
                                    class="form-label text-muted fs-11 text-uppercase fw-semibold mb-1">Início</label>
                                <flatPickr v-model="filtros.dataInicio" class="form-control" :config="flatpickrOptions"
                                    placeholder="Selecione..." />
                            </div>

                            <!-- Data Fim -->
                            <div class="col-6 col-md">
                                <label class="form-label text-muted fs-11 text-uppercase fw-semibold mb-1">Fim</label>
                                <flatPickr v-model="filtros.dataFim" class="form-control" :config="flatpickrOptions"
                                    placeholder="Selecione..." />
                            </div>

                            <!-- Botão Criar Lote -->
                            <div class="col-12 col-md-auto text-end d-flex align-items-end">
                                <button class="btn btn-primary" @click="openCriarLoteModal">
                                    <i class="ri-add-line align-bottom me-1"></i> Criar Lote
                                </button>
                            </div>
                        </div>

                        <!-- Limpar filtros -->
                        <div class="mt-2 text-end"
                            v-if="filtros.busca || filtros.convenio || filtros.status || filtros.dataInicio || filtros.dataFim">
                            <button class="btn btn-sm btn-ghost-danger shadow-none" @click="limparFiltros">
                                <i class="ri-close-line align-bottom me-1"></i> Limpar Filtros
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0" v-if="faturamentosFiltrados.length === 0">
                    <div class="card-body text-center py-5">
                        <div class="avatar-md mx-auto mb-3">
                            <div class="avatar-title bg-light text-primary rounded-circle fs-24">
                                <i class="ri-file-list-3-line"></i>
                            </div>
                        </div>
                        <h5 class="mt-2">Nenhum lote de faturamento encontrado</h5>
                        <p class="text-muted">Clique em "Criar Lote" para começar.</p>
                    </div>
                </div>

                <div class="card shadow-sm border-0" v-if="faturamentosFiltrados.length > 0">
                    <div class="card-header align-items-center d-flex border-bottom-dashed">
                        <h4 class="card-title mb-0 flex-grow-1 fs-14 fw-semibold text-uppercase text-muted">
                            Lotes de Faturamento
                        </h4>
                    </div>
                    <div class="card-body bg-light-subtle p-3 p-md-4">


                        <div class="card ribbon-box border shadow-none mb-3" v-for="lote in faturamentosFiltrados"
                            :key="lote.id">
                            <div class="card-body pb-3 position-relative">
                                <div class="ribbon ribbon-primary ribbon-shape" style="z-index: 10;">Lote {{
                                    lote.numero_lote || lote.id }}</div>

                                <!-- Status + Menu no canto superior direito (Desktop) -->
                                <div class="position-absolute top-0 end-0 p-2 d-none d-md-flex align-items-center gap-2"
                                    style="z-index: 5;">
                                    <span class="badge px-3 py-1 fs-12 shadow-sm rounded-pill"
                                        :class="getLoteStatusClass(lote.status)">
                                        {{ lote.status.replace('_', ' ') }}
                                    </span>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-ghost-secondary rounded-circle shadow-none p-0"
                                            style="width: 28px; height: 28px; line-height: 28px;" type="button"
                                            :id="'dropdownLote' + lote.id" data-bs-toggle="dropdown"
                                            aria-expanded="false" @click.stop>
                                            <i class="ri-more-2-fill fs-16"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end"
                                            :aria-labelledby="'dropdownLote' + lote.id">
                                            <li>
                                                <a class="dropdown-item" href="#"
                                                    @click.prevent.stop="baixarXml(lote.id)">
                                                    <i class="ri-file-code-line align-bottom me-2 text-muted"></i>
                                                    Baixar XML
                                                </a>
                                            </li>
                                            <li v-if="lote.status === 'FECHADA'">
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li v-if="lote.status === 'FECHADA'">
                                                <a class="dropdown-item fw-medium text-primary" href="#"
                                                    @click.prevent.stop="askProcessarLote(lote.id)">
                                                    <i class="ri-settings-4-line align-bottom me-2"></i> Processar Lote
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Status + Menu visível em mobile -->
                                <div
                                    class="d-md-none text-end mt-2 mb-2 me-2 d-flex justify-content-end align-items-center gap-2">
                                    <span class="badge px-3 py-1 fs-12 shadow-sm rounded-pill"
                                        :class="getLoteStatusClass(lote.status)">
                                        {{ lote.status.replace('_', ' ') }}
                                    </span>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-ghost-secondary rounded-circle shadow-none p-0"
                                            style="width: 28px; height: 28px; line-height: 28px;" type="button"
                                            :id="'dropdownLoteMob' + lote.id" data-bs-toggle="dropdown"
                                            aria-expanded="false" @click.stop>
                                            <i class="ri-more-2-fill fs-16"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end"
                                            :aria-labelledby="'dropdownLoteMob' + lote.id">
                                            <li>
                                                <a class="dropdown-item" href="#"
                                                    @click.prevent.stop="baixarXml(lote.id)">
                                                    <i class="ri-file-code-line align-bottom me-2 text-muted"></i>
                                                    Baixar XML
                                                </a>
                                            </li>
                                            <li v-if="lote.status === 'FECHADA'">
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li v-if="lote.status === 'FECHADA'">
                                                <a class="dropdown-item fw-medium text-primary" href="#"
                                                    @click.prevent.stop="askProcessarLote(lote.id)">
                                                    <i class="ri-settings-4-line align-bottom me-2"></i> Processar Lote
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="row align-items-center mt-3 mt-md-0" style="cursor: pointer;"
                                    @click="toggleCollapse(lote.id)">

                                    <!-- Convênio Icone e Nome -->
                                    <div
                                        class="col-md-3 col-12 mb-3 mb-md-0 border-end-md pe-md-4 text-center text-md-start">
                                        <div class="d-flex flex-column flex-md-row align-items-center">
                                            <div class="flex-shrink-0 mb-2 mb-md-0 me-md-3">
                                                <img v-if="lote.convenio_logo" :src="`/storage/${lote.convenio_logo}`"
                                                    alt="Logo" class="rounded bg-light p-1"
                                                    style="object-fit: contain; width: 70px; height: 70px;" />
                                                <div v-else
                                                    class="rounded bg-primary-subtle text-primary d-flex justify-content-center align-items-center"
                                                    style="width: 70px; height: 70px;">
                                                    <i class="ri-hospital-line" style="font-size: 40px;"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="fs-15 fw-semibold mb-1 text-primary">{{ lote.convenio }}</h5>
                                                <span class="text-muted fs-12"><i
                                                        class="ri-calendar-event-line align-middle me-1"></i>
                                                    {{ lote.data_faturamento || '-' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Barra de Progresso das Guias -->
                                    <div class="col-md-4 col-12 mb-3 mb-md-0 px-md-4">
                                        <div class="d-flex justify-content-between align-items-end mb-2">
                                            <span class="text-uppercase fw-semibold fs-11 text-muted">Progresso das
                                                Guias</span>
                                            <span class="badge bg-info-subtle text-info fw-semibold px-2 py-1 fs-11">
                                                <i class="ri-file-list-3-line align-middle me-1"></i> {{
                                                    lote.total_guias }} guias no
                                                lote
                                            </span>
                                        </div>

                                        <div v-if="lote.guias_timeline && lote.guias_timeline.length > 0">
                                            <div class="progress animated-progress"
                                                style="height: 8px; border-radius: 4px; background-color: #e9ebec;">
                                                <div v-for="(t, index) in lote.guias_timeline" :key="index"
                                                    class="progress-bar" :class="getProgressBarClass(t.status)"
                                                    role="progressbar" :style="{ width: t.percentage + '%' }"
                                                    :title="t.status + ' (' + t.count + ')'">
                                                </div>
                                            </div>

                                            <!-- Legendas -->
                                            <div class="d-flex flex-wrap mt-2 gap-2 justify-content-start">
                                                <div v-for="(t, index) in lote.guias_timeline" :key="index"
                                                    class="d-flex align-items-center fs-11 fw-medium"
                                                    :class="getProgressTextClass(t.status)">
                                                    <i class="ri-checkbox-blank-circle-fill me-1"
                                                        style="font-size: 8px;"></i>
                                                    {{ t.percentage }}% {{ t.status }} ({{ t.count }})
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="text-muted fs-12 d-flex align-items-center mt-2">
                                            <i class="ri-information-line me-1"></i> Este lote não possui guias
                                            associadas.
                                        </div>
                                    </div>

                                    <!-- Status e Total do Lote -->
                                    <div class="col-md-4 col-12 text-center border-start-md ps-md-4">
                                        <div class="row g-2 mb-2">
                                            <div class="col-4">
                                                <p class="text-muted text-uppercase fw-semibold fs-10 mb-1">Total</p>
                                                <h6 class="text-primary fw-bold mb-0">{{
                                                    formatCurrency(lote.valor_total) }}</h6>
                                            </div>
                                            <div class="col-4">
                                                <p class="text-muted text-uppercase fw-semibold fs-10 mb-1">Glosado</p>
                                                <h6 class="text-danger fw-bold mb-0">{{
                                                    formatCurrency(calcularValorGlosado(lote)) }}
                                                </h6>
                                            </div>
                                            <div class="col-4">
                                                <p class="text-muted text-uppercase fw-semibold fs-10 mb-1">Aprovado</p>
                                                <h6 class="text-success fw-bold mb-0">{{
                                                    formatCurrency(calcularValorAprovado(lote)) }}</h6>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-1 col-12 text-center text-md-end mt-3 mt-md-0">
                                        <button class="btn btn-sm btn-ghost-secondary rounded-circle shadow-none">
                                            <i class="ri-arrow-down-s-line fs-20"
                                                :class="{ 'ri-arrow-up-s-line': isExpanded(lote.id) }"></i>
                                        </button>
                                    </div>

                                </div>

                                <!-- Tabela Colapsável -->
                                <div v-show="isExpanded(lote.id)" class="mt-4 ms-md-4 border-top pt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fs-13 fw-semibold text-muted text-uppercase mb-0">Guias Atreladas ao
                                            Lote</h6>
                                        <div class="d-flex gap-2">
                                            <button v-if="lote.status === 'ABERTA'" class="btn btn-sm btn-soft-success"
                                                @click.stop="abrirAddModal(lote)">
                                                <i class="ri-add-line align-bottom me-1"></i> Adicionar Guias
                                            </button>
                                            <button v-if="lote.status === 'ABERTA'" class="btn btn-sm btn-soft-warning"
                                                @click.stop="askFecharLote(lote.id)"
                                                :disabled="fechandoLote === lote.id">
                                                <i class="ri-lock-line align-bottom me-1"></i> Fechar Lote
                                            </button>
                                            <button v-else-if="lote.status === 'FECHADA'"
                                                class="btn btn-sm btn-soft-info" @click.stop="askReabrirLote(lote.id)"
                                                :disabled="fechandoLote === lote.id">
                                                <i class="ri-lock-unlock-line align-bottom me-1"></i> Reabrir Lote
                                            </button>
                                        </div>
                                    </div>

                                    <div class="table-wrapper-choices" :key="'table-' + lote.id + '-' + lote.status">
                                        <SimpleTable :items="lote.guias || []" :columns="guiasLoteColumns"
                                            :hasActions="['ABERTA', 'FECHADA'].includes(lote.status)" actionsLabel="Ação" variant="borderless" :compact="true"
                                            emptyTitle="Nenhuma guia atrelada a este lote."
                                            :row-class="getGuiaRowClass">
                                            <!-- <template #cell(id)="{ item }">
                                                <span class="fw-medium text-primary">#{{ item.id }}</span>
                                            </template> -->
                                            <template #cell(numero)="{ item }">
                                                <a v-if="item.agendamento_id"
                                                    :href="route('guias.imprimirDaAgenda', item.agendamento_id) + '?guia_id=' + item.id"
                                                    target="_blank"
                                                    class="text-primary fw-medium text-decoration-underline">
                                                    <template
                                                        v-if="item.numero_guia_prestador || item.numero_guia_operadora">
                                                        {{ item.numero_guia_prestador || item.numero_guia_operadora }}
                                                    </template>
                                                    <template v-else>Ver Guia</template>
                                                </a>
                                                <span v-else class="text-primary fw-medium">{{
                                                    item.numero_guia_prestador ||
                                                    item.numero_guia_operadora || '-' }}</span>
                                            </template>
                                            <template #cell(tipo)="{ item }">
                                                {{ item.tipo || 'Guia de Consulta' }}
                                            </template>
                                            <template #cell(senha)="{ item }">
                                                {{ item.senha || '-' }}
                                            </template>
                                            <template #cell(valor_total)="{ item }">
                                                {{ formatCurrency(item.valor_total) }}
                                            </template>
                                            <template #cell(glosa)="{ item }">
                                                <template
                                                    v-if="['ABERTA', 'PROCESSADA', 'RECEBIDO'].includes(lote.status)">
                                                    <span v-if="item.valor_glosado > 0" class="text-danger fw-medium">{{
                                                        formatCurrency(item.valor_glosado) }}</span>
                                                    <span v-else class="text-muted">-</span>
                                                </template>
                                                <template v-else>
                                                    <div v-if="item.status === 'GLOSADA'">
                                                        <input type="number" step="0.01"
                                                            class="form-control form-control-sm"
                                                            style="min-width: 90px; max-width: 100px;"
                                                            v-model="item.valor_glosado"
                                                            @blur="atualizarValorGlosado(lote.id, item.id, item.valor_glosado)">
                                                    </div>
                                                    <span v-else-if="item.valor_glosado > 0"
                                                        class="text-danger fw-medium">{{
                                                            formatCurrency(item.valor_glosado) }}</span>
                                                    <span v-else class="text-muted">-</span>
                                                </template>
                                            </template>
                                            <template #cell(status)="{ item }">
                                                <span
                                                    v-if="['ABERTA', 'PROCESSADA', 'RECEBIDO'].includes(lote.status) || item.status === 'DEVOLVIDA'">
                                                    {{ item.status ? item.status.replace(/_/g, ' ') : 'PENDENTE' }}
                                                </span>
                                                <div v-else>
                                                    <select :value="item.status" data-choices data-choices-search-false
                                                        style="min-width: 150px;" class="form-select form-select-sm"
                                                        :disabled="updatingStatus[item.id]"
                                                        @change="atualizarStatusGuia(lote.id, item.id, $event.detail ? $event.detail.value : $event.target.value)">
                                                        <option v-for="opt in statusOptions" :key="opt.value"
                                                            :value="opt.value">
                                                            {{ opt.label }}
                                                        </option>
                                                    </select>
                                                </div>
                                            </template>
                                            <template #actions="{ item }">
                                                <button v-if="lote.status === 'ABERTA'"
                                                    class="btn btn-sm btn-soft-danger shadow-none"
                                                    @click.stop="askDeleteGuia(lote.id, item.id)"
                                                    :disabled="removendoGuia === item.id || item.status === 'DEVOLVIDA'">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                                <button v-if="lote.status === 'FECHADA'"
                                                    class="btn btn-sm btn-soft-dark shadow-none ms-1"
                                                    @click.stop="askDevolverGuiaLote(item.id)"
                                                    :disabled="devolvendoGuiaId === item.id || item.status === 'DEVOLVIDA'"
                                                    title="Devolver Guia">
                                                    <i class="ri-arrow-go-back-line"></i>
                                                </button>
                                            </template>
                                        </SimpleTable>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Coluna Direita -->
            <div class="col-lg-3">
                <div class="sticky-top" style="top: 80px;">
                    <!-- Guias Vencidas -->
                    <div class="card shadow-sm border-0 border-danger mb-3">
                        <div class="card-header bg-danger-subtle align-items-center d-flex border-bottom-dashed">
                            <h4 class="card-title mb-0 flex-grow-1 fs-14 fw-semibold text-uppercase text-danger">
                                Guias Vencidas
                            </h4>
                            <div class="flex-shrink-0">
                                <span class="badge bg-danger fs-11">Atrasadas</span>
                            </div>
                        </div>

                        <div class="card-body px-0" style="max-height: 35vh; overflow-y: auto;">
                            <div v-if="guiasVencidas && guiasVencidas.length > 0">
                                <div class="list-group list-group-flush border-dashed">
                                    <div v-for="guia in guiasVencidas" :key="'venc-' + guia.id"
                                        class="list-group-item list-group-item-action d-flex align-items-center flex-column px-3 py-2 border-bottom">
                                        <div class="w-100 d-flex justify-content-between align-items-center mb-1">
                                            <div class="fw-semibold text-danger fs-13">
                                                Guia:
                                                <a v-if="guia.agendamento_id"
                                                    :href="`/guias/${guia.agendamento_id}/imprimir`" target="_blank"
                                                    class="text-decoration-underline text-danger" title="Imprimir Guia">
                                                    {{ guia.numero_guia || 'N/I' }}
                                                </a>
                                                <span v-else>{{ guia.numero_guia || 'N/I' }}</span>
                                            </div>
                                            <span class="badge bg-danger text-white fs-10" title="Dias atrasados">{{
                                                Math.abs(guia.dias_vencer) }}
                                                dias</span>
                                        </div>
                                        <div class="w-100 d-flex justify-content-between align-items-center">
                                            <div class="text-muted fs-12 text-truncate" style="max-width: 65%;">
                                                {{ guia.atendimento?.agendamento?.paciente?.nome || 'Paciente não info.'
                                                }}
                                            </div>
                                            <div class="fw-medium text-dark fs-12">
                                                {{ formatCurrency(guia.valor_total) }}
                                            </div>
                                        </div>
                                        <div class="w-100 mt-1 d-flex justify-content-between align-items-center">
                                            <div class="text-muted fs-11 text-truncate" style="max-width: 50%;">
                                                Convênio: <strong>{{ guia.faturamento?.convenio?.nome || 'N/A'
                                                }}</strong>
                                            </div>
                                            <div class="text-muted fs-11 text-end" v-if="guia.data_vencimento">
                                                Venceu: <strong class="text-danger">{{ guia.data_vencimento }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-4 px-3">
                                <div class="avatar-sm mx-auto mb-2">
                                    <div class="avatar-title bg-light text-success rounded-circle fs-20">
                                        <i class="ri-check-double-line"></i>
                                    </div>
                                </div>
                                <h6 class="fs-13 fw-semibold">Nenhuma guia vencida!</h6>
                            </div>
                        </div>
                    </div>

                    <!-- Guias Prestes a Vencer -->
                    <div class="card shadow-sm border-0 border-warning">
                        <div class="card-header bg-warning-subtle align-items-center d-flex border-bottom-dashed">
                            <h4
                                class="card-title mb-0 flex-grow-1 fs-14 fw-semibold text-uppercase text-warning-emphasis">
                                Prestes a Vencer
                            </h4>
                            <div class="flex-shrink-0">
                                <span class="badge bg-warning text-dark fs-11">Atenção</span>
                            </div>
                        </div>

                        <div class="card-body px-0" style="max-height: 35vh; overflow-y: auto;">
                            <div v-if="guiasPrestesAVencer && guiasPrestesAVencer.length > 0">
                                <div class="list-group list-group-flush border-dashed">
                                    <div v-for="guia in guiasPrestesAVencer" :key="guia.id"
                                        class="list-group-item list-group-item-action d-flex align-items-center flex-column px-3 py-2 border-bottom">
                                        <div class="w-100 d-flex justify-content-between align-items-center mb-1">
                                            <div class="fw-semibold text-primary fs-13">
                                                Guia:
                                                <a v-if="guia.agendamento_id"
                                                    :href="`/guias/${guia.agendamento_id}/imprimir`" target="_blank"
                                                    class="text-decoration-underline" title="Imprimir Guia">
                                                    {{ guia.numero_guia || 'N/I' }}
                                                </a>
                                                <span v-else>{{ guia.numero_guia || 'N/I' }}</span>
                                            </div>
                                            <span class="badge bg-warning text-dark fs-10">{{ guia.dias_vencer }}
                                                dias</span>
                                        </div>
                                        <div class="w-100 d-flex justify-content-between align-items-center">
                                            <div class="text-muted fs-12 text-truncate" style="max-width: 65%;">
                                                {{ guia.atendimento?.agendamento?.paciente?.nome || 'Paciente não info.'
                                                }}
                                            </div>
                                            <div class="fw-medium text-dark fs-12">
                                                {{ formatCurrency(guia.valor_total) }}
                                            </div>
                                        </div>
                                        <div class="w-100 mt-1 d-flex justify-content-between align-items-center">
                                            <div class="text-muted fs-11 text-truncate" style="max-width: 50%;">
                                                Convênio: <strong>{{ guia.faturamento?.convenio?.nome || 'N/A'
                                                }}</strong>
                                            </div>
                                            <div class="text-muted fs-11 text-end" v-if="guia.data_vencimento">
                                                Vence: <strong class="text-warning-emphasis">{{ guia.data_vencimento
                                                }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-4 px-3">
                                <div class="avatar-sm mx-auto mb-2">
                                    <div class="avatar-title bg-light text-success rounded-circle fs-20">
                                        <i class="ri-check-double-line"></i>
                                    </div>
                                </div>
                                <h6 class="fs-13 fw-semibold">Tudo em dia!</h6>
                                <p class="text-muted fs-12 mb-0">Nenhuma guia prestes a vencer encontrada no momento.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Criar Lote -->
        <Modal v-model="showLoteModal" title="Criar Lote de Faturamento" size="lg" :show-footer="true"
            @save="salvarLote" cancel-text="Cancelar">
            <div class="mb-3">
                <label class="form-label">Selecione o Convênio</label>
                <Multiselect v-model="loteForm.convenio_id" :options="conveniosOptions"
                    placeholder="Escolha um convênio..." @change="carregarGuias" />
            </div>


        </Modal>

        <!-- Modal Adicionar Nova Guia -->
        <Modal v-model="showAddModal"
            :title="'Adicionar Guias - Lote #' + (gerenciarLote?.numero_lote || gerenciarLote?.id || '')" size="xl"
            customWidth="98vw" :show-footer="false">
            <div v-if="loadingDisponiveisAdd" class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Buscando guias disponíveis...</p>
            </div>
            <div v-else>
                <div v-if="guiasDisponiveisAdd.length === 0" class="alert alert-info border-0 mb-0">
                    <i class="ri-information-line me-2"></i> Não há outras guias pendentes para o convênio <strong>{{
                        gerenciarLote?.convenio }}</strong>.
                </div>
                <div v-else>
                    <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                        <table class="table table-hover align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th scope="col" style="width: 50px;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" @change="toggleAllGuiasAdd"
                                                :checked="allGuiasAddSelected">
                                        </div>
                                    </th>
                                    <th>ID</th>
                                    <th>Nº da Guia</th>
                                    <th>Paciente</th>
                                    <th>Carteira</th>
                                    <th>Procedimento</th>
                                    <th>Profissional</th>
                                    <th>Tipo</th>
                                    <th>Senha/Aut.</th>
                                    <th>Status</th>
                                    <th>Valor</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="guia in guiasDisponiveisAdd" :key="guia.id">
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" :value="guia.id"
                                                v-model="guiasParaAdicionar">
                                        </div>
                                    </td>
                                    <td>#{{ guia.id }}</td>
                                    <td>
                                        <a v-if="guia.agendamento_id"
                                            :href="route('guias.imprimirDaAgenda', guia.agendamento_id) + '?guia_id=' + guia.id"
                                            target="_blank" class="text-primary fw-medium text-decoration-underline">
                                            <template v-if="guia.numero_guia_prestador || guia.numero_guia_operadora">
                                                {{ guia.numero_guia_prestador || guia.numero_guia_operadora }}
                                            </template>
                                            <template v-else>Ver Guia</template>
                                        </a>
                                        <span v-else class="text-primary fw-medium">{{ guia.numero_guia_prestador ||
                                            guia.numero_guia_operadora || '-' }}</span>
                                    </td>
                                    <td>
                                        {{ guia.paciente_nome }}<br>
                                    </td>
                                    <td>{{ guia.numero_carteira || '-' }}</td>
                                    <td>{{ guia.procedimento_solicitado_descricao || '-' }}</td>
                                    <td>{{ guia.profissional_solicitante_nome || '-' }}</td>
                                    <td>{{ guia.tipo || 'Guia de Consulta' }}</td>
                                    <td>{{ guia.senha || '-' }}</td>
                                    <td><span class="badge" :class="getBadgeClass(guia.status)">{{ guia.status }}</span>
                                    </td>
                                    <td>{{ formatCurrency(guia.valor_total) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-soft-danger"
                                            @click="devolverGuiaParaContasMedicas(guia.id)"
                                            title="Devolver para Contas Médicas"
                                            :disabled="devolvendoGuiaId === guia.id || guia.status === 'DEVOLVIDA'">
                                            <i class="ri-arrow-go-back-line"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3 p-3 bg-light rounded">
                        <span class="me-3 fw-medium">Selecionadas: {{ guiasParaAdicionar.length }}</span>
                        <button class="btn btn-success" @click="adicionarGuiasAoLote"
                            :disabled="guiasParaAdicionar.length === 0 || adicionandoGuias">
                            <i class="ri-add-circle-line align-bottom me-1"></i>
                            <span v-if="adicionandoGuias">Adicionando...</span>
                            <span v-else>Adicionar Guias Selecionadas</span>
                        </button>
                    </div>
                </div>
            </div>
        </Modal>

        <ModalDelete v-model="deleteModal" title="Remover Guia" :subTitle="deleteSubTitle" @save="confirmDeleteGuia" />

        <ModalConfirm v-model="confirmDevolverModal" title="Devolver Guia"
            subTitle="Deseja realmente devolver esta guia para a equipe de Contas Médicas?"
            message="Esta guia será removida desta lista e voltará para a fila de validação."
            nameButton="Sim, devolver guia" buttonClass="btn-warning" @save="executarDevolverGuia" />

        <ModalConfirm v-model="confirmDevolverLoteModal" title="Devolver Guia"
            subTitle="Deseja realmente devolver esta guia para a equipe de Contas Médicas?"
            message="Esta guia será clonada e retornará para a fila de validação, mantendo o histórico original intacto."
            nameButton="Sim, devolver guia" buttonClass="btn-warning" @save="executarDevolverGuiaLote" />

        <ModalConfirm v-model="confirmFecharLoteModal" title="Fechar Lote" subTitle="Deseja realmente fechar este lote?"
            message="Ao fechar o lote, não será mais possível adicionar ou remover guias. Porém, você poderá alterar o status e valor da glosa das guias."
            nameButton="Sim, fechar lote" buttonClass="btn-warning" @save="executarFecharLote" />

        <ModalConfirm v-model="confirmReabrirLoteModal" title="Reabrir Lote"
            subTitle="Deseja realmente reabrir este lote?"
            message="O lote voltará a receber edições e ficará com o status de ABERTA." nameButton="Sim, reabrir"
            buttonClass="btn-info" @save="executarReabrirLote" />

        <ModalConfirm v-model="confirmProcessarLoteModal" title="Processar Lote"
            subTitle="Deseja realmente processar este lote?"
            message="Esta ação é irreversível. O lote e as guias não poderão mais ser alterados e os lançamentos no financeiro (Contas a Receber) serão gerados."
            nameButton="Sim, processar" buttonClass="btn-primary" @save="executarProcessarLote" />

    </Layout>
</template>

<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Modal from "@/Components/Modal.vue";
import ModalDelete from "@/Components/ModalDelete.vue";
import ModalConfirm from "@/Components/ModalConfirm.vue";
import SimpleTable from "@/Components/Tables/SimpleTable.vue";
import Multiselect from '@vueform/multiselect';
import "@vueform/multiselect/themes/default.css";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.min.css";
import "flatpickr/dist/l10n/pt.js";
import axios from "axios";

const flatpickrOptions = { altInput: true, altInputClass: "form-control", altFormat: "d M, Y", dateFormat: "Y-m-d", locale: "pt", static: true };

const guiasLoteColumns = [
    { key: 'numero', label: 'Nº da Guia' },
    { key: 'data_atendimento', label: 'Data' },
    { key: 'paciente_nome', label: 'Paciente' },
    { key: 'tipo', label: 'Tipo' },
    { key: 'senha', label: 'Senha/Aut.' },
    { key: 'valor_total', label: 'Valor' },
    { key: 'glosa', label: 'Glosa' },
    { key: 'status', label: 'Status' }
];

const props = defineProps({
    faturamentos: { type: Array, default: () => [] },
    guiasPrestesAVencer: { type: Array, default: () => [] },
    guiasVencidas: { type: Array, default: () => [] },
    convenios_list: { type: Array, default: () => [] },
});

// Filtros
const filtros = ref({
    busca: '',
    convenio: '',
    status: '',
    dataInicio: '',
    dataFim: ''
});

function limparFiltros() {
    filtros.value = { busca: '', convenio: '', status: '', dataInicio: '', dataFim: '' };
}

const faturamentosFiltrados = computed(() => {
    let resultado = props.faturamentos;

    // Filtro por busca
    if (filtros.value.busca) {
        const q = filtros.value.busca.toLowerCase();
        resultado = resultado.filter(lote =>
            (lote.convenio && lote.convenio.toLowerCase().includes(q)) ||
            String(lote.id).includes(q)
        );
    }

    // Filtro por convênio
    if (filtros.value.convenio) {
        resultado = resultado.filter(lote => lote.convenio === filtros.value.convenio);
    }

    // Filtro por status
    if (filtros.value.status) {
        if (filtros.value.status === 'COM_GLOSA') {
            resultado = resultado.filter(lote => calcularValorGlosado(lote) > 0);
        } else {
            resultado = resultado.filter(lote => lote.status === filtros.value.status);
        }
    }

    // Filtro por data início
    if (filtros.value.dataInicio) {
        resultado = resultado.filter(lote => lote.data_faturamento >= filtros.value.dataInicio);
    }

    // Filtro por data fim
    if (filtros.value.dataFim) {
        resultado = resultado.filter(lote => lote.data_faturamento <= filtros.value.dataFim);
    }

    return resultado;
});

function formatCurrency(n) {
    const v = Number(n || 0);
    try {
        return v.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
    } catch (_) {
        return `R$ ${v.toFixed(2)}`.replace(".", ",");
    }
}

// Criar Lote Logic
const showLoteModal = ref(false);
const loadingGuias = ref(false);
const guiasDisponiveis = ref([]);

const conveniosOptions = computed(() => {
    return props.convenios_list.map(c => ({
        value: c.id,
        label: c.descricao
    }));
});

const loteForm = useForm({
    convenio_id: null,
    guias: []
});

function openCriarLoteModal() {
    loteForm.reset();
    guiasDisponiveis.value = [];
    showLoteModal.value = true;
}

function salvarLote() {
    loteForm.post(route('faturamento.store_lote'), {
        onSuccess: () => {
            showLoteModal.value = false;
        }
    });
}

// Toggle Collapse Logic
const expandedLotes = ref([]);

function isExpanded(loteId) {
    return expandedLotes.value.includes(loteId);
}

function toggleCollapse(loteId) {
    if (isExpanded(loteId)) {
        expandedLotes.value = expandedLotes.value.filter(id => id !== loteId);
    } else {
        expandedLotes.value.push(loteId);
    }
}

// Remover Guia Logic
const removendoGuia = ref(null);
const deleteModal = ref(false);
const deleteSubTitle = ref('Deseja realmente remover esta guia do lote?');
const guiaToRemove = ref({ loteId: null, guiaId: null });

function askDeleteGuia(loteId, guiaId) {
    guiaToRemove.value = { loteId, guiaId };
    deleteModal.value = true;
}

function confirmDeleteGuia() {
    if (!guiaToRemove.value.loteId) return;

    removendoGuia.value = guiaToRemove.value.guiaId;
    router.delete(route('faturamentos.guias.remove', { lote: guiaToRemove.value.loteId, guia: guiaToRemove.value.guiaId }), {
        preserveScroll: true,
        onFinish: () => {
            removendoGuia.value = null;
            deleteModal.value = false;
        }
    });
}

const statusOptions = [
    // { value: 'PRONTA_FATURAMENTO', label: 'PRONTA FATURAMENTO' },
    // { value: 'ENVIADA_FATURAMENTO', label: 'ENVIADA FATURAMENTO' },
    { value: 'FATURADA', label: 'FATURADA' },
    { value: 'GLOSADA', label: 'GLOSADA' }
];

const updatingStatus = ref({});

function atualizarStatusGuia(loteId, guiaId, novoStatus) {
    const lote = props.faturamentos.find(l => l.id === loteId);
    const guia = lote?.guias?.find(g => g.id === guiaId);

    // Evita loop infinito caso o evento dispare repetidamente com o mesmo valor
    if (guia && guia.status === novoStatus) return;

    // Evita requisições concorrentes ou loops de re-inicialização do Choices.js
    if (updatingStatus.value[guiaId]) return;

    updatingStatus.value[guiaId] = true;

    router.patch(route('faturamentos.guias.updateStatus', { lote: loteId, guia: guiaId }), { status: novoStatus }, {
        preserveScroll: true,
        onFinish: () => {
            updatingStatus.value[guiaId] = false;
        },
        onError: () => {
            window.dispatchEvent(new CustomEvent('flash:show', {
                detail: { type: 'danger', message: 'Erro ao atualizar o status da guia.' }
            }));
        }
    });
}

const getGuiaRowClass = (item) => {
    return item.status === 'DEVOLVIDA' ? 'row-devolvida' : '';
};

function atualizarValorGlosado(loteId, guiaId, valorGlosado) {
    router.patch(route('faturamentos.guias.updateGlosa', { lote: loteId, guia: guiaId }), { valor_glosado: valorGlosado }, {
        preserveScroll: true,
        onError: () => {
            window.dispatchEvent(new CustomEvent('flash:show', {
                detail: { type: 'danger', message: 'Erro ao atualizar o valor glosado.' }
            }));
        }
    });
}

// Adicionar Guia Logic
const showAddModal = ref(false);
const gerenciarLote = ref(null);
const guiasDisponiveisAdd = ref([]);
const loadingDisponiveisAdd = ref(false);
const guiasParaAdicionar = ref([]);
const adicionandoGuias = ref(false);
const devolvendoGuiaId = ref(null);
const confirmDevolverModal = ref(false);
const confirmDevolverLoteModal = ref(false);
const guiaIdParaDevolver = ref(null);

function abrirAddModal(lote) {
    gerenciarLote.value = lote;
    showAddModal.value = true;
    carregarGuiasDisponiveisAdd();
}

function carregarGuiasDisponiveisAdd() {
    loadingDisponiveisAdd.value = true;
    axios.get(route('faturamento.guias_disponiveis'), { params: { convenio_id: gerenciarLote.value.convenio_id } })
        .then(res => {
            guiasDisponiveisAdd.value = res.data || [];
            guiasParaAdicionar.value = [];
        })
        .finally(() => {
            loadingDisponiveisAdd.value = false;
        });
}

const allGuiasAddSelected = computed(() => {
    return guiasDisponiveisAdd.value.length > 0 && guiasParaAdicionar.value.length === guiasDisponiveisAdd.value.length;
});

function toggleAllGuiasAdd(e) {
    if (e.target.checked) {
        guiasParaAdicionar.value = guiasDisponiveisAdd.value.map(g => g.id);
    } else {
        guiasParaAdicionar.value = [];
    }
}

function adicionarGuiasAoLote() {
    adicionandoGuias.value = true;
    router.post(route('faturamentos.guias.add', gerenciarLote.value.id), {
        guias: guiasParaAdicionar.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showAddModal.value = false;
        },
        onFinish: () => {
            adicionandoGuias.value = false;
        }
    });
}

function askDevolverGuiaLote(guiaId) {
    guiaIdParaDevolver.value = guiaId;
    confirmDevolverLoteModal.value = true;
}

function executarDevolverGuiaLote() {
    if (!guiaIdParaDevolver.value) return;
    devolvendoGuiaId.value = guiaIdParaDevolver.value;
    confirmDevolverLoteModal.value = false;

    axios.post(route('guias.devolver', guiaIdParaDevolver.value))
        .then(res => {
            window.dispatchEvent(new CustomEvent('flash:show', {
                detail: { type: 'success', message: 'Guia devolvida com sucesso.' }
            }));
            router.reload({ preserveScroll: true });
        })
        .catch(err => {
            window.dispatchEvent(new CustomEvent('flash:show', {
                detail: { type: 'danger', message: 'Erro ao devolver guia.' }
            }));
        })
        .finally(() => {
            devolvendoGuiaId.value = null;
            guiaIdParaDevolver.value = null;
        });
}

const devolverGuiaParaContasMedicas = (id) => {
    guiaIdParaDevolver.value = id;
    confirmDevolverModal.value = true;
};

const executarDevolverGuia = async () => {
    if (!guiaIdParaDevolver.value) return;
    const id = guiaIdParaDevolver.value;
    try {
        confirmDevolverModal.value = false;
        devolvendoGuiaId.value = id;
        await axios.post(route('faturamento.guias.devolver', id));
        guiasDisponiveisAdd.value = guiasDisponiveisAdd.value.filter(g => g.id !== id);
        window.dispatchEvent(new CustomEvent('flash:show', {
            detail: { type: 'success', message: 'Guia devolvida para o Contas Médicas com sucesso!' }
        }));
    } catch (error) {
        console.error(error);
        window.dispatchEvent(new CustomEvent('flash:show', {
            detail: { type: 'danger', message: error.response?.data?.message || 'Erro ao devolver a guia.' }
        }));
    } finally {
        devolvendoGuiaId.value = null;
        guiaIdParaDevolver.value = null;
    }
};

// Fechar / Reabrir Lote Logic
const fechandoLote = ref(null);
const confirmFecharLoteModal = ref(false);
const confirmReabrirLoteModal = ref(false);
const confirmProcessarLoteModal = ref(false);
const loteParaFechar = ref(null);
const loteParaReabrir = ref(null);
const loteParaProcessar = ref(null);

function askFecharLote(loteId) {
    loteParaFechar.value = loteId;
    confirmFecharLoteModal.value = true;
}

function askReabrirLote(loteId) {
    loteParaReabrir.value = loteId;
    confirmReabrirLoteModal.value = true;
}

function executarFecharLote() {
    if (!loteParaFechar.value) return;
    fechandoLote.value = loteParaFechar.value;
    router.patch(route('faturamentos.fechar', loteParaFechar.value), {}, {
        preserveScroll: true,
        onFinish: () => {
            fechandoLote.value = null;
            confirmFecharLoteModal.value = false;
            loteParaFechar.value = null;
        }
    });
}

function executarReabrirLote() {
    if (!loteParaReabrir.value) return;
    fechandoLote.value = loteParaReabrir.value;
    router.patch(route('faturamentos.fechar', loteParaReabrir.value), {}, {
        preserveScroll: true,
        onFinish: () => {
            fechandoLote.value = null;
            confirmReabrirLoteModal.value = false;
            loteParaReabrir.value = null;
        }
    });
}

function askProcessarLote(loteId) {
    loteParaProcessar.value = loteId;
    confirmProcessarLoteModal.value = true;
}

function executarProcessarLote() {
    if (!loteParaProcessar.value) return;
    fechandoLote.value = loteParaProcessar.value;
    confirmProcessarLoteModal.value = false;

    router.post(route('faturamento.processar_lote', loteParaProcessar.value), {}, {
        preserveScroll: true,
        onSuccess: () => {
            window.dispatchEvent(new CustomEvent('flash:show', {
                detail: { type: 'success', message: 'Lote processado com sucesso.' }
            }));
            loteParaProcessar.value = null;
        },
        onError: () => {
            window.dispatchEvent(new CustomEvent('flash:show', {
                detail: { type: 'danger', message: 'Erro ao processar lote.' }
            }));
        },
        onFinish: () => {
            fechandoLote.value = null;
        }
    });
}

function baixarXml(loteId) {
    window.dispatchEvent(new CustomEvent('flash:show', {
        detail: { type: 'info', message: 'Gerando arquivo XML para o lote #' + loteId + '...' }
    }));

    // Abrir a rota de download em uma nova aba para forçar o navegador a baixar o arquivo
    window.open(route('faturamento.xml', loteId), '_blank');
}

// Helpers
function getBadgeClass(status) {
    const s = String(status || '').toUpperCase();
    switch (s) {
        case 'CRIADA': return 'bg-secondary';
        case 'ATENDIDO': return 'bg-info';
        case 'VALIDADA': return 'bg-primary';
        case 'EM_ANALISE': return 'bg-warning text-dark';
        case 'AUTORIZADA': return 'bg-success';
        case 'PRONTA_FATURAMENTO': return 'bg-success-subtle text-success';
        case 'ENVIADA_FATURAMENTO': return 'bg-warning-subtle text-warning';
        case 'FATURADA': return 'bg-dark-subtle text-dark';
        case 'GLOSADA': return 'bg-danger-subtle text-danger';
        case 'DEVOLVIDA': return 'bg-dark';
        case 'PAGA': return 'bg-primary';
        case 'CANCELADA': return 'bg-danger';
        default: return 'bg-info';
    }
}

function getProgressBarClass(status) {
    switch (status) {
        case 'CRIADA': return 'bg-secondary';
        case 'ATENDIDO': return 'bg-info';
        case 'EM_ANALISE': return 'bg-warning text-dark';
        case 'AUTORIZADA': return 'bg-success';
        case 'GLOSADA': return 'bg-danger';
        case 'DEVOLVIDA': return 'bg-dark';
        case 'PAGA': return 'bg-primary';
        default: return 'bg-info';
    }
}

function getProgressTextClass(status) {
    switch (status) {
        case 'CRIADA': return 'text-secondary';
        case 'ATENDIDO': return 'text-info';
        case 'EM_ANALISE': return 'text-warning';
        case 'AUTORIZADA': return 'text-success';
        case 'GLOSADA': return 'text-danger';
        case 'DEVOLVIDA': return 'text-dark';
        case 'PAGA': return 'text-primary';
        default: return 'text-info';
    }
}

function getLoteStatusClass(status) {
    if (status === 'FECHADA') return 'bg-dark';
    if (status === 'ABERTA') return 'bg-warning text-dark';
    return 'bg-secondary';
}

function calcularValorGlosado(lote) {
    if (!lote || !lote.guias) return 0;
    return lote.guias.reduce((sum, guia) => sum + (parseFloat(guia.valor_glosado) || 0), 0);
}

function calcularValorAprovado(lote) {
    if (!lote) return 0;

    // If it is saved in the database
    if (lote.valor_aprovado !== undefined && lote.valor_aprovado !== null) {
        return parseFloat(lote.valor_aprovado);
    }

    // Dynamic fallback
    const total = parseFloat(lote.valor_total) || 0;
    const glosado = calcularValorGlosado(lote);
    return total - glosado;
}

function getBgColorLote(status) {
    if (status === 'PAGA') return 'bg-success';
    if (status === 'GLOSADA') return 'bg-danger';
    return 'bg-secondary';
}
</script>

<style scoped>
/* Corrige o corte do dropdown Choices.js dentro de tabelas responsivas */
:deep(.table-wrapper-choices .table-responsive) {
    overflow: visible !important;
}

/* Corrige alinhamento do flatpickr static wrapper nos filtros */
:deep(.flatpickr-wrapper) {
    display: block !important;
    width: 100%;
}
</style>

<style scoped>
:deep(.row-devolvida) {
    opacity: 0.6;
    background-color: rgba(var(--vz-secondary-rgb), 0.05);
}

:deep(.row-devolvida td) {
    position: relative;
}

:deep(.row-devolvida td::after) {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    top: 50%;
    border-top: 1px solid var(--vz-danger);
    pointer-events: none;
}
</style>
