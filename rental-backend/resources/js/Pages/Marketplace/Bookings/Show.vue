<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { StarIcon } from '@heroicons/vue/24/solid';

interface Booking {
    id: number; job_description: string; status: string;
    location: string | null; scheduled_date: string | null; scheduled_time: string | null;
    agreed_price: string | null; client_notes: string | null; worker_notes: string | null;
    rejection_reason: string | null; accepted_at: string | null; completed_at: string | null;
    created_at: string;
    worker_profile: { id: number; user: { name: string }; category: { name: string; icon: string }; phone: string; };
    worker_service: { service_name: string; base_rate: string } | null;
    review: { rating: number; comment: string | null; worker_reply: string | null } | null;
}

const props = defineProps<{ booking: Booking }>();

const showReview = ref(false);
const reviewForm = useForm({ rating: 5, comment: '' });

const submitReview = () => {
    reviewForm.post(route('marketplace.bookings.review', props.booking.id), {
        onSuccess: () => { showReview.value = false; },
    });
};

const cancel = () => {
    if (confirm('Cancel this booking?')) {
        router.post(route('marketplace.bookings.cancel', props.booking.id));
    }
};

const statusClass = (s: string) => ({
    pending:     'bg-yellow-500/20 text-yellow-400',
    accepted:    'bg-blue-500/20 text-blue-400',
    rejected:    'bg-red-500/20 text-red-400',
    in_progress: 'bg-purple-500/20 text-purple-400',
    completed:   'bg-green-500/20 text-green-400',
    cancelled:   'bg-gray-500/20 text-gray-400',
    disputed:    'bg-orange-500/20 text-orange-400',
}[s] ?? '');
</script>

<template>
    <Head title="Booking Details" />

    <AuthenticatedLayout>
        <template #header>
            <nav class="flex text-sm text-gray-400 gap-2">
                <Link :href="route('marketplace.bookings.index')" class="hover:text-white">My Bookings</Link>
                <span>/</span>
                <span class="text-white">#{{ booking.id }}</span>
            </nav>
        </template>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">
            <!-- Status card -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h1 class="text-xl font-bold text-white">Booking #{{ booking.id }}</h1>
                        <p class="text-gray-400 text-sm">{{ booking.worker_profile.category.icon }} {{ booking.worker_profile.category.name }}</p>
                    </div>
                    <span :class="['px-3 py-1 rounded-full text-sm capitalize', statusClass(booking.status)]">{{ booking.status.replace('_', ' ') }}</span>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                    <div class="flex gap-2"><dt class="text-gray-400 w-28 flex-shrink-0">Worker</dt><dd class="text-white">{{ booking.worker_profile.user.name }}</dd></div>
                    <div v-if="booking.worker_service" class="flex gap-2"><dt class="text-gray-400 w-28 flex-shrink-0">Service</dt><dd class="text-white">{{ booking.worker_service.service_name }}</dd></div>
                    <div v-if="booking.location" class="flex gap-2"><dt class="text-gray-400 w-28 flex-shrink-0">Location</dt><dd class="text-gray-300">{{ booking.location }}</dd></div>
                    <div v-if="booking.scheduled_date" class="flex gap-2"><dt class="text-gray-400 w-28 flex-shrink-0">Scheduled</dt><dd class="text-gray-300">{{ booking.scheduled_date }} {{ booking.scheduled_time }}</dd></div>
                    <div v-if="booking.agreed_price" class="flex gap-2"><dt class="text-gray-400 w-28 flex-shrink-0">Agreed Price</dt><dd class="text-white font-bold">K{{ booking.agreed_price }}</dd></div>
                    <div class="flex gap-2 sm:col-span-2"><dt class="text-gray-400 w-28 flex-shrink-0">Description</dt><dd class="text-gray-300">{{ booking.job_description }}</dd></div>
                    <div v-if="booking.rejection_reason" class="flex gap-2 sm:col-span-2"><dt class="text-gray-400 w-28 flex-shrink-0">Rejection</dt><dd class="text-red-400">{{ booking.rejection_reason }}</dd></div>
                    <div v-if="booking.worker_notes" class="flex gap-2 sm:col-span-2"><dt class="text-gray-400 w-28 flex-shrink-0">Worker notes</dt><dd class="text-gray-300">{{ booking.worker_notes }}</dd></div>
                </dl>

                <!-- Actions -->
                <div class="mt-5 flex gap-3">
                    <button v-if="['pending','accepted'].includes(booking.status)" @click="cancel"
                        class="border border-red-500/50 text-red-400 hover:bg-red-500/10 px-4 py-2 rounded-lg text-sm transition-colors">
                        Cancel Booking
                    </button>
                    <button v-if="booking.status === 'completed' && !booking.review" @click="showReview = true"
                        class="bg-brand-red hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                        Leave a Review
                    </button>
                </div>
            </div>

            <!-- Existing review -->
            <div v-if="booking.review" class="bg-gray-800 rounded-xl border border-gray-700 p-5">
                <h2 class="text-white font-semibold mb-3">Your Review</h2>
                <div class="flex gap-0.5 mb-2">
                    <StarIcon v-for="i in 5" :key="i" :class="['h-5 w-5', booking.review.rating >= i ? 'text-yellow-400' : 'text-gray-600']" />
                </div>
                <p v-if="booking.review.comment" class="text-gray-300 text-sm">{{ booking.review.comment }}</p>
                <div v-if="booking.review.worker_reply" class="mt-3 pl-3 border-l-2 border-brand-red">
                    <p class="text-xs text-gray-400 mb-0.5">Worker reply:</p>
                    <p class="text-gray-300 text-sm">{{ booking.review.worker_reply }}</p>
                </div>
            </div>

            <!-- Review form -->
            <div v-if="showReview" class="bg-gray-800 rounded-xl border border-brand-red p-5">
                <h2 class="text-white font-semibold mb-4">Leave a Review</h2>
                <form @submit.prevent="submitReview" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-300 mb-2">Rating</label>
                        <div class="flex gap-1">
                            <button v-for="i in 5" :key="i" type="button" @click="reviewForm.rating = i">
                                <StarIcon :class="['h-7 w-7 transition-colors', reviewForm.rating >= i ? 'text-yellow-400' : 'text-gray-600']" />
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Comment (optional)</label>
                        <textarea v-model="reviewForm.comment" rows="3"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red"></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" :disabled="reviewForm.processing" class="bg-brand-red hover:bg-red-700 disabled:opacity-50 text-white px-5 py-2 rounded-lg text-sm">Submit</button>
                        <button type="button" @click="showReview = false" class="text-gray-400 hover:text-white px-4 py-2 text-sm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
