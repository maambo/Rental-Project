<script setup lang="ts">
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
</script>

<template>
    <div class="bg-brand-gold/10 border-l-4 border-brand-gold p-4 mb-6 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <ExclamationTriangleIcon class="h-5 w-5 text-brand-gold" aria-hidden="true" />
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-brand-gold">Safety Warning</h3>
                <div class="mt-1 text-sm text-yellow-700 dark:text-yellow-600">
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
