<template>
    <div class="py-6 space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-500">Bangladeshi Companies</p>
                <h1 class="text-2xl font-bold text-gray-900">Processing Partners</h1>
                <p class="text-sm text-gray-600">List of local companies receiving client documents.</p>
            </div>
            <Link
                href="/bd-companies/create"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700"
            >
                <span class="text-lg leading-none">+</span>
                Add Company
            </Link>
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
                        <tr v-for="company in companies" :key="company.id" class="transition hover:bg-gray-50">
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
                                <IconButton
                                    icon="fa-solid fa-pen-to-square"
                                    class="bg-blue-600 text-white hover:bg-blue-700"
                                    tooltip="Edit company"
                                    @click="router.visit(`/bd-companies/${company.id}/edit`)"
                                />
                            </td>
                        </tr>
                        <tr v-if="companies.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                No companies yet. <Link href="/bd-companies/create" class="text-blue-600 font-semibold hover:underline">Add the first one</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, router } from "@inertiajs/vue3";
import IconButton from "@/Components/Buttons/IconButton.vue";

const props = defineProps({
    companies: {
        type: Array,
        default: () => [],
    },
});

const money = (value) => {
    if (value === null || value === undefined || value === "") return "—";
    return new Intl.NumberFormat("en-US", { style: "currency", currency: "BDT" }).format(Number(value || 0));
};

const companies = props.companies || [];
</script>
