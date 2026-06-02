<template>
    <Head title="Expenses" />
    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <p class="text-[12px] font-bold uppercase tracking-wider text-[#1d4ed8] mb-1">Accounting</p>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">Expenses</h1>
                <p class="text-sm text-gray-500">Track outflows by category and date.</p>
            </div>
            <Link
                href="/expenses/create"
                class="bg-[#1d4ed8] text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-[#1e40af] transition shadow-sm border border-transparent inline-flex items-center gap-2"
            >
                <i class="fa-solid fa-plus"></i>
                Add Expense
            </Link>
        </div>

        <div v-if="flash.success" class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 mb-6 font-medium flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-check-circle text-blue-600"></i>
            {{ flash.success }}
        </div>

        <!-- Search Section -->
        <div class="rounded-[24px] border border-gray-100 bg-white p-6 shadow-[0_2px_12px_rgba(0,0,0,0.02)] mb-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between tracking-tight">
                <div class="flex-1 max-w-md relative">
                    <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="w-full bg-gray-50 border border-gray-200 rounded-full pl-10 pr-10 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1d4ed8] focus:border-transparent transition-all"
                        placeholder="Search by title, category, vendor, or notes..."
                    />
                    <button
                        v-if="searchQuery"
                        @click="searchQuery = ''"
                        class="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center rounded-full bg-gray-200 text-gray-600 hover:bg-gray-300 transition-colors"
                    >
                        <i class="fa-solid fa-times text-xs"></i>
                    </button>
                </div>
                <div v-if="searchQuery" class="text-sm font-medium text-gray-500 bg-gray-50 px-4 py-2 rounded-full border border-gray-100">
                    Showing <span class="font-bold text-[#1d4ed8]">{{ filteredExpenses.length }}</span> of {{ expenses.length }} expenses
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-700">
                    <thead class="text-[11px] text-gray-500 uppercase font-bold tracking-wider bg-gray-50/50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Title</th>
                            <th class="px-6 py-4">Category</th>
                            <th class="px-6 py-4">Paid On</th>
                            <th class="px-6 py-4">Vendor</th>
                            <th class="px-6 py-4 font-bold text-gray-900 text-right">Amount</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="expense in filteredExpenses" :key="expense.id" class="transition-colors hover:bg-gray-50/50">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 mb-1">{{ expense.title }}</div>
                                <p v-if="expense.notes" class="text-xs text-gray-500 border-l-2 border-gray-200 pl-2 mt-1">{{ expense.notes }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-md bg-gray-100 text-gray-600 uppercase tracking-wider" v-if="expense.category">{{ expense.category }}</span>
                                <span v-else class="text-gray-400">—</span>
                            </td>
                            <td class="px-6 py-4 font-medium">{{ expense.paid_on || "—" }}</td>
                            <td class="px-6 py-4">{{ expense.vendor || "—" }}</td>
                            <td class="px-6 py-4 font-bold text-gray-900 text-right text-[15px] tabular-nums">{{ money(expense.amount) }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a
                                        v-if="expense.attachment_url"
                                        :href="expense.attachment_url"
                                        class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors"
                                        title="View Attachment"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        <i class="fa-solid fa-paperclip"></i>
                                    </a>
                                    <button
                                        @click="router.visit(`/expenses/${expense.id}/edit`)"
                                        class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 hover:bg-amber-100 flex items-center justify-center transition-colors"
                                        title="Edit Expense"
                                    >
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredExpenses.length === 0 && expenses.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fa-solid fa-receipt text-gray-300 text-2xl"></i>
                                </div>
                                <div class="font-bold text-gray-900 mb-1">No expenses recorded yet</div>
                                <div class="text-sm font-medium mb-4">Start tracking your outflows to gain accounting insights.</div>
                                <Link href="/expenses/create" class="inline-flex items-center text-[#1d4ed8] font-bold text-sm tracking-wide bg-blue-50 px-4 py-2 rounded-full hover:bg-blue-100 transition-colors">
                                    <i class="fa-solid fa-plus mr-2"></i> Add your first expense
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="filteredExpenses.length === 0 && expenses.length > 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fa-solid fa-search text-gray-300 text-2xl"></i>
                                </div>
                                <div class="font-bold text-gray-900 mb-1">No expenses found matching your search</div>
                                <div class="text-sm font-medium mb-4">Try adjusting your keywords or clearing the search.</div>
                                <button @click="searchQuery = ''" class="inline-flex items-center text-gray-600 font-bold text-sm bg-gray-100 px-4 py-2 rounded-full hover:bg-gray-200 transition-colors">
                                    Clear search
                                </button>
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
