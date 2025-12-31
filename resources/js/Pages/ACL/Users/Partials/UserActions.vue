<template>
    <div class="flex gap-2 justify-end">
        <IconButton
            v-if="canEdit"
            @click="editUser"
            icon="fa-solid fa-pen-to-square"
            extra-class="bg-blue-600 text-white"
        />
    </div>
</template>

<script setup>
import IconButton from '@/Components/Buttons/IconButton.vue';
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    data: { type: Object, required: true },
});

const page = usePage();
const permissions = computed(() => page.props.userPermissions || []);

const hasPermission = (perm) =>
    permissions.value.includes('user.*') || permissions.value.includes(perm);

const canEdit = computed(() => hasPermission('user.update'));

const editUser = () => router.visit(route('users.edit', props.data.id));
</script>
