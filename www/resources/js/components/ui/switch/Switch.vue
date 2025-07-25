<script setup lang="ts">
import { cn } from '@/lib/utils'

interface SwitchProps {
  class?: string
  checked?: boolean
  disabled?: boolean
}

defineOptions({
  inheritAttrs: false,
})

const props = defineProps<SwitchProps>()
const emit = defineEmits<{
  'update:checked': [value: boolean]
}>()

const toggle = () => {
  if (!props.disabled) {
    emit('update:checked', !props.checked)
  }
}
</script>

<template>
  <button
    v-bind="$attrs"
    :class="
      cn(
        'peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
        props.checked ? 'bg-blue-600' : 'bg-gray-200',
        props.class,
      )
    "
    :disabled="props.disabled"
    @click="toggle"
  >
    <div
      :class="cn(
        'pointer-events-none block h-5 w-5 rounded-full bg-white shadow-lg ring-0 transition-transform',
        props.checked ? 'translate-x-5' : 'translate-x-0'
      )"
    />
  </button>
</template>