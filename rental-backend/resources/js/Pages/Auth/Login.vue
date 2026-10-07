<script setup lang="ts">
import AuthDivider from '@/Components/AuthDivider.vue';
import Checkbox from '@/Components/Checkbox.vue';
import GoogleAuthButton from '@/Components/GoogleAuthButton.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-400">
            {{ status }}
        </div>

        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-white">Welcome Back</h2>
            <p class="text-sm text-gray-400">Sign in to manage your account</p>
        </div>

        <!-- Google Sign In -->
        <div class="mb-6">
            <GoogleAuthButton label="Sign in with Google" />
        </div>

        <div class="mb-6">
            <AuthDivider label="Or sign in with email" />
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full focus:border-brand-orange focus:ring-brand-orange"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full focus:border-brand-orange focus:ring-brand-orange"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" class="text-brand-red focus:ring-brand-red" />
                    <span class="ms-2 text-sm text-gray-400"
                        >Remember me</span
                    >
                </label>
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-gray-400 underline hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-orange focus:ring-offset-2 focus:ring-offset-gray-800"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton
                    class="ms-4 bg-gradient-to-r from-brand-red to-brand-orange hover:from-red-600 hover:to-orange-600 focus:bg-brand-red active:bg-brand-red focus:ring-brand-orange"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
            
            <div class="mt-6 text-center text-sm text-gray-400">
                Don't have an account?
                <Link :href="route('register')" class="text-brand-red hover:text-brand-orange font-semibold">
                    Register
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
