<template>
    <Head title="Document Tracking" />
    <div class="py-6 space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-500">Document Tracking</p>
                <h1 class="text-2xl font-bold text-gray-900">{{ client.name }}</h1>
                <div class="flex items-center gap-3 mt-1">
                    <p class="text-sm text-gray-600">Passport: <span class="font-semibold">{{ client.passport_number }}</span></p>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset" :class="client.visa_stage_badge_class">
                        {{ client.visa_stage_label }}
                    </span>
                </div>
            </div>
            <Link
                href="/clients"
                class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
            >
                ← Back to clients
            </Link>
        </div>

        <div v-if="flash.success" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ flash.success }}
        </div>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-gray-100 bg-white shadow-sm p-4 space-y-3">
                <h2 class="text-lg font-semibold text-gray-900">Current Location</h2>
                <p class="text-sm text-gray-600">Papers are currently in whose hand</p>
                <div v-if="current" class="space-y-3">
                    <div class="rounded-lg bg-blue-50 p-3 border border-blue-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold uppercase text-blue-600">{{ getHolderTypeLabel(current.holder_type) }}</span>
                            <span
                                v-if="current.days_with_holder !== null"
                                class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold ring-1 ring-inset"
                                :class="current.overdue ? 'bg-red-50 text-red-700 ring-red-200' : 'bg-emerald-50 text-emerald-700 ring-emerald-200'"
                            >
                                {{ formatDays(current.days_with_holder) }} days
                            </span>
                        </div>
                        <p class="text-sm font-bold text-gray-900">{{ current.holder_name }}</p>
                        <div v-if="current.holder_details" class="mt-2 space-y-1 text-xs text-gray-600">
                            <p v-if="current.holder_details.email">📧 {{ current.holder_details.email }}</p>
                            <p v-if="current.holder_details.mobile">📱 {{ current.holder_details.mobile }}</p>
                            <p v-if="current.holder_details.phone">📞 {{ current.holder_details.phone }}</p>
                            <p v-if="current.holder_details.country">🌍 {{ current.holder_details.country }}</p>
                        </div>
                    </div>

                    <div v-if="current.processing_status" class="rounded-lg border p-3" :class="getStatusClass(current.processing_status)">
                        <p class="text-xs font-semibold uppercase mb-1">Processing Status</p>
                        <p class="text-sm font-bold capitalize">{{ formatStatus(current.processing_status) }}</p>
                        <p v-if="current.processing_notes" class="text-xs mt-1 text-gray-600">{{ current.processing_notes }}</p>
                    </div>

                    <div class="space-y-1 text-xs text-gray-600">
                        <div>
                            <span class="font-semibold">Received:</span>
                            <span class="ml-1">{{ formatDate(current.received_at) }}</span>
                        </div>
                        <div v-if="current.expected_return_at">
                            <span class="font-semibold">Expected Return:</span>
                            <span class="ml-1">{{ current.expected_return_at }}</span>
                        </div>
                        <p v-if="current.notes" class="mt-2 text-gray-700 italic">{{ current.notes }}</p>
                    </div>
                </div>
                <div v-else class="text-sm text-gray-500">No current location set. Papers not yet tracked.</div>
            </div>

            <div class="md:col-span-2 rounded-xl border border-gray-100 bg-white shadow-sm p-4 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Transfer Documents</h2>
                <p class="text-sm text-gray-600">Hand over papers to MITT staff, BD company, or other parties</p>
                <form class="grid gap-4 md:grid-cols-2" @submit.prevent="submit">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Holder Type *</label>
                        <HolderTypeSelector
                            v-model="form.to_holder_type"
                            :holder-types="holderTypes"
                        />
                        <p v-if="form.errors.to_holder_type" class="text-xs text-red-600">{{ form.errors.to_holder_type }}</p>
                    </div>

                    <!-- Specific holder selection based on type -->
                    <div v-if="form.to_holder_type === 'agency_user'" class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">MITT Staff Member *</label>
                        <div class="relative">
                            <input
                                v-model="mittStaffSearch"
                                @focus="showMittStaffDropdown = true"
                                @blur="hideMittStaffDropdown"
                                type="text"
                                class="input"
                                :placeholder="selectedMittStaffLabel || 'Search or select staff member...'"
                            />
                            <div
                                v-if="showMittStaffDropdown"
                                class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg max-h-60 overflow-auto"
                            >
                                <div
                                    v-for="user in filteredMittStaff"
                                    :key="user.value"
                                    @mousedown.prevent="selectMittStaff(user)"
                                    class="px-4 py-2.5 cursor-pointer hover:bg-blue-50 border-b border-gray-100 last:border-b-0"
                                >
                                    <div class="font-medium text-gray-900">
                                        {{ user.label }}
                                    </div>
                                </div>
                                <div
                                    v-if="filteredMittStaff.length === 0"
                                    class="px-4 py-3 text-sm text-gray-500 text-center"
                                >
                                    No staff found
                                </div>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="openAddMittStaffModal"
                            class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add MITT Staff
                        </button>
                        <p v-if="form.errors.to_holder_id" class="text-xs text-red-600">{{ form.errors.to_holder_id }}</p>
                    </div>

                    <div v-if="form.to_holder_type === 'agent'" class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Agent</label>
                        <select v-model="form.to_holder_id" class="input">
                            <option value="">Select agent</option>
                            <option v-for="agent in agents" :key="agent.value" :value="agent.value">
                                {{ agent.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.to_holder_id" class="text-xs text-red-600">{{ form.errors.to_holder_id }}</p>
                    </div>

                    <div v-if="form.to_holder_type === 'bd_company'" class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">BD Processing Company *</label>
                        <select v-model="form.to_holder_id" class="input">
                            <option value="">Select company</option>
                            <option v-for="company in bdCompanies" :key="company.value" :value="company.value">
                                {{ company.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.to_holder_id" class="text-xs text-red-600">{{ form.errors.to_holder_id }}</p>
                    </div>

                    <div v-if="form.to_holder_type === 'foreign_company'" class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Foreign Company</label>
                        <select v-model="form.to_holder_id" class="input">
                            <option value="">Select company</option>
                            <option v-for="company in foreignCompanies" :key="company.value" :value="company.value">
                                {{ company.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.to_holder_id" class="text-xs text-red-600">{{ form.errors.to_holder_id }}</p>
                    </div>

                    <div v-if="form.to_holder_type && form.to_holder_type !== 'agency_user' && form.to_holder_type !== 'agent' && form.to_holder_type !== 'bd_company' && form.to_holder_type !== 'foreign_company'" class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Expected Return Date</label>
                        <VueDatePicker
                            v-model="form.expected_return_at"
                            :enable-time-picker="false"
                            model-type="yyyy-MM-dd"
                            class="input"
                        />
                        <p v-if="form.errors.expected_return_at" class="text-xs text-red-600">{{ form.errors.expected_return_at }}</p>
                    </div>

                    <div v-if="form.to_holder_type === 'bd_company' || form.to_holder_type === 'foreign_company'" class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Processing Status</label>
                        <select v-model="form.processing_status" class="input">
                            <option value="">Select status</option>
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.processing_status" class="text-xs text-red-600">{{ form.errors.processing_status }}</p>
                    </div>

                    <div v-if="form.to_holder_type === 'bd_company' || form.to_holder_type === 'foreign_company'" class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Processing Notes</label>
                        <textarea v-model="form.processing_notes" rows="2" class="input" placeholder="Add notes about processing..."></textarea>
                        <p v-if="form.errors.processing_notes" class="text-xs text-red-600">{{ form.errors.processing_notes }}</p>
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-sm font-medium text-gray-700">Transfer Notes</label>
                        <textarea v-model="form.notes" rows="2" class="input" placeholder="Why are papers being transferred..."></textarea>
                        <p v-if="form.errors.notes" class="text-xs text-red-600">{{ form.errors.notes }}</p>
                    </div>
                    <div class="md:col-span-2 flex justify-end gap-2">
                        <button
                            type="button"
                            @click="clearForm"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Clear
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-60"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? "Transferring..." : "Transfer Documents" }}
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section class="rounded-xl border border-gray-100 bg-white shadow-sm p-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Movement History</h2>
            <p class="text-sm text-gray-600 mb-4">Track where papers were previously</p>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-700">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                        <tr>
                            <th class="px-4 py-3">From (Previous Holder)</th>
                            <th class="px-4 py-3">To (New Holder)</th>
                            <th class="px-4 py-3">Transferred At</th>
                            <th class="px-4 py-3">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in history" :key="item.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="text-xs text-gray-500 uppercase block">{{ getHolderTypeLabel(item.from_holder_type) }}</span>
                                <span class="font-medium text-gray-900">{{ item.from_holder_name || "—" }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs text-gray-500 uppercase block">{{ getHolderTypeLabel(item.to_holder_type) }}</span>
                                <span class="font-medium text-gray-900">{{ item.to_holder_name || "—" }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ formatDate(item.moved_at) }}</td>
                            <td class="px-4 py-3 text-gray-600 text-xs italic">{{ item.notes || "—" }}</td>
                        </tr>
                        <tr v-if="history.length === 0">
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">
                                📋 No transfer history yet. Papers haven't been moved.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <Modal :show="showMittStaffModal" @close="closeAddMittStaffModal" max-width="sm">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Add MITT Staff</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-600">Name *</label>
                        <input v-model="newMittStaff.name" class="input mt-1" placeholder="Staff name" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600">Designation</label>
                        <input v-model="newMittStaff.designation" class="input mt-1" placeholder="Designation (optional)" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600">Mobile</label>
                        <input v-model="newMittStaff.mobile" class="input mt-1" placeholder="Mobile (optional)" />
                    </div>
                    <p v-if="mittStaffError" class="text-xs text-red-600">{{ mittStaffError }}</p>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button
                        type="button"
                        @click="closeAddMittStaffModal"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="createMittStaff"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-60"
                        :disabled="mittStaffSaving"
                    >
                        {{ mittStaffSaving ? "Saving..." : "Save" }}
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { computed, watch, onMounted, ref, reactive } from "vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import axios from "axios";
import Modal from "@/Components/Modal.vue";
import HolderTypeSelector from "@/Components/HolderTypeSelector.vue";
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const props = defineProps({
    client: Object,
    current: Object,
    history: {
        type: Array,
        default: () => [],
    },
    holders: {
        type: Array,
        default: () => [],
    },
    agencyUsers: {
        type: Array,
        default: () => [],
    },
    agents: {
        type: Array,
        default: () => [],
    },
    bdCompanies: {
        type: Array,
        default: () => [],
    },
    foreignCompanies: {
        type: Array,
        default: () => [],
    },
    statusOptions: {
        type: Array,
        default: () => [],
    },
});

const formatStatus = (value) => {
    if (!value) return '—';
    const map = {
        pending: 'Pending',
        processing: 'Processing',
        completed: 'Completed',
        rejected: 'Rejected',
        ok: 'Ok',
    };
    return map[value] || value.charAt(0).toUpperCase() + value.slice(1);
};

const flash = computed(() => usePage().props.flash || {});

const formatDays = (value) => {
    if (value === null || value === undefined) {
        return null;
    }
    const num = Number(value);
    if (Number.isNaN(num)) {
        return null;
    }
    if (num > 0 && num < 1) {
        return "< 1";
    }
    return Math.round(num);
};

const form = useForm({
    to_holder_type: "",
    to_holder_id: null,
    expected_return_at: "",
    processing_status: "",
    processing_notes: "",
    notes: "",
});

const holderTypes = ref([...(props.holders || [])]);
const isRestoringForm = ref(true);

const mittStaffOptions = ref([...(props.agencyUsers || [])]);
const mittStaffSearch = ref("");
const showMittStaffDropdown = ref(false);
const showMittStaffModal = ref(false);
const mittStaffSaving = ref(false);
const mittStaffError = ref("");
const newMittStaff = reactive({
    name: "",
    designation: "",
    mobile: "",
});

const selectedMittStaffLabel = computed(() => {
    const match = mittStaffOptions.value.find((user) => user.value === form.to_holder_id);
    return match ? match.label : "";
});

const filteredMittStaff = computed(() => {
    const query = mittStaffSearch.value.toLowerCase().trim();
    if (!query) {
        return mittStaffOptions.value;
    }
    return mittStaffOptions.value.filter((user) =>
        user.label.toLowerCase().includes(query)
    );
});

// LocalStorage key for this client's form
const storageKey = `document_tracking_form_${props.client.id}`;

// Restore form from localStorage on mount
onMounted(() => {
    const saved = localStorage.getItem(storageKey);
    if (saved) {
        try {
            const data = JSON.parse(saved);
            Object.keys(data).forEach(key => {
                if (form.hasOwnProperty(key)) {
                    form[key] = data[key];
                }
            });
        } catch (e) {
            console.error('Failed to restore form data:', e);
        }
    }
    isRestoringForm.value = false;
});

// Save form to localStorage whenever it changes
watch(
    () => ({ ...form.data() }),
    (newData) => {
        localStorage.setItem(storageKey, JSON.stringify(newData));
    },
    { deep: true }
);

watch(
    () => form.to_holder_type,
    (newValue, oldValue) => {
        if (isRestoringForm.value) {
            return;
        }
        if (newValue !== oldValue) {
            onHolderTypeChange();
        }
    }
);

const submit = () => {
    form.post(`/clients/${props.client.id}/documents`, {
        preserveScroll: true,
    });
};

const clearForm = () => {
    form.reset();
    localStorage.removeItem(storageKey);
};

const onHolderTypeChange = () => {
    // Reset holder_id when type changes
    form.to_holder_id = null;
    mittStaffSearch.value = "";
};


const hideMittStaffDropdown = () => {
    setTimeout(() => {
        showMittStaffDropdown.value = false;
    }, 150);
};

const selectMittStaff = (user) => {
    form.to_holder_id = user.value;
    mittStaffSearch.value = user.label;
    showMittStaffDropdown.value = false;
};

const openAddMittStaffModal = () => {
    mittStaffError.value = "";
    showMittStaffModal.value = true;
};

const closeAddMittStaffModal = () => {
    showMittStaffModal.value = false;
    mittStaffError.value = "";
    newMittStaff.name = "";
    newMittStaff.designation = "";
    newMittStaff.mobile = "";
};

const createMittStaff = async () => {
    const name = newMittStaff.name.trim();
    if (!name) {
        mittStaffError.value = "Name is required.";
        return;
    }
    mittStaffSaving.value = true;
    mittStaffError.value = "";
    try {
        const { data } = await axios.post(
            "/office-staff",
            {
                name,
                designation: newMittStaff.designation.trim() || null,
                mobile: newMittStaff.mobile.trim() || null,
                status: "active",
            },
            { headers: { Accept: "application/json" } }
        );
        if (data?.staffMember) {
            mittStaffOptions.value = [...mittStaffOptions.value, data.staffMember];
            form.to_holder_id = data.staffMember.value;
            mittStaffSearch.value = data.staffMember.label;
        }
        closeAddMittStaffModal();
    } catch (error) {
        mittStaffError.value = error?.response?.data?.message || "Failed to add staff member.";
    } finally {
        mittStaffSaving.value = false;
    }
};

const getHolderTypeLabel = (value) => {
    if (!value) return "—";
    const holder = holderTypes.value.find((type) => type.value === value);
    if (holder) {
        return holder.label;
    }
    const map = {
        agency: "MITT (General)",
        agency_user: "MITT Staff",
        bd_company: "BD Company",
        foreign_company: "Foreign Company",
        agent: "Agent",
        other: "Other",
    };
    return map[value] || value;
};

const getStatusClass = (status) => {
    const classes = {
        pending: "bg-yellow-50 border-yellow-200",
        accepted: "bg-green-50 border-green-200",
        rejected: "bg-red-50 border-red-200",
        completed: "bg-blue-50 border-blue-200",
    };
    return classes[status] || "bg-gray-50 border-gray-200";
};

const formatDate = (dateString) => {
    if (!dateString) return "—";
    const date = new Date(dateString);
    return date.toLocaleString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
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
