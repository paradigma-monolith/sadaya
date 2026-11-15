import { reactive } from 'vue';

const confirmState = reactive({
  isOpen: false,
  title: 'Konfirmasi Tindakan',
  message: 'Apakah Anda yakin ingin melanjutkan?',
  confirmText: 'Ya, Lanjutkan',
  cancelText: 'Batal',
  variant: 'danger', // 'danger' | 'warning' | 'info'
  resolveFn: null,
});

const settle = (value) => {
  const resolver = confirmState.resolveFn;
  confirmState.resolveFn = null;
  confirmState.isOpen = false;
  if (typeof resolver === 'function') {
    resolver(value);
  }
};

export function useConfirm() {
  const confirmDialog = (options) => {
    if (typeof confirmState.resolveFn === 'function') {
      confirmState.resolveFn(false);
    }

    const opts = typeof options === 'string' ? { message: options } : (options || {});

    confirmState.title = opts.title || 'Konfirmasi Tindakan';
    confirmState.message = opts.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
    confirmState.confirmText = opts.confirmText || (opts.variant === 'info' ? 'Lanjutkan' : 'Ya, Lanjutkan');
    confirmState.cancelText = opts.cancelText || 'Batal';
    confirmState.variant = opts.variant || 'danger';
    confirmState.isOpen = true;

    return new Promise((resolve) => {
      confirmState.resolveFn = resolve;
    });
  };

  return {
    confirmState,
    confirmDialog,
    acceptConfirm: () => settle(true),
    cancelConfirm: () => settle(false),
  };
}
