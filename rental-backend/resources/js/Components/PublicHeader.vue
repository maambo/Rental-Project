<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

withDefaults(defineProps<{ canRegister?: boolean }>(), { canRegister: true });

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
const open = ref(false);

const links = [
    { label: 'Browse Properties', href: () => route('properties.index'), match: 'properties.*' },
    { label: 'Find Workers', href: () => route('marketplace.workers.index'), match: 'marketplace.workers.*' },
];

// Contextual auth actions: hide the button for the page you're already on
const onLogin = computed(() => route().current('login'));
const onRegister = computed(() => route().current('register'));

const navLink = (match: string) =>
    ['rounded-lg px-3 py-2 text-sm font-medium transition',
        route().current(match) ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800'];
</script>

<template>
    <header class="sticky top-0 z-30 border-b border-gray-800 bg-dark-bg/80 backdrop-blur supports-[backdrop-filter]:bg-dark-bg/70">
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-6 px-4 sm:px-6 lg:px-8">
            <!-- Logo (same mark as the sidebar) -->
            <Link :href="route('landing')" class="flex items-center gap-2">
                <ApplicationLogo class="h-8 w-8 fill-current text-brand-red" />
                <span class="text-xl font-bold tracking-wider text-white">RENTAL<span class="text-brand-red">APP</span></span>
            </Link>

            <!-- Desktop nav -->
            <nav class="hidden flex-1 items-center gap-1 md:flex">
                <Link v-for="l in links" :key="l.label" :href="l.href()" :class="navLink(l.match)">{{ l.label }}</Link>
            </nav>

            <!-- Auth actions -->
            <div class="ml-auto flex items-center gap-2 md:ml-0">
                <Link v-if="user" :href="route('dashboard')"
                      class="rounded-full bg-brand-red px-5 py-2 text-sm font-semibold text-white transition hover:bg-brand-orange focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                    Dashboard
                </Link>
                <template v-else>
                    <Link v-if="!onLogin" :href="route('login')"
                          class="hidden rounded-full px-4 py-2 text-sm font-semibold text-gray-300 transition hover:text-white sm:block">
                        {{ onRegister ? 'Already have an account? Log in' : 'Log in' }}
                    </Link>
                    <Link v-if="canRegister && !onRegister" :href="route('register')"
                          class="rounded-full bg-brand-red px-5 py-2 text-sm font-semibold text-white transition hover:bg-brand-orange focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                        {{ onLogin ? 'Create account' : 'Register' }}
                    </Link>
                </template>

                <button type="button"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-800 hover:text-white md:hidden"
                        @click="open = !open">
                    <span class="sr-only">Toggle menu</span>
                    <XMarkIcon v-if="open" class="h-6 w-6" />
                    <Bars3Icon v-else class="h-6 w-6" />
                </button>
            </div>
        </div>

        <!-- Mobile nav -->
        <nav v-show="open" class="flex flex-col gap-1 border-t border-gray-800 px-4 py-3 md:hidden">
            <Link v-for="l in links" :key="l.label" :href="l.href()" :class="navLink(l.match)">{{ l.label }}</Link>
            <Link v-if="!user" :href="route('login')" :class="navLink('login')">Log in</Link>
        </nav>
    </header>
</template>
