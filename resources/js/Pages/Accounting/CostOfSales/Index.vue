<template>
    <Head title="Cost of Sales" />
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-pink-50/30 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ categoryName }}</h1>
                        <p class="text-sm text-gray-600 mt-1">Cost of Sales - {{ period.name }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select class="px-4 py-2 pr-10 border border-gray-300 rounded-lg text-sm font-medium bg-white">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                        <a :href="route('accounting.cost-of-sales.export', { category: category, type: 'excel' })" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium text-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-excel"></i>
                            Export to Excel
                        </a>
                        <button @click="exportPdf" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf"></i>
                            Export to PDF
                        </button>
                        <button @click="showAddModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm">
                            + Add Entry
                        </button>
                    </div>
                </div>
            </div>

            <!-- Total Summary -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-pink-500 p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">Total {{ categoryName }} Costs</h2>
                    <p class="text-4xl font-bold text-pink-700">{{ money(totalAmount) }}</p>
                </div>
            </div>

            <!-- Cost Breakdown Panel -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-pink-500 p-6">
                <h3 class="text-lg font-bold text-pink-700 mb-4">{{ categoryName }} - Cost Breakdown</h3>

                <!-- Subcategory List -->
                <ul class="space-y-1">
                    <li v-for="(subcategory, index) in subcategories" :key="subcategory.id" class="group hover:bg-pink-50 rounded-lg p-2 transition-colors cursor-pointer">
                        <div class="flex justify-between items-center">
                            <button @click="filterBySubcategory(subcategory.name)" class="text-sm text-gray-700 hover:text-pink-800 font-medium text-left flex-1 flex items-center gap-2">
                                <span class="font-bold text-pink-700">{{ String.fromCharCode(65 + index) }}.</span>
                                <span>{{ subcategory.name }}</span>
                            </button>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-pink-700">{{ money(costBreakdown[subcategory.name] || 0) }}</span>
                                <button @click="quickAdd(subcategory.name)" class="opacity-0 group-hover:opacity-100 transition-opacity text-green-600 hover:text-green-800 text-xl font-bold" title="Quick Add">
                                    +
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>

                <!-- Total Row -->
                <div class="mt-4 pt-4 border-t-2 border-pink-300">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-gray-900">Total {{ categoryName }}:</span>
                        <span class="text-xl font-bold text-pink-700">{{ money(totalAmount) }}</span>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subcategory</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-if="filteredEntries.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    {{ filterActive ? 'No entries found for this subcategory.' : 'No cost entries yet. Click "Add Entry" to create one.' }}
                                </td>
                            </tr>
                            <tr v-for="entry in filteredEntries" :key="entry.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ entry.subcategory }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <template v-if="entry.client">
                                        <div>{{ entry.client.name }}</div>
                                        <div class="text-xs text-gray-500">{{ entry.client.phone_number }}</div>
                                    </template>
                                    <span v-else class="text-gray-400 italic">Organization-wide</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ entry.description }}</td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-pink-700">{{ money(entry.amount) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ entry.created_at }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <IconButton
                                            icon="fa-solid fa-pen-to-square"
                                            class="bg-blue-600 text-white hover:bg-blue-700"
                                            tooltip="Edit entry"
                                            @click="editEntry(entry)"
                                        />
                                        <IconButton
                                            icon="fa-solid fa-trash"
                                            class="bg-red-600 text-white hover:bg-red-700"
                                            tooltip="Delete entry"
                                            @click="deleteEntry(entry)"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="showAddModal || editingEntry" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ editingEntry ? 'Edit Cost Entry' : 'Add Cost Entry' }}
                    </h2>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client *</label>
                        <div class="relative" ref="clientDropdownRef">
                            <input
                                v-model="clientSearch"
                                @input="filterClients"
                                @focus="showClientDropdown = true"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                placeholder="Search by name or phone number..."
                            />
                            <div
                                v-if="showClientDropdown"
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto"
                            >
                                <div
                                    v-for="client in filteredClients"
                                    :key="client.id"
                                    @click="selectClient(client)"
                                    class="px-4 py-2 hover:bg-pink-50 cursor-pointer text-sm border-t border-gray-100"
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subcategory *</label>
                        <SubcategorySelector
                            v-model="form.subcategory"
                            :subcategories="subcategories"
                            type="cost_of_sales"
                            :category="category"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            placeholder="0.00"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea
                            v-model="form.notes"
                            rows="2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            placeholder="Additional notes (optional)"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-6 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition-colors font-medium text-sm"
                        >
                            {{ editingEntry ? 'Update Entry' : 'Add Entry' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import IconButton from '@/Components/Buttons/IconButton.vue';
import SubcategorySelector from '@/Components/SubcategorySelector.vue';

const props = defineProps({
    category: String,
    categoryName: String,
    period: Object,
    periods: Array,
    entries: Array,
    totalAmount: Number,
    costBreakdown: Object,
    clients: Array,
    subcategories: Array,
});

const showAddModal = ref(false);
const showDetails = ref(false);
const editingEntry = ref(null);
const filterSubcategory = ref(null);
const clientSearch = ref('');
const filteredClients = ref(props.clients || []);
const showClientDropdown = ref(false);
const clientDropdownRef = ref(null);
const setBodyScrollLock = (locked) => {
    document.body.style.overflow = locked ? 'hidden' : '';
};

const form = ref({
    client_id: null,
    subcategory: '',
    description: '',
    amount: 0,
    notes: '',
});

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (clientDropdownRef.value && !clientDropdownRef.value.contains(event.target)) {
        showClientDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    setBodyScrollLock(false);
});

watch(
    () => showAddModal.value || !!editingEntry.value,
    (isOpen) => setBodyScrollLock(isOpen)
);

const filteredEntries = computed(() => {
    if (!filterSubcategory.value) {
        return props.entries;
    }
    return props.entries.filter(e => e.subcategory === filterSubcategory.value);
});

const filterActive = computed(() => {
    return filterSubcategory.value !== null;
});

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
};

const filterBySubcategory = (subcategory) => {
    filterSubcategory.value = subcategory;
    showDetails.value = true;
};

const clearFilter = () => {
    filterSubcategory.value = null;
};

const quickAdd = (subcategory) => {
    form.value.subcategory = subcategory;
    showAddModal.value = true;
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
    form.value.client_id = client.id;
    clientSearch.value = `${client.name} (${client.phone_number})`;
    showClientDropdown.value = false;
};

const submitForm = () => {
    // Validate client is selected
    if (!form.value.client_id) {
        alert('Please select a client');
        return;
    }

    const data = {
        ...form.value,
        accounting_period_id: props.period.id,
        category: props.category,
    };

    if (editingEntry.value) {
        router.put(`/accounting/cost-of-sales/${editingEntry.value.id}`, data, {
            onSuccess: () => closeModal(),
        });
    } else {
        router.post('/accounting/cost-of-sales', data, {
            onSuccess: () => closeModal(),
        });
    }
};

const editEntry = (entry) => {
    editingEntry.value = entry;
    form.value = {
        client_id: entry.client_id || null,
        subcategory: entry.subcategory,
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
};

const deleteEntry = (entry) => {
    if (confirm('Are you sure you want to delete this cost entry?')) {
        router.delete(`/accounting/cost-of-sales/${entry.id}`);
    }
};

const closeModal = () => {
    showAddModal.value = false;
    editingEntry.value = null;
    clientSearch.value = '';
    showClientDropdown.value = false;
    form.value = {
        client_id: null,
        subcategory: '',
        description: '',
        amount: 0,
        notes: '',
    };
};

const exportPdf = () => {
    const url = route('accounting.cost-of-sales.export', { category: props.category, type: 'pdf' });
    window.open(url, '_blank');
};
</script>
