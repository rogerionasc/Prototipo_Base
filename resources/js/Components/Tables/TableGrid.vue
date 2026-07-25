<script setup>
// -------------------- IMPORTAÇÕES --------------------
import { ref, nextTick, watch, onMounted, computed, onBeforeUnmount } from 'vue';
import { Grid, html } from 'gridjs';
import 'gridjs/dist/theme/mermaid.css';
import Multiselect from '@vueform/multiselect';
import "@vueform/multiselect/themes/default.css";
import 'flatpickr/dist/flatpickr.css';
import Lottie from 'lottie-web';
import animationData from "@/Components/widgets/msoeawqm.json";
import animationCube from "@/Components/widgets/auvicynv.json";
import LottieComponent from "@/Components/widgets/lottie.vue";

// -------------------- PROPRIEDADES (PROPS) --------------------
// Define as propriedades que o componente pode receber
const props = defineProps({
    data: { type: Array, default: () => [] },
    columns: { type: Array, default: () => ["ID", "Nome", "Email", "Cargo", "Empresa", "País"] },
    serverUrl: { type: String, default: '' },
    serverQuery: { type: Object, default: () => ({}) },
    showStatus: { type: Boolean, default: false },
    search: { type: Boolean, default: true },
    searchPlaceholder: { type: String, default: 'Buscar...' },
    showCheckbox: { type: Boolean, default: true },
    showMultiDelete: { type: Boolean, default: true },
    showAddButton: { type: Boolean, default: true },
    addButtonText: { type: String, default: 'Adicionar' },
    addButtonIconClass: { type: String, default: 'ri-add-fill' },
    addButtonDisabled: { type: Boolean, default: false },
    showActions: { type: Boolean, default: true },
    showImage: { type: Boolean, default: false },
    showPerPagination: { type: Boolean, default: true },
    tableTitle: { type: String, default: 'Listas ...' },
    showDiaryButton: { type: Boolean, default: false },
    actionsConfig: { type: Object, default: () => ({ delete: true, edit: true, show: true, diary: false, print: false, download: false, restore: false, receive: false }) },
    actionsLabels: { type: Object, default: () => ({ delete: 'Excluir', edit: 'Editar', show: 'Visualizar', diary: 'Agenda', print: 'Imprimir', download: 'Baixar', restore: 'Reabrir', receive: 'Receber' }) },
    actionsButtonText: { type: Object, default: () => ({}) },
    actionsIcons: { type: Object, default: () => ({}) },
    actionsLoading: { type: Object, default: () => ({}) },
    disableActions: { type: Boolean, default: false },
    compactSpacing: { type: Boolean, default: false },
});

// -------------------- EMITS --------------------
// Define os eventos que o componente pode emitir
const emit = defineEmits([
    'add',
    'modalDdeletarMultiplos',
    'delete',
    'edit',
    'show',
    'diary',
    'print',
    'download',
    'restore',
    'receive',
    'procedure',
    'selectionChange'
]);

// -------------------- REFS E VARIÁVEIS REATIVAS --------------------
const wrapper = ref(null); // Referência ao container da tabela
const searchQuery = ref(''); // Valor do campo de busca
const debouncedQuery = ref(''); // Valor da busca com debounce
const limit = ref(10); // Limite de itens por página
const selectedRows = ref([]); // IDs das linhas selecionadas
let changeListener = null; // Listener para mudanças em checkboxes
let clickListener = null; // Listener para cliques em botões de ação
const isLoading = ref(true); // Estado de carregamento da tabela
let gridInstance = null; // Instância do Grid.js
const lastServerRows = ref([]);
let lottieObserver = null;
let tableObserver = null;
let tableObserverTimer = null;

function observeTableSelectionPersistence() {
    if (!wrapper.value) return;
    if (tableObserver) {
        try { tableObserver.disconnect(); } catch (_) {}
        tableObserver = null;
    }
    tableObserver = new MutationObserver(() => {
        if (tableObserverTimer) clearTimeout(tableObserverTimer);
        tableObserverTimer = setTimeout(() => {
            updateCheckboxes();
        }, 0);
    });
    tableObserver.observe(wrapper.value, { childList: true, subtree: true });
}

// -------------------- UTILITÁRIOS --------------------
// Função debounce para atrasar a execução de uma função
function useDebounce(fn, delay) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            fn.apply(this, args);
        }, delay);
    };
}

// Atualiza a consulta de busca após 500ms
const updateDebouncedQuery = useDebounce(() => {
    debouncedQuery.value = searchQuery.value;
}, 500);

// Observa mudanças no campo de busca e aplica debounce
watch(searchQuery, () => {
    isLoading.value = true;
    updateDebouncedQuery();
});

// Observa mudanças na busca ou no limite de itens por página e reinicializa a tabela
watch([debouncedQuery, limit], () => {
    nextTick(initGrid);
});

// -------------------- FILTRO DE DADOS --------------------
// Filtra os dados conforme a busca
function filterData(data, query) {
    if (!query) return data;
    const q = query.toLowerCase();
    return data.filter((row) =>
        Object.values(row).some((value) =>
            String(value).toLowerCase().includes(q)
        )
    );
}

// Dados filtrados de acordo com a busca
const filteredData = computed(() => {
    if (props.serverUrl) return props.data;
    return filterData(props.data, debouncedQuery.value);
});

function buildServerUrl(offset, limitValue, searchValue) {
    const base = String(props.serverUrl || '').trim();
    const q = String(searchValue || '').trim();
    const params = new URLSearchParams();
    params.set('limit', String(limitValue ?? 10));
    params.set('offset', String(offset ?? 0));
    if (q) params.set('q', q);
    const extra = props.serverQuery && typeof props.serverQuery === 'object' ? props.serverQuery : {};
    Object.entries(extra).forEach(([k, v]) => {
        if (v === null || typeof v === 'undefined') return;
        const s = String(v).trim();
        if (!s) return;
        params.set(String(k), s);
    });
    return base.includes('?') ? `${base}&${params.toString()}` : `${base}?${params.toString()}`;
}

// -------------------- ANIMAÇÃO LOTTIE --------------------
// Observa o container da animação Lottie e inicializa quando necessário
function observeLottieContainer() {
    const initAll = () => {
        const nodes = document.querySelectorAll('.lottie-container');
        nodes.forEach((noResult) => {
            if (!noResult || noResult.dataset.lottieInitialized) return;
            noResult.dataset.lottieInitialized = 'true';
            Lottie.loadAnimation({
                container: noResult,
                renderer: 'svg',
                loop: true,
                autoplay: true,
                animationData,
            });
        });
    };

    initAll();
    if (lottieObserver) return;
    lottieObserver = new MutationObserver(() => {
        initAll();
    });
    lottieObserver.observe(document.body, { childList: true, subtree: true });
}

// -------------------- BADGE DE STATUS --------------------
// Retorna o badge de status estilizado conforme o valor
const getStatusBadge = (cell) => {
    const statusMap = {
        'ativo': { class: 'bg-success-subtle text-success', text: 'Ativo' },
        'inativo': { class: 'bg-danger-subtle text-danger', text: 'Inativo' },
        'pendente': { class: 'bg-warning-subtle text-warning', text: 'Pendente' },
        'suspenso': { class: 'bg-secondary-subtle text-secondary', text: 'Suspenso' }
    };

    const status = String(cell ?? '').toLowerCase();
    const { class: badgeClass, text: badgeText } = statusMap[status] || {
        class: 'bg-light text-dark',
        text: cell || 'Desconhecido'
    };

    return html(`<span class="badge ${badgeClass}">${badgeText}</span>`);
};

function resolveImageUrl(v) {
    const s = String(v ?? '').trim();
    if (!s) return '';
    if (s.startsWith('http://') || s.startsWith('https://') || s.startsWith('data:') || s.startsWith('/')) return s;
    return `/storage/${s}`;
}

function buildImageCell(cell, col) {
    const src = resolveImageUrl(cell);
    if (!src) return html(`<span class="text-muted">—</span>`);
    const alt = String(col?.name ?? 'Imagem');
    return html(`<div class="d-flex align-items-center justify-content-center" style="height:40px;"><img src="${encodeURI(src)}" alt="${alt.replace(/"/g, '&quot;')}" style="height: 40px; width: 40px; object-fit: contain; display:block;" /></div>`);
}

function shouldIncludeColumn(col) {
    if (typeof col === 'object' && col) {
        if (col.showImage === true && props.showImage === false) return false;
    }
    return true;
}

function getSelectedRowIds() {
    return [...(selectedRows.value || [])];
}

function clearSelection() {
    selectedRows.value = [];
    emit('selectionChange', getSelectedRowIds());
    nextTick(updateCheckboxes);
}

function setSelectedRowIds(ids) {
    selectedRows.value = Array.isArray(ids) ? [...ids].map(String) : [];
    emit('selectionChange', getSelectedRowIds());
    nextTick(updateCheckboxes);
}

function getSelectedRowObjects() {
    const ids = new Set((selectedRows.value || []).map(x => String(x)));
    const rows = Array.isArray(lastServerRows.value) ? lastServerRows.value : [];
    const local = Array.isArray(props.data) ? props.data : [];
    const pool = props.serverUrl ? rows : local;
    return pool.filter(r => ids.has(String(r?.id)));
}

// -------------------- CHECKBOXES --------------------
// Atualiza o estado dos checkboxes de seleção
function updateCheckboxes() {
    const checkAll = wrapper.value?.querySelector('input[data-check-all]');
    const checkboxes = wrapper.value?.querySelectorAll('input[data-row-id]');
    checkboxes?.forEach(checkbox => {
        const rowId = checkbox.getAttribute('data-row-id');
        checkbox.checked = selectedRows.value.includes(rowId);
    });
    if (checkAll && checkboxes) {
        const totalCheckboxes = checkboxes.length;
        const selectedCheckboxes = Array.from(checkboxes).filter(cb => cb.checked).length;
        checkAll.checked = totalCheckboxes > 0 && totalCheckboxes === selectedCheckboxes;
    }
}

// Manipula mudanças nos checkboxes (seleção de linhas ou selecionar tudo)
function handleCheckboxChange(e) {
    const target = e.target;
    if (!target.matches('input[type="checkbox"]')) return;
    if (target.hasAttribute('data-check-all')) {
        const checkboxes = wrapper.value.querySelectorAll('input[data-row-id]');
        if (target.checked) {
            checkboxes.forEach(checkbox => {
                const rowId = checkbox.getAttribute('data-row-id');
                if (!selectedRows.value.includes(rowId)) {
                    selectedRows.value.push(rowId);
                }
                checkbox.checked = true;
            });
        } else {
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            selectedRows.value = [];
        }
        emit('selectionChange', getSelectedRowIds());
    } else if (target.hasAttribute('data-row-id')) {
        const rowId = target.getAttribute('data-row-id');
        if (target.checked) {
            selectedRows.value.push(rowId);
        } else {
            selectedRows.value = selectedRows.value.filter(id => id !== rowId);
        }
        const checkboxes = wrapper.value.querySelectorAll('input[data-row-id]');
        const totalCheckboxes = checkboxes.length;
        const selectedCheckboxes = Array.from(checkboxes).filter(cb => cb.checked).length;
        const checkAll = wrapper.value.querySelector('input[data-check-all]');
        if (checkAll) {
            checkAll.checked = totalCheckboxes > 0 && totalCheckboxes === selectedCheckboxes;
        }
        emit('selectionChange', getSelectedRowIds());
    }
}

// -------------------- INICIALIZAÇÃO DA TABELA --------------------
// Inicializa e renderiza a tabela Grid.js
function initGrid() {
    if (!wrapper.value) return;
    if (changeListener) {
        wrapper.value.removeEventListener('change', changeListener);
    }
    if (clickListener) {
        wrapper.value.removeEventListener('click', clickListener);
    }
    if (tableObserver) {
        try { tableObserver.disconnect(); } catch (_) {}
        tableObserver = null;
    }
    if (tableObserverTimer) {
        clearTimeout(tableObserverTimer);
        tableObserverTimer = null;
    }
    if (gridInstance) {
        gridInstance.destroy();
        gridInstance = null;
    }

    const defaultFormatter = (cell) => {
        if (typeof cell === 'string') {
            if (cell.match(/^\d{2}-\d{2}-\d{4}([T ]\d{2}:\d{2}(:\d{2})?)?$/)) {
                return cell.replace(/-/g, '/').replace('T', ' ');
            }
            if (cell.match(/^\d{4}-\d{2}-\d{2}([T ]\d{2}:\d{2}:\d{2}(\.\d+)?Z?)?$/)) {
                const isDateOnly = cell.length === 10;
                const dt = isDateOnly ? `${cell}T00:00:00` : cell.replace(' ', 'T');
                const d = new Date(dt);
                if (!isNaN(d.getTime())) {
                    const day = String(d.getDate()).padStart(2, '0');
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const year = d.getFullYear();
                    if (isDateOnly) {
                        return `${day}/${month}/${year}`;
                    } else {
                        const hours = String(d.getHours()).padStart(2, '0');
                        const mins = String(d.getMinutes()).padStart(2, '0');
                        return `${day}/${month}/${year} ${hours}:${mins}`;
                    }
                }
            }
        }
        return cell;
    };

    isLoading.value = true;
    let gridColumns;
    const visibleBaseColumns = (props.columns || []).filter(shouldIncludeColumn);
    // Monta as colunas da tabela, incluindo checkbox se necessário
    if (props.showCheckbox) {
        gridColumns = [
            {
                id: 'select',
                name: html(`<div class="d-flex justify-content-center" style="width:44px;"><input type="checkbox" class="form-check-input" data-check-all="true" /></div>`),
                width: '44px',
                attributes: () => ({ style: 'width:44px;min-width:44px;max-width:44px;padding-left:.5rem;padding-right:.5rem;' }),
                formatter: (cell, row) => {
                    const rowId = row.cells[1].data;
                    return html(`<div class="d-flex justify-content-center" style="width:44px;"><input type="checkbox" class="form-check-input" data-row-id="${rowId}" ${selectedRows.value.includes(rowId) ? 'checked' : ''} /></div>`);
                }
            },
            ...visibleBaseColumns.map((col, idx) => {
                if (typeof col === 'object') {
                    const computedId = col.id
                        ? String(col.id)
                        : (typeof col.name === 'string' ? String(col.name).toLowerCase() : `col_${idx}`);
                    const out = {
                        id: computedId,
                        name: col.name,
                        sort: typeof col.sort === 'boolean' ? col.sort : true
                    };
                    if (typeof col.formatter === 'function') out.formatter = col.formatter;
                    else if (col.showImage === true) out.formatter = (cell) => buildImageCell(cell, col);
                    else out.formatter = defaultFormatter;
                    if (col.attributes) out.attributes = col.attributes;
                    if (col.width) out.width = col.width;
                    return out;
                } else {
                    return {
                        id: String(col).toLowerCase(),
                        name: col,
                        sort: true,
                        formatter: defaultFormatter
                    };
                }
            })
        ];
    } else {
        gridColumns = visibleBaseColumns.map((col, idx) => {
            if (typeof col === 'object') {
                const computedId = col.id
                    ? String(col.id)
                    : (typeof col.name === 'string' ? String(col.name).toLowerCase() : `col_${idx}`);
                const out = {
                    id: computedId,
                    name: col.name,
                    sort: typeof col.sort === 'boolean' ? col.sort : true
                };
                if (typeof col.formatter === 'function') out.formatter = col.formatter;
                else if (col.showImage === true) out.formatter = (cell) => buildImageCell(cell, col);
                else out.formatter = defaultFormatter;
                if (col.attributes) out.attributes = col.attributes;
                if (col.width) out.width = col.width;
                return out;
            } else {
                return {
                    id: String(col).toLowerCase(),
                    name: col,
                    sort: true,
                    formatter: defaultFormatter
                };
            }
        });
    }

    // Adiciona coluna de status se necessário
    if (props.showStatus) {
        gridColumns.push({
            id: 'status',
            name: 'Status',
            formatter: (cell) => getStatusBadge(cell)
        });
    }
    // Adiciona coluna de ações se necessário
    if (props.showActions) {
        gridColumns.push({
            id: 'actions',
            name: 'Ações',
            formatter: (cell, row) => {
                if (!row || !row.cells || !Array.isArray(row.cells)) {
                    return html(`<div class="d-flex gap-2"></div>`);
                }
                const idIndex = props.showCheckbox ? 1 : 0;
                const firstCell = row.cells[idIndex]?.data;
                let idCol;
                if (visibleBaseColumns[0] && typeof visibleBaseColumns[0] === 'object') {
                    idCol = visibleBaseColumns[0].id || visibleBaseColumns[0].name;
                } else {
                    idCol = visibleBaseColumns[0];
                }
                const rowBase = props.serverUrl ? (lastServerRows.value || []) : (props.data || []);
                const rowData = rowBase.find(r => {
                    const matchId = r.id && String(r.id) === String(firstCell);
                    const matchCol = idCol && String(r[idCol]) === String(firstCell);
                    return matchId || matchCol;
                }) || {};
                const rowId = rowData?.id || firstCell;
                const rowDataStr = JSON.stringify(rowData).replace(/'/g, "&#39;");
                const ac = props.actionsConfig || { delete: true, edit: true, show: true, diary: false };
                const al = props.actionsLabels || {};
                const bt = props.actionsButtonText || {};
                const ai = props.actionsIcons || {};
                const disabledAll = !!props.disableActions;
                const loadingMap = props.actionsLoading || {};
                const rowLoading = (loadingMap && (loadingMap[String(rowId)] ?? loadingMap[rowId])) ?? null;
                const isLoadingAction = (actionName) => {
                    try {
                        if (!rowLoading) return false;
                        if (typeof rowLoading === 'boolean') return !!rowLoading;
                        if (typeof rowLoading === 'string') return String(rowLoading) === String(actionName);
                        if (Array.isArray(rowLoading)) return rowLoading.map(String).includes(String(actionName));
                        if (typeof rowLoading === 'object') return !!rowLoading[actionName];
                        return false;
                    } catch (_) {
                        return false;
                    }
                };
                const can = (v) => {
                    try {
                        if (typeof v === 'function') return !!v(rowData);
                        return !!v;
                    } catch (_) {
                        return false;
                    }
                };
                const buttons = [
                    can(ac.delete) ? `<button class="btn btn-sm btn-soft-danger" type="button" data-action="delete" data-id="${rowId}" data-row='${rowDataStr}' data-loading="${isLoadingAction('delete') ? 'true' : 'false'}" ${(disabledAll || isLoadingAction('delete')) ? 'disabled' : ''} title="${al.delete ?? 'Excluir'}">${isLoadingAction('delete') ? `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>` : ''}<i class="${ai.delete ?? 'ri-delete-bin-5-fill'} align-bottom"></i>${bt.delete ? `<span class="d-none d-sm-inline ms-1">${bt.delete}</span>` : ''}</button>` : ``,
                    can(ac.edit) ? `<button class="btn btn-sm btn-soft-info" type="button" data-action="edit" data-id="${rowId}" data-row='${rowDataStr}' data-loading="${isLoadingAction('edit') ? 'true' : 'false'}" ${(disabledAll || isLoadingAction('edit')) ? 'disabled' : ''} title="${al.edit ?? 'Editar'}">${isLoadingAction('edit') ? `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>` : ''}<i class="${ai.edit ?? 'ri-pencil-fill'} align-bottom"></i>${bt.edit ? `<span class="d-none d-sm-inline ms-1">${bt.edit}</span>` : ''}</button>` : ``,
                    can(ac.show) ? `<button class="btn btn-sm btn-soft-warning" type="button" data-action="show" data-id="${rowId}" data-row='${rowDataStr}' data-loading="${isLoadingAction('show') ? 'true' : 'false'}" ${(disabledAll || isLoadingAction('show')) ? 'disabled' : ''} title="${al.show ?? 'Visualizar'}">${isLoadingAction('show') ? `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>` : ''}<i class="${ai.show ?? 'ri-eye-fill'} align-bottom"></i>${bt.show ? `<span class="d-none d-sm-inline ms-1">${bt.show}</span>` : ''}</button>` : ``,
                    (can(ac.restore) && rowData.fechado_em) ? `<button class="btn btn-sm btn-soft-primary" type="button" data-action="restore" data-id="${rowId}" data-row='${rowDataStr}' data-loading="${isLoadingAction('restore') ? 'true' : 'false'}" ${(disabledAll || isLoadingAction('restore')) ? 'disabled' : ''} title="${al.restore ?? 'Reabrir'}">${isLoadingAction('restore') ? `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>` : ''}<i class="${ai.restore ?? 'ri-refresh-line'} align-bottom"></i>${bt.restore ? `<span class="d-none d-sm-inline ms-1">${bt.restore}</span>` : ''}</button>` : ``,
                    can(ac.print) ? `<button class="btn btn-sm btn-soft-success" type="button" data-action="print" data-id="${rowId}" data-row='${rowDataStr}' data-loading="${isLoadingAction('print') ? 'true' : 'false'}" ${(disabledAll || isLoadingAction('print')) ? 'disabled' : ''} title="${al.print ?? 'Imprimir'}">${isLoadingAction('print') ? `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>` : ''}<i class="${ai.print ?? 'ri-printer-fill'} align-bottom"></i>${bt.print ? `<span class="d-none d-sm-inline ms-1">${bt.print}</span>` : ''}</button>` : ``,
                    can(ac.download) ? `<button class="btn btn-sm btn-soft-dark" type="button" data-action="download" data-id="${rowId}" data-row='${rowDataStr}' data-loading="${isLoadingAction('download') ? 'true' : 'false'}" ${(disabledAll || isLoadingAction('download')) ? 'disabled' : ''} title="${al.download ?? 'Baixar'}">${isLoadingAction('download') ? `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>` : ''}<i class="${ai.download ?? 'ri-download-line'} align-bottom"></i>${bt.download ? `<span class="d-none d-sm-inline ms-1">${bt.download}</span>` : ''}</button>` : ``,
                    can(ac.receive) ? `<button class="btn btn-sm btn-soft-success" type="button" data-action="receive" data-id="${rowId}" data-row='${rowDataStr}' data-loading="${isLoadingAction('receive') ? 'true' : 'false'}" ${(disabledAll || isLoadingAction('receive')) ? 'disabled' : ''} title="${al.receive ?? 'Receber'}">${isLoadingAction('receive') ? `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>` : ''}<i class="${ai.receive ?? 'ri-money-dollar-box-line'} align-bottom"></i>${bt.receive ? `<span class="d-none d-sm-inline ms-1">${bt.receive}</span>` : ''}</button>` : ``,
                    can(ac.procedure) ? `<button class="btn btn-sm btn-soft-primary" type="button" data-action="procedure" data-id="${rowId}" data-row='${rowDataStr}' data-loading="${isLoadingAction('procedure') ? 'true' : 'false'}" ${(disabledAll || isLoadingAction('procedure')) ? 'disabled' : ''} title="${al.procedure ?? 'Procedimentos'}">${isLoadingAction('procedure') ? `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>` : ''}<i class="${ai.procedure ?? 'ri-list-check'} align-bottom"></i>${bt.procedure ? `<span class="d-none d-sm-inline ms-1">${bt.procedure}</span>` : ''}</button>` : ``,
                    (props.showDiaryButton && can(ac.diary)) ? `<button class="btn btn-sm btn-soft-dark" type="button" data-action="diary" data-id="${rowId}" data-row='${rowDataStr}' data-loading="${isLoadingAction('diary') ? 'true' : 'false'}" ${(disabledAll || isLoadingAction('diary')) ? 'disabled' : ''} title="${al.diary ?? 'Agenda'}">${isLoadingAction('diary') ? `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>` : ''}<i class="${ai.diary ?? 'ri-calendar-2-line'} align-bottom"></i>${bt.diary ? `<span class="d-none d-sm-inline ms-1">${bt.diary}</span>` : ''}</button>` : ``
                ].join('');
                return html(`<div class="d-flex gap-2">${buttons}</div>`);
            }
        });
    }
    gridColumns = gridColumns.map(c => {
        if (c && typeof c.formatter !== 'undefined' && typeof c.formatter !== 'function') {
            delete c.formatter;
        }
        return c;
    });
    // Cria a instância do Grid.js
    const languageBase = {
        pagination: {
            previous: 'Anterior',
            next: 'Próximo',
            showing: 'Exibindo'
        },
        loading: () => html(''),
    };

    const localNoRecords = () => html(`
        <div class="noresult">
            <div class="text-center">
                <div class="lottie-container" style="width:75px;height:75px;margin:0 auto;"></div>
                <h5 class="mt-2">Desculpa! Nenhum registro encontrado</h5>
                <p class="text-muted mb-0">Nós recomendamos utilizar o filtro para refinar melhor sua pesquisa.</p>
            </div>
        </div>
    `);

    const gridConfig = {
        columns: gridColumns,
        pagination: {
            enabled: true,
            limit: limit.value || 10,
        },
        className: {
            table: 'table table-hover mb-0 align-middle',
            thead: 'table-light'
        },
        language: {
            ...languageBase,
            noRecordsFound: localNoRecords,
            error: props.serverUrl ? () => html(`<div class="py-4 text-center text-danger">Erro ao carregar os dados.</div>`) : undefined,
        }
    };

    if (props.serverUrl) {
        gridConfig.server = {
            url: buildServerUrl(0, limit.value || 10, debouncedQuery.value),
            handle: async (res) => {
                if (!res || !res.ok) {
                    let details = '';
                    try { details = await res.text(); } catch (_) {}
                    isLoading.value = false;
                    throw new Error(details || `HTTP ${res?.status || 0}`);
                }
                return res.json();
            },
            then: (resp) => {
                const rows = Array.isArray(resp?.data) ? resp.data : (Array.isArray(resp) ? resp : []);
                lastServerRows.value = rows;
                setTimeout(() => {
                    isLoading.value = false;
                    updateCheckboxes();
                }, 0);
                return rows.map((r) => gridColumns.map((c) => {
                    const key = c?.id;
                    if (!key) return null;
                    if (key === 'select' || key === 'actions') return null;
                    return r?.[key] ?? null;
                }));
            },
            total: (resp) => {
                const t = resp?.total ?? resp?.meta?.total ?? resp?.count ?? null;
                const n = Number(t);
                return Number.isFinite(n) ? n : 0;
            },
        };
        gridConfig.pagination = {
            enabled: true,
            limit: limit.value || 10,
            server: {
                url: (prev, page, limitValue) => {
                    isLoading.value = true;
                    return buildServerUrl((page || 0) * (limitValue || 10), limitValue || 10, debouncedQuery.value);
                },
            },
        };
    } else {
        gridConfig.data = filteredData.value;
    }

    const grid = new Grid(gridConfig);
    grid.render(wrapper.value);
    gridInstance = grid;
    // Listeners para checkboxes e botões de ação
    changeListener = handleCheckboxChange;
    clickListener = (e) => {
        const target = e.target.closest('[data-action]');
        if (!target) return;
        if (target.getAttribute('data-loading') === 'true' || target.hasAttribute('disabled')) return;
        const action = target.getAttribute('data-action');
        const id = target.getAttribute('data-id');
        let rowObj = {};
        try { rowObj = JSON.parse(target.getAttribute('data-row')); } catch (e) {}
        if (action === 'delete') {
            emit('delete', rowObj);
        } else if (action === 'edit') {
            emit('edit', rowObj?.id ?? id, rowObj);
        } else if (action === 'show') {
            emit('show', rowObj?.id ?? id);
        } else if (action === 'diary') {
            emit('diary', rowObj?.id ?? id);
        } else if (action === 'print') {
            emit('print', rowObj?.id ?? id, rowObj);
        } else if (action === 'download') {
            emit('download', rowObj?.id ?? id, rowObj);
        } else if (action === 'restore') {
            emit('restore', rowObj?.id ?? id, rowObj);
        } else if (action === 'receive') {
            emit('receive', rowObj?.id ?? id, rowObj);
        } else if (action === 'procedure') {
            emit('procedure', rowObj?.id ?? id, rowObj);
        }
    };
    wrapper.value.addEventListener('change', changeListener);
    wrapper.value.addEventListener('click', clickListener);
    nextTick(() => {
        updateCheckboxes();
        if (!props.serverUrl) isLoading.value = false;
    });
    observeTableSelectionPersistence();
    // Sempre inicializa o observer para garantir que a animação seja exibida (inclusive no modo server)
    setTimeout(observeLottieContainer, 0);
}

defineExpose({ getSelectedRowIds, getSelectedRowObjects, clearSelection, setSelectedRowIds });

watch(filteredData, () => {
    nextTick(initGrid);
}, { deep: true });
watch(() => props.data, () => {
    nextTick(initGrid);
}, { deep: true });
watch(() => props.serverUrl, () => {
    nextTick(initGrid);
});
watch(() => props.serverQuery, () => {
    nextTick(initGrid);
}, { deep: true });
watch(() => props.actionsLoading, () => {
    nextTick(initGrid);
}, { deep: true });
watch(() => props.disableActions, () => {
    nextTick(initGrid);
});

// Remove listeners ao desmontar o componente
onBeforeUnmount(() => {
    if (wrapper.value && changeListener) {
        wrapper.value.removeEventListener('change', changeListener);
    }
    if (tableObserver) {
        try { tableObserver.disconnect(); } catch (_) {}
        tableObserver = null;
    }
    if (tableObserverTimer) {
        clearTimeout(tableObserverTimer);
        tableObserverTimer = null;
    }
    if (lottieObserver) {
        lottieObserver.disconnect();
        lottieObserver = null;
    }
});

// Inicializa a tabela ao montar o componente
onMounted(async () => {
    await nextTick();
    isLoading.value = true;
    setTimeout(() => {
        initGrid();
        setTimeout(observeLottieContainer, 0);
    }, 100);
});
</script>

<template>
    <!-- Tabela -->
    <div class="row">
        <div :class="props.compactSpacing ? 'card' : 'card card-body'">
            <div :class="['card-body', props.compactSpacing ? 'px-0 pt-0' : 'px-0']">
                <h5 :class="['card-title','mb-0','flex-grow-1', props.compactSpacing ? 'mb-1' : 'mb-3']">{{ props.tableTitle }}</h5>
                <!-- Filtros -->
                <BCardBody class="border border-dashed border-end-0 border-start-0 px-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="search-box" style="width: 300px;" v-if="props.search">
                            <input type="text" name="table_search" autocomplete="on" class="form-control search" :placeholder="searchPlaceholder" v-model="searchQuery" />
                            <i class="ri-search-line search-icon"></i>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <button id="deleteMulti" @click="emit('modalDdeletarMultiplos', selectedRows)" v-if="props.showMultiDelete && selectedRows.length > 0 && props.showCheckbox"
                                type="button"
                                class="btn btn-danger btn-icon waves-effect waves-light"
                            >
                                <i class="ri-delete-bin-5-line"></i>
                            </button>
                            <div v-if="showPerPagination" class="d-flex align-items-center">
                                <span class="text-muted text-nowrap me-2">Exibir:</span>
                                <Multiselect
                                    class="form-control text-nowrap"
                                    style="width: 100px;"
                                    name="perPagination"
                                    id="perPagination"
                                    v-model="limit"
                                    :options="[
                                        { value: 10, label: '10' },
                                        { value: 20, label: '20' },
                                        { value: 50, label: '50' },
                                        { value: 100, label: '100' }
                                    ]"
                                    :canClear="false"
                                    :searchable="false"
                                />
                            </div>
                            <button v-if="props.showAddButton" type="button" class="btn btn-success btn-label waves-effect waves-light" @click="emit('add')" :disabled="props.addButtonDisabled">
                                <i :class="`${props.addButtonIconClass} label-icon align-middle fs-16 me-2`"></i> {{ props.addButtonText }}
                            </button>
                        </div>
                    </div>
                </BCardBody>

                <!-- Loader enquanto a tabela está sendo construída -->
                <div v-if="isLoading" class="d-flex justify-content-center align-items-center py-5">
                    <div class="text-center">
                        <LottieComponent :options="{ animationData: animationCube, loop: true, autoplay: true }" :height="75" :width="75" />
                        <h5 class="mt-2">Aguarde! Carregando...</h5>
                        <p class="text-muted mb-0">
                            Estamos trabalhando para trazer os dados.
                        </p>
                    </div>
                </div>

                <!-- Container da tabela -->
                <div v-show="!isLoading" ref="wrapper" :class="['table-card','table-responsive', props.compactSpacing ? 'mt-2' : 'mt-3','px-3']"></div>
            </div>
        </div>
    </div>
</template>

<style>
.gridjs-loading {
  display: none !important;
}

.table-responsive table {
  table-layout: auto !important;
  width: 100% !important;
}

.table th, .table td {
  max-width: 192px !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  white-space: nowrap !important;
  vertical-align: middle !important;
}
.table td {
  padding: 0.25rem 0.5rem !important;
}
.table tbody td:last-child {
  width: 1%;
  max-width: none !important;
  overflow: visible !important;
  text-overflow: initial !important;
}
</style>
