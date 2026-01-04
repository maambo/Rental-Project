<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ArrowLeftIcon, CheckIcon, XMarkIcon, XCircleIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';

const props = defineProps<{
    property: any;
}>();

const selectedImage = ref<string | null>(null);

const openImage = (imageUrl: string) => {
    selectedImage.value = imageUrl;
};

const closeImage = () => {
    selectedImage.value = null;
};

const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    under_review: 'bg-blue-100 text-blue-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
};

const approve = () => {
    if (confirm('Approve this property?')) {
        router.post(route('admin.properties.approve', props.property.id));
    }
};

const reject = () => {
    const reason = prompt('Rejection reason:');
    if (reason) {
        router.post(route('admin.properties.reject', props.property.id), { rejection_reason: reason });
    }
};
</script>

<template>
    <Head title="Property Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.properties.index')" class="text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="w-6 h-6" />
                    </Link>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Property Details</h2>
                </div>
                <span :class="['px-3 py-1 text-sm rounded-full', statusColors[property.approval_status]]">
                    {{ property.approval_status.toUpperCase() }}
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Basic Information</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Property Code</label>
                                        <p class="mt-1 text-lg font-mono text-gray-900 dark:text-white">{{ property.code }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Price</label>
                                        <p class="mt-1 text-2xl font-bold text-brand-red">K{{ Number(property.price).toLocaleString() }}</p>
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-500">Title</label>
                                        <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ property.title }}</p>
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-500">Description</label>
                                        <p class="mt-1 text-gray-900 dark:text-white leading-relaxed">{{ property.description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Location</h3>
                            </div>
                            <div class="p-6">
                                <div class="flex items-start gap-3">
                                    <span class="text-2xl">📍</span>
                                    <div>
                                        <p class="text-lg text-gray-900 dark:text-white">{{ property.location }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Property Details -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Property Details</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-3 gap-6">
                                    <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <p class="text-3xl mb-2">🛏️</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ property.bedrooms }}</p>
                                        <p class="text-sm text-gray-500">Bedrooms</p>
                                    </div>
                                    <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <p class="text-3xl mb-2">🚿</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ property.bathrooms }}</p>
                                        <p class="text-sm text-gray-500">Bathrooms</p>
                                    </div>
                                    <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <p class="text-3xl mb-2">📐</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ property.square_feet || property.sqft || 'N/A' }}</p>
                                        <p class="text-sm text-gray-500">Square Feet</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Property Images -->
                        <div v-if="property.images && property.images.length > 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Property Images</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    <div 
                                        v-for="(image, index) in property.images" 
                                        :key="image.id" 
                                        class="relative aspect-video rounded-lg overflow-hidden group cursor-pointer hover:opacity-90 transition"
                                        @click="openImage('/storage/' + image.image_url)"
                                    >
                                        <img :src="'/storage/' + image.image_url" :alt="`Property image ${index + 1}`" class="w-full h-full object-cover" />
                                        <div v-if="image.is_primary" class="absolute top-2 left-2 bg-brand-red text-white text-xs px-2 py-1 rounded">
                                            Primary
                                        </div>
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition flex items-center justify-center">
                                            <span class="text-white opacity-0 group-hover:opacity-100 transition text-sm font-medium">Click to view</span>
                                        </div>
                                    </div>
                                </div>
                                <p v-if="!property.images || property.images.length === 0" class="text-gray-500 text-center py-4">No images uploaded</p>
                            </div>
                        </div>

                        <!-- Landlord Information -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Landlord Information</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Name</label>
                                        <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ property.landlord?.name || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Email</label>
                                        <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ property.landlord?.email || 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Approval Status -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Approval Status</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Current Status</label>
                                    <p class="mt-1">
                                        <span :class="['px-3 py-1 text-sm rounded-full', statusColors[property.approval_status]]">
                                            {{ property.approval_status }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Submitted On</label>
                                    <p class="mt-1 text-gray-900 dark:text-white">{{ new Date(property.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                                </div>
                                <div v-if="property.approved_date">
                                    <label class="block text-sm font-medium text-gray-500">Approved On</label>
                                    <p class="mt-1 text-gray-900 dark:text-white">{{ new Date(property.approved_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                                </div>
                                <div v-if="property.rejection_reason">
                                    <label class="block text-sm font-medium text-gray-500">Rejection Reason</label>
                                    <p class="mt-1 text-red-600">{{ property.rejection_reason }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Info -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Info</h3>
                            </div>
                            <div class="p-6 space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Property ID</span>
                                    <span class="text-gray-900 dark:text-white font-medium">#{{ property.id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Listed</span>
                                    <span class="text-gray-900 dark:text-white font-medium">{{ new Date(property.created_at).toLocaleDateString() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Last Updated</span>
                                    <span class="text-gray-900 dark:text-white font-medium">{{ new Date(property.updated_at).toLocaleDateString() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div v-if="property.approval_status === 'pending' || property.approval_status === 'under_review'" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Actions</h3>
                            </div>
                            <div class="p-6 space-y-3">
                                <button
                                    @click="approve"
                                    class="w-full flex items-center justify-center gap-2 bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 font-medium"
                                >
                                    <CheckIcon class="w-5 h-5" />
                                    Approve Property
                                </button>
                                <button
                                    @click="reject"
                                    class="w-full flex items-center justify-center gap-2 bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 font-medium"
                                >
                                    <XMarkIcon class="w-5 h-5" />
                                    Reject Property
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Image Lightbox Modal -->
    <div v-if="selectedImage" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90" @click="closeImage">
        <button @click="closeImage" class="absolute top-4 right-4 text-white hover:text-gray-300 transition">
            <XCircleIcon class="w-10 h-10" />
        </button>
        <img :src="selectedImage" alt="Property image" class="max-w-[90vw] max-h-[90vh] object-contain" @click.stop />
    </div>
</template>
