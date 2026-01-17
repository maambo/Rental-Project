<script setup lang="ts">
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { ExclamationCircleIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const props = defineProps<{
    show: boolean;
    propertyId: number;
}>();

const emit = defineEmits(['close', 'submitted']);

const reasons = [
    { id: 'money_before_viewing', label: 'Asked for money before viewing' },
    { id: 'property_not_exist', label: 'Property does not exist / Fake location' },
    { id: 'stolen_photos', label: 'Photos are stolen / not real' },
    { id: 'not_responding', label: 'Landlord is not responding' },
    { id: 'already_rented', label: 'Property is already taken' },
    { id: 'harassment', label: 'Harassment / Abusive behavior' },
    { id: 'other', label: 'Other reason' },
];

const selectedReasons = ref<string[]>([]);
const details = ref('');
const processing = ref(false);

const submit = () => {
    if (selectedReasons.value.length === 0) return;

    processing.value = true;
    axios.post(route('api.reports.store'), {
        property_id: props.propertyId,
        reasons: selectedReasons.value,
        details: details.value
    }).then(() => {
        emit('submitted');
        close();
    }).catch(err => {
        console.error(err);
    }).finally(() => {
        processing.value = false;
    });
};

const close = () => {
    selectedReasons.value = [];
    details.value = '';
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="close">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4 text-brand-red">
                <ExclamationCircleIcon class="w-6 h-6" />
                <h2 class="text-lg font-bold">Report this Listing</h2>
            </div>

            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Help us keep the community safe. Your report is anonymous.
            </p>

            <div class="space-y-3 mb-6">
                <div v-for="reason in reasons" :key="reason.id" class="flex items-start">
                    <input 
                        type="checkbox" 
                        :id="reason.id" 
                        :value="reason.id"
                        v-model="selectedReasons"
                        class="mt-1 rounded border-gray-300 text-brand-red shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                    />
                    <label :for="reason.id" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        {{ reason.label }}
                    </label>
                </div>
            </div>

            <div v-if="selectedReasons.includes('other') || selectedReasons.length > 0" class="mb-6">
                <InputLabel value="Additional Details (Optional)" />
                <textarea 
                    v-model="details"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                    rows="3"
                    placeholder="Please provide more context..."
                ></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <SecondaryButton @click="close">Cancel</SecondaryButton>
                <PrimaryButton 
                    @click="submit" 
                    :disabled="selectedReasons.length === 0 || processing"
                    class="bg-brand-red hover:bg-red-700"
                >
                    {{ processing ? 'Submitting...' : 'Submit Report' }}
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
