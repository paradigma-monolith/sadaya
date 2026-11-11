<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import PortalTab from './tabs/PortalTab.vue';
import AircraftTab from './tabs/AircraftTab.vue';
import ShipTab from './tabs/ShipTab.vue';
import FlightMedicalTab from './tabs/FlightMedicalTab.vue';
import {
  FileCheck,
  Globe,
  Plane,
  Anchor,
  BriefcaseMedical,
  Pill,
  ExternalLink,
  Ship,
} from 'lucide-vue-next';

const props = defineProps({
  active_tab: {
    type: String,
    default: 'portal',
  },
  aircraft: {
    type: Object,
    required: true,
  },
  ship: {
    type: Object,
    required: true,
  },
  alat: {
    type: Object,
    required: true,
  },
  obat: {
    type: Object,
    required: true,
  },
  summary: {
    type: Object,
    default: () => ({ total_records: 0, pending_verification: 0 }),
  },
});

const VALID_TABS = ['portal', 'aircraft', 'ship', 'alat', 'obat'];
const currentTab = ref(VALID_TABS.includes(props.active_tab) ? props.active_tab : 'portal');

watch(
  () => props.active_tab,
  (nextTab) => {
    if (VALID_TABS.includes(nextTab)) {
      currentTab.value = nextTab;
    }
  }
);

const switchTab = (tab) => {
  if (!VALID_TABS.includes(tab)) return;
  currentTab.value = tab;
  router.get('/travel-consent', { tab }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

const tabs = computed(() => [
  {
    id: 'portal',
    label: 'Portal Travel Consent',
    icon: Globe,
    count: '4 Layanan',
    pending: 0,
  },
  {
    id: 'aircraft',
    label: 'Data Inspeksi Pesawat',
    icon: Plane,
    count: props.aircraft?.stats?.total ?? 0,
    pending: props.aircraft?.stats?.pending_count ?? 0,
  },
  {
    id: 'ship',
    label: 'Inspeksi Sanitasi Kapal',
    icon: Anchor,
    count: props.ship?.stats?.total ?? 0,
    pending: props.ship?.stats?.pending_count ?? 0,
  },
  {
    id: 'alat',
    label: 'Pemakaian Alkes P3K',
    icon: BriefcaseMedical,
    count: props.alat?.stats?.total ?? 0,
    pending: props.alat?.stats?.submitted_count ?? 0,
  },
  {
    id: 'obat',
    label: 'Pemakaian Obat P3K',
    icon: Pill,
    count: props.obat?.stats?.total ?? 0,
    pending: props.obat?.stats?.submitted_count ?? 0,
  },
]);
</script>

<template>
  <Head title="Travel Consent &bull; Pengawasan Alat Angkut &amp; Barang &bull; BKK Bandung" />

  <HybridNavbar mode="inertia" :currentRoute="`travel-consent-${currentTab}`">
    <main class="w-full px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

      <!-- ==================== HEADER BAR ==================== -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-semibold bg-cyan-50 dark:bg-cyan-950/40 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800 mb-2">
            <Ship class="w-3.5 h-3.5 text-cyan-600 dark:text-cyan-400" />
            <span>Tim Kerja Pengawasan Faktor Risiko Kesehatan Alat Angkut dan Barang &bull; BKK Kelas I Bandung</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
            <FileCheck class="w-7 h-7 sm:w-8 sm:h-8 text-[#00A99D] shrink-0" />
            <span>Travel Consent</span>
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-3xl leading-relaxed">
            Pusat layanan terpadu Portal Travel Consent, Inspeksi Sanitasi Pesawat, Inspeksi Sanitasi Kapal (SSCC/SSCEC), serta Pemakaian Alkes &amp; Obat P3K Penerbangan.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
          <a
            href="/travelconsent/home"
            target="_blank"
            class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#00A99D] to-[#005851] hover:brightness-105 text-xs font-bold text-white transition shadow-sm flex items-center gap-1.5 cursor-pointer"
          >
            <Globe class="w-4 h-4" />
            <span>Buka Portal Publik</span>
            <ExternalLink class="w-3.5 h-3.5" />
          </a>
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
          :class="currentTab === tab.id
            ? 'bg-[#005851] text-white shadow-xs'
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
        >
          <component :is="tab.icon" class="w-3.5 h-3.5" />
          <span>{{ tab.label }}</span>
          <span
            class="px-1.5 py-0.5 rounded-full text-[10px] font-mono"
            :class="currentTab === tab.id
              ? 'bg-white/20 text-white'
              : 'bg-slate-200/70 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
          >
            {{ tab.count }}
          </span>
        </button>
      </div>

      <!-- ==================== ACTIVE TAB CONTENT ==================== -->
      <PortalTab
        v-if="currentTab === 'portal'"
        :aircraft-stats="aircraft.stats"
        :ship-stats="ship.stats"
        :alat-stats="alat.stats"
        :obat-stats="obat.stats"
        :summary="summary"
        @switch-tab="switchTab"
      />

      <AircraftTab
        v-else-if="currentTab === 'aircraft'"
        :inspections="aircraft.inspections"
        :filters="aircraft.filters"
        :stats="aircraft.stats"
      />

      <ShipTab
        v-else-if="currentTab === 'ship'"
        :inspections="ship.inspections"
        :filters="ship.filters"
        :stats="ship.stats"
        :ports="ship.ports"
      />

      <FlightMedicalTab
        v-else-if="currentTab === 'alat'"
        type="alat"
        :inspections="alat.inspections"
        :filters="alat.filters"
        :stats="alat.stats"
        :embarkasi-list="alat.embarkasiList"
      />

      <FlightMedicalTab
        v-else-if="currentTab === 'obat'"
        type="obat"
        :inspections="obat.inspections"
        :filters="obat.filters"
        :stats="obat.stats"
        :embarkasi-list="obat.embarkasiList"
      />

    </main>
  </HybridNavbar>
</template>

