<template>
    <div class="py-8 space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-500">Agents</p>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ isEdit ? "Edit agent" : "Add an agent" }}
                </h1>
                <p class="text-sm text-gray-600">
                    {{ isEdit ? "Update agent details and services offered." : "Capture agent details and services offered." }}
                </p>
            </div>
            <div class="flex gap-3">
                <Link
                    href="/agents"
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
                              ? "Update Agent"
                              : "Save Agent"
                    }}
                </button>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <section class="rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>
                        <p class="text-sm text-gray-600">Name, contact, and district.</p>
                    </div>
                    <p class="text-xs text-gray-500">Fields with * are required.</p>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-2">
                    <FormGroup label="Agent Name *" :error="form.errors.name">
                        <input v-model="form.name" :class="inputClass('name')" placeholder="Ex: Rahim Uddin" required />
                    </FormGroup>
                    <FormGroup label="Mobile Number" :error="form.errors.mobile">
                        <input v-model="form.mobile" :class="inputClass('mobile')" placeholder="Ex: 01XXXXXXXXX" />
                    </FormGroup>
                    <FormGroup label="District *" :error="form.errors.district">
                        <input v-model="form.district" :class="inputClass('district')" required />
                    </FormGroup>
                    <FormGroup label="Address" :error="form.errors.address">
                        <input v-model="form.address" :class="inputClass('address')" />
                    </FormGroup>
                </div>
            </section>

            <section class="rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Documents & Banking</h2>
                    <p class="text-sm text-gray-600">NID upload and bank details.</p>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-2">
                    <FormGroup label="NID Number" :error="form.errors.nid_number">
                        <input v-model="form.nid_number" :class="inputClass('nid_number')" />
                    </FormGroup>
                    <FormGroup label="NID Upload" :error="form.errors.nid_file" hint="PDF or image">
                        <input type="file" :class="fileClass" accept=".pdf,image/*" @change="(e) => handleFile(e, 'nid_file')" />
                    </FormGroup>
                    <FormGroup label="Bank Account Details" :error="form.errors.bank_details" class="md:col-span-2">
                        <textarea v-model="form.bank_details" rows="2" :class="textareaClass" placeholder="Bank name, branch, account no."></textarea>
                    </FormGroup>
                </div>
            </section>

            <section class="rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Services</h2>
                    <p class="text-sm text-gray-600">Select services this agent handles.</p>
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap gap-2">
                        <label
                            v-for="service in services"
                            :key="service"
                            class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 cursor-pointer hover:border-blue-300"
                        >
                            <input
                                type="checkbox"
                                :value="service"
                                v-model="form.services"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            {{ service }}
                        </label>
                    </div>
                    <p v-if="form.errors.services" class="mt-2 text-xs text-red-600">{{ form.errors.services }}</p>
                </div>
            </section>

            <div class="flex items-center justify-end gap-3">
                <Link
                    href="/agents"
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
                              ? "Update Agent"
                              : "Save Agent"
                    }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { computed, defineComponent, h } from "vue";
import { Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    services: {
        type: Array,
        default: () => [],
    },
    agent: {
        type: Object,
        default: null,
    },
    mode: {
        type: String,
        default: "create",
    },
});

const isEdit = computed(() => !!props.agent);

const buildFormState = () => ({
    name: props.agent?.name ?? "",
    nid_number: props.agent?.nid_number ?? "",
    nid_file: null,
    district: props.agent?.district ?? "",
    bank_details: props.agent?.bank_details ?? "",
    mobile: props.agent?.mobile ?? "",
    address: props.agent?.address ?? "",
    services: props.agent?.services ?? [],
});

const form = useForm(buildFormState());

const baseInput =
    "w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 bg-white";
const baseFile =
    "w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 bg-white file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500";
const baseTextarea =
    "w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 bg-white";

const inputClass = (field) =>
    [
        baseInput,
        form.errors[field] ? "border-red-400 focus:border-red-500 focus:ring-red-500" : "",
    ]
        .filter(Boolean)
        .join(" ");

const fileClass = baseFile;
const textareaClass = baseTextarea;

const handleFile = (event, field) => {
    const files = event.target?.files;
    form[field] = files && files.length ? files[0] : null;
};

const submit = () => {
    const url = isEdit.value && props.agent ? `/agents/${props.agent.id}` : "/agents";

    if (isEdit.value) {
        form.put(url, {
            forceFormData: true,
        });
    } else {
        form.post(url, {
            forceFormData: true,
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
