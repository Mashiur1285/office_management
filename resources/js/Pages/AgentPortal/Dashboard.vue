<script setup>
import { Head, router } from "@inertiajs/vue3";
import { ref, computed, onMounted } from "vue";
import axios from "axios";

const state = ref({ loading: true, agent: null, summary: null, clients: [] });
const search = ref("");
const statusFilter = ref("all");

onMounted(async () => {
    const { data } = await axios.get(route("agent-portal.data"));
    state.value = { loading: false, ...data };
});

const statusOptions = [
    { value: "all",        label: "All Status" },
    { value: "pending",    label: "Pending" },
    { value: "processing", label: "Processing" },
    { value: "completed",  label: "Completed" },
    { value: "rejected",   label: "Rejected" },
];

const filtered = computed(() => {
    if (!state.value.clients) return [];
    return state.value.clients.filter(c => {
        const matchSearch = !search.value ||
            c.name?.toLowerCase().includes(search.value.toLowerCase()) ||
            c.passport_number?.toLowerCase().includes(search.value.toLowerCase());
        const matchStatus = statusFilter.value === "all" || c.status_value === statusFilter.value;
        return matchSearch && matchStatus;
    });
});

const fmt = (n) => "৳" + new Intl.NumberFormat("en-BD", { notation: "compact" }).format(n || 0);
const logout = () => router.post(route("logout"));

const initials = computed(() => {
    const name = state.value.agent?.name || "";
    return name.split(" ").map(w => w[0]).join("").substring(0, 2).toUpperCase();
});
</script>

<template>
    <Head title="Agent Portal" />

    <div class="min-h-screen font-sans" style="background: #e8f4ff;">

        <!-- ── Hero Header ── -->
        <div class="relative overflow-hidden pb-28"
             style="background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 50%, #10b981 100%);">

            <!-- Decorative blobs -->
            <div class="absolute -top-10 -right-10 w-56 h-56 rounded-full opacity-20"
                 style="background: radial-gradient(circle, #fff, transparent 70%);"></div>
            <div class="absolute top-16 left-1/3 w-40 h-40 rounded-full opacity-10"
                 style="background: radial-gradient(circle, #a7f3d0, transparent 70%);"></div>
            <div class="absolute -bottom-8 left-10 w-48 h-48 rounded-full opacity-15"
                 style="background: radial-gradient(circle, #bfdbfe, transparent 70%);"></div>

            <!-- Top bar -->
            <div class="relative z-10 flex items-center justify-between px-6 pt-6 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-base text-white"
                         style="background: rgba(255,255,255,0.25); backdrop-filter: blur(8px);">
                        {{ initials || 'A' }}
                    </div>
                    <div v-if="!state.loading">
                        <p class="text-white/60 text-[10px] font-bold uppercase tracking-widest">Welcome back</p>
                        <h1 class="text-white font-black text-[16px] leading-tight">{{ state.agent?.name }}</h1>
                    </div>
                </div>
                <button @click="logout"
                    class="flex items-center gap-2 text-[12px] font-bold text-white px-4 py-2 rounded-full transition-all"
                    style="background: rgba(255,255,255,0.2); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.25);">
                    <font-awesome-icon icon="arrow-right-from-bracket" class="w-3 h-3" />
                    Logout
                </button>
            </div>

            <!-- Search bar -->
            <div class="relative z-10 px-5 mt-2">
                <div class="flex items-center gap-3 px-4 py-3 rounded-2xl"
                     style="background: rgba(255,255,255,0.25); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.3);">
                    <font-awesome-icon icon="magnifying-glass" class="w-4 h-4 text-white/70 shrink-0" />
                    <input v-model="search" type="text" placeholder="Search name or passport number..."
                        class="flex-1 bg-transparent border-0 text-white placeholder-white/50 text-sm focus:outline-none focus:ring-0" />
                </div>
            </div>
        </div>

        <!-- ── Floating Stats Cards ── -->
        <div class="relative z-10 px-5 -mt-20 mb-5">

            <!-- Loading spinner -->
            <div v-if="state.loading" class="flex justify-center py-10">
                <div class="w-9 h-9 rounded-full border-[3px] border-white/40 border-t-white animate-spin"></div>
            </div>

            <template v-else>
                <!-- Status Cards Row -->
                <div class="grid grid-cols-4 gap-3 mb-4">
                    <div v-for="card in [
                        { label: 'Total',     value: state.summary?.total     ?? 0, color: 'from-blue-500 to-cyan-400',    icon: 'users'  },
                        { label: 'Completed', value: state.summary?.completed ?? 0, color: 'from-emerald-500 to-teal-400', icon: 'check'  },
                        { label: 'Pending',   value: state.summary?.pending   ?? 0, color: 'from-amber-400 to-orange-400', icon: 'clock'  },
                        { label: 'Rejected',  value: state.summary?.rejected  ?? 0, color: 'from-rose-500 to-red-400',     icon: 'xmark'  },
                    ]" :key="card.label"
                        class="rounded-2xl p-4 text-white flex flex-col relative overflow-hidden shadow-lg"
                        :style="`background: linear-gradient(135deg, var(--tw-gradient-stops));`"
                        :class="`bg-gradient-to-br ${card.color}`">
                        <div class="absolute -right-3 -bottom-3 w-14 h-14 rounded-full opacity-20"
                             style="background: rgba(255,255,255,0.5);"></div>
                        <font-awesome-icon :icon="card.icon" class="w-4 h-4 text-white/70 mb-2" />
                        <p class="text-[28px] font-black leading-none mb-1">{{ card.value }}</p>
                        <p class="text-white/70 text-[10px] font-bold uppercase tracking-wider">{{ card.label }}</p>
                    </div>
                </div>

                <!-- Financial Cards -->
                <div class="grid grid-cols-3 gap-3 mb-5">
                    <div v-for="fin in [
                        { label: 'Total Fee',  value: fmt(state.summary?.total_fee),  sub: 'Overall',      icon: 'chart-line',        bg: 'rgba(255,255,255,0.85)', text: '#1e40af' },
                        { label: 'Total Paid', value: fmt(state.summary?.total_paid), sub: 'Received',     icon: 'check-circle',      bg: 'rgba(255,255,255,0.85)', text: '#065f46' },
                        { label: 'Total Due',  value: fmt(state.summary?.total_due),  sub: 'Outstanding',  icon: 'triangle-exclamation', bg: 'rgba(255,255,255,0.85)', text: '#991b1b' },
                    ]" :key="fin.label"
                        class="rounded-2xl px-5 py-4 shadow-sm"
                        style="backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.8);"
                        :style="`background: ${fin.bg};`">
                        <p class="text-[10px] font-bold uppercase tracking-widest mb-2" :style="`color: ${fin.text}; opacity: 0.6;`">{{ fin.label }}</p>
                        <p class="text-[22px] font-black leading-none" :style="`color: ${fin.text};`">{{ fin.value }}</p>
                        <p class="text-[10px] mt-1 font-medium" :style="`color: ${fin.text}; opacity: 0.4;`">{{ fin.sub }}</p>
                    </div>
                </div>

                <!-- Client List Card -->
                <div class="rounded-2xl overflow-hidden shadow-sm"
                     style="background: rgba(255,255,255,0.92); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.8);">

                    <!-- Header -->
                    <div class="flex items-center justify-between px-5 py-4 border-b"
                         style="border-color: rgba(59,130,246,0.1);">
                        <div class="flex items-center gap-2.5">
                            <div class="w-1.5 h-5 rounded-full" style="background: linear-gradient(180deg, #3b82f6, #10b981);"></div>
                            <h2 class="font-black text-gray-800 text-[15px]">My Clients</h2>
                            <span class="text-[11px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">
                                {{ filtered.length }}
                            </span>
                        </div>
                        <select v-model="statusFilter"
                            class="text-[12px] font-semibold text-blue-600 bg-blue-50 border-0 rounded-xl px-3 py-1.5 focus:outline-none focus:ring-0 cursor-pointer">
                            <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
                        </select>
                    </div>

                    <!-- Empty state -->
                    <div v-if="filtered.length === 0" class="py-14 text-center">
                        <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center"
                             style="background: linear-gradient(135deg, #eff6ff, #ecfdf5);">
                            <font-awesome-icon icon="users" class="w-7 h-7 text-blue-300" />
                        </div>
                        <p class="text-gray-400 font-semibold text-sm">No clients found</p>
                        <p class="text-gray-300 text-[12px] mt-1">Try adjusting your search or filter</p>
                    </div>

                    <!-- Client rows -->
                    <div v-else>
                        <div v-for="(client, idx) in filtered" :key="client.id"
                             class="flex items-start gap-4 px-5 py-4 transition-all duration-150 hover:bg-blue-50/40 cursor-default"
                             :class="idx < filtered.length - 1 ? 'border-b' : ''"
                             style="border-color: rgba(59,130,246,0.07);">

                            <!-- Avatar -->
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-white text-[13px] shrink-0 shadow-sm"
                                 :style="`background: linear-gradient(135deg, ${['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b'][idx % 5]}, ${['#06b6d4','#ec4899','#3b82f6','#3b82f6','#ef4444'][idx % 5]});`">
                                {{ client.name?.charAt(0)?.toUpperCase() }}
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-bold text-gray-800 text-[14px] leading-tight">{{ client.name }}</p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">{{ client.passport_number }} · {{ client.job_sector || '—' }}</p>
                                    </div>
                                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full shrink-0 mt-0.5"
                                          :class="client.status_badge">
                                        {{ client.status }}
                                    </span>
                                </div>

                                <!-- Bottom row -->
                                <div class="flex items-center gap-4 mt-2">
                                    <div class="flex items-center gap-1">
                                        <span class="text-[10px] text-gray-400">Paid</span>
                                        <span class="text-[12px] font-black text-emerald-600">{{ fmt(client.paid) }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-[10px] text-gray-400">Due</span>
                                        <span class="text-[12px] font-black" :class="client.due > 0 ? 'text-red-500' : 'text-gray-300'">{{ fmt(client.due) }}</span>
                                    </div>
                                    <div v-if="client.expected_date_to_collect !== '—'" class="flex items-center gap-1 ml-auto">
                                        <font-awesome-icon icon="calendar-days" class="w-3 h-3 text-blue-300" />
                                        <span class="text-[11px] text-blue-500 font-semibold">{{ client.expected_date_to_collect }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </template>
        </div>

    </div>
</template>

<style scoped>
input::placeholder { color: rgba(255,255,255,0.5); }
</style>
