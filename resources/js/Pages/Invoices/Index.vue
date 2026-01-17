<template>
    <Head title="Invoices" />

    <div class="space-y-6">
        <div class="overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-800 text-white shadow-xl">
            <div class="px-6 py-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Invoices</h1>
                        <p class="text-sm text-blue-100">Create and track client invoices.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search invoices..."
                                class="w-full sm:w-80 rounded-xl border border-white/30 bg-white px-4 py-2.5 pl-10 text-sm text-gray-900 shadow-sm focus:border-white focus:outline-none focus:ring-2 focus:ring-white/40"
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
                        <a
                            :href="route('invoices.export', { type: 'excel', search: searchQuery })"
                            class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:bg-green-700 whitespace-nowrap"
                        >
                            Export to Excel
                        </a>
                        <a
                            :href="route('invoices.export', { type: 'pdf', search: searchQuery })"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:bg-red-700 whitespace-nowrap"
                        >
                            Export to PDF
                        </a>
                        <Link
                            v-if="canAdd"
                            :href="route('invoices.create')"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-blue-700 shadow-lg transition hover:shadow-xl hover:scale-105 whitespace-nowrap"
                        >
                            New Invoice
                        </Link>
                    </div>
                </div>
            </div>
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
                            <td class="px-4 py-3 font-semibold text-gray-900 whitespace-nowrap">{{ invoice.invoice_no }}</td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ invoice.invoice_date }}</td>
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
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <IconButton
                                    v-if="canEdit"
                                    icon="fa-solid fa-pen-to-square"
                                    class="bg-blue-600 text-white hover:bg-blue-700"
                                    tooltip="Edit invoice"
                                    @click="router.visit(route('invoices.edit', invoice.id))"
                                />
                                <IconButton
                                    icon="fa-solid fa-eye"
                                    class="bg-gray-100 text-gray-700 hover:bg-gray-200"
                                    tooltip="View invoice"
                                    @click="router.visit(route('invoices.show', invoice.id))"
                                />
                                <a
                                    v-if="invoice.pdf_path"
                                    :href="route('invoices.download', invoice.id)"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition duration-200 bg-white text-gray-700 hover:bg-gray-50 border border-gray-200"
                                    title="Download PDF"
                                >
                                    <FontAwesomeIcon icon="fa-solid fa-download" />
                                </a>
                            </div>
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
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import IconButton from '@/Components/Buttons/IconButton.vue';

const props = defineProps({
    invoices: Array,
    filters: Object,
});

const page = usePage();
const canAdd = computed(() => {
    const perms = page.props.userPermissions || [];
    return perms.includes('invoice.add') || perms.includes('invoice.*') || perms.includes('*') || perms.includes('superadmin');
});

const canEdit = computed(() => {
    const perms = page.props.userPermissions || [];
    return perms.includes('invoice.update') || perms.includes('invoice.*') || perms.includes('*') || perms.includes('superadmin');
});


const searchQuery = ref(props.filters?.search || '');

// Debounce search to avoid too many requests
let searchTimeout = null;
watch(searchQuery, (value) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(() => {
        router.get(
            route('invoices.index'),
            { search: value || undefined },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    }, 300);
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
