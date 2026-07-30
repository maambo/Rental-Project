<script setup lang="ts">
import { ShieldCheckIcon, CheckBadgeIcon, StarIcon } from '@heroicons/vue/24/solid';

defineProps<{
    level: 'basic' | 'trusted' | 'premium';
}>();
</script>

<template>
    <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium"
        :class="{
            'bg-gray-700 text-gray-300': level === 'basic',
            'bg-brand-info/10 text-brand-info': level === 'trusted',
            'bg-brand-warning/10 text-brand-warning': level === 'premium'
        }"
    >
        <ShieldCheckIcon v-if="level === 'basic'" class="w-3.5 h-3.5" />
        <CheckBadgeIcon v-if="level === 'trusted'" class="w-3.5 h-3.5" />
        <StarIcon v-if="level === 'premium'" class="w-3.5 h-3.5" />
        
        <span v-if="level === 'basic'">Basic Verified</span>
        <span v-if="level === 'trusted'">Trusted Landlord</span>
        <span v-if="level === 'premium'">Premium Member</span>
    </div>
</template>
