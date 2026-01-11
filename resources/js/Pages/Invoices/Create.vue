<template>
    <Head title="Create Invoice" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Invoice</h1>
                <p class="text-sm text-gray-600">Generate a client invoice with payment details.</p>
            </div>
            <Link
                :href="route('invoices.index')"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
            >
                Back to list
            </Link>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Invoice Info</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <FormGroup label="Invoice Date">
                        <input
                            :value="invoiceDate"
                            readonly
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700"
                        />
                    </FormGroup>
                    <FormGroup label="Payment Method" :error="form.errors.payment_method">
                        <select
                            v-model="form.payment_method"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                        >
                            <option value="" disabled>Select method</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank</option>
                            <option value="Mobile">Mobile</option>
                        </select>
                    </FormGroup>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Client Info</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <FormGroup label="Client Name" :error="form.errors.client_id">
                        <select
                            v-model="form.client_id"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                            @change="handleClientChange"
                        >
                            <option value="" disabled>Select client</option>
                            <option v-for="client in clients" :key="client.id" :value="client.id">
                                {{ client.name }}
                            </option>
                        </select>
                    </FormGroup>
                    <FormGroup label="Organization Name" :error="form.errors.organization_name">
                        <input
                            v-model="form.organization_name"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                            placeholder="Organization name"
                        />
                    </FormGroup>
                    <FormGroup label="Mobile Number">
                        <input
                            v-model="form.client_mobile"
                            readonly
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700"
                        />
                    </FormGroup>
                    <FormGroup label="Client Email" :error="form.errors.client_email">
                        <input
                            v-model="form.client_email"
                            type="email"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                            placeholder="Client email"
                        />
                    </FormGroup>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Service Info</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <FormGroup label="Select Service" :error="form.errors.service_category">
                        <select
                            v-model="form.service_category"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                            @change="handleServiceCategoryChange"
                        >
                            <option value="travel_tourism">Travel and Tourism</option>
                            <option value="manpower_exporting">Manpower Exporting</option>
                            <option value="student_package">Student Package</option>
                            <option value="other_income">Other Income</option>
                        </select>
                    </FormGroup>
                    <FormGroup label="Service Type" :error="form.errors.service_type">
                        <select
                            v-model="form.service_type"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                        >
                            <option value="" disabled>Select subcategory</option>
                            <option v-for="item in serviceTypes" :key="item" :value="item">
                                {{ item }}
                            </option>
                        </select>
                    </FormGroup>
                </div>
                <div class="mt-4">
                    <FormGroup label="Description (Optional)" :error="form.errors.description">
                        <textarea
                            v-model="form.description"
                            rows="5"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                            placeholder="Service details or notes"
                        ></textarea>
                    </FormGroup>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Invoice Items</h2>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white hover:bg-gray-800"
                        @click="addItem"
                    >
                        <font-awesome-icon icon="circle-plus" />
                        Add Row
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-600">
                            <tr>
                                <th class="px-3 py-2">SL</th>
                                <th class="px-3 py-2">Service Description</th>
                                <th class="px-3 py-2">Qty</th>
                                <th class="px-3 py-2">Unit Price</th>
                                <th class="px-3 py-2 text-right">Line Total</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in form.items" :key="index" class="border-b">
                                <td class="px-3 py-2 font-semibold text-gray-700">{{ index + 1 }}</td>
                                <td class="px-3 py-2">
                                    <input
                                        v-model="item.service_description"
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"
                                        placeholder="Service description"
                                    />
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        v-model="item.quantity"
                                        type="number"
                                        min="0.01"
                                        step="0.01"
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"
                                        placeholder="1"
                                    />
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        v-model="item.unit_price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"
                                        placeholder="0.00"
                                    />
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                    {{ money(lineTotals[index]) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <button
                                        type="button"
                                        class="text-red-600 hover:text-red-700"
                                        @click="removeItem(index)"
                                        v-if="form.items.length > 1"
                                    >
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Summary</h2>
                <div class="grid gap-4 md:grid-cols-3">
                    <FormGroup label="Subtotal">
                        <input
                            :value="money(subtotal)"
                            readonly
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700"
                        />
                    </FormGroup>
                    <FormGroup label="Discount" :error="form.errors.discount_amount">
                        <input
                            v-model="form.discount_amount"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                            placeholder="0.00"
                        />
                    </FormGroup>
                    <FormGroup label="VAT Rate (%)" :error="form.errors.vat_rate">
                        <input
                            v-model="form.vat_rate"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                            placeholder="0"
                        />
                    </FormGroup>
                    <FormGroup label="VAT Amount">
                        <input
                            :value="money(vatAmount)"
                            readonly
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700"
                        />
                    </FormGroup>
                    <FormGroup label="Total Amount">
                        <input
                            :value="money(totalAmount)"
                            readonly
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-900 font-semibold"
                        />
                    </FormGroup>
                    <FormGroup label="Paid Amount" :error="form.errors.paid_amount">
                        <input
                            v-model="form.paid_amount"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                            placeholder="0.00"
                        />
                    </FormGroup>
                    <FormGroup label="Due Amount">
                        <input
                            :value="money(dueAmount)"
                            readonly
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700"
                        />
                    </FormGroup>
                    <FormGroup label="Payment Date" :error="form.errors.payment_date">
                        <input
                            v-model="form.payment_date"
                            type="date"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                        />
                    </FormGroup>
                    <FormGroup label="Status">
                        <input
                            :value="paymentStatusLabel"
                            readonly
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700"
                        />
                    </FormGroup>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Company Contact</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <FormGroup label="Phone" :error="form.errors.company_phone">
                        <input
                            v-model="form.company_phone"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                            placeholder="Phone number"
                        />
                    </FormGroup>
                    <FormGroup label="Email" :error="form.errors.company_email">
                        <input
                            v-model="form.company_email"
                            type="email"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                            placeholder="Company email"
                        />
                    </FormGroup>
                </div>
                <FormGroup label="Address" :error="form.errors.company_address" class="mt-4">
                    <textarea
                        v-model="form.company_address"
                        rows="3"
                        class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                        placeholder="Company address"
                    ></textarea>
                </FormGroup>
            </section>

            <div class="flex items-center justify-end gap-3">
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                    :disabled="form.processing"
                >
                    <span v-if="!form.processing">Save & Generate PDF</span>
                    <span v-else>Saving...</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { computed, defineComponent, h, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    clients: Array,
    subcategories: Object,
    companyDefaults: Object,
});

const invoiceDate = new Date().toISOString().split('T')[0];

const form = useForm({
    client_id: '',
    organization_name: '',
    client_mobile: '',
    client_email: '',
    service_category: 'travel_tourism',
    service_type: '',
    description: '',
    company_phone: props.companyDefaults?.phone || '',
    company_email: props.companyDefaults?.email || '',
    company_address: props.companyDefaults?.address || '',
    discount_amount: '',
    vat_rate: '',
    paid_amount: '',
    payment_date: '',
    payment_method: '',
    items: [
        { service_description: '', quantity: '1', unit_price: '' },
    ],
});

const serviceTypes = computed(() => {
    return (props.subcategories?.[form.service_category] || []).map(item => item.name);
});

const lineTotals = computed(() => {
    return form.items.map(item => {
        const qty = parseFloat(item.quantity) || 0;
        const price = parseFloat(item.unit_price) || 0;
        return qty * price;
    });
});

const subtotal = computed(() => {
    return lineTotals.value.reduce((sum, value) => sum + value, 0);
});

const discountAmount = computed(() => parseFloat(form.discount_amount) || 0);
const vatRate = computed(() => parseFloat(form.vat_rate) || 0);
const vatAmount = computed(() => {
    const taxable = Math.max(0, subtotal.value - discountAmount.value);
    return (taxable * vatRate.value) / 100;
});

const totalAmount = computed(() => {
    const taxable = Math.max(0, subtotal.value - discountAmount.value);
    return taxable + vatAmount.value;
});

const paidAmount = computed(() => parseFloat(form.paid_amount) || 0);
const dueAmount = computed(() => Math.max(0, totalAmount.value - paidAmount.value));

const paymentStatusLabel = computed(() => {
    if (paidAmount.value <= 0) return 'Unpaid';
    if (dueAmount.value <= 0) return 'Paid';
    return 'Partial';
});

const handleClientChange = () => {
    const client = props.clients.find(item => item.id === form.client_id);
    if (!client) return;
    form.organization_name = client.organization_name || '';
    form.client_email = client.email || '';
    form.client_mobile = client.mobile || '';
};

const handleServiceCategoryChange = () => {
    form.service_type = '';
};

const addItem = () => {
    form.items.push({ service_description: '', quantity: '1', unit_price: '' });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const submit = () => {
    form.post(route('invoices.store'));
};

watch(
    () => form.client_id,
    () => handleClientChange()
);

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
};

const FormGroup = defineComponent({
    name: 'FormGroup',
    props: {
        label: { type: String, default: '' },
        error: { type: String, default: '' },
        hint: { type: String, default: '' },
    },
    setup(props, { slots }) {
        return () =>
            h(
                'div',
                { class: 'space-y-2', role: 'group' },
                [
                    props.label
                        ? h('label', { class: 'text-sm font-medium text-gray-700' }, props.label)
                        : null,
                    slots.default ? slots.default() : null,
                    props.hint ? h('p', { class: 'text-xs text-gray-500' }, props.hint) : null,
                    props.error ? h('p', { class: 'text-xs text-red-600' }, props.error) : null,
                ].filter(Boolean)
            );
    },
});
</script>
