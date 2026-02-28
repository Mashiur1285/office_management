<template>
    <Head title="Office Staff" />
    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans">
        
        <!-- Main Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">Office Staff</h1>
                <p class="text-sm text-gray-500">Manage agency office staff members who handle documents.</p>
            </div>
            <div class="flex items-center gap-3">
                <a
                    :href="route('office-staff.export', { type: 'excel' })"
                    class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2"
                >
                    <font-awesome-icon icon="file-excel" class="w-3.5 h-3.5 text-green-600" />
                    Excel
                </a>
                <a
                    :href="route('office-staff.export', { type: 'pdf' })"
                    class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2"
                >
                    <font-awesome-icon icon="file-pdf" class="w-3.5 h-3.5 text-red-600" />
                    PDF
                </a>
                <Link
                    href="/office-staff/create"
                    class="bg-[#1e5b43] text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-[#164230] transition flex items-center gap-2 shadow-sm"
                >
                    <font-awesome-icon icon="plus" class="w-3.5 h-3.5" />
                    Add Staff Member
                </Link>
            </div>
        </div>

        <div v-if="flash.success" class="mb-6 flex items-center gap-3 rounded-[16px] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800 shadow-sm animate-pulse" role="alert">
            <font-awesome-icon icon="check-circle" class="w-5 h-5" />
            <div class="flex-1">
                <span class="font-bold">Success:</span>
                <span class="ml-1">{{ flash.success }}</span>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white rounded-[24px] border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)] p-5 mb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-gray-400 bg-gray-50/80 rounded-full px-4 py-2.5 w-full md:w-96 border border-gray-100">
                <font-awesome-icon icon="magnifying-glass" class="w-4 h-4" />
                <input
                    v-model="searchQuery"
                    type="text"
                    class="border-none bg-transparent focus:ring-0 text-sm w-full text-gray-700 placeholder-gray-400 outline-none"
                    placeholder="Search by name, designation, mobile, or email..."
                />
                <button
                    v-if="searchQuery"
                    @click="searchQuery = ''"
                    class="text-gray-400 hover:text-gray-600 outline-none"
                >
                    <font-awesome-icon icon="times" class="w-4 h-4" />
                </button>
            </div>
            <div v-if="searchQuery" class="text-sm text-gray-600">
                Showing <span class="font-semibold text-gray-900">{{ filteredStaff.length }}</span> of {{ staff.length }} staff members
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-[24px] border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead class="bg-gray-50/50 text-[11px] uppercase text-gray-400 font-bold tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 whitespace-nowrap">Name</th>
                            <th class="px-6 py-4 whitespace-nowrap">Designation</th>
                            <th class="px-6 py-4 whitespace-nowrap">Contact</th>
                            <th class="px-6 py-4 whitespace-nowrap">Joining Date</th>
                            <th class="px-6 py-4 whitespace-nowrap">Status</th>
                            <th class="px-6 py-4 whitespace-nowrap text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="member in filteredStaff" :key="member.id" class="transition-colors hover:bg-gray-50/50 group">
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
                            <td class="px-6 py-4 text-right border-l border-transparent group-hover:border-gray-100">
                                <div class="flex items-center justify-end gap-1.5 transition-opacity">
                                    <button @click="router.visit(`/office-staff/${member.id}`)" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-[#1e5b43] hover:bg-emerald-50 transition" title="View">
                                        <font-awesome-icon icon="eye" class="w-3.5 h-3.5" />
                                    </button>
                                    <button @click="router.visit(`/office-staff/${member.id}/edit`)" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-amber-500 hover:bg-amber-50 transition" title="Edit">
                                        <font-awesome-icon icon="edit" class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Empty States -->
                        <tr v-if="filteredStaff.length === 0 && staff.length === 0">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4 border border-gray-100">
                                    <font-awesome-icon icon="users" class="w-6 h-6 text-gray-300" />
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">No office staff yet</h3>
                                <p class="text-sm text-gray-500 mb-5">Get started by adding your first staff member</p>
                                <Link
                                    href="/office-staff/create"
                                    class="bg-[#1e5b43] text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-[#164230] transition inline-flex items-center gap-2"
                                >
                                    <font-awesome-icon icon="plus" class="w-3.5 h-3.5" />
                                    Add First Staff Member
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="filteredStaff.length === 0 && staff.length > 0">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4 border border-gray-100">
                                    <font-awesome-icon icon="search" class="w-6 h-6 text-gray-300" />
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">No results found</h3>
                                <p class="text-sm text-gray-500 mb-5">Try adjusting your search to find what you're looking for</p>
                                <button
                                    @click="searchQuery = ''"
                                    class="border border-gray-200 text-gray-700 bg-white px-5 py-2 hover:bg-gray-50 rounded-full font-semibold text-sm transition"
                                >
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
