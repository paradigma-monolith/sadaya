<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
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
    default: 'aircraft',
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

const VALID_TABS = ['aircraft', 'ship', 'alat', 'obat'];
const currentTab = ref(VALID_TABS.includes(props.active_tab) ? props.active_tab : 'aircraft');

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
    <main class="clinical-main">

      <!-- ==================== HEADER BAR ==================== -->
      <div class="clinical-header flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-9 h-9 rounded-lg bg-[#002B27] border border-[#00A99D]/40 text-[#99CC33] flex items-center justify-center shrink-0">
            <FileCheck class="w-4 h-4" />
          </div>
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <h1 class="text-sm sm:text-[15px] font-bold text-white tracking-tight">
                Travel Consent Hub
              </h1>
              <span class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-[#002B27]/80 text-[#99DFD6] border border-[#00A99D]/30">
                <Ship class="w-3 h-3 text-[#99CC33]" />
                <span>Alat Angkut &amp; Barang</span>
              </span>
            </div>
            <p class="text-[11px] text-slate-200/85 truncate mt-0.5">
              Data Inspeksi Pesawat, Sanitasi Kapal (SSCC/SSCEC), serta Pemakaian Alkes &amp; Obat P3K Penerbangan.
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
          <a
            href="/travelconsent/home"
            target="_blank"
            class="px-3 py-1.5 rounded-lg bg-[#00A99D] hover:bg-[#009388] text-xs font-semibold text-white transition-colors flex items-center gap-1.5 cursor-pointer"
          >
            <Globe class="w-3.5 h-3.5" />
            <span>Portal Publik</span>
            <ExternalLink class="w-3 h-3" />
          </a>
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
          :class="currentTab === tab.id
            ? 'bg-[#005851] text-white'
            : 'text-slate-600 hover:text-[#005851] hover:bg-slate-50'"
        >
          <component :is="tab.icon" class="w-3.5 h-3.5" />
          <span>{{ tab.label }}</span>
          <span
            class="px-1.5 py-0.2 rounded text-[10px] font-mono clinical-num"
            :class="currentTab === tab.id
              ? 'bg-white/20 text-white'
              : 'bg-slate-100 text-slate-600'"
          >
            {{ tab.count }}
          </span>
        </button>
      </div>

      <!-- ==================== ACTIVE TAB CONTENT ==================== -->
      <AircraftTab
        v-if="currentTab === 'aircraft'"
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

