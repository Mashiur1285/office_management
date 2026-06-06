<template>
    <aside
        :class="[
            'fixed top-0 left-0 z-[60] h-screen transition-all duration-300 border-r border-white/10 shadow-2xl overflow-hidden',
            sidebarCollapsed ? 'w-16 -translate-x-full sm:translate-x-0' : 'w-64 -translate-x-full sm:translate-x-0'
        ]"
        style="background: linear-gradient(160deg, #0f172a 0%, #1a1040 55%, #0f172a 100%);"
        aria-label="Sidebar"
    >
        <!-- Decorative glow blobs -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full opacity-20"
                 style="background: radial-gradient(circle, #6366f1, transparent 70%);"></div>
            <div class="absolute bottom-10 -left-10 w-44 h-44 rounded-full opacity-15"
                 style="background: radial-gradient(circle, #7c3aed, transparent 70%);"></div>
            <div class="absolute top-1/2 right-0 w-32 h-32 rounded-full opacity-10"
                 style="background: radial-gradient(circle, #3b82f6, transparent 70%);"></div>
        </div>

        <!-- Logo Section -->
        <div
            class="relative z-10 flex items-center px-3 py-4 border-b border-white/10"
            :class="sidebarCollapsed ? 'flex-col gap-2' : 'justify-between'"
        >
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-white shadow-lg flex-shrink-0 overflow-hidden p-1">
                    <img src="/images/zulia.jpeg" alt="Logo" class="w-full h-full object-contain" />
                </div>
                <div v-if="!sidebarCollapsed" class="min-w-0">
                    <p class="text-white font-bold text-[12px] leading-snug tracking-wide">{{ appName }}</p>
                </div>
            </div>
            <button
                @click="toggleSidebar()"
                class="flex items-center justify-center w-7 h-7 rounded-full border border-white/20 bg-white/10 hover:bg-white/20 hover:border-white/30 transition-all duration-200 flex-shrink-0 group/toggle"
                :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            >
                <font-awesome-icon
                    :icon="sidebarCollapsed ? 'angles-right' : 'angles-left'"
                    class="w-3 h-3 text-gray-400 group-hover/toggle:text-white transition-colors duration-200"
                />
            </button>
        </div>

        <div class="relative z-10 px-2 py-3 pb-4 overflow-y-auto bg-transparent h-[calc(100%-80px)]">
            <!-- Search bar -->
            <div v-if="!sidebarCollapsed" class="mb-3 px-1">
                <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 border border-white/10 focus-within:border-indigo-500/50 focus-within:bg-white/15 transition-all duration-200">
                    <font-awesome-icon icon="magnifying-glass" class="w-3.5 h-3.5 text-gray-500 flex-shrink-0" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search..."
                        class="flex-1 bg-transparent border-0 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:ring-0 min-w-0"
                    />
                    <button
                        v-if="searchQuery"
                        @click="searchQuery = ''"
                        class="flex items-center justify-center w-4 h-4 rounded-full bg-white/20 hover:bg-white/30 transition-colors flex-shrink-0"
                    >
                        <font-awesome-icon icon="xmark" class="w-2.5 h-2.5 text-gray-300" />
                    </button>
                </div>
            </div>

            <!-- MENU label -->
            <p v-if="!sidebarCollapsed" class="text-[10px] font-semibold text-gray-600 uppercase tracking-widest px-2 mb-2">Menu</p>

            <ul class="space-y-0.5 font-medium">
                <!-- Home -->
                <li v-if="hasPermission('dashboard.view') && matchesSearch('Home')">
                    <Link href="/dashboard" :class="linkClass('/dashboard')" :title="sidebarCollapsed ? 'Home' : ''">
                        <div :class="iconBox(isActivePath('/dashboard'), 'lg', 'blue')">
                            <font-awesome-icon icon="house" :class="iconFa(isActivePath('/dashboard'), 'lg', 'blue')" />
                        </div>
                        <span v-if="!sidebarCollapsed" class="ml-3">Home</span>
                    </Link>
                </li>

                <!-- Operations -->
                <li v-if="showOperations && operationsVisible">
                    <button type="button" :class="dropdownBtnClass(['/quotations', '/invoices'])" @click="handleToggleOperations" :title="sidebarCollapsed ? 'Operations' : ''">
                        <div :class="iconBox(isAnyChildActive(['/quotations', '/invoices']), 'lg', 'orange')">
                            <font-awesome-icon icon="cubes" :class="iconFa(isAnyChildActive(['/quotations', '/invoices']), 'lg', 'orange')" />
                        </div>
                        <span v-if="!sidebarCollapsed" class="flex-1 ml-3 text-left whitespace-nowrap">Operations</span>
                        <svg v-if="!sidebarCollapsed" class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': operationsOpen }" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                        </svg>
                    </button>
                    <ul v-show="operationsOpen && !sidebarCollapsed" class="py-2 space-y-2 ml-3">
                        <li v-if="hasPermission('quotation.view') && matchesSearch('Quotation')">
                            <Link href="/quotations" :class="linkClass('/quotations', true)">
                                <div :class="iconBox(isActivePath('/quotations', true), 'sm', 'orange')">
                                    <font-awesome-icon icon="file-invoice" :class="iconFa(isActivePath('/quotations', true), 'sm', 'orange')" />
                                </div>
                                <span class="ml-3">Quotation</span>
                            </Link>
                        </li>
                        <li v-if="hasPermission('invoice.view') && matchesSearch('Invoice')">
                            <Link href="/invoices" :class="linkClass('/invoices', true)">
                                <div :class="iconBox(isActivePath('/invoices', true), 'sm', 'orange')">
                                    <font-awesome-icon icon="file-invoice" :class="iconFa(isActivePath('/invoices', true), 'sm', 'orange')" />
                                </div>
                                <span class="ml-3">Invoice</span>
                            </Link>
                        </li>
                    </ul>
                </li>

                <!-- Airline Tickets -->
                <li v-if="matchesSearch('Airline Tickets')">
                    <Link href="/airline-tickets" :class="linkClass('/airline-tickets')" :title="sidebarCollapsed ? 'Airline Tickets' : ''">
                        <div :class="iconBox(isActivePath('/airline-tickets'), 'lg', 'sky')">
                            <font-awesome-icon icon="plane-departure" :class="iconFa(isActivePath('/airline-tickets'), 'lg', 'sky')" />
                        </div>
                        <span v-if="!sidebarCollapsed" class="ml-3">Airline Tickets</span>
                    </Link>
                </li>

                <!-- Registrations -->
                <li v-if="showRegistrations && registrationsVisible">
                    <button type="button" :class="dropdownBtnClass(['/clients', '/agents', '/office-staff', '/bd-companies', '/foreign-companies'])" @click="handleToggleRegistrations" :title="sidebarCollapsed ? 'Registrations' : ''">
                        <div :class="iconBox(isAnyChildActive(['/clients', '/agents', '/office-staff', '/bd-companies', '/foreign-companies']), 'lg', 'violet')">
                            <font-awesome-icon icon="layer-group" :class="iconFa(isAnyChildActive(['/clients', '/agents', '/office-staff', '/bd-companies', '/foreign-companies']), 'lg', 'violet')" />
                        </div>
                        <span v-if="!sidebarCollapsed" class="flex-1 ml-3 text-left whitespace-nowrap">Registrations</span>
                        <svg v-if="!sidebarCollapsed" class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': registrationsOpen }" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                        </svg>
                    </button>
                    <ul v-show="registrationsOpen && !sidebarCollapsed" class="py-2 space-y-2 ml-3">
                        <li v-if="hasPermission('client.view') && matchesSearch('Clients')">
                            <Link href="/clients" :class="linkClass('/clients', true)">
                                <div :class="iconBox(isActivePath('/clients', true), 'sm', 'violet')">
                                    <font-awesome-icon icon="id-card" :class="iconFa(isActivePath('/clients', true), 'sm', 'violet')" />
                                </div>
                                <span class="ml-3">Clients</span>
                            </Link>
                        </li>
                        <li v-if="hasPermission('agent.view') && matchesSearch('Agents')">
                            <Link href="/agents" :class="linkClass('/agents', true)">
                                <div :class="iconBox(isActivePath('/agents', true), 'sm', 'violet')">
                                    <font-awesome-icon icon="address-card" :class="iconFa(isActivePath('/agents', true), 'sm', 'violet')" />
                                </div>
                                <span class="ml-3">Agents</span>
                            </Link>
                        </li>
                        <li v-if="hasPermission('office-staff.view') && matchesSearch('Office Staff')">
                            <Link href="/office-staff" :class="linkClass('/office-staff', true)">
                                <div :class="iconBox(isActivePath('/office-staff', true), 'sm', 'violet')">
                                    <font-awesome-icon icon="users" :class="iconFa(isActivePath('/office-staff', true), 'sm', 'violet')" />
                                </div>
                                <span class="ml-3">Office Staff</span>
                            </Link>
                        </li>
                        <li v-if="hasPermission('bd-company.view') && matchesSearch('Vendors')">
                            <Link href="/bd-companies" :class="linkClass('/bd-companies', true)">
                                <div :class="iconBox(isActivePath('/bd-companies', true), 'sm', 'violet')">
                                    <font-awesome-icon icon="building" :class="iconFa(isActivePath('/bd-companies', true), 'sm', 'violet')" />
                                </div>
                                <span class="ml-3">Vendors</span>
                            </Link>
                        </li>
                        <li v-if="hasPermission('foreign-company.view') && matchesSearch('Foreign Companies')">
                            <Link href="/foreign-companies" :class="linkClass('/foreign-companies', true)">
                                <div :class="iconBox(isActivePath('/foreign-companies', true), 'sm', 'violet')">
                                    <font-awesome-icon icon="globe" :class="iconFa(isActivePath('/foreign-companies', true), 'sm', 'violet')" />
                                </div>
                                <span class="ml-3">Foreign Companies</span>
                            </Link>
                        </li>
                    </ul>
                </li>

                <!-- Database -->
                <li v-if="showDatabase && databaseVisible">
                    <button type="button" :class="dropdownBtnClass(['/database/clients', '/database/agents', '/database/office-staff', '/database/bd-companies', '/database/foreign-companies'])" @click="handleToggleDatabase" :title="sidebarCollapsed ? 'Database' : ''">
                        <div :class="iconBox(isAnyChildActive(['/database/clients', '/database/agents', '/database/office-staff', '/database/bd-companies', '/database/foreign-companies']), 'lg', 'blue')">
                            <font-awesome-icon icon="database" :class="iconFa(isAnyChildActive(['/database/clients', '/database/agents', '/database/office-staff', '/database/bd-companies', '/database/foreign-companies']), 'lg', 'blue')" />
                        </div>
                        <span v-if="!sidebarCollapsed" class="flex-1 ml-3 text-left whitespace-nowrap">Database</span>
                        <svg v-if="!sidebarCollapsed" class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': databaseOpen }" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                        </svg>
                    </button>
                    <ul v-show="databaseOpen && !sidebarCollapsed" class="py-2 space-y-2 ml-3">
                        <li v-if="hasPermission('client.view') && matchesSearch('Clients')">
                            <Link href="/database/clients" :class="linkClass('/database/clients', true)">
                                <div :class="iconBox(isActivePath('/database/clients', true), 'sm', 'blue')">
                                    <font-awesome-icon icon="id-card" :class="iconFa(isActivePath('/database/clients', true), 'sm', 'blue')" />
                                </div>
                                <span class="ml-3">Clients</span>
                            </Link>
                        </li>
                        <li v-if="hasPermission('agent.view') && matchesSearch('Agents')">
                            <Link href="/database/agents" :class="linkClass('/database/agents', true)">
                                <div :class="iconBox(isActivePath('/database/agents', true), 'sm', 'blue')">
                                    <font-awesome-icon icon="address-card" :class="iconFa(isActivePath('/database/agents', true), 'sm', 'blue')" />
                                </div>
                                <span class="ml-3">Agents</span>
                            </Link>
                        </li>
                        <li v-if="hasPermission('office-staff.view') && matchesSearch('Office Staff')">
                            <Link href="/database/office-staff" :class="linkClass('/database/office-staff', true)">
                                <div :class="iconBox(isActivePath('/database/office-staff', true), 'sm', 'blue')">
                                    <font-awesome-icon icon="users" :class="iconFa(isActivePath('/database/office-staff', true), 'sm', 'blue')" />
                                </div>
                                <span class="ml-3">Office Staff</span>
                            </Link>
                        </li>
                        <li v-if="hasPermission('bd-company.view') && matchesSearch('Vendors')">
                            <Link href="/database/bd-companies" :class="linkClass('/database/bd-companies', true)">
                                <div :class="iconBox(isActivePath('/database/bd-companies', true), 'sm', 'blue')">
                                    <font-awesome-icon icon="building" :class="iconFa(isActivePath('/database/bd-companies', true), 'sm', 'blue')" />
                                </div>
                                <span class="ml-3">Vendors</span>
                            </Link>
                        </li>
                        <li v-if="hasPermission('foreign-company.view') && matchesSearch('Foreign Companies')">
                            <Link href="/database/foreign-companies" :class="linkClass('/database/foreign-companies', true)">
                                <div :class="iconBox(isActivePath('/database/foreign-companies', true), 'sm', 'blue')">
                                    <font-awesome-icon icon="globe" :class="iconFa(isActivePath('/database/foreign-companies', true), 'sm', 'blue')" />
                                </div>
                                <span class="ml-3">Foreign Companies</span>
                            </Link>
                        </li>
                    </ul>
                </li>

                <!-- Accounts -->
                <li v-if="showAccounting && accountingVisible">
                    <button type="button" :class="dropdownBtnClass(['/accounting'])" @click="handleToggleAccounting" :title="sidebarCollapsed ? 'Accounts' : ''">
                        <div :class="iconBox(isAnyChildActive(['/accounting']), 'lg', 'teal')">
                            <font-awesome-icon icon="chart-line" :class="iconFa(isAnyChildActive(['/accounting']), 'lg', 'teal')" />
                        </div>
                        <span v-if="!sidebarCollapsed" class="flex-1 ml-3 text-left whitespace-nowrap">Accounts</span>
                        <svg v-if="!sidebarCollapsed" class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': accountingOpen }" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                        </svg>
                    </button>
                    <ul v-show="accountingOpen && !sidebarCollapsed" class="py-2 space-y-2 ml-3">
                        <li>
                            <Link href="/accounting" :class="linkClass('/accounting', true)">
                                <div :class="iconBox(isActivePath('/accounting', true), 'sm', 'teal')">
                                    <font-awesome-icon icon="gauge" :class="iconFa(isActivePath('/accounting', true), 'sm', 'teal')" />
                                </div>
                                <span class="ml-3">Dashboard</span>
                            </Link>
                        </li>
                        <li>
                            <button type="button" class="flex items-center w-full p-2 rounded-xl transition-all duration-200 text-gray-400 hover:bg-white/10 hover:text-white text-sm group" @click="toggleIncome">
                                <div :class="iconBox(isAnyChildActive(['/accounting/income']), 'sm', 'teal')">
                                    <font-awesome-icon icon="money-bill-trend-up" :class="iconFa(isAnyChildActive(['/accounting/income']), 'sm', 'teal')" />
                                </div>
                                <span class="flex-1 ml-3 text-left whitespace-nowrap">Income</span>
                                <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': incomeOpen }" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                                </svg>
                            </button>
                            <ul v-show="incomeOpen" class="py-2 space-y-1 ml-6">
                                <li>
                                    <Link href="/accounting/income/travel-tourism" :class="subLinkClass('/accounting/income/travel-tourism')">
                                        <font-awesome-icon icon="plane" class="w-3.5 h-3.5" />
                                        <span class="ml-3">Travel & Tourism</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/income/manpower" :class="subLinkClass('/accounting/income/manpower')">
                                        <font-awesome-icon icon="user-tie" class="w-3.5 h-3.5" />
                                        <span class="ml-3">Manpower Exporting</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/income/student" :class="subLinkClass('/accounting/income/student')">
                                        <font-awesome-icon icon="graduation-cap" class="w-3.5 h-3.5" />
                                        <span class="ml-3">Student Package</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/income/other" :class="subLinkClass('/accounting/income/other')">
                                        <font-awesome-icon icon="hand-holding-dollar" class="w-3.5 h-3.5" />
                                        <span class="ml-3">Other Income</span>
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <button type="button" class="flex items-center w-full p-2 rounded-xl transition-all duration-200 text-gray-400 hover:bg-white/10 hover:text-white text-sm group" @click="toggleCostOfSales">
                                <div :class="iconBox(isAnyChildActive(['/accounting/cost-of-sales']), 'sm', 'teal')">
                                    <font-awesome-icon icon="box" :class="iconFa(isAnyChildActive(['/accounting/cost-of-sales']), 'sm', 'teal')" />
                                </div>
                                <span class="flex-1 ml-3 text-left whitespace-nowrap">Cost of Sales</span>
                                <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': costOfSalesOpen }" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                                </svg>
                            </button>
                            <ul v-show="costOfSalesOpen" class="py-2 space-y-1 ml-6">
                                <li>
                                    <Link href="/accounting/cost-of-sales/travel-tourism" :class="subLinkClass('/accounting/cost-of-sales/travel-tourism')">
                                        <font-awesome-icon icon="plane" class="w-3.5 h-3.5" />
                                        <span class="ml-3">Travel & Tourism</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/cost-of-sales/manpower" :class="subLinkClass('/accounting/cost-of-sales/manpower')">
                                        <font-awesome-icon icon="user-tie" class="w-3.5 h-3.5" />
                                        <span class="ml-3">Manpower Exporting</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/cost-of-sales/student" :class="subLinkClass('/accounting/cost-of-sales/student')">
                                        <font-awesome-icon icon="graduation-cap" class="w-3.5 h-3.5" />
                                        <span class="ml-3">Student Package</span>
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <Link href="/accounting/gross-profit" :class="linkClass('/accounting/gross-profit', true)">
                                <div :class="iconBox(isActivePath('/accounting/gross-profit', true), 'sm', 'teal')">
                                    <font-awesome-icon icon="chart-line" :class="iconFa(isActivePath('/accounting/gross-profit', true), 'sm', 'teal')" />
                                </div>
                                <span class="ml-3">Gross Profit</span>
                            </Link>
                        </li>
                        <li>
                            <button type="button" class="flex items-center w-full p-2 rounded-xl transition-all duration-200 text-gray-400 hover:bg-white/10 hover:text-white text-sm group" @click="toggleOperatingExpenses">
                                <div :class="iconBox(isAnyChildActive(['/accounting/operating-expenses']), 'sm', 'teal')">
                                    <font-awesome-icon icon="wallet" :class="iconFa(isAnyChildActive(['/accounting/operating-expenses']), 'sm', 'teal')" />
                                </div>
                                <span class="flex-1 ml-3 text-left whitespace-nowrap">Operating Expenses</span>
                                <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': operatingExpensesOpen }" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                                </svg>
                            </button>
                            <ul v-show="operatingExpensesOpen" class="py-2 space-y-1 ml-6">
                                <li>
                                    <Link href="/accounting/operating-expenses/employee" :class="subLinkClass('/accounting/operating-expenses/employee')">
                                        <font-awesome-icon icon="users" class="w-3.5 h-3.5" />
                                        <span class="ml-3">Employee & Manpower</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/operating-expenses/administrative" :class="subLinkClass('/accounting/operating-expenses/administrative')">
                                        <font-awesome-icon icon="file-lines" class="w-3.5 h-3.5" />
                                        <span class="ml-3">Administrative</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/operating-expenses/selling-marketing" :class="subLinkClass('/accounting/operating-expenses/selling-marketing')">
                                        <font-awesome-icon icon="bullhorn" class="w-3.5 h-3.5" />
                                        <span class="ml-3">Selling & Marketing</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/operating-expenses/general" :class="subLinkClass('/accounting/operating-expenses/general')">
                                        <font-awesome-icon icon="list" class="w-3.5 h-3.5" />
                                        <span class="ml-3">General</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/operating-profit" :class="subLinkClass('/accounting/operating-profit')">
                                        <font-awesome-icon icon="chart-line" class="w-3.5 h-3.5" />
                                        <span class="ml-3">Operating Profit</span>
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <Link href="/accounting/non-operating" :class="linkClass('/accounting/non-operating', true)">
                                <div :class="iconBox(isActivePath('/accounting/non-operating', true), 'sm', 'teal')">
                                    <font-awesome-icon icon="coins" :class="iconFa(isActivePath('/accounting/non-operating', true), 'sm', 'teal')" />
                                </div>
                                <span class="ml-3">Non-Operating</span>
                            </Link>
                        </li>
                        <li>
                            <Link href="/accounting/net-profit-before-tax" :class="linkClass('/accounting/net-profit-before-tax', true)">
                                <div :class="iconBox(isActivePath('/accounting/net-profit-before-tax', true), 'sm', 'teal')">
                                    <font-awesome-icon icon="sack-dollar" :class="iconFa(isActivePath('/accounting/net-profit-before-tax', true), 'sm', 'teal')" />
                                </div>
                                <span class="ml-3">Net Profit Before Tax</span>
                            </Link>
                        </li>
                        <li>
                            <Link href="/accounting/tax" :class="linkClass('/accounting/tax', true)">
                                <div :class="iconBox(isActivePath('/accounting/tax', true), 'sm', 'teal')">
                                    <font-awesome-icon icon="file-invoice-dollar" :class="iconFa(isActivePath('/accounting/tax', true), 'sm', 'teal')" />
                                </div>
                                <span class="ml-3">Tax Management</span>
                            </Link>
                        </li>
                        <li>
                            <Link href="/accounting/net-profit-after-tax" :class="linkClass('/accounting/net-profit-after-tax', true)">
                                <div :class="iconBox(isActivePath('/accounting/net-profit-after-tax', true), 'sm', 'teal')">
                                    <font-awesome-icon icon="trophy" :class="iconFa(isActivePath('/accounting/net-profit-after-tax', true), 'sm', 'teal')" />
                                </div>
                                <span class="ml-3">Net Profit After Tax</span>
                            </Link>
                        </li>
                        <li>
                            <Link href="/accounting/tax-summary" :class="linkClass('/accounting/tax-summary', true)">
                                <div :class="iconBox(isActivePath('/accounting/tax-summary', true), 'sm', 'teal')">
                                    <font-awesome-icon icon="file-invoice-dollar" :class="iconFa(isActivePath('/accounting/tax-summary', true), 'sm', 'teal')" />
                                </div>
                                <span class="ml-3">Tax Summary</span>
                            </Link>
                        </li>
                        <li>
                            <Link href="/accounting/tax-report" :class="linkClass('/accounting/tax-report', true)">
                                <div :class="iconBox(isActivePath('/accounting/tax-report', true), 'sm', 'teal')">
                                    <font-awesome-icon icon="file-lines" :class="iconFa(isActivePath('/accounting/tax-report', true), 'sm', 'teal')" />
                                </div>
                                <span class="ml-3">Tax Report</span>
                            </Link>
                        </li>
                        <li>
                            <Link href="/accounting/vat-summary" :class="linkClass('/accounting/vat-summary', true)">
                                <div :class="iconBox(isActivePath('/accounting/vat-summary', true), 'sm', 'teal')">
                                    <font-awesome-icon icon="percent" :class="iconFa(isActivePath('/accounting/vat-summary', true), 'sm', 'teal')" />
                                </div>
                                <span class="ml-3">VAT Summary</span>
                            </Link>
                        </li>
                        <li>
                            <Link href="/accounting/vat-report" :class="linkClass('/accounting/vat-report', true)">
                                <div :class="iconBox(isActivePath('/accounting/vat-report', true), 'sm', 'teal')">
                                    <font-awesome-icon icon="file-lines" :class="iconFa(isActivePath('/accounting/vat-report', true), 'sm', 'teal')" />
                                </div>
                                <span class="ml-3">VAT Report</span>
                            </Link>
                        </li>
                    </ul>
                </li>

                <!-- Reports -->
                <li v-if="showReports && reportsVisible">
                    <button type="button" :class="dropdownBtnClass(['/reports'])" @click="handleToggleReports" :title="sidebarCollapsed ? 'Reports' : ''">
                        <div :class="iconBox(isAnyChildActive(['/reports']), 'lg', 'rose')">
                            <font-awesome-icon icon="chart-bar" :class="iconFa(isAnyChildActive(['/reports']), 'lg', 'rose')" />
                        </div>
                        <span v-if="!sidebarCollapsed" class="flex-1 ml-3 text-left whitespace-nowrap">Reports</span>
                        <svg v-if="!sidebarCollapsed" class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': reportsOpen }" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                        </svg>
                    </button>
                    <ul v-show="reportsOpen && !sidebarCollapsed" class="py-2 space-y-2 ml-3">
                        <li v-if="matchesSearch('Refund Report')">
                            <Link href="/reports/refund-report" :class="linkClass('/reports/refund-report', true)">
                                <div :class="iconBox(isActivePath('/reports/refund-report', true), 'sm', 'rose')">
                                    <font-awesome-icon icon="money-bill" :class="iconFa(isActivePath('/reports/refund-report', true), 'sm', 'rose')" />
                                </div>
                                <span class="ml-3">Refund Report</span>
                            </Link>
                        </li>
                    </ul>
                </li>

                <!-- ACL -->
                <li v-if="showAccessManagement && accessVisible">
                    <button type="button" :class="dropdownBtnClass(['/users', '/roles'])" @click="handleToggleAccess" :title="sidebarCollapsed ? 'ACL' : ''">
                        <div :class="iconBox(isAnyChildActive(['/users', '/roles']), 'lg', 'indigo')">
                            <font-awesome-icon icon="user-shield" :class="iconFa(isAnyChildActive(['/users', '/roles']), 'lg', 'indigo')" />
                        </div>
                        <span v-if="!sidebarCollapsed" class="flex-1 ml-3 text-left whitespace-nowrap">ACL</span>
                        <svg v-if="!sidebarCollapsed" class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': accessOpen }" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                        </svg>
                    </button>
                    <ul v-show="accessOpen && !sidebarCollapsed" class="py-2 space-y-2 ml-3">
                        <li v-if="hasPermission('user.view') && matchesSearch('Users')">
                            <Link href="/users" :class="linkClass('/users', true)">
                                <div :class="iconBox(isActivePath('/users', true), 'sm', 'indigo')">
                                    <font-awesome-icon icon="users-gear" :class="iconFa(isActivePath('/users', true), 'sm', 'indigo')" />
                                </div>
                                <span class="ml-3">Users</span>
                            </Link>
                        </li>
                        <li v-if="hasPermission('role.view') && matchesSearch('Roles')">
                            <Link href="/roles" :class="linkClass('/roles', true)">
                                <div :class="iconBox(isActivePath('/roles', true), 'sm', 'indigo')">
                                    <font-awesome-icon icon="users-gear" :class="iconFa(isActivePath('/roles', true), 'sm', 'indigo')" />
                                </div>
                                <span class="ml-3">Roles</span>
                            </Link>
                        </li>
                    </ul>
                </li>

                <!-- Settings -->
                <li v-if="hasPermission('settings.view') && matchesSearch('Settings')">
                    <Link href="/settings" :class="linkClass('/settings')" :title="sidebarCollapsed ? 'Settings' : ''">
                        <div :class="iconBox(isActivePath('/settings'), 'lg', 'amber')">
                            <font-awesome-icon icon="gear" :class="iconFa(isActivePath('/settings'), 'lg', 'amber')" />
                        </div>
                        <span v-if="!sidebarCollapsed" class="ml-3">Settings</span>
                    </Link>
                </li>

                <!-- Notepad -->
                <li v-if="matchesSearch('Notepad')">
                    <Link href="/notepad" :class="linkClass('/notepad')" :title="sidebarCollapsed ? 'My Notepad' : ''">
                        <div :class="iconBox(isActivePath('/notepad'), 'lg', 'sky')">
                            <font-awesome-icon icon="file-lines" :class="iconFa(isActivePath('/notepad'), 'lg', 'sky')" />
                        </div>
                        <span v-if="!sidebarCollapsed" class="ml-3">My Notepad</span>
                    </Link>
                </li>

                <!-- Logout -->
                <li v-if="matchesSearch('Logout')">
                    <button
                        @click="showLogoutModal = true"
                        :class="['flex items-center w-full p-2 rounded-xl transition-colors duration-200 text-gray-400 hover:bg-red-500/20 hover:text-red-400 group', sidebarCollapsed ? 'justify-center' : '']"
                        :title="sidebarCollapsed ? 'Logout' : ''"
                    >
                        <div :class="iconBox(false, 'lg', 'red')">
                            <font-awesome-icon icon="arrow-right-from-bracket" :class="iconFa(false, 'lg', 'red')" />
                        </div>
                        <span v-if="!sidebarCollapsed" class="ml-3">Logout</span>
                    </button>
                </li>
            </ul>
        </div>

        <!-- Logout Confirmation Modal -->
        <Modal :show="showLogoutModal" @close="showLogoutModal = false" max-width="sm">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <font-awesome-icon icon="arrow-right-from-bracket" class="w-6 h-6 text-red-600" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Confirm Logout</h3>
                <p class="text-base text-gray-600 text-center mb-6">Are you sure you want to logout?</p>
                <div class="flex gap-3">
                    <button @click="showLogoutModal = false" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200 font-medium">Cancel</button>
                    <button @click="handleLogout" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 font-medium">Yes, Logout</button>
                </div>
            </div>
        </Modal>
    </aside>
</template>

<script setup>
import { Link, usePage, router } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import Modal from "@/Components/Modal.vue";
import { useSidebar } from "@/Composables/useSidebar";

const { sidebarCollapsed, toggleSidebar } = useSidebar();

const $page = usePage();
const userPermissions = computed(() => $page.props.userPermissions || []);
const userRoles = computed(() => $page.props.userRoles || []);
const appName = computed(() => $page.props.settings?.app_name || "Zulia");

const hasPermission = (permission) =>
    userPermissions.value.includes(permission) ||
    userPermissions.value.includes("*") ||
    userPermissions.value.includes("superadmin");

const isAdmin = computed(() => {
    const roles = userRoles.value;
    return roles.includes("admin") || roles.includes("super-admin") ||
        roles.includes("superadmin") || roles.includes("Admin") || roles.includes("Super Admin");
});

const showAccessManagement = computed(() => isAdmin.value);
const showOperations = computed(() => hasPermission("quotation.view") || hasPermission("invoice.view"));
const showRegistrations = computed(() =>
    hasPermission("client.view") || hasPermission("agent.view") ||
    hasPermission("office-staff.view") || hasPermission("bd-company.view") ||
    hasPermission("foreign-company.view")
);
const showDatabase = computed(() => showRegistrations.value);
const showAccounting = computed(() => hasPermission("accounting.view"));
const showReports = computed(() =>
    hasPermission("accounting.view") || hasPermission("reports.view") ||
    hasPermission("reports.*") || hasPermission("invoice.view") || hasPermission("client.view")
);

const currentPath = computed(() => $page.url.split("?")[0]);

const isActivePath = (path, exact = false) => {
    if (!path) return false;
    return exact ? currentPath.value === path : currentPath.value.startsWith(path);
};

const isAnyChildActive = (paths) => paths.some((p) => currentPath.value.startsWith(p));

// --- Color map (dark sidebar variant) ---
const colorMap = {
    blue:   { box: "bg-blue-500/15 group-hover:bg-blue-500/25",     fa: "text-blue-400 group-hover:text-blue-300" },
    orange: { box: "bg-orange-500/15 group-hover:bg-orange-500/25", fa: "text-orange-400 group-hover:text-orange-300" },
    violet: { box: "bg-violet-500/15 group-hover:bg-violet-500/25", fa: "text-violet-400 group-hover:text-violet-300" },
    teal:   { box: "bg-teal-500/15 group-hover:bg-teal-500/25",     fa: "text-teal-400 group-hover:text-teal-300" },
    rose:   { box: "bg-rose-500/15 group-hover:bg-rose-500/25",     fa: "text-rose-400 group-hover:text-rose-300" },
    indigo: { box: "bg-indigo-500/15 group-hover:bg-indigo-500/25", fa: "text-indigo-400 group-hover:text-indigo-300" },
    amber:  { box: "bg-amber-500/15 group-hover:bg-amber-500/25",   fa: "text-amber-400 group-hover:text-amber-300" },
    sky:    { box: "bg-sky-500/15 group-hover:bg-sky-500/25",       fa: "text-sky-400 group-hover:text-sky-300" },
    red:    { box: "bg-red-500/15 group-hover:bg-red-500/25",       fa: "text-red-400 group-hover:text-red-300" },
};

const iconBox = (active, size = "lg", color = "blue") => {
    const sz = size === "lg" ? "w-7 h-7" : "w-6 h-6";
    const base = `flex items-center justify-center ${sz} rounded-md transition-colors duration-200`;
    if (active) return `${base} bg-indigo-500/30`;
    const c = colorMap[color] || colorMap.blue;
    return `${base} ${c.box}`;
};

const iconFa = (active, size = "lg", color = "blue") => {
    const sz = size === "lg" ? "w-4 h-4" : "w-3.5 h-3.5";
    const base = `${sz} transition-colors duration-200`;
    if (active) return `${base} text-indigo-300`;
    const c = colorMap[color] || colorMap.blue;
    return `${base} ${c.fa}`;
};

// --- Link / button class helpers ---
const linkClass = (path, exact = false) => {
    const active = isActivePath(path, exact);
    const base = "flex items-center p-2 rounded-xl transition-all duration-200 group";
    if (sidebarCollapsed.value) {
        return [base, "justify-center", active
            ? "bg-indigo-500/20 text-white"
            : "text-gray-400 hover:bg-white/10 hover:text-white"];
    }
    return [base, active
        ? "bg-indigo-500/20 text-white font-semibold border-l-[3px] border-indigo-400 pl-[5px]"
        : "text-gray-400 hover:bg-white/10 hover:text-white border-l-[3px] border-transparent pl-[5px]"];
};

const subLinkClass = (path) => {
    const active = currentPath.value === path;
    return ["flex items-center p-2 text-sm font-medium rounded-xl transition-all duration-200 group",
        active
            ? "bg-indigo-500/20 text-white border-l-[3px] border-indigo-400 pl-[5px]"
            : "text-gray-400 hover:bg-white/10 hover:text-white border-l-[3px] border-transparent pl-[5px]",
    ];
};

const dropdownBtnClass = (childPaths) => {
    const active = isAnyChildActive(childPaths);
    const base = "flex items-center w-full p-2 rounded-xl transition-all duration-200 group";
    if (sidebarCollapsed.value) {
        return [base, "justify-center", active
            ? "bg-indigo-500/20 text-white"
            : "text-gray-400 hover:bg-white/10 hover:text-white"];
    }
    return [base, active
        ? "text-white bg-indigo-500/10"
        : "text-gray-400 hover:bg-white/10 hover:text-white"];
};

// --- Search ---
const searchQuery = ref("");
const sq = computed(() => searchQuery.value.toLowerCase().trim());
const matchesSearch = (label) => !sq.value || label.toLowerCase().includes(sq.value);

const operationsVisible = computed(() => matchesSearch("Operations") || matchesSearch("Quotation") || matchesSearch("Invoice") || matchesSearch("Airline Tickets"));
const registrationsVisible = computed(() =>
    matchesSearch("Registrations") || matchesSearch("Clients") || matchesSearch("Agents") ||
    matchesSearch("Office Staff") || matchesSearch("Vendors") || matchesSearch("Foreign Companies")
);
const databaseVisible = computed(() =>
    matchesSearch("Database") || matchesSearch("Clients") || matchesSearch("Agents") ||
    matchesSearch("Office Staff") || matchesSearch("Vendors") || matchesSearch("Foreign Companies")
);
const accountingVisible = computed(() => matchesSearch("Accounts") || !sq.value);
const reportsVisible = computed(() => matchesSearch("Reports") || matchesSearch("Refund Report"));
const accessVisible = computed(() => matchesSearch("ACL") || matchesSearch("Users") || matchesSearch("Roles"));

watch(sq, (val) => {
    if (val) {
        if (operationsVisible.value) operationsOpen.value = true;
        if (registrationsVisible.value) registrationsOpen.value = true;
        if (databaseVisible.value) databaseOpen.value = true;
        if (accountingVisible.value) accountingOpen.value = true;
        if (reportsVisible.value) reportsOpen.value = true;
        if (accessVisible.value) accessOpen.value = true;
    }
});

watch(sidebarCollapsed, (collapsed) => {
    if (collapsed) searchQuery.value = "";
});

// --- Dropdown toggles ---
const operationsOpen = ref(false);
const handleToggleOperations = () => {
    if (sidebarCollapsed.value) { toggleSidebar(); operationsOpen.value = true; return; }
    operationsOpen.value = !operationsOpen.value;
};

const registrationsOpen = ref(false);
const handleToggleRegistrations = () => {
    if (sidebarCollapsed.value) { toggleSidebar(); registrationsOpen.value = true; return; }
    registrationsOpen.value = !registrationsOpen.value;
};

const databaseOpen = ref(false);
const handleToggleDatabase = () => {
    if (sidebarCollapsed.value) { toggleSidebar(); databaseOpen.value = true; return; }
    databaseOpen.value = !databaseOpen.value;
};

const accountingOpen = ref(false);
const handleToggleAccounting = () => {
    if (sidebarCollapsed.value) { toggleSidebar(); accountingOpen.value = true; return; }
    accountingOpen.value = !accountingOpen.value;
};

const reportsOpen = ref(false);
const handleToggleReports = () => {
    if (sidebarCollapsed.value) { toggleSidebar(); reportsOpen.value = true; return; }
    reportsOpen.value = !reportsOpen.value;
};

const accessOpen = ref(false);
const handleToggleAccess = () => {
    if (sidebarCollapsed.value) { toggleSidebar(); accessOpen.value = true; return; }
    accessOpen.value = !accessOpen.value;
};

const incomeOpen = ref(false);
const toggleIncome = () => { incomeOpen.value = !incomeOpen.value; };

const costOfSalesOpen = ref(false);
const toggleCostOfSales = () => { costOfSalesOpen.value = !costOfSalesOpen.value; };

const operatingExpensesOpen = ref(false);
const toggleOperatingExpenses = () => { operatingExpensesOpen.value = !operatingExpensesOpen.value; };

const showLogoutModal = ref(false);
const handleLogout = () => { router.post(route("logout")); };
</script>
