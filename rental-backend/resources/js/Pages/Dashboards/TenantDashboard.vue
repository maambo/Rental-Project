<script setup lang="ts">
import DashboardCard from '@/Components/DashboardCard.vue';
import { HomeModernIcon, ChatBubbleLeftRightIcon, WrenchScrewdriverIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline';

</script>

<template>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <DashboardCard
            title="Current Rentals"
            value="2"
            subtitle="Active properties"
            :icon="HomeModernIcon"
            colorClass="text-brand-red"
        />
        <DashboardCard
             title="Messages"
            value="5"
             subtitle="Unread"
            :icon="ChatBubbleLeftRightIcon"
            colorClass="text-blue-500"
            trend="2 new"
            :trendUp="true"
        />
        <DashboardCard
             title="Maintenance"
            value="3"
             subtitle="Scheduled"
            :icon="WrenchScrewdriverIcon"
            colorClass="text-brand-orange"
        />
        <DashboardCard
             title="Rent Due"
            value="K3.5k"
            subtitle="Due in 5 days"
            :icon="CurrencyDollarIcon"
            colorClass="text-green-500"
             trend="On time"
            :trendUp="true"
        />
    </div>
</template>
