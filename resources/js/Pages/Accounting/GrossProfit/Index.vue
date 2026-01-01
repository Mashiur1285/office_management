<template>
    <Head title="Gross Profit" />
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-green-50/30 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Gross Profit</h1>
                        <p class="text-sm text-gray-600 mt-1">Profitability Analysis - {{ period.name }}</p>
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

            <!-- Gross Profit Calculation (Prominent Green Box) -->
            <div class="bg-white rounded-2xl shadow-lg border-4 border-green-500 p-8">
                <h2 class="text-2xl font-bold text-green-700 mb-6 text-center">Gross Profit Calculation</h2>

                <div class="space-y-6">
                    <!-- Formula Display -->
                    <div class="bg-green-50 rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex-1">
                                <p class="text-sm text-gray-600 mb-2">Total Income</p>
                                <p class="text-3xl font-bold text-green-700">{{ money(totalIncome) }}</p>
                            </div>
                            <div class="px-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-600 mb-2">Total Cost of Sales</p>
                                <p class="text-3xl font-bold text-pink-700">{{ money(totalCostOfSales) }}</p>
                            </div>
                            <div class="px-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-600 mb-2">Gross Profit</p>
                                <p class="text-3xl font-bold" :class="grossProfit >= 0 ? 'text-green-700' : 'text-red-700'">
                                    {{ money(grossProfit) }}
                                </p>
                            </div>
                        </div>

                        <!-- Formula Text -->
                        <div class="text-center pt-4 border-t border-green-200">
                            <p class="text-sm text-gray-600">
                                Formula: <span class="font-semibold">Gross Profit = Total Income - Total Cost of Sales</span>
                            </p>
                        </div>
                    </div>

                    <!-- Profitability Metrics -->
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                            <p class="text-sm text-gray-600 mb-2">Total Income (with VAT)</p>
                            <p class="text-2xl font-bold text-green-700">{{ money(totalIncome + totalVat) }}</p>
                        </div>
                        <div class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-xl p-6 border border-pink-200">
                            <p class="text-sm text-gray-600 mb-2">Total Costs</p>
                            <p class="text-2xl font-bold text-pink-700">{{ money(totalCostOfSales) }}</p>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                            <p class="text-sm text-gray-600 mb-2">Gross Margin %</p>
                            <p class="text-2xl font-bold text-blue-700">{{ grossMarginPercentage }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Income vs Cost Breakdown by Category -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Income vs Cost Breakdown by Category</h3>

                <div class="space-y-4">
                    <!-- Travel & Tourism -->
                    <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-900">Travel & Tourism</h4>
                            <Link href="/accounting/income/travel-tourism" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                View Details →
                            </Link>
                        </div>
                        <div class="grid grid-cols-4 gap-4">
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Income</p>
                                <p class="text-lg font-bold text-green-700">{{ money(incomeByCategory.travel_tourism) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Costs</p>
                                <p class="text-lg font-bold text-pink-700">{{ money(costsByCategory.travel_tourism) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Profit</p>
                                <p class="text-lg font-bold" :class="(incomeByCategory.travel_tourism - costsByCategory.travel_tourism) >= 0 ? 'text-blue-700' : 'text-red-700'">
                                    {{ money(incomeByCategory.travel_tourism - costsByCategory.travel_tourism) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Margin %</p>
                                <p class="text-lg font-bold text-gray-700">{{ calculateMargin(incomeByCategory.travel_tourism, costsByCategory.travel_tourism) }}%</p>
                            </div>
                        </div>
                    </div>

                    <!-- Manpower Exporting -->
                    <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-900">Manpower Exporting</h4>
                            <Link href="/accounting/income/manpower" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                View Details →
                            </Link>
                        </div>
                        <div class="grid grid-cols-4 gap-4">
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Income</p>
                                <p class="text-lg font-bold text-green-700">{{ money(incomeByCategory.manpower_exporting) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Costs</p>
                                <p class="text-lg font-bold text-pink-700">{{ money(costsByCategory.manpower_exporting) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Profit</p>
                                <p class="text-lg font-bold" :class="(incomeByCategory.manpower_exporting - costsByCategory.manpower_exporting) >= 0 ? 'text-blue-700' : 'text-red-700'">
                                    {{ money(incomeByCategory.manpower_exporting - costsByCategory.manpower_exporting) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Margin %</p>
                                <p class="text-lg font-bold text-gray-700">{{ calculateMargin(incomeByCategory.manpower_exporting, costsByCategory.manpower_exporting) }}%</p>
                            </div>
                        </div>
                    </div>

                    <!-- Student Package -->
                    <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-900">Student Package</h4>
                            <Link href="/accounting/income/student" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                View Details →
                            </Link>
                        </div>
                        <div class="grid grid-cols-4 gap-4">
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Income</p>
                                <p class="text-lg font-bold text-green-700">{{ money(incomeByCategory.student_package) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Costs</p>
                                <p class="text-lg font-bold text-pink-700">{{ money(costsByCategory.student_package) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Profit</p>
                                <p class="text-lg font-bold" :class="(incomeByCategory.student_package - costsByCategory.student_package) >= 0 ? 'text-blue-700' : 'text-red-700'">
                                    {{ money(incomeByCategory.student_package - costsByCategory.student_package) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Margin %</p>
                                <p class="text-lg font-bold text-gray-700">{{ calculateMargin(incomeByCategory.student_package, costsByCategory.student_package) }}%</p>
                            </div>
                        </div>
                    </div>

                    <!-- Other Income (No associated costs) -->
                    <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow bg-green-50">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-900">Other Income</h4>
                            <Link href="/accounting/income/other" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                View Details →
                            </Link>
                        </div>
                        <div class="grid grid-cols-4 gap-4">
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Income</p>
                                <p class="text-lg font-bold text-green-700">{{ money(incomeByCategory.other_income) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Costs</p>
                                <p class="text-lg font-bold text-gray-400">{{ money(0) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Profit</p>
                                <p class="text-lg font-bold text-blue-700">{{ money(incomeByCategory.other_income) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Margin %</p>
                                <p class="text-lg font-bold text-gray-700">100%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Links</h3>
                <div class="grid grid-cols-2 gap-4">
                    <Link href="/accounting/income/travel-tourism" class="flex items-center justify-between p-4 border border-green-200 rounded-lg hover:bg-green-50 transition-colors">
                        <span class="font-medium text-gray-900">View All Income</span>
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </Link>
                    <Link href="/accounting/cost-of-sales/travel-tourism" class="flex items-center justify-between p-4 border border-pink-200 rounded-lg hover:bg-pink-50 transition-colors">
                        <span class="font-medium text-gray-900">View All Costs</span>
                        <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </Link>
                </div>
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
