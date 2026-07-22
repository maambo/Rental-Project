<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

interface Application {
    id: number;
    status: string;
    created_at: string;
    user: { id: number; name: string; email: string };
    property: { id: number; title: string };
}

interface Paginated<T> { data: T[] }

defineProps<{ applications: Paginated<Application> }>();

const statusConfig: Record<string, { pill: string; label: string }> = {
    pending:           { pill: 'bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30',  label: 'Pending' },
    under_review:      { pill: 'bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30',        label: 'Under Review' },
    payment_requested: { pill: 'bg-orange-500/20 text-orange-400 ring-1 ring-orange-500/30',  label: 'Payment Requested' },
    completed:         { pill: 'bg-green-500/20 text-green-400 ring-1 ring-green-500/30',     label: 'Completed' },
    rejected:          { pill: 'bg-red-500/20 text-red-400 ring-1 ring-red-500/30',           label: 'Rejected' },
    cancelled:         { pill: 'bg-gray-500/20 text-gray-400 ring-1 ring-gray-500/30',        label: 'Cancelled' },
};

const cfg = (status: string) => statusConfig[status] ?? { pill: 'bg-gray-500/20 text-gray-400 ring-1 ring-gray-500/30', label: status };

const isActive = (status: string) => ['pending', 'under_review', 'payment_requested'].includes(status);

const rejectingId = ref<number | null>(null);
const rejectForm = useForm({ reason: '' });

const openReject = (id: number) => {
    rejectingId.value = id;
    rejectForm.reset();
};

const submitReject = () => {
    if (!rejectingId.value) return;
    rejectForm.post(route('landlord.property-applications.reject', rejectingId.value), {
        onSuccess: () => { rejectingId.value = null; rejectForm.reset(); },
    });
};
</script>

<template>
    <Head title="Applications" />

    <AuthenticatedLayout header="Incoming Applications">
        <div class="p-6">
            <div class="bg-light-bg rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-dark-bg/60">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Applicant</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Property</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Submitted</th>
                                <th class="relative px-5 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            <tr v-if="!applications.data.length">
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">No applications yet.</td>
                            </tr>
                            <tr v-for="app in applications.data" :key="app.id"
                                class="hover:bg-dark-bg/40 transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-white">{{ app.user.name }}</div>
                                    <div class="text-xs text-gray-400">{{ app.user.email }}</div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-300">{{ app.property.title }}</td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                          :class="cfg(app.status).pill">
                                        {{ cfg(app.status).label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-400">
                                    {{ new Date(app.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-3">
                                        <button v-if="isActive(app.status)"
                                                @click="openReject(app.id)"
                                                class="text-xs text-red-400 hover:text-red-300 transition-colors">
                                            Reject
                                        </button>
                                        <Link :href="route('landlord.property-applications.show', app.id)"
                                              class="text-brand-red hover:text-red-400 font-medium transition-colors">
                                            View →
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Inline reject modal -->
        <Teleport to="body">
            <div v-if="rejectingId !== null" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
                <div class="bg-light-bg rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
                    <h3 class="text-white font-semibold text-lg mb-1">Reject Application</h3>
                    <p class="text-gray-400 text-sm mb-4">Provide an optional reason. The applicant will be notified by email.</p>
                    <form @submit.prevent="submitReject">
                        <textarea v-model="rejectForm.reason" rows="3" placeholder="Reason for rejection (optional)"
                                  class="w-full bg-dark-bg border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-red/50 resize-none mb-4" />
                        <div class="flex gap-3">
                            <button type="button" @click="rejectingId = null"
                                    class="flex-1 px-4 py-2 border border-gray-700 rounded-lg text-gray-300 text-sm hover:bg-gray-700/50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" :disabled="rejectForm.processing"
                                    class="flex-1 px-4 py-2 bg-brand-red hover:bg-red-700 disabled:opacity-50 text-white text-sm font-semibold rounded-lg transition-colors">
                                {{ rejectForm.processing ? 'Rejecting…' : 'Confirm Reject' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
