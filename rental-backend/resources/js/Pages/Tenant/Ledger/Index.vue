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
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Financial Ledger
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">Transaction History</h3>
                            <div class="text-right">
                                <span class="text-sm text-gray-500 uppercase">Current Balance</span>
                                <div class="text-2xl font-bold" :class="currentBalance > 0 ? 'text-red-500' : 'text-green-500'">
                                    K{{ currentBalance.toLocaleString() }}
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Debit (Due)</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Credit (Paid)</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="entry in ledger" :key="entry.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ entry.date }}</td>
                                        <td class="px-6 py-4 text-sm">
                                            <div class="font-medium text-gray-900 dark:text-white">{{ entry.description }}</div>
                                            <div class="text-xs text-gray-500">{{ entry.reference }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ entry.property }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-500 font-medium">
                                            {{ entry.debit > 0 ? 'K' + entry.debit.toLocaleString() : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-500 font-medium">
                                            {{ entry.credit > 0 ? 'K' + entry.credit.toLocaleString() : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold">
                                            K{{ entry.balance.toLocaleString() }}
                                        </td>
                                    </tr>
                                    <tr v-if="ledger.length === 0">
                                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                            No transactions found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
