<script setup lang="ts">
import { cn } from '@/lib/utils'

interface SelectProps {
  class?: string
  modelValue?: string
  disabled?: boolean
}

defineOptions({
  inheritAttrs: false,
})

const props = defineProps<SelectProps>()
const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const handleChange = (event: Event) => {
  const target = event.target as HTMLSelectElement
  emit('update:modelValue', target.value)
}
</script>

<template>
  <select
    :value="props.modelValue"
    :class="
      cn(
        'flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:cursor-not-allowed disabled:opacity-50',
        props.class,
      )
    "
    :disabled="props.disabled"
    v-bind="$attrs"
    @change="handleChange"
  >
    <slot />
  </select>
</template>