<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    ClockIcon, 
    CheckCircleIcon, 
    XCircleIcon,
    DocumentTextIcon,
    BanknotesIcon,
    BuildingOfficeIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps<{
    application: any;
    tierInfo: any;
}>();

const statusConfig = {
    pending: {
        color: 'yellow',
        icon: ClockIcon,
        title: 'Application Pending',
        message: 'Your landlord application is under review. We\'ll notify you once it\'s processed.'
    },
    under_review: {
        color: 'blue',
        icon: ClockIcon,
        title: 'Under Review',
        message: 'Our team is currently reviewing your application. This usually takes 1-2 business days.'
    },
    approved: {
        color: 'green',
        icon: CheckCircleIcon,
        title: 'Application Approved!',
        message: 'Congratulations! Your landlord application has been approved. You can now start listing properties.'
    },
    rejected: {
        color: 'red',
        icon: XCircleIcon,
        title: 'Application Rejected',
        message: 'Unfortunately, your application was not approved. Please see the reason below.'
    }
};

const currentStatus = props.application ? statusConfig[props.application.status] : null;
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Applicant Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Application Status Card -->
                <div v-if="application" :class="[
                    'bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-l-4',
                    `border-${currentStatus.color}-500`
                ]">
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div :class="[
                                'p-3 rounded-full',
                                `bg-${currentStatus.color}-100 text-${currentStatus.color}-600`
                            ]">
                                <component :is="currentStatus.icon" class="w-8 h-8" />
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                                    {{ currentStatus.title }}
                                </h3>
                                <p class="text-gray-600 dark:text-gray-400 mb-4">
                                    {{ currentStatus.message }}
                                </p>
                                
                                <!-- Application Details -->
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                                    <div>
                                        <p class="text-xs text-gray-500">Tier</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white capitalize">{{ application.tier }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Applied On</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ new Date(application.created_at).toLocaleDateString() }}
                                        </p>
                                    </div>
                                    <div v-if="application.reviewed_at">
                                        <p class="text-xs text-gray-500">Reviewed On</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ new Date(application.reviewed_at).toLocaleDateString() }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Status</p>
                                        <p class="text-sm font-semibold capitalize" :class="`text-${currentStatus.color}-600`">
                                            {{ application.status.replace('_', ' ') }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Rejection Reason -->
                                <div v-if="application.status === 'rejected' && application.rejection_reason" 
                                     class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                    <p class="text-sm font-medium text-red-800 dark:text-red-200 mb-1">Rejection Reason:</p>
                                    <p class="text-sm text-red-700 dark:text-red-300">{{ application.rejection_reason }}</p>
                                </div>

                                <!-- Actions -->
                                <div class="mt-6 flex gap-3">
                                    <Link :href="route('landlord.status')" 
                                          class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                        View Full Details
                                    </Link>
                                    <Link v-if="application.status === 'rejected'" :href="route('landlord.apply')" 
                                          class="inline-flex items-center px-4 py-2 bg-brand-red border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                        Reapply
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Application Card -->
                <div v-else class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <DocumentTextIcon class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Application Found</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            You haven't submitted a landlord application yet.
                        </p>
                        <Link :href="route('landlord.apply')" 
                              class="inline-flex items-center px-4 py-2 bg-brand-red border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                            Apply Now
                        </Link>
                    </div>
                </div>

                <!-- Tier Information -->
                <div v-if="application" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <BuildingOfficeIcon class="w-6 h-6 text-blue-600" />
                                </div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Your Tier</h3>
                            </div>
                            <p class="text-3xl font-bold text-brand-red capitalize">{{ application.tier }}</p>
                            <p class="text-sm text-gray-500 mt-1">Landlord Tier</p>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="p-2 bg-green-100 rounded-lg">
                                    <BanknotesIcon class="w-6 h-6 text-green-600" />
                                </div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Property Limit</h3>
                            </div>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">
                                {{ tierInfo && tierInfo[application.tier] ? tierInfo[application.tier].properties : '—' }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">Properties allowed</p>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="p-2 bg-purple-100 rounded-lg">
                                    <DocumentTextIcon class="w-6 h-6 text-purple-600" />
                                </div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Application Fee</h3>
                            </div>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">
                                K{{ tierInfo && tierInfo[application.tier] ? tierInfo[application.tier].fee : '—' }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">One-time payment</p>
                        </div>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                    <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">Need Help?</h3>
                    <p class="text-sm text-blue-800 dark:text-blue-200 mb-4">
                        If you have any questions about your application or the landlord tiers, feel free to contact our support team.
                    </p>
                    <button class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        Contact Support
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
