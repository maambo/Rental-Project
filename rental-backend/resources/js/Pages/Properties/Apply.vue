<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { HomeIcon, BuildingOffice2Icon, MapPinIcon } from '@heroicons/vue/24/outline';

interface Property {
    id: number;
    title: string;
    price: number;
    property_type: 'residential' | 'commercial';
    property_subtype: string;
    listing_type: 'rent' | 'sale';
    street_address: string;
    province?: { name: string };
    district?: { name: string };
    town?: { name: string };
    images?: { image_url: string; is_primary: boolean }[];
}

const props = defineProps<{ property: Property }>();

const isResidentialRent = computed(
    () => props.property.property_type === 'residential' && props.property.listing_type === 'rent'
);
const isCommercial = computed(() => props.property.property_type === 'commercial');

const primaryImage = computed(() => {
    const img = props.property.images?.find(i => i.is_primary) ?? props.property.images?.[0];
    return img ? '/storage/' + img.image_url : null;
});

const locationText = computed(() => {
    const parts = [
        props.property.street_address,
        props.property.town?.name,
        props.property.district?.name,
        props.property.province?.name,
    ].filter(Boolean);
    return parts.join(', ');
});

const subtypeLabel: Record<string, string> = {
    house: 'House', apartment: 'Apartment', room: 'Room / Bedsitter',
    shop: 'Shop', office_space: 'Office Space', warehouse: 'Warehouse',
    farm: 'Farm', plot: 'Plot',
};

const form = useForm({
    property_id:         props.property.id,
    message:             '',
    preferred_move_in:   '',
    additional_comments: '',
    // Residential rent fields
    adults:     1,
    children:   0,
    has_pets:   false,
    pet_details: '',
    // Commercial fields
    intended_use:  '',
    business_name: '',
});

const submit = () => form.post(route('properties.apply.store', props.property.id));
</script>

<template>
    <Head :title="`Apply — ${property.title}`" />

    <AuthenticatedLayout :header="`Apply for Property`" :back-url="route('properties.show', property.id)">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Property summary card -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden flex gap-4 p-4">
                    <img v-if="primaryImage" :src="primaryImage"
                        class="w-28 h-20 object-cover rounded-md shrink-0" />
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-semibold uppercase px-2 py-0.5 rounded-full"
                                :class="isResidentialRent ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
                                                          : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'">
                                {{ subtypeLabel[property.property_subtype] ?? property.property_subtype }}
                            </span>
                            <span class="text-xs font-semibold uppercase px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                For {{ property.listing_type }}
                            </span>
                        </div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white mt-1 truncate">{{ property.title }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-0.5">
                            <MapPinIcon class="w-4 h-4 shrink-0" />
                            {{ locationText }}
                        </p>
                        <p class="text-base font-bold text-brand-red mt-1">K{{ Number(property.price).toLocaleString() }} / mo</p>
                    </div>
                </div>

                <!-- Application form -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-8">

                        <!-- ── Cover Letter ───────────────────────────────────────── -->
                        <section>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                                Your Message
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <InputLabel value="Message to Landlord" />
                                    <textarea v-model="form.message" rows="4"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-brand-red focus:ring-brand-red"
                                        placeholder="Introduce yourself and explain why you're a good fit…"></textarea>
                                    <InputError :message="form.errors.message" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel for="preferred_move_in" value="Preferred Move-in Date" />
                                    <TextInput id="preferred_move_in" type="date" class="mt-1 block w-full"
                                        v-model="form.preferred_move_in" />
                                    <InputError :message="form.errors.preferred_move_in" class="mt-1" />
                                </div>
                            </div>
                        </section>

                        <!-- ── Residential Rent: Occupant Details ─────────────────── -->
                        <section v-if="isResidentialRent">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4 flex items-center gap-2">
                                <HomeIcon class="w-5 h-5 text-brand-red" />
                                Occupant Details
                            </h3>
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="adults" value="Number of Adults *" />
                                        <input id="adults" type="number" min="1" max="20" required
                                            v-model.number="form.adults"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-brand-red focus:ring-brand-red" />
                                        <InputError :message="form.errors.adults" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel for="children" value="Number of Children *" />
                                        <input id="children" type="number" min="0" max="20" required
                                            v-model.number="form.children"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-brand-red focus:ring-brand-red" />
                                        <InputError :message="form.errors.children" class="mt-1" />
                                    </div>
                                </div>
                                <div>
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <Checkbox name="has_pets" v-model:checked="form.has_pets" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">We have pets</span>
                                    </label>
                                    <InputError :message="form.errors.has_pets" class="mt-1" />
                                </div>
                                <div v-if="form.has_pets">
                                    <InputLabel for="pet_details" value="Pet details (type, breed, size)" />
                                    <TextInput id="pet_details" type="text" class="mt-1 block w-full"
                                        v-model="form.pet_details"
                                        placeholder="e.g., 1 small dog — Chihuahua" />
                                    <InputError :message="form.errors.pet_details" class="mt-1" />
                                </div>
                            </div>
                        </section>

                        <!-- ── Commercial: Business Details ───────────────────────── -->
                        <section v-if="isCommercial">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4 flex items-center gap-2">
                                <BuildingOffice2Icon class="w-5 h-5 text-brand-red" />
                                Business Details
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="intended_use" value="Intended Use *" />
                                    <TextInput id="intended_use" type="text" class="mt-1 block w-full"
                                        v-model="form.intended_use" required
                                        placeholder="e.g., Retail clothing shop" />
                                    <InputError :message="form.errors.intended_use" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel for="business_name" value="Business / Company Name" />
                                    <TextInput id="business_name" type="text" class="mt-1 block w-full"
                                        v-model="form.business_name"
                                        placeholder="e.g., Mwamba Textiles Ltd" />
                                    <InputError :message="form.errors.business_name" class="mt-1" />
                                </div>
                            </div>
                        </section>

                        <!-- ── Additional Comments ────────────────────────────────── -->
                        <section>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Additional Comments</h3>
                            <textarea v-model="form.additional_comments" rows="3"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-brand-red focus:ring-brand-red"
                                placeholder="Anything else the landlord should know?"></textarea>
                            <InputError :message="form.errors.additional_comments" class="mt-1" />
                        </section>

                        <!-- ── Submit ──────────────────────────────────────────────── -->
                        <div class="flex items-center gap-4 pt-2">
                            <PrimaryButton :disabled="form.processing">
                                {{ form.processing ? 'Submitting…' : 'Submit Application' }}
                            </PrimaryButton>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
