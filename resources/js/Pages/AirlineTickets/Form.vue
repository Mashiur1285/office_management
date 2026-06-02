<template>
    <Head :title="isEdit ? 'Edit Ticket' : 'New Ticket'" />

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

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
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">Passenger Info</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Link to Client <span class="text-gray-400 font-normal">(optional)</span></label>
                            <select v-model="form.client_id" class="input">
                                <option :value="null">— No client link —</option>
                                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="label">Passenger Name <span class="req">*</span></label>
                            <input v-model="form.passenger_name" type="text" class="input" placeholder="Full name" />
                            <p v-if="form.errors.passenger_name" class="err">{{ form.errors.passenger_name }}</p>
                        </div>
                        <div>
                            <label class="label">Passenger Email <span class="req">*</span></label>
                            <input v-model="form.passenger_email" type="email" class="input" placeholder="email@example.com" />
                            <p v-if="form.errors.passenger_email" class="err">{{ form.errors.passenger_email }}</p>
                        </div>
                        <div>
                            <label class="label">Passenger Phone</label>
                            <input v-model="form.passenger_phone" type="text" class="input" placeholder="+880..." />
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100" />

                <!-- Section: Flight -->
                <div>
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">Flight Details</h2>
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
                            <label class="label">PNR</label>
                            <input v-model="form.pnr" type="text" class="input font-mono" placeholder="e.g. ABCDEF" />
                        </div>
                        <div>
                            <label class="label">Ticket Number</label>
                            <input v-model="form.ticket_number" type="text" class="input font-mono" placeholder="e.g. 997-1234567890" />
                        </div>
                        <div>
                            <label class="label">Origin <span class="req">*</span></label>
                            <input v-model="form.origin" type="text" class="input" placeholder="e.g. DAC - Dhaka" />
                            <p v-if="form.errors.origin" class="err">{{ form.errors.origin }}</p>
                        </div>
                        <div>
                            <label class="label">Destination <span class="req">*</span></label>
                            <input v-model="form.destination" type="text" class="input" placeholder="e.g. DXB - Dubai" />
                            <p v-if="form.errors.destination" class="err">{{ form.errors.destination }}</p>
                        </div>
                        <div>
                            <label class="label">Flight Date <span class="req">*</span></label>
                            <input v-model="form.flight_date" type="date" class="input" />
                            <p v-if="form.errors.flight_date" class="err">{{ form.errors.flight_date }}</p>
                        </div>
                        <div>
                            <label class="label">Departure Time</label>
                            <input v-model="form.departure_time" type="time" class="input" />
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
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

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
    airline_name:    props.ticket?.airline_name    ?? '',
    flight_number:   props.ticket?.flight_number   ?? '',
    pnr:             props.ticket?.pnr             ?? '',
    ticket_number:   props.ticket?.ticket_number   ?? '',
    origin:          props.ticket?.origin          ?? '',
    destination:     props.ticket?.destination     ?? '',
    flight_date:     props.ticket?.flight_date     ?? '',
    departure_time:  props.ticket?.departure_time  ?? '',
    status:          props.ticket?.status          ?? 'confirmed',
    notes:           props.ticket?.notes           ?? '',
});

function submit() {
    if (isEdit.value) {
        form.put(`/airline-tickets/${props.ticket.id}`);
    } else {
        form.post('/airline-tickets');
    }
}
</script>

<style scoped>
.label { @apply block text-sm font-medium text-gray-700 mb-1.5; }
.input { @apply w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white; }
.req   { @apply text-red-500; }
.err   { @apply text-xs text-red-500 mt-1; }
</style>
