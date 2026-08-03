<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import VerificationBadge from '@/Components/VerificationBadge.vue';
import {
    UserIcon, MapPinIcon, DocumentTextIcon,
    ClipboardDocumentCheckIcon, BoltIcon,
    CheckIcon, XMarkIcon, ClockIcon,
} from '@heroicons/vue/24/outline';

type ApplicationStatus = 'pending' | 'under_review' | 'approved' | 'rejected';

interface LandlordApplication {
    id: number;
    status: ApplicationStatus;
    user_name: string;
    user_email: string;
    nrc_passport: string;
    verification_level: 'basic' | 'trusted' | 'premium';
    landlord_type: string;
    address: string;
    town: string;
    province: string;
    id_document_url: string;
    proof_of_address_url: string;
    tax_certificate_url?: string;
    selfie_url?: string;
    video_walkthrough_url?: string;
    business_registration_url?: string;
    created_at: string;
    reviewed_at?: string;
    rejection_reason?: string;
}

const props = defineProps<{
    application: LandlordApplication;
}>();

const statusColors: Record<ApplicationStatus, string> = {
    pending:      'bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30',
    under_review: 'bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30',
    approved:     'bg-green-500/20 text-green-400 ring-1 ring-green-500/30',
    rejected:     'bg-red-500/20 text-red-400 ring-1 ring-red-500/30',
};

const approve = () => {
    if (confirm('Approve this landlord application?')) {
        router.post(route('admin.applications.approve', props.application.id));
    }
};

const reject = () => {
    const reason = prompt('Rejection reason:');
    if (reason) {
        router.post(route('admin.applications.reject', props.application.id), { rejection_reason: reason });
    }
};
</script>

<template>
    <Head title="Application Details" />

    <AuthenticatedLayout header="Application Details" :back-url="route('admin.applications.index')">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Applicant Information -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <UserIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Applicant Information</h3>
                                <span :class="['ml-auto px-2.5 py-0.5 rounded-full text-xs font-semibold', statusColors[application.status]]">
                                    {{ application.status.replace('_', ' ') }}
                                </span>
                            </div>
                            <div class="p-5">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                        <p class="text-xs text-gray-500 mb-0.5">Full Name</p>
                                        <p class="text-sm font-semibold text-white">{{ application.user_name }}</p>
                                    </div>
                                    <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                        <p class="text-xs text-gray-500 mb-0.5">Email Address</p>
                                        <p class="text-sm font-semibold text-white break-all">{{ application.user_email }}</p>
                                    </div>
                                    <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                        <p class="text-xs text-gray-500 mb-0.5">NRC / Passport</p>
                                        <p class="text-sm font-semibold text-white font-mono">{{ application.nrc_passport }}</p>
                                    </div>
                                    <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                        <p class="text-xs text-gray-500 mb-0.5">Landlord Type</p>
                                        <p class="text-sm font-semibold text-white capitalize">
                                            {{ application.landlord_type ? application.landlord_type.replace('_', ' ') : '—' }}
                                        </p>
                                    </div>
                                    <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 col-span-2">
                                        <p class="text-xs text-gray-500 mb-1">Verification Level</p>
                                        <VerificationBadge :level="application.verification_level" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <MapPinIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Address Information</h3>
                            </div>
                            <div class="p-5">
                                <div class="grid grid-cols-1 gap-3">
                                    <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                        <p class="text-xs text-gray-500 mb-0.5">Street Address</p>
                                        <p class="text-sm font-semibold text-white">{{ application.address }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                            <p class="text-xs text-gray-500 mb-0.5">Town / City</p>
                                            <p class="text-sm font-semibold text-white">{{ application.town }}</p>
                                        </div>
                                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                            <p class="text-xs text-gray-500 mb-0.5">Province</p>
                                            <p class="text-sm font-semibold text-white">{{ application.province }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Documents -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <DocumentTextIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Uploaded Documents</h3>
                            </div>
                            <div class="p-5 space-y-2">
                                <a :href="`/storage/${application.id_document_url}`" target="_blank"
                                   class="flex items-center gap-3 p-3 bg-dark-bg/60 rounded-lg hover:bg-dark-bg transition-colors">
                                    <span class="text-2xl">📄</span>
                                    <div>
                                        <p class="text-sm font-semibold text-white">ID Document</p>
                                        <p class="text-xs text-gray-500">Click to view</p>
                                    </div>
                                    <span class="ml-auto text-xs text-brand-red">Open →</span>
                                </a>
                                <a :href="`/storage/${application.proof_of_address_url}`" target="_blank"
                                   class="flex items-center gap-3 p-3 bg-dark-bg/60 rounded-lg hover:bg-dark-bg transition-colors">
                                    <span class="text-2xl">📄</span>
                                    <div>
                                        <p class="text-sm font-semibold text-white">Proof of Address</p>
                                        <p class="text-xs text-gray-500">Click to view</p>
                                    </div>
                                    <span class="ml-auto text-xs text-brand-red">Open →</span>
                                </a>
                                <a v-if="application.selfie_url" :href="`/storage/${application.selfie_url}`" target="_blank"
                                   class="flex items-center gap-3 p-3 bg-dark-bg/60 rounded-lg hover:bg-dark-bg transition-colors">
                                    <span class="text-2xl">🤳</span>
                                    <div>
                                        <p class="text-sm font-semibold text-white">Selfie / Photo ID</p>
                                        <p class="text-xs text-gray-500">Click to view</p>
                                    </div>
                                    <span class="ml-auto text-xs text-brand-red">Open →</span>
                                </a>
                                <a v-if="application.tax_certificate_url" :href="`/storage/${application.tax_certificate_url}`" target="_blank"
                                   class="flex items-center gap-3 p-3 bg-dark-bg/60 rounded-lg hover:bg-dark-bg transition-colors">
                                    <span class="text-2xl">📄</span>
                                    <div>
                                        <p class="text-sm font-semibold text-white">Tax Certificate</p>
                                        <p class="text-xs text-gray-500">Click to view</p>
                                    </div>
                                    <span class="ml-auto text-xs text-brand-red">Open →</span>
                                </a>
                                <a v-if="application.business_registration_url" :href="`/storage/${application.business_registration_url}`" target="_blank"
                                   class="flex items-center gap-3 p-3 bg-dark-bg/60 rounded-lg hover:bg-dark-bg transition-colors">
                                    <span class="text-2xl">🏢</span>
                                    <div>
                                        <p class="text-sm font-semibold text-white">Business Registration</p>
                                        <p class="text-xs text-gray-500">Click to view</p>
                                    </div>
                                    <span class="ml-auto text-xs text-brand-red">Open →</span>
                                </a>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6 lg:sticky lg:top-4 lg:self-start">

                        <!-- Status Card -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <ClipboardDocumentCheckIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Application Status</h3>
                            </div>
                            <div class="p-4 space-y-3">
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-1">Current Status</p>
                                    <span :class="['px-2.5 py-0.5 rounded-full text-xs font-semibold', statusColors[application.status]]">
                                        {{ application.status.replace('_', ' ') }}
                                    </span>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Applied On</p>
                                    <p class="text-xs font-semibold text-white">
                                        {{ new Date(application.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                                    </p>
                                </div>
                                <div v-if="application.reviewed_at" class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Reviewed On</p>
                                    <p class="text-xs font-semibold text-white">
                                        {{ new Date(application.reviewed_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                                    </p>
                                </div>
                                <div v-if="application.rejection_reason" class="bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Rejection Reason</p>
                                    <p class="text-xs text-red-400">{{ application.rejection_reason }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div v-if="application.status === 'pending' || application.status === 'under_review'"
                             class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <BoltIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Actions</h3>
                            </div>
                            <div class="p-4 space-y-2">
                                <button
                                    @click="() => router.post(route('admin.applications.under-review', application.id))"
                                    class="w-full flex items-center justify-center gap-2 bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30 px-4 py-2.5 rounded-lg hover:bg-blue-500/30 font-medium text-sm transition-colors"
                                >
                                    <ClockIcon class="w-4 h-4" />
                                    Mark as Under Review
                                </button>
                                <button
                                    @click="approve"
                                    class="w-full flex items-center justify-center gap-2 bg-green-500/20 text-green-400 ring-1 ring-green-500/30 px-4 py-2.5 rounded-lg hover:bg-green-500/30 font-medium text-sm transition-colors"
                                >
                                    <CheckIcon class="w-4 h-4" />
                                    Approve Application
                                </button>
                                <button
                                    @click="reject"
                                    class="w-full flex items-center justify-center gap-2 bg-red-500/20 text-red-400 ring-1 ring-red-500/30 px-4 py-2.5 rounded-lg hover:bg-red-500/30 font-medium text-sm transition-colors"
                                >
                                    <XMarkIcon class="w-4 h-4" />
                                    Reject Application
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
