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
    id_type: 'nrc' as 'nrc' | 'passport',
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
    },
    {
        id: 'trusted',
        name: 'Trusted Landlord',
        price: 'Verified',
        features: ['Unlimited Properties', 'Verified Badge', 'Priority Reviews', 'Common Listings'],
    },
    {
        id: 'premium',
        name: 'Premium Landlord',
        price: 'K300/mo',
        features: ['Unlimited Properties', 'Premium Badge', 'Top Placement', 'Video Tours'],
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

    <div class="min-h-screen bg-dark-bg">
        <!-- Header -->
        <div class="bg-gray-800 border-b border-gray-700">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="mb-4">
                    <Link href="/" class="inline-flex items-center gap-2 text-gray-400 hover:text-brand-red transition">
                        <HomeIcon class="h-6 w-6" />
                        <span class="font-medium">Back to Home</span>
                    </Link>
                </div>
                <h1 class="text-3xl font-bold text-white">Become a Landlord</h1>
                <p class="mt-2 text-gray-400">Join our platform and start earning from your properties</p>
            </div>
        </div>

        <!-- Form Content -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <form @submit.prevent="submit" class="space-y-6">

            <!-- Personal Information -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold mb-6 text-white">1. Personal Information</h3>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <InputLabel for="name" value="Full Name *" />
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-gray-200 shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
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
                                class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-gray-200 shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
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
                                class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-gray-200 shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                                placeholder="+260 XXX XXX XXX"
                                required
                            />
                            <InputError :message="form.errors.phone" class="mt-2" />
                        </div>
                    </div>

                    <!-- ID Type + NRC/Passport Number -->
                    <div>
                        <InputLabel value="ID Type *" />
                        <div class="mt-1 flex gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="form.id_type" value="nrc" class="text-brand-red focus:ring-brand-red" />
                                <span class="text-sm text-gray-300">NRC (Zambian citizen)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="form.id_type" value="passport" class="text-brand-red focus:ring-brand-red" />
                                <span class="text-sm text-gray-300">Passport (non-citizen)</span>
                            </label>
                        </div>
                        <InputError :message="form.errors.id_type" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="nrc_passport" :value="form.id_type === 'nrc' ? 'NRC Number *' : 'Passport Number *'" />
                        <input
                            id="nrc_passport"
                            v-model="form.nrc_passport"
                            type="text"
                            class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-gray-200 shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                            :placeholder="form.id_type === 'nrc' ? 'e.g. 123456/78/1' : 'e.g. A1234567'"
                            required
                        />
                        <InputError :message="form.errors.nrc_passport" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="password" value="Password *" />
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-gray-200 shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
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
                                class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-gray-200 shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
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
                            class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-gray-200 shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
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
                                class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-gray-200 shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
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
                                class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-gray-200 shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                                required
                            />
                            <InputError :message="form.errors.town" class="mt-2" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Landlord Type Selection -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold mb-6 text-white">2. Landlord Type</h3>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input id="private" type="radio" value="private_landlord" v-model="form.landlord_type" name="landlord_type" class="w-4 h-4 text-brand-red bg-gray-900 border-gray-600 focus:ring-brand-red" />
                        <label for="private" class="ml-2 block text-sm font-medium text-gray-300">
                            Private Landlord (I own the properties)
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input id="agent" type="radio" value="agent" v-model="form.landlord_type" name="landlord_type" class="w-4 h-4 text-brand-red bg-gray-900 border-gray-600 focus:ring-brand-red" />
                        <label for="agent" class="ml-2 block text-sm font-medium text-gray-300">
                            Real Estate Agent / Property Manager
                        </label>
                    </div>
                </div>
            </div>

            <!-- Tier Selection -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold mb-6 text-white">3. Select Verification Level</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="tier in tiers"
                        :key="tier.id"
                        @click="selectTier(tier.id)"
                        class="cursor-pointer border-2 rounded-xl p-6 transition relative"
                        :class="[form.verification_level === tier.id ? 'border-brand-red bg-brand-red/5' : 'border-gray-700 hover:border-brand-red/50']"
                    >
                        <div v-if="form.verification_level === tier.id" class="absolute top-2 right-2 text-brand-red">
                            <CheckCircleIcon class="h-6 w-6" />
                        </div>
                        <h4 class="font-bold text-xl mb-2 text-white">{{ tier.name }}</h4>
                        <div class="text-2xl font-bold text-white mb-4">{{ tier.price }}</div>
                        <ul class="space-y-2 mb-4">
                            <li v-for="feature in tier.features" :key="feature" class="text-sm text-gray-400 flex items-center">
                                <span class="mr-2 text-green-400">✓</span> {{ feature }}
                            </li>
                        </ul>
                    </div>
                </div>
                <InputError :message="form.errors.verification_level" class="mt-2" />
            </div>

            <!-- Documents -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold mb-6 text-white">4. Upload Documents</h3>

                <div class="grid grid-cols-1 gap-6">
                    <!-- Basic Docs -->
                    <div>
                        <InputLabel value="ID Document (Passport/NRC) *" />
                        <input type="file" @change="(e) => handleFileChange(e, 'id_document')" class="mt-1 block w-full text-sm text-gray-400
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-brand-red/10 file:text-brand-red
                        hover:file:bg-brand-red/20" accept=".pdf,.jpg,.png" required />
                        <InputError :message="form.errors.id_document" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="Proof of Address (Utility Bill) *" />
                        <input type="file" @change="(e) => handleFileChange(e, 'proof_of_address')" class="mt-1 block w-full text-sm text-gray-400
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-brand-red/10 file:text-brand-red
                        hover:file:bg-brand-red/20" accept=".pdf,.jpg,.png" required />
                        <InputError :message="form.errors.proof_of_address" class="mt-2" />
                    </div>

                    <!-- Selfie — always required -->
                    <div class="border-t border-gray-700 pt-4 mt-4">
                        <div>
                            <InputLabel value="Selfie / Live Photo *" />
                            <p class="text-xs text-gray-400 mb-2">A clear photo of your face — used to match your ID document.</p>
                            <input type="file" required @change="(e) => handleFileChange(e, 'selfie')" class="mt-1 block w-full text-sm text-gray-400
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-brand-red/10 file:text-brand-red
                            hover:file:bg-brand-red/20" accept=".jpg,.jpeg,.png" />
                            <InputError :message="form.errors.selfie" class="mt-2" />
                        </div>
                    </div>

                    <!-- Agent Docs -->
                    <div v-if="form.landlord_type === 'agent'" class="border-t border-gray-700 pt-4 mt-4">
                        <h4 class="font-medium text-white mb-4">Required for Agents</h4>
                        <div>
                            <InputLabel value="Business Registration/Pacra Certificate *" />
                            <input type="file" @change="(e) => handleFileChange(e, 'business_registration')" class="mt-1 block w-full text-sm text-gray-400
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-brand-red/10 file:text-brand-red
                            hover:file:bg-brand-red/20" accept=".pdf,.jpg,.png" />
                            <InputError :message="form.errors.business_registration" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Tax Certificate (Optional)" />
                        <input type="file" @change="(e) => handleFileChange(e, 'tax_certificate')" class="mt-1 block w-full text-sm text-gray-400
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
</template>
