<script setup>
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import { useDate } from '../../composables/useDate';
import {
  Plane,
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
  BarChart3,
  Trash2,
  X,
  FileSpreadsheet,
  Camera,
  MapPin,
} from 'lucide-vue-next';

const props = defineProps({
  inspections: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ search: '', kesimpulan: '', status: '' }),
  },
  stats: {
    type: Object,
    required: true,
  },
});

const { formatDate } = useDate();

const searchInput = ref(props.filters.search || '');
const selectedKesimpulan = ref(props.filters.kesimpulan || '');
const selectedStatus = ref(props.filters.status || '');

const handleFilter = () => {
  router.get('/aircraft-inspections', {
    search: searchInput.value || undefined,
    kesimpulan: selectedKesimpulan.value || undefined,
    status: selectedStatus.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const resetFilter = () => {
  searchInput.value = '';
  selectedKesimpulan.value = '';
  selectedStatus.value = '';
  router.get('/aircraft-inspections', {}, { preserveState: true });
};

// Modal Inspection Details & Verification
const activeInspection = ref(null);
const showDetailModal = ref(false);
const selectedPhotoModal = ref(null);
const statusForm = ref({
  status: 'approved',
  kesimpulan: 'layak',
  tindakan: '',
  catatan_rekomendasi: '',
});

const openDetail = (item) => {
  activeInspection.value = item;
  statusForm.value = {
    status: item.status || 'submitted',
    kesimpulan: item.kesimpulan || 'layak',
    tindakan: item.tindakan || '',
    catatan_rekomendasi: item.catatan_rekomendasi || '',
  };
  showDetailModal.value = true;
};

const closeDetail = () => {
  showDetailModal.value = false;
  activeInspection.value = null;
};

const submitStatusUpdate = () => {
  if (!activeInspection.value) return;
  router.put(`/aircraft-inspections/${activeInspection.value.id}/status`, statusForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      closeDetail();
    },
  });
};

const deleteInspection = (id, regNo) => {
  if (confirm(`Apakah Anda yakin ingin menghapus data inspeksi ${regNo}?`)) {
    router.delete(`/aircraft-inspections/${id}`, {
      preserveScroll: true,
    });
  }
};
</script>

<template>
  <Head title="Manajemen Inspeksi Sanitasi Pesawat &bull; BKK Bandung" />

  <HybridNavbar mode="inertia" currentRoute="aircraft-inspections">
    <main class="w-full px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">
      
      <!-- Top Title & Quick Action Bar -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-3xl p-6 shadow-xs">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-semibold bg-orange-50 dark:bg-orange-500/15 text-orange-700 dark:text-orange-400 border border-orange-200 dark:border-orange-500/40 mb-2">
            <Plane class="w-3.5 h-3.5" />
            <span>Kekarantinaan Kesehatan Alat Angkut Udara (BKK Kelas I Bandung)</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
            Data Inspeksi Sanitasi Pesawat
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-2xl leading-relaxed">
            Pusat manajemen dan verifikasi permohonan persetujuan perjalanan (Travel Consent) &amp; Aircraft Sanitation Inspection Check List.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <Link
            href="/aircraft-inspections/dashboard"
            class="px-4 py-2.5 rounded-xl font-semibold text-xs text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-[#1a202c] hover:bg-slate-200 dark:hover:bg-[#232b3a] border border-slate-200 dark:border-[#2d3748] transition-colors flex items-center gap-1.5"
          >
            <BarChart3 class="w-4 h-4 text-orange-500" />
            <span>Dashboard Grafik</span>
          </Link>

          <a
            href="/travelconsent/public_aircraft"
            target="_blank"
            class="px-4 py-2.5 rounded-xl font-bold text-xs text-slate-950 bg-orange-500 hover:bg-orange-400 transition-all shadow-md shadow-orange-500/30 flex items-center gap-1.5"
          >
            <Plus class="w-4 h-4" />
            <span>Buka Form Publik ↗</span>
          </a>

          <a
            :href="`/aircraft-inspections/export?format=xlsx${selectedKesimpulan ? '&kesimpulan=' + selectedKesimpulan : ''}${selectedStatus ? '&status=' + selectedStatus : ''}`"
            class="px-3.5 py-2.5 rounded-xl font-semibold text-xs text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-200 dark:border-emerald-800/60 transition-colors flex items-center gap-1.5"
            title="Unduh Spreadsheet Excel"
          >
            <FileSpreadsheet class="w-4 h-4" />
            <span>Ekspor Excel</span>
          </a>
        </div>
      </div>

      <!-- Live Metric Cards Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
        <!-- Total -->
        <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-2xl p-4 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>TOTAL INSPEKSI</span>
            <Plane class="w-4 h-4 text-orange-500" />
          </div>
          <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1.5">
            {{ stats.total }}
          </div>
          <div class="text-[11px] text-slate-400 mt-0.5">Semua maskapai</div>
        </div>

        <!-- Kepatuhan / Layak -->
        <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-2xl p-4 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>LAYAK TERBANG</span>
            <CheckCircle2 class="w-4 h-4 text-orange-500" />
          </div>
          <div class="text-2xl font-extrabold text-orange-500 mt-1.5">
            {{ stats.layak_count }}
          </div>
          <div class="text-[11px] text-orange-400/80 mt-0.5">
            {{ stats.compliance_rate }}% Kelaikan
          </div>
        </div>

        <!-- Tidak Layak -->
        <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-2xl p-4 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>TIDAK LAYAK</span>
            <AlertTriangle class="w-4 h-4 text-rose-500" />
          </div>
          <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1.5">
            {{ stats.tidak_layak_count }}
          </div>
          <div class="text-[11px] text-rose-500/80 mt-0.5">Perlu tindakan</div>
        </div>

        <!-- Tindakan Penyehatan -->
        <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-2xl p-4 shadow-xs">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>TINDAKAN DISINSEKSI</span>
            <ShieldCheck class="w-4 h-4 text-amber-500" />
          </div>
          <div class="text-2xl font-extrabold text-amber-500 mt-1.5">
            {{ stats.need_action_count }}
          </div>
          <div class="text-[11px] text-amber-500/80 mt-0.5">Penyehatan pesawat</div>
        </div>

        <!-- Hari Ini -->
        <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-2xl p-4 shadow-xs col-span-2 sm:col-span-1">
          <div class="flex items-center justify-between text-slate-500 text-xs font-mono">
            <span>INSPEKSI HARI INI</span>
            <FileCheck class="w-4 h-4 text-orange-400" />
          </div>
          <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1.5">
            {{ stats.today_count }}
          </div>
          <div class="text-[11px] text-orange-400 mt-0.5">Real-time update</div>
        </div>
      </div>

      <!-- Filters & Search Toolbar -->
      <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-2xl p-4 flex flex-col md:flex-row items-center justify-between gap-3 shadow-xs">
        <div class="relative w-full md:w-80">
          <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            v-model="searchInput"
            @keyup.enter="handleFilter"
            type="text"
            placeholder="Cari maskapai, no registrasi, petugas..."
            class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-slate-50 dark:bg-[#0d1017] border border-slate-200 dark:border-[#232a36] text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500"
          />
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto flex-wrap">
          <select
            v-model="selectedKesimpulan"
            @change="handleFilter"
            class="px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-[#0d1017] border border-slate-200 dark:border-[#232a36] text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500"
          >
            <option value="">Semua Kelaikan</option>
            <option value="layak">Layak Bebas Serangga</option>
            <option value="tidak_layak">Tidak Layak (Tindakan)</option>
          </select>

          <select
            v-model="selectedStatus"
            @change="handleFilter"
            class="px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-[#0d1017] border border-slate-200 dark:border-[#232a36] text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500"
          >
            <option value="">Semua Status</option>
            <option value="submitted">Submitted (Menunggu)</option>
            <option value="verified">Verified</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>

          <button
            type="button"
            @click="handleFilter"
            class="px-3 py-2 text-xs font-bold rounded-xl bg-orange-500 hover:bg-orange-400 text-slate-950 transition-colors cursor-pointer"
          >
            Terapkan
          </button>

          <button
            v-if="searchInput || selectedKesimpulan || selectedStatus"
            type="button"
            @click="resetFilter"
            class="px-3 py-2 text-xs text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors"
          >
            Reset
          </button>
        </div>
      </div>

      <!-- Main Inspections Table Container -->
      <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-3xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                <th class="px-5 py-3.5">No. Registrasi</th>
                <th class="px-5 py-3.5">Pemilik / Maskapai</th>
                <th class="px-5 py-3.5">Tanda Reg</th>
                <th class="px-5 py-3.5">Waktu Inspeksi</th>
                <th class="px-5 py-3.5">Kelaikan Sanitasi</th>
                <th class="px-5 py-3.5">Tindakan</th>
                <th class="px-5 py-3.5">Status</th>
                <th class="px-5 py-3.5 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
              <tr
                v-for="item in inspections.data"
                :key="item.id"
                class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors"
              >
                <!-- Registration No -->
                <td class="px-5 py-4 font-mono font-bold text-orange-600 dark:text-orange-400 whitespace-nowrap">
                  {{ item.registration_no }}
                </td>

                <!-- Pemilik / Maskapai -->
                <td class="px-5 py-4 font-medium text-slate-800 dark:text-slate-200">
                  <div class="font-semibold">{{ item.pemilik }}</div>
                  <div class="text-[11px] text-slate-400">Petugas Kabin: {{ item.petugas_kabin_nama }}</div>
                </td>

                <!-- Tanda Registrasi -->
                <td class="px-5 py-4 font-mono text-slate-700 dark:text-slate-300">
                  <div class="font-bold">{{ item.tanda_kebangsaan }}</div>
                  <div v-if="item.foto_dokumentasi && item.foto_dokumentasi.length" class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[10px] font-sans font-medium bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800/60" title="Foto geotagging dilampirkan">
                    <Camera class="w-3 h-3 text-sky-600" />
                    <span>{{ item.foto_dokumentasi.length }} Foto</span>
                  </div>
                </td>

                <!-- Waktu -->
                <td class="px-5 py-4 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                  <div>{{ formatDate(item.tanggal) }}</div>
                  <div class="text-[10.5px] font-mono text-slate-400">{{ item.waktu }} WIB</div>
                </td>

                <!-- Kesimpulan Sanitasi -->
                <td class="px-5 py-4 whitespace-nowrap">
                  <span
                    class="px-2.5 py-1 rounded-full text-[11px] font-semibold border inline-flex items-center gap-1.5"
                    :class="item.kesimpulan === 'layak'
                      ? 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                      : 'bg-rose-50 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800'"
                  >
                    <component :is="item.kesimpulan === 'layak' ? CheckCircle2 : XCircle" class="w-3.5 h-3.5" />
                    <span>{{ item.kesimpulan === 'layak' ? 'Layak' : 'Tidak Layak' }}</span>
                  </span>
                </td>

                <!-- Tindakan Penyehatan -->
                <td class="px-5 py-4 whitespace-nowrap">
                  <span
                    v-if="item.tindakan"
                    class="px-2 py-0.5 rounded-lg text-[10.5px] font-semibold uppercase bg-amber-50 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800"
                  >
                    {{ item.tindakan.replace('_', ' ') }}
                  </span>
                  <span v-else class="text-slate-400 text-[11px]">Nihil</span>
                </td>

                <!-- Workflow Status -->
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

                <!-- Actions -->
                <td class="px-5 py-4 text-right whitespace-nowrap">
                  <div class="inline-flex items-center gap-1.5">
                    <button
                      type="button"
                      @click="openDetail(item)"
                      class="p-1.5 rounded-lg text-slate-500 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-50 dark:hover:bg-slate-800 transition-colors"
                      title="Lihat Detail & Verifikasi"
                    >
                      <Eye class="w-4 h-4" />
                    </button>

                    <a
                      :href="`/aircraft-inspections/${item.id}/pdf`"
                      class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-slate-800 transition-colors"
                      title="Unduh Berkas PDF Resmi"
                    >
                      <Download class="w-4 h-4" />
                    </a>

                    <button
                      type="button"
                      @click="deleteInspection(item.id, item.registration_no)"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800 transition-colors"
                      title="Hapus Data"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="inspections.data.length === 0">
                <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                  <Plane class="w-8 h-8 mx-auto mb-2 opacity-50" />
                  <p class="font-medium">Belum ada data inspeksi yang sesuai dengan filter.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="inspections.links && inspections.links.length > 3" class="px-5 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs">
          <div class="text-slate-500">
            Menampilkan {{ inspections.from || 0 }} - {{ inspections.to || 0 }} dari {{ inspections.total }} data
          </div>
          <div class="flex items-center gap-1">
            <template v-for="(link, i) in inspections.links" :key="i">
              <Link
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
                class="px-2.5 py-1 rounded-lg transition-colors font-medium"
                :class="link.active
                  ? 'bg-sky-600 text-white font-bold'
                  : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
              />
              <span v-else v-html="link.label" class="px-2.5 py-1 text-slate-300 dark:text-slate-700" />
            </template>
          </div>
        </div>
      </div>

      <!-- Detail & Verification Drawer / Modal -->
      <div v-if="showDetailModal && activeInspection" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl">
          <!-- Modal Header -->
          <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-sky-100 dark:bg-sky-950 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
                <Plane class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                  Lembar Hasil Inspeksi &bull; {{ activeInspection.registration_no }}
                </h3>
                <div class="text-xs text-slate-500">
                  {{ activeInspection.pemilik }} &bull; Reg: {{ activeInspection.tanda_kebangsaan }}
                </div>
              </div>
            </div>
            <button
              type="button"
              @click="closeDetail"
              class="p-2 rounded-xl text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <!-- Modal Body (Scrollable) -->
          <div class="p-6 overflow-y-auto space-y-6 text-xs">
            <!-- Kop Surat Resmi Balai Kekarantinaan Kesehatan Bandung -->
            <div class="p-2 sm:p-3 bg-white rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xs">
              <img
                src="/storage/images/Kop_BKK_bandung.png"
                alt="Kop Balai Kekarantinaan Kesehatan Bandung"
                class="w-full h-auto object-contain block mx-auto"
                onerror="this.onerror=null;this.src='/images/Kop_BKK_bandung.png';"
              />
            </div>

            <!-- Inspection Details Overview -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 dark:bg-slate-950 p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
              <div>
                <div class="text-slate-400">Tanggal &amp; Waktu</div>
                <div class="font-bold text-slate-800 dark:text-slate-200">{{ formatDate(activeInspection.tanggal) }} ({{ activeInspection.waktu }} WIB)</div>
              </div>
              <div>
                <div class="text-slate-400">Kelaikan Sanitasi</div>
                <div class="font-bold" :class="activeInspection.kesimpulan === 'layak' ? 'text-emerald-600' : 'text-rose-600'">
                  {{ activeInspection.kesimpulan.toUpperCase() }}
                </div>
              </div>
              <div>
                <div class="text-slate-400">Tindakan Penyehatan</div>
                <div class="font-bold text-slate-800 dark:text-slate-200">{{ activeInspection.tindakan ? activeInspection.tindakan.toUpperCase() : 'NIHIL' }}</div>
              </div>
              <div>
                <div class="text-slate-400">Status Dokumen</div>
                <div class="font-mono font-bold uppercase text-sky-600">{{ activeInspection.status }}</div>
              </div>
            </div>

            <!-- Signatures Section -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-center">
                <div class="text-slate-500 font-semibold mb-2">Petugas Kabin (Cabin Crew)</div>
                <div class="h-24 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden mb-2">
                  <img
                    v-if="activeInspection.petugas_kabin_ttd && activeInspection.petugas_kabin_ttd.startsWith('data:image')"
                    :src="activeInspection.petugas_kabin_ttd"
                    class="max-h-20 max-w-full object-contain"
                  />
                  <span v-else class="text-slate-400 text-xs italic">(Tertanda digital)</span>
                </div>
                <div class="font-bold text-slate-900 dark:text-white">{{ activeInspection.petugas_kabin_nama }}</div>
              </div>

              <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-center">
                <div class="text-slate-500 font-semibold mb-2">Petugas BKK Bandung</div>
                <div class="h-24 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden mb-2">
                  <img
                    v-if="activeInspection.petugas_bkk_ttd && activeInspection.petugas_bkk_ttd.startsWith('data:image')"
                    :src="activeInspection.petugas_bkk_ttd"
                    class="max-h-20 max-w-full object-contain"
                  />
                  <span v-else class="text-slate-400 text-xs italic">(Tertanda digital)</span>
                </div>
                <div class="font-bold text-slate-900 dark:text-white">{{ activeInspection.petugas_bkk_nama }}</div>
              </div>
            </div>

            <!-- Geotagging & Photo Documentation Section -->
            <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-950/40 space-y-3">
              <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                  <Camera class="w-4 h-4 text-sky-600" />
                  <span>Dokumentasi Foto Geotagging Lapangan</span>
                  <span v-if="activeInspection.foto_dokumentasi && activeInspection.foto_dokumentasi.length" class="text-xs font-semibold px-2 py-0.5 rounded-full bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300">
                    {{ activeInspection.foto_dokumentasi.length }} Berkas
                  </span>
                </div>

                <div v-if="activeInspection.latitude && activeInspection.longitude">
                  <a
                    :href="`https://www.google.com/maps?q=${activeInspection.latitude},${activeInspection.longitude}`"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 transition-colors"
                  >
                    <MapPin class="w-3.5 h-3.5 text-emerald-600" />
                    <span>📍 {{ Number(activeInspection.latitude).toFixed(6) }}, {{ Number(activeInspection.longitude).toFixed(6) }} (Peta ↗)</span>
                  </a>
                </div>
              </div>

              <div v-if="activeInspection.lokasi_geotag" class="text-xs text-slate-500">
                Lokasi: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ activeInspection.lokasi_geotag }}</span>
              </div>

              <!-- Photo Gallery Grid -->
              <div v-if="activeInspection.foto_dokumentasi && activeInspection.foto_dokumentasi.length" class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2">
                <div
                  v-for="(foto, fIdx) in activeInspection.foto_dokumentasi"
                  :key="fIdx"
                  class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-2xs group flex flex-col"
                >
                  <div
                    class="relative h-32 w-full bg-slate-950 cursor-pointer overflow-hidden"
                    @click="selectedPhotoModal = foto"
                    title="Klik untuk melihat foto resolusi penuh"
                  >
                    <img
                      :src="foto.url || ('/storage/' + foto.path)"
                      :alt="foto.category || 'Dokumentasi Lapangan'"
                      class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                    />
                    <div v-if="foto.latitude && foto.longitude" class="absolute bottom-0 inset-x-0 bg-slate-950/85 text-[9.5px] font-mono text-white px-2 py-0.5 truncate pointer-events-none">
                      📍 {{ Number(foto.latitude).toFixed(5) }}, {{ Number(foto.longitude).toFixed(5) }}
                    </div>
                  </div>
                  <div class="p-2.5 flex flex-col gap-1 flex-grow">
                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800/60 truncate">
                      {{ foto.category || 'Dokumentasi Lapangan' }}
                    </span>
                    <div v-if="foto.captured_at" class="text-[10px] text-slate-400">
                      {{ new Date(foto.captured_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="text-xs text-slate-400 italic py-4 text-center bg-white dark:bg-slate-900 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
                Belum ada foto dokumentasi geotagging yang diunggah pada inspeksi ini.
              </div>
            </div>

            <!-- Status & Recommendation Update Form -->
            <div class="p-5 rounded-2xl border border-sky-200 dark:border-sky-900 bg-sky-50/40 dark:bg-sky-950/20 space-y-4">
              <div class="font-bold text-sm text-sky-900 dark:text-sky-200 flex items-center gap-2">
                <ShieldCheck class="w-4 h-4 text-sky-600" />
                <span>Panel Verifikasi &amp; Approval Karantina</span>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                  <label class="block text-slate-600 dark:text-slate-400 mb-1 font-semibold">Status Persetujuan:</label>
                  <select v-model="statusForm.status" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white">
                    <option value="submitted">Submitted</option>
                    <option value="verified">Verified</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                  </select>
                </div>

                <div>
                  <label class="block text-slate-600 dark:text-slate-400 mb-1 font-semibold">Kelaikan Sanitasi:</label>
                  <select v-model="statusForm.kesimpulan" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white">
                    <option value="layak">Layak Bebas Serangga</option>
                    <option value="tidak_layak">Tidak Layak</option>
                  </select>
                </div>

                <div>
                  <label class="block text-slate-600 dark:text-slate-400 mb-1 font-semibold">Tindakan Penyehatan:</label>
                  <select v-model="statusForm.tindakan" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white">
                    <option value="">Tidak Perlu</option>
                    <option value="disinseksi">Disinseksi</option>
                    <option value="fumigasi">Fumigasi</option>
                    <option value="disinfeksi">Disinfeksi</option>
                    <option value="general_cleaning">General Cleaning</option>
                  </select>
                </div>
              </div>

              <div>
                <label class="block text-slate-600 dark:text-slate-400 mb-1 font-semibold">Catatan / Rekomendasi Resmi BKK:</label>
                <input
                  v-model="statusForm.catatan_rekomendasi"
                  type="text"
                  placeholder="Tambahkan catatan hasil verifikasi..."
                  class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white"
                />
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <a
              :href="`/aircraft-inspections/${activeInspection.id}/pdf`"
              class="px-4 py-2 rounded-xl font-semibold text-xs text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors flex items-center gap-1.5"
            >
              <Download class="w-4 h-4 text-emerald-500" />
              <span>Unduh Berkas PDF</span>
            </a>

            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="closeDetail"
                class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
              >
                Tutup
              </button>
              <button
                type="button"
                @click="submitStatusUpdate"
                class="px-4 py-2 rounded-xl text-xs font-bold text-slate-950 bg-orange-500 hover:bg-orange-400 transition-colors shadow-sm"
              >
                Simpan Perubahan
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Lightbox Photo Modal -->
      <div
        v-if="selectedPhotoModal"
        class="fixed inset-0 z-60 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md"
        @click="selectedPhotoModal = null"
      >
        <div
          class="relative max-w-4xl max-h-[90vh] bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden p-3 shadow-2xl flex flex-col"
          @click.stop
        >
          <button
            type="button"
            @click="selectedPhotoModal = null"
            class="absolute top-4 right-4 z-20 p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-white transition-colors"
          >
            <X class="w-5 h-5" />
          </button>

          <div class="overflow-hidden rounded-2xl flex items-center justify-center bg-black/60 max-h-[75vh]">
            <img
              :src="selectedPhotoModal.url || ('/storage/' + selectedPhotoModal.path)"
              :alt="selectedPhotoModal.category"
              class="max-h-[75vh] w-auto max-w-full object-contain"
            />
          </div>

          <div class="pt-3 px-2 flex flex-wrap items-center justify-between gap-2 text-xs text-white">
            <div class="font-bold">
              {{ selectedPhotoModal.category || 'Dokumentasi Lapangan' }}
            </div>
            <div v-if="selectedPhotoModal.latitude && selectedPhotoModal.longitude" class="font-mono text-sky-400">
              📍 Lat: {{ Number(selectedPhotoModal.latitude).toFixed(6) }}, Lng: {{ Number(selectedPhotoModal.longitude).toFixed(6) }}
            </div>
          </div>
        </div>
      </div>

    </main>
  </HybridNavbar>
</template>
