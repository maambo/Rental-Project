<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { MagnifyingGlassIcon, StarIcon, MapPinIcon } from '@heroicons/vue/24/outline';
import { StarIcon as StarSolid } from '@heroicons/vue/24/solid';

interface Category { id: number; name: string; icon: string; }
interface Town      { id: number; name: string; }
interface Worker {
    id: number;
    tagline: string;
    experience_years: number;
    rating_average: string;
    rating_count: number;
    profile_photo_url: string | null;
    service_radius_km: number | null;
    is_featured: boolean;
    user: { name: string };
    category: { name: string; icon: string };
    town: { name: string } | null;
}

const props = defineProps<{
    workers:    { data: Worker[]; links: any[]; meta: any };
    categories: Category[];
    towns:      Town[];
    filters:    { category?: string; town?: string; search?: string };
}>();

const search   = ref(props.filters.search ?? '');
const category = ref(props.filters.category ?? '');
const town     = ref(props.filters.town ?? '');

const applyFilters = () => {
    router.get(route('marketplace.workers.index'), {
        search:   search.value || undefined,
        category: category.value || undefined,
        town:     town.value || undefined,
    }, { preserveState: true, replace: true });
};

const starFill = (rating: string, pos: number) => parseFloat(rating) >= pos ? 'text-yellow-400' : 'text-gray-600';

const photoUrl = (url: string | null) =>
    url ? `/storage/${url}` : 'https://ui-avatars.com/api/?name=Worker&background=374151&color=fff&size=128';
</script>

<template>
    <Head title="Skilled Workers Marketplace" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Skilled Workers Marketplace</h2>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Search bar -->
            <div class="bg-gray-800 rounded-xl p-4 mb-6 flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                    <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Search by skill, name..."
                        class="w-full pl-10 pr-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red" />
                </div>
                <select v-model="category" class="bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red">
                    <option value="">All Categories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.icon }} {{ cat.name }}</option>
                </select>
                <select v-model="town" class="bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red">
                    <option value="">All Towns</option>
                    <option v-for="t in towns" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
                <button @click="applyFilters" class="bg-brand-red hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                    Search
                </button>
            </div>

            <!-- Workers grid -->
            <div v-if="workers.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                <Link v-for="w in workers.data" :key="w.id" :href="route('marketplace.workers.show', w.id)"
                    class="bg-gray-800 rounded-xl overflow-hidden border border-gray-700 hover:border-brand-red transition-colors group">
                    <div class="relative">
                        <img :src="photoUrl(w.profile_photo_url)" :alt="w.user.name" class="w-full h-40 object-cover group-hover:brightness-90 transition" />
                        <span v-if="w.is_featured" class="absolute top-2 right-2 bg-yellow-500 text-black text-xs font-bold px-2 py-0.5 rounded-full">FEATURED</span>
                    </div>
                    <div class="p-4">
                        <p class="text-xs text-brand-red font-medium mb-0.5">{{ w.category.icon }} {{ w.category.name }}</p>
                        <h3 class="text-white font-semibold text-sm leading-snug">{{ w.user.name }}</h3>
                        <p class="text-gray-400 text-xs mt-0.5 truncate">{{ w.tagline }}</p>
                        <div class="flex items-center gap-1 mt-2">
                            <StarSolid v-for="i in 5" :key="i" :class="['h-3.5 w-3.5', starFill(w.rating_average, i)]" />
                            <span class="text-gray-400 text-xs ml-1">({{ w.rating_count }})</span>
                        </div>
                        <div v-if="w.town" class="flex items-center gap-1 mt-2 text-gray-400 text-xs">
                            <MapPinIcon class="h-3.5 w-3.5" />{{ w.town.name }}
                        </div>
                        <p class="text-gray-500 text-xs mt-1">{{ w.experience_years }} yrs experience</p>
                    </div>
                </Link>
            </div>

            <div v-else class="text-center text-gray-400 py-20">
                <p class="text-lg">No workers found matching your search.</p>
                <p class="text-sm mt-1">Try adjusting your filters.</p>
            </div>

            <!-- Pagination -->
            <div v-if="workers.links.length > 3" class="flex justify-center gap-1 mt-8">
                <Link v-for="link in workers.links" :key="link.label"
                    :href="link.url ?? '#'"
                    :class="['px-3 py-1.5 rounded text-sm', link.active ? 'bg-brand-red text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700', !link.url ? 'opacity-40 pointer-events-none' : '']"
                    v-html="link.label" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
