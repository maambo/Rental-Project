<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    variant?: 'success' | 'danger' | 'warning' | 'info' | 'brand' | 'neutral';
    pill?: boolean;
    size?: 'sm' | 'md';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'neutral',
    pill: false,
    size: 'md',
});

// Base styles for the badge
const baseStyles = 'inline-flex items-center font-medium border';

// Color classes mapping
const variantStyles = {
    success: 'bg-emerald-50 text-emerald-700 border-emerald-200/60 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-800/30',
    danger: 'bg-red-50 text-red-700 border-red-200/60 dark:bg-red-950/30 dark:text-red-400 dark:border-red-800/30',
    warning: 'bg-amber-50 text-amber-700 border-amber-200/60 dark:bg-amber-950/30 dark:text-amber-400 dark:border-amber-800/30',
    info: 'bg-blue-50 text-blue-700 border-blue-200/60 dark:bg-blue-950/30 dark:text-blue-400 dark:border-blue-800/30',
    brand: 'bg-red-50/50 text-brand-red border-brand-red/20 dark:bg-red-950/20 dark:text-brand-orange dark:border-brand-orange/20',
    neutral: 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-800/50 dark:text-gray-300 dark:border-gray-700/60',
};

// Size styles mapping
const sizeStyles = {
    sm: 'px-1.5 py-0.5 text-[10px]',
    md: 'px-2.5 py-0.5 text-xs',
};

// Shape classes mapping
const shapeStyles = computed(() => {
    return props.pill ? 'rounded-full' : 'rounded-md';
});

// Combine classes
const badgeClasses = computed(() => {
    return `${baseStyles} ${variantStyles[props.variant]} ${sizeStyles[props.size]} ${shapeStyles.value}`;
});
</script>

<template>
    <span :class="badgeClasses">
        <slot />
    </span>
</template>
