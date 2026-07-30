<script setup lang="ts">
import { computed, ref } from 'vue';

type Method = 'mobile_money' | 'card';
type Provider = 'mtn' | 'airtel' | 'zamtel';

const props = withDefaults(defineProps<{
    amount?: number | string;
    processing?: boolean;
    submitLabel?: string;
}>(), {
    processing: false,
    submitLabel: 'Pay Now',
});

const emit = defineEmits<{
    submit: [payload: Record<string, string>];
}>();

const method = ref<Method>('mobile_money');
const provider = ref<Provider>('mtn');
const phone = ref('');

const cardNumber = ref('');
const cardExpiry = ref('');
const cardCvv = ref('');
const cardholderName = ref('');

const simulating = ref(false);
const errors = ref<Record<string, string>>({});

const providers: { value: Provider; label: string; accent: string; prefixHint: string }[] = [
    { value: 'mtn', label: 'MTN Mobile Money', accent: 'mtn', prefixHint: '096 / 076' },
    { value: 'airtel', label: 'Airtel Money', accent: 'airtel', prefixHint: '097 / 077' },
    { value: 'zamtel', label: 'Zamtel Kwacha', accent: 'zamtel', prefixHint: '095' },
];

const isFormValid = computed(() => {
    if (method.value === 'mobile_money') {
        return /^[0-9+\s]{7,15}$/.test(phone.value);
    }
    const digits = cardNumber.value.replace(/\D/g, '');
    return (
        digits.length >= 12 &&
        digits.length <= 19 &&
        /^(0[1-9]|1[0-2])\/\d{2}$/.test(cardExpiry.value) &&
        /^\d{3,4}$/.test(cardCvv.value) &&
        cardholderName.value.trim().length > 0
    );
});

function formatCardNumber(e: Event) {
    const input = e.target as HTMLInputElement;
    const digits = input.value.replace(/\D/g, '').slice(0, 19);
    cardNumber.value = digits.replace(/(.{4})/g, '$1 ').trim();
}

function formatExpiry(e: Event) {
    const input = e.target as HTMLInputElement;
    let digits = input.value.replace(/\D/g, '').slice(0, 4);
    if (digits.length >= 3) {
        digits = digits.slice(0, 2) + '/' + digits.slice(2);
    }
    cardExpiry.value = digits;
}

function formatCvv(e: Event) {
    const input = e.target as HTMLInputElement;
    cardCvv.value = input.value.replace(/\D/g, '').slice(0, 4);
}

function handlePay() {
    errors.value = {};

    if (!isFormValid.value) {
        errors.value.form = method.value === 'mobile_money'
            ? 'Enter a valid phone number.'
            : 'Check your card number, expiry, and CVV.';
        return;
    }

    const payload: Record<string, string> =
        method.value === 'mobile_money'
            ? { method: 'mobile_money', provider: provider.value, phone: phone.value }
            : {
                method: 'card',
                card_number: cardNumber.value.replace(/\s/g, ''),
                card_expiry: cardExpiry.value,
                card_cvv: cardCvv.value,
                cardholder_name: cardholderName.value,
            };

    // Brief simulated processing delay before the (simulated) charge is submitted —
    // no real gateway is contacted; this is purely so the UI feels like a real checkout.
    simulating.value = true;
    setTimeout(() => {
        simulating.value = false;
        emit('submit', payload);
    }, 900);
}

const isBusy = computed(() => simulating.value || props.processing);
</script>

<template>
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <div v-if="amount !== undefined" class="flex items-center justify-between mb-5 pb-5 border-b border-gray-700">
            <span class="text-gray-400 text-sm">Amount due</span>
            <span class="text-2xl font-bold text-white">K{{ Number(amount).toLocaleString() }}</span>
        </div>

        <!-- Method tabs -->
        <div class="grid grid-cols-2 gap-2 mb-5">
            <button
                type="button"
                @click="method = 'mobile_money'"
                :class="[
                    'flex items-center justify-center gap-2 rounded-lg py-2.5 text-sm font-medium transition-colors border',
                    method === 'mobile_money'
                        ? 'bg-brand-red/10 border-brand-red text-white'
                        : 'bg-gray-700 border-gray-600 text-gray-400 hover:text-white',
                ]"
            >
                📱 Mobile Money
            </button>
            <button
                type="button"
                @click="method = 'card'"
                :class="[
                    'flex items-center justify-center gap-2 rounded-lg py-2.5 text-sm font-medium transition-colors border',
                    method === 'card'
                        ? 'bg-brand-red/10 border-brand-red text-white'
                        : 'bg-gray-700 border-gray-600 text-gray-400 hover:text-white',
                ]"
            >
                💳 Card
            </button>
        </div>

        <!-- Mobile Money -->
        <div v-if="method === 'mobile_money'" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Choose provider</label>
                <div class="grid grid-cols-3 gap-2">
                    <button
                        v-for="p in providers"
                        :key="p.value"
                        type="button"
                        @click="provider = p.value"
                        :class="[
                            'provider-chip rounded-lg py-2.5 px-2 text-xs font-semibold border transition-all',
                            `provider-${p.accent}`,
                            provider === p.value ? 'is-active' : 'bg-gray-700 border-gray-600 text-gray-400',
                        ]"
                    >
                        {{ p.label }}
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Phone number</label>
                <input
                    v-model="phone"
                    type="tel"
                    placeholder="e.g. 096 1234567"
                    class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red"
                />
                <p class="text-gray-500 text-xs mt-1">
                    {{ providers.find(p => p.value === provider)?.label }} numbers typically start with {{ providers.find(p => p.value === provider)?.prefixHint }}
                </p>
            </div>
        </div>

        <!-- Card -->
        <div v-else class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Card number</label>
                <div class="relative">
                    <input
                        :value="cardNumber"
                        @input="formatCardNumber"
                        type="text"
                        inputmode="numeric"
                        placeholder="4242 4242 4242 4242"
                        maxlength="23"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white pl-3 pr-16 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red font-mono tracking-wide"
                    />
                    <span
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold px-1.5 py-0.5 rounded"
                        :class="cardNumber.replace(/\s/g, '').startsWith('4') ? 'bg-blue-500/20 text-blue-400' : 'bg-orange-500/20 text-orange-400'"
                    >
                        {{ cardNumber.replace(/\s/g, '').startsWith('4') ? 'VISA' : 'MASTERCARD' }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Expiry</label>
                    <input
                        :value="cardExpiry"
                        @input="formatExpiry"
                        type="text"
                        inputmode="numeric"
                        placeholder="MM/YY"
                        maxlength="5"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red font-mono"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">CVV</label>
                    <input
                        :value="cardCvv"
                        @input="formatCvv"
                        type="password"
                        inputmode="numeric"
                        placeholder="•••"
                        maxlength="4"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red font-mono"
                    />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Cardholder name</label>
                <input
                    v-model="cardholderName"
                    type="text"
                    placeholder="As printed on the card"
                    class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red"
                />
            </div>
        </div>

        <p v-if="errors.form" class="text-red-400 text-xs mt-4">{{ errors.form }}</p>

        <button
            type="button"
            @click="handlePay"
            :disabled="isBusy"
            class="w-full mt-5 bg-brand-red hover:bg-red-700 disabled:opacity-60 text-white font-semibold py-2.5 rounded-lg transition-colors flex items-center justify-center gap-2"
        >
            <svg v-if="isBusy" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            {{ isBusy ? 'Processing payment…' : submitLabel }}
        </button>

        <p class="text-center text-gray-500 text-xs mt-3">
            Simulated checkout — no real charge is made. Card details are never stored.
        </p>
    </div>
</template>

<style scoped>
.provider-chip.is-active.provider-mtn { background: rgba(255, 204, 0, 0.15); border-color: #ffcc00; color: #ffcc00; }
.provider-chip.is-active.provider-airtel { background: rgba(237, 28, 36, 0.15); border-color: #ed1c24; color: #ff6b6f; }
.provider-chip.is-active.provider-zamtel { background: rgba(0, 166, 81, 0.15); border-color: #00a651; color: #34d399; }
</style>
