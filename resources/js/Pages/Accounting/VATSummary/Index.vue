<template>
    <Head title="VAT Summary" />
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-red-50/30 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-800 text-white shadow-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white">VAT Summary & Reconciliation</h1>
                        <p class="text-sm text-blue-100 mt-1">Period: {{ period.name }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select class="px-4 py-2 pr-10 border border-gray-300 rounded-lg text-sm font-medium bg-white text-gray-900">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                        <a :href="route('accounting.vat-summary.report')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium text-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-excel"></i>
                            Export to Excel
                        </a>
                        <a :href="route('accounting.vat-summary.report', { type: 'pdf' })" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf"></i>
                            Export to PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Total VAT Summary with Payments -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-red-500 p-6">
                <h2 class="text-xl font-bold text-red-700 mb-6">VAT Payment Summary</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Total VAT Payable -->
                    <div class="bg-red-50 rounded-lg p-4 border-2 border-red-300">
                        <div class="text-sm text-gray-600 mb-1">Total VAT Collected</div>
                        <div class="text-2xl font-bold text-red-700">
                            {{ money(totalVat) }}
                        </div>
                    </div>

                    <!-- Total Payments -->
                    <div class="bg-blue-50 rounded-lg p-4 border-2 border-blue-300">
                        <div class="text-sm text-gray-600 mb-1">Total Payments Made</div>
                        <div class="text-2xl font-bold text-blue-700">
                            {{ money(totalVatPayments) }}
                        </div>
                    </div>

                    <!-- Balance -->
                    <div :class="['rounded-lg p-4 border-2', vatBalance > 0 ? 'bg-orange-50 border-orange-300' : 'bg-green-50 border-green-300']">
                        <div class="text-sm text-gray-600 mb-1">Remaining Balance</div>
                        <div :class="['text-2xl font-bold', vatBalance > 0 ? 'text-orange-700' : 'text-green-700']">
                            {{ money(vatBalance) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- VAT Payment Form -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-green-500 p-6">
                <h2 class="text-xl font-bold text-green-700 mb-4">Record VAT Payment</h2>
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
                                <option v-for="client in clientsWithVat.filter(c => !c.vat_paid)" :key="client.id" :value="client.id">
                                    {{ client.name }} (Unpaid VAT: {{ money(client.vat_unpaid_amount) }})
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
                                <span class="text-lg font-bold text-blue-700">{{ money(vatBalance) }}</span>
                            </div>
                            <p class="text-xs text-gray-600">This amount will be distributed across all clients with unpaid VAT</p>
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

            <!-- Clients with VAT Receivable -->
            <div v-if="clientsWithVat && clientsWithVat.length > 0" class="bg-white rounded-xl shadow-sm border-4 border-blue-500 overflow-hidden">
                <button
                    @click="showClientsWithVat = !showClientsWithVat"
                    class="w-full bg-blue-600 text-white p-4 flex items-center justify-between hover:bg-blue-700 transition-colors"
                >
                    <div>
                        <h2 class="text-xl font-bold">Clients with VAT Receivable</h2>
                        <p class="text-sm text-blue-100 mt-1">{{ clientsWithVat.length }} client(s) with VAT due</p>
                    </div>
                    <svg
                        class="w-6 h-6 transition-transform duration-200"
                        :class="{ 'rotate-180': showClientsWithVat }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div v-show="showClientsWithVat" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-blue-50 border-b-2 border-blue-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Client Name</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-blue-900 uppercase tracking-wider">Total VAT</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-blue-900 uppercase tracking-wider">Paid</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-blue-900 uppercase tracking-wider">Unpaid</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Payment Info</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="client in clientsWithVat" :key="client.id" :class="client.vat_paid ? 'bg-green-50' : 'hover:bg-gray-50'">
                                <td class="px-6 py-3 text-sm font-medium text-gray-900">
                                    {{ client.name }}
                                </td>
                                <td class="px-6 py-3 text-sm font-semibold text-right text-gray-900">
                                    {{ money(client.total_vat_receivable) }}
                                </td>
                                <td class="px-6 py-3 text-sm text-right text-green-700">
                                    {{ money(client.vat_paid_amount) }}
                                </td>
                                <td class="px-6 py-3 text-sm font-bold text-right" :class="client.vat_paid ? 'text-gray-400' : 'text-orange-700'">
                                    {{ money(client.vat_unpaid_amount) }}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span v-if="client.vat_paid" class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                        Fully Paid
                                    </span>
                                    <span v-else-if="client.vat_paid_amount > 0" class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                                        Partially Paid
                                    </span>
                                    <span v-else class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-semibold">
                                        Pending
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-600">
                                    <div v-if="client.vat_paid_amount > 0">
                                        <div v-if="client.vat_chalan_number" class="text-xs">Challan: {{ client.vat_chalan_number }}</div>
                                        <div v-if="client.vat_payment_date" class="text-xs">Date: {{ formatDate(client.vat_payment_date) }}</div>
                                    </div>
                                    <div v-else class="text-gray-400">—</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VAT Payment History -->
            <div v-if="vatPayments && vatPayments.length > 0" class="bg-white rounded-xl shadow-sm border-4 border-purple-500 overflow-hidden">
                <div class="bg-purple-600 text-white p-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-xl font-bold">VAT Payment History</h2>
                            <p class="text-sm text-purple-100 mt-1">All recorded payments for this period</p>
                        </div>
                        <div class="flex-1 max-w-md">
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    v-model="searchPayments"
                                    type="text"
                                    class="block w-full rounded-lg border border-purple-400 bg-purple-500 pl-10 pr-4 py-2 text-sm text-white placeholder-purple-200 focus:border-white focus:ring-2 focus:ring-white focus:bg-purple-600 transition"
                                    placeholder="Search by challan number, client, date..."
                                />
                                <button
                                    v-if="searchPayments"
                                    @click="searchPayments = ''"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-purple-200 hover:text-white"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-if="searchPayments" class="mt-2 text-sm text-purple-100">
                        Showing <span class="font-semibold text-white">{{ filteredVatPayments.length }}</span> of {{ vatPayments.length }} payments
                    </div>
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
                            <tr v-for="payment in filteredVatPayments" :key="payment.id" class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-900">
                                    {{ formatDate(payment.payment_date) }}
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    <span
                                        :class="payment.payment_type === 'bulk' ? 'px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs font-semibold' : 'px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold'"
                                    >
                                        {{ payment.payment_type === 'bulk' ? 'Bulk' : 'Individual' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-900">
                                    {{ payment.client_name || '—' }}
                                </td>
                                <td class="px-6 py-3 text-sm font-semibold text-green-700">
                                    {{ money(payment.payment_amount) }}
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-900">
                                    {{ payment.chalan_number || '—' }}
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-600">
                                    {{ payment.notes || '—' }}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <a
                                        v-if="payment.chalan_slip"
                                        :href="`/storage/${payment.chalan_slip}`"
                                        target="_blank"
                                        class="inline-flex items-center px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </a>
                                    <span v-else class="text-sm text-gray-400">—</span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <button
                                        @click="deletePaymentRecord(payment.id)"
                                        class="p-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                                        title="Delete Payment"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredVatPayments.length === 0 && vatPayments.length > 0">
                                <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No payments found matching your search.
                                    <button @click="searchPayments = ''" class="text-purple-600 font-semibold hover:underline ml-1">Clear search</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VAT Breakdown by Category -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-red-500 overflow-hidden">
                <div class="bg-red-600 text-white p-4">
                    <h2 class="text-xl font-bold">Detailed VAT Breakdown</h2>
                    <p class="text-sm text-red-100 mt-1">Output VAT from all income sources</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-red-50 border-b-2 border-red-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-red-900 uppercase tracking-wider">
                                    Description
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-red-900 uppercase tracking-wider">
                                    VAT Amount
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <!-- Travel & Tourism Section -->
                            <template v-if="vatData.travel_tourism && vatData.travel_tourism.length > 0">
                                <tr class="bg-green-50">
                                    <td colspan="2" class="px-6 py-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-green-700" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                                                </svg>
                                                <span class="font-bold text-green-900">1. Travel & Tourism Income VAT</span>
                                            </div>
                                            <Link href="/accounting/income/travel-tourism" class="text-xs text-green-700 hover:text-green-800 hover:underline font-medium">
                                                View Entries →
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="item in vatData.travel_tourism" :key="item.subcategory" class="hover:bg-gray-50">
                                    <td class="px-6 py-3 pl-12 text-sm text-gray-900">
                                        {{ item.subcategory }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">
                                        {{ money(item.vat_amount) }}
                                    </td>
                                </tr>
                                <tr class="bg-green-100 border-t-2 border-green-300">
                                    <td class="px-6 py-3 pl-12 text-sm font-bold text-green-900">
                                        Subtotal - Travel & Tourism VAT
                                    </td>
                                    <td class="px-6 py-3 text-sm text-right font-bold text-green-700">
                                        {{ money(categoryTotals.travel_tourism) }}
                                    </td>
                                </tr>
                            </template>

                            <!-- Manpower Exporting Section -->
                            <template v-if="vatData.manpower_exporting && vatData.manpower_exporting.length > 0">
                                <tr class="bg-blue-50">
                                    <td colspan="2" class="px-6 py-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-blue-700" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                                </svg>
                                                <span class="font-bold text-blue-900">2. Manpower Exporting Income VAT</span>
                                            </div>
                                            <Link href="/accounting/income/manpower" class="text-xs text-blue-700 hover:text-blue-800 hover:underline font-medium">
                                                View Entries →
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="item in vatData.manpower_exporting" :key="item.subcategory" class="hover:bg-gray-50">
                                    <td class="px-6 py-3 pl-12 text-sm text-gray-900">
                                        {{ item.subcategory }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">
                                        {{ money(item.vat_amount) }}
                                    </td>
                                </tr>
                                <tr class="bg-blue-100 border-t-2 border-blue-300">
                                    <td class="px-6 py-3 pl-12 text-sm font-bold text-blue-900">
                                        Subtotal - Manpower Exporting VAT
                                    </td>
                                    <td class="px-6 py-3 text-sm text-right font-bold text-blue-700">
                                        {{ money(categoryTotals.manpower_exporting) }}
                                    </td>
                                </tr>
                            </template>

                            <!-- Student Package Section -->
                            <template v-if="vatData.student_package && vatData.student_package.length > 0">
                                <tr class="bg-purple-50">
                                    <td colspan="2" class="px-6 py-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-purple-700" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838l-2.727 1.17 1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                                                </svg>
                                                <span class="font-bold text-purple-900">3. Student Package Income VAT</span>
                                            </div>
                                            <Link href="/accounting/income/student" class="text-xs text-purple-700 hover:text-purple-800 hover:underline font-medium">
                                                View Entries →
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="item in vatData.student_package" :key="item.subcategory" class="hover:bg-gray-50">
                                    <td class="px-6 py-3 pl-12 text-sm text-gray-900">
                                        {{ item.subcategory }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">
                                        {{ money(item.vat_amount) }}
                                    </td>
                                </tr>
                                <tr class="bg-purple-100 border-t-2 border-purple-300">
                                    <td class="px-6 py-3 pl-12 text-sm font-bold text-purple-900">
                                        Subtotal - Student Package VAT
                                    </td>
                                    <td class="px-6 py-3 text-sm text-right font-bold text-purple-700">
                                        {{ money(categoryTotals.student_package) }}
                                    </td>
                                </tr>
                            </template>

                            <!-- Other Income Section -->
                            <template v-if="vatData.other_income && vatData.other_income.length > 0">
                                <tr class="bg-amber-50">
                                    <td colspan="2" class="px-6 py-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-amber-700" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="font-bold text-amber-900">4. Other Income VAT</span>
                                            </div>
                                            <Link href="/accounting/income/other" class="text-xs text-amber-700 hover:text-amber-800 hover:underline font-medium">
                                                View Entries →
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="item in vatData.other_income" :key="item.subcategory" class="hover:bg-gray-50">
                                    <td class="px-6 py-3 pl-12 text-sm text-gray-900">
                                        {{ item.subcategory }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">
                                        {{ money(item.vat_amount) }}
                                    </td>
                                </tr>
                                <tr class="bg-amber-100 border-t-2 border-amber-300">
                                    <td class="px-6 py-3 pl-12 text-sm font-bold text-amber-900">
                                        Subtotal - Other Income VAT
                                    </td>
                                    <td class="px-6 py-3 text-sm text-right font-bold text-amber-700">
                                        {{ money(categoryTotals.other_income) }}
                                    </td>
                                </tr>
                            </template>

                            <!-- Empty State -->
                            <tr v-if="!hasAnyVat" class="hover:bg-gray-50">
                                <td colspan="2" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <div class="text-sm font-medium">No VAT entries found for this period</div>
                                    <div class="text-xs text-gray-400 mt-1">Add income entries with VAT to see the summary here</div>
                                </td>
                            </tr>

                            <!-- Grand Total -->
                            <tr v-if="hasAnyVat" class="bg-green-100 border-t-4 border-green-500">
                                <td class="px-6 py-4 text-base font-bold text-gray-900">
                                    TOTAL VAT PAYABLE (All Categories)
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="text-2xl font-bold text-green-700">
                                        {{ money(totalVat) }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VAT Compliance Note -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-1">VAT Compliance & Reconciliation</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li><strong>Output VAT:</strong> This summary shows VAT collected from sales and services (15% standard rate)</li>
                            <li><strong>Monthly Filing:</strong> Submit VAT returns to NBR by the 15th of the following month</li>
                            <li><strong>Reconciliation:</strong> Ensure all income entries with applicable VAT are recorded for accurate tax filing</li>
                            <li><strong>Net VAT:</strong> Calculate Net VAT Payable = Output VAT (shown here) - Input VAT (from expenses)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link href="/accounting/income/travel-tourism" class="bg-green-50 hover:bg-green-100 rounded-lg p-4 border border-green-200 transition-colors">
                    <div class="text-sm text-green-700 font-medium mb-1">Income Sections</div>
                    <div class="font-semibold text-gray-900">View & Edit Income Entries</div>
                </Link>
                <Link href="/accounting/net-profit-after-tax" class="bg-purple-50 hover:bg-purple-100 rounded-lg p-4 border border-purple-200 transition-colors">
                    <div class="text-sm text-purple-700 font-medium mb-1">P&L Summary</div>
                    <div class="font-semibold text-gray-900">Net Profit After Tax</div>
                </Link>
                <Link href="/accounting" class="bg-red-50 hover:bg-red-100 rounded-lg p-4 border border-red-200 transition-colors">
                    <div class="text-sm text-red-700 font-medium mb-1">Dashboard</div>
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
    vatData: Object,
    categoryTotals: Object,
    totalVat: Number,
    vatPayments: Array,
    totalVatPayments: Number,
    vatBalance: Number,
    clientsWithVat: Array,
});

const chalanSlipInput = ref(null);
const isSubmitting = ref(false);
const searchPayments = ref("");

const paymentForm = ref({
    payment_type: 'bulk',
    client_id: null,
    payment_amount: '',
    payment_date: new Date().toISOString().split('T')[0],
    chalan_number: '',
    chalan_slip: null,
    notes: '',
});

const showClientsWithVat = ref(false);

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-BD', { year: 'numeric', month: 'short', day: 'numeric' });
};

const hasAnyVat = computed(() => {
    return (
        (props.vatData.travel_tourism && props.vatData.travel_tourism.length > 0) ||
        (props.vatData.manpower_exporting && props.vatData.manpower_exporting.length > 0) ||
        (props.vatData.student_package && props.vatData.student_package.length > 0) ||
        (props.vatData.other_income && props.vatData.other_income.length > 0)
    );
});

const filteredVatPayments = computed(() => {
    if (!props.vatPayments) return [];
    if (!searchPayments.value) return props.vatPayments;

    const query = searchPayments.value.toLowerCase();
    return props.vatPayments.filter((payment) => {
        return (
            payment.chalan_number?.toLowerCase().includes(query) ||
            payment.client_name?.toLowerCase().includes(query) ||
            payment.payment_type?.toLowerCase().includes(query) ||
            payment.notes?.toLowerCase().includes(query) ||
            payment.payment_date?.toLowerCase().includes(query)
        );
    });
});

const handleChalanSlipChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        paymentForm.value.chalan_slip = file;
    }
};

const submitPayment = () => {
    if (isSubmitting.value) return;

    // Validate chalan number is required
    if (!paymentForm.value.chalan_number || paymentForm.value.chalan_number.trim() === '') {
        alert('Please enter a Chalan Number.');
        return;
    }

    // Auto-set payment amount for bulk payments
    let paymentAmount;
    if (paymentForm.value.payment_type === 'bulk') {
        paymentAmount = props.vatBalance;
    } else {
        paymentAmount = parseFloat(paymentForm.value.payment_amount) || 0;
    }

    // Validate client selection for individual payments
    if (paymentForm.value.payment_type === 'individual') {
        if (!paymentForm.value.client_id) {
            alert('Please select a client for individual payment.');
            return;
        }

        const selectedClient = props.clientsWithVat.find(c => c.id === paymentForm.value.client_id);
        if (selectedClient && paymentAmount > selectedClient.vat_unpaid_amount) {
            alert(`Payment amount ${money(paymentAmount)} exceeds client unpaid VAT ${money(selectedClient.vat_unpaid_amount)}.`);
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

    if (paymentForm.value.chalan_number) {
        formData.append('chalan_number', paymentForm.value.chalan_number);
    }

    if (paymentForm.value.chalan_slip) {
        formData.append('chalan_slip', paymentForm.value.chalan_slip);
    }

    if (paymentForm.value.notes) {
        formData.append('notes', paymentForm.value.notes);
    }

    router.post(`/accounting/vat-summary/${props.period.id}/payment`, formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Reset form
            paymentForm.value = {
                payment_type: 'bulk',
                client_id: null,
                payment_amount: '',
                payment_date: new Date().toISOString().split('T')[0],
                chalan_number: '',
                chalan_slip: null,
                notes: '',
            };

            // Reset file input
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

    router.delete(`/accounting/vat-payment/${paymentId}`, {
        preserveScroll: true,
    });
};
</script>
