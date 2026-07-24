<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, ref } from 'vue';
import { MapPinIcon, PhoneIcon, StarIcon as StarOutline, ShieldCheckIcon, BriefcaseIcon, PhotoIcon } from '@heroicons/vue/24/outline';
import { StarIcon } from '@heroicons/vue/24/solid';

interface Service { id: number; service_name: string; rate_type: string; base_rate: string; minimum_charge: string | null; description: string | null; }
interface Review  { id: number; rating: number; comment: string | null; worker_reply: string | null; client: { name: string }; created_at: string; }
interface Photo   { id: number; image_url: string; caption: string | null; }

interface Worker {
    id: number; tagline: string; bio: string; experience_years: number;
    rating_average: string; rating_count: number; profile_photo_url: string | null;
    service_radius_km: number | null; phone: string; is_verified: boolean; is_featured: boolean;
    user: { name: string; email: string };
    category: { name: string; icon: string };
    town: { name: string } | null;
    services: Service[];
    portfolio_photos: Photo[];
    reviews: Review[];
}

const props = defineProps<{
    worker:   Worker;
    services: Service[];
}>();

const tab = ref<'about' | 'services' | 'portfolio' | 'reviews'>('about');

const photoUrl = (url: string | null) =>
    url ? `/storage/${url}` : 'https://ui-avatars.com/api/?name=Worker&background=374151&color=fff&size=256';

const starFill = (rating: number, pos: number) => rating >= pos ? 'text-yellow-400' : 'text-gray-600';

const rateLabel = (type: string) => ({ hourly: '/hr', per_job: '/job', per_day: '/day' }[type] ?? '');
</script>

<template>
    <Head :title="worker.user.name + ' — Skilled Worker'" />

    <AuthenticatedLayout>
        <template #header>
            <nav class="flex text-sm text-gray-400 gap-2">
                <Link :href="route('marketplace.workers.index')" class="hover:text-white">Workers</Link>
                <span>/</span>
                <span class="text-white">{{ worker.user.name }}</span>
            </nav>
        </template>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Hero card -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden mb-6">
                <div class="flex flex-col sm:flex-row items-start gap-6 p-6">
                    <img :src="photoUrl(worker.profile_photo_url)" :alt="worker.user.name" class="w-32 h-32 rounded-full object-cover shrink-0" />
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h1 class="text-2xl font-bold text-white">{{ worker.user.name }}</h1>
                            <span v-if="worker.is_verified" class="flex items-center gap-1 text-xs bg-green-500/20 text-green-400 ring-1 ring-green-500/30 px-2 py-0.5 rounded-full">
                                <ShieldCheckIcon class="h-3.5 w-3.5" /> Verified
                            </span>
                            <span v-if="worker.is_featured" class="text-xs bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-400/30 px-2 py-0.5 rounded-full">Featured</span>
                        </div>
                        <p class="text-brand-red text-sm font-medium">{{ worker.category.icon }} {{ worker.category.name }}</p>
                        <p class="text-gray-300 mt-1">{{ worker.tagline }}</p>
                        <div class="flex items-center gap-3 mt-3 flex-wrap text-sm text-gray-400">
                            <span class="flex items-center gap-1">
                                <StarIcon v-for="i in 5" :key="i" :class="['h-4 w-4', starFill(parseFloat(worker.rating_average), i)]" />
                                {{ worker.rating_average }} ({{ worker.rating_count }} reviews)
                            </span>
                            <span v-if="worker.town" class="flex items-center gap-1"><MapPinIcon class="h-4 w-4" />{{ worker.town.name }}</span>
                            <span class="flex items-center gap-1"><BriefcaseIcon class="h-4 w-4" />{{ worker.experience_years }} yrs</span>
                        </div>
                    </div>
                    <div class="shrink-0">
                        <Link :href="route('marketplace.workers.book', worker.id)"
                            class="inline-block bg-brand-red hover:bg-red-700 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors text-sm">
                            Book Now
                        </Link>
                        <p class="text-gray-400 text-xs mt-2 flex items-center gap-1"><PhoneIcon class="h-3.5 w-3.5" />{{ worker.phone }}</p>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="border-t border-gray-700 flex gap-0">
                    <button v-for="t in (['about','services','portfolio','reviews'] as const)" :key="t"
                        @click="tab = t"
                        :class="['px-5 py-3 text-sm font-medium capitalize transition-colors border-b-2', tab === t ? 'border-brand-red text-white' : 'border-transparent text-gray-400 hover:text-white']">
                        {{ t }} <span v-if="t === 'reviews'" class="text-xs opacity-60">({{ worker.rating_count }})</span>
                    </button>
                </div>
            </div>

            <!-- About -->
            <div v-if="tab === 'about'" class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h2 class="text-white font-semibold mb-3">About</h2>
                <p class="text-gray-300 whitespace-pre-wrap">{{ worker.bio }}</p>
            </div>

            <!-- Services -->
            <div v-else-if="tab === 'services'" class="space-y-3">
                <div v-if="services.length === 0" class="text-gray-400 text-center py-10">No services listed yet.</div>
                <div v-for="s in services" :key="s.id" class="bg-gray-800 rounded-xl border border-gray-700 p-4 flex justify-between items-start">
                    <div>
                        <p class="text-white font-medium">{{ s.service_name }}</p>
                        <p v-if="s.description" class="text-gray-400 text-sm mt-0.5">{{ s.description }}</p>
                        <p v-if="s.minimum_charge" class="text-gray-500 text-xs mt-1">Min charge: K{{ s.minimum_charge }}</p>
                    </div>
                    <div class="text-right shrink-0 ml-4">
                        <span class="text-white font-bold">K{{ s.base_rate }}</span>
                        <span class="text-gray-400 text-sm">{{ rateLabel(s.rate_type) }}</span>
                    </div>
                </div>
            </div>

            <!-- Portfolio -->
            <div v-else-if="tab === 'portfolio'">
                <div v-if="worker.portfolio_photos.length === 0" class="text-gray-400 text-center py-10">No portfolio photos yet.</div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    <div v-for="p in worker.portfolio_photos" :key="p.id" class="relative group rounded-lg overflow-hidden aspect-square bg-gray-700">
                        <img :src="`/storage/${p.image_url}`" :alt="p.caption ?? ''" class="w-full h-full object-cover group-hover:brightness-75 transition" />
                        <p v-if="p.caption" class="absolute bottom-0 left-0 right-0 p-2 text-xs text-white bg-gradient-to-t from-black/80 opacity-0 group-hover:opacity-100 transition">{{ p.caption }}</p>
                    </div>
                </div>
            </div>

            <!-- Reviews -->
            <div v-else-if="tab === 'reviews'" class="space-y-4">
                <div v-if="worker.reviews.length === 0" class="text-gray-400 text-center py-10">No reviews yet.</div>
                <div v-for="r in worker.reviews" :key="r.id" class="bg-gray-800 rounded-xl border border-gray-700 p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="text-white font-medium text-sm">{{ r.client.name }}</p>
                            <p class="text-gray-500 text-xs">{{ new Date(r.created_at).toLocaleDateString() }}</p>
                        </div>
                        <div class="flex items-center gap-0.5">
                            <StarIcon v-for="i in 5" :key="i" :class="['h-4 w-4', starFill(r.rating, i)]" />
                        </div>
                    </div>
                    <p v-if="r.comment" class="text-gray-300 text-sm">{{ r.comment }}</p>
                    <div v-if="r.worker_reply" class="mt-3 pl-3 border-l-2 border-brand-red">
                        <p class="text-xs text-gray-400 mb-0.5">Worker reply:</p>
                        <p class="text-gray-300 text-sm">{{ r.worker_reply }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
