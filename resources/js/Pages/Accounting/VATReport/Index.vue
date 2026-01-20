<template>
    <Head title="VAT Report" />
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-purple-50/30 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-800 text-white shadow-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white">VAT Report</h1>
                        <p class="text-sm text-blue-100 mt-1">Monthly VAT summary with payments and balance</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select class="px-4 py-2 pr-10 border border-gray-300 rounded-lg text-sm font-medium bg-white text-gray-900">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="periods.length > 0 && p.id === periods[0].id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                        <a :href="route('accounting.vat-report.report')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium text-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-excel"></i>
                            Export to Excel
                        </a>
                        <a :href="route('accounting.vat-report.report', { type: 'pdf' })" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf"></i>
                            Export to PDF
                        </a>
                        <Link href="/accounting/vat-summary" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium text-sm">
                            Go to VAT Summary
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Report Table -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-purple-500 overflow-hidden">
                <div class="bg-purple-600 text-white p-4">
                    <h2 class="text-xl font-bold">VAT Overview - All Periods</h2>
                    <p class="text-sm text-purple-100 mt-1">Complete VAT payment tracking across all accounting periods</p>
                </div>
                <div class="bg-white border-b border-purple-100 px-4 py-3">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm font-medium text-gray-700">Search by period or chalan number</div>
                        <div class="flex w-full max-w-md items-center gap-2">
                            <input
                                v-model="searchTerm"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200"
                                placeholder="e.g. 2024-Q1 or CH-12345"
                            />
                            <button
                                v-if="searchTerm"
                                @click="searchTerm = ''"
                                class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-purple-50 border-b-2 border-purple-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-purple-900 uppercase tracking-wider">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-purple-900 uppercase tracking-wider">Date Range</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-purple-900 uppercase tracking-wider">Total VAT</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-purple-900 uppercase tracking-wider">Payments</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-purple-900 uppercase tracking-wider">Balance</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-purple-900 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-purple-900 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <template v-for="period in filteredPeriods" :key="period.id">
                                <tr class="hover:bg-gray-50 cursor-pointer" @click="togglePeriod(period.id)">
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                        <div class="flex items-center gap-2">
                                            <svg :class="['w-4 h-4 transition-transform', expandedPeriods.includes(period.id) ? 'rotate-90' : '']" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ period.name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ formatDate(period.start_date) }} - {{ formatDate(period.end_date) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-right text-red-700">
                                        {{ money(period.total_vat) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-right text-blue-700">
                                        {{ money(period.total_payments) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-right" :class="period.balance > 0 ? 'text-orange-700' : 'text-green-700'">
                                        {{ money(period.balance) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span :class="['px-2 py-1 text-xs font-semibold rounded-full capitalize',
                                            period.status === 'active' ? 'bg-green-100 text-green-800' :
                                            period.status === 'closed' ? 'bg-gray-100 text-gray-800' :
                                            'bg-blue-100 text-blue-800']">
                                            {{ period.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button
                                            v-if="getChalanFileCount(period) > 0"
                                            @click.stop="togglePeriod(period.id)"
                                            class="inline-flex items-center px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View Files ({{ getChalanFileCount(period) }})
                                        </button>
                                        <span v-else class="text-sm text-gray-400">No files</span>
                                    </td>
                                </tr>

                                <!-- Expanded Payment Details -->
                                <tr v-if="expandedPeriods.includes(period.id) && getFilteredPayments(period).length > 0" class="bg-purple-50">
                                    <td colspan="7" class="px-6 py-4">
                                        <div class="bg-white rounded-lg p-4 shadow-sm">
                                            <h3 class="text-sm font-bold text-purple-900 mb-3">Payment Details for {{ period.name }}</h3>
                                            <table class="w-full">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Date</th>
                                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Amount</th>
                                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Chalan Number</th>
                                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Notes</th>
                                                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700">Chalan Slip</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    <tr v-for="payment in getFilteredPayments(period)" :key="payment.id" class="hover:bg-gray-50">
                                                        <td class="px-4 py-2 text-sm text-gray-900">{{ formatDate(payment.payment_date) }}</td>
                                                        <td class="px-4 py-2 text-sm font-semibold text-green-700">{{ money(payment.payment_amount) }}</td>
                                                        <td class="px-4 py-2 text-sm text-gray-900">{{ payment.chalan_number || '—' }}</td>
                                                        <td class="px-4 py-2 text-sm text-gray-600">{{ payment.notes || '—' }}</td>
                                                        <td class="px-4 py-2 text-center">
                                                            <a
                                                                v-if="payment.chalan_slip"
                                                                :href="`/storage/${payment.chalan_slip}`"
                                                                target="_blank"
                                                                class="text-blue-600 hover:text-blue-800 text-sm underline"
                                                            >
                                                                View
                                                            </a>
                                                            <span v-else class="text-sm text-gray-400">—</span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>

                                <!-- No Payments Message -->
                                <tr v-if="expandedPeriods.includes(period.id) && getFilteredPayments(period).length === 0" class="bg-purple-50">
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                        {{ searchTerm ? 'No payments match this search' : 'No payments recorded for this period' }}
                                    </td>
                                </tr>
                            </template>

                            <!-- Empty State -->
                            <tr v-if="filteredPeriods.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <div class="text-sm font-medium">{{ searchTerm ? 'No matching periods found' : 'No accounting periods found' }}</div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        {{ searchTerm ? 'Try a different period name or chalan number.' : 'Create an accounting period to start tracking VAT.' }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                    <div class="text-sm text-gray-600 mb-1">Total VAT (All Periods)</div>
                    <div class="text-2xl font-bold text-red-700">{{ money(totalAllVat) }}</div>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                    <div class="text-sm text-gray-600 mb-1">Total Payments (All Periods)</div>
                    <div class="text-2xl font-bold text-blue-700">{{ money(totalAllPayments) }}</div>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                    <div class="text-sm text-gray-600 mb-1">Outstanding Balance</div>
                    <div class="text-2xl font-bold" :class="totalBalance > 0 ? 'text-orange-700' : 'text-green-700'">
                        {{ money(totalBalance) }}
                    </div>
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
