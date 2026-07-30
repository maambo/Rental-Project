<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}>();
</script>

<template>
    <div v-if="links.length > 3">
        <div class="flex flex-wrap -mb-1">
            <template v-for="(link, key) in links" :key="key">
                <div
                    v-if="link.url === null"
                    class="mr-1 mb-1 px-4 py-2 text-sm leading-4 text-gray-500 border border-gray-700 rounded-lg"
                    v-html="link.label"
                />
                <Link
                    v-else
                    class="mr-1 mb-1 px-4 py-2 text-sm leading-4 border rounded-lg transition-colors"
                    :class="link.active ? 'bg-brand-red text-white border-brand-red' : 'bg-gray-800 border-gray-700 text-gray-300 hover:bg-gray-700'"
                    :href="link.url"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>
