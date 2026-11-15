<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import BaseChart from '../../Components/BaseChart.vue';
import ClinicalSkeleton from '../../Components/ClinicalSkeleton.vue';
import SinkarkesSummaryCard from './components/SinkarkesSummaryCard.vue';
import { useTheme } from '@/composables/useTheme';
import {
  FileSpreadsheet,
  Calendar,
  Building2,
  Plane,
  Users,
  FileCheck,
  HeartPulse,
  ShieldCheck,
  CalendarCheck,
  PieChart as PieChartIcon,
  Printer,
} from 'lucide-vue-next';

const props = defineProps({
  year: {
    type: Number,
    default: 2026,
  },
  wilker: {
    type: String,
    default: 'all',
  },
  is_consolidated: {
    type: Boolean,
    default: true,
  },
  wilker_options: {
    type: Array,
    default: () => [],
  },
  kpi: {
    type: Object,
    default: () => ({}),
  },
  matrix: {
    type: Array,
    default: () => [],
  },
  traffic_chart: {
    type: Object,
    default: () => ({}),
  },
  document_chart: {
    type: Array,
    default: () => [],
  },
  wilker_comparison: {
    type: Array,
    default: () => [],
  },
  health_chart: {
    type: Object,
    default: () => ({}),
  },
  environment_chart: {
    type: Object,
    default: () => ({}),
  },
  summary_table: {
    type: Object,
    default: () => ({ items: [], monthly_totals: {}, total_year: 0, section_totals: {} }),
  },
  wilker_summary: {
    type: Object,
    default: () => ({ items: [], monthly_totals: {}, total_year: 0 }),
  },
});

const { isDark } = useTheme();
const selectedYear = ref(props.year);
const selectedWilker = ref(props.wilker);
const isFiltering = ref(false);
const isMobileViewport = ref(false);
const availableYears = [2024, 2025, 2026, 2027, 2028, 2029, 2030];

let mqList = null;
const updateViewportFlag = () => {
  if (typeof window !== 'undefined') {
    isMobileViewport.value = window.innerWidth < 640;
  }
};

onMounted(() => {
  updateViewportFlag();
  if (typeof window !== 'undefined' && window.matchMedia) {
    mqList = window.matchMedia('(max-width: 639px)');
    mqList.addEventListener?.('change', updateViewportFlag);
  }
});

onUnmounted(() => {
  mqList?.removeEventListener?.('change', updateViewportFlag);
});

const handlePrint = () => {
  if (typeof window !== 'undefined') {
    window.print();
  }
};

// Handle filter change
const handleFilterChange = () => {
  router.get('/sinkarkes/dashboard', {
    year: selectedYear.value,
    wilker: selectedWilker.value,
  }, {
    preserveState: true,
    replace: true,
    onStart: () => {
      isFiltering.value = true;
    },
    onFinish: () => {
      isFiltering.value = false;
    },
  });
};

// 1. Chart 1: Monthly Traffic Trend (Line & Area)
const trafficOption = computed(() => {
  const months = props.traffic_chart.months || [];
  return {
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'cross' },
      backgroundColor: 'rgba(15, 23, 42, 0.95)',
      borderColor: '#334155',
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
        data: props.traffic_chart.plane || [],
        itemStyle: { color: '#00A99D' }, // Kemenkes Turquoise
        lineStyle: { width: 2.5, color: '#00A99D' },
        areaStyle: {
          color: {
            type: 'linear',
            x: 0, y: 0, x2: 0, y2: 1,
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
        data: props.traffic_chart.ship || [],
        itemStyle: { color: '#005851' }, // Kemenkes Teal Tua
        lineStyle: { width: 2.5, color: '#005851' },
      },
      {
        name: 'Penumpang (Org)',
        type: 'line',
        smooth: true,
        yAxisIndex: 1,
        data: props.traffic_chart.passenger || [],
        itemStyle: { color: '#f59e0b' }, // Amber
        lineStyle: { width: 2, color: '#f59e0b', type: 'dashed' },
      },
      {
        name: 'Kru & ABK (Org)',
        type: 'line',
        smooth: true,
        yAxisIndex: 1,
        data: props.traffic_chart.crew || [],
        itemStyle: { color: '#7CA622' }, // Kemenkes Hijau Kuning
        lineStyle: { width: 2, color: '#7CA622' },
      },
    ],
  };
});

// 2. Chart 2: Document Composition (Donut / Pie)
const documentOption = computed(() => {
  const compact = isMobileViewport.value;
  return {
    tooltip: {
      trigger: 'item',
      formatter: '{b}: <strong>{c} dokumen</strong> ({d}%)',
      backgroundColor: 'rgba(15, 23, 42, 0.95)',
      textStyle: { color: '#f8fafc', fontSize: 11 },
    },
    legend: compact
      ? {
          orient: 'horizontal',
          bottom: 0,
          left: 'center',
          textStyle: { color: '#64748b', fontSize: 10 },
          itemWidth: 10,
          itemHeight: 10,
        }
      : {
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
        radius: compact ? ['38%', '64%'] : ['45%', '72%'],
        center: compact ? ['50%', '39%'] : ['35%', '50%'],
        avoidLabelOverlap: true,
        itemStyle: {
          borderRadius: 6,
          borderColor: isDark.value ? '#111820' : '#ffffff',
          borderWidth: 2,
        },
        label: {
          show: false,
          position: 'center',
        },
        emphasis: {
          label: {
            show: true,
            fontSize: 14,
            fontWeight: 'bold',
            formatter: '{b}\n{c} ({d}%)',
          },
        },
        color: [
          '#00A99D',
          '#005851',
          '#7CA622',
          '#0284c7',
          '#8b5cf6',
          '#f59e0b',
          '#ec4899',
          '#64748b',
        ],
        data: props.document_chart || [],
      },
    ],
  };
});

// 3. Chart 3: Cross-Wilker Comparison (Horizontal Bar Chart)
const wilkerOption = computed(() => {
  const comparison = props.wilker_comparison || [];
  const categories = comparison.map(c => c.short_name);
  const alatAngkut = comparison.map(c => c.total_alat_angkut);
  const orang = comparison.map(c => c.total_orang);
  const dokumen = comparison.map(c => c.total_dokumen);

  return {
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'shadow' },
      backgroundColor: 'rgba(15, 23, 42, 0.95)',
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

// 4. Chart 4: Health Services & ICV Vaccines (Stacked Bar)
const healthOption = computed(() => {
  const months = props.health_chart.months || [];
  return {
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'shadow' },
      backgroundColor: 'rgba(15, 23, 42, 0.95)',
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
        data: props.health_chart.poliklinik || [],
        itemStyle: { color: '#0284c7', borderRadius: [4, 4, 0, 0] },
      },
      {
        name: 'Surat Berbadan Sehat',
        type: 'bar',
        data: props.health_chart.surat_sehat || [],
        itemStyle: { color: '#7CA622', borderRadius: [4, 4, 0, 0] },
      },
      {
        name: 'Vaksinasi ICV',
        type: 'bar',
        data: props.health_chart.vaksin_icv || [],
        itemStyle: { color: '#00A99D', borderRadius: [4, 4, 0, 0] },
      },
    ],
  };
});

// Navigate directly to month input
const openMonthForm = (targetWilker, targetMonth) => {
  router.get('/sinkarkes', {
    wilker: targetWilker,
    year: selectedYear.value,
    month: targetMonth,
  });
};
</script>

<template>
  <Head :title="`Dashboard SINKARKES ${year} &bull; BKK Bandung`" />

  <HybridNavbar mode="inertia" currentRoute="sinkarkes-dashboard">
    <main class="clinical-main font-sans">

      <!-- Top Executive Header & Filter Bar -->
      <div class="clinical-header flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <!-- Institutional Title & Description -->
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-9 h-9 rounded-lg bg-[#002B27] border border-[#00A99D]/40 text-[#99CC33] flex items-center justify-center shrink-0">
            <Building2 class="w-4 h-4" />
          </div>
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <h1 class="text-sm sm:text-[15px] font-bold text-white tracking-tight">
                Executive Summary &amp; Dashboard SINKARKES
              </h1>
              <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-[#002B27]/80 text-[#99DFD6] border border-[#00A99D]/30">
                Tahun {{ year }}
              </span>
            </div>
            <p class="text-[11px] text-slate-200/85 truncate mt-0.5">
              Ringkasan kinerja terpadu: Alat Angkut, Orang, Dokumen Karantina, Layanan Kesehatan &amp; Pengendalian Risiko Lingkungan.
            </p>
          </div>
        </div>

        <!-- Quick Navigation & Filter Controls -->
        <div class="flex flex-wrap items-center gap-1.5 shrink-0 print:hidden">
          <!-- Filter Wilayah Kerja -->
          <div class="flex items-center gap-1.5 bg-[#002D29] border border-white/15 rounded-lg px-2.5 py-1 text-white">
            <Building2 class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
            <select
              v-model="selectedWilker"
              @change="handleFilterChange"
              aria-label="Filter Wilayah Kerja"
              class="bg-transparent text-xs font-semibold text-white border-none focus:ring-0 cursor-pointer outline-none pr-1 py-0.5"
              title="Pilih Wilayah Kerja"
            >
              <option value="all" class="text-slate-900 dark:text-slate-100 dark:bg-[#111820]">Semua Wilayah Kerja (Konsolidasi)</option>
              <option v-for="w in wilker_options" :key="w" :value="w" class="text-slate-900 dark:text-slate-100 dark:bg-[#111820]">
                {{ w }}
              </option>
            </select>
          </div>

          <!-- Filter Tahun -->
          <div class="flex items-center gap-1.5 bg-[#002D29] border border-white/15 rounded-lg px-2.5 py-1 text-white">
            <Calendar class="w-3.5 h-3.5 text-[#99CC33] shrink-0" />
            <select
              v-model.number="selectedYear"
              @change="handleFilterChange"
              aria-label="Filter Tahun Laporan"
              class="bg-transparent text-xs font-semibold text-white border-none focus:ring-0 cursor-pointer outline-none pr-1 py-0.5"
              title="Pilih Tahun Laporan"
            >
              <option v-for="y in availableYears" :key="y" :value="y" class="text-slate-900 dark:text-slate-100 dark:bg-[#111820]">
                Tahun {{ y }}
              </option>
            </select>
          </div>

          <!-- Print Report Button -->
          <button
            type="button"
            @click="handlePrint"
            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-white/15 bg-white/10 hover:bg-white/20 text-xs font-semibold text-white transition-colors cursor-pointer"
            title="Cetak / Simpan PDF Laporan"
          >
            <Printer class="w-3.5 h-3.5 text-[#99DFD6]" />
            <span class="hidden sm:inline">Cetak</span>
          </button>

          <!-- Switch to Form Input Button -->
          <Link
            :href="`/sinkarkes?wilker=${encodeURIComponent(selectedWilker !== 'all' ? selectedWilker : 'POS Bandara Husen')}&year=${selectedYear}`"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-[#00A99D] hover:bg-[#009388] transition-colors cursor-pointer"
          >
            <FileSpreadsheet class="w-3.5 h-3.5" />
            <span>Formulir Input &rarr;</span>
          </Link>
        </div>
      </div>

      <!-- Screen Reader Live Filter Announcement & Skeleton State -->
      <p class="sr-only" role="status" aria-live="polite">
        {{ isFiltering ? 'Memuat data dashboard SINKARKES...' : `Menampilkan laporan SINKARKES tahun ${year} untuk ${selectedWilker === 'all' ? 'semua wilayah kerja' : selectedWilker}` }}
      </p>
      <ClinicalSkeleton v-if="isFiltering" variant="cards" :cols="4" />

      <!-- 6 Key Performance Metric Cards Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5">
        <!-- 1. Total Alat Angkut -->
        <div class="bg-white dark:bg-[#12161f] border border-slate-200/90 dark:border-[#232a36] rounded-2xl p-4 shadow-xs relative overflow-hidden group hover:border-[#00A99D]/40 transition-colors">
          <div class="flex items-center justify-between text-slate-500 text-[11px] font-mono">
            <span>ALAT ANGKUT</span>
            <div class="w-7 h-7 rounded-xl bg-[#00A99D]/10 text-[#00A99D] flex items-center justify-center">
              <Plane class="w-3.5 h-3.5" />
            </div>
          </div>
          <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-2 font-mono">
            {{ kpi.total_alat_angkut?.toLocaleString('id-ID') || 0 }}
          </div>
          <div class="text-[10px] text-slate-400 mt-1 flex items-center gap-1.5">
            <span>Pesawat: <strong>{{ kpi.total_pesawat || 0 }}</strong></span>
            <span>&bull;</span>
            <span>Kapal: <strong>{{ kpi.total_kapal || 0 }}</strong></span>
          </div>
        </div>

        <!-- 2. Orang Terawasi -->
        <div class="bg-white dark:bg-[#12161f] border border-slate-200/90 dark:border-[#232a36] rounded-2xl p-4 shadow-xs relative overflow-hidden group hover:border-amber-400/40 transition-colors">
          <div class="flex items-center justify-between text-slate-500 text-[11px] font-mono">
            <span>ORANG TERAWASI</span>
            <div class="w-7 h-7 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center">
              <Users class="w-3.5 h-3.5" />
            </div>
          </div>
          <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-2 font-mono">
            {{ kpi.total_orang?.toLocaleString('id-ID') || 0 }}
          </div>
          <div class="text-[10px] text-slate-400 mt-1 flex items-center gap-1.5 clinical-num">
            <span>Penumpang: <strong>{{ kpi.total_penumpang || 0 }}</strong></span>
            <span>&bull;</span>
            <span>Kru: <strong>{{ kpi.total_crew || 0 }}</strong></span>
          </div>
        </div>

        <!-- 3. Dokumen Kekarantinaan -->
        <div class="clinical-kpi relative overflow-hidden">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-[10px] font-semibold uppercase tracking-wider">
            <span>DOKUMEN KARANTINA</span>
            <div class="w-6 h-6 rounded-lg bg-[#005851]/10 dark:bg-[#00A99D]/20 text-[#005851] dark:text-[#5eead4] flex items-center justify-center">
              <FileCheck class="w-3.5 h-3.5" />
            </div>
          </div>
          <div class="text-xl font-bold text-slate-900 dark:text-white mt-1.5 clinical-num">
            {{ kpi.total_dokumen?.toLocaleString('id-ID') || 0 }}
          </div>
          <div class="text-[10px] text-slate-400 dark:text-slate-400 mt-0.5 truncate">
            SSCEC, SSCC, P3K, Desinseksi
          </div>
        </div>

        <!-- 4. Layanan Kesehatan -->
        <div class="clinical-kpi relative overflow-hidden">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-[10px] font-semibold uppercase tracking-wider">
            <span>LAYANAN MEDIS</span>
            <div class="w-6 h-6 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center">
              <HeartPulse class="w-3.5 h-3.5" />
            </div>
          </div>
          <div class="text-xl font-bold text-slate-900 dark:text-white mt-1.5 clinical-num">
            {{ kpi.total_layanan_kesehatan?.toLocaleString('id-ID') || 0 }}
          </div>
          <div class="text-[10px] text-slate-400 dark:text-slate-400 mt-0.5 truncate">
            Poliklinik BKK &amp; Uji Laik
          </div>
        </div>

        <!-- 5. Vaksinasi ICV -->
        <div class="clinical-kpi relative overflow-hidden">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-[10px] font-semibold uppercase tracking-wider">
            <span>VAKSINASI ICV</span>
            <div class="w-6 h-6 rounded-lg bg-[#7CA622]/10 dark:bg-[#7CA622]/20 text-[#7CA622] dark:text-[#bef264] flex items-center justify-center">
              <ShieldCheck class="w-3.5 h-3.5" />
            </div>
          </div>
          <div class="text-xl font-bold text-slate-900 dark:text-white mt-1.5 clinical-num">
            {{ kpi.total_vaksinasi?.toLocaleString('id-ID') || 0 }}
          </div>
          <div class="text-[10px] text-slate-400 dark:text-slate-400 mt-0.5 truncate">
            Yellow Fever, Meningitis, dll
          </div>
        </div>

        <!-- 6. Kepatuhan Pelaporan -->
        <div class="clinical-kpi relative overflow-hidden">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-[10px] font-semibold uppercase tracking-wider">
            <span>KELENGKAPAN</span>
            <div class="w-6 h-6 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
              <CalendarCheck class="w-3.5 h-3.5" />
            </div>
          </div>
          <div class="text-xl font-bold text-slate-900 dark:text-white mt-1.5 clinical-num">
            {{ kpi.compliance_rate || 0 }}%
          </div>
          <div class="text-[10px] text-slate-400 dark:text-slate-400 mt-0.5 truncate clinical-num">
            {{ kpi.submitted_reports_count }} / {{ kpi.total_possible_reports }} bulan terisi
          </div>
        </div>
      </div>

      <!-- Charts Row 1: Traffic Trend (Line) & Document Composition (Pie) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
        <!-- Monthly Traffic Trend Chart (Span 2 cols) -->
        <div class="lg:col-span-2 clinical-surface p-4 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-1">
              <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#00A99D]"></span>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                  Tren Bulanan Pengawasan Alat Angkut &amp; Orang ({{ year }})
                </h2>
              </div>
              <span class="text-[11px] text-slate-400 font-mono">12 Bulan</span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
              Dinamika volume kedatangan/keberangkatan pesawat, kapal laut, penumpang, dan kru sepanjang tahun.
            </p>
          </div>

          <div class="w-full h-[300px]">
            <BaseChart :option="trafficOption" height="300px" />
          </div>
        </div>

        <!-- Document & Certificate Composition (Pie Chart) -->
        <div class="clinical-surface p-4 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-1">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#005851]"></span>
                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                  Komposisi Dokumen Kekarantinaan
                </h2>
              </div>
              <PieChartIcon class="w-4 h-4 text-slate-400" />
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
              Distribusi penerbitan SSCEC, SSCC, P3K, HAC, dan sertifikat penyehatan.
            </p>
          </div>

          <div class="w-full h-[320px]">
            <BaseChart :option="documentOption" height="320px" />
          </div>
        </div>
      </div>

      <!-- Charts Row 2: Cross-Wilker Comparison & Health Services -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Cross-Wilker Operational Volume Comparison -->
        <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-3xl p-5 sm:p-6 shadow-xs flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-1">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#7CA622]"></span>
                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                  Perbandingan Volume Lintas 7 Wilayah Kerja
                </h2>
              </div>
              <span class="text-xs text-slate-400 font-mono">BKK Bandung</span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
              Komparasi beban aktivitas alat angkut, orang, dan dokumen antar pos dan wilayah kerja.
            </p>
          </div>

          <div class="w-full h-[320px]">
            <BaseChart :option="wilkerOption" height="320px" />
          </div>
        </div>

        <!-- Health Services & Vaccines Monthly Stack -->
        <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-3xl p-5 sm:p-6 shadow-xs flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-1">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-sky-600"></span>
                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                  Layanan Kesehatan Pelabuhan &amp; Vaksinasi ICV
                </h2>
              </div>
              <span class="text-xs text-slate-400 font-mono">UKP</span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
              Kunjungan poliklinik BKK, penerbitan surat keterangan sehat, dan vaksinasi haji/umrah/internasional.
            </p>
          </div>

          <div class="w-full h-[320px]">
            <BaseChart :option="healthOption" height="320px" />
          </div>
        </div>
      </div>

      <SinkarkesSummaryCard
        :year="year"
        :is-consolidated="is_consolidated"
        :selected-wilker="selectedWilker"
        :summary-table="summary_table"
        :wilker-summary="wilker_summary"
        :matrix="matrix"
        @open-month-form="openMonthForm"
      />

    </main>
  </HybridNavbar>
</template>

