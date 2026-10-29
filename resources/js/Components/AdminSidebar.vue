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
      title: 'MENU UTAMA',
      items: [
        {
          name: 'Dashboard Utama',
          path: '/inertia/dashboard',
          url: getUrl('/inertia/dashboard'),
          icon: LayoutDashboard,
          iconColor: 'text-emerald-600',
          active: !isSpa.value && (props.currentRoute === 'dashboard' || props.currentRoute === 'home'),
        },
      ],
    },
    {
      title: 'KARANTINA KESEHATAN UDARA',
      items: [
        {
          name: 'Data Inspeksi Pesawat',
          path: '/aircraft-inspections',
          url: getUrl('/aircraft-inspections'),
          icon: Plane,
          iconColor: 'text-indigo-600',
          active: props.currentRoute === 'aircraft-inspections',
        },
        {
          name: 'Analitik Sanitasi',
          path: '/aircraft-inspections/dashboard',
          url: getUrl('/aircraft-inspections/dashboard'),
          icon: BarChart3,
          iconColor: 'text-emerald-600',
          active: props.currentRoute === 'aircraft-dashboard',
        },
        {
          name: 'Form Publik Pesawat',
          path: '/travelconsent/public_aircraft',
          url: getUrl('/travelconsent/public_aircraft'),
          icon: Globe,
          iconColor: 'text-amber-600',
          external: true,
        },
      ],
    },
    {
      title: 'TATA KELOLA & KEAMANAN',
      items: [
        {
          name: 'Audit Trail Logs',
          path: '/audit-logs',
          url: getUrl('/audit-logs'),
          icon: History,
          iconColor: 'text-rose-500',
          active: props.currentRoute === 'audit',
        },
        {
          name: 'Profil & Keamanan',
          path: '/profile',
          url: getUrl('/profile'),
          icon: UserIcon,
          iconColor: 'text-slate-600',
          active: props.currentRoute === 'profile',
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
        class="fixed inset-0 z-40 bg-slate-900/30 backdrop-blur-2xs lg:hidden"
      ></div>
    </transition>

    <!-- Sidebar Aside Element (Clean White Theme matching Reference Image 2) -->
    <aside
      class="fixed top-0 bottom-0 left-0 z-40 bg-white border-r border-slate-200/80 flex flex-col transition-all duration-300 font-sans shadow-xl lg:shadow-none"
      :class="[
        isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        isCollapsed ? 'w-20' : 'w-72 max-w-[85vw]',
      ]"
    >
      <!-- Sidebar Header -->
      <div
        class="h-14 border-b border-slate-200/80 flex items-center"
        :class="isCollapsed ? 'justify-center px-2' : 'justify-between px-4'"
      >
        <a
          :href="getUrl('/')"
          @click="handleNavClick('/', false, $event)"
          class="flex items-center gap-2.5 overflow-hidden group cursor-pointer"
          :class="isCollapsed ? 'justify-center' : ''"
          title="BKK Kelas I Bandung"
        >
          <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center font-black text-white bg-slate-900 shadow-xs">
            <span class="text-[10px] font-mono tracking-wider">BKK</span>
          </div>

          <div v-if="!isCollapsed" class="transition-opacity duration-200 min-w-0">
            <span class="block text-sm font-bold text-slate-900 tracking-tight group-hover:text-emerald-600 transition-colors truncate">
              BKK Kelas I Bandung
            </span>
            <span class="block text-[10px] text-slate-500 font-medium truncate">
              Induk &amp; 6 Wilayah Kerja
            </span>
          </div>
        </a>

        <!-- Mobile Close Button Only (Desktop uses the single toggle button in Top Navbar) -->
        <button
          type="button"
          @click="closeMobile"
          class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors cursor-pointer shrink-0"
          title="Tutup Navigasi"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Navigation Links Container -->
      <div class="flex-1 overflow-y-auto px-3 py-4 space-y-5">
        <div v-for="(section, sIndex) in navSections" :key="sIndex" class="space-y-1">
          <!-- Section Title -->
          <div 
            v-if="!isCollapsed" 
            class="px-2.5 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400"
          >
            {{ section.title }}
          </div>
          <div v-else class="h-[1px] bg-slate-100 my-2 mx-1"></div>

          <!-- Section Links -->
          <a
            v-for="(item, iIndex) in section.items"
            :key="iIndex"
            :href="item.url"
            :target="item.external ? '_blank' : '_self'"
            @click="handleNavClick(item.path, item.external, $event)"
            class="flex items-center rounded-lg text-xs transition-all group relative cursor-pointer"
            :class="[
              isCollapsed ? 'justify-center py-2.5 px-2' : 'gap-3 px-3 py-2',
              item.active 
                ? 'bg-slate-100 text-slate-900 font-semibold border border-slate-200/80 shadow-2xs'
                : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium border border-transparent'
            ]"
            :title="isCollapsed ? item.name : ''"
          >
            <component 
              :is="item.icon" 
              class="w-4 h-4 flex-shrink-0 transition-transform group-hover:scale-105"
              :class="item.active ? item.iconColor : `${item.iconColor} opacity-80 group-hover:opacity-100`"
            />

            <!-- Label -->
            <div v-if="!isCollapsed" class="flex-1 flex items-center overflow-hidden">
              <span class="truncate">{{ item.name }}</span>
            </div>

            <!-- Tooltip Popup when collapsed -->
            <div 
              v-if="isCollapsed" 
              class="absolute left-full ml-3 px-2.5 py-1 bg-slate-900 text-white text-[11px] font-medium rounded-lg shadow-lg whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity z-50"
            >
              {{ item.name }}
            </div>
          </a>
        </div>
      </div>

      <!-- Quick Action Button (+ New Inspection Form like "+ New Project" in Reference Image 2) -->
      <div v-if="!isCollapsed" class="px-3 pb-2.5">
        <a
          :href="getUrl('/travelconsent/public_aircraft')"
          target="_blank"
          class="w-full py-2 px-3 rounded-lg bg-slate-50 hover:bg-slate-100 border border-slate-200/90 text-slate-700 hover:text-slate-900 text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors"
        >
          <span>+ Input Inspeksi Pesawat</span>
        </a>
      </div>

      <!-- Sidebar Footer: User Profile & Quick Logout -->
      <div class="p-3 border-t border-slate-200/80 bg-slate-50/60">
        <div class="flex items-center gap-2.5" :class="isCollapsed ? 'justify-center' : 'justify-between'">
          <a 
            :href="getUrl('/profile')"
            @click="handleNavClick('/profile', false, $event)"
            class="flex items-center gap-2.5 overflow-hidden group cursor-pointer"
            :title="isCollapsed ? (currentUser?.name || 'Profil Akun') : ''"
          >
            <img
              :src="currentUser?.avatar_url || 'https://ui-avatars.com/api/?name=Admin&background=0f172a&color=fff'"
              :alt="currentUser?.name || 'User'"
              class="w-8 h-8 rounded-full object-cover border border-slate-200 flex-shrink-0"
            />
            <div v-if="!isCollapsed" class="overflow-hidden text-left">
              <span class="block text-xs font-semibold text-slate-800 group-hover:text-slate-950 truncate">
                {{ currentUser?.name || 'Administrator' }}
              </span>
              <span class="block text-[10px] text-emerald-600 font-medium truncate">
                {{ currentUser?.roles?.[0] || 'Superadmin' }}
              </span>
            </div>
          </a>

          <button
            v-if="!isCollapsed"
            type="button"
            @click="handleLogout"
            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-all cursor-pointer"
            title="Keluar dari sesi"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>
      </div>

    </aside>
  </div>
</template>
