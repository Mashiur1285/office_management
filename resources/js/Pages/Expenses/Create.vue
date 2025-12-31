<template>
    <div class="py-8 space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-500">Accounting</p>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ isEdit ? "Edit expense" : "Add expense" }}
                </h1>
                <p class="text-sm text-gray-600">
                    {{ isEdit ? "Update expense details." : "Record a new expense." }}
                </p>
            </div>
            <div class="flex gap-3">
                <Link
                    href="/expenses"
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
                    {{ form.processing ? (isEdit ? "Updating..." : "Saving...") : isEdit ? "Update Expense" : "Save Expense" }}
                </button>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <section class="rounded-xl border border-gray-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Expense Details</h2>
                        <p class="text-sm text-gray-600">Title, amount, and optional metadata.</p>
                    </div>
                    <p class="text-xs text-gray-500">Fields with * are required.</p>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-2">
                    <FormGroup label="Title *" :error="form.errors.title">
                        <input v-model="form.title" :class="inputClass('title')" required placeholder="Ex: Visa processing fee" />
                    </FormGroup>
                    <FormGroup label="Amount *" :error="form.errors.amount">
                        <input v-model="form.amount" type="number" step="0.01" :class="inputClass('amount')" required />
                    </FormGroup>
                    <FormGroup label="Category" :error="form.errors.category">
                        <input v-model="form.category" :class="inputClass('category')" placeholder="Ex: Operations" />
                    </FormGroup>
                    <FormGroup label="Paid On" :error="form.errors.paid_on">
                        <input v-model="form.paid_on" type="date" :class="inputClass('paid_on')" />
                    </FormGroup>
                    <FormGroup label="Vendor" :error="form.errors.vendor">
                        <input v-model="form.vendor" :class="inputClass('vendor')" placeholder="Ex: ABC Travels" />
                    </FormGroup>
                    <FormGroup label="Attachment" :error="form.errors.attachment" hint="PDF or image, max 10MB">
                        <input
                            type="file"
                            :class="fileClass"
                            accept=".pdf,image/*"
                            @change="(e) => handleFile(e, 'attachment')"
                        />
                    </FormGroup>
                    <FormGroup label="Notes" :error="form.errors.notes" class="md:col-span-2">
                        <textarea v-model="form.notes" rows="3" :class="textareaClass" placeholder="Add context or remarks"></textarea>
                    </FormGroup>
                </div>
            </section>

            <div class="flex items-center justify-end gap-3">
                <Link
                    href="/expenses"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-60"
                    :disabled="form.processing"
                >
                    {{ form.processing ? (isEdit ? "Updating..." : "Saving...") : isEdit ? "Update Expense" : "Save Expense" }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { computed, defineComponent, h } from "vue";
import { Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    expense: {
        type: Object,
        default: null,
    },
    mode: {
        type: String,
        default: "create",
    },
});

const isEdit = computed(() => !!props.expense);

const buildFormState = () => ({
    title: props.expense?.title ?? "",
    amount: props.expense?.amount ?? "",
    category: props.expense?.category ?? "",
    paid_on: props.expense?.paid_on ?? "",
    vendor: props.expense?.vendor ?? "",
    notes: props.expense?.notes ?? "",
    attachment: null,
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
    const url = isEdit.value && props.expense ? `/expenses/${props.expense.id}` : "/expenses";

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
