<script setup lang="ts">
withDefaults(defineProps<{
    label?: string;
}>(), {
    label: 'Or continue with email',
});
</script>

<template>
    <div class="relative flex py-2 items-center">
        <div class="flex-grow border-t border-gray-600"></div>
        <span class="flex-shrink-0 mx-4 text-gray-400 text-sm">{{ label }}</span>
        <div class="flex-grow border-t border-gray-600"></div>
    </div>
</template>
