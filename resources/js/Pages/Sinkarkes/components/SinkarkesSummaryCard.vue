<script setup>
import { ref, computed } from 'vue';
import { Building2, Activity, CalendarCheck, Search, Table } from 'lucide-vue-next';

const props = defineProps({
  year: {
    type: Number,
    required: true,
  },
  isConsolidated: {
    type: Boolean,
    default: true,
  },
  selectedWilker: {
    type: String,
    default: 'all',
  },
  summaryTable: {
    type: Object,
    default: () => ({ items: [], monthly_totals: {}, total_year: 0, section_totals: {} }),
  },
  wilkerSummary: {
    type: Object,
    default: () => ({ items: [], monthly_totals: {}, total_year: 0 }),
  },
  matrix: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['open-month-form']);

const activeSummaryTab = ref('indicators'); // 'indicators' | 'wilkers' | 'compliance'
const selectedSectionFilter = ref('ALL'); // 'ALL' | 'KSE' | 'UKP' | 'PRL'
const summarySearchQuery = ref('');
const monthNames = ['JAN', 'FEB', 'MART', 'APRL', 'MEI', 'JUN', 'JULI', 'AGUST', 'SEPT', 'OKT', 'NOV', 'DES'];

const filteredSummaryItems = computed(() => {
  const items = props.summaryTable?.items || [];
  return items.filter(item => {
    const matchSection = selectedSectionFilter.value === 'ALL' || item.section === selectedSectionFilter.value;
    const matchSearch = !summarySearchQuery.value ||
      item.label.toLowerCase().includes(summarySearchQuery.value.toLowerCase()) ||
      item.code.toLowerCase().includes(summarySearchQuery.value.toLowerCase());
    return matchSection && matchSearch;
  });
});

const sectionCounts = computed(() => {
  const items = props.summaryTable?.items || [];
  return {
    ALL: items.length,
    KSE: items.filter(i => i.section === 'KSE').length,
    UKP: items.filter(i => i.section === 'UKP').length,
    PRL: items.filter(i => i.section === 'PRL').length,
  };
});
</script>

<template>
  <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-3xl p-5 sm:p-6 shadow-xs space-y-4">
    <!-- Header & Top Controls -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
      <div>
        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#00A99D]/10 text-[#005851] dark:text-[#00A99D] mb-1">
          <Table class="w-3.5 h-3.5" />
          <span>TOTAL SUMMARY &amp; REKAPITULASI (JAN &ndash; DES {{ year }})</span>
        </div>
        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
          Tabel Total Summary Bulanan &amp; Capaian Tahunan
        </h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
          Rekapitulasi total capaian indikator utama per bulan Januari s/d Desember beserta akumulasi total tahunan {{ isConsolidated ? 'seluruh Wilayah Kerja' : selectedWilker }}.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-1.5 bg-slate-100 dark:bg-[#161c28] p-1 rounded-2xl border border-slate-200/60 dark:border-slate-800 self-start lg:self-auto">
        <button
          type="button"
          @click="activeSummaryTab = 'indicators'"
          class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer"
          :class="activeSummaryTab === 'indicators' ? 'bg-white dark:bg-[#1f2633] text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
        >
          <Activity class="w-3.5 h-3.5 text-[#00A99D]" />
          <span>Rekap Indikator Utama</span>
        </button>
        <button
          type="button"
          @click="activeSummaryTab = 'wilkers'"
          class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer"
          :class="activeSummaryTab === 'wilkers' ? 'bg-white dark:bg-[#1f2633] text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
        >
          <Building2 class="w-3.5 h-3.5 text-[#7CA622]" />
          <span>Rekap per Wilker</span>
        </button>
        <button
          type="button"
          @click="activeSummaryTab = 'compliance'"
          class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer"
          :class="activeSummaryTab === 'compliance' ? 'bg-white dark:bg-[#1f2633] text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
        >
          <CalendarCheck class="w-3.5 h-3.5 text-sky-500" />
          <span>Status Pengisian</span>
        </button>
      </div>
    </div>

    <!-- Filter Bar (For Indikator View) -->
    <div v-if="activeSummaryTab === 'indicators'" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-1">
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
          class="px-3 py-1 rounded-xl text-xs font-medium transition-all shrink-0 cursor-pointer flex items-center gap-1.5 border"
          :class="selectedSectionFilter === sec.id
            ? 'bg-[#00A99D] text-white border-[#00A99D] shadow-xs font-bold'
            : 'bg-white dark:bg-[#161c28] text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-[#1b2230]'"
        >
          <span>{{ sec.label }}</span>
          <span
            class="px-1.5 py-0.2 rounded-full text-[10px] font-mono"
            :class="selectedSectionFilter === sec.id ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500'"
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
          class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 dark:bg-[#161c28] border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-hidden focus:ring-1 focus:ring-[#00A99D] text-slate-800 dark:text-slate-100"
        />
      </div>
    </div>

    <!-- TAB 1: Tabel Indikator Utama (Jan - Des + Total Tahunan) -->
    <div v-if="activeSummaryTab === 'indicators'" class="md:hidden space-y-2.5">
      <div
        v-for="row in filteredSummaryItems"
        :key="`mob-ind-${row.code}`"
        class="p-3.5 rounded-2xl border border-slate-200 dark:border-[#232a36] bg-white dark:bg-[#12161f] space-y-2.5"
      >
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0 space-y-1">
            <div class="flex items-center gap-1.5">
              <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-[#1b2230] font-mono font-bold text-[10px] text-slate-700 dark:text-slate-300">{{ row.code }}</span>
              <span
                class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider"
                :class="{
                  'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400': row.section === 'KSE',
                  'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400': row.section === 'UKP',
                  'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400': row.section === 'PRL',
                }"
              >{{ row.section }}</span>
              <span class="text-[10px] font-mono text-slate-400">{{ row.unit }}</span>
            </div>
            <p class="text-xs font-semibold text-slate-800 dark:text-slate-100 leading-snug">{{ row.label }}</p>
          </div>
          <div class="text-right shrink-0 px-2.5 py-1 rounded-xl bg-[#00A99D]/10">
            <span class="block text-[9px] uppercase font-semibold text-slate-500 dark:text-slate-400">Total</span>
            <span class="font-mono font-bold text-xs text-[#005851] dark:text-[#00A99D]">
              {{ row.total_year > 0 ? row.total_year.toLocaleString('id-ID') : '-' }}
            </span>
          </div>
        </div>
        <div class="grid grid-cols-4 gap-1.5 pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px] font-mono">
          <div v-for="(m, idx) in monthNames" :key="`mob-m-${row.code}-${idx}`" class="flex items-center justify-between px-1.5 py-0.5 rounded bg-slate-50 dark:bg-[#161c28]">
            <span class="text-slate-400">{{ m }}</span>
            <span :class="row.months[idx + 1] > 0 ? 'font-bold text-slate-800 dark:text-slate-100' : 'text-slate-300 dark:text-slate-600'">
              {{ row.months[idx + 1] > 0 ? row.months[idx + 1].toLocaleString('id-ID') : '-' }}
            </span>
          </div>
        </div>
      </div>
      <div v-if="filteredSummaryItems.length === 0" class="py-8 text-center text-xs text-slate-400 italic">
        Tidak ada indikator yang sesuai dengan filter pencarian.
      </div>
    </div>

    <div v-if="activeSummaryTab === 'indicators'" class="hidden md:block overflow-x-auto rounded-2xl border border-slate-200 dark:border-[#232a36]">
      <table class="w-full min-w-[980px] text-left text-xs border-collapse">
        <thead>
          <tr class="bg-slate-50 dark:bg-[#161c28] text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200 dark:border-slate-800">
            <th class="py-3 px-3 w-20 text-center sticky left-0 bg-slate-50 dark:bg-[#161c28] z-10 shadow-xs">Kode</th>
            <th class="py-3 px-3 min-w-[240px] sticky left-20 bg-slate-50 dark:bg-[#161c28] z-10 shadow-xs">Uraian Indikator</th>
            <th class="py-3 px-2 text-center w-14">Satuan</th>
            <th
              v-for="m in monthNames"
              :key="m"
              class="py-3 px-2 text-right w-14 font-mono text-[10px]"
            >
              {{ m }}
            </th>
            <th class="py-3 px-3 text-right w-24 bg-[#00A99D]/10 text-[#005851] dark:text-[#00A99D] font-mono font-bold text-[11px]">
              TOTAL TAHUN
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-[#1f2633]">
          <tr
            v-for="row in filteredSummaryItems"
            :key="row.code"
            class="hover:bg-slate-50/80 dark:hover:bg-[#161c28]/60 transition-colors group"
          >
            <td class="py-2.5 px-3 font-mono font-bold text-[11px] text-center text-slate-600 dark:text-slate-400 sticky left-0 bg-white dark:bg-[#12161f] group-hover:bg-slate-50/80 dark:group-hover:bg-[#161c28]/60 z-10 shadow-xs">
              <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-[#1b2230] text-slate-700 dark:text-slate-300">
                {{ row.code }}
              </span>
            </td>

            <td class="py-2.5 px-3 text-slate-800 dark:text-slate-100 font-medium sticky left-20 bg-white dark:bg-[#12161f] group-hover:bg-slate-50/80 dark:group-hover:bg-[#161c28]/60 z-10 shadow-xs">
              <div class="flex items-center gap-1.5">
                <span
                  class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider shrink-0"
                  :class="{
                    'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400': row.section === 'KSE',
                    'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400': row.section === 'UKP',
                    'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400': row.section === 'PRL',
                  }"
                >
                  {{ row.section }}
                </span>
                <span class="truncate">{{ row.label }}</span>
              </div>
            </td>

            <td class="py-2.5 px-2 text-center text-slate-400 font-mono text-[10px]">
              {{ row.unit }}
            </td>

            <td
              v-for="mId in 12"
              :key="mId"
              class="py-2.5 px-2 text-right font-mono text-[11px]"
              :class="row.months[mId] > 0 ? 'text-slate-800 dark:text-slate-100 font-medium' : 'text-slate-300 dark:text-slate-600'"
            >
              {{ row.months[mId] > 0 ? row.months[mId].toLocaleString('id-ID') : '-' }}
            </td>

            <td class="py-2.5 px-3 text-right font-mono font-bold text-xs bg-[#00A99D]/5 text-[#005851] dark:text-[#00A99D] group-hover:bg-[#00A99D]/10 transition-colors">
              {{ row.total_year > 0 ? row.total_year.toLocaleString('id-ID') : '-' }}
            </td>
          </tr>

          <tr v-if="filteredSummaryItems.length === 0">
            <td colspan="16" class="py-8 text-center text-slate-400 italic">
              Tidak ada indikator yang sesuai dengan filter pencarian.
            </td>
          </tr>
        </tbody>

        <tfoot class="bg-slate-100/90 dark:bg-[#18202d] border-t-2 border-slate-300 dark:border-slate-700 font-bold">
          <tr>
            <td colspan="2" class="py-3 px-3 text-slate-800 dark:text-white sticky left-0 bg-slate-100 dark:bg-[#18202d] z-10 shadow-xs text-xs uppercase tracking-wider">
              TOTAL KESELURUHAN (GRAND TOTAL)
            </td>
            <td class="py-3 px-2 text-center text-slate-500 font-mono text-[10px]">
              Total
            </td>
            <td
              v-for="mId in 12"
              :key="mId"
              class="py-3 px-2 text-right font-mono text-[11px] text-slate-900 dark:text-white"
            >
              {{ (summaryTable?.monthly_totals?.[mId] || 0) > 0 ? summaryTable.monthly_totals[mId].toLocaleString('id-ID') : '-' }}
            </td>
            <td class="py-3 px-3 text-right font-mono font-extrabold text-sm bg-[#00A99D]/20 text-[#005851] dark:text-[#00A99D]">
              {{ (summaryTable?.total_year || 0).toLocaleString('id-ID') }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- TAB 2: Tabel Rekapitulasi per Wilayah Kerja -->
    <div v-else-if="activeSummaryTab === 'wilkers'" class="space-y-3">
      <div class="md:hidden space-y-2.5">
        <div
          v-for="wRow in (wilkerSummary?.items || [])"
          :key="`mob-w-${wRow.wilker}`"
          class="p-3.5 rounded-2xl border border-slate-200 dark:border-[#232a36] bg-white dark:bg-[#12161f] space-y-2.5"
        >
          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0 font-semibold text-xs text-slate-800 dark:text-slate-100">
              <Building2 class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
              <span class="truncate">{{ wRow.wilker }}</span>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
              <span
                class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold"
                :class="wRow.compliance_percent >= 80 ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-600'"
              >
                {{ wRow.filled_months }}/12
              </span>
              <span class="px-2 py-0.5 rounded-lg bg-[#7CA622]/10 font-mono font-bold text-xs text-[#547313] dark:text-[#7CA622]">
                {{ wRow.total_year > 0 ? wRow.total_year.toLocaleString('id-ID') : '-' }}
              </span>
            </div>
          </div>
          <div class="grid grid-cols-4 gap-1.5 pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px] font-mono">
            <div v-for="(m, idx) in monthNames" :key="`mob-wm-${wRow.wilker}-${idx}`" class="flex items-center justify-between px-1.5 py-0.5 rounded bg-slate-50 dark:bg-[#161c28]">
              <span class="text-slate-400">{{ m }}</span>
              <span :class="wRow.months[idx + 1] > 0 ? 'font-bold text-slate-800 dark:text-slate-100' : 'text-slate-300 dark:text-slate-600'">
                {{ wRow.months[idx + 1] > 0 ? wRow.months[idx + 1].toLocaleString('id-ID') : '-' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="hidden md:block overflow-x-auto rounded-2xl border border-slate-200 dark:border-[#232a36]">
        <table class="w-full min-w-[980px] text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-[#161c28] text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200 dark:border-slate-800">
              <th class="py-3 px-3 min-w-[220px] sticky left-0 bg-slate-50 dark:bg-[#161c28] z-10 shadow-xs">Wilayah Kerja / Pos</th>
              <th class="py-3 px-2 text-center w-28">Kepatuhan</th>
              <th
                v-for="m in monthNames"
                :key="m"
                class="py-3 px-2 text-right w-14 font-mono text-[10px]"
              >
                {{ m }}
              </th>
              <th class="py-3 px-3 text-right w-28 bg-[#7CA622]/10 text-[#547313] dark:text-[#7CA622] font-mono font-bold text-[11px]">
                TOTAL TAHUN
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-[#1f2633]">
            <tr
              v-for="wRow in (wilkerSummary?.items || [])"
              :key="wRow.wilker"
              class="hover:bg-slate-50/80 dark:hover:bg-[#161c28]/60 transition-colors group"
            >
              <td class="py-2.5 px-3 font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2 sticky left-0 bg-white dark:bg-[#12161f] group-hover:bg-slate-50/80 dark:group-hover:bg-[#161c28]/60 z-10 shadow-xs">
                <Building2 class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
                <span class="truncate">{{ wRow.wilker }}</span>
              </td>

              <td class="py-2.5 px-2 text-center">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold"
                  :class="wRow.compliance_percent >= 80 ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-600'"
                >
                  {{ wRow.filled_months }}/12 bln ({{ wRow.compliance_percent }}%)
                </span>
              </td>

              <td
                v-for="mId in 12"
                :key="mId"
                class="py-2.5 px-2 text-right font-mono text-[11px]"
                :class="wRow.months[mId] > 0 ? 'text-slate-800 dark:text-slate-100 font-medium' : 'text-slate-300 dark:text-slate-600'"
              >
                {{ wRow.months[mId] > 0 ? wRow.months[mId].toLocaleString('id-ID') : '-' }}
              </td>

              <td class="py-2.5 px-3 text-right font-mono font-bold text-xs bg-[#7CA622]/5 text-[#547313] dark:text-[#7CA622] group-hover:bg-[#7CA622]/10 transition-colors">
                {{ wRow.total_year > 0 ? wRow.total_year.toLocaleString('id-ID') : '-' }}
              </td>
            </tr>
          </tbody>

          <tfoot class="bg-slate-100/90 dark:bg-[#18202d] border-t-2 border-slate-300 dark:border-slate-700 font-bold">
            <tr>
              <td class="py-3 px-3 text-slate-800 dark:text-white sticky left-0 bg-slate-100 dark:bg-[#18202d] z-10 shadow-xs text-xs uppercase tracking-wider">
                TOTAL KONSOLIDASI BKK BANDUNG
              </td>
              <td class="py-3 px-2 text-center text-slate-500 font-mono text-[10px]">
                7 Wilker
              </td>
              <td
                v-for="mId in 12"
                :key="mId"
                class="py-3 px-2 text-right font-mono text-[11px] text-slate-900 dark:text-white"
              >
                {{ (wilkerSummary?.monthly_totals?.[mId] || 0) > 0 ? wilkerSummary.monthly_totals[mId].toLocaleString('id-ID') : '-' }}
              </td>
              <td class="py-3 px-3 text-right font-mono font-extrabold text-sm bg-[#7CA622]/20 text-[#547313] dark:text-[#7CA622]">
                {{ (wilkerSummary?.total_year || 0).toLocaleString('id-ID') }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- TAB 3: Status Pengisian (Matriks Kepatuhan Dot Table) -->
    <div v-else-if="activeSummaryTab === 'compliance'" class="space-y-3">
      <div class="flex items-center justify-between text-xs text-slate-500 px-1">
        <span>Klik pada sel bulan untuk membuka dan mengisi formulir bulanan.</span>
        <div class="flex items-center gap-3">
          <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span><span>Terisi</span></div>
          <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-slate-200 dark:bg-slate-700"></span><span>Kosong</span></div>
        </div>
      </div>

      <div class="md:hidden space-y-2.5">
        <div
          v-for="row in matrix"
          :key="`mob-comp-${row.wilker}`"
          class="p-3.5 rounded-2xl border border-slate-200 dark:border-[#232a36] bg-white dark:bg-[#12161f] space-y-2.5"
        >
          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0 font-semibold text-xs text-slate-800 dark:text-slate-100">
              <Building2 class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
              <span class="truncate">{{ row.wilker }}</span>
            </div>
            <span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 shrink-0">{{ row.compliance_percent }}%</span>
          </div>
          <div class="grid grid-cols-6 gap-1.5">
            <button
              v-for="(m, idx) in monthNames"
              :key="`mob-btn-${row.wilker}-${idx}`"
              type="button"
              @click="emit('open-month-form', row.wilker, idx + 1)"
              :aria-label="row.months[idx + 1]?.has_data ? `Buka ${row.wilker} bulan ${m}: ${row.months[idx + 1].filled_count} indikator terisi` : `Isi ${row.wilker} bulan ${m}`"
              class="py-1.5 rounded-lg flex flex-col items-center justify-center text-[10px] font-mono transition cursor-pointer border"
              :class="row.months[idx + 1]?.has_data
                ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/60'
                : 'bg-slate-50 dark:bg-[#161c28] text-slate-400 border-slate-200/70 dark:border-slate-800'"
            >
              <span>{{ m }}</span>
              <span class="text-[9px] font-bold">{{ row.months[idx + 1]?.has_data ? row.months[idx + 1].filled_count : '-' }}</span>
            </button>
          </div>
        </div>
      </div>

      <div class="hidden md:block overflow-x-auto rounded-2xl border border-slate-200 dark:border-[#232a36]">
        <table class="w-full min-w-[760px] text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-[#161c28] text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200 dark:border-slate-800">
              <th class="py-3 px-3 min-w-[200px]">Wilayah Kerja</th>
              <th
                v-for="m in monthNames"
                :key="m"
                class="py-3 px-2 text-center w-12 text-[10px]"
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
              <td class="py-2.5 px-3 font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <Building2 class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
                <span class="truncate">{{ row.wilker }}</span>
              </td>
              <td
                v-for="mId in 12"
                :key="mId"
                class="py-2 px-1 text-center"
              >
                <button
                  type="button"
                  @click="emit('open-month-form', row.wilker, mId)"
                  class="w-8 h-8 rounded-xl flex flex-col items-center justify-center transition-all cursor-pointer group"
                  :class="[
                    row.months[mId]?.has_data
                      ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 hover:scale-105'
                      : 'bg-slate-50 dark:bg-[#161c28] text-slate-300 dark:text-slate-600 border border-slate-200/60 dark:border-slate-800 hover:border-slate-300'
                  ]"
                  :aria-label="row.months[mId]?.has_data ? `${row.wilker} - Bulan ${mId}: ${row.months[mId].filled_count} indikator terisi.` : `${row.wilker} - Bulan ${mId}: Belum ada data.`"
                  :title="row.months[mId]?.has_data ? `${row.wilker} - Bulan ${mId}: ${row.months[mId].filled_count} indikator terisi.` : `${row.wilker} - Bulan ${mId}: Belum ada data.`"
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
  </div>
</template>
