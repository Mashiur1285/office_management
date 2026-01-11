<template>
    <Head title="Tax Summary" />
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-amber-50/30 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Tax Summary & Payments</h1>
                        <p class="text-sm text-gray-600 mt-1">Period: {{ period.name }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select class="px-4 py-2 pr-10 border border-gray-300 rounded-lg text-sm font-medium bg-white">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                        <a :href="route('accounting.tax-summary.report')" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium text-sm flex items-center gap-2">
                            <i class="fa-solid fa-download"></i>
                            Download Report
                        </a>
                        <a :href="route('accounting.tax-summary.report', { type: 'pdf' })" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf"></i>
                            Download PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Total Tax Summary with Payments -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-amber-500 p-6">
                <h2 class="text-xl font-bold text-amber-700 mb-6">Tax Payment Summary</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-amber-50 rounded-lg p-4 border-2 border-amber-300">
                        <div class="text-sm text-gray-600 mb-1">Total Tax</div>
                        <div class="text-2xl font-bold text-amber-700">
                            {{ money(totalTax) }}
                        </div>
                    </div>

                    <div class="bg-blue-50 rounded-lg p-4 border-2 border-blue-300">
                        <div class="text-sm text-gray-600 mb-1">Total Payments Made</div>
                        <div class="text-2xl font-bold text-blue-700">
                            {{ money(totalTaxPayments) }}
                        </div>
                    </div>

                    <div :class="['rounded-lg p-4 border-2', taxBalance > 0 ? 'bg-orange-50 border-orange-300' : 'bg-green-50 border-green-300']">
                        <div class="text-sm text-gray-600 mb-1">Remaining Balance</div>
                        <div :class="['text-2xl font-bold', taxBalance > 0 ? 'text-orange-700' : 'text-green-700']">
                            {{ money(taxBalance) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax Breakdown -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-yellow-400 p-6">
                <h2 class="text-xl font-bold text-yellow-700 mb-4">Tax Breakdown</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                        <div class="text-sm text-gray-600 mb-1">Current Tax</div>
                        <div class="text-2xl font-bold text-yellow-700">
                            {{ money(taxByType.current) }}
                        </div>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                        <div class="text-sm text-gray-600 mb-1">Deferred Tax</div>
                        <div class="text-2xl font-bold text-yellow-700">
                            {{ money(taxByType.deferred) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax Payment Form -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-green-500 p-6">
                <h2 class="text-xl font-bold text-green-700 mb-4">Record Tax Payment</h2>
                <form @submit.prevent="submitPayment" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Type *</label>
                            <select
                                v-model="paymentForm.payment_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                required
                            >
                                <option value="bulk">Bulk Payment (All Clients)</option>
                                <option value="individual">Individual Client Payment</option>
                            </select>
                        </div>

                        <div v-if="paymentForm.payment_type === 'individual'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Client *</label>
                            <select
                                v-model="paymentForm.client_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                required
                            >
                                <option :value="null">-- Select Client --</option>
                                <option v-for="client in clientsWithTax.filter(c => !c.tax_paid)" :key="client.id" :value="client.id">
                                    {{ client.name }} (Unpaid Tax: {{ money(client.tax_unpaid_amount) }})
                                </option>
                            </select>
                        </div>

                        <div v-if="paymentForm.payment_type === 'individual'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Amount *</label>
                            <input
                                v-model="paymentForm.payment_amount"
                                type="number"
                                step="0.01"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="0.00"
                            />
                        </div>

                        <div v-else class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Total Payment Amount:</span>
                                <span class="text-lg font-bold text-blue-700">{{ money(taxBalance) }}</span>
                            </div>
                            <p class="text-xs text-gray-600">This amount will be distributed across all clients with unpaid tax</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date *</label>
                            <VueDatePicker
                                v-model="paymentForm.payment_date"
                                :enable-time-picker="false"
                                model-type="yyyy-MM-dd"
                                required
                                class="w-full"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Chalan Number *</label>
                            <input
                                v-model="paymentForm.chalan_number"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Enter chalan number"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Chalan Slip</label>
                            <input
                                ref="chalanSlipInput"
                                type="file"
                                @change="handleChalanSlipChange"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea
                                v-model="paymentForm.notes"
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Any additional notes..."
                            ></textarea>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ isSubmitting ? 'Saving...' : 'Record Payment' }}
                    </button>
                </form>
            </div>

            <!-- Clients with Tax Due -->
            <div v-if="clientsWithTax && clientsWithTax.length > 0" class="bg-white rounded-xl shadow-sm border-4 border-blue-500 overflow-hidden">
                <button
                    @click="showClientsWithTax = !showClientsWithTax"
                    class="w-full bg-blue-600 text-white p-4 flex items-center justify-between hover:bg-blue-700 transition-colors"
                >
                    <div>
                        <h2 class="text-xl font-bold">Clients with Tax Due</h2>
                        <p class="text-sm text-blue-100 mt-1">{{ clientsWithTax.length }} client(s) with tax due</p>
                    </div>
                    <svg
                        class="w-6 h-6 transition-transform duration-200"
                        :class="{ 'rotate-180': showClientsWithTax }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div v-show="showClientsWithTax" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-blue-50 border-b-2 border-blue-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Client Name</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-blue-900 uppercase tracking-wider">Total Tax</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-blue-900 uppercase tracking-wider">Paid</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-blue-900 uppercase tracking-wider">Unpaid</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Payment Info</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="client in clientsWithTax" :key="client.id" :class="client.tax_paid ? 'bg-green-50' : 'hover:bg-gray-50'">
                                <td class="px-6 py-3 text-sm font-medium text-gray-900">
                                    {{ client.name }}
                                </td>
                                <td class="px-6 py-3 text-sm font-semibold text-right text-gray-900">
                                    {{ money(client.total_tax_receivable) }}
                                </td>
                                <td class="px-6 py-3 text-sm text-right text-green-700">
                                    {{ money(client.tax_paid_amount) }}
                                </td>
                                <td class="px-6 py-3 text-sm font-bold text-right" :class="client.tax_paid ? 'text-gray-400' : 'text-orange-700'">
                                    {{ money(client.tax_unpaid_amount) }}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span v-if="client.tax_paid" class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                        Fully Paid
                                    </span>
                                    <span v-else-if="client.tax_paid_amount > 0" class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                                        Partially Paid
                                    </span>
                                    <span v-else class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-semibold">
                                        Pending
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-600">
                                    <div v-if="client.tax_paid_amount > 0">
                                        <div v-if="client.tax_chalan_number" class="text-xs">Chalan: {{ client.tax_chalan_number }}</div>
                                        <div v-if="client.tax_payment_date" class="text-xs">Date: {{ formatDate(client.tax_payment_date) }}</div>
                                    </div>
                                    <div v-else class="text-gray-400">—</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tax Payment History -->
            <div v-if="taxPayments && taxPayments.length > 0" class="bg-white rounded-xl shadow-sm border-4 border-purple-500 overflow-hidden">
                <div class="bg-purple-600 text-white p-4">
                    <h2 class="text-xl font-bold">Tax Payment History</h2>
                    <p class="text-sm text-purple-100 mt-1">All recorded payments for this period</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-purple-50 border-b-2 border-purple-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-purple-900 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-purple-900 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-purple-900 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-purple-900 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-purple-900 uppercase tracking-wider">Chalan Number</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-purple-900 uppercase tracking-wider">Notes</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-purple-900 uppercase tracking-wider">Chalan Slip</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-purple-900 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="payment in taxPayments" :key="payment.id" class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-900">
                                    {{ formatDate(payment.payment_date) }}
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-700 capitalize">
                                    {{ payment.payment_type }}
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-700">
                                    {{ payment.client_name || 'Organization-wide' }}
                                </td>
                                <td class="px-6 py-3 text-sm font-semibold text-green-700">
                                    {{ money(payment.payment_amount) }}
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-700">
                                    {{ payment.chalan_number || '—' }}
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-700">
                                    {{ payment.notes || '—' }}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <a
                                        v-if="payment.chalan_slip"
                                        :href="`/storage/${payment.chalan_slip}`"
                                        target="_blank"
                                        class="text-blue-600 hover:text-blue-800 text-sm underline"
                                    >
                                        View
                                    </a>
                                    <span v-else class="text-sm text-gray-400">—</span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <button
                                        @click="deletePaymentRecord(payment.id)"
                                        class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-xs"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link href="/accounting/tax" class="bg-amber-50 hover:bg-amber-100 rounded-lg p-4 border border-amber-200 transition-colors">
                    <div class="text-sm text-amber-700 font-medium mb-1">Tax Management</div>
                    <div class="font-semibold text-gray-900">Add & Edit Tax Entries</div>
                </Link>
                <Link href="/accounting/net-profit-after-tax" class="bg-purple-50 hover:bg-purple-100 rounded-lg p-4 border border-purple-200 transition-colors">
                    <div class="text-sm text-purple-700 font-medium mb-1">P&L Summary</div>
                    <div class="font-semibold text-gray-900">Net Profit After Tax</div>
                </Link>
                <Link href="/accounting" class="bg-amber-50 hover:bg-amber-100 rounded-lg p-4 border border-amber-200 transition-colors">
                    <div class="text-sm text-amber-700 font-medium mb-1">Dashboard</div>
                    <div class="font-semibold text-gray-900">Accounting Home</div>
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const props = defineProps({
    period: Object,
    periods: Array,
    taxByType: Object,
    totalTax: Number,
    taxPayments: Array,
    totalTaxPayments: Number,
    taxBalance: Number,
    clientsWithTax: Array,
});

const chalanSlipInput = ref(null);
const isSubmitting = ref(false);
const showClientsWithTax = ref(false);

const paymentForm = ref({
    payment_type: 'bulk',
    client_id: null,
    payment_amount: '',
    payment_date: new Date().toISOString().split('T')[0],
    chalan_number: '',
    chalan_slip: null,
    notes: '',
});

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-BD', { year: 'numeric', month: 'short', day: 'numeric' });
};

const handleChalanSlipChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        paymentForm.value.chalan_slip = file;
    }
};

const submitPayment = () => {
    if (isSubmitting.value) return;

    if (!paymentForm.value.chalan_number || paymentForm.value.chalan_number.trim() === '') {
        alert('Please enter a Chalan Number.');
        return;
    }

    let paymentAmount;
    if (paymentForm.value.payment_type === 'bulk') {
        paymentAmount = props.taxBalance;
    } else {
        paymentAmount = parseFloat(paymentForm.value.payment_amount) || 0;
    }

    if (paymentForm.value.payment_type === 'individual') {
        if (!paymentForm.value.client_id) {
            alert('Please select a client for individual payment.');
            return;
        }

        const selectedClient = props.clientsWithTax.find(c => c.id === paymentForm.value.client_id);
        if (selectedClient && paymentAmount > selectedClient.tax_unpaid_amount) {
            alert(`Payment amount ${money(paymentAmount)} exceeds client unpaid tax ${money(selectedClient.tax_unpaid_amount)}.`);
            return;
        }
    }

    isSubmitting.value = true;

    const formData = new FormData();
    formData.append('payment_type', paymentForm.value.payment_type);
    if (paymentForm.value.client_id) {
        formData.append('client_id', paymentForm.value.client_id);
    }
    formData.append('payment_amount', paymentAmount);
    formData.append('payment_date', paymentForm.value.payment_date);
    formData.append('chalan_number', paymentForm.value.chalan_number);

    if (paymentForm.value.chalan_slip) {
        formData.append('chalan_slip', paymentForm.value.chalan_slip);
    }
    if (paymentForm.value.notes) {
        formData.append('notes', paymentForm.value.notes);
    }

    router.post(`/accounting/tax-summary/${props.period.id}/payment`, formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            paymentForm.value = {
                payment_type: 'bulk',
                client_id: null,
                payment_amount: '',
                payment_date: new Date().toISOString().split('T')[0],
                chalan_number: '',
                chalan_slip: null,
                notes: '',
            };

            if (chalanSlipInput.value) {
                chalanSlipInput.value.value = '';
            }

            isSubmitting.value = false;
        },
        onError: (errors) => {
            console.error('Payment submission error:', errors);
            alert('Failed to record payment. Please check the form and try again.');
            isSubmitting.value = false;
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

const deletePaymentRecord = (paymentId) => {
    if (!confirm('Are you sure you want to delete this payment record?')) return;

    router.delete(`/accounting/tax-payment/${paymentId}`, {
        preserveScroll: true,
    });
};
</script>
