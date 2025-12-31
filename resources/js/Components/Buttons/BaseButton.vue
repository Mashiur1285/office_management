<template>
    <FwbButton
        :pill="pill"
        :size="size"
        :class="btnClass"
        :color="color"
        :outline="outline"
        :gradient="gradient"
        :disabled="disabled"
        :href="href"
        :loading="loading"
        :type="type"
        @click="$emit('onClick')"
    >
        <template #prefix>
            <FontAwesomeIcon v-if="prefixIcon" :icon="prefixIcon" />
        </template>

        {{ label }}

        <template #suffix>
            <FontAwesomeIcon v-if="suffixIcon" :icon="suffixIcon" />
        </template>
    </FwbButton>
</template>

<script setup>
import { FwbButton } from 'flowbite-vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const props = defineProps({
    label: { type: String, default: 'Label' },
    color: { type: String, default: 'default' },
    size: { type: String, default: 'md' },
    btnClass: { type: String, default: '' },
    pill: { type: Boolean, default: true },
    outline: { type: Boolean, default: false },
    gradient: { type: [String, null], default: null },
    type: { type: String, default: 'button' },
    disabled: { type: Boolean, default: false },
    href: { type: String, default: undefined },
    loading: { type: Boolean, default: false },
    prefixIcon: { type: String, default: undefined },
    suffixIcon: { type: String, default: undefined },
});

defineEmits(['onClick']);
</script>
