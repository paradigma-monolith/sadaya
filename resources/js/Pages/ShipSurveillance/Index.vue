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
    <main class="clinical-main">

      <!-- ==================== HEADER BAR ==================== -->
      <div class="clinical-header flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-9 h-9 rounded-lg bg-[#002B27] border border-[#00A99D]/40 text-[#99CC33] flex items-center justify-center shrink-0">
            <Anchor class="w-4 h-4" />
          </div>
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <h1 class="text-sm sm:text-[15px] font-bold tracking-tight text-white">
                Pengawasan Kedatangan Kapal (TANGKAP)
              </h1>
              <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-[#002B27]/80 text-[#99DFD6] border border-[#00A99D]/30">
                Kekarantinaan Pelabuhan
              </span>
            </div>
            <p class="text-[11px] text-slate-200/85 truncate mt-0.5">
              Self-Assessment Maritime Declaration of Health, Skoring Faktor Risiko, Verifikasi Sandar &amp; Estimasi PNBP.
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-1.5 shrink-0">
          <!-- Export CSV -->
          <a
            :href="exportCsvUrl"
            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-white/15 bg-white/10 hover:bg-white/20 text-xs font-semibold text-white transition-colors"
          >
            <Download class="w-3.5 h-3.5 text-[#99CC33]" />
            <span>Export CSV</span>
          </a>

          <!-- Portal Pelayanan Dropdown (TANGKAP) -->
          <div ref="portalDropdownRef" class="relative inline-flex items-center">
            <a
              href="/ship-surveillance/portal"
              target="_blank"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-l-lg bg-[#00A99D] hover:bg-[#009388] text-white text-xs font-semibold transition-colors cursor-pointer"
              title="Buka Portal Self-Assessment TANGKAP"
            >
              <ExternalLink class="w-3.5 h-3.5" />
              <span>Portal TANGKAP</span>
            </a>
            <button
              type="button"
              @click="showPortalDropdown = !showPortalDropdown"
              class="px-2 py-1.5 rounded-r-lg bg-[#009388] hover:bg-[#007e74] text-white text-xs border-l border-white/20 transition-colors flex items-center justify-center cursor-pointer"
              title="Pilih Formulir Spesifik"
            >
              <ChevronDown class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': showPortalDropdown }" />
            </button>

            <div
              v-if="showPortalDropdown"
              class="absolute right-0 top-full mt-1.5 w-68 bg-white border border-slate-200 rounded-xl shadow-xl z-50 p-1.5 space-y-0.5 text-slate-800"
            >
              <div class="px-2.5 py-1 text-[10px] font-mono uppercase text-slate-400 tracking-wider">
                Portal Karantina Kesehatan (TANGKAP)
              </div>

              <a
                href="/ship-surveillance/portal"
                target="_blank"
                @click="showPortalDropdown = false"
                class="flex items-start gap-2 p-2 rounded-lg text-xs hover:bg-slate-50 text-slate-700 transition-colors group"
              >
                <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                  <Globe class="w-3.5 h-3.5" />
                </div>
                <div class="min-w-0">
                  <div class="font-semibold text-slate-900 group-hover:text-[#00A99D] transition-colors">
                    Portal Utama TANGKAP ↗
                  </div>
                  <div class="text-[10px] text-slate-500 line-clamp-1">
                    Pilihan formulir kedatangan kapal
                  </div>
                </div>
              </a>

              <a
                href="/ship-surveillance/assessment/domestic"
                target="_blank"
                @click="showPortalDropdown = false"
                class="flex items-start gap-2 p-2 rounded-lg text-xs hover:bg-slate-50 text-slate-700 transition-colors group"
              >
                <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                  <Flag class="w-3.5 h-3.5" />
                </div>
                <div class="min-w-0">
                  <div class="font-semibold text-slate-900 group-hover:text-[#00A99D] transition-colors">
                    Kapal Dari Dalam Negeri ↗
                  </div>
                  <div class="text-[10px] text-slate-500 line-clamp-1">
                    Formulir self-assessment rute domestik
                  </div>
                </div>
              </a>

              <a
                href="/ship-surveillance/assessment/international"
                target="_blank"
                @click="showPortalDropdown = false"
                class="flex items-start gap-2 p-2 rounded-lg text-xs hover:bg-slate-50 text-slate-700 transition-colors group"
              >
                <div class="w-6 h-6 rounded-md bg-cyan-50 text-cyan-600 flex items-center justify-center shrink-0 mt-0.5">
                  <Ship class="w-3.5 h-3.5" />
                </div>
                <div class="min-w-0">
                  <div class="font-semibold text-slate-900 group-hover:text-[#00A99D] transition-colors">
                    Ship Arriving from Abroad ↗
                  </div>
                  <div class="text-[10px] text-slate-500 line-clamp-1">
                    Form English kapal luar negeri (TANGKAP)
                  </div>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- ==================== MULTI-TAB NAVIGATION ==================== -->
      <div class="clinical-tabs">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          type="button"
          @click="switchTab(tab.id)"
          class="clinical-tab"
          :class="activeTab === tab.id
            ? 'bg-[#005851] text-white'
            : 'text-slate-600 hover:text-[#005851] hover:bg-slate-50'"
        >
          <component :is="tab.icon" class="w-3.5 h-3.5" />
          <span>{{ tab.label }}</span>
          <span
            class="px-1.5 py-0.2 rounded text-[10px] font-mono clinical-num"
            :class="activeTab === tab.id
              ? 'bg-white/20 text-white'
              : tab.accent === 'rose'
                ? 'bg-rose-50 text-rose-600'
                : 'bg-slate-100 text-slate-600'"
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
