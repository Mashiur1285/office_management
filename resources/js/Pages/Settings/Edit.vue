<template>
    <Head title="Settings" />
    <div class="py-8 space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-500">System</p>
                <h1 class="text-2xl font-bold text-gray-900">Application Settings</h1>
                <p class="text-sm text-gray-600">
                    Configure the application and the letterhead used on receipts &amp; reports.
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

        <!-- Tabs -->
        <div class="flex gap-1 border-b border-gray-200">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                @click="activeTab = tab.key"
                class="px-5 py-2.5 text-sm font-semibold transition -mb-px border-b-2"
                :class="activeTab === tab.key
                    ? 'border-blue-600 text-blue-700'
                    : 'border-transparent text-gray-500 hover:text-gray-800'"
            >
                <font-awesome-icon :icon="tab.icon" class="w-3.5 h-3.5 mr-1.5" />
                {{ tab.label }}
            </button>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- ===================== GENERAL TAB ===================== -->
            <section v-show="activeTab === 'general'" class="rounded-xl border border-gray-100 bg-white shadow-sm">
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
                        <p class="text-xs text-gray-500 mt-1">This appears in the header/sidebar and as the letterhead business name.</p>
                    </FormGroup>

                    <FormGroup label="Logo Image" :error="form.errors.logo" class="md:col-span-2">
                        <div class="space-y-3">
                            <div v-if="logoPreview" class="flex items-center gap-4">
                                <img
                                    :src="logoPreview"
                                    alt="Current logo"
                                    class="h-16 w-auto border border-gray-200 rounded-lg p-2"
                                />
                                <span class="text-sm text-gray-600">Current logo</span>
                            </div>
                            <input
                                type="file"
                                :class="fileClass"
                                accept="image/png,image/jpeg,image/jpg,image/svg+xml"
                                @change="(e) => handleFile(e, 'logo')"
                            />
                            <p class="text-xs text-gray-500">
                                PNG, JPG, JPEG, or SVG. Maximum 2MB. Used in the header and letterhead.
                            </p>
                        </div>
                    </FormGroup>
                </div>
            </section>

            <!-- ===================== INVOICE / LETTERHEAD TAB ===================== -->
            <section v-show="activeTab === 'invoice'" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Left: fields -->
                <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">Invoice Letterhead</h2>
                        <p class="text-sm text-gray-600">Shown on the top &amp; bottom of receipts, quotations, invoices and reports.</p>
                    </div>
                    <div class="space-y-4 p-6">
                        <label class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                            <span>
                                <span class="block text-sm font-semibold text-gray-800">Show letterhead on PDFs</span>
                                <span class="block text-xs text-gray-500">Turn off to print on pre-printed pads.</span>
                            </span>
                            <button
                                type="button"
                                @click="form.letterhead_enabled = !form.letterhead_enabled"
                                :class="['relative inline-flex h-6 w-11 items-center rounded-full transition-colors', form.letterhead_enabled ? 'bg-emerald-500' : 'bg-gray-200']"
                            >
                                <span :class="['inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform', form.letterhead_enabled ? 'translate-x-6' : 'translate-x-1']"></span>
                            </button>
                        </label>

                        <FormGroup label="Top Gap (inches)" :error="form.errors.letterhead_top_gap">
                            <div class="flex items-center gap-2">
                                <input v-model="form.letterhead_top_gap" type="number" step="0.1" min="0" max="10" :class="inputClass('letterhead_top_gap')" class="w-32" />
                                <span class="text-sm text-gray-500">inch from the top</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Blank space printed above the letterhead — set this to line up with a pre-printed pad.</p>
                        </FormGroup>

                        <FormGroup label="Header Image (optional)" :error="form.errors.header_image">
                            <div class="space-y-2">
                                <div v-if="headerImagePreview" class="flex items-center gap-3">
                                    <img :src="headerImagePreview" class="h-12 w-auto max-w-[220px] rounded border border-gray-200 object-contain p-1" />
                                    <button type="button" @click="removeHeaderImage" class="text-xs font-semibold text-red-600 hover:underline">Remove</button>
                                </div>
                                <input type="file" :class="fileClass" accept="image/png,image/jpeg,image/jpg,image/svg+xml" @change="(e) => handleFile(e, 'header_image')" />
                                <p class="text-xs text-gray-500">If set, this full-width banner replaces the built header (name + tagline). PNG/JPG/SVG, max 3MB.</p>
                            </div>
                        </FormGroup>

                        <FormGroup label="Header Tagline" :error="form.errors.company_tagline">
                            <input v-model="form.company_tagline" :class="inputClass('company_tagline')" placeholder="e.g. IATA Accredited | ATAB Member" />
                            <p class="text-xs text-gray-500 mt-1">Small line under the business name (ignored if a header image is set).</p>
                        </FormGroup>

                        <FormGroup label="Address" :error="form.errors.company_address">
                            <textarea v-model="form.company_address" rows="3" :class="inputClass('company_address')" placeholder="Full office address"></textarea>
                        </FormGroup>

                        <FormGroup label="Phone / Mobile" :error="form.errors.company_phone">
                            <input v-model="form.company_phone" :class="inputClass('company_phone')" placeholder="+88 01..." />
                        </FormGroup>

                        <FormGroup label="Email" :error="form.errors.company_email">
                            <input v-model="form.company_email" :class="inputClass('company_email')" placeholder="office@example.com" />
                        </FormGroup>

                        <FormGroup label="Footer Note (optional)" :error="form.errors.footer_note">
                            <input v-model="form.footer_note" :class="inputClass('footer_note')" placeholder="e.g. Thank you for your business." />
                        </FormGroup>

                        <div class="border-t border-gray-100 pt-4">
                            <label class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                                <span>
                                    <span class="block text-sm font-semibold text-gray-800">Watermark</span>
                                    <span class="block text-xs text-gray-500">Faint text behind the PDF content.</span>
                                </span>
                                <button
                                    type="button"
                                    @click="form.watermark_enabled = !form.watermark_enabled"
                                    :class="['relative inline-flex h-6 w-11 items-center rounded-full transition-colors', form.watermark_enabled ? 'bg-emerald-500' : 'bg-gray-200']"
                                >
                                    <span :class="['inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform', form.watermark_enabled ? 'translate-x-6' : 'translate-x-1']"></span>
                                </button>
                            </label>
                            <div v-if="form.watermark_enabled" class="mt-3 space-y-3">
                                <div class="flex items-center gap-6 text-sm">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" value="text" v-model="form.watermark_type" />
                                        Text
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" value="image" v-model="form.watermark_type" />
                                        Image
                                    </label>
                                </div>

                                <FormGroup v-if="form.watermark_type === 'text'" label="Watermark Text" :error="form.errors.watermark_text">
                                    <input v-model="form.watermark_text" :class="inputClass('watermark_text')" :placeholder="form.app_name || 'Business name'" />
                                    <p class="text-xs text-gray-500 mt-1">Leave empty to use the business name.</p>
                                </FormGroup>

                                <FormGroup v-else label="Watermark Image" :error="form.errors.watermark_image">
                                    <div class="space-y-2">
                                        <div v-if="watermarkImagePreview" class="flex items-center gap-3">
                                            <img :src="watermarkImagePreview" class="h-14 w-auto max-w-[160px] rounded border border-gray-200 object-contain p-1 opacity-70" />
                                            <button type="button" @click="removeWatermarkImage" class="text-xs font-semibold text-red-600 hover:underline">Remove</button>
                                        </div>
                                        <input type="file" :class="fileClass" accept="image/png,image/jpeg,image/jpg,image/svg+xml" @change="(e) => handleFile(e, 'watermark_image')" />
                                        <p class="text-xs text-gray-500">Shown faintly behind the PDF content. PNG/JPG/SVG, max 3MB.</p>
                                    </div>
                                </FormGroup>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: live preview -->
                <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">Live Preview</h2>
                        <p class="text-sm text-gray-600">Approximate look of the letterhead on a PDF.</p>
                    </div>
                    <div class="p-6">
                        <div class="relative mx-auto max-w-md rounded-lg border border-gray-200 bg-white p-6 shadow-inner overflow-hidden" style="min-height: 460px;">
                            <!-- Watermark -->
                            <div v-if="form.watermark_enabled" class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                <img v-if="form.watermark_type === 'image' && watermarkImagePreview" :src="watermarkImagePreview" class="max-h-40 max-w-[70%] -rotate-45 select-none opacity-10" />
                                <span v-else-if="form.watermark_type === 'text'" class="text-4xl font-black uppercase text-gray-900/5 -rotate-45 select-none">
                                    {{ form.watermark_text || form.app_name }}
                                </span>
                            </div>

                            <!-- Top gap -->
                            <div :style="{ height: Math.min(Number(form.letterhead_top_gap) || 0, 6) * 22 + 'px' }"></div>

                            <template v-if="form.letterhead_enabled">
                                <!-- Header -->
                                <img v-if="headerImagePreview" :src="headerImagePreview" class="w-full max-h-24 object-contain border-b-2 pb-2" style="border-color:#1a3a8f;" />
                                <div v-else class="flex items-center gap-3 border-b-2 pb-3" style="border-color:#1a3a8f;">
                                    <img v-if="logoPreview" :src="logoPreview" class="h-10 w-10 object-contain" />
                                    <div v-else class="h-10 w-10 rounded bg-blue-100"></div>
                                    <div>
                                        <div class="text-sm font-extrabold uppercase tracking-wide" style="color:#1a3a8f;">{{ form.app_name || 'Business Name' }}</div>
                                        <div class="text-[9px] uppercase tracking-wide text-gray-500">{{ form.company_tagline }}</div>
                                    </div>
                                </div>
                            </template>

                            <!-- Body placeholder -->
                            <div class="relative py-6">
                                <div class="mb-2 text-sm font-bold text-gray-800">Sample Report / Receipt</div>
                                <div class="space-y-1.5">
                                    <div class="h-2 w-full rounded bg-gray-100"></div>
                                    <div class="h-2 w-5/6 rounded bg-gray-100"></div>
                                    <div class="h-2 w-4/6 rounded bg-gray-100"></div>
                                    <div class="h-2 w-full rounded bg-gray-100"></div>
                                </div>
                            </div>

                            <template v-if="form.letterhead_enabled">
                                <!-- Footer -->
                                <div class="absolute inset-x-6 bottom-6">
                                    <div v-if="form.footer_note" class="mb-1 text-center text-[9px] italic text-gray-500">{{ form.footer_note }}</div>
                                    <div class="flex items-center gap-3 border-t-2 pt-2" style="border-color:#1a3a8f;">
                                        <img v-if="logoPreview" :src="logoPreview" class="h-8 w-8 object-contain" />
                                        <div class="text-[8px] leading-relaxed text-gray-600">
                                            <div class="text-[9px] font-extrabold uppercase" style="color:#1a3a8f;">{{ form.app_name || 'Business Name' }}</div>
                                            <div v-if="form.company_address"><strong>Address:</strong> {{ form.company_address }}</div>
                                            <div v-if="form.company_phone"><strong>Mobile:</strong> {{ form.company_phone }}</div>
                                            <div v-if="form.company_email"><strong>Email:</strong> {{ form.company_email }}</div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div v-if="!form.letterhead_enabled" class="absolute inset-x-6 bottom-6 text-center text-[10px] text-gray-400">
                                Letterhead is off — PDFs print without header/footer.
                            </div>
                        </div>
                    </div>
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
import { defineComponent, h, ref, computed } from "vue";
import { Head, useForm } from "@inertiajs/vue3";

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
});

const tabs = [
    { key: "general", label: "General", icon: "gear" },
    { key: "invoice", label: "Invoice", icon: "file-invoice" },
];
const activeTab = ref("general");

const form = useForm({
    app_name: props.settings.app_name || "",
    logo: null,
    company_address: props.settings.company_address || "",
    company_phone: props.settings.company_phone || "",
    company_email: props.settings.company_email || "",
    company_tagline: props.settings.company_tagline || "",
    footer_note: props.settings.footer_note || "",
    letterhead_enabled: props.settings.letterhead_enabled ?? true,
    letterhead_top_gap: props.settings.letterhead_top_gap ?? 0,
    header_image: null,
    remove_header_image: false,
    watermark_enabled: props.settings.watermark_enabled ?? false,
    watermark_text: props.settings.watermark_text || "",
    watermark_type: props.settings.watermark_type || "text",
    watermark_image: null,
    remove_watermark_image: false,
});

const localLogoUrl = ref(null);
const logoPreview = computed(() => localLogoUrl.value || props.settings.logo_url || null);

const localHeaderUrl = ref(null);
const headerImagePreview = computed(() =>
    form.remove_header_image ? null : localHeaderUrl.value || props.settings.header_image_url || null
);

const localWatermarkUrl = ref(null);
const watermarkImagePreview = computed(() =>
    form.remove_watermark_image ? null : localWatermarkUrl.value || props.settings.watermark_image_url || null
);

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
    if (field === "logo") {
        localLogoUrl.value = form.logo ? URL.createObjectURL(form.logo) : null;
    } else if (field === "header_image") {
        localHeaderUrl.value = form.header_image ? URL.createObjectURL(form.header_image) : null;
        if (form.header_image) form.remove_header_image = false;
    } else if (field === "watermark_image") {
        localWatermarkUrl.value = form.watermark_image ? URL.createObjectURL(form.watermark_image) : null;
        if (form.watermark_image) form.remove_watermark_image = false;
    }
};

const removeHeaderImage = () => {
    form.header_image = null;
    localHeaderUrl.value = null;
    form.remove_header_image = true;
};

const removeWatermarkImage = () => {
    form.watermark_image = null;
    localWatermarkUrl.value = null;
    form.remove_watermark_image = true;
};

const submit = () => {
    form.put(route("settings.update"), {
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
