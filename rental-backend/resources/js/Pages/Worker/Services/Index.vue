<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

interface Profile  { id: number; tagline: string; }
interface Service  { id: number; service_name: string; description: string | null; rate_type: string; base_rate: string; minimum_charge: string | null; is_active: boolean; }

const props = defineProps<{
    profile:  Profile;
    services: Service[];
}>();

const showAdd = ref(false);
const editId  = ref<number | null>(null);

const addForm = useForm({
    service_name:   '',
    description:    '',
    rate_type:      'per_job' as 'hourly' | 'per_job' | 'per_day',
    base_rate:      '' as string | number,
    minimum_charge: '' as string | number,
});

const editForm = useForm({
    service_name:   '',
    description:    '',
    rate_type:      'per_job' as 'hourly' | 'per_job' | 'per_day',
    base_rate:      '' as string | number,
    minimum_charge: '' as string | number,
});

const submitAdd = () => {
    addForm.post(route('worker.services.store'), {
        onSuccess: () => { addForm.reset(); showAdd.value = false; },
    });
};

const startEdit = (s: Service) => {
    editId.value = s.id;
    editForm.service_name = s.service_name;
    editForm.description  = s.description ?? '';
    editForm.rate_type    = s.rate_type as any;
    editForm.base_rate    = s.base_rate;
    editForm.minimum_charge = s.minimum_charge ?? '';
};

const submitEdit = (id: number) => {
    editForm.put(route('worker.services.update', id), {
        onSuccess: () => { editId.value = null; },
    });
};

const destroy = (id: number) => {
    if (confirm('Remove this service?')) {
        router.delete(route('worker.services.destroy', id), { preserveScroll: true });
    }
};

const rateLabel = (type: string) => ({ hourly: '/hr', per_job: '/job', per_day: '/day' }[type] ?? '');
</script>

<template>
    <Head title="My Services" />

    <AuthenticatedLayout>
        <template #header>
            <nav class="flex text-sm text-gray-400 gap-2">
                <Link :href="route('worker.dashboard')" class="hover:text-white">Dashboard</Link>
                <span>/</span>
                <span class="text-white">Services</span>
            </nav>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">
            <div class="flex justify-between items-center">
                <h1 class="text-xl font-bold text-white">My Services</h1>
                <button @click="showAdd = !showAdd" class="flex items-center gap-1.5 bg-brand-red hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    <PlusIcon class="h-4 w-4" /> Add Service
                </button>
            </div>

            <!-- Add form -->
            <div v-if="showAdd" class="bg-gray-800 rounded-xl border border-brand-red p-5">
                <h2 class="text-white font-semibold mb-4">New Service</h2>
                <form @submit.prevent="submitAdd" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm text-gray-300 mb-1">Service Name <span class="text-red-400">*</span></label>
                            <input v-model="addForm.service_name" type="text" required maxlength="120"
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-300 mb-1">Rate Type</label>
                            <select v-model="addForm.rate_type" class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red">
                                <option value="per_job">Per Job</option>
                                <option value="hourly">Hourly</option>
                                <option value="per_day">Per Day</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-300 mb-1">Rate (ZMW) <span class="text-red-400">*</span></label>
                            <input v-model="addForm.base_rate" type="number" min="0" step="0.01" required
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm text-gray-300 mb-1">Description (optional)</label>
                            <input v-model="addForm.description" type="text" maxlength="500"
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red" />
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" :disabled="addForm.processing" class="bg-brand-red hover:bg-red-700 disabled:opacity-50 text-white px-5 py-2 rounded-lg text-sm">Add</button>
                        <button type="button" @click="showAdd = false" class="text-gray-400 hover:text-white text-sm px-4">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Services list -->
            <div v-if="services.length === 0" class="text-center text-gray-400 py-16">
                <p>No services yet. Add your first service above.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-start">
                <div v-for="s in services" :key="s.id" class="bg-gray-800 rounded-xl border border-gray-700 p-4">
                    <div v-if="editId !== s.id" class="flex justify-between items-start">
                        <div>
                            <p class="text-white font-medium">{{ s.service_name }}</p>
                            <p v-if="s.description" class="text-gray-400 text-sm">{{ s.description }}</p>
                            <p class="text-brand-red font-semibold mt-1">K{{ s.base_rate }}<span class="text-gray-400 font-normal text-sm">{{ rateLabel(s.rate_type) }}</span></p>
                        </div>
                        <div class="flex gap-2">
                            <button @click="startEdit(s)" class="text-gray-400 hover:text-white p-1.5"><PencilIcon class="h-4 w-4" /></button>
                            <button @click="destroy(s.id)" class="text-gray-400 hover:text-red-400 p-1.5"><TrashIcon class="h-4 w-4" /></button>
                        </div>
                    </div>

                    <!-- Inline edit -->
                    <form v-else @submit.prevent="submitEdit(s.id)" class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="col-span-2">
                                <input v-model="editForm.service_name" type="text" required maxlength="120"
                                    class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red text-sm" />
                            </div>
                            <select v-model="editForm.rate_type" class="bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-red">
                                <option value="per_job">Per Job</option>
                                <option value="hourly">Hourly</option>
                                <option value="per_day">Per Day</option>
                            </select>
                            <input v-model="editForm.base_rate" type="number" min="0" step="0.01" required
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-red" />
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" :disabled="editForm.processing" class="bg-brand-red hover:bg-red-700 disabled:opacity-50 text-white px-4 py-1.5 rounded text-sm">Save</button>
                            <button type="button" @click="editId = null" class="text-gray-400 hover:text-white text-sm px-3">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
