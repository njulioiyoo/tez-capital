<template>
  <div 
    v-if="toastState.items.length > 0"
    class="fixed bottom-4 left-1/2 transform -translate-x-1/2 z-[9999] pointer-events-none"
  >
    <div class="flex flex-col gap-2 pointer-events-auto">
      <div
        v-for="toast in toastState.items"
        :key="toast.id"
        :class="[
          'flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg border max-w-md min-w-80',
          'backdrop-blur-sm transition-all duration-300',
          toast.variant === 'destructive' 
            ? 'bg-red-50/95 border-red-200 text-red-800 dark:bg-red-950/95 dark:border-red-800 dark:text-red-100'
            : 'bg-white/95 border-gray-200 text-gray-900 dark:bg-gray-950/95 dark:border-gray-800 dark:text-gray-100'
        ]"
      >
        <!-- Icon -->
        <div class="flex-shrink-0">
          <svg 
            v-if="toast.variant !== 'destructive'" 
            class="w-5 h-5 text-green-600 dark:text-green-400" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg 
            v-else 
            class="w-5 h-5 text-red-600 dark:text-red-400" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        
        <!-- Content -->
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium">{{ toast.title }}</p>
          <p v-if="toast.description" class="text-sm opacity-80 mt-1">{{ toast.description }}</p>
        </div>
        
        <!-- Close button -->
        <button
          @click="removeToast(toast.id)"
          class="flex-shrink-0 p-1 rounded-md hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toastState, removeToast } from '@/composables/useToastSimple'

// Debug: Watch for changes
import { watch } from 'vue'
watch(() => toastState.items.length, (newLength) => {
  console.log('ToastContainer: items length changed to:', newLength)
})
</script>