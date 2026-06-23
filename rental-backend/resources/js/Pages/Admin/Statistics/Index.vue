<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { UsersIcon, BuildingOfficeIcon, DocumentTextIcon, CurrencyDollarIcon, PresentationChartLineIcon } from '@heroicons/vue/24/outline';
import { Line, Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, BarElement } from 'chart.js';
import { computed } from 'vue';

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, BarElement);

interface UserStats {
    total: number;
    admins: number;
    landlords: number;
    tenants: number;
}

interface PropertyStats {
    total: number;
    approved: number;
    pending: number;
    rejected: number;
    total_views: number;
    total_likes: number;
}

interface ApplicationStats {
    total: number;
    pending: number;
    approved: number;
    rejected: number;
}

interface FinancialStats {
    total_transactions: number;
    avg_transaction: number;
}

interface Stats {
    users: UserStats;
    properties: PropertyStats;
    applications: ApplicationStats;
    financials: FinancialStats;
}

interface GrowthPoint {
    month: string;
    count: number;
}

interface Transaction {
    id: number;
    TransactionID: string;
    user_name: string;
    Amount: number;
    created_at: string;
}

const props = defineProps<{
    stats: Stats;
    growthData: Array<GrowthPoint>;
    recentTransactions: Array<Transaction>;
}>();

const growthChartData = computed(() => ({
    labels: props.growthData.map(d => d.month),
    datasets: [{
        label: 'New Users',
        data: props.growthData.map(d => d.count),
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        fill: true,
        tension: 0.4
    }]
}));

const financialChartData = computed(() => ({
    labels: ['Total Revenue', 'Avg Transaction'],
    datasets: [{
        label: 'Financials (K)',
        data: [props.stats.financials.total_transactions, props.stats.financials.avg_transaction],
        backgroundColor: ['#10b981', '#3b82f6'],
    }]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
};
</script>

<template>
    <Head title="Statistics" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">System Statistics</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Summary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <UsersIcon class="w-8 h-8 text-blue-600 mb-2" />
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.users.total }}</div>
                        <div class="text-sm text-gray-500">Total Users</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <BuildingOfficeIcon class="w-8 h-8 text-green-600 mb-2" />
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.properties.total }}</div>
                        <div class="text-sm text-gray-500">Total Properties</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <DocumentTextIcon class="w-8 h-8 text-purple-600 mb-2" />
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.applications.total }}</div>
                        <div class="text-sm text-gray-500">Applications</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <CurrencyDollarIcon class="w-8 h-8 text-emerald-600 mb-2" />
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">K{{ stats.financials.total_transactions.toLocaleString() }}</div>
                        <div class="text-sm text-gray-500">Total Revenue</div>
                    </div>
                </div>

                <!-- Engagement Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.properties.total_views.toLocaleString() }}</div>
                            <div class="text-sm text-gray-500">Total Property Views</div>
                        </div>
                        <PresentationChartLineIcon class="w-12 h-12 text-blue-100 dark:text-blue-900" />
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.properties.total_likes.toLocaleString() }}</div>
                            <div class="text-sm text-gray-500">Total Property Likes</div>
                        </div>
                        <CurrencyDollarIcon class="w-12 h-12 text-emerald-100 dark:text-emerald-900" />
                    </div>
                </div>

                <!-- Graphs Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow min-h-[400px]">
                        <div class="flex items-center mb-4">
                            <PresentationChartLineIcon class="w-6 h-6 text-blue-600 mr-2" />
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">User Growth (Last 6 Months)</h3>
                        </div>
                        <div class="h-[300px]">
                            <Line :data="growthChartData" :options="chartOptions" />
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow min-h-[400px]">
                        <div class="flex items-center mb-4">
                            <CurrencyDollarIcon class="w-6 h-6 text-emerald-600 mr-2" />
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Financial Overview</h3>
                        </div>
                        <div class="h-[300px]">
                            <Bar :data="financialChartData" :options="chartOptions" />
                        </div>
                    </div>
                </div>

                <!-- Detailed Stats Table -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- User Distribution -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">User Distribution</h3>
                        <div class="space-y-4">
                            <div v-for="(count, role) in stats.users" :key="role" class="flex justify-between items-center border-b dark:border-gray-700 pb-2">
                                <span class="capitalize text-gray-600 dark:text-gray-400">{{ role }}</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ count }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Property/Application Breakdown -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Processing Pipeline</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center border-b dark:border-gray-700 pb-2">
                                <span class="text-gray-600 dark:text-gray-400">Pending Approvals</span>
                                <span class="font-bold text-yellow-600">{{ stats.properties.pending }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b dark:border-gray-700 pb-2">
                                <span class="text-gray-600 dark:text-gray-400">Approved Properties</span>
                                <span class="font-bold text-green-600">{{ stats.properties.approved }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b dark:border-gray-700 pb-2">
                                <span class="text-gray-600 dark:text-gray-400">Rejected Properties</span>
                                <span class="font-bold text-red-600">{{ stats.properties.rejected }}</span>
                            </div>
                            <div class="mt-4 pt-4 border-t dark:border-gray-700">
                                <div class="flex justify-between items-center border-b dark:border-gray-700 pb-2 text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Pending Apps</span>
                                    <span class="font-bold text-yellow-600">{{ stats.applications.pending }}</span>
                                </div>
                                <div class="flex justify-between items-center border-b dark:border-gray-700 pb-2 text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Approved Apps</span>
                                    <span class="font-bold text-green-600">{{ stats.applications.approved }}</span>
                                </div>
                                <div class="flex justify-between items-center border-b dark:border-gray-700 pb-2 text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Rejected Apps</span>
                                    <span class="font-bold text-red-600">{{ stats.applications.rejected }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recent Transactions</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-500 uppercase text-xs">
                                    <th class="px-4 py-2">Date</th>
                                    <th class="px-4 py-2">User</th>
                                    <th class="px-4 py-2">ID</th>
                                    <th class="px-4 py-2 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="tx in recentTransactions" :key="tx.id" class="border-t dark:border-gray-700">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ new Date(tx.created_at).toLocaleDateString() }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ tx.user_name }}</td>
                                    <td class="px-4 py-3 text-sm font-mono text-gray-500">{{ tx.TransactionID }}</td>
                                    <td class="px-4 py-3 text-sm font-bold text-emerald-600 text-right">K{{ tx.Amount }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
