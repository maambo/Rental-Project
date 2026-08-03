<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">
                Profile
            </h2>
        </template>

        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 sm:p-8">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 sm:p-8">
                    <UpdatePasswordForm />
                </div>

                <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 sm:p-8 lg:col-span-2">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
