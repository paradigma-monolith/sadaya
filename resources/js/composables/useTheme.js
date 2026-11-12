import { ref } from 'vue';

const STORAGE_KEY = 'hybrid_theme';

const readInitialTheme = () => {
  if (typeof document !== 'undefined' && document.documentElement.classList.contains('dark')) {
    return true;
  }
  if (typeof window !== 'undefined') {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      if (saved === 'dark') return true;
      if (saved === 'light') return false;
      return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    } catch {
      return false;
    }
  }
  return false;
};

const isDark = ref(readInitialTheme());
let listenersBound = false;

const applyTheme = (dark) => {
  const next = Boolean(dark);
  isDark.value = next;

  if (typeof document !== 'undefined') {
    document.documentElement.classList.toggle('dark', next);
    document.documentElement.style.colorScheme = next ? 'dark' : 'light';
  }

  if (typeof window !== 'undefined') {
    try {
      localStorage.setItem(STORAGE_KEY, next ? 'dark' : 'light');
    } catch {
      // ignore storage write errors
    }
  }
};

export function useTheme() {
  if (typeof window !== 'undefined' && !listenersBound) {
    listenersBound = true;
    applyTheme(readInitialTheme());

    window.addEventListener('storage', (event) => {
      if (event.key === STORAGE_KEY) {
        applyTheme(event.newValue === 'dark');
      }
    });
  }

  const toggleTheme = () => {
    applyTheme(!isDark.value);
  };

  const setTheme = (mode) => {
    applyTheme(mode === 'dark');
  };

  return {
    isDark,
    toggleTheme,
    setTheme,
  };
}

