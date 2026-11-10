<script setup>
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import {
  Leaf,
  Utensils,
  Droplets,
  Building2,
  Bug,
  Search,
  Filter,
  RotateCcw,
  Download,
  Printer,
  Eye,
  Plus,
  CheckCircle2,
  AlertTriangle,
  XCircle,
  ShieldCheck,
  Calendar,
  MapPin,
  User,
  Activity,
  Layers,
  ChevronRight,
  X,
  FileSpreadsheet,
  Check,
  TrendingUp,
} from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
  active_tab: {
    type: String,
    default: 'overview',
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  wilker_options: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
  inspeksi_tpp: {
    type: Object,
    default: () => ({ data: [] }),
  },
  inspeksi_sab: {
    type: Object,
    default: () => ({ data: [] }),
  },
  inspeksi_hsbu: {
    type: Object,
    default: () => ({ data: [] }),
  },
  sivera_list: {
    type: Object,
    default: () => ({ data: [] }),
  },
  master_tpp: {
    type: Array,
    default: () => [],
  },
  master_sab: {
    type: Array,
    default: () => [],
  },
  master_hsbu: {
    type: Array,
    default: () => [],
  },
});

// Active Tab State
const currentTab = ref(props.active_tab || 'overview');

// Filter States
const search = ref(props.filters.search || '');
const selectedWilker = ref(props.filters.wilker || '');
const selectedRisiko = ref(props.filters.tingkat_risiko || '');
const selectedTahun = ref(props.filters.tahun || '');
const selectedBulan = ref(props.filters.bulan || '');

const switchTab = (tab) => {
  currentTab.value = tab;
  applyFilter(tab);
};

const applyFilter = (targetTab = currentTab.value) => {
  router.get('/prl', {
    tab: targetTab,
    search: search.value || undefined,
    wilker: selectedWilker.value || undefined,
    tingkat_risiko: selectedRisiko.value || undefined,
    tahun: selectedTahun.value || undefined,
    bulan: selectedBulan.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const resetFilter = () => {
  search.value = '';
  selectedWilker.value = '';
  selectedRisiko.value = '';
  selectedTahun.value = '';
  selectedBulan.value = '';
  router.get('/prl', { tab: currentTab.value }, { preserveState: true, replace: true });
};

// Modal Detail State
const showDetailModal = ref(false);
const detailItem = ref(null);
const detailType = ref('');

const openDetail = (item, type) => {
  detailItem.value = item;
  detailType.value = type;
  showDetailModal.value = true;
};

const closeDetail = () => {
  showDetailModal.value = false;
  detailItem.value = null;
  detailType.value = '';
};

// Modal Tambah Sarana Baru State
const showAddSaranaModal = ref(false);
const addSaranaType = ref('tpp');
const isSubmittingSarana = ref(false);
const saranaForm = ref({
  wilker: '',
  nama: '',
  penanggung_jawab: '',
  no_hp: '',
  jml_karyawan: '',
  kapasitas: '',
  sumber_air: '',
});

const openAddSarana = (type = 'tpp') => {
  addSaranaType.value = type;
  saranaForm.value = {
    wilker: props.wilker_options[0] || 'Wilayah Kerja Patimban',
    nama: '',
    penanggung_jawab: '',
    no_hp: '',
    jml_karyawan: '1-5 Pekerja',
    kapasitas: '',
    sumber_air: 'PDAM',
  };
  showAddSaranaModal.value = true;
};

const submitSarana = async () => {
  if (!saranaForm.value.nama) {
    alert('Nama sarana wajib diisi!');
    return;
  }
  isSubmittingSarana.value = true;
  try {
    let endpoint = '/prl/tpp';
    let payload = {
      wilker: saranaForm.value.wilker,
      nama_tpp: saranaForm.value.nama,
      penanggung_jawab: saranaForm.value.penanggung_jawab,
      jml_karyawan: saranaForm.value.jml_karyawan,
      kapasitas_produksi: saranaForm.value.kapasitas,
    };

    if (addSaranaType.value === 'sab') {
      endpoint = '/prl/sab';
      payload = {
        wilker: saranaForm.value.wilker,
        nama_sab: saranaForm.value.nama,
        penanggung_jawab: saranaForm.value.penanggung_jawab,
        penanggung_jawab_no_hp: saranaForm.value.no_hp,
        sumber_air: saranaForm.value.sumber_air,
        kapasitas_penampungan: saranaForm.value.kapasitas,
      };
    } else if (addSaranaType.value === 'hsbu') {
      endpoint = '/prl/hsbu';
      payload = {
        wilker: saranaForm.value.wilker,
        nama_gedung: saranaForm.value.nama,
        penanggung_jawab: saranaForm.value.penanggung_jawab,
        jml_karyawan: saranaForm.value.jml_karyawan,
      };
    }

    const res = await axios.post(endpoint, payload);
    if (res.data.success) {
      alert(res.data.message);
      showAddSaranaModal.value = false;
      router.reload();
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan data sarana.');
  } finally {
    isSubmittingSarana.value = false;
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const getRiskBadgeClass = (risk) => {
  switch (risk) {
    case 'Rendah':
      return 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
    case 'Sedang':
      return 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800';
    case 'Tinggi':
      return 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800';
    default:
      return 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700';
  }
};
</script>

<template>
  <Head title="Pengendalian Risiko Lingkungan (PRL) &bull; BKK Bandung" />

  <HybridNavbar mode="inertia" currentRoute="prl">
    <main class="w-full px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

      <!-- ==================== HEADER BAR ==================== -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 mb-2">
            <Leaf class="w-3.5 h-3.5 text-emerald-600" />
            <span>Substansi Pengendalian Risiko Lingkungan (PRL) &bull; BKK Kelas I Bandung</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
            Sanitasi Lingkungan Pelabuhan &amp; Bandara
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-3xl leading-relaxed">
            Pusat terpadu pengawasan Tempat Pengelolaan Pangan (TPP), Sarana Air Bersih (SAB), Higiene Sanitasi Bangunan dan Usaha (HSBU), serta Surveilans Vektor &amp; Reservoar (SIVERA) di seluruh wilayah kerja.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <button
            type="button"
            @click="openAddSarana('tpp')"
            class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-[#00A99D] to-[#005851] hover:opacity-95 text-xs font-bold text-white transition shadow-sm flex items-center gap-1.5 cursor-pointer"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>Tambah Sarana Baru</span>
          </button>

          <a
            :href="`/prl/export/${currentTab === 'overview' ? 'tpp' : currentTab}`"
            class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-xs font-bold text-slate-700 dark:text-slate-200 transition shadow-xs flex items-center gap-1.5 cursor-pointer"
          >
            <Download class="w-3.5 h-3.5 text-slate-500" />
            <span>Export CSV / Excel</span>
          </a>
        </div>
      </div>

      <!-- ==================== KPI STATS CARDS ==================== -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Sarana -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Sarana Terdaftar</span>
            <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-[#00A99D]">
              <Layers class="w-5 h-5" />
            </div>
          </div>
          <div class="mt-2 flex items-baseline gap-2">
            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white font-mono">{{ stats.total_sarana }}</span>
            <span class="text-xs text-slate-400">Unit Sarana</span>
          </div>
          <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2">
            <span>{{ stats.total_tpp }} TPP</span> &bull; 
            <span>{{ stats.total_sab }} SAB</span> &bull; 
            <span>{{ stats.total_hsbu }} HSBU</span>
          </div>
        </div>

        <!-- Card 2: Total Riwayat Inspeksi -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Riwayat Inspeksi</span>
            <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/50 flex items-center justify-center text-[#005851] dark:text-teal-400">
              <ShieldCheck class="w-5 h-5" />
            </div>
          </div>
          <div class="mt-2 flex items-baseline gap-2">
            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white font-mono">{{ stats.total_inspeksi }}</span>
            <span class="text-xs text-slate-400">Inspeksi Sanitasi</span>
          </div>
          <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2">
            <span>{{ stats.total_insp_tpp }} TPP</span> &bull; 
            <span>{{ stats.total_insp_sab }} SAB</span> &bull; 
            <span>{{ stats.total_insp_hsbu }} HSBU</span>
          </div>
        </div>

        <!-- Card 3: Kepatuhan Bebas Risiko Tinggi -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Kepatuhan Sanitasi</span>
            <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center text-amber-600">
              <TrendingUp class="w-5 h-5" />
            </div>
          </div>
          <div class="mt-2 flex items-baseline gap-2">
            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white font-mono">{{ stats.kepatuhan_persen }}%</span>
            <span class="text-xs text-emerald-600 font-semibold font-mono">Risiko Rendah</span>
          </div>
          <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2">
            <span class="text-emerald-600 font-semibold">{{ stats.risiko_rendah }} Rendah</span> &bull; 
            <span class="text-amber-600 font-semibold">{{ stats.risiko_sedang }} Sedang</span> &bull; 
            <span class="text-rose-600 font-semibold">{{ stats.risiko_tinggi }} Tinggi</span>
          </div>
        </div>

        <!-- Card 4: SIVERA Vektor -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Surveilans Vektor (SIVERA)</span>
            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600">
              <Bug class="w-5 h-5" />
            </div>
          </div>
          <div class="mt-2 flex items-baseline gap-2">
            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white font-mono">{{ stats.sivera_total }}</span>
            <span class="text-xs text-slate-400">Pemantauan</span>
          </div>
          <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2">
            <span class="text-emerald-600 font-semibold">{{ stats.sivera_memenuhi }} Sesuai Baku Mutu</span> &bull; 
            <span class="text-rose-600 font-semibold">{{ stats.sivera_diatas_baku }} Di Atas Baku</span>
          </div>
        </div>
      </div>

      <!-- ==================== SUB-TABS NAVIGATION ==================== -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200 dark:border-slate-800">
        <button
          type="button"
          @click="switchTab('overview')"
          class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
          :class="currentTab === 'overview' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
        >
          <Activity class="w-3.5 h-3.5" />
          <span>Ringkasan &amp; Analitik PRL</span>
        </button>

        <button
          type="button"
          @click="switchTab('tpp')"
          class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
          :class="currentTab === 'tpp' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
        >
          <Utensils class="w-3.5 h-3.5" />
          <span>Tempat Pengelolaan Pangan (TPP)</span>
          <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20 font-mono">{{ stats.total_insp_tpp }}</span>
        </button>

        <button
          type="button"
          @click="switchTab('sab')"
          class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
          :class="currentTab === 'sab' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
        >
          <Droplets class="w-3.5 h-3.5" />
          <span>Sarana Air Bersih (SAB)</span>
          <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20 font-mono">{{ stats.total_insp_sab }}</span>
        </button>

        <button
          type="button"
          @click="switchTab('hsbu')"
          class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
          :class="currentTab === 'hsbu' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
        >
          <Building2 class="w-3.5 h-3.5" />
          <span>Higiene Bangunan &amp; Usaha (HSBU)</span>
          <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20 font-mono">{{ stats.total_insp_hsbu }}</span>
        </button>

        <button
          type="button"
          @click="switchTab('sivera')"
          class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
          :class="currentTab === 'sivera' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
        >
          <Bug class="w-3.5 h-3.5" />
          <span>Surveilans Vektor (SIVERA)</span>
          <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20 font-mono">{{ stats.sivera_total }}</span>
        </button>
      </div>

      <!-- ==================== FILTER BAR (Untuk tab data) ==================== -->
      <div v-if="currentTab !== 'overview'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
          <!-- Cari -->
          <div class="md:col-span-2">
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Cari Sarana / Pemeriksa</label>
            <div class="relative">
              <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
              <input
                type="text"
                v-model="search"
                @keyup.enter="applyFilter()"
                placeholder="Ketik nama warung, gedung, pemilik, pemeriksa..."
                class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
              />
            </div>
          </div>

          <!-- Wilayah Kerja -->
          <div>
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Wilayah Kerja</label>
            <select
              v-model="selectedWilker"
              @change="applyFilter()"
              class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
            >
              <option value="">Semua Wilker</option>
              <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
            </select>
          </div>

          <!-- Tingkat Risiko -->
          <div>
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Tingkat Risiko</label>
            <select
              v-model="selectedRisiko"
              @change="applyFilter()"
              class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
            >
              <option value="">Semua Kategori</option>
              <option value="Rendah">Risiko Rendah</option>
              <option value="Sedang">Risiko Sedang</option>
              <option value="Tinggi">Risiko Tinggi</option>
            </select>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="applyFilter()"
              class="flex-1 py-2 rounded-xl bg-[#00A99D] hover:bg-[#008f85] text-white text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs"
            >
              <Filter class="w-3.5 h-3.5" />
              <span>Saring</span>
            </button>
            <button
              type="button"
              @click="resetFilter"
              class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition cursor-pointer"
              title="Reset Filter"
            >
              <RotateCcw class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- ==================== TAB CONTENT: OVERVIEW ==================== -->
      <div v-if="currentTab === 'overview'" class="space-y-6">
        <!-- Wilker Matrix Table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 gap-2">
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <MapPin class="w-4 h-4 text-[#00A99D]" />
                <span>Distribusi Sarana &amp; Riwayat Inspeksi Per Wilayah Kerja</span>
              </h2>
              <p class="text-xs text-slate-400 mt-0.5">Sebaran sarana sanitasi lingkungan dan total pengawasan di pelabuhan &amp; bandara.</p>
            </div>
            <span class="text-xs font-mono text-slate-500 bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full">
              {{ stats.wilker_stats?.length || 0 }} Wilker Aktif
            </span>
          </div>

          <div class="overflow-x-auto mt-4">
            <table class="w-full text-xs text-left">
              <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                  <th class="py-3 px-3">Wilayah Kerja / Pelabuhan / Bandara</th>
                  <th class="py-3 px-3 text-center">Sarana TPP</th>
                  <th class="py-3 px-3 text-center">Sarana SAB</th>
                  <th class="py-3 px-3 text-center">Sarana HSBU</th>
                  <th class="py-3 px-3 text-center font-bold text-slate-900 dark:text-white">Total Sarana</th>
                  <th class="py-3 px-3 text-center font-bold text-[#005851] dark:text-emerald-400">Total Inspeksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="w in stats.wilker_stats" :key="w.wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                  <td class="py-3.5 px-3 font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#00A99D]"></span>
                    {{ w.wilker }}
                  </td>
                  <td class="py-3.5 px-3 text-center font-mono text-slate-600 dark:text-slate-300">{{ w.tpp }}</td>
                  <td class="py-3.5 px-3 text-center font-mono text-slate-600 dark:text-slate-300">{{ w.sab }}</td>
                  <td class="py-3.5 px-3 text-center font-mono text-slate-600 dark:text-slate-300">{{ w.hsbu }}</td>
                  <td class="py-3.5 px-3 text-center font-mono font-bold text-slate-900 dark:text-white bg-slate-50/50 dark:bg-slate-800/30">
                    {{ w.total_sarana }}
                  </td>
                  <td class="py-3.5 px-3 text-center font-mono font-extrabold text-[#005851] dark:text-emerald-400 bg-emerald-50/30 dark:bg-emerald-950/20">
                    {{ w.total_inspeksi }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- 3 Feature Cards to Drill In -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <!-- TPP Box -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs flex flex-col justify-between">
            <div>
              <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center mb-3">
                <Utensils class="w-5 h-5" />
              </div>
              <h3 class="font-bold text-slate-900 dark:text-white text-base">Higiene Sanitasi Pangan (TPP)</h3>
              <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                Pemeriksaan jasa boga, restoran, rumah makan, kantin, dan depot air minum di lingkungan pelabuhan dan bandara.
              </p>
              <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-between text-xs text-slate-600 dark:text-slate-400">
                <span>{{ stats.total_tpp }} Sarana Terdaftar</span>
                <span class="font-bold font-mono text-[#005851] dark:text-emerald-400">{{ stats.total_insp_tpp }} Riwayat Inspeksi</span>
              </div>
            </div>
            <button
              type="button"
              @click="switchTab('tpp')"
              class="mt-4 w-full py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 transition flex items-center justify-center gap-2 cursor-pointer"
            >
              <span>Buka Modul TPP</span>
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>

          <!-- SAB Box -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs flex flex-col justify-between">
            <div>
              <div class="w-10 h-10 rounded-2xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 flex items-center justify-center mb-3">
                <Droplets class="w-5 h-5" />
              </div>
              <h3 class="font-bold text-slate-900 dark:text-white text-base">Sarana Air Bersih (SAB)</h3>
              <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                Pengawasan kualitas dan perlindungan fisik sumber mata air, PDAM, sumur bor, dan tangki penampungan air bersih.
              </p>
              <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-between text-xs text-slate-600 dark:text-slate-400">
                <span>{{ stats.total_sab }} Sarana Terdaftar</span>
                <span class="font-bold font-mono text-[#005851] dark:text-emerald-400">{{ stats.total_insp_sab }} Riwayat Inspeksi</span>
              </div>
            </div>
            <button
              type="button"
              @click="switchTab('sab')"
              class="mt-4 w-full py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 transition flex items-center justify-center gap-2 cursor-pointer"
            >
              <span>Buka Modul SAB</span>
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>

          <!-- HSBU Box -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs flex flex-col justify-between">
            <div>
              <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-[#00A99D] flex items-center justify-center mb-3">
                <Building2 class="w-5 h-5" />
              </div>
              <h3 class="font-bold text-slate-900 dark:text-white text-base">Higiene Bangunan &amp; Usaha (HSBU)</h3>
              <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                Inspeksi gedung perkantoran, terminal penumpang, ruang kerja, pencahayaan, ventilasi, kebisingan, dan toilet bandara/pelabuhan.
              </p>
              <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-between text-xs text-slate-600 dark:text-slate-400">
                <span>{{ stats.total_hsbu }} Gedung Terdaftar</span>
                <span class="font-bold font-mono text-[#005851] dark:text-emerald-400">{{ stats.total_insp_hsbu }} Riwayat Inspeksi</span>
              </div>
            </div>
            <button
              type="button"
              @click="switchTab('hsbu')"
              class="mt-4 w-full py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 transition flex items-center justify-center gap-2 cursor-pointer"
            >
              <span>Buka Modul HSBU</span>
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- ==================== TAB CONTENT: TPP ==================== -->
      <div v-if="currentTab === 'tpp'" class="space-y-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
          <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <Utensils class="w-4 h-4 text-amber-500" />
                <span>Riwayat Inspeksi Tempat Pengelolaan Pangan (TPP)</span>
              </h2>
              <p class="text-xs text-slate-400 mt-0.5">Daftar pemeriksaan higiene sanitasi pangan berbasis risiko di pelabuhan dan bandara.</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full">
              {{ inspeksi_tpp?.total || 0 }} Total Data
            </span>
          </div>

          <div class="overflow-x-auto mt-4">
            <table class="w-full text-xs text-left">
              <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                  <th class="py-3 px-3">No</th>
                  <th class="py-3 px-3">Wilayah Kerja</th>
                  <th class="py-3 px-3">Tanggal</th>
                  <th class="py-3 px-3">Nama TPP</th>
                  <th class="py-3 px-3">Penanggung Jawab</th>
                  <th class="py-3 px-3">Petugas Pemeriksa</th>
                  <th class="py-3 px-3 text-center">Skor &amp; Risiko</th>
                  <th class="py-3 px-3 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="(item, idx) in inspeksi_tpp.data" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                  <td class="py-3 px-3 text-slate-400 font-mono">{{ ((inspeksi_tpp.current_page - 1) * inspeksi_tpp.per_page) + idx + 1 }}</td>
                  <td class="py-3 px-3 font-semibold text-slate-800 dark:text-slate-200">{{ item.wilker }}</td>
                  <td class="py-3 px-3 font-mono text-slate-500">{{ formatDate(item.tanggal_inspeksi) }}</td>
                  <td class="py-3 px-3 font-bold text-[#005851] dark:text-emerald-400">{{ item.nama_tpp }}</td>
                  <td class="py-3 px-3 text-slate-600 dark:text-slate-300">{{ item.penanggung_jawab_tpp || '-' }}</td>
                  <td class="py-3 px-3 text-slate-500 max-w-[200px] truncate" :title="item.nama_pemeriksa">{{ item.nama_pemeriksa }}</td>
                  <td class="py-3 px-3 text-center">
                    <span
                      class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                      :class="getRiskBadgeClass(item.tingkat_risiko)"
                    >
                      <span>Skor: {{ item.hasil }}</span> &bull; <span>{{ item.tingkat_risiko }}</span>
                    </span>
                  </td>
                  <td class="py-3 px-3 text-center">
                    <div class="inline-flex items-center gap-1.5">
                      <button
                        type="button"
                        @click="openDetail(item, 'tpp')"
                        class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 text-slate-600 dark:text-slate-300 transition cursor-pointer"
                        title="Lihat Rincian Inspeksi"
                      >
                        <Eye class="w-3.5 h-3.5" />
                      </button>
                      <a
                        :href="`/prl/inspeksi/tpp/${item.id}/pdf`"
                        target="_blank"
                        class="p-1.5 rounded-lg border border-rose-200 dark:border-rose-900/60 bg-rose-50/50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 hover:bg-rose-100 transition cursor-pointer"
                        title="Cetak Laporan PDF"
                      >
                        <Printer class="w-3.5 h-3.5" />
                      </a>
                    </div>
                  </td>
                </tr>
                <tr v-if="!inspeksi_tpp.data || inspeksi_tpp.data.length === 0">
                  <td colspan="8" class="text-center py-8 text-xs text-slate-400">Tidak ada data inspeksi TPP yang sesuai dengan filter.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="inspeksi_tpp.links && inspeksi_tpp.links.length > 3" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <span class="text-xs text-slate-400">Menampilkan {{ inspeksi_tpp.from || 0 }} - {{ inspeksi_tpp.to || 0 }} dari {{ inspeksi_tpp.total }} data</span>
            <div class="flex items-center gap-1">
              <Link
                v-for="(link, lIdx) in inspeksi_tpp.links"
                :key="lIdx"
                :href="link.url || '#'"
                v-html="link.label"
                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition"
                :class="link.active ? 'bg-[#005851] text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-40'"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- ==================== TAB CONTENT: SAB ==================== -->
      <div v-if="currentTab === 'sab'" class="space-y-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
          <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <Droplets class="w-4 h-4 text-sky-500" />
                <span>Riwayat Inspeksi Sarana Air Bersih (SAB)</span>
              </h2>
              <p class="text-xs text-slate-400 mt-0.5">Pengawasan kualitas fisik, sanitasi, dan perlindungan sarana air bersih di bandara &amp; pelabuhan.</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full">
              {{ inspeksi_sab?.total || 0 }} Total Data
            </span>
          </div>

          <div class="overflow-x-auto mt-4">
            <table class="w-full text-xs text-left">
              <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                  <th class="py-3 px-3">No</th>
                  <th class="py-3 px-3">Wilayah Kerja</th>
                  <th class="py-3 px-3">Tanggal</th>
                  <th class="py-3 px-3">Nama SAB</th>
                  <th class="py-3 px-3">Sumber Air / Kapasitas</th>
                  <th class="py-3 px-3">Petugas Pemeriksa</th>
                  <th class="py-3 px-3 text-center">Skor &amp; Risiko</th>
                  <th class="py-3 px-3 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="(item, idx) in inspeksi_sab.data" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                  <td class="py-3 px-3 text-slate-400 font-mono">{{ ((inspeksi_sab.current_page - 1) * inspeksi_sab.per_page) + idx + 1 }}</td>
                  <td class="py-3 px-3 font-semibold text-slate-800 dark:text-slate-200">{{ item.wilker }}</td>
                  <td class="py-3 px-3 font-mono text-slate-500">{{ formatDate(item.tanggal_inspeksi) }}</td>
                  <td class="py-3 px-3 font-bold text-[#005851] dark:text-emerald-400">{{ item.nama_sab }}</td>
                  <td class="py-3 px-3 text-slate-600 dark:text-slate-300">
                    {{ item.sumber_air_sab || '-' }} ({{ item.kapasitas_penampungan_sab || 0 }} L)
                  </td>
                  <td class="py-3 px-3 text-slate-500 max-w-[200px] truncate" :title="item.nama_pemeriksa">{{ item.nama_pemeriksa }}</td>
                  <td class="py-3 px-3 text-center">
                    <span
                      class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                      :class="getRiskBadgeClass(item.tingkat_risiko)"
                    >
                      <span>Skor: {{ item.hasil }}</span> &bull; <span>{{ item.tingkat_risiko }}</span>
                    </span>
                  </td>
                  <td class="py-3 px-3 text-center">
                    <div class="inline-flex items-center gap-1.5">
                      <button
                        type="button"
                        @click="openDetail(item, 'sab')"
                        class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 text-slate-600 dark:text-slate-300 transition cursor-pointer"
                        title="Lihat Rincian Inspeksi"
                      >
                        <Eye class="w-3.5 h-3.5" />
                      </button>
                      <a
                        :href="`/prl/inspeksi/sab/${item.id}/pdf`"
                        target="_blank"
                        class="p-1.5 rounded-lg border border-rose-200 dark:border-rose-900/60 bg-rose-50/50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 hover:bg-rose-100 transition cursor-pointer"
                        title="Cetak Laporan PDF"
                      >
                        <Printer class="w-3.5 h-3.5" />
                      </a>
                    </div>
                  </td>
                </tr>
                <tr v-if="!inspeksi_sab.data || inspeksi_sab.data.length === 0">
                  <td colspan="8" class="text-center py-8 text-xs text-slate-400">Tidak ada data inspeksi SAB yang sesuai dengan filter.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="inspeksi_sab.links && inspeksi_sab.links.length > 3" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <span class="text-xs text-slate-400">Menampilkan {{ inspeksi_sab.from || 0 }} - {{ inspeksi_sab.to || 0 }} dari {{ inspeksi_sab.total }} data</span>
            <div class="flex items-center gap-1">
              <Link
                v-for="(link, lIdx) in inspeksi_sab.links"
                :key="lIdx"
                :href="link.url || '#'"
                v-html="link.label"
                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition"
                :class="link.active ? 'bg-[#005851] text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-40'"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- ==================== TAB CONTENT: HSBU ==================== -->
      <div v-if="currentTab === 'hsbu'" class="space-y-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
          <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <Building2 class="w-4 h-4 text-emerald-600" />
                <span>Riwayat Inspeksi Higiene Sanitasi Bangunan dan Usaha (HSBU)</span>
              </h2>
              <p class="text-xs text-slate-400 mt-0.5">Penilaian faktor risiko lingkungan gedung perkantoran, terminal penumpang, ruang kerja, dan toilet.</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full">
              {{ inspeksi_hsbu?.total || 0 }} Total Data
            </span>
          </div>

          <div class="overflow-x-auto mt-4">
            <table class="w-full text-xs text-left">
              <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                  <th class="py-3 px-3">No</th>
                  <th class="py-3 px-3">Wilayah Kerja</th>
                  <th class="py-3 px-3">Tanggal</th>
                  <th class="py-3 px-3">Nama Bangunan / Gedung</th>
                  <th class="py-3 px-3">Penanggung Jawab</th>
                  <th class="py-3 px-3">Petugas Pemeriksa</th>
                  <th class="py-3 px-3 text-center">Skor &amp; Risiko</th>
                  <th class="py-3 px-3 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="(item, idx) in inspeksi_hsbu.data" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                  <td class="py-3 px-3 text-slate-400 font-mono">{{ ((inspeksi_hsbu.current_page - 1) * inspeksi_hsbu.per_page) + idx + 1 }}</td>
                  <td class="py-3 px-3 font-semibold text-slate-800 dark:text-slate-200">{{ item.wilker }}</td>
                  <td class="py-3 px-3 font-mono text-slate-500">{{ formatDate(item.tanggal_inspeksi) }}</td>
                  <td class="py-3 px-3 font-bold text-[#005851] dark:text-emerald-400">{{ item.nama_gedung }}</td>
                  <td class="py-3 px-3 text-slate-600 dark:text-slate-300">{{ item.penanggung_jawab_hsbu || '-' }}</td>
                  <td class="py-3 px-3 text-slate-500 max-w-[200px] truncate" :title="item.nama_pemeriksa">{{ item.nama_pemeriksa }}</td>
                  <td class="py-3 px-3 text-center">
                    <span
                      class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                      :class="getRiskBadgeClass(item.tingkat_risiko)"
                    >
                      <span>Skor: {{ item.total }}</span> &bull; <span>{{ item.tingkat_risiko }}</span>
                    </span>
                  </td>
                  <td class="py-3 px-3 text-center">
                    <div class="inline-flex items-center gap-1.5">
                      <button
                        type="button"
                        @click="openDetail(item, 'hsbu')"
                        class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 text-slate-600 dark:text-slate-300 transition cursor-pointer"
                        title="Lihat Rincian Inspeksi"
                      >
                        <Eye class="w-3.5 h-3.5" />
                      </button>
                      <a
                        :href="`/prl/inspeksi/hsbu/${item.id}/pdf`"
                        target="_blank"
                        class="p-1.5 rounded-lg border border-rose-200 dark:border-rose-900/60 bg-rose-50/50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 hover:bg-rose-100 transition cursor-pointer"
                        title="Cetak Laporan PDF"
                      >
                        <Printer class="w-3.5 h-3.5" />
                      </a>
                    </div>
                  </td>
                </tr>
                <tr v-if="!inspeksi_hsbu.data || inspeksi_hsbu.data.length === 0">
                  <td colspan="8" class="text-center py-8 text-xs text-slate-400">Tidak ada data inspeksi HSBU yang sesuai dengan filter.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="inspeksi_hsbu.links && inspeksi_hsbu.links.length > 3" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <span class="text-xs text-slate-400">Menampilkan {{ inspeksi_hsbu.from || 0 }} - {{ inspeksi_hsbu.to || 0 }} dari {{ inspeksi_hsbu.total }} data</span>
            <div class="flex items-center gap-1">
              <Link
                v-for="(link, lIdx) in inspeksi_hsbu.links"
                :key="lIdx"
                :href="link.url || '#'"
                v-html="link.label"
                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition"
                :class="link.active ? 'bg-[#005851] text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-40'"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- ==================== TAB CONTENT: SIVERA ==================== -->
      <div v-if="currentTab === 'sivera'" class="space-y-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
          <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <Bug class="w-4 h-4 text-indigo-500" />
                <span>Surveilans Vektor &amp; Reservoar (SIVERA)</span>
              </h2>
              <p class="text-xs text-slate-400 mt-0.5">Pemantauan kepadatan Jentik Aedes sp., Lalat, Tikus, Kecoa, Pinjal, dan Nyamuk Anopheles.</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full">
              {{ sivera_list?.total || 0 }} Pemantauan
            </span>
          </div>

          <div class="overflow-x-auto mt-4">
            <table class="w-full text-xs text-left">
              <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                  <th class="py-3 px-3">No</th>
                  <th class="py-3 px-3">Jenis Vektor</th>
                  <th class="py-3 px-3">Wilayah Kerja</th>
                  <th class="py-3 px-3">Lokasi Pemantauan</th>
                  <th class="py-3 px-3">Tanggal</th>
                  <th class="py-3 px-3">Parameter Indeks</th>
                  <th class="py-3 px-3 text-center">Status Baku Mutu</th>
                  <th class="py-3 px-3">Tindakan Pengendalian</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="(item, idx) in sivera_list.data" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                  <td class="py-3 px-3 text-slate-400 font-mono">{{ ((sivera_list.current_page - 1) * sivera_list.per_page) + idx + 1 }}</td>
                  <td class="py-3 px-3 font-bold text-indigo-600 dark:text-indigo-400">{{ item.jenis_vektor }}</td>
                  <td class="py-3 px-3 font-semibold text-slate-800 dark:text-slate-200">{{ item.wilker }}</td>
                  <td class="py-3 px-3 text-slate-600 dark:text-slate-300">{{ item.lokasi }}</td>
                  <td class="py-3 px-3 font-mono text-slate-500">{{ formatDate(item.tanggal) }}</td>
                  <td class="py-3 px-3 font-mono font-bold text-slate-800 dark:text-slate-200">{{ item.parameter_utama }}</td>
                  <td class="py-3 px-3 text-center">
                    <span
                      class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                      :class="item.baku_mutu_status === 'Memenuhi Syarat' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800'"
                    >
                      {{ item.baku_mutu_status }}
                    </span>
                  </td>
                  <td class="py-3 px-3 text-slate-500 max-w-[250px] truncate" :title="item.tindakan_pengendalian">
                    {{ item.tindakan_pengendalian || '-' }}
                  </td>
                </tr>
                <tr v-if="!sivera_list.data || sivera_list.data.length === 0">
                  <td colspan="8" class="text-center py-8 text-xs text-slate-400">Tidak ada data surveilans vektor SIVERA yang tercatat.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ==================== MODAL DETAIL INSPEKSI ==================== -->
      <div
        v-if="showDetailModal && detailItem"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
      >
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
              <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-emerald-600">Rincian Hasil Inspeksi Sanitasi</span>
              <h3 class="text-base font-extrabold text-slate-900 dark:text-white mt-0.5">
                {{ detailItem.nama_tpp || detailItem.nama_sab || detailItem.nama_gedung }}
              </h3>
            </div>
            <button
              type="button"
              @click="closeDetail"
              class="p-1.5 rounded-xl hover:bg-slate-100 text-slate-400 hover:text-slate-700 cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <div class="grid grid-cols-2 gap-3 text-xs bg-slate-50 dark:bg-slate-800/40 p-3.5 rounded-2xl">
            <div>
              <span class="text-slate-400 block text-[11px]">Wilayah Kerja:</span>
              <strong class="text-slate-800 dark:text-slate-200">{{ detailItem.wilker }}</strong>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px]">Tanggal Inspeksi:</span>
              <strong class="text-slate-800 dark:text-slate-200">{{ formatDate(detailItem.tanggal_inspeksi) }}</strong>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px]">Penanggung Jawab:</span>
              <strong class="text-slate-800 dark:text-slate-200">
                {{ detailItem.penanggung_jawab_tpp || detailItem.penanggung_jawab_sab || detailItem.penanggung_jawab_hsbu || '-' }}
              </strong>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px]">Petugas Pemeriksa:</span>
              <strong class="text-slate-800 dark:text-slate-200">{{ detailItem.nama_pemeriksa }}</strong>
            </div>
          </div>

          <!-- Skor Box -->
          <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-500 font-semibold">Tingkat Risiko &amp; Nilai Skor:</span>
              <div class="text-xl font-extrabold text-slate-900 dark:text-white mt-1">
                Skor: {{ detailItem.hasil !== undefined ? detailItem.hasil : detailItem.total }}
              </div>
            </div>
            <span
              class="px-3 py-1 rounded-full text-xs font-bold border"
              :class="getRiskBadgeClass(detailItem.tingkat_risiko)"
            >
              {{ detailItem.tingkat_risiko }}
            </span>
          </div>

          <!-- Saran Tindakan -->
          <div v-if="detailItem.saran_rencana_tindakan" class="text-xs space-y-1">
            <span class="font-bold text-slate-700 dark:text-slate-300">Saran Rencana Tindak Lanjut:</span>
            <p class="p-3 bg-slate-50 dark:bg-slate-800/40 rounded-xl text-slate-600 dark:text-slate-300">
              {{ detailItem.saran_rencana_tindakan }}
            </p>
          </div>

          <!-- Kesimpulan / Catatan -->
          <div v-if="detailItem.kesimpulan || detailItem.catatan" class="text-xs space-y-1">
            <span class="font-bold text-slate-700 dark:text-slate-300">Kesimpulan / Catatan Tambahan:</span>
            <p class="p-3 bg-slate-50 dark:bg-slate-800/40 rounded-xl text-slate-600 dark:text-slate-300">
              {{ detailItem.kesimpulan || detailItem.catatan }}
            </p>
          </div>

          <div class="pt-2 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="closeDetail"
              class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 hover:bg-slate-50 cursor-pointer"
            >
              Tutup
            </button>
            <a
              :href="`/prl/inspeksi/${detailType}/${detailItem.id}/pdf`"
              target="_blank"
              class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 hover:opacity-95 text-xs font-bold text-white transition flex items-center gap-1.5 cursor-pointer shadow-sm"
            >
              <Printer class="w-3.5 h-3.5" />
              <span>Cetak Dokumen PDF</span>
            </a>
          </div>
        </div>
      </div>

      <!-- ==================== MODAL TAMBAH SARANA ==================== -->
      <div
        v-if="showAddSaranaModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
      >
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
              <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#00A99D]">Form Registrasi Sarana</span>
              <h3 class="text-base font-extrabold text-slate-900 dark:text-white mt-0.5">
                Tambah Sarana {{ addSaranaType === 'tpp' ? 'TPP' : (addSaranaType === 'sab' ? 'SAB' : 'HSBU') }}
              </h3>
            </div>
            <button
              type="button"
              @click="showAddSaranaModal = false"
              class="p-1.5 rounded-xl hover:bg-slate-100 text-slate-400 hover:text-slate-700 cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <!-- Radio Type Switcher -->
          <div class="flex items-center gap-2 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl text-xs">
            <button
              type="button"
              @click="addSaranaType = 'tpp'"
              class="flex-1 py-1.5 rounded-lg font-bold transition text-center"
              :class="addSaranaType === 'tpp' ? 'bg-white dark:bg-slate-700 text-[#005851] dark:text-emerald-400 shadow-xs' : 'text-slate-500'"
            >
              TPP
            </button>
            <button
              type="button"
              @click="addSaranaType = 'sab'"
              class="flex-1 py-1.5 rounded-lg font-bold transition text-center"
              :class="addSaranaType === 'sab' ? 'bg-white dark:bg-slate-700 text-[#005851] dark:text-emerald-400 shadow-xs' : 'text-slate-500'"
            >
              SAB
            </button>
            <button
              type="button"
              @click="addSaranaType = 'hsbu'"
              class="flex-1 py-1.5 rounded-lg font-bold transition text-center"
              :class="addSaranaType === 'hsbu' ? 'bg-white dark:bg-slate-700 text-[#005851] dark:text-emerald-400 shadow-xs' : 'text-slate-500'"
            >
              HSBU
            </button>
          </div>

          <div class="space-y-3 text-xs">
            <div>
              <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Wilayah Kerja</label>
              <select
                v-model="saranaForm.wilker"
                class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
              >
                <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
              </select>
            </div>

            <div>
              <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">
                {{ addSaranaType === 'tpp' ? 'Nama TPP (Warung/Restoran/Kantin)' : (addSaranaType === 'sab' ? 'Nama Sarana Air Bersih' : 'Nama Gedung / Bangunan Usaha') }}
              </label>
              <input
                type="text"
                v-model="saranaForm.nama"
                placeholder="Contoh: Kantin Sehat Bandara Kertajati"
                class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
              />
            </div>

            <div>
              <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Penanggung Jawab / Pemilik</label>
              <input
                type="text"
                v-model="saranaForm.penanggung_jawab"
                placeholder="Nama pemilik atau pengelola"
                class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
              />
            </div>

            <div v-if="addSaranaType === 'sab'">
              <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nomor Telepon / HP</label>
              <input
                type="text"
                v-model="saranaForm.no_hp"
                placeholder="0812xxxxxxxx"
                class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
              />
            </div>

            <div v-if="addSaranaType === 'sab'">
              <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Sumber Air</label>
              <select
                v-model="saranaForm.sumber_air"
                class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
              >
                <option value="PDAM">PDAM</option>
                <option value="Mata Air">Mata Air</option>
                <option value="Sumur Bor">Sumur Bor</option>
                <option value="Air Tanah">Air Tanah</option>
                <option value="Pengolahan Mandiri (WTP)">Pengolahan Mandiri (WTP)</option>
              </select>
            </div>
          </div>

          <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
            <button
              type="button"
              @click="showAddSaranaModal = false"
              class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 hover:bg-slate-50 cursor-pointer"
            >
              Batal
            </button>
            <button
              type="button"
              @click="submitSarana"
              :disabled="isSubmittingSarana"
              class="px-5 py-2 rounded-xl bg-gradient-to-r from-[#00A99D] to-[#005851] hover:opacity-95 text-xs font-bold text-white transition cursor-pointer shadow-sm disabled:opacity-50"
            >
              {{ isSubmittingSarana ? 'Menyimpan...' : 'Simpan Sarana' }}
            </button>
          </div>
        </div>
      </div>

    </main>
  </HybridNavbar>
</template>
