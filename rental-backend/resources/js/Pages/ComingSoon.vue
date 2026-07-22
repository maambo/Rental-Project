<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ClockIcon } from '@heroicons/vue/24/outline';

defineProps<{ feature: string }>();
</script>

<template>
    <Head :title="feature" />
    <AuthenticatedLayout :header="feature">
        <div class="py-12">
            <div class="max-w-xl mx-auto px-4 text-center">
                <div class="bg-light-bg rounded-xl p-12">
                    <ClockIcon class="w-16 h-16 mx-auto text-gray-600 mb-4" />
                    <h2 class="text-xl font-bold text-white mb-2">Coming Soon</h2>
                    <p class="text-gray-400 text-sm">
                        The <span class="text-brand-red font-medium">{{ feature }}</span>
                        feature is under development and will be available soon.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
