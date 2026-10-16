<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Bars3Icon, ArrowLeftIcon, ChevronDownIcon, UserIcon, ArrowLeftOnRectangleIcon } from '@heroicons/vue/24/outline';
import Dropdown from '@/Components/Dropdown.vue';
import NotificationBell from '@/Components/NotificationBell.vue';

defineProps<{
    title?: string;
    backUrl?: string;
}>();

defineEmits<{ (e: 'toggle-sidebar'): void }>();

const page = usePage();
const user = computed(() => (page.props.auth as any).user);
const role = computed(() => (user.value?.roleModel?.name || 'tenant').replace(/_/g, ' '));

const menuItem = 'flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-300 transition hover:bg-gray-700 hover:text-white';
</script>

<template>
    <header class="sticky top-0 z-30 flex-shrink-0 border-b border-gray-800 bg-dark-bg/80 backdrop-blur supports-[backdrop-filter]:bg-dark-bg/70">
        <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
            <!-- Mobile menu toggle -->
            <button type="button"
                    class="-ml-2 rounded-lg p-2 text-gray-400 transition hover:bg-gray-800 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-red lg:hidden"
                    @click="$emit('toggle-sidebar')">
                <span class="sr-only">Open sidebar</span>
                <Bars3Icon class="h-6 w-6" />
            </button>

            <!-- Back + title -->
            <div class="flex min-w-0 flex-1 items-center gap-3">
                <Link v-if="backUrl" :href="backUrl"
                      class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-800 hover:text-white"
                      aria-label="Go back">
                    <ArrowLeftIcon class="h-5 w-5" />
                </Link>
                <h1 v-if="title" class="truncate text-lg font-semibold text-white sm:text-xl">{{ title }}</h1>
                <slot v-else name="title" />
            </div>

            <!-- Right actions -->
            <div class="flex items-center gap-1 sm:gap-2">
                <slot name="actions" />
                <NotificationBell />

                <Dropdown align="right" content-classes="py-1 bg-gray-800 ring-1 ring-gray-700">
                    <template #trigger>
                        <button type="button"
                                class="flex items-center gap-2 rounded-full py-1 pl-1 pr-2 transition hover:bg-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-red">
                            <img :src="user.avatar_url" :alt="user.name"
                                 class="h-8 w-8 rounded-full bg-light-bg object-cover" />
                            <span class="hidden text-left leading-tight md:block">
                                <span class="block max-w-[10rem] truncate text-sm font-medium text-white">{{ user.name }}</span>
                                <span class="block text-xs capitalize text-gray-500">{{ role }}</span>
                            </span>
                            <ChevronDownIcon class="hidden h-4 w-4 text-gray-500 md:block" />
                        </button>
                    </template>
                    <template #content>
                        <div class="border-b border-gray-700 px-4 py-2 md:hidden">
                            <p class="truncate text-sm font-medium text-white">{{ user.name }}</p>
                            <p class="text-xs capitalize text-gray-500">{{ role }}</p>
                        </div>
                        <Link :href="route('profile.edit')" :class="menuItem">
                            <UserIcon class="h-4 w-4" />Profile
                        </Link>
                        <Link :href="route('logout')" method="post" as="button" :class="menuItem">
                            <ArrowLeftOnRectangleIcon class="h-4 w-4" />Log out
                        </Link>
                    </template>
                </Dropdown>
            </div>
        </div>
    </header>
</template>
