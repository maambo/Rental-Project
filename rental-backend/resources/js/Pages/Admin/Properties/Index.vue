<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { MagnifyingGlassIcon, XMarkIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline';

type ApprovalStatus = 'pending' | 'approved' | 'rejected';

interface Property {
    id: number;
    code: string;
    title: string;
    landlord_name: string;
    price: number;
    approval_status: ApprovalStatus;
    availability_status: string;
    property_type: string;
    listing_type: string;
    province: string | null;
    district: string | null;
    town: string | null;
    created_at: string;
}

interface Province {
    id: number;
    name: string;
}

const props = defineProps<{
    properties: Array<Property>;
    stats: any;
    currentStatus: string;
    provinces: Array<Province>;
    filters: {
        search: string | null;
        provinceId: string | null;
        propertyType: string | null;
        listingType: string | null;
    };
}>();

const statusColors: Record<ApprovalStatus, string> = {
    pending:  'bg-yellow-500/20 text-yellow-400 ring-1 ring-yellow-500/30',
    approved: 'bg-green-500/20 text-green-400 ring-1 ring-green-500/30',
    rejected: 'bg-red-500/20 text-red-400 ring-1 ring-red-500/30',
};

const availabilityColors: Record<string, string> = {
    available: 'bg-green-500/20 text-green-400 ring-1 ring-green-500/30',
    rented:    'bg-blue-500/20 text-blue-400 ring-1 ring-blue-500/30',
    sold:      'bg-gray-500/20 text-gray-400 ring-1 ring-gray-500/30',
};

const tabs = [
    { key: 'pending',  label: 'Pending' },
    { key: 'approved', label: 'Approved' },
    { key: 'rejected', label: 'Rejected' },
    { key: 'all',      label: 'All' },
];

const search       = ref(props.filters.search ?? '');
const provinceId   = ref(props.filters.provinceId ?? '');
const propertyType = ref(props.filters.propertyType ?? '');
const listingType  = ref(props.filters.listingType ?? '');

function applyFilters(status?: string) {
    router.get(route('admin.properties.index'), {
        status:        status ?? props.currentStatus,
        search:        search.value || undefined,
        province_id:   provinceId.value || undefined,
        property_type: propertyType.value || undefined,
        listing_type:  listingType.value || undefined,
    }, { preserveState: true, replace: true });
}

function clearFilters() {
    search.value       = '';
    provinceId.value   = '';
    propertyType.value = '';
    listingType.value  = '';
    applyFilters();
}

function setStatus(status: string) {
    applyFilters(status);
}

const hasActiveFilters = () =>
    !!(search.value || provinceId.value || propertyType.value || listingType.value);
</script>

<template>
    <Head title="Approve Properties" />

    <AuthenticatedLayout header="Property Approvals">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-light-bg rounded-xl p-4">
                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                            <p class="text-xs text-gray-500 mb-0.5">Total</p>
                            <p class="text-2xl font-bold text-white">{{ stats.total }}</p>
                        </div>
                    </div>
                    <div class="bg-light-bg rounded-xl p-4">
                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                            <p class="text-xs text-gray-500 mb-0.5">Pending</p>
                            <p class="text-2xl font-bold text-yellow-400">{{ stats.pending }}</p>
                        </div>
                    </div>
                    <div class="bg-light-bg rounded-xl p-4">
                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                            <p class="text-xs text-gray-500 mb-0.5">Approved</p>
                            <p class="text-2xl font-bold text-green-400">{{ stats.approved }}</p>
                        </div>
                    </div>
                    <div class="bg-light-bg rounded-xl p-4">
                        <div class="bg-dark-bg/60 rounded-lg px-3 py-2.5">
                            <p class="text-xs text-gray-500 mb-0.5">Rejected</p>
                            <p class="text-2xl font-bold text-red-400">{{ stats.rejected }}</p>
                        </div>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="bg-light-bg rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                        <MagnifyingGlassIcon class="w-4 h-4 text-brand-red" />
                        <h3 class="text-sm font-semibold text-white">Filters</h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                            <!-- Search -->
                            <div class="relative lg:col-span-2">
                                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search title, code or landlord…"
                                    class="w-full pl-9 pr-3 py-2 text-sm border border-gray-700 rounded-md bg-dark-bg text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-red placeholder-gray-500"
                                    @keydown.enter="applyFilters()"
                                />
                            </div>

                            <!-- Province -->
                            <select
                                v-model="provinceId"
                                class="py-2 px-3 text-sm border border-gray-700 rounded-md bg-dark-bg text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-red"
                                @change="applyFilters()"
                            >
                                <option value="">All Provinces</option>
                                <option v-for="p in provinces" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>

                            <!-- Property Type -->
                            <select
                                v-model="propertyType"
                                class="py-2 px-3 text-sm border border-gray-700 rounded-md bg-dark-bg text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-red"
                                @change="applyFilters()"
                            >
                                <option value="">All Types</option>
                                <option value="residential">Residential</option>
                                <option value="commercial">Commercial</option>
                            </select>

                            <!-- Listing Type -->
                            <select
                                v-model="listingType"
                                class="py-2 px-3 text-sm border border-gray-700 rounded-md bg-dark-bg text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-red"
                                @change="applyFilters()"
                            >
                                <option value="">Rent &amp; Sale</option>
                                <option value="rent">For Rent</option>
                                <option value="sale">For Sale</option>
                            </select>
                        </div>

                        <!-- Active filter chips + clear -->
                        <div v-if="hasActiveFilters()" class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="text-xs text-gray-500">Active filters:</span>
                            <span v-if="search" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-dark-bg/60 ring-1 ring-gray-700/40 text-xs text-gray-300">
                                "{{ search }}"
                                <button @click="search = ''; applyFilters()"><XMarkIcon class="h-3 w-3" /></button>
                            </span>
                            <span v-if="provinceId" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-dark-bg/60 ring-1 ring-gray-700/40 text-xs text-gray-300">
                                {{ provinces.find(p => p.id == Number(provinceId))?.name }}
                                <button @click="provinceId = ''; applyFilters()"><XMarkIcon class="h-3 w-3" /></button>
                            </span>
                            <span v-if="propertyType" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-dark-bg/60 ring-1 ring-gray-700/40 text-xs text-gray-300">
                                {{ propertyType }}
                                <button @click="propertyType = ''; applyFilters()"><XMarkIcon class="h-3 w-3" /></button>
                            </span>
                            <span v-if="listingType" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-dark-bg/60 ring-1 ring-gray-700/40 text-xs text-gray-300">
                                {{ listingType }}
                                <button @click="listingType = ''; applyFilters()"><XMarkIcon class="h-3 w-3" /></button>
                            </span>
                            <button @click="clearFilters" class="ml-auto text-xs text-brand-red hover:underline">Clear all</button>
                        </div>
                    </div>
                </div>

                <!-- Properties Table -->
                <div class="bg-light-bg rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                        <BuildingOfficeIcon class="w-4 h-4 text-brand-red" />
                        <h3 class="text-sm font-semibold text-white">Properties</h3>
                        <!-- Status Tabs -->
                        <nav class="ml-4 flex gap-1">
                            <button
                                v-for="tab in tabs"
                                :key="tab.key"
                                @click="setStatus(tab.key)"
                                :class="[
                                    'px-3 py-1 text-xs font-medium rounded-md transition-colors',
                                    currentStatus === tab.key
                                        ? 'bg-brand-red/20 text-brand-red'
                                        : 'text-gray-400 hover:text-gray-200 hover:bg-dark-bg/40'
                                ]"
                            >
                                {{ tab.label }}
                                <span v-if="tab.key === 'pending' && stats.pending > 0"
                                    class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs rounded-full bg-yellow-500/20 text-yellow-400">
                                    {{ stats.pending }}
                                </span>
                            </button>
                        </nav>
                        <span class="ml-auto text-xs text-gray-500">{{ properties.length }} results</span>
                    </div>

                    <div v-if="properties.length === 0" class="py-12 text-center text-gray-500 text-sm">
                        No properties found.
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700/50">
                            <thead class="bg-dark-bg/60">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Code</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Title</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Landlord</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Location</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Price</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Availability</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Submitted</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/50">
                                <tr v-for="property in properties" :key="property.id"
                                    class="hover:bg-dark-bg/40 transition-colors">
                                    <td class="px-4 py-3 text-xs font-mono text-gray-500">{{ property.code }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-white max-w-[180px] truncate">
                                        {{ property.title }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-400">{{ property.landlord_name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-400">
                                        <span v-if="property.town">{{ property.town }}, </span>
                                        <span v-if="property.district">{{ property.district }}</span>
                                        <span v-else class="text-gray-600">—</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-xs capitalize text-gray-300">{{ property.property_type }}</span>
                                            <span class="text-xs text-gray-500 capitalize">{{ property.listing_type }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-400">K{{ Number(property.price).toLocaleString() }}</td>
                                    <td class="px-4 py-3">
                                        <span :class="['px-2.5 py-0.5 rounded-full text-xs font-semibold', statusColors[property.approval_status]]">
                                            {{ property.approval_status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span :class="['px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize', availabilityColors[property.availability_status] ?? 'bg-gray-500/20 text-gray-400 ring-1 ring-gray-500/30']">
                                            {{ property.availability_status ?? 'available' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-400 whitespace-nowrap">
                                        {{ new Date(property.created_at).toLocaleDateString() }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <Link
                                            :href="route('admin.properties.show', property.id)"
                                            class="text-brand-red hover:text-red-400 font-medium text-sm whitespace-nowrap"
                                        >
                                            View →
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
