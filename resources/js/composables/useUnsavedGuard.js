import { onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useConfirm } from './useConfirm';

/**
 * Guard against losing unsaved changes during browser close/reload or Inertia GET navigation.
 *
 * @param {import('vue').Ref<boolean> | (() => boolean)} dirtySource
 * @param {{ title?: string, message?: string, confirmText?: string, cancelText?: string }} options
 */
export function useUnsavedGuard(dirtySource, options = {}) {
  const { confirmDialog } = useConfirm();
  let removeBeforeListener = null;
  let bypassNextVisit = false;
  let isConfirmOpen = false;

  const isDirty = () => {
    if (typeof dirtySource === 'function') {
      return Boolean(dirtySource());
    }
    return Boolean(dirtySource?.value);
  };

  const title = options.title || 'Perubahan Belum Disimpan';
  const message =
    options.message ||
    'Anda memiliki perubahan data yang belum disimpan. Jika meninggalkan halaman ini sekarang, perubahan tersebut akan hilang.';

  const handleBeforeUnload = (event) => {
    if (!isDirty() || bypassNextVisit) return;
    event.preventDefault();
    event.returnValue = '';
  };

  const replayVisit = (visit) => {
    bypassNextVisit = true;
    router.visit(visit.url, {
      method: visit.method,
      data: visit.data,
      replace: visit.replace,
      preserveScroll: visit.preserveScroll,
      preserveState: visit.preserveState,
      only: visit.only,
      except: visit.except,
      headers: visit.headers,
      onFinish: () => {
        bypassNextVisit = false;
      },
    });
  };

  onMounted(() => {
    window.addEventListener('beforeunload', handleBeforeUnload);

    removeBeforeListener = router.on('before', (event) => {
      const visit = event?.detail?.visit;
      if (!visit || bypassNextVisit || !isDirty()) return;

      // Never block form submissions (POST/PUT/PATCH/DELETE) or background prefetches.
      if (visit.prefetch || (visit.method && visit.method.toLowerCase() !== 'get')) {
        return;
      }

      event.preventDefault();

      if (isConfirmOpen) return false;
      isConfirmOpen = true;

      confirmDialog({
        title,
        message,
        confirmText: options.confirmText || 'Tinggalkan Halaman',
        cancelText: options.cancelText || 'Tetap di Sini',
        variant: 'warning',
      })
        .then((confirmed) => {
          if (confirmed) {
            replayVisit(visit);
          }
        })
        .finally(() => {
          isConfirmOpen = false;
        });

      return false;
    });
  });

  onUnmounted(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload);
    if (typeof removeBeforeListener === 'function') {
      removeBeforeListener();
    }
  });

  return {
    allowNextNavigation: () => {
      bypassNextVisit = true;
    },
  };
}
