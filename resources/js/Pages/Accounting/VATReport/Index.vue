<template>
    <Head title="VAT Report" />
    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">VAT Report</h1>
                <p class="text-sm text-gray-500">Monthly VAT summary with payments and balance</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <select class="px-4 py-2 border border-gray-200 rounded-full text-sm font-medium bg-white text-gray-700 shadow-sm focus:ring-2 focus:ring-[#1d4ed8] outline-none">
                    <option v-for="p in periods" :key="p.id" :value="p.id" :selected="periods.length > 0 && p.id === periods[0].id">
                        {{ p.name }} ({{ p.type }})
                    </option>
                </select>
                <a :href="route('accounting.vat-report.report')" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-file-excel text-blue-600"></i>
                    Excel
                </a>
                <a :href="route('accounting.vat-report.report', { type: 'pdf' })" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-file-pdf text-red-600"></i>
                    PDF
                </a>
                <Link href="/accounting/vat-summary" class="bg-[#1d4ed8] text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-[#1e40af] transition shadow-sm border border-transparent">
                    VAT Summary
                </Link>
            </div>
        </div>

        <!-- Report Table -->
        <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 overflow-hidden mb-6">
            <div class="p-6 border-b border-gray-100 flex flex-col justify-center bg-gray-50/50">
                <h2 class="text-[20px] font-bold text-gray-900">VAT Overview - All Periods</h2>
                <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mt-1">Complete VAT payment tracking across all accounting periods</p>
            </div>
            <div class="bg-white border-b border-gray-100 px-6 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-[12px] uppercase tracking-wider font-bold text-gray-500">Search by period or chalan number</div>
                    <div class="flex w-full max-w-md relative">
                        <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            v-model="searchTerm"
                            type="text"
                            class="w-full bg-gray-50 border border-gray-200 rounded-full pl-10 pr-10 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1d4ed8] focus:border-transparent transition-all"
                            placeholder="e.g. 2024-Q1 or CH-12345"
                        />
                        <button
                            v-if="searchTerm"
                            @click="searchTerm = ''"
                            class="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center rounded-full bg-gray-200 text-gray-600 hover:bg-gray-300 transition-colors"
                        >
                            <font-awesome-icon icon="xmark" class="text-xs" />
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-[11px] text-gray-500 uppercase font-bold tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Period</th>
                            <th class="px-6 py-4">Date Range</th>
                            <th class="px-6 py-4 text-right">Total VAT</th>
                            <th class="px-6 py-4 text-right">Payments</th>
                            <th class="px-6 py-4 text-right">Balance</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template v-for="period in filteredPeriods" :key="period.id">
                            <tr class="hover:bg-gray-50/50 cursor-pointer transition-colors" @click="togglePeriod(period.id)">
                                <td class="px-6 py-4 font-bold text-gray-900 border-x-0">
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 rounded bg-gray-100 flex items-center justify-center transition-transform text-gray-500" :class="expandedPeriods.includes(period.id) ? 'rotate-90 text-[#1d4ed8] bg-blue-50' : ''">
                                            <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                        </div>
                                        {{ period.name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-medium">
                                    {{ formatDate(period.start_date) }} - {{ formatDate(period.end_date) }}
                                </td>
                                <td class="px-6 py-4 font-bold text-right text-gray-900">
                                    {{ money(period.total_vat) }}
                                </td>
                                <td class="px-6 py-4 font-bold text-right text-[#1d4ed8]">
                                    {{ money(period.total_payments) }}
                                </td>
                                <td class="px-6 py-4 font-bold text-right" :class="period.balance > 0 ? 'text-orange-600' : 'text-gray-400'">
                                    {{ money(period.balance) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="['px-3 py-1 text-[11px] font-bold rounded-md uppercase tracking-wider',
                                        period.status === 'active' ? 'bg-blue-100 text-blue-800' :
                                        period.status === 'closed' ? 'bg-gray-100 text-gray-600' :
                                        'bg-blue-100 text-blue-800']">
                                        {{ period.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button
                                        v-if="getChalanFileCount(period) > 0"
                                        @click.stop="togglePeriod(period.id)"
                                        class="inline-flex items-center px-4 py-1.5 bg-blue-50 text-[#1d4ed8] rounded-full hover:bg-blue-100 border border-blue-100 transition-colors text-[12px] font-bold uppercase tracking-wider"
                                    >
                                        <i class="fa-solid fa-file-invoice mr-2"></i>
                                        View Files ({{ getChalanFileCount(period) }})
                                    </button>
                                    <span v-else class="text-xs text-gray-400 font-medium">No files</span>
                                </td>
                            </tr>

                            <!-- Expanded Payment Details -->
                            <tr v-if="expandedPeriods.includes(period.id) && getFilteredPayments(period).length > 0" class="bg-gray-50/30">
                                <td colspan="7" class="px-6 py-4 shadow-inner">
                                    <div class="bg-white rounded-[16px] p-5 shadow-sm border border-gray-100">
                                        <h3 class="text-[12px] font-bold text-gray-500 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Payment Details for {{ period.name }}</h3>
                                        <table class="w-full text-sm text-left">
                                            <thead class="text-[11px] text-gray-400 uppercase font-bold tracking-wider">
                                                <tr>
                                                    <th class="px-4 py-2">Date</th>
                                                    <th class="px-4 py-2">Amount</th>
                                                    <th class="px-4 py-2">Chalan Number</th>
                                                    <th class="px-4 py-2">Notes</th>
                                                    <th class="px-4 py-2 text-center">Chalan Slip</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-50">
                                                <tr v-for="payment in getFilteredPayments(period)" :key="payment.id" class="hover:bg-gray-50 transition-colors">
                                                    <td class="px-4 py-3 font-medium text-gray-700">{{ formatDate(payment.payment_date) }}</td>
                                                    <td class="px-4 py-3 font-bold text-[#1d4ed8]">{{ money(payment.payment_amount) }}</td>
                                                    <td class="px-4 py-3 font-mono text-xs text-gray-600 font-semibold bg-gray-50 rounded px-1.5 py-0.5 inline-block mt-2">{{ payment.chalan_number || '—' }}</td>
                                                    <td class="px-4 py-3 text-gray-500">{{ payment.notes || '—' }}</td>
                                                    <td class="px-4 py-3 text-center">
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
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>

                            <!-- No Payments Message -->
                            <tr v-if="expandedPeriods.includes(period.id) && getFilteredPayments(period).length === 0" class="bg-gray-50/30">
                                <td colspan="7" class="px-6 py-6 text-center text-sm font-medium text-gray-500 shadow-inner">
                                    {{ searchTerm ? 'No payments match this search' : 'No payments recorded for this period' }}
                                </td>
                            </tr>
                        </template>

                        <!-- Empty State -->
                        <tr v-if="filteredPeriods.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fa-solid fa-folder-open text-gray-300 text-3xl mb-3 block"></i>
                                <div class="text-sm font-bold text-gray-900">{{ searchTerm ? 'No matching periods found' : 'No accounting periods found' }}</div>
                                <div class="text-xs text-gray-400 mt-1 font-medium">
                                    {{ searchTerm ? 'Try a different period name or chalan number.' : 'Create an accounting period to start tracking VAT.' }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pb-8">
            <div class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-6 flex flex-col justify-center">
                <div class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Total VAT (All Periods)</div>
                <div class="text-[32px] font-bold text-gray-900">{{ money(totalAllVat) }}</div>
            </div>
            <div class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-6 flex flex-col justify-center">
                <div class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Total Payments (All Periods)</div>
                <div class="text-[32px] font-bold text-[#1d4ed8]">{{ money(totalAllPayments) }}</div>
            </div>
            <div :class="['rounded-[20px] shadow-[0_4px_12px_rgba(0,0,0,0.05)] border border-gray-100 p-6 flex flex-col justify-center transform scale-105', totalBalance > 0 ? 'bg-orange-50 border-orange-200' : 'bg-[#1d4ed8] text-white']">
                <div :class="['text-[12px] uppercase tracking-wider font-bold mb-2', totalBalance > 0 ? 'text-gray-500' : 'text-blue-100/90']">Outstanding Balance</div>
                <div :class="['text-[36px] font-bold leading-none', totalBalance > 0 ? 'text-orange-600' : 'text-white']">
                    {{ money(totalBalance) }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    periods: Array,
});

const expandedPeriods = ref([]);
const searchTerm = ref('');

const money = (value) => {
    if (value === null || value === undefined) return '৳0';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-BD', { year: 'numeric', month: 'short', day: 'numeric' });
};

const togglePeriod = (periodId) => {
    const index = expandedPeriods.value.indexOf(periodId);
    if (index > -1) {
        expandedPeriods.value.splice(index, 1);
    } else {
        expandedPeriods.value.push(periodId);
    }
};

const getChalanFileCount = (period) => {
    if (!period.payments) return 0;
    return period.payments.filter(payment => payment.chalan_slip).length;
};

const normalize = (value) => {
    if (value === null || value === undefined) return '';
    return String(value).toLowerCase();
};

const getFilteredPayments = (period) => {
    if (!period.payments) return [];
    if (!searchTerm.value) return period.payments;
    const term = normalize(searchTerm.value);
    return period.payments.filter(payment => normalize(payment.chalan_number).includes(term));
};

const filteredPeriods = computed(() => {
    if (!searchTerm.value) return props.periods;
    const term = normalize(searchTerm.value);
    return props.periods.filter((period) => {
        const periodMatch =
            normalize(period.name).includes(term) ||
            normalize(period.status).includes(term) ||
            normalize(period.start_date).includes(term) ||
            normalize(period.end_date).includes(term);
        return periodMatch || getFilteredPayments(period).length > 0;
    });
});

const totalAllVat = computed(() => {
    return props.periods.reduce((sum, period) => sum + period.total_vat, 0);
});

const totalAllPayments = computed(() => {
    return props.periods.reduce((sum, period) => sum + period.total_payments, 0);
});

const totalBalance = computed(() => {
    return props.periods.reduce((sum, period) => sum + period.balance, 0);
});
</script>
