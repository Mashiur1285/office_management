<template>
    <Head title="Ticket Details" />

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Header Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-[#1d4ed8] px-6 py-8 text-white">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="h-16 w-16 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                                <font-awesome-icon icon="plane" class="w-7 h-7 text-white" />
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold mb-1">{{ ticket.passenger_name }}</h1>
                                <div class="flex flex-wrap items-center gap-2 text-sm">
                                    <span class="bg-white/20 px-3 py-1 rounded-full font-mono font-semibold">
                                        {{ ticket.flight_number }}
                                    </span>
                                    <span class="bg-white/20 px-3 py-1 rounded-full">
                                        {{ ticket.airline_name }}
                                    </span>
                                    <span v-if="ticket.pnr" class="bg-white/20 px-3 py-1 rounded-full font-mono">
                                        Airline PNR: {{ ticket.pnr }}
                                    </span>
                                    <span v-if="ticket.reservation_pnr" class="bg-white/20 px-3 py-1 rounded-full font-mono">
                                        Res PNR: {{ ticket.reservation_pnr }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <Link href="/airline-tickets" class="flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 rounded-xl text-sm font-medium transition">
                                <font-awesome-icon icon="arrow-left" class="w-4 h-4" />
                                Back
                            </Link>
                            <a :href="`/airline-tickets/${ticket.id}/pdf`" target="_blank" class="flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 rounded-xl text-sm font-medium transition">
                                <font-awesome-icon icon="print" class="w-4 h-4" />
                                Print
                            </a>
                            <a :href="`/airline-tickets/${ticket.id}/pdf?action=download`" class="flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 rounded-xl text-sm font-medium transition">
                                <font-awesome-icon icon="file-pdf" class="w-4 h-4" />
                                PDF
                            </a>
                            <Link :href="`/airline-tickets/${ticket.id}/edit`" class="flex items-center gap-2 px-5 py-2.5 bg-white text-[#1d4ed8] hover:bg-blue-50 rounded-xl text-sm font-semibold shadow transition">
                                <font-awesome-icon icon="pen" class="w-4 h-4" />
                                Edit
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flash -->
            <div
                v-if="flash.success"
                class="flex items-center gap-3 rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-800 shadow-sm"
            >
                <font-awesome-icon icon="check-circle" class="w-5 h-5 flex-shrink-0" />
                <span>{{ flash.success }}</span>
            </div>
            <div
                v-if="flash.error"
                class="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 shadow-sm"
            >
                <font-awesome-icon icon="circle-exclamation" class="w-5 h-5 flex-shrink-0" />
                <span>{{ flash.error }}</span>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left: Main Details -->
                <div class="lg:col-span-2 space-y-5">

                    <!-- Flight Info -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-5">Flight Information</h2>

                        <!-- Route Visual -->
                        <div class="flex items-center justify-center gap-4 py-5 mb-5 bg-gray-50 rounded-xl">
                            <div class="text-center">
                                <div class="text-3xl font-black text-gray-900 tracking-wide">{{ ticket.origin }}</div>
                                <div class="text-[10px] text-gray-400 mt-1 font-semibold uppercase tracking-wider">Departure</div>
                                <div class="text-sm font-semibold text-gray-700 mt-0.5">
                                    {{ formatDate(ticket.flight_date) }}
                                    <span v-if="ticket.departure_time" class="text-gray-500 font-normal">· {{ ticket.departure_time }}</span>
                                </div>
                            </div>
                            <div class="flex-1 flex items-center gap-2 px-4">
                                <div class="flex-1 border-t-2 border-dashed border-gray-300"></div>
                                <font-awesome-icon icon="plane" class="w-5 h-5 text-[#1d4ed8]" />
                                <div class="flex-1 border-t-2 border-dashed border-gray-300"></div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-black text-gray-900 tracking-wide">{{ ticket.destination }}</div>
                                <div class="text-[10px] text-gray-400 mt-1 font-semibold uppercase tracking-wider">Arrival</div>
                                <div class="text-sm font-semibold text-gray-700 mt-0.5">
                                    {{ ticket.arrival_date ? formatDate(ticket.arrival_date) : '—' }}
                                    <span v-if="ticket.arrival_time" class="text-gray-500 font-normal">· {{ ticket.arrival_time }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-4 gap-x-6 text-sm">
                            <div>
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Airline</span>
                                <p class="font-semibold text-gray-900 mt-1">{{ ticket.airline_name }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Flight No.</span>
                                <p class="font-semibold text-gray-900 font-mono mt-1">{{ ticket.flight_number }}</p>
                            </div>
                            <div v-if="ticket.pnr">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Airline PNR</span>
                                <p class="font-semibold text-gray-900 font-mono mt-1">{{ ticket.pnr }}</p>
                            </div>
                            <div v-if="ticket.reservation_pnr">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Reservation PNR</span>
                                <p class="font-semibold text-gray-900 font-mono mt-1">{{ ticket.reservation_pnr }}</p>
                            </div>
                            <div v-if="ticket.ticket_class">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Class</span>
                                <p class="font-semibold text-gray-900 mt-1">{{ ticket.ticket_class }}</p>
                            </div>
                            <div v-if="ticket.issue_date">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Issue Date</span>
                                <p class="font-semibold text-gray-900 mt-1">{{ formatDate(ticket.issue_date) }}</p>
                            </div>
                            <div v-if="ticket.airport_name">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Airport</span>
                                <p class="font-semibold text-gray-900 mt-1">{{ ticket.airport_name }}</p>
                            </div>
                            <div v-if="ticket.terminal">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Terminal</span>
                                <p class="font-semibold text-gray-900 mt-1">{{ ticket.terminal }}</p>
                            </div>
                            <div v-if="ticket.gate">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Gate</span>
                                <p class="font-semibold text-gray-900 mt-1">{{ ticket.gate }}</p>
                            </div>
                        </div>

                        <!-- Transit Info -->
                        <div v-if="ticket.has_transit && ticket.transits?.length" class="mt-5 pt-5 border-t border-gray-100">
                            <h3 class="text-xs font-bold text-amber-600 uppercase tracking-wider mb-3">Transit</h3>
                            <div class="space-y-2">
                                <div v-for="(t, i) in ticket.transits" :key="i" class="bg-amber-50 rounded-lg px-4 py-2.5 text-sm flex flex-wrap gap-x-4 gap-y-1">
                                    <span class="font-bold text-amber-700">{{ i + 1 }}.</span>
                                    <span class="font-semibold text-gray-800">{{ t.at }}</span>
                                    <span v-if="t.date" class="text-gray-500">{{ formatDate(t.date) }}</span>
                                    <span v-if="t.time" class="text-gray-500">{{ t.time }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Passenger Info -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Passenger Info</h2>
                            <span v-if="allPassengers.length > 1" class="text-xs font-semibold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full">
                                {{ allPassengers.length }} passengers
                            </span>
                        </div>

                        <!-- Passengers table -->
                        <div class="overflow-x-auto mb-5">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                        <th class="py-2 pr-4 font-semibold">#</th>
                                        <th class="py-2 pr-4 font-semibold">Full Name</th>
                                        <th class="py-2 pr-4 font-semibold">Passport No.</th>
                                        <th class="py-2 font-semibold">Ticket No.</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <tr v-for="(p, i) in allPassengers" :key="i">
                                        <td class="py-2.5 pr-4 text-gray-400 font-semibold">{{ i + 1 }}</td>
                                        <td class="py-2.5 pr-4 font-semibold text-gray-900">{{ p.passenger_name || '—' }}</td>
                                        <td class="py-2.5 pr-4 font-mono text-gray-700">{{ p.passport_number || '—' }}</td>
                                        <td class="py-2.5 font-mono text-gray-700">{{ p.ticket_number || '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                            <div v-if="ticket.passenger_phone">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Phone</span>
                                <p class="font-medium text-gray-700 mt-1">{{ ticket.passenger_phone }}</p>
                            </div>
                            <div v-if="ticket.passenger_email">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Email</span>
                                <p class="font-medium text-gray-700 mt-1">{{ ticket.passenger_email }}</p>
                            </div>
                            <div v-if="ticket.client">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Linked Client</span>
                                <Link :href="`/clients/${ticket.client.id}`" class="block mt-1 text-blue-700 hover:underline font-semibold">
                                    {{ ticket.client.name }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Luggage & Amenities -->
                    <div v-if="hasLuggageInfo" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">Luggage &amp; Amenities</h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                            <div v-if="ticket.hand_luggage_kg || ticket.hand_luggage_max_weight">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Hand Luggage</span>
                                <p class="font-semibold text-gray-900 mt-1">
                                    {{ ticket.hand_luggage_kg ? ticket.hand_luggage_kg + ' kg' : '—' }}
                                    <span v-if="ticket.hand_luggage_max_weight" class="text-gray-500 font-normal">(max {{ ticket.hand_luggage_max_weight }} kg)</span>
                                </p>
                            </div>
                            <div v-if="ticket.cabin_luggage_kg || ticket.cabin_luggage_max_weight">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Cabin Luggage</span>
                                <p class="font-semibold text-gray-900 mt-1">
                                    {{ ticket.cabin_luggage_kg ? ticket.cabin_luggage_kg + ' kg' : '—' }}
                                    <span v-if="ticket.cabin_luggage_max_weight" class="text-gray-500 font-normal">(max {{ ticket.cabin_luggage_max_weight }} kg)</span>
                                </p>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Complementary Food</span>
                                <p class="font-semibold mt-1" :class="ticket.complementary_food ? 'text-emerald-600' : 'text-gray-500'">
                                    {{ ticket.complementary_food ? 'Yes' : 'No' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Cancellation Policy -->
                    <div v-if="hasCancellationInfo" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-3">Cancellation Policy</h2>
                        <ul class="space-y-2 text-sm text-gray-700 list-disc pl-5">
                            <li v-if="ticket.free_cancellation_days != null">
                                Free cancellation before <strong>{{ ticket.free_cancellation_days }}</strong> days.
                            </li>
                            <li v-if="ticket.partial_cancellation_days != null || ticket.partial_cancellation_percent != null">
                                Partial cancellation before <strong>{{ ticket.partial_cancellation_days ?? '—' }}</strong> days,
                                charged <strong>{{ ticket.partial_cancellation_percent ?? '—' }}%</strong> of the ticket fee.
                            </li>
                            <li v-if="ticket.no_refund_hours != null">
                                No refund before <strong>{{ ticket.no_refund_hours }}</strong> hours.
                            </li>
                        </ul>
                    </div>

                    <!-- Notes -->
                    <div v-if="ticket.notes" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-3">Notes</h2>
                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ ticket.notes }}</p>
                    </div>

                </div>

                <!-- Right: Date + Actions -->
                <div class="space-y-5">

                    <!-- Date Card -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">Schedule</h2>

                        <div class="space-y-4">
                            <div>
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Flight Date</span>
                                <p class="text-xl font-black text-gray-900 mt-1">{{ formatDate(ticket.flight_date) }}</p>
                            </div>
                            <div v-if="ticket.original_flight_date" class="pt-3 border-t border-gray-100">
                                <span class="text-xs font-semibold text-red-400 uppercase tracking-wider">Original Date</span>
                                <p class="text-base font-bold text-red-400 line-through mt-1">{{ formatDate(ticket.original_flight_date) }}</p>
                            </div>
                            <div v-if="ticket.departure_time">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Departure</span>
                                <p class="font-semibold text-gray-900 mt-1">{{ ticket.departure_time }}</p>
                            </div>
                            <div v-if="ticket.arrival_date" class="pt-3 border-t border-gray-100">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Arrival Date</span>
                                <p class="text-lg font-black text-gray-900 mt-1">{{ formatDate(ticket.arrival_date) }}</p>
                            </div>
                            <div v-if="ticket.arrival_time">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Arrival</span>
                                <p class="font-semibold text-gray-900 mt-1">{{ ticket.arrival_time }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold capitalize"
                                :class="statusClass(ticket.status)"
                            >
                                {{ ticket.status }}
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold"
                                :class="ticket.is_purchased ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-gray-100 text-gray-500 ring-1 ring-gray-200'"
                            >
                                <font-awesome-icon :icon="ticket.is_purchased ? 'circle-check' : 'circle-xmark'" class="w-3 h-3" />
                                {{ ticket.is_purchased ? 'Purchased' : 'Not Purchased' }}
                            </span>
                        </div>
                        <div v-if="ticket.is_purchased && ticket.purchase_date" class="mt-2 text-xs text-gray-500">
                            Purchased on <span class="font-semibold text-gray-700">{{ formatDate(ticket.purchase_date) }}</span>
                        </div>
                    </div>

                    <!-- Reschedule Action Card -->
                    <div class="bg-amber-50 rounded-2xl border border-amber-200 shadow-sm p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <font-awesome-icon icon="rotate" class="w-4 h-4 text-amber-600" />
                            <h2 class="text-sm font-bold text-amber-800">Reschedule Flight</h2>
                        </div>
                        <p class="text-xs text-amber-700 mb-4 leading-relaxed">
                            Change the flight date and automatically send an email notification to
                            <strong>{{ ticket.passenger_email }}</strong>.
                        </p>
                        <button
                            @click="openRescheduleModal"
                            class="w-full bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold py-2.5 rounded-xl transition flex items-center justify-center gap-2"
                        >
                            <font-awesome-icon icon="paper-plane" class="w-3.5 h-3.5" />
                            Change Date & Notify
                        </button>
                    </div>

                    <!-- Danger Zone -->
                    <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-5">
                        <h2 class="text-xs font-bold text-red-400 uppercase tracking-widest mb-3">Danger Zone</h2>
                        <button
                            @click="deleteTicket"
                            class="w-full border border-red-200 text-red-600 hover:bg-red-50 text-sm font-semibold py-2.5 rounded-xl transition"
                        >
                            Delete Ticket
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Reschedule Modal -->
    <Teleport to="body">
        <Transition
            enter-from-class="opacity-0"
            enter-active-class="transition duration-200"
            leave-to-class="opacity-0"
            leave-active-class="transition duration-150"
        >
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showModal = false">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">

                    <!-- Modal Header -->
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-amber-100 flex items-center justify-center">
                                <font-awesome-icon icon="rotate" class="w-4 h-4 text-amber-600" />
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">Reschedule Flight</h3>
                                <p class="text-xs text-gray-500">An email will be sent to the passenger</p>
                            </div>
                        </div>
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100">
                            <font-awesome-icon icon="xmark" class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Current date info -->
                    <div class="bg-gray-50 rounded-xl px-4 py-3 mb-5 text-sm">
                        <span class="text-gray-500">Current date:</span>
                        <span class="font-bold text-gray-900 ml-2">{{ formatDate(ticket.flight_date) }}</span>
                    </div>

                    <form @submit.prevent="submitReschedule" class="space-y-4">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                New Flight Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="rescheduleForm.new_flight_date"
                                type="date"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                                required
                            />
                            <p v-if="rescheduleForm.errors.new_flight_date" class="text-xs text-red-500 mt-1">
                                {{ rescheduleForm.errors.new_flight_date }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">New Departure Time</label>
                            <input
                                v-model="rescheduleForm.new_departure_time"
                                type="time"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Reason <span class="text-gray-400 font-normal">(optional)</span></label>
                            <textarea
                                v-model="rescheduleForm.reason"
                                rows="3"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none"
                                placeholder="Airline schedule change, operational reasons..."
                            ></textarea>
                        </div>

                        <!-- Email preview hint -->
                        <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-xs text-blue-700 flex items-start gap-2">
                            <font-awesome-icon icon="envelope" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" />
                            <span>
                                A notification email will be sent to
                                <strong>{{ ticket.passenger_email }}</strong> with the new flight date.
                            </span>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button
                                type="button"
                                @click="showModal = false"
                                class="flex-1 border border-gray-200 text-gray-700 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50 transition"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="rescheduleForm.processing"
                                class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-xl text-sm font-bold transition disabled:opacity-60 flex items-center justify-center gap-2"
                            >
                                <font-awesome-icon v-if="rescheduleForm.processing" icon="spinner" class="w-3.5 h-3.5 animate-spin" />
                                <font-awesome-icon v-else icon="paper-plane" class="w-3.5 h-3.5" />
                                {{ rescheduleForm.processing ? 'Sending...' : 'Confirm & Notify' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    ticket: Object,
});

const page  = usePage();
const flash = computed(() => page.props.flash ?? {});

const allPassengers = computed(() => {
    const primary = {
        passenger_name:  props.ticket.passenger_name,
        passport_number: props.ticket.passport_number,
        ticket_number:   props.ticket.ticket_number,
    };
    return [primary, ...(props.ticket.additional_passengers ?? [])];
});

const hasLuggageInfo = computed(() => {
    const t = props.ticket;
    return !!(t.hand_luggage_kg || t.hand_luggage_max_weight || t.cabin_luggage_kg || t.cabin_luggage_max_weight || t.complementary_food);
});

const hasCancellationInfo = computed(() => {
    const t = props.ticket;
    return t.free_cancellation_days != null || t.partial_cancellation_days != null
        || t.partial_cancellation_percent != null || t.no_refund_hours != null;
});

const showModal = ref(false);

const rescheduleForm = useForm({
    new_flight_date:     '',
    new_departure_time:  '',
    reason:              '',
});

function openRescheduleModal() {
    rescheduleForm.reset();
    showModal.value = true;
}

function submitReschedule() {
    rescheduleForm.post(`/airline-tickets/${props.ticket.id}/reschedule`, {
        onSuccess: () => {
            showModal.value = false;
        },
    });
}

function deleteTicket() {
    if (!confirm(`Delete ticket for ${props.ticket.passenger_name}? This cannot be undone.`)) return;
    router.delete(`/airline-tickets/${props.ticket.id}`);
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function statusClass(status) {
    const map = {
        confirmed:   'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
        rescheduled: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
        cancelled:   'bg-red-50 text-red-700 ring-1 ring-red-200',
        flown:       'bg-gray-100 text-gray-600 ring-1 ring-gray-200',
    };
    return map[status] ?? 'bg-gray-100 text-gray-600';
}
</script>
