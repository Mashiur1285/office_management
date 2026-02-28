<template>
    <nav
        class="fixed top-0 z-50 w-full bg-white border-b border-gray-100 shadow-sm sm:pl-64"
    >
        <div
            class="px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between"
        >
            <!-- Page Title / Breadcrumb area -->
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">
                        {{ currentPageLabel }}
                    </span>
                </div>
            </div>
            <div class="flex items-center space-x-6">
                <div class="relative" ref="notificationRef">
                    <button
                        type="button"
                        class="relative flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm font-medium text-gray-600 hover:border-emerald-300 hover:text-emerald-700 hover:bg-emerald-50 focus:outline-none transition"
                        @click="notificationsOpen = !notificationsOpen"
                    >
                        <div class="relative">
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0h6z"
                                />
                            </svg>
                            <span
                                v-if="notificationCount"
                                class="absolute -top-2 -right-2 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-600 px-1.5 text-xs font-bold text-white animate-pulse"
                            >
                                {{ notificationCount }}
                            </span>
                        </div>
                        <span class="hidden sm:inline">Notifications</span>
                        <svg
                            class="w-4 h-4 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                    </button>
                    <div
                        v-if="notificationsOpen"
                        class="absolute right-0 mt-3 w-96 bg-white border border-gray-200 rounded-2xl shadow-2xl py-3 z-50"
                    >
                        <div class="px-4 pb-3 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-semibold text-gray-500 uppercase">
                                    Overdue Files
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="text-xs font-semibold text-red-600 bg-red-50 px-2 py-0.5 rounded-full">
                                        8+ Days
                                    </div>
                                    <button
                                        type="button"
                                        class="text-xs font-semibold text-blue-600 hover:text-blue-800"
                                        @click="resetSeenNotifications"
                                    >
                                        Reset
                                    </button>
                                </div>
                            </div>
                            <div class="mt-1 text-sm text-gray-600">
                                Clients with files Pending at BD company.
                            </div>
                        </div>
                        <div v-if="!notificationItems.length" class="px-4 py-6 text-sm text-gray-500 text-center">
                            No overdue files.
                        </div>
                        <button
                            v-for="item in visibleNotifications"
                            :key="item.client_id"
                            class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition"
                            @click="goToClient(item.client_id)"
                        >
                            <div class="flex items-start gap-3">
                                <div class="mt-1 h-2.5 w-2.5 rounded-full bg-red-500"></div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div class="font-semibold text-gray-900">
                                            {{ item.client_name }}
                                        </div>
                                        <div class="text-xs font-semibold text-red-600">
                                            {{ item.days }} days
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Received: {{ item.received_at }}
                                    </div>
                                </div>
                            </div>
                        </button>
                        <button
                            v-if="notificationItems.length > 5"
                            type="button"
                            class="w-full text-left px-4 py-2 text-sm font-semibold text-blue-600 hover:text-blue-800 hover:bg-blue-50"
                            @click="showAllNotifications = true"
                        >
                            See more
                        </button>
                    </div>
                </div>
                <div class="relative" ref="dropdownRef">
                    <button
                        type="button"
                        class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-emerald-700 focus:outline-none bg-gray-50 border border-gray-200 rounded-full px-3 py-1.5 hover:border-emerald-300 hover:bg-emerald-50 transition"
                        @click="dropdownOpen = !dropdownOpen"
                    >
                        <div class="w-6 h-6 rounded-full bg-emerald-600 flex items-center justify-center text-white text-xs font-bold">
                            {{ userName.charAt(0).toUpperCase() }}
                        </div>
                        <span class="mr-1">{{ userName }}</span>
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                    </button>
                    <div
                        v-if="dropdownOpen"
                        class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-50"
                    >
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            @click="dropdownOpen = false"
                        >
                            Logout
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { Link, usePage, router } from "@inertiajs/vue3";
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import axios from "axios";

const dropdownOpen = ref(false);
const dropdownRef = ref(null);
const notificationsOpen = ref(false);
const notificationRef = ref(null);
const notificationItems = ref([]);
const showAllNotifications = ref(false);
const seenNotificationIds = ref(new Set());
const notificationCount = computed(() =>
    notificationItems.value.filter((item) => !seenNotificationIds.value.has(item.client_id)).length
);
const visibleNotifications = computed(() =>
    showAllNotifications.value
        ? notificationItems.value
        : notificationItems.value.slice(0, 5)
);
const page = usePage();
const userName = computed(() => page.props.auth?.user?.name || "User");
const appName = computed(() => page.props.settings?.app_name || "MITT");
const logoUrl = computed(() => page.props.settings?.logo_url || "/images/mtt-logo.png");

const currentPageLabel = computed(() => {
    const path = page.url.split("?")[0];
    if (path === "/dashboard") return "Dashboard";
    if (path.startsWith("/clients")) return "Clients";
    if (path.startsWith("/agents")) return "Agents";
    if (path.startsWith("/office-staff")) return "Office Staff";
    if (path.startsWith("/bd-companies")) return "BD Companies";
    if (path.startsWith("/foreign-companies")) return "Foreign Companies";
    if (path.startsWith("/invoices")) return "Invoices";
    if (path.startsWith("/quotations")) return "Quotations";
    if (path.startsWith("/accounting")) return "Accounting";
    if (path.startsWith("/reports")) return "Reports";
    if (path.startsWith("/settings")) return "Settings";
    if (path.startsWith("/users")) return "Users";
    if (path.startsWith("/roles")) return "Roles";
    if (path.startsWith("/notepad")) return "Notepad";
    return appName.value;
});

const handleClickOutside = (event) => {
    if (!dropdownOpen.value) return;
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        dropdownOpen.value = false;
    }
};

const handleNotificationClickOutside = (event) => {
    if (!notificationsOpen.value) return;
    if (notificationRef.value && !notificationRef.value.contains(event.target)) {
        notificationsOpen.value = false;
    }
};

const loadSeenNotifications = () => {
    try {
        const stored = localStorage.getItem("seen_overdue_notifications");
        const parsed = stored ? JSON.parse(stored) : [];
        seenNotificationIds.value = new Set(parsed);
    } catch {
        seenNotificationIds.value = new Set();
    }
};

const saveSeenNotifications = () => {
    localStorage.setItem(
        "seen_overdue_notifications",
        JSON.stringify([...seenNotificationIds.value])
    );
};

const resetSeenNotifications = () => {
    seenNotificationIds.value = new Set();
    saveSeenNotifications();
};

const markAllAsSeen = () => {
    notificationItems.value.forEach((item) => {
        seenNotificationIds.value.add(item.client_id);
    });
    saveSeenNotifications();
};

const fetchNotifications = async () => {
    try {
        const { data } = await axios.get(route("notifications.document-overdue"));
        notificationItems.value = data.items || [];
        showAllNotifications.value = false;
    } catch (error) {
        notificationItems.value = [];
    }
};

const goToClient = (clientId) => {
    notificationsOpen.value = false;
    seenNotificationIds.value.add(clientId);
    saveSeenNotifications();
    router.visit(`/clients/${clientId}`);
};

let notificationTimer = null;

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
    document.addEventListener("click", handleNotificationClickOutside);
    loadSeenNotifications();
    fetchNotifications();
    notificationTimer = setInterval(fetchNotifications, 60000);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
    document.removeEventListener("click", handleNotificationClickOutside);
    if (notificationTimer) {
        clearInterval(notificationTimer);
    }
});

watch(
    () => notificationsOpen.value,
    (isOpen) => {
        if (isOpen) {
            markAllAsSeen();
        }
    }
);
</script>
