<script setup lang="ts">
import { cn } from '@/lib/utils'

export interface AlertDescriptionProps {
  class?: string
}

defineOptions({
  inheritAttrs: false,
})

const props = defineProps<AlertDescriptionProps>()
</script>

<template>
  <div
    v-bind="$attrs"
    :class="cn('text-sm [&_p]:leading-relaxed', props.class)"
  >
    <slot />
  </div>
</template>