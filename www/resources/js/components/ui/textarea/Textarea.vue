<script setup lang="ts">
import { cn } from '@/lib/utils'

export interface TextareaProps {
  class?: string
  placeholder?: string
  rows?: number
  disabled?: boolean
  modelValue?: string
}

defineOptions({
  inheritAttrs: false,
})

const props = defineProps<TextareaProps>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const handleInput = (event: Event) => {
  const target = event.target as HTMLTextAreaElement
  emit('update:modelValue', target.value)
}
</script>

<template>
  <textarea
    :value="props.modelValue"
    :class="
      cn(
        'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
        props.class,
      )
    "
    :placeholder="props.placeholder"
    :rows="props.rows"
    :disabled="props.disabled"
    v-bind="$attrs"
    @input="handleInput"
  />
</template>