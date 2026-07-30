<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface AuditLog {
    id: number;
    event: string;
    user_id: number | null;
    user?: { name: string; email: string };
    auditable_type: string;
    auditable_id: number;
    ip_address: string | null;
    created_at: string;
}

defineProps<{
    logs: { data: AuditLog[]; total?: number };
    filters: { event?: string; user_id?: string };
}>();
</script>

<template>
    <Head title="Audit Logs" />

    <AuthenticatedLayout header="Audit Logs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">Event</th>
                            <th class="px-4 py-3 font-medium">User</th>
                            <th class="px-4 py-3 font-medium">Target</th>
                            <th class="px-4 py-3 font-medium">IP</th>
                            <th class="px-4 py-3 font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-if="!logs.data.length">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                No audit logs yet.
                            </td>
                        </tr>
                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-700/40">
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-brand-info/20 text-brand-info capitalize">
                                    {{ log.event }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-300">
                                {{ log.user?.name ?? 'System' }}
                            </td>
                            <td class="px-4 py-3 text-gray-400">
                                {{ log.auditable_type?.split('\\').pop() }} #{{ log.auditable_id }}
                            </td>
                            <td class="px-4 py-3 text-gray-400">
                                {{ log.ip_address ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-400">
                                {{ new Date(log.created_at).toLocaleString() }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
