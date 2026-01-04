<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Properties</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="landlord in landlords" :key="landlord.id">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ landlord.name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ landlord.email }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ landlord.phone || '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 capitalize">{{ landlord.tier || '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ landlord.property_count }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ new Date(landlord.created_at).toLocaleDateString() }}
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
