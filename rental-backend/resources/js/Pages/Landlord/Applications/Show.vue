<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

interface Property {
    id: number; title: string; listing_type: string; property_type: string;
    price: number; province?: { name: string }; district?: { name: string }; town?: { name: string };
}
interface Application {
    id: number; status: string; message: string | null; preferred_move_in: string | null;
    additional_comments: string | null; adults: number | null; children: number | null;
    has_pets: boolean | null; pet_details: string | null; intended_use: string | null;
    business_name: string | null; created_at: string; review_started_at: string | null;
    payment_requested_at: string | null; payment_deadline: string | null; completed_at: string | null; rejection_reason: string | null;
    applicant_terms: string | null; landlord_agreed_terms_at: string | null;
    user: { id: number; name: string; email: string; phone?: string };
    property: Property;
}

const props = defineProps<{ application: Application }>();

const rejectForm = useForm({ reason: '' });
const showRejectModal = ref(false);

const startReview = useForm({});
const requestPayment = useForm({ deadline_hours: 48, landlord_agreed_terms: false as boolean | undefined });

const deadlineOptions = [
    { value: 12,  label: '12 hours' },
    { value: 24,  label: '24 hours' },
    { value: 48,  label: '48 hours' },
    { value: 72,  label: '3 days' },
    { value: 120, label: '5 days' },
    { value: 168, label: '7 days' },
];

const statusConfig: Record<string, { pill: string; label: string }> = {
    pending:           { pill: 'bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30',  label: 'Pending' },
    under_review:      { pill: 'bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30',        label: 'Under Review' },
    payment_requested: { pill: 'bg-orange-500/20 text-orange-400 ring-1 ring-orange-500/30',  label: 'Payment Requested' },
    completed:         { pill: 'bg-green-500/20 text-green-400 ring-1 ring-green-500/30',     label: 'Completed' },
    rejected:          { pill: 'bg-red-500/20 text-red-400 ring-1 ring-red-500/30',           label: 'Rejected' },
};
const cfg = (s: string) => statusConfig[s] ?? { pill: 'bg-gray-500/20 text-gray-400 ring-1 ring-gray-500/30', label: s };

const fmtDate = (d: string | null) => d ? new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';
const fmtPrice = (n: number) => 'K' + n.toLocaleString();

const isActive = ['pending', 'under_review', 'payment_requested'].includes(props.application.status);
</script>

<template>
    <Head :title="`Application – ${application.user.name}`" />

    <AuthenticatedLayout :header="`Application from ${application.user.name}`"
                         :back-url="route('landlord.property-applications.index')">
        <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left: application details -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Applicant info -->
                <div class="bg-light-bg rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Applicant</h3>
                    <div class="flex items-start gap-4">
                        <div class="h-12 w-12 rounded-full bg-brand-red/20 flex items-center justify-center text-brand-red font-bold text-lg flex-shrink-0">
                            {{ application.user.name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <div class="text-white font-semibold">{{ application.user.name }}</div>
                            <div class="text-gray-400 text-sm">{{ application.user.email }}</div>
                            <div v-if="application.user.phone" class="text-gray-400 text-sm">{{ application.user.phone }}</div>
                        </div>
                    </div>
                </div>

                <!-- Property info -->
                <div class="bg-light-bg rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Property</h3>
                    <div class="flex justify-between items-start">
                        <div>
                            <Link :href="route('properties.show', application.property.id)"
                                  class="text-white font-semibold hover:text-brand-red transition-colors">
                                {{ application.property.title }}
                            </Link>
                            <div class="text-gray-400 text-sm mt-1">
                                {{ [application.property.town?.name, application.property.district?.name, application.property.province?.name].filter(Boolean).join(', ') }}
                            </div>
                            <div class="flex gap-2 mt-2">
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-700 text-gray-300 capitalize">{{ application.property.property_type }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-700 text-gray-300 capitalize">{{ application.property.listing_type }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-white font-bold text-lg">{{ fmtPrice(application.property.price) }}</div>
                            <div class="text-gray-400 text-xs">{{ application.property.listing_type === 'rent' ? '/month' : 'asking price' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Application details -->
                <div class="bg-light-bg rounded-xl p-5 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Application Details</h3>

                    <div v-if="application.message" class="bg-dark-bg/60 rounded-lg p-4">
                        <div class="text-xs text-gray-500 mb-1">Message</div>
                        <p class="text-gray-300 text-sm whitespace-pre-line">{{ application.message }}</p>
                    </div>

                    <div v-if="application.preferred_move_in" class="grid grid-cols-2 gap-4">
                        <div class="bg-dark-bg/60 rounded-lg p-3">
                            <div class="text-xs text-gray-500 mb-1">Preferred Move-in</div>
                            <div class="text-gray-200 text-sm font-medium">{{ new Date(application.preferred_move_in).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }) }}</div>
                        </div>
                    </div>

                    <!-- Residential fields -->
                    <template v-if="application.adults !== null">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-dark-bg/60 rounded-lg p-3 text-center">
                                <div class="text-xl font-bold text-white">{{ application.adults }}</div>
                                <div class="text-xs text-gray-500 mt-1">Adults</div>
                            </div>
                            <div class="bg-dark-bg/60 rounded-lg p-3 text-center">
                                <div class="text-xl font-bold text-white">{{ application.children ?? 0 }}</div>
                                <div class="text-xs text-gray-500 mt-1">Children</div>
                            </div>
                            <div class="bg-dark-bg/60 rounded-lg p-3 text-center">
                                <div class="text-xl font-bold" :class="application.has_pets ? 'text-yellow-400' : 'text-gray-500'">
                                    {{ application.has_pets ? 'Yes' : 'No' }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Pets</div>
                            </div>
                        </div>
                        <div v-if="application.pet_details" class="bg-dark-bg/60 rounded-lg p-3">
                            <div class="text-xs text-gray-500 mb-1">Pet Details</div>
                            <p class="text-gray-300 text-sm">{{ application.pet_details }}</p>
                        </div>
                    </template>

                    <!-- Commercial fields -->
                    <template v-if="application.intended_use">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-dark-bg/60 rounded-lg p-3">
                                <div class="text-xs text-gray-500 mb-1">Intended Use</div>
                                <div class="text-gray-200 text-sm">{{ application.intended_use }}</div>
                            </div>
                            <div v-if="application.business_name" class="bg-dark-bg/60 rounded-lg p-3">
                                <div class="text-xs text-gray-500 mb-1">Business Name</div>
                                <div class="text-gray-200 text-sm">{{ application.business_name }}</div>
                            </div>
                        </div>
                    </template>

                    <div v-if="application.additional_comments" class="bg-dark-bg/60 rounded-lg p-4">
                        <div class="text-xs text-gray-500 mb-1">Additional Comments</div>
                        <p class="text-gray-300 text-sm whitespace-pre-line">{{ application.additional_comments }}</p>
                    </div>

                    <!-- Applicant's Terms & Conditions -->
                    <div v-if="application.applicant_terms" class="bg-yellow-500/5 border border-yellow-500/20 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs text-yellow-400 font-semibold uppercase tracking-wider">Applicant's Terms &amp; Conditions</span>
                            <span v-if="application.landlord_agreed_terms_at"
                                  class="text-xs px-2 py-0.5 rounded-full bg-green-500/20 text-green-400 ring-1 ring-green-500/30">
                                ✓ You agreed
                            </span>
                        </div>
                        <div class="bg-dark-bg/40 rounded-md p-3 text-sm text-gray-300 whitespace-pre-line leading-relaxed max-h-48 overflow-y-auto">{{ application.applicant_terms }}</div>
                    </div>

                    <div v-if="application.rejection_reason" class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                        <div class="text-xs text-red-400 mb-1 font-medium">Rejection Reason</div>
                        <p class="text-red-300 text-sm">{{ application.rejection_reason }}</p>
                    </div>
                </div>
            </div>

            <!-- Right: status + actions -->
            <div class="space-y-6 lg:sticky lg:top-4 lg:self-start">

                <!-- Status timeline -->
                <div class="bg-light-bg rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Status</h3>
                    <div class="flex items-center gap-3 mb-5">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" :class="cfg(application.status).pill">
                            {{ cfg(application.status).label }}
                        </span>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Submitted</span>
                            <span class="text-gray-300">{{ fmtDate(application.created_at) }}</span>
                        </div>
                        <div v-if="application.review_started_at" class="flex justify-between">
                            <span class="text-gray-500">Review started</span>
                            <span class="text-gray-300">{{ fmtDate(application.review_started_at) }}</span>
                        </div>
                        <div v-if="application.payment_requested_at" class="flex justify-between">
                            <span class="text-gray-500">Payment requested</span>
                            <span class="text-gray-300">{{ fmtDate(application.payment_requested_at) }}</span>
                        </div>
                        <div v-if="application.payment_deadline" class="flex justify-between">
                            <span class="text-gray-500">Payment deadline</span>
                            <span class="font-medium" :class="new Date(application.payment_deadline) < new Date() ? 'text-red-400' : 'text-orange-300'">
                                {{ fmtDate(application.payment_deadline) }}
                            </span>
                        </div>
                        <div v-if="application.completed_at" class="flex justify-between">
                            <span class="text-gray-500">Completed</span>
                            <span class="text-gray-300">{{ fmtDate(application.completed_at) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Workflow actions -->
                <div class="bg-light-bg rounded-xl p-5 space-y-3">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Actions</h3>

                    <!-- Start Review (pending only) -->
                    <form v-if="application.status === 'pending'"
                          @submit.prevent="startReview.post(route('landlord.property-applications.start-review', application.id))">
                        <button type="submit" :disabled="startReview.processing"
                                class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-semibold rounded-lg transition-colors">
                            {{ startReview.processing ? 'Starting…' : 'Start Review' }}
                        </button>
                        <p class="text-xs text-gray-500 mt-1.5 text-center">Notifies the applicant that you've started reviewing.</p>
                    </form>

                    <!-- Request Payment (under_review only) -->
                    <form v-if="application.status === 'under_review'"
                          @submit.prevent="requestPayment.post(route('landlord.property-applications.request-payment', application.id))"
                          class="space-y-3">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5 font-medium">Payment deadline</label>
                            <select v-model="requestPayment.deadline_hours"
                                    class="w-full bg-dark-bg border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500/50">
                                <option v-for="opt in deadlineOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Application is auto-rejected if payment isn't made in time.</p>
                        </div>
                        <!-- Agreement checkbox — only shown if applicant set terms -->
                        <div v-if="application.applicant_terms && !application.landlord_agreed_terms_at">
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input v-model="requestPayment.landlord_agreed_terms" type="checkbox"
                                       class="mt-0.5 h-4 w-4 rounded border-gray-600 bg-dark-bg text-orange-500 focus:ring-orange-500/50 flex-shrink-0" />
                                <span class="text-xs text-gray-400 group-hover:text-gray-300 transition-colors leading-relaxed">
                                    I have read and agree to the applicant's terms &amp; conditions above, and these will be included in the lease agreement.
                                </span>
                            </label>
                            <p v-if="requestPayment.errors.landlord_agreed_terms" class="text-xs text-red-400 mt-1">{{ requestPayment.errors.landlord_agreed_terms }}</p>
                        </div>

                        <button type="submit"
                                :disabled="requestPayment.processing || (!!application.applicant_terms && !requestPayment.landlord_agreed_terms)"
                                class="w-full px-4 py-2.5 bg-orange-600 hover:bg-orange-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition-colors">
                            {{ requestPayment.processing ? 'Sending…' : 'Request Payment' }}
                        </button>
                    </form>

                    <!-- Reject (any active) -->
                    <div v-if="isActive">
                        <button @click="showRejectModal = true"
                                class="w-full px-4 py-2.5 border border-red-500/50 hover:bg-red-500/10 text-red-400 hover:text-red-300 text-sm font-semibold rounded-lg transition-colors">
                            Reject Application
                        </button>
                    </div>

                    <div v-if="!isActive && application.status !== 'completed'" class="text-sm text-gray-500 text-center py-2">
                        No further actions available.
                    </div>
                    <div v-if="application.status === 'completed'" class="text-sm text-green-400 text-center py-2 font-medium">
                        ✓ Application completed successfully.
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <Teleport to="body">
            <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
                <div class="bg-light-bg rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
                    <h3 class="text-white font-semibold text-lg mb-1">Reject Application</h3>
                    <p class="text-gray-400 text-sm mb-4">Provide an optional reason. The applicant will be notified by email.</p>
                    <form @submit.prevent="rejectForm.post(route('landlord.property-applications.reject', application.id), { onSuccess: () => showRejectModal = false })">
                        <textarea v-model="rejectForm.reason" rows="3" placeholder="Reason for rejection (optional)"
                                  class="w-full bg-dark-bg border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-red/50 resize-none mb-4" />
                        <div class="flex gap-3">
                            <button type="button" @click="showRejectModal = false"
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
