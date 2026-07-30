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
        case 'active': return 'text-green-400 bg-green-500/20';
        case 'completed': return 'text-blue-400 bg-blue-500/20';
        case 'terminated': return 'text-red-400 bg-red-500/20';
        default: return 'text-gray-400 bg-gray-500/20';
    }
};
</script>

<template>
    <Head title="Rental History" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">
                Rental History
            </h2>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">Property</th>
                            <th class="px-4 py-3 font-medium">Landlord/Tenant</th>
                            <th class="px-4 py-3 font-medium">Period</th>
                            <th class="px-4 py-3 font-medium">Rent</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="record in history.data" :key="record.id" class="hover:bg-gray-700/40">
                            <td class="px-4 py-3 font-medium text-white">
                                {{ record.property.title }}
                            </td>
                            <td class="px-4 py-3 text-gray-400">
                                <div v-if="($page.props.auth.user as any).role === 'tenant'">
                                    {{ record.landlord.name }}
                                </div>
                                <div v-else>
                                    {{ record.tenant.name }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-400">
                                {{ record.start_date }} - {{ record.end_date || 'Present' }}
                            </td>
                            <td class="px-4 py-3 text-white font-bold">
                                K{{ Number(record.monthly_rent).toLocaleString() }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs rounded-full capitalize" :class="getStatusColor(record.status)">
                                    {{ record.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Link :href="route('rental-history.show', record.id)" class="text-brand-red hover:text-brand-orange">View Details</Link>
                            </td>
                        </tr>
                        <tr v-if="history.data.length === 0">
                            <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                                No rental history found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
