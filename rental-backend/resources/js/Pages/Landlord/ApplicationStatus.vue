<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { CheckCircleIcon, ClockIcon, XCircleIcon, EyeIcon } from '@heroicons/vue/24/solid';
import VerificationBadge from '@/Components/VerificationBadge.vue';

const props = defineProps<{
    application: {
        id: number;
        verification_level: 'basic' | 'trusted' | 'premium';
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
        color: 'text-yellow-400 bg-yellow-500/20',
        icon: ClockIcon,
        description: 'Your application is waiting for admin review'
    },
    under_review: {
        label: 'Under Review',
        color: 'text-blue-400 bg-blue-500/20',
        icon: EyeIcon,
        description: 'An admin is currently reviewing your application'
    },
    approved: {
        label: 'Approved',
        color: 'text-green-400 bg-green-500/20',
        icon: CheckCircleIcon,
        description: 'Congratulations! Your application has been approved'
    },
    rejected: {
        label: 'Rejected',
        color: 'text-red-400 bg-red-500/20',
        icon: XCircleIcon,
        description: 'Your application was not approved'
    }
};

const currentStatus = statusConfig[props.application.status as keyof typeof statusConfig];

const verificationLabels = {
    basic: 'Basic (Free)',
    trusted: 'Trusted Landlord',
    premium: 'Premium Landlord'
};
</script>

<template>
    <Head title="Application Status" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Landlord Application Status</h2>
        </template>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">

            <!-- Status Card -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div :class="`p-3 rounded-full ${currentStatus.color}`">
                        <component :is="currentStatus.icon" class="h-8 w-8" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-2xl font-bold text-white">{{ currentStatus.label }}</h3>
                        <p class="text-gray-400">{{ currentStatus.description }}</p>
                    </div>
                    <span :class="`px-4 py-2 rounded-full font-semibold text-sm ${currentStatus.color}`">
                        {{ currentStatus.label }}
                    </span>
                </div>

                <div v-if="application.rejection_reason" class="mt-4 p-4 bg-red-500/10 border border-red-500/30 rounded-lg">
                    <h4 class="font-semibold text-red-300 mb-2">Rejection Reason:</h4>
                    <p class="text-red-300">{{ application.rejection_reason }}</p>
                </div>

                <div v-if="application.admin_notes" class="mt-4 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                    <h4 class="font-semibold text-blue-300 mb-2">Admin Notes:</h4>
                    <p class="text-blue-300">{{ application.admin_notes }}</p>
                </div>
            </div>

            <!-- Application Details -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Application Details</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Verification Level</label>
                        <p class="text-white font-semibold">
                            <VerificationBadge :level="application.verification_level" />
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Submitted On</label>
                        <p class="text-white">{{ new Date(application.created_at).toLocaleDateString() }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-sm font-medium text-gray-500">Address</label>
                        <p class="text-white">{{ application.address }}, {{ application.town }}, {{ application.province }}</p>
                    </div>
                </div>
            </div>

            <!-- Documents -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Uploaded Documents</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex items-center justify-between p-3 bg-gray-900/40 rounded-lg">
                        <span class="text-gray-300">ID Document</span>
                        <a :href="`/storage/${application.id_document_url}`" target="_blank" class="text-brand-red hover:underline">View</a>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-900/40 rounded-lg">
                        <span class="text-gray-300">Proof of Address</span>
                        <a :href="`/storage/${application.proof_of_address_url}`" target="_blank" class="text-brand-red hover:underline">View</a>
                    </div>
                    <div v-if="application.tax_certificate_url" class="flex items-center justify-between p-3 bg-gray-900/40 rounded-lg">
                        <span class="text-gray-300">Tax Certificate</span>
                        <a :href="`/storage/${application.tax_certificate_url}`" target="_blank" class="text-brand-red hover:underline">View</a>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
