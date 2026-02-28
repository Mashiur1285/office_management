<template>
    <Head title="Agents" />
    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans">
        
        <!-- Main Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">Agents</h1>
                <p class="text-sm text-gray-500">See all agents and how many clients each manages.</p>
            </div>
            <div class="flex items-center gap-3">
                <a
                    :href="route('agents.export', { type: 'excel' })"
                    class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2"
                >
                    <font-awesome-icon icon="file-excel" class="w-3.5 h-3.5 text-green-600" />
                    Excel
                </a>
                <a
                    :href="route('agents.export', { type: 'pdf' })"
                    class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center gap-2"
                >
                    <font-awesome-icon icon="file-pdf" class="w-3.5 h-3.5 text-red-600" />
                    PDF
                </a>
                <Link
                    href="/agents/create"
                    class="bg-[#1e5b43] text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-[#164230] transition flex items-center gap-2 shadow-sm"
                >
                    <font-awesome-icon icon="plus" class="w-3.5 h-3.5" />
                    Add Agent
                </Link>
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
                    placeholder="Search by name, mobile, or district..."
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
                Showing <span class="font-semibold text-gray-900">{{ filteredAgents.length }}</span> of {{ agents.length }} agents
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-[24px] border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.02)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead class="bg-gray-50/50 text-[11px] uppercase text-gray-400 font-bold tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 whitespace-nowrap">Agent</th>
                            <th class="px-6 py-4 whitespace-nowrap">Mobile</th>
                            <th class="px-6 py-4 whitespace-nowrap">District</th>
                            <th class="px-6 py-4 whitespace-nowrap">Services</th>
                            <th class="px-6 py-4 whitespace-nowrap text-right">Clients</th>
                            <th class="px-6 py-4 whitespace-nowrap text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="agent in filteredAgents" :key="agent.id" class="transition-colors hover:bg-gray-50/50 group">
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
                            <td class="px-6 py-4 text-right border-l border-transparent group-hover:border-gray-100">
                                <div class="flex items-center justify-end gap-1.5 transition-opacity">
                                    <button @click="router.visit(`/agents/${agent.id}`)" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-[#1e5b43] hover:bg-emerald-50 transition" title="View">
                                        <font-awesome-icon icon="eye" class="w-3.5 h-3.5" />
                                    </button>
                                    <button @click="router.visit(`/agents/${agent.id}/edit`)" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-amber-500 hover:bg-amber-50 transition" title="Edit">
                                        <font-awesome-icon icon="edit" class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Empty States -->
                        <tr v-if="filteredAgents.length === 0 && agents.length === 0">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4 border border-gray-100">
                                    <font-awesome-icon icon="address-card" class="w-6 h-6 text-gray-300" />
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">No agents yet</h3>
                                <p class="text-sm text-gray-500 mb-5">Get started by adding your first agent</p>
                                <Link
                                    href="/agents/create"
                                    class="bg-[#1e5b43] text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-[#164230] transition inline-flex items-center gap-2"
                                >
                                    <font-awesome-icon icon="plus" class="w-3.5 h-3.5" />
                                    Add First Agent
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="filteredAgents.length === 0 && agents.length > 0">
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
import { Head, Link, router } from "@inertiajs/vue3";
import IconButton from "@/Components/Buttons/IconButton.vue";

const props = defineProps({
    agents: {
        type: Array,
        default: () => [],
    },
});

const agents = props.agents || [];
const searchQuery = ref("");

const filteredAgents = computed(() => {
    if (!searchQuery.value) return agents;

    const query = searchQuery.value.toLowerCase();
    return agents.filter((agent) => {
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
</script>
