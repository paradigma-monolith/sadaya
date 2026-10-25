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
        itemStyle: { color: '#0284c7' },
        lineStyle: { width: 3, color: '#0284c7' },
        areaStyle: {
          color: {
            type: 'linear',
            x: 0,
            y: 0,
            x2: 0,
            y2: 1,
            colorStops: [
              { offset: 0, color: 'rgba(2, 132, 199, 0.35)' },
              { offset: 1, color: 'rgba(2, 132, 199, 0.02)' },
            ],
          },
        },
      },
      {
        name: 'Layak',
        type: 'line',
        smooth: true,
        data: props.chart_data.trend_layak,
        itemStyle: { color: '#10b981' },
        lineStyle: { width: 2, color: '#10b981' },
      },
      {
        name: 'Tidak Layak',
        type: 'line',
        smooth: true,
        data: props.chart_data.trend_tidak_layak,
        itemStyle: { color: '#f43f5e' },
        lineStyle: { width: 2, color: '#f43f5e' },
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
      textStyle: { color: '#94a3b8', fontSize: 11 },
    },
    series: [
      {
        name: 'Kelaikan Sanitasi',
        type: 'pie',
        radius: ['50%', '75%'],
        avoidLabelOverlap: false,
        itemStyle: {
          borderRadius: 8,
          borderColor: '#0f172a',
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
      axisLabel: { fontSize: 10, color: '#94a3b8', interval: 0, rotate: 15 },
      axisLine: { lineStyle: { color: '#64748b' } },
    },
    yAxis: {
      type: 'value',
      splitLine: { lineStyle: { color: 'rgba(148, 163, 184, 0.15)' } },
      axisLabel: { fontSize: 10, color: '#94a3b8' },
    },
    series: [
      {
        type: 'bar',
        data: values,
        barWidth: '40%',
        itemStyle: {
          color: {
            type: 'linear',
            x: 0,
            y: 0,
            x2: 0,
            y2: 1,
            colorStops: [
              { offset: 0, color: '#38bdf8' },
              { offset: 1, color: '#0369a1' },
            ],
          },
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
      splitLine: { lineStyle: { color: 'rgba(148, 163, 184, 0.15)' } },
      axisLabel: { fontSize: 10, color: '#94a3b8' },
    },
    yAxis: {
      type: 'category',
      data: ['Gen. Cleaning', 'Disinfeksi', 'Fumigasi', 'Disinseksi'],
      axisLabel: { fontSize: 10.5, color: '#94a3b8' },
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
          color: '#f59e0b',
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
    <main class="w-full px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

      <!-- Hero Banner -->
      <div class="relative bg-gradient-to-r from-sky-950/60 via-slate-900/80 to-indigo-950/60 border border-sky-700/30 dark:border-sky-800/40 rounded-3xl p-6 sm:p-8 backdrop-blur-sm shadow-xl">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
          <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-sky-950/80 text-sky-300 border border-sky-700/40 mb-3">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              <span>Telemetri Kekarantinaan Kesehatan Udara &bull; BKK Bandung</span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
              Dashboard Analisis Sanitasi Pesawat
            </h1>
            <p class="mt-2 text-slate-300 text-sm max-w-2xl leading-relaxed">
              Pemantauan kepatuhan standar International Health Regulations (IHR 2005) dan kelaikan sanitasi seluruh armada penerbangan di wilayah kerja Balai Kekarantinaan Kesehatan Bandung.
            </p>
          </div>

          <!-- Quick Navigation Buttons -->
          <div class="flex flex-wrap items-center gap-2.5">
            <Link
              href="/aircraft-inspections"
              class="px-4 py-2.5 rounded-xl font-semibold text-xs text-white bg-sky-600 hover:bg-sky-500 transition-all shadow-md shadow-sky-600/30 flex items-center gap-1.5 cursor-pointer"
            >
              <ListOrdered class="w-4 h-4" />
              <span>Kelola Data Inspeksi</span>
              <ArrowRight class="w-3.5 h-3.5" />
            </Link>

            <a
              href="/travelconsent/public_aircraft"
              target="_blank"
              class="px-4 py-2.5 rounded-xl font-semibold text-xs text-slate-200 bg-slate-800/80 hover:bg-slate-700 border border-slate-700 transition-colors flex items-center gap-1.5"
            >
              <Plus class="w-4 h-4 text-emerald-400" />
              <span>Buka Form Publik ↗</span>
            </a>
          </div>
        </div>
      </div>

      <!-- 4 Top Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Total Inspections -->
        <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>VOLUME INSPEKSI</span>
            <Plane class="w-4 h-4 text-sky-500" />
          </div>
          <div class="text-3xl font-black text-slate-900 dark:text-white mt-2">
            {{ stats.total }}
          </div>
          <div class="text-xs text-slate-400 mt-1 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-sky-400"></span>
            <span>Total seluruh inspeksi terdaftar</span>
          </div>
        </div>

        <!-- Compliance Rate -->
        <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>KEPATUHAN KELAIKAN</span>
            <CheckCircle2 class="w-4 h-4 text-emerald-500" />
          </div>
          <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-2">
            {{ stats.compliance_rate }}%
          </div>
          <div class="text-xs text-emerald-600/80 dark:text-emerald-400/80 mt-1">
            {{ stats.layak_count }} dari {{ stats.total }} pesawat layak
          </div>
        </div>

        <!-- Unqualified / Defect -->
        <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>PERLU TINDAKAN</span>
            <AlertTriangle class="w-4 h-4 text-rose-500" />
          </div>
          <div class="text-3xl font-black text-rose-600 dark:text-rose-400 mt-2">
            {{ stats.tidak_layak_count }}
          </div>
          <div class="text-xs text-rose-500/80 mt-1">
            Disinseksi / Fumigasi / Disinfeksi
          </div>
        </div>

        <!-- Inspeksi Hari Ini -->
        <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>INSPEKSI HARI INI</span>
            <Activity class="w-4 h-4 text-teal-500 animate-pulse" />
          </div>
          <div class="text-3xl font-black text-teal-600 dark:text-teal-400 mt-2">
            {{ stats.today_count }}
          </div>
          <div class="text-xs text-slate-400 mt-1">
            Pesawat mendarat &amp; diperiksa hari ini
          </div>
        </div>
      </div>

      <!-- Charts Row 1: 14-Day Trend & Donut Ratio -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 14-Day Trend Area Chart -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-white">
                Tren Inspeksi Sanitasi 14 Hari Terakhir
              </h2>
              <p class="text-xs text-slate-500 mt-0.5">
                Fluktuasi volume inspeksi, pesawat layak, dan pesawat yang memerlukan penyehatan
              </p>
            </div>
            <span class="text-xs font-mono px-2.5 py-1 rounded-lg bg-sky-50 dark:bg-sky-950 text-sky-600 font-semibold border border-sky-200 dark:border-sky-800">
              14 Hari
            </span>
          </div>

          <BaseChart :option="trendOption" height="340px" />
        </div>

        <!-- Donut Compliance Ratio -->
        <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs flex flex-col justify-between">
          <div>
            <h2 class="text-base font-bold text-slate-900 dark:text-white">
              Proporsi Kelaikan Terbang
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
              Rasio kelaikan penerbitan Sertifikat Bebas Serangga
            </p>
          </div>

          <div class="py-2">
            <BaseChart :option="ratioOption" height="280px" />
          </div>

          <div class="pt-3 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-500 text-center">
            Standar Bebas Vektor Penyakit &bull; Permenkes 2024
          </div>
        </div>
      </div>

      <!-- Charts Row 2: Airline Breakdown & Measures Applied -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Airline Breakdown -->
        <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-white">
                Volume Inspeksi per Maskapai / Operator
              </h2>
              <p class="text-xs text-slate-500 mt-0.5">
                Distribusi armada penerbangan yang diawasi di BKK Bandung
              </p>
            </div>
          </div>

          <BaseChart :option="airlineOption" height="300px" />
        </div>

        <!-- Measures Applied -->
        <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-white">
                Tindakan Penyehatan yang Diterapkan
              </h2>
              <p class="text-xs text-slate-500 mt-0.5">
                Frekuensi intervensi karantina (Disinseksi, Fumigasi, Disinfeksi, Deep Cleaning)
              </p>
            </div>
          </div>

          <BaseChart :option="actionsOption" height="300px" />
        </div>
      </div>

      <!-- Recent Inspections Summary Table -->
      <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-base font-bold text-slate-900 dark:text-white">
              Inspeksi Sanitasi Terbaru
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
              Daftar 6 pemeriksaan pesawat terkini yang tercatat di sistem
            </p>
          </div>

          <Link
            href="/aircraft-inspections"
            class="text-xs font-semibold text-sky-600 hover:text-sky-500 flex items-center gap-1"
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
