<template>
    <!-- Authenticated Layout -->
    <div v-if="isAuth" class="bg-gray-50">
        <TheNavbar class="print:hidden" />
        <TheSidebar class="print:hidden" />

        <div
            class="flex flex-col min-h-screen justify-between transition-all duration-200 sm:ml-64 pt-16 print:sm:ml-0 print:pt-0 layout-main-container bg-gray-50"
        >
            <!-- Main Content Area -->
            <div class="flex-1 p-3 md:p-4 lg:p-5 print:p-0">
                <div class="max-w-7xl mx-auto">
                    <slot />
                </div>
            </div>

            <!-- Footer -->
            <TheFooter />
        </div>
    </div>

    <!-- Guest/Non-Authenticated Layout -->
    <div
        v-else
        class="min-h-screen bg-gradient-to-br from-gray-50 via-gray-100 to-gray-200"
    >
        <!-- Decorative Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div
                class="absolute top-0 -left-4 w-72 h-72 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"
            ></div>
            <div
                class="absolute top-0 -right-4 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"
            ></div>
            <div
                class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000"
            ></div>
        </div>

        <!-- Content Container -->
        <div
            class="relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4"
        >
            <slot />
        </div>
    </div>
</template>

<script setup>
import { usePage } from "@inertiajs/vue3";
import TheFooter from "./TheFooter.vue";
import TheNavbar from "./Navbar/TheNavbar.vue";
import TheSidebar from "./Sidebar/TheSidebar.vue";
import { computed } from "vue";

const page = usePage();
const isAuth = computed(() => page.props?.auth?.user);
</script>

<style scoped>
/* Animated blob effects for guest layout */
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
