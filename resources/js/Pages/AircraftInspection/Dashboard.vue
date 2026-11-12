<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import BaseChart from '../../Components/BaseChart.vue';
import { useDate } from '../../composables/useDate';
import {
  Plane,
  ShieldCheck,
  AlertTriangle,
  FileCheck,
  CheckCircle2,
  XCircle,
  ExternalLink,
  Plus,
  ArrowRight,
  Download,
  ListOrdered,
  Calendar,
  Activity
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

// 1. Trend Line & Bar Area Chart (14 Days)
const trendOption = computed(() => {
  return {
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'cross' },
    },
    legend: {
      data: ['Total Inspeksi', 'Layak', 'Tidak Layak'],
      bottom: 0,
      textStyle: { color: '#94a3b8', fontSize: 11 },
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
      data: props.chart_data.trend_dates,
      axisLine: { lineStyle: { color: '#64748b' } },
      axisLabel: { fontSize: 10, color: '#94a3b8' },
    },
    yAxis: {
      type: 'value',
      splitLine: { lineStyle: { color: 'rgba(148, 163, 184, 0.15)' } },
      axisLabel: { fontSize: 10, color: '#94a3b8' },
    },
    series: [
      {
        name: 'Total Inspeksi',
        type: 'line',
        smooth: true,
        data: props.chart_data.trend_total,
        itemStyle: { color: '#10b981' },
        lineStyle: { width: 2.5, color: '#10b981' },
        areaStyle: {
          color: {
            type: 'linear',
            x: 0,
            y: 0,
            x2: 0,
            y2: 1,
            colorStops: [
              { offset: 0, color: 'rgba(16, 185, 129, 0.22)' },
              { offset: 1, color: 'rgba(16, 185, 129, 0.0)' },
            ],
          },
        },
      },
      {
        name: 'Layak',
        type: 'line',
        smooth: true,
        data: props.chart_data.trend_layak,
        itemStyle: { color: '#0f172a' },
        lineStyle: { width: 2, color: '#0f172a' },
      },
      {
        name: 'Tidak Layak',
        type: 'line',
        smooth: true,
        data: props.chart_data.trend_tidak_layak,
        itemStyle: { color: '#e11d48' },
        lineStyle: { width: 2, color: '#e11d48' },
      },
    ],
  };
});

// 2. Compliance Donut Ratio Chart
const ratioOption = computed(() => {
  return {
    tooltip: {
      trigger: 'item',
      formatter: '{b}: {c} ({d}%)',
    },
    legend: {
      bottom: '2%',
      left: 'center',
      textStyle: { color: '#64748b', fontSize: 11 },
    },
    series: [
      {
        name: 'Kelaikan Sanitasi',
        type: 'pie',
        radius: ['50%', '75%'],
        avoidLabelOverlap: false,
        itemStyle: {
          borderRadius: 8,
          borderColor: '#ffffff',
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
          },
        },
        data: [
          { value: props.stats.layak_count, name: 'Layak Bebas Serangga', itemStyle: { color: '#10b981' } },
          { value: props.stats.tidak_layak_count, name: 'Tidak Layak (Tindakan)', itemStyle: { color: '#f43f5e' } },
        ],
      },
    ],
  };
});

// 3. Airline Inspections Bar Chart
const airlineOption = computed(() => {
  const names = props.chart_data.airlines.map((a) => a.name);
  const values = props.chart_data.airlines.map((a) => a.value);

  return {
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'shadow' },
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '8%',
      top: '8%',
      containLabel: true,
    },
    xAxis: {
      type: 'category',
      data: names,
      axisLabel: { fontSize: 10, color: '#64748b', interval: 0, rotate: 15 },
      axisLine: { lineStyle: { color: '#e2e8f0' } },
    },
    yAxis: {
      type: 'value',
      splitLine: { lineStyle: { color: '#f1f5f9' } },
      axisLabel: { fontSize: 10, color: '#64748b' },
    },
    series: [
      {
        type: 'bar',
        data: values,
        barWidth: '40%',
        itemStyle: {
          color: '#10b981',
          borderRadius: [6, 6, 0, 0],
        },
      },
    ],
  };
});

// 4. Measures Breakdown Chart
const actionsOption = computed(() => {
  const actions = props.chart_data.actions;
  return {
    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
    grid: { left: '3%', right: '4%', bottom: '5%', top: '8%', containLabel: true },
    xAxis: {
      type: 'value',
      splitLine: { lineStyle: { color: '#f1f5f9' } },
      axisLabel: { fontSize: 10, color: '#64748b' },
    },
    yAxis: {
      type: 'category',
      data: ['Gen. Cleaning', 'Disinfeksi', 'Fumigasi', 'Disinseksi'],
      axisLabel: { fontSize: 10.5, color: '#64748b' },
    },
    series: [
      {
        type: 'bar',
        data: [
          actions.general_cleaning || 0,
          actions.disinfeksi || 0,
          actions.fumigasi || 0,
          actions.disinseksi || 0,
        ],
        barWidth: '50%',
        itemStyle: {
          color: '#84cc16',
          borderRadius: [0, 6, 6, 0],
        },
      },
    ],
  };
});
</script>

<template>
  <Head title="Dashboard Inspeksi Sanitasi Pesawat &bull; BKK Bandung" />

  <HybridNavbar mode="inertia" currentRoute="aircraft-dashboard">
    <main class="clinical-main">

      <!-- Clinical Header Banner -->
      <div class="clinical-header flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-9 h-9 rounded-lg bg-[#002B27] border border-[#00A99D]/40 text-[#99CC33] flex items-center justify-center shrink-0">
            <Plane class="w-4 h-4" />
          </div>
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <h1 class="text-sm sm:text-[15px] font-bold text-white tracking-tight">
                Dashboard Analisis Sanitasi Pesawat
              </h1>
              <span class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-[#002B27]/80 text-[#99DFD6] border border-[#00A99D]/30">
                IHR 2005
              </span>
            </div>
            <p class="mt-0.5 text-[11px] text-slate-200/85 truncate">
              Pemantauan kepatuhan standar IHR 2005 dan kelaikan sanitasi armada penerbangan wilayah kerja BKK Bandung.
            </p>
          </div>
        </div>

        <!-- Quick Navigation Buttons -->
        <div class="flex flex-wrap items-center gap-1.5 shrink-0">
          <Link
            href="/aircraft-inspections"
            class="px-3 py-1.5 rounded-lg font-semibold text-xs text-white bg-white/10 hover:bg-white/20 border border-white/15 transition-colors flex items-center gap-1.5 cursor-pointer"
          >
            <ListOrdered class="w-3.5 h-3.5 text-[#99DFD6]" />
            <span>Kelola Data</span>
            <ArrowRight class="w-3 h-3" />
          </Link>

          <a
            href="/travelconsent/public_aircraft"
            target="_blank"
            class="px-3 py-1.5 rounded-lg font-semibold text-xs text-white bg-[#00A99D] hover:bg-[#009388] transition-colors flex items-center gap-1.5"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>Form Publik ↗</span>
          </a>
        </div>
      </div>

      <!-- 4 Top Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Total Inspections -->
        <div class="clinical-kpi">
          <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-semibold uppercase tracking-wider">
            <span>Volume Inspeksi</span>
            <Plane class="w-3.5 h-3.5 text-[#00A99D]" />
          </div>
          <div class="text-2xl font-bold text-slate-900 mt-1.5 font-mono clinical-num">
            {{ stats.total }}
          </div>
          <div class="text-[11px] text-slate-500 mt-0.5">
            Total seluruh inspeksi terdaftar
          </div>
        </div>

        <!-- Compliance Rate -->
        <div class="clinical-kpi">
          <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-semibold uppercase tracking-wider">
            <span>Kepatuhan Kelaikan</span>
            <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600" />
          </div>
          <div class="text-2xl font-bold text-[#005851] mt-1.5 font-mono clinical-num">
            {{ stats.compliance_rate }}%
          </div>
          <div class="text-[11px] text-slate-500 mt-0.5 clinical-num">
            {{ stats.layak_count }} dari {{ stats.total }} pesawat layak
          </div>
        </div>

        <!-- Unqualified / Defect -->
        <div class="clinical-kpi">
          <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-semibold uppercase tracking-wider">
            <span>Perlu Tindakan</span>
            <AlertTriangle class="w-3.5 h-3.5 text-rose-500" />
          </div>
          <div class="text-2xl font-bold text-rose-600 mt-1.5 font-mono clinical-num">
            {{ stats.tidak_layak_count }}
          </div>
          <div class="text-[11px] text-rose-600 mt-0.5">
            Disinseksi / Fumigasi / Disinfeksi
          </div>
        </div>

        <!-- Inspeksi Hari Ini -->
        <div class="clinical-kpi">
          <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-semibold uppercase tracking-wider">
            <span>Inspeksi Hari Ini</span>
            <Activity class="w-3.5 h-3.5 text-[#00A99D]" />
          </div>
          <div class="text-2xl font-bold text-slate-900 mt-1.5 font-mono clinical-num">
            {{ stats.today_count }}
          </div>
          <div class="text-[11px] text-slate-500 mt-0.5">
            Pesawat mendarat &amp; diperiksa hari ini
          </div>
        </div>
      </div>

      <!-- Charts Row 1: 14-Day Trend & Donut Ratio -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
        <!-- 14-Day Trend Area Chart -->
        <div class="lg:col-span-2 clinical-surface p-4">
          <div class="flex items-center justify-between mb-3">
            <div>
              <h2 class="text-sm font-bold text-slate-900">
                Tren Inspeksi Sanitasi 14 Hari Terakhir
              </h2>
              <p class="text-xs text-slate-500 mt-0.5">
                Fluktuasi volume inspeksi, pesawat layak, dan pesawat yang memerlukan penyehatan
              </p>
            </div>
            <span class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-teal-50 text-[#005851] font-semibold border border-teal-200">
              14 Hari
            </span>
          </div>

          <BaseChart :option="trendOption" height="320px" />
        </div>

        <!-- Donut Compliance Ratio -->
        <div class="clinical-surface p-4 flex flex-col justify-between">
          <div>
            <h2 class="text-sm font-bold text-slate-900">
              Proporsi Kelaikan Terbang
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
              Rasio kelaikan penerbitan Sertifikat Bebas Serangga
            </p>
          </div>

          <div class="py-1.5">
            <BaseChart :option="ratioOption" height="270px" />
          </div>

          <div class="pt-2.5 border-t border-slate-100 text-[11px] text-slate-500 text-center">
            Standar Bebas Vektor Penyakit &bull; Permenkes 2024
          </div>
        </div>
      </div>

      <!-- Charts Row 2: Airline Breakdown & Measures Applied -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
        <!-- Airline Breakdown -->
        <div class="clinical-surface p-4">
          <div class="flex items-center justify-between mb-3">
            <div>
              <h2 class="text-sm font-bold text-slate-900">
                Volume Inspeksi per Maskapai / Operator
              </h2>
              <p class="text-xs text-slate-500 mt-0.5">
                Distribusi armada penerbangan yang diawasi di BKK Bandung
              </p>
            </div>
          </div>

          <BaseChart :option="airlineOption" height="280px" />
        </div>

        <!-- Measures Applied -->
        <div class="clinical-surface p-4">
          <div class="flex items-center justify-between mb-3">
            <div>
              <h2 class="text-sm font-bold text-slate-900">
                Tindakan Penyehatan yang Diterapkan
              </h2>
              <p class="text-xs text-slate-500 mt-0.5">
                Frekuensi intervensi karantina (Disinseksi, Fumigasi, Disinfeksi, Deep Cleaning)
              </p>
            </div>
          </div>

          <BaseChart :option="actionsOption" height="280px" />
        </div>
      </div>

      <!-- Recent Inspections Summary Table -->
      <div class="clinical-surface p-4">
        <div class="flex items-center justify-between mb-3">
          <div>
            <h2 class="text-sm font-bold text-slate-900">
              Inspeksi Sanitasi Terbaru
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
              Daftar 6 pemeriksaan pesawat terkini yang tercatat di sistem
            </p>
          </div>

          <Link
            href="/aircraft-inspections"
            class="text-xs font-semibold text-[#005851] hover:text-[#004640] flex items-center gap-1"
          >
            <span>Lihat Semua Data</span>
            <ArrowRight class="w-3.5 h-3.5" />
          </Link>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 font-semibold uppercase text-[10.5px]">
                <th class="py-2.5">No. Registrasi</th>
                <th class="py-2.5">Maskapai</th>
                <th class="py-2.5">Tanda Reg</th>
                <th class="py-2.5">Tanggal</th>
                <th class="py-2.5">Kelaikan</th>
                <th class="py-2.5">Petugas BKK</th>
                <th class="py-2.5 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
              <tr v-for="item in recent" :key="item.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                <td class="py-3 font-mono font-bold text-sky-600 dark:text-sky-400">
                  {{ item.registration_no }}
                </td>
                <td class="py-3 font-medium text-slate-800 dark:text-slate-200">
                  {{ item.pemilik }}
                </td>
                <td class="py-3 font-mono font-bold text-slate-700 dark:text-slate-300">
                  {{ item.tanda_kebangsaan }}
                </td>
                <td class="py-3 text-slate-500">
                  {{ formatDate(item.tanggal) }} ({{ item.waktu }} WIB)
                </td>
                <td class="py-3">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10.5px] font-semibold border inline-flex items-center gap-1"
                    :class="item.kesimpulan === 'layak'
                      ? 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                      : 'bg-rose-50 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800'"
                  >
                    <span>{{ item.kesimpulan === 'layak' ? 'Layak' : 'Tidak Layak' }}</span>
                  </span>
                </td>
                <td class="py-3 text-slate-600 dark:text-slate-300">
                  {{ item.petugas_bkk_nama }}
                </td>
                <td class="py-3 text-right">
                  <a
                    :href="`/aircraft-inspections/${item.id}/pdf`"
                    class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 transition-colors inline-flex items-center gap-1"
                    title="Unduh PDF Resmi"
                  >
                    <Download class="w-3.5 h-3.5 text-emerald-500" />
                    <span>PDF</span>
                  </a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </HybridNavbar>
</template>
