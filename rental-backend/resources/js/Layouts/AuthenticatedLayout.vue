<script setup lang="ts">
import { ref } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import Toast from '@/Components/Toast.vue';
import AppHeader from '@/Components/AppHeader.vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

defineProps<{
    header?: string;
    backUrl?: string;
}>();

const showingSidebar = ref(false);
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-dark-bg">
        <Toast />

        <!-- Desktop Sidebar -->
        <Sidebar class="hidden lg:flex flex-shrink-0 overflow-y-auto" />

        <!-- Mobile Sidebar Overlay -->
        <div v-show="showingSidebar" class="fixed inset-0 z-50 flex lg:hidden" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="showingSidebar = false"></div>

            <!-- Sidebar Panel -->
            <div class="relative flex-1 flex flex-col max-w-xs w-full bg-dark-bg transition ease-in-out duration-300 transform">
                <div class="absolute top-0 right-0 -mr-12 pt-2">
                    <button type="button" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white" @click="showingSidebar = false">
                        <span class="sr-only">Close sidebar</span>
                        <XMarkIcon class="h-6 w-6 text-white" aria-hidden="true" />
                    </button>
                </div>

                <Sidebar class="flex-1" />
            </div>

            <div class="flex-shrink-0 w-14">
                <!-- Force sidebar to shrink to fit close icon -->
            </div>
        </div>

        <!-- Main Content Column -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <AppHeader :title="header" :back-url="backUrl" @toggle-sidebar="showingSidebar = true">
                <template v-if="$slots.header" #title><slot name="header" /></template>
                <template v-if="$slots.actions" #actions><slot name="actions" /></template>
            </AppHeader>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-dark-bg p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
