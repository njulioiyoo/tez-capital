<template>
  <Teleport to="body">
    <div class="fixed bottom-4 left-1/2 transform -translate-x-1/2 z-50">
      <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-2 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-2 scale-95"
      >
        <div
          v-if="visible"
          :class="[
            'flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg border max-w-md min-w-80',
            'backdrop-blur-sm',
            variant === 'destructive' 
              ? 'bg-red-50/90 border-red-200 text-red-800 dark:bg-red-950/90 dark:border-red-800 dark:text-red-100'
              : 'bg-white/90 border-gray-200 text-gray-900 dark:bg-gray-950/90 dark:border-gray-800 dark:text-gray-100'
          ]"
        >
          <!-- Icon -->
          <div class="flex-shrink-0">
            <CheckCircle 
              v-if="variant === 'default'" 
              class="w-5 h-5 text-green-600 dark:text-green-400" 
            />
            <XCircle 
              v-else 
              class="w-5 h-5 text-red-600 dark:text-red-400" 
            />
          </div>
          
          <!-- Content -->
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium">{{ title }}</p>
            <p v-if="description" class="text-sm opacity-80 mt-1">{{ description }}</p>
          </div>
          
          <!-- Close button -->
          <button
            @click="close"
            class="flex-shrink-0 p-1 rounded-md hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
      </Transition>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { CheckCircle, XCircle, X } from 'lucide-vue-next'

interface Props {
  title: string
  description?: string
  variant?: 'default' | 'destructive'
  duration?: number
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  duration: 5000
})

const emit = defineEmits<{
  close: []
}>()

const visible = ref(false)

const close = () => {
  visible.value = false
  setTimeout(() => {
    emit('close')
  }, 200)
}

onMounted(() => {
  visible.value = true
  
  if (props.duration > 0) {
    setTimeout(() => {
      close()
    }, props.duration)
  }
})
</script>