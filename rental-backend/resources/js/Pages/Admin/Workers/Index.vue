<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { ShieldCheckIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

interface Category { id: number; name: string; }
interface Worker {
    id: number; tagline: string; is_verified: boolean; is_active: boolean; is_featured: boolean;
    rating_average: string; rating_count: number; experience_years: number;
    user: { id: number; name: string; email: string };
    category: { name: string; icon: string };
    town: { name: string } | null;
}

const props = defineProps<{
    workers:    { data: Worker[]; links: any[]; meta: any };
    categories: Category[];
    filters:    { search?: string; category?: string; status?: string };
}>();

const search   = ref(props.filters.search ?? '');
const category = ref(props.filters.category ?? '');
const status   = ref(props.filters.status ?? '');

const applyFilters = () => {
    router.get(route('admin.workers.index'), {
        search:   search.value || undefined,
        category: category.value || undefined,
        status:   status.value || undefined,
    }, { preserveState: true, replace: true });
};

const verify = (id: number) => {
    router.post(route('admin.workers.verify', id), {}, { preserveScroll: true });
};

const revoke = (id: number) => {
    if (confirm('Revoke verification for this worker?')) {
        router.post(route('admin.workers.revoke', id), {}, { preserveScroll: true });
    }
};

const toggleFeatured = (id: number) => {
    router.post(route('admin.workers.toggle-featured', id), {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Admin — Workers" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Workers Marketplace</h2>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">
            <!-- Filters -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-48">
                    <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
                    <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Search workers..."
                        class="w-full pl-9 pr-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-red" />
                </div>
                <select v-model="category" class="bg-gray-700 border border-gray-600 rounded-lg text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red">
                    <option value="">All Categories</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <select v-model="status" class="bg-gray-700 border border-gray-600 rounded-lg text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending Verification</option>
                    <option value="verified">Verified</option>
                </select>
                <button @click="applyFilters" class="bg-brand-red hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg transition-colors">Filter</button>
                <Link :href="route('admin.workers.categories.index')" class="border border-gray-600 text-gray-300 hover:bg-gray-700 text-sm px-4 py-2 rounded-lg transition-colors">
                    Manage Categories
                </Link>
            </div>

            <!-- Table -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">Worker</th>
                            <th class="px-4 py-3 font-medium">Category</th>
                            <th class="px-4 py-3 font-medium">Location</th>
                            <th class="px-4 py-3 font-medium">Rating</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-if="workers.data.length === 0">
                            <td colspan="6" class="text-center text-gray-500 py-10">No workers found.</td>
                        </tr>
                        <tr v-for="w in workers.data" :key="w.id" class="hover:bg-gray-700/40">
                            <td class="px-4 py-3">
                                <p class="text-white font-medium">{{ w.user.name }}</p>
                                <p class="text-gray-400 text-xs">{{ w.user.email }}</p>
                                <p class="text-gray-500 text-xs mt-0.5">{{ w.tagline }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-300">{{ w.category.icon }} {{ w.category.name }}</td>
                            <td class="px-4 py-3 text-gray-300">{{ w.town?.name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="text-yellow-400">★</span>
                                <span class="text-white ml-0.5">{{ w.rating_average }}</span>
                                <span class="text-gray-400 text-xs"> ({{ w.rating_count }})</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    <span v-if="w.is_verified" class="inline-flex items-center gap-1 text-xs bg-green-500/20 text-green-400 px-2 py-0.5 rounded-full w-fit">
                                        <ShieldCheckIcon class="h-3 w-3" /> Verified
                                    </span>
                                    <span v-else class="text-xs bg-yellow-500/20 text-yellow-400 px-2 py-0.5 rounded-full w-fit">Pending</span>
                                    <span v-if="w.is_featured" class="text-xs bg-yellow-400/20 text-yellow-300 px-2 py-0.5 rounded-full w-fit">Featured</span>
                                    <span v-if="!w.is_active" class="text-xs bg-gray-500/20 text-gray-400 px-2 py-0.5 rounded-full w-fit">Inactive</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2 justify-end flex-wrap">
                                    <button v-if="!w.is_verified" @click="verify(w.id)"
                                        class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded transition-colors">Verify</button>
                                    <button v-else @click="revoke(w.id)"
                                        class="border border-red-500/50 text-red-400 hover:bg-red-500/10 text-xs px-3 py-1.5 rounded transition-colors">Revoke</button>
                                    <button @click="toggleFeatured(w.id)"
                                        :class="['text-xs px-3 py-1.5 rounded transition-colors border', w.is_featured ? 'border-yellow-500/50 text-yellow-400 hover:bg-yellow-500/10' : 'border-gray-600 text-gray-400 hover:bg-gray-700']">
                                        {{ w.is_featured ? 'Unfeature' : 'Feature' }}
                                    </button>
                                    <Link :href="route('marketplace.workers.show', w.id)"
                                        class="border border-gray-600 text-gray-300 hover:bg-gray-700 text-xs px-3 py-1.5 rounded transition-colors">View</Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="workers.links.length > 3" class="flex justify-center gap-1">
                <Link v-for="link in workers.links" :key="link.label"
                    :href="link.url ?? '#'"
                    :class="['px-3 py-1.5 rounded text-sm', link.active ? 'bg-brand-red text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700', !link.url ? 'opacity-40 pointer-events-none' : '']"
                    v-html="link.label" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
