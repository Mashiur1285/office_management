<template>
    <Head title="Gross Profit" />
    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">Gross Profit</h1>
                <p class="text-sm text-gray-500">Profitability Analysis - {{ period.name }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <select class="px-4 py-2 border border-gray-200 rounded-full text-sm font-medium bg-white text-gray-700 shadow-sm focus:ring-2 focus:ring-[#1d4ed8] outline-none">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                        <a :href="route('accounting.gross-profit.report')" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-excel text-blue-600"></i>
                            Excel
                        </a>
                        <a :href="route('accounting.gross-profit.report', { type: 'pdf' })" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf text-red-600"></i>
                            PDF
                        </a>
                    </div>
                </div>

            <!-- Gross Profit Calculation -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-8 mb-6">
                <h2 class="text-[20px] font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Gross Profit Calculation</h2>

                <div class="space-y-6">
                    <!-- Formula Display -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 flex flex-col justify-center transition-all hover:bg-gray-50 hover:shadow-sm">
                            <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Total Income</p>
                            <p class="text-[32px] font-bold text-[#1d4ed8] leading-none">{{ money(totalIncome) }}</p>
                        </div>
                        <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 flex flex-col justify-center transition-all hover:bg-gray-50 hover:shadow-sm">
                            <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Total Cost of Sales</p>
                            <p class="text-[32px] font-bold text-red-600 leading-none">{{ money(totalCostOfSales) }}</p>
                        </div>
                        <div class="bg-[#1d4ed8] rounded-[24px] shadow-[0_4px_12px_rgba(30,91,67,0.2)] p-6 flex flex-col justify-center transform scale-105">
                            <p class="text-[12px] uppercase tracking-wider font-bold text-blue-100/90 mb-2">Gross Profit</p>
                            <p class="text-[36px] font-bold text-white leading-none">
                                {{ money(grossProfit) }}
                            </p>
                        </div>
                    </div>

                    <!-- Profitability Metrics -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-100">
                        <div class="bg-white rounded-[20px] p-6 shadow-sm border border-blue-100 flex flex-col justify-center relative overflow-hidden">
                            <div class="absolute right-0 bottom-0 w-24 h-24 bg-blue-50 rounded-tl-[64px] z-0 opacity-50"></div>
                            <div class="relative z-10">
                                <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Total Income (with VAT)</p>
                                <p class="text-[24px] font-bold text-blue-600 leading-none">{{ money(totalIncome + totalVat) }}</p>
                            </div>
                        </div>
                        <div class="bg-white rounded-[20px] p-6 shadow-sm border border-red-100 flex flex-col justify-center relative overflow-hidden">
                            <div class="absolute right-0 bottom-0 w-24 h-24 bg-red-50 rounded-tl-[64px] z-0 opacity-50"></div>
                            <div class="relative z-10">
                                <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Total Costs</p>
                                <p class="text-[24px] font-bold text-red-600 leading-none">{{ money(totalCostOfSales) }}</p>
                            </div>
                        </div>
                        <div class="bg-white rounded-[20px] p-6 shadow-sm border border-blue-100 flex flex-col justify-center relative overflow-hidden">
                            <div class="absolute right-0 bottom-0 w-24 h-24 bg-blue-50 rounded-tl-[64px] z-0 opacity-50"></div>
                            <div class="relative z-10">
                                <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Gross Margin %</p>
                                <p class="text-[24px] font-bold text-blue-600 leading-none">{{ grossMarginPercentage }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Income vs Cost Breakdown by Category -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-8 mb-6">
                <h3 class="text-[20px] font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Income vs Cost Breakdown by Category</h3>

                <div class="space-y-4">
                    <!-- Travel & Tourism -->
                    <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 transition-all hover:shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4 pb-4 border-b border-gray-100">
                            <h4 class="font-bold text-gray-900 text-[16px] flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-[#1d4ed8]/10 text-[#1d4ed8] flex items-center justify-center">
                                    <i class="fa-solid fa-plane"></i>
                                </div>
                                Travel & Tourism
                            </h4>
                            <Link href="/accounting/income/travel-tourism" class="text-sm text-gray-500 hover:text-[#1d4ed8] font-bold uppercase tracking-wider flex items-center gap-1.5 transition-colors bg-white px-4 py-2 rounded-full border border-gray-200 shadow-sm hover:border-[#1d4ed8]/30">
                                View Details <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </Link>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div>
                                <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-1">Income</p>
                                <p class="text-[18px] font-bold text-[#1d4ed8] leading-none">{{ money(incomeByCategory.travel_tourism) }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-1">Costs</p>
                                <p class="text-[18px] font-bold text-red-600 leading-none">{{ money(costsByCategory.travel_tourism) }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-1">Profit</p>
                                <p class="text-[18px] font-bold leading-none" :class="(incomeByCategory.travel_tourism - costsByCategory.travel_tourism) >= 0 ? 'text-[#1d4ed8]' : 'text-red-600'">
                                    {{ money(incomeByCategory.travel_tourism - costsByCategory.travel_tourism) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-1">Margin %</p>
                                <p class="text-[18px] font-bold text-gray-900 leading-none">{{ calculateMargin(incomeByCategory.travel_tourism, costsByCategory.travel_tourism) }}%</p>
                            </div>
                        </div>
                    </div>

                    <!-- Manpower Exporting -->
                    <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 transition-all hover:shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4 pb-4 border-b border-gray-100">
                            <h4 class="font-bold text-gray-900 text-[16px] flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-[#1d4ed8]/10 text-[#1d4ed8] flex items-center justify-center">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                Manpower Exporting
                            </h4>
                            <Link href="/accounting/income/manpower" class="text-sm text-gray-500 hover:text-[#1d4ed8] font-bold uppercase tracking-wider flex items-center gap-1.5 transition-colors bg-white px-4 py-2 rounded-full border border-gray-200 shadow-sm hover:border-[#1d4ed8]/30">
                                View Details <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </Link>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div>
                                <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-1">Income</p>
                                <p class="text-[18px] font-bold text-[#1d4ed8] leading-none">{{ money(incomeByCategory.manpower_exporting) }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-1">Costs</p>
                                <p class="text-[18px] font-bold text-red-600 leading-none">{{ money(costsByCategory.manpower_exporting) }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-1">Profit</p>
                                <p class="text-[18px] font-bold leading-none" :class="(incomeByCategory.manpower_exporting - costsByCategory.manpower_exporting) >= 0 ? 'text-[#1d4ed8]' : 'text-red-600'">
                                    {{ money(incomeByCategory.manpower_exporting - costsByCategory.manpower_exporting) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-1">Margin %</p>
                                <p class="text-[18px] font-bold text-gray-900 leading-none">{{ calculateMargin(incomeByCategory.manpower_exporting, costsByCategory.manpower_exporting) }}%</p>
                            </div>
                        </div>
                    </div>

                    <!-- Student Package -->
                    <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 transition-all hover:shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4 pb-4 border-b border-gray-100">
                            <h4 class="font-bold text-gray-900 text-[16px] flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-[#1d4ed8]/10 text-[#1d4ed8] flex items-center justify-center">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                </div>
                                Student Package
                            </h4>
                            <Link href="/accounting/income/student" class="text-sm text-gray-500 hover:text-[#1d4ed8] font-bold uppercase tracking-wider flex items-center gap-1.5 transition-colors bg-white px-4 py-2 rounded-full border border-gray-200 shadow-sm hover:border-[#1d4ed8]/30">
                                View Details <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </Link>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div>
                                <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-1">Income</p>
                                <p class="text-[18px] font-bold text-[#1d4ed8] leading-none">{{ money(incomeByCategory.student_package) }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-1">Costs</p>
                                <p class="text-[18px] font-bold text-red-600 leading-none">{{ money(costsByCategory.student_package) }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-1">Profit</p>
                                <p class="text-[18px] font-bold leading-none" :class="(incomeByCategory.student_package - costsByCategory.student_package) >= 0 ? 'text-[#1d4ed8]' : 'text-red-600'">
                                    {{ money(incomeByCategory.student_package - costsByCategory.student_package) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-1">Margin %</p>
                                <p class="text-[18px] font-bold text-gray-900 leading-none">{{ calculateMargin(incomeByCategory.student_package, costsByCategory.student_package) }}%</p>
                            </div>
                        </div>
                    </div>

                    <!-- Other Income -->
                    <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 transition-all hover:shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4 pb-4 border-b border-gray-100">
                            <h4 class="font-bold text-gray-900 text-[16px] flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-[#1d4ed8]/10 text-[#1d4ed8] flex items-center justify-center">
                                    <i class="fa-solid fa-plus"></i>
                                </div>
                                Other Income
                            </h4>
                            <Link href="/accounting/income/other" class="text-sm text-gray-500 hover:text-[#1d4ed8] font-bold uppercase tracking-wider flex items-center gap-1.5 transition-colors bg-white px-4 py-2 rounded-full border border-gray-200 shadow-sm hover:border-[#1d4ed8]/30">
                                View Details <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </Link>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div>
                                <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-1">Income</p>
                                <p class="text-[18px] font-bold text-[#1d4ed8] leading-none">{{ money(incomeByCategory.other_income) }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-1">Costs</p>
                                <p class="text-[18px] font-bold text-gray-400 leading-none">{{ money(0) }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-1">Profit</p>
                                <p class="text-[18px] font-bold text-[#1d4ed8] leading-none">{{ money(incomeByCategory.other_income) }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-wider font-bold text-gray-500 mb-1">Margin %</p>
                                <p class="text-[18px] font-bold text-gray-900 leading-none">100%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-8">
                <h3 class="text-[20px] font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Quick Links</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Link href="/accounting/income/travel-tourism" class="flex items-center justify-between p-5 border border-gray-100 bg-gray-50/50 rounded-[16px] hover:bg-white hover:border-[#1d4ed8]/30 hover:shadow-sm transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                <i class="fa-solid fa-money-bill-trend-up"></i>
                            </div>
                            <span class="font-bold text-gray-900 group-hover:text-[#1d4ed8] transition-colors">View All Income</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-400 group-hover:text-[#1d4ed8] transition-colors text-xs"></i>
                    </Link>
                    <Link href="/accounting/cost-of-sales/travel-tourism" class="flex items-center justify-between p-5 border border-gray-100 bg-gray-50/50 rounded-[16px] hover:bg-white hover:border-red-600/30 hover:shadow-sm transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                                <i class="fa-solid fa-money-bill-transfer"></i>
                            </div>
                            <span class="font-bold text-gray-900 group-hover:text-red-600 transition-colors">View All Costs</span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-400 group-hover:text-red-600 transition-colors text-xs"></i>
                    </Link>
                </div>
            </div>
        </div>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    period: Object,
    periods: Array,
    totalIncome: Number,
    totalVat: Number,
    totalCostOfSales: Number,
    grossProfit: Number,
    incomeByCategory: Object,
    costsByCategory: Object,
});

const grossMarginPercentage = computed(() => {
    if (props.totalIncome === 0) return '0.00';
    return ((props.grossProfit / props.totalIncome) * 100).toFixed(2);
});

const calculateMargin = (income, cost) => {
    if (income === 0) return '0.00';
    return (((income - cost) / income) * 100).toFixed(2);
};

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
};
</script>
