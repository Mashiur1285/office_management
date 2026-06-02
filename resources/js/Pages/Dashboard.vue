<script setup>
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { onMounted, reactive, computed, ref, watch } from "vue";
import { Bar, Doughnut } from "vue-chartjs";
import {
    Chart as ChartJS,
    ArcElement,
    BarElement,
    CategoryScale,
    LinearScale,
    Title,
    Tooltip,
    Legend,
} from "chart.js";

ChartJS.register(
    ArcElement,
    BarElement,
    CategoryScale,
    LinearScale,
    Title,
    Tooltip,
    Legend
);

const page = usePage();

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
                backgroundColor: "#1e5b43",
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
                backgroundColor: ["#1e5b43", "#e0ede6"],
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
        "MITT",
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
                "#1e5b43",
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
            <!-- Card 1: Green (Clients) -->
            <div class="bg-gradient-to-br from-[#1e5b43] to-[#174633] rounded-[24px] p-6 text-white relative shadow-[0_4px_30px_rgba(22,163,74,0.45)] hover:shadow-[0_8px_55px_rgba(22,163,74,0.7)] hover:-translate-y-1 transition-all duration-300 cursor-pointer overflow-hidden group" @click="navigateTo('Clients')">
                <div class="flex justify-between items-start mb-4 relative z-10">
                    <h3 class="font-medium text-emerald-50 text-[15px]">Total Clients</h3>
                    <div class="w-8 h-8 rounded-full border border-emerald-300/30 flex items-center justify-center bg-white/10 backdrop-blur-sm -mr-1 -mt-1 group-hover:bg-white/20 transition-colors">
                        <font-awesome-icon icon="arrow-trend-up" class="w-3 h-3 text-white" />
                    </div>
                </div>
                <div class="text-[52px] font-bold mb-5 tracking-tight leading-none relative z-10">{{ state.stats.total_clients ?? 0 }}</div>
                <div class="flex items-center gap-2 text-xs text-emerald-100 font-medium relative z-10">
                    <span class="bg-[#2f8863] text-white px-2 py-0.5 rounded flex items-center gap-1 font-bold">
                        <font-awesome-icon icon="caret-up" class="w-2.5 h-2.5" /> 12
                    </span>
                    <span>Increased from last month</span>
                </div>
            </div>

            <!-- Card 2: White (Agents) -->
            <div class="bg-white rounded-[24px] p-6 text-gray-900 border border-green-200 shadow-[0_0_22px_rgba(34,197,94,0.15)] hover:shadow-[0_0_40px_rgba(34,197,94,0.32)] hover:border-green-300 relative hover:-translate-y-1 transition duration-300 cursor-pointer group" @click="navigateTo('Agents')">
                 <div class="flex justify-between items-start mb-4">
                    <h3 class="font-medium text-gray-800 text-[15px]">Agents</h3>
                    <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center bg-white -mr-1 -mt-1 group-hover:border-gray-300 transition-colors">
                        <font-awesome-icon icon="arrow-trend-up" class="w-3 h-3 text-gray-600" />
                    </div>
                </div>
                <div class="text-[52px] font-bold mb-5 tracking-tight leading-none">{{ state.stats.total_agents ?? 0 }}</div>
                <div class="flex items-center gap-2 text-xs text-gray-500 font-medium">
                     <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded flex items-center gap-1 font-bold">
                         <font-awesome-icon icon="caret-up" class="w-2.5 h-2.5" /> 6
                     </span>
                     <span>Increased from last month</span>
                </div>
            </div>

             <!-- Card 3: White (BD Companies) -->
             <div class="bg-white rounded-[24px] p-6 text-gray-900 border border-green-200 shadow-[0_0_22px_rgba(34,197,94,0.15)] hover:shadow-[0_0_40px_rgba(34,197,94,0.32)] hover:border-green-300 relative hover:-translate-y-1 transition duration-300 cursor-pointer group" @click="navigateTo('BD Companies')">
                 <div class="flex justify-between items-start mb-4">
                    <h3 class="font-medium text-gray-800 text-[15px]">BD Companies</h3>
                    <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center bg-white -mr-1 -mt-1 group-hover:border-gray-300 transition-colors">
                        <font-awesome-icon icon="arrow-trend-up" class="w-3 h-3 text-gray-600" />
                    </div>
                </div>
                <div class="text-[52px] font-bold mb-5 tracking-tight leading-none">{{ state.stats.total_bd_companies ?? 0 }}</div>
                <div class="flex items-center gap-2 text-xs text-gray-500 font-medium">
                     <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded flex items-center gap-1 font-bold">
                         <font-awesome-icon icon="caret-up" class="w-2.5 h-2.5" /> 2
                     </span>
                     <span>Increased from last month</span>
                </div>
            </div>

             <!-- Card 4: White (Foreign Companies) -->
             <div class="bg-white rounded-[24px] p-6 text-gray-900 border border-green-200 shadow-[0_0_22px_rgba(34,197,94,0.15)] hover:shadow-[0_0_40px_rgba(34,197,94,0.32)] hover:border-green-300 relative hover:-translate-y-1 transition duration-300 cursor-pointer group" @click="navigateTo('Foreign Companies')">
                 <div class="flex justify-between items-start mb-4">
                    <h3 class="font-medium text-gray-800 text-[15px]">Foreign Companies</h3>
                    <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center bg-white -mr-1 -mt-1 group-hover:border-gray-300 transition-colors">
                        <font-awesome-icon icon="arrow-trend-up" class="w-3 h-3 text-gray-600" />
                    </div>
                </div>
                <div class="text-[52px] font-bold mb-5 tracking-tight leading-none">{{ state.stats.total_foreign_companies ?? 0 }}</div>
                <div class="flex items-center gap-2 text-xs text-gray-500 font-medium h-5">
                     <span>On Discuss</span>
                </div>
            </div>
        </div>

        <!-- Middle Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-5">
            <!-- Project Analytics (Sales/Expenses) -->
            <div class="lg:col-span-6 bg-white rounded-[24px] p-6 border border-green-200 shadow-[0_0_22px_rgba(34,197,94,0.15)] hover:shadow-[0_0_40px_rgba(34,197,94,0.32)] hover:border-green-300 transition-all duration-300">
                 <h3 class="font-bold text-gray-900 mb-6 text-lg">Sales Analytics</h3>
                 <!-- Legend inside chart -->
                 <div class="flex justify-center gap-4 text-[11px] font-medium text-gray-500 mb-2">
                      <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#1e5b43]"></span> Sales</div>
                      <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#b8decb]"></span> Expenses</div>
                 </div>
                 <div class="h-44 w-full">
                      <Bar :data="salesExpensesTrendData" :options="barChartOptions" />
                 </div>
            </div>

             <!-- Reminders -->
            <div class="lg:col-span-3 bg-white rounded-[24px] p-6 border border-green-200 shadow-[0_0_22px_rgba(34,197,94,0.15)] hover:shadow-[0_0_40px_rgba(34,197,94,0.32)] hover:border-green-300 transition-all duration-300 flex flex-col justify-between relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="font-bold text-gray-900 mb-6 text-lg">Reminders</h3>
                    <div class="text-xl font-bold text-[#1e5b43] leading-tight mb-2">
                        {{ state.bdCompanyFiles?.pending ?? 0 }} Files Pending<br>at BD Company
                    </div>
                    <div class="text-[13px] font-medium text-gray-500">
                        Total pipeline in BD: {{ state.bdCompanyFiles?.total ?? 0 }}
                    </div>
                </div>
                <button class="w-full bg-[#1e5b43] text-white py-3.5 rounded-full text-[13px] font-bold tracking-wide hover:bg-[#164230] transition flex items-center justify-center gap-2 mt-4 relative z-10 shadow-lg shadow-[#1e5b43]/30" @click="goToClients({ bd_company_status: 'pending'})">
                    <font-awesome-icon icon="folder-open" class="w-3.5 h-3.5" />
                    Review Files
                </button>
            </div>

             <!-- Projects List (Receivable Today/Client Due) -->
             <div class="lg:col-span-3 bg-white rounded-[24px] p-6 border border-green-200 shadow-[0_0_22px_rgba(34,197,94,0.15)] hover:shadow-[0_0_40px_rgba(34,197,94,0.32)] hover:border-green-300 transition-all duration-300 overflow-y-auto" style="height: 320px;">
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
        </div>

        <!-- Bottom Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-5 pb-8">
            <!-- Team Collaboration (Agent Summary) -->
             <div class="lg:col-span-5 bg-white rounded-[24px] p-6 border border-green-200 shadow-[0_0_22px_rgba(34,197,94,0.15)] hover:shadow-[0_0_40px_rgba(34,197,94,0.32)] hover:border-green-300 transition-all duration-300 h-[320px] overflow-y-auto">
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
                              idx % 3 === 0 ? 'bg-emerald-50 text-emerald-600 border-emerald-100' :
                              idx % 3 === 1 ? 'bg-yellow-50 text-yellow-600 border-yellow-100' :
                              'bg-red-50 text-red-500 border-red-100'
                          ]">
                              {{ agent.status || (idx % 3 === 0 ? 'Completed' : idx % 3 === 1 ? 'In Progress' : 'Pending') }}
                          </span>
                      </div>
                 </div>
             </div>

             <!-- Project Progress (Doughnut) -->
              <div class="lg:col-span-4 bg-white rounded-[24px] p-6 border border-green-200 shadow-[0_0_22px_rgba(34,197,94,0.15)] hover:shadow-[0_0_40px_rgba(34,197,94,0.32)] hover:border-green-300 transition-all duration-300 h-[320px] flex flex-col relative overflow-hidden">
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
                      <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#1e5b43]"></span> Receivable</div>
                      <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#e0ede6]"></span> Payable</div>
                  </div>
              </div>

             <!-- Time Tracker Tracker -->
             <div class="lg:col-span-3 bg-gradient-to-br from-[#0c2419] to-[#1e5b43] rounded-[24px] p-6 text-white shadow-[0_0_45px_rgba(22,163,74,0.45)] hover:shadow-[0_0_70px_rgba(22,163,74,0.7)] h-[320px] flex flex-col relative overflow-hidden group transition-all duration-300">
                 <!-- Aesthetic wave effects using radial gradients -->
                 <div class="absolute -right-8 -top-8 w-48 h-48 bg-[#2f8863]/40 rounded-full blur-[40px] mix-blend-screen transition duration-700 group-hover:bg-[#2f8863]/60"></div>
                 <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-[#10b981]/20 rounded-full blur-[60px] mix-blend-screen transition duration-700"></div>
                 
                 <!-- SVG Waves placeholder to mimic the complex 3D ribbons in Image -->
                 <svg class="absolute inset-0 w-full h-full opacity-[0.15] mix-blend-overlay" viewBox="0 0 100 100" preserveAspectRatio="none">
                     <path d="M0,50 Q25,20 50,50 T100,50" fill="none" stroke="#fff" stroke-width="2" />
                     <path d="M0,70 Q25,40 50,70 T100,70" fill="none" stroke="#fff" stroke-width="1.5" />
                     <path d="M0,90 Q25,60 50,90 T100,90" fill="none" stroke="#fff" stroke-width="4" />
                     <path d="M0,30 Q25,0 50,30 T100,30" fill="none" stroke="#fff" stroke-width="2" />
                 </svg>

                 <div class="relative z-10 flex flex-col h-full">
                     <h3 class="font-medium text-emerald-50 text-[15px] opacity-90">Total Storage</h3>
                     
                     <div class="flex-1 flex flex-col items-center justify-center -mt-4">
                         <div class="text-[44px] font-light tracking-wider text-white mb-6 drop-shadow-md">
                              {{ formatBytes(state.appUsage.total).replace(' GB', '') }}
                         </div>
                     </div>
                 </div>
             </div>
        </div>

        <!-- Airline Tickets Widget -->
        <div class="mb-6">
            <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                            <font-awesome-icon icon="plane-departure" class="w-4 h-4 text-[#1e5b43]" />
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
                            <div class="text-lg font-black text-emerald-600">{{ state.ticketStats.confirmed }}</div>
                            <div class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Confirmed</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-black text-amber-500">{{ state.ticketStats.rescheduled }}</div>
                            <div class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Rescheduled</div>
                        </div>
                        <Link href="/airline-tickets" class="text-xs text-emerald-700 font-semibold hover:underline">
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
                                :class="ticket.status === 'confirmed' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'"
                            >{{ ticket.status }}</span>
                            <Link :href="`/airline-tickets/${ticket.id}`" class="text-xs text-emerald-700 font-semibold hover:underline">View</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tracking Graph Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 pb-8">
            <!-- Tracking Donut -->
            <div class="bg-white rounded-[24px] p-6 border border-green-200 shadow-[0_0_22px_rgba(34,197,94,0.15)] hover:shadow-[0_0_40px_rgba(34,197,94,0.32)] hover:border-green-300 transition-all duration-300 h-[320px] flex flex-col relative overflow-hidden">
                <div class="flex items-center justify-between mb-2 z-10">
                    <h3 class="font-bold text-gray-900 text-lg">Tracking Graph</h3>
                    <span class="text-[11px] font-semibold text-gray-400 bg-gray-50 px-2.5 py-1 rounded-full border border-gray-100">Live</span>
                </div>
                <div class="flex-1 mt-4 relative z-10 flex flex-col items-center justify-center gap-6 w-full lg:flex-row lg:items-center lg:justify-between">
                    <div class="w-40 h-40 shrink-0 sm:w-44 sm:h-44 lg:w-48 lg:h-48">
                      <Doughnut :data="fileTrackingDonutData" :options="fileTrackingDonutOptions" />
                    </div>
                    <div class="w-full space-y-3 text-[12px] font-semibold text-gray-600 lg:mr-2 lg:flex-1 lg:pl-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> MITT Total</div>
                            <span class="text-gray-900">{{ mittPipelineTotal }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> BD Pending</div>
                            <span class="text-gray-900">{{ state.bdCompanyFiles?.pending || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> BD Accepted</div>
                            <span class="text-gray-900">{{ state.bdCompanyFiles?.accepted || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> BD Rejected</div>
                            <span class="text-gray-900">{{ state.bdCompanyFiles?.rejected || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span> BD Completed</div>
                            <span class="text-gray-900">{{ state.bdCompanyFiles?.completed || 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tracking Bars -->
            <div class="bg-white rounded-[24px] p-6 border border-green-200 shadow-[0_0_22px_rgba(34,197,94,0.15)] hover:shadow-[0_0_40px_rgba(34,197,94,0.32)] hover:border-green-300 transition-all duration-300 h-[320px] flex flex-col relative overflow-hidden">
                <div class="flex items-center justify-between mb-4 z-10">
                    <h3 class="font-bold text-gray-900 text-lg">Tracking Bars</h3>
                    <span class="text-[11px] font-semibold text-gray-400 bg-gray-50 px-2.5 py-1 rounded-full border border-gray-100">Status Array</span>
                </div>
                <div class="flex-1 w-full pt-4">
                     <Bar :data="bdCompanyFilesData" :options="bdCompanyFilesOptions" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Any required additional CSS overrides */
input::placeholder {
  color: #9ca3af;
  font-weight: 500;
}
</style>
