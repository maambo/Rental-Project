<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface RentalRecord {
    id: number;
    property: { title: string };
    tenant: { name: string };
    landlord: { name: string };
    start_date: string;
    end_date: string | null;
    monthly_rent: string;
    status: string;
}

const props = defineProps<{
    history: {
        data: RentalRecord[];
        links: any[];
    }
}>();

const getStatusColor = (status: string) => {
    switch (status) {
        case 'active': return 'text-green-600 bg-green-100';
        case 'completed': return 'text-blue-600 bg-blue-100';
        case 'terminated': return 'text-red-600 bg-red-100';
        default: return 'text-gray-600 bg-gray-100';
    }
};
</script>

<template>
    <Head title="Rental History" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Rental History
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Landlord/Tenant</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rent</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="record in history.data" :key="record.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            {{ record.property.title }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div v-if="($page.props.auth.user as any).role === 'tenant'">
                                                {{ record.landlord.name }}
                                            </div>
                                            <div v-else>
                                                {{ record.tenant.name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ record.start_date }} - {{ record.end_date || 'Present' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-bold">
                                            K{{ Number(record.monthly_rent).toLocaleString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full capitalize" :class="getStatusColor(record.status)">
                                                {{ record.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('rental-history.show', record.id)" class="text-indigo-600 hover:text-indigo-900">View Details</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="history.data.length === 0">
                                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                            No rental history found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
