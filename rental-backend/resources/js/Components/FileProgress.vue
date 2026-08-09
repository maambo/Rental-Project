<script setup lang="ts">
interface FileState {
    name: string;
    sizeMB: number;
    pct: number;
    tooLarge: boolean;
}

defineProps<{
    state?: FileState;
    maxMb?: number;
}>();
</script>

<template>
    <div v-if="state" class="mt-2">
        <div class="flex items-center justify-between text-xs mb-1">
            <span :class="state.tooLarge ? 'text-red-400' : 'text-gray-400'" class="truncate max-w-[70%]">
                {{ state.name }}
            </span>
            <span :class="state.tooLarge ? 'text-red-400 font-semibold' : 'text-gray-500'">
                {{ state.sizeMB.toFixed(2) }} MB / {{ maxMb ?? 5 }} MB
            </span>
        </div>
        <div class="w-full h-1.5 rounded-full bg-gray-700 overflow-hidden">
            <div
                class="h-full rounded-full transition-all duration-300"
                :class="state.tooLarge ? 'bg-red-500' : 'bg-brand-red'"
                :style="{ width: state.pct + '%' }"
            />
        </div>
        <p v-if="state.tooLarge" class="mt-1 text-xs text-red-400">
            File exceeds the {{ maxMb ?? 5 }} MB limit — please choose a smaller file.
        </p>
    </div>
</template>
