<template>
    <li>
        <div class="flex flex-col space-y-2">
            <div
                class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group cursor-pointer"
                :class="{ 'bg-gray-100': active }"
                @click="states.rotate = !states.rotate"
            >
                <!-- Icon -->
                <div
                    class="flex-shrink-0 w-6 h-6 flex items-center justify-center"
                >
                    <FontAwesomeIcon
                        :icon="['fas', icon.split(' ').pop() || 'layer-group']"
                        class="w-5 h-5 text-gray-700"
                    />
                </div>
                <!-- Label -->
                <span class="ml-3 text-sm tracking-wide truncate flex-1">{{
                    label
                }}</span>
                <!-- Down Arrow -->
                <div
                    class="flex-shrink-0 w-6 h-6 flex items-center justify-center"
                >
                    <FontAwesomeIcon
                        :icon="['fas', 'chevron-down']"
                        class="w-[12px] h-4 text-gray-700 transition-transform duration-200 ease-in-out"
                        :class="{ 'rotate-180': states.rotate }"
                    />
                </div>
            </div>

            <!-- Submenu -->
            <div v-if="states.rotate" class="ml-6 space-y-1">
                <SidebarSingleLevelMenu
                    v-for="menu in submenu"
                    :key="menu.href"
                    :label="menu.label"
                    :href="menu.href"
                    :icon="menu.icon"
                    :active="$page.url === menu.href"
                />
            </div>
        </div>
    </li>
</template>

<script setup lang="ts">
import { computed, reactive } from "vue";
import SidebarSingleLevelMenu from "./SidebarSingleLevelMenu.vue";
import { Submenu } from "@/App/Utils/types";
import { usePage } from "@inertiajs/vue3";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

interface Props {
    label: string;
    icon: string;
    submenu: Submenu[];
}

const props = withDefaults(defineProps<Props>(), {
    label: "label",
    icon: "fa-solid fa-layer-group",
    submenu: () => [
        {
            label: "Dashboard",
            href: route("dashboard"),
            icon: "fa-solid fa-house",
        },
        {
            label: "Departments",
            href: route("departments.index"),
            icon: "fa-solid fa-building",
        },
    ],
});

interface State {
    rotate: boolean;
}

const states: State = reactive({
    rotate: false,
});

const page = usePage();

const active = computed<boolean>(() => {
    return props.submenu.some((item) => item.href === page.url);
});
</script>

<style scoped>
.group {
    transition: background-color 0.2s ease;
}

.w-6 {
    min-width: 1.5rem; /* 24px */
}

.text-gray-700 {
    color: #374151; /* Tailwind gray-700 */
}

.group:hover .text-gray-700 {
    color: #374151; /* Maintain color on hover */
}
</style>
