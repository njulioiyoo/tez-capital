<script setup lang="ts">
import { cn } from '@/lib/utils'

interface Props {
  class?: string
}

defineOptions({
  inheritAttrs: false,
})

const props = defineProps<Props>()
</script>

<template>
  <div
    v-bind="$attrs"
    :class="
      cn(
        'inline-flex h-10 items-center justify-center rounded-md bg-gray-100 p-1 text-gray-600',
        props.class,
      )
    "
  >
    <slot />
  </div>
</template>