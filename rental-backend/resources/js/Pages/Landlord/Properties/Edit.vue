<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import LocationPicker from '@/Components/LocationPicker.vue';
import InputError from '@/Components/InputError.vue';
import { PhotoIcon, XMarkIcon, TrashIcon } from '@heroicons/vue/24/outline';

interface PropertyImage { id: number; image_url: string; is_primary: boolean; }
interface UtilityOption { id: number; label: string; }
interface UtilityType   { id: number; name: string; icon: string | null; options: UtilityOption[]; }
interface Property {
    id: number; title: string; description: string; price: number;
    bedrooms: number; bathrooms: number;
    property_type: string; property_subtype: string; listing_type: string;
    province_id: number; district_id: number; town_id: number;
    street_address: string; latitude: number; longitude: number;
    images: PropertyImage[];
    utilities: { id: number }[];
}

const props = defineProps<{ property: Property; utilityTypes: UtilityType[]; }>();

const selectedImageIdsToRemove = ref<number[]>([]);

const form = useForm({
    title:            props.property.title,
    description:      props.property.description,
    price:            String(props.property.price),
    bedrooms:         props.property.bedrooms,
    bathrooms:        props.property.bathrooms,
    property_type:    props.property.property_type as 'residential' | 'commercial',
    property_subtype: props.property.property_subtype,
    listing_type:     props.property.listing_type as 'rent' | 'sale',
    province_id:      props.property.province_id as number | null,
    district_id:      props.property.district_id as number | null,
    town_id:          props.property.town_id as number | null,
    street_address:   props.property.street_address,
    latitude:         props.property.latitude as number | null,
    longitude:        props.property.longitude as number | null,
    utilities:        props.property.utilities.map(u => u.id),
    remove_images:    [] as number[],
    new_images:       [] as File[],
});

// ── Property type ────────────────────────────────────────────────────
const residentialSubtypes = ['house', 'apartment', 'room', 'farm', 'plot'];
const commercialSubtypes  = ['shop', 'office_space', 'warehouse', 'farm', 'plot'];
const subtypeOptions = computed(() =>
    form.property_type === 'residential' ? residentialSubtypes :
    form.property_type === 'commercial'  ? commercialSubtypes  : []
);
const subtypeLabels: Record<string, string> = {
    house: 'House', apartment: 'Apartment', room: 'Room / Bedsitter',
    farm: 'Farm', plot: 'Plot', shop: 'Shop', office_space: 'Office Space', warehouse: 'Warehouse',
};
function onTypeChange() { form.property_subtype = ''; }

// ── Utilities ────────────────────────────────────────────────────────
function selectUtilityOption(typeOptions: UtilityOption[], optionId: number) {
    typeOptions.forEach(o => {
        const i = form.utilities.indexOf(o.id);
        if (i > -1) form.utilities.splice(i, 1);
    });
    form.utilities.push(optionId);
}

// ── Image removal ────────────────────────────────────────────────────
function toggleRemoveImage(id: number) {
    const i = form.remove_images.indexOf(id);
    if (i > -1) form.remove_images.splice(i, 1);
    else         form.remove_images.push(id);
}

// ── New image upload ─────────────────────────────────────────────────
const newImagePreviews = ref<string[]>([]);
function handleNewImages(event: Event) {
    const files = Array.from((event.target as HTMLInputElement).files ?? []);
    form.new_images = files;
    newImagePreviews.value = [];
    files.forEach(f => {
        const r = new FileReader();
        r.onload = e => newImagePreviews.value.push(e.target?.result as string);
        r.readAsDataURL(f);
    });
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

const submit = () =>
    form.post(route('landlord.properties.update', props.property.id), {
        forceFormData: true,
        // Inertia uses POST + _method spoofing
    });
</script>

<template>
    <Head title="Edit Property" />

    <AuthenticatedLayout header="Edit Property" :back-url="route('landlord.properties.index')">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

                <div v-if="Object.keys(form.errors).length" class="bg-red-50 dark:bg-red-900/20 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-sm font-semibold text-red-800 dark:text-red-200 mb-1">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300 space-y-0.5">
                        <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                    </ul>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-8">
                        <!-- Spoof PUT -->
                        <input type="hidden" name="_method" value="PUT" />

                        <!-- ── Basic Info ──────────────────────────────────────── -->
                        <section>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Basic Information</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Property Title *</label>
                                    <input v-model="form.title" type="text" required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red" />
                                    <InputError :message="form.errors.title" class="mt-1" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description *</label>
                                    <textarea v-model="form.description" rows="4" required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red"></textarea>
                                    <InputError :message="form.errors.description" class="mt-1" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price (ZMW) *</label>
                                    <input v-model="form.price" type="number" step="0.01" min="0" required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red" />
                                    <InputError :message="form.errors.price" class="mt-1" />
                                </div>
                            </div>
                        </section>

                        <!-- ── Property Type ─────────────────────────────────────── -->
                        <section>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Property Type</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                                    <div class="flex gap-3">
                                        <label v-for="t in ['residential','commercial']" :key="t"
                                            class="flex-1 flex items-center justify-center gap-2 p-3 border rounded-lg cursor-pointer text-sm font-medium transition-colors"
                                            :class="form.property_type === t ? 'border-brand-red bg-brand-red/10 text-brand-red' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'">
                                            <input type="radio" v-model="form.property_type" :value="t" class="hidden" @change="onTypeChange" />
                                            {{ t.charAt(0).toUpperCase() + t.slice(1) }}
                                        </label>
                                    </div>
                                    <InputError :message="form.errors.property_type" class="mt-1" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subtype *</label>
                                    <select v-model="form.property_subtype" :disabled="!form.property_type"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red disabled:opacity-50">
                                        <option value="" disabled>Select subtype</option>
                                        <option v-for="s in subtypeOptions" :key="s" :value="s">{{ subtypeLabels[s] }}</option>
                                    </select>
                                    <InputError :message="form.errors.property_subtype" class="mt-1" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Listing Type *</label>
                                    <div class="flex gap-3">
                                        <label v-for="lt in ['rent','sale']" :key="lt"
                                            class="flex-1 flex items-center justify-center gap-2 p-3 border rounded-lg cursor-pointer text-sm font-medium transition-colors"
                                            :class="form.listing_type === lt ? 'border-brand-red bg-brand-red/10 text-brand-red' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'">
                                            <input type="radio" v-model="form.listing_type" :value="lt" class="hidden" />
                                            {{ lt.charAt(0).toUpperCase() + lt.slice(1) }}
                                        </label>
                                    </div>
                                    <InputError :message="form.errors.listing_type" class="mt-1" />
                                </div>
                            </div>
                        </section>

                        <!-- ── Details ────────────────────────────────────────────── -->
                        <section>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Property Details</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bedrooms</label>
                                    <input v-model.number="form.bedrooms" type="number" min="0"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bathrooms</label>
                                    <input v-model.number="form.bathrooms" type="number" min="0"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red" />
                                </div>
                            </div>
                        </section>

                        <!-- ── Location ───────────────────────────────────────────── -->
                        <section>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Location *</h3>
                            <LocationPicker v-model="locationValue" />
                            <div class="mt-1 space-y-0.5">
                                <InputError :message="form.errors.province_id" />
                                <InputError :message="form.errors.district_id" />
                                <InputError :message="form.errors.town_id" />
                                <InputError :message="form.errors.street_address" />
                                <InputError :message="form.errors.latitude" />
                                <InputError :message="form.errors.longitude" />
                            </div>
                        </section>

                        <!-- ── Utilities ──────────────────────────────────────────── -->
                        <section v-if="utilityTypes.length">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                                Utilities <span class="text-gray-400 font-normal text-sm">(optional)</span>
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div v-for="ut in utilityTypes" :key="ut.id">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ ut.name }}</p>
                                    <div class="flex flex-wrap gap-2">
                                        <button v-for="opt in ut.options" :key="opt.id" type="button"
                                            @click="selectUtilityOption(ut.options, opt.id)"
                                            class="px-3 py-1.5 text-sm rounded-full border transition-colors"
                                            :class="form.utilities.includes(opt.id)
                                                ? 'bg-brand-red text-white border-brand-red'
                                                : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-brand-red'">
                                            {{ opt.label }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- ── Existing Images ────────────────────────────────────── -->
                        <section v-if="property.images.length">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Existing Images</h3>
                            <div class="grid grid-cols-3 md:grid-cols-5 gap-3">
                                <div v-for="img in property.images" :key="img.id" class="relative group">
                                    <img :src="'/storage/' + img.image_url" class="w-full h-28 object-cover rounded-lg"
                                        :class="form.remove_images.includes(img.id) ? 'opacity-40 grayscale' : ''" />
                                    <button type="button" @click="toggleRemoveImage(img.id)"
                                        class="absolute top-1 right-1 p-1 rounded-full text-white transition-all"
                                        :class="form.remove_images.includes(img.id) ? 'bg-gray-500' : 'bg-red-600 opacity-0 group-hover:opacity-100'">
                                        <TrashIcon class="w-3 h-3" />
                                    </button>
                                    <span v-if="img.is_primary" class="absolute bottom-1 left-1 px-1.5 py-0.5 bg-brand-red text-white text-xs rounded">Primary</span>
                                    <span v-if="form.remove_images.includes(img.id)" class="absolute inset-0 flex items-center justify-center text-xs text-gray-600 font-medium">Remove</span>
                                </div>
                            </div>
                        </section>

                        <!-- ── Add New Images ─────────────────────────────────────── -->
                        <section>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Add New Images</h3>
                            <label class="block cursor-pointer">
                                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-brand-red transition-colors">
                                    <PhotoIcon class="w-10 h-10 mx-auto text-gray-400 mb-2" />
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Click to add more images</p>
                                </div>
                                <input type="file" @change="handleNewImages" accept="image/jpeg,image/png,image/jpg" multiple class="hidden" />
                            </label>
                            <div v-if="newImagePreviews.length" class="grid grid-cols-3 md:grid-cols-5 gap-3 mt-4">
                                <img v-for="(p, i) in newImagePreviews" :key="i" :src="p" class="w-full h-28 object-cover rounded-lg" />
                            </div>
                        </section>

                        <!-- ── Actions ────────────────────────────────────────────── -->
                        <div class="flex items-center gap-4 pt-2">
                            <button type="submit" :disabled="form.processing"
                                class="px-6 py-2.5 bg-brand-red text-white text-sm font-semibold rounded-md hover:bg-red-700 disabled:opacity-50 transition-colors">
                                {{ form.processing ? 'Saving…' : 'Save Changes' }}
                            </button>
                            <Link :href="route('landlord.properties.index')"
                                class="px-6 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                                Cancel
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
