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
    tier: '', // kept for compatibility if needed, but we used verification_level in backend
    verification_level: 'basic',
    landlord_type: 'private_landlord',
    id_document: null,
    proof_of_address: null,
    tax_certificate: null,
    selfie: null,
    video_walkthrough: null,
    business_registration: null,
});

const tiers = [
    {
        id: 'basic',
        name: 'Basic (Free)',
        price: 'Free',
        features: ['Up to 5 Properties', 'Basic Support', 'Standard Listings'],
        color: 'border-gray-200 hover:border-brand-blue',
    },
    {
        id: 'trusted',
        name: 'Trusted Landlord',
        price: 'Verified',
        features: ['Unlimited Properties', 'Verified Badge', 'Priority Reviews', 'Common Listings'],
        color: 'border-brand-blue ring-1 ring-brand-blue',
    },
    {
        id: 'premium',
        name: 'Premium Landlord',
        price: 'K300/mo',
        features: ['Unlimited Properties', 'Premium Badge', 'Top Placement', 'Video Tours'],
        color: 'border-brand-gold ring-1 ring-brand-gold',
    },
];

const selectTier = (tierId: string) => {
    form.verification_level = tierId;
    form.tier = tierId; // populate legacy field temporarily
};

const handleFileChange = (e: Event, field: 'id_document' | 'proof_of_address' | 'tax_certificate' | 'selfie' | 'video_walkthrough' | 'business_registration') => {
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

        
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <div class="mb-4">
                        <Link href="/" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-brand-red transition">
                            <HomeIcon class="h-6 w-6" /> <!-- Reduced size slightly for better proportion -->
                            <span class="font-medium">Back to Home</span>
                        </Link>
                    </div>
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

                    <!-- Landlord Type Selection -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-6 text-gray-900 dark:text-white">2. Landlord Type</h3>
                        <div class="space-y-4">
                             <div class="flex items-center">
                                <input id="private" type="radio" value="private_landlord" v-model="form.landlord_type" name="landlord_type" class="w-4 h-4 text-brand-blue border-gray-300 focus:ring-brand-blue" />
                                <label for="private" class="ml-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Private Landlord (I own the properties)
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="agent" type="radio" value="agent" v-model="form.landlord_type" name="landlord_type" class="w-4 h-4 text-brand-blue border-gray-300 focus:ring-brand-blue" />
                                <label for="agent" class="ml-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Real Estate Agent / Property Manager
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Tier Selection -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-6 text-gray-900 dark:text-white">3. Select Verification Level</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div 
                                v-for="tier in tiers" 
                                :key="tier.id"
                                @click="selectTier(tier.id)"
                                class="cursor-pointer border-2 rounded-xl p-6 transition relative"
                                :class="[form.verification_level === tier.id ? 'border-brand-blue bg-brand-blue/5' : 'border-gray-200 dark:border-gray-700 hover:border-brand-blue/50']"
                            >
                                <div v-if="form.verification_level === tier.id" class="absolute top-2 right-2 text-brand-blue">
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
                        <InputError :message="form.errors.verification_level" class="mt-2" />
                    </div>

                    <!-- Documents -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-6 text-gray-900 dark:text-white">4. Upload Documents</h3>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Basic Docs -->
                            <div>
                                <InputLabel value="ID Document (Passport/NRC) *" />
                                <input type="file" @change="(e) => handleFileChange(e, 'id_document')" class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-brand-blue/10 file:text-brand-blue
                                hover:file:bg-brand-blue/20" accept=".pdf,.jpg,.png" required />
                                <InputError :message="form.errors.id_document" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel value="Proof of Address (Utility Bill) *" />
                                <input type="file" @change="(e) => handleFileChange(e, 'proof_of_address')" class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-brand-blue/10 file:text-brand-blue
                                hover:file:bg-brand-blue/20" accept=".pdf,.jpg,.png" required />
                                <InputError :message="form.errors.proof_of_address" class="mt-2" />
                            </div>

                            <!-- Trusted Tier Docs -->
                            <div v-if="form.verification_level === 'trusted' || form.verification_level === 'premium'" class="border-t pt-4 mt-4">
                                <h4 class="font-medium text-gray-900 dark:text-white mb-4">Required for Trusted Status</h4>
                                <div>
                                    <InputLabel value="Selfie with ID *" />
                                    <p class="text-xs text-gray-500 mb-2">Please take a clear photo of yourself holding your ID card next to your face.</p>
                                    <input type="file" @change="(e) => handleFileChange(e, 'selfie')" class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-brand-blue/10 file:text-brand-blue
                                    hover:file:bg-brand-blue/20" accept=".jpg,.png" />
                                    <InputError :message="form.errors.selfie" class="mt-2" />
                                </div>
                            </div>
                            
                            <!-- Agent Docs -->
                             <div v-if="form.landlord_type === 'agent'" class="border-t pt-4 mt-4">
                                <h4 class="font-medium text-gray-900 dark:text-white mb-4">Required for Agents</h4>
                                <div>
                                    <InputLabel value="Business Registration/Pacra Certificate *" />
                                    <input type="file" @change="(e) => handleFileChange(e, 'business_registration')" class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-brand-blue/10 file:text-brand-blue
                                    hover:file:bg-brand-blue/20" accept=".pdf,.jpg,.png" />
                                    <InputError :message="form.errors.business_registration" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <InputLabel value="Tax Certificate (Optional)" />
                                <input type="file" @change="(e) => handleFileChange(e, 'tax_certificate')" class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-brand-blue/10 file:text-brand-blue
                                hover:file:bg-brand-blue/20" accept=".pdf,.jpg,.png" />
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
