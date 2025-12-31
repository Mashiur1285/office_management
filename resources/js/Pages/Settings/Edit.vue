<template>
    <Head title="Settings" />
    <div class="py-8 space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-500">System</p>
                <h1 class="text-2xl font-bold text-gray-900">Application Settings</h1>
                <p class="text-sm text-gray-600">
                    Configure application name and logo for the header.
                </p>
            </div>
            <div class="flex gap-3">
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-60"
                    :disabled="form.processing"
                    @click="submit"
                >
                    {{ form.processing ? "Saving..." : "Save Settings" }}
                </button>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- General Settings Section -->
            <section class="rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">General Settings</h2>
                        <p class="text-sm text-gray-600">Application name and branding.</p>
                    </div>
                    <p class="text-xs text-gray-500">Fields with * are required.</p>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-2">
                    <FormGroup label="Application/Company Name *" :error="form.errors.app_name" class="md:col-span-2">
                        <input
                            v-model="form.app_name"
                            :class="inputClass('app_name')"
                            required
                            placeholder="Enter application or company name"
                        />
                        <p class="text-xs text-gray-500 mt-1">This will appear in the header</p>
                    </FormGroup>

                    <FormGroup label="Logo Image" :error="form.errors.logo" class="md:col-span-2">
                        <div class="space-y-3">
                            <!-- Current Logo Preview -->
                            <div v-if="settings.logo_url" class="flex items-center gap-4">
                                <img
                                    :src="settings.logo_url"
                                    alt="Current logo"
                                    class="h-16 w-auto border border-gray-200 rounded-lg p-2"
                                />
                                <span class="text-sm text-gray-600">Current logo</span>
                            </div>

                            <!-- File Upload -->
                            <input
                                type="file"
                                :class="fileClass"
                                accept="image/png,image/jpeg,image/jpg,image/svg+xml"
                                @change="(e) => handleFile(e, 'logo')"
                            />
                            <p class="text-xs text-gray-500">
                                PNG, JPG, JPEG, or SVG. Maximum 2MB. Used in header.
                            </p>
                        </div>
                    </FormGroup>
                </div>
            </section>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-60"
                    :disabled="form.processing"
                >
                    {{ form.processing ? "Saving..." : "Save Settings" }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { defineComponent, h } from "vue";
import { Head, useForm } from "@inertiajs/vue3";

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    app_name: props.settings.app_name || '',
    logo: null,
});

const baseInput =
    "w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 bg-white";
const baseFile =
    "w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 bg-white file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500";

const inputClass = (field) =>
    [
        baseInput,
        form.errors[field] ? "border-red-400 focus:border-red-500 focus:ring-red-500" : "",
    ]
        .filter(Boolean)
        .join(" ");

const fileClass = baseFile;

const handleFile = (event, field) => {
    const files = event.target?.files;
    form[field] = files && files.length ? files[0] : null;
};

const submit = () => {
    form.put(route('settings.update'), {
        forceFormData: true,
    });
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
