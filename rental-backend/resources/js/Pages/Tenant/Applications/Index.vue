<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';

interface PropertyImage { image_url: string; is_primary: boolean }
interface Property {
    id: number; title: string; listing_type: string; property_type: string;
    price: number; images: PropertyImage[];
    province?: { name: string }; district?: { name: string };
}
interface Application {
    id: number; status: string; message: string | null;
    preferred_move_in: string | null; rejection_reason: string | null;
    created_at: string; review_started_at: string | null;
    payment_requested_at: string | null; payment_deadline: string | null; completed_at: string | null;
    applicant_terms: string | null; landlord_agreed_terms_at: string | null;
    property: Property & { terms_and_conditions?: string | null };
}

defineProps<{ applications: Application[] }>();

const statusConfig: Record<string, { pill: string; label: string; description: string }> = {
    pending:           { pill: 'bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30',  label: 'Pending',           description: 'Waiting for the landlord to respond.' },
    under_review:      { pill: 'bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30',        label: 'Under Review',      description: 'The landlord is reviewing your application.' },
    payment_requested: { pill: 'bg-orange-500/20 text-orange-400 ring-1 ring-orange-500/30',  label: 'Payment Required',  description: 'The landlord has approved! Complete payment to secure the property.' },
    completed:         { pill: 'bg-green-500/20 text-green-400 ring-1 ring-green-500/30',     label: 'Completed',         description: 'Congratulations! You have secured this property.' },
    rejected:          { pill: 'bg-red-500/20 text-red-400 ring-1 ring-red-500/30',           label: 'Rejected',          description: 'This application was not successful.' },
    cancelled:         { pill: 'bg-gray-500/20 text-gray-400 ring-1 ring-gray-500/30',        label: 'Cancelled',         description: 'You cancelled this application.' },
};

const cfg = (s: string) => statusConfig[s] ?? { pill: 'bg-gray-500/20 text-gray-400 ring-1 ring-gray-500/30', label: s, description: '' };

// ── Payment confirmation modal ──────────────────────────────────────────────
const confirmingApp = ref<Application | null>(null);
const checks = reactive({ visited: false, nonRefundable: false, accurate: false, landlordTerms: false });
const allChecked = () => {
    if (!checks.visited || !checks.nonRefundable || !checks.accurate) return false;
    if (confirmingApp.value?.property?.terms_and_conditions && !checks.landlordTerms) return false;
    return true;
};

const openPayConfirm = (app: Application) => {
    confirmingApp.value = app;
    checks.visited = false;
    checks.nonRefundable = false;
    checks.accurate = false;
    checks.landlordTerms = false;
};

const payForms: Record<number, ReturnType<typeof useForm>> = {};
const getPayForm = (id: number) => {
    if (!payForms[id]) payForms[id] = useForm({});
    return payForms[id];
};

const submitPayment = () => {
    if (!confirmingApp.value || !allChecked()) return;
    const app = confirmingApp.value;
    getPayForm(app.id).post(
        route('properties.pay', { property: app.property.id, application: app.id }),
        { onSuccess: () => { confirmingApp.value = null; } }
    );
};

// ── Cancel ──────────────────────────────────────────────────────────────────
const cancelApplication = (app: Application) => {
    if (!confirm('Cancel this application? This cannot be undone.')) return;
    router.delete(route('applications.cancel', app.id), { preserveScroll: true });
};

const fmtPrice = (n: number) => 'K' + n.toLocaleString();
const fmtDate  = (d: string | null) => d ? new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : null;

// Live countdown
const now = ref(Date.now());
let ticker: ReturnType<typeof setInterval>;
onMounted(() => { ticker = setInterval(() => { now.value = Date.now(); }, 1000); });
onUnmounted(() => clearInterval(ticker));

const countdown = (deadline: string | null): string => {
    if (!deadline) return '';
    const diff = new Date(deadline).getTime() - now.value;
    if (diff <= 0) return 'Expired';
    const h = Math.floor(diff / 3_600_000);
    const m = Math.floor((diff % 3_600_000) / 60_000);
    const s = Math.floor((diff % 60_000) / 1000);
    if (h > 0) return `${h}h ${m}m remaining`;
    if (m > 0) return `${m}m ${s}s remaining`;
    return `${s}s remaining`;
};

const isUrgent = (deadline: string | null): boolean => {
    if (!deadline) return false;
    return new Date(deadline).getTime() - now.value < 3_600_000; // < 1 hour
};

const steps = (app: Application) => [
    { label: 'Applied',           date: app.created_at,           done: true },
    { label: 'Under Review',      date: app.review_started_at,    done: !!app.review_started_at },
    { label: 'Payment Requested', date: app.payment_requested_at, done: !!app.payment_requested_at },
    { label: 'Completed',         date: app.completed_at,         done: !!app.completed_at },
];
</script>

<template>
    <Head title="My Applications" />

    <AuthenticatedLayout header="My Applications">
        <div class="p-6">
            <div v-if="!applications.length" class="text-center py-20">
                <div class="text-5xl mb-4">📋</div>
                <h3 class="text-xl font-semibold text-white mb-2">No applications yet</h3>
                <p class="text-gray-400 mb-6">Browse properties and apply for one that interests you.</p>
                <Link :href="route('properties.index')"
                      class="inline-flex items-center px-5 py-2.5 bg-brand-red hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    Browse Properties
                </Link>
            </div>

            <div v-else class="space-y-5">
                <div v-for="app in applications" :key="app.id" class="bg-light-bg rounded-xl overflow-hidden">

                    <!-- Property banner -->
                    <div class="flex items-start gap-4 p-5">
                        <div class="h-16 w-24 rounded-lg overflow-hidden flex-shrink-0 bg-dark-bg">
                            <img v-if="app.property.images?.[0]"
                                 :src="'/storage/' + app.property.images[0].image_url"
                                 :alt="app.property.title"
                                 class="h-full w-full object-cover" />
                            <div v-else class="h-full w-full flex items-center justify-center text-gray-600 text-2xl">🏠</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3 flex-wrap">
                                <div>
                                    <h3 class="text-white font-semibold">{{ app.property.title }}</h3>
                                    <p class="text-gray-400 text-sm mt-0.5">
                                        {{ [app.property.district?.name, app.property.province?.name].filter(Boolean).join(', ') }}
                                    </p>
                                    <div class="flex gap-2 mt-1.5">
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-700 text-gray-300 capitalize">{{ app.property.property_type }}</span>
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-700 text-gray-300 capitalize">{{ app.property.listing_type }}</span>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-white font-bold">{{ fmtPrice(app.property.price) }}</div>
                                    <div class="text-gray-500 text-xs">{{ app.property.listing_type === 'rent' ? '/month' : 'asking price' }}</div>
                                    <span class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs font-medium" :class="cfg(app.status).pill">
                                        {{ cfg(app.status).label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status description -->
                    <div class="px-5 pb-4">
                        <p class="text-sm text-gray-400">{{ cfg(app.status).description }}</p>
                        <div v-if="app.status === 'rejected' && app.rejection_reason"
                             class="mt-3 bg-red-500/10 border border-red-500/20 rounded-lg p-3 text-sm text-red-300">
                            {{ app.rejection_reason }}
                        </div>
                    </div>

                    <!-- Progress timeline -->
                    <div class="border-t border-gray-700/50 px-5 py-4">
                        <div class="flex items-center gap-0">
                            <template v-for="(step, i) in steps(app)" :key="i">
                                <div class="flex flex-col items-center flex-shrink-0">
                                    <div class="h-6 w-6 rounded-full flex items-center justify-center text-xs font-bold"
                                         :class="step.done ? 'bg-green-500 text-white' : 'bg-gray-700 text-gray-500'">
                                        {{ step.done ? '✓' : i + 1 }}
                                    </div>
                                    <div class="text-xs mt-1 text-center" :class="step.done ? 'text-gray-300' : 'text-gray-600'" style="max-width: 72px">
                                        {{ step.label }}
                                    </div>
                                    <div v-if="step.date && step.done" class="text-xs text-gray-500 text-center" style="max-width: 72px">
                                        {{ fmtDate(step.date) }}
                                    </div>
                                </div>
                                <div v-if="i < steps(app).length - 1"
                                     class="flex-1 h-0.5 mb-5"
                                     :class="steps(app)[i + 1].done ? 'bg-green-500' : 'bg-gray-700'" />
                            </template>
                        </div>
                    </div>

                    <!-- Pay banner -->
                    <div v-if="app.status === 'payment_requested'"
                         class="border-t px-5 py-4"
                         :class="isUrgent(app.payment_deadline) ? 'border-red-500/30 bg-red-500/5' : 'border-orange-500/20 bg-orange-500/5'">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="text-sm font-semibold" :class="isUrgent(app.payment_deadline) ? 'text-red-300' : 'text-orange-300'">
                                    Payment Required
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    First payment secures the property. Payment is <span class="text-red-400 font-medium">non-refundable</span>.
                                </div>
                                <!-- Deadline countdown -->
                                <div v-if="app.payment_deadline" class="mt-2 flex items-center gap-2">
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                                          :class="isUrgent(app.payment_deadline)
                                              ? 'bg-red-500/20 text-red-300 ring-1 ring-red-500/30'
                                              : 'bg-orange-500/20 text-orange-300 ring-1 ring-orange-500/30'">
                                        ⏱ {{ countdown(app.payment_deadline) }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        Deadline: {{ fmtDate(app.payment_deadline) }}
                                    </span>
                                </div>
                            </div>
                            <button @click="openPayConfirm(app)"
                                    class="flex-shrink-0 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-lg transition-colors shadow-lg shadow-green-900/30">
                                Make Payment
                            </button>
                        </div>
                    </div>

                    <!-- Cancel -->
                    <div v-if="['pending', 'under_review', 'payment_requested'].includes(app.status)"
                         class="border-t border-gray-700/30 px-5 py-3 flex justify-end">
                        <button @click="cancelApplication(app)"
                                class="text-xs text-gray-500 hover:text-red-400 transition-colors">
                            Cancel application
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment confirmation modal -->
        <Teleport to="body">
            <div v-if="confirmingApp" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm px-4">
                <div class="bg-light-bg rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">

                    <!-- Header -->
                    <div class="bg-dark-bg/80 px-6 py-5 border-b border-gray-700">
                        <h2 class="text-white font-bold text-lg">Confirm Payment</h2>
                        <p class="text-gray-400 text-sm mt-0.5">{{ confirmingApp.property.title }}</p>
                    </div>

                    <!-- Amount -->
                    <div class="px-6 pt-5 pb-4 flex items-center justify-between bg-green-500/5 border-b border-green-500/10">
                        <span class="text-gray-400 text-sm">Amount due</span>
                        <span class="text-green-400 font-bold text-xl">{{ fmtPrice(confirmingApp.property.price) }}</span>
                    </div>

                    <!-- Warning banner -->
                    <div class="mx-6 mt-5 flex gap-3 bg-red-500/10 border border-red-500/30 rounded-xl p-4">
                        <div class="text-red-400 text-lg flex-shrink-0">⚠️</div>
                        <p class="text-red-300 text-sm leading-relaxed">
                            <span class="font-semibold">This payment is non-refundable.</span>
                            Only proceed if you have personally inspected the property and are fully satisfied with its condition.
                        </p>
                    </div>

                    <!-- Landlord T&Cs (if set) -->
                    <div v-if="confirmingApp?.property?.terms_and_conditions" class="mx-6 mt-4">
                        <div class="rounded-lg border border-yellow-500/20 bg-yellow-500/5 p-4">
                            <h4 class="text-xs font-semibold text-yellow-300 uppercase tracking-wider mb-2">📋 Landlord's Terms &amp; Conditions</h4>
                            <div class="bg-dark-bg/50 rounded-md p-3 text-xs text-gray-300 whitespace-pre-line leading-relaxed max-h-36 overflow-y-auto">{{ confirmingApp.property.terms_and_conditions }}</div>
                        </div>
                    </div>

                    <!-- Checkboxes -->
                    <div class="px-6 pt-5 pb-2 space-y-4">
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <input v-model="checks.visited" type="checkbox"
                                   class="mt-0.5 h-4 w-4 rounded border-gray-600 bg-dark-bg text-green-500 focus:ring-green-500/50 focus:ring-offset-0 flex-shrink-0" />
                            <span class="text-sm text-gray-300 group-hover:text-white transition-colors leading-relaxed">
                                I have <span class="font-medium text-white">personally visited and inspected</span> the property and I am satisfied with its condition.
                            </span>
                        </label>
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <input v-model="checks.nonRefundable" type="checkbox"
                                   class="mt-0.5 h-4 w-4 rounded border-gray-600 bg-dark-bg text-green-500 focus:ring-green-500/50 focus:ring-offset-0 flex-shrink-0" />
                            <span class="text-sm text-gray-300 group-hover:text-white transition-colors leading-relaxed">
                                I understand that this payment is <span class="font-medium text-white">final and non-refundable</span> once confirmed.
                            </span>
                        </label>
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <input v-model="checks.accurate" type="checkbox"
                                   class="mt-0.5 h-4 w-4 rounded border-gray-600 bg-dark-bg text-green-500 focus:ring-green-500/50 focus:ring-offset-0 flex-shrink-0" />
                            <span class="text-sm text-gray-300 group-hover:text-white transition-colors leading-relaxed">
                                I confirm that all the information I have provided in my application is <span class="font-medium text-white">accurate and truthful</span>.
                            </span>
                        </label>

                        <!-- T&Cs agreement — only shown when landlord set terms -->
                        <label v-if="confirmingApp?.property?.terms_and_conditions"
                               class="flex items-start gap-3 cursor-pointer group border-t border-yellow-500/20 pt-4">
                            <input v-model="checks.landlordTerms" type="checkbox"
                                   class="mt-0.5 h-4 w-4 rounded border-gray-600 bg-dark-bg text-yellow-500 focus:ring-yellow-500/50 focus:ring-offset-0 flex-shrink-0" />
                            <span class="text-sm text-gray-300 group-hover:text-white transition-colors leading-relaxed">
                                I have read and agree to the <span class="font-medium text-yellow-300">landlord's terms &amp; conditions</span> shown above, and I understand they will form part of my lease agreement.
                            </span>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-5 flex gap-3">
                        <button @click="confirmingApp = null"
                                class="flex-1 px-4 py-2.5 border border-gray-700 rounded-lg text-gray-300 text-sm hover:bg-gray-700/50 transition-colors">
                            Go Back
                        </button>
                        <button @click="submitPayment"
                                :disabled="!allChecked() || (confirmingApp ? getPayForm(confirmingApp.id).processing : false)"
                                class="flex-1 px-4 py-2.5 bg-green-600 hover:bg-green-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-bold rounded-lg transition-colors">
                            {{ confirmingApp && getPayForm(confirmingApp.id).processing ? 'Processing…' : 'Confirm & Pay' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
