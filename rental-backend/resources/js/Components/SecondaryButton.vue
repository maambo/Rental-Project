<script setup lang="ts">
withDefaults(
    defineProps<{
        type?: 'button' | 'submit' | 'reset';
    }>(),
    {
        type: 'button',
    },
);
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-lg border border-gray-600 bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-300 shadow-sm transition duration-150 ease-in-out hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-red focus:ring-offset-2 disabled:opacity-25 focus:ring-offset-gray-800"
    >
        <slot />
    </button>
</template>
