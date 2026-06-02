<template>
    <aside
        :class="[
            'fixed top-0 left-0 z-[60] h-screen transition-all duration-300 bg-white border-r border-green-200 shadow-sm rounded-r-2xl overflow-hidden',
            sidebarCollapsed ? 'w-16 -translate-x-full sm:translate-x-0' : 'w-64 -translate-x-full sm:translate-x-0'
        ]"
        aria-label="Sidebar"
    >
        <!-- Logo Section -->
        <div
            class="flex items-center px-3 py-4 border-b border-gray-100"
            :class="sidebarCollapsed ? 'flex-col gap-2' : 'justify-between'"
        >
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-green-700 shadow-sm flex-shrink-0">
                    <font-awesome-icon icon="leaf" class="w-3.5 h-3.5 text-white" />
                </div>
                <div v-if="!sidebarCollapsed">
                    <p class="text-gray-900 font-bold text-base leading-tight">{{ appName }}</p>
                    <p class="text-gray-400 text-[10px] leading-tight">Management</p>
                </div>
            </div>
            <button
                @click="toggleSidebar()"
                class="flex items-center justify-center w-7 h-7 rounded-full border border-gray-200 bg-white shadow-sm hover:bg-green-50 hover:border-green-400 hover:shadow-green-100 transition-all duration-200 flex-shrink-0 group/toggle"
                :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            >
                <font-awesome-icon
                    :icon="sidebarCollapsed ? 'angles-right' : 'angles-left'"
                    class="w-3 h-3 text-gray-400 group-hover/toggle:text-green-600 transition-colors duration-200"
                />
            </button>
        </div>

        <div class="px-2 py-3 pb-4 overflow-y-auto bg-white h-[calc(100%-80px)]">
            <!-- Search bar -->
            <div v-if="!sidebarCollapsed" class="mb-3 px-1">
                <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-green-50 border border-green-100 focus-within:border-green-400 focus-within:bg-white focus-within:shadow-sm transition-all duration-200">
                    <font-awesome-icon icon="magnifying-glass" class="w-3.5 h-3.5 text-green-500 flex-shrink-0" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search..."
                        class="flex-1 bg-transparent border-0 text-sm text-gray-700 placeholder-green-400 focus:outline-none focus:ring-0 min-w-0"
                    />
                    <button
                        v-if="searchQuery"
                        @click="searchQuery = ''"
                        class="flex items-center justify-center w-4 h-4 rounded-full bg-green-200 hover:bg-green-300 transition-colors flex-shrink-0"
                    >
                        <font-awesome-icon icon="xmark" class="w-2.5 h-2.5 text-green-700" />
                    </button>
                </div>
            </div>

            <!-- MENU label -->
            <p v-if="!sidebarCollapsed" class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-2 mb-2">Menu</p>

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
                        <div :class="iconBox(isActivePath('/airline-tickets'), 'lg', 'blue')">
                            <font-awesome-icon icon="plane-departure" :class="iconFa(isActivePath('/airline-tickets'), 'lg', 'blue')" />
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
                        <li v-if="hasPermission('bd-company.view') && matchesSearch('BD Companies')">
                            <Link href="/bd-companies" :class="linkClass('/bd-companies', true)">
                                <div :class="iconBox(isActivePath('/bd-companies', true), 'sm', 'violet')">
                                    <font-awesome-icon icon="building" :class="iconFa(isActivePath('/bd-companies', true), 'sm', 'violet')" />
                                </div>
                                <span class="ml-3">BD Companies</span>
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
                            <button type="button" class="flex items-center w-full p-2 rounded-lg transition-colors duration-200 text-gray-600 hover:bg-green-50 hover:text-green-700 text-sm group" @click="toggleIncome">
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
                            <button type="button" class="flex items-center w-full p-2 rounded-lg transition-colors duration-200 text-gray-600 hover:bg-green-50 hover:text-green-700 text-sm group" @click="toggleCostOfSales">
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
                            <button type="button" class="flex items-center w-full p-2 rounded-lg transition-colors duration-200 text-gray-600 hover:bg-green-50 hover:text-green-700 text-sm group" @click="toggleOperatingExpenses">
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
                        :class="['flex items-center w-full p-2 rounded-lg transition-colors duration-200 text-gray-600 hover:bg-red-50 hover:text-red-600 group', sidebarCollapsed ? 'justify-center' : '']"
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
const appName = computed(() => $page.props.settings?.app_name || "MITT");

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

// --- Color map ---
const colorMap = {
    blue:   { box: "bg-blue-100 group-hover:bg-blue-50",     fa: "text-blue-600 group-hover:text-blue-500" },
    orange: { box: "bg-orange-100 group-hover:bg-orange-50", fa: "text-orange-500 group-hover:text-orange-400" },
    violet: { box: "bg-violet-100 group-hover:bg-violet-50", fa: "text-violet-600 group-hover:text-violet-500" },
    teal:   { box: "bg-teal-100 group-hover:bg-teal-50",     fa: "text-teal-600 group-hover:text-teal-500" },
    rose:   { box: "bg-rose-100 group-hover:bg-rose-50",     fa: "text-rose-500 group-hover:text-rose-400" },
    indigo: { box: "bg-indigo-100 group-hover:bg-indigo-50", fa: "text-indigo-600 group-hover:text-indigo-500" },
    amber:  { box: "bg-amber-100 group-hover:bg-amber-50",   fa: "text-amber-600 group-hover:text-amber-500" },
    sky:    { box: "bg-sky-100 group-hover:bg-sky-50",       fa: "text-sky-600 group-hover:text-sky-500" },
    red:    { box: "bg-red-100 group-hover:bg-red-50",       fa: "text-red-500 group-hover:text-red-400" },
};

const iconBox = (active, size = "lg", color = "blue") => {
    const sz = size === "lg" ? "w-7 h-7" : "w-6 h-6";
    const base = `flex items-center justify-center ${sz} rounded-md transition-colors duration-200`;
    if (active) return `${base} bg-green-100`;
    const c = colorMap[color] || colorMap.blue;
    return `${base} ${c.box}`;
};

const iconFa = (active, size = "lg", color = "blue") => {
    const sz = size === "lg" ? "w-4 h-4" : "w-3.5 h-3.5";
    const base = `${sz} transition-colors duration-200`;
    if (active) return `${base} text-green-700`;
    const c = colorMap[color] || colorMap.blue;
    return `${base} ${c.fa}`;
};

// --- Link / button class helpers ---
const linkClass = (path, exact = false) => {
    const active = isActivePath(path, exact);
    const base = "flex items-center p-2 rounded-lg transition-all duration-200 group";
    if (sidebarCollapsed.value) {
        return [base, "justify-center", active ? "bg-green-50 text-green-800 font-semibold" : "text-gray-600 hover:bg-green-50 hover:text-green-700"];
    }
    return [base, "border-l-[3px] pl-[5px]", active
        ? "bg-green-50 text-green-800 font-semibold border-green-700"
        : "text-gray-600 hover:bg-green-50 hover:text-green-700 border-transparent",
    ];
};

const subLinkClass = (path) => {
    const active = currentPath.value === path;
    return ["flex items-center p-2 text-sm font-medium rounded-lg transition-all duration-200 border-l-[3px] pl-[5px] group",
        active ? "bg-green-50 text-green-800 border-green-600" : "text-gray-500 hover:bg-green-50 hover:text-green-700 border-transparent",
    ];
};

const dropdownBtnClass = (childPaths) => {
    const active = isAnyChildActive(childPaths);
    const base = "flex items-center w-full p-2 rounded-lg transition-colors duration-200 group";
    if (sidebarCollapsed.value) {
        return [base, "justify-center", active ? "bg-green-50 text-green-700" : "text-gray-600 hover:bg-green-50 hover:text-green-700"];
    }
    return [base, active ? "text-green-700" : "text-gray-600 hover:bg-green-50 hover:text-green-700"];
};

// --- Search ---
const searchQuery = ref("");
const sq = computed(() => searchQuery.value.toLowerCase().trim());
const matchesSearch = (label) => !sq.value || label.toLowerCase().includes(sq.value);

const operationsVisible = computed(() => matchesSearch("Operations") || matchesSearch("Quotation") || matchesSearch("Invoice") || matchesSearch("Airline Tickets"));
const registrationsVisible = computed(() =>
    matchesSearch("Registrations") || matchesSearch("Clients") || matchesSearch("Agents") ||
    matchesSearch("Office Staff") || matchesSearch("BD Companies") || matchesSearch("Foreign Companies")
);
const accountingVisible = computed(() => matchesSearch("Accounts") || !sq.value);
const reportsVisible = computed(() => matchesSearch("Reports") || matchesSearch("Refund Report"));
const accessVisible = computed(() => matchesSearch("ACL") || matchesSearch("Users") || matchesSearch("Roles"));

watch(sq, (val) => {
    if (val) {
        if (operationsVisible.value) operationsOpen.value = true;
        if (registrationsVisible.value) registrationsOpen.value = true;
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
