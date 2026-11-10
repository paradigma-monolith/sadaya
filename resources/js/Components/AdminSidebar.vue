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
  ChevronDown,
  Sparkles,
  Layers,
  X,
  ShieldCheck,
  FileSpreadsheet,
  Plane,
  BarChart3,
  Ship,
  Anchor,
  BriefcaseMedical,
  Pill,
  ExternalLink,
  Database,
  Leaf,
  Utensils,
  Droplets,
  Building2,
  Bug
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

// Collapsible dropdown state for sidebar sections
const openSections = ref({});

const isSectionOpen = (section) => {
  if (section.collapsible === false) return true;
  if (openSections.value[section.title] === undefined) {
    const hasActive = section.items && section.items.some(i => i.active);
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

onMounted(() => {
  try {
    const saved = localStorage.getItem('sadaya_sidebar_sections');
    if (saved) {
      const parsed = JSON.parse(saved);
      if (parsed && typeof parsed === 'object') {
        openSections.value = { ...openSections.value, ...parsed };
      }
    }
  } catch (e) {}

  // Ensure section containing active route is always expanded
  navSections.value.forEach((sec) => {
    if (sec.items && sec.items.some(i => i.active)) {
      openSections.value[sec.title] = true;
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
          name: 'Portal Travel Consent',
          path: '/travelconsent/home',
          url: getUrl('/travelconsent/home'),
          icon: Globe,
          iconColor: 'text-[#005851]',
          external: true,
          active: false,
        },
        {
          name: 'Data Inspeksi Pesawat',
          path: '/aircraft-inspections',
          url: getUrl('/aircraft-inspections'),
          icon: Plane,
          iconColor: 'text-[#00A99D]',
          active: props.currentRoute === 'aircraft-inspections' || currentPath.includes('/aircraft-inspections'),
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
          name: 'Inspeksi Sanitasi Kapal',
          path: '/ship-inspections',
          url: getUrl('/ship-inspections'),
          icon: Anchor,
          iconColor: 'text-[#005851]',
          active: props.currentRoute === 'ship-inspections' || currentPath.includes('/ship-inspections'),
        },
        {
          name: 'Pemakaian Alkes P3K',
          path: '/flight-medical-inspections/alat',
          url: getUrl('/flight-medical-inspections/alat'),
          icon: BriefcaseMedical,
          iconColor: 'text-[#7CA622]',
          active: props.currentRoute === 'flight-medical-alat' || currentPath.includes('/flight-medical-inspections/alat'),
        },
        {
          name: 'Pemakaian Obat P3K',
          path: '/flight-medical-inspections/obat',
          url: getUrl('/flight-medical-inspections/obat'),
          icon: Pill,
          iconColor: 'text-[#00A99D]',
          active: props.currentRoute === 'flight-medical-obat' || currentPath.includes('/flight-medical-inspections/obat'),
        },
        {
          name: 'Form Input SINKARKES',
          path: '/sinkarkes',
          url: getUrl('/sinkarkes'),
          icon: FileSpreadsheet,
          iconColor: 'text-[#005851]',
          active: props.currentRoute === 'sinkarkes' || (currentPath.includes('/sinkarkes') && !currentPath.includes('/sinkarkes/dashboard')),
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
          name: 'Dashboard & Ringkasan PRL',
          path: '/prl',
          url: getUrl('/prl'),
          icon: Leaf,
          iconColor: 'text-[#00A99D]',
          active: props.currentRoute === 'prl' || currentPath === '/prl' || currentPath.endsWith('/prl'),
        },
        {
          name: 'TPP (Tempat Pengelolaan Pangan)',
          path: '/prl/tpp',
          url: getUrl('/prl/tpp'),
          icon: Utensils,
          iconColor: 'text-amber-500',
          active: props.currentRoute === 'prl-tpp' || currentPath.includes('/prl/tpp'),
        },
        {
          name: 'SAB (Sarana Air Bersih)',
          path: '/prl/sab',
          url: getUrl('/prl/sab'),
          icon: Droplets,
          iconColor: 'text-sky-500',
          active: props.currentRoute === 'prl-sab' || currentPath.includes('/prl/sab'),
        },
        {
          name: 'HSBU (Hygiene Sanitasi Bangunan)',
          path: '/prl/hsbu',
          url: getUrl('/prl/hsbu'),
          icon: Building2,
          iconColor: 'text-purple-500',
          active: props.currentRoute === 'prl-hsbu' || currentPath.includes('/prl/hsbu'),
        },
        {
          name: 'SIVERA (Vektor & Reservoar)',
          path: '/prl/sivera',
          url: getUrl('/prl/sivera'),
          icon: Bug,
          iconColor: 'text-rose-500',
          active: props.currentRoute === 'prl-sivera' || currentPath.includes('/prl/sivera'),
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
      class="fixed top-0 bottom-0 left-0 z-40 bg-white border-r border-slate-200/80 flex flex-col transition-all duration-300 font-sans shadow-xl lg:shadow-none"
      :class="[
        isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        isCollapsed ? 'w-16' : 'w-64 max-w-[85vw]',
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
          title="Kementerian Kesehatan RI &bull; BKK Kelas I Bandung"
        >
          <img 
            :src="getUrl('/images/LOGO KARANTINA.png')" 
            alt="Logo Karantina Kesehatan" 
            class="h-8 w-auto max-w-[36px] object-contain flex-shrink-0 transition-transform group-hover:scale-105 drop-shadow-2xs"
            onerror="this.onerror=null;this.src='/storage/images/LOGO KARANTINA.png';"
          />

          <div v-if="!isCollapsed" class="transition-opacity duration-200 min-w-0">
            <span class="block text-sm font-bold text-slate-900 tracking-tight group-hover:text-[#00A99D] transition-colors truncate">
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
          class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors cursor-pointer shrink-0"
          title="Tutup Navigasi"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Navigation Links Container -->
      <div class="flex-1 overflow-y-auto px-3 py-3 space-y-3">
        <div v-for="(section, sIndex) in navSections" :key="sIndex" class="space-y-1">
          <!-- Collapsed Mode Thin Divider -->
          <div v-if="isCollapsed" class="h-[1px] bg-slate-100 my-2 mx-1"></div>

          <!-- Non-collapsible Section Header (e.g. MENU UTAMA) -->
          <div 
            v-else-if="section.collapsible === false" 
            class="px-2.5 pt-1 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 leading-snug"
          >
            {{ section.title }}
          </div>

          <!-- Collapsible Section Dropdown Button (e.g. TIM KERJA ...) -->
          <button
            v-else
            type="button"
            @click="toggleSection(section.title)"
            class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all cursor-pointer group text-left select-none"
            :class="[
              isSectionOpen(section) 
                ? 'bg-slate-100/70 text-slate-800' 
                : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'
            ]"
            :title="`Klik untuk ${isSectionOpen(section) ? 'menutup' : 'membuka'} menu`"
          >
            <div class="flex items-center gap-2 min-w-0 pr-1">
              <component 
                v-if="section.icon"
                :is="section.icon"
                class="w-3.5 h-3.5 shrink-0 transition-colors"
                :class="section.iconColor || 'text-slate-500'"
              />
              <span class="truncate leading-tight font-bold">{{ section.title }}</span>
              <span 
                v-if="section.items && section.items.some(i => i.active)"
                class="w-1.5 h-1.5 rounded-full bg-[#00A99D] shrink-0"
                title="Bagian ini berisi menu yang sedang aktif"
              ></span>
            </div>
            <div class="flex items-center gap-1 shrink-0 ml-1">
              <span 
                v-if="section.items?.length"
                class="text-[9px] font-semibold px-1.5 py-0.2 rounded-full bg-slate-200/70 text-slate-600 group-hover:bg-slate-300 group-hover:text-slate-800 transition-colors"
              >
                {{ section.items.length }}
              </span>
              <ChevronDown 
                class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 shrink-0"
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
                  ? 'ml-2 pl-2 border-l-2 border-slate-200/70 space-y-0.5 mt-0.5' 
                  : 'space-y-0.5'
              ]"
            >
              <a
                v-for="(item, iIndex) in section.items"
                :key="iIndex"
                :href="item.url"
                :target="item.external ? '_blank' : '_self'"
                :rel="item.external ? 'noopener noreferrer' : null"
                @click="handleNavClick(item.path, item.external, $event)"
                class="flex items-center rounded-lg text-xs transition-all group relative cursor-pointer"
                :class="[
                  isCollapsed ? 'justify-center py-2.5 px-2' : 'gap-2.5 px-2.5 py-2',
                  item.active 
                    ? 'bg-[#E6F7F5] text-[#005851] font-bold border border-[#99DFD6] shadow-2xs'
                    : 'text-slate-600 hover:text-[#005851] hover:bg-[#F0FDFB] font-medium border border-transparent'
                ]"
                :title="isCollapsed ? item.name : ''"
              >
                <!-- Left Active Indicator Bar -->
                <span v-if="item.active" class="absolute left-0 top-1.5 bottom-1.5 w-1 bg-[#00A99D] rounded-r-full"></span>

                <component 
                  :is="item.icon" 
                  class="w-4 h-4 flex-shrink-0 transition-transform group-hover:scale-105"
                  :class="item.active ? item.iconColor : `${item.iconColor} opacity-80 group-hover:opacity-100`"
                />

                <!-- Label -->
                <div v-if="!isCollapsed" class="flex-1 flex items-center justify-between overflow-hidden">
                  <span class="truncate">{{ item.name }}</span>
                  <div v-if="item.badge || item.external" class="flex items-center gap-1.5 shrink-0 ml-1.5">
                    <span 
                      v-if="item.badge" 
                      class="px-1.5 py-0.5 text-[9px] font-semibold rounded bg-[#E6F7F5] text-[#005851] border border-[#99DFD6] uppercase tracking-wider"
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
                  class="absolute left-full ml-3 px-2.5 py-1 bg-slate-900 text-white text-[11px] font-medium rounded-lg shadow-lg whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity z-50"
                >
                  {{ item.name }}
                </div>
              </a>
            </div>
          </transition>
        </div>
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
