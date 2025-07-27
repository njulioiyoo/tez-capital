<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useBranding } from '@/composables/useBranding';

const { companyLogo, companyName } = useBranding();
</script>

<template>
    <div class="flex size-8 items-center justify-center rounded-md overflow-hidden">
        <img 
            v-if="companyLogo" 
            :src="companyLogo" 
            :alt="companyName + ' Logo'"
            class="size-8 object-contain"
        />
        <div 
            v-else 
            class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
        >
            <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
        </div>
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">{{ companyName }}</span>
    </div>
</template>
