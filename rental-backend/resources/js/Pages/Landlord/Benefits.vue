<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { CheckCircleIcon } from '@heroicons/vue/24/solid';

const props = defineProps<{
    tiers: Array<any>;
}>();

</script>

<template>
    <Head title="Member Benefits" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Member Benefits</h2>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-8 text-center">
                <h3 class="text-2xl font-bold text-white">Upgrade Your Experience</h3>
                <p class="mt-2 text-gray-400">Choose the verification level that suits your needs.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="tier in tiers"
                    :key="tier.id"
                    class="relative rounded-xl border border-gray-700 bg-gray-800 p-8 flex flex-col transition-transform hover:-translate-y-1"
                >
                    <div class="mb-4">
                        <h4 class="text-lg font-semibold text-white">{{ tier.display_name }}</h4>
                        <p class="mt-2 text-3xl font-bold text-white">{{ tier.price_display }}</p>
                    </div>

                    <ul class="space-y-4 mb-8 flex-1">
                        <li v-for="feature in tier.features" :key="feature" class="flex items-start">
                            <CheckCircleIcon class="h-6 w-6 mr-3 flex-shrink-0 text-brand-red" />
                            <span class="text-gray-300">{{ feature }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
