<template>
    <Head title="Create Quotation" />

    <div class="space-y-6">
        <div
            v-if="toastVisible"
            class="fixed right-6 top-6 z-50 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-lg"
        >
            {{ toastMessage }}
        </div>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Quotation</h1>
                <p class="text-sm text-gray-600">
                    Fill in the details and generate a client quotation.
                </p>
            </div>
            <Link
                :href="route('quotations.index')"
                class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors shadow-sm"
            >
                Back to list
            </Link>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Quotation Info</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <FormGroup label="Quotation Date">
                        <input
                            :value="quotationDate"
                            readonly
                            class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-2.5 text-sm text-gray-700"
                        />
                    </FormGroup>
                    <FormGroup label="Quotation Valid Until" :error="form.errors.valid_until">
                        <input
                            v-model="form.valid_until"
                            type="date"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                        />
                    </FormGroup>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Client Info</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <FormGroup label="Client Name" :error="form.errors.client_id">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 min-w-0">
                                <SearchableSelect
                                    v-model="form.client_id"
                                    :options="clientList"
                                    placeholder="Select client"
                                    @change="handleClientChange"
                                />
                            </div>
                            <button
                                type="button"
                                @click="openClientModal"
                                title="Add new client"
                                class="flex-shrink-0 flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500 text-white hover:bg-emerald-600 transition"
                            >
                                <font-awesome-icon icon="plus" class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </FormGroup>
                    <FormGroup label="Organization Name" :error="form.errors.organization_name">
                        <input
                            v-model="form.organization_name"
                            list="quotation-org-list"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                            placeholder="Select or type organization"
                        />
                        <datalist id="quotation-org-list">
                            <option v-for="org in orgList" :key="org" :value="org" />
                        </datalist>
                    </FormGroup>
                    <FormGroup label="Passport Number">
                        <input
                            v-model="form.client_passport"
                            readonly
                            class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-2.5 text-sm text-gray-700"
                        />
                    </FormGroup>
                    <FormGroup label="Mobile Number">
                        <input
                            v-model="form.client_mobile"
                            readonly
                            class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-2.5 text-sm text-gray-700"
                        />
                    </FormGroup>
                    <FormGroup label="Agent Name">
                        <div class="flex items-center gap-2">
                            <input
                                v-model="form.client_agent"
                                list="quotation-agent-list"
                                class="flex-1 min-w-0 rounded-lg border border-gray-200 px-4 py-2 text-sm"
                                placeholder="Select or type agent"
                            />
                            <datalist id="quotation-agent-list">
                                <option v-for="agent in agentList" :key="agent.id" :value="agent.name" />
                            </datalist>
                            <button
                                type="button"
                                @click="openAgentModal"
                                title="Add new agent"
                                class="flex-shrink-0 flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500 text-white hover:bg-emerald-600 transition"
                            >
                                <font-awesome-icon icon="plus" class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </FormGroup>
                    <FormGroup label="Client Email" :error="form.errors.client_email">
                        <input
                            v-model="form.client_email"
                            type="email"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                            placeholder="Client email"
                        />
                    </FormGroup>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Service Info</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <FormGroup label="Select Service" :error="form.errors.service_category">
                        <select
                            v-model="form.service_category"
                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                            @change="handleServiceCategoryChange"
                        >
                            <option value="travel_tourism">Travel and Tourism</option>
                            <option value="manpower_exporting">Manpower Exporting</option>
                            <option value="student_package">Student Package</option>
                            <option value="other_income">Other Income</option>
                        </select>
                    </FormGroup>
                    <FormGroup label="Cost Head" :error="form.errors.service_type">
                        <SubcategorySelector
                            v-model="form.service_type"
                            :subcategories="subcategoryOptions"
                            type="income"
                            :category="form.service_category"
                            label="Cost Head"
                        />
                    </FormGroup>
                </div>
                <div class="mt-4">
                    <FormGroup label="Description" :error="form.errors.description">
                        <textarea
                            v-model="form.description"
                            rows="8"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm transition-all focus:border-[#1d4ed8] focus:ring-1 focus:ring-[#1d4ed8]"
                            placeholder="Write detailed description (maximum 350 words)"
                        ></textarea>
                        <p class="mt-2 text-xs text-gray-500">
                            Word count: {{ descriptionWordCount }} / 350
                        </p>
                    </FormGroup>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Items</h2>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white hover:bg-gray-800"
                        @click="addItem"
                    >
                        <font-awesome-icon icon="circle-plus" />
                        Add Row
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <p v-if="form.errors.items" class="mb-3 text-sm text-red-600">{{ form.errors.items }}</p>
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-600">
                            <tr>
                                <th class="px-3 py-2">SL</th>
                                <th class="px-3 py-2">Service Description</th>
                                <th class="px-3 py-2">Qty</th>
                                <th class="px-3 py-2">Unit Price</th>
                                <th class="px-3 py-2">Discount</th>
                                <th class="px-3 py-2">VAT %</th>
                                <th class="px-3 py-2 text-right">VAT Amt</th>
                                <th class="px-3 py-2 text-right">Line Total</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in form.items" :key="index" class="border-b">
                                <td class="px-3 py-2 font-semibold text-gray-700">{{ index + 1 }}</td>
                                <td class="px-3 py-2">
                                    <input
                                        v-model="item.service_description"
                                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm transition-all focus:border-[#1d4ed8] focus:ring-1 focus:ring-[#1d4ed8]"
                                        placeholder="Service description"
                                    />
                                    <p v-if="form.errors[`items.${index}.service_description`]" class="mt-1 text-xs text-red-600">
                                        {{ form.errors[`items.${index}.service_description`] }}
                                    </p>
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        v-model="item.quantity"
                                        type="number"
                                        min="1"
                                        step="1"
                                        inputmode="numeric"
                                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm transition-all focus:border-[#1d4ed8] focus:ring-1 focus:ring-[#1d4ed8]"
                                        placeholder="1"
                                    />
                                    <p v-if="form.errors[`items.${index}.quantity`]" class="mt-1 text-xs text-red-600">
                                        {{ form.errors[`items.${index}.quantity`] }}
                                    </p>
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        v-model="item.unit_price"
                                        type="number"
                                        min="0"
                                        step="1"
                                        inputmode="numeric"
                                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm transition-all focus:border-[#1d4ed8] focus:ring-1 focus:ring-[#1d4ed8]"
                                        placeholder="0.00"
                                    />
                                    <p v-if="form.errors[`items.${index}.unit_price`]" class="mt-1 text-xs text-red-600">
                                        {{ form.errors[`items.${index}.unit_price`] }}
                                    </p>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <select
                                            v-model="item.discount_type"
                                            class="w-24 rounded-xl border border-gray-200 px-2 py-2 text-sm transition-all focus:border-[#1d4ed8] focus:ring-1 focus:ring-[#1d4ed8]"
                                        >
                                            <option value="percent">%</option>
                                            <option value="amount">৳</option>
                                        </select>
                                        <input
                                            v-model="item.discount_value"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm transition-all focus:border-[#1d4ed8] focus:ring-1 focus:ring-[#1d4ed8]"
                                            placeholder="0"
                                        />
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ money(itemCalculations[index].discountAmount) }}
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        v-model="item.vat_rate"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm transition-all focus:border-[#1d4ed8] focus:ring-1 focus:ring-[#1d4ed8]"
                                        placeholder="0"
                                    />
                                </td>
                                <td class="px-3 py-2 text-right text-gray-700">
                                    {{ money(itemCalculations[index].vatAmount) }}
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                    {{ money(itemCalculations[index].lineTotal) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <button
                                        type="button"
                                        class="text-red-600 hover:text-red-700"
                                        @click="removeItem(index)"
                                        v-if="form.items.length > 1"
                                    >
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Summary</h2>
                <div class="grid gap-4 md:grid-cols-3">
                    <FormGroup label="Subtotal">
                        <input
                            :value="money(subtotal)"
                            readonly
                            class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-2.5 text-sm text-gray-700"
                        />
                    </FormGroup>
                    <FormGroup label="Discount Total">
                        <input
                            :value="money(discountTotal)"
                            readonly
                            class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-2.5 text-sm text-gray-700"
                        />
                    </FormGroup>
                    <FormGroup label="VAT Amount">
                        <input
                            :value="money(vatAmount)"
                            readonly
                            class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-2.5 text-sm text-gray-700"
                        />
                    </FormGroup>
                    <FormGroup label="Total Amount">
                        <input
                            :value="money(totalAmount)"
                            readonly
                            class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 font-semibold"
                        />
                    </FormGroup>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Quotation Maker</h2>
                <FormGroup label="Office Staff" :error="form.errors.quotation_maker_id">
                    <select
                        v-model="form.quotation_maker_id"
                        class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm"
                    >
                        <option value="" disabled>Select staff</option>
                        <option v-for="staff in officeStaff" :key="staff.id" :value="staff.id">
                            {{ staff.name }}
                        </option>
                    </select>
                </FormGroup>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Terms & Conditions</h2>
                <div class="flex flex-wrap gap-4">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" value="default" v-model="form.terms_type" />
                        Default
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" value="custom" v-model="form.terms_type" />
                        Custom
                    </label>
                </div>
                <textarea
                    v-model="form.terms_text"
                    rows="6"
                    class="mt-4 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm transition-all focus:border-[#1d4ed8] focus:ring-1 focus:ring-[#1d4ed8]"
                    :readonly="form.terms_type === 'default'"
                ></textarea>
                <p v-if="form.errors.terms_text" class="mt-2 text-sm text-red-600">
                    {{ form.errors.terms_text }}
                </p>
            </section>

            <div class="flex items-center justify-end gap-3">
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-full bg-[#1d4ed8] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#154130] transition-colors shadow-sm"
                    :disabled="form.processing"
                >
                    <span v-if="!form.processing">Save & Generate PDF</span>
                    <span v-else>Saving...</span>
                </button>
            </div>
        </form>

        <!-- Quick-create Client Modal -->
        <Teleport to="body">
            <div v-if="clientModal.open" class="fixed inset-0 z-[60] flex items-center justify-center p-4" @click.self="closeClientModal">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Add New Client</h3>
                        <button type="button" @click="closeClientModal" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                            <font-awesome-icon icon="xmark" class="h-4 w-4" />
                        </button>
                    </div>

                    <p v-if="clientModal.error" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm text-red-700">{{ clientModal.error }}</p>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Full Name <span class="text-red-500">*</span></label>
                            <input v-model="clientModal.fields.name" class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm" placeholder="Client full name" />
                            <p v-if="clientModal.errors.name" class="mt-1 text-xs text-red-600">{{ clientModal.errors.name[0] }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Passport Number <span class="text-red-500">*</span></label>
                            <input v-model="clientModal.fields.passport_number" class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm font-mono" placeholder="e.g. AB1234567" />
                            <p v-if="clientModal.errors.passport_number" class="mt-1 text-xs text-red-600">{{ clientModal.errors.passport_number[0] }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Mobile</label>
                            <input v-model="clientModal.fields.mobile" class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm" placeholder="+880..." />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Email</label>
                            <input v-model="clientModal.fields.email" type="email" class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm" placeholder="client@example.com" />
                            <p v-if="clientModal.errors.email" class="mt-1 text-xs text-red-600">{{ clientModal.errors.email[0] }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Organization</label>
                            <input v-model="clientModal.fields.organization_name" list="quotation-org-list" class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm" placeholder="Organization name" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Agent</label>
                            <select v-model="clientModal.fields.agent_id" class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm">
                                <option :value="null">— No agent —</option>
                                <option v-for="agent in agentList" :key="agent.id" :value="agent.id">{{ agent.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="closeClientModal" class="rounded-full border border-gray-300 px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="button" @click="submitClientModal" :disabled="clientModal.processing" class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60">
                            <font-awesome-icon v-if="clientModal.processing" icon="spinner" class="h-3.5 w-3.5 animate-spin" />
                            {{ clientModal.processing ? 'Saving...' : 'Save Client' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Quick-create Agent Modal -->
        <Teleport to="body">
            <div v-if="agentModal.open" class="fixed inset-0 z-[60] flex items-center justify-center p-4" @click.self="closeAgentModal">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Add New Agent</h3>
                        <button type="button" @click="closeAgentModal" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                            <font-awesome-icon icon="xmark" class="h-4 w-4" />
                        </button>
                    </div>

                    <p v-if="agentModal.error" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm text-red-700">{{ agentModal.error }}</p>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Agent Name <span class="text-red-500">*</span></label>
                            <input v-model="agentModal.fields.name" class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm" placeholder="Agent full name" />
                            <p v-if="agentModal.errors.name" class="mt-1 text-xs text-red-600">{{ agentModal.errors.name[0] }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Mobile</label>
                            <input v-model="agentModal.fields.mobile" class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm" placeholder="+880..." />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="closeAgentModal" class="rounded-full border border-gray-300 px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="button" @click="submitAgentModal" :disabled="agentModal.processing" class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60">
                            <font-awesome-icon v-if="agentModal.processing" icon="spinner" class="h-3.5 w-3.5 animate-spin" />
                            {{ agentModal.processing ? 'Saving...' : 'Save Agent' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { computed, defineComponent, h, onMounted, reactive, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import SubcategorySelector from '@/Components/SubcategorySelector.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({
    clients: Array,
    officeStaff: Array,
    subcategories: Array,
    agents: { type: Array, default: () => [] },
    organizations: { type: Array, default: () => [] },
    defaultTerms: String,
});

// Local reactive copies so newly created records appear immediately in dropdowns.
const clientList = ref([...(props.clients ?? [])]);
const agentList = ref([...(props.agents ?? [])]);
const orgList = ref([...(props.organizations ?? [])]);

const quotationDate = new Date().toISOString().split('T')[0];

const form = useForm({
    client_id: '',
    organization_name: '',
    client_passport: '',
    client_mobile: '',
    client_agent: '',
    client_email: '',
    service_category: 'travel_tourism',
    service_type: '',
    description: '',
    quotation_maker_id: '',
    terms_type: 'default',
    terms_text: props.defaultTerms,
    valid_until: '',

    items: [
        {
            service_description: '',
            quantity: 1,
            unit_price: '',
            discount_type: 'percent',
            discount_value: '',
            vat_rate: '',
        },
    ],
});

const descriptionWordCount = computed(() => {
    const text = form.description.trim();
    if (!text) return 0;
    return text.split(/\s+/u).filter(Boolean).length;
});

const subcategoryOptions = computed(() => {
    return (props.subcategories || []).filter((item) => item.category === form.service_category);
});

const toastMessage = ref('');
const toastVisible = ref(false);
let toastTimer = null;

const showToast = (message) => {
    toastMessage.value = message;
    toastVisible.value = true;
    if (toastTimer) {
        clearTimeout(toastTimer);
    }
    toastTimer = setTimeout(() => {
        toastVisible.value = false;
    }, 3000);
};

const itemCalculations = computed(() => {
    return form.items.map(item => {
        const quantity = parseFloat(item.quantity) || 0;
        const unitPrice = parseFloat(item.unit_price) || 0;
        const baseAmount = quantity * unitPrice;
        const discountValue = parseFloat(item.discount_value) || 0;
        const discountAmount = item.discount_type === 'amount'
            ? discountValue
            : (baseAmount * discountValue) / 100;
        const safeDiscount = Math.min(baseAmount, Math.max(0, discountAmount));
        const taxable = Math.max(0, baseAmount - safeDiscount);
        const vatRate = parseFloat(item.vat_rate) || 0;
        const vatAmount = (taxable * vatRate) / 100;
        const lineTotal = taxable + vatAmount;

        return {
            baseAmount,
            discountAmount: safeDiscount,
            vatAmount,
            lineTotal,
        };
    });
});

const subtotal = computed(() => {
    return itemCalculations.value.reduce((sum, item) => sum + item.baseAmount, 0);
});

const discountTotal = computed(() => {
    return itemCalculations.value.reduce((sum, item) => sum + item.discountAmount, 0);
});

const vatAmount = computed(() => {
    return itemCalculations.value.reduce((sum, item) => sum + item.vatAmount, 0);
});

const totalAmount = computed(() => {
    return itemCalculations.value.reduce((sum, item) => sum + item.lineTotal, 0);
});

const handleClientChange = () => {
    const client = clientList.value.find(item => item.id === form.client_id);
    if (!client) return;
    form.organization_name = client.organization_name || '';
    form.client_email = client.email || '';
    form.client_mobile = client.mobile || '';
    form.client_passport = client.passport_number || '';
    form.client_agent = client.agent_name || '';
};

const handleServiceCategoryChange = () => {
    form.service_type = '';
};

const addItem = () => {
    form.items.push({
        service_description: '',
        quantity: 1,
        unit_price: '',
        discount_type: 'percent',
        discount_value: '',
        vat_rate: '',
    });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

// ---- Quick-create Client modal ----
const clientModal = reactive({
    open: false,
    processing: false,
    error: '',
    errors: {},
    fields: { name: '', passport_number: '', mobile: '', email: '', organization_name: '', agent_id: null },
});

const openClientModal = () => {
    clientModal.error = '';
    clientModal.errors = {};
    clientModal.fields = { name: '', passport_number: '', mobile: '', email: '', organization_name: '', agent_id: null };
    clientModal.open = true;
};

const closeClientModal = () => {
    clientModal.open = false;
};

const submitClientModal = async () => {
    clientModal.processing = true;
    clientModal.error = '';
    clientModal.errors = {};
    try {
        const { data } = await axios.post(route('clients.quick-store'), clientModal.fields);
        clientList.value.push(data.client);
        if (data.client.organization_name && !orgList.value.includes(data.client.organization_name)) {
            orgList.value.push(data.client.organization_name);
        }
        form.client_id = data.client.id;
        handleClientChange();
        closeClientModal();
    } catch (e) {
        if (e.response?.status === 422) {
            clientModal.errors = e.response.data.errors || {};
            clientModal.error = 'Please correct the highlighted fields.';
        } else {
            clientModal.error = e.response?.data?.message || 'Could not create client. Please try again.';
        }
    } finally {
        clientModal.processing = false;
    }
};

// ---- Quick-create Agent modal ----
const agentModal = reactive({
    open: false,
    processing: false,
    error: '',
    errors: {},
    fields: { name: '', mobile: '' },
});

const openAgentModal = () => {
    agentModal.error = '';
    agentModal.errors = {};
    agentModal.fields = { name: '', mobile: '' };
    agentModal.open = true;
};

const closeAgentModal = () => {
    agentModal.open = false;
};

const submitAgentModal = async () => {
    agentModal.processing = true;
    agentModal.error = '';
    agentModal.errors = {};
    try {
        const { data } = await axios.post(route('agents.quick-store'), agentModal.fields);
        agentList.value.push(data.agent);
        form.client_agent = data.agent.name;
        closeAgentModal();
    } catch (e) {
        if (e.response?.status === 422) {
            agentModal.errors = e.response.data.errors || {};
            agentModal.error = 'Please correct the highlighted fields.';
        } else {
            agentModal.error = e.response?.data?.message || 'Could not create agent. Please try again.';
        }
    } finally {
        agentModal.processing = false;
    }
};

const submit = () => {
    if (form.description && descriptionWordCount.value > 350) {
        form.setError('description', 'Description must be within 350 words.');
        return;
    }

    form.post(route('quotations.store'), {
        onError: (errors) => {
            if (Object.keys(errors).length > 0) {
                showToast('Required fields are missing. Please fill them in and try again.');
            }
        },
    });
};

watch(
    () => form.terms_type,
    (value) => {
        if (value === 'default') {
            form.terms_text = props.defaultTerms;
        }
    }
);

onMounted(() => {
    if (props.officeStaff?.length) {
        form.quotation_maker_id = props.officeStaff[0].id;
    }
});

const money = (value) => {
    if (value === null || value === undefined) return '৳0.00';
    return '৳' + new Intl.NumberFormat('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
};

const FormGroup = defineComponent({
    name: 'FormGroup',
    props: {
        label: { type: String, default: '' },
        error: { type: String, default: '' },
        hint: { type: String, default: '' },
    },
    setup(props, { slots }) {
        return () =>
            h(
                'div',
                { class: 'space-y-2', role: 'group' },
                [
                    props.label
                        ? h('label', { class: 'text-sm font-medium text-gray-700' }, props.label)
                        : null,
                    slots.default ? slots.default() : null,
                    props.hint ? h('p', { class: 'text-xs text-gray-500' }, props.hint) : null,
                    props.error ? h('p', { class: 'text-xs text-red-600' }, props.error) : null,
                ].filter(Boolean)
            );
    },
});
</script>
