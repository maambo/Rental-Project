<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    UserIcon, IdentificationIcon, DocumentTextIcon, MapPinIcon,
    ChartBarIcon, BoltIcon, PencilSquareIcon, TrashIcon,
    ArrowRightOnRectangleIcon, CheckBadgeIcon,
} from '@heroicons/vue/24/outline';

type ApplicationStatus = 'pending' | 'under_review' | 'approved' | 'rejected';

interface LandlordApplication {
    id: number;
    status: ApplicationStatus;
    nrc_passport: string | null;
    landlord_type: string | null;
    address: string | null;
    province: string | null;
    town: string | null;
    id_document_url: string | null;
    proof_of_address_url: string | null;
    tax_certificate_url: string | null;
    selfie_url: string | null;
    video_walkthrough_url: string | null;
    business_registration_url: string | null;
    verification_submitted_at: string | null;
    admin_notes: string | null;
    rejection_reason: string | null;
    reviewed_at: string | null;
    reviewer?: { id: number; name: string } | null;
    created_at: string;
}

interface AdminUser {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    avatar: string | null;
    avatar_url: string;
    google_id: string | null;
    id_type: string | null;
    nrc_passport: string | null;
    id_document_url: string | null;
    selfie_url: string | null;
    identity_verified_at: string | null;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    role_model?: { id: number; name: string } | null;
    landlord_application?: LandlordApplication | null;
    active_subscription?: { id: number; ends_at: string | null; tier?: { name: string } | null } | null;
    properties_count: number;
    tour_requests_count: number;
    tenant_rentals_count: number;
    reviews_count: number;
}

const props = defineProps<{ user: AdminUser }>();

const statusColors: Record<ApplicationStatus, string> = {
    pending:      'bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30',
    under_review: 'bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30',
    approved:     'bg-green-500/20 text-green-400 ring-1 ring-green-500/30',
    rejected:     'bg-red-500/20 text-red-400 ring-1 ring-red-500/30',
};

const formatDate = (value: string | null | undefined) =>
    value
        ? new Date(value).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
        : '—';

const humanize = (value: string | null | undefined) =>
    value ? value.replace(/_/g, ' ') : '—';

// Documents live in two places: the identity files captured at registration sit on
// the user row, the rest only exist once a landlord application is submitted.
const documents = () => {
    const app = props.user.landlord_application;
    const list: Array<{ label: string; path: string; icon: string }> = [];
    const push = (label: string, path: string | null | undefined, icon = '📄') => {
        if (path) list.push({ label, path, icon });
    };

    push('ID Document (registration)', props.user.id_document_url);
    push('Selfie (registration)', props.user.selfie_url, '🤳');

    if (app) {
        push('ID Document (application)', app.id_document_url);
        push('Proof of Address', app.proof_of_address_url);
        push('Selfie (application)', app.selfie_url, '🤳');
        push('Tax Certificate', app.tax_certificate_url);
        push('Business Registration', app.business_registration_url, '🏢');
        push('Video Walkthrough', app.video_walkthrough_url, '🎥');
    }

    return list;
};

const deleteUser = () => {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        router.delete(route('admin.users.destroy', props.user.id));
    }
};

const loginAs = () => {
    if (confirm('Are you sure you want to log in as this user?')) {
        router.post(route('admin.users.login-as', props.user.id));
    }
};
</script>

<template>
    <Head :title="user.name" />

    <AuthenticatedLayout header="User Details" :back-url="route('admin.users.index')">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Account -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <UserIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Account</h3>
                                <span class="ml-auto px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize bg-gray-500/20 text-gray-300 ring-1 ring-gray-500/30">
                                    {{ humanize(user.role_model?.name) }}
                                </span>
                            </div>
                            <div class="p-5">
                                <div class="flex items-center gap-4 mb-4">
                                    <img
                                        :src="user.avatar_url"
                                        :alt="user.name"
                                        class="h-16 w-16 rounded-full object-cover ring-2 ring-gray-700 bg-gray-700"
                                    />
                                    <div>
                                        <p class="text-base font-semibold text-white">{{ user.name }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ user.avatar ? 'Custom profile photo' : 'Generated initials avatar' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Full Name</p>
                                    <p class="text-sm font-semibold text-white">{{ user.name }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Email Address</p>
                                    <p class="text-sm font-semibold text-white break-all">{{ user.email }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Phone</p>
                                    <p class="text-sm font-semibold text-white">{{ user.phone || '—' }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Email Verified</p>
                                    <p class="text-sm font-semibold text-white">{{ formatDate(user.email_verified_at) }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Registered On</p>
                                    <p class="text-sm font-semibold text-white">{{ formatDate(user.created_at) }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Sign-in Method</p>
                                    <p class="text-sm font-semibold text-white">{{ user.google_id ? 'Google' : 'Email & Password' }}</p>
                                </div>
                                </div>
                            </div>
                        </div>

                        <!-- Identity submitted at registration -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <IdentificationIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Identity (submitted at registration)</h3>
                                <span v-if="user.identity_verified_at"
                                      class="ml-auto flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-500/20 text-green-400 ring-1 ring-green-500/30">
                                    <CheckBadgeIcon class="w-3.5 h-3.5" /> Verified
                                </span>
                                <span v-else
                                      class="ml-auto px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30">
                                    Unverified
                                </span>
                            </div>
                            <div class="p-5 grid grid-cols-2 gap-3">
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">ID Type</p>
                                    <p class="text-sm font-semibold text-white uppercase">{{ user.id_type || '—' }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">NRC / Passport</p>
                                    <p class="text-sm font-semibold text-white font-mono">{{ user.nrc_passport || '—' }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 col-span-2">
                                    <p class="text-xs text-gray-500 mb-0.5">Identity Verified On</p>
                                    <p class="text-sm font-semibold text-white">{{ formatDate(user.identity_verified_at) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Landlord application -->
                        <div v-if="user.landlord_application" class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <MapPinIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Landlord Application</h3>
                                <span :class="['ml-auto px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize', statusColors[user.landlord_application.status]]">
                                    {{ humanize(user.landlord_application.status) }}
                                </span>
                            </div>
                            <div class="p-5 grid grid-cols-2 gap-3">
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Landlord Type</p>
                                    <p class="text-sm font-semibold text-white capitalize">{{ humanize(user.landlord_application.landlord_type) }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">NRC / Passport</p>
                                    <p class="text-sm font-semibold text-white font-mono">{{ user.landlord_application.nrc_passport || '—' }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 col-span-2">
                                    <p class="text-xs text-gray-500 mb-0.5">Street Address</p>
                                    <p class="text-sm font-semibold text-white">{{ user.landlord_application.address || '—' }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Town / City</p>
                                    <p class="text-sm font-semibold text-white">{{ user.landlord_application.town || '—' }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Province</p>
                                    <p class="text-sm font-semibold text-white">{{ user.landlord_application.province || '—' }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Submitted On</p>
                                    <p class="text-sm font-semibold text-white">{{ formatDate(user.landlord_application.created_at) }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Reviewed On</p>
                                    <p class="text-sm font-semibold text-white">
                                        {{ formatDate(user.landlord_application.reviewed_at) }}
                                        <span v-if="user.landlord_application.reviewer" class="text-gray-500 font-normal">
                                            by {{ user.landlord_application.reviewer.name }}
                                        </span>
                                    </p>
                                </div>
                                <div v-if="user.landlord_application.admin_notes" class="bg-dark-bg/60 rounded-lg px-3 py-2.5 col-span-2">
                                    <p class="text-xs text-gray-500 mb-0.5">Admin Notes</p>
                                    <p class="text-sm text-gray-300">{{ user.landlord_application.admin_notes }}</p>
                                </div>
                                <div v-if="user.landlord_application.rejection_reason"
                                     class="bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2.5 col-span-2">
                                    <p class="text-xs text-gray-500 mb-0.5">Rejection Reason</p>
                                    <p class="text-sm text-red-400">{{ user.landlord_application.rejection_reason }}</p>
                                </div>
                                <div class="col-span-2">
                                    <Link :href="route('admin.applications.show', user.landlord_application.id)"
                                          class="text-xs text-brand-red hover:text-brand-orange">
                                        Open full application →
                                    </Link>
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
                                <a v-for="doc in documents()" :key="doc.label"
                                   :href="`/storage/${doc.path}`" target="_blank"
                                   class="flex items-center gap-3 p-3 bg-dark-bg/60 rounded-lg hover:bg-dark-bg transition-colors">
                                    <span class="text-2xl">{{ doc.icon }}</span>
                                    <div>
                                        <p class="text-sm font-semibold text-white">{{ doc.label }}</p>
                                        <p class="text-xs text-gray-500">Click to view</p>
                                    </div>
                                    <span class="ml-auto text-xs text-brand-red">Open →</span>
                                </a>
                                <p v-if="documents().length === 0" class="text-sm text-gray-500 text-center py-4">
                                    No documents uploaded.
                                </p>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6 lg:sticky lg:top-4 lg:self-start">

                        <!-- Activity -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <ChartBarIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Activity</h3>
                            </div>
                            <div class="p-4 grid grid-cols-2 gap-3">
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Properties</p>
                                    <p class="text-lg font-semibold text-white">{{ user.properties_count }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Tour Requests</p>
                                    <p class="text-lg font-semibold text-white">{{ user.tour_requests_count }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Rentals</p>
                                    <p class="text-lg font-semibold text-white">{{ user.tenant_rentals_count }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Reviews</p>
                                    <p class="text-lg font-semibold text-white">{{ user.reviews_count }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 col-span-2">
                                    <p class="text-xs text-gray-500 mb-0.5">Subscription</p>
                                    <p class="text-sm font-semibold text-white capitalize">
                                        {{ user.active_subscription?.tier?.name || 'None' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <BoltIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Actions</h3>
                            </div>
                            <div class="p-4 space-y-2">
                                <Link :href="route('admin.users.edit', user.id)"
                                      class="w-full flex items-center justify-center gap-2 bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30 px-4 py-2.5 rounded-lg hover:bg-blue-500/30 font-medium text-sm transition-colors">
                                    <PencilSquareIcon class="w-4 h-4" />
                                    Edit User
                                </Link>
                                <button @click="loginAs"
                                        class="w-full flex items-center justify-center gap-2 bg-green-500/20 text-green-400 ring-1 ring-green-500/30 px-4 py-2.5 rounded-lg hover:bg-green-500/30 font-medium text-sm transition-colors">
                                    <ArrowRightOnRectangleIcon class="w-4 h-4" />
                                    Login as User
                                </button>
                                <button @click="deleteUser"
                                        class="w-full flex items-center justify-center gap-2 bg-red-500/20 text-red-400 ring-1 ring-red-500/30 px-4 py-2.5 rounded-lg hover:bg-red-500/30 font-medium text-sm transition-colors">
                                    <TrashIcon class="w-4 h-4" />
                                    Delete User
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
