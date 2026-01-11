<template>
    <Head :title="`Invoice ${invoice.invoice_no}`" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Invoice {{ invoice.invoice_no }}</h1>
                <p class="text-sm text-gray-600">Invoice details and receipt summary.</p>
            </div>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('invoices.index')"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Back to list
                </Link>
                <a
                    v-if="invoice.pdf_path"
                    :href="route('invoices.download', invoice.id)"
                    class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
                >
                    Download PDF
                </a>
            </div>
        </div>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2">
                <InfoRow label="Invoice Date" :value="invoice.invoice_date" />
                <InfoRow label="Payment Status" :value="capitalize(invoice.payment_status)" />
                <InfoRow label="Client Name" :value="invoice.client_name" />
                <InfoRow label="Organization" :value="invoice.organization_name || '—'" />
                <InfoRow label="Mobile" :value="invoice.client_mobile || '—'" />
                <InfoRow label="Email" :value="invoice.client_email || '—'" />
                <InfoRow label="Service" :value="formatService(invoice.service_category, invoice.service_type)" />
                <InfoRow label="Created By" :value="invoice.created_by || '—'" />
            </div>
            <div class="mt-4" v-if="invoice.description">
                <h2 class="text-sm font-semibold text-gray-700 mb-2">Description</h2>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ invoice.description }}</p>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Invoice Items</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-600">
                        <tr>
                            <th class="px-3 py-2">SL</th>
                            <th class="px-3 py-2">Service Description</th>
                            <th class="px-3 py-2 text-right">Qty</th>
                            <th class="px-3 py-2 text-right">Unit Price</th>
                            <th class="px-3 py-2 text-right">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in invoice.items" :key="item.id" class="border-b">
                            <td class="px-3 py-2 font-semibold text-gray-700">{{ item.sl }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ item.service_description }}</td>
                            <td class="px-3 py-2 text-right">{{ item.quantity }}</td>
                            <td class="px-3 py-2 text-right">{{ money(item.unit_price) }}</td>
                            <td class="px-3 py-2 text-right font-semibold text-gray-900">{{ money(item.line_total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Summary</h2>
            <div class="grid gap-4 md:grid-cols-3">
                <InfoRow label="Subtotal" :value="money(invoice.subtotal)" />
                <InfoRow label="Discount" :value="money(invoice.discount_amount)" />
                <InfoRow label="VAT Rate" :value="`${invoice.vat_rate}%`" />
                <InfoRow label="VAT Amount" :value="money(invoice.vat_amount)" />
                <InfoRow label="Total Amount" :value="money(invoice.total_amount)" />
                <InfoRow label="Paid Amount" :value="money(invoice.paid_amount)" />
                <InfoRow label="Due Amount" :value="money(invoice.due_amount)" />
                <InfoRow label="Payment Date" :value="invoice.payment_date || '—'" />
                <InfoRow label="Payment Method" :value="invoice.payment_method || '—'" />
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Company Contact</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <InfoRow label="Phone" :value="invoice.company_phone || '—'" />
                <InfoRow label="Email" :value="invoice.company_email || '—'" />
            </div>
            <div class="mt-4">
                <InfoRow label="Address" :value="invoice.company_address || '—'" />
            </div>
        </section>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { defineComponent, h } from 'vue';

const props = defineProps({
    invoice: Object,
});

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
};

const capitalize = (text) => {
    if (!text) return '';
    return text.charAt(0).toUpperCase() + text.slice(1);
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

const InfoRow = defineComponent({
    name: 'InfoRow',
    props: {
        label: { type: String, default: '' },
        value: { type: String, default: '' },
    },
    setup(props) {
        return () =>
            h('div', { class: 'space-y-1' }, [
                h('div', { class: 'text-xs font-semibold text-gray-500 uppercase' }, props.label),
                h('div', { class: 'text-sm font-medium text-gray-900' }, props.value),
            ]);
    },
});
</script>
