<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50/30 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Operating Profit</h1>
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

            <!-- Operating Profit Formula -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-blue-500 p-6">
                <h2 class="text-xl font-bold text-blue-700 mb-6">Net Operating Profit Calculation</h2>

                <div class="space-y-4">
                    <!-- Formula Display -->
                    <div class="bg-blue-50 rounded-lg p-6">
                        <div class="flex items-center justify-center gap-4 text-lg font-medium">
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Gross Profit</div>
                                <Link href="/accounting/gross-profit" class="text-2xl font-bold text-green-700 hover:text-green-800 hover:underline">
                                    {{ money(grossProfit) }}
                                </Link>
                            </div>
                            <div class="text-3xl text-gray-400">−</div>
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Total Operating Expenses</div>
                                <div class="text-2xl font-bold text-pink-700">{{ money(totalOperatingExpensesWithVat) }}</div>
                            </div>
                            <div class="text-3xl text-gray-400">=</div>
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Net Operating Profit</div>
                                <div class="text-3xl font-bold" :class="operatingProfit >= 0 ? 'text-blue-700' : 'text-red-700'">
                                    {{ money(operatingProfit) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Metric -->
                    <div class="bg-white rounded-lg border-2 border-blue-200 p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Operating Margin:</span>
                            <span class="text-lg font-bold text-blue-700">{{ operatingMargin }}%</span>
                        </div>
                        <div class="mt-2 text-xs text-gray-500">
                            Operating Profit ÷ Gross Profit × 100
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operating Expenses Breakdown -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-blue-500 p-6">
                <h3 class="text-lg font-bold text-blue-700 mb-4">Operating Expenses Breakdown</h3>

                <!-- Table Header -->
                <div class="mb-2">
                    <div class="grid grid-cols-12 gap-2 px-2 py-2 bg-gray-50 rounded-lg font-semibold text-xs text-gray-600 uppercase">
                        <div class="col-span-5">Category</div>
                        <div class="col-span-2 text-right">Amount</div>
                        <div class="col-span-2 text-right">VAT</div>
                        <div class="col-span-3 text-right">Total</div>
                    </div>
                </div>

                <!-- Category List -->
                <ul class="space-y-1">
                    <li class="hover:bg-blue-50 rounded-lg p-2 transition-colors">
                        <div class="grid grid-cols-12 gap-2 items-center">
                            <Link href="/accounting/operating-expenses/employee" class="col-span-5 text-sm text-gray-700 hover:text-blue-800 font-medium flex items-center gap-2">
                                <span class="font-bold text-blue-700">A.</span>
                                <span>Employee & Manpower</span>
                            </Link>
                            <div class="col-span-2 text-right">
                                <span class="font-semibold text-green-700">{{ money(expensesByCategory.employee_manpower.amount) }}</span>
                            </div>
                            <div class="col-span-2 text-right">
                                <span class="font-semibold text-red-700">{{ money(expensesByCategory.employee_manpower.vat_amount) }}</span>
                            </div>
                            <div class="col-span-3 text-right">
                                <span class="font-bold text-pink-700">{{ money(expensesByCategory.employee_manpower.total) }}</span>
                            </div>
                        </div>
                    </li>
                    <li class="hover:bg-blue-50 rounded-lg p-2 transition-colors">
                        <div class="grid grid-cols-12 gap-2 items-center">
                            <Link href="/accounting/operating-expenses/administrative" class="col-span-5 text-sm text-gray-700 hover:text-blue-800 font-medium flex items-center gap-2">
                                <span class="font-bold text-blue-700">B.</span>
                                <span>Administrative</span>
                            </Link>
                            <div class="col-span-2 text-right">
                                <span class="font-semibold text-green-700">{{ money(expensesByCategory.administrative.amount) }}</span>
                            </div>
                            <div class="col-span-2 text-right">
                                <span class="font-semibold text-red-700">{{ money(expensesByCategory.administrative.vat_amount) }}</span>
                            </div>
                            <div class="col-span-3 text-right">
                                <span class="font-bold text-pink-700">{{ money(expensesByCategory.administrative.total) }}</span>
                            </div>
                        </div>
                    </li>
                    <li class="hover:bg-blue-50 rounded-lg p-2 transition-colors">
                        <div class="grid grid-cols-12 gap-2 items-center">
                            <Link href="/accounting/operating-expenses/selling-marketing" class="col-span-5 text-sm text-gray-700 hover:text-blue-800 font-medium flex items-center gap-2">
                                <span class="font-bold text-blue-700">C.</span>
                                <span>Selling & Marketing</span>
                            </Link>
                            <div class="col-span-2 text-right">
                                <span class="font-semibold text-green-700">{{ money(expensesByCategory.selling_marketing.amount) }}</span>
                            </div>
                            <div class="col-span-2 text-right">
                                <span class="font-semibold text-red-700">{{ money(expensesByCategory.selling_marketing.vat_amount) }}</span>
                            </div>
                            <div class="col-span-3 text-right">
                                <span class="font-bold text-pink-700">{{ money(expensesByCategory.selling_marketing.total) }}</span>
                            </div>
                        </div>
                    </li>
                    <li class="hover:bg-blue-50 rounded-lg p-2 transition-colors">
                        <div class="grid grid-cols-12 gap-2 items-center">
                            <Link href="/accounting/operating-expenses/general" class="col-span-5 text-sm text-gray-700 hover:text-blue-800 font-medium flex items-center gap-2">
                                <span class="font-bold text-blue-700">D.</span>
                                <span>General</span>
                            </Link>
                            <div class="col-span-2 text-right">
                                <span class="font-semibold text-green-700">{{ money(expensesByCategory.general.amount) }}</span>
                            </div>
                            <div class="col-span-2 text-right">
                                <span class="font-semibold text-red-700">{{ money(expensesByCategory.general.vat_amount) }}</span>
                            </div>
                            <div class="col-span-3 text-right">
                                <span class="font-bold text-pink-700">{{ money(expensesByCategory.general.total) }}</span>
                            </div>
                        </div>
                    </li>
                </ul>

                <!-- Total Row -->
                <div class="mt-4 pt-4 border-t-2 border-blue-300">
                    <div class="grid grid-cols-12 gap-2 items-center">
                        <div class="col-span-5">
                            <span class="font-bold text-gray-900">Total Operating Expenses:</span>
                        </div>
                        <div class="col-span-2 text-right">
                            <span class="text-xl font-bold text-green-700">{{ money(totalOperatingExpenses) }}</span>
                        </div>
                        <div class="col-span-2 text-right">
                            <span class="text-xl font-bold text-red-700">{{ money(totalOperatingExpensesVat) }}</span>
                        </div>
                        <div class="col-span-3 text-right">
                            <span class="text-xl font-bold text-pink-700">{{ money(totalOperatingExpensesWithVat) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Final Operating Profit Display -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-8">
                <div class="text-center text-white">
                    <div class="text-sm font-medium uppercase tracking-wide mb-2">Net Operating Profit</div>
                    <div class="text-5xl font-bold mb-2">{{ money(operatingProfit) }}</div>
                    <div class="text-sm opacity-90">
                        {{ operatingProfit >= 0 ? 'Positive operating performance' : 'Operating loss - review expenses' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    period: Object,
    periods: Array,
    grossProfit: Number,
    totalOperatingExpenses: Number,
    totalOperatingExpensesVat: Number,
    totalOperatingExpensesWithVat: Number,
    operatingProfit: Number,
    expensesByCategory: Object,
});

const operatingMargin = computed(() => {
    if (props.grossProfit === 0) return '0.00';
    return ((props.operatingProfit / props.grossProfit) * 100).toFixed(2);
});

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT' }).format(value);
};
</script>
