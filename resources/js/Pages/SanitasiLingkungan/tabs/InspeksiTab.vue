<script setup>
import { computed } from 'vue';
import PaginationBar from '../../../Components/PaginationBar.vue';
import { useDebouncedFn } from '@/composables/useDebouncedFn';
import {
  Utensils,
  Droplets,
  Building2,
  Search,
  Filter,
  RotateCcw,
  Plus,
  Eye,
  Printer,
  Trash2,
} from 'lucide-vue-next';
import { formatDate, getRiskBadgeClass } from '../utils';

const props = defineProps({
  type: { type: String, required: true }, // 'tpp' | 'sab' | 'hsbu'
  dataset: { type: Object, default: () => ({ data: [] }) },
  perPage: { type: Number, default: 25 },
  wilkerOptions: { type: Array, default: () => [] },
  search: { type: String, default: '' },
  selectedWilker: { type: String, default: '' },
  selectedRisiko: { type: String, default: '' },
  tglMulai: { type: String, default: '' },
  tglSelesai: { type: String, default: '' },
});

const emit = defineEmits([
  'update:search',
  'update:selectedWilker',
  'update:selectedRisiko',
  'update:tglMulai',
  'update:tglSelesai',
  'apply-filter',
  'reset-filter',
  'change-per-page',
  'open-add-inspeksi',
  'open-add-sarana',
  'open-detail',
  'delete-inspection',
]);

const debouncedFilter = useDebouncedFn(() => {
  emit('apply-filter');
}, 350);

const metaByType = {
  tpp: {
    icon: Utensils,
    title: 'Riwayat Inspeksi Tempat Pengelolaan Pangan (TPP)',
    subtitle: 'Daftar pemeriksaan higiene sanitasi pangan berbasis risiko di pelabuhan dan bandara.',
    addBtn: '+ Input Inspeksi TPP Baru',
    addSaranaBtn: '+ Tambah Sarana TPP',
    searchLabel: 'Cari Sarana TPP / Penanggung Jawab / Pemeriksa',
    searchPlaceholder: 'Ketik nama TPP, penanggung jawab, pemeriksa...',
    riskLowLabel: 'Rendah (Skor ≤ 5)',
    riskMedLabel: 'Sedang (Skor 6 - 13)',
    riskHighLabel: 'Tinggi (Skor > 13)',
    nameHeader: 'Nama TPP',
    subHeader: 'Penanggung Jawab',
  },
  sab: {
    icon: Droplets,
    title: 'Riwayat Inspeksi Sarana Air Bersih (SAB)',
    subtitle: 'Pemeriksaan sumber air bersih, tandon penampungan, pipa distribusi & faktor risiko kontaminasi.',
    addBtn: '+ Input Inspeksi SAB Baru',
    addSaranaBtn: '+ Tambah Sarana SAB',
    searchLabel: 'Cari Sarana SAB / Penanggung Jawab / Pemeriksa',
    searchPlaceholder: 'Ketik nama sarana air bersih, pemeriksa...',
    riskLowLabel: 'Rendah (< 25%)',
    riskMedLabel: 'Sedang (25% - 50%)',
    riskHighLabel: 'Tinggi (> 50%)',
    nameHeader: 'Nama SAB',
    subHeader: 'Sumber & Kapasitas',
  },
  hsbu: {
    icon: Building2,
    title: 'Riwayat Inspeksi Higiene Bangunan & Usaha (HSBU)',
    subtitle: 'Penilaian kesehatan lingkungan gedung terminal, perkantoran, ruang tunggu & area publik.',
    addBtn: '+ Input Inspeksi HSBU Baru',
    addSaranaBtn: '+ Tambah Gedung HSBU',
    searchLabel: 'Cari Gedung / Penanggung Jawab / Pemeriksa',
    searchPlaceholder: 'Ketik nama bangunan/gedung, pemeriksa...',
    riskLowLabel: 'Rendah (Memenuhi)',
    riskMedLabel: 'Sedang',
    riskHighLabel: 'Tinggi (TMS)',
    nameHeader: 'Nama Gedung/Bangunan',
    subHeader: 'Penanggung Jawab',
  },
};

const ui = computed(() => metaByType[props.type] || metaByType.tpp);

const onSearchInput = (value) => {
  emit('update:search', value);
  debouncedFilter();
};

const triggerImmediateFilter = () => {
  debouncedFilter.cancel();
  emit('apply-filter');
};

const handleReset = () => {
  debouncedFilter.cancel();
  emit('reset-filter');
};

const getItemName = (item) => item.nama_tpp || item.nama_sab || item.nama_gedung || '-';
const getItemSub = (item) => {
  if (props.type === 'sab') {
    return `${item.sumber_air_sab || '-'} (${item.kapasitas_penampungan_sab || 0} L)`;
  }
  return item.penanggung_jawab_tpp || item.penanggung_jawab_hsbu || '-';
};
const getItemScore = (item) => (props.type === 'hsbu' ? item.total : item.hasil);
const hasActiveFilters = computed(() => Boolean(props.search || props.selectedWilker || props.selectedRisiko || props.tglMulai || props.tglSelesai));
</script>

<template>
  <div class="space-y-3">
    <div class="clinical-surface p-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/80 dark:border-slate-700/70">
        <div>
          <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <component :is="ui.icon" class="w-4 h-4 text-[#00A99D]" />
            <span>{{ ui.title }}</span>
          </h2>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ ui.subtitle }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <button
            type="button"
            @click="emit('open-add-inspeksi', type)"
            class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-[#005851] to-[#00A99D] hover:brightness-105 text-white text-xs font-bold flex items-center gap-1.5 cursor-pointer shadow-xs"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>{{ ui.addBtn }}</span>
          </button>
          <button
            type="button"
            @click="emit('open-add-sarana', type)"
            class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold flex items-center gap-1 cursor-pointer"
          >
            <span>{{ ui.addSaranaBtn }}</span>
          </button>
        </div>
      </div>

      <!-- FILTER BAR -->
      <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/60 mt-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
          <div class="lg:col-span-4">
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ ui.searchLabel }}</label>
            <div class="relative">
              <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
              <input
                type="text"
                :value="search"
                @input="onSearchInput($event.target.value)"
                @keyup.enter="triggerImmediateFilter"
                :placeholder="ui.searchPlaceholder"
                class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
              />
            </div>
          </div>

          <div class="lg:col-span-2">
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Wilayah Kerja</label>
            <select
              :value="selectedWilker"
              @change="emit('update:selectedWilker', $event.target.value); triggerImmediateFilter()"
              class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
            >
              <option value="">Semua Wilker</option>
              <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
            </select>
          </div>

          <div class="lg:col-span-2">
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tingkat Risiko</label>
            <select
              :value="selectedRisiko"
              @change="emit('update:selectedRisiko', $event.target.value); triggerImmediateFilter()"
              class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
            >
              <option value="">Semua Risiko</option>
              <option value="Rendah">{{ ui.riskLowLabel }}</option>
              <option value="Sedang">{{ ui.riskMedLabel }}</option>
              <option value="Tinggi">{{ ui.riskHighLabel }}</option>
            </select>
          </div>

          <div class="lg:col-span-2">
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Mulai</label>
            <input
              type="date"
              :value="tglMulai"
              @change="emit('update:tglMulai', $event.target.value); triggerImmediateFilter()"
              class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
            />
          </div>

          <div class="lg:col-span-2 flex items-end gap-1.5">
            <div class="flex-1">
              <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Selesai</label>
              <input
                type="date"
                :value="tglSelesai"
                @change="emit('update:tglSelesai', $event.target.value); triggerImmediateFilter()"
                class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
              />
            </div>
            <button
              type="button"
              @click="triggerImmediateFilter"
              class="p-2 rounded-xl bg-[#005851] hover:bg-[#004842] text-white transition cursor-pointer shadow-xs"
              aria-label="Terapkan filter"
              title="Terapkan Filter"
            >
              <Filter class="w-4 h-4" />
            </button>
            <button
              type="button"
              @click="handleReset"
              class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition cursor-pointer"
              aria-label="Reset semua filter"
              title="Reset Semua Filter"
            >
              <RotateCcw class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Active filter pills -->
        <div v-if="hasActiveFilters" class="mt-3 pt-2.5 border-t border-slate-200/60 dark:border-slate-700/50 flex items-center gap-2 flex-wrap text-xs">
          <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Filter Aktif:</span>
          <span v-if="search" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-[11px]">
            Kata Kunci: "{{ search }}"
            <button type="button" @click="emit('update:search', ''); triggerImmediateFilter()" aria-label="Hapus filter kata kunci" class="hover:text-rose-600 font-bold cursor-pointer">&times;</button>
          </span>
          <span v-if="selectedWilker" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-teal-50 dark:bg-teal-950/50 text-[#005851] dark:text-teal-300 border border-teal-200 dark:border-teal-800 text-[11px]">
            Wilker: {{ selectedWilker }}
            <button type="button" @click="emit('update:selectedWilker', ''); triggerImmediateFilter()" aria-label="Hapus filter wilker" class="hover:text-rose-600 font-bold cursor-pointer">&times;</button>
          </span>
          <span v-if="selectedRisiko" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-[11px]">
            Risiko: {{ selectedRisiko }}
            <button type="button" @click="emit('update:selectedRisiko', ''); triggerImmediateFilter()" aria-label="Hapus filter risiko" class="hover:text-rose-600 font-bold cursor-pointer">&times;</button>
          </span>
          <span v-if="tglMulai || tglSelesai" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 text-[11px]">
            Periode: {{ tglMulai || '...' }} s/d {{ tglSelesai || '...' }}
            <button type="button" @click="emit('update:tglMulai', ''); emit('update:tglSelesai', ''); triggerImmediateFilter()" aria-label="Hapus filter periode" class="hover:text-rose-600 font-bold cursor-pointer">&times;</button>
          </span>
          <button type="button" @click="handleReset" class="text-[11px] text-rose-600 hover:underline font-semibold ml-auto cursor-pointer">
            Reset Semua
          </button>
        </div>
      </div>

      <!-- MOBILE CARD VIEW -->
      <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800 mt-3">
        <div v-if="!dataset?.data?.length" class="py-10 text-center text-xs text-slate-400">
          Belum ada data inspeksi untuk filter ini.
        </div>
        <div
          v-for="item in dataset.data"
          :key="`mob-${type}-${item.id}`"
          class="py-3.5 space-y-2 text-xs"
        >
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <div class="font-bold text-[#005851] dark:text-emerald-400 truncate">{{ getItemName(item) }}</div>
              <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ item.wilker }} &bull; {{ formatDate(item.tanggal_inspeksi) }}</div>
            </div>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border shrink-0" :class="getRiskBadgeClass(item.tingkat_risiko)">
              {{ getItemScore(item) }} &bull; {{ item.tingkat_risiko }}
            </span>
          </div>
          <div class="text-[11px] text-slate-600 dark:text-slate-300 flex flex-wrap gap-x-3 gap-y-0.5">
            <span>{{ getItemSub(item) }}</span>
            <span class="text-slate-400">Pemeriksa: {{ item.nama_pemeriksa || '-' }}</span>
          </div>
          <div class="flex items-center justify-end gap-1.5 pt-1">
            <button
              type="button"
              @click="emit('open-detail', item, type)"
              class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 inline-flex items-center gap-1 text-[11px] font-semibold cursor-pointer"
              aria-label="Lihat rincian inspeksi"
              title="Lihat Rincian Inspeksi"
            >
              <Eye class="w-3.5 h-3.5" />
              <span>Detail</span>
            </button>
            <a
              :href="`/prl/inspeksi/${type}/${item.id}/pdf`"
              target="_blank"
              class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 inline-flex items-center gap-1 text-[11px] font-semibold cursor-pointer"
              aria-label="Cetak lembar pengawasan PDF"
              title="Cetak Lembar Pengawasan PDF"
            >
              <Printer class="w-3.5 h-3.5" />
              <span>PDF</span>
            </a>
            <button
              type="button"
              @click="emit('delete-inspection', item, type)"
              class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-950/60 text-rose-500 transition cursor-pointer"
              aria-label="Hapus catatan inspeksi"
              title="Hapus Catatan"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </div>

      <!-- DESKTOP TABLE -->
      <div class="hidden md:block overflow-x-auto mt-4">
        <table class="w-full text-xs text-left">
          <thead>
            <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
              <th class="py-3 px-3">No</th>
              <th class="py-3 px-3">Wilayah Kerja</th>
              <th class="py-3 px-3">Tanggal</th>
              <th class="py-3 px-3">{{ ui.nameHeader }}</th>
              <th class="py-3 px-3">{{ ui.subHeader }}</th>
              <th class="py-3 px-3">Petugas Pemeriksa</th>
              <th class="py-3 px-3 text-center">Skor &amp; Risiko</th>
              <th class="py-3 px-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-if="!dataset?.data?.length">
              <td colspan="8" class="py-10 text-center text-slate-400">
                Belum ada data inspeksi untuk filter ini.
              </td>
            </tr>
            <tr v-for="(item, idx) in dataset.data" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
              <td class="py-3 px-3 text-slate-400 font-mono">{{ ((dataset.current_page - 1) * dataset.per_page) + idx + 1 }}</td>
              <td class="py-3 px-3 font-semibold text-slate-800 dark:text-slate-200">{{ item.wilker }}</td>
              <td class="py-3 px-3 font-mono text-slate-500">{{ formatDate(item.tanggal_inspeksi) }}</td>
              <td class="py-3 px-3 font-bold text-[#005851] dark:text-emerald-400">{{ getItemName(item) }}</td>
              <td class="py-3 px-3 text-slate-600 dark:text-slate-300">{{ getItemSub(item) }}</td>
              <td class="py-3 px-3 text-slate-500 max-w-[200px] truncate" :title="item.nama_pemeriksa">{{ item.nama_pemeriksa }}</td>
              <td class="py-3 px-3 text-center">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="getRiskBadgeClass(item.tingkat_risiko)">
                  <span>Skor: {{ getItemScore(item) }}</span> &bull; <span>{{ item.tingkat_risiko }}</span>
                </span>
              </td>
              <td class="py-3 px-3 text-center">
                <div class="inline-flex items-center gap-1.5">
                  <button
                    type="button"
                    @click="emit('open-detail', item, type)"
                    class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition cursor-pointer"
                    aria-label="Lihat rincian inspeksi"
                    title="Lihat Rincian Inspeksi"
                  >
                    <Eye class="w-3.5 h-3.5" />
                  </button>
                  <a
                    :href="`/prl/inspeksi/${type}/${item.id}/pdf`"
                    target="_blank"
                    class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition cursor-pointer"
                    aria-label="Cetak lembar pengawasan PDF"
                    title="Cetak Lembar Pengawasan PDF"
                  >
                    <Printer class="w-3.5 h-3.5" />
                  </a>
                  <button
                    type="button"
                    @click="emit('delete-inspection', item, type)"
                    class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-950/60 text-rose-500 transition cursor-pointer"
                    aria-label="Hapus catatan inspeksi"
                    title="Hapus Catatan"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION -->
      <div
        v-if="dataset?.total > 0"
        class="px-4 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 rounded-b-3xl flex flex-col sm:flex-row items-center justify-between gap-3 text-xs"
      >
        <div class="text-slate-500 dark:text-slate-400 flex items-center gap-2 flex-wrap">
          <span>Menampilkan <strong class="text-slate-700 dark:text-slate-200">{{ dataset.from || 0 }}</strong> - <strong class="text-slate-700 dark:text-slate-200">{{ dataset.to || 0 }}</strong> dari <strong class="text-slate-700 dark:text-slate-200">{{ dataset.total || 0 }}</strong> data</span>
          <span class="text-slate-300 dark:text-slate-600">|</span>
          <div class="flex items-center gap-1.5">
            <span class="text-[11px] text-slate-400">Tampilkan:</span>
            <select
              :value="perPage"
              @change="emit('change-per-page', type, $event.target.value)"
              class="py-1 px-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-[#00A99D]"
            >
              <option :value="10">10 / hal</option>
              <option :value="25">25 / hal</option>
              <option :value="50">50 / hal</option>
              <option :value="100">100 / hal</option>
              <option :value="1000">Semua</option>
            </select>
          </div>
        </div>
        <PaginationBar
          :meta="dataset"
          compact
          :preserve-scroll="false"
          active-class="bg-[#00A99D] text-white font-bold shadow-xs"
        />
      </div>
    </div>
  </div>
</template>
