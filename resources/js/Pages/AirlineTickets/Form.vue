<template>
    <Head :title="isEdit ? 'Edit Ticket' : 'New Ticket'" />

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Page Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="bg-[#1d4ed8] px-6 py-7 text-white flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-white/10 flex items-center justify-center">
                            <font-awesome-icon icon="plane" class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">{{ isEdit ? 'Edit Ticket' : 'New Airline Ticket' }}</h1>
                            <p class="text-blue-200 text-sm mt-0.5">{{ isEdit ? 'Update ticket details' : 'Add a new passenger ticket' }}</p>
                        </div>
                    </div>
                    <Link href="/airline-tickets" class="flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 rounded-xl text-sm font-medium transition">
                        <font-awesome-icon icon="arrow-left" class="w-4 h-4" />
                        Back
                    </Link>
                </div>
            </div>

            <!-- Form Card -->
            <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">

                <!-- Errors -->
                <div v-if="Object.keys(form.errors).length" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 space-y-1">
                    <p v-for="(err, field) in form.errors" :key="field">{{ err }}</p>
                </div>

                <!-- Section: Passenger -->
                <div>
                    <h2 class="section-title">Passenger Info</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="label">Full Name <span class="req">*</span></label>
                            <input v-model="form.passenger_name" type="text" class="input" placeholder="Full name" />
                            <p v-if="form.errors.passenger_name" class="err">{{ form.errors.passenger_name }}</p>
                        </div>
                        <div>
                            <label class="label">Passport Number</label>
                            <input v-model="form.passport_number" type="text" class="input font-mono" placeholder="e.g. AB1234567" />
                        </div>
                        <div>
                            <label class="label">Phone</label>
                            <input v-model="form.passenger_phone" type="text" class="input" placeholder="+880..." />
                        </div>
                        <div>
                            <label class="label">Email</label>
                            <input v-model="form.passenger_email" type="email" class="input" placeholder="email@example.com" />
                            <p v-if="form.errors.passenger_email" class="err">{{ form.errors.passenger_email }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label">Link to Client <span class="text-gray-400 font-normal">(optional)</span></label>
                            <select v-model="form.client_id" class="input">
                                <option :value="null">— No client link —</option>
                                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100" />

                <!-- Section: Ticket Info -->
                <div>
                    <h2 class="section-title">Ticket Information</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="label">Ticket Number</label>
                            <input v-model="form.ticket_number" type="text" class="input font-mono" placeholder="e.g. 997-1234567890" />
                        </div>
                        <div>
                            <label class="label">PNR Number</label>
                            <input v-model="form.pnr" type="text" class="input font-mono" placeholder="e.g. ABCDEF" />
                        </div>
                        <div>
                            <label class="label">Issue Date</label>
                            <input v-model="form.issue_date" type="date" class="input" />
                        </div>
                        <div>
                            <label class="label">Status <span class="req">*</span></label>
                            <select v-model="form.status" class="input">
                                <option value="confirmed">Confirmed</option>
                                <option value="rescheduled">Rescheduled</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="flown">Flown</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100" />

                <!-- Section: Airline -->
                <div>
                    <h2 class="section-title">Flight Details</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Airline Name <span class="req">*</span></label>
                            <input v-model="form.airline_name" type="text" class="input" placeholder="e.g. Biman Bangladesh" />
                            <p v-if="form.errors.airline_name" class="err">{{ form.errors.airline_name }}</p>
                        </div>
                        <div>
                            <label class="label">Flight Number <span class="req">*</span></label>
                            <input v-model="form.flight_number" type="text" class="input" placeholder="e.g. BG-001" />
                            <p v-if="form.errors.flight_number" class="err">{{ form.errors.flight_number }}</p>
                        </div>
                    </div>
                </div>

                <!-- FROM -->
                <div class="rounded-xl border border-blue-100 bg-blue-50/40 p-4">
                    <h3 class="text-xs font-bold text-blue-700 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <font-awesome-icon icon="plane-departure" class="w-3.5 h-3.5" /> From
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="label">Origin Airport <span class="req">*</span></label>
                            <AirportCombobox v-model="form.origin" placeholder="Search origin airport..." />
                            <p v-if="form.errors.origin" class="err">{{ form.errors.origin }}</p>
                        </div>
                        <div>
                            <label class="label">Departure Date <span class="req">*</span></label>
                            <input v-model="form.flight_date" type="date" class="input" />
                            <p v-if="form.errors.flight_date" class="err">{{ form.errors.flight_date }}</p>
                        </div>
                        <div>
                            <label class="label">Departure Time</label>
                            <input v-model="form.departure_time" type="time" class="input" />
                        </div>
                    </div>
                </div>

                <!-- TO -->
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-4">
                    <h3 class="text-xs font-bold text-emerald-700 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <font-awesome-icon icon="plane" class="w-3.5 h-3.5" /> To
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="label">Destination Airport <span class="req">*</span></label>
                            <AirportCombobox v-model="form.destination" placeholder="Search destination airport..." />
                            <p v-if="form.errors.destination" class="err">{{ form.errors.destination }}</p>
                        </div>
                        <div>
                            <label class="label">Arrival Date</label>
                            <input v-model="form.arrival_date" type="date" class="input" />
                        </div>
                        <div>
                            <label class="label">Arrival Time</label>
                            <input v-model="form.arrival_time" type="time" class="input" />
                        </div>
                    </div>
                </div>

                <!-- Transit -->
                <div class="rounded-xl border border-amber-100 bg-amber-50/30 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-bold text-amber-700 uppercase tracking-widest flex items-center gap-2">
                            <font-awesome-icon icon="rotate" class="w-3.5 h-3.5" /> Transit
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500">No</span>
                            <button
                                type="button"
                                @click="form.has_transit = !form.has_transit"
                                :class="['relative inline-flex h-6 w-11 items-center rounded-full transition-colors', form.has_transit ? 'bg-amber-500' : 'bg-gray-200']"
                            >
                                <span :class="['inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform', form.has_transit ? 'translate-x-6' : 'translate-x-1']"></span>
                            </button>
                            <span class="text-sm text-gray-500">Yes</span>
                        </div>
                    </div>

                    <div v-if="form.has_transit" class="space-y-3">
                        <div v-for="(transit, idx) in form.transits" :key="idx" class="bg-white rounded-lg border border-amber-100 p-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-amber-600">Transit {{ idx + 1 }}</span>
                                <button v-if="form.transits.length > 1" type="button" @click="removeTransit(idx)" class="text-red-400 hover:text-red-600 text-xs">
                                    <font-awesome-icon icon="trash" />
                                </button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="sm:col-span-2">
                                    <label class="label">Transit At</label>
                                    <input v-model="transit.at" type="text" class="input" placeholder="e.g. Dubai (DXB)" />
                                </div>
                                <div>
                                    <label class="label">Transit Date</label>
                                    <input v-model="transit.date" type="date" class="input" />
                                </div>
                                <div>
                                    <label class="label">Transit Time</label>
                                    <input v-model="transit.time" type="time" class="input" />
                                </div>
                            </div>
                        </div>

                        <button type="button" @click="addTransit" class="flex items-center gap-2 text-sm text-amber-600 hover:text-amber-800 font-medium transition">
                            <font-awesome-icon icon="circle-plus" class="w-4 h-4" />
                            Add More Transit
                        </button>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="label">Notes</label>
                    <textarea v-model="form.notes" rows="3" class="input resize-none" placeholder="Any additional notes..."></textarea>
                </div>

                <!-- Submit -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <Link href="/airline-tickets" class="px-5 py-2.5 rounded-full text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-[#1d4ed8] text-white px-6 py-2.5 rounded-full text-sm font-semibold hover:bg-[#1e40af] transition disabled:opacity-60 flex items-center gap-2"
                    >
                        <font-awesome-icon v-if="form.processing" icon="spinner" class="w-3.5 h-3.5 animate-spin" />
                        {{ isEdit ? 'Update Ticket' : 'Create Ticket' }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AirportCombobox from '@/Components/AirportCombobox.vue';

const props = defineProps({
    ticket:  Object,
    clients: Array,
});

const isEdit = computed(() => !!props.ticket);

const form = useForm({
    client_id:       props.ticket?.client_id       ?? null,
    passenger_name:  props.ticket?.passenger_name  ?? '',
    passenger_email: props.ticket?.passenger_email ?? '',
    passenger_phone: props.ticket?.passenger_phone ?? '',
    passport_number: props.ticket?.passport_number ?? '',
    airline_name:    props.ticket?.airline_name    ?? '',
    flight_number:   props.ticket?.flight_number   ?? '',
    pnr:             props.ticket?.pnr             ?? '',
    ticket_number:   props.ticket?.ticket_number   ?? '',
    issue_date:      props.ticket?.issue_date      ?? '',
    origin:          props.ticket?.origin          ?? '',
    destination:     props.ticket?.destination     ?? '',
    flight_date:     props.ticket?.flight_date     ?? '',
    departure_time:  props.ticket?.departure_time  ?? '',
    arrival_date:    props.ticket?.arrival_date    ?? '',
    arrival_time:    props.ticket?.arrival_time    ?? '',
    has_transit:     props.ticket?.has_transit     ?? false,
    transits:        props.ticket?.transits        ?? [{ at: '', date: '', time: '' }],
    status:          props.ticket?.status          ?? 'confirmed',
    notes:           props.ticket?.notes           ?? '',
});

function addTransit() {
    form.transits.push({ at: '', date: '', time: '' });
}

function removeTransit(idx) {
    form.transits.splice(idx, 1);
}

function submit() {
    if (isEdit.value) {
        form.put(`/airline-tickets/${props.ticket.id}`);
    } else {
        form.post('/airline-tickets');
    }
}
</script>

<style scoped>
.section-title { @apply text-sm font-bold text-gray-500 uppercase tracking-widest mb-4; }
.label { @apply block text-sm font-medium text-gray-700 mb-1.5; }
.input { @apply w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white; }
.req   { @apply text-red-500; }
.err   { @apply text-xs text-red-500 mt-1; }
</style>
