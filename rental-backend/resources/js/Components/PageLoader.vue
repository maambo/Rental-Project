<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';

// `router.on('start'/'finish')` fires for page visits AND for `useForm().post/put/...`,
// since the form helpers call `router.visit` under the hood — one listener covers both.
const SHOW_DELAY_MS = 250;

const visible = ref(false);
let showTimer: ReturnType<typeof setTimeout> | null = null;

const clearShowTimer = () => {
    if (showTimer) {
        clearTimeout(showTimer);
        showTimer = null;
    }
};

const handleStart = () => {
    clearShowTimer();
    showTimer = setTimeout(() => {
        visible.value = true;
    }, SHOW_DELAY_MS);
};

const handleFinish = () => {
    clearShowTimer();
    visible.value = false;
};

let stopStart: (() => void) | undefined;
let stopFinish: (() => void) | undefined;

onMounted(() => {
    stopStart = router.on('start', handleStart);
    stopFinish = router.on('finish', handleFinish);
});

onUnmounted(() => {
    stopStart?.();
    stopFinish?.();
    clearShowTimer();
});
</script>

<template>
    <Transition name="page-loader-fade">
        <div
            v-if="visible"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm"
            role="status"
            aria-live="polite"
            aria-label="Loading"
        >
            <LoadingSpinner size="lg" class="text-brand-red" />
        </div>
    </Transition>
</template>

<style scoped>
.page-loader-fade-enter-active,
.page-loader-fade-leave-active {
    transition: opacity 0.15s ease;
}
.page-loader-fade-enter-from,
.page-loader-fade-leave-to {
    opacity: 0;
}
</style>
