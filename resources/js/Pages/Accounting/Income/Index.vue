<template>
    <Head title="Income" />
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-green-50/30 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ categoryName }}</h1>
                        <p class="text-sm text-gray-600 mt-1">Income Management - {{ period.name }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                        <button @click="showAddModal = true" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium text-sm">
                            + Add Entry
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <p class="text-sm text-gray-600">Total Income</p>
                    <p class="text-3xl font-bold text-green-700">{{ money(totalAmount) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <p class="text-sm text-gray-600">Total VAT (15%)</p>
                    <p class="text-3xl font-bold text-orange-700">{{ money(totalVat) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <p class="text-sm text-gray-600">Total with VAT</p>
                    <p class="text-3xl font-bold text-blue-700">{{ money(totalWithVat) }}</p>
                </div>
            </div>

            <!-- Entries Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subcategory</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">VAT Rate</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">VAT Amount</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-if="entries.length === 0">
                                <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                    No income entries yet. Click "Add Entry" to create one.
                                </td>
                            </tr>
                            <tr v-for="entry in entries" :key="entry.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ entry.subcategory }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <template v-if="entry.client">
                                        <div>{{ entry.client.name }}</div>
                                        <div class="text-xs text-gray-500">{{ entry.client.phone_number }}</div>
                                    </template>
                                    <span v-else class="text-gray-400 italic">Organization-wide</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ entry.description }}</td>
                                <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">{{ money(entry.amount) }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-700">{{ entry.vat_rate }}%</td>
                                <td class="px-6 py-4 text-sm text-right font-medium text-orange-700">{{ money(entry.vat_amount) }}</td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-green-700">{{ money(entry.amount + entry.vat_amount) }}</td>
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
        <div v-if="showAddModal || editingEntry" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ editingEntry ? 'Edit Income Entry' : 'Add Income Entry' }}
                    </h2>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client (Optional)</label>
                        <div class="relative">
                            <input
                                v-model="clientSearch"
                                @input="filterClients"
                                @focus="showClientDropdown = true"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Search by name or phone number..."
                            />
                            <div
                                v-if="showClientDropdown"
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto"
                            >
                                <div
                                    @click="selectClient(null)"
                                    class="px-4 py-2 hover:bg-green-50 cursor-pointer text-sm"
                                >
                                    No Client (Organization-wide)
                                </div>
                                <div
                                    v-for="client in filteredClients"
                                    :key="client.id"
                                    @click="selectClient(client)"
                                    class="px-4 py-2 hover:bg-green-50 cursor-pointer text-sm border-t border-gray-100"
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
                        <select
                            v-model="form.subcategory"
                            @change="updateVatRate"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                            <option value="">Select subcategory</option>
                            <option v-for="sub in subcategories" :key="sub.name" :value="sub.name">
                                {{ sub.name }} (VAT: {{ sub.vat_rate }}%)
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                        <textarea
                            v-model="form.description"
                            required
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Enter detailed description"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount (৳) *</label>
                            <input
                                v-model.number="form.amount"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="0.00"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">VAT Rate (%)</label>
                            <input
                                v-model.number="form.vat_rate"
                                type="number"
                                readonly
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                            />
                        </div>
                    </div>

                    <div v-if="form.vat_rate > 0" class="bg-orange-50 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700">Calculated VAT Amount:</span>
                            <span class="text-lg font-bold text-orange-700">{{ money(calculatedVat) }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-sm font-medium text-gray-900">Total with VAT:</span>
                            <span class="text-xl font-bold text-green-700">{{ money(totalWithVatPreview) }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea
                            v-model="form.notes"
                            rows="2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium text-sm"
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
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    category: String,
    categoryName: String,
    period: Object,
    periods: Array,
    entries: Array,
    totalAmount: Number,
    totalVat: Number,
    totalWithVat: Number,
    clients: Array,
});

// Define subcategories with predefined VAT rates based on category
const subcategoryDefinitions = {
    travel_tourism: [
        { name: 'Air Ticket Service Charge', vat_rate: 15 },
        { name: 'Airline Commission', vat_rate: 0 },
        { name: 'Tour Package Sale - Inbound', vat_rate: 15 },
        { name: 'Tour Package Sale - Outbound', vat_rate: 15 },
        { name: 'Umrah/Hajj Service Costs', vat_rate: 0 },
        { name: 'Hotel Booking Commission', vat_rate: 0 },
        { name: 'Visa Processing Fee', vat_rate: 15 },
        { name: 'Travel Insurance Commission', vat_rate: 0 },
        { name: 'Re-issue & Cancellation Charge', vat_rate: 15 },
    ],
    manpower_exporting: [
        { name: 'Manpower Training Service Charge', vat_rate: 15 },
        { name: 'Recruitment Service Fee', vat_rate: 15 },
        { name: 'Processing Fees', vat_rate: 0 },
        { name: 'Medical Application Fee', vat_rate: 0 },
        { name: 'Employer/Foreign Agent Commission', vat_rate: 15 },
        { name: 'Document/Foreign Agent Commission', vat_rate: 15 },
        { name: 'Visa Endorsement Service Charge', vat_rate: 15 },
        { name: 'Other Income Service Charge', vat_rate: 15 },
    ],
    student_package: [
        { name: 'Student File Opening Fee', vat_rate: 15 },
        { name: 'Admission & Application Fees', vat_rate: 15 },
        { name: 'University/College Commission', vat_rate: 0 },
        { name: 'Document Visa Processing Fees', vat_rate: 15 },
        { name: 'Student Visa Fee', vat_rate: 15 },
        { name: 'IELTS Reference Commission', vat_rate: 0 },
        { name: 'Other Reference Commission', vat_rate: 15 },
        { name: 'Other Commission', vat_rate: 15 },
    ],
    other_income: [
        { name: 'Courier or Documentation Charges', vat_rate: 15 },
        { name: 'Forex Gain/Loss', vat_rate: 0 },
        { name: 'Miscellaneous Service Income', vat_rate: 15 },
    ],
};

const subcategories = computed(() => subcategoryDefinitions[props.category] || []);

const showAddModal = ref(false);
const editingEntry = ref(null);
const clientSearch = ref('');
const filteredClients = ref(props.clients || []);
const showClientDropdown = ref(false);

const form = ref({
    client_id: null,
    subcategory: '',
    description: '',
    amount: 0,
    vat_rate: 0,
    notes: '',
});

const calculatedVat = computed(() => {
    if (!form.value.amount || !form.value.vat_rate) return 0;
    return (form.value.amount * form.value.vat_rate) / 100;
});

const totalWithVatPreview = computed(() => {
    return (form.value.amount || 0) + calculatedVat.value;
});

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
};

const updateVatRate = () => {
    const selected = subcategories.value.find(sub => sub.name === form.value.subcategory);
    if (selected) {
        form.value.vat_rate = selected.vat_rate;
    }
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
        category: props.category,
    };

    if (editingEntry.value) {
        router.put(`/accounting/income/${editingEntry.value.id}`, data, {
            onSuccess: () => closeModal(),
        });
    } else {
        router.post('/accounting/income', data, {
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
        vat_rate: entry.vat_rate,
        notes: entry.notes || '',
    };
    // Set client search value
    if (entry.client) {
        clientSearch.value = `${entry.client.name} (${entry.client.phone_number})`;
    } else {
        clientSearch.value = 'No Client (Organization-wide)';
    }
    // Update VAT rate based on subcategory
    updateVatRate();
};

const deleteEntry = (entry) => {
    if (confirm('Are you sure you want to delete this income entry?')) {
        router.delete(`/accounting/income/${entry.id}`);
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
        vat_rate: 0,
        notes: '',
    };
};
</script>
