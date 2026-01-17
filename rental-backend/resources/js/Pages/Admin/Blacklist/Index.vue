<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps<{
    blacklist: any;
}>();

const form = useForm({
    nrc_passport: '',
    phone_number: '',
    reason: '',
    type: 'fraud',
});

const submit = () => {
    form.post(route('admin.blacklist.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Blacklist Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Blacklist Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Add New -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Add to Blacklist</h3>
                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel value="NRC / Passport" />
                            <TextInput v-model="form.nrc_passport" class="mt-1 block w-full" required />
                        </div>
                         <div>
                            <InputLabel value="Phone Number" />
                            <TextInput v-model="form.phone_number" class="mt-1 block w-full" />
                        </div>
                        <div class="md:col-span-2">
                             <InputLabel value="Reason" />
                            <TextInput v-model="form.reason" class="mt-1 block w-full" required />
                        </div>
                        <div class="md:col-span-2 flex justify-end">
                            <PrimaryButton :disabled="form.processing">Add to Blacklist</PrimaryButton>
                        </div>
                    </form>
                </div>

                <!-- List -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NRC/Passport</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="item in blacklist.data" :key="item.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ item.nrc_passport }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.phone_number }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.reason }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ item.type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ new Date(item.created_at).toLocaleDateString() }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
