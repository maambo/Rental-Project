<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, watch } from 'vue';
import { MagnifyingGlassIcon, PencilSquareIcon, TrashIcon, UserPlusIcon } from '@heroicons/vue/24/outline';
import { debounce } from 'lodash';

const props = defineProps<{
    users: {
        data: Array<any>;
        links: Array<any>;
    };
    roles: Array<any>;
    filters: {
        search: string;
        role: string;
    };
}>();

const search = ref(props.filters.search || '');
const role = ref(props.filters.role || 'all');
const roles = props.roles; // Make roles available to template explicitly if needed, or use props.roles in template

watch([search, role], debounce(() => {
    router.get(
        route('users.index'),
        { search: search.value, role: role.value },
        { preserveState: true, replace: true }
    );
}, 300));

const deleteUser = (id: number) => {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        router.delete(route('users.destroy', id));
    }
};
</script>

<template>
    <Head title="Manage Users" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Manage Users
            </h2>
        </template>

        <div class="space-y-6">
            <!-- Actions Bar -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 gap-4">
                    <!-- Search -->
                    <div class="relative w-full max-w-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <TextInput
                            v-model="search"
                            type="text"
                            placeholder="Search users..."
                            class="pl-10 block w-full"
                        />
                    </div>
                    
                    <!-- Role Filter -->
                     <select
                        v-model="role"
                        class="block w-full max-w-[150px] rounded-md border-gray-300 shadow-sm focus:border-brand-orange focus:ring-brand-orange dark:border-gray-700 dark:bg-dark-bg dark:text-gray-300 dark:focus:border-brand-orange dark:focus:ring-brand-orange"
                    >
                        <option value="all">All Roles</option>
                        <option v-for="r in props.roles" :key="r.id" :value="r.name">{{ r.name.charAt(0).toUpperCase() + r.name.slice(1) }}</option>
                    </select>
                </div>

                <Link :href="route('users.create')">
                    <PrimaryButton>
                        <UserPlusIcon class="mr-2 h-5 w-5" />
                        Add User
                    </PrimaryButton>
                </Link>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-light-bg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Role</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Created At</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-light-bg">
                        <tr v-for="user in users.data" :key="user.id" class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ user.name }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 capitalize"
                                    :class="{
                                        'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300': user.role_model?.name === 'admin',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300': user.role_model?.name === 'landlord',
                                        'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300': user.role_model?.name === 'tenant',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': !['admin', 'landlord', 'tenant'].includes(user.role_model?.name)
                                    }"
                                >
                                    {{ user.role_model?.name || 'No Role' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ new Date(user.created_at).toLocaleDateString() }}
                            </td>
                             <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-3">
                                    <Link :href="route('users.edit', user.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-blue-400 dark:hover:text-blue-300">
                                        <PencilSquareIcon class="h-5 w-5" />
                                    </Link>
                                    <button @click="deleteUser(user.id)" class="text-brand-red hover:text-red-900 dark:hover:text-red-300">
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                         <tr v-if="users.data.length === 0">
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No users found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center">
                <Pagination :links="users.links" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
