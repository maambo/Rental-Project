<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { HomeIcon } from '@heroicons/vue/24/solid';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-dark-bg"
    >
        <div>
            <Link href="/">
                <HomeIcon class="h-20 w-20 fill-current text-brand-red" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-light-bg"
        >
            <slot />
        </div>
    </div>
</template>
