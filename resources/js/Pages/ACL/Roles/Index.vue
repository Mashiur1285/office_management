<template>
    <Head title="Roles" />
    <Table
        title="Roles"
        :columns="columns"
        :data="roles?.data || []"
        searchPlaceHolder="Search by name"
        :searchValue="state.search"
        :isSearching="state.isSearching"
        :totalPages="roles?.last_page"
        :currentPage="state.currentPage"
        :showSearch="true"
        @search="onSearch"
        @pageChanged="onPageChange"
        @updatePerPage="onUpdatePerPage"
    >
        <template #action>
            <BaseButton
                label="Add Role"
                color="default"
                prefix-icon="fa-solid fa-plus"
                :pill="true"
                @onClick="router.visit(route('roles.create'))"
                v-if="canCreate"
            />
        </template>
    </Table>
</template>

<script setup>
import { reactive, onMounted, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import Table from '@/Components/Tables/BaseTable.vue';
import BaseButton from '@/Components/Buttons/BaseButton.vue';
import RoleStatus from '@/Pages/ACL/Roles/Partials/RoleStatus.vue';
import RoleActions from '@/Pages/ACL/Roles/Partials/RoleActions.vue';
import RolePermissionsColumn from '@/Pages/ACL/Roles/Partials/RolePermissionsColumn.vue';

const props = defineProps({
    roles: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();

const state = reactive({
    search: props.filters?.name || '',
    isSearching: false,
    currentPage: Number(new URL(window.location.href).searchParams.get('page') || 1),
});

const columns = [
    { label: 'Name', key: 'name' },
    { label: 'Description', component: RolePermissionsColumn, headerClass: 'w-[650px]' },
    { label: 'Status', component: RoleStatus, headerClass: 'w-12' },
    { label: 'Actions', component: RoleActions, headerClass: 'w-16' },
];

const permissions = computed(() => page.props.userPermissions || []);
const hasPermission = (permission) =>
    permissions.value?.includes('role.*') || permissions.value?.includes(permission);

const canCreate = computed(() => hasPermission('role.add'));

const onSearch = (value) => {
    state.search = value;
    state.isSearching = true;
    router.visit(route('roles.index'), { data: { name: value, page: 1 } , preserveState: true, replace: true, onFinish: () => (state.isSearching = false)});
};

const onPageChange = (pageNumber) => {
    state.currentPage = pageNumber;
    router.visit(route('roles.index'), { data: { page: pageNumber, name: state.search }, preserveState: true, replace: true });
};

const onUpdatePerPage = (perPage) => {
    state.currentPage = 1;
    router.visit(route('roles.index'), { data: { per_page: perPage, page: 1, name: state.search }, preserveState: true, replace: true });
};

onMounted(() => {
    state.currentPage = Number(new URL(window.location.href).searchParams.get('page') ?? 1);
    state.search = String(new URL(window.location.href).searchParams.get('name') || state.search || '');
});
</script>
