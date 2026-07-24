<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

interface Category { id: number; name: string; icon: string; }
interface Town      { id: number; name: string; }
interface Profile {
    id: number; trade_category_id: number; town_id: number | null;
    tagline: string; bio: string; experience_years: number; phone: string;
    service_radius_km: number | null; is_active: boolean;
    profile_photo_url: string | null; certificate_url: string | null;
}

const props = defineProps<{
    profile:    Profile;
    categories: Category[];
    towns:      Town[];
}>();

const form = useForm({
    trade_category_id: props.profile.trade_category_id,
    town_id:           props.profile.town_id ?? '',
    tagline:           props.profile.tagline,
    bio:               props.profile.bio,
    experience_years:  props.profile.experience_years,
    phone:             props.profile.phone,
    service_radius_km: props.profile.service_radius_km ?? '',
    is_active:         props.profile.is_active,
    profile_photo:     null as File | null,
    certificate:       null as File | null,
});

const photoPreview = ref<string | null>(
    props.profile.profile_photo_url ? `/storage/${props.profile.profile_photo_url}` : null
);

const onPhoto = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    form.profile_photo = file;
    const reader = new FileReader();
    reader.onload = ev => { photoPreview.value = ev.target?.result as string; };
    reader.readAsDataURL(file);
};

const submit = () => {
    form.put(route('worker.profile.update'));
};
</script>

<template>
    <Head title="Edit Worker Profile" />

    <AuthenticatedLayout>
        <template #header>
            <nav class="flex text-sm text-gray-400 gap-2">
                <Link :href="route('worker.dashboard')" class="hover:text-white">Dashboard</Link>
                <span>/</span>
                <span class="text-white">Edit Profile</span>
            </nav>
        </template>

        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Trade Category</label>
                        <select v-model="form.trade_category_id" class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red">
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.icon }} {{ c.name }}</option>
                        </select>
                    </div>

                    <!-- Tagline -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Tagline</label>
                        <input v-model="form.tagline" type="text" maxlength="120"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red" />
                        <p v-if="form.errors.tagline" class="text-red-400 text-xs mt-1">{{ form.errors.tagline }}</p>
                    </div>

                    <!-- Bio -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Bio</label>
                        <textarea v-model="form.bio" rows="5" maxlength="2000"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red"></textarea>
                    </div>

                    <!-- Experience + Phone -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Years of Experience</label>
                            <input v-model="form.experience_years" type="number" min="0" max="50"
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Phone Number</label>
                            <input v-model="form.phone" type="tel"
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red" />
                        </div>
                    </div>

                    <!-- Town + Radius -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Town</label>
                            <select v-model="form.town_id" class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red">
                                <option value="">— Any —</option>
                                <option v-for="t in towns" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Service Radius (km)</label>
                            <input v-model="form.service_radius_km" type="number" min="1" max="500"
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red" />
                        </div>
                    </div>

                    <!-- Active toggle -->
                    <div class="flex items-center gap-3">
                        <input v-model="form.is_active" type="checkbox" id="is_active" class="h-4 w-4 accent-brand-red rounded" />
                        <label for="is_active" class="text-sm text-gray-300">Profile visible in marketplace</label>
                    </div>

                    <!-- Photo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Profile Photo</label>
                        <div class="flex items-center gap-4">
                            <img v-if="photoPreview" :src="photoPreview" class="w-16 h-16 rounded-full object-cover" />
                            <div v-else class="w-16 h-16 rounded-full bg-gray-700 flex items-center justify-center text-gray-500 text-xl">👤</div>
                            <input type="file" accept="image/*" @change="onPhoto" class="text-sm text-gray-400 file:mr-3 file:py-1.5 file:px-3 file:border-0 file:rounded file:bg-gray-700 file:text-gray-300 hover:file:bg-gray-600" />
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="flex-1 bg-brand-red hover:bg-red-700 disabled:opacity-50 text-white font-semibold py-2.5 rounded-lg transition-colors">
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                        <Link :href="route('worker.dashboard')" class="px-6 py-2.5 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 text-sm">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
