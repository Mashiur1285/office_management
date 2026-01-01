<template>
    <Head title="VAT Summary" />
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-red-50/30 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">VAT Summary & Reconciliation</h1>
                        <p class="text-sm text-gray-600 mt-1">Period: {{ period.name }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium">
                            <option v-for="p in periods" :key="p.id" :value="p.id" :selected="p.id === period.id">
                                {{ p.name }} ({{ p.type }})
                            </option>
                        </select>
                        <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                            </svg>
                            Export VAT Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Total VAT Summary -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-red-500 p-6">
                <h2 class="text-xl font-bold text-red-700 mb-6">Total VAT Payable (Sales Tax)</h2>
                <div class="bg-green-100 rounded-lg p-6 border-2 border-green-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-sm text-gray-600 mb-1">Total VAT Collected from All Income</div>
                            <div class="text-xl font-bold text-gray-900">Total VAT Payable</div>
                        </div>
                        <div class="text-4xl font-bold text-green-700">
                            {{ money(totalVat) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- VAT File Upload Section -->
            <div class="bg-white rounded-xl shadow-sm border-4 border-blue-500 p-6">
                <h2 class="text-xl font-bold text-blue-700 mb-4">VAT Council File</h2>
                <div class="flex items-center gap-3">
                    <input
                        type="file"
                        ref="vatFileInput"
                        @change="handleFileUpload"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="hidden"
                    />
                    <button
                        @click="$refs.vatFileInput.click()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        {{ period.vat_file ? 'Replace' : 'Upload' }}
                    </button>

                    <!-- Eye Button - Always visible but disabled when no file -->
                    <a
                        v-if="period.vat_file"
                        :href="`/storage/${period.vat_file}`"
                        target="_blank"
                        class="p-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                        title="View File"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </a>
                    <button
                        v-else
                        disabled
                        class="p-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed"
                        title="No file uploaded"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>

                    <!-- Delete Button - Only when file exists -->
                    <button
                        v-if="period.vat_file"
                        @click="deleteFile"
                        class="p-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                        title="Delete File"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Accepted formats: PDF, JPG, JPEG, PNG (Max 10MB)</p>
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

const props = defineProps({
    period: Object,
    periods: Array,
    vatData: Object,
    categoryTotals: Object,
    totalVat: Number,
});

const vatFileInput = ref(null);

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
};

const hasAnyVat = computed(() => {
    return (
        (props.vatData.travel_tourism && props.vatData.travel_tourism.length > 0) ||
        (props.vatData.manpower_exporting && props.vatData.manpower_exporting.length > 0) ||
        (props.vatData.student_package && props.vatData.student_package.length > 0) ||
        (props.vatData.other_income && props.vatData.other_income.length > 0)
    );
});

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('vat_file', file);

    router.post(`/accounting/vat-summary/${props.period.id}/upload`, formData, {
        preserveScroll: true,
        onSuccess: () => {
            if (vatFileInput.value) {
                vatFileInput.value.value = '';
            }
        },
        onError: (errors) => {
            console.error('Upload error:', errors);
            alert(errors.vat_file || 'Failed to upload file');
            if (vatFileInput.value) {
                vatFileInput.value.value = '';
            }
        },
        onFinish: () => {
            console.log('Upload finished');
        }
    });
};

const deleteFile = () => {
    if (!confirm('Are you sure you want to delete this VAT file?')) return;

    router.delete(`/accounting/vat-summary/${props.period.id}/delete-file`);
};
</script>
