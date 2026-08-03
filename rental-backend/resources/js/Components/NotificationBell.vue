<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import { BellIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

interface Notification {
    id: string;
    data: {
        type: string;
        title: string;
        message: string;
        action_url: string;
        action_text?: string;
    };
    read_at: string | null;
    created_at: string;
}

const open = ref(false);
const notifications = ref<Notification[]>([]);
const loading = ref(false);

const unreadCount = computed(() => (usePage().props.auth as any)?.user?.unreadNotificationsCount ?? 0);
const hasUnread = computed(() => unreadCount.value > 0);

const fetchNotifications = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get(route('notifications.recent'));
        notifications.value = data;
    } finally {
        loading.value = false;
    }
};

const toggleDropdown = () => {
    open.value = !open.value;
    if (open.value) fetchNotifications();
};

const closeOnEscape = (e: KeyboardEvent) => {
    if (open.value && e.key === 'Escape') open.value = false;
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const clickNotification = (n: Notification) => {
    if (!n.read_at) {
        router.post(route('notifications.markAsRead', n.id), {}, {
            preserveScroll: true,
            onSuccess: () => router.visit(n.data.action_url),
        });
    } else {
        router.visit(n.data.action_url);
    }
    open.value = false;
};

const markAllAsRead = () => {
    router.post(route('notifications.markAllAsRead'), {}, {
        preserveScroll: true,
        onSuccess: () => fetchNotifications(),
    });
};
</script>

<template>
    <div class="relative">
        <button
            @click="toggleDropdown"
            class="relative p-2 text-gray-400 hover:text-white transition rounded-lg hover:bg-gray-700"
        >
            <BellIcon class="h-6 w-6" />
            <span
                v-if="hasUnread"
                class="absolute -top-0.5 -right-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-brand-red text-[10px] font-bold text-white"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <!-- Backdrop -->
        <div v-show="open" class="fixed inset-0 z-40" @click="open = false" />

        <!-- Dropdown panel -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-show="open"
                class="absolute right-0 z-50 mt-2 w-80 rounded-lg bg-gray-800 border border-gray-700 shadow-xl overflow-hidden"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-700">
                    <h3 class="text-sm font-semibold text-white">Notifications</h3>
                    <button
                        v-if="hasUnread"
                        @click="markAllAsRead"
                        class="text-xs text-brand-red hover:text-red-400 transition"
                    >
                        Mark all as read
                    </button>
                </div>

                <!-- List -->
                <div class="max-h-80 overflow-y-auto divide-y divide-gray-700/50">
                    <div v-if="loading" class="p-6 text-center text-sm text-gray-500">
                        Loading...
                    </div>

                    <div v-else-if="!notifications.length" class="p-6 text-center text-sm text-gray-500">
                        No notifications yet.
                    </div>

                    <a
                        v-for="n in notifications"
                        :key="n.id"
                        @click.prevent="clickNotification(n)"
                        :href="n.data.action_url"
                        class="block px-4 py-3 hover:bg-gray-700/60 transition cursor-pointer"
                        :class="!n.read_at ? 'bg-brand-red/5' : ''"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="mt-1.5 h-2 w-2 rounded-full flex-shrink-0"
                                :class="!n.read_at ? 'bg-brand-red' : 'bg-transparent'"
                            />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white truncate">{{ n.data.title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5 line-clamp-2">{{ n.data.message }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ n.created_at }}</p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-700 px-4 py-2">
                    <Link
                        :href="route('notifications.index')"
                        class="block text-center text-xs text-brand-red hover:text-red-400 font-medium transition"
                        @click="open = false"
                    >
                        View all notifications
                    </Link>
                </div>
            </div>
        </Transition>
    </div>
</template>
