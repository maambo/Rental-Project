<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import VerificationBadge from '@/Components/VerificationBadge.vue';
import { ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    landlords: Array<any>;
}>();
</script>

<template>
    <Head title="Landlord Profiles" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Landlord Profiles</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Verification</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Properties</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="landlord in landlords" :key="landlord.id">
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <Link :href="route('admin.landlords.show', landlord.id)" class="text-brand-blue hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                                            {{ landlord.name }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ landlord.email }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ landlord.phone || '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 capitalize">
                                        <VerificationBadge :level="landlord.verification_level" />
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ landlord.property_count }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ new Date(landlord.created_at).toLocaleDateString() }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <Link :href="route('chat.show', landlord.id)" class="text-brand-blue hover:text-blue-900 dark:hover:text-blue-300 inline-flex items-center gap-1">
                                            <ChatBubbleLeftRightIcon class="w-5 h-5" />
                                            Chat
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
