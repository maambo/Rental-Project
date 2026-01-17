<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    tiers: Array<any>;
}>();

const form = useForm({
    tiers: props.tiers.map(tier => ({
        ...tier,
        features: tier.features || []
    }))
});

const addFeature = (tierIndex: number) => {
    form.tiers[tierIndex].features.push('New Feature');
};

const removeFeature = (tierIndex: number, featureIndex: number) => {
    form.tiers[tierIndex].features.splice(featureIndex, 1);
};

const submit = () => {
    form.post(route('admin.settings.update'));
};
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">System Settings</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-8">
                        
                        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Verification Tiers</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Manage pricing, limits, and features for landlord tiers.</p>
                            </div>
                            <PrimaryButton :disabled="form.processing">Save Changes</PrimaryButton>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div v-for="(tier, tIndex) in form.tiers" :key="tier.id" class="border dark:border-gray-700 rounded-lg p-5 bg-gray-50 dark:bg-gray-900/50">
                                <div class="flex justify-between items-center mb-4">
                                     <h4 class="text-md font-bold text-gray-900 dark:text-white uppercase tracking-wide">{{ tier.name }}</h4>
                                     <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full" v-if="tier.is_active">Active</span>
                                     <span class="text-xs px-2 py-1 bg-gray-100 text-gray-800 rounded-full" v-else>Inactive</span>
                                </div>
                                
                                <div class="space-y-4">
                                    <!-- Display Name -->
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Display Name</label>
                                        <input v-model="tier.display_name" type="text" class="mt-1 block w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm" />
                                    </div>

                                    <!-- Price Display & Amount -->
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Price Label</label>
                                            <input v-model="tier.price_display" type="text" placeholder="e.g Free" class="mt-1 block w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm" />
                                        </div>
                                         <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Amount (ZMW)</label>
                                            <input v-model="tier.price_amount" type="number" class="mt-1 block w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm" />
                                        </div>
                                    </div>

                                    <!-- Property Limit -->
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Property Limit (-1 for Unl.)</label>
                                        <input v-model="tier.property_limit" type="number" class="mt-1 block w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm" />
                                    </div>

                                    <!-- Features List -->
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Features</label>
                                            <button type="button" @click="addFeature(tIndex)" class="text-xs text-brand-blue hover:underline flex items-center gap-1">
                                                <PlusIcon class="w-3 h-3" /> Add
                                            </button>
                                        </div>
                                        <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                            <div v-for="(feature, fIndex) in tier.features" :key="fIndex" class="flex items-center gap-2">
                                                <input v-model="tier.features[fIndex]" type="text" class="block w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm py-1" />
                                                <button type="button" @click="removeFeature(tIndex, Number(fIndex))" class="text-red-500 hover:text-red-700">
                                                    <TrashIcon class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
