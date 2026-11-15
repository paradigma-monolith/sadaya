<script setup>
import { ref, defineAsyncComponent } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { useConfirm } from '@/composables/useConfirm';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import {
  Leaf,
  Utensils,
  Droplets,
  Building2,
  Download,
  Plus,
  ClipboardCheck,
} from 'lucide-vue-next';

const InspeksiTab = defineAsyncComponent(() => import('./tabs/InspeksiTab.vue'));
const AddInspeksiModal = defineAsyncComponent(() => import('./components/AddInspeksiModal.vue'));
const AddSaranaModal = defineAsyncComponent(() => import('./components/AddSaranaModal.vue'));
const DetailInspeksiModal = defineAsyncComponent(() => import('./components/DetailInspeksiModal.vue'));

const props = defineProps({
  active_tab: { type: String, default: 'tpp' },
  filters: { type: Object, default: () => ({}) },
  wilker_options: { type: Array, default: () => [] },
  pemeriksa_options: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  inspeksi_tpp: { type: Object, default: () => ({ data: [] }) },
  inspeksi_sab: { type: Object, default: () => ({ data: [] }) },
  inspeksi_hsbu: { type: Object, default: () => ({ data: [] }) },
  master_tpp: { type: Array, default: () => [] },
  master_sab: { type: Array, default: () => [] },
  master_hsbu: { type: Array, default: () => [] },
});

const { confirmDialog } = useConfirm();

const currentTab = ref(
  props.active_tab === 'overview' || !props.active_tab || props.active_tab === 'sivera'
    ? 'tpp'
    : props.active_tab
);

const perPageTpp = ref(props.filters.per_page_tpp || 25);
const perPageSab = ref(props.filters.per_page_sab || 25);
const perPageHsbu = ref(props.filters.per_page_hsbu || 25);

const search = ref(props.filters.search || '');
const selectedWilker = ref(props.filters.wilker || '');
const selectedRisiko = ref(props.filters.tingkat_risiko || '');
const tglMulai = ref(props.filters.tgl_mulai || '');
const tglSelesai = ref(props.filters.tgl_selesai || '');
const selectedTahun = ref(props.filters.tahun || '');
const selectedBulan = ref(props.filters.bulan || '');

const showDetailModal = ref(false);
const detailItem = ref(null);
const detailType = ref('');

const showAddSaranaModal = ref(false);
const addSaranaType = ref('tpp');

const showAddInspeksiModal = ref(false);
const addInspeksiType = ref('tpp');

const applyFilter = (targetTab = currentTab.value) => {
  router.get('/sanitasi-lingkungan', {
    tab: targetTab,
    search: search.value || undefined,
    wilker: selectedWilker.value || undefined,
    tingkat_risiko: selectedRisiko.value || undefined,
    tgl_mulai: tglMulai.value || undefined,
    tgl_selesai: tglSelesai.value || undefined,
    tahun: selectedTahun.value || undefined,
    bulan: selectedBulan.value || undefined,
    per_page_tpp: perPageTpp.value !== 25 ? perPageTpp.value : undefined,
    per_page_sab: perPageSab.value !== 25 ? perPageSab.value : undefined,
    per_page_hsbu: perPageHsbu.value !== 25 ? perPageHsbu.value : undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const switchTab = (tab) => {
  currentTab.value = tab;
  applyFilter(tab);
};

const resetFilter = () => {
  search.value = '';
  selectedWilker.value = '';
  selectedRisiko.value = '';
  tglMulai.value = '';
  tglSelesai.value = '';
  selectedTahun.value = '';
  selectedBulan.value = '';
  router.get('/sanitasi-lingkungan', { tab: currentTab.value }, { preserveState: true, replace: true });
};

const changePerPage = (type, val) => {
  const p = Number(val);
  const params = {
    tab: currentTab.value,
    search: search.value || undefined,
    wilker: selectedWilker.value || undefined,
    tingkat_risiko: selectedRisiko.value || undefined,
    tgl_mulai: tglMulai.value || undefined,
    tgl_selesai: tglSelesai.value || undefined,
    tahun: selectedTahun.value || undefined,
    bulan: selectedBulan.value || undefined,
  };
  if (type === 'tpp') {
    perPageTpp.value = p;
    params.per_page_tpp = p;
  } else if (type === 'sab') {
    perPageSab.value = p;
    params.per_page_sab = p;
  } else if (type === 'hsbu') {
    perPageHsbu.value = p;
    params.per_page_hsbu = p;
  }
  router.get('/sanitasi-lingkungan', params, {
    preserveState: true,
    replace: true,
  });
};

const openAddInspeksi = (type = null) => {
  addInspeksiType.value = type || currentTab.value || 'tpp';
  showAddInspeksiModal.value = true;
};

const openAddSarana = (type = 'tpp') => {
  addSaranaType.value = type;
  showAddSaranaModal.value = true;
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

const deleteInspection = async (item, type) => {
  const ok = await confirmDialog({
    title: 'Hapus Catatan Inspeksi?',
    message: `Apakah Anda yakin ingin menghapus catatan ${item.nama_tpp || item.nama_sab || item.nama_gedung}?`,
    confirmText: 'Ya, Hapus',
    variant: 'danger',
  });
  if (!ok) return;
  try {
    const res = await axios.delete(`/sanitasi-lingkungan/inspeksi/${type}/${item.id}`);
    toast.success(res.data.message || 'Data berhasil dihapus');
    router.reload();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menghapus data.');
  }
};
</script>

<template>
  <Head title="Sanitasi Lingkungan (TPP, SAB, HSBU) &bull; BKK Bandung" />

  <HybridNavbar mode="inertia" currentRoute="sanitasi">
    <main class="clinical-main">
      <!-- HEADER BAR -->
      <div class="clinical-header flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-9 h-9 rounded-lg bg-[#002B27] border border-[#00A99D]/40 text-[#99CC33] flex items-center justify-center shrink-0">
            <ClipboardCheck class="w-4 h-4" />
          </div>
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <h1 class="text-sm sm:text-[15px] font-bold text-white tracking-tight">
                Sanitasi Lingkungan (TPP, SAB &amp; HSBU)
              </h1>
              <span class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-[#002B27]/80 text-[#99DFD6] border border-[#00A99D]/30">
                <Leaf class="w-3 h-3 text-[#99CC33]" />
                <span>Kesehatan Lingkungan</span>
              </span>
            </div>
            <p class="text-[11px] text-slate-200/85 truncate mt-0.5">
              Pengawasan Tempat Pengelolaan Pangan (TPP), Sarana Air Bersih (SAB) &amp; Higiene Bangunan Usaha (HSBU).
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
            :href="`/sanitasi-lingkungan/export/${currentTab}`"
            class="px-2.5 py-1.5 rounded-lg border border-white/15 bg-white/10 hover:bg-white/20 text-xs font-semibold text-white transition-colors flex items-center gap-1 cursor-pointer"
            title="Export CSV / Excel"
          >
            <Download class="w-3.5 h-3.5 text-[#99DFD6]" />
            <span>CSV</span>
          </a>
        </div>
      </div>

      <!-- SUB-TABS NAVIGATION -->
      <div class="clinical-tabs">
        <button
          type="button"
          @click="switchTab('tpp')"
          class="clinical-tab"
          :class="currentTab === 'tpp' ? 'clinical-tab-active' : 'clinical-tab-inactive'"
        >
          <Utensils class="w-3.5 h-3.5" />
          <span>Tempat Pengelolaan Pangan (TPP)</span>
          <span class="px-1.5 py-0.2 rounded text-[10px] font-mono clinical-num" :class="currentTab === 'tpp' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'">{{ stats.total_insp_tpp || inspeksi_tpp?.total || 0 }}</span>
        </button>

        <button
          type="button"
          @click="switchTab('sab')"
          class="clinical-tab"
          :class="currentTab === 'sab' ? 'clinical-tab-active' : 'clinical-tab-inactive'"
        >
          <Droplets class="w-3.5 h-3.5" />
          <span>Sarana Air Bersih (SAB)</span>
          <span class="px-1.5 py-0.2 rounded text-[10px] font-mono clinical-num" :class="currentTab === 'sab' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'">{{ stats.total_insp_sab || inspeksi_sab?.total || 0 }}</span>
        </button>

        <button
          type="button"
          @click="switchTab('hsbu')"
          class="clinical-tab"
          :class="currentTab === 'hsbu' ? 'clinical-tab-active' : 'clinical-tab-inactive'"
        >
          <Building2 class="w-3.5 h-3.5" />
          <span>Higiene Bangunan &amp; Usaha (HSBU)</span>
          <span class="px-1.5 py-0.2 rounded text-[10px] font-mono clinical-num" :class="currentTab === 'hsbu' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'">{{ stats.total_insp_hsbu || inspeksi_hsbu?.total || 0 }}</span>
        </button>
      </div>

      <!-- TAB CONTENT -->
      <InspeksiTab
        :type="currentTab"
        :dataset="currentTab === 'tpp' ? inspeksi_tpp : (currentTab === 'sab' ? inspeksi_sab : inspeksi_hsbu)"
        :per-page="currentTab === 'tpp' ? perPageTpp : (currentTab === 'sab' ? perPageSab : perPageHsbu)"
        :wilker-options="wilker_options"
        v-model:search="search"
        v-model:selected-wilker="selectedWilker"
        v-model:selected-risiko="selectedRisiko"
        v-model:tgl-mulai="tglMulai"
        v-model:tgl-selesai="tglSelesai"
        @apply-filter="applyFilter()"
        @reset-filter="resetFilter()"
        @change-per-page="changePerPage"
        @open-add-inspeksi="openAddInspeksi"
        @open-add-sarana="openAddSarana"
        @open-detail="openDetail"
        @delete-inspection="deleteInspection"
      />

      <!-- MODALS (Lazy loaded when opened) -->
      <AddInspeksiModal
        v-if="showAddInspeksiModal"
        :open="showAddInspeksiModal"
        :initial-type="addInspeksiType"
        :wilker-options="wilker_options"
        :pemeriksa-options="pemeriksa_options"
        :master-tpp="master_tpp"
        :master-sab="master_sab"
        :master-hsbu="master_hsbu"
        @close="showAddInspeksiModal = false"
        @saved="router.reload()"
      />

      <AddSaranaModal
        v-if="showAddSaranaModal"
        :open="showAddSaranaModal"
        :initial-type="addSaranaType"
        :wilker-options="wilker_options"
        @close="showAddSaranaModal = false"
        @saved="router.reload()"
      />

      <DetailInspeksiModal
        v-if="showDetailModal"
        :open="showDetailModal"
        :item="detailItem"
        :detail-type="detailType"
        @close="closeDetail"
      />
    </main>
  </HybridNavbar>
</template>
