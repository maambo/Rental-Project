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
            <div class="flex justify-between items-center w-full">
                <h2 class="text-xl font-semibold text-white">
                    Rental Details
                </h2>
                <Link :href="route('rental-history.index')" class="text-sm text-gray-400 hover:text-white">
                    &larr; Back to History
                </Link>
            </div>
        </template>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">
            <!-- Property Card -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-2xl font-bold text-white mb-4">{{ record.property.title }}</h3>
                <p class="text-gray-400 mb-2">{{ record.property.address }}</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    <div class="border border-gray-700 rounded-lg p-4">
                        <h4 class="text-xs font-medium text-gray-500 uppercase">Status</h4>
                        <p class="text-lg font-bold text-white capitalize">{{ record.status }}</p>
                    </div>
                    <div class="border border-gray-700 rounded-lg p-4">
                        <h4 class="text-xs font-medium text-gray-500 uppercase">Monthly Rent</h4>
                        <p class="text-lg font-bold text-white">K{{ Number(record.monthly_rent).toLocaleString() }}</p>
                    </div>
                    <div class="border border-gray-700 rounded-lg p-4">
                        <h4 class="text-xs font-medium text-gray-500 uppercase">Start Date</h4>
                        <p class="text-lg font-semibold text-white">{{ record.start_date }}</p>
                    </div>
                    <div class="border border-gray-700 rounded-lg p-4">
                        <h4 class="text-xs font-medium text-gray-500 uppercase">End Date</h4>
                        <p class="text-lg font-semibold text-white">{{ record.end_date || 'Active' }}</p>
                    </div>
                </div>
            </div>

            <!-- Parties Involved -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Landlord -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h4 class="text-lg font-semibold mb-4 text-green-400">Landlord Details</h4>
                    <p class="font-bold text-xl text-white">{{ record.landlord.name }}</p>
                    <p class="text-gray-400">{{ record.landlord.email }}</p>
                </div>
                <!-- Tenant -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h4 class="text-lg font-semibold mb-4 text-brand-info">Tenant Details</h4>
                    <p class="font-bold text-xl text-white">{{ record.tenant.name }}</p>
                    <p class="text-gray-400">{{ record.tenant.email }}</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
