<script setup>
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { onMounted, reactive, computed, ref, watch } from "vue";
import { Bar, Doughnut, Line } from "vue-chartjs";
import {
    Chart as ChartJS,
    ArcElement,
    BarElement,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Title,
    Tooltip,
    Legend,
    Filler,
} from "chart.js";

ChartJS.register(
    ArcElement,
    BarElement,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Title,
    Tooltip,
    Legend,
    Filler
);

const page = usePage();

// Route mapping for stat cards
const routeMap = {
    Clients: "/clients",
    Agents: "/agents",
    "Vendors": "/bd-companies",
    "Foreign Companies": "/foreign-companies",
    Staff: "/office-staff",
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
    salesSummary: { total: 0, paid: 0, due: 0, expenses: 0 },
    appUsage: { total: 0, path: "" },
    bdCompanyFiles: {
        agency_total: 0,
        total: 0,
        pending: 0,
        accepted: 0,
        rejected: 0,
        completed: 0,
    },
    agentClientSummary: [],
    foreignCountrySummary: [],
    refundSummary: { total: 0, count: 0, items: [] },
    appName: "",
    errorLog: null,
    ticketStats: { total: 0, confirmed: 0, rescheduled: 0, upcoming: [] },
});

const fetchData = async () => {
    state.loading = true;
    state.errorLog = null;
    try {
        const params = {};
        const { data } = await axios.get(typeof route !== 'undefined' ? route('dashboard.data') : "/dashboard/data", { params });
        state.stats = data.stats;
        state.salesMonthly = data.salesMonthly || [];
        state.expensesMonthly = data.expensesMonthly || [];
        state.receivableToday = data.receivableToday;
        state.payableToday = data.payableToday;
        state.salesSummary = data.salesSummary || {
            total: 0,
            paid: 0,
            due: 0,
            expenses: 0,
        };
        state.appUsage = data.appUsage || { total: 0, path: "" };
        state.bdCompanyFiles = data.bdCompanyFiles || {
            agency_total: 0,
            total: 0,
            pending: 0,
            accepted: 0,
            rejected: 0,
            completed: 0,
        };
        state.agentClientSummary = data.agentClientSummary || [];
        state.foreignCountrySummary = data.foreignCountrySummary || [];
        state.refundSummary = data.refundSummary || {
            total: 0,
            count: 0,
            items: [],
        };
        state.appName = data.appName || "";
        state.ticketStats = data.ticketStats || { total: 0, confirmed: 0, rescheduled: 0, upcoming: [] };
    } catch (e) {
        state.errorLog = e.message || String(e);
        if (e.response && e.response.data) {
            state.errorLog += " | Backend: " + JSON.stringify(e.response.data).substring(0, 200);
        }
    } finally {
        state.loading = false;
    }
};

onMounted(fetchData);

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: { display: false, beginAtZero: true },
        x: { grid: { display: false, drawBorder: false }, ticks: { font: { weight: '600' }, color: '#9ca3af' } }
    },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: "rgba(0, 0, 0, 0.8)",
            padding: 12,
            callbacks: {
                label: function (context) {
                    const value = context.parsed.y;
                    return `৳` + new Intl.NumberFormat("en-BD", { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
                },
            },
        },
    },
};

// ── Sales Line Chart (dark + gradient fill) ──
const salesLineData = computed(() => {
    const labels    = state.salesMonthly.length    ? state.salesMonthly.map(m => m.label?.substring(0,3))    : ['Jan','Feb','Mar','Apr','May','Jun'];
    const salesData = state.salesMonthly.length    ? state.salesMonthly.map(m => m.amount || 0)    : [0, 0, 0, 330000, 0, 0];
    const expData   = state.expensesMonthly.length ? state.expensesMonthly.map(m => m.amount || 0) : [0, 0, 0, 0, 0, 0];

    const salesGradient = (ctx) => {
        const { chart } = ctx;
        const { chartArea } = chart;
        if (!chartArea) return 'rgba(251,146,60,0.5)';
        const g = chart.ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
        g.addColorStop(0, 'rgba(251,146,60,0.75)');
        g.addColorStop(0.5, 'rgba(239,68,68,0.45)');
        g.addColorStop(1, 'rgba(251,146,60,0.02)');
        return g;
    };

    const expGradient = (ctx) => {
        const { chart } = ctx;
        const { chartArea } = chart;
        if (!chartArea) return 'rgba(168,85,247,0.4)';
        const g = chart.ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
        g.addColorStop(0, 'rgba(192,38,211,0.70)');
        g.addColorStop(0.5, 'rgba(139,92,246,0.40)');
        g.addColorStop(1, 'rgba(168,85,247,0.02)');
        return g;
    };

    return {
        labels,
        datasets: [
            {
                label: 'Sales',
                data: salesData,
                borderColor: '#fb923c',
                backgroundColor: salesGradient,
                borderWidth: 2.5,
                pointBackgroundColor: '#fb923c',
                pointBorderColor: 'rgba(255,255,255,0.3)',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 7,
                tension: 0.45,
                fill: true,
            },
            {
                label: 'Expenses',
                data: expData,
                borderColor: '#c026d3',
                backgroundColor: expGradient,
                borderWidth: 2.5,
                pointBackgroundColor: '#c026d3',
                pointBorderColor: 'rgba(255,255,255,0.3)',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 7,
                tension: 0.45,
                fill: true,
            },
        ],
    };
});

const salesLineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    scales: {
        y: {
            beginAtZero: true,
            grid: { color: 'rgba(0,0,0,0.06)', drawBorder: false },
            ticks: {
                color: '#9ca3af',
                font: { size: 10 },
                callback: v => '৳' + new Intl.NumberFormat('en-BD', { notation: 'compact' }).format(v),
            },
            border: { display: false },
        },
        x: {
            grid: { color: 'rgba(0,0,0,0.04)' },
            ticks: { color: '#6b7280', font: { size: 10, weight: '600' } },
            border: { display: false },
        },
    },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(15,10,30,0.95)',
            padding: 12,
            titleFont: { size: 12, weight: 'bold' },
            titleColor: '#fff',
            bodyColor: 'rgba(255,255,255,0.75)',
            borderColor: 'rgba(255,255,255,0.1)',
            borderWidth: 1,
            callbacks: {
                label: ctx => '  ' + ctx.dataset.label + ': ৳' + new Intl.NumberFormat('en-BD').format(ctx.parsed.y),
            },
        },
    },
};

// ── Tracking multi-ring donut ──
const trackingRingData = computed(() => {
    const total     = (mittPipelineTotal.value || 1);
    const zttbl     = state.bdCompanyFiles?.agency_total || 0;
    const pending   = state.bdCompanyFiles?.pending      || 0;
    const completed = state.bdCompanyFiles?.completed    || 0;
    const accepted  = state.bdCompanyFiles?.accepted     || 0;
    const rejected  = state.bdCompanyFiles?.rejected     || 0;
    return { total, zttbl, pending, completed, accepted, rejected };
});

const trackingDonutMulti = computed(() => ({
    labels: ['ZTTBL','Vendor Completed','Vendor Accepted','Vendor Pending'],
    datasets: [
        {
            label: 'ZTTBL',
            data: [
                trackingRingData.value.zttbl,
                Math.max(0, trackingRingData.value.total - trackingRingData.value.zttbl),
            ],
            backgroundColor: ['#3b82f6','rgba(59,130,246,0.07)'],
            borderWidth: 0, cutout: '86%', borderRadius: [6,0],
        },
        {
            label: 'Vendor Completed',
            data: [
                trackingRingData.value.completed,
                Math.max(0, trackingRingData.value.total - trackingRingData.value.completed),
            ],
            backgroundColor: ['#a855f7','rgba(168,85,247,0.07)'],
            borderWidth: 0, cutout: '74%', borderRadius: [6,0],
        },
        {
            label: 'Vendor Accepted',
            data: [
                trackingRingData.value.accepted,
                Math.max(0, trackingRingData.value.total - trackingRingData.value.accepted),
            ],
            backgroundColor: ['#10b981','rgba(16,185,129,0.07)'],
            borderWidth: 0, cutout: '62%', borderRadius: [6,0],
        },
        {
            label: 'Vendor Pending',
            data: [
                trackingRingData.value.pending,
                Math.max(0, trackingRingData.value.total - trackingRingData.value.pending),
            ],
            backgroundColor: ['#ec4899','rgba(236,72,153,0.07)'],
            borderWidth: 0, cutout: '50%', borderRadius: [6,0],
        },
    ],
}));

const trackingDonutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    animation: { animateRotate: true, animateScale: true, duration: 1200 },
    plugins: {
        legend: { display: false },
        tooltip: {
            enabled: true,
            backgroundColor: 'rgba(10,15,30,0.92)',
            padding: 10,
            titleFont: { size: 0 },
            bodyFont: { size: 12, weight: 'bold' },
            bodyColor: '#fff',
            borderColor: 'rgba(255,255,255,0.1)',
            borderWidth: 1,
            callbacks: {
                title: () => '',
                label: (ctx) => {
                    if (ctx.dataIndex !== 0) return null;
                    const labels = ['ZTTBL (At Agency)', 'Vendor Completed', 'Vendor Accepted', 'Vendor Pending'];
                    const val = ctx.parsed;
                    const total = trackingRingData.value.total || 1;
                    const pct = Math.round(val / total * 100);
                    return `  ${labels[ctx.datasetIndex]}: ${val}  (${pct}%)`;
                },
            },
        },
    },
};

const salesExpensesTrendData = computed(() => {
    const labels = state.salesMonthly.map((m) => m.label?.substring(0, 3) || '');
    const salesData = state.salesMonthly.map((m) => m.amount || 0);
    const expensesData = state.expensesMonthly.map((m) => m.amount || 0);

    return {
        // Use first letter of month like image uses S M T W T F S
        labels: labels.length ? labels : ['S', 'M', 'T', 'W', 'T', 'F', 'S'],
        datasets: [
            {
                label: "Sales",
                data: salesData.length ? salesData : [1000, 2000, 1500, 3000, 2500, 4000, 3500],
                backgroundColor: "#1d4ed8",
                borderRadius: { topLeft: 25, topRight: 25, bottomLeft: 25, bottomRight: 25 },
                borderSkipped: false,
                barPercentage: 0.8,
                categoryPercentage: 0.8
            },
            {
                label: "Expenses",
                data: expensesData.length ? expensesData : [800, 1500, 1200, 2000, 1800, 2500, 2200],
                backgroundColor: "#b8decb",
                borderRadius: { topLeft: 25, topRight: 25, bottomLeft: 25, bottomRight: 25 },
                borderSkipped: false,
                barPercentage: 0.8,
                categoryPercentage: 0.8
            },
        ],
    };
});

const doughnutChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: "80%",
    circumference: 180,
    rotation: 270,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: function (context) {
                    const label = context.label || "";
                    const value = context.parsed || 0;
                    return label + ": ৳" + new Intl.NumberFormat("en-BD").format(value);
                },
            },
        },
    },
};

const receivablePayableData = computed(() => {
    const receivable = state.receivableToday?.total || 41;
    const payable = state.payableToday?.total || 59;
    
    return {
        labels: ["Receivable", "Payable"],
        datasets: [
            {
                data: [receivable, payable],
                backgroundColor: ["#1d4ed8", "#e0ede6"],
                borderWidth: 0,
                borderRadius: [20, 20],
                cutout: "75%",
                hoverOffset: 0
            },
        ],
    };
});

const formatBytes = (bytes) => {
    if (!bytes || bytes <= 0) return "0 GB";
    const gb = bytes / (1024 * 1024 * 1024);
    return gb.toFixed(2) + " GB";
};

const goToClients = (query) => {
    router.visit("/clients", { data: query });
};

const goToAgent = (agentId) => {
    if (!agentId) return;
    router.visit(`/agents/${agentId}`);
};

const mittPipelineTotal = computed(
    () => (state.bdCompanyFiles?.agency_total || 0) + (state.bdCompanyFiles?.total || 0),
);

const fileTrackingDonutData = computed(() => ({
    labels: [
        "ZTTBL Total",
        "BD Pending",
        "BD Accepted",
        "BD Rejected",
        "BD Completed",
    ],
    datasets: [
        {
            data: [
                mittPipelineTotal.value,
                state.bdCompanyFiles?.pending || 0,
                state.bdCompanyFiles?.accepted || 0,
                state.bdCompanyFiles?.rejected || 0,
                state.bdCompanyFiles?.completed || 0,
            ],
            backgroundColor: [
                "#3b82f6", // Blue
                "#f59e0b", // Amber
                "#10b981", // Emerald
                "#ef4444", // Red
                "#06b6d4", // Cyan
            ],
            borderWidth: 0,
            hoverOffset: 4,
            cutout: "75%",
            borderRadius: [8, 8, 8, 8, 8]
        },
    ],
}));

const fileTrackingDonutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: "70%",
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: "rgba(0, 0, 0, 0.8)",
            padding: 10,
        },
    },
};

const bdCompanyFilesData = computed(() => ({
    labels: [
        "ZTTBL",
        "BD",
        "Pen",
        "Acc",
        "Rej",
        "Com",
    ],
    datasets: [
        {
            label: "Files",
            data: [
                mittPipelineTotal.value,
                state.bdCompanyFiles?.total || 0,
                state.bdCompanyFiles?.pending || 0,
                state.bdCompanyFiles?.accepted || 0,
                state.bdCompanyFiles?.rejected || 0,
                state.bdCompanyFiles?.completed || 0,
            ],
            backgroundColor: [
                "#1d4ed8",
                "#2f8863",
                "#b8decb",
                "#10b981",
                "#ef4444",
                "#06b6d4"
            ],
            borderRadius: 12,
            borderSkipped: false,
            barPercentage: 0.6,
        },
    ]
}));

const bdCompanyFilesOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: { display: false, beginAtZero: true },
        x: { grid: { display: false, drawBorder: false }, ticks: { font: { weight: '600' }, color: '#9ca3af' } }
    },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: "rgba(0, 0, 0, 0.8)",
            padding: 12,
        },
    },
};

</script>

<template>
    <Head title="Dashboard" />

    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans">
        
        <!-- Debugging Error Log Banner -->
        <div v-if="state.errorLog" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <strong class="font-bold">Fetch Error: </strong>
            <span class="block sm:inline">{{ state.errorLog }}</span>
        </div>


        <!-- Main Dashboard Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">Dashboard</h1>
                <p class="text-sm text-gray-500">Plan, prioritize, and accomplish your tasks with ease.</p>
            </div>
        </div>

        <!-- Stat Cards (4 cols) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-5">

            <!-- Card 1: Total Clients — Royal Blue -->
            <div class="elite-card group cursor-pointer rounded-[20px] p-5 text-white relative overflow-hidden"
                 style="background: linear-gradient(135deg, #5B7CF7 0%, #4361EE 50%, #3730D4 100%);
                        box-shadow: 0 4px 15px rgba(67,97,238,0.35), 0 12px 30px rgba(67,97,238,0.2), 0 30px 50px rgba(67,97,238,0.1);"
                 @click="navigateTo('Clients')">
                <div class="absolute -right-6 -top-6 w-32 h-32 rounded-full opacity-20 pointer-events-none"
                     style="background: radial-gradient(circle, #fff, transparent 70%);"></div>
                <div class="absolute -left-4 -bottom-4 w-24 h-24 rounded-full opacity-10 pointer-events-none"
                     style="background: radial-gradient(circle, #a5b4fc, transparent 70%);"></div>
                <div class="relative z-10 flex justify-between items-start mb-5">
                    <p class="text-white/70 text-[12px] font-semibold uppercase tracking-wider">Total Clients</p>
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center bg-white/15 backdrop-blur-sm ring-1 ring-white/25 group-hover:bg-white/25 transition-all">
                        <font-awesome-icon icon="users" class="w-4 h-4 text-white" />
                    </div>
                </div>
                <div class="relative z-10 text-[52px] font-black leading-none tracking-tight" style="text-shadow: 0 2px 8px rgba(0,0,0,0.15);">{{ state.stats.total_clients ?? 0 }}</div>
            </div>

            <!-- Card 2: Agents — Purple -->
            <div class="elite-card group cursor-pointer rounded-[20px] p-5 text-white relative overflow-hidden"
                 style="background: linear-gradient(135deg, #B06CF7 0%, #9333EA 50%, #7E22CE 100%);
                        box-shadow: 0 4px 15px rgba(147,51,234,0.35), 0 12px 30px rgba(147,51,234,0.2), 0 30px 50px rgba(147,51,234,0.1);"
                 @click="navigateTo('Agents')">
                <div class="absolute -right-6 -top-6 w-32 h-32 rounded-full opacity-20 pointer-events-none"
                     style="background: radial-gradient(circle, #fff, transparent 70%);"></div>
                <div class="absolute -left-4 -bottom-4 w-24 h-24 rounded-full opacity-10 pointer-events-none"
                     style="background: radial-gradient(circle, #e9d5ff, transparent 70%);"></div>
                <div class="relative z-10 flex justify-between items-start mb-5">
                    <p class="text-white/70 text-[12px] font-semibold uppercase tracking-wider">Agents</p>
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center bg-white/15 backdrop-blur-sm ring-1 ring-white/25 group-hover:bg-white/25 transition-all">
                        <font-awesome-icon icon="address-card" class="w-4 h-4 text-white" />
                    </div>
                </div>
                <div class="relative z-10 text-[52px] font-black leading-none tracking-tight" style="text-shadow: 0 2px 8px rgba(0,0,0,0.15);">{{ state.stats.total_agents ?? 0 }}</div>
            </div>

            <!-- Card 3: Vendors — Teal -->
            <div class="elite-card group cursor-pointer rounded-[20px] p-5 text-white relative overflow-hidden"
                 style="background: linear-gradient(135deg, #06B6D4 0%, #0891B2 50%, #0E7490 100%);
                        box-shadow: 0 4px 15px rgba(8,145,178,0.35), 0 12px 30px rgba(8,145,178,0.2), 0 30px 50px rgba(8,145,178,0.1);"
                 @click="navigateTo('Vendors')">
                <div class="absolute -right-6 -top-6 w-32 h-32 rounded-full opacity-20 pointer-events-none"
                     style="background: radial-gradient(circle, #fff, transparent 70%);"></div>
                <div class="absolute -left-4 -bottom-4 w-24 h-24 rounded-full opacity-10 pointer-events-none"
                     style="background: radial-gradient(circle, #a5f3fc, transparent 70%);"></div>
                <div class="relative z-10 flex justify-between items-start mb-5">
                    <p class="text-white/70 text-[12px] font-semibold uppercase tracking-wider">Vendors</p>
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center bg-white/15 backdrop-blur-sm ring-1 ring-white/25 group-hover:bg-white/25 transition-all">
                        <font-awesome-icon icon="building" class="w-4 h-4 text-white" />
                    </div>
                </div>
                <div class="relative z-10 text-[52px] font-black leading-none tracking-tight" style="text-shadow: 0 2px 8px rgba(0,0,0,0.15);">{{ state.stats.total_bd_companies ?? 0 }}</div>
            </div>

            <!-- Card 4: Foreign Companies — Coral -->
            <div class="elite-card group cursor-pointer rounded-[20px] p-5 text-white relative overflow-hidden"
                 style="background: linear-gradient(135deg, #F97316 0%, #EA580C 50%, #C2410C 100%);
                        box-shadow: 0 4px 15px rgba(234,88,12,0.35), 0 12px 30px rgba(234,88,12,0.2), 0 30px 50px rgba(234,88,12,0.1);"
                 @click="navigateTo('Foreign Companies')">
                <div class="absolute -right-6 -top-6 w-32 h-32 rounded-full opacity-20 pointer-events-none"
                     style="background: radial-gradient(circle, #fff, transparent 70%);"></div>
                <div class="absolute -left-4 -bottom-4 w-24 h-24 rounded-full opacity-10 pointer-events-none"
                     style="background: radial-gradient(circle, #fed7aa, transparent 70%);"></div>
                <div class="relative z-10 flex justify-between items-start mb-5">
                    <p class="text-white/70 text-[12px] font-semibold uppercase tracking-wider">Foreign Companies</p>
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center bg-white/15 backdrop-blur-sm ring-1 ring-white/25 group-hover:bg-white/25 transition-all">
                        <font-awesome-icon icon="globe" class="w-4 h-4 text-white" />
                    </div>
                </div>
                <div class="relative z-10 text-[52px] font-black leading-none tracking-tight" style="text-shadow: 0 2px 8px rgba(0,0,0,0.15);">{{ state.stats.total_foreign_companies ?? 0 }}</div>
            </div>
        </div>

        <!-- ── Sales Trend + Spinning Ring ── -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-5">

            <!-- Sales vs Expenses — white + orange blend -->
            <div class="lg:col-span-8 rounded-[24px] overflow-hidden relative border border-orange-100/60"
                 style="background: linear-gradient(135deg, #ffffff 0%, #fff7ed 50%, #ffedd5 100%);
                        box-shadow: 0 4px_20px rgba(251,146,60,0.12), 0 12px 40px rgba(249,115,22,0.08);">

                <!-- Decorative blobs -->
                <div class="absolute -top-12 -right-12 w-48 h-48 rounded-full opacity-25 pointer-events-none"
                     style="background: radial-gradient(circle, #fdba74, transparent 70%);"></div>
                <div class="absolute bottom-0 left-1/4 w-40 h-40 rounded-full opacity-15 pointer-events-none"
                     style="background: radial-gradient(circle, #fed7aa, transparent 70%);"></div>

                <div class="relative z-10 px-6 pt-5 pb-5">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="font-bold text-gray-800 text-base tracking-tight">Sales vs Expenses Trend</h3>
                            <p class="text-gray-400 text-[11px] mt-0.5">Income and expense overview</p>
                        </div>
                        <span class="text-[10px] text-orange-500 font-semibold bg-orange-50 px-3 py-1.5 rounded-full border border-orange-100">Last 6 months</span>
                    </div>

                    <!-- Stats row -->
                    <div class="grid grid-cols-4 gap-px bg-orange-100/40 rounded-2xl overflow-hidden mb-5">
                        <div class="bg-white/70 px-4 py-3 text-center">
                            <p class="text-gray-400 text-[10px] font-medium uppercase tracking-wider mb-1">Total Sales</p>
                            <p class="text-gray-900 font-black text-[18px] leading-tight">৳{{ new Intl.NumberFormat('en-BD',{notation:'compact'}).format(state.salesSummary?.total || 0) }}</p>
                        </div>
                        <div class="bg-white/70 px-4 py-3 text-center">
                            <p class="text-orange-400 text-[10px] font-medium uppercase tracking-wider mb-1">Paid</p>
                            <p class="text-orange-600 font-black text-[18px] leading-tight">৳{{ new Intl.NumberFormat('en-BD',{notation:'compact'}).format(state.salesSummary?.paid || 0) }}</p>
                        </div>
                        <div class="bg-white/70 px-4 py-3 text-center">
                            <p class="text-amber-500 text-[10px] font-medium uppercase tracking-wider mb-1">Due</p>
                            <p class="text-amber-700 font-black text-[18px] leading-tight">৳{{ new Intl.NumberFormat('en-BD',{notation:'compact'}).format(state.salesSummary?.due || 0) }}</p>
                        </div>
                        <div class="bg-white/70 px-4 py-3 text-center">
                            <p class="text-red-400 text-[10px] font-medium uppercase tracking-wider mb-1">Expense</p>
                            <p class="text-red-600 font-black text-[18px] leading-tight">৳{{ new Intl.NumberFormat('en-BD',{notation:'compact'}).format(state.salesSummary?.expenses || 0) }}</p>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="flex items-center gap-5 mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-[3px] rounded-full" style="background:linear-gradient(90deg,#fb923c,#ef4444)"></div>
                            <span class="text-[11px] text-gray-500 font-semibold">Sales</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-[3px] rounded-full" style="background:linear-gradient(90deg,#c026d3,#8b5cf6)"></div>
                            <span class="text-[11px] text-gray-500 font-semibold">Expenses</span>
                        </div>
                    </div>

                    <!-- Chart -->
                    <div class="rounded-2xl overflow-hidden p-3 h-52 bg-white border border-orange-100/50">
                        <Line :data="salesLineData" :options="salesLineOptions" />
                    </div>
                </div>
            </div>

            <!-- Spinning Multi-ring Tracking Donut -->
            <div class="lg:col-span-4 bg-white rounded-[24px] p-6 border border-slate-100 shadow-[0_2px_20px_rgba(0,0,0,0.05)] flex flex-col">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-6 bg-purple-500 rounded-full"></div>
                        <h3 class="font-bold text-gray-900 text-[15px]">File Tracking</h3>
                    </div>
                    <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-100">Live</span>
                </div>

                <!-- Multi-ring donut -->
                <div class="relative flex-1 flex items-center justify-center">
                    <div class="w-48 h-48 donut-spin">
                        <Doughnut :data="trackingDonutMulti" :options="trackingDonutOptions" />
                    </div>
                    <div class="absolute flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-2xl font-black text-gray-900">{{ trackingRingData.total }}</span>
                        <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Total</span>
                    </div>
                </div>

                <!-- Legend -->
                <div class="space-y-2 mt-4">
                    <div v-for="row in [
                        { label: 'ZTTBL (At Agency)', color: 'bg-blue-500',    val: trackingRingData.zttbl     },
                        { label: 'Vendor Completed',  color: 'bg-purple-500',  val: trackingRingData.completed },
                        { label: 'Vendor Accepted',   color: 'bg-emerald-500', val: trackingRingData.accepted  },
                        { label: 'Vendor Pending',    color: 'bg-pink-500',    val: trackingRingData.pending   },
                        { label: 'Vendor Rejected',   color: 'bg-red-400',     val: trackingRingData.rejected  },
                    ]" :key="row.label" class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" :class="row.color"></div>
                            <span class="text-xs text-gray-600 font-medium">{{ row.label }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-gray-900">{{ row.val }}</span>
                            <span class="text-[10px] text-gray-400">{{ trackingRingData.total > 0 ? Math.round(row.val / trackingRingData.total * 100) : 0 }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Middle Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-5">
            <!-- Project Analytics (Sales/Expenses) -->
            <div class="lg:col-span-6 bg-white rounded-[24px] p-6 border border-blue-200 shadow-[0_0_22px_rgba(34,197,94,0.15)] hover:shadow-[0_0_40px_rgba(34,197,94,0.32)] hover:border-blue-300 transition-all duration-300">
                 <h3 class="font-bold text-gray-900 mb-6 text-lg">Sales Analytics</h3>
                 <!-- Legend inside chart -->
                 <div class="flex justify-center gap-4 text-[11px] font-medium text-gray-500 mb-2">
                      <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#1d4ed8]"></span> Sales</div>
                      <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#b8decb]"></span> Expenses</div>
                 </div>
                 <div class="h-44 w-full">
                      <Bar :data="salesExpensesTrendData" :options="barChartOptions" />
                 </div>
            </div>

             <!-- Projects List (Receivable Today/Client Due) -->
             <div class="lg:col-span-3 bg-white rounded-[24px] p-6 border border-blue-200 shadow-[0_0_22px_rgba(34,197,94,0.15)] hover:shadow-[0_0_40px_rgba(34,197,94,0.32)] hover:border-blue-300 transition-all duration-300 overflow-y-auto" style="height: 320px;">
                 <div class="flex justify-between items-center mb-6">
                     <h3 class="font-bold text-gray-900 text-lg">Client Dues</h3>
                     <button class="border border-gray-200 text-gray-800 text-[11px] font-bold px-3 py-1.5 rounded-full hover:bg-gray-50 flex items-center gap-1 transition"><font-awesome-icon icon="plus" class="w-2.5 h-2.5 text-gray-500" /> New</button>
                 </div>
                 <div class="space-y-5">
                      <div v-for="(item, idx) in (state.payableToday?.items || []).slice(0, 4)" :key="idx" class="flex items-start gap-3.5">
                           <!-- Dynamic color based on index -->
                           <div :class="[
                               'w-8 h-8 rounded-full flex items-center justify-center shrink-0 mt-0.5',
                               idx % 4 === 0 ? 'bg-blue-50 text-blue-500' : 
                               idx % 4 === 1 ? 'bg-teal-50 text-teal-500' : 
                               idx % 4 === 2 ? 'bg-orange-50 text-orange-500' : 
                               'bg-yellow-50 text-yellow-500'
                           ]">
                               <font-awesome-icon icon="file-invoice-dollar" class="w-3.5 h-3.5" />
                           </div>
                           <div>
                               <div class="text-sm font-bold text-gray-900 leading-tight mb-0.5">{{ item.name }}</div>
                               <div class="text-[11px] font-medium text-gray-400">Due amount: {{ item.amount }}</div>
                           </div>
                      </div>
                      <div v-if="!state.payableToday?.items || state.payableToday?.items?.length === 0" class="text-sm text-gray-500">No dues found.</div>
                 </div>
             </div>

            <!-- Total Storage -->
            <div class="lg:col-span-3 rounded-[24px] p-6 text-white h-[320px] flex flex-col relative overflow-hidden group transition-all duration-300"
                 style="background: linear-gradient(135deg, #5B7CF7 0%, #4361EE 50%, #3730D4 100%);
                        box-shadow: 0 4px 15px rgba(67,97,238,0.35), 0 12px 30px rgba(67,97,238,0.2), 0 30px 50px rgba(67,97,238,0.1);">
                <div class="absolute -right-6 -top-6 w-36 h-36 rounded-full opacity-20 pointer-events-none"
                     style="background: radial-gradient(circle, #fff, transparent 70%);"></div>
                <div class="absolute -left-8 -bottom-8 w-40 h-40 rounded-full opacity-10 pointer-events-none"
                     style="background: radial-gradient(circle, #a5b4fc, transparent 70%);"></div>
                <svg class="absolute inset-0 w-full h-full opacity-[0.08]" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <path d="M0,50 Q25,20 50,50 T100,50" fill="none" stroke="#fff" stroke-width="2" />
                    <path d="M0,70 Q25,40 50,70 T100,70" fill="none" stroke="#fff" stroke-width="1.5" />
                    <path d="M0,90 Q25,60 50,90 T100,90" fill="none" stroke="#fff" stroke-width="3" />
                </svg>
                <div class="relative z-10 flex flex-col h-full">
                    <p class="text-white/70 text-[12px] font-semibold uppercase tracking-wider">Total Storage</p>
                    <div class="flex-1 flex flex-col items-center justify-center -mt-4">
                        <div class="text-[52px] font-black leading-none tracking-tight drop-shadow-md mb-1" style="text-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                            {{ formatBytes(state.appUsage.total).replace(' GB', '') }}
                        </div>
                        <p class="text-white/50 text-[11px] font-semibold uppercase tracking-widest">GB Used</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-5 pb-8">
            <!-- Team Collaboration (Agent Summary) -->
             <div class="lg:col-span-6 bg-white rounded-[24px] p-6 border border-blue-200 shadow-[0_0_22px_rgba(34,197,94,0.15)] hover:shadow-[0_0_40px_rgba(34,197,94,0.32)] hover:border-blue-300 transition-all duration-300 h-[320px] overflow-y-auto">
                 <div class="flex justify-between items-center mb-6">
                     <h3 class="font-bold text-gray-900 text-lg">Agent Collaboration</h3>
                     <button class="border border-gray-200 text-gray-800 text-[11px] font-bold px-4 py-1.5 rounded-full hover:bg-gray-50 flex items-center gap-1 transition"><font-awesome-icon icon="plus" class="w-2.5 h-2.5 text-gray-500" /> Add Member</button>
                 </div>
                 <div class="space-y-5">
                      <div v-for="(agent, idx) in (state.agentClientSummary || []).slice(0, 4)" :key="agent.name" class="flex items-center justify-between" @click="agent.id ? goToAgent(agent.id) : null" :class="{'cursor-pointer': agent.id}">
                          <div class="flex items-center gap-3">
                              <!-- Dynamic colored placeholders matching the image style -->
                              <div :class="[
                                  'w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0 border border-white shadow-sm',
                                  idx % 4 === 0 ? 'bg-[#ffdfbf] text-orange-800' :
                                  idx % 4 === 1 ? 'bg-[#c0aede] text-purple-800' :
                                  idx % 4 === 2 ? 'bg-[#b6e3f4] text-blue-800' :
                                  'bg-[#ffdfbf] text-orange-800'
                              ]">
                                  {{ agent.name.charAt(0) }}
                              </div>
                              <div>
                                  <div class="text-sm font-bold text-gray-900 leading-tight mb-0.5">{{ agent.name }}</div>
                                  <div class="text-[11px] font-medium text-gray-400">Managing <span class="font-bold text-gray-700">{{ agent.clients_count }} Clients</span></div>
                              </div>
                          </div>
                          <!-- Conditional Status Badge matching Image -->
                          <span :class="[
                              'text-[10px] font-bold px-2.5 py-1 rounded-md border',
                              idx % 3 === 0 ? 'bg-blue-50 text-blue-600 border-blue-100' :
                              idx % 3 === 1 ? 'bg-yellow-50 text-yellow-600 border-yellow-100' :
                              'bg-red-50 text-red-500 border-red-100'
                          ]">
                              {{ agent.status || (idx % 3 === 0 ? 'Completed' : idx % 3 === 1 ? 'In Progress' : 'Pending') }}
                          </span>
                      </div>
                 </div>
             </div>

             <!-- Project Progress (Doughnut) -->
              <div class="lg:col-span-6 bg-white rounded-[24px] p-6 border border-blue-200 shadow-[0_0_22px_rgba(34,197,94,0.15)] hover:shadow-[0_0_40px_rgba(34,197,94,0.32)] hover:border-blue-300 transition-all duration-300 h-[320px] flex flex-col relative overflow-hidden">
                  <h3 class="font-bold text-gray-900 text-lg mb-2 z-10">Receivable vs Payable</h3>
                  <div class="flex-1 mt-6 relative z-10 flex justify-center w-full">
                      <div class="w-56 h-auto">
                        <Doughnut :data="receivablePayableData" :options="doughnutChartOptions" />
                      </div>
                      <div class="absolute inset-0 flex flex-col items-center justify-end pb-12 pointer-events-none">
                          <span class="text-4xl font-extrabold text-[#2a2a2a] tracking-tight">{{ state.receivableToday.total > 0 ?  Math.round((state.receivableToday.total / (state.receivableToday.total + state.payableToday.total)) * 100) : 41 }}%</span>
                          <span class="text-[11px] font-medium text-gray-500 mt-0.5">Receivable Share</span>
                      </div>
                  </div>
                  <div class="mt-auto flex justify-center gap-5 text-[11px] font-bold text-gray-500 pt-2 z-10">
                      <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#1d4ed8]"></span> Receivable</div>
                      <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#e0ede6]"></span> Payable</div>
                  </div>
              </div>

        </div>

        <!-- Airline Tickets Widget -->
        <div class="mb-6">
            <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                            <font-awesome-icon icon="plane-departure" class="w-4 h-4 text-[#1d4ed8]" />
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">Upcoming Flights</h3>
                            <p class="text-xs text-gray-400">Next 7 days</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-center">
                            <div class="text-lg font-black text-gray-900">{{ state.ticketStats.total }}</div>
                            <div class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Total</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-black text-blue-600">{{ state.ticketStats.confirmed }}</div>
                            <div class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Confirmed</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-black text-amber-500">{{ state.ticketStats.rescheduled }}</div>
                            <div class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Rescheduled</div>
                        </div>
                        <Link href="/airline-tickets" class="text-xs text-blue-700 font-semibold hover:underline">
                            View all →
                        </Link>
                    </div>
                </div>

                <!-- Upcoming list -->
                <div v-if="state.ticketStats.upcoming.length === 0" class="px-6 py-8 text-center text-gray-400 text-sm">
                    No flights in the next 7 days.
                </div>
                <div v-else class="divide-y divide-gray-50">
                    <div
                        v-for="ticket in state.ticketStats.upcoming"
                        :key="ticket.id"
                        class="flex items-center justify-between px-6 py-3.5 hover:bg-gray-50/50 transition"
                    >
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center flex-shrink-0">
                                <font-awesome-icon icon="plane" class="w-4 h-4 text-gray-500" />
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900 text-sm">{{ ticket.passenger_name }}</div>
                                <div class="text-xs text-gray-500">{{ ticket.flight_number }} · {{ ticket.airline_name }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-6 text-sm">
                            <div class="flex items-center gap-1.5 text-gray-700 font-medium">
                                <span>{{ ticket.origin }}</span>
                                <font-awesome-icon icon="arrow-right" class="w-3 h-3 text-gray-400" />
                                <span>{{ ticket.destination }}</span>
                            </div>
                            <div class="text-gray-900 font-bold text-sm w-24 text-right">
                                {{ new Date(ticket.flight_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short' }) }}
                            </div>
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold capitalize"
                                :class="ticket.status === 'confirmed' ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-200' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'"
                            >{{ ticket.status }}</span>
                            <Link :href="`/airline-tickets/${ticket.id}`" class="text-xs text-blue-700 font-semibold hover:underline">View</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
.elite-card {
    transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.3s ease;
}
.elite-card:hover {
    transform: translateY(-6px) scale(1.02);
}

input::placeholder {
  color: #9ca3af;
  font-weight: 500;
}

/* Donut spin — slow rotation */
.donut-spin {
    animation: donut-rotate 35s linear infinite;
    transform-origin: center center;
}
.donut-spin:hover {
    animation-play-state: paused;
}

@keyframes donut-rotate {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
</style>
