<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

type ApprovalStatus = 'pending' | 'approved' | 'rejected';

interface Property {
    id: number;
    code: string;
    title: string;
    landlord_name: string;
    price: number;
    approval_status: ApprovalStatus;
    created_at: string;
}

const props = defineProps<{
    properties: Array<Property>;
    stats: any;
    currentStatus: string;
}>();

const statusColors: Record<ApprovalStatus, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
};
</script>

<template>
    <Head title="Approve Properties" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Property Approvals</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</div>
                        <div class="text-sm text-gray-500">Total</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                        <div class="text-2xl font-bold text-yellow-600">{{ stats.pending }}</div>
                        <div class="text-sm text-gray-500">Pending</div>
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

                <!-- Properties Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Landlord</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="property in properties" :key="property.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ property.code }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ property.title }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ property.landlord_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">K{{ Number(property.price).toLocaleString() }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="['px-2 py-1 text-xs rounded-full', statusColors[property.approval_status]]">
                                            {{ property.approval_status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ new Date(property.created_at).toLocaleDateString() }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Link 
                                            :href="route('admin.properties.show', property.id)"
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
