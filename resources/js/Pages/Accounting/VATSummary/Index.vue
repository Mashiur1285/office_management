<template>
    <Head title="VAT Summary" />
    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">VAT Summary & Reconciliation</h1>
                <p class="text-sm text-gray-500">Period: {{ period.name }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <select class="px-4 py-2 border border-gray-200 rounded-full text-sm font-medium bg-white text-gray-700 shadow-sm focus:ring-2 focus:ring-[#1e5b43] outline-none">
                    <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                        {{ p.name }} ({{ p.type }})
                    </option>
                </select>
                <a :href="route('accounting.vat-summary.report')" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-file-excel text-green-600"></i>
                    Excel
                </a>
                <a :href="route('accounting.vat-summary.report', { type: 'pdf' })" class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-file-pdf text-red-600"></i>
                    PDF
                </a>
            </div>
        </div>

        <!-- Total VAT Summary with Payments -->
        <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-8 mb-6">
            <h2 class="text-[20px] font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">VAT Payment Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total VAT Payable -->
                <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 flex flex-col justify-center transition-all hover:bg-gray-50 hover:shadow-sm">
                    <div class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Total VAT Collected</div>
                    <div class="text-[32px] font-bold text-gray-800">
                        {{ money(totalVat) }}
                    </div>
                </div>

                <!-- Total Payments -->
                <div class="bg-gray-50/50 rounded-[20px] p-6 border border-gray-100 flex flex-col justify-center transition-all hover:bg-gray-50 hover:shadow-sm">
                    <div class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mb-2">Total Payments Made</div>
                    <div class="text-[32px] font-bold text-[#1e5b43]">
                        {{ money(totalVatPayments) }}
                    </div>
                </div>

                <!-- Balance -->
                <div :class="['rounded-[20px] p-6 border border-gray-100 shadow-[0_4px_12px_rgba(0,0,0,0.05)] transform scale-105 flex flex-col justify-center', vatBalance > 0 ? 'bg-orange-50 border-orange-200' : 'bg-[#1e5b43] text-white']">
                    <div :class="['text-[12px] uppercase tracking-wider font-bold mb-2', vatBalance > 0 ? 'text-gray-500' : 'text-emerald-100/90']">Remaining Balance</div>
                    <div :class="['text-[36px] font-bold leading-none', vatBalance > 0 ? 'text-orange-600' : 'text-white']">
                        {{ money(vatBalance) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- VAT Payment Form -->
        <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 p-8 mb-6">
            <h2 class="text-[20px] font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Record VAT Payment</h2>
            <form @submit.prevent="submitPayment" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-2 uppercase tracking-wider">Payment Type *</label>
                        <select
                            v-model="paymentForm.payment_type"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm font-medium bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent transition-colors outline-none cursor-pointer"
                            required
                        >
                            <option value="bulk">Bulk Payment (All Clients)</option>
                            <option value="individual">Individual Client Payment</option>
                        </select>
                    </div>

                    <div v-if="paymentForm.payment_type === 'individual'">
                        <label class="block text-[11px] font-bold text-gray-700 mb-2 uppercase tracking-wider">Select Client *</label>
                        <select
                            v-model="paymentForm.client_id"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm font-medium bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent transition-colors outline-none cursor-pointer"
                            required
                        >
                            <option :value="null">-- Select Client --</option>
                            <option v-for="client in clientsWithVat.filter(c => !c.vat_paid)" :key="client.id" :value="client.id">
                                {{ client.name }} (Unpaid VAT: {{ money(client.vat_unpaid_amount) }})
                            </option>
                        </select>
                    </div>

                    <div v-if="paymentForm.payment_type === 'individual'">
                        <label class="block text-[11px] font-bold text-gray-700 mb-2 uppercase tracking-wider">Payment Amount *</label>
                        <input
                            v-model="paymentForm.payment_amount"
                            type="number"
                            step="0.01"
                            required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm font-medium bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent transition-colors outline-none"
                            placeholder="0.00"
                        />
                    </div>

                    <div v-else class="p-5 bg-gray-50 border border-gray-200 rounded-[16px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[12px] uppercase tracking-wider font-bold text-gray-500">Total Payment Amount:</span>
                            <span class="text-[24px] font-bold text-[#1e5b43]">{{ money(vatBalance) }}</span>
                        </div>
                        <p class="text-xs text-gray-500 font-medium">This amount will be distributed across all clients with unpaid VAT</p>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-2 uppercase tracking-wider">Payment Date *</label>
                        <div class="theme-datepicker">
                            <VueDatePicker
                                v-model="paymentForm.payment_date"
                                :enable-time-picker="false"
                                model-type="yyyy-MM-dd"
                                required
                                class="w-full"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-2 uppercase tracking-wider">Chalan Number *</label>
                        <input
                            v-model="paymentForm.chalan_number"
                            type="text"
                            required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm font-medium bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent transition-colors outline-none"
                            placeholder="Enter chalan number"
                        />
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-2 uppercase tracking-wider">Chalan Slip (Optional)</label>
                        <input
                            ref="chalanSlipInput"
                            type="file"
                            @change="handleChalanSlipChange"
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full px-4 py-3 border border-gray-200 rounded-[12px] text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#1e5b43] file:text-white hover:file:bg-[#164230]"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-bold text-gray-700 mb-2 uppercase tracking-wider">Notes</label>
                        <textarea
                            v-model="paymentForm.notes"
                            rows="3"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent transition-colors outline-none resize-none"
                            placeholder="Any additional notes..."
                        ></textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="px-8 py-3 bg-[#1e5b43] text-white rounded-full hover:bg-[#164230] transition-colors font-bold text-sm shadow-[0_4px_12px_rgba(30,91,67,0.2)] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ isSubmitting ? 'Saving...' : 'Record Payment' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Clients with VAT Receivable -->
        <div v-if="clientsWithVat && clientsWithVat.length > 0" class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 overflow-hidden mb-6">
            <button
                @click="showClientsWithVat = !showClientsWithVat"
                class="w-full p-6 flex items-center justify-between hover:bg-gray-50 transition-colors border-b border-gray-100"
            >
                <div class="text-left">
                    <h2 class="text-[20px] font-bold text-gray-900">Clients with VAT Receivable</h2>
                    <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mt-1">{{ clientsWithVat.length }} client(s) with VAT due</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 transition-transform duration-300" :class="{ 'rotate-180': showClientsWithVat }">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>

            <div v-show="showClientsWithVat" class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-[11px] text-gray-500 uppercase font-bold tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Client Name</th>
                            <th class="px-6 py-4 text-right">Total VAT</th>
                            <th class="px-6 py-4 text-right">Paid</th>
                            <th class="px-6 py-4 text-right">Unpaid</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4">Payment Info</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="client in clientsWithVat" :key="client.id" :class="client.vat_paid ? 'bg-emerald-50/30' : 'hover:bg-gray-50/50 transition-colors'">
                            <td class="px-6 py-4 font-bold text-gray-900 border-x-0">
                                {{ client.name }}
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-900 text-right">
                                {{ money(client.total_vat_receivable) }}
                            </td>
                            <td class="px-6 py-4 text-[#1e5b43] font-semibold text-right">
                                {{ money(client.vat_paid_amount) }}
                            </td>
                            <td class="px-6 py-4 font-bold text-right" :class="client.vat_paid ? 'text-gray-400' : 'text-orange-600'">
                                {{ money(client.vat_unpaid_amount) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span v-if="client.vat_paid" class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-md text-[11px] font-bold uppercase tracking-wider">
                                    Fully Paid
                                </span>
                                <span v-else-if="client.vat_paid_amount > 0" class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-md text-[11px] font-bold uppercase tracking-wider">
                                    Partially Paid
                                </span>
                                <span v-else class="px-3 py-1 bg-orange-100 text-orange-800 rounded-md text-[11px] font-bold uppercase tracking-wider">
                                    Pending
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                <div v-if="client.vat_paid_amount > 0">
                                    <div v-if="client.vat_chalan_number" class="text-xs mb-0.5"><span class="font-semibold text-gray-700">Chalan:</span> {{ client.vat_chalan_number }}</div>
                                    <div v-if="client.vat_payment_date" class="text-xs"><span class="font-semibold text-gray-700">Date:</span> {{ formatDate(client.vat_payment_date) }}</div>
                                </div>
                                <div v-else class="text-gray-400">—</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- VAT Payment History -->
        <div v-if="vatPayments && vatPayments.length > 0" class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-100 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between bg-gray-50/50">
                <div>
                    <h2 class="text-[20px] font-bold text-gray-900">VAT Payment History</h2>
                    <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mt-1">All recorded payments for this period</p>
                </div>
                <div class="flex-1 max-w-md relative">
                    <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input
                        v-model="searchPayments"
                        type="text"
                        class="w-full bg-white border border-gray-200 rounded-full pl-10 pr-10 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1e5b43] focus:border-transparent transition-all shadow-sm"
                        placeholder="Search by challan number, client, date..."
                    />
                    <button
                        v-if="searchPayments"
                        @click="searchPayments = ''"
                        class="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 transition-colors"
                    >
                        <font-awesome-icon icon="xmark" class="text-xs" />
                    </button>
                </div>
            </div>
            <div v-if="searchPayments" class="bg-blue-50 px-6 py-2 border-b border-blue-100 text-sm text-blue-800">
                Showing <span class="font-bold">{{ filteredVatPayments.length }}</span> of {{ vatPayments.length }} payments
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-[11px] text-gray-500 uppercase font-bold tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">Client</th>
                            <th class="px-6 py-4">Amount</th>
                            <th class="px-6 py-4">Chalan Number</th>
                            <th class="px-6 py-4">Notes</th>
                            <th class="px-6 py-4 text-center">Chalan Slip</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="payment in filteredVatPayments" :key="payment.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900 border-x-0">
                                {{ formatDate(payment.payment_date) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-md text-[11px] font-bold uppercase tracking-wider">
                                    {{ payment.payment_type === 'bulk' ? 'Bulk' : 'Individual' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 font-medium">
                                {{ payment.client_name || '—' }}
                            </td>
                            <td class="px-6 py-4 font-bold text-[#1e5b43]">
                                {{ money(payment.payment_amount) }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-gray-600 font-semibold bg-gray-50 rounded select-all inline-block mt-2 px-1">
                                {{ payment.chalan_number || '—' }}
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ payment.notes || '—' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a
                                    v-if="payment.chalan_slip"
                                    :href="`/storage/${payment.chalan_slip}`"
                                    target="_blank"
                                    class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center mx-auto transition-colors"
                                    title="View Chalan Slip"
                                >
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>
                                <span v-else class="text-sm text-gray-300">—</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button
                                    @click="deletePaymentRecord(payment.id)"
                                    class="w-8 h-8 rounded-full bg-gray-50 text-red-500 hover:bg-red-50 flex items-center justify-center mx-auto transition-colors"
                                    title="Delete Payment"
                                >
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filteredVatPayments.length === 0 && vatPayments.length > 0">
                            <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">
                                <i class="fa-solid fa-search text-gray-300 text-2xl mb-3 block"></i>
                                No payments found matching your search.
                                <button @click="searchPayments = ''" class="text-[#1e5b43] font-bold hover:underline ml-1">Clear search</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- VAT Breakdown by Category -->
        <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 overflow-hidden mb-6">
            <div class="p-6 border-b border-gray-100 flex flex-col justify-center bg-gray-50/50">
                <h2 class="text-[20px] font-bold text-gray-900">Detailed VAT Breakdown</h2>
                <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mt-1">Output VAT from all income sources</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-[11px] text-gray-500 uppercase font-bold tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Description</th>
                            <th class="px-6 py-4 text-right">VAT Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <!-- Travel & Tourism Section -->
                        <template v-if="vatData.travel_tourism && vatData.travel_tourism.length > 0">
                            <tr class="bg-emerald-50/30">
                                <td colspan="2" class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-plane text-[#1e5b43]"></i>
                                            <span class="font-bold text-gray-900">1. Travel & Tourism Income VAT</span>
                                        </div>
                                        <Link href="/accounting/income/travel-tourism" class="text-xs text-[#1e5b43] hover:text-[#164230] hover:underline font-bold">
                                            View Entries →
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="item in vatData.travel_tourism" :key="item.subcategory" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 pl-12 text-sm text-gray-600 font-medium">
                                    {{ item.subcategory }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-gray-900">
                                    {{ money(item.vat_amount) }}
                                </td>
                            </tr>
                            <tr class="bg-gray-50/50 border-t border-gray-100">
                                <td class="px-6 py-4 pl-12 text-[11px] uppercase tracking-wider font-bold text-gray-500">
                                    Subtotal - Travel & Tourism VAT
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-[#1e5b43]">
                                    {{ money(categoryTotals.travel_tourism) }}
                                </td>
                            </tr>
                        </template>

                        <!-- Manpower Exporting Section -->
                        <template v-if="vatData.manpower_exporting && vatData.manpower_exporting.length > 0">
                            <tr class="bg-emerald-50/30">
                                <td colspan="2" class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-users text-[#1e5b43]"></i>
                                            <span class="font-bold text-gray-900">2. Manpower Exporting Income VAT</span>
                                        </div>
                                        <Link href="/accounting/income/manpower" class="text-xs text-[#1e5b43] hover:text-[#164230] hover:underline font-bold">
                                            View Entries →
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="item in vatData.manpower_exporting" :key="item.subcategory" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 pl-12 text-sm text-gray-600 font-medium">
                                    {{ item.subcategory }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-gray-900">
                                    {{ money(item.vat_amount) }}
                                </td>
                            </tr>
                            <tr class="bg-gray-50/50 border-t border-gray-100">
                                <td class="px-6 py-4 pl-12 text-[11px] uppercase tracking-wider font-bold text-gray-500">
                                    Subtotal - Manpower Exporting VAT
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-[#1e5b43]">
                                    {{ money(categoryTotals.manpower_exporting) }}
                                </td>
                            </tr>
                        </template>

                        <!-- Student Package Section -->
                        <template v-if="vatData.student_package && vatData.student_package.length > 0">
                            <tr class="bg-emerald-50/30">
                                <td colspan="2" class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-graduation-cap text-[#1e5b43]"></i>
                                            <span class="font-bold text-gray-900">3. Student Package Income VAT</span>
                                        </div>
                                        <Link href="/accounting/income/student" class="text-xs text-[#1e5b43] hover:text-[#164230] hover:underline font-bold">
                                            View Entries →
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="item in vatData.student_package" :key="item.subcategory" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 pl-12 text-sm text-gray-600 font-medium">
                                    {{ item.subcategory }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-gray-900">
                                    {{ money(item.vat_amount) }}
                                </td>
                            </tr>
                            <tr class="bg-gray-50/50 border-t border-gray-100">
                                <td class="px-6 py-4 pl-12 text-[11px] uppercase tracking-wider font-bold text-gray-500">
                                    Subtotal - Student Package VAT
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-[#1e5b43]">
                                    {{ money(categoryTotals.student_package) }}
                                </td>
                            </tr>
                        </template>

                        <!-- Other Income Section -->
                        <template v-if="vatData.other_income && vatData.other_income.length > 0">
                            <tr class="bg-emerald-50/30">
                                <td colspan="2" class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-layer-group text-[#1e5b43]"></i>
                                            <span class="font-bold text-gray-900">4. Other Income VAT</span>
                                        </div>
                                        <Link href="/accounting/income/other" class="text-xs text-[#1e5b43] hover:text-[#164230] hover:underline font-bold">
                                            View Entries →
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="item in vatData.other_income" :key="item.subcategory" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 pl-12 text-sm text-gray-600 font-medium">
                                    {{ item.subcategory }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-gray-900">
                                    {{ money(item.vat_amount) }}
                                </td>
                            </tr>
                            <tr class="bg-gray-50/50 border-t border-gray-100">
                                <td class="px-6 py-4 pl-12 text-[11px] uppercase tracking-wider font-bold text-gray-500">
                                    Subtotal - Other Income VAT
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-[#1e5b43]">
                                    {{ money(categoryTotals.other_income) }}
                                </td>
                            </tr>
                        </template>

                        <!-- Empty State -->
                        <tr v-if="!hasAnyVat" class="hover:bg-gray-50/50">
                            <td colspan="2" class="px-6 py-12 text-center text-gray-500">
                                <i class="fa-solid fa-folder-open text-gray-300 text-3xl mb-3 block"></i>
                                <div class="text-sm font-bold text-gray-900">No VAT entries found for this period</div>
                                <div class="text-xs text-gray-400 mt-1 font-medium">Add income entries with VAT to see the summary here</div>
                            </td>
                        </tr>

                        <!-- Grand Total -->
                        <tr v-if="hasAnyVat" class="bg-[#1e5b43] text-white rounded-b-[24px]">
                            <td class="px-6 py-5 text-[14px] uppercase tracking-wider font-bold">
                                Total VAT Payable (All Categories)
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="text-[28px] font-bold text-emerald-100/90 leading-none">
                                    {{ money(totalVat) }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- VAT Compliance Note -->
        <div class="bg-blue-50/50 rounded-[20px] shadow-sm border border-blue-100 p-6 mb-8 flex items-start gap-4 transition-all hover:bg-blue-50">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                <i class="fa-solid fa-circle-info text-xl"></i>
            </div>
            <div>
                <h4 class="font-bold text-blue-900 mb-2">VAT Compliance & Reconciliation</h4>
                <ul class="text-sm text-blue-800/80 space-y-1.5 font-medium list-disc ml-4">
                    <li><strong class="text-blue-900">Output VAT:</strong> This summary shows VAT collected from sales and services (15% standard rate)</li>
                    <li><strong class="text-blue-900">Monthly Filing:</strong> Submit VAT returns to NBR by the 15th of the following month</li>
                    <li><strong class="text-blue-900">Reconciliation:</strong> Ensure all income entries with applicable VAT are recorded for accurate tax filing</li>
                    <li><strong class="text-blue-900">Net VAT:</strong> Calculate Net VAT Payable = Output VAT (shown here) - Input VAT (from expenses)</li>
                </ul>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pb-8">
            <Link href="/accounting/income/travel-tourism" class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-[#1e5b43]/20 p-6 flex flex-col justify-center text-center group hover:border-[#1e5b43]/40 transition-colors relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-[#1e5b43]/5 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                <div class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Income Sections</div>
                <div class="font-bold text-gray-900 group-hover:text-[#1e5b43] transition-colors">View & Edit Income Entries</div>
            </Link>
            <Link href="/accounting/net-profit-after-tax" class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-[#1e5b43]/20 p-6 flex flex-col justify-center text-center group hover:border-[#1e5b43]/40 transition-colors relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-[#1e5b43]/5 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                <div class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">P&L Summary</div>
                <div class="font-bold text-gray-900 group-hover:text-[#1e5b43] transition-colors">Net Profit After Tax</div>
            </Link>
            <Link href="/accounting" class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-[#1e5b43]/20 p-6 flex items-center justify-between group hover:border-[#1e5b43]/40 transition-colors">
                <div>
                    <div class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Dashboard</div>
                    <div class="font-bold text-gray-900 group-hover:text-[#1e5b43] transition-colors">Accounting Home</div>
                </div>
                <i class="fa-solid fa-house text-gray-300 group-hover:text-[#1e5b43] transition-colors"></i>
            </Link>
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
