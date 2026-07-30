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
        class="rounded-lg border-gray-600 bg-gray-700 text-gray-200 shadow-sm focus:border-brand-orange focus:ring-brand-orange"
        v-model="model"
        ref="input"
    />
</template>
