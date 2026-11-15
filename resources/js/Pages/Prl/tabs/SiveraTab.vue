<script setup>
import { defineAsyncComponent, computed } from 'vue';
import { useDebouncedFn } from '@/composables/useDebouncedFn';
import {
  Bug,
  Plus,
  Activity,
  Search,
  Filter,
  RotateCcw,
  Sparkles,
} from 'lucide-vue-next';

const SiveraDashboardTab = defineAsyncComponent(() => import('../../Sivera/tabs/DashboardTab.vue'));
const SiveraRecordsTab = defineAsyncComponent(() => import('../../Sivera/tabs/RecordsTab.vue'));

const props = defineProps({
  siveraFilterVektor: { type: String, default: 'Dashboard' },
  search: { type: String, default: '' },
  selectedWilker: { type: String, default: '' },
  selectedRisiko: { type: String, default: '' },
  tglMulai: { type: String, default: '' },
  tglSelesai: { type: String, default: '' },
  wilkerOptions: { type: Array, default: () => [] },
  siveraDashboardData: { type: Object, default: () => ({}) },
  filteredSiveraData: { type: Array, default: () => [] },
  siveraList: { type: Object, default: () => ({ data: [] }) },
  perPageSivera: { type: [Number, String], default: 25 },
});

const emit = defineEmits([
  'switch-vektor',
  'update:search',
  'update:selectedWilker',
  'update:selectedRisiko',
  'update:tglMulai',
  'update:tglSelesai',
  'apply-filter',
  'reset-filter',
  'open-add-inspeksi',
  'open-pengendalian',
  'edit-sivera',
  'open-detail',
  'delete-inspection',
  'change-per-page',
]);

const debouncedFilter = useDebouncedFn(() => {
  emit('apply-filter');
}, 350);

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

const hasActiveFilters = computed(() => Boolean(props.search || props.selectedWilker || props.selectedRisiko || props.tglMulai || props.tglSelesai));

const siveraDashData = computed(() => {
  if (props.siveraDashboardData && props.siveraDashboardData.summary) {
    return props.siveraDashboardData;
  }
  return {
    labels: [],
    summary: { total_surveilans: 0, kepatuhan_pct: 100 },
  };
});

const vektorTabs = [
  'Dashboard',
  'Semua Data',
  'Jentik Aedes',
  'Lalat',
  'Kecoa',
  'Tikus',
  'Pinjal',
  'Jentik Anopheles',
  'Nyamuk Anopheles',
];
</script>

<template>
  <div class="space-y-4">
    <div class="clinical-surface p-4 sm:p-5">
      <!-- Header SIVERA -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
        <div>
          <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <Bug class="w-4 h-4 text-indigo-500" />
            <span>Surveilans Vektor dan Reservoar (SIVERA)</span>
          </h2>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pemantauan 7 jenis vektor &amp; binatang pembawa penyakit (Jentik Aedes, Lalat, Kecoa, Tikus, Pinjal, Anopheles) di perimeter dan buffer.</p>
        </div>
        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="emit('open-add-inspeksi', 'sivera', siveraFilterVektor !== 'Semua Data' && siveraFilterVektor !== 'Dashboard' ? siveraFilterVektor : 'Jentik Aedes sp.')"
            class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:brightness-105 text-white text-xs font-bold flex items-center gap-1.5 cursor-pointer shadow-xs"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>+ Catat Data Surveilans {{ siveraFilterVektor !== 'Semua Data' && siveraFilterVektor !== 'Dashboard' ? siveraFilterVektor : 'Baru' }}</span>
          </button>
        </div>
      </div>

      <!-- SIVERA Sub-Vektor Filter Navigation Pills -->
      <div class="flex items-center gap-1.5 overflow-x-auto py-3 border-b border-slate-100 dark:border-slate-800 text-xs">
        <button
          v-for="vTab in vektorTabs"
          :key="vTab"
          type="button"
          @click="emit('switch-vektor', vTab)"
          class="px-3 py-1.5 rounded-full font-semibold transition shrink-0 cursor-pointer flex items-center gap-1.5"
          :class="siveraFilterVektor === vTab ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
        >
          <Activity v-if="vTab === 'Dashboard'" class="w-3.5 h-3.5" />
          <span>{{ vTab }}</span>
        </button>
      </div>

      <!-- FILTER KHUSUS MENU SIVERA -->
      <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/60 my-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
          <div class="lg:col-span-4">
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Cari Lokasi / Titik Survei / Parameter SIVERA</label>
            <div class="relative">
              <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
              <input
                type="text"
                :value="search"
                @input="onSearchInput($event.target.value)"
                @keyup.enter="triggerImmediateFilter"
                placeholder="Ketik lokasi, titik survei, parameter..."
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
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Status Baku Mutu</label>
            <select
              :value="selectedRisiko"
              @change="emit('update:selectedRisiko', $event.target.value); triggerImmediateFilter()"
              class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
            >
              <option value="">Semua Status</option>
              <option value="Memenuhi Syarat">Memenuhi Syarat (MS)</option>
              <option value="Di Atas Baku Mutu">Di Atas Baku Mutu (TMS)</option>
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
              aria-label="Terapkan filter SIVERA"
              title="Terapkan Filter"
            >
              <Filter class="w-4 h-4" />
            </button>
            <button
              type="button"
              @click="handleReset"
              class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-300 transition cursor-pointer"
              aria-label="Reset semua filter SIVERA"
              title="Reset Semua Filter"
            >
              <RotateCcw class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Active filter pills -->
        <div v-if="hasActiveFilters" class="mt-3 pt-2.5 border-t border-slate-200/60 dark:border-slate-700/50 flex items-center gap-2 flex-wrap text-xs">
          <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Filter Aktif:</span>
          <span v-if="search" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/50 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-[11px]">
            Kata Kunci: "{{ search }}"
            <button type="button" @click="emit('update:search', ''); triggerImmediateFilter()" aria-label="Hapus filter kata kunci" class="hover:text-rose-600 font-bold cursor-pointer">&times;</button>
          </span>
          <span v-if="selectedWilker" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-teal-50 dark:bg-teal-950/50 text-[#005851] dark:text-teal-300 border border-teal-200 dark:border-teal-800 text-[11px]">
            Wilker: {{ selectedWilker }}
            <button type="button" @click="emit('update:selectedWilker', ''); triggerImmediateFilter()" aria-label="Hapus filter wilker" class="hover:text-rose-600 font-bold cursor-pointer">&times;</button>
          </span>
          <span v-if="selectedRisiko" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-[11px]">
            Status: {{ selectedRisiko }}
            <button type="button" @click="emit('update:selectedRisiko', ''); triggerImmediateFilter()" aria-label="Hapus filter status" class="hover:text-rose-600 font-bold cursor-pointer">&times;</button>
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

      <!-- 1. SIVERA DASHBOARD VIEW -->
      <div v-if="siveraFilterVektor === 'Dashboard'" class="space-y-6 mt-4">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 border border-teal-500/30 text-white p-6 md:p-7 shadow-xl">
          <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
          <div class="absolute -left-12 -top-12 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

          <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div class="max-w-2xl">
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/20 border border-teal-400/30 text-teal-300 text-xs font-semibold mb-3">
                <Sparkles class="w-3.5 h-3.5" />
                <span>Sistem Informasi Surveilans Vektor &amp; Reservoar (SIVERA)</span>
              </div>
              <h2 class="text-xl md:text-2xl font-black text-white tracking-tight">
                Dashboard Surveilans Vektor dan Reservoar
              </h2>
              <p class="text-xs md:text-sm text-slate-300 mt-1 leading-relaxed">
                Ringkasan surveilans kepadatan jentik, lalat, kecoa, tikus, pinjal, dan anopheles di wilayah perimeter &amp; buffer dalam tampilan analitik interaktif, terpadu, dan profesional.
              </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
              <div class="px-3.5 py-2 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 text-center min-w-[110px]">
                <div class="text-[10px] text-teal-200 uppercase tracking-wider font-bold">Wilker Terpantau</div>
                <div class="text-lg font-black text-white font-mono mt-0.5">{{ siveraDashData.labels.length }}</div>
              </div>
              <div class="px-3.5 py-2 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 text-center min-w-[110px]">
                <div class="text-[10px] text-teal-200 uppercase tracking-wider font-bold">Total Pemantauan</div>
                <div class="text-lg font-black text-white font-mono mt-0.5">{{ siveraDashData.summary.total_surveilans }}</div>
              </div>
              <div class="px-3.5 py-2 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 text-center min-w-[120px]">
                <div class="text-[10px] text-emerald-300 uppercase tracking-wider font-bold">Baku Mutu Aman</div>
                <div class="text-lg font-black text-emerald-300 font-mono mt-0.5">{{ siveraDashData.summary.kepatuhan_pct }}%</div>
              </div>
            </div>
          </div>
        </div>

        <SiveraDashboardTab
          :dashboard-data="siveraDashboardData"
          @switch-vektor="emit('switch-vektor', $event)"
        />
      </div>

      <!-- 2. SIVERA DATA TABLE VIEW -->
      <SiveraRecordsTab
        v-else
        :items="filteredSiveraData"
        :meta="siveraList"
        :per-page="perPageSivera"
        @pengendalian="emit('open-pengendalian', $event)"
        @edit="emit('edit-sivera', $event)"
        @detail="emit('open-detail', $event, 'sivera')"
        @delete="emit('delete-inspection', $event, 'sivera')"
        @change-per-page="emit('change-per-page', 'sivera', $event)"
      />
    </div>
  </div>
</template>
