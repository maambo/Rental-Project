<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';
import {
    MagnifyingGlassIcon,
    AdjustmentsHorizontalIcon,
    XMarkIcon,
    MapIcon,
    Squares2X2Icon,
    ArrowsPointingOutIcon,
    ArrowsPointingInIcon,
} from '@heroicons/vue/24/outline';

interface Image { id: number; image_url: string; is_primary: boolean }
interface Location { id: number; name: string }
interface Property {
    id: number; title: string; price: number; property_type: string;
    property_subtype: string; listing_type: string; bedrooms: number | null;
    bathrooms: number | null; street_address: string;
    province: Location; district: Location; town: Location;
    images: Image[];
}
interface MapProperty {
    id: number; title: string; price: number;
    latitude: number; longitude: number;
    property_type: string; listing_type: string;
}
interface Paginated<T> { data: T[]; links: any[]; total?: number; last_page?: number; meta?: { current_page: number; last_page: number; total: number } }

const props = defineProps<{
    properties: Paginated<Property>;
    mapProperties: MapProperty[];
    provinces: Location[];
    districts: Location[];
    towns: Location[];
    filters: Record<string, string>;
}>();

// ── View toggle ──────────────────────────────────────────────────────────────
const viewMode = ref<'grid' | 'map'>('grid');

// ── Filter state ──────────────────────────────────────────────────────────────
const search       = ref(props.filters.search ?? '');
const provinceId   = ref(props.filters.province_id ?? '');
const districtId   = ref(props.filters.district_id ?? '');
const townId       = ref(props.filters.town_id ?? '');
const propertyType = ref(props.filters.property_type ?? '');
const listingType  = ref(props.filters.listing_type ?? '');
const minPrice     = ref(props.filters.min_price ?? '');
const maxPrice     = ref(props.filters.max_price ?? '');
const bedrooms     = ref(props.filters.bedrooms ?? '');

const districts = ref<Location[]>(props.districts);
const towns     = ref<Location[]>(props.towns);

async function loadDistricts(id: string) {
    if (!id) { districts.value = []; towns.value = []; return; }
    const { data } = await axios.get(`/api/locations/districts/${id}`);
    districts.value = data;
    towns.value = [];
    districtId.value = '';
    townId.value = '';
}
async function loadTowns(id: string) {
    if (!id) { towns.value = []; return; }
    const { data } = await axios.get(`/api/locations/towns/${id}`);
    towns.value = data;
    townId.value = '';
}
watch(provinceId, loadDistricts);
watch(districtId, loadTowns);

function applyFilters() {
    router.get(route('properties.index'), {
        search:        search.value || undefined,
        province_id:   provinceId.value || undefined,
        district_id:   districtId.value || undefined,
        town_id:       townId.value || undefined,
        property_type: propertyType.value || undefined,
        listing_type:  listingType.value || undefined,
        min_price:     minPrice.value || undefined,
        max_price:     maxPrice.value || undefined,
        bedrooms:      bedrooms.value || undefined,
    }, { preserveScroll: true });
}
function clearFilters() {
    search.value = ''; provinceId.value = ''; districtId.value = ''; townId.value = '';
    propertyType.value = ''; listingType.value = ''; minPrice.value = ''; maxPrice.value = '';
    bedrooms.value = '';
    router.get(route('properties.index'));
}
function propertyImage(property: Property) {
    const primary = property.images.find(i => i.is_primary) ?? property.images[0];
    return primary ? `/storage/${primary.image_url}` : null;
}
const hasFilters = () => Object.values(props.filters).some(v => !!v);

// ── Map ───────────────────────────────────────────────────────────────────────
const mapEl        = ref<HTMLDivElement | null>(null);
const isFullscreen = ref(false);
let   leafletMap: any = null;
let   clusterGroup: any = null;

// Colors: residential-rent=blue, residential-sale=orange, commercial-rent=purple, commercial-sale=green
const MARKER_COLORS: Record<string, string> = {
    'residential-rent': '#3B82F6',
    'residential-sale': '#F97316',
    'commercial-rent':  '#A855F7',
    'commercial-sale':  '#22C55E',
};
const LEGEND_ITEMS = [
    { key: 'residential-rent', color: '#3B82F6', label: 'Residential · Rent' },
    { key: 'residential-sale', color: '#F97316', label: 'Residential · Sale' },
    { key: 'commercial-rent',  color: '#A855F7', label: 'Commercial · Rent'  },
    { key: 'commercial-sale',  color: '#22C55E', label: 'Commercial · Sale'  },
];

function markerColor(p: MapProperty) {
    return MARKER_COLORS[`${p.property_type}-${p.listing_type}`] ?? '#6B7280';
}

function makeIcon(L: any, color: string) {
    return L.divIcon({
        className: '',
        html: `<div style="
            width:28px;height:28px;border-radius:50%;
            background:${color};border:3px solid #fff;
            box-shadow:0 2px 6px rgba(0,0,0,.4);
            display:flex;align-items:center;justify-content:center;
        ">
            <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='white' width='13' height='13'>
                <path d='M10 2a6 6 0 0 0-6 6c0 4.418 6 10 6 10s6-5.582 6-10a6 6 0 0 0-6-6Zm0 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4Z'/>
            </svg>
        </div>`,
        iconSize:   [28, 28],
        iconAnchor: [14, 28],
        popupAnchor:[0, -30],
    });
}

async function initMap() {
    if (!mapEl.value) return;

    const L  = await import('leaflet');
    await import('leaflet/dist/leaflet.css');
    // Import the plugin AFTER L so it can extend the same L instance
    await import('leaflet.markercluster');
    await import('leaflet.markercluster/dist/MarkerCluster.css');
    await import('leaflet.markercluster/dist/MarkerCluster.Default.css');

    leafletMap = L.map(mapEl.value, {
        center: [-13.1, 27.8],
        zoom: 6,
        maxBounds: [[-18.1, 21.9], [-8.2, 33.7]],
        maxBoundsViscosity: 0.9,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
        maxZoom: 18,
    }).addTo(leafletMap);

    // The plugin extends L directly — access via L after the import above
    clusterGroup = (L as any).markerClusterGroup({
        maxClusterRadius: 60,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        iconCreateFunction(cluster: any) {
            const count = cluster.getChildCount();
            const size  = count < 10 ? 36 : count < 50 ? 44 : 52;
            return L.divIcon({
                html: `<div style="
                    width:${size}px;height:${size}px;border-radius:50%;
                    background:#DC2626;border:3px solid #fff;
                    box-shadow:0 2px 8px rgba(0,0,0,.35);
                    color:#fff;font-weight:700;font-size:${size < 44 ? 12 : 14}px;
                    display:flex;align-items:center;justify-content:center;
                ">${count}</div>`,
                className: '',
                iconSize: [size, size],
                iconAnchor: [size / 2, size / 2],
            });
        },
    });

    for (const p of props.mapProperties) {
        const color  = markerColor(p);
        const marker = L.marker([Number(p.latitude), Number(p.longitude)], { icon: makeIcon(L, color) });
        marker.bindPopup(`
            <div style="min-width:180px;font-family:sans-serif;">
                <div style="font-weight:700;font-size:14px;margin-bottom:4px;line-height:1.3">${p.title}</div>
                <div style="color:#DC2626;font-weight:700;font-size:15px;margin-bottom:6px">
                    K${Number(p.price).toLocaleString()}${p.listing_type === 'rent' ? '<span style="color:#6B7280;font-weight:400;font-size:12px">/mo</span>' : ''}
                </div>
                <div style="margin-bottom:8px;font-size:12px;color:#6B7280;text-transform:capitalize">
                    ${p.property_type} · ${p.listing_type}
                </div>
                <a href="/properties/${p.id}"
                   style="display:inline-block;padding:5px 12px;background:#DC2626;color:#fff;border-radius:6px;text-decoration:none;font-size:12px;font-weight:600">
                    View Details →
                </a>
            </div>
        `, { maxWidth: 240 });
        clusterGroup.addLayer(marker);
    }

    leafletMap.addLayer(clusterGroup);

    // Fit map to markers so properties are always in view
    const pinned = props.mapProperties.filter(p => p.latitude && p.longitude);
    if (pinned.length === 1) {
        leafletMap.setView([Number(pinned[0].latitude), Number(pinned[0].longitude)], 14);
    } else if (pinned.length > 1) {
        const bounds = L.latLngBounds(pinned.map(p => [Number(p.latitude), Number(p.longitude)]));
        leafletMap.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
    }
}

function destroyMap() {
    leafletMap?.remove();
    leafletMap   = null;
    clusterGroup = null;
}

watch(viewMode, async (mode) => {
    if (mode === 'map') {
        await nextTick();
        initMap();
    } else {
        destroyMap();
    }
});

onUnmounted(destroyMap);

// Fullscreen
function toggleFullscreen() {
    const el = mapEl.value?.closest('.map-wrapper') as HTMLElement | null;
    if (!el) return;
    if (!document.fullscreenElement) {
        el.requestFullscreen().then(() => { isFullscreen.value = true; });
    } else {
        document.exitFullscreen().then(() => { isFullscreen.value = false; });
    }
}
onMounted(() => {
    document.addEventListener('fullscreenchange', () => {
        isFullscreen.value = !!document.fullscreenElement;
        nextTick(() => leafletMap?.invalidateSize());
    });
});
</script>

<template>
    <Head title="Browse Properties" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <Link :href="route('landing')" class="text-xl font-bold text-brand-red">RentalApp</Link>
                <div class="flex items-center gap-3">
                    <Link :href="route('login')" class="text-sm text-gray-600 dark:text-gray-300 hover:text-brand-red">Sign in</Link>
                    <Link :href="route('register')" class="text-sm bg-brand-red text-white px-4 py-2 rounded-md hover:bg-red-700">Register</Link>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Filter Form -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-center gap-2 mb-4">
                    <AdjustmentsHorizontalIcon class="w-5 h-5 text-brand-red" />
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Filter Properties</h2>
                    <button v-if="hasFilters()" @click="clearFilters"
                        class="ml-auto flex items-center gap-1 text-sm text-gray-500 hover:text-red-500">
                        <XMarkIcon class="w-4 h-4" /> Clear filters
                    </button>
                </div>

                <form @submit.prevent="applyFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="sm:col-span-2 lg:col-span-4">
                        <div class="relative">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input v-model="search" type="text" placeholder="Search by title or description…"
                                class="w-full pl-9 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-red" />
                        </div>
                    </div>

                    <!-- Province -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Province</label>
                        <select v-model="provinceId" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-red">
                            <option value="">All Provinces</option>
                            <option v-for="p in provinces" :key="p.id" :value="String(p.id)">{{ p.name }}</option>
                        </select>
                    </div>

                    <!-- District -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">District</label>
                        <select v-model="districtId" :disabled="!districts.length" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-red disabled:opacity-50">
                            <option value="">All Districts</option>
                            <option v-for="d in districts" :key="d.id" :value="String(d.id)">{{ d.name }}</option>
                        </select>
                    </div>

                    <!-- Town -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Town</label>
                        <select v-model="townId" :disabled="!towns.length" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-red disabled:opacity-50">
                            <option value="">All Towns</option>
                            <option v-for="t in towns" :key="t.id" :value="String(t.id)">{{ t.name }}</option>
                        </select>
                    </div>

                    <!-- Property type -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Type</label>
                        <select v-model="propertyType" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-red">
                            <option value="">All Types</option>
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                        </select>
                    </div>

                    <!-- Listing type -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">For</label>
                        <select v-model="listingType" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-red">
                            <option value="">Rent &amp; Sale</option>
                            <option value="rent">Rent</option>
                            <option value="sale">Sale</option>
                        </select>
                    </div>

                    <!-- Bedrooms -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Min Bedrooms</label>
                        <select v-model="bedrooms" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-red">
                            <option value="">Any</option>
                            <option v-for="n in [1,2,3,4,5]" :key="n" :value="String(n)">{{ n }}+</option>
                        </select>
                    </div>

                    <!-- Min price -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Min Price (ZMW)</label>
                        <input v-model="minPrice" type="number" min="0" placeholder="0"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-red" />
                    </div>

                    <!-- Max price -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Max Price (ZMW)</label>
                        <input v-model="maxPrice" type="number" min="0" placeholder="Any"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-brand-red" />
                    </div>

                    <!-- Submit -->
                    <div class="sm:col-span-2 lg:col-span-4 flex justify-end">
                        <button type="submit"
                            class="px-6 py-2 bg-brand-red text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition-colors">
                            Search Properties
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results bar + view toggle -->
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-semibold text-gray-900 dark:text-white">{{ properties.total ?? properties.meta?.total ?? 0 }}</span> properties found
                    <span v-if="viewMode === 'map'" class="ml-1">({{ mapProperties.length }} on map)</span>
                </p>

                <!-- Grid / Map toggle -->
                <div class="flex items-center gap-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-1 shadow-sm">
                    <button @click="viewMode = 'grid'" :class="[
                        'flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-colors',
                        viewMode === 'grid'
                            ? 'bg-brand-red text-white shadow-sm'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                    ]">
                        <Squares2X2Icon class="w-4 h-4" /> Grid
                    </button>
                    <button @click="viewMode = 'map'" :class="[
                        'flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-colors',
                        viewMode === 'map'
                            ? 'bg-brand-red text-white shadow-sm'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                    ]">
                        <MapIcon class="w-4 h-4" /> Map
                    </button>
                </div>
            </div>

            <!-- ── MAP VIEW ── -->
            <div v-if="viewMode === 'map'" class="map-wrapper relative rounded-xl overflow-hidden shadow-lg border border-gray-200 dark:border-gray-700 mb-8" style="height:600px">
                <!-- Map container -->
                <div ref="mapEl" class="w-full h-full z-0"></div>

                <!-- Fullscreen button -->
                <button @click="toggleFullscreen"
                    class="absolute top-3 right-3 z-[1000] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-2 shadow-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    :title="isFullscreen ? 'Exit fullscreen' : 'Fullscreen'">
                    <ArrowsPointingOutIcon v-if="!isFullscreen" class="w-5 h-5 text-gray-700 dark:text-gray-300" />
                    <ArrowsPointingInIcon  v-else                class="w-5 h-5 text-gray-700 dark:text-gray-300" />
                </button>

                <!-- Legend -->
                <div class="absolute bottom-6 left-3 z-[1000] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl p-3 shadow-lg text-xs">
                    <p class="font-semibold text-gray-700 dark:text-gray-200 mb-2 text-xs uppercase tracking-wide">Legend</p>
                    <div class="space-y-1.5">
                        <div v-for="item in LEGEND_ITEMS" :key="item.key" class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded-full flex-shrink-0 border-2 border-white shadow-sm" :style="{ background: item.color }"></span>
                            <span class="text-gray-600 dark:text-gray-300">{{ item.label }}</span>
                        </div>
                        <div class="flex items-center gap-2 pt-1 border-t border-gray-100 dark:border-gray-700 mt-1">
                            <span class="w-5 h-5 rounded-full bg-brand-red flex-shrink-0 flex items-center justify-center border-2 border-white shadow-sm">
                                <span class="text-white font-bold" style="font-size:9px">N</span>
                            </span>
                            <span class="text-gray-600 dark:text-gray-300">Cluster (click to zoom)</span>
                        </div>
                    </div>
                </div>

                <!-- Empty map state -->
                <div v-if="mapProperties.length === 0" class="absolute inset-0 z-[999] flex items-center justify-center bg-white/70 dark:bg-gray-900/70 rounded-xl pointer-events-none">
                    <div class="text-center">
                        <p class="text-4xl mb-3">🗺️</p>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">No properties with coordinates match your filters.</p>
                    </div>
                </div>
            </div>

            <!-- ── GRID VIEW ── -->
            <template v-else>
                <div v-if="properties.data.length" data-testid="properties-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <Link v-for="property in properties.data" :key="property.id"
                        :href="route('properties.show', property.id)"
                        class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow border border-gray-100 dark:border-gray-700">

                        <!-- Image -->
                        <div class="relative h-48 bg-gray-200 dark:bg-gray-700 overflow-hidden">
                            <img v-if="propertyImage(property)" :src="propertyImage(property)!" :alt="property.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            <div v-else class="flex items-center justify-center h-full text-gray-400 text-sm">No image</div>

                            <!-- Badges -->
                            <div class="absolute top-2 left-2 flex gap-1">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold capitalize"
                                    :class="property.property_type === 'residential' ? 'bg-blue-500 text-white' : 'bg-purple-500 text-white'">
                                    {{ property.property_type }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-xs font-semibold capitalize"
                                    :class="property.listing_type === 'rent' ? 'bg-green-500 text-white' : 'bg-orange-500 text-white'">
                                    {{ property.listing_type }}
                                </span>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <h3 class="font-semibold text-gray-900 dark:text-white text-sm leading-tight line-clamp-2">{{ property.title }}</h3>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 capitalize">{{ property.property_subtype }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 flex items-center gap-1">
                                📍 {{ [property.town?.name, property.district?.name, property.province?.name].filter(Boolean).join(', ') }}
                            </p>
                            <div class="flex items-center justify-between">
                                <p class="text-brand-red font-bold text-sm">
                                    K{{ Number(property.price).toLocaleString() }}
                                    <span v-if="property.listing_type === 'rent'" class="text-gray-400 font-normal">/mo</span>
                                </p>
                                <div v-if="property.bedrooms" class="text-xs text-gray-500 dark:text-gray-400">
                                    🛏 {{ property.bedrooms }} · 🚿 {{ property.bathrooms }}
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty state -->
                <div v-else class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl shadow-sm">
                    <p class="text-4xl mb-4">🏠</p>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No properties found</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Try adjusting your filters.</p>
                    <button @click="clearFilters" class="text-sm text-brand-red hover:underline">Clear all filters</button>
                </div>

                <!-- Pagination -->
                <div v-if="(properties.last_page ?? properties.meta?.last_page ?? 1) > 1" class="mt-8 flex justify-center gap-1">
                    <template v-for="link in properties.links" :key="link.label">
                        <Link v-if="link.url" :href="link.url"
                            class="px-3 py-2 text-sm rounded-lg"
                            :class="link.active ? 'bg-brand-red text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600'"
                            v-html="link.label" />
                        <span v-else class="px-3 py-2 text-sm text-gray-400" v-html="link.label" />
                    </template>
                </div>
            </template>
        </main>
    </div>
</template>
