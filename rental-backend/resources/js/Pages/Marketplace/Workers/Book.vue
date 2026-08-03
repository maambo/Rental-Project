<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Service { id: number; service_name: string; rate_type: string; base_rate: string; }
interface Worker  { id: number; user: { name: string }; category: { name: string; icon: string }; phone: string; }

const props = defineProps<{
    worker:   Worker;
    services: Service[];
}>();

const form = useForm({
    worker_service_id: '' as string | number,
    job_description:   '',
    location:          '',
    scheduled_date:    '',
    scheduled_time:    '',
    client_notes:      '',
});

const submit = () => {
    form.post(route('marketplace.workers.storeBooking', props.worker.id));
};
</script>

<template>
    <Head :title="`Book ${worker.user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <nav class="flex text-sm text-gray-400 gap-2">
                <Link :href="route('marketplace.workers.index')" class="hover:text-white">Workers</Link>
                <span>/</span>
                <Link :href="route('marketplace.workers.show', worker.id)" class="hover:text-white">{{ worker.user.name }}</Link>
                <span>/</span>
                <span class="text-white">Book</span>
            </nav>
        </template>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h1 class="text-xl font-bold text-white mb-1">Book {{ worker.user.name }}</h1>
                <p class="text-gray-400 text-sm mb-6">{{ worker.category.icon }} {{ worker.category.name }}</p>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Service -->
                    <div v-if="services.length">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Service (optional)</label>
                        <select v-model="form.worker_service_id" class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red">
                            <option value="">— Select a service —</option>
                            <option v-for="s in services" :key="s.id" :value="s.id">{{ s.service_name }} — K{{ s.base_rate }}</option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Job Description <span class="text-red-400">*</span></label>
                        <textarea v-model="form.job_description" rows="4" required
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red"
                            placeholder="Describe the work you need done..."></textarea>
                        <p v-if="form.errors.job_description" class="text-red-400 text-xs mt-1">{{ form.errors.job_description }}</p>
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Location / Address</label>
                        <input v-model="form.location" type="text" class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red" placeholder="Where is the job?" />
                    </div>

                    <!-- Date & Time -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Preferred Date</label>
                            <input v-model="form.scheduled_date" type="date" class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Preferred Time</label>
                            <input v-model="form.scheduled_time" type="time" class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red" />
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Additional Notes</label>
                        <textarea v-model="form.client_notes" rows="2" class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red"></textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="flex-1 bg-brand-red hover:bg-red-700 disabled:opacity-50 text-white font-semibold py-2.5 rounded-lg transition-colors">
                            {{ form.processing ? 'Sending...' : 'Send Booking Request' }}
                        </button>
                        <Link :href="route('marketplace.workers.show', worker.id)" class="px-6 py-2.5 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors text-sm">
                            Cancel
                        </Link>
                    </div>
                </form>
                </div>

                <!-- Worker summary sidebar -->
                <div class="space-y-4 lg:sticky lg:top-4 lg:self-start">
                    <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
                        <h2 class="text-sm font-semibold text-white mb-3">Worker</h2>
                        <p class="text-white font-medium">{{ worker.user.name }}</p>
                        <p class="text-gray-400 text-sm mt-0.5">{{ worker.category.icon }} {{ worker.category.name }}</p>
                        <p v-if="worker.phone" class="text-gray-500 text-xs mt-2">{{ worker.phone }}</p>
                    </div>

                    <div v-if="services.length" class="bg-gray-800 rounded-xl border border-gray-700 p-5">
                        <h2 class="text-sm font-semibold text-white mb-3">Services &amp; Rates</h2>
                        <div class="space-y-2">
                            <div v-for="s in services" :key="s.id" class="flex items-center justify-between text-sm">
                                <span class="text-gray-300">{{ s.service_name }}</span>
                                <span class="text-brand-red font-semibold">K{{ s.base_rate }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
