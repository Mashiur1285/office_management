<template>
    <Head title="Non-Operating" />
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-cyan-50/30 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Non-Operating Income & Expenses</h1>
                        <p class="text-sm text-gray-600 mt-1">Period: {{ period.name }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Net Non-Operating Display -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-cyan-500 p-6">
                <h2 class="text-xl font-bold text-cyan-700 mb-4">Net Non-Operating Profit/Loss</h2>
                <div class="bg-cyan-50 rounded-lg p-6">
                    <div class="flex items-center justify-center gap-4 text-lg font-medium">
                        <div class="text-center">
                            <div class="text-sm text-gray-600 mb-1">Non-Operating Income</div>
                            <div class="text-2xl font-bold text-green-700">{{ money(totalIncome) }}</div>
                        </div>
                        <div class="text-3xl text-gray-400">−</div>
                        <div class="text-center">
                            <div class="text-sm text-gray-600 mb-1">Non-Operating Expenses</div>
                            <div class="text-2xl font-bold text-red-700">{{ money(totalExpenses) }}</div>
                        </div>
                        <div class="text-3xl text-gray-400">=</div>
                        <div class="text-center">
                            <div class="text-sm text-gray-600 mb-1">Net Result</div>
                            <div class="text-3xl font-bold" :class="netNonOperating >= 0 ? 'text-cyan-700' : 'text-red-700'">
                                {{ money(netNonOperating) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert for loss -->
                <div v-if="netNonOperating < 0" class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium text-red-800">Net Non-Operating Loss: Review financing and forex exposure</span>
                    </div>
                </div>
            </div>

            <!-- Split View: Income & Expenses -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Non-Operating Income -->
                <div class="bg-white rounded-xl shadow-sm border-4 border-cyan-400 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-cyan-700">Non-Operating Income</h3>
                        <button @click="showAddModal('income')" class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium text-xs">
                            + Add Income
                        </button>
                    </div>

                    <!-- Income Breakdown -->
                    <div class="space-y-2 mb-4">
                        <div v-for="(category, index) in incomeCategories" :key="category" class="group hover:bg-cyan-50 rounded-lg p-2 transition-colors">
                            <div class="flex justify-between items-center">
                                <button @click="filterByCategory('income', category)" class="text-sm text-gray-700 hover:text-cyan-800 font-medium text-left flex items-center gap-2 flex-1">
                                    <span class="font-bold text-cyan-700">{{ String.fromCharCode(65 + index) }}.</span>
                                    <span>{{ category }}</span>
                                </button>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-green-700">{{ money(incomeBreakdown[category] || 0) }}</span>
                                    <button @click="quickAdd('income', category)" class="opacity-0 group-hover:opacity-100 transition-opacity text-green-600 hover:text-green-800 text-xl font-bold" title="Quick Add">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Income -->
                    <div class="pt-4 border-t-2 border-cyan-300">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-900">Total Income:</span>
                            <span class="text-xl font-bold text-green-700">{{ money(totalIncome) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Non-Operating Expenses -->
                <div class="bg-white rounded-xl shadow-sm border-4 border-cyan-400 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-cyan-700">Non-Operating Expenses</h3>
                        <button @click="showAddModal('expense')" class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-xs">
                            + Add Expense
                        </button>
                    </div>

                    <!-- Expense Breakdown -->
                    <div class="space-y-2 mb-4">
                        <div v-for="(category, index) in expenseCategories" :key="category" class="group hover:bg-cyan-50 rounded-lg p-2 transition-colors">
                            <div class="flex justify-between items-center">
                                <button @click="filterByCategory('expense', category)" class="text-sm text-gray-700 hover:text-cyan-800 font-medium text-left flex items-center gap-2 flex-1">
                                    <span class="font-bold text-cyan-700">{{ String.fromCharCode(65 + index) }}.</span>
                                    <span>{{ category }}</span>
                                </button>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-red-700">{{ money(expenseBreakdown[category] || 0) }}</span>
                                    <button @click="quickAdd('expense', category)" class="opacity-0 group-hover:opacity-100 transition-opacity text-red-600 hover:text-red-800 text-xl font-bold" title="Quick Add">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Expenses -->
                    <div class="pt-4 border-t-2 border-cyan-300">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-900">Total Expenses:</span>
                            <span class="text-xl font-bold text-red-700">{{ money(totalExpenses) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Entries Table (Expandable) -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <button @click="showDetails = !showDetails" class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                            <svg :class="['w-4 h-4 transition-transform', showDetails ? 'rotate-90' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            {{ showDetails ? 'Hide' : 'Show' }} Detailed Entries ({{ filteredEntries.length }} {{ filterActive ? 'filtered' : 'total' }})
                        </button>
                        <button v-if="filterActive" @click="clearFilter" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Clear Filter
                        </button>
                    </div>
                </div>
                <div v-show="showDetails" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-if="filteredEntries.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    {{ filterActive ? 'No entries found for this category.' : 'No non-operating entries yet. Click "Add Income" or "Add Expense" to create one.' }}
                                </td>
                            </tr>
                            <tr v-for="entry in filteredEntries" :key="entry.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm">
                                    <span :class="['px-2 py-1 rounded-full text-xs font-medium', entry.type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">
                                        {{ entry.type === 'income' ? 'Income' : 'Expense' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ entry.category }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <template v-if="entry.client">
                                        <div>{{ entry.client.name }}</div>
                                        <div class="text-xs text-gray-500">{{ entry.client.phone_number }}</div>
                                    </template>
                                    <span v-else class="text-gray-400 italic">Organization-wide</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ entry.description }}</td>
                                <td class="px-6 py-4 text-sm text-right font-bold" :class="entry.type === 'income' ? 'text-green-700' : 'text-red-700'">
                                    {{ money(entry.amount) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ entry.created_at }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="editEntry(entry)" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            Edit
                                        </button>
                                        <button @click="deleteEntry(entry)" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
                <div class="p-6 border-b border-gray-200 flex-shrink-0">
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ editingEntry ? 'Edit Entry' : `Add ${form.type === 'income' ? 'Income' : 'Expense'} Entry` }}
                    </h2>
                </div>
                <form @submit.prevent="submitForm" class="flex-1 overflow-y-auto">
                    <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client (Optional)</label>
                        <div class="relative">
                            <input
                                v-model="clientSearch"
                                @input="filterClients"
                                @focus="showClientDropdown = true"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                                placeholder="Search by name or phone number..."
                            />
                            <div
                                v-if="showClientDropdown"
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto"
                            >
                                <div
                                    @click="selectClient(null)"
                                    class="px-4 py-2 hover:bg-cyan-50 cursor-pointer text-sm"
                                >
                                    No Client (Organization-wide)
                                </div>
                                <div
                                    v-for="client in filteredClients"
                                    :key="client.id"
                                    @click="selectClient(client)"
                                    class="px-4 py-2 hover:bg-cyan-50 cursor-pointer text-sm border-t border-gray-100"
                                >
                                    <div class="font-medium">{{ client.name }}</div>
                                    <div class="text-xs text-gray-500">{{ client.phone_number }}</div>
                                </div>
                                <div v-if="filteredClients.length === 0 && clientSearch" class="px-4 py-2 text-sm text-gray-500 italic">
                                    No clients found
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select
                            v-model="form.category"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                        >
                            <option value="">Select category</option>
                            <option v-for="cat in (form.type === 'income' ? incomeCategories : expenseCategories)" :key="cat" :value="cat">
                                {{ cat }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                        <textarea
                            v-model="form.description"
                            required
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                            placeholder="Enter detailed description"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount (৳) *</label>
                        <input
                            v-model.number="form.amount"
                            type="number"
                            step="0.01"
                            min="0"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                            placeholder="0.00"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea
                            v-model="form.notes"
                            rows="2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                            placeholder="Additional notes (optional)"
                        ></textarea>
                    </div>

                    </div>
                </form>
                <div class="p-6 border-t border-gray-200 bg-gray-50 flex-shrink-0">
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 bg-white transition-colors font-medium text-sm"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            @click="submitForm"
                            class="px-6 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition-colors font-medium text-sm"
                        >
                            {{ editingEntry ? 'Update Entry' : 'Add Entry' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    period: Object,
    periods: Array,
    incomeEntries: Array,
    expenseEntries: Array,
    totalIncome: Number,
    totalExpenses: Number,
    netNonOperating: Number,
    incomeBreakdown: Object,
    expenseBreakdown: Object,
    clients: Array,
});

const incomeCategories = [
    'Interest Income',
    'Foreign Exchange Gain',
];

const expenseCategories = [
    'Interest on Bank Loan',
    'Foreign Exchange Loss',
    'Penalties & Legal Fees',
];

const showModal = ref(false);
const showDetails = ref(false);
const editingEntry = ref(null);
const filterType = ref(null);
const filterCategory = ref(null);
const clientSearch = ref('');
const filteredClients = ref(props.clients || []);
const showClientDropdown = ref(false);

const form = ref({
    client_id: null,
    type: '',
    category: '',
    description: '',
    amount: 0,
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

const showAddModal = (type) => {
    form.value.type = type;
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
    if (client) {
        form.value.client_id = client.id;
        clientSearch.value = `${client.name} (${client.phone_number})`;
    } else {
        form.value.client_id = null;
        clientSearch.value = 'No Client (Organization-wide)';
    }
    showClientDropdown.value = false;
};

const submitForm = () => {
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
        notes: entry.notes || '',
    };
    // Set client search value
    if (entry.client) {
        clientSearch.value = `${entry.client.name} (${entry.client.phone_number})`;
    } else {
        clientSearch.value = 'No Client (Organization-wide)';
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
        notes: '',
    };
};
</script>
