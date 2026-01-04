<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ArrowLeftIcon, PhotoIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';

const props = defineProps<{
    tier: string;
    currentCount: number;
    limit: number | null;
}>();

const form = useForm({
    title: '',
    description: '',
    price: '',
    location: '',
    province: '',
    town: '',
    bedrooms: 1,
    bathrooms: 1,
    square_feet: '',
    amenities: [] as string[],
    images: [] as File[],
});

const imagePreviews = ref<string[]>([]);

const handleImages = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        const files = Array.from(target.files);
        form.images = files;
        
        // Generate previews
        imagePreviews.value = [];
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreviews.value.push(e.target?.result as string);
            };
            reader.readAsDataURL(file);
        });
    }
};

const removeImage = (index: number) => {
    const newImages = Array.from(form.images);
    newImages.splice(index, 1);
    form.images = newImages;
    imagePreviews.value.splice(index, 1);
};

const amenitiesList = [
    'Parking', 'WiFi', 'Air Conditioning', 'Heating', 'Laundry', 
    'Dishwasher', 'Gym', 'Pool', 'Security', 'Balcony',
    'Garden', 'Pet Friendly', 'Furnished', 'Elevator'
];

const toggleAmenity = (amenity: string) => {
    const index = form.amenities.indexOf(amenity);
    if (index > -1) {
        form.amenities.splice(index, 1);
    } else {
        form.amenities.push(amenity);
    }
};

const submit = () => {
    form.post(route('landlord.properties.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Add Property" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('landlord.properties.index')" class="text-gray-600 hover:text-gray-900">
                    <ArrowLeftIcon class="w-6 h-6" />
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Add New Property</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Tier Limit Info -->
                <div v-if="limit" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        <strong>{{ tier }} Tier:</strong> {{ currentCount }} / {{ limit }} properties used
                    </p>
                </div>

                <!-- Error Display -->
                <div v-if="Object.keys(form.errors).length > 0" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                    <h3 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-2">Please fix the following errors:</h3>
                    <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300 space-y-1">
                        <li v-for="(error, field) in form.errors" :key="field">
                            <strong>{{ field }}:</strong> {{ error }}
                        </li>
                    </ul>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Basic Information -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Property Title *</label>
                                        <input
                                            v-model="form.title"
                                            type="text"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red"
                                            placeholder="e.g., Modern 2-Bedroom Apartment in Lusaka"
                                        />
                                        <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description *</label>
                                        <textarea
                                            v-model="form.description"
                                            required
                                            rows="4"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red"
                                            placeholder="Describe your property..."
                                        ></textarea>
                                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monthly Rent (ZMW) *</label>
                                        <input
                                            v-model="form.price"
                                            type="number"
                                            step="0.01"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red"
                                            placeholder="5000"
                                        />
                                        <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Location -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Location</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Street Address *</label>
                                        <input
                                            v-model="form.location"
                                            type="text"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red"
                                        />
                                        <p v-if="form.errors.location" class="mt-1 text-sm text-red-600">{{ form.errors.location }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Town/City *</label>
                                        <input
                                            v-model="form.town"
                                            type="text"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red"
                                        />
                                        <p v-if="form.errors.town" class="mt-1 text-sm text-red-600">{{ form.errors.town }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Province *</label>
                                        <input
                                            v-model="form.province"
                                            type="text"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red"
                                        />
                                        <p v-if="form.errors.province" class="mt-1 text-sm text-red-600">{{ form.errors.province }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Property Details -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Property Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bedrooms *</label>
                                        <input
                                            v-model.number="form.bedrooms"
                                            type="number"
                                            min="0"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bathrooms *</label>
                                        <input
                                            v-model.number="form.bathrooms"
                                            type="number"
                                            min="0"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Square Feet *</label>
                                        <input
                                            v-model.number="form.square_feet"
                                            type="number"
                                            min="0"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Amenities -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Amenities</h3>
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                    <label
                                        v-for="amenity in amenitiesList"
                                        :key="amenity"
                                        class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                                        :class="form.amenities.includes(amenity) ? 'border-brand-red bg-brand-red/10' : 'border-gray-300 dark:border-gray-600'"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="form.amenities.includes(amenity)"
                                            @change="toggleAmenity(amenity)"
                                            class="rounded text-brand-red focus:ring-brand-red"
                                        />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ amenity }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Images -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Property Images *</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block w-full cursor-pointer">
                                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-brand-red">
                                                <PhotoIcon class="w-12 h-12 mx-auto text-gray-400 mb-2" />
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    Click to upload images (max 10)
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">JPG, PNG up to 5MB each</p>
                                            </div>
                                            <input
                                                type="file"
                                                @change="handleImages"
                                                accept="image/jpeg,image/png,image/jpg"
                                                multiple
                                                class="hidden"
                                            />
                                        </label>
                                        <p v-if="form.errors.images" class="mt-1 text-sm text-red-600">{{ form.errors.images }}</p>
                                    </div>

                                    <!-- Image Previews -->
                                    <div v-if="imagePreviews.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div
                                            v-for="(preview, index) in imagePreviews"
                                            :key="index"
                                            class="relative group"
                                        >
                                            <img :src="preview" class="w-full h-32 object-cover rounded-lg" />
                                            <button
                                                type="button"
                                                @click="removeImage(index)"
                                                class="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                                            >
                                                <XMarkIcon class="w-4 h-4" />
                                            </button>
                                            <span v-if="index === 0" class="absolute bottom-2 left-2 px-2 py-1 bg-brand-red text-white text-xs rounded">
                                                Primary
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-4 pt-4">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center px-6 py-3 bg-brand-red border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-red disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Creating...' : 'Create Property' }}
                                </button>
                                <Link
                                    :href="route('landlord.properties.index')"
                                    class="inline-flex items-center px-6 py-3 bg-gray-300 border border-transparent rounded-md font-semibold text-sm text-gray-700 uppercase tracking-widest hover:bg-gray-400"
                                >
                                    Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
