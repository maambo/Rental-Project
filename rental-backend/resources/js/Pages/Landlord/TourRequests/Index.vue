<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    CalendarDaysIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    PencilSquareIcon,
    ChatBubbleLeftEllipsisIcon,
} from '@heroicons/vue/24/outline';

interface TourRequest {
    id: number;
    status: 'pending' | 'approved' | 'rejected' | 'cancelled';
    scheduled_at: string;
    notes: string | null;
    landlord_response: string | null;
    created_at: string;
    property: { id: number; title: string };
    user: { id: number; name: string; email: string };
}

interface Paginated {
    data: TourRequest[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
    current_page: number;
    last_page: number;
}

interface Counts {
    total: number;
    pending: number;
    approved: number;
    rejected: number;
}

const props = defineProps<{ tours: Paginated; counts: Counts }>();

// ── Shared selection ─────────────────────────────────────────
const selectedTour = ref<TourRequest | null>(null);

// ── Approve / Reject modal ───────────────────────────────────
const showActionModal = ref(false);
const modalAction     = ref<'approve' | 'reject'>('approve');

const actionForm = useForm({ landlord_response: '' });

function openActionModal(tour: TourRequest, action: 'approve' | 'reject') {
    selectedTour.value = tour;
    modalAction.value  = action;
    actionForm.reset('landlord_response');
    showActionModal.value = true;
}

function submitAction() {
    if (!selectedTour.value) return;
    const routeName = modalAction.value === 'approve'
        ? 'landlord.tour-requests.approve'
        : 'landlord.tour-requests.reject';

    actionForm.post(route(routeName, selectedTour.value.id), {
        onSuccess: () => { showActionModal.value = false; },
    });
}

// ── Edit modal ───────────────────────────────────────────────
const showEditModal = ref(false);

const editForm = useForm({
    scheduled_at:      '',
    landlord_response: '',
});

function openEditModal(tour: TourRequest) {
    selectedTour.value = tour;
    // Convert ISO string to datetime-local format (YYYY-MM-DDTHH:mm)
    const d = new Date(tour.scheduled_at);
    const pad = (n: number) => String(n).padStart(2, '0');
    editForm.scheduled_at      = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    editForm.landlord_response = tour.landlord_response ?? '';
    showEditModal.value = true;
}

function submitEdit() {
    if (!selectedTour.value) return;
    editForm.patch(route('landlord.tour-requests.update', selectedTour.value.id), {
        onSuccess: () => { showEditModal.value = false; },
    });
}

// ── Helpers ──────────────────────────────────────────────────
const statusConfig: Record<string, { pill: string; label: string }> = {
    pending:      { pill: 'bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30', label: 'Pending' },
    approved:     { pill: 'bg-green-500/20  text-green-400  ring-1 ring-green-500/30',  label: 'Approved' },
    rejected:     { pill: 'bg-red-500/20    text-red-400    ring-1 ring-red-500/30',    label: 'Rejected' },
    cancelled:    { pill: 'bg-gray-500/20   text-gray-400   ring-1 ring-gray-500/30',   label: 'Cancelled' },
    rescheduled:  { pill: 'bg-blue-500/20   text-blue-400   ring-1 ring-blue-500/30',   label: 'Rescheduled — Awaiting Tenant' },
};

const fmt = (iso: string) =>
    new Date(iso).toLocaleString('en-ZM', { dateStyle: 'medium', timeStyle: 'short' });

function navigate(url: string | null) {
    if (url) router.visit(url);
}
</script>

<template>
    <Head title="Tour Requests" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Tour Requests</h2>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

            <!-- ── Stat cards ──────────────────────────────────── -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-gray-800 border border-gray-700 rounded-xl p-4 flex items-center gap-3">
                    <CalendarDaysIcon class="h-8 w-8 text-brand-red shrink-0" />
                    <div>
                        <p class="text-2xl font-bold text-white">{{ counts.total }}</p>
                        <p class="text-xs text-gray-400">Total</p>
                    </div>
                </div>
                <div class="bg-gray-800 border border-gray-700 rounded-xl p-4 flex items-center gap-3">
                    <ClockIcon class="h-8 w-8 text-yellow-400 shrink-0" />
                    <div>
                        <p class="text-2xl font-bold text-white">{{ counts.pending }}</p>
                        <p class="text-xs text-gray-400">Pending</p>
                    </div>
                </div>
                <div class="bg-gray-800 border border-gray-700 rounded-xl p-4 flex items-center gap-3">
                    <CheckCircleIcon class="h-8 w-8 text-green-400 shrink-0" />
                    <div>
                        <p class="text-2xl font-bold text-white">{{ counts.approved }}</p>
                        <p class="text-xs text-gray-400">Approved</p>
                    </div>
                </div>
                <div class="bg-gray-800 border border-gray-700 rounded-xl p-4 flex items-center gap-3">
                    <XCircleIcon class="h-8 w-8 text-red-400 shrink-0" />
                    <div>
                        <p class="text-2xl font-bold text-white">{{ counts.rejected }}</p>
                        <p class="text-xs text-gray-400">Rejected</p>
                    </div>
                </div>
            </div>

            <!-- ── Table ───────────────────────────────────────── -->
            <div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-700">
                    <h3 class="text-base font-semibold text-white">All Requests</h3>
                </div>

                <div v-if="tours.data.length === 0" class="text-center py-20 text-gray-500">
                    No tour requests yet.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase">
                            <tr>
                                <th class="px-6 py-3 text-left">Tenant</th>
                                <th class="px-6 py-3 text-left">Property</th>
                                <th class="px-6 py-3 text-left">Scheduled</th>
                                <th class="px-6 py-3 text-left">Notes</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-left">Response</th>
                                <th class="px-6 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            <tr v-for="tour in tours.data" :key="tour.id" class="hover:bg-gray-700/30 transition">
                                <!-- Tenant -->
                                <td class="px-6 py-4">
                                    <p class="font-medium text-white">{{ tour.user.name }}</p>
                                    <p class="text-xs text-gray-400">{{ tour.user.email }}</p>
                                </td>

                                <!-- Property -->
                                <td class="px-6 py-4 text-gray-300">{{ tour.property.title }}</td>

                                <!-- Scheduled -->
                                <td class="px-6 py-4 text-gray-300 whitespace-nowrap">{{ fmt(tour.scheduled_at) }}</td>

                                <!-- Notes -->
                                <td class="px-6 py-4 text-gray-400 max-w-[160px] truncate" :title="tour.notes ?? ''">
                                    {{ tour.notes ?? '—' }}
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', statusConfig[tour.status]?.pill]">
                                        {{ statusConfig[tour.status]?.label ?? tour.status }}
                                    </span>
                                </td>

                                <!-- Landlord response -->
                                <td class="px-6 py-4 text-gray-400 max-w-[160px] truncate" :title="tour.landlord_response ?? ''">
                                    {{ tour.landlord_response ?? '—' }}
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <!-- Approve / Decline — only for pending -->
                                        <template v-if="tour.status === 'pending'">
                                            <button
                                                @click="openActionModal(tour, 'approve')"
                                                class="inline-flex items-center gap-1 rounded-lg bg-green-500/10 hover:bg-green-500/20 text-green-400 border border-green-500/30 px-2.5 py-1.5 text-xs font-medium transition"
                                            >
                                                <CheckCircleIcon class="h-3.5 w-3.5" />Approve
                                            </button>
                                            <button
                                                @click="openActionModal(tour, 'reject')"
                                                class="inline-flex items-center gap-1 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 px-2.5 py-1.5 text-xs font-medium transition"
                                            >
                                                <XCircleIcon class="h-3.5 w-3.5" />Decline
                                            </button>
                                        </template>

                                        <!-- Edit — always available -->
                                        <button
                                            @click="openEditModal(tour)"
                                            class="inline-flex items-center gap-1 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 border border-blue-500/30 px-2.5 py-1.5 text-xs font-medium transition"
                                        >
                                            <PencilSquareIcon class="h-3.5 w-3.5" />Edit
                                        </button>

                                        <!-- Message tenant -->
                                        <Link
                                            :href="route('chat.show', tour.user.id)"
                                            class="inline-flex items-center gap-1 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-300 border border-gray-600 px-2.5 py-1.5 text-xs font-medium transition"
                                        >
                                            <ChatBubbleLeftEllipsisIcon class="h-3.5 w-3.5" />Message
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="tours.last_page > 1" class="px-6 py-4 border-t border-gray-700 flex flex-wrap gap-1 justify-center">
                    <button
                        v-for="link in tours.links"
                        :key="link.label"
                        :disabled="!link.url"
                        @click="navigate(link.url)"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-sm transition',
                            link.active
                                ? 'bg-brand-red text-white'
                                : 'bg-gray-700 text-gray-300 hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed',
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>

        <!-- ── Approve / Reject modal ───────────────────────────── -->
        <Teleport to="body">
            <div v-if="showActionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showActionModal = false" />
                <div class="relative bg-gray-800 border border-gray-700 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
                    <h3 class="text-lg font-semibold text-white capitalize">{{ modalAction }} Tour Request</h3>

                    <div v-if="selectedTour" class="bg-gray-900/50 rounded-lg p-3 space-y-1 text-sm">
                        <p class="text-gray-300"><span class="text-gray-500">Tenant:</span> {{ selectedTour.user.name }}</p>
                        <p class="text-gray-300"><span class="text-gray-500">Property:</span> {{ selectedTour.property.title }}</p>
                        <p class="text-gray-300"><span class="text-gray-500">Scheduled:</span> {{ fmt(selectedTour.scheduled_at) }}</p>
                        <p v-if="selectedTour.notes" class="text-gray-300"><span class="text-gray-500">Notes:</span> {{ selectedTour.notes }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">
                            Message to tenant <span class="text-gray-500">(optional)</span>
                        </label>
                        <textarea
                            v-model="actionForm.landlord_response"
                            rows="3"
                            maxlength="500"
                            :placeholder="modalAction === 'approve'
                                ? 'e.g. Please arrive at 10am, I will meet you at the gate.'
                                : 'e.g. The slot is no longer available. Please request another date.'"
                            class="w-full rounded-lg border border-gray-600 bg-gray-900 text-white text-sm px-3 py-2 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-red"
                        />
                    </div>

                    <div class="flex gap-3 justify-end">
                        <button @click="showActionModal = false"
                            class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-300 text-sm transition">
                            Cancel
                        </button>
                        <button @click="submitAction" :disabled="actionForm.processing"
                            :class="['px-5 py-2 rounded-lg text-sm font-semibold transition disabled:opacity-50',
                                modalAction === 'approve' ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-red-600 hover:bg-red-700 text-white']">
                            {{ actionForm.processing ? 'Saving…' : (modalAction === 'approve' ? 'Confirm Approval' : 'Confirm Decline') }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ── Edit modal ───────────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showEditModal = false" />
                <div class="relative bg-gray-800 border border-gray-700 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
                    <h3 class="text-lg font-semibold text-white">Edit Tour Request</h3>

                    <div v-if="selectedTour" class="bg-gray-900/50 rounded-lg p-3 space-y-1 text-sm">
                        <p class="text-gray-300"><span class="text-gray-500">Tenant:</span> {{ selectedTour.user.name }}</p>
                        <p class="text-gray-300"><span class="text-gray-500">Property:</span> {{ selectedTour.property.title }}</p>
                    </div>

                    <!-- Reschedule -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">New date & time</label>
                        <input
                            v-model="editForm.scheduled_at"
                            type="datetime-local"
                            class="w-full rounded-lg border border-gray-600 bg-gray-900 text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red"
                        />
                        <p v-if="editForm.errors.scheduled_at" class="mt-1 text-xs text-red-400">{{ editForm.errors.scheduled_at }}</p>
                    </div>

                    <!-- Info banner -->
                    <div class="flex items-start gap-2 rounded-lg bg-blue-500/10 border border-blue-500/30 px-3 py-2.5 text-sm text-blue-300">
                        <svg class="h-4 w-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
                        Saving will mark this request as <strong class="text-blue-200 mx-1">Rescheduled</strong> and notify the tenant to accept or decline.
                    </div>

                    <!-- Response message -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">
                            Message to tenant <span class="text-gray-500">(optional)</span>
                        </label>
                        <textarea
                            v-model="editForm.landlord_response"
                            rows="3"
                            maxlength="500"
                            placeholder="e.g. I've rescheduled your tour to the new time shown above."
                            class="w-full rounded-lg border border-gray-600 bg-gray-900 text-white text-sm px-3 py-2 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-red"
                        />
                    </div>

                    <!-- Quick-message link -->
                    <div v-if="selectedTour" class="border-t border-gray-700 pt-3">
                        <p class="text-xs text-gray-500 mb-2">Need a longer conversation?</p>
                        <Link
                            :href="route('chat.show', selectedTour.user.id)"
                            class="inline-flex items-center gap-2 text-sm text-brand-red hover:underline"
                        >
                            <ChatBubbleLeftEllipsisIcon class="h-4 w-4" />
                            Open full chat with {{ selectedTour.user.name }}
                        </Link>
                    </div>

                    <div class="flex gap-3 justify-end">
                        <button @click="showEditModal = false"
                            class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-300 text-sm transition">
                            Cancel
                        </button>
                        <button @click="submitEdit" :disabled="editForm.processing"
                            class="px-5 py-2 rounded-lg bg-brand-red hover:bg-red-700 text-white text-sm font-semibold transition disabled:opacity-50">
                            {{ editForm.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
