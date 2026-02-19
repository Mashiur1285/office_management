<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import axios from "axios";
import { onMounted, reactive, computed, ref, watch } from "vue";
import { Pie, Doughnut, Line, Bar } from "vue-chartjs";
import {
    Chart as ChartJS,
    ArcElement,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from "chart.js";

ChartJS.register(
    ArcElement,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
);

// Route mapping for stat cards
const routeMap = {
    Clients: "/clients",
    Agents: "/agents",
    "BD Companies": "/bd-companies",
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
});

const totals = computed(() => [
    { label: "Clients", value: state.stats.total_clients },
    { label: "Agents", value: state.stats.total_agents },
    { label: "BD Companies", value: state.stats.total_bd_companies },
    { label: "Foreign Companies", value: state.stats.total_foreign_companies },
    { label: "Staff", value: state.stats.total_staff },
]);

const fileRangePreset = ref("this_month");
const fileCustomRange = reactive({ start: "", end: "" });
const agentRangePreset = ref("this_month");
const agentCustomRange = reactive({ start: "", end: "" });
const countryRangePreset = ref("this_month");
const countryCustomRange = reactive({ start: "", end: "" });
const refundRangePreset = ref("this_month");
const refundCustomRange = reactive({ start: "", end: "" });

const formatDate = (date) => date.toISOString().split("T")[0];

const getPresetRange = (preset) => {
    const now = new Date();
    const start = new Date(now);
    const end = new Date(now);

    if (preset === "this_week") {
        const day = (now.getDay() + 6) % 7;
        start.setDate(now.getDate() - day);
    } else if (preset === "last_week") {
        const day = (now.getDay() + 6) % 7;
        end.setDate(now.getDate() - day - 1);
        start.setDate(end.getDate() - 6);
    } else if (preset === "this_month") {
        start.setDate(1);
    } else if (preset === "last_month") {
        start.setMonth(now.getMonth() - 1, 1);
        end.setMonth(now.getMonth(), 0);
    } else if (preset === "this_year") {
        start.setMonth(0, 1);
        end.setMonth(11, 31);
    } else if (preset === "last_year") {
        start.setFullYear(now.getFullYear() - 1, 0, 1);
        end.setFullYear(now.getFullYear() - 1, 11, 31);
    }

    return { start: formatDate(start), end: formatDate(end) };
};

const fileDateRange = computed(() => {
    if (fileRangePreset.value === "custom") {
        if (fileCustomRange.start && fileCustomRange.end) {
            return { start: fileCustomRange.start, end: fileCustomRange.end };
        }
        return null;
    }
    return getPresetRange(fileRangePreset.value);
});

const agentDateRange = computed(() => {
    if (agentRangePreset.value === "custom") {
        if (agentCustomRange.start && agentCustomRange.end) {
            return { start: agentCustomRange.start, end: agentCustomRange.end };
        }
        return null;
    }
    return getPresetRange(agentRangePreset.value);
});

const countryDateRange = computed(() => {
    if (countryRangePreset.value === "custom") {
        if (countryCustomRange.start && countryCustomRange.end) {
            return {
                start: countryCustomRange.start,
                end: countryCustomRange.end,
            };
        }
        return null;
    }
    return getPresetRange(countryRangePreset.value);
});

const refundDateRange = computed(() => {
    if (refundRangePreset.value === "custom") {
        if (refundCustomRange.start && refundCustomRange.end) {
            return {
                start: refundCustomRange.start,
                end: refundCustomRange.end,
            };
        }
        return null;
    }
    return getPresetRange(refundRangePreset.value);
});

const fetchData = async () => {
    state.loading = true;
    try {
        const params = {};
        if (fileDateRange.value) {
            params.file_start_date = fileDateRange.value.start;
            params.file_end_date = fileDateRange.value.end;
        }
        if (agentDateRange.value) {
            params.agent_start_date = agentDateRange.value.start;
            params.agent_end_date = agentDateRange.value.end;
        }
        if (countryDateRange.value) {
            params.country_start_date = countryDateRange.value.start;
            params.country_end_date = countryDateRange.value.end;
        }
        if (refundDateRange.value) {
            params.refund_start_date = refundDateRange.value.start;
            params.refund_end_date = refundDateRange.value.end;
        }
        const { data } = await axios.get("/dashboard/data", { params });
        state.stats = data.stats;
        state.salesMonthly = data.salesMonthly;
        state.expensesMonthly = data.expensesMonthly;
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
    } finally {
        state.loading = false;
    }
};

onMounted(fetchData);

watch(
    [
        fileRangePreset,
        () => fileCustomRange.start,
        () => fileCustomRange.end,
        agentRangePreset,
        () => agentCustomRange.start,
        () => agentCustomRange.end,
        countryRangePreset,
        () => countryCustomRange.start,
        () => countryCustomRange.end,
        refundRangePreset,
        () => refundCustomRange.start,
        () => refundCustomRange.end,
    ],
    () => {
        if (fileRangePreset.value !== "custom") {
            fetchData();
        } else if (fileCustomRange.start && fileCustomRange.end) {
            fetchData();
        }

        if (agentRangePreset.value !== "custom") {
            fetchData();
        } else if (agentCustomRange.start && agentCustomRange.end) {
            fetchData();
        }

        if (countryRangePreset.value !== "custom") {
            fetchData();
        } else if (countryCustomRange.start && countryCustomRange.end) {
            fetchData();
        }

        if (refundRangePreset.value !== "custom") {
            fetchData();
        } else if (refundCustomRange.start && refundCustomRange.end) {
            fetchData();
        }
    },
);

// Entity Distribution Pie Chart with gradient colors
const entityDistributionData = computed(() => ({
    labels: ["Clients", "Agents", "BD Companies", "Foreign Companies", "Staff"],
    datasets: [
        {
            data: [
                state.stats.total_clients,
                state.stats.total_agents,
                state.stats.total_bd_companies,
                state.stats.total_foreign_companies,
                state.stats.total_staff,
            ],
            backgroundColor: [
                "rgba(59, 130, 246, 0.9)", // Blue for Clients
                "rgba(34, 197, 94, 0.9)", // Green for Agents
                "rgba(249, 115, 22, 0.9)", // Orange for BD Companies
                "rgba(168, 85, 247, 0.9)", // Purple for Foreign Companies
                "rgba(236, 72, 153, 0.9)", // Pink for Staff
            ],
            borderColor: "#ffffff",
            borderWidth: 3,
            hoverOffset: 15,
            hoverBorderWidth: 4,
            hoverBorderColor: "#ffffff",
        },
    ],
}));

// Receivable vs Payable Doughnut Chart with enhanced styling
const receivablePayableData = computed(() => ({
    labels: ["Total Receivable", "Total Payable"],
    datasets: [
        {
            data: [state.receivableToday.total, state.payableToday.total],
            backgroundColor: [
                "rgba(34, 197, 94, 0.9)", // Green for Receivable
                "rgba(239, 68, 68, 0.9)", // Red for Payable
            ],
            borderColor: "#ffffff",
            borderWidth: 4,
            hoverOffset: 20,
            hoverBorderWidth: 5,
            hoverBorderColor: "#ffffff",
        },
    ],
}));

// Sales vs Expenses Line Chart (raw monthly data)
const salesExpensesTrendData = computed(() => {
    const labels = state.salesMonthly.map((m) => m.label);
    const salesData = state.salesMonthly.map((m) => m.amount || 0);
    const expensesData = state.expensesMonthly.map((m) => m.amount || 0);

    return {
        labels,
        datasets: [
            {
                label: "Sales",
                data: salesData,
                borderColor: "rgba(59, 130, 246, 1)",
                backgroundColor: "rgba(59, 130, 246, 0.15)",
                borderWidth: 4,
                fill: true,
                tension: 0.45,
                pointRadius: 6,
                pointHoverRadius: 9,
                pointBackgroundColor: "rgba(59, 130, 246, 1)",
                pointBorderColor: "#ffffff",
                pointBorderWidth: 2,
                pointHoverBorderWidth: 3,
            },
            {
                label: "Expenses",
                data: expensesData,
                borderColor: "rgba(239, 68, 68, 1)",
                backgroundColor: "rgba(239, 68, 68, 0.15)",
                borderWidth: 4,
                fill: true,
                tension: 0.45,
                pointRadius: 6,
                pointHoverRadius: 9,
                pointBackgroundColor: "rgba(239, 68, 68, 1)",
                pointBorderColor: "#ffffff",
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
        easing: "easeInOutQuart",
    },
    plugins: {
        legend: {
            position: "bottom",
            labels: {
                padding: 15,
                font: {
                    size: 12,
                    weight: "bold",
                },
                usePointStyle: true,
                pointStyle: "circle",
            },
        },
        tooltip: {
            backgroundColor: "rgba(0, 0, 0, 0.8)",
            padding: 12,
            titleFont: { size: 14, weight: "bold" },
            bodyFont: { size: 13 },
            borderColor: "rgba(255, 255, 255, 0.3)",
            borderWidth: 1,
            callbacks: {
                label: function (context) {
                    const label = context.label || "";
                    const value = context.parsed || 0;
                    const total = context.dataset.data.reduce(
                        (a, b) => a + b,
                        0,
                    );
                    const percentage =
                        total > 0 ? ((value / total) * 100).toFixed(1) : 0;
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
        easing: "easeInOutQuart",
    },
    cutout: "65%",
    plugins: {
        legend: {
            position: "bottom",
            labels: {
                padding: 15,
                font: {
                    size: 12,
                    weight: "bold",
                },
                usePointStyle: true,
                pointStyle: "circle",
            },
        },
        tooltip: {
            backgroundColor: "rgba(0, 0, 0, 0.8)",
            padding: 12,
            titleFont: { size: 14, weight: "bold" },
            bodyFont: { size: 13 },
            borderColor: "rgba(255, 255, 255, 0.3)",
            borderWidth: 1,
            callbacks: {
                label: function (context) {
                    const label = context.label || "";
                    const value = context.parsed || 0;
                    const formatted =
                        "৳" +
                        new Intl.NumberFormat("en-BD", {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0,
                        }).format(value);
                    const total = context.dataset.data.reduce(
                        (a, b) => a + b,
                        0,
                    );
                    const percentage =
                        total > 0 ? ((value / total) * 100).toFixed(1) : 0;
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
        easing: "easeInOutQuart",
    },
    interaction: {
        mode: "index",
        intersect: false,
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: "rgba(0, 0, 0, 0.05)",
                drawBorder: false,
            },
            ticks: {
                font: { size: 11 },
                callback: function (value) {
                    return (
                        "৳" +
                        new Intl.NumberFormat("en-BD", {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0,
                        }).format(value)
                    );
                },
            },
        },
        x: {
            grid: {
                display: false,
                drawBorder: false,
            },
            ticks: {
                font: { size: 11, weight: "bold" },
            },
        },
    },
    plugins: {
        legend: {
            position: "top",
            labels: {
                font: {
                    size: 13,
                    weight: "bold",
                },
                padding: 20,
                usePointStyle: true,
                pointStyle: "circle",
            },
        },
        tooltip: {
            backgroundColor: "rgba(0, 0, 0, 0.8)",
            padding: 12,
            titleFont: { size: 14, weight: "bold" },
            bodyFont: { size: 13 },
            borderColor: "rgba(255, 255, 255, 0.3)",
            borderWidth: 1,
            callbacks: {
                label: function (context) {
                    const label = context.dataset.label || "";
                    const value = context.parsed.y;
                    const formatted =
                        "৳" +
                        new Intl.NumberFormat("en-BD", {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0,
                        }).format(value);
                    return `${label}: ${formatted}`;
                },
            },
        },
    },
};

const bdCompanyFilesData = computed(() => ({
    labels: [
        "Total at MITT",
        "At BD Company",
        "BD Pending",
        "BD Accepted",
        "BD Rejected",
        "BD Completed",
    ],
    datasets: [
        {
            label: "Base",
            data: [
                mittPipelineTotal.value,
                state.bdCompanyFiles.total,
                state.bdCompanyFiles.pending,
                state.bdCompanyFiles.accepted,
                state.bdCompanyFiles.rejected,
                state.bdCompanyFiles.completed,
            ],
            grouped: false,
            barThickness: 44,
            backgroundColor: [
                "rgba(99, 102, 241, 0.18)",
                "rgba(59, 130, 246, 0.18)",
                "rgba(251, 191, 36, 0.18)",
                "rgba(34, 197, 94, 0.18)",
                "rgba(239, 68, 68, 0.18)",
                "rgba(14, 116, 144, 0.18)",
            ],
            borderRadius: 14,
            borderSkipped: false,
            order: 1,
        },
        {
            label: "Core",
            data: [
                mittPipelineTotal.value,
                state.bdCompanyFiles.total,
                state.bdCompanyFiles.pending,
                state.bdCompanyFiles.accepted,
                state.bdCompanyFiles.rejected,
                state.bdCompanyFiles.completed,
            ],
            grouped: false,
            barThickness: 28,
            backgroundColor: [
                "rgba(99, 102, 241, 0.92)",
                "rgba(59, 130, 246, 0.55)",
                "rgba(251, 191, 36, 0.85)",
                "rgba(34, 197, 94, 0.6)",
                "rgba(239, 68, 68, 0.9)",
                "rgba(14, 116, 144, 0.7)",
            ],
            borderRadius: 12,
            borderSkipped: false,
            order: 2,
        },
    ],
}));

const fileTrackingDonutData = computed(() => ({
    labels: [
        "MITT Total",
        "BD Pending",
        "BD Accepted",
        "BD Rejected",
        "BD Completed",
    ],
    datasets: [
        {
            data: [
                mittPipelineTotal.value,
                state.bdCompanyFiles.pending,
                state.bdCompanyFiles.accepted,
                state.bdCompanyFiles.rejected,
                state.bdCompanyFiles.completed,
            ],
            backgroundColor: [
                "rgba(99, 102, 241, 0.85)",
                "rgba(251, 191, 36, 0.85)",
                "rgba(34, 197, 94, 0.85)",
                "rgba(239, 68, 68, 0.85)",
                "rgba(14, 116, 144, 0.85)",
            ],
            borderColor: "#ffffff",
            borderWidth: 2,
            hoverOffset: 6,
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
            backgroundColor: "rgba(15, 23, 42, 0.9)",
            padding: 10,
            titleFont: { size: 12, weight: "bold" },
            bodyFont: { size: 12 },
        },
    },
};

const bdCompanyFilesOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: "rgba(15, 23, 42, 0.9)",
            padding: 10,
            titleFont: { size: 13, weight: "bold" },
            bodyFont: { size: 12 },
            callbacks: {
                label: (context) => {
                    if (context.dataset?.label === "Base") {
                        return null;
                    }
                    return `${context.label}: ${context.parsed.y}`;
                },
            },
        },
    },
    datasets: {
        bar: {
            barPercentage: 1,
            categoryPercentage: 0.85,
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: { precision: 0, font: { size: 11 } },
            grid: { color: "rgba(15, 23, 42, 0.06)" },
        },
        x: {
            ticks: {
                font: { size: 10, weight: "bold" },
                maxRotation: 0,
                minRotation: 0,
                autoSkip: false,
            },
            grid: { display: false },
        },
    },
};

const bdCompanyTiles = computed(() => [
    {
        key: "bd_total",
        label: "At BD Company",
        value: state.bdCompanyFiles.total,
        tone: "blue",
        query: { bd_company_scope: 1 },
    },
    {
        key: "bd_pending",
        label: "BD Pending",
        value: state.bdCompanyFiles.pending,
        tone: "amber",
        query: { bd_company_status: "pending" },
    },
    {
        key: "bd_accepted",
        label: "BD Accepted",
        value: state.bdCompanyFiles.accepted,
        tone: "green",
        query: { bd_company_status: "accepted" },
    },
    {
        key: "bd_rejected",
        label: "BD Rejected",
        value: state.bdCompanyFiles.rejected,
        tone: "red",
        query: { bd_company_status: "rejected" },
    },
    {
        key: "bd_completed",
        label: "BD Completed",
        value: state.bdCompanyFiles.completed,
        tone: "teal",
        query: { bd_company_status: "completed" },
    },
]);

const goToClients = (query) => {
    router.visit("/clients", { data: query });
};

const goToAgent = (agentId) => {
    if (!agentId) return;
    router.visit(`/agents/${agentId}`);
};

const goToForeignCountry = (country) => {
    if (!country) return;
    router.visit(route("foreign-companies.country", { country }));
};

const formatBytes = (bytes) => {
    if (!bytes || bytes <= 0) return "0 GB";
    const gb = bytes / (1024 * 1024 * 1024);
    return gb.toFixed(2) + " GB";
};

const storagePercent = computed(() => {
    const totalBytes = state.appUsage.total || 0;
    const gb = totalBytes / (1024 * 1024 * 1024);
    const percent = (gb / 10) * 100;
    return Math.max(0, Math.min(100, Math.round(percent)));
});

const mittPipelineTotal = computed(
    () => state.bdCompanyFiles.agency_total + state.bdCompanyFiles.total,
);
</script>

<template>
    <Head title="Dashboard" />

    <div class="p-4 md:p-6 space-y-6">
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
        >
            <div
                class="w-full max-w-3xl rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-800 shadow-md px-6 py-5 text-white"
            >
                <h1 class="text-2xl font-semibold">Dashboard</h1>
                <p class="text-sm text-blue-100">
                    Overview of sales, expenses, and payables/receivables with
                    smart analytics.
                </p>
                <div v-if="state.loading" class="text-sm text-blue-100 mt-2">
                    Loading...
                </div>
            </div>
            <div class="w-full lg:w-64">
                <div
                    class="relative overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-sm px-4 py-3"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-[11px] uppercase tracking-[0.2em] text-blue-600 font-semibold"
                            >
                                Storage
                            </p>
                            <p class="text-xs text-gray-500">App usage</p>
                        </div>
                        <div class="text-xs font-semibold text-gray-700">
                            {{ formatBytes(state.appUsage.total) }}
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-3">
                        <div
                            class="relative h-16 w-16 overflow-hidden rounded-2xl bg-blue-50 border border-blue-100"
                        >
                            <div
                                class="absolute inset-x-0 bottom-0 water-wave"
                                :style="{ height: `${storagePercent}%` }"
                            >
                                <div class="water-surface"></div>
                            </div>
                            <div
                                class="absolute inset-0 flex flex-col items-center justify-center text-[11px] font-bold text-blue-700"
                            >
                                <span class="text-xs"
                                    >{{ storagePercent }}%</span
                                >
                                <span
                                    class="text-[10px] font-semibold text-blue-500"
                                    >Used</span
                                >
                            </div>
                        </div>
                        <div class="text-[11px] text-gray-500 leading-4">
                            <div class="font-semibold text-gray-700">
                                {{ formatBytes(state.appUsage.total) }}
                            </div>
                            <div class="text-[10px] text-gray-400">
                                of 10 GB
                            </div>
                            <div
                                class="mt-1 flex items-center gap-1 text-[10px] text-blue-500"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full bg-blue-400 animate-pulse"
                                ></span>
                                Live usage
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards - Clickable -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div
                v-for="item in totals"
                :key="item.label"
                @click="navigateTo(item.label)"
                class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 hover:shadow-lg hover:border-blue-400 hover:scale-105 transition-all duration-300 cursor-pointer group"
            >
                <div
                    class="text-sm text-gray-500 group-hover:text-blue-600 transition-colors"
                >
                    {{ item.label }}
                </div>
                <div
                    class="text-2xl font-semibold text-gray-900 group-hover:text-blue-700 transition-colors"
                >
                    {{ item.value }}
                </div>
                <div
                    class="text-xs text-gray-400 mt-1 group-hover:text-blue-500 transition-colors"
                >
                    Click to view details →
                </div>
            </div>
        </div>

        <!-- File Tracking Section -->
        <div
            class="rounded-[28px] border border-slate-200 bg-gradient-to-br from-white via-slate-50 to-slate-100/80 shadow-[0_20px_60px_-40px_rgba(15,23,42,0.45)] p-6"
        >
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-white"
                    >
                        <font-awesome-icon icon="file-lines" />
                        File Tracking
                    </div>
                    <p class="mt-1 text-sm text-slate-600">
                        Friendly overview of MITT and BD company processing
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div
                        class="flex items-center gap-2 rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-xs font-semibold text-slate-600 shadow-sm"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"
                        ></span>
                        Live counts
                    </div>
                    <div
                        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white/90 px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm"
                    >
                        <span
                            class="text-[10px] uppercase tracking-wide text-slate-400"
                            >Range</span
                        >
                        <select
                            v-model="fileRangePreset"
                            class="rounded-lg border border-slate-200 bg-white px-2 py-1 pr-8 text-xs text-slate-700"
                        >
                            <option value="this_week">This Week</option>
                            <option value="last_week">Last Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_year">This Year</option>
                            <option value="last_year">Last Year</option>
                            <option value="custom">Custom</option>
                        </select>
                        <div
                            v-if="fileRangePreset === 'custom'"
                            class="flex items-center gap-2"
                        >
                            <input
                                v-model="fileCustomRange.start"
                                type="date"
                                class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-700"
                            />
                            <span class="text-[10px] text-slate-400">to</span>
                            <input
                                v-model="fileCustomRange.end"
                                type="date"
                                class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-700"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-3 lg:grid-cols-3">
                <div
                    class="rounded-2xl border border-indigo-100 bg-white/90 p-5 shadow-[0_18px_40px_-30px_rgba(99,102,241,0.55)]"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-indigo-600"
                            >
                                All Files at MITT
                            </p>
                            <p class="text-[11px] text-slate-500">
                                Total pipeline (MITT + BD)
                            </p>
                        </div>
                        <span class="text-xl font-bold text-indigo-700">{{
                            mittPipelineTotal
                        }}</span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            @click="goToClients({ agency_scope: 1 })"
                            class="rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-2.5 text-left transition hover:bg-indigo-100 hover:shadow-sm"
                        >
                            <div
                                class="flex items-center gap-2 text-[11px] font-semibold text-indigo-700"
                            >
                                <font-awesome-icon
                                    icon="file-lines"
                                    class="text-indigo-500"
                                />
                                MITT Pending
                            </div>
                            <div class="mt-1 text-lg font-bold text-indigo-800">
                                {{ state.bdCompanyFiles.agency_total }}
                            </div>
                        </button>
                        <button
                            type="button"
                            @click="goToClients({ bd_company_scope: 1 })"
                            class="rounded-xl border border-blue-100 bg-blue-50 px-3 py-2.5 text-left transition hover:bg-blue-100 hover:shadow-sm"
                        >
                            <div
                                class="flex items-center gap-2 text-[11px] font-semibold text-blue-700"
                            >
                                <font-awesome-icon
                                    icon="building"
                                    class="text-blue-500"
                                />
                                At BD Company
                            </div>
                            <div class="mt-1 text-lg font-bold text-blue-800">
                                {{ state.bdCompanyFiles.total }}
                            </div>
                        </button>
                        <button
                            type="button"
                            @click="
                                goToClients({ bd_company_status: 'rejected' })
                            "
                            class="rounded-xl border border-red-100 bg-red-50 px-3 py-2.5 text-left transition hover:bg-red-100 hover:shadow-sm"
                        >
                            <div
                                class="flex items-center gap-2 text-[11px] font-semibold text-red-700"
                            >
                                <font-awesome-icon
                                    icon="circle-xmark"
                                    class="text-red-500"
                                />
                                Rejected
                            </div>
                            <div class="mt-1 text-lg font-bold text-red-800">
                                {{ state.bdCompanyFiles.rejected }}
                            </div>
                        </button>
                        <button
                            type="button"
                            @click="
                                goToClients({ bd_company_status: 'completed' })
                            "
                            class="rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2.5 text-left transition hover:bg-emerald-100 hover:shadow-sm"
                        >
                            <div
                                class="flex items-center gap-2 text-[11px] font-semibold text-emerald-700"
                            >
                                <font-awesome-icon
                                    icon="trophy"
                                    class="text-emerald-500"
                                />
                                Completed
                            </div>
                            <div
                                class="mt-1 text-lg font-bold text-emerald-800"
                            >
                                {{ state.bdCompanyFiles.completed }}
                            </div>
                        </button>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-blue-100 bg-white/90 p-5 shadow-[0_18px_40px_-30px_rgba(59,130,246,0.45)] lg:col-span-2"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-blue-600"
                            >
                                BD Company Breakdown
                            </p>
                            <p class="text-[11px] text-slate-500">
                                Status-wise processing
                            </p>
                        </div>
                        <span
                            class="text-xs font-semibold text-blue-700 bg-blue-50 px-3 py-1 rounded-full"
                        >
                            {{ state.bdCompanyFiles.total }} in BD
                        </span>
                    </div>
                    <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                        <button
                            v-for="tile in bdCompanyTiles"
                            :key="tile.key"
                            type="button"
                            @click="goToClients(tile.query)"
                            class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white/80 px-3 py-3 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                        >
                            <div
                                :class="[
                                    'absolute left-0 top-0 h-full w-1.5',
                                    tile.tone === 'blue' && 'bg-blue-500',
                                    tile.tone === 'amber' && 'bg-amber-500',
                                    tile.tone === 'green' && 'bg-green-500',
                                    tile.tone === 'red' && 'bg-red-500',
                                    tile.tone === 'teal' && 'bg-teal-500',
                                    tile.tone === 'indigo' && 'bg-indigo-500',
                                ]"
                            ></div>
                            <div
                                :class="[
                                    'text-[11px] font-semibold uppercase tracking-wide',
                                    tile.tone === 'blue' && 'text-blue-600',
                                    tile.tone === 'amber' && 'text-amber-600',
                                    tile.tone === 'green' && 'text-green-600',
                                    tile.tone === 'red' && 'text-red-600',
                                    tile.tone === 'teal' && 'text-teal-600',
                                    tile.tone === 'indigo' && 'text-indigo-600',
                                ]"
                            >
                                <div class="flex items-center gap-2">
                                    <font-awesome-icon
                                        :icon="
                                            tile.tone === 'amber'
                                                ? 'clock'
                                                : tile.tone === 'green'
                                                  ? 'circle-check'
                                                  : tile.tone === 'red'
                                                    ? 'circle-xmark'
                                                    : tile.tone === 'teal'
                                                      ? 'trophy'
                                                      : 'building'
                                        "
                                        class="opacity-70"
                                    />
                                    {{ tile.label }}
                                </div>
                            </div>
                            <div
                                class="mt-2 text-xl font-bold text-slate-900 group-hover:text-slate-950"
                            >
                                {{ tile.value }}
                            </div>
                            <div
                                class="mt-1 text-[11px] text-slate-400 group-hover:text-slate-500"
                            >
                                View list →
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_18px_40px_-30px_rgba(15,23,42,0.35)]"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-slate-600"
                            >
                                Tracking Graph
                            </p>
                            <p class="text-[11px] text-slate-500">
                                Quick breakdown
                            </p>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-500"
                            >Live</span
                        >
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="h-36 w-36">
                            <Doughnut
                                :data="fileTrackingDonutData"
                                :options="fileTrackingDonutOptions"
                            />
                        </div>
                        <div class="space-y-2 text-xs text-slate-600">
                            <div class="flex items-center gap-2">
                                <span
                                    class="h-2 w-2 rounded-full bg-indigo-500"
                                ></span>
                                MITT Total: {{ mittPipelineTotal }}
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="h-2 w-2 rounded-full bg-amber-400"
                                ></span>
                                BD Pending: {{ state.bdCompanyFiles.pending }}
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="h-2 w-2 rounded-full bg-green-500"
                                ></span>
                                BD Accepted: {{ state.bdCompanyFiles.accepted }}
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="h-2 w-2 rounded-full bg-red-500"
                                ></span>
                                BD Rejected: {{ state.bdCompanyFiles.rejected }}
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="h-2 w-2 rounded-full bg-teal-600"
                                ></span>
                                BD Completed:
                                {{ state.bdCompanyFiles.completed }}
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_18px_40px_-30px_rgba(15,23,42,0.35)]"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-slate-600"
                            >
                                Tracking Bars
                            </p>
                            <p class="text-[11px] text-slate-500">
                                MITT vs BD company status
                            </p>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-500"
                            >Live</span
                        >
                    </div>
                    <div class="h-56">
                        <Bar
                            :data="bdCompanyFilesData"
                            :options="bdCompanyFilesOptions"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Agent & Foreign Country Summaries -->
        <div class="grid gap-4 lg:grid-cols-2">
            <div
                class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-white via-emerald-50 to-emerald-100/60 shadow-sm p-6"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Agent Client Summary
                        </h2>
                        <p class="text-xs text-gray-600">
                            Each agent and total assigned clients
                        </p>
                    </div>
                    <div class="flex flex-nowrap items-center gap-2 shrink-0">
                        <span
                            class="text-xs font-semibold text-emerald-700 bg-emerald-100 px-3 py-1 rounded-full whitespace-nowrap"
                        >
                            {{ state.agentClientSummary.length }} Agents
                        </span>
                        <div
                            class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-white px-3 py-2 text-xs font-semibold text-emerald-700 shadow-sm"
                        >
                            <span
                                class="text-[10px] uppercase tracking-wide text-emerald-400"
                                >Range</span
                            >
                            <select
                                v-model="agentRangePreset"
                                class="rounded-lg border border-emerald-200 bg-white px-2 py-1 pr-8 text-xs text-emerald-700 whitespace-nowrap"
                            >
                                <option value="this_week">This Week</option>
                                <option value="last_week">Last Week</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="this_year">This Year</option>
                                <option value="last_year">Last Year</option>
                                <option value="custom">Custom</option>
                            </select>
                            <div
                                v-if="agentRangePreset === 'custom'"
                                class="flex items-center gap-2"
                            >
                                <input
                                    v-model="agentCustomRange.start"
                                    type="date"
                                    class="rounded-lg border border-emerald-200 bg-white px-2 py-1 text-xs text-emerald-700"
                                />
                                <span class="text-[10px] text-emerald-400"
                                    >to</span
                                >
                                <input
                                    v-model="agentCustomRange.end"
                                    type="date"
                                    class="rounded-lg border border-emerald-200 bg-white px-2 py-1 text-xs text-emerald-700"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 max-h-[240px] space-y-3 overflow-y-auto pr-1">
                    <div
                        v-for="agent in state.agentClientSummary"
                        :key="agent.id"
                        class="flex items-center justify-between rounded-xl bg-white/80 px-4 py-3 shadow-sm transition hover:shadow-md hover:-translate-y-0.5 cursor-pointer"
                        @click="goToAgent(agent.id)"
                    >
                        <div class="text-sm font-semibold text-gray-900">
                            {{ agent.name }}
                        </div>
                        <span
                            class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700"
                        >
                            {{ agent.clients_count }} Clients
                        </span>
                    </div>
                    <div
                        v-if="!state.agentClientSummary.length"
                        class="text-sm text-gray-500"
                    >
                        No agent data found.
                    </div>
                </div>
            </div>

            <div
                class="rounded-2xl border border-indigo-200 bg-gradient-to-br from-white via-indigo-50 to-indigo-100/60 shadow-sm p-6"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Foreign Country Summary
                        </h2>
                        <p class="text-xs text-gray-600">
                            Clients grouped by destination country
                        </p>
                    </div>
                    <div class="flex flex-nowrap items-center gap-2 shrink-0">
                        <span
                            class="text-xs font-semibold text-indigo-700 bg-indigo-100 px-3 py-1 rounded-full whitespace-nowrap"
                        >
                            {{ state.foreignCountrySummary.length }} Countries
                        </span>
                        <div
                            class="flex items-center gap-2 rounded-xl border border-indigo-200 bg-white px-3 py-2 text-xs font-semibold text-indigo-700 shadow-sm ml-2"
                        >
                            <span
                                class="text-[10px] uppercase tracking-wide text-indigo-400"
                                >Range</span
                            >
                            <select
                                v-model="countryRangePreset"
                                class="rounded-lg border border-indigo-200 bg-white px-2 py-1 pr-8 text-xs text-indigo-700 whitespace-nowrap"
                            >
                                <option value="this_week">This Week</option>
                                <option value="last_week">Last Week</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="this_year">This Year</option>
                                <option value="last_year">Last Year</option>
                                <option value="custom">Custom</option>
                            </select>
                            <div
                                v-if="countryRangePreset === 'custom'"
                                class="flex items-center gap-2"
                            >
                                <input
                                    v-model="countryCustomRange.start"
                                    type="date"
                                    class="rounded-lg border border-indigo-200 bg-white px-2 py-1 text-xs text-indigo-700"
                                />
                                <span class="text-[10px] text-indigo-400"
                                    >to</span
                                >
                                <input
                                    v-model="countryCustomRange.end"
                                    type="date"
                                    class="rounded-lg border border-indigo-200 bg-white px-2 py-1 text-xs text-indigo-700"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 max-h-[240px] space-y-3 overflow-y-auto pr-1">
                    <div
                        v-for="countryItem in state.foreignCountrySummary"
                        :key="countryItem.country"
                        class="flex items-center justify-between rounded-xl bg-white/80 px-4 py-3 shadow-sm transition hover:shadow-md hover:-translate-y-0.5 cursor-pointer"
                        @click="goToForeignCountry(countryItem.country)"
                    >
                        <div class="text-sm font-semibold text-gray-900">
                            {{ countryItem.country }}
                        </div>
                        <span
                            class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700"
                        >
                            {{ countryItem.total }} Clients
                        </span>
                    </div>
                    <div
                        v-if="!state.foreignCountrySummary.length"
                        class="text-sm text-gray-500"
                    >
                        No foreign country data found.
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid gap-4 lg:grid-cols-3">
            <!-- Sales vs Expenses Sine Wave Chart -->
            <div
                class="lg:col-span-2 bg-gradient-to-br from-white to-blue-50 border border-blue-100 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 p-6"
            >
                <div class="flex items-center justify-between mb-4">
                    <h2
                        class="text-lg font-semibold text-gray-900 flex items-center gap-2"
                    >
                        <span class="w-1 h-6 bg-blue-500 rounded-full"></span>
                        Sales vs Expenses Trend
                    </h2>
                    <div
                        class="text-xs text-gray-500 bg-white px-3 py-1 rounded-full shadow-sm"
                    >
                        Last 6 months (Sine Wave)
                    </div>
                </div>
                <div
                    class="grid gap-3 sm:grid-cols-4 text-sm text-gray-700 mb-4"
                >
                    <div
                        class="bg-white border border-gray-200 rounded-lg px-4 py-2"
                    >
                        <div class="text-xs text-gray-500">Total Sales</div>
                        <div class="font-semibold text-gray-900">
                            {{
                                "৳" +
                                new Intl.NumberFormat("en-BD", {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0,
                                }).format(state.salesSummary.total)
                            }}
                        </div>
                    </div>
                    <div
                        class="bg-white border border-gray-200 rounded-lg px-4 py-2"
                    >
                        <div class="text-xs text-gray-500">Paid</div>
                        <div class="font-semibold text-green-700">
                            {{
                                "৳" +
                                new Intl.NumberFormat("en-BD", {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0,
                                }).format(state.salesSummary.paid)
                            }}
                        </div>
                    </div>
                    <div
                        class="bg-white border border-gray-200 rounded-lg px-4 py-2"
                    >
                        <div class="text-xs text-gray-500">Due</div>
                        <div class="font-semibold text-orange-700">
                            {{
                                "৳" +
                                new Intl.NumberFormat("en-BD", {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0,
                                }).format(state.salesSummary.due)
                            }}
                        </div>
                    </div>
                    <div
                        class="bg-white border border-gray-200 rounded-lg px-4 py-2"
                    >
                        <div class="text-xs text-gray-500">Total Expense</div>
                        <div class="font-semibold text-red-700">
                            {{
                                "৳" +
                                new Intl.NumberFormat("en-BD", {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0,
                                }).format(state.salesSummary.expenses)
                            }}
                        </div>
                    </div>
                </div>
                <div class="h-80 bg-white rounded-lg p-2">
                    <Line
                        :data="salesExpensesTrendData"
                        :options="lineChartOptions"
                    />
                </div>
            </div>

            <!-- Receivable vs Payable Doughnut Chart -->
            <div
                class="bg-gradient-to-br from-white to-green-50 border border-green-100 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 p-6"
            >
                <div class="mb-4">
                    <h2
                        class="text-lg font-semibold text-gray-900 flex items-center gap-2"
                    >
                        <span class="w-1 h-6 bg-green-500 rounded-full"></span>
                        Receivable vs Payable
                    </h2>
                    <div
                        class="text-xs text-gray-500 bg-white px-3 py-1 rounded-full shadow-sm inline-block mt-1"
                    >
                        Total Outstanding
                    </div>
                </div>
                <div class="h-72 bg-white rounded-lg p-2">
                    <Doughnut
                        :data="receivablePayableData"
                        :options="doughnutChartOptions"
                    />
                </div>
            </div>
        </div>

        <!-- Entity Distribution and Details -->
        <div class="grid gap-4 lg:grid-cols-3">
            <!-- Entity Distribution Pie Chart -->
            <div
                class="bg-gradient-to-br from-white to-purple-50 border border-purple-100 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 p-6"
            >
                <div class="mb-4">
                    <h2
                        class="text-lg font-semibold text-gray-900 flex items-center gap-2"
                    >
                        <span class="w-1 h-6 bg-purple-500 rounded-full"></span>
                        Entity Distribution
                    </h2>
                    <div
                        class="text-xs text-gray-500 bg-white px-3 py-1 rounded-full shadow-sm inline-block mt-1"
                    >
                        Organization Overview
                    </div>
                </div>
                <div class="h-72 bg-white rounded-lg p-2">
                    <Pie
                        :data="entityDistributionData"
                        :options="pieChartOptions"
                    />
                </div>
            </div>

            <!-- Client Advance -->
            <div
                class="bg-white border border-gray-200 rounded-lg shadow-sm p-4"
            >
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-green-700">
                        Client Advance
                    </h2>
                    <div class="text-2xl font-bold text-green-600">
                        {{
                            "৳" +
                            new Intl.NumberFormat("en-BD", {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0,
                            }).format(state.receivableToday.total)
                        }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        Extra paid amount over total fee
                    </div>
                </div>
                <ul class="space-y-2 max-h-60 overflow-y-auto">
                    <li
                        v-for="item in state.receivableToday.items"
                        :key="item.name"
                        class="flex justify-between text-sm text-gray-800 border-b pb-2 hover:bg-gray-50 px-2 -mx-2 rounded"
                    >
                        <span class="font-medium">{{ item.name }}</span>
                        <span class="text-green-600">{{ item.amount }}</span>
                    </li>
                    <li
                        v-if="!state.receivableToday.items.length"
                        class="text-sm text-gray-500 text-center py-4"
                    >
                        No advance payments
                    </li>
                </ul>
            </div>

            <!-- Client Due -->
            <div
                class="bg-white border border-gray-200 rounded-lg shadow-sm p-4"
            >
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-red-700">
                        Client Due
                    </h2>
                    <div class="text-2xl font-bold text-red-600">
                        {{
                            "৳" +
                            new Intl.NumberFormat("en-BD", {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0,
                            }).format(state.payableToday.total)
                        }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        Total due amount from clients
                    </div>
                </div>
                <ul class="space-y-2 max-h-60 overflow-y-auto">
                    <li
                        v-for="item in state.payableToday.items"
                        :key="item.name"
                        class="flex justify-between text-sm text-gray-800 border-b pb-2 hover:bg-gray-50 px-2 -mx-2 rounded"
                    >
                        <span class="font-medium">{{ item.name }}</span>
                        <span class="text-red-600">{{ item.amount }}</span>
                    </li>
                    <li
                        v-if="!state.payableToday.items.length"
                        class="text-sm text-gray-500 text-center py-4"
                    >
                        No due entries
                    </li>
                </ul>
            </div>
        </div>

        <!-- Refund Summary -->
        <div
            class="rounded-2xl border border-rose-200 bg-gradient-to-br from-white via-rose-50 to-rose-100/60 shadow-sm p-6"
        >
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-4"
            >
                <div>
                    <h2
                        class="text-lg font-semibold text-gray-900 flex items-center gap-2"
                    >
                        <span class="w-1 h-6 bg-rose-500 rounded-full"></span>
                        Refund Summary
                    </h2>
                    <p class="text-xs text-gray-600">
                        All refunds issued to clients and agents
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="text-xs font-semibold text-rose-700 bg-rose-100 px-3 py-1 rounded-full"
                    >
                        {{ state.refundSummary.count }} Refunds
                    </span>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Total Refunded</p>
                        <p class="text-lg font-bold text-rose-600">
                            {{
                                "৳" +
                                new Intl.NumberFormat("en-BD", {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0,
                                }).format(state.refundSummary.total)
                            }}
                        </p>
                    </div>
                    <div
                        class="flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-700 shadow-sm"
                    >
                        <span
                            class="text-[10px] uppercase tracking-wide text-rose-400"
                            >Range</span
                        >
                        <select
                            v-model="refundRangePreset"
                            class="rounded-lg border border-rose-200 bg-white px-2 py-1 pr-8 text-xs text-rose-700 whitespace-nowrap"
                        >
                            <option value="this_week">This Week</option>
                            <option value="last_week">Last Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_year">This Year</option>
                            <option value="last_year">Last Year</option>
                            <option value="custom">Custom</option>
                        </select>
                        <div
                            v-if="refundRangePreset === 'custom'"
                            class="flex items-center gap-2"
                        >
                            <input
                                v-model="refundCustomRange.start"
                                type="date"
                                class="rounded-lg border border-rose-200 bg-white px-2 py-1 text-xs text-rose-700"
                            />
                            <span class="text-[10px] text-rose-400">to</span>
                            <input
                                v-model="refundCustomRange.end"
                                type="date"
                                class="rounded-lg border border-rose-200 bg-white px-2 py-1 text-xs text-rose-700"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="h-px w-full bg-gradient-to-r from-transparent via-rose-200 to-transparent mb-4"
            ></div>
            <div
                class="overflow-hidden rounded-xl border border-rose-100 bg-white"
            >
                <div class="max-h-[280px] overflow-y-auto">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="bg-rose-50 text-xs uppercase text-rose-600 sticky top-0"
                        >
                            <tr>
                                <th class="px-4 py-2.5 font-semibold">Name</th>
                                <th class="px-4 py-2.5 font-semibold">Type</th>
                                <th class="px-4 py-2.5 font-semibold">Date</th>
                                <th
                                    class="px-4 py-2.5 font-semibold text-right"
                                >
                                    Amount
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rose-50">
                            <tr
                                v-for="(item, idx) in state.refundSummary.items"
                                :key="idx"
                                class="hover:bg-rose-50/50 transition"
                            >
                                <td
                                    class="px-4 py-2.5 font-semibold text-gray-900"
                                >
                                    {{ item.name }}
                                </td>
                                <td class="px-4 py-2.5">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                            item.type === 'Agent'
                                                ? 'bg-purple-100 text-purple-800'
                                                : 'bg-blue-100 text-blue-800',
                                        ]"
                                    >
                                        {{ item.type }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-gray-500">
                                    {{ item.date || "—" }}
                                </td>
                                <td
                                    class="px-4 py-2.5 text-right font-semibold text-rose-600"
                                >
                                    {{
                                        "৳" +
                                        new Intl.NumberFormat("en-BD", {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        }).format(item.amount)
                                    }}
                                </td>
                            </tr>
                            <tr v-if="!state.refundSummary.items.length">
                                <td
                                    colspan="4"
                                    class="px-4 py-6 text-center text-sm text-gray-500"
                                >
                                    No refunds recorded yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.water-wave {
    transition: height 0.8s ease;
    animation: waterFloat 3.5s ease-in-out infinite;
    background: linear-gradient(
        120deg,
        rgba(59, 130, 246, 0.85),
        rgba(34, 211, 238, 0.9),
        rgba(59, 130, 246, 0.85)
    );
}

.water-surface {
    height: 8px;
    background: repeating-linear-gradient(
        90deg,
        rgba(255, 255, 255, 0.35) 0,
        rgba(255, 255, 255, 0.35) 10px,
        rgba(255, 255, 255, 0.1) 10px,
        rgba(255, 255, 255, 0.1) 20px
    );
    animation: surfaceDrift 2.6s linear infinite;
}

@keyframes waterFloat {
    0% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-6px);
    }
    100% {
        transform: translateY(0);
    }
}

@keyframes surfaceDrift {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-20px);
    }
}
</style>
