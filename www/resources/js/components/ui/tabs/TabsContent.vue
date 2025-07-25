<script setup lang="ts">
import { inject, computed } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  class?: string
  value: string
}

defineOptions({
  inheritAttrs: false,
})

const props = defineProps<Props>()

const tabs = inject('tabs') as any
const isActive = computed(() => tabs.activeTab.value === props.value)
</script>

<template>
  <div
    v-if="isActive"
    v-bind="$attrs"
    :class="
      cn(
        'mt-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2',
        props.class,
      )
    "
  >
    <slot />
  </div>
</template>