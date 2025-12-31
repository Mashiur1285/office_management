<template>
    <aside
        id="logo-sidebar"
        class="fixed top-16 left-0 z-40 w-64 h-[calc(100vh-4rem)] transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 shadow"
        aria-label="Sidebar"
    >
        <div class="h-full px-3 pt-4 pb-4 overflow-y-auto bg-white">
            <ul class="space-y-2 font-medium">
                <li v-if="hasPermission('dashboard.view')">
                    <Link href="/dashboard" :class="linkClass('/dashboard')">
                        <font-awesome-icon icon="house" class="w-5 h-5" />
                        <span class="ml-3">Dashboard</span>
                    </Link>
                </li>

                <li v-if="hasPermission('client.view')">
                    <Link href="/clients" :class="linkClass('/clients')">
                        <font-awesome-icon icon="id-card" class="w-5 h-5" />
                        <span class="ml-3">Clients</span>
                    </Link>
                </li>

                <li v-if="hasPermission('agent.view')">
                    <Link href="/agents" :class="linkClass('/agents')">
                        <font-awesome-icon icon="address-card" class="w-5 h-5" />
                        <span class="ml-3">Agents</span>
                    </Link>
                </li>

                <li v-if="hasPermission('office-staff.view')">
                    <Link href="/office-staff" :class="linkClass('/office-staff')">
                        <font-awesome-icon icon="users" class="w-5 h-5" />
                        <span class="ml-3">Office Staff</span>
                    </Link>
                </li>

                <li v-if="hasPermission('bd-company.view')">
                    <Link href="/bd-companies" :class="linkClass('/bd-companies')">
                        <font-awesome-icon icon="building" class="w-5 h-5" />
                        <span class="ml-3">BD Companies</span>
                    </Link>
                </li>

                <li v-if="hasPermission('foreign-company.view')">
                    <Link href="/foreign-companies" :class="linkClass('/foreign-companies')">
                        <font-awesome-icon icon="globe" class="w-5 h-5" />
                        <span class="ml-3">Foreign Companies</span>
                    </Link>
                </li>

                <li v-if="hasPermission('expense.view')">
                    <Link href="/expenses" :class="linkClass('/expenses')">
                        <font-awesome-icon icon="receipt" class="w-5 h-5" />
                        <span class="ml-3">Expenses</span>
                    </Link>
                </li>

                <li>
                    <button
                        type="button"
                        class="flex items-center w-full p-2 rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100"
                        @click="toggleAccounting"
                    >
                        <font-awesome-icon icon="chart-line" class="w-5 h-5" />
                        <span class="flex-1 ml-3 text-left whitespace-nowrap">Accounting</span>
                        <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': accountingOpen }" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                        </svg>
                    </button>
                    <ul v-show="accountingOpen" class="py-2 space-y-2 ml-3">
                        <li>
                            <Link href="/accounting" :class="linkClass('/accounting')">
                                <font-awesome-icon icon="gauge" class="w-5 h-5" />
                                <span class="ml-3">Dashboard</span>
                            </Link>
                        </li>
                        <li>
                            <button
                                type="button"
                                class="flex items-center w-full p-2 rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100 text-sm"
                                @click="toggleIncome"
                            >
                                <font-awesome-icon icon="money-bill-trend-up" class="w-4 h-4" />
                                <span class="flex-1 ml-3 text-left whitespace-nowrap">Income</span>
                                <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': incomeOpen }" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                                </svg>
                            </button>
                            <ul v-show="incomeOpen" class="py-2 space-y-1 ml-6">
                                <li>
                                    <Link href="/accounting/income/travel-tourism" class="flex items-center p-2 text-sm font-medium rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                        <font-awesome-icon icon="plane" class="w-4 h-4" />
                                        <span class="ml-3">Travel & Tourism</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/income/manpower" class="flex items-center p-2 text-sm font-medium rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                        <font-awesome-icon icon="user-tie" class="w-4 h-4" />
                                        <span class="ml-3">Manpower Exporting</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/income/student" class="flex items-center p-2 text-sm font-medium rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                        <font-awesome-icon icon="graduation-cap" class="w-4 h-4" />
                                        <span class="ml-3">Student Package</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/income/other" class="flex items-center p-2 text-sm font-medium rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                        <font-awesome-icon icon="hand-holding-dollar" class="w-4 h-4" />
                                        <span class="ml-3">Other Income</span>
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <button
                                type="button"
                                class="flex items-center w-full p-2 rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100 text-sm"
                                @click="toggleCostOfSales"
                            >
                                <font-awesome-icon icon="box" class="w-4 h-4" />
                                <span class="flex-1 ml-3 text-left whitespace-nowrap">Cost of Sales</span>
                                <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': costOfSalesOpen }" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                                </svg>
                            </button>
                            <ul v-show="costOfSalesOpen" class="py-2 space-y-1 ml-6">
                                <li>
                                    <Link href="/accounting/cost-of-sales/travel-tourism" class="flex items-center p-2 text-sm font-medium rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                        <font-awesome-icon icon="plane" class="w-4 h-4" />
                                        <span class="ml-3">Travel & Tourism</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/cost-of-sales/manpower" class="flex items-center p-2 text-sm font-medium rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                        <font-awesome-icon icon="user-tie" class="w-4 h-4" />
                                        <span class="ml-3">Manpower Exporting</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/cost-of-sales/student" class="flex items-center p-2 text-sm font-medium rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                        <font-awesome-icon icon="graduation-cap" class="w-4 h-4" />
                                        <span class="ml-3">Student Package</span>
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <Link href="/accounting/gross-profit" class="flex items-center p-2 text-sm rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                <font-awesome-icon icon="chart-line" class="w-4 h-4" />
                                <span class="ml-3">Gross Profit</span>
                            </Link>
                        </li>
                        <li>
                            <button
                                type="button"
                                class="flex items-center w-full p-2 rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100 text-sm"
                                @click="toggleOperatingExpenses"
                            >
                                <font-awesome-icon icon="wallet" class="w-4 h-4" />
                                <span class="flex-1 ml-3 text-left whitespace-nowrap">Operating Expenses</span>
                                <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': operatingExpensesOpen }" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                                </svg>
                            </button>
                            <ul v-show="operatingExpensesOpen" class="py-2 space-y-1 ml-6">
                                <li>
                                    <Link href="/accounting/operating-expenses/employee" class="flex items-center p-2 text-sm font-medium rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                        <font-awesome-icon icon="users" class="w-4 h-4" />
                                        <span class="ml-3">Employee & Manpower</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/operating-expenses/administrative" class="flex items-center p-2 text-sm font-medium rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                        <font-awesome-icon icon="file-lines" class="w-4 h-4" />
                                        <span class="ml-3">Administrative</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/operating-expenses/selling-marketing" class="flex items-center p-2 text-sm font-medium rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                        <font-awesome-icon icon="bullhorn" class="w-4 h-4" />
                                        <span class="ml-3">Selling & Marketing</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/operating-expenses/general" class="flex items-center p-2 text-sm font-medium rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                        <font-awesome-icon icon="list" class="w-4 h-4" />
                                        <span class="ml-3">General</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/accounting/operating-profit" class="flex items-center p-2 text-sm font-semibold rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                        <font-awesome-icon icon="chart-line" class="w-4 h-4" />
                                        <span class="ml-3">Operating Profit</span>
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <Link href="/accounting/non-operating" class="flex items-center p-2 text-sm rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                <font-awesome-icon icon="coins" class="w-4 h-4" />
                                <span class="ml-3">Non-Operating</span>
                            </Link>
                        </li>
                        <li>
                            <Link href="/accounting/net-profit-before-tax" class="flex items-center p-2 text-sm rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                <font-awesome-icon icon="sack-dollar" class="w-4 h-4" />
                                <span class="ml-3">Net Profit Before Tax</span>
                            </Link>
                        </li>
                        <li>
                            <Link href="/accounting/tax" class="flex items-center p-2 text-sm rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                <font-awesome-icon icon="file-invoice-dollar" class="w-4 h-4" />
                                <span class="ml-3">Tax Management</span>
                            </Link>
                        </li>
                        <li>
                            <Link href="/accounting/net-profit-after-tax" class="flex items-center p-2 text-sm rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                <font-awesome-icon icon="trophy" class="w-4 h-4" />
                                <span class="ml-3">Net Profit After Tax</span>
                            </Link>
                        </li>
                        <li>
                            <Link href="/accounting/vat-summary" class="flex items-center p-2 text-sm rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100">
                                <font-awesome-icon icon="percent" class="w-4 h-4" />
                                <span class="ml-3">VAT Summary</span>
                            </Link>
                        </li>
                    </ul>
                </li>

                <li v-if="hasPermission('settings.view')">
                    <Link href="/settings" :class="linkClass('/settings')">
                        <font-awesome-icon icon="gear" class="w-5 h-5" />
                        <span class="ml-3">Settings</span>
                    </Link>
                </li>

                <li v-if="showAccessManagement">
                    <button
                        type="button"
                        class="flex items-center w-full p-2 rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100"
                        @click="toggleAccess"
                    >
                        <font-awesome-icon icon="user-shield" class="w-5 h-5" />
                        <span class="flex-1 ml-3 text-left whitespace-nowrap">Access Management</span>
                        <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': accessOpen }" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                        </svg>
                    </button>
                    <ul v-show="accessOpen" class="py-2 space-y-2 ml-3">
                        <li v-if="hasPermission('user.view')">
                            <Link href="/users" :class="linkClass('/users')">
                                <font-awesome-icon icon="users-gear" class="w-5 h-5" />
                                <span class="ml-3">Users</span>
                            </Link>
                        </li>
                        <li v-if="hasPermission('role.view')">
                            <Link href="/roles" :class="linkClass('/roles')">
                                <font-awesome-icon icon="users-gear" class="w-5 h-5" />
                                <span class="ml-3">Roles</span>
                            </Link>
                        </li>
                    </ul>
                </li>

                <li>
                    <button
                        @click="showLogoutModal = true"
                        class="flex items-center w-full p-2 rounded-lg transition-colors duration-200 text-gray-900 hover:bg-gray-100"
                    >
                        <font-awesome-icon icon="arrow-right-from-bracket" class="w-5 h-5" />
                        <span class="ml-3">Logout</span>
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
                <p class="text-sm text-gray-600 text-center mb-6">Are you sure you want to logout?</p>
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

const linkClass = (path) => [
    "flex items-center p-2 rounded-lg transition-colors duration-200",
    $page.url.startsWith(path)
        ? "bg-blue-50 text-blue-700 hover:bg-blue-100"
        : "text-gray-900 hover:bg-gray-100",
];

const accessOpen = ref(true);
const toggleAccess = () => {
    accessOpen.value = !accessOpen.value;
};

const accountingOpen = ref(true);
const toggleAccounting = () => {
    accountingOpen.value = !accountingOpen.value;
};

const incomeOpen = ref(true);
const toggleIncome = () => {
    incomeOpen.value = !incomeOpen.value;
};

const costOfSalesOpen = ref(true);
const toggleCostOfSales = () => {
    costOfSalesOpen.value = !costOfSalesOpen.value;
};

const operatingExpensesOpen = ref(true);
const toggleOperatingExpenses = () => {
    operatingExpensesOpen.value = !operatingExpensesOpen.value;
};

const showLogoutModal = ref(false);
const handleLogout = () => {
    router.post(route('logout'));
};
</script>
