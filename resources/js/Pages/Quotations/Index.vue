<template>
    <Head title="Quotations" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Quotations</h1>
                <p class="text-sm text-gray-500 mt-1">Create and manage client quotations.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative w-full sm:w-80">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search quotations..."
                        class="w-full rounded-full border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-900 transition-colors focus:border-[#1d4ed8] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1d4ed8]/20"
                    />
                    <svg
                        class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
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
                <!-- Export Excel -->
                <a
                    :href="route('quotations.export', { type: 'excel', search: searchQuery })"
                    class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors whitespace-nowrap"
                >
                    <font-awesome-icon icon="file-excel" class="text-blue-600" />
                    Excel
                </a>
                <!-- Export PDF -->
                <a
                    :href="route('quotations.export', { type: 'pdf', search: searchQuery })"
                    class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors whitespace-nowrap"
                >
                    <font-awesome-icon icon="file-pdf" class="text-red-500" />
                    PDF
                </a>
                <Link
                    :href="route('quotations.create')"
                    class="bg-[#1d4ed8] hover:bg-[#154130] text-white px-4 py-2 rounded-full text-sm font-medium transition-colors shadow-sm flex items-center gap-2 whitespace-nowrap"
                >
                    <font-awesome-icon icon="plus" />
                    New Quotation
                </Link>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Quotation No</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Client</th>
                            <th class="px-4 py-3">Service</th>
                            <th class="px-4 py-3">Maker</th>
                            <th class="px-4 py-3">Created By</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="quotation in quotations" :key="quotation.id">
                            <td class="px-4 py-3 font-semibold text-gray-900 whitespace-nowrap">
                                {{ quotation.quotation_no }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ quotation.quotation_date }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ quotation.client_name }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ formatService(quotation.service_category, quotation.service_type) }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ quotation.quotation_maker || '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ quotation.created_by || '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <IconButton
                                        v-if="canEdit"
                                        icon="fa-solid fa-pen-to-square"
                                        class="bg-blue-50 text-blue-600 hover:bg-blue-100"
                                        tooltip="Edit quotation"
                                        @click="router.visit(route('quotations.edit', quotation.id))"
                                    />
                                    <IconButton
                                        icon="fa-solid fa-eye"
                                        class="bg-gray-100 text-gray-600 hover:bg-gray-200"
                                        tooltip="View quotation"
                                        @click="router.visit(route('quotations.show', quotation.id))"
                                    />
                                    <a
                                        :href="route('quotations.download', quotation.id)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition duration-200 bg-gray-100 text-gray-600 hover:bg-gray-200"
                                        title="Download PDF"
                                    >
                                        <font-awesome-icon icon="download" class="text-sm" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!quotations.length">
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                No quotations found yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import IconButton from '@/Components/Buttons/IconButton.vue';

const props = defineProps({
    quotations: {
        type: Array,
        default: () => [],
    },
    filters: Object,
});

const page = usePage();
const canEdit = computed(() => {
    const perms = page.props.userPermissions || [];
    return perms.includes('quotation.update') || perms.includes('quotation.*') || perms.includes('*') || perms.includes('superadmin');
});


const searchQuery = ref(props.filters?.search || '');

// Debounce search to avoid too many requests
let searchTimeout = null;
watch(searchQuery, (value) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(() => {
        router.get(
            route('quotations.index'),
            { search: value || undefined },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    }, 300);
});

const categoryMap = {
    travel_tourism: 'Travel & Tourism',
    manpower_exporting: 'Manpower Exporting',
    student_package: 'Student Package',
    other_income: 'Other Income',
};

const formatService = (category, type) => {
    const categoryName = categoryMap[category] || category;
    return type ? `${categoryName} • ${type}` : categoryName;
};
</script>
