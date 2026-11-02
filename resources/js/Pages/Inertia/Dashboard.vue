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
  Crosshair,
  Compass,
  Wifi,
  Zap,
  Scan,
  Terminal,
  Shield,
  Gauge,
  Flame,
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
// Offices & Interactive Map (CENTERPIECE HERO)
// ---------------------------------------------------------------------------
const officesList = computed(() => {
  if (props.offices && props.offices.length > 0) return props.offices;
  if (props.bkk_offices && props.bkk_offices.length > 0) return props.bkk_offices;
  return [];
});

const selectedOffice = ref(null);
const searchOffice = ref('');
const activeOfficeFilter = ref('all'); // 'all' | 'udara' | 'laut'
const activeMapLayer = ref('osm'); // 'osm' | 'hot' | 'osmde'

onMounted(() => {
  if (officesList.value.length > 0) {
    selectedOffice.value = officesList.value[0];
  }
});

const mapComponentRef = ref(null);
const mapCenter = ref([107.6108, -6.8989]);
const mapZoom = ref(8.3);
const mapPitch = ref(32);
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
        { label: 'Total Layanan', value: `${totalOps} Dokumen`, color: '#10b981' },
        { label: 'Terakhir Aktif', value: o.last_human || 'Belum ada', color: '#38bdf8' },
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
      target.zoom || 12.2,
      target.pitch || 45,
      target.bearing || 0
    );
  }
};

const switchMapLayer = (layerKey) => {
  activeMapLayer.value = layerKey;
  if (mapComponentRef.value) {
    mapComponentRef.value.setStyle(layerKey);
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

// Max office total for relative workload bar calculation
const maxOfficeTotal = computed(() => {
  const totals = officesList.value.map(o => o.total || 0);
  return Math.max(...totals, 1);
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
      lineWidth: 2.4,
      area: true,
      areaColor: ['rgba(16, 185, 129, 0.22)', 'rgba(16, 185, 129, 0.0)'],
    },
    {
      key: 'aircraft',
      name: 'Pesawat',
      color: '#0284c7',
      data: props.trend?.aircraft || [],
      lineWidth: 2.0,
      area: false,
    },
    {
      key: 'ship',
      name: 'Kapal',
      color: '#10b981',
      data: props.trend?.ship || [],
      lineWidth: 2.0,
      area: false,
    },
    {
      key: 'alat',
      name: 'Alkes',
      color: '#f59e0b',
      data: props.trend?.alat || [],
      lineWidth: 1.8,
      area: false,
    },
    {
      key: 'obat',
      name: 'Obat',
      color: '#f43f5e',
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
      smooth: 0.3,
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
      backgroundColor: '#0f172a',
      borderColor: '#334155',
      borderWidth: 1,
      padding: [10, 14],
      textStyle: { color: '#f8fafc', fontSize: 11 },
      extraCssText: 'box-shadow: 0 12px 25px -4px rgba(0, 0, 0, 0.4); border-radius: 10px;',
      axisPointer: {
        type: 'line',
        lineStyle: { color: '#10b981', width: 1.5, type: 'dashed' },
      },
    },
    grid: {
      top: 24,
      right: 18,
      bottom: 22,
      left: 32,
    },
    xAxis: {
      type: 'category',
      data: dates,
      boundaryGap: trendViewMode.value === 'bar',
      axisLine: { lineStyle: { color: '#e2e8f0' } },
      axisTick: { show: false },
      axisLabel: { color: '#64748b', fontSize: 10, fontWeight: 500 },
    },
    yAxis: {
      type: 'value',
      minInterval: 1,
      splitLine: { lineStyle: { color: '#f1f5f9', type: 'solid' } },
      axisLabel: { color: '#64748b', fontSize: 10 },
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
  <Head title="Tactical Command Center — BKK Kelas I Bandung" />

  <HybridNavbar mode="inertia" currentRoute="dashboard">
    <main class="w-full px-3 sm:px-5 lg:px-6 pt-3 pb-16 space-y-3.5 transition-all duration-300">
      
      <!-- ========================================================================= -->
      <!-- TOP COMMAND BAR: CYBER TACTICAL GLASSMORPHISM                             -->
      <!-- ========================================================================= -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-950 via-slate-900 to-emerald-950 text-white p-3.5 sm:p-4 shadow-xl border border-slate-800/80 ring-1 ring-emerald-500/20">
        <!-- Background Cyber Grid Accent -->
        <div class="absolute inset-0 opacity-10 pointer-events-none bg-[radial-gradient(#10b981_1px,transparent_1px)] [background-size:16px_16px]"></div>

        <div class="relative z-10 flex flex-wrap items-center justify-between gap-3">
          <!-- Left: Identity & Live Telemetry -->
          <div class="flex items-center gap-3 min-w-0">
            <div class="relative w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center font-black text-slate-950 shrink-0 shadow-lg shadow-emerald-500/20 border border-emerald-300/40">
              <Radio class="w-5 h-5 text-slate-950 animate-pulse" />
              <span class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-emerald-400 border-2 border-slate-950 animate-ping"></span>
            </div>

            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <h1 class="text-sm sm:text-base font-black tracking-tight text-white flex items-center gap-2 truncate">
                  <span>PUSAT KOMANDO GEOSPASIAL</span>
                  <span class="text-emerald-400 font-mono text-xs font-semibold">BKK KELAS I BANDUNG</span>
                </h1>
                <span class="hidden md:inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/40">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                  OPERASIONAL AKTIF
                </span>
              </div>
              <p class="text-[11px] text-slate-300 flex items-center gap-2 truncate mt-0.5">
                <span>Jaringan 7 Wilker &bull; 4 Moda Karantina Terpadu</span>
                <span class="text-slate-500">|</span>
                <span class="font-mono text-emerald-300">{{ generated_at }}</span>
              </p>
            </div>
          </div>

          <!-- Right: Command Controls & Form Launchers -->
          <div class="flex items-center gap-2 shrink-0 text-xs">
            <button
              type="button"
              @click="refreshData"
              :disabled="isRefreshing"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white/10 hover:bg-emerald-500/20 text-white hover:text-emerald-300 border border-white/15 hover:border-emerald-400/30 transition-all cursor-pointer disabled:opacity-50"
              title="Sinkronisasi data telemetri terkini"
            >
              <RefreshCw class="w-3.5 h-3.5" :class="isRefreshing ? 'animate-spin text-emerald-400' : 'text-emerald-400'" />
              <span class="hidden sm:inline">{{ isRefreshing ? 'Menghubungkan...' : 'Sinkron' }}</span>
            </button>

            <div class="h-5 w-px bg-slate-700/80 hidden sm:block"></div>

            <div class="flex items-center gap-1">
              <Link
                :href="getUrl('/public_aircraft')"
                target="_blank"
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-sky-500/20 hover:bg-sky-500/30 text-sky-300 border border-sky-400/30 transition-all flex items-center gap-1"
                title="Input Inspeksi Pesawat Udara"
              >
                <Plane class="w-3.5 h-3.5" />
                <span class="hidden md:inline">Pesawat</span>
              </Link>
              <Link
                :href="getUrl('/public_sanitasi')"
                target="_blank"
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-400/30 transition-all flex items-center gap-1"
                title="Input Karantina Sanitasi Kapal Laut"
              >
                <Ship class="w-3.5 h-3.5" />
                <span class="hidden md:inline">Kapal</span>
              </Link>
              <Link
                :href="getUrl('/public_alat')"
                target="_blank"
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-400/30 transition-all flex items-center gap-1"
                title="Input Pemeriksaan Alat Kesehatan Haji"
              >
                <Stethoscope class="w-3.5 h-3.5" />
                <span class="hidden md:inline">Alkes</span>
              </Link>
              <Link
                :href="getUrl('/public_obat')"
                target="_blank"
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-400/30 transition-all flex items-center gap-1"
                title="Input Pemakaian Obat P3K Pesawat"
              >
                <Pill class="w-3.5 h-3.5" />
                <span class="hidden md:inline">Obat</span>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- 4 HIGH-TECH INTERACTIVE SERVICE TILES (NEON ACCENTS)                      -->
      <!-- ========================================================================= -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5">
        
        <!-- Tile 1: Sanitasi Pesawat Udara -->
        <div
          @click="selectedModuleKey = selectedModuleKey === 'aircraft' ? 'all' : 'aircraft'"
          class="rounded-xl p-3 transition-all cursor-pointer border relative overflow-hidden group bg-white shadow-2xs hover:shadow-md"
          :class="selectedModuleKey === 'aircraft'
            ? 'border-sky-500 ring-2 ring-sky-500/30 bg-gradient-to-br from-sky-50/50 to-white'
            : 'border-slate-200 hover:border-sky-400'"
        >
          <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-500 to-blue-600" :class="selectedModuleKey === 'aircraft' ? 'opacity-100' : 'opacity-70'"></div>
          <div class="flex items-center justify-between text-xs mt-0.5">
            <span class="font-bold flex items-center gap-1.5 text-slate-800">
              <span class="w-6 h-6 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center shrink-0">
                <Plane class="w-3.5 h-3.5" />
              </span>
              <span>Sanitasi Pesawat</span>
            </span>
            <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-sky-50 text-sky-700 border border-sky-200">
              {{ kpi.compliance_rate }}% Laik
            </span>
          </div>

          <div class="flex items-baseline justify-between mt-2">
            <div>
              <span class="text-xl font-black text-slate-900 tracking-tight font-mono">
                {{ aircraft_stats.total }}
              </span>
              <span class="text-xs text-slate-400 ml-1">dok</span>
            </div>
            <div class="text-[11px] font-semibold text-slate-500">
              <span class="text-emerald-600 font-bold">{{ aircraft_stats.layak }} Layak</span> &bull; 
              <span :class="aircraft_stats.tidak_layak > 0 ? 'text-rose-600 font-bold' : 'text-slate-400'">{{ aircraft_stats.tidak_layak }} Tindakan</span>
            </div>
          </div>
        </div>

        <!-- Tile 2: Sanitasi Kapal Laut -->
        <div
          @click="selectedModuleKey = selectedModuleKey === 'ship' ? 'all' : 'ship'"
          class="rounded-xl p-3 transition-all cursor-pointer border relative overflow-hidden group bg-white shadow-2xs hover:shadow-md"
          :class="selectedModuleKey === 'ship'
            ? 'border-emerald-500 ring-2 ring-emerald-500/30 bg-gradient-to-br from-emerald-50/50 to-white'
            : 'border-slate-200 hover:border-emerald-400'"
        >
          <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-600" :class="selectedModuleKey === 'ship' ? 'opacity-100' : 'opacity-70'"></div>
          <div class="flex items-center justify-between text-xs mt-0.5">
            <span class="font-bold flex items-center gap-1.5 text-slate-800">
              <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                <Ship class="w-3.5 h-3.5" />
              </span>
              <span>Sanitasi Kapal</span>
            </span>
            <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
              IHR 2005
            </span>
          </div>

          <div class="flex items-baseline justify-between mt-2">
            <div>
              <span class="text-xl font-black text-slate-900 tracking-tight font-mono">
                {{ ship_stats.total }}
              </span>
              <span class="text-xs text-slate-400 ml-1">kapal</span>
            </div>
            <div class="text-[11px] font-semibold text-slate-500">
              <span class="text-emerald-700 font-bold">{{ ship_stats.sscec }} SSCEC</span> &bull; 
              <span class="text-amber-700 font-bold">{{ ship_stats.sscc }} SSCC</span>
            </div>
          </div>
        </div>

        <!-- Tile 3: Alkes Haji & Emergensi -->
        <div
          @click="selectedModuleKey = selectedModuleKey === 'alat' ? 'all' : 'alat'"
          class="rounded-xl p-3 transition-all cursor-pointer border relative overflow-hidden group bg-white shadow-2xs hover:shadow-md"
          :class="selectedModuleKey === 'alat'
            ? 'border-amber-500 ring-2 ring-amber-500/30 bg-gradient-to-br from-amber-50/50 to-white'
            : 'border-slate-200 hover:border-amber-400'"
        >
          <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-600" :class="selectedModuleKey === 'alat' ? 'opacity-100' : 'opacity-70'"></div>
          <div class="flex items-center justify-between text-xs mt-0.5">
            <span class="font-bold flex items-center gap-1.5 text-slate-800">
              <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                <Stethoscope class="w-3.5 h-3.5" />
              </span>
              <span>Alkes Haji Pesawat</span>
            </span>
            <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-amber-50 text-amber-800 border border-amber-200">
              Emergensi
            </span>
          </div>

          <div class="flex items-baseline justify-between mt-2">
            <div>
              <span class="text-xl font-black text-slate-900 tracking-tight font-mono">
                {{ medical_stats.alat }}
              </span>
              <span class="text-xs text-slate-400 ml-1">dok</span>
            </div>
            <div class="text-[11px] font-semibold text-slate-500">
              <span class="text-emerald-600 font-bold">{{ kpi.approved }} Selesai</span> &bull; 
              <span class="text-amber-600 font-bold">{{ kpi.pending }} Proses</span>
            </div>
          </div>
        </div>

        <!-- Tile 4: Obat P3K Pesawat -->
        <div
          @click="selectedModuleKey = selectedModuleKey === 'obat' ? 'all' : 'obat'"
          class="rounded-xl p-3 transition-all cursor-pointer border relative overflow-hidden group bg-white shadow-2xs hover:shadow-md"
          :class="selectedModuleKey === 'obat'
            ? 'border-rose-500 ring-2 ring-rose-500/30 bg-gradient-to-br from-rose-50/50 to-white'
            : 'border-slate-200 hover:border-rose-400'"
        >
          <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-pink-600" :class="selectedModuleKey === 'obat' ? 'opacity-100' : 'opacity-70'"></div>
          <div class="flex items-center justify-between text-xs mt-0.5">
            <span class="font-bold flex items-center gap-1.5 text-slate-800">
              <span class="w-6 h-6 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                <Pill class="w-3.5 h-3.5" />
              </span>
              <span>Obat P3K Pesawat</span>
            </span>
            <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-rose-50 text-rose-700 border border-rose-200">
              Total {{ kpi.total }}
            </span>
          </div>

          <div class="flex items-baseline justify-between mt-2">
            <div>
              <span class="text-xl font-black text-slate-900 tracking-tight font-mono">
                {{ medical_stats.obat }}
              </span>
              <span class="text-xs text-slate-400 ml-1">dok</span>
            </div>
            <div class="text-[11px] font-semibold text-slate-500">
              <span class="text-emerald-700 font-bold">{{ kpi.completion_rate }}% Verifikasi</span> &bull; 
              <span class="text-slate-600 font-medium">{{ kpi.today }} Hari Ini</span>
            </div>
          </div>
        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- CENTERPIECE: TACTICAL GEOSPATIAL COMMAND CENTER (THE HERO VIEW)           -->
      <!-- ========================================================================= -->
      <div class="bg-white rounded-2xl border-2 border-slate-900/90 shadow-2xl overflow-hidden ring-4 ring-emerald-500/10">
        
        <!-- Centerpiece Top High-Tech Command Bar -->
        <div class="px-4 py-2.5 bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 text-white flex flex-wrap items-center justify-between gap-3 border-b border-slate-800">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-emerald-500 text-slate-950 flex items-center justify-center font-black text-xs shadow-md shadow-emerald-500/30">
              <Scan class="w-4 h-4" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h2 class="text-sm font-extrabold tracking-tight text-white flex items-center gap-1.5">
                  <span>PETA GEOSPASIAL WILAYAH KERJA JAWA BARAT</span>
                </h2>
                <span class="px-1.5 py-0.2 rounded text-[10px] font-mono font-bold bg-emerald-400/20 text-emerald-300 border border-emerald-400/40">
                  7 NODE TERHUBUNG
                </span>
              </div>
              <p class="text-[11px] text-slate-400">
                Vektor koneksi otomatis menghubungkan Kantor Induk Bandung dengan 6 Pos/Wilayah Kerja
              </p>
            </div>
          </div>

          <!-- Futuristic Controls: Map Layer Switcher & WebGL Actions -->
          <div class="flex flex-wrap items-center gap-2 text-xs">
            <!-- Map Layer Switcher -->
            <div class="inline-flex items-center p-0.5 rounded-lg bg-slate-800/90 border border-slate-700/80 text-[10px] font-bold">
              <button
                type="button"
                @click="switchMapLayer('osm')"
                class="px-2 py-0.5 rounded transition-all cursor-pointer"
                :class="activeMapLayer === 'osm' ? 'bg-emerald-500 text-slate-950 shadow-xs' : 'text-slate-300 hover:text-white'"
              >
                OSM Standar
              </button>
              <button
                type="button"
                @click="switchMapLayer('hot')"
                class="px-2 py-0.5 rounded transition-all cursor-pointer"
                :class="activeMapLayer === 'hot' ? 'bg-emerald-500 text-slate-950 shadow-xs' : 'text-slate-300 hover:text-white'"
              >
                Humanitarian
              </button>
              <button
                type="button"
                @click="switchMapLayer('osmde')"
                class="px-2 py-0.5 rounded transition-all cursor-pointer"
                :class="activeMapLayer === 'osmde' ? 'bg-emerald-500 text-slate-950 shadow-xs' : 'text-slate-300 hover:text-white'"
              >
                Topografi
              </button>
            </div>

            <!-- WebGL Camera Actions -->
            <div class="flex items-center gap-1">
              <button
                type="button"
                @click="resetGlobalView"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white/10 hover:bg-white/20 text-white transition-all cursor-pointer border border-white/10"
                title="Reset tampilan seluruh Jawa Barat"
              >
                <Globe class="w-3 h-3 text-emerald-400" />
                <span>Global</span>
              </button>
              <button
                type="button"
                @click="toggle3DPitch"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold border transition-all cursor-pointer"
                :class="mapPitch > 25
                  ? 'bg-emerald-500 text-slate-950 border-emerald-400 shadow-xs'
                  : 'bg-white/10 hover:bg-white/20 text-white border-white/10'"
              >
                <Box class="w-3 h-3" />
                <span>3D</span>
              </button>
              <button
                type="button"
                @click="toggleOrbit"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold border transition-all cursor-pointer"
                :class="isOrbiting
                  ? 'bg-emerald-500 text-slate-950 border-emerald-400 shadow-xs'
                  : 'bg-white/10 hover:bg-white/20 text-white border-white/10'"
              >
                <RotateCw class="w-3 h-3" :class="isOrbiting ? 'animate-spin' : ''" />
                <span>Orbit</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Fly to Node Ribbon (Interactive Tactical Beacon Strip) -->
        <div class="px-3.5 py-2 bg-slate-900 border-b border-slate-800 text-white flex flex-wrap items-center justify-between gap-2 text-xs">
          <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-[10px] font-mono font-bold tracking-wider uppercase text-emerald-400 mr-1 flex items-center gap-1">
              <Crosshair class="w-3 h-3 text-emerald-400" />
              TARGET:
            </span>
            <button
              v-for="node in bkkMapMarkers"
              :key="node.id"
              type="button"
              @click="selectOffice(node)"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs transition-all cursor-pointer border"
              :class="selectedOffice?.no === node.no
                ? 'bg-emerald-500 text-slate-950 border-emerald-400 font-extrabold shadow-md shadow-emerald-500/30'
                : 'bg-slate-800/90 text-slate-300 border-slate-700/80 hover:bg-slate-700 hover:text-white'"
            >
              <span class="text-[9px] font-mono font-black" :class="selectedOffice?.no === node.no ? 'text-slate-950' : 'text-emerald-400'">
                {{ node.shortCode }}
              </span>
              <span>{{ node.shortTitle.replace('Wilker ', '').replace('Induk ', '') }}</span>
              <span
                v-if="node.total > 0"
                class="text-[9px] font-bold px-1 rounded-full font-mono"
                :class="selectedOffice?.no === node.no ? 'bg-slate-950 text-emerald-400' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30'"
              >
                {{ node.total }}
              </span>
            </button>
          </div>

          <div class="text-[11px] font-mono text-slate-400 hidden xl:flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>KLIK NODE UNTUK INSPEKSI LOKASI PRESISI</span>
          </div>
        </div>

        <!-- Centerpiece Body: Map + Tactical Wilker Telemetry Dock -->
        <div class="grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-200">
          
          <!-- Large WebGL Map Area (8 Cols) -->
          <div class="lg:col-span-8 relative min-w-0 bg-slate-900">
            <BaseMap
              ref="mapComponentRef"
              :initialCenter="[107.55, -6.65]"
              :initialZoom="8.2"
              :initialPitch="25"
              :initialBearing="0"
              styleType="osm"
              :markers="bkkMapMarkers"
              height="530px"
              @selectMarker="selectOffice"
              @mapMove="onCameraMove"
            />

            <!-- Top Left Cyber HUD Overlay -->
            <div class="absolute top-3 left-3 z-10 w-48 rounded-xl bg-slate-950/90 backdrop-blur-md border border-emerald-500/30 px-3 py-2.5 shadow-2xl text-[10px] font-mono text-emerald-300 pointer-events-none ring-1 ring-emerald-500/20">
              <div class="flex items-center justify-between border-b border-emerald-500/30 pb-1 mb-1.5">
                <div class="flex items-center gap-1.5 font-bold text-emerald-400">
                  <Terminal class="w-3 h-3 text-emerald-400" />
                  <span>TELEMETRI GPS</span>
                </div>
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
              </div>
              <div class="space-y-1 text-slate-300">
                <div class="flex justify-between">
                  <span class="text-slate-400">LAT / LNG:</span>
                  <span class="text-white font-bold">{{ mapCenter[1].toFixed(4) }}, {{ mapCenter[0].toFixed(4) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-400">ZOOM / TILT:</span>
                  <span class="text-white font-bold">{{ mapZoom.toFixed(1) }}x / {{ mapPitch }}°</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-400">STATUS LINK:</span>
                  <span class="text-emerald-400 font-bold">TERKONEKSI (7)</span>
                </div>
              </div>
            </div>

            <!-- Bottom Right Target Node Telemetry HUD -->
            <div
              v-if="activeMapNode"
              class="absolute bottom-3 right-3 z-10 rounded-xl bg-slate-950/92 backdrop-blur-md border border-slate-800 shadow-2xl transition-all duration-200 text-white ring-1 ring-emerald-500/30"
              :class="isNodeCardMinimized ? 'w-52 px-3 py-2' : 'w-72 sm:w-80 px-4 py-3'"
            >
              <div class="flex items-center justify-between gap-1.5">
                <div class="flex items-center gap-2.5 min-w-0">
                  <div class="w-7 h-7 rounded-lg bg-emerald-500 text-slate-950 flex items-center justify-center text-xs font-mono font-black shrink-0 shadow-xs">
                    {{ activeMapNode.shortCode }}
                  </div>
                  <div class="min-w-0">
                    <h4 class="text-xs font-black text-white truncate">
                      {{ activeMapNode.title }}
                    </h4>
                    <p v-if="!isNodeCardMinimized" class="text-[10px] text-emerald-400 truncate font-mono">
                      {{ activeMapNode.type }} &bull; {{ activeMapNode.moda }}
                    </p>
                  </div>
                </div>
                <button
                  type="button"
                  @click="isNodeCardMinimized = !isNodeCardMinimized"
                  class="w-5 h-5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center text-xs leading-none cursor-pointer"
                >
                  {{ isNodeCardMinimized ? '+' : '−' }}
                </button>
              </div>

              <template v-if="!isNodeCardMinimized">
                <div class="grid grid-cols-2 gap-2 pt-2.5 mt-2.5 border-t border-slate-800/80 text-[11px] font-mono">
                  <div class="p-1.5 rounded bg-slate-900/80 border border-slate-800">
                    <span class="text-[9px] text-slate-400 block uppercase">Total Dokumen</span>
                    <span class="text-sm font-black text-emerald-400">{{ activeMapNode.total || 0 }}</span>
                  </div>
                  <div class="p-1.5 rounded bg-slate-900/80 border border-slate-800">
                    <span class="text-[9px] text-slate-400 block uppercase">Terakhir Aktif</span>
                    <span class="text-xs font-semibold text-slate-200 truncate block">{{ activeMapNode.last_human || 'Belum ada' }}</span>
                  </div>
                </div>
                <div class="mt-2 pt-2 border-t border-slate-800 text-[10px] text-slate-300 line-clamp-2 leading-tight flex items-start gap-1">
                  <MapPin class="w-3 h-3 text-rose-400 shrink-0 mt-0.5" />
                  <span>{{ activeMapNode.address }}</span>
                </div>
              </template>
            </div>
          </div>

          <!-- Side Cockpit: Tactical Wilker Telemetry Dock (4 Cols) -->
          <div class="lg:col-span-4 p-3.5 flex flex-col justify-between bg-slate-50">
            <div>
              <!-- Cockpit Header: Filter & Search -->
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

                  <span class="text-[10px] font-mono font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">
                    STATUS AKTIF
                  </span>
                </div>

                <div class="relative w-full">
                  <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
                  <input
                    v-model="searchOffice"
                    type="text"
                    placeholder="Cari wilker / alamat..."
                    class="w-full pl-8 pr-2 py-1 rounded-lg bg-white border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-slate-400"
                  />
                </div>
              </div>

              <!-- High-Density Telemetry Card List -->
              <div class="divide-y divide-slate-100 overflow-y-auto max-h-[410px] mt-1 pr-1">
                <div
                  v-for="office in filteredOffices"
                  :key="office.no"
                  @click="selectOffice(office)"
                  class="py-2.5 px-2.5 rounded-xl transition-all cursor-pointer flex flex-col gap-1.5 mb-1"
                  :class="selectedOffice?.no === office.no
                    ? 'bg-white border-2 border-emerald-500 shadow-md ring-2 ring-emerald-500/20'
                    : 'bg-white/70 hover:bg-white border border-slate-200/80'"
                >
                  <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                      <span class="w-5 h-5 rounded-md bg-slate-900 text-white text-[9px] font-mono font-black flex items-center justify-center shrink-0">
                        {{ office.no }}
                      </span>
                      <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-900 truncate" :title="office.name">
                          {{ office.short_name || office.name }}
                        </div>
                        <div class="text-[10px] text-slate-500 truncate flex items-center gap-1">
                          <span>{{ office.moda }}</span>
                          <span>&bull;</span>
                          <span class="text-slate-400">{{ office.zone }}</span>
                        </div>
                      </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                      <div class="text-right">
                        <span class="text-xs font-black text-slate-900 font-mono">{{ office.total || 0 }}</span>
                        <span class="text-[9px] text-slate-400 ml-0.5">dok</span>
                      </div>
                      <button
                        type="button"
                        class="w-6 h-6 rounded-md bg-slate-100 hover:bg-emerald-500 hover:text-slate-950 text-slate-400 flex items-center justify-center transition-colors"
                        title="Arahkan kamera peta ke wilker ini"
                      >
                        <Crosshair class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>

                  <!-- Relative workload bar indicator -->
                  <div class="w-full bg-slate-100 rounded-full h-1 overflow-hidden">
                    <div
                      class="h-full rounded-full transition-all duration-500"
                      :class="selectedOffice?.no === office.no ? 'bg-emerald-500' : 'bg-slate-400'"
                      :style="{ width: `${Math.max(8, ((office.total || 0) / maxOfficeTotal) * 100)}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Cockpit Footer Summary -->
            <div class="pt-2 mt-2 border-t border-slate-200 text-[11px] text-slate-500 flex items-center justify-between font-mono">
              <span>Target: <strong class="text-slate-900 font-sans">{{ selectedOffice?.short_name || '-' }}</strong></span>
              <span class="text-emerald-700 font-bold">{{ selectedOffice?.total || 0 }} Dok</span>
            </div>
          </div>

        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- ANALYTICS COCKPIT: DYNAMIC TREND + OPERATIONAL RISK FOCUS                 -->
      <!-- ========================================================================= -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5">
        
        <!-- 30-Day Trend Multi-Series ECharts (7 Cols) -->
        <div class="lg:col-span-7 rounded-2xl p-4 bg-white border border-slate-200/90 shadow-2xs flex flex-col justify-between">
          <div>
            <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
              <div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-900 tracking-tight flex items-center gap-1.5">
                  <ChartLine class="w-4 h-4 text-emerald-600" />
                  Tren Pengawasan 30 Hari Terakhir
                </h3>
              </div>

              <!-- Line vs Bar Mode -->
              <div class="inline-flex items-center p-0.5 rounded-lg bg-slate-100 border border-slate-200">
                <button
                  type="button"
                  @click="trendViewMode = 'line'"
                  class="p-1 rounded transition-all cursor-pointer"
                  :class="trendViewMode === 'line' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-400 hover:text-slate-700'"
                  title="Grafik Garis"
                >
                  <ChartLine class="w-3.5 h-3.5" />
                </button>
                <button
                  type="button"
                  @click="trendViewMode = 'bar'"
                  class="p-1 rounded transition-all cursor-pointer"
                  :class="trendViewMode === 'bar' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-400 hover:text-slate-700'"
                  title="Grafik Batang"
                >
                  <ChartColumn class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>

            <!-- ECharts Chart -->
            <div class="-mx-2">
              <BaseChart :option="trendChartOption" height="215px" />
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

        <!-- Operational Risk & Treatment Focus (5 Cols) -->
        <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3">
          
          <!-- Card 1: Tindakan Karantina Pesawat -->
          <div class="rounded-2xl p-4 bg-white border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between mb-2">
              <h4 class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                <Plane class="w-3.5 h-3.5 text-sky-600" />
                Tindakan Sanitasi Pesawat
              </h4>
              <span class="text-[11px] font-semibold text-slate-500 font-mono">{{ aircraft_stats.layak }} Layak &bull; {{ aircraft_stats.tidak_layak }} Tindakan</span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs">
              <div
                v-for="act in aircraft_stats.tindakan"
                :key="act.key"
                class="px-2.5 py-1.5 rounded-lg border border-slate-100 bg-slate-50 flex items-center justify-between"
              >
                <span class="text-slate-600 text-[11px]">{{ act.label }}</span>
                <span class="font-bold text-slate-900 font-mono">{{ act.count }}</span>
              </div>
            </div>
          </div>

          <!-- Card 2: Temuan Risiko Kapal (32 Area IHR 2005) -->
          <div class="rounded-2xl p-4 bg-white border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between mb-2">
              <h4 class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                <Ship class="w-3.5 h-3.5 text-emerald-600" />
                Temuan Faktor Risiko Sanitasi Kapal
              </h4>
              <span class="text-[11px] font-semibold text-amber-600 font-mono">{{ ship_stats.ships_with_risk }} / {{ ship_stats.total }} Kapal</span>
            </div>

            <div v-if="ship_stats.top_risk_areas && ship_stats.top_risk_areas.length > 0" class="space-y-1.5 text-xs">
              <div
                v-for="(area, idx) in ship_stats.top_risk_areas"
                :key="idx"
                class="flex items-center justify-between px-2.5 py-1 rounded bg-amber-50/80 border border-amber-200/50 text-[11px]"
              >
                <span class="font-medium text-slate-800 truncate">{{ area.name }}</span>
                <span class="font-bold text-amber-700 shrink-0 ml-2 font-mono">{{ area.count }} temuan</span>
              </div>
            </div>
            <p v-else class="text-[11px] text-slate-500 italic">
              Nihil faktor risiko pada seluruh 32 area sanitasi kapal.
            </p>
          </div>

        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- COMPACT LIVE FEED & AUDIT TRAIL TABLE                                     -->
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
              <span>Dokumen Pemeriksaan Terkini</span>
              <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-emerald-400 text-slate-950 font-black font-mono">
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

                <td class="py-2 px-3 whitespace-nowrap text-slate-500 text-[11px] font-mono">
                  {{ item.date }}
                </td>

                <td class="py-2 px-3 whitespace-nowrap">
                  <div class="flex items-center gap-1.5">
                    <span
                      class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider font-mono"
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
              <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-[10px] shrink-0 font-mono">
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
                <div class="text-[10px] text-slate-500 font-mono">IP: {{ lh.ip_address }} &bull; Perangkat: {{ lh.device }}</div>
              </div>
            </div>
            <div class="text-right">
              <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono">
                {{ lh.status }}
              </span>
              <div class="text-[10px] text-slate-400 mt-0.5 font-mono">{{ lh.login_at }}</div>
            </div>
          </div>
        </div>

      </div>

    </main>
  </HybridNavbar>
</template>
