<template>
    <button
        v-if="tooltip"
        :data-tooltip-target="icon"
        :disabled="disabled"
        @click="$emit('click')"
    >
        <FontAwesomeIcon :class="[defaultIconClass, extraClass, disabledClass]" :icon="icon" />
    </button>
    <button v-else :disabled="disabled" @click="$emit('click')">
        <FontAwesomeIcon :class="[defaultIconClass, extraClass, disabledClass]" :icon="icon" />
    </button>
    <div
        v-if="tooltip"
        :id="icon"
        role="tooltip"
        class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip"
    >
        {{ tooltip }}
        <div class="tooltip-arrow" data-popper-arrow></div>
    </div>
</template>

<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { computed } from 'vue';

const props = defineProps({
    extraClass: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
    icon: { type: String, default: 'fa-solid fa-pen' },
    tooltip: { type: String, default: null },
});

const defaultIconClass = 'p-2 rounded-full bg-blue-600 text-white cursor-pointer hover:opacity-80';
const disabledClass = computed(() => (props.disabled ? 'bg-gray-300 cursor-not-allowed' : ''));

defineEmits(['click']);
</script>
