<template>
    <div class="relative" ref="dropdownRef">
        <button
            type="button"
            @click="toggleDropdown"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-left flex items-center justify-between"
        >
            <span v-if="selectedType" class="text-gray-900">{{ selectedType.label }}</span>
            <span v-else class="text-gray-500">Select holder type</span>
            <i class="fa-solid fa-chevron-down text-gray-400"></i>
        </button>

        <div
            v-if="showDropdown"
            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-80 overflow-auto"
        >
            <div
                v-for="type in holderTypes"
                :key="type.id"
                class="flex items-center justify-between px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100"
            >
                <div @click="selectType(type)" class="flex-1">
                    <span class="text-gray-900">{{ type.label }}</span>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    modelValue: String,
    holderTypes: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']);

const showDropdown = ref(false);

const selectedType = computed(() => {
    if (!props.modelValue) return null;
    return props.holderTypes.find(type => type.value === props.modelValue) || null;
});

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

const selectType = (type) => {
    emit('update:modelValue', type.value);
    showDropdown.value = false;
};


const dropdownRef = ref(null);
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        showDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

watch(
    () => props.holderTypes,
    () => {
        if (props.modelValue && !selectedType.value) {
            emit('update:modelValue', '');
        }
    }
);
</script>
