<template>
    <Head title="Agents" />
    <div class="py-6 space-y-6">
        <div class="overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-800 text-white shadow-xl">
            <div class="px-6 py-8">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-100">Agents</p>
                        <h1 class="text-2xl font-bold text-white">Agent Directory</h1>
                        <p class="text-sm text-blue-100">See all agents and how many clients each manages.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a
                            :href="route('agents.export', { type: 'excel' })"
                            class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:bg-green-700"
                        >
                            Export to Excel
                        </a>
                        <a
                            :href="route('agents.export', { type: 'pdf' })"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:bg-red-700"
                        >
                            Export to PDF
                        </a>
                        <Link
                            href="/agents/create"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-blue-700 shadow-lg transition hover:shadow-xl hover:scale-105"
                        >
                            <span class="text-lg leading-none">+</span>
                            Add Agent
                        </Link>
                    </div>
                </div>
            </div>
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
                            placeholder="Search by name, mobile, or district..."
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
                    Showing <span class="font-semibold text-gray-900">{{ filteredAgents.length }}</span> of {{ agents.length }} agents
                </div>
            </div>
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
                        <tr v-for="agent in filteredAgents" :key="agent.id" class="transition hover:bg-gray-50">
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
                                    <IconButton
                                        icon="fa-solid fa-trash"
                                        extraClass="bg-red-100 text-red-600 hover:bg-red-200"
                                        tooltip="Delete agent"
                                        @click="confirmDelete(agent)"
                                    />
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredAgents.length === 0 && agents.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                No agents yet. <Link href="/agents/create" class="text-blue-600 font-semibold hover:underline">Add the first one</Link>
                            </td>
                        </tr>
                        <tr v-if="filteredAgents.length === 0 && agents.length > 0">
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                No agents found matching your search.
                                <button @click="searchQuery = ''" class="text-blue-600 font-semibold hover:underline ml-1">Clear search</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
        <div
            v-if="deleteModal.show"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <div
                class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                @click="deleteModal.show = false"
            ></div>
            <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl">
                <div class="p-6">
                    <div class="flex justify-center mb-4">
                        <div class="rounded-full bg-red-100 p-4">
                            <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-center text-lg font-bold text-gray-900 mb-2">Are you sure?</h3>
                    <p class="text-center text-sm text-gray-500 mb-1">You are about to delete</p>
                    <p class="text-center text-base font-semibold text-gray-800 mb-4">"{{ deleteModal.name }}"</p>
                    <p class="text-center text-xs text-red-500 mb-6">This action cannot be undone.</p>
                    <div class="flex gap-3">
                        <button
                            @click="deleteModal.show = false"
                            class="flex-1 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition"
                        >Cancel</button>
                        <button
                            @click="doDelete"
                            class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition"
                        >Yes, Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import IconButton from "@/Components/Buttons/IconButton.vue";

const props = defineProps({
    agents: {
        type: Array,
        default: () => [],
    },
});

const agents = computed(() => props.agents || []);
const searchQuery = ref("");

const filteredAgents = computed(() => {
    if (!searchQuery.value) return agents.value;

    const query = searchQuery.value.toLowerCase();
    return agents.value.filter((agent) => {
        const mobile = agent.mobile ? String(agent.mobile).toLowerCase() : "";
        const services = agent.services ? agent.services.join(" ").toLowerCase() : "";
        return (
            agent.name?.toLowerCase().includes(query) ||
            mobile.includes(query) ||
            agent.district?.toLowerCase().includes(query) ||
            services.includes(query)
        );
    });
});

const deleteModal = ref({ show: false, id: null, name: '' });

const confirmDelete = (agent) => {
    deleteModal.value = { show: true, id: agent.id, name: agent.name };
};

const doDelete = () => {
    router.delete(`/agents/${deleteModal.value.id}`, {
        preserveScroll: true,
        onFinish: () => { deleteModal.value.show = false; },
    });
};
</script>
