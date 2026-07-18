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
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="section-title mb-0">Passenger Info</h2>
                        <span class="text-xs text-gray-400">{{ 1 + form.additional_passengers.length }} passenger(s) under this PNR</span>
                    </div>

                    <!-- Passenger rows (multiple under one PNR) -->
                    <div class="space-y-3">
                        <!-- Primary passenger -->
                        <div class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_1fr_auto] gap-3 sm:items-end">
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
                                <label class="label">Ticket Number</label>
                                <input v-model="form.ticket_number" type="text" class="input font-mono" placeholder="e.g. 997-1234567890" />
                            </div>
                            <button
                                type="button"
                                @click="addPassenger"
                                title="Add another passenger"
                                class="h-[42px] w-[42px] flex-shrink-0 flex items-center justify-center rounded-xl bg-emerald-500 text-white hover:bg-emerald-600 transition"
                            >
                                <font-awesome-icon icon="plus" class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- Additional passengers -->
                        <div
                            v-for="(p, idx) in form.additional_passengers"
                            :key="idx"
                            class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_1fr_auto] gap-3 sm:items-end"
                        >
                            <div>
                                <label class="label sm:hidden">Full Name</label>
                                <input v-model="p.passenger_name" type="text" class="input" placeholder="Full name" />
                            </div>
                            <div>
                                <label class="label sm:hidden">Passport Number</label>
                                <input v-model="p.passport_number" type="text" class="input font-mono" placeholder="e.g. AB1234567" />
                            </div>
                            <div>
                                <label class="label sm:hidden">Ticket Number</label>
                                <input v-model="p.ticket_number" type="text" class="input font-mono" placeholder="e.g. 997-1234567890" />
                            </div>
                            <button
                                type="button"
                                @click="removePassenger(idx)"
                                title="Remove passenger"
                                class="h-[42px] w-[42px] flex-shrink-0 flex items-center justify-center rounded-xl border border-red-200 text-red-500 hover:bg-red-50 transition"
                            >
                                <font-awesome-icon icon="trash" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Contact / booking-level fields -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-100">
                        <div>
                            <label class="label">Phone</label>
                            <input v-model="form.passenger_phone" type="text" class="input" placeholder="+880..." />
                        </div>
                        <div>
                            <label class="label">Email</label>
                            <input v-model="form.passenger_email" type="email" class="input" placeholder="email@example.com" />
                            <p v-if="form.errors.passenger_email" class="err">{{ form.errors.passenger_email }}</p>
                        </div>
                        <div>
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
                            <label class="label">Airline PNR Number</label>
                            <input v-model="form.pnr" type="text" class="input font-mono" placeholder="e.g. ABCDEF" />
                        </div>
                        <div>
                            <label class="label">Reservation / Guest PNR</label>
                            <input v-model="form.reservation_pnr" type="text" class="input font-mono" placeholder="e.g. GUEST123" />
                        </div>
                        <div>
                            <label class="label">Ticket Class</label>
                            <select v-model="form.ticket_class" class="input">
                                <option value="">— Select —</option>
                                <option value="Economy">Economy</option>
                                <option value="Premium Economy">Premium Economy</option>
                                <option value="Business">Business</option>
                                <option value="First">First</option>
                            </select>
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

                    <!-- Purchase / Procurement -->
                    <div class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50/40 p-4 flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex items-center gap-3">
                            <font-awesome-icon icon="receipt" class="w-4 h-4 text-emerald-600" />
                            <span class="label mb-0">Ticket Purchased?</span>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500">No</span>
                                <button
                                    type="button"
                                    @click="form.is_purchased = !form.is_purchased"
                                    :class="['relative inline-flex h-6 w-11 items-center rounded-full transition-colors', form.is_purchased ? 'bg-emerald-500' : 'bg-gray-200']"
                                >
                                    <span :class="['inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform', form.is_purchased ? 'translate-x-6' : 'translate-x-1']"></span>
                                </button>
                                <span class="text-sm text-gray-500">Yes</span>
                            </div>
                        </div>
                        <div v-if="form.is_purchased" class="sm:ml-auto">
                            <label class="label">Purchase Date</label>
                            <input v-model="form.purchase_date" type="date" class="input" />
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100" />

                <!-- Section: Luggage & Amenities -->
                <div>
                    <h2 class="section-title">Luggage &amp; Amenities</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-4">
                            <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <font-awesome-icon icon="suitcase-rolling" class="w-3.5 h-3.5" /> Hand Luggage
                            </h3>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="label">Allowance (kg)</label>
                                    <input v-model="form.hand_luggage_kg" type="text" class="input" placeholder="e.g. 7" />
                                </div>
                                <div>
                                    <label class="label">Max Weight (kg)</label>
                                    <input v-model="form.hand_luggage_max_weight" type="text" class="input" placeholder="e.g. 10" />
                                </div>
                            </div>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-4">
                            <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <font-awesome-icon icon="suitcase" class="w-3.5 h-3.5" /> Cabin Luggage
                            </h3>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="label">Allowance (kg)</label>
                                    <input v-model="form.cabin_luggage_kg" type="text" class="input" placeholder="e.g. 23" />
                                </div>
                                <div>
                                    <label class="label">Max Weight (kg)</label>
                                    <input v-model="form.cabin_luggage_max_weight" type="text" class="input" placeholder="e.g. 30" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-3">
                        <span class="label mb-0">Complementary Food</span>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500">No</span>
                            <button
                                type="button"
                                @click="form.complementary_food = !form.complementary_food"
                                :class="['relative inline-flex h-6 w-11 items-center rounded-full transition-colors', form.complementary_food ? 'bg-emerald-500' : 'bg-gray-200']"
                            >
                                <span :class="['inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform', form.complementary_food ? 'translate-x-6' : 'translate-x-1']"></span>
                            </button>
                            <span class="text-sm text-gray-500">Yes</span>
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
                        <div>
                            <label class="label">Airport Name</label>
                            <input v-model="form.airport_name" type="text" class="input" placeholder="e.g. Hazrat Shahjalal Intl" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">Terminal</label>
                                <input v-model="form.terminal" type="text" class="input" placeholder="e.g. T2" />
                            </div>
                            <div>
                                <label class="label">Gate</label>
                                <input v-model="form.gate" type="text" class="input" placeholder="e.g. 12B" />
                            </div>
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

                <!-- Cancellation Policy -->
                <div class="rounded-xl border border-red-100 bg-red-50/30 p-4">
                    <h3 class="text-xs font-bold text-red-700 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <font-awesome-icon icon="ban" class="w-3.5 h-3.5" /> Cancellation Policy
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex flex-wrap items-center gap-2 text-gray-700">
                            <span>Free cancellation before</span>
                            <input v-model="form.free_cancellation_days" type="number" min="0" class="input w-20 text-center" placeholder="0" />
                            <span>days.</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-gray-700">
                            <span>Partial cancellation before</span>
                            <input v-model="form.partial_cancellation_days" type="number" min="0" class="input w-20 text-center" placeholder="0" />
                            <span>days, charged</span>
                            <input v-model="form.partial_cancellation_percent" type="number" min="0" max="100" step="0.01" class="input w-20 text-center" placeholder="0" />
                            <span>% of the ticket fee.</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-gray-700">
                            <span>No refund before</span>
                            <input v-model="form.no_refund_hours" type="number" min="0" class="input w-20 text-center" placeholder="0" />
                            <span>hours.</span>
                        </div>
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
    reservation_pnr: props.ticket?.reservation_pnr ?? '',
    ticket_number:   props.ticket?.ticket_number   ?? '',
    additional_passengers: props.ticket?.additional_passengers?.map(p => ({
        passenger_name:  p.passenger_name  ?? '',
        passport_number: p.passport_number ?? '',
        ticket_number:   p.ticket_number   ?? '',
    })) ?? [],
    ticket_class:    props.ticket?.ticket_class    ?? '',
    hand_luggage_kg:          props.ticket?.hand_luggage_kg          ?? '',
    hand_luggage_max_weight:  props.ticket?.hand_luggage_max_weight  ?? '',
    cabin_luggage_kg:         props.ticket?.cabin_luggage_kg         ?? '',
    cabin_luggage_max_weight: props.ticket?.cabin_luggage_max_weight ?? '',
    complementary_food:       props.ticket?.complementary_food       ?? false,
    issue_date:      props.ticket?.issue_date      ?? '',
    origin:          props.ticket?.origin          ?? '',
    destination:     props.ticket?.destination     ?? '',
    airport_name:    props.ticket?.airport_name    ?? '',
    terminal:        props.ticket?.terminal        ?? '',
    gate:            props.ticket?.gate            ?? '',
    flight_date:     props.ticket?.flight_date     ?? '',
    departure_time:  props.ticket?.departure_time  ?? '',
    arrival_date:    props.ticket?.arrival_date    ?? '',
    arrival_time:    props.ticket?.arrival_time    ?? '',
    has_transit:     props.ticket?.has_transit     ?? false,
    transits:        props.ticket?.transits        ?? [{ at: '', date: '', time: '' }],
    status:          props.ticket?.status          ?? 'confirmed',
    is_purchased:    props.ticket?.is_purchased    ?? false,
    purchase_date:   props.ticket?.purchase_date   ?? '',
    notes:           props.ticket?.notes           ?? '',
    free_cancellation_days:       props.ticket?.free_cancellation_days       ?? '',
    partial_cancellation_days:    props.ticket?.partial_cancellation_days    ?? '',
    partial_cancellation_percent: props.ticket?.partial_cancellation_percent ?? '',
    no_refund_hours:              props.ticket?.no_refund_hours              ?? '',
});

function addPassenger() {
    form.additional_passengers.push({ passenger_name: '', passport_number: '', ticket_number: '' });
}

function removePassenger(idx) {
    form.additional_passengers.splice(idx, 1);
}

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
