<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

interface PropertyIncome { property_id: number | null; property: string; total_billed: number; total_paid: number; outstanding: number; bill_count: number; }
interface BillDetail { id: number; description: string; amount: number; status: string; date: string | null; paid_at: string | null; }

const props = defineProps<{
    from: string;
    to: string;
    income: PropertyIncome[];
    drillPropertyId: number | null;
    propertyDetail: BillDetail[] | null;
}>();

const from = ref(props.from);
const to = ref(props.to);

const applyRange = () => {
    router.get(route('landlord.reports.index'), { from: from.value, to: to.value }, { preserveState: true });
};

const drillInto = (propertyId: number | null) => {
    if (!propertyId) return;
    router.get(route('landlord.reports.index'), { from: from.value, to: to.value, property: propertyId }, { preserveState: true, preserveScroll: true });
};

const fmt = (n: number) => 'K' + Number(n).toLocaleString();
</script>

<template>
    <Head title="My Income Report" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Income Report</h2>
        </template>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
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
                <a :href="route('landlord.reports.export.csv', { from, to })"
                   class="border border-gray-600 text-gray-300 hover:bg-gray-700 text-sm px-4 py-2 rounded-lg transition-colors">Export CSV</a>
                <a :href="route('landlord.reports.export.pdf', { from, to })"
                   class="border border-gray-600 text-gray-300 hover:bg-gray-700 text-sm px-4 py-2 rounded-lg transition-colors">Export PDF</a>
            </div>

            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">Property</th>
                            <th class="px-4 py-3 font-medium text-right">Billed</th>
                            <th class="px-4 py-3 font-medium text-right">Paid</th>
                            <th class="px-4 py-3 font-medium text-right">Outstanding</th>
                            <th class="px-4 py-3 font-medium text-right">Bills</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="(row, i) in income" :key="i"
                            @click="drillInto(row.property_id)"
                            class="hover:bg-gray-700/40"
                            :class="row.property_id ? 'cursor-pointer' : ''">
                            <td class="px-4 py-3 text-white">{{ row.property }}</td>
                            <td class="px-4 py-3 text-right text-gray-300">{{ fmt(row.total_billed) }}</td>
                            <td class="px-4 py-3 text-right text-green-400 font-medium">{{ fmt(row.total_paid) }}</td>
                            <td class="px-4 py-3 text-right" :class="row.outstanding > 0 ? 'text-yellow-400' : 'text-gray-500'">{{ fmt(row.outstanding) }}</td>
                            <td class="px-4 py-3 text-right text-gray-400">{{ row.bill_count }}</td>
                        </tr>
                        <tr v-if="income.length === 0">
                            <td colspan="5" class="text-center text-gray-500 py-10">No billing activity in this range.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="propertyDetail" class="bg-gray-800 rounded-xl border border-brand-red/40 overflow-x-auto">
                <div class="px-4 py-3 border-b border-gray-700 text-white font-semibold">Bill Detail</div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">Description</th>
                            <th class="px-4 py-3 font-medium">Due Date</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="d in propertyDetail" :key="d.id">
                            <td class="px-4 py-3 text-white">{{ d.description }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ d.date }}</td>
                            <td class="px-4 py-3 text-gray-300 capitalize">{{ d.status.replace('_', ' ') }}</td>
                            <td class="px-4 py-3 text-right text-white font-medium">{{ fmt(d.amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="text-gray-500 text-xs">Click a property row above to drill into its individual bills.</p>
        </div>
    </AuthenticatedLayout>
</template>
