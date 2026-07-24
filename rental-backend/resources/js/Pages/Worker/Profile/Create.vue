<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

interface Category { id: number; name: string; icon: string; }
interface Town      { id: number; name: string; }

const props = defineProps<{
    categories: Category[];
    towns:      Town[];
}>();

const form = useForm({
    trade_category_id: '' as string | number,
    town_id:           '' as string | number,
    tagline:           '',
    bio:               '',
    experience_years:  0,
    phone:             '',
    service_radius_km: '' as string | number,
    profile_photo:     null as File | null,
    certificate:       null as File | null,
});

const photoPreview = ref<string | null>(null);

const onPhoto = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    form.profile_photo = file;
    const reader = new FileReader();
    reader.onload = ev => { photoPreview.value = ev.target?.result as string; };
    reader.readAsDataURL(file);
};

const onCert = (e: Event) => {
    form.certificate = (e.target as HTMLInputElement).files?.[0] ?? null;
};

const submit = () => {
    form.post(route('worker.profile.store'));
};
</script>

<template>
    <Head title="Create Worker Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Become a Worker</h2>
        </template>

        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <p class="text-gray-400 text-sm mb-6">Set up your skilled worker profile to start receiving bookings on the marketplace.</p>

                <form @submit.prevent="submit" class="space-y-5" enctype="multipart/form-data">
                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Trade Category <span class="text-red-400">*</span></label>
                        <select v-model="form.trade_category_id" required class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red">
                            <option value="">— Select your trade —</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.icon }} {{ c.name }}</option>
                        </select>
                        <p v-if="form.errors.trade_category_id" class="text-red-400 text-xs mt-1">{{ form.errors.trade_category_id }}</p>
                    </div>

                    <!-- Tagline -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Tagline <span class="text-red-400">*</span></label>
                        <input v-model="form.tagline" type="text" maxlength="120" required
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red"
                            placeholder="e.g. Reliable electrician with 10 years experience" />
                        <p v-if="form.errors.tagline" class="text-red-400 text-xs mt-1">{{ form.errors.tagline }}</p>
                    </div>

                    <!-- Bio -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Bio / About You <span class="text-red-400">*</span></label>
                        <textarea v-model="form.bio" rows="5" required maxlength="2000"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red"
                            placeholder="Describe your skills, experience and what makes you stand out..."></textarea>
                        <p v-if="form.errors.bio" class="text-red-400 text-xs mt-1">{{ form.errors.bio }}</p>
                    </div>

                    <!-- Experience + Phone -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Years of Experience <span class="text-red-400">*</span></label>
                            <input v-model="form.experience_years" type="number" min="0" max="50" required
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Phone Number <span class="text-red-400">*</span></label>
                            <input v-model="form.phone" type="tel" required
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red"
                                placeholder="+260 ..." />
                        </div>
                    </div>

                    <!-- Town + Radius -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Town / Location</label>
                            <select v-model="form.town_id" class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red">
                                <option value="">— Any —</option>
                                <option v-for="t in towns" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Service Radius (km)</label>
                            <input v-model="form.service_radius_km" type="number" min="1" max="500"
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red"
                                placeholder="e.g. 25" />
                        </div>
                    </div>

                    <!-- Profile photo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Profile Photo</label>
                        <div class="flex items-center gap-4">
                            <img v-if="photoPreview" :src="photoPreview" class="w-16 h-16 rounded-full object-cover" />
                            <div v-else class="w-16 h-16 rounded-full bg-gray-700 flex items-center justify-center text-gray-500 text-xl">👤</div>
                            <input type="file" accept="image/*" @change="onPhoto" class="text-sm text-gray-400 file:mr-3 file:py-1.5 file:px-3 file:border-0 file:rounded file:bg-gray-700 file:text-gray-300 hover:file:bg-gray-600" />
                        </div>
                    </div>

                    <!-- Certificate -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Certificate / Qualification (optional)</label>
                        <input type="file" accept=".pdf,.jpg,.jpeg,.png" @change="onCert"
                            class="text-sm text-gray-400 file:mr-3 file:py-1.5 file:px-3 file:border-0 file:rounded file:bg-gray-700 file:text-gray-300 hover:file:bg-gray-600" />
                        <p class="text-gray-500 text-xs mt-1">Upload a trade certificate or qualification document (PDF, JPG, PNG — max 5MB)</p>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="flex-1 bg-brand-red hover:bg-red-700 disabled:opacity-50 text-white font-semibold py-2.5 rounded-lg transition-colors">
                            {{ form.processing ? 'Creating...' : 'Create Worker Profile' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
