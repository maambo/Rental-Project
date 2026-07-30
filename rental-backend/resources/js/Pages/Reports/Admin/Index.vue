<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { BarChart } from 'echarts/charts';
import { GridComponent, TooltipComponent } from 'echarts/components';
import VChart from 'vue-echarts';
import { computed, ref } from 'vue';

use([CanvasRenderer, BarChart, GridComponent, TooltipComponent]);

interface RevenuePoint { period: string; total: number; count: number; }
interface SubscriptionRevenue { tier_type: string; count: number; total: number; }
interface OutstandingBalance { id: number; tenant: string; property: string; amount: number; due_date: string | null; days_overdue: number; }
interface TransactionDetail { id: number; transaction_id: string; user: string; amount: number; type: string; date: string; }

const props = defineProps<{
    from: string;
    to: string;
    revenueByPeriod: RevenuePoint[];
    subscriptionRevenue: SubscriptionRevenue[];
    outstandingBalances: OutstandingBalance[];
    totalRevenue: number;
    averageTransaction: number;
    drillPeriod: string | null;
    periodDetail: TransactionDetail[] | null;
}>();

const from = ref(props.from);
const to = ref(props.to);

const applyRange = () => {
    router.get(route('admin.financial-reports.index'), { from: from.value, to: to.value }, { preserveState: true });
};

const drillInto = (period: string) => {
    router.get(route('admin.financial-reports.index'), { from: from.value, to: to.value, period }, { preserveState: true, preserveScroll: true });
};

const chartOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    grid: { left: 50, right: 20, top: 20, bottom: 30 },
    xAxis: { type: 'category', data: props.revenueByPeriod.map(r => r.period), axisLine: { lineStyle: { color: '#6b7280' } } },
    yAxis: { type: 'value', splitLine: { lineStyle: { color: 'rgba(107,114,128,0.2)' } } },
    series: [{
        name: 'Revenue (K)',
        type: 'bar',
        data: props.revenueByPeriod.map(r => r.total),
        itemStyle: { color: '#ef4444' },
        barWidth: '50%',
    }],
}));

const onChartClick = (params: { name: string }) => drillInto(params.name);

const fmt = (n: number) => 'K' + Number(n).toLocaleString();
</script>

<template>
    <Head title="Financial Reports" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Financial Reports</h2>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Filters + export -->
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
                <div class="flex-1"></div>
                <a :href="route('admin.financial-reports.export.csv', { from, to })"
                   class="border border-gray-600 text-gray-300 hover:bg-gray-700 text-sm px-4 py-2 rounded-lg transition-colors">Export CSV</a>
                <a :href="route('admin.financial-reports.export.pdf', { from, to })"
                   class="border border-gray-600 text-gray-300 hover:bg-gray-700 text-sm px-4 py-2 rounded-lg transition-colors">Export PDF</a>
            </div>

            <!-- Summary tiles -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
                    <div class="text-gray-400 text-sm">Total Revenue</div>
                    <div class="text-2xl font-bold text-white mt-1">{{ fmt(totalRevenue) }}</div>
                </div>
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
                    <div class="text-gray-400 text-sm">Average Transaction</div>
                    <div class="text-2xl font-bold text-white mt-1">{{ fmt(averageTransaction) }}</div>
                </div>
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
                    <div class="text-gray-400 text-sm">Outstanding Balances</div>
                    <div class="text-2xl font-bold text-yellow-400 mt-1">{{ fmt(outstandingBalances.reduce((s, o) => s + o.amount, 0)) }}</div>
                </div>
            </div>

            <!-- Revenue chart -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-white font-semibold mb-4">Revenue by Period</h3>
                <div class="h-64"><VChart :option="chartOption" autoresize @click="onChartClick" /></div>
            </div>

            <!-- Revenue table with drill-through -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">Period</th>
                            <th class="px-4 py-3 font-medium text-right">Revenue</th>
                            <th class="px-4 py-3 font-medium text-right">Transactions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="r in revenueByPeriod" :key="r.period"
                            @click="drillInto(r.period)"
                            class="hover:bg-gray-700/40 cursor-pointer"
                            :class="{ 'bg-brand-red/5': drillPeriod === r.period }">
                            <td class="px-4 py-3 text-white">{{ r.period }}</td>
                            <td class="px-4 py-3 text-right text-white font-medium">{{ fmt(r.total) }}</td>
                            <td class="px-4 py-3 text-right text-gray-400">{{ r.count }}</td>
                        </tr>
                        <tr v-if="revenueByPeriod.length === 0">
                            <td colspan="3" class="text-center text-gray-500 py-10">No revenue in this range.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Drill-through detail -->
            <div v-if="drillPeriod" class="bg-gray-800 rounded-xl border border-brand-red/40 overflow-x-auto">
                <div class="px-4 py-3 border-b border-gray-700 text-white font-semibold">Transactions in {{ drillPeriod }}</div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">Date</th>
                            <th class="px-4 py-3 font-medium">User</th>
                            <th class="px-4 py-3 font-medium">Transaction ID</th>
                            <th class="px-4 py-3 font-medium">Type</th>
                            <th class="px-4 py-3 font-medium text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="t in periodDetail" :key="t.id">
                            <td class="px-4 py-3 text-gray-400">{{ new Date(t.date).toLocaleString() }}</td>
                            <td class="px-4 py-3 text-white">{{ t.user }}</td>
                            <td class="px-4 py-3 font-mono text-gray-400 text-xs">{{ t.transaction_id }}</td>
                            <td class="px-4 py-3 text-gray-300">{{ t.type }}</td>
                            <td class="px-4 py-3 text-right text-white font-medium">{{ fmt(t.amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Subscription revenue -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-white font-semibold mb-4">New Subscription Revenue</h3>
                <div class="space-y-2">
                    <div v-for="s in subscriptionRevenue" :key="s.tier_type" class="flex justify-between text-sm border-b border-gray-700/50 pb-2">
                        <span class="text-gray-300 capitalize">{{ s.tier_type }} ({{ s.count }})</span>
                        <span class="text-white font-medium">{{ fmt(s.total) }}</span>
                    </div>
                    <p v-if="subscriptionRevenue.length === 0" class="text-gray-500 text-sm">No new subscriptions in this range.</p>
                </div>
            </div>

            <!-- Outstanding balances -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
                <div class="px-4 py-3 border-b border-gray-700 text-white font-semibold">Outstanding Balances (Platform-wide)</div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">Tenant</th>
                            <th class="px-4 py-3 font-medium">Property</th>
                            <th class="px-4 py-3 font-medium">Due Date</th>
                            <th class="px-4 py-3 font-medium text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="o in outstandingBalances" :key="o.id">
                            <td class="px-4 py-3 text-white">{{ o.tenant }}</td>
                            <td class="px-4 py-3 text-gray-300">{{ o.property }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ o.due_date }}</td>
                            <td class="px-4 py-3 text-right font-medium" :class="o.days_overdue > 0 ? 'text-red-400' : 'text-white'">{{ fmt(o.amount) }}</td>
                        </tr>
                        <tr v-if="outstandingBalances.length === 0">
                            <td colspan="4" class="text-center text-gray-500 py-10">No outstanding balances.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
