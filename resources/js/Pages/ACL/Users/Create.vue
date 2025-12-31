<template>
    <Head title="Add User" />
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Add User</h1>
            <p class="text-sm text-gray-600">Create a new user and assign a role.</p>
        </div>
        <Link
            :href="route('users.index')"
            class="text-sm text-blue-600 hover:text-blue-800 font-medium"
        >
            Back to Users
        </Link>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <form @submit.prevent="submit" class="space-y-4 max-w-2xl">
            <div>
                <label class="block text-sm font-medium text-gray-700">Name*</label>
                <input
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Enter full name"
                    required
                />
                <InputError :message="form.errors.name" class="mt-1" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email*</label>
                <input
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="user@example.com"
                    required
                />
                <InputError :message="form.errors.email" class="mt-1" />
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password*</label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required
                    />
                    <InputError :message="form.errors.password" class="mt-1" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Confirm Password*</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required
                    />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Role</label>
                <select
                    v-model="form.role_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">Select a role</option>
                    <option v-for="role in roles" :key="role.id" :value="role.id">
                        {{ role.name }}
                    </option>
                </select>
                <InputError :message="form.errors.role_id" class="mt-1" />
            </div>

            <div class="pt-4">
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Save User
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    roles: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: '',
});

const submit = () => {
    form.post(route('users.store'));
};
</script>
