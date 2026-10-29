<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import BaseChart from '../../Components/BaseChart.vue';
import {
  MoreHorizontal,
  ExternalLink,
  Compass,
  MessageSquare,
  ThumbsUp,
  ClipboardCheck,
  Plane,
  Layers,
  Download,
  User as UserIcon,
  ShieldCheck,
  History,
  BookOpen,
  Activity
} from 'lucide-vue-next';

const props = defineProps({
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
    default: () => ({ dates: [], counts: [] }),
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

// Active Tab for Bottom Floating List ('activity' | 'users' | 'modules')
const activeTab = ref('activity');

// Theme Detection
const isDark = ref(false);
const updateThemeState = () => {
  if (typeof document !== 'undefined') {
    isDark.value = document.documentElement.classList.contains('dark');
  }
};

let themeObserver = null;
onMounted(() => {
  updateThemeState();
  if (typeof MutationObserver !== 'undefined' && typeof document !== 'undefined') {
    themeObserver = new MutationObserver(() => updateThemeState());
    themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
  }
});

onUnmounted(() => {
  if (themeObserver) {
    themeObserver.disconnect();
    themeObserver = null;
  }
});

// Computed Percentages & Display Metrics
const totalInspections = computed(() => props.metrics?.total_inspections || 20);
const layakInspections = computed(() => props.metrics?.layak_inspections ?? 16);
const tidakLayakInspections = computed(() => props.metrics?.tidak_layak_inspections ?? 4);

const compliancePct = computed(() => {
  const tot = layakInspections.value + tidakLayakInspections.value;
  if (tot <= 0) return 80;
  return Math.round((layakInspections.value / tot) * 100);
});

const actionPct = computed(() => 100 - compliancePct.value);

const heroTotalFormatted = computed(() => {
  const raw = (props.metrics?.total_activities || 0) + (props.metrics?.total_inspections || 0) + (props.metrics?.total_users || 0);
  if (raw < 100) {
    return `${raw}.0000K`;
  }
  return `${(raw / 1000).toFixed(4)}K`;
});

// 1. Coral-Pink Gradient Hero Card Wave Chart (3 smooth intersecting waves)
const heroWaveOption = computed(() => {
  const baseCounts = props.chart_data?.counts?.length ? props.chart_data.counts : [12, 18, 14, 22, 16, 25, 29];
  const wave1 = baseCounts.map((v, i) => v + (i % 2 === 0 ? 4 : -2));
  const wave2 = baseCounts.map((v, i) => Math.max(2, v + (i % 2 === 0 ? -3 : 5)));
  const wave3 = baseCounts.map((v, i) => Math.max(4, Math.round(v * 0.75) + (i * 2)));

  return {
    grid: {
      left: 0,
      right: 0,
      top: 10,
      bottom: 10,
    },
    xAxis: {
      type: 'category',
      show: false,
      boundaryGap: false,
      data: props.chart_data?.dates?.length ? props.chart_data.dates : ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
    },
    yAxis: {
      type: 'value',
      show: false,
    },
    series: [
      {
        type: 'line',
        smooth: 0.55,
        symbol: 'none',
        data: wave1,
        lineStyle: { width: 2, color: 'rgba(255, 255, 255, 0.9)' },
      },
      {
        type: 'line',
        smooth: 0.55,
        symbol: 'none',
        data: wave2,
        lineStyle: { width: 2, color: '#FFD166' },
      },
      {
        type: 'line',
        smooth: 0.55,
        symbol: 'none',
        data: wave3,
        lineStyle: { width: 2, color: '#7B2CBF' },
        markLine: {
          silent: true,
          symbol: 'none',
          lineStyle: {
            color: 'rgba(255, 255, 255, 0.35)',
            type: 'solid',
            width: 1,
          },
          data: [{ yAxis: Math.round( wave1.reduce((a, b) => a + b, 0) / wave1.length ) }],
          label: { show: false },
        },
      },
    ],
  };
});

// 2. Multi-Segment Rounded Donut Chart (Matches "Pending Messages" donut in reference image)
const donutOption = computed(() => {
  return {
    tooltip: {
      trigger: 'item',
      formatter: '{b}: {c} ({d}%)',
    },
    series: [
      // Outer subtle track ring
      {
        type: 'pie',
        radius: ['84%', '94%'],
        silent: true,
        label: { show: false },
        data: [
          {
            value: 100,
            itemStyle: {
              color: isDark.value ? 'rgba(255,255,255,0.04)' : '#F5F4F0',
            },
          },
        ],
      },
      // Main Multi-Segment Rounded Ring
      {
        name: 'Distribusi Status',
        type: 'pie',
        radius: ['66%', '79%'],
        avoidLabelOverlap: false,
        padAngle: 4,
        itemStyle: {
          borderRadius: 12,
        },
        label: { show: false },
        data: [
          {
            value: Math.max(1, Math.round(compliancePct.value * 0.45)),
            name: 'Terverifikasi BKK',
            itemStyle: { color: isDark.value ? '#F8FAFC' : '#111322' },
          },
          {
            value: Math.max(1, Math.round(compliancePct.value * 0.35)),
            name: 'Layak Sanitasi',
            itemStyle: { color: '#FF7556' },
          },
          {
            value: Math.max(1, Math.round(compliancePct.value * 0.2)),
            name: 'Sertifikat Aktif',
            itemStyle: { color: '#F84593' },
          },
          {
            value: Math.max(1, actionPct.value),
            name: 'Tindakan Penyehatan',
            itemStyle: { color: '#FFD166' },
          },
        ],
      },
    ],
  };
});

// 3. Right-Column "Post Stats" Vertical Pill Bar + Smooth Spline Chart (M T W T F S S)
const postStatsOption = computed(() => {
  const rawCounts = props.chart_data?.counts?.length === 7
    ? props.chart_data.counts
    : [42, 65, 54, 96, 62, 70, 58];
  const maxVal = Math.max(...rawCounts, 10);
  const peakIdx = rawCounts.indexOf(Math.max(...rawCounts));

  const barData = rawCounts.map((val, idx) => {
    const isPeak = idx === (peakIdx !== -1 ? peakIdx : 3);
    return {
      value: Math.max(val, Math.round(maxVal * 0.35)),
      itemStyle: {
        borderRadius: [16, 16, 16, 16],
        color: isPeak
          ? {
              type: 'linear',
              x: 0,
              y: 0,
              x2: 0,
              y2: 1,
              colorStops: [
                { offset: 0, color: '#FF7E55' },
                { offset: 1, color: '#F84593' },
              ],
            }
          : (isDark.value ? '#262A40' : '#D9D8D4'),
      },
    };
  });

  const waveDark = rawCounts.map((v, i) => Math.max(1, Math.round(v * 0.48) + (i % 2 === 0 ? 4 : -3)));
  const waveCoral = rawCounts.map((v, i) => Math.max(1, Math.round(v * 0.42) + (i % 2 === 0 ? -3 : 5)));

  return {
    tooltip: {
      trigger: 'axis',
      backgroundColor: isDark.value ? '#111322' : '#FFFFFF',
      borderColor: isDark.value ? '#262A40' : '#E2E8F0',
      textStyle: { color: isDark.value ? '#F8FAFC' : '#111322', fontSize: 12 },
    },
    grid: {
      left: '4%',
      right: '4%',
      top: '10%',
      bottom: '14%',
      containLabel: false,
    },
    xAxis: {
      type: 'category',
      data: ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
      axisLine: { show: false },
      axisTick: { show: false },
      axisLabel: {
        color: isDark.value ? '#94A3B8' : '#A0A09C',
        fontSize: 11,
        fontWeight: 600,
        margin: 14,
      },
    },
    yAxis: {
      type: 'value',
      show: false,
    },
    series: [
      {
        name: 'Volume Harian',
        type: 'bar',
        barWidth: 14,
        data: barData,
        z: 2,
      },
      {
        name: 'Tren Utama',
        type: 'line',
        smooth: 0.5,
        symbol: 'none',
        data: waveDark,
        lineStyle: {
          width: 1.5,
          color: isDark.value ? '#F8FAFC' : '#111322',
        },
        z: 3,
      },
      {
        name: 'Tren Sekunder',
        type: 'line',
        smooth: 0.5,
        symbol: 'none',
        data: waveCoral,
        lineStyle: {
          width: 1.5,
          color: '#FF7556',
        },
        z: 3,
      },
    ],
  };
});

// Enriched Floating Rows for "Post Activity" Tab
const floatingActivityRows = computed(() => {
  const categories = ['Company', 'BKK Aviation', 'Audit Trail', 'Persona', 'Security', 'System'];
  const metricsLabels = ['7.k', '3.k', '2.k', '1.k', '4.k', '6.k'];
  const trends = [
    { label: '↓ 4%', up: false },
    { label: '↑ 5%', up: true },
    { label: '↑ 6%', up: true },
    { label: '↑ 8%', up: true },
    { label: '↓ 2%', up: false },
    { label: '↑ 9%', up: true },
  ];
  const avatarBgColors = ['FFD166', 'FF7556', 'F84593', '111322', '06D6A0', '118AB2'];

  if (props.recent_activities && props.recent_activities.length > 0) {
    return props.recent_activities.slice(0, 5).map((item, idx) => ({
      id: item.id,
      title: item.description,
      subtitle: `Oleh: ${item.causer_name} • ${item.created_at}`,
      category: item.subject_type || categories[idx % categories.length],
      metric: metricsLabels[idx % metricsLabels.length],
      trend: trends[idx % trends.length],
      avatar: item.causer_avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(item.causer_name || 'S')}&background=${avatarBgColors[idx % avatarBgColors.length]}&color=fff`,
      href: getUrl('/audit-logs'),
    }));
  }

  return [
    {
      id: 1,
      title: 'Inspeksi Sanitasi Armada BKK Bandung',
      subtitle: 'Post number 1 • Standar IHR 2005',
      category: 'BKK Aviation',
      metric: '7.k',
      trend: { label: '↓ 4%', up: false },
      avatar: 'https://ui-avatars.com/api/?name=BKK&background=FFD166&color=111322',
      href: getUrl('/aircraft-inspections'),
    },
    {
      id: 2,
      title: 'Dashboard Analitik Kelaikan Pesawat',
      subtitle: 'Post number 2 • ECharts Telemetry',
      category: 'Analytics',
      metric: '3.k',
      trend: { label: '↑ 5%', up: true },
      avatar: 'https://ui-avatars.com/api/?name=AN&background=FF7556&color=fff',
      href: getUrl('/aircraft-inspections/dashboard'),
    },
    {
      id: 3,
      title: 'Audit Trail & Pencatatan Spatie Otomatis',
      subtitle: 'Post number 3 • Keamanan Sistem',
      category: 'Security',
      metric: '2.k',
      trend: { label: '↑ 6%', up: true },
      avatar: 'https://ui-avatars.com/api/?name=AU&background=F84593&color=fff',
      href: getUrl('/audit-logs'),
    },
    {
      id: 4,
      title: 'Formulir Publik Pemeriksaan Pesawat',
      subtitle: 'Post number 4 • Layanan Mandiri',
      category: 'Public Form',
      metric: '1.k',
      trend: { label: '↑ 8%', up: true },
      avatar: 'https://ui-avatars.com/api/?name=FP&background=111322&color=fff',
      href: getUrl('/travelconsent/public_aircraft'),
    },
  ];
});

const weeklyTotal = computed(() => {
  if (props.chart_data?.counts?.length) {
    const sum = props.chart_data.counts.reduce((a, b) => a + b, 0);
    return sum > 0 ? sum : 874;
  }
  return 874;
});
</script>

<template>
  <Head title="Analytics &bull; Pusat Kontrol Sistem" />

  <HybridNavbar mode="inertia" currentRoute="dashboard" pageTitle="Analytics">
    <main class="w-full px-4 sm:px-6 lg:px-10 py-4 sm:py-6">
      <!-- Reference-Matched 12-Column Asymmetric Bento Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        
        <!-- LEFT / MAIN COLUMN (8 Columns) -->
        <div class="lg:col-span-8 space-y-7">
          
          <!-- Top Row: 2 Featured Analytics Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- CARD 1: Vibrant Coral-Pink Gradient Card ("Total Likes" style from reference) -->
            <div class="relative overflow-hidden rounded-[34px] bg-gradient-to-br from-[#FF7E55] via-[#FF5A76] to-[#F74292] p-6 sm:p-7 text-white shadow-[0_20px_45px_-15px_rgba(255,90,118,0.5)] flex flex-col justify-between min-h-[290px]">
              <!-- Top Title & Menu Dots -->
              <div>
                <div class="flex items-center justify-between">
                  <span class="text-xs sm:text-sm font-medium text-white/90 tracking-wide">
                    Total Activity &amp; Logs
                  </span>
                  <a :href="getUrl('/audit-logs')" class="p-1 rounded-lg hover:bg-white/15 transition-colors text-white/90" title="Lihat Detail Audit">
                    <MoreHorizontal class="w-5 h-5" />
                  </a>
                </div>

                <!-- Large Hero KPI Number -->
                <div class="text-3xl sm:text-[34px] font-extrabold tracking-tight mt-1.5">
                  {{ heroTotalFormatted }}
                </div>
              </div>

              <!-- Smooth Multi-Line Wave Chart inside Coral-Pink Card -->
              <div class="my-2 -mx-2">
                <BaseChart :option="heroWaveOption" height="105px" />
              </div>

              <!-- Bottom 3-Column Percentage Breakdown with Vertical Dashed Dividers -->
              <div class="grid grid-cols-3 pt-2">
                <div class="pr-3">
                  <span class="block text-[11px] font-medium text-white/80">Layak</span>
                  <span class="block text-lg sm:text-xl font-bold mt-1">%{{ compliancePct }}</span>
                </div>

                <div class="px-4 border-x border-dashed border-white/35">
                  <span class="block text-[11px] font-medium text-white/80">Audit</span>
                  <span class="block text-lg sm:text-xl font-bold mt-1">%50</span>
                </div>

                <div class="pl-4">
                  <span class="block text-[11px] font-medium text-white/80">Tindakan</span>
                  <span class="block text-lg sm:text-xl font-bold mt-1">%{{ actionPct }}</span>
                </div>
              </div>
            </div>

            <!-- CARD 2: Clean White Card with Multi-Segment Donut ("Pending Messages" style from reference) -->
            <div class="rounded-[34px] bg-white dark:bg-[#16192B] p-6 sm:p-7 shadow-[0_12px_35px_rgba(0,0,0,0.03)] flex flex-col justify-between min-h-[290px]">
              <!-- Header -->
              <div class="flex items-center justify-between">
                <span class="text-xs sm:text-sm font-bold text-[#111322] dark:text-white">
                  Inspeksi Sanitasi BKK
                </span>
                <a :href="getUrl('/aircraft-inspections/dashboard')" class="p-1 rounded-lg text-[#111322] dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 transition-colors" title="Buka Dashboard Sanitasi">
                  <MoreHorizontal class="w-5 h-5" />
                </a>
              </div>

              <!-- Body: Left KPI & Legend + Right Donut Chart with 3D Center Cross -->
              <div class="flex items-center justify-between gap-2 my-auto pt-2">
                <!-- Left Metrics & Legend -->
                <div class="space-y-5">
                  <div class="text-3xl sm:text-[34px] font-extrabold text-[#111322] dark:text-white tracking-tight">
                    {{ totalInspections }}.k
                  </div>

                  <div class="space-y-3">
                    <div>
                      <div class="flex items-center gap-2 text-xs text-slate-400 font-medium">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#FF7556]"></span>
                        <span>Layak Terbang</span>
                      </div>
                      <div class="text-sm font-extrabold text-[#111322] dark:text-white mt-0.5">
                        %{{ compliancePct }}
                      </div>
                    </div>

                    <div>
                      <div class="flex items-center gap-2 text-xs text-slate-400 font-medium">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#FFD166]"></span>
                        <span>Tindakan</span>
                      </div>
                      <div class="text-sm font-extrabold text-[#111322] dark:text-white mt-0.5">
                        %{{ actionPct }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Right Donut Ring with Custom 3D Coral Cross in Center -->
                <div class="relative w-40 h-40 sm:w-44 sm:h-44 shrink-0 flex items-center justify-center">
                  <BaseChart :option="donutOption" height="176px" />
                  <!-- 3D Stylized Coral Cross Icon in Donut Center (Matches reference image) -->
                  <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <svg viewBox="0 0 64 64" class="w-11 h-11 drop-shadow-[0_6px_10px_rgba(217,83,79,0.35)]" fill="none">
                      <defs>
                        <linearGradient id="cross3dA" x1="12" y1="12" x2="52" y2="52" gradientUnits="userSpaceOnUse">
                          <stop offset="0%" stop-color="#FF8A65" />
                          <stop offset="100%" stop-color="#C84B31" />
                        </linearGradient>
                        <linearGradient id="cross3dB" x1="52" y1="12" x2="12" y2="52" gradientUnits="userSpaceOnUse">
                          <stop offset="0%" stop-color="#FFAB91" />
                          <stop offset="100%" stop-color="#B83B2A" />
                        </linearGradient>
                      </defs>
                      <rect x="26" y="10" width="12" height="44" rx="6" transform="rotate(-35 32 32)" fill="url(#cross3dA)" />
                      <rect x="26" y="10" width="12" height="44" rx="6" transform="rotate(55 32 32)" fill="url(#cross3dB)" />
                      <circle cx="21" cy="24" r="3" fill="#FFCCBC" fill-opacity="0.6" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- Bottom Section: Tabbed Floating Row Cards ("Post Activity" & "User" in reference) -->
          <div class="space-y-4">
            <!-- Tabs Header -->
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/10 pb-3">
              <div class="flex items-center gap-7">
                <button
                  type="button"
                  @click="activeTab = 'activity'"
                  class="relative text-xs sm:text-sm font-bold transition-colors cursor-pointer pb-1"
                  :class="activeTab === 'activity'
                    ? 'text-[#111322] dark:text-white after:content-[\'\'] after:absolute after:left-0 after:right-0 after:-bottom-[13px] after:h-[2.5px] after:bg-[#111322] dark:after:bg-white after:rounded-full'
                    : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'"
                >
                  Post Activity
                </button>

                <button
                  type="button"
                  @click="activeTab = 'users'"
                  class="relative text-xs sm:text-sm font-bold transition-colors cursor-pointer pb-1"
                  :class="activeTab === 'users'
                    ? 'text-[#111322] dark:text-white after:content-[\'\'] after:absolute after:left-0 after:right-0 after:-bottom-[13px] after:h-[2.5px] after:bg-[#111322] dark:after:bg-white after:rounded-full'
                    : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'"
                >
                  User Sessions ({{ recent_logins.length }})
                </button>

                <button
                  type="button"
                  @click="activeTab = 'modules'"
                  class="relative text-xs sm:text-sm font-bold transition-colors cursor-pointer pb-1"
                  :class="activeTab === 'modules'
                    ? 'text-[#111322] dark:text-white after:content-[\'\'] after:absolute after:left-0 after:right-0 after:-bottom-[13px] after:h-[2.5px] after:bg-[#111322] dark:after:bg-white after:rounded-full'
                    : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'"
                >
                  Quick Modules
                </button>
              </div>

              <a
                :href="activeTab === 'activity' ? getUrl('/audit-logs') : getUrl('/aircraft-inspections')"
                class="text-xs font-bold text-[#FF5C77] hover:underline"
              >
                Lihat Semua →
              </a>
            </div>

            <!-- TAB 1: Floating White Pill Rows for Activity -->
            <div v-if="activeTab === 'activity'" class="space-y-3">
              <a
                v-for="row in floatingActivityRows"
                :key="row.id"
                :href="row.href"
                class="group bg-white dark:bg-[#16192B] rounded-[22px] px-4 sm:px-5 py-3.5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-[0_10px_28px_rgba(0,0,0,0.06)] transition-all flex items-center justify-between gap-4 cursor-pointer"
              >
                <!-- Left: Rounded Square Thumbnail + Title + Subtitle -->
                <div class="flex items-center gap-3.5 min-w-0 flex-1">
                  <img
                    :src="row.avatar"
                    :alt="row.title"
                    class="w-11 h-11 rounded-2xl object-cover shrink-0 shadow-2xs"
                  />
                  <div class="min-w-0">
                    <div class="flex items-center gap-2">
                      <span class="text-xs sm:text-[13px] font-bold text-[#111322] dark:text-white truncate group-hover:text-[#FF5C77] transition-colors">
                        {{ row.title }}
                      </span>
                      <ExternalLink class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600 shrink-0" />
                    </div>
                    <span class="block text-[11px] text-slate-400 mt-0.5 truncate">
                      {{ row.subtitle }}
                    </span>
                  </div>
                </div>

                <!-- Middle: Category Column -->
                <div class="hidden sm:block w-28 text-center">
                  <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">
                    {{ row.category }}
                  </span>
                </div>

                <!-- Right: Dark Navy Circle Icon + Metric + Soft Trend Badge + Dots -->
                <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                  <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-[#111322] dark:bg-white/10 text-white flex items-center justify-center">
                      <Compass class="w-3.5 h-3.5" />
                    </span>
                    <span class="text-xs font-bold text-[#111322] dark:text-white w-7">
                      {{ row.metric }}
                    </span>
                  </div>

                  <span
                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold min-w-[50px] text-center"
                    :class="row.trend.up
                      ? 'bg-[#E6F8F0] dark:bg-emerald-950/70 text-[#10B981]'
                      : 'bg-[#FFEAE8] dark:bg-rose-950/70 text-[#FF574A]'"
                  >
                    {{ row.trend.label }}
                  </span>

                  <span class="p-1 text-slate-400 group-hover:text-[#111322] dark:group-hover:text-white transition-colors">
                    <MoreHorizontal class="w-4 h-4" />
                  </span>
                </div>
              </a>
            </div>

            <!-- TAB 2: User Login Sessions in the Same Floating Pill Row Style -->
            <div v-else-if="activeTab === 'users'" class="space-y-3">
              <div
                v-for="(login, idx) in recent_logins"
                :key="login.id"
                class="bg-white dark:bg-[#16192B] rounded-[22px] px-4 sm:px-5 py-3.5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex items-center justify-between gap-4"
              >
                <div class="flex items-center gap-3.5 min-w-0 flex-1">
                  <img
                    :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(login.user_name)}&background=${idx % 2 === 0 ? 'FF7556' : '111322'}&color=fff`"
                    :alt="login.user_name"
                    class="w-11 h-11 rounded-2xl object-cover shrink-0"
                  />
                  <div class="min-w-0">
                    <div class="flex items-center gap-2">
                      <span class="text-xs sm:text-[13px] font-bold text-[#111322] dark:text-white truncate">
                        {{ login.user_name }}
                      </span>
                      <ShieldCheck class="w-3.5 h-3.5 text-emerald-500 shrink-0" />
                    </div>
                    <span class="block text-[11px] text-slate-400 mt-0.5 truncate">
                      IP: {{ login.ip_address }} &bull; {{ login.device }}
                    </span>
                  </div>
                </div>

                <div class="hidden sm:block w-28 text-center">
                  <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">
                    {{ login.login_at }}
                  </span>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                  <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-[#E6F8F0] dark:bg-emerald-950/70 text-[#10B981]">
                    {{ login.status }}
                  </span>
                  <a :href="getUrl('/profile')" class="p-1 text-slate-400 hover:text-[#111322] dark:hover:text-white">
                    <MoreHorizontal class="w-4 h-4" />
                  </a>
                </div>
              </div>

              <div v-if="recent_logins.length === 0" class="bg-white dark:bg-[#16192B] rounded-[22px] p-8 text-center text-xs text-slate-400">
                Belum ada riwayat sesi login tercatat.
              </div>
            </div>

            <!-- TAB 3: Quick System Modules & Exports -->
            <div v-else class="space-y-3">
              <a
                :href="getUrl('/aircraft-inspections')"
                class="group bg-white dark:bg-[#16192B] rounded-[22px] px-5 py-3.5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-md transition-all flex items-center justify-between gap-4"
              >
                <div class="flex items-center gap-3.5">
                  <div class="w-11 h-11 rounded-2xl bg-[#FCE2DF] text-[#FF574A] flex items-center justify-center">
                    <Plane class="w-5 h-5" />
                  </div>
                  <div>
                    <span class="text-xs sm:text-[13px] font-bold text-[#111322] dark:text-white group-hover:text-[#FF5C77]">
                      Manajemen Data Inspeksi Pesawat BKK
                    </span>
                    <span class="block text-[11px] text-slate-400 mt-0.5">
                      Verifikasi, cetak sertifikat PDF, dan ekspor Excel
                    </span>
                  </div>
                </div>
                <span class="px-3 py-1 rounded-lg text-[11px] font-bold bg-[#E6F8F0] text-[#10B981]">Buka Modul →</span>
              </a>

              <a
                :href="getUrl('/spa')"
                class="group bg-white dark:bg-[#16192B] rounded-[22px] px-5 py-3.5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-md transition-all flex items-center justify-between gap-4"
              >
                <div class="flex items-center gap-3.5">
                  <div class="w-11 h-11 rounded-2xl bg-[#E6F8F0] text-[#10B981] flex items-center justify-center">
                    <Layers class="w-5 h-5" />
                  </div>
                  <div>
                    <span class="text-xs sm:text-[13px] font-bold text-[#111322] dark:text-white group-hover:text-[#10B981]">
                      Mode Traditional SPA (Vue Router + Pinia)
                    </span>
                    <span class="block text-[11px] text-slate-400 mt-0.5">
                      Aplikasi client-side mandiri terhubung ke REST API
                    </span>
                  </div>
                </div>
                <span class="px-3 py-1 rounded-lg text-[11px] font-bold bg-[#FCE2DF] text-[#FF574A]">SPA Mode →</span>
              </a>

              <a
                :href="getUrl('/exports/audit-logs/pdf')"
                class="group bg-white dark:bg-[#16192B] rounded-[22px] px-5 py-3.5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-md transition-all flex items-center justify-between gap-4"
              >
                <div class="flex items-center gap-3.5">
                  <div class="w-11 h-11 rounded-2xl bg-slate-100 dark:bg-white/10 text-[#111322] dark:text-white flex items-center justify-center">
                    <Download class="w-5 h-5" />
                  </div>
                  <div>
                    <span class="text-xs sm:text-[13px] font-bold text-[#111322] dark:text-white">
                      Unduh Laporan Audit Trail Resmi (PDF)
                    </span>
                    <span class="block text-[11px] text-slate-400 mt-0.5">
                      Rekapitulasi aktivitas keamanan sistem
                    </span>
                  </div>
                </div>
                <span class="px-3 py-1 rounded-lg text-[11px] font-bold bg-slate-100 dark:bg-white/10 text-[#111322] dark:text-white">Unduh PDF</span>
              </a>
            </div>
          </div>

        </div>

        <!-- RIGHT COLUMN (4 Columns — "Comments", "Post Stats", "Links Shared" style) -->
        <div class="lg:col-span-4 space-y-6">
          
          <!-- TOP MINI CARD ("Comments" style from reference) -->
          <div class="bg-white dark:bg-[#16192B] rounded-[30px] p-4 sm:p-5 shadow-[0_12px_35px_rgba(0,0,0,0.03)] flex items-center gap-5">
            <!-- Blush-Pink Rounded Squircle Badge -->
            <div class="w-20 h-20 rounded-[22px] bg-[#FADCD9] dark:bg-[#FF5C77]/20 flex flex-col items-center justify-center shrink-0 text-[#111322] dark:text-white">
              <span class="text-lg font-extrabold tracking-tight leading-none">
                {{ metrics.total_activities || 3 }}.k
              </span>
              <MessageSquare class="w-4 h-4 mt-2 text-[#111322] dark:text-[#FF7556] stroke-[2.2]" />
            </div>

            <!-- Title & Coral Gradient Progress Bar -->
            <div class="flex-1 min-w-0 pr-2">
              <div class="text-sm font-bold text-[#111322] dark:text-white">
                Audit Logs
              </div>
              <div class="w-full h-2.5 rounded-full bg-[#F4F3EF] dark:bg-slate-800 mt-3 overflow-hidden">
                <div class="h-full w-2/5 rounded-full bg-gradient-to-r from-[#F84593] to-[#FF6B4A]"></div>
              </div>
            </div>
          </div>

          <!-- MIDDLE TALL CARD ("Post Stats" Pill Bar + Wave Line Chart from reference) -->
          <div class="bg-white dark:bg-[#16192B] rounded-[34px] p-6 sm:p-7 shadow-[0_12px_35px_rgba(0,0,0,0.03)] flex flex-col justify-between">
            <!-- Card Header -->
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-bold text-[#111322] dark:text-white">
                Post Stats
              </span>
              <a :href="getUrl('/aircraft-inspections/dashboard')" class="p-1 rounded-lg text-[#111322] dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">
                <MoreHorizontal class="w-5 h-5" />
              </a>
            </div>

            <!-- Vertical Pill Bars + Spline Wave Chart -->
            <div class="relative my-2">
              <BaseChart :option="postStatsOption" height="235px" />
            </div>

            <!-- Bottom Summary Tile ("Completed Posts / Current Week / 874") -->
            <div class="mt-3 pt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
              <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-[#FADCD9] dark:bg-[#FF5C77]/20 flex items-center justify-center text-[#111322] dark:text-[#FF7556]">
                  <ClipboardCheck class="w-5 h-5 stroke-[2]" />
                </div>
                <div>
                  <span class="block text-xs font-bold text-[#111322] dark:text-white">
                    Completed Logs
                  </span>
                  <span class="block text-[11px] text-slate-400 mt-0.5">
                    Current Week
                  </span>
                </div>
              </div>

              <div class="text-xl font-extrabold text-[#111322] dark:text-white">
                {{ weeklyTotal }}
              </div>
            </div>
          </div>

          <!-- BOTTOM MINI CARD ("Links Shared" style from reference) -->
          <div class="bg-white dark:bg-[#16192B] rounded-[30px] p-4 sm:p-5 shadow-[0_12px_35px_rgba(0,0,0,0.03)] flex items-center gap-5">
            <!-- Blush-Pink Rounded Squircle Badge -->
            <div class="w-20 h-20 rounded-[22px] bg-[#FADCD9] dark:bg-[#FF5C77]/20 flex flex-col items-center justify-center shrink-0 text-[#111322] dark:text-white">
              <span class="text-lg font-extrabold tracking-tight leading-none">
                {{ metrics.total_users || 10 }}.k
              </span>
              <ThumbsUp class="w-4 h-4 mt-2 text-[#111322] dark:text-[#FF7556] stroke-[2.2]" />
            </div>

            <!-- Title & Dark Navy Progress Bar -->
            <div class="flex-1 min-w-0 pr-2">
              <div class="text-sm font-bold text-[#111322] dark:text-white">
                Active Accounts
              </div>
              <div class="w-full h-2.5 rounded-full bg-[#F4F3EF] dark:bg-slate-800 mt-3 overflow-hidden">
                <div class="h-full w-3/5 rounded-full bg-[#111322] dark:bg-white"></div>
              </div>
            </div>
          </div>

        </div>

      </div>
    </main>
  </HybridNavbar>
</template>
