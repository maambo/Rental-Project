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
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Member Benefits</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-8 text-center">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Upgrade Your Experience</h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Choose the verification level that suits your needs.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div 
                        v-for="tier in tiers" 
                        :key="tier.id"
                        class="relative rounded-2xl border p-8 shadow-sm flex flex-col transition-transform hover:-translate-y-1"
                        :class="[tier.styling?.color, tier.styling?.bg]"
                    >
                        <div class="mb-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ tier.display_name }}</h4>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ tier.price_display }}</p>
                        </div>
                        
                        <ul class="space-y-4 mb-8 flex-1">
                            <li v-for="feature in tier.features" :key="feature" class="flex items-start">
                                <CheckCircleIcon :class="['h-6 w-6 mr-3 flex-shrink-0', tier.styling?.checkColor]" />
                                <span class="text-gray-600 dark:text-gray-300">{{ feature }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
