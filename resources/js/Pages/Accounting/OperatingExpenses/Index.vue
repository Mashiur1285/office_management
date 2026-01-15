<template>
    <Head title="Operating Expenses" />
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-pink-50/30 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ categoryName }}</h1>
                        <p class="text-sm text-gray-600 mt-1">Operating Expenses - {{ period.name }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select class="px-4 py-2 pr-10 border border-gray-300 rounded-lg text-sm font-medium bg-white">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                        <a :href="`/accounting/operating-expenses/${category}/report`" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium text-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-excel"></i>
                            Export to Excel
                        </a>
                        <a :href="`/accounting/operating-expenses/${category}/report?type=pdf`" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf"></i>
                            Export to PDF
                        </a>
                        <button @click="showAddModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm">
                            + Add Entry
                        </button>
                    </div>
                </div>
            </div>

            <!-- Total Summary -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-pink-500 p-6">
                <div class="grid grid-cols-3 gap-6">
                    <div>
                        <p class="text-sm text-gray-600">Total Amount</p>
                        <p class="text-3xl font-bold text-green-700">{{ money(totalAmount) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total VAT</p>
                        <p class="text-3xl font-bold text-red-700">{{ money(totalVat) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total with VAT</p>
                        <p class="text-3xl font-bold text-pink-700">{{ money(totalWithVat) }}</p>
                    </div>
                </div>
            </div>

            <!-- Expense Breakdown Panel -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-pink-500 p-6">
                <h3 class="text-lg font-bold text-pink-700 mb-4">{{ categoryName }} - Expense Breakdown</h3>

                <!-- Table Header -->
                <div class="mb-2">
                    <div class="grid grid-cols-12 gap-2 px-2 py-2 bg-gray-50 rounded-lg font-semibold text-xs text-gray-600 uppercase">
                        <div class="col-span-6">Subcategory</div>
                        <div class="col-span-2 text-right">Amount</div>
                        <div class="col-span-2 text-right">VAT</div>
                        <div class="col-span-2 text-right">Total</div>
                    </div>
                </div>

                <!-- Subcategory List -->
                <ul class="space-y-1">
                    <li v-for="(subcategory, index) in subcategories" :key="subcategory.id" class="group hover:bg-pink-50 rounded-lg p-2 transition-colors cursor-pointer">
                        <div class="grid grid-cols-12 gap-2 items-center">
                            <button @click="filterBySubcategory(subcategory.name)" class="col-span-6 text-sm text-gray-700 hover:text-pink-800 font-medium text-left flex items-center gap-2">
                                <span class="font-bold text-pink-700">{{ String.fromCharCode(65 + index) }}.</span>
                                <span>{{ subcategory.name }}</span>
                                <span class="text-xs text-gray-500">(VAT: {{ subcategory.vat_rate }}%)</span>
                            </button>
                            <div class="col-span-2 text-right">
                                <span class="font-semibold text-green-700">{{ money(getBreakdown(subcategory.name).amount) }}</span>
                            </div>
                            <div class="col-span-2 text-right">
                                <span class="font-semibold text-red-700">{{ money(getBreakdown(subcategory.name).vat_amount) }}</span>
                            </div>
                            <div class="col-span-2 text-right flex items-center justify-end gap-2">
                                <span class="font-bold text-pink-700">{{ money(getBreakdown(subcategory.name).total) }}</span>
                                <button @click="quickAdd(subcategory.name)" class="opacity-0 group-hover:opacity-100 transition-opacity text-green-600 hover:text-green-800 text-xl font-bold" title="Quick Add">
                                    +
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>

                <!-- Total Row -->
                <div class="mt-4 pt-4 border-t-2 border-pink-300">
                    <div class="grid grid-cols-12 gap-2 items-center">
                        <div class="col-span-6">
                            <span class="font-bold text-gray-900">Total {{ categoryName }}:</span>
                        </div>
                        <div class="col-span-2 text-right">
                            <span class="text-xl font-bold text-green-700">{{ money(totalAmount) }}</span>
                        </div>
                        <div class="col-span-2 text-right">
                            <span class="text-xl font-bold text-red-700">{{ money(totalVat) }}</span>
                        </div>
                        <div class="col-span-2 text-right">
                            <span class="text-xl font-bold text-pink-700">{{ money(totalWithVat) }}</span>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subcategory</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client/Staff</th>
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
                            <tr v-if="filteredEntries.length === 0">
                                <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                    {{ filterActive ? 'No entries found for this subcategory.' : 'No operating expense entries yet. Click "Add Entry" to create one.' }}
                                </td>
                            </tr>
                            <tr v-for="entry in filteredEntries" :key="entry.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ entry.subcategory }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <template v-if="entry.staff">
                                        <div>{{ entry.staff.name }}</div>
                                        <div class="text-xs text-gray-500">
                                            Salary: {{ money(entry.salary_amount) }},
                                            Paid: {{ money(entry.paid_amount) }},
                                            Due: {{ money(entry.due_amount) }}
                                        </div>
                                    </template>
                                    <template v-else-if="entry.client">
                                        <div>{{ entry.client.name }}</div>
                                        <div class="text-xs text-gray-500">{{ entry.client.phone_number }}</div>
                                    </template>
                                    <span v-else class="text-gray-400 italic">Organization-wide</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ entry.description }}</td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-green-700">{{ money(entry.amount) }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-700">{{ entry.vat_rate }}%</td>
                                <td class="px-6 py-4 text-sm text-right font-medium text-red-700">{{ money(entry.vat_amount) }}</td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-pink-700">{{ money(entry.amount + entry.vat_amount) }}</td>
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
                        {{ editingEntry ? 'Edit Operating Expense Entry' : 'Add Operating Expense Entry' }}
                    </h2>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div v-if="!isEmployeeCategory">
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
                        <p v-if="form.errors?.client_id" class="text-xs text-red-600">{{ form.errors.client_id }}</p>
                    </div>

                    <div v-else>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Staff Member *</label>
                        <select
                            v-model="form.staff_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                        >
                            <option value="">Select staff</option>
                            <option v-for="staff in officeStaff" :key="staff.id" :value="staff.id">
                                {{ staff.name }}
                            </option>
                        </select>
                        <p v-if="form.errors?.staff_id" class="text-xs text-red-600">{{ form.errors.staff_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subcategory *</label>
                        <SubcategorySelector
                            v-model="form.subcategory"
                            :subcategories="subcategories"
                            type="operating_expenses"
                            :category="category"
                            @update:vatRate="form.vat_rate = $event"
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

                    <div v-if="isSalarySubcategory" class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salary (৳) *</label>
                            <input
                                v-model.number="form.salary_amount"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                placeholder="0.00"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bonus (৳)</label>
                            <input
                                v-model.number="form.bonus_amount"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                placeholder="0.00"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Paid (৳) *</label>
                            <input
                                v-model.number="form.paid_amount"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                placeholder="0.00"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Due (৳)</label>
                            <input
                                :value="money(form.due_amount || 0)"
                                readonly
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                            />
                        </div>
                    </div>

                    <div v-else class="grid grid-cols-2 gap-4">
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">VAT Rate (%)</label>
                            <input
                                v-model.number="form.vat_rate"
                                type="number"
                                readonly
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                            />
                        </div>
                    </div>

                    <div v-if="!isEmployeeCategory && form.vat_rate > 0" class="bg-red-50 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700">Calculated VAT Amount:</span>
                            <span class="text-lg font-bold text-red-700">{{ money(calculatedVat) }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-sm font-medium text-gray-900">Total with VAT:</span>
                            <span class="text-xl font-bold text-pink-700">{{ money(totalWithVatPreview) }}</span>
                        </div>
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
    totalVat: Number,
    totalWithVat: Number,
    expenseBreakdown: Object,
    clients: Array,
    officeStaff: Array,
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
const isEmployeeCategory = computed(() => props.category === 'employee_manpower');
const isSalarySubcategory = computed(() => {
    if (!isEmployeeCategory.value) return false;
    const value = (form.value.subcategory || '').toString().trim().toLowerCase();
    return value.replace(/&/g, 'and') === 'salaries and wages';
});
const setBodyScrollLock = (locked) => {
    document.body.style.overflow = locked ? 'hidden' : '';
};

const form = ref({
    client_id: null,
    staff_id: null,
    subcategory: '',
    description: '',
    amount: 0,
    salary_amount: 0,
    bonus_amount: 0,
    paid_amount: 0,
    due_amount: 0,
    vat_rate: 0,
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

const calculatedVat = computed(() => {
    if (!form.value.amount || !form.value.vat_rate) return 0;
    return (form.value.amount * form.value.vat_rate) / 100;
});

const totalWithVatPreview = computed(() => {
    return (form.value.amount || 0) + calculatedVat.value;
});

watch(
    () => [form.value.salary_amount, form.value.bonus_amount, form.value.paid_amount, isSalarySubcategory.value],
    () => {
        if (!isSalarySubcategory.value) return;
        const salary = Number(form.value.salary_amount || 0);
        const bonus = Number(form.value.bonus_amount || 0);
        const paid = Number(form.value.paid_amount || 0);
        const due = Math.max(0, salary + bonus - paid);
        form.value.due_amount = due;
        form.value.amount = paid;
        form.value.vat_rate = 0;
    }
);

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
};

const getBreakdown = (subcategory) => {
    return props.expenseBreakdown[subcategory] || { amount: 0, vat_amount: 0, total: 0 };
};

const filterBySubcategory = (subcategory) => {
    filterSubcategory.value = subcategory;
    showDetails.value = true;
};

const clearFilter = () => {
    filterSubcategory.value = null;
};

const quickAdd = (subcategoryName) => {
    form.value.subcategory = subcategoryName;
    // Set VAT rate from subcategory
    const selected = props.subcategories.find(sub => sub.name === subcategoryName);
    if (selected) {
        form.value.vat_rate = selected.vat_rate;
    }
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
    if (isEmployeeCategory.value) {
        if (!form.value.staff_id) {
            alert('Please select a staff member');
            return;
        }
    } else {
        if (!form.value.client_id) {
            alert('Please select a client');
            return;
        }
    }

    const data = {
        ...form.value,
        accounting_period_id: props.period.id,
        category: props.category,
    };

    if (editingEntry.value) {
        router.put(`/accounting/operating-expenses/${editingEntry.value.id}`, data, {
            onSuccess: () => closeModal(),
        });
    } else {
        router.post('/accounting/operating-expenses', data, {
            onSuccess: () => closeModal(),
        });
    }
};

const editEntry = (entry) => {
    editingEntry.value = entry;
    form.value = {
        client_id: entry.client_id || null,
        staff_id: entry.staff_id || null,
        subcategory: entry.subcategory,
        description: entry.description,
        amount: entry.amount,
        salary_amount: entry.salary_amount || 0,
        bonus_amount: entry.bonus_amount || 0,
        paid_amount: entry.paid_amount || 0,
        due_amount: entry.due_amount || 0,
        vat_rate: entry.vat_rate,
        notes: entry.notes || '',
    };
    // Set client search value
    if (entry.client) {
        clientSearch.value = `${entry.client.name} (${entry.client.phone_number})`;
    } else if (entry.staff) {
        clientSearch.value = entry.staff.name;
    } else {
        clientSearch.value = 'No Client (Organization-wide)';
    }
};

const deleteEntry = (entry) => {
    if (confirm('Are you sure you want to delete this operating expense entry?')) {
        router.delete(`/accounting/operating-expenses/${entry.id}`);
    }
};

const closeModal = () => {
    showAddModal.value = false;
    editingEntry.value = null;
    clientSearch.value = '';
    showClientDropdown.value = false;
    form.value = {
        client_id: null,
        staff_id: null,
        subcategory: '',
        description: '',
        amount: 0,
        salary_amount: 0,
        bonus_amount: 0,
        paid_amount: 0,
        due_amount: 0,
        vat_rate: 0,
        notes: '',
    };
};
</script>
