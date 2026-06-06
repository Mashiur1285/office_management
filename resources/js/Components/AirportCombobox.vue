<template>
    <div class="relative" ref="wrapper">
        <!-- Input -->
        <input
            ref="inputRef"
            type="text"
            :value="displayValue"
            @input="onInput"
            @focus="open"
            @keydown.escape="close"
            @keydown.down.prevent="moveDown"
            @keydown.up.prevent="moveUp"
            @keydown.enter.prevent="selectHighlighted"
            :placeholder="placeholder"
            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
            autocomplete="off"
        />

        <!-- Dropdown -->
        <div
            v-if="isOpen"
            class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden"
            style="max-height: 260px; overflow-y: auto;"
        >
            <!-- Results -->
            <template v-if="filtered.length">
                <div
                    v-for="(airport, idx) in filtered"
                    :key="airport.id"
                    @mousedown.prevent="selectAirport(airport)"
                    :class="['flex items-center gap-3 px-3.5 py-2.5 cursor-pointer text-sm transition', idx === highlighted ? 'bg-blue-50 text-blue-700' : 'hover:bg-gray-50 text-gray-700']"
                >
                    <span class="font-mono font-bold text-blue-600 w-10 shrink-0">{{ airport.code }}</span>
                    <span class="flex-1">{{ airport.name }}</span>
                    <span class="text-xs text-gray-400 shrink-0">{{ airport.country }}</span>
                    <button @mousedown.stop.prevent="editAirport(airport)" class="text-gray-300 hover:text-blue-500 transition ml-1" title="Edit">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </button>
                    <button @mousedown.stop.prevent="deleteAirport(airport)" class="text-gray-300 hover:text-red-500 transition" title="Delete">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
            <div v-else class="px-3.5 py-2.5 text-sm text-gray-400">No results found.</div>

            <!-- Add new button -->
            <div class="border-t border-gray-100">
                <button
                    @mousedown.prevent="openAddModal"
                    class="w-full flex items-center gap-2 px-3.5 py-2.5 text-sm text-blue-600 hover:bg-blue-50 font-medium transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add new airport
                </button>
            </div>
        </div>

        <!-- Add / Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" @mousedown.self="closeModal">
            <div class="absolute inset-0 bg-black/30 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 z-10">
                <h3 class="text-base font-bold text-gray-800 mb-4">{{ editingAirport ? 'Edit Airport' : 'Add Airport' }}</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Code (e.g. DAC)</label>
                        <input v-model="modalForm.code" type="text" maxlength="10" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 uppercase" placeholder="DAC" />
                        <p v-if="modalError.code" class="text-xs text-red-500 mt-1">{{ modalError.code }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Airport / City Name</label>
                        <input v-model="modalForm.name" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Dhaka (Hazrat Shahjalal)" />
                        <p v-if="modalError.name" class="text-xs text-red-500 mt-1">{{ modalError.name }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Country</label>
                        <input v-model="modalForm.country" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Bangladesh" />
                    </div>
                </div>
                <p v-if="modalError.general" class="text-xs text-red-500 mt-2">{{ modalError.general }}</p>
                <div class="flex justify-end gap-2 mt-5">
                    <button @click="closeModal" class="px-4 py-2 text-sm rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Cancel</button>
                    <button @click="saveModal" :disabled="saving" class="px-4 py-2 text-sm rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition disabled:opacity-60">
                        {{ saving ? 'Saving...' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Search airport...' },
});
const emit = defineEmits(['update:modelValue']);

const wrapper    = ref(null);
const inputRef   = ref(null);
const isOpen     = ref(false);
const query      = ref('');
const airports   = ref([]);
const highlighted = ref(0);
const showModal  = ref(false);
const editingAirport = ref(null);
const saving     = ref(false);
const modalForm  = ref({ code: '', name: '', country: '' });
const modalError = ref({});

const displayValue = computed(() => query.value !== null ? query.value : props.modelValue);

const filtered = computed(() => {
    const q = query.value.toLowerCase();
    if (!q) return airports.value.slice(0, 20);
    return airports.value.filter(a =>
        a.code.toLowerCase().includes(q) ||
        a.name.toLowerCase().includes(q) ||
        (a.country || '').toLowerCase().includes(q)
    ).slice(0, 20);
});

async function fetchAirports() {
    const res = await axios.get('/airports');
    airports.value = res.data;
}

function onInput(e) {
    query.value = e.target.value;
    emit('update:modelValue', e.target.value);
    isOpen.value = true;
    highlighted.value = 0;
}

function open() {
    isOpen.value = true;
    query.value = props.modelValue || '';
}

function close() {
    isOpen.value = false;
}

function selectAirport(airport) {
    const val = `${airport.code} - ${airport.name}`;
    query.value = val;
    emit('update:modelValue', val);
    isOpen.value = false;
}

function moveDown()  { if (highlighted.value < filtered.value.length - 1) highlighted.value++; }
function moveUp()    { if (highlighted.value > 0) highlighted.value--; }
function selectHighlighted() {
    if (filtered.value[highlighted.value]) selectAirport(filtered.value[highlighted.value]);
}

function openAddModal() {
    editingAirport.value = null;
    modalForm.value = { code: '', name: '', country: '' };
    modalError.value = {};
    showModal.value = true;
    isOpen.value = false;
}

function editAirport(airport) {
    editingAirport.value = airport;
    modalForm.value = { code: airport.code, name: airport.name, country: airport.country || '' };
    modalError.value = {};
    showModal.value = true;
    isOpen.value = false;
}

async function deleteAirport(airport) {
    if (!confirm(`Delete "${airport.code} - ${airport.name}"?`)) return;
    await axios.delete(`/airports/${airport.id}`);
    airports.value = airports.value.filter(a => a.id !== airport.id);
}

function closeModal() { showModal.value = false; }

async function saveModal() {
    modalError.value = {};
    if (!modalForm.value.code) { modalError.value.code = 'Code is required'; return; }
    if (!modalForm.value.name) { modalError.value.name = 'Name is required'; return; }
    saving.value = true;
    try {
        if (editingAirport.value) {
            const res = await axios.put(`/airports/${editingAirport.value.id}`, modalForm.value);
            const idx = airports.value.findIndex(a => a.id === editingAirport.value.id);
            if (idx !== -1) airports.value[idx] = res.data;
        } else {
            const res = await axios.post('/airports', modalForm.value);
            airports.value.push(res.data);
            selectAirport(res.data);
        }
        showModal.value = false;
    } catch (err) {
        const errors = err.response?.data?.errors || {};
        modalError.value = {
            code: errors.code?.[0],
            name: errors.name?.[0],
            general: err.response?.data?.message,
        };
    } finally {
        saving.value = false;
    }
}

function onClickOutside(e) {
    if (wrapper.value && !wrapper.value.contains(e.target)) close();
}

onMounted(() => {
    fetchAirports();
    document.addEventListener('mousedown', onClickOutside);
});
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside));

watch(() => props.modelValue, (val) => {
    if (val !== query.value) query.value = val;
});
</script>
