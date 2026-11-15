import { getCurrentInstance, onUnmounted } from 'vue';

/**
 * Create a debounced wrapper around a callback with automatic unmount cleanup.
 *
 * @param {Function} fn
 * @param {number} delay
 * @returns {Function & { cancel: () => void }}
 */
export function useDebouncedFn(fn, delay = 350) {
  let timer = null;

  const debounced = (...args) => {
    if (timer) clearTimeout(timer);
    timer = setTimeout(() => {
      timer = null;
      fn(...args);
    }, delay);
  };

  debounced.cancel = () => {
    if (timer) {
      clearTimeout(timer);
      timer = null;
    }
  };

  if (getCurrentInstance()) {
    onUnmounted(() => {
      debounced.cancel();
    });
  }

  return debounced;
}
