<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-green-50/30 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Net Profit Before Tax</h1>
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

            <!-- Net Profit Before Tax Calculation -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-green-500 p-6">
                <h2 class="text-xl font-bold text-green-700 mb-6">Pre-Tax Profit Calculation</h2>

                <div class="space-y-4">
                    <!-- Formula Display -->
                    <div class="bg-green-50 rounded-lg p-6">
                        <div class="flex items-center justify-center gap-4 text-lg font-medium">
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Operating Profit</div>
                                <Link href="/accounting/operating-profit" class="text-2xl font-bold text-blue-700 hover:text-blue-800 hover:underline">
                                    {{ money(operatingProfit) }}
                                </Link>
                            </div>
                            <div class="text-3xl text-gray-400">+</div>
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Net Non-Operating</div>
                                <Link href="/accounting/non-operating" class="text-2xl font-bold hover:underline" :class="netNonOperating >= 0 ? 'text-cyan-700 hover:text-cyan-800' : 'text-red-700 hover:text-red-800'">
                                    {{ money(netNonOperating) }}
                                </Link>
                            </div>
                            <div class="text-3xl text-gray-400">=</div>
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Net Profit Before Tax</div>
                                <div class="text-3xl font-bold" :class="netProfitBeforeTax >= 0 ? 'text-green-700' : 'text-red-700'">
                                    {{ money(netProfitBeforeTax) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profitability Indicator -->
                    <div v-if="netProfitBeforeTax >= 0" class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium text-green-800">Profitable before tax - Ready for tax planning</span>
                        </div>
                    </div>
                    <div v-else class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium text-red-800">Loss before tax - Review operations and non-operating activities</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Breakdown -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-green-500 p-6">
                <h3 class="text-lg font-bold text-green-700 mb-4">Detailed Breakdown</h3>

                <div class="space-y-6">
                    <!-- Operating Performance Section -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-900 mb-3">Operating Performance</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-700">Gross Profit</span>
                                <Link href="/accounting/gross-profit" class="font-semibold text-green-700 hover:text-green-800 hover:underline">
                                    {{ money(grossProfit) }}
                                </Link>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-700">Less: Operating Expenses (with VAT)</span>
                                <Link href="/accounting/operating-profit" class="font-semibold text-pink-700 hover:text-pink-800 hover:underline">
                                    {{ money(totalOperatingExpenses + totalOperatingExpensesVat) }}
                                </Link>
                            </div>
                            <div class="pt-2 border-t border-blue-200 flex justify-between items-center">
                                <span class="font-bold text-gray-900">Operating Profit</span>
                                <span class="text-xl font-bold text-blue-700">{{ money(operatingProfit) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Non-Operating Section -->
                    <div class="bg-cyan-50 rounded-lg p-4">
                        <h4 class="font-semibold text-cyan-900 mb-3">Non-Operating Activities</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-700">Non-Operating Income</span>
                                <Link href="/accounting/non-operating" class="font-semibold text-green-700 hover:text-green-800 hover:underline">
                                    {{ money(nonOperatingIncome) }}
                                </Link>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-700">Less: Non-Operating Expenses</span>
                                <Link href="/accounting/non-operating" class="font-semibold text-red-700 hover:text-red-800 hover:underline">
                                    {{ money(nonOperatingExpenses) }}
                                </Link>
                            </div>
                            <div class="pt-2 border-t border-cyan-200 flex justify-between items-center">
                                <span class="font-bold text-gray-900">Net Non-Operating</span>
                                <span class="text-xl font-bold" :class="netNonOperating >= 0 ? 'text-cyan-700' : 'text-red-700'">
                                    {{ money(netNonOperating) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Final Calculation -->
                    <div class="bg-green-100 rounded-lg p-4 border-2 border-green-400">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="text-sm text-gray-600 mb-1">Combined Result</div>
                                <div class="font-bold text-gray-900">Net Profit Before Tax</div>
                            </div>
                            <div class="text-3xl font-bold" :class="netProfitBeforeTax >= 0 ? 'text-green-700' : 'text-red-700'">
                                {{ money(netProfitBeforeTax) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax Planning Note -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-1">Tax Planning Ready</h4>
                        <p class="text-sm text-gray-600">
                            This is your pre-tax profit figure. Proceed to Tax Management to record current and deferred tax expenses, then view your final Net Profit After Tax.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link href="/accounting/operating-profit" class="bg-blue-50 hover:bg-blue-100 rounded-lg p-4 border border-blue-200 transition-colors">
                    <div class="text-sm text-blue-700 font-medium mb-1">← Previous</div>
                    <div class="font-semibold text-gray-900">Operating Profit</div>
                </Link>
                <Link href="/accounting/tax" class="bg-green-50 hover:bg-green-100 rounded-lg p-4 border border-green-200 transition-colors">
                    <div class="text-sm text-green-700 font-medium mb-1">Next Step →</div>
                    <div class="font-semibold text-gray-900">Tax Management</div>
                </Link>
                <Link href="/accounting/non-operating" class="bg-cyan-50 hover:bg-cyan-100 rounded-lg p-4 border border-cyan-200 transition-colors">
                    <div class="text-sm text-cyan-700 font-medium mb-1">Related</div>
                    <div class="font-semibold text-gray-900">Non-Operating</div>
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    period: Object,
    periods: Array,
    grossProfit: Number,
    totalOperatingExpenses: Number,
    totalOperatingExpensesVat: Number,
    operatingProfit: Number,
    nonOperatingIncome: Number,
    nonOperatingExpenses: Number,
    netNonOperating: Number,
    netProfitBeforeTax: Number,
});

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT' }).format(value);
};
</script>
