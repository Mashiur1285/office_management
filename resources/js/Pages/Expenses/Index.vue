<template>
    <div class="py-6 space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-500">Accounting</p>
                <h1 class="text-2xl font-bold text-gray-900">Expenses</h1>
                <p class="text-sm text-gray-600">Track outflows by category and date.</p>
            </div>
            <Link
                href="/expenses/create"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700"
            >
                <span class="text-lg leading-none">+</span>
                Add Expense
            </Link>
        </div>

        <div v-if="flash.success" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ flash.success }}
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-700">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Title</th>
                            <th class="px-6 py-3 font-semibold">Category</th>
                            <th class="px-6 py-3 font-semibold">Paid On</th>
                            <th class="px-6 py-3 font-semibold">Vendor</th>
                            <th class="px-6 py-3 font-semibold">Amount</th>
                            <th class="px-6 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="expense in expenses" :key="expense.id" class="transition hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ expense.title }}</div>
                                <p v-if="expense.notes" class="text-xs text-gray-500">{{ expense.notes }}</p>
                            </td>
                            <td class="px-6 py-4">{{ expense.category || "—" }}</td>
                            <td class="px-6 py-4">{{ expense.paid_on || "—" }}</td>
                            <td class="px-6 py-4">{{ expense.vendor || "—" }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ money(expense.amount) }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        v-if="expense.attachment_url"
                                        :href="expense.attachment_url"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        Attachment
                                    </a>
                                    <IconButton
                                        icon="fa-solid fa-pen-to-square"
                                        class="bg-blue-600 text-white hover:bg-blue-700"
                                        tooltip="Edit expense"
                                        @click="router.visit(`/expenses/${expense.id}/edit`)"
                                    />
                                </div>
                            </td>
                        </tr>
                        <tr v-if="expenses.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                No expenses recorded yet. <Link href="/expenses/create" class="text-blue-600 font-semibold hover:underline">Add your first expense</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import IconButton from "@/Components/Buttons/IconButton.vue";

const props = defineProps({
    expenses: {
        type: Array,
        default: () => [],
    },
});

const flash = computed(() => usePage().props.flash || {});

const money = (value) => {
    if (value === null || value === undefined || value === "") return "—";
    return new Intl.NumberFormat("en-US", { style: "currency", currency: "BDT" }).format(Number(value || 0));
};

const expenses = props.expenses || [];
</script>
