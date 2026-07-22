<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    MapPinIcon, HomeIcon, ShareIcon, FlagIcon,
    StarIcon as StarOutlineIcon,
    CurrencyDollarIcon, TagIcon, BuildingOffice2Icon,
    SparklesIcon, BoltIcon, PhotoIcon, EyeIcon,
    UserIcon, EnvelopeIcon, CalendarDaysIcon,
    CheckCircleIcon, XCircleIcon, DocumentTextIcon,
    ChatBubbleLeftEllipsisIcon, ClockIcon,
    PencilSquareIcon, ClipboardDocumentListIcon,
    HeartIcon, PaperClipIcon, DocumentArrowDownIcon,
    ShieldCheckIcon,
} from '@heroicons/vue/24/outline';
import { StarIcon } from '@heroicons/vue/24/solid';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ScamWarningBanner from '@/Components/ScamWarningBanner.vue';
import VerificationBadge from '@/Components/VerificationBadge.vue';
import ReportListingModal from '@/Components/ReportListingModal.vue';
import PropertiesMap from '@/Components/PropertiesMap.vue';

const props = defineProps<{
    property: any;
    applicantCount?: number;
    isOwner: boolean;
}>();

const page = usePage();
const user = computed(() => (page.props.auth as any).user);

// Image lightbox
const selectedImage = ref<string | null>(null);
const openImage  = (url: string) => { selectedImage.value = url; };
const closeImage = () => { selectedImage.value = null; };

// Amenities
const amenities = computed<string[]>(() => {
    const a = props.property.amenities;
    if (!a) return [];
    if (Array.isArray(a)) return a;
    try { return JSON.parse(a); } catch { return []; }
});

// Status helpers
const availabilityConfig: Record<string, { pill: string; label: string }> = {
    available: { pill: 'bg-green-500/20 text-green-400 ring-1 ring-green-500/30', label: 'Available' },
    rented:    { pill: 'bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30',    label: 'Rented' },
    sold:      { pill: 'bg-gray-500/20 text-gray-400 ring-1 ring-gray-500/30',    label: 'Sold' },
};
const approvalConfig: Record<string, { pill: string; dot: string }> = {
    pending:      { pill: 'bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30', dot: 'bg-yellow-400' },
    under_review: { pill: 'bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30',       dot: 'bg-blue-400' },
    approved:     { pill: 'bg-green-500/20 text-green-400 ring-1 ring-green-500/30',     dot: 'bg-green-400' },
    rejected:     { pill: 'bg-red-500/20 text-red-400 ring-1 ring-red-500/30',           dot: 'bg-red-400' },
};
const av  = () => availabilityConfig[props.property.availability_status] ?? availabilityConfig.available;
const sc  = () => approvalConfig[props.property.approval_status] ?? approvalConfig.pending;
const fmt = (d: string | null) => d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';

// Apply
const applyNow = () => {
    if (!user.value) { window.location.href = route('login'); return; }
    if (isOwner.value) { alert('You cannot apply to your own property.'); return; }
    window.location.href = route('properties.apply', props.property.id);
};

// Tour
const showTourModal = ref(false);
const tourForm = useForm({ scheduled_at: '', notes: '' });
const scheduleTour = () => {
    if (!user.value) { window.location.href = route('login'); return; }
    if (isOwner.value) { alert('You cannot schedule a tour for your own property.'); return; }
    showTourModal.value = true;
};
const closeTourModal = () => { showTourModal.value = false; tourForm.reset(); };
const submitTourRequest = () => {
    tourForm.post(route('properties.tour.store', props.property.id), {
        onSuccess: closeTourModal,
    });
};

// Reviews
const reviewForm = useForm({ rating: 5, comment: '' });
const submitReview = () => {
    reviewForm.post(route('properties.reviews.store', props.property.id), {
        onSuccess: () => reviewForm.reset(),
    });
};

// Report
const showReportModal = ref(false);
const handleReportSubmitted = () => {
    alert('Thank you for your report. We will review this listing shortly.');
};

// Single-property map data for the reusable PropertiesMap component
const singlePropertyForMap = computed(() => [{
    id: props.property.id,
    title: props.property.title,
    price: props.property.price,
    listing_type: props.property.listing_type,
    availability_status: props.property.availability_status,
    approval_status: props.property.approval_status,
    latitude: props.property.latitude,
    longitude: props.property.longitude,
    detailUrl: '#', // already on this page
}]);
</script>

<template>
    <Head :title="property.title" />

    <AuthenticatedLayout
        :header="property.title"
        :back-url="isOwner ? route('landlord.properties.index') : route('properties.index')"
    >
        <div class="py-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Scam warning for tenants only -->
                <ScamWarningBanner v-if="!isOwner" class="mb-4" />

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                    <!-- ══════════════════════════════════════
                         LEFT COLUMN  (shared structure)
                    ══════════════════════════════════════ -->
                    <div class="lg:col-span-2 space-y-4">

                        <!-- Photos -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <PhotoIcon class="w-4 h-4 text-brand-red" />
                                    <h3 class="text-sm font-semibold text-white">Photos</h3>
                                </div>
                                <span class="text-xs text-gray-500">{{ property.images?.length ?? 0 }} photo{{ (property.images?.length ?? 0) !== 1 ? 's' : '' }}</span>
                            </div>
                            <div v-if="property.images?.length" class="p-4 grid grid-cols-3 sm:grid-cols-4 gap-2">
                                <div
                                    v-for="(image, index) in property.images"
                                    :key="image.id"
                                    :class="index === 0 ? 'col-span-3 sm:col-span-4 aspect-video' : 'aspect-video'"
                                    class="relative rounded-lg overflow-hidden cursor-pointer group ring-1 ring-gray-700/40"
                                    @click="openImage('/storage/' + image.image_url)"
                                >
                                    <img :src="'/storage/' + image.image_url" :alt="`Photo ${index + 1}`"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200" />
                                    <div v-if="image.is_primary"
                                        class="absolute top-1.5 left-1.5 bg-brand-red text-white text-[10px] px-1.5 py-0.5 rounded font-medium">
                                        Primary
                                    </div>
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition flex items-center justify-center">
                                        <EyeIcon class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition" />
                                    </div>
                                </div>
                            </div>
                            <div v-else class="p-8 flex flex-col items-center justify-center gap-2">
                                <PhotoIcon class="w-10 h-10 text-gray-600" />
                                <p class="text-sm text-gray-500">No photos available</p>
                            </div>
                        </div>

                        <!-- Overview -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <HomeIcon class="w-4 h-4 text-brand-red" />
                                    <h3 class="text-sm font-semibold text-white">Overview</h3>
                                </div>
                                <!-- Owner sees both status pills; tenants see availability only -->
                                <div class="flex items-center gap-2">
                                    <template v-if="isOwner">
                                        <span :class="[sc().pill, 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize']">
                                            <span :class="[sc().dot, 'h-1.5 w-1.5 rounded-full']"></span>
                                            {{ property.approval_status?.replace('_', ' ') }}
                                        </span>
                                    </template>
                                    <span :class="[av().pill, 'px-2.5 py-0.5 rounded-full text-xs font-semibold']">
                                        {{ av().label }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-4 space-y-4">
                                <!-- Key metrics -->
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <div class="col-span-2 bg-dark-bg/60 rounded-lg px-3 py-2.5 flex items-start gap-2">
                                        <CurrencyDollarIcon class="w-4 h-4 text-brand-red mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-400">Asking Price</p>
                                            <p class="text-xl font-bold text-brand-red">K{{ Number(property.price).toLocaleString() }}</p>
                                            <p class="text-xs text-gray-500 capitalize">for {{ property.listing_type }}</p>
                                        </div>
                                    </div>
                                    <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 flex items-start gap-2">
                                        <BuildingOffice2Icon class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-400">Type</p>
                                            <p class="text-xs font-semibold text-white capitalize">{{ property.property_type }}</p>
                                            <p class="text-xs text-gray-500 capitalize">{{ property.property_subtype?.replace('_', ' ') ?? '—' }}</p>
                                        </div>
                                    </div>
                                    <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 flex items-start gap-2">
                                        <TagIcon class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs text-gray-400">Details</p>
                                            <p v-if="property.bedrooms > 0 || property.bathrooms > 0" class="text-xs font-semibold text-white">
                                                {{ property.bedrooms > 0 ? property.bedrooms + ' bed' : '' }}{{ property.bedrooms > 0 && property.bathrooms > 0 ? ' · ' : '' }}{{ property.bathrooms > 0 ? property.bathrooms + ' bath' : '' }}
                                            </p>
                                            <p v-else class="text-xs font-semibold text-gray-500">N/A</p>
                                            <p class="text-xs text-gray-500">{{ property.square_feet ? property.square_feet + ' sqft' : '—' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Rating row — tenants only -->
                                <div v-if="!isOwner && property.reviews_count" class="flex items-center gap-1.5">
                                    <StarIcon class="h-4 w-4 text-amber-500" />
                                    <span class="text-sm font-bold text-white">{{ parseFloat(property.average_rating ?? 0).toFixed(1) }}</span>
                                    <span class="text-xs text-gray-500">({{ property.reviews_count }} review{{ property.reviews_count !== 1 ? 's' : '' }})</span>
                                </div>

                                <!-- Owner: specs grid with all fields -->
                                <template v-if="isOwner">
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                            <p class="text-xs text-gray-500 mb-0.5">Listed</p>
                                            <p class="text-xs font-semibold text-white">{{ fmt(property.created_at) }}</p>
                                        </div>
                                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                            <p class="text-xs text-gray-500 mb-0.5">Property Code</p>
                                            <p class="text-xs font-semibold text-white font-mono">{{ property.code ?? '—' }}</p>
                                        </div>
                                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                            <p class="text-xs text-gray-500 mb-0.5">Visible in Search</p>
                                            <p class="text-xs font-semibold" :class="property.is_visible_in_search ? 'text-green-400' : 'text-red-400'">
                                                {{ property.is_visible_in_search ? 'Yes' : 'No' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div v-if="property.rejection_reason" class="p-2.5 bg-red-500/10 ring-1 ring-red-500/20 rounded-lg">
                                        <p class="text-xs font-medium text-red-400 mb-0.5">Rejection Reason</p>
                                        <p class="text-xs text-red-300/80">{{ property.rejection_reason }}</p>
                                    </div>
                                </template>

                                <!-- Description -->
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1.5">Description</p>
                                    <p class="text-sm text-gray-300 leading-relaxed whitespace-pre-line">{{ property.description }}</p>
                                </div>

                                <!-- Terms & Conditions -->
                                <div v-if="property.terms_and_conditions">
                                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1.5">
                                        {{ isOwner ? 'Your Terms &amp; Conditions' : "Landlord's Terms &amp; Conditions" }}
                                    </p>
                                    <p v-if="!isOwner" class="text-xs text-gray-500 mb-2">You will be required to agree to these before completing payment.</p>
                                    <div class="bg-dark-bg/60 rounded-lg p-3 text-sm text-gray-300 whitespace-pre-line leading-relaxed border border-yellow-500/10">
                                        {{ property.terms_and_conditions }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <MapPinIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Location</h3>
                            </div>
                            <div class="p-4 space-y-3">
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    <div class="bg-dark-bg/60 rounded-lg px-3 py-2">
                                        <p class="text-xs text-gray-500 mb-0.5">Province</p>
                                        <p class="text-xs font-medium text-white">{{ property.province?.name ?? '—' }}</p>
                                    </div>
                                    <div class="bg-dark-bg/60 rounded-lg px-3 py-2">
                                        <p class="text-xs text-gray-500 mb-0.5">District</p>
                                        <p class="text-xs font-medium text-white">{{ property.district?.name ?? '—' }}</p>
                                    </div>
                                    <div class="bg-dark-bg/60 rounded-lg px-3 py-2">
                                        <p class="text-xs text-gray-500 mb-0.5">Town</p>
                                        <p class="text-xs font-medium text-white">{{ property.town?.name ?? '—' }}</p>
                                    </div>
                                    <!-- Full street address for owner; area only for tenant -->
                                    <div v-if="isOwner" class="col-span-2 sm:col-span-3 bg-dark-bg/60 rounded-lg px-3 py-2">
                                        <p class="text-xs text-gray-500 mb-0.5">Street Address</p>
                                        <p class="text-xs font-medium text-white">{{ property.street_address ?? '—' }}</p>
                                    </div>
                                </div>
                                <PropertiesMap
                                    :properties="singlePropertyForMap"
                                    height="h-56"
                                    :zoom="15"
                                />
                                <!-- Tenant sees general area note -->
                                <p v-if="!isOwner" class="text-xs text-gray-500 text-center">Exact address shared after application is approved.</p>
                            </div>
                        </div>

                        <!-- Amenities -->
                        <div v-if="amenities.length" class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <SparklesIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Amenities</h3>
                                <span class="ml-auto text-xs text-gray-500">{{ amenities.length }}</span>
                            </div>
                            <div class="p-4 flex flex-wrap gap-2">
                                <span v-for="amenity in amenities" :key="amenity"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-dark-bg/60 ring-1 ring-gray-700/60 text-xs text-gray-300">
                                    <CheckCircleIcon class="w-3 h-3 text-green-400" />
                                    {{ amenity }}
                                </span>
                            </div>
                        </div>

                        <!-- Utilities -->
                        <div v-if="property.utilities?.length" class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <BoltIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Utilities</h3>
                            </div>
                            <div class="p-4 grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <div v-for="utility in property.utilities" :key="utility.id"
                                    class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">{{ utility.name }}</p>
                                    <p class="text-xs font-semibold text-white">
                                        {{ utility.pivot?.utility_option_id
                                            ? (utility.options?.find((o: any) => o.id === utility.pivot.utility_option_id)?.label ?? '—')
                                            : 'Not specified' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ── Owner-only: Documents ── -->
                        <div v-if="isOwner" class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <PaperClipIcon class="w-4 h-4 text-brand-red" />
                                    <h3 class="text-sm font-semibold text-white">Documents</h3>
                                </div>
                                <span class="text-xs text-gray-500">{{ property.documents?.length ?? 0 }} file{{ (property.documents?.length ?? 0) !== 1 ? 's' : '' }}</span>
                            </div>
                            <div class="p-4">
                                <div v-if="property.documents?.length" class="space-y-2">
                                    <a v-for="doc in property.documents" :key="doc.id"
                                        :href="'/storage/' + doc.file_path" target="_blank"
                                        class="flex items-center gap-3 px-3 py-2.5 bg-dark-bg/60 rounded-lg hover:bg-dark-bg/80 transition group">
                                        <DocumentTextIcon class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-white truncate">{{ doc.name }}</p>
                                            <p class="text-xs text-gray-500">{{ doc.file_size_formatted }} · {{ doc.mime_type?.split('/')[1]?.toUpperCase() }}</p>
                                        </div>
                                        <DocumentArrowDownIcon class="w-4 h-4 text-gray-500 group-hover:text-white transition flex-shrink-0" />
                                    </a>
                                </div>
                                <div v-else class="py-6 text-center">
                                    <PaperClipIcon class="w-6 h-6 text-gray-600 mx-auto mb-1" />
                                    <p class="text-xs text-gray-500">No documents uploaded</p>
                                </div>
                            </div>
                        </div>

                        <!-- ── Owner-only: Applications summary ── -->
                        <div v-if="isOwner" class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <ClipboardDocumentListIcon class="w-4 h-4 text-brand-red" />
                                    <h3 class="text-sm font-semibold text-white">Applications</h3>
                                    <span class="ml-1 text-xs px-2 py-0.5 rounded-full bg-dark-bg/60 text-gray-400">{{ applicantCount ?? 0 }} total</span>
                                </div>
                                <Link :href="route('landlord.property-applications.index')"
                                    class="text-xs text-brand-red hover:text-red-400 font-medium">
                                    View All →
                                </Link>
                            </div>
                            <div class="p-4 grid grid-cols-3 gap-2">
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 text-center">
                                    <p class="text-2xl font-bold text-white">{{ applicantCount ?? 0 }}</p>
                                    <p class="text-xs text-gray-500">Total</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 text-center">
                                    <p class="text-2xl font-bold text-yellow-400">{{ property.applications?.filter((a: any) => a.status === 'pending').length ?? 0 }}</p>
                                    <p class="text-xs text-gray-500">Pending</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 text-center">
                                    <p class="text-2xl font-bold text-green-400">{{ property.applications?.filter((a: any) => a.status === 'approved').length ?? 0 }}</p>
                                    <p class="text-xs text-gray-500">Approved</p>
                                </div>
                            </div>
                        </div>

                        <!-- ── Tenant-only: Reviews ── -->
                        <div v-if="!isOwner" class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <ChatBubbleLeftEllipsisIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Reviews</h3>
                                <span class="ml-auto text-xs text-gray-500">{{ property.reviews_count }}</span>
                            </div>
                            <div class="p-4 space-y-4">
                                <div v-if="user" class="bg-dark-bg/60 rounded-lg p-4">
                                    <h4 class="text-xs font-semibold text-gray-300 uppercase tracking-wide mb-3">Write a Review</h4>
                                    <form @submit.prevent="submitReview" class="space-y-3">
                                        <div class="flex items-center gap-1.5">
                                            <template v-for="star in 5" :key="star">
                                                <StarIcon @click="reviewForm.rating = star"
                                                    class="h-5 w-5 cursor-pointer hover:scale-110 transition"
                                                    :class="star <= reviewForm.rating ? 'text-amber-500' : 'text-gray-600'" />
                                            </template>
                                        </div>
                                        <textarea v-model="reviewForm.comment" rows="3"
                                            class="w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 text-sm focus:border-brand-red focus:ring-brand-red placeholder-gray-500"
                                            placeholder="Share your experience…" required></textarea>
                                        <div class="flex justify-end">
                                            <PrimaryButton :disabled="reviewForm.processing">Post Review</PrimaryButton>
                                        </div>
                                    </form>
                                </div>
                                <div v-else class="bg-dark-bg/60 rounded-lg px-4 py-3 text-center">
                                    <Link :href="route('login')" class="text-brand-red font-semibold hover:underline text-sm">Log in</Link>
                                    <span class="text-gray-400 text-sm"> to write a review.</span>
                                </div>

                                <div v-if="property.reviews?.length" class="divide-y divide-gray-700/50">
                                    <div v-for="review in property.reviews" :key="review.id" class="py-4 first:pt-0 last:pb-0">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <div class="h-7 w-7 rounded-full bg-brand-red/10 flex items-center justify-center text-brand-red font-bold text-xs flex-shrink-0">
                                                    {{ review.user.name.charAt(0).toUpperCase() }}
                                                </div>
                                                <span class="text-sm font-medium text-white">{{ review.user.name }}</span>
                                            </div>
                                            <div class="flex items-center gap-1 text-amber-500">
                                                <StarIcon class="h-3.5 w-3.5" />
                                                <span class="text-xs font-bold">{{ review.rating }}</span>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-300 leading-relaxed">{{ review.comment }}</p>
                                        <p class="mt-1.5 text-xs text-gray-500">{{ new Date(review.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}</p>
                                    </div>
                                </div>
                                <div v-else class="py-6 text-center">
                                    <StarOutlineIcon class="w-8 h-8 text-gray-600 mx-auto mb-2" />
                                    <p class="text-xs text-gray-500">No reviews yet. Be the first!</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- ══════════════════════════════════════
                         RIGHT COLUMN — OWNER view
                    ══════════════════════════════════════ -->
                    <div v-if="isOwner" class="space-y-4">

                        <!-- Management actions -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <ShieldCheckIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Manage</h3>
                            </div>
                            <div class="p-4 space-y-2">
                                <Link v-if="['pending', 'rejected'].includes(property.approval_status)"
                                    :href="route('landlord.properties.edit', property.id)"
                                    class="w-full flex items-center justify-center gap-2 bg-brand-red hover:bg-red-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition">
                                    <PencilSquareIcon class="w-4 h-4" /> Edit Property
                                </Link>
                                <Link :href="route('landlord.property-applications.index')"
                                    class="w-full flex items-center justify-center gap-2 bg-dark-bg/60 hover:bg-dark-bg text-gray-300 hover:text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                                    <ClipboardDocumentListIcon class="w-4 h-4" /> View Applications
                                </Link>
                            </div>
                        </div>

                        <!-- Status & dates -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <ClockIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Status &amp; Dates</h3>
                            </div>
                            <div class="p-4 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Approval</span>
                                    <span :class="[sc().pill, 'px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize']">
                                        {{ property.approval_status?.replace('_', ' ') }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Availability</span>
                                    <span :class="[av().pill, 'px-2.5 py-0.5 rounded-full text-xs font-semibold']">
                                        {{ av().label }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Listed</span>
                                    <span class="text-xs font-medium text-gray-300">{{ fmt(property.created_at) }}</span>
                                </div>
                                <div v-if="property.submitted_date" class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Submitted for review</span>
                                    <span class="text-xs font-medium text-gray-300">{{ fmt(property.submitted_date) }}</span>
                                </div>
                                <div v-if="property.approved_date" class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Approved</span>
                                    <span class="text-xs font-medium text-green-400">{{ fmt(property.approved_date) }}</span>
                                </div>
                                <div v-if="property.availability_changed_at" class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Status updated</span>
                                    <span class="text-xs font-medium text-gray-300">{{ fmt(property.availability_changed_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Engagement stats -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <EyeIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Engagement</h3>
                            </div>
                            <div class="p-4 grid grid-cols-2 gap-2">
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 text-center">
                                    <EyeIcon class="w-4 h-4 text-gray-400 mx-auto mb-1" />
                                    <p class="text-xl font-bold text-white">{{ property.view_count ?? 0 }}</p>
                                    <p class="text-xs text-gray-500">Views</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 text-center">
                                    <ClipboardDocumentListIcon class="w-4 h-4 text-yellow-400 mx-auto mb-1" />
                                    <p class="text-xl font-bold text-white">{{ applicantCount ?? 0 }}</p>
                                    <p class="text-xs text-gray-500">Applicants</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 text-center">
                                    <HeartIcon class="w-4 h-4 text-red-400 mx-auto mb-1" />
                                    <p class="text-xl font-bold text-white">{{ property.like_count ?? 0 }}</p>
                                    <p class="text-xs text-gray-500">Likes</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 text-center">
                                    <StarIcon class="w-4 h-4 text-amber-400 mx-auto mb-1" />
                                    <p class="text-xl font-bold text-white">{{ parseFloat(property.average_rating ?? 0).toFixed(1) }}</p>
                                    <p class="text-xs text-gray-500">Avg Rating</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- ══════════════════════════════════════
                         RIGHT COLUMN — TENANT view
                    ══════════════════════════════════════ -->
                    <div v-else class="space-y-4">

                        <!-- CTA card -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-white">Interested?</h3>
                                <div class="flex items-center gap-2">
                                    <button @click="showReportModal = true"
                                        class="flex items-center gap-1 text-xs text-gray-500 hover:text-red-400 transition">
                                        <FlagIcon class="h-3.5 w-3.5" /> Report
                                    </button>
                                    <button class="flex items-center gap-1 text-xs text-gray-500 hover:text-white transition">
                                        <ShareIcon class="h-3.5 w-3.5" /> Share
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 space-y-3">
                                <div v-if="applicantCount && applicantCount > 0"
                                    class="text-center text-xs text-orange-400 font-medium bg-orange-500/10 rounded-lg px-3 py-2">
                                    {{ applicantCount }} {{ applicantCount === 1 ? 'person has' : 'people have' }} applied — act fast!
                                </div>
                                <button @click="scheduleTour"
                                    class="w-full flex items-center justify-center gap-2 bg-brand-red hover:bg-red-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition">
                                    <CalendarDaysIcon class="w-4 h-4" /> Schedule a Tour
                                </button>
                                <button @click="applyNow"
                                    class="w-full flex items-center justify-center gap-2 border border-brand-red text-brand-red hover:bg-brand-red/10 px-4 py-2.5 rounded-lg text-sm font-semibold transition">
                                    Apply Now
                                </button>
                            </div>
                        </div>

                        <!-- Availability status for tenants -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <ClockIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Status</h3>
                            </div>
                            <div class="p-4 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Availability</span>
                                    <span :class="[av().pill, 'px-2.5 py-0.5 rounded-full text-xs font-semibold']">
                                        {{ av().label }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Listed</span>
                                    <span class="text-xs font-medium text-gray-300">{{ fmt(property.created_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Landlord info -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <UserIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Landlord</h3>
                            </div>
                            <div class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-dark-bg/80 ring-1 ring-gray-700 flex items-center justify-center flex-shrink-0 text-brand-red font-bold text-sm">
                                        {{ property.landlord?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <p class="text-sm font-semibold text-white">{{ property.landlord?.name ?? '—' }}</p>
                                            <VerificationBadge v-if="property.landlord?.verification_level" :level="property.landlord.verification_level" />
                                        </div>
                                        <p v-if="property.landlord?.landlord_type === 'agent'"
                                            class="text-xs text-brand-red font-medium uppercase tracking-wide mt-0.5">
                                            Verified Agent
                                        </p>
                                        <p class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                            <EnvelopeIcon class="w-3 h-3" />
                                            {{ property.landlord?.email ?? '—' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Tour Modal -->
    <div v-if="showTourModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeTourModal">
        <div class="fixed inset-0 bg-black/75" @click="closeTourModal"></div>
        <div class="relative bg-light-bg rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-white">Schedule a Tour</h3>
                <button @click="closeTourModal" class="text-gray-400 hover:text-white transition">
                    <XCircleIcon class="w-5 h-5" />
                </button>
            </div>
            <div class="px-5 py-5">
                <p class="text-sm text-gray-400 mb-4">Pick a date and time to visit <span class="text-white font-medium">{{ property.title }}</span>.</p>
                <form @submit.prevent="submitTourRequest" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Date &amp; Time</label>
                        <input type="datetime-local" v-model="tourForm.scheduled_at" required
                            class="block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Notes <span class="text-gray-500 font-normal">(optional)</span></label>
                        <textarea v-model="tourForm.notes" rows="3"
                            class="block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red placeholder-gray-500"
                            placeholder="Any specific questions or preferred times?"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-1">
                        <button type="button" @click="closeTourModal"
                            class="px-4 py-2 text-sm font-medium bg-gray-700 text-gray-300 rounded-md hover:bg-gray-600 transition">
                            Cancel
                        </button>
                        <button type="submit" :disabled="tourForm.processing"
                            class="px-4 py-2 text-sm font-medium bg-brand-red text-white rounded-md hover:bg-red-700 disabled:opacity-50 transition">
                            {{ tourForm.processing ? 'Sending…' : 'Schedule Request' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Image Lightbox -->
    <div v-if="selectedImage" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90" @click="closeImage">
        <button @click="closeImage" class="absolute top-4 right-4 text-white hover:text-gray-300 transition">
            <XCircleIcon class="w-10 h-10" />
        </button>
        <img :src="selectedImage" alt="Property image" class="max-w-[90vw] max-h-[90vh] object-contain" @click.stop />
    </div>

    <ReportListingModal
        :show="showReportModal"
        :property-id="property.id"
        @close="showReportModal = false"
        @submitted="handleReportSubmitted"
    />
</template>
