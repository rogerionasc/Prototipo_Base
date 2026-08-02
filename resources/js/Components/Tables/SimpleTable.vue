<script setup>
import { computed, ref, useSlots } from 'vue';

const props = defineProps({
    title: { type: String, default: '' },
    items: { type: Array, required: true },
    columns: { type: Array, required: true },
    searchable: { type: Boolean, default: false },
    searchPlaceholder: { type: String, default: 'Buscar...' },
    searchFields: { type: Array, default: () => [] },
    emptyTitle: { type: String, default: 'Nenhum registro encontrado' },
    emptyMessage: { type: String, default: 'Não há dados para exibir no momento.' },
    emptyIcon: { type: String, default: 'ri-file-list-3-line' },
    rowClass: { type: Function, default: () => '' },
    variant: { type: String, default: 'card' }, // 'card' or 'borderless'
    compact: { type: Boolean, default: false },
    tableClass: { type: String, default: 'table-borderless' },
    hasActions: { type: Boolean, default: false },
    actions: { type: Array, default: () => [] },
    actionsLabel: { type: String, default: 'Ações' }
});

const emit = defineEmits(['action']);

const searchQuery = ref('');

const filteredItems = computed(() => {
    if (!props.searchable || !searchQuery.value || props.searchFields.length === 0) {
        return props.items;
    }
    
    const query = searchQuery.value.toLowerCase();
    
    return props.items.filter(item => {
        return props.searchFields.some(field => {
            // Suporte para campos aninhados como 'paciente.nome'
            const value = field.split('.').reduce((o, i) => (o ? o[i] : null), item);
            return value && String(value).toLowerCase().includes(query);
        });
    });
});

const slots = useSlots();
</script>

<template>
    <div :class="{ 'card border-0 shadow-sm': variant === 'card' }">
        <div :class="{ 'card-header bg-white border-0': variant === 'card', 'mb-3': variant === 'borderless' }" v-if="title || searchable || slots['header-actions']">
            <div class="row g-4 align-items-center">
                <div class="col-sm" v-if="title">
                    <h5 :class="{ 'card-title mb-0': variant === 'card', 'fs-15 mb-0 fw-semibold text-dark': variant === 'borderless' }">{{ title }}</h5>
                </div>
                <div class="col-sm-auto ms-auto" v-if="searchable || slots['header-actions']">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="search-box" v-if="searchable" style="width: 300px;">
                            <input type="text" autocomplete="on" class="form-control search" :placeholder="searchPlaceholder" v-model="searchQuery">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                        <slot name="header-actions"></slot>
                    </div>
                </div>
            </div>
        </div>
        
        <div :class="{ 'card-body': variant === 'card', 'pt-3': variant === 'card' && (title || searchable || slots['header-actions']) }">
            <slot name="top"></slot>

            <div class="table-responsive" :class="{ 'table-card': variant === 'card', 'mb-1': true }">
                <table class="table table-nowrap align-middle mb-0" :class="[tableClass, { 'table-sm': compact }]">
                    <thead class="table-light text-muted">
                        <tr>
                            <th v-for="(col, index) in columns" :key="index" :style="{ width: col.width || 'auto' }" :class="col.thClass || ''">
                                {{ col.label }}
                            </th>
                            <th v-if="hasActions" style="width: 120px;" class="text-end">{{ actionsLabel }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <slot name="body" :items="filteredItems" :columns="columns">
                            <tr v-for="(item, index) in filteredItems" :key="item.id || index" :class="rowClass(item)">
                                <td v-for="(col, colIndex) in columns" :key="colIndex" :class="col.tdClass || ''">
                                    <slot :name="'cell(' + col.key + ')'" :item="item" :index="index">
                                        {{ item[col.key] }}
                                    </slot>
                                </td>
                                <td v-if="hasActions" class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <slot name="actions" :item="item" :index="index">
                                            <button v-for="(action, actionIndex) in actions" :key="actionIndex" 
                                                class="btn btn-sm" :class="action.class || 'btn-soft-secondary'"
                                                :title="action.label"
                                                @click="$emit('action', action.event, item)">
                                                <i v-if="action.icon" :class="action.icon"></i>
                                                <span v-if="action.showLabel" :class="{'ms-1': action.icon}">{{ action.label }}</span>
                                            </button>
                                        </slot>
                                    </div>
                                </td>
                            </tr>
                        </slot>

                        <tr v-if="filteredItems.length === 0">
                            <td :colspan="hasActions ? columns.length + 1 : columns.length">
                                <div class="text-center py-4">
                                    <div class="avatar-md mx-auto mb-3">
                                        <div class="avatar-title bg-light text-primary rounded-circle fs-24">
                                            <i :class="emptyIcon"></i>
                                        </div>
                                    </div>
                                    <h5 class="mt-2">{{ emptyTitle }}</h5>
                                    <p class="text-muted" v-if="searchQuery && filteredItems.length === 0">A busca não retornou resultados.</p>
                                    <p class="text-muted" v-else>{{ emptyMessage }}</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
