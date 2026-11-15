<script setup>
import { ref, computed } from 'vue';
import {
  Bug,
  Droplets,
  Activity,
  Rat,
  Layers,
  Wind,
  Table,
  Search,
  X,
} from 'lucide-vue-next';

const props = defineProps({
  wilkerDetail: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['switch-vektor']);

const activeSiveraDetailTab = ref('semua');
const siveraWilkerSearch = ref('');

const detailTabs = [
  { key: 'semua', label: 'Semua Vektor', activeClass: 'bg-teal-600 text-white shadow-xs' },
  { key: 'jentik', label: 'Jentik Aedes', activeClass: 'bg-blue-600 text-white shadow-xs' },
  { key: 'lalat', label: 'Lalat', activeClass: 'bg-amber-600 text-white shadow-xs' },
  { key: 'kecoa', label: 'Kecoa', activeClass: 'bg-rose-600 text-white shadow-xs' },
  { key: 'tikus', label: 'Tikus & Pinjal', activeClass: 'bg-indigo-600 text-white shadow-xs' },
  { key: 'anopheles', label: 'Anopheles', activeClass: 'bg-purple-600 text-white shadow-xs' },
];

const filteredWilkerDetail = computed(() => {
  const list = props.wilkerDetail || [];
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
</script>

<template>
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
</template>
