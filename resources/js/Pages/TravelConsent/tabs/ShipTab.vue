<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useDate } from '../../../composables/useDate';
import SharePublicModal from '../components/SharePublicModal.vue';
import PaginationBar from '../components/PaginationBar.vue';
import {
  Ship,
  Anchor,
  ShieldCheck,
  AlertTriangle,
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
  Compass,
  Check,
  Info,
  Link2,
} from 'lucide-vue-next';

const props = defineProps({
  inspections: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ search: '', pelabuhan: '', sertifikat: '', status: '' }),
  },
  stats: {
    type: Object,
    required: true,
  },
  ports: {
    type: Array,
    default: () => [],
  },
});

const { formatDate } = useDate();

const searchInput = ref(props.filters.search || '');
const selectedPelabuhan = ref(props.filters.pelabuhan || '');
const selectedSertifikat = ref(props.filters.sertifikat || '');
const selectedStatus = ref(props.filters.status || '');

watch(
  () => props.filters,
  (next) => {
    searchInput.value = next?.search || '';
    selectedPelabuhan.value = next?.pelabuhan || '';
    selectedSertifikat.value = next?.sertifikat || '';
    selectedStatus.value = next?.status || '';
  },
  { deep: true }
);

const handleFilter = () => {
  router.get('/travel-consent', {
    tab: 'ship',
    ship_search: searchInput.value || undefined,
    ship_pelabuhan: selectedPelabuhan.value || undefined,
    ship_sertifikat: selectedSertifikat.value || undefined,
    ship_status: selectedStatus.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

const resetFilter = () => {
  searchInput.value = '';
  selectedPelabuhan.value = '';
  selectedSertifikat.value = '';
  selectedStatus.value = '';
  router.get('/travel-consent', { tab: 'ship' }, { preserveState: true, preserveScroll: true, replace: true });
};

const activeInspection = ref(null);
const showDetailModal = ref(false);
const showShareModal = ref(false);
const shareTarget = ref(null);
const activeModalTab = ref('overview'); // 'overview' | 'checklist' | 'verify'

const shareUrl = computed(() => {
  if (!shareTarget.value) return '';
  const path = `/travelconsent/receipt_sanitasi/${shareTarget.value.registration_no}`;
  return typeof window === 'undefined' ? path : `${window.location.origin}${path}`;
});

const shareMetaRows = computed(() => {
  if (!shareTarget.value) return [];
  return [
    {
      label: 'Nomor Registrasi',
      value: shareTarget.value.registration_no,
      valueClass: 'font-mono font-bold text-teal-700 dark:text-teal-400',
    },
    {
      label: 'Nama Kapal • IMO',
      value: `${shareTarget.value.nama_kapal} • ${shareTarget.value.imo_no}`,
      valueClass: 'font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[240px]',
    },
    {
      label: 'Pelabuhan',
      value: shareTarget.value.pelabuhan || '-',
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
  sertifikat_mencatat: 'Pembebasan',
  pejabat_penerbit: '',
  keterangan: '',
});

const openDetail = (item) => {
  activeInspection.value = item;
  statusForm.value = {
    status: item.status || 'submitted',
    sertifikat_mencatat: item.sertifikat_mencatat || 'Pembebasan',
    pejabat_penerbit: item.pejabat_penerbit || 'dr. H. Rinaldi, Sp.KP',
    keterangan: item.keterangan || '',
  };
  activeModalTab.value = 'overview';
  showDetailModal.value = true;
};

const closeDetail = () => {
  showDetailModal.value = false;
  activeInspection.value = null;
};

const submitStatusUpdate = () => {
  if (!activeInspection.value) return;
  router.put(`/ship-inspections/${activeInspection.value.id}/status`, statusForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      closeDetail();
    },
  });
};

const deleteInspection = (id, regNo, shipName) => {
  if (confirm(`Apakah Anda yakin ingin menghapus data inspeksi kapal ${shipName} (${regNo})?`)) {
    router.delete(`/ship-inspections/${id}`, {
      preserveScroll: true,
    });
  }
};
</script>

<template>
  <div class="space-y-6">
    <!-- Tab Action Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-5 shadow-xs">
      <div>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-semibold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 mb-1.5">
          <Anchor class="w-3.5 h-3.5" />
          <span>Kekarantinaan Kesehatan Pelabuhan Laut</span>
        </div>
        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
          Inspeksi Sanitasi Kapal (SSCC / SSCEC)
        </h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-3xl">
          Manajemen &amp; penerbitan Sertifikat Sanitasi Kapal (<em class="text-slate-700 dark:text-slate-300 font-medium">Ship Sanitation Control / Exemption Certificate</em>) standar WHO IHR 2005.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <a
          href="/travelconsent/public_sanitasi"
          target="_blank"
          class="px-4 py-2.5 rounded-xl font-bold text-xs text-white bg-emerald-600 hover:bg-emerald-500 transition-all shadow-sm flex items-center gap-1.5 cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>Form Input Baru ↗</span>
        </a>

        <a
          :href="`/ship-inspections/export?format=xlsx${selectedPelabuhan ? '&pelabuhan=' + encodeURIComponent(selectedPelabuhan) : ''}${selectedSertifikat ? '&sertifikat=' + encodeURIComponent(selectedSertifikat) : ''}${selectedStatus ? '&status=' + encodeURIComponent(selectedStatus) : ''}`"
          class="px-3.5 py-2.5 rounded-xl font-semibold text-xs text-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-200 dark:border-emerald-800/60 transition-colors flex items-center gap-1.5"
          title="Unduh Data Inspeksi Kapal ke Excel"
        >
          <FileSpreadsheet class="w-4 h-4 text-emerald-600" />
          <span>Ekspor Excel</span>
        </a>
      </div>
    </div>

    <!-- Live Metric Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
        <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
          <span>TOTAL SERTIFIKAT</span>
          <Ship class="w-4 h-4 text-emerald-600" />
        </div>
        <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1.5">
          {{ stats.total }}
        </div>
        <div class="text-[11px] text-slate-400 mt-0.5">Semua pelabuhan</div>
      </div>

      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
        <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
          <span>PEMBEBASAN (SSCEC)</span>
          <CheckCircle2 class="w-4 h-4 text-emerald-600" />
        </div>
        <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1.5">
          {{ stats.pembebasan_count }}
        </div>
        <div class="text-[11px] text-emerald-600/80 mt-0.5">Nihil tindakan pengendalian</div>
      </div>

      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
        <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
          <span>PENGENDALIAN (SSCC)</span>
          <AlertTriangle class="w-4 h-4 text-amber-500" />
        </div>
        <div class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1.5">
          {{ stats.pengendalian_count }}
        </div>
        <div class="text-[11px] text-amber-600/80 mt-0.5">Tindakan karantina</div>
      </div>

      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
        <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
          <span>TERVERIFIKASI</span>
          <ShieldCheck class="w-4 h-4 text-sky-600" />
        </div>
        <div class="text-2xl font-extrabold text-sky-600 dark:text-sky-400 mt-1.5">
          {{ stats.approved_count }}
        </div>
        <div class="text-[11px] text-sky-600/80 mt-0.5">Sertifikat aktif</div>
      </div>

      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-xs col-span-2 sm:col-span-1">
        <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
          <span>INSPEKSI HARI INI</span>
          <FileCheck class="w-4 h-4 text-indigo-500" />
        </div>
        <div class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1.5">
          {{ stats.today_count }}
        </div>
        <div class="text-[11px] text-indigo-500/80 mt-0.5">Real-time pelabuhan</div>
      </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 flex flex-col md:flex-row items-center justify-between gap-3 shadow-xs">
      <div class="relative w-full md:w-80">
        <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
        <input
          v-model="searchInput"
          @keyup.enter="handleFilter"
          type="text"
          placeholder="Cari nama kapal, IMO, reg no, pemeriksa..."
          class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all"
        />
      </div>

      <div class="flex items-center gap-2 w-full md:w-auto flex-wrap">
        <select
          v-model="selectedPelabuhan"
          @change="handleFilter"
          class="px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
        >
          <option value="">Semua Pelabuhan / Wilker</option>
          <option v-for="port in ports" :key="port" :value="port">{{ port }}</option>
        </select>

        <select
          v-model="selectedSertifikat"
          @change="handleFilter"
          class="px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
        >
          <option value="">Semua Jenis Sertifikat</option>
          <option value="Pembebasan">SSCEC (Pembebasan)</option>
          <option value="Pengendalian">SSCC (Pengendalian)</option>
        </select>

        <select
          v-model="selectedStatus"
          @change="handleFilter"
          class="px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
        >
          <option value="">Semua Status</option>
          <option value="submitted">Submitted (Menunggu)</option>
          <option value="verified">Verified (Petugas)</option>
          <option value="approved">Approved (Terbit)</option>
          <option value="rejected">Rejected (Ditolak)</option>
        </select>

        <button
          type="button"
          @click="handleFilter"
          class="px-3.5 py-2 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white transition-colors cursor-pointer"
        >
          Terapkan
        </button>

        <button
          v-if="searchInput || selectedPelabuhan || selectedSertifikat || selectedStatus"
          type="button"
          @click="resetFilter"
          class="px-3 py-2 text-xs text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- Main Inspections Table Container -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-950/60 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
              <th class="px-5 py-3.5">No. Registrasi</th>
              <th class="px-5 py-3.5">Identitas Kapal</th>
              <th class="px-5 py-3.5">Pelabuhan / Wilker</th>
              <th class="px-5 py-3.5">Tanggal</th>
              <th class="px-5 py-3.5">Jenis Sertifikat</th>
              <th class="px-5 py-3.5">Pemeriksa</th>
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
              <td class="px-5 py-4 font-mono font-bold text-emerald-700 dark:text-emerald-400 whitespace-nowrap">
                <div>{{ item.registration_no }}</div>
                <div class="text-[10px] text-slate-400 font-sans font-normal mt-0.5">
                  {{ item.imo_no }}
                </div>
              </td>

              <td class="px-5 py-4 font-medium text-slate-800 dark:text-slate-200">
                <div class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-1.5">
                  <span>{{ item.nama_kapal }}</span>
                  <span v-if="item.bendera" class="text-[10px] font-normal px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                    {{ item.bendera }}
                  </span>
                </div>
                <div class="text-[11px] text-slate-500 mt-0.5">
                  {{ item.detail_jenis_kapal || item.jenis_kapal || 'General Cargo' }} &bull; {{ item.berat_gt ? Number(item.berat_gt).toLocaleString() + ' GT' : '-' }}
                </div>
              </td>

              <td class="px-5 py-4 whitespace-nowrap">
                <div class="font-semibold text-slate-800 dark:text-slate-200 flex items-center gap-1">
                  <MapPin class="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                  <span>{{ item.pelabuhan || '-' }}</span>
                </div>
                <div v-if="item.lokasi_geotag" class="text-[10.5px] text-slate-400 max-w-[200px] truncate" :title="item.lokasi_geotag">
                  {{ item.lokasi_geotag }}
                </div>
              </td>

              <td class="px-5 py-4 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                <div class="font-medium text-slate-700 dark:text-slate-300">{{ formatDate(item.tanggal) }}</div>
                <div class="text-[10px] text-slate-400 font-mono">{{ item.created_at ? item.created_at.substring(11, 16) : '' }} WIB</div>
              </td>

              <td class="px-5 py-4 whitespace-nowrap">
                <span
                  class="px-2.5 py-1 rounded-full text-[11px] font-bold border inline-flex items-center gap-1.5"
                  :class="item.sertifikat_mencatat === 'Pembebasan'
                    ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                    : 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800'"
                >
                  <component :is="item.sertifikat_mencatat === 'Pembebasan' ? CheckCircle2 : AlertTriangle" class="w-3.5 h-3.5" />
                  <span>{{ item.sertifikat_mencatat === 'Pembebasan' ? 'SSCEC (Bebas)' : 'SSCC (Kendali)' }}</span>
                </span>
              </td>

              <td class="px-5 py-4 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                <div class="font-medium text-slate-800 dark:text-slate-200">{{ item.nama_pemeriksa }}</div>
                <div class="text-[10.5px] text-slate-400">{{ item.alamat_pemeriksa || 'BKK Kelas I Bandung' }}</div>
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
                    class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    title="Salin / Lihat Link Publik Dokumen"
                  >
                    <Link2 class="w-4 h-4" />
                  </button>

                  <button
                    type="button"
                    @click="openDetail(item)"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    title="Lihat Detail & Verifikasi"
                  >
                    <Eye class="w-4 h-4" />
                  </button>

                  <a
                    :href="`/ship-inspections/${item.id}/pdf`"
                    target="_blank"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    title="Cetak Bukti Registrasi / Sertifikat"
                  >
                    <Download class="w-4 h-4" />
                  </a>

                  <button
                    type="button"
                    @click="deleteInspection(item.id, item.registration_no, item.nama_kapal)"
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
                <Ship class="w-8 h-8 mx-auto mb-2 opacity-50 text-emerald-600" />
                <p class="font-medium">Belum ada data inspeksi kapal yang sesuai dengan filter.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <PaginationBar
        :meta="inspections"
        noun="permohonan sertifikat"
        activeClass="bg-emerald-600 text-white border-emerald-600 font-bold"
      />
    </div>

    <!-- Inspection Detail & Verification Modal -->
    <div
      v-if="showDetailModal && activeInspection"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
      @click.self="closeDetail"
    >
      <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-4xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-950/60">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs">
              <Ship class="w-5 h-5" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                  {{ activeInspection.nama_kapal }}
                </h3>
                <span class="px-2 py-0.5 rounded text-[10.5px] font-mono font-bold bg-emerald-100 text-emerald-800">
                  {{ activeInspection.registration_no }}
                </span>
              </div>
              <p class="text-xs text-slate-500">
                IMO: {{ activeInspection.imo_no }} &bull; Pelabuhan: {{ activeInspection.pelabuhan }} &bull; Tanggal: {{ formatDate(activeInspection.tanggal) }}
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
            :class="activeModalTab === 'overview' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'"
          >
            Ringkasan Kapal &amp; Pemeriksa
          </button>
          <button
            type="button"
            @click="activeModalTab = 'checklist'"
            class="py-3 border-b-2 transition-colors cursor-pointer flex items-center gap-1.5 shrink-0"
            :class="activeModalTab === 'checklist' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'"
          >
            Checklist 32 Area WHO IHR 2005
            <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] rounded-full">
              {{ activeInspection.areas ? activeInspection.areas.length : 0 }}
            </span>
          </button>
          <button
            type="button"
            @click="activeModalTab = 'verify'"
            class="py-3 border-b-2 transition-colors cursor-pointer flex items-center gap-1 shrink-0"
            :class="activeModalTab === 'verify' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'"
          >
            <ShieldCheck class="w-3.5 h-3.5" />
            <span>Verifikasi &amp; Penerbitan</span>
          </button>
        </div>

        <div class="p-6 overflow-y-auto flex-1 space-y-6 text-xs">
          <div v-if="activeModalTab === 'overview'" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 space-y-2.5">
                <h4 class="font-bold text-slate-900 dark:text-white text-xs flex items-center gap-1.5">
                  <Compass class="w-4 h-4 text-emerald-600" />
                  <span>Spesifikasi &amp; Dokumen Kapal</span>
                </h4>
                <div class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Nama Kapal:</span>
                    <strong class="text-slate-900 dark:text-white">{{ activeInspection.nama_kapal }}</strong>
                  </div>
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Nomor IMO:</span>
                    <span class="font-mono">{{ activeInspection.imo_no }}</span>
                  </div>
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Bendera Kebangsaan:</span>
                    <span>{{ activeInspection.bendera || '-' }}</span>
                  </div>
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Tipe / Kategori Kapal:</span>
                    <span>{{ activeInspection.detail_jenis_kapal || activeInspection.jenis_kapal || '-' }}</span>
                  </div>
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Tonase Kotor (GT):</span>
                    <span>{{ activeInspection.berat_gt ? Number(activeInspection.berat_gt).toLocaleString() + ' GT' : '-' }}</span>
                  </div>
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Kondisi Palka:</span>
                    <span class="font-semibold">{{ activeInspection.kondisi_palka || '-' }}</span>
                  </div>
                </div>
              </div>

              <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 space-y-2.5">
                <h4 class="font-bold text-slate-900 dark:text-white text-xs flex items-center gap-1.5">
                  <Anchor class="w-4 h-4 text-emerald-600" />
                  <span>Kekarantinaan &amp; Wilker</span>
                </h4>
                <div class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Pelabuhan Inspeksi:</span>
                    <strong class="text-slate-900 dark:text-white">{{ activeInspection.pelabuhan }}</strong>
                  </div>
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Tanggal Pemeriksaan:</span>
                    <span>{{ formatDate(activeInspection.tanggal) }}</span>
                  </div>
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Nama Pemeriksa:</span>
                    <span>{{ activeInspection.nama_pemeriksa }}</span>
                  </div>
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Alamat / Instansi:</span>
                    <span>{{ activeInspection.alamat_pemeriksa || 'BKK Kelas I Bandung' }}</span>
                  </div>
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Jenis Sertifikat:</span>
                    <strong :class="activeInspection.sertifikat_mencatat === 'Pembebasan' ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-400'">
                      {{ activeInspection.sertifikat_mencatat === 'Pembebasan' ? 'SSCEC (Pembebasan Sanitasi)' : 'SSCC (Pengendalian Sanitasi)' }}
                    </strong>
                  </div>
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Pejabat Penerbit:</span>
                    <span>{{ activeInspection.pejabat_penerbit || '-' }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="p-4 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900">
              <h5 class="font-bold text-emerald-900 dark:text-emerald-300 mb-1 flex items-center gap-1.5">
                <Info class="w-4 h-4 text-emerald-600" />
                <span>Keterangan Hasil Inspeksi Sanitasi:</span>
              </h5>
              <p class="text-slate-700 dark:text-slate-300 leading-relaxed italic">
                "{{ activeInspection.keterangan || 'Tidak ada catatan tambahan khusus.' }}"
              </p>
            </div>

            <div v-if="activeInspection.tanda_tangan" class="p-4 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
              <h5 class="font-bold text-slate-800 dark:text-slate-200 mb-2">Tanda Tangan Digital Pemeriksa Sanitasi:</h5>
              <div class="p-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white inline-block">
                <img :src="activeInspection.tanda_tangan" alt="Tanda Tangan" class="h-20 object-contain" />
              </div>
              <p class="text-[10px] text-slate-400 mt-1">Ditandatangani secara digital oleh {{ activeInspection.nama_pemeriksa }}</p>
            </div>
          </div>

          <div v-if="activeModalTab === 'checklist'" class="space-y-4">
            <div class="flex items-center justify-between text-xs text-slate-500">
              <span>Evaluasi 32 Ruang / Area Kapal Berdasarkan WHO IHR 2005:</span>
              <span class="font-semibold text-emerald-600">Standar Internasional</span>
            </div>

            <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold text-[11px]">
                  <tr>
                    <th class="px-4 py-2.5">No</th>
                    <th class="px-4 py-2.5">Area Pemeriksaan (WHO IHR 2005)</th>
                    <th class="px-4 py-2.5">Temuan Lapangan</th>
                    <th class="px-4 py-2.5">Hasil Pemeriksaan Sampel</th>
                    <th class="px-4 py-2.5">Tindakan Pengendalian</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                  <tr v-for="(area, aIdx) in (activeInspection.formatted_areas || activeInspection.areas || [])" :key="aIdx" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                    <td class="px-4 py-2.5 text-slate-400 font-mono">{{ area.no || (aIdx + 1) }}</td>
                    <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-slate-200">
                      <div>{{ area.nama || area.area_name }}</div>
                      <span v-if="area.kategori" class="text-[10px] text-slate-400 font-normal">{{ area.kategori }}</span>
                    </td>
                    <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400">{{ area.finding || area.keterangan || area.bukti || 'Nihil tanda vektor / kondisi bersih' }}</td>
                    <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400">{{ area.sample || area.sampel || 'Sesuai Standar' }}</td>
                    <td class="px-4 py-2.5">
                      <span
                        class="px-2 py-0.5 rounded text-[10px] font-semibold border"
                        :class="(area.result || area.status || '').toLowerCase().includes('ditemukan') || (area.result || area.status || '').toLowerCase().includes('kendali') ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
                      >
                        {{ area.result || area.status || 'Memenuhi Syarat' }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="!activeInspection.areas || activeInspection.areas.length === 0">
                    <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                      Checklist area tidak tersedia atau disimpan dalam format ringkasan.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div v-if="activeModalTab === 'verify'" class="space-y-4">
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 space-y-4">
              <h4 class="font-bold text-slate-900 dark:text-white text-sm">Formulir Verifikasi Pejabat Karantina</h4>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-1">Status Verifikasi:</label>
                  <select
                    v-model="statusForm.status"
                    class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white font-medium focus:ring-2 focus:ring-emerald-500"
                  >
                    <option value="submitted">Submitted (Menunggu Pemeriksaan)</option>
                    <option value="verified">Verified (Telah Diverifikasi Lapangan)</option>
                    <option value="approved">Approved (Diterbitkan Sertifikat)</option>
                    <option value="rejected">Rejected (Ditolak / Ditunda)</option>
                  </select>
                </div>

                <div>
                  <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-1">Penerbitan Sertifikat:</label>
                  <select
                    v-model="statusForm.sertifikat_mencatat"
                    class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white font-medium focus:ring-2 focus:ring-emerald-500"
                  >
                    <option value="Pembebasan">SSCEC (Sertifikat Pembebasan Sanitasi Kapal)</option>
                    <option value="Pengendalian">SSCC (Sertifikat Pengendalian Sanitasi Kapal)</option>
                  </select>
                </div>

                <div class="md:col-span-2">
                  <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-1">Nama Pejabat Penerbit Sertifikat:</label>
                  <input
                    v-model="statusForm.pejabat_penerbit"
                    type="text"
                    placeholder="Contoh: dr. H. Rinaldi, Sp.KP / Kepala BKK Bandung"
                    class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500"
                  />
                </div>

                <div class="md:col-span-2">
                  <label class="block text-slate-700 dark:text-slate-300 font-semibold mb-1">Catatan / Rekomendasi Resmi Karantina:</label>
                  <textarea
                    v-model="statusForm.keterangan"
                    rows="3"
                    placeholder="Catatan kebersihan kapal, rekomendasi pengendalian serangga/tikus, atau masa berlaku sertifikat..."
                    class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 flex items-center justify-between">
          <div class="text-xs text-slate-400">
            Diverifikasi oleh: <strong class="text-slate-700 dark:text-slate-300">{{ activeInspection.verifier ? activeInspection.verifier.name : 'Belum diverifikasi' }}</strong>
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
              class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition-colors shadow-xs cursor-pointer flex items-center gap-1.5"
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
      title="Link Publik Hasil Dokumen Sanitasi Kapal"
      subtitle="Dapat diakses publik tanpa login untuk melihat & membagikan sertifikat."
      :url="shareUrl"
      :meta-rows="shareMetaRows"
      accent="teal"
      @close="showShareModal = false"
    />
  </div>
</template>

