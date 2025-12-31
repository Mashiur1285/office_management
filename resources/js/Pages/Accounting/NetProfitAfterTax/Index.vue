<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-purple-50/30 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Net Profit After Tax</h1>
                        <p class="text-sm text-gray-600 mt-1">Period: {{ period.name }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                        <button @click="exportPDF" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                            </svg>
                            Export PDF
                        </button>
                        <button @click="exportExcel" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Export Excel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Net Profit After Tax Calculation -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-purple-500 p-6">
                <h2 class="text-xl font-bold text-purple-700 mb-6">Bottom-Line Profit (After Tax)</h2>

                <div class="space-y-4">
                    <!-- Formula Display -->
                    <div class="bg-purple-50 rounded-lg p-6">
                        <div class="flex items-center justify-center gap-4 text-lg font-medium">
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Net Profit Before Tax</div>
                                <Link href="/accounting/net-profit-before-tax" class="text-2xl font-bold hover:underline" :class="netProfitBeforeTax >= 0 ? 'text-green-700 hover:text-green-800' : 'text-red-700 hover:text-red-800'">
                                    {{ money(netProfitBeforeTax) }}
                                </Link>
                            </div>
                            <div class="text-3xl text-gray-400">−</div>
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Total Tax Expenses</div>
                                <Link href="/accounting/tax" class="text-2xl font-bold text-yellow-700 hover:text-yellow-800 hover:underline">
                                    {{ money(totalTax) }}
                                </Link>
                            </div>
                            <div class="text-3xl text-gray-400">=</div>
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Net Profit After Tax</div>
                                <div class="text-3xl font-bold" :class="netProfitAfterTax >= 0 ? 'text-purple-700' : 'text-red-700'">
                                    {{ money(netProfitAfterTax) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Indicator -->
                    <div v-if="netProfitAfterTax >= 0" class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium text-purple-800">Profitable after tax - Strong bottom-line performance</span>
                        </div>
                    </div>
                    <div v-else class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium text-red-800">Net loss after tax - Review all P&L sections for improvement opportunities</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comprehensive P&L Summary -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-purple-500 p-6">
                <h3 class="text-lg font-bold text-purple-700 mb-4">Complete Profit & Loss Summary</h3>

                <div class="space-y-6">
                    <!-- Revenue & Gross Profit Section -->
                    <div class="bg-green-50 rounded-lg p-4">
                        <h4 class="font-semibold text-green-900 mb-3">Revenue & Gross Profit</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-700">Total Income</span>
                                <span class="font-semibold text-green-700">{{ money(totalIncome) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-700">Less: Cost of Sales</span>
                                <span class="font-semibold text-red-700">{{ money(totalCostOfSales) }}</span>
                            </div>
                            <div class="pt-2 border-t border-green-200 flex justify-between items-center">
                                <span class="font-bold text-gray-900">Gross Profit</span>
                                <Link href="/accounting/gross-profit" class="text-xl font-bold text-green-700 hover:text-green-800 hover:underline">
                                    {{ money(grossProfit) }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Operating Performance Section -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-900 mb-3">Operating Performance</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-700">Gross Profit</span>
                                <span class="font-semibold text-green-700">{{ money(grossProfit) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-700">Less: Operating Expenses (with VAT)</span>
                                <span class="font-semibold text-pink-700">{{ money(totalOperatingExpenses + totalOperatingExpensesVat) }}</span>
                            </div>
                            <div class="pt-2 border-t border-blue-200 flex justify-between items-center">
                                <span class="font-bold text-gray-900">Operating Profit</span>
                                <Link href="/accounting/operating-profit" class="text-xl font-bold text-blue-700 hover:text-blue-800 hover:underline">
                                    {{ money(operatingProfit) }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Non-Operating Section -->
                    <div class="bg-cyan-50 rounded-lg p-4">
                        <h4 class="font-semibold text-cyan-900 mb-3">Non-Operating Activities</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-700">Non-Operating Income</span>
                                <span class="font-semibold text-green-700">{{ money(nonOperatingIncome) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-700">Less: Non-Operating Expenses</span>
                                <span class="font-semibold text-red-700">{{ money(nonOperatingExpenses) }}</span>
                            </div>
                            <div class="pt-2 border-t border-cyan-200 flex justify-between items-center">
                                <span class="font-bold text-gray-900">Net Non-Operating</span>
                                <Link href="/accounting/non-operating" class="text-xl font-bold hover:underline" :class="netNonOperating >= 0 ? 'text-cyan-700 hover:text-cyan-800' : 'text-red-700 hover:text-red-800'">
                                    {{ money(netNonOperating) }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Pre-Tax Profit Section -->
                    <div class="bg-green-100 rounded-lg p-4 border-2 border-green-400">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="text-sm text-gray-600 mb-1">Before Tax</div>
                                <div class="font-bold text-gray-900">Net Profit Before Tax</div>
                            </div>
                            <Link href="/accounting/net-profit-before-tax" class="text-3xl font-bold hover:underline" :class="netProfitBeforeTax >= 0 ? 'text-green-700 hover:text-green-800' : 'text-red-700 hover:text-red-800'">
                                {{ money(netProfitBeforeTax) }}
                            </Link>
                        </div>
                    </div>

                    <!-- Tax Section -->
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <h4 class="font-semibold text-yellow-900 mb-3">Tax Expenses</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-700">Current Tax</span>
                                <span class="font-semibold text-orange-700">{{ money(currentTax) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-700">Deferred Tax</span>
                                <span class="font-semibold text-amber-700">{{ money(deferredTax) }}</span>
                            </div>
                            <div class="pt-2 border-t border-yellow-200 flex justify-between items-center">
                                <span class="font-bold text-gray-900">Total Tax Expenses</span>
                                <Link href="/accounting/tax" class="text-xl font-bold text-yellow-700 hover:text-yellow-800 hover:underline">
                                    {{ money(totalTax) }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Final Bottom Line -->
                    <div class="bg-purple-100 rounded-lg p-6 border-4 border-purple-400">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="text-sm text-gray-600 mb-1">Bottom-Line Result</div>
                                <div class="text-xl font-bold text-gray-900">Net Profit After Tax</div>
                            </div>
                            <div class="text-4xl font-bold" :class="netProfitAfterTax >= 0 ? 'text-purple-700' : 'text-red-700'">
                                {{ money(netProfitAfterTax) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Insights -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-purple-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-1">Bottom-Line Performance</h4>
                        <p class="text-sm text-gray-600">
                            Net Profit After Tax represents your company's final profitability after all revenues, expenses, and taxes. This is the ultimate measure of business performance and is used for dividend distribution and retained earnings calculations.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link href="/accounting/tax" class="bg-yellow-50 hover:bg-yellow-100 rounded-lg p-4 border border-yellow-200 transition-colors">
                    <div class="text-sm text-yellow-700 font-medium mb-1">← Previous</div>
                    <div class="font-semibold text-gray-900">Tax Management</div>
                </Link>
                <Link href="/accounting/net-profit-before-tax" class="bg-green-50 hover:bg-green-100 rounded-lg p-4 border border-green-200 transition-colors">
                    <div class="text-sm text-green-700 font-medium mb-1">Related</div>
                    <div class="font-semibold text-gray-900">Net Profit Before Tax</div>
                </Link>
                <Link href="/accounting" class="bg-purple-50 hover:bg-purple-100 rounded-lg p-4 border border-purple-200 transition-colors">
                    <div class="text-sm text-purple-700 font-medium mb-1">Dashboard</div>
                    <div class="font-semibold text-gray-900">Accounting Home</div>
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
    totalIncome: Number,
    totalVat: Number,
    totalCostOfSales: Number,
    grossProfit: Number,
    totalOperatingExpenses: Number,
    totalOperatingExpensesVat: Number,
    operatingProfit: Number,
    nonOperatingIncome: Number,
    nonOperatingExpenses: Number,
    netNonOperating: Number,
    netProfitBeforeTax: Number,
    currentTax: Number,
    deferredTax: Number,
    totalTax: Number,
    netProfitAfterTax: Number,
});

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT' }).format(value);
};

const exportPDF = () => {
    alert('PDF export functionality will be implemented with a PDF generation library');
    // TODO: Implement PDF export using a library like jsPDF or server-side PDF generation
};

const exportExcel = () => {
    alert('Excel export functionality will be implemented with a library like ExcelJS');
    // TODO: Implement Excel export using a library like ExcelJS or SheetJS
};
</script>
