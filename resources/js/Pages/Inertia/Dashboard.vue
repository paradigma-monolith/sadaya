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
  BarChart3,
  FileSpreadsheet,
  Calendar,
  FileCheck,
  HeartPulse,
  CalendarCheck,
  Table,
  PieChart as PieChartIcon,
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
      color: '#005851', // Kemenkes Turquoise Tua / Deep Teal
      data: props.trend?.total || [],
      lineWidth: 2.5,
      area: true,
      areaColor: ['rgba(0, 169, 157, 0.25)', 'rgba(0, 169, 157, 0.0)'],
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
      color: '#007D74', // Kemenkes Medium Teal
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

// ---------------------------------------------------------------------------
// SINKARKES Integrated Executive Analytics & Total Summary State
// ---------------------------------------------------------------------------
const sinkarkesData = computed(() => props.sinkarkes || {});
const selectedSinkarkesYear = ref(props.sinkarkes?.year || 2026);
const selectedSinkarkesWilker = ref(props.sinkarkes?.wilker || 'all');
const availableSinkarkesYears = [2024, 2025, 2026, 2027, 2028, 2029, 2030];

watch(
  () => props.sinkarkes,
  (val) => {
    if (val?.year) selectedSinkarkesYear.value = val.year;
    if (val?.wilker) selectedSinkarkesWilker.value = val.wilker;
  }
);

const sinkarkesKpi = computed(() => sinkarkesData.value.kpi || {});
const sinkarkesWilkerOptions = computed(() => sinkarkesData.value.wilker_options || []);
const sinkarkesMatrix = computed(() => sinkarkesData.value.matrix || []);
const sinkarkesSummaryTable = computed(
  () => sinkarkesData.value.summary_table || { items: [], monthly_totals: {}, total_year: 0, section_totals: {} }
);
const sinkarkesWilkerSummary = computed(
  () => sinkarkesData.value.wilker_summary || { items: [], monthly_totals: {}, total_year: 0 }
);

const activeSummaryTab = ref('indicators'); // 'indicators' | 'wilkers' | 'compliance'
const selectedSectionFilter = ref('ALL'); // 'ALL' | 'KSE' | 'UKP' | 'PRL'
const summarySearchQuery = ref('');
const monthNames = ['JAN', 'FEB', 'MART', 'APRL', 'MEI', 'JUN', 'JULI', 'AGUST', 'SEPT', 'OKT', 'NOV', 'DES'];

const filteredSummaryItems = computed(() => {
  const items = sinkarkesSummaryTable.value?.items || [];
  return items.filter((item) => {
    const matchSection = selectedSectionFilter.value === 'ALL' || item.section === selectedSectionFilter.value;
    const matchSearch =
      !summarySearchQuery.value ||
      item.label.toLowerCase().includes(summarySearchQuery.value.toLowerCase()) ||
      item.code.toLowerCase().includes(summarySearchQuery.value.toLowerCase());
    return matchSection && matchSearch;
  });
});

const sectionCounts = computed(() => {
  const items = sinkarkesSummaryTable.value?.items || [];
  return {
    ALL: items.length,
    KSE: items.filter((i) => i.section === 'KSE').length,
    UKP: items.filter((i) => i.section === 'UKP').length,
    PRL: items.filter((i) => i.section === 'PRL').length,
  };
});

const handleSinkarkesFilterChange = () => {
  const targetPath = typeof window !== 'undefined' ? window.location.pathname : '/dashboard';
  router.get(
    targetPath,
    {
      year: selectedSinkarkesYear.value,
      wilker: selectedSinkarkesWilker.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
};

const openSinkarkesMonthForm = (targetWilker, targetMonth) => {
  router.get(getUrl('/sinkarkes'), {
    wilker: targetWilker,
    year: selectedSinkarkesYear.value,
    month: targetMonth,
  });
};

// SINKARKES Chart 1: Monthly Traffic Trend (12 Months)
const sinkarkesTrafficOption = computed(() => {
  const tc = sinkarkesData.value.traffic_chart || {};
  const months = tc.months || ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  return {
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'cross' },
      backgroundColor: '#003B36',
      borderColor: '#00A99D',
      textStyle: { color: '#f8fafc', fontSize: 11 },
    },
    legend: {
      data: ['Pesawat (Pswt)', 'Kapal Laut (Kpl)', 'Penumpang (Org)', 'Kru & ABK (Org)'],
      bottom: 0,
      textStyle: { color: '#64748b', fontSize: 11 },
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '12%',
      top: '8%',
      containLabel: true,
    },
    xAxis: {
      type: 'category',
      boundaryGap: false,
      data: months,
      axisLine: { lineStyle: { color: '#cbd5e1' } },
      axisLabel: { fontSize: 11, color: '#64748b' },
    },
    yAxis: [
      {
        type: 'value',
        name: 'Alat Angkut',
        splitLine: { lineStyle: { color: 'rgba(226, 232, 240, 0.6)' } },
        axisLabel: { fontSize: 10, color: '#64748b' },
      },
      {
        type: 'value',
        name: 'Orang (Penumpang/Kru)',
        position: 'right',
        splitLine: { show: false },
        axisLabel: { fontSize: 10, color: '#64748b' },
      },
    ],
    series: [
      {
        name: 'Pesawat (Pswt)',
        type: 'line',
        smooth: true,
        yAxisIndex: 0,
        data: tc.plane || [],
        itemStyle: { color: '#00A99D' },
        lineStyle: { width: 2.5, color: '#00A99D' },
        areaStyle: {
          color: {
            type: 'linear',
            x: 0,
            y: 0,
            x2: 0,
            y2: 1,
            colorStops: [
              { offset: 0, color: 'rgba(0, 169, 157, 0.35)' },
              { offset: 1, color: 'rgba(0, 169, 157, 0.02)' },
            ],
          },
        },
      },
      {
        name: 'Kapal Laut (Kpl)',
        type: 'line',
        smooth: true,
        yAxisIndex: 0,
        data: tc.ship || [],
        itemStyle: { color: '#005851' },
        lineStyle: { width: 2.5, color: '#005851' },
      },
      {
        name: 'Penumpang (Org)',
        type: 'line',
        smooth: true,
        yAxisIndex: 1,
        data: tc.passenger || [],
        itemStyle: { color: '#f59e0b' },
        lineStyle: { width: 2, color: '#f59e0b', type: 'dashed' },
      },
      {
        name: 'Kru & ABK (Org)',
        type: 'line',
        smooth: true,
        yAxisIndex: 1,
        data: tc.crew || [],
        itemStyle: { color: '#7CA622' },
        lineStyle: { width: 2, color: '#7CA622' },
      },
    ],
  };
});

// SINKARKES Chart 2: Document Composition (Donut / Pie)
const sinkarkesDocumentOption = computed(() => {
  return {
    tooltip: {
      trigger: 'item',
      formatter: '{b}: <strong>{c} dokumen</strong> ({d}%)',
      backgroundColor: '#003B36',
      borderColor: '#00A99D',
      textStyle: { color: '#f8fafc', fontSize: 11 },
    },
    legend: {
      orient: 'vertical',
      right: '2%',
      top: 'middle',
      textStyle: { color: '#64748b', fontSize: 10 },
      itemWidth: 12,
      itemHeight: 12,
    },
    series: [
      {
        name: 'Dokumen Kekarantinaan',
        type: 'pie',
        radius: ['45%', '72%'],
        center: ['35%', '50%'],
        avoidLabelOverlap: true,
        itemStyle: {
          borderRadius: 6,
          borderColor: '#fff',
          borderWidth: 2,
        },
        label: {
          show: false,
          position: 'center',
        },
        emphasis: {
          label: {
            show: true,
            fontSize: 13,
            fontWeight: 'bold',
            formatter: '{b}\n{c} ({d}%)',
          },
        },
        color: ['#00A99D', '#005851', '#7CA622', '#0284c7', '#8b5cf6', '#f59e0b', '#ec4899', '#64748b'],
        data: sinkarkesData.value.document_chart || [],
      },
    ],
  };
});

// SINKARKES Chart 3: Cross-Wilker Comparison
const sinkarkesWilkerOption = computed(() => {
  const comparison = sinkarkesData.value.wilker_comparison || [];
  const categories = comparison.map((c) => c.short_name);
  const alatAngkut = comparison.map((c) => c.total_alat_angkut);
  const orang = comparison.map((c) => c.total_orang);
  const dokumen = comparison.map((c) => c.total_dokumen);

  return {
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'shadow' },
      backgroundColor: '#003B36',
      borderColor: '#00A99D',
      textStyle: { color: '#f8fafc', fontSize: 11 },
    },
    legend: {
      data: ['Alat Angkut', 'Orang (Penumpang & Kru)', 'Dokumen Karantina'],
      bottom: 0,
      textStyle: { color: '#64748b', fontSize: 11 },
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '12%',
      top: '5%',
      containLabel: true,
    },
    xAxis: {
      type: 'value',
      splitLine: { lineStyle: { color: 'rgba(226, 232, 240, 0.6)' } },
      axisLabel: { fontSize: 10, color: '#64748b' },
    },
    yAxis: {
      type: 'category',
      data: categories,
      axisLine: { lineStyle: { color: '#cbd5e1' } },
      axisLabel: { fontSize: 11, color: '#334155', fontWeight: 'bold' },
    },
    series: [
      {
        name: 'Alat Angkut',
        type: 'bar',
        stack: 'total',
        data: alatAngkut,
        itemStyle: { color: '#00A99D', borderRadius: [0, 4, 4, 0] },
      },
      {
        name: 'Orang (Penumpang & Kru)',
        type: 'bar',
        data: orang,
        itemStyle: { color: '#f59e0b', borderRadius: [0, 4, 4, 0] },
      },
      {
        name: 'Dokumen Karantina',
        type: 'bar',
        data: dokumen,
        itemStyle: { color: '#005851', borderRadius: [0, 4, 4, 0] },
      },
    ],
  };
});

// SINKARKES Chart 4: Health Services & ICV Vaccines
const sinkarkesHealthOption = computed(() => {
  const hc = sinkarkesData.value.health_chart || {};
  const months = hc.months || ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  return {
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'shadow' },
      backgroundColor: '#003B36',
      borderColor: '#00A99D',
      textStyle: { color: '#f8fafc', fontSize: 11 },
    },
    legend: {
      data: ['Kunjungan Poliklinik BKK', 'Surat Berbadan Sehat', 'Vaksinasi ICV'],
      bottom: 0,
      textStyle: { color: '#64748b', fontSize: 11 },
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '12%',
      top: '8%',
      containLabel: true,
    },
    xAxis: {
      type: 'category',
      data: months,
      axisLine: { lineStyle: { color: '#cbd5e1' } },
      axisLabel: { fontSize: 11, color: '#64748b' },
    },
    yAxis: {
      type: 'value',
      splitLine: { lineStyle: { color: 'rgba(226, 232, 240, 0.6)' } },
      axisLabel: { fontSize: 10, color: '#64748b' },
    },
    series: [
      {
        name: 'Kunjungan Poliklinik BKK',
        type: 'bar',
        data: hc.poliklinik || [],
        itemStyle: { color: '#0284c7', borderRadius: [4, 4, 0, 0] },
      },
      {
        name: 'Surat Berbadan Sehat',
        type: 'bar',
        data: hc.surat_sehat || [],
        itemStyle: { color: '#7CA622', borderRadius: [4, 4, 0, 0] },
      },
      {
        name: 'Vaksinasi ICV',
        type: 'bar',
        data: hc.vaksin_icv || [],
        itemStyle: { color: '#00A99D', borderRadius: [4, 4, 0, 0] },
      },
    ],
  };
});
</script>

<template>
  <Head title="Tactical Command Center — BKK Kelas I Bandung" />

  <HybridNavbar mode="inertia" currentRoute="dashboard">
    <main class="w-full px-3 sm:px-5 lg:px-6 pt-3 pb-16 space-y-3.5 transition-all duration-300">
      
      <!-- ========================================================================= -->
      <!-- TOP COMMAND BAR: HARMONISASI IDENTITAS KEMENKES RI (TURQUOISE & DARK TEAL)-->
      <!-- ========================================================================= -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#003B36] via-[#004D44] to-[#005851] text-white p-3.5 sm:p-4 shadow-xl border border-[#005851]/80 ring-1 ring-[#00A99D]/30 font-sans">
        <!-- Background Subtle Brand Grid Accent -->
        <div class="absolute inset-0 opacity-15 pointer-events-none bg-[radial-gradient(#00A99D_1px,transparent_1px)] [background-size:16px_16px]"></div>

        <div class="relative z-10 flex flex-wrap items-center justify-between gap-3">
          <!-- Left: Identity & Live Telemetry -->
          <div class="flex items-center gap-3 min-w-0">
            <div class="relative w-9 h-9 rounded-xl bg-gradient-to-br from-[#00A99D] to-[#005851] flex items-center justify-center font-black text-white shrink-0 shadow-lg shadow-black/20 border border-[#99DFD6]/40">
              <Radio class="w-5 h-5 text-white animate-pulse" />
              <span class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-[#99CC33] border-2 border-[#003B36] animate-ping"></span>
            </div>

            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <h1 class="text-sm sm:text-base font-extrabold font-heading tracking-tight text-white flex items-center gap-2 truncate">
                  <span>PUSAT KOMANDO GEOSPASIAL</span>
                  <span class="text-[#99CC33] font-mono text-xs font-bold">BKK KELAS I BANDUNG</span>
                </h1>
                <span class="hidden md:inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#00A99D]/20 text-[#E6F7F5] border border-[#00A99D]/40">
                  <span class="w-1.5 h-1.5 rounded-full bg-[#99CC33] animate-pulse"></span>
                  OPERASIONAL AKTIF
                </span>
              </div>
              <p class="text-[11px] text-slate-200 flex items-center gap-2 truncate mt-0.5">
                <span>Jaringan 7 Wilker &bull; 4 Moda Karantina Terpadu</span>
                <span class="text-slate-400">|</span>
                <span class="font-mono text-[#99DFD6]">{{ generated_at }}</span>
              </p>
            </div>
          </div>

          <!-- Right: Command Controls, Mode Switcher & Form Launchers -->
          <div class="flex flex-wrap items-center gap-2 shrink-0 text-xs">
            <!-- Unified View Mode Switcher -->
            <div class="inline-flex items-center p-0.5 rounded-xl bg-[#002D29]/90 border border-[#00A99D]/40 text-[11px] font-bold">
              <button
                type="button"
                @click="dashboardViewMode = 'all'"
                class="px-2.5 py-1 rounded-lg transition-all cursor-pointer"
                :class="dashboardViewMode === 'all' ? 'bg-[#00A99D] text-white shadow-xs' : 'text-slate-300 hover:text-white'"
              >
                Semua Terpadu
              </button>
              <button
                type="button"
                @click="dashboardViewMode = 'sinkarkes'"
                class="px-2.5 py-1 rounded-lg transition-all cursor-pointer flex items-center gap-1"
                :class="dashboardViewMode === 'sinkarkes' ? 'bg-[#00A99D] text-white shadow-xs' : 'text-slate-300 hover:text-white'"
              >
                <BarChart3 class="w-3 h-3 text-[#99CC33]" />
                <span>SINKARKES</span>
              </button>
              <button
                type="button"
                @click="dashboardViewMode = 'geospatial'"
                class="px-2.5 py-1 rounded-lg transition-all cursor-pointer hidden sm:inline-flex items-center gap-1"
                :class="dashboardViewMode === 'geospatial' ? 'bg-[#00A99D] text-white shadow-xs' : 'text-slate-300 hover:text-white'"
              >
                <Globe class="w-3 h-3 text-[#99DFD6]" />
                <span>Peta &amp; Harian</span>
              </button>
            </div>

            <button
              type="button"
              @click="refreshData"
              :disabled="isRefreshing"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white/10 hover:bg-[#00A99D]/25 text-white hover:text-[#E6F7F5] border border-white/15 hover:border-[#00A99D]/40 transition-all cursor-pointer disabled:opacity-50"
              title="Sinkronisasi data telemetri terkini"
            >
              <RefreshCw class="w-3.5 h-3.5" :class="isRefreshing ? 'animate-spin text-[#99CC33]' : 'text-[#99DFD6]'" />
              <span class="hidden sm:inline">{{ isRefreshing ? 'Menghubungkan...' : 'Sinkron' }}</span>
            </button>

            <div class="h-5 w-px bg-white/20 hidden sm:block"></div>

            <div class="flex items-center gap-1">
              <Link
                :href="getUrl('/sinkarkes')"
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-[#99CC33]/25 hover:bg-[#99CC33]/40 text-[#F7FCE8] border border-[#99CC33]/50 transition-all flex items-center gap-1 cursor-pointer"
                title="Buka Formulir Input Laporan SINKARKES"
              >
                <FileSpreadsheet class="w-3.5 h-3.5 text-[#99CC33]" />
                <span class="hidden md:inline">SINKARKES</span>
              </Link>
              <a
                :href="getUrl('/travelconsent/public_aircraft')"
                target="_blank"
                rel="noopener noreferrer"
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-[#00A99D]/20 hover:bg-[#00A99D]/35 text-[#E6F7F5] border border-[#00A99D]/40 transition-all flex items-center gap-1 cursor-pointer"
                title="Input Inspeksi Pesawat Udara"
              >
                <Plane class="w-3.5 h-3.5" />
                <span class="hidden md:inline">Pesawat</span>
              </a>
              <a
                :href="getUrl('/travelconsent/public_sanitasi')"
                target="_blank"
                rel="noopener noreferrer"
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-[#005851]/40 hover:bg-[#005851]/60 text-white border border-[#99DFD6]/35 transition-all flex items-center gap-1 cursor-pointer"
                title="Input Karantina Sanitasi Kapal Laut"
              >
                <Ship class="w-3.5 h-3.5" />
                <span class="hidden md:inline">Kapal</span>
              </a>
              <a
                :href="getUrl('/travelconsent/public_alat')"
                target="_blank"
                rel="noopener noreferrer"
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-[#99CC33]/20 hover:bg-[#99CC33]/35 text-[#F7FCE8] border border-[#99CC33]/40 transition-all flex items-center gap-1 cursor-pointer"
                title="Input Pemeriksaan Alat Kesehatan Haji"
              >
                <Stethoscope class="w-3.5 h-3.5" />
                <span class="hidden md:inline">Alkes</span>
              </a>
              <a
                :href="getUrl('/travelconsent/public_obat')"
                target="_blank"
                rel="noopener noreferrer"
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-[#00A99D]/25 hover:bg-[#00A99D]/40 text-[#E6F7F5] border border-[#00A99D]/45 transition-all flex items-center gap-1 cursor-pointer"
                title="Input Pemakaian Obat P3K Pesawat"
              >
                <Pill class="w-3.5 h-3.5" />
                <span class="hidden md:inline">Obat</span>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- 4 HARMONIZED SERVICE TILES (KEMENKES BRAND IDENTITY)                       -->
      <!-- ========================================================================= -->
      <div v-if="dashboardViewMode !== 'sinkarkes'" class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 font-sans">
        
        <!-- Tile 1: Sanitasi Pesawat Udara (Turquoise Accent) -->
        <div
          @click="selectedModuleKey = selectedModuleKey === 'aircraft' ? 'all' : 'aircraft'"
          class="rounded-xl p-3 transition-all cursor-pointer border relative overflow-hidden group bg-white shadow-2xs hover:shadow-md"
          :class="selectedModuleKey === 'aircraft'
            ? 'border-[#00A99D] ring-2 ring-[#00A99D]/25 bg-gradient-to-br from-[#E6F7F5]/50 to-white'
            : 'border-slate-200 hover:border-[#00A99D]'"
        >
          <div class="absolute top-0 left-0 right-0 h-1 bg-[#00A99D]" :class="selectedModuleKey === 'aircraft' ? 'opacity-100' : 'opacity-80'"></div>
          <div class="flex items-center justify-between text-xs mt-0.5">
            <span class="font-bold flex items-center gap-1.5 text-slate-800">
              <span class="w-6 h-6 rounded-lg bg-[#E6F7F5] text-[#005851] flex items-center justify-center shrink-0">
                <Plane class="w-3.5 h-3.5" />
              </span>
              <span>Sanitasi Pesawat</span>
            </span>
            <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-[#E6F7F5] text-[#005851] border border-[#99DFD6]">
              {{ kpi.compliance_rate }}% Laik
            </span>
          </div>

          <div class="flex items-baseline justify-between mt-2">
            <div>
              <span class="text-xl font-black text-[#005851] tracking-tight font-mono">
                {{ aircraft_stats.total }}
              </span>
              <span class="text-xs text-slate-400 ml-1">dok</span>
            </div>
            <div class="text-[11px] font-semibold text-slate-500">
              <span class="text-[#00A99D] font-bold">{{ aircraft_stats.layak }} Layak</span> &bull; 
              <span :class="aircraft_stats.tidak_layak > 0 ? 'text-rose-600 font-bold' : 'text-slate-400'">{{ aircraft_stats.tidak_layak }} Tindakan</span>
            </div>
          </div>
        </div>

        <!-- Tile 2: Sanitasi Kapal Laut (Dark Teal Accent) -->
        <div
          @click="selectedModuleKey = selectedModuleKey === 'ship' ? 'all' : 'ship'"
          class="rounded-xl p-3 transition-all cursor-pointer border relative overflow-hidden group bg-white shadow-2xs hover:shadow-md"
          :class="selectedModuleKey === 'ship'
            ? 'border-[#005851] ring-2 ring-[#005851]/25 bg-gradient-to-br from-[#E0EFEF]/50 to-white'
            : 'border-slate-200 hover:border-[#005851]'"
        >
          <div class="absolute top-0 left-0 right-0 h-1 bg-[#005851]" :class="selectedModuleKey === 'ship' ? 'opacity-100' : 'opacity-80'"></div>
          <div class="flex items-center justify-between text-xs mt-0.5">
            <span class="font-bold flex items-center gap-1.5 text-slate-800">
              <span class="w-6 h-6 rounded-lg bg-[#E0EFEF] text-[#005851] flex items-center justify-center shrink-0">
                <Ship class="w-3.5 h-3.5" />
              </span>
              <span>Sanitasi Kapal</span>
            </span>
            <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-[#E0EFEF] text-[#005851] border border-[#99DFD6]">
              IHR 2005
            </span>
          </div>

          <div class="flex items-baseline justify-between mt-2">
            <div>
              <span class="text-xl font-black text-[#005851] tracking-tight font-mono">
                {{ ship_stats.total }}
              </span>
              <span class="text-xs text-slate-400 ml-1">kapal</span>
            </div>
            <div class="text-[11px] font-semibold text-slate-500">
              <span class="text-[#005851] font-bold">{{ ship_stats.sscec }} SSCEC</span> &bull; 
              <span class="text-amber-700 font-bold">{{ ship_stats.sscc }} SSCC</span>
            </div>
          </div>
        </div>

        <!-- Tile 3: Alkes Haji & Emergensi (Lime Accent) -->
        <div
          @click="selectedModuleKey = selectedModuleKey === 'alat' ? 'all' : 'alat'"
          class="rounded-xl p-3 transition-all cursor-pointer border relative overflow-hidden group bg-white shadow-2xs hover:shadow-md"
          :class="selectedModuleKey === 'alat'
            ? 'border-[#99CC33] ring-2 ring-[#99CC33]/30 bg-gradient-to-br from-[#F7FCE8]/60 to-white'
            : 'border-slate-200 hover:border-[#99CC33]'"
        >
          <div class="absolute top-0 left-0 right-0 h-1 bg-[#99CC33]" :class="selectedModuleKey === 'alat' ? 'opacity-100' : 'opacity-80'"></div>
          <div class="flex items-center justify-between text-xs mt-0.5">
            <span class="font-bold flex items-center gap-1.5 text-slate-800">
              <span class="w-6 h-6 rounded-lg bg-[#F7FCE8] text-[#557A18] flex items-center justify-center shrink-0">
                <Stethoscope class="w-3.5 h-3.5" />
              </span>
              <span>Alkes Haji Pesawat</span>
            </span>
            <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-[#F7FCE8] text-[#557A18] border border-[#D6EC8E]">
              Emergensi
            </span>
          </div>

          <div class="flex items-baseline justify-between mt-2">
            <div>
              <span class="text-xl font-black text-[#005851] tracking-tight font-mono">
                {{ medical_stats.alat }}
              </span>
              <span class="text-xs text-slate-400 ml-1">dok</span>
            </div>
            <div class="text-[11px] font-semibold text-slate-500">
              <span class="text-[#557A18] font-bold">{{ kpi.approved }} Selesai</span> &bull; 
              <span class="text-amber-700 font-bold">{{ kpi.pending }} Proses</span>
            </div>
          </div>
        </div>

        <!-- Tile 4: Obat P3K Pesawat (Turquoise-Lime Blend) -->
        <div
          @click="selectedModuleKey = selectedModuleKey === 'obat' ? 'all' : 'obat'"
          class="rounded-xl p-3 transition-all cursor-pointer border relative overflow-hidden group bg-white shadow-2xs hover:shadow-md"
          :class="selectedModuleKey === 'obat'
            ? 'border-[#00A99D] ring-2 ring-[#00A99D]/25 bg-gradient-to-br from-[#E6F7F5]/50 to-white'
            : 'border-slate-200 hover:border-[#00A99D]'"
        >
          <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#00A99D] to-[#99CC33]" :class="selectedModuleKey === 'obat' ? 'opacity-100' : 'opacity-80'"></div>
          <div class="flex items-center justify-between text-xs mt-0.5">
            <span class="font-bold flex items-center gap-1.5 text-slate-800">
              <span class="w-6 h-6 rounded-lg bg-[#E6F7F5] text-[#005851] flex items-center justify-center shrink-0">
                <Pill class="w-3.5 h-3.5" />
              </span>
              <span>Obat P3K Pesawat</span>
            </span>
            <span class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-[#E6F7F5] text-[#005851] border border-[#99DFD6]">
              Total {{ kpi.total }}
            </span>
          </div>

          <div class="flex items-baseline justify-between mt-2">
            <div>
              <span class="text-xl font-black text-[#005851] tracking-tight font-mono">
                {{ medical_stats.obat }}
              </span>
              <span class="text-xs text-slate-400 ml-1">dok</span>
            </div>
            <div class="text-[11px] font-semibold text-slate-500">
              <span class="text-[#00A99D] font-bold">{{ kpi.completion_rate }}% Verifikasi</span> &bull; 
              <span class="text-slate-600 font-medium">{{ kpi.today }} Hari Ini</span>
            </div>
          </div>
        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- CENTERPIECE: TACTICAL GEOSPATIAL COMMAND CENTER (HARMONISASI KEMENKES)     -->
      <!-- ========================================================================= -->
      <div v-if="dashboardViewMode !== 'sinkarkes'" class="bg-white rounded-2xl border-2 border-[#003B36] shadow-2xl overflow-hidden ring-4 ring-[#00A99D]/15 font-sans">
        
        <!-- Centerpiece Top Command Bar (Dark Teal Gradient) -->
        <div class="px-4 py-2.5 bg-gradient-to-r from-[#003B36] via-[#004D44] to-[#005851] text-white flex flex-wrap items-center justify-between gap-3 border-b border-[#003B36]">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-[#00A99D] text-white flex items-center justify-center font-black text-xs shadow-md shadow-[#00A99D]/30 border border-[#99DFD6]/40">
              <Scan class="w-4 h-4" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h2 class="text-sm font-extrabold font-heading tracking-tight text-white flex items-center gap-1.5">
                  <span>PETA GEOSPASIAL WILAYAH KERJA JAWA BARAT</span>
                </h2>
                <span class="px-1.5 py-0.2 rounded text-[10px] font-mono font-bold bg-[#00A99D]/20 text-[#E6F7F5] border border-[#00A99D]/40">
                  7 NODE TERHUBUNG
                </span>
              </div>
              <p class="text-[11px] text-slate-200">
                Vektor koneksi otomatis menghubungkan Kantor Induk Bandung dengan 6 Pos/Wilayah Kerja
              </p>
            </div>
          </div>

          <!-- Controls: Map Layer Switcher & WebGL Actions -->
          <div class="flex flex-wrap items-center gap-2 text-xs">
            <!-- Map Layer Switcher -->
            <div class="inline-flex items-center p-0.5 rounded-lg bg-[#00312D] border border-[#005851] text-[10px] font-bold">
              <button
                type="button"
                @click="switchMapLayer('osm')"
                class="px-2 py-0.5 rounded transition-all cursor-pointer"
                :class="activeMapLayer === 'osm' ? 'bg-[#00A99D] text-white shadow-xs' : 'text-slate-300 hover:text-white'"
              >
                OSM Standar
              </button>
              <button
                type="button"
                @click="switchMapLayer('hot')"
                class="px-2 py-0.5 rounded transition-all cursor-pointer"
                :class="activeMapLayer === 'hot' ? 'bg-[#00A99D] text-white shadow-xs' : 'text-slate-300 hover:text-white'"
              >
                Humanitarian
              </button>
              <button
                type="button"
                @click="switchMapLayer('osmde')"
                class="px-2 py-0.5 rounded transition-all cursor-pointer"
                :class="activeMapLayer === 'osmde' ? 'bg-[#00A99D] text-white shadow-xs' : 'text-slate-300 hover:text-white'"
              >
                Topografi
              </button>
            </div>

            <!-- WebGL Camera Actions -->
            <div class="flex items-center gap-1">
              <button
                type="button"
                @click="resetGlobalView"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white/10 hover:bg-[#00A99D]/25 text-white transition-all cursor-pointer border border-white/10"
                title="Reset tampilan seluruh Jawa Barat"
              >
                <Globe class="w-3 h-3 text-[#99CC33]" />
                <span>Global</span>
              </button>
              <button
                type="button"
                @click="toggle3DPitch"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold border transition-all cursor-pointer"
                :class="mapPitch > 25
                  ? 'bg-[#00A99D] text-white border-[#00A99D] shadow-xs'
                  : 'bg-white/10 hover:bg-[#00A99D]/20 text-white border-white/10'"
              >
                <Box class="w-3 h-3" />
                <span>3D</span>
              </button>
              <button
                type="button"
                @click="toggleOrbit"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold border transition-all cursor-pointer"
                :class="isOrbiting
                  ? 'bg-[#00A99D] text-white border-[#00A99D] shadow-xs'
                  : 'bg-white/10 hover:bg-[#00A99D]/20 text-white border-white/10'"
              >
                <RotateCw class="w-3 h-3" :class="isOrbiting ? 'animate-spin' : ''" />
                <span>Orbit</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Fly to Node Ribbon (Interactive Tactical Beacon Strip) -->
        <div class="px-3.5 py-2 bg-[#002D29] border-b border-[#003B36] text-white flex flex-wrap items-center justify-between gap-2 text-xs">
          <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-[10px] font-mono font-bold tracking-wider uppercase text-[#99CC33] mr-1 flex items-center gap-1">
              <Crosshair class="w-3 h-3 text-[#99CC33]" />
              TARGET:
            </span>
            <button
              v-for="node in bkkMapMarkers"
              :key="node.id"
              type="button"
              @click="selectOffice(node)"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs transition-all cursor-pointer border"
              :class="selectedOffice?.no === node.no
                ? 'bg-[#00A99D] text-white border-[#00A99D] font-extrabold shadow-md shadow-[#00A99D]/30'
                : 'bg-[#003B36]/90 text-slate-200 border-[#004D44] hover:bg-[#005851] hover:text-white'"
            >
              <span class="text-[9px] font-mono font-black" :class="selectedOffice?.no === node.no ? 'text-white' : 'text-[#99CC33]'">
                {{ node.shortCode }}
              </span>
              <span>{{ node.shortTitle.replace('Wilker ', '').replace('Induk ', '') }}</span>
              <span
                v-if="node.total > 0"
                class="text-[9px] font-bold px-1 rounded-full font-mono"
                :class="selectedOffice?.no === node.no ? 'bg-[#003B36] text-[#99CC33]' : 'bg-[#00A99D]/20 text-[#E6F7F5] border border-[#00A99D]/30'"
              >
                {{ node.total }}
              </span>
            </button>
          </div>

          <div class="text-[11px] font-mono text-slate-300 hidden xl:flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#99CC33] animate-pulse"></span>
            <span>KLIK NODE UNTUK INSPEKSI LOKASI PRESISI</span>
          </div>
        </div>

        <!-- Centerpiece Body: Map + Tactical Wilker Telemetry Dock -->
        <div class="grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-200">
          
          <!-- Large WebGL Map Area (8 Cols) -->
          <div class="lg:col-span-8 relative min-w-0 bg-[#002D29]">
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
            <div class="absolute top-3 left-3 z-10 w-48 rounded-xl bg-[#002D29]/92 backdrop-blur-md border border-[#00A99D]/30 px-3 py-2.5 shadow-2xl text-[10px] font-mono text-[#99DFD6] pointer-events-none ring-1 ring-[#00A99D]/20">
              <div class="flex items-center justify-between border-b border-[#00A99D]/30 pb-1 mb-1.5">
                <div class="flex items-center gap-1.5 font-bold text-[#99CC33]">
                  <Terminal class="w-3 h-3 text-[#99CC33]" />
                  <span>TELEMETRI GPS</span>
                </div>
                <span class="w-1.5 h-1.5 rounded-full bg-[#99CC33] animate-ping"></span>
              </div>
              <div class="space-y-1 text-slate-200">
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
                  <span class="text-[#99CC33] font-bold">TERKONEKSI (7)</span>
                </div>
              </div>
            </div>

            <!-- Bottom Right Target Node Telemetry HUD -->
            <div
              v-if="activeMapNode"
              class="absolute bottom-3 right-3 z-10 rounded-xl bg-[#002D29]/94 backdrop-blur-md border border-[#005851] shadow-2xl transition-all duration-200 text-white ring-1 ring-[#00A99D]/30"
              :class="isNodeCardMinimized ? 'w-52 px-3 py-2' : 'w-72 sm:w-80 px-4 py-3'"
            >
              <div class="flex items-center justify-between gap-1.5">
                <div class="flex items-center gap-2.5 min-w-0">
                  <div class="w-7 h-7 rounded-lg bg-[#00A99D] text-white flex items-center justify-center text-xs font-mono font-black shrink-0 shadow-xs border border-[#99DFD6]/40">
                    {{ activeMapNode.shortCode }}
                  </div>
                  <div class="min-w-0">
                    <h4 class="text-xs font-black text-white truncate">
                      {{ activeMapNode.title }}
                    </h4>
                    <p v-if="!isNodeCardMinimized" class="text-[10px] text-[#99CC33] truncate font-mono">
                      {{ activeMapNode.type }} &bull; {{ activeMapNode.moda }}
                    </p>
                  </div>
                </div>
                <button
                  type="button"
                  @click="isNodeCardMinimized = !isNodeCardMinimized"
                  class="w-5 h-5 rounded bg-[#003B36] hover:bg-[#005851] text-slate-300 flex items-center justify-center text-xs leading-none cursor-pointer"
                >
                  {{ isNodeCardMinimized ? '+' : '−' }}
                </button>
              </div>

              <template v-if="!isNodeCardMinimized">
                <div class="grid grid-cols-2 gap-2 pt-2.5 mt-2.5 border-t border-[#003B36] text-[11px] font-mono">
                  <div class="p-1.5 rounded bg-[#003B36]/80 border border-[#004D44]">
                    <span class="text-[9px] text-slate-400 block uppercase">Total Dokumen</span>
                    <span class="text-sm font-black text-[#99CC33]">{{ activeMapNode.total || 0 }}</span>
                  </div>
                  <div class="p-1.5 rounded bg-[#003B36]/80 border border-[#004D44]">
                    <span class="text-[9px] text-slate-400 block uppercase">Terakhir Aktif</span>
                    <span class="text-xs font-semibold text-[#E6F7F5] truncate block">{{ activeMapNode.last_human || 'Belum ada' }}</span>
                  </div>
                </div>
                <div class="mt-2 pt-2 border-t border-[#003B36] text-[10px] text-slate-300 line-clamp-2 leading-tight flex items-start gap-1">
                  <MapPin class="w-3 h-3 text-[#99CC33] shrink-0 mt-0.5" />
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
                      :class="activeOfficeFilter === 'all' ? 'bg-[#005851] text-white' : 'text-slate-600 hover:text-[#005851]'"
                    >
                      Semua (7)
                    </button>
                    <button
                      type="button"
                      @click="activeOfficeFilter = 'udara'"
                      class="px-2 py-0.5 rounded text-[11px] font-bold transition-all cursor-pointer"
                      :class="activeOfficeFilter === 'udara' ? 'bg-[#005851] text-white' : 'text-slate-600 hover:text-[#005851]'"
                    >
                      Udara (3)
                    </button>
                    <button
                      type="button"
                      @click="activeOfficeFilter = 'laut'"
                      class="px-2 py-0.5 rounded text-[11px] font-bold transition-all cursor-pointer"
                      :class="activeOfficeFilter === 'laut' ? 'bg-[#005851] text-white' : 'text-slate-600 hover:text-[#005851]'"
                    >
                      Laut (4)
                    </button>
                  </div>

                  <span class="text-[10px] font-mono font-bold text-[#005851] bg-[#E6F7F5] px-1.5 py-0.2 rounded border border-[#99DFD6]">
                    STATUS AKTIF
                  </span>
                </div>

                <div class="relative w-full">
                  <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
                  <input
                    v-model="searchOffice"
                    type="text"
                    placeholder="Cari wilker / alamat..."
                    class="w-full pl-8 pr-2 py-1 rounded-lg bg-white border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#00A99D]"
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
                    ? 'bg-white border-2 border-[#00A99D] shadow-md ring-2 ring-[#00A99D]/20'
                    : 'bg-white/70 hover:bg-white border border-slate-200/80'"
                >
                  <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                      <span class="w-5 h-5 rounded-md bg-[#005851] text-white text-[9px] font-mono font-black flex items-center justify-center shrink-0">
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
                        <span class="text-xs font-black text-[#005851] font-mono">{{ office.total || 0 }}</span>
                        <span class="text-[9px] text-slate-400 ml-0.5">dok</span>
                      </div>
                      <button
                        type="button"
                        class="w-6 h-6 rounded-md bg-slate-100 hover:bg-[#00A99D] hover:text-white text-slate-400 flex items-center justify-center transition-colors"
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
                      :class="selectedOffice?.no === office.no ? 'bg-[#00A99D]' : 'bg-[#99CC33]/70'"
                      :style="{ width: `${Math.max(8, ((office.total || 0) / maxOfficeTotal) * 100)}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Cockpit Footer Summary -->
            <div class="pt-2 mt-2 border-t border-slate-200 text-[11px] text-slate-500 flex items-center justify-between font-mono">
              <span>Target: <strong class="text-[#005851] font-sans">{{ selectedOffice?.short_name || '-' }}</strong></span>
              <span class="text-[#005851] font-bold">{{ selectedOffice?.total || 0 }} Dok</span>
            </div>
          </div>

        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- INTEGRATED SINKARKES EXECUTIVE SUMMARY, CHARTS & TOTAL SUMMARY TABLE       -->
      <!-- ========================================================================= -->
      <div id="sinkarkes-section" v-if="dashboardViewMode !== 'geospatial'" class="space-y-3.5 font-sans">
        
        <!-- SINKARKES Header & Filter Controls Banner -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
          <div class="px-4 py-3 bg-gradient-to-r from-[#003B36] via-[#004D44] to-[#005851] text-white flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-8 h-8 rounded-xl bg-[#00A99D] text-white flex items-center justify-center shadow-md shadow-[#00A99D]/30 border border-[#99DFD6]/40 shrink-0">
                <BarChart3 class="w-4 h-4" />
              </div>
              <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <h2 class="text-sm sm:text-base font-extrabold tracking-tight text-white">
                    EXECUTIVE SUMMARY &amp; REKAPITULASI SINKARKES ({{ selectedSinkarkesYear }})
                  </h2>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#99CC33]/20 text-[#99CC33] border border-[#99CC33]/40">
                    KSE &bull; UKP &bull; PRL
                  </span>
                </div>
                <p class="text-[11px] text-slate-200 truncate mt-0.5">
                  Ringkasan capaian kinerja kekarantinaan kesehatan terpadu: Alat Angkut, Orang, Dokumen Karantina, Layanan Poliklinik BKK &amp; Vaksinasi ICV
                </p>
              </div>
            </div>

            <!-- Wilker, Year, & Form Input Controls -->
            <div class="flex flex-wrap items-center gap-2 shrink-0">
              <!-- Wilayah Kerja Selector -->
              <div class="flex items-center gap-1.5 bg-[#002D29]/90 border border-[#00A99D]/40 rounded-xl px-2.5 py-1.5 text-white">
                <Building2 class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
                <select
                  v-model="selectedSinkarkesWilker"
                  @change="handleSinkarkesFilterChange"
                  class="bg-transparent text-xs font-bold text-white border-none focus:ring-0 cursor-pointer outline-none pr-1"
                  title="Filter Wilayah Kerja SINKARKES"
                >
                  <option value="all" class="text-slate-900">Semua Wilayah Kerja (Konsolidasi)</option>
                  <option v-for="w in sinkarkesWilkerOptions" :key="w" :value="w" class="text-slate-900">
                    {{ w }}
                  </option>
                </select>
              </div>

              <!-- Year Selector -->
              <div class="flex items-center gap-1.5 bg-[#002D29]/90 border border-[#00A99D]/40 rounded-xl px-2.5 py-1.5 text-white">
                <Calendar class="w-3.5 h-3.5 text-[#99CC33] shrink-0" />
                <select
                  v-model.number="selectedSinkarkesYear"
                  @change="handleSinkarkesFilterChange"
                  class="bg-transparent text-xs font-bold text-white border-none focus:ring-0 cursor-pointer outline-none pr-1"
                  title="Pilih Tahun Laporan SINKARKES"
                >
                  <option v-for="y in availableSinkarkesYears" :key="y" :value="y" class="text-slate-900">
                    Tahun {{ y }}
                  </option>
                </select>
              </div>

              <!-- Open Form Input Button -->
              <Link
                :href="getUrl(`/sinkarkes?wilker=${encodeURIComponent(selectedSinkarkesWilker !== 'all' ? selectedSinkarkesWilker : 'POS Bandara Husen')}&year=${selectedSinkarkesYear}`)"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-[#00A99D] hover:bg-[#008f85] border border-[#99DFD6]/40 transition-all shadow-sm cursor-pointer"
              >
                <FileSpreadsheet class="w-3.5 h-3.5" />
                <span>Formulir Input &rarr;</span>
              </Link>
            </div>
          </div>

          <!-- 6 Key Performance Metric Cards Grid -->
          <div class="p-3.5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 bg-slate-50/60">
            <!-- 1. Total Alat Angkut -->
            <div class="bg-white border border-slate-200/90 rounded-xl p-3 shadow-2xs hover:border-[#00A99D] transition-colors">
              <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-bold">
                <span>ALAT ANGKUT</span>
                <div class="w-6 h-6 rounded-lg bg-[#E6F7F5] text-[#005851] flex items-center justify-center">
                  <Plane class="w-3.5 h-3.5" />
                </div>
              </div>
              <div class="text-xl sm:text-2xl font-black text-[#005851] mt-1.5 font-mono">
                {{ (sinkarkesKpi.total_alat_angkut || 0).toLocaleString('id-ID') }}
              </div>
              <div class="text-[10px] text-slate-500 mt-1 flex items-center gap-1 truncate">
                <span>Pswt: <strong class="text-slate-700">{{ (sinkarkesKpi.total_pesawat || 0).toLocaleString('id-ID') }}</strong></span>
                <span>&bull;</span>
                <span>Kpl: <strong class="text-slate-700">{{ (sinkarkesKpi.total_kapal || 0).toLocaleString('id-ID') }}</strong></span>
              </div>
            </div>

            <!-- 2. Orang Terawasi -->
            <div class="bg-white border border-slate-200/90 rounded-xl p-3 shadow-2xs hover:border-amber-400 transition-colors">
              <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-bold">
                <span>ORANG TERAWASI</span>
                <div class="w-6 h-6 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                  <Users class="w-3.5 h-3.5" />
                </div>
              </div>
              <div class="text-xl sm:text-2xl font-black text-[#005851] mt-1.5 font-mono">
                {{ (sinkarkesKpi.total_orang || 0).toLocaleString('id-ID') }}
              </div>
              <div class="text-[10px] text-slate-500 mt-1 flex items-center gap-1 truncate">
                <span>Pnp: <strong class="text-slate-700">{{ (sinkarkesKpi.total_penumpang || 0).toLocaleString('id-ID') }}</strong></span>
                <span>&bull;</span>
                <span>Kru: <strong class="text-slate-700">{{ (sinkarkesKpi.total_crew || 0).toLocaleString('id-ID') }}</strong></span>
              </div>
            </div>

            <!-- 3. Dokumen Kekarantinaan -->
            <div class="bg-white border border-slate-200/90 rounded-xl p-3 shadow-2xs hover:border-[#005851] transition-colors">
              <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-bold">
                <span>DOKUMEN KARANTINA</span>
                <div class="w-6 h-6 rounded-lg bg-[#E0EFEF] text-[#005851] flex items-center justify-center">
                  <FileCheck class="w-3.5 h-3.5" />
                </div>
              </div>
              <div class="text-xl sm:text-2xl font-black text-[#005851] mt-1.5 font-mono">
                {{ (sinkarkesKpi.total_dokumen || 0).toLocaleString('id-ID') }}
              </div>
              <div class="text-[10px] text-slate-500 mt-1 truncate">
                SSCEC, SSCC, P3K, Desinseksi
              </div>
            </div>

            <!-- 4. Layanan Medis -->
            <div class="bg-white border border-slate-200/90 rounded-xl p-3 shadow-2xs hover:border-sky-400 transition-colors">
              <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-bold">
                <span>LAYANAN MEDIS</span>
                <div class="w-6 h-6 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                  <HeartPulse class="w-3.5 h-3.5" />
                </div>
              </div>
              <div class="text-xl sm:text-2xl font-black text-[#005851] mt-1.5 font-mono">
                {{ (sinkarkesKpi.total_layanan_kesehatan || 0).toLocaleString('id-ID') }}
              </div>
              <div class="text-[10px] text-slate-500 mt-1 truncate">
                Poliklinik BKK &amp; Uji Laik
              </div>
            </div>

            <!-- 5. Vaksinasi ICV -->
            <div class="bg-white border border-slate-200/90 rounded-xl p-3 shadow-2xs hover:border-[#7CA622] transition-colors">
              <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-bold">
                <span>VAKSINASI ICV</span>
                <div class="w-6 h-6 rounded-lg bg-[#F7FCE8] text-[#557A18] flex items-center justify-center">
                  <ShieldCheck class="w-3.5 h-3.5" />
                </div>
              </div>
              <div class="text-xl sm:text-2xl font-black text-[#005851] mt-1.5 font-mono">
                {{ (sinkarkesKpi.total_vaksinasi || 0).toLocaleString('id-ID') }}
              </div>
              <div class="text-[10px] text-slate-500 mt-1 truncate">
                Yellow Fever, Meningitis, dll
              </div>
            </div>

            <!-- 6. Kepatuhan Pelaporan -->
            <div class="bg-white border border-slate-200/90 rounded-xl p-3 shadow-2xs hover:border-emerald-500 transition-colors">
              <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-bold">
                <span>KELENGKAPAN</span>
                <div class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                  <CalendarCheck class="w-3.5 h-3.5" />
                </div>
              </div>
              <div class="text-xl sm:text-2xl font-black text-[#005851] mt-1.5 font-mono">
                {{ sinkarkesKpi.compliance_rate || 0 }}%
              </div>
              <div class="text-[10px] text-slate-500 mt-1 truncate">
                {{ sinkarkesKpi.submitted_reports_count || 0 }} / {{ sinkarkesKpi.total_possible_reports || 84 }} bulan terisi
              </div>
            </div>
          </div>
        </div>

        <!-- SINKARKES Charts Row 1: Monthly Traffic Trend (2 Cols) & Document Composition (1 Col) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3.5">
          <!-- Monthly Traffic Trend Chart -->
          <div class="lg:col-span-2 bg-white border border-slate-200/90 rounded-2xl p-4 shadow-2xs flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-full bg-[#00A99D]"></span>
                  <h3 class="text-xs sm:text-sm font-bold text-slate-900">
                    Tren Bulanan Pengawasan Alat Angkut &amp; Orang ({{ selectedSinkarkesYear }})
                  </h3>
                </div>
                <span class="text-[11px] text-slate-400 font-mono">12 Bulan</span>
              </div>
              <p class="text-[11px] text-slate-500 mb-2">
                Dinamika volume kedatangan/keberangkatan pesawat, kapal laut, penumpang, dan kru sepanjang tahun.
              </p>
            </div>
            <div class="w-full h-[270px]">
              <BaseChart :option="sinkarkesTrafficOption" height="270px" />
            </div>
          </div>

          <!-- Document & Certificate Composition -->
          <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-2xs flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-full bg-[#005851]"></span>
                  <h3 class="text-xs sm:text-sm font-bold text-slate-900">
                    Komposisi Dokumen Kekarantinaan
                  </h3>
                </div>
                <PieChartIcon class="w-3.5 h-3.5 text-slate-400" />
              </div>
              <p class="text-[11px] text-slate-500 mb-2">
                Distribusi penerbitan SSCEC, SSCC, P3K, HAC, dan sertifikat penyehatan.
              </p>
            </div>
            <div class="w-full h-[270px]">
              <BaseChart :option="sinkarkesDocumentOption" height="270px" />
            </div>
          </div>
        </div>

        <!-- SINKARKES Charts Row 2: Cross-Wilker Comparison & Health Services -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5">
          <!-- Cross-Wilker Operational Volume Comparison -->
          <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-2xs flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-full bg-[#7CA622]"></span>
                  <h3 class="text-xs sm:text-sm font-bold text-slate-900">
                    Perbandingan Volume Lintas 7 Wilayah Kerja
                  </h3>
                </div>
                <span class="text-[11px] text-slate-400 font-mono">BKK Bandung</span>
              </div>
              <p class="text-[11px] text-slate-500 mb-2">
                Komparasi beban aktivitas alat angkut, orang, dan dokumen antar pos dan wilayah kerja.
              </p>
            </div>
            <div class="w-full h-[270px]">
              <BaseChart :option="sinkarkesWilkerOption" height="270px" />
            </div>
          </div>

          <!-- Health Services & Vaccines Monthly Stack -->
          <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-2xs flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-full bg-sky-600"></span>
                  <h3 class="text-xs sm:text-sm font-bold text-slate-900">
                    Layanan Kesehatan Pelabuhan &amp; Vaksinasi ICV
                  </h3>
                </div>
                <span class="text-[11px] text-slate-400 font-mono">UKP</span>
              </div>
              <p class="text-[11px] text-slate-500 mb-2">
                Kunjungan poliklinik BKK, penerbitan surat keterangan sehat, dan vaksinasi haji/umrah/internasional.
              </p>
            </div>
            <div class="w-full h-[270px]">
              <BaseChart :option="sinkarkesHealthOption" height="270px" />
            </div>
          </div>
        </div>

        <!-- SINKARKES Total Summary Table (Jan - Des + Total Tahunan) -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 sm:p-5 shadow-2xs space-y-3.5">
          <!-- Header & Top Controls -->
          <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 border-b border-slate-100 pb-3.5">
            <div>
              <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#E6F7F5] text-[#005851] border border-[#99DFD6] mb-1">
                <Table class="w-3.5 h-3.5 text-[#00A99D]" />
                <span>TOTAL SUMMARY &amp; REKAPITULASI SINKARKES (JAN &ndash; DES {{ selectedSinkarkesYear }})</span>
              </div>
              <h3 class="text-sm sm:text-base font-bold text-slate-900">
                Tabel Total Summary Bulanan &amp; Capaian Tahunan
              </h3>
              <p class="text-xs text-slate-500 mt-0.5">
                Rekapitulasi total capaian indikator utama per bulan Januari s/d Desember beserta akumulasi total tahunan {{ sinkarkesData.is_consolidated !== false ? 'seluruh Wilayah Kerja' : selectedSinkarkesWilker }}.
              </p>
            </div>

            <!-- Tab Switcher: Indikator vs Wilker vs Status Matriks -->
            <div class="flex flex-wrap items-center gap-1.5 bg-slate-100 p-1 rounded-xl border border-slate-200/80 self-start lg:self-auto">
              <button
                type="button"
                @click="activeSummaryTab = 'indicators'"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer"
                :class="activeSummaryTab === 'indicators' ? 'bg-[#005851] text-white shadow-xs font-bold' : 'text-slate-600 hover:text-[#005851]'"
              >
                <Activity class="w-3.5 h-3.5" :class="activeSummaryTab === 'indicators' ? 'text-[#99CC33]' : 'text-[#00A99D]'" />
                <span>Rekap Indikator Utama</span>
              </button>
              <button
                type="button"
                @click="activeSummaryTab = 'wilkers'"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer"
                :class="activeSummaryTab === 'wilkers' ? 'bg-[#005851] text-white shadow-xs font-bold' : 'text-slate-600 hover:text-[#005851]'"
              >
                <Building2 class="w-3.5 h-3.5" :class="activeSummaryTab === 'wilkers' ? 'text-[#99CC33]' : 'text-[#7CA622]'" />
                <span>Rekap per Wilker</span>
              </button>
              <button
                type="button"
                @click="activeSummaryTab = 'compliance'"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer"
                :class="activeSummaryTab === 'compliance' ? 'bg-[#005851] text-white shadow-xs font-bold' : 'text-slate-600 hover:text-[#005851]'"
              >
                <CalendarCheck class="w-3.5 h-3.5" :class="activeSummaryTab === 'compliance' ? 'text-[#99CC33]' : 'text-sky-500'" />
                <span>Status Pengisian</span>
              </button>
            </div>
          </div>

          <!-- Filter Bar (For Indikator View) -->
          <div v-if="activeSummaryTab === 'indicators'" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
            <!-- Section Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
              <button
                v-for="sec in [
                  { id: 'ALL', label: 'Semua', count: sectionCounts.ALL },
                  { id: 'KSE', label: 'KSE (Karantina)', count: sectionCounts.KSE },
                  { id: 'UKP', label: 'UKP (Layanan Medis)', count: sectionCounts.UKP },
                  { id: 'PRL', label: 'PRL (Lingkungan)', count: sectionCounts.PRL }
                ]"
                :key="sec.id"
                type="button"
                @click="selectedSectionFilter = sec.id"
                class="px-2.5 py-1 rounded-lg text-xs font-medium transition-all shrink-0 cursor-pointer flex items-center gap-1.5 border"
                :class="selectedSectionFilter === sec.id
                  ? 'bg-[#00A99D] text-white border-[#00A99D] shadow-xs font-bold'
                  : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
              >
                <span>{{ sec.label }}</span>
                <span
                  class="px-1.5 py-0.2 rounded-full text-[10px] font-mono"
                  :class="selectedSectionFilter === sec.id ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'"
                >
                  {{ sec.count }}
                </span>
              </button>
            </div>

            <!-- Search Box -->
            <div class="relative w-full sm:w-64 shrink-0">
              <Search class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
              <input
                v-model="summarySearchQuery"
                type="text"
                placeholder="Cari nama indikator atau kode..."
                class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-[#00A99D] text-slate-800"
              />
            </div>
          </div>

          <!-- TAB 1: Tabel Indikator Utama (Jan - Des + Total Tahunan) -->
          <div v-if="activeSummaryTab === 'indicators'" class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full min-w-[980px] text-left text-xs border-collapse">
              <thead>
                <tr class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                  <th class="py-2.5 px-3 w-20 text-center sticky left-0 bg-slate-50 z-10 shadow-xs">Kode</th>
                  <th class="py-2.5 px-3 min-w-[240px] sticky left-20 bg-slate-50 z-10 shadow-xs">Uraian Indikator</th>
                  <th class="py-2.5 px-2 text-center w-14">Satuan</th>
                  <th
                    v-for="m in monthNames"
                    :key="m"
                    class="py-2.5 px-2 text-right w-14 font-mono text-[10px]"
                  >
                    {{ m }}
                  </th>
                  <th class="py-2.5 px-3 text-right w-24 bg-[#E6F7F5] text-[#005851] font-mono font-bold text-[11px]">
                    TOTAL TAHUN
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="row in filteredSummaryItems"
                  :key="row.code"
                  class="hover:bg-[#E6F7F5]/30 transition-colors group"
                >
                  <td class="py-2 px-3 font-mono font-bold text-[11px] text-center text-slate-600 sticky left-0 bg-white group-hover:bg-[#f2fbfa] z-10 shadow-xs">
                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700">
                      {{ row.code }}
                    </span>
                  </td>

                  <td class="py-2 px-3 text-slate-800 font-medium sticky left-20 bg-white group-hover:bg-[#f2fbfa] z-10 shadow-xs">
                    <div class="flex items-center gap-1.5">
                      <span
                        class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider shrink-0"
                        :class="{
                          'bg-blue-50 text-blue-600': row.section === 'KSE',
                          'bg-emerald-50 text-emerald-600': row.section === 'UKP',
                          'bg-amber-50 text-amber-600': row.section === 'PRL',
                        }"
                      >
                        {{ row.section }}
                      </span>
                      <span class="truncate">{{ row.label }}</span>
                    </div>
                  </td>

                  <td class="py-2 px-2 text-center text-slate-400 font-mono text-[10px]">
                    {{ row.unit }}
                  </td>

                  <td
                    v-for="mId in 12"
                    :key="mId"
                    class="py-2 px-2 text-right font-mono text-[11px]"
                    :class="row.months[mId] > 0 ? 'text-slate-800 font-medium' : 'text-slate-300'"
                  >
                    {{ row.months[mId] > 0 ? row.months[mId].toLocaleString('id-ID') : '-' }}
                  </td>

                  <td class="py-2 px-3 text-right font-mono font-bold text-xs bg-[#E6F7F5]/50 text-[#005851] group-hover:bg-[#E6F7F5] transition-colors">
                    {{ row.total_year > 0 ? row.total_year.toLocaleString('id-ID') : '-' }}
                  </td>
                </tr>

                <tr v-if="filteredSummaryItems.length === 0">
                  <td colspan="16" class="py-8 text-center text-slate-400 italic">
                    Tidak ada indikator yang sesuai dengan filter pencarian.
                  </td>
                </tr>
              </tbody>

              <!-- Grand Total Row -->
              <tfoot class="bg-slate-100/90 border-t-2 border-slate-300 font-bold">
                <tr>
                  <td colspan="2" class="py-2.5 px-3 text-slate-800 sticky left-0 bg-slate-100 z-10 shadow-xs text-xs uppercase tracking-wider">
                    TOTAL KESELURUHAN (GRAND TOTAL)
                  </td>
                  <td class="py-2.5 px-2 text-center text-slate-500 font-mono text-[10px]">
                    Total
                  </td>
                  <td
                    v-for="mId in 12"
                    :key="mId"
                    class="py-2.5 px-2 text-right font-mono text-[11px] text-slate-900"
                  >
                    {{ (sinkarkesSummaryTable?.monthly_totals?.[mId] || 0) > 0 ? sinkarkesSummaryTable.monthly_totals[mId].toLocaleString('id-ID') : '-' }}
                  </td>
                  <td class="py-2.5 px-3 text-right font-mono font-extrabold text-sm bg-[#00A99D]/20 text-[#005851]">
                    {{ (sinkarkesSummaryTable?.total_year || 0).toLocaleString('id-ID') }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- TAB 2: Tabel Rekapitulasi per Wilayah Kerja -->
          <div v-else-if="activeSummaryTab === 'wilkers'" class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full min-w-[980px] text-left text-xs border-collapse">
              <thead>
                <tr class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                  <th class="py-2.5 px-3 min-w-[220px] sticky left-0 bg-slate-50 z-10 shadow-xs">Wilayah Kerja / Pos</th>
                  <th class="py-2.5 px-2 text-center w-28">Kepatuhan</th>
                  <th
                    v-for="m in monthNames"
                    :key="m"
                    class="py-2.5 px-2 text-right w-14 font-mono text-[10px]"
                  >
                    {{ m }}
                  </th>
                  <th class="py-2.5 px-3 text-right w-28 bg-[#F7FCE8] text-[#557A18] font-mono font-bold text-[11px]">
                    TOTAL TAHUN
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="wRow in (sinkarkesWilkerSummary?.items || [])"
                  :key="wRow.wilker"
                  class="hover:bg-[#E6F7F5]/30 transition-colors group"
                >
                  <td class="py-2.5 px-3 font-semibold text-slate-800 flex items-center gap-2 sticky left-0 bg-white group-hover:bg-[#f2fbfa] z-10 shadow-xs">
                    <Building2 class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
                    <span class="truncate">{{ wRow.wilker }}</span>
                  </td>

                  <td class="py-2.5 px-2 text-center">
                    <span
                      class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold"
                      :class="wRow.compliance_percent >= 80 ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'"
                    >
                      {{ wRow.filled_months }}/12 bln ({{ wRow.compliance_percent }}%)
                    </span>
                  </td>

                  <td
                    v-for="mId in 12"
                    :key="mId"
                    class="py-2.5 px-2 text-right font-mono text-[11px]"
                    :class="wRow.months[mId] > 0 ? 'text-slate-800 font-medium' : 'text-slate-300'"
                  >
                    {{ wRow.months[mId] > 0 ? wRow.months[mId].toLocaleString('id-ID') : '-' }}
                  </td>

                  <td class="py-2.5 px-3 text-right font-mono font-bold text-xs bg-[#F7FCE8]/60 text-[#557A18] group-hover:bg-[#F7FCE8] transition-colors">
                    {{ wRow.total_year > 0 ? wRow.total_year.toLocaleString('id-ID') : '-' }}
                  </td>
                </tr>
              </tbody>

              <!-- Grand Total Row -->
              <tfoot class="bg-slate-100/90 border-t-2 border-slate-300 font-bold">
                <tr>
                  <td class="py-2.5 px-3 text-slate-800 sticky left-0 bg-slate-100 z-10 shadow-xs text-xs uppercase tracking-wider">
                    TOTAL KONSOLIDASI BKK BANDUNG
                  </td>
                  <td class="py-2.5 px-2 text-center text-slate-500 font-mono text-[10px]">
                    7 Wilker
                  </td>
                  <td
                    v-for="mId in 12"
                    :key="mId"
                    class="py-2.5 px-2 text-right font-mono text-[11px] text-slate-900"
                  >
                    {{ (sinkarkesWilkerSummary?.monthly_totals?.[mId] || 0) > 0 ? sinkarkesWilkerSummary.monthly_totals[mId].toLocaleString('id-ID') : '-' }}
                  </td>
                  <td class="py-2.5 px-3 text-right font-mono font-extrabold text-sm bg-[#99CC33]/25 text-[#557A18]">
                    {{ (sinkarkesWilkerSummary?.total_year || 0).toLocaleString('id-ID') }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- TAB 3: Status Pengisian (Matriks Kepatuhan Dot Table) -->
          <div v-else-if="activeSummaryTab === 'compliance'" class="space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-500 px-1">
              <span>Klik pada sel bulan untuk membuka dan mengisi formulir bulanan wilayah kerja terkait.</span>
              <div class="flex items-center gap-3">
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span><span>Terisi</span></div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-slate-200"></span><span>Kosong</span></div>
              </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200">
              <table class="w-full min-w-[760px] text-left text-xs border-collapse">
                <thead>
                  <tr class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <th class="py-2.5 px-3 min-w-[200px]">Wilayah Kerja</th>
                    <th
                      v-for="m in monthNames"
                      :key="m"
                      class="py-2.5 px-2 text-center w-12 text-[10px]"
                    >
                      {{ m }}
                    </th>
                    <th class="py-2.5 px-3 text-center w-28">Kepatuhan</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr
                    v-for="row in sinkarkesMatrix"
                    :key="row.wilker"
                    class="hover:bg-slate-50/80 transition-colors"
                  >
                    <td class="py-2 px-3 font-semibold text-slate-800 flex items-center gap-2">
                      <Building2 class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
                      <span class="truncate">{{ row.wilker }}</span>
                    </td>
                    <td
                      v-for="mId in 12"
                      :key="mId"
                      class="py-1.5 px-1 text-center"
                    >
                      <button
                        type="button"
                        @click="openSinkarkesMonthForm(row.wilker, mId)"
                        class="w-8 h-8 rounded-lg flex flex-col items-center justify-center transition-all cursor-pointer mx-auto"
                        :class="[
                          row.months[mId]?.has_data
                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:scale-105'
                            : 'bg-slate-50 text-slate-300 border border-slate-200/60 hover:border-slate-300'
                        ]"
                        :title="row.months[mId]?.has_data ? `${row.wilker} - Bulan ${mId}: ${row.months[mId].filled_count} indikator terisi.` : `${row.wilker} - Bulan ${mId}: Belum ada data. Klik untuk mengisi.`"
                      >
                        <span
                          class="w-2 h-2 rounded-full"
                          :class="row.months[mId]?.has_data ? 'bg-emerald-500' : 'bg-slate-300'"
                        ></span>
                        <span v-if="row.months[mId]?.has_data" class="text-[9px] font-mono leading-none mt-0.5">
                          {{ row.months[mId].filled_count }}
                        </span>
                      </button>
                    </td>
                    <td class="py-2 px-3 text-center">
                      <div class="flex items-center justify-center gap-2">
                        <div class="w-16 h-2 bg-slate-200 rounded-full overflow-hidden">
                          <div
                            class="h-full rounded-full transition-all duration-300"
                            :class="row.compliance_percent >= 80 ? 'bg-emerald-500' : (row.compliance_percent >= 40 ? 'bg-[#00A99D]' : 'bg-amber-500')"
                            :style="{ width: `${row.compliance_percent}%` }"
                          ></div>
                        </div>
                        <span class="text-xs font-mono font-bold text-slate-700 min-w-[36px]">
                          {{ row.compliance_percent }}%
                        </span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- ANALYTICS COCKPIT: DYNAMIC TREND + OPERATIONAL RISK FOCUS                 -->
      <!-- ========================================================================= -->
      <div v-if="dashboardViewMode !== 'sinkarkes'" class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 font-sans">
        
        <!-- 30-Day Trend Multi-Series ECharts (7 Cols) -->
        <div class="lg:col-span-7 rounded-2xl p-4 bg-white border border-slate-200/90 shadow-2xs flex flex-col justify-between">
          <div>
            <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
              <div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-900 tracking-tight flex items-center gap-1.5">
                  <ChartLine class="w-4 h-4 text-[#00A99D]" />
                  Tren Pengawasan 30 Hari Terakhir
                </h3>
              </div>

              <!-- Line vs Bar Mode -->
              <div class="inline-flex items-center p-0.5 rounded-lg bg-slate-100 border border-slate-200">
                <button
                  type="button"
                  @click="trendViewMode = 'line'"
                  class="p-1 rounded transition-all cursor-pointer"
                  :class="trendViewMode === 'line' ? 'bg-[#005851] text-white shadow-2xs font-bold' : 'text-slate-400 hover:text-slate-700'"
                  title="Grafik Garis"
                >
                  <ChartLine class="w-3.5 h-3.5" />
                </button>
                <button
                  type="button"
                  @click="trendViewMode = 'bar'"
                  class="p-1 rounded transition-all cursor-pointer"
                  :class="trendViewMode === 'bar' ? 'bg-[#005851] text-white shadow-2xs font-bold' : 'text-slate-400 hover:text-slate-700'"
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
              <span class="w-3 h-3 rounded flex items-center justify-center text-white" :class="seriesVisibility.total ? 'bg-[#005851]' : 'bg-slate-200'">
                <Check class="w-2 h-2 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.total ? 'font-bold text-[#005851]' : 'text-slate-400'">Total ({{ kpi.total }})</span>
            </button>

            <button type="button" @click="toggleSeries('aircraft')" class="inline-flex items-center gap-1 cursor-pointer">
              <span class="w-3 h-3 rounded flex items-center justify-center text-white" :class="seriesVisibility.aircraft ? 'bg-[#00A99D]' : 'bg-slate-200'">
                <Check class="w-2 h-2 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.aircraft ? 'font-bold text-[#005851]' : 'text-slate-400'">Pesawat ({{ aircraft_stats.total }})</span>
            </button>

            <button type="button" @click="toggleSeries('ship')" class="inline-flex items-center gap-1 cursor-pointer">
              <span class="w-3 h-3 rounded flex items-center justify-center text-white" :class="seriesVisibility.ship ? 'bg-[#007D74]' : 'bg-slate-200'">
                <Check class="w-2 h-2 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.ship ? 'font-bold text-[#005851]' : 'text-slate-400'">Kapal ({{ ship_stats.total }})</span>
            </button>

            <button type="button" @click="toggleSeries('alat')" class="inline-flex items-center gap-1 cursor-pointer">
              <span class="w-3 h-3 rounded flex items-center justify-center text-white" :class="seriesVisibility.alat ? 'bg-[#7CA622]' : 'bg-slate-200'">
                <Check class="w-2 h-2 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.alat ? 'font-bold text-[#557A18]' : 'text-slate-400'">Alkes ({{ medical_stats.alat }})</span>
            </button>

            <button type="button" @click="toggleSeries('obat')" class="inline-flex items-center gap-1 cursor-pointer">
              <span class="w-3 h-3 rounded flex items-center justify-center text-white" :class="seriesVisibility.obat ? 'bg-[#E11D48]' : 'bg-slate-200'">
                <Check class="w-2 h-2 stroke-[3]" />
              </span>
              <span :class="seriesVisibility.obat ? 'font-bold text-slate-800' : 'text-slate-400'">Obat ({{ medical_stats.obat }})</span>
            </button>
          </div>
        </div>

        <!-- Operational Risk & Treatment Focus (5 Cols) -->
        <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3">
          
          <!-- Card 1: Tindakan Karantina Pesawat -->
          <div class="rounded-2xl p-4 bg-white border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between mb-2">
              <h4 class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                <Plane class="w-3.5 h-3.5 text-[#00A99D]" />
                Tindakan Sanitasi Pesawat
              </h4>
              <span class="text-[11px] font-semibold text-[#005851] font-mono">{{ aircraft_stats.layak }} Layak &bull; {{ aircraft_stats.tidak_layak }} Tindakan</span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs">
              <div
                v-for="act in aircraft_stats.tindakan"
                :key="act.key"
                class="px-2.5 py-1.5 rounded-lg border border-[#E6F7F5] bg-[#E6F7F5]/40 flex items-center justify-between"
              >
                <span class="text-slate-600 text-[11px]">{{ act.label }}</span>
                <span class="font-bold text-[#005851] font-mono">{{ act.count }}</span>
              </div>
            </div>
          </div>

          <!-- Card 2: Temuan Risiko Kapal (32 Area IHR 2005) -->
          <div class="rounded-2xl p-4 bg-white border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between mb-2">
              <h4 class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                <Ship class="w-3.5 h-3.5 text-[#005851]" />
                Temuan Faktor Risiko Sanitasi Kapal
              </h4>
              <span class="text-[11px] font-semibold text-amber-700 font-mono">{{ ship_stats.ships_with_risk }} / {{ ship_stats.total }} Kapal</span>
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
      <div v-if="dashboardViewMode !== 'sinkarkes'" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden font-sans">
        
        <!-- Header & Tabs -->
        <div class="px-4 py-2.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2.5 text-xs">
          <div class="flex items-center gap-1.5">
            <button
              type="button"
              @click="activeFeedTab = 'inspections'"
              class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer flex items-center gap-1.5"
              :class="activeFeedTab === 'inspections' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 hover:text-[#005851] bg-slate-100'"
            >
              <ClipboardCheck class="w-3.5 h-3.5" />
              <span>Dokumen Pemeriksaan Terkini</span>
              <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-[#99CC33] text-[#003B36] font-black font-mono">
                {{ recent_inspections.length }}
              </span>
            </button>

            <button
              type="button"
              @click="activeFeedTab = 'activities'"
              class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer flex items-center gap-1.5"
              :class="activeFeedTab === 'activities' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 hover:text-[#005851] bg-slate-100'"
            >
              <Activity class="w-3.5 h-3.5" />
              <span>Audit Log ({{ recent_activities.length }})</span>
            </button>

            <button
              type="button"
              @click="activeFeedTab = 'logins'"
              class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer flex items-center gap-1.5"
              :class="activeFeedTab === 'logins' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 hover:text-[#005851] bg-slate-100'"
            >
              <LogIn class="w-3.5 h-3.5" />
              <span>Sesi Login ({{ recent_logins.length }})</span>
            </button>
          </div>

          <!-- Search & Status Filter -->
          <div v-if="activeFeedTab === 'inspections'" class="flex items-center gap-2">
            <select
              v-model="statusFilterFeed"
              class="px-2 py-1 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-800 font-medium focus:outline-none focus:border-[#00A99D]"
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
                class="w-full pl-6 pr-2 py-1 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-[#00A99D]"
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
                class="hover:bg-[#E6F7F5]/30 transition-colors"
              >
                <!-- Layanan Badge (Harmonized to Kemenkes Brand) -->
                <td class="py-2 px-3 whitespace-nowrap">
                  <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold"
                    :class="{
                      'bg-[#E6F7F5] text-[#005851] border border-[#99DFD6]': item.module === 'aircraft',
                      'bg-[#E0EFEF] text-[#005851] border border-[#99DFD6]': item.module === 'ship',
                      'bg-[#F7FCE8] text-[#557A18] border border-[#D6EC8E]': item.module === 'alat',
                      'bg-[#E6F7F5] text-[#005851] border border-[#99DFD6]': item.module === 'obat',
                    }"
                  >
                    <component :is="moduleIconMap[item.module] || Layers" class="w-3 h-3" />
                    {{ item.module_label }}
                  </span>
                </td>

                <td class="py-2 px-3 whitespace-nowrap font-mono font-bold text-[#005851]">
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
                    <MapPin class="w-3 h-3 text-[#00A99D]" />
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
                        'bg-[#E6F7F5] text-[#005851] border border-[#99DFD6]': item.status === 'approved',
                        'bg-amber-50 text-amber-700 border border-amber-200': item.status === 'submitted',
                        'bg-[#F7FCE8] text-[#557A18] border border-[#D6EC8E]': item.status === 'verified',
                        'bg-rose-50 text-rose-700 border border-rose-200': item.status === 'rejected',
                      }"
                    >
                      {{ item.status_label }}
                    </span>
                    <span
                      class="text-[11px] font-semibold"
                      :class="item.result_ok ? 'text-[#00A99D] font-bold' : 'text-rose-600 font-bold'"
                    >
                      {{ item.result }}
                    </span>
                  </div>
                </td>

                <td class="py-2 px-3 whitespace-nowrap text-right">
                  <Link
                    :href="getUrl(item.url)"
                    class="inline-flex items-center gap-0.5 px-2.5 py-0.5 rounded text-[11px] font-semibold bg-[#E6F7F5] hover:bg-[#00A99D] text-[#005851] hover:text-white transition-all border border-[#99DFD6]/60"
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
              <div class="w-6 h-6 rounded-full bg-[#E6F7F5] text-[#005851] flex items-center justify-center font-bold text-[10px] shrink-0 font-mono">
                {{ act.causer_name.charAt(0) }}
              </div>
              <div>
                <div class="font-bold text-slate-900">
                  {{ act.causer_name }}
                  <span class="font-normal text-slate-500">melakukan</span>
                  <span class="font-semibold text-[#005851]">{{ act.event || 'aktivitas' }}</span>
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
              <div class="w-6 h-6 rounded-full bg-[#E6F7F5] text-[#005851] flex items-center justify-center shrink-0">
                <LogIn class="w-3 h-3" />
              </div>
              <div>
                <div class="font-bold text-slate-900">{{ lh.user_name }} ({{ lh.email }})</div>
                <div class="text-[10px] text-slate-500 font-mono">IP: {{ lh.ip_address }} &bull; Perangkat: {{ lh.device }}</div>
              </div>
            </div>
            <div class="text-right">
              <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-[#E6F7F5] text-[#005851] border border-[#99DFD6] font-mono">
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
