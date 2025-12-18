<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminDashboard from '@/Pages/Dashboards/AdminDashboard.vue';
import LandlordDashboard from '@/Pages/Dashboards/LandlordDashboard.vue';
import TenantDashboard from '@/Pages/Dashboards/TenantDashboard.vue';

const page = usePage();
// @ts-ignore
const user = computed(() => page.props.auth.user);
const role = computed(() => user.value?.role || 'tenant');

const welcomeMessage = computed(() => {
    if (role.value === 'admin') return 'Welcome, Admin!';
    if (role.value === 'landlord') return `Welcome, ${user.value.name}!`;
    return `Welcome back, ${user.value.name}!`;
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Dashboard
            </h2>
        </template>

        <div class="space-y-6">
            <!-- Welcome Banner -->
            <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-brand-red to-brand-orange p-8 text-white shadow-lg">
                <div class="relative z-10">
                    <h1 class="text-3xl font-bold">{{ welcomeMessage }}</h1>
                    <p class="mt-2 text-white/90">Here's what's happening with your rentals today.</p>
                </div>
                <!-- Decorative circle -->
                <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
            </div>

            <!-- Role Dashboards -->
            <AdminDashboard v-if="role === 'admin'" />
            <LandlordDashboard v-else-if="role === 'landlord'" />
            <TenantDashboard v-else />
        </div>
    </AuthenticatedLayout>
</template>
