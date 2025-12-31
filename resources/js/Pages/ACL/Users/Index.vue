<template>
    <Head title="Users" />
    <Table
        title="Users"
        :columns="columns"
        :data="users?.data || []"
        searchPlaceHolder="Search by name or email"
        :searchValue="state.search"
        :isSearching="state.isSearching"
        :totalPages="users?.last_page"
        :currentPage="state.currentPage"
        :showSearch="true"
        @search="onSearch"
        @pageChanged="onPageChange"
        @updatePerPage="onUpdatePerPage"
    >
        <template #action>
            <BaseButton
                label="Add User"
                color="default"
                prefix-icon="fa-solid fa-plus"
                :pill="true"
                @onClick="addUser"
                v-if="canCreate"
            />
        </template>
    </Table>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { reactive, computed, onMounted } from 'vue';
import Table from '@/Components/Tables/BaseTable.vue';
import BaseButton from '@/Components/Buttons/BaseButton.vue';
import UserActions from '@/Pages/ACL/Users/Partials/UserActions.vue';

const props = defineProps({
    users: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const state = reactive({
    search: props.filters?.name || '',
    isSearching: false,
    currentPage: Number(new URL(window.location.href).searchParams.get('page') ?? 1),
});

const columns = [
    { label: 'Name', key: 'name' },
    { label: 'Email', key: 'email' },
    { label: 'Role', key: 'role' },
    { label: 'Actions', component: UserActions, headerClass: 'w-16' },
];

const permissions = computed(() => page.props.userPermissions || []);
const hasPermission = (perm) =>
    permissions.value.includes('user.*') || permissions.value.includes(perm);
const canCreate = computed(() => hasPermission('user.add'));

const addUser = () => router.visit(route('users.create'));

const onSearch = (value) => {
    state.search = value;
    state.isSearching = true;
    router.visit(route('users.index'), { data: { name: value, page: 1 }, preserveState: true, replace: true, onFinish: () => (state.isSearching = false) });
};

const onPageChange = (pageNumber) => {
    state.currentPage = pageNumber;
    router.visit(route('users.index'), { data: { page: pageNumber, name: state.search }, preserveState: true, replace: true });
};

const onUpdatePerPage = (perPage) => {
    state.currentPage = 1;
    router.visit(route('users.index'), { data: { per_page: perPage, page: 1, name: state.search }, preserveState: true, replace: true });
};

onMounted(() => {
    state.currentPage = Number(new URL(window.location.href).searchParams.get('page') ?? 1);
    state.search = String(new URL(window.location.href).searchParams.get('name') || state.search || '');
});
</script>
