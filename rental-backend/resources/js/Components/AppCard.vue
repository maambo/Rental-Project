<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

interface Props {
    variant?: 'flat' | 'elevated' | 'bordered' | 'glassmorphic';
    clickable?: boolean;
    hoverEffect?: boolean;
    href?: string;
    method?: 'get' | 'post' | 'put' | 'patch' | 'delete';
    as?: string;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'flat',
    clickable: false,
    hoverEffect: false,
});

// Base styles for the card wrapper
const baseStyles = 'rounded-xl overflow-hidden transition-all duration-300 block text-left';

// Variant styles
const variantStyles = {
    flat: 'bg-white dark:bg-light-bg border border-gray-100 dark:border-gray-800/60',
    elevated: 'bg-white dark:bg-light-bg shadow-sm dark:shadow-md border border-transparent',
    bordered: 'bg-white dark:bg-light-bg border border-gray-200 dark:border-gray-700/80',
    glassmorphic: 'bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border border-white/20 dark:border-gray-800/40 shadow-lg',
};

// Hover dynamic classes
const hoverStyles = computed(() => {
    if (!props.clickable && !props.hoverEffect) return '';
    
    let styles = 'cursor-pointer transform hover:-translate-y-1 ';
    
    if (props.variant === 'elevated' || props.variant === 'glassmorphic') {
        styles += 'hover:shadow-lg dark:hover:shadow-xl';
    } else {
        styles += 'hover:border-brand-orange/40 dark:hover:border-brand-orange/30 hover:shadow-md';
    }
    
    return styles;
});

// Combine classes
const cardClasses = computed(() => {
    return `${baseStyles} ${variantStyles[props.variant]} ${hoverStyles.value}`;
});
</script>

<template>
    <!-- Card as a Routing Link -->
    <Link
        v-if="href"
        :href="href"
        :method="method"
        :as="as"
        :class="cardClasses"
        class="no-underline text-inherit"
    >
        <!-- Header -->
        <div v-if="$slots.header" class="border-b border-gray-100 dark:border-gray-800/60 px-5 py-4">
            <slot name="header" />
        </div>

        <!-- Body -->
        <div class="p-5">
            <slot />
        </div>

        <!-- Footer -->
        <div v-if="$slots.footer" class="border-t border-gray-100 dark:border-gray-800/60 bg-gray-50/50 dark:bg-gray-800/20 px-5 py-4">
            <slot name="footer" />
        </div>
    </Link>

    <!-- Standard Card Container -->
    <div v-else :class="cardClasses">
        <!-- Header -->
        <div v-if="$slots.header" class="border-b border-gray-100 dark:border-gray-800/60 px-5 py-4">
            <slot name="header" />
        </div>

        <!-- Body -->
        <div class="p-5">
            <slot />
        </div>

        <!-- Footer -->
        <div v-if="$slots.footer" class="border-t border-gray-100 dark:border-gray-800/60 bg-gray-50/50 dark:bg-gray-800/20 px-5 py-4">
            <slot name="footer" />
        </div>
    </div>
</template>
