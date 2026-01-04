<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { CheckCircleIcon, ClockIcon, XCircleIcon, EyeIcon } from '@heroicons/vue/24/solid';

const props = defineProps<{
    application: {
        id: number;
        tier: string;
        status: string;
        address: string;
        province: string;
        town: string;
        id_document_url: string;
        proof_of_address_url: string;
        tax_certificate_url: string | null;
        admin_notes: string | null;
        rejection_reason: string | null;
        created_at: string;
        updated_at: string;
        reviewed_at: string | null;
    };
}>();

const statusConfig = {
    pending: {
        label: 'Pending Review',
        color: 'text-yellow-600 bg-yellow-100',
        icon: ClockIcon,
        description: 'Your application is waiting for admin review'
    },
    under_review: {
        label: 'Under Review',
        color: 'text-blue-600 bg-blue-100',
        icon: EyeIcon,
        description: 'An admin is currently reviewing your application'
    },
    approved: {
        label: 'Approved',
        color: 'text-green-600 bg-green-100',
        icon: CheckCircleIcon,
        description: 'Congratulations! Your application has been approved'
    },
    rejected: {
        label: 'Rejected',
        color: 'text-red-600 bg-red-100',
        icon: XCircleIcon,
        description: 'Your application was not approved'
    }
};

const currentStatus = statusConfig[props.application.status as keyof typeof statusConfig];

const tierLabels = {
    small: 'Small Landlord',
    medium: 'Medium Landlord',
    large: 'Enterprise'
};
</script>

<template>
    <Head title="Application Status" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Landlord Application Status</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Status Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div :class="`p-3 rounded-full ${currentStatus.color}`">
                            <component :is="currentStatus.icon" class="h-8 w-8" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ currentStatus.label }}</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ currentStatus.description }}</p>
                        </div>
                        <span :class="`px-4 py-2 rounded-full font-semibold text-sm ${currentStatus.color}`">
                            {{ currentStatus.label }}
                        </span>
                    </div>

                    <div v-if="application.rejection_reason" class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <h4 class="font-semibold text-red-900 dark:text-red-200 mb-2">Rejection Reason:</h4>
                        <p class="text-red-700 dark:text-red-300">{{ application.rejection_reason }}</p>
                    </div>

                    <div v-if="application.admin_notes" class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <h4 class="font-semibold text-blue-900 dark:text-blue-200 mb-2">Admin Notes:</h4>
                        <p class="text-blue-700 dark:text-blue-300">{{ application.admin_notes }}</p>
                    </div>
                </div>

                <!-- Application Details -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Application Details</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Tier</label>
                            <p class="text-gray-900 dark:text-white font-semibold">{{ tierLabels[application.tier as keyof typeof tierLabels] }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Submitted On</label>
                            <p class="text-gray-900 dark:text-white">{{ new Date(application.created_at).toLocaleDateString() }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</label>
                            <p class="text-gray-900 dark:text-white">{{ application.address }}, {{ application.town }}, {{ application.province }}</p>
                        </div>
                    </div>
                </div>

                <!-- Documents -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Uploaded Documents</h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-gray-700 dark:text-gray-300">ID Document</span>
                            <a :href="`/storage/${application.id_document_url}`" target="_blank" class="text-brand-red hover:underline">View</a>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-gray-700 dark:text-gray-300">Proof of Address</span>
                            <a :href="`/storage/${application.proof_of_address_url}`" target="_blank" class="text-brand-red hover:underline">View</a>
                        </div>
                        <div v-if="application.tax_certificate_url" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-gray-700 dark:text-gray-300">Tax Certificate</span>
                            <a :href="`/storage/${application.tax_certificate_url}`" target="_blank" class="text-brand-red hover:underline">View</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
