<script setup>
import { ref, computed, onMounted, onUnmounted, watch, defineAsyncComponent } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useSidebar } from '@/composables/useSidebar';
import { useTheme } from '@/composables/useTheme';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import BaseChart from '../../Components/BaseChart.vue';
import DashboardKpiDeck from './components/DashboardKpiDeck.vue';

const GeospatialDeck = defineAsyncComponent(() => import('./components/GeospatialDeck.vue'));
const SinkarkesSection = defineAsyncComponent(() => import('./components/SinkarkesSection.vue'));
const RadarTelemetryDeck = defineAsyncComponent(() => import('./components/RadarTelemetryDeck.vue'));
const LiveFeedSection = defineAsyncComponent(() => import('./components/LiveFeedSection.vue'));
import {
  Plane,
  Ship,
  Stethoscope,
  Pill,
  ClipboardCheck,
  Clock,
  RefreshCw,
  Globe,
  CalendarDays,
  ChartColumn,
  ChartLine,
  Check,
  Radio,
  Compass,
  Zap,
  BarChart3,
  FileSpreadsheet,
  FileCheck,
  Bug,
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
  sinkarkes: {
    type: Object,
    default: () => ({
      year: 2026,
      wilker: 'all',
      is_consolidated: true,
      wilker_options: [],
      kpi: {},
      matrix: [],
      traffic_chart: {},
      document_chart: [],
      wilker_comparison: [],
      health_chart: {},
      environment_chart: {},
      summary_table: { items: [], monthly_totals: {}, total_year: 0, section_totals: {} },
      wilker_summary: { items: [], monthly_totals: {}, total_year: 0 },
    }),
  },
  metrics: {
    type: Object,
    default: () => ({}),
  },
});

const dashboardViewMode = ref('all'); // 'all' | 'sinkarkes' | 'geospatial'

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
const { isDark } = useTheme();
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

onMounted(() => {
  if (officesList.value.length > 0) {
    selectedOffice.value = officesList.value[0];
  }
});

const geospatialDeckRef = ref(null);

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
        { label: 'Total Layanan', value: `${totalOps} Dokumen`, color: '#00A99D' },
        { label: 'Terakhir Aktif', value: o.last_human || 'Belum ada', color: '#005851' },
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
  const target = bkkMapMarkers.value.find((m) => m.no === office.no);
  if (target) {
    geospatialDeckRef.value?.flyToNode(target);
  }
};

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
  const dark = isDark.value;
  const dates = props.trend?.dates?.length ? props.trend.dates : ['1', '2', '3'];
  const seriesConfig = [
    {
      key: 'total',
      name: 'Total',
      color: dark ? '#5eead4' : '#005851', // High-contrast Teal on Dark / Deep Teal on Light
      data: props.trend?.total || [],
      lineWidth: 2.5,
      area: true,
      areaColor: dark
        ? ['rgba(94, 234, 212, 0.22)', 'rgba(94, 234, 212, 0.0)']
        : ['rgba(0, 169, 157, 0.25)', 'rgba(0, 169, 157, 0.0)'],
    },
    {
      key: 'aircraft',
      name: 'Pesawat',
      color: '#00A99D', // Kemenkes Turquoise Utama
      data: props.trend?.aircraft || [],
      lineWidth: 2.0,
      area: false,
    },
    {
      key: 'ship',
      name: 'Kapal',
      color: dark ? '#38bdf8' : '#007D74', // Distinct Cyan on Dark / Medium Teal on Light
      data: props.trend?.ship || [],
      lineWidth: 2.0,
      area: false,
    },
    {
      key: 'alat',
      name: 'Alkes',
      color: '#7CA622', // Kemenkes Hijau Kuning / Lime Dark
      data: props.trend?.alat || [],
      lineWidth: 1.8,
      area: false,
    },
    {
      key: 'obat',
      name: 'Obat',
      color: '#E11D48', // Harmonized Alert/Rose Accent
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
      backgroundColor: '#003B36',
      borderColor: '#00A99D',
      borderWidth: 1,
      padding: [10, 14],
      textStyle: { color: '#f8fafc', fontSize: 11, fontFamily: 'Segoe UI, sans-serif' },
      extraCssText: 'box-shadow: 0 12px 25px -4px rgba(0, 0, 0, 0.4); border-radius: 10px;',
      axisPointer: {
        type: 'line',
        lineStyle: { color: '#00A99D', width: 1.5, type: 'dashed' },
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
      axisLine: { lineStyle: { color: dark ? '#334155' : '#e2e8f0' } },
      axisTick: { show: false },
      axisLabel: { color: dark ? '#94a3b8' : '#64748b', fontSize: 10, fontWeight: 500 },
    },
    yAxis: {
      type: 'value',
      minInterval: 1,
      splitLine: { lineStyle: { color: dark ? 'rgba(148, 163, 184, 0.12)' : '#f1f5f9', type: 'solid' } },
      axisLabel: { color: dark ? '#94a3b8' : '#64748b', fontSize: 10 },
    },
    series,
  };
});

// (Feed filter & SINKARKES analytics moved into LiveFeedSection.vue & SinkarkesSection.vue)

// ---------------------------------------------------------------------------
// INTERACTIVE TACTICAL RADAR, SONAR SCANNER & LIVE TELEMETRY ENGINE
// ---------------------------------------------------------------------------
const radarSweepSpeed = ref('normal'); // 'normal' | 'fast'
const isSonarPinging = ref(false);
const activeRadarFilter = ref('all'); // 'all' | 'udara' | 'laut'
const liveTimeStr = ref('');
const radarAngle = ref(0);

let telemetryTimer = null;
const updateLiveClock = () => {
  const now = new Date();
  liveTimeStr.value = now.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false,
  }) + ' WIB';
  radarAngle.value = (radarAngle.value + (radarSweepSpeed.value === 'fast' ? 29 : 13)) % 360;
};

onMounted(() => {
  updateLiveClock();
  telemetryTimer = setInterval(updateLiveClock, 1000);
});

onUnmounted(() => {
  if (telemetryTimer) {
    clearInterval(telemetryTimer);
    telemetryTimer = null;
  }
});

const triggerSonarPing = () => {
  isSonarPinging.value = false;
  setTimeout(() => {
    isSonarPinging.value = true;
    setTimeout(() => {
      isSonarPinging.value = false;
    }, 1800);
  }, 20);
};

// Compute real geographic bearing & distance from Induk Bandung (-6.8989, 107.6108) for the Circular Sonar Radar
const radarBlips = computed(() => {
  const hqLat = -6.8989;
  const hqLng = 107.6108;
  const maxRadiusKm = 135; // Max distance in West Java (~Pelabuhanratu / Cirebon)

  return bkkMapMarkers.value
    .filter((node) => {
      if (activeRadarFilter.value === 'all') return true;
      const modaLower = `${node.moda || ''} ${node.type || ''}`.toLowerCase();
      if (activeRadarFilter.value === 'udara') {
        return node.is_hq || modaLower.includes('udara') || modaLower.includes('bandara');
      }
      return node.is_hq || modaLower.includes('laut') || modaLower.includes('pelabuhan');
    })
    .map((node) => {
      if (node.is_hq || node.no === 1) {
        return {
          ...node,
          x: 50,
          y: 50,
          distanceKm: 0,
          bearingDeg: 0,
        };
      }

      const dLatKm = (node.lat - hqLat) * 110.574;
      const dLngKm = (node.lng - hqLng) * (111.32 * Math.cos((hqLat * Math.PI) / 180));
      const distanceKm = Math.round(Math.sqrt(dLatKm * dLatKm + dLngKm * dLngKm));

      // Bearing in degrees (0 = North, 90 = East, 180 = South, 270 = West)
      const rad = Math.atan2(dLngKm, dLatKm);
      const bearingDeg = Math.round(((rad * 180) / Math.PI + 360) % 360);

      // Normalize radius into 14% .. 42% of radar circle radius (center is 50%, 50%)
      const normR = Math.min(42, Math.max(14, (distanceKm / maxRadiusKm) * 40));
      const x = Number((50 + normR * Math.sin(rad)).toFixed(2));
      const y = Number((50 - normR * Math.cos(rad)).toFixed(2));

      return {
        ...node,
        x,
        y,
        distanceKm,
        bearingDeg,
      };
    });
});

// (wilkerRadarOption & modaReadinessRadarOption moved into RadarTelemetryDeck.vue)

</script>

<template>
  <Head title="Tactical Command Center — BKK Kelas I Bandung" />

  <HybridNavbar mode="inertia" currentRoute="dashboard">
    <main class="w-full px-3 sm:px-5 lg:px-6 pt-3 pb-12 space-y-3 transition-all duration-200">
      
      <!-- ========================================================================= -->
      <!-- TOP COMMAND BAR: ENTERPRISE CLINICAL COMPACT HEADER                       -->
      <!-- ========================================================================= -->
      <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-[#003631] via-[#004640] to-[#005851] text-white px-4 py-3 border border-[#00A99D]/30 shadow-sm font-sans">
        <div
          class="pointer-events-none absolute -right-12 -top-12 w-56 h-56 rounded-full opacity-25 blur-2xl"
          style="background: radial-gradient(circle, rgba(0, 169, 157, 0.8), transparent 70%);"
        ></div>
        <div class="relative flex flex-wrap items-center justify-between gap-3">
          <!-- Left: Brand Telemetry Emblem & Status -->
          <div class="flex items-center gap-3 min-w-0">
            <button
              type="button"
              @click="triggerSonarPing"
              aria-label="Pancarkan sinyal ping sonar radar"
              title="Klik untuk memancarkan sinyal Ping Sonar Radar"
              class="relative w-9 h-9 rounded-lg bg-[#002B27] hover:bg-[#00201D] flex items-center justify-center shrink-0 border border-[#00A99D]/50 shadow-[0_0_12px_rgba(0,169,157,0.2)] cursor-pointer transition-all hover:scale-105"
            >
              <Radio class="w-4 h-4 text-[#99CC33]" />
            </button>

            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-sm sm:text-[15px] font-bold tracking-tight text-white truncate">
                  Pusat Komando Surveilans &amp; Geospasial
                </h1>
                <span class="hidden md:inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-mono font-semibold bg-[#002B27]/85 text-[#99DFD6] border border-[#00A99D]/35">
                  <span class="relative flex h-1.5 w-1.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#99CC33] opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-[#99CC33]"></span>
                  </span>
                  <span>{{ officesList.length || 7 }} WILKER AKTIF</span>
                </span>
                <span class="hidden xl:inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-mono font-medium bg-[#002B27]/70 text-slate-200 border border-white/10 tabular-nums">
                  <Compass class="w-3 h-3 text-[#99CC33]" />
                  <span>AZM {{ String(radarAngle).padStart(3, '0') }}&deg;</span>
                </span>
              </div>
              <p class="text-[11px] text-slate-200/90 flex flex-wrap items-center gap-2 truncate mt-0.5">
                <span>BKK Kelas I Bandung &bull; 4 Moda Karantina Terpadu</span>
                <span class="text-white/30">|</span>
                <span class="font-mono text-[#99DFD6] flex items-center gap-1 tabular-nums">
                  <Clock class="w-3 h-3 text-[#99CC33]" />
                  {{ liveTimeStr || generated_at }}
                </span>
              </p>
            </div>
          </div>

          <!-- Right: View Switcher & Sync -->
          <div class="flex flex-wrap items-center gap-1.5 shrink-0 text-xs">
            <!-- Unified View Mode Switcher -->
            <div class="inline-flex items-center p-0.5 rounded-lg bg-[#002B27]/90 border border-white/15 text-[11px] font-semibold">
              <button
                type="button"
                @click="dashboardViewMode = 'all'"
                class="px-2.5 py-1 rounded-md transition-all cursor-pointer"
                :class="dashboardViewMode === 'all' ? 'bg-[#00A99D] text-white shadow-[0_0_12px_rgba(0,169,157,0.35)]' : 'text-slate-300 hover:text-white'"
              >
                Semua
              </button>
              <button
                type="button"
                @click="dashboardViewMode = 'sinkarkes'"
                class="px-2.5 py-1 rounded-md transition-all cursor-pointer flex items-center gap-1"
                :class="dashboardViewMode === 'sinkarkes' ? 'bg-[#00A99D] text-white shadow-[0_0_12px_rgba(0,169,157,0.35)]' : 'text-slate-300 hover:text-white'"
              >
                <BarChart3 class="w-3 h-3 text-[#99CC33]" />
                <span>SINKARKES</span>
              </button>
              <button
                type="button"
                @click="dashboardViewMode = 'geospatial'"
                class="px-2.5 py-1 rounded-md transition-all cursor-pointer hidden sm:inline-flex items-center gap-1"
                :class="dashboardViewMode === 'geospatial' ? 'bg-[#00A99D] text-white shadow-[0_0_12px_rgba(0,169,157,0.35)]' : 'text-slate-300 hover:text-white'"
              >
                <Globe class="w-3 h-3 text-[#99DFD6]" />
                <span>Peta</span>
              </button>
            </div>

            <button
              type="button"
              @click="triggerSonarPing"
              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold bg-[#99CC33]/15 hover:bg-[#99CC33]/25 text-[#d9f99d] border border-[#99CC33]/40 transition-all cursor-pointer"
              title="Pancarkan Gelombang Sonar Radar"
            >
              <Zap class="w-3.5 h-3.5" :class="isSonarPinging ? 'animate-pulse' : ''" />
              <span class="hidden sm:inline">Ping</span>
            </button>

            <button
              type="button"
              @click="refreshData"
              :disabled="isRefreshing"
              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/15 transition-colors cursor-pointer disabled:opacity-50"
              title="Sinkronisasi data telemetri terkini"
            >
              <RefreshCw class="w-3.5 h-3.5" :class="isRefreshing ? 'animate-spin text-[#99CC33]' : 'text-[#99DFD6]'" />
              <span class="hidden sm:inline">{{ isRefreshing ? 'Memuat...' : 'Sinkron' }}</span>
            </button>
          </div>
        </div>

        <!-- Quick Operational Module Launcher + Readiness Telemetry -->
        <div class="relative mt-2.5 pt-2 border-t border-white/15 flex flex-wrap items-center justify-between gap-2 text-[11px]">
          <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-[10px] font-mono uppercase tracking-wider text-[#99DFD6]/80 mr-1 hidden sm:inline">Akses Cepat:</span>
            <Link
              :href="getUrl('/travel-consent')"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/10 hover:bg-white/20 text-white border border-white/15 transition-colors font-medium"
            >
              <FileCheck class="w-3 h-3 text-[#99CC33]" />
              <span>Travel Consent</span>
            </Link>
            <Link
              :href="getUrl('/ship-surveillance')"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/10 hover:bg-white/20 text-white border border-white/15 transition-colors font-medium"
            >
              <Ship class="w-3 h-3 text-[#99DFD6]" />
              <span>TANGKAP Kapal</span>
            </Link>
            <Link
              :href="getUrl('/sinkarkes-daily')"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/10 hover:bg-white/20 text-white border border-white/15 transition-colors font-medium"
            >
              <CalendarDays class="w-3 h-3 text-[#99CC33]" />
              <span>Rekap Harian</span>
            </Link>
            <Link
              :href="getUrl('/sinkarkes')"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/10 hover:bg-white/20 text-white border border-white/15 transition-colors font-medium"
            >
              <FileSpreadsheet class="w-3 h-3 text-[#99DFD6]" />
              <span>Form SINKARKES</span>
            </Link>
            <Link
              :href="getUrl('/sanitasi-lingkungan')"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/10 hover:bg-white/20 text-white border border-white/15 transition-colors font-medium"
            >
              <ClipboardCheck class="w-3 h-3 text-[#99CC33]" />
              <span>Sanitasi Lingkungan</span>
            </Link>
            <Link
              :href="getUrl('/sivera')"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/10 hover:bg-white/20 text-white border border-white/15 transition-colors font-medium"
            >
              <Bug class="w-3 h-3 text-rose-300" />
              <span>SIVERA</span>
            </Link>
          </div>

          <div class="hidden xl:flex items-center gap-2 font-mono text-[10px] tabular-nums">
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-[#002B27]/80 border border-white/10 text-[#99DFD6]">
              <span class="text-white/60">HARI INI</span>
              <strong class="text-white">{{ kpi.today || 0 }}</strong>
            </span>
            <span
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded border"
              :class="(kpi.pending || 0) > 0
                ? 'bg-amber-500/15 border-amber-400/40 text-amber-200'
                : 'bg-[#002B27]/80 border-white/10 text-emerald-200'"
            >
              <span class="opacity-75">ANTRE</span>
              <strong>{{ kpi.pending || 0 }}</strong>
            </span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-[#99CC33]/15 border border-[#99CC33]/35 text-[#d9f99d]">
              <span class="opacity-75">LAIK</span>
              <strong>{{ kpi.compliance_rate || 0 }}%</strong>
            </span>
          </div>
        </div>
      </div>

      <!-- 4 CLINICAL SERVICE KPI CARDS -->
      <DashboardKpiDeck
        v-if="dashboardViewMode !== 'sinkarkes'"
        :selected-module-key="selectedModuleKey"
        :kpi="kpi"
        :aircraft-stats="aircraft_stats"
        :ship-stats="ship_stats"
        :medical-stats="medical_stats"
        @toggle-module="(key) => (selectedModuleKey = selectedModuleKey === key ? 'all' : key)"
      />

      <!-- ========================================================================= -->
      <!-- CENTERPIECE: GEOSPATIAL COMMAND CENTER (Lazy Component + Async MapLibre)  -->
      <!-- ========================================================================= -->
      <GeospatialDeck
        v-if="dashboardViewMode !== 'sinkarkes'"
        ref="geospatialDeckRef"
        :bkk-map-markers="bkkMapMarkers"
        :selected-office="selectedOffice"
        :active-map-node="activeMapNode"
        :radar-blips="radarBlips"
        :radar-angle="radarAngle"
        :sinkarkes="sinkarkes"
        :max-office-total="maxOfficeTotal"
        v-model:radar-sweep-speed="radarSweepSpeed"
        v-model:active-radar-filter="activeRadarFilter"
        :is-sonar-pinging="isSonarPinging"
        @select-office="selectOffice"
        @trigger-ping="triggerSonarPing"
      />

      <!-- ========================================================================= -->
      <!-- MULTI-RADAR & WILKER TELEMETRY DECK (Lazy Component)                      -->
      <!-- ========================================================================= -->
      <RadarTelemetryDeck
        :offices-list="officesList"
        :filtered-offices="filteredOffices"
        :selected-office="selectedOffice"
        :max-office-total="maxOfficeTotal"
        v-model:active-office-filter="activeOfficeFilter"
        v-model:search-office="searchOffice"
        :sinkarkes="sinkarkes"
        :kpi="kpi"
        :ship-stats="ship_stats"
        :medical-stats="medical_stats"
        @select-office="selectOffice"
      />

      <!-- ========================================================================= -->
      <!-- INTEGRATED SINKARKES EXECUTIVE SUMMARY, CHARTS & TABLES (Lazy Component)  -->
      <!-- ========================================================================= -->
      <SinkarkesSection v-if="dashboardViewMode !== 'geospatial'" :sinkarkes="sinkarkes" />

      <!-- ========================================================================= -->
      <!-- ANALYTICS COCKPIT: DYNAMIC TREND + OPERATIONAL RISK FOCUS                 -->
      <!-- ========================================================================= -->
      <div v-if="dashboardViewMode !== 'sinkarkes'" class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 font-sans">
        
        <!-- 30-Day Trend Multi-Series ECharts (7 Cols) -->
        <div class="lg:col-span-7 clinical-surface p-4 flex flex-col justify-between">
          <div>
            <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
              <div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-1.5">
                  <ChartLine class="w-4 h-4 text-[#00A99D]" />
                  Tren Pengawasan 30 Hari Terakhir
                </h3>
              </div>

              <!-- Line vs Bar Mode -->
              <div class="inline-flex items-center p-0.5 rounded-lg bg-slate-100 dark:bg-[#16202a] border border-slate-200 dark:border-slate-700">
                <button
                  type="button"
                  @click="trendViewMode = 'line'"
                  class="p-1 rounded transition-all cursor-pointer"
                  :class="trendViewMode === 'line' ? 'bg-[#005851] text-white shadow-2xs font-bold' : 'text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                  aria-label="Tampilkan grafik garis"
                  title="Grafik Garis"
                >
                  <ChartLine class="w-3.5 h-3.5" />
                </button>
                <button
                  type="button"
                  @click="trendViewMode = 'bar'"
                  class="p-1 rounded transition-all cursor-pointer"
                  :class="trendViewMode === 'bar' ? 'bg-[#005851] text-white shadow-2xs font-bold' : 'text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                  aria-label="Tampilkan grafik batang"
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
          <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center gap-3 text-[11px]">
            <button type="button" @click="toggleSeries('total')" class="inline-flex items-center gap-1 cursor-pointer">
              <span class="w-3 h-3 rounded flex items-center justify-center text-white" :class="seriesVisibility.total ? 'bg-[#005851]' : 'bg-slate-200 dark:bg-slate-700'">
                <Check class="w-2 h-2 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.total ? 'font-bold text-[#005851] dark:text-[#5eead4]' : 'text-slate-400 dark:text-slate-500'">Total ({{ kpi.total }})</span>
            </button>

            <button type="button" @click="toggleSeries('aircraft')" class="inline-flex items-center gap-1 cursor-pointer">
              <span class="w-3 h-3 rounded flex items-center justify-center text-white" :class="seriesVisibility.aircraft ? 'bg-[#00A99D]' : 'bg-slate-200 dark:bg-slate-700'">
                <Check class="w-2 h-2 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.aircraft ? 'font-bold text-[#005851] dark:text-[#5eead4]' : 'text-slate-400 dark:text-slate-500'">Pesawat ({{ aircraft_stats.total }})</span>
            </button>

            <button type="button" @click="toggleSeries('ship')" class="inline-flex items-center gap-1 cursor-pointer">
              <span class="w-3 h-3 rounded flex items-center justify-center text-white" :class="seriesVisibility.ship ? 'bg-[#007D74]' : 'bg-slate-200 dark:bg-slate-700'">
                <Check class="w-2 h-2 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.ship ? 'font-bold text-[#005851] dark:text-[#5eead4]' : 'text-slate-400 dark:text-slate-500'">Kapal ({{ ship_stats.total }})</span>
            </button>

            <button type="button" @click="toggleSeries('alat')" class="inline-flex items-center gap-1 cursor-pointer">
              <span class="w-3 h-3 rounded flex items-center justify-center text-white" :class="seriesVisibility.alat ? 'bg-[#7CA622]' : 'bg-slate-200 dark:bg-slate-700'">
                <Check class="w-2 h-2 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.alat ? 'font-bold text-[#557A18] dark:text-[#bef264]' : 'text-slate-400 dark:text-slate-500'">Alkes ({{ medical_stats.alat }})</span>
            </button>

            <button type="button" @click="toggleSeries('obat')" class="inline-flex items-center gap-1 cursor-pointer">
              <span class="w-3 h-3 rounded flex items-center justify-center text-white" :class="seriesVisibility.obat ? 'bg-[#E11D48]' : 'bg-slate-200 dark:bg-slate-700'">
                <Check class="w-2 h-2 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.obat ? 'font-bold text-slate-800 dark:text-rose-300' : 'text-slate-400 dark:text-slate-500'">Obat ({{ medical_stats.obat }})</span>
            </button>
          </div>
        </div>

        <!-- Operational Risk & Treatment Focus (5 Cols) -->
        <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3">
          
          <!-- Card 1: Tindakan Karantina Pesawat -->
          <div class="clinical-surface p-4">
            <div class="flex items-center justify-between mb-2">
              <h4 class="text-xs font-bold text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
                <Plane class="w-3.5 h-3.5 text-[#00A99D]" />
                Tindakan Sanitasi Pesawat
              </h4>
              <span class="text-[11px] font-semibold text-[#005851] dark:text-[#5eead4] font-mono">{{ aircraft_stats.layak }} Layak &bull; {{ aircraft_stats.tidak_layak }} Tindakan</span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs">
              <div
                v-for="act in aircraft_stats.tindakan"
                :key="act.key"
                class="px-2.5 py-1.5 rounded-lg border border-[#E6F7F5] dark:border-[#00A99D]/30 bg-[#E6F7F5]/40 dark:bg-[#00A99D]/12 flex items-center justify-between"
              >
                <span class="text-slate-700 dark:text-slate-200 text-[11px] font-medium">{{ act.label }}</span>
                <span class="font-bold text-[#005851] dark:text-[#5eead4] font-mono">{{ act.count }}</span>
              </div>
            </div>
          </div>

          <!-- Card 2: Temuan Risiko Kapal (32 Area IHR 2005) -->
          <div class="clinical-surface p-4">
            <div class="flex items-center justify-between mb-2">
              <h4 class="text-xs font-bold text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
                <Ship class="w-3.5 h-3.5 text-[#005851] dark:text-[#5eead4]" />
                Temuan Faktor Risiko Sanitasi Kapal
              </h4>
              <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-300 font-mono">{{ ship_stats.ships_with_risk }} / {{ ship_stats.total }} Kapal</span>
            </div>

            <div v-if="ship_stats.top_risk_areas && ship_stats.top_risk_areas.length > 0" class="space-y-1.5 text-xs">
              <div
                v-for="(area, idx) in ship_stats.top_risk_areas"
                :key="idx"
                class="flex items-center justify-between px-2.5 py-1.5 rounded-lg bg-amber-50/80 dark:bg-amber-500/12 border border-amber-200/50 dark:border-amber-500/30 text-[11px]"
              >
                <span class="font-medium text-slate-800 dark:text-slate-100 truncate">{{ area.name }}</span>
                <span class="font-bold text-amber-700 dark:text-amber-300 shrink-0 ml-2 font-mono">{{ area.count }} temuan</span>
              </div>
            </div>
            <p v-else class="text-[11px] text-slate-500 dark:text-slate-400 italic">
              Nihil faktor risiko pada seluruh 32 area sanitasi kapal.
            </p>
          </div>

        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- COMPACT LIVE FEED & AUDIT TRAIL TABLE (Lazy Component)                    -->
      <!-- ========================================================================= -->
      <LiveFeedSection
        v-if="dashboardViewMode !== 'sinkarkes'"
        :recent-inspections="recent_inspections"
        :recent-activities="recent_activities"
        :recent-logins="recent_logins"
        :selected-module-key="selectedModuleKey"
        :module-icon-map="moduleIconMap"
      />

    </main>
  </HybridNavbar>
</template>

<style scoped>
@keyframes shimmer-glide {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(200%);
  }
}

.shimmer-bar {
  position: relative;
  overflow: hidden;
}

.shimmer-bar::after {
  content: '';
  position: absolute;
  inset: 0;
  width: 50%;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.45),
    transparent
  );
  animation: shimmer-glide 2.6s ease-in-out infinite;
}
</style>
