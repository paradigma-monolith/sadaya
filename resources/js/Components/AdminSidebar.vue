<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage, router as inertiaRouter } from '@inertiajs/vue3';
import { useRouter } from 'vue-router';
import { 
  Home, 
  LayoutDashboard, 
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
  ChevronDown,
  ClipboardCheck,
  FileCheck,
  Layers,
  X,
  ShieldCheck,
  FileSpreadsheet,
  BarChart3,
  Ship,
  ExternalLink,
  Database,
  Leaf,
  Utensils,
  Droplets,
  Building2,
  Bug,
  CalendarDays
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
  if (!isSpa.value && !targetPath.startsWith('/spa') && !targetPath.startsWith('/docs') && !targetPath.startsWith('/pulse') && !targetPath.startsWith('/travelconsent')) {
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

// Collapsible dropdown state for sidebar sections & submenus
const openSections = ref({});
const openSubMenus = ref({});

const isSectionOpen = (section) => {
  if (section.collapsible === false) return true;
  if (openSections.value[section.title] === undefined) {
    const hasActive = section.items && section.items.some(i => i.active || (i.children && i.children.some(c => c.active)));
    openSections.value[section.title] = hasActive ? true : false;
  }
  return !!openSections.value[section.title];
};

const toggleSection = (title) => {
  openSections.value[title] = !openSections.value[title];
  try {
    localStorage.setItem('sadaya_sidebar_sections', JSON.stringify(openSections.value));
  } catch (e) {}
};

const isSubMenuOpen = (item) => {
  if (openSubMenus.value[item.name] !== undefined) {
    return !!openSubMenus.value[item.name];
  }
  const hasActive = item.children && item.children.some(c => c.active);
  openSubMenus.value[item.name] = hasActive ? true : false;
  return !!openSubMenus.value[item.name];
};

const toggleSubMenu = (name) => {
  openSubMenus.value[name] = !openSubMenus.value[name];
  try {
    localStorage.setItem('sadaya_sidebar_submenus', JSON.stringify(openSubMenus.value));
  } catch (e) {}
};

onMounted(() => {
  try {
    const saved = localStorage.getItem('sadaya_sidebar_sections');
    if (saved) {
      const parsed = JSON.parse(saved);
      if (parsed && typeof parsed === 'object') {
        openSections.value = { ...openSections.value, ...parsed };
      }
    }
    const savedSubs = localStorage.getItem('sadaya_sidebar_submenus');
    if (savedSubs) {
      const parsedSubs = JSON.parse(savedSubs);
      if (parsedSubs && typeof parsedSubs === 'object') {
        openSubMenus.value = { ...openSubMenus.value, ...parsedSubs };
      }
    }
  } catch (e) {}

  // Ensure section & submenu containing active route is always expanded
  navSections.value.forEach((sec) => {
    if (sec.items) {
      const secHasActive = sec.items.some(i => i.active || (i.children && i.children.some(c => c.active)));
      if (secHasActive) {
        openSections.value[sec.title] = true;
      }
      sec.items.forEach((item) => {
        if (item.children && item.children.some(c => c.active)) {
          openSubMenus.value[item.name] = true;
        }
      });
    }
  });
});

const navSections = computed(() => {
  const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';

  return [
    {
      title: 'MENU UTAMA',
      collapsible: false,
      items: [
        {
          name: 'Dashboard Utama',
          path: '/dashboard',
          url: getUrl('/dashboard'),
          icon: LayoutDashboard,
          iconColor: 'text-[#00A99D]',
          active: !isSpa.value && (
            props.currentRoute === 'dashboard' || 
            props.currentRoute === 'home' || 
            props.currentRoute === 'sinkarkes-dashboard' ||
            currentPath.endsWith('/dashboard') ||
            currentPath === '/'
          ),
        },
      ],
    },
    {
      title: 'Tim Kerja Pengawasan Faktor Risiko Kesehatan Alat Angkut dan Barang',
      icon: Ship,
      iconColor: 'text-cyan-600',
      collapsible: true,
      items: [
        {
          name: 'Travel Consent',
          path: '/travel-consent',
          url: getUrl('/travel-consent'),
          icon: FileCheck,
          iconColor: 'text-[#00A99D]',
          active:
            props.currentRoute?.startsWith('travel-consent') ||
            props.currentRoute === 'aircraft-inspections' ||
            props.currentRoute === 'aircraft-index' ||
            props.currentRoute === 'ship-inspections' ||
            props.currentRoute === 'flight-medical-alat' ||
            props.currentRoute === 'flight-medical-obat' ||
            currentPath.includes('/travel-consent') ||
            currentPath.includes('/aircraft-inspections') ||
            currentPath.includes('/ship-inspections') ||
            currentPath.includes('/flight-medical-inspections'),
        },
        {
          name: 'Pengawasan Kapal (TANGKAP)',
          path: '/ship-surveillance',
          url: getUrl('/ship-surveillance'),
          icon: Ship,
          iconColor: 'text-[#00A99D]',
          active: props.currentRoute === 'ship-surveillance' || currentPath.includes('/ship-surveillance'),
        },
        {
          name: 'Rekap Harian Kapal & Pesawat',
          path: '/sinkarkes-daily',
          url: getUrl('/sinkarkes-daily'),
          icon: CalendarDays,
          iconColor: 'text-teal-600',
          active: props.currentRoute === 'sinkarkes-daily' || currentPath.includes('/sinkarkes-daily'),
        },
        {
          name: 'Form Input SINKARKES',
          path: '/sinkarkes',
          url: getUrl('/sinkarkes'),
          icon: FileSpreadsheet,
          iconColor: 'text-[#005851]',
          active: props.currentRoute === 'sinkarkes' || (currentPath.includes('/sinkarkes') && !currentPath.includes('/sinkarkes/dashboard') && !currentPath.includes('/sinkarkes-daily')),
        },
      ],
    },
    {
      title: 'TIM KERJA PENGAWASAN FAKTOR RISIKO KESEHATAN LINGKUNGAN',
      icon: Leaf,
      iconColor: 'text-emerald-600',
      collapsible: true,
      items: [
        {
          name: 'Sanitasi Lingkungan',
          path: '/sanitasi-lingkungan',
          url: getUrl('/sanitasi-lingkungan'),
          icon: ClipboardCheck,
          iconColor: 'text-emerald-600',
          active: props.currentRoute?.startsWith('sanitasi') || props.currentRoute === 'prl' || currentPath.includes('/sanitasi-lingkungan') || (currentPath.includes('/prl') && !currentPath.includes('/prl/sivera')),
        },
        {
          name: 'SIVERA (Vektor & Reservoar)',
          path: '/sivera',
          url: getUrl('/sivera'),
          icon: Bug,
          iconColor: 'text-rose-500',
          active: props.currentRoute === 'sivera' || props.currentRoute === 'prl-sivera' || currentPath.includes('/sivera') || currentPath.includes('/prl/sivera'),
        },
      ],
    },
    {
      title: 'TATA KELOLA & KEAMANAN',
      icon: ShieldCheck,
      iconColor: 'text-slate-600',
      collapsible: true,
      items: [
        {
          name: 'Audit Trail Logs',
          path: '/audit-logs',
          url: getUrl('/audit-logs'),
          icon: History,
          iconColor: 'text-slate-600',
          active: props.currentRoute === 'audit' || currentPath.includes('/audit-logs'),
        },
        {
          name: 'Profil & Keamanan',
          path: '/profile',
          url: getUrl('/profile'),
          icon: UserIcon,
          iconColor: 'text-slate-600',
          active: props.currentRoute === 'profile' || currentPath.includes('/profile'),
        },
        {
          name: 'Pusat Import Database',
          path: '/database-import',
          url: getUrl('/database-import'),
          icon: Database,
          iconColor: 'text-indigo-600',
          active: props.currentRoute === 'database-import' || currentPath.includes('/database-import'),
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
      class="fixed top-0 bottom-0 left-0 z-40 bg-white border-r border-slate-200/90 flex flex-col transition-all duration-200 font-sans shadow-xl lg:shadow-none"
      :class="[
        isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        isCollapsed ? 'w-[60px]' : 'w-64 max-w-[85vw]',
      ]"
    >
      <!-- Sidebar Header -->
      <div
        class="h-12 border-b border-slate-200/90 flex items-center shrink-0"
        :class="isCollapsed ? 'justify-center px-2' : 'justify-between px-3.5'"
      >
        <a
          :href="getUrl('/')"
          @click="handleNavClick('/', false, $event)"
          class="flex items-center gap-2 overflow-hidden group cursor-pointer"
          :class="isCollapsed ? 'justify-center' : ''"
          title="Kementerian Kesehatan RI • BKK Kelas I Bandung"
        >
          <img 
            :src="getUrl('/images/LOGO KARANTINA.png')" 
            alt="Logo Karantina Kesehatan" 
            class="h-7 w-auto max-w-[30px] object-contain flex-shrink-0 transition-transform group-hover:scale-105"
            onerror="this.onerror=null;this.src='/storage/images/LOGO KARANTINA.png';"
          />

          <div v-if="!isCollapsed" class="transition-opacity duration-200 min-w-0 leading-tight">
            <span class="block text-[13px] font-bold text-slate-900 tracking-tight group-hover:text-[#00A99D] transition-colors truncate">
              BKK Kelas I Bandung
            </span>
            <span class="block text-[10px] text-slate-500 font-medium truncate">
              Kementerian Kesehatan RI
            </span>
          </div>
        </a>

        <!-- Mobile Close Button Only (Desktop uses the single toggle button in Top Navbar) -->
        <button
          type="button"
          @click="closeMobile"
          class="lg:hidden p-1 rounded-md text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors cursor-pointer shrink-0"
          title="Tutup Navigasi"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Navigation Links Container -->
      <div class="flex-1 overflow-y-auto px-2.5 py-2.5 space-y-2">
        <div v-for="(section, sIndex) in navSections" :key="sIndex" class="space-y-0.5">
          <!-- Collapsed Mode Thin Divider -->
          <div v-if="isCollapsed" class="h-[1px] bg-slate-100 my-1.5 mx-1"></div>

          <!-- Non-collapsible Section Header (e.g. MENU UTAMA) -->
          <div 
            v-else-if="section.collapsible === false" 
            class="px-2 pt-1 pb-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 leading-snug"
          >
            {{ section.title }}
          </div>

          <!-- Collapsible Section Dropdown Button (e.g. TIM KERJA ...) -->
          <button
            v-else
            type="button"
            @click="toggleSection(section.title)"
            class="w-full flex items-start justify-between px-2 py-1.5 rounded-md text-[10px] font-bold uppercase tracking-wider transition-colors cursor-pointer group text-left select-none"
            :class="[
              isSectionOpen(section) 
                ? 'bg-slate-50 text-slate-800' 
                : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50/70'
            ]"
            :title="`Klik untuk ${isSectionOpen(section) ? 'menutup' : 'membuka'} menu`"
          >
            <div class="flex items-start gap-1.5 min-w-0 flex-1 pr-1">
              <component 
                v-if="section.icon"
                :is="section.icon"
                class="w-3.5 h-3.5 shrink-0 transition-colors mt-0.5"
                :class="section.iconColor || 'text-slate-500'"
              />
              <span class="leading-snug font-bold break-words whitespace-normal text-slate-700 group-hover:text-slate-900">{{ section.title }}</span>
              <span 
                v-if="section.items && section.items.some(i => i.active)"
                class="w-1.5 h-1.5 rounded-full bg-[#00A99D] shrink-0 mt-1"
                title="Bagian ini berisi menu yang sedang aktif"
              ></span>
            </div>
            <div class="flex items-center gap-1 shrink-0 ml-1 mt-0.5">
              <span 
                v-if="section.items?.length"
                class="text-[9px] font-semibold px-1.5 py-0.2 rounded-full bg-slate-200/70 text-slate-600 group-hover:bg-slate-300/80 transition-colors tabular-nums"
              >
                {{ section.items.length }}
              </span>
              <ChevronDown 
                class="w-3 h-3 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 shrink-0"
                :class="isSectionOpen(section) ? 'rotate-180 text-slate-700' : ''"
              />
            </div>
          </button>

          <!-- Section Links Container with transition and indentation -->
          <transition
            enter-active-class="transition-all duration-200 ease-out overflow-hidden"
            enter-from-class="max-h-0 opacity-0 -translate-y-1"
            enter-to-class="max-h-[600px] opacity-100 translate-y-0"
            leave-active-class="transition-all duration-150 ease-in overflow-hidden"
            leave-from-class="max-h-[600px] opacity-100 translate-y-0"
            leave-to-class="max-h-0 opacity-0 -translate-y-1"
          >
            <div 
              v-show="isCollapsed || isSectionOpen(section)"
              :class="[
                !isCollapsed && section.collapsible !== false 
                  ? 'ml-2 pl-2 border-l border-slate-200/80 space-y-0.5 mt-0.5' 
                  : 'space-y-0.5'
              ]"
            >
              <template v-for="(item, iIndex) in section.items" :key="iIndex">
                <!-- Dropdown Item with Submenu Children (Expanded Mode) -->
                <div v-if="item.children && item.children.length && !isCollapsed" class="space-y-0.5">
                  <button
                    type="button"
                    @click="toggleSubMenu(item.name)"
                    class="w-full flex items-center justify-between rounded-md text-xs transition-colors group relative cursor-pointer px-2.5 py-1.5 text-left select-none"
                    :class="[
                      (item.active || item.children.some(c => c.active))
                        ? 'bg-[#E6F7F5]/90 text-[#005851] font-semibold'
                        : 'text-slate-600 hover:text-[#005851] hover:bg-slate-50 font-medium'
                    ]"
                  >
                    <!-- Left Active Indicator Bar -->
                    <span v-if="item.active || item.children.some(c => c.active)" class="absolute left-0 top-1 bottom-1 w-[3px] bg-[#00A99D] rounded-r-full"></span>

                    <div class="flex items-center gap-2 min-w-0 flex-1">
                      <component 
                        :is="item.icon" 
                        class="w-3.5 h-3.5 flex-shrink-0 transition-transform"
                        :class="(item.active || item.children.some(c => c.active)) ? item.iconColor : `${item.iconColor} opacity-80 group-hover:opacity-100`"
                      />
                      <span class="leading-snug break-words whitespace-normal text-[12px]">{{ item.name }}</span>
                    </div>
                    <div class="flex items-center gap-1 shrink-0 ml-1">
                      <span class="text-[9px] font-semibold px-1.5 py-0.2 rounded-full bg-slate-200/70 text-slate-600 tabular-nums">
                        {{ item.children.length }}
                      </span>
                      <ChevronDown 
                        class="w-3 h-3 text-slate-400 group-hover:text-slate-600 transition-transform duration-200"
                        :class="isSubMenuOpen(item) ? 'rotate-180 text-emerald-700' : ''"
                      />
                    </div>
                  </button>

                  <!-- Indented Submenu Items -->
                  <transition
                    enter-active-class="transition-all duration-200 ease-out overflow-hidden"
                    enter-from-class="max-h-0 opacity-0 -translate-y-1"
                    enter-to-class="max-h-[300px] opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-150 ease-in overflow-hidden"
                    leave-from-class="max-h-[300px] opacity-100 translate-y-0"
                    leave-to-class="max-h-0 opacity-0 -translate-y-1"
                  >
                    <div v-show="isSubMenuOpen(item)" class="ml-2.5 pl-2 border-l border-emerald-200/80 space-y-0.5 my-0.5">
                      <a
                        v-for="(child, cIndex) in item.children"
                        :key="cIndex"
                        :href="child.url"
                        @click="handleNavClick(child.path, false, $event)"
                        class="flex items-center gap-2 px-2 py-1 rounded-md text-[11px] transition-colors group relative cursor-pointer"
                        :class="[
                          child.active
                            ? 'bg-[#E6F7F5]/90 text-[#005851] font-semibold'
                            : 'text-slate-600 hover:text-[#005851] hover:bg-slate-50 font-medium'
                        ]"
                      >
                        <span v-if="child.active" class="w-1.5 h-1.5 rounded-full bg-[#00A99D] shrink-0"></span>
                        <component 
                          :is="child.icon" 
                          class="w-3.5 h-3.5 flex-shrink-0"
                          :class="child.active ? child.iconColor : `${child.iconColor} opacity-75 group-hover:opacity-100`"
                        />
                        <span class="leading-tight break-words whitespace-normal">{{ child.name }}</span>
                      </a>
                    </div>
                  </transition>
                </div>

                <!-- Single Link Item (or when collapsed) -->
                <a
                  v-else
                  :href="item.url"
                  :target="item.external ? '_blank' : '_self'"
                  :rel="item.external ? 'noopener noreferrer' : null"
                  @click="handleNavClick(item.path, item.external, $event)"
                  class="flex items-start rounded-md text-[12px] transition-colors group relative cursor-pointer"
                  :class="[
                    isCollapsed ? 'justify-center py-2 px-1.5' : 'gap-2 px-2.5 py-1.5',
                    item.active 
                      ? 'bg-[#E6F7F5]/90 text-[#005851] font-semibold'
                      : 'text-slate-600 hover:text-[#005851] hover:bg-slate-50 font-medium'
                  ]"
                  :title="isCollapsed ? item.name : ''"
                >
                  <!-- Left Active Indicator Bar -->
                  <span v-if="item.active" class="absolute left-0 top-1 bottom-1 w-[3px] bg-[#00A99D] rounded-r-full"></span>

                  <component 
                    :is="item.icon" 
                    class="w-4 h-4 flex-shrink-0 transition-transform mt-0.5"
                    :class="item.active ? item.iconColor : `${item.iconColor} opacity-80 group-hover:opacity-100`"
                  />

                  <!-- Label -->
                  <div v-if="!isCollapsed" class="flex-1 flex items-start justify-between min-w-0">
                    <span class="leading-snug break-words whitespace-normal">{{ item.name }}</span>
                    <div v-if="item.badge || item.external" class="flex items-center gap-1 shrink-0 ml-1 mt-0.5">
                      <span 
                        v-if="item.badge" 
                        class="px-1.5 py-0.2 text-[9px] font-semibold rounded bg-[#E6F7F5] text-[#005851] border border-[#99DFD6]/80 uppercase tracking-wider"
                      >
                        {{ item.badge }}
                      </span>
                      <ExternalLink 
                        v-if="item.external" 
                        class="w-3 h-3 text-slate-400 group-hover:text-slate-600" 
                      />
                    </div>
                  </div>

                  <!-- Tooltip Popup when collapsed -->
                  <div 
                    v-if="isCollapsed" 
                    class="absolute left-full ml-3 px-2.5 py-1 bg-slate-900 text-white text-[11px] font-medium rounded-md shadow-lg whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity z-50"
                  >
                    {{ item.name }}
                  </div>
                </a>
              </template>
            </div>
          </transition>
        </div>
      </div>

      <!-- Sidebar Footer: User Profile & Quick Logout -->
      <div class="p-2.5 border-t border-slate-200/80 bg-slate-50/70 shrink-0">
        <div class="flex items-center gap-2" :class="isCollapsed ? 'justify-center' : 'justify-between'">
          <a 
            :href="getUrl('/profile')"
            @click="handleNavClick('/profile', false, $event)"
            class="flex items-center gap-2 overflow-hidden group cursor-pointer"
            :title="isCollapsed ? (currentUser?.name || 'Profil Akun') : ''"
          >
            <img
              :src="currentUser?.avatar_url || 'https://ui-avatars.com/api/?name=Admin&background=0f172a&color=fff'"
              :alt="currentUser?.name || 'User'"
              class="w-7 h-7 rounded-full object-cover border border-slate-200 flex-shrink-0"
            />
            <div v-if="!isCollapsed" class="overflow-hidden text-left leading-tight">
              <span class="block text-xs font-semibold text-slate-800 group-hover:text-slate-950 truncate">
                {{ currentUser?.name || 'Administrator' }}
              </span>
              <span class="block text-[10px] text-[#005851] font-medium truncate">
                {{ currentUser?.roles?.[0] || 'Superadmin' }}
              </span>
            </div>
          </a>

          <button
            v-if="!isCollapsed"
            type="button"
            @click="handleLogout"
            class="p-1.5 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
            title="Keluar dari sesi"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>
      </div>

    </aside>
  </div>
</template>
