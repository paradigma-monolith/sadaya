const DEFAULT_MAX_AGE_MS = 1000 * 60 * 60 * 24;

/**
 * Resolve default browser localStorage safely outside SSR/Node contexts.
 */
function getDefaultStorage() {
  if (typeof window === 'undefined') return null;
  try {
    return window.localStorage;
  } catch {
    return null;
  }
}

/**
 * Persist and restore local form drafts with TTL expiration and malformed JSON protection.
 *
 * @param {string} storageKey
 * @param {{ storage?: Pick<Storage, 'getItem' | 'setItem' | 'removeItem'> | null, maxAgeMs?: number }} options
 */
export function useFormDraft(storageKey, options = {}) {
  const storage = options.storage !== undefined ? options.storage : getDefaultStorage();
  const maxAgeMs = typeof options.maxAgeMs === 'number' ? options.maxAgeMs : DEFAULT_MAX_AGE_MS;

  const save = (data) => {
    if (!storage || !storageKey) return false;
    try {
      storage.setItem(
        storageKey,
        JSON.stringify({
          updatedAt: Date.now(),
          data,
        })
      );
      return true;
    } catch {
      return false;
    }
  };

  const clear = () => {
    if (!storage || !storageKey) return;
    try {
      storage.removeItem(storageKey);
    } catch {
      // Ignore storage access restrictions
    }
  };

  const restore = () => {
    if (!storage || !storageKey) return null;
    try {
      const raw = storage.getItem(storageKey);
      if (!raw) return null;
      const parsed = JSON.parse(raw);
      if (!parsed || typeof parsed !== 'object' || !('data' in parsed)) {
        clear();
        return null;
      }
      if (maxAgeMs > 0 && typeof parsed.updatedAt === 'number' && Date.now() - parsed.updatedAt > maxAgeMs) {
        clear();
        return null;
      }
      return parsed.data;
    } catch {
      clear();
      return null;
    }
  };

  const hasDraft = () => restore() !== null;

  return {
    save,
    restore,
    clear,
    hasDraft,
  };
}
