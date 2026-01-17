<template>
    <Head title="Office Staff" />
    <div class="py-6 space-y-6">
        <div class="overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-800 text-white shadow-xl">
            <div class="px-6 py-8">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-100">Office Staff</p>
                        <h1 class="text-2xl font-bold text-white">Staff Directory</h1>
                        <p class="text-sm text-blue-100">Manage agency office staff members who handle documents.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a
                            :href="route('office-staff.export', { type: 'excel' })"
                            class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:bg-green-700"
                        >
                            Export to Excel
                        </a>
                        <a
                            :href="route('office-staff.export', { type: 'pdf' })"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:bg-red-700"
                        >
                            Export to PDF
                        </a>
                        <Link
                            href="/office-staff/create"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-blue-700 shadow-lg transition hover:shadow-xl hover:scale-105"
                        >
                            <span class="text-lg leading-none">+</span>
                            Add Staff Member
                        </Link>
                    </div>
                </div>
            </div>
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
                            placeholder="Search by name, designation, mobile, or email..."
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
                    Showing <span class="font-semibold text-gray-900">{{ filteredStaff.length }}</span> of {{ staff.length }} staff members
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-700">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Name</th>
                            <th class="px-6 py-3 font-semibold">Designation</th>
                            <th class="px-6 py-3 font-semibold">Contact</th>
                            <th class="px-6 py-3 font-semibold">Joining Date</th>
                            <th class="px-6 py-3 font-semibold">Status</th>
                            <th class="px-6 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="member in filteredStaff" :key="member.id" class="transition hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ member.name }}</div>
                                <div v-if="member.email" class="text-xs text-gray-500">{{ member.email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ member.designation || "—" }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ member.mobile || "—" }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ member.joining_date || "—" }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                    :class="member.status === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-50 text-gray-700'"
                                >
                                    {{ member.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <IconButton
                                        icon="fa-solid fa-pen-to-square"
                                        class="bg-blue-600 text-white hover:bg-blue-700"
                                        tooltip="Edit staff"
                                        @click="router.visit(`/office-staff/${member.id}/edit`)"
                                    />
                                    <IconButton
                                        icon="fa-solid fa-eye"
                                        class="bg-gray-100 text-gray-700 hover:bg-gray-200"
                                        tooltip="View salary"
                                        @click="router.visit(`/office-staff/${member.id}`)"
                                    />
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredStaff.length === 0 && staff.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                No office staff yet. <Link href="/office-staff/create" class="text-blue-600 font-semibold hover:underline">Add the first one</Link>
                            </td>
                        </tr>
                        <tr v-if="filteredStaff.length === 0 && staff.length > 0">
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                No staff found matching your search.
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
    staff: {
        type: Array,
        default: () => [],
    },
});

const staff = props.staff || [];
const searchQuery = ref("");

const filteredStaff = computed(() => {
    if (!searchQuery.value) return staff;

    const query = searchQuery.value.toLowerCase();
    return staff.filter((member) => {
        return (
            member.name?.toLowerCase().includes(query) ||
            member.designation?.toLowerCase().includes(query) ||
            member.mobile?.toLowerCase().includes(query) ||
            member.email?.toLowerCase().includes(query) ||
            member.status?.toLowerCase().includes(query)
        );
    });
});

const flash = computed(() => usePage().props.flash || {});
</script>
