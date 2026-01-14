<template>
    <Head :title="`Quotation ${quotation.quotation_no}`" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Quotation {{ quotation.quotation_no }}
                </h1>
                <p class="text-sm text-gray-600">
                    Created on {{ quotation.quotation_date }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('quotations.index')"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Back
                </Link>
                <a
                    :href="route('quotations.download', quotation.id)"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
                >
                    <font-awesome-icon icon="file-pdf" />
                    Download PDF
                </a>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <InfoCard title="Client Info">
                <InfoRow label="Client Name" :value="quotation.client_name" />
                <InfoRow label="Organization" :value="quotation.organization_name || '—'" />
                <InfoRow label="Mobile" :value="quotation.client_mobile || '—'" />
                <InfoRow label="Email" :value="quotation.client_email || '—'" />
            </InfoCard>
            <InfoCard title="Service Info">
                <InfoRow label="Service" :value="formatService(quotation.service_category, quotation.service_type)" />
                <InfoRow label="Validity" :value="quotation.valid_until" />
                <InfoRow label="Quotation Maker" :value="quotation.quotation_maker || '—'" />
                <InfoRow label="Created By" :value="quotation.created_by || '—'" />
            </InfoCard>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Description</h2>
            <p class="whitespace-pre-line text-sm text-gray-700">
                {{ quotation.description }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Items</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-600">
                        <tr>
                            <th class="px-3 py-2">SL</th>
                            <th class="px-3 py-2">Service Description</th>
                            <th class="px-3 py-2 text-right">Price</th>
                            <th class="px-3 py-2 text-right">Discount</th>
                            <th class="px-3 py-2 text-right">VAT %</th>
                            <th class="px-3 py-2 text-right">VAT Amt</th>
                            <th class="px-3 py-2 text-right">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in quotation.items" :key="item.id" class="border-b">
                            <td class="px-3 py-2 font-semibold text-gray-700">{{ item.sl }}</td>
                            <td class="px-3 py-2">{{ item.service_description }}</td>
                            <td class="px-3 py-2 text-right">{{ money(item.price) }}</td>
                            <td class="px-3 py-2 text-right">{{ money(item.discount_amount) }}</td>
                            <td class="px-3 py-2 text-right">{{ item.vat_rate }}%</td>
                            <td class="px-3 py-2 text-right">{{ money(item.vat_amount) }}</td>
                            <td class="px-3 py-2 text-right font-semibold text-gray-900">{{ money(item.line_total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Summary</h2>
            <div class="grid gap-4 md:grid-cols-3">
                <InfoRow label="Subtotal" :value="money(quotation.subtotal)" />
                <InfoRow label="Discount Total" :value="money(quotation.discount_amount)" />
                <InfoRow label="VAT Amount" :value="money(quotation.vat_amount)" />
                <InfoRow label="Total Amount" :value="money(quotation.total_amount)" />
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Terms & Conditions</h2>
            <p class="whitespace-pre-line text-sm text-gray-700">
                {{ quotation.terms_text }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Company Contact</h2>
            <InfoRow label="Phone" :value="quotation.company_phone || '—'" />
            <InfoRow label="Email" :value="quotation.company_email || '—'" />
            <InfoRow label="Address" :value="quotation.company_address || '—'" />
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    quotation: Object,
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

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
};

const InfoCard = {
    props: ['title'],
    template: `
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ title }}</h2>
            <slot />
        </div>
    `,
};

const InfoRow = {
    props: ['label', 'value'],
    template: `
        <div class="flex flex-col gap-1 pb-3 text-sm">
            <span class="text-xs font-semibold uppercase text-gray-500">{{ label }}</span>
            <span class="text-gray-800">{{ value }}</span>
        </div>
    `,
};
</script>
