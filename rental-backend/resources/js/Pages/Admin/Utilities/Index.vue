<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { PlusIcon, TrashIcon, PencilIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface UtilityOption { id: number; label: string; value: string; sort_order: number; }
interface UtilityType   { id: number; name: string; icon: string | null; is_active: boolean; sort_order: number; options: UtilityOption[]; }

const props = defineProps<{ utilityTypes: UtilityType[] }>();

// ── Create utility type ──────────────────────────────────────────────
const showCreateForm = ref(false);
const createForm = useForm({ name: '', icon: '', sort_order: 0 });

function createType() {
    createForm.post(route('admin.utilities.store'), {
        onSuccess: () => { createForm.reset(); showCreateForm.value = false; },
    });
}

// ── Edit utility type inline ─────────────────────────────────────────
const editingTypeId = ref<number | null>(null);
const editForm = useForm({ name: '', icon: '', sort_order: 0 });

function startEditType(ut: UtilityType) {
    editingTypeId.value = ut.id;
    editForm.name       = ut.name;
    editForm.icon       = ut.icon ?? '';
    editForm.sort_order = ut.sort_order;
}

function saveType(ut: UtilityType) {
    editForm.put(route('admin.utilities.update', ut.id), {
        onSuccess: () => { editingTypeId.value = null; },
    });
}

function cancelEditType() { editingTypeId.value = null; }

function destroyType(ut: UtilityType) {
    if (!confirm(`Deactivate "${ut.name}"? Existing property data will be preserved.`)) return;
    router.delete(route('admin.utilities.destroy', ut.id));
}

// ── Add option ───────────────────────────────────────────────────────
const addingOptionForType = ref<number | null>(null);
const optionForm = useForm({ label: '', value: '', sort_order: 0 });

function startAddOption(typeId: number) {
    addingOptionForType.value = typeId;
    optionForm.reset();
}

function saveOption(typeId: number) {
    optionForm.post(route('admin.utilities.options.store', typeId), {
        onSuccess: () => { addingOptionForType.value = null; },
    });
}

function destroyOption(optionId: number) {
    if (!confirm('Remove this option?')) return;
    router.delete(route('admin.utilities.options.destroy', optionId));
}
</script>

<template>
    <Head title="Utility Types" />

    <AuthenticatedLayout header="Utility Types" :back-url="route('admin.settings.index')">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Header action -->
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Configure the utility types and their selectable options that landlords can assign to properties.
                    </p>
                    <button @click="showCreateForm = !showCreateForm" type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-brand-red text-white text-sm font-semibold rounded-md hover:bg-red-700 transition-colors">
                        <PlusIcon class="w-4 h-4" />
                        Add Utility Type
                    </button>
                </div>

                <!-- Create form -->
                <div v-if="showCreateForm" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">New Utility Type</h3>
                    <form @submit.prevent="createType" class="flex flex-wrap gap-3 items-end">
                        <div class="flex-1 min-w-40">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Name *</label>
                            <input v-model="createForm.name" type="text" required placeholder="e.g., Power"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm focus:border-brand-red focus:ring-brand-red" />
                            <InputError :message="createForm.errors.name" class="mt-0.5" />
                        </div>
                        <div class="w-24">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Icon</label>
                            <input v-model="createForm.icon" type="text" placeholder="⚡"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm focus:border-brand-red focus:ring-brand-red" />
                        </div>
                        <div class="w-24">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Order</label>
                            <input v-model.number="createForm.sort_order" type="number" min="0"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm focus:border-brand-red focus:ring-brand-red" />
                        </div>
                        <button type="submit" :disabled="createForm.processing"
                            class="px-4 py-2 bg-brand-red text-white text-sm font-semibold rounded-md hover:bg-red-700 disabled:opacity-50">
                            Create
                        </button>
                        <button type="button" @click="showCreateForm = false"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-md">
                            Cancel
                        </button>
                    </form>
                </div>

                <!-- Utility type cards -->
                <div v-if="!utilityTypes.length" class="text-center py-16 text-gray-400 dark:text-gray-500 text-sm">
                    No utility types yet. Add one above.
                </div>

                <div v-for="ut in utilityTypes" :key="ut.id"
                    class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden"
                    :class="!ut.is_active ? 'opacity-50' : ''">

                    <!-- Type header -->
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                        <span v-if="ut.icon" class="text-xl">{{ ut.icon }}</span>

                        <!-- Inline edit -->
                        <template v-if="editingTypeId === ut.id">
                            <input v-model="editForm.name" type="text"
                                class="flex-1 text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red" />
                            <input v-model="editForm.icon" type="text" placeholder="Icon" class="w-16 text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red" />
                            <input v-model.number="editForm.sort_order" type="number" class="w-16 text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-red focus:ring-brand-red" />
                            <button @click="saveType(ut)" type="button" class="p-1.5 rounded-md bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 hover:bg-green-200">
                                <CheckIcon class="w-4 h-4" />
                            </button>
                            <button @click="cancelEditType" type="button" class="p-1.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200">
                                <XMarkIcon class="w-4 h-4" />
                            </button>
                        </template>

                        <template v-else>
                            <span class="flex-1 text-sm font-semibold text-gray-900 dark:text-white">{{ ut.name }}</span>
                            <span v-if="!ut.is_active" class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 px-2 py-0.5 rounded-full">Inactive</span>
                            <button @click="startEditType(ut)" type="button"
                                class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <PencilIcon class="w-4 h-4" />
                            </button>
                            <button @click="destroyType(ut)" type="button"
                                class="p-1.5 rounded-md text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30">
                                <TrashIcon class="w-4 h-4" />
                            </button>
                        </template>
                    </div>

                    <!-- Options list -->
                    <div class="px-5 py-4 space-y-2">
                        <div v-if="!ut.options.length && addingOptionForType !== ut.id"
                            class="text-xs text-gray-400 italic">No options yet.</div>

                        <div v-for="opt in ut.options" :key="opt.id"
                            class="flex items-center gap-3 py-1.5">
                            <span class="flex-1 text-sm text-gray-800 dark:text-gray-200">{{ opt.label }}</span>
                            <span class="text-xs text-gray-400 font-mono">{{ opt.value }}</span>
                            <button @click="destroyOption(opt.id)" type="button"
                                class="p-1 rounded text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <XMarkIcon class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- Add option inline form -->
                        <template v-if="addingOptionForType === ut.id">
                            <div class="flex flex-wrap gap-2 items-end pt-2">
                                <div class="flex-1 min-w-36">
                                    <label class="block text-xs font-medium text-gray-500 mb-0.5">Label *</label>
                                    <input v-model="optionForm.label" type="text" required placeholder="e.g., ZESCO"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm focus:border-brand-red focus:ring-brand-red" />
                                    <InputError :message="optionForm.errors.label" class="mt-0.5" />
                                </div>
                                <div class="w-32">
                                    <label class="block text-xs font-medium text-gray-500 mb-0.5">Value *</label>
                                    <input v-model="optionForm.value" type="text" required placeholder="e.g., zesco"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm text-sm focus:border-brand-red focus:ring-brand-red" />
                                    <InputError :message="optionForm.errors.value" class="mt-0.5" />
                                </div>
                                <button @click="saveOption(ut.id)" :disabled="optionForm.processing" type="button"
                                    class="px-3 py-2 bg-brand-red text-white text-xs font-semibold rounded-md hover:bg-red-700 disabled:opacity-50">
                                    Add
                                </button>
                                <button @click="addingOptionForType = null" type="button"
                                    class="px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-semibold rounded-md">
                                    Cancel
                                </button>
                            </div>
                        </template>

                        <button v-else @click="startAddOption(ut.id)" type="button"
                            class="mt-1 inline-flex items-center gap-1 text-xs text-brand-red hover:text-red-700 font-medium">
                            <PlusIcon class="w-3 h-3" />
                            Add option
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
