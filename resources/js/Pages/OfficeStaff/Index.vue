<template>
    <div class="py-6 space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-500">Office Staff</p>
                <h1 class="text-2xl font-bold text-gray-900">Staff Directory</h1>
                <p class="text-sm text-gray-600">Manage agency office staff members who handle documents.</p>
            </div>
            <Link
                href="/office-staff/create"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700"
            >
                <span class="text-lg leading-none">+</span>
                Add Staff Member
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
                            <th class="px-6 py-3 font-semibold">Name</th>
                            <th class="px-6 py-3 font-semibold">Designation</th>
                            <th class="px-6 py-3 font-semibold">Contact</th>
                            <th class="px-6 py-3 font-semibold">Joining Date</th>
                            <th class="px-6 py-3 font-semibold">Status</th>
                            <th class="px-6 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="member in staff" :key="member.id" class="transition hover:bg-gray-50">
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
                                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="member.status === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-50 text-gray-700'"
                                >
                                    {{ member.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <IconButton
                                    icon="fa-solid fa-pen-to-square"
                                    class="bg-blue-600 text-white hover:bg-blue-700"
                                    tooltip="Edit staff"
                                    @click="router.visit(`/office-staff/${member.id}/edit`)"
                                />
                            </td>
                        </tr>
                        <tr v-if="staff.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                No office staff yet. <Link href="/office-staff/create" class="text-blue-600 font-semibold hover:underline">Add the first one</Link>
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
    staff: {
        type: Array,
        default: () => [],
    },
});

const flash = computed(() => usePage().props.flash || {});
</script>
