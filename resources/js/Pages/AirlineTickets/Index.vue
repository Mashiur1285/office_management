<template>
    <Head title="Airline Tickets" />

    <div class="px-4 py-8 md:px-6 lg:px-8 bg-[#f5f6f8] min-h-screen text-gray-800 font-sans">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-[32px] font-bold text-gray-900 tracking-tight leading-none mb-2">Airline Tickets</h1>
                <p class="text-sm text-gray-500">Manage passenger tickets and send flight reschedule notifications</p>
            </div>
            <Link
                href="/airline-tickets/create"
                class="bg-[#1d4ed8] text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-[#1e40af] transition flex items-center gap-2 shadow-sm w-fit"
            >
                <font-awesome-icon icon="plus" class="w-3.5 h-3.5" />
                Add Ticket
            </Link>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="bg-gradient-to-br from-[#1d4ed8] to-[#1e3a8a] rounded-[24px] p-6 text-white shadow-md overflow-hidden">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="font-medium text-blue-50 text-[15px]">Total Tickets</h3>
                    <div class="w-8 h-8 rounded-full border border-blue-300/30 flex items-center justify-center bg-white/10">
                        <font-awesome-icon icon="ticket" class="w-3 h-3 text-white" />
                    </div>
                </div>
                <div class="text-[52px] font-bold mb-5 tracking-tight leading-none">{{ stats.total }}</div>
                <div class="text-xs text-blue-100 font-medium">All managed tickets</div>
            </div>

            <div class="bg-white rounded-[24px] p-6 border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="font-medium text-gray-800 text-[15px]">Confirmed</h3>
                    <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center">
                        <font-awesome-icon icon="check" class="w-3 h-3 text-blue-500" />
                    </div>
                </div>
                <div class="text-[52px] font-bold mb-5 tracking-tight leading-none">{{ stats.confirmed }}</div>
                <div class="text-xs text-gray-500 font-medium">Active bookings</div>
            </div>

            <div class="bg-white rounded-[24px] p-6 border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="font-medium text-gray-800 text-[15px]">Rescheduled</h3>
                    <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center">
                        <font-awesome-icon icon="rotate" class="w-3 h-3 text-amber-500" />
                    </div>
                </div>
                <div class="text-[52px] font-bold mb-5 tracking-tight leading-none">{{ stats.rescheduled }}</div>
                <div class="text-xs text-gray-500 font-medium">Date changed by airline</div>
            </div>

            <div class="bg-white rounded-[24px] p-6 border border-gray-100 shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="font-medium text-gray-800 text-[15px]">Cancelled</h3>
                    <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center">
                        <font-awesome-icon icon="xmark" class="w-3 h-3 text-red-500" />
                    </div>
                </div>
                <div class="text-[52px] font-bold mb-5 tracking-tight leading-none">{{ stats.cancelled }}</div>
                <div class="text-xs text-gray-500 font-medium">Cancelled bookings</div>
            </div>
        </div>

        <!-- Flash -->
        <div
            v-if="flash.success"
            class="mb-6 flex items-center gap-3 rounded-[16px] border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-800 shadow-sm"
        >
            <font-awesome-icon icon="check-circle" class="w-5 h-5" />
            <span>{{ flash.success }}</span>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <font-awesome-icon icon="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by name, email, PNR, flight no..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                    @input="applyFilters"
                />
            </div>
            <select
                v-model="statusFilter"
                class="text-sm border border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                @change="applyFilters"
            >
                <option value="">All Statuses</option>
                <option value="confirmed">Confirmed</option>
                <option value="rescheduled">Rescheduled</option>
                <option value="cancelled">Cancelled</option>
                <option value="flown">Flown</option>
            </select>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
                            <th class="text-left px-5 py-3.5 font-semibold text-gray-500 text-xs uppercase tracking-wider">Passenger</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-gray-500 text-xs uppercase tracking-wider">Flight</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-gray-500 text-xs uppercase tracking-wider">Route</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-gray-500 text-xs uppercase tracking-wider">Date</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-gray-500 text-xs uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="tickets.data.length === 0">
                            <td colspan="6" class="px-5 py-16 text-center text-gray-400">
                                <font-awesome-icon icon="plane-slash" class="w-8 h-8 mb-3 block mx-auto opacity-30" />
                                No tickets found.
                            </td>
                        </tr>
                        <tr
                            v-for="ticket in tickets.data"
                            :key="ticket.id"
                            class="hover:bg-gray-50/50 transition-colors"
                        >
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ ticket.passenger_name }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ ticket.passenger_email }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ ticket.flight_number }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ ticket.airline_name }}</div>
                                <div v-if="ticket.pnr" class="text-xs text-gray-400 mt-0.5 font-mono">PNR: {{ ticket.pnr }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-1.5 font-semibold text-gray-800">
                                    <span>{{ ticket.origin }}</span>
                                    <font-awesome-icon icon="arrow-right" class="w-3 h-3 text-gray-400" />
                                    <span>{{ ticket.destination }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ formatDate(ticket.flight_date) }}</div>
                                <div v-if="ticket.original_flight_date" class="text-xs text-red-400 line-through mt-0.5">
                                    {{ formatDate(ticket.original_flight_date) }}
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold capitalize" :class="statusClass(ticket.status)">
                                    {{ ticket.status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <Link
                                    :href="`/airline-tickets/${ticket.id}`"
                                    class="text-blue-700 hover:text-blue-900 font-semibold text-xs px-3 py-1.5 rounded-lg hover:bg-blue-50 transition"
                                >
                                    View
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="tickets.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
                <span>Showing {{ tickets.from }} – {{ tickets.to }} of {{ tickets.total }}</span>
                <div class="flex gap-2">
                    <Link
                        v-if="tickets.prev_page_url"
                        :href="tickets.prev_page_url"
                        class="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-700 font-medium transition"
                    >Prev</Link>
                    <Link
                        v-if="tickets.next_page_url"
                        :href="tickets.next_page_url"
                        class="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-700 font-medium transition"
                    >Next</Link>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    tickets: Object,
    stats:   Object,
    filters: Object,
});

const page   = usePage();
const flash  = computed(() => page.props.flash ?? {});
const search       = ref(props.filters?.search ?? '');
const statusFilter = ref(props.filters?.status ?? '');

let debounceTimer = null;

function applyFilters() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/airline-tickets', { search: search.value, status: statusFilter.value }, { preserveState: true, replace: true });
    }, 300);
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
