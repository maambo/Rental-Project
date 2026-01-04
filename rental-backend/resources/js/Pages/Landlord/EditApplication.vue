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
    tier: props.application.tier,
    id_document: null as File | null,
    proof_of_address: null as File | null,
    tax_certificate: null as File | null,
});

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

                            <!-- Tier Selection -->
                            <div class="mb-8">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Landlord Tier *</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <label class="relative flex cursor-pointer rounded-lg border p-4 focus:outline-none"
                                           :class="form.tier === 'small' ? 'border-brand-red bg-brand-red/10' : 'border-gray-300 dark:border-gray-600'">
                                        <input v-model="form.tier" type="radio" value="small" class="sr-only" />
                                        <div class="flex w-full items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">Small</p>
                                                <p class="text-xs text-gray-500">Up to 10 properties</p>
                                            </div>
                                        </div>
                                    </label>
                                    <label class="relative flex cursor-pointer rounded-lg border p-4 focus:outline-none"
                                           :class="form.tier === 'medium' ? 'border-brand-red bg-brand-red/10' : 'border-gray-300 dark:border-gray-600'">
                                        <input v-model="form.tier" type="radio" value="medium" class="sr-only" />
                                        <div class="flex w-full items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">Medium</p>
                                                <p class="text-xs text-gray-500">Up to 50 properties</p>
                                            </div>
                                        </div>
                                    </label>
                                    <label class="relative flex cursor-pointer rounded-lg border p-4 focus:outline-none"
                                           :class="form.tier === 'large' ? 'border-brand-red bg-brand-red/10' : 'border-gray-300 dark:border-gray-600'">
                                        <input v-model="form.tier" type="radio" value="large" class="sr-only" />
                                        <div class="flex w-full items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">Large</p>
                                                <p class="text-xs text-gray-500">Unlimited properties</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <p v-if="form.errors.tier" class="mt-1 text-sm text-red-600">{{ form.errors.tier }}</p>
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
