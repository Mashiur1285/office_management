<template>
    <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4 shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <div class="font-semibold text-gray-800">{{ title }}</div>
            <label class="inline-flex items-center text-sm text-gray-600">
                <input
                    type="checkbox"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                    :checked="isAllSelected"
                    @change="toggleAll"
                />
                <span class="ml-2">Select all</span>
            </label>
        </div>
        <div class="grid gap-2 sm:grid-cols-2">
            <label
                v-for="permission in permissions"
                :key="permission.id"
                class="inline-flex items-center text-sm text-gray-700"
            >
                <input
                    type="checkbox"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                    :value="permission.id"
                    :checked="selectedIds.includes(permission.id)"
                    @change="() => toggle(permission.id)"
                />
                <span class="ml-2 capitalize">{{ formatName(permission.name) }}</span>
            </label>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    title: { type: String, required: true },
    permissions: { type: Array, required: true },
    selectedIds: { type: Array, required: true },
});

const emit = defineEmits(['toggle', 'toggle-all']);

const toggle = (id) => emit('toggle', id);
const toggleAll = () => emit('toggle-all');

const isAllSelected = computed(() => {
    if (!props.permissions.length) return false;
    return props.permissions.every((p) => props.selectedIds.includes(p.id));
});

const formatName = (name) => {
    const parts = name.split('.');
    const action = parts[parts.length - 1];
    if (action === '*') return 'All';
    return action.replace('-', ' ');
};
</script>
