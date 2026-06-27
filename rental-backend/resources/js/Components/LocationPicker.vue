<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';
import { MagnifyingGlassIcon, MapPinIcon } from '@heroicons/vue/24/outline';
import type { Map as LeafletMap, Marker } from 'leaflet';

interface Province { id: number; name: string; code: string; }
interface District  { id: number; name: string; }
interface Town      { id: number; name: string; latitude: number; longitude: number; }

interface ModelValue {
    province_id: number | null;
    district_id: number | null;
    town_id: number | null;
    street_address: string;
    latitude: number | null;
    longitude: number | null;
}

const props = defineProps<{ modelValue: ModelValue }>();
const emit = defineEmits<{ 'update:modelValue': [v: ModelValue] }>();

// ── Cascading selects ────────────────────────────────────────────────
const provinces  = ref<Province[]>([]);
const districts  = ref<District[]>([]);
const towns      = ref<Town[]>([]);

const selectedProvince = ref<number | null>(props.modelValue.province_id);
const selectedDistrict = ref<number | null>(props.modelValue.district_id);
const selectedTown     = ref<number | null>(props.modelValue.town_id);
const streetAddress    = ref(props.modelValue.street_address);
const latitude         = ref<number | null>(props.modelValue.latitude);
const longitude        = ref<number | null>(props.modelValue.longitude);

onMounted(async () => {
    const { data } = await axios.get('/api/locations/provinces');
    provinces.value = data;

    if (selectedProvince.value) await loadDistricts(selectedProvince.value);
    if (selectedDistrict.value) await loadTowns(selectedDistrict.value);

    await nextTick();
    initMap();
});

async function loadDistricts(provinceId: number) {
    const { data } = await axios.get(`/api/locations/districts/${provinceId}`);
    districts.value = data;
    towns.value = [];
}

async function loadTowns(districtId: number) {
    const { data } = await axios.get(`/api/locations/towns/${districtId}`);
    towns.value = data;
}

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
    if (id) {
        const town = towns.value.find(t => t.id === id);
        if (town) {
            latitude.value  = town.latitude;
            longitude.value = town.longitude;
            flyTo(town.latitude, town.longitude);
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

// ── Map (Leaflet) ────────────────────────────────────────────────────
const mapEl  = ref<HTMLDivElement | null>(null);
let   map:    LeafletMap | null = null;
let   marker: Marker   | null  = null;

const ZAMBIA_CENTER: [number, number] = [-13.1, 27.8];
const ZAMBIA_BOUNDS: [[number, number], [number, number]] = [[-18.1, 21.9], [-8.2, 33.7]];

async function initMap() {
    if (!mapEl.value) return;
    const L = await import('leaflet');
    await import('leaflet/dist/leaflet.css');

    // Fix default icon paths broken by Vite bundling
    const iconUrl     = new URL('leaflet/dist/images/marker-icon.png',    import.meta.url).href;
    const iconRetinaUrl = new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href;
    const shadowUrl   = new URL('leaflet/dist/images/marker-shadow.png',  import.meta.url).href;
    L.Icon.Default.mergeOptions({ iconUrl, iconRetinaUrl, shadowUrl });

    map = L.map(mapEl.value, {
        center: latitude.value && longitude.value
            ? [latitude.value, longitude.value]
            : ZAMBIA_CENTER,
        zoom: latitude.value ? 14 : 6,
        maxBounds: ZAMBIA_BOUNDS,
        maxBoundsViscosity: 1.0,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
    }).addTo(map);

    if (latitude.value && longitude.value) {
        marker = L.marker([latitude.value, longitude.value], { draggable: true }).addTo(map);
        marker.on('dragend', onMarkerDrag);
    }

    map.on('click', (e) => {
        if (!isInZambia(e.latlng.lat, e.latlng.lng)) return;
        placeMarker(e.latlng.lat, e.latlng.lng);
    });
}

function flyTo(lat: number, lng: number) {
    map?.flyTo([lat, lng], 13);
    placeMarker(lat, lng);
}

async function placeMarker(lat: number, lng: number) {
    if (!map) return;
    const L = await import('leaflet');
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        marker.on('dragend', onMarkerDrag);
    }
    latitude.value  = parseFloat(lat.toFixed(7));
    longitude.value = parseFloat(lng.toFixed(7));
    emitUpdate();
}

function onMarkerDrag(e: any) {
    const ll = e.target.getLatLng();
    if (!isInZambia(ll.lat, ll.lng)) {
        marker?.setLatLng([latitude.value!, longitude.value!]);
        return;
    }
    latitude.value  = parseFloat(ll.lat.toFixed(7));
    longitude.value = parseFloat(ll.lng.toFixed(7));
    emitUpdate();
}

function isInZambia(lat: number, lng: number) {
    return lat >= -18.1 && lat <= -8.2 && lng >= 21.9 && lng <= 33.7;
}

onUnmounted(() => { map?.remove(); });

// ── OSM search ──────────────────────────────────────────────────────
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
    flyTo(lat, lng);
    searchQuery.value   = result.display_name;
    searchResults.value = [];
}
</script>

<template>
    <div class="space-y-4">
        <!-- OSM Search -->
        <div class="relative">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Search location on map
            </label>
            <div class="relative">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Type a place name in Zambia…"
                    class="pl-9 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red text-sm"
                />
                <span v-if="searching" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">Searching…</span>
            </div>
            <ul v-if="searchResults.length" class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-md shadow-lg max-h-52 overflow-y-auto text-sm">
                <li
                    v-for="r in searchResults"
                    :key="r.place_id"
                    @click="pickResult(r)"
                    class="px-3 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 flex items-start gap-2"
                >
                    <MapPinIcon class="w-4 h-4 mt-0.5 shrink-0 text-brand-red" />
                    <span>{{ r.display_name }}</span>
                </li>
            </ul>
        </div>

        <!-- Leaflet Map -->
        <div ref="mapEl" class="w-full h-72 rounded-lg border border-gray-300 dark:border-gray-600 z-0"></div>
        <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
            <MapPinIcon class="w-3 h-3" />
            Click on the map or drag the pin to set the exact property location. Must be within Zambia.
        </p>

        <!-- Lat/Lng read-only display -->
        <div v-if="latitude && longitude" class="flex gap-4 text-xs text-gray-500 dark:text-gray-400">
            <span>Lat: {{ latitude }}</span>
            <span>Lng: {{ longitude }}</span>
        </div>

        <!-- Street Address -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Street Address *
            </label>
            <input
                v-model="streetAddress"
                type="text"
                placeholder="e.g., Plot 12, Cairo Road"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red"
            />
        </div>

        <!-- Cascading Dropdowns -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Province *</label>
                <select
                    v-model="selectedProvince"
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red"
                >
                    <option :value="null" disabled>Select province</option>
                    <option v-for="p in provinces" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">District *</label>
                <select
                    v-model="selectedDistrict"
                    :disabled="!selectedProvince"
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red disabled:opacity-50"
                >
                    <option :value="null" disabled>{{ selectedProvince ? 'Select district' : 'Select province first' }}</option>
                    <option v-for="d in districts" :key="d.id" :value="d.id">{{ d.name }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Town *</label>
                <select
                    v-model="selectedTown"
                    :disabled="!selectedDistrict"
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red disabled:opacity-50"
                >
                    <option :value="null" disabled>{{ selectedDistrict ? 'Select town' : 'Select district first' }}</option>
                    <option v-for="t in towns" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
            </div>
        </div>
    </div>
</template>
