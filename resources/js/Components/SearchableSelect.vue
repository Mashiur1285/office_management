<template>
    <div class="relative" ref="wrapperRef">
        <!-- Trigger -->
        <button
            type="button"
            @click="toggle"
            class="w-full flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm text-left transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            :class="modelValue ? 'text-gray-900' : 'text-gray-400'"
        >
            <span class="truncate">{{ selectedLabel }}</span>
            <svg
                class="h-4 w-4 text-gray-400 flex-shrink-0 transition-transform duration-200"
                :class="open ? 'rotate-180' : ''"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown -->
        <div
            v-if="open"
            class="absolute z-50 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-xl"
        >
            <!-- Search input -->
            <div class="p-2 border-b border-gray-100">
                <div class="relative">
                    <svg
                        class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        ref="searchRef"
                        v-model="search"
                        type="text"
                        placeholder="Search client..."
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white"
                        @keydown.esc="open = false"
                    />
                </div>
            </div>

            <!-- Options list -->
            <ul class="max-h-56 overflow-y-auto py-1">
                <li
                    v-if="filtered.length === 0"
                    class="px-4 py-3 text-sm text-gray-400 text-center"
                >
                    No client found
                </li>
                <li
                    v-for="option in filtered"
                    :key="option.id"
                    @click="select(option)"
                    class="flex items-center justify-between px-4 py-2.5 text-sm cursor-pointer hover:bg-blue-50 transition"
                    :class="modelValue === option.id ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-800'"
                >
                    <span>{{ option.name }}</span>
                    <span v-if="option.passport_number" class="text-xs text-gray-400 ml-2">
                        {{ option.passport_number }}
                    </span>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onBeforeUnmount } from "vue";

const props = defineProps({
    modelValue: { default: "" },
    options: { type: Array, default: () => [] },
    placeholder: { type: String, default: "Select client" },
});

const emit = defineEmits(["update:modelValue", "change"]);

const open = ref(false);
const search = ref("");
const wrapperRef = ref(null);
const searchRef = ref(null);

const selectedLabel = computed(() => {
    if (!props.modelValue) return props.placeholder;
    const found = props.options.find((o) => o.id === props.modelValue);
    return found ? found.name : props.placeholder;
});

const filtered = computed(() => {
    const q = search.value.toLowerCase().trim();
    if (!q) return props.options;
    return props.options.filter(
        (o) =>
            o.name?.toLowerCase().includes(q) ||
            o.passport_number?.toLowerCase().includes(q)
    );
});

const toggle = () => {
    open.value = !open.value;
    if (open.value) {
        search.value = "";
        nextTick(() => searchRef.value?.focus());
    }
};

const select = (option) => {
    emit("update:modelValue", option.id);
    emit("change");
    open.value = false;
    search.value = "";
};

const handleOutsideClick = (e) => {
    if (wrapperRef.value && !wrapperRef.value.contains(e.target)) {
        open.value = false;
    }
};

onMounted(() => document.addEventListener("mousedown", handleOutsideClick));
onBeforeUnmount(() => document.removeEventListener("mousedown", handleOutsideClick));
</script>
