<script setup>
import { ref, computed, watch, defineAsyncComponent, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import ClinicalSkeleton from '../../Components/ClinicalSkeleton.vue';
import { toast } from 'vue-sonner';
import { useConfirm } from '@/composables/useConfirm';
import axios from 'axios';
import {
  Leaf,
  Utensils,
  Droplets,
  Building2,
  Bug,
  Download,
  Plus,
  ClipboardCheck,
} from 'lucide-vue-next';

const InspeksiTab = defineAsyncComponent(() => import('../SanitasiLingkungan/tabs/InspeksiTab.vue'));
const SiveraTab = defineAsyncComponent(() => import('./tabs/SiveraTab.vue'));
const AddInspeksiModal = defineAsyncComponent(() => import('../SanitasiLingkungan/components/AddInspeksiModal.vue'));
const AddSaranaModal = defineAsyncComponent(() => import('../SanitasiLingkungan/components/AddSaranaModal.vue'));
const DetailInspeksiModal = defineAsyncComponent(() => import('../SanitasiLingkungan/components/DetailInspeksiModal.vue'));
const SiveraInspeksiModal = defineAsyncComponent(() => import('../Sivera/components/InspeksiModal.vue'));
const SiveraPengendalianModal = defineAsyncComponent(() => import('../Sivera/components/PengendalianModal.vue'));
const SiveraDetailModal = defineAsyncComponent(() => import('../Sivera/components/DetailModal.vue'));

const props = defineProps({
  active_tab: { type: String, default: 'tpp' },
  filters: { type: Object, default: () => ({}) },
  wilker_options: { type: Array, default: () => [] },
  pemeriksa_options: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  inspeksi_tpp: { type: Object, default: () => ({ data: [] }) },
  inspeksi_sab: { type: Object, default: () => ({ data: [] }) },
  inspeksi_hsbu: { type: Object, default: () => ({ data: [] }) },
  sivera_list: { type: Object, default: () => ({ data: [] }) },
  sivera_dashboard_metrics: { type: Object, default: () => ({}) },
  sivera_dashboard_data: { type: Object, default: () => ({}) },
  master_tpp: { type: Array, default: () => [] },
  master_sab: { type: Array, default: () => [] },
  master_hsbu: { type: Array, default: () => [] },
});

const { confirm: confirmDialog } = useConfirm();
const isLoading = ref(false);
let unbindStart = null;
let unbindFinish = null;

onMounted(() => {
  unbindStart = router.on('start', () => {
    isLoading.value = true;
  });
  unbindFinish = router.on('finish', () => {
    isLoading.value = false;
  });
});

onUnmounted(() => {
  unbindStart?.();
  unbindFinish?.();
});

const currentTab = ref(props.active_tab || 'tpp');
watch(() => props.active_tab, (val) => {
  if (val) currentTab.value = val;
});

const search = ref(props.filters.search || '');
const selectedWilker = ref(props.filters.wilker || '');
const selectedRisiko = ref(props.filters.tingkat_risiko || '');
const tglMulai = ref(props.filters.tgl_mulai || '');
const tglSelesai = ref(props.filters.tgl_selesai || '');
const selectedTahun = ref(props.filters.tahun || '');
const selectedBulan = ref(props.filters.bulan || '');
const siveraFilterVektor = ref(props.filters.sivera_vektor || 'Dashboard');

const perPageTpp = ref(Number(props.filters.per_page_tpp || 25));
const perPageSab = ref(Number(props.filters.per_page_sab || 25));
const perPageHsbu = ref(Number(props.filters.per_page_hsbu || 25));
const perPageSivera = ref(Number(props.filters.per_page_sivera || 25));

const activeSanitasiDataset = computed(() => {
  if (currentTab.value === 'sab') return props.inspeksi_sab;
  if (currentTab.value === 'hsbu') return props.inspeksi_hsbu;
  return props.inspeksi_tpp;
});

const activeSanitasiPerPage = computed(() => {
  if (currentTab.value === 'sab') return perPageSab.value;
  if (currentTab.value === 'hsbu') return perPageHsbu.value;
  return perPageTpp.value;
});

const buildQueryParams = (overrideTab = null) => ({
  tab: overrideTab || currentTab.value,
  search: search.value || undefined,
  wilker: selectedWilker.value || undefined,
  tingkat_risiko: selectedRisiko.value || undefined,
  tgl_mulai: tglMulai.value || undefined,
  tgl_selesai: tglSelesai.value || undefined,
  tahun: selectedTahun.value || undefined,
  bulan: selectedBulan.value || undefined,
  per_page_tpp: perPageTpp.value,
  per_page_sab: perPageSab.value,
  per_page_hsbu: perPageHsbu.value,
  per_page_sivera: perPageSivera.value,
});

const applyFilter = (overrideTab = null) => {
  const params = buildQueryParams(overrideTab);
  if (params.tab === 'sivera' && siveraFilterVektor.value !== 'Dashboard') {
    params.sivera_vektor = siveraFilterVektor.value;
  }
  router.get('/prl', params, { preserveState: true, replace: true });
};

const switchTab = (tabName) => {
  currentTab.value = tabName;
  applyFilter(tabName);
};

const resetFilter = () => {
  search.value = '';
  selectedWilker.value = '';
  selectedRisiko.value = '';
  tglMulai.value = '';
  tglSelesai.value = '';
  selectedTahun.value = '';
  selectedBulan.value = '';
  router.get('/prl', { tab: currentTab.value }, { preserveState: true, replace: true });
};

const switchSiveraVektor = (vTab) => {
  siveraFilterVektor.value = vTab;
  const params = buildQueryParams('sivera');
  if (vTab !== 'Dashboard') {
    params.sivera_vektor = vTab;
  }
  router.get('/prl', params, { preserveState: true, replace: true });
};

const changePerPage = (type, value) => {
  const p = Number(value) || 25;
  if (type === 'tpp') perPageTpp.value = p;
  else if (type === 'sab') perPageSab.value = p;
  else if (type === 'hsbu') perPageHsbu.value = p;
  else if (type === 'sivera') perPageSivera.value = p;

  const params = buildQueryParams();
  if (type === 'sivera' && siveraFilterVektor.value !== 'Dashboard') {
    params.sivera_vektor = siveraFilterVektor.value;
  }
  router.get('/prl', params, { preserveState: true, replace: true });
};

const filteredSiveraData = computed(() => {
  const data = props.sivera_list?.data || [];
  if (siveraFilterVektor.value === 'Dashboard' || siveraFilterVektor.value === 'Semua Data') {
    return data;
  }
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

// Modals state
const showSanitasiInspeksiModal = ref(false);
const sanitasiModalInitialType = ref('tpp');
const showAddSaranaModal = ref(false);
const addSaranaInitialType = ref('tpp');
const showSiveraInspeksiModal = ref(false);
const siveraInitialVektor = ref('Jentik Aedes sp.');
const siveraEditItem = ref(null);
const showPengendalianModal = ref(false);
const pengendalianItem = ref(null);
const showDetailModal = ref(false);
const detailItem = ref(null);
const detailType = ref('tpp');

const openAddInspeksi = (type = null, vectorSub = null) => {
  const chosenType = type || currentTab.value;
  if (chosenType === 'sivera') {
    siveraEditItem.value = null;
    siveraInitialVektor.value = vectorSub || 'Jentik Aedes sp.';
    showSiveraInspeksiModal.value = true;
    return;
  }
  sanitasiModalInitialType.value = chosenType;
  showSanitasiInspeksiModal.value = true;
};

const switchModalToSivera = () => {
  showSanitasiInspeksiModal.value = false;
  siveraEditItem.value = null;
  siveraInitialVektor.value = siveraFilterVektor.value !== 'Dashboard' && siveraFilterVektor.value !== 'Semua Data'
    ? siveraFilterVektor.value
    : 'Jentik Aedes sp.';
  showSiveraInspeksiModal.value = true;
};

const switchModalToSanitasi = (sanitasiType) => {
  showSiveraInspeksiModal.value = false;
  sanitasiModalInitialType.value = sanitasiType;
  showSanitasiInspeksiModal.value = true;
};

const openAddSarana = (type = 'tpp') => {
  addSaranaInitialType.value = type === 'sivera' ? 'tpp' : type;
  showAddSaranaModal.value = true;
};

const openEditSivera = (item) => {
  siveraEditItem.value = item;
  showSiveraInspeksiModal.value = true;
};

const openPengendalian = (item) => {
  pengendalianItem.value = item;
  showPengendalianModal.value = true;
};

const openDetail = (item, type) => {
  detailItem.value = item;
  detailType.value = type;
  showDetailModal.value = true;
};

const closeDetail = () => {
  showDetailModal.value = false;
  detailItem.value = null;
};

const handleSaved = () => {
  showSanitasiInspeksiModal.value = false;
  showAddSaranaModal.value = false;
  showSiveraInspeksiModal.value = false;
  showPengendalianModal.value = false;
  siveraEditItem.value = null;
  router.reload({ preserveScroll: true });
};

const deleteInspection = async (item, type) => {
  const ok = await confirmDialog({
    title: 'Hapus Catatan?',
    message: `Apakah Anda yakin ingin menghapus catatan ${item.nama_tpp || item.nama_sab || item.nama_gedung || item.jenis_vektor}?`,
    confirmText: 'Ya, Hapus',
    variant: 'danger',
  });
  if (!ok) return;

  try {
    const res = await axios.delete(`/prl/inspeksi/${type}/${item.id}`);
    toast.success(res.data.message || 'Data berhasil dihapus');
    router.reload({ preserveScroll: true });
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menghapus data.');
  }
};
</script>

<template>
  <Head title="Pengendalian Risiko Lingkungan (PRL) &bull; BKK Bandung" />

  <HybridNavbar mode="inertia" currentRoute="prl">
    <main class="clinical-main">
      <!-- ==================== HEADER BAR ==================== -->
      <div class="clinical-header flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-9 h-9 rounded-lg bg-[#002B27] border border-[#00A99D]/40 text-[#99CC33] flex items-center justify-center shrink-0">
            <Leaf class="w-4 h-4" />
          </div>
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <h1 class="text-sm sm:text-[15px] font-bold text-white tracking-tight">
                Pengendalian Risiko Lingkungan (PRL)
              </h1>
              <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-[#002B27]/80 text-[#99DFD6] border border-[#00A99D]/30">
                Pelabuhan &amp; Bandara
              </span>
            </div>
            <p class="text-[11px] text-slate-200/85 truncate mt-0.5">
              Pengawasan TPP, Sarana Air Bersih (SAB), HSBU, serta Surveilans Vektor &amp; Reservoar (SIVERA).
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-1.5 shrink-0">
          <button
            type="button"
            @click="openAddInspeksi()"
            class="px-3 py-1.5 rounded-lg bg-[#00A99D] hover:bg-[#009388] text-xs font-semibold text-white transition-colors flex items-center gap-1.5 cursor-pointer"
          >
            <ClipboardCheck class="w-3.5 h-3.5" />
            <span>+ Input Inspeksi</span>
          </button>

          <button
            type="button"
            @click="openAddSarana('tpp')"
            class="px-2.5 py-1.5 rounded-lg border border-white/15 bg-white/10 hover:bg-white/20 text-xs font-semibold text-white transition-colors flex items-center gap-1 cursor-pointer"
          >
            <Plus class="w-3.5 h-3.5 text-[#99CC33]" />
            <span>+ Sarana</span>
          </button>

          <a
            :href="`/prl/export/${currentTab}`"
            class="px-2.5 py-1.5 rounded-lg border border-white/15 bg-white/10 hover:bg-white/20 text-xs font-semibold text-white transition-colors flex items-center gap-1 cursor-pointer"
            title="Export CSV / Excel"
          >
            <Download class="w-3.5 h-3.5 text-[#99DFD6]" />
            <span>CSV</span>
          </a>
        </div>
      </div>

      <!-- ==================== SUB-TABS NAVIGATION ==================== -->
      <div class="clinical-tabs" role="tablist" aria-label="Kategori Pengendalian Risiko Lingkungan">
        <button
          type="button"
          role="tab"
          :aria-selected="currentTab === 'tpp'"
          @click="switchTab('tpp')"
          class="clinical-tab"
          :class="currentTab === 'tpp' ? 'clinical-tab-active' : 'clinical-tab-inactive'"
        >
          <Utensils class="w-3.5 h-3.5" />
          <span>Tempat Pengelolaan Pangan (TPP)</span>
          <span
            class="px-1.5 py-0.2 rounded text-[10px] font-mono clinical-num"
            :class="currentTab === 'tpp' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
          >{{ stats.total_insp_tpp || 0 }}</span>
        </button>

        <button
          type="button"
          role="tab"
          :aria-selected="currentTab === 'sab'"
          @click="switchTab('sab')"
          class="clinical-tab"
          :class="currentTab === 'sab' ? 'clinical-tab-active' : 'clinical-tab-inactive'"
        >
          <Droplets class="w-3.5 h-3.5" />
          <span>Sarana Air Bersih (SAB)</span>
          <span
            class="px-1.5 py-0.2 rounded text-[10px] font-mono clinical-num"
            :class="currentTab === 'sab' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
          >{{ stats.total_insp_sab || 0 }}</span>
        </button>

        <button
          type="button"
          role="tab"
          :aria-selected="currentTab === 'hsbu'"
          @click="switchTab('hsbu')"
          class="clinical-tab"
          :class="currentTab === 'hsbu' ? 'clinical-tab-active' : 'clinical-tab-inactive'"
        >
          <Building2 class="w-3.5 h-3.5" />
          <span>Higiene Bangunan &amp; Usaha (HSBU)</span>
          <span
            class="px-1.5 py-0.2 rounded text-[10px] font-mono clinical-num"
            :class="currentTab === 'hsbu' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
          >{{ stats.total_insp_hsbu || 0 }}</span>
        </button>

        <button
          type="button"
          role="tab"
          :aria-selected="currentTab === 'sivera'"
          @click="switchTab('sivera')"
          class="clinical-tab"
          :class="currentTab === 'sivera' ? 'clinical-tab-active' : 'clinical-tab-inactive'"
        >
          <Bug class="w-3.5 h-3.5" />
          <span>Surveilans Vektor (SIVERA)</span>
          <span
            class="px-1.5 py-0.2 rounded text-[10px] font-mono clinical-num"
            :class="currentTab === 'sivera' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
          >{{ stats.sivera_total || 0 }}</span>
        </button>
      </div>

      <p class="sr-only" role="status" aria-live="polite">
        {{ isLoading ? 'Memuat data PRL...' : `Tab aktif: ${currentTab.toUpperCase()}` }}
      </p>
      <ClinicalSkeleton v-if="isLoading" variant="table" :rows="6" :cols="6" />

      <!-- ==================== TAB CONTENT ==================== -->
      <InspeksiTab
        v-if="currentTab !== 'sivera'"
        :type="currentTab"
        :dataset="activeSanitasiDataset"
        :per-page="activeSanitasiPerPage"
        :wilker-options="wilker_options"
        v-model:search="search"
        v-model:selectedWilker="selectedWilker"
        v-model:selectedRisiko="selectedRisiko"
        v-model:tglMulai="tglMulai"
        v-model:tglSelesai="tglSelesai"
        @apply-filter="applyFilter()"
        @reset-filter="resetFilter"
        @change-per-page="changePerPage"
        @open-add-inspeksi="openAddInspeksi"
        @open-add-sarana="openAddSarana"
        @open-detail="openDetail"
        @delete-inspection="deleteInspection"
      />

      <SiveraTab
        v-else
        :sivera-filter-vektor="siveraFilterVektor"
        v-model:search="search"
        v-model:selectedWilker="selectedWilker"
        v-model:selectedRisiko="selectedRisiko"
        v-model:tglMulai="tglMulai"
        v-model:tglSelesai="tglSelesai"
        :wilker-options="wilker_options"
        :sivera-dashboard-data="sivera_dashboard_data"
        :filtered-sivera-data="filteredSiveraData"
        :sivera-list="sivera_list"
        :per-page-sivera="perPageSivera"
        @switch-vektor="switchSiveraVektor"
        @apply-filter="applyFilter('sivera')"
        @reset-filter="resetFilter"
        @open-add-inspeksi="openAddInspeksi"
        @open-pengendalian="openPengendalian"
        @edit-sivera="openEditSivera"
        @open-detail="openDetail"
        @delete-inspection="deleteInspection"
        @change-per-page="changePerPage"
      />

      <!-- ==================== ASYNC MODALS ==================== -->
      <AddInspeksiModal
        v-if="showSanitasiInspeksiModal"
        :open="showSanitasiInspeksiModal"
        :initial-type="sanitasiModalInitialType"
        api-prefix="/prl"
        :allow-sivera="true"
        :wilker-options="wilker_options"
        :pemeriksa-options="pemeriksa_options"
        :master-tpp="master_tpp"
        :master-sab="master_sab"
        :master-hsbu="master_hsbu"
        @switch-sivera="switchModalToSivera"
        @close="showSanitasiInspeksiModal = false"
        @saved="handleSaved"
      />

      <AddSaranaModal
        v-if="showAddSaranaModal"
        :open="showAddSaranaModal"
        :initial-type="addSaranaInitialType"
        :wilker-options="wilker_options"
        @close="showAddSaranaModal = false"
        @saved="handleSaved"
      />

      <DetailInspeksiModal
        v-if="showDetailModal && detailType !== 'sivera'"
        :open="Boolean(showDetailModal && detailItem)"
        :item="detailItem"
        :detail-type="detailType"
        @close="closeDetail"
      />

      <SiveraInspeksiModal
        v-if="showSiveraInspeksiModal"
        :open="showSiveraInspeksiModal"
        :wilker-options="wilker_options"
        :initial-vektor="siveraInitialVektor"
        :edit-item="siveraEditItem"
        :allow-sanitasi-tabs="true"
        @switch-sanitasi="switchModalToSanitasi"
        @close="showSiveraInspeksiModal = false; siveraEditItem = null"
        @saved="handleSaved"
      />

      <SiveraPengendalianModal
        v-if="showPengendalianModal"
        :open="showPengendalianModal"
        :item="pengendalianItem"
        @close="showPengendalianModal = false"
        @saved="handleSaved"
      />

      <SiveraDetailModal
        v-if="showDetailModal && detailType === 'sivera'"
        :open="Boolean(showDetailModal && detailItem)"
        :item="detailItem"
        detail-type="sivera"
        @close="closeDetail"
      />
    </main>
  </HybridNavbar>
</template>
