<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import {
    HomeIcon, UserIcon, ClipboardDocumentListIcon, ChartBarIcon,
    Cog6ToothIcon, ArrowLeftOnRectangleIcon, BuildingOfficeIcon,
    ChatBubbleLeftRightIcon, WrenchScrewdriverIcon, CheckBadgeIcon,
    UsersIcon, FolderIcon, ShieldCheckIcon, DocumentTextIcon,
    ClockIcon, GlobeAltIcon, ChevronDownIcon, CreditCardIcon,
    BriefcaseIcon, CalendarDaysIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = computed(() => (page.props.auth as any).user);
const role = computed(() => user.value?.roleModel?.name || 'tenant');
const hasWorkerProfile = computed(() => user.value?.hasWorkerProfile ?? false);

const isActive = (pattern: string) => route().current(pattern);

const link = "flex items-center px-4 py-2.5 text-sm font-medium transition-colors duration-150 rounded-lg mx-2";
const active = "bg-brand-red/10 text-brand-red";
const inactive = "text-gray-400 hover:bg-gray-800 hover:text-white";

// Accordion — only one group open at a time; null = all collapsed
const defaultGroup: Record<string, string> = {
    admin: 'quick',
    applicant_landlord: 'applicant',
    landlord: 'landlord',
};
const openGroups = ref<Set<string>>(new Set([defaultGroup[role.value] ?? 'tenant']));

const toggle = (key: string) => {
    if (openGroups.value.has(key)) {
        openGroups.value.delete(key);
    } else {
        openGroups.value.add(key);
    }
    openGroups.value = new Set(openGroups.value); // trigger reactivity
};
const isOpen = (key: string) => openGroups.value.has(key);
</script>

<template>
    <div class="flex h-screen w-64 flex-col bg-dark-bg text-white shadow-xl overflow-hidden">

        <!-- Logo -->
        <div class="flex h-16 items-center justify-center border-b border-gray-800 flex-shrink-0">
            <Link :href="route('dashboard')" class="flex items-center gap-2">
                <ApplicationLogo class="h-8 w-8 fill-current text-brand-red" />
                <span class="text-xl font-bold tracking-wider">RENTAL<span class="text-brand-red">APP</span></span>
            </Link>
        </div>

        <!-- User Info -->
        <div class="border-b border-gray-800 p-4 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-light-bg text-brand-red flex-shrink-0">
                    <UserIcon class="h-5 w-5" />
                </div>
                <div class="overflow-hidden">
                    <p class="truncate text-sm font-medium text-white">{{ user.name }}</p>
                    <p class="truncate text-xs text-gray-500 capitalize">{{ role.replace(/_/g, ' ') }}</p>
                </div>
            </div>
        </div>

        <!-- Navigation — no scrollbar, fits within flex column -->
        <nav class="flex-1 overflow-hidden py-3 flex flex-col gap-0.5">

            <!-- ── ADMIN ─────────────────────────────────────────────── -->
            <template v-if="role === 'admin'">
                <Link :href="route('dashboard')" :class="[link, isActive('dashboard') ? active : inactive]">
                    <HomeIcon class="mr-3 h-5 w-5 flex-shrink-0" />Dashboard
                </Link>

                <!-- Quick Actions group -->
                <button @click="toggle('quick')"
                        class="flex items-center justify-between px-4 py-1.5 mx-2 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-300 transition-colors w-[calc(100%-1rem)]">
                    <span>Quick Actions</span>
                    <ChevronDownIcon class="h-3.5 w-3.5 transition-transform duration-200" :class="isOpen('quick') ? '' : '-rotate-90'" />
                </button>
                <template v-if="isOpen('quick')">
                    <Link :href="route('admin.applications.index')" :class="[link, isActive('admin.applications.*') ? active : inactive]">
                        <ClipboardDocumentListIcon class="mr-3 h-5 w-5 flex-shrink-0" />Applications
                    </Link>
                    <Link :href="route('admin.properties.index')" :class="[link, isActive('admin.properties.*') ? active : inactive]">
                        <CheckBadgeIcon class="mr-3 h-5 w-5 flex-shrink-0" />Properties
                    </Link>
                    <Link :href="route('admin.statistics.index')" :class="[link, isActive('admin.statistics.*') ? active : inactive]">
                        <ChartBarIcon class="mr-3 h-5 w-5 flex-shrink-0" />Statistics
                    </Link>
                </template>

                <!-- Management group -->
                <button @click="toggle('manage')"
                        class="flex items-center justify-between px-4 py-1.5 mx-2 mt-1 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-300 transition-colors w-[calc(100%-1rem)]">
                    <span>Management</span>
                    <ChevronDownIcon class="h-3.5 w-3.5 transition-transform duration-200" :class="isOpen('manage') ? '' : '-rotate-90'" />
                </button>
                <template v-if="isOpen('manage')">
                    <Link :href="route('admin.users.index')" :class="[link, isActive('admin.users.*') ? active : inactive]">
                        <UsersIcon class="mr-3 h-5 w-5 flex-shrink-0" />Manage Users
                    </Link>
                    <Link :href="route('admin.utilities.index')" :class="[link, isActive('admin.utilities.*') ? active : inactive]">
                        <WrenchScrewdriverIcon class="mr-3 h-5 w-5 flex-shrink-0" />Utilities
                    </Link>
                    <Link :href="route('admin.roles.index')" :class="[link, isActive('admin.roles.*') ? active : inactive]">
                        <ShieldCheckIcon class="mr-3 h-5 w-5 flex-shrink-0" />Roles &amp; Permissions
                    </Link>
                    <Link :href="route('admin.landlords.index')" :class="[link, isActive('admin.landlords.*') ? active : inactive]">
                        <BuildingOfficeIcon class="mr-3 h-5 w-5 flex-shrink-0" />Landlord Profiles
                    </Link>
                    <Link :href="route('admin.subscriptions.index')" :class="[link, isActive('admin.subscriptions.*') ? active : inactive]">
                        <CreditCardIcon class="mr-3 h-5 w-5 flex-shrink-0" />Subscriptions
                    </Link>
                </template>
            </template>

            <!-- ── APPLICANT LANDLORD ─────────────────────────────────── -->
            <template v-else-if="role === 'applicant_landlord'">
                <Link :href="route('dashboard')" :class="[link, isActive('dashboard') ? active : inactive]">
                    <HomeIcon class="mr-3 h-5 w-5 flex-shrink-0" />Dashboard
                </Link>

                <button @click="toggle('applicant')"
                        class="flex items-center justify-between px-4 py-1.5 mx-2 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-300 transition-colors w-[calc(100%-1rem)]">
                    <span>Application</span>
                    <ChevronDownIcon class="h-3.5 w-3.5 transition-transform duration-200" :class="isOpen('applicant') ? '' : '-rotate-90'" />
                </button>
                <template v-if="isOpen('applicant')">
                    <Link :href="route('landlord.status')" :class="[link, isActive('landlord.status') ? active : inactive]">
                        <ClockIcon class="mr-3 h-5 w-5 flex-shrink-0" />Application Status
                    </Link>
                    <Link :href="route('landlord.application.edit')" :class="[link, isActive('landlord.application.edit') ? active : inactive]">
                        <DocumentTextIcon class="mr-3 h-5 w-5 flex-shrink-0" />Update Application
                    </Link>
                    <Link :href="route('help-support')" :class="[link, isActive('help-support') ? active : inactive]">
                        <ChatBubbleLeftRightIcon class="mr-3 h-5 w-5 flex-shrink-0" />Help &amp; Support
                    </Link>
                </template>
            </template>

            <!-- ── LANDLORD ───────────────────────────────────────────── -->
            <template v-else-if="role === 'landlord'">
                <Link :href="route('landlord.dashboard')" :class="[link, isActive('landlord.dashboard') ? active : inactive]">
                    <HomeIcon class="mr-3 h-5 w-5 flex-shrink-0" />Dashboard
                </Link>

                <button @click="toggle('landlord')"
                        class="flex items-center justify-between px-4 py-1.5 mx-2 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-300 transition-colors w-[calc(100%-1rem)]">
                    <span>Property Management</span>
                    <ChevronDownIcon class="h-3.5 w-3.5 transition-transform duration-200" :class="isOpen('landlord') ? '' : '-rotate-90'" />
                </button>
                <template v-if="isOpen('landlord')">
                    <Link :href="route('landlord.properties.index')" :class="[link, isActive('landlord.properties.*') ? active : inactive]">
                        <BuildingOfficeIcon class="mr-3 h-5 w-5 flex-shrink-0" />My Properties
                    </Link>
                    <Link :href="route('landlord.property-applications.index')" :class="[link, isActive('landlord.property-applications.*') ? active : inactive]">
                        <ClipboardDocumentListIcon class="mr-3 h-5 w-5 flex-shrink-0" />Applications
                    </Link>
                    <Link :href="route('landlord.tour-requests.index')" :class="[link, isActive('landlord.tour-requests.*') ? active : inactive]">
                        <UsersIcon class="mr-3 h-5 w-5 flex-shrink-0" />Tour Requests
                    </Link>
                    <Link :href="route('landlord.maintenance.index')" :class="[link, isActive('landlord.maintenance.*') ? active : inactive]">
                        <WrenchScrewdriverIcon class="mr-3 h-5 w-5 flex-shrink-0" />Maintenance
                    </Link>
                    <Link :href="route('chat.index')" :class="[link, isActive('chat.*') ? active : inactive]">
                        <ChatBubbleLeftRightIcon class="mr-3 h-5 w-5 flex-shrink-0" />Messages
                    </Link>
                </template>
            </template>

            <!-- ── TENANT ─────────────────────────────────────────────── -->
            <template v-else>
                <Link :href="route('dashboard')" :class="[link, isActive('dashboard') ? active : inactive]">
                    <HomeIcon class="mr-3 h-5 w-5 flex-shrink-0" />Dashboard
                </Link>

                <button @click="toggle('tenant')"
                        class="flex items-center justify-between px-4 py-1.5 mx-2 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-300 transition-colors w-[calc(100%-1rem)]">
                    <span>My Space</span>
                    <ChevronDownIcon class="h-3.5 w-3.5 transition-transform duration-200" :class="isOpen('tenant') ? '' : '-rotate-90'" />
                </button>
                <template v-if="isOpen('tenant')">
                    <Link :href="route('tenant.applications.index')" :class="[link, isActive('tenant.applications.*') ? active : inactive]">
                        <ClipboardDocumentListIcon class="mr-3 h-5 w-5 flex-shrink-0" />My Applications
                    </Link>
                    <Link :href="route('tenant.my-rentals.index')" :class="[link, isActive('tenant.my-rentals.*') ? active : inactive]">
                        <BuildingOfficeIcon class="mr-3 h-5 w-5 flex-shrink-0" />My Rentals
                    </Link>
                    <Link :href="route('chat.index')" :class="[link, isActive('chat.*') ? active : inactive]">
                        <ChatBubbleLeftRightIcon class="mr-3 h-5 w-5 flex-shrink-0" />Messages
                    </Link>
                    <Link :href="route('tenant.maintenance.index')" :class="[link, isActive('tenant.maintenance.*') ? active : inactive]">
                        <WrenchScrewdriverIcon class="mr-3 h-5 w-5 flex-shrink-0" />Maintenance
                    </Link>
                    <Link :href="route('rental-history.index')" :class="[link, isActive('rental-history.*') ? active : inactive]">
                        <FolderIcon class="mr-3 h-5 w-5 flex-shrink-0" />History
                    </Link>
                </template>
            </template>

            <!-- ── MARKETPLACE (all roles) ───────────────────────────── -->
            <button @click="toggle('marketplace')"
                    class="flex items-center justify-between px-4 py-1.5 mx-2 mt-1 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-300 transition-colors w-[calc(100%-1rem)]">
                <span>Marketplace</span>
                <ChevronDownIcon class="h-3.5 w-3.5 transition-transform duration-200" :class="isOpen('marketplace') ? '' : '-rotate-90'" />
            </button>
            <template v-if="isOpen('marketplace')">
                <Link :href="route('marketplace.workers.index')" :class="[link, isActive('marketplace.workers.*') ? active : inactive]">
                    <BriefcaseIcon class="mr-3 h-5 w-5 flex-shrink-0" />Find Workers
                </Link>
                <Link :href="route('marketplace.bookings.index')" :class="[link, isActive('marketplace.bookings.*') ? active : inactive]">
                    <CalendarDaysIcon class="mr-3 h-5 w-5 flex-shrink-0" />My Bookings
                </Link>
                <Link v-if="hasWorkerProfile" :href="route('worker.dashboard')" :class="[link, isActive('worker.*') ? active : inactive]">
                    <UserIcon class="mr-3 h-5 w-5 flex-shrink-0" />Worker Dashboard
                </Link>
                <Link v-else :href="route('worker.profile.create')" :class="[link, inactive]">
                    <UserIcon class="mr-3 h-5 w-5 flex-shrink-0" />Become a Worker
                </Link>
                <Link v-if="role === 'admin'" :href="route('admin.workers.index')" :class="[link, isActive('admin.workers.*') ? active : inactive]">
                    <ShieldCheckIcon class="mr-3 h-5 w-5 flex-shrink-0" />Manage Workers
                </Link>
            </template>

            <!-- ── Bottom links (always visible) ─────────────────────── -->
            <div class="mt-auto pt-3 border-t border-gray-800 flex flex-col gap-0.5">
                <Link :href="route('landing')" :class="[link, isActive('landing') ? active : inactive]">
                    <GlobeAltIcon class="mr-3 h-5 w-5 flex-shrink-0" />Home
                </Link>
                <Link :href="route('profile.edit')" :class="[link, isActive('profile.edit') ? active : inactive]">
                    <UserIcon class="mr-3 h-5 w-5 flex-shrink-0" />Profile
                </Link>
                <Link v-if="role === 'admin'" :href="route('admin.settings.index')" :class="[link, isActive('admin.settings.*') ? active : inactive]">
                    <Cog6ToothIcon class="mr-3 h-5 w-5 flex-shrink-0" />Settings
                </Link>
                <Link :href="route('logout')" method="post" as="button"
                      class="flex items-center rounded-lg mx-2 px-4 py-2.5 text-sm font-medium text-gray-400 hover:bg-red-900/20 hover:text-red-400 transition-colors">
                    <ArrowLeftOnRectangleIcon class="mr-3 h-5 w-5 flex-shrink-0" />Logout
                </Link>
            </div>
        </nav>
    </div>
</template>
