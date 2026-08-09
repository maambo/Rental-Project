<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FileProgress from '@/Components/FileProgress.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { HomeIcon, BuildingOffice2Icon, XCircleIcon } from '@heroicons/vue/24/solid';
import { onMounted, onUnmounted, reactive, computed, ref } from 'vue';

const MAX_MB = 5;
const MAX_BYTES = MAX_MB * 1024 * 1024;

type FileField = 'id_document' | 'selfie';

interface FileState {
    name: string;
    sizeMB: number;
    pct: number;
    tooLarge: boolean;
}

const fileStates = reactive<Partial<Record<FileField, FileState>>>({});

const roleFromUrl = () =>
    new URLSearchParams(window.location.search).get('as') === 'tenant' ? 'tenant' : null;

const role = ref<'tenant' | null>(roleFromUrl());

const chooseTenant = () => {
    role.value = 'tenant';
    window.history.pushState({}, '', route('register') + '?as=tenant');
};

const back = () => {
    role.value = null;
    window.history.pushState({}, '', route('register'));
};

const syncRole = () => {
    role.value = roleFromUrl();
};

onMounted(() => window.addEventListener('popstate', syncRole));
onUnmounted(() => window.removeEventListener('popstate', syncRole));

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    id_type: 'nrc' as 'nrc' | 'passport',
    nrc_passport: '',
    id_document: null as File | null,
    selfie: null as File | null,
});

const handleFileChange = (e: Event, field: FileField) => {
    const target = e.target as HTMLInputElement;
    if (!target.files || !target.files[0]) return;

    const file = target.files[0];
    const tooLarge = file.size > MAX_BYTES;

    fileStates[field] = {
        name: file.name,
        sizeMB: file.size / (1024 * 1024),
        pct: Math.min((file.size / MAX_BYTES) * 100, 100),
        tooLarge,
    };

    if (!tooLarge) {
        form[field] = file as any;
    } else {
        form[field] = null as any;
        target.value = '';
    }
};

const hasOversizedFile = computed(() =>
    Object.values(fileStates).some(s => s?.tooLarge)
);

const submit = () => {
    if (hasOversizedFile.value) return;
    form.post(route('register'), {
        forceFormData: true,
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head title="Register" />

    <div class="min-h-screen bg-dark-bg">
        <!-- Header -->
        <div class="bg-gray-800 border-b border-gray-700">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="mb-4">
                    <Link href="/" class="inline-flex items-center gap-2 text-gray-400 hover:text-brand-red transition">
                        <HomeIcon class="h-6 w-6" />
                        <span class="font-medium">Back to Home</span>
                    </Link>
                </div>
                <h1 class="text-3xl font-bold text-white">
                    {{ role ? 'Register as Tenant' : 'Create Account' }}
                </h1>
                <p class="mt-2 text-gray-400">
                    {{ role ? 'Create your account and verify your identity to start renting' : 'Join our community today — choose how you want to get started' }}
                </p>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Role chooser -->
            <div v-if="!role" class="space-y-6">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-6 text-white">Choose your account type</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <button
                            type="button"
                            @click="chooseTenant"
                            class="cursor-pointer border-2 border-gray-700 hover:border-brand-red/50 rounded-xl p-6 text-left transition focus:outline-none focus:border-brand-red"
                        >
                            <HomeIcon class="h-8 w-8 text-brand-red mb-4" />
                            <h4 class="font-bold text-xl mb-2 text-white">Register as Tenant</h4>
                            <p class="text-sm text-gray-400 mb-4">I'm looking for a place to rent</p>
                            <ul class="space-y-2">
                                <li class="text-sm text-gray-400 flex items-center">
                                    <span class="mr-2 text-green-400">✓</span> Browse and apply for listings
                                </li>
                                <li class="text-sm text-gray-400 flex items-center">
                                    <span class="mr-2 text-green-400">✓</span> Schedule property tours
                                </li>
                                <li class="text-sm text-gray-400 flex items-center">
                                    <span class="mr-2 text-green-400">✓</span> Sign up with Google or email
                                </li>
                            </ul>
                        </button>

                        <Link
                            :href="route('landlord.apply')"
                            class="block cursor-pointer border-2 border-gray-700 hover:border-brand-red/50 rounded-xl p-6 text-left transition focus:outline-none focus:border-brand-red"
                        >
                            <BuildingOffice2Icon class="h-8 w-8 text-brand-red mb-4" />
                            <h4 class="font-bold text-xl mb-2 text-white">Register as Landlord</h4>
                            <p class="text-sm text-gray-400 mb-4">I want to list and manage properties</p>
                            <ul class="space-y-2">
                                <li class="text-sm text-gray-400 flex items-center">
                                    <span class="mr-2 text-green-400">✓</span> List and manage properties
                                </li>
                                <li class="text-sm text-gray-400 flex items-center">
                                    <span class="mr-2 text-green-400">✓</span> Review tenant applications
                                </li>
                                <li class="text-sm text-gray-400 flex items-center">
                                    <span class="mr-2 text-green-400">✓</span> Requires document verification
                                </li>
                            </ul>
                        </Link>
                    </div>
                </div>

                <div class="text-center">
                    <Link
                        :href="route('login')"
                        class="rounded-md text-sm text-gray-400 underline hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-orange focus:ring-offset-2 focus:ring-offset-dark-bg"
                    >
                        Already registered?
                    </Link>
                </div>
            </div>

            <!-- Tenant registration -->
            <form v-else @submit.prevent="submit" class="space-y-6">
                <button
                    type="button"
                    @click="back"
                    class="inline-flex items-center text-sm text-gray-400 hover:text-brand-red transition focus:outline-none"
                >
                    &larr; Choose a different account type
                </button>

                <!-- Google -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <a :href="route('auth.google')" class="flex items-center justify-center w-full px-4 py-2.5 border border-gray-600 rounded-lg shadow-sm bg-gray-700 text-sm font-medium text-gray-200 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-brand-red transition">
                        <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                            <g transform="matrix(1, 0, 0, 1, 27.009001, -39.238998)">
                                <path fill="#4285F4" d="M -3.264 51.509 C -3.264 50.719 -3.334 49.969 -3.454 49.239 L -14.754 49.239 L -14.754 53.749 L -8.284 53.749 C -8.574 55.229 -9.424 56.479 -10.684 57.329 L -10.684 60.329 L -6.824 60.329 C -4.564 58.239 -3.264 55.159 -3.264 51.509 Z" />
                                <path fill="#34A853" d="M -14.754 63.239 C -11.514 63.239 -8.804 62.159 -6.824 60.329 L -10.684 57.329 C -11.764 58.049 -13.134 58.489 -14.754 58.489 C -17.884 58.489 -20.534 56.379 -21.484 53.529 L -25.464 53.529 L -25.464 56.619 C -23.494 60.539 -19.444 63.239 -14.754 63.239 Z" />
                                <path fill="#FBBC05" d="M -21.484 53.529 C -21.734 52.809 -21.864 52.039 -21.864 51.239 C -21.864 50.439 -21.734 49.669 -21.484 48.949 L -21.484 45.859 L -25.464 45.859 C -26.284 47.479 -26.754 49.299 -26.754 51.239 C -26.754 53.179 -26.284 54.999 -25.464 56.619 L -21.484 53.529 Z" />
                                <path fill="#EA4335" d="M -14.754 43.989 C -12.984 43.989 -11.404 44.599 -10.154 45.789 L -6.734 42.369 C -8.804 40.429 -11.514 39.239 -14.754 39.239 C -19.444 39.239 -23.494 41.939 -25.464 45.859 L -21.484 48.949 C -20.534 46.099 -17.884 43.989 -14.754 43.989 Z" />
                            </g>
                        </svg>
                        Sign up with Google
                    </a>
                    <div class="relative flex pt-4 items-center">
                        <div class="flex-grow border-t border-gray-700"></div>
                        <span class="flex-shrink-0 mx-4 text-gray-400 text-sm">Or sign up with email</span>
                        <div class="flex-grow border-t border-gray-700"></div>
                    </div>
                </div>

                <!-- Account details -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-6 text-white">1. Account Details</h3>

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <InputLabel for="name" value="Full Name *" />
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-gray-200 shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                                required
                                autofocus
                                autocomplete="name"
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="email" value="Email Address *" />
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-gray-200 shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                                    required
                                    autocomplete="username"
                                />
                                <InputError :message="form.errors.email" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="phone" value="Phone Number *" />
                                <input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-gray-200 shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                                    placeholder="+260 XXX XXX XXX"
                                    required
                                />
                                <InputError :message="form.errors.phone" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="password" value="Password *" />
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-gray-200 shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                                    required
                                    autocomplete="new-password"
                                />
                                <InputError :message="form.errors.password" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="password_confirmation" value="Confirm Password *" />
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-gray-200 shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                                    required
                                    autocomplete="new-password"
                                />
                                <InputError :message="form.errors.password_confirmation" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Identity verification -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-1 text-white">2. Identity Verification</h3>
                    <p class="text-xs text-gray-500 mb-6">Maximum file size: <span class="text-gray-300 font-medium">5 MB</span> per file. Accepted formats: PDF, JPG, PNG.</p>

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <InputLabel value="ID Type *" />
                            <div class="mt-1 flex gap-6">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="form.id_type" value="nrc" class="text-brand-red focus:ring-brand-red" />
                                    <span class="text-sm text-gray-300">NRC (Zambian citizen)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="form.id_type" value="passport" class="text-brand-red focus:ring-brand-red" />
                                    <span class="text-sm text-gray-300">Passport (non-citizen)</span>
                                </label>
                            </div>
                            <InputError :message="form.errors.id_type" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="nrc_passport" :value="form.id_type === 'nrc' ? 'NRC Number *' : 'Passport Number *'" />
                            <input
                                id="nrc_passport"
                                v-model="form.nrc_passport"
                                type="text"
                                class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-gray-200 shadow-sm focus:border-brand-red focus:ring focus:ring-brand-red focus:ring-opacity-50"
                                :placeholder="form.id_type === 'nrc' ? 'e.g. 123456/78/1' : 'e.g. A1234567'"
                                required
                            />
                            <InputError :message="form.errors.nrc_passport" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="id_document" :value="form.id_type === 'nrc' ? 'NRC Document (photo/scan) *' : 'Passport (photo/scan) *'" />
                            <input
                                id="id_document"
                                type="file"
                                accept=".pdf,.jpg,.jpeg,.png"
                                required
                                class="mt-1 block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-red/10 file:text-brand-red hover:file:bg-brand-red/20"
                                @change="(e) => handleFileChange(e, 'id_document')"
                            />
                            <FileProgress :state="fileStates.id_document" />
                            <InputError :message="form.errors.id_document" class="mt-1" />
                        </div>

                        <div class="border-t border-gray-700 pt-4 mt-2">
                            <InputLabel for="selfie" value="Selfie / Live Photo *" />
                            <p class="text-xs text-gray-400 mb-2">A clear photo of your face — used to match your ID document.</p>
                            <input
                                id="selfie"
                                type="file"
                                accept=".jpg,.jpeg,.png"
                                required
                                class="mt-1 block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-red/10 file:text-brand-red hover:file:bg-brand-red/20"
                                @change="(e) => handleFileChange(e, 'selfie')"
                            />
                            <FileProgress :state="fileStates.selfie" />
                            <InputError :message="form.errors.selfie" class="mt-1" />
                        </div>
                    </div>
                </div>

                <!-- Oversized global warning -->
                <div v-if="hasOversizedFile" class="flex items-center gap-2 rounded-lg bg-red-500/10 border border-red-500/30 px-4 py-3 text-sm text-red-400">
                    <XCircleIcon class="h-5 w-5 shrink-0" />
                    One or more files exceed the 5 MB limit. Please remove them before submitting.
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Link
                        :href="route('login')"
                        class="rounded-md text-sm text-gray-400 underline hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-orange focus:ring-offset-2 focus:ring-offset-dark-bg"
                    >
                        Already registered?
                    </Link>

                    <PrimaryButton :disabled="form.processing || hasOversizedFile">
                        Register
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>
