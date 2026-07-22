<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';
import { MagnifyingGlassIcon, MapPinIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';
import type { Map as LeafletMap, Marker } from 'leaflet';

interface Province { id: number; name: string; code: string; }
interface District  { id: number; name: string; }
interface Town      { id: number; name: string; latitude: number; longitude: number; }

interface ModelValue {
    province_id:    number | null;
    district_id:    number | null;
    town_id:        number | null;
    street_address: string;
    latitude:       number | null;
    longitude:      number | null;
}

const props = defineProps<{ modelValue: ModelValue }>();
const emit  = defineEmits<{ 'update:modelValue': [v: ModelValue] }>();

// ── State ────────────────────────────────────────────────────────────
const provinces  = ref<Province[]>([]);
const districts  = ref<District[]>([]);
const towns      = ref<Town[]>([]);

const selectedProvince = ref<number | null>(props.modelValue.province_id);
const selectedDistrict = ref<number | null>(props.modelValue.district_id);
const selectedTown     = ref<number | null>(props.modelValue.town_id);
const streetAddress    = ref(props.modelValue.street_address ?? '');
const latitude         = ref<number | null>(props.modelValue.latitude);
const longitude        = ref<number | null>(props.modelValue.longitude);

const geocoding = ref(false); // true while reverse-geocoding

// ── Bootstrap ────────────────────────────────────────────────────────
onMounted(async () => {
    const { data } = await axios.get('/api/locations/provinces');
    provinces.value = data;
    if (selectedProvince.value) await loadDistricts(selectedProvince.value);
    if (selectedDistrict.value) await loadTowns(selectedDistrict.value);
    await nextTick();
    initMap();
});

// ── Cascading loaders ────────────────────────────────────────────────
async function loadDistricts(provinceId: number) {
    const { data } = await axios.get(`/api/locations/districts/${provinceId}`);
    districts.value = data;
    towns.value = [];
}

async function loadTowns(districtId: number) {
    const { data } = await axios.get(`/api/locations/towns/${districtId}`);
    towns.value = data;
}

// ── Watchers for manual dropdown changes ─────────────────────────────
watch(selectedProvince, async (id) => {
    selectedDistrict.value = null;
    selectedTown.value = null;
    districts.value = [];
    towns.value = [];
    if (id) await loadDistricts(id);
    emitUpdate();
});

watch(selectedDistrict, async (id) => {
    selectedTown.value = null;
    towns.value = [];
    if (id) await loadTowns(id);
    emitUpdate();
});

watch(selectedTown, (id) => {
    // When user manually picks a town, fly to it (unless geocode already set coords)
    if (id) {
        const town = towns.value.find(t => t.id === id);
        if (town) {
            const lat = parseFloat(town.latitude as any);
            const lng = parseFloat(town.longitude as any);
            latitude.value  = lat;
            longitude.value = lng;
            flyTo(lat, lng);
        }
    }
    emitUpdate();
});

watch([streetAddress, latitude, longitude], () => emitUpdate());

function emitUpdate() {
    emit('update:modelValue', {
        province_id:    selectedProvince.value,
        district_id:    selectedDistrict.value,
        town_id:        selectedTown.value,
        street_address: streetAddress.value,
        latitude:       latitude.value,
        longitude:      longitude.value,
    });
}

// ── Reverse geocoding ────────────────────────────────────────────────
async function reverseGeocode(lat: number, lng: number) {
    geocoding.value = true;
    try {
        const { data } = await axios.get('/api/locations/reverse', { params: { lat, lng } });

        // Province
        if (data.province_id && data.province_id !== selectedProvince.value) {
            selectedProvince.value = data.province_id;
            await loadDistricts(data.province_id);
        }

        // District
        if (data.district_id && data.district_id !== selectedDistrict.value) {
            selectedDistrict.value = data.district_id;
            await loadTowns(data.district_id);
        }

        // Town
        if (data.town_id) selectedTown.value = data.town_id;

        // Street — only pre-fill if the field is empty or we got something specific
        if (data.street_address) streetAddress.value = data.street_address;

        emitUpdate();
    } catch {
        // Silently ignore — user can fill dropdowns manually
    } finally {
        geocoding.value = false;
    }
}

// ── Map (Leaflet) ────────────────────────────────────────────────────
const mapEl = ref<HTMLDivElement | null>(null);
let map:    LeafletMap | null = null;
let marker: Marker     | null = null;

const ZAMBIA_CENTER: [number, number] = [-13.1, 27.8];
const ZAMBIA_BOUNDS: [[number, number], [number, number]] = [[-18.1, 21.9], [-8.2, 33.7]];

async function initMap() {
    if (!mapEl.value) return;
    const L = await import('leaflet');
    await import('leaflet/dist/leaflet.css');

    map = L.map(mapEl.value, {
        center: latitude.value && longitude.value
            ? [latitude.value, longitude.value]
            : ZAMBIA_CENTER,
        zoom: latitude.value ? 15 : 6,
        maxBounds: ZAMBIA_BOUNDS,
        maxBoundsViscosity: 1.0,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    if (latitude.value && longitude.value) {
        marker = L.marker([latitude.value, longitude.value], { icon: pinIcon(L), draggable: true }).addTo(map);
        marker.on('dragend', onMarkerDrag);
    }

    map.on('click', (e) => {
        if (!isInZambia(e.latlng.lat, e.latlng.lng)) return;
        placeMarker(e.latlng.lat, e.latlng.lng, true);
    });
}

function flyTo(lat: number, lng: number) {
    map?.flyTo([lat, lng], 15);
    placeMarker(lat, lng, false); // don't re-geocode when we fly due to a dropdown pick
}

function pinIcon(L: any) {
    return L.divIcon({
        className: '',
        html: `<div style="
            width:18px;height:18px;
            background:#E21608;
            border:2.5px solid #fff;
            border-radius:50%;
            box-shadow:0 0 0 4px rgba(226,22,8,0.3);
            cursor:grab;
        "></div>`,
        iconSize: [18, 18],
        iconAnchor: [9, 9],
    });
}

async function placeMarker(lat: number, lng: number, geocode: boolean) {
    if (!map) return;
    const L = await import('leaflet');
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng], { icon: pinIcon(L), draggable: true }).addTo(map);
        marker.on('dragend', onMarkerDrag);
    }
    latitude.value  = parseFloat(lat.toFixed(7));
    longitude.value = parseFloat(lng.toFixed(7));
    emitUpdate();

    if (geocode) await reverseGeocode(lat, lng);
}

async function onMarkerDrag(e: any) {
    const ll = e.target.getLatLng();
    if (!isInZambia(ll.lat, ll.lng)) {
        marker?.setLatLng([latitude.value!, longitude.value!]);
        return;
    }
    latitude.value  = parseFloat(ll.lat.toFixed(7));
    longitude.value = parseFloat(ll.lng.toFixed(7));
    emitUpdate();
    await reverseGeocode(ll.lat, ll.lng);
}

function isInZambia(lat: number, lng: number) {
    return lat >= -18.1 && lat <= -8.2 && lng >= 21.9 && lng <= 33.7;
}

onUnmounted(() => { map?.remove(); });

// ── OSM forward search ───────────────────────────────────────────────
const searchQuery   = ref('');
const searchResults = ref<any[]>([]);
const searching     = ref(false);
let   searchTimer: ReturnType<typeof setTimeout> | null = null;

watch(searchQuery, (q) => {
    if (searchTimer) clearTimeout(searchTimer);
    if (!q.trim()) { searchResults.value = []; return; }
    searchTimer = setTimeout(() => doSearch(q), 400);
});

async function doSearch(q: string) {
    searching.value = true;
    try {
        const { data } = await axios.get('/api/locations/search', { params: { q } });
        searchResults.value = data;
    } finally {
        searching.value = false;
    }
}

function pickResult(result: any) {
    const lat = parseFloat(result.lat);
    const lng = parseFloat(result.lon);
    searchQuery.value   = result.display_name;
    searchResults.value = [];
    map?.flyTo([lat, lng], 15);
    placeMarker(lat, lng, true);
}
</script>

<template>
    <div class="space-y-4">

        <!-- OSM Search -->
        <div class="relative">
            <label class="block text-sm font-medium text-gray-300 mb-1">Search location</label>
            <div class="relative">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Type a place name in Zambia…"
                    class="pl-9 block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red text-sm placeholder-gray-500"
                />
                <span v-if="searching" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-500">Searching…</span>
            </div>
            <ul v-if="searchResults.length"
                class="absolute z-50 mt-1 w-full bg-light-bg border border-gray-700/60 rounded-md shadow-xl max-h-52 overflow-y-auto text-sm">
                <li
                    v-for="r in searchResults"
                    :key="r.place_id"
                    @click="pickResult(r)"
                    class="px-3 py-2 cursor-pointer hover:bg-dark-bg/60 text-gray-300 flex items-start gap-2"
                >
                    <MapPinIcon class="w-4 h-4 mt-0.5 shrink-0 text-brand-red" />
                    <span class="line-clamp-2">{{ r.display_name }}</span>
                </li>
            </ul>
        </div>

        <!-- Map -->
        <div class="relative">
            <div ref="mapEl" class="w-full h-72 rounded-lg border border-gray-700/60 z-0 overflow-hidden"></div>

            <!-- Geocoding overlay -->
            <div v-if="geocoding"
                class="absolute inset-0 bg-dark-bg/60 rounded-lg flex items-center justify-center z-10 backdrop-blur-sm">
                <div class="flex items-center gap-2 bg-light-bg px-3 py-2 rounded-lg shadow text-sm text-gray-300">
                    <ArrowPathIcon class="w-4 h-4 animate-spin text-brand-red" />
                    Detecting location…
                </div>
            </div>
        </div>

        <p class="text-xs text-gray-500 flex items-center gap-1">
            <MapPinIcon class="w-3 h-3 shrink-0" />
            Click the map or drag the pin — province, district, town &amp; street will be filled automatically.
        </p>

        <!-- Lat/Lng read-only badge -->
        <div v-if="latitude && longitude"
            class="inline-flex items-center gap-2 text-xs text-gray-500 bg-dark-bg/60 px-3 py-1.5 rounded-full ring-1 ring-gray-700/40">
            <MapPinIcon class="w-3 h-3 text-brand-red" />
            {{ latitude.toFixed(5) }}, {{ longitude.toFixed(5) }}
        </div>

        <!-- Street Address -->
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Street Address *</label>
            <input
                v-model="streetAddress"
                type="text"
                placeholder="e.g., Plot 12, Cairo Road"
                class="block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red placeholder-gray-500"
            />
        </div>

        <!-- Cascading Dropdowns -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Province *</label>
                <select v-model="selectedProvince"
                    class="block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red">
                    <option :value="null" disabled>Select province</option>
                    <option v-for="p in provinces" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">District *</label>
                <select v-model="selectedDistrict" :disabled="!selectedProvince"
                    class="block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red disabled:opacity-50">
                    <option :value="null" disabled>{{ selectedProvince ? 'Select district' : 'Select province first' }}</option>
                    <option v-for="d in districts" :key="d.id" :value="d.id">{{ d.name }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Town *</label>
                <select v-model="selectedTown" :disabled="!selectedDistrict"
                    class="block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red disabled:opacity-50">
                    <option :value="null" disabled>{{ selectedDistrict ? 'Select town' : 'Select district first' }}</option>
                    <option v-for="t in towns" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
            </div>
        </div>

    </div>
</template>
