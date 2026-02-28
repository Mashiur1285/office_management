<template>
    <Head title="Operating Profit" />
    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">Operating Profit</h1>
                <p class="text-sm text-gray-500">Period: {{ period.name }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <select class="px-4 py-2 border border-gray-200 rounded-full text-sm font-medium bg-white text-gray-700 shadow-sm focus:ring-2 focus:ring-[#1e5b43] outline-none">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                        <a :href="route('accounting.operating-profit.report')" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-excel text-green-600"></i>
                            Excel
                        </a>
                        <a :href="route('accounting.operating-profit.report', { type: 'pdf' })" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf text-red-600"></i>
                            PDF
                        </a>
                    </div>
                </div>

            <!-- Operating Profit Formula -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-8 mb-6">
                <h2 class="text-[20px] font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Net Operating Profit Calculation</h2>

                <div class="space-y-6">
                    <!-- Formula Display -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 flex flex-col justify-center transition-all hover:bg-gray-50 hover:shadow-sm">
                            <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Gross Profit</p>
                            <Link href="/accounting/gross-profit" class="text-[32px] font-bold text-[#1e5b43] leading-none hover:text-[#164230] transition-colors">
                                {{ money(grossProfit) }}
                            </Link>
                        </div>
                        <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 flex flex-col justify-center transition-all hover:bg-gray-50 hover:shadow-sm">
                            <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Total Operating Expenses (with VAT &amp; Tax)</p>
                            <p class="text-[32px] font-bold text-red-600 leading-none">{{ money(totalOperatingExpensesWithVatTax) }}</p>
                        </div>
                        <div class="bg-white rounded-[24px] shadow-[0_4px_12px_rgba(0,0,0,0.05)] border-2 border-[#1e5b43] p-6 flex flex-col justify-center transform scale-105">
                            <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Net Operating Profit</p>
                            <p class="text-[36px] font-bold leading-none" :class="operatingProfit >= 0 ? 'text-[#1e5b43]' : 'text-red-600'">
                                {{ money(operatingProfit) }}
                            </p>
                        </div>
                    </div>

                    <!-- Performance Metric -->
                    <div class="bg-white rounded-[20px] p-6 shadow-sm border border-emerald-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-6">
                        <div class="flex flex-col">
                            <span class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-1">Operating Margin</span>
                            <span class="text-xs text-gray-400">Operating Profit ÷ Gross Profit × 100</span>
                        </div>
                        <span class="text-[32px] font-bold text-[#1e5b43] leading-none">{{ operatingMargin }}%</span>
                    </div>
                </div>
            </div>

            <!-- Operating Expenses Breakdown -->
            <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-8 mb-6">
                <h3 class="text-[20px] font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Operating Expenses Breakdown</h3>

                <!-- Table Header -->
                <div class="mb-4">
                    <div class="grid grid-cols-12 gap-2 px-4 py-3 bg-gray-50/50 rounded-[12px] font-bold text-[11px] text-gray-400 uppercase tracking-wider border border-gray-100">
                        <div class="col-span-12 md:col-span-4 mb-2 md:mb-0">Category</div>
                        <div class="col-span-3 md:col-span-2 text-right">Amount</div>
                        <div class="col-span-3 md:col-span-2 text-right">VAT</div>
                        <div class="col-span-3 md:col-span-2 text-right">Tax</div>
                        <div class="col-span-3 md:col-span-2 text-right">Total</div>
                    </div>
                </div>

                <!-- Category List -->
                <ul class="space-y-2">
                    <li class="hover:bg-gray-50/50 rounded-[16px] p-4 transition-colors border border-transparent hover:border-gray-100 group">
                        <div class="grid grid-cols-12 gap-2 items-center">
                            <Link href="/accounting/operating-expenses/employee" class="col-span-12 md:col-span-4 text-sm text-gray-700 hover:text-[#1e5b43] font-medium flex items-center gap-3 mb-2 md:mb-0">
                                <div class="w-8 h-8 rounded-full bg-[#1e5b43]/10 text-[#1e5b43] flex items-center justify-center text-[11px] font-bold">
                                    A
                                </div>
                                <span class="font-bold">Employee & Manpower</span>
                            </Link>
                            <div class="col-span-3 md:col-span-2 text-right">
                                <span class="font-semibold text-gray-900">{{ money(expensesByCategory.employee_manpower.amount) }}</span>
                            </div>
                            <div class="col-span-3 md:col-span-2 text-right">
                                <span class="font-semibold text-red-500">{{ money(expensesByCategory.employee_manpower.vat_amount) }}</span>
                            </div>
                            <div class="col-span-3 md:col-span-2 text-right">
                                <span class="font-semibold text-amber-500">{{ money(expensesByCategory.employee_manpower.tax_amount) }}</span>
                            </div>
                            <div class="col-span-3 md:col-span-2 text-right">
                                <span class="text-[16px] font-bold text-[#1e5b43]">{{ money(expensesByCategory.employee_manpower.total) }}</span>
                            </div>
                        </div>
                    </li>
                    <li class="hover:bg-gray-50/50 rounded-[16px] p-4 transition-colors border border-transparent hover:border-gray-100 group">
                        <div class="grid grid-cols-12 gap-2 items-center">
                            <Link href="/accounting/operating-expenses/administrative" class="col-span-12 md:col-span-4 text-sm text-gray-700 hover:text-[#1e5b43] font-medium flex items-center gap-3 mb-2 md:mb-0">
                                <div class="w-8 h-8 rounded-full bg-[#1e5b43]/10 text-[#1e5b43] flex items-center justify-center text-[11px] font-bold">
                                    B
                                </div>
                                <span class="font-bold">Administrative</span>
                            </Link>
                            <div class="col-span-3 md:col-span-2 text-right">
                                <span class="font-semibold text-gray-900">{{ money(expensesByCategory.administrative.amount) }}</span>
                            </div>
                            <div class="col-span-3 md:col-span-2 text-right">
                                <span class="font-semibold text-red-500">{{ money(expensesByCategory.administrative.vat_amount) }}</span>
                            </div>
                            <div class="col-span-3 md:col-span-2 text-right">
                                <span class="font-semibold text-amber-500">{{ money(expensesByCategory.administrative.tax_amount) }}</span>
                            </div>
                            <div class="col-span-3 md:col-span-2 text-right">
                                <span class="text-[16px] font-bold text-[#1e5b43]">{{ money(expensesByCategory.administrative.total) }}</span>
                            </div>
                        </div>
                    </li>
                    <li class="hover:bg-gray-50/50 rounded-[16px] p-4 transition-colors border border-transparent hover:border-gray-100 group">
                        <div class="grid grid-cols-12 gap-2 items-center">
                            <Link href="/accounting/operating-expenses/selling-marketing" class="col-span-12 md:col-span-4 text-sm text-gray-700 hover:text-[#1e5b43] font-medium flex items-center gap-3 mb-2 md:mb-0">
                                <div class="w-8 h-8 rounded-full bg-[#1e5b43]/10 text-[#1e5b43] flex items-center justify-center text-[11px] font-bold">
                                    C
                                </div>
                                <span class="font-bold">Selling & Marketing</span>
                            </Link>
                            <div class="col-span-3 md:col-span-2 text-right">
                                <span class="font-semibold text-gray-900">{{ money(expensesByCategory.selling_marketing.amount) }}</span>
                            </div>
                            <div class="col-span-3 md:col-span-2 text-right">
                                <span class="font-semibold text-red-500">{{ money(expensesByCategory.selling_marketing.vat_amount) }}</span>
                            </div>
                            <div class="col-span-3 md:col-span-2 text-right">
                                <span class="font-semibold text-amber-500">{{ money(expensesByCategory.selling_marketing.tax_amount) }}</span>
                            </div>
                            <div class="col-span-3 md:col-span-2 text-right">
                                <span class="text-[16px] font-bold text-[#1e5b43]">{{ money(expensesByCategory.selling_marketing.total) }}</span>
                            </div>
                        </div>
                    </li>
                    <li class="hover:bg-gray-50/50 rounded-[16px] p-4 transition-colors border border-transparent hover:border-gray-100 group">
                        <div class="grid grid-cols-12 gap-2 items-center">
                            <Link href="/accounting/operating-expenses/general" class="col-span-12 md:col-span-4 text-sm text-gray-700 hover:text-[#1e5b43] font-medium flex items-center gap-3 mb-2 md:mb-0">
                                <div class="w-8 h-8 rounded-full bg-[#1e5b43]/10 text-[#1e5b43] flex items-center justify-center text-[11px] font-bold">
                                    D
                                </div>
                                <span class="font-bold">General</span>
                            </Link>
                            <div class="col-span-3 md:col-span-2 text-right">
                                <span class="font-semibold text-gray-900">{{ money(expensesByCategory.general.amount) }}</span>
                            </div>
                            <div class="col-span-3 md:col-span-2 text-right">
                                <span class="font-semibold text-red-500">{{ money(expensesByCategory.general.vat_amount) }}</span>
                            </div>
                            <div class="col-span-3 md:col-span-2 text-right">
                                <span class="font-semibold text-amber-500">{{ money(expensesByCategory.general.tax_amount) }}</span>
                            </div>
                            <div class="col-span-3 md:col-span-2 text-right">
                                <span class="text-[16px] font-bold text-[#1e5b43]">{{ money(expensesByCategory.general.total) }}</span>
                            </div>
                        </div>
                    </li>
                </ul>

                <!-- Total Row -->
                <div class="mt-6 pt-6 border-t border-gray-100 px-4">
                    <div class="grid grid-cols-12 gap-2 items-center">
                        <div class="col-span-12 md:col-span-4 mb-2 md:mb-0">
                            <span class="text-[11px] uppercase tracking-wider font-bold text-gray-500">Total Operating Expenses:</span>
                        </div>
                        <div class="col-span-3 md:col-span-2 text-right">
                            <span class="text-[16px] font-bold text-gray-900">{{ money(totalOperatingExpenses) }}</span>
                        </div>
                        <div class="col-span-3 md:col-span-2 text-right">
                            <span class="text-[16px] font-bold text-red-600">{{ money(totalOperatingExpensesVat) }}</span>
                        </div>
                        <div class="col-span-3 md:col-span-2 text-right">
                            <span class="text-[16px] font-bold text-amber-600">{{ money(totalOperatingExpensesTax) }}</span>
                        </div>
                        <div class="col-span-3 md:col-span-2 text-right">
                            <span class="text-[20px] font-bold text-[#1e5b43]">{{ money(totalOperatingExpensesWithVatTax) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Final Operating Profit Display -->
            <div class="bg-[#1e5b43] rounded-[24px] shadow-[0_8px_24px_rgba(30,91,67,0.3)] p-10 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-64 h-64 bg-white/5 rounded-bl-[128px] z-0"></div>
                <div class="absolute left-0 bottom-0 w-32 h-32 bg-white/5 rounded-tr-[64px] z-0"></div>
                <div class="text-center text-white relative z-10">
                    <div class="text-[14px] font-bold uppercase tracking-widest mb-4 opacity-90">Net Operating Profit</div>
                    <div class="text-[64px] font-black tracking-tight mb-4 leading-none">{{ money(operatingProfit) }}</div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm text-sm font-medium">
                        <i :class="['fa-solid', operatingProfit >= 0 ? 'fa-arrow-trend-up text-emerald-300' : 'fa-arrow-trend-down text-red-300']"></i>
                        {{ operatingProfit >= 0 ? 'Positive operating performance' : 'Operating loss - review expenses' }}
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
    grossProfit: Number,
    totalOperatingExpenses: Number,
    totalOperatingExpensesVat: Number,
    totalOperatingExpensesTax: Number,
    totalOperatingExpensesWithVatTax: Number,
    operatingProfit: Number,
    expensesByCategory: Object,
});

const operatingMargin = computed(() => {
    if (props.grossProfit === 0) return '0.00';
    return ((props.operatingProfit / props.grossProfit) * 100).toFixed(2);
});

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
};
</script>
