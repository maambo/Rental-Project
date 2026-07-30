<script setup lang="ts">
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
</script>

<template>
    <div class="bg-brand-warning/10 border-l-4 border-brand-warning p-4 mb-6 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <ExclamationTriangleIcon class="h-5 w-5 text-brand-warning" aria-hidden="true" />
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-brand-warning">Safety Warning</h3>
                <div class="mt-1 text-sm text-yellow-300">
                    <p>
                        Never pay any money before physically viewing the property. 
                        If a landlord asks for "booking fees" or "transport money" before meeting, 
                        <span class="font-bold">it is a scam.</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
