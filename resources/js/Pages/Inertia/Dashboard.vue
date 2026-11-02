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
// Offices & Interactive Map (CENTERPIECE)
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
const mapZoom = ref(8.3);
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
      zoom: o.is_hq ? 12.6 : 12.0,
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
      target.pitch || 45,
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
      name: 'Total',
      color: '#0f172a',
      data: props.trend?.total || [],
      lineWidth: 2.2,
      area: true,
      areaColor: ['rgba(15, 23, 42, 0.08)', 'rgba(15, 23, 42, 0.0)'],
    },
    {
      key: 'aircraft',
      name: 'Pesawat',
      color: '#0284c7',
      data: props.trend?.aircraft || [],
      lineWidth: 1.8,
      area: false,
    },
    {
      key: 'ship',
      name: 'Kapal',
      color: '#059669',
      data: props.trend?.ship || [],
      lineWidth: 1.8,
      area: false,
    },
    {
      key: 'alat',
      name: 'Alkes',
      color: '#d97706',
      data: props.trend?.alat || [],
      lineWidth: 1.6,
      area: false,
    },
    {
      key: 'obat',
      name: 'Obat',
      color: '#e11d48',
      data: props.trend?.obat || [],
      lineWidth: 1.6,
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
      symbolSize: 5,
      showSymbol: false,
      data: cfg.data,
      itemStyle: { color: cfg.color, borderRadius: [3, 3, 0, 0] },
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
      padding: [8, 12],
      textStyle: { color: '#0f172a', fontSize: 11 },
      extraCssText: 'box-shadow: 0 8px 20px -4px rgba(15, 23, 42, 0.12); border-radius: 10px;',
      axisPointer: {
        type: 'line',
        lineStyle: { color: '#0f172a', width: 1, type: 'dashed' },
      },
    },
    grid: {
      top: 24,
      right: 16,
      bottom: 22,
      left: 30,
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
  <Head title="Pusat Komando Geospasial — BKK Kelas I Bandung" />

  <HybridNavbar mode="inertia" currentRoute="dashboard">
    <main class="w-full px-3 sm:px-5 lg:px-6 pt-3 pb-12 space-y-3.5 transition-all duration-300">
      
      <!-- ========================================================================= -->
      <!-- COMPACT COMMAND HEADER STRIP                                              -->
      <!-- ========================================================================= -->
      <div class="flex flex-wrap items-center justify-between gap-2.5 px-3.5 py-2 rounded-xl bg-slate-900 text-white shadow-xs border border-slate-800">
        <div class="flex items-center gap-2.5 min-w-0">
          <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-400/30">
            <Radio class="w-3.5 h-3.5 animate-pulse" />
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <h1 class="text-sm font-bold tracking-tight text-white truncate">
                Pusat Komando Geospasial BKK Kelas I Bandung
              </h1>
              <span class="hidden sm:inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                LIVE
              </span>
            </div>
            <p class="text-[11px] text-slate-400 truncate">
              Monitoring 1 Kantor Induk &amp; 6 Wilayah Kerja Jawa Barat &bull; {{ generated_at }}
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2 shrink-0 text-xs">
          <button
            type="button"
            @click="refreshData"
            :disabled="isRefreshing"
            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/15 transition-all cursor-pointer disabled:opacity-50"
            title="Segarkan data"
          >
            <RefreshCw class="w-3 h-3" :class="isRefreshing ? 'animate-spin text-emerald-400' : 'text-slate-300'" />
            <span class="hidden sm:inline">{{ isRefreshing ? 'Sinkron...' : 'Segarkan' }}</span>
          </button>

          <div class="h-4 w-px bg-slate-700 hidden sm:block"></div>

          <div class="flex items-center gap-1">
            <Link
              :href="getUrl('/public_aircraft')"
              target="_blank"
              class="px-2 py-1 rounded-md text-[11px] font-semibold bg-sky-500/20 text-sky-300 hover:bg-sky-500/30 transition-all"
            >
              + Pesawat
            </Link>
            <Link
              :href="getUrl('/public_sanitasi')"
              target="_blank"
              class="px-2 py-1 rounded-md text-[11px] font-semibold bg-emerald-500/20 text-emerald-300 hover:bg-emerald-500/30 transition-all"
            >
              + Kapal
            </Link>
            <Link
              :href="getUrl('/public_alat')"
              target="_blank"
              class="px-2 py-1 rounded-md text-[11px] font-semibold bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 transition-all"
            >
              + Alkes
            </Link>
            <Link
              :href="getUrl('/public_obat')"
              target="_blank"
              class="px-2 py-1 rounded-md text-[11px] font-semibold bg-rose-500/20 text-rose-300 hover:bg-rose-500/30 transition-all"
            >
              + Obat
            </Link>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- COMPACT KPI CARDS STRIP (4 SERVICE TILES)                                 -->
      <!-- ========================================================================= -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5">
        
        <!-- Tile 1: Sanitasi Pesawat -->
        <div
          @click="selectedModuleKey = selectedModuleKey === 'aircraft' ? 'all' : 'aircraft'"
          class="rounded-xl p-3 transition-all cursor-pointer border relative overflow-hidden group"
          :class="selectedModuleKey === 'aircraft'
            ? 'bg-slate-900 text-white border-slate-900 shadow-sm ring-2 ring-sky-500'
            : 'bg-white hover:bg-slate-50 text-slate-800 border-slate-200/90 shadow-2xs'"
        >
          <div class="flex items-center justify-between text-xs">
            <span class="font-bold flex items-center gap-1.5" :class="selectedModuleKey === 'aircraft' ? 'text-sky-300' : 'text-slate-700'">
              <Plane class="w-3.5 h-3.5 text-sky-500" />
              Sanitasi Pesawat
            </span>
            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded" :class="selectedModuleKey === 'aircraft' ? 'bg-sky-400/20 text-sky-200' : 'bg-sky-50 text-sky-700'">
              {{ kpi.compliance_rate }}% Laik
            </span>
          </div>
          <div class="flex items-baseline justify-between mt-1">
            <span class="text-xl font-black tracking-tight" :class="selectedModuleKey === 'aircraft' ? 'text-white' : 'text-slate-900'">
              {{ aircraft_stats.total }} <span class="text-xs font-normal text-slate-400">dok</span>
            </span>
            <span class="text-[11px] font-semibold" :class="selectedModuleKey === 'aircraft' ? 'text-slate-300' : 'text-slate-500'">
              {{ aircraft_stats.layak }} Layak &bull; {{ aircraft_stats.tidak_layak }} Tindakan
            </span>
          </div>
        </div>

        <!-- Tile 2: Sanitasi Kapal Laut -->
        <div
          @click="selectedModuleKey = selectedModuleKey === 'ship' ? 'all' : 'ship'"
          class="rounded-xl p-3 transition-all cursor-pointer border relative overflow-hidden group"
          :class="selectedModuleKey === 'ship'
            ? 'bg-slate-900 text-white border-slate-900 shadow-sm ring-2 ring-emerald-500'
            : 'bg-white hover:bg-slate-50 text-slate-800 border-slate-200/90 shadow-2xs'"
        >
          <div class="flex items-center justify-between text-xs">
            <span class="font-bold flex items-center gap-1.5" :class="selectedModuleKey === 'ship' ? 'text-emerald-300' : 'text-slate-700'">
              <Ship class="w-3.5 h-3.5 text-emerald-500" />
              Sanitasi Kapal Laut
            </span>
            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded" :class="selectedModuleKey === 'ship' ? 'bg-emerald-400/20 text-emerald-200' : 'bg-emerald-50 text-emerald-700'">
              IHR 2005
            </span>
          </div>
          <div class="flex items-baseline justify-between mt-1">
            <span class="text-xl font-black tracking-tight" :class="selectedModuleKey === 'ship' ? 'text-white' : 'text-slate-900'">
              {{ ship_stats.total }} <span class="text-xs font-normal text-slate-400">kapal</span>
            </span>
            <span class="text-[11px] font-semibold" :class="selectedModuleKey === 'ship' ? 'text-slate-300' : 'text-slate-500'">
              {{ ship_stats.sscec }} SSCEC &bull; {{ ship_stats.sscc }} SSCC
            </span>
          </div>
        </div>

        <!-- Tile 3: Alkes Haji -->
        <div
          @click="selectedModuleKey = selectedModuleKey === 'alat' ? 'all' : 'alat'"
          class="rounded-xl p-3 transition-all cursor-pointer border relative overflow-hidden group"
          :class="selectedModuleKey === 'alat'
            ? 'bg-slate-900 text-white border-slate-900 shadow-sm ring-2 ring-amber-500'
            : 'bg-white hover:bg-slate-50 text-slate-800 border-slate-200/90 shadow-2xs'"
        >
          <div class="flex items-center justify-between text-xs">
            <span class="font-bold flex items-center gap-1.5" :class="selectedModuleKey === 'alat' ? 'text-amber-300' : 'text-slate-700'">
              <Stethoscope class="w-3.5 h-3.5 text-amber-500" />
              Alkes Pesawat Haji
            </span>
            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded" :class="selectedModuleKey === 'alat' ? 'bg-amber-400/20 text-amber-200' : 'bg-amber-50 text-amber-700'">
              P3K &amp; Emergensi
            </span>
          </div>
          <div class="flex items-baseline justify-between mt-1">
            <span class="text-xl font-black tracking-tight" :class="selectedModuleKey === 'alat' ? 'text-white' : 'text-slate-900'">
              {{ medical_stats.alat }} <span class="text-xs font-normal text-slate-400">dok</span>
            </span>
            <span class="text-[11px] font-semibold" :class="selectedModuleKey === 'alat' ? 'text-slate-300' : 'text-slate-500'">
              {{ kpi.approved }} Selesai &bull; {{ kpi.pending }} Proses
            </span>
          </div>
        </div>

        <!-- Tile 4: Obat P3K Pesawat -->
        <div
          @click="selectedModuleKey = selectedModuleKey === 'obat' ? 'all' : 'obat'"
          class="rounded-xl p-3 transition-all cursor-pointer border relative overflow-hidden group"
          :class="selectedModuleKey === 'obat'
            ? 'bg-slate-900 text-white border-slate-900 shadow-sm ring-2 ring-rose-500'
            : 'bg-white hover:bg-slate-50 text-slate-800 border-slate-200/90 shadow-2xs'"
        >
          <div class="flex items-center justify-between text-xs">
            <span class="font-bold flex items-center gap-1.5" :class="selectedModuleKey === 'obat' ? 'text-rose-300' : 'text-slate-700'">
              <Pill class="w-3.5 h-3.5 text-rose-500" />
              Obat-Obatan Pesawat
            </span>
            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded" :class="selectedModuleKey === 'obat' ? 'bg-rose-400/20 text-rose-200' : 'bg-rose-50 text-rose-700'">
              Total: {{ kpi.total }} Dokumen
            </span>
          </div>
          <div class="flex items-baseline justify-between mt-1">
            <span class="text-xl font-black tracking-tight" :class="selectedModuleKey === 'obat' ? 'text-white' : 'text-slate-900'">
              {{ medical_stats.obat }} <span class="text-xs font-normal text-slate-400">dok</span>
            </span>
            <span class="text-[11px] font-semibold" :class="selectedModuleKey === 'obat' ? 'text-slate-300' : 'text-slate-500'">
              {{ kpi.today }} Hari Ini &bull; {{ kpi.completion_rate }}% Verifikasi
            </span>
          </div>
        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- CENTERPIECE: PETA GEOSPASIAL WILAYAH KERJA (COMMAND CENTER HERO)          -->
      <!-- ========================================================================= -->
      <div class="bg-white rounded-2xl border-2 border-slate-300/80 shadow-md overflow-hidden ring-1 ring-slate-900/5">
        
        <!-- Centerpiece Top Control Bar -->
        <div class="px-4 py-2.5 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white flex flex-wrap items-center justify-between gap-2.5 border-b border-slate-700/80">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-emerald-500 text-slate-950 flex items-center justify-center font-black text-xs shadow-xs">
              <MapPin class="w-4 h-4" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h2 class="text-sm font-extrabold tracking-tight text-white">
                  Pusat Kendali Geospasial Wilayah Kerja Jawa Barat
                </h2>
                <span class="px-1.5 py-0.2 rounded text-[10px] font-mono font-bold bg-emerald-400 text-slate-950">
                  OpenStreetMap WebGL
                </span>
              </div>
              <p class="text-[11px] text-slate-300">
                Visualisasi interaktif 1 Kantor Induk &amp; 6 Wilker/Pos Karantina Kesehatan
              </p>
            </div>
          </div>

          <!-- WebGL Camera Actions -->
          <div class="flex items-center gap-1.5">
            <button
              type="button"
              @click="resetGlobalView"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-white/10 hover:bg-white/20 text-white transition-all cursor-pointer"
              title="Reset ke tampilan global Jawa Barat"
            >
              <Globe class="w-3 h-3 text-emerald-400" />
              <span>Reset Posisi</span>
            </button>
            <button
              type="button"
              @click="toggle3DPitch"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold border transition-all cursor-pointer"
              :class="mapPitch > 25
                ? 'bg-emerald-500 text-slate-950 border-emerald-400 shadow-xs'
                : 'bg-white/10 hover:bg-white/20 text-white border-white/20'"
            >
              <Box class="w-3 h-3" />
              <span>3D</span>
            </button>
            <button
              type="button"
              @click="toggleOrbit"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold border transition-all cursor-pointer"
              :class="isOrbiting
                ? 'bg-emerald-500 text-slate-950 border-emerald-400 shadow-xs'
                : 'bg-white/10 hover:bg-white/20 text-white border-white/20'"
            >
              <RotateCw class="w-3 h-3" :class="isOrbiting ? 'animate-spin' : ''" />
              <span>Orbit</span>
            </button>
          </div>
        </div>

        <!-- Fly to Node Bar (High Priority Quick Nav) -->
        <div class="px-4 py-2 bg-slate-50 border-b border-slate-200/90 flex flex-wrap items-center justify-between gap-2 text-xs">
          <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-[11px] font-bold text-slate-500 mr-1 flex items-center gap-1">
              <Navigation class="w-3 h-3 text-emerald-600" />
              Fly to:
            </span>
            <button
              v-for="node in bkkMapMarkers"
              :key="node.id"
              type="button"
              @click="selectOffice(node)"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs transition-all cursor-pointer border"
              :class="selectedOffice?.no === node.no
                ? 'bg-slate-900 text-white border-slate-900 font-bold shadow-xs'
                : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100 hover:text-slate-950'"
            >
              <span class="text-[9px] font-mono font-black" :class="selectedOffice?.no === node.no ? 'text-emerald-400' : 'text-slate-500'">
                {{ node.shortCode }}
              </span>
              <span>{{ node.shortTitle.replace('Wilker ', '').replace('Induk ', '') }}</span>
              <span
                v-if="node.total > 0"
                class="text-[9px] font-bold px-1 rounded-full"
                :class="selectedOffice?.no === node.no ? 'bg-emerald-400 text-slate-950' : 'bg-emerald-100 text-emerald-800'"
              >
                {{ node.total }}
              </span>
            </button>
          </div>

          <div class="text-[11px] text-slate-500 font-medium hidden md:block">
            Klik node atau baris wilker untuk mengarahkan peta
          </div>
        </div>

        <!-- The Map + Side Cockpit Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-200">
          
          <!-- Large WebGL Map Area (8 Cols) -->
          <div class="lg:col-span-8 relative min-w-0 bg-slate-100">
            <BaseMap
              ref="mapComponentRef"
              :initialCenter="[107.55, -6.65]"
              :initialZoom="8.1"
              :initialPitch="25"
              :initialBearing="0"
              styleType="osm"
              :markers="bkkMapMarkers"
              height="500px"
              @selectMarker="selectOffice"
              @mapMove="onCameraMove"
            />

            <!-- Top Left Floating HUD -->
            <div class="absolute top-3 left-3 z-10 w-44 rounded-xl bg-white/95 backdrop-blur-md border border-slate-200/90 px-2.5 py-2 shadow-md pointer-events-none text-[10px] font-mono">
              <div class="flex items-center gap-1.5 font-bold text-emerald-700 mb-1">
                <Navigation class="w-3 h-3 text-emerald-600" />
                <span>Posisi GPS Kamera</span>
              </div>
              <div class="text-slate-500 space-y-0.5">
                <div class="flex justify-between">
                  <span>Koordinat:</span>
                  <span class="text-slate-900 font-bold">{{ mapCenter[0].toFixed(2) }}, {{ mapCenter[1].toFixed(2) }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Zoom / Tilt:</span>
                  <span class="text-slate-900 font-bold">{{ mapZoom.toFixed(1) }}x / {{ mapPitch }}°</span>
                </div>
              </div>
            </div>

            <!-- Bottom Right Selected Node Detail Card -->
            <div
              v-if="activeMapNode"
              class="absolute bottom-3 right-3 z-10 rounded-xl bg-white/95 backdrop-blur-md border border-slate-200/90 shadow-xl transition-all duration-200"
              :class="isNodeCardMinimized ? 'w-48 px-2.5 py-1.5' : 'w-64 sm:w-72 px-3.5 py-2.5'"
            >
              <div class="flex items-center justify-between gap-1.5">
                <div class="flex items-center gap-2 min-w-0">
                  <div class="w-6 h-6 rounded bg-slate-900 text-white flex items-center justify-center text-[10px] font-mono font-black shrink-0">
                    {{ activeMapNode.shortCode }}
                  </div>
                  <div class="min-w-0">
                    <h4 class="text-xs font-bold text-slate-900 truncate">
                      {{ activeMapNode.title }}
                    </h4>
                    <p v-if="!isNodeCardMinimized" class="text-[10px] text-slate-500 truncate">
                      {{ activeMapNode.type }} &bull; {{ activeMapNode.moda }}
                    </p>
                  </div>
                </div>
                <button
                  type="button"
                  @click="isNodeCardMinimized = !isNodeCardMinimized"
                  class="w-5 h-5 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-xs leading-none cursor-pointer"
                >
                  {{ isNodeCardMinimized ? '+' : '−' }}
                </button>
              </div>

              <template v-if="!isNodeCardMinimized">
                <div class="grid grid-cols-2 gap-2 pt-2 mt-2 border-t border-slate-100 text-[10px]">
                  <div>
                    <span class="text-slate-400 block">Total Layanan:</span>
                    <span class="font-bold text-emerald-700 text-xs">{{ activeMapNode.total || 0 }} Dokumen</span>
                  </div>
                  <div class="border-l border-slate-100 pl-2">
                    <span class="text-slate-400 block">Aktivitas Terakhir:</span>
                    <span class="font-semibold text-slate-800 truncate block">{{ activeMapNode.last_human || 'Belum ada' }}</span>
                  </div>
                </div>
                <div class="mt-2 pt-1.5 border-t border-slate-100 text-[10px] text-slate-500 line-clamp-2 leading-tight">
                  📍 {{ activeMapNode.address }}
                </div>
              </template>
            </div>
          </div>

          <!-- Side Cockpit: Wilker Interactive Directory (4 Cols) -->
          <div class="lg:col-span-4 p-3.5 flex flex-col justify-between bg-slate-50/50">
            <div>
              <!-- Search & Filter Tab Bar -->
              <div class="space-y-2 pb-2.5 border-b border-slate-200">
                <div class="flex items-center justify-between gap-1">
                  <div class="flex items-center gap-1">
                    <button
                      type="button"
                      @click="activeOfficeFilter = 'all'"
                      class="px-2 py-0.5 rounded text-[11px] font-bold transition-all cursor-pointer"
                      :class="activeOfficeFilter === 'all' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900'"
                    >
                      Semua (7)
                    </button>
                    <button
                      type="button"
                      @click="activeOfficeFilter = 'udara'"
                      class="px-2 py-0.5 rounded text-[11px] font-bold transition-all cursor-pointer"
                      :class="activeOfficeFilter === 'udara' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900'"
                    >
                      Udara (3)
                    </button>
                    <button
                      type="button"
                      @click="activeOfficeFilter = 'laut'"
                      class="px-2 py-0.5 rounded text-[11px] font-bold transition-all cursor-pointer"
                      :class="activeOfficeFilter === 'laut' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900'"
                    >
                      Laut (4)
                    </button>
                  </div>

                  <span class="text-[10px] font-mono text-slate-400">7 Node</span>
                </div>

                <div class="relative w-full">
                  <Search class="w-3 h-3 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
                  <input
                    v-model="searchOffice"
                    type="text"
                    placeholder="Cari wilker / alamat..."
                    class="w-full pl-7 pr-2 py-1 rounded-lg bg-white border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-slate-300"
                  />
                </div>
              </div>

              <!-- High-Density Wilker Telemetry List -->
              <div class="divide-y divide-slate-100 overflow-y-auto max-h-[380px] mt-1 pr-1">
                <div
                  v-for="office in filteredOffices"
                  :key="office.no"
                  @click="selectOffice(office)"
                  class="py-2 px-2 rounded-xl transition-all cursor-pointer flex items-center justify-between gap-2"
                  :class="selectedOffice?.no === office.no
                    ? 'bg-emerald-50 border border-emerald-200 shadow-2xs font-bold'
                    : 'hover:bg-white text-slate-700'"
                >
                  <div class="flex items-center gap-2 min-w-0">
                    <span class="w-5 h-5 rounded-md bg-slate-900 text-white text-[9px] font-mono font-black flex items-center justify-center shrink-0">
                      {{ office.no }}
                    </span>
                    <div class="min-w-0">
                      <div class="text-xs truncate font-bold text-slate-900" :title="office.name">
                        {{ office.short_name || office.name }}
                      </div>
                      <div class="text-[10px] text-slate-500 truncate">
                        {{ office.moda }} &bull; {{ office.zone }}
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center gap-2 shrink-0">
                    <div class="text-right">
                      <div class="text-xs font-black text-slate-900 leading-none">
                        {{ office.total || 0 }}
                      </div>
                      <div class="text-[9px] text-slate-400">dokumen</div>
                    </div>

                    <button
                      type="button"
                      class="w-5 h-5 rounded bg-slate-100 hover:bg-emerald-100 text-slate-400 hover:text-emerald-700 flex items-center justify-center transition-colors"
                      title="Fokus di peta"
                    >
                      <MapPin class="w-3 h-3" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="pt-2 mt-2 border-t border-slate-200 text-[10px] text-slate-500 flex items-center justify-between">
              <span>Kantor Terpilih: <strong class="text-slate-800">{{ selectedOffice?.short_name || '-' }}</strong></span>
              <span class="font-mono text-emerald-700 font-bold">{{ selectedOffice?.total || 0 }} Dok</span>
            </div>
          </div>

        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- COMPACT LOWER COCKPIT: TREND CHARTS + OPERATIONAL RISK FOCUS              -->
      <!-- ========================================================================= -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5">
        
        <!-- 30-Day Trend Chart (7 cols) -->
        <div class="lg:col-span-7 rounded-2xl p-4 bg-white border border-slate-200/90 shadow-2xs flex flex-col justify-between">
          <div>
            <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
              <div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-900 tracking-tight flex items-center gap-1.5">
                  <ChartLine class="w-4 h-4 text-emerald-600" />
                  Tren Pengawasan 30 Hari
                </h3>
              </div>

              <!-- Line vs Bar Mode -->
              <div class="inline-flex items-center p-0.5 rounded-lg bg-slate-100 border border-slate-200">
                <button
                  type="button"
                  @click="trendViewMode = 'line'"
                  class="p-1 rounded transition-all cursor-pointer"
                  :class="trendViewMode === 'line' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-400 hover:text-slate-700'"
                  title="Garis"
                >
                  <ChartLine class="w-3.5 h-3.5" />
                </button>
                <button
                  type="button"
                  @click="trendViewMode = 'bar'"
                  class="p-1 rounded transition-all cursor-pointer"
                  :class="trendViewMode === 'bar' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-400 hover:text-slate-700'"
                  title="Batang"
                >
                  <ChartColumn class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>

            <!-- ECharts Chart -->
            <div class="-mx-2">
              <BaseChart :option="trendChartOption" height="210px" />
            </div>
          </div>

          <!-- Series Checklist -->
          <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-3 text-[11px]">
            <button type="button" @click="toggleSeries('total')" class="inline-flex items-center gap-1 cursor-pointer">
              <span class="w-3 h-3 rounded flex items-center justify-center text-white" :class="seriesVisibility.total ? 'bg-slate-900' : 'bg-slate-200'">
                <Check class="w-2 h-2 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.total ? 'font-bold text-slate-900' : 'text-slate-400'">Total ({{ kpi.total }})</span>
            </button>

            <button type="button" @click="toggleSeries('aircraft')" class="inline-flex items-center gap-1 cursor-pointer">
              <span class="w-3 h-3 rounded flex items-center justify-center text-white" :class="seriesVisibility.aircraft ? 'bg-sky-600' : 'bg-slate-200'">
                <Check class="w-2 h-2 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.aircraft ? 'font-bold text-slate-900' : 'text-slate-400'">Pesawat ({{ aircraft_stats.total }})</span>
            </button>

            <button type="button" @click="toggleSeries('ship')" class="inline-flex items-center gap-1 cursor-pointer">
              <span class="w-3 h-3 rounded flex items-center justify-center text-white" :class="seriesVisibility.ship ? 'bg-emerald-600' : 'bg-slate-200'">
                <Check class="w-2 h-2 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.ship ? 'font-bold text-slate-900' : 'text-slate-400'">Kapal ({{ ship_stats.total }})</span>
            </button>

            <button type="button" @click="toggleSeries('alat')" class="inline-flex items-center gap-1 cursor-pointer">
              <span class="w-3 h-3 rounded flex items-center justify-center text-white" :class="seriesVisibility.alat ? 'bg-amber-600' : 'bg-slate-200'">
                <Check class="w-2 h-2 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.alat ? 'font-bold text-slate-900' : 'text-slate-400'">Alkes ({{ medical_stats.alat }})</span>
            </button>

            <button type="button" @click="toggleSeries('obat')" class="inline-flex items-center gap-1 cursor-pointer">
              <span class="w-3 h-3 rounded flex items-center justify-center text-white" :class="seriesVisibility.obat ? 'bg-rose-600' : 'bg-slate-200'">
                <Check class="w-2 h-2 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.obat ? 'font-bold text-slate-900' : 'text-slate-400'">Obat ({{ medical_stats.obat }})</span>
            </button>
          </div>
        </div>

        <!-- Operational Risk & Actions Focus (5 cols) -->
        <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3">
          
          <!-- Card 1: Tindakan Pesawat -->
          <div class="rounded-2xl p-4 bg-white border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between mb-2">
              <h4 class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                <Plane class="w-3.5 h-3.5 text-sky-600" />
                Tindakan Sanitasi Pesawat
              </h4>
              <span class="text-[11px] font-semibold text-slate-500">{{ aircraft_stats.layak }} Layak &bull; {{ aircraft_stats.tidak_layak }} Tindakan</span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs">
              <div
                v-for="act in aircraft_stats.tindakan"
                :key="act.key"
                class="px-2.5 py-1.5 rounded-lg border border-slate-100 bg-slate-50 flex items-center justify-between"
              >
                <span class="text-slate-600 text-[11px]">{{ act.label }}</span>
                <span class="font-bold text-slate-900">{{ act.count }}</span>
              </div>
            </div>
          </div>

          <!-- Card 2: Temuan Risiko Kapal (IHR 2005) -->
          <div class="rounded-2xl p-4 bg-white border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between mb-2">
              <h4 class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                <Ship class="w-3.5 h-3.5 text-emerald-600" />
                Temuan Faktor Risiko Kapal
              </h4>
              <span class="text-[11px] font-semibold text-amber-600">{{ ship_stats.ships_with_risk }} dari {{ ship_stats.total }} Kapal</span>
            </div>

            <div v-if="ship_stats.top_risk_areas && ship_stats.top_risk_areas.length > 0" class="space-y-1.5 text-xs">
              <div
                v-for="(area, idx) in ship_stats.top_risk_areas"
                :key="idx"
                class="flex items-center justify-between px-2.5 py-1 rounded bg-amber-50/80 border border-amber-200/50 text-[11px]"
              >
                <span class="font-medium text-slate-800 truncate">{{ area.name }}</span>
                <span class="font-bold text-amber-700 shrink-0 ml-2">{{ area.count }} temuan</span>
              </div>
            </div>
            <p v-else class="text-[11px] text-slate-500 italic">
              Nihil faktor risiko pada seluruh area sanitasi kapal.
            </p>
          </div>

        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- COMPACT TABLE: DOKUMEN PEMERIKSAAN TERKINI (LIVE FEED)                    -->
      <!-- ========================================================================= -->
      <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        
        <!-- Header & Tabs -->
        <div class="px-4 py-2.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2.5 text-xs">
          <div class="flex items-center gap-1.5">
            <button
              type="button"
              @click="activeFeedTab = 'inspections'"
              class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer flex items-center gap-1.5"
              :class="activeFeedTab === 'inspections' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900 bg-slate-100'"
            >
              <ClipboardCheck class="w-3.5 h-3.5" />
              <span>Dokumen Terkini</span>
              <span class="px-1 py-0.2 rounded-full text-[9px] bg-emerald-400 text-slate-950 font-black">
                {{ recent_inspections.length }}
              </span>
            </button>

            <button
              type="button"
              @click="activeFeedTab = 'activities'"
              class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer flex items-center gap-1.5"
              :class="activeFeedTab === 'activities' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900 bg-slate-100'"
            >
              <Activity class="w-3.5 h-3.5" />
              <span>Audit Log ({{ recent_activities.length }})</span>
            </button>

            <button
              type="button"
              @click="activeFeedTab = 'logins'"
              class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer flex items-center gap-1.5"
              :class="activeFeedTab === 'logins' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900 bg-slate-100'"
            >
              <LogIn class="w-3.5 h-3.5" />
              <span>Sesi Login ({{ recent_logins.length }})</span>
            </button>
          </div>

          <!-- Search & Status Filter -->
          <div v-if="activeFeedTab === 'inspections'" class="flex items-center gap-2">
            <select
              v-model="statusFilterFeed"
              class="px-2 py-1 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-800 font-medium focus:outline-none"
            >
              <option value="all">Semua Status</option>
              <option value="submitted">Diajukan</option>
              <option value="approved">Disetujui</option>
              <option value="verified">Diverifikasi</option>
            </select>

            <div class="relative w-44 sm:w-52">
              <Search class="w-3 h-3 text-slate-400 absolute left-2 top-1/2 -translate-y-1/2" />
              <input
                v-model="searchFeed"
                type="text"
                placeholder="Cari no reg / armada..."
                class="w-full pl-6 pr-2 py-1 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white"
              />
            </div>
          </div>
        </div>

        <!-- Table View -->
        <div v-if="activeFeedTab === 'inspections'" class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-slate-50 text-slate-900 font-bold border-b border-slate-100 text-[11px]">
                <th class="py-2.5 px-3">Layanan</th>
                <th class="py-2.5 px-3">No. Registrasi</th>
                <th class="py-2.5 px-3">Armada / Subjek</th>
                <th class="py-2.5 px-3">Petugas BKK</th>
                <th class="py-2.5 px-3">Lokasi / Wilker</th>
                <th class="py-2.5 px-3">Waktu</th>
                <th class="py-2.5 px-3">Status &amp; Hasil</th>
                <th class="py-2.5 px-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr
                v-for="item in filteredInspections"
                :key="item.registration_no"
                class="hover:bg-slate-50/70 transition-colors"
              >
                <!-- Layanan Badge -->
                <td class="py-2 px-3 whitespace-nowrap">
                  <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold"
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

                <td class="py-2 px-3 whitespace-nowrap font-mono font-bold text-slate-900">
                  {{ item.registration_no }}
                </td>

                <td class="py-2 px-3 text-slate-800 font-semibold max-w-xs truncate" :title="item.subject">
                  {{ item.subject }}
                </td>

                <td class="py-2 px-3 whitespace-nowrap text-slate-600">
                  {{ item.officer }}
                </td>

                <td class="py-2 px-3 whitespace-nowrap text-slate-600">
                  <span class="inline-flex items-center gap-1">
                    <MapPin class="w-3 h-3 text-slate-400" />
                    {{ item.office }}
                  </span>
                </td>

                <td class="py-2 px-3 whitespace-nowrap text-slate-500 text-[11px]">
                  {{ item.date }}
                </td>

                <td class="py-2 px-3 whitespace-nowrap">
                  <div class="flex items-center gap-1.5">
                    <span
                      class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider"
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

                <td class="py-2 px-3 whitespace-nowrap text-right">
                  <Link
                    :href="getUrl(item.url)"
                    class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-800 transition-colors"
                  >
                    <span>Buka</span>
                    <ChevronRight class="w-3 h-3" />
                  </Link>
                </td>
              </tr>

              <tr v-if="filteredInspections.length === 0">
                <td colspan="8" class="py-6 text-center text-slate-500 text-xs">
                  Tidak ada dokumen pemeriksaan yang cocok dengan filter yang dipilih.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Audit Log View -->
        <div v-else-if="activeFeedTab === 'activities'" class="p-3.5 space-y-2">
          <div
            v-for="act in recent_activities"
            :key="act.id"
            class="flex items-start justify-between p-2 rounded-lg bg-slate-50 border border-slate-100 text-xs"
          >
            <div class="flex items-start gap-2">
              <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-[10px] shrink-0">
                {{ act.causer_name.charAt(0) }}
              </div>
              <div>
                <div class="font-bold text-slate-900">
                  {{ act.causer_name }}
                  <span class="font-normal text-slate-500">melakukan</span>
                  <span class="font-semibold text-emerald-700">{{ act.event || 'aktivitas' }}</span>
                </div>
                <div class="text-slate-600 text-[11px]">
                  {{ act.description }}
                </div>
              </div>
            </div>
            <span class="text-[10px] text-slate-400 shrink-0 ml-2 font-mono">
              {{ act.created_at }}
            </span>
          </div>
        </div>

        <!-- Login History View -->
        <div v-else-if="activeFeedTab === 'logins'" class="p-3.5 space-y-2">
          <div
            v-for="lh in recent_logins"
            :key="lh.id"
            class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100 text-xs"
          >
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                <LogIn class="w-3 h-3" />
              </div>
              <div>
                <div class="font-bold text-slate-900">{{ lh.user_name }} ({{ lh.email }})</div>
                <div class="text-[10px] text-slate-500">IP: {{ lh.ip_address }} &bull; Perangkat: {{ lh.device }}</div>
              </div>
            </div>
            <div class="text-right">
              <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                {{ lh.status }}
              </span>
              <div class="text-[10px] text-slate-400 mt-0.5">{{ lh.login_at }}</div>
            </div>
          </div>
        </div>

      </div>

    </main>
  </HybridNavbar>
</template>
