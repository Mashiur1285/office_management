<template>
    <Head title="Create Foreign Company" />
    <div class="py-8 space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-500">Foreign Companies</p>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ isEdit ? "Edit overseas company" : "Add an overseas company" }}
                </h1>
                <p class="text-sm text-gray-600">
                    {{ isEdit ? "Update partner abroad details." : "Partners abroad receiving your clients." }}
                </p>
            </div>
            <div class="flex gap-3">
                <Link
                    href="/foreign-companies"
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
                              ? "Update Company"
                              : "Save Company"
                    }}
                </button>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <section class="rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Company Details</h2>
                        <p class="text-sm text-gray-600">Name, country, and contacts.</p>
                    </div>
                    <p class="text-xs text-gray-500">Fields with * are required.</p>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-2">
                    <FormGroup label="Company Name *" :error="form.errors.name">
                        <input v-model="form.name" :class="inputClass('name')" placeholder="Ex: XYZ Intl" required />
                    </FormGroup>
                    <FormGroup label="Country" :error="form.errors.country">
                        <input v-model="form.country" :class="inputClass('country')" placeholder="Ex: UAE, Saudi Arabia" />
                    </FormGroup>
                    <FormGroup label="Job Categories" :error="form.errors.job_categories">
                        <input v-model="form.job_categories" :class="inputClass('job_categories')" placeholder="Ex: Construction, Agriculture" />
                    </FormGroup>
                    <FormGroup label="Owner Name" :error="form.errors.owner_name">
                        <input v-model="form.owner_name" :class="inputClass('owner_name')" />
                    </FormGroup>
                    <FormGroup label="Owner Phone" :error="form.errors.owner_phone">
                        <input v-model="form.owner_phone" :class="inputClass('owner_phone')" />
                    </FormGroup>
                    <FormGroup label="Contact Person Name" :error="form.errors.contact_person_name">
                        <input v-model="form.contact_person_name" :class="inputClass('contact_person_name')" />
                    </FormGroup>
                    <FormGroup label="Contact Person Phone" :error="form.errors.contact_person_phone">
                        <input v-model="form.contact_person_phone" :class="inputClass('contact_person_phone')" />
                    </FormGroup>
                    <FormGroup label="Office Address" :error="form.errors.office_address" class="md:col-span-2">
                        <textarea v-model="form.office_address" rows="2" :class="textareaClass" placeholder="Street, City, Country"></textarea>
                    </FormGroup>
                    <FormGroup label="Per Client Fee" :error="form.errors.per_client_fee">
                        <input v-model="form.per_client_fee" type="number" step="0.01" :class="inputClass('per_client_fee')" />
                    </FormGroup>
                </div>
            </section>

            <div class="flex items-center justify-end gap-3">
                <Link
                    href="/foreign-companies"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-60"
                    :disabled="form.processing"
                >
                    {{
                        form.processing
                            ? isEdit
                                ? "Updating..."
                                : "Saving..."
                            : isEdit
                              ? "Update Company"
                              : "Save Company"
                    }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { computed, defineComponent, h } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    company: {
        type: Object,
        default: null,
    },
    mode: {
        type: String,
        default: "create",
    },
});

const isEdit = computed(() => !!props.company);

const buildFormState = () => ({
    name: props.company?.name ?? "",
    country: props.company?.country ?? "",
    job_categories: props.company?.job_categories ?? "",
    owner_name: props.company?.owner_name ?? "",
    owner_phone: props.company?.owner_phone ?? "",
    office_address: props.company?.office_address ?? "",
    contact_person_name: props.company?.contact_person_name ?? "",
    contact_person_phone: props.company?.contact_person_phone ?? "",
    per_client_fee: props.company?.per_client_fee ?? "",
});

const form = useForm(buildFormState());

const baseInput =
    "w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 bg-white";
const baseTextarea =
    "w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 bg-white";

const inputClass = (field) =>
    [
        baseInput,
        form.errors[field] ? "border-red-400 focus:border-red-500 focus:ring-red-500" : "",
    ]
        .filter(Boolean)
        .join(" ");

const textareaClass = baseTextarea;

const submit = () => {
    const url =
        isEdit.value && props.company
            ? `/foreign-companies/${props.company.id}`
            : "/foreign-companies";

    if (isEdit.value) {
        form.put(url, {
            onSuccess: () => {},
        });
    } else {
        form.post(url, {
            onSuccess: () => form.reset(),
        });
    }
};

const FormGroup = defineComponent({
    name: "FormGroup",
    props: {
        label: String,
        error: String,
        hint: String,
    },
    setup(props, { slots }) {
        return () =>
            h(
                "div",
                { class: "space-y-2", role: "group" },
                [
                    h("label", { class: "text-sm font-medium text-gray-700" }, props.label),
                    slots.default ? slots.default() : null,
                    props.hint ? h("p", { class: "text-xs text-gray-500" }, props.hint) : null,
                    props.error ? h("p", { class: "text-xs text-red-600" }, props.error) : null,
                ].filter(Boolean)
            );
    },
});
</script>
