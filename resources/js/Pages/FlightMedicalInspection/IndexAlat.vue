<script setup>
import { ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import { useDate } from '../../composables/useDate';
import {
  Activity,
  Briefcase,
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
  Clock,
  Plane,
  Check,
  Info,
} from 'lucide-vue-next';

const props = defineProps({
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

const searchInput = ref(props.filters.search || '');
const selectedEmbarkasi = ref(props.filters.embarkasi || '');
const selectedStatus = ref(props.filters.status || '');

const handleFilter = () => {
  router.get('/flight-medical-inspections/alat', {
    search: searchInput.value || undefined,
    embarkasi: selectedEmbarkasi.value || undefined,
    status: selectedStatus.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const resetFilter = () => {
  searchInput.value = '';
  selectedEmbarkasi.value = '';
  selectedStatus.value = '';
  router.get('/flight-medical-inspections/alat', {}, { preserveState: true });
};

// Modal Detail & Verification
const activeItem = ref(null);
const showDetailModal = ref(false);
const activeTab = ref('overview'); // 'overview' | 'p3k' | 'emergensi' | 'verify'

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
  activeTab.value = 'overview';
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
  if (confirm(`Apakah Anda yakin ingin menghapus data pemakaian alat kesehatan ${regNo}?`)) {
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
  <Head title="Pemakaian Alkes P3K &amp; Emergensi &bull; BKK Bandung" />

  <HybridNavbar mode="inertia" currentRoute="flight-medical-alat">
    <main class="w-full px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">
      
      <!-- Top Title & Quick Action Bar -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white border border-slate-200/80 rounded-3xl p-6 shadow-xs">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-semibold bg-blue-50 text-blue-700 border border-blue-200 mb-2">
            <Plane class="w-3.5 h-3.5" />
            <span>Pengawasan Faktor Risiko Alat Angkut &bull; Penerbangan Haji &amp; Pesawat</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            Pemakaian Alat Kesehatan Kotak P3K &amp; Emergensi
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-3xl leading-relaxed">
            Pengawasan dan rekam medis pemakaian First Aid Kit (17 alat) &amp; Emergency Medical Kit (21 alat) pada penerbangan maskapai dan kloter haji di Wilayah Kerja BKK Kelas I Bandung.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <a
            href="/travelconsent/public_alat"
            target="_blank"
            class="px-4 py-2.5 rounded-xl font-bold text-xs text-white bg-blue-600 hover:bg-blue-500 transition-all shadow-sm flex items-center gap-1.5 cursor-pointer"
          >
            <Plus class="w-4 h-4" />
            <span>Form Input Alkes ↗</span>
          </a>

          <a
            :href="`/flight-medical-inspections/export?type=alat&format=xlsx${selectedEmbarkasi ? '&embarkasi=' + encodeURIComponent(selectedEmbarkasi) : ''}${selectedStatus ? '&status=' + encodeURIComponent(selectedStatus) : ''}`"
            class="px-3.5 py-2.5 rounded-xl font-semibold text-xs text-blue-800 bg-blue-50 hover:bg-blue-100 border border-blue-200 transition-colors flex items-center gap-1.5"
            title="Unduh Data Alkes ke Excel"
          >
            <FileSpreadsheet class="w-4 h-4 text-blue-600" />
            <span>Ekspor Excel</span>
          </a>
        </div>
      </div>

      <!-- Live Metric Cards Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
        <!-- Total -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>TOTAL PEMAKAIAN</span>
            <Activity class="w-4 h-4 text-blue-600" />
          </div>
          <div class="text-2xl font-extrabold text-slate-900 mt-1.5">
            {{ stats.total }}
          </div>
          <div class="text-[11px] text-slate-400 mt-0.5">Laporan tercatat</div>
        </div>

        <!-- Kloter -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>KLOTER DILAYANI</span>
            <Plane class="w-4 h-4 text-indigo-600" />
          </div>
          <div class="text-2xl font-extrabold text-indigo-600 mt-1.5">
            {{ stats.total_kloter }}
          </div>
          <div class="text-[11px] text-indigo-500/80 mt-0.5">Embarkasi KJT &amp; JKS</div>
        </div>

        <!-- Total Item Terpakai -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>TOTAL ALAT TERPAKAI</span>
            <Briefcase class="w-4 h-4 text-amber-500" />
          </div>
          <div class="text-2xl font-extrabold text-amber-600 mt-1.5">
            {{ stats.total_items_used }}
          </div>
          <div class="text-[11px] text-amber-500/80 mt-0.5">Pcs perlengkapan</div>
        </div>

        <!-- Terverifikasi Approved -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>TERVERIFIKASI</span>
            <CheckCircle2 class="w-4 h-4 text-emerald-600" />
          </div>
          <div class="text-2xl font-extrabold text-emerald-600 mt-1.5">
            {{ stats.approved_count }}
          </div>
          <div class="text-[11px] text-emerald-600/80 mt-0.5">Disahkan karantina</div>
        </div>

        <!-- Menunggu Submitted -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs col-span-2 sm:col-span-1">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>MENUNGGU VERIFIKASI</span>
            <FileCheck class="w-4 h-4 text-sky-500" />
          </div>
          <div class="text-2xl font-extrabold text-sky-600 mt-1.5">
            {{ stats.submitted_count }}
          </div>
          <div class="text-[11px] text-sky-500/80 mt-0.5">Proses review</div>
        </div>
      </div>

      <!-- Filters Toolbar -->
      <div class="bg-white border border-slate-200/80 rounded-2xl p-4 flex flex-col md:flex-row items-center justify-between gap-3 shadow-xs">
        <div class="relative w-full md:w-80">
          <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            v-model="searchInput"
            @keyup.enter="handleFilter"
            type="text"
            placeholder="Cari kloter, embarkasi, awak kabin, pelapor..."
            class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
          />
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto flex-wrap">
          <!-- Embarkasi -->
          <select
            v-model="selectedEmbarkasi"
            @change="handleFilter"
            class="px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Semua Embarkasi</option>
            <option v-for="emb in embarkasiList" :key="emb" :value="emb">{{ emb }}</option>
          </select>

          <!-- Status -->
          <select
            v-model="selectedStatus"
            @change="handleFilter"
            class="px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
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
            class="px-3.5 py-2 text-xs font-bold rounded-xl bg-blue-600 hover:bg-blue-500 text-white transition-colors cursor-pointer"
          >
            Terapkan
          </button>

          <button
            v-if="searchInput || selectedEmbarkasi || selectedStatus"
            type="button"
            @click="resetFilter"
            class="px-3 py-2 text-xs text-slate-500 hover:text-slate-900 transition-colors cursor-pointer"
          >
            Reset
          </button>
        </div>
      </div>

      <!-- Main Table -->
      <div class="bg-white border border-slate-200/80 rounded-3xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="border-b border-slate-200/80 bg-slate-50/75 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                <th class="px-5 py-3.5">No. Registrasi</th>
                <th class="px-5 py-3.5">Embarkasi &amp; Kloter</th>
                <th class="px-5 py-3.5">Penerbangan Haji</th>
                <th class="px-5 py-3.5">Alkes Terpakai</th>
                <th class="px-5 py-3.5">Awak Kabin &amp; Pelapor</th>
                <th class="px-5 py-3.5">Waktu Lapor</th>
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
                <td class="px-5 py-4 font-mono font-bold text-blue-700 whitespace-nowrap">
                  {{ item.registration_no }}
                </td>

                <!-- Kloter -->
                <td class="px-5 py-4">
                  <div class="font-bold text-slate-900">{{ item.nomor_kloter }}</div>
                  <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                    <MapPin class="w-3 h-3 text-blue-500" />
                    <span>{{ item.embarkasi }}</span>
                  </div>
                </td>

                <!-- Haji H/M -->
                <td class="px-5 py-4 font-mono text-slate-700 whitespace-nowrap">
                  <div>{{ item.penerbangan_haji_h }} H / {{ item.penerbangan_haji_m }} M</div>
                  <div v-if="item.no_kontak" class="text-[10px] text-slate-400 font-sans">
                    📞 {{ item.no_kontak }}
                  </div>
                </td>

                <!-- Alat Terpakai -->
                <td class="px-5 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded text-[10.5px] font-mono font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                      P3K: {{ countUsed(item.items_p3k) }} Pcs
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10.5px] font-mono font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                      EMK: {{ countUsed(item.items_emergensi) }} Pcs
                    </span>
                  </div>
                </td>

                <!-- Awak Kabin & Pelapor -->
                <td class="px-5 py-4">
                  <div class="font-medium text-slate-800">{{ item.crew_nama }}</div>
                  <div class="text-[11px] text-slate-400">Pelapor: {{ item.pelapor_nama }}</div>
                </td>

                <!-- Waktu -->
                <td class="px-5 py-4 text-slate-600 whitespace-nowrap">
                  <div>{{ formatDate(item.created_at) }}</div>
                  <div class="text-[10px] text-slate-400 font-mono">{{ item.created_at ? item.created_at.substring(11, 16) : '' }} WIB</div>
                </td>

                <!-- Status -->
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
                      @click="openDetail(item)"
                      class="p-1.5 rounded-lg text-slate-500 hover:text-blue-700 hover:bg-blue-50 transition-colors cursor-pointer"
                      title="Lihat Detail & Verifikasi"
                    >
                      <Eye class="w-4 h-4" />
                    </button>

                    <a
                      :href="`/travelconsent/receipt_alat/${item.registration_no}`"
                      target="_blank"
                      class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-colors cursor-pointer"
                      title="Lihat Tanda Terima"
                    >
                      <Download class="w-4 h-4" />
                    </a>

                    <button
                      type="button"
                      @click="deleteInspection(item.id, item.registration_no)"
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
                  <Activity class="w-8 h-8 mx-auto mb-2 opacity-50 text-blue-600" />
                  <p class="font-medium">Belum ada data pemakaian alat kesehatan yang sesuai.</p>
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
            Menampilkan <strong class="text-slate-800">{{ inspections.from || 0 }}</strong> - <strong class="text-slate-800">{{ inspections.to || 0 }}</strong> dari <strong class="text-slate-800">{{ inspections.total }}</strong> permohonan
          </div>

          <div class="flex items-center gap-1 flex-wrap">
            <template v-for="(link, lIdx) in inspections.links" :key="lIdx">
              <Link
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
                class="px-3 py-1.5 rounded-lg border text-xs font-medium transition-colors"
                :class="link.active ? 'bg-blue-600 text-white border-blue-600 font-bold' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
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

    <!-- Modal Detail & Verification -->
    <div
      v-if="showDetailModal && activeItem"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
      @click.self="closeDetail"
    >
      <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-xs">
              <Activity class="w-5 h-5" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-base font-extrabold text-slate-900">
                  {{ activeItem.nomor_kloter }} &bull; {{ activeItem.embarkasi }}
                </h3>
                <span class="px-2 py-0.5 rounded text-[10.5px] font-mono font-bold bg-blue-100 text-blue-800">
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
            class="p-2 rounded-xl text-slate-400 hover:text-slate-800 hover:bg-slate-200/60 transition-colors"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-slate-200 px-6 bg-white gap-6 text-xs font-semibold">
          <button
            type="button"
            @click="activeTab = 'overview'"
            class="py-3 border-b-2 transition-colors cursor-pointer"
            :class="activeTab === 'overview' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-900'"
          >
            Informasi Kloter &amp; TTD
          </button>
          <button
            type="button"
            @click="activeTab = 'p3k'"
            class="py-3 border-b-2 transition-colors cursor-pointer flex items-center gap-1.5"
            :class="activeTab === 'p3k' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-900'"
          >
            Alkes Kotak P3K
            <span class="px-1.5 py-0.2 bg-slate-100 text-slate-600 text-[10px] rounded-full">
              {{ activeItem.items_p3k ? activeItem.items_p3k.length : 0 }}
            </span>
          </button>
          <button
            type="button"
            @click="activeTab = 'emergensi'"
            class="py-3 border-b-2 transition-colors cursor-pointer flex items-center gap-1.5"
            :class="activeTab === 'emergensi' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-900'"
          >
            Alkes Kotak Emergensi
            <span class="px-1.5 py-0.2 bg-slate-100 text-slate-600 text-[10px] rounded-full">
              {{ activeItem.items_emergensi ? activeItem.items_emergensi.length : 0 }}
            </span>
          </button>
          <button
            type="button"
            @click="activeTab = 'verify'"
            class="py-3 border-b-2 transition-colors cursor-pointer flex items-center gap-1"
            :class="activeTab === 'verify' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-900'"
          >
            <ShieldCheck class="w-3.5 h-3.5" />
            <span>Verifikasi Karantina</span>
          </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto flex-1 space-y-6 text-xs">
          
          <!-- TAB 1: OVERVIEW -->
          <div v-if="activeTab === 'overview'" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Kloter Info -->
              <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                <h4 class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                  <Plane class="w-4 h-4 text-blue-600" />
                  <span>Data Kloter &amp; Penerbangan</span>
                </h4>
                <div class="divide-y divide-slate-200 text-slate-700">
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Nomor Kloter:</span>
                    <strong class="text-slate-900">{{ activeItem.nomor_kloter }}</strong>
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

              <!-- Petugas Info -->
              <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                <h4 class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                  <ShieldCheck class="w-4 h-4 text-blue-600" />
                  <span>Petugas &amp; Lokasi</span>
                </h4>
                <div class="divide-y divide-slate-200 text-slate-700">
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Awak Kabin (Crew):</span>
                    <strong class="text-slate-900">{{ activeItem.crew_nama }}</strong>
                  </div>
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Petugas / Pelapor:</span>
                    <strong class="text-slate-900">{{ activeItem.pelapor_nama }}</strong>
                  </div>
                  <div class="py-1.5 flex justify-between">
                    <span class="text-slate-500">Lokasi Geotag:</span>
                    <span>{{ activeItem.lokasi_geotag || '-' }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Dual Signature Previews -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="p-4 rounded-2xl bg-white border border-slate-200 space-y-1.5">
                <h5 class="font-bold text-slate-800">Tanda Tangan Awak Kabin:</h5>
                <div class="p-2 border border-slate-200 rounded-xl bg-slate-50">
                  <img v-if="activeItem.crew_ttd" :src="activeItem.crew_ttd" alt="TTD Crew" class="h-16 object-contain mx-auto" />
                  <p v-else class="text-slate-400 text-center italic py-4">Tidak ada tanda tangan</p>
                </div>
                <p class="text-[10px] text-slate-400 text-center">{{ activeItem.crew_nama }}</p>
              </div>

              <div class="p-4 rounded-2xl bg-white border border-slate-200 space-y-1.5">
                <h5 class="font-bold text-slate-800">Tanda Tangan Petugas / Pelapor:</h5>
                <div class="p-2 border border-slate-200 rounded-xl bg-slate-50">
                  <img v-if="activeItem.pelapor_ttd" :src="activeItem.pelapor_ttd" alt="TTD Pelapor" class="h-16 object-contain mx-auto" />
                  <p v-else class="text-slate-400 text-center italic py-4">Tidak ada tanda tangan</p>
                </div>
                <p class="text-[10px] text-slate-400 text-center">{{ activeItem.pelapor_nama }}</p>
              </div>
            </div>

            <!-- Notes -->
            <div class="p-4 rounded-2xl bg-blue-50/50 border border-blue-100">
              <h5 class="font-bold text-blue-900 mb-1 flex items-center gap-1.5">
                <Info class="w-4 h-4 text-blue-600" />
                <span>Catatan Pemakaian:</span>
              </h5>
              <p class="text-slate-700 italic">"{{ activeItem.keterangan || 'Tidak ada catatan tambahan.' }}"</p>
            </div>
          </div>

          <!-- TAB 2: P3K ITEMS -->
          <div v-if="activeTab === 'p3k'" class="space-y-4">
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold text-[11px]">
                  <tr>
                    <th class="px-4 py-2.5 w-12 text-center">No</th>
                    <th class="px-4 py-2.5">Nama Alat P3K</th>
                    <th class="px-4 py-2.5 w-24 text-center">Satuan</th>
                    <th class="px-4 py-2.5 w-24 text-center">Isi</th>
                    <th class="px-4 py-2.5 w-24 text-center">Terpakai</th>
                    <th class="px-4 py-2.5">Keterangan</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="(item, idx) in activeItem.items_p3k || []" :key="idx" class="hover:bg-slate-50/60">
                    <td class="px-4 py-2 text-center font-mono text-slate-400">{{ idx + 1 }}</td>
                    <td class="px-4 py-2 font-medium text-slate-800">{{ item.nama }}</td>
                    <td class="px-4 py-2 text-center text-slate-500">{{ item.satuan }}</td>
                    <td class="px-4 py-2 text-center font-mono">{{ item.isi }}</td>
                    <td class="px-4 py-2 text-center font-mono font-bold" :class="parseInt(item.terpakai) > 0 ? 'text-blue-600' : 'text-slate-400'">
                      {{ item.terpakai }}
                    </td>
                    <td class="px-4 py-2 text-slate-600">{{ item.keterangan || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 3: EMERGENSI ITEMS -->
          <div v-if="activeTab === 'emergensi'" class="space-y-4">
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold text-[11px]">
                  <tr>
                    <th class="px-4 py-2.5 w-12 text-center">No</th>
                    <th class="px-4 py-2.5">Nama Alat Emergensi</th>
                    <th class="px-4 py-2.5 w-24 text-center">Satuan</th>
                    <th class="px-4 py-2.5 w-24 text-center">Isi</th>
                    <th class="px-4 py-2.5 w-24 text-center">Terpakai</th>
                    <th class="px-4 py-2.5">Keterangan</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="(item, idx) in activeItem.items_emergensi || []" :key="idx" class="hover:bg-slate-50/60">
                    <td class="px-4 py-2 text-center font-mono text-slate-400">{{ idx + 1 }}</td>
                    <td class="px-4 py-2 font-medium text-slate-800">{{ item.nama }}</td>
                    <td class="px-4 py-2 text-center text-slate-500">{{ item.satuan }}</td>
                    <td class="px-4 py-2 text-center font-mono">{{ item.isi }}</td>
                    <td class="px-4 py-2 text-center font-mono font-bold" :class="parseInt(item.terpakai) > 0 ? 'text-indigo-600' : 'text-slate-400'">
                      {{ item.terpakai }}
                    </td>
                    <td class="px-4 py-2 text-slate-600">{{ item.keterangan || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 4: VERIFY -->
          <div v-if="activeTab === 'verify'" class="space-y-4">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
              <h4 class="font-bold text-slate-900 text-sm">Verifikasi Laporan Karantina</h4>
              <div>
                <label class="block text-slate-700 font-semibold mb-1">Status Verifikasi:</label>
                <select
                  v-model="statusForm.status"
                  class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 font-medium focus:ring-2 focus:ring-blue-500"
                >
                  <option value="submitted">Submitted (Menunggu)</option>
                  <option value="verified">Verified (Telah Diverifikasi)</option>
                  <option value="approved">Approved (Disahkan)</option>
                  <option value="rejected">Rejected (Ditolak / Perlu Perbaikan)</option>
                </select>
              </div>

              <div>
                <label class="block text-slate-700 font-semibold mb-1">Catatan / Rekomendasi Resmi:</label>
                <textarea
                  v-model="statusForm.keterangan"
                  rows="3"
                  placeholder="Catatan penggantian stok alkes, rekomendasi pemeriksaan ulang..."
                  class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 focus:ring-2 focus:ring-blue-500"
                ></textarea>
              </div>
            </div>
          </div>

        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
          <div class="text-xs text-slate-400">
            Diverifikasi oleh: <strong class="text-slate-700">{{ activeItem.verifier ? activeItem.verifier.name : 'Belum diverifikasi' }}</strong>
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
              class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 transition-colors shadow-xs cursor-pointer flex items-center gap-1.5"
            >
              <Check class="w-4 h-4" />
              <span>Simpan Pembaruan</span>
            </button>
          </div>
        </div>

      </div>
    </div>

  </HybridNavbar>
</template>

