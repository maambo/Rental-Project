<script setup lang="ts">
import PublicHeader from '@/Components/PublicHeader.vue';
</script>

<template>
    <div class="flex min-h-screen flex-col bg-dark-bg">
        <PublicHeader />

        <div class="flex flex-1 flex-col items-center justify-center px-4 py-8">
        <div
            class="w-full overflow-hidden bg-gray-800 border border-gray-700 px-6 py-4 sm:max-w-md sm:rounded-xl"
        >
            <slot />
        </div>
        </div>
    </div>
</template>
