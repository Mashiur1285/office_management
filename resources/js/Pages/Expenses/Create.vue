<template>
    <Head title="Create Expense" />
    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <p class="text-[12px] font-bold uppercase tracking-wider text-[#1e5b43] mb-1">Accounting</p>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">
                    {{ isEdit ? "Edit expense" : "Add expense" }}
                </h1>
                <p class="text-sm text-gray-500">
                    {{ isEdit ? "Update expense details." : "Record a new expense." }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <Link
                    href="/expenses"
                    class="border border-gray-200 text-gray-700 bg-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-gray-50 transition shadow-sm flex items-center justify-center"
                >
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to list
                </Link>
                <button
                    type="button"
                    class="bg-[#1e5b43] text-white px-6 py-2.5 rounded-full text-sm font-semibold hover:bg-[#164230] transition shadow-sm border border-transparent disabled:opacity-60 disabled:cursor-not-allowed"
                    :disabled="form.processing"
                    @click="submit"
                >
                    <span v-if="form.processing">
                         <i class="fa-solid fa-spinner fa-spin mr-2"></i> {{ isEdit ? "Updating..." : "Saving..." }}
                    </span>
                    <span v-else>
                         <i class="fa-solid fa-check mr-2"></i> {{ isEdit ? "Update Expense" : "Save Expense" }}
                    </span>
                </button>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6 max-w-4xl">
            <section class="bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-gray-100 overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-100 flex flex-col justify-center bg-gray-50/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-[20px] font-bold text-gray-900 tracking-tight">Expense Details</h2>
                            <p class="text-[12px] uppercase tracking-wider font-bold text-gray-500 mt-1">Title, amount, and optional metadata.</p>
                        </div>
                        <p class="text-xs font-bold text-[#1e5b43] bg-emerald-50 px-3 py-1.5 rounded-full border border-emerald-100">Fields with * are required.</p>
                    </div>
                </div>
                <div class="grid gap-6 p-6 md:p-8 md:grid-cols-2">
                    <FormGroup label="Title *" :error="form.errors.title">
                        <input v-model="form.title" :class="inputClass('title')" required placeholder="Ex: Visa processing fee" />
                    </FormGroup>
                    <FormGroup label="Amount *" :error="form.errors.amount">
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 font-bold text-gray-500">৳</span>
                            <input v-model="form.amount" type="number" step="0.01" :class="[inputClass('amount'), 'pl-8']" required />
                        </div>
                    </FormGroup>
                    <FormGroup label="Category" :error="form.errors.category">
                        <input v-model="form.category" :class="inputClass('category')" placeholder="Ex: Operations" />
                    </FormGroup>
                    <FormGroup label="Paid On" :error="form.errors.paid_on">
                        <VueDatePicker
                            v-model="form.paid_on"
                            :enable-time-picker="false"
                            model-type="yyyy-MM-dd"
                            input-class-name="dp-custom-input"
                        />
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

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <Link
                    href="/expenses"
                    class="border border-gray-200 text-gray-700 bg-white px-6 py-2.5 rounded-full text-sm font-bold tracking-tight hover:bg-gray-50 transition shadow-sm"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    class="bg-[#1e5b43] text-white px-8 py-2.5 rounded-full text-sm font-bold tracking-tight hover:bg-[#164230] transition shadow-sm border border-transparent disabled:opacity-60 disabled:cursor-not-allowed"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">
                         <i class="fa-solid fa-spinner fa-spin mr-2"></i> {{ isEdit ? "Updating..." : "Saving..." }}
                    </span>
                    <span v-else>
                         <i class="fa-solid fa-check mr-2"></i> {{ isEdit ? "Update Expense" : "Save Expense" }}
                    </span>
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { computed, defineComponent, h } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

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
    "w-full rounded-full border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-900 focus:border-transparent focus:ring-2 focus:ring-[#1e5b43] bg-gray-50 focus:bg-white transition-all";
const baseFile =
    "w-full rounded-full border border-gray-200 px-5 py-2 text-sm font-medium text-gray-900 bg-gray-50 file:mr-4 file:rounded-full file:border-0 file:bg-[#1e5b43] file:text-white file:px-4 file:py-1 file:text-xs file:font-bold hover:file:bg-[#164230] file:transition-colors focus:border-transparent focus:ring-2 focus:ring-[#1e5b43] transition-all file:cursor-pointer";
const baseTextarea =
    "w-full rounded-2xl border border-gray-200 px-5 py-3 text-sm font-medium text-gray-900 focus:border-transparent focus:ring-2 focus:ring-[#1e5b43] bg-gray-50 focus:bg-white transition-all";

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
                { class: "space-y-1.5", role: "group" },
                [
                    h("label", { class: "text-[13px] font-bold text-gray-700 tracking-wide ml-1 uppercase" }, props.label),
                    slots.default ? slots.default() : null,
                    props.hint ? h("p", { class: "text-[11px] font-bold text-gray-400 uppercase tracking-wider ml-1" }, props.hint) : null,
                    props.error ? h("p", { class: "text-xs font-bold text-red-500 ml-1" }, props.error) : null,
                ].filter(Boolean)
            );
    },
});
</script>

<style>
/* Dashboard styles for vue-datepicker */
.dp-custom-input {
    width: 100%;
    border-radius: 9999px !important; /* rounded-full */
    border: 1px solid #e5e7eb !important; /* border-gray-200 */
    padding: 0.625rem 1.25rem 0.625rem 2.5rem !important; /* py-2.5 px-5 pl-10 */
    font-size: 0.875rem !important; /* text-sm */
    font-weight: 500 !important; /* font-medium */
    color: #111827 !important; /* text-gray-900 */
    background-color: #f9fafb !important; /* bg-gray-50 */
    transition: all 0.2s !important;
}

.dp-custom-input:focus {
    background-color: #ffffff !important;
    border-color: transparent !important;
    outline: none !important;
    box-shadow: 0 0 0 2px #1e5b43 !important; /* focus:ring-[#1e5b43] */
}
</style>
