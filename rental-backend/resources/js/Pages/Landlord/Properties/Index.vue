<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    PlusIcon,
    BuildingOfficeIcon,
    PencilIcon,
    TrashIcon,
    EyeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps<{
    properties: any[];
    tier: string;
}>();

const getStatusBadge = (status: string) => {
    const badges = {
        approved: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return badges[status as keyof typeof badges] || 'bg-gray-100 text-gray-800';
};

const deleteProperty = (id: number) => {
    if (confirm('Are you sure you want to delete this property?')) {
        router.delete(route('landlord.properties.destroy', id));
    }
};
</script>

<template>
    <Head title="My Properties" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">My Properties</h2>
                <Link
                    :href="route('landlord.properties.create')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-brand-red text-white rounded-lg hover:bg-red-700 font-medium"
                >
                    <PlusIcon class="w-5 h-5" />
                    Add Property
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="properties.length === 0" class="text-center py-12">
                            <BuildingOfficeIcon class="w-20 h-20 mx-auto text-gray-400 mb-4" />
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Properties Yet</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-6">
                                Start by adding your first rental property.
                            </p>
                            <Link
                                :href="route('landlord.properties.create')"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-brand-red text-white rounded-lg hover:bg-red-700 font-medium"
                            >
                                <PlusIcon class="w-5 h-5" />
                                Add Your First Property
                            </Link>
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div
                                v-for="property in properties"
                                :key="property.id"
                                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden hover:shadow-lg transition-shadow"
                            >
                                <!-- Image -->
                                <div class="relative h-48 bg-gray-200 dark:bg-gray-700">
                                    <img
                                        v-if="property.images && property.images.length > 0"
                                        :src="`/storage/${property.images.find((img: any) => img.is_primary)?.image_url || property.images[0].image_url}`"
                                        :alt="property.title"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="flex items-center justify-center h-full">
                                        <BuildingOfficeIcon class="w-16 h-16 text-gray-400" />
                                    </div>
                                    
                                    <!-- Status Badge -->
                                    <div class="absolute top-3 right-3">
                                        <span
                                            :class="[
                                                'px-3 py-1 rounded-full text-xs font-medium capitalize',
                                                getStatusBadge(property.approval_status)
                                            ]"
                                        >
                                            {{ property.approval_status }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        {{ property.title }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        {{ property.location }}
                                    </p>
                                    <p class="text-lg font-bold text-brand-red mb-4">
                                        K{{ property.price }}/month
                                    </p>

                                    <!-- Property Details -->
                                    <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        <span>{{ property.bedrooms }} beds</span>
                                        <span>{{ property.bathrooms }} baths</span>
                                        <span>{{ property.square_feet }} sqft</span>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="route('properties.show', property.id)"
                                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
                                        >
                                            <EyeIcon class="w-4 h-4" />
                                            View
                                        </Link>
                                        <Link
                                            v-if="['pending', 'rejected'].includes(property.approval_status)"
                                            :href="route('landlord.properties.edit', property.id)"
                                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-medium"
                                        >
                                            <PencilIcon class="w-4 h-4" />
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteProperty(property.id)"
                                            class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                                        >
                                            <TrashIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
