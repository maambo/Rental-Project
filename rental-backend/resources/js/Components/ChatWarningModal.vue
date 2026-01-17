<script setup lang="ts">
import { ref, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ExclamationTriangleIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="close">
        <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-brand-gold/10 rounded-full mb-4">
                <ExclamationTriangleIcon class="w-8 h-8 text-brand-gold" />
            </div>
            
            <h2 class="text-xl font-bold text-center text-gray-900 dark:text-white mb-4">
                Safety Warning!
            </h2>

            <div class="space-y-4 text-sm text-gray-600 dark:text-gray-300">
                <div class="bg-yellow-50 dark:bg-yellow-900/10 p-4 rounded-lg">
                    <p class="font-bold text-brand-gold mb-1">⚠️ DO NOT PAY IN ADVANCE</p>
                    <p>Scammers often ask for "booking fees" or "transport money" before you see the house. Never send mobile money until you have met the landlord and seen the property.</p>
                </div>

                <div class="flex gap-3">
                    <ShieldCheckIcon class="w-5 h-5 text-brand-blue flex-shrink-0" />
                    <p>Only communicate through this app so we can protect you if something goes wrong.</p>
                </div>

                <div class="flex gap-3">
                    <ShieldCheckIcon class="w-5 h-5 text-brand-blue flex-shrink-0" />
                    <p>We will reveal the landlord's phone number only after you confirm you want to proceed with a viewing.</p>
                </div>
            </div>

            <div class="mt-6 flex justify-center">
                <PrimaryButton @click="close" class="w-full justify-center bg-brand-blue hover:bg-blue-700">
                    I Understand, Proceed to Chat
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
