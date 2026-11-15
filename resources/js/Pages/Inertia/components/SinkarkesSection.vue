<script setup>
import { ref, computed, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import BaseChart from '../../../Components/BaseChart.vue';
import SinkarkesSummaryCard from '../../Sinkarkes/components/SinkarkesSummaryCard.vue';
import {
  BarChart3,
  Building2,
  Calendar,
  CalendarCheck,
  FileCheck,
  FileSpreadsheet,
  HeartPulse,
  PieChart as PieChartIcon,
  Plane,
  ShieldCheck,
  Users,
} from 'lucide-vue-next';

const props = defineProps({
  sinkarkes: {
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

const sinkarkesDocumentOption = computed(() => ({
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
}));

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
  <div id="sinkarkes-section" class="space-y-3 font-sans">
    <!-- SINKARKES Header & Filter Controls Banner -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-[0_1px_2px_rgba(15,23,42,0.03)] overflow-hidden">
      <div class="px-4 py-2.5 bg-[#004640] text-white flex flex-col lg:flex-row lg:items-center justify-between gap-2.5 border-b border-[#003B36]">
        <div class="flex items-center gap-2.5 min-w-0">
          <div class="w-7 h-7 rounded-md bg-[#00A99D] text-white flex items-center justify-center shrink-0">
            <BarChart3 class="w-4 h-4" />
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <h2 class="text-xs sm:text-sm font-bold tracking-tight text-white">
                Ringkasan Eksekutif &amp; Rekapitulasi SINKARKES ({{ selectedSinkarkesYear }})
              </h2>
              <span class="px-1.5 py-0.2 rounded text-[10px] font-mono font-semibold bg-[#002D29] text-[#99CC33] border border-[#99CC33]/35">
                KSE &bull; UKP &bull; PRL
              </span>
            </div>
            <p class="text-[11px] text-slate-200/85 truncate">
              Konsolidasi pengawasan alat angkut, orang, dokumen karantina, poliklinik &amp; vaksinasi ICV
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-1.5 shrink-0">
          <div class="flex items-center gap-1.5 bg-[#002D29] border border-white/15 rounded-lg px-2.5 py-1 text-white">
            <Building2 class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
            <select
              v-model="selectedSinkarkesWilker"
              @change="handleSinkarkesFilterChange"
              class="bg-transparent text-xs font-semibold text-white border-none focus:ring-0 cursor-pointer outline-none pr-1"
              title="Filter Wilayah Kerja SINKARKES"
            >
              <option value="all" class="text-slate-900">Semua Wilayah Kerja (Konsolidasi)</option>
              <option v-for="w in sinkarkesWilkerOptions" :key="w" :value="w" class="text-slate-900">
                {{ w }}
              </option>
            </select>
          </div>

          <div class="flex items-center gap-1.5 bg-[#002D29] border border-white/15 rounded-lg px-2.5 py-1 text-white">
            <Calendar class="w-3.5 h-3.5 text-[#99CC33] shrink-0" />
            <select
              v-model.number="selectedSinkarkesYear"
              @change="handleSinkarkesFilterChange"
              class="bg-transparent text-xs font-semibold text-white border-none focus:ring-0 cursor-pointer outline-none pr-1"
              title="Pilih Tahun Laporan SINKARKES"
            >
              <option v-for="y in availableSinkarkesYears" :key="y" :value="y" class="text-slate-900">
                Tahun {{ y }}
              </option>
            </select>
          </div>

          <Link
            :href="getUrl(`/sinkarkes?wilker=${encodeURIComponent(selectedSinkarkesWilker !== 'all' ? selectedSinkarkesWilker : 'POS Bandara Husen')}&year=${selectedSinkarkesYear}`)"
            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold text-white bg-[#00A99D] hover:bg-[#009388] transition-colors cursor-pointer"
          >
            <FileSpreadsheet class="w-3.5 h-3.5" />
            <span>Formulir Input &rarr;</span>
          </Link>
        </div>
      </div>

      <!-- 6 Key Performance Metric Cards Grid -->
      <div class="p-3 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 bg-slate-50/70">
        <div class="bg-white border border-slate-200/90 rounded-lg p-2.5 hover:border-[#00A99D] transition-colors">
          <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-semibold">
            <span>ALAT ANGKUT</span>
            <div class="w-5 h-5 rounded bg-[#E6F7F5] text-[#005851] flex items-center justify-center">
              <Plane class="w-3 h-3" />
            </div>
          </div>
          <div class="text-lg sm:text-xl font-bold text-slate-900 mt-1 font-mono tabular-nums">
            {{ (sinkarkesKpi.total_alat_angkut || 0).toLocaleString('id-ID') }}
          </div>
          <div class="text-[10px] text-slate-500 mt-0.5 flex items-center gap-1 truncate tabular-nums">
            <span>Pswt: <strong class="text-slate-700">{{ (sinkarkesKpi.total_pesawat || 0).toLocaleString('id-ID') }}</strong></span>
            <span>&bull;</span>
            <span>Kpl: <strong class="text-slate-700">{{ (sinkarkesKpi.total_kapal || 0).toLocaleString('id-ID') }}</strong></span>
          </div>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-lg p-2.5 hover:border-amber-400 transition-colors">
          <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-semibold">
            <span>ORANG TERAWASI</span>
            <div class="w-5 h-5 rounded bg-amber-50 text-amber-600 flex items-center justify-center">
              <Users class="w-3 h-3" />
            </div>
          </div>
          <div class="text-lg sm:text-xl font-bold text-slate-900 mt-1 font-mono tabular-nums">
            {{ (sinkarkesKpi.total_orang || 0).toLocaleString('id-ID') }}
          </div>
          <div class="text-[10px] text-slate-500 mt-0.5 flex items-center gap-1 truncate tabular-nums">
            <span>Pnp: <strong class="text-slate-700">{{ (sinkarkesKpi.total_penumpang || 0).toLocaleString('id-ID') }}</strong></span>
            <span>&bull;</span>
            <span>Kru: <strong class="text-slate-700">{{ (sinkarkesKpi.total_crew || 0).toLocaleString('id-ID') }}</strong></span>
          </div>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-lg p-2.5 hover:border-[#005851] transition-colors">
          <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-semibold">
            <span>DOKUMEN KARANTINA</span>
            <div class="w-5 h-5 rounded bg-[#E0EFEF] text-[#005851] flex items-center justify-center">
              <FileCheck class="w-3 h-3" />
            </div>
          </div>
          <div class="text-lg sm:text-xl font-bold text-slate-900 mt-1 font-mono tabular-nums">
            {{ (sinkarkesKpi.total_dokumen || 0).toLocaleString('id-ID') }}
          </div>
          <div class="text-[10px] text-slate-500 mt-0.5 truncate">
            SSCEC, SSCC, P3K, Desinseksi
          </div>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-lg p-2.5 hover:border-sky-400 transition-colors">
          <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-semibold">
            <span>LAYANAN MEDIS</span>
            <div class="w-5 h-5 rounded bg-sky-50 text-sky-600 flex items-center justify-center">
              <HeartPulse class="w-3 h-3" />
            </div>
          </div>
          <div class="text-lg sm:text-xl font-bold text-slate-900 mt-1 font-mono tabular-nums">
            {{ (sinkarkesKpi.total_layanan_kesehatan || 0).toLocaleString('id-ID') }}
          </div>
          <div class="text-[10px] text-slate-500 mt-0.5 truncate">
            Poliklinik BKK &amp; Uji Laik
          </div>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-lg p-2.5 hover:border-[#7CA622] transition-colors">
          <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-semibold">
            <span>VAKSINASI ICV</span>
            <div class="w-5 h-5 rounded bg-[#F7FCE8] text-[#557A18] flex items-center justify-center">
              <ShieldCheck class="w-3 h-3" />
            </div>
          </div>
          <div class="text-lg sm:text-xl font-bold text-slate-900 mt-1 font-mono tabular-nums">
            {{ (sinkarkesKpi.total_vaksinasi || 0).toLocaleString('id-ID') }}
          </div>
          <div class="text-[10px] text-slate-500 mt-0.5 truncate">
            Yellow Fever, Meningitis, dll
          </div>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-lg p-2.5 hover:border-emerald-500 transition-colors">
          <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-semibold">
            <span>KELENGKAPAN</span>
            <div class="w-5 h-5 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <CalendarCheck class="w-3 h-3" />
            </div>
          </div>
          <div class="text-lg sm:text-xl font-bold text-slate-900 mt-1 font-mono tabular-nums">
            {{ sinkarkesKpi.compliance_rate || 0 }}%
          </div>
          <div class="text-[10px] text-slate-500 mt-0.5 truncate tabular-nums">
            {{ sinkarkesKpi.submitted_reports_count || 0 }} / {{ sinkarkesKpi.total_possible_reports || 84 }} bulan terisi
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3.5">
      <div class="lg:col-span-2 clinical-surface p-4 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-1">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-[#00A99D]"></span>
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
        <div class="w-full h-[265px]">
          <BaseChart :option="sinkarkesTrafficOption" height="265px" />
        </div>
      </div>

      <div class="clinical-surface p-4 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-1">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-[#005851]"></span>
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
        <div class="w-full h-[265px]">
          <BaseChart :option="sinkarkesDocumentOption" height="265px" />
        </div>
      </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
      <div class="clinical-surface p-4 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-1">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-[#7CA622]"></span>
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
        <div class="w-full h-[265px]">
          <BaseChart :option="sinkarkesWilkerOption" height="265px" />
        </div>
      </div>

      <div class="clinical-surface p-4 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-1">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-sky-600"></span>
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
        <div class="w-full h-[265px]">
          <BaseChart :option="sinkarkesHealthOption" height="265px" />
        </div>
      </div>
    </div>

    <SinkarkesSummaryCard
      :year="Number(selectedSinkarkesYear)"
      :is-consolidated="sinkarkesData.is_consolidated !== false"
      :selected-wilker="String(selectedSinkarkesWilker)"
      :summary-table="sinkarkesSummaryTable"
      :wilker-summary="sinkarkesWilkerSummary"
      :matrix="sinkarkesMatrix"
      @open-month-form="openSinkarkesMonthForm"
    />
  </div>
</template>
