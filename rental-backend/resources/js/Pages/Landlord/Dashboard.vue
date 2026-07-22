<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PropertiesMap from '@/Components/PropertiesMap.vue';
import {
    BuildingOfficeIcon,
    CheckCircleIcon,
    ClockIcon,
    UserGroupIcon,
    CalendarIcon,
    PlusIcon,
    MapPinIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{
    stats: {
        total_properties: number;
        approved_properties: number;
        pending_properties: number;
        total_applications: number;
        pending_applications: number;
        tour_requests: number;
    };
    mapProperties: any[];
}>();

const mapReadyProperties = computed(() =>
    props.mapProperties.map(p => ({
        ...p,
        detailUrl: route('properties.show', p.id),
    }))
);
</script>

<template>
    <Head title="Landlord Dashboard" />

    <AuthenticatedLayout header="Landlord Dashboard">
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    <div class="bg-light-bg rounded-xl p-4">
                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 flex items-center gap-3">
                            <BuildingOfficeIcon class="w-7 h-7 text-brand-red shrink-0" />
                            <div>
                                <p class="text-xs text-gray-500">Properties</p>
                                <p class="text-2xl font-bold text-white">{{ stats.total_properties }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-light-bg rounded-xl p-4">
                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 flex items-center gap-3">
                            <CheckCircleIcon class="w-7 h-7 text-green-400 shrink-0" />
                            <div>
                                <p class="text-xs text-gray-500">Approved</p>
                                <p class="text-2xl font-bold text-white">{{ stats.approved_properties }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-light-bg rounded-xl p-4">
                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 flex items-center gap-3">
                            <ClockIcon class="w-7 h-7 text-yellow-400 shrink-0" />
                            <div>
                                <p class="text-xs text-gray-500">Pending</p>
                                <p class="text-2xl font-bold text-white">{{ stats.pending_properties }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-light-bg rounded-xl p-4">
                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 flex items-center gap-3">
                            <UserGroupIcon class="w-7 h-7 text-purple-400 shrink-0" />
                            <div>
                                <p class="text-xs text-gray-500">Applications</p>
                                <p class="text-2xl font-bold text-white">{{ stats.total_applications }}</p>
                                <p class="text-[10px] text-gray-500">{{ stats.pending_applications }} pending</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-light-bg rounded-xl p-4">
                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 flex items-center gap-3">
                            <CalendarIcon class="w-7 h-7 text-orange-400 shrink-0" />
                            <div>
                                <p class="text-xs text-gray-500">Tour Requests</p>
                                <p class="text-2xl font-bold text-white">{{ stats.tour_requests }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Properties Map -->
                <div class="bg-light-bg rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-700/60 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <MapPinIcon class="w-4 h-4 text-brand-red" />
                            <h3 class="text-sm font-semibold text-white">My Properties</h3>
                            <span class="ml-1 text-xs px-2 py-0.5 rounded-full bg-dark-bg/60 text-gray-400">
                                {{ mapProperties.length }} total · {{ mapProperties.filter(p => p.latitude && p.longitude).length }} on map
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <Link :href="route('landlord.properties.create')"
                                class="inline-flex items-center gap-1.5 text-xs bg-brand-red text-white px-3 py-1.5 rounded-md font-medium hover:bg-red-700 transition">
                                <PlusIcon class="w-3.5 h-3.5" /> Add Property
                            </Link>
                            <Link :href="route('landlord.properties.index')"
                                class="text-xs text-brand-red hover:text-red-400 font-medium">
                                View All →
                            </Link>
                        </div>
                    </div>

                    <div class="p-4">
                        <!-- Map -->
                        <PropertiesMap
                            v-if="mapProperties.length"
                            :properties="mapReadyProperties"
                            height="h-[480px]"
                            :zoom="11"
                        />

                        <!-- Empty state -->
                        <div v-else class="py-16 text-center">
                            <BuildingOfficeIcon class="w-14 h-14 mx-auto text-gray-600 mb-3" />
                            <h3 class="text-base font-semibold text-white mb-1">No Properties Yet</h3>
                            <p class="text-sm text-gray-400 mb-6">Add your first property listing to see it on the map.</p>
                            <Link :href="route('landlord.properties.create')"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-red text-white rounded-lg hover:bg-red-700 text-sm font-medium transition">
                                <PlusIcon class="w-4 h-4" /> Add Property
                            </Link>
                        </div>

                        <!-- Legend -->
                        <div v-if="mapProperties.length" class="mt-3 flex flex-wrap items-center gap-4 px-1">
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-green-500 ring-2 ring-green-500/30 inline-block"></span>
                                <span class="text-xs text-gray-400">Approved</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-yellow-500 ring-2 ring-yellow-500/30 inline-block"></span>
                                <span class="text-xs text-gray-400">Pending</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-red-500 ring-2 ring-red-500/30 inline-block"></span>
                                <span class="text-xs text-gray-400">Rented / Sold</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-blue-500 ring-2 ring-blue-500/30 inline-block"></span>
                                <span class="text-xs text-gray-400">Under Review</span>
                            </div>
                            <span class="text-xs text-gray-600 ml-auto">Click a pin to see property details</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
