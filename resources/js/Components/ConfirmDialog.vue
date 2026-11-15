<script setup>
import { computed } from 'vue';
import {
  DialogRoot,
  DialogPortal,
  DialogOverlay,
  DialogContent,
  DialogTitle,
  DialogDescription,
} from 'reka-ui';
import { AlertTriangle, Trash2, Info, X } from 'lucide-vue-next';
import { useConfirm } from '@/composables/useConfirm';

const { confirmState, acceptConfirm, cancelConfirm } = useConfirm();

const handleOpenChange = (open) => {
  if (!open) {
    cancelConfirm();
  }
};

const variantStyles = computed(() => {
  if (confirmState.variant === 'warning') {
    return {
      iconBg: 'bg-amber-50 dark:bg-amber-950/60 border-amber-200 dark:border-amber-800/80 text-amber-600 dark:text-amber-400',
      button: 'bg-amber-600 hover:bg-amber-700 focus-visible:ring-amber-500 text-white',
      icon: AlertTriangle,
    };
  }
  if (confirmState.variant === 'info') {
    return {
      iconBg: 'bg-[#E6F7F5] dark:bg-[#00A99D]/15 border-[#99DFD6] dark:border-[#00A99D]/40 text-[#005851] dark:text-[#4fd1c5]',
      button: 'bg-[#005851] hover:bg-[#00443f] focus-visible:ring-[#00A99D] text-white',
      icon: Info,
    };
  }
  return {
    iconBg: 'bg-rose-50 dark:bg-rose-950/60 border-rose-200 dark:border-rose-800/80 text-rose-600 dark:text-rose-400',
    button: 'bg-rose-600 hover:bg-rose-700 focus-visible:ring-rose-500 text-white',
    icon: Trash2,
  };
});
</script>

<template>
  <DialogRoot :open="confirmState.isOpen" @update:open="handleOpenChange">
    <DialogPortal>
      <DialogOverlay class="fixed inset-0 z-[90] bg-slate-900/45 backdrop-blur-xs transition-opacity" />
      <DialogContent
        class="fixed left-1/2 top-1/2 z-[91] w-[calc(100%-2rem)] max-w-md -translate-x-1/2 -translate-y-1/2 rounded-xl border border-slate-200 dark:border-slate-700/90 bg-white dark:bg-[#111820] p-5 shadow-2xl outline-none font-sans"
      >
        <div class="flex items-start gap-3.5">
          <div
            class="w-10 h-10 rounded-lg border flex items-center justify-center shrink-0"
            :class="variantStyles.iconBg"
          >
            <component :is="variantStyles.icon" class="w-5 h-5" />
          </div>

          <div class="flex-1 min-w-0">
            <DialogTitle class="text-sm font-bold text-slate-900 dark:text-slate-100 leading-snug pr-6">
              {{ confirmState.title }}
            </DialogTitle>
            <DialogDescription class="mt-1.5 text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">
              {{ confirmState.message }}
            </DialogDescription>
          </div>

          <button
            type="button"
            @click="cancelConfirm"
            class="absolute right-3.5 top-3.5 p-1 rounded-md text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
            aria-label="Tutup dialog"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="mt-5 flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <button
            type="button"
            @click="cancelConfirm"
            class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700 transition-colors cursor-pointer"
          >
            {{ confirmState.cancelText }}
          </button>
          <button
            type="button"
            @click="acceptConfirm"
            class="px-3.5 py-1.5 rounded-lg text-xs font-bold shadow-xs transition-colors focus:outline-none focus-visible:ring-2 cursor-pointer"
            :class="variantStyles.button"
          >
            {{ confirmState.confirmText }}
          </button>
        </div>
      </DialogContent>
    </DialogPortal>
  </DialogRoot>
</template>
