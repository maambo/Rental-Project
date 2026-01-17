<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import ChatWarningModal from '@/Components/ChatWarningModal.vue';
import ScamWarningBanner from '@/Components/ScamWarningBanner.vue';
import VerificationBadge from '@/Components/VerificationBadge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps<{
    recipient?: any;
    conversations?: any[];
    messages?: any[];
}>();

const showWarning = ref(false);

onMounted(() => {
    // Show warning if opening a new chat or if not acknowledged before
    // For MVP, show on load if a recipient is selected
    if (props.recipient) {
        showWarning.value = true;
    }
});
</script>

<template>
    <Head title="Messages" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Safety Banner -->
                <div class="mb-4">
                     <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700 dark:text-red-200">
                                    <span class="font-bold">Safety Alert:</span> Never pay any money (booking fees, transport, etc.) before physically viewing the property.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-[600px] flex">
                    <!-- Sidebar (Conversations) -->
                    <div class="w-1/3 border-r border-gray-200 dark:border-gray-700">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Messages</h2>
                        </div>
                        <div class="overflow-y-auto h-full p-2">
                            <div v-if="!conversations || conversations.length === 0" class="text-center text-gray-500 mt-10">
                                No conversations yet.
                            </div>
                        </div>
                    </div>

                    <!-- Chat Area -->
                    <div class="w-2/3 flex flex-col">
                        <div v-if="recipient" class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-brand-blue flex items-center justify-center text-white font-bold">
                                    {{ recipient.name.charAt(0) }}
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        {{ recipient.name }}
                                        <VerificationBadge v-if="recipient.verification_level" :level="recipient.verification_level" />
                                    </div>
                                    <div class="text-xs text-brand-red font-medium" v-if="!recipient.phone_verified">
                                        Phone Hidden (Confirm Viewing to Reveal)
                                    </div>
                                </div>
                            </div>
                            <div>
                                <PrimaryButton class="bg-brand-green hover:bg-green-700">
                                    Confirm Viewing
                                </PrimaryButton>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900">
                             <div class="flex justify-center my-4">
                                <span class="bg-yellow-100 text-yellow-800 text-xs px-3 py-1 rounded-full border border-yellow-200">
                                    Safety: Do not take conversations to WhatsApp immediately.
                                </span>
                            </div>
                            <!-- Mock Message -->
                            <div v-if="recipient" class="flex justify-end">
                                <div class="bg-brand-blue text-white rounded-lg py-2 px-4 max-w-md">
                                    Hi, is this property still available?
                                </div>
                            </div>
                             <div v-else class="h-full flex items-center justify-center text-gray-500">
                                Select a conversation to start chatting
                            </div>
                        </div>

                        <!-- Input -->
                        <div v-if="recipient" class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                            <div class="flex gap-2">
                                <input type="text" placeholder="Type a message..." class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-brand-blue focus:border-brand-blue" />
                                <button class="bg-brand-blue text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                                    Send
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ChatWarningModal :show="showWarning" @close="showWarning = false" />
    </AuthenticatedLayout>
</template>
