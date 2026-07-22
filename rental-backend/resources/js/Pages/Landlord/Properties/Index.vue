<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { PlusIcon, BuildingOfficeIcon, PencilIcon, TrashIcon, EyeIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{ properties: any[] }>();

const approvalBadge: Record<string, string> = {
    approved: 'bg-green-500/20 text-green-400 ring-1 ring-green-500/30',
    pending:  'bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30',
    rejected: 'bg-red-500/20 text-red-400 ring-1 ring-red-500/30',
};

const availabilityConfig: Record<string, { label: string; class: string }> = {
    available: { label: 'Available',  class: 'bg-green-500/20 text-green-400 ring-1 ring-green-500/30' },
    rented:    { label: 'Rented',     class: 'bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30' },
    sold:      { label: 'Sold',       class: 'bg-gray-500/20 text-gray-400 ring-1 ring-gray-500/30' },
};

const deleteProperty = (id: number) => {
    if (confirm('Delete this property? This cannot be undone.')) {
        router.delete(route('landlord.properties.destroy', id));
    }
};

const updateAvailability = (id: number, status: string) => {
    router.patch(route('landlord.properties.availability.update', id), { availability_status: status }, { preserveScroll: true });
};
</script>

<template>
    <Head title="My Properties" />

    <AuthenticatedLayout header="My Properties">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Header row -->
                <div class="flex items-center justify-between mb-6">
                    <p class="text-sm text-gray-400">{{ properties.length }} propert{{ properties.length !== 1 ? 'ies' : 'y' }}</p>
                    <Link :href="route('landlord.properties.create')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-brand-red text-white rounded-lg hover:bg-red-700 text-sm font-medium transition">
                        <PlusIcon class="w-4 h-4" /> Add Property
                    </Link>
                </div>

                <!-- Empty state -->
                <div v-if="properties.length === 0" class="bg-light-bg rounded-xl py-16 text-center">
                    <BuildingOfficeIcon class="w-14 h-14 mx-auto text-gray-600 mb-3" />
                    <h3 class="text-base font-semibold text-white mb-1">No Properties Yet</h3>
                    <p class="text-sm text-gray-400 mb-6">Start by adding your first property listing.</p>
                    <Link :href="route('landlord.properties.create')"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-red text-white rounded-lg hover:bg-red-700 text-sm font-medium transition">
                        <PlusIcon class="w-4 h-4" /> Add Property
                    </Link>
                </div>

                <!-- Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="property in properties" :key="property.id"
                        class="bg-light-bg rounded-xl overflow-hidden flex flex-col">

                        <!-- Image -->
                        <div class="relative h-44 bg-dark-bg/60 flex-shrink-0">
                            <img
                                v-if="property.images?.length"
                                :src="`/storage/${(property.images.find((i: any) => i.is_primary) ?? property.images[0]).image_url}`"
                                :alt="property.title"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="flex items-center justify-center h-full">
                                <BuildingOfficeIcon class="w-12 h-12 text-gray-600" />
                            </div>

                            <!-- Approval badge -->
                            <span :class="['absolute top-2 left-2 px-2 py-0.5 rounded-full text-xs font-semibold capitalize', approvalBadge[property.approval_status] ?? 'bg-gray-500/20 text-gray-400']">
                                {{ property.approval_status }}
                            </span>

                            <!-- Availability badge -->
                            <span :class="['absolute top-2 right-2 px-2 py-0.5 rounded-full text-xs font-semibold', availabilityConfig[property.availability_status]?.class ?? '']">
                                {{ availabilityConfig[property.availability_status]?.label ?? property.availability_status }}
                            </span>
                        </div>

                        <!-- Body -->
                        <div class="p-4 flex flex-col flex-1">
                            <h3 class="text-sm font-semibold text-white leading-snug mb-0.5 line-clamp-2">{{ property.title }}</h3>
                            <p class="text-xs text-gray-400 mb-1 capitalize">
                                {{ property.property_subtype?.replace('_', ' ') }} · For {{ property.listing_type }}
                            </p>
                            <p class="text-base font-bold text-brand-red mb-3">
                                K{{ Number(property.price).toLocaleString() }}
                                <span class="text-xs font-normal text-gray-500">{{ property.listing_type === 'rent' ? '/mo' : '' }}</span>
                            </p>

                            <!-- Availability selector — only for approved properties -->
                            <div v-if="property.approval_status === 'approved'" class="mb-3">
                                <label class="text-xs text-gray-500 mb-1 block">Availability</label>
                                <select
                                    :value="property.availability_status"
                                    @change="updateAvailability(property.id, ($event.target as HTMLSelectElement).value)"
                                    class="w-full text-xs rounded-md border border-gray-700 bg-gray-900 text-gray-200 py-1.5 px-2 focus:ring-1 focus:ring-brand-red focus:border-brand-red"
                                >
                                    <option value="available">Available</option>
                                    <option value="rented">Rented</option>
                                    <option value="sold">Sold</option>
                                </select>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 mt-auto">
                                <Link :href="route('properties.show', property.id)"
                                    class="flex-1 flex items-center justify-center gap-1.5 px-3 py-1.5 bg-dark-bg/60 hover:bg-dark-bg text-gray-300 hover:text-white rounded-md text-xs font-medium transition">
                                    <EyeIcon class="w-3.5 h-3.5" /> View
                                </Link>
                                <Link :href="route('landlord.properties.edit', property.id)"
                                    class="flex-1 flex items-center justify-center gap-1.5 px-3 py-1.5 bg-dark-bg/60 hover:bg-dark-bg text-gray-300 hover:text-white rounded-md text-xs font-medium transition">
                                    <PencilIcon class="w-3.5 h-3.5" /> Edit
                                </Link>
                                <button @click="deleteProperty(property.id)"
                                    class="px-2.5 py-1.5 bg-red-600/20 hover:bg-red-600/40 text-red-400 rounded-md transition">
                                    <TrashIcon class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
