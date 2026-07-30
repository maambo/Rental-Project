<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { UsersIcon, BuildingOfficeIcon, DocumentTextIcon, CurrencyDollarIcon, PresentationChartLineIcon } from '@heroicons/vue/24/outline';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { LineChart, BarChart } from 'echarts/charts';
import { GridComponent, TooltipComponent, TitleComponent } from 'echarts/components';
import VChart from 'vue-echarts';
import { computed } from 'vue';

use([CanvasRenderer, LineChart, BarChart, GridComponent, TooltipComponent, TitleComponent]);

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

const growthChartOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    grid: { left: 40, right: 20, top: 20, bottom: 30 },
    xAxis: { type: 'category', data: props.growthData.map(d => d.month), axisLine: { lineStyle: { color: '#6b7280' } } },
    yAxis: { type: 'value', splitLine: { lineStyle: { color: 'rgba(107,114,128,0.2)' } } },
    series: [{
        name: 'New Users',
        type: 'line',
        data: props.growthData.map(d => d.count),
        smooth: true,
        itemStyle: { color: '#E21608' },
        areaStyle: { color: 'rgba(226, 22, 8, 0.15)' },
    }],
}));

const financialChartOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    grid: { left: 50, right: 20, top: 20, bottom: 30 },
    xAxis: { type: 'category', data: ['Total Revenue', 'Avg Transaction'], axisLine: { lineStyle: { color: '#6b7280' } } },
    yAxis: { type: 'value', splitLine: { lineStyle: { color: 'rgba(107,114,128,0.2)' } } },
    series: [{
        name: 'Financials (K)',
        type: 'bar',
        data: [
            { value: props.stats.financials.total_transactions, itemStyle: { color: '#10b981' } },
            { value: props.stats.financials.avg_transaction, itemStyle: { color: '#E21608' } },
        ],
        barWidth: '50%',
    }],
}));
</script>

<template>
    <Head title="Statistics" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">System Statistics</h2>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Summary Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <UsersIcon class="w-8 h-8 text-brand-red mb-2" />
                    <div class="text-2xl font-bold text-white">{{ stats.users.total }}</div>
                    <div class="text-sm text-gray-400">Total Users</div>
                </div>
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <BuildingOfficeIcon class="w-8 h-8 text-brand-red mb-2" />
                    <div class="text-2xl font-bold text-white">{{ stats.properties.total }}</div>
                    <div class="text-sm text-gray-400">Total Properties</div>
                </div>
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <DocumentTextIcon class="w-8 h-8 text-brand-red mb-2" />
                    <div class="text-2xl font-bold text-white">{{ stats.applications.total }}</div>
                    <div class="text-sm text-gray-400">Applications</div>
                </div>
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <CurrencyDollarIcon class="w-8 h-8 text-brand-success mb-2" />
                    <div class="text-2xl font-bold text-white">K{{ stats.financials.total_transactions.toLocaleString() }}</div>
                    <div class="text-sm text-gray-400">Total Revenue</div>
                </div>
            </div>

            <!-- Engagement Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 flex items-center justify-between">
                    <div>
                        <div class="text-3xl font-bold text-white">{{ stats.properties.total_views.toLocaleString() }}</div>
                        <div class="text-sm text-gray-400">Total Property Views</div>
                    </div>
                    <PresentationChartLineIcon class="w-12 h-12 text-gray-700" />
                </div>
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 flex items-center justify-between">
                    <div>
                        <div class="text-3xl font-bold text-white">{{ stats.properties.total_likes.toLocaleString() }}</div>
                        <div class="text-sm text-gray-400">Total Property Likes</div>
                    </div>
                    <CurrencyDollarIcon class="w-12 h-12 text-gray-700" />
                </div>
            </div>

            <!-- Graphs Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 min-h-[400px]">
                    <div class="flex items-center mb-4">
                        <PresentationChartLineIcon class="w-6 h-6 text-brand-red mr-2" />
                        <h3 class="text-lg font-semibold text-white">User Growth (Last 6 Months)</h3>
                    </div>
                    <div class="h-[300px]">
                        <VChart :option="growthChartOption" autoresize />
                    </div>
                </div>

                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 min-h-[400px]">
                    <div class="flex items-center mb-4">
                        <CurrencyDollarIcon class="w-6 h-6 text-brand-success mr-2" />
                        <h3 class="text-lg font-semibold text-white">Financial Overview</h3>
                    </div>
                    <div class="h-[300px]">
                        <VChart :option="financialChartOption" autoresize />
                    </div>
                </div>
            </div>

            <!-- Detailed Stats Table -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- User Distribution -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">User Distribution</h3>
                    <div class="space-y-3">
                        <div v-for="(count, role) in stats.users" :key="role" class="flex justify-between items-center border-b border-gray-700/50 pb-2">
                            <span class="capitalize text-gray-400">{{ role }}</span>
                            <span class="font-bold text-white">{{ count }}</span>
                        </div>
                    </div>
                </div>

                <!-- Property/Application Breakdown -->
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Processing Pipeline</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center border-b border-gray-700/50 pb-2">
                            <span class="text-gray-400">Pending Approvals</span>
                            <span class="font-bold text-yellow-400">{{ stats.properties.pending }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-gray-700/50 pb-2">
                            <span class="text-gray-400">Approved Properties</span>
                            <span class="font-bold text-green-400">{{ stats.properties.approved }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-gray-700/50 pb-2">
                            <span class="text-gray-400">Rejected Properties</span>
                            <span class="font-bold text-red-400">{{ stats.properties.rejected }}</span>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-700">
                            <div class="flex justify-between items-center border-b border-gray-700/50 pb-2 text-sm">
                                <span class="text-gray-400">Pending Apps</span>
                                <span class="font-bold text-yellow-400">{{ stats.applications.pending }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-gray-700/50 pb-2 text-sm">
                                <span class="text-gray-400">Approved Apps</span>
                                <span class="font-bold text-green-400">{{ stats.applications.approved }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-gray-700/50 pb-2 text-sm">
                                <span class="text-gray-400">Rejected Apps</span>
                                <span class="font-bold text-red-400">{{ stats.applications.rejected }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
                <div class="px-6 py-4 border-b border-gray-700">
                    <h3 class="text-lg font-semibold text-white">Recent Transactions</h3>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">Date</th>
                            <th class="px-4 py-3 font-medium">User</th>
                            <th class="px-4 py-3 font-medium">ID</th>
                            <th class="px-4 py-3 font-medium text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="tx in recentTransactions" :key="tx.id" class="hover:bg-gray-700/40">
                            <td class="px-4 py-3 text-white">{{ new Date(tx.created_at).toLocaleDateString() }}</td>
                            <td class="px-4 py-3 text-white">{{ tx.user_name }}</td>
                            <td class="px-4 py-3 font-mono text-gray-400 text-xs">{{ tx.TransactionID }}</td>
                            <td class="px-4 py-3 font-bold text-green-400 text-right">K{{ tx.Amount }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
