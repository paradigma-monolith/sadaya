<script setup>
import {
  DialogRoot,
  DialogPortal,
  DialogOverlay,
  DialogContent,
  DialogTitle,
  DialogDescription,
  VisuallyHidden,
} from 'reka-ui';

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Dialog',
  },
  description: {
    type: String,
    default: '',
  },
  maxWidth: {
    type: String,
    default: 'max-w-lg',
  },
  overlayClass: {
    type: String,
    default: 'z-50 bg-slate-900/60 dark:bg-slate-950/75 backdrop-blur-xs',
  },
  contentClass: {
    type: String,
    default: 'z-50 max-h-[92vh] overflow-y-auto',
  },
  closeOnOutside: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['close', 'update:open']);

const handleOpenChange = (value) => {
  emit('update:open', value);
  if (!value) {
    emit('close');
  }
};

const handleInteractOutside = (event) => {
  if (!props.closeOnOutside) {
    event.preventDefault();
  }
};
</script>

<template>
  <DialogRoot :open="open" @update:open="handleOpenChange">
    <DialogPortal>
      <DialogOverlay
        class="fixed inset-0 transition-opacity"
        :class="overlayClass"
      />
      <DialogContent
        @interact-outside="handleInteractOutside"
        class="fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-1.5rem)] sm:w-[calc(100%-2rem)] outline-none font-sans"
        :class="[maxWidth, contentClass]"
      >
        <VisuallyHidden>
          <DialogTitle>{{ title }}</DialogTitle>
          <DialogDescription>{{ description || title }}</DialogDescription>
        </VisuallyHidden>
        <slot />
      </DialogContent>
    </DialogPortal>
  </DialogRoot>
</template>
