<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Sun, Moon, Monitor, Check } from 'lucide-vue-next';

const isOpen = ref(false);
const currentTheme = ref('system'); // 'dark' | 'light' | 'system'
const dropdownRef = ref(null);

const themes = [
  { id: 'light', label: 'Terang', icon: Sun, desc: 'Mode terang klasik' },
  { id: 'dark', label: 'Gelap', icon: Moon, desc: 'Mode gelap elegan' },
  { id: 'system', label: 'Sistem', icon: Monitor, desc: 'Mengikuti tema OS' },
];

const activeIcon = computed(() => {
  if (currentTheme.value === 'light') return Sun;
  if (currentTheme.value === 'dark') return Moon;
  return Monitor;
});

const applyTheme = (theme) => {
  currentTheme.value = theme;
  if (typeof window !== 'undefined') {
    localStorage.setItem('hybrid_theme', theme);
    const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    if (isDark) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  }
  isOpen.value = false;
};

const handleMediaChange = (e) => {
  if (currentTheme.value === 'system') {
    if (e.matches) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  }
};

const handleClickOutside = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    isOpen.value = false;
  }
};

let mediaQuery = null;

onMounted(() => {
  if (typeof window !== 'undefined') {
    const saved = localStorage.getItem('hybrid_theme') || 'dark';
    applyTheme(saved);

    mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    if (mediaQuery.addEventListener) {
      mediaQuery.addEventListener('change', handleMediaChange);
    } else if (mediaQuery.addListener) {
      mediaQuery.addListener(handleMediaChange);
    }

    document.addEventListener('click', handleClickOutside);
  }
});

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    if (mediaQuery) {
      if (mediaQuery.removeEventListener) {
        mediaQuery.removeEventListener('change', handleMediaChange);
      } else if (mediaQuery.removeListener) {
        mediaQuery.removeListener(handleMediaChange);
      }
    }
    document.removeEventListener('click', handleClickOutside);
  }
});
</script>

<template>
  <div class="relative font-sans" ref="dropdownRef">
    <!-- Trigger Button -->
    <button
      type="button"
      @click="isOpen = !isOpen"
      class="p-2 rounded-xl text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-[#141923] hover:bg-slate-200 dark:hover:bg-[#1e2534] border border-slate-300 dark:border-[#232a36] transition-all cursor-pointer flex items-center justify-center group"
      :title="`Tema: ${currentTheme.charAt(0).toUpperCase() + currentTheme.slice(1)} (Klik untuk mengganti)`"
    >
      <component 
        :is="activeIcon" 
        class="w-4 h-4 transition-transform group-hover:scale-110" 
        :class="currentTheme === 'dark' ? 'text-orange-400' : (currentTheme === 'light' ? 'text-amber-500' : 'text-orange-500')"
      />
    </button>

    <!-- Theme Dropdown Modal Menu -->
    <transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 scale-95 -translate-y-1"
      enter-to-class="opacity-100 scale-100 translate-y-0"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100 scale-100 translate-y-0"
      leave-to-class="opacity-0 scale-95 -translate-y-1"
    >
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2 w-52 bg-white/95 dark:bg-[#12161f]/98 border border-slate-200 dark:border-[#232a36] rounded-2xl shadow-2xl backdrop-blur-xl p-1.5 z-50 overflow-hidden"
      >
        <div class="px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-[#1e2530] mb-1">
          Pilih Tema Tampilan
        </div>

        <div class="space-y-0.5">
          <button
            v-for="t in themes"
            :key="t.id"
            type="button"
            @click="applyTheme(t.id)"
            class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-xs font-medium transition-all cursor-pointer group"
            :class="currentTheme === t.id 
              ? 'bg-orange-50 dark:bg-orange-500/15 text-orange-600 dark:text-orange-300 font-semibold border border-orange-200 dark:border-orange-500/40' 
              : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#1a202c] hover:text-slate-900 dark:hover:text-white border border-transparent'"
          >
            <div class="flex items-center gap-2.5">
              <div 
                class="w-6 h-6 rounded-lg flex items-center justify-center transition-colors"
                :class="currentTheme === t.id 
                  ? 'bg-orange-100 dark:bg-orange-500 text-orange-600 dark:text-slate-950' 
                  : 'bg-slate-100 dark:bg-[#1a202c] text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white'"
              >
                <component :is="t.icon" class="w-3.5 h-3.5" />
              </div>

              <div class="text-left">
                <span class="block leading-tight">{{ t.label }}</span>
                <span class="block text-[9px] text-slate-400 dark:text-slate-500 leading-tight font-normal">{{ t.desc }}</span>
              </div>
            </div>

            <Check 
              v-if="currentTheme === t.id" 
              class="w-3.5 h-3.5 text-orange-600 dark:text-orange-400 flex-shrink-0" 
            />
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>
