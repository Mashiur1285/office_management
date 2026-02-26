<template>
    <Head title="Edit Invoice" />

    <div class="space-y-6">
        <div
            v-if="toastVisible"
            class="fixed right-6 top-6 z-50 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-lg"
        >
            {{ toastMessage }}
        </div>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Invoice</h1>
                <p class="text-sm text-gray-600">Update invoice details and regenerate PDF.</p>
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
                    <FormGroup label="Invoice Number">
                    <input
                        :value="props.invoice.invoice_no"
                        readonly
                        class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700 font-semibold"
                    />
                </FormGroup>
                <FormGroup label="Invoice Date">
                    <input
                        :value="props.invoice.invoice_date"
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
                        <SearchableSelect
                            v-model="form.client_id"
                            :options="clients"
                            placeholder="Select client"
                            @change="handleClientChange"
                        />
                    </FormGroup>
                    <FormGroup label="Organization Name" :error="form.errors.organization_name">
                        <input
                            v-model="form.organization_name"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                            placeholder="Organization name"
                        />
                    </FormGroup>
                    <FormGroup label="Passport Number">
                        <input
                            v-model="form.client_passport"
                            readonly
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700"
                        />
                    </FormGroup>
                    <FormGroup label="Mobile Number">
                        <input
                            v-model="form.client_mobile"
                            readonly
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700"
                        />
                    </FormGroup>
                    <FormGroup label="Agent Name">
                        <input
                            v-model="form.client_agent"
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
                    <FormGroup label="Cost Head" :error="form.errors.service_type">
                        <SubcategorySelector
                            v-model="form.service_type"
                            :subcategories="subcategoryOptions"
                            type="income"
                            :category="form.service_category"
                            label="Cost Head"
                        />
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
                    <p v-if="form.errors.items" class="mb-3 text-sm text-red-600">{{ form.errors.items }}</p>
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-600">
                            <tr>
                                <th class="px-3 py-2">SL</th>
                                <th class="px-3 py-2">Service Description</th>
                                <th class="px-3 py-2">Qty</th>
                                <th class="px-3 py-2">Unit Price</th>
                                <th class="px-3 py-2">Discount</th>
                                <th class="px-3 py-2">VAT %</th>
                                <th class="px-3 py-2 text-right">VAT Amt</th>
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
                                    <p v-if="form.errors[`items.${index}.service_description`]" class="mt-1 text-xs text-red-600">
                                        {{ form.errors[`items.${index}.service_description`] }}
                                    </p>
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        v-model="item.quantity"
                                        type="number"
                                        min="1"
                                        step="1"
                                        inputmode="numeric"
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"
                                        placeholder="1"
                                    />
                                    <p v-if="form.errors[`items.${index}.quantity`]" class="mt-1 text-xs text-red-600">
                                        {{ form.errors[`items.${index}.quantity`] }}
                                    </p>
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        v-model="item.unit_price"
                                        type="number"
                                        min="0"
                                        step="1"
                                        inputmode="numeric"
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"
                                        placeholder="0.00"
                                    />
                                    <p v-if="form.errors[`items.${index}.unit_price`]" class="mt-1 text-xs text-red-600">
                                        {{ form.errors[`items.${index}.unit_price`] }}
                                    </p>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <select
                                            v-model="item.discount_type"
                                            class="w-24 rounded-lg border border-gray-200 px-2 py-2 text-sm"
                                        >
                                            <option value="percent">%</option>
                                            <option value="amount">৳</option>
                                        </select>
                                        <input
                                            v-model="item.discount_value"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"
                                            placeholder="0"
                                        />
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ money(itemCalculations[index].discountAmount) }}
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        v-model="item.vat_rate"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"
                                        placeholder="0"
                                    />
                                </td>
                                <td class="px-3 py-2 text-right text-gray-700">
                                    {{ money(itemCalculations[index].vatAmount) }}
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                    {{ money(itemCalculations[index].lineTotal) }}
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
                    <FormGroup label="Discount Total">
                        <input
                            :value="money(discountTotal)"
                            readonly
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700"
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
                    <FormGroup
                        label="Paid Amount"
                        :error="form.errors.paid_amount"
                        labelClass="text-sm font-semibold text-blue-700"
                    >
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
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Terms & Conditions</h2>
                <div class="flex flex-wrap gap-4">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" value="default" v-model="form.terms_type" />
                        Default
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" value="custom" v-model="form.terms_type" />
                        Custom
                    </label>
                </div>
                <textarea
                    v-model="form.terms_text"
                    rows="6"
                    class="mt-4 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                    :readonly="form.terms_type === 'default'"
                ></textarea>
                <p v-if="form.errors.terms_text" class="mt-2 text-sm text-red-600">
                    {{ form.errors.terms_text }}
                </p>
            </section>

            <div class="flex items-center justify-end gap-3">
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                    :disabled="form.processing"
                >
                    <span v-if="!form.processing">Update & Regenerate PDF</span>
                    <span v-else>Updating...</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { computed, defineComponent, h, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import SubcategorySelector from '@/Components/SubcategorySelector.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({
    invoice: Object,
    clients: Array,
    agents: Array,
    subcategories: Array,
    defaultTerms: String,
});

const initialClient = props.clients.find(item => item.id === props.invoice.client_id) || {};

const form = useForm({
    client_id: props.invoice.client_id,
    organization_name: props.invoice.organization_name || '',
    client_passport: initialClient.passport_number || '',
    client_mobile: props.invoice.client_mobile || '',
    client_agent: initialClient.agent_name || '',
    client_email: props.invoice.client_email || '',
    service_category: props.invoice.service_category,
    service_type: props.invoice.service_type,
    description: props.invoice.description || '',
    terms_type: props.invoice.terms_type || 'default',
    terms_text: props.invoice.terms_text || props.defaultTerms,
    paid_amount: props.invoice.paid_amount || '',
    payment_date: props.invoice.payment_date || '',
    payment_method: props.invoice.payment_method || '',
    items: props.invoice.items.length > 0 ? props.invoice.items : [
        { service_description: '', quantity: '1', unit_price: '', discount_type: 'percent', discount_value: '', vat_rate: '' },
    ],
});

const subcategoryOptions = computed(() => {
    return (props.subcategories || []).filter((item) => item.category === form.service_category);
});

const itemCalculations = computed(() => {
    return form.items.map(item => {
        const qty = parseFloat(item.quantity) || 0;
        const price = parseFloat(item.unit_price) || 0;
        const baseAmount = qty * price;
        const discountValue = parseFloat(item.discount_value) || 0;
        const discountAmount = item.discount_type === 'amount'
            ? discountValue
            : (baseAmount * discountValue) / 100;
        const safeDiscount = Math.min(baseAmount, Math.max(0, discountAmount));
        const taxable = Math.max(0, baseAmount - safeDiscount);
        const vatRate = parseFloat(item.vat_rate) || 0;
        const vatAmount = (taxable * vatRate) / 100;
        const lineTotal = taxable + vatAmount;

        return {
            baseAmount,
            discountAmount: safeDiscount,
            vatAmount,
            lineTotal,
        };
    });
});

const subtotal = computed(() => {
    return itemCalculations.value.reduce((sum, item) => sum + item.baseAmount, 0);
});

const discountTotal = computed(() => {
    return itemCalculations.value.reduce((sum, item) => sum + item.discountAmount, 0);
});

const vatAmount = computed(() => {
    return itemCalculations.value.reduce((sum, item) => sum + item.vatAmount, 0);
});

const totalAmount = computed(() => {
    return itemCalculations.value.reduce((sum, item) => sum + item.lineTotal, 0);
});

const paidAmount = computed(() => parseFloat(form.paid_amount) || 0);
const dueAmount = computed(() => Math.max(0, totalAmount.value - paidAmount.value));

const paymentStatusLabel = computed(() => {
    if (paidAmount.value <= 0) return 'Unpaid';
    if (dueAmount.value <= 0) return 'Paid';
    return 'Partial';
});

const toastMessage = ref('');
const toastVisible = ref(false);
let toastTimer = null;

const showToast = (message) => {
    toastMessage.value = message;
    toastVisible.value = true;
    if (toastTimer) {
        clearTimeout(toastTimer);
    }
    toastTimer = setTimeout(() => {
        toastVisible.value = false;
    }, 3000);
};

const handleClientChange = () => {
    const client = props.clients.find(item => item.id === form.client_id);
    if (!client) return;
    form.organization_name = client.organization_name || '';
    form.client_email = client.email || '';
    form.client_mobile = client.mobile || '';
    form.client_passport = client.passport_number || '';
    form.client_agent = client.agent_name || '';
};

const handleServiceCategoryChange = () => {
    form.service_type = '';
};

const addItem = () => {
    form.items.push({
        service_description: '',
        quantity: '1',
        unit_price: '',
        discount_type: 'percent',
        discount_value: '',
        vat_rate: '',
    });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const submit = () => {
    form.put(route('invoices.update', props.invoice.id), {
        onError: (errors) => {
            if (Object.keys(errors).length > 0) {
                showToast('Required fields are missing. Please fill them in and try again.');
            }
        },
    });
};

watch(
    () => form.client_id,
    () => handleClientChange()
);

watch(
    () => form.terms_type,
    (value) => {
        if (value === 'default') {
            form.terms_text = props.defaultTerms;
        }
    }
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
        labelClass: { type: String, default: '' },
    },
    setup(props, { slots }) {
        const labelClass = props.labelClass || 'text-sm font-medium text-gray-700';
        return () =>
            h(
                'div',
                { class: 'space-y-2', role: 'group' },
                [
                    props.label
                        ? h('label', { class: labelClass }, props.label)
                        : null,
                    slots.default ? slots.default() : null,
                    props.hint ? h('p', { class: 'text-xs text-gray-500' }, props.hint) : null,
                    props.error ? h('p', { class: 'text-xs text-red-600' }, props.error) : null,
                ].filter(Boolean)
            );
    },
});
</script>
