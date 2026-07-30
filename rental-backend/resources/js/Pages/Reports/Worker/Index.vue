<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

interface ServiceIncome { service: string; jobs_completed: number; gross_earnings: number; platform_fees: number; net_earnings: number; }
interface BookingDetail { id: number; client: string; agreed_price: number; platform_fee: number; worker_net: number; completed_at: string | null; }

const props = defineProps<{
    from: string;
    to: string;
    income: ServiceIncome[];
    drillService: string | null;
    serviceDetail: BookingDetail[] | null;
}>();

const from = ref(props.from);
const to = ref(props.to);

const applyRange = () => {
    router.get(route('worker.reports.index'), { from: from.value, to: to.value }, { preserveState: true });
};

const drillInto = (service: string) => {
    router.get(route('worker.reports.index'), { from: from.value, to: to.value, service }, { preserveState: true, preserveScroll: true });
};

const fmt = (n: number) => 'K' + Number(n).toLocaleString();
</script>

<template>
    <Head title="My Earnings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Earnings Report</h2>
        </template>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
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
                            <th class="px-4 py-3 font-medium">Service</th>
                            <th class="px-4 py-3 font-medium text-right">Jobs</th>
                            <th class="px-4 py-3 font-medium text-right">Gross</th>
                            <th class="px-4 py-3 font-medium text-right">Platform Fees</th>
                            <th class="px-4 py-3 font-medium text-right">Net Earnings</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="row in income" :key="row.service"
                            @click="drillInto(row.service)"
                            class="hover:bg-gray-700/40 cursor-pointer"
                            :class="{ 'bg-brand-red/5': drillService === row.service }">
                            <td class="px-4 py-3 text-white">{{ row.service }}</td>
                            <td class="px-4 py-3 text-right text-gray-400">{{ row.jobs_completed }}</td>
                            <td class="px-4 py-3 text-right text-gray-300">{{ fmt(row.gross_earnings) }}</td>
                            <td class="px-4 py-3 text-right text-yellow-400">{{ fmt(row.platform_fees) }}</td>
                            <td class="px-4 py-3 text-right text-green-400 font-medium">{{ fmt(row.net_earnings) }}</td>
                        </tr>
                        <tr v-if="income.length === 0">
                            <td colspan="5" class="text-center text-gray-500 py-10">No completed jobs in this range.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="serviceDetail" class="bg-gray-800 rounded-xl border border-brand-red/40 overflow-x-auto">
                <div class="px-4 py-3 border-b border-gray-700 text-white font-semibold">Bookings — {{ drillService }}</div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">Client</th>
                            <th class="px-4 py-3 font-medium">Completed</th>
                            <th class="px-4 py-3 font-medium text-right">Agreed Price</th>
                            <th class="px-4 py-3 font-medium text-right">Fee</th>
                            <th class="px-4 py-3 font-medium text-right">Net</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="b in serviceDetail" :key="b.id">
                            <td class="px-4 py-3 text-white">{{ b.client }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ b.completed_at }}</td>
                            <td class="px-4 py-3 text-right text-gray-300">{{ fmt(b.agreed_price) }}</td>
                            <td class="px-4 py-3 text-right text-yellow-400">{{ fmt(b.platform_fee) }}</td>
                            <td class="px-4 py-3 text-right text-green-400 font-medium">{{ fmt(b.worker_net) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
