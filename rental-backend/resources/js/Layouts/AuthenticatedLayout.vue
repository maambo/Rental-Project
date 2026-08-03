<script setup lang="ts">
import { ref } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import Toast from '@/Components/Toast.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import { Bars3Icon, XMarkIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

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
            <!-- Page Heading -->
            <header class="bg-gray-800 border-b border-gray-700">
                <div class="mx-auto px-4 py-4 sm:px-6 lg:px-8 flex items-center gap-4">
                    <button v-show="!showingSidebar" @click="showingSidebar = true" class="p-2 rounded-md text-gray-400 hover:text-white focus:outline-none transition ease-in-out duration-150 z-50 lg:hidden">
                        <Bars3Icon class="h-6 w-6" />
                    </button>

                    <div class="flex items-center gap-4 flex-1 min-w-0">
                        <Link v-if="backUrl" :href="backUrl" class="text-gray-400 hover:text-white transition">
                            <ArrowLeftIcon class="w-6 h-6" />
                        </Link>
                        <h2 v-if="header" class="font-semibold text-xl text-white truncate">
                            {{ header }}
                        </h2>
                        <slot v-else name="header" />
                    </div>

                    <NotificationBell />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-dark-bg p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
