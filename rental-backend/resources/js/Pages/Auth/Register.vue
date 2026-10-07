<script setup lang="ts">
import AppButton from '@/Components/AppButton.vue';
import AuthDivider from '@/Components/AuthDivider.vue';
import GoogleAuthButton from '@/Components/GoogleAuthButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import FileProgress from '@/Components/FileProgress.vue';
import TextInput from '@/Components/TextInput.vue';
import WizardProgress from '@/Components/WizardProgress.vue';
import { useFormDraft } from '@/composables/useFormDraft';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { HomeIcon, BuildingOffice2Icon, XCircleIcon, ArrowPathIcon } from '@heroicons/vue/24/solid';
import { computed, onMounted, reactive, ref } from 'vue';

const MAX_MB = 5;
const MAX_BYTES = MAX_MB * 1024 * 1024;

const STEPS = ['Account Type', 'Account Details', 'Identity Verification'];

type FileField = 'id_document' | 'selfie';

interface FileState {
    name: string;
    sizeMB: number;
    pct: number;
    tooLarge: boolean;
}

const fileStates = reactive<Partial<Record<FileField, FileState>>>({});

const step = ref(1);

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

// ── Draft persistence ───────────────────────────────────────────────────
// Passwords and the File fields are deliberately excluded — passwords
// shouldn't sit in localStorage, and File objects can't survive JSON anyway.
const DRAFT_KEY = 'registration:tenant:draft';
const draftRestored = ref(false);

interface Draft {
    step: number;
    name: string;
    email: string;
    phone: string;
    id_type: 'nrc' | 'passport';
    nrc_passport: string;
    [key: string]: unknown;
}

const { save: saveDraft, restore: restoreDraft, clear: clearDraft } = useFormDraft<Draft>(
    DRAFT_KEY,
    () => ({
        step: step.value,
        name: form.name,
        email: form.email,
        phone: form.phone,
        id_type: form.id_type,
        nrc_passport: form.nrc_passport,
    }),
    (data) => {
        if (data.step && data.step > 1) step.value = data.step;
        if (data.name) form.name = data.name;
        if (data.email) form.email = data.email;
        if (data.phone) form.phone = data.phone;
        if (data.id_type) form.id_type = data.id_type;
        if (data.nrc_passport) form.nrc_passport = data.nrc_passport;
    },
);

onMounted(() => {
    draftRestored.value = restoreDraft() && step.value > 1;
});

const persistDraft = () => saveDraft();

const startOver = () => {
    clearDraft();
    draftRestored.value = false;
    step.value = 1;
    form.reset();
};

// ── Step navigation ─────────────────────────────────────────────────────
// Advancing past a step validates it first — errors surface inline via the
// same `form.errors` the backend uses, and the step only moves on if clean.
const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const validateDetailsStep = (): boolean => {
    form.clearErrors();

    if (!form.name.trim()) form.setError('name', 'Full name is required.');

    if (!form.email.trim()) {
        form.setError('email', 'Email is required.');
    } else if (!EMAIL_PATTERN.test(form.email)) {
        form.setError('email', 'Enter a valid email address.');
    }

    if (!form.phone.trim()) form.setError('phone', 'Phone number is required.');

    if (!form.password) {
        form.setError('password', 'Password is required.');
    } else if (form.password.length < 8) {
        form.setError('password', 'Password must be at least 8 characters.');
    }

    if (!form.password_confirmation) {
        form.setError('password_confirmation', 'Please confirm your password.');
    } else if (form.password && form.password !== form.password_confirmation) {
        form.setError('password_confirmation', 'Passwords do not match.');
    }

    return Object.keys(form.errors).length === 0;
};

const chooseTenant = () => {
    step.value = 2;
    persistDraft();
};

const goToStep = (target: number) => {
    form.clearErrors();
    step.value = target;
    persistDraft();
};

const nextFromDetails = () => {
    if (!validateDetailsStep()) return;
    goToStep(3);
};

// ── Files ────────────────────────────────────────────────────────────────
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
        onSuccess: () => clearDraft(),
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
                    {{ step > 1 ? 'Register as Tenant' : 'Create Account' }}
                </h1>
                <p class="mt-2 text-gray-400">
                    {{ step > 1 ? 'Create your account and verify your identity to start renting' : 'Join our community today — choose how you want to get started' }}
                </p>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Progress -->
            <WizardProgress :steps="STEPS" :current-step="step" />

            <!-- Restored draft banner -->
            <div
                v-if="draftRestored"
                class="flex items-center justify-between gap-4 rounded-lg bg-brand-info/10 border border-brand-info/30 px-4 py-3 text-sm text-blue-300"
            >
                <span class="flex items-center gap-2">
                    <ArrowPathIcon class="h-4 w-4 shrink-0" />
                    We restored your in-progress registration.
                </span>
                <button type="button" @click="startOver" class="underline hover:text-white shrink-0">
                    Start over
                </button>
            </div>

            <!-- Step 1: Role chooser -->
            <div v-if="step === 1" class="space-y-6">
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
                <!-- Step 2: Account details -->
                <template v-if="step === 2">
                    <!-- Google -->
                    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                        <GoogleAuthButton label="Sign up with Google" />
                        <div class="pt-4">
                            <AuthDivider label="Or sign up with email" />
                        </div>
                    </div>

                    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                        <h3 class="text-lg font-semibold mb-6 text-white">Account Details</h3>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <InputLabel for="name" value="Full Name *" />
                                <TextInput
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full focus:border-brand-red focus:ring-brand-red"
                                    required
                                    autofocus
                                    autocomplete="name"
                                    @input="persistDraft"
                                />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="email" value="Email Address *" />
                                    <TextInput
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        class="mt-1 block w-full focus:border-brand-red focus:ring-brand-red"
                                        required
                                        autocomplete="username"
                                        @input="persistDraft"
                                    />
                                    <InputError :message="form.errors.email" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="phone" value="Phone Number *" />
                                    <TextInput
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        class="mt-1 block w-full focus:border-brand-red focus:ring-brand-red"
                                        placeholder="+260 XXX XXX XXX"
                                        required
                                        @input="persistDraft"
                                    />
                                    <InputError :message="form.errors.phone" class="mt-2" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="password" value="Password *" />
                                    <TextInput
                                        id="password"
                                        v-model="form.password"
                                        type="password"
                                        class="mt-1 block w-full focus:border-brand-red focus:ring-brand-red"
                                        required
                                        autocomplete="new-password"
                                    />
                                    <InputError :message="form.errors.password" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="password_confirmation" value="Confirm Password *" />
                                    <TextInput
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        type="password"
                                        class="mt-1 block w-full focus:border-brand-red focus:ring-brand-red"
                                        required
                                        autocomplete="new-password"
                                    />
                                    <InputError :message="form.errors.password_confirmation" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Step 3: Identity verification -->
                <template v-if="step === 3">
                    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                        <h3 class="text-lg font-semibold mb-1 text-white">Identity Verification</h3>
                        <p class="text-xs text-gray-500 mb-6">Maximum file size: <span class="text-gray-300 font-medium">5 MB</span> per file. Accepted formats: PDF, JPG, PNG.</p>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <InputLabel value="ID Type *" />
                                <div class="mt-1 flex gap-6">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" v-model="form.id_type" value="nrc" class="text-brand-red focus:ring-brand-red" @change="persistDraft" />
                                        <span class="text-sm text-gray-300">NRC (Zambian citizen)</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" v-model="form.id_type" value="passport" class="text-brand-red focus:ring-brand-red" @change="persistDraft" />
                                        <span class="text-sm text-gray-300">Passport (non-citizen)</span>
                                    </label>
                                </div>
                                <InputError :message="form.errors.id_type" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="nrc_passport" :value="form.id_type === 'nrc' ? 'NRC Number *' : 'Passport Number *'" />
                                <TextInput
                                    id="nrc_passport"
                                    v-model="form.nrc_passport"
                                    type="text"
                                    class="mt-1 block w-full focus:border-brand-red focus:ring-brand-red"
                                    :placeholder="form.id_type === 'nrc' ? 'e.g. 123456/78/1' : 'e.g. A1234567'"
                                    required
                                    @input="persistDraft"
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
                </template>

                <!-- Step footer: navigation -->
                <div class="flex items-center justify-between gap-4">
                    <AppButton variant="secondary" @click="goToStep(step - 1)">
                        Previous
                    </AppButton>

                    <div class="flex items-center gap-4">
                        <Link
                            :href="route('login')"
                            class="rounded-md text-sm text-gray-400 underline hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-orange focus:ring-offset-2 focus:ring-offset-dark-bg"
                        >
                            Already registered?
                        </Link>

                        <AppButton v-if="step === 2" @click="nextFromDetails">
                            Next
                        </AppButton>

                        <AppButton v-else type="submit" :loading="form.processing" :disabled="form.processing || hasOversizedFile">
                            Register
                        </AppButton>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
