<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import ChatWarningModal from '@/Components/ChatWarningModal.vue';

interface Conversation {
    user: { id: number; name: string };
    last_message: string;
    last_at: string;
    unread_count: number;
}

interface Message {
    id: number;
    content: string;
    mine: boolean;
    sender: string;
    sent_at: string;
}

const props = defineProps<{
    recipient?: { id: number; name: string };
    conversations: Conversation[];
    messages?: Message[];
}>();

const showWarning = ref(false);
const messageList = ref<HTMLElement | null>(null);

const form = useForm({ receiver_id: props.recipient?.id ?? 0, content: '' });

const send = () => {
    if (!form.content.trim()) return;
    form.post(route('chat.store'), {
        preserveScroll: true,
        onSuccess: () => { form.reset('content'); scrollToBottom(); },
    });
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messageList.value) {
            messageList.value.scrollTop = messageList.value.scrollHeight;
        }
    });
};

// Poll for new messages every 4 seconds when a conversation is open
let pollTimer: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    if (props.recipient) {
        showWarning.value = true;
        scrollToBottom();
        pollTimer = setInterval(() => {
            router.reload({ only: ['messages', 'conversations'] });
        }, 4000);
    }
});

onUnmounted(() => {
    if (pollTimer) clearInterval(pollTimer);
});

const initial = (name: string) => name.charAt(0).toUpperCase();
</script>

<template>
    <Head title="Messages" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Messages</h2>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Safety Banner -->
            <div class="mb-4 bg-red-500/10 border-l-4 border-red-500 p-3 rounded-r-lg flex items-center gap-2">
                <svg class="h-4 w-4 text-red-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-red-300">
                    <span class="font-bold">Safety Alert:</span> Never pay any money (booking fees, transport, etc.) before physically viewing the property.
                </p>
            </div>

            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden flex" style="height: 620px;">

                <!-- ── Conversation list ──────────────────────────────── -->
                <div class="w-1/3 border-r border-gray-700 flex flex-col">
                    <div class="px-4 py-3 border-b border-gray-700">
                        <h3 class="text-sm font-semibold text-white">Conversations</h3>
                    </div>

                    <div class="overflow-y-auto flex-1">
                        <div v-if="conversations.length === 0" class="text-center text-gray-500 text-sm mt-12 px-4">
                            No conversations yet.
                        </div>

                        <a
                            v-for="conv in conversations"
                            :key="conv.user.id"
                            :href="route('chat.show', conv.user.id)"
                            @click.prevent="router.visit(route('chat.show', conv.user.id))"
                            :class="[
                                'flex items-center gap-3 px-4 py-3 hover:bg-gray-700/50 transition cursor-pointer border-b border-gray-700/40',
                                recipient?.id === conv.user.id ? 'bg-gray-700/60' : '',
                            ]"
                        >
                            <div class="h-9 w-9 rounded-full bg-brand-red flex items-center justify-center text-white font-bold text-sm shrink-0">
                                {{ initial(conv.user.name) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-white truncate">{{ conv.user.name }}</span>
                                    <span class="text-xs text-gray-500 shrink-0 ml-1">{{ conv.last_at }}</span>
                                </div>
                                <p class="text-xs text-gray-400 truncate mt-0.5">{{ conv.last_message }}</p>
                            </div>
                            <span v-if="conv.unread_count > 0"
                                class="ml-1 shrink-0 h-5 w-5 rounded-full bg-brand-red text-white text-[10px] font-bold flex items-center justify-center">
                                {{ conv.unread_count }}
                            </span>
                        </a>
                    </div>
                </div>

                <!-- ── Chat area ─────────────────────────────────────── -->
                <div class="flex-1 flex flex-col">

                    <!-- Header -->
                    <div v-if="recipient" class="px-4 py-3 border-b border-gray-700 flex items-center gap-3 bg-gray-900/40 shrink-0">
                        <div class="h-9 w-9 rounded-full bg-brand-red flex items-center justify-center text-white font-bold text-sm">
                            {{ initial(recipient.name) }}
                        </div>
                        <span class="font-semibold text-white">{{ recipient.name }}</span>
                    </div>
                    <div v-else class="px-4 py-3 border-b border-gray-700 bg-gray-900/40 shrink-0">
                        <span class="text-gray-500 text-sm">Select a conversation</span>
                    </div>

                    <!-- Messages -->
                    <div ref="messageList" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-900/20">
                        <div v-if="!recipient" class="h-full flex items-center justify-center text-gray-500 text-sm">
                            Select a conversation to start chatting.
                        </div>

                        <template v-else>
                            <div class="flex justify-center">
                                <span class="bg-yellow-500/20 text-yellow-400 text-xs px-3 py-1 rounded-full">
                                    Safety: Do not take conversations to WhatsApp immediately.
                                </span>
                            </div>

                            <div v-if="!messages || messages.length === 0" class="text-center text-gray-500 text-sm mt-8">
                                No messages yet. Say hello!
                            </div>

                            <div
                                v-for="msg in messages"
                                :key="msg.id"
                                :class="['flex', msg.mine ? 'justify-end' : 'justify-start']"
                            >
                                <div :class="[
                                    'max-w-xs lg:max-w-md px-4 py-2 rounded-2xl text-sm',
                                    msg.mine
                                        ? 'bg-brand-red text-white rounded-br-sm'
                                        : 'bg-gray-700 text-gray-100 rounded-bl-sm',
                                ]">
                                    <p>{{ msg.content }}</p>
                                    <p :class="['text-[10px] mt-1', msg.mine ? 'text-red-200' : 'text-gray-400']">
                                        {{ msg.sent_at }}
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Input -->
                    <div v-if="recipient" class="px-4 py-3 border-t border-gray-700 bg-gray-800 shrink-0">
                        <form @submit.prevent="send" class="flex gap-2">
                            <input
                                v-model="form.content"
                                type="text"
                                placeholder="Type a message…"
                                maxlength="2000"
                                autocomplete="off"
                                class="flex-1 rounded-lg border border-gray-600 bg-gray-700 text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red focus:border-brand-red placeholder-gray-500"
                                @keydown.enter.exact.prevent="send"
                            />
                            <button
                                type="submit"
                                :disabled="form.processing || !form.content.trim()"
                                class="bg-brand-red hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Send
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <ChatWarningModal :show="showWarning" @close="showWarning = false" />
    </AuthenticatedLayout>
</template>
