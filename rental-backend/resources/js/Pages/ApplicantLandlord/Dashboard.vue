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
import VerificationBadge from '@/Components/VerificationBadge.vue';

const props = defineProps<{
    application: any;
    tierInfo: any;
}>();

const statusConfig = {
    pending: {
        border: 'border-yellow-500',
        badge: 'bg-yellow-500/20 text-yellow-400',
        text: 'text-yellow-400',
        icon: ClockIcon,
        title: 'Application Pending',
        message: 'Your landlord application is under review. We\'ll notify you once it\'s processed.'
    },
    under_review: {
        border: 'border-blue-500',
        badge: 'bg-blue-500/20 text-blue-400',
        text: 'text-blue-400',
        icon: ClockIcon,
        title: 'Under Review',
        message: 'Our team is currently reviewing your application. This usually takes 1-2 business days.'
    },
    approved: {
        border: 'border-green-500',
        badge: 'bg-green-500/20 text-green-400',
        text: 'text-green-400',
        icon: CheckCircleIcon,
        title: 'Application Approved!',
        message: 'Congratulations! Your landlord application has been approved. You can now start listing properties.'
    },
    rejected: {
        border: 'border-red-500',
        badge: 'bg-red-500/20 text-red-400',
        text: 'text-red-400',
        icon: XCircleIcon,
        title: 'Application Rejected',
        message: 'Unfortunately, your application was not approved. Please see the reason below.'
    }
};

const currentStatus = props.application ? statusConfig[props.application.status as keyof typeof statusConfig] : null;

const verificationLabels: Record<string, string> = {
    basic: 'Basic',
    trusted: 'Trusted',
    premium: 'Premium'
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Applicant Dashboard</h2>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">
            <!-- Application Status Card -->
            <div v-if="application && currentStatus" :class="['bg-gray-800 rounded-xl border border-gray-700 border-l-4', currentStatus.border]">
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div :class="['p-3 rounded-full', currentStatus.badge]">
                            <component :is="currentStatus.icon" class="w-8 h-8" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-white mb-2">
                                {{ currentStatus.title }}
                            </h3>
                            <p class="text-gray-400 mb-4">
                                {{ currentStatus.message }}
                            </p>

                            <!-- Application Details -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                                <div>
                                    <p class="text-xs text-gray-500">Verification Level</p>
                                    <p class="text-sm font-semibold text-white capitalize">
                                        <VerificationBadge :level="application.verification_level" />
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Applied On</p>
                                    <p class="text-sm font-semibold text-white">
                                        {{ new Date(application.created_at).toLocaleDateString() }}
                                    </p>
                                </div>
                                <div v-if="application.reviewed_at">
                                    <p class="text-xs text-gray-500">Reviewed On</p>
                                    <p class="text-sm font-semibold text-white">
                                        {{ new Date(application.reviewed_at).toLocaleDateString() }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Status</p>
                                    <p class="text-sm font-semibold capitalize" :class="currentStatus.text">
                                        {{ application.status.replace('_', ' ') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Rejection Reason -->
                            <div v-if="application.status === 'rejected' && application.rejection_reason"
                                 class="mt-4 p-4 bg-red-500/10 rounded-lg">
                                <p class="text-sm font-medium text-red-300 mb-1">Rejection Reason:</p>
                                <p class="text-sm text-red-300">{{ application.rejection_reason }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="mt-6 flex gap-3">
                                <Link :href="route('landlord.status')"
                                      class="inline-flex items-center px-4 py-2 border border-gray-600 rounded-lg font-semibold text-xs text-gray-300 uppercase tracking-widest hover:bg-gray-700 transition-colors">
                                    View Full Details
                                </Link>
                                <Link v-if="application.status === 'rejected'" :href="route('landlord.apply')"
                                      class="inline-flex items-center px-4 py-2 bg-brand-red border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition-colors">
                                    Reapply
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Application Card -->
            <div v-else class="bg-gray-800 rounded-xl border border-gray-700">
                <div class="p-6 text-center">
                    <DocumentTextIcon class="w-16 h-16 mx-auto text-gray-600 mb-4" />
                    <h3 class="text-lg font-semibold text-white mb-2">No Application Found</h3>
                    <p class="text-gray-400 mb-4">
                        You haven't submitted a landlord application yet.
                    </p>
                    <Link :href="route('landlord.apply')"
                          class="inline-flex items-center px-4 py-2 bg-brand-red border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition-colors">
                        Apply Now
                    </Link>
                </div>
            </div>

            <!-- Tier Information -->
            <div v-if="application" class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-brand-red/10 rounded-lg">
                            <BuildingOfficeIcon class="w-6 h-6 text-brand-red" />
                        </div>
                        <h3 class="font-semibold text-white">Your Level</h3>
                    </div>
                    <p class="text-3xl font-bold text-brand-red capitalize">
                        {{ verificationLabels[application.verification_level] || application.verification_level }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">Verification Status</p>
                </div>

                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-green-500/10 rounded-lg">
                            <BanknotesIcon class="w-6 h-6 text-green-400" />
                        </div>
                        <h3 class="font-semibold text-white">Property Limit</h3>
                    </div>
                    <p class="text-3xl font-bold text-white">
                        {{ tierInfo && tierInfo[application.verification_level] ? tierInfo[application.verification_level].properties : '—' }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">Properties allowed</p>
                </div>

                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-purple-500/10 rounded-lg">
                            <DocumentTextIcon class="w-6 h-6 text-purple-400" />
                        </div>
                        <h3 class="font-semibold text-white">Application Fee</h3>
                    </div>
                    <p class="text-3xl font-bold text-white">
                        {{ tierInfo && tierInfo[application.verification_level] ? tierInfo[application.verification_level].fee : '—' }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">One-time payment</p>
                </div>
            </div>

            <!-- Help Section -->
            <div class="bg-brand-info/10 border border-brand-info/30 rounded-xl p-6">
                <h3 class="font-semibold text-brand-info mb-2">Need Help?</h3>
                <p class="text-sm text-blue-300 mb-4">
                    If you have any questions about your application or the landlord tiers, feel free to contact our support team.
                </p>
                <button class="inline-flex items-center px-4 py-2 bg-brand-info border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:brightness-110 transition">
                    Contact Support
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
