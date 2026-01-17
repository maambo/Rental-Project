<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps<{
    property: any;
}>();

// Form matching fields in migration
const form = useForm({
    employer_name: '',
    job_title: '',
    monthly_income: '',
    employment_duration: '',
    reference_1_name: '',
    reference_1_phone: '',
    reference_1_relation: '',
    reference_2_name: '',
    reference_2_phone: '',
    reference_2_relation: '',
    preferred_move_in_date: '',
    lease_duration_months: '',
    occupants_count: '',
    has_pets: false,
    pet_details: '',
    additional_comments: '',
});

const submit = () => {
    form.post(route('properties.apply.store', props.property.id));
};
</script>

<template>
    <Head title="Apply for Property" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Apply for: {{ property.title }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            
                            <!-- Employment Details -->
                            <h3 class="text-lg font-medium mb-4 text-brand-red border-b pb-2">Employment Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <InputLabel for="employer_name" value="Employer Name *" />
                                    <TextInput id="employer_name" type="text" class="mt-1 block w-full" v-model="form.employer_name" required />
                                    <InputError class="mt-2" :message="form.errors.employer_name" />
                                </div>
                                <div>
                                    <InputLabel for="job_title" value="Job Title *" />
                                    <TextInput id="job_title" type="text" class="mt-1 block w-full" v-model="form.job_title" required />
                                    <InputError class="mt-2" :message="form.errors.job_title" />
                                </div>
                                 <div>
                                    <InputLabel for="monthly_income" value="Monthly Income (K) *" />
                                    <TextInput id="monthly_income" type="number" class="mt-1 block w-full" v-model="form.monthly_income" required />
                                    <InputError class="mt-2" :message="form.errors.monthly_income" />
                                </div>
                                 <div>
                                    <InputLabel for="employment_duration" value="Duration (e.g. 2 years) *" />
                                    <TextInput id="employment_duration" type="text" class="mt-1 block w-full" v-model="form.employment_duration" required />
                                    <InputError class="mt-2" :message="form.errors.employment_duration" />
                                </div>
                            </div>

                            <!-- References -->
                            <h3 class="text-lg font-medium mb-4 text-brand-red border-b pb-2">References</h3>
                            <div class="mb-6">
                                <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Reference 1 *</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <TextInput placeholder="Name" type="text" class="block w-full" v-model="form.reference_1_name" required />
                                    </div>
                                    <div>
                                        <TextInput placeholder="Phone" type="tel" class="block w-full" v-model="form.reference_1_phone" required />
                                    </div>
                                    <div>
                                         <TextInput placeholder="Relationship" type="text" class="block w-full" v-model="form.reference_1_relation" required />
                                    </div>
                                </div>
                            </div>
                             <div class="mb-6">
                                <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Reference 2 *</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <TextInput placeholder="Name" type="text" class="block w-full" v-model="form.reference_2_name" required />
                                    </div>
                                    <div>
                                        <TextInput placeholder="Phone" type="tel" class="block w-full" v-model="form.reference_2_phone" required />
                                    </div>
                                    <div>
                                         <TextInput placeholder="Relationship" type="text" class="block w-full" v-model="form.reference_2_relation" required />
                                    </div>
                                </div>
                            </div>

                            <!-- Lease Details -->
                            <h3 class="text-lg font-medium mb-4 text-brand-red border-b pb-2">Lease Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <InputLabel for="preferred_move_in_date" value="Preferred Move-in Date *" />
                                    <TextInput id="preferred_move_in_date" type="date" class="mt-1 block w-full" v-model="form.preferred_move_in_date" required />
                                    <InputError class="mt-2" :message="form.errors.preferred_move_in_date" />
                                </div>
                                <div>
                                    <InputLabel for="lease_duration_months" value="Lease Duration (Months) *" />
                                    <TextInput id="lease_duration_months" type="number" class="mt-1 block w-full" v-model="form.lease_duration_months" required />
                                    <InputError class="mt-2" :message="form.errors.lease_duration_months" />
                                </div>
                                 <div>
                                    <InputLabel for="occupants_count" value="Number of Occupants *" />
                                    <TextInput id="occupants_count" type="number" class="mt-1 block w-full" v-model="form.occupants_count" required />
                                    <InputError class="mt-2" :message="form.errors.occupants_count" />
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="flex items-center">
                                    <Checkbox name="has_pets" v-model:checked="form.has_pets" />
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Do you have pets?</span>
                                </label>
                                <div v-if="form.has_pets" class="mt-4">
                                    <InputLabel for="pet_details" value="Pet Details (Type, Breed, Size)" />
                                    <TextInput id="pet_details" type="text" class="mt-1 block w-full" v-model="form.pet_details" />
                                </div>
                            </div>

                             <div class="mb-6">
                                <InputLabel for="additional_comments" value="Additional Comments" />
                                <textarea id="additional_comments" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="3" v-model="form.additional_comments"></textarea>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Submit Application
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
