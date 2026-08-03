<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, computed } from 'vue';
import axios from 'axios';
import {
    BuildingOffice2Icon, CurrencyDollarIcon, EyeIcon, HeartIcon,
    TagIcon, HomeModernIcon, ArrowDownTrayIcon, ArrowPathIcon,
    MapPinIcon, BoltIcon,
} from '@heroicons/vue/24/outline';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { BarChart } from 'echarts/charts';
import { GridComponent, TooltipComponent } from 'echarts/components';
import VChart from 'vue-echarts';

use([CanvasRenderer, BarChart, GridComponent, TooltipComponent]);

interface Option { id: number; name: string; }
interface Summary {
    total_properties: number; avg_price: number; total_views: number;
    total_likes: number; for_rent: number; for_sale: number;
}
interface BreakdownRow { label: string; total: number; avg_price?: number; }
interface TypeRow { property_type: string; property_subtype: string | null; total: number; avg_price: number; }

const props = defineProps<{
    filters: Record<string, string | number | undefined>;
    summary: Summary;
    byProvince: BreakdownRow[];
    byDistrict: BreakdownRow[];
    byType: TypeRow[];
    byListing: BreakdownRow[];
    byUtility: BreakdownRow[];
    provinces: Option[];
    utilityTypes: Option[];
    propertyTypes: string[];
    propertySubtypes: string[];
}>();

const form = reactive({
    province_id: props.filters.province_id ?? '',
    district_id: props.filters.district_id ?? '',
    town_id: props.filters.town_id ?? '',
    property_type: props.filters.property_type ?? '',
    property_subtype: props.filters.property_subtype ?? '',
    listing_type: props.filters.listing_type ?? '',
    availability_status: props.filters.availability_status ?? '',
    approval_status: props.filters.approval_status ?? '',
    utility_type_id: props.filters.utility_type_id ?? '',
});

const districts = ref<Option[]>([]);
const towns = ref<Option[]>([]);
const loadingDistricts = ref(false);
const loadingTowns = ref(false);

const onProvinceChange = async () => {
    form.district_id = '';
    form.town_id = '';
    districts.value = [];
    towns.value = [];
    if (!form.province_id) return;
    loadingDistricts.value = true;
    try {
        const { data } = await axios.get(`/api/locations/districts/${form.province_id}`);
        districts.value = data;
    } finally {
        loadingDistricts.value = false;
    }
};

const onDistrictChange = async () => {
    form.town_id = '';
    towns.value = [];
    if (!form.district_id) return;
    loadingTowns.value = true;
    try {
        const { data } = await axios.get(`/api/locations/towns/${form.district_id}`);
        towns.value = data;
    } finally {
        loadingTowns.value = false;
    }
};

// Restore district/town options on load if filters already had a province/district selected
if (form.province_id) onProvinceChange().then(() => { if (props.filters.district_id) { form.district_id = String(props.filters.district_id); onDistrictChange(); } });

const applyFilters = () => {
    const query: Record<string, string> = {};
    Object.entries(form).forEach(([k, v]) => { if (v !== '' && v != null) query[k] = String(v); });
    router.get(route('admin.analytics.properties.index'), query, { preserveState: true, preserveScroll: true });
};

const resetFilters = () => {
    router.get(route('admin.analytics.properties.index'));
};

const exportUrl = (kind: 'csv' | 'pdf') => {
    const query: Record<string, string> = {};
    Object.entries(form).forEach(([k, v]) => { if (v !== '' && v != null) query[k] = String(v); });
    const params = new URLSearchParams(query).toString();
    const base = route(`admin.analytics.properties.export.${kind}`);
    return params ? `${base}?${params}` : base;
};

const fmt = (n: number) => 'K' + Number(n ?? 0).toLocaleString(undefined, { maximumFractionDigits: 2 });
const subtypeLabel = (s: string | null) => s ? s.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase()) : '—';

const provinceChartOption = computed(() => ({
    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
    grid: { left: 8, right: 16, top: 16, bottom: 28, containLabel: true },
    xAxis: { type: 'category', data: props.byProvince.map(r => r.label), axisLine: { lineStyle: { color: '#6b7280' } }, axisLabel: { color: '#9ca3af', rotate: props.byProvince.length > 6 ? 30 : 0 } },
    yAxis: { type: 'value', splitLine: { lineStyle: { color: 'rgba(107,114,128,0.2)' } }, axisLabel: { color: '#9ca3af' } },
    series: [{
        name: 'Properties',
        type: 'bar',
        data: props.byProvince.map(r => r.total),
        itemStyle: { color: '#E21608', borderRadius: [4, 4, 0, 0] },
        barMaxWidth: 40,
    }],
}));
</script>

<template>
    <Head title="Property Analytics" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Property Analytics</h2>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

            <!-- Filters -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Province</label>
                        <select v-model="form.province_id" @change="onProvinceChange" class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red">
                            <option value="">All Provinces</option>
                            <option v-for="p in provinces" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">District</label>
                        <select v-model="form.district_id" @change="onDistrictChange" :disabled="!form.province_id || loadingDistricts"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red disabled:opacity-50">
                            <option value="">All Districts</option>
                            <option v-for="d in districts" :key="d.id" :value="d.id">{{ d.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Town</label>
                        <select v-model="form.town_id" :disabled="!form.district_id || loadingTowns"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red disabled:opacity-50">
                            <option value="">All Towns</option>
                            <option v-for="t in towns" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Property Type</label>
                        <select v-model="form.property_type" class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red">
                            <option value="">All Types</option>
                            <option v-for="t in propertyTypes" :key="t" :value="t">{{ t.charAt(0).toUpperCase() + t.slice(1) }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Subtype</label>
                        <select v-model="form.property_subtype" class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red">
                            <option value="">All Subtypes</option>
                            <option v-for="s in propertySubtypes" :key="s" :value="s">{{ subtypeLabel(s) }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Listing</label>
                        <select v-model="form.listing_type" class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red">
                            <option value="">Rent &amp; Sale</option>
                            <option value="rent">For Rent</option>
                            <option value="sale">For Sale</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Utility</label>
                        <select v-model="form.utility_type_id" class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red">
                            <option value="">Any Utility</option>
                            <option v-for="u in utilityTypes" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Approval Status</label>
                        <select v-model="form.approval_status" class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red">
                            <option value="">Any Status</option>
                            <option value="approved">Approved</option>
                            <option value="pending">Pending</option>
                            <option value="under_review">Under Review</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Availability</label>
                        <select v-model="form.availability_status" class="w-full bg-gray-700 border border-gray-600 rounded-lg text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-red">
                            <option value="">Any</option>
                            <option value="available">Available</option>
                            <option value="rented">Rented</option>
                            <option value="sold">Sold</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 mt-4 pt-4 border-t border-gray-700/60">
                    <button @click="applyFilters" class="bg-brand-red hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        Apply Filters
                    </button>
                    <button @click="resetFilters" class="flex items-center gap-1.5 border border-gray-600 text-gray-300 hover:bg-gray-700 text-sm px-3 py-2 rounded-lg transition-colors">
                        <ArrowPathIcon class="w-4 h-4" /> Reset
                    </button>
                    <div class="flex-1"></div>
                    <a :href="exportUrl('csv')" class="flex items-center gap-1.5 border border-gray-600 text-gray-300 hover:bg-gray-700 text-sm px-3 py-2 rounded-lg transition-colors">
                        <ArrowDownTrayIcon class="w-4 h-4" /> Export CSV
                    </a>
                    <a :href="exportUrl('pdf')" class="flex items-center gap-1.5 border border-gray-600 text-gray-300 hover:bg-gray-700 text-sm px-3 py-2 rounded-lg transition-colors">
                        <ArrowDownTrayIcon class="w-4 h-4" /> Export PDF
                    </a>
                </div>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
                    <BuildingOffice2Icon class="w-5 h-5 text-brand-red mb-2" />
                    <div class="text-xl font-bold text-white">{{ summary.total_properties }}</div>
                    <div class="text-xs text-gray-400">Properties</div>
                </div>
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
                    <CurrencyDollarIcon class="w-5 h-5 text-green-400 mb-2" />
                    <div class="text-xl font-bold text-white">{{ fmt(summary.avg_price) }}</div>
                    <div class="text-xs text-gray-400">Avg Price</div>
                </div>
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
                    <TagIcon class="w-5 h-5 text-blue-400 mb-2" />
                    <div class="text-xl font-bold text-white">{{ summary.for_rent }}</div>
                    <div class="text-xs text-gray-400">For Rent</div>
                </div>
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
                    <HomeModernIcon class="w-5 h-5 text-purple-400 mb-2" />
                    <div class="text-xl font-bold text-white">{{ summary.for_sale }}</div>
                    <div class="text-xs text-gray-400">For Sale</div>
                </div>
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
                    <EyeIcon class="w-5 h-5 text-gray-400 mb-2" />
                    <div class="text-xl font-bold text-white">{{ summary.total_views.toLocaleString() }}</div>
                    <div class="text-xs text-gray-400">Views</div>
                </div>
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
                    <HeartIcon class="w-5 h-5 text-red-400 mb-2" />
                    <div class="text-xl font-bold text-white">{{ summary.total_likes.toLocaleString() }}</div>
                    <div class="text-xs text-gray-400">Likes</div>
                </div>
            </div>

            <!-- Province chart -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
                <div class="flex items-center gap-2 mb-4">
                    <MapPinIcon class="w-4 h-4 text-brand-red" />
                    <h3 class="text-sm font-semibold text-white">Properties by Province</h3>
                </div>
                <div v-if="byProvince.length" class="h-[280px]">
                    <VChart :option="provinceChartOption" autoresize />
                </div>
                <p v-else class="text-center text-gray-500 py-16 text-sm">No properties match the current filters.</p>
            </div>

            <!-- Breakdown tables -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                        <MapPinIcon class="w-4 h-4 text-brand-red" />
                        <h3 class="text-sm font-semibold text-white">By District</h3>
                    </div>
                    <div class="max-h-80 overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead class="sticky top-0 bg-gray-800">
                                <tr class="border-b border-gray-700 text-gray-400 text-left">
                                    <th class="px-4 py-2 font-medium">District</th>
                                    <th class="px-4 py-2 font-medium text-right">Properties</th>
                                    <th class="px-4 py-2 font-medium text-right">Avg Price</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="row in byDistrict" :key="row.label" class="hover:bg-gray-700/40">
                                    <td class="px-4 py-2 text-white">{{ row.label }}</td>
                                    <td class="px-4 py-2 text-right text-gray-300">{{ row.total }}</td>
                                    <td class="px-4 py-2 text-right text-gray-300">{{ fmt(row.avg_price ?? 0) }}</td>
                                </tr>
                                <tr v-if="byDistrict.length === 0">
                                    <td colspan="3" class="px-4 py-8 text-center text-gray-500">No data.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                        <HomeModernIcon class="w-4 h-4 text-brand-red" />
                        <h3 class="text-sm font-semibold text-white">By Property Type</h3>
                    </div>
                    <div class="max-h-80 overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead class="sticky top-0 bg-gray-800">
                                <tr class="border-b border-gray-700 text-gray-400 text-left">
                                    <th class="px-4 py-2 font-medium">Type</th>
                                    <th class="px-4 py-2 font-medium">Subtype</th>
                                    <th class="px-4 py-2 font-medium text-right">Properties</th>
                                    <th class="px-4 py-2 font-medium text-right">Avg Price</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="row in byType" :key="row.property_type + row.property_subtype" class="hover:bg-gray-700/40">
                                    <td class="px-4 py-2 text-white capitalize">{{ row.property_type }}</td>
                                    <td class="px-4 py-2 text-gray-300">{{ subtypeLabel(row.property_subtype) }}</td>
                                    <td class="px-4 py-2 text-right text-gray-300">{{ row.total }}</td>
                                    <td class="px-4 py-2 text-right text-gray-300">{{ fmt(row.avg_price) }}</td>
                                </tr>
                                <tr v-if="byType.length === 0">
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">No data.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                        <TagIcon class="w-4 h-4 text-brand-red" />
                        <h3 class="text-sm font-semibold text-white">By Listing Type</h3>
                    </div>
                    <div class="p-4 grid grid-cols-2 gap-3">
                        <div v-for="row in byListing" :key="row.label" class="bg-dark-bg/60 rounded-lg px-3 py-2.5 text-center">
                            <p class="text-lg font-bold text-white">{{ row.total }}</p>
                            <p class="text-xs text-gray-500 capitalize">For {{ row.label }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ fmt(row.avg_price ?? 0) }} avg</p>
                        </div>
                        <p v-if="byListing.length === 0" class="col-span-2 text-center text-gray-500 py-6 text-sm">No data.</p>
                    </div>
                </div>

                <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                        <BoltIcon class="w-4 h-4 text-brand-red" />
                        <h3 class="text-sm font-semibold text-white">By Utility</h3>
                    </div>
                    <div class="max-h-64 overflow-y-auto p-4 space-y-2">
                        <div v-for="row in byUtility" :key="row.label" class="flex items-center justify-between text-sm">
                            <span class="text-gray-300">{{ row.label }}</span>
                            <span class="text-white font-semibold">{{ row.total }}</span>
                        </div>
                        <p v-if="byUtility.length === 0" class="text-center text-gray-500 py-6 text-sm">No data.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
