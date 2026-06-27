<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout header="System Settings">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        System settings management is coming soon.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
