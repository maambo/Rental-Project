<script setup lang="ts">
defineProps<{
    title: string;
    value: string;
    icon: any; // Heroicon component
    colorClass: string; // e.g., 'text-brand-red', 'text-blue-500'
    subtitle?: string;
    trend?: string;
    trendUp?: boolean;
}>();
</script>

<template>
    <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-light-bg">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ title }}
                </p>
                <h3 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                    {{ value }}
                </h3>
            </div>
            <div
                class="rounded-lg p-3"
                :class="[colorClass.replace('text-', 'bg-').replace('500', '100') + '/10']"
            >
                <component :is="icon" class="h-6 w-6" :class="colorClass" />
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between">
            <p v-if="subtitle" class="text-sm text-gray-500 dark:text-gray-400">
                {{ subtitle }}
            </p>
            
            <div v-if="trend" class="flex items-center text-sm">
                <span
                    :class="trendUp ? 'text-green-500' : 'text-red-500'"
                    class="font-medium"
                >
                    {{ trend }}
                </span>
                <svg
                    v-if="trendUp"
                    class="ml-1 h-4 w-4 text-green-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
                 <svg
                    v-else
                    class="ml-1 h-4 w-4 text-red-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </div>
        </div>
    </div>
</template>
