<script setup lang="ts">
import DashboardCard from '@/Components/DashboardCard.vue';
import { HomeModernIcon, ChatBubbleLeftRightIcon, ClipboardDocumentListIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline';

</script>

<template>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <DashboardCard
            title="My Properties"
            value="5"
            subtitle="3 occupied"
            :icon="HomeModernIcon"
            colorClass="text-brand-red"
            trend="+1 this month"
            :trendUp="true"
        />
        <DashboardCard
             title="Pending Tours"
            value="8"
             subtitle="Awaiting response"
            :icon="ClipboardDocumentListIcon"
            colorClass="text-brand-orange"
            trend="3 new today"
            :trendUp="true"
        />
        <DashboardCard
             title="Monthly Revenue"
            value="K42.5k"
             subtitle="From 3 properties"
            :icon="CurrencyDollarIcon"
            colorClass="text-green-500"
            trend="+12% vs last month"
            :trendUp="true"
        />
        <DashboardCard
            title="Unread Messages"
            value="12"
            subtitle="From Tenants"
            :icon="ChatBubbleLeftRightIcon"
            colorClass="text-blue-500"
             trend="4 new"
            :trendUp="false"
        />
    </div>
</template>
