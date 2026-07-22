<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { MapPinIcon, ArrowsPointingOutIcon, ArrowsPointingInIcon } from '@heroicons/vue/24/outline';

export interface MapProperty {
    id: number;
    title: string;
    price: number | string;
    listing_type: string;
    property_type?: string;
    availability_status: string;
    approval_status: string;
    latitude: number | null;
    longitude: number | null;
    street_address?: string;
    detailUrl: string;
}

const props = defineProps<{
    properties: MapProperty[];
    height?: string;
    zoom?: number;
}>();

const wrapper      = ref<HTMLElement | null>(null);
const mapContainer = ref<HTMLElement | null>(null);
const isFullscreen = ref(false);
let leafletMap: any = null;

const pinColors: Record<string, string> = {
    approved:     '#22c55e',
    under_review: '#3b82f6',
    pending:      '#eab308',
    rejected:     '#ef4444',
};

const availBadge = (status: string) => {
    if (status === 'available') return '<span style="background:#22c55e20;color:#4ade80;border:1px solid #22c55e40;padding:1px 7px;border-radius:9999px;font-size:10px;font-weight:600;">Available</span>';
    if (status === 'rented')    return '<span style="background:#3b82f620;color:#60a5fa;border:1px solid #3b82f640;padding:1px 7px;border-radius:9999px;font-size:10px;font-weight:600;">Rented</span>';
    return '<span style="background:#6b728020;color:#9ca3af;border:1px solid #6b728040;padding:1px 7px;border-radius:9999px;font-size:10px;font-weight:600;">Sold</span>';
};

const initMap = async (el: HTMLElement) => {
    if (leafletMap) return;

    const L = (await import('leaflet')).default;
    await import('leaflet/dist/leaflet.css');
    const { MarkerClusterGroup } = await import('leaflet.markercluster');
    await import('leaflet.markercluster/dist/MarkerCluster.css');
    await import('leaflet.markercluster/dist/MarkerCluster.Default.css');

    const pinned = props.properties.filter(p => p.latitude && p.longitude);

    leafletMap = L.map(el, {
        scrollWheelZoom: true,
        zoomControl: true,
        // Temporary view — overridden by fitBounds below (or setView for empty/single)
        center: [-15.4167, 28.2833],
        zoom: 6,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(leafletMap);

    // ── Cluster group ────────────────────────────────────────────────────
    const cluster = (L as any).markerClusterGroup({
        showCoverageOnHover: false,
        maxClusterRadius: 50,
        iconCreateFunction: (c: any) => {
            const count = c.getChildCount();
            return L.divIcon({
                className: '',
                html: `<div style="
                    width:38px;height:38px;
                    background:#E21608;
                    border:2px solid #fff;
                    border-radius:50%;
                    box-shadow:0 0 0 4px rgba(226,22,8,0.25);
                    display:flex;align-items:center;justify-content:center;
                    color:#fff;font-size:12px;font-weight:700;
                ">${count}</div>`,
                iconSize: [38, 38],
                iconAnchor: [19, 19],
            });
        },
    });

    // ── Individual markers ───────────────────────────────────────────────
    pinned.forEach((property) => {
        const unavailable = property.availability_status === 'rented' || property.availability_status === 'sold';
        const colorKey = unavailable ? 'rejected' : (property.approval_status ?? 'pending');
        const color = pinColors[colorKey] ?? '#E21608';

        const icon = L.divIcon({
            className: '',
            html: `<div style="
                width:16px;height:16px;
                background:${color};
                border:2px solid #fff;
                border-radius:50%;
                box-shadow:0 0 0 4px ${color}40;
                cursor:pointer;
            "></div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8],
        });

        const popup = L.popup({
            className: 'pmap-popup',
            maxWidth: 230,
            offset: [0, -4],
        }).setContent(`
            <div style="background:#1F2937;border-radius:10px;overflow:hidden;min-width:190px;">
                <div style="padding:11px 13px;">
                    <p style="color:#fff;font-size:13px;font-weight:600;margin:0 0 3px;line-height:1.3;">${property.title}</p>
                    <p style="color:#E21608;font-size:15px;font-weight:700;margin:0 0 7px;">
                        K${Number(property.price).toLocaleString()}
                        <span style="color:#6b7280;font-size:11px;font-weight:400;">/${property.listing_type}</span>
                    </p>
                    <div style="margin-bottom:9px;">${availBadge(property.availability_status)}</div>
                    <a href="${property.detailUrl}"
                       style="display:block;text-align:center;background:#E21608;color:#fff;padding:6px 0;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;">
                        View Details →
                    </a>
                </div>
            </div>
        `);

        L.marker([Number(property.latitude), Number(property.longitude)], { icon })
            .bindPopup(popup)
            .addTo(cluster);
    });

    leafletMap.addLayer(cluster);

    // Auto-fit to markers so the map always shows properties regardless of zoom/center
    if (pinned.length === 1) {
        leafletMap.setView([Number(pinned[0].latitude), Number(pinned[0].longitude)], props.zoom ?? 14);
    } else if (pinned.length > 1) {
        const bounds = L.latLngBounds(pinned.map(p => [Number(p.latitude), Number(p.longitude)]));
        leafletMap.fitBounds(bounds, { padding: [40, 40], maxZoom: props.zoom ?? 15 });
    }
};

// ── Fullscreen toggle ────────────────────────────────────────────────────
const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value;
    // Let Vue re-paint, then force Leaflet to recalculate its size
    nextTick(() => { leafletMap?.invalidateSize(); });
};

// Escape key closes fullscreen
const onKeyDown = (e: KeyboardEvent) => { if (e.key === 'Escape' && isFullscreen.value) isFullscreen.value = false; };

watch(mapContainer, async (el) => {
    if (el) await nextTick().then(() => initMap(el));
});

onMounted(async () => {
    window.addEventListener('keydown', onKeyDown);
    if (mapContainer.value) await initMap(mapContainer.value);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeyDown);
    leafletMap?.remove();
    leafletMap = null;
});
</script>

<template>
    <!-- Wrapper handles the fullscreen overlay -->
    <div
        ref="wrapper"
        :class="[
            isFullscreen
                ? 'fixed inset-0 z-[9999] bg-dark-bg flex flex-col p-0'
                : 'relative',
        ]"
    >
        <!-- Fullscreen toggle button -->
        <button
            @click="toggleFullscreen"
            :title="isFullscreen ? 'Exit fullscreen (Esc)' : 'Fullscreen'"
            class="absolute top-3 right-3 z-[1000] flex items-center justify-center w-8 h-8 rounded-md bg-gray-900/80 border border-gray-700/60 text-gray-300 hover:text-white hover:bg-gray-800 transition backdrop-blur-sm"
        >
            <ArrowsPointingInIcon v-if="isFullscreen" class="w-4 h-4" />
            <ArrowsPointingOutIcon v-else class="w-4 h-4" />
        </button>

        <!-- Map or empty state -->
        <div v-if="properties.filter(p => p.latitude && p.longitude).length"
            ref="mapContainer"
            :class="[
                'w-full rounded-lg overflow-hidden ring-1 ring-gray-700/60 z-0',
                isFullscreen ? 'flex-1 rounded-none ring-0' : (height ?? 'h-80'),
            ]">
        </div>
        <div v-else
            :class="[
                'w-full rounded-lg bg-dark-bg/60 ring-1 ring-gray-700/40 flex flex-col items-center justify-center gap-2',
                isFullscreen ? 'flex-1' : (height ?? 'h-80'),
            ]">
            <MapPinIcon class="w-8 h-8 text-gray-600" />
            <p class="text-sm text-gray-500">No properties with location data</p>
        </div>
    </div>
</template>

<style>
/* Dark popup chrome */
.pmap-popup .leaflet-popup-content-wrapper {
    background: transparent;
    border: none;
    box-shadow: 0 8px 32px rgba(0,0,0,0.65);
    border-radius: 10px;
    padding: 0;
}
.pmap-popup .leaflet-popup-content { margin: 0; }
.pmap-popup .leaflet-popup-tip     { background: #1F2937; }
.pmap-popup .leaflet-popup-close-button {
    color: #9ca3af !important;
    top: 6px !important;
    right: 8px !important;
}
/* Cluster icon reset so our custom html renders cleanly */
.leaflet-cluster-anim .leaflet-marker-icon,
.leaflet-cluster-anim .leaflet-marker-shadow {
    transition: transform 0.3s ease-out, opacity 0.3s ease-in;
}
</style>
