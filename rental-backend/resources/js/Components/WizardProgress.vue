<script setup lang="ts">
import { CheckIcon } from '@heroicons/vue/24/solid';

defineProps<{
    steps: string[];
    currentStep: number; // 1-indexed
}>();
</script>

<template>
    <ol class="flex items-start w-full">
        <li
            v-for="(label, index) in steps"
            :key="label"
            class="flex items-start"
            :class="index < steps.length - 1 ? 'flex-1' : ''"
        >
            <div class="flex flex-col items-center shrink-0 w-20 sm:w-28">
                <div
                    class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold shrink-0 transition-colors"
                    :class="index + 1 < currentStep
                        ? 'bg-brand-red text-white'
                        : index + 1 === currentStep
                            ? 'bg-brand-red/10 text-brand-red border-2 border-brand-red'
                            : 'bg-gray-700 text-gray-500 border border-gray-600'"
                >
                    <CheckIcon v-if="index + 1 < currentStep" class="w-4 h-4" />
                    <span v-else>{{ index + 1 }}</span>
                </div>
                <span
                    class="mt-2 text-xs font-medium text-center leading-tight"
                    :class="index + 1 <= currentStep ? 'text-gray-200' : 'text-gray-500'"
                >
                    {{ label }}
                </span>
            </div>

            <div
                v-if="index < steps.length - 1"
                class="flex-1 h-0.5 mt-4"
                :class="index + 1 < currentStep ? 'bg-brand-red' : 'bg-gray-700'"
            />
        </li>
    </ol>
</template>
