<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { 
  DialogRoot, 
  DialogPortal, 
  DialogOverlay, 
  DialogContent, 
  DialogTitle, 
  DialogDescription 
} from 'reka-ui';
import { 
  Search, 
  LayoutDashboard, 
  Layers, 
  BookOpen, 
  Activity, 
  History, 
  User, 
  Moon, 
  Sun, 
  LogOut, 
  CornerDownLeft, 
  Loader2,
  Sparkles,
  X,
  Ship,
  Anchor
} from 'lucide-vue-next';
import { router as inertiaRouter } from '@inertiajs/vue3';
import { useRouter } from 'vue-router';

const props = defineProps({
  mode: {
    type: String,
    default: 'inertia', // 'inertia' | 'spa'
  },
});

const isOpen = ref(false);
const searchQuery = ref('');
const activeIndex = ref(0);
const isSearching = ref(false);
const searchInputRef = ref(null);
const searchResults = ref({
  users: [],
  navigation: [],
});

let vueRouter = null;
try {
  vueRouter = useRouter();
} catch (e) {
  vueRouter = null;
}

const prefix = computed(() => {
  if (typeof window !== 'undefined' && window.location.pathname.includes('/hybridlaravel/public')) {
    return '/hybridlaravel/public';
  }
  return '';
});

const getUrl = (path) => `${prefix.value}${path}`;

// Static quick actions & navigation options
const defaultActions = [
  {
    id: 'nav-dashboard',
    title: 'Dashboard Utama',
    subtitle: 'Ringkasan operasional BKK Kelas I Bandung & 6 Wilker',
    category: 'Navigasi',
    icon: LayoutDashboard,
    action: () => navigateTo('/inertia/dashboard'),
  },
  {
    id: 'nav-aircraft',
    title: 'Data Inspeksi Pesawat',
    subtitle: 'Kelola daftar inspeksi sanitasi pesawat udara',
    category: 'Karantina Udara',
    icon: Layers,
    action: () => navigateTo('/aircraft-inspections'),
  },
  {
    id: 'nav-ship-inspections',
    title: 'Data Inspeksi Kapal (SSCC / SSCEC)',
    subtitle: 'Kelola sertifikat & verifikasi sanitasi kapal laut',
    category: 'Karantina Laut',
    icon: Anchor,
    action: () => navigateTo('/ship-inspections'),
  },
  {
    id: 'nav-ship-sanitasi',
    title: 'Form Sanitasi Kapal (SSCC / SSCEC)',
    subtitle: 'Sertifikat Pengendalian & Pembebasan Sanitasi Kapal',
    category: 'Karantina Laut',
    icon: Ship,
    action: () => window.open(getUrl('/travelconsent/public_sanitasi'), '_blank'),
  },
  {
    id: 'nav-audit',
    title: 'Audit Trail Logs',
    subtitle: 'Lihat riwayat aktivitas sistem & keamanan akun',
    category: 'Tata Kelola',
    icon: History,
    action: () => navigateTo('/audit-logs'),
  },
  {
    id: 'nav-profile',
    title: 'Profil & Keamanan Akun',
    subtitle: 'Pengaturan profil pengguna, avatar, dan kata sandi',
    category: 'Akun',
    icon: User,
    action: () => navigateTo('/profile'),
  },
];

const navigateTo = (path) => {
  isOpen.value = false;
  if (props.mode === 'spa' && (path === '/spa' || path.startsWith('/spa/'))) {
    if (vueRouter) {
      const internal = path.replace(/^\/spa/, '') || '/';
      vueRouter.push(internal);
      return;
    }
  }

  if (props.mode !== 'spa' && !path.startsWith('/spa') && !path.startsWith('/docs') && !path.startsWith('/pulse')) {
    try {
      if (inertiaRouter && typeof inertiaRouter.visit === 'function') {
        inertiaRouter.visit(getUrl(path));
        return;
      }
    } catch (e) {}
  }

  window.location.href = getUrl(path);
};

// Filtered items combining local actions and backend Scout search results
const flatItems = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();
  
  if (!query) {
    return defaultActions;
  }

  const filteredLocal = defaultActions.filter(item => 
    item.title.toLowerCase().includes(query) || 
    item.subtitle.toLowerCase().includes(query)
  );

  const scoutUsers = (searchResults.value.users || []).map(u => ({
    id: `user-${u.id}`,
    title: u.title,
    subtitle: u.subtitle,
    category: 'Pengguna (Scout)',
    icon: User,
    action: () => navigateTo(u.url || '/inertia/dashboard'),
  }));

  return [...filteredLocal, ...scoutUsers];
});

// Debounced Scout search request
let searchTimer = null;
const performScoutSearch = () => {
  const query = searchQuery.value.trim();
  if (query.length < 2) {
    searchResults.value = { users: [], navigation: [] };
    isSearching.value = false;
    return;
  }

  isSearching.value = true;
  if (searchTimer) clearTimeout(searchTimer);

  searchTimer = setTimeout(async () => {
    try {
      const res = await fetch(getUrl(`/api/v1/search?q=${encodeURIComponent(query)}`));
      if (res.ok) {
        const data = await res.json();
        searchResults.value = data.results || { users: [], navigation: [] };
      }
    } catch (e) {
      console.error('Scout search failed:', e);
    } finally {
      isSearching.value = false;
    }
  }, 200);
};

watch(searchQuery, () => {
  activeIndex.value = 0;
  performScoutSearch();
});

const onKeyDown = (e) => {
  if (!isOpen.value) return;

  if (e.key === 'ArrowDown') {
    e.preventDefault();
    activeIndex.value = (activeIndex.value + 1) % (flatItems.value.length || 1);
  } else if (e.key === 'ArrowUp') {
    e.preventDefault();
    activeIndex.value = (activeIndex.value - 1 + flatItems.value.length) % (flatItems.value.length || 1);
  } else if (e.key === 'Enter') {
    e.preventDefault();
    if (flatItems.value[activeIndex.value]) {
      flatItems.value[activeIndex.value].action();
    }
  }
};

const open = () => {
  isOpen.value = true;
  searchQuery.value = '';
  activeIndex.value = 0;
  nextTick(() => {
    searchInputRef.value?.focus();
  });
};

const close = () => {
  isOpen.value = false;
};

defineExpose({ open, close });

const handleGlobalKey = (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault();
    if (isOpen.value) {
      close();
    } else {
      open();
    }
  }
};

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.addEventListener('keydown', handleGlobalKey);
  }
});

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('keydown', handleGlobalKey);
  }
  if (searchTimer) clearTimeout(searchTimer);
});
</script>

<template>
  <DialogRoot v-model:open="isOpen">
    <DialogPortal>
      <!-- Backdrop Overlay -->
      <DialogOverlay class="fixed inset-0 z-50 bg-slate-900/25 backdrop-blur-xs transition-opacity duration-200" />
      
      <!-- Command Palette Content Modal -->
      <DialogContent 
        class="fixed left-1/2 top-24 z-50 w-full max-w-xl -translate-x-1/2 rounded-2xl border border-slate-200/90 bg-white shadow-2xl p-0 overflow-hidden ring-1 ring-slate-900/5 outline-none animate-in fade-in-0 zoom-in-95 duration-150"
        @keydown="onKeyDown"
      >
        <DialogTitle class="sr-only">Command Palette Global Search</DialogTitle>
        <DialogDescription class="sr-only">Cari menu, tindakan, dan record sistem secara instan</DialogDescription>

        <!-- Search Input Bar -->
        <div class="relative flex items-center border-b border-slate-100 px-4 py-3.5">
          <Search class="w-4 h-4 text-emerald-600 shrink-0 mr-3" />
          <input
            ref="searchInputRef"
            v-model="searchQuery"
            type="text"
            placeholder="Ketik untuk mencari (menu, aksi, wilayah kerja)..."
            class="w-full bg-transparent text-sm text-slate-900 placeholder-slate-400 focus:outline-none"
            autocomplete="off"
            spellcheck="false"
          />
          <div v-if="isSearching" class="shrink-0 mr-2">
            <Loader2 class="w-4 h-4 text-emerald-600 animate-spin" />
          </div>
          <button 
            type="button"
            @click="close"
            class="shrink-0 p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Results List -->
        <div class="max-h-80 overflow-y-auto p-2 divide-y divide-slate-100">
          <div v-if="flatItems.length === 0" class="py-12 text-center text-sm text-slate-400">
            <Sparkles class="w-8 h-8 text-slate-300 mx-auto mb-2" />
            <p>Tidak ada hasil untuk "<span class="font-semibold text-slate-700">{{ searchQuery }}</span>"</p>
          </div>

          <div v-else class="space-y-0.5">
            <button
              v-for="(item, idx) in flatItems"
              :key="item.id"
              type="button"
              @click="item.action()"
              @mouseenter="activeIndex = idx"
              class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left transition-all cursor-pointer group"
              :class="activeIndex === idx ? 'bg-slate-100/90 text-slate-900' : 'text-slate-600 hover:bg-slate-50'"
            >
              <div class="flex items-center gap-3 min-w-0">
                <div 
                  class="p-2 rounded-lg shrink-0 transition-colors"
                  :class="activeIndex === idx ? 'bg-emerald-500 text-white shadow-xs' : 'bg-slate-100 text-slate-500'"
                >
                  <component :is="item.icon" class="w-4 h-4" />
                </div>
                <div class="min-w-0">
                  <div class="text-xs font-semibold text-slate-900 truncate">{{ item.title }}</div>
                  <div class="text-[11px] text-slate-500 truncate">{{ item.subtitle }}</div>
                </div>
              </div>

              <div class="flex items-center gap-2 shrink-0 ml-3">
                <span class="text-[10px] uppercase font-mono px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-500 border border-slate-200/70">
                  {{ item.category }}
                </span>
                <CornerDownLeft v-if="activeIndex === idx" class="w-3.5 h-3.5 text-emerald-600" />
              </div>
            </button>
          </div>
        </div>

        <!-- Footer Shortcuts Guide -->
        <div class="border-t border-slate-100 bg-slate-50/80 px-4 py-2 flex items-center justify-between text-[11px] text-slate-500">
          <div class="flex items-center gap-3">
            <span class="flex items-center gap-1">
              <kbd class="px-1.5 py-0.5 rounded-md bg-white border border-slate-200 font-mono text-[10px] text-slate-600">↑↓</kbd>
              Pilih
            </span>
            <span class="flex items-center gap-1">
              <kbd class="px-1.5 py-0.5 rounded-md bg-white border border-slate-200 font-mono text-[10px] text-slate-600">↵</kbd>
              Buka
            </span>
            <span class="flex items-center gap-1">
              <kbd class="px-1.5 py-0.5 rounded-md bg-white border border-slate-200 font-mono text-[10px] text-slate-600">ESC</kbd>
              Tutup
            </span>
          </div>
          <div class="font-mono text-[10px] text-slate-500 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            BKK Kelas I Bandung Search
          </div>
        </div>
      </DialogContent>
    </DialogPortal>
  </DialogRoot>
</template>
