<template>
    <Head title="Foreign Company Details" />
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-[#1e5b43] px-6 py-8 text-white">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="h-20 w-20 rounded-xl bg-white/10 flex items-center justify-center border-4 border-white/20">
                                    <svg class="h-10 w-10 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c1.657 0 3-1.343 3-3S13.657 2 12 2s-3 1.343-3 3 1.343 3 3 3zM20 21a8 8 0 10-16 0"/>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-100 mb-1">Foreign Company</p>
                                <h1 class="text-3xl font-bold mb-2">{{ company.name }}</h1>
                                <div class="flex flex-wrap items-center gap-3 text-sm">
                                    <span v-if="company.country" class="inline-flex items-center gap-1.5 bg-white/20 px-3 py-1 rounded-full">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12a4 4 0 100-8 4 4 0 000 8zM20 21a8 8 0 10-16 0"/>
                                        </svg>
                                        {{ company.country }}
                                    </span>
                                    <span v-if="company.contact_person_name" class="inline-flex items-center gap-1.5 bg-white/20 px-3 py-1 rounded-full">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A4 4 0 017 16h10a4 4 0 013.879 1.804M15 11a3 3 0 10-6 0 3 3 0 006 0z"/>
                                        </svg>
                                        {{ company.contact_person_name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <Link href="/foreign-companies" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 rounded-xl font-medium transition-all duration-200 backdrop-blur-sm">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Back
                            </Link>
                            <Link :href="`/foreign-companies/${company.id}/edit`" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-[#1e5b43] hover:bg-emerald-50 rounded-xl font-semibold shadow-lg transition-all duration-200">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-blue-50 rounded-xl">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Clients</p>
                            <p class="text-2xl font-bold text-gray-900">{{ clients.length }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-indigo-50 rounded-xl">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11.5a3 3 0 100-6 3 3 0 000 6zM19.4 21a8 8 0 10-14.8 0"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Country</p>
                            <p class="text-lg font-bold text-gray-900 truncate">{{ company.country || "—" }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-purple-50 rounded-xl">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11M9 21V3m7.586 14H21v-4.586a2 2 0 00-.586-1.414l-4.414-4.414A2 2 0 0014.586 6H10"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Per Client Fee</p>
                            <p class="text-lg font-bold text-gray-900 truncate">{{ money(company.per_client_fee) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Linked Clients -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <div class="h-8 w-1 bg-gradient-to-b from-[#1e5b43] to-[#2d8262] rounded-full"></div>
                            Linked Clients
                        </h2>
                        <p class="text-sm text-gray-600 ml-3">Click a client to view their details</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-full text-xs font-bold border border-indigo-100">
                        {{ clients.length }} {{ clients.length === 1 ? 'client' : 'clients' }}
                    </span>
                </div>

                <div v-if="clients.length" class="grid gap-3 max-h-[600px] overflow-y-auto pr-2">
                    <div
                        v-for="client in clients"
                        :key="client.id"
                        class="group relative bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border-2 border-blue-100 hover:border-blue-300 transition-all hover:shadow-lg"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0 space-y-2">
                                <div class="flex items-center gap-2">
                                    <div class="p-2 bg-blue-100 rounded-lg">
                                        <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <h3 class="font-bold text-gray-900">{{ client.name }}</h3>
                                </div>
                                <div class="space-y-1 text-xs text-gray-600 ml-10">
                                    <p class="flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span class="font-medium">Passport:</span> {{ client.passport_number || "—" }}
                                    </p>
                                    <p class="flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                        </svg>
                                        <span class="font-medium">NID:</span> {{ client.nid_number || "—" }}
                                    </p>
                                </div>
                            </div>
                            <Link
                                :href="`/clients/${client.id}`"
                                class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 bg-[#1e5b43] text-white rounded-lg text-xs font-semibold shadow-md hover:bg-[#154130] transition-all group-hover:scale-105"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Show
                            </Link>
                        </div>
                    </div>
                </div>
                <div v-else class="flex flex-col items-center justify-center py-12">
                    <div class="p-4 bg-gray-100 rounded-full mb-4">
                        <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500">No clients linked to this company yet</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    company: {
        type: Object,
        required: true,
    },
    clients: {
        type: Array,
        default: () => [],
    },
});

const company = props.company;
const clients = props.clients || [];

const money = (value) => {
    if (value === null || value === undefined || value === "") return "—";
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(Number(value || 0));
};
</script>
