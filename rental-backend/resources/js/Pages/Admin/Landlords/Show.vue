<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import VerificationBadge from '@/Components/VerificationBadge.vue';
import { 
    UserIcon, 
    PhoneIcon, 
    EnvelopeIcon, 
    BuildingOfficeIcon,
    DocumentCheckIcon,
    ChatBubbleLeftRightIcon,
    ArrowTopRightOnSquareIcon
} from '@heroicons/vue/24/outline';

defineProps<{
    landlord: any;
    properties: Array<any>;
}>();

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW'
    }).format(value);
};
</script>

<template>
    <Head :title="`${landlord.name} - Profile`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                    Landlord Profile
                </h2>
                <Link :href="route('admin.landlords.index')" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    &larr; Back to List
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Profile Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row gap-6">
                            <!-- Avatar/Initials -->
                            <div class="flex-shrink-0">
                                <div class="h-24 w-24 rounded-full bg-brand-blue/10 flex items-center justify-center text-3xl font-bold text-brand-blue">
                                    {{ landlord.name.charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            
                            <!-- Info -->
                            <div class="flex-grow space-y-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ landlord.name }}</h3>
                                        <VerificationBadge :level="landlord.verification_level" />
                                    </div>
                                    <p class="text-sm text-gray-500 capitalize">{{ landlord.landlord_type?.replace('_', ' ') || 'Private Landlord' }}</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                        <EnvelopeIcon class="w-5 h-5" />
                                        <a :href="`mailto:${landlord.email}`" class="hover:underline">{{ landlord.email }}</a>
                                    </div>
                                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                        <PhoneIcon class="w-5 h-5" />
                                        <a :href="`tel:${landlord.phone}`" class="hover:underline">{{ landlord.phone || 'No phone' }}</a>
                                    </div>
                                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                        <UserIcon class="w-5 h-5" />
                                        <span>Joined: {{ new Date(landlord.created_at).toLocaleDateString() }}</span>
                                    </div>
                                     <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                        <BuildingOfficeIcon class="w-5 h-5" />
                                        <span>Properties: {{ properties.length }}</span>
                                    </div>
                                </div>

                                <div class="pt-2 flex gap-3">
                                    <Link :href="route('chat.show', landlord.id)" class="inline-flex items-center px-4 py-2 bg-brand-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <ChatBubbleLeftRightIcon class="w-4 h-4 mr-2" />
                                        Chat
                                    </Link>
                                    <a v-if="landlord.verification_level === 'basic' && landlord.application_status === 'approved'" :href="`mailto:${landlord.email}?subject=Upgrade to Trusted Landlord`" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                        Suggest Upgrade
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Verification Documents -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <DocumentCheckIcon class="w-5 h-5 text-gray-500" />
                            Verification Documents
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- ID Document -->
                            <div v-if="landlord.id_document_url" class="border rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">🆔</span>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">ID Document</p>
                                            <p class="text-xs text-gray-500">Required for Basic</p>
                                        </div>
                                    </div>
                                    <a :href="`/storage/${landlord.id_document_url}`" target="_blank" class="text-brand-blue hover:text-blue-700">
                                        <ArrowTopRightOnSquareIcon class="w-5 h-5" />
                                    </a>
                                </div>
                            </div>

                             <!-- Selfie -->
                            <div v-if="landlord.selfie_url" class="border rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">📸</span>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">Selfie with ID</p>
                                            <p class="text-xs text-gray-500">Required for Trusted</p>
                                        </div>
                                    </div>
                                    <a :href="`/storage/${landlord.selfie_url}`" target="_blank" class="text-brand-blue hover:text-blue-700">
                                        <ArrowTopRightOnSquareIcon class="w-5 h-5" />
                                    </a>
                                </div>
                            </div>

                             <!-- Business/Video -->
                            <div v-if="landlord.business_registration_url" class="border rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">🏢</span>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">Business Reg</p>
                                            <p class="text-xs text-gray-500">For Agents</p>
                                        </div>
                                    </div>
                                    <a :href="`/storage/${landlord.business_registration_url}`" target="_blank" class="text-brand-blue hover:text-blue-700">
                                        <ArrowTopRightOnSquareIcon class="w-5 h-5" />
                                    </a>
                                </div>
                            </div>
                            <!-- Video -->
                             <div v-else-if="landlord.video_walkthrough_url" class="border rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">🎥</span>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">Walkthrough</p>
                                            <p class="text-xs text-gray-500">For Premium</p>
                                        </div>
                                    </div>
                                    <a :href="landlord.video_walkthrough_url" target="_blank" class="text-brand-blue hover:text-blue-700">
                                        <ArrowTopRightOnSquareIcon class="w-5 h-5" />
                                    </a>
                                </div>
                            </div>
                             <div v-else class="col-span-1 md:col-span-3 text-center py-4 bg-gray-50 dark:bg-gray-900 rounded-lg text-gray-500 text-sm italic" v-if="!landlord.id_document_url">
                                No documents uploaded.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Properties List -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                         <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <BuildingOfficeIcon class="w-5 h-5 text-gray-500" />
                            Properties ({{ properties.length }})
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reports</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="property in properties" :key="property.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0 bg-gray-100 rounded-md overflow-hidden">
                                                    <img v-if="property.images && property.images.length" :src="`/storage/${JSON.parse(property.images)[0]}`" class="h-10 w-10 object-cover">
                                                    <div v-else class="h-10 w-10 flex items-center justify-center text-gray-400">🏠</div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ property.title }}</div>
                                                    <div class="text-sm text-gray-500">{{ property.address }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatCurrency(property.price) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                             <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 capitalize"
                                                :class="{
                                                    'bg-green-100 text-green-800': property.approval_status === 'approved',
                                                    'bg-yellow-100 text-yellow-800': property.approval_status === 'pending',
                                                    'bg-red-100 text-red-800': property.approval_status === 'rejected'
                                                }">
                                                {{ property.approval_status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span v-if="property.report_count > 0" class="text-red-500 font-bold border border-red-200 bg-red-50 px-2 py-1 rounded">
                                                {{ property.report_count }}
                                            </span>
                                            <span v-else class="text-gray-400">-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ new Date(property.created_at).toLocaleDateString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a :href="route('properties.show', property.id)" target="_blank" class="text-brand-blue hover:text-blue-900 dark:hover:text-blue-300">
                                                Inspect
                                            </a>
                                        </td>
                                    </tr>
                                    <tr v-if="properties.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 italic">No properties found for this landlord.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
