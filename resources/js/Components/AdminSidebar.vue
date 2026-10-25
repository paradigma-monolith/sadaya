<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage, router as inertiaRouter } from '@inertiajs/vue3';
import { useRouter } from 'vue-router';
import { 
  Home, 
  LayoutDashboard, 
  Globe,
  Boxes, 
  ShoppingBag, 
  Compass, 
  History, 
  User as UserIcon, 
  BookOpen, 
  Activity, 
  LogOut, 
  ChevronLeft, 
  ChevronRight, 
  Sparkles,
  Layers,
  X,
  ShieldCheck,
  FileSpreadsheet,
  Plane,
  BarChart3
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

// Safely obtain Vue Router if in SPA mode
let vueRouter = null;
try {
  vueRouter = useRouter();
} catch (e) {
  vueRouter = null;
}

// Safely resolve active authenticated user
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
const { isOpen, isCollapsed, toggleCollapse, closeMobile } = useSidebar();

const handleNavClick = (targetPath, isExternal, e) => {
  if (typeof window !== 'undefined' && window.innerWidth < 1024) {
    closeMobile();
  }
  if (isExternal || !targetPath) return;

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
};

const handleLogout = () => {
  window.location.href = getUrl('/logout');
};

const navSections = computed(() => {
  return [
    {
      title: 'Pusat Kontrol',
      items: [
        {
          name: 'Pusat Dashboard',
          path: '/inertia/dashboard',
          url: getUrl('/inertia/dashboard'),
          icon: LayoutDashboard,
          active: !isSpa.value && (props.currentRoute === 'dashboard' || props.currentRoute === 'home'),
          badge: 'Utama',
          badgeColor: 'bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
        },
      ],
    },
    {
      title: 'Single Page App',
      items: [
        {
          name: 'Dashboard (SPA)',
          path: '/spa',
          url: getUrl('/spa'),
          icon: Layers,
          active: isSpa.value,
          badge: 'Client',
          badgeColor: 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
        },
      ],
    },
    {
      title: 'Karantina Kesehatan Udara',
      items: [
        {
          name: 'Data Inspeksi Pesawat',
          path: '/aircraft-inspections',
          url: getUrl('/aircraft-inspections'),
          icon: Plane,
          active: props.currentRoute === 'aircraft-inspections',
          badge: 'BKK',
          badgeColor: 'bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800',
        },
        {
          name: 'Dashboard Analisis',
          path: '/aircraft-inspections/dashboard',
          url: getUrl('/aircraft-inspections/dashboard'),
          icon: BarChart3,
          active: props.currentRoute === 'aircraft-dashboard',
          badge: 'ECharts',
          badgeColor: 'bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
        },
        {
          name: 'Form Publik Pesawat',
          path: '/travelconsent/public_aircraft',
          url: getUrl('/travelconsent/public_aircraft'),
          icon: Globe,
          external: true,
          badge: 'Public',
          badgeColor: 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
        },
      ],
    },
    {
      title: 'Tata Kelola & Keamanan',
      items: [
        {
          name: 'Audit Trail Logs',
          path: '/audit-logs',
          url: getUrl('/audit-logs'),
          icon: History,
          active: props.currentRoute === 'audit',
          badge: 'Spatie',
          badgeColor: 'bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800',
        },
        {
          name: 'Profil & Keamanan',
          path: '/profile',
          url: getUrl('/profile'),
          icon: UserIcon,
          active: props.currentRoute === 'profile',
          badge: 'RBAC',
          badgeColor: 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
        },
      ],
    },
    {
      title: 'DevTools & Monitoring',
      items: [
        {
          name: 'Scramble API Docs',
          path: '/docs/api',
          url: getUrl('/docs/api'),
          icon: BookOpen,
          external: true,
          badge: 'OpenAPI',
          badgeColor: 'bg-cyan-100 dark:bg-cyan-950 text-cyan-700 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800',
        },
        {
          name: 'Laravel Pulse',
          path: '/pulse',
          url: getUrl('/pulse'),
          icon: Activity,
          external: true,
          badge: 'Metrics',
          badgeColor: 'bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
        },
      ],
    },
  ];
});
</script>

<template>
  <div>
    <!-- Backdrop (shown ONLY on mobile when drawer is open) -->
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
        class="fixed inset-0 z-40 bg-slate-950/80 backdrop-blur-sm lg:hidden"
      ></div>
    </transition>

    <!-- Sidebar Aside Element (Always visible on desktop, responsive drawer on mobile) -->
    <aside
      class="fixed top-0 bottom-0 left-0 z-40 bg-white/95 dark:bg-slate-950/95 border-r border-slate-200 dark:border-slate-800/90 backdrop-blur-2xl flex flex-col transition-all duration-300 font-sans shadow-2xl lg:shadow-none"
      :class="[
        // Visibility: ON DESKTOP ALWAYS SHOWN (lg:translate-x-0)! On mobile slides in when isOpen
        isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        // Desktop width: compact w-20 or full w-72
        isCollapsed ? 'w-20' : 'w-72 max-w-[85vw]',
      ]"
    >
      <!-- Sidebar Header -->
      <div class="h-16 px-4 border-b border-slate-200 dark:border-slate-800/80 flex items-center justify-between">
        <a :href="getUrl('/')" @click="handleNavClick('/', false, $event)" class="flex items-center gap-3 overflow-hidden group cursor-pointer">
          <div 
            class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center font-black text-white shadow-lg transition-all"
            :class="isSpa 
              ? 'bg-gradient-to-tr from-emerald-600 to-teal-400 shadow-emerald-500/20' 
              : 'bg-gradient-to-tr from-indigo-600 to-violet-500 shadow-indigo-500/20'"
          >
            <span class="text-xs tracking-wider">HL</span>
          </div>

          <div v-if="!isCollapsed" class="transition-opacity duration-200">
            <span class="block text-sm font-bold text-slate-900 dark:text-white tracking-tight group-hover:text-indigo-600 dark:group-hover:text-slate-200 transition-colors">
              Hybrid Laravel
            </span>
            <span class="block text-[10px] font-mono text-indigo-600 dark:text-indigo-400">
              Starter Kit v2.0
            </span>
          </div>
        </a>

        <!-- Controls: Desktop Collapse, Mobile Close -->
        <div class="flex items-center gap-1">
          <!-- Desktop Collapse Button -->
          <button
            type="button"
            @click="toggleCollapse"
            class="hidden lg:flex p-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/80 border border-slate-200 dark:border-slate-800 transition-colors cursor-pointer"
            :title="isCollapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'"
          >
            <ChevronRight v-if="isCollapsed" class="w-4 h-4" />
            <ChevronLeft v-else class="w-4 h-4" />
          </button>

          <!-- Close Drawer Button (Mobile Only) -->
          <button
            type="button"
            @click="closeMobile"
            class="lg:hidden p-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
            title="Tutup Navigasi"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Navigation Links Container -->
      <div class="flex-1 overflow-y-auto px-3 py-4 space-y-5 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-800">
        <div v-for="(section, sIndex) in navSections" :key="sIndex" class="space-y-1">
          <!-- Section Title (Hidden when collapsed) -->
          <div 
            v-if="!isCollapsed" 
            class="px-2.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500"
          >
            {{ section.title }}
          </div>
          <div v-else class="h-[1px] bg-slate-200 dark:bg-slate-800/70 my-2 mx-1"></div>

          <!-- Section Links -->
          <a
            v-for="(item, iIndex) in section.items"
            :key="iIndex"
            :href="item.url"
            :target="item.external ? '_blank' : '_self'"
            @click="handleNavClick(item.path, item.external, $event)"
            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all group relative cursor-pointer"
            :class="item.active 
              ? (isSpa ? 'bg-teal-50 dark:bg-teal-600/20 text-teal-800 dark:text-teal-200 font-semibold border border-teal-200 dark:border-teal-700/40' : 'bg-indigo-50 dark:bg-indigo-600/20 text-indigo-800 dark:text-indigo-200 font-semibold border border-indigo-200 dark:border-indigo-700/40')
              : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900 border border-transparent'"
            :title="isCollapsed ? item.name : ''"
          >
            <component 
              :is="item.icon" 
              class="w-4 h-4 flex-shrink-0 transition-transform group-hover:scale-110"
              :class="item.active ? (isSpa ? 'text-teal-600 dark:text-teal-400' : 'text-indigo-600 dark:text-indigo-400') : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200'"
            />

            <!-- Label & Badge -->
            <div v-if="!isCollapsed" class="flex-1 flex items-center justify-between overflow-hidden">
              <span class="truncate">{{ item.name }}</span>
              <span 
                v-if="item.badge" 
                class="text-[9px] font-mono px-1.5 py-0.2 rounded border"
                :class="item.badgeColor"
              >
                {{ item.badge }}
              </span>
            </div>

            <!-- Tooltip Popup when collapsed -->
            <div 
              v-if="isCollapsed" 
              class="absolute left-full ml-3 px-2.5 py-1 bg-slate-900 text-white text-[11px] font-medium rounded-lg shadow-xl border border-slate-700 whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity z-50"
            >
              {{ item.name }}
              <span v-if="item.external" class="text-cyan-400 ml-1">↗</span>
            </div>
          </a>
        </div>
      </div>


      <!-- Sidebar Footer: User Profile & Quick Logout -->
      <div class="p-3 border-t border-slate-200 dark:border-slate-800/80 bg-slate-50/90 dark:bg-slate-950/90">
        <div class="flex items-center gap-2.5" :class="isCollapsed ? 'justify-center' : 'justify-between'">
          
          <!-- Avatar + Info -->
          <a 
            :href="getUrl('/profile')"
            @click="handleNavClick('/profile', false, $event)"
            class="flex items-center gap-2.5 overflow-hidden group cursor-pointer"
            :title="isCollapsed ? (currentUser?.name || 'Profil Akun') : ''"
          >
            <img
              :src="currentUser?.avatar_url || 'https://ui-avatars.com/api/?name=Admin&background=6366f1&color=fff'"
              :alt="currentUser?.name || 'User'"
              class="w-8 h-8 rounded-full object-cover border border-indigo-500/40 flex-shrink-0 group-hover:border-indigo-400 transition-colors"
            />
            <div v-if="!isCollapsed" class="overflow-hidden text-left">
              <span class="block text-xs font-semibold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-white truncate transition-colors">
                {{ currentUser?.name || 'Administrator' }}
              </span>
              <span class="block text-[10px] font-mono text-indigo-600 dark:text-indigo-400 truncate">
                {{ currentUser?.roles?.[0] || 'Superadmin' }}
              </span>
            </div>
          </a>

          <!-- Logout Button -->
          <button
            v-if="!isCollapsed"
            type="button"
            @click="handleLogout"
            class="p-2 rounded-xl text-slate-400 hover:text-rose-600 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-transparent hover:border-rose-200 dark:hover:border-rose-900/40 transition-all cursor-pointer"
            title="Keluar dari sesi"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>
      </div>

    </aside>
  </div>
</template>
