<script setup lang="ts">
import DashboardCard from '@/Components/DashboardCard.vue';
import { HomeModernIcon, UserGroupIcon, UsersIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline';

// Mock Data matching Flutter
</script>

<template>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <DashboardCard
            title="Total Properties"
            value="124"
            subtitle="12 vacant"
            :icon="HomeModernIcon"
            colorClass="text-brand-red"
            trend="+12% vs last month"
            :trendUp="true"
        />
        <DashboardCard
            title="Landlords"
            value="45"
            subtitle="Active"
            :icon="UserGroupIcon"
            colorClass="text-blue-500"
             trend="+5% new this week"
            :trendUp="true"
        />
        <DashboardCard
            title="Tenants"
            value="208"
             subtitle="Active"
            :icon="UsersIcon"
            colorClass="text-green-500"
             trend="+8% active users"
            :trendUp="true"
        />
        <DashboardCard
            title="Monthly Revenue"
            value="K125.4k"
             subtitle="Total Volume"
            :icon="CurrencyDollarIcon"
            colorClass="text-purple-500"
             trend="+15% growth"
            :trendUp="true"
        />
    </div>
</template>
