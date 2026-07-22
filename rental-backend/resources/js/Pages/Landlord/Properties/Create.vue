<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import LocationPicker from '@/Components/LocationPicker.vue';
import InputError from '@/Components/InputError.vue';
import {
    PhotoIcon, XMarkIcon, DocumentArrowUpIcon, DocumentTextIcon,
    InformationCircleIcon, HomeIcon, MapPinIcon, WrenchScrewdriverIcon,
} from '@heroicons/vue/24/outline';

interface UtilityOption { id: number; label: string; }
interface UtilityType   { id: number; name: string; icon: string | null; options: UtilityOption[]; }

const props = defineProps<{
    utilityTypes: UtilityType[];
}>();

const bedroomSubtypes = ['house', 'apartment', 'room'];
const showBedroomFields = computed(() => bedroomSubtypes.includes(form.property_subtype));

const form = useForm({
    title:                '',
    description:          '',
    terms_and_conditions: '',
    price:                '',
    bedrooms:         null as number | null,
    bathrooms:        null as number | null,
    property_type:    '' as 'residential' | 'commercial' | '',
    property_subtype: '',
    listing_type:     '' as 'rent' | 'sale' | '',
    province_id:    null as number | null,
    district_id:    null as number | null,
    town_id:        null as number | null,
    street_address: '',
    latitude:       null as number | null,
    longitude:      null as number | null,
    utilities:        [] as number[],
    images:           [] as File[],
    documents:        [] as File[],
    document_names:   [] as string[],
});

// ── Document handling ─────────────────────────────────────────────
const documentFiles = ref<File[]>([]);
const documentNames = ref<string[]>([]);

function handleDocuments(event: Event) {
    const files = Array.from((event.target as HTMLInputElement).files ?? []);
    files.forEach(f => {
        documentFiles.value.push(f);
        documentNames.value.push(f.name.replace(/\.[^.]+$/, ''));
    });
    form.documents = documentFiles.value;
    form.document_names = documentNames.value;
    (event.target as HTMLInputElement).value = '';
}

function removeDocument(index: number) {
    documentFiles.value.splice(index, 1);
    documentNames.value.splice(index, 1);
    form.documents = documentFiles.value;
    form.document_names = documentNames.value;
}

// ── Property type options ────────────────────────────────────────────
const residentialSubtypes = ['house', 'apartment', 'room', 'farm', 'plot'];
const commercialSubtypes  = ['shop', 'office_space', 'warehouse', 'farm', 'plot'];

const subtypeOptions = computed(() =>
    form.property_type === 'residential' ? residentialSubtypes :
    form.property_type === 'commercial'  ? commercialSubtypes  : []
);

const subtypeLabels: Record<string, string> = {
    house: 'House', apartment: 'Apartment', room: 'Room / Bedsitter',
    farm: 'Farm', plot: 'Plot', shop: 'Shop', office_space: 'Office Space',
    warehouse: 'Warehouse',
};

function onTypeChange() {
    form.property_subtype = '';
}

// ── Utilities ────────────────────────────────────────────────────────
function selectUtilityOption(typeOptions: UtilityOption[], optionId: number) {
    typeOptions.forEach(o => {
        const i = form.utilities.indexOf(o.id);
        if (i > -1) form.utilities.splice(i, 1);
    });
    form.utilities.push(optionId);
}

// ── Image handling ───────────────────────────────────────────────────
const imagePreviews = ref<string[]>([]);

function handleImages(event: Event) {
    const files = Array.from((event.target as HTMLInputElement).files ?? []);
    form.images = files;
    imagePreviews.value = [];
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = e => imagePreviews.value.push(e.target?.result as string);
        reader.readAsDataURL(file);
    });
}

function removeImage(index: number) {
    const arr = Array.from(form.images);
    arr.splice(index, 1);
    form.images = arr;
    imagePreviews.value.splice(index, 1);
}

// ── Location sync ────────────────────────────────────────────────────
const locationValue = computed({
    get: () => ({
        province_id:    form.province_id,
        district_id:    form.district_id,
        town_id:        form.town_id,
        street_address: form.street_address,
        latitude:       form.latitude,
        longitude:      form.longitude,
    }),
    set: (v) => {
        form.province_id    = v.province_id;
        form.district_id    = v.district_id;
        form.town_id        = v.town_id;
        form.street_address = v.street_address;
        form.latitude       = v.latitude;
        form.longitude      = v.longitude;
    },
});

const submit = () => form.post(route('landlord.properties.store'), { forceFormData: true });
</script>

<template>
    <Head title="Add Property" />

    <AuthenticatedLayout header="Add New Property" :back-url="route('landlord.properties.index')">
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Validation errors -->
                    <div v-if="Object.keys(form.errors).length"
                        class="bg-red-500/10 border border-red-500/30 rounded-xl p-4">
                        <p class="text-sm font-semibold text-red-300 mb-1">Please fix the following errors:</p>
                        <ul class="list-disc list-inside text-sm text-red-400 space-y-0.5">
                            <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                        </ul>
                    </div>

                    <!-- ── Basic Info ─────────────────────────────────────────── -->
                    <div class="bg-light-bg rounded-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                            <InformationCircleIcon class="w-4 h-4 text-brand-red" />
                            <h3 class="text-sm font-semibold text-white">Basic Information</h3>
                        </div>
                        <div class="p-5 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300">Property Title *</label>
                                <input v-model="form.title" type="text" required
                                    class="mt-1 block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red placeholder-gray-500"
                                    placeholder="e.g., Modern 3-Bedroom House in Kabulonga" />
                                <InputError :message="form.errors.title" class="mt-1" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300">Description *</label>
                                <textarea v-model="form.description" rows="4" required
                                    class="mt-1 block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red placeholder-gray-500"
                                    placeholder="Describe the property, features, neighbourhood…"></textarea>
                                <InputError :message="form.errors.description" class="mt-1" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300">Terms &amp; Conditions <span class="text-gray-500 font-normal">(optional)</span></label>
                                <p class="text-xs text-gray-500 mt-0.5 mb-1">Set any conditions that applicants must agree to before completing payment. These will be included in the lease agreement.</p>
                                <textarea v-model="form.terms_and_conditions" rows="5"
                                    class="mt-1 block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red text-sm placeholder-gray-500"
                                    placeholder="e.g. No pets allowed. Tenant is responsible for all utility bills. Rent is due on the 1st of each month…"></textarea>
                                <InputError :message="form.errors.terms_and_conditions" class="mt-1" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300">Price (ZMW) *</label>
                                <input v-model="form.price" type="number" step="0.01" min="0" required
                                    class="mt-1 block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red placeholder-gray-500"
                                    placeholder="5000" />
                                <InputError :message="form.errors.price" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- ── Property Type ───────────────────────────────────────── -->
                    <div class="bg-light-bg rounded-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                            <HomeIcon class="w-4 h-4 text-brand-red" />
                            <h3 class="text-sm font-semibold text-white">Property Type</h3>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Category -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-1">Category *</label>
                                    <div class="flex gap-3">
                                        <label v-for="t in ['residential','commercial']" :key="t"
                                            class="flex-1 flex items-center justify-center gap-2 p-3 border rounded-lg cursor-pointer text-sm font-medium transition-colors"
                                            :class="form.property_type === t
                                                ? 'border-brand-red bg-brand-red/10 text-brand-red'
                                                : 'border-gray-700 text-gray-400 hover:border-brand-red hover:text-gray-300'"
                                        >
                                            <input type="radio" v-model="form.property_type" :value="t" class="hidden" @change="onTypeChange" />
                                            {{ t.charAt(0).toUpperCase() + t.slice(1) }}
                                        </label>
                                    </div>
                                    <InputError :message="form.errors.property_type" class="mt-1" />
                                </div>
                                <!-- Subtype -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-1">Subtype *</label>
                                    <select v-model="form.property_subtype" :disabled="!form.property_type"
                                        class="block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red disabled:opacity-50">
                                        <option value="" disabled>{{ form.property_type ? 'Select subtype' : 'Select category first' }}</option>
                                        <option v-for="s in subtypeOptions" :key="s" :value="s">{{ subtypeLabels[s] }}</option>
                                    </select>
                                    <InputError :message="form.errors.property_subtype" class="mt-1" />
                                </div>
                                <!-- Listing type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-1">Listing Type *</label>
                                    <div class="flex gap-3">
                                        <label v-for="lt in ['rent','sale']" :key="lt"
                                            class="flex-1 flex items-center justify-center gap-2 p-3 border rounded-lg cursor-pointer text-sm font-medium transition-colors"
                                            :class="form.listing_type === lt
                                                ? 'border-brand-red bg-brand-red/10 text-brand-red'
                                                : 'border-gray-700 text-gray-400 hover:border-brand-red hover:text-gray-300'"
                                        >
                                            <input type="radio" v-model="form.listing_type" :value="lt" class="hidden" />
                                            {{ lt.charAt(0).toUpperCase() + lt.slice(1) }}
                                        </label>
                                    </div>
                                    <InputError :message="form.errors.listing_type" class="mt-1" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Property Details ───────────────────────────────────── -->
                    <div v-if="showBedroomFields" class="bg-light-bg rounded-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                            <HomeIcon class="w-4 h-4 text-brand-red" />
                            <h3 class="text-sm font-semibold text-white">Property Details</h3>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300">Bedrooms</label>
                                    <input v-model.number="form.bedrooms" type="number" min="0"
                                        class="mt-1 block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300">Bathrooms</label>
                                    <input v-model.number="form.bathrooms" type="number" min="0"
                                        class="mt-1 block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Location ───────────────────────────────────────────── -->
                    <div class="bg-light-bg rounded-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                            <MapPinIcon class="w-4 h-4 text-brand-red" />
                            <h3 class="text-sm font-semibold text-white">Location *</h3>
                        </div>
                        <div class="p-5">
                            <LocationPicker v-model="locationValue" />
                            <div class="mt-1 space-y-0.5">
                                <InputError :message="form.errors.province_id"    />
                                <InputError :message="form.errors.district_id"    />
                                <InputError :message="form.errors.town_id"        />
                                <InputError :message="form.errors.street_address" />
                                <InputError :message="form.errors.latitude"       />
                                <InputError :message="form.errors.longitude"      />
                            </div>
                        </div>
                    </div>

                    <!-- ── Utilities ──────────────────────────────────────────── -->
                    <div v-if="utilityTypes.length" class="bg-light-bg rounded-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                            <WrenchScrewdriverIcon class="w-4 h-4 text-brand-red" />
                            <h3 class="text-sm font-semibold text-white">Utilities <span class="text-gray-500 font-normal text-xs">(optional)</span></h3>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div v-for="ut in utilityTypes" :key="ut.id">
                                    <p class="text-sm font-medium text-gray-300 mb-2">{{ ut.name }}</p>
                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            v-for="opt in ut.options"
                                            :key="opt.id"
                                            type="button"
                                            @click="selectUtilityOption(ut.options, opt.id)"
                                            class="px-3 py-1.5 text-sm rounded-full border transition-colors"
                                            :class="form.utilities.includes(opt.id)
                                                ? 'bg-brand-red text-white border-brand-red'
                                                : 'border-gray-700 text-gray-400 hover:border-brand-red hover:text-gray-200'"
                                        >
                                            {{ opt.label }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Images ─────────────────────────────────────────────── -->
                    <div class="bg-light-bg rounded-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                            <PhotoIcon class="w-4 h-4 text-brand-red" />
                            <h3 class="text-sm font-semibold text-white">Property Images *</h3>
                        </div>
                        <div class="p-5">
                            <label class="block cursor-pointer">
                                <div class="border-2 border-dashed border-gray-700 rounded-lg p-6 text-center hover:border-brand-red transition-colors">
                                    <PhotoIcon class="w-10 h-10 mx-auto text-gray-500 mb-2" />
                                    <p class="text-sm text-gray-400">Click to upload images (max 10)</p>
                                    <p class="text-xs text-gray-500 mt-1">JPG, PNG up to 5MB each</p>
                                </div>
                                <input type="file" @change="handleImages" accept="image/jpeg,image/png,image/jpg" multiple class="hidden" />
                            </label>
                            <InputError :message="form.errors.images" class="mt-1" />

                            <div v-if="imagePreviews.length" class="grid grid-cols-3 md:grid-cols-5 gap-3 mt-4">
                                <div v-for="(preview, i) in imagePreviews" :key="i" class="relative group">
                                    <img :src="preview" class="w-full h-28 object-cover rounded-lg" />
                                    <button type="button" @click="removeImage(i)"
                                        class="absolute top-1 right-1 p-1 bg-red-600 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                        <XMarkIcon class="w-3 h-3" />
                                    </button>
                                    <span v-if="i === 0" class="absolute bottom-1 left-1 px-1.5 py-0.5 bg-brand-red text-white text-xs rounded">Primary</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Documents ─────────────────────────────────────────── -->
                    <div class="bg-light-bg rounded-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                            <DocumentArrowUpIcon class="w-4 h-4 text-brand-red" />
                            <h3 class="text-sm font-semibold text-white">Documents <span class="text-gray-500 font-normal text-xs">(optional)</span></h3>
                        </div>
                        <div class="p-5">
                            <p class="text-xs text-gray-500 mb-4">Upload supporting documents such as title deeds, lease agreements, or floor plans. PDF, Word, or image files up to 10 MB each.</p>
                            <label class="block cursor-pointer">
                                <div class="border-2 border-dashed border-gray-700 rounded-lg p-5 text-center hover:border-brand-red transition-colors">
                                    <DocumentArrowUpIcon class="w-8 h-8 mx-auto text-gray-500 mb-2" />
                                    <p class="text-sm text-gray-400">Click to upload documents</p>
                                    <p class="text-xs text-gray-500 mt-1">PDF, DOC, DOCX, JPG, PNG · max 10 MB each</p>
                                </div>
                                <input type="file" @change="handleDocuments" accept=".pdf,.doc,.docx,image/jpeg,image/png" multiple class="hidden" />
                            </label>
                            <div v-if="documentFiles.length" class="mt-3 space-y-2">
                                <div v-for="(doc, i) in documentFiles" :key="i"
                                    class="flex items-center gap-3 p-2.5 bg-dark-bg/60 rounded-lg">
                                    <DocumentTextIcon class="w-5 h-5 text-gray-500 flex-shrink-0" />
                                    <input v-model="documentNames[i]" type="text" :placeholder="doc.name"
                                        class="flex-1 text-sm bg-transparent border-0 p-0 focus:ring-0 text-gray-300 placeholder-gray-500"
                                        @input="form.document_names[i] = documentNames[i]" />
                                    <span class="text-xs text-gray-500 flex-shrink-0">{{ (doc.size / 1024).toFixed(0) }} KB</span>
                                    <button type="button" @click="removeDocument(i)" class="text-red-400 hover:text-red-300">
                                        <XMarkIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Actions ────────────────────────────────────────────── -->
                    <div class="flex items-center gap-4 pb-4">
                        <button type="submit" :disabled="form.processing"
                            class="px-6 py-2.5 bg-brand-red text-white text-sm font-semibold rounded-md hover:bg-red-700 disabled:opacity-50 transition-colors">
                            {{ form.processing ? 'Creating…' : 'Create Property' }}
                        </button>
                        <Link :href="route('landlord.properties.index')"
                            class="px-6 py-2.5 bg-gray-700 text-gray-300 text-sm font-semibold rounded-md hover:bg-gray-600 transition-colors">
                            Cancel
                        </Link>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
