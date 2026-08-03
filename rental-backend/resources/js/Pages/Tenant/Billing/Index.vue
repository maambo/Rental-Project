<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PaymentMethodPicker from '@/Components/PaymentMethodPicker.vue';
import { ref } from 'vue';

interface Billing {
    id: number;
    Amount: number;
    Date: string;
    Description: string;
    status: string;
    proof_of_payment: string | null;
    lease_agreement: { property: { title: string } | null } | null;
}

defineProps<{
    billings: { data: Billing[]; links: any[]; meta: any };
}>();

const payingId = ref<number | null>(null);
const uploadingId = ref<number | null>(null);

const payForm = useForm({});
const uploadForm = useForm({ proof_of_payment: null as File | null });

const submitPayment = (billingId: number, payload: Record<string, string>) => {
    payForm.transform(() => payload).post(route('tenant.billing.pay', billingId), {
        onSuccess: () => { payingId.value = null; },
    });
};

const submitProof = (billingId: number) => {
    uploadForm.post(route('tenant.billing.confirm', billingId), {
        onSuccess: () => { uploadingId.value = null; uploadForm.reset(); },
    });
};

const onFile = (e: Event) => {
    uploadForm.proof_of_payment = (e.target as HTMLInputElement).files?.[0] ?? null;
};

const statusPill = (s: string) => ({
    pending: 'bg-yellow-500/20 text-yellow-400',
    paid: 'bg-green-500/20 text-green-400',
    partially_paid: 'bg-blue-500/20 text-blue-400',
    cancelled: 'bg-gray-500/20 text-gray-400',
}[s] ?? 'bg-gray-500/20 text-gray-400');

const fmt = (n: number) => 'K' + Number(n).toLocaleString();
</script>

<template>
    <Head title="My Billing" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">My Billing</h2>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">
            <div v-if="billings.data.length === 0" class="bg-gray-800 rounded-xl border border-gray-700 p-10 text-center text-gray-500">
                No bills yet.
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
            <div v-for="b in billings.data" :key="b.id" class="bg-gray-800 rounded-xl border border-gray-700 p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-white font-semibold">{{ b.Description }}</p>
                        <p class="text-gray-400 text-sm mt-0.5">{{ b.lease_agreement?.property?.title ?? '—' }}</p>
                        <p class="text-gray-500 text-xs mt-1">{{ new Date(b.Date).toLocaleDateString() }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <div class="text-white font-bold">{{ fmt(b.Amount) }}</div>
                        <span :class="['inline-block mt-1 px-2 py-0.5 rounded-full text-xs capitalize', statusPill(b.status)]">{{ b.status.replace('_', ' ') }}</span>
                    </div>
                </div>

                <template v-if="b.status !== 'paid'">
                    <div v-if="payingId !== b.id && uploadingId !== b.id" class="flex gap-3 mt-4">
                        <button @click="payingId = b.id"
                            class="flex-1 bg-brand-red hover:bg-red-700 text-white text-sm font-semibold py-2 rounded-lg transition-colors">
                            Pay Now
                        </button>
                        <button @click="uploadingId = b.id"
                            class="flex-1 border border-gray-600 text-gray-300 hover:bg-gray-700 text-sm font-semibold py-2 rounded-lg transition-colors">
                            Upload Proof of Payment
                        </button>
                    </div>

                    <div v-if="payingId === b.id" class="mt-4">
                        <PaymentMethodPicker
                            :amount="b.Amount"
                            :processing="payForm.processing"
                            submit-label="Confirm & Pay"
                            @submit="(payload) => submitPayment(b.id, payload)"
                        />
                        <button @click="payingId = null" class="w-full mt-2 text-gray-500 hover:text-gray-300 text-sm">Cancel</button>
                    </div>

                    <div v-if="uploadingId === b.id" class="mt-4 bg-gray-900/50 rounded-lg p-4 border border-gray-700">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Upload proof of payment (bank slip, screenshot, etc.)</label>
                        <input type="file" accept=".jpg,.jpeg,.png,.pdf" @change="onFile"
                            class="text-sm text-gray-400 file:mr-3 file:py-1.5 file:px-3 file:border-0 file:rounded file:bg-gray-700 file:text-gray-300 hover:file:bg-gray-600" />
                        <p v-if="uploadForm.errors.proof_of_payment" class="text-red-400 text-xs mt-1">{{ uploadForm.errors.proof_of_payment }}</p>
                        <div class="flex gap-3 mt-3">
                            <button @click="submitProof(b.id)" :disabled="uploadForm.processing || !uploadForm.proof_of_payment"
                                class="bg-brand-red hover:bg-red-700 disabled:opacity-50 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
                                Submit
                            </button>
                            <button @click="uploadingId = null" class="text-gray-500 hover:text-gray-300 text-sm px-3">Cancel</button>
                        </div>
                    </div>
                </template>

                <a v-else-if="b.proof_of_payment" :href="'/storage/' + b.proof_of_payment" target="_blank"
                   class="inline-block text-xs text-blue-400 hover:underline mt-3">View submitted proof</a>
            </div>
            </div>

            <div v-if="billings.links.length > 3" class="flex justify-center gap-1">
                <Link v-for="link in billings.links" :key="link.label"
                    :href="link.url ?? '#'"
                    :class="['px-3 py-1.5 rounded text-sm', link.active ? 'bg-brand-red text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700', !link.url ? 'opacity-40 pointer-events-none' : '']"
                    v-html="link.label" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
