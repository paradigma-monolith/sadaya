<script setup>
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import { useDate } from '../../composables/useDate';
import {
  Ship,
  Anchor,
  ShieldCheck,
  AlertTriangle,
  FileCheck,
  FileText,
  Search,
  Filter,
  Download,
  Eye,
  CheckCircle2,
  XCircle,
  ExternalLink,
  Plus,
  Trash2,
  X,
  FileSpreadsheet,
  Camera,
  MapPin,
  Clock,
  Compass,
  Check,
  Info,
  Link2,
  Copy,
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

const handleFilter = () => {
  router.get('/ship-inspections', {
    search: searchInput.value || undefined,
    pelabuhan: selectedPelabuhan.value || undefined,
    sertifikat: selectedSertifikat.value || undefined,
    status: selectedStatus.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const resetFilter = () => {
  searchInput.value = '';
  selectedPelabuhan.value = '';
  selectedSertifikat.value = '';
  selectedStatus.value = '';
  router.get('/ship-inspections', {}, { preserveState: true });
};

// Modal Inspection Details & Verification
const activeInspection = ref(null);
const showDetailModal = ref(false);
const showShareModal = ref(false);
const shareTarget = ref(null);
const copied = ref(false);

const getPublicUrl = (regNo) => {
  if (typeof window === 'undefined') return `/travelconsent/receipt_sanitasi/${regNo}`;
  return `${window.location.origin}/travelconsent/receipt_sanitasi/${regNo}`;
};

const openShareModal = (item) => {
  shareTarget.value = item;
  copied.value = false;
  showShareModal.value = true;
};

const copyPublicUrl = async (url) => {
  try {
    await navigator.clipboard.writeText(url);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2500);
  } catch (e) {
    const input = document.createElement('input');
    input.value = url;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2500);
  }
};
const activeTab = ref('overview'); // 'overview' | 'checklist' | 'verify'

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
  activeTab.value = 'overview';
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
  <Head title="Manajemen Inspeksi Sanitasi Kapal &bull; BKK Bandung" />

  <HybridNavbar mode="inertia" currentRoute="ship-inspections">
    <main class="w-full px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">
      
      <!-- Top Title & Quick Action Bar -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white border border-slate-200/80 rounded-3xl p-6 shadow-xs">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-2">
            <Anchor class="w-3.5 h-3.5" />
            <span>Kekarantinaan Kesehatan Pelabuhan Laut (BKK Kelas I Bandung)</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            Data Inspeksi Sanitasi Kapal (SSCC / SSCEC)
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-3xl leading-relaxed">
            Pusat manajemen dan penerbitan Sertifikat Sanitasi Kapal (<em class="text-slate-700 font-medium">Ship Sanitation Control / Exemption Certificate</em>) standar WHO IHR 2005 di Wilayah Kerja Patimban, Cirebon, Indramayu, dan Pelabuhan Ratu.
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
            class="px-3.5 py-2.5 rounded-xl font-semibold text-xs text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors flex items-center gap-1.5"
            title="Unduh Data Inspeksi Kapal ke Excel"
          >
            <FileSpreadsheet class="w-4 h-4 text-emerald-600" />
            <span>Ekspor Excel</span>
          </a>
        </div>
      </div>

      <!-- Live Metric Cards Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
        <!-- Total -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>TOTAL SERTIFIKAT</span>
            <Ship class="w-4 h-4 text-emerald-600" />
          </div>
          <div class="text-2xl font-extrabold text-slate-900 mt-1.5">
            {{ stats.total }}
          </div>
          <div class="text-[11px] text-slate-400 mt-0.5">Semua pelabuhan</div>
        </div>

        <!-- SSCEC Pembebasan -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>PEMBEBASAN (SSCEC)</span>
            <CheckCircle2 class="w-4 h-4 text-emerald-600" />
          </div>
          <div class="text-2xl font-extrabold text-emerald-600 mt-1.5">
            {{ stats.pembebasan_count }}
          </div>
          <div class="text-[11px] text-emerald-600/80 mt-0.5">
            Nihil tindakan pengendalian
          </div>
        </div>

        <!-- SSCC Pengendalian -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>PENGENDALIAN (SSCC)</span>
            <AlertTriangle class="w-4 h-4 text-amber-500" />
          </div>
          <div class="text-2xl font-extrabold text-amber-600 mt-1.5">
            {{ stats.pengendalian_count }}
          </div>
          <div class="text-[11px] text-amber-600/80 mt-0.5">Tindakan karantina</div>
        </div>

        <!-- Terbit / Approved -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>TERVERIFIKASI</span>
            <ShieldCheck class="w-4 h-4 text-sky-600" />
          </div>
          <div class="text-2xl font-extrabold text-sky-600 mt-1.5">
            {{ stats.approved_count }}
          </div>
          <div class="text-[11px] text-sky-600/80 mt-0.5">Sertifikat aktif</div>
        </div>

        <!-- Hari Ini -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs col-span-2 sm:col-span-1">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>INSPEKSI HARI INI</span>
            <FileCheck class="w-4 h-4 text-indigo-500" />
          </div>
          <div class="text-2xl font-extrabold text-indigo-600 mt-1.5">
            {{ stats.today_count }}
          </div>
          <div class="text-[11px] text-indigo-500/80 mt-0.5">Real-time pelabuhan</div>
        </div>
      </div>

      <!-- Filters & Search Toolbar -->
      <div class="bg-white border border-slate-200/80 rounded-2xl p-4 flex flex-col md:flex-row items-center justify-between gap-3 shadow-xs">
        <div class="relative w-full md:w-80">
          <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            v-model="searchInput"
            @keyup.enter="handleFilter"
            type="text"
            placeholder="Cari nama kapal, IMO, reg no, pemeriksa..."
            class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all"
          />
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto flex-wrap">
          <!-- Pelabuhan -->
          <select
            v-model="selectedPelabuhan"
            @change="handleFilter"
            class="px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >
            <option value="">Semua Pelabuhan / Wilker</option>
            <option v-for="port in ports" :key="port" :value="port">{{ port }}</option>
          </select>

          <!-- Jenis Sertifikat -->
          <select
            v-model="selectedSertifikat"
            @change="handleFilter"
            class="px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >
            <option value="">Semua Jenis Sertifikat</option>
            <option value="Pembebasan">SSCEC (Pembebasan)</option>
            <option value="Pengendalian">SSCC (Pengendalian)</option>
          </select>

          <!-- Status -->
          <select
            v-model="selectedStatus"
            @change="handleFilter"
            class="px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500"
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
            class="px-3 py-2 text-xs text-slate-500 hover:text-slate-900 transition-colors cursor-pointer"
          >
            Reset
          </button>
        </div>
      </div>

      <!-- Main Inspections Table Container -->
      <div class="bg-white border border-slate-200/80 rounded-3xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="border-b border-slate-200/80 bg-slate-50/75 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
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
            <tbody class="divide-y divide-slate-100">
              <tr
                v-for="item in inspections.data"
                :key="item.id"
                class="hover:bg-slate-50/80 transition-colors"
              >
                <!-- Registration No -->
                <td class="px-5 py-4 font-mono font-bold text-emerald-700 whitespace-nowrap">
                  <div>{{ item.registration_no }}</div>
                  <div class="text-[10px] text-slate-400 font-sans font-normal mt-0.5">
                    {{ item.imo_no }}
                  </div>
                </td>

                <!-- Identitas Kapal -->
                <td class="px-5 py-4 font-medium text-slate-800">
                  <div class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                    <span>{{ item.nama_kapal }}</span>
                    <span v-if="item.bendera" class="text-[10px] font-normal px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">
                      {{ item.bendera }}
                    </span>
                  </div>
                  <div class="text-[11px] text-slate-500 mt-0.5">
                    {{ item.detail_jenis_kapal || item.jenis_kapal || 'General Cargo' }} &bull; {{ item.berat_gt ? Number(item.berat_gt).toLocaleString() + ' GT' : '-' }}
                  </div>
                </td>

                <!-- Pelabuhan -->
                <td class="px-5 py-4 whitespace-nowrap">
                  <div class="font-semibold text-slate-800 flex items-center gap-1">
                    <MapPin class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" />
                    <span>{{ item.pelabuhan || '-' }}</span>
                  </div>
                  <div v-if="item.lokasi_geotag" class="text-[10.5px] text-slate-400 max-w-[200px] truncate" :title="item.lokasi_geotag">
                    {{ item.lokasi_geotag }}
                  </div>
                </td>

                <!-- Tanggal -->
                <td class="px-5 py-4 text-slate-600 whitespace-nowrap">
                  <div class="font-medium text-slate-700">{{ formatDate(item.tanggal) }}</div>
                  <div class="text-[10px] text-slate-400 font-mono">{{ item.created_at ? item.created_at.substring(11, 16) : '' }} WIB</div>
                </td>

                <!-- Jenis Sertifikat -->
                <td class="px-5 py-4 whitespace-nowrap">
                  <span
                    class="px-2.5 py-1 rounded-full text-[11px] font-bold border inline-flex items-center gap-1.5"
                    :class="item.sertifikat_mencatat === 'Pembebasan'
                      ? 'bg-emerald-50 text-emerald-800 border-emerald-200'
                      : 'bg-amber-50 text-amber-800 border-amber-200'"
                  >
                    <component :is="item.sertifikat_mencatat === 'Pembebasan' ? CheckCircle2 : AlertTriangle" class="w-3.5 h-3.5" />
                    <span>{{ item.sertifikat_mencatat === 'Pembebasan' ? 'SSCEC (Bebas)' : 'SSCC (Kendali)' }}</span>
                  </span>
                </td>

                <!-- Pemeriksa -->
                <td class="px-5 py-4 text-slate-600 whitespace-nowrap">
                  <div class="font-medium text-slate-800">{{ item.nama_pemeriksa }}</div>
                  <div class="text-[10.5px] text-slate-400">{{ item.alamat_pemeriksa || 'BKK Kelas I Bandung' }}</div>
                </td>

                <!-- Workflow Status -->
                <td class="px-5 py-4 whitespace-nowrap">
                  <span
                    class="px-2.5 py-1 rounded-full text-[10.5px] font-mono font-medium uppercase border"
                    :class="{
                      'bg-emerald-50 text-emerald-700 border-emerald-200': item.status === 'approved',
                      'bg-sky-50 text-sky-700 border-sky-200': item.status === 'verified',
                      'bg-amber-50 text-amber-700 border-amber-200': item.status === 'submitted',
                      'bg-rose-50 text-rose-700 border-rose-200': item.status === 'rejected'
                    }"
                  >
                    {{ item.status }}
                  </span>
                </td>

                <!-- Actions -->
                <td class="px-5 py-4 text-right whitespace-nowrap">
                  <div class="inline-flex items-center gap-1.5">
                    <button
                      type="button"
                      @click="openShareModal(item)"
                      class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 transition-colors cursor-pointer"
                      title="Salin / Lihat Link Publik Dokumen"
                    >
                      <Link2 class="w-4 h-4" />
                    </button>

                    <button
                      type="button"
                      @click="openDetail(item)"
                      class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 transition-colors cursor-pointer"
                      title="Lihat Detail & Verifikasi"
                    >
                      <Eye class="w-4 h-4" />
                    </button>

                    <a
                      :href="`/ship-inspections/${item.id}/pdf`"
                      target="_blank"
                      class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-colors cursor-pointer"
                      title="Cetak Bukti Registrasi / Sertifikat"
                    >
                      <Download class="w-4 h-4" />
                    </a>

                    <button
                      type="button"
                      @click="deleteInspection(item.id, item.registration_no, item.nama_kapal)"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
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

        <!-- Pagination -->
        <div
          v-if="inspections.links && inspections.links.length > 3"
          class="px-5 py-4 border-t border-slate-200/80 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500"
        >
          <div>
            Menampilkan <strong class="text-slate-800">{{ inspections.from || 0 }}</strong> - <strong class="text-slate-800">{{ inspections.to || 0 }}</strong> dari <strong class="text-slate-800">{{ inspections.total }}</strong> permohonan sertifikat
          </div>

          <div class="flex items-center gap-1 flex-wrap">
            <template v-for="(link, lIdx) in inspections.links" :key="lIdx">
              <Link
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
                class="px-3 py-1.5 rounded-lg border text-xs font-medium transition-colors"
                :class="link.active ? 'bg-emerald-600 text-white border-emerald-600 font-bold' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
                preserve-scroll
              />
              <span
                v-else
                v-html="link.label"
                class="px-3 py-1.5 rounded-lg border border-slate-100 text-slate-300 text-xs cursor-not-allowed"
              />
            </template>
          </div>
        </div>
      </div>

    </main>

    <!-- Inspection Detail & Verification Modal -->
    <div
      v-if="showDetailModal && activeInspection"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
      @click.self="closeDetail"
    >
      <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden">
        
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs">
              <Ship class="w-5 h-5" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-base font-extrabold text-slate-900">
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
            class="p-2 rounded-xl text-slate-400 hover:text-slate-800 hover:bg-slate-200/60 transition-colors"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Tabs -->
        <div class="flex border-b border-slate-200 px-6 bg-white gap-6 text-xs font-semibold">
          <button
            type="button"
            @click="activeTab = 'overview'"
            class="py-3 border-b-2 transition-colors cursor-pointer"
            :class="activeTab === 'overview' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-900'"
          >
            Ringkasan Kapal &amp; Pemeriksa
          </button>
          <button
            type="button"
            @click="activeTab = 'checklist'"
            class="py-3 border-b-2 transition-colors cursor-pointer flex items-center gap-1.5"
            :class="activeTab === 'checklist' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-900'"
          >
            Checklist 32 Area WHO IHR 2005
            <span class="px-1.5 py-0.2 bg-slate-100 text-slate-600 text-[10px] rounded-full">
              {{ activeInspection.areas ? activeInspection.areas.length : 0 }}
            </span>
          </button>
          <button
            type="button"
            @click="activeTab = 'verify'"
            class="py-3 border-b-2 transition-colors cursor-pointer flex items-center gap-1"
            :class="activeTab === 'verify' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-900'"
          >
            <ShieldCheck class="w-3.5 h-3.5" />
            <span>Verifikasi &amp; Penerbitan</span>
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto flex-1 space-y-6 text-xs">
          
          <!-- TAB 1: OVERVIEW -->
          <div v-if="activeTab === 'overview'" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Vessel Details Box -->
              <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                <h4 class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                  <Compass class="w-4 h-4 text-emerald-600" />
                  <span>Spesifikasi &amp; Dokumen Kapal</span>
                </h4>
                <div class="divide-y divide-slate-200 text-slate-700">
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Nama Kapal:</span>
                    <strong class="text-slate-900">{{ activeInspection.nama_kapal }}</strong>
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

              <!-- Inspection Metadata Box -->
              <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                <h4 class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                  <Anchor class="w-4 h-4 text-emerald-600" />
                  <span>Kekarantinaan &amp; Wilker</span>
                </h4>
                <div class="divide-y divide-slate-200 text-slate-700">
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Pelabuhan Inspeksi:</span>
                    <strong class="text-slate-900">{{ activeInspection.pelabuhan }}</strong>
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
                    <strong :class="activeInspection.sertifikat_mencatat === 'Pembebasan' ? 'text-emerald-700' : 'text-amber-700'">
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

            <!-- Notes & Remarks -->
            <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100">
              <h5 class="font-bold text-emerald-900 mb-1 flex items-center gap-1.5">
                <Info class="w-4 h-4 text-emerald-600" />
                <span>Keterangan Hasil Inspeksi Sanitasi:</span>
              </h5>
              <p class="text-slate-700 leading-relaxed italic">
                "{{ activeInspection.keterangan || 'Tidak ada catatan tambahan khusus.' }}"
              </p>
            </div>

            <!-- Digital Signature Preview -->
            <div v-if="activeInspection.tanda_tangan" class="p-4 rounded-2xl bg-white border border-slate-200">
              <h5 class="font-bold text-slate-800 mb-2">Tanda Tangan Digital Pemeriksa Sanitasi:</h5>
              <div class="p-2 border border-slate-200 rounded-xl bg-slate-50 inline-block">
                <img :src="activeInspection.tanda_tangan" alt="Tanda Tangan" class="h-20 object-contain" />
              </div>
              <p class="text-[10px] text-slate-400 mt-1">Ditandatangani secara digital oleh {{ activeInspection.nama_pemeriksa }}</p>
            </div>
          </div>

          <!-- TAB 2: CHECKLIST 32 AREAS -->
          <div v-if="activeTab === 'checklist'" class="space-y-4">
            <div class="flex items-center justify-between text-xs text-slate-500">
              <span>Evaluasi 32 Ruang / Area Kapal Berdasarkan WHO IHR 2005:</span>
              <span class="font-semibold text-emerald-600">Standar Internasional</span>
            </div>

            <div class="border border-slate-200 rounded-2xl overflow-hidden">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold text-[11px]">
                  <tr>
                    <th class="px-4 py-2.5">No</th>
                    <th class="px-4 py-2.5">Area Pemeriksaan (WHO IHR 2005)</th>
                    <th class="px-4 py-2.5">Temuan Lapangan</th>
                    <th class="px-4 py-2.5">Hasil Pemeriksaan Sampel</th>
                    <th class="px-4 py-2.5">Tindakan Pengendalian</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="(area, aIdx) in (activeInspection.formatted_areas || activeInspection.areas || [])" :key="aIdx" class="hover:bg-slate-50/60">
                    <td class="px-4 py-2.5 text-slate-400 font-mono">{{ area.no || (aIdx + 1) }}</td>
                    <td class="px-4 py-2.5 font-semibold text-slate-800">
                      <div>{{ area.nama || area.area_name }}</div>
                      <span v-if="area.kategori" class="text-[10px] text-slate-400 font-normal">{{ area.kategori }}</span>
                    </td>
                    <td class="px-4 py-2.5 text-slate-600">{{ area.finding || area.keterangan || area.bukti || 'Nihil tanda vektor / kondisi bersih' }}</td>
                    <td class="px-4 py-2.5 text-slate-600">{{ area.sample || area.sampel || 'Sesuai Standar' }}</td>
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

          <!-- TAB 3: VERIFY & APPROVE -->
          <div v-if="activeTab === 'verify'" class="space-y-4">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
              <h4 class="font-bold text-slate-900 text-sm">Formulir Verifikasi Pejabat Karantina</h4>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Status -->
                <div>
                  <label class="block text-slate-700 font-semibold mb-1">Status Verifikasi:</label>
                  <select
                    v-model="statusForm.status"
                    class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 font-medium focus:ring-2 focus:ring-emerald-500"
                  >
                    <option value="submitted">Submitted (Menunggu Pemeriksaan)</option>
                    <option value="verified">Verified (Telah Diverifikasi Lapangan)</option>
                    <option value="approved">Approved (Diterbitkan Sertifikat)</option>
                    <option value="rejected">Rejected (Ditolak / Ditunda)</option>
                  </select>
                </div>

                <!-- Jenis Sertifikat -->
                <div>
                  <label class="block text-slate-700 font-semibold mb-1">Penerbitan Sertifikat:</label>
                  <select
                    v-model="statusForm.sertifikat_mencatat"
                    class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 font-medium focus:ring-2 focus:ring-emerald-500"
                  >
                    <option value="Pembebasan">SSCEC (Sertifikat Pembebasan Sanitasi Kapal)</option>
                    <option value="Pengendalian">SSCC (Sertifikat Pengendalian Sanitasi Kapal)</option>
                  </select>
                </div>

                <!-- Pejabat Penerbit -->
                <div class="md:col-span-2">
                  <label class="block text-slate-700 font-semibold mb-1">Nama Pejabat Penerbit Sertifikat:</label>
                  <input
                    v-model="statusForm.pejabat_penerbit"
                    type="text"
                    placeholder="Contoh: dr. H. Rinaldi, Sp.KP / Kepala BKK Bandung"
                    class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 focus:ring-2 focus:ring-emerald-500"
                  />
                </div>

                <!-- Catatan / Keterangan -->
                <div class="md:col-span-2">
                  <label class="block text-slate-700 font-semibold mb-1">Catatan / Rekomendasi Resmi Karantina:</label>
                  <textarea
                    v-model="statusForm.keterangan"
                    rows="3"
                    placeholder="Catatan kebersihan kapal, rekomendasi pengendalian serangga/tikus, atau masa berlaku sertifikat..."
                    class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 focus:ring-2 focus:ring-emerald-500"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
          <div class="text-xs text-slate-400">
            Diverifikasi oleh: <strong class="text-slate-700">{{ activeInspection.verifier ? activeInspection.verifier.name : 'Belum diverifikasi' }}</strong>
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="closeDetail"
              class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200/60 transition-colors cursor-pointer"
            >
              Tutup
            </button>

            <button
              v-if="activeTab === 'verify'"
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

    <!-- Modal Link Publik Hasil Dokumen Sanitasi Kapal -->
    <div 
      v-if="showShareModal && shareTarget" 
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
      @click.self="showShareModal = false"
    >
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl p-6 relative">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                <Link2 class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white leading-tight">
                  Link Publik Hasil Dokumen Sanitasi Kapal
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                  Dapat diakses publik tanpa login untuk melihat &amp; membagikan sertifikat.
                </p>
              </div>
            </div>
            <button 
              type="button" 
              @click="showShareModal = false" 
              class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
            >
              <X class="w-4 h-4" />
            </button>
          </div>

          <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-3 mb-4 text-xs space-y-1.5 border border-slate-200/80 dark:border-slate-700/80">
            <div class="flex justify-between items-center">
              <span class="text-slate-500">Nomor Registrasi:</span>
              <span class="font-mono font-bold text-teal-700 dark:text-teal-400">{{ shareTarget.registration_no }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500">Nama Kapal &bull; IMO:</span>
              <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[240px]">
                {{ shareTarget.nama_kapal }} &bull; {{ shareTarget.imo_no }}
              </span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500">Pelabuhan:</span>
              <span class="font-medium text-slate-700 dark:text-slate-300">
                {{ shareTarget.pelabuhan }}
              </span>
            </div>
          </div>

          <div class="space-y-2 mb-5">
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
              Tautan Dokumen Publik:
            </label>
            <div class="flex items-center gap-2">
              <input 
                type="text" 
                readonly 
                :value="getPublicUrl(shareTarget.registration_no)" 
                class="flex-1 px-3 py-2 text-xs font-mono bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 select-all focus:outline-none focus:ring-2 focus:ring-teal-500"
                @focus="$event.target.select()"
              />
              <button
                type="button"
                @click="copyPublicUrl(getPublicUrl(shareTarget.registration_no))"
                class="px-4 py-2 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 shrink-0 shadow-xs cursor-pointer text-white"
                :class="copied ? 'bg-emerald-600' : 'bg-slate-900 hover:bg-slate-800'"
              >
                <component :is="copied ? Check : Copy" class="w-3.5 h-3.5" />
                <span>{{ copied ? 'Tersalin!' : 'Salin Link' }}</span>
              </button>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
            <a 
              :href="getPublicUrl(shareTarget.registration_no)" 
              target="_blank" 
              class="px-3.5 py-2 rounded-xl text-xs font-semibold text-teal-700 dark:text-teal-400 bg-teal-50 hover:bg-teal-100 dark:bg-teal-950/60 dark:hover:bg-teal-900/60 border border-teal-200 dark:border-teal-800 transition-colors inline-flex items-center gap-1.5"
            >
              <ExternalLink class="w-3.5 h-3.5" />
              <span>Buka Dokumen</span>
            </a>
            <button 
              type="button" 
              @click="showShareModal = false" 
              class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>

  </HybridNavbar>
</template>

