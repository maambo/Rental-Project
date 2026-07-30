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
            <h2 class="text-xl font-semibold text-white">Landlord Profiles</h2>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Phone</th>
                            <th class="px-4 py-3 font-medium">Verification</th>
                            <th class="px-4 py-3 font-medium">Properties</th>
                            <th class="px-4 py-3 font-medium">Joined</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="landlord in landlords" :key="landlord.id" class="hover:bg-gray-700/40">
                            <td class="px-4 py-3 font-medium">
                                <Link :href="route('admin.landlords.show', landlord.id)" class="text-brand-red hover:text-brand-orange hover:underline">
                                    {{ landlord.name }}
                                </Link>
                            </td>
                            <td class="px-4 py-3 text-gray-400">{{ landlord.email }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ landlord.phone || '-' }}</td>
                            <td class="px-4 py-3 text-gray-400 capitalize">
                                <VerificationBadge :level="landlord.verification_level" />
                            </td>
                            <td class="px-4 py-3 text-gray-400">{{ landlord.property_count }}</td>
                            <td class="px-4 py-3 text-gray-400">
                                {{ new Date(landlord.created_at).toLocaleDateString() }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Link :href="route('chat.show', landlord.id)" class="text-brand-red hover:text-brand-orange inline-flex items-center gap-1">
                                    <ChatBubbleLeftRightIcon class="w-5 h-5" />
                                    Chat
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
