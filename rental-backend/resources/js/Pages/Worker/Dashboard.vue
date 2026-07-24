<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { StarIcon } from '@heroicons/vue/24/solid';
import { BriefcaseIcon, CalendarDaysIcon, CheckCircleIcon, ClockIcon, PhotoIcon, StarIcon as StarOutline } from '@heroicons/vue/24/outline';

interface Profile {
    id: number; tagline: string; is_verified: boolean; is_active: boolean; is_featured: boolean;
    rating_average: string; rating_count: number; profile_photo_url: string | null;
    category: { name: string; icon: string };
    town: { name: string } | null;
    all_services: any[];
}
interface Stats {
    pending_bookings: number; active_bookings: number; completed_bookings: number;
    total_reviews: number; average_rating: string; portfolio_photos: number;
}
interface Booking {
    id: number; job_description: string; status: string; created_at: string;
    client: { name: string };
    worker_service: { service_name: string } | null;
}

const props = defineProps<{
    profile:        Profile;
    stats:          Stats;
    recentBookings: Booking[];
}>();

const statusClass = (s: string) => ({
    pending:     'bg-yellow-500/20 text-yellow-400',
    accepted:    'bg-blue-500/20 text-blue-400',
    in_progress: 'bg-purple-500/20 text-purple-400',
    completed:   'bg-green-500/20 text-green-400',
    rejected:    'bg-red-500/20 text-red-400',
    cancelled:   'bg-gray-500/20 text-gray-400',
}[s] ?? '');

const photoUrl = (url: string | null) =>
    url ? `/storage/${url}` : 'https://ui-avatars.com/api/?name=Worker&background=374151&color=fff&size=128';
</script>

<template>
    <Head title="Worker Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Worker Dashboard</h2>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Profile card -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 flex flex-col sm:flex-row items-start gap-5">
                <img :src="photoUrl(profile.profile_photo_url)" :alt="profile.tagline" class="w-20 h-20 rounded-full object-cover shrink-0" />
                <div class="flex-1">
                    <div class="flex flex-wrap gap-2 items-center mb-1">
                        <span class="text-brand-red font-medium text-sm">{{ profile.category.icon }} {{ profile.category.name }}</span>
                        <span v-if="profile.is_verified" class="text-xs bg-green-500/20 text-green-400 ring-1 ring-green-500/30 px-2 py-0.5 rounded-full">Verified</span>
                        <span v-if="!profile.is_active" class="text-xs bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30 px-2 py-0.5 rounded-full">Inactive</span>
                    </div>
                    <p class="text-gray-300">{{ profile.tagline }}</p>
                    <div class="flex items-center gap-1 mt-2">
                        <StarIcon v-for="i in 5" :key="i" :class="['h-4 w-4', parseFloat(stats.average_rating) >= i ? 'text-yellow-400' : 'text-gray-600']" />
                        <span class="text-gray-400 text-sm ml-1">{{ stats.average_rating }} ({{ stats.total_reviews }} reviews)</span>
                    </div>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <Link :href="route('worker.profile.edit')" class="border border-gray-600 text-gray-300 hover:bg-gray-700 px-4 py-2 rounded-lg text-sm transition-colors">Edit Profile</Link>
                    <Link :href="route('marketplace.workers.show', profile.id)" class="border border-brand-red text-brand-red hover:bg-brand-red hover:text-white px-4 py-2 rounded-lg text-sm transition-colors">View Public Page</Link>
                </div>
            </div>

            <!-- Stats grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 text-center">
                    <p class="text-2xl font-bold text-yellow-400">{{ stats.pending_bookings }}</p>
                    <p class="text-gray-400 text-xs mt-1">Pending</p>
                </div>
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 text-center">
                    <p class="text-2xl font-bold text-blue-400">{{ stats.active_bookings }}</p>
                    <p class="text-gray-400 text-xs mt-1">Active</p>
                </div>
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 text-center">
                    <p class="text-2xl font-bold text-green-400">{{ stats.completed_bookings }}</p>
                    <p class="text-gray-400 text-xs mt-1">Completed</p>
                </div>
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 text-center">
                    <p class="text-2xl font-bold text-white">{{ stats.total_reviews }}</p>
                    <p class="text-gray-400 text-xs mt-1">Reviews</p>
                </div>
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 text-center">
                    <p class="text-2xl font-bold text-brand-red">{{ profile.all_services.length }}</p>
                    <p class="text-gray-400 text-xs mt-1">Services</p>
                </div>
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 text-center">
                    <p class="text-2xl font-bold text-purple-400">{{ stats.portfolio_photos }}</p>
                    <p class="text-gray-400 text-xs mt-1">Photos</p>
                </div>
            </div>

            <!-- Quick links -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <Link :href="route('worker.bookings.index')" class="bg-gray-800 border border-gray-700 hover:border-brand-red rounded-xl p-4 flex flex-col items-center gap-2 text-center transition-colors group">
                    <CalendarDaysIcon class="h-6 w-6 text-gray-400 group-hover:text-brand-red" />
                    <span class="text-sm text-gray-300">Bookings</span>
                </Link>
                <Link :href="route('worker.services.index')" class="bg-gray-800 border border-gray-700 hover:border-brand-red rounded-xl p-4 flex flex-col items-center gap-2 text-center transition-colors group">
                    <BriefcaseIcon class="h-6 w-6 text-gray-400 group-hover:text-brand-red" />
                    <span class="text-sm text-gray-300">Services</span>
                </Link>
                <Link :href="route('worker.portfolio.index')" class="bg-gray-800 border border-gray-700 hover:border-brand-red rounded-xl p-4 flex flex-col items-center gap-2 text-center transition-colors group">
                    <PhotoIcon class="h-6 w-6 text-gray-400 group-hover:text-brand-red" />
                    <span class="text-sm text-gray-300">Portfolio</span>
                </Link>
                <Link :href="route('worker.profile.edit')" class="bg-gray-800 border border-gray-700 hover:border-brand-red rounded-xl p-4 flex flex-col items-center gap-2 text-center transition-colors group">
                    <StarOutline class="h-6 w-6 text-gray-400 group-hover:text-brand-red" />
                    <span class="text-sm text-gray-300">Edit Profile</span>
                </Link>
            </div>

            <!-- Recent bookings -->
            <div class="bg-gray-800 rounded-xl border border-gray-700">
                <div class="px-5 py-4 border-b border-gray-700 flex justify-between items-center">
                    <h3 class="text-white font-semibold">Recent Bookings</h3>
                    <Link :href="route('worker.bookings.index')" class="text-brand-red hover:text-red-400 text-sm">View all →</Link>
                </div>
                <div v-if="recentBookings.length === 0" class="text-gray-400 text-sm text-center py-8">No bookings yet.</div>
                <div v-else class="divide-y divide-gray-700">
                    <Link v-for="b in recentBookings" :key="b.id"
                        :href="route('worker.bookings.show', b.id)"
                        class="flex justify-between items-center px-5 py-3 hover:bg-gray-700/50 transition-colors">
                        <div class="min-w-0">
                            <p class="text-white text-sm font-medium truncate">{{ b.client.name }}</p>
                            <p class="text-gray-400 text-xs truncate">{{ b.job_description }}</p>
                        </div>
                        <span :class="['ml-3 shrink-0 text-xs px-2 py-0.5 rounded-full capitalize', statusClass(b.status)]">{{ b.status.replace('_', ' ') }}</span>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
