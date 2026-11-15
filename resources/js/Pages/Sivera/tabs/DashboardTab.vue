<script setup>
import { ref, computed } from 'vue';
import {
  Bug,
  Droplets,
  Activity,
  Rat,
  Layers,
  Wind,
  BarChart3,
  Table,
  Search,
  X,
} from 'lucide-vue-next';
import { kecoaSpeciesColors, tikusSpeciesColors } from '../utils';

const props = defineProps({
  dashboardData: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['switch-vektor']);

const siveraCardViews = ref({
  kecoa: 'chart',
  lalat: 'chart',
  jentik: 'chart',
  tikus: 'chart',
});

const activeSiveraDetailTab = ref('semua');
const siveraWilkerSearch = ref('');

const siveraDashData = computed(() => {
  if (props.dashboardData && props.dashboardData.summary) {
    return props.dashboardData;
  }
  return {
    labels: [],
    summary: {
      total_surveilans: 0,
      jentik: 0,
      kontainer_positif: 0,
      abj_avg: 100,
      lalat: 0,
      avg_lalat: 0,
      lalat_ms: 0,
      lalat_tms: 0,
      kecoa: 0,
      kecoa_tertangkap: 0,
      kecoa_perangkap: 0,
      avg_kecoa: 0,
      tikus: 0,
      tikus_tertangkap: 0,
      tikus_perangkap: 0,
      avg_success_trap: 0,
      pinjal: 0,
      pinjal_umum: 0,
      pinjal_khusus: 0,
      anopheles_jentik: 0,
      anopheles_nyamuk: 0,
      avg_indeks_habitat: 0,
      avg_mbr: 0,
      kepatuhan_pct: 100,
    },
    charts: {
      kecoa: {},
      lalat_status: { MS: [], TMS: [] },
      jentik_kontainer: [],
      spesies_tikus: {},
    },
    wilker_detail: [],
  };
});

const buildStackedSpeciesAgg = (chartData, labels) => {
  const chart = chartData || {};
  const species = Object.keys(chart);
  const speciesTotals = {};
  let grandTotal = 0;
  for (const sp of species) {
    const sum = (chart[sp] || []).reduce((a, b) => a + Number(b || 0), 0);
    speciesTotals[sp] = sum;
    grandTotal += sum;
  }
  const wilkerTotals = labels.map((_, idx) =>
    species.reduce((acc, sp) => acc + Number((chart[sp] && chart[sp][idx]) || 0), 0)
  );
  const maxWilkerTotal = Math.max(...wilkerTotals, 1);
  return { chart, labels, species, speciesTotals, grandTotal, wilkerTotals, maxWilkerTotal };
};

const kecoaChartAgg = computed(() =>
  buildStackedSpeciesAgg(siveraDashData.value.charts?.kecoa, siveraDashData.value.labels || [])
);

const tikusChartAgg = computed(() =>
  buildStackedSpeciesAgg(siveraDashData.value.charts?.spesies_tikus, siveraDashData.value.labels || [])
);

const stackedSpeciesCards = computed(() => [
  {
    key: 'kecoa',
    title: 'Spesies Kecoa per Wilker',
    badge: 'Kecoa',
    badgeClass: 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border-rose-200 dark:border-rose-800',
    totalPillClass: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
    subtitle: 'Perbandingan spesies tertangkap pada masing-masing wilayah kerja.',
    emptyChartText: 'Belum ada data wilayah kerja surveilans kecoa.',
    emptyTableText: 'Belum ada data surveilans kecoa',
    colors: kecoaSpeciesColors,
    agg: kecoaChartAgg.value,
  },
  {
    key: 'tikus',
    title: 'Sebaran Spesies Tikus',
    badge: 'Tikus & Pinjal',
    badgeClass: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
    totalPillClass: 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800',
    subtitle: 'Distribusi spesies tikus tertangkap pada masing-masing wilayah kerja.',
    emptyChartText: 'Belum ada data wilayah kerja surveilans tikus.',
    emptyTableText: 'Belum ada data surveilans tikus',
    colors: tikusSpeciesColors,
    agg: tikusChartAgg.value,
  },
]);

const lalatChartAgg = computed(() => {
  const chart = siveraDashData.value.charts?.lalat_status || { MS: [], TMS: [] };
  const labels = siveraDashData.value.labels || [];
  const totalMs = (chart.MS || []).reduce((a, b) => a + Number(b || 0), 0);
  const totalTms = (chart.TMS || []).reduce((a, b) => a + Number(b || 0), 0);
  const grandTotal = totalMs + totalTms;
  const wilkerTotals = labels.map((_, idx) =>
    Number((chart.MS && chart.MS[idx]) || 0) + Number((chart.TMS && chart.TMS[idx]) || 0)
  );
  const maxWilkerTotal = Math.max(...wilkerTotals, 1);
  return {
    chart,
    labels,
    totalMs,
    totalTms,
    grandTotal,
    pctMs: grandTotal > 0 ? ((totalMs / grandTotal) * 100).toFixed(1) : '100.0',
    wilkerTotals,
    maxWilkerTotal,
  };
});

const jentikKontainerData = computed(() => {
  const list = siveraDashData.value.charts?.jentik_kontainer || [];
  const total = list.reduce((a, b) => a + Number(b.count || 0), 0);
  const top = list.length > 0 ? `${list[0].name} (${list[0].count})` : '-';
  return { list, total, top };
});

const filteredWilkerDetail = computed(() => {
  const list = siveraDashData.value.wilker_detail || [];
  if (!siveraWilkerSearch.value.trim()) return list;
  const q = siveraWilkerSearch.value.toLowerCase().trim();
  return list.filter((item) => (item.wilker || '').toLowerCase().includes(q));
});

const wilkerDetailTotals = computed(() => {
  const rows = filteredWilkerDetail.value;
  const t = {
    jentik_survei: 0,
    jentik_kontainer_diperiksa: 0,
    jentik_kontainer_positif: 0,
    jentik_rumah_diperiksa: 0,
    jentik_rumah_positif: 0,
    lalat_survei: 0,
    lalat_rata_sum: 0,
    lalat_ms: 0,
    lalat_tms: 0,
    kecoa_survei: 0,
    kecoa_perangkap: 0,
    kecoa_tertangkap: 0,
    tikus_survei: 0,
    tikus_perangkap: 0,
    tikus_tertangkap: 0,
    tikus_trap_sum: 0,
    pinjal_survei: 0,
    pinjal_umum: 0,
    pinjal_khusus: 0,
    anopheles_jentik_survei: 0,
    anopheles_habitat_sum: 0,
    anopheles_nyamuk_survei: 0,
    anopheles_mbr_sum: 0,
  };
  for (const r of rows) {
    t.jentik_survei += (r.jentik_survei || 0);
    t.jentik_kontainer_diperiksa += (r.jentik_kontainer_diperiksa || 0);
    t.jentik_kontainer_positif += (r.jentik_kontainer_positif || 0);
    t.jentik_rumah_diperiksa += (r.jentik_rumah_diperiksa || 0);
    t.jentik_rumah_positif += (r.jentik_rumah_positif || 0);
    t.lalat_survei += (r.lalat_survei || 0);
    t.lalat_rata_sum += (r.lalat_rata_sum || 0);
    t.lalat_ms += (r.lalat_ms || 0);
    t.lalat_tms += (r.lalat_tms || 0);
    t.kecoa_survei += (r.kecoa_survei || 0);
    t.kecoa_perangkap += (r.kecoa_perangkap || 0);
    t.kecoa_tertangkap += (r.kecoa_tertangkap || 0);
    t.tikus_survei += (r.tikus_survei || 0);
    t.tikus_perangkap += (r.tikus_perangkap || 0);
    t.tikus_tertangkap += (r.tikus_tertangkap || 0);
    t.tikus_trap_sum += (r.tikus_success_trap_sum || 0);
    t.pinjal_survei += (r.pinjal_survei || 0);
    t.pinjal_umum += (r.pinjal_umum || 0);
    t.pinjal_khusus += (r.pinjal_khusus || 0);
    t.anopheles_jentik_survei += (r.anopheles_jentik_survei || 0);
    t.anopheles_habitat_sum += (r.anopheles_habitat_sum || 0);
    t.anopheles_nyamuk_survei += (r.anopheles_nyamuk_survei || 0);
    t.anopheles_mbr_sum += (r.anopheles_mbr_sum || 0);
  }
  return {
    ...t,
    abj_avg: t.jentik_rumah_diperiksa > 0
      ? (((t.jentik_rumah_diperiksa - t.jentik_rumah_positif) / t.jentik_rumah_diperiksa) * 100).toFixed(1)
      : (t.jentik_kontainer_diperiksa > 0 ? (((t.jentik_kontainer_diperiksa - t.jentik_kontainer_positif) / t.jentik_kontainer_diperiksa) * 100).toFixed(1) : '100.0'),
    lalat_avg: t.lalat_survei > 0 ? (t.lalat_rata_sum / t.lalat_survei).toFixed(2) : '0.00',
    kecoa_kepadatan: t.kecoa_perangkap > 0 ? (t.kecoa_tertangkap / t.kecoa_perangkap).toFixed(2) : '0.00',
    tikus_trap_avg: t.tikus_survei > 0 ? (t.tikus_trap_sum / t.tikus_survei).toFixed(2) : '0.00',
    anopheles_habitat_avg: t.anopheles_jentik_survei > 0 ? (t.anopheles_habitat_sum / t.anopheles_jentik_survei).toFixed(1) : '0.0',
    anopheles_mbr_avg: t.anopheles_nyamuk_survei > 0 ? (t.anopheles_mbr_sum / t.anopheles_nyamuk_survei).toFixed(2) : '0.00',
  };
});

const detailTabs = [
  { key: 'semua', label: 'Semua Vektor', activeClass: 'bg-teal-600 text-white shadow-xs' },
  { key: 'jentik', label: 'Jentik Aedes', activeClass: 'bg-blue-600 text-white shadow-xs' },
  { key: 'lalat', label: 'Lalat', activeClass: 'bg-amber-600 text-white shadow-xs' },
  { key: 'kecoa', label: 'Kecoa', activeClass: 'bg-rose-600 text-white shadow-xs' },
  { key: 'tikus', label: 'Tikus & Pinjal', activeClass: 'bg-indigo-600 text-white shadow-xs' },
  { key: 'anopheles', label: 'Anopheles', activeClass: 'bg-purple-600 text-white shadow-xs' },
];
</script>

<template>
  <div class="space-y-6 mt-4">
    <!-- 6 KPI Metric Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3.5">
      <!-- 1. Jentik -->
      <div class="p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Jentik Aedes</span>
            <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
              <Droplets class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-black text-slate-900 dark:text-white font-mono">
            {{ siveraDashData.summary.jentik }}
            <span class="text-xs font-normal text-slate-400">survei</span>
          </div>
          <div class="text-[11px] text-slate-500 mt-1">
            Positif: <strong class="text-rose-600 font-mono">{{ siveraDashData.summary.kontainer_positif }}</strong> kontainer
          </div>
        </div>
        <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <span class="text-[10px] font-semibold text-slate-400">ABJ Rata-rata</span>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono" :class="Number(siveraDashData.summary.abj_avg) >= 95 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300'">
            {{ siveraDashData.summary.abj_avg }}%
          </span>
        </div>
      </div>

      <!-- 2. Lalat -->
      <div class="p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Lalat (Fly Index)</span>
            <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
              <Bug class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-black text-slate-900 dark:text-white font-mono">
            {{ Number(siveraDashData.summary.avg_lalat).toFixed(2) }}
          </div>
          <div class="text-[11px] text-slate-500 mt-1">
            Total: <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ siveraDashData.summary.lalat }}</strong> survei
          </div>
        </div>
        <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <span class="text-[10px] font-semibold text-slate-400">Kepadatan MS/TMS</span>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
            {{ siveraDashData.summary.lalat_ms }} MS / {{ siveraDashData.summary.lalat_tms }} TMS
          </span>
        </div>
      </div>

      <!-- 3. Kecoa -->
      <div class="p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kecoa Tertangkap</span>
            <div class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center">
              <Activity class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-black text-slate-900 dark:text-white font-mono">
            {{ siveraDashData.summary.kecoa_tertangkap }}
            <span class="text-xs font-normal text-slate-400">ekor</span>
          </div>
          <div class="text-[11px] text-slate-500 mt-1">
            Dari <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ siveraDashData.summary.kecoa_perangkap }}</strong> perangkap
          </div>
        </div>
        <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <span class="text-[10px] font-semibold text-slate-400">Kepadatan</span>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">
            {{ Number(siveraDashData.summary.avg_kecoa).toFixed(2) }} /trap
          </span>
        </div>
      </div>

      <!-- 4. Tikus -->
      <div class="p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Success Trap Tikus</span>
            <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
              <Rat class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-black text-slate-900 dark:text-white font-mono">
            {{ Number(siveraDashData.summary.avg_success_trap).toFixed(2) }}%
          </div>
          <div class="text-[11px] text-slate-500 mt-1">
            <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ siveraDashData.summary.tikus_tertangkap }}</strong> ekor / <span class="font-mono">{{ siveraDashData.summary.tikus_perangkap }}</span> trap
          </div>
        </div>
        <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <span class="text-[10px] font-semibold text-slate-400">Baku Mutu (&lt;1%)</span>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono" :class="Number(siveraDashData.summary.avg_success_trap) < 1 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300'">
            {{ Number(siveraDashData.summary.avg_success_trap) < 1 ? 'Memenuhi' : 'Waspada' }}
          </span>
        </div>
      </div>

      <!-- 5. Pinjal -->
      <div class="p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pinjal Tikus</span>
            <div class="w-7 h-7 rounded-lg bg-cyan-50 dark:bg-cyan-950/40 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
              <Layers class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-black text-slate-900 dark:text-white font-mono">
            {{ siveraDashData.summary.pinjal_umum }}
          </div>
          <div class="text-[11px] text-slate-500 mt-1">
            Indeks Khusus: <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ siveraDashData.summary.pinjal_khusus }}</strong>
          </div>
        </div>
        <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <span class="text-[10px] font-semibold text-slate-400">Total Survei</span>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-cyan-50 text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300">
            {{ siveraDashData.summary.pinjal }} kegiatan
          </span>
        </div>
      </div>

      <!-- 6. Anopheles -->
      <div class="p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Anopheles</span>
            <div class="w-7 h-7 rounded-lg bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center">
              <Wind class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-black text-slate-900 dark:text-white font-mono">
            {{ Number(siveraDashData.summary.anopheles_jentik || 0) + Number(siveraDashData.summary.anopheles_nyamuk || 0) }}
            <span class="text-xs font-normal text-slate-400">survei</span>
          </div>
          <div class="text-[11px] text-slate-500 mt-1">
            Indeks Habitat: <strong class="text-purple-600 font-mono">{{ siveraDashData.summary.avg_indeks_habitat }}%</strong>
          </div>
        </div>
        <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <span class="text-[10px] font-semibold text-slate-400">Rata MBR</span>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300">
            {{ Number(siveraDashData.summary.avg_mbr).toFixed(2) }}
          </span>
        </div>
      </div>
    </div>

    <!-- 4 Interactive Visualization Cards Grid with Toggle [Grafik] / [Tabel] -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <!-- Card 1 (Kecoa) -->
      <div class="p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex flex-col justify-between">
        <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ stackedSpeciesCards[0].title }}</h3>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border" :class="stackedSpeciesCards[0].badgeClass">{{ stackedSpeciesCards[0].badge }}</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ stackedSpeciesCards[0].subtitle }}</p>
          </div>
          <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 text-xs">
            <button
              type="button"
              @click="siveraCardViews.kecoa = 'chart'"
              class="flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer"
              :class="siveraCardViews.kecoa === 'chart' ? 'bg-white dark:bg-slate-700 text-teal-700 dark:text-teal-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
            >
              <BarChart3 class="w-3.5 h-3.5" />
              <span>Grafik</span>
            </button>
            <button
              type="button"
              @click="siveraCardViews.kecoa = 'table'"
              class="flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer"
              :class="siveraCardViews.kecoa === 'table' ? 'bg-white dark:bg-slate-700 text-teal-700 dark:text-teal-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
            >
              <Table class="w-3.5 h-3.5" />
              <span>Tabel</span>
            </button>
          </div>
        </div>

        <div class="mt-4 min-h-[260px] flex flex-col justify-center">
          <div v-if="siveraCardViews.kecoa === 'chart'" class="space-y-4">
            <div class="flex flex-wrap items-center gap-2.5 text-[11px] pb-2 border-b border-dashed border-slate-100 dark:border-slate-800">
              <div v-for="sp in kecoaChartAgg.species" :key="sp" class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full" :class="kecoaSpeciesColors[sp]?.bar || 'bg-slate-400'"></span>
                <span class="text-slate-600 dark:text-slate-400 font-medium">{{ sp }}:</span>
                <strong class="font-mono text-slate-800 dark:text-slate-200">{{ kecoaChartAgg.speciesTotals[sp] || 0 }}</strong>
              </div>
            </div>

            <div class="space-y-3.5 max-h-[220px] overflow-y-auto pr-1">
              <div v-for="(wilker, idx) in kecoaChartAgg.labels" :key="wilker" class="space-y-1">
                <div class="flex items-center justify-between text-xs">
                  <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[240px]">{{ wilker }}</span>
                  <span class="font-mono text-xs font-bold text-slate-600 dark:text-slate-400">
                    Total: {{ kecoaChartAgg.wilkerTotals[idx] || 0 }} ekor
                  </span>
                </div>
                <div class="h-4 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden flex shadow-inner">
                  <template v-if="(kecoaChartAgg.wilkerTotals[idx] || 0) > 0">
                    <div
                      v-for="sp in kecoaChartAgg.species"
                      :key="sp"
                      v-show="((kecoaChartAgg.chart[sp] && kecoaChartAgg.chart[sp][idx]) || 0) > 0"
                      class="h-full transition-all duration-300 relative group"
                      :class="kecoaSpeciesColors[sp]?.bar || 'bg-slate-400'"
                      :style="{ width: (((kecoaChartAgg.chart[sp][idx] || 0) / kecoaChartAgg.wilkerTotals[idx]) * 100) + '%' }"
                      :title="`${sp}: ${kecoaChartAgg.chart[sp][idx]} ekor`"
                    ></div>
                  </template>
                  <div v-else class="h-full w-full flex items-center justify-center text-[10px] text-slate-400 italic">0 ekor</div>
                </div>
              </div>
              <div v-if="kecoaChartAgg.labels.length === 0" class="text-center py-8 text-xs text-slate-400">
                Belum ada data wilayah kerja surveilans kecoa.
              </div>
            </div>
          </div>

          <div v-else>
            <!-- Mobile Cards -->
            <div class="md:hidden space-y-2 max-h-[260px] overflow-y-auto">
              <div v-for="(wilker, idx) in kecoaChartAgg.labels" :key="wilker" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50 space-y-1.5 text-xs">
                <div class="flex items-center justify-between">
                  <span class="font-bold text-slate-800 dark:text-slate-200">{{ wilker }}</span>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300">
                    Total: {{ kecoaChartAgg.wilkerTotals[idx] || 0 }}
                  </span>
                </div>
                <div class="flex flex-wrap gap-1.5 text-[11px]">
                  <span v-for="sp in kecoaChartAgg.species" :key="sp" class="px-2 py-0.5 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono">
                    {{ sp }}: <strong>{{ (kecoaChartAgg.chart[sp] && kecoaChartAgg.chart[sp][idx]) || 0 }}</strong>
                  </span>
                </div>
              </div>
              <div v-if="kecoaChartAgg.labels.length === 0" class="py-6 text-center text-xs text-slate-400">Belum ada data surveilans kecoa</div>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-y-auto max-h-[260px] rounded-xl border border-slate-200 dark:border-slate-800">
              <table class="w-full text-xs text-center border-collapse">
                <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 z-10 border-b border-slate-200 dark:border-slate-700">
                  <tr>
                    <th class="py-2 px-3 text-left font-bold">Wilayah Kerja</th>
                    <th v-for="sp in kecoaChartAgg.species" :key="sp" class="py-2 px-2 font-bold">{{ sp }}</th>
                    <th class="py-2 px-2.5 font-bold">Total</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                  <tr v-for="(wilker, idx) in kecoaChartAgg.labels" :key="wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                    <td class="py-2 px-3 text-left font-semibold text-slate-800 dark:text-slate-200">{{ wilker }}</td>
                    <td v-for="sp in kecoaChartAgg.species" :key="sp" class="py-2 px-2 font-mono">
                      <span :class="((kecoaChartAgg.chart[sp] && kecoaChartAgg.chart[sp][idx]) || 0) > 0 ? 'font-bold text-slate-900 dark:text-white' : 'text-slate-400'">
                        {{ (kecoaChartAgg.chart[sp] && kecoaChartAgg.chart[sp][idx]) || 0 }}
                      </span>
                    </td>
                    <td class="py-2 px-2.5 font-mono font-bold">
                      <span class="px-2 py-0.5 rounded-full text-[10px] bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300">
                        {{ kecoaChartAgg.wilkerTotals[idx] || 0 }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="kecoaChartAgg.labels.length === 0">
                    <td :colspan="kecoaChartAgg.species.length + 2" class="py-6 text-center text-slate-400">Belum ada data surveilans kecoa</td>
                  </tr>
                </tbody>
                <tfoot class="sticky bottom-0 bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
                  <tr>
                    <td class="py-2 px-3 text-left">Total Keseluruhan</td>
                    <td v-for="sp in kecoaChartAgg.species" :key="sp" class="py-2 px-2 font-mono">{{ kecoaChartAgg.speciesTotals[sp] || 0 }}</td>
                    <td class="py-2 px-2.5 font-mono">
                      <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-900 text-white dark:bg-white dark:text-slate-900">{{ kecoaChartAgg.grandTotal }}</span>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 2: Status Kepadatan Lalat -->
      <div class="p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex flex-col justify-between">
        <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-bold text-slate-900 dark:text-white">Status Kepadatan Lalat</h3>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">Lalat</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Perbandingan kategori MS (&lt; 2) dan TMS (&ge; 2) berdasarkan standar fly grill.</p>
          </div>
          <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 text-xs">
            <button
              type="button"
              @click="siveraCardViews.lalat = 'chart'"
              class="flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer"
              :class="siveraCardViews.lalat === 'chart' ? 'bg-white dark:bg-slate-700 text-teal-700 dark:text-teal-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
            >
              <BarChart3 class="w-3.5 h-3.5" />
              <span>Grafik</span>
            </button>
            <button
              type="button"
              @click="siveraCardViews.lalat = 'table'"
              class="flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer"
              :class="siveraCardViews.lalat === 'table' ? 'bg-white dark:bg-slate-700 text-teal-700 dark:text-teal-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
            >
              <Table class="w-3.5 h-3.5" />
              <span>Tabel</span>
            </button>
          </div>
        </div>

        <div class="mt-4 min-h-[260px] flex flex-col justify-center">
          <div v-if="siveraCardViews.lalat === 'chart'" class="space-y-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs pb-2 border-b border-dashed border-slate-100 dark:border-slate-800">
              <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800">
                <span class="text-[10px] text-slate-400 font-semibold block">Total Survei</span>
                <strong class="font-mono text-slate-800 dark:text-slate-200 text-sm">{{ lalatChartAgg.grandTotal }}</strong>
              </div>
              <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300">
                <span class="text-[10px] font-semibold block">MS (&lt; 2)</span>
                <strong class="font-mono text-sm">{{ lalatChartAgg.totalMs }}</strong>
              </div>
              <div class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-300">
                <span class="text-[10px] font-semibold block">TMS (&ge; 2)</span>
                <strong class="font-mono text-sm">{{ lalatChartAgg.totalTms }}</strong>
              </div>
              <div class="p-2 rounded-xl bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-300">
                <span class="text-[10px] font-semibold block">% Kepatuhan</span>
                <strong class="font-mono text-sm">{{ lalatChartAgg.pctMs }}%</strong>
              </div>
            </div>

            <div class="space-y-3.5 max-h-[190px] overflow-y-auto pr-1">
              <div v-for="(wilker, idx) in lalatChartAgg.labels" :key="wilker" class="space-y-1">
                <div class="flex items-center justify-between text-xs">
                  <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[240px]">{{ wilker }}</span>
                  <div class="flex items-center gap-2">
                    <span class="text-[11px] text-emerald-600 font-bold font-mono">{{ lalatChartAgg.chart.MS[idx] || 0 }} MS</span>
                    <span class="text-slate-300">&bull;</span>
                    <span class="text-[11px] text-rose-600 font-bold font-mono">{{ lalatChartAgg.chart.TMS[idx] || 0 }} TMS</span>
                  </div>
                </div>
                <div class="h-4 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden flex shadow-inner">
                  <template v-if="(lalatChartAgg.wilkerTotals[idx] || 0) > 0">
                    <div
                      class="h-full bg-emerald-500 transition-all duration-300"
                      :style="{ width: (((lalatChartAgg.chart.MS[idx] || 0) / lalatChartAgg.wilkerTotals[idx]) * 100) + '%' }"
                      :title="`MS: ${lalatChartAgg.chart.MS[idx] || 0}`"
                    ></div>
                    <div
                      class="h-full bg-rose-500 transition-all duration-300"
                      :style="{ width: (((lalatChartAgg.chart.TMS[idx] || 0) / lalatChartAgg.wilkerTotals[idx]) * 100) + '%' }"
                      :title="`TMS: ${lalatChartAgg.chart.TMS[idx] || 0}`"
                    ></div>
                  </template>
                  <div v-else class="h-full w-full flex items-center justify-center text-[10px] text-slate-400 italic">0 survei</div>
                </div>
              </div>
              <div v-if="lalatChartAgg.labels.length === 0" class="text-center py-8 text-xs text-slate-400">
                Belum ada data wilayah kerja surveilans lalat.
              </div>
            </div>
          </div>

          <div v-else>
            <!-- Mobile Cards -->
            <div class="md:hidden space-y-2 max-h-[260px] overflow-y-auto">
              <div v-for="(wilker, idx) in lalatChartAgg.labels" :key="wilker" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50 space-y-1.5 text-xs">
                <div class="flex items-center justify-between gap-2">
                  <span class="font-bold text-slate-800 dark:text-slate-200">{{ wilker }}</span>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono" :class="(lalatChartAgg.wilkerTotals[idx] || 0) > 0 && (((lalatChartAgg.chart.MS[idx] || 0) / lalatChartAgg.wilkerTotals[idx]) * 100) >= 80 ? 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300'">
                    {{ (lalatChartAgg.wilkerTotals[idx] || 0) > 0 ? (((lalatChartAgg.chart.MS[idx] || 0) / lalatChartAgg.wilkerTotals[idx]) * 100).toFixed(1) : 100 }}% MS
                  </span>
                </div>
                <div class="flex items-center justify-between text-[11px] font-mono">
                  <span class="text-emerald-600 font-bold">MS: {{ lalatChartAgg.chart.MS[idx] || 0 }}</span>
                  <span class="text-rose-600 font-bold">TMS: {{ lalatChartAgg.chart.TMS[idx] || 0 }}</span>
                  <span class="text-slate-600 dark:text-slate-300 font-bold">Total: {{ lalatChartAgg.wilkerTotals[idx] || 0 }}</span>
                </div>
              </div>
              <div v-if="lalatChartAgg.labels.length === 0" class="py-6 text-center text-xs text-slate-400">Belum ada data surveilans lalat</div>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-y-auto max-h-[260px] rounded-xl border border-slate-200 dark:border-slate-800">
              <table class="w-full text-xs text-center border-collapse">
                <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 z-10 border-b border-slate-200 dark:border-slate-700">
                  <tr>
                    <th class="py-2 px-3 text-left font-bold">Wilayah Kerja</th>
                    <th class="py-2 px-2.5 font-bold text-emerald-700 dark:text-emerald-400">MS (&lt; 2)</th>
                    <th class="py-2 px-2.5 font-bold text-rose-700 dark:text-rose-400">TMS (&ge; 2)</th>
                    <th class="py-2 px-2.5 font-bold">Total Survei</th>
                    <th class="py-2 px-2.5 font-bold">Persentase MS</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                  <tr v-for="(wilker, idx) in lalatChartAgg.labels" :key="wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                    <td class="py-2 px-3 text-left font-semibold text-slate-800 dark:text-slate-200">{{ wilker }}</td>
                    <td class="py-2 px-2.5 font-mono">
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300">
                        {{ lalatChartAgg.chart.MS[idx] || 0 }}
                      </span>
                    </td>
                    <td class="py-2 px-2.5 font-mono">
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300">
                        {{ lalatChartAgg.chart.TMS[idx] || 0 }}
                      </span>
                    </td>
                    <td class="py-2 px-2.5 font-mono font-bold text-slate-900 dark:text-white">{{ lalatChartAgg.wilkerTotals[idx] || 0 }}</td>
                    <td class="py-2 px-2.5 font-mono font-bold">
                      <span
                        class="px-2 py-0.5 rounded-full text-[10px]"
                        :class="(lalatChartAgg.wilkerTotals[idx] || 0) > 0 && (((lalatChartAgg.chart.MS[idx] || 0) / lalatChartAgg.wilkerTotals[idx]) * 100) >= 80 ? 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300'"
                      >
                        {{ (lalatChartAgg.wilkerTotals[idx] || 0) > 0 ? (((lalatChartAgg.chart.MS[idx] || 0) / lalatChartAgg.wilkerTotals[idx]) * 100).toFixed(1) : 100 }}%
                      </span>
                    </td>
                  </tr>
                  <tr v-if="lalatChartAgg.labels.length === 0">
                    <td colspan="5" class="py-6 text-center text-slate-400">Belum ada data surveilans lalat</td>
                  </tr>
                </tbody>
                <tfoot class="sticky bottom-0 bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
                  <tr>
                    <td class="py-2 px-3 text-left">Total Keseluruhan</td>
                    <td class="py-2 px-2.5 font-mono text-emerald-700 dark:text-emerald-400">{{ lalatChartAgg.totalMs }}</td>
                    <td class="py-2 px-2.5 font-mono text-rose-700 dark:text-rose-400">{{ lalatChartAgg.totalTms }}</td>
                    <td class="py-2 px-2.5 font-mono">{{ lalatChartAgg.grandTotal }}</td>
                    <td class="py-2 px-2.5 font-mono">
                      <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-900 text-white dark:bg-white dark:text-slate-900">{{ lalatChartAgg.pctMs }}%</span>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 3: Kontainer Positif Jentik -->
      <div class="p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex flex-col justify-between">
        <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-bold text-slate-900 dark:text-white">Kontainer Positif Jentik</h3>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">Jentik</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Jenis kontainer penampungan air yang ditemukan positif jentik Aedes sp.</p>
          </div>
          <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 text-xs">
            <button
              type="button"
              @click="siveraCardViews.jentik = 'chart'"
              class="flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer"
              :class="siveraCardViews.jentik === 'chart' ? 'bg-white dark:bg-slate-700 text-teal-700 dark:text-teal-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
            >
              <BarChart3 class="w-3.5 h-3.5" />
              <span>Grafik</span>
            </button>
            <button
              type="button"
              @click="siveraCardViews.jentik = 'table'"
              class="flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer"
              :class="siveraCardViews.jentik === 'table' ? 'bg-white dark:bg-slate-700 text-teal-700 dark:text-teal-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
            >
              <Table class="w-3.5 h-3.5" />
              <span>Tabel</span>
            </button>
          </div>
        </div>

        <div class="mt-4 min-h-[260px] flex flex-col justify-center">
          <div v-if="siveraCardViews.jentik === 'chart'" class="space-y-3.5">
            <div class="flex items-center justify-between text-xs pb-2 border-b border-dashed border-slate-100 dark:border-slate-800">
              <span class="text-slate-500">Total Temuan Positif: <strong class="text-slate-900 dark:text-white font-mono">{{ jentikKontainerData.total }}</strong></span>
              <span class="text-xs font-semibold text-teal-600 dark:text-teal-400">{{ jentikKontainerData.top }}</span>
            </div>

            <div class="space-y-3 max-h-[210px] overflow-y-auto pr-1">
              <div v-for="(item, idx) in jentikKontainerData.list" :key="item.name" class="space-y-1">
                <div class="flex items-center justify-between text-xs">
                  <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded-full bg-teal-100 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 text-[10px] font-bold flex items-center justify-center">{{ idx + 1 }}</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ item.name }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="font-mono text-slate-900 dark:text-white font-bold">{{ item.count }} temuan</span>
                    <span class="text-[11px] font-mono text-slate-400">({{ item.pct }}%)</span>
                  </div>
                </div>
                <div class="h-3 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden shadow-inner">
                  <div class="h-full bg-gradient-to-r from-teal-500 to-emerald-400 rounded-full transition-all duration-300" :style="{ width: item.pct + '%' }"></div>
                </div>
              </div>
              <div v-if="jentikKontainerData.list.length === 0" class="text-center py-10 text-xs text-slate-400">
                Belum ada temuan kontainer positif jentik tercatat.
              </div>
            </div>
          </div>

          <div v-else>
            <!-- Mobile Cards -->
            <div class="md:hidden space-y-2 max-h-[260px] overflow-y-auto">
              <div v-for="(item, idx) in jentikKontainerData.list" :key="item.name" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2 min-w-0">
                  <span class="text-slate-400 font-mono">#{{ idx + 1 }}</span>
                  <span class="font-semibold text-slate-800 dark:text-slate-200 truncate">{{ item.name }}</span>
                </div>
                <div class="flex items-center gap-2 shrink-0 font-mono">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300">{{ item.count }}</span>
                  <span class="text-[11px] font-semibold text-slate-600 dark:text-slate-300">{{ item.pct }}%</span>
                </div>
              </div>
              <div v-if="jentikKontainerData.list.length === 0" class="py-6 text-center text-xs text-slate-400">Belum ada temuan kontainer positif</div>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-y-auto max-h-[260px] rounded-xl border border-slate-200 dark:border-slate-800">
              <table class="w-full text-xs text-center border-collapse">
                <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 z-10 border-b border-slate-200 dark:border-slate-700">
                  <tr>
                    <th class="py-2 px-2.5 w-12 font-bold">No</th>
                    <th class="py-2 px-3 text-left font-bold">Jenis Kontainer</th>
                    <th class="py-2 px-3 font-bold">Jumlah Temuan</th>
                    <th class="py-2 px-3 font-bold">Persentase (%)</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                  <tr v-for="(item, idx) in jentikKontainerData.list" :key="item.name" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                    <td class="py-2 px-2.5 text-slate-400 font-mono">{{ idx + 1 }}</td>
                    <td class="py-2 px-3 text-left font-semibold text-slate-800 dark:text-slate-200">{{ item.name }}</td>
                    <td class="py-2 px-3 font-mono">
                      <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300">{{ item.count }}</span>
                    </td>
                    <td class="py-2 px-3 font-mono font-semibold text-slate-700 dark:text-slate-300">{{ item.pct }}%</td>
                  </tr>
                  <tr v-if="jentikKontainerData.list.length === 0">
                    <td colspan="4" class="py-6 text-center text-slate-400">Belum ada temuan kontainer positif</td>
                  </tr>
                </tbody>
                <tfoot class="sticky bottom-0 bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
                  <tr>
                    <td colspan="2" class="py-2 px-3 text-left">Total Temuan Positif</td>
                    <td class="py-2 px-3 font-mono text-blue-700 dark:text-blue-400">{{ jentikKontainerData.total }}</td>
                    <td class="py-2 px-3 font-mono">100%</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 4: Sebaran Spesies Tikus -->
      <div class="p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex flex-col justify-between">
        <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ stackedSpeciesCards[1].title }}</h3>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border" :class="stackedSpeciesCards[1].badgeClass">{{ stackedSpeciesCards[1].badge }}</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ stackedSpeciesCards[1].subtitle }}</p>
          </div>
          <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 text-xs">
            <button
              type="button"
              @click="siveraCardViews.tikus = 'chart'"
              class="flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer"
              :class="siveraCardViews.tikus === 'chart' ? 'bg-white dark:bg-slate-700 text-teal-700 dark:text-teal-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
            >
              <BarChart3 class="w-3.5 h-3.5" />
              <span>Grafik</span>
            </button>
            <button
              type="button"
              @click="siveraCardViews.tikus = 'table'"
              class="flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer"
              :class="siveraCardViews.tikus === 'table' ? 'bg-white dark:bg-slate-700 text-teal-700 dark:text-teal-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
            >
              <Table class="w-3.5 h-3.5" />
              <span>Tabel</span>
            </button>
          </div>
        </div>

        <div class="mt-4 min-h-[260px] flex flex-col justify-center">
          <div v-if="siveraCardViews.tikus === 'chart'" class="space-y-4">
            <div class="flex flex-wrap items-center gap-2.5 text-[11px] pb-2 border-b border-dashed border-slate-100 dark:border-slate-800">
              <div v-for="sp in tikusChartAgg.species" :key="sp" class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full" :class="tikusSpeciesColors[sp]?.bar || 'bg-slate-400'"></span>
                <span class="text-slate-600 dark:text-slate-400 font-medium">{{ sp }}:</span>
                <strong class="font-mono text-slate-800 dark:text-slate-200">{{ tikusChartAgg.speciesTotals[sp] || 0 }}</strong>
              </div>
            </div>

            <div class="space-y-3.5 max-h-[220px] overflow-y-auto pr-1">
              <div v-for="(wilker, idx) in tikusChartAgg.labels" :key="wilker" class="space-y-1">
                <div class="flex items-center justify-between text-xs">
                  <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[240px]">{{ wilker }}</span>
                  <span class="font-mono text-xs font-bold text-slate-600 dark:text-slate-400">
                    Total: {{ tikusChartAgg.wilkerTotals[idx] || 0 }} ekor
                  </span>
                </div>
                <div class="h-4 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden flex shadow-inner">
                  <template v-if="(tikusChartAgg.wilkerTotals[idx] || 0) > 0">
                    <div
                      v-for="sp in tikusChartAgg.species"
                      :key="sp"
                      v-show="((tikusChartAgg.chart[sp] && tikusChartAgg.chart[sp][idx]) || 0) > 0"
                      class="h-full transition-all duration-300 relative group"
                      :class="tikusSpeciesColors[sp]?.bar || 'bg-slate-400'"
                      :style="{ width: (((tikusChartAgg.chart[sp][idx] || 0) / tikusChartAgg.wilkerTotals[idx]) * 100) + '%' }"
                      :title="`${sp}: ${tikusChartAgg.chart[sp][idx]} ekor`"
                    ></div>
                  </template>
                  <div v-else class="h-full w-full flex items-center justify-center text-[10px] text-slate-400 italic">0 ekor</div>
                </div>
              </div>
              <div v-if="tikusChartAgg.labels.length === 0" class="text-center py-8 text-xs text-slate-400">
                Belum ada data wilayah kerja surveilans tikus.
              </div>
            </div>
          </div>

          <div v-else>
            <!-- Mobile Cards -->
            <div class="md:hidden space-y-2 max-h-[260px] overflow-y-auto">
              <div v-for="(wilker, idx) in tikusChartAgg.labels" :key="wilker" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50 space-y-1.5 text-xs">
                <div class="flex items-center justify-between">
                  <span class="font-bold text-slate-800 dark:text-slate-200">{{ wilker }}</span>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300">
                    Total: {{ tikusChartAgg.wilkerTotals[idx] || 0 }}
                  </span>
                </div>
                <div class="flex flex-wrap gap-1.5 text-[11px]">
                  <span v-for="sp in tikusChartAgg.species" :key="sp" class="px-2 py-0.5 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono">
                    {{ sp }}: <strong>{{ (tikusChartAgg.chart[sp] && tikusChartAgg.chart[sp][idx]) || 0 }}</strong>
                  </span>
                </div>
              </div>
              <div v-if="tikusChartAgg.labels.length === 0" class="py-6 text-center text-xs text-slate-400">Belum ada data surveilans tikus</div>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-y-auto max-h-[260px] rounded-xl border border-slate-200 dark:border-slate-800">
              <table class="w-full text-xs text-center border-collapse">
                <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 z-10 border-b border-slate-200 dark:border-slate-700">
                  <tr>
                    <th class="py-2 px-3 text-left font-bold">Wilayah Kerja</th>
                    <th v-for="sp in tikusChartAgg.species" :key="sp" class="py-2 px-2 font-bold">{{ sp }}</th>
                    <th class="py-2 px-2.5 font-bold">Total</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                  <tr v-for="(wilker, idx) in tikusChartAgg.labels" :key="wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                    <td class="py-2 px-3 text-left font-semibold text-slate-800 dark:text-slate-200">{{ wilker }}</td>
                    <td v-for="sp in tikusChartAgg.species" :key="sp" class="py-2 px-2 font-mono">
                      <span :class="((tikusChartAgg.chart[sp] && tikusChartAgg.chart[sp][idx]) || 0) > 0 ? 'font-bold text-slate-900 dark:text-white' : 'text-slate-400'">
                        {{ (tikusChartAgg.chart[sp] && tikusChartAgg.chart[sp][idx]) || 0 }}
                      </span>
                    </td>
                    <td class="py-2 px-2.5 font-mono font-bold">
                      <span class="px-2 py-0.5 rounded-full text-[10px] bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300">
                        {{ tikusChartAgg.wilkerTotals[idx] || 0 }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="tikusChartAgg.labels.length === 0">
                    <td :colspan="tikusChartAgg.species.length + 2" class="py-6 text-center text-slate-400">Belum ada data surveilans tikus</td>
                  </tr>
                </tbody>
                <tfoot class="sticky bottom-0 bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
                  <tr>
                    <td class="py-2 px-3 text-left">Total Keseluruhan</td>
                    <td v-for="sp in tikusChartAgg.species" :key="sp" class="py-2 px-2 font-mono">{{ tikusChartAgg.speciesTotals[sp] || 0 }}</td>
                    <td class="py-2 px-2.5 font-mono">
                      <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-900 text-white dark:bg-white dark:text-slate-900">{{ tikusChartAgg.grandTotal }}</span>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Master Detail Matrix: Rekapitulasi Rinci Data Surveilans per Wilayah Kerja -->
    <div class="rounded-3xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
      <div class="p-5 bg-gradient-to-r from-slate-50 via-slate-50/70 to-white dark:from-slate-800/60 dark:to-slate-900 border-b border-slate-200 dark:border-slate-800">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div>
            <h3 class="text-sm md:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
              <Table class="w-4 h-4 text-teal-600 dark:text-teal-400" />
              <span>Rekapitulasi Rinci Data Surveilans per Wilayah Kerja</span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Tabel detail hasil surveilans lengkap untuk seluruh vektor dan reservoar penyakit.
            </p>
          </div>

          <div class="flex items-center gap-2.5">
            <div class="relative min-w-[200px] w-full sm:w-auto">
              <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
              <input
                v-model="siveraWilkerSearch"
                type="text"
                placeholder="Cari wilayah kerja..."
                class="w-full pl-8 pr-7 py-1.5 rounded-xl text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-teal-500"
              />
              <button
                v-if="siveraWilkerSearch"
                type="button"
                @click="siveraWilkerSearch = ''"
                aria-label="Hapus pencarian wilayah kerja"
                title="Hapus pencarian"
                class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
              >
                <X class="w-3 h-3" />
              </button>
            </div>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-800">
          <button
            v-for="tab in detailTabs"
            :key="tab.key"
            type="button"
            @click="activeSiveraDetailTab = tab.key"
            class="px-3 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
            :class="activeSiveraDetailTab === tab.key ? tab.activeClass : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700'"
          >
            <Layers v-if="tab.key === 'semua'" class="w-3.5 h-3.5" />
            <Droplets v-else-if="tab.key === 'jentik'" class="w-3.5 h-3.5" />
            <Bug v-else-if="tab.key === 'lalat'" class="w-3.5 h-3.5" />
            <Activity v-else-if="tab.key === 'kecoa'" class="w-3.5 h-3.5" />
            <Rat v-else-if="tab.key === 'tikus'" class="w-3.5 h-3.5" />
            <Wind v-else class="w-3.5 h-3.5" />
            <span>{{ tab.label }}</span>
          </button>
        </div>
      </div>

      <!-- Mobile Cards View -->
      <div class="md:hidden p-3 space-y-2.5">
        <div
          v-for="(row, idx) in filteredWilkerDetail"
          :key="`mob-${activeSiveraDetailTab}-${row.wilker}`"
          class="p-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 space-y-2 text-xs"
        >
          <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
              <span class="text-[10px] font-mono text-slate-400 mr-1.5">#{{ idx + 1 }}</span>
              <span class="font-bold text-slate-900 dark:text-white">{{ row.wilker }}</span>
            </div>
            <button
              v-if="activeSiveraDetailTab !== 'semua'"
              type="button"
              @click="emit('switch-vektor', activeSiveraDetailTab === 'jentik' ? 'Jentik Aedes' : activeSiveraDetailTab === 'lalat' ? 'Lalat' : activeSiveraDetailTab === 'kecoa' ? 'Kecoa' : activeSiveraDetailTab === 'tikus' ? 'Tikus' : 'Nyamuk Anopheles')"
              class="text-teal-600 dark:text-teal-400 font-semibold text-[11px] hover:underline shrink-0 cursor-pointer"
            >
              Lihat Data
            </button>
          </div>

          <div v-if="activeSiveraDetailTab === 'semua'" class="grid grid-cols-2 gap-1.5 text-[11px]">
            <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-700">
              <span class="text-slate-400 block">Jentik ABJ</span>
              <strong class="font-mono" :class="row.abj >= 95 ? 'text-emerald-600' : 'text-rose-600'">{{ row.abj }}%</strong>
              <span class="text-slate-400 ml-1">({{ row.jentik_survei }} srv)</span>
            </div>
            <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-700">
              <span class="text-slate-400 block">Lalat Fly Index</span>
              <strong class="font-mono text-slate-800 dark:text-slate-200">{{ Number(row.lalat_avg).toFixed(2) }}</strong>
              <span class="text-slate-400 ml-1">({{ row.lalat_survei }} srv)</span>
            </div>
            <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-700">
              <span class="text-slate-400 block">Kecoa / Tikus</span>
              <strong class="font-mono text-slate-800 dark:text-slate-200">{{ row.kecoa_tertangkap }} / {{ row.tikus_tertangkap }} ekor</strong>
            </div>
            <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-700">
              <span class="text-slate-400 block">Anopheles MBR</span>
              <strong class="font-mono text-slate-800 dark:text-slate-200">{{ Number(row.anopheles_mbr_avg).toFixed(2) }}</strong>
              <span class="text-slate-400 ml-1">(Hab {{ row.anopheles_habitat_avg }}%)</span>
            </div>
          </div>

          <div v-else-if="activeSiveraDetailTab === 'jentik'" class="grid grid-cols-3 gap-1.5 text-[11px] font-mono">
            <div><span class="text-slate-400 block font-sans">Survei</span><strong>{{ row.jentik_survei }}</strong></div>
            <div><span class="text-slate-400 block font-sans">Kont (+)</span><strong class="text-rose-600">{{ row.jentik_kontainer_positif }}</strong></div>
            <div><span class="text-slate-400 block font-sans">ABJ</span><strong :class="row.abj >= 95 ? 'text-emerald-600' : 'text-rose-600'">{{ row.abj }}%</strong></div>
          </div>

          <div v-else-if="activeSiveraDetailTab === 'lalat'" class="grid grid-cols-3 gap-1.5 text-[11px] font-mono">
            <div><span class="text-slate-400 block font-sans">Fly Idx</span><strong>{{ Number(row.lalat_avg).toFixed(2) }}</strong></div>
            <div><span class="text-slate-400 block font-sans">MS</span><strong class="text-emerald-600">{{ row.lalat_ms }}</strong></div>
            <div><span class="text-slate-400 block font-sans">TMS</span><strong class="text-rose-600">{{ row.lalat_tms }}</strong></div>
          </div>

          <div v-else-if="activeSiveraDetailTab === 'kecoa'" class="grid grid-cols-3 gap-1.5 text-[11px] font-mono">
            <div><span class="text-slate-400 block font-sans">Perangkap</span><strong>{{ row.kecoa_perangkap }}</strong></div>
            <div><span class="text-slate-400 block font-sans">Ekor</span><strong class="text-rose-600">{{ row.kecoa_tertangkap }}</strong></div>
            <div><span class="text-slate-400 block font-sans">Indeks</span><strong>{{ Number(row.kecoa_kepadatan).toFixed(2) }}</strong></div>
          </div>

          <div v-else-if="activeSiveraDetailTab === 'tikus'" class="grid grid-cols-3 gap-1.5 text-[11px] font-mono">
            <div><span class="text-slate-400 block font-sans">Tikus</span><strong class="text-indigo-600">{{ row.tikus_tertangkap }} / {{ row.tikus_perangkap }}</strong></div>
            <div><span class="text-slate-400 block font-sans">Trap %</span><strong>{{ Number(row.tikus_success_trap_avg).toFixed(2) }}%</strong></div>
            <div><span class="text-slate-400 block font-sans">Pinjal U/K</span><strong>{{ row.pinjal_umum }}/{{ row.pinjal_khusus }}</strong></div>
          </div>

          <div v-else-if="activeSiveraDetailTab === 'anopheles'" class="grid grid-cols-2 gap-1.5 text-[11px] font-mono">
            <div><span class="text-slate-400 block font-sans">Habitat Jentik</span><strong>{{ row.anopheles_habitat_avg }}%</strong> ({{ row.anopheles_jentik_survei }} srv)</div>
            <div><span class="text-slate-400 block font-sans">MBR Nyamuk</span><strong>{{ Number(row.anopheles_mbr_avg).toFixed(2) }}</strong> ({{ row.anopheles_nyamuk_survei }} srv)</div>
          </div>
        </div>

        <div v-if="filteredWilkerDetail.length === 0" class="py-8 text-center text-xs text-slate-400">
          Tidak ada data wilayah kerja yang sesuai.
        </div>
      </div>

      <!-- Desktop Table Body -->
      <div class="hidden md:block overflow-x-auto">
        <!-- 1. Tab Semua Vektor -->
        <table v-if="activeSiveraDetailTab === 'semua'" class="w-full text-xs text-center border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold">
              <th rowspan="2" class="py-2.5 px-2.5 w-12 border-r border-slate-200 dark:border-slate-700">No</th>
              <th rowspan="2" class="py-2.5 px-3.5 text-left min-w-[200px] border-r border-slate-200 dark:border-slate-700">Wilayah Kerja</th>
              <th colspan="3" class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700 bg-blue-50/50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400">Jentik Aedes</th>
              <th colspan="3" class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700 bg-amber-50/50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400">Lalat</th>
              <th colspan="2" class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700 bg-rose-50/50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400">Kecoa</th>
              <th colspan="3" class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700 bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400">Tikus</th>
              <th colspan="2" class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700 bg-cyan-50/50 dark:bg-cyan-950/20 text-cyan-700 dark:text-cyan-400">Pinjal</th>
              <th colspan="2" class="py-1.5 px-2 bg-purple-50/50 dark:bg-purple-950/20 text-purple-700 dark:text-purple-400">Anopheles</th>
            </tr>
            <tr class="bg-slate-100/70 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 text-[10px] font-semibold">
              <th class="py-1.5 px-2">Survei</th>
              <th class="py-1.5 px-2">Kontainer (+)</th>
              <th class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700">ABJ (%)</th>
              <th class="py-1.5 px-2">Survei</th>
              <th class="py-1.5 px-2">Rata Fly Idx</th>
              <th class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700">Status</th>
              <th class="py-1.5 px-2">Survei</th>
              <th class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700">Tertangkap</th>
              <th class="py-1.5 px-2">Perangkap</th>
              <th class="py-1.5 px-2">Tertangkap</th>
              <th class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700">Trap (%)</th>
              <th class="py-1.5 px-2">Koleksi</th>
              <th class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700">Pinjal</th>
              <th class="py-1.5 px-2">Habitat (%)</th>
              <th class="py-1.5 px-2">MBR</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-for="(row, idx) in filteredWilkerDetail" :key="row.wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
              <td class="py-2.5 px-2.5 text-slate-400 font-mono border-r border-slate-100 dark:border-slate-800">{{ idx + 1 }}</td>
              <td class="py-2.5 px-3.5 text-left font-semibold text-slate-900 dark:text-slate-100 border-r border-slate-100 dark:border-slate-800">{{ row.wilker }}</td>
              <td class="py-2.5 px-2 font-mono">{{ row.jentik_survei }}</td>
              <td class="py-2.5 px-2 font-mono">
                <span class="px-1.5 py-0.5 rounded text-[10px]" :class="row.jentik_kontainer_positif > 0 ? 'bg-rose-50 text-rose-700 font-bold dark:bg-rose-950/40 dark:text-rose-300' : 'text-slate-400'">{{ row.jentik_kontainer_positif }}</span>
              </td>
              <td class="py-2.5 px-2 font-mono font-bold border-r border-slate-100 dark:border-slate-800" :class="row.abj >= 95 ? 'text-emerald-600' : 'text-rose-600'">{{ row.abj }}%</td>
              <td class="py-2.5 px-2 font-mono">{{ row.lalat_survei }}</td>
              <td class="py-2.5 px-2 font-mono">{{ Number(row.lalat_avg).toFixed(2) }}</td>
              <td class="py-2.5 px-2 border-r border-slate-100 dark:border-slate-800">
                <span v-if="row.lalat_survei > 0" class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="row.lalat_avg < 2 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300'">
                  {{ row.lalat_avg < 2 ? 'MS' : 'TMS' }}
                </span>
                <span v-else class="text-slate-400">-</span>
              </td>
              <td class="py-2.5 px-2 font-mono">{{ row.kecoa_survei }}</td>
              <td class="py-2.5 px-2 font-mono border-r border-slate-100 dark:border-slate-800">
                <span class="px-1.5 py-0.5 rounded text-[10px]" :class="row.kecoa_tertangkap > 0 ? 'bg-rose-50 text-rose-700 font-bold dark:bg-rose-950/40 dark:text-rose-300' : 'text-slate-400'">{{ row.kecoa_tertangkap }}</span>
              </td>
              <td class="py-2.5 px-2 font-mono">{{ row.tikus_perangkap }}</td>
              <td class="py-2.5 px-2 font-mono">
                <span class="px-1.5 py-0.5 rounded text-[10px]" :class="row.tikus_tertangkap > 0 ? 'bg-indigo-50 text-indigo-700 font-bold dark:bg-indigo-950/40 dark:text-indigo-300' : 'text-slate-400'">{{ row.tikus_tertangkap }}</span>
              </td>
              <td class="py-2.5 px-2 font-mono font-bold border-r border-slate-100 dark:border-slate-800">{{ Number(row.tikus_success_trap_avg).toFixed(2) }}%</td>
              <td class="py-2.5 px-2 font-mono">{{ row.pinjal_survei }}</td>
              <td class="py-2.5 px-2 font-mono border-r border-slate-100 dark:border-slate-800">{{ row.pinjal_umum + row.pinjal_khusus }}</td>
              <td class="py-2.5 px-2 font-mono">{{ row.anopheles_habitat_avg }}%</td>
              <td class="py-2.5 px-2 font-mono">{{ Number(row.anopheles_mbr_avg).toFixed(2) }}</td>
            </tr>
            <tr v-if="filteredWilkerDetail.length === 0">
              <td colspan="17" class="py-8 text-center text-slate-400">Tidak ada data wilayah kerja yang sesuai dengan pencarian.</td>
            </tr>
          </tbody>
          <tfoot class="bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t-2 border-slate-200 dark:border-slate-700">
            <tr>
              <td colspan="2" class="py-2.5 px-3.5 text-left border-r border-slate-200 dark:border-slate-700">Total / Rata-rata</td>
              <td class="py-2.5 px-2 font-mono">{{ wilkerDetailTotals.jentik_survei }}</td>
              <td class="py-2.5 px-2 font-mono text-rose-600">{{ wilkerDetailTotals.jentik_kontainer_positif }}</td>
              <td class="py-2.5 px-2 font-mono border-r border-slate-200 dark:border-slate-700 text-teal-600">{{ wilkerDetailTotals.abj_avg }}%</td>
              <td class="py-2.5 px-2 font-mono">{{ wilkerDetailTotals.lalat_survei }}</td>
              <td class="py-2.5 px-2 font-mono">{{ wilkerDetailTotals.lalat_avg }}</td>
              <td class="py-2.5 px-2 border-r border-slate-200 dark:border-slate-700">-</td>
              <td class="py-2.5 px-2 font-mono">{{ wilkerDetailTotals.kecoa_survei }}</td>
              <td class="py-2.5 px-2 font-mono border-r border-slate-200 dark:border-slate-700 text-rose-600">{{ wilkerDetailTotals.kecoa_tertangkap }}</td>
              <td class="py-2.5 px-2 font-mono">{{ wilkerDetailTotals.tikus_perangkap }}</td>
              <td class="py-2.5 px-2 font-mono text-indigo-600">{{ wilkerDetailTotals.tikus_tertangkap }}</td>
              <td class="py-2.5 px-2 font-mono border-r border-slate-200 dark:border-slate-700">{{ wilkerDetailTotals.tikus_trap_avg }}%</td>
              <td class="py-2.5 px-2 font-mono">{{ wilkerDetailTotals.pinjal_survei }}</td>
              <td class="py-2.5 px-2 font-mono border-r border-slate-200 dark:border-slate-700">{{ wilkerDetailTotals.pinjal_umum + wilkerDetailTotals.pinjal_khusus }}</td>
              <td class="py-2.5 px-2 font-mono">{{ wilkerDetailTotals.anopheles_habitat_avg }}%</td>
              <td class="py-2.5 px-2 font-mono">{{ wilkerDetailTotals.anopheles_mbr_avg }}</td>
            </tr>
          </tfoot>
        </table>

        <!-- 2. Tab Jentik Aedes -->
        <table v-else-if="activeSiveraDetailTab === 'jentik'" class="w-full text-xs text-center border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold">
              <th class="py-2.5 px-3 w-12">No</th>
              <th class="py-2.5 px-3 text-left">Wilayah Kerja</th>
              <th class="py-2.5 px-3">Total Survei</th>
              <th class="py-2.5 px-3">Rumah Diperiksa</th>
              <th class="py-2.5 px-3">Rumah Positif</th>
              <th class="py-2.5 px-3">Kontainer Diperiksa</th>
              <th class="py-2.5 px-3">Kontainer Positif</th>
              <th class="py-2.5 px-3">ABJ (%)</th>
              <th class="py-2.5 px-3">Status Baku Mutu (&ge; 95%)</th>
              <th class="py-2.5 px-3">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-for="(row, idx) in filteredWilkerDetail" :key="row.wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
              <td class="py-2.5 px-3 text-slate-400 font-mono">{{ idx + 1 }}</td>
              <td class="py-2.5 px-3 text-left font-semibold text-slate-900 dark:text-slate-100">{{ row.wilker }}</td>
              <td class="py-2.5 px-3 font-mono font-bold">{{ row.jentik_survei }}</td>
              <td class="py-2.5 px-3 font-mono">{{ row.jentik_rumah_diperiksa }}</td>
              <td class="py-2.5 px-3 font-mono">{{ row.jentik_rumah_positif }}</td>
              <td class="py-2.5 px-3 font-mono">{{ row.jentik_kontainer_diperiksa }}</td>
              <td class="py-2.5 px-3 font-mono font-bold text-rose-600">{{ row.jentik_kontainer_positif }}</td>
              <td class="py-2.5 px-3 font-mono font-black" :class="row.abj >= 95 ? 'text-emerald-600' : 'text-rose-600'">{{ row.abj }}%</td>
              <td class="py-2.5 px-3">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="row.abj >= 95 ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800'">
                  {{ row.abj >= 95 ? 'Memenuhi Syarat' : 'Tidak Memenuhi' }}
                </span>
              </td>
              <td class="py-2.5 px-3">
                <button type="button" @click="emit('switch-vektor', 'Jentik Aedes')" class="text-teal-600 dark:text-teal-400 hover:text-teal-700 font-semibold text-[11px] hover:underline cursor-pointer">
                  Lihat Data
                </button>
              </td>
            </tr>
            <tr v-if="filteredWilkerDetail.length === 0">
              <td colspan="10" class="py-8 text-center text-slate-400">Belum ada data surveilans jentik.</td>
            </tr>
          </tbody>
          <tfoot class="bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
            <tr>
              <td colspan="2" class="py-2.5 px-3 text-left">Total Keseluruhan</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.jentik_survei }}</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.jentik_rumah_diperiksa }}</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.jentik_rumah_positif }}</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.jentik_kontainer_diperiksa }}</td>
              <td class="py-2.5 px-3 font-mono text-rose-600">{{ wilkerDetailTotals.jentik_kontainer_positif }}</td>
              <td class="py-2.5 px-3 font-mono text-teal-600">{{ wilkerDetailTotals.abj_avg }}%</td>
              <td colspan="2">-</td>
            </tr>
          </tfoot>
        </table>

        <!-- 3. Tab Lalat -->
        <table v-else-if="activeSiveraDetailTab === 'lalat'" class="w-full text-xs text-center border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold">
              <th class="py-2.5 px-3 w-12">No</th>
              <th class="py-2.5 px-3 text-left">Wilayah Kerja</th>
              <th class="py-2.5 px-3">Total Survei</th>
              <th class="py-2.5 px-3">Rata Fly Index</th>
              <th class="py-2.5 px-3 text-emerald-700 dark:text-emerald-400">Jumlah MS (&lt; 2)</th>
              <th class="py-2.5 px-3 text-rose-700 dark:text-rose-400">Jumlah TMS (&ge; 2)</th>
              <th class="py-2.5 px-3">Kepatuhan (%)</th>
              <th class="py-2.5 px-3">Status Baku Mutu</th>
              <th class="py-2.5 px-3">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-for="(row, idx) in filteredWilkerDetail" :key="row.wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
              <td class="py-2.5 px-3 text-slate-400 font-mono">{{ idx + 1 }}</td>
              <td class="py-2.5 px-3 text-left font-semibold text-slate-900 dark:text-slate-100">{{ row.wilker }}</td>
              <td class="py-2.5 px-3 font-mono font-bold">{{ row.lalat_survei }}</td>
              <td class="py-2.5 px-3 font-mono font-bold text-slate-900 dark:text-white">{{ Number(row.lalat_avg).toFixed(2) }}</td>
              <td class="py-2.5 px-3 font-mono text-emerald-600 font-bold">{{ row.lalat_ms }}</td>
              <td class="py-2.5 px-3 font-mono text-rose-600 font-bold">{{ row.lalat_tms }}</td>
              <td class="py-2.5 px-3 font-mono font-bold">{{ row.lalat_survei > 0 ? ((row.lalat_ms / row.lalat_survei) * 100).toFixed(1) : 100 }}%</td>
              <td class="py-2.5 px-3">
                <span v-if="row.lalat_survei > 0" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="row.lalat_avg < 2 ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800'">
                  {{ row.lalat_avg < 2 ? 'Memenuhi Syarat' : 'Di Atas Baku Mutu' }}
                </span>
                <span v-else class="text-slate-400">-</span>
              </td>
              <td class="py-2.5 px-3">
                <button type="button" @click="emit('switch-vektor', 'Lalat')" class="text-teal-600 dark:text-teal-400 hover:text-teal-700 font-semibold text-[11px] hover:underline cursor-pointer">
                  Lihat Data
                </button>
              </td>
            </tr>
            <tr v-if="filteredWilkerDetail.length === 0">
              <td colspan="9" class="py-8 text-center text-slate-400">Belum ada data surveilans lalat.</td>
            </tr>
          </tbody>
          <tfoot class="bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
            <tr>
              <td colspan="2" class="py-2.5 px-3 text-left">Total Keseluruhan</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.lalat_survei }}</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.lalat_avg }}</td>
              <td class="py-2.5 px-3 font-mono text-emerald-600">{{ wilkerDetailTotals.lalat_ms }}</td>
              <td class="py-2.5 px-3 font-mono text-rose-600">{{ wilkerDetailTotals.lalat_tms }}</td>
              <td colspan="3">-</td>
            </tr>
          </tfoot>
        </table>

        <!-- 4. Tab Kecoa -->
        <table v-else-if="activeSiveraDetailTab === 'kecoa'" class="w-full text-xs text-center border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold">
              <th class="py-2.5 px-3 w-12">No</th>
              <th class="py-2.5 px-3 text-left">Wilayah Kerja</th>
              <th class="py-2.5 px-3">Total Survei</th>
              <th class="py-2.5 px-3">Perangkap Dipasang</th>
              <th class="py-2.5 px-3">Kecoa Tertangkap</th>
              <th class="py-2.5 px-3">Indeks Kepadatan (ekor/trap)</th>
              <th class="py-2.5 px-3">Status Baku Mutu (&lt; 2)</th>
              <th class="py-2.5 px-3">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-for="(row, idx) in filteredWilkerDetail" :key="row.wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
              <td class="py-2.5 px-3 text-slate-400 font-mono">{{ idx + 1 }}</td>
              <td class="py-2.5 px-3 text-left font-semibold text-slate-900 dark:text-slate-100">{{ row.wilker }}</td>
              <td class="py-2.5 px-3 font-mono font-bold">{{ row.kecoa_survei }}</td>
              <td class="py-2.5 px-3 font-mono">{{ row.kecoa_perangkap }}</td>
              <td class="py-2.5 px-3 font-mono font-bold text-rose-600">{{ row.kecoa_tertangkap }}</td>
              <td class="py-2.5 px-3 font-mono font-bold">{{ Number(row.kecoa_kepadatan).toFixed(2) }}</td>
              <td class="py-2.5 px-3">
                <span v-if="row.kecoa_survei > 0" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="row.kecoa_kepadatan < 2 ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800'">
                  {{ row.kecoa_kepadatan < 2 ? 'Memenuhi Syarat' : 'Di Atas Baku Mutu' }}
                </span>
                <span v-else class="text-slate-400">-</span>
              </td>
              <td class="py-2.5 px-3">
                <button type="button" @click="emit('switch-vektor', 'Kecoa')" class="text-teal-600 dark:text-teal-400 hover:text-teal-700 font-semibold text-[11px] hover:underline cursor-pointer">
                  Lihat Data
                </button>
              </td>
            </tr>
            <tr v-if="filteredWilkerDetail.length === 0">
              <td colspan="8" class="py-8 text-center text-slate-400">Belum ada data surveilans kecoa.</td>
            </tr>
          </tbody>
          <tfoot class="bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
            <tr>
              <td colspan="2" class="py-2.5 px-3 text-left">Total Keseluruhan</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.kecoa_survei }}</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.kecoa_perangkap }}</td>
              <td class="py-2.5 px-3 font-mono text-rose-600">{{ wilkerDetailTotals.kecoa_tertangkap }}</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.kecoa_kepadatan }}</td>
              <td colspan="2">-</td>
            </tr>
          </tfoot>
        </table>

        <!-- 5. Tab Tikus & Pinjal -->
        <table v-else-if="activeSiveraDetailTab === 'tikus'" class="w-full text-xs text-center border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold">
              <th class="py-2.5 px-3 w-12">No</th>
              <th class="py-2.5 px-3 text-left">Wilayah Kerja</th>
              <th class="py-2.5 px-3">Survei Tikus</th>
              <th class="py-2.5 px-3">Perangkap</th>
              <th class="py-2.5 px-3">Tikus Tertangkap</th>
              <th class="py-2.5 px-3">Success Trap (%)</th>
              <th class="py-2.5 px-3">Survei Pinjal</th>
              <th class="py-2.5 px-3">Pinjal Umum</th>
              <th class="py-2.5 px-3">Pinjal Khusus</th>
              <th class="py-2.5 px-3">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-for="(row, idx) in filteredWilkerDetail" :key="row.wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
              <td class="py-2.5 px-3 text-slate-400 font-mono">{{ idx + 1 }}</td>
              <td class="py-2.5 px-3 text-left font-semibold text-slate-900 dark:text-slate-100">{{ row.wilker }}</td>
              <td class="py-2.5 px-3 font-mono font-bold">{{ row.tikus_survei }}</td>
              <td class="py-2.5 px-3 font-mono">{{ row.tikus_perangkap }}</td>
              <td class="py-2.5 px-3 font-mono font-bold text-indigo-600">{{ row.tikus_tertangkap }}</td>
              <td class="py-2.5 px-3 font-mono font-black" :class="row.tikus_success_trap_avg < 1 ? 'text-emerald-600' : 'text-amber-600'">
                {{ Number(row.tikus_success_trap_avg).toFixed(2) }}%
              </td>
              <td class="py-2.5 px-3 font-mono">{{ row.pinjal_survei }}</td>
              <td class="py-2.5 px-3 font-mono">{{ row.pinjal_umum }}</td>
              <td class="py-2.5 px-3 font-mono">{{ row.pinjal_khusus }}</td>
              <td class="py-2.5 px-3">
                <button type="button" @click="emit('switch-vektor', 'Tikus')" class="text-teal-600 dark:text-teal-400 hover:text-teal-700 font-semibold text-[11px] hover:underline cursor-pointer">
                  Lihat Data
                </button>
              </td>
            </tr>
            <tr v-if="filteredWilkerDetail.length === 0">
              <td colspan="10" class="py-8 text-center text-slate-400">Belum ada data surveilans tikus &amp; pinjal.</td>
            </tr>
          </tbody>
          <tfoot class="bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
            <tr>
              <td colspan="2" class="py-2.5 px-3 text-left">Total Keseluruhan</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.tikus_survei }}</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.tikus_perangkap }}</td>
              <td class="py-2.5 px-3 font-mono text-indigo-600">{{ wilkerDetailTotals.tikus_tertangkap }}</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.tikus_trap_avg }}%</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.pinjal_survei }}</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.pinjal_umum }}</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.pinjal_khusus }}</td>
              <td>-</td>
            </tr>
          </tfoot>
        </table>

        <!-- 6. Tab Anopheles -->
        <table v-else-if="activeSiveraDetailTab === 'anopheles'" class="w-full text-xs text-center border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold">
              <th class="py-2.5 px-3 w-12">No</th>
              <th class="py-2.5 px-3 text-left">Wilayah Kerja</th>
              <th class="py-2.5 px-3">Survei Jentik</th>
              <th class="py-2.5 px-3">Indeks Habitat (%)</th>
              <th class="py-2.5 px-3">Status Jentik (&lt; 1%)</th>
              <th class="py-2.5 px-3">Survei Nyamuk Dewasa</th>
              <th class="py-2.5 px-3">Rata MBR (ekor/org/mlm)</th>
              <th class="py-2.5 px-3">Status Nyamuk (&lt; 0.025)</th>
              <th class="py-2.5 px-3">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-for="(row, idx) in filteredWilkerDetail" :key="row.wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
              <td class="py-2.5 px-3 text-slate-400 font-mono">{{ idx + 1 }}</td>
              <td class="py-2.5 px-3 text-left font-semibold text-slate-900 dark:text-slate-100">{{ row.wilker }}</td>
              <td class="py-2.5 px-3 font-mono font-bold">{{ row.anopheles_jentik_survei }}</td>
              <td class="py-2.5 px-3 font-mono font-bold">{{ row.anopheles_habitat_avg }}%</td>
              <td class="py-2.5 px-3">
                <span v-if="row.anopheles_jentik_survei > 0" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="row.anopheles_habitat_avg < 1 ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800'">
                  {{ row.anopheles_habitat_avg < 1 ? 'Memenuhi Syarat' : 'Di Atas Baku Mutu' }}
                </span>
                <span v-else class="text-slate-400">-</span>
              </td>
              <td class="py-2.5 px-3 font-mono font-bold">{{ row.anopheles_nyamuk_survei }}</td>
              <td class="py-2.5 px-3 font-mono font-bold">{{ Number(row.anopheles_mbr_avg).toFixed(2) }}</td>
              <td class="py-2.5 px-3">
                <span v-if="row.anopheles_nyamuk_survei > 0" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="row.anopheles_mbr_avg < 0.025 ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800'">
                  {{ row.anopheles_mbr_avg < 0.025 ? 'Memenuhi Syarat' : 'Di Atas Baku Mutu' }}
                </span>
                <span v-else class="text-slate-400">-</span>
              </td>
              <td class="py-2.5 px-3">
                <button type="button" @click="emit('switch-vektor', 'Nyamuk Anopheles')" class="text-teal-600 dark:text-teal-400 hover:text-teal-700 font-semibold text-[11px] hover:underline cursor-pointer">
                  Lihat Data
                </button>
              </td>
            </tr>
            <tr v-if="filteredWilkerDetail.length === 0">
              <td colspan="9" class="py-8 text-center text-slate-400">Belum ada data surveilans Anopheles.</td>
            </tr>
          </tbody>
          <tfoot class="bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
            <tr>
              <td colspan="2" class="py-2.5 px-3 text-left">Total Keseluruhan</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.anopheles_jentik_survei }}</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.anopheles_habitat_avg }}%</td>
              <td>-</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.anopheles_nyamuk_survei }}</td>
              <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.anopheles_mbr_avg }}</td>
              <td colspan="2">-</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</template>
