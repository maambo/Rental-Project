<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import DashboardCard from '@/Components/DashboardCard.vue';
import { MapPinIcon, HomeIcon, ArrowRightIcon, EyeIcon, UsersIcon, ChartBarIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline';
import { StarIcon } from '@heroicons/vue/24/solid';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
    laravelVersion: string;
    phpVersion: string;
    properties: Array<any>;
}>();
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <!-- Hero Section -->
        <div class="relative bg-gradient-to-br from-brand-red to-brand-orange text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-24">
                <!-- Navbar -->
                <nav class="flex justify-between items-center mb-16">
                    <div class="flex items-center gap-2">
                        <HomeIcon class="h-8 w-8 text-white" />
                        <span class="text-2xl font-bold tracking-wider">RENTALAPP</span>
                    </div>
                    <div v-if="canLogin" class="flex gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="rounded-full bg-white/20 px-6 py-2 text-sm font-semibold text-white ring-1 ring-white/50 transition hover:bg-white/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="rounded-full bg-white/20 px-6 py-2 text-sm font-semibold text-white ring-1 ring-white/50 transition hover:bg-white/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                            >
                                Log in
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="rounded-full bg-white px-6 py-2 text-sm font-semibold text-brand-red transition hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                            >
                                Register
                            </Link>
                        </template>
                    </div>
                </nav>

                <!-- Hero Content -->
                <div class="max-w-2xl">
                    <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-4">
                        Find Your Perfect Rental
                    </h1>
                    <p class="text-lg text-white/90 mb-8">
                        Discover amazing properties in Lusaka. From cozy apartments to spacious family homes, we have it all.
                    </p>
                    
                    <!-- Search Bar Placeholder or CTA -->
                    <div class="flex flex-col sm:flex-row gap-4">
                         <a href="#properties" class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-full text-brand-red bg-white hover:bg-gray-50 md:text-lg">
                            Browse Properties
                        </a>
                        <Link :href="route('landlord.apply')" class="inline-flex justify-center items-center px-6 py-3 border border-white text-base font-medium rounded-full text-white hover:bg-white/10 md:text-lg">
                            Become a Landlord
                        </Link>
                    </div>
                </div>
            </div>
             <!-- Decorative Curve -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" class="fill-gray-50 dark:fill-black w-full h-auto block">
                    <path d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,48C1120,43,1280,53,1360,58.7L1440,64L1440,320L1360,320C1280,320,1120,320,960,320C800,320,640,320,480,320C320,320,160,320,80,320L0,320Z"></path>
                </svg>
            </div>
        </div>

        <!-- Featured Properties -->
        <div id="properties" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Featured Properties</h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Handpicked properties just for you</p>
                </div>
                 <Link href="#" class="text-brand-red font-semibold hover:text-brand-orange flex items-center gap-1">
                    View All <ArrowRightIcon class="h-4 w-4" />
                </Link>
            </div>

            <div v-if="properties.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="property in properties" :key="property.id" class="bg-white dark:bg-light-bg rounded-2xl shadow-lg overflow-hidden transition hover:shadow-xl group">
                    <!-- Image -->
                    <div class="relative h-48 bg-gray-200">
                        <img 
                            v-if="property.images && property.images.length > 0" 
                            :src="'/storage/'+property.images[0].image_url" 
                            class="w-full h-full object-cover transition duration-500 group-hover:scale-110" 
                            alt="Property Image"
                        >
                        <div v-else class="w-full h-full flex items-center justify-center bg-gray-200 dark:bg-gray-700">
                            <HomeIcon class="h-12 w-12 text-gray-400" />
                        </div>
                        
                        <!-- Price Tag -->
                        <div class="absolute top-4 left-4 bg-brand-red text-white px-3 py-1 rounded-full text-sm font-bold shadow-md">
                            K{{ property.price.toLocaleString() }}/mo
                        </div>
                        
                         <!-- Type Tag -->
                        <div class="absolute top-4 right-4 bg-white/90 dark:bg-black/50 backdrop-blur-sm px-2 py-1 rounded-md text-xs font-semibold text-gray-800 dark:text-white capitalize">
                            {{ property.type }}
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-2">
                             <h3 class="text-lg font-bold text-gray-900 dark:text-white truncate pr-2">{{ property.title }}</h3>
                             <div class="flex items-center gap-1 text-sm bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">
                                <StarIcon class="h-3 w-3 text-amber-500" />
                                <span class="font-medium text-gray-700 dark:text-gray-300">New</span>
                            </div>
                        </div>
                       
                        <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400 text-sm mb-4">
                            <MapPinIcon class="h-4 w-4" />
                            <span class="truncate">{{ property.location }}</span>
                        </div>

                        <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700 pt-4">
                            <div class="flex gap-4">
                                <span>{{ property.bedrooms }} Beds</span>
                                <span>{{ property.bathrooms }} Baths</span>
                            </div>
                            <!-- Link to property show page -->
                            <Link :href="route('properties.show', property.id)" class="text-brand-red font-medium hover:text-brand-orange">
                                Details
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
             <div v-else class="text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                    <HomeIcon class="h-8 w-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">No properties yet</h3>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Check back soon for new listings!</p>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="bg-white dark:bg-light-bg border-t border-gray-200 dark:border-gray-800 mt-12 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-gray-500 dark:text-gray-400">&copy; {{ new Date().getFullYear() }} Rental App. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>
