<script setup>
//-------------------- IMPORTAÇÕES --------------------
import { onMounted, ref, nextTick, watch } from 'vue';
import { Grid, html } from 'gridjs';
import 'gridjs/dist/theme/mermaid.css';
import Lottie from 'lottie-web';
import animationData from "@/Components/widgets/msoeawqm.json";
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';

//-------------------- PROPS --------------------
// Define as propriedades que o componente pode receber
const props = defineProps({
    modal: String,
    showCheckbox: { type: Boolean, default: true },     // Controla se exibe checkbox nas linhas
    showAddButton: { type: Boolean, default: true },    // Controla se exibe botão de adicionar
    showStatus: { type: Boolean, default: false },      // Controla se exibe coluna de status
    columns: { type: Array, default: () => ["ID", "Nome", "Email", "Cargo", "Empresa", "País"] }, // Colunas da tabela
    data: { type: [Array, Object, null], default: () => [] }, // Dados da tabela
    paginationLimit: { type: Number, default: 10 },     // Limite de itens por página
    search: { type: Boolean, default: true },           // Controla se exibe campo de busca
    showActions: { type: Boolean, default: true },      // Controla se exibe botões de ação
    searchPlaceholder: { type: String, default: 'Buscar...' } // Placeholder do campo de busca
});

//-------------------- EMITS --------------------
// Define os eventos que o componente pode emitir
const emit = defineEmits([
    'abrir-modal-adicionar',   // Evento para abrir modal de adicionar
    'abrir-modal-deletar-varios', // Evento para deletar múltiplos itens
    'delete-item',             // Evento para deletar um item
    'edit-item',               // Evento para editar um item
    'show-item'                // Evento para visualizar um item
]);

//-------------------- REFS --------------------
// Referencias reativas para elementos e estados
const wrapper = ref(null);         // Referência para o elemento wrapper da tabela
const sortValue = ref('');         // Valor atual da ordenação
const gridInstance = ref(null);    // Instância do Grid.js

//-------------------- WATCH --------------------
// Observa mudanças no valor de ordenação
watch(sortValue, (newValue) => {
    if (gridInstance.value) {
        const columnIndex = props.columns.findIndex(col => col === newValue);
        if (columnIndex !== -1) {
            gridInstance.value.sort(columnIndex, 'asc');
        }
    }
});

//-------------------- FUNÇÕES PRINCIPAIS --------------------

/**
 * Função principal para renderizar a tabela
 * Inicializa o Grid.js e configura todos os listeners necessários
 */
async function renderGrid() {
    console.log('Dados recebidos:', props.data);

    // Verifica se existem dados válidos para renderizar
    if (!props.data || !Array.isArray(props.data) || props.data.length === 0) {
        console.warn("Nenhum dado para renderizar.");
        return;
    }

    // Limpa instância anterior se existir
    if (gridInstance.value) {
        gridInstance.value.destroy();
    }

    try {
        if (!wrapper.value) {
            console.error("Elemento wrapper não encontrado");
            return;
        }

        await nextTick();

        // Configura os event listeners dos botões
        setupEventListeners();

        // Configuração do Grid.js
        const grid = new Grid({
            className: {
                table: 'table align-middle table-nowrap',
                thead: 'table-light text-muted',
                tbody: 'list form-check-all'
            },
            checkbox: true,
            columns: buildColumns(),
            data: prepareData(),
            pagination: {
                limit: props.paginationLimit,
                summary: true
            },
            search: props.search,
            sort: true,
            language: {
                search: { placeholder: props.searchPlaceholder },
                pagination: {
                    previous: "Anterior",
                    next: "Próximo",
                    showing: "Exibindo",
                    results: () => "registros"
                },
                loading: 'Carregando...',
                noRecordsFound: 'Nenhum registro encontrado'
            }
        });

        // Renderiza a tabela e salva a instância
        grid.render(wrapper.value);
        gridInstance.value = grid;

        // Configura os event listeners após a renderização
        await nextTick();
        setupHeaderUI();
        setupSelectListener();
        setupCheckboxLogic();

        // Observer para customizar a mensagem de "não encontrado"
        const observer = new MutationObserver((mutations) => {
            const notFoundElement = wrapper.value?.querySelector('.gridjs-notfound');
            if (notFoundElement && !notFoundElement.dataset.customized) {
                notFoundElement.innerHTML = `
                    <div class="noresult">
                        <div class="text-center">
                            <div class="lottie-container" style="width:75px;height:75px;margin:0 auto;"></div>
                            <h5 class="mt-2">Desculpa! Nenhum registro encontrado</h5>
                            <p class="text-muted mb-0">
                                Nós recomendamos utilizar o filtro para refinar melhor sua pesquisa.
                            </p>
                        </div>
                    </div>`;
                notFoundElement.dataset.customized = 'true';

                // Inicializa a animação Lottie
                const container = notFoundElement.querySelector('.lottie-container');
                if (container) {
                    Lottie.loadAnimation({
                        container,
                        renderer: 'svg',
                        loop: true,
                        autoplay: true,
                        animationData,
                    });
                }
            }
        });

        observer.observe(wrapper.value, { childList: true, subtree: true });

        // Adiciona listener para reconfigurar eventos após mudanças na tabela
        grid.on('rendered', () => {
            setupEventListeners();
        });

    } catch (error) {
        console.error("Erro ao renderizar a tabela:", error);
    }
}

/**
 * Configura os event listeners para os botões de ação
 * Gerencia eventos de delete, edit e show
 */
function setupEventListeners() {
    if (wrapper.value) {
        // Remove event listeners antigos para evitar duplicação
        const oldWrapper = wrapper.value.cloneNode(true);
        wrapper.value.parentNode.replaceChild(oldWrapper, wrapper.value);
        wrapper.value = oldWrapper;

        // Adiciona listener usando delegação de eventos
        wrapper.value.addEventListener('click', (e) => {
            const actionHandlers = {
                'delete': (id) => emit('delete-item', id),
                'edit': (id) => emit('edit-item', id),
                'show': (id) => emit('show-item', id)
            };

            // Verifica qual botão foi clicado e emite o evento correspondente
            for (const [action, handler] of Object.entries(actionHandlers)) {
                const button = e.target.closest(`[data-action="${action}"]`);
                if (button) {
                    handler(button.getAttribute('data-id'));
                    return;
                }
            }
        });
    }
}

/**
 * Configura o listener para o select de itens por página
 * Gerencia a mudança na quantidade de itens exibidos
 */
function setupSelectListener() {
    const showItemPerPageTable = document.getElementById('showItemPerPageTable');

    if (showItemPerPageTable) {
        // Remove event listeners antigos
        const newElement = showItemPerPageTable.cloneNode(true);
        showItemPerPageTable.parentNode.replaceChild(newElement, showItemPerPageTable);

        // Adiciona novo listener
        newElement.addEventListener('change', async function (e) {
            const newLimit = parseInt(e.target.value, 10);
            if (gridInstance.value) {
                try {
                    // Atualiza a configuração da tabela
                    gridInstance.value.updateConfig({
                        pagination: {
                            limit: newLimit
                        }
                    });

                    // Re-renderiza a tabela
                    await gridInstance.value.forceRender();
                    await nextTick();

                    // Reconfigura os elementos UI
                    setupHeaderUI();
                    setupSelectListener();
                    setupCheckboxLogic();
                } catch (error) {
                    console.error('Erro ao atualizar paginação:', error);
                }
            }
        });
    }
}

// Lifecycle Hooks
onMounted(async () => {
    try {
        if (!props.data || (Array.isArray(props.data) && props.data.length === 0)) {
            console.warn("Nenhum dado para montar a tabela.");
            return;
        }
        await renderGrid();
    } catch (error) {
        console.error("Erro no onMounted:", error);
    }
});

// Funções auxiliares
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

function buildColumns() {
    if (!props.columns || !Array.isArray(props.columns)) {
        console.warn('Colunas inválidas fornecidas, usando colunas padrão');
        return ['ID', 'Nome', 'Email', 'Cargo', 'Empresa', 'País'];
    }

    const finalColumns = [];

    if (props.showCheckbox) {
        finalColumns.push({
            id: 'select',
            name: html(`<input class="form-check-input" type="checkbox" id="checkAll" />`),
            sort: false,
            formatter: (_, row) => {
                if (!row || !row.cells || !row.cells[1]?.data) {
                    return html(`<input class="form-check-input" type="checkbox" name="chk_child" value="" />`);
                }
                return html(`
                    <input class="form-check-input" type="checkbox" name="chk_child" value="${row.cells[1].data}" />
                `);
            }
        });
    }

    finalColumns.push(...props.columns);

    if (props.showStatus) {
        finalColumns.push({
            id: 'status',
            name: 'Status',
            sort: false,
            formatter: (cell) => getStatusBadge(cell)
        });
    }

    if (props.showActions) {
        finalColumns.push({
            id: 'actions',
            name: 'Ações',
            sort: false,
            attributes: { style: 'min-width: 7rem; width: 7rem;' },
            formatter: (_, row) => {
                if (!row || !row.cells || !row.cells[1]?.data) {
                    return html(`<td class="gap-2 d-inline-flex"></td>`);
                }
                return html(`
                    <td class="gap-2 d-inline-flex">
                        <button class="btn btn-sm btn-soft-danger" type="button" data-action="delete" data-id="${row.cells[1].data}">
                            <i class="ri-delete-bin-5-fill align-bottom"></i>
                        </button>
                        <button class="btn btn-sm btn-soft-info" type="button" data-action="edit" data-id="${row.cells[1].data}">
                            <i class="ri-pencil-fill align-bottom"></i>
                        </button>
                        <button class="btn btn-sm btn-soft-warning" type="button" data-action="show" data-id="${row.cells[1].data}">
                            <i class="ri-eye-fill align-bottom"></i>
                        </button>
                    </td>
                `);
            }
        });
    }

    return finalColumns;
}

function prepareData() {
    if (!props.data) {
        console.warn('Nenhum dado fornecido');
        return [];
    }

    if (!Array.isArray(props.data)) {
        console.warn('Dados não são um array');
        return [];
    }

    // Garante que cada linha é um array válido
    return props.data.map(row => {
        // Se a linha não for um array, converte para array
        if (!Array.isArray(row)) {
            row = [row];
        }

        // Se showCheckbox estiver ativado, adiciona o checkbox no início
        if (props.showCheckbox) {
            return [null, ...row];
        }

        return row;
    }).filter(row => {
        // Filtra linhas inválidas
        return Array.isArray(row) && row.length > 0;
    });
}

function setupHeaderUI() {
    const search = wrapper.value?.querySelector('.gridjs-search');
    const parent = search?.parentNode;

    if (search && parent) {
        // Remove wrapper anterior se existir
        const existingWrapper = document.querySelector('.custom-header-wrapper');
        if (existingWrapper) {
            existingWrapper.remove();
        }

        const layoutWrapper = document.createElement('div');
        layoutWrapper.className = 'd-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center card-header px-0 border-0 gap-2 custom-header-wrapper';

        parent.insertBefore(layoutWrapper, search);

        const buttonGroup = document.createElement('div');
        buttonGroup.className = 'hstack gap-2 border-0 order-0 order-sm-1';

        // Preserva o valor selecionado do select
        const currentSelect = document.getElementById('showItemPerPageTable');
        const currentValue = currentSelect ? currentSelect.value : '10';

        buttonGroup.innerHTML = `
            ${props.showCheckbox ? `
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted text-nowrap">Exibir:</span>
                <select class="form-select" style="width:80px;" id="showItemPerPageTable">
                    <option value="10" ${currentValue === '10' ? 'selected' : ''}>10</option>
                    <option value="20" ${currentValue === '20' ? 'selected' : ''}>20</option>
                    <option value="50" ${currentValue === '50' ? 'selected' : ''}>50</option>
                    <option value="100" ${currentValue === '100' ? 'selected' : ''}>100</option>
                </select>
            </div>
                <a class="btn btn-md btn-soft-danger" type="button" id="remove-actions" style="display: none;">
                    <i class="ri-delete-bin-2-line"></i>
                </a>` : ''}
            ${props.showAddButton ? `
                <a class="btn btn-md btn-success d-flex" type="button" id="create-btn">
                    <i class="ri-add-line align-bottom me-1"></i>
                    <span class="d-none d-sm-inline">Adicionar</span>
                </a>` : ''}
        `;

        layoutWrapper.appendChild(buttonGroup);
        layoutWrapper.appendChild(search);
        search.classList.add('order-1', 'order-sm-0', 'w-100');

        setupHeaderButtons(buttonGroup);
    }
}

function setupHeaderButtons(buttonGroup) {
    const addButton = buttonGroup.querySelector('#create-btn');
    const deleteMultiButton = buttonGroup.querySelector('#remove-actions');

    if (addButton) {
        addButton.addEventListener('click', () => emit('abrir-modal-adicionar'));
    }

    if (deleteMultiButton) {
        deleteMultiButton.addEventListener('click', () => {
            const selected = wrapper.value.querySelectorAll('input[name="chk_child"]:checked');
            const ids = Array.from(selected).map(cb => cb.value);
            emit('abrir-modal-deletar-varios', ids);
        });
    }
}

function setupCheckboxLogic() {
    setTimeout(() => {
        const checkAll = wrapper.value?.querySelector('#checkAll');
        const removeActions = document.getElementById('remove-actions');

        wrapper.value?.addEventListener('change', (event) => {
            const target = event.target;

            if (target.id === 'checkAll') {
                handleCheckAll(target, removeActions);
            }

            if (target.name === 'chk_child') {
                handleIndividualCheckbox(target, checkAll, removeActions);
            }
        });
    }, 100);
}

function handleCheckAll(target, removeActions) {
    const checkboxes = wrapper.value?.querySelectorAll('input[name="chk_child"]') || [];
    checkboxes.forEach(cb => {
        cb.checked = target.checked;
        cb.closest("tr")?.classList.toggle("table-active", target.checked);
    });
    if (removeActions) {
        removeActions.style.display = target.checked ? 'block' : 'none';
    }
}

function handleIndividualCheckbox(target, checkAll, removeActions) {
    const checkedCount = wrapper.value.querySelectorAll('input[name="chk_child"]:checked').length;
    const totalCheckboxes = wrapper.value.querySelectorAll('input[name="chk_child"]').length;

    if (removeActions) {
        removeActions.style.display = checkedCount > 0 ? 'block' : 'none';
    }
    if (checkAll) {
        checkAll.checked = (checkedCount === totalCheckboxes && totalCheckboxes > 0);
    }
    target.closest("tr")?.classList.toggle("table-active", target.checked);
}
</script>

<template>
    <div class="row">
        <div class="card card-body">
            <div class="card-body">
                <div ref="wrapper" class="table-responsive table-card"></div>
            </div>
        </div>
    </div>
</template>
