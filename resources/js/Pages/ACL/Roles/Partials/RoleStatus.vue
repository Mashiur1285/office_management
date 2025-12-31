<template>
    <FwbToggle v-model="formData.is_active" @update:model-value="updateStatus" />
</template>

<script setup>
import { FwbToggle } from 'flowbite-vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    data: { type: Object, required: true },
});

const formData = useForm({
    is_active: props.data.is_active,
});

const updateStatus = () => {
    formData.put(route('roles.update', { role: props.data.id }), {
        preserveScroll: true,
    });
};
</script>
