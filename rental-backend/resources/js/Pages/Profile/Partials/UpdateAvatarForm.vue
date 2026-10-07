<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { CameraIcon, TrashIcon } from '@heroicons/vue/24/outline';

const user = computed(() => usePage().props.auth.user);

const fileInput = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(null);

const form = useForm<{ avatar: File | null }>({ avatar: null });
const removeForm = useForm({});

// Show the freshly picked file until the upload lands, then fall back to whatever
// the server says the current avatar is.
const displayedUrl = computed(() => previewUrl.value ?? user.value.avatar_url);

const hasCustomAvatar = computed(() => Boolean(user.value.avatar));

const clearPreview = () => {
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = null;
    form.avatar = null;
    if (fileInput.value) fileInput.value.value = '';
};

const onFileSelected = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;

    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = URL.createObjectURL(file);
    form.avatar = file;
};

const upload = () => {
    if (!form.avatar) return;

    form.post(route('profile.avatar.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => clearPreview(),
    });
};

const remove = () => {
    if (!confirm('Remove your profile photo?')) return;

    removeForm.delete(route('profile.avatar.destroy'), {
        preserveScroll: true,
        onSuccess: () => clearPreview(),
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-white">Profile Photo</h2>
            <p class="mt-1 text-sm text-gray-400">
                Upload a photo so people recognise you. JPG, PNG or WebP, up to 2&nbsp;MB.
            </p>
        </header>

        <div class="mt-6 flex flex-col sm:flex-row sm:items-center gap-6">
            <!-- Preview -->
            <div class="relative flex-shrink-0">
                <img
                    :src="displayedUrl"
                    :alt="`${user.name}'s profile photo`"
                    class="h-24 w-24 rounded-full object-cover ring-2 ring-gray-700 bg-gray-700"
                />
                <button
                    type="button"
                    @click="fileInput?.click()"
                    class="absolute -bottom-1 -right-1 rounded-full bg-brand-red p-2 text-white shadow-lg hover:bg-brand-orange transition-colors"
                    title="Choose a photo"
                >
                    <CameraIcon class="h-4 w-4" />
                </button>
            </div>

            <div class="flex-1 space-y-3">
                <input
                    ref="fileInput"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="hidden"
                    @change="onFileSelected"
                />

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        @click="fileInput?.click()"
                        class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-gray-700 transition-colors"
                    >
                        Choose photo
                    </button>

                    <PrimaryButton v-if="form.avatar" :disabled="form.processing" @click="upload">
                        {{ form.processing ? 'Uploading…' : 'Save photo' }}
                    </PrimaryButton>

                    <button
                        v-if="form.avatar"
                        type="button"
                        @click="clearPreview"
                        class="text-sm text-gray-400 hover:text-white"
                    >
                        Cancel
                    </button>

                    <button
                        v-else-if="hasCustomAvatar"
                        type="button"
                        @click="remove"
                        :disabled="removeForm.processing"
                        class="flex items-center gap-1.5 text-sm text-red-400 hover:text-red-300 disabled:opacity-50"
                    >
                        <TrashIcon class="h-4 w-4" />
                        Remove photo
                    </button>
                </div>

                <div v-if="form.progress" class="h-1.5 w-full max-w-xs overflow-hidden rounded-full bg-gray-700">
                    <div class="h-full bg-brand-red transition-all" :style="{ width: `${form.progress.percentage}%` }" />
                </div>

                <InputError :message="form.errors.avatar" />

                <p v-if="!hasCustomAvatar && !form.avatar" class="text-xs text-gray-500">
                    You're currently using a generated avatar based on your initials.
                </p>
            </div>
        </div>
    </section>
</template>
