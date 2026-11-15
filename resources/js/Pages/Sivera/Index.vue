<script setup>
import { ref, computed, defineAsyncComponent } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useDebouncedFn } from '@/composables/useDebouncedFn';
import { Bug, Search, Filter, RotateCcw, Plus, Activity } from 'lucide-vue-next';

const DashboardTab = defineAsyncComponent(() => import('./tabs/DashboardTab.vue'));
const RecordsTab = defineAsyncComponent(() => import('./tabs/RecordsTab.vue'));
const InspeksiModal = defineAsyncComponent(() => import('./components/InspeksiModal.vue'));
const PengendalianModal = defineAsyncComponent(() => import('./components/PengendalianModal.vue'));
const DetailModal = defineAsyncComponent(() => import('./components/DetailModal.vue'));

const props = defineProps({
  filters: {
    type: Object,
    default: () => ({}),
  },
  wilker_options: {
    type: Array,
    default: () => [],
  },
  pemeriksa_options: {
    type: Array,
    default: () => [],
  },
  sivera_dashboard_metrics: {
    type: Object,
    default: () => ({}),
  },
  sivera_dashboard_data: {
    type: Object,
    default: () => ({
      labels: [],
      summary: {},
      charts: {
        kecoa: {},
        lalat_status: { MS: [], TMS: [] },
        jentik_kontainer: [],
        spesies_tikus: {},
      },
      wilker_detail: [],
    }),
  },
  sivera_list: {
    type: Object,
    default: () => ({ data: [] }),
  },
});

const siveraFilterVektor = ref(props.filters.sivera_vektor || 'Dashboard');
const perPageSivera = ref(props.filters.per_page_sivera || 25);

const search = ref(props.filters.search || '');
const selectedWilker = ref(props.filters.wilker || '');
const selectedRisiko = ref(props.filters.tingkat_risiko || '');
const tglMulai = ref(props.filters.tgl_mulai || '');
const tglSelesai = ref(props.filters.tgl_selesai || '');
const selectedTahun = ref(props.filters.tahun || '');
const selectedBulan = ref(props.filters.bulan || '');

const buildFilterQuery = () => ({
  sivera_vektor: siveraFilterVektor.value !== 'Dashboard' ? siveraFilterVektor.value : undefined,
  search: search.value || undefined,
  wilker: selectedWilker.value || undefined,
  tingkat_risiko: selectedRisiko.value || undefined,
  tgl_mulai: tglMulai.value || undefined,
  tgl_selesai: tglSelesai.value || undefined,
  tahun: selectedTahun.value || undefined,
  bulan: selectedBulan.value || undefined,
  per_page_sivera: perPageSivera.value !== 25 ? perPageSivera.value : undefined,
});

const applyFilter = () => {
  router.get('/sivera', buildFilterQuery(), { preserveState: true, replace: true });
};

const debouncedApplyFilter = useDebouncedFn(applyFilter, 350);

const applyFilterImmediate = () => {
  debouncedApplyFilter.cancel();
  applyFilter();
};

const switchSiveraVektor = (vTab) => {
  debouncedApplyFilter.cancel();
  siveraFilterVektor.value = vTab;
  router.get('/sivera', {
    ...buildFilterQuery(),
    per_page_sivera: perPageSivera.value || undefined,
  }, { preserveState: true, replace: true });
};

const resetFilter = () => {
  debouncedApplyFilter.cancel();
  search.value = '';
  selectedWilker.value = '';
  selectedRisiko.value = '';
  tglMulai.value = '';
  tglSelesai.value = '';
  selectedTahun.value = '';
  selectedBulan.value = '';
  siveraFilterVektor.value = 'Dashboard';
  router.get('/sivera', {}, { preserveState: true, replace: true });
};

const changePerPage = (val) => {
  debouncedApplyFilter.cancel();
  perPageSivera.value = Number(val);
  router.get('/sivera', {
    ...buildFilterQuery(),
    per_page_sivera: perPageSivera.value,
  }, { preserveState: true, replace: true });
};

const filteredSiveraData = computed(() => {
  const data = props.sivera_list?.data || [];
  if (siveraFilterVektor.value === 'Dashboard' || siveraFilterVektor.value === 'Semua Data') return data;
  const filter = siveraFilterVektor.value.toLowerCase();
  return data.filter((item) => {
    const jv = (item.jenis_vektor || '').toLowerCase();
    if (filter.includes('anopheles')) {
      if (filter.includes('jentik')) return jv.includes('anopheles') && jv.includes('jentik');
      if (filter.includes('nyamuk')) return jv.includes('anopheles') && jv.includes('nyamuk');
      return jv.includes('anopheles');
    }
    return jv.includes(filter) || filter.includes(jv);
  });
});

// Modal states
const showAddInspeksiModal = ref(false);
const initialModalVektor = ref('Jentik Aedes sp.');
const editingItem = ref(null);

const openAddInspeksi = (vectorSub = 'Jentik Aedes sp.') => {
  editingItem.value = null;
  initialModalVektor.value = vectorSub || 'Jentik Aedes sp.';
  showAddInspeksiModal.value = true;
};

const openEditInspeksi = (item) => {
  editingItem.value = item;
  initialModalVektor.value = item?.jenis_vektor || 'Jentik Aedes sp.';
  showAddInspeksiModal.value = true;
};

const closeAddInspeksi = () => {
  showAddInspeksiModal.value = false;
  editingItem.value = null;
};

const handleInspeksiSaved = () => {
  closeAddInspeksi();
  router.reload();
};

const showPengendalianModal = ref(false);
const pengendalianItem = ref(null);

const openPengendalian = (item) => {
  pengendalianItem.value = item;
  showPengendalianModal.value = true;
};

const closePengendalian = () => {
  showPengendalianModal.value = false;
  pengendalianItem.value = null;
};

const handlePengendalianSaved = () => {
  closePengendalian();
  router.reload();
};

const showDetailModal = ref(false);
const detailItem = ref(null);

const openDetail = (item) => {
  detailItem.value = item;
  showDetailModal.value = true;
};

const closeDetail = () => {
  showDetailModal.value = false;
  detailItem.value = null;
};

const { confirmDialog } = useConfirm();

const deleteInspection = async (item) => {
  const ok = await confirmDialog({
    title: 'Hapus Catatan SIVERA?',
    message: `Apakah Anda yakin ingin menghapus catatan ${item.jenis_vektor} di ${item.wilker}?`,
    confirmText: 'Ya, Hapus',
    variant: 'danger',
  });
  if (!ok) return;
  try {
    const res = await axios.delete(`/sivera/${item.id}`);
    toast.success(res.data.message || 'Data surveilans berhasil dihapus');
    router.reload();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menghapus data.');
  }
};

const vectorTabs = [
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
  <Head title="Surveilans Vektor dan Reservoar (SIVERA) &bull; BKK Bandung" />

  <HybridNavbar mode="inertia" currentRoute="sivera">
    <main class="clinical-main">
      <!-- ==================== SIVERA HEADER BAR ==================== -->
      <div class="clinical-header flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-9 h-9 rounded-lg bg-[#002B27] border border-[#00A99D]/40 text-[#99CC33] flex items-center justify-center shrink-0">
            <Bug class="w-4 h-4" />
          </div>
          <div class="min-w-0">
            <h1 class="text-sm sm:text-[15px] font-bold text-white tracking-tight">
              Surveilans Vektor dan Reservoar (SIVERA)
            </h1>
            <p class="text-[11px] text-slate-200/85 truncate mt-0.5">
              Pemantauan 7 jenis vektor &amp; binatang pembawa penyakit di area perimeter dan buffer BKK Bandung.
            </p>
          </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <button
            type="button"
            @click="openAddInspeksi(siveraFilterVektor !== 'Semua Data' && siveraFilterVektor !== 'Dashboard' ? siveraFilterVektor : 'Jentik Aedes sp.')"
            class="px-3 py-1.5 rounded-lg bg-[#00A99D] hover:bg-[#009388] text-white text-xs font-semibold flex items-center gap-1.5 cursor-pointer transition-colors"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>+ Catat {{ siveraFilterVektor !== 'Semua Data' && siveraFilterVektor !== 'Dashboard' ? siveraFilterVektor : 'Data Surveilans' }}</span>
          </button>
        </div>
      </div>

      <!-- ==================== SIVERA TAB / SYSTEM CONTENT ==================== -->
      <div class="space-y-3">
        <div class="clinical-surface p-4">
          <!-- SIVERA Sub-Vektor Filter Navigation Pills -->
          <div class="flex items-center gap-1 overflow-x-auto pb-2.5 border-b border-slate-200/80 dark:border-slate-700/80 text-xs">
            <button
              v-for="vTab in vectorTabs"
              :key="vTab"
              type="button"
              @click="switchSiveraVektor(vTab)"
              class="px-2.5 py-1 rounded-lg font-semibold transition-colors shrink-0 cursor-pointer flex items-center gap-1.5"
              :class="siveraFilterVektor === vTab ? 'clinical-tab-active' : 'clinical-tab-inactive'"
            >
              <Activity v-if="vTab === 'Dashboard'" class="w-3.5 h-3.5" />
              <span>{{ vTab }}</span>
            </button>
          </div>

          <!-- FILTER KHUSUS MENU SIVERA -->
          <div class="p-3 rounded-lg bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700/80 my-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5 items-end">
              <!-- Search -->
              <div class="lg:col-span-4">
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Cari Lokasi / Titik / Parameter</label>
                <div class="relative">
                  <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
                  <input
                    type="text"
                    v-model="search"
                    @input="debouncedApplyFilter()"
                    @keyup.enter="applyFilterImmediate()"
                    placeholder="Ketik lokasi, titik survei, parameter..."
                    class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-[#00A99D] focus:ring-2 focus:ring-[#00A99D]/15 focus:outline-none"
                  />
                </div>
              </div>

              <!-- Wilker -->
              <div class="lg:col-span-2">
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Wilayah Kerja</label>
                <select
                  v-model="selectedWilker"
                  @change="applyFilterImmediate()"
                  class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:border-[#00A99D] focus:outline-none"
                >
                  <option value="">Semua Wilker</option>
                  <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
                </select>
              </div>

              <!-- Status Baku Mutu -->
              <div class="lg:col-span-2">
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Status Baku Mutu</label>
                <select
                  v-model="selectedRisiko"
                  @change="applyFilterImmediate()"
                  class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:border-[#00A99D] focus:outline-none"
                >
                  <option value="">Semua Status</option>
                  <option value="Memenuhi Syarat">Memenuhi Syarat (MS)</option>
                  <option value="Di Atas Baku Mutu">Di Atas Baku Mutu (TMS)</option>
                </select>
              </div>

              <!-- Tanggal Mulai -->
              <div class="lg:col-span-2">
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Tanggal Mulai</label>
                <input
                  type="date"
                  v-model="tglMulai"
                  @change="applyFilterImmediate()"
                  class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:border-[#00A99D] focus:outline-none"
                />
              </div>

              <!-- Tanggal Selesai & Actions -->
              <div class="lg:col-span-2 flex items-end gap-1.5">
                <div class="flex-1">
                  <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Selesai</label>
                  <input
                    type="date"
                    v-model="tglSelesai"
                    @change="applyFilterImmediate()"
                    class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                  />
                </div>
                <button
                  type="button"
                  @click="applyFilterImmediate()"
                  aria-label="Terapkan Filter Surveilans"
                  title="Terapkan Filter"
                  class="p-2 rounded-xl bg-[#005851] hover:bg-[#004842] text-white transition cursor-pointer shadow-xs"
                >
                  <Filter class="w-4 h-4" />
                </button>
                <button
                  type="button"
                  @click="resetFilter()"
                  aria-label="Reset Semua Filter"
                  title="Reset Semua Filter"
                  class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-300 transition cursor-pointer"
                >
                  <RotateCcw class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Active filter pills -->
            <div v-if="search || selectedWilker || selectedRisiko || tglMulai || tglSelesai" class="mt-3 pt-2.5 border-t border-slate-200/60 dark:border-slate-700/50 flex items-center gap-2 flex-wrap text-xs">
              <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Filter Aktif:</span>
              <span v-if="search" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-800 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800 text-[11px]">
                Kata Kunci: "{{ search }}"
                <button type="button" @click="search = ''; applyFilterImmediate()" aria-label="Hapus filter kata kunci" title="Hapus filter kata kunci" class="hover:text-rose-600 font-bold cursor-pointer">&times;</button>
              </span>
              <span v-if="selectedWilker" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-teal-50 text-[#005851] border border-teal-200 dark:bg-teal-950/40 dark:text-teal-300 dark:border-teal-800 text-[11px]">
                Wilker: {{ selectedWilker }}
                <button type="button" @click="selectedWilker = ''; applyFilterImmediate()" aria-label="Hapus filter wilayah kerja" title="Hapus filter wilayah kerja" class="hover:text-rose-600 font-bold cursor-pointer">&times;</button>
              </span>
              <span v-if="selectedRisiko" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800 text-[11px]">
                Status: {{ selectedRisiko }}
                <button type="button" @click="selectedRisiko = ''; applyFilterImmediate()" aria-label="Hapus filter status baku mutu" title="Hapus filter status baku mutu" class="hover:text-rose-600 font-bold cursor-pointer">&times;</button>
              </span>
              <span v-if="tglMulai || tglSelesai" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800 text-[11px]">
                Periode: {{ tglMulai || '...' }} s/d {{ tglSelesai || '...' }}
                <button type="button" @click="tglMulai = ''; tglSelesai = ''; applyFilterImmediate()" aria-label="Hapus filter periode tanggal" title="Hapus filter periode tanggal" class="hover:text-rose-600 font-bold cursor-pointer">&times;</button>
              </span>
              <button type="button" @click="resetFilter()" class="text-[11px] text-rose-600 hover:underline font-semibold ml-auto cursor-pointer">
                Reset Semua
              </button>
            </div>
          </div>

          <DashboardTab
            v-if="siveraFilterVektor === 'Dashboard'"
            :dashboard-data="sivera_dashboard_data"
            @switch-vektor="switchSiveraVektor"
          />

          <RecordsTab
            v-else
            :items="filteredSiveraData"
            :meta="sivera_list"
            :per-page="perPageSivera"
            @pengendalian="openPengendalian"
            @edit="openEditInspeksi"
            @detail="openDetail"
            @delete="deleteInspection"
            @change-per-page="changePerPage"
          />
        </div>
      </div>

      <InspeksiModal
        v-if="showAddInspeksiModal"
        :open="showAddInspeksiModal"
        :wilker-options="wilker_options"
        :initial-vektor="initialModalVektor"
        :edit-item="editingItem"
        @close="closeAddInspeksi"
        @saved="handleInspeksiSaved"
      />

      <PengendalianModal
        v-if="showPengendalianModal"
        :open="showPengendalianModal"
        :item="pengendalianItem"
        @close="closePengendalian"
        @saved="handlePengendalianSaved"
      />

      <DetailModal
        v-if="showDetailModal"
        :open="showDetailModal"
        :item="detailItem"
        detail-type="sivera"
        @close="closeDetail"
      />
    </main>
  </HybridNavbar>
</template>
