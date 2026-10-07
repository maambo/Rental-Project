<script setup lang="ts">
import { ExclamationTriangleIcon, QuestionMarkCircleIcon } from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';
import AppButton from '@/Components/AppButton.vue';
import { confirmDialogState, acceptConfirmDialog, rejectConfirmDialog } from '@/composables/useConfirm';

const iconWrapClass: Record<string, string> = {
    danger: 'bg-brand-danger/10 text-brand-danger',
    primary: 'bg-brand-red/10 text-brand-red',
};
</script>

<template>
    <Modal :show="confirmDialogState.show" max-width="sm" @close="rejectConfirmDialog">
        <div class="p-6">
            <div
                class="flex items-center justify-center w-12 h-12 mx-auto rounded-full mb-4"
                :class="iconWrapClass[confirmDialogState.variant]"
            >
                <ExclamationTriangleIcon v-if="confirmDialogState.variant === 'danger'" class="w-7 h-7" />
                <QuestionMarkCircleIcon v-else class="w-7 h-7" />
            </div>

            <h2 class="text-lg font-semibold text-center text-white">
                {{ confirmDialogState.title }}
            </h2>

            <p class="mt-2 text-sm text-center text-gray-400">
                {{ confirmDialogState.message }}
            </p>

            <div class="mt-6 flex justify-center gap-3">
                <AppButton variant="secondary" @click="rejectConfirmDialog">
                    {{ confirmDialogState.cancelText }}
                </AppButton>
                <AppButton :variant="confirmDialogState.variant" @click="acceptConfirmDialog">
                    {{ confirmDialogState.confirmText }}
                </AppButton>
            </div>
        </div>
    </Modal>
</template>
