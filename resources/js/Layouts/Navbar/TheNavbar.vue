<template>
    <nav
        class="fixed top-0 z-50 w-full bg-white border-b border-gray-200 shadow-sm"
    >
        <div
            class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between"
        >
            <Link href="/" class="flex items-center space-x-2">
                <img
                    :src="logoUrl"
                    alt="logo"
                    class="h-10 w-auto"
                />
                <span class="text-lg font-semibold text-gray-800">{{ appName }}</span>
            </Link>
            <div class="flex items-center space-x-6">
                <div class="relative">
                    <button
                        type="button"
                        class="flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 focus:outline-none"
                        @click="dropdownOpen = !dropdownOpen"
                    >
                        <span class="mr-2">{{ userName }}</span>
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
import { Link, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const dropdownOpen = ref(false);
const page = usePage();
const userName = computed(() => page.props.auth?.user?.name || "User");
const appName = computed(() => page.props.settings?.app_name || "MITT");
const logoUrl = computed(() => page.props.settings?.logo_url || "/images/mtt-logo.png");
</script>
