<template>
    <Head title="Invoices" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
                <p class="text-sm text-gray-600">Create and track client invoices.</p>
            </div>
            <Link
                v-if="canAdd"
                :href="route('invoices.create')"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
            >
                New Invoice
            </Link>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Invoice No</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Client</th>
                            <th class="px-4 py-3">Service</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-right">Paid</th>
                            <th class="px-4 py-3 text-right">Due</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ invoice.invoice_no }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ invoice.invoice_date }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ invoice.client_name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ formatService(invoice.service_category, invoice.service_type) }}</td>
                            <td class="px-4 py-3 text-right text-gray-900">{{ money(invoice.total_amount) }}</td>
                            <td class="px-4 py-3 text-right text-green-700">{{ money(invoice.paid_amount) }}</td>
                            <td class="px-4 py-3 text-right text-orange-700">{{ money(invoice.due_amount) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="statusClass(invoice.payment_status)" class="px-2 py-1 text-xs font-semibold rounded-full capitalize">
                                    {{ invoice.payment_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <Link
                                    :href="route('invoices.show', invoice.id)"
                                    class="text-blue-600 hover:text-blue-700 text-sm font-semibold"
                                >
                                    View
                                </Link>
                                <a
                                    v-if="invoice.pdf_path"
                                    :href="route('invoices.download', invoice.id)"
                                    class="text-gray-600 hover:text-gray-800 text-sm font-semibold"
                                >
                                    Download
                                </a>
                            </td>
                        </tr>
                        <tr v-if="invoices.length === 0">
                            <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500">
                                No invoices found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    invoices: Array,
});

const page = usePage();
const canAdd = computed(() => {
    const perms = page.props.userPermissions || [];
    return perms.includes('invoice.add') || perms.includes('invoice.*') || perms.includes('*') || perms.includes('superadmin');
});

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
};

const statusClass = (status) => {
    if (status === 'paid') return 'bg-green-100 text-green-800';
    if (status === 'partial') return 'bg-yellow-100 text-yellow-800';
    return 'bg-orange-100 text-orange-800';
};

const formatService = (category, type) => {
    const map = {
        travel_tourism: 'Travel & Tourism',
        manpower_exporting: 'Manpower Exporting',
        student_package: 'Student Package',
        other_income: 'Other Income',
    };
    const categoryLabel = map[category] || category;
    return type ? `${categoryLabel} • ${type}` : categoryLabel;
};
</script>
