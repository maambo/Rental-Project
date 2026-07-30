<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Tier {
    id: number;
    name: string;
    tier_type: string;
    display_name: string;
    price_display: string;
    price_amount: number;
    property_limit: number;
    features: string[] | null;
}

defineProps<{ tiers: Tier[] }>();
</script>

<template>
    <Head title="Subscription Plans" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Subscription Plans</h2>
        </template>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div v-for="tier in tiers" :key="tier.id"
                    class="bg-gray-800 rounded-xl border border-gray-700 p-6 flex flex-col">
                    <h3 class="text-white font-bold text-lg">{{ tier.display_name }}</h3>
                    <p class="text-brand-red font-bold text-2xl mt-2">{{ tier.price_display }}</p>
                    <p class="text-gray-400 text-xs mt-1">
                        {{ tier.property_limit === -1 ? 'Unlimited properties' : `${tier.property_limit} propert${tier.property_limit === 1 ? 'y' : 'ies'}` }}
                    </p>

                    <ul v-if="tier.features?.length" class="mt-4 space-y-2 flex-1">
                        <li v-for="(f, i) in tier.features" :key="i" class="text-gray-300 text-sm flex items-start gap-2">
                            <span class="text-green-400 flex-shrink-0">✓</span>{{ f }}
                        </li>
                    </ul>
                    <div v-else class="flex-1"></div>

                    <Link :href="route('payments.subscribe.create', tier.id)"
                        class="mt-5 block text-center bg-brand-red hover:bg-red-700 text-white font-semibold py-2.5 rounded-lg transition-colors">
                        {{ tier.price_amount === 0 ? 'Select' : 'Subscribe' }}
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
