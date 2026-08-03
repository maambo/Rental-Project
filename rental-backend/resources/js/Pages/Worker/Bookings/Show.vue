<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

interface Booking {
    id: number; job_description: string; status: string;
    location: string | null; scheduled_date: string | null; scheduled_time: string | null;
    client_notes: string | null; worker_notes: string | null;
    rejection_reason: string | null; accepted_at: string | null; completed_at: string | null;
    client: { name: string; email: string };
    worker_service: { service_name: string } | null;
    review: { rating: number; comment: string | null; worker_reply: string | null } | null;
}

const props = defineProps<{ booking: Booking }>();

const showReject      = ref(false);
const showComplete    = ref(false);
const showReply       = ref(false);
const rejectForm      = useForm({ rejection_reason: '' });
const completeForm    = useForm({ worker_notes: '' });
const replyForm       = useForm({ worker_reply: '' });

const accept = () => {
    router.post(route('worker.bookings.accept', props.booking.id), {}, { preserveScroll: true });
};

const submitReject = () => {
    rejectForm.post(route('worker.bookings.reject', props.booking.id), {
        onSuccess: () => { showReject.value = false; },
    });
};

const markInProgress = () => {
    router.post(route('worker.bookings.in-progress', props.booking.id), {}, { preserveScroll: true });
};

const submitComplete = () => {
    completeForm.post(route('worker.bookings.complete', props.booking.id), {
        onSuccess: () => { showComplete.value = false; },
    });
};

const statusClass = (s: string) => ({
    pending:     'bg-yellow-500/20 text-yellow-400',
    accepted:    'bg-blue-500/20 text-blue-400',
    in_progress: 'bg-purple-500/20 text-purple-400',
    completed:   'bg-green-500/20 text-green-400',
    rejected:    'bg-red-500/20 text-red-400',
    cancelled:   'bg-gray-500/20 text-gray-400',
}[s] ?? '');
</script>

<template>
    <Head title="Booking Details" />

    <AuthenticatedLayout>
        <template #header>
            <nav class="flex text-sm text-gray-400 gap-2">
                <Link :href="route('worker.bookings.index')" class="hover:text-white">Bookings</Link>
                <span>/</span>
                <span class="text-white">#{{ booking.id }}</span>
            </nav>
        </template>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">
            <!-- Details card -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <div class="flex justify-between items-start mb-5">
                    <div>
                        <h1 class="text-xl font-bold text-white">Booking #{{ booking.id }}</h1>
                        <p class="text-gray-400 text-sm mt-0.5">from {{ booking.client.name }}</p>
                    </div>
                    <span :class="['px-3 py-1 rounded-full text-sm capitalize', statusClass(booking.status)]">{{ booking.status.replace('_', ' ') }}</span>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                    <div class="flex gap-2"><dt class="text-gray-400 w-28 flex-shrink-0">Client</dt><dd class="text-white">{{ booking.client.name }}</dd></div>
                    <div v-if="booking.worker_service" class="flex gap-2"><dt class="text-gray-400 w-28 flex-shrink-0">Service</dt><dd class="text-white">{{ booking.worker_service.service_name }}</dd></div>
                    <div v-if="booking.location" class="flex gap-2"><dt class="text-gray-400 w-28 flex-shrink-0">Location</dt><dd class="text-gray-300">{{ booking.location }}</dd></div>
                    <div v-if="booking.scheduled_date" class="flex gap-2"><dt class="text-gray-400 w-28 flex-shrink-0">Scheduled</dt><dd class="text-gray-300">{{ booking.scheduled_date }} {{ booking.scheduled_time }}</dd></div>
                    <div class="flex gap-2 sm:col-span-2"><dt class="text-gray-400 w-28 flex-shrink-0">Description</dt><dd class="text-gray-300">{{ booking.job_description }}</dd></div>
                    <div v-if="booking.client_notes" class="flex gap-2 sm:col-span-2"><dt class="text-gray-400 w-28 flex-shrink-0">Client notes</dt><dd class="text-gray-300">{{ booking.client_notes }}</dd></div>
                    <div v-if="booking.rejection_reason" class="flex gap-2 sm:col-span-2"><dt class="text-gray-400 w-28 flex-shrink-0">Rejection</dt><dd class="text-red-400">{{ booking.rejection_reason }}</dd></div>
                </dl>

                <!-- Action buttons -->
                <div class="mt-5 flex flex-wrap gap-3">
                    <button v-if="booking.status === 'pending'" @click="accept"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">Accept</button>
                    <button v-if="booking.status === 'pending'" @click="showReject = true"
                        class="border border-red-500/50 text-red-400 hover:bg-red-500/10 px-4 py-2 rounded-lg text-sm transition-colors">Reject</button>
                    <button v-if="booking.status === 'accepted'" @click="markInProgress"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">Mark In Progress</button>
                    <button v-if="booking.status === 'in_progress'" @click="showComplete = true"
                        class="bg-brand-red hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">Mark Complete</button>
                </div>
            </div>

            <!-- Reject form -->
            <div v-if="showReject" class="bg-gray-800 rounded-xl border border-red-500/50 p-5">
                <form @submit.prevent="submitReject" class="space-y-3">
                    <label class="block text-sm text-gray-300">Reason for rejection <span class="text-red-400">*</span></label>
                    <textarea v-model="rejectForm.rejection_reason" rows="3" required maxlength="500"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                    <div class="flex gap-3">
                        <button type="submit" :disabled="rejectForm.processing" class="bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white px-4 py-2 rounded-lg text-sm">Reject Booking</button>
                        <button type="button" @click="showReject = false" class="text-gray-400 hover:text-white text-sm px-3">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Complete form -->
            <div v-if="showComplete" class="bg-gray-800 rounded-xl border border-green-500/50 p-5">
                <form @submit.prevent="submitComplete" class="space-y-3">
                    <label class="block text-sm text-gray-300">Completion notes (optional)</label>
                    <textarea v-model="completeForm.worker_notes" rows="2" maxlength="500"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                    <div class="flex gap-3">
                        <button type="submit" :disabled="completeForm.processing" class="bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white px-4 py-2 rounded-lg text-sm">Confirm Complete</button>
                        <button type="button" @click="showComplete = false" class="text-gray-400 hover:text-white text-sm px-3">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Review (if any) -->
            <div v-if="booking.review" class="bg-gray-800 rounded-xl border border-gray-700 p-5">
                <h2 class="text-white font-semibold mb-2">Client Review</h2>
                <div class="flex gap-0.5 mb-2">
                    <span v-for="i in 5" :key="i" :class="['text-lg', booking.review.rating >= i ? 'text-yellow-400' : 'text-gray-600']">★</span>
                </div>
                <p v-if="booking.review.comment" class="text-gray-300 text-sm">{{ booking.review.comment }}</p>
                <div v-if="booking.review.worker_reply" class="mt-3 pl-3 border-l-2 border-brand-red">
                    <p class="text-xs text-gray-400 mb-0.5">Your reply:</p>
                    <p class="text-gray-300 text-sm">{{ booking.review.worker_reply }}</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
