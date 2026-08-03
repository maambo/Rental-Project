<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed } from 'vue';
import { CalendarDaysIcon, HomeIcon, ClockIcon, CheckCircleIcon, XCircleIcon, ChatBubbleLeftEllipsisIcon, ChatBubbleOvalLeftIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

interface TourRequest {
    id: number;
    scheduled_at: string;
    notes: string | null;
    status: 'pending' | 'approved' | 'rejected' | 'cancelled' | 'rescheduled';
    landlord_response: string | null;
    created_at: string;
    property: {
        id: number;
        title: string;
        street_address: string;
        landlord_id: number;
    } | null;
}

interface Paginated {
    data: TourRequest[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
    current_page: number;
    last_page: number;
}

const props = defineProps<{ tours: Paginated }>();

const statusConfig = {
    pending:      { pill: 'bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30', label: 'Pending' },
    approved:     { pill: 'bg-green-500/20  text-green-400  ring-1 ring-green-500/30',  label: 'Approved' },
    rejected:     { pill: 'bg-red-500/20    text-red-400    ring-1 ring-red-500/30',    label: 'Rejected' },
    cancelled:    { pill: 'bg-gray-500/20   text-gray-400   ring-1 ring-gray-500/30',   label: 'Cancelled' },
    rescheduled:  { pill: 'bg-blue-500/20   text-blue-400   ring-1 ring-blue-500/30 animate-pulse', label: 'Rescheduled — Action Required' },
};

// Accept / Decline forms (keyed by tour id to avoid shared state)
const acceptForm  = useForm({});
const declineForm = useForm({});

function acceptReschedule(tourId: number) {
    acceptForm.post(route('tenant.tour-requests.accept', tourId));
}
function declineReschedule(tourId: number) {
    declineForm.post(route('tenant.tour-requests.decline', tourId));
}

const pill = (s: string) => statusConfig[s as keyof typeof statusConfig]?.pill ?? statusConfig.pending.pill;
const label = (s: string) => statusConfig[s as keyof typeof statusConfig]?.label ?? s;

const fmt = (d: string) => new Date(d).toLocaleString('en-GB', {
    day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
});

const counts = computed(() => {
    const all = props.tours.data;
    return {
        total:     props.tours.total,
        pending:   all.filter(t => t.status === 'pending').length,
        approved:  all.filter(t => t.status === 'approved').length,
        rejected:  all.filter(t => t.status === 'rejected').length,
    };
});

// Simple SVG donut chart values (stroke-dasharray trick)
const CIRCUMFERENCE = 2 * Math.PI * 40; // r=40
const donutSegments = computed(() => {
    const total = counts.value.total || 1;
    const segments = [
        { key: 'pending',  color: '#eab308', count: counts.value.pending },
        { key: 'approved', color: '#22c55e', count: counts.value.approved },
        { key: 'rejected', color: '#ef4444', count: props.tours.data.filter(t => t.status === 'rejected').length },
    ];
    let offset = 0;
    return segments.map(s => {
        const dash = (s.count / total) * CIRCUMFERENCE;
        const gap  = CIRCUMFERENCE - dash;
        const seg  = { ...s, dash, gap, offset };
        offset += dash;
        return seg;
    });
});
</script>

<template>
    <Head title="My Tour Requests" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <CalendarDaysIcon class="w-5 h-5 text-brand-red" />
                <h2 class="text-xl font-semibold text-white">My Tour Requests</h2>
            </div>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

            <!-- ── Stat cards + donut chart ────────────────────────────── -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                <!-- Stat cards -->
                <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-gray-800 border border-gray-700 rounded-xl p-4 flex flex-col gap-1">
                        <span class="text-xs text-gray-500">Total</span>
                        <span class="text-2xl font-bold text-white">{{ counts.total }}</span>
                        <CalendarDaysIcon class="w-5 h-5 text-gray-500 mt-1" />
                    </div>
                    <div class="bg-gray-800 border border-gray-700 rounded-xl p-4 flex flex-col gap-1">
                        <span class="text-xs text-gray-500">Pending</span>
                        <span class="text-2xl font-bold text-yellow-400">{{ counts.pending }}</span>
                        <ClockIcon class="w-5 h-5 text-yellow-500/50 mt-1" />
                    </div>
                    <div class="bg-gray-800 border border-gray-700 rounded-xl p-4 flex flex-col gap-1">
                        <span class="text-xs text-gray-500">Approved</span>
                        <span class="text-2xl font-bold text-green-400">{{ counts.approved }}</span>
                        <CheckCircleIcon class="w-5 h-5 text-green-500/50 mt-1" />
                    </div>
                    <div class="bg-gray-800 border border-gray-700 rounded-xl p-4 flex flex-col gap-1">
                        <span class="text-xs text-gray-500">Rejected</span>
                        <span class="text-2xl font-bold text-red-400">{{ counts.rejected }}</span>
                        <XCircleIcon class="w-5 h-5 text-red-500/50 mt-1" />
                    </div>
                </div>

                <!-- Donut chart -->
                <div class="bg-gray-800 border border-gray-700 rounded-xl p-4 flex items-center justify-center gap-6">
                    <svg width="100" height="100" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#374151" stroke-width="14" />
                        <template v-if="counts.total > 0">
                            <circle
                                v-for="seg in donutSegments"
                                :key="seg.key"
                                cx="50" cy="50" r="40"
                                fill="none"
                                :stroke="seg.color"
                                stroke-width="14"
                                :stroke-dasharray="`${seg.dash} ${seg.gap}`"
                                :stroke-dashoffset="-seg.offset"
                                style="transform: rotate(-90deg); transform-origin: 50% 50%;"
                            />
                        </template>
                        <text x="50" y="54" text-anchor="middle" class="fill-white" font-size="16" font-weight="bold" fill="white">
                            {{ counts.total }}
                        </text>
                    </svg>
                    <div class="space-y-1.5 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 shrink-0"></span>
                            <span class="text-gray-400">Pending ({{ counts.pending }})</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-green-400 shrink-0"></span>
                            <span class="text-gray-400">Approved ({{ counts.approved }})</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-400 shrink-0"></span>
                            <span class="text-gray-400">Rejected ({{ counts.rejected }})</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── History table ───────────────────────────────────────── -->
            <div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-700 flex items-center gap-2">
                    <CalendarDaysIcon class="w-4 h-4 text-brand-red" />
                    <h3 class="text-sm font-semibold text-white">Tour History</h3>
                </div>

                <div v-if="tours.data.length === 0" class="py-16 text-center text-gray-500">
                    <CalendarDaysIcon class="w-10 h-10 mx-auto mb-3 opacity-30" />
                    <p>You haven't requested any tours yet.</p>
                    <Link :href="route('properties.index')" class="mt-3 inline-block text-brand-red hover:underline text-sm">Browse properties</Link>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-700 text-gray-400 text-left">
                                <th class="px-5 py-3 font-medium">Property</th>
                                <th class="px-5 py-3 font-medium">Scheduled</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3 font-medium">Landlord Response</th>
                                <th class="px-5 py-3 font-medium">Your Notes</th>
                                <th class="px-5 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            <tr v-for="tour in tours.data" :key="tour.id" class="hover:bg-gray-700/30">
                                <!-- Property -->
                                <td class="px-5 py-4">
                                    <Link
                                        v-if="tour.property"
                                        :href="route('properties.show', tour.property.id)"
                                        class="flex items-start gap-2 group"
                                    >
                                        <HomeIcon class="w-4 h-4 text-gray-500 shrink-0 mt-0.5 group-hover:text-brand-red transition-colors" />
                                        <div>
                                            <p class="text-white font-medium group-hover:text-brand-red transition-colors">{{ tour.property.title }}</p>
                                            <p class="text-xs text-gray-500">{{ tour.property.street_address }}</p>
                                        </div>
                                    </Link>
                                    <span v-else class="text-gray-500 italic">Property removed</span>
                                </td>

                                <!-- Scheduled date -->
                                <td class="px-5 py-4 text-gray-300 whitespace-nowrap">{{ fmt(tour.scheduled_at) }}</td>

                                <!-- Status -->
                                <td class="px-5 py-4">
                                    <span :class="['px-2.5 py-0.5 rounded-full text-xs font-semibold', pill(tour.status)]">
                                        {{ label(tour.status) }}
                                    </span>
                                </td>

                                <!-- Landlord response -->
                                <td class="px-5 py-4 text-gray-300 max-w-xs">
                                    <div v-if="tour.landlord_response" class="flex items-start gap-1.5">
                                        <ChatBubbleLeftEllipsisIcon class="w-4 h-4 text-gray-500 shrink-0 mt-0.5" />
                                        <span class="text-sm">{{ tour.landlord_response }}</span>
                                    </div>
                                    <span v-else class="text-gray-600 text-xs italic">Awaiting response</span>
                                </td>

                                <!-- Tenant notes -->
                                <td class="px-5 py-4 text-gray-400 max-w-xs text-xs">
                                    {{ tour.notes ?? '—' }}
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-4">
                                    <!-- Rescheduled: tenant must accept or decline -->
                                    <div v-if="tour.status === 'rescheduled'" class="flex flex-col gap-2">
                                        <div class="flex items-center gap-1.5 text-xs text-blue-400 font-medium mb-1">
                                            <ArrowPathIcon class="w-3.5 h-3.5" />
                                            New time proposed
                                        </div>
                                        <div class="flex gap-2">
                                            <button
                                                @click="acceptReschedule(tour.id)"
                                                :disabled="acceptForm.processing"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-green-500/10 hover:bg-green-500/20 text-green-400 border border-green-500/30 text-xs font-medium transition disabled:opacity-50"
                                            >
                                                <CheckCircleIcon class="w-3.5 h-3.5" />Accept
                                            </button>
                                            <button
                                                @click="declineReschedule(tour.id)"
                                                :disabled="declineForm.processing"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 text-xs font-medium transition disabled:opacity-50"
                                            >
                                                <XCircleIcon class="w-3.5 h-3.5" />Decline
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Default: chat with landlord -->
                                    <Link
                                        v-else-if="tour.property?.landlord_id"
                                        :href="route('chat.show', tour.property.landlord_id)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-300 hover:text-white text-xs font-medium transition-colors"
                                        title="Message the landlord about this tour"
                                    >
                                        <ChatBubbleOvalLeftIcon class="w-4 h-4" />
                                        Message
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="tours.last_page > 1" class="px-5 py-3 border-t border-gray-700 flex gap-1 flex-wrap">
                    <template v-for="lnk in tours.links" :key="lnk.label">
                        <Link
                            v-if="lnk.url"
                            :href="lnk.url"
                            :class="[
                                'px-3 py-1 rounded text-xs transition-colors',
                                lnk.active
                                    ? 'bg-brand-red text-white'
                                    : 'bg-gray-700 text-gray-300 hover:bg-gray-600',
                            ]"
                            v-html="lnk.label"
                        />
                        <span v-else class="px-3 py-1 rounded text-xs bg-gray-800 text-gray-600" v-html="lnk.label" />
                    </template>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
