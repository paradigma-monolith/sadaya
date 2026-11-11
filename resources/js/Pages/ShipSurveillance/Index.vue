<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import DataKedatanganTab from './tabs/DataKedatanganTab.vue';
import LaporanKinerjaTab from './tabs/LaporanKinerjaTab.vue';
import ZonaMerahTab from './tabs/ZonaMerahTab.vue';
import SurveillanceModals from './components/SurveillanceModals.vue';
import { formatNumber } from './utils/formatters';
import {
  Ship,
  Anchor,
  Download,
  ExternalLink,
  Layers,
  FileBarChart,
  ChevronDown,
  Globe,
  UploadCloud,
  Flag,
} from 'lucide-vue-next';

const props = defineProps({
  active_tab: {
    type: String,
    default: 'data',
  },
  surveillances: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  ports: {
    type: Array,
    default: () => [],
  },
  summary: {
    type: Object,
    default: () => ({}),
  },
  pnbp_per_pelabuhan: {
    type: Array,
    default: () => [],
  },
  top_verifikators: {
    type: Array,
    default: () => [],
  },
  kpi_targets: {
    type: Object,
    default: () => ({}),
  },
  quarantine_questions: {
    type: Object,
    default: () => ({}),
  },
  red_zones_data: {
    type: Object,
    default: () => ({ countries: [], summary: { total: 0, red_count: 0, safe_count: 0 }, logs: [] }),
  },
});

const VALID_TABS = ['data', 'laporan', 'red-zones'];
const activeTab = ref(VALID_TABS.includes(props.active_tab) ? props.active_tab : 'data');

watch(
  () => props.active_tab,
  (nextTab) => {
    if (VALID_TABS.includes(nextTab)) {
      activeTab.value = nextTab;
    }
  }
);

const switchTab = (tab) => {
  if (!VALID_TABS.includes(tab)) return;
  activeTab.value = tab;
  if (typeof window !== 'undefined') {
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    window.history.replaceState(window.history.state, '', url.pathname + url.search + url.hash);
  }
};

// Keep Red Zones state reactive so tab count stays synced immediately
const redZonesState = ref(
  props.red_zones_data || { countries: [], summary: { total: 0, red_count: 0, safe_count: 0 }, logs: [] }
);

watch(
  () => props.red_zones_data,
  (nextData) => {
    if (nextData) {
      redZonesState.value = nextData;
    }
  }
);

const tabs = computed(() => [
  {
    id: 'data',
    label: 'Data Kedatangan',
    icon: Layers,
    count: formatNumber(props.summary?.total_kapal ?? props.surveillances?.total ?? 0),
    accent: 'default',
  },
  {
    id: 'laporan',
    label: 'Laporan & Analitik Kinerja',
    icon: FileBarChart,
    count: `${props.pnbp_per_pelabuhan?.length || 0} Wilker`,
    accent: 'default',
  },
  {
    id: 'red-zones',
    label: 'Zona Merah (PDF)',
    icon: UploadCloud,
    count: redZonesState.value?.summary?.red_count || 0,
    accent: 'rose',
  },
]);

const exportCsvUrl = computed(() => {
  const params = new URLSearchParams();
  if (props.filters?.pelabuhan) params.set('pelabuhan', props.filters.pelabuhan);
  if (props.filters?.kategori) params.set('kategori', props.filters.kategori);
  if (props.filters?.kresiko) params.set('kresiko', props.filters.kresiko);
  if (props.filters?.status) params.set('status', props.filters.status);
  const qs = params.toString();
  return qs ? `/ship-surveillance/export/csv?${qs}` : '/ship-surveillance/export/csv';
});

// Shared Modal States
const activeItem = ref(null);
const showDetailModal = ref(false);
const showVerifyModal = ref(false);
const verifyTarget = ref(null);

const openDetailModal = (item) => {
  activeItem.value = item;
  showDetailModal.value = true;
};

const closeDetailModal = () => {
  activeItem.value = null;
  showDetailModal.value = false;
};

const openVerifyModal = (item) => {
  verifyTarget.value = item;
  showVerifyModal.value = true;
};

const closeVerifyModal = () => {
  verifyTarget.value = null;
  showVerifyModal.value = false;
};

// Portal Pelayanan TANGKAP Dropdown State
const showPortalDropdown = ref(false);
const portalDropdownRef = ref(null);

const handleDocumentClick = (e) => {
  if (portalDropdownRef.value && !portalDropdownRef.value.contains(e.target)) {
    showPortalDropdown.value = false;
  }
};

onMounted(() => {
  if (typeof document !== 'undefined') {
    document.addEventListener('click', handleDocumentClick);
  }
});

onUnmounted(() => {
  if (typeof document !== 'undefined') {
    document.removeEventListener('click', handleDocumentClick);
  }
});
</script>

<template>
  <Head title="Pengawasan Kedatangan Kapal (TANGKAP) &bull; BKK Bandung" />

  <HybridNavbar mode="inertia" currentRoute="ship-surveillance">
    <main class="w-full px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

      <!-- ==================== HEADER BAR ==================== -->
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
        <div class="flex items-start sm:items-center gap-3.5 min-w-0">
          <img
            src="/storage/images/LOGO KARANTINA.png"
            alt="Logo Karantina Kesehatan"
            class="h-12 w-12 object-contain drop-shadow-xs shrink-0 mt-0.5 sm:mt-0"
            onerror="this.onerror=null;this.src='/images/LOGO KARANTINA.png';"
          />
          <div class="min-w-0">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 mb-1.5">
              <Anchor class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
              <span>Kekarantinaan Pelabuhan &bull; BKK Kelas I Bandung</span>
            </div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white flex flex-wrap items-center gap-2">
              <span>Pengawasan Kedatangan Kapal (TANGKAP)</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-3xl leading-relaxed">
              Digitalisasi Self-Assessment Maritime Declaration of Health, Skoring Faktor Risiko, Verifikasi Sandar &amp; Estimasi PNBP.
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
          <!-- Export CSV -->
          <a
            :href="exportCsvUrl"
            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 transition shadow-xs"
          >
            <Download class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
            <span>Export CSV</span>
          </a>

          <!-- Portal Pelayanan Dropdown (TANGKAP) -->
          <div ref="portalDropdownRef" class="relative inline-flex items-center shadow-sm">
            <a
              href="/ship-surveillance/portal"
              target="_blank"
              class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-l-xl bg-gradient-to-r from-[#005851] to-[#00A99D] text-white text-xs font-bold hover:brightness-105 transition cursor-pointer"
              title="Buka Portal Self-Assessment TANGKAP (Langsung di Sadaya)"
            >
              <ExternalLink class="w-3.5 h-3.5" />
              <span>Portal Pelayanan (TANGKAP)</span>
            </a>
            <button
              type="button"
              @click="showPortalDropdown = !showPortalDropdown"
              class="px-2.5 py-2.5 rounded-r-xl bg-[#00A99D] hover:bg-[#008f84] text-white text-xs border-l border-white/20 transition flex items-center justify-center cursor-pointer"
              title="Pilih Formulir Spesifik"
            >
              <ChevronDown class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showPortalDropdown }" />
            </button>

            <div
              v-if="showPortalDropdown"
              class="absolute right-0 top-full mt-2 w-72 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl z-50 p-2 space-y-1"
            >
              <div class="px-3 py-1.5 text-[10px] font-mono uppercase text-slate-400 tracking-wider">
                Portal Karantina Kesehatan (TANGKAP)
              </div>

              <a
                href="/ship-surveillance/portal"
                target="_blank"
                @click="showPortalDropdown = false"
                class="flex items-start gap-2.5 p-2 rounded-xl text-xs hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition group"
              >
                <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                  <Globe class="w-4 h-4" />
                </div>
                <div class="min-w-0">
                  <div class="font-bold text-slate-900 dark:text-white group-hover:text-[#00A99D] transition">
                    Portal Utama TANGKAP ↗
                  </div>
                  <div class="text-[11px] text-slate-500 line-clamp-1">
                    Pilihan formulir kedatangan kapal
                  </div>
                </div>
              </a>

              <a
                href="/ship-surveillance/assessment/domestic"
                target="_blank"
                @click="showPortalDropdown = false"
                class="flex items-start gap-2.5 p-2 rounded-xl text-xs hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition group"
              >
                <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                  <Flag class="w-4 h-4" />
                </div>
                <div class="min-w-0">
                  <div class="font-bold text-slate-900 dark:text-white group-hover:text-[#00A99D] transition">
                    Kapal Dari Dalam Negeri ↗
                  </div>
                  <div class="text-[11px] text-slate-500 line-clamp-1">
                    Formulir self-assessment rute domestik
                  </div>
                </div>
              </a>

              <a
                href="/ship-surveillance/assessment/international"
                target="_blank"
                @click="showPortalDropdown = false"
                class="flex items-start gap-2.5 p-2 rounded-xl text-xs hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition group"
              >
                <div class="w-7 h-7 rounded-lg bg-cyan-500/10 text-cyan-600 flex items-center justify-center shrink-0 mt-0.5">
                  <Ship class="w-4 h-4" />
                </div>
                <div class="min-w-0">
                  <div class="font-bold text-slate-900 dark:text-white group-hover:text-[#00A99D] transition">
                    Ship Arriving from Abroad ↗
                  </div>
                  <div class="text-[11px] text-slate-500 line-clamp-1">
                    Form English kapal luar negeri (TANGKAP)
                  </div>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- ==================== MULTI-TAB NAVIGATION ==================== -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1.5 border-b border-slate-200 dark:border-slate-800">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          type="button"
          @click="switchTab(tab.id)"
          class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
          :class="activeTab === tab.id
            ? 'bg-[#005851] text-white shadow-xs'
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
        >
          <component :is="tab.icon" class="w-3.5 h-3.5" />
          <span>{{ tab.label }}</span>
          <span
            class="px-1.5 py-0.5 rounded-full text-[10px] font-mono"
            :class="activeTab === tab.id
              ? 'bg-white/20 text-white'
              : tab.accent === 'rose'
                ? 'bg-rose-500/15 text-rose-600 dark:text-rose-400'
                : 'bg-slate-200/70 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
          >
            {{ tab.count }}
          </span>
        </button>
      </div>

      <!-- ==================== TAB CONTENTS ==================== -->
      <DataKedatanganTab
        v-show="activeTab === 'data'"
        :surveillances="surveillances"
        :filters="filters"
        :ports="ports"
        @open-detail="openDetailModal"
        @open-verify="openVerifyModal"
      />

      <LaporanKinerjaTab
        v-show="activeTab === 'laporan'"
        :summary="summary"
        :pnbp-per-pelabuhan="pnbp_per_pelabuhan"
        :top-verifikators="top_verifikators"
        :kpi-targets="kpi_targets"
        :filters="filters"
      />

      <ZonaMerahTab
        v-show="activeTab === 'red-zones'"
        v-model="redZonesState"
      />

    </main>

    <!-- Modals -->
    <SurveillanceModals
      :show-detail-modal="showDetailModal"
      :active-item="activeItem"
      :quarantine-questions="quarantine_questions"
      :show-verify-modal="showVerifyModal"
      :verify-target="verifyTarget"
      @close-detail="closeDetailModal"
      @open-verify="openVerifyModal"
      @close-verify="closeVerifyModal"
    />
  </HybridNavbar>
</template>
