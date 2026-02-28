<template>
    <Head title="Tax" />
    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">Tax Management</h1>
                <p class="text-sm text-gray-500">Period: {{ period.name }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <select class="px-4 py-2 border border-gray-200 rounded-full text-sm font-medium bg-white text-gray-700 shadow-sm focus:ring-2 focus:ring-[#1e5b43] outline-none">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                        <a :href="route('accounting.tax.report')" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-excel text-green-600"></i>
                            Excel
                        </a>
                        <a :href="route('accounting.tax.report', { type: 'pdf' })" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf text-red-600"></i>
                            PDF
                        </a>
                    </div>
                </div>

            <!-- Tax Summary -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-8 mb-6">
                <h2 class="text-[20px] font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Total Tax Expenses</h2>

                <div class="space-y-6">
                    <!-- Formula Display -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 flex flex-col justify-center transition-all hover:bg-gray-50 hover:shadow-sm">
                            <div class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Current Tax</div>
                            <div class="text-[32px] font-bold text-amber-600">{{ money(totalCurrentTax) }}</div>
                        </div>
                        <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 flex flex-col justify-center transition-all hover:bg-gray-50 hover:shadow-sm">
                            <div class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Deferred Tax</div>
                            <div class="text-[32px] font-bold text-amber-600">{{ money(totalDeferredTax) }}</div>
                        </div>
                        <div class="bg-white rounded-[24px] shadow-[0_4px_12px_rgba(0,0,0,0.05)] border-2 border-orange-500 p-6 flex flex-col justify-center transform scale-105">
                            <div class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Total Tax</div>
                            <div class="text-[36px] font-bold text-orange-600 leading-none">{{ money(totalTax) }}</div>
                        </div>
                    </div>

                    <!-- Pre/Post Tax Comparison -->
                    <div class="bg-gray-50/80 rounded-[20px] p-6 border border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-6 mt-4">
                        <div class="flex-1 text-center sm:text-left">
                            <div class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-1">Net Profit Before Tax</div>
                            <Link href="/accounting/net-profit-before-tax" class="text-[24px] font-bold text-[#1e5b43] hover:text-[#164230] transition-colors">
                                {{ money(netProfitBeforeTax) }}
                            </Link>
                        </div>
                        <div class="hidden sm:block">
                            <i class="fa-solid fa-arrow-right text-gray-300 text-2xl"></i>
                        </div>
                        <div class="flex-1 text-center sm:text-right">
                            <div class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-1">Net Profit After Tax</div>
                            <div class="text-[28px] font-bold leading-none" :class="netProfitAfterTax >= 0 ? 'text-[#1e5b43]' : 'text-red-500'">
                                {{ money(netProfitAfterTax) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax Entry Sections -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Current Tax -->
                <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-[16px] font-bold text-gray-900 border-b-2 border-amber-500 pb-1 inline-block">Current Tax</h3>
                        <button @click="showAddModal('current')" class="px-4 py-2 bg-[#1e5b43] text-white rounded-full hover:bg-[#164230] transition-colors font-semibold text-xs shadow-sm flex items-center gap-1.5">
                            <i class="fa-solid fa-plus w-3 h-3"></i> Add
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div v-if="currentTaxEntries.length === 0" class="text-center py-10 text-gray-400 text-sm border-2 border-dashed border-gray-100 rounded-[16px]">
                            No current tax entries yet
                        </div>
                        <div v-for="entry in currentTaxEntries" :key="entry.id" class="bg-gray-50/50 rounded-[16px] p-4 border border-transparent hover:border-gray-100 transition-all group">
                            <div class="flex justify-between items-start">
                                <div class="flex-1 pr-4">
                                    <div class="font-bold text-gray-900 text-sm">{{ entry.description }}</div>
                                    <div v-if="entry.client" class="text-[11px] text-emerald-700 font-medium mt-1 bg-emerald-50 inline-block px-2 py-0.5 rounded-md border border-emerald-100">
                                        Client: {{ entry.client.name }} ({{ entry.client.phone_number }})
                                    </div>
                                    <div v-else-if="entry.staff" class="text-[11px] text-blue-700 font-medium mt-1 bg-blue-50 inline-block px-2 py-0.5 rounded-md border border-blue-100">
                                        Staff: {{ entry.staff.name }}
                                    </div>
                                    <div v-else class="text-[11px] text-gray-500 italic mt-1 bg-gray-100 inline-block px-2 py-0.5 rounded-md">Organization-wide</div>
                                    <div v-if="entry.notes" class="text-xs text-gray-500 mt-2">{{ entry.notes }}</div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span class="font-bold text-amber-600 text-[16px]">{{ money(entry.amount) }}</span>
                                    <div class="flex gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="editEntry(entry, 'current')" class="w-7 h-7 rounded-full flex items-center justify-center text-gray-400 hover:text-amber-500 hover:bg-amber-50 transition" title="Edit entry">
                                            <i class="fa-solid fa-pen-to-square w-3 h-3"></i>
                                        </button>
                                        <button @click="deleteEntry(entry)" class="w-7 h-7 rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition" title="Delete entry">
                                            <i class="fa-solid fa-trash w-3 h-3"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 flex justify-between items-center">
                        <span class="text-[11px] uppercase tracking-wider font-bold text-gray-500">Total Current Tax:</span>
                        <span class="text-[20px] font-bold text-amber-600">{{ money(totalCurrentTax) }}</span>
                    </div>
                </div>

                <!-- Deferred Tax -->
                <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-[16px] font-bold text-gray-900 border-b-2 border-orange-500 pb-1 inline-block">Deferred Tax</h3>
                        <button @click="showAddModal('deferred')" class="px-4 py-2 bg-[#1e5b43] text-white rounded-full hover:bg-[#164230] transition-colors font-semibold text-xs shadow-sm flex items-center gap-1.5">
                            <i class="fa-solid fa-plus w-3 h-3"></i> Add
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div v-if="deferredTaxEntries.length === 0" class="text-center py-10 text-gray-400 text-sm border-2 border-dashed border-gray-100 rounded-[16px]">
                            No deferred tax entries yet
                        </div>
                        <div v-for="entry in deferredTaxEntries" :key="entry.id" class="bg-gray-50/50 rounded-[16px] p-4 border border-transparent hover:border-gray-100 transition-all group">
                            <div class="flex justify-between items-start">
                                <div class="flex-1 pr-4">
                                    <div class="font-bold text-gray-900 text-sm">{{ entry.description }}</div>
                                    <div v-if="entry.client" class="text-[11px] text-emerald-700 font-medium mt-1 bg-emerald-50 inline-block px-2 py-0.5 rounded-md border border-emerald-100">
                                        Client: {{ entry.client.name }} ({{ entry.client.phone_number }})
                                    </div>
                                    <div v-else-if="entry.staff" class="text-[11px] text-blue-700 font-medium mt-1 bg-blue-50 inline-block px-2 py-0.5 rounded-md border border-blue-100">
                                        Staff: {{ entry.staff.name }}
                                    </div>
                                    <div v-else class="text-[11px] text-gray-500 italic mt-1 bg-gray-100 inline-block px-2 py-0.5 rounded-md">Organization-wide</div>
                                    <div v-if="entry.notes" class="text-xs text-gray-500 mt-2">{{ entry.notes }}</div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span class="font-bold text-amber-600 text-[16px]">{{ money(entry.amount) }}</span>
                                    <div class="flex gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="editEntry(entry, 'deferred')" class="w-7 h-7 rounded-full flex items-center justify-center text-gray-400 hover:text-amber-500 hover:bg-amber-50 transition" title="Edit entry">
                                            <i class="fa-solid fa-pen-to-square w-3 h-3"></i>
                                        </button>
                                        <button @click="deleteEntry(entry)" class="w-7 h-7 rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition" title="Delete entry">
                                            <i class="fa-solid fa-trash w-3 h-3"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 flex justify-between items-center">
                        <span class="text-[11px] uppercase tracking-wider font-bold text-gray-500">Total Deferred Tax:</span>
                        <span class="text-[20px] font-bold text-amber-600">{{ money(totalDeferredTax) }}</span>
                    </div>
                </div>
            </div>

            <!-- Info Note -->
            <div class="bg-blue-50/50 rounded-[20px] shadow-sm border border-blue-100 p-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-blue-900 mb-2">Tax Entry Guidelines</h4>
                        <ul class="text-sm text-blue-800 space-y-1.5 list-disc pl-4">
                            <li><strong>Current Tax:</strong> Statutory tax liability for the current period based on taxable income.</li>
                            <li><strong>Deferred Tax:</strong> Tax impact of temporary differences between accounting and tax bases.</li>
                            <li>Total tax expenses reduce Net Profit Before Tax to arrive at Net Profit After Tax.</li>
                        </ul>
                    </div>
                </div>
            </div>

        <!-- Add/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 sm:p-6 overflow-y-auto">
            <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden transform transition-all">
                <div class="p-6 border-b border-gray-100 flex-shrink-0 flex items-center justify-between bg-gray-50/50">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ editingEntry ? 'Edit Tax Entry' : `Add ${form.tax_type === 'current' ? 'Current' : 'Deferred'} Tax Entry` }}
                    </h2>
                    <button @click="closeModal" class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 hover:bg-gray-300 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-times w-3.5 h-3.5"></i>
                    </button>
                </div>
                <form @submit.prevent="submitForm" class="flex-1 overflow-y-auto">
                    <div class="p-6 space-y-5">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 mb-2 uppercase tracking-wider">Applies To</label>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        @click="setTargetType('client')"
                                        :class="targetType === 'client' ? 'bg-[#1e5b43] border-[#1e5b43] text-white' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'"
                                        class="px-5 py-2 text-sm font-bold rounded-full border transition-colors shadow-sm"
                                    >
                                        Client
                                    </button>
                                    <button
                                        type="button"
                                        @click="setTargetType('staff')"
                                        :class="targetType === 'staff' ? 'bg-[#1e5b43] border-[#1e5b43] text-white' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'"
                                        class="px-5 py-2 text-sm font-bold rounded-full border transition-colors shadow-sm"
                                    >
                                        Staff
                                    </button>
                                    <button
                                        type="button"
                                        @click="setTargetType('other')"
                                        :class="targetType === 'other' ? 'bg-[#1e5b43] border-[#1e5b43] text-white' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'"
                                        class="px-5 py-2 text-sm font-bold rounded-full border transition-colors shadow-sm"
                                    >
                                        Other (Organization-wide)
                                    </button>
                                </div>
                            </div>
                            <div v-if="targetType === 'client'">
                                <label class="block text-[11px] font-bold text-gray-700 mb-1.5 uppercase tracking-wider">Client *</label>
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
                            <div v-else-if="targetType === 'staff'">
                                <label class="block text-[11px] font-bold text-gray-700 mb-1.5 uppercase tracking-wider">Staff *</label>
                                <div class="relative" ref="staffDropdownRef">
                                    <input
                                        v-model="staffSearch"
                                        @input="filterStaff"
                                        @focus="showStaffDropdown = true"
                                        type="text"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent text-sm transition-shadow"
                                        placeholder="Search by staff name..."
                                    />
                                    <div
                                        v-if="showStaffDropdown"
                                        class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-auto py-1"
                                    >
                                        <div
                                            v-for="staff in filteredStaff"
                                            :key="staff.id"
                                            @click="selectStaff(staff)"
                                            class="px-4 py-2 cursor-pointer hover:bg-gray-50 border-b border-gray-50 last:border-0"
                                        >
                                            <div class="font-bold text-sm text-gray-900">{{ staff.name }}</div>
                                        </div>
                                        <div v-if="filteredStaff.length === 0 && staffSearch" class="px-4 py-3 text-sm text-gray-500 italic text-center">
                                            No staff found
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="rounded-[16px] border border-dashed border-gray-200 bg-gray-50 p-4 text-xs text-gray-600 flex items-center gap-3">
                                <i class="fa-solid fa-circle-info text-blue-500 text-lg"></i>
                                Organization-wide tax entry. No client or staff will be linked.
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1.5 uppercase tracking-wider">Heading</label>
                            <input
                                v-model="form.description"
                                type="text"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent text-sm transition-shadow"
                                placeholder="e.g., Corporate Income Tax, Tax on Temporary Differences"
                            />
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1.5 uppercase tracking-wider">Amount (৳) *</label>
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
                            <label class="block text-[11px] font-bold text-gray-700 mb-1.5 uppercase tracking-wider">Notes</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent text-sm transition-shadow"
                                placeholder="Additional details (optional)"
                            ></textarea>
                        </div>
                    </div>
                </form>
                <div class="p-5 border-t border-gray-100 bg-gray-50 flex-shrink-0 flex justify-end gap-3 rounded-b-[24px]">
                    <button
                        type="button"
                        @click="closeModal"
                        class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-bold hover:bg-gray-50 transition-colors shadow-sm"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
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
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import IconButton from '@/Components/Buttons/IconButton.vue';

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
    officeStaff: Array,
});

const showModal = ref(false);
const editingEntry = ref(null);
const targetType = ref('other');
const clientSearch = ref('');
const filteredClients = ref(props.clients || []);
const showClientDropdown = ref(false);
const clientDropdownRef = ref(null);
const staffSearch = ref('');
const filteredStaff = ref(props.officeStaff || []);
const showStaffDropdown = ref(false);
const staffDropdownRef = ref(null);
const setBodyScrollLock = (locked) => {
    document.body.style.overflow = locked ? 'hidden' : '';
};

const form = ref({
    client_id: null,
    staff_id: null,
    tax_type: '',
    description: '',
    amount: 0,
    notes: '',
});

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (clientDropdownRef.value && !clientDropdownRef.value.contains(event.target)) {
        showClientDropdown.value = false;
    }
    if (staffDropdownRef.value && !staffDropdownRef.value.contains(event.target)) {
        showStaffDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    setBodyScrollLock(false);
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

const filterStaff = () => {
    const search = staffSearch.value.toLowerCase();
    if (!search) {
        filteredStaff.value = props.officeStaff || [];
    } else {
        filteredStaff.value = (props.officeStaff || []).filter((staff) =>
            staff.name.toLowerCase().includes(search)
        );
    }
};

const selectClient = (client) => {
    form.value.client_id = client.id;
    clientSearch.value = `${client.name} (${client.phone_number})`;
    showClientDropdown.value = false;
};

const selectStaff = (staff) => {
    form.value.staff_id = staff.id;
    staffSearch.value = staff.name;
    showStaffDropdown.value = false;
};

const setTargetType = (type) => {
    targetType.value = type;
    if (type === 'client') {
        form.value.staff_id = null;
        staffSearch.value = '';
    } else if (type === 'staff') {
        form.value.client_id = null;
        clientSearch.value = '';
    } else {
        form.value.client_id = null;
        form.value.staff_id = null;
        clientSearch.value = '';
        staffSearch.value = '';
    }
};

const showAddModal = (taxType) => {
    form.value.tax_type = taxType;
    targetType.value = 'other';
    showModal.value = true;
};

const submitForm = () => {
    if (targetType.value === 'client' && !form.value.client_id) {
        alert('Please select a client.');
        return;
    }
    if (targetType.value === 'staff' && !form.value.staff_id) {
        alert('Please select a staff member.');
        return;
    }
    if (targetType.value === 'other') {
        form.value.client_id = null;
        form.value.staff_id = null;
    }

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
        staff_id: entry.staff_id || null,
        tax_type: taxType,
        description: entry.description,
        amount: entry.amount,
        notes: entry.notes || '',
    };

    // Set client search value if editing
    if (entry.client_id && entry.client) {
        targetType.value = 'client';
        clientSearch.value = `${entry.client.name} (${entry.client.phone_number})`;
        staffSearch.value = '';
    } else if (entry.staff_id && entry.staff) {
        targetType.value = 'staff';
        staffSearch.value = entry.staff.name;
        clientSearch.value = '';
    } else {
        targetType.value = 'other';
        clientSearch.value = '';
        staffSearch.value = '';
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
    targetType.value = 'other';
    form.value = {
        client_id: null,
        staff_id: null,
        tax_type: '',
        description: '',
        amount: 0,
        notes: '',
    };
    clientSearch.value = '';
    staffSearch.value = '';
    filteredClients.value = props.clients || [];
    filteredStaff.value = props.officeStaff || [];
    showClientDropdown.value = false;
    showStaffDropdown.value = false;
};

watch(
    () => showModal.value,
    (isOpen) => setBodyScrollLock(isOpen)
);
</script>
