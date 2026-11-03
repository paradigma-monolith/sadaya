<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { usePage, router as inertiaRouter } from '@inertiajs/vue3';
import { useRouter } from 'vue-router';
import { 
  LogOut, 
  User as UserIcon, 
  Bell, 
  History, 
  BookOpen, 
  Sparkles, 
  CheckCheck, 
  Radio,
  Menu,
  Compass,
  ChevronDown,
  LayoutDashboard,
  Layers,
  Package,
  Home,
  X,
  Search
} from 'lucide-vue-next';
import AdminSidebar from './AdminSidebar.vue';
import FloatingChat from './FloatingChat.vue';
import CommandPalette from './CommandPalette.vue';
import { Toaster } from 'vue-sonner';
import { useSidebar } from '@/composables/useSidebar';

const props = defineProps({
  mode: {
    type: String,
    default: 'inertia', // 'inertia' | 'spa'
  },
  currentRoute: {
    type: String,
    default: '',
  },
});

const isSpa = computed(() => props.mode === 'spa');

const prefix = computed(() => {
  if (typeof window !== 'undefined' && window.location.pathname.includes('/hybridlaravel/public')) {
    return '/hybridlaravel/public';
  }
  return '';
});

const getUrl = (path) => `${prefix.value}${path}`;

// Safely obtain Vue Router if mounted in SPA context
let vueRouter = null;
try {
  vueRouter = useRouter();
} catch (e) {
  vueRouter = null;
}

// Compute active navigation item with automatic URL pathname fallback
const activeNav = computed(() => {
  if (props.currentRoute) return props.currentRoute;
  if (typeof window === 'undefined') return '';
  const p = window.location.pathname;
  if (p.endsWith('/spa') || p.includes('/spa/')) return 'spa';
  if (p.includes('/aircraft-inspections/dashboard')) return 'aircraft-dashboard';
  if (p.includes('/aircraft-inspections')) return 'aircraft-index';
  if (p.includes('/audit-logs')) return 'audit';
  if (p.includes('/profile')) return 'profile';
  if (p.includes('/inertia/dashboard') || p === '/' || p.endsWith('/public') || p.endsWith('/public/')) return 'dashboard';
  return '';
});

// Dynamic brand pill badge depending on context (SPA Mode, Inertia Bridge)
const brandBadge = computed(() => {
  if (isSpa.value) {
    return {
      text: 'SPA Mode',
      badgeClass: 'bg-emerald-50 border-emerald-200 text-emerald-700',
      iconClass: 'bg-slate-900 text-white shadow-sm',
    };
  }
  return {
    text: 'Kelas I Bandung',
    badgeClass: 'bg-slate-100 border-slate-200 text-slate-700',
    iconClass: 'bg-slate-900 text-white shadow-sm',
  };
});

// Dropdown Navigation States & Element References
const inertiaDropdownOpen = ref(false);
const spaDropdownOpen = ref(false);
const showNotifications = ref(false);

const inertiaDropdownRef = ref(null);
const spaDropdownRef = ref(null);
const notifDropdownRef = ref(null);

const isInertiaActive = computed(() => {
  return !isSpa.value && ['dashboard', 'map', 'products', 'audit'].includes(activeNav.value);
});

const isSpaActive = computed(() => {
  return isSpa.value || ['api-explorer'].includes(activeNav.value);
});

const toggleInertiaDropdown = () => {
  inertiaDropdownOpen.value = !inertiaDropdownOpen.value;
  if (inertiaDropdownOpen.value) {
    spaDropdownOpen.value = false;
    showNotifications.value = false;
  }
};

const toggleSpaDropdown = () => {
  spaDropdownOpen.value = !spaDropdownOpen.value;
  if (spaDropdownOpen.value) {
    inertiaDropdownOpen.value = false;
    showNotifications.value = false;
  }
};

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value;
  if (showNotifications.value) {
    inertiaDropdownOpen.value = false;
    spaDropdownOpen.value = false;
  }
};

const closeAllDropdowns = () => {
  inertiaDropdownOpen.value = false;
  spaDropdownOpen.value = false;
  showNotifications.value = false;
};

const handleClickOutside = (e) => {
  if (inertiaDropdownRef.value && !inertiaDropdownRef.value.contains(e.target)) {
    inertiaDropdownOpen.value = false;
  }
  if (spaDropdownRef.value && !spaDropdownRef.value.contains(e.target)) {
    spaDropdownOpen.value = false;
  }
  if (notifDropdownRef.value && !notifDropdownRef.value.contains(e.target)) {
    showNotifications.value = false;
  }
};

// Seamless client-side navigation (Eliminates jarring full-page reloads)
const handleNav = (targetPath, e) => {
  closeAllDropdowns();

  // Case 1: In SPA mode & clicking an SPA link -> instantaneous Vue Router push
  if (isSpa.value && (targetPath === '/spa' || targetPath.startsWith('/spa/'))) {
    if (vueRouter) {
      if (e) e.preventDefault();
      const internalRoute = targetPath.replace(/^\/spa/, '') || '/';
      vueRouter.push(internalRoute);
      return;
    }
  }

  // Case 2: In Inertia mode & clicking an Inertia/Hub link -> smooth Inertia visit
  if (!isSpa.value && !targetPath.startsWith('/spa') && !targetPath.startsWith('/docs') && !targetPath.startsWith('/pulse')) {
    try {
      if (inertiaRouter && typeof inertiaRouter.visit === 'function') {
        if (e) e.preventDefault();
        inertiaRouter.visit(getUrl(targetPath));
        return;
      }
    } catch (err) {}
  }

  // Case 3: Crossing paradigms (Inertia <-> SPA) or external docs -> let browser navigate cleanly
};

// Safely access current authenticated user in either Inertia or SPA mode
const currentUser = computed(() => {
  if (isSpa.value) {
    return (typeof window !== 'undefined' && window.authUser) ? window.authUser : null;
  }
  try {
    const page = usePage();
    return page?.props?.auth?.user || null;
  } catch (e) {
    return (typeof window !== 'undefined' && window.authUser) ? window.authUser : null;
  }
});

// Synchronized persistent sidebar state via composable
const { isOpen, isCollapsed, toggleCollapse, toggleOpen } = useSidebar();

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

const fetchNotifications = async () => {
  if (!currentUser.value) return;
  try {
    const res = await fetch(getUrl('/notifications'));
    if (res.ok) {
      const data = await res.json();
      unreadCount.value = data.unread_count || 0;
      notifications.value = data.notifications || [];
    }
  } catch (e) {
    // silent fallback
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

onMounted(() => {
  fetchNotifications();

  // Listen for real-time Reverb WebSocket notifications
  if (typeof window !== 'undefined' && window.Echo && currentUser.value) {
    window.Echo.private(`App.Models.User.${currentUser.value.id}`)
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

  // Click outside listener to dismiss dropdowns
  if (typeof document !== 'undefined') {
    document.addEventListener('click', handleClickOutside);
  }

  // Toggle sidebar via keyboard shortcut (Ctrl+B / Cmd+B) and close dropdowns (Escape)
  if (typeof window !== 'undefined') {
    window.addEventListener('keydown', (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
        e.preventDefault();
        handleSidebarToggle();
      }
      if (e.key === 'Escape') {
        closeAllDropdowns();
      }
    });
  }
});

onUnmounted(() => {
  if (typeof document !== 'undefined') {
    document.removeEventListener('click', handleClickOutside);
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
  <div class="min-h-screen bg-[#f8fafc] text-slate-900 selection:bg-emerald-500 selection:text-white flex flex-col">
    <!-- Enterprise Persistent Sidebar Navigation -->
    <AdminSidebar 
      :mode="mode" 
      :currentRoute="currentRoute" 
    />

    <!-- Main Layout Wrapper (Auto-offset for persistent desktop sidebar) -->
    <div 
      class="flex-1 flex flex-col transition-all duration-300 w-full"
      :class="isCollapsed ? 'lg:pl-16' : 'lg:pl-64'"
    >
      <!-- Top Navbar Header (Clean White Executive Style matching Reference Image 1) -->
      <header class="sticky top-0 z-30 w-full bg-white/95 backdrop-blur-md border-b border-slate-200/80">
        <!-- User Impersonation Alert Banner -->
        <div v-if="currentUser?.is_impersonating" class="bg-amber-500 text-white font-medium text-xs px-4 py-1.5 flex items-center justify-between shadow-xs">
          <div class="flex items-center gap-2">
            <span>⚠️ Anda sedang login sebagai <strong>{{ currentUser.name }}</strong> (Mode Impersonasi)</span>
          </div>
          <a :href="getUrl('/impersonate/leave')" class="px-2.5 py-0.5 rounded-md bg-slate-900 text-white text-[11px] font-semibold hover:bg-slate-800 transition-colors">
            Kembali ke Super Admin ✕
          </a>
        </div>

        <div class="w-full px-4 sm:px-6 lg:px-8">
          <div class="flex items-center justify-between h-14">
            <!-- Left: Single Clean Sidebar Toggle & Mobile-Only Brand (No Duplicate Logo Next to Sidebar) -->
            <div class="flex items-center gap-3 shrink-0">
              <button
                type="button"
                @click="handleSidebarToggle"
                class="p-2 rounded-lg text-slate-500 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-200/90 shadow-2xs transition-all cursor-pointer flex items-center justify-center shrink-0"
                :title="isCollapsed ? 'Perluas Sidebar (Ctrl+B)' : 'Ciutkan Sidebar (Ctrl+B)'"
              >
                <Menu class="w-4 h-4" />
              </button>

              <!-- Mobile Brand (Hidden on lg+ because Sidebar already displays BKK Kelas I Bandung) -->
              <a :href="getUrl('/')" @click="handleNav('/', $event)" class="flex lg:hidden items-center gap-2 group cursor-pointer shrink-0">
                <img 
                  :src="getUrl('/images/logokemenkes.png')" 
                  alt="Logo Kemenkes" 
                  class="h-7 w-auto max-w-[32px] object-contain shrink-0 drop-shadow-2xs"
                  onerror="this.onerror=null;this.src='/storage/images/logokemenkes.png';"
                />
                <span class="text-sm font-bold text-slate-900 tracking-tight">
                  BKK Kelas I Bandung
                </span>
              </a>

              <!-- Desktop Clean Context Label (Replaces redundant second BKK logo) -->
              <div class="hidden lg:flex items-center gap-2 text-xs text-slate-500">
                <span class="font-semibold text-slate-800">Command Center</span>
                <span class="text-slate-300">/</span>
                <span class="text-slate-500">Wilayah Kerja Jawa Barat</span>
              </div>
            </div>

            <!-- Center Navigation Links (Harmonized with Kemenkes Turquoise & Dark Teal) -->
            <nav class="hidden lg:flex items-center gap-6 text-xs h-14 shrink-0 font-sans">
              <a
                :href="getUrl('/inertia/dashboard')" 
                @click="handleNav('/inertia/dashboard', $event)"
                class="h-full inline-flex items-center px-1 border-b-2 transition-all cursor-pointer"
                :class="(!isSpa && (activeNav === 'dashboard' || activeNav === 'home'))
                  ? 'border-[#00A99D] text-[#005851] font-bold'
                  : 'border-transparent text-slate-500 hover:text-[#00A99D] hover:border-[#99DFD6] font-medium'"
              >
                Dashboard
              </a>

              <a
                :href="getUrl('/audit-logs')" 
                @click="handleNav('/audit-logs', $event)"
                class="h-full inline-flex items-center px-1 border-b-2 transition-all cursor-pointer"
                :class="(!isSpa && activeNav === 'audit')
                  ? 'border-[#00A99D] text-[#005851] font-bold'
                  : 'border-transparent text-slate-500 hover:text-[#00A99D] hover:border-[#99DFD6] font-medium'"
              >
                Audit Trail
              </a>
            </nav>

            <!-- Right Side: Search, Generate Report Pill, Notifications & User Avatar -->
            <div class="flex items-center gap-2 shrink-0">

              <!-- Command Palette Trigger Button (Ctrl + K) -->
              <button
                type="button"
                @click="openCommandPalette"
                class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-slate-50 hover:bg-[#E6F7F5] hover:text-[#005851] text-slate-500 text-xs border border-slate-200/90 transition-all cursor-pointer"
                title="Buka Pencarian Cepat (Ctrl+K)"
              >
                <Search class="w-3.5 h-3.5 text-slate-400" />
                <span class="hidden md:inline text-[11px] font-medium">Search...</span>
                <kbd class="hidden md:inline-flex items-center px-1.5 py-0.5 text-[10px] font-mono bg-white border border-slate-200 rounded text-slate-400">⌘K</kbd>
              </button>

              <!-- Generate Report Button (Official Kemenkes Turquoise Action Button) -->
              <a
                :href="getUrl('/exports/audit-logs/pdf')"
                class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#00A99D] hover:bg-[#009388] text-white border border-[#00A99D] text-xs font-semibold shadow-xs hover:shadow transition-all"
              >
                <span>Generate Report</span>
              </a>

              <!-- Notification Bell Dropdown -->
              <div class="relative" ref="notifDropdownRef">
                <button
                  type="button"
                  @click="toggleNotifications"
                  class="relative p-1.5 rounded-lg text-slate-500 hover:text-[#005851] bg-white hover:bg-[#E6F7F5] border border-slate-200 transition-all cursor-pointer"
                  title="Notifikasi Real-Time"
                >
                  <Bell class="w-4 h-4" />
                  <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 w-4 h-4 bg-[#00A99D] text-white rounded-full text-[9px] font-bold flex items-center justify-center ring-1 ring-white">
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
                    class="fixed inset-x-3 top-16 mt-2 max-w-sm mx-auto sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-2 sm:w-84 sm:max-w-md sm:mx-0 max-h-[min(520px,calc(100vh-5.5rem))] flex flex-col bg-white border border-slate-200 rounded-2xl shadow-xl p-3.5 z-50 text-slate-800"
                  >
                    <div class="flex items-center justify-between pb-2.5 mb-2.5 border-b border-slate-100 text-xs shrink-0">
                      <div class="flex items-center gap-2">
                        <span class="font-bold text-[#005851] flex items-center gap-1.5">
                          <Radio class="w-3.5 h-3.5 text-[#00A99D]" />
                          <span>Notifikasi Sistem</span>
                        </span>
                        <span v-if="unreadCount > 0" class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#E6F7F5] text-[#005851] border border-[#99DFD6]">
                          {{ unreadCount }} Baru
                        </span>
                      </div>

                      <div class="flex items-center gap-1.5">
                        <button
                          type="button"
                          @click="markAllRead"
                          class="text-[11px] text-slate-500 hover:text-[#00A99D] flex items-center gap-1 cursor-pointer py-1 px-1.5 rounded-lg hover:bg-[#E6F7F5] transition-colors"
                        >
                          <CheckCheck class="w-3.5 h-3.5 text-[#00A99D]" />
                          <span class="hidden min-[380px]:inline">Tandai Dibaca</span>
                        </button>
                        <button
                          type="button"
                          @click="showNotifications = false"
                          class="sm:hidden p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                        >
                          <X class="w-4 h-4" />
                        </button>
                      </div>
                    </div>

                    <div class="flex-1 min-h-0 overflow-y-auto space-y-2 overscroll-contain pr-1">
                      <div 
                        v-for="n in notifications" 
                        :key="n.id" 
                        @click="markSingleRead(n)"
                        class="p-2.5 rounded-xl text-xs transition-colors cursor-pointer hover:border-[#99DFD6] border" 
                        :class="n.read_at ? 'bg-slate-50 border-slate-100 text-slate-500' : 'bg-[#E6F7F5]/50 border-[#99DFD6] text-slate-800'"
                      >
                        <div class="flex items-start justify-between gap-1.5">
                          <div class="font-semibold text-[#005851] text-xs leading-tight">
                            {{ n.data?.title || 'Pemberitahuan Sistem' }}
                          </div>
                          <span v-if="!n.read_at" class="w-1.5 h-1.5 rounded-full bg-[#00A99D] shrink-0 mt-1"></span>
                        </div>
                        <div class="text-[11px] text-slate-600 mt-1 leading-relaxed">
                          {{ n.data?.message }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-1.5 font-mono">
                          {{ n.data?.time || 'baru saja' }}
                        </div>
                      </div>

                      <div v-if="notifications.length === 0" class="py-8 text-center text-xs text-slate-400">
                        <Radio class="w-6 h-6 text-slate-300 mx-auto mb-2" />
                        <p>Belum ada notifikasi baru.</p>
                      </div>
                    </div>
                  </div>
                </Transition>
              </div>

              <!-- User Profile Avatar Pill -->
              <a
                :href="getUrl('/profile')"
                @click="handleNav('/profile', $event)"
                title="Kelola Profil Akun"
                class="flex items-center gap-2 p-1 sm:pr-3 rounded-full bg-white hover:bg-slate-50 border border-slate-200 transition-all group cursor-pointer shrink-0 max-w-[180px]"
              >
                <img
                  :src="currentUser?.avatar_url || 'https://ui-avatars.com/api/?name=Admin&background=0f172a&color=fff'"
                  :alt="currentUser?.name || 'User'"
                  class="w-7 h-7 rounded-full object-cover border border-slate-200 shrink-0"
                />
                <div class="hidden md:block text-left min-w-0 flex-1">
                  <span class="block font-semibold text-slate-800 group-hover:text-slate-950 truncate text-xs leading-tight">
                    {{ currentUser?.name }}
                  </span>
                </div>
              </a>

              <!-- Logout Button -->
              <button
                type="button"
                @click="handleLogout"
                title="Keluar dari sesi"
                class="hidden md:inline-flex items-center gap-1 p-1.5 rounded-lg text-xs font-medium text-slate-500 hover:text-rose-600 bg-white hover:bg-rose-50 border border-slate-200 hover:border-rose-200 transition-all cursor-pointer shrink-0"
              >
                <LogOut class="w-3.5 h-3.5" />
              </button>
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
          class="fixed top-16 inset-x-3 sm:inset-x-auto sm:right-6 sm:w-96 z-50 bg-white text-slate-900 border border-emerald-200 rounded-2xl p-3 shadow-xl flex items-start gap-3"
        >
          <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
            <Radio class="w-4 h-4" />
          </div>
          <div class="flex-1 min-w-0 cursor-pointer" @click="showNotifications = true; liveToast = null;">
            <div class="flex items-center justify-between gap-1">
              <span class="text-xs font-bold text-slate-900 leading-tight truncate">
                {{ liveToast.title }}
              </span>
              <span class="text-[10px] text-slate-400 font-mono shrink-0">baru saja</span>
            </div>
            <p class="text-[11px] text-slate-600 mt-0.5 line-clamp-2 leading-relaxed">
              {{ liveToast.message }}
            </p>
          </div>
          <button 
            type="button" 
            @click="liveToast = null" 
            class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition-colors shrink-0 cursor-pointer"
          >
            <X class="w-3.5 h-3.5" />
          </button>
        </div>
      </Transition>

      <!-- Page Content Slot (Full Width) -->
      <div class="flex-1 w-full">
        <slot />
      </div>
    </div>

    <!-- Real-Time Floating Chat strictly at Bottom-Right -->
    <FloatingChat :mode="mode" />

    <!-- Global Command Palette Modal (Ctrl + K) -->
    <CommandPalette ref="commandPaletteRef" :mode="mode" />

    <!-- Vue Sonner Global Rich Toasts -->
    <Toaster position="top-right" richColors />
  </div>
</template>
