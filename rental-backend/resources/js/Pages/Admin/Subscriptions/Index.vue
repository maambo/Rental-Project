<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { CreditCardIcon, PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface Tier {
    id: number; name: string; tier_type: string;
    display_name: string; price_display: string; property_limit: number;
}
interface Subscription {
    id: number; status: string; billing_cycle: string;
    starts_at: string; ends_at: string | null; cancelled_at: string | null;
    user: { id: number; name: string; email: string };
    tier: Tier;
}

const props = defineProps<{
    subscriptions: { data: Subscription[]; links: any[] };
    tiers: Tier[];
    stats: { active: number; cancelled: number; expired: number };
    tierType: string;
}>();

const showCreate = ref(false);

const form = useForm({
    user_id: '' as string | number,
    verification_tier_id: '' as string | number,
    billing_cycle: 'free',
    ends_at: '',
    notes: '',
});

const submit = () => {
    form.post(route('admin.subscriptions.store'), {
        onSuccess: () => { showCreate.value = false; form.reset(); },
    });
};

const cancel = (id: number) => {
    if (confirm('Cancel this subscription?')) {
        router.delete(route('admin.subscriptions.destroy', id), { preserveScroll: true });
    }
};

const statusClass = (status: string) => ({
    active:    'bg-green-500/20 text-green-400 ring-1 ring-green-500/30',
    cancelled: 'bg-red-500/20 text-red-400 ring-1 ring-red-500/30',
    expired:   'bg-gray-500/20 text-gray-400 ring-1 ring-gray-500/30',
    trial:     'bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30',
}[status] ?? '');

const cycleBadge = (cycle: string) => ({
    free:    'bg-gray-500/20 text-gray-400',
    monthly: 'bg-blue-500/20 text-blue-400',
    annual:  'bg-purple-500/20 text-purple-400',
}[cycle] ?? '');
</script>

<template>
    <Head title="Subscriptions" />

    <AuthenticatedLayout header="Subscriptions">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-light-bg rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-green-400">{{ stats.active }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Active</p>
                    </div>
                    <div class="bg-light-bg rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-red-400">{{ stats.cancelled }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Cancelled</p>
                    </div>
                    <div class="bg-light-bg rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-gray-400">{{ stats.expired }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Expired</p>
                    </div>
                </div>

                <!-- Header row -->
                <div class="flex items-center justify-between">
                    <!-- Tier type filter -->
                    <div class="flex gap-2">
                        <Link v-for="t in ['landlord', 'tenant', 'worker']" :key="t"
                            :href="route('admin.subscriptions.index', { tier_type: t })"
                            :class="['px-3 py-1.5 rounded-lg text-xs font-medium capitalize transition',
                                tierType === t
                                    ? 'bg-brand-red text-white'
                                    : 'bg-light-bg text-gray-400 hover:text-white']">
                            {{ t }}
                        </Link>
                    </div>

                    <button @click="showCreate = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-brand-red text-white rounded-lg hover:bg-red-700 text-sm font-medium transition">
                        <PlusIcon class="w-4 h-4" /> Grant Subscription
                    </button>
                </div>

                <!-- Table -->
                <div class="bg-light-bg rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                        <CreditCardIcon class="w-4 h-4 text-brand-red" />
                        <h3 class="text-sm font-semibold text-white capitalize">{{ tierType }} Subscriptions</h3>
                    </div>

                    <table class="min-w-full divide-y divide-gray-700/50">
                        <thead class="bg-dark-bg/60">
                            <tr>
                                <th class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-5 py-3 text-left">User</th>
                                <th class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-5 py-3 text-left">Tier</th>
                                <th class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-5 py-3 text-left">Status</th>
                                <th class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-5 py-3 text-left">Cycle</th>
                                <th class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-5 py-3 text-left">Expires</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            <tr v-for="sub in subscriptions.data" :key="sub.id" class="hover:bg-dark-bg/40 transition-colors">
                                <td class="px-5 py-3">
                                    <p class="text-sm font-medium text-white">{{ sub.user.name }}</p>
                                    <p class="text-xs text-gray-500">{{ sub.user.email }}</p>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="text-sm text-white">{{ sub.tier.display_name }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span :class="['px-2 py-0.5 rounded-full text-xs font-semibold capitalize', statusClass(sub.status)]">
                                        {{ sub.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <span :class="['px-2 py-0.5 rounded-full text-xs font-semibold capitalize', cycleBadge(sub.billing_cycle)]">
                                        {{ sub.billing_cycle }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-400">
                                    {{ sub.ends_at ? new Date(sub.ends_at).toLocaleDateString() : '∞ No expiry' }}
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <button v-if="sub.status === 'active'" @click="cancel(sub.id)"
                                        class="p-1.5 bg-red-600/20 hover:bg-red-600/40 text-red-400 rounded-md transition">
                                        <XMarkIcon class="w-3.5 h-3.5" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="subscriptions.data.length === 0">
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">No subscriptions found.</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="subscriptions.links.length > 3" class="px-5 py-3 border-t border-gray-700/60 flex gap-1">
                        <Link v-for="link in subscriptions.links" :key="link.label"
                            :href="link.url ?? '#'"
                            :class="['px-2.5 py-1 rounded text-xs', link.active ? 'bg-brand-red text-white' : 'text-gray-400 hover:text-white']"
                            v-html="link.label" />
                    </div>
                </div>

            </div>
        </div>

        <!-- Grant Subscription Modal -->
        <Teleport to="body">
            <div v-if="showCreate" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 px-4">
                <div class="bg-light-bg rounded-xl w-full max-w-md p-6">
                    <h2 class="text-base font-semibold text-white mb-4">Grant Subscription</h2>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">User ID</label>
                            <input v-model="form.user_id" type="number" placeholder="Enter user ID"
                                class="w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand-red" />
                            <p v-if="form.errors.user_id" class="text-xs text-red-400 mt-1">{{ form.errors.user_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Tier</label>
                            <select v-model="form.verification_tier_id"
                                class="w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand-red">
                                <option value="" disabled>Select tier</option>
                                <option v-for="tier in tiers" :key="tier.id" :value="tier.id">
                                    {{ tier.display_name }} ({{ tier.price_display }})
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Billing Cycle</label>
                            <select v-model="form.billing_cycle"
                                class="w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand-red">
                                <option value="free">Free</option>
                                <option value="monthly">Monthly</option>
                                <option value="annual">Annual</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Expiry Date <span class="text-gray-500">(leave blank = no expiry)</span></label>
                            <input v-model="form.ends_at" type="date"
                                class="w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand-red" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Notes</label>
                            <textarea v-model="form.notes" rows="2" placeholder="Optional admin notes"
                                class="w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand-red resize-none"></textarea>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="showCreate = false"
                                class="flex-1 px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 text-sm font-medium transition">
                                Cancel
                            </button>
                            <button type="submit" :disabled="form.processing"
                                class="flex-1 px-4 py-2 bg-brand-red text-white rounded-lg hover:bg-red-700 text-sm font-medium transition disabled:opacity-60">
                                Grant
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
