<template>
    <Head title="Create Office Staff" />
    <div class="py-8 space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-500">Office Staff</p>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ isEdit ? "Edit Staff Member" : "Add Staff Member" }}
                </h1>
                <p class="text-sm text-gray-600">
                    {{
                        isEdit
                            ? "Update agency office staff who handle document processing."
                            : "Add agency office staff who handle document processing."
                    }}
                </p>
            </div>
            <div class="flex gap-3">
                <Link
                    href="/office-staff"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    ← Back to list
                </Link>
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-60"
                    :disabled="form.processing"
                    @click="submit"
                >
                    {{
                        form.processing
                            ? isEdit
                                ? "Updating..."
                                : "Saving..."
                            : isEdit
                              ? "Update Staff Member"
                              : "Save Staff Member"
                    }}
                </button>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <section class="rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>
                        <p class="text-sm text-gray-600">Name, designation, and contact details.</p>
                    </div>
                    <p class="text-xs text-gray-500">Fields with * are required.</p>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Staff Name *</label>
                        <input v-model="form.name" class="input" :class="{ 'border-red-500': form.errors.name }" placeholder="Ex: John Doe" required />
                        <p v-if="form.errors.name" class="text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Designation</label>
                        <input v-model="form.designation" class="input" :class="{ 'border-red-500': form.errors.designation }" placeholder="Ex: Document Officer" />
                        <p v-if="form.errors.designation" class="text-xs text-red-600">{{ form.errors.designation }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Email</label>
                        <input v-model="form.email" type="email" class="input" :class="{ 'border-red-500': form.errors.email }" placeholder="staff@example.com" />
                        <p v-if="form.errors.email" class="text-xs text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Mobile Number</label>
                        <input v-model="form.mobile" class="input" :class="{ 'border-red-500': form.errors.mobile }" placeholder="01XXXXXXXXX" />
                        <p v-if="form.errors.mobile" class="text-xs text-red-600">{{ form.errors.mobile }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">NID Number</label>
                        <input v-model="form.nid_number" class="input" :class="{ 'border-red-500': form.errors.nid_number }" />
                        <p v-if="form.errors.nid_number" class="text-xs text-red-600">{{ form.errors.nid_number }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Joining Date</label>
                        <input v-model="form.joining_date" type="date" class="input" :class="{ 'border-red-500': form.errors.joining_date }" />
                        <p v-if="form.errors.joining_date" class="text-xs text-red-600">{{ form.errors.joining_date }}</p>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium text-gray-700">Address</label>
                        <textarea v-model="form.address" rows="2" class="input" :class="{ 'border-red-500': form.errors.address }"></textarea>
                        <p v-if="form.errors.address" class="text-xs text-red-600">{{ form.errors.address }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Status *</label>
                        <select v-model="form.status" class="input" :class="{ 'border-red-500': form.errors.status }" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <p v-if="form.errors.status" class="text-xs text-red-600">{{ form.errors.status }}</p>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium text-gray-700">Notes</label>
                        <textarea v-model="form.notes" rows="3" class="input" :class="{ 'border-red-500': form.errors.notes }" placeholder="Any additional notes..."></textarea>
                        <p v-if="form.errors.notes" class="text-xs text-red-600">{{ form.errors.notes }}</p>
                    </div>
                </div>
            </section>

            <div class="flex items-center justify-end gap-3">
                <Link
                    href="/office-staff"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-60"
                    :disabled="form.processing"
                >
                    {{
                        form.processing
                            ? isEdit
                                ? "Updating..."
                                : "Saving..."
                            : isEdit
                              ? "Update Staff Member"
                              : "Save Staff Member"
                    }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    staffMember: {
        type: Object,
        default: null,
    },
    mode: {
        type: String,
        default: "create",
    },
});

const isEdit = computed(() => !!props.staffMember);

const buildFormState = () => ({
    name: props.staffMember?.name ?? "",
    designation: props.staffMember?.designation ?? "",
    email: props.staffMember?.email ?? "",
    mobile: props.staffMember?.mobile ?? "",
    nid_number: props.staffMember?.nid_number ?? "",
    address: props.staffMember?.address ?? "",
    joining_date: props.staffMember?.joining_date ?? "",
    status: props.staffMember?.status ?? "active",
    notes: props.staffMember?.notes ?? "",
});

const form = useForm(buildFormState());

const submit = () => {
    const url =
        isEdit.value && props.staffMember
            ? `/office-staff/${props.staffMember.id}`
            : "/office-staff";

    if (isEdit.value) {
        form.put(url);
    } else {
        form.post(url);
    }
};
</script>

<style scoped>
.input {
    width: 100%;
    border-radius: 0.5rem;
    border: 1px solid rgb(229, 231, 235);
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    color: rgb(17, 24, 39);
    background-color: #ffffff;
}

.input:focus {
    outline: none;
    border-color: rgb(59, 130, 246);
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}
</style>
