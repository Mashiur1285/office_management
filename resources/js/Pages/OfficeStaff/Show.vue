<template>
    <Head :title="`${staff.name} Salary Summary`" />

    <div class="py-6 space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ staff.name }}</h1>
                <p class="text-sm text-gray-600">Salary summary for {{ month.label }}</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold uppercase text-gray-500">Month</label>
                    <input
                        v-model="selectedMonth"
                        type="month"
                        class="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                        @change="applyMonth"
                    />
                </div>
            </div>
            <Link
                href="/office-staff"
                class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
            >
                ← Back to staff
            </Link>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-blue-100 bg-blue-50 p-5">
                <p class="text-xs font-semibold uppercase text-blue-600">Salary</p>
                <p class="mt-2 text-2xl font-bold text-blue-900">{{ money(summary.salary_total) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-5">
                <p class="text-xs font-semibold uppercase text-emerald-600">Bonus</p>
                <p class="mt-2 text-2xl font-bold text-emerald-900">{{ money(summary.bonus_total) }}</p>
            </div>
            <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-5">
                <p class="text-xs font-semibold uppercase text-indigo-600">Paid</p>
                <p class="mt-2 text-2xl font-bold text-indigo-900">{{ money(summary.paid_total) }}</p>
            </div>
            <div class="rounded-xl border border-rose-100 bg-rose-50 p-5">
                <p class="text-xs font-semibold uppercase text-rose-600">Due</p>
                <p class="mt-2 text-2xl font-bold text-rose-900">{{ money(summary.due_total) }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Salary Entries</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                        <tr>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Subcategory</th>
                            <th class="px-6 py-3">Description</th>
                            <th class="px-6 py-3 text-right">Salary</th>
                            <th class="px-6 py-3 text-right">Bonus</th>
                            <th class="px-6 py-3 text-right">Paid</th>
                            <th class="px-6 py-3 text-right">Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="entry in entries" :key="entry.id">
                            <td class="px-6 py-3 text-gray-600">{{ entry.created_at }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ entry.subcategory }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ entry.description || '—' }}</td>
                            <td class="px-6 py-3 text-right text-gray-900">{{ money(entry.salary_amount) }}</td>
                            <td class="px-6 py-3 text-right text-gray-900">{{ money(entry.bonus_amount) }}</td>
                            <td class="px-6 py-3 text-right text-gray-900">{{ money(entry.paid_amount) }}</td>
                            <td class="px-6 py-3 text-right text-rose-700 font-semibold">{{ money(entry.due_amount) }}</td>
                        </tr>
                        <tr v-if="entries.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                No salary entries for this month.
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
});

const selectedMonth = ref(props.month?.value || '');

const applyMonth = () => {
    router.get(`/office-staff/${props.staff.id}`, { month: selectedMonth.value }, { preserveState: true });
};

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value || 0));
};
</script>
