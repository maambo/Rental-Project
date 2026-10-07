<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, watch } from 'vue';
import { MagnifyingGlassIcon, PencilSquareIcon, TrashIcon, UserPlusIcon, ArrowRightOnRectangleIcon, EyeIcon } from '@heroicons/vue/24/outline';
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
        route('admin.users.index'),
        { search: search.value, role: role.value },
        { preserveState: true, replace: true }
    );
}, 300));

const deleteUser = (id: number) => {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        router.delete(route('admin.users.destroy', id));
    }
};

const loginAs = (id: number) => {
    if (confirm('Are you sure you want to log in as this user?')) {
        router.post(route('admin.users.login-as', id));
    }
};
</script>

<template>
    <Head title="Manage Users" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">
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
                        class="block w-full max-w-[150px] rounded-lg border-gray-600 bg-gray-700 text-gray-200 shadow-sm focus:border-brand-orange focus:ring-brand-orange"
                    >
                        <option value="all">All Roles</option>
                        <option v-for="r in props.roles" :key="r.id" :value="r.name">{{ r.name.charAt(0).toUpperCase() + r.name.slice(1) }}</option>
                    </select>
                </div>

                <Link :href="route('admin.users.create')">
                    <PrimaryButton>
                        <UserPlusIcon class="mr-2 h-5 w-5" />
                        Add User
                    </PrimaryButton>
                </Link>
            </div>

            <!-- Table -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Role</th>
                            <th class="px-4 py-3 font-medium">Created At</th>
                            <th class="px-4 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="hover:bg-gray-700/40 cursor-pointer"
                            @click="router.visit(route('admin.users.show', user.id))"
                        >
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img
                                        :src="user.avatar_url"
                                        :alt="user.name"
                                        class="h-8 w-8 rounded-full object-cover bg-gray-700 flex-shrink-0"
                                    />
                                    <div class="font-medium text-white">{{ user.name }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-gray-400">{{ user.email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs capitalize"
                                    :class="{
                                        'bg-purple-500/20 text-purple-400': user.role_model?.name === 'admin',
                                        'bg-blue-500/20 text-blue-400': user.role_model?.name === 'landlord',
                                        'bg-green-500/20 text-green-400': user.role_model?.name === 'tenant',
                                        'bg-gray-500/20 text-gray-400': !['admin', 'landlord', 'tenant'].includes(user.role_model?.name)
                                    }"
                                >
                                    {{ user.role_model?.name || 'No Role' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-400">
                                {{ new Date(user.created_at).toLocaleDateString() }}
                            </td>
                            <td class="px-4 py-3 text-right" @click.stop>
                                <div class="flex justify-end gap-3">
                                    <Link :href="route('admin.users.show', user.id)" class="text-gray-400 hover:text-white" title="View Details">
                                        <EyeIcon class="h-5 w-5" />
                                    </Link>
                                    <button @click="loginAs(user.id)" class="text-green-400 hover:text-green-300" title="Login As User">
                                        <ArrowRightOnRectangleIcon class="h-5 w-5" />
                                    </button>
                                    <Link :href="route('admin.users.edit', user.id)" class="text-brand-red hover:text-brand-orange" title="Edit User">
                                        <PencilSquareIcon class="h-5 w-5" />
                                    </Link>
                                    <button @click="deleteUser(user.id)" class="text-red-400 hover:text-red-300" title="Delete User">
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
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
