<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { PlusIcon, TrashIcon, PhotoIcon } from '@heroicons/vue/24/outline';

interface Profile { id: number; tagline: string; }
interface Photo   { id: number; image_url: string; caption: string | null; sort_order: number; }

const props = defineProps<{
    profile: Profile;
    photos:  Photo[];
}>();

const showAdd = ref(false);
const preview = ref<string | null>(null);

const form = useForm({
    photo:   null as File | null,
    caption: '',
});

const onFile = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    form.photo = file;
    const reader = new FileReader();
    reader.onload = ev => { preview.value = ev.target?.result as string; };
    reader.readAsDataURL(file);
};

const submit = () => {
    form.post(route('worker.portfolio.store'), {
        onSuccess: () => { form.reset(); preview.value = null; showAdd.value = false; },
    });
};

const remove = (id: number) => {
    if (confirm('Remove this photo from your portfolio?')) {
        router.delete(route('worker.portfolio.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="My Portfolio" />

    <AuthenticatedLayout>
        <template #header>
            <nav class="flex text-sm text-gray-400 gap-2">
                <Link :href="route('worker.dashboard')" class="hover:text-white">Dashboard</Link>
                <span>/</span>
                <span class="text-white">Portfolio</span>
            </nav>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold text-white">Portfolio Photos</h1>
                    <p class="text-gray-400 text-sm mt-0.5">{{ photos.length }} / 20 photos</p>
                </div>
                <button v-if="photos.length < 20" @click="showAdd = !showAdd"
                    class="flex items-center gap-1.5 bg-brand-red hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    <PlusIcon class="h-4 w-4" /> Add Photo
                </button>
            </div>

            <!-- Add form -->
            <div v-if="showAdd" class="bg-gray-800 rounded-xl border border-brand-red p-5">
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Photo <span class="text-red-400">*</span></label>
                        <input type="file" accept="image/*" required @change="onFile"
                            class="text-sm text-gray-400 file:mr-3 file:py-1.5 file:px-3 file:border-0 file:rounded file:bg-gray-700 file:text-gray-300 hover:file:bg-gray-600" />
                    </div>
                    <img v-if="preview" :src="preview" class="h-40 rounded-lg object-cover" />
                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Caption (optional)</label>
                        <input v-model="form.caption" type="text" maxlength="120"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red" />
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" :disabled="form.processing" class="bg-brand-red hover:bg-red-700 disabled:opacity-50 text-white px-5 py-2 rounded-lg text-sm">Upload</button>
                        <button type="button" @click="showAdd = false; preview = null" class="text-gray-400 hover:text-white text-sm px-4">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Grid -->
            <div v-if="photos.length === 0" class="text-center text-gray-400 py-20 flex flex-col items-center gap-3">
                <PhotoIcon class="h-12 w-12 text-gray-600" />
                <p>No portfolio photos yet. Showcase your work by adding photos.</p>
            </div>

            <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                <div v-for="p in photos" :key="p.id" class="relative group rounded-xl overflow-hidden aspect-square bg-gray-700">
                    <img :src="`/storage/${p.image_url}`" :alt="p.caption ?? ''" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex flex-col justify-between p-2">
                        <button @click="remove(p.id)" class="self-end bg-red-600 hover:bg-red-700 text-white rounded-full p-1.5">
                            <TrashIcon class="h-4 w-4" />
                        </button>
                        <p v-if="p.caption" class="text-white text-xs">{{ p.caption }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
