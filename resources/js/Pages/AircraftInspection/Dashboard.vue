<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import BaseChart from '../../Components/BaseChart.vue';
import { useDate } from '../../composables/useDate';
import {
  Plane,
  ShieldCheck,
  AlertTriangle,
  Download,
  MoreHorizontal,
  ExternalLink,
  Compass,
  ClipboardCheck,
  ThumbsUp
} from 'lucide-vue-next';

const props = defineProps({
  stats: {
    type: Object,
    required: true,
  },
  chart_data: {
    type: Object,
    required: true,
  },
  recent: {
    type: Array,
    default: () => [],
  },
});

const { formatDate } = useDate();

// Active Tab for Bottom Floating Rows ('recent' | 'airlines' | 'actions')
const activeTab = ref('recent');

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

const compliancePct = computed(() => Math.round(props.stats?.compliance_rate ?? 80));
const defectPct = computed(() => Math.max(0, 100 - compliancePct.value));

// 1. Coral-Pink Gradient Card Multi-Wave Chart (14-Day Inspection Waves)
const heroWaveOption = computed(() => {
  const totalArr = props.chart_data?.trend_total?.length ? props.chart_data.trend_total : [4, 7, 5, 9, 6, 11, 8];
  const layakArr = props.chart_data?.trend_layak?.length ? props.chart_data.trend_layak : [3, 6, 4, 8, 5, 9, 7];
  const tidakLayakArr = props.chart_data?.trend_tidak_layak?.length ? props.chart_data.trend_tidak_layak : [1, 1, 1, 1, 1, 2, 1];

  return {
    tooltip: {
      trigger: 'axis',
      backgroundColor: '#111322',
      borderColor: '#262A40',
      textStyle: { color: '#FFFFFF', fontSize: 11 },
    },
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
      data: props.chart_data?.trend_dates || [],
    },
    yAxis: {
      type: 'value',
      show: false,
    },
    series: [
      {
        name: 'Total Inspeksi',
        type: 'line',
        smooth: 0.55,
        symbol: 'none',
        data: totalArr,
        lineStyle: { width: 2.2, color: '#FFFFFF' },
      },
      {
        name: 'Layak Sanitasi',
        type: 'line',
        smooth: 0.55,
        symbol: 'none',
        data: layakArr,
        lineStyle: { width: 2, color: '#FFD166' },
      },
      {
        name: 'Tindakan Penyehatan',
        type: 'line',
        smooth: 0.55,
        symbol: 'none',
        data: tidakLayakArr,
        lineStyle: { width: 2, color: '#7B2CBF' },
      },
    ],
  };
});

// 2. Multi-Segment Rounded Donut Chart (Kelaikan & Tindakan)
const donutOption = computed(() => {
  const actions = props.chart_data?.actions || {};
  return {
    tooltip: {
      trigger: 'item',
      formatter: '{b}: {c} ({d}%)',
    },
    series: [
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
      {
        name: 'Kelaikan Sanitasi',
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
            value: Math.max(1, props.stats.layak_count),
            name: 'Layak Bebas Vektor',
            itemStyle: { color: isDark.value ? '#F8FAFC' : '#111322' },
          },
          {
            value: Math.max(1, actions.disinseksi || 1),
            name: 'Disinseksi',
            itemStyle: { color: '#FF7556' },
          },
          {
            value: Math.max(1, (actions.disinfeksi || 0) + (actions.fumigasi || 0) || 1),
            name: 'Disinfeksi & Fumigasi',
            itemStyle: { color: '#F84593' },
          },
          {
            value: Math.max(1, actions.general_cleaning || 1),
            name: 'General Cleaning',
            itemStyle: { color: '#FFD166' },
          },
        ],
      },
    ],
  };
});

// 3. Right-Column "Post Stats" Vertical Pill Bars + Spline Wave Chart (Last 7 Days)
const weeklyPillChartOption = computed(() => {
  const last7Total = (props.chart_data?.trend_total || []).slice(-7);
  const last7Layak = (props.chart_data?.trend_layak || []).slice(-7);
  const counts = last7Total.length === 7 ? last7Total : [4, 7, 5, 11, 6, 8, 5];
  const maxVal = Math.max(...counts, 5);
  const peakIdx = counts.indexOf(Math.max(...counts));

  const barData = counts.map((val, idx) => {
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
        name: 'Inspeksi Harian',
        type: 'bar',
        barWidth: 14,
        data: barData,
        z: 2,
      },
      {
        name: 'Total Armada',
        type: 'line',
        smooth: 0.5,
        symbol: 'none',
        data: counts,
        lineStyle: {
          width: 1.5,
          color: isDark.value ? '#F8FAFC' : '#111322',
        },
        z: 3,
      },
      {
        name: 'Layak Terbang',
        type: 'line',
        smooth: 0.5,
        symbol: 'none',
        data: last7Layak.length === 7 ? last7Layak : counts.map(v => Math.max(1, v - 1)),
        lineStyle: {
          width: 1.5,
          color: '#FF7556',
        },
        z: 3,
      },
    ],
  };
});

const avatarPalette = ['FFD166', 'FF7556', 'F84593', '111322', '06D6A0', '118AB2'];
</script>

<template>
  <Head title="Sanitation Analytics &bull; BKK Bandung" />

  <HybridNavbar mode="inertia" currentRoute="aircraft-dashboard" pageTitle="Sanitation Analytics">
    <main class="w-full px-4 sm:px-6 lg:px-10 py-4 sm:py-6">
      <!-- Reference-Matched 12-Column Asymmetric Bento Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        
        <!-- LEFT / MAIN COLUMN (8 Columns) -->
        <div class="lg:col-span-8 space-y-7">
          
          <!-- Top Row: 2 Featured Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- CARD 1: Coral-Pink Gradient Hero Card ("Total Likes" style) -->
            <div class="relative overflow-hidden rounded-[34px] bg-gradient-to-br from-[#FF7E55] via-[#FF5A76] to-[#F74292] p-6 sm:p-7 text-white shadow-[0_20px_45px_-15px_rgba(255,90,118,0.5)] flex flex-col justify-between min-h-[290px]">
              <div>
                <div class="flex items-center justify-between">
                  <span class="text-xs sm:text-sm font-medium text-white/90 tracking-wide">
                    Total Aircraft Inspections
                  </span>
                  <Link href="/aircraft-inspections" class="p-1 rounded-lg hover:bg-white/15 transition-colors text-white/90" title="Kelola Data Inspeksi">
                    <MoreHorizontal class="w-5 h-5" />
                  </Link>
                </div>

                <div class="text-3xl sm:text-[34px] font-extrabold tracking-tight mt-1.5">
                  {{ stats.total }}.0000K
                </div>
              </div>

              <!-- Smooth Multi-Line Wave Chart -->
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
                  <span class="block text-[11px] font-medium text-white/80">Tindakan</span>
                  <span class="block text-lg sm:text-xl font-bold mt-1">%{{ defectPct }}</span>
                </div>

                <div class="pl-4">
                  <span class="block text-[11px] font-medium text-white/80">IHR 2005</span>
                  <span class="block text-lg sm:text-xl font-bold mt-1">%100</span>
                </div>
              </div>
            </div>

            <!-- CARD 2: Clean White Card with Multi-Segment Donut ("Pending Messages" style) -->
            <div class="rounded-[34px] bg-white dark:bg-[#16192B] p-6 sm:p-7 shadow-[0_12px_35px_rgba(0,0,0,0.03)] flex flex-col justify-between min-h-[290px]">
              <div class="flex items-center justify-between">
                <span class="text-xs sm:text-sm font-bold text-[#111322] dark:text-white">
                  Kelaikan Sanitasi Armada
                </span>
                <Link href="/aircraft-inspections" class="p-1 rounded-lg text-[#111322] dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">
                  <MoreHorizontal class="w-5 h-5" />
                </Link>
              </div>

              <div class="flex items-center justify-between gap-2 my-auto pt-2">
                <!-- Left Metrics & Legend -->
                <div class="space-y-5">
                  <div class="text-3xl sm:text-[34px] font-extrabold text-[#111322] dark:text-white tracking-tight">
                    {{ stats.layak_count }}.k
                  </div>

                  <div class="space-y-3">
                    <div>
                      <div class="flex items-center gap-2 text-xs text-slate-400 font-medium">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#FF7556]"></span>
                        <span>Bebas Vektor</span>
                      </div>
                      <div class="text-sm font-extrabold text-[#111322] dark:text-white mt-0.5">
                        %{{ compliancePct }}
                      </div>
                    </div>

                    <div>
                      <div class="flex items-center gap-2 text-xs text-slate-400 font-medium">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#FFD166]"></span>
                        <span>Intervensi</span>
                      </div>
                      <div class="text-sm font-extrabold text-[#111322] dark:text-white mt-0.5">
                        %{{ defectPct }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Right Donut Ring with Custom 3D Coral Cross in Center -->
                <div class="relative w-40 h-40 sm:w-44 sm:h-44 shrink-0 flex items-center justify-center">
                  <BaseChart :option="donutOption" height="176px" />
                  <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <svg viewBox="0 0 64 64" class="w-11 h-11 drop-shadow-[0_6px_10px_rgba(217,83,79,0.35)]" fill="none">
                      <defs>
                        <linearGradient id="bkkCrossA" x1="12" y1="12" x2="52" y2="52" gradientUnits="userSpaceOnUse">
                          <stop offset="0%" stop-color="#FF8A65" />
                          <stop offset="100%" stop-color="#C84B31" />
                        </linearGradient>
                        <linearGradient id="bkkCrossB" x1="52" y1="12" x2="12" y2="52" gradientUnits="userSpaceOnUse">
                          <stop offset="0%" stop-color="#FFAB91" />
                          <stop offset="100%" stop-color="#B83B2A" />
                        </linearGradient>
                      </defs>
                      <rect x="26" y="10" width="12" height="44" rx="6" transform="rotate(-35 32 32)" fill="url(#bkkCrossA)" />
                      <rect x="26" y="10" width="12" height="44" rx="6" transform="rotate(55 32 32)" fill="url(#bkkCrossB)" />
                      <circle cx="21" cy="24" r="3" fill="#FFCCBC" fill-opacity="0.6" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- Bottom Section: Tabbed Floating Row List ("Post Activity" style) -->
          <div class="space-y-4">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/10 pb-3">
              <div class="flex items-center gap-7">
                <button
                  type="button"
                  @click="activeTab = 'recent'"
                  class="relative text-xs sm:text-sm font-bold transition-colors cursor-pointer pb-1"
                  :class="activeTab === 'recent'
                    ? 'text-[#111322] dark:text-white after:content-[\'\'] after:absolute after:left-0 after:right-0 after:-bottom-[13px] after:h-[2.5px] after:bg-[#111322] dark:after:bg-white after:rounded-full'
                    : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'"
                >
                  Inspection Activity
                </button>

                <button
                  type="button"
                  @click="activeTab = 'airlines'"
                  class="relative text-xs sm:text-sm font-bold transition-colors cursor-pointer pb-1"
                  :class="activeTab === 'airlines'
                    ? 'text-[#111322] dark:text-white after:content-[\'\'] after:absolute after:left-0 after:right-0 after:-bottom-[13px] after:h-[2.5px] after:bg-[#111322] dark:after:bg-white after:rounded-full'
                    : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'"
                >
                  Airlines ({{ chart_data.airlines?.length || 0 }})
                </button>
              </div>

              <Link
                href="/aircraft-inspections"
                class="text-xs font-bold text-[#FF5C77] hover:underline"
              >
                Kelola Semua →
              </Link>
            </div>

            <!-- TAB 1: Recent Aircraft Inspections as Floating White Pill Cards -->
            <div v-if="activeTab === 'recent'" class="space-y-3">
              <div
                v-for="(item, idx) in recent"
                :key="item.id"
                class="group bg-white dark:bg-[#16192B] rounded-[22px] px-4 sm:px-5 py-3.5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-[0_10px_28px_rgba(0,0,0,0.06)] transition-all flex items-center justify-between gap-4"
              >
                <!-- Left: Airline Thumbnail + Registration & Officer -->
                <div class="flex items-center gap-3.5 min-w-0 flex-1">
                  <img
                    :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(item.tanda_kebangsaan || 'PK')}&background=${avatarPalette[idx % avatarPalette.length]}&color=fff&bold=true`"
                    :alt="item.pemilik"
                    class="w-11 h-11 rounded-2xl object-cover shrink-0 shadow-2xs"
                  />
                  <div class="min-w-0">
                    <div class="flex items-center gap-2">
                      <span class="text-xs sm:text-[13px] font-bold text-[#111322] dark:text-white truncate">
                        {{ item.pemilik }} ({{ item.tanda_kebangsaan }})
                      </span>
                      <ExternalLink class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600 shrink-0" />
                    </div>
                    <span class="block text-[11px] text-slate-400 mt-0.5 truncate">
                      {{ item.registration_no }} &bull; Petugas: {{ item.petugas_bkk_nama }}
                    </span>
                  </div>
                </div>

                <!-- Middle: Inspection Date -->
                <div class="hidden sm:block w-32 text-center">
                  <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">
                    {{ formatDate(item.tanggal) }}
                  </span>
                </div>

                <!-- Right: Compass Icon + Status Pill + PDF Action -->
                <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                  <div class="hidden md:flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-[#111322] dark:bg-white/10 text-white flex items-center justify-center">
                      <Compass class="w-3.5 h-3.5" />
                    </span>
                    <span class="text-xs font-bold text-[#111322] dark:text-white">
                      {{ item.waktu }}
                    </span>
                  </div>

                  <span
                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold min-w-[68px] text-center"
                    :class="item.kesimpulan === 'layak'
                      ? 'bg-[#E6F8F0] dark:bg-emerald-950/70 text-[#10B981]'
                      : 'bg-[#FFEAE8] dark:bg-rose-950/70 text-[#FF574A]'"
                  >
                    {{ item.kesimpulan === 'layak' ? '↑ Layak' : '↓ Tindakan' }}
                  </span>

                  <a
                    :href="`/aircraft-inspections/${item.id}/pdf`"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-[#111322] dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition-colors"
                    title="Unduh Sertifikat PDF"
                  >
                    <Download class="w-4 h-4" />
                  </a>
                </div>
              </div>
            </div>

            <!-- TAB 2: Airlines Breakdown as Floating Pill Cards -->
            <div v-else class="space-y-3">
              <div
                v-for="(airline, idx) in chart_data.airlines"
                :key="airline.name"
                class="bg-white dark:bg-[#16192B] rounded-[22px] px-4 sm:px-5 py-3.5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex items-center justify-between gap-4"
              >
                <div class="flex items-center gap-3.5 min-w-0 flex-1">
                  <img
                    :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(airline.name)}&background=${avatarPalette[idx % avatarPalette.length]}&color=fff&bold=true`"
                    :alt="airline.fullName"
                    class="w-11 h-11 rounded-2xl object-cover shrink-0"
                  />
                  <div class="min-w-0">
                    <span class="block text-xs sm:text-[13px] font-bold text-[#111322] dark:text-white truncate">
                      {{ airline.fullName }}
                    </span>
                    <span class="block text-[11px] text-slate-400 mt-0.5">
                      Operator Penerbangan Terdaftar
                    </span>
                  </div>
                </div>

                <div class="hidden sm:block w-28 text-center">
                  <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">
                    {{ airline.name }}
                  </span>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                  <span class="w-7 h-7 rounded-full bg-[#111322] dark:bg-white/10 text-white flex items-center justify-center">
                    <Plane class="w-3.5 h-3.5" />
                  </span>
                  <span class="text-xs font-bold text-[#111322] dark:text-white">
                    {{ airline.value }} Armada
                  </span>
                  <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-[#E6F8F0] dark:bg-emerald-950/70 text-[#10B981]">
                    ↑ {{ Math.round((airline.value / Math.max(stats.total, 1)) * 100) }}%
                  </span>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- RIGHT COLUMN (4 Columns) -->
        <div class="lg:col-span-4 space-y-6">
          
          <!-- TOP MINI CARD ("Comments" style) -->
          <div class="bg-white dark:bg-[#16192B] rounded-[30px] p-4 sm:p-5 shadow-[0_12px_35px_rgba(0,0,0,0.03)] flex items-center gap-5">
            <div class="w-20 h-20 rounded-[22px] bg-[#FADCD9] dark:bg-[#FF5C77]/20 flex flex-col items-center justify-center shrink-0 text-[#111322] dark:text-white">
              <span class="text-lg font-extrabold tracking-tight leading-none">
                {{ stats.compliance_rate }}%
              </span>
              <ShieldCheck class="w-4 h-4 mt-2 text-[#111322] dark:text-[#FF7556] stroke-[2.2]" />
            </div>

            <div class="flex-1 min-w-0 pr-2">
              <div class="text-sm font-bold text-[#111322] dark:text-white">
                Compliance Rate
              </div>
              <div class="w-full h-2.5 rounded-full bg-[#F4F3EF] dark:bg-slate-800 mt-3 overflow-hidden">
                <div
                  class="h-full rounded-full bg-gradient-to-r from-[#F84593] to-[#FF6B4A]"
                  :style="{ width: `${Math.min(100, Math.max(15, stats.compliance_rate))}%` }"
                ></div>
              </div>
            </div>
          </div>

          <!-- MIDDLE TALL CARD ("Post Stats" style) -->
          <div class="bg-white dark:bg-[#16192B] rounded-[34px] p-6 sm:p-7 shadow-[0_12px_35px_rgba(0,0,0,0.03)] flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-bold text-[#111322] dark:text-white">
                Inspection Stats
              </span>
              <Link href="/aircraft-inspections" class="p-1 rounded-lg text-[#111322] dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">
                <MoreHorizontal class="w-5 h-5" />
              </Link>
            </div>

            <div class="relative my-2">
              <BaseChart :option="weeklyPillChartOption" height="235px" />
            </div>

            <div class="mt-3 pt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
              <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-[#FADCD9] dark:bg-[#FF5C77]/20 flex items-center justify-center text-[#111322] dark:text-[#FF7556]">
                  <ClipboardCheck class="w-5 h-5 stroke-[2]" />
                </div>
                <div>
                  <span class="block text-xs font-bold text-[#111322] dark:text-white">
                    Completed Inspections
                  </span>
                  <span class="block text-[11px] text-slate-400 mt-0.5">
                    Total Terverifikasi
                  </span>
                </div>
              </div>

              <div class="text-xl font-extrabold text-[#111322] dark:text-white">
                {{ stats.total }}
              </div>
            </div>
          </div>

          <!-- BOTTOM MINI CARD ("Links Shared" style) -->
          <div class="bg-white dark:bg-[#16192B] rounded-[30px] p-4 sm:p-5 shadow-[0_12px_35px_rgba(0,0,0,0.03)] flex items-center gap-5">
            <div class="w-20 h-20 rounded-[22px] bg-[#FADCD9] dark:bg-[#FF5C77]/20 flex flex-col items-center justify-center shrink-0 text-[#111322] dark:text-white">
              <span class="text-lg font-extrabold tracking-tight leading-none">
                {{ stats.tidak_layak_count }}.k
              </span>
              <ThumbsUp class="w-4 h-4 mt-2 text-[#111322] dark:text-[#FF7556] stroke-[2.2]" />
            </div>

            <div class="flex-1 min-w-0 pr-2">
              <div class="text-sm font-bold text-[#111322] dark:text-white">
                Vector Interventions
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
