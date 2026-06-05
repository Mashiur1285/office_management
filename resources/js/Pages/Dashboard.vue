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
        y: {
            display: true,
            beginAtZero: true,
            grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
            border: { display: false },
            ticks: {
                font: { size: 10, weight: '600' },
                color: '#9ca3af',
                callback: v => '৳' + new Intl.NumberFormat('en-BD', { notation: 'compact' }).format(v),
            }
        },
        x: {
            grid: { display: false },
            border: { display: false },
            ticks: { font: { size: 11, weight: '700' }, color: '#6b7280' }
        }
    },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: "rgba(15,23,42,0.92)",
            padding: { x: 14, y: 10 },
            cornerRadius: 10,
            titleFont: { size: 11, weight: '700' },
            bodyFont: { size: 12, weight: '600' },
            titleColor: 'rgba(255,255,255,0.6)',
            bodyColor: '#ffffff',
            callbacks: {
                label: ctx => `  ${ctx.dataset.label}: ৳` + new Intl.NumberFormat('en-BD').format(ctx.parsed.y),
            },
        },
    },
};

// ── Month calendar selector ──
const selectedMonthIdx = ref(null); // null = all 6 months

const calendarMonths = computed(() => {
    const now = new Date();
    return Array.from({ length: 6 }, (_, i) => {
        const d = new Date(now.getFullYear(), now.getMonth() - 5 + i, 1);
        return {
            short: d.toLocaleDateString('en-US', { month: 'short' }),
            full:  d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }),
            year:  d.getFullYear(),
        };
    });
});

const displayedSalesSummary = computed(() => {
    const idx = selectedMonthIdx.value;
    if (idx === null) return state.salesSummary;
    const total    = state.salesMonthly[idx]?.amount    || 0;
    const expenses = state.expensesMonthly[idx]?.amount || 0;
    const ratio    = state.salesSummary?.total > 0 ? (state.salesSummary.paid / state.salesSummary.total) : 0.82;
    return { total, paid: Math.round(total * ratio), due: Math.round(total * (1 - ratio)), expenses };
});

// ── Sales Line Chart (gradient fill, per-point highlight) ──
const salesLineData = computed(() => {
    const labels    = state.salesMonthly.length    ? state.salesMonthly.map(m => m.label?.substring(0,3))    : ['Jan','Feb','Mar','Apr','May','Jun'];
    const salesData = state.salesMonthly.length    ? state.salesMonthly.map(m => m.amount || 0)    : [0, 0, 0, 330000, 0, 0];
    const expData   = state.expensesMonthly.length ? state.expensesMonthly.map(m => m.amount || 0) : [0, 0, 0, 0, 0, 0];

    const sel = selectedMonthIdx.value;

    const salesRadius   = salesData.map((_, i) => sel === null ? 4 : (i === sel ? 8 : 3));
    const expRadius     = expData.map((_, i)   => sel === null ? 4 : (i === sel ? 8 : 3));
    const salesPtColor  = salesData.map((_, i) => sel === null || i === sel ? '#fb923c' : 'rgba(251,146,60,0.2)');
    const expPtColor    = expData.map((_, i)   => sel === null || i === sel ? '#c026d3' : 'rgba(192,38,211,0.2)');

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
                pointBackgroundColor: salesPtColor,
                pointBorderColor: salesPtColor.map(c => c === '#fb923c' ? 'rgba(255,255,255,0.6)' : 'transparent'),
                pointBorderWidth: 2,
                pointRadius: salesRadius,
                pointHoverRadius: 9,
                tension: 0.45,
                fill: true,
            },
            {
                label: 'Expenses',
                data: expData,
                borderColor: '#c026d3',
                backgroundColor: expGradient,
                borderWidth: 2.5,
                pointBackgroundColor: expPtColor,
                pointBorderColor: expPtColor.map(c => c === '#c026d3' ? 'rgba(255,255,255,0.6)' : 'transparent'),
                pointBorderWidth: 2,
                pointRadius: expRadius,
                pointHoverRadius: 9,
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
    const total     = mittPipelineTotal.value || 0;
    const zttbl     = state.bdCompanyFiles?.agency_total || 0;
    const pending   = state.bdCompanyFiles?.pending      || 0;
    const completed = state.bdCompanyFiles?.completed    || 0;
    const accepted  = state.bdCompanyFiles?.accepted     || 0;
    const rejected  = state.bdCompanyFiles?.rejected     || 0;
    return { total, zttbl, pending, completed, accepted, rejected };
});

const trackingDonutMulti = computed(() => ({
    labels: ['ZTTBL','Vendor Completed','Vendor Accepted','Vendor Pending','Vendor Rejected'],
    datasets: (() => {
        const t = trackingRingData.value;
        const isEmpty = t.total === 0;

        const ghostData = [1, 0];
        const realData  = (val) => [val, Math.max(0, t.total - val)];

        return [
            {
                label: 'ZTTBL',
                data: isEmpty ? ghostData : realData(t.zttbl),
                backgroundColor: isEmpty
                    ? ['rgba(59,130,246,0.12)',  'rgba(59,130,246,0.05)']
                    : ['#3b82f6', 'rgba(59,130,246,0.07)'],
                borderWidth: 0, cutout: '88%', borderRadius: isEmpty ? 0 : [6,0],
            },
            {
                label: 'Vendor Completed',
                data: isEmpty ? ghostData : realData(t.completed),
                backgroundColor: isEmpty
                    ? ['rgba(168,85,247,0.12)', 'rgba(168,85,247,0.05)']
                    : ['#a855f7', 'rgba(168,85,247,0.07)'],
                borderWidth: 0, cutout: '76%', borderRadius: isEmpty ? 0 : [6,0],
            },
            {
                label: 'Vendor Accepted',
                data: isEmpty ? ghostData : realData(t.accepted),
                backgroundColor: isEmpty
                    ? ['rgba(16,185,129,0.12)',  'rgba(16,185,129,0.05)']
                    : ['#10b981', 'rgba(16,185,129,0.07)'],
                borderWidth: 0, cutout: '64%', borderRadius: isEmpty ? 0 : [6,0],
            },
            {
                label: 'Vendor Pending',
                data: isEmpty ? ghostData : realData(t.pending),
                backgroundColor: isEmpty
                    ? ['rgba(236,72,153,0.12)',  'rgba(236,72,153,0.05)']
                    : ['#ec4899', 'rgba(236,72,153,0.07)'],
                borderWidth: 0, cutout: '52%', borderRadius: isEmpty ? 0 : [6,0],
            },
            {
                label: 'Vendor Rejected',
                data: isEmpty ? ghostData : realData(t.rejected),
                backgroundColor: isEmpty
                    ? ['rgba(239,68,68,0.12)',   'rgba(239,68,68,0.05)']
                    : ['#ef4444', 'rgba(239,68,68,0.07)'],
                borderWidth: 0, cutout: '40%', borderRadius: isEmpty ? 0 : [6,0],
            },
        ];
    })(),
}));

const trackingDonutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    animation: { animateRotate: true, animateScale: true, duration: 1200 },
    plugins: {
        legend: { display: false },
        tooltip: {
            enabled: false,
            external: ({ chart, tooltip }) => {
                const TOOLTIP_ID = 'tracking-chart-tooltip';
                let el = document.getElementById(TOOLTIP_ID);

                if (!el) {
                    el = document.createElement('div');
                    el.id = TOOLTIP_ID;
                    Object.assign(el.style, {
                        position: 'fixed',
                        pointerEvents: 'none',
                        zIndex: '9999',
                        background: 'rgba(15,23,42,0.95)',
                        color: 'white',
                        borderRadius: '10px',
                        padding: '8px 14px',
                        fontSize: '12px',
                        fontWeight: '600',
                        border: '1px solid rgba(255,255,255,0.1)',
                        whiteSpace: 'nowrap',
                        boxShadow: '0 4px 20px rgba(0,0,0,0.35)',
                        transition: 'opacity 0.15s',
                    });
                    document.body.appendChild(el);
                }

                // Hide when no data or hovering on background slice
                const dp = tooltip.dataPoints?.[0];
                if (tooltip.opacity === 0 || !dp || dp.dataIndex !== 0) {
                    el.style.opacity = '0';
                    return;
                }

                const dsIdx     = dp.datasetIndex ?? 0;
                const allLabels = ['ZTTBL (At Agency)', 'Vendor Completed', 'Vendor Accepted', 'Vendor Pending', 'Vendor Rejected'];
                const colors    = ['#3b82f6', '#a855f7', '#10b981', '#ec4899', '#ef4444'];
                const label     = allLabels[dsIdx] ?? '';
                const color     = colors[dsIdx]    ?? '#fff';
                const val       = dp.parsed ?? 0;
                const total     = trackingRingData.value.total || 1;
                const pct       = Math.round(val / total * 100);

                el.innerHTML = `
                    <div style="color:rgba(255,255,255,0.4);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">${label}</div>
                    <div style="display:flex;align-items:center;gap:6px">
                        <span style="width:8px;height:8px;border-radius:50%;background:${color};flex-shrink:0;display:inline-block"></span>
                        <span style="font-size:13px;font-weight:800">${val} files</span>
                        <span style="color:rgba(255,255,255,0.4);font-size:11px">(${pct}%)</span>
                    </div>`;

                // Map canvas-space caret → screen-space (accounting for CSS rotation)
                const spinEl = chart.canvas.parentElement;
                const rect   = chart.canvas.getBoundingClientRect();
                const matrix = new DOMMatrix(window.getComputedStyle(spinEl).transform);
                const angle  = Math.atan2(matrix.b, matrix.a);

                const dx = tooltip.caretX - chart.width  / 2;
                const dy = tooltip.caretY - chart.height / 2;

                const screenX = rect.left + rect.width  / 2 + dx * Math.cos(angle) - dy * Math.sin(angle);
                const screenY = rect.top  + rect.height / 2 + dx * Math.sin(angle) + dy * Math.cos(angle);

                el.style.left      = screenX + 'px';
                el.style.top       = (screenY - 58) + 'px';
                el.style.transform = 'translateX(-50%)';
                el.style.opacity   = '1';
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
                backgroundColor: "rgba(79,70,229,0.85)",
                hoverBackgroundColor: "#4f46e5",
                borderRadius: { topLeft: 8, topRight: 8, bottomLeft: 0, bottomRight: 0 },
                borderSkipped: false,
                barPercentage: 0.55,
                categoryPercentage: 0.75,
            },
            {
                label: "Expenses",
                data: expensesData.length ? expensesData : [800, 1500, 1200, 2000, 1800, 2500, 2200],
                backgroundColor: "rgba(6,182,212,0.65)",
                hoverBackgroundColor: "#06b6d4",
                borderRadius: { topLeft: 8, topRight: 8, bottomLeft: 0, bottomRight: 0 },
                borderSkipped: false,
                barPercentage: 0.55,
                categoryPercentage: 0.75,
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
    const receivable = state.receivableToday?.total || 0;
    const payable = state.payableToday?.total || 0;

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

const receivablePayableSummary = computed(() => {
    const receivable = state.receivableToday?.total || 0;
    const payable = state.payableToday?.total || 0;
    const total = receivable + payable;
    if (total === 0) return { pct: 0, label: 'No Activity', color: 'text-gray-400' };
    if (receivable >= payable) {
        return { pct: Math.round((receivable / total) * 100), label: 'Receivable Share', color: 'text-blue-600' };
    }
    return { pct: Math.round((payable / total) * 100), label: 'Payable Share', color: 'text-orange-500' };
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

    <div class="px-4 py-7 md:px-6 lg:px-8 min-h-screen font-sans relative" style="background: #a8d8ef;">

        <!-- Background blobs -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden" style="z-index: 0;">
            <div class="absolute top-0 left-0 w-[700px] h-[700px] rounded-full blur-[120px] opacity-50" style="background: radial-gradient(circle, #29b6f6, transparent 65%); transform: translate(-25%, -25%);"></div>
            <div class="absolute top-0 right-0 w-[600px] h-[600px] rounded-full blur-[100px] opacity-40" style="background: radial-gradient(circle, #26d0a0, transparent 65%); transform: translate(20%, -20%);"></div>
            <div class="absolute top-1/3 left-1/2 w-[500px] h-[500px] rounded-full blur-[100px] opacity-30" style="background: radial-gradient(circle, #4db6e4, transparent 65%); transform: translate(-50%, -30%);"></div>
            <div class="absolute bottom-0 left-1/4 w-[500px] h-[500px] rounded-full blur-[100px] opacity-35" style="background: radial-gradient(circle, #34d399, transparent 65%); transform: translate(-30%, 30%);"></div>
        </div>

        <!-- Content wrapper -->
        <div class="relative" style="z-index: 1;">

        <!-- Error Banner -->
        <div v-if="state.errorLog" class="mb-4 bg-red-50/80 backdrop-blur-sm border border-red-200/60 text-red-600 px-4 py-3 rounded-xl text-sm">
            <strong>Error:</strong> {{ state.errorLog }}
        </div>

        <!-- Header Banner -->
        <div class="relative rounded-2xl overflow-hidden mb-7 px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
             style="background: linear-gradient(135deg, #1565c0 0%, #00897b 100%); box-shadow: 0 8px 32px rgba(21,101,192,0.25);">
            <div class="absolute -right-16 -top-16 w-52 h-52 rounded-full pointer-events-none opacity-20" style="background: radial-gradient(circle, #a5f3fc, transparent 70%);"></div>
            <div class="absolute -left-10 -bottom-10 w-36 h-36 rounded-full pointer-events-none opacity-15" style="background: radial-gradient(circle, #fff, transparent 70%);"></div>
            <div class="absolute right-1/3 top-0 w-64 h-full pointer-events-none opacity-10" style="background: radial-gradient(ellipse, #bbf7d0, transparent 60%);"></div>
            <div class="relative z-10">
                <h1 class="text-2xl font-bold text-white tracking-tight leading-none">Dashboard</h1>
                <p class="text-sm text-white/60 mt-1">Track your business performance at a glance.</p>
            </div>
            <div class="relative z-10 flex items-center gap-2 text-[12px] text-white/80 bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2.5 border border-white/30 self-start sm:self-auto">
                <font-awesome-icon icon="calendar-days" class="w-3.5 h-3.5 text-white/70" />
                <span class="font-semibold">{{ new Date().toLocaleDateString('en-GB', { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' }) }}</span>
            </div>
        </div>

        <!-- ── Stat Cards ── -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

            <div class="elite-card group cursor-pointer rounded-2xl p-5 text-white relative overflow-hidden"
                 style="background: linear-gradient(135deg, #5B7CF7 0%, #4361EE 50%, #3730D4 100%);
                        box-shadow: 0 4px 20px rgba(67,97,238,0.30), 0 12px 35px rgba(67,97,238,0.15);"
                 @click="navigateTo('Clients')">
                <div class="absolute -right-5 -top-5 w-28 h-28 rounded-full opacity-[0.15] pointer-events-none" style="background: radial-gradient(circle, #fff, transparent 70%);"></div>
                <div class="absolute -left-3 -bottom-3 w-20 h-20 rounded-full opacity-[0.08] pointer-events-none" style="background: radial-gradient(circle, #a5b4fc, transparent 70%);"></div>
                <div class="relative z-10 flex justify-between items-start mb-4">
                    <p class="text-white/60 text-[11px] font-bold uppercase tracking-widest">Total Clients</p>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-white/15 ring-1 ring-white/20 group-hover:bg-white/25 transition-all">
                        <font-awesome-icon icon="users" class="w-4 h-4 text-white" />
                    </div>
                </div>
                <div class="relative z-10 text-[48px] font-black leading-none tracking-tight" style="text-shadow: 0 2px 10px rgba(0,0,0,0.15);">{{ state.stats.total_clients ?? 0 }}</div>
                <p class="relative z-10 text-white/35 text-[10px] font-semibold mt-2 uppercase tracking-widest">registered</p>
            </div>

            <div class="elite-card group cursor-pointer rounded-2xl p-5 text-white relative overflow-hidden"
                 style="background: linear-gradient(135deg, #B06CF7 0%, #9333EA 50%, #7E22CE 100%);
                        box-shadow: 0 4px 20px rgba(147,51,234,0.30), 0 12px 35px rgba(147,51,234,0.15);"
                 @click="navigateTo('Agents')">
                <div class="absolute -right-5 -top-5 w-28 h-28 rounded-full opacity-[0.15] pointer-events-none" style="background: radial-gradient(circle, #fff, transparent 70%);"></div>
                <div class="absolute -left-3 -bottom-3 w-20 h-20 rounded-full opacity-[0.08] pointer-events-none" style="background: radial-gradient(circle, #e9d5ff, transparent 70%);"></div>
                <div class="relative z-10 flex justify-between items-start mb-4">
                    <p class="text-white/60 text-[11px] font-bold uppercase tracking-widest">Agents</p>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-white/15 ring-1 ring-white/20 group-hover:bg-white/25 transition-all">
                        <font-awesome-icon icon="address-card" class="w-4 h-4 text-white" />
                    </div>
                </div>
                <div class="relative z-10 text-[48px] font-black leading-none tracking-tight" style="text-shadow: 0 2px 10px rgba(0,0,0,0.15);">{{ state.stats.total_agents ?? 0 }}</div>
                <p class="relative z-10 text-white/35 text-[10px] font-semibold mt-2 uppercase tracking-widest">active agents</p>
            </div>

            <div class="elite-card group cursor-pointer rounded-2xl p-5 text-white relative overflow-hidden"
                 style="background: linear-gradient(135deg, #06B6D4 0%, #0891B2 50%, #0E7490 100%);
                        box-shadow: 0 4px 20px rgba(8,145,178,0.30), 0 12px 35px rgba(8,145,178,0.15);"
                 @click="navigateTo('Vendors')">
                <div class="absolute -right-5 -top-5 w-28 h-28 rounded-full opacity-[0.15] pointer-events-none" style="background: radial-gradient(circle, #fff, transparent 70%);"></div>
                <div class="absolute -left-3 -bottom-3 w-20 h-20 rounded-full opacity-[0.08] pointer-events-none" style="background: radial-gradient(circle, #a5f3fc, transparent 70%);"></div>
                <div class="relative z-10 flex justify-between items-start mb-4">
                    <p class="text-white/60 text-[11px] font-bold uppercase tracking-widest">Vendors</p>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-white/15 ring-1 ring-white/20 group-hover:bg-white/25 transition-all">
                        <font-awesome-icon icon="building" class="w-4 h-4 text-white" />
                    </div>
                </div>
                <div class="relative z-10 text-[48px] font-black leading-none tracking-tight" style="text-shadow: 0 2px 10px rgba(0,0,0,0.15);">{{ state.stats.total_bd_companies ?? 0 }}</div>
                <p class="relative z-10 text-white/35 text-[10px] font-semibold mt-2 uppercase tracking-widest">BD vendors</p>
            </div>

            <div class="elite-card group cursor-pointer rounded-2xl p-5 text-white relative overflow-hidden"
                 style="background: linear-gradient(135deg, #F97316 0%, #EA580C 50%, #C2410C 100%);
                        box-shadow: 0 4px 20px rgba(234,88,12,0.30), 0 12px 35px rgba(234,88,12,0.15);"
                 @click="navigateTo('Foreign Companies')">
                <div class="absolute -right-5 -top-5 w-28 h-28 rounded-full opacity-[0.15] pointer-events-none" style="background: radial-gradient(circle, #fff, transparent 70%);"></div>
                <div class="absolute -left-3 -bottom-3 w-20 h-20 rounded-full opacity-[0.08] pointer-events-none" style="background: radial-gradient(circle, #fed7aa, transparent 70%);"></div>
                <div class="relative z-10 flex justify-between items-start mb-4">
                    <p class="text-white/60 text-[11px] font-bold uppercase tracking-widest">Foreign Companies</p>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-white/15 ring-1 ring-white/20 group-hover:bg-white/25 transition-all">
                        <font-awesome-icon icon="globe" class="w-4 h-4 text-white" />
                    </div>
                </div>
                <div class="relative z-10 text-[48px] font-black leading-none tracking-tight" style="text-shadow: 0 2px 10px rgba(0,0,0,0.15);">{{ state.stats.total_foreign_companies ?? 0 }}</div>
                <p class="relative z-10 text-white/35 text-[10px] font-semibold mt-2 uppercase tracking-widest">partners</p>
            </div>
        </div>

        <!-- ── Row 1: Sales Trend + File Tracking ── -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 mb-4">

            <!-- Sales vs Expenses Trend -->
            <div class="lg:col-span-8 bg-white/50 backdrop-blur-xl rounded-2xl overflow-hidden border border-white/50 shadow-sm">

                <!-- Header -->
                <div class="flex items-start justify-between px-6 pt-5 pb-4 border-b border-white/40">
                    <div>
                        <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Overview</p>
                        <h3 class="text-gray-900 font-black text-[16px] tracking-tight leading-none">Sales vs Expenses</h3>
                        <p class="text-gray-400 text-[11px] mt-1">
                            {{ selectedMonthIdx === null ? 'Last 6 months' : calendarMonths[selectedMonthIdx]?.full }}
                        </p>
                    </div>
                    <button v-if="selectedMonthIdx !== null"
                            @click="selectedMonthIdx = null"
                            class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-full border border-indigo-100 hover:bg-indigo-100 transition-all">
                        ← All months
                    </button>
                    <span v-else class="text-[10px] text-orange-600 font-bold bg-orange-50 px-3 py-1.5 rounded-full border border-orange-100">
                        Last 6 months
                    </span>
                </div>

                <!-- Stats row -->
                <div class="grid grid-cols-4 divide-x divide-gray-100 border-b border-white/40">
                    <div class="px-5 py-3.5">
                        <div class="flex items-center gap-1.5 mb-1.5">
                            <div class="w-5 h-5 rounded-md bg-indigo-50 flex items-center justify-center">
                                <font-awesome-icon icon="chart-line" class="w-2.5 h-2.5 text-indigo-500" />
                            </div>
                            <p class="text-gray-400 text-[9px] font-bold uppercase tracking-wider">Total Sales</p>
                        </div>
                        <p class="text-gray-900 font-black text-[18px] leading-none">৳{{ new Intl.NumberFormat('en-BD',{notation:'compact'}).format(displayedSalesSummary?.total || 0) }}</p>
                    </div>
                    <div class="px-5 py-3.5">
                        <div class="flex items-center gap-1.5 mb-1.5">
                            <div class="w-5 h-5 rounded-md bg-emerald-50 flex items-center justify-center">
                                <font-awesome-icon icon="check" class="w-2.5 h-2.5 text-emerald-500" />
                            </div>
                            <p class="text-gray-400 text-[9px] font-bold uppercase tracking-wider">Paid</p>
                        </div>
                        <p class="text-emerald-600 font-black text-[18px] leading-none">৳{{ new Intl.NumberFormat('en-BD',{notation:'compact'}).format(displayedSalesSummary?.paid || 0) }}</p>
                    </div>
                    <div class="px-5 py-3.5">
                        <div class="flex items-center gap-1.5 mb-1.5">
                            <div class="w-5 h-5 rounded-md bg-amber-50 flex items-center justify-center">
                                <font-awesome-icon icon="clock" class="w-2.5 h-2.5 text-amber-500" />
                            </div>
                            <p class="text-gray-400 text-[9px] font-bold uppercase tracking-wider">Due</p>
                        </div>
                        <p class="text-amber-600 font-black text-[18px] leading-none">৳{{ new Intl.NumberFormat('en-BD',{notation:'compact'}).format(displayedSalesSummary?.due || 0) }}</p>
                    </div>
                    <div class="px-5 py-3.5">
                        <div class="flex items-center gap-1.5 mb-1.5">
                            <div class="w-5 h-5 rounded-md bg-red-50 flex items-center justify-center">
                                <font-awesome-icon icon="arrow-trend-down" class="w-2.5 h-2.5 text-red-500" />
                            </div>
                            <p class="text-gray-400 text-[9px] font-bold uppercase tracking-wider">Expense</p>
                        </div>
                        <p class="text-red-500 font-black text-[18px] leading-none">৳{{ new Intl.NumberFormat('en-BD',{notation:'compact'}).format(displayedSalesSummary?.expenses || 0) }}</p>
                    </div>
                </div>

                <!-- Chart + Calendar -->
                <div class="flex gap-5 px-5 pt-4 pb-5">

                    <!-- Chart -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-5 mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-[3px] rounded-full bg-orange-400"></div>
                                <span class="text-[11px] text-gray-500 font-semibold">Sales</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-[3px] rounded-full bg-purple-500"></div>
                                <span class="text-[11px] text-gray-500 font-semibold">Expenses</span>
                            </div>
                        </div>
                        <div class="h-[188px]">
                            <Line :data="salesLineData" :options="salesLineOptions" />
                        </div>
                    </div>

                    <!-- Month calendar widget -->
                    <div class="w-[260px] shrink-0 rounded-2xl border border-pink-100/50 bg-white/50 backdrop-blur-xl flex flex-col overflow-hidden"
                         style="box-shadow: 0 2px 20px rgba(236,72,153,0.10);">

                        <!-- Calendar header -->
                        <div class="px-5 pt-5 pb-4 border-b border-pink-50">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[10px] font-bold text-pink-400 uppercase tracking-widest">Period</span>
                                <span class="text-[11px] font-black text-pink-300 bg-pink-50 px-2.5 py-0.5 rounded-lg">
                                    {{ calendarMonths[5]?.year }}
                                </span>
                            </div>
                            <p class="text-[15px] font-black text-gray-800 leading-tight">
                                {{ selectedMonthIdx === null ? 'Last 6 months' : calendarMonths[selectedMonthIdx]?.full }}
                            </p>
                            <!-- Progress bar -->
                            <div class="flex gap-1.5 mt-3">
                                <div v-for="(m, i) in calendarMonths" :key="i"
                                     :class="['h-[4px] rounded-full transition-all duration-300 flex-1',
                                              selectedMonthIdx === i ? 'bg-pink-500' :
                                              selectedMonthIdx === null ? 'bg-pink-200' : 'bg-pink-100']">
                                </div>
                            </div>
                        </div>

                        <!-- Day labels (decorative) -->
                        <div class="grid grid-cols-7 px-4 pt-4 pb-1.5">
                            <span v-for="d in ['S','M','T','W','T','F','S']" :key="d+'lbl'"
                                  class="text-[10px] text-pink-200 font-bold text-center">{{ d }}</span>
                        </div>

                        <!-- Month grid 3×2 -->
                        <div class="grid grid-cols-3 gap-2 px-4 pb-4 pt-1.5 flex-1">
                            <button
                                v-for="(m, i) in calendarMonths"
                                :key="m.full"
                                @click="selectedMonthIdx = selectedMonthIdx === i ? null : i"
                                :class="[
                                    'flex flex-col items-center justify-center rounded-2xl py-4 transition-all duration-200 relative',
                                    selectedMonthIdx === i
                                        ? 'text-white scale-[1.05]'
                                        : i === 5
                                            ? 'bg-pink-50 text-pink-600 hover:bg-pink-100'
                                            : 'bg-pink-50/60 text-pink-400 hover:bg-pink-100 hover:text-pink-600'
                                ]"
                                :style="selectedMonthIdx === i
                                    ? 'background: linear-gradient(135deg, #db2777, #be185d); box-shadow: 0 6px 18px rgba(219,39,119,0.35);'
                                    : ''"
                            >
                                <span class="text-[15px] font-black leading-none">{{ m.short }}</span>
                                <span :class="['text-[9px] font-semibold mt-1 leading-none',
                                               selectedMonthIdx === i ? 'text-pink-200' : 'text-pink-300']">
                                    {{ m.year }}
                                </span>
                                <span v-if="i === 5 && selectedMonthIdx !== 5"
                                      class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-pink-400">
                                </span>
                            </button>
                        </div>

                        <!-- All months button -->
                        <div class="px-4 pb-4">
                            <button
                                @click="selectedMonthIdx = null"
                                :class="[
                                    'w-full py-2.5 rounded-xl text-[12px] font-bold transition-all duration-200',
                                    selectedMonthIdx === null
                                        ? 'bg-pink-500 text-white shadow-md shadow-pink-200'
                                        : 'bg-pink-50 text-pink-400 hover:bg-pink-100 hover:text-pink-600'
                                ]"
                            >All 6 months</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Tracking -->
            <div class="lg:col-span-4 bg-white/50 backdrop-blur-xl rounded-2xl border border-white/50 shadow-sm p-6 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-[3px] h-5 rounded-full bg-purple-500"></div>
                        <h3 class="font-bold text-gray-900 text-[15px]">File Tracking</h3>
                    </div>
                    <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-100">Live</span>
                </div>
                <div class="relative flex-1 flex items-center justify-center">
                    <div class="w-44 h-44 donut-spin">
                        <Doughnut :data="trackingDonutMulti" :options="trackingDonutOptions" />
                    </div>
                    <div class="absolute flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-2xl font-black text-gray-900">{{ trackingRingData.total }}</span>
                        <span class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Total</span>
                    </div>
                </div>
                <div class="space-y-2.5 mt-4 pt-4 border-t border-white/40">
                    <div v-for="row in [
                        { label: 'ZTTBL (At Agency)', color: 'bg-blue-500',    val: trackingRingData.zttbl     },
                        { label: 'Vendor Completed',  color: 'bg-purple-500',  val: trackingRingData.completed },
                        { label: 'Vendor Accepted',   color: 'bg-emerald-500', val: trackingRingData.accepted  },
                        { label: 'Vendor Pending',    color: 'bg-pink-500',    val: trackingRingData.pending   },
                        { label: 'Vendor Rejected',   color: 'bg-red-400',     val: trackingRingData.rejected  },
                    ]" :key="row.label" class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full shrink-0" :class="row.color"></div>
                            <span class="text-[11px] text-gray-500 font-medium">{{ row.label }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-bold text-gray-900">{{ row.val }}</span>
                            <span class="text-[10px] text-gray-400 w-7 text-right">{{ trackingRingData.total > 0 ? Math.round(row.val / trackingRingData.total * 100) : 0 }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Row 2: Sales Analytics + Client Dues + Total Storage ── -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 mb-4">

            <!-- Sales Analytics -->
            <div class="lg:col-span-6 bg-white/50 backdrop-blur-xl rounded-2xl border border-white/50 shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="px-6 pt-5 pb-4 border-b border-white/40">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Monthly</p>
                            <h3 class="font-black text-gray-800 text-[16px] tracking-tight leading-none">Sales Analytics</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-1.5 bg-indigo-50/80 text-indigo-600 text-[11px] font-bold px-3 py-1.5 rounded-full">
                                <span class="w-2 h-2 rounded-full bg-indigo-500 inline-block"></span> Sales
                            </div>
                            <div class="flex items-center gap-1.5 bg-cyan-50/80 text-cyan-600 text-[11px] font-bold px-3 py-1.5 rounded-full">
                                <span class="w-2 h-2 rounded-full bg-cyan-400 inline-block"></span> Expenses
                            </div>
                        </div>
                    </div>
                    <!-- Quick stats -->
                    <div class="flex items-center gap-6 mt-3">
                        <div>
                            <span class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Total Sales</span>
                            <p class="text-[15px] font-black text-indigo-600 leading-tight">৳{{ new Intl.NumberFormat('en-BD',{notation:'compact'}).format(state.salesSummary?.total || 0) }}</p>
                        </div>
                        <div class="w-px h-7 bg-white/60"></div>
                        <div>
                            <span class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Total Expenses</span>
                            <p class="text-[15px] font-black text-cyan-500 leading-tight">৳{{ new Intl.NumberFormat('en-BD',{notation:'compact'}).format(state.salesSummary?.expenses || 0) }}</p>
                        </div>
                        <div class="w-px h-7 bg-white/60"></div>
                        <div>
                            <span class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Net</span>
                            <p class="text-[15px] font-black text-emerald-500 leading-tight">৳{{ new Intl.NumberFormat('en-BD',{notation:'compact'}).format((state.salesSummary?.total || 0) - (state.salesSummary?.expenses || 0)) }}</p>
                        </div>
                    </div>
                </div>
                <!-- Chart -->
                <div class="px-5 py-4 h-[180px]">
                    <Bar :data="salesExpensesTrendData" :options="barChartOptions" />
                </div>
            </div>

            <!-- Client Dues -->
            <div class="lg:col-span-3 bg-white/50 backdrop-blur-xl rounded-2xl border border-white/50 shadow-sm p-5 overflow-y-auto" style="height: 320px;">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-[3px] h-5 rounded-full bg-amber-400"></div>
                        <h3 class="font-bold text-gray-900 text-[15px]">Client Dues</h3>
                    </div>
                </div>
                <div class="space-y-0.5">
                    <div v-for="(item, idx) in (state.payableToday?.items || []).slice(0, 6)" :key="idx"
                         class="flex items-center gap-3 px-2.5 py-2 rounded-xl hover:bg-white/50 transition cursor-pointer">
                        <div :class="[
                            'w-8 h-8 rounded-lg flex items-center justify-center shrink-0 text-xs font-black',
                            idx % 4 === 0 ? 'bg-indigo-50 text-indigo-600' :
                            idx % 4 === 1 ? 'bg-violet-50 text-violet-600' :
                            idx % 4 === 2 ? 'bg-orange-50 text-orange-600' :
                            'bg-teal-50 text-teal-600'
                        ]">{{ item.name?.charAt(0)?.toUpperCase() }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[13px] font-semibold text-gray-800 truncate">{{ item.name }}</div>
                            <div class="text-[10px] text-gray-400 font-medium">৳{{ new Intl.NumberFormat('en-BD').format(item.amount || 0) }}</div>
                        </div>
                        <span class="text-[10px] font-bold text-red-500 bg-red-50 px-2 py-0.5 rounded-md shrink-0">Due</span>
                    </div>
                    <div v-if="!state.payableToday?.items?.length" class="flex flex-col items-center justify-center py-10">
                        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center mb-2">
                            <font-awesome-icon icon="check" class="w-4 h-4 text-gray-300" />
                        </div>
                        <p class="text-sm text-gray-400 font-medium">No dues found</p>
                    </div>
                </div>
            </div>

            <!-- Total Storage -->
            <div class="lg:col-span-3 rounded-2xl p-6 text-white flex flex-col relative overflow-hidden"
                 style="background: linear-gradient(135deg, #5B7CF7 0%, #4361EE 50%, #3730D4 100%);
                        box-shadow: 0 4px 20px rgba(67,97,238,0.30), 0 12px 35px rgba(67,97,238,0.15);">
                <div class="absolute -right-5 -top-5 w-32 h-32 rounded-full opacity-[0.15] pointer-events-none" style="background: radial-gradient(circle, #fff, transparent 70%);"></div>
                <div class="absolute -left-6 -bottom-6 w-36 h-36 rounded-full opacity-[0.08] pointer-events-none" style="background: radial-gradient(circle, #a5b4fc, transparent 70%);"></div>
                <svg class="absolute inset-0 w-full h-full opacity-[0.05]" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <path d="M0,50 Q25,20 50,50 T100,50" fill="none" stroke="#fff" stroke-width="2" />
                    <path d="M0,75 Q25,45 50,75 T100,75" fill="none" stroke="#fff" stroke-width="1.5" />
                </svg>
                <div class="relative z-10 flex flex-col h-full">
                    <p class="text-white/60 text-[11px] font-bold uppercase tracking-widest">Total Storage</p>
                    <div class="flex-1 flex flex-col items-center justify-center">
                        <div class="text-[54px] font-black leading-none tracking-tight mb-1" style="text-shadow: 0 2px 12px rgba(0,0,0,0.2);">
                            {{ formatBytes(state.appUsage.total).replace(' GB', '') }}
                        </div>
                        <p class="text-white/45 text-[11px] font-bold uppercase tracking-widest">GB Used</p>
                    </div>
                    <div class="mt-4 pt-4 border-t border-white/10">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-white/40 font-medium">Path</span>
                            <span class="text-white/65 font-semibold truncate max-w-[110px]">{{ state.appUsage.path || '/storage' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Row 3: Agent Collaboration + Receivable vs Payable ── -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 mb-4">

            <!-- Agent Collaboration -->
            <div class="lg:col-span-6 bg-white/50 backdrop-blur-xl rounded-2xl border border-white/50 shadow-sm p-6 h-[320px] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-[3px] h-5 rounded-full bg-orange-400"></div>
                        <h3 class="font-bold text-gray-900 text-[15px]">Agent Collaboration</h3>
                    </div>
                    <button class="text-[11px] font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 px-2.5 py-1.5 rounded-full hover:bg-indigo-100 transition flex items-center gap-1">
                        <font-awesome-icon icon="plus" class="w-2.5 h-2.5" /> Add
                    </button>
                </div>
                <div class="space-y-0.5">
                    <div v-for="(agent, idx) in (state.agentClientSummary || []).slice(0, 5)" :key="agent.name"
                         class="flex items-center justify-between px-2.5 py-2 rounded-xl hover:bg-white/50 transition cursor-pointer"
                         @click="agent.id ? goToAgent(agent.id) : null">
                        <div class="flex items-center gap-3">
                            <div :class="[
                                'w-9 h-9 rounded-xl flex items-center justify-center font-black text-sm shrink-0',
                                idx % 4 === 0 ? 'bg-orange-100 text-orange-700' :
                                idx % 4 === 1 ? 'bg-violet-100 text-violet-700' :
                                idx % 4 === 2 ? 'bg-sky-100 text-sky-700' :
                                'bg-teal-100 text-teal-700'
                            ]">{{ agent.name.charAt(0) }}</div>
                            <div>
                                <div class="text-[13px] font-semibold text-gray-900 leading-tight">{{ agent.name }}</div>
                                <div class="text-[11px] text-gray-400">Managing <span class="font-bold text-gray-700">{{ agent.clients_count }}</span> clients</div>
                            </div>
                        </div>
                        <span :class="[
                            'text-[10px] font-bold px-2.5 py-1 rounded-lg',
                            idx % 3 === 0 ? 'bg-emerald-50 text-emerald-600' :
                            idx % 3 === 1 ? 'bg-amber-50 text-amber-600' :
                            'bg-red-50 text-red-500'
                        ]">{{ agent.status || (idx % 3 === 0 ? 'Completed' : idx % 3 === 1 ? 'In Progress' : 'Pending') }}</span>
                    </div>
                    <div v-if="!(state.agentClientSummary || []).length" class="flex items-center justify-center py-10">
                        <p class="text-sm text-gray-400">No agents found</p>
                    </div>
                </div>
            </div>

            <!-- Receivable vs Payable -->
            <div class="lg:col-span-6 bg-white/50 backdrop-blur-xl rounded-2xl border border-white/50 shadow-sm p-6 h-[320px] flex flex-col overflow-hidden">
                <!-- Header -->
                <div class="flex items-center justify-between mb-2 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-[3px] h-5 rounded-full bg-blue-500"></div>
                        <h3 class="font-bold text-gray-900 text-[15px]">Receivable vs Payable</h3>
                    </div>
                    <div class="flex items-center gap-4 text-[11px] font-semibold text-gray-400">
                        <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#1d4ed8]"></span> Receivable</div>
                        <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-gray-200"></span> Payable</div>
                    </div>
                </div>

                <!-- Chart -->
                <div class="relative flex justify-center h-[180px] shrink-0">
                    <div class="w-52 h-full">
                        <Doughnut :data="receivablePayableData" :options="doughnutChartOptions" />
                    </div>
                    <div class="absolute inset-0 flex flex-col items-center justify-end pb-4 pointer-events-none">
                        <span :class="['text-[36px] font-black tracking-tight leading-none', receivablePayableSummary.color]">
                            {{ receivablePayableSummary.pct }}%
                        </span>
                        <span class="text-[11px] text-gray-400 font-semibold mt-0.5">{{ receivablePayableSummary.label }}</span>
                    </div>
                </div>

                <!-- Amounts -->
                <div class="grid grid-cols-2 gap-3 pt-3 mt-auto border-t border-white/40 shrink-0">
                    <div class="text-center">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-0.5">Receivable</p>
                        <p class="text-lg font-black text-blue-600">৳{{ new Intl.NumberFormat('en-BD',{notation:'compact'}).format(state.receivableToday?.total || 0) }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-0.5">Payable</p>
                        <p class="text-lg font-black text-gray-800">৳{{ new Intl.NumberFormat('en-BD',{notation:'compact'}).format(state.payableToday?.total || 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Airline Tickets ── -->
        <div class="mb-6">
            <div class="bg-white/50 backdrop-blur-xl rounded-2xl border border-white/50 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/40">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                            <font-awesome-icon icon="plane-departure" class="w-4 h-4 text-indigo-500" />
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-[15px]">Upcoming Flights</h3>
                            <p class="text-[11px] text-gray-400">Next 7 days</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-5">
                        <div class="text-center">
                            <div class="text-lg font-black text-gray-900">{{ state.ticketStats.total }}</div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Total</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-black text-indigo-600">{{ state.ticketStats.confirmed }}</div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Confirmed</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-black text-amber-500">{{ state.ticketStats.rescheduled }}</div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Rescheduled</div>
                        </div>
                        <Link href="/airline-tickets" class="text-[11px] font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-full border border-indigo-100 hover:bg-indigo-100 transition">
                            View all →
                        </Link>
                    </div>
                </div>
                <div v-if="state.ticketStats.upcoming.length === 0" class="px-6 py-10 text-center">
                    <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                        <font-awesome-icon icon="plane" class="w-5 h-5 text-gray-300" />
                    </div>
                    <p class="text-sm text-gray-400 font-medium">No flights in the next 7 days</p>
                </div>
                <div v-else class="divide-y divide-white/40">
                    <div v-for="ticket in state.ticketStats.upcoming" :key="ticket.id"
                         class="flex items-center justify-between px-6 py-3.5 hover:bg-white/50 transition">
                        <div class="flex items-center gap-4">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
                                <font-awesome-icon icon="plane" class="w-3.5 h-3.5 text-indigo-400" />
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900 text-sm">{{ ticket.passenger_name }}</div>
                                <div class="text-[11px] text-gray-400">{{ ticket.flight_number }} · {{ ticket.airline_name }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="flex items-center gap-1.5 text-sm text-gray-700 font-semibold">
                                <span>{{ ticket.origin }}</span>
                                <font-awesome-icon icon="arrow-right" class="w-3 h-3 text-gray-300" />
                                <span>{{ ticket.destination }}</span>
                            </div>
                            <div class="text-gray-900 font-bold text-sm w-20 text-right">
                                {{ new Date(ticket.flight_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short' }) }}
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold capitalize"
                                  :class="ticket.status === 'confirmed' ? 'bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100' : 'bg-amber-50 text-amber-600 ring-1 ring-amber-100'">
                                {{ ticket.status }}
                            </span>
                            <Link :href="`/airline-tickets/${ticket.id}`" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-700">View →</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        </div><!-- end content wrapper -->
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
