<template>
    <Head title="Refund Report" />
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-rose-50/30 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-800 text-white shadow-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white">Refund Report</h1>
                        <p class="text-sm text-blue-100 mt-1">Track all refunds issued to clients and agents</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a
                            :href="`/reports/refund-report/export?${exportParams}`"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm flex items-center gap-2"
                        >
                            <i class="fa-solid fa-file-excel"></i>
                            Export to Excel
                        </a>
                        <a
                            :href="`/reports/refund-report/export?type=pdf&${exportParams}`"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-sm flex items-center gap-2"
                        >
                            <i class="fa-solid fa-file-pdf"></i>
                            Export to PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Summary Card -->
        <div
            class="rounded-2xl border border-rose-100 bg-rose-50 p-6 shadow-sm"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-rose-600">
                        Total Refund Amount
                    </p>
                    <p class="text-3xl font-bold text-rose-700">
                        {{ money(totalRefund) }}
                    </p>
                </div>
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-full bg-rose-100"
                >
                    <svg
                        class="h-7 w-7 text-rose-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"
                        />
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-sm text-rose-600">
                {{ refundsData.length }} refund{{
                    refundsData.length !== 1 ? "s" : ""
                }}
                recorded
            </p>
        </div>

        <!-- Filters Section -->
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
                <div class="flex-1 max-w-md">
                    <label
                        class="block text-xs font-semibold text-gray-600 mb-1"
                        >Search</label
                    >
                    <div class="relative">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                        >
                            <svg
                                class="h-5 w-5 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500 focus:bg-white transition"
                            placeholder="Search by client, agent, notes..."
                        />
                        <button
                            v-if="searchQuery"
                            @click="searchQuery = ''"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="w-40">
                    <label
                        class="block text-xs font-semibold text-gray-600 mb-1"
                        >Start Date</label
                    >
                    <input
                        v-model="startDate"
                        type="date"
                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500"
                    />
                </div>
                <div class="w-40">
                    <label
                        class="block text-xs font-semibold text-gray-600 mb-1"
                        >End Date</label
                    >
                    <input
                        v-model="endDate"
                        type="date"
                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500"
                    />
                </div>
                <button
                    @click="applyFilters"
                    class="rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700"
                >
                    Apply
                </button>
                <button
                    v-if="hasFilters"
                    @click="clearFilters"
                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Clear
                </button>
            </div>
        </div>

        <!-- Data Table -->
        <div
            class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-700">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Date</th>
                            <th class="px-6 py-3 font-semibold">Refunded To</th>
                            <th class="px-6 py-3 font-semibold">Source</th>
                            <th class="px-6 py-3 font-semibold text-right">
                                Amount
                            </th>
                            <th class="px-6 py-3 font-semibold">Notes</th>
                            <th class="px-6 py-3 font-semibold">Created By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="refund in filteredRefunds"
                            :key="refund.id"
                            class="transition hover:bg-gray-50"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ refund.payment_date || "—" }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">
                                    {{ refund.refunded_to || "—" }}
                                </div>
                                <span
                                    :class="[
                                        'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium mt-1',
                                        refund.refunded_to_type === 'Agent'
                                            ? 'bg-purple-100 text-purple-800'
                                            : 'bg-blue-100 text-blue-800',
                                    ]"
                                >
                                    {{ refund.refunded_to_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="[
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        refund.source === 'agent'
                                            ? 'bg-purple-100 text-purple-800'
                                            : 'bg-blue-100 text-blue-800',
                                    ]"
                                >
                                    {{
                                        refund.source === "agent"
                                            ? "Agent"
                                            : "Client"
                                    }}
                                </span>
                                <p
                                    v-if="refund.agent_name"
                                    class="text-xs text-gray-500 mt-1"
                                >
                                    {{ refund.agent_name }}
                                </p>
                            </td>
                            <td
                                class="px-6 py-4 text-right font-semibold text-rose-600"
                            >
                                {{ money(refund.amount) }}
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate">
                                {{ refund.notes || "—" }}
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ refund.created_by || "—" }}
                            </td>
                        </tr>
                        <tr
                            v-if="
                                filteredRefunds.length === 0 &&
                                refundsData.length === 0
                            "
                        >
                            <td
                                colspan="6"
                                class="px-6 py-8 text-center text-sm text-gray-500"
                            >
                                No refunds recorded yet.
                            </td>
                        </tr>
                        <tr v-else-if="filteredRefunds.length === 0">
                            <td
                                colspan="6"
                                class="px-6 py-8 text-center text-sm text-gray-500"
                            >
                                No refunds found matching your search.
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="filteredRefunds.length > 0" class="bg-gray-50">
                        <tr>
                            <td
                                colspan="3"
                                class="px-6 py-3 text-right font-semibold text-gray-700"
                            >
                                Total:
                            </td>
                            <td
                                class="px-6 py-3 text-right font-bold text-rose-600"
                            >
                                {{ money(filteredTotal) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end"
        >
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600">Show:</span>
                <select
                    v-model="perPage"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white text-gray-800 w-20"
                    @change="onPerPageChange"
                >
                    <option :value="20">20</option>
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white text-gray-800 disabled:bg-gray-100 disabled:text-gray-400"
                    :disabled="currentPage <= 1"
                    @click="changePage(currentPage - 1)"
                >
                    &lt; Prev
                </button>
                <span
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white text-gray-800 font-medium"
                >
                    {{ currentPage }} / {{ totalPages }}
                </span>
                <button
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white text-gray-800 disabled:bg-gray-100 disabled:text-gray-400"
                    :disabled="currentPage >= totalPages"
                    @click="changePage(currentPage + 1)"
                >
                    Next &gt;
                </button>
            </div>
        </div>
        </div>
    </div>
</template>

<script setup>
import { Head, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
    refunds: Object,
    totalRefund: Number,
    filters: Object,
});

const searchQuery = ref("");
const startDate = ref(props.filters?.start_date || "");
const endDate = ref(props.filters?.end_date || "");
const refundsData = computed(() => props.refunds?.data || []);
const totalPages = computed(() => props.refunds?.last_page || 1);
const currentPage = ref(
    Number(new URL(window.location.href).searchParams.get("page") ?? 1),
);
const perPage = ref(
    Number(new URL(window.location.href).searchParams.get("per_page") ?? 20),
);

const filteredRefunds = computed(() => {
    if (!searchQuery.value) return refundsData.value;

    const query = searchQuery.value.toLowerCase();
    return refundsData.value.filter((refund) => {
        return (
            refund.client_name?.toLowerCase().includes(query) ||
            refund.client_phone?.toLowerCase().includes(query) ||
            refund.refunded_to?.toLowerCase().includes(query) ||
            refund.agent_name?.toLowerCase().includes(query) ||
            refund.payment_method?.toLowerCase().includes(query) ||
            refund.notes?.toLowerCase().includes(query) ||
            refund.created_by?.toLowerCase().includes(query)
        );
    });
});

const filteredTotal = computed(() => {
    return filteredRefunds.value.reduce((sum, r) => sum + r.amount, 0);
});

const hasFilters = computed(() => {
    return startDate.value || endDate.value;
});

const exportParams = computed(() => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.append("search", searchQuery.value);
    if (startDate.value) params.append("start_date", startDate.value);
    if (endDate.value) params.append("end_date", endDate.value);
    if (perPage.value) params.append("per_page", perPage.value);
    return params.toString();
});

const applyFilters = () => {
    router.get(
        "/reports/refund-report",
        {
            start_date: startDate.value || undefined,
            end_date: endDate.value || undefined,
            per_page: perPage.value,
            page: 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const clearFilters = () => {
    startDate.value = "";
    endDate.value = "";
    router.get(
        "/reports/refund-report",
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const changePage = (page) => {
    currentPage.value = page;
    router.get(
        "/reports/refund-report",
        {
            search: searchQuery.value || undefined,
            start_date: startDate.value || undefined,
            end_date: endDate.value || undefined,
            per_page: perPage.value,
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const onPerPageChange = () => {
    currentPage.value = 1;
    router.get(
        "/reports/refund-report",
        {
            search: searchQuery.value || undefined,
            start_date: startDate.value || undefined,
            end_date: endDate.value || undefined,
            per_page: perPage.value,
            page: 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const money = (value) => {
    if (value === null || value === undefined) return "৳0.00";
    return (
        "৳" +
        new Intl.NumberFormat("en-BD", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(value)
    );
};
</script>
