<template>
    <Head title="Cost of Sales" />
    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">{{ categoryName }}</h1>
                <p class="text-sm text-gray-500">Cost of Sales - {{ period.name }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <select class="px-4 py-2 border border-gray-200 rounded-full text-sm font-medium bg-white text-gray-700 shadow-sm focus:ring-2 focus:ring-[#1e5b43] outline-none">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                        <a :href="route('accounting.cost-of-sales.export', { category: category, type: 'excel' })" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-excel text-green-600"></i>
                            Excel
                        </a>
                        <button @click="exportPdf" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf text-red-600"></i>
                            PDF
                        </button>
                        <button @click="showAddModal = true" class="bg-[#1e5b43] text-white px-5 py-2.5 rounded-full text-sm font-bold hover:bg-[#164230] transition-colors shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-plus"></i> Add Entry
                        </button>
                    </div>
                </div>
            </div>

            <!-- Total Summary -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(30,91,67,0.1)] border-2 border-gray-100 p-6 flex items-center justify-between mb-6 transform transition-all hover:-translate-y-1 hover:shadow-lg">
                <h2 class="text-[18px] uppercase tracking-wider font-bold text-gray-500">Total {{ categoryName }} Costs</h2>
                <p class="text-[40px] font-bold text-[#1e5b43] leading-none">{{ money(totalAmount) }}</p>
            </div>

            <!-- Cost Breakdown Panel -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-6 mb-6">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
                    <h3 class="text-[18px] font-bold text-gray-900">{{ categoryName }} - Cost Breakdown</h3>
                </div>

                <!-- Subcategory List -->
                <ul class="space-y-1">
                    <li v-for="(subcategory, index) in subcategories" :key="subcategory.id" class="group hover:bg-gray-50 rounded-xl transition-colors cursor-pointer border border-transparent hover:border-gray-100 px-4 py-3">
                        <div class="flex justify-between items-center">
                            <button @click="filterBySubcategory(subcategory.name)" class="text-sm text-gray-700 hover:text-[#1e5b43] font-medium text-left flex-1 flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-[10px] font-bold">{{ String.fromCharCode(65 + index) }}</span>
                                <span>{{ subcategory.name }}</span>
                            </button>
                            <div class="flex items-center gap-4">
                                <span class="font-bold text-gray-900">{{ money(costBreakdown[subcategory.name] || 0) }}</span>
                                <button @click="quickAdd(subcategory.name)" class="w-7 h-7 rounded-full flex items-center justify-center text-[#1e5b43] bg-[#1e5b43]/5 hover:bg-[#1e5b43]/10 opacity-0 group-hover:opacity-100 transition-all focus:opacity-100" title="Quick Add">
                                    <i class="fa-solid fa-plus text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>

                <!-- Total Row -->
                <div class="mt-4 pt-4 border-t border-gray-200 px-4">
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] uppercase tracking-wider font-bold text-gray-500">Total {{ categoryName }}:</span>
                        <span class="text-[20px] font-bold text-[#1e5b43] pr-[44px]">{{ money(totalAmount) }}</span>
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
                                <th class="px-6 py-4 whitespace-nowrap">Cost Head</th>
                                <th class="px-6 py-4 whitespace-nowrap">Client</th>
                                <th class="px-6 py-4 whitespace-nowrap">Description</th>
                                <th class="px-6 py-4 whitespace-nowrap text-right">Amount</th>
                                <th class="px-6 py-4 whitespace-nowrap">Date</th>
                                <th class="px-6 py-4 whitespace-nowrap text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-if="filteredEntries.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    {{ filterActive ? 'No entries found for this subcategory.' : 'No cost entries yet. Click "Add Entry" to create one.' }}
                                </td>
                            </tr>
                            <tr v-for="entry in filteredEntries" :key="entry.id" class="transition-colors hover:bg-gray-50/50 group">
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ entry.subcategory }}</td>
                                <td class="px-6 py-4">
                                    <template v-if="entry.client">
                                        <div class="font-medium text-gray-900">{{ entry.client.name }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">{{ entry.client.phone_number }}</div>
                                    </template>
                                    <span v-else class="inline-flex px-2 py-1 rounded bg-gray-100 text-gray-500 text-[10px] uppercase font-bold tracking-wider">Org Wide</span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 max-w-[300px] truncate" :title="entry.description">{{ entry.description || "—" }}</td>
                                <td class="px-6 py-4 text-right font-bold text-[#1e5b43]">
                                    {{ money(entry.amount) }}
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
        <div v-if="showAddModal || editingEntry" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 sm:p-6">
            <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden transform transition-all">
                <div class="p-6 border-b border-gray-100 flex-shrink-0 flex items-center justify-between bg-gray-50/50">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ editingEntry ? 'Edit Cost Entry' : 'Add Cost Entry' }}
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
                                v-model="form.subcategory"
                                :subcategories="subcategories"
                                type="cost_of_sales"
                                :category="category"
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
