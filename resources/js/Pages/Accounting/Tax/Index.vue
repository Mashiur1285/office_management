<template>
    <Head title="Tax" />
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-yellow-50/30 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Tax Management</h1>
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

            <!-- Tax Summary -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-yellow-500 p-6">
                <h2 class="text-xl font-bold text-yellow-700 mb-6">Total Tax Expenses</h2>

                <div class="space-y-4">
                    <!-- Formula Display -->
                    <div class="bg-yellow-50 rounded-lg p-6">
                        <div class="flex items-center justify-center gap-4 text-lg font-medium">
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Current Tax</div>
                                <div class="text-2xl font-bold text-orange-700">{{ money(totalCurrentTax) }}</div>
                            </div>
                            <div class="text-3xl text-gray-400">+</div>
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Deferred Tax</div>
                                <div class="text-2xl font-bold text-amber-700">{{ money(totalDeferredTax) }}</div>
                            </div>
                            <div class="text-3xl text-gray-400">=</div>
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Total Tax</div>
                                <div class="text-3xl font-bold text-yellow-700">{{ money(totalTax) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Pre/Post Tax Comparison -->
                    <div class="bg-yellow-100 rounded-lg p-4 border-2 border-yellow-400">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Net Profit Before Tax</div>
                                <Link href="/accounting/net-profit-before-tax" class="text-xl font-bold text-green-700 hover:text-green-800 hover:underline">
                                    {{ money(netProfitBeforeTax) }}
                                </Link>
                            </div>
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Net Profit After Tax</div>
                                <div class="text-xl font-bold" :class="netProfitAfterTax >= 0 ? 'text-green-700' : 'text-red-700'">
                                    {{ money(netProfitAfterTax) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax Entry Sections -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Current Tax -->
                <div class="bg-white rounded-xl shadow-sm border-4 border-orange-400 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-orange-700">Current Tax</h3>
                        <button @click="showAddModal('current')" class="px-3 py-1 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors font-medium text-xs">
                            + Add Current Tax
                        </button>
                    </div>

                    <div class="space-y-2">
                        <div v-if="currentTaxEntries.length === 0" class="text-center py-8 text-gray-500 text-sm">
                            No current tax entries yet
                        </div>
                        <div v-for="entry in currentTaxEntries" :key="entry.id" class="bg-orange-50 rounded-lg p-3 hover:bg-orange-100 transition-colors">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900 text-sm">{{ entry.description }}</div>
                                    <div v-if="entry.client" class="text-xs text-gray-600 mt-1">
                                        Client: {{ entry.client.name }} ({{ entry.client.phone_number }})
                                    </div>
                                    <div v-else class="text-xs text-gray-400 italic mt-1">Organization-wide</div>
                                    <div v-if="entry.notes" class="text-xs text-gray-600 mt-1">{{ entry.notes }}</div>
                                </div>
                                <div class="flex items-center gap-3 ml-4">
                                    <span class="font-bold text-orange-700">{{ money(entry.amount) }}</span>
                                    <div class="flex gap-1">
                                        <button @click="editEntry(entry, 'current')" class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                                        <button @click="deleteEntry(entry)" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t-2 border-orange-300">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-900">Total Current Tax:</span>
                            <span class="text-xl font-bold text-orange-700">{{ money(totalCurrentTax) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Deferred Tax -->
                <div class="bg-white rounded-xl shadow-sm border-4 border-amber-400 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-amber-700">Deferred Tax</h3>
                        <button @click="showAddModal('deferred')" class="px-3 py-1 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors font-medium text-xs">
                            + Add Deferred Tax
                        </button>
                    </div>

                    <div class="space-y-2">
                        <div v-if="deferredTaxEntries.length === 0" class="text-center py-8 text-gray-500 text-sm">
                            No deferred tax entries yet
                        </div>
                        <div v-for="entry in deferredTaxEntries" :key="entry.id" class="bg-amber-50 rounded-lg p-3 hover:bg-amber-100 transition-colors">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900 text-sm">{{ entry.description }}</div>
                                    <div v-if="entry.client" class="text-xs text-gray-600 mt-1">
                                        Client: {{ entry.client.name }} ({{ entry.client.phone_number }})
                                    </div>
                                    <div v-else class="text-xs text-gray-400 italic mt-1">Organization-wide</div>
                                    <div v-if="entry.notes" class="text-xs text-gray-600 mt-1">{{ entry.notes }}</div>
                                </div>
                                <div class="flex items-center gap-3 ml-4">
                                    <span class="font-bold text-amber-700">{{ money(entry.amount) }}</span>
                                    <div class="flex gap-1">
                                        <button @click="editEntry(entry, 'deferred')" class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                                        <button @click="deleteEntry(entry)" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t-2 border-amber-300">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-900">Total Deferred Tax:</span>
                            <span class="text-xl font-bold text-amber-700">{{ money(totalDeferredTax) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Note -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-1">Tax Entry Guidelines</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li><strong>Current Tax:</strong> Statutory tax liability for the current period based on taxable income</li>
                            <li><strong>Deferred Tax:</strong> Tax impact of temporary differences between accounting and tax bases</li>
                            <li>Total tax expenses reduce Net Profit Before Tax to arrive at Net Profit After Tax</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
                <div class="p-6 border-b border-gray-200 flex-shrink-0">
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ editingEntry ? 'Edit Tax Entry' : `Add ${form.tax_type === 'current' ? 'Current' : 'Deferred'} Tax Entry` }}
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
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                    placeholder="Search by name or phone number..."
                                />
                                <div
                                    v-if="showClientDropdown"
                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto"
                                >
                                    <div
                                        @click="selectClient(null)"
                                        class="px-4 py-2 hover:bg-yellow-50 cursor-pointer text-sm"
                                    >
                                        No Client (Organization-wide)
                                    </div>
                                    <div
                                        v-for="client in filteredClients"
                                        :key="client.id"
                                        @click="selectClient(client)"
                                        class="px-4 py-2 hover:bg-yellow-50 cursor-pointer text-sm border-t border-gray-100"
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                            <input
                                v-model="form.description"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                placeholder="e.g., Corporate Income Tax, Tax on Temporary Differences"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount (৳) *</label>
                            <input
                                v-model.number="form.amount"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                placeholder="0.00"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                placeholder="Additional details (optional)"
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
                            class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors font-medium text-sm"
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
import { ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    period: Object,
    periods: Array,
    currentTaxEntries: Array,
    deferredTaxEntries: Array,
    totalCurrentTax: Number,
    totalDeferredTax: Number,
    totalTax: Number,
    netProfitBeforeTax: Number,
    netProfitAfterTax: Number,
    clients: Array,
});

const showModal = ref(false);
const editingEntry = ref(null);
const clientSearch = ref('');
const filteredClients = ref(props.clients || []);
const showClientDropdown = ref(false);

const form = ref({
    client_id: null,
    tax_type: '',
    description: '',
    amount: 0,
    notes: '',
});

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
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

const showAddModal = (taxType) => {
    form.value.tax_type = taxType;
    showModal.value = true;
};

const submitForm = () => {
    const data = {
        ...form.value,
        accounting_period_id: props.period.id,
    };

    if (editingEntry.value) {
        router.put(`/accounting/tax/${editingEntry.value.id}`, data, {
            onSuccess: () => closeModal(),
        });
    } else {
        router.post('/accounting/tax', data, {
            onSuccess: () => closeModal(),
        });
    }
};

const editEntry = (entry, taxType) => {
    editingEntry.value = entry;
    form.value = {
        client_id: entry.client_id || null,
        tax_type: taxType,
        description: entry.description,
        amount: entry.amount,
        notes: entry.notes || '',
    };

    // Set client search value if editing
    if (entry.client_id && entry.client) {
        clientSearch.value = `${entry.client.name} (${entry.client.phone_number})`;
    } else {
        clientSearch.value = 'No Client (Organization-wide)';
    }

    showModal.value = true;
};

const deleteEntry = (entry) => {
    if (confirm('Are you sure you want to delete this tax entry?')) {
        router.delete(`/accounting/tax/${entry.id}`);
    }
};

const closeModal = () => {
    showModal.value = false;
    editingEntry.value = null;
    form.value = {
        client_id: null,
        tax_type: '',
        description: '',
        amount: 0,
        notes: '',
    };
    clientSearch.value = '';
    filteredClients.value = props.clients || [];
    showClientDropdown.value = false;
};
</script>
