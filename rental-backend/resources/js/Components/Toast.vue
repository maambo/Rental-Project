<script setup lang="ts">
import { computed, watch, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircleIcon, XCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const page = usePage();
const show = ref(false);
const message = ref('');
const type = ref<'success' | 'error'>('success');

watch(() => page.props.flash, (flash: any) => {
    if (flash?.success) {
        type.value = 'success';
        message.value = flash.success;
        show.value = true;
    } else if (flash?.error) {
        type.value = 'error';
        message.value = flash.error;
        show.value = true;
    }

    if (show.value) {
        setTimeout(() => {
            show.value = false;
        }, 3000);
    }
}, { deep: true });

const bgClass = computed(() => type.value === 'success' ? 'bg-green-50 dark:bg-green-900/50 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/50 border-red-200 dark:border-red-800');
const textClass = computed(() => type.value === 'success' ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200');
const iconComponent = computed(() => type.value === 'success' ? CheckCircleIcon : XCircleIcon);
const iconClass = computed(() => type.value === 'success' ? 'text-green-400 dark:text-green-300' : 'text-red-400 dark:text-red-300');

</script>

<template>
    <transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed bottom-4 right-4 z-50 pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5" :class="bgClass">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <component :is="iconComponent" class="h-6 w-6" :class="iconClass" aria-hidden="true" />
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium" :class="textClass">
                            {{ message }}
                        </p>
                    </div>
                    <div class="ml-4 flex flex-shrink-0">
                        <button type="button" @click="show = false" class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2" :class="[type === 'success' ? 'text-green-500 hover:bg-green-100 focus:ring-green-600 dark:hover:bg-green-800' : 'text-red-500 hover:bg-red-100 focus:ring-red-600 dark:hover:bg-red-800']">
                            <span class="sr-only">Close</span>
                            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
