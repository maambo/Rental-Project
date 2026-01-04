<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import {
    HomeIcon,
    UserIcon,
    ClipboardDocumentListIcon,
    ChartBarIcon,
    Cog6ToothIcon,
    ArrowLeftOnRectangleIcon,
    BuildingOfficeIcon,
    ChatBubbleLeftRightIcon,
    WrenchScrewdriverIcon,
    CurrencyDollarIcon,
    CheckBadgeIcon,
    UsersIcon,
    FolderIcon,
    ShieldCheckIcon,
    DocumentTextIcon,
    ClockIcon
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = computed(() => page.props.auth.user);
const role = computed(() => user.value?.roleModel?.name || 'tenant');

const isActive = (routePattern: string) => {
    return route().current(routePattern);
};

const commonClasses = "flex items-center px-4 py-3 text-sm font-medium transition-colors duration-200";
const activeClasses = "border-r-4 border-brand-red bg-brand-red/10 text-brand-red";
const inactiveClasses = "text-gray-400 hover:bg-gray-800 hover:text-white border-r-4 border-transparent";

</script>

<template>
    <div class="flex h-screen w-64 flex-col bg-dark-bg text-white shadow-xl">
        <!-- Logo -->
        <div class="flex h-16 items-center justify-center border-b border-gray-800">
            <Link :href="route('dashboard')" class="flex items-center gap-2">
                <ApplicationLogo class="h-8 w-8 fill-current text-brand-red" />
                <span class="text-xl font-bold tracking-wider">RENTAL<span class="text-brand-red">APP</span></span>
            </Link>
        </div>

        <!-- User Info -->
        <div class="border-b border-gray-800 p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-light-bg text-brand-orange">
                    <UserIcon class="h-6 w-6" />
                </div>
                <div class="overflow-hidden">
                    <p class="truncate text-sm font-medium text-white">{{ user.name }}</p>
                    <p class="truncate text-xs text-gray-500 capitalize">{{ role.replace('_', ' ') }}</p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 space-y-1">
            
            <!-- ADMIN MENU -->
            <template v-if="role === 'admin'">
                <Link :href="route('dashboard')" :class="[commonClasses, isActive('dashboard') ? activeClasses : inactiveClasses]">
                    <HomeIcon class="mr-3 h-5 w-5" />
                    Dashboard
                </Link>
                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Quick Actions</div>
                <Link :href="route('admin.applications.index')" :class="[commonClasses, isActive('admin.applications.*') ? activeClasses : inactiveClasses]">
                    <ClipboardDocumentListIcon class="mr-3 h-5 w-5" />
                    Applications
                </Link>
                 <Link :href="route('admin.properties.index')" :class="[commonClasses, isActive('admin.properties.*') ? activeClasses : inactiveClasses]">
                    <CheckBadgeIcon class="mr-3 h-5 w-5" />
                    Approve Properties
                </Link>
                 <Link :href="route('admin.statistics.index')" :class="[commonClasses, isActive('admin.statistics.*') ? activeClasses : inactiveClasses]">
                    <ChartBarIcon class="mr-3 h-5 w-5" />
                    Statistics
                </Link>
                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Management</div>
                <Link :href="route('users.index')" :class="[commonClasses, isActive('users.*') ? activeClasses : inactiveClasses]">
                    <UsersIcon class="mr-3 h-5 w-5" />
                    Manage Users
                </Link>
                <Link :href="route('admin.roles.index')" :class="[commonClasses, isActive('admin.roles.*') ? activeClasses : inactiveClasses]">
                    <ShieldCheckIcon class="mr-3 h-5 w-5" />
                    Roles & Permissions
                </Link>
                 <Link :href="route('admin.landlords.index')" :class="[commonClasses, isActive('admin.landlords.*') ? activeClasses : inactiveClasses]">
                    <BuildingOfficeIcon class="mr-3 h-5 w-5" />
                    Landlord Profiles
                </Link>
            </template>

            <!-- APPLICANT LANDLORD MENU -->
            <template v-else-if="role === 'applicant_landlord'">
                <Link :href="route('dashboard')" :class="[commonClasses, isActive('dashboard') ? activeClasses : inactiveClasses]">
                    <HomeIcon class="mr-3 h-5 w-5" />
                    Dashboard
                </Link>
                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Application</div>
                <Link :href="route('landlord.status')" :class="[commonClasses, isActive('landlord.status') ? activeClasses : inactiveClasses]">
                    <ClockIcon class="mr-3 h-5 w-5" />
                    Application Status
                </Link>
                <Link :href="route('landlord.application.edit')" :class="[commonClasses, isActive('landlord.application.edit') ? activeClasses : inactiveClasses]">
                    <DocumentTextIcon class="mr-3 h-5 w-5" />
                    Update Application
                </Link>
                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Information</div>
                <Link href="#" :class="[commonClasses, inactiveClasses]">
                    <BuildingOfficeIcon class="mr-3 h-5 w-5" />
                    Tier Benefits
                </Link>
                <Link href="#" :class="[commonClasses, inactiveClasses]">
                    <ChatBubbleLeftRightIcon class="mr-3 h-5 w-5" />
                    Help & Support
                </Link>
            </template>

            <!-- LANDLORD MENU -->
            <template v-else-if="role === 'landlord'">
                 <Link :href="route('landlord.dashboard')" :class="[commonClasses, isActive('landlord.dashboard') ? activeClasses : inactiveClasses]">
                    <HomeIcon class="mr-3 h-5 w-5" />
                    Dashboard
                </Link>
                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Property Management</div>
                <Link :href="route('landlord.properties.index')" :class="[commonClasses, isActive('landlord.properties.*') ? activeClasses : inactiveClasses]">
                    <BuildingOfficeIcon class="mr-3 h-5 w-5" />
                    My Properties
                </Link>
                <Link href="#" :class="[commonClasses, inactiveClasses]">
                    <UsersIcon class="mr-3 h-5 w-5" />
                    Tour Requests
                </Link>
                <Link href="#" :class="[commonClasses, inactiveClasses]">
                    <WrenchScrewdriverIcon class="mr-3 h-5 w-5" />
                    Maintenance
                </Link>
                 <Link href="#" :class="[commonClasses, inactiveClasses]">
                    <ChatBubbleLeftRightIcon class="mr-3 h-5 w-5" />
                    Messages
                </Link>
            </template>

            <!-- TENANT MENU -->
            <template v-else>
                 <Link :href="route('dashboard')" :class="[commonClasses, isActive('dashboard') ? activeClasses : inactiveClasses]">
                    <HomeIcon class="mr-3 h-5 w-5" />
                    Dashboard
                </Link>
                <Link href="#" :class="[commonClasses, inactiveClasses]">
                    <BuildingOfficeIcon class="mr-3 h-5 w-5" />
                    My Rentals
                </Link>
                <Link href="#" :class="[commonClasses, inactiveClasses]">
                    <ChatBubbleLeftRightIcon class="mr-3 h-5 w-5" />
                    Messages
                </Link>
                <Link href="#" :class="[commonClasses, inactiveClasses]">
                    <WrenchScrewdriverIcon class="mr-3 h-5 w-5" />
                    Maintenance
                </Link>
                <Link href="#" :class="[commonClasses, inactiveClasses]">
                    <FolderIcon class="mr-3 h-5 w-5" />
                    History
                </Link>
            </template>

            <div class="my-4 border-t border-gray-800"></div>
            
             <Link :href="route('profile.edit')" :class="[commonClasses, isActive('profile.edit') ? activeClasses : inactiveClasses]">
                <UserIcon class="mr-3 h-5 w-5" />
                Profile
            </Link>
             <Link v-if="role === 'admin'" :href="route('admin.settings.index')" :class="[commonClasses, isActive('admin.settings.*') ? activeClasses : inactiveClasses]">
                <Cog6ToothIcon class="mr-3 h-5 w-5" />
                Settings
            </Link>
        </nav>

        <!-- Logout -->
        <div class="border-t border-gray-800 p-4">
            <Link :href="route('logout')" method="post" as="button" class="flex w-full items-center rounded-lg px-4 py-2 text-sm font-medium text-gray-400 hover:bg-red-900/20 hover:text-red-500 transition-colors">
                <ArrowLeftOnRectangleIcon class="mr-3 h-5 w-5" />
                Logout
            </Link>
        </div>
    </div>
</template>
