<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

interface Props {
    variant?: 'primary' | 'secondary' | 'danger' | 'success' | 'outline' | 'ghost' | 'link';
    size?: 'xs' | 'sm' | 'md' | 'lg';
    loading?: boolean;
    disabled?: boolean;
    href?: string;
    type?: 'button' | 'submit' | 'reset';
    method?: 'get' | 'post' | 'put' | 'patch' | 'delete';
    as?: string;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    size: 'md',
    loading: false,
    disabled: false,
    type: 'button',
});

// Base styles for the button
const baseStyles = 'inline-flex items-center justify-center font-semibold tracking-wide transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-dark-bg disabled:opacity-50 disabled:cursor-not-allowed';

// Variant-specific styles
const variantStyles = {
    primary: 'bg-gradient-to-r from-brand-red to-brand-orange text-white hover:brightness-110 active:brightness-95 focus:ring-brand-red shadow-sm border border-transparent',
    secondary: 'bg-white border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-brand-red dark:bg-gray-800 shadow-sm',
    danger: 'bg-brand-danger text-white hover:bg-red-600 active:bg-red-700 focus:ring-brand-danger shadow-sm border border-transparent',
    success: 'bg-brand-success text-white hover:bg-emerald-600 active:bg-emerald-700 focus:ring-brand-success shadow-sm border border-transparent',
    outline: 'border border-brand-orange text-brand-orange hover:bg-brand-orange/5 dark:hover:bg-brand-orange/10 focus:ring-brand-orange bg-transparent',
    ghost: 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 focus:ring-brand-red bg-transparent border border-transparent',
    link: 'text-brand-orange hover:underline focus:ring-brand-orange bg-transparent p-0 border-0 focus:ring-0 focus:ring-offset-0',
};

// Size-specific styles
const sizeStyles = {
    xs: 'px-2 py-1 text-xs rounded',
    sm: 'px-3 py-1.5 text-xs rounded-md',
    md: 'px-4 py-2 text-sm rounded-md',
    lg: 'px-5 py-3 text-base rounded-lg',
};

// Compute final combined classes
const buttonClasses = computed(() => {
    if (props.variant === 'link') {
        return `inline-flex items-center text-brand-orange hover:underline focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed ${props.disabled || props.loading ? 'pointer-events-none' : ''}`;
    }
    return `${baseStyles} ${variantStyles[props.variant]} ${sizeStyles[props.size]}`;
});

const isDisabled = computed(() => props.disabled || props.loading);
</script>

<template>
    <!-- Inertia Link version -->
    <Link
        v-if="href"
        :href="href"
        :method="method"
        :as="as"
        :class="[buttonClasses, { 'pointer-events-none': isDisabled }]"
        class="no-underline"
    >
        <svg
            v-if="loading"
            class="mr-2 h-4 w-4 animate-spin text-current"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            />
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
        </svg>
        <slot />
    </Link>

    <!-- Standard Button version -->
    <button
        v-else
        :type="type"
        :disabled="isDisabled"
        :class="buttonClasses"
    >
        <svg
            v-if="loading"
            class="mr-2 h-4 w-4 animate-spin text-current"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            />
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
        </svg>
        <slot />
    </button>
</template>
