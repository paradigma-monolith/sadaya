<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { usePage, router as inertiaRouter } from '@inertiajs/vue3';
import { 
  LogOut, 
  User as UserIcon, 
  Bell, 
  CheckCheck, 
  Radio,
  Menu,
  ChevronDown,
  X,
  Search,
  Sun,
  Moon
} from 'lucide-vue-next';
import AdminSidebar from './AdminSidebar.vue';
import FloatingChat from './FloatingChat.vue';
import CommandPalette from './CommandPalette.vue';
import ConfirmDialog from './ConfirmDialog.vue';
import ClinicalSkeleton from './ClinicalSkeleton.vue';
import { Toaster } from 'vue-sonner';
import { useSidebar } from '@/composables/useSidebar';
import { useTheme } from '@/composables/useTheme';

const props = defineProps({
  mode: {
    type: String,
    default: 'inertia',
  },
  currentRoute: {
    type: String,
    default: '',
  },
});

const prefix = computed(() => {
  if (typeof window !== 'undefined' && window.location.pathname.includes('/hybridlaravel/public')) {
    return '/hybridlaravel/public';
  }
  return '';
});

const getUrl = (path) => `${prefix.value}${path}`;

// Compute active navigation item with automatic URL pathname fallback
const activeNav = computed(() => {
  if (props.currentRoute) return props.currentRoute;
  if (typeof window === 'undefined') return '';
  const p = window.location.pathname;
  if (p.endsWith('/spa') || p.includes('/spa/')) return 'spa';
  if (p.includes('/travel-consent')) return 'travel-consent';
  if (p.includes('/aircraft-inspections/dashboard')) return 'aircraft-dashboard';
  if (p.includes('/aircraft-inspections')) return 'aircraft-index';
  if (p.includes('/ship-inspections')) return 'ship-inspections';
  if (p.includes('/flight-medical-inspections/alat')) return 'flight-medical-alat';
  if (p.includes('/flight-medical-inspections/obat')) return 'flight-medical-obat';
  if (p.includes('/sinkarkes-daily')) return 'sinkarkes-daily';
  if (p.includes('/sinkarkes/dashboard')) return 'sinkarkes-dashboard';
  if (p.includes('/sinkarkes')) return 'sinkarkes';
  if (p.includes('/sanitasi-lingkungan/tpp') || p.includes('/prl/tpp')) return 'sanitasi-tpp';
  if (p.includes('/sanitasi-lingkungan/sab') || p.includes('/prl/sab')) return 'sanitasi-sab';
  if (p.includes('/sanitasi-lingkungan/hsbu') || p.includes('/prl/hsbu')) return 'sanitasi-hsbu';
  if (p.includes('/sanitasi-lingkungan') || p.includes('/prl')) return 'sanitasi';
  if (p.includes('/sivera') || p.includes('/prl/sivera')) return 'sivera';
  if (p.includes('/audit-logs')) return 'audit';
  if (p.includes('/profile')) return 'profile';
  if (p.includes('/ship-surveillance')) return 'ship-surveillance';
  if (p.includes('/database-import')) return 'database-import';
  if (p.includes('/inertia/dashboard') || p === '/' || p.endsWith('/public') || p.endsWith('/public/')) return 'dashboard';
  return '';
});

const breadcrumbLabel = computed(() => {
  const labels = {
    dashboard: 'Command Center',
    home: 'Command Center',
    audit: 'Audit Trail Sistem',
    profile: 'Profil Akun',
    'sinkarkes-daily': 'Input Harian SINKARKES',
    'sinkarkes-dashboard': 'Analitik SINKARKES',
    sinkarkes: 'Formulir Laporan SINKARKES',
    'travel-consent': 'Travel Consent Hub',
    'ship-surveillance': 'TANGKAP Surveillance',
    'aircraft-dashboard': 'Dashboard Inspeksi Pesawat',
    'aircraft-index': 'Data Inspeksi Pesawat',
    'ship-inspections': 'Inspeksi Sanitasi Kapal',
    'flight-medical-alat': 'Pemakaian Alkes P3K',
    'flight-medical-obat': 'Pemakaian Obat P3K',
    'sanitasi-tpp': 'Sanitasi TPP',
    'sanitasi-sab': 'Sanitasi SAB',
    'sanitasi-hsbu': 'Sanitasi HSBU',
    sanitasi: 'Sanitasi Lingkungan',
    sivera: 'SIVERA',
    'database-import': 'Import Database',
  };
  return labels[activeNav.value] || 'Portal Karantina Kesehatan';
});

// Dropdown Navigation States & Element References
const showNotifications = ref(false);
const showUserMenu = ref(false);

const notifDropdownRef = ref(null);
const userDropdownRef = ref(null);

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value;
  if (showNotifications.value) {
    showUserMenu.value = false;
    fetchNotifications();
  }
};

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value;
  if (showUserMenu.value) {
    showNotifications.value = false;
  }
};

const closeAllDropdowns = () => {
  showNotifications.value = false;
  showUserMenu.value = false;
};

const handleClickOutside = (e) => {
  if (notifDropdownRef.value && !notifDropdownRef.value.contains(e.target)) {
    showNotifications.value = false;
  }
  if (userDropdownRef.value && !userDropdownRef.value.contains(e.target)) {
    showUserMenu.value = false;
  }
};

// Seamless client-side navigation
const handleNav = (targetPath, e) => {
  closeAllDropdowns();

  if (!targetPath.startsWith('/docs') && !targetPath.startsWith('/pulse')) {
    try {
      if (inertiaRouter && typeof inertiaRouter.visit === 'function') {
        if (e) e.preventDefault();
        inertiaRouter.visit(getUrl(targetPath));
        return;
      }
    } catch (err) {}
  }
};

// Safely access current authenticated user
const currentUser = computed(() => {
  try {
    const page = usePage();
    return page?.props?.auth?.user || null;
  } catch (e) {
    return (typeof window !== 'undefined' && window.authUser) ? window.authUser : null;
  }
});

// Synchronized persistent sidebar & theme state via composables
const { isOpen, isCollapsed, toggleCollapse, toggleOpen } = useSidebar();
const { isDark, toggleTheme } = useTheme();

const handleSidebarToggle = () => {
  if (typeof window !== 'undefined' && window.innerWidth >= 1024) {
    toggleCollapse();
  } else {
    toggleOpen();
  }
};

// Global Command Palette State
const commandPaletteRef = ref(null);
const openCommandPalette = () => {
  commandPaletteRef.value?.open();
};

// Real-Time Notifications State
const unreadCount = ref(0);
const notifications = ref([]);
const isLoadingNotifications = ref(false);

const fetchNotifications = async () => {
  if (!currentUser.value) return;
  if (notifications.value.length === 0) {
    isLoadingNotifications.value = true;
  }
  try {
    const res = await fetch(getUrl('/notifications'));
    if (res.ok) {
      const data = await res.json();
      unreadCount.value = data.unread_count || 0;
      notifications.value = data.notifications || [];
    }
  } catch (e) {
    // silent fallback
  } finally {
    isLoadingNotifications.value = false;
  }
};

const markAllRead = async () => {
  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    await fetch(getUrl('/notifications/read-all'), {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json',
      },
    });
    unreadCount.value = 0;
    notifications.value.forEach(n => n.read_at = new Date().toISOString());
  } catch (e) {}
};

const markSingleRead = async (notification) => {
  if (!notification.read_at) {
    try {
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      await fetch(getUrl(`/notifications/${notification.id}/read`), {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': token,
          'Accept': 'application/json',
        },
      });
      notification.read_at = new Date().toISOString();
      if (unreadCount.value > 0) unreadCount.value--;
    } catch (e) {}
  }
  if (notification.data?.action_url) {
    showNotifications.value = false;
    handleNav(notification.data.action_url);
  }
};

// Real-Time Incoming Toast State & Web Audio Chime
const liveToast = ref(null);
let toastTimeout = null;

const playNotificationChime = () => {
  try {
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return;
    const ctx = new AudioCtx();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.type = 'sine';
    osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
    osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15); // A5
    gain.gain.setValueAtTime(0.08, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
    osc.start(ctx.currentTime);
    osc.stop(ctx.currentTime + 0.35);
  } catch (e) {}
};

const showLiveToast = (notification) => {
  liveToast.value = {
    id: notification.id,
    title: notification.title || 'Notifikasi Reverb Baru',
    message: notification.message || '',
    action_url: notification.action_url || null,
  };
  if (toastTimeout) clearTimeout(toastTimeout);
  toastTimeout = setTimeout(() => {
    liveToast.value = null;
  }, 5000);
};

let isNavbarMounted = true;

onMounted(async () => {
  fetchNotifications();

  // Lazy-load Echo & listen for real-time Reverb WebSocket notifications
  if (typeof window !== 'undefined' && currentUser.value) {
    let echoInstance = window.Echo || null;
    if (!echoInstance) {
      try {
        const { getEcho } = await import('../echo.js');
        if (isNavbarMounted) {
          echoInstance = getEcho();
        }
      } catch (e) {}
    }

    if (echoInstance && isNavbarMounted && currentUser.value) {
      echoInstance.private(`App.Models.User.${currentUser.value.id}`)
        .notification((notification) => {
          unreadCount.value++;
          notifications.value.unshift({
            id: notification.id,
            data: {
              title: notification.title,
              message: notification.message,
              type: notification.type || 'info',
              action_url: notification.action_url || null,
              time: 'baru saja',
            },
            read_at: null,
            created_at: new Date().toISOString(),
          });
          showLiveToast(notification);
          playNotificationChime();
        });
    }
  }

  // Click outside listener to dismiss dropdowns
  if (typeof document !== 'undefined') {
    document.addEventListener('click', handleClickOutside);
  }

  // Toggle sidebar via keyboard shortcut (Ctrl+B / Cmd+B) and close dropdowns (Escape)
  if (typeof window !== 'undefined') {
    window.addEventListener('keydown', handleGlobalKeydown);
  }
});

const handleGlobalKeydown = (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
    e.preventDefault();
    handleSidebarToggle();
  }
  if (e.key === 'Escape') {
    closeAllDropdowns();
  }
};

onUnmounted(() => {
  isNavbarMounted = false;
  if (typeof document !== 'undefined') {
    document.removeEventListener('click', handleClickOutside);
  }
  if (typeof window !== 'undefined') {
    window.removeEventListener('keydown', handleGlobalKeydown);
  }
  if (toastTimeout) {
    clearTimeout(toastTimeout);
  }
});

const handleLogout = () => {
  window.location.href = getUrl('/logout');
};
</script>

<template>
  <div class="min-h-screen bg-[#f4f7f9] dark:bg-[#0b1116] text-slate-900 dark:text-slate-100 selection:bg-[#00A99D] selection:text-white flex flex-col transition-colors duration-200">
    <!-- Keyboard Skip Link -->
    <a
      href="#sadaya-main-content"
      class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[120] focus:px-3 focus:py-2 focus:rounded-lg focus:bg-[#005851] focus:text-white focus:text-xs focus:font-semibold focus:shadow-lg"
    >
      Lewati ke konten utama
    </a>

    <!-- Enterprise Persistent Sidebar Navigation -->
    <AdminSidebar 
      :mode="mode" 
      :currentRoute="currentRoute" 
    />

    <!-- Main Layout Wrapper (Auto-offset for persistent desktop sidebar) -->
    <div 
      class="flex-1 flex flex-col transition-all duration-200 w-full print:pl-0"
      :class="isCollapsed ? 'lg:pl-[60px]' : 'lg:pl-64'"
    >
      <!-- Top Navbar Header (Enterprise Clinical Compact) -->
      <header class="sticky top-0 z-30 w-full bg-white/95 dark:bg-[#111820]/95 backdrop-blur-md border-b border-slate-200/90 dark:border-slate-800 transition-colors print:hidden">
        <!-- User Impersonation Alert Banner -->
        <div v-if="currentUser?.is_impersonating" class="bg-amber-500 text-white font-medium text-xs px-4 py-1 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span>⚠️ Anda sedang login sebagai <strong>{{ currentUser.name }}</strong> (Mode Impersonasi)</span>
          </div>
          <a :href="getUrl('/impersonate/leave')" class="px-2 py-0.5 rounded bg-slate-900 text-white text-[11px] font-semibold hover:bg-slate-800 transition-colors">
            Kembali ke Super Admin ✕
          </a>
        </div>

        <div class="w-full px-3 sm:px-5 lg:px-6">
          <div class="flex items-center justify-between h-12">
            <!-- Left: Single Clean Sidebar Toggle & Context Breadcrumb -->
            <div class="flex items-center gap-2.5 shrink-0 min-w-0">
              <button
                type="button"
                @click="handleSidebarToggle"
                class="p-1.5 rounded-md text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-50 dark:bg-[#16202a] hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700 transition-colors cursor-pointer flex items-center justify-center shrink-0"
                :title="isCollapsed ? 'Perluas Sidebar (Ctrl+B)' : 'Ciutkan Sidebar (Ctrl+B)'"
                :aria-label="isCollapsed ? 'Perluas Sidebar (Ctrl+B)' : 'Ciutkan Sidebar (Ctrl+B)'"
              >
                <Menu class="w-4 h-4" />
              </button>

              <!-- Mobile Brand (Hidden on lg+ because Sidebar already displays BKK Kelas I Bandung) -->
              <a :href="getUrl('/')" @click="handleNav('/', $event)" class="flex lg:hidden items-center gap-2 group cursor-pointer shrink-0">
                <img 
                  :src="getUrl('/images/LOGO KARANTINA.png')" 
                  alt="Logo Karantina Kesehatan" 
                  class="h-6 w-auto max-w-[28px] object-contain shrink-0"
                  onerror="this.onerror=null;this.src='/storage/images/LOGO KARANTINA.png';"
                />
                <span class="text-[13px] font-bold text-slate-900 dark:text-slate-100 tracking-tight truncate">
                  BKK Kelas I Bandung
                </span>
              </a>

              <!-- Desktop Clean Dynamic Breadcrumb -->
              <div class="hidden lg:flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <span class="font-bold text-[#005851] dark:text-[#4fd1c5] tracking-tight">SADAYA</span>
                <span class="text-slate-300 dark:text-slate-700">/</span>
                <span class="font-medium text-slate-700 dark:text-slate-200">{{ breadcrumbLabel }}</span>
                <span class="text-slate-300 dark:text-slate-700">·</span>
                <span class="text-[11px] text-slate-400 dark:text-slate-500">Wilayah Kerja Jawa Barat</span>
              </div>
            </div>

            <!-- Right Side: Search, Dark Mode Toggle, Notifications & User Avatar -->
            <div class="flex items-center gap-1.5 shrink-0">

              <!-- Command Palette Trigger Button (Ctrl + K) -->
              <button
                type="button"
                @click="openCommandPalette"
                class="flex items-center gap-2 px-2 py-1 rounded-md bg-slate-50 dark:bg-[#16202a] hover:bg-[#E6F7F5]/70 dark:hover:bg-[#00A99D]/15 hover:text-[#005851] dark:hover:text-[#4fd1c5] text-slate-500 dark:text-slate-400 text-xs border border-slate-200/90 dark:border-slate-700 transition-colors cursor-pointer"
                title="Buka Pencarian Cepat (Ctrl+K)"
              >
                <Search class="w-3.5 h-3.5 text-slate-400" />
                <span class="hidden md:inline text-[11px] font-medium">Cari modul...</span>
                <kbd class="hidden md:inline-flex items-center px-1 py-0.2 text-[10px] font-mono bg-white dark:bg-[#111820] border border-slate-200 dark:border-slate-700 rounded text-slate-400 dark:text-slate-500">⌘K</kbd>
              </button>

              <!-- Dark / Light Mode Toggle -->
              <button
                type="button"
                @click="toggleTheme"
                class="p-1.5 rounded-md border transition-colors cursor-pointer flex items-center justify-center shrink-0"
                :class="isDark
                  ? 'bg-[#16202a] border-slate-700 text-amber-300 hover:bg-slate-800 hover:text-amber-200'
                  : 'bg-white border-slate-200/90 text-slate-500 hover:bg-slate-50 hover:text-[#005851]'"
                :title="isDark ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'"
                :aria-label="isDark ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'"
              >
                <Sun v-if="isDark" class="w-3.5 h-3.5" />
                <Moon v-else class="w-3.5 h-3.5" />
              </button>

              <!-- Notification Bell Dropdown -->
              <div class="relative" ref="notifDropdownRef">
                <button
                  type="button"
                  @click="toggleNotifications"
                  class="relative p-1.5 rounded-md text-slate-500 dark:text-slate-400 hover:text-[#005851] dark:hover:text-[#4fd1c5] bg-white dark:bg-[#16202a] hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200/90 dark:border-slate-700 transition-colors cursor-pointer"
                  title="Notifikasi Real-Time"
                  aria-label="Buka Notifikasi Real-Time"
                >
                  <Bell class="w-3.5 h-3.5" />
                  <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-[#00A99D] text-white rounded-full text-[9px] font-bold flex items-center justify-center ring-1 ring-white dark:ring-[#111820] tabular-nums">
                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                  </span>
                </button>

                <div 
                  v-if="showNotifications" 
                  @click="showNotifications = false" 
                  class="fixed inset-0 bg-slate-900/20 backdrop-blur-2xs z-40 sm:hidden"
                ></div>

                <Transition
                  enter-active-class="transition duration-200 ease-out"
                  enter-from-class="transform opacity-0 scale-95 -translate-y-2"
                  enter-to-class="transform opacity-100 scale-100 translate-y-0"
                  leave-active-class="transition duration-150 ease-in"
                  leave-from-class="transform opacity-100 scale-100 translate-y-0"
                  leave-to-class="transform opacity-0 scale-95 -translate-y-2"
                >
                  <div 
                    v-if="showNotifications" 
                    class="fixed inset-x-3 top-16 mt-2 max-w-sm mx-auto sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-2 sm:w-84 sm:max-w-md sm:mx-0 max-h-[min(520px,calc(100vh-5.5rem))] flex flex-col bg-white dark:bg-[#111820] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl p-3.5 z-50 text-slate-800 dark:text-slate-100"
                  >
                    <div class="flex items-center justify-between pb-2.5 mb-2.5 border-b border-slate-100 dark:border-slate-800 text-xs shrink-0">
                      <div class="flex items-center gap-2">
                        <span class="font-bold text-[#005851] dark:text-[#4fd1c5] flex items-center gap-1.5">
                          <Radio class="w-3.5 h-3.5 text-[#00A99D]" />
                          <span>Notifikasi Sistem</span>
                        </span>
                        <span v-if="unreadCount > 0" class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#E6F7F5] dark:bg-[#00A99D]/20 text-[#005851] dark:text-[#4fd1c5] border border-[#99DFD6] dark:border-[#00A99D]/40">
                          {{ unreadCount }} Baru
                        </span>
                      </div>

                      <div class="flex items-center gap-1.5">
                        <button
                          type="button"
                          @click="markAllRead"
                          class="text-[11px] text-slate-500 dark:text-slate-400 hover:text-[#00A99D] flex items-center gap-1 cursor-pointer py-1 px-1.5 rounded-lg hover:bg-[#E6F7F5] dark:hover:bg-[#00A99D]/15 transition-colors"
                        >
                          <CheckCheck class="w-3.5 h-3.5 text-[#00A99D]" />
                          <span class="hidden min-[380px]:inline">Tandai Dibaca</span>
                        </button>
                        <button
                          type="button"
                          @click="showNotifications = false"
                          aria-label="Tutup panel notifikasi"
                          title="Tutup panel notifikasi"
                          class="sm:hidden p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                        >
                          <X class="w-4 h-4" />
                        </button>
                      </div>
                    </div>

                    <div class="flex-1 min-h-0 overflow-y-auto space-y-2 overscroll-contain pr-1">
                      <ClinicalSkeleton v-if="isLoadingNotifications && notifications.length === 0" variant="lines" :rows="3" />

                      <template v-else>
                        <div 
                          v-for="n in notifications" 
                          :key="n.id" 
                          @click="markSingleRead(n)"
                          class="p-2.5 rounded-xl text-xs transition-colors cursor-pointer hover:border-[#99DFD6] dark:hover:border-[#00A99D]/50 border" 
                          :class="n.read_at ? 'bg-slate-50 dark:bg-[#16202a] border-slate-100 dark:border-slate-800 text-slate-500 dark:text-slate-400' : 'bg-[#E6F7F5]/50 dark:bg-[#00A99D]/10 border-[#99DFD6] dark:border-[#00A99D]/40 text-slate-800 dark:text-slate-100'"
                        >
                          <div class="flex items-start justify-between gap-1.5">
                            <div class="font-semibold text-[#005851] dark:text-[#4fd1c5] text-xs leading-tight">
                              {{ n.data?.title || 'Pemberitahuan Sistem' }}
                            </div>
                            <span v-if="!n.read_at" class="w-1.5 h-1.5 rounded-full bg-[#00A99D] shrink-0 mt-1"></span>
                          </div>
                          <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                            {{ n.data?.message }}
                          </div>
                          <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5 font-mono">
                            {{ n.data?.time || 'baru saja' }}
                          </div>
                        </div>

                        <div v-if="notifications.length === 0" class="py-8 text-center text-xs text-slate-400 dark:text-slate-500">
                          <Radio class="w-6 h-6 text-slate-300 dark:text-slate-600 mx-auto mb-2" />
                          <p>Belum ada notifikasi baru.</p>
                        </div>
                      </template>
                    </div>
                  </div>
                </Transition>
              </div>

              <!-- User Account Dropdown Menu (Accessible on Mobile & Desktop) -->
              <div class="relative shrink-0" ref="userDropdownRef">
                <button
                  type="button"
                  @click="toggleUserMenu"
                  title="Menu Akun Pengguna"
                  class="flex items-center gap-1.5 p-1 sm:pr-2.5 rounded-full bg-white dark:bg-[#16202a] hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition-all group cursor-pointer max-w-[190px]"
                >
                  <img
                    :src="currentUser?.avatar_url || 'https://ui-avatars.com/api/?name=Admin&background=0f172a&color=fff'"
                    :alt="currentUser?.name || 'User'"
                    class="w-7 h-7 rounded-full object-cover border border-slate-200 dark:border-slate-700 shrink-0"
                  />
                  <div class="hidden md:block text-left min-w-0 flex-1">
                    <span class="block font-semibold text-slate-800 dark:text-slate-100 group-hover:text-slate-950 dark:group-hover:text-white truncate text-xs leading-tight">
                      {{ currentUser?.name }}
                    </span>
                  </div>
                  <ChevronDown
                    class="w-3.5 h-3.5 text-slate-400 hidden sm:block transition-transform duration-150"
                    :class="showUserMenu ? 'rotate-180 text-[#00A99D]' : ''"
                  />
                </button>

                <Transition
                  enter-active-class="transition duration-150 ease-out"
                  enter-from-class="transform opacity-0 scale-95 -translate-y-1"
                  enter-to-class="transform opacity-100 scale-100 translate-y-0"
                  leave-active-class="transition duration-100 ease-in"
                  leave-from-class="transform opacity-100 scale-100 translate-y-0"
                  leave-to-class="transform opacity-0 scale-95 -translate-y-1"
                >
                  <div
                    v-if="showUserMenu"
                    class="absolute right-0 top-full mt-2 w-56 bg-white dark:bg-[#111820] border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-1.5 z-50 text-xs"
                  >
                    <div class="px-3.5 py-2 border-b border-slate-100 dark:border-slate-800">
                      <p class="font-bold text-slate-900 dark:text-slate-100 truncate">
                        {{ currentUser?.name || 'Administrator' }}
                      </p>
                      <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                        {{ currentUser?.email || currentUser?.roles?.[0] || 'BKK Kelas I Bandung' }}
                      </p>
                    </div>

                    <div class="p-1 space-y-0.5">
                      <a
                        :href="getUrl('/profile')"
                        @click="handleNav('/profile', $event)"
                        class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium transition-colors cursor-pointer"
                      >
                        <UserIcon class="w-3.5 h-3.5 text-[#00A99D]" />
                        <span>Profil &amp; Keamanan</span>
                      </a>

                      <button
                        type="button"
                        @click="toggleTheme(); showUserMenu = false"
                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium transition-colors cursor-pointer"
                      >
                        <span class="flex items-center gap-2">
                          <Sun v-if="isDark" class="w-3.5 h-3.5 text-amber-400" />
                          <Moon v-else class="w-3.5 h-3.5 text-slate-500" />
                          <span>{{ isDark ? 'Mode Terang' : 'Mode Gelap' }}</span>
                        </span>
                        <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                          {{ isDark ? 'DARK' : 'LIGHT' }}
                        </span>
                      </button>
                    </div>

                    <div class="border-t border-slate-100 dark:border-slate-800 p-1 mt-0.5">
                      <button
                        type="button"
                        @click="handleLogout"
                        class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 font-semibold transition-colors cursor-pointer"
                      >
                        <LogOut class="w-3.5 h-3.5" />
                        <span>Keluar dari Sesi</span>
                      </button>
                    </div>
                  </div>
                </Transition>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Real-Time Reverb Live Toast -->
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform -translate-y-4 opacity-0 scale-95"
        enter-to-class="transform translate-y-0 opacity-100 scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="transform translate-y-0 opacity-100 scale-100"
        leave-to-class="transform -translate-y-4 opacity-0 scale-95"
      >
        <div 
          v-if="liveToast"
          role="status"
          aria-live="polite"
          class="fixed top-16 inset-x-3 sm:inset-x-auto sm:right-6 sm:w-96 z-50 bg-white dark:bg-[#111820] text-slate-900 dark:text-slate-100 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-3 shadow-xl flex items-start gap-3 print:hidden"
        >
          <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
            <Radio class="w-4 h-4" />
          </div>
          <button
            type="button"
            class="flex-1 min-w-0 text-left cursor-pointer"
            @click="showNotifications = true; liveToast = null;"
          >
            <div class="flex items-center justify-between gap-1">
              <span class="text-xs font-bold text-slate-900 dark:text-slate-100 leading-tight truncate">
                {{ liveToast.title }}
              </span>
              <span class="text-[10px] text-slate-400 font-mono shrink-0">baru saja</span>
            </div>
            <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5 line-clamp-2 leading-relaxed">
              {{ liveToast.message }}
            </p>
          </button>
          <button 
            type="button" 
            @click="liveToast = null" 
            aria-label="Tutup notifikasi"
            title="Tutup notifikasi"
            class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors shrink-0 cursor-pointer"
          >
            <X class="w-3.5 h-3.5" />
          </button>
        </div>
      </Transition>

      <!-- Page Content Slot (Full Width) -->
      <div id="sadaya-main-content" tabindex="-1" class="flex-1 w-full outline-none">
        <slot />
      </div>
    </div>

    <!-- Real-Time Floating Chat strictly at Bottom-Right -->
    <FloatingChat :mode="mode" class="print:hidden" />

    <!-- Global Command Palette Modal (Ctrl + K) -->
    <CommandPalette ref="commandPaletteRef" :mode="mode" />

    <!-- Global In-App Confirm Dialog -->
    <ConfirmDialog />

    <!-- Vue Sonner Global Rich Toasts -->
    <Toaster position="top-right" richColors :theme="isDark ? 'dark' : 'light'" />
  </div>
</template>
