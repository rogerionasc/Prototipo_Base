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
    data: { type: Array, default: () => [] }, // Dados da tabela
    columns: { type: Array, default: () => ["ID", "Nome", "Email", "Cargo", "Empresa", "País"] }, // Colunas
    showStatus: { type: Boolean, default: true }, // Exibe coluna de status
    search: { type: Boolean, default: true }, // Exibe campo de busca
    searchPlaceholder: { type: String, default: 'Buscar...' }, // Placeholder do campo de busca
    showCheckbox: { type: Boolean, default: true }, // Exibe checkbox nas linhas
    showAddButton: { type: Boolean, default: true }, // Exibe botão de adicionar
    showActions: { type: Boolean, default: true }, // Exibe botões de ação
    showPerPagination: { type: Boolean, default: true }, // Exibe seleção de itens por página
    tableTitle: { type: String, default: 'Listas ...' }, // Título da tabela
});

// -------------------- EMITS --------------------
// Define os eventos que o componente pode emitir
const emit = defineEmits([
    'add',   // Evento para abrir modal de adicionar
    'modalDdeletarMultiplos', // Evento para deletar múltiplos itens
    'delete',             // Evento para deletar um item
    'edit',               // Evento para editar um item
    'show'                // Evento para visualizar um item
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
const filteredData = computed(() => filterData(props.data, debouncedQuery.value));

// -------------------- ANIMAÇÃO LOTTIE --------------------
// Observa o container da animação Lottie e inicializa quando necessário
function observeLottieContainer() {
    const observer = new MutationObserver(() => {
        const container = document.querySelector('.lottie-container');
        if (container && !container.dataset.lottieInitialized) {
            container.dataset.lottieInitialized = 'true';
            Lottie.loadAnimation({
                container,
                renderer: 'svg',
                loop: true,
                autoplay: true,
                animationData,
            });
            observer.disconnect();
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
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

    const status = (cell || '').toLowerCase();
    const { class: badgeClass, text: badgeText } = statusMap[status] || {
        class: 'bg-light text-dark',
        text: cell || 'Desconhecido'
    };

    return html(`<span class="badge ${badgeClass}">${badgeText}</span>`);
};

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
    if (gridInstance) {
        gridInstance.destroy();
        gridInstance = null;
    }
    isLoading.value = true;
    let gridColumns;
    // Monta as colunas da tabela, incluindo checkbox se necessário
    if (props.showCheckbox) {
        gridColumns = [
            {
                id: 'select',
                name: html(`<input type="checkbox" class="form-check-input" data-check-all="true" />`),
                formatter: (cell, row) => {
                    const rowId = row.cells[1].data;
                    return html(`<input type="checkbox" class="form-check-input" data-row-id="${rowId}" ${selectedRows.value.includes(rowId) ? 'checked' : ''} />`);
                }
            },
            ...props.columns.map((col, idx) => {
                if (typeof col === 'object') {
                    return {
                        id: col.id ? String(col.id) : String(col.name).toLowerCase(),
                        name: col.name,
                        sort: true
                    };
                } else {
                    return {
                        id: String(col).toLowerCase(),
                        name: col,
                        sort: true
                    };
                }
            })
        ];
    } else {
        gridColumns = props.columns.map((col, idx) => {
            if (typeof col === 'object') {
                return {
                    id: col.id ? String(col.id) : String(col.name).toLowerCase(),
                    name: col.name,
                    sort: true
                };
            } else {
                return {
                    id: String(col).toLowerCase(),
                    name: col,
                    sort: true
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
                const rowId = row.cells[1].data;
                // Captura o objeto completo da linha
                const rowData = filteredData.value.find(r => {
                    let idCol;
                    if (props.columns[0] && typeof props.columns[0] === 'object') {
                        idCol = props.columns[0].id || props.columns[0].name;
                    } else {
                        idCol = props.columns[0];
                    }
                    if (!idCol) return false;
                    return String(r[idCol]) === String(rowId) || String(r.id) === String(rowId);
                }) || {};
                // Passa o objeto completo no evento
                return html(`
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-soft-danger" type="button" data-action="delete" data-id="${rowId}" data-row='${JSON.stringify(rowData)}' title="Excluir"><i class="ri-delete-bin-5-fill align-bottom"></i></button>
                        <button class="btn btn-sm btn-soft-info" type="button" data-action="edit" data-id="${rowId}" title="Editar"><i class="ri-pencil-fill align-bottom"></i></button>
                        <button class="btn btn-sm btn-soft-warning" type="button" data-action="show" data-id="${rowId}" title="Visualizar"><i class="ri-eye-fill align-bottom"></i></button>
                    </div>
                `);
            }
        });
    }
    // Cria a instância do Grid.js
    const grid = new Grid({
        columns: gridColumns,
        data: filteredData.value,
        pagination: {
            enabled: true,
            limit: limit.value || 10,
        },
        className: {
            table: 'table table-hover mb-0',
            thead: 'table-light'
        },
        language: {
            noRecordsFound: () => html(`
                <div class="noresult">
                    <div class="text-center">
                        <div class="lottie-container" style="width:75px;height:75px;margin:0 auto;"></div>
                        <h5 class="mt-2">Desculpa! Nenhum registro encontrado</h5>
                        <p class="text-muted mb-0">Nós recomendamos utilizar o filtro para refinar melhor sua pesquisa.</p>
                    </div>
                </div>
            `),
            pagination: {
                previous: 'Anterior',
                next: 'Próximo',
                showing: 'Exibindo'
            }
        }
    });
    grid.render(wrapper.value);
    gridInstance = grid;
    // Listeners para checkboxes e botões de ação
    changeListener = handleCheckboxChange;
    clickListener = (e) => {
        const target = e.target.closest('[data-action]');
        if (!target) return;
        const action = target.getAttribute('data-action');
        const id = target.getAttribute('data-id');
        // Recupera o objeto completo da linha se for delete
        if(action === 'delete') {
            let rowObj = {};
            try {
                rowObj = JSON.parse(target.getAttribute('data-row'));
            } catch (e) {}
            emit('delete', rowObj);
        }
        else if(action === 'edit') emit('edit', id);
        else if(action === 'show') emit('show', id);
    };
    wrapper.value.addEventListener('change', changeListener);
    wrapper.value.addEventListener('click', clickListener);
    nextTick(() => {
        updateCheckboxes();
        setTimeout(() => isLoading.value = false, 300);
    });
    // Exibe animação Lottie se não houver dados
    if (filteredData.value.length === 0) {
        setTimeout(observeLottieContainer, 0);
    }
}

// Observa mudanças nos dados filtrados e reinicializa a tabela
watch([filteredData], () => {
    nextTick(initGrid);
});

// Remove listeners ao desmontar o componente
onBeforeUnmount(() => {
    if (wrapper.value && changeListener) {
        wrapper.value.removeEventListener('change', changeListener);
    }
});

// Inicializa a tabela ao montar o componente
onMounted(async () => {
    await nextTick();
    isLoading.value = true;
    setTimeout(() => {
        initGrid();
        setTimeout(() => {
            // Inicializa animação LottieCube
            const container = document.querySelector('.lottie-animationCube');
            if (container && !container.dataset.lottieInitialized) {
                container.dataset.lottieInitialized = 'true';
                Lottie.loadAnimation({
                    container,
                    renderer: 'svg',
                    loop: true,
                    autoplay: true,
                    animationData: animationCube,
                });
            }
        }, 0);
    }, 100);
});
</script>

<template>
    <!-- Tabela -->
    <div class="row">
        <div class="card card-body">
            <div class="card-body px-0">
                <h5 class="card-title mb-0 flex-grow-1 mb-3">{{ props.tableTitle }}</h5>
                <!-- Filtros -->
                <BCardBody class="border border-dashed border-end-0 border-start-0 px-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="search-box" style="width: 300px;" v-if="props.search">
                            <input type="text" class="form-control search" :placeholder="searchPlaceholder" v-model="searchQuery" />
                            <i class="ri-search-line search-icon"></i>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <button id="deleteMulti" @click="emit('modalDdeletarMultiplos', selectedRows)" v-if="selectedRows.length > 0 && props.showCheckbox"
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
                            <button v-if="props.showAddButton" type="button" class="btn btn-success btn-label waves-effect waves-light" @click="emit('add')"><i
                                    class=" ri-add-fill label-icon align-middle fs-16 me-2"></i> Adicionar</button>
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

                <!-- Container da tabela (só aparece quando o loader some) -->
                <div v-show="!isLoading" ref="wrapper" class="table-card table-responsive mt-3 px-3"></div>
            </div>
        </div>
    </div>
</template>

<style>
.table-responsive table {
  table-layout: auto !important;
  width: 100% !important;
}

/* Limita a largura das colunas e adiciona reticências */
.table th, .table td {
  max-width: 192px !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  white-space: nowrap !important;
  /* word-break: break-all !important; */
  /* Removido white-space: normal e word-break para evitar conflito */
}
</style>
