<template>
    <div class="py-6 space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-500">Agents</p>
                <h1 class="text-2xl font-bold text-gray-900">Agent Directory</h1>
                <p class="text-sm text-gray-600">See all agents and how many clients each manages.</p>
            </div>
            <Link
                href="/agents/create"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700"
            >
                <span class="text-lg leading-none">+</span>
                Add Agent
            </Link>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-700">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Agent</th>
                            <th class="px-6 py-3 font-semibold">Mobile</th>
                            <th class="px-6 py-3 font-semibold">District</th>
                            <th class="px-6 py-3 font-semibold">Services</th>
                            <th class="px-6 py-3 font-semibold text-right">Clients</th>
                            <th class="px-6 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="agent in agents" :key="agent.id" class="transition hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ agent.name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ agent.mobile || "—" }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ agent.district || "—" }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="service in agent.services || []"
                                        :key="service"
                                        class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700"
                                    >
                                        {{ service }}
                                    </span>
                                    <span v-if="!agent.services || agent.services.length === 0" class="text-xs text-gray-500">—</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-800">
                                    {{ agent.clients_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <IconButton
                                        icon="fa-solid fa-eye"
                                        class="bg-gray-100 text-gray-700 hover:bg-gray-200"
                                        tooltip="View agent"
                                        @click="router.visit(`/agents/${agent.id}`)"
                                    />
                                    <IconButton
                                        icon="fa-solid fa-pen-to-square"
                                        class="bg-blue-600 text-white hover:bg-blue-700"
                                        tooltip="Edit agent"
                                        @click="router.visit(`/agents/${agent.id}/edit`)"
                                    />
                                </div>
                            </td>
                        </tr>
                        <tr v-if="agents.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                No agents yet. <Link href="/agents/create" class="text-blue-600 font-semibold hover:underline">Add the first one</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, router } from "@inertiajs/vue3";
import IconButton from "@/Components/Buttons/IconButton.vue";

const props = defineProps({
    agents: {
        type: Array,
        default: () => [],
    },
});

const agents = props.agents || [];
</script>
