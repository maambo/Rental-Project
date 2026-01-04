<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { CheckCircleIcon, HomeIcon } from '@heroicons/vue/24/solid';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    nrc_passport: '',
    address: '',
    province: '',
    town: '',
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

const selectTier = (tierId: string) => {
    form.tier = tierId;
};

const handleFileChange = (e: Event, field: 'id_document' | 'proof_of_address' | 'tax_certificate') => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form[field] = target.files[0] as any;
    }
};

const submit = () => {
    form.post(route('landlord.apply.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Become a Landlord" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Logo and Back to Home -->
        <div class="absolute top-4 left-4 z-10">
            <Link href="/" class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-brand-red transition">
                <HomeIcon class="h-8 w-8" />
                <span class="font-medium">Back to Home</span>
            </Link>
        </div>
        
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Become a Landlord</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Join our platform and start earning from your properties</p>
                </div>
            </div>

            <!-- Form Content -->
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <form @submit.prevent="submit" class="space-y-8">
                    
                    <!-- Personal Information -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-6 text-gray-900 dark:text-white">1. Personal Information</h3>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <InputLabel for="name" value="Full Name *" />
                                <input 
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                                    required
                                />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="email" value="Email Address *" />
                                    <input 
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                                        required
                                    />
                                    <InputError :message="form.errors.email" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="phone" value="Phone Number *" />
                                    <input 
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                                        placeholder="+260 XXX XXX XXX"
                                        required
                                    />
                                    <InputError :message="form.errors.phone" class="mt-2" />
                                </div>
                            </div>

                            <!-- NRC/Passport Number -->
                            <div>
                                <InputLabel for="nrc_passport" value="NRC / Passport Number *" />
                                <input 
                                    id="nrc_passport"
                                    v-model="form.nrc_passport"
                                    type="text"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                                    placeholder="123456/78/1 or AB1234567"
                                    required
                                />
                                <InputError :message="form.errors.nrc_passport" class="mt-2" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">National Registration Card or Passport number</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="password" value="Password *" />
                                    <input 
                                        id="password"
                                        v-model="form.password"
                                        type="password"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                                        required
                                    />
                                    <InputError :message="form.errors.password" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="password_confirmation" value="Confirm Password *" />
                                    <input 
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        type="password"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                                        required
                                    />
                                    <InputError :message="form.errors.password_confirmation" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <InputLabel for="address" value="Street Address *" />
                                <input 
                                    id="address"
                                    v-model="form.address"
                                    type="text"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                                    required
                                />
                                <InputError :message="form.errors.address" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="province" value="Province/State *" />
                                    <input 
                                        id="province"
                                        v-model="form.province"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                                        required
                                    />
                                    <InputError :message="form.errors.province" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="town" value="Town/City *" />
                                    <input 
                                        id="town"
                                        v-model="form.town"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                                        required
                                    />
                                    <InputError :message="form.errors.town" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tier Selection -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-6 text-gray-900 dark:text-white">2. Select your Tier</h3>
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
                        <h3 class="text-lg font-medium mb-6 text-gray-900 dark:text-white">3. Upload Documents</h3>
                        
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
        </div>
    </div>
</template>
