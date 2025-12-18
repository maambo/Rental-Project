<script setup lang="ts">
import { onMounted, ref } from 'vue';

const model = defineModel<string>({ required: true });

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="rounded-md border-gray-300 shadow-sm focus:border-brand-orange focus:ring-brand-orange dark:border-gray-700 dark:bg-dark-bg dark:text-gray-300 dark:focus:border-brand-orange dark:focus:ring-brand-orange"
        v-model="model"
        ref="input"
    />
</template>
