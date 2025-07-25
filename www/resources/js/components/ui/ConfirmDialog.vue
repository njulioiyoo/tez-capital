<template>
  <Dialog :open="isOpen" @update:open="updateOpen">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <AlertTriangle class="w-5 h-5 text-amber-500" />
          {{ title }}
        </DialogTitle>
        <DialogDescription v-if="description">
          {{ description }}
        </DialogDescription>
      </DialogHeader>
      
      <DialogFooter class="flex-row justify-end gap-2 sm:gap-2">
        <Button
          variant="outline"
          @click="cancel"
          :disabled="loading"
        >
          Cancel
        </Button>
        <Button
          :variant="variant"
          @click="confirm"
          :disabled="loading"
        >
          <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
          {{ confirmText }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { AlertTriangle, Loader2 } from 'lucide-vue-next'

interface Props {
  open?: boolean
  title?: string
  description?: string
  confirmText?: string
  variant?: 'default' | 'destructive'
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  open: false,
  title: 'Confirm Action',
  confirmText: 'Confirm',
  variant: 'destructive',
  loading: false
})

const emit = defineEmits<{
  'update:open': [value: boolean]
  confirm: []
  cancel: []
}>()

const isOpen = ref(props.open)

watch(() => props.open, (newValue) => {
  isOpen.value = newValue
})

const updateOpen = (value: boolean) => {
  isOpen.value = value
  emit('update:open', value)
}

const confirm = () => {
  emit('confirm')
}

const cancel = () => {
  updateOpen(false)
  emit('cancel')
}
</script>