<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    ArrowLeftIcon, CheckIcon, XMarkIcon, XCircleIcon,
    UserIcon, EnvelopeIcon, MapPinIcon, HomeModernIcon,
    CurrencyDollarIcon, TagIcon, CalendarDaysIcon,
    BuildingOffice2Icon, ClockIcon, HashtagIcon,
    PhotoIcon, EyeIcon, HeartIcon, FlagIcon,
    WrenchScrewdriverIcon, SparklesIcon, DocumentTextIcon,
    ShieldCheckIcon, BoltIcon, CheckCircleIcon,
    DocumentArrowDownIcon, PaperClipIcon,
} from '@heroicons/vue/24/outline';
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps<{ property: any }>();

const selectedImage = ref<string | null>(null);
const mapEl = ref<HTMLElement | null>(null);
let mapInstance: any = null;

const openImage = (url: string) => { selectedImage.value = url; };
const closeImage = () => { selectedImage.value = null; };

const availabilityConfig: Record<string, { pill: string; label: string }> = {
    available: { pill: 'bg-green-500/20 text-green-400 ring-1 ring-green-500/30', label: 'Available' },
    rented:    { pill: 'bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30',    label: 'Rented' },
    sold:      { pill: 'bg-gray-500/20 text-gray-400 ring-1 ring-gray-500/30',    label: 'Sold' },
};
const av = () => availabilityConfig[props.property.availability_status] ?? availabilityConfig.available;

const statusConfig: Record<string, { pill: string; dot: string }> = {
    pending:      { pill: 'bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30', dot: 'bg-yellow-400' },
    under_review: { pill: 'bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30',       dot: 'bg-blue-400' },
    approved:     { pill: 'bg-green-500/20 text-green-400 ring-1 ring-green-500/30',     dot: 'bg-green-400' },
    rejected:     { pill: 'bg-red-500/20 text-red-400 ring-1 ring-red-500/30',           dot: 'bg-red-400' },
};
const sc = () => statusConfig[props.property.approval_status] ?? statusConfig.pending;
const fmt = (d: string | null) => d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
const isPending = () => ['pending', 'under_review'].includes(props.property.approval_status);

const amenities = computed<string[]>(() => {
    const a = props.property.amenities;
    if (!a) return [];
    if (Array.isArray(a)) return a;
    try { return JSON.parse(a); } catch { return []; }
});

const approve = () => {
    if (confirm('Approve this property?')) router.post(route('admin.properties.approve', props.property.id));
};
const reject = () => {
    const reason = prompt('Rejection reason:');
    if (reason) router.post(route('admin.properties.reject', props.property.id), { rejection_reason: reason });
};

onMounted(async () => {
    const lat = parseFloat(props.property.latitude);
    const lng = parseFloat(props.property.longitude);
    if (!mapEl.value || isNaN(lat) || isNaN(lng)) return;

    const L = (await import('leaflet')).default;
    await import('leaflet/dist/leaflet.css');

    mapInstance = L.map(mapEl.value, { zoomControl: true, dragging: true, scrollWheelZoom: false }).setView([lat, lng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(mapInstance);

    const icon = L.divIcon({
        className: '',
        html: `<div style="width:14px;height:14px;background:#E21608;border:2px solid #fff;border-radius:50%;box-shadow:0 0 0 4px rgba(226,22,8,0.25)"></div>`,
        iconSize: [14, 14], iconAnchor: [7, 7],
    });
    L.marker([lat, lng], { icon }).addTo(mapInstance).bindPopup(props.property.title);
});

onUnmounted(() => { mapInstance?.remove(); });
</script>

<template>
    <Head title="Property Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.properties.index')"
                    class="p-1.5 rounded-md bg-light-bg hover:bg-gray-700 text-gray-400 hover:text-white transition">
                    <ArrowLeftIcon class="w-4 h-4" />
                </Link>
                <div>
                    <p class="text-xs text-gray-500 font-mono">{{ property.code }}</p>
                    <h2 class="font-semibold text-base text-white leading-tight">{{ property.title }}</h2>
                </div>
            </div>
        </template>

        <div class="py-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                    <!-- ── LEFT COLUMN ── -->
                    <div class="lg:col-span-2 space-y-4">

                        <!-- Overview -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <HomeModernIcon class="w-4 h-4 text-brand-red" />
                                    <h3 class="text-sm font-semibold text-white">Overview</h3>
                                </div>
                                <span :class="[sc().pill, 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize']">
                                    <span :class="[sc().dot, 'h-1.5 w-1.5 rounded-full']"></span>
                                    {{ property.approval_status.replace('_', ' ') }}
                                </span>
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

                                <!-- Description -->
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1.5">Description</p>
                                    <p class="text-sm text-gray-300 leading-relaxed">{{ property.description }}</p>
                                </div>

                                <!-- Terms & Conditions -->
                                <div v-if="property.terms_and_conditions">
                                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1.5">Terms &amp; Conditions</p>
                                    <div class="bg-dark-bg/60 rounded-lg p-3 text-sm text-gray-300 whitespace-pre-line leading-relaxed border border-yellow-500/10">
                                        {{ property.terms_and_conditions }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Property Specs -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <WrenchScrewdriverIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Property Specifications</h3>
                            </div>
                            <div class="p-4 grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Property Type</p>
                                    <p class="text-xs font-semibold text-white capitalize">{{ property.property_type ?? '—' }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Sub-type</p>
                                    <p class="text-xs font-semibold text-white capitalize">{{ property.property_subtype?.replace('_', ' ') ?? '—' }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Listing</p>
                                    <p class="text-xs font-semibold text-white capitalize">For {{ property.listing_type ?? '—' }}</p>
                                </div>
                                <div v-if="property.bedrooms != null && property.bedrooms > 0" class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Bedrooms</p>
                                    <p class="text-xs font-semibold text-white">{{ property.bedrooms }}</p>
                                </div>
                                <div v-if="property.bathrooms != null && property.bathrooms > 0" class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Bathrooms</p>
                                    <p class="text-xs font-semibold text-white">{{ property.bathrooms }}</p>
                                </div>
                                <div v-if="property.square_feet" class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Square Feet</p>
                                    <p class="text-xs font-semibold text-white">{{ property.square_feet }}</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Visible in Search</p>
                                    <p class="text-xs font-semibold" :class="property.is_visible_in_search ? 'text-green-400' : 'text-red-400'">
                                        {{ property.is_visible_in_search ? 'Yes' : 'No' }}
                                    </p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Auto Suspended</p>
                                    <p class="text-xs font-semibold" :class="property.is_auto_suspended ? 'text-red-400' : 'text-green-400'">
                                        {{ property.is_auto_suspended ? 'Yes' : 'No' }}
                                    </p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                                    <p class="text-xs text-gray-500 mb-0.5">Real-time Photo</p>
                                    <p class="text-xs font-semibold" :class="property.requires_real_time_photo ? 'text-yellow-400' : 'text-gray-400'">
                                        {{ property.requires_real_time_photo ? 'Required' : 'Not required' }}
                                    </p>
                                </div>
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
                                <span
                                    v-for="amenity in amenities"
                                    :key="amenity"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-dark-bg/60 ring-1 ring-gray-700/60 text-xs text-gray-300"
                                >
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
                                <div
                                    v-for="utility in property.utilities"
                                    :key="utility.id"
                                    class="bg-dark-bg/60 rounded-lg px-3 py-2.5"
                                >
                                    <p class="text-xs text-gray-500 mb-0.5">{{ utility.name }}</p>
                                    <p class="text-xs font-semibold text-white">
                                        {{ utility.pivot?.utility_option_id ? (utility.options?.find((o: any) => o.id === utility.pivot.utility_option_id)?.label ?? '—') : 'Not specified' }}
                                    </p>
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
                                    <div class="col-span-2 sm:col-span-3 bg-dark-bg/60 rounded-lg px-3 py-2">
                                        <p class="text-xs text-gray-500 mb-0.5">Street Address</p>
                                        <p class="text-xs font-medium text-white">{{ property.street_address ?? property.location ?? '—' }}</p>
                                    </div>
                                </div>
                                <!-- Mini map -->
                                <div v-if="property.latitude && property.longitude"
                                    ref="mapEl"
                                    class="w-full h-52 rounded-lg overflow-hidden ring-1 ring-gray-700/60">
                                </div>
                                <div v-else class="w-full h-24 rounded-lg bg-dark-bg/60 ring-1 ring-gray-700/40 flex flex-col items-center justify-center gap-1">
                                    <MapPinIcon class="w-5 h-5 text-gray-600" />
                                    <p class="text-xs text-gray-500">No coordinates available</p>
                                </div>
                            </div>
                        </div>

                        <!-- Images -->
                        <div v-if="property.images?.length" class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <PhotoIcon class="w-4 h-4 text-brand-red" />
                                    <h3 class="text-sm font-semibold text-white">Images</h3>
                                </div>
                                <span class="text-xs text-gray-500">{{ property.images.length }} photo{{ property.images.length !== 1 ? 's' : '' }}</span>
                            </div>
                            <div class="p-4 grid grid-cols-3 sm:grid-cols-4 gap-2">
                                <div
                                    v-for="(image, index) in property.images"
                                    :key="image.id"
                                    class="relative aspect-video rounded-lg overflow-hidden cursor-pointer group ring-1 ring-gray-700/40"
                                    @click="openImage('/storage/' + image.image_url)"
                                >
                                    <img :src="'/storage/' + image.image_url" :alt="`Photo ${index + 1}`"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200" />
                                    <div v-if="image.is_primary"
                                        class="absolute top-1 left-1 bg-brand-red text-white text-[10px] px-1.5 py-0.5 rounded font-medium">
                                        Primary
                                    </div>
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition flex items-center justify-center">
                                        <span class="text-white text-xs font-medium opacity-0 group-hover:opacity-100 transition">View</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Documents -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <PaperClipIcon class="w-4 h-4 text-brand-red" />
                                    <h3 class="text-sm font-semibold text-white">Documents</h3>
                                </div>
                                <span class="text-xs text-gray-500">{{ property.documents?.length ?? 0 }} file{{ (property.documents?.length ?? 0) !== 1 ? 's' : '' }}</span>
                            </div>
                            <div class="p-4">
                                <div v-if="property.documents?.length" class="space-y-2">
                                    <a
                                        v-for="doc in property.documents"
                                        :key="doc.id"
                                        :href="'/storage/' + doc.file_path"
                                        target="_blank"
                                        class="flex items-center gap-3 px-3 py-2.5 bg-dark-bg/60 rounded-lg hover:bg-dark-bg/80 transition group"
                                    >
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

                        <!-- Applications summary -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <DocumentTextIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Applications</h3>
                                <span class="ml-auto text-xs px-2 py-0.5 rounded-full bg-dark-bg/60 text-gray-400">{{ property.applications?.length ?? 0 }} total</span>
                            </div>
                            <div class="p-4 grid grid-cols-3 gap-2">
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 text-center">
                                    <p class="text-lg font-bold text-white">{{ property.applications?.length ?? 0 }}</p>
                                    <p class="text-xs text-gray-500">Total</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 text-center">
                                    <p class="text-lg font-bold text-yellow-400">
                                        {{ property.applications?.filter((a: any) => a.status === 'pending').length ?? 0 }}
                                    </p>
                                    <p class="text-xs text-gray-500">Pending</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5 text-center">
                                    <p class="text-lg font-bold text-green-400">
                                        {{ property.applications?.filter((a: any) => a.status === 'approved').length ?? 0 }}
                                    </p>
                                    <p class="text-xs text-gray-500">Approved</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── RIGHT COLUMN ── -->
                    <div class="space-y-4">

                        <!-- Actions -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <ShieldCheckIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Review Action</h3>
                            </div>
                            <div class="p-4 space-y-2">
                                <template v-if="isPending()">
                                    <button @click="approve"
                                        class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-500 text-white px-3 py-2.5 rounded-lg text-sm font-medium transition">
                                        <CheckIcon class="w-4 h-4" /> Approve Property
                                    </button>
                                    <button @click="reject"
                                        class="w-full flex items-center justify-center gap-2 bg-red-600/80 hover:bg-red-600 text-white px-3 py-2.5 rounded-lg text-sm font-medium transition">
                                        <XMarkIcon class="w-4 h-4" /> Reject Property
                                    </button>
                                </template>
                                <template v-else>
                                    <div :class="[sc().pill, 'rounded-lg px-3 py-2.5 text-xs text-center font-semibold capitalize']">
                                        {{ property.approval_status.replace('_', ' ') }}
                                    </div>
                                    <button v-if="property.approval_status === 'approved'" @click="reject"
                                        class="w-full flex items-center justify-center gap-1.5 border border-red-500/40 text-red-400 hover:bg-red-500/10 px-3 py-2 rounded-lg text-xs font-medium transition">
                                        <XMarkIcon class="w-3.5 h-3.5" /> Revoke & Reject
                                    </button>
                                    <button v-if="property.approval_status === 'rejected'" @click="approve"
                                        class="w-full flex items-center justify-center gap-1.5 border border-green-500/40 text-green-400 hover:bg-green-500/10 px-3 py-2 rounded-lg text-xs font-medium transition">
                                        <CheckIcon class="w-3.5 h-3.5" /> Approve Instead
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <ClockIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Status & Dates</h3>
                            </div>
                            <div class="p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Approval</span>
                                    <span :class="[sc().pill, 'px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize']">
                                        {{ property.approval_status.replace('_', ' ') }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Availability</span>
                                    <span :class="[av().pill, 'px-2.5 py-0.5 rounded-full text-xs font-semibold']">
                                        {{ av().label }}
                                    </span>
                                </div>
                                <div v-if="property.availability_changed_at" class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500 flex items-center gap-1"><ClockIcon class="w-3.5 h-3.5" />Status set</span>
                                    <span class="text-xs font-medium text-gray-300">{{ fmt(property.availability_changed_at) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500 flex items-center gap-1"><CalendarDaysIcon class="w-3.5 h-3.5" />Submitted</span>
                                    <span class="text-xs font-medium text-gray-300">{{ fmt(property.created_at) }}</span>
                                </div>
                                <div v-if="property.submitted_date" class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500 flex items-center gap-1"><CalendarDaysIcon class="w-3.5 h-3.5" />Review sent</span>
                                    <span class="text-xs font-medium text-gray-300">{{ fmt(property.submitted_date) }}</span>
                                </div>
                                <div v-if="property.approved_date" class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500 flex items-center gap-1"><CheckIcon class="w-3.5 h-3.5" />Approved</span>
                                    <span class="text-xs font-medium text-green-400">{{ fmt(property.approved_date) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500 flex items-center gap-1"><ClockIcon class="w-3.5 h-3.5" />Updated</span>
                                    <span class="text-xs font-medium text-gray-300">{{ fmt(property.updated_at) }}</span>
                                </div>
                                <div v-if="property.rejection_reason" class="mt-1 p-2.5 bg-red-500/10 ring-1 ring-red-500/20 rounded-lg">
                                    <p class="text-xs font-medium text-red-400 mb-0.5">Rejection Reason</p>
                                    <p class="text-xs text-red-300/80">{{ property.rejection_reason }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Engagement stats -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <EyeIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Engagement</h3>
                            </div>
                            <div class="p-4 grid grid-cols-3 gap-2">
                                <div class="bg-dark-bg/60 rounded-lg px-2.5 py-2.5 text-center">
                                    <EyeIcon class="w-4 h-4 text-gray-400 mx-auto mb-1" />
                                    <p class="text-sm font-bold text-white">{{ property.view_count ?? 0 }}</p>
                                    <p class="text-xs text-gray-500">Views</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-2.5 py-2.5 text-center">
                                    <HeartIcon class="w-4 h-4 text-red-400 mx-auto mb-1" />
                                    <p class="text-sm font-bold text-white">{{ property.like_count ?? 0 }}</p>
                                    <p class="text-xs text-gray-500">Likes</p>
                                </div>
                                <div class="bg-dark-bg/60 rounded-lg px-2.5 py-2.5 text-center">
                                    <FlagIcon class="w-4 h-4 text-yellow-400 mx-auto mb-1" />
                                    <p class="text-sm font-bold text-white">{{ property.report_count ?? 0 }}</p>
                                    <p class="text-xs text-gray-500">Reports</p>
                                </div>
                            </div>
                        </div>

                        <!-- Landlord -->
                        <div class="bg-light-bg rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                                <UserIcon class="w-4 h-4 text-brand-red" />
                                <h3 class="text-sm font-semibold text-white">Landlord</h3>
                            </div>
                            <div class="p-4">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="h-9 w-9 rounded-full bg-dark-bg/80 ring-1 ring-gray-700 flex items-center justify-center flex-shrink-0">
                                        <UserIcon class="h-4 w-4 text-gray-400" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-white">{{ property.landlord?.name ?? '—' }}</p>
                                        <p class="text-xs text-gray-500 flex items-center gap-1">
                                            <EnvelopeIcon class="w-3 h-3" />
                                            {{ property.landlord?.email ?? '—' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 pt-3 border-t border-gray-700/60">
                                    <div class="bg-dark-bg/60 rounded-lg px-2.5 py-2">
                                        <p class="text-xs text-gray-500 flex items-center gap-1 mb-0.5">
                                            <HashtagIcon class="w-3 h-3" /> Property ID
                                        </p>
                                        <p class="text-xs font-mono font-medium text-white">#{{ property.id }}</p>
                                    </div>
                                    <div class="bg-dark-bg/60 rounded-lg px-2.5 py-2">
                                        <p class="text-xs text-gray-500 flex items-center gap-1 mb-0.5">
                                            <CalendarDaysIcon class="w-3 h-3" /> Listed
                                        </p>
                                        <p class="text-xs font-medium text-white">{{ fmt(property.created_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Lightbox -->
    <div v-if="selectedImage" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90" @click="closeImage">
        <button @click="closeImage" class="absolute top-4 right-4 text-white hover:text-gray-300 transition">
            <XCircleIcon class="w-10 h-10" />
        </button>
        <img :src="selectedImage" alt="Property image" class="max-w-[90vw] max-h-[90vh] object-contain" @click.stop />
    </div>
</template>
