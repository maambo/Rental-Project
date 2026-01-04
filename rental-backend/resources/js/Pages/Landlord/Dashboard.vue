<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    BuildingOfficeIcon, 
    CheckCircleIcon, 
    ClockIcon,
    UserGroupIcon,
    CalendarIcon,
    PlusIcon
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
    recentProperties: any[];
    tier: string;
    propertyLimit: number | null;
}>();

const getStatusBadge = (status: string) => {
    const badges = {
        approved: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return badges[status as keyof typeof badges] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Landlord Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Landlord Dashboard</h2>
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
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Tier Info Banner -->
                <div class="bg-gradient-to-r from-brand-red to-red-700 text-white rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold capitalize">{{ tier }} Tier</h3>
                            <p class="text-red-100 mt-1">
                                {{ propertyLimit ? `${stats.total_properties} / ${propertyLimit}` : stats.total_properties }} 
                                {{ propertyLimit ? 'Properties' : 'Unlimited Properties' }}
                            </p>
                        </div>
                        <BuildingOfficeIcon class="w-16 h-16 opacity-50" />
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Total Properties -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-blue-100 rounded-lg">
                                    <BuildingOfficeIcon class="w-8 h-8 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Properties</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_properties }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approved Properties -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-green-100 rounded-lg">
                                    <CheckCircleIcon class="w-8 h-8 text-green-600" />
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Approved</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.approved_properties }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Properties -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-yellow-100 rounded-lg">
                                    <ClockIcon class="w-8 h-8 text-yellow-600" />
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Pending Approval</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.pending_properties }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Applications -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-purple-100 rounded-lg">
                                    <UserGroupIcon class="w-8 h-8 text-purple-600" />
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Applications</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_applications }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ stats.pending_applications }} pending</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tour Requests -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-orange-100 rounded-lg">
                                    <CalendarIcon class="w-8 h-8 text-orange-600" />
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Tour Requests</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.tour_requests }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Properties -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Properties</h3>
                        <Link
                            :href="route('landlord.properties.index')"
                            class="text-sm text-brand-red hover:text-red-700 font-medium"
                        >
                            View All
                        </Link>
                    </div>
                    <div class="p-6">
                        <div v-if="recentProperties.length === 0" class="text-center py-8">
                            <BuildingOfficeIcon class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                            <p class="text-gray-600 dark:text-gray-400 mb-4">No properties yet</p>
                            <Link
                                :href="route('landlord.properties.create')"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-brand-red text-white rounded-lg hover:bg-red-700"
                            >
                                <PlusIcon class="w-5 h-5" />
                                Add Your First Property
                            </Link>
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="property in recentProperties"
                                :key="property.id"
                                class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ property.title }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ property.location }}</p>
                                    <p class="text-sm font-medium text-brand-red mt-1">K{{ property.price }}/month</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span
                                        :class="[
                                            'px-3 py-1 rounded-full text-xs font-medium capitalize',
                                            getStatusBadge(property.approval_status)
                                        ]"
                                    >
                                        {{ property.approval_status }}
                                    </span>
                                    <Link
                                        :href="route('landlord.properties.edit', property.id)"
                                        class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                                    >
                                        View
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Link
                        :href="route('landlord.properties.create')"
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow border-2 border-dashed border-gray-300 dark:border-gray-600"
                    >
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-brand-red/10 rounded-lg">
                                <PlusIcon class="w-8 h-8 text-brand-red" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Add New Property</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">List a new property for rent</p>
                            </div>
                        </div>
                    </Link>

                    <Link
                        :href="route('landlord.properties.index')"
                        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow"
                    >
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <BuildingOfficeIcon class="w-8 h-8 text-blue-600" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Manage Properties</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">View and edit your listings</p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
