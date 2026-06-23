<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface RentalRecord {
    id: number;
    property: { title: string, address: string, description: string };
    tenant: { name: string, email: string };
    landlord: { name: string, email: string };
    start_date: string;
    end_date: string | null;
    monthly_rent: string;
    status: string;
}

const props = defineProps<{
    record: RentalRecord;
}>();
</script>

<template>
    <Head title="Rental Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Rental Details
                </h2>
                <Link :href="route('rental-history.index')" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">
                    &larr; Back to History
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Property Card -->
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                    <h3 class="text-2xl font-bold mb-4">{{ record.property.title }}</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-2">{{ record.property.address }}</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div class="border rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-500 uppercase">Status</h4>
                            <p class="text-lg font-bold capitalize">{{ record.status }}</p>
                        </div>
                        <div class="border rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-500 uppercase">Monthly Rent</h4>
                            <p class="text-lg font-bold">K{{ Number(record.monthly_rent).toLocaleString() }}</p>
                        </div>
                        <div class="border rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-500 uppercase">Start Date</h4>
                            <p class="text-lg font-semibold">{{ record.start_date }}</p>
                        </div>
                        <div class="border rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-500 uppercase">End Date</h4>
                            <p class="text-lg font-semibold">{{ record.end_date || 'Active' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Parties Involved -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Landlord -->
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                        <h4 class="text-lg font-medium mb-4 text-emerald-600">Landlord Details</h4>
                        <p class="font-bold text-xl">{{ record.landlord.name }}</p>
                        <p class="text-gray-500">{{ record.landlord.email }}</p>
                    </div>
                    <!-- Tenant -->
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                        <h4 class="text-lg font-medium mb-4 text-blue-600">Tenant Details</h4>
                        <p class="font-bold text-xl">{{ record.tenant.name }}</p>
                        <p class="text-gray-500">{{ record.tenant.email }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
