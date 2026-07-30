<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Billing {
    id: number;
    Amount: number;
    Date: string;
    Description: string;
    status: string;
    proof_of_payment: string | null;
    paid_at: string | null;
    user: { name: string; email: string } | null;
    lease_agreement: { property: { title: string } | null } | null;
}

defineProps<{
    billings: { data: Billing[]; links: any[]; meta: any };
}>();

const verify = (id: number) => {
    if (!confirm('Mark this bill as verified and paid?')) return;
    router.post(route('landlord.billing.verify', id), {}, { preserveScroll: true });
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
    <Head title="Billing" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Tenant Billing</h2>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">Tenant</th>
                            <th class="px-4 py-3 font-medium">Property</th>
                            <th class="px-4 py-3 font-medium">Description</th>
                            <th class="px-4 py-3 font-medium">Date</th>
                            <th class="px-4 py-3 font-medium text-right">Amount</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-if="billings.data.length === 0">
                            <td colspan="7" class="text-center text-gray-500 py-10">No billing records yet.</td>
                        </tr>
                        <tr v-for="b in billings.data" :key="b.id" class="hover:bg-gray-700/40">
                            <td class="px-4 py-3">
                                <p class="text-white font-medium">{{ b.user?.name ?? '—' }}</p>
                                <p class="text-gray-400 text-xs">{{ b.user?.email }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-300">{{ b.lease_agreement?.property?.title ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-300">{{ b.Description }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ new Date(b.Date).toLocaleDateString() }}</td>
                            <td class="px-4 py-3 text-right text-white font-medium">{{ fmt(b.Amount) }}</td>
                            <td class="px-4 py-3">
                                <span :class="['px-2 py-0.5 rounded-full text-xs capitalize', statusPill(b.status)]">{{ b.status.replace('_', ' ') }}</span>
                                <a v-if="b.proof_of_payment" :href="'/storage/' + b.proof_of_payment" target="_blank"
                                   class="block text-xs text-blue-400 hover:underline mt-1">View proof</a>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button v-if="b.status !== 'paid'" @click="verify(b.id)"
                                    class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded transition-colors">
                                    Verify payment
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
