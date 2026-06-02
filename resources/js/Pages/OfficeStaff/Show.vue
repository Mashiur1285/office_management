<template>
    <Head :title="`${staff.name} Salary Summary`" />

    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">{{ staff.name }}</h1>
                <p class="text-sm text-gray-500">Salary summary for {{ month.label }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 bg-white rounded-full px-4 py-2 border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
                    <label class="text-xs font-bold uppercase tracking-wider text-gray-400">Month</label>
                    <input
                        v-model="selectedMonth"
                        type="month"
                        class="border-none bg-transparent focus:ring-0 text-sm py-1 font-semibold text-gray-700 outline-none w-auto"
                        @change="applyMonth"
                    />
                </div>
                <Link
                    :href="props.readOnly ? '/database/office-staff' : '/office-staff'"
                    class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2"
                >
                    <font-awesome-icon icon="arrow-left" class="w-3.5 h-3.5" />
                    Back to Staff
                </Link>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="bg-white rounded-[24px] border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)] p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
                    <font-awesome-icon icon="money-bill-wave" class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Salary</p>
                    <p class="text-xl font-bold text-gray-900">{{ money(summary.salary_total) }}</p>
                </div>
            </div>
            <div class="bg-white rounded-[24px] border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)] p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
                    <font-awesome-icon icon="gift" class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Bonus</p>
                    <p class="text-xl font-bold text-gray-900">{{ money(summary.bonus_total) }}</p>
                </div>
            </div>
            <div class="bg-white rounded-[24px] border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)] p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 flex-shrink-0">
                    <font-awesome-icon icon="check-circle" class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Paid</p>
                    <p class="text-xl font-bold text-gray-900">{{ money(summary.paid_total) }}</p>
                </div>
            </div>
            <div class="bg-white rounded-[24px] border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)] p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-rose-50 flex items-center justify-center text-rose-600 flex-shrink-0">
                    <font-awesome-icon icon="exclamation-circle" class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Due</p>
                    <p class="text-xl font-bold text-rose-600">{{ money(summary.due_total) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[24px] border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)] overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Salary Entries</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm border-collapse">
                    <thead class="bg-gray-50/50 text-[11px] uppercase text-gray-400 font-bold tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 whitespace-nowrap">Date</th>
                            <th class="px-6 py-4 whitespace-nowrap">Subcategory</th>
                            <th class="px-6 py-4 whitespace-nowrap">Description</th>
                            <th class="px-6 py-4 whitespace-nowrap text-right">Salary</th>
                            <th class="px-6 py-4 whitespace-nowrap text-right">Bonus</th>
                            <th class="px-6 py-4 whitespace-nowrap text-right">Paid</th>
                            <th class="px-6 py-4 whitespace-nowrap text-right">Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="entry in entries" :key="entry.id" class="transition-colors hover:bg-gray-50/50 group">
                            <td class="px-6 py-3 text-gray-600">{{ entry.created_at }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ entry.subcategory }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ entry.description || '—' }}</td>
                            <td class="px-6 py-3 text-right text-gray-900">{{ money(entry.salary_amount) }}</td>
                            <td class="px-6 py-3 text-right text-gray-900">{{ money(entry.bonus_amount) }}</td>
                            <td class="px-6 py-3 text-right text-gray-900">{{ money(entry.paid_amount) }}</td>
                            <td class="px-6 py-3 text-right text-rose-700 font-semibold">{{ money(entry.due_amount) }}</td>
                        </tr>
                        <tr v-if="entries.length === 0">
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4 border border-gray-100">
                                    <font-awesome-icon icon="receipt" class="w-6 h-6 text-gray-300" />
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">No salary entries</h3>
                                <p class="text-sm text-gray-500 mb-5">There are no salary entries recorded for this month.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    staff: Object,
    period: Object,
    month: Object,
    summary: Object,
    entries: Array,
    readOnly: {
        type: Boolean,
        default: false,
    },
});

const selectedMonth = ref(props.month?.value || '');

const applyMonth = () => {
    const baseUrl = props.readOnly ? `/database/office-staff/${props.staff.id}` : `/office-staff/${props.staff.id}`;
    router.get(baseUrl, { month: selectedMonth.value }, { preserveState: true });
};

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value || 0));
};
</script>
