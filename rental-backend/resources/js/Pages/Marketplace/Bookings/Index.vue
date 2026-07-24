<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Booking {
    id: number;
    job_description: string;
    status: string;
    scheduled_date: string | null;
    agreed_price: string | null;
    created_at: string;
    worker_profile: { id: number; user: { name: string }; category: { name: string; icon: string } };
    worker_service: { service_name: string } | null;
}

defineProps<{
    bookings: { data: Booking[]; links: any[] };
}>();

const statusClass = (s: string) => ({
    pending:     'bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30',
    accepted:    'bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30',
    rejected:    'bg-red-500/20 text-red-400 ring-1 ring-red-500/30',
    in_progress: 'bg-purple-500/20 text-purple-400 ring-1 ring-purple-500/30',
    completed:   'bg-green-500/20 text-green-400 ring-1 ring-green-500/30',
    cancelled:   'bg-gray-500/20 text-gray-400 ring-1 ring-gray-500/30',
    disputed:    'bg-orange-500/20 text-orange-400 ring-1 ring-orange-500/30',
}[s] ?? '');
</script>

<template>
    <Head title="My Bookings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">My Bookings</h2>
        </template>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center mb-6">
                <p class="text-gray-400 text-sm">Bookings you've made with skilled workers.</p>
                <Link :href="route('marketplace.workers.index')" class="bg-brand-red hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">Find a Worker</Link>
            </div>

            <div v-if="bookings.data.length === 0" class="text-center text-gray-400 py-20">
                <p class="text-lg">You have no bookings yet.</p>
            </div>

            <div class="space-y-3">
                <Link v-for="b in bookings.data" :key="b.id"
                    :href="route('marketplace.bookings.show', b.id)"
                    class="block bg-gray-800 rounded-xl border border-gray-700 hover:border-brand-red p-4 transition-colors">
                    <div class="flex justify-between items-start gap-4">
                        <div class="min-w-0">
                            <p class="text-white font-medium truncate">{{ b.worker_profile.user.name }}</p>
                            <p class="text-gray-400 text-xs">{{ b.worker_profile.category.icon }} {{ b.worker_profile.category.name }}</p>
                            <p class="text-gray-300 text-sm mt-1 line-clamp-1">{{ b.job_description }}</p>
                            <p v-if="b.scheduled_date" class="text-gray-500 text-xs mt-0.5">Scheduled: {{ b.scheduled_date }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span :class="['text-xs px-2 py-0.5 rounded-full capitalize', statusClass(b.status)]">{{ b.status.replace('_', ' ') }}</span>
                            <p v-if="b.agreed_price" class="text-white font-semibold mt-1">K{{ b.agreed_price }}</p>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="bookings.links.length > 3" class="flex justify-center gap-1 mt-8">
                <Link v-for="link in bookings.links" :key="link.label"
                    :href="link.url ?? '#'"
                    :class="['px-3 py-1.5 rounded text-sm', link.active ? 'bg-brand-red text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700', !link.url ? 'opacity-40 pointer-events-none' : '']"
                    v-html="link.label" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
