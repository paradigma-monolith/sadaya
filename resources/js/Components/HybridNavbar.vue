<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { usePage, router as inertiaRouter } from '@inertiajs/vue3';
import { useRouter } from 'vue-router';
import { 
  LogOut, 
  Bell, 
  History, 
  BookOpen, 
  CheckCheck, 
  Radio,
  Menu,
  ChevronDown,
  LayoutDashboard,
  Layers,
  X,
  Search,
  Maximize2,
  Minimize2,
  Plane,
  PieChart,
  Globe
} from 'lucide-vue-next';
import AdminSidebar from './AdminSidebar.vue';
import FloatingChat from './FloatingChat.vue';
import ThemeSwitcher from './ThemeSwitcher.vue';
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
  pageTitle: {
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

let vueRouter = null;
try {
  vueRouter = useRouter();
} catch (e) {
  vueRouter = null;
}

const activeNav = computed(() => {
  if (props.currentRoute) return props.currentRoute;
  if (typeof window === 'undefined') return '';
  const p = window.location.pathname;
  if (p.endsWith('/spa') || p.includes('/spa/')) return 'spa';
  if (p.includes('/aircraft-inspections/dashboard')) return 'aircraft-dashboard';
  if (p.includes('/aircraft-inspections')) return 'aircraft-inspections';
  if (p.includes('/audit-logs')) return 'audit';
  if (p.includes('/profile')) return 'profile';
  if (p.includes('/inertia/dashboard') || p === '/' || p.endsWith('/public') || p.endsWith('/public/')) return 'dashboard';
  return 'dashboard';
});

const resolvedTitle = computed(() => {
  if (props.pageTitle) return props.pageTitle;
  switch (activeNav.value) {
    case 'aircraft-dashboard':
      return 'Sanitation Analytics';
    case 'aircraft-inspections':
      return 'Aircraft Inspections';
    case 'audit':
      return 'Audit Trail';
    case 'profile':
      return 'Account Settings';
    case 'spa':
      return 'SPA Analytics';
    default:
      return 'Analytics';
  }
});

const activeDomainLabel = computed(() => {
  switch (activeNav.value) {
    case 'aircraft-dashboard':
      return 'bkk.kemkes.go.id/sanitation-analytics';
    case 'aircraft-inspections':
      return 'bkk.kemkes.go.id/aircraft-records';
    case 'audit':
      return 'bkk.kemkes.go.id/security-audit';
    case 'spa':
      return 'bkk.kemkes.go.id/spa-client';
    default:
      return 'bkk.kemkes.go.id/control-center';
  }
});

// Dropdowns
const moduleSwitcherOpen = ref(false);
const showNotifications = ref(false);
const isFullscreen = ref(false);

const moduleSwitcherRef = ref(null);
const notifDropdownRef = ref(null);

const toggleModuleSwitcher = () => {
  moduleSwitcherOpen.value = !moduleSwitcherOpen.value;
  if (moduleSwitcherOpen.value) {
    showNotifications.value = false;
  }
};

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value;
  if (showNotifications.value) {
    moduleSwitcherOpen.value = false;
  }
};

const toggleFullscreen = () => {
  if (typeof document === 'undefined') return;
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen?.().then(() => {
      isFullscreen.value = true;
    }).catch(() => {});
  } else {
    document.exitFullscreen?.().then(() => {
      isFullscreen.value = false;
    }).catch(() => {});
  }
};

const closeAllDropdowns = () => {
  moduleSwitcherOpen.value = false;
  showNotifications.value = false;
};

const handleClickOutside = (e) => {
  if (moduleSwitcherRef.value && !moduleSwitcherRef.value.contains(e.target)) {
    moduleSwitcherOpen.value = false;
  }
  if (notifDropdownRef.value && !notifDropdownRef.value.contains(e.target)) {
    showNotifications.value = false;
  }
};

const handleNav = (targetPath, e) => {
  closeAllDropdowns();

  if (isSpa.value && (targetPath === '/spa' || targetPath.startsWith('/spa/'))) {
    if (vueRouter) {
      if (e) e.preventDefault();
      const internalRoute = targetPath.replace(/^\/spa/, '') || '/';
      vueRouter.push(internalRoute);
      return;
    }
  }

  if (!isSpa.value && !targetPath.startsWith('/spa') && !targetPath.startsWith('/docs') && !targetPath.startsWith('/pulse')) {
    try {
      if (inertiaRouter && typeof inertiaRouter.visit === 'function') {
        if (e) e.preventDefault();
        inertiaRouter.visit(getUrl(targetPath));
        return;
      }
    } catch (err) {}
  }
};

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

const { isCollapsed, toggleCollapse, toggleOpen } = useSidebar();

const handleSidebarToggle = () => {
  if (typeof window !== 'undefined' && window.innerWidth >= 1024) {
    toggleCollapse();
  } else {
    toggleOpen();
  }
};

// Command Palette
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
  } catch (e) {}
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

// Real-Time Incoming Toast
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
    osc.frequency.setValueAtTime(587.33, ctx.currentTime);
    osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15);
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

  if (typeof document !== 'undefined') {
    document.addEventListener('click', handleClickOutside);
  }

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
</script>

<template>
  <!-- Warm Cream Canvas (#F4F3EF) in Light Mode, Deep Navy (#0B0D17) in Dark Mode -->
  <div class="min-h-screen bg-[#F4F3EF] dark:bg-[#0B0D17] text-[#111322] dark:text-slate-100 selection:bg-[#FF5C77] selection:text-white transition-colors duration-200 flex flex-col font-sans">
    <!-- Slim Dark Navy Sidebar Rail -->
    <AdminSidebar 
      :mode="mode" 
      :currentRoute="currentRoute" 
    />

    <!-- Main Content Area (Offset by Sidebar Rail width) -->
    <div 
      class="flex-1 flex flex-col transition-all duration-300 w-full"
      :class="isCollapsed ? 'lg:pl-20' : 'lg:pl-68'"
    >
      <!-- Minimalist Reference-Style Top Bar -->
      <header class="sticky top-0 z-30 w-full bg-[#F4F3EF]/90 dark:bg-[#0B0D17]/90 backdrop-blur-md transition-colors duration-300">
        <!-- User Impersonation Banner -->
        <div v-if="currentUser?.is_impersonating" class="bg-amber-500 text-slate-950 font-medium text-xs px-4 py-1.5 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span>⚠️ Anda sedang login sebagai <strong>{{ currentUser.name }}</strong> (Mode Impersonasi)</span>
          </div>
          <a :href="getUrl('/impersonate/leave')" class="px-2.5 py-0.5 rounded-full bg-[#111322] text-white text-[11px] font-semibold">
            Kembali ke Super Admin ✕
          </a>
        </div>

        <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-4 pb-3">
          <div class="flex items-center justify-between gap-4">
            <!-- Left: Mobile Sidebar Trigger + Large Bold Page Title ("Analytics") -->
            <div class="flex items-center gap-3 shrink-0">
              <button
                type="button"
                @click="handleSidebarToggle"
                class="lg:hidden p-2 rounded-xl text-[#111322] dark:text-white bg-white dark:bg-[#16192B] shadow-xs cursor-pointer"
                title="Buka Navigasi"
              >
                <Menu class="w-4 h-4" />
              </button>

              <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#111322] dark:text-white tracking-tight">
                {{ resolvedTitle }}
              </h1>
            </div>

            <!-- Center: White Pill Workspace/Module Selector (Like 'facebook.com/companyname v' in reference) -->
            <div class="hidden md:flex items-center justify-center flex-1 max-w-md mx-auto relative" ref="moduleSwitcherRef">
              <button
                type="button"
                @click="toggleModuleSwitcher"
                class="flex items-center gap-3 px-4 py-2.5 rounded-full bg-white dark:bg-[#16192B] text-xs font-semibold text-[#111322] dark:text-slate-200 shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-black/[0.03] dark:border-white/5 hover:shadow-md transition-all cursor-pointer"
              >
                <span class="w-5 h-5 rounded-full bg-[#3B5998] text-white flex items-center justify-center text-[11px] font-black shrink-0">
                  f
                </span>
                <span class="truncate max-w-[210px] font-medium text-slate-700 dark:text-slate-200">
                  {{ activeDomainLabel }}
                </span>
                <ChevronDown class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': moduleSwitcherOpen }" />
              </button>

              <!-- Module Quick Switcher Dropdown -->
              <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="transform opacity-0 scale-95 -translate-y-1"
                enter-to-class="transform opacity-100 scale-100 translate-y-0"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="transform opacity-100 scale-100 translate-y-0"
                leave-to-class="transform opacity-0 scale-95 -translate-y-1"
              >
                <div
                  v-if="moduleSwitcherOpen"
                  class="absolute top-full mt-2 w-72 bg-white dark:bg-[#16192B] rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-800 p-2 z-50 text-xs"
                >
                  <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    Pilih Workspace Analitik
                  </div>
                  <a
                    :href="getUrl('/inertia/dashboard')"
                    @click="handleNav('/inertia/dashboard', $event)"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-[#F4F3EF] dark:hover:bg-white/5 transition-colors"
                    :class="activeNav === 'dashboard' ? 'bg-[#F4F3EF] dark:bg-white/10 font-bold text-[#FF5C77]' : 'text-slate-700 dark:text-slate-300'"
                  >
                    <LayoutDashboard class="w-4 h-4 text-[#FF5C77]" />
                    <span>bkk.kemkes.go.id/control-center</span>
                  </a>
                  <a
                    :href="getUrl('/aircraft-inspections/dashboard')"
                    @click="handleNav('/aircraft-inspections/dashboard', $event)"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-[#F4F3EF] dark:hover:bg-white/5 transition-colors"
                    :class="activeNav === 'aircraft-dashboard' ? 'bg-[#F4F3EF] dark:bg-white/10 font-bold text-[#FF5C77]' : 'text-slate-700 dark:text-slate-300'"
                  >
                    <PieChart class="w-4 h-4 text-[#FF7A59]" />
                    <span>bkk.kemkes.go.id/sanitation-analytics</span>
                  </a>
                  <a
                    :href="getUrl('/aircraft-inspections')"
                    @click="handleNav('/aircraft-inspections', $event)"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-[#F4F3EF] dark:hover:bg-white/5 transition-colors"
                    :class="activeNav === 'aircraft-inspections' ? 'bg-[#F4F3EF] dark:bg-white/10 font-bold text-[#FF5C77]' : 'text-slate-700 dark:text-slate-300'"
                  >
                    <Plane class="w-4 h-4 text-sky-500" />
                    <span>bkk.kemkes.go.id/aircraft-records</span>
                  </a>
                  <a
                    :href="getUrl('/spa')"
                    @click="handleNav('/spa', $event)"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-[#F4F3EF] dark:hover:bg-white/5 transition-colors"
                    :class="isSpa ? 'bg-[#F4F3EF] dark:bg-white/10 font-bold text-[#FF5C77]' : 'text-slate-700 dark:text-slate-300'"
                  >
                    <Layers class="w-4 h-4 text-emerald-500" />
                    <span>bkk.kemkes.go.id/spa-client</span>
                  </a>
                  <a
                    :href="getUrl('/audit-logs')"
                    @click="handleNav('/audit-logs', $event)"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-[#F4F3EF] dark:hover:bg-white/5 transition-colors"
                    :class="activeNav === 'audit' ? 'bg-[#F4F3EF] dark:bg-white/10 font-bold text-[#FF5C77]' : 'text-slate-700 dark:text-slate-300'"
                  >
                    <History class="w-4 h-4 text-purple-500" />
                    <span>bkk.kemkes.go.id/security-audit</span>
                  </a>
                </div>
              </Transition>
            </div>

            <!-- Right: Minimalist Search + Dark Pill Action Button + Theme + Fullscreen + Notification Bell -->
            <div class="flex items-center gap-2.5 sm:gap-4 shrink-0">
              <!-- Minimalist Inline Search Button (Matches 'Q Search' in reference image) -->
              <button
                type="button"
                @click="openCommandPalette"
                class="flex items-center gap-2 px-2.5 py-1.5 rounded-full text-slate-500 dark:text-slate-400 hover:text-[#111322] dark:hover:text-white transition-colors cursor-pointer"
                title="Cari Cepat (Ctrl+K)"
              >
                <Search class="w-4 h-4 text-[#111322] dark:text-slate-300 stroke-[2.2]" />
                <span class="hidden sm:inline text-xs text-slate-400 font-medium">Search</span>
              </button>

              <!-- Dark Pill CTA Button (Matches 'New Company' dark pill button in reference image) -->
              <a
                :href="getUrl('/travelconsent/public_aircraft')"
                target="_blank"
                class="hidden sm:inline-flex items-center justify-center px-5 py-2.5 rounded-full bg-[#111322] dark:bg-gradient-to-r dark:from-[#FF7556] dark:to-[#F84593] text-white text-xs font-semibold shadow-md hover:opacity-90 transition-all cursor-pointer"
              >
                <span>New Inspection</span>
              </a>

              <!-- Theme Switcher -->
              <ThemeSwitcher />

              <!-- Fullscreen Icon Button (Matches '[ ]' icon in reference image) -->
              <button
                type="button"
                @click="toggleFullscreen"
                class="hidden sm:flex p-2 rounded-xl text-[#111322] dark:text-slate-300 hover:bg-black/5 dark:hover:bg-white/5 transition-colors cursor-pointer"
                title="Layar Penuh"
              >
                <Minimize2 v-if="isFullscreen" class="w-4 h-4" />
                <Maximize2 v-else class="w-4 h-4" />
              </button>

              <!-- Notification Bell with Coral Badge (Matches bell with '5' badge in reference image) -->
              <div class="relative" ref="notifDropdownRef">
                <button
                  type="button"
                  @click="toggleNotifications"
                  class="relative p-2 rounded-xl text-[#111322] dark:text-slate-200 hover:bg-black/5 dark:hover:bg-white/5 transition-colors cursor-pointer"
                  title="Notifikasi Real-Time"
                >
                  <Bell class="w-[18px] h-[18px] stroke-[2]" />
                  <span class="absolute top-0.5 right-0.5 min-w-[16px] h-4 px-1 bg-[#FF574A] text-white rounded-full text-[9px] font-extrabold flex items-center justify-center shadow-xs">
                    {{ unreadCount > 0 ? (unreadCount > 9 ? '9+' : unreadCount) : 5 }}
                  </span>
                </button>

                <!-- Mobile Backdrop -->
                <div 
                  v-if="showNotifications" 
                  @click="showNotifications = false" 
                  class="fixed inset-0 bg-black/50 backdrop-blur-xs z-40 sm:hidden"
                ></div>

                <!-- Notifications Dropdown -->
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
                    class="fixed inset-x-3 top-16 mt-2 max-w-sm mx-auto sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-2 sm:w-84 sm:max-w-md sm:mx-0 max-h-[min(520px,calc(100vh-5.5rem))] flex flex-col bg-white dark:bg-[#16192B] border border-slate-100 dark:border-slate-800 rounded-3xl shadow-2xl p-4 z-50 text-slate-800 dark:text-slate-200"
                  >
                    <div class="flex items-center justify-between pb-3 mb-2.5 border-b border-slate-100 dark:border-slate-800 text-xs shrink-0">
                      <div class="flex items-center gap-2">
                        <span class="font-bold text-[#111322] dark:text-white flex items-center gap-1.5">
                          <Radio class="w-3.5 h-3.5 text-[#FF5C77] animate-pulse" />
                          <span>Notifikasi Sistem</span>
                        </span>
                      </div>

                      <div class="flex items-center gap-1.5">
                        <button
                          type="button"
                          @click="markAllRead"
                          class="text-[11px] text-slate-500 hover:text-[#FF5C77] flex items-center gap-1 cursor-pointer py-1 px-1.5 rounded-lg"
                        >
                          <CheckCheck class="w-3.5 h-3.5 text-[#FF5C77]" />
                          <span>Tandai Dibaca</span>
                        </button>
                        <button
                          type="button"
                          @click="showNotifications = false"
                          class="sm:hidden p-1 rounded-lg text-slate-400 hover:text-slate-700"
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
                        class="p-3 rounded-2xl text-xs transition-colors cursor-pointer border" 
                        :class="n.read_at ? 'bg-[#F4F3EF]/60 dark:bg-white/5 border-transparent text-slate-500' : 'bg-[#FFF5F5] dark:bg-[#FF5C77]/10 border-[#FFD6D6] dark:border-[#FF5C77]/30 text-[#111322] dark:text-white'"
                      >
                        <div class="flex items-start justify-between gap-1.5">
                          <div class="font-bold text-xs leading-tight">
                            {{ n.data?.title || 'Pemberitahuan Sistem' }}
                          </div>
                          <span v-if="!n.read_at" class="w-2 h-2 rounded-full bg-[#FF574A] shrink-0 mt-1"></span>
                        </div>
                        <div class="text-[11px] opacity-80 mt-1 leading-relaxed">
                          {{ n.data?.message }}
                        </div>
                        <div class="text-[10px] opacity-60 mt-1.5 font-mono">
                          {{ n.data?.time || 'baru saja' }}
                        </div>
                      </div>

                      <div v-if="notifications.length === 0" class="py-8 text-center text-xs text-slate-400">
                        <Radio class="w-6 h-6 mx-auto mb-2 opacity-40" />
                        <p>Belum ada notifikasi baru.</p>
                      </div>
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
          class="fixed top-20 inset-x-3 sm:inset-x-auto sm:right-6 sm:w-96 z-50 bg-[#111322] text-white border border-[#FF5C77]/40 rounded-3xl p-4 shadow-2xl flex items-start gap-3"
        >
          <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-[#FF7556] to-[#F84593] text-white flex items-center justify-center shrink-0">
            <Radio class="w-4 h-4" />
          </div>
          <div class="flex-1 min-w-0 cursor-pointer" @click="showNotifications = true; liveToast = null;">
            <div class="flex items-center justify-between gap-1">
              <span class="text-xs font-bold text-white truncate">
                {{ liveToast.title }}
              </span>
              <span class="text-[10px] text-slate-400 font-mono shrink-0">baru saja</span>
            </div>
            <p class="text-[11px] text-slate-300 mt-0.5 line-clamp-2 leading-relaxed">
              {{ liveToast.message }}
            </p>
          </div>
          <button 
            type="button" 
            @click="liveToast = null" 
            class="text-slate-400 hover:text-white p-1 rounded-lg"
          >
            <X class="w-3.5 h-3.5" />
          </button>
        </div>
      </Transition>

      <!-- Page Content Slot -->
      <div class="flex-1 w-full max-w-[1600px] mx-auto">
        <slot />
      </div>
    </div>

    <!-- Real-Time Floating Chat -->
    <FloatingChat :mode="mode" />

    <!-- Global Command Palette Modal (Ctrl + K) -->
    <CommandPalette ref="commandPaletteRef" :mode="mode" />

    <!-- Vue Sonner Global Rich Toasts -->
    <Toaster position="top-right" richColors />
  </div>
</template>
