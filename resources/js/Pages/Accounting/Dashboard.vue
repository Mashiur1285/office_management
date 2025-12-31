<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50/30 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Accounting Dashboard</h1>
                        <p class="text-sm text-gray-600 mt-1">Profit & Loss Overview - {{ period.name }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                        <span class="px-3 py-1 rounded-full text-xs font-bold" :class="statusClass(period.status)">
                            {{ period.status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Income Breakdown Pie Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Income Breakdown</h3>
                    <div class="h-64 flex items-center justify-center">
                        <Pie :data="incomeChartData" :options="pieChartOptions" />
                    </div>
                </div>

                <!-- Expense Breakdown Pie Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Operating Expense Breakdown</h3>
                    <div class="h-64 flex items-center justify-center">
                        <Pie :data="expenseChartData" :options="pieChartOptions" />
                    </div>
                </div>

                <!-- P&L Flow Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">P&L Flow</h3>
                    <div class="h-64">
                        <Doughnut :data="plOverviewChartData" :options="doughnutChartOptions" />
                    </div>
                </div>

                <!-- Performance Trend -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Performance Trend</h3>
                    <div class="h-64">
                        <Line :data="performanceTrendData" :options="lineChartOptions" />
                    </div>
                </div>
            </div>

            <!-- Key Metrics Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 border border-green-200">
                    <p class="text-sm text-green-700 font-medium mb-1">Total Revenue</p>
                    <p class="text-2xl font-bold text-green-900">{{ money(period.total_income) }}</p>
                    <div class="mt-2 flex items-center text-xs text-green-700">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                        </svg>
                        Income
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200">
                    <p class="text-sm text-blue-700 font-medium mb-1">Gross Profit</p>
                    <p class="text-2xl font-bold text-blue-900">{{ money(period.gross_profit) }}</p>
                    <div class="mt-2 text-xs text-blue-700">
                        {{ profitMargin(period.gross_profit, period.total_income) }}% margin
                    </div>
                </div>

                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-5 border border-indigo-200">
                    <p class="text-sm text-indigo-700 font-medium mb-1">Operating Profit</p>
                    <p class="text-2xl font-bold text-indigo-900">{{ money(period.operating_profit) }}</p>
                    <div class="mt-2 text-xs text-indigo-700">
                        {{ profitMargin(period.operating_profit, period.total_income) }}% margin
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 border border-purple-200">
                    <p class="text-sm text-purple-700 font-medium mb-1">Net Profit</p>
                    <p class="text-2xl font-bold text-purple-900">{{ money(period.net_profit_after_tax) }}</p>
                    <div class="mt-2 text-xs text-purple-700">
                        {{ profitMargin(period.net_profit_after_tax, period.total_income) }}% margin
                    </div>
                </div>
            </div>

            <!-- P&L Statement Summary -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Financial Statement Summary</h2>

                <div class="space-y-4">
                    <!-- Revenue -->
                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                        <span class="text-gray-700 font-medium">Total Revenue</span>
                        <span class="text-xl font-bold text-green-700">{{ money(period.total_income) }}</span>
                    </div>

                    <!-- Cost of Sales -->
                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                        <span class="text-gray-700 font-medium">Cost of Sales</span>
                        <span class="text-xl font-bold text-red-700">({{ money(period.total_cost_of_sales) }})</span>
                    </div>

                    <!-- Gross Profit -->
                    <div class="flex justify-between items-center py-3 border-b border-gray-200 bg-blue-50 px-3 -mx-3 rounded">
                        <span class="text-gray-900 font-bold">Gross Profit</span>
                        <span class="text-2xl font-bold text-blue-700">{{ money(period.gross_profit) }}</span>
                    </div>

                    <!-- Operating Expenses -->
                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                        <span class="text-gray-700 font-medium">Operating Expenses</span>
                        <span class="text-xl font-bold text-orange-700">({{ money(period.total_operating_expenses) }})</span>
                    </div>

                    <!-- Operating Profit -->
                    <div class="flex justify-between items-center py-3 border-b border-gray-200 bg-indigo-50 px-3 -mx-3 rounded">
                        <span class="text-gray-900 font-bold">Operating Profit</span>
                        <span class="text-2xl font-bold text-indigo-700">{{ money(period.operating_profit) }}</span>
                    </div>

                    <!-- Non-Operating -->
                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                        <span class="text-gray-700 font-medium">Net Non-Operating</span>
                        <span class="text-xl font-bold" :class="period.net_non_operating >= 0 ? 'text-cyan-700' : 'text-red-700'">
                            {{ money(period.net_non_operating) }}
                        </span>
                    </div>

                    <!-- Net Profit Before Tax -->
                    <div class="flex justify-between items-center py-3 border-b border-gray-200 bg-green-50 px-3 -mx-3 rounded">
                        <span class="text-gray-900 font-bold">Net Profit Before Tax</span>
                        <span class="text-2xl font-bold text-green-700">{{ money(period.net_profit_before_tax) }}</span>
                    </div>

                    <!-- Tax -->
                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                        <span class="text-gray-700 font-medium">Tax Expenses</span>
                        <span class="text-xl font-bold text-yellow-700">({{ money(period.total_tax) }})</span>
                    </div>

                    <!-- Net Profit After Tax -->
                    <div class="flex justify-between items-center py-4 bg-gradient-to-r from-purple-100 to-indigo-100 px-4 -mx-3 rounded-lg border-2 border-purple-300">
                        <span class="text-xl font-bold text-gray-900">NET PROFIT AFTER TAX</span>
                        <span class="text-3xl font-bold text-purple-700">{{ money(period.net_profit_after_tax) }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <Link href="/accounting/income/travel-tourism" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-all hover:scale-105">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-700" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Income</p>
                            <p class="text-xs text-gray-500">Manage</p>
                        </div>
                    </div>
                </Link>

                <Link href="/accounting/operating-expenses" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-all hover:scale-105">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-700" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Expenses</p>
                            <p class="text-xs text-gray-500">Track</p>
                        </div>
                    </div>
                </Link>

                <Link href="/accounting/tax" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-all hover:scale-105">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-700" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Tax</p>
                            <p class="text-xs text-gray-500">Manage</p>
                        </div>
                    </div>
                </Link>

                <Link href="/accounting/vat-summary" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-all hover:scale-105">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-700" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">VAT</p>
                            <p class="text-xs text-gray-500">Summary</p>
                        </div>
                    </div>
                </Link>

                <Link href="/accounting/net-profit-after-tax" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-all hover:scale-105">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-700" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Net Profit</p>
                            <p class="text-xs text-gray-500">View</p>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Pie, Doughnut, Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    ArcElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js';

// Register Chart.js components
ChartJS.register(
    ArcElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
);

const props = defineProps({
    period: Object,
    periods: Array,
    incomeBreakdown: Object,
    expenseBreakdown: Object,
    plOverview: Object,
});

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT' }).format(value);
};

const profitMargin = (profit, revenue) => {
    if (!revenue || revenue === 0) return 0;
    return ((profit / revenue) * 100).toFixed(1);
};

const statusClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-700',
        active: 'bg-green-100 text-green-700',
        closed: 'bg-blue-100 text-blue-700',
    };
    return classes[status] || 'bg-gray-100 text-gray-700';
};

// Income Breakdown Pie Chart
const incomeChartData = computed(() => ({
    labels: ['Travel & Tourism', 'Manpower Exporting', 'Student Package', 'Other Income'],
    datasets: [{
        data: [
            props.incomeBreakdown.travel_tourism,
            props.incomeBreakdown.manpower_exporting,
            props.incomeBreakdown.student_package,
            props.incomeBreakdown.other_income,
        ],
        backgroundColor: [
            'rgba(34, 197, 94, 0.8)',  // green
            'rgba(59, 130, 246, 0.8)',  // blue
            'rgba(168, 85, 247, 0.8)',  // purple
            'rgba(251, 191, 36, 0.8)',  // yellow
        ],
        borderColor: [
            'rgb(34, 197, 94)',
            'rgb(59, 130, 246)',
            'rgb(168, 85, 247)',
            'rgb(251, 191, 36)',
        ],
        borderWidth: 2,
    }],
}));

// Expense Breakdown Pie Chart
const expenseChartData = computed(() => ({
    labels: ['Employee & Manpower', 'Administrative', 'Selling & Marketing', 'General'],
    datasets: [{
        data: [
            props.expenseBreakdown.employee_manpower,
            props.expenseBreakdown.administrative,
            props.expenseBreakdown.selling_marketing,
            props.expenseBreakdown.general,
        ],
        backgroundColor: [
            'rgba(239, 68, 68, 0.8)',   // red
            'rgba(249, 115, 22, 0.8)',  // orange
            'rgba(236, 72, 153, 0.8)',  // pink
            'rgba(156, 163, 175, 0.8)', // gray
        ],
        borderColor: [
            'rgb(239, 68, 68)',
            'rgb(249, 115, 22)',
            'rgb(236, 72, 153)',
            'rgb(156, 163, 175)',
        ],
        borderWidth: 2,
    }],
}));

// P&L Overview Doughnut Chart
const plOverviewChartData = computed(() => ({
    labels: ['Revenue', 'Cost of Sales', 'Operating Expenses', 'Tax', 'Net Profit'],
    datasets: [{
        data: [
            props.plOverview.revenue,
            props.plOverview.cost_of_sales,
            props.plOverview.operating_expenses,
            props.plOverview.tax,
            Math.abs(props.plOverview.net_profit),
        ],
        backgroundColor: [
            'rgba(34, 197, 94, 0.8)',   // green - revenue
            'rgba(239, 68, 68, 0.8)',   // red - cost
            'rgba(249, 115, 22, 0.8)',  // orange - expenses
            'rgba(251, 191, 36, 0.8)',  // yellow - tax
            'rgba(139, 92, 246, 0.8)',  // purple - profit
        ],
        borderColor: [
            'rgb(34, 197, 94)',
            'rgb(239, 68, 68)',
            'rgb(249, 115, 22)',
            'rgb(251, 191, 36)',
            'rgb(139, 92, 246)',
        ],
        borderWidth: 2,
    }],
}));

// Performance Trend with Wave Pattern
const performanceTrendData = computed(() => {
    const months = 12;
    const labels = [];
    const revenueData = [];
    const profitData = [];

    // Generate wave pattern data
    for (let i = 0; i < months; i++) {
        labels.push(`Month ${i + 1}`);
        // Create sine wave pattern for revenue
        const revenue = props.period.total_income * (1 + 0.3 * Math.sin(i * Math.PI / 6));
        revenueData.push(revenue);
        // Create wave pattern for profit with phase shift
        const profit = props.period.net_profit_after_tax * (1 + 0.4 * Math.sin((i + 2) * Math.PI / 6));
        profitData.push(profit);
    }

    return {
        labels,
        datasets: [
            {
                label: 'Revenue Trend',
                data: revenueData,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true,
            },
            {
                label: 'Profit Trend',
                data: profitData,
                borderColor: 'rgb(139, 92, 246)',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.4,
                fill: true,
            },
        ],
    };
});

const pieChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                padding: 15,
                font: {
                    size: 11,
                },
            },
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    const label = context.label || '';
                    const value = context.parsed || 0;
                    const formatted = new Intl.NumberFormat('en-BD', {
                        style: 'currency',
                        currency: 'BDT'
                    }).format(value);
                    return `${label}: ${formatted}`;
                },
            },
        },
    },
};

const doughnutChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                padding: 10,
                font: {
                    size: 10,
                },
            },
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    const label = context.label || '';
                    const value = context.parsed || 0;
                    const formatted = new Intl.NumberFormat('en-BD', {
                        style: 'currency',
                        currency: 'BDT'
                    }).format(value);
                    return `${label}: ${formatted}`;
                },
            },
        },
    },
};

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                padding: 15,
                font: {
                    size: 11,
                },
            },
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    const label = context.dataset.label || '';
                    const value = context.parsed.y || 0;
                    const formatted = new Intl.NumberFormat('en-BD', {
                        style: 'currency',
                        currency: 'BDT'
                    }).format(value);
                    return `${label}: ${formatted}`;
                },
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: function(value) {
                    return '৳' + (value / 1000).toFixed(0) + 'K';
                },
            },
        },
    },
};
</script>
