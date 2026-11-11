<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useDate } from '../../../composables/useDate';
import SharePublicModal from '../components/SharePublicModal.vue';
import PaginationBar from '../components/PaginationBar.vue';
import {
  Activity,
  Briefcase,
  HeartPulse,
  Pill,
  ShieldCheck,
  FileCheck,
  Search,
  Download,
  Eye,
  CheckCircle2,
  Plus,
  Trash2,
  X,
  FileSpreadsheet,
  MapPin,
  Plane,
  Check,
  Info,
  Link2,
} from 'lucide-vue-next';

const props = defineProps({
  type: {
    type: String,
    required: true, // 'alat' | 'obat'
  },
  inspections: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ search: '', embarkasi: '', status: '' }),
  },
  stats: {
    type: Object,
    required: true,
  },
  embarkasiList: {
    type: Array,
    default: () => [],
  },
});

const { formatDate } = useDate();

const META = {
  alat: {
    tabKey: 'alat',
    badgeLabel: 'Pengawasan Alat Kesehatan Penerbangan',
    heading: 'Pemakaian Alat Kesehatan Kotak P3K & Emergensi',
    description: 'Pengawasan dan rekam medis pemakaian First Aid Kit (17 alat) & Emergency Medical Kit (21 alat) pada penerbangan maskapai dan kloter haji.',
    formUrl: '/travelconsent/public_alat',
    formLabel: 'Form Input Alkes ↗',
    exportTitle: 'Unduh Data Alkes ke Excel',
    totalUsedLabel: 'TOTAL ALAT TERPAKAI',
    totalUsedUnit: 'Pcs perlengkapan',
    colUsedLabel: 'Alkes Terpakai',
    itemNoun: 'Alkes',
    nounLower: 'alat kesehatan',
    modalP3kTab: 'Alkes Kotak P3K',
    modalEmkTab: 'Alkes Kotak Emergensi',
    modalP3kHeader: 'Nama Alat P3K',
    modalEmkHeader: 'Nama Alat Emergensi',
    notesPlaceholder: 'Catatan penggantian stok alkes, rekomendasi pemeriksaan ulang...',
    shareTitle: 'Link Publik Dokumen Pemakaian Alkes',
    receiptPrefix: '/travelconsent/receipt_alat/',
    accent: 'blue',
    badgeClass: 'bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
    primaryBtn: 'bg-blue-600 hover:bg-blue-500 text-white',
    exportBtn: 'text-blue-800 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 border-blue-200 dark:border-blue-800',
    iconClass: 'text-blue-600',
    inputRing: 'focus:ring-blue-500',
    regTextClass: 'text-blue-700 dark:text-blue-400',
    p3kPillClass: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800',
    p3kUsedClass: 'text-blue-600 dark:text-blue-400',
    modalHeaderIcon: 'bg-blue-600',
    modalRegBadge: 'bg-blue-100 text-blue-800',
    activeModalTabClass: 'border-blue-600 text-blue-600',
    notesBoxClass: 'bg-blue-50/50 dark:bg-blue-950/30 border-blue-100 dark:border-blue-900',
    notesTitleClass: 'text-blue-900 dark:text-blue-300',
    pageActiveClass: 'bg-blue-600 text-white border-blue-600 font-bold',
    mainIcon: Activity,
    usedIcon: Briefcase,
  },
  obat: {
    tabKey: 'obat',
    badgeLabel: 'Pengawasan Sediaan Farmasi Penerbangan',
    heading: 'Pemakaian Obat-obatan Kotak P3K & Emergensi',
    description: 'Pengawasan dan rekam medis pemakaian First Aid Kit Medications (6 obat) & Emergency Medical Kit (15 obat) pada penerbangan maskapai dan kloter haji.',
    formUrl: '/travelconsent/public_obat',
    formLabel: 'Form Input Obat ↗',
    exportTitle: 'Unduh Data Obat ke Excel',
    totalUsedLabel: 'TOTAL OBAT TERPAKAI',
    totalUsedUnit: 'Pcs / unit obat',
    colUsedLabel: 'Obat Terpakai',
    itemNoun: 'Obat',
    nounLower: 'obat',
    modalP3kTab: 'Obat Kotak P3K',
    modalEmkTab: 'Obat Kotak Emergensi',
    modalP3kHeader: 'Nama Obat P3K',
    modalEmkHeader: 'Nama Obat Emergensi',
    notesPlaceholder: 'Catatan penggantian stok obat, rekomendasi pemeriksaan ulang...',
    shareTitle: 'Link Publik Dokumen Pemakaian Obat',
    receiptPrefix: '/travelconsent/receipt_obat/',
    accent: 'rose',
    badgeClass: 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
    primaryBtn: 'bg-rose-600 hover:bg-rose-500 text-white',
    exportBtn: 'text-rose-800 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 border-rose-200 dark:border-rose-800',
    iconClass: 'text-rose-600',
    inputRing: 'focus:ring-rose-500',
    regTextClass: 'text-rose-700 dark:text-rose-400',
    p3kPillClass: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
    p3kUsedClass: 'text-rose-600 dark:text-rose-400',
    modalHeaderIcon: 'bg-rose-600',
    modalRegBadge: 'bg-rose-100 text-rose-800',
    activeModalTabClass: 'border-rose-600 text-rose-600',
    notesBoxClass: 'bg-rose-50/50 dark:bg-rose-950/30 border-rose-100 dark:border-rose-900',
    notesTitleClass: 'text-rose-900 dark:text-rose-300',
    pageActiveClass: 'bg-rose-600 text-white border-rose-600 font-bold',
    mainIcon: HeartPulse,
    usedIcon: Pill,
  },
};

const cfg = computed(() => META[props.type] || META.alat);

const searchInput = ref(props.filters.search || '');
const selectedEmbarkasi = ref(props.filters.embarkasi || '');
const selectedStatus = ref(props.filters.status || '');

watch(
  () => [props.type, props.filters],
  () => {
    searchInput.value = props.filters?.search || '';
    selectedEmbarkasi.value = props.filters?.embarkasi || '';
    selectedStatus.value = props.filters?.status || '';
  },
  { deep: true }
);

const handleFilter = () => {
  const tabKey = cfg.value.tabKey;
  router.get('/travel-consent', {
    tab: tabKey,
    [`${tabKey}_search`]: searchInput.value || undefined,
    [`${tabKey}_embarkasi`]: selectedEmbarkasi.value || undefined,
    [`${tabKey}_status`]: selectedStatus.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

const resetFilter = () => {
  searchInput.value = '';
  selectedEmbarkasi.value = '';
  selectedStatus.value = '';
  router.get('/travel-consent', { tab: cfg.value.tabKey }, { preserveState: true, preserveScroll: true, replace: true });
};

const activeItem = ref(null);
const showDetailModal = ref(false);
const showShareModal = ref(false);
const shareTarget = ref(null);
const activeModalTab = ref('overview'); // 'overview' | 'p3k' | 'emergensi' | 'verify'

const shareUrl = computed(() => {
  if (!shareTarget.value) return '';
  const path = `${cfg.value.receiptPrefix}${shareTarget.value.registration_no}`;
  return typeof window === 'undefined' ? path : `${window.location.origin}${path}`;
});

const shareMetaRows = computed(() => {
  if (!shareTarget.value) return [];
  return [
    {
      label: 'Nomor Registrasi',
      value: shareTarget.value.registration_no,
      valueClass: `font-mono font-bold ${cfg.value.regTextClass}`,
    },
    {
      label: 'Embarkasi • Kloter',
      value: `${shareTarget.value.embarkasi} • ${shareTarget.value.nomor_kloter}`,
      valueClass: 'font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[240px]',
    },
    {
      label: 'Crew / Awak Kabin',
      value: shareTarget.value.crew_nama || '-',
      valueClass: 'font-medium text-slate-700 dark:text-slate-300',
    },
  ];
});

const openShareModal = (item) => {
  shareTarget.value = item;
  showShareModal.value = true;
};

const statusForm = ref({
  status: 'approved',
  keterangan: '',
});

const openDetail = (item) => {
  activeItem.value = item;
  statusForm.value = {
    status: item.status || 'submitted',
    keterangan: item.keterangan || '',
  };
  activeModalTab.value = 'overview';
  showDetailModal.value = true;
};

const closeDetail = () => {
  showDetailModal.value = false;
  activeItem.value = null;
};

const submitStatusUpdate = () => {
  if (!activeItem.value) return;
  router.put(`/flight-medical-inspections/${activeItem.value.id}/status`, statusForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      closeDetail();
    },
  });
};

const deleteInspection = (id, regNo) => {
  if (confirm(`Apakah Anda yakin ingin menghapus data pemakaian ${cfg.value.nounLower} ${regNo}?`)) {
    router.delete(`/flight-medical-inspections/${id}`, {
      preserveScroll: true,
    });
  }
};

const countUsed = (items) => {
  if (!Array.isArray(items)) return 0;
  return items.reduce((acc, curr) => acc + (parseInt(curr.terpakai) || 0), 0);
};
</script>

<template>
  <div class="space-y-6">
    <!-- Tab Action Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-5 shadow-xs">
      <div>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-semibold border mb-1.5" :class="cfg.badgeClass">
          <Plane class="w-3.5 h-3.5" />
          <span>{{ cfg.badgeLabel }}</span>
        </div>
        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
          {{ cfg.heading }}
        </h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-3xl">
          {{ cfg.description }}
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <a
          :href="cfg.formUrl"
          target="_blank"
          class="px-4 py-2.5 rounded-xl font-bold text-xs transition-all shadow-sm flex items-center gap-1.5 cursor-pointer"
          :class="cfg.primaryBtn"
        >
          <Plus class="w-4 h-4" />
          <span>{{ cfg.formLabel }}</span>
        </a>

        <a
          :href="`/flight-medical-inspections/export?type=${type}&format=xlsx${selectedEmbarkasi ? '&embarkasi=' + encodeURIComponent(selectedEmbarkasi) : ''}${selectedStatus ? '&status=' + encodeURIComponent(selectedStatus) : ''}`"
          class="px-3.5 py-2.5 rounded-xl font-semibold text-xs border transition-colors flex items-center gap-1.5"
          :class="cfg.exportBtn"
          :title="cfg.exportTitle"
        >
          <FileSpreadsheet class="w-4 h-4" :class="cfg.iconClass" />
          <span>Ekspor Excel</span>
        </a>
      </div>
    </div>

    <!-- Live Metric Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
        <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
          <span>TOTAL PEMAKAIAN</span>
          <component :is="cfg.mainIcon" class="w-4 h-4" :class="cfg.iconClass" />
        </div>
        <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1.5">
          {{ stats.total }}
        </div>
        <div class="text-[11px] text-slate-400 mt-0.5">Laporan tercatat</div>
      </div>

      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
        <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
          <span>KLOTER DILAYANI</span>
          <Plane class="w-4 h-4 text-indigo-600" />
        </div>
        <div class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1.5">
          {{ stats.total_kloter }}
        </div>
        <div class="text-[11px] text-indigo-500/80 mt-0.5">Embarkasi KJT &amp; JKS</div>
      </div>

      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
        <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
          <span>{{ cfg.totalUsedLabel }}</span>
          <component :is="cfg.usedIcon" class="w-4 h-4 text-amber-500" />
        </div>
        <div class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1.5">
          {{ stats.total_items_used }}
        </div>
        <div class="text-[11px] text-amber-500/80 mt-0.5">{{ cfg.totalUsedUnit }}</div>
      </div>

      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
        <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
          <span>TERVERIFIKASI</span>
          <CheckCircle2 class="w-4 h-4 text-emerald-600" />
        </div>
        <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1.5">
          {{ stats.approved_count }}
        </div>
        <div class="text-[11px] text-emerald-600/80 mt-0.5">Disahkan karantina</div>
      </div>

      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-xs col-span-2 sm:col-span-1">
        <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
          <span>MENUNGGU VERIFIKASI</span>
          <FileCheck class="w-4 h-4 text-sky-500" />
        </div>
        <div class="text-2xl font-extrabold text-sky-600 dark:text-sky-400 mt-1.5">
          {{ stats.submitted_count }}
        </div>
        <div class="text-[11px] text-sky-500/80 mt-0.5">Proses review</div>
      </div>
    </div>

    <!-- Filters Toolbar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 flex flex-col md:flex-row items-center justify-between gap-3 shadow-xs">
      <div class="relative w-full md:w-80">
        <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
        <input
          v-model="searchInput"
          @keyup.enter="handleFilter"
          type="text"
          placeholder="Cari kloter, embarkasi, awak kabin, pelapor..."
          class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 transition-all"
          :class="cfg.inputRing"
        />
      </div>

      <div class="flex items-center gap-2 w-full md:w-auto flex-wrap">
        <select
          v-model="selectedEmbarkasi"
          @change="handleFilter"
          class="px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2"
          :class="cfg.inputRing"
        >
          <option value="">Semua Embarkasi</option>
          <option v-for="emb in embarkasiList" :key="emb" :value="emb">{{ emb }}</option>
        </select>

        <select
          v-model="selectedStatus"
          @change="handleFilter"
          class="px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2"
          :class="cfg.inputRing"
        >
          <option value="">Semua Status</option>
          <option value="submitted">Submitted (Menunggu)</option>
          <option value="verified">Verified (Petugas)</option>
          <option value="approved">Approved (Disahkan)</option>
          <option value="rejected">Rejected (Ditolak)</option>
        </select>

        <button
          type="button"
          @click="handleFilter"
          class="px-3.5 py-2 text-xs font-bold rounded-xl transition-colors cursor-pointer"
          :class="cfg.primaryBtn"
        >
          Terapkan
        </button>

        <button
          v-if="searchInput || selectedEmbarkasi || selectedStatus"
          type="button"
          @click="resetFilter"
          class="px-3 py-2 text-xs text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- Main Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-950/60 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
              <th class="px-5 py-3.5">No. Registrasi</th>
              <th class="px-5 py-3.5">Embarkasi &amp; Kloter</th>
              <th class="px-5 py-3.5">Penerbangan Haji</th>
              <th class="px-5 py-3.5">{{ cfg.colUsedLabel }}</th>
              <th class="px-5 py-3.5">Awak Kabin &amp; Pelapor</th>
              <th class="px-5 py-3.5">Waktu Lapor</th>
              <th class="px-5 py-3.5">Status</th>
              <th class="px-5 py-3.5 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr
              v-for="item in inspections.data"
              :key="item.id"
              class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="px-5 py-4 font-mono font-bold whitespace-nowrap" :class="cfg.regTextClass">
                {{ item.registration_no }}
              </td>

              <td class="px-5 py-4">
                <div class="font-bold text-slate-900 dark:text-white">{{ item.nomor_kloter }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                  <MapPin class="w-3 h-3" :class="cfg.iconClass" />
                  <span>{{ item.embarkasi }}</span>
                </div>
              </td>

              <td class="px-5 py-4 font-mono text-slate-700 dark:text-slate-300 whitespace-nowrap">
                <div>{{ item.penerbangan_haji_h }} H / {{ item.penerbangan_haji_m }} M</div>
                <div v-if="item.no_kontak" class="text-[10px] text-slate-400 font-sans">
                  {{ item.no_kontak }}
                </div>
              </td>

              <td class="px-5 py-4 whitespace-nowrap">
                <div class="flex items-center gap-1.5">
                  <span class="px-2 py-0.5 rounded text-[10.5px] font-mono font-semibold border" :class="cfg.p3kPillClass">
                    P3K: {{ countUsed(item.items_p3k) }} Pcs
                  </span>
                  <span class="px-2 py-0.5 rounded text-[10.5px] font-mono font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800">
                    EMK: {{ countUsed(item.items_emergensi) }} Pcs
                  </span>
                </div>
              </td>

              <td class="px-5 py-4">
                <div class="font-medium text-slate-800 dark:text-slate-200">{{ item.crew_nama }}</div>
                <div class="text-[11px] text-slate-400">Pelapor: {{ item.pelapor_nama }}</div>
              </td>

              <td class="px-5 py-4 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                <div>{{ formatDate(item.created_at) }}</div>
                <div class="text-[10px] text-slate-400 font-mono">{{ item.created_at ? item.created_at.substring(11, 16) : '' }} WIB</div>
              </td>

              <td class="px-5 py-4 whitespace-nowrap">
                <span
                  class="px-2.5 py-1 rounded-full text-[10.5px] font-mono font-medium uppercase border"
                  :class="{
                    'bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800': item.status === 'approved',
                    'bg-sky-50 dark:bg-sky-950 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800': item.status === 'verified',
                    'bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800': item.status === 'submitted',
                    'bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800': item.status === 'rejected'
                  }"
                >
                  {{ item.status }}
                </span>
              </td>

              <td class="px-5 py-4 text-right whitespace-nowrap">
                <div class="inline-flex items-center gap-1.5">
                  <button
                    type="button"
                    @click="openShareModal(item)"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    title="Salin / Lihat Link Publik Dokumen"
                  >
                    <Link2 class="w-4 h-4" />
                  </button>

                  <button
                    type="button"
                    @click="openDetail(item)"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    title="Lihat Detail & Verifikasi"
                  >
                    <Eye class="w-4 h-4" />
                  </button>

                  <a
                    :href="`${cfg.receiptPrefix}${item.registration_no}`"
                    target="_blank"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    title="Lihat Tanda Terima"
                  >
                    <Download class="w-4 h-4" />
                  </a>

                  <button
                    type="button"
                    @click="deleteInspection(item.id, item.registration_no)"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    title="Hapus Data"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="inspections.data.length === 0">
              <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                <component :is="cfg.mainIcon" class="w-8 h-8 mx-auto mb-2 opacity-50" :class="cfg.iconClass" />
                <p class="font-medium">Belum ada data pemakaian {{ cfg.nounLower }} yang sesuai.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <PaginationBar
        :meta="inspections"
        noun="permohonan"
        :active-class="cfg.pageActiveClass"
      />
    </div>

    <!-- Modal Detail & Verification -->
    <div
      v-if="showDetailModal && activeItem"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
      @click.self="closeDetail"
    >
      <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-4xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-950/60">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl text-white flex items-center justify-center shadow-xs" :class="cfg.modalHeaderIcon">
              <component :is="cfg.mainIcon" class="w-5 h-5" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                  {{ activeItem.nomor_kloter }} &bull; {{ activeItem.embarkasi }}
                </h3>
                <span class="px-2 py-0.5 rounded text-[10.5px] font-mono font-bold" :class="cfg.modalRegBadge">
                  {{ activeItem.registration_no }}
                </span>
              </div>
              <p class="text-xs text-slate-500">
                Penerbangan Haji: {{ activeItem.penerbangan_haji_h }} H / {{ activeItem.penerbangan_haji_m }} M
              </p>
            </div>
          </div>

          <button
            type="button"
            @click="closeDetail"
            class="p-2 rounded-xl text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800 transition-colors cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="flex border-b border-slate-200 dark:border-slate-800 px-6 bg-white dark:bg-slate-900 gap-6 text-xs font-semibold overflow-x-auto">
          <button
            type="button"
            @click="activeModalTab = 'overview'"
            class="py-3 border-b-2 transition-colors cursor-pointer shrink-0"
            :class="activeModalTab === 'overview' ? cfg.activeModalTabClass : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'"
          >
            Informasi Kloter &amp; TTD
          </button>
          <button
            type="button"
            @click="activeModalTab = 'p3k'"
            class="py-3 border-b-2 transition-colors cursor-pointer flex items-center gap-1.5 shrink-0"
            :class="activeModalTab === 'p3k' ? cfg.activeModalTabClass : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'"
          >
            {{ cfg.modalP3kTab }}
            <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] rounded-full">
              {{ activeItem.items_p3k ? activeItem.items_p3k.length : 0 }}
            </span>
          </button>
          <button
            type="button"
            @click="activeModalTab = 'emergensi'"
            class="py-3 border-b-2 transition-colors cursor-pointer flex items-center gap-1.5 shrink-0"
            :class="activeModalTab === 'emergensi' ? cfg.activeModalTabClass : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'"
          >
            {{ cfg.modalEmkTab }}
            <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] rounded-full">
              {{ activeItem.items_emergensi ? activeItem.items_emergensi.length : 0 }}
            </span>
          </button>
          <button
            type="button"
            @click="activeModalTab = 'verify'"
            class="py-3 border-b-2 transition-colors cursor-pointer flex items-center gap-1 shrink-0"
            :class="activeModalTab === 'verify' ? cfg.activeModalTabClass : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'"
          >
            <ShieldCheck class="w-3.5 h-3.5" />
            <span>Verifikasi Karantina</span>
          </button>
        </div>

        <div class="p-6 overflow-y-auto flex-1 space-y-6 text-xs">
          <div v-if="activeModalTab === 'overview'" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 space-y-2.5">
                <h4 class="font-bold text-slate-900 dark:text-white text-xs flex items-center gap-1.5">
                  <Plane class="w-4 h-4" :class="cfg.iconClass" />
                  <span>Data Kloter &amp; Penerbangan</span>
                </h4>
                <div class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Nomor Kloter:</span>
                    <strong class="text-slate-900 dark:text-white">{{ activeItem.nomor_kloter }}</strong>
                  </div>
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Embarkasi:</span>
                    <span class="font-medium">{{ activeItem.embarkasi }}</span>
                  </div>
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Penerbangan Haji:</span>
                    <span>{{ activeItem.penerbangan_haji_h }} H / {{ activeItem.penerbangan_haji_m }} M</span>
                  </div>
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Kontak Dokter/PIC:</span>
                    <span>{{ activeItem.no_kontak || '-' }}</span>
                  </div>
                </div>
              </div>

              <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 space-y-2.5">
                <h4 class="font-bold text-slate-900 dark:text-white text-xs flex items-center gap-1.5">
                  <ShieldCheck class="w-4 h-4" :class="cfg.iconClass" />
                  <span>Petugas &amp; Lokasi</span>
                </h4>
                <div class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Awak Kabin (Crew):</span>
                    <strong class="text-slate-900 dark:text-white">{{ activeItem.crew_nama }}</strong>
                  </div>
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Petugas / Pelapor:</span>
                    <strong class="text-slate-900 dark:text-white">{{ activeItem.pelapor_nama }}</strong>
                  </div>
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Lokasi Geotag:</span>
                    <span>{{ activeItem.lokasi_geotag || '-' }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="p-4 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1.5">
                <h5 class="font-bold text-slate-800 dark:text-slate-200">Tanda Tangan Awak Kabin:</h5>
                <div class="p-2 border border-slate-200 rounded-xl bg-white">
                  <img v-if="activeItem.crew_ttd" :src="activeItem.crew_ttd" alt="TTD Crew" class="h-16 object-contain mx-auto" />
                  <p v-else class="text-slate-400 text-center italic py-4">Tidak ada tanda tangan</p>
                </div>
                <p class="text-[10px] text-slate-400 text-center">{{ activeItem.crew_nama }}</p>
              </div>

              <div class="p-4 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1.5">
                <h5 class="font-bold text-slate-800 dark:text-slate-200">Tanda Tangan Petugas / Pelapor:</h5>
                <div class="p-2 border border-slate-200 rounded-xl bg-white">
                  <img v-if="activeItem.pelapor_ttd" :src="activeItem.pelapor_ttd" alt="TTD Pelapor" class="h-16 object-contain mx-auto" />
                  <p v-else class="text-slate-400 text-center italic py-4">Tidak ada tanda tangan</p>
                </div>
                <p class="text-[10px] text-slate-400 text-center">{{ activeItem.pelapor_nama }}</p>
              </div>
            </div>

            <div class="p-4 rounded-2xl border" :class="cfg.notesBoxClass">
              <h5 class="font-bold mb-1 flex items-center gap-1.5" :class="cfg.notesTitleClass">
                <Info class="w-4 h-4" :class="cfg.iconClass" />
                <span>Catatan Pemakaian:</span>
              </h5>
              <p class="text-slate-700 dark:text-slate-300 italic">"{{ activeItem.keterangan || 'Tidak ada catatan tambahan.' }}"</p>
            </div>
          </div>

          <div v-if="activeModalTab === 'p3k'" class="space-y-4">
            <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 font-bold text-[11px]">
                  <tr>
                    <th class="px-4 py-2.5 w-12 text-center">No</th>
                    <th class="px-4 py-2.5">{{ cfg.modalP3kHeader }}</th>
                    <th class="px-4 py-2.5 w-24 text-center">Satuan</th>
                    <th class="px-4 py-2.5 w-24 text-center">Isi</th>
                    <th class="px-4 py-2.5 w-24 text-center">Terpakai</th>
                    <th class="px-4 py-2.5">Keterangan</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                  <tr v-for="(item, idx) in activeItem.items_p3k || []" :key="idx" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                    <td class="px-4 py-2 text-center font-mono text-slate-400">{{ idx + 1 }}</td>
                    <td class="px-4 py-2 font-medium text-slate-800 dark:text-slate-200">{{ item.nama }}</td>
                    <td class="px-4 py-2 text-center text-slate-500">{{ item.satuan }}</td>
                    <td class="px-4 py-2 text-center font-mono">{{ item.isi }}</td>
                    <td class="px-4 py-2 text-center font-mono font-bold" :class="parseInt(item.terpakai) > 0 ? cfg.p3kUsedClass : 'text-slate-400'">
                      {{ item.terpakai }}
                    </td>
                    <td class="px-4 py-2 text-slate-600 dark:text-slate-400">{{ item.keterangan || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div v-if="activeModalTab === 'emergensi'" class="space-y-4">
            <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 font-bold text-[11px]">
                  <tr>
                    <th class="px-4 py-2.5 w-12 text-center">No</th>
                    <th class="px-4 py-2.5">{{ cfg.modalEmkHeader }}</th>
                    <th class="px-4 py-2.5 w-24 text-center">Satuan</th>
                    <th class="px-4 py-2.5 w-24 text-center">Isi</th>
                    <th class="px-4 py-2.5 w-24 text-center">Terpakai</th>
                    <th class="px-4 py-2.5">Keterangan</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                  <tr v-for="(item, idx) in activeItem.items_emergensi || []" :key="idx" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                    <td class="px-4 py-2 text-center font-mono text-slate-400">{{ idx + 1 }}</td>
                    <td class="px-4 py-2 font-medium text-slate-800 dark:text-slate-200">{{ item.nama }}</td>
                    <td class="px-4 py-2 text-center text-slate-500">{{ item.satuan }}</td>
                    <td class="px-4 py-2 text-center font-mono">{{ item.isi }}</td>
                    <td class="px-4 py-2 text-center font-mono font-bold" :class="parseInt(item.terpakai) > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400'">
                      {{ item.terpakai }}
                    </td>
                    <td class="px-4 py-2 text-slate-600 dark:text-slate-400">{{ item.keterangan || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div v-if="activeModalTab === 'verify'" class="space-y-4">
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 space-y-4">
              <h4 class="font-bold text-slate-900 dark:text-white text-sm">Verifikasi Laporan Karantina</h4>
              <div>
                <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-1">Status Verifikasi:</label>
                <select
                  v-model="statusForm.status"
                  class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white font-medium focus:ring-2"
                  :class="cfg.inputRing"
                >
                  <option value="submitted">Submitted (Menunggu)</option>
                  <option value="verified">Verified (Telah Diverifikasi)</option>
                  <option value="approved">Approved (Disahkan)</option>
                  <option value="rejected">Rejected (Ditolak / Perlu Perbaikan)</option>
                </select>
              </div>

              <div>
                <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-1">Catatan / Rekomendasi Resmi:</label>
                <textarea
                  v-model="statusForm.keterangan"
                  rows="3"
                  :placeholder="cfg.notesPlaceholder"
                  class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white focus:ring-2"
                  :class="cfg.inputRing"
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 flex items-center justify-between">
          <div class="text-xs text-slate-400">
            Diverifikasi oleh: <strong class="text-slate-700 dark:text-slate-300">{{ activeItem.verifier ? activeItem.verifier.name : 'Belum diverifikasi' }}</strong>
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="closeDetail"
              class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition-colors cursor-pointer"
            >
              Tutup
            </button>

            <button
              v-if="activeModalTab === 'verify'"
              type="button"
              @click="submitStatusUpdate"
              class="px-5 py-2 rounded-xl text-xs font-bold transition-colors shadow-xs cursor-pointer flex items-center gap-1.5"
              :class="cfg.primaryBtn"
            >
              <Check class="w-4 h-4" />
              <span>Simpan Pembaruan</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <SharePublicModal
      :show="showShareModal && !!shareTarget"
      :title="cfg.shareTitle"
      subtitle="Dapat diakses publik tanpa login untuk melihat & membagikan tanda terima."
      :url="shareUrl"
      :meta-rows="shareMetaRows"
      :accent="cfg.accent"
      @close="showShareModal = false"
    />
  </div>
</template>

