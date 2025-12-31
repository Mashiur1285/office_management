<template>
    <div class="border border-gray-200 rounded-lg mb-4 bg-white">
        <div class="px-4 py-3 font-semibold text-gray-800 text-lg border-b">
            {{ title }}
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left w-40">Section</th>
                        <th
                            v-for="action in orderedActions"
                            :key="action"
                            class="px-4 py-3 text-center"
                        >
                            {{ formatAction(action) }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="px-4 py-3 text-gray-800 capitalize">{{ sectionLabel }}</td>
                        <td
                            v-for="action in orderedActions"
                            :key="action"
                            class="px-4 py-3 text-center"
                        >
                            <input
                                type="checkbox"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                :checked="selectedIds.includes(map[action])"
                                @change="toggle(map[action], action)"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    sectionLabel: { type: String, required: true },
    permissions: { type: Array, required: true },
    selectedIds: { type: Array, required: true },
});

const emit = defineEmits(['toggle']);

const map = computed(() => {
    const result = {};
    props.permissions.forEach((perm) => {
        const action = perm.name.split('.').pop();
        result[action] = perm.id;
    });
    return result;
});

const preferredOrder = ['*', 'view', 'add', 'update', 'delete'];
const orderedActions = computed(() => {
    const actions = props.permissions.map((p) => p.name.split('.').pop());
    const unique = Array.from(new Set(actions));
    return [
        ...preferredOrder.filter((a) => unique.includes(a)),
        ...unique.filter((a) => !preferredOrder.includes(a)),
    ];
});

const formatAction = (action) => {
    if (action === '*') return 'All';
    return action.charAt(0).toUpperCase() + action.slice(1).replace('-', ' ');
};

const toggle = (id, action) => {
    // If toggling "All" (*), toggle all other permissions in this section
    if (action === '*') {
        const allPermissionIds = props.permissions.map(p => p.id);
        const isAllCurrentlySelected = props.selectedIds.includes(id);

        // If All is currently selected, unselect all
        // If All is not selected, select all
        allPermissionIds.forEach(permId => {
            const isSelected = props.selectedIds.includes(permId);
            // Toggle all permissions to match the new state of "All"
            if (isAllCurrentlySelected && isSelected) {
                emit('toggle', permId); // Unselect
            } else if (!isAllCurrentlySelected && !isSelected) {
                emit('toggle', permId); // Select
            }
        });
    } else {
        // If toggling individual permission
        const allPermissionId = map.value['*'];
        const isAllSelected = allPermissionId && props.selectedIds.includes(allPermissionId);

        emit('toggle', id);

        // If All was selected, unselect it
        if (isAllSelected) {
            emit('toggle', allPermissionId);
        } else if (allPermissionId) {
            // Check if all individual permissions (except "All") are now selected
            // If yes, auto-select "All"
            const individualPermissions = props.permissions.filter(p => {
                const [, permAction] = p.name.split('.');
                return permAction !== '*';
            });

            // Check if toggling this permission will make all individual permissions selected
            const willBeSelected = !props.selectedIds.includes(id);

            if (willBeSelected) {
                const allIndividualsSelected = individualPermissions.every(p => {
                    if (p.id === id) return true; // This one will be selected
                    return props.selectedIds.includes(p.id);
                });

                if (allIndividualsSelected && !props.selectedIds.includes(allPermissionId)) {
                    emit('toggle', allPermissionId); // Auto-select "All"
                }
            }
        }
    }
};
</script>
