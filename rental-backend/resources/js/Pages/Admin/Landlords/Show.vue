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
            <div class="flex items-center justify-between w-full">
                <h2 class="text-xl font-semibold text-white">
                    Landlord Profile
                </h2>
                <Link :href="route('admin.landlords.index')" class="text-sm text-gray-400 hover:text-white">
                    &larr; Back to List
                </Link>
            </div>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">
            <!-- Profile Card -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Avatar/Initials -->
                    <div class="flex-shrink-0">
                        <div class="h-24 w-24 rounded-full bg-brand-red/10 flex items-center justify-center text-3xl font-bold text-brand-red">
                            {{ landlord.name.charAt(0).toUpperCase() }}
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="flex-grow space-y-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-2xl font-bold text-white">{{ landlord.name }}</h3>
                                <VerificationBadge :level="landlord.verification_level" />
                            </div>
                            <p class="text-sm text-gray-400 capitalize">{{ landlord.landlord_type?.replace('_', ' ') || 'Private Landlord' }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center gap-2 text-gray-300">
                                <EnvelopeIcon class="w-5 h-5 text-gray-500" />
                                <a :href="`mailto:${landlord.email}`" class="hover:underline">{{ landlord.email }}</a>
                            </div>
                            <div class="flex items-center gap-2 text-gray-300">
                                <PhoneIcon class="w-5 h-5 text-gray-500" />
                                <a :href="`tel:${landlord.phone}`" class="hover:underline">{{ landlord.phone || 'No phone' }}</a>
                            </div>
                            <div class="flex items-center gap-2 text-gray-300">
                                <UserIcon class="w-5 h-5 text-gray-500" />
                                <span>Joined: {{ new Date(landlord.created_at).toLocaleDateString() }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-300">
                                <BuildingOfficeIcon class="w-5 h-5 text-gray-500" />
                                <span>Properties: {{ properties.length }}</span>
                            </div>
                        </div>

                        <div class="pt-2 flex gap-3">
                            <Link :href="route('chat.show', landlord.id)" class="inline-flex items-center px-4 py-2 bg-brand-red hover:bg-red-700 rounded-lg font-semibold text-xs text-white uppercase tracking-widest transition-colors">
                                <ChatBubbleLeftRightIcon class="w-4 h-4 mr-2" />
                                Chat
                            </Link>
                            <a v-if="landlord.verification_level === 'basic' && landlord.application_status === 'approved'" :href="`mailto:${landlord.email}?subject=Upgrade to Trusted Landlord`" class="inline-flex items-center px-4 py-2 border border-gray-600 rounded-lg font-semibold text-xs text-gray-300 uppercase tracking-widest hover:bg-gray-700 transition-colors">
                                Suggest Upgrade
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verification Documents -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <DocumentCheckIcon class="w-5 h-5 text-gray-500" />
                    Verification Documents
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- ID Document -->
                    <div v-if="landlord.id_document_url" class="border border-gray-700 rounded-lg p-4 hover:bg-gray-700/40 transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🆔</span>
                                <div>
                                    <p class="font-medium text-white">ID Document</p>
                                    <p class="text-xs text-gray-500">Required for Basic</p>
                                </div>
                            </div>
                            <a :href="`/storage/${landlord.id_document_url}`" target="_blank" class="text-brand-red hover:text-brand-orange">
                                <ArrowTopRightOnSquareIcon class="w-5 h-5" />
                            </a>
                        </div>
                    </div>

                    <!-- Selfie -->
                    <div v-if="landlord.selfie_url" class="border border-gray-700 rounded-lg p-4 hover:bg-gray-700/40 transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">📸</span>
                                <div>
                                    <p class="font-medium text-white">Selfie with ID</p>
                                    <p class="text-xs text-gray-500">Required for Trusted</p>
                                </div>
                            </div>
                            <a :href="`/storage/${landlord.selfie_url}`" target="_blank" class="text-brand-red hover:text-brand-orange">
                                <ArrowTopRightOnSquareIcon class="w-5 h-5" />
                            </a>
                        </div>
                    </div>

                    <!-- Business/Video -->
                    <div v-if="landlord.business_registration_url" class="border border-gray-700 rounded-lg p-4 hover:bg-gray-700/40 transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🏢</span>
                                <div>
                                    <p class="font-medium text-white">Business Reg</p>
                                    <p class="text-xs text-gray-500">For Agents</p>
                                </div>
                            </div>
                            <a :href="`/storage/${landlord.business_registration_url}`" target="_blank" class="text-brand-red hover:text-brand-orange">
                                <ArrowTopRightOnSquareIcon class="w-5 h-5" />
                            </a>
                        </div>
                    </div>
                    <!-- Video -->
                    <div v-else-if="landlord.video_walkthrough_url" class="border border-gray-700 rounded-lg p-4 hover:bg-gray-700/40 transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🎥</span>
                                <div>
                                    <p class="font-medium text-white">Walkthrough</p>
                                    <p class="text-xs text-gray-500">For Premium</p>
                                </div>
                            </div>
                            <a :href="landlord.video_walkthrough_url" target="_blank" class="text-brand-red hover:text-brand-orange">
                                <ArrowTopRightOnSquareIcon class="w-5 h-5" />
                            </a>
                        </div>
                    </div>
                    <div v-else class="col-span-1 md:col-span-3 text-center py-4 bg-gray-900/40 rounded-lg text-gray-500 text-sm italic" v-if="!landlord.id_document_url">
                        No documents uploaded.
                    </div>
                </div>
            </div>

            <!-- Properties List -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
                <div class="px-6 py-4 border-b border-gray-700">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <BuildingOfficeIcon class="w-5 h-5 text-gray-500" />
                        Properties ({{ properties.length }})
                    </h3>
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">Property</th>
                            <th class="px-4 py-3 font-medium">Price</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Reports</th>
                            <th class="px-4 py-3 font-medium">Added</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="property in properties" :key="property.id" class="hover:bg-gray-700/40">
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0 bg-gray-900 rounded-md overflow-hidden">
                                        <img v-if="property.images && property.images.length" :src="`/storage/${JSON.parse(property.images)[0]}`" class="h-10 w-10 object-cover">
                                        <div v-else class="h-10 w-10 flex items-center justify-center text-gray-600">🏠</div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="font-medium text-white capitalize">{{ property.title }}</div>
                                        <div class="text-gray-500 text-xs">{{ property.address }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-300">
                                {{ formatCurrency(property.price) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs capitalize"
                                    :class="{
                                        'bg-green-500/20 text-green-400': property.approval_status === 'approved',
                                        'bg-yellow-500/20 text-yellow-400': property.approval_status === 'pending',
                                        'bg-red-500/20 text-red-400': property.approval_status === 'rejected'
                                    }">
                                    {{ property.approval_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span v-if="property.report_count > 0" class="text-red-400 font-bold bg-red-500/10 px-2 py-1 rounded">
                                    {{ property.report_count }}
                                </span>
                                <span v-else class="text-gray-500">-</span>
                            </td>
                            <td class="px-4 py-3 text-gray-400">
                                {{ new Date(property.created_at).toLocaleDateString() }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a :href="route('properties.show', property.id)" target="_blank" class="text-brand-red hover:text-brand-orange">
                                    Inspect
                                </a>
                            </td>
                        </tr>
                        <tr v-if="properties.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 italic">No properties found for this landlord.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
