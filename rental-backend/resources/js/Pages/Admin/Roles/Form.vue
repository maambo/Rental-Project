<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

const props = defineProps<{
    permissions: Record<string, Array<{
        id: number;
        name: string;
        display_name: string;
        description: string;
    }>>;
    role?: {
        id: number;
        name: string;
        display_name: string;
        description: string;
    } | null;
    rolePermissions?: number[];
}>();

const form = useForm({
    name: props.role?.name || '',
    display_name: props.role?.display_name || '',
    description: props.role?.description || '',
    permissions: props.rolePermissions || [],
});

const submit = () => {
    if (props.role) {
        form.put(route('admin.roles.update', props.role.id));
    } else {
        form.post(route('admin.roles.store'));
    }
};

const togglePermission = (permissionId: number) => {
    const index = form.permissions.indexOf(permissionId);
    if (index > -1) {
        form.permissions.splice(index, 1);
    } else {
        form.permissions.push(permissionId);
    }
};

const toggleGroup = (groupPermissions: Array<any>) => {
    const groupIds = groupPermissions.map(p => p.id);
    const allSelected = groupIds.every(id => form.permissions.includes(id));
    
    if (allSelected) {
        form.permissions = form.permissions.filter(id => !groupIds.includes(id));
    } else {
        groupIds.forEach(id => {
            if (!form.permissions.includes(id)) {
                form.permissions.push(id);
            }
        });
    }
};
</script>

<template>
    <Head :title="role ? 'Edit Role' : 'Create Role'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ role ? 'Edit Role' : 'Create New Role' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Basic Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="name" value="Role Name (slug)" />
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                    placeholder="e.g., admin, landlord"
                                    required
                                />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="display_name" value="Display Name" />
                                <input
                                    id="display_name"
                                    v-model="form.display_name"
                                    type="text"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                    placeholder="e.g., Administrator"
                                    required
                                />
                                <InputError :message="form.errors.display_name" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="description" value="Description" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                            ></textarea>
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>

                        <!-- Permissions -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Assign Permissions</h3>
                            
                            <div v-for="(groupPerms, group) in permissions" :key="group" class="mb-6">
                                <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-200 dark:border-gray-700">
                                    <h4 class="font-semibold text-gray-700 dark:text-gray-300 capitalize">{{ group || 'Other' }}</h4>
                                    <button
                                        type="button"
                                        @click="toggleGroup(groupPerms)"
                                        class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                    >
                                        {{ groupPerms.every(p => form.permissions.includes(p.id)) ? 'Deselect All' : 'Select All' }}
                                    </button>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <label
                                        v-for="permission in groupPerms"
                                        :key="permission.id"
                                        class="flex items-start space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="form.permissions.includes(permission.id)"
                                            @change="togglePermission(permission.id)"
                                            class="mt-1 rounded border-gray-300 text-brand-red focus:ring-brand-red"
                                        />
                                        <div class="flex-1">
                                            <div class="font-medium text-sm text-gray-900 dark:text-gray-100">
                                                {{ permission.display_name }}
                                            </div>
                                            <div v-if="permission.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ permission.description }}
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a :href="route('admin.roles.index')" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                Cancel
                            </a>
                            <PrimaryButton :disabled="form.processing">
                                {{ role ? 'Update Role' : 'Create Role' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
