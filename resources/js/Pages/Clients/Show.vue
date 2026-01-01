<template>
    <Head title="Client Details" />
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50/30 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-8 text-white">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div v-if="client.photo_path" class="h-20 w-20 rounded-xl overflow-hidden border-4 border-white/20 shadow-lg">
                                    <img :src="`/storage/${client.photo_path}`" :alt="client.name" class="h-full w-full object-cover" />
                                </div>
                                <div v-else class="h-20 w-20 rounded-xl bg-white/10 flex items-center justify-center border-4 border-white/20">
                                    <svg class="h-10 w-10 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold mb-2">{{ client.name }}</h1>
                                <div class="flex flex-wrap items-center gap-3 text-sm">
                                    <span class="inline-flex items-center gap-1.5 bg-white/20 px-3 py-1 rounded-full">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        {{ client.passport_number }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 bg-white/20 px-3 py-1 rounded-full">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                        </svg>
                                        {{ client.nid_number }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-semibold" :class="client.status_badge">
                                        {{ client.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <Link href="/clients" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 rounded-xl font-medium transition-all duration-200 backdrop-blur-sm">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Back
                            </Link>
                            <Link :href="`/clients/${client.id}/edit`" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-blue-600 hover:bg-blue-50 rounded-xl font-semibold shadow-lg transition-all duration-200">
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
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-emerald-50 rounded-xl">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</p>
                            <p class="text-xl font-bold text-gray-900">{{ money(client.paid_amount) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-red-50 rounded-xl">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Due</p>
                            <p class="text-xl font-bold text-gray-900">{{ money(client.current_due) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-blue-50 rounded-xl">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Fee</p>
                            <p class="text-xl font-bold text-gray-900">{{ money(client.total_fee) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-purple-50 rounded-xl">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</p>
                            <p class="text-xl font-bold text-gray-900">{{ progressText }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button
                            @click="activeTab = 'overview'"
                            :class="[
                                'flex-1 py-4 px-6 text-center font-semibold text-sm transition-all',
                                activeTab === 'overview'
                                    ? 'border-b-2 border-blue-600 text-blue-600'
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
                                    ? 'border-b-2 border-blue-600 text-blue-600'
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
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Client Info Card -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <div class="h-8 w-1 bg-gradient-to-b from-blue-600 to-blue-400 rounded-full"></div>
                                    Client Information
                                </h2>
                                <div class="space-y-3">
                                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-gray-500 uppercase">Agent</p>
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ client.agent_name || "—" }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-gray-500 uppercase">BD Company</p>
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ client.bd_company || "—" }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl">
                                        <svg class="h-5 w-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-gray-500 uppercase">Foreign Company</p>
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ client.foreign_company || "—" }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Badges -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <div class="h-8 w-1 bg-gradient-to-b from-blue-600 to-blue-400 rounded-full"></div>
                                    Processing Status
                                </h2>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                        <span class="text-sm font-medium text-gray-700">Online</span>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold ring-2 ring-inset" :class="statusBadgeClass(client.online_status)">
                                            {{ statusLabel(client.online_status) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                        <span class="text-sm font-medium text-gray-700">Calling</span>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold ring-2 ring-inset" :class="statusBadgeClass(client.calling_status)">
                                            {{ statusLabel(client.calling_status) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                        <span class="text-sm font-medium text-gray-700">E-Visa</span>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold ring-2 ring-inset" :class="statusBadgeClass(client.evisa_status)">
                                            {{ statusLabel(client.evisa_status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Current Holder -->
                            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl shadow-sm border border-emerald-100 p-6">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-white rounded-xl shadow-sm">
                                        <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs font-bold text-emerald-700 uppercase tracking-wider mb-1">Current Document Holder</p>
                                        <p class="text-xl font-bold text-gray-900 mb-2">{{ client.current_holder_name || client.current_holder_label || "Not Assigned" }}</p>
                                        <p v-if="client.processing_status" class="text-sm text-gray-600">Processing: <span class="font-semibold">{{ client.processing_status }}</span></p>
                                        <div v-if="client.notes" class="mt-3 p-3 bg-white/60 rounded-lg">
                                            <p class="text-xs font-semibold text-gray-500 mb-1">Notes:</p>
                                            <p class="text-sm text-gray-700">{{ client.notes }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tracking Flow -->
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-100 p-4">
                                <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 12h6"/>
                                    </svg>
                                    Document Flow
                                </h3>

                                <div v-if="stages && stages.length > 0" class="relative">
                                    <!-- Progress Line -->
                                    <div class="absolute top-5 left-0 right-0 h-1 bg-gray-300 rounded-full" style="z-index: 0;">
                                        <div
                                            class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full transition-all duration-500"
                                            :style="{ width: progressPercentage + '%' }"
                                        ></div>
                                    </div>

                                    <!-- Stages in one line -->
                                    <div class="relative flex items-center justify-between" style="z-index: 1;">
                                        <div
                                            v-for="(stage, index) in stages"
                                            :key="index"
                                            class="flex flex-col items-center flex-1"
                                        >
                                            <!-- Stage Circle -->
                                            <div
                                                class="w-10 h-10 rounded-full flex items-center justify-center mb-2 transition-all duration-300 shadow-md relative"
                                                :class="index < currentStageIndex ? 'bg-green-500' : index === currentStageIndex ? 'bg-blue-600 ring-2 ring-blue-300' : 'bg-gray-300'"
                                            >
                                                <svg v-if="index < currentStageIndex" class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <svg v-else-if="index === currentStageIndex" class="h-5 w-5 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span v-else class="text-white text-xs font-bold">{{ index + 1 }}</span>
                                            </div>

                                            <!-- Stage Name -->
                                            <div class="text-center">
                                                <p
                                                    class="text-xs font-semibold mb-1"
                                                    :class="index <= currentStageIndex ? 'text-gray-900' : 'text-gray-500'"
                                                >
                                                    {{ stage.name }}
                                                </p>
                                                <p class="text-[10px] text-gray-600 max-w-[100px] truncate">{{ stage.description }}</p>

                                                <!-- Final status badge -->
                                                <div v-if="index === stages.length - 1 && index === currentStageIndex" class="mt-1">
                                                    <span
                                                        v-if="client.status_value === 'completed'"
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700"
                                                    >
                                                        ✓ Completed
                                                    </span>
                                                    <span
                                                        v-else-if="client.status_value === 'rejected'"
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700"
                                                    >
                                                        ✗ Rejected
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- No Stages Message -->
                                <div v-else class="text-center py-4">
                                    <p class="text-xs text-gray-500">No tracking stages</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents Tab -->
                    <div v-show="activeTab === 'documents'">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Client Photo -->
                            <div v-if="client.photo_path" class="group relative bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border-2 border-blue-100 hover:border-blue-300 transition-all hover:shadow-lg">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="p-2 bg-blue-100 rounded-lg">
                                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-blue-700 uppercase">Photo</p>
                                        <p class="text-xs text-gray-600">Profile Image</p>
                                    </div>
                                </div>
                                <div class="aspect-video rounded-lg overflow-hidden mb-3">
                                    <img :src="`/storage/${client.photo_path}`" :alt="client.name" class="w-full h-full object-cover" />
                                </div>
                                <a :href="`/storage/${client.photo_path}`" target="_blank" class="inline-flex items-center gap-2 text-xs font-semibold text-blue-600 hover:text-blue-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View Full Size
                                </a>
                            </div>

                            <!-- NID File -->
                            <div v-if="client.nid_file_path" class="group bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-4 border-2 border-purple-100 hover:border-purple-300 transition-all hover:shadow-lg">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="p-2 bg-purple-100 rounded-lg">
                                        <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-purple-700 uppercase">NID</p>
                                        <p class="text-xs text-gray-600">{{ client.nid_number }}</p>
                                    </div>
                                </div>
                                <p class="text-sm font-medium text-gray-900 mb-3 truncate">{{ getFileName(client.nid_file_path) }}</p>
                                <a :href="`/storage/${client.nid_file_path}`" target="_blank" class="inline-flex items-center gap-2 text-xs font-semibold text-purple-600 hover:text-purple-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Download
                                </a>
                            </div>

                            <!-- Passport File -->
                            <div v-if="client.passport_file_path" class="group bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border-2 border-green-100 hover:border-green-300 transition-all hover:shadow-lg">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="p-2 bg-green-100 rounded-lg">
                                        <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-green-700 uppercase">Passport</p>
                                        <p class="text-xs text-gray-600">{{ client.passport_number }}</p>
                                    </div>
                                </div>
                                <p class="text-sm font-medium text-gray-900 mb-3 truncate">{{ getFileName(client.passport_file_path) }}</p>
                                <a :href="`/storage/${client.passport_file_path}`" target="_blank" class="inline-flex items-center gap-2 text-xs font-semibold text-green-600 hover:text-green-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Download
                                </a>
                            </div>

                            <!-- Medical Report -->
                            <div v-if="client.medical_report_path" class="group bg-gradient-to-br from-rose-50 to-red-50 rounded-xl p-4 border-2 border-rose-100 hover:border-rose-300 transition-all hover:shadow-lg">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="p-2 bg-rose-100 rounded-lg">
                                        <svg class="h-5 w-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-rose-700 uppercase">Medical</p>
                                        <p class="text-xs text-gray-600">Report</p>
                                    </div>
                                </div>
                                <p class="text-sm font-medium text-gray-900 mb-3 truncate">{{ getFileName(client.medical_report_path) }}</p>
                                <a :href="`/storage/${client.medical_report_path}`" target="_blank" class="inline-flex items-center gap-2 text-xs font-semibold text-rose-600 hover:text-rose-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Download
                                </a>
                            </div>

                            <!-- Fit Report -->
                            <div v-if="client.fit_report_path" class="group bg-gradient-to-br from-amber-50 to-yellow-50 rounded-xl p-4 border-2 border-amber-100 hover:border-amber-300 transition-all hover:shadow-lg">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="p-2 bg-amber-100 rounded-lg">
                                        <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-amber-700 uppercase">Fit Status</p>
                                        <p class="text-xs text-gray-600">{{ statusLabel(client.fit_status) }}</p>
                                    </div>
                                </div>
                                <p class="text-sm font-medium text-gray-900 mb-3 truncate">{{ getFileName(client.fit_report_path) }}</p>
                                <a :href="`/storage/${client.fit_report_path}`" target="_blank" class="inline-flex items-center gap-2 text-xs font-semibold text-amber-600 hover:text-amber-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Download
                                </a>
                            </div>
                        </div>

                        <!-- No Files Message -->
                        <div v-if="!hasAnyFiles" class="text-center py-12">
                            <div class="inline-flex p-4 bg-gray-100 rounded-full mb-4">
                                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-500">No documents uploaded yet</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    client: {
        type: Object,
        required: true,
    },
    stages: {
        type: Array,
        default: () => [],
    },
    currentStageIndex: {
        type: Number,
        default: null,
    },
});

const client = props.client;
const activeTab = ref('overview');

const money = (value) => {
    if (value === null || value === undefined || value === "") return "—";
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(Number(value || 0));
};

const progressText = computed(() => {
    const total = Number(client.total_fee || 0);
    const paid = Number(client.paid_amount || 0);
    if (!total) return "No total set";
    const pct = Math.min(100, Math.max(0, Math.round((paid / total) * 100)));
    return `${pct}%`;
});

const statusLabel = (value) => {
    if (!value) return "—";
    const map = {
        pending: "Pending",
        ok: "OK",
        rejected: "Reject",
        fit: "Fit",
        unfit: "Unfit",
    };
    return map[value] || value;
};

const statusBadgeClass = (value) => {
    const map = {
        pending: "bg-amber-50 text-amber-700 ring-amber-200",
        ok: "bg-emerald-50 text-emerald-700 ring-emerald-200",
        rejected: "bg-red-50 text-red-700 ring-red-200",
    };
    return map[value] || "bg-gray-50 text-gray-600 ring-gray-200";
};

const getFileName = (path) => {
    if (!path) return "";
    return path.split("/").pop();
};

const hasAnyFiles = computed(() => {
    return !!(
        client.photo_path ||
        client.nid_file_path ||
        client.passport_file_path ||
        client.medical_report_path ||
        client.fit_report_path
    );
});

const progressPercentage = computed(() => {
    if (!props.stages || props.stages.length === 0) return 0;
    if (props.currentStageIndex === null) return 0;
    return ((props.currentStageIndex + 1) / props.stages.length) * 100;
});
</script>
