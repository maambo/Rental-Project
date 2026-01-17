<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps<{
    applications: Array<any>;
    stats: any;
    currentStatus: string;
}>();

import VerificationBadge from '@/Components/VerificationBadge.vue';

const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    under_review: 'bg-blue-100 text-blue-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
};
</script>

<template>
    <Head title="Landlord Applications" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Landlord Applications</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</div>
                        <div class="text-sm text-gray-500">Total</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                        <div class="text-2xl font-bold text-yellow-600">{{ stats.pending }}</div>
                        <div class="text-sm text-gray-500">Pending</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                        <div class="text-2xl font-bold text-blue-600">{{ stats.under_review }}</div>
                        <div class="text-sm text-gray-500">Under Review</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                        <div class="text-2xl font-bold text-green-600">{{ stats.approved }}</div>
                        <div class="text-sm text-gray-500">Approved</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                        <div class="text-2xl font-bold text-red-600">{{ stats.rejected }}</div>
                        <div class="text-sm text-gray-500">Rejected</div>
                    </div>
                </div>

                <!-- Applications Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applicant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Verification</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applied</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="app in applications" :key="app.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ app.user_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ app.user_email }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <VerificationBadge :level="app.verification_level" />
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 capitalize">
                                        {{ app.landlord_type?.replace('_', ' ') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="['px-2 py-1 text-xs rounded-full', statusColors[app.status]]">
                                            {{ app.status.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ new Date(app.created_at).toLocaleDateString() }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Link 
                                            :href="route('admin.applications.show', app.id)"
                                            class="text-blue-600 hover:text-blue-900 font-medium"
                                        >
                                            View Details →
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
