<script setup lang="ts">
import { cn } from '@/lib/utils'

interface Props {
  variant?: 'default' | 'secondary' | 'destructive' | 'outline'
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default'
})

const badgeClasses = {
  default: 'border-transparent bg-blue-500 text-white hover:bg-blue-600',
  secondary: 'border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200',
  destructive: 'border-transparent bg-red-500 text-white hover:bg-red-600',
  outline: 'text-foreground border-gray-200 dark:border-gray-700',
}
</script>

<template>
  <span :class="cn(
    'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
    badgeClasses[props.variant],
    props.class
  )">
    <slot />
  </span>
</template>