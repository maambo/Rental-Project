<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface LedgerEntry {
    id: number;
    date: string;
    description: string;
    reference: string;
    debit: number;
    credit: number;
    balance: number;
    status: string;
    property: string;
}

const props = defineProps<{
    ledger: LedgerEntry[];
    currentBalance: number;
}>();
</script>

<template>
    <Head title="My Ledger" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">
                Financial Ledger
            </h2>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-white">Transaction History</h3>
                        <div class="text-right">
                            <span class="text-sm text-gray-500 uppercase">Current Balance</span>
                            <div class="text-2xl font-bold" :class="currentBalance > 0 ? 'text-red-400' : 'text-green-400'">
                                K{{ currentBalance.toLocaleString() }}
                            </div>
                        </div>
                    </div>

                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-700 text-gray-400 text-left">
                                <th class="px-4 py-3 font-medium">Date</th>
                                <th class="px-4 py-3 font-medium">Description</th>
                                <th class="px-4 py-3 font-medium">Property</th>
                                <th class="px-4 py-3 font-medium text-right">Debit (Due)</th>
                                <th class="px-4 py-3 font-medium text-right">Credit (Paid)</th>
                                <th class="px-4 py-3 font-medium text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            <tr v-for="entry in ledger" :key="entry.id" class="hover:bg-gray-700/40">
                                <td class="px-4 py-3 text-gray-300">{{ entry.date }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-white">{{ entry.description }}</div>
                                    <div class="text-xs text-gray-500">{{ entry.reference }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-400">{{ entry.property }}</td>
                                <td class="px-4 py-3 text-right text-red-400 font-medium">
                                    {{ entry.debit > 0 ? 'K' + entry.debit.toLocaleString() : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right text-green-400 font-medium">
                                    {{ entry.credit > 0 ? 'K' + entry.credit.toLocaleString() : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right text-white font-bold">
                                    K{{ entry.balance.toLocaleString() }}
                                </td>
                            </tr>
                            <tr v-if="ledger.length === 0">
                                <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                                    No transactions found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
