<template>
    <Head title="Clients" />

    <div class="py-6 space-y-6">
        <!-- Header Section -->
        <div
            class="overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-800 text-white shadow-xl"
        >
            <div class="px-6 py-8">
                <div
                    class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                title="Clients"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                            <p
                                class="text-sm uppercase tracking-[0.25em] text-white font-semibold bg-white/10 px-3 py-1 rounded-full ring-1 ring-white/30 shadow-sm "
                            >
                                Client Management
                            </p>
                        </div>
                        <p class="text-sm text-blue-100 max-w-2xl">
                            Track client documents, visa stages, deadlines, and
                            payments in real-time
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a
                            :href="route('clients.export', { type: 'excel', bd_company_status: props.filters?.bd_company_status, bd_company_scope: props.filters?.bd_company_scope ? 1 : 0, agency_scope: props.filters?.agency_scope ? 1 : 0, foreign_country: props.filters?.foreign_country })"
                            class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:bg-green-700"
                        >
                            Export to Excel
                        </a>
                        <a
                            :href="route('clients.export', { type: 'pdf', bd_company_status: props.filters?.bd_company_status, bd_company_scope: props.filters?.bd_company_scope ? 1 : 0, agency_scope: props.filters?.agency_scope ? 1 : 0, foreign_country: props.filters?.foreign_country })"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:bg-red-700"
                        >
                            Export to PDF
                        </a>
                        <Link
                            href="/clients/create"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-blue-700 shadow-lg transition hover:shadow-xl hover:scale-105"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                title="Add client"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                            Add New Client
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                class="relative overflow-hidden rounded-xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-6 shadow-sm transition hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p
                            class="text-xs font-medium uppercase tracking-wide text-blue-600"
                        >
                            Total Clients
                        </p>
                        <p class="mt-2 text-3xl font-bold text-blue-900">
                            {{ filteredClients.length }}
                        </p>
                    </div>
                    <div class="rounded-full bg-blue-100 p-3">
                        <svg
                            class="h-6 w-6 text-blue-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            title="Total clients"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="relative overflow-hidden rounded-xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-6 shadow-sm transition hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p
                            class="text-xs font-medium uppercase tracking-wide text-amber-600"
                        >
                            Processing
                        </p>
                        <p class="mt-2 text-3xl font-bold text-amber-900">
                            {{ stats.processing }}
                        </p>
                    </div>
                    <div class="rounded-full bg-amber-100 p-3">
                        <svg
                            class="h-6 w-6 text-amber-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            title="Processing"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="relative overflow-hidden rounded-xl border border-green-100 bg-gradient-to-br from-green-50 to-white p-6 shadow-sm transition hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p
                            class="text-xs font-medium uppercase tracking-wide text-green-600"
                        >
                            Completed
                        </p>
                        <p class="mt-2 text-3xl font-bold text-green-900">
                            {{ stats.completed }}
                        </p>
                    </div>
                    <div class="rounded-full bg-green-100 p-3">
                        <svg
                            class="h-6 w-6 text-green-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            title="Completed"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="relative overflow-hidden rounded-xl border border-red-100 bg-gradient-to-br from-red-50 to-white p-6 shadow-sm transition hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p
                            class="text-xs font-medium uppercase tracking-wide text-red-600"
                        >
                            Rejected
                        </p>
                        <p class="mt-2 text-3xl font-bold text-red-900">
                            {{ stats.rejected }}
                        </p>
                    </div>
                    <div class="rounded-full bg-red-100 p-3">
                        <svg
                            class="h-6 w-6 text-red-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            title="Rejected"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Alert -->
        <div
            v-if="flash.success"
            class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm animate-pulse"
            role="alert"
        >
            <svg
                class="h-5 w-5 flex-shrink-0"
                fill="currentColor"
                viewBox="0 0 20 20"
                title="Success"
            >
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"
                />
            </svg>
            <div class="flex-1">
                <span class="font-semibold">Success:</span>
                <span class="ml-1">{{ flash.success }}</span>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <!-- Search Bar -->
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                        >
                            <svg
                                class="h-5 w-5 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                title="Search"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
                            placeholder="Search by name, passport, NID, or phone..."
                        />
                        <button
                            v-if="searchQuery"
                            @click="searchQuery = ''"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                title="Clear search"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                        >Filter:</span
                    >
                    <button
                        @click="selectedStage = 'all'"
                        :class="[
                            'px-3 py-1.5 text-xs font-semibold rounded-lg transition',
                            selectedStage === 'all'
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                        ]"
                    >
                        All
                    </button>
                    <button
                        v-for="stage in statusFilters"
                        :key="stage.value"
                        @click="selectedStage = stage.value"
                        :class="[
                            'px-3 py-1.5 text-xs font-semibold rounded-lg transition',
                            selectedStage === stage.value
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                        ]"
                    >
                        {{ stage.label }}
                    </button>
                </div>
            </div>

            <!-- Results count -->
            <div
                v-if="searchQuery || selectedStage !== 'all'"
                class="mt-3 pt-3 border-t border-gray-100"
            >
                <p class="text-sm text-gray-600">
                    Showing
                    <span class="font-semibold text-gray-900">{{
                        filteredClients.length
                    }}</span>
                    of {{ clients.length }} clients
                </p>
            </div>
        </div>

        <!-- Table Card -->
        <div
            class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-md"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="sticky top-0 z-10 bg-gradient-to-r from-gray-50 to-gray-100 text-xs uppercase text-gray-700 border-b border-gray-200"
                    >
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold">
                                Client Info
                            </th>
                            <th scope="col" class="px-6 py-4 font-semibold">
                                Agent
                            </th>
                            <th scope="col" class="px-6 py-4 font-semibold">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-4 font-semibold">
                                Passport
                            </th>
                            <th scope="col" class="px-6 py-4 font-semibold">
                                Due Amount
                            </th>
                            <th scope="col" class="px-6 py-4 font-semibold">
                                VAT Receivable
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-4 font-semibold text-right"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="client in paginatedClients"
                            :key="client.id"
                            class="transition hover:bg-blue-50/50 group"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative flex-shrink-0">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-bold text-sm shadow-md ring-2 ring-white"
                                        >
                                            <img
                                                v-if="client.photo_url"
                                                :src="client.photo_url"
                                                alt="avatar"
                                                class="h-12 w-12 rounded-full object-cover"
                                            />
                                            <span v-else>{{
                                                initials(client.name)
                                            }}</span>
                                        </div>
                                        <div
                                            class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full bg-green-500 ring-2 ring-white"
                                        ></div>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span
                                            class="font-semibold text-gray-900 truncate"
                                            >{{ client.name }}</span
                                        >
                                        <span class="text-xs text-gray-500"
                                            >PS: {{ client.passport_number }}</span
                                        >
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">
                                    {{ client.agent_name || "—" }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset whitespace-nowrap capitalize"
                                    :class="client.status_badge"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-current"
                                    ></span>
                                    {{ client.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">
                                    {{ client.passport_number || "—" }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1">
                                    <svg
                                        class="h-4 w-4 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        title="Current due"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    <p class="font-bold text-gray-900">
                                        {{ money(client.current_due) }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-1">
                                        <svg
                                            class="h-4 w-4 text-red-500"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            title="VAT unpaid"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"
                                            />
                                        </svg>
                                        <p class="font-bold text-gray-900">
                                            {{ money(client.vat_unpaid) }}
                                        </p>
                                    </div>
                                    <div
                                        v-if="client.vat_paid"
                                        class="flex-shrink-0"
                                    >
                                        <svg
                                            class="h-5 w-5 text-green-600"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                            title="VAT paid"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <IconButton
                                        icon="fa-solid fa-eye"
                                        class="bg-gray-100 text-gray-700 hover:bg-gray-200"
                                        tooltip="View client"
                                        @click="
                                            router.visit(
                                                `/clients/${client.id}`
                                            )
                                        "
                                    />
                                    <IconButton
                                        icon="fa-solid fa-pen-to-square"
                                        class="bg-blue-600 text-white hover:bg-blue-700"
                                        tooltip="Edit client"
                                        @click="
                                            router.visit(
                                                `/clients/${client.id}/edit`
                                            )
                                        "
                                    />
                                    <IconButton
                                        icon="fa-solid fa-location-dot"
                                        class="bg-white text-blue-700 hover:bg-blue-50 border border-blue-200"
                                        tooltip="Track documents"
                                        @click="
                                            router.visit(
                                                `/clients/${client.id}/documents`
                                            )
                                        "
                                    />
                                    <IconButton
                                        icon="fa-solid fa-trash"
                                        extraClass="bg-red-100 text-red-600 hover:bg-red-200"
                                        tooltip="Delete client"
                                        @click="confirmDelete(client)"
                                    />
                                </div>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr
                            v-if="
                                filteredClients.length === 0 &&
                                clients.length === 0
                            "
                        >
                            <td colspan="7" class="px-4 py-16">
                                <div
                                    class="flex flex-col items-center justify-center text-center"
                                >
                                    <div
                                        class="rounded-full bg-gray-100 p-6 mb-4"
                                    >
                                        <svg
                                            class="h-16 w-16 text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            title="No clients"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                            />
                                        </svg>
                                    </div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 mb-1"
                                    >
                                        No clients yet
                                    </h3>
                                    <p class="text-sm text-gray-500 mb-4">
                                        Get started by adding your first client
                                    </p>
                                    <Link
                                        href="/clients/create"
                                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            title="Add client"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 4v16m8-8H4"
                                            />
                                        </svg>
                                        Add First Client
                                    </Link>
                                </div>
                            </td>
                        </tr>

                        <!-- No Results -->
                        <tr
                            v-if="
                                filteredClients.length === 0 &&
                                clients.length > 0
                            "
                        >
                            <td colspan="7" class="px-4 py-12">
                                <div
                                    class="flex flex-col items-center justify-center text-center"
                                >
                                    <div
                                        class="rounded-full bg-gray-100 p-6 mb-4"
                                    >
                                        <svg
                                            class="h-12 w-12 text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                            />
                                        </svg>
                                    </div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 mb-1"
                                    >
                                        No results found
                                    </h3>
                                    <p class="text-sm text-gray-500 mb-4">
                                        Try adjusting your search or filter to
                                        find what you're looking for
                                    </p>
                                    <button
                                        @click="clearFilters"
                                        class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200"
                                    >
                                        Clear filters
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-t border-gray-100 px-6 py-4">
                <!-- Per page + info -->
                <div class="flex items-center gap-3 text-sm text-gray-600">
                    <span>Rows per page:</span>
                    <div class="flex items-center gap-1">
                        <button
                            v-for="n in [25, 50, 75, 100]"
                            :key="n"
                            @click="perPage = n"
                            :class="[
                                'px-2.5 py-1 rounded-lg text-xs font-semibold transition',
                                perPage === n
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                            ]"
                        >
                            {{ n }}
                        </button>
                    </div>
                    <span class="text-gray-400">|</span>
                    <span>
                        {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, filteredClients.length) }}
                        of {{ filteredClients.length }}
                    </span>
                </div>

                <!-- Page navigation -->
                <div class="flex items-center gap-1">
                    <button
                        @click="currentPage = 1"
                        :disabled="currentPage === 1"
                        class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition"
                        title="First page"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7M18 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button
                        @click="currentPage--"
                        :disabled="currentPage === 1"
                        class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition"
                        title="Previous page"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <span class="px-3 py-1 text-sm font-semibold text-gray-700">
                        {{ currentPage }} / {{ totalPages }}
                    </span>

                    <button
                        @click="currentPage++"
                        :disabled="currentPage === totalPages"
                        class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition"
                        title="Next page"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <button
                        @click="currentPage = totalPages"
                        :disabled="currentPage === totalPages"
                        class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition"
                        title="Last page"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M6 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
        <div
            v-if="deleteModal.show"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <!-- Backdrop -->
            <div
                class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                @click="deleteModal.show = false"
            ></div>

            <!-- Modal -->
            <div
                class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl"
            >
                <div class="p-6">
                    <!-- Icon -->
                    <div class="flex justify-center mb-4">
                        <div class="rounded-full bg-red-100 p-4">
                            <svg
                                class="h-8 w-8 text-red-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                />
                            </svg>
                        </div>
                    </div>

                    <!-- Text -->
                    <h3 class="text-center text-lg font-bold text-gray-900 mb-2">
                        Are you sure?
                    </h3>
                    <p class="text-center text-sm text-gray-500 mb-1">
                        You are about to delete
                    </p>
                    <p class="text-center text-base font-semibold text-gray-800 mb-4">
                        "{{ deleteModal.clientName }}"
                    </p>
                    <p class="text-center text-xs text-red-500 mb-6">
                        This action cannot be undone.
                    </p>

                    <!-- Buttons -->
                    <div class="flex gap-3">
                        <button
                            @click="deleteModal.show = false"
                            class="flex-1 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition"
                        >
                            Cancel
                        </button>
                        <button
                            @click="doDelete"
                            class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition"
                        >
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import IconButton from "@/Components/Buttons/IconButton.vue";

const props = defineProps({
    clients: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const flash = computed(() => usePage().props.flash || {});

// Search and filter state
const searchQuery = ref("");
const selectedStage = ref("all");
const perPage = ref(25);
const currentPage = ref(1);

watch([searchQuery, selectedStage, perPage], () => {
    currentPage.value = 1;
});

const statusFilters = [
    { value: "pending", label: "Pending" },
    { value: "company_processing", label: "Company Processing" },
    { value: "completed", label: "Completed" },
    { value: "rejected", label: "Rejected" },
];

// Filtered clients based on search and stage
const filteredClients = computed(() => {
    let result = props.clients || [];

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter((client) => {
            const mobile = client.mobile
                ? String(client.mobile).toLowerCase()
                : "";
            return (
                client.name?.toLowerCase().includes(query) ||
                client.passport_number?.toLowerCase().includes(query) ||
                client.nid_number?.toLowerCase().includes(query) ||
                mobile.includes(query)
            );
        });
    }

    // Stage filter
    if (selectedStage.value !== "all") {
        result = result.filter(
            (client) => client.status_value === selectedStage.value
        );
    }

    return result;
});

const totalPages = computed(() => Math.ceil(filteredClients.value.length / perPage.value) || 1);

const paginatedClients = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filteredClients.value.slice(start, start + perPage.value);
});

// Statistics
const stats = computed(() => ({
    processing: (props.clients || []).filter(
        (c) => c.status_value === "company_processing"
    ).length,
    completed: (props.clients || []).filter(
        (c) => c.status_value === "completed"
    ).length,
    rejected: (props.clients || []).filter((c) => c.status_value === "rejected")
        .length,
    pending: (props.clients || []).filter((c) => c.status_value === "pending")
        .length,
}));

const initials = (name = "") => {
    return name
        .split(" ")
        .map((chunk) => chunk.charAt(0))
        .join("")
        .slice(0, 2)
        .toUpperCase();
};

const money = (value) => {
    if (value === null || value === undefined) return "—";
    return (
        "৳" +
        new Intl.NumberFormat("en-BD", {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(Number(value || 0))
    );
};

const clearFilters = () => {
    searchQuery.value = "";
    selectedStage.value = "all";
};

const deleteModal = ref({ show: false, clientId: null, clientName: "" });

const confirmDelete = (client) => {
    deleteModal.value = { show: true, clientId: client.id, clientName: client.name };
};

const doDelete = () => {
    router.delete(`/clients/${deleteModal.value.clientId}`, {
        preserveScroll: true,
        onFinish: () => { deleteModal.value.show = false; },
    });
};

if (
    props.filters?.bd_company_status ||
    props.filters?.bd_company_scope ||
    props.filters?.agency_scope
) {
    selectedStage.value = "all";
}

const exportData = () => {
    // Create CSV content
    const headers = [
        "Name",
        "NID",
        "Passport",
        "Job Sector",
        "Agent",
        "Status",
        "BD Company",
        "Foreign Company",
        "Due Amount",
    ];
    const rows = filteredClients.value.map((client) => [
        client.name,
        client.nid_number,
        client.passport_number,
        client.job_sector || "",
        client.agent_name || "",
        client.status,
        client.bd_company || "",
        client.foreign_company || "",
        client.current_due || "0",
    ]);

    const csvContent = [
        headers.join(","),
        ...rows.map((row) => row.map((cell) => `"${cell}"`).join(",")),
    ].join("\n");

    // Download
    const blob = new Blob([csvContent], { type: "text/csv" });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `clients-export-${new Date().toISOString().split("T")[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
};
</script>
