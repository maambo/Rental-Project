<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { BellIcon, CheckIcon } from '@heroicons/vue/24/outline';

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

interface PaginatedNotifications {
    data: Notification[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

defineProps<{
    notifications: PaginatedNotifications;
}>();

const markAsRead = (id: string) => {
    router.post(route('notifications.markAsRead', id), {}, { preserveScroll: true });
};

const markAllAsRead = () => {
    router.post(route('notifications.markAllAsRead'), {}, { preserveScroll: true });
};

const visit = (n: Notification) => {
    if (!n.read_at) {
        router.post(route('notifications.markAsRead', n.id), {}, {
            preserveScroll: true,
            onSuccess: () => router.visit(n.data.action_url),
        });
    } else {
        router.visit(n.data.action_url);
    }
};
</script>

<template>
    <Head title="Notifications" />

    <AuthenticatedLayout header="Notifications">
        <div class="max-w-3xl mx-auto space-y-4">
            <!-- Header actions -->
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-400">
                    Your recent notifications
                </p>
                <button
                    @click="markAllAsRead"
                    class="inline-flex items-center gap-1.5 text-sm text-brand-red hover:text-red-400 transition font-medium"
                >
                    <CheckIcon class="h-4 w-4" />
                    Mark all as read
                </button>
            </div>

            <!-- Empty state -->
            <div
                v-if="!notifications.data.length"
                class="rounded-lg bg-gray-800 border border-gray-700 p-12 text-center"
            >
                <BellIcon class="h-12 w-12 text-gray-600 mx-auto mb-3" />
                <p class="text-gray-400">No notifications yet.</p>
                <p class="text-sm text-gray-500 mt-1">You'll be notified when something needs your attention.</p>
            </div>

            <!-- Notification cards -->
            <div v-else class="space-y-2">
                <button
                    v-for="n in notifications.data"
                    :key="n.id"
                    @click="visit(n)"
                    class="w-full text-left rounded-lg border transition p-4 flex items-start gap-4 group"
                    :class="n.read_at
                        ? 'bg-gray-800/50 border-gray-700/50 hover:bg-gray-800'
                        : 'bg-gray-800 border-gray-700 hover:bg-gray-700/80'"
                >
                    <!-- Unread dot -->
                    <div class="mt-1.5 flex-shrink-0">
                        <div
                            class="h-2.5 w-2.5 rounded-full"
                            :class="n.read_at ? 'bg-gray-700' : 'bg-brand-red'"
                        />
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-4">
                            <h3
                                class="text-sm font-semibold truncate"
                                :class="n.read_at ? 'text-gray-400' : 'text-white'"
                            >
                                {{ n.data.title }}
                            </h3>
                            <span class="text-xs text-gray-500 flex-shrink-0">{{ n.created_at }}</span>
                        </div>
                        <p class="text-sm text-gray-400 mt-1">{{ n.data.message }}</p>
                        <span
                            v-if="n.data.action_text"
                            class="inline-block mt-2 text-xs font-medium text-brand-red group-hover:text-red-400 transition"
                        >
                            {{ n.data.action_text }} &rarr;
                        </span>
                    </div>

                    <!-- Mark as read (if unread) -->
                    <button
                        v-if="!n.read_at"
                        @click.stop="markAsRead(n.id)"
                        class="mt-1 p-1 text-gray-500 hover:text-white transition rounded"
                        title="Mark as read"
                    >
                        <CheckIcon class="h-4 w-4" />
                    </button>
                </button>
            </div>

            <!-- Pagination -->
            <Pagination :links="notifications.links" />
        </div>
    </AuthenticatedLayout>
</template>
