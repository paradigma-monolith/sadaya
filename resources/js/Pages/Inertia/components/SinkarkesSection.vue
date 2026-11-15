<script setup>
import { ref, computed, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import BaseChart from '../../../Components/BaseChart.vue';
import {
  Activity,
  BarChart3,
  Building2,
  Calendar,
  CalendarCheck,
  FileCheck,
  FileSpreadsheet,
  HeartPulse,
  PieChart as PieChartIcon,
  Plane,
  Search,
  ShieldCheck,
  Table,
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

    <!-- Summary Tables Card -->
    <div class="clinical-surface p-4 space-y-3">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2.5 border-b border-slate-100 pb-3">
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

      <div v-if="activeSummaryTab === 'indicators'" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
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

      <!-- TAB 1: Indicators -->
      <div v-if="activeSummaryTab === 'indicators'">
        <div class="md:hidden space-y-2.5">
          <div
            v-for="row in filteredSummaryItems"
            :key="`mob-sum-${row.code}`"
            class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2 text-xs"
          >
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                  <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-[#16202a] font-mono font-bold text-[10px]">{{ row.code }}</span>
                  <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase" :class="{
                    'bg-blue-50 text-blue-600': row.section === 'KSE',
                    'bg-emerald-50 text-emerald-600': row.section === 'UKP',
                    'bg-amber-50 text-amber-600': row.section === 'PRL',
                  }">{{ row.section }}</span>
                  <span class="text-[10px] font-mono text-slate-400">{{ row.unit }}</span>
                </div>
                <p class="font-semibold text-slate-800 dark:text-slate-100 mt-1 leading-snug">{{ row.label }}</p>
              </div>
              <span class="px-2 py-1 rounded-lg bg-[#E6F7F5] dark:bg-[#00A99D]/15 font-mono font-bold text-xs text-[#005851] dark:text-[#5eead4] shrink-0">
                {{ row.total_year > 0 ? row.total_year.toLocaleString('id-ID') : '-' }}
              </span>
            </div>
            <div class="grid grid-cols-4 gap-1 pt-1.5 border-t border-slate-100 dark:border-slate-800 text-[10px] font-mono">
              <div v-for="(m, idx) in monthNames" :key="`mob-ind-${row.code}-${idx}`" class="flex items-center justify-between px-1.5 py-0.5 rounded bg-slate-50 dark:bg-[#16202a]">
                <span class="text-slate-400">{{ m }}</span>
                <span :class="row.months[idx + 1] > 0 ? 'font-bold text-slate-800 dark:text-slate-100' : 'text-slate-300 dark:text-slate-600'">
                  {{ row.months[idx + 1] > 0 ? row.months[idx + 1].toLocaleString('id-ID') : '-' }}
                </span>
              </div>
            </div>
          </div>
          <div v-if="filteredSummaryItems.length === 0" class="py-8 text-center text-slate-400 italic text-xs">
            Tidak ada indikator yang sesuai dengan filter pencarian.
          </div>
        </div>

        <div class="hidden md:block overflow-x-auto rounded-xl border border-slate-200">
          <table class="w-full min-w-[980px] text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50 dark:bg-[#16202a] text-slate-700 dark:text-slate-200 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200 dark:border-slate-800">
                <th class="py-2.5 px-3 w-20 text-center sticky left-0 bg-slate-50 dark:bg-[#16202a] z-10 shadow-xs">Kode</th>
                <th class="py-2.5 px-3 min-w-[240px] sticky left-20 bg-slate-50 dark:bg-[#16202a] z-10 shadow-xs">Uraian Indikator</th>
                <th class="py-2.5 px-2 text-center w-14">Satuan</th>
                <th v-for="m in monthNames" :key="m" class="py-2.5 px-2 text-right w-14 font-mono text-[10px]">{{ m }}</th>
                <th class="py-2.5 px-3 text-right w-24 bg-[#E6F7F5] dark:bg-[#00A99D]/20 text-[#005851] dark:text-[#5eead4] font-mono font-bold text-[11px]">TOTAL TAHUN</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr v-for="row in filteredSummaryItems" :key="row.code" class="hover:bg-[#E6F7F5]/30 dark:hover:bg-[#00A99D]/10 transition-colors group">
                <td class="py-2 px-3 font-mono font-bold text-[11px] text-center text-slate-600 dark:text-slate-300 sticky left-0 bg-white dark:bg-[#111820] group-hover:bg-[#f2fbfa] dark:group-hover:bg-[#142627] z-10 shadow-xs">
                  <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-[#16202a] text-slate-700 dark:text-slate-200">{{ row.code }}</span>
                </td>
                <td class="py-2 px-3 text-slate-800 dark:text-slate-100 font-medium sticky left-20 bg-white dark:bg-[#111820] group-hover:bg-[#f2fbfa] dark:group-hover:bg-[#142627] z-10 shadow-xs">
                  <div class="flex items-center gap-1.5">
                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider shrink-0" :class="{
                      'bg-blue-50 text-blue-600': row.section === 'KSE',
                      'bg-emerald-50 text-emerald-600': row.section === 'UKP',
                      'bg-amber-50 text-amber-600': row.section === 'PRL',
                    }">{{ row.section }}</span>
                    <span class="truncate">{{ row.label }}</span>
                  </div>
                </td>
                <td class="py-2 px-2 text-center text-slate-400 font-mono text-[10px]">{{ row.unit }}</td>
                <td v-for="mId in 12" :key="mId" class="py-2 px-2 text-right font-mono text-[11px]" :class="row.months[mId] > 0 ? 'text-slate-800 dark:text-slate-100 font-medium' : 'text-slate-300 dark:text-slate-600'">
                  {{ row.months[mId] > 0 ? row.months[mId].toLocaleString('id-ID') : '-' }}
                </td>
                <td class="py-2 px-3 text-right font-mono font-bold text-xs bg-[#E6F7F5]/50 dark:bg-[#00A99D]/15 text-[#005851] dark:text-[#5eead4] group-hover:bg-[#E6F7F5] dark:group-hover:bg-[#00A99D]/25 transition-colors">
                  {{ row.total_year > 0 ? row.total_year.toLocaleString('id-ID') : '-' }}
                </td>
              </tr>
              <tr v-if="filteredSummaryItems.length === 0">
                <td colspan="16" class="py-8 text-center text-slate-400 italic">Tidak ada indikator yang sesuai dengan filter pencarian.</td>
              </tr>
            </tbody>
            <tfoot class="bg-slate-100/90 dark:bg-[#16202a] border-t-2 border-slate-300 dark:border-slate-700 font-bold">
              <tr>
                <td colspan="2" class="py-2.5 px-3 text-slate-800 dark:text-slate-100 sticky left-0 bg-slate-100 dark:bg-[#16202a] z-10 shadow-xs text-xs uppercase tracking-wider">TOTAL KESELURUHAN (GRAND TOTAL)</td>
                <td class="py-2.5 px-2 text-center text-slate-500 font-mono text-[10px]">Total</td>
                <td v-for="mId in 12" :key="mId" class="py-2.5 px-2 text-right font-mono text-[11px] text-slate-900 dark:text-slate-100">
                  {{ (sinkarkesSummaryTable?.monthly_totals?.[mId] || 0) > 0 ? sinkarkesSummaryTable.monthly_totals[mId].toLocaleString('id-ID') : '-' }}
                </td>
                <td class="py-2.5 px-3 text-right font-mono font-extrabold text-sm bg-[#00A99D]/20 text-[#005851] dark:text-[#5eead4]">
                  {{ (sinkarkesSummaryTable?.total_year || 0).toLocaleString('id-ID') }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- TAB 2: Wilkers -->
      <div v-else-if="activeSummaryTab === 'wilkers'">
        <div class="md:hidden space-y-2.5">
          <div v-for="wRow in (sinkarkesWilkerSummary?.items || [])" :key="`mob-wil-${wRow.wilker}`" class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2 text-xs">
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-100 min-w-0">
                <Building2 class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
                <span class="truncate">{{ wRow.wilker }}</span>
              </div>
              <div class="flex items-center gap-1.5 shrink-0">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold" :class="wRow.compliance_percent >= 80 ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'">
                  {{ wRow.filled_months }}/12
                </span>
                <span class="px-2 py-0.5 rounded-lg bg-[#F7FCE8] dark:bg-[#1d2d18] font-mono font-bold text-[#557A18] dark:text-[#bef264]">
                  {{ wRow.total_year > 0 ? wRow.total_year.toLocaleString('id-ID') : '-' }}
                </span>
              </div>
            </div>
            <div class="grid grid-cols-4 gap-1 pt-1.5 border-t border-slate-100 dark:border-slate-800 text-[10px] font-mono">
              <div v-for="(m, idx) in monthNames" :key="`mob-w-${wRow.wilker}-${idx}`" class="flex items-center justify-between px-1.5 py-0.5 rounded bg-slate-50 dark:bg-[#16202a]">
                <span class="text-slate-400">{{ m }}</span>
                <span :class="wRow.months[idx + 1] > 0 ? 'font-bold text-slate-800 dark:text-slate-100' : 'text-slate-300 dark:text-slate-600'">
                  {{ wRow.months[idx + 1] > 0 ? wRow.months[idx + 1].toLocaleString('id-ID') : '-' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="hidden md:block overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
          <table class="w-full min-w-[980px] text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50 dark:bg-[#16202a] text-slate-700 dark:text-slate-200 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200 dark:border-slate-800">
                <th class="py-2.5 px-3 min-w-[220px] sticky left-0 bg-slate-50 dark:bg-[#16202a] z-10 shadow-xs">Wilayah Kerja / Pos</th>
                <th class="py-2.5 px-2 text-center w-28">Kepatuhan</th>
                <th v-for="m in monthNames" :key="m" class="py-2.5 px-2 text-right w-14 font-mono text-[10px]">{{ m }}</th>
                <th class="py-2.5 px-3 text-right w-28 bg-[#F7FCE8] dark:bg-[#1d2d18] text-[#557A18] dark:text-[#bef264] font-mono font-bold text-[11px]">TOTAL TAHUN</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr v-for="wRow in (sinkarkesWilkerSummary?.items || [])" :key="wRow.wilker" class="hover:bg-[#E6F7F5]/30 dark:hover:bg-[#00A99D]/10 transition-colors group">
                <td class="py-2.5 px-3 font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2 sticky left-0 bg-white dark:bg-[#111820] group-hover:bg-[#f2fbfa] dark:group-hover:bg-[#142627] z-10 shadow-xs">
                  <Building2 class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
                  <span class="truncate">{{ wRow.wilker }}</span>
                </td>
                <td class="py-2.5 px-2 text-center">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold" :class="wRow.compliance_percent >= 80 ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-300' : 'bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-300'">
                    {{ wRow.filled_months }}/12 bln ({{ wRow.compliance_percent }}%)
                  </span>
                </td>
                <td v-for="mId in 12" :key="mId" class="py-2.5 px-2 text-right font-mono text-[11px]" :class="wRow.months[mId] > 0 ? 'text-slate-800 dark:text-slate-100 font-medium' : 'text-slate-300 dark:text-slate-600'">
                  {{ wRow.months[mId] > 0 ? wRow.months[mId].toLocaleString('id-ID') : '-' }}
                </td>
                <td class="py-2.5 px-3 text-right font-mono font-bold text-xs bg-[#F7FCE8]/60 dark:bg-[#1d2d18]/75 text-[#557A18] dark:text-[#bef264] group-hover:bg-[#F7FCE8] dark:group-hover:bg-[#24391d] transition-colors">
                  {{ wRow.total_year > 0 ? wRow.total_year.toLocaleString('id-ID') : '-' }}
                </td>
              </tr>
            </tbody>
            <tfoot class="bg-slate-100/90 dark:bg-[#16202a] border-t-2 border-slate-300 dark:border-slate-700 font-bold">
              <tr>
                <td class="py-2.5 px-3 text-slate-800 dark:text-slate-100 sticky left-0 bg-slate-100 dark:bg-[#16202a] z-10 shadow-xs text-xs uppercase tracking-wider">TOTAL KONSOLIDASI BKK BANDUNG</td>
                <td class="py-2.5 px-2 text-center text-slate-500 dark:text-slate-400 font-mono text-[10px]">7 Wilker</td>
                <td v-for="mId in 12" :key="mId" class="py-2.5 px-2 text-right font-mono text-[11px] text-slate-900 dark:text-slate-100">
                  {{ (sinkarkesWilkerSummary?.monthly_totals?.[mId] || 0) > 0 ? sinkarkesWilkerSummary.monthly_totals[mId].toLocaleString('id-ID') : '-' }}
                </td>
                <td class="py-2.5 px-3 text-right font-mono font-extrabold text-sm bg-[#99CC33]/25 dark:bg-[#99CC33]/20 text-[#557A18] dark:text-[#d9f99d]">
                  {{ (sinkarkesWilkerSummary?.total_year || 0).toLocaleString('id-ID') }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- TAB 3: Compliance -->
      <div v-else-if="activeSummaryTab === 'compliance'" class="space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500 px-1">
          <span>Klik pada sel bulan untuk membuka dan mengisi formulir bulanan wilayah kerja terkait.</span>
          <div class="flex items-center gap-3">
            <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span><span>Terisi</span></div>
            <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-slate-200 dark:bg-slate-700"></span><span>Kosong</span></div>
          </div>
        </div>

        <div class="md:hidden space-y-2.5">
          <div v-for="row in sinkarkesMatrix" :key="`mob-mat-${row.wilker}`" class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2 text-xs">
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-100 min-w-0">
                <Building2 class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
                <span class="truncate">{{ row.wilker }}</span>
              </div>
              <span class="font-mono font-bold text-slate-700 dark:text-slate-300 shrink-0">{{ row.compliance_percent }}%</span>
            </div>
            <div class="grid grid-cols-6 gap-1.5">
              <button
                v-for="(m, idx) in monthNames"
                :key="`mob-dot-${row.wilker}-${idx}`"
                type="button"
                @click="openSinkarkesMonthForm(row.wilker, idx + 1)"
                :aria-label="row.months[idx + 1]?.has_data ? `${row.wilker} bulan ${m}: ${row.months[idx + 1].filled_count} indikator` : `Isi ${row.wilker} bulan ${m}`"
                class="py-1.5 rounded-lg flex flex-col items-center justify-center text-[10px] font-mono border cursor-pointer"
                :class="row.months[idx + 1]?.has_data ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-400 border-slate-200/70'"
              >
                <span>{{ m }}</span>
                <span class="text-[9px] font-bold">{{ row.months[idx + 1]?.has_data ? row.months[idx + 1].filled_count : '-' }}</span>
              </button>
            </div>
          </div>
        </div>

        <div class="hidden md:block overflow-x-auto rounded-xl border border-slate-200">
          <table class="w-full min-w-[760px] text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                <th class="py-2.5 px-3 min-w-[200px]">Wilayah Kerja</th>
                <th v-for="m in monthNames" :key="m" class="py-2.5 px-2 text-center w-12 text-[10px]">{{ m }}</th>
                <th class="py-2.5 px-3 text-center w-28">Kepatuhan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="row in sinkarkesMatrix" :key="row.wilker" class="hover:bg-slate-50/80 transition-colors">
                <td class="py-2 px-3 font-semibold text-slate-800 flex items-center gap-2">
                  <Building2 class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
                  <span class="truncate">{{ row.wilker }}</span>
                </td>
                <td v-for="mId in 12" :key="mId" class="py-1.5 px-1 text-center">
                  <button
                    type="button"
                    @click="openSinkarkesMonthForm(row.wilker, mId)"
                    class="w-8 h-8 rounded-lg flex flex-col items-center justify-center transition-all cursor-pointer mx-auto"
                    :class="row.months[mId]?.has_data ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:scale-105' : 'bg-slate-50 text-slate-300 border border-slate-200/60 hover:border-slate-300'"
                    :aria-label="row.months[mId]?.has_data ? `${row.wilker} bulan ${mId}: ${row.months[mId].filled_count} indikator terisi` : `${row.wilker} bulan ${mId}: belum ada data`"
                    :title="row.months[mId]?.has_data ? `${row.wilker} - Bulan ${mId}: ${row.months[mId].filled_count} indikator terisi.` : `${row.wilker} - Bulan ${mId}: Belum ada data. Klik untuk mengisi.`"
                  >
                    <span class="w-2 h-2 rounded-full" :class="row.months[mId]?.has_data ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                    <span v-if="row.months[mId]?.has_data" class="text-[9px] font-mono leading-none mt-0.5">{{ row.months[mId].filled_count }}</span>
                  </button>
                </td>
                <td class="py-2 px-3 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <div class="w-16 h-2 bg-slate-200 rounded-full overflow-hidden">
                      <div class="h-full rounded-full transition-all duration-300" :class="row.compliance_percent >= 80 ? 'bg-emerald-500' : (row.compliance_percent >= 40 ? 'bg-[#00A99D]' : 'bg-amber-500')" :style="{ width: `${row.compliance_percent}%` }"></div>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-700 min-w-[36px]">{{ row.compliance_percent }}%</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
