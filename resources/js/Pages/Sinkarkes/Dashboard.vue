<script setup>
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import BaseChart from '../../Components/BaseChart.vue';
import {
  BarChart3,
  FileSpreadsheet,
  Calendar,
  Building2,
  Plane,
  Ship,
  Users,
  FileCheck,
  HeartPulse,
  ShieldCheck,
  ShieldAlert,
  CalendarCheck,
  Download,
  Upload,
  ArrowRight,
  TrendingUp,
  Activity,
  CheckCircle2,
  AlertCircle,
  HelpCircle,
  Clock,
  Sparkles,
  Layers,
  PieChart as PieChartIcon
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
});

const selectedYear = ref(props.year);
const selectedWilker = ref(props.wilker);
const availableYears = [2024, 2025, 2026, 2027, 2028, 2029, 2030];

// Handle filter change
const handleFilterChange = () => {
  router.get('/sinkarkes/dashboard', {
    year: selectedYear.value,
    wilker: selectedWilker.value,
  }, {
    preserveState: true,
    replace: true,
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
  return {
    tooltip: {
      trigger: 'item',
      formatter: '{b}: <strong>{c} dokumen</strong> ({d}%)',
      backgroundColor: 'rgba(15, 23, 42, 0.95)',
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
      data: ['Kunjungan Poliklinik KKP', 'Surat Berbadan Sehat', 'Vaksinasi ICV'],
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
        name: 'Kunjungan Poliklinik KKP',
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
    <main class="w-full px-3 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-6 max-w-[1600px] mx-auto font-sans">

      <!-- Top Executive Header & Filter Bar -->
      <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <!-- Institutional Title & Description -->
          <div class="space-y-1.5">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] sm:text-xs font-mono font-semibold bg-[#00A99D]/10 text-[#005851] dark:text-[#00A99D] border border-[#00A99D]/30">
              <Building2 class="w-3.5 h-3.5 text-[#00A99D]" />
              <span>SINKARKES &bull; BALAI KEKARANTINAAN KESEHATAN KELAS I BANDUNG</span>
            </div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
              <span>Executive Summary &amp; Dashboard SINKARKES</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-3xl leading-relaxed">
              Ringkasan capaian kinerja kekarantinaan kesehatan terpadu: Pengawasan Alat Angkut &amp; Orang, Dokumen Karantina, Layanan Kesehatan Pelabuhan, serta Pengendalian Risiko Lingkungan.
            </p>
          </div>

          <!-- Quick Navigation & Filter Controls -->
          <div class="flex flex-wrap items-center gap-2.5">
            <!-- Filter Wilayah Kerja -->
            <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-[#1a2130] border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1.5 shadow-2xs">
              <Building2 class="w-4 h-4 text-[#00A99D] shrink-0" />
              <select
                v-model="selectedWilker"
                @change="handleFilterChange"
                class="bg-transparent text-xs font-bold text-slate-800 dark:text-slate-100 border-none focus:ring-0 cursor-pointer outline-none pr-2 py-0.5"
                title="Pilih Wilayah Kerja"
              >
                <option value="all">Semua Wilayah Kerja (Konsolidasi)</option>
                <option v-for="w in wilker_options" :key="w" :value="w">
                  {{ w }}
                </option>
              </select>
            </div>

            <!-- Filter Tahun -->
            <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-[#1a2130] border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1.5 shadow-2xs">
              <Calendar class="w-4 h-4 text-[#7CA622] shrink-0" />
              <select
                v-model.number="selectedYear"
                @change="handleFilterChange"
                class="bg-transparent text-xs font-bold text-slate-800 dark:text-slate-100 border-none focus:ring-0 cursor-pointer outline-none pr-2 py-0.5"
                title="Pilih Tahun Laporan"
              >
                <option v-for="y in availableYears" :key="y" :value="y">
                  Tahun {{ y }}
                </option>
              </select>
            </div>

            <!-- Switch to Form Input Button -->
            <Link
              :href="`/sinkarkes?wilker=${encodeURIComponent(selectedWilker !== 'all' ? selectedWilker : 'POS Bandara Husen')}&year=${selectedYear}`"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-[#00A99D] hover:bg-[#008f85] transition-all shadow-sm cursor-pointer"
            >
              <FileSpreadsheet class="w-4 h-4" />
              <span>Buka Formulir Input &rarr;</span>
            </Link>
          </div>
        </div>
      </div>

      <!-- 6 Key Performance Metric Cards Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
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
          <div class="text-[10px] text-slate-400 mt-1 flex items-center gap-1.5">
            <span>Penumpang: <strong>{{ kpi.total_penumpang || 0 }}</strong></span>
            <span>&bull;</span>
            <span>Kru: <strong>{{ kpi.total_crew || 0 }}</strong></span>
          </div>
        </div>

        <!-- 3. Dokumen Kekarantinaan -->
        <div class="bg-white dark:bg-[#12161f] border border-slate-200/90 dark:border-[#232a36] rounded-2xl p-4 shadow-xs relative overflow-hidden group hover:border-[#005851]/40 transition-colors">
          <div class="flex items-center justify-between text-slate-500 text-[11px] font-mono">
            <span>DOKUMEN KARANTINA</span>
            <div class="w-7 h-7 rounded-xl bg-[#005851]/10 text-[#005851] dark:text-[#00A99D] flex items-center justify-center">
              <FileCheck class="w-3.5 h-3.5" />
            </div>
          </div>
          <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-2 font-mono">
            {{ kpi.total_dokumen?.toLocaleString('id-ID') || 0 }}
          </div>
          <div class="text-[10px] text-slate-400 mt-1 truncate">
            SSCEC, SSCC, P3K, Desinseksi
          </div>
        </div>

        <!-- 4. Layanan Kesehatan -->
        <div class="bg-white dark:bg-[#12161f] border border-slate-200/90 dark:border-[#232a36] rounded-2xl p-4 shadow-xs relative overflow-hidden group hover:border-sky-400/40 transition-colors">
          <div class="flex items-center justify-between text-slate-500 text-[11px] font-mono">
            <span>LAYANAN MEDIS</span>
            <div class="w-7 h-7 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 flex items-center justify-center">
              <HeartPulse class="w-3.5 h-3.5" />
            </div>
          </div>
          <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-2 font-mono">
            {{ kpi.total_layanan_kesehatan?.toLocaleString('id-ID') || 0 }}
          </div>
          <div class="text-[10px] text-slate-400 mt-1 truncate">
            Poliklinik KKP &amp; Uji Laik
          </div>
        </div>

        <!-- 5. Vaksinasi ICV -->
        <div class="bg-white dark:bg-[#12161f] border border-slate-200/90 dark:border-[#232a36] rounded-2xl p-4 shadow-xs relative overflow-hidden group hover:border-[#7CA622]/40 transition-colors">
          <div class="flex items-center justify-between text-slate-500 text-[11px] font-mono">
            <span>VAKSINASI ICV</span>
            <div class="w-7 h-7 rounded-xl bg-[#7CA622]/10 text-[#7CA622] flex items-center justify-center">
              <ShieldCheck class="w-3.5 h-3.5" />
            </div>
          </div>
          <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-2 font-mono">
            {{ kpi.total_vaksinasi?.toLocaleString('id-ID') || 0 }}
          </div>
          <div class="text-[10px] text-slate-400 mt-1 truncate">
            Yellow Fever, Meningitis, dll
          </div>
        </div>

        <!-- 6. Kepatuhan Pelaporan -->
        <div class="bg-white dark:bg-[#12161f] border border-slate-200/90 dark:border-[#232a36] rounded-2xl p-4 shadow-xs relative overflow-hidden group hover:border-emerald-500/40 transition-colors">
          <div class="flex items-center justify-between text-slate-500 text-[11px] font-mono">
            <span>KELENGKAPAN</span>
            <div class="w-7 h-7 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center">
              <CalendarCheck class="w-3.5 h-3.5" />
            </div>
          </div>
          <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-2 font-mono">
            {{ kpi.compliance_rate || 0 }}%
          </div>
          <div class="text-[10px] text-slate-400 mt-1 truncate">
            {{ kpi.submitted_reports_count }} / {{ kpi.total_possible_reports }} bulan terisi
          </div>
        </div>
      </div>

      <!-- Charts Row 1: Traffic Trend (Line) & Document Composition (Pie) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Monthly Traffic Trend Chart (Span 2 cols) -->
        <div class="lg:col-span-2 bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-3xl p-5 sm:p-6 shadow-xs flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-1">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#00A99D]"></span>
                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                  Tren Bulanan Pengawasan Alat Angkut &amp; Orang ({{ year }})
                </h2>
              </div>
              <span class="text-xs text-slate-400 font-mono">12 Bulan</span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
              Dinamika volume kedatangan/keberangkatan pesawat, kapal laut, penumpang, dan kru sepanjang tahun.
            </p>
          </div>

          <div class="w-full h-[320px]">
            <BaseChart :option="trafficOption" height="320px" />
          </div>
        </div>

        <!-- Document & Certificate Composition (Pie Chart) -->
        <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-3xl p-5 sm:p-6 shadow-xs flex flex-col justify-between">
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
              Kunjungan poliklinik KKP, penerbitan surat keterangan sehat, dan vaksinasi haji/umrah/internasional.
            </p>
          </div>

          <div class="w-full h-[320px]">
            <BaseChart :option="healthOption" height="320px" />
          </div>
        </div>
      </div>

      <!-- Reporting Matrix (Wilker x 12 Months Heatmap Table) -->
      <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-3xl p-5 sm:p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
          <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#00A99D]/10 text-[#005851] dark:text-[#00A99D] mb-1">
              <CalendarCheck class="w-3.5 h-3.5" />
              <span>MATRIKS STATUS PELAPORAN TAHUN {{ year }}</span>
            </div>
            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
              Status Kepatuhan Pengisian Laporan Bulanan (Jan &ndash; Des)
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Klik pada sel bulan untuk langsung membuka dan mengedit formulir laporan wilayah kerja tersebut.
            </p>
          </div>

          <div class="flex items-center gap-3 text-xs">
            <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
              <span>Terisi</span>
            </div>
            <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
              <span class="w-2.5 h-2.5 rounded-full bg-slate-200 dark:bg-slate-700"></span>
              <span>Kosong</span>
            </div>
          </div>
        </div>

        <!-- Matrix Table -->
        <div class="overflow-x-auto">
          <table class="w-full min-w-[760px] text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50 dark:bg-[#161c28] text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200 dark:border-slate-800">
                <th class="py-3 px-3 min-w-[200px]">Wilayah Kerja</th>
                <th
                  v-for="m in ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']"
                  :key="m"
                  class="py-3 px-2 text-center w-12"
                >
                  {{ m }}
                </th>
                <th class="py-3 px-3 text-center w-28">Kepatuhan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-[#1f2633]">
              <tr
                v-for="row in matrix"
                :key="row.wilker"
                class="hover:bg-slate-50/80 dark:hover:bg-[#161c28]/50 transition-colors"
              >
                <!-- Wilker Name -->
                <td class="py-2.5 px-3 font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                  <Building2 class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
                  <span class="truncate">{{ row.wilker }}</span>
                </td>

                <!-- 12 Months Status Dots -->
                <td
                  v-for="mId in 12"
                  :key="mId"
                  class="py-2 px-1 text-center"
                >
                  <button
                    type="button"
                    @click="openMonthForm(row.wilker, mId)"
                    class="w-8 h-8 rounded-xl flex flex-col items-center justify-center transition-all cursor-pointer group"
                    :class="[
                      row.months[mId]?.has_data
                        ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 hover:scale-105'
                        : 'bg-slate-50 dark:bg-[#161c28] text-slate-300 dark:text-slate-600 border border-slate-200/60 dark:border-slate-800 hover:border-slate-300'
                    ]"
                    :title="row.months[mId]?.has_data ? `${row.wilker} - Bulan ${mId}: ${row.months[mId].filled_count} indikator terisi. Diperbarui oleh ${row.months[mId].updater || '-'}` : `${row.wilker} - Bulan ${mId}: Belum ada data. Klik untuk menginput.`"
                  >
                    <span
                      class="w-2 h-2 rounded-full"
                      :class="row.months[mId]?.has_data ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600'"
                    ></span>
                    <span v-if="row.months[mId]?.has_data" class="text-[9px] font-mono leading-none mt-0.5">
                      {{ row.months[mId].filled_count }}
                    </span>
                  </button>
                </td>

                <!-- Compliance Badge & Progress -->
                <td class="py-2.5 px-3 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <div class="w-16 h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                      <div
                        class="h-full rounded-full transition-all duration-300"
                        :class="row.compliance_percent >= 80 ? 'bg-emerald-500' : (row.compliance_percent >= 40 ? 'bg-[#00A99D]' : 'bg-amber-500')"
                        :style="{ width: `${row.compliance_percent}%` }"
                      ></div>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 min-w-[36px]">
                      {{ row.compliance_percent }}%
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </HybridNavbar>
</template>
