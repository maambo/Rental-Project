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
            <h2 class="text-xl font-semibold text-white">
                Blacklist Management
            </h2>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">

            <!-- Add New -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Add to Blacklist</h3>
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
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">NRC/Passport</th>
                            <th class="px-4 py-3 font-medium">Phone</th>
                            <th class="px-4 py-3 font-medium">Reason</th>
                            <th class="px-4 py-3 font-medium">Type</th>
                            <th class="px-4 py-3 font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="item in blacklist.data" :key="item.id" class="hover:bg-gray-700/40">
                            <td class="px-4 py-3 text-white">{{ item.nrc_passport }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ item.phone_number }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ item.reason }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-red-500/20 text-red-400">
                                    {{ item.type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-400">
                                {{ new Date(item.created_at).toLocaleDateString() }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
