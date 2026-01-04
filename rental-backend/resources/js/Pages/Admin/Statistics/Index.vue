<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { UsersIcon, BuildingOfficeIcon, DocumentTextIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    stats: any;
    recentApplications: Array<any>;
}>();
</script>

<template>
    <Head title="Statistics" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">System Statistics</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- User Stats -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Users</h3>
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                            <UsersIcon class="w-8 h-8 text-blue-600 mb-2" />
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.users.total }}</div>
                            <div class="text-sm text-gray-500">Total Users</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                            <div class="text-2xl font-bold text-purple-600">{{ stats.users.admins }}</div>
                            <div class="text-sm text-gray-500">Admins</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                            <div class="text-2xl font-bold text-green-600">{{ stats.users.landlords }}</div>
                            <div class="text-sm text-gray-500">Landlords</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                            <div class="text-2xl font-bold text-blue-600">{{ stats.users.tenants }}</div>
                            <div class="text-sm text-gray-500">Tenants</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                            <div class="text-2xl font-bold text-yellow-600">{{ stats.users.applicants }}</div>
                            <div class="text-sm text-gray-500">Applicants</div>
                        </div>
                    </div>
                </div>

                <!-- Property Stats -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Properties</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                            <BuildingOfficeIcon class="w-8 h-8 text-blue-600 mb-2" />
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.properties.total }}</div>
                            <div class="text-sm text-gray-500">Total Properties</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                            <div class="text-2xl font-bold text-green-600">{{ stats.properties.available }}</div>
                            <div class="text-sm text-gray-500">Available</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                            <div class="text-2xl font-bold text-blue-600">{{ stats.properties.rented }}</div>
                            <div class="text-sm text-gray-500">Rented</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                            <div class="text-2xl font-bold text-yellow-600">{{ stats.properties.maintenance }}</div>
                            <div class="text-sm text-gray-500">Maintenance</div>
                        </div>
                    </div>
                </div>

                <!-- Application Stats -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Applications</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                            <DocumentTextIcon class="w-8 h-8 text-blue-600 mb-2" />
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.applications.total }}</div>
                            <div class="text-sm text-gray-500">Total Applications</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                            <div class="text-2xl font-bold text-yellow-600">{{ stats.applications.pending }}</div>
                            <div class="text-sm text-gray-500">Pending</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                            <div class="text-2xl font-bold text-green-600">{{ stats.applications.approved }}</div>
                            <div class="text-sm text-gray-500">Approved</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                            <div class="text-2xl font-bold text-red-600">{{ stats.applications.rejected }}</div>
                            <div class="text-sm text-gray-500">Rejected</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
