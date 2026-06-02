<template>
    <Head title="Clients" />

    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans">
        
        <!-- Main Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">Clients</h1>
                <p class="text-sm text-gray-500">Track client documents, visa stages, deadlines, and payments</p>
            </div>
            <div class="flex items-center gap-3">
                <a
                    :href="route('clients.export', { type: 'excel', bd_company_status: props.filters?.bd_company_status, bd_company_scope: props.filters?.bd_company_scope ? 1 : 0, agency_scope: props.filters?.agency_scope ? 1 : 0, foreign_country: props.filters?.foreign_country })"
                    class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2"
                >
                    <font-awesome-icon icon="file-excel" class="w-3.5 h-3.5 text-blue-600" />
                    Excel
                </a>
                <a
                    :href="route('clients.export', { type: 'pdf', bd_company_status: props.filters?.bd_company_status, bd_company_scope: props.filters?.bd_company_scope ? 1 : 0, agency_scope: props.filters?.agency_scope ? 1 : 0, foreign_country: props.filters?.foreign_country })"
                    class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2"
                >
                    <font-awesome-icon icon="file-pdf" class="w-3.5 h-3.5 text-red-600" />
                    PDF
                </a>
                <Link
                    v-if="!props.readOnly"
                    href="/clients/create"
                    class="bg-[#1d4ed8] text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-[#1e40af] transition flex items-center gap-2 shadow-sm"
                >
                    <font-awesome-icon icon="plus" class="w-3.5 h-3.5" />
                    Add Client
                </Link>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="bg-gradient-to-br from-[#1d4ed8] to-[#1e3a8a] rounded-[24px] p-6 text-white relative shadow-md overflow-hidden">
                <div class="flex justify-between items-start mb-4 relative z-10">
                    <h3 class="font-medium text-blue-50 text-[15px]">Total Clients</h3>
                    <div class="w-8 h-8 rounded-full border border-blue-300/30 flex items-center justify-center bg-white/10 backdrop-blur-sm -mr-1 -mt-1 group-hover:bg-white/20 transition-colors">
                        <font-awesome-icon icon="users" class="w-3 h-3 text-white" />
                    </div>
                </div>
                <div class="text-[52px] font-bold mb-5 tracking-tight leading-none relative z-10">{{ filteredClients.length }}</div>
                <div class="flex items-center gap-2 text-xs text-blue-100 font-medium relative z-10">
                    <span>All managed clients</span>
                </div>
            </div>

            <div class="bg-white rounded-[24px] p-6 text-gray-900 border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] relative">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="font-medium text-gray-800 text-[15px]">Processing</h3>
                    <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center bg-white -mr-1 -mt-1">
                        <font-awesome-icon icon="spinner" class="w-3 h-3 text-amber-500" />
                    </div>
                </div>
                <div class="text-[52px] font-bold mb-5 tracking-tight leading-none">{{ stats.processing }}</div>
                <div class="flex items-center gap-2 text-xs text-gray-500 font-medium">
                    <span>Active processing files</span>
                </div>
            </div>

            <div class="bg-white rounded-[24px] p-6 text-gray-900 border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] relative">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="font-medium text-gray-800 text-[15px]">Completed</h3>
                    <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center bg-white -mr-1 -mt-1">
                        <font-awesome-icon icon="check" class="w-3 h-3 text-blue-500" />
                    </div>
                </div>
                <div class="text-[52px] font-bold mb-5 tracking-tight leading-none">{{ stats.completed }}</div>
                <div class="flex items-center gap-2 text-xs text-gray-500 font-medium">
                    <span>Successfully finished</span>
                </div>
            </div>

            <div class="bg-white rounded-[24px] p-6 text-gray-900 border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)] relative">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="font-medium text-gray-800 text-[15px]">Rejected</h3>
                    <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center bg-white -mr-1 -mt-1">
                        <font-awesome-icon icon="times" class="w-3 h-3 text-red-500" />
                    </div>
                </div>
                <div class="text-[52px] font-bold mb-5 tracking-tight leading-none">{{ stats.rejected }}</div>
                <div class="flex items-center gap-2 text-xs text-gray-500 font-medium">
                    <span>Requires attention</span>
                </div>
            </div>
        </div>

        <!-- Success Alert -->
        <div
            v-if="flash.success"
            class="mb-6 flex items-center gap-3 rounded-[16px] border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-800 shadow-sm animate-pulse"
            role="alert"
        >
            <font-awesome-icon icon="check-circle" class="w-5 h-5" />
            <div class="flex-1">
                <span class="font-bold">Success:</span>
                <span class="ml-1">{{ flash.success }}</span>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white rounded-[24px] border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)] p-5 mb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-gray-400 bg-gray-50/80 rounded-full px-4 py-2.5 w-full md:w-96 border border-gray-100">
                <font-awesome-icon icon="magnifying-glass" class="w-4 h-4" />
                <input
                    v-model="searchQuery"
                    type="text"
                    class="border-none bg-transparent focus:ring-0 text-sm w-full text-gray-700 placeholder-gray-400 outline-none"
                    placeholder="Search by name, passport, NID..."
                />
                <button
                    v-if="searchQuery"
                    @click="searchQuery = ''"
                    class="text-gray-400 hover:text-gray-600 outline-none"
                >
                    <font-awesome-icon icon="times" class="w-4 h-4" />
                </button>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mr-1">Filter</span>
                <button
                    @click="selectedStage = 'all'"
                    :class="[
                        'px-4 py-2 text-[13px] font-bold rounded-full transition-all duration-200',
                        selectedStage === 'all'
                            ? 'bg-[#1d4ed8] text-white shadow-md'
                            : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50',
                    ]"
                >
                    All
                </button>
                <button
                    v-for="stage in statusFilters"
                    :key="stage.value"
                    @click="selectedStage = stage.value"
                    :class="[
                        'px-4 py-2 text-[13px] font-bold rounded-full transition-all duration-200',
                        selectedStage === stage.value
                            ? 'bg-[#1d4ed8] text-white shadow-md'
                            : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50',
                    ]"
                >
                    {{ stage.label }}
                </button>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-[24px] border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead class="bg-gray-50/50 text-[11px] uppercase text-gray-400 font-bold tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 whitespace-nowrap">Client Info</th>
                            <th class="px-6 py-4 whitespace-nowrap">Agent</th>
                            <th class="px-6 py-4 whitespace-nowrap">Status</th>
                            <th class="px-6 py-4 whitespace-nowrap">Passport</th>
                            <th class="px-6 py-4 whitespace-nowrap">Due Amount</th>
                            <th class="px-6 py-4 whitespace-nowrap">VAT Receivable</th>
                            <th class="px-6 py-4 text-right whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr
                            v-for="client in paginatedClients"
                            :key="client.id"
                            class="transition-colors hover:bg-gray-50/50 group"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold shrink-0 bg-[#f0f9ff] text-blue-600 border border-blue-100 relative">
                                        <img
                                            v-if="client.photo_url"
                                            :src="client.photo_url"
                                            class="w-full h-full rounded-full object-cover"
                                        />
                                        <span v-else>{{ initials(client.name) }}</span>
                                        <div class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full bg-blue-500 border-2 border-white"></div>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900 text-[14px]">{{ client.name }}</span>
                                        <span class="text-[12px] font-medium text-gray-400">{{ client.nid_number || client.mobile || 'No NID/Phone' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-gray-700">{{ client.agent_name || "—" }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-[11px] font-bold whitespace-nowrap border"
                                    :class="getBadgeStyles(client.status_value)"
                                >
                                    {{ client.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-gray-700">{{ client.passport_number || "—" }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 text-[14px]">{{ money(client.current_due) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="font-bold text-gray-900 text-[14px]">{{ money(client.vat_unpaid) }}</div>
                                    <div v-if="client.vat_paid" class="text-blue-500" title="VAT Paid">
                                        <font-awesome-icon icon="check-circle" class="w-4 h-4" />
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right border-l border-transparent group-hover:border-gray-100">
                                <div class="flex items-center justify-end gap-1.5 transition-opacity">
                                    <button @click="router.visit(props.readOnly ? `/database/clients/${client.id}` : `/clients/${client.id}`)" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-[#1d4ed8] hover:bg-blue-50 transition" title="View">
                                        <font-awesome-icon icon="eye" class="w-3.5 h-3.5" />
                                    </button>
                                    <button v-if="!props.readOnly" @click="router.visit(`/clients/${client.id}/documents`)" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition" title="Documents">
                                        <font-awesome-icon icon="file-alt" class="w-3.5 h-3.5" />
                                    </button>
                                    <button v-if="!props.readOnly" @click="router.visit(`/clients/${client.id}/edit`)" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-amber-500 hover:bg-amber-50 transition" title="Edit">
                                        <font-awesome-icon icon="edit" class="w-3.5 h-3.5" />
                                    </button>
                                    <button v-if="!props.readOnly" @click="confirmDelete(client)" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition" title="Delete">
                                        <font-awesome-icon icon="trash" class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty States -->
                        <tr v-if="filteredClients.length === 0 && clients.length === 0">
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4 border border-gray-100">
                                    <font-awesome-icon icon="users" class="w-6 h-6 text-gray-300" />
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">No clients yet</h3>
                                <p class="text-sm text-gray-500 mb-5">Get started by adding your first client</p>
                                <Link
                                    v-if="!props.readOnly"
                                    href="/clients/create"
                                    class="bg-[#1d4ed8] text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-[#1e40af] transition inline-flex items-center gap-2"
                                >
                                    <font-awesome-icon icon="plus" class="w-3.5 h-3.5" />
                                    Add First Client
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="filteredClients.length === 0 && clients.length > 0">
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4 border border-gray-100">
                                    <font-awesome-icon icon="search" class="w-6 h-6 text-gray-300" />
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">No results found</h3>
                                <p class="text-sm text-gray-500 mb-5">Try adjusting your filters to find what you're looking for</p>
                                <button
                                    @click="clearFilters"
                                    class="border border-gray-200 text-gray-700 bg-white px-5 py-2 hover:bg-gray-50 rounded-full font-semibold text-sm transition"
                                >
                                    Clear filters
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="border-t border-gray-100 px-6 py-4 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3 text-sm font-medium text-gray-500">
                    <span>Rows per page:</span>
                    <div class="flex items-center gap-1">
                        <button
                            v-for="n in [25, 50, 75, 100]"
                            :key="n"
                            @click="perPage = n"
                            :class="[
                                'px-2.5 py-1 rounded-md text-[13px] font-bold transition-colors',
                                perPage === n
                                    ? 'bg-[#1d4ed8] text-white'
                                    : 'text-gray-500 hover:bg-gray-200',
                            ]"
                        >
                            {{ n }}
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[13px] font-semibold text-gray-600 mr-2">
                        {{ (currentPage - 1) * perPage + 1 }}-{{ Math.min(currentPage * perPage, filteredClients.length) }} of {{ filteredClients.length }}
                    </span>
                    <button
                        @click="currentPage = 1"
                        :disabled="currentPage === 1"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-white hover:shadow-sm hover:text-gray-700 disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:shadow-none transition"
                    >
                        <font-awesome-icon icon="angle-double-left" class="w-3.5 h-3.5" />
                    </button>
                    <button
                        @click="currentPage--"
                        :disabled="currentPage === 1"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-white hover:shadow-sm hover:text-gray-700 disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:shadow-none transition"
                    >
                        <font-awesome-icon icon="angle-left" class="w-3.5 h-3.5" />
                    </button>
                    <button
                        @click="currentPage++"
                        :disabled="currentPage === totalPages"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-white hover:shadow-sm hover:text-gray-700 disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:shadow-none transition"
                    >
                        <font-awesome-icon icon="angle-right" class="w-3.5 h-3.5" />
                    </button>
                    <button
                        @click="currentPage = totalPages"
                        :disabled="currentPage === totalPages"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-white hover:shadow-sm hover:text-gray-700 disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:shadow-none transition"
                    >
                        <font-awesome-icon icon="angle-double-right" class="w-3.5 h-3.5" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal (Match Dashboard style) -->
    <Teleport to="body">
        <div v-if="deleteModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" @click="deleteModal.show = false"></div>
            
            <div class="relative w-full max-w-sm rounded-[24px] bg-white shadow-2xl p-6 text-center animate-fade-in-up">
                <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4 border border-red-100">
                    <font-awesome-icon icon="exclamation-triangle" class="w-7 h-7 text-red-500" />
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-1">Delete Client</h3>
                <p class="text-sm font-medium text-gray-500 mb-1">You are about to delete</p>
                <p class="text-[15px] font-bold text-gray-800 mb-4 px-2 truncate">"{{ deleteModal.clientName }}"</p>
                <p class="text-xs font-semibold tracking-wide uppercase text-red-500 mb-6 px-4 bg-red-50 py-2 rounded-lg inline-block">Cannot be undone</p>
                
                <div class="flex gap-3">
                    <button
                        @click="deleteModal.show = false"
                        class="flex-1 rounded-full border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 transition"
                    >
                        Cancel
                    </button>
                    <button
                        @click="doDelete"
                        class="flex-1 rounded-full bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700 transition shadow-[0_4px_12px_rgba(220,38,38,0.25)]"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import IconButton from "@/Components/Buttons/IconButton.vue";

const props = defineProps({
    clients: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    readOnly: {
        type: Boolean,
        default: false,
    },
});

const flash = computed(() => usePage().props.flash || {});

// Search and filter state
const searchQuery = ref("");
const selectedStage = ref("all");
const perPage = ref(25);
const currentPage = ref(1);

watch([searchQuery, selectedStage, perPage], () => {
    currentPage.value = 1;
});

const statusFilters = [
    { value: "pending", label: "Pending" },
    { value: "company_processing", label: "Company Processing" },
    { value: "completed", label: "Completed" },
    { value: "rejected", label: "Rejected" },
];

// Filtered clients based on search and stage
const filteredClients = computed(() => {
    let result = props.clients || [];

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter((client) => {
            const mobile = client.mobile
                ? String(client.mobile).toLowerCase()
                : "";
            return (
                client.name?.toLowerCase().includes(query) ||
                client.passport_number?.toLowerCase().includes(query) ||
                client.nid_number?.toLowerCase().includes(query) ||
                mobile.includes(query)
            );
        });
    }

    // Stage filter
    if (selectedStage.value !== "all") {
        result = result.filter(
            (client) => client.status_value === selectedStage.value
        );
    }

    return result;
});

const totalPages = computed(() => Math.ceil(filteredClients.value.length / perPage.value) || 1);

const paginatedClients = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filteredClients.value.slice(start, start + perPage.value);
});

// Statistics
const stats = computed(() => ({
    processing: (props.clients || []).filter(
        (c) => c.status_value === "company_processing"
    ).length,
    completed: (props.clients || []).filter(
        (c) => c.status_value === "completed"
    ).length,
    rejected: (props.clients || []).filter((c) => c.status_value === "rejected")
        .length,
    pending: (props.clients || []).filter((c) => c.status_value === "pending")
        .length,
}));

const initials = (name = "") => {
    return name
        .split(" ")
        .map((chunk) => chunk.charAt(0))
        .join("")
        .slice(0, 2)
        .toUpperCase();
};

const getBadgeStyles = (statusValue) => {
    const map = {
        pending: "bg-gray-50 text-gray-600 border-gray-200",
        company_processing: "bg-amber-50 text-amber-600 border-amber-200",
        completed: "bg-blue-50 text-[#1d4ed8] border-blue-200",
        rejected: "bg-red-50 text-red-600 border-red-200",
    };
    return map[statusValue] || "bg-gray-50 text-gray-600 border-gray-200";
};

const money = (value) => {
    if (value === null || value === undefined) return "—";
    return (
        "৳" +
        new Intl.NumberFormat("en-BD", {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(Number(value || 0))
    );
};

const clearFilters = () => {
    searchQuery.value = "";
    selectedStage.value = "all";
};

const deleteModal = ref({ show: false, clientId: null, clientName: "" });

const confirmDelete = (client) => {
    deleteModal.value = { show: true, clientId: client.id, clientName: client.name };
};

const doDelete = () => {
    router.delete(`/clients/${deleteModal.value.clientId}`, {
        preserveScroll: true,
        onFinish: () => { deleteModal.value.show = false; },
    });
};

if (
    props.filters?.bd_company_status ||
    props.filters?.bd_company_scope ||
    props.filters?.agency_scope
) {
    selectedStage.value = "all";
}

const exportData = () => {
    // Create CSV content
    const headers = [
        "Name",
        "NID",
        "Passport",
        "Job Sector",
        "Agent",
        "Status",
        "Vendor",
        "Foreign Company",
        "Due Amount",
    ];
    const rows = filteredClients.value.map((client) => [
        client.name,
        client.nid_number,
        client.passport_number,
        client.job_sector || "",
        client.agent_name || "",
        client.status,
        client.bd_company || "",
        client.foreign_company || "",
        client.current_due || "0",
    ]);

    const csvContent = [
        headers.join(","),
        ...rows.map((row) => row.map((cell) => `"${cell}"`).join(",")),
    ].join("\n");

    // Download
    const blob = new Blob([csvContent], { type: "text/csv" });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `clients-export-${new Date().toISOString().split("T")[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
};
</script>
