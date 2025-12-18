<script setup lang="ts">
import { ref } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import Toast from '@/Components/Toast.vue';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="flex min-h-screen bg-gray-100 dark:bg-dark-bg">
        <Toast />
        <!-- Sidebar -->
        <Sidebar class="hidden md:flex" />

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen overflow-hidden">
            <!-- Mobile Header (Optional, if we want to show hamburger on mobile) -->
            <!-- For now, simplifying to just render content area. Mobile sidebar logic would go here. -->
            
            <!-- Page Heading -->
            <header
                class="bg-white shadow dark:bg-light-bg"
                v-if="$slots.header"
            >
                <div class="mx-auto px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6 dark:bg-dark-bg">
                <slot />
            </main>
        </div>
    </div>
</template>
