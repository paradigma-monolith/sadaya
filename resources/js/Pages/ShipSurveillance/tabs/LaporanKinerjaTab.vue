<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { formatCurrency, formatNumber } from '../utils/formatters';
import {
  Ship,
  Flag,
  Compass,
  Anchor,
  DollarSign,
  AlertTriangle,
  Award,
  Activity,
  Info,
  Building,
  TrendingUp,
} from 'lucide-vue-next';

const props = defineProps({
  summary: {
    type: Object,
    default: () => ({}),
  },
  pnbpPerPelabuhan: {
    type: Array,
    default: () => [],
  },
  topVerifikators: {
    type: Array,
    default: () => [],
  },
  kpiTargets: {
    type: Object,
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

// Top Verifikator Leaderboard (Mengecualikan MOHAMAD DAHLAN FAZHRY)
const filteredTopVerifikators = computed(() => {
  return (props.topVerifikators || []).filter((ver) => {
    const name = (ver?.verifier_name || '').toUpperCase().trim();
    return !name.includes('MOHAMAD DAHLAN FAZHRY');
  });
});

const selectedTahun = ref(props.filters?.tahun || new Date().getFullYear());

watch(
  () => props.filters?.tahun,
  (val) => {
    if (val) selectedTahun.value = val;
  }
);

const handleYearChange = () => {
  router.get(
    '/ship-surveillance',
    {
      ...props.filters,
      tahun: selectedTahun.value,
      tab: 'laporan',
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
};

const pnbpTariffs = [
  { range: '≤ 100 GT', fee: 'Rp 50.000' },
  { range: '> 100 - 200 GT', fee: 'Rp 60.000' },
  { range: '> 200 - 350 GT', fee: 'Rp 70.000' },
  { range: '> 350 - 1.000 GT', fee: 'Rp 85.000' },
  { range: '> 1.000 - 2.000 GT', fee: 'Rp 120.000' },
  { range: '> 2.000 - 3.500 GT', fee: 'Rp 150.000' },
  { range: '> 3.500 - 7.000 GT', fee: 'Rp 175.000' },
  { range: '> 7.000 - 10.000 GT', fee: 'Rp 200.000' },
  { range: '> 20.000 GT', fee: 'Rp 300.000' },
];
</script>

<template>
  <div class="space-y-6">
    <!-- Quick KPI Counter Cards (Hanya di Tab Laporan & Analitik Kinerja) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
      <!-- Total Kapal -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
          <span class="text-xs font-medium uppercase tracking-wider">Total Kapal</span>
          <Ship class="w-4 h-4 text-cyan-600" />
        </div>
        <div class="mt-2">
          <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
            {{ formatNumber(summary.total_kapal) }}
          </span>
          <span class="text-xs text-slate-400 ml-1">Kpl</span>
        </div>
      </div>

      <!-- Kapal LN -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
          <span class="text-xs font-medium uppercase tracking-wider">Luar Negeri</span>
          <Flag class="w-4 h-4 text-amber-500" />
        </div>
        <div class="mt-2">
          <span class="text-2xl font-bold tracking-tight text-amber-600 dark:text-amber-400">
            {{ formatNumber(summary.kapal_luar_negeri) }}
          </span>
          <span class="text-xs text-slate-400 ml-1">Kpl ({{ summary.total_kapal ? Math.round((summary.kapal_luar_negeri / summary.total_kapal) * 100) : 0 }}%)</span>
        </div>
      </div>

      <!-- Kapal DN -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
          <span class="text-xs font-medium uppercase tracking-wider">Dalam Negeri</span>
          <Compass class="w-4 h-4 text-emerald-600" />
        </div>
        <div class="mt-2">
          <span class="text-2xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400">
            {{ formatNumber(summary.kapal_dalam_negeri) }}
          </span>
          <span class="text-xs text-slate-400 ml-1">Kpl</span>
        </div>
      </div>

      <!-- Total GT -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
          <span class="text-xs font-medium uppercase tracking-wider">Total Bobot GT</span>
          <Anchor class="w-4 h-4 text-[#00A99D]" />
        </div>
        <div class="mt-2">
          <span class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
            {{ formatNumber(summary.total_gt) }}
          </span>
          <span class="text-xs text-slate-400 ml-1">GT</span>
        </div>
      </div>

      <!-- Estimasi PNBP -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
          <span class="text-xs font-medium uppercase tracking-wider">Estimasi PNBP</span>
          <DollarSign class="w-4 h-4 text-emerald-600" />
        </div>
        <div class="mt-2">
          <span class="text-lg font-bold tracking-tight text-emerald-600 dark:text-emerald-400">
            {{ formatCurrency(summary.total_pnbp) }}
          </span>
        </div>
      </div>

      <!-- High Risk Alert -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
          <span class="text-xs font-medium uppercase tracking-wider">Risiko Tinggi</span>
          <AlertTriangle class="w-4 h-4 text-rose-500" />
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-2xl font-bold tracking-tight text-rose-600 dark:text-rose-400">
            {{ formatNumber(summary.kresiko_tinggi) }}
          </span>
          <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-rose-500/10 text-rose-600 border border-rose-500/20">
            Karantina
          </span>
        </div>
      </div>
    </div>

    <!-- Laporan Overview & Top Verifikator -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Top Verifikator Leaderboard -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
          <div class="flex items-center gap-2">
            <Award class="w-5 h-5 text-amber-500" />
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Verifikator Terbanyak</h3>
          </div>
          <span class="text-xs text-slate-400">Petugas Karantina</span>
        </div>

        <div class="space-y-3">
          <div
            v-for="(ver, index) in filteredTopVerifikators"
            :key="index"
            class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60"
          >
            <div class="flex items-center gap-2.5 min-w-0">
              <span
                :class="[
                  'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0',
                  index === 0 ? 'bg-amber-500 text-white' : index === 1 ? 'bg-slate-400 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300'
                ]"
              >
                {{ index + 1 }}
              </span>
              <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">
                {{ ver.verifier_name }}
              </span>
            </div>
            <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 shrink-0 ml-2">
              {{ ver.jumlah_verifikasi }} Kapal
            </div>
          </div>

          <div v-if="!filteredTopVerifikators || filteredTopVerifikators.length === 0" class="text-center py-6 text-xs text-slate-400">
            Belum ada data verifikasi kapal.
          </div>
        </div>
      </div>

      <!-- Distribusi Risiko & Keputusan -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between space-y-4">
        <div class="space-y-4">
          <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
            <Activity class="w-5 h-5 text-[#00A99D]" />
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Distribusi Faktor Risiko (Kresiko)</h3>
          </div>

          <div class="space-y-3">
            <!-- Risiko Rendah -->
            <div>
              <div class="flex justify-between text-xs mb-1 font-medium">
                <span class="text-emerald-700 dark:text-emerald-400">RENDAH (Skor &lt; 10)</span>
                <span class="font-bold">{{ summary.kresiko_rendah }} Kapal ({{ summary.total_kapal ? Math.round((summary.kresiko_rendah / summary.total_kapal) * 100) : 0 }}%)</span>
              </div>
              <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                <div
                  class="bg-emerald-500 h-full rounded-full transition-all duration-500"
                  :style="{ width: (summary.total_kapal ? (summary.kresiko_rendah / summary.total_kapal) * 100 : 0) + '%' }"
                ></div>
              </div>
            </div>

            <!-- Risiko Sedang -->
            <div>
              <div class="flex justify-between text-xs mb-1 font-medium">
                <span class="text-amber-700 dark:text-amber-400">SEDANG (Skor 10 - 19)</span>
                <span class="font-bold">{{ summary.kresiko_sedang }} Kapal ({{ summary.total_kapal ? Math.round((summary.kresiko_sedang / summary.total_kapal) * 100) : 0 }}%)</span>
              </div>
              <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                <div
                  class="bg-amber-500 h-full rounded-full transition-all duration-500"
                  :style="{ width: (summary.total_kapal ? (summary.kresiko_sedang / summary.total_kapal) * 100 : 0) + '%' }"
                ></div>
              </div>
            </div>

            <!-- Risiko Tinggi -->
            <div>
              <div class="flex justify-between text-xs mb-1 font-medium">
                <span class="text-rose-700 dark:text-rose-400">TINGGI (Skor &ge; 20)</span>
                <span class="font-bold">{{ summary.kresiko_tinggi }} Kapal ({{ summary.total_kapal ? Math.round((summary.kresiko_tinggi / summary.total_kapal) * 100) : 0 }}%)</span>
              </div>
              <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                <div
                  class="bg-rose-500 h-full rounded-full transition-all duration-500"
                  :style="{ width: (summary.total_kapal ? (summary.kresiko_tinggi / summary.total_kapal) * 100 : 0) + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Keputusan Sandar vs Karantina -->
        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 gap-2.5 text-center">
          <div class="p-2.5 rounded-xl bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-200 dark:border-cyan-800/40">
            <div class="text-[10px] uppercase font-bold text-cyan-700 dark:text-cyan-400">Izin Sandar</div>
            <div class="text-lg font-extrabold text-cyan-900 dark:text-cyan-200">{{ summary.status_sandar }} Kapal</div>
          </div>
          <div class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/40">
            <div class="text-[10px] uppercase font-bold text-rose-700 dark:text-rose-400">Zona Karantina</div>
            <div class="text-lg font-extrabold text-rose-900 dark:text-rose-200">{{ summary.status_karantina }} Kapal</div>
          </div>
        </div>
      </div>

      <!-- Tarif PNBP PP Kemenkes Reference Box -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
        <div>
          <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3 mb-3">
            <Info class="w-5 h-5 text-emerald-600" />
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tarif PNBP Jasa Karantina</h3>
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
            Tarif resmi pemeriksaan kesehatan kapal dalam karantina berdasarkan Peraturan Pemerintah Tarif PNBP Kemenkes RI:
          </p>
          <div class="space-y-1.5 text-[11px] text-slate-600 dark:text-slate-300 max-h-48 overflow-y-auto pr-1">
            <div
              v-for="(tariff, idx) in pnbpTariffs"
              :key="tariff.range"
              :class="[
                'flex justify-between py-1',
                idx < pnbpTariffs.length - 1 ? 'border-b border-slate-100 dark:border-slate-800' : ''
              ]"
            >
              <span>{{ tariff.range }}</span>
              <span class="font-bold">{{ tariff.fee }}</span>
            </div>
          </div>
        </div>

        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-xs">
          <span class="text-slate-500">Total Potensi PNBP:</span>
          <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono text-sm">{{ formatCurrency(summary.total_pnbp) }}</span>
        </div>
      </div>
    </div>

    <!-- Tabel Estimasi PNBP per Pelabuhan -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
      <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
        <div class="flex items-center gap-2">
          <Building class="w-5 h-5 text-[#00A99D]" />
          <h3 class="text-sm font-bold text-slate-900 dark:text-white">Estimasi Penerimaan PNBP per Pelabuhan Wilker</h3>
        </div>
        <span class="text-xs text-slate-400">Breakdown Real-Time</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
          <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
            <tr>
              <th class="py-3 px-4">Pelabuhan Wilker</th>
              <th class="py-3 px-4 text-center">Jumlah Kapal</th>
              <th class="py-3 px-4 text-center">Akumulasi Bobot GT</th>
              <th class="py-3 px-4 text-right">Estimasi PNBP (Rp)</th>
              <th class="py-3 px-4 text-right">Kontribusi (%)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-for="(pnbp, index) in pnbpPerPelabuhan" :key="index" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
              <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">
                <div class="flex items-center gap-2">
                  <Anchor class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
                  <span>{{ pnbp.pelabuhan }}</span>
                </div>
              </td>
              <td class="py-3 px-4 text-center font-mono">{{ pnbp.jumlah_kapal }} Kapal</td>
              <td class="py-3 px-4 text-center font-mono">{{ formatNumber(pnbp.total_gt) }} GT</td>
              <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                {{ formatCurrency(pnbp.total_pnbp) }}
              </td>
              <td class="py-3 px-4 text-right font-mono font-semibold">{{ pnbp.persentase }}%</td>
            </tr>
          </tbody>
          <tfoot class="bg-slate-100/60 dark:bg-slate-800/80 font-bold border-t border-slate-200 dark:border-slate-700">
            <tr>
              <td class="py-3 px-4">TOTAL KESELURUHAN</td>
              <td class="py-3 px-4 text-center font-mono">{{ summary.total_kapal }} Kapal</td>
              <td class="py-3 px-4 text-center font-mono">{{ formatNumber(summary.total_gt) }} GT</td>
              <td class="py-3 px-4 text-right font-mono text-emerald-700 dark:text-emerald-300">
                {{ formatCurrency(summary.total_pnbp) }}
              </td>
              <td class="py-3 px-4 text-right font-mono">100%</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Target Capaian Perjanjian Kinerja (PK Kapal Luar Negeri) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4">
      <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-3">
        <div class="flex items-center gap-2">
          <TrendingUp class="w-5 h-5 text-[#00A99D]" />
          <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Capaian Target Kedatangan Kapal Luar Negeri (Perjanjian Kinerja)</h3>
            <p class="text-xs text-slate-500">Monitoring realisasi pengawasan kapal internasional vs target kinerja tahun {{ selectedTahun }}</p>
          </div>
        </div>
        <select
          v-model="selectedTahun"
          @change="handleYearChange"
          class="text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-1.5 font-semibold"
        >
          <option :value="2026">Tahun 2026</option>
          <option :value="2025">Tahun 2025</option>
        </select>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Bulanan -->
        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Target Bulanan (Bulan {{ kpiTargets.bulanan?.bulan }})</span>
              <span class="text-xs font-mono font-bold text-emerald-600">{{ kpiTargets.bulanan?.persentase }}%</span>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-700 h-3 rounded-full overflow-hidden mb-3">
              <div class="bg-emerald-500 h-full rounded-full transition-all duration-500" :style="{ width: (kpiTargets.bulanan?.persentase || 0) + '%' }"></div>
            </div>
          </div>
          <div class="flex justify-between items-center text-xs font-mono text-slate-500">
            <span>Realisasi: <strong class="text-slate-900 dark:text-white">{{ kpiTargets.bulanan?.realisasi }} Kpl</strong></span>
            <span>Target: <strong>{{ kpiTargets.bulanan?.target }} Kpl</strong></span>
          </div>
        </div>

        <!-- Triwulanan -->
        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Target Triwulan {{ kpiTargets.triwulanan?.triwulan }}</span>
              <span class="text-xs font-mono font-bold text-amber-500">{{ kpiTargets.triwulanan?.persentase }}%</span>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-700 h-3 rounded-full overflow-hidden mb-3">
              <div class="bg-amber-500 h-full rounded-full transition-all duration-500" :style="{ width: (kpiTargets.triwulanan?.persentase || 0) + '%' }"></div>
            </div>
          </div>
          <div class="flex justify-between items-center text-xs font-mono text-slate-500">
            <span>Realisasi: <strong class="text-slate-900 dark:text-white">{{ kpiTargets.triwulanan?.realisasi }} Kpl</strong></span>
            <span>Target: <strong>{{ kpiTargets.triwulanan?.target }} Kpl</strong></span>
          </div>
        </div>

        <!-- Tahunan -->
        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Target Tahunan ({{ kpiTargets.tahunan?.tahun }})</span>
              <span class="text-xs font-mono font-bold text-cyan-600">{{ kpiTargets.tahunan?.persentase }}%</span>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-700 h-3 rounded-full overflow-hidden mb-3">
              <div class="bg-cyan-500 h-full rounded-full transition-all duration-500" :style="{ width: (kpiTargets.tahunan?.persentase || 0) + '%' }"></div>
            </div>
          </div>
          <div class="flex justify-between items-center text-xs font-mono text-slate-500">
            <span>Realisasi: <strong class="text-slate-900 dark:text-white">{{ kpiTargets.tahunan?.realisasi }} Kpl</strong></span>
            <span>Target: <strong>{{ kpiTargets.tahunan?.target }} Kpl</strong></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

