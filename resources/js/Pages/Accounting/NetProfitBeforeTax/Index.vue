<template>
    <Head title="Net Profit Before Tax" />
    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">Net Profit Before Tax</h1>
                <p class="text-sm text-gray-500">Period: {{ period.name }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <select class="px-4 py-2 border border-gray-200 rounded-full text-sm font-medium bg-white text-gray-700 shadow-sm focus:ring-2 focus:ring-[#1d4ed8] outline-none">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                        <a :href="route('accounting.net-profit-before-tax.report')" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-excel text-blue-600"></i>
                            Excel
                        </a>
                        <a :href="route('accounting.net-profit-before-tax.report', { type: 'pdf' })" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf text-red-600"></i>
                            PDF
                        </a>
                    </div>
                </div>

            <!-- Net Profit Before Tax Calculation -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-8 mb-6">
                <h2 class="text-[20px] font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Pre-Tax Profit Calculation</h2>

                <div class="space-y-6">
                    <!-- Formula Display -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 flex flex-col justify-center transition-all hover:bg-gray-50 hover:shadow-sm">
                            <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Operating Profit</p>
                            <Link href="/accounting/operating-profit" class="text-[32px] font-bold text-[#1d4ed8] leading-none hover:text-[#1e40af] transition-colors">
                                {{ money(operatingProfit) }}
                            </Link>
                        </div>
                        <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 flex flex-col justify-center transition-all hover:bg-gray-50 hover:shadow-sm">
                            <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Net Non-Operating</p>
                            <Link href="/accounting/non-operating" class="text-[32px] font-bold leading-none hover:opacity-80 transition-colors" :class="netNonOperating >= 0 ? 'text-[#1d4ed8]' : 'text-red-600'">
                                {{ money(netNonOperating) }}
                            </Link>
                        </div>
                        <div class="bg-white rounded-[24px] shadow-[0_4px_12px_rgba(0,0,0,0.05)] border-2 border-[#1d4ed8] p-6 flex flex-col justify-center transform scale-105">
                            <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Net Profit Before Tax</p>
                            <p class="text-[36px] font-bold leading-none" :class="netProfitBeforeTax >= 0 ? 'text-[#1d4ed8]' : 'text-red-600'">
                                {{ money(netProfitBeforeTax) }}
                            </p>
                        </div>
                    </div>

                    <!-- Profitability Indicator -->
                    <div v-if="netProfitBeforeTax >= 0" class="bg-blue-50 rounded-[20px] border border-blue-100 p-6 flex items-center gap-4 mt-6">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <span class="block font-bold text-blue-900 mb-1">Profitable before tax</span>
                            <span class="text-sm text-blue-700">Ready for tax planning.</span>
                        </div>
                    </div>
                    <div v-else class="bg-red-50 rounded-[20px] border border-red-100 p-6 flex items-center gap-4 mt-6">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <span class="block font-bold text-red-900 mb-1">Loss before tax</span>
                            <span class="text-sm text-red-700">Review operations and non-operating activities.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Breakdown -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-8 mb-6">
                <h3 class="text-[20px] font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Detailed Breakdown</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Operating Performance Section -->
                    <div class="border border-gray-100 rounded-[20px] p-6 hover:shadow-sm transition-all">
                        <h4 class="font-bold text-gray-800 mb-4 border-b border-gray-50 pb-2">Operating Performance</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500 font-medium">Gross Profit</span>
                                <Link href="/accounting/gross-profit" class="font-bold text-[#1d4ed8] hover:text-[#1e40af] transition-colors">
                                    {{ money(grossProfit) }}
                                </Link>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500 font-medium">Less: Operating Expenses</span>
                                <Link href="/accounting/operating-profit" class="font-bold text-red-500 hover:text-red-700 transition-colors">
                                    {{ money(totalOperatingExpensesWithVatTax) }}
                                </Link>
                            </div>
                            <div class="pt-3 border-t border-gray-100 flex justify-between items-center mt-2">
                                <span class="font-bold text-gray-800 uppercase tracking-wider text-[11px]">Operating Profit</span>
                                <span class="text-[18px] font-bold text-[#1d4ed8]">{{ money(operatingProfit) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Non-Operating Section -->
                    <div class="border border-gray-100 rounded-[20px] p-6 hover:shadow-sm transition-all">
                        <h4 class="font-bold text-gray-800 mb-4 border-b border-gray-50 pb-2">Non-Operating Activities</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500 font-medium">Non-Operating Income</span>
                                <Link href="/accounting/non-operating" class="font-bold text-[#1d4ed8] hover:text-[#1e40af] transition-colors">
                                    {{ money(nonOperatingIncome) }}
                                </Link>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500 font-medium">Less: Non-Operating Expenses</span>
                                <Link href="/accounting/non-operating" class="font-bold text-red-500 hover:text-red-700 transition-colors">
                                    {{ money(nonOperatingExpenses) }}
                                </Link>
                            </div>
                            <div class="pt-3 border-t border-gray-100 flex justify-between items-center mt-2">
                                <span class="font-bold text-gray-800 uppercase tracking-wider text-[11px]">Net Non-Operating</span>
                                <span class="text-[18px] font-bold" :class="netNonOperating >= 0 ? 'text-[#1d4ed8]' : 'text-red-500'">
                                    {{ money(netNonOperating) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Final Calculation -->
                <div class="mt-6 bg-[#1d4ed8] rounded-[20px] p-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-white shadow-[0_8px_24px_rgba(30,91,67,0.3)]">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-widest mb-1 opacity-80">Combined Result</div>
                        <div class="text-[20px] font-bold">Net Profit Before Tax</div>
                    </div>
                    <div class="text-[40px] font-black tracking-tight leading-none bg-white text-[#1d4ed8] px-6 py-2 rounded-[16px]">
                        {{ money(netProfitBeforeTax) }}
                    </div>
                </div>
            </div>

            <!-- Tax Planning Note -->
            <div class="bg-gray-50/80 rounded-[20px] shadow-sm border border-gray-100 p-6 mb-8">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 flex-shrink-0">
                        <i class="fa-solid fa-lightbulb"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 mb-1">Tax Planning Ready</h4>
                        <p class="text-sm text-gray-600">
                            This is your pre-tax profit figure. Proceed to Tax Management to record current and deferred tax expenses, then view your final Net Profit After Tax.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pb-8">
                <Link href="/accounting/operating-profit" class="bg-white hover:bg-gray-50 rounded-[20px] p-6 border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)] transition-all flex flex-col justify-center items-center text-center group">
                    <i class="fa-solid fa-arrow-left text-gray-400 mb-2 group-hover:-translate-x-1 transition-transform"></i>
                    <div class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Previous</div>
                    <div class="font-bold text-gray-900 group-hover:text-[#1d4ed8] transition-colors">Operating Profit</div>
                </Link>
                <Link href="/accounting/tax" class="bg-white hover:bg-gray-50 rounded-[20px] p-6 border border-[#1d4ed8]/20 shadow-[0_2px_12px_rgba(30,91,67,0.05)] transition-all flex flex-col justify-center items-center text-center group">
                    <i class="fa-solid fa-arrow-right text-[#1d4ed8] mb-2 group-hover:translate-x-1 transition-transform"></i>
                    <div class="text-[11px] text-[#1d4ed8] font-bold uppercase tracking-wider mb-1">Next Step</div>
                    <div class="font-bold text-[#1d4ed8]">Tax Management</div>
                </Link>
                <Link href="/accounting/non-operating" class="bg-white hover:bg-gray-50 rounded-[20px] p-6 border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)] transition-all flex flex-col justify-center items-center text-center group">
                    <i class="fa-solid fa-link text-gray-400 mb-2"></i>
                    <div class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Related</div>
                    <div class="font-bold text-gray-900 group-hover:text-[#1d4ed8] transition-colors">Non-Operating</div>
                </Link>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    period: Object,
    periods: Array,
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
});

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
};
</script>
