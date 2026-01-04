<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ArrowLeftIcon, CheckIcon, XMarkIcon, ClockIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    application: any;
}>();

const statusColors = {
    pending: 'bg-yellow-100 text-yellow-800',
    under_review: 'bg-blue-100 text-blue-800',
   approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
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

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.applications.index')" class="text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="w-6 h-6" />
                    </Link>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Application Details</h2>
                </div>
                <span :class="['px-3 py-1 text-sm rounded-full', statusColors[application.status]]">
                    {{ application.status.replace('_', ' ').toUpperCase() }}
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Applicant Information -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Applicant Information</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Full Name</label>
                                        <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ application.user_name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Email Address</label>
                                        <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ application.user_email }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">NRC/Passport Number</label>
                                        <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ application.nrc_passport }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Tier Selection</label>
                                        <p class="mt-1 text-lg text-gray-900 dark:text-white capitalize">
                                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full">{{ application.tier }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Address Information</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Street Address</label>
                                        <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ application.address }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Town/City</label>
                                            <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ application.town }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Province</label>
                                            <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ application.province }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Documents -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Uploaded Documents</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-3">
                                    <a :href="`/storage/${application.id_document_url}`" target="_blank" 
                                       class="flex items-center gap-3 p-4 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <span class="text-3xl">📄</span>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">ID Document</p>
                                            <p class="text-sm text-gray-500">Click to view</p>
                                        </div>
                                    </a>
                                    <a :href="`/storage/${application.proof_of_address_url}`" target="_blank" 
                                       class="flex items-center gap-3 p-4 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <span class="text-3xl">📄</span>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">Proof of Address</p>
                                            <p class="text-sm text-gray-500">Click to view</p>
                                        </div>
                                    </a>
                                    <a v-if="application.tax_certificate_url" :href="`/storage/${application.tax_certificate_url}`" target="_blank" 
                                       class="flex items-center gap-3 p-4 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <span class="text-3xl">📄</span>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">Tax Certificate</p>
                                            <p class="text-sm text-gray-500">Click to view</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Application Status -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Current Status</label>
                                    <p class="mt-1">
                                        <span :class="['px-3 py-1 text-sm rounded-full', statusColors[application.status]]">
                                            {{ application.status.replace('_', ' ') }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Applied On</label>
                                    <p class="mt-1 text-gray-900 dark:text-white">{{ new Date(application.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                                </div>
                                <div v-if="application.reviewed_at">
                                    <label class="block text-sm font-medium text-gray-500">Reviewed On</label>
                                    <p class="mt-1 text-gray-900 dark:text-white">{{ new Date(application.reviewed_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                                </div>
                                <div v-if="application.rejection_reason">
                                    <label class="block text-sm font-medium text-gray-500">Rejection Reason</label>
                                    <p class="mt-1 text-red-600">{{ application.rejection_reason }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div v-if="application.status === 'pending' || application.status === 'under_review'" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Actions</h3>
                            </div>
                            <div class="p-6 space-y-3">
                                <button
                                    @click="() => router.post(route('admin.applications.under-review', application.id))"
                                    class="w-full flex items-center justify-center gap-2 bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 font-medium"
                                >
                                    <ClockIcon class="w-5 h-5" />
                                    Mark as Under Review
                                </button>
                                <button
                                    @click="approve"
                                    class="w-full flex items-center justify-center gap-2 bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 font-medium"
                                >
                                    <CheckIcon class="w-5 h-5" />
                                    Approve Application
                                </button>
                                <button
                                    @click="reject"
                                    class="w-full flex items-center justify-center gap-2 bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 font-medium"
                                >
                                    <XMarkIcon class="w-5 h-5" />
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
