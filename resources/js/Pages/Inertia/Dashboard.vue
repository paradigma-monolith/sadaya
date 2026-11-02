<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useSidebar } from '@/composables/useSidebar';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import BaseChart from '../../Components/BaseChart.vue';
import BaseMap from '../../Components/BaseMap.vue';
import {
  Plane,
  Ship,
  Stethoscope,
  Pill,
  ClipboardCheck,
  TriangleAlert,
  Clock,
  CircleCheck,
  ShieldCheck,
  TrendingUp,
  RefreshCw,
  MapPin,
  Search,
  ArrowUpRight,
  Activity,
  LogIn,
  Globe,
  Box,
  CalendarDays,
  Building2,
  ChevronRight,
  ChartColumn,
  ChartLine,
  Layers,
  Anchor,
  Users,
  Check,
  ChevronDown,
  ExternalLink,
  Filter,
  Sparkles,
  Radio,
  Eye,
  RotateCw,
  Navigation,
} from 'lucide-vue-next';

const props = defineProps({
  generated_at: {
    type: String,
    default: '',
  },
  period: {
    type: Object,
    default: () => ({ days: 30, start: '', end: '' }),
  },
  kpi: {
    type: Object,
    default: () => ({
      total: 0,
      period_count: 0,
      prev_count: 0,
      growth: 0,
      today: 0,
      pending: 0,
      approved: 0,
      verified: 0,
      rejected: 0,
      compliance_rate: 0,
      ships_with_risk: 0,
      ship_total: 0,
      completion_rate: 0,
    }),
  },
  modules: {
    type: Array,
    default: () => [],
  },
  status_breakdown: {
    type: Array,
    default: () => [],
  },
  aircraft_stats: {
    type: Object,
    default: () => ({ total: 0, layak: 0, tidak_layak: 0, tindakan: [] }),
  },
  ship_stats: {
    type: Object,
    default: () => ({ total: 0, sscec: 0, sscc: 0, ships_with_risk: 0, top_risk_areas: [], jenis_kapal: [] }),
  },
  medical_stats: {
    type: Object,
    default: () => ({ total: 0, alat: 0, obat: 0, embarkasi: [] }),
  },
  trend: {
    type: Object,
    default: () => ({ dates: [], iso: [], aircraft: [], ship: [], alat: [], obat: [], total: [] }),
  },
  offices: {
    type: Array,
    default: () => [],
  },
  unassigned: {
    type: Number,
    default: 0,
  },
  recent_inspections: {
    type: Array,
    default: () => [],
  },
  recent_activities: {
    type: Array,
    default: () => [],
  },
  recent_logins: {
    type: Array,
    default: () => [],
  },
  users: {
    type: Object,
    default: () => ({ total: 0, roles: 0, activities: 0 }),
  },
  bkk_offices: {
    type: Array,
    default: () => [],
  },
  metrics: {
    type: Object,
    default: () => ({}),
  },
});

const getUrl = (path) => {
  if (typeof window !== 'undefined' && window.location.pathname.includes('/hybridlaravel/public')) {
    return `/hybridlaravel/public${path}`;
  }
  return path;
};

const isRefreshing = ref(false);
const refreshData = () => {
  isRefreshing.value = true;
  router.reload({
    onFinish: () => {
      isRefreshing.value = false;
    },
  });
};

const { isCollapsed } = useSidebar();
watch(isCollapsed, () => {
  [50, 120, 200, 280, 350].forEach((delay) => {
    setTimeout(() => {
      window.dispatchEvent(new Event('resize'));
    }, delay);
  });
});

// ---------------------------------------------------------------------------
// Offices & Interactive Map
// ---------------------------------------------------------------------------
const officesList = computed(() => {
  if (props.offices && props.offices.length > 0) return props.offices;
  if (props.bkk_offices && props.bkk_offices.length > 0) return props.bkk_offices;
  return [];
});

const selectedOffice = ref(null);
const searchOffice = ref('');
const activeOfficeFilter = ref('all'); // 'all' | 'udara' | 'laut'

onMounted(() => {
  if (officesList.value.length > 0) {
    selectedOffice.value = officesList.value[0];
  }
});

const mapComponentRef = ref(null);
const mapCenter = ref([107.6108, -6.8989]);
const mapZoom = ref(8.2);
const mapPitch = ref(30);
const mapBearing = ref(0);
const isOrbiting = ref(false);
const isNodeCardMinimized = ref(false);

const bkkMapMarkers = computed(() => {
  const shortCodes = ['BDG', 'BDO', 'CBN', 'IDM', 'KJT', 'PTB', 'PLR'];
  return officesList.value.map((o, idx) => {
    const lat = Number(o.lat);
    const lng = Number(o.lng);
    const totalOps = o.total !== undefined ? o.total : (o.daily_ops || 0);

    return {
      ...o,
      id: `bkk-node-${o.no}`,
      flag: o.code ? o.code.split('-')[1] || shortCodes[idx] : shortCodes[idx],
      shortCode: o.code ? o.code.split('-')[1] || shortCodes[idx] : shortCodes[idx],
      title: o.name,
      shortTitle: o.short_name || o.name,
      lng,
      lat,
      coordinates: [lng, lat],
      location: o.address,
      status: 'AKTIF',
      zoom: o.is_hq ? 12.5 : 12.0,
      pitch: 45,
      bearing: 0,
      popupRows: [
        { label: 'Tipe / Moda', value: `${o.type} (${o.moda})` },
        { label: 'Total Inspeksi', value: `${totalOps} Dokumen`, color: '#059669' },
        { label: 'Terakhir Aktif', value: o.last_human || 'Belum ada', color: '#0284c7' },
      ],
    };
  });
});

const activeMapNode = computed(() => {
  const currentNo = selectedOffice.value?.no || 1;
  return bkkMapMarkers.value.find((m) => m.no === currentNo) || bkkMapMarkers.value[0];
});

const selectOffice = (office) => {
  selectedOffice.value = office;
  isNodeCardMinimized.value = false;
  const target = bkkMapMarkers.value.find((m) => m.no === office.no);
  if (target && mapComponentRef.value) {
    mapComponentRef.value.flyTo(
      [target.lng, target.lat],
      target.zoom || 12.0,
      target.pitch || 40,
      target.bearing || 0
    );
  }
};

const onCameraMove = (cam) => {
  mapCenter.value = [Number(cam.lng), Number(cam.lat)];
  mapZoom.value = Number(cam.zoom);
  mapPitch.value = Number(cam.pitch);
  mapBearing.value = Number(cam.bearing);
};

const resetGlobalView = () => {
  mapComponentRef.value?.resetView();
};

const toggle3DPitch = () => {
  mapComponentRef.value?.toggle3DPitch();
};

const toggleOrbit = () => {
  if (mapComponentRef.value) {
    isOrbiting.value = mapComponentRef.value.toggleOrbit();
  }
};

onUnmounted(() => {
  if (isOrbiting.value && mapComponentRef.value) {
    mapComponentRef.value.toggleOrbit();
  }
});

const filteredOffices = computed(() => {
  let list = officesList.value;
  if (activeOfficeFilter.value === 'udara') {
    list = list.filter((o) => o.is_hq || o.category === 'udara' || o.moda.toLowerCase().includes('udara') || o.moda.toLowerCase().includes('bandara'));
  } else if (activeOfficeFilter.value === 'laut') {
    list = list.filter((o) => o.category === 'laut' || o.moda.toLowerCase().includes('laut') || o.moda.toLowerCase().includes('pelabuhan'));
  }

  const q = searchOffice.value.trim().toLowerCase();
  if (!q) return list;
  return list.filter((o) =>
    (o.name && o.name.toLowerCase().includes(q)) ||
    (o.short_name && o.short_name.toLowerCase().includes(q)) ||
    (o.address && o.address.toLowerCase().includes(q)) ||
    (o.zone && o.zone.toLowerCase().includes(q)) ||
    (o.code && o.code.toLowerCase().includes(q))
  );
});

// ---------------------------------------------------------------------------
// Service Modules Quick Filter & Details
// ---------------------------------------------------------------------------
const selectedModuleKey = ref('all'); // 'all' | 'aircraft' | 'ship' | 'alat' | 'obat'

const moduleIconMap = {
  aircraft: Plane,
  ship: Ship,
  alat: Stethoscope,
  obat: Pill,
};

// ---------------------------------------------------------------------------
// Multi-Series ECharts Trend Chart
// ---------------------------------------------------------------------------
const trendViewMode = ref('line'); // 'line' | 'bar'
const seriesVisibility = ref({
  total: true,
  aircraft: true,
  ship: true,
  alat: false,
  obat: false,
});

const toggleSeries = (key) => {
  seriesVisibility.value[key] = !seriesVisibility.value[key];
};

const trendChartOption = computed(() => {
  const dates = props.trend?.dates?.length ? props.trend.dates : ['1', '2', '3'];
  const seriesConfig = [
    {
      key: 'total',
      name: 'Total Layanan',
      color: '#0f172a',
      data: props.trend?.total || [],
      lineWidth: 2.5,
      area: true,
      areaColor: ['rgba(15, 23, 42, 0.08)', 'rgba(15, 23, 42, 0.0)'],
    },
    {
      key: 'aircraft',
      name: 'Sanitasi Pesawat',
      color: '#0284c7',
      data: props.trend?.aircraft || [],
      lineWidth: 2.0,
      area: false,
    },
    {
      key: 'ship',
      name: 'Sanitasi Kapal',
      color: '#059669',
      data: props.trend?.ship || [],
      lineWidth: 2.0,
      area: false,
    },
    {
      key: 'alat',
      name: 'Alkes Haji',
      color: '#d97706',
      data: props.trend?.alat || [],
      lineWidth: 1.8,
      area: false,
    },
    {
      key: 'obat',
      name: 'Obat P3K',
      color: '#e11d48',
      data: props.trend?.obat || [],
      lineWidth: 1.8,
      area: false,
    },
  ];

  const series = [];
  seriesConfig.forEach((cfg) => {
    if (!seriesVisibility.value[cfg.key]) return;

    series.push({
      name: cfg.name,
      type: trendViewMode.value,
      smooth: 0.25,
      symbol: 'circle',
      symbolSize: 6,
      showSymbol: false,
      data: cfg.data,
      itemStyle: { color: cfg.color, borderRadius: [4, 4, 0, 0] },
      lineStyle: { width: cfg.lineWidth, color: cfg.color },
      areaStyle:
        trendViewMode.value === 'line' && cfg.area
          ? {
              color: {
                type: 'linear',
                x: 0,
                y: 0,
                x2: 0,
                y2: 1,
                colorStops: [
                  { offset: 0, color: cfg.areaColor[0] },
                  { offset: 1, color: cfg.areaColor[1] },
                ],
              },
            }
          : undefined,
    });
  });

  return {
    backgroundColor: 'transparent',
    tooltip: {
      trigger: 'axis',
      backgroundColor: '#ffffff',
      borderColor: '#e2e8f0',
      borderWidth: 1,
      padding: [10, 14],
      textStyle: { color: '#0f172a', fontSize: 11 },
      extraCssText: 'box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.12); border-radius: 12px;',
      axisPointer: {
        type: 'line',
        lineStyle: { color: '#0f172a', width: 1, type: 'dashed' },
      },
    },
    grid: {
      top: 30,
      right: 20,
      bottom: 25,
      left: 35,
    },
    xAxis: {
      type: 'category',
      data: dates,
      boundaryGap: trendViewMode.value === 'bar',
      axisLine: { lineStyle: { color: '#e2e8f0' } },
      axisTick: { show: false },
      axisLabel: { color: '#94a3b8', fontSize: 10 },
    },
    yAxis: {
      type: 'value',
      minInterval: 1,
      splitLine: { lineStyle: { color: '#f1f5f9', type: 'solid' } },
      axisLabel: { color: '#94a3b8', fontSize: 10 },
    },
    series,
  };
});

// ---------------------------------------------------------------------------
// Recent Feed Filter & Tabs
// ---------------------------------------------------------------------------
const activeFeedTab = ref('inspections'); // 'inspections' | 'activities' | 'logins'
const searchFeed = ref('');
const statusFilterFeed = ref('all'); // 'all' | 'submitted' | 'approved' | 'verified'

const filteredInspections = computed(() => {
  let list = props.recent_inspections || [];
  if (selectedModuleKey.value !== 'all') {
    list = list.filter((r) => r.module === selectedModuleKey.value);
  }
  if (statusFilterFeed.value !== 'all') {
    list = list.filter((r) => r.status === statusFilterFeed.value);
  }
  const q = searchFeed.value.trim().toLowerCase();
  if (!q) return list;
  return list.filter((r) =>
    r.registration_no.toLowerCase().includes(q) ||
    r.subject.toLowerCase().includes(q) ||
    r.officer.toLowerCase().includes(q) ||
    r.office.toLowerCase().includes(q)
  );
});
</script>

<template>
  <Head title="Pusat Komando & Pemantauan Karantina — BKK Kelas I Bandung" />

  <HybridNavbar mode="inertia" currentRoute="dashboard">
    <main class="w-full px-4 sm:px-6 lg:px-8 pt-4 pb-16 space-y-6 transition-all duration-300">
      
      <!-- ========================================================================= -->
      <!-- TOP COMMAND HEADER BAR                                                    -->
      <!-- ========================================================================= -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-5 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 text-white shadow-lg border border-slate-700/60">
        <div class="space-y-1">
          <div class="flex flex-wrap items-center gap-2.5">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wide uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              Sistem Operasional Real-Time
            </span>
            <span class="text-xs text-slate-300">
              {{ generated_at || 'Memuat waktu...' }}
            </span>
          </div>

          <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2.5">
            <Building2 class="w-6 h-6 text-emerald-400 shrink-0" />
            <span>Pusat Komando Balai Kekarantinaan Kesehatan Kelas I Bandung</span>
          </h1>

          <p class="text-xs sm:text-sm text-slate-300 max-w-3xl">
            Pemantauan terpadu 4 moda layanan karantina (Pesawat, Kapal Laut, Alkes, dan Obat P3K) di 1 Kantor Induk &amp; 6 Wilayah Kerja Jawa Barat.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
          <button
            type="button"
            @click="refreshData"
            :disabled="isRefreshing"
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/15 transition-all cursor-pointer disabled:opacity-50"
            title="Muat ulang telemetri data"
          >
            <RefreshCw class="w-3.5 h-3.5" :class="isRefreshing ? 'animate-spin text-emerald-400' : 'text-slate-300'" />
            <span>{{ isRefreshing ? 'Memperbarui...' : 'Segarkan Data' }}</span>
          </button>

          <Link
            :href="getUrl('/public_aircraft')"
            target="_blank"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-950/40 transition-all cursor-pointer"
          >
            <ExternalLink class="w-3.5 h-3.5" />
            <span>Form Publik</span>
          </Link>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- ROW 1: HEADLINE EXECUTIVE KPI METRICS                                     -->
      <!-- ========================================================================= -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- KPI 1: Total Pengawasan Karantina -->
        <div class="rounded-2xl p-5 bg-white border border-slate-200/90 shadow-xs hover:border-slate-300 transition-all">
          <div class="flex items-center justify-between text-slate-500 mb-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Total Pengawasan</span>
            <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-900">
              <ClipboardCheck class="w-4 h-4" />
            </div>
          </div>
          <div class="flex items-baseline gap-2">
            <span class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
              {{ kpi.total || 0 }}
            </span>
            <span class="text-xs font-semibold text-emerald-600 inline-flex items-center">
              <TrendingUp class="w-3 h-3 mr-0.5" />
              {{ kpi.period_count || 0 }} bln ini
            </span>
          </div>
          <p class="text-xs text-slate-600 mt-2 flex items-center justify-between pt-2 border-t border-slate-100">
            <span>Hari ini: <strong class="text-slate-900">{{ kpi.today || 0 }} dokumen</strong></span>
            <span class="text-slate-600">4 Moda Terintegrasi</span>
          </p>
        </div>

        <!-- KPI 2: Kelaikan Sanitasi Pesawat -->
        <div class="rounded-2xl p-5 bg-white border border-slate-200/90 shadow-xs hover:border-slate-300 transition-all">
          <div class="flex items-center justify-between text-slate-500 mb-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Kelaikan Sanitasi Udara</span>
            <div class="w-8 h-8 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600">
              <Plane class="w-4 h-4" />
            </div>
          </div>
          <div class="flex items-baseline gap-2">
            <span class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
              {{ kpi.compliance_rate || 0 }}%
            </span>
            <span class="text-xs font-medium text-slate-600">
              ({{ aircraft_stats.layak || 0 }} Layak / {{ aircraft_stats.total || 0 }})
            </span>
          </div>
          <p class="text-xs text-slate-600 mt-2 flex items-center justify-between pt-2 border-t border-slate-100">
            <span>Perlu Tindakan: <strong class="text-rose-600">{{ aircraft_stats.tidak_layak || 0 }}</strong></span>
            <Link :href="getUrl('/aircraft-inspections')" class="text-sky-600 hover:text-sky-800 font-semibold inline-flex items-center gap-0.5">
              Detail <ChevronRight class="w-3 h-3" />
            </Link>
          </p>
        </div>

        <!-- KPI 3: Karantina Sanitasi Kapal (IHR 2005) -->
        <div class="rounded-2xl p-5 bg-white border border-slate-200/90 shadow-xs hover:border-slate-300 transition-all">
          <div class="flex items-center justify-between text-slate-500 mb-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Sanitasi Kapal Laut</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
              <Ship class="w-4 h-4" />
            </div>
          </div>
          <div class="flex items-baseline gap-2">
            <span class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
              {{ ship_stats.total || 0 }}
            </span>
            <span class="text-xs font-medium text-slate-600">Kapal</span>
            <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md ml-auto">
              {{ ship_stats.sscec || 0 }} SSCEC / {{ ship_stats.sscc || 0 }} SSCC
            </span>
          </div>
          <p class="text-xs text-slate-600 mt-2 flex items-center justify-between pt-2 border-t border-slate-100">
            <span>Faktor Risiko: <strong class="text-amber-600">{{ ship_stats.ships_with_risk || 0 }} kapal</strong></span>
            <Link :href="getUrl('/ship-inspections')" class="text-emerald-700 hover:text-emerald-900 font-semibold inline-flex items-center gap-0.5">
              Detail <ChevronRight class="w-3 h-3" />
            </Link>
          </p>
        </div>

        <!-- KPI 4: Status Verifikasi & Penyelesaian -->
        <div class="rounded-2xl p-5 bg-white border border-slate-200/90 shadow-xs hover:border-slate-300 transition-all">
          <div class="flex items-center justify-between text-slate-500 mb-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Tingkat Verifikasi</span>
            <div class="w-8 h-8 rounded-xl bg-violet-50 flex items-center justify-center text-violet-600">
              <ShieldCheck class="w-4 h-4" />
            </div>
          </div>
          <div class="flex items-baseline gap-2">
            <span class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
              {{ kpi.completion_rate || 0 }}%
            </span>
            <span class="text-xs font-medium text-slate-600">Selesai</span>
          </div>
          <div class="flex items-center gap-3 text-xs mt-2 pt-2 border-t border-slate-100">
            <span class="text-emerald-600 font-semibold">✓ {{ kpi.approved || 0 }} Disetujui</span>
            <span class="text-amber-600 font-semibold">⏳ {{ kpi.pending || 0 }} Menunggu</span>
            <span class="text-sky-600 font-semibold">🔍 {{ kpi.verified || 0 }} Cek</span>
          </div>
        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- ROW 2: 4 SERVICE MODULE CARDS (INTERACTIVE FILTER)                        -->
      <!-- ========================================================================= -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="mod in modules"
          :key="mod.key"
          @click="selectedModuleKey = selectedModuleKey === mod.key ? 'all' : mod.key"
          class="rounded-2xl p-4.5 transition-all cursor-pointer border relative overflow-hidden group"
          :class="selectedModuleKey === mod.key
            ? 'bg-slate-900 text-white border-slate-900 shadow-md ring-2 ring-emerald-500'
            : 'bg-white hover:bg-slate-50/90 text-slate-800 border-slate-200/90 shadow-2xs'"
        >
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-2.5">
              <div
                class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                :class="selectedModuleKey === mod.key ? 'bg-emerald-500 text-slate-950 font-black' : 'bg-slate-100 text-slate-700 group-hover:bg-emerald-50 group-hover:text-emerald-700'"
              >
                <component :is="moduleIconMap[mod.key] || Layers" class="w-4.5 h-4.5" />
              </div>
              <div>
                <h3 class="text-sm font-bold tracking-tight leading-snug" :class="selectedModuleKey === mod.key ? 'text-white' : 'text-slate-900'">
                  {{ mod.label }}
                </h3>
                <span
                  class="text-[10px] font-semibold tracking-wider uppercase"
                  :class="selectedModuleKey === mod.key ? 'text-emerald-300' : 'text-slate-600'"
                >
                  Moda {{ mod.moda }}
                </span>
              </div>
            </div>

            <Link
              :href="getUrl(mod.url)"
              @click.stop
              class="p-1 rounded-lg transition-colors"
              :class="selectedModuleKey === mod.key ? 'text-slate-400 hover:text-white hover:bg-white/10' : 'text-slate-400 hover:text-slate-700 hover:bg-slate-100'"
              title="Buka modul lengkap"
            >
              <ArrowUpRight class="w-4 h-4" />
            </Link>
          </div>

          <div class="flex items-baseline justify-between mt-1">
            <div>
              <span class="text-2xl font-black" :class="selectedModuleKey === mod.key ? 'text-white' : 'text-slate-900'">
                {{ mod.total }}
              </span>
              <span class="text-xs ml-1" :class="selectedModuleKey === mod.key ? 'text-slate-300' : 'text-slate-600'">Dokumen</span>
            </div>
            <div class="flex items-center gap-2 text-[11px] font-semibold">
              <span class="text-emerald-600">✓ {{ mod.approved }}</span>
              <span class="text-amber-500">⏳ {{ mod.pending }}</span>
            </div>
          </div>

          <!-- Active state pill -->
          <div class="mt-3 pt-2 text-[11px] font-medium border-t flex items-center justify-between"
            :class="selectedModuleKey === mod.key ? 'border-white/15 text-emerald-300' : 'border-slate-100 text-slate-600 group-hover:text-slate-900'"
          >
            <span>{{ selectedModuleKey === mod.key ? '✓ Filter aktif' : 'Klik untuk filter feed' }}</span>
            <span class="text-[10px]">Buka Modul →</span>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- ROW 3: MAP VIEW + WILKER OPERATIONAL DIRECTORY                            -->
      <!-- ========================================================================= -->
      <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-200/80">
          
          <!-- Map Section (Left, 7 Cols) -->
          <div class="lg:col-span-7 p-6 flex flex-col justify-between">
            <div>
              <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div>
                  <div class="flex items-center gap-2">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                      Peta Geospasial Wilayah Kerja
                    </h2>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                      7 Titik Operasional
                    </span>
                  </div>
                  <p class="text-xs text-slate-600 mt-0.5">
                    1 Kantor Induk &amp; 6 Wilayah Kerja BKK Kelas I Bandung (Peta OpenStreetMap presisi).
                  </p>
                </div>

                <!-- Camera Action Controls -->
                <div class="flex flex-wrap items-center gap-1.5">
                  <button
                    type="button"
                    @click="resetGlobalView"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 transition-all cursor-pointer"
                  >
                    <Globe class="w-3.5 h-3.5 text-emerald-600" />
                    Reset
                  </button>
                  <button
                    type="button"
                    @click="toggle3DPitch"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition-all cursor-pointer"
                    :class="mapPitch > 25
                      ? 'bg-slate-900 text-white border-slate-900 shadow-2xs'
                      : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200'"
                  >
                    <Box class="w-3.5 h-3.5" :class="mapPitch > 25 ? 'text-emerald-400' : 'text-slate-500'" />
                    3D
                  </button>
                  <button
                    type="button"
                    @click="toggleOrbit"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition-all cursor-pointer"
                    :class="isOrbiting
                      ? 'bg-emerald-600 text-white border-emerald-600 shadow-2xs'
                      : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200'"
                  >
                    <RotateCw class="w-3.5 h-3.5" :class="isOrbiting ? 'animate-spin' : 'text-slate-500'" />
                    Orbit
                  </button>
                </div>
              </div>

              <!-- Quick Fly-to-Node Chips -->
              <div class="flex flex-wrap items-center gap-1.5 mb-3">
                <span class="text-[11px] font-semibold text-slate-600 mr-1">Fly to:</span>
                <button
                  v-for="node in bkkMapMarkers"
                  :key="node.id"
                  type="button"
                  @click="selectOffice(node)"
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs transition-all cursor-pointer border"
                  :class="activeMapNode?.no === node.no
                    ? 'bg-slate-900 text-white border-slate-900 font-bold shadow-2xs'
                    : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 hover:text-slate-900'"
                >
                  <span class="text-[10px] font-mono font-black" :class="activeMapNode?.no === node.no ? 'text-emerald-400' : 'text-slate-600'">
                    {{ node.shortCode }}
                  </span>
                  <span>{{ node.shortTitle.replace('Wilker ', '').replace('Induk ', '') }}</span>
                  <span
                    v-if="node.total > 0"
                    class="text-[10px] font-bold px-1.5 py-0.2 rounded-full"
                    :class="activeMapNode?.no === node.no ? 'bg-emerald-500 text-slate-950' : 'bg-emerald-50 text-emerald-700'"
                  >
                    {{ node.total }}
                  </span>
                </button>
              </div>
            </div>

            <!-- Map Container -->
            <div class="relative rounded-xl overflow-hidden border border-slate-200/90 shadow-xs">
              <BaseMap
                ref="mapComponentRef"
                :initialCenter="[107.55, -6.65]"
                :initialZoom="7.9"
                :initialPitch="25"
                :initialBearing="0"
                styleType="osm"
                :markers="bkkMapMarkers"
                height="450px"
                @selectMarker="selectOffice"
                @mapMove="onCameraMove"
              />

              <!-- HUD Top Left -->
              <div class="absolute top-2.5 left-2.5 z-10 w-44 rounded-xl bg-white/95 backdrop-blur-md border border-slate-200 px-3 py-2 shadow-md pointer-events-none">
                <div class="flex items-center gap-1.5 text-[10px] font-bold text-emerald-700 mb-1">
                  <Navigation class="w-3 h-3 text-emerald-600" />
                  <span>GPS Posisi Kamera</span>
                </div>
                <div class="space-y-0.5 text-[10px] font-mono text-slate-600">
                  <div class="flex items-center justify-between">
                    <span>Koordinat:</span>
                    <span class="text-slate-900 font-bold">{{ mapCenter[0].toFixed(2) }}, {{ mapCenter[1].toFixed(2) }}</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span>Zoom / Pitch:</span>
                    <span class="text-slate-900 font-bold">{{ mapZoom.toFixed(1) }}x / {{ mapPitch }}°</span>
                  </div>
                </div>
              </div>

              <!-- Selected Office Floating Card Bottom Right -->
              <div
                v-if="activeMapNode"
                class="absolute bottom-2.5 right-2.5 z-10 rounded-xl bg-white/95 backdrop-blur-md border border-slate-200 shadow-xl transition-all duration-200"
                :class="isNodeCardMinimized ? 'w-52 px-3 py-2' : 'w-64 sm:w-72 px-3.5 py-3'"
              >
                <div class="flex items-center justify-between gap-1.5">
                  <div class="flex items-center gap-2 min-w-0">
                    <div class="w-6 h-6 rounded bg-slate-900 flex items-center justify-center text-[10px] font-mono font-black text-white shrink-0">
                      {{ activeMapNode.shortCode }}
                    </div>
                    <div class="min-w-0">
                      <h4 class="text-xs font-bold text-slate-900 truncate">
                        {{ activeMapNode.title }}
                      </h4>
                      <p v-if="!isNodeCardMinimized" class="text-[10px] text-slate-600 truncate">
                        {{ activeMapNode.type }} &bull; {{ activeMapNode.moda }}
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center gap-1 shrink-0">
                    <button
                      type="button"
                      @click="isNodeCardMinimized = !isNodeCardMinimized"
                      class="w-5 h-5 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-xs leading-none cursor-pointer"
                      :title="isNodeCardMinimized ? 'Perbesar' : 'Perkecil'"
                    >
                      {{ isNodeCardMinimized ? '+' : '−' }}
                    </button>
                  </div>
                </div>

                <template v-if="!isNodeCardMinimized">
                  <div class="grid grid-cols-2 gap-2 pt-2 mt-2 border-t border-slate-100 text-[10px]">
                    <div class="flex flex-col">
                      <span class="text-slate-600">Total Pengawasan:</span>
                      <span class="font-bold text-emerald-700 text-xs">{{ activeMapNode.total || 0 }} Dokumen</span>
                    </div>
                    <div class="flex flex-col border-l border-slate-100 pl-2">
                      <span class="text-slate-600">Aktivitas Terakhir:</span>
                      <span class="font-semibold text-slate-900 truncate">{{ activeMapNode.last_human || 'Belum ada' }}</span>
                    </div>
                  </div>
                  <div class="mt-2 pt-2 border-t border-slate-100 text-[10px] text-slate-600 line-clamp-2 leading-snug">
                    📍 {{ activeMapNode.address }}
                  </div>
                </template>
              </div>
            </div>
          </div>

          <!-- Directory Table Section (Right, 5 Cols) -->
          <div class="lg:col-span-5 p-6 flex flex-col justify-between">
            <div>
              <!-- Filter & Search Bar -->
              <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-1">
                  <button
                    type="button"
                    @click="activeOfficeFilter = 'all'"
                    class="px-2.5 py-1 rounded-lg text-xs transition-all cursor-pointer"
                    :class="activeOfficeFilter === 'all'
                      ? 'bg-slate-900 text-white font-bold'
                      : 'text-slate-600 hover:text-slate-900 font-medium'"
                  >
                    Semua ({{ officesList.length }})
                  </button>
                  <button
                    type="button"
                    @click="activeOfficeFilter = 'udara'"
                    class="px-2.5 py-1 rounded-lg text-xs transition-all cursor-pointer"
                    :class="activeOfficeFilter === 'udara'
                      ? 'bg-slate-900 text-white font-bold'
                      : 'text-slate-600 hover:text-slate-900 font-medium'"
                  >
                    Udara
                  </button>
                  <button
                    type="button"
                    @click="activeOfficeFilter = 'laut'"
                    class="px-2.5 py-1 rounded-lg text-xs transition-all cursor-pointer"
                    :class="activeOfficeFilter === 'laut'
                      ? 'bg-slate-900 text-white font-bold'
                      : 'text-slate-600 hover:text-slate-900 font-medium'"
                  >
                    Laut
                  </button>
                </div>

                <div class="relative w-full sm:w-44">
                  <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
                  <input
                    v-model="searchOffice"
                    type="text"
                    placeholder="Cari wilker..."
                    class="w-full pl-8 pr-2 py-1 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-slate-300"
                  />
                </div>
              </div>

              <!-- Directory Table -->
              <div class="overflow-x-auto mt-2">
                <table class="w-full text-left border-collapse text-xs">
                  <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-900">
                      <th class="py-2.5 px-2">Wilker / Pos</th>
                      <th class="py-2.5 px-2">Moda</th>
                      <th class="py-2.5 px-2 text-right">Layanan</th>
                      <th class="py-2.5 pl-2 pr-1 text-right">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100">
                    <tr
                      v-for="office in filteredOffices"
                      :key="office.no"
                      @click="selectOffice(office)"
                      class="group transition-colors cursor-pointer"
                      :class="selectedOffice?.no === office.no ? 'bg-emerald-50/70 font-semibold' : 'hover:bg-slate-50/80'"
                    >
                      <td class="py-2.5 px-2">
                        <div class="flex items-center gap-2">
                          <span class="w-5 h-5 rounded bg-slate-900 text-white text-[9px] font-mono font-bold flex items-center justify-center shrink-0">
                            {{ office.no }}
                          </span>
                          <div class="min-w-0">
                            <div class="font-bold text-slate-900 truncate" :title="office.name">
                              {{ office.short_name || office.name }}
                            </div>
                            <div class="text-[10px] text-slate-600 truncate">
                              {{ office.zone }}
                            </div>
                          </div>
                        </div>
                      </td>

                      <td class="py-2.5 px-2 whitespace-nowrap text-slate-600 text-[11px]">
                        {{ office.moda }}
                      </td>

                      <td class="py-2.5 px-2 text-right whitespace-nowrap">
                        <span class="font-bold text-slate-900">{{ office.total || 0 }}</span>
                        <span class="text-[10px] text-slate-600 ml-0.5">dok</span>
                      </td>

                      <td class="py-2.5 pl-2 pr-1 text-right">
                        <button
                          type="button"
                          class="p-1 rounded text-slate-400 group-hover:text-emerald-700 hover:bg-emerald-100 transition-colors"
                          title="Fokus di peta"
                        >
                          <MapPin class="w-3.5 h-3.5 inline" />
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Directory Footer Notice -->
            <div class="pt-3 mt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600">
              <span>Menampilkan {{ filteredOffices.length }} dari {{ officesList.length }} Kantor</span>
              <span v-if="unassigned > 0" class="text-amber-600">
                ⚠️ {{ unassigned }} data belum memiliki geotag wilker
              </span>
            </div>
          </div>

        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- ROW 4: ANALYTICS & TRENDS                                                 -->
      <!-- ========================================================================= -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left: 30-Day Multi-Series ECharts Trend (7 cols) -->
        <div class="lg:col-span-7 rounded-2xl p-6 bg-white border border-slate-200/90 shadow-xs flex flex-col justify-between">
          <div>
            <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
              <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                  Tren Pengawasan Karantina (30 Hari)
                </h2>
                <p class="text-xs text-slate-600">
                  Aktivitas pengawasan sanitasi berdasarkan tanggal pemeriksaan.
                </p>
              </div>

              <!-- Line vs Bar Mode -->
              <div class="inline-flex items-center p-0.5 rounded-lg bg-slate-100 border border-slate-200">
                <button
                  type="button"
                  @click="trendViewMode = 'line'"
                  class="p-1 rounded-md transition-all cursor-pointer"
                  :class="trendViewMode === 'line' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                  title="Grafik Garis"
                >
                  <ChartLine class="w-4 h-4" />
                </button>
                <button
                  type="button"
                  @click="trendViewMode = 'bar'"
                  class="p-1 rounded-md transition-all cursor-pointer"
                  :class="trendViewMode === 'bar' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                  title="Grafik Batang"
                >
                  <ChartColumn class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- ECharts Container -->
            <div class="-mx-2 mt-2">
              <BaseChart :option="trendChartOption" height="260px" />
            </div>
          </div>

          <!-- Series Filter Toggles -->
          <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-3 text-xs">
            <button
              type="button"
              @click="toggleSeries('total')"
              class="inline-flex items-center gap-1.5 font-medium cursor-pointer"
            >
              <span
                class="w-3.5 h-3.5 rounded flex items-center justify-center text-white"
                :class="seriesVisibility.total ? 'bg-slate-900' : 'bg-slate-200'"
              >
                <Check class="w-2.5 h-2.5 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.total ? 'text-slate-900 font-bold' : 'text-slate-600'">Total</span>
            </button>

            <button
              type="button"
              @click="toggleSeries('aircraft')"
              class="inline-flex items-center gap-1.5 font-medium cursor-pointer"
            >
              <span
                class="w-3.5 h-3.5 rounded flex items-center justify-center text-white"
                :class="seriesVisibility.aircraft ? 'bg-sky-600' : 'bg-slate-200'"
              >
                <Check class="w-2.5 h-2.5 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.aircraft ? 'text-slate-900 font-bold' : 'text-slate-600'">Pesawat</span>
            </button>

            <button
              type="button"
              @click="toggleSeries('ship')"
              class="inline-flex items-center gap-1.5 font-medium cursor-pointer"
            >
              <span
                class="w-3.5 h-3.5 rounded flex items-center justify-center text-white"
                :class="seriesVisibility.ship ? 'bg-emerald-600' : 'bg-slate-200'"
              >
                <Check class="w-2.5 h-2.5 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.ship ? 'text-slate-900 font-bold' : 'text-slate-600'">Kapal</span>
            </button>

            <button
              type="button"
              @click="toggleSeries('alat')"
              class="inline-flex items-center gap-1.5 font-medium cursor-pointer"
            >
              <span
                class="w-3.5 h-3.5 rounded flex items-center justify-center text-white"
                :class="seriesVisibility.alat ? 'bg-amber-600' : 'bg-slate-200'"
              >
                <Check class="w-2.5 h-2.5 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.alat ? 'text-slate-900 font-bold' : 'text-slate-600'">Alkes Haji</span>
            </button>

            <button
              type="button"
              @click="toggleSeries('obat')"
              class="inline-flex items-center gap-1.5 font-medium cursor-pointer"
            >
              <span
                class="w-3.5 h-3.5 rounded flex items-center justify-center text-white"
                :class="seriesVisibility.obat ? 'bg-rose-600' : 'bg-slate-200'"
              >
                <Check class="w-2.5 h-2.5 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.obat ? 'text-slate-900 font-bold' : 'text-slate-600'">Obat P3K</span>
            </button>
          </div>
        </div>

        <!-- Right: Operational & Risk Focus Breakdown (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
          
          <!-- Card A: Tindakan Karantina Pesawat -->
          <div class="rounded-2xl p-5 bg-white border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <Plane class="w-4 h-4 text-sky-600" />
                Tindakan Sanitasi Pesawat
              </h3>
              <span class="text-xs text-slate-600">Total {{ aircraft_stats.total }} Pesawat</span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs">
              <div
                v-for="act in aircraft_stats.tindakan"
                :key="act.key"
                class="p-2.5 rounded-xl border border-slate-100 bg-slate-50/70 flex items-center justify-between"
              >
                <span class="text-slate-600">{{ act.label }}</span>
                <span class="font-bold text-slate-900">{{ act.count }}</span>
              </div>
            </div>
          </div>

          <!-- Card B: Temuan Risiko Karantina Kapal Laut (IHR 2005) -->
          <div class="rounded-2xl p-5 bg-white border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <Ship class="w-4 h-4 text-emerald-600" />
                Temuan Faktor Risiko Kapal
              </h3>
              <span class="text-xs text-slate-600">{{ ship_stats.ships_with_risk }} dari {{ ship_stats.total }} Kapal</span>
            </div>

            <div v-if="ship_stats.top_risk_areas && ship_stats.top_risk_areas.length > 0" class="space-y-2 text-xs">
              <div
                v-for="(area, idx) in ship_stats.top_risk_areas"
                :key="idx"
                class="flex items-center justify-between p-2 rounded-lg bg-amber-50/70 border border-amber-200/60 text-slate-800"
              >
                <div class="flex items-center gap-2 min-w-0">
                  <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                  <span class="font-medium truncate">{{ area.name }}</span>
                </div>
                <span class="font-bold text-amber-700 shrink-0 ml-2">{{ area.count }} temuan</span>
              </div>
            </div>
            <p v-else class="text-xs text-slate-600 italic">
              Nihil faktor risiko terdeteksi pada seluruh 32 area kapal.
            </p>
          </div>

          <!-- Card C: Sebaran Embarkasi Alkes & Obat Haji -->
          <div class="rounded-2xl p-5 bg-white border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <Stethoscope class="w-4 h-4 text-amber-600" />
                Sebaran Embarkasi Haji
              </h3>
              <span class="text-xs text-slate-600">{{ medical_stats.total }} Pemeriksaan</span>
            </div>

            <div class="flex flex-wrap gap-2 text-xs pt-1">
              <div
                v-for="(emb, idx) in medical_stats.embarkasi"
                :key="idx"
                class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 flex items-center gap-1.5 font-medium"
              >
                <span>{{ emb.name }}</span>
                <span class="w-4 h-4 rounded-full bg-white text-[10px] font-bold text-slate-900 flex items-center justify-center">
                  {{ emb.count }}
                </span>
              </div>
            </div>
          </div>

        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- ROW 5: LIVE RECENT INSPECTIONS FEED & AUDIT TRAIL                         -->
      <!-- ========================================================================= -->
      <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        
        <!-- Header Tabs -->
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="activeFeedTab = 'inspections'"
              class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2"
              :class="activeFeedTab === 'inspections'
                ? 'bg-slate-900 text-white shadow-2xs'
                : 'text-slate-600 hover:text-slate-900 bg-slate-100'"
            >
              <ClipboardCheck class="w-4 h-4" />
              <span>Dokumen Pemeriksaan Terkini</span>
              <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-500 text-slate-950 font-black">
                {{ recent_inspections.length }}
              </span>
            </button>

            <button
              type="button"
              @click="activeFeedTab = 'activities'"
              class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2"
              :class="activeFeedTab === 'activities'
                ? 'bg-slate-900 text-white shadow-2xs'
                : 'text-slate-600 hover:text-slate-900 bg-slate-100'"
            >
              <Activity class="w-4 h-4" />
              <span>Audit Log ({{ recent_activities.length }})</span>
            </button>

            <button
              type="button"
              @click="activeFeedTab = 'logins'"
              class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2"
              :class="activeFeedTab === 'logins'
                ? 'bg-slate-900 text-white shadow-2xs'
                : 'text-slate-600 hover:text-slate-900 bg-slate-100'"
            >
              <LogIn class="w-4 h-4" />
              <span>Sesi Login ({{ recent_logins.length }})</span>
            </button>
          </div>

          <!-- Feed Search & Status Filter (for Inspections) -->
          <div v-if="activeFeedTab === 'inspections'" class="flex flex-wrap items-center gap-2">
            <select
              v-model="statusFilterFeed"
              class="px-2.5 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-800 font-medium focus:outline-none"
            >
              <option value="all">Semua Status</option>
              <option value="submitted">Diajukan</option>
              <option value="approved">Disetujui</option>
              <option value="verified">Diverifikasi</option>
            </select>

            <div class="relative w-full sm:w-56">
              <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
              <input
                v-model="searchFeed"
                type="text"
                placeholder="Cari no reg / armada / petugas..."
                class="w-full pl-8 pr-2 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-slate-300"
              />
            </div>
          </div>
        </div>

        <!-- TAB CONTENT 1: INSPECTIONS TABLE -->
        <div v-if="activeFeedTab === 'inspections'" class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold text-slate-900">
                <th class="py-3 px-4">Layanan</th>
                <th class="py-3 px-4">No. Registrasi</th>
                <th class="py-3 px-4">Armada / Subjek</th>
                <th class="py-3 px-4">Petugas BKK</th>
                <th class="py-3 px-4">Lokasi / Wilker</th>
                <th class="py-3 px-4">Waktu</th>
                <th class="py-3 px-4">Status &amp; Hasil</th>
                <th class="py-3 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr
                v-for="item in filteredInspections"
                :key="item.registration_no"
                class="hover:bg-slate-50/80 transition-colors"
              >
                <!-- Layanan Badge -->
                <td class="py-3 px-4 whitespace-nowrap">
                  <span
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold"
                    :class="{
                      'bg-sky-50 text-sky-700 border border-sky-200': item.module === 'aircraft',
                      'bg-emerald-50 text-emerald-700 border border-emerald-200': item.module === 'ship',
                      'bg-amber-50 text-amber-700 border border-amber-200': item.module === 'alat',
                      'bg-rose-50 text-rose-700 border border-rose-200': item.module === 'obat',
                    }"
                  >
                    <component :is="moduleIconMap[item.module] || Layers" class="w-3 h-3" />
                    {{ item.module_label }}
                  </span>
                </td>

                <!-- Registration No -->
                <td class="py-3 px-4 whitespace-nowrap font-mono font-bold text-slate-900">
                  {{ item.registration_no }}
                </td>

                <!-- Subject -->
                <td class="py-3 px-4 text-slate-800 font-semibold max-w-xs truncate" :title="item.subject">
                  {{ item.subject }}
                </td>

                <!-- Officer -->
                <td class="py-3 px-4 whitespace-nowrap text-slate-600">
                  {{ item.officer }}
                </td>

                <!-- Office -->
                <td class="py-3 px-4 whitespace-nowrap text-slate-600">
                  <span class="inline-flex items-center gap-1">
                    <MapPin class="w-3 h-3 text-slate-400" />
                    {{ item.office }}
                  </span>
                </td>

                <!-- Time -->
                <td class="py-3 px-4 whitespace-nowrap text-slate-600">
                  <div>{{ item.date }}</div>
                  <div class="text-[10px] text-slate-600">{{ item.date_human }}</div>
                </td>

                <!-- Status & Result -->
                <td class="py-3 px-4 whitespace-nowrap">
                  <div class="flex items-center gap-1.5">
                    <span
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                      :class="{
                        'bg-emerald-50 text-emerald-700 border border-emerald-200': item.status === 'approved',
                        'bg-amber-50 text-amber-700 border border-amber-200': item.status === 'submitted',
                        'bg-sky-50 text-sky-700 border border-sky-200': item.status === 'verified',
                        'bg-rose-50 text-rose-700 border border-rose-200': item.status === 'rejected',
                      }"
                    >
                      {{ item.status_label }}
                    </span>
                    <span
                      class="text-[11px] font-semibold"
                      :class="item.result_ok ? 'text-emerald-700' : 'text-rose-600'"
                    >
                      {{ item.result }}
                    </span>
                  </div>
                </td>

                <!-- Action Button -->
                <td class="py-3 px-4 whitespace-nowrap text-right">
                  <Link
                    :href="getUrl(item.url)"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-800 transition-colors"
                  >
                    <span>Buka</span>
                    <ChevronRight class="w-3 h-3" />
                  </Link>
                </td>
              </tr>

              <tr v-if="filteredInspections.length === 0">
                <td colspan="8" class="py-8 text-center text-slate-600 text-xs">
                  Tidak ada dokumen pemeriksaan yang cocok dengan filter yang dipilih.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- TAB CONTENT 2: AUDIT LOG -->
        <div v-else-if="activeFeedTab === 'activities'" class="p-5">
          <div class="space-y-3">
            <div
              v-for="act in recent_activities"
              :key="act.id"
              class="flex items-start justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs"
            >
              <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0">
                  {{ act.causer_name.charAt(0) }}
                </div>
                <div>
                  <div class="font-bold text-slate-900">
                    {{ act.causer_name }}
                    <span class="font-normal text-slate-600">melakukan</span>
                    <span class="font-semibold text-emerald-700">{{ act.event || 'aktivitas' }}</span>
                  </div>
                  <div class="text-slate-600 mt-0.5">
                    {{ act.description }}
                  </div>
                </div>
              </div>
              <span class="text-[11px] text-slate-600 shrink-0 ml-4 font-mono">
                {{ act.created_at }}
              </span>
            </div>

            <div v-if="recent_activities.length === 0" class="py-6 text-center text-slate-600 text-xs">
              Belum ada aktivitas audit log yang tercatat.
            </div>
          </div>
        </div>

        <!-- TAB CONTENT 3: LOGIN HISTORY -->
        <div v-else-if="activeFeedTab === 'logins'" class="p-5">
          <div class="space-y-3">
            <div
              v-for="lh in recent_logins"
              :key="lh.id"
              class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs"
            >
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                  <LogIn class="w-4 h-4" />
                </div>
                <div>
                  <div class="font-bold text-slate-900">{{ lh.user_name }} ({{ lh.email }})</div>
                  <div class="text-[11px] text-slate-600">IP: {{ lh.ip_address }} &bull; Perangkat: {{ lh.device }}</div>
                </div>
              </div>
              <div class="text-right">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                  {{ lh.status }}
                </span>
                <div class="text-[10px] text-slate-600 mt-0.5">{{ lh.login_at }}</div>
              </div>
            </div>

            <div v-if="recent_logins.length === 0" class="py-6 text-center text-slate-600 text-xs">
              Belum ada riwayat sesi login yang tercatat.
            </div>
          </div>
        </div>

      </div>

    </main>
  </HybridNavbar>
</template>
