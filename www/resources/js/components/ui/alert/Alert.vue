<script setup lang="ts">
import { cn } from '@/lib/utils'

interface AlertProps {
  class?: string
  variant?: 'default' | 'destructive'
}

defineOptions({
  inheritAttrs: false,
})

const props = withDefaults(defineProps<AlertProps>(), {
  variant: 'default'
})
</script>

<template>
  <div
    v-bind="$attrs"
    :class="cn(
      'relative w-full rounded-lg border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground',
      props.variant === 'destructive' 
        ? 'border-red-200 text-red-800 bg-red-50 [&>svg]:text-red-600' 
        : 'bg-white text-gray-900 border-gray-200',
      props.class
    )"
    role="alert"
  >
    <slot />
  </div>
</template>