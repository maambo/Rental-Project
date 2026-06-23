<script setup lang="ts">
import DashboardCard from '@/Components/DashboardCard.vue';
import { HomeModernIcon, ChatBubbleLeftRightIcon, WrenchScrewdriverIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    stats: {
        activeRentals: number;
        unreadMessages: number;
        maintenanceRequests: number;
        rentDue: number;
    }
}>();

</script>

<template>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <DashboardCard
            title="Active Rentals"
            :value="stats.activeRentals.toString()"
            subtitle="Properties you stay in"
            :icon="HomeModernIcon"
            colorClass="text-brand-red"
        />
        <DashboardCard
             title="Messages"
            :value="stats.unreadMessages.toString()"
             subtitle="Unread"
            :icon="ChatBubbleLeftRightIcon"
            colorClass="text-blue-500"
            :trend="stats.unreadMessages > 0 ? stats.unreadMessages + ' new' : 'No new'"
            :trendUp="stats.unreadMessages > 0"
        />
        <DashboardCard
             title="Maintenance"
            :value="stats.maintenanceRequests.toString()"
             subtitle="Scheduled"
            :icon="WrenchScrewdriverIcon"
            colorClass="text-brand-orange"
        />
        <DashboardCard
             title="Rent Due"
            :value="'K' + stats.rentDue.toLocaleString()"
            subtitle="Current balance"
            :icon="CurrencyDollarIcon"
            colorClass="text-green-500"
             trend="Based on bills"
            :trendUp="false"
        />
    </div>
</template>
