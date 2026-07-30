<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PaymentMethodPicker from '@/Components/PaymentMethodPicker.vue';
import { ref } from 'vue';

interface Tier {
    id: number;
    display_name: string;
    price_display: string;
    price_amount: number;
}

const props = defineProps<{ tier: Tier }>();

const billingCycle = ref<'monthly' | 'annual'>('monthly');

const form = useForm({
    billing_cycle: 'monthly',
});

const submitCheckout = (paymentPayload: Record<string, string>) => {
    form
        .transform(() => ({ ...paymentPayload, billing_cycle: billingCycle.value }))
        .post(route('payments.subscribe.store', props.tier.id));
};
</script>

<template>
    <Head title="Checkout" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Checkout — {{ tier.display_name }}</h2>
        </template>

        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
                <div class="flex items-center justify-between">
                    <span class="text-gray-300 font-medium">{{ tier.display_name }}</span>
                    <span class="text-white font-bold">{{ tier.price_display }}</span>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Billing cycle</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="billingCycle = 'monthly'"
                            :class="['py-2 rounded-lg text-sm font-medium border transition-colors',
                                billingCycle === 'monthly' ? 'bg-brand-red/10 border-brand-red text-white' : 'bg-gray-700 border-gray-600 text-gray-400']">
                            Monthly
                        </button>
                        <button type="button" @click="billingCycle = 'annual'"
                            :class="['py-2 rounded-lg text-sm font-medium border transition-colors',
                                billingCycle === 'annual' ? 'bg-brand-red/10 border-brand-red text-white' : 'bg-gray-700 border-gray-600 text-gray-400']">
                            Annual
                        </button>
                    </div>
                </div>
            </div>

            <PaymentMethodPicker
                :amount="tier.price_amount"
                :processing="form.processing"
                submit-label="Subscribe & Pay"
                @submit="submitCheckout"
            />
        </div>
    </AuthenticatedLayout>
</template>
