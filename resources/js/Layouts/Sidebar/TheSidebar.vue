<template>
    <aside
        id="logo-sidebar"
        class="fixed top-0 left-0 z-[60] w-64 h-screen transition-transform -translate-x-full bg-gray-900 border-r border-gray-800 sm:translate-x-0 shadow-xl"
        aria-label="Sidebar"
    >
        <div class="h-full px-3 pt-4 pb-4 overflow-y-auto bg-gray-900">
            <ul class="space-y-2 font-medium">
                <li v-if="hasPermission('dashboard.view')">
                    <Link href="/dashboard" :class="linkClass('/dashboard')">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-500"
                        >
                            <font-awesome-icon
                                icon="house"
                                class="w-5 h-5 text-white"
                            />
                        </div>
                        <span class="ml-3">Dashboard</span>
                    </Link>
                </li>

                <li v-if="hasPermission('quotation.view')">
                    <Link href="/quotations" :class="linkClass('/quotations')">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-amber-500"
                        >
                            <font-awesome-icon
                                icon="file-invoice"
                                class="w-5 h-5 text-white"
                            />
                        </div>
                        <span class="ml-3">Quotation</span>
                    </Link>
                </li>

                <li v-if="hasPermission('invoice.view')">
                    <Link href="/invoices" :class="linkClass('/invoices')">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-orange-500"
                        >
                            <font-awesome-icon
                                icon="file-invoice"
                                class="w-5 h-5 text-white"
                            />
                        </div>
                        <span class="ml-3">Invoice</span>
                    </Link>
                </li>

                <li v-if="hasPermission('client.view')">
                    <Link href="/clients" :class="linkClass('/clients')">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-green-500"
                        >
                            <font-awesome-icon
                                icon="id-card"
                                class="w-5 h-5 text-white"
                            />
                        </div>
                        <span class="ml-3">Clients</span>
                    </Link>
                </li>

                <li v-if="hasPermission('agent.view')">
                    <Link href="/agents" :class="linkClass('/agents')">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-cyan-500"
                        >
                            <font-awesome-icon
                                icon="address-card"
                                class="w-5 h-5 text-white"
                            />
                        </div>
                        <span class="ml-3">Agents</span>
                    </Link>
                </li>

                <li v-if="hasPermission('office-staff.view')">
                    <Link
                        href="/office-staff"
                        :class="linkClass('/office-staff')"
                    >
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-sky-500"
                        >
                            <font-awesome-icon
                                icon="users"
                                class="w-5 h-5 text-white"
                            />
                        </div>
                        <span class="ml-3">Office Staff</span>
                    </Link>
                </li>

                <li v-if="hasPermission('bd-company.view')">
                    <Link
                        href="/bd-companies"
                        :class="linkClass('/bd-companies')"
                    >
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-purple-500"
                        >
                            <font-awesome-icon
                                icon="building"
                                class="w-5 h-5 text-white"
                            />
                        </div>
                        <span class="ml-3">BD Companies</span>
                    </Link>
                </li>

                <li v-if="hasPermission('foreign-company.view')">
                    <Link
                        href="/foreign-companies"
                        :class="linkClass('/foreign-companies')"
                    >
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-500"
                        >
                            <font-awesome-icon
                                icon="globe"
                                class="w-5 h-5 text-white"
                            />
                        </div>
                        <span class="ml-3">Foreign Companies</span>
                    </Link>
                </li>

                <li>
                    <button
                        type="button"
                        class="flex items-center w-full p-2 rounded-lg transition-colors duration-200 text-white hover:bg-gray-800"
                        @click="toggleAccounting"
                    >
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-pink-500"
                        >
                            <font-awesome-icon
                                icon="chart-line"
                                class="w-5 h-5 text-white"
                            />
                        </div>
                        <span class="flex-1 ml-3 text-left whitespace-nowrap"
                            >Accounting</span
                        >
                        <svg
                            class="w-3 h-3 transition-transform duration-200"
                            :class="{ 'rotate-180': accountingOpen }"
                            aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 10 6"
                        >
                            <path
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m1 1 4 4 4-4"
                            />
                        </svg>
                    </button>
                    <ul v-show="accountingOpen" class="py-2 space-y-2 ml-3">
                        <li>
                            <Link
                                href="/accounting"
                                :class="linkClass('/accounting', true)"
                            >
                                <div
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-500"
                                >
                                    <font-awesome-icon
                                        icon="gauge"
                                        class="w-4 h-4 text-white"
                                    />
                                </div>
                                <span class="ml-3">Dashboard</span>
                            </Link>
                        </li>
                        <li>
                            <button
                                type="button"
                                class="flex items-center w-full p-2 rounded-lg transition-colors duration-200 text-white hover:bg-gray-800 text-base"
                                @click="toggleIncome"
                            >
                                <div
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-green-500"
                                >
                                    <font-awesome-icon
                                        icon="money-bill-trend-up"
                                        class="w-4 h-4 text-white"
                                    />
                                </div>
                                <span
                                    class="flex-1 ml-3 text-left whitespace-nowrap"
                                    >Income</span
                                >
                                <svg
                                    class="w-3 h-3 transition-transform duration-200"
                                    :class="{ 'rotate-180': incomeOpen }"
                                    aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 10 6"
                                >
                                    <path
                                        stroke="currentColor"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="m1 1 4 4 4-4"
                                    />
                                </svg>
                            </button>
                            <ul v-show="incomeOpen" class="py-2 space-y-1 ml-6">
                                <li>
                                    <Link
                                        href="/accounting/income/travel-tourism"
                                        :class="subLinkClass('/accounting/income/travel-tourism')"
                                    >
                                        <font-awesome-icon
                                            icon="plane"
                                            class="w-4 h-4"
                                        />
                                        <span class="ml-3"
                                            >Travel & Tourism</span
                                        >
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href="/accounting/income/manpower"
                                        :class="subLinkClass('/accounting/income/manpower')"
                                    >
                                        <font-awesome-icon
                                            icon="user-tie"
                                            class="w-4 h-4"
                                        />
                                        <span class="ml-3"
                                            >Manpower Exporting</span
                                        >
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href="/accounting/income/student"
                                        :class="subLinkClass('/accounting/income/student')"
                                    >
                                        <font-awesome-icon
                                            icon="graduation-cap"
                                            class="w-4 h-4"
                                        />
                                        <span class="ml-3"
                                            >Student Package</span
                                        >
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href="/accounting/income/other"
                                        :class="subLinkClass('/accounting/income/other')"
                                    >
                                        <font-awesome-icon
                                            icon="hand-holding-dollar"
                                            class="w-4 h-4"
                                        />
                                        <span class="ml-3">Other Income</span>
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <button
                                type="button"
                                class="flex items-center w-full p-2 rounded-lg transition-colors duration-200 text-white hover:bg-gray-800 text-base"
                                @click="toggleCostOfSales"
                            >
                                <div
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-orange-500"
                                >
                                    <font-awesome-icon
                                        icon="box"
                                        class="w-4 h-4 text-white"
                                    />
                                </div>
                                <span
                                    class="flex-1 ml-3 text-left whitespace-nowrap"
                                    >Cost of Sales</span
                                >
                                <svg
                                    class="w-3 h-3 transition-transform duration-200"
                                    :class="{ 'rotate-180': costOfSalesOpen }"
                                    aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 10 6"
                                >
                                    <path
                                        stroke="currentColor"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="m1 1 4 4 4-4"
                                    />
                                </svg>
                            </button>
                            <ul
                                v-show="costOfSalesOpen"
                                class="py-2 space-y-1 ml-6"
                            >
                                <li>
                                    <Link
                                        href="/accounting/cost-of-sales/travel-tourism"
                                        :class="subLinkClass('/accounting/cost-of-sales/travel-tourism')"
                                    >
                                        <font-awesome-icon
                                            icon="plane"
                                            class="w-4 h-4"
                                        />
                                        <span class="ml-3"
                                            >Travel & Tourism</span
                                        >
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href="/accounting/cost-of-sales/manpower"
                                        :class="subLinkClass('/accounting/cost-of-sales/manpower')"
                                    >
                                        <font-awesome-icon
                                            icon="user-tie"
                                            class="w-4 h-4"
                                        />
                                        <span class="ml-3"
                                            >Manpower Exporting</span
                                        >
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href="/accounting/cost-of-sales/student"
                                        :class="subLinkClass('/accounting/cost-of-sales/student')"
                                    >
                                        <font-awesome-icon
                                            icon="graduation-cap"
                                            class="w-4 h-4"
                                        />
                                        <span class="ml-3"
                                            >Student Package</span
                                        >
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <Link
                                href="/accounting/gross-profit"
                                :class="linkClass('/accounting/gross-profit', true)"
                            >
                                <div
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-teal-500"
                                >
                                    <font-awesome-icon
                                        icon="chart-line"
                                        class="w-4 h-4 text-white"
                                    />
                                </div>
                                <span class="ml-3">Gross Profit</span>
                            </Link>
                        </li>
                        <li>
                            <button
                                type="button"
                                class="flex items-center w-full p-2 rounded-lg transition-colors duration-200 text-white hover:bg-gray-800 text-base"
                                @click="toggleOperatingExpenses"
                            >
                                <div
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-purple-500"
                                >
                                    <font-awesome-icon
                                        icon="wallet"
                                        class="w-4 h-4 text-white"
                                    />
                                </div>
                                <span
                                    class="flex-1 ml-3 text-left whitespace-nowrap"
                                    >Operating Expenses</span
                                >
                                <svg
                                    class="w-3 h-3 transition-transform duration-200"
                                    :class="{
                                        'rotate-180': operatingExpensesOpen,
                                    }"
                                    aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 10 6"
                                >
                                    <path
                                        stroke="currentColor"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="m1 1 4 4 4-4"
                                    />
                                </svg>
                            </button>
                            <ul
                                v-show="operatingExpensesOpen"
                                class="py-2 space-y-1 ml-6"
                            >
                                <li>
                                    <Link
                                        href="/accounting/operating-expenses/employee"
                                        :class="subLinkClass('/accounting/operating-expenses/employee')"
                                    >
                                        <font-awesome-icon
                                            icon="users"
                                            class="w-4 h-4"
                                        />
                                        <span class="ml-3"
                                            >Employee & Manpower</span
                                        >
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href="/accounting/operating-expenses/administrative"
                                        :class="subLinkClass('/accounting/operating-expenses/administrative')"
                                    >
                                        <font-awesome-icon
                                            icon="file-lines"
                                            class="w-4 h-4"
                                        />
                                        <span class="ml-3">Administrative</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href="/accounting/operating-expenses/selling-marketing"
                                        :class="subLinkClass('/accounting/operating-expenses/selling-marketing')"
                                    >
                                        <font-awesome-icon
                                            icon="bullhorn"
                                            class="w-4 h-4"
                                        />
                                        <span class="ml-3"
                                            >Selling & Marketing</span
                                        >
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href="/accounting/operating-expenses/general"
                                        :class="subLinkClass('/accounting/operating-expenses/general')"
                                    >
                                        <font-awesome-icon
                                            icon="list"
                                            class="w-4 h-4"
                                        />
                                        <span class="ml-3">General</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href="/accounting/operating-profit"
                                        :class="subLinkClass('/accounting/operating-profit')"
                                    >
                                        <font-awesome-icon
                                            icon="chart-line"
                                            class="w-4 h-4"
                                        />
                                        <span class="ml-3"
                                            >Operating Profit</span
                                        >
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <Link
                                href="/accounting/non-operating"
                                :class="linkClass('/accounting/non-operating', true)"
                            >
                                <div
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-yellow-500"
                                >
                                    <font-awesome-icon
                                        icon="coins"
                                        class="w-4 h-4 text-white"
                                    />
                                </div>
                                <span class="ml-3">Non-Operating</span>
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/accounting/net-profit-before-tax"
                                :class="linkClass('/accounting/net-profit-before-tax', true)"
                            >
                                <div
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-500"
                                >
                                    <font-awesome-icon
                                        icon="sack-dollar"
                                        class="w-4 h-4 text-white"
                                    />
                                </div>
                                <span class="ml-3">Net Profit Before Tax</span>
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/accounting/tax"
                                :class="linkClass('/accounting/tax', true)"
                            >
                                <div
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-red-500"
                                >
                                    <font-awesome-icon
                                        icon="file-invoice-dollar"
                                        class="w-4 h-4 text-white"
                                    />
                                </div>
                                <span class="ml-3">Tax Management</span>
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/accounting/net-profit-after-tax"
                                :class="linkClass('/accounting/net-profit-after-tax', true)"
                            >
                                <div
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-500"
                                >
                                    <font-awesome-icon
                                        icon="trophy"
                                        class="w-4 h-4 text-white"
                                    />
                                </div>
                                <span class="ml-3">Net Profit After Tax</span>
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/accounting/tax-summary"
                                :class="linkClass('/accounting/tax-summary', true)"
                            >
                                <div
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-500"
                                >
                                    <font-awesome-icon
                                        icon="file-invoice-dollar"
                                        class="w-4 h-4 text-white"
                                    />
                                </div>
                                <span class="ml-3">Tax Summary</span>
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/accounting/tax-report"
                                :class="linkClass('/accounting/tax-report', true)"
                            >
                                <div
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-orange-500"
                                >
                                    <font-awesome-icon
                                        icon="file-alt"
                                        class="w-4 h-4 text-white"
                                    />
                                </div>
                                <span class="ml-3">Tax Report</span>
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/accounting/vat-summary"
                                :class="linkClass('/accounting/vat-summary', true)"
                            >
                                <div
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-lime-500"
                                >
                                    <font-awesome-icon
                                        icon="percent"
                                        class="w-4 h-4 text-white"
                                    />
                                </div>
                                <span class="ml-3">VAT Summary</span>
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/accounting/vat-report"
                                :class="linkClass('/accounting/vat-report', true)"
                            >
                                <div
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-fuchsia-500"
                                >
                                    <font-awesome-icon
                                        icon="file-alt"
                                        class="w-4 h-4 text-white"
                                    />
                                </div>
                                <span class="ml-3">VAT Report</span>
                            </Link>
                        </li>
                    </ul>
                </li>

                <li v-if="hasPermission('settings.view')">
                    <Link href="/settings" :class="linkClass('/settings')">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-slate-500"
                        >
                            <font-awesome-icon
                                icon="gear"
                                class="w-5 h-5 text-white"
                            />
                        </div>
                        <span class="ml-3">Settings</span>
                    </Link>
                </li>

                <li v-if="showAccessManagement">
                    <button
                        type="button"
                        class="flex items-center w-full p-2 rounded-lg transition-colors duration-200 text-white hover:bg-gray-800"
                        @click="toggleAccess"
                    >
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-rose-500"
                        >
                            <font-awesome-icon
                                icon="user-shield"
                                class="w-5 h-5 text-white"
                            />
                        </div>
                        <span class="flex-1 ml-3 text-left whitespace-nowrap"
                            >Access Management</span
                        >
                        <svg
                            class="w-3 h-3 transition-transform duration-200"
                            :class="{ 'rotate-180': accessOpen }"
                            aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 10 6"
                        >
                            <path
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m1 1 4 4 4-4"
                            />
                        </svg>
                    </button>
                    <ul v-show="accessOpen" class="py-2 space-y-2 ml-3">
                        <li v-if="hasPermission('user.view')">
                            <Link href="/users" :class="linkClass('/users')">
                                <div
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-violet-500"
                                >
                                    <font-awesome-icon
                                        icon="users-gear"
                                        class="w-4 h-4 text-white"
                                    />
                                </div>
                                <span class="ml-3">Users</span>
                            </Link>
                        </li>
                        <li v-if="hasPermission('role.view')">
                            <Link href="/roles" :class="linkClass('/roles')">
                                <div
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-600"
                                >
                                    <font-awesome-icon
                                        icon="users-gear"
                                        class="w-4 h-4 text-white"
                                    />
                                </div>
                                <span class="ml-3">Roles</span>
                            </Link>
                        </li>
                    </ul>
                </li>
                <li>
                    <Link href="/notepad" :class="linkClass('/notepad')">
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-amber-500"
                        >
                            <font-awesome-icon
                                icon="file-lines"
                                class="w-5 h-5 text-white"
                            />
                        </div>
                        <span class="ml-3">My Notepad</span>
                    </Link>
                </li>

                <li>
                    <button
                        @click="showLogoutModal = true"
                        class="flex items-center w-full p-2 rounded-lg transition-colors duration-200 text-white hover:bg-gray-800"
                    >
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-600"
                        >
                            <font-awesome-icon
                                icon="arrow-right-from-bracket"
                                class="w-5 h-5 text-white"
                            />
                        </div>
                        <span class="ml-3">Logout</span>
                    </button>
                </li>
            </ul>
        </div>

        <!-- Logout Confirmation Modal -->
        <Modal
            :show="showLogoutModal"
            @close="showLogoutModal = false"
            max-width="sm"
        >
            <div class="p-6">
                <div
                    class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4"
                >
                    <font-awesome-icon
                        icon="arrow-right-from-bracket"
                        class="w-6 h-6 text-red-600"
                    />
                </div>
                <h3
                    class="text-lg font-semibold text-gray-900 text-center mb-2"
                >
                    Confirm Logout
                </h3>
                <p class="text-base text-gray-600 text-center mb-6">
                    Are you sure you want to logout?
                </p>
                <div class="flex gap-3">
                    <button
                        @click="showLogoutModal = false"
                        class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200 font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        @click="handleLogout"
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 font-medium"
                    >
                        Yes, Logout
                    </button>
                </div>
            </div>
        </Modal>
    </aside>
</template>

<script setup>
import { Link, usePage, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import Modal from "@/Components/Modal.vue";

const $page = usePage();
const userPermissions = computed(() => $page.props.userPermissions || []);

const hasPermission = (permission) =>
    userPermissions.value.includes(permission) ||
    userPermissions.value.includes("*") ||
    userPermissions.value.includes("superadmin");

const showAccessManagement =
    hasPermission("role.view") || hasPermission("user.view");

const currentPath = computed(() => $page.url.split("?")[0]);

const linkClass = (path, exact = false) => [
    "flex items-center p-2 rounded-lg transition-colors duration-200",
    (exact ? currentPath.value === path : currentPath.value.startsWith(path))
        ? "bg-gray-800 text-white hover:bg-gray-700"
        : "text-white hover:bg-gray-800",
];

const subLinkClass = (path) => [
    "flex items-center p-2 text-base font-medium rounded-lg transition-colors duration-200",
    currentPath.value === path
        ? "bg-gray-800 text-white"
        : "text-gray-300 hover:bg-gray-800 hover:text-white",
];

const accessOpen = ref(false);
const toggleAccess = () => {
    accessOpen.value = !accessOpen.value;
};

const accountingOpen = ref(false);
const toggleAccounting = () => {
    accountingOpen.value = !accountingOpen.value;
};

const incomeOpen = ref(false);
const toggleIncome = () => {
    incomeOpen.value = !incomeOpen.value;
};

const costOfSalesOpen = ref(false);
const toggleCostOfSales = () => {
    costOfSalesOpen.value = !costOfSalesOpen.value;
};

const operatingExpensesOpen = ref(false);
const toggleOperatingExpenses = () => {
    operatingExpensesOpen.value = !operatingExpensesOpen.value;
};

const showLogoutModal = ref(false);
const handleLogout = () => {
    router.post(route("logout"));
};
</script>
