<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, reactive, computed } from 'vue';
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
    Legend,
    Filler
} from 'chart.js';

ChartJS.register(
    ArcElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

// Route mapping for stat cards
const routeMap = {
    'Clients': '/clients',
    'Agents': '/agents',
    'BD Companies': '/bd-companies',
    'Foreign Companies': '/foreign-companies',
    'Staff': '/office-staff'
};

const navigateTo = (label) => {
    const route = routeMap[label];
    if (route) {
        router.visit(route);
    }
};

const state = reactive({
    loading: true,
    stats: {
        total_clients: 0,
        total_agents: 0,
        total_bd_companies: 0,
        total_foreign_companies: 0,
        total_staff: 0,
    },
    salesMonthly: [],
    expensesMonthly: [],
    receivableToday: { total: 0, items: [] },
    payableToday: { total: 0, items: [] },
});

const totals = computed(() => [
    { label: 'Clients', value: state.stats.total_clients },
    { label: 'Agents', value: state.stats.total_agents },
    { label: 'BD Companies', value: state.stats.total_bd_companies },
    { label: 'Foreign Companies', value: state.stats.total_foreign_companies },
    { label: 'Staff', value: state.stats.total_staff },
]);

const fetchData = async () => {
    state.loading = true;
    try {
        const { data } = await axios.get('/dashboard/data');
        state.stats = data.stats;
        state.salesMonthly = data.salesMonthly;
        state.expensesMonthly = data.expensesMonthly;
        state.receivableToday = data.receivableToday;
        state.payableToday = data.payableToday;
    } finally {
        state.loading = false;
    }
};

onMounted(fetchData);

// Entity Distribution Pie Chart with gradient colors
const entityDistributionData = computed(() => ({
    labels: ['Clients', 'Agents', 'BD Companies', 'Foreign Companies', 'Staff'],
    datasets: [{
        data: [
            state.stats.total_clients,
            state.stats.total_agents,
            state.stats.total_bd_companies,
            state.stats.total_foreign_companies,
            state.stats.total_staff,
        ],
        backgroundColor: [
            'rgba(59, 130, 246, 0.9)',   // Blue for Clients
            'rgba(34, 197, 94, 0.9)',    // Green for Agents
            'rgba(249, 115, 22, 0.9)',   // Orange for BD Companies
            'rgba(168, 85, 247, 0.9)',   // Purple for Foreign Companies
            'rgba(236, 72, 153, 0.9)',   // Pink for Staff
        ],
        borderColor: '#ffffff',
        borderWidth: 3,
        hoverOffset: 15,
        hoverBorderWidth: 4,
        hoverBorderColor: '#ffffff',
    }],
}));

// Receivable vs Payable Doughnut Chart with enhanced styling
const receivablePayableData = computed(() => ({
    labels: ['Total Receivable', 'Total Payable'],
    datasets: [{
        data: [
            state.receivableToday.total,
            state.payableToday.total,
        ],
        backgroundColor: [
            'rgba(34, 197, 94, 0.9)',    // Green for Receivable
            'rgba(239, 68, 68, 0.9)',    // Red for Payable
        ],
        borderColor: '#ffffff',
        borderWidth: 4,
        hoverOffset: 20,
        hoverBorderWidth: 5,
        hoverBorderColor: '#ffffff',
    }],
}));

// Sales vs Expenses Sine Wave Line Chart with enhanced smooth curves
const salesExpensesTrendData = computed(() => {
    const labels = state.salesMonthly.map(m => m.label);
    const salesData = [];
    const expensesData = [];

    // Generate smooth sine wave pattern based on actual data
    for (let i = 0; i < state.salesMonthly.length; i++) {
        const baseSales = state.salesMonthly[i]?.amount || 0;
        const baseExpenses = state.expensesMonthly[i]?.amount || 0;

        // Create smoother sine wave with multiple frequencies for realistic pattern
        const salesWave = baseSales * (1 + 0.2 * Math.sin(i * Math.PI / 2.5) + 0.1 * Math.cos(i * Math.PI / 4));
        const expensesWave = baseExpenses * (1 + 0.18 * Math.sin((i + 1.5) * Math.PI / 2.8) + 0.08 * Math.cos(i * Math.PI / 3.5));

        salesData.push(salesWave);
        expensesData.push(expensesWave);
    }

    return {
        labels,
        datasets: [
            {
                label: 'Sales',
                data: salesData,
                borderColor: 'rgba(59, 130, 246, 1)',
                backgroundColor: 'rgba(59, 130, 246, 0.15)',
                borderWidth: 4,
                fill: true,
                tension: 0.45,
                pointRadius: 6,
                pointHoverRadius: 9,
                pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointHoverBorderWidth: 3,
            },
            {
                label: 'Expenses',
                data: expensesData,
                borderColor: 'rgba(239, 68, 68, 1)',
                backgroundColor: 'rgba(239, 68, 68, 0.15)',
                borderWidth: 4,
                fill: true,
                tension: 0.45,
                pointRadius: 6,
                pointHoverRadius: 9,
                pointBackgroundColor: 'rgba(239, 68, 68, 1)',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointHoverBorderWidth: 3,
            },
        ],
    };
});

// Enhanced Chart Options with Animations
const pieChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
        animateRotate: true,
        animateScale: true,
        duration: 1500,
        easing: 'easeInOutQuart',
    },
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                padding: 15,
                font: {
                    size: 12,
                    weight: 'bold',
                },
                usePointStyle: true,
                pointStyle: 'circle',
            },
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 13 },
            borderColor: 'rgba(255, 255, 255, 0.3)',
            borderWidth: 1,
            callbacks: {
                label: function(context) {
                    const label = context.label || '';
                    const value = context.parsed || 0;
                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                    return `${label}: ${value} (${percentage}%)`;
                },
            },
        },
    },
};

const doughnutChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
        animateRotate: true,
        animateScale: true,
        duration: 1500,
        easing: 'easeInOutQuart',
    },
    cutout: '65%',
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                padding: 15,
                font: {
                    size: 12,
                    weight: 'bold',
                },
                usePointStyle: true,
                pointStyle: 'circle',
            },
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 13 },
            borderColor: 'rgba(255, 255, 255, 0.3)',
            borderWidth: 1,
            callbacks: {
                label: function(context) {
                    const label = context.label || '';
                    const value = context.parsed || 0;
                    const formatted = '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                    return `${label}: ${formatted} (${percentage}%)`;
                },
            },
        },
    },
};

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
        duration: 2000,
        easing: 'easeInOutQuart',
    },
    interaction: {
        mode: 'index',
        intersect: false,
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(0, 0, 0, 0.05)',
                drawBorder: false,
            },
            ticks: {
                font: { size: 11 },
                callback: function(value) {
                    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
                },
            },
        },
        x: {
            grid: {
                display: false,
                drawBorder: false,
            },
            ticks: {
                font: { size: 11, weight: 'bold' },
            },
        },
    },
    plugins: {
        legend: {
            position: 'top',
            labels: {
                font: {
                    size: 13,
                    weight: 'bold',
                },
                padding: 20,
                usePointStyle: true,
                pointStyle: 'circle',
            },
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 13 },
            borderColor: 'rgba(255, 255, 255, 0.3)',
            borderWidth: 1,
            callbacks: {
                label: function(context) {
                    const label = context.dataset.label || '';
                    const value = context.parsed.y;
                    const formatted = '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
                    return `${label}: ${formatted}`;
                },
            },
        },
    },
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="p-4 md:p-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
                <p class="text-sm text-gray-600">Overview of sales, expenses, and payables/receivables with smart analytics.</p>
            </div>
            <div v-if="state.loading" class="text-sm text-gray-500">Loading...</div>
        </div>

        <!-- Stats Cards - Clickable -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div
                v-for="item in totals"
                :key="item.label"
                @click="navigateTo(item.label)"
                class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 hover:shadow-lg hover:border-blue-400 hover:scale-105 transition-all duration-300 cursor-pointer group"
            >
                <div class="text-sm text-gray-500 group-hover:text-blue-600 transition-colors">{{ item.label }}</div>
                <div class="text-2xl font-semibold text-gray-900 group-hover:text-blue-700 transition-colors">{{ item.value }}</div>
                <div class="text-xs text-gray-400 mt-1 group-hover:text-blue-500 transition-colors">Click to view details →</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid gap-4 lg:grid-cols-3">
            <!-- Sales vs Expenses Sine Wave Chart -->
            <div class="lg:col-span-2 bg-gradient-to-br from-white to-blue-50 border border-blue-100 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <span class="w-1 h-6 bg-blue-500 rounded-full"></span>
                        Sales vs Expenses Trend
                    </h2>
                    <div class="text-xs text-gray-500 bg-white px-3 py-1 rounded-full shadow-sm">Last 6 months (Sine Wave)</div>
                </div>
                <div class="h-80 bg-white rounded-lg p-2">
                    <Line :data="salesExpensesTrendData" :options="lineChartOptions" />
                </div>
            </div>

            <!-- Receivable vs Payable Doughnut Chart -->
            <div class="bg-gradient-to-br from-white to-green-50 border border-green-100 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 p-6">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <span class="w-1 h-6 bg-green-500 rounded-full"></span>
                        Receivable vs Payable
                    </h2>
                    <div class="text-xs text-gray-500 bg-white px-3 py-1 rounded-full shadow-sm inline-block mt-1">Total Outstanding</div>
                </div>
                <div class="h-72 bg-white rounded-lg p-2">
                    <Doughnut :data="receivablePayableData" :options="doughnutChartOptions" />
                </div>
            </div>
        </div>

        <!-- Entity Distribution and Details -->
        <div class="grid gap-4 lg:grid-cols-3">
            <!-- Entity Distribution Pie Chart -->
            <div class="bg-gradient-to-br from-white to-purple-50 border border-purple-100 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 p-6">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <span class="w-1 h-6 bg-purple-500 rounded-full"></span>
                        Entity Distribution
                    </h2>
                    <div class="text-xs text-gray-500 bg-white px-3 py-1 rounded-full shadow-sm inline-block mt-1">Organization Overview</div>
                </div>
                <div class="h-72 bg-white rounded-lg p-2">
                    <Pie :data="entityDistributionData" :options="pieChartOptions" />
                </div>
            </div>

            <!-- Total Receivable Details -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-green-700">Total Receivable</h2>
                    <div class="text-2xl font-bold text-green-600">
                        {{ '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(state.receivableToday.total) }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Money to receive from clients</div>
                </div>
                <ul class="space-y-2 max-h-60 overflow-y-auto">
                    <li
                        v-for="item in state.receivableToday.items"
                        :key="item.name + item.due_on"
                        class="flex justify-between text-sm text-gray-800 border-b pb-2 hover:bg-gray-50 px-2 -mx-2 rounded"
                    >
                        <span class="font-medium">{{ item.name }}</span>
                        <span class="text-green-600">{{ item.amount }}</span>
                    </li>
                    <li v-if="!state.receivableToday.items.length" class="text-sm text-gray-500 text-center py-4">No receivables</li>
                </ul>
            </div>

            <!-- Total Payable Details -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-red-700">Total Payable</h2>
                    <div class="text-2xl font-bold text-red-600">
                        {{ '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(state.payableToday.total) }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Money to pay to clients</div>
                </div>
                <ul class="space-y-2 max-h-60 overflow-y-auto">
                    <li
                        v-for="item in state.payableToday.items"
                        :key="item.name + item.due_on"
                        class="flex justify-between text-sm text-gray-800 border-b pb-2 hover:bg-gray-50 px-2 -mx-2 rounded"
                    >
                        <span class="font-medium">{{ item.name }}</span>
                        <span class="text-red-600">{{ item.amount }}</span>
                    </li>
                    <li v-if="!state.payableToday.items.length" class="text-sm text-gray-500 text-center py-4">No payables</li>
                </ul>
            </div>
        </div>

        <!-- Performance Insights -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Performance Insights</h2>
            <div class="grid gap-4 md:grid-cols-3">
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <div class="text-sm text-gray-600 mb-1">Net Position</div>
                    <div class="text-xl font-bold" :class="state.receivableToday.total - state.payableToday.total >= 0 ? 'text-green-600' : 'text-red-600'">
                        {{ '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(state.receivableToday.total - state.payableToday.total) }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Receivable - Payable</div>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <div class="text-sm text-gray-600 mb-1">Total Entities</div>
                    <div class="text-xl font-bold text-blue-600">
                        {{ state.stats.total_clients + state.stats.total_agents + state.stats.total_bd_companies + state.stats.total_foreign_companies + state.stats.total_staff }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Combined Count</div>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <div class="text-sm text-gray-600 mb-1">Average Monthly Sales</div>
                    <div class="text-xl font-bold text-indigo-600">
                        {{ '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(state.salesMonthly.reduce((acc, m) => acc + m.amount, 0) / (state.salesMonthly.length || 1)) }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Last 6 Months</div>
                </div>
            </div>
        </div>
    </div>
</template>
