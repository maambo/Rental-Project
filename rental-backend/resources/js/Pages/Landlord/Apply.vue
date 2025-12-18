<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { CheckCircleIcon } from '@heroicons/vue/24/solid';

const form = useForm({
    tier: '',
    id_document: null,
    proof_of_address: null,
    tax_certificate: null,
});

const tiers = [
    {
        id: 'small',
        name: 'Small Landlord',
        price: 'K200/mo',
        features: ['Up to 3 Properties', 'Basic Support', 'Standard Analytics'],
        color: 'border-gray-200 hover:border-brand-red',
    },
    {
        id: 'medium',
        name: 'Medium Landlord',
        price: 'K500/mo',
        features: ['Up to 10 Properties', 'Priority Support', 'Advanced Analytics', 'Featured Listings'],
        color: 'border-brand-red ring-1 ring-brand-red',
    },
    {
        id: 'large',
        name: 'Enterprise',
        price: 'K1000/mo',
        features: ['Unlimited Properties', '24/7 Support', 'Custom Analytics', 'Top Placement'],
        color: 'border-gray-200 hover:border-brand-red',
    },
];

const selectTier = (tierId) => {
    form.tier = tierId;
};

const handleFileChange = (e, field) => {
    form[field] = e.target.files[0];
};

const submit = () => {
    form.post(route('landlord.apply.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Become a Landlord" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Become a Landlord</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-8">
                    
                    <!-- Tier Selection -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-6 text-gray-900 dark:text-white">1. Select your Tier</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div 
                                v-for="tier in tiers" 
                                :key="tier.id"
                                @click="selectTier(tier.id)"
                                class="cursor-pointer border-2 rounded-xl p-6 transition relative"
                                :class="[form.tier === tier.id ? 'border-brand-red bg-brand-red/5' : 'border-gray-200 dark:border-gray-700 hover:border-brand-red/50']"
                            >
                                <div v-if="form.tier === tier.id" class="absolute top-2 right-2 text-brand-red">
                                    <CheckCircleIcon class="h-6 w-6" />
                                </div>
                                <h4 class="font-bold text-xl mb-2 dark:text-white">{{ tier.name }}</h4>
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-200 mb-4">{{ tier.price }}</div>
                                <ul class="space-y-2 mb-4">
                                    <li v-for="feature in tier.features" :key="feature" class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                        <span class="mr-2 text-green-500">✓</span> {{ feature }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <InputError :message="form.errors.tier" class="mt-2" />
                    </div>

                    <!-- Documents -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-6 text-gray-900 dark:text-white">2. Upload Documents</h3>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <InputLabel value="ID Document (Passport/NRC) *" />
                                <input type="file" @change="(e) => handleFileChange(e, 'id_document')" class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-brand-red/10 file:text-brand-red
                                hover:file:bg-brand-red/20" accept=".pdf,.jpg,.png" required />
                                <InputError :message="form.errors.id_document" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel value="Proof of Address (Utility Bill) *" />
                                <input type="file" @change="(e) => handleFileChange(e, 'proof_of_address')" class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-brand-red/10 file:text-brand-red
                                hover:file:bg-brand-red/20" accept=".pdf,.jpg,.png" required />
                                <InputError :message="form.errors.proof_of_address" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel value="Tax Certificate (Optional)" />
                                <input type="file" @change="(e) => handleFileChange(e, 'tax_certificate')" class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-brand-red/10 file:text-brand-red
                                hover:file:bg-brand-red/20" accept=".pdf,.jpg,.png" />
                                <InputError :message="form.errors.tax_certificate" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <PrimaryButton :disabled="form.processing">
                            Submit Application
                        </PrimaryButton>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
