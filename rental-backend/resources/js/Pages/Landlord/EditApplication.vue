<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';

const props = defineProps<{
    application: any;
    user: any;
}>();

const form = useForm({
    address: props.application.address,
    province: props.application.province,
    town: props.application.town,
    landlord_type: props.application.landlord_type,
    verification_level: props.application.verification_level,
    id_document: null as File | null,
    proof_of_address: null as File | null,
    tax_certificate: null as File | null,
    selfie: null as File | null,
    video_walkthrough: null as File | null,
    business_registration: null as File | null,
});

const tiers = [
    {
        id: 'basic',
        name: 'Basic (Free)',
        price: 'Free',
        features: ['Up to 5 Properties', 'Basic Support', 'Standard Listings'],
        color: 'border-gray-200 hover:border-brand-blue',
        bg: 'bg-white dark:bg-gray-800',
        checkColor: 'text-gray-400'
    },
    {
        id: 'trusted',
        name: 'Trusted Landlord',
        price: 'Verified',
        features: ['Unlimited Properties', 'Verified Badge', 'Priority Reviews', 'Common Listings'],
        color: 'border-brand-blue ring-1 ring-brand-blue',
        bg: 'bg-blue-50 dark:bg-blue-900/10',
        checkColor: 'text-brand-blue'
    },
    {
        id: 'premium',
        name: 'Premium Landlord',
        price: 'K300/mo',
        features: ['Unlimited Properties', 'Premium Badge', 'Top Placement', 'Video Tours'],
        color: 'border-brand-gold ring-1 ring-brand-gold',
        bg: 'bg-yellow-50 dark:bg-yellow-900/10',
        checkColor: 'text-brand-gold'
    },
];

const selectTier = (tierId: string) => {
    form.verification_level = tierId;
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('landlord.application.update'), {
        forceFormData: true,
    });
};

const handleFile = (field: string, event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        (form as any)[field] = target.files[0];
    }
};
</script>

<template>
    <Head title="Edit Application" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('landlord.status')" class="text-gray-600 hover:text-gray-900">
                    <ArrowLeftIcon class="w-6 h-6" />
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Edit Application</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Warning Message -->
                        <div v-if="application.status !== 'pending'" class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                <strong>Note:</strong> You can only edit applications that are pending or rejected.
                            </p>
                        </div>

                        <form @submit.prevent="submit">
                            <!-- Read-Only Fields -->
                            <div class="mb-8">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                    Account Information (Read-Only)
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
                                        <input
                                            type="text"
                                            :value="user.name"
                                            readonly
                                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 cursor-not-allowed"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                        <input
                                            type="email"
                                            :value="user.email"
                                            readonly
                                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 cursor-not-allowed"
                                        />
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NRC/Passport Number</label>
                                        <input
                                            type="text"
                                            :value="application.nrc_passport"
                                            readonly
                                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 cursor-not-allowed"
                                        />
                                        <p class="mt-1 text-xs text-gray-500">This field cannot be edited for security reasons</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Editable Fields -->
                            <div class="mb-8">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Address Information</h3>
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Street Address *</label>
                                        <input
                                            v-model="form.address"
                                            type="text"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red"
                                        />
                                        <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Town/City *</label>
                                            <input
                                                v-model="form.town"
                                                type="text"
                                                required
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red"
                                            />
                                            <p v-if="form.errors.town" class="mt-1 text-sm text-red-600">{{ form.errors.town }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Province *</label>
                                            <input
                                                v-model="form.province"
                                                type="text"
                                                required
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red"
                                            />
                                            <p v-if="form.errors.province" class="mt-1 text-sm text-red-600">{{ form.errors.province }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Landlord Type Selection -->
                            <div class="mb-8">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Landlord Type</h3>
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

                            <!-- Verification Level Selection -->
                            <div class="mb-8">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Verification Level</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div 
                                        v-for="tier in tiers" 
                                        :key="tier.id"
                                        @click="selectTier(tier.id)"
                                        class="cursor-pointer border-2 rounded-xl p-6 transition relative"
                                        :class="[form.verification_level === tier.id ? (tier.color + ' ' + tier.bg) : 'border-gray-200 dark:border-gray-700 hover:border-brand-blue/50']"
                                    >
                                        <div v-if="form.verification_level === tier.id" class="absolute top-2 right-2 text-brand-blue">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" /></svg>
                                        </div>
                                        <h4 class="font-bold text-xl mb-2 dark:text-white">{{ tier.name }}</h4>
                                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-200 mb-4">{{ tier.price }}</div>
                                        <ul class="space-y-2 mb-4">
                                            <li v-for="feature in tier.features" :key="feature" class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                                <span class="mr-2" :class="tier.checkColor">✓</span> {{ feature }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <p v-if="form.errors.verification_level" class="mt-1 text-sm text-red-600">{{ form.errors.verification_level }}</p>
                            </div>

                            <!-- Document Uploads -->
                            <div class="mb-8">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Documents (Optional - Update if needed)</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Leave blank to keep existing documents</p>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ID Document</label>
                                        <input
                                            type="file"
                                            @change="handleFile('id_document', $event)"
                                            accept=".pdf,.jpg,.jpeg,.png"
                                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-red file:text-white hover:file:bg-red-700"
                                        />
                                        <p class="mt-1 text-xs text-gray-500">Current: <a :href="`/storage/${application.id_document_url}`" target="_blank" class="text-blue-600 hover:underline">View Document</a></p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Proof of Address</label>
                                        <input
                                            type="file"
                                            @change="handleFile('proof_of_address', $event)"
                                            accept=".pdf,.jpg,.jpeg,.png"
                                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-red file:text-white hover:file:bg-red-700"
                                        />
                                        <p class="mt-1 text-xs text-gray-500">Current: <a :href="`/storage/${application.proof_of_address_url}`" target="_blank" class="text-blue-600 hover:underline">View Document</a></p>
                                    </div>

                                    <!-- Trusted Tier Docs -->
                                    <div v-if="form.verification_level === 'trusted' || form.verification_level === 'premium'" class="border-t pt-4 mt-4">
                                        <h4 class="font-medium text-gray-900 dark:text-white mb-4">Required for Trusted Status</h4>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selfie with ID</label>
                                            <input type="file" @change="handleFile('selfie', $event)" class="mt-1 block w-full text-sm text-gray-500
                                            file:mr-4 file:py-2 file:px-4
                                            file:rounded-full file:border-0
                                            file:text-sm file:font-semibold
                                            file:bg-brand-red file:text-white
                                            hover:file:bg-red-700" accept=".jpg,.png" />
                                            <p v-if="application.selfie_url" class="mt-1 text-xs text-gray-500">Current: <a :href="`/storage/${application.selfie_url}`" target="_blank" class="text-blue-600 hover:underline">View Selfie</a></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Agent Docs -->
                                    <div v-if="form.landlord_type === 'agent'" class="border-t pt-4 mt-4">
                                        <h4 class="font-medium text-gray-900 dark:text-white mb-4">Required for Agents</h4>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Business Registration/Pacra Certificate</label>
                                            <input type="file" @change="handleFile('business_registration', $event)" class="mt-1 block w-full text-sm text-gray-500
                                            file:mr-4 file:py-2 file:px-4
                                            file:rounded-full file:border-0
                                            file:text-sm file:font-semibold
                                            file:bg-brand-red file:text-white
                                            hover:file:bg-red-700" accept=".pdf,.jpg,.png" />
                                            <p v-if="application.business_registration_url" class="mt-1 text-xs text-gray-500">Current: <a :href="`/storage/${application.business_registration_url}`" target="_blank" class="text-blue-600 hover:underline">View Certificate</a></p>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tax Certificate (Optional)</label>
                                        <input
                                            type="file"
                                            @change="handleFile('tax_certificate', $event)"
                                            accept=".pdf,.jpg,.jpeg,.png"
                                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-red file:text-white hover:file:bg-red-700"
                                        />
                                        <p v-if="application.tax_certificate_url" class="mt-1 text-xs text-gray-500">Current: <a :href="`/storage/${application.tax_certificate_url}`" target="_blank" class="text-blue-600 hover:underline">View Document</a></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-4">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center px-6 py-3 bg-brand-red border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-red disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Updating...' : 'Update Application' }}
                                </button>
                                <Link
                                    :href="route('landlord.status')"
                                    class="inline-flex items-center px-6 py-3 bg-gray-300 border border-transparent rounded-md font-semibold text-sm text-gray-700 uppercase tracking-widest hover:bg-gray-400"
                                >
                                    Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
