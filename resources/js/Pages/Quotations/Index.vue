<template>
    <Head title="Quotations" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Quotations</h1>
                <p class="text-sm text-gray-600">
                    Create and manage client quotations.
                </p>
            </div>
            <Link
                :href="route('quotations.create')"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
            >
                <font-awesome-icon icon="file-invoice" />
                New Quotation
            </Link>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
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
                            <td class="px-4 py-3 font-semibold text-gray-900">
                                {{ quotation.quotation_no }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
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
                                <div class="flex items-center justify-end gap-3">
                                    <Link
                                        :href="route('quotations.show', quotation.id)"
                                        class="text-blue-600 hover:text-blue-700"
                                    >
                                        View
                                    </Link>
                                    <a
                                        :href="route('quotations.download', quotation.id)"
                                        class="text-green-600 hover:text-green-700"
                                    >
                                        Download PDF
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
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    quotations: {
        type: Array,
        default: () => [],
    },
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
