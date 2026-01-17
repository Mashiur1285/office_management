<template>
    <Head title="BD Companies" />
    <div class="py-6 space-y-6">
        <div class="overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-800 text-white shadow-xl">
            <div class="px-6 py-8">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-100">Bangladeshi Companies</p>
                        <h1 class="text-2xl font-bold text-white">Processing Partners</h1>
                        <p class="text-sm text-blue-100">List of local companies receiving client documents.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a
                            :href="route('bd-companies.export', { type: 'excel' })"
                            class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:bg-green-700"
                        >
                            Export to Excel
                        </a>
                        <a
                            :href="route('bd-companies.export', { type: 'pdf' })"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:bg-red-700"
                        >
                            Export to PDF
                        </a>
                        <Link
                            href="/bd-companies/create"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-blue-700 shadow-lg transition hover:shadow-xl hover:scale-105"
                        >
                            <span class="text-lg leading-none">+</span>
                            Add Company
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
                            placeholder="Search by name, owner, contact person, or job category..."
                        />
                        <button
                            v-if="searchQuery"
                            @click="searchQuery = ''"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div v-if="searchQuery" class="text-sm text-gray-600">
                    Showing <span class="font-semibold text-gray-900">{{ filteredCompanies.length }}</span> of {{ companies.length }} companies
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-700">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Name</th>
                            <th class="px-6 py-3 font-semibold">Job Categories</th>
                            <th class="px-6 py-3 font-semibold">Owner</th>
                            <th class="px-6 py-3 font-semibold">Contact Person</th>
                            <th class="px-6 py-3 font-semibold">Per Client Fee</th>
                            <th class="px-6 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="company in filteredCompanies" :key="company.id" class="transition hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ company.name }}</div>
                                <p class="text-xs text-gray-500">{{ company.office_address || "—" }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ company.job_categories || "—" }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ company.owner_name || "—" }}</p>
                                <p class="text-xs text-gray-500">{{ company.owner_phone || "" }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ company.contact_person_name || "—" }}</p>
                                <p class="text-xs text-gray-500">{{ company.contact_person_phone || "" }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ money(company.per_client_fee) }}
                                </p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <IconButton
                                        icon="fa-solid fa-eye"
                                        class="bg-gray-100 text-gray-700 hover:bg-gray-200"
                                        tooltip="View company"
                                        @click="router.visit(`/bd-companies/${company.id}`)"
                                    />
                                    <IconButton
                                        icon="fa-solid fa-pen-to-square"
                                        class="bg-blue-600 text-white hover:bg-blue-700"
                                        tooltip="Edit company"
                                        @click="router.visit(`/bd-companies/${company.id}/edit`)"
                                    />
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredCompanies.length === 0 && companies.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                No companies yet. <Link href="/bd-companies/create" class="text-blue-600 font-semibold hover:underline">Add the first one</Link>
                            </td>
                        </tr>
                        <tr v-if="filteredCompanies.length === 0 && companies.length > 0">
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                No companies found matching your search.
                                <button @click="searchQuery = ''" class="text-blue-600 font-semibold hover:underline ml-1">Clear search</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import IconButton from "@/Components/Buttons/IconButton.vue";

const props = defineProps({
    companies: {
        type: Array,
        default: () => [],
    },
});

const companies = props.companies || [];
const searchQuery = ref("");

const filteredCompanies = computed(() => {
    if (!searchQuery.value) return companies;

    const query = searchQuery.value.toLowerCase();
    return companies.filter((company) => {
        return (
            company.name?.toLowerCase().includes(query) ||
            company.owner_name?.toLowerCase().includes(query) ||
            company.owner_phone?.toLowerCase().includes(query) ||
            company.contact_person_name?.toLowerCase().includes(query) ||
            company.contact_person_phone?.toLowerCase().includes(query) ||
            company.job_categories?.toLowerCase().includes(query) ||
            company.office_address?.toLowerCase().includes(query)
        );
    });
});

const money = (value) => {
    if (value === null || value === undefined || value === "") return "—";
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(Number(value || 0));
};
</script>
