<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { useDate } from '../../../composables/useDate';
import { useConfirm } from '@/composables/useConfirm';
import { useDebouncedFn } from '@/composables/useDebouncedFn';
import SharePublicModal from '../components/SharePublicModal.vue';
import FlightMedicalDetailModal from '../components/FlightMedicalDetailModal.vue';
import PaginationBar from '@/Components/PaginationBar.vue';
import {
  Activity,
  Briefcase,
  HeartPulse,
  Pill,
  FileCheck,
  Search,
  Download,
  Eye,
  CheckCircle2,
  Plus,
  Trash2,
  FileSpreadsheet,
  MapPin,
  Plane,
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
  debouncedFilter.cancel();
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

const debouncedFilter = useDebouncedFn(handleFilter, 350);

watch(searchInput, (val) => {
  if ((val || '') !== (props.filters?.search || '')) {
    debouncedFilter();
  }
});

const resetFilter = () => {
  debouncedFilter.cancel();
  searchInput.value = '';
  selectedEmbarkasi.value = '';
  selectedStatus.value = '';
  router.get('/travel-consent', { tab: cfg.value.tabKey }, { preserveState: true, preserveScroll: true, replace: true });
};

const activeItem = ref(null);
const showDetailModal = ref(false);
const showShareModal = ref(false);
const shareTarget = ref(null);

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

const openDetail = (item) => {
  activeItem.value = item;
  showDetailModal.value = true;
};

const closeDetail = () => {
  showDetailModal.value = false;
  activeItem.value = null;
};

const isSubmittingStatus = ref(false);

const submitStatusUpdate = (payload) => {
  if (!activeItem.value || isSubmittingStatus.value) return;
  isSubmittingStatus.value = true;
  router.put(`/flight-medical-inspections/${activeItem.value.id}/status`, payload, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success(`Status pemakaian ${cfg.value.nounLower} diperbarui`);
      closeDetail();
    },
    onError: () => {
      toast.error(`Gagal menyimpan status pemakaian ${cfg.value.nounLower}`);
    },
    onFinish: () => {
      isSubmittingStatus.value = false;
    },
  });
};

const { confirmDialog } = useConfirm();

const deleteInspection = async (id, regNo) => {
  const ok = await confirmDialog({
    title: `Hapus Data Pemakaian ${cfg.value.nounLower}?`,
    message: `Apakah Anda yakin ingin menghapus data pemakaian ${cfg.value.nounLower} ${regNo}?`,
    confirmText: 'Ya, Hapus',
    variant: 'danger',
  });
  if (ok) {
    router.delete(`/flight-medical-inspections/${id}`, {
      preserveScroll: true,
      onSuccess: () => toast.success(`Data pemakaian ${regNo} dihapus`),
      onError: () => toast.error('Gagal menghapus data pemakaian'),
    });
  }
};

const countUsed = (items) => {
  if (!Array.isArray(items)) return 0;
  return items.reduce((acc, curr) => acc + (parseInt(curr.terpakai) || 0), 0);
};
</script>

<template>
  <div class="space-y-3">
    <!-- Tab Action Header -->
    <div class="clinical-surface flex flex-col md:flex-row md:items-center justify-between gap-3 px-4 py-3">
      <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2">
          <h2 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-1.5">
            <component :is="cfg.mainIcon" class="w-4 h-4 text-[#00A99D]" />
            <span>{{ cfg.heading }}</span>
          </h2>
          <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-semibold border" :class="cfg.badgeClass">
            {{ cfg.badgeLabel }}
          </span>
        </div>
        <p class="text-[11px] text-slate-500 mt-0.5">
          {{ cfg.description }}
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-1.5 shrink-0">
        <a
          :href="cfg.formUrl"
          target="_blank"
          class="clinical-btn-primary"
        >
          <Plus class="w-3.5 h-3.5" />
          <span>{{ cfg.formLabel }}</span>
        </a>

        <a
          :href="`/flight-medical-inspections/export?type=${type}&format=xlsx${selectedEmbarkasi ? '&embarkasi=' + encodeURIComponent(selectedEmbarkasi) : ''}${selectedStatus ? '&status=' + encodeURIComponent(selectedStatus) : ''}`"
          class="clinical-btn-secondary"
          :title="cfg.exportTitle"
        >
          <FileSpreadsheet class="w-3.5 h-3.5 text-[#005851]" />
          <span>Ekspor Excel</span>
        </a>
      </div>
    </div>

    <!-- Live Metric Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5">
      <div class="clinical-kpi">
        <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-semibold">
          <span>TOTAL PEMAKAIAN</span>
          <component :is="cfg.mainIcon" class="w-3.5 h-3.5" :class="cfg.iconClass" />
        </div>
        <div class="text-xl font-bold text-slate-900 mt-1 font-mono clinical-num">
          {{ stats.total }}
        </div>
        <div class="text-[11px] text-slate-400">Laporan tercatat</div>
      </div>

      <div class="clinical-kpi">
        <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-semibold">
          <span>KLOTER DILAYANI</span>
          <Plane class="w-3.5 h-3.5 text-[#00A99D]" />
        </div>
        <div class="text-xl font-bold text-[#005851] mt-1 font-mono clinical-num">
          {{ stats.total_kloter }}
        </div>
        <div class="text-[11px] text-slate-500">Embarkasi KJT &amp; JKS</div>
      </div>

      <div class="clinical-kpi">
        <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-semibold">
          <span>{{ cfg.totalUsedLabel }}</span>
          <component :is="cfg.usedIcon" class="w-3.5 h-3.5 text-amber-500" />
        </div>
        <div class="text-xl font-bold text-amber-600 mt-1 font-mono clinical-num">
          {{ stats.total_items_used }}
        </div>
        <div class="text-[11px] text-amber-600">{{ cfg.totalUsedUnit }}</div>
      </div>

      <div class="clinical-kpi">
        <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-semibold">
          <span>TERVERIFIKASI</span>
          <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600" />
        </div>
        <div class="text-xl font-bold text-emerald-700 mt-1 font-mono clinical-num">
          {{ stats.approved_count }}
        </div>
        <div class="text-[11px] text-emerald-700">Disahkan karantina</div>
      </div>

      <div class="clinical-kpi col-span-2 sm:col-span-1">
        <div class="flex items-center justify-between text-slate-500 text-[10px] font-mono font-semibold">
          <span>MENUNGGU VERIFIKASI</span>
          <FileCheck class="w-3.5 h-3.5 text-sky-600" />
        </div>
        <div class="text-xl font-bold text-sky-700 mt-1 font-mono clinical-num">
          {{ stats.submitted_count }}
        </div>
        <div class="text-[11px] text-sky-500/80 mt-0.5">Proses review</div>
      </div>
    </div>

    <!-- Filters Toolbar -->
    <div class="clinical-surface p-3 flex flex-col md:flex-row items-center justify-between gap-2.5">
      <div class="relative w-full md:w-80">
        <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
        <input
          v-model="searchInput"
          @keyup.enter="handleFilter"
          type="text"
          placeholder="Cari kloter, embarkasi, awak kabin, pelapor..."
          class="clinical-input w-full pl-9 pr-3"
        />
      </div>

      <div class="flex items-center gap-2 w-full md:w-auto flex-wrap">
        <select
          v-model="selectedEmbarkasi"
          @change="handleFilter"
          class="clinical-input w-auto"
        >
          <option value="">Semua Embarkasi</option>
          <option v-for="emb in embarkasiList" :key="emb" :value="emb">{{ emb }}</option>
        </select>

        <select
          v-model="selectedStatus"
          @change="handleFilter"
          class="clinical-input w-auto"
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
          class="clinical-btn-primary py-1.5 px-3"
        >
          Terapkan
        </button>

        <button
          v-if="searchInput || selectedEmbarkasi || selectedStatus"
          type="button"
          @click="resetFilter"
          class="clinical-btn-secondary py-1.5 px-2.5"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- Main Table -->
    <div class="clinical-surface overflow-hidden">
      <!-- Mobile Card View -->
      <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
        <div
          v-for="item in inspections.data"
          :key="`mob-${item.id}`"
          class="p-3.5 space-y-2.5 text-xs"
        >
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <div class="font-mono font-bold text-[#005851] dark:text-[#4fd1c5]">{{ item.registration_no }}</div>
              <div class="font-bold text-slate-900 dark:text-slate-100 truncate">{{ item.nomor_kloter }} &bull; {{ item.embarkasi }}</div>
              <div class="text-[11px] text-slate-500">Haji: {{ item.penerbangan_haji_h }} H / {{ item.penerbangan_haji_m }} M</div>
            </div>
            <span
              class="px-2 py-0.5 rounded-full text-[10px] font-mono font-medium uppercase border shrink-0"
              :class="{
                'bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800': item.status === 'approved',
                'bg-sky-50 dark:bg-sky-950 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800': item.status === 'verified',
                'bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800': item.status === 'submitted',
                'bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800': item.status === 'rejected'
              }"
            >
              {{ item.status }}
            </span>
          </div>

          <div class="flex flex-wrap items-center gap-1.5">
            <span class="px-2 py-0.5 rounded text-[10.5px] font-mono font-semibold border" :class="cfg.p3kPillClass">
              P3K: {{ countUsed(item.items_p3k) }} Pcs
            </span>
            <span class="px-2 py-0.5 rounded text-[10.5px] font-mono font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800">
              EMK: {{ countUsed(item.items_emergensi) }} Pcs
            </span>
          </div>

          <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
            <div class="text-[11px] text-slate-500 truncate">
              Crew: <strong class="text-slate-700 dark:text-slate-300">{{ item.crew_nama }}</strong>
            </div>
            <div class="inline-flex items-center gap-1 shrink-0">
              <button
                type="button"
                @click="openShareModal(item)"
                class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                title="Salin / Lihat Link Publik Dokumen"
                :aria-label="`Salin tautan publik ${item.registration_no}`"
              >
                <Link2 class="w-4 h-4" />
              </button>
              <button
                type="button"
                @click="openDetail(item)"
                class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                title="Lihat Detail & Verifikasi"
                :aria-label="`Detail ${item.registration_no}`"
              >
                <Eye class="w-4 h-4" />
              </button>
              <a
                :href="`${cfg.receiptPrefix}${item.registration_no}`"
                target="_blank"
                class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                title="Lihat Tanda Terima"
                :aria-label="`Lihat tanda terima ${item.registration_no}`"
              >
                <Download class="w-4 h-4" />
              </a>
              <button
                type="button"
                @click="deleteInspection(item.id, item.registration_no)"
                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                title="Hapus Data"
                :aria-label="`Hapus ${item.registration_no}`"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <div v-if="inspections.data.length === 0" class="px-5 py-12 text-center text-slate-400">
          <component :is="cfg.mainIcon" class="w-8 h-8 mx-auto mb-2 opacity-50" :class="cfg.iconClass" />
          <p class="font-medium text-xs">Belum ada data pemakaian {{ cfg.nounLower }} yang sesuai.</p>
        </div>
      </div>

      <!-- Desktop Table View -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50/90 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
              <th class="px-4 py-2.5">No. Registrasi</th>
              <th class="px-4 py-2.5">Embarkasi &amp; Kloter</th>
              <th class="px-4 py-2.5">Penerbangan Haji</th>
              <th class="px-4 py-2.5">{{ cfg.colUsedLabel }}</th>
              <th class="px-4 py-2.5">Awak Kabin &amp; Pelapor</th>
              <th class="px-4 py-2.5">Waktu Lapor</th>
              <th class="px-4 py-2.5">Status</th>
              <th class="px-4 py-2.5 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="item in inspections.data"
              :key="item.id"
              class="hover:bg-[#F4FBF9] transition-colors"
            >
              <td class="px-4 py-2.5 font-mono font-bold whitespace-nowrap text-[#005851]">
                {{ item.registration_no }}
              </td>

              <td class="px-4 py-2.5">
                <div class="font-bold text-slate-900">{{ item.nomor_kloter }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                  <MapPin class="w-3 h-3" :class="cfg.iconClass" />
                  <span>{{ item.embarkasi }}</span>
                </div>
              </td>

              <td class="px-4 py-2.5 font-mono text-slate-700 whitespace-nowrap">
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

    <FlightMedicalDetailModal
      :open="Boolean(showDetailModal && activeItem)"
      :item="activeItem"
      :cfg="cfg"
      :is-submitting="isSubmittingStatus"
      @close="closeDetail"
      @submit="submitStatusUpdate"
    />

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

