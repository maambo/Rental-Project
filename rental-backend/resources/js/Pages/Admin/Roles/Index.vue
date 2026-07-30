<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { TrashIcon, PencilSquareIcon, PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    roles: Array<{
        id: number;
        name: string;
        display_name: string;
        description: string;
        permissions: Array<any>;
    }>;
}>();

const deleteRole = (roleId: number, roleName: string) => {
    if (confirm(`Are you sure you want to delete the role "${roleName}"?`)) {
        router.delete(route('admin.roles.destroy', roleId));
    }
};
</script>

<template>
    <Head title="Manage Roles" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <h2 class="text-xl font-semibold text-white">Role Management</h2>
                <Link :href="route('admin.roles.create')" class="inline-flex items-center px-4 py-2 bg-brand-red text-white rounded-lg hover:bg-red-700 transition-colors">
                    <PlusIcon class="w-5 h-5 mr-2" />
                    Create Role
                </Link>
            </div>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-gray-400 text-left">
                            <th class="px-4 py-3 font-medium">Role Name</th>
                            <th class="px-4 py-3 font-medium">Display Name</th>
                            <th class="px-4 py-3 font-medium">Description</th>
                            <th class="px-4 py-3 font-medium">Permissions</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="role in roles" :key="role.id" class="hover:bg-gray-700/40">
                            <td class="px-4 py-3 font-medium text-white">
                                {{ role.name }}
                            </td>
                            <td class="px-4 py-3 text-gray-400">
                                {{ role.display_name }}
                            </td>
                            <td class="px-4 py-3 text-gray-400">
                                {{ role.description || '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 bg-brand-info/20 text-brand-info rounded-full text-xs">
                                    {{ role.permissions.length }} permissions
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Link :href="route('admin.roles.edit', role.id)" class="text-brand-red hover:text-brand-orange mr-4">
                                    <PencilSquareIcon class="w-5 h-5 inline" />
                                </Link>
                                <button
                                    v-if="role.name !== 'admin'"
                                    @click="deleteRole(role.id, role.display_name)"
                                    class="text-red-400 hover:text-red-300">
                                    <TrashIcon class="w-5 h-5 inline" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
