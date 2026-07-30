<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Pagination from '@/Components/Pagination.vue'; // Assuming you have pagination

const props = defineProps<{
    reports: any;
}>();

const resolveReport = (id: number) => {
    if (confirm('Are you sure you want to resolve this report?')) {
        // Implementation for resolve
    }
};
</script>

<template>
    <Head title="Property Reports" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">
                Property Reports
            </h2>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">Property</th>
                            <th class="px-4 py-3 font-medium">Reasons</th>
                            <th class="px-4 py-3 font-medium">Reporter</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Date</th>
                            <th class="px-4 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="report in reports.data" :key="report.id" class="hover:bg-gray-700/40">
                            <td class="px-4 py-3">
                                <div class="font-medium text-white">{{ report.property?.title || 'Deleted Property' }}</div>
                                <div class="text-gray-500 text-xs">{{ report.property?.code }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="reason in report.report_reasons" :key="reason" class="px-2 py-0.5 rounded-full text-xs bg-red-500/20 text-red-400">
                                        {{ reason }}
                                    </span>
                                </div>
                                <div v-if="report.additional_details" class="text-xs text-gray-500 mt-1 truncate max-w-xs">
                                    "{{ report.additional_details }}"
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-400">
                                {{ report.reporter?.name }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-yellow-500/20 text-yellow-400">
                                    {{ report.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-400">
                                {{ new Date(report.created_at).toLocaleDateString() }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button @click="resolveReport(report.id)" class="text-brand-red hover:text-brand-orange mr-3">Resolve</button>
                                <button class="text-red-400 hover:text-red-300">Ban Landlord</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
