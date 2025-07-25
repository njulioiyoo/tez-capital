<script setup lang="ts">
import { provide, ref } from 'vue'
import { cn } from '@/lib/utils'

interface TabsProps {
  class?: string
  modelValue?: string
}

defineOptions({
  inheritAttrs: false,
})

const props = defineProps<TabsProps>()
const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const activeTab = ref(props.modelValue || '')

const setActiveTab = (value: string) => {
  activeTab.value = value
  emit('update:modelValue', value)
}

provide('tabs', {
  activeTab,
  setActiveTab
})
</script>

<template>
  <div
    v-bind="$attrs"
    :class="cn('w-full', props.class)"
  >
    <slot />
  </div>
</template>