<template>
    <div class="flex gap-2 justify-end">
        <IconButton
            v-if="canEdit"
            @click="editRole"
            icon="fa-solid fa-pen-to-square"
            extra-class="bg-blue-600 text-white"
            :disabled="isAdminRole"
        />

        <IconButton
            v-if="canDelete"
            icon="fa-solid fa-trash"
            extra-class="bg-red-500 text-white"
            :disabled="isAdminRole"
            @click="destroy"
        />
    </div>
</template>

<script setup>
import IconButton from '@/Components/Buttons/IconButton.vue';
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    data: { type: Object, required: true },
});

const page = usePage();
const permissions = computed(() => page.props.userPermissions || []);

const hasPermission = (perm) =>
    permissions.value.includes('role.*') || permissions.value.includes(perm);

const isAdminRole = computed(() => props.data?.name?.toLowerCase() === 'admin');

const canEdit = computed(() => hasPermission('role.update'));
const canDelete = computed(() => hasPermission('role.delete'));

const editRole = () => router.visit(route('roles.edit', props.data.id));
const destroy = () => {
    if (isAdminRole.value) return;
    if (!confirm('Delete this role?')) return;
    router.delete(route('roles.destroy', props.data.id), { preserveScroll: true });
};
</script>
