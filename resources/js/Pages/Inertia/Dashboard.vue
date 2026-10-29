<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useSidebar } from '@/composables/useSidebar';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import BaseChart from '../../Components/BaseChart.vue';
import BaseMap from '../../Components/BaseMap.vue';
import {
  Users,
  ShieldCheck,
  History,
  Database,
  Radio,
  Activity,
  Layers,
  ArrowRight,
  Download,
  User as UserIcon,
  CheckCircle2,
  Clock,
  Plane,
  MapPin,
  Compass,
  Crosshair,
  Building2,
  Anchor,
  Navigation,
  Eye,
  FileSpreadsheet,
  Sparkles,
  ExternalLink,
  Search,
  RefreshCw,
  Globe,
  Box,
  RotateCw,
  Calendar,
  Settings2,
  MoreVertical,
  TrendingUp,
  BarChart3,
  LineChart,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  Check,
} from 'lucide-vue-next';

const props = defineProps({
  bkk_offices: {
    type: Array,
    default: () => [],
  },
  metrics: {
    type: Object,
    required: true,
  },
  recent_activities: {
    type: Array,
    default: () => [],
  },
  recent_logins: {
    type: Array,
    default: () => [],
  },
  chart_data: {
    type: Object,
    default: () => ({ dates: [], counts: [], inspections: [] }),
  },
  system: {
    type: Object,
    required: true,
  },
});

const getUrl = (path) => {
  if (typeof window !== 'undefined' && window.location.pathname.includes('/hybridlaravel/public')) {
    return `/hybridlaravel/public${path}`;
  }
  return path;
};

// Default fallback offices if prop is empty (matches the 7 offices of BKK Kelas I Bandung)
const defaultOffices = [
  {
    no: 1,
    code: 'BKK-BDG-01',
    name: 'Balai Kekarantinaan Kesehatan Kelas I Bandung (Induk)',
    short_name: 'Induk BKK Bandung',
    zone: 'Kota Bandung',
    type: 'Kantor Induk',
    moda: 'Pusat Komando',
    address: 'Jl. Cikapayang No.5, Tamansari, Kec. Bandung Wetan, Kota Bandung, Jawa Barat 40116',
    lat: -6.8989,
    lng: 107.6108,
    is_hq: true,
    status: 'ACTIVE',
    personnel: 68,
    readiness: 98,
    daily_ops: 412,
  },
  {
    no: 2,
    code: 'POS-BDO-02',
    name: 'POS Bandara Husen Sastranegara',
    short_name: 'POS Bandara Husen',
    zone: 'Kota Bandung',
    type: 'Pos Bandara',
    moda: 'Udara',
    address: 'Jl. Pajajaran Dalam, Husen Sastranegara, Kec. Cicendo, Kota Bandung, Jawa Barat 40174',
    lat: -6.9006,
    lng: 107.5763,
    is_hq: false,
    status: 'ACTIVE',
    personnel: 24,
    readiness: 96,
    daily_ops: 228,
  },
  {
    no: 3,
    code: 'WLK-CBN-03',
    name: 'Wilayah Kerja Cirebon',
    short_name: 'Wilker Cirebon',
    zone: 'Kota Cirebon',
    type: 'Wilayah Kerja',
    moda: 'Pelabuhan Laut',
    address: 'Jl. Terusan Jl. Sekar Kemuning II No. 7, Karyamulya, Kec. Kesambi, Kota Cirebon, Jawa Barat 45131',
    lat: -6.7320,
    lng: 108.5523,
    is_hq: false,
    status: 'ACTIVE',
    personnel: 22,
    readiness: 97,
    daily_ops: 215,
  },
  {
    no: 4,
    code: 'WLK-IDM-04',
    name: 'Wilayah Kerja Indramayu',
    short_name: 'Wilker Indramayu',
    zone: 'Kab. Indramayu',
    type: 'Wilayah Kerja',
    moda: 'Pelabuhan Laut & Migas',
    address: 'Jl. Jend. Sudirman, Lemahmekar, Kec. Indramayu, Kabupaten Indramayu, Jawa Barat 45213',
    lat: -6.3276,
    lng: 108.3249,
    is_hq: false,
    status: 'ACTIVE',
    personnel: 18,
    readiness: 95,
    daily_ops: 145,
  },
  {
    no: 5,
    code: 'WLK-KJT-05',
    name: 'Wilayah Kerja Kertajati',
    short_name: 'Wilker Kertajati',
    zone: 'Kab. Majalengka',
    type: 'Wilayah Kerja',
    moda: 'Bandara Internasional',
    address: 'Kertajati, Majalengka Regency, West Java 45457',
    lat: -6.6561,
    lng: 108.1578,
    is_hq: false,
    status: 'ACTIVE',
    personnel: 30,
    readiness: 99,
    daily_ops: 170,
  },
  {
    no: 6,
    code: 'WLK-PTB-06',
    name: 'Wilayah Kerja Patimban',
    short_name: 'Wilker Patimban',
    zone: 'Kab. Subang',
    type: 'Wilayah Kerja',
    moda: 'Pelabuhan Samudera',
    address: 'Pelabuhan Patimban, Pusakanagara, Subang, Jawa Barat 41255',
    lat: -6.2415,
    lng: 107.9048,
    is_hq: false,
    status: 'ACTIVE',
    personnel: 26,
    readiness: 97,
    daily_ops: 155,
  },
  {
    no: 7,
    code: 'WLK-PLR-07',
    name: 'Wilayah Kerja Pelabuharatu',
    short_name: 'Wilker Pelabuharatu',
    zone: 'Kab. Sukabumi',
    type: 'Wilayah Kerja',
    moda: 'Pelabuhan Pesisir Selatan',
    address: 'Jl. Sirgalih, Pelabuhanratu, Kec. Pelabuhanratu, Kabupaten Sukabumi, Jawa Barat 43364',
    lat: -6.9875,
    lng: 106.5514,
    is_hq: false,
    status: 'ACTIVE',
    personnel: 16,
    readiness: 95,
    daily_ops: 95,
  },
];

const offices = computed(() => {
  if (props.bkk_offices && props.bkk_offices.length > 0) {
    return props.bkk_offices.map((o, idx) => ({
      ...defaultOffices[idx % defaultOffices.length],
      ...o,
    }));
  }
  return defaultOffices;
});

const selectedOffice = ref(null);
const searchOffice = ref('');
const activeTabFilter = ref('all'); // 'all' | 'udara' | 'laut'
const checkedRows = ref([1]);

const { isCollapsed } = useSidebar();

// Smoothly notify map and charts to resize during sidebar collapse/expand animation
watch(isCollapsed, () => {
  [50, 120, 200, 280, 350].forEach((delay) => {
    setTimeout(() => {
      window.dispatchEvent(new Event('resize'));
    }, delay);
  });
});

onMounted(() => {
  selectedOffice.value = offices.value[0];
});

// MapLibre WebGL Map State & Controls (OpenStreetMap locked)
const mapComponentRef = ref(null);
const mapStyle = ref('osm');
const mapCenter = ref([107.6108, -6.8989]);
const mapZoom = ref(8.3);
const mapPitch = ref(35);
const mapBearing = ref(0);
const isOrbiting = ref(false);
const isNodeCardMinimized = ref(false);
let orbitTimer = null;

// Map markers computed from the 7 BKK Kelas I Bandung offices
const bkkMapMarkers = computed(() => {
  const shortCodes = ['BDG', 'BDO', 'CBN', 'IDM', 'KJT', 'PTB', 'PLR'];
  const latencies = ['4ms', '5ms', '8ms', '9ms', '6ms', '7ms', '11ms'];
  const throughputs = ['1428 req/s', '980 req/s', '952 req/s', '840 req/s', '1120 req/s', '1045 req/s', '760 req/s'];
  const badgeStyles = [
    'bg-slate-900 text-white',
    'bg-emerald-600 text-white',
    'bg-blue-600 text-white',
    'bg-amber-500 text-slate-950',
    'bg-violet-600 text-white',
    'bg-teal-600 text-white',
    'bg-rose-600 text-white',
  ];

  return offices.value.map((o, idx) => ({
    ...o,
    id: `bkk-node-${o.no}`,
    flag: shortCodes[idx] || `W${o.no}`,
    short_code: shortCodes[idx] || `W${o.no}`,
    shortCode: shortCodes[idx] || `W${o.no}`,
    title: o.name,
    shortTitle: o.short_name,
    lng: Number(o.lng),
    lat: Number(o.lat),
    coordinates: [Number(o.lng), Number(o.lat)],
    location: o.address,
    color: o.is_hq ? '#10b981' : '#059669',
    badgeClass: badgeStyles[idx % badgeStyles.length],
    status: 'ONLINE',
    role: `${o.type} (${o.moda})`,
    ping: latencies[idx] || '6ms',
    latency: latencies[idx] || '6ms',
    connections: parseInt(throughputs[idx], 10) || 890,
    throughput: throughputs[idx] || '890 req/s',
    zoom: o.is_hq ? 12.2 : 11.8,
    pitch: 45,
    bearing: 0,
  }));
});

const activeMapNode = computed(() => {
  const currentNo = selectedOffice.value?.no || 1;
  return bkkMapMarkers.value.find((m) => m.no === currentNo) || bkkMapMarkers.value[0];
});

const selectOffice = (office) => {
  selectedOffice.value = office;
  isNodeCardMinimized.value = false;
  const targetMarker = bkkMapMarkers.value.find((m) => m.no === office.no);
  if (targetMarker && mapComponentRef.value) {
    mapComponentRef.value.flyTo(
      [targetMarker.lng, targetMarker.lat],
      targetMarker.zoom,
      targetMarker.pitch,
      targetMarker.bearing
    );
  }
};

const onMarkerClick = (marker) => {
  selectedOffice.value = marker;
  isNodeCardMinimized.value = false;
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

// Filtered offices for the right-side executive directory table
const filteredOffices = computed(() => {
  let list = bkkMapMarkers.value;
  if (activeTabFilter.value === 'udara') {
    list = list.filter((o) => o.is_hq || o.moda.toLowerCase().includes('udara') || o.moda.toLowerCase().includes('bandara'));
  } else if (activeTabFilter.value === 'laut') {
    list = list.filter((o) => o.moda.toLowerCase().includes('laut') || o.moda.toLowerCase().includes('pelabuhan'));
  }

  const q = searchOffice.value.trim().toLowerCase();
  if (!q) return list;
  return list.filter(
    (o) =>
      o.name.toLowerCase().includes(q) ||
      o.address.toLowerCase().includes(q) ||
      o.zone.toLowerCase().includes(q) ||
      o.shortCode.toLowerCase().includes(q)
  );
});

const toggleRowCheck = (no) => {
  if (checkedRows.value.includes(no)) {
    checkedRows.value = checkedRows.value.filter((n) => n !== no);
  } else {
    checkedRows.value.push(no);
  }
};

// Top-left card ("Kesiapsiagaan & Operasional BKK") 24 mini-bars like "Deliveries -> Fleet Utilization"
const utilizationBars = [
  88, 92, 95, 90, 94, 96, 91, 89, 84, 86, 93, 95,
  97, 94, 96, 98, 95, 93, 97, 96, 99, 97, 95, 98,
];

// Bottom-Left Chart ("Tren Pengawasan & Inspeksi Sanitasi" — styled like "Revenue and costs" in Image 1)
const showLineInspeksi = ref(true);
const showLineVektor = ref(true);
const showLineLaik = ref(true);
const comparePrevPeriod = ref(false);
const trendViewMode = ref('line'); // 'line' | 'bar'

const trendChartOption = computed(() => {
  const dates =
    props.chart_data?.dates?.length > 0
      ? props.chart_data.dates
      : ['16 Okt', '17 Okt', '18 Okt', '19 Okt', '20 Okt', '21 Okt', '22 Okt', '23 Okt', '24 Okt', '25 Okt', '26 Okt', '27 Okt', '28 Okt', '29 Okt'];

  const totalInspeksi = [95, 102, 118, 104, 109, 114, 121, 115, 118, 124, 116, 128, 122, 134];
  const laikSanitasi = [89, 91, 106, 76, 98, 96, 91, 97, 95, 86, 102, 79, 98, 108];
  const tindakanVektor = [75, 79, 84, 88, 96, 78, 82, 91, 84, 98, 86, 91, 83, 95];

  const series = [];

  if (showLineInspeksi.value) {
    series.push({
      name: 'Inspeksi Sanitasi',
      type: trendViewMode.value,
      smooth: 0.25,
      symbol: 'circle',
      symbolSize: 6,
      showSymbol: false,
      data: totalInspeksi,
      itemStyle: { color: '#10b981', borderRadius: [3, 3, 0, 0] },
      lineStyle: { width: 2.2, color: '#10b981' },
      areaStyle:
        trendViewMode.value === 'line'
          ? {
              color: {
                type: 'linear',
                x: 0,
                y: 0,
                x2: 0,
                y2: 1,
                colorStops: [
                  { offset: 0, color: 'rgba(16, 185, 129, 0.16)' },
                  { offset: 1, color: 'rgba(16, 185, 129, 0.0)' },
                ],
              },
            }
          : undefined,
    });
  }

  if (showLineLaik.value) {
    series.push({
      name: 'Laik Sanitasi',
      type: trendViewMode.value,
      smooth: 0.25,
      symbol: 'circle',
      symbolSize: 6,
      showSymbol: false,
      data: laikSanitasi,
      itemStyle: { color: '#0f172a', borderRadius: [3, 3, 0, 0] },
      lineStyle: { width: 1.8, color: '#0f172a' },
    });
  }

  if (showLineVektor.value) {
    series.push({
      name: 'Pengendalian Vektor',
      type: trendViewMode.value,
      smooth: 0.25,
      symbol: 'circle',
      symbolSize: 6,
      showSymbol: false,
      data: tindakanVektor,
      itemStyle: { color: '#e11d48', borderRadius: [3, 3, 0, 0] },
      lineStyle: { width: 1.8, color: '#e11d48' },
      areaStyle:
        trendViewMode.value === 'line'
          ? {
              color: {
                type: 'linear',
                x: 0,
                y: 0,
                x2: 0,
                y2: 1,
                colorStops: [
                  { offset: 0, color: 'rgba(225, 29, 72, 0.08)' },
                  { offset: 1, color: 'rgba(225, 29, 72, 0.0)' },
                ],
              },
            }
          : undefined,
    });
  }

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
      top: 26,
      right: 32,
      bottom: 24,
      left: 36,
    },
    xAxis: {
      type: 'category',
      data: dates,
      boundaryGap: trendViewMode.value === 'bar',
      axisLine: { lineStyle: { color: '#e2e8f0' } },
      axisTick: { show: false },
      axisLabel: { color: '#94a3b8', fontSize: 10 },
    },
    yAxis: [
      {
        type: 'value',
        name: 'Inspeksi & Laik Sanitasi',
        nameTextStyle: { color: '#94a3b8', fontSize: 10, align: 'left', padding: [0, 0, 6, -20] },
        splitLine: { lineStyle: { color: '#f1f5f9', type: 'solid' } },
        axisLabel: { color: '#94a3b8', fontSize: 10 },
      },
      {
        type: 'value',
        name: 'Tindakan',
        nameTextStyle: { color: '#94a3b8', fontSize: 10, align: 'right', padding: [0, -10, 6, 0] },
        splitLine: { show: false },
        axisLabel: { color: '#94a3b8', fontSize: 10 },
      },
    ],
    series,
  };
});

// Bottom-Center ("Volume Harian & Kepatuhan" — styled like "Balance and costs" in Image 1)
const dailyBalanceBars = [
  { emerald: 52, rose: 28, dot: 68 },
  { emerald: 48, rose: 34, dot: 64 },
  { emerald: 60, rose: 32, dot: 75 },
  { emerald: 56, rose: 38, dot: 70 },
  { emerald: 66, rose: 24, dot: 78 },
  { emerald: 72, rose: 35, dot: 84 },
  { emerald: 78, rose: 30, dot: 88 },
  { emerald: 74, rose: 32, dot: 82 },
  { emerald: 85, rose: 26, dot: 92 },
  { emerald: 80, rose: 0, dot: 86 },
  { emerald: 64, rose: 0, dot: 72 },
  { emerald: 58, rose: 0, dot: 68 },
  { emerald: 62, rose: 0, dot: 74 },
  { emerald: 55, rose: 36, dot: 70 },
  { emerald: 68, rose: 42, dot: 80 },
  { emerald: 82, rose: 30, dot: 90 },
  { emerald: 76, rose: 38, dot: 84 },
  { emerald: 64, rose: 24, dot: 74 },
];

// Bottom-Right ("Distribusi Beban Kerja per Kantor" — styled like "Costs by category" in Image 1)
const categoryDistribution = computed(() => {
  const colors = [
    'bg-emerald-500',
    'bg-lime-400',
    'bg-amber-300',
    'bg-emerald-300',
    'bg-lime-300',
    'bg-teal-200',
    'bg-slate-200',
  ];
  const totalOps = offices.value.reduce((acc, o) => acc + (o.daily_ops || 100), 0) || 1420;

  return offices.value.map((o, idx) => {
    const share = Math.round(((o.daily_ops || 100) / totalOps) * 100);
    return {
      ...o,
      colorClass: colors[idx % colors.length],
      opsFormatted: `${o.daily_ops || 120} Ops`,
      share: `${share}%`,
      shareNum: share,
    };
  });
});
</script>

<template>
  <Head title="Executive Dashboard — BKK Kelas I Bandung" />

  <!-- Top Clean Executive Navbar + Sidebar Layout Wrapper (Sypher & Unique Style) -->
  <HybridNavbar mode="inertia" currentRoute="dashboard">
    <!-- Main Container Inside HybridNavbar Slot -->
    <main class="w-full px-4 sm:px-6 lg:px-8 pt-4 pb-12 space-y-4 transition-all duration-300">
      
      <!-- Sub-Header Metadata Strip (Matches "Changes: Last month | Last update: 3 min ago ... Manage layout" in Image 1) -->
      <div class="flex flex-wrap items-center justify-between gap-3 px-1 text-xs text-slate-500">
        <div class="flex flex-wrap items-center gap-4">
          <button
            type="button"
            class="inline-flex items-center gap-1.5 text-slate-600 hover:text-slate-900 font-medium transition-colors cursor-pointer"
          >
            <Calendar class="w-3.5 h-3.5 text-slate-400" />
            <span>Periode: <strong class="font-semibold text-slate-800">14 Hari Terakhir (Okt 2026)</strong></span>
            <ChevronDown class="w-3.5 h-3.5 text-slate-400" />
          </button>

          <span class="text-slate-300">|</span>

          <span class="inline-flex items-center gap-1.5 text-slate-400">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            Pembaruan terakhir: Baru saja (Real-Time)
          </span>
        </div>

        <div class="flex items-center gap-4">
          <Link
            :href="getUrl('/aircraft-inspections')"
            class="inline-flex items-center gap-1.5 text-slate-600 hover:text-slate-900 font-medium transition-colors"
          >
            <Settings2 class="w-3.5 h-3.5 text-slate-400" />
            <span>Kelola Inspeksi &amp; Wilker ({{ offices.length }} Kantor)</span>
          </Link>
        </div>
      </div>

      <!-- Seamless Executive White Grid Card (Matches "Sypher" Image 1 Architecture) -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden divide-y divide-slate-200/80">
        
        <!-- ========================================================================= -->
        <!-- ROW 1: DELIVERIES-STYLE KPI PANEL (LEFT) + INVOICES-STYLE SUMMARY (RIGHT) -->
        <!-- ========================================================================= -->
        <div class="grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-200/80">
          
          <!-- Top-Left: Kesiapsiagaan & Operasional BKK (Styled like "Deliveries" in Image 1) -->
          <div class="lg:col-span-6 min-w-0 p-6">
            <div class="flex items-center justify-between mb-5">
              <h2 class="text-[17px] font-bold text-slate-900 tracking-tight">
                Kesiapsiagaan &amp; Operasional BKK
              </h2>
              <button
                type="button"
                class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                title="Opsi"
              >
                <MoreVertical class="w-4 h-4" />
              </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
              <!-- Metric 1: Vertical Mini-Bars with Pointer (Like "FLEET UTILIZATION 95.1%") -->
              <div>
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                  KELAIKAN SANITASI
                </div>
                <div class="text-xl font-bold text-slate-900 mt-0.5 tracking-tight">
                  {{ metrics.aircraft_avg_score || 96.4 }}%
                </div>

                <!-- 24 Vertical Mini-Bars with Pointer Triangle -->
                <div class="mt-4 relative pt-2">
                  <!-- Small Black Pointer Triangle above bar #20 -->
                  <div class="absolute top-0 right-[14%] text-[8px] leading-none text-slate-900">
                    ▼
                  </div>
                  <div class="flex items-end gap-[3px] h-7">
                    <div
                      v-for="(val, idx) in utilizationBars"
                      :key="idx"
                      class="flex-1 rounded-[1px] transition-all duration-300"
                      :class="idx >= 7 && idx <= 9 ? 'bg-lime-400' : 'bg-emerald-500'"
                      :style="{ height: `${Math.max(45, val - 20)}%` }"
                    ></div>
                  </div>
                </div>
              </div>

              <!-- Metric 2: Smooth Gradient Bar with Pointer (Like "ON-TIME DELIVERY RATE 97.9%") -->
              <div>
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                  KESIAPSIAGAAN 7 WILKER
                </div>
                <div class="text-xl font-bold text-slate-900 mt-0.5 tracking-tight">
                  97.9%
                </div>

                <!-- Horizontal Gradient Bar with Pointer Triangle -->
                <div class="mt-4 relative pt-4">
                  <div class="absolute top-1 right-[8%] text-[8px] leading-none text-slate-900">
                    ▼
                  </div>
                  <div class="h-2.5 w-full rounded-xs bg-gradient-to-r from-amber-400 via-lime-400 to-emerald-500"></div>
                </div>
              </div>

              <!-- Metric 3: Total Inspections + Sparkline (Like "TOTAL DELIVERIES 1,352 ↑ 3.5%") -->
              <div>
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                  TOTAL PENGAWASAN
                </div>
                <div class="flex items-baseline gap-2 mt-0.5">
                  <span class="text-xl font-bold text-slate-900 tracking-tight">
                    1,420
                  </span>
                  <span class="text-[11px] font-semibold text-emerald-600">
                    ↑ 12.4%
                  </span>
                </div>

                <!-- Clean Emerald Sparkline SVG -->
                <div class="mt-3 h-9 w-full relative">
                  <svg viewBox="0 0 140 36" class="w-full h-full overflow-visible">
                    <defs>
                      <linearGradient id="sparkGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#10b981" stop-opacity="0.22" />
                        <stop offset="100%" stop-color="#10b981" stop-opacity="0.0" />
                      </linearGradient>
                    </defs>
                    <path
                      d="M0,26 L12,24 L24,18 L36,21 L48,16 L60,17 L72,14 L84,15 L96,12 L108,13 L120,9 L136,7 L136,34 L0,34 Z"
                      fill="url(#sparkGrad)"
                    />
                    <path
                      d="M0,26 L12,24 L24,18 L36,21 L48,16 L60,17 L72,14 L84,15 L96,12 L108,13 L120,9 L136,7"
                      fill="none"
                      stroke="#10b981"
                      stroke-width="1.6"
                      stroke-linecap="round"
                    />
                    <line x1="136" y1="7" x2="136" y2="34" stroke="#94a3b8" stroke-width="1" stroke-dasharray="2 2" />
                    <circle cx="136" cy="7" r="2.2" fill="#10b981" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Top-Right: Ringkasan Wilayah Kerja & Pengawasan (Styled like "Invoices" in Image 1) -->
          <div class="lg:col-span-6 min-w-0 p-6">
            <div class="flex items-center justify-between mb-5">
              <h2 class="text-[17px] font-bold text-slate-900 tracking-tight">
                Ringkasan Wilayah Kerja &amp; Pengawasan
              </h2>
              <button
                type="button"
                class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                title="Opsi"
              >
                <MoreVertical class="w-4 h-4" />
              </button>
            </div>

            <!-- 3 Proportional Columns with Bottom Thick Colored Bars -->
            <div class="grid grid-cols-12 gap-3 items-end">
              <!-- Col 1: Induk & Pos Bandara (51.5%) -->
              <div class="col-span-6 pr-3 border-r border-slate-100">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                  INDUK &amp; POS UDARA
                </div>
                <div class="text-xl font-bold text-slate-900 mt-0.5 tracking-tight">
                  810 <span class="text-xs font-medium text-slate-400">Ops</span>
                </div>
                <div class="mt-4 text-[11px] font-medium text-slate-400 mb-1.5">
                  57.0%
                </div>
                <div class="h-2 w-full rounded-xs bg-emerald-500"></div>
              </div>

              <!-- Col 2: Wilker Pelabuhan Laut (31.0%) -->
              <div class="col-span-4 pr-3 border-r border-slate-100">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                  WILKER LAUT
                </div>
                <div class="text-xl font-bold text-slate-900 mt-0.5 tracking-tight">
                  440 <span class="text-xs font-medium text-slate-400">Ops</span>
                </div>
                <div class="mt-4 text-[11px] font-medium text-slate-400 mb-1.5">
                  31.0%
                </div>
                <div class="h-2 w-full rounded-xs bg-lime-400"></div>
              </div>

              <!-- Col 3: Tindakan Penyehatan (12.0%) -->
              <div class="col-span-2">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 truncate">
                  VEKTOR
                </div>
                <div class="text-xl font-bold text-slate-900 mt-0.5 tracking-tight">
                  170
                </div>
                <div class="mt-4 text-[11px] font-medium text-slate-400 mb-1.5">
                  12.0%
                </div>
                <div class="h-2 w-full rounded-xs bg-slate-200"></div>
              </div>
            </div>
          </div>

        </div>

        <!-- ========================================================================= -->
        <!-- ROW 2: OPENSTREETMAP WEBGL MAP (LEFT) + CLEAN EXECUTIVE TABLE (RIGHT)     -->
        <!-- ========================================================================= -->
        <div class="grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-200/80">
          
          <!-- Center-Left: OpenStreetMap WebGL Map (1 Induk & 6 Wilker BKK Kelas I Bandung) -->
          <div class="lg:col-span-7 min-w-0 p-6 flex flex-col justify-between">
            <div>
              <!-- Card Title & Camera Controls -->
              <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div>
                  <div class="flex items-center gap-2">
                    <h2 class="text-[17px] font-bold text-slate-900 tracking-tight">
                      Peta Geospasial BKK Kelas I Bandung
                    </h2>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                      OpenStreetMap
                    </span>
                  </div>
                  <p class="text-xs text-slate-400 mt-0.5">
                    Sebaran 1 Kantor Induk &amp; 6 Wilayah Kerja Jawa Barat (Klik titik untuk terbang ke lokasi)
                  </p>
                </div>

                <!-- Clean Light Camera Action Buttons -->
                <div class="flex flex-wrap items-center gap-1.5">
                  <button
                    type="button"
                    @click="resetGlobalView"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-medium bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200/90 transition-all cursor-pointer"
                  >
                    <Globe class="w-3.5 h-3.5 text-emerald-600" />
                    Tampilan Global
                  </button>
                  <button
                    type="button"
                    @click="toggle3DPitch"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-medium border transition-all cursor-pointer"
                    :class="mapPitch > 25
                      ? 'bg-slate-900 text-white border-slate-900 shadow-2xs'
                      : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200/90'"
                  >
                    <Box class="w-3.5 h-3.5" :class="mapPitch > 25 ? 'text-emerald-400' : 'text-slate-500'" />
                    Kemiringan 3D
                  </button>
                  <button
                    type="button"
                    @click="toggleOrbit"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-medium border transition-all cursor-pointer"
                    :class="isOrbiting
                      ? 'bg-emerald-600 text-white border-emerald-600 shadow-2xs'
                      : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200/90'"
                  >
                    <RotateCw class="w-3.5 h-3.5" :class="isOrbiting ? 'animate-spin' : 'text-slate-500'" />
                    Auto Orbit
                  </button>
                </div>
              </div>

              <!-- Fly to Node Bar (Clean Light Pills) -->
              <div class="flex flex-wrap items-center gap-1.5 mb-3">
                <span class="text-[11px] font-medium text-slate-400 mr-1">Fly to Node:</span>
                <button
                  v-for="node in bkkMapMarkers"
                  :key="node.id"
                  type="button"
                  @click="selectOffice(node)"
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] transition-all cursor-pointer border"
                  :class="activeMapNode?.no === node.no
                    ? 'bg-slate-900 text-white border-slate-900 font-semibold shadow-2xs'
                    : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:text-slate-900'"
                >
                  <span class="text-[9px] font-mono font-bold" :class="activeMapNode?.no === node.no ? 'text-emerald-400' : 'text-slate-400'">
                    {{ node.shortCode }}
                  </span>
                  <span>{{ node.shortTitle.replace('Wilker ', '').replace('Induk ', '') }}</span>
                  <span class="text-[9px] font-mono" :class="activeMapNode?.no === node.no ? 'text-emerald-300' : 'text-emerald-600'">
                    ({{ node.latency }})
                  </span>
                </button>
              </div>
            </div>

            <!-- Interactive MapLibre WebGL Map Container -->
            <div class="relative rounded-xl overflow-hidden border border-slate-200 shadow-xs">
              <BaseMap
                ref="mapComponentRef"
                :initialCenter="[107.6108, -6.68]"
                :initialZoom="7.75"
                :initialPitch="35"
                :initialBearing="0"
                styleType="osm"
                :markers="bkkMapMarkers"
                height="430px"
                @selectMarker="onMarkerClick"
                @mapMove="onCameraMove"
              />

              <!-- Compact Top-Left Overlay: Real-Time WebGL HUD (Clean Light/Translucent) -->
              <div class="absolute top-2.5 left-2.5 z-10 w-44 rounded-xl bg-white/92 backdrop-blur-md border border-slate-200/90 px-2.5 py-2 shadow-md pointer-events-none">
                <div class="flex items-center gap-1.5 text-[10px] font-bold text-emerald-700 mb-1">
                  <Navigation class="w-2.5 h-2.5 text-emerald-600" />
                  <span>Real-Time WebGL HUD</span>
                </div>
                <div class="space-y-0.5 text-[9px] font-mono">
                  <div class="flex items-center justify-between text-slate-500">
                    <span>Lng / Lat:</span>
                    <span class="text-slate-900 font-bold">{{ mapCenter[0].toFixed(2) }}, {{ mapCenter[1].toFixed(4) }}</span>
                  </div>
                  <div class="flex items-center justify-between text-slate-500">
                    <span>Zoom / Pitch:</span>
                    <span class="text-slate-900 font-bold">{{ mapZoom.toFixed(2) }}x / {{ mapPitch }}°</span>
                  </div>
                  <div class="flex items-center justify-between text-slate-500">
                    <span>Bearing:</span>
                    <span class="text-slate-900 font-bold">{{ mapBearing }}°</span>
                  </div>
                </div>
              </div>

              <!-- Compact Bottom-Right Overlay: Selected Node Card (Clean White Card with Minimize Toggle) -->
              <div
                v-if="activeMapNode"
                class="absolute bottom-2.5 right-2.5 z-10 rounded-xl bg-white/95 backdrop-blur-md border border-slate-200/90 shadow-lg transition-all duration-200"
                :class="isNodeCardMinimized ? 'w-52 px-2.5 py-1.5' : 'w-60 sm:w-64 px-3 py-2'"
              >
                <div class="flex items-center justify-between gap-1.5">
                  <div class="flex items-center gap-2 min-w-0">
                    <div class="w-6 h-5 rounded bg-slate-900 flex items-center justify-center text-[9px] font-mono font-bold text-white shrink-0">
                      {{ activeMapNode.shortCode }}
                    </div>
                    <div class="min-w-0">
                      <h4 class="text-[11px] font-bold text-slate-900 truncate leading-tight">
                        {{ activeMapNode.title }}
                      </h4>
                      <p v-if="!isNodeCardMinimized" class="text-[9px] text-slate-500 truncate leading-tight">
                        {{ activeMapNode.role }}
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center gap-1 shrink-0">
                    <span class="px-1.5 py-0.5 rounded-full text-[8px] font-mono font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                      {{ activeMapNode.status }}
                    </span>
                    <button
                      type="button"
                      @click="isNodeCardMinimized = !isNodeCardMinimized"
                      class="w-4 h-4 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-[10px] leading-none cursor-pointer"
                      :title="isNodeCardMinimized ? 'Tampilkan detail' : 'Perkecil kotak'"
                    >
                      {{ isNodeCardMinimized ? '+' : '−' }}
                    </button>
                  </div>
                </div>

                <template v-if="!isNodeCardMinimized">
                  <div class="grid grid-cols-2 gap-2 pt-1.5 mt-1.5 border-t border-slate-100 text-[9px]">
                    <div class="flex items-center justify-between pr-1">
                      <span class="text-slate-400">Ping:</span>
                      <span class="font-mono font-bold text-emerald-600">{{ activeMapNode.latency }}</span>
                    </div>
                    <div class="flex items-center justify-between pl-1 border-l border-slate-100">
                      <span class="text-slate-400">Trafik:</span>
                      <span class="font-mono font-bold text-blue-600">{{ activeMapNode.throughput }}</span>
                    </div>
                  </div>

                  <div class="mt-1.5 pt-1.5 border-t border-slate-100 flex items-start gap-1 text-[9px] text-slate-500 leading-tight">
                    <span class="text-rose-500 shrink-0">📍</span>
                    <span class="line-clamp-2">{{ activeMapNode.address }}</span>
                  </div>
                </template>
              </div>
            </div>
          </div>

          <!-- Center-Right: Clean Executive Directory Table of 7 Offices (Styled like the right table in Image 1) -->
          <div class="lg:col-span-5 min-w-0 p-6 flex flex-col justify-between">
            <div>
              <!-- Filter Tabs & Search Bar (Matches "All | Unpaid | Recent request | Paid ... Search ⌘F" in Image 1) -->
              <div class="flex flex-wrap items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-1">
                  <button
                    type="button"
                    @click="activeTabFilter = 'all'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer"
                    :class="activeTabFilter === 'all'
                      ? 'bg-white border border-slate-200/90 text-slate-900 font-semibold shadow-2xs'
                      : 'text-slate-500 hover:text-slate-900 font-medium'"
                  >
                    Semua (7)
                  </button>
                  <button
                    type="button"
                    @click="activeTabFilter = 'udara'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer"
                    :class="activeTabFilter === 'udara'
                      ? 'bg-white border border-slate-200/90 text-slate-900 font-semibold shadow-2xs'
                      : 'text-slate-500 hover:text-slate-900 font-medium'"
                  >
                    Induk &amp; Udara (3)
                  </button>
                  <button
                    type="button"
                    @click="activeTabFilter = 'laut'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer"
                    :class="activeTabFilter === 'laut'
                      ? 'bg-white border border-slate-200/90 text-slate-900 font-semibold shadow-2xs'
                      : 'text-slate-500 hover:text-slate-900 font-medium'"
                  >
                    Pelabuhan Laut (4)
                  </button>
                </div>

                <!-- Clean Search Input -->
                <div class="relative w-full sm:w-48">
                  <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
                  <input
                    v-model="searchOffice"
                    type="text"
                    placeholder="Cari kantor / alamat..."
                    class="w-full pl-8 pr-8 py-1.5 rounded-lg bg-slate-50/80 border border-slate-200/90 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-slate-300 transition-all"
                  />
                  <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[9px] font-mono text-slate-400">
                    ⌘F
                  </span>
                </div>
              </div>

              <!-- Clean Executive Table (Matches "Company | Issue date | Contact | Value" in Image 1) -->
              <div class="overflow-x-auto mt-1">
                <table class="w-full text-left border-collapse">
                  <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-900">
                      <th class="py-3 pl-1 pr-2 w-7">
                        <input
                          type="checkbox"
                          class="w-3.5 h-3.5 rounded border-slate-300 text-slate-900 focus:ring-0 cursor-pointer"
                          :checked="checkedRows.length === filteredOffices.length"
                          @change="checkedRows = checkedRows.length === filteredOffices.length ? [] : filteredOffices.map(o => o.no)"
                        />
                      </th>
                      <th class="py-3 px-2">Kantor / Wilayah Kerja</th>
                      <th class="py-3 px-2">Zona ↓</th>
                      <th class="py-3 px-2">Alamat Lengkap</th>
                      <th class="py-3 pl-2 pr-1 text-right">Ops/Hari</th>
                      <th class="py-3 pl-1 w-6"></th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 text-xs">
                    <tr
                      v-for="office in filteredOffices"
                      :key="office.no"
                      @click="selectOffice(office)"
                      class="group transition-colors cursor-pointer"
                      :class="selectedOffice?.no === office.no ? 'bg-emerald-50/50' : 'hover:bg-slate-50/80'"
                    >
                      <!-- Checkbox -->
                      <td class="py-3 pl-1 pr-2" @click.stop>
                        <input
                          type="checkbox"
                          class="w-3.5 h-3.5 rounded border-slate-300 text-slate-900 focus:ring-0 cursor-pointer"
                          :checked="checkedRows.includes(office.no)"
                          @change="toggleRowCheck(office.no)"
                        />
                      </td>

                      <!-- Kantor + Colored Square Icon Badge -->
                      <td class="py-3 px-2">
                        <div class="flex items-center gap-2.5">
                          <div
                            class="w-6 h-6 rounded-lg flex items-center justify-center text-[9px] font-mono font-bold shrink-0 shadow-2xs"
                            :class="office.badgeClass"
                          >
                            {{ office.shortCode }}
                          </div>
                          <div class="min-w-0">
                            <div class="font-semibold text-slate-900 truncate" :title="office.name">
                              {{ office.short_name }}
                            </div>
                            <div class="text-[10px] text-slate-400 truncate">
                              {{ office.type }}
                            </div>
                          </div>
                        </div>
                      </td>

                      <!-- Zona -->
                      <td class="py-3 px-2 whitespace-nowrap text-slate-600 font-medium text-[11px]">
                        {{ office.zone }}
                      </td>

                      <!-- Alamat Lengkap -->
                      <td class="py-3 px-2 text-slate-500 text-[11px]">
                        <div class="truncate" :title="office.address">
                          {{ office.address }}
                        </div>
                      </td>

                      <!-- Ops / Readiness -->
                      <td class="py-3 pl-2 pr-1 text-right whitespace-nowrap">
                        <span class="font-semibold text-slate-900">{{ office.daily_ops }}</span>
                        <span class="text-[10px] text-slate-400 ml-0.5">ops</span>
                      </td>

                      <!-- Action Dots -->
                      <td class="py-3 pl-1 text-right text-slate-400 group-hover:text-slate-700">
                        <MoreVertical class="w-3.5 h-3.5 inline" />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Table Footer Pagination (Matches "Show by 10 ⌄ ... 1 / 4 >" in Image 1) -->
            <div class="pt-4 mt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
              <div class="flex items-center gap-2">
                <span>Tampilkan</span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border border-slate-200 bg-white text-slate-700 font-medium text-[11px]">
                  7 Kantor
                  <ChevronDown class="w-3 h-3 text-slate-400" />
                </span>
              </div>

              <div class="flex items-center gap-2 text-[11px]">
                <span class="px-2 py-1 rounded-md border border-slate-200 bg-white font-semibold text-slate-800">1</span>
                <span class="text-slate-400">/ 1</span>
                <button type="button" class="p-1 rounded hover:bg-slate-100 text-slate-400 hover:text-slate-700">
                  <ChevronRight class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          </div>

        </div>

        <!-- ========================================================================= -->
        <!-- ROW 3: REVENUE & COSTS CHART + BALANCE & COSTS + COSTS BY CATEGORY        -->
        <!-- ========================================================================= -->
        <div class="grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-200/80">
          
          <!-- Bottom-Left: Tren Pengawasan & Inspeksi Sanitasi (Matches "Revenue and costs" in Image 1) -->
          <div class="lg:col-span-5 min-w-0 p-6 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-2">
                <h2 class="text-[17px] font-bold text-slate-900 tracking-tight">
                  Tren Pengawasan &amp; Inspeksi
                </h2>

                <div class="flex items-center gap-1.5">
                  <!-- Line vs Bar Toggle Pill (Like Image 1 top-right of Revenue and costs) -->
                  <div class="inline-flex items-center p-0.5 rounded-lg bg-slate-100 border border-slate-200/80">
                    <button
                      type="button"
                      @click="trendViewMode = 'line'"
                      class="p-1 rounded-md transition-all cursor-pointer"
                      :class="trendViewMode === 'line' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-400 hover:text-slate-700'"
                      title="Grafik Garis"
                    >
                      <LineChart class="w-3.5 h-3.5" />
                    </button>
                    <button
                      type="button"
                      @click="trendViewMode = 'bar'"
                      class="p-1 rounded-md transition-all cursor-pointer"
                      :class="trendViewMode === 'bar' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-400 hover:text-slate-700'"
                      title="Grafik Batang"
                    >
                      <BarChart3 class="w-3.5 h-3.5" />
                    </button>
                  </div>

                  <button
                    type="button"
                    class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                  >
                    <MoreVertical class="w-4 h-4" />
                  </button>
                </div>
              </div>

              <!-- ECharts Multi-Line Chart -->
              <div class="-mx-2">
                <BaseChart :option="trendChartOption" height="225px" />
              </div>
            </div>

            <!-- Interactive Legend Checkboxes (Matches "[✓] Revenue [✓] Costs [✓] Net profit ... Vs. previous period" in Image 1) -->
            <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs">
              <div class="flex flex-wrap items-center gap-4">
                <button
                  type="button"
                  @click="showLineInspeksi = !showLineInspeksi"
                  class="inline-flex items-center gap-1.5 font-medium text-slate-700 cursor-pointer"
                >
                  <span
                    class="w-4 h-4 rounded flex items-center justify-center text-white transition-colors"
                    :class="showLineInspeksi ? 'bg-emerald-500' : 'bg-slate-200'"
                  >
                    <Check class="w-3 h-3 stroke-[3]" />
                  </span>
                  <span>Inspeksi Sanitasi</span>
                </button>

                <button
                  type="button"
                  @click="showLineVektor = !showLineVektor"
                  class="inline-flex items-center gap-1.5 font-medium text-slate-700 cursor-pointer"
                >
                  <span
                    class="w-4 h-4 rounded flex items-center justify-center text-white transition-colors"
                    :class="showLineVektor ? 'bg-rose-500' : 'bg-slate-200'"
                  >
                    <Check class="w-3 h-3 stroke-[3]" />
                  </span>
                  <span>Tindakan Vektor</span>
                </button>

                <button
                  type="button"
                  @click="showLineLaik = !showLineLaik"
                  class="inline-flex items-center gap-1.5 font-medium text-slate-700 cursor-pointer"
                >
                  <span
                    class="w-4 h-4 rounded flex items-center justify-center text-white transition-colors"
                    :class="showLineLaik ? 'bg-slate-900' : 'bg-slate-200'"
                  >
                    <Check class="w-3 h-3 stroke-[3]" />
                  </span>
                  <span>Laik Sanitasi</span>
                </button>
              </div>

              <!-- Toggle Switch: Vs. periode lalu -->
              <button
                type="button"
                @click="comparePrevPeriod = !comparePrevPeriod"
                class="inline-flex items-center gap-2 text-[11px] text-slate-400 hover:text-slate-700 cursor-pointer"
              >
                <span
                  class="w-7 h-4 rounded-full p-0.5 transition-colors flex items-center"
                  :class="comparePrevPeriod ? 'bg-emerald-500 justify-end' : 'bg-slate-200 justify-start'"
                >
                  <span class="w-3 h-3 rounded-full bg-white shadow-2xs"></span>
                </span>
                <span>Vs. periode lalu</span>
              </button>
            </div>
          </div>

          <!-- Bottom-Center: Volume Harian & Kepatuhan (Matches "Balance and costs" in Image 1) -->
          <div class="lg:col-span-3 min-w-0 p-6 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-4">
                <h2 class="text-[17px] font-bold text-slate-900 tracking-tight">
                  Volume &amp; Kepatuhan
                </h2>
                <button
                  type="button"
                  class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                >
                  <MoreVertical class="w-4 h-4" />
                </button>
              </div>

              <div class="flex items-baseline justify-between">
                <div>
                  <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                    TOTAL OPERASIONAL
                  </div>
                  <div class="text-xl font-bold text-slate-900 mt-0.5 tracking-tight">
                    1,420 <span class="text-xs font-medium text-slate-400">Ops</span>
                  </div>
                </div>
                <div class="text-[11px] text-slate-400">
                  Target: &gt;95% Laik
                </div>
              </div>
            </div>

            <!-- Paired Emerald & Rose Vertical Bars with Black Target Dots on Top (Exact Match to "Balance and costs" in Image 1) -->
            <div class="mt-6 pt-4">
              <div class="flex items-end justify-between gap-1.5 h-36">
                <div
                  v-for="(item, idx) in dailyBalanceBars"
                  :key="idx"
                  class="flex-1 flex flex-col items-center h-full justify-end relative"
                >
                  <!-- Black Target Dot above bar -->
                  <span
                    class="w-1 h-1 rounded-full bg-slate-900 mb-1.5 shrink-0"
                  ></span>

                  <!-- Paired Emerald & Rose Bars -->
                  <div class="w-full flex items-end justify-center gap-[1.5px] h-full">
                    <div
                      class="w-1.5 rounded-t-[1px] bg-emerald-500 transition-all duration-300"
                      :style="{ height: `${item.emerald}%` }"
                    ></div>
                    <div
                      v-if="item.rose > 0"
                      class="w-1.5 rounded-t-[1px] bg-rose-500 transition-all duration-300"
                      :style="{ height: `${item.rose}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom-Right: Distribusi Beban Kerja per Kantor (Matches "Costs by category" in Image 1) -->
          <div class="lg:col-span-4 min-w-0 p-6 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-4">
                <h2 class="text-[17px] font-bold text-slate-900 tracking-tight">
                  Distribusi Operasional Wilker
                </h2>
                <button
                  type="button"
                  class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                >
                  <MoreVertical class="w-4 h-4" />
                </button>
              </div>

              <!-- Multi-Segment Horizontal Stacked Progress Bar -->
              <div class="flex items-center gap-1 h-2.5 w-full mb-5">
                <div
                  v-for="cat in categoryDistribution"
                  :key="cat.no"
                  class="h-full rounded-xs transition-all duration-300"
                  :class="cat.colorClass"
                  :style="{ width: `${Math.max(6, cat.shareNum)}%` }"
                  :title="`${cat.short_name}: ${cat.share}`"
                ></div>
              </div>

              <!-- 7 Category Rows with Colored Square Dot + Dotted Leader + Value + Share % -->
              <div class="space-y-2.5 text-xs">
                <div
                  v-for="cat in categoryDistribution"
                  :key="cat.no"
                  @click="selectOffice(cat)"
                  class="flex items-baseline justify-between gap-2 group cursor-pointer"
                >
                  <div class="flex items-center gap-2 shrink-0">
                    <span class="w-2 h-2 rounded-[2px] shrink-0" :class="cat.colorClass"></span>
                    <span class="text-slate-600 group-hover:text-slate-900 font-medium transition-colors">
                      {{ cat.short_name }}
                    </span>
                  </div>

                  <!-- Dotted Horizontal Leader -->
                  <div class="flex-1 border-b border-dotted border-slate-200 mx-1"></div>

                  <div class="flex items-baseline gap-3 shrink-0 font-mono text-[11px]">
                    <span class="font-bold text-slate-900">{{ cat.opsFormatted }}</span>
                    <span class="text-slate-500 w-8 text-right">{{ cat.share }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>

      </div>
    </main>
  </HybridNavbar>
</template>
