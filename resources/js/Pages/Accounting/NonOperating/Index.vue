<template>
    <Head title="Non-Operating" />
    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">Non-Operating Income & Expenses</h1>
                <p class="text-sm text-gray-500">Period: {{ period.name }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <select class="px-4 py-2 border border-gray-200 rounded-full text-sm font-medium bg-white text-gray-700 shadow-sm focus:ring-2 focus:ring-[#1e5b43] outline-none">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                        <a :href="route('accounting.non-operating.report')" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-excel text-green-600"></i>
                            Excel
                        </a>
                        <a :href="route('accounting.non-operating.report', { type: 'pdf' })" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf text-red-600"></i>
                            PDF
                        </a>
                    </div>
                </div>

            <!-- Net Non-Operating Display -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(30,91,67,0.1)] border-2 border-[#1e5b43]/20 overflow-hidden mb-6">
                <div class="bg-[#1e5b43]/5 border-b border-[#1e5b43]/10 px-6 py-4">
                    <h2 class="text-xl font-bold text-[#1e5b43]">Net Non-Operating Profit/Loss</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white rounded-[20px] shadow-[0_2px_8px_rgba(0,0,0,0.02)] border border-gray-100 p-5 text-center transition-transform hover:-translate-y-1">
                            <div class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-2">Non-Operating Income</div>
                            <div class="text-2xl font-bold text-[#1e5b43]">{{ money(totalIncome) }}</div>
                        </div>
                        <div class="bg-white rounded-[20px] shadow-[0_2px_8px_rgba(0,0,0,0.02)] border border-gray-100 p-5 text-center transition-transform hover:-translate-y-1">
                            <div class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-2">Non-Operating Expenses</div>
                            <div class="text-2xl font-bold text-red-600">{{ money(totalExpenses) }}</div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mt-2">Tax: {{ money(totalExpenseTax) }}</div>
                        </div>
                        <div class="bg-[#1e5b43] rounded-[20px] shadow-[0_4px_12px_rgba(30,91,67,0.2)] p-5 text-center transform scale-105">
                            <div class="text-[11px] uppercase tracking-wider font-bold text-emerald-100/90 mb-2">Net Result</div>
                            <div class="text-3xl font-bold" :class="netNonOperating >= 0 ? 'text-white' : 'text-red-300'">
                                {{ money(netNonOperating) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert for loss -->
                <div v-if="netNonOperating < 0" class="mx-6 mb-6 mt-0 bg-red-50 border border-red-100 rounded-[16px] p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 shrink-0">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <span class="text-sm font-bold text-red-800">Net Non-Operating Loss: Review financing and forex exposure</span>
                </div>
            </div>

            <!-- Split View: Income & Expenses -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Non-Operating Income -->
                <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-6 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
                        <h3 class="text-[18px] font-bold text-gray-900">Non-Operating Income</h3>
                        <button @click="showAddModal('income')" class="bg-[#1e5b43]/10 text-[#1e5b43] px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-[#1e5b43]/20 transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-plus w-3 h-3"></i> Add Income
                        </button>
                    </div>

                    <!-- Income Breakdown -->
                    <div class="space-y-1 mb-6 flex-1">
                        <div v-for="(category, index) in incomeCategories" :key="category" class="group hover:bg-gray-50 rounded-xl p-2.5 transition-colors border border-transparent hover:border-gray-100">
                            <div class="flex justify-between items-center">
                                <button @click="filterByCategory('income', category)" class="text-sm text-gray-700 hover:text-[#1e5b43] font-medium text-left flex items-center gap-3 flex-1">
                                    <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-[10px] font-bold">{{ String.fromCharCode(65 + index) }}</span>
                                    <span>{{ category }}</span>
                                </button>
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-[#1e5b43]">{{ money(incomeBreakdown[category] || 0) }}</span>
                                    <button @click="quickAdd('income', category)" class="w-7 h-7 rounded-full flex items-center justify-center text-[#1e5b43] bg-[#1e5b43]/5 hover:bg-[#1e5b43]/10 opacity-0 group-hover:opacity-100 transition-all focus:opacity-100" title="Quick Add">
                                        <i class="fa-solid fa-plus text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Income -->
                    <div class="pt-5 border-t border-gray-200 mt-auto">
                        <div class="flex justify-between items-center">
                            <span class="text-[11px] uppercase tracking-wider font-bold text-gray-500">Total Income:</span>
                            <span class="text-[20px] font-bold text-[#1e5b43]">{{ money(totalIncome) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Non-Operating Expenses -->
                <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-6 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
                        <h3 class="text-[18px] font-bold text-gray-900">Non-Operating Expenses</h3>
                        <button @click="showAddModal('expense')" class="bg-red-50 text-red-600 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-red-100 transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-plus w-3 h-3"></i> Add Expense
                        </button>
                    </div>

                    <!-- Expense Breakdown -->
                    <div class="space-y-1 mb-6 flex-1">
                        <div v-for="(category, index) in expenseCategories" :key="category" class="group hover:bg-gray-50 rounded-xl p-2.5 transition-colors border border-transparent hover:border-gray-100">
                            <div class="flex justify-between items-center">
                                <button @click="filterByCategory('expense', category)" class="text-sm text-gray-700 hover:text-red-700 font-medium text-left flex items-center gap-3 flex-1">
                                    <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-[10px] font-bold">{{ String.fromCharCode(65 + index) }}</span>
                                    <span>{{ category }}</span>
                                </button>
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-red-600">{{ money(expenseBreakdown[category] || 0) }}</span>
                                    <button @click="quickAdd('expense', category)" class="w-7 h-7 rounded-full flex items-center justify-center text-red-600 bg-red-50 hover:bg-red-100 opacity-0 group-hover:opacity-100 transition-all focus:opacity-100" title="Quick Add">
                                        <i class="fa-solid fa-plus text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Expenses -->
                    <div class="pt-5 border-t border-gray-200 mt-auto">
                        <div class="flex justify-between items-center">
                            <span class="text-[11px] uppercase tracking-wider font-bold text-gray-500">Total Expenses:</span>
                            <span class="text-[20px] font-bold text-red-600">{{ money(totalExpenses) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Entries Table (Expandable) -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 overflow-hidden mb-6 transition-all duration-300 ease-in-out">
                <div class="p-5 border-b border-gray-100 bg-white cursor-pointer hover:bg-gray-50/50 transition-colors" @click="showDetails = !showDetails">
                    <div class="flex items-center justify-between pointer-events-none">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                                <i :class="['fa-solid fa-chevron-right transition-transform duration-300 text-xs', showDetails ? 'rotate-90' : '']"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm tracking-wide">{{ showDetails ? 'Hide' : 'Show' }} Detailed Entries</h3>
                                <p class="text-xs text-gray-500 mt-0.5">{{ filteredEntries.length }} {{ filterActive ? 'filtered' : 'total' }} entries</p>
                            </div>
                        </div>
                        <button v-if="filterActive" @click.stop="clearFilter" class="pointer-events-auto bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider transition-colors">
                            Clear Filter
                        </button>
                    </div>
                </div>
                
                <div v-show="showDetails" class="overflow-x-auto border-t border-gray-100">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-gray-50/50 text-[11px] uppercase text-gray-400 font-bold tracking-wider border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 whitespace-nowrap">Type</th>
                                <th class="px-6 py-4 whitespace-nowrap">Cost Head</th>
                                <th class="px-6 py-4 whitespace-nowrap">Client</th>
                                <th class="px-6 py-4 whitespace-nowrap">Description</th>
                                <th class="px-6 py-4 whitespace-nowrap text-right">Amount</th>
                                <th class="px-6 py-4 whitespace-nowrap text-right">Tax Rate</th>
                                <th class="px-6 py-4 whitespace-nowrap text-right">Tax Amount</th>
                                <th class="px-6 py-4 whitespace-nowrap text-right">Total</th>
                                <th class="px-6 py-4 whitespace-nowrap">Date</th>
                                <th class="px-6 py-4 whitespace-nowrap text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-if="filteredEntries.length === 0">
                                <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                                    {{ filterActive ? 'No entries found for this subcategory.' : 'No non-operating entries yet. Click "Add Income" or "Add Expense" to create one.' }}
                                </td>
                            </tr>
                            <tr v-for="entry in filteredEntries" :key="entry.id" class="transition-colors hover:bg-gray-50/50 group">
                                <td class="px-6 py-4">
                                    <span :class="['px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider', entry.type === 'income' ? 'bg-emerald-50 text-[#1e5b43] border border-emerald-100' : 'bg-red-50 text-red-600 border border-red-100']">
                                        {{ entry.type === 'income' ? 'Income' : 'Expense' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ entry.category }}</td>
                                <td class="px-6 py-4">
                                    <template v-if="entry.client">
                                        <div class="font-medium text-gray-900">{{ entry.client.name }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">{{ entry.client.phone_number }}</div>
                                    </template>
                                    <span v-else class="inline-flex px-2 py-1 rounded bg-gray-100 text-gray-500 text-[10px] uppercase font-bold tracking-wider">Org Wide</span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 max-w-[200px] truncate" :title="entry.description">{{ entry.description || "—" }}</td>
                                <td class="px-6 py-4 text-right font-medium" :class="entry.type === 'income' ? 'text-[#1e5b43]' : 'text-red-600'">
                                    {{ money(entry.amount) }}
                                </td>
                                <td class="px-6 py-4 text-right text-gray-500">
                                    {{ entry.type === 'expense' ? `${entry.tax_rate}%` : '—' }}
                                </td>
                                <td class="px-6 py-4 text-right font-medium" :class="entry.type === 'expense' ? 'text-orange-600' : 'text-gray-400'">
                                    {{ entry.type === 'expense' ? money(entry.tax_amount) : '—' }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900">
                                    {{ money(entry.type === 'expense' ? (entry.amount + entry.tax_amount) : entry.amount) }}
                                </td>
                                <td class="px-6 py-4 text-gray-500">{{ entry.created_at }}</td>
                                <td class="px-6 py-4 text-right border-l border-transparent group-hover:border-gray-100">
                                    <div class="flex items-center justify-end gap-1.5 transition-opacity">
                                        <button @click="editEntry(entry)" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-amber-500 hover:bg-amber-50 transition" title="Edit entry">
                                            <i class="fa-solid fa-pen-to-square w-3.5 h-3.5"></i>
                                        </button>
                                        <button @click="deleteEntry(entry)" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition" title="Delete entry">
                                            <i class="fa-solid fa-trash w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- Add/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 sm:p-6">
            <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden transform transition-all">
                <div class="p-6 border-b border-gray-100 flex-shrink-0 flex items-center justify-between bg-gray-50/50">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ editingEntry ? 'Edit Entry' : `Add ${form.type === 'income' ? 'Income' : 'Expense'} Entry` }}
                    </h2>
                    <button @click="closeModal" class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 hover:bg-gray-300 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-times w-3.5 h-3.5"></i>
                    </button>
                </div>
                <div class="overflow-y-auto flex-1 p-6">
                    <form @submit.prevent="submitForm" class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5 uppercase tracking-wider text-[11px]">Client *</label>
                            <div class="relative" ref="clientDropdownRef">
                                <input
                                    v-model="clientSearch"
                                    @input="filterClients"
                                    @focus="showClientDropdown = true"
                                    type="text"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent text-sm transition-shadow"
                                    placeholder="Search by name or phone number..."
                                />
                                <div
                                    v-if="showClientDropdown"
                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-auto py-1"
                                >
                                    <div
                                        v-for="client in filteredClients"
                                        :key="client.id"
                                        @click="selectClient(client)"
                                        class="px-4 py-2 cursor-pointer hover:bg-gray-50 border-b border-gray-50 last:border-0"
                                    >
                                        <div class="font-bold text-sm text-gray-900">{{ client.name }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">{{ client.phone_number }}</div>
                                    </div>
                                    <div v-if="filteredClients.length === 0 && clientSearch" class="px-4 py-3 text-sm text-gray-500 italic text-center">
                                        No clients found
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5 uppercase tracking-wider text-[11px]">Cost Head *</label>
                            <SubcategorySelector
                                v-model="form.category"
                                :subcategories="form.type === 'income' ? incomeSubcategoryObjects : expenseSubcategoryObjects"
                                type="non_operating"
                                :category="form.type"
                                label="Cost Head"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5 uppercase tracking-wider text-[11px]">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent text-sm transition-shadow"
                                placeholder="Enter detailed description"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1.5 uppercase tracking-wider text-[11px]">Amount (৳) *</label>
                                <input
                                    v-model.number="form.amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent text-sm transition-shadow"
                                    placeholder="0.00"
                                />
                            </div>
                            <div v-if="form.type === 'expense'">
                                <label class="block text-sm font-bold text-gray-700 mb-1.5 uppercase tracking-wider text-[11px]">Tax Rate (%)</label>
                                <input
                                    v-model.number="form.tax_rate"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent text-sm transition-shadow"
                                    placeholder="0"
                                />
                            </div>
                        </div>
                        
                        <div v-if="form.type === 'expense' && form.tax_rate > 0" class="mt-4 rounded-[16px] border border-orange-100 bg-orange-50/50 p-4">
                            <div class="flex justify-between items-center pb-2 border-b border-orange-100">
                                <span class="text-sm font-medium text-orange-900">Calculated Tax Amount:</span>
                                <span class="text-sm font-bold text-orange-600">{{ money(calculatedTax) }}</span>
                            </div>
                            <div class="flex justify-between items-center mt-3">
                                <span class="text-sm font-bold text-gray-900 uppercase tracking-wider text-[11px]">Total with Tax:</span>
                                <span class="text-lg font-bold text-gray-900">{{ money(totalWithTaxPreview) }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5 uppercase tracking-wider text-[11px]">Notes</label>
                            <textarea
                                v-model="form.notes"
                                rows="2"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent text-sm transition-shadow"
                                placeholder="Additional notes (optional)"
                            ></textarea>
                        </div>
                    </form>
                </div>
                <div class="p-5 border-t border-gray-100 bg-gray-50 flex-shrink-0 flex justify-end gap-3 rounded-b-[24px]">
                    <button
                        type="button"
                        @click="closeModal"
                        class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-bold hover:bg-gray-50 transition-colors shadow-sm"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="submitForm"
                        class="bg-[#1e5b43] text-white px-6 py-2.5 rounded-full text-sm font-bold hover:bg-[#164230] transition-colors shadow-sm"
                    >
                        {{ editingEntry ? 'Update Entry' : 'Add Entry' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onUnmounted, onMounted } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import IconButton from '@/Components/Buttons/IconButton.vue';
import SubcategorySelector from '@/Components/SubcategorySelector.vue';

const props = defineProps({
    period: Object,
    periods: Array,
    incomeEntries: Array,
    expenseEntries: Array,
    totalIncome: Number,
    totalExpenseTax: Number,
    totalExpenses: Number,
    netNonOperating: Number,
    incomeBreakdown: Object,
    expenseBreakdown: Object,
    clients: Array,
    subcategories: {
        type: Array,
        default: () => [],
    },
});

const incomeSubcategoryObjects = computed(() =>
    (props.subcategories || []).filter((sub) => sub.category === 'income')
);

const expenseSubcategoryObjects = computed(() =>
    (props.subcategories || []).filter((sub) => sub.category === 'expense')
);

const incomeCategories = computed(() =>
    incomeSubcategoryObjects.value.map((sub) => sub.name)
);

const expenseCategories = computed(() =>
    expenseSubcategoryObjects.value.map((sub) => sub.name)
);

const showModal = ref(false);
const showDetails = ref(false);
const editingEntry = ref(null);
const filterType = ref(null);
const filterCategory = ref(null);
const clientSearch = ref('');
const filteredClients = ref(props.clients || []);
const showClientDropdown = ref(false);
const clientDropdownRef = ref(null);
const setBodyScrollLock = (locked) => {
    document.body.style.overflow = locked ? 'hidden' : '';
};

const handleClickOutside = (event) => {
    if (clientDropdownRef.value && !clientDropdownRef.value.contains(event.target)) {
        showClientDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

const form = ref({
    client_id: null,
    type: '',
    category: '',
    description: '',
    amount: 0,
    tax_rate: 0,
    notes: '',
});

const filteredEntries = computed(() => {
    const allEntries = [...props.incomeEntries.map(e => ({...e, type: 'income'})), ...props.expenseEntries.map(e => ({...e, type: 'expense'}))];

    if (!filterType.value && !filterCategory.value) {
        return allEntries;
    }

    return allEntries.filter(e => {
        if (filterType.value && e.type !== filterType.value) return false;
        if (filterCategory.value && e.category !== filterCategory.value) return false;
        return true;
    });
});

const filterActive = computed(() => {
    return filterType.value !== null || filterCategory.value !== null;
});

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
};

const calculatedTax = computed(() => {
    if (!form.value.amount || !form.value.tax_rate) return 0;
    return (form.value.amount * form.value.tax_rate) / 100;
});

const totalWithTaxPreview = computed(() => {
    return (form.value.amount || 0) + calculatedTax.value;
});

const showAddModal = (type) => {
    form.value.type = type;
    if (type !== 'expense') {
        form.value.tax_rate = 0;
    }
    showModal.value = true;
};

const filterByCategory = (type, category) => {
    filterType.value = type;
    filterCategory.value = category;
    showDetails.value = true;
};

const clearFilter = () => {
    filterType.value = null;
    filterCategory.value = null;
};

const quickAdd = (type, category) => {
    form.value.type = type;
    form.value.category = category;
    if (type !== 'expense') {
        form.value.tax_rate = 0;
    }
    showModal.value = true;
};

const filterClients = () => {
    const search = clientSearch.value.toLowerCase();
    if (!search) {
        filteredClients.value = props.clients || [];
    } else {
        filteredClients.value = (props.clients || []).filter(client =>
            client.name.toLowerCase().includes(search) ||
            client.phone_number.toLowerCase().includes(search)
        );
    }
};

const selectClient = (client) => {
    if (!client) return;
    form.value.client_id = client.id;
    clientSearch.value = `${client.name} (${client.phone_number})`;
    showClientDropdown.value = false;
};

const submitForm = () => {
    if (!form.value.client_id) {
        alert('Please select a client.');
        return;
    }
    const data = {
        ...form.value,
        accounting_period_id: props.period.id,
    };

    if (editingEntry.value) {
        router.put(`/accounting/non-operating/${editingEntry.value.id}`, data, {
            onSuccess: () => closeModal(),
        });
    } else {
        router.post('/accounting/non-operating', data, {
            onSuccess: () => closeModal(),
        });
    }
};

const editEntry = (entry) => {
    editingEntry.value = entry;
    form.value = {
        client_id: entry.client_id || null,
        type: entry.type,
        category: entry.category,
        description: entry.description,
        amount: entry.amount,
        tax_rate: entry.tax_rate || 0,
        notes: entry.notes || '',
    };
    // Set client search value
    if (entry.client) {
        clientSearch.value = `${entry.client.name} (${entry.client.phone_number})`;
    } else {
        clientSearch.value = '';
    }
    showModal.value = true;
};

const deleteEntry = (entry) => {
    if (confirm('Are you sure you want to delete this entry?')) {
        router.delete(`/accounting/non-operating/${entry.id}`);
    }
};

const closeModal = () => {
    showModal.value = false;
    editingEntry.value = null;
    clientSearch.value = '';
    showClientDropdown.value = false;
    form.value = {
        client_id: null,
        type: '',
        category: '',
        description: '',
        amount: 0,
        tax_rate: 0,
        notes: '',
    };
};

watch(
    () => showModal.value,
    (isOpen) => setBodyScrollLock(isOpen)
);

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    setBodyScrollLock(false);
});
</script>
