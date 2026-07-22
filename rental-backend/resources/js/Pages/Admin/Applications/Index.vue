<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import VerificationBadge from '@/Components/VerificationBadge.vue';
import { ClipboardDocumentListIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    applications: Array<any>;
    stats: any;
    currentStatus: string;
}>();

const statusColors: Record<string, string> = {
    pending:      'bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30',
    under_review: 'bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30',
    approved:     'bg-green-500/20 text-green-400 ring-1 ring-green-500/30',
    rejected:     'bg-red-500/20 text-red-400 ring-1 ring-red-500/30',
};
</script>

<template>
    <Head title="Landlord Applications" />

    <AuthenticatedLayout header="Landlord Applications">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="bg-light-bg rounded-xl p-4">
                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                            <p class="text-xs text-gray-500 mb-0.5">Total</p>
                            <p class="text-2xl font-bold text-white">{{ stats.total }}</p>
                        </div>
                    </div>
                    <div class="bg-light-bg rounded-xl p-4">
                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                            <p class="text-xs text-gray-500 mb-0.5">Pending</p>
                            <p class="text-2xl font-bold text-yellow-400">{{ stats.pending }}</p>
                        </div>
                    </div>
                    <div class="bg-light-bg rounded-xl p-4">
                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                            <p class="text-xs text-gray-500 mb-0.5">Under Review</p>
                            <p class="text-2xl font-bold text-blue-400">{{ stats.under_review }}</p>
                        </div>
                    </div>
                    <div class="bg-light-bg rounded-xl p-4">
                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                            <p class="text-xs text-gray-500 mb-0.5">Approved</p>
                            <p class="text-2xl font-bold text-green-400">{{ stats.approved }}</p>
                        </div>
                    </div>
                    <div class="bg-light-bg rounded-xl p-4">
                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                            <p class="text-xs text-gray-500 mb-0.5">Rejected</p>
                            <p class="text-2xl font-bold text-red-400">{{ stats.rejected }}</p>
                        </div>
                    </div>
                </div>

                <!-- Applications Table -->
                <div class="bg-light-bg rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                        <ClipboardDocumentListIcon class="w-4 h-4 text-brand-red" />
                        <h3 class="text-sm font-semibold text-white">Applications</h3>
                        <span class="ml-auto text-xs text-gray-500">{{ applications.length }} records</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700/50">
                            <thead class="bg-dark-bg/60">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Applicant</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Email</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Verification</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Type</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Applied</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/50">
                                <tr v-for="app in applications" :key="app.id"
                                    class="hover:bg-dark-bg/40 transition-colors cursor-pointer">
                                    <td class="px-5 py-4 text-sm text-white">{{ app.user?.name ?? app.user_name }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-400">{{ app.user?.email ?? app.user_email }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-400">
                                        <VerificationBadge :level="app.verification_level" />
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-400 capitalize">
                                        {{ app.landlord_type?.replace('_', ' ') }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <span :class="['px-2.5 py-0.5 rounded-full text-xs font-semibold', statusColors[app.status] ?? 'bg-gray-500/20 text-gray-400 ring-1 ring-gray-500/30']">
                                            {{ app.status.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-400">
                                        {{ new Date(app.created_at).toLocaleDateString() }}
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <Link
                                            :href="route('admin.applications.show', app.id)"
                                            class="text-brand-red hover:text-red-400 font-medium text-sm"
                                        >
                                            View Details →
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!applications.length">
                                    <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-500">
                                        No applications found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
