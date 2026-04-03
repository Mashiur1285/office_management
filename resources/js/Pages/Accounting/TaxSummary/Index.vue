<template>
    <Head title="Tax Summary" />
    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">Tax Summary & Payments</h1>
                <p class="text-sm text-gray-500">Period: {{ period.name }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <select class="px-4 py-2 border border-gray-200 rounded-full text-sm font-medium bg-white text-gray-700 shadow-sm focus:ring-2 focus:ring-[#1e5b43] outline-none">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                        <a :href="route('accounting.tax-summary.report')" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-excel text-green-600"></i>
                            Excel
                        </a>
                        <a :href="route('accounting.tax-summary.report', { type: 'pdf' })" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf text-red-600"></i>
                            PDF
                        </a>
                    </div>
                </div>

            <!-- Total Tax Summary with Payments -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-8 mb-6">
                <h2 class="text-[20px] font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Tax Payment Summary</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 flex flex-col justify-center transition-all hover:bg-gray-50 hover:shadow-sm">
                        <div class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Total Tax</div>
                        <div class="text-[32px] font-bold text-amber-600">
                            {{ money(totalTax) }}
                        </div>
                    </div>

                    <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 flex flex-col justify-center transition-all hover:bg-gray-50 hover:shadow-sm">
                        <div class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Total Payments Made</div>
                        <div class="text-[32px] font-bold text-[#1e5b43]">
                            {{ money(totalTaxPayments) }}
                        </div>
                    </div>

                    <div :class="['rounded-[20px] p-6 border border-gray-100 shadow-[0_4px_12px_rgba(0,0,0,0.05)] transform scale-105 flex flex-col justify-center', taxBalance > 0 ? 'bg-orange-50 border-orange-200' : 'bg-[#1e5b43] text-white']">
                        <div :class="['text-[12px] uppercase tracking-wider font-bold mb-2', taxBalance > 0 ? 'text-gray-500' : 'text-emerald-100/90']">Remaining Balance</div>
                        <div :class="['text-[36px] font-bold leading-none', taxBalance > 0 ? 'text-orange-600' : 'text-white']">
                            {{ money(taxBalance) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax Breakdown -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-8 mb-6">
                <h2 class="text-[20px] font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Tax Breakdown</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 transition-all hover:bg-gray-50 hover:shadow-sm">
                        <div class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Current Tax</div>
                        <div class="text-[28px] font-bold text-gray-800">
                            {{ money(taxByType.current) }}
                        </div>
                    </div>
                    <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 transition-all hover:bg-gray-50 hover:shadow-sm">
                        <div class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Deferred Tax</div>
                        <div class="text-[28px] font-bold text-gray-800">
                            {{ money(taxByType.deferred) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax Payment Form -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-8 mb-6">
                <h2 class="text-[20px] font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Record Tax Payment</h2>
                <form @submit.prevent="submitPayment" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-2 uppercase tracking-wider">Payment Type *</label>
                            <select
                                v-model="paymentForm.payment_type"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm font-medium bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent transition-colors outline-none cursor-pointer"
                                required
                            >
                                <option value="bulk">Bulk Payment (All Clients)</option>
                                <option value="individual">Individual Client Payment</option>
                            </select>
                        </div>

                        <div v-if="paymentForm.payment_type === 'individual'">
                            <label class="block text-[11px] font-bold text-gray-700 mb-2 uppercase tracking-wider">Select Client *</label>
                            <select
                                v-model="paymentForm.client_id"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm font-medium bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent transition-colors outline-none cursor-pointer"
                                required
                            >
                                <option :value="null">-- Select Client --</option>
                                <option v-for="client in clientsWithTax.filter(c => !c.tax_paid)" :key="client.id" :value="client.id">
                                    {{ client.name }} (Unpaid Tax: {{ money(client.tax_unpaid_amount) }})
                                </option>
                            </select>
                        </div>

                        <div v-if="paymentForm.payment_type === 'individual'">
                            <label class="block text-[11px] font-bold text-gray-700 mb-2 uppercase tracking-wider">Payment Amount *</label>
                            <input
                                v-model="paymentForm.payment_amount"
                                type="number"
                                step="0.01"
                                required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm font-medium bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent transition-colors outline-none"
                                placeholder="0.00"
                            />
                        </div>

                        <div v-else class="p-5 bg-gray-50 border border-gray-200 rounded-[16px]">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[12px] uppercase tracking-wider font-bold text-gray-500">Total Payment Amount:</span>
                                <span class="text-[24px] font-bold text-[#1e5b43]">{{ money(taxBalance) }}</span>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">This amount will be distributed across all clients with unpaid tax</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-2 uppercase tracking-wider">Payment Date *</label>
                            <div class="theme-datepicker">
                                <VueDatePicker
                                    v-model="paymentForm.payment_date"
                                    :enable-time-picker="false"
                                    model-type="yyyy-MM-dd"
                                    required
                                    class="w-full"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-2 uppercase tracking-wider">Chalan Number *</label>
                            <input
                                v-model="paymentForm.chalan_number"
                                type="text"
                                required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm font-medium bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent transition-colors outline-none"
                                placeholder="Enter chalan number"
                            />
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-2 uppercase tracking-wider">Chalan Slip (Optional)</label>
                            <input
                                ref="chalanSlipInput"
                                type="file"
                                @change="handleChalanSlipChange"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full px-4 py-3 border border-gray-200 rounded-[12px] text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#1e5b43] file:text-white hover:file:bg-[#164230]"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-bold text-gray-700 mb-2 uppercase tracking-wider">Notes</label>
                            <textarea
                                v-model="paymentForm.notes"
                                rows="3"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent transition-colors outline-none resize-none"
                                placeholder="Any additional notes..."
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="px-8 py-3 bg-[#1e5b43] text-white rounded-full hover:bg-[#164230] transition-colors font-bold text-sm shadow-[0_4px_12px_rgba(30,91,67,0.2)] disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ isSubmitting ? 'Saving...' : 'Record Payment' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Clients with Tax Due -->
            <div v-if="clientsWithTax && clientsWithTax.length > 0" class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 overflow-hidden mb-6">
                <button
                    @click="showClientsWithTax = !showClientsWithTax"
                    class="w-full p-6 flex items-center justify-between hover:bg-gray-50 transition-colors border-b border-gray-100"
                >
                    <div class="text-left">
                        <h2 class="text-[20px] font-bold text-gray-900">Clients with Tax Due</h2>
                        <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mt-1">{{ clientsWithTax.length }} client(s) with tax due</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 transition-transform duration-300" :class="{ 'rotate-180': showClientsWithTax }">
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                </button>

                <div v-show="showClientsWithTax" class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-[11px] text-gray-500 uppercase font-bold tracking-wider bg-gray-50/50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4">Client Name</th>
                                <th class="px-6 py-4 text-right">Total Tax</th>
                                <th class="px-6 py-4 text-right">Paid</th>
                                <th class="px-6 py-4 text-right">Unpaid</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4">Payment Info</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="client in clientsWithTax" :key="client.id" :class="client.tax_paid ? 'bg-emerald-50/30' : 'hover:bg-gray-50/50 transition-colors'">
                                <td class="px-6 py-4 font-bold text-gray-900 border-x-0">
                                    {{ client.name }}
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900 text-right">
                                    {{ money(client.total_tax_receivable) }}
                                </td>
                                <td class="px-6 py-4 text-[#1e5b43] font-semibold text-right">
                                    {{ money(client.tax_paid_amount) }}
                                </td>
                                <td class="px-6 py-4 font-bold text-right" :class="client.tax_paid ? 'text-gray-400' : 'text-orange-600'">
                                    {{ money(client.tax_unpaid_amount) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span v-if="client.tax_paid" class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-md text-[11px] font-bold uppercase tracking-wider">
                                        Fully Paid
                                    </span>
                                    <span v-else-if="client.tax_paid_amount > 0" class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-md text-[11px] font-bold uppercase tracking-wider">
                                        Partially Paid
                                    </span>
                                    <span v-else class="px-3 py-1 bg-orange-100 text-orange-800 rounded-md text-[11px] font-bold uppercase tracking-wider">
                                        Pending
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500">
                                    <div v-if="client.tax_paid_amount > 0">
                                        <div v-if="client.tax_chalan_number" class="text-xs mb-0.5"><span class="font-semibold text-gray-700">Chalan:</span> {{ client.tax_chalan_number }}</div>
                                        <div v-if="client.tax_payment_date" class="text-xs"><span class="font-semibold text-gray-700">Date:</span> {{ formatDate(client.tax_payment_date) }}</div>
                                    </div>
                                    <div v-else class="text-gray-400">—</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tax Payment History -->
            <div v-if="taxPayments && taxPayments.length > 0" class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-100 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between bg-gray-50/50">
                    <div>
                        <h2 class="text-[20px] font-bold text-gray-900">Tax Payment History</h2>
                        <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mt-1">All recorded payments for this period</p>
                    </div>
                    <div class="flex-1 max-w-md relative">
                        <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            v-model="searchPayments"
                            type="text"
                            class="w-full bg-white border border-gray-200 rounded-full pl-10 pr-10 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent transition-all shadow-sm"
                            placeholder="Search by challan number, client, date..."
                        />
                        <button
                            v-if="searchPayments"
                            @click="searchPayments = ''"
                            class="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 transition-colors"
                        >
                            <font-awesome-icon icon="xmark" class="text-xs" />
                        </button>
                    </div>
                </div>
                <div v-if="searchPayments" class="bg-blue-50 px-6 py-2 border-b border-blue-100 text-sm text-blue-800">
                    Showing <span class="font-bold">{{ filteredTaxPayments.length }}</span> of {{ taxPayments.length }} payments
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-[11px] text-gray-500 uppercase font-bold tracking-wider bg-gray-50/50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Type</th>
                                <th class="px-6 py-4">Client</th>
                                <th class="px-6 py-4">Amount</th>
                                <th class="px-6 py-4">Chalan Number</th>
                                <th class="px-6 py-4">Notes</th>
                                <th class="px-6 py-4 text-center">Chalan Slip</th>
                                <th class="px-6 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="payment in filteredTaxPayments" :key="payment.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-900 border-x-0">
                                    {{ formatDate(payment.payment_date) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-md text-[11px] font-bold uppercase tracking-wider">
                                        {{ payment.payment_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-medium">
                                    {{ payment.client_name || 'Organization-wide' }}
                                </td>
                                <td class="px-6 py-4 font-bold text-[#1e5b43]">
                                    {{ money(payment.payment_amount) }}
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-gray-600 font-semibold bg-gray-50 rounded select-all inline-block mt-2 px-1">
                                    {{ payment.chalan_number || '—' }}
                                </td>
                                <td class="px-6 py-4 text-gray-500">
                                    {{ payment.notes || '—' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a
                                        v-if="payment.chalan_slip"
                                        :href="`/storage/${payment.chalan_slip}`"
                                        target="_blank"
                                        class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center mx-auto transition-colors"
                                        title="View Chalan Slip"
                                    >
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>
                                    <span v-else class="text-sm text-gray-300">—</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button
                                        @click="deletePaymentRecord(payment.id)"
                                        class="w-8 h-8 rounded-full bg-gray-50 text-red-500 hover:bg-red-50 flex items-center justify-center mx-auto transition-colors"
                                        title="Delete Payment"
                                    >
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredTaxPayments.length === 0 && taxPayments.length > 0">
                                <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                                    <i class="fa-solid fa-search text-gray-300 text-2xl mb-3 block"></i>
                                    No payments found matching your search.
                                    <button @click="searchPayments = ''" class="text-[#1e5b43] font-bold hover:underline ml-1">Clear search</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pb-8">
                <Link href="/accounting/tax" class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-[#1e5b43]/20 p-6 flex items-center justify-between group hover:border-[#1e5b43]/40 transition-colors">
                    <div>
                        <div class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Previous</div>
                        <div class="font-bold text-gray-900 group-hover:text-[#1e5b43] transition-colors">Tax Management</div>
                    </div>
                    <i class="fa-solid fa-arrow-left text-gray-300 group-hover:-translate-x-1 group-hover:text-[#1e5b43] transition-all"></i>
                </Link>
                <Link href="/accounting/net-profit-after-tax" class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-[#1e5b43]/20 p-6 flex flex-col justify-center text-center group hover:border-[#1e5b43]/40 transition-colors relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-[#1e5b43]/5 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                    <div class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">P&L Summary</div>
                    <div class="font-bold text-gray-900 group-hover:text-[#1e5b43] transition-colors">Net Profit After Tax</div>
                </Link>
                <Link href="/accounting" class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-[#1e5b43]/20 p-6 flex items-center justify-between group hover:border-[#1e5b43]/40 transition-colors">
                    <div>
                        <div class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Dashboard</div>
                        <div class="font-bold text-gray-900 group-hover:text-[#1e5b43] transition-colors">Accounting Home</div>
                    </div>
                    <i class="fa-solid fa-house text-gray-300 group-hover:text-[#1e5b43] transition-colors"></i>
                </Link>
            </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const props = defineProps({
    period: Object,
    periods: Array,
    taxByType: Object,
    totalTax: Number,
    taxPayments: Array,
    totalTaxPayments: Number,
    taxBalance: Number,
    clientsWithTax: Array,
});

const chalanSlipInput = ref(null);
const isSubmitting = ref(false);
const showClientsWithTax = ref(false);
const searchPayments = ref("");

const filteredTaxPayments = computed(() => {
    if (!props.taxPayments) return [];
    if (!searchPayments.value) return props.taxPayments;

    const query = searchPayments.value.toLowerCase();
    return props.taxPayments.filter((payment) => {
        return (
            payment.chalan_number?.toLowerCase().includes(query) ||
            payment.client_name?.toLowerCase().includes(query) ||
            payment.payment_type?.toLowerCase().includes(query) ||
            payment.notes?.toLowerCase().includes(query) ||
            payment.payment_date?.toLowerCase().includes(query)
        );
    });
});

const paymentForm = ref({
    payment_type: 'bulk',
    client_id: null,
    payment_amount: '',
    payment_date: new Date().toISOString().split('T')[0],
    chalan_number: '',
    chalan_slip: null,
    notes: '',
});

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-BD', { year: 'numeric', month: 'short', day: 'numeric' });
};

const handleChalanSlipChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        paymentForm.value.chalan_slip = file;
    }
};

const submitPayment = () => {
    if (isSubmitting.value) return;

    if (!paymentForm.value.chalan_number || paymentForm.value.chalan_number.trim() === '') {
        alert('Please enter a Chalan Number.');
        return;
    }

    let paymentAmount;
    if (paymentForm.value.payment_type === 'bulk') {
        paymentAmount = props.taxBalance;
    } else {
        paymentAmount = parseFloat(paymentForm.value.payment_amount) || 0;
    }

    if (paymentForm.value.payment_type === 'individual') {
        if (!paymentForm.value.client_id) {
            alert('Please select a client for individual payment.');
            return;
        }

        const selectedClient = props.clientsWithTax.find(c => c.id === paymentForm.value.client_id);
        if (selectedClient && paymentAmount > selectedClient.tax_unpaid_amount) {
            alert(`Payment amount ${money(paymentAmount)} exceeds client unpaid tax ${money(selectedClient.tax_unpaid_amount)}.`);
            return;
        }
    }

    isSubmitting.value = true;

    const formData = new FormData();
    formData.append('payment_type', paymentForm.value.payment_type);
    if (paymentForm.value.client_id) {
        formData.append('client_id', paymentForm.value.client_id);
    }
    formData.append('payment_amount', paymentAmount);
    formData.append('payment_date', paymentForm.value.payment_date);
    formData.append('chalan_number', paymentForm.value.chalan_number);

    if (paymentForm.value.chalan_slip) {
        formData.append('chalan_slip', paymentForm.value.chalan_slip);
    }
    if (paymentForm.value.notes) {
        formData.append('notes', paymentForm.value.notes);
    }

    router.post(`/accounting/tax-summary/${props.period.id}/payment`, formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            paymentForm.value = {
                payment_type: 'bulk',
                client_id: null,
                payment_amount: '',
                payment_date: new Date().toISOString().split('T')[0],
                chalan_number: '',
                chalan_slip: null,
                notes: '',
            };

            if (chalanSlipInput.value) {
                chalanSlipInput.value.value = '';
            }

            isSubmitting.value = false;
        },
        onError: (errors) => {
            console.error('Payment submission error:', errors);
            alert('Failed to record payment. Please check the form and try again.');
            isSubmitting.value = false;
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

const deletePaymentRecord = (paymentId) => {
    if (!confirm('Are you sure you want to delete this payment record?')) return;

    router.delete(`/accounting/tax-payment/${paymentId}`, {
        preserveScroll: true,
    });
};
</script>
