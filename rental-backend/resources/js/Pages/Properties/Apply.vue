<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import {
    HomeIcon, BuildingOffice2Icon, MapPinIcon,
    DocumentTextIcon, ChatBubbleLeftIcon,
} from '@heroicons/vue/24/outline';

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
    terms_and_conditions?: string | null;
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
    applicant_terms:     '',
    adults:     1,
    children:   0,
    has_pets:   false,
    pet_details: '',
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
                <div class="bg-light-bg rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                        <HomeIcon class="w-4 h-4 text-brand-red" />
                        <h3 class="text-sm font-semibold text-white">Property Summary</h3>
                    </div>
                    <div class="p-4 flex gap-4">
                        <img v-if="primaryImage" :src="primaryImage"
                            class="w-28 h-20 object-cover rounded-md shrink-0" />
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-semibold uppercase px-2.5 py-0.5 rounded-full ring-1"
                                    :class="isResidentialRent
                                        ? 'bg-green-500/20 text-green-400 ring-green-500/30'
                                        : 'bg-blue-500/20 text-blue-400 ring-blue-500/30'">
                                    {{ subtypeLabel[property.property_subtype] ?? property.property_subtype }}
                                </span>
                                <span class="text-xs font-semibold uppercase px-2.5 py-0.5 rounded-full bg-gray-500/20 text-gray-400 ring-1 ring-gray-500/30">
                                    For {{ property.listing_type }}
                                </span>
                            </div>
                            <h2 class="text-base font-semibold text-white mt-1 truncate">{{ property.title }}</h2>
                            <p class="text-sm text-gray-400 flex items-center gap-1 mt-0.5">
                                <MapPinIcon class="w-4 h-4 shrink-0" />
                                {{ locationText }}
                            </p>
                            <p class="text-base font-bold text-brand-red mt-1">K{{ Number(property.price).toLocaleString() }} / mo</p>
                        </div>
                    </div>
                </div>

                <!-- Application form -->
                <form @submit.prevent="submit" class="space-y-6">

                    <!-- ── Landlord T&Cs notice ───────────────────────────────── -->
                    <div v-if="property.terms_and_conditions"
                         class="rounded-xl border border-yellow-500/30 bg-yellow-500/5 p-4">
                        <h4 class="text-sm font-semibold text-yellow-300 mb-2">📋 Landlord's Terms &amp; Conditions</h4>
                        <p class="text-xs text-gray-400 mb-3">By submitting this application you acknowledge these terms. You will be asked to formally agree before payment.</p>
                        <div class="bg-dark-bg/40 rounded-md p-3 text-sm text-gray-300 whitespace-pre-line leading-relaxed max-h-48 overflow-y-auto">{{ property.terms_and_conditions }}</div>
                    </div>

                    <!-- ── Cover Letter ───────────────────────────────────────── -->
                    <div class="bg-light-bg rounded-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                            <ChatBubbleLeftIcon class="w-4 h-4 text-brand-red" />
                            <h3 class="text-sm font-semibold text-white">Your Message</h3>
                        </div>
                        <div class="p-5 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300">Message to Landlord</label>
                                <textarea v-model="form.message" rows="4"
                                    class="mt-1 block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red placeholder-gray-500"
                                    placeholder="Introduce yourself and explain why you're a good fit…"></textarea>
                                <InputError :message="form.errors.message" class="mt-1" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300">Preferred Move-in Date</label>
                                <input id="preferred_move_in" type="date"
                                    class="mt-1 block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red"
                                    v-model="form.preferred_move_in" />
                                <InputError :message="form.errors.preferred_move_in" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- ── Residential Rent: Occupant Details ─────────────────── -->
                    <div v-if="isResidentialRent" class="bg-light-bg rounded-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                            <HomeIcon class="w-4 h-4 text-brand-red" />
                            <h3 class="text-sm font-semibold text-white">Occupant Details</h3>
                        </div>
                        <div class="p-5 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300">Number of Adults *</label>
                                    <input id="adults" type="number" min="1" max="20" required
                                        v-model.number="form.adults"
                                        class="mt-1 block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red" />
                                    <InputError :message="form.errors.adults" class="mt-1" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300">Number of Children *</label>
                                    <input id="children" type="number" min="0" max="20" required
                                        v-model.number="form.children"
                                        class="mt-1 block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red" />
                                    <InputError :message="form.errors.children" class="mt-1" />
                                </div>
                            </div>
                            <div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <Checkbox name="has_pets" v-model:checked="form.has_pets" />
                                    <span class="text-sm text-gray-300">We have pets</span>
                                </label>
                                <InputError :message="form.errors.has_pets" class="mt-1" />
                            </div>
                            <div v-if="form.has_pets">
                                <label class="block text-sm font-medium text-gray-300">Pet details (type, breed, size)</label>
                                <input id="pet_details" type="text"
                                    class="mt-1 block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red placeholder-gray-500"
                                    v-model="form.pet_details"
                                    placeholder="e.g., 1 small dog — Chihuahua" />
                                <InputError :message="form.errors.pet_details" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- ── Commercial: Business Details ───────────────────────── -->
                    <div v-if="isCommercial" class="bg-light-bg rounded-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                            <BuildingOffice2Icon class="w-4 h-4 text-brand-red" />
                            <h3 class="text-sm font-semibold text-white">Business Details</h3>
                        </div>
                        <div class="p-5 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300">Intended Use *</label>
                                <input id="intended_use" type="text"
                                    class="mt-1 block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red placeholder-gray-500"
                                    v-model="form.intended_use" required
                                    placeholder="e.g., Retail clothing shop" />
                                <InputError :message="form.errors.intended_use" class="mt-1" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300">Business / Company Name</label>
                                <input id="business_name" type="text"
                                    class="mt-1 block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red placeholder-gray-500"
                                    v-model="form.business_name"
                                    placeholder="e.g., Mwamba Textiles Ltd" />
                                <InputError :message="form.errors.business_name" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- ── Additional Comments ────────────────────────────────── -->
                    <div class="bg-light-bg rounded-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                            <ChatBubbleLeftIcon class="w-4 h-4 text-brand-red" />
                            <h3 class="text-sm font-semibold text-white">Additional Comments</h3>
                        </div>
                        <div class="p-5">
                            <textarea v-model="form.additional_comments" rows="3"
                                class="block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red placeholder-gray-500"
                                placeholder="Anything else the landlord should know?"></textarea>
                            <InputError :message="form.errors.additional_comments" class="mt-1" />
                        </div>
                    </div>

                    <!-- ── Your Terms & Conditions ─────────────────────────────── -->
                    <div class="bg-light-bg rounded-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-700/60 flex items-center gap-2">
                            <DocumentTextIcon class="w-4 h-4 text-brand-red" />
                            <h3 class="text-sm font-semibold text-white">Your Terms &amp; Conditions <span class="text-gray-500 font-normal text-xs">(optional)</span></h3>
                        </div>
                        <div class="p-5">
                            <p class="text-sm text-gray-400 mb-3">
                                Set any conditions you require from the landlord — for example maintenance responsibilities, access hours, or what fixtures are included.
                                The landlord must agree to these before requesting payment, and they will be included in the lease agreement.
                            </p>
                            <textarea v-model="form.applicant_terms" rows="5"
                                class="block w-full rounded-md border border-gray-700 bg-dark-bg text-gray-200 shadow-sm focus:border-brand-red focus:ring-brand-red text-sm placeholder-gray-500"
                                placeholder="e.g. Landlord must repair the roof before move-in. All existing appliances (fridge, stove) remain in the property…"></textarea>
                            <InputError :message="form.errors.applicant_terms" class="mt-1" />
                        </div>
                    </div>

                    <!-- ── Submit ──────────────────────────────────────────────── -->
                    <div class="flex items-center gap-4 pb-4">
                        <PrimaryButton :disabled="form.processing">
                            {{ form.processing ? 'Submitting…' : 'Submit Application' }}
                        </PrimaryButton>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
