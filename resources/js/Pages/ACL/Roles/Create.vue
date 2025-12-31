<template>
    <Head title="Create Role" />
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Create Role</h1>
            <p class="text-sm text-gray-600">Define a role and choose its permissions.</p>
        </div>
        <Link
            :href="route('roles.index')"
            class="text-sm text-blue-600 hover:text-blue-800 font-medium"
        >
            Back to Roles
        </Link>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Role name*</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Ex: Admin"
                    />
                    <InputError :message="form.errors.name" class="mt-1" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Short description"
                    />
                    <InputError :message="form.errors.description" class="mt-1" />
                </div>

                <button
                    type="button"
                    class="inline-flex w-full justify-center items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    @click="submit"
                >
                    Create Role
                </button>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-semibold text-gray-800">Permissions</h2>
                <label class="inline-flex items-center text-sm text-gray-700">
                    <input
                        type="checkbox"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                        :checked="allSelected"
                        @change="toggleAll"
                    />
                    <span class="ml-2">Select all</span>
                </label>
            </div>
            <div class="space-y-3">
                <PermissionMatrix
                    v-for="group in groupedPermissions"
                    :key="group.key"
                    :title="group.label"
                    :section-label="group.label"
                    :permissions="group.permissions"
                    :selected-ids="form.permission_ids"
                    @toggle="togglePermission"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import PermissionMatrix from '@/Components/ACL/PermissionMatrix.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    permissions: { type: Array, required: true },
});

const form = useForm({
    name: '',
    description: '',
    permission_ids: [],
});

const groupedPermissions = computed(() => {
    const groups = {};
    // Filter out role, user, and permission permissions - these are only for superadmin
    const filteredPermissions = props.permissions.filter((perm) => {
        const [module] = perm.name.split('.');
        return module !== 'role' && module !== 'user' && module !== 'permission';
    });

    filteredPermissions.forEach((perm) => {
        const [module] = perm.name.split('.');
        if (!groups[module]) {
            groups[module] = { key: module, label: formatLabel(module), permissions: [] };
        }
        groups[module].permissions.push(perm);
    });
    return Object.values(groups);
});

const formatLabel = (key) => key.replace('-', ' ').replace('_', ' ').toUpperCase();

const togglePermission = (id) => {
    const exists = form.permission_ids.includes(id);
    form.permission_ids = exists
        ? form.permission_ids.filter((pid) => pid !== id)
        : [...form.permission_ids, id];
};

const toggleGroup = (perms) => {
    const ids = perms.map((p) => p.id);
    const hasAll = ids.every((id) => form.permission_ids.includes(id));
    if (hasAll) {
        form.permission_ids = form.permission_ids.filter((id) => !ids.includes(id));
    } else {
        const merged = new Set([...form.permission_ids, ...ids]);
        form.permission_ids = Array.from(merged);
    }
};

const allSelected = computed(() => {
    // Only consider non-restricted permissions (exclude role, user, and permission permissions)
    const availablePermissions = props.permissions.filter((perm) => {
        const [module] = perm.name.split('.');
        return module !== 'role' && module !== 'user' && module !== 'permission';
    });
    if (!availablePermissions.length) return false;
    return availablePermissions.every((p) => form.permission_ids.includes(p.id));
});

const toggleAll = () => {
    // Only toggle non-restricted permissions
    const availablePermissions = props.permissions.filter((perm) => {
        const [module] = perm.name.split('.');
        return module !== 'role' && module !== 'user' && module !== 'permission';
    });
    form.permission_ids = allSelected.value
        ? []
        : availablePermissions.map((p) => p.id);
};

const submit = () => {
    form.post(route('roles.store'));
};
</script>
