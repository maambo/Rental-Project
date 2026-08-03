<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

interface HistoryRow {
    id: number;
    property: string;
    description: string;
    amount: number;
    status: string;
    date: string | null;
    paid_at: string | null;
}

const props = defineProps<{ from: string; to: string; history: HistoryRow[] }>();

const from = ref(props.from);
const to = ref(props.to);

const applyRange = () => {
    router.get(route('tenant.reports.index'), { from: from.value, to: to.value }, { preserveState: true });
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
    <Head title="My Payment History" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Payment History</h2>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">From</label>
                    <input v-model="from" type="date" class="bg-gray-700 border border-gray-600 rounded-lg text-white text-sm px-3 py-2" />
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">To</label>
                    <input v-model="to" type="date" class="bg-gray-700 border border-gray-600 rounded-lg text-white text-sm px-3 py-2" />
                </div>
                <button @click="applyRange" class="bg-brand-red hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg transition-colors">Apply</button>
            </div>

            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">Property</th>
                            <th class="px-4 py-3 font-medium">Description</th>
                            <th class="px-4 py-3 font-medium">Due Date</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="h in history" :key="h.id" class="hover:bg-gray-700/40">
                            <td class="px-4 py-3 text-white">{{ h.property }}</td>
                            <td class="px-4 py-3 text-gray-300">{{ h.description }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ h.date }}</td>
                            <td class="px-4 py-3">
                                <span :class="['px-2 py-0.5 rounded-full text-xs capitalize', statusPill(h.status)]">{{ h.status.replace('_', ' ') }}</span>
                            </td>
                            <td class="px-4 py-3 text-right text-white font-medium">{{ fmt(h.amount) }}</td>
                        </tr>
                        <tr v-if="history.length === 0">
                            <td colspan="5" class="text-center text-gray-500 py-10">No payment history in this range.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
