<script setup>
import { computed } from 'vue';
import { usePage, router as inertiaRouter } from '@inertiajs/vue3';
import { useRouter } from 'vue-router';
import { 
  Gauge,
  PieChart,
  Plane,
  Globe,
  History,
  User as UserIcon,
  BookOpen,
  Activity,
  LogOut,
  ChevronLeft,
  ChevronRight,
  Layers,
  X,
  MoreHorizontal
} from 'lucide-vue-next';
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

let vueRouter = null;
try {
  vueRouter = useRouter();
} catch (e) {
  vueRouter = null;
}

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

const { isOpen, isCollapsed, toggleCollapse, closeMobile } = useSidebar();

const handleNavClick = (targetPath, isExternal, e) => {
  if (typeof window !== 'undefined' && window.innerWidth < 1024) {
    closeMobile();
  }
  if (isExternal || !targetPath) return;

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

const handleLogout = () => {
  window.location.href = getUrl('/logout');
};

const navSections = computed(() => {
  return [
    {
      title: 'Utama & Analitik',
      items: [
        {
          name: 'Pusat Analytics',
          path: '/inertia/dashboard',
          url: getUrl('/inertia/dashboard'),
          icon: Gauge,
          active: !isSpa.value && (props.currentRoute === 'dashboard' || props.currentRoute === 'home'),
          badge: 'Utama',
        },
        {
          name: 'Dashboard Sanitasi BKK',
          path: '/aircraft-inspections/dashboard',
          url: getUrl('/aircraft-inspections/dashboard'),
          icon: PieChart,
          active: props.currentRoute === 'aircraft-dashboard',
          badge: 'Analytics',
        },
        {
          name: 'Data Inspeksi Pesawat',
          path: '/aircraft-inspections',
          url: getUrl('/aircraft-inspections'),
          icon: Plane,
          active: props.currentRoute === 'aircraft-inspections',
          badge: 'BKK',
        },
        {
          name: 'Dashboard SPA Mandiri',
          path: '/spa',
          url: getUrl('/spa'),
          icon: Layers,
          active: isSpa.value,
          badge: 'SPA',
        },
      ],
    },
    {
      title: 'Layanan & Tata Kelola',
      items: [
        {
          name: 'Form Publik Pesawat',
          path: '/travelconsent/public_aircraft',
          url: getUrl('/travelconsent/public_aircraft'),
          icon: Globe,
          external: true,
          badge: 'Public',
        },
        {
          name: 'Audit Trail Logs',
          path: '/audit-logs',
          url: getUrl('/audit-logs'),
          icon: History,
          active: props.currentRoute === 'audit',
          badge: 'Audit',
        },
        {
          name: 'Scramble API Docs',
          path: '/docs/api',
          url: getUrl('/docs/api'),
          icon: BookOpen,
          external: true,
          badge: 'API',
        },
        {
          name: 'Laravel Pulse',
          path: '/pulse',
          url: getUrl('/pulse'),
          icon: Activity,
          external: true,
          badge: 'Live',
        },
      ],
    },
  ];
});
</script>

<template>
  <div>
    <!-- Mobile Drawer Backdrop -->
    <transition
      enter-active-class="transition-opacity duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div 
        v-if="isOpen" 
        @click="closeMobile"
        class="fixed inset-0 z-40 bg-black/60 backdrop-blur-xs lg:hidden"
      ></div>
    </transition>

    <!-- Slim Dark Navy Sidebar Rail (#111322) matching reference UI -->
    <aside
      class="fixed top-0 bottom-0 left-0 z-40 bg-[#111322] text-white flex flex-col justify-between transition-all duration-300 font-sans select-none shadow-2xl"
      :class="[
        isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        isCollapsed ? 'w-20' : 'w-68 max-w-[85vw]',
      ]"
    >
      <!-- Top Brand S-Ribbon Logo & Expand/Collapse Trigger -->
      <div class="pt-6 pb-4 px-3 flex items-center" :class="isCollapsed ? 'justify-center' : 'justify-between px-5'">
        <a 
          :href="getUrl('/')" 
          @click="handleNavClick('/', false, $event)" 
          class="flex items-center gap-3 group cursor-pointer"
          title="SADAYA Analytics Hub"
        >
          <!-- Stylized Multi-Layered Coral/Orange/Pink Ribbon "S" Logo from Reference Image -->
          <div class="w-10 h-10 rounded-2xl flex items-center justify-center relative shrink-0 group-hover:scale-105 transition-transform">
            <svg viewBox="0 0 40 40" class="w-8 h-8 drop-shadow-md" fill="none" xmlns="http://www.w3.org/2000/svg">
              <defs>
                <linearGradient id="sadayaGrad1" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                  <stop offset="0%" stop-color="#FF8A65" />
                  <stop offset="50%" stop-color="#FF5C77" />
                  <stop offset="100%" stop-color="#F84593" />
                </linearGradient>
                <linearGradient id="sadayaGrad2" x1="40" y1="0" x2="0" y2="40" gradientUnits="userSpaceOnUse">
                  <stop offset="0%" stop-color="#FFB385" />
                  <stop offset="100%" stop-color="#FF5263" />
                </linearGradient>
              </defs>
              <!-- Top Ribbon Fold -->
              <path d="M23.5 6L11 14.5L18.5 20L30 11.5L23.5 6Z" fill="url(#sadayaGrad2)" />
              <!-- Center Diagonal Ribbon -->
              <path d="M11 14.5L29 25.5L21.5 31L9.5 21.5L11 14.5Z" fill="url(#sadayaGrad1)" />
              <!-- Bottom Ribbon Fold -->
              <path d="M29 25.5L16.5 34L10 28.5L21.5 20L29 25.5Z" fill="#F48FB1" fill-opacity="0.85" />
            </svg>
          </div>

          <div v-if="!isCollapsed" class="overflow-hidden">
            <span class="block text-base font-extrabold tracking-tight text-white">
              SADAYA
            </span>
            <span class="block text-[10px] font-medium text-[#FF7A59] tracking-wider uppercase">
              Analytics Hub
            </span>
          </div>
        </a>

        <!-- Desktop Collapse / Expand Button when Expanded -->
        <button
          v-if="!isCollapsed"
          type="button"
          @click="toggleCollapse"
          class="hidden lg:flex p-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 transition-colors cursor-pointer"
          title="Ciutkan ke Icon Rail"
        >
          <ChevronLeft class="w-4 h-4" />
        </button>

        <!-- Mobile Close Button -->
        <button
          type="button"
          @click="closeMobile"
          class="lg:hidden p-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 transition-colors cursor-pointer"
          title="Tutup Navigasi"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Vertical Navigation Rail -->
      <div class="flex-1 overflow-y-auto py-4 space-y-5 no-scrollbar" :class="isCollapsed ? 'px-0' : 'px-3'">
        <div v-for="(section, sIndex) in navSections" :key="sIndex" class="space-y-2">
          <!-- Section Title (Only when expanded) -->
          <div 
            v-if="!isCollapsed" 
            class="px-3 pb-1 text-[10px] font-bold uppercase tracking-widest text-slate-500"
          >
            {{ section.title }}
          </div>

          <!-- Navigation Links -->
          <div v-for="(item, iIndex) in section.items" :key="iIndex" class="relative flex items-center" :class="isCollapsed ? 'justify-center' : ''">
            <a
              :href="item.url"
              :target="item.external ? '_blank' : '_self'"
              @click="handleNavClick(item.path, item.external, $event)"
              class="group relative flex items-center transition-all duration-200 cursor-pointer"
              :class="[
                isCollapsed
                  ? (item.active
                      ? 'w-[78px] mr-auto py-3 pl-6 pr-3 rounded-r-full bg-[#9A9BA6] text-white shadow-lg justify-between -mr-2 z-20'
                      : 'w-12 h-11 rounded-2xl text-slate-400 hover:text-white hover:bg-white/8 justify-center')
                  : (item.active
                      ? 'w-full px-3.5 py-2.5 rounded-2xl bg-gradient-to-r from-[#FF7556] to-[#F84593] text-white font-semibold shadow-lg shadow-[#FF5C77]/25 gap-3'
                      : 'w-full px-3.5 py-2.5 rounded-2xl text-slate-400 hover:text-white hover:bg-white/8 gap-3 font-medium text-xs')
              ]"
              :title="isCollapsed ? item.name : ''"
            >
              <component 
                :is="item.icon" 
                class="w-[19px] h-[19px] shrink-0 transition-transform duration-200 group-hover:scale-110"
                :class="item.active ? 'text-white stroke-[2.2]' : 'text-slate-400 group-hover:text-white stroke-[1.75]'"
              />

              <!-- Active Dot on Slim Rail Pill (Signature detail from reference UI) -->
              <span 
                v-if="isCollapsed && item.active" 
                class="w-1.5 h-1.5 rounded-full bg-white shadow-xs shrink-0"
              ></span>

              <!-- Expanded Text & Badge -->
              <div v-if="!isCollapsed" class="flex-1 flex items-center justify-between overflow-hidden text-xs">
                <span class="truncate">{{ item.name }}</span>
                <span 
                  v-if="item.badge" 
                  class="text-[9px] font-mono px-2 py-0.5 rounded-full"
                  :class="item.active ? 'bg-white/25 text-white font-bold' : 'bg-white/8 text-slate-400'"
                >
                  {{ item.badge }}
                </span>
              </div>

              <!-- Hover Tooltip in Slim Rail Mode -->
              <div 
                v-if="isCollapsed" 
                class="fixed left-22 px-3 py-1.5 bg-[#1A1D30] text-white text-xs font-semibold rounded-xl shadow-xl border border-white/10 whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity z-50 flex items-center gap-1.5"
              >
                <span>{{ item.name }}</span>
                <span v-if="item.external" class="text-[#FF7A59]">↗</span>
              </div>
            </a>
          </div>
        </div>
      </div>

      <!-- Bottom Section: 3D Ribbon Loop Accent + Expand Toggle + User Avatar & Quick Actions -->
      <div class="pb-5 pt-2 flex flex-col items-center gap-4">
        <!-- Decorative 3D Torus/Ribbon Loop Accent + Rail Expand/Collapse Button -->
        <button
          type="button"
          @click="toggleCollapse"
          class="hidden lg:flex items-center justify-center group relative cursor-pointer focus:outline-none"
          :title="isCollapsed ? 'Perluas Sidebar (Ctrl+B)' : 'Ciutkan Sidebar (Ctrl+B)'"
        >
          <div class="w-12 h-12 rounded-2xl flex items-center justify-center relative transition-transform duration-300 group-hover:scale-110">
            <!-- 3D Loop SVG inspired by the floating yellow-orange-pink loop on the reference sidebar -->
            <svg viewBox="0 0 64 64" class="w-11 h-11 drop-shadow-[0_6px_12px_rgba(255,92,119,0.35)]" fill="none">
              <defs>
                <linearGradient id="loopGradA" x1="8" y1="12" x2="56" y2="52" gradientUnits="userSpaceOnUse">
                  <stop offset="0%" stop-color="#FFE066" />
                  <stop offset="45%" stop-color="#FF8A5B" />
                  <stop offset="100%" stop-color="#F84593" />
                </linearGradient>
                <linearGradient id="loopGradB" x1="54" y1="14" x2="14" y2="54" gradientUnits="userSpaceOnUse">
                  <stop offset="0%" stop-color="#EA80FC" />
                  <stop offset="50%" stop-color="#FF5C77" />
                  <stop offset="100%" stop-color="#FFB74D" />
                </linearGradient>
              </defs>
              <path d="M18 38C11 33 11 21 19 16C27 11 37 18 43 26C49 34 53 42 46 48C39 54 29 48 24 41" stroke="url(#loopGradB)" stroke-width="9" stroke-linecap="round" />
              <path d="M46 24C53 29 53 41 45 46C37 51 27 44 21 36C15 28 11 20 18 14C25 8 35 14 40 21" stroke="url(#loopGradA)" stroke-width="9" stroke-linecap="round" />
            </svg>
          </div>
          <span v-if="!isCollapsed" class="text-xs font-medium text-slate-400 group-hover:text-white ml-1">
            Mode Ringkas
          </span>
        </button>

        <!-- User Avatar Card & Footer Icons (Matching bottom-left of reference image) -->
        <div class="w-full px-3 flex flex-col items-center gap-3">
          <div class="flex items-center" :class="isCollapsed ? 'justify-center' : 'w-full justify-between px-2'">
            <a
              :href="getUrl('/profile')"
              @click="handleNavClick('/profile', false, $event)"
              class="relative group cursor-pointer flex items-center gap-3"
              :title="currentUser?.name || 'Profil Saya'"
            >
              <!-- Rounded Square Frame around Avatar -->
              <div class="relative p-1 rounded-2xl border border-white/20 bg-white/5 group-hover:border-[#FF7556] transition-colors">
                <img
                  :src="currentUser?.avatar_url || 'https://ui-avatars.com/api/?name=Admin&background=FF5C77&color=fff'"
                  :alt="currentUser?.name || 'User'"
                  class="w-9 h-9 rounded-xl object-cover"
                />
                <!-- Coral Notification Badge on Avatar Corner (Like '6' in reference image) -->
                <span class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-[#FF574A] text-white text-[10px] font-extrabold flex items-center justify-center shadow-md ring-2 ring-[#111322]">
                  {{ currentUser?.roles?.length || 1 }}
                </span>
              </div>

              <div v-if="!isCollapsed" class="overflow-hidden text-left">
                <span class="block text-xs font-bold text-white truncate">
                  {{ currentUser?.name || 'Administrator' }}
                </span>
                <span class="block text-[10px] text-slate-400 truncate">
                  {{ currentUser?.roles?.[0] || 'Super Admin' }}
                </span>
              </div>
            </a>

            <button
              v-if="!isCollapsed"
              type="button"
              @click="handleLogout"
              class="p-2 rounded-xl text-slate-400 hover:text-[#FF574A] hover:bg-white/8 transition-colors cursor-pointer"
              title="Keluar dari sesi"
            >
              <LogOut class="w-4 h-4" />
            </button>
          </div>

          <!-- Bottom Mini Action Row ([->  ...) when in Slim Rail mode -->
          <div v-if="isCollapsed" class="flex items-center justify-center gap-2.5 pt-0.5 text-slate-400">
            <button
              type="button"
              @click="handleLogout"
              class="p-1.5 rounded-lg hover:text-white hover:bg-white/10 transition-colors cursor-pointer"
              title="Keluar Sesi"
            >
              <LogOut class="w-3.5 h-3.5" />
            </button>
            <a
              :href="getUrl('/profile')"
              @click="handleNavClick('/profile', false, $event)"
              class="p-1.5 rounded-lg hover:text-white hover:bg-white/10 transition-colors cursor-pointer"
              title="Pengaturan Profil"
            >
              <MoreHorizontal class="w-3.5 h-3.5" />
            </a>
          </div>
        </div>
      </div>
    </aside>
  </div>
</template>
