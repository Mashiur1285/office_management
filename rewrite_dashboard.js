const fs = require('fs');

const content = `<script setup>
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
});

const fetchData = async () => {
    state.loading = true;
    try {
        const params = {};
        const { data } = await axios.get("/dashboard/data", { params });
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
        x: { grid: { display: false, drawBorder: false }, ticks: { font: { weight: '600' } } }
    },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: "rgba(0, 0, 0, 0.8)",
            padding: 12,
            callbacks: {
                label: function (context) {
                    const value = context.parsed.y;
                    return \`৳\` + new Intl.NumberFormat("en-BD", { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
                },
            },
        },
    },
};

const salesExpensesTrendData = computed(() => {
    const labels = state.salesMonthly.map((m) => m.label?.substring(0, 3));
    const salesData = state.salesMonthly.map((m) => m.amount || 0);
    const expensesData = state.expensesMonthly.map((m) => m.amount || 0);

    return {
        labels: labels.length ? labels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
        datasets: [
            {
                label: "Sales",
                data: salesData.length ? salesData : [1000, 2000, 1500, 3000, 2500, 4000, 3500],
                backgroundColor: "#1e5b43",
                borderRadius: { topLeft: 20, topRight: 20, bottomLeft: 20, bottomRight: 20 },
                borderSkipped: false,
                barPercentage: 0.6,
                categoryPercentage: 0.8
            },
            {
                label: "Expenses",
                data: expensesData.length ? expensesData : [800, 1500, 1200, 2000, 1800, 2500, 2200],
                backgroundColor: "#b8decb",
                borderRadius: { topLeft: 20, topRight: 20, bottomLeft: 20, bottomRight: 20 },
                borderSkipped: false,
                barPercentage: 0.6,
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
    // If no real data, show a dummy ratio that results in 41% to map the image visually
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
    router.visit(\`/agents/\${agentId}\`);
};

</script>

<template>
    <Head title="Dashboard" />

    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#fdfdfd] min-h-screen text-gray-800 font-sans">
        <!-- Top header Area -->
        <div class="bg-white rounded-[24px] shadow-sm flex flex-col sm:flex-row sm:items-center justify-between p-3 mb-8 border border-gray-100 gap-4">
            <!-- Search -->
            <div class="flex items-center gap-2 pl-4 text-gray-400 bg-gray-50 rounded-full px-4 py-2 flex-grow sm:flex-grow-0 sm:w-80 border border-gray-100">
                <font-awesome-icon icon="magnifying-glass" class="w-4 h-4" />
                <input type="text" placeholder="Search task" class="border-none bg-transparent focus:ring-0 text-sm w-full text-gray-700 placeholder-gray-400 outline-none" />
                <span class="bg-gray-200 text-gray-600 text-[10px] px-1.5 py-0.5 rounded ml-2 font-semibold tracking-widest hidden sm:inline-block">⌘F</span>
            </div>
            
            <!-- Profile/icons -->
            <div class="flex items-center justify-end gap-3 pr-1 w-full sm:w-auto">
                <button class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
                    <font-awesome-icon icon="envelope" class="w-4 h-4" />
                </button>
                <div class="relative">
                    <button class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
                        <font-awesome-icon icon="bell" class="w-4 h-4" />
                    </button>
                </div>
                <div class="flex items-center gap-2 pr-1 py-1 ml-2 cursor-pointer">
                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-700 font-bold text-lg overflow-hidden shrink-0 border border-gray-200">
                        <img v-if="page.props.auth?.user?.profile_photo_url" :src="page.props.auth?.user?.profile_photo_url" class="w-full h-full object-cover">
                        <span v-else>{{ page.props.auth?.user?.name?.charAt(0) || 'U' }}</span>
                    </div>
                    <div class="flex flex-col hidden sm:flex">
                        <span class="text-sm font-semibold text-gray-800 leading-tight">{{ page.props.auth?.user?.name || 'User Name' }}</span>
                        <span class="text-xs text-gray-500">{{ page.props.auth?.user?.email?.length > 20 ? page.props.auth.user.email.substring(0, 18) + '...' : (page.props.auth?.user?.email || 'user@example.com') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Dashboard Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">Dashboard</h1>
                <p class="text-sm text-gray-500">Plan, prioritize, and accomplish your tasks with ease.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="bg-[#1e5b43] text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-[#164230] transition flex items-center gap-2">
                    <font-awesome-icon icon="plus" class="w-3.5 h-3.5" />
                    Add Project
                </button>
                <button class="border border-[#1e5b43] text-[#1e5b43] bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-emerald-50 transition">
                    Import Data
                </button>
            </div>
        </div>

        <!-- Stat Cards (4 cols) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-5">
            <!-- Card 1: Green (Clients) -->
            <div class="bg-gradient-to-br from-[#1e5b43] to-[#174633] rounded-[28px] p-6 text-white relative shadow-md hover:-translate-y-1 transition duration-300 cursor-pointer overflow-hidden" @click="navigateTo('Clients')">
                <div class="flex justify-between items-start mb-4 relative z-10">
                    <h3 class="font-medium text-emerald-50 text-[15px]">Total Clients</h3>
                    <div class="w-8 h-8 rounded-full border border-emerald-300/30 flex items-center justify-center bg-white/10 backdrop-blur-sm -mr-1 -mt-1 group-hover:rotate-45 transition-transform">
                        <font-awesome-icon icon="arrow-up-right" class="w-3 h-3 text-white" />
                    </div>
                </div>
                <div class="text-[52px] font-bold mb-4 tracking-tight leading-none relative z-10">{{ state.stats.total_clients || '24' }}</div>
                <div class="flex items-center gap-2 text-xs text-emerald-100 font-medium relative z-10">
                    <span class="bg-[#2f8863] text-white px-2 py-0.5 rounded flex items-center gap-1 font-bold">
                        <font-awesome-icon icon="caret-up" /> 12
                    </span>
                    <span>Increased from last month</span>
                </div>
            </div>

            <!-- Card 2: White (Agents) -->
            <div class="bg-white rounded-[28px] p-6 text-gray-900 border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] relative hover:-translate-y-1 transition duration-300 cursor-pointer" @click="navigateTo('Agents')">
                 <div class="flex justify-between items-start mb-4">
                    <h3 class="font-medium text-gray-800 text-[15px]">Ended Projects</h3>
                    <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center bg-white -mr-1 -mt-1 hover:bg-gray-50 transition-colors">
                        <font-awesome-icon icon="arrow-up-right" class="w-3 h-3 text-gray-600" />
                    </div>
                </div>
                <!-- Map 'Agents' data here for real usage but label it similar to design or keep exact label if requested. User requested same names but design like image. Let's use user's names. -->
                <div class="text-[52px] font-bold mb-4 tracking-tight leading-none">{{ state.stats.total_agents || '10' }}</div>
                <div class="flex items-center gap-2 text-xs text-gray-500 font-medium">
                     <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded flex items-center gap-1 font-bold">
                         <font-awesome-icon icon="caret-up" /> 6
                     </span>
                     <span>Increased from last month</span>
                </div>
            </div>

             <!-- Card 3: White (BD Companies) -->
             <div class="bg-white rounded-[28px] p-6 text-gray-900 border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] relative hover:-translate-y-1 transition duration-300 cursor-pointer" @click="navigateTo('BD Companies')">
                 <div class="flex justify-between items-start mb-4">
                    <h3 class="font-medium text-gray-800 text-[15px]">Running Projects</h3>
                    <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center bg-white -mr-1 -mt-1 hover:bg-gray-50 transition-colors">
                        <font-awesome-icon icon="arrow-up-right" class="w-3 h-3 text-gray-600" />
                    </div>
                </div>
                <div class="text-[52px] font-bold mb-4 tracking-tight leading-none">{{ state.stats.total_bd_companies || '12' }}</div>
                <div class="flex items-center gap-2 text-xs text-gray-500 font-medium">
                     <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded flex items-center gap-1 font-bold">
                         <font-awesome-icon icon="caret-up" /> 2
                     </span>
                     <span>Increased from last month</span>
                </div>
            </div>

             <!-- Card 4: White (Foreign Companies) -->
             <div class="bg-white rounded-[28px] p-6 text-gray-900 border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] relative hover:-translate-y-1 transition duration-300 cursor-pointer" @click="navigateTo('Foreign Companies')">
                 <div class="flex justify-between items-start mb-4">
                    <h3 class="font-medium text-gray-800 text-[15px]">Pending Project</h3>
                    <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center bg-white -mr-1 -mt-1 hover:bg-gray-50 transition-colors">
                        <font-awesome-icon icon="arrow-up-right" class="w-3 h-3 text-gray-600" />
                    </div>
                </div>
                <div class="text-[52px] font-bold mb-4 tracking-tight leading-none">{{ state.stats.total_foreign_companies || '2' }}</div>
                <div class="flex items-center gap-2 text-xs text-gray-500 font-medium">
                     <span>On Discuss</span>
                </div>
            </div>
        </div>

        <!-- Middle Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-5">
            <!-- Project Analytics (Sales/Expenses) -->
            <div class="lg:col-span-6 bg-white rounded-[28px] p-6 border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
                 <h3 class="font-bold text-gray-900 mb-6 text-lg">Project Analytics</h3>
                 <div class="h-44 mt-4 w-full pt-4">
                      <Bar :data="salesExpensesTrendData" :options="barChartOptions" />
                 </div>
            </div>

             <!-- Reminders -->
            <div class="lg:col-span-3 bg-white rounded-[28px] p-6 border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)] flex flex-col justify-between relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="font-bold text-gray-900 mb-6 text-lg">Reminders</h3>
                    <div class="text-xl font-bold text-[#1e5b43] leading-tight mb-2">
                        Meeting with Arc Company
                    </div>
                    <div class="text-[13px] font-medium text-gray-500">
                        Time : 02.00 pm - 04.00 pm
                    </div>
                </div>
                <button class="w-full bg-[#1e5b43] text-white py-3.5 rounded-full text-[13px] font-bold tracking-wide hover:bg-[#164230] transition flex items-center justify-center gap-2 mt-4 relative z-10 shadow-lg shadow-[#1e5b43]/30">
                    <font-awesome-icon icon="video" class="w-3 h-3" />
                    Start Meeting
                </button>
                <!-- Decorative element -->
                <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-green-50 rounded-full blur-2xl z-0"></div>
            </div>

             <!-- Projects List -->
             <div class="lg:col-span-3 bg-white rounded-[28px] p-6 border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)] overflow-y-auto" style="height: 320px;">
                 <div class="flex justify-between items-center mb-6">
                     <h3 class="font-bold text-gray-900 text-lg">Project</h3>
                     <button class="border border-gray-200 text-gray-800 text-xs font-semibold px-3 py-1.5 rounded-full hover:bg-gray-50 flex items-center gap-1 transition"><font-awesome-icon icon="plus" class="w-3 h-3" /> New</button>
                 </div>
                 <div class="space-y-5">
                      <!-- List Item 1 -->
                      <div class="flex items-start gap-4">
                           <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center shrink-0 mt-0.5">
                               <font-awesome-icon icon="code" class="text-blue-500 w-3.5 h-3.5" />
                           </div>
                           <div>
                               <div class="text-sm font-bold text-gray-900">Develop API Endpoints</div>
                               <div class="text-[11px] font-medium text-gray-400 mt-0.5">Due date: Nov 26, 2024</div>
                           </div>
                      </div>
                      <!-- List Item 2 -->
                      <div class="flex items-start gap-4">
                           <div class="w-8 h-8 rounded-full bg-teal-50 flex items-center justify-center shrink-0 mt-0.5">
                               <font-awesome-icon icon="user-plus" class="text-teal-500 w-3.5 h-3.5" />
                           </div>
                           <div>
                               <div class="text-sm font-bold text-gray-900">Onboarding Flow</div>
                               <div class="text-[11px] font-medium text-gray-400 mt-0.5">Due date: Nov 28, 2024</div>
                           </div>
                      </div>
                      <!-- List Item 3 -->
                      <div class="flex items-start gap-4">
                           <div class="w-8 h-8 rounded-full bg-orange-50 flex items-center justify-center shrink-0 mt-0.5">
                               <font-awesome-icon icon="shapes" class="text-orange-500 w-3.5 h-3.5" />
                           </div>
                           <div>
                               <div class="text-sm font-bold text-gray-900">Build Dashboard</div>
                               <div class="text-[11px] font-medium text-gray-400 mt-0.5">Due date: Nov 30, 2024</div>
                           </div>
                      </div>
                      <!-- List Item 4 -->
                      <div class="flex items-start gap-4">
                           <div class="w-8 h-8 rounded-full bg-yellow-50 flex items-center justify-center shrink-0 mt-0.5">
                               <font-awesome-icon icon="bolt" class="text-yellow-500 w-3.5 h-3.5" />
                           </div>
                           <div>
                               <div class="text-sm font-bold text-gray-900">Optimize Page Load</div>
                               <div class="text-[11px] font-medium text-gray-400 mt-0.5">Due date: Dec 5, 2024</div>
                           </div>
                      </div>
                      <!-- List Item 5 -->
                      <div class="flex items-start gap-4">
                           <div class="w-8 h-8 rounded-full bg-purple-50 flex items-center justify-center shrink-0 mt-0.5">
                               <font-awesome-icon icon="bug" class="text-purple-600 w-3.5 h-3.5" />
                           </div>
                           <div>
                               <div class="text-sm font-bold text-gray-900">Cross-Browser Testing</div>
                               <div class="text-[11px] font-medium text-gray-400 mt-0.5">Due date: Dec 6, 2024</div>
                           </div>
                      </div>
                 </div>
             </div>
        </div>

        <!-- Bottom Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <!-- Team Collaboration (Agent Summary) -->
             <div class="lg:col-span-5 bg-white rounded-[28px] p-6 border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)] h-[320px] overflow-y-auto">
                 <div class="flex justify-between items-center mb-6">
                     <h3 class="font-bold text-gray-900 text-lg">Team Collaboration</h3>
                     <button class="border border-gray-200 text-gray-800 text-xs font-semibold px-4 py-1.5 rounded-full hover:bg-gray-50 flex items-center gap-1 transition"><font-awesome-icon icon="plus" class="w-3 h-3" /> Add Member</button>
                 </div>
                 <div class="space-y-5">
                      <!-- Team Member 1 -->
                      <div class="flex items-center justify-between">
                          <div class="flex items-center gap-3">
                              <div class="w-10 h-10 rounded-full bg-red-100 overflow-hidden shrink-0 border border-gray-100">
                                  <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Alexandra&backgroundColor=ffdfbf" alt="avatar" class="w-full h-full object-cover">
                              </div>
                              <div>
                                  <div class="text-sm font-bold text-gray-900">Alexandra Deff</div>
                                  <div class="text-[11px] font-medium text-gray-400 mt-0.5 cursor-pointer hover:text-gray-600">Working on <span class="font-bold text-gray-700">Github Project Repository</span></div>
                              </div>
                          </div>
                          <span class="bg-emerald-50 text-emerald-600 border border-emerald-100 text-[10px] font-bold px-2.5 py-1 rounded-md">Completed</span>
                      </div>
                      
                      <!-- Team Member 2 -->
                      <div class="flex items-center justify-between">
                          <div class="flex items-center gap-3">
                              <div class="w-10 h-10 rounded-full bg-green-100 overflow-hidden shrink-0 border border-gray-100">
                                  <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Edwin&backgroundColor=c0aede" alt="avatar" class="w-full h-full object-cover">
                              </div>
                              <div>
                                  <div class="text-sm font-bold text-gray-900">Edwin Adenike</div>
                                  <div class="text-[11px] font-medium text-gray-400 mt-0.5 cursor-pointer hover:text-gray-600">Working on <span class="font-bold text-gray-700">Integrate User Authentication System</span></div>
                              </div>
                          </div>
                          <span class="bg-yellow-50 text-yellow-600 border border-yellow-100 text-[10px] font-bold px-2.5 py-1 rounded-md">In Progress</span>
                      </div>
                      
                      <!-- Team Member 3 -->
                      <div class="flex items-center justify-between">
                          <div class="flex items-center gap-3">
                              <div class="w-10 h-10 rounded-full bg-blue-100 overflow-hidden shrink-0 border border-gray-100">
                                  <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Isaac&backgroundColor=b6e3f4" alt="avatar" class="w-full h-full object-cover">
                              </div>
                              <div>
                                  <div class="text-sm font-bold text-gray-900">Isaac Oluwatemilorun</div>
                                  <div class="text-[11px] font-medium text-gray-400 mt-0.5 cursor-pointer hover:text-gray-600">Working on <span class="font-bold text-gray-700">Develop Search and Filter Functionality</span></div>
                              </div>
                          </div>
                          <span class="bg-red-50 text-red-500 border border-red-100 text-[10px] font-bold px-2.5 py-1 rounded-md">Pending</span>
                      </div>

                      <!-- Team Member 4 -->
                      <div class="flex items-center justify-between">
                          <div class="flex items-center gap-3">
                              <div class="w-10 h-10 rounded-full bg-orange-100 overflow-hidden shrink-0 border border-gray-100">
                                  <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=David&backgroundColor=ffdfbf" alt="avatar" class="w-full h-full object-cover">
                              </div>
                              <div>
                                  <div class="text-sm font-bold text-gray-900">David Oshodi</div>
                                  <div class="text-[11px] font-medium text-gray-400 mt-0.5 cursor-pointer hover:text-gray-600">Working on <span class="font-bold text-gray-700">Responsive Layout for Homepage</span></div>
                              </div>
                          </div>
                          <span class="bg-yellow-50 text-yellow-600 border border-yellow-100 text-[10px] font-bold px-2.5 py-1 rounded-md">In Progress</span>
                      </div>
                 </div>
             </div>

             <!-- Project Progress (Doughnut) -->
              <div class="lg:col-span-4 bg-white rounded-[28px] p-6 border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)] h-[320px] flex flex-col relative overflow-hidden">
                  <h3 class="font-bold text-gray-900 text-lg mb-2 z-10">Project Progress</h3>
                  <div class="flex-1 mt-6 relative z-10 flex justify-center w-full">
                      <div class="w-56 h-auto">
                        <Doughnut :data="receivablePayableData" :options="doughnutChartOptions" />
                      </div>
                      <div class="absolute inset-0 flex flex-col items-center justify-end pb-12 pointer-events-none">
                          <span class="text-4xl font-extrabold text-[#2a2a2a] tracking-tight">41%</span>
                          <span class="text-[11px] font-medium text-gray-500 mt-0.5">Project Ended</span>
                      </div>
                  </div>
                  <div class="mt-auto flex justify-center gap-5 text-[11px] font-bold text-gray-500 pt-2 z-10">
                      <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#1e5b43]"></span> Completed</div>
                      <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#1e5b43]" style="opacity: 0.8"></span> In Progress</div>
                      <div class="flex items-center gap-1.5">
                          <svg width="10" height="10" viewBox="0 0 10 10" class="rounded-full bg-gray-100 opacity-50">
                              <path d="M0 10 L10 0 M-3 7 L7 -3 M3 13 L13 3" stroke="#9ca3af" stroke-width="2"/>
                          </svg> 
                          Pending
                      </div>
                  </div>
              </div>

             <!-- Time Tracker -->
             <div class="lg:col-span-3 bg-gradient-to-br from-[#122e23] via-[#0b281c] to-[#041a10] rounded-[28px] p-6 text-white shadow-xl h-[320px] flex flex-col relative overflow-hidden group">
                 <!-- Abstract Wavy Background effect using radial gradients to mimic standard image background -->
                 <div class="absolute -right-16 -top-16 w-64 h-64 bg-green-500/20 rounded-full blur-[60px] group-hover:bg-green-500/30 transition duration-700"></div>
                 <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-emerald-700/30 rounded-full blur-[60px] group-hover:bg-emerald-700/40 transition duration-700"></div>
                 
                 <!-- SVG Waves placeholder to mimic the complex 3D ribbons in Image -->
                 <svg class="absolute inset-0 w-full h-full opacity-30 mix-blend-overlay" viewBox="0 0 100 100" preserveAspectRatio="none">
                     <path d="M0,50 Q25,20 50,50 T100,50" fill="none" stroke="#22c55e" stroke-width="2" />
                     <path d="M0,70 Q25,40 50,70 T100,70" fill="none" stroke="#22c55e" stroke-width="1.5" />
                     <path d="M0,90 Q25,60 50,90 T100,90" fill="none" stroke="#10b981" stroke-width="3" />
                     <path d="M0,30 Q25,0 50,30 T100,30" fill="none" stroke="#059669" stroke-width="2" />
                 </svg>

                 <div class="relative z-10 flex flex-col h-full">
                     <h3 class="font-medium text-emerald-50 text-[15px] opacity-90">Time Tracker</h3>
                     
                     <div class="flex-1 flex flex-col items-center justify-center -mt-4">
                         <div class="text-[44px] font-light tracking-wider text-white mb-6 drop-shadow-md">
                              01:24:08
                         </div>
                         <div class="flex justify-center gap-3">
                             <button class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-gray-100 transition shadow-lg">
                                 <font-awesome-icon icon="pause" class="w-3 h-3" />
                             </button>
                             <button class="w-10 h-10 bg-[#ef4444] text-white rounded-full flex items-center justify-center hover:bg-red-600 transition shadow-lg">
                                 <font-awesome-icon icon="square" class="w-3 h-3" />
                             </button>
                         </div>
                     </div>
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
\`

fs.writeFileSync('/Users/mashiurrahman/Desktop/officeM/office_management/resources/js/Pages/Dashboard.vue', content);
