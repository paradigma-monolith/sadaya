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
  Anchor,
  BriefcaseMedical,
  Pill,
  FileSpreadsheet,
  BarChart3,
  Globe,
  CalendarDays,
  ClipboardCheck,
  Bug,
  Database
} from 'lucide-vue-next';
import { router as inertiaRouter } from '@inertiajs/vue3';
import { useRouter } from 'vue-router';
import { useTheme } from '@/composables/useTheme';
import ClinicalSkeleton from './ClinicalSkeleton.vue';

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

const { isDark, toggleTheme } = useTheme();

// Static quick actions & navigation options
const defaultActions = computed(() => [
  {
    id: 'nav-dashboard',
    title: 'Dashboard Utama',
    subtitle: 'Ringkasan operasional BKK Kelas I Bandung & 6 Wilker',
    category: 'Navigasi',
    icon: LayoutDashboard,
    action: () => navigateTo('/dashboard'),
  },
  {
    id: 'action-toggle-theme',
    title: isDark.value ? 'Beralih ke Mode Terang (Light Mode)' : 'Beralih ke Mode Gelap (Dark Mode)',
    subtitle: 'Ubah tampilan antarmuka terang / gelap secara instan',
    category: 'Preferensi',
    icon: isDark.value ? Sun : Moon,
    action: () => {
      toggleTheme();
      isOpen.value = false;
    },
  },
  {
    id: 'nav-travel-consent',
    title: 'Travel Consent Hub (Pesawat, Kapal, Alkes & Obat P3K)',
    subtitle: 'Kelola daftar inspeksi sanitasi & sertifikat kesehatan alat angkut',
    category: 'Alat Angkut & Barang',
    icon: Layers,
    action: () => navigateTo('/travel-consent'),
  },
  {
    id: 'nav-public-home',
    title: 'Portal Publik Travel Consent',
    subtitle: 'Buka halaman portal layanan publik inspeksi sanitasi & P3K',
    category: 'Alat Angkut & Barang',
    icon: Globe,
    action: () => window.open('/travelconsent/home', '_blank'),
  },
  {
    id: 'nav-aircraft',
    title: 'Travel Consent — Data Inspeksi Pesawat',
    subtitle: 'Kelola daftar inspeksi sanitasi pesawat udara',
    category: 'Alat Angkut & Barang',
    icon: Layers,
    action: () => navigateTo('/travel-consent/aircraft'),
  },
  {
    id: 'nav-ship-surveillance',
    title: 'Pengawasan Kedatangan Kapal (TANGKAP & Karantina)',
    subtitle: 'Self-assessment karantina, skoring risiko, verifikasi sandar & estimasi PNBP',
    category: 'Alat Angkut & Barang',
    icon: Ship,
    action: () => navigateTo('/ship-surveillance'),
  },
  {
    id: 'nav-ship-inspections',
    title: 'Travel Consent — Inspeksi Sanitasi Kapal (SSCC / SSCEC)',
    subtitle: 'Kelola sertifikat & verifikasi sanitasi kapal laut',
    category: 'Alat Angkut & Barang',
    icon: Anchor,
    action: () => navigateTo('/travel-consent/ship'),
  },
  {
    id: 'nav-medical-alat',
    title: 'Travel Consent — Pemakaian Alkes P3K',
    subtitle: 'Kelola rekam alat kesehatan P3K & emergensi kloter haji',
    category: 'Alat Angkut & Barang',
    icon: BriefcaseMedical,
    action: () => navigateTo('/travel-consent/alat'),
  },
  {
    id: 'nav-medical-obat',
    title: 'Travel Consent — Pemakaian Obat P3K',
    subtitle: 'Kelola rekam obat-obatan P3K & emergensi kloter haji',
    category: 'Alat Angkut & Barang',
    icon: Pill,
    action: () => navigateTo('/travel-consent/obat'),
  },
  {
    id: 'nav-sanitasi',
    title: 'Sanitasi Lingkungan (TPP, SAB, HSBU)',
    subtitle: 'Pengawasan tempat pengolahan pangan, air bersih & gedung perkantoran',
    category: 'Kesehatan Lingkungan',
    icon: ClipboardCheck,
    action: () => navigateTo('/sanitasi-lingkungan'),
  },
  {
    id: 'nav-sivera',
    title: 'SIVERA (Surveilans Vektor & Reservoar)',
    subtitle: 'Pemantauan kepadatan nyamuk, jentik, lalat, kecoa, tikus & pinjal',
    category: 'Kesehatan Lingkungan',
    icon: Bug,
    action: () => navigateTo('/sivera'),
  },
  {
    id: 'nav-sinkarkes-dashboard',
    title: 'Dashboard & Analitik SINKARKES',
    subtitle: 'Executive summary, diagram grafis bulanan & total summary di Dashboard Utama',
    category: 'Laporan & Dokumen',
    icon: BarChart3,
    action: () => navigateTo('/dashboard#sinkarkes-section'),
  },
  {
    id: 'nav-sinkarkes',
    title: 'Form Input SINKARKES',
    subtitle: 'Laporan Karantina Kesehatan Terpadu (KSE, UKP, PRL) BKK Kelas I Bandung',
    category: 'Laporan & Dokumen',
    icon: FileSpreadsheet,
    action: () => navigateTo('/sinkarkes'),
  },
  {
    id: 'nav-sinkarkes-daily',
    title: 'Rekap Harian Kapal & Pesawat (SINKARKES)',
    subtitle: 'Input data harian kapal & pesawat yang otomatis terakumulasi ke Formulir SINKARKES',
    category: 'Laporan & Dokumen',
    icon: CalendarDays,
    action: () => navigateTo('/sinkarkes-daily'),
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
    id: 'nav-tangkap-portal',
    title: 'Portal Karantina Kapal TANGKAP (Self-Assessment MDH)',
    subtitle: 'Formulir kedatangan kapal Mandiri Terintegrasi BKK Bandung',
    category: 'Karantina Laut',
    icon: Globe,
    action: () => window.open(getUrl('/ship-surveillance/portal'), '_blank'),
  },
  {
    id: 'nav-database-import',
    title: 'Pusat Migrasi & Import Database',
    subtitle: 'Sinkronisasi dan migrasi data SQL dari sistem lama / backup',
    category: 'Tata Kelola',
    icon: Database,
    action: () => navigateTo('/database-import'),
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
]);

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
    return defaultActions.value;
  }

  const filteredLocal = defaultActions.value.filter(item => 
    item.title.toLowerCase().includes(query) || 
    item.subtitle.toLowerCase().includes(query) ||
    item.category.toLowerCase().includes(query)
  );

  const scoutUsers = (searchResults.value.users || []).map(u => ({
    id: `user-${u.id}`,
    title: u.title,
    subtitle: u.subtitle,
    category: 'Pengguna (Scout)',
    icon: User,
    action: () => navigateTo(u.url || '/dashboard'),
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
      <DialogOverlay class="fixed inset-0 z-50 bg-slate-900/40 dark:bg-slate-950/65 backdrop-blur-xs transition-opacity duration-200" />
      
      <!-- Command Palette Content Modal -->
      <DialogContent 
        class="fixed left-1/2 top-16 sm:top-20 z-50 w-[calc(100%-1.5rem)] max-w-lg -translate-x-1/2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#111820] shadow-2xl p-0 overflow-hidden outline-none animate-in fade-in-0 zoom-in-95 duration-150"
        @keydown="onKeyDown"
      >
        <DialogTitle class="sr-only">Command Palette Global Search</DialogTitle>
        <DialogDescription class="sr-only">Cari menu, tindakan, dan record sistem secara instan</DialogDescription>

        <!-- Search Input Bar -->
        <div class="relative flex items-center border-b border-slate-200/80 dark:border-slate-800 px-3.5 py-2.5 font-sans">
          <Search class="w-4 h-4 text-[#00A99D] shrink-0 mr-2.5" />
          <input
            ref="searchInputRef"
            v-model="searchQuery"
            type="text"
            placeholder="Ketik untuk mencari (menu, aksi, tema, wilayah kerja)..."
            class="w-full bg-transparent text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none"
            autocomplete="off"
            spellcheck="false"
          />
          <div v-if="isSearching" class="shrink-0 mr-2">
            <Loader2 class="w-4 h-4 text-[#00A99D] animate-spin" />
          </div>
          <button 
            type="button"
            @click="close"
            aria-label="Tutup pencarian cepat"
            title="Tutup (Esc)"
            class="shrink-0 p-1 rounded-md text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Results List -->
        <div class="max-h-72 overflow-y-auto p-1.5 divide-y divide-slate-100 dark:divide-slate-800 font-sans">
          <div v-if="isSearching && flatItems.length === 0" class="p-2">
            <ClinicalSkeleton variant="lines" :rows="3" />
          </div>

          <div v-else-if="flatItems.length === 0" class="py-10 text-center text-xs text-slate-400 dark:text-slate-500">
            <Sparkles class="w-7 h-7 text-slate-300 dark:text-slate-600 mx-auto mb-1.5" />
            <p>Tidak ada hasil untuk "<span class="font-semibold text-slate-700 dark:text-slate-200">{{ searchQuery }}</span>"</p>
          </div>

          <div v-else class="space-y-0.5">
            <button
              v-for="(item, idx) in flatItems"
              :key="item.id"
              type="button"
              @click="item.action()"
              @mouseenter="activeIndex = idx"
              class="w-full flex items-center justify-between px-2.5 py-2 rounded-lg text-left transition-colors cursor-pointer group"
              :class="activeIndex === idx ? 'bg-[#E6F7F5] dark:bg-[#00A99D]/20 text-[#005851] dark:text-[#5eead4]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#16202a]'"
            >
              <div class="flex items-center gap-2.5 min-w-0">
                <div 
                  class="p-1.5 rounded-md shrink-0 transition-colors"
                  :class="activeIndex === idx ? 'bg-[#005851] text-white' : 'bg-slate-100 dark:bg-[#16202a] text-slate-500 dark:text-slate-400'"
                >
                  <component :is="item.icon" class="w-3.5 h-3.5" />
                </div>
                <div class="min-w-0">
                  <div class="text-xs font-semibold text-slate-900 dark:text-slate-100 truncate">{{ item.title }}</div>
                  <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ item.subtitle }}</div>
                </div>
              </div>

              <div class="flex items-center gap-2 shrink-0 ml-2.5">
                <span class="hidden sm:inline-flex text-[10px] uppercase font-mono px-1.5 py-0.2 rounded bg-white dark:bg-[#16202a] text-[#005851] dark:text-[#4fd1c5] border border-[#99DFD6]/80 dark:border-[#00A99D]/40">
                  {{ item.category }}
                </span>
                <CornerDownLeft v-if="activeIndex === idx" class="w-3.5 h-3.5 text-[#00A99D]" />
              </div>
            </button>
          </div>
        </div>

        <!-- Footer Shortcuts Guide -->
        <div class="border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-[#0d141b] px-4 py-2 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 font-sans">
          <div class="flex items-center gap-3">
            <span class="flex items-center gap-1">
              <kbd class="px-1.5 py-0.5 rounded-md bg-white dark:bg-[#16202a] border border-slate-200 dark:border-slate-700 font-mono text-[10px] text-slate-600 dark:text-slate-300">↑↓</kbd>
              Pilih
            </span>
            <span class="flex items-center gap-1">
              <kbd class="px-1.5 py-0.5 rounded-md bg-white dark:bg-[#16202a] border border-slate-200 dark:border-slate-700 font-mono text-[10px] text-slate-600 dark:text-slate-300">↵</kbd>
              Buka
            </span>
            <span class="flex items-center gap-1">
              <kbd class="px-1.5 py-0.5 rounded-md bg-white dark:bg-[#16202a] border border-slate-200 dark:border-slate-700 font-mono text-[10px] text-slate-600 dark:text-slate-300">ESC</kbd>
              Tutup
            </span>
          </div>
          <div class="font-mono text-[10px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-[#00A99D]"></span>
            BKK Kelas I Bandung Search
          </div>
        </div>
      </DialogContent>
    </DialogPortal>
  </DialogRoot>
</template>
