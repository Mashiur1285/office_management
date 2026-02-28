<template>
    <Head title="Net Profit After Tax" />
    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">Net Profit After Tax</h1>
                <p class="text-sm text-gray-500">Period: {{ period.name }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <select class="px-4 py-2 border border-gray-200 rounded-full text-sm font-medium bg-white text-gray-700 shadow-sm focus:ring-2 focus:ring-[#1e5b43] outline-none">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                        <a :href="route('accounting.net-profit-after-tax.report')" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-excel text-green-600"></i>
                            Excel
                        </a>
                        <a :href="route('accounting.net-profit-after-tax.report', { type: 'pdf' })" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf text-red-600"></i>
                            PDF
                        </a>
                    </div>
                </div>

            <!-- Net Profit After Tax Calculation -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(30,91,67,0.1)] border-2 border-[#1e5b43]/20 overflow-hidden mb-6">
                <div class="bg-[#1e5b43]/5 border-b border-[#1e5b43]/10 px-6 py-4">
                    <h2 class="text-xl font-bold text-[#1e5b43]">Bottom-Line Profit (After Tax)</h2>
                </div>
                <div class="p-6 space-y-4">
                    <!-- Formula Display -->
                    <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-white rounded-[20px] shadow-[0_2px_8px_rgba(0,0,0,0.02)] border border-gray-100 p-5 text-center transition-transform hover:-translate-y-1">
                                <div class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-2">Net Profit Before Tax</div>
                                <Link href="/accounting/net-profit-before-tax" class="text-2xl font-bold" :class="netProfitBeforeTax >= 0 ? 'text-[#1e5b43] hover:text-[#164230]' : 'text-red-600 hover:text-red-700'">
                                    {{ money(netProfitBeforeTax) }}
                                </Link>
                            </div>
                            <div class="bg-white rounded-[20px] shadow-[0_2px_8px_rgba(0,0,0,0.02)] border border-gray-100 p-5 text-center transition-transform hover:-translate-y-1">
                                <div class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-2">Total Tax Expenses</div>
                                <Link href="/accounting/tax" class="text-2xl font-bold text-orange-600 hover:text-orange-700">
                                    {{ money(totalTax) }}
                                </Link>
                            </div>
                            <div class="bg-[#1e5b43] rounded-[20px] shadow-[0_4px_12px_rgba(30,91,67,0.2)] p-5 text-center transform scale-105">
                                <div class="text-[11px] uppercase tracking-wider font-bold text-emerald-100/90 mb-2">Net Profit After Tax</div>
                                <div class="text-3xl font-bold" :class="netProfitAfterTax >= 0 ? 'text-white' : 'text-red-300'">
                                    {{ money(netProfitAfterTax) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Indicator -->
                    <div v-if="netProfitAfterTax >= 0" class="bg-emerald-50 border border-emerald-100 rounded-[16px] p-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <span class="text-sm font-bold text-[#1e5b43]">Profitable after tax - Strong bottom-line performance</span>
                    </div>
                    <div v-else class="bg-red-50 border border-red-100 rounded-[16px] p-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 shrink-0">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <span class="text-sm font-bold text-red-800">Net loss after tax - Review all P&L sections for improvement opportunities</span>
                    </div>
                </div>
            </div>

            <!-- Comprehensive P&L Summary -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-6 mb-6">
                <h3 class="text-[18px] font-bold text-gray-900 mb-6">Complete Profit & Loss Summary</h3>

                <div class="space-y-6">
                    <!-- Revenue & Gross Profit Section -->
                    <div class="bg-gray-50/50 rounded-2xl p-5 border border-gray-100">
                        <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-200 pb-2">Revenue & Gross Profit</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600 font-medium">Total Income</span>
                                <span class="font-bold text-[#1e5b43]">{{ money(totalIncome) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600 font-medium">Less: Cost of Sales</span>
                                <span class="font-bold text-red-500">{{ money(totalCostOfSales) }}</span>
                            </div>
                            <div class="pt-3 border-t border-gray-200 flex justify-between items-center">
                                <span class="font-bold text-gray-900">Gross Profit</span>
                                <Link href="/accounting/gross-profit" class="text-xl font-bold text-[#1e5b43] hover:text-[#164230] hover:underline">
                                    {{ money(grossProfit) }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Operating Performance Section -->
                    <div class="bg-gray-50/50 rounded-2xl p-5 border border-gray-100">
                        <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-200 pb-2">Operating Performance</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600 font-medium">Gross Profit</span>
                                <span class="font-bold text-[#1e5b43]">{{ money(grossProfit) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600 font-medium">Less: Operating Expenses (with VAT &amp; Tax)</span>
                                <span class="font-bold text-orange-500">{{ money(totalOperatingExpensesWithVatTax) }}</span>
                            </div>
                            <div class="pt-3 border-t border-gray-200 flex justify-between items-center">
                                <span class="font-bold text-gray-900">Operating Profit</span>
                                <Link href="/accounting/operating-profit" class="text-xl font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ money(operatingProfit) }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Non-Operating Section -->
                    <div class="bg-gray-50/50 rounded-2xl p-5 border border-gray-100">
                        <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-200 pb-2">Non-Operating Activities</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600 font-medium">Non-Operating Income</span>
                                <span class="font-bold text-[#1e5b43]">{{ money(nonOperatingIncome) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600 font-medium">Less: Non-Operating Expenses</span>
                                <span class="font-bold text-red-500">{{ money(nonOperatingExpenses) }}</span>
                            </div>
                            <div class="pt-3 border-t border-gray-200 flex justify-between items-center">
                                <span class="font-bold text-gray-900">Net Non-Operating</span>
                                <Link href="/accounting/non-operating" class="text-xl font-bold hover:underline" :class="netNonOperating >= 0 ? 'text-indigo-600 hover:text-indigo-800' : 'text-red-600 hover:text-red-700'">
                                    {{ money(netNonOperating) }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Pre-Tax Profit Section -->
                    <div class="bg-gray-50/50 rounded-2xl p-5 border-l-4 border-l-[#1e5b43] border border-gray-100">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-1">Before Tax</div>
                                <div class="font-bold text-gray-900">Net Profit Before Tax</div>
                            </div>
                            <Link href="/accounting/net-profit-before-tax" class="text-3xl font-bold hover:underline" :class="netProfitBeforeTax >= 0 ? 'text-[#1e5b43] hover:text-[#164230]' : 'text-red-600 hover:text-red-700'">
                                {{ money(netProfitBeforeTax) }}
                            </Link>
                        </div>
                    </div>

                    <!-- Tax Section -->
                    <div class="bg-gray-50/50 rounded-2xl p-5 border border-gray-100">
                        <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-200 pb-2">Tax Expenses</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600 font-medium">Current Tax</span>
                                <span class="font-bold text-orange-600">{{ money(currentTax) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600 font-medium">Deferred Tax</span>
                                <span class="font-bold text-amber-600">{{ money(deferredTax) }}</span>
                            </div>
                            <div class="pt-3 border-t border-gray-200 flex justify-between items-center">
                                <span class="font-bold text-gray-900">Total Tax Expenses</span>
                                <Link href="/accounting/tax" class="text-xl font-bold text-orange-600 hover:text-orange-700 hover:underline">
                                    {{ money(totalTax) }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Final Bottom Line -->
                    <div class="bg-[#1e5b43] rounded-2xl p-6 text-white shadow-[0_2px_12px_rgba(30,91,67,0.15)]">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="text-sm text-emerald-100/90 mb-1">Bottom-Line Result</div>
                                <div class="text-[22px] font-bold">Net Profit After Tax</div>
                            </div>
                            <div class="text-4xl font-bold" :class="netProfitAfterTax >= 0 ? 'text-white' : 'text-red-300'">
                                {{ money(netProfitAfterTax) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom-Line Performance -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-6 mb-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-circle-info text-blue-500 text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 mb-1">Bottom-Line Performance</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Net Profit After Tax represents your company's final profitability after all revenues, expenses, and taxes. This is the ultimate measure of business performance and is used for dividend distribution and retained earnings calculations.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link href="/accounting/tax" class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-5 hover:border-gray-300 transition-colors group">
                    <div class="text-[11px] uppercase tracking-wider font-bold text-gray-400 mb-2 group-hover:text-gray-500">← Previous</div>
                    <div class="font-bold text-gray-900">Tax Management</div>
                </Link>
                <Link href="/accounting/net-profit-before-tax" class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-5 hover:border-gray-300 transition-colors group">
                    <div class="text-[11px] uppercase tracking-wider font-bold text-gray-400 mb-2 group-hover:text-gray-500">Related</div>
                    <div class="font-bold text-gray-900">Net Profit Before Tax</div>
                </Link>
                <Link href="/accounting" class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-5 hover:border-gray-300 transition-colors group">
                    <div class="text-[11px] uppercase tracking-wider font-bold text-gray-400 mb-2 group-hover:text-gray-500">Dashboard</div>
                    <div class="font-bold text-gray-900">Accounting Home</div>
                </Link>
            </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    period: Object,
    periods: Array,
    totalIncome: Number,
    totalVat: Number,
    totalCostOfSales: Number,
    grossProfit: Number,
    totalOperatingExpenses: Number,
    totalOperatingExpensesVat: Number,
    totalOperatingExpensesTax: Number,
    totalOperatingExpensesWithVatTax: Number,
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
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
};

</script>
