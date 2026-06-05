<template>
    <Head title="Agent Details" />
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-[#1d4ed8] px-6 py-8 text-white">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="h-20 w-20 rounded-xl bg-white/10 flex items-center justify-center border-4 border-white/20">
                                    <svg class="h-10 w-10 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-100 mb-1">Agent Profile</p>
                                <h1 class="text-3xl font-bold mb-2">{{ agent.name }}</h1>
                                <div class="flex flex-wrap items-center gap-3 text-sm">
                                    <span v-if="agent.mobile" class="inline-flex items-center gap-1.5 bg-white/20 px-3 py-1 rounded-full">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ agent.mobile }}
                                    </span>
                                    <span v-if="agent.district" class="inline-flex items-center gap-1.5 bg-white/20 px-3 py-1 rounded-full">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ agent.district }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <Link :href="props.readOnly ? '/database/agents' : '/agents'" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 rounded-xl font-medium transition-all duration-200 backdrop-blur-sm">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Back
                            </Link>
                            <Link v-if="!props.readOnly" :href="`/agents/${agent.id}/edit`" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-[#1d4ed8] hover:bg-blue-50 rounded-xl font-semibold shadow-lg transition-all duration-200">
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
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
                        <div class="p-3 bg-blue-50 rounded-xl">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Services Offered</p>
                            <p class="text-2xl font-bold text-gray-900">{{ agent.services?.length || 0 }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-blue-50 rounded-xl">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c1.657 0 3-1.343 3-3S13.657 2 12 2s-3 1.343-3 3 1.343 3 3 3zm0 2c-2.761 0-5 2.239-5 5v3h10v-3c0-2.761-2.239-5-5-5z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Received</p>
                            <p class="text-xl font-bold text-blue-700">{{ formatMoney(agent.total_received) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-rose-50 rounded-xl">
                            <svg class="h-6 w-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c1.657 0 3-1.343 3-3S13.657 2 12 2s-3 1.343-3 3 1.343 3 3 3zm0 2c-2.761 0-5 2.239-5 5v3h10v-3c0-2.761-2.239-5-5-5z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Refunded</p>
                            <p class="text-xl font-bold text-rose-600">{{ formatMoney(agent.total_refunded) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-purple-50 rounded-xl">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Location</p>
                            <p class="text-lg font-bold text-gray-900 truncate">{{ agent.district || "—" }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Summary -->
            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Payment Summary</h2>
                        <p class="text-sm text-gray-600">Total received and refunded for this agent.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-xl border border-blue-100 bg-blue-50/40 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Total Received</p>
                        <p class="mt-2 text-2xl font-bold text-blue-700">{{ formatMoney(agent.total_received) }}</p>
                    </div>
                    <div class="rounded-xl border border-rose-100 bg-rose-50/40 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-rose-600">Total Refunded</p>
                        <p class="mt-2 text-2xl font-bold text-rose-600">{{ formatMoney(agent.total_refunded) }}</p>
                    </div>
                </div>
            </section>

            <!-- Refund History -->
            <section v-if="refunds.length" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <div class="h-8 w-1 bg-gradient-to-b from-rose-600 to-rose-400 rounded-full"></div>
                            Refund History
                        </h2>
                        <p class="text-sm text-gray-600">All refunds associated with this agent.</p>
                    </div>
                    <span class="text-xs font-semibold text-rose-700 bg-rose-100 px-3 py-1 rounded-full">
                        {{ refunds.length }} {{ refunds.length === 1 ? 'refund' : 'refunds' }}
                    </span>
                </div>
                <div class="overflow-hidden rounded-xl border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-700">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Date</th>
                                    <th class="px-4 py-3 font-semibold">Client</th>
                                    <th class="px-4 py-3 font-semibold">Method</th>
                                    <th class="px-4 py-3 font-semibold text-right">Amount</th>
                                    <th class="px-4 py-3 font-semibold">Notes</th>
                                    <th class="px-4 py-3 font-semibold">Created By</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="refund in refunds" :key="refund.id" class="transition hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap">{{ refund.payment_date || '—' }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-900">{{ refund.client_name || '—' }}</td>
                                    <td class="px-4 py-3">{{ refund.payment_method || '—' }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-rose-600">{{ formatMoney(refund.amount) }}</td>
                                    <td class="px-4 py-3 max-w-xs truncate">{{ refund.notes || '—' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ refund.created_by || '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Tabs -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button
                            @click="activeTab = 'overview'"
                            :class="[
                                'flex-1 py-4 px-6 text-center font-semibold text-sm transition-all',
                                activeTab === 'overview'
                                    ? 'border-b-2 border-[#1d4ed8] text-[#1d4ed8]'
                                    : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]"
                        >
                            Overview
                        </button>
                        <button
                            @click="activeTab = 'documents'"
                            :class="[
                                'flex-1 py-4 px-6 text-center font-semibold text-sm transition-all',
                                activeTab === 'documents'
                                    ? 'border-b-2 border-[#1d4ed8] text-[#1d4ed8]'
                                    : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]"
                        >
                            Documents & Files
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <!-- Overview Tab -->
                    <div v-show="activeTab === 'overview'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Agent Information -->
                        <div class="space-y-6">
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <div class="h-8 w-1 bg-gradient-to-b from-[#1d4ed8] to-[#2d8262] rounded-full"></div>
                                    Contact Information
                                </h2>
                                <div class="space-y-3">
                                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-gray-500 uppercase">Mobile</p>
                                            <p class="text-sm font-medium text-gray-900">{{ agent.mobile || "—" }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-gray-500 uppercase">District</p>
                                            <p class="text-sm font-medium text-gray-900">{{ agent.district || "—" }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-gray-500 uppercase">Address</p>
                                            <p class="text-sm font-medium text-gray-900">{{ agent.address || "—" }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Services -->
                            <div v-if="agent.services && agent.services.length" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <div class="h-8 w-1 bg-gradient-to-b from-[#1d4ed8] to-[#2d8262] rounded-full"></div>
                                    Services Offered
                                </h2>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="service in agent.services"
                                        :key="service"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-50 text-blue-700 rounded-lg text-xs font-semibold border border-blue-100"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        {{ service }}
                                    </span>
                                </div>
                            </div>

                            <!-- Bank Details -->
                            <div v-if="agent.bank_details" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <div class="h-8 w-1 bg-gradient-to-b from-[#1d4ed8] to-[#2d8262] rounded-full"></div>
                                    Bank Details
                                </h2>
                                <div class="p-3 bg-gray-50 rounded-xl">
                                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ agent.bank_details }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Linked Clients -->
                        <div class="lg:col-span-2">
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                            <div class="h-8 w-1 bg-gradient-to-b from-[#1d4ed8] to-[#2d8262] rounded-full"></div>
                                            Linked Clients
                                        </h2>
                                        <p class="text-sm text-gray-600 ml-3">Click a client to view their details</p>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-full text-xs font-bold border border-blue-100">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
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
                                                :href="props.readOnly ? `/database/clients/${client.id}` : `/clients/${client.id}`"
                                                class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 bg-[#1d4ed8] text-white rounded-lg text-xs font-semibold shadow-md hover:bg-[#154130] transition-all group-hover:scale-105"
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
                                    <p class="text-sm font-medium text-gray-500">No clients linked to this agent yet</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents Tab -->
                    <div v-show="activeTab === 'documents'">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- NID File -->
                            <div v-if="agent.nid_file_path" class="group bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-4 border-2 border-purple-100 hover:border-purple-300 transition-all hover:shadow-lg">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-2 bg-purple-100 rounded-lg">
                                        <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-purple-700 uppercase">NID Document</p>
                                        <p class="text-xs text-gray-600">National ID Card</p>
                                    </div>
                                </div>
                                <div class="mb-4 flex items-center justify-center py-8 bg-white/60 rounded-lg">
                                    <svg class="h-16 w-16 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="flex gap-2">
                                    <a :href="`/storage/${agent.nid_file_path}`" target="_blank" class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold text-purple-600 bg-white hover:bg-purple-50 rounded-lg transition-colors border border-purple-200">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </a>
                                    <a :href="`/storage/${agent.nid_file_path}`" download class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Download
                                    </a>
                                </div>
                            </div>

                            <!-- No Documents Message -->
                            <div v-if="!agent.nid_file_path" class="col-span-full flex flex-col items-center justify-center py-16">
                                <div class="p-4 bg-gray-100 rounded-full mb-4">
                                    <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 mb-1">No documents uploaded yet</p>
                                <p class="text-xs text-gray-400">Documents will appear here once uploaded</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Portal Account Card -->
                <div v-if="!props.readOnly" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                                <font-awesome-icon icon="user-shield" class="w-4 h-4 text-indigo-500" />
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-[15px]">Agent Portal Account</h3>
                                <p class="text-[11px] text-gray-400">Online access for this agent</p>
                            </div>
                        </div>
                        <!-- Has account -->
                        <div v-if="agent.has_account" class="flex items-center gap-2">
                            <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100">
                                ✓ Account Active
                            </span>
                            <button @click="showResetPassword = !showResetPassword"
                                class="text-[11px] font-bold text-amber-600 bg-amber-50 px-3 py-1 rounded-full border border-amber-100 hover:bg-amber-100 transition">
                                Reset Password
                            </button>
                        </div>
                        <!-- No account -->
                        <button v-else @click="showCreateAccount = !showCreateAccount"
                            class="flex items-center gap-1.5 text-[12px] font-bold text-indigo-600 bg-indigo-50 px-4 py-2 rounded-full border border-indigo-100 hover:bg-indigo-100 transition">
                            <font-awesome-icon icon="plus" class="w-3 h-3" />
                            Create Account
                        </button>
                    </div>

                    <!-- Account email display -->
                    <div v-if="agent.has_account" class="bg-gray-50 rounded-xl px-4 py-3 text-[13px] text-gray-600 mb-3">
                        <span class="text-gray-400 font-medium">Email: </span>
                        <span class="font-semibold">{{ agent.account_email }}</span>
                    </div>

                    <!-- Create account form -->
                    <div v-if="showCreateAccount && !agent.has_account" class="border border-indigo-100 rounded-xl p-4 bg-indigo-50/30 mt-3">
                        <p class="text-[12px] font-semibold text-gray-600 mb-3">Set login credentials for this agent:</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1 block">Email</label>
                                <input v-model="accountForm.email" type="email" placeholder="agent@email.com"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                                <p v-if="accountForm.errors.email" class="text-xs text-red-500 mt-1">{{ accountForm.errors.email }}</p>
                            </div>
                            <div>
                                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1 block">Password</label>
                                <input v-model="accountForm.password" type="password" placeholder="Min 8 characters"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                                <p v-if="accountForm.errors.password" class="text-xs text-red-500 mt-1">{{ accountForm.errors.password }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-3">
                            <button @click="createAccount" :disabled="accountForm.processing"
                                class="px-4 py-2 bg-indigo-600 text-white text-[12px] font-bold rounded-lg hover:bg-indigo-700 transition disabled:opacity-50">
                                {{ accountForm.processing ? 'Creating...' : 'Create Account' }}
                            </button>
                            <button @click="showCreateAccount = false" class="px-4 py-2 bg-gray-100 text-gray-600 text-[12px] font-bold rounded-lg hover:bg-gray-200 transition">
                                Cancel
                            </button>
                        </div>
                    </div>

                    <!-- Reset password form -->
                    <div v-if="showResetPassword && agent.has_account" class="border border-amber-100 rounded-xl p-4 bg-amber-50/30 mt-3">
                        <p class="text-[12px] font-semibold text-gray-600 mb-3">Set new password:</p>
                        <div class="flex gap-3 items-start">
                            <div class="flex-1">
                                <input v-model="resetForm.password" type="password" placeholder="New password (min 8 chars)"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-200" />
                                <p v-if="resetForm.errors.password" class="text-xs text-red-500 mt-1">{{ resetForm.errors.password }}</p>
                            </div>
                            <button @click="resetPassword" :disabled="resetForm.processing"
                                class="px-4 py-2 bg-amber-500 text-white text-[12px] font-bold rounded-lg hover:bg-amber-600 transition disabled:opacity-50">
                                {{ resetForm.processing ? 'Saving...' : 'Update' }}
                            </button>
                            <button @click="showResetPassword = false" class="px-4 py-2 bg-gray-100 text-gray-600 text-[12px] font-bold rounded-lg hover:bg-gray-200 transition">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    agent: {
        type: Object,
        required: true,
    },
    clients: {
        type: Array,
        default: () => [],
    },
    refunds: {
        type: Array,
        default: () => [],
    },
    readOnly: {
        type: Boolean,
        default: false,
    },
});

const agent = props.agent;
const clients = props.clients || [];
const refunds = props.refunds || [];

const activeTab = ref('overview');

const accountForm = useForm({ email: '', password: '' });
const resetForm   = useForm({ password: '' });
const showCreateAccount = ref(false);
const showResetPassword = ref(false);

const createAccount = () => {
    accountForm.post(route('agents.create-account', agent.id), {
        onSuccess: () => { showCreateAccount.value = false; accountForm.reset(); }
    });
};
const resetPassword = () => {
    resetForm.post(route('agents.reset-password', agent.id), {
        onSuccess: () => { showResetPassword.value = false; resetForm.reset(); }
    });
};

const formatMoney = (value) => {
    const amount = Number(value) || 0;
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount);
};
</script>
