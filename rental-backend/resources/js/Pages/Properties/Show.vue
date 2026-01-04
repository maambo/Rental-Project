<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; 
import { MapPinIcon, HomeIcon, ShareIcon, HeartIcon } from '@heroicons/vue/24/outline';
import { HeartIcon as HeartIconSolid, StarIcon } from '@heroicons/vue/24/solid';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps<{
    property: any;
}>();

const page = usePage();
const user = computed(() => page.props.auth.user);
const isOwner = computed(() => user.value && user.value.id === props.property.landlord_id);

const currentImageIndex = ref(0);
const isLiked = ref(false); 

const images = computed(() => props.property.images || []);
const primaryImage = computed(() => images.value.length > 0 ? '/storage/' + images.value[currentImageIndex.value].image_url : null);

const nextImage = () => {
    if (images.value.length > 0) {
        currentImageIndex.value = (currentImageIndex.value + 1) % images.value.length;
    }
};

const prevImage = () => {
    if (images.value.length > 0) {
        currentImageIndex.value = (currentImageIndex.value - 1 + images.value.length) % images.value.length;
    }
};

const toggleLike = () => {
    if (!user.value) {
        alert('Please login to like properties.');
        return;
    }
    isLiked.value = !isLiked.value;
};

const applyNow = () => {
    if (!user.value) {
        window.location.href = route('login');
        return;
    }
    if (isOwner.value) {
        alert('You cannot apply to your own property.');
        return;
    }
    window.location.href = route('properties.apply', props.property.id);
};

// Tour Request Logic
const showTourModal = ref(false);
const tourForm = useForm({
    scheduled_at: '',
    notes: '',
});

const scheduleTour = () => {
    if (!user.value) {
        window.location.href = route('login');
        return;
    }
    if (isOwner.value) {
        alert('You cannot schedule a tour for your own property.');
        return;
    }
    showTourModal.value = true;
};

const closeTourModal = () => {
    showTourModal.value = false;
    tourForm.reset();
};

const submitTourRequest = () => {
    tourForm.post(route('properties.tour.store', props.property.id), {
        onSuccess: () => {
            closeTourModal();
            // Optional: Show a toast here if not handled globally
        },
    });
};

// Review Logic
const reviewForm = useForm({
    rating: 5,
    comment: '',
});

const submitReview = () => {
    reviewForm.post(route('properties.reviews.store', props.property.id), {
        onSuccess: () => {
            reviewForm.reset();
        },
    });
};


</script>

<template>
    <Head :title="property.title" />

    <!-- Use AuthenticatedLayout when logged in -->
    <AuthenticatedLayout v-if="user">
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div class="relative bg-black rounded-2xl overflow-hidden aspect-video group">
                        <img 
                            v-if="primaryImage" 
                            :src="primaryImage" 
                            class="w-full h-full object-cover" 
                            alt="Property Image"
                        >
                         <div v-else class="w-full h-full flex items-center justify-center bg-gray-800 text-white">
                            No Images Available
                        </div>
                         <button 
                            v-if="images.length > 1" 
                            @click.stop="prevImage"
                            class="absolute left-4 top-1/2 -translate-y-1/2 bg-black/50 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition"
                        >
                            &larr;
                        </button>
                         <button 
                            v-if="images.length > 1" 
                            @click.stop="nextImage"
                            class="absolute right-4 top-1/2 -translate-y-1/2 bg-black/50 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition"
                        >
                            &rarr;
                        </button>
                        <div v-if="images.length > 0" class="absolute bottom-4 right-4 bg-black/60 text-white px-3 py-1 rounded-full text-sm">
                            {{ currentImageIndex + 1 }} / {{ images.length }}
                        </div>
                    </div>

                     <div class="bg-white dark:bg-light-bg rounded-2xl p-6 shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ property.title }}</h1>
                                <div class="flex items-center gap-2 mt-2 text-gray-500 dark:text-gray-400">
                                    <MapPinIcon class="h-5 w-5" />
                                    <span>{{ property.location }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold text-brand-red">K{{ property.price.toLocaleString() }}<span class="text-lg text-gray-500 font-normal">/mo</span></div>
                                <div class="flex items-center justify-end gap-1 mt-1">
                                    <StarIcon class="h-5 w-5 text-amber-500" />
                                    <span class="font-bold text-gray-900 dark:text-white">{{ parseFloat(property.average_rating).toFixed(1) }}</span>
                                    <span class="text-gray-500">({{ property.reviews_count }} reviews)</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mt-8 pt-8 border-t border-gray-100 dark:border-gray-700">
                             <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ property.bedrooms }}</div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Bedrooms</div>
                            </div>
                             <div class="text-center border-l border-r border-gray-100 dark:border-gray-700">
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ property.bathrooms }}</div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Bathrooms</div>
                            </div>
                             <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ property.square_feet || property.sqft }}</div>
                                <div class="text-xs uppercase tracking-wide text-gray-500">Sq Ft</div>
                            </div>
                        </div>
                     </div>

                     <div class="bg-white dark:bg-light-bg rounded-2xl p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Description</h2>
                        <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 whitespace-pre-line">
                            {{ property.description }}
                        </div>
                     </div>

                     <div class="bg-white dark:bg-light-bg rounded-2xl p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Location</h2>
                        <div class="h-64 bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center">
                            <span class="text-gray-500">Map View Coming Soon</span>
                        </div>
                     </div>

                     <!-- Reviews Section -->
                     <div class="bg-white dark:bg-light-bg rounded-2xl p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Reviews ({{ property.reviews_count }})</h2>
                        
                        <!-- Review Form -->
                        <div v-if="user && !isOwner" class="mb-8 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                            <h3 class="text-md font-semibold mb-3 dark:text-gray-200">Write a Review</h3>
                            <form @submit.prevent="submitReview">
                                <div class="flex items-center gap-2 mb-3">
                                    <template v-for="star in 5" :key="star">
                                        <StarIcon 
                                            @click="reviewForm.rating = star"
                                            class="h-6 w-6 cursor-pointer hover:scale-110 transition" 
                                            :class="star <= reviewForm.rating ? 'text-amber-500 fill-current' : 'text-gray-300'" 
                                        />
                                    </template>
                                </div>
                                <textarea 
                                    v-model="reviewForm.comment" 
                                    rows="3" 
                                    class="w-full rounded-lg border-gray-300 focus:border-brand-red focus:ring-brand-red dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="Share your experience..."
                                    required
                                ></textarea>
                                <div class="mt-2 text-right">
                                    <PrimaryButton :disabled="reviewForm.processing">Post Review</PrimaryButton>
                                </div>
                            </form>
                        </div>
                        <div v-else-if="isOwner" class="mb-8 text-center py-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                            <p class="text-gray-600 dark:text-gray-400">You cannot review your own property.</p>
                        </div>
                        <div v-else class="mb-8 text-center py-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                            <Link :href="route('login')" class="text-brand-red font-semibold hover:underline">Log in</Link> to write a review.
                        </div>

                        <!-- Review List -->
                        <div class="space-y-6">
                            <div v-for="review in property.reviews" :key="review.id" class="border-b border-gray-100 dark:border-gray-700 last:border-0 pb-6 last:pb-0">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-brand-red/10 flex items-center justify-center text-brand-red font-bold text-xs">
                                            {{ review.user.name.charAt(0) }}
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ review.user.name }}</span>
                                    </div>
                                    <div class="flex items-center text-amber-500 text-sm">
                                        <StarIcon class="h-4 w-4 fill-current" />
                                        <span class="ml-1 font-bold">{{ review.rating }}</span>
                                    </div>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">{{ review.comment }}</p>
                                <div class="mt-2 text-xs text-gray-400">
                                    {{ new Date(review.created_at).toLocaleDateString() }}
                                </div>
                            </div>
                            <div v-if="property.reviews.length === 0" class="text-center text-gray-500 py-4">
                                No reviews yet. Be the first to review!
                            </div>
                        </div>
                     </div>

                </div>

                <div class="lg:col-span-1">
                    <div class="sticky top-24 space-y-6">
                        <div class="bg-white dark:bg-light-bg rounded-2xl p-6 shadow-lg border border-gray-100 dark:border-gray-700">
                             <div class="flex items-center justify-between mb-6">
                                <button @click="toggleLike" class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-brand-red transition">
                                    <component :is="isLiked ? HeartIconSolid : HeartIcon" class="h-6 w-6" :class="{'text-brand-red': isLiked}" />
                                    {{ isLiked ? 'Saved' : 'Save' }}
                                </button>
                                <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-brand-red transition">
                                    <ShareIcon class="h-6 w-6" />
                                    Share
                                </button>
                            </div>

                            <div v-if="!isOwner" class="space-y-4">
                                <PrimaryButton @click="scheduleTour" class="w-full justify-center py-3 text-lg">
                                    Schedule Tour
                                </PrimaryButton>
                                <button @click="applyNow" class="w-full flex justify-center py-3 px-4 border border-brand-red text-lg font-medium rounded-md text-brand-red bg-white hover:bg-brand-red/5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-red transition">
                                    Apply Now
                                </button>
                            </div>
                            <div v-else class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                <p class="text-sm text-blue-800 dark:text-blue-200 text-center">
                                    This is your property
                                </p>
                            </div>

                             <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-4">
                                     <div class="h-12 w-12 rounded-full bg-gray-200 overflow-hidden">
                                         <svg class="h-full w-full text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 dark:text-white">{{ property.landlord?.name || 'Landlord' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </AuthenticatedLayout>

    <!-- Use simple layout when not logged in -->
    <div v-else class="min-h-screen bg-gray-50 dark:bg-black">
         <nav class="bg-white dark:bg-light-bg shadow sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link href="/" class="text-brand-red font-bold text-xl">RENTALAPP</Link>
                    </div>
                     <div class="flex items-center gap-4">
                        <Link :href="route('login')" class="text-gray-500 hover:text-gray-700 dark:text-gray-300">Log in</Link>
                        <Link :href="route('register')" class="text-brand-red font-semibold">Register</Link>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Same content here -->
            <p class="text-center text-gray-600 dark:text-gray-400">Please <Link :href="route('login')" class="text-brand-red font-semibold hover:underline">log in</Link> to view property details.</p>
        </main>
    </div>

    <!-- Tour Request Modal -->
    <div v-if="showTourModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeTourModal"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                Schedule a Tour
                            </h3>
                            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Pick a date and time to visit {{ property.title }}.
                            </div>
                            
                            <form @submit.prevent="submitTourRequest" class="mt-4 space-y-4">
                                <div>
                                    <label for="scheduled_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date & Time</label>
                                    <input type="datetime-local" id="scheduled_at" v-model="tourForm.scheduled_at" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                </div>
                                
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes (Optional)</label>
                                    <textarea id="notes" v-model="tourForm.notes" rows="3"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        placeholder="Any specific questions or preferred times?"></textarea>
                                </div>

                                <div class="flex justify-end gap-3 mt-6">
                                    <button type="button" @click="closeTourModal" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                                        Cancel
                                    </button>
                                    <button type="submit" :disabled="tourForm.processing" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-brand-red text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                        Schedule Request
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
