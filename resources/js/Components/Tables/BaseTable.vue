<template>
    <div class="flex justify-between space-x-4 items-center">
        <div class="text-2xl font-semibold leading-6 text-gray-900" :class="tableTitleClass">
            <slot name="title">
                {{ title }}
            </slot>
        </div>
        <div>
            <slot name="action"></slot>
        </div>
    </div>

    <div class="mt-3 flex w-full justify-between items-center">
        <slot name="filters" />
        <slot v-if="showSearch" name="search">
            <div class="w-[250px]">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                        <FontAwesomeIcon icon="fa-solid fa-magnifying-glass" />
                    </span>
                    <input
                        :placeholder="searchPlaceHolder"
                        :value="searchValue"
                        @input="$emit('search', $event.target.value)"
                        type="search"
                        class="pl-9 pr-3 py-2 w-full border border-gray-300 rounded-lg text-sm text-gray-800 bg-white focus:border-blue-500 focus:ring-blue-500"
                    />
                    <span v-if="isSearching" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500">
                        <FontAwesomeIcon icon="fa-solid fa-rotate-right" class="animate-spin" />
                    </span>
                </div>
            </div>
        </slot>
    </div>

    <div class="relative shadow sm:rounded-lg mt-3" :class="baseTableClass">
        <table class="min-w-full border rounded-[10px] overflow-hidden text-[14px]" :class="tableClass">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50" :class="headerClass">
                <tr class="bg-gray-200">
                    <th v-if="showSerial" class="py-3 px-4 border w-10 text-left">SL</th>
                    <th v-for="(column, index) in columns" :key="index" class="py-3 px-4 text-left border" :class="column.headerClass">
                        {{ column.label }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="data.length === 0">
                    <td class="px-3 py-2 border text-center font-bold" :colspan="columns.length + 1">
                        No data found!
                    </td>
                </tr>
                <tr
                    v-for="(row, rowIndex) in data"
                    :key="row.id || rowIndex"
                    @click="$emit('rowClick', row)"
                    class="bg-white hover:bg-gray-100"
                    :class="{
                        'cursor-pointer': rowClickable,
                        '!bg-gray-100': rowIndex % 2 !== 0,
                    }"
                >
                    <td v-if="showSerial" class="px-3 !py-1">
                        <slot name="serial" :index="rowIndex">
                            {{ (currentPage - 1) * Number(pagination.perPage) + rowIndex + 1 }}
                        </slot>
                    </td>
                    <td
                        v-for="(column, colIndex) in columns"
                        :key="colIndex"
                        class="px-3 !py-1"
                        :class="column.cellClass"
                    >
                        <template v-if="!column.component && column.key">
                            {{ row[column.key] }}
                        </template>
                        <template v-else-if="column.component">
                            <component :is="column.component" :data="row" />
                        </template>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div v-if="totalPages" class="flex mt-5 items-center w-full relative gap-4" :class="basePaginationClass">
        <div>
            <select
                v-model="pagination.perPage"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white text-gray-800 focus:border-blue-500 focus:ring-blue-500"
                @change="$emit('updatePerPage', pagination.perPage)"
            >
                <option v-for="opt in perPageOptions" :key="opt.value" :value="opt.value">{{ opt.name }}</option>
            </select>
        </div>
        <div class="flex w-full justify-center items-center gap-2" :class="paginationClass">
            <button
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white text-gray-800 disabled:bg-gray-100 disabled:text-gray-400"
                :disabled="currentPage <= 1"
                @click="$emit('pageChanged', currentPage - 1)"
            >
                Prev
            </button>
            <span class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white text-gray-800">
                {{ currentPage }}
            </span>
            <button
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white text-gray-800 disabled:bg-gray-100 disabled:text-gray-400"
                :disabled="!totalPages || currentPage >= totalPages"
                @click="$emit('pageChanged', currentPage + 1)"
            >
                Next
            </button>
        </div>
    </div>
</template>

<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { reactive } from 'vue';

defineProps({
    title: { type: String, default: 'Table Title' },
    columns: { type: Array, default: () => [] },
    data: { type: Array, default: () => [] },
    baseTableClass: { type: String, default: 'overflow-x-auto' },
    tableClass: { type: String, default: '' },
    tableTitleClass: { type: String, default: '' },
    headerClass: { type: String, default: '' },
    rowClickable: { type: Boolean, default: false },
    showSerial: { type: Boolean, default: true },
    searchPlaceHolder: { type: String, default: 'Search' },
    searchClass: { type: String, default: '' },
    searchValue: { type: String, default: '' },
    isSearching: { type: Boolean, default: false },
    totalPages: { type: Number, default: undefined },
    currentPage: { type: Number, default: 1 },
    showSearch: { type: Boolean, default: false },
    basePaginationClass: { type: String, default: '' },
    paginationClass: { type: String, default: '' },
});

const pagination = reactive({ perPage: '25' });

const perPageOptions = [
    { value: '25', name: '25' },
    { value: '50', name: '50' },
    { value: '75', name: '75' },
    { value: '100', name: '100' },
];

defineEmits(['rowClick', 'search', 'updatePerPage', 'pageChanged']);
</script>
