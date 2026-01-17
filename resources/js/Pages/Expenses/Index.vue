<template>
    <Head title="Expenses" />
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

        <!-- Search Section -->
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
                            placeholder="Search by title, category, vendor, or notes..."
                        />
                        <button
                            v-if="searchQuery"
                            @click="searchQuery = ''"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div v-if="searchQuery" class="text-sm text-gray-600">
                    Showing <span class="font-semibold text-gray-900">{{ filteredExpenses.length }}</span> of {{ expenses.length }} expenses
                </div>
            </div>
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
                        <tr v-for="expense in filteredExpenses" :key="expense.id" class="transition hover:bg-gray-50">
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
                        <tr v-if="filteredExpenses.length === 0 && expenses.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                No expenses recorded yet. <Link href="/expenses/create" class="text-blue-600 font-semibold hover:underline">Add your first expense</Link>
                            </td>
                        </tr>
                        <tr v-if="filteredExpenses.length === 0 && expenses.length > 0">
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                No expenses found matching your search.
                                <button @click="searchQuery = ''" class="text-blue-600 font-semibold hover:underline ml-1">Clear search</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import IconButton from "@/Components/Buttons/IconButton.vue";

const props = defineProps({
    expenses: {
        type: Array,
        default: () => [],
    },
});

const expenses = props.expenses || [];
const searchQuery = ref("");

const filteredExpenses = computed(() => {
    if (!searchQuery.value) return expenses;

    const query = searchQuery.value.toLowerCase();
    return expenses.filter((expense) => {
        return (
            expense.title?.toLowerCase().includes(query) ||
            expense.category?.toLowerCase().includes(query) ||
            expense.vendor?.toLowerCase().includes(query) ||
            expense.notes?.toLowerCase().includes(query) ||
            expense.paid_on?.toLowerCase().includes(query)
        );
    });
});

const flash = computed(() => usePage().props.flash || {});

const money = (value) => {
    if (value === null || value === undefined || value === "") return "—";
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(Number(value || 0));
};
</script>
