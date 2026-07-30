<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout header="System Settings">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <p class="text-gray-400 text-sm">
                    System settings management is coming soon.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
