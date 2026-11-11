<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
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
  Trash2,
  X,
  FileSpreadsheet,
  Clock,
  Compass,
  Check,
  Info,
  Calendar,
  Layers,
  Award,
  DollarSign,
  TrendingUp,
  Activity,
  Edit2,
  Save,
  CheckCircle,
  RefreshCw,
  Printer,
  FileBarChart,
  Users,
  Building,
  Flag,
  ChevronDown,
  Globe,
  Database,
  UploadCloud,
  Server,
  AlertCircle,
} from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
  surveillances: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  ports: {
    type: Array,
    default: () => [],
  },
  summary: {
    type: Object,
    default: () => ({}),
  },
  pnbp_per_pelabuhan: {
    type: Array,
    default: () => [],
  },
  top_verifikators: {
    type: Array,
    default: () => [],
  },
  kpi_targets: {
    type: Object,
    default: () => ({}),
  },
  quarantine_questions: {
    type: Object,
    default: () => ({}),
  },
});

const page = usePage();
const { formatDate } = useDate();

// Top Verifikator Leaderboard (Mengecualikan MOHAMAD DAHLAN FAZHRY)
const filteredTopVerifikators = computed(() => {
  return (props.top_verifikators || []).filter((ver) => {
    const name = (ver?.verifier_name || '').toUpperCase().trim();
    return !name.includes('MOHAMAD DAHLAN FAZHRY');
  });
});

// Active Tab ('data' | 'laporan')
const activeTab = ref('data');

// Filter State
const searchInput = ref(props.filters.search || '');
const selectedPelabuhan = ref(props.filters.pelabuhan || '');
const selectedKategori = ref(props.filters.kategori || '');
const selectedKresiko = ref(props.filters.kresiko || '');
const selectedStatus = ref(props.filters.status || '');
const selectedStartDate = ref(props.filters.start_date || '');
const selectedEndDate = ref(props.filters.end_date || '');
const selectedBulan = ref(props.filters.bulan || new Date().getMonth() + 1);
const selectedTriwulan = ref(props.filters.triwulan || Math.ceil((new Date().getMonth() + 1) / 3));
const selectedTahun = ref(props.filters.tahun || new Date().getFullYear());

const handleFilter = () => {
  router.get('/ship-surveillance', {
    search: searchInput.value || undefined,
    pelabuhan: selectedPelabuhan.value || undefined,
    kategori: selectedKategori.value || undefined,
    kresiko: selectedKresiko.value || undefined,
    status: selectedStatus.value || undefined,
    start_date: selectedStartDate.value || undefined,
    end_date: selectedEndDate.value || undefined,
    bulan: selectedBulan.value || undefined,
    triwulan: selectedTriwulan.value || undefined,
    tahun: selectedTahun.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const resetFilter = () => {
  searchInput.value = '';
  selectedPelabuhan.value = '';
  selectedKategori.value = '';
  selectedKresiko.value = '';
  selectedStatus.value = '';
  selectedStartDate.value = '';
  selectedEndDate.value = '';
  router.get('/ship-surveillance', {}, { preserveState: true });
};

// Inline Editing Gross Tonnage
const editingGtId = ref(null);
const editingGtValue = ref(0);
const isSavingGt = ref(false);

const startEditGt = (item) => {
  editingGtId.value = item.id;
  editingGtValue.value = item.gross_tonnage;
};

const cancelEditGt = () => {
  editingGtId.value = null;
  editingGtValue.value = 0;
};

const saveGt = async (item) => {
  if (editingGtValue.value < 0) return;
  isSavingGt.value = true;
  try {
    const res = await fetch(`/ship-surveillance/${item.id}/gross-tonnage`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ gross_tonnage: editingGtValue.value }),
    });
    const result = await res.json();
    if (result.success) {
      item.gross_tonnage = result.data.gross_tonnage;
      item.estimasi_pnbp = result.data.estimasi_pnbp;
      editingGtId.value = null;
    } else {
      alert(result.message || 'Gagal memperbarui Gross Tonnage');
    }
  } catch (err) {
    alert('Terjadi kesalahan koneksi saat memperbarui Gross Tonnage');
  } finally {
    isSavingGt.value = false;
  }
};

// Detail Modal
const activeItem = ref(null);
const showDetailModal = ref(false);

const openDetailModal = (item) => {
  activeItem.value = item;
  showDetailModal.value = true;
};

const closeDetailModal = () => {
  activeItem.value = null;
  showDetailModal.value = false;
};

// Verification Modal
const showVerifyModal = ref(false);
const verifyTarget = ref(null);
const verifyDecision = ref('SANDAR');
const verifyNotes = ref('');
const isSubmittingVerify = ref(false);

const openVerifyModal = (item) => {
  verifyTarget.value = item;
  verifyDecision.value = item.hasil_status || 'SANDAR';
  verifyNotes.value = item.catatan_verifikasi || '';
  showVerifyModal.value = true;
};

const closeVerifyModal = () => {
  verifyTarget.value = null;
  showVerifyModal.value = false;
};

const submitVerify = () => {
  if (!verifyTarget.value) return;
  isSubmittingVerify.value = true;
  router.put(`/ship-surveillance/${verifyTarget.value.id}/verify`, {
    hasil_status: verifyDecision.value,
    catatan_verifikasi: verifyNotes.value,
  }, {
    onSuccess: () => {
      closeVerifyModal();
    },
    onFinish: () => {
      isSubmittingVerify.value = false;
    },
  });
};

// Delete Action
const deleteItem = (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus data pengawasan kapal "${item.nama_kapal}"?`)) {
    router.delete(`/ship-surveillance/${item.id}`);
  }
};

// Helpers
const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

const formatNumber = (val) => {
  return new Intl.NumberFormat('id-ID').format(val || 0);
};

const getRiskBadgeClass = (kresiko) => {
  switch (kresiko) {
    case 'TINGGI':
      return 'bg-red-500/10 text-red-700 dark:text-red-400 border border-red-500/30';
    case 'SEDANG':
      return 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/30';
    default:
      return 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30';
  }
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'ZONA KARANTINA':
      return 'bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-500/30';
    case 'SANDAR':
      return 'bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border border-cyan-500/30';
    default:
      return 'bg-slate-500/10 text-slate-700 dark:text-slate-400 border border-slate-500/30';
  }
};

const getQuestionDisplay = (item, qNo) => {
  const no = Number(qNo);
  const questions = item?.questions || {};
  const entry = questions[no] ?? questions[String(no)] ?? null;

  if (entry && typeof entry === 'object' && 'answer' in entry) {
    const score = Number(entry.score || 0);
    const isNeutral = no === 1 && (entry.answer === 'Tidak Berlaku (Domestik)' || entry.answer === '-');
    const isRisk = !isNeutral && (score > 0 || entry.answer === 'Ya' || (no === 19 && entry.answer === 'Tidak'));
    return {
      text: entry.answer || '-',
      badgeClass: isRisk
        ? 'bg-red-500/10 text-red-600 border border-red-500/30'
        : isNeutral
          ? 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700'
          : 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/30',
    };
  }

  if (no === 1) {
    if (item?.kategori === 'Dalam Negeri' && !item?.negara_singgah_14_hari) {
      return {
        text: 'Tidak Berlaku (Domestik)',
        badgeClass: 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700',
      };
    }
    if (item?.negara_singgah_14_hari) {
      const legacyScore = Number(questions.Q1 ?? questions.q1 ?? 0);
      return {
        text: item.negara_singgah_14_hari,
        badgeClass: legacyScore > 0
          ? 'bg-red-500/10 text-red-600 border border-red-500/30'
          : 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/30',
      };
    }
  }

  const rawVal = questions[`Q${no}`] ?? questions[`q${no}`];
  if (rawVal !== undefined && rawVal !== null) {
    const numVal = Number(rawVal);
    const isRisk = numVal > 0;
    const text = no === 19 ? (numVal === 0 ? 'Ya' : 'Tidak') : (numVal > 0 ? 'Ya' : 'Tidak');
    return {
      text,
      badgeClass: isRisk
        ? 'bg-red-500/10 text-red-600 border border-red-500/30'
        : 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/30',
    };
  }

  return {
    text: no === 1 && item?.kategori === 'Dalam Negeri' ? 'Tidak Berlaku (Domestik)' : 'Tidak',
    badgeClass: 'bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700',
  };
};

// Portal Pelayanan TANGKAP Dropdown State
const showPortalDropdown = ref(false);
const portalDropdownRef = ref(null);

const handleDocumentClick = (e) => {
  if (portalDropdownRef.value && !portalDropdownRef.value.contains(e.target)) {
    showPortalDropdown.value = false;
  }
};

onMounted(() => {
  if (typeof document !== 'undefined') {
    document.addEventListener('click', handleDocumentClick);
  }
});

onUnmounted(() => {
  if (typeof document !== 'undefined') {
    document.removeEventListener('click', handleDocumentClick);
  }
});
</script>

<template>
  <Head title="Pengawasan Kedatangan Kapal (TANGKAP) &bull; BKK Bandung" />

  <HybridNavbar mode="inertia" currentRoute="ship-surveillance">
    <main class="w-full px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">
      
      <!-- Top Title & Navigation Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="space-y-1">
          <div class="flex items-center gap-3">
            <img 
              src="/storage/images/LOGO KARANTINA.png" 
              alt="Logo Karantina Kesehatan" 
              class="h-11 w-11 object-contain drop-shadow-xs shrink-0"
              onerror="this.onerror=null;this.src='/images/LOGO KARANTINA.png';"
            />
            <div>
              <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                Pengawasan Kedatangan Kapal (TANGKAP)
                <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                  Kekarantinaan Pelabuhan
                </span>
              </h1>
              <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Digitalisasi Self-Assessment Maritime Declaration of Health, Skoring Faktor Risiko, Verifikasi Sandar &amp; Estimasi PNBP
              </p>
            </div>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <!-- Tab Navigation -->
          <div class="inline-flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 border border-slate-200 dark:border-slate-700/60">
            <button
              type="button"
              @click="activeTab = 'data'"
              :class="[
                'px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5',
                activeTab === 'data'
                  ? 'bg-white dark:bg-slate-900 text-[#005851] dark:text-[#00A99D] shadow-sm'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
              ]"
            >
              <Layers class="w-3.5 h-3.5" />
              Data Kedatangan
            </button>
            <button
              type="button"
              @click="activeTab = 'laporan'"
              :class="[
                'px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5',
                activeTab === 'laporan'
                  ? 'bg-white dark:bg-slate-900 text-[#005851] dark:text-[#00A99D] shadow-sm'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
              ]"
            >
              <FileBarChart class="w-3.5 h-3.5" />
              Laporan & Analitik Kinerja
            </button>
          </div>

          <!-- Export CSV -->
          <a
            :href="`/ship-surveillance/export/csv?pelabuhan=${selectedPelabuhan}&kategori=${selectedKategori}&kresiko=${selectedKresiko}&status=${selectedStatus}`"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition"
          >
            <Download class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
            Export CSV
          </a>

          <!-- Public Self-Assessment Portal Link (TANGKAP - Native Sadaya) -->
          <div ref="portalDropdownRef" class="relative inline-flex items-center shadow-xs">
            <a
              href="/ship-surveillance/portal"
              target="_blank"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-l-xl bg-gradient-to-r from-[#005851] to-[#00A99D] text-white text-xs font-semibold hover:opacity-95 transition cursor-pointer"
              title="Buka Portal Self-Assessment TANGKAP (Langsung di Sadaya)"
            >
              <ExternalLink class="w-3.5 h-3.5" />
              <span>Portal Pelayanan (TANGKAP)</span>
            </a>
            <button
              type="button"
              @click="showPortalDropdown = !showPortalDropdown"
              class="px-2 py-1.5 rounded-r-xl bg-[#00A99D] hover:bg-[#008f84] text-white text-xs border-l border-white/20 transition flex items-center justify-center cursor-pointer"
              title="Pilih Formulir Spesifik"
            >
              <ChevronDown class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': showPortalDropdown }" />
            </button>

            <!-- Dropdown Menu -->
            <div
              v-if="showPortalDropdown"
              class="absolute right-0 top-full mt-2 w-72 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl z-50 p-2 space-y-1"
            >
              <div class="px-3 py-1.5 text-[10px] font-mono uppercase text-slate-400 tracking-wider">
                Portal Karantina Kesehatan (TANGKAP)
              </div>
              
              <a
                href="/ship-surveillance/portal"
                target="_blank"
                @click="showPortalDropdown = false"
                class="flex items-start gap-2.5 p-2 rounded-xl text-xs hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition group"
              >
                <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                  <Globe class="w-4 h-4" />
                </div>
                <div class="min-w-0">
                  <div class="font-bold text-slate-900 dark:text-white group-hover:text-[#00A99D] transition">
                    Portal Utama TANGKAP ↗
                  </div>
                  <div class="text-[11px] text-slate-500 line-clamp-1">
                    Pilihan formulir kedatangan kapal
                  </div>
                </div>
              </a>

              <a
                href="/ship-surveillance/assessment/domestic"
                target="_blank"
                @click="showPortalDropdown = false"
                class="flex items-start gap-2.5 p-2 rounded-xl text-xs hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition group"
              >
                <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                  <Flag class="w-4 h-4" />
                </div>
                <div class="min-w-0">
                  <div class="font-bold text-slate-900 dark:text-white group-hover:text-[#00A99D] transition">
                    Kapal Dari Dalam Negeri ↗
                  </div>
                  <div class="text-[11px] text-slate-500 line-clamp-1">
                    Formulir self-assessment rute domestik
                  </div>
                </div>
              </a>

              <a
                href="/ship-surveillance/assessment/international"
                target="_blank"
                @click="showPortalDropdown = false"
                class="flex items-start gap-2.5 p-2 rounded-xl text-xs hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition group"
              >
                <div class="w-7 h-7 rounded-lg bg-cyan-500/10 text-cyan-600 flex items-center justify-center shrink-0 mt-0.5">
                  <Ship class="w-4 h-4" />
                </div>
                <div class="min-w-0">
                  <div class="font-bold text-slate-900 dark:text-white group-hover:text-[#00A99D] transition">
                    Ship Arriving from Abroad ↗
                  </div>
                  <div class="text-[11px] text-slate-500 line-clamp-1">
                    Form English kapal luar negeri (TANGKAP)
                  </div>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick KPI Counter Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <!-- Total Kapal -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
            <span class="text-xs font-medium uppercase tracking-wider">Total Kapal</span>
            <Ship class="w-4 h-4 text-cyan-600" />
          </div>
          <div class="mt-2">
            <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
              {{ formatNumber(summary.total_kapal) }}
            </span>
            <span class="text-xs text-slate-400 ml-1">Kpl</span>
          </div>
        </div>

        <!-- Kapal LN -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
            <span class="text-xs font-medium uppercase tracking-wider">Luar Negeri</span>
            <Flag class="w-4 h-4 text-amber-500" />
          </div>
          <div class="mt-2">
            <span class="text-2xl font-bold tracking-tight text-amber-600 dark:text-amber-400">
              {{ formatNumber(summary.kapal_luar_negeri) }}
            </span>
            <span class="text-xs text-slate-400 ml-1">Kpl ({{ summary.total_kapal ? Math.round((summary.kapal_luar_negeri / summary.total_kapal) * 100) : 0 }}%)</span>
          </div>
        </div>

        <!-- Kapal DN -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
            <span class="text-xs font-medium uppercase tracking-wider">Dalam Negeri</span>
            <Compass class="w-4 h-4 text-emerald-600" />
          </div>
          <div class="mt-2">
            <span class="text-2xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400">
              {{ formatNumber(summary.kapal_dalam_negeri) }}
            </span>
            <span class="text-xs text-slate-400 ml-1">Kpl</span>
          </div>
        </div>

        <!-- Total GT -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
            <span class="text-xs font-medium uppercase tracking-wider">Total Bobot GT</span>
            <Anchor class="w-4 h-4 text-[#00A99D]" />
          </div>
          <div class="mt-2">
            <span class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
              {{ formatNumber(summary.total_gt) }}
            </span>
            <span class="text-xs text-slate-400 ml-1">GT</span>
          </div>
        </div>

        <!-- Estimasi PNBP -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
            <span class="text-xs font-medium uppercase tracking-wider">Estimasi PNBP</span>
            <DollarSign class="w-4 h-4 text-emerald-600" />
          </div>
          <div class="mt-2">
            <span class="text-lg font-bold tracking-tight text-emerald-600 dark:text-emerald-400">
              {{ formatCurrency(summary.total_pnbp) }}
            </span>
          </div>
        </div>

        <!-- High Risk Alert -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
            <span class="text-xs font-medium uppercase tracking-wider">Risiko Tinggi</span>
            <AlertTriangle class="w-4 h-4 text-rose-500" />
          </div>
          <div class="mt-2 flex items-baseline gap-2">
            <span class="text-2xl font-bold tracking-tight text-rose-600 dark:text-rose-400">
              {{ formatNumber(summary.kresiko_tinggi) }}
            </span>
            <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-rose-500/10 text-rose-600 border border-rose-500/20">
              Karantina
            </span>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 1: DATA PENGAWASAN KEDATANGAN KAPAL -->
      <!-- ========================================== -->
      <div v-show="activeTab === 'data'" class="space-y-6">
        
        <!-- Filter Bar -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300">
              <Filter class="w-4 h-4 text-[#00A99D]" />
              Filter Data Kedatangan Kapal
            </div>
            <button
              v-if="selectedPelabuhan || selectedKategori || selectedKresiko || selectedStatus || searchInput"
              @click="resetFilter"
              class="text-xs text-rose-600 dark:text-rose-400 hover:underline flex items-center gap-1 font-medium"
            >
              <RefreshCw class="w-3 h-3" />
              Reset Filter
            </button>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div>
              <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Pencarian</label>
              <div class="relative">
                <input
                  v-model="searchInput"
                  @keyup.enter="handleFilter"
                  type="text"
                  placeholder="Nama kapal / registrasi / asal..."
                  class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 pl-8 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                />
                <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" />
              </div>
            </div>

            <!-- Pelabuhan Tujuan -->
            <div>
              <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Pelabuhan Tujuan</label>
              <select
                v-model="selectedPelabuhan"
                @change="handleFilter"
                class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
              >
                <option value="">Semua Pelabuhan Wilker</option>
                <option v-for="port in ports" :key="port" :value="port">{{ port }}</option>
              </select>
            </div>

            <!-- Kategori Asal -->
            <div>
              <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Kategori Pelayaran</label>
              <select
                v-model="selectedKategori"
                @change="handleFilter"
                class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
              >
                <option value="">Semua Kategori</option>
                <option value="Dalam Negeri">Dalam Negeri (Domestik)</option>
                <option value="Luar Negeri">Luar Negeri (Internasional)</option>
              </select>
            </div>

            <!-- Tingkat Risiko -->
            <div>
              <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Faktor Risiko</label>
              <select
                v-model="selectedKresiko"
                @change="handleFilter"
                class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
              >
                <option value="">Semua Tingkat Risiko</option>
                <option value="RENDAH">Rendah (Skor &lt; 10)</option>
                <option value="SEDANG">Sedang (Skor 10 - 19)</option>
                <option value="TINGGI">Tinggi (Skor &ge; 20)</option>
              </select>
            </div>

            <!-- Status Keputusan -->
            <div>
              <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Keputusan / Status</label>
              <select
                v-model="selectedStatus"
                @change="handleFilter"
                class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
              >
                <option value="">Semua Status</option>
                <option value="SANDAR">Izin Sandar</option>
                <option value="ZONA KARANTINA">Zona Karantina (Lego Jangkar)</option>
                <option value="pending">Menunggu Verifikasi (Pending)</option>
                <option value="verified">Sudah Terverifikasi</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
          <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
              <Ship class="w-4 h-4 text-[#00A99D]" />
              Daftar Kedatangan Kapal &amp; Pengawasan Kesehatan
            </h2>
            <span class="text-xs text-slate-500 dark:text-slate-400">
              Menampilkan {{ surveillances.data?.length || 0 }} dari {{ surveillances.total || 0 }} data
            </span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                <tr>
                  <th class="py-3 px-3 w-12 text-center">No</th>
                  <th class="py-3 px-3">Waktu Kedatangan</th>
                  <th class="py-3 px-3">Identitas Kapal</th>
                  <th class="py-3 px-3">Rute Pelayaran</th>
                  <th class="py-3 px-3 w-32">Gross Tonnage (GT)</th>
                  <th class="py-3 px-3 text-center">Kategori</th>
                  <th class="py-3 px-3 text-center">Skor &amp; Risiko</th>
                  <th class="py-3 px-3 text-center">Keputusan</th>
                  <th class="py-3 px-3">Estimasi PNBP</th>
                  <th class="py-3 px-3 text-center w-28">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                <tr
                  v-for="(item, idx) in surveillances.data"
                  :key="item.id"
                  class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors"
                >
                  <!-- No -->
                  <td class="py-3 px-3 text-center font-mono text-slate-400">
                    {{ (surveillances.current_page - 1) * surveillances.per_page + idx + 1 }}
                  </td>

                  <!-- Waktu -->
                  <td class="py-3 px-3 whitespace-nowrap">
                    <div class="font-medium text-slate-800 dark:text-slate-200">
                      {{ formatDate(item.tanggal_kedatangan) }}
                    </div>
                    <div class="text-[11px] text-slate-400 font-mono">
                      {{ item.registration_no }}
                    </div>
                  </td>

                  <!-- Nama & Bendera -->
                  <td class="py-3 px-3">
                    <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                      {{ item.nama_kapal }}
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5">
                      <span>Bendera: {{ item.origin_country || 'N/A' }}</span>
                      <span>•</span>
                      <span>{{ item.jml_crew }} Crew</span>
                    </div>
                  </td>

                  <!-- Rute Pelayaran -->
                  <td class="py-3 px-3">
                    <div class="text-slate-700 dark:text-slate-300">
                      <span class="text-slate-400 text-[10px] uppercase font-semibold">Dari:</span> {{ item.datang_dari }}
                    </div>
                    <div class="text-[11px] text-[#00A99D] font-medium mt-0.5">
                      <span class="text-slate-400 text-[10px] uppercase font-semibold">Tujuan:</span> {{ item.pelabuhan }}
                    </div>
                  </td>

                  <!-- Gross Tonnage (Interactive Inline Editing) -->
                  <td class="py-3 px-3">
                    <div v-if="editingGtId === item.id" class="flex items-center gap-1">
                      <input
                        v-model.number="editingGtValue"
                        type="number"
                        min="0"
                        class="w-20 px-2 py-1 text-xs rounded border border-emerald-500 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none"
                      />
                      <button
                        @click="saveGt(item)"
                        :disabled="isSavingGt"
                        class="p-1 text-emerald-600 hover:bg-emerald-50 rounded"
                        title="Simpan GT"
                      >
                        <Check class="w-3.5 h-3.5" />
                      </button>
                      <button
                        @click="cancelEditGt"
                        class="p-1 text-rose-500 hover:bg-rose-50 rounded"
                        title="Batal"
                      >
                        <X class="w-3.5 h-3.5" />
                      </button>
                    </div>
                    <div v-else class="flex items-center justify-between group">
                      <span class="font-mono font-semibold text-slate-800 dark:text-slate-200">
                        {{ formatNumber(item.gross_tonnage) }} GT
                      </span>
                      <button
                        @click="startEditGt(item)"
                        class="opacity-0 group-hover:opacity-100 p-1 text-slate-400 hover:text-[#00A99D] transition"
                        title="Ubah Gross Tonnage"
                      >
                        <Edit2 class="w-3 h-3" />
                      </button>
                    </div>
                  </td>

                  <!-- Kategori Pelayaran -->
                  <td class="py-3 px-3 text-center whitespace-nowrap">
                    <span
                      :class="[
                        'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold',
                        item.kategori === 'Luar Negeri'
                          ? 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20'
                          : 'bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-500/20'
                      ]"
                    >
                      {{ item.kategori }}
                    </span>
                  </td>

                  <!-- Skor & Kresiko -->
                  <td class="py-3 px-3 text-center whitespace-nowrap">
                    <div class="flex flex-col items-center gap-1">
                      <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold', getRiskBadgeClass(item.kresiko)]">
                        {{ item.kresiko }} ({{ item.skoring }})
                      </span>
                    </div>
                  </td>

                  <!-- Keputusan Status -->
                  <td class="py-3 px-3 text-center whitespace-nowrap">
                    <div class="flex flex-col items-center gap-1">
                      <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold', getStatusBadgeClass(item.hasil_status)]">
                        {{ item.hasil_status }}
                      </span>
                      <span v-if="item.status_verifikasi === 'verified'" class="text-[9px] text-emerald-600 dark:text-emerald-400 flex items-center gap-0.5">
                        <CheckCircle class="w-2.5 h-2.5" /> Terverifikasi
                      </span>
                      <span v-else class="text-[9px] text-amber-600 dark:text-amber-400">
                        Pending
                      </span>
                    </div>
                  </td>

                  <!-- Estimasi PNBP -->
                  <td class="py-3 px-3 whitespace-nowrap">
                    <div class="font-mono text-emerald-600 dark:text-emerald-400 font-semibold">
                      {{ formatCurrency(item.estimasi_pnbp) }}
                    </div>
                    <div class="text-[10px] text-slate-400">
                      Tarif PP Kemenkes
                    </div>
                  </td>

                  <!-- Aksi -->
                  <td class="py-3 px-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1">
                      <!-- Detail Modal Button -->
                      <button
                        @click="openDetailModal(item)"
                        class="p-1.5 text-slate-600 hover:text-cyan-600 dark:text-slate-400 dark:hover:text-cyan-400 hover:bg-cyan-50 dark:hover:bg-cyan-900/30 rounded-lg transition"
                        title="Lihat Detail Lembar Karantina"
                      >
                        <Eye class="w-4 h-4" />
                      </button>

                      <!-- Verify Button -->
                      <button
                        @click="openVerifyModal(item)"
                        class="p-1.5 text-slate-600 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-lg transition"
                        title="Verifikasi Rekomendasi Sandar / Karantina"
                      >
                        <ShieldCheck class="w-4 h-4" />
                      </button>

                      <!-- Delete Button -->
                      <button
                        @click="deleteItem(item)"
                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition"
                        title="Hapus Data"
                      >
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="!surveillances.data || surveillances.data.length === 0">
                  <td colspan="10" class="py-12 text-center text-slate-400">
                    <Ship class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-700 mb-2" />
                    <p class="font-medium text-sm">Tidak ada data pengawasan kedatangan kapal yang cocok dengan filter.</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="surveillances.links && surveillances.links.length > 3" class="p-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <span class="text-xs text-slate-500">
              Halaman {{ surveillances.current_page }} dari {{ surveillances.last_page }}
            </span>
            <div class="flex items-center gap-1">
              <Link
                v-for="(link, i) in surveillances.links"
                :key="i"
                :href="link.url || '#'"
                v-html="link.label"
                :class="[
                  'px-3 py-1 text-xs rounded-lg transition',
                  link.active
                    ? 'bg-[#005851] text-white font-bold'
                    : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800',
                  !link.url ? 'opacity-40 cursor-not-allowed' : ''
                ]"
              />
            </div>
          </div>
        </div>

      </div>

      <!-- ========================================== -->
      <!-- TAB 2: LAPORAN & ANALITIK KINERJA -->
      <!-- ========================================== -->
      <div v-show="activeTab === 'laporan'" class="space-y-6">

        <!-- Laporan Overview & Top Verifikator -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          
          <!-- Top Verifikator Leaderboard -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
              <div class="flex items-center gap-2">
                <Award class="w-5 h-5 text-amber-500" />
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Verifikator Terbanyak</h3>
              </div>
              <span class="text-xs text-slate-400">Petugas Karantina</span>
            </div>

            <div class="space-y-3">
              <div
                v-for="(ver, index) in filteredTopVerifikators"
                :key="index"
                class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60"
              >
                <div class="flex items-center gap-2.5">
                  <span
                    :class="[
                      'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold',
                      index === 0 ? 'bg-amber-500 text-white' : index === 1 ? 'bg-slate-400 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300'
                    ]"
                  >
                    {{ index + 1 }}
                  </span>
                  <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                    {{ ver.verifier_name }}
                  </span>
                </div>
                <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                  {{ ver.jumlah_verifikasi }} Kapal
                </div>
              </div>

              <div v-if="!filteredTopVerifikators || filteredTopVerifikators.length === 0" class="text-center py-6 text-xs text-slate-400">
                Belum ada data verifikasi kapal.
              </div>
            </div>
          </div>

          <!-- Distribusi Risiko & Keputusan -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
              <Activity class="w-5 h-5 text-[#00A99D]" />
              <h3 class="text-sm font-bold text-slate-900 dark:text-white">Distribusi Faktor Risiko (Kresiko)</h3>
            </div>

            <div class="space-y-3">
              <!-- Risiko Rendah -->
              <div>
                <div class="flex justify-between text-xs mb-1 font-medium">
                  <span class="text-emerald-700 dark:text-emerald-400">RENDAH (Skor &lt; 10)</span>
                  <span class="font-bold">{{ summary.kresiko_rendah }} Kapal ({{ summary.total_kapal ? Math.round((summary.kresiko_rendah / summary.total_kapal) * 100) : 0 }}%)</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                  <div
                    class="bg-emerald-500 h-full rounded-full transition-all duration-500"
                    :style="{ width: (summary.total_kapal ? (summary.kresiko_rendah / summary.total_kapal) * 100 : 0) + '%' }"
                  ></div>
                </div>
              </div>

              <!-- Risiko Sedang -->
              <div>
                <div class="flex justify-between text-xs mb-1 font-medium">
                  <span class="text-amber-700 dark:text-amber-400">SEDANG (Skor 10 - 19)</span>
                  <span class="font-bold">{{ summary.kresiko_sedang }} Kapal ({{ summary.total_kapal ? Math.round((summary.kresiko_sedang / summary.total_kapal) * 100) : 0 }}%)</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                  <div
                    class="bg-amber-500 h-full rounded-full transition-all duration-500"
                    :style="{ width: (summary.total_kapal ? (summary.kresiko_sedang / summary.total_kapal) * 100 : 0) + '%' }"
                  ></div>
                </div>
              </div>

              <!-- Risiko Tinggi -->
              <div>
                <div class="flex justify-between text-xs mb-1 font-medium">
                  <span class="text-rose-700 dark:text-rose-400">TINGGI (Skor &ge; 20)</span>
                  <span class="font-bold">{{ summary.kresiko_tinggi }} Kapal ({{ summary.total_kapal ? Math.round((summary.kresiko_tinggi / summary.total_kapal) * 100) : 0 }}%)</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                  <div
                    class="bg-rose-500 h-full rounded-full transition-all duration-500"
                    :style="{ width: (summary.total_kapal ? (summary.kresiko_tinggi / summary.total_kapal) * 100 : 0) + '%' }"
                  ></div>
                </div>
              </div>
            </div>

            <!-- Keputusan Sandar vs Karantina -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 gap-2 text-center">
              <div class="p-2.5 rounded-xl bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-200 dark:border-cyan-800/40">
                <div class="text-[10px] uppercase font-bold text-cyan-700 dark:text-cyan-400">Izin Sandar</div>
                <div class="text-lg font-extrabold text-cyan-900 dark:text-cyan-200">{{ summary.status_sandar }} Kapal</div>
              </div>
              <div class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/40">
                <div class="text-[10px] uppercase font-bold text-rose-700 dark:text-rose-400">Zona Karantina</div>
                <div class="text-lg font-extrabold text-rose-900 dark:text-rose-200">{{ summary.status_karantina }} Kapal</div>
              </div>
            </div>
          </div>

          <!-- Tarif PNBP PP Kemenkes Reference Box -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div>
              <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3 mb-3">
                <Info class="w-5 h-5 text-emerald-600" />
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tarif PNBP Jasa Karantina</h3>
              </div>
              <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                Tarif resmi pemeriksaan kesehatan kapal dalam karantina berdasarkan Peraturan Pemerintah Tarif PNBP Kemenkes RI:
              </p>
              <div class="space-y-1.5 text-[11px] text-slate-600 dark:text-slate-300 max-h-48 overflow-y-auto pr-1">
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                  <span>&le; 100 GT</span>
                  <span class="font-bold">Rp 50.000</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                  <span>> 100 - 200 GT</span>
                  <span class="font-bold">Rp 60.000</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                  <span>> 200 - 350 GT</span>
                  <span class="font-bold">Rp 70.000</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                  <span>> 350 - 1.000 GT</span>
                  <span class="font-bold">Rp 85.000</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                  <span>> 1.000 - 2.000 GT</span>
                  <span class="font-bold">Rp 120.000</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                  <span>> 2.000 - 3.500 GT</span>
                  <span class="font-bold">Rp 150.000</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                  <span>> 3.500 - 7.000 GT</span>
                  <span class="font-bold">Rp 175.000</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                  <span>> 7.000 - 10.000 GT</span>
                  <span class="font-bold">Rp 200.000</span>
                </div>
                <div class="flex justify-between py-1">
                  <span>> 20.000 GT</span>
                  <span class="font-bold">Rp 300.000</span>
                </div>
              </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-xs">
              <span class="text-slate-500">Total Potensi PNBP:</span>
              <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono text-sm">{{ formatCurrency(summary.total_pnbp) }}</span>
            </div>
          </div>

        </div>

        <!-- Tabel Estimasi PNBP per Pelabuhan -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
          <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2">
              <Building class="w-5 h-5 text-[#00A99D]" />
              <h3 class="text-sm font-bold text-slate-900 dark:text-white">Estimasi Penerimaan PNBP per Pelabuhan Wilker</h3>
            </div>
            <span class="text-xs text-slate-400">Breakdown Real-Time</span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
              <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                <tr>
                  <th class="py-3 px-4">Pelabuhan Wilker</th>
                  <th class="py-3 px-4 text-center">Jumlah Kapal</th>
                  <th class="py-3 px-4 text-center">Akumulasi Bobot GT</th>
                  <th class="py-3 px-4 text-right">Estimasi PNBP (Rp)</th>
                  <th class="py-3 px-4 text-right">Kontribusi (%)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="(pnbp, index) in pnbp_per_pelabuhan" :key="index" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                  <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                    <Anchor class="w-3.5 h-3.5 text-[#00A99D]" />
                    {{ pnbp.pelabuhan }}
                  </td>
                  <td class="py-3 px-4 text-center font-mono">{{ pnbp.jumlah_kapal }} Kapal</td>
                  <td class="py-3 px-4 text-center font-mono">{{ formatNumber(pnbp.total_gt) }} GT</td>
                  <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                    {{ formatCurrency(pnbp.total_pnbp) }}
                  </td>
                  <td class="py-3 px-4 text-right font-mono font-semibold">{{ pnbp.persentase }}%</td>
                </tr>
              </tbody>
              <tfoot class="bg-slate-100/60 dark:bg-slate-800/80 font-bold border-t border-slate-200 dark:border-slate-700">
                <tr>
                  <td class="py-3 px-4">TOTAL KESELURUHAN</td>
                  <td class="py-3 px-4 text-center font-mono">{{ summary.total_kapal }} Kapal</td>
                  <td class="py-3 px-4 text-center font-mono">{{ formatNumber(summary.total_gt) }} GT</td>
                  <td class="py-3 px-4 text-right font-mono text-emerald-700 dark:text-emerald-300">
                    {{ formatCurrency(summary.total_pnbp) }}
                  </td>
                  <td class="py-3 px-4 text-right font-mono">100%</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- Target Capaian Perjanjian Kinerja (PK Kapal Luar Negeri) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2">
              <TrendingUp class="w-5 h-5 text-[#00A99D]" />
              <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Capaian Target Kedatangan Kapal Luar Negeri (Perjanjian Kinerja)</h3>
                <p class="text-xs text-slate-500">Monitoring realisasi pengawasan kapal internasional vs target kinerja tahun {{ selectedTahun }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <select v-model="selectedTahun" @change="handleFilter" class="text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-2.5 py-1">
                <option :value="2026">Tahun 2026</option>
                <option :value="2025">Tahun 2025</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Bulanan -->
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex flex-col justify-between">
              <div>
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Target Bulanan (Bulan {{ kpi_targets.bulanan?.bulan }})</span>
                  <span class="text-xs font-mono font-bold text-emerald-600">{{ kpi_targets.bulanan?.persentase }}%</span>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-700 h-3 rounded-full overflow-hidden mb-3">
                  <div class="bg-emerald-500 h-full rounded-full transition-all duration-500" :style="{ width: kpi_targets.bulanan?.persentase + '%' }"></div>
                </div>
              </div>
              <div class="flex justify-between items-center text-xs font-mono text-slate-500">
                <span>Realisasi: <strong class="text-slate-900 dark:text-white">{{ kpi_targets.bulanan?.realisasi }} Kpl</strong></span>
                <span>Target: <strong>{{ kpi_targets.bulanan?.target }} Kpl</strong></span>
              </div>
            </div>

            <!-- Triwulanan -->
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex flex-col justify-between">
              <div>
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Target Triwulan {{ kpi_targets.triwulanan?.triwulan }}</span>
                  <span class="text-xs font-mono font-bold text-amber-500">{{ kpi_targets.triwulanan?.persentase }}%</span>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-700 h-3 rounded-full overflow-hidden mb-3">
                  <div class="bg-amber-500 h-full rounded-full transition-all duration-500" :style="{ width: kpi_targets.triwulanan?.persentase + '%' }"></div>
                </div>
              </div>
              <div class="flex justify-between items-center text-xs font-mono text-slate-500">
                <span>Realisasi: <strong class="text-slate-900 dark:text-white">{{ kpi_targets.triwulanan?.realisasi }} Kpl</strong></span>
                <span>Target: <strong>{{ kpi_targets.triwulanan?.target }} Kpl</strong></span>
              </div>
            </div>

            <!-- Tahunan -->
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex flex-col justify-between">
              <div>
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Target Tahunan ({{ kpi_targets.tahunan?.tahun }})</span>
                  <span class="text-xs font-mono font-bold text-cyan-600">{{ kpi_targets.tahunan?.persentase }}%</span>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-700 h-3 rounded-full overflow-hidden mb-3">
                  <div class="bg-cyan-500 h-full rounded-full transition-all duration-500" :style="{ width: kpi_targets.tahunan?.persentase + '%' }"></div>
                </div>
              </div>
              <div class="flex justify-between items-center text-xs font-mono text-slate-500">
                <span>Realisasi: <strong class="text-slate-900 dark:text-white">{{ kpi_targets.tahunan?.realisasi }} Kpl</strong></span>
                <span>Target: <strong>{{ kpi_targets.tahunan?.target }} Kpl</strong></span>
              </div>
            </div>
          </div>
        </div>

      </div>

    </main>

    <!-- ============================================== -->
    <!-- MODAL DETAIL PEMERIKSAAN KARANTINA (TANGKAP)   -->
    <!-- ============================================== -->
    <div
      v-if="showDetailModal && activeItem"
      class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
    >
      <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-3xl w-full max-h-[90vh] flex flex-col shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-gradient-to-r from-[#005851] to-[#00A99D] text-white">
          <div class="flex items-center gap-2.5">
            <Ship class="w-5 h-5" />
            <div>
              <h3 class="font-bold text-base">{{ activeItem.nama_kapal }}</h3>
              <p class="text-xs text-white/80">No Registrasi: {{ activeItem.registration_no }}</p>
            </div>
          </div>
          <button @click="closeDetailModal" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto space-y-6 text-xs text-slate-700 dark:text-slate-300">
          
          <!-- Vessel Profile Summary -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 dark:bg-slate-800/60 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
            <div>
              <div class="text-slate-400 font-medium">Pelabuhan Asal</div>
              <div class="font-bold text-slate-900 dark:text-white mt-0.5">{{ activeItem.datang_dari }}</div>
            </div>
            <div>
              <div class="text-slate-400 font-medium">Pelabuhan Tujuan</div>
              <div class="font-bold text-slate-900 dark:text-white mt-0.5">{{ activeItem.pelabuhan }}</div>
            </div>
            <div>
              <div class="text-slate-400 font-medium">Negara Asal / Bendera</div>
              <div class="font-bold text-slate-900 dark:text-white mt-0.5">{{ activeItem.origin_country || 'N/A' }}</div>
            </div>
            <div>
              <div class="text-slate-400 font-medium">Gross Tonnage</div>
              <div class="font-bold text-slate-900 dark:text-white mt-0.5">{{ formatNumber(activeItem.gross_tonnage) }} GT</div>
            </div>
            <div>
              <div class="text-slate-400 font-medium">Jumlah ABK</div>
              <div class="font-bold text-slate-900 dark:text-white mt-0.5">{{ activeItem.jml_crew }} Orang</div>
            </div>
            <div>
              <div class="text-slate-400 font-medium">Kategori Pelayaran</div>
              <div class="font-bold text-slate-900 dark:text-white mt-0.5">{{ activeItem.kategori }}</div>
            </div>
            <div>
              <div class="text-slate-400 font-medium">Skor &amp; Risiko</div>
              <div class="font-bold mt-0.5">
                <span :class="['px-2 py-0.5 rounded text-[10px] font-bold', getRiskBadgeClass(activeItem.kresiko)]">
                  {{ activeItem.kresiko }} ({{ activeItem.skoring }})
                </span>
              </div>
            </div>
            <div>
              <div class="text-slate-400 font-medium">Estimasi PNBP</div>
              <div class="font-bold text-emerald-600 dark:text-emerald-400 mt-0.5 font-mono">{{ formatCurrency(activeItem.estimasi_pnbp) }}</div>
            </div>
            <div v-if="activeItem.negara_singgah_14_hari" class="col-span-2 sm:col-span-4 pt-2 border-t border-slate-200 dark:border-slate-700/60">
              <div class="text-slate-400 font-medium">Negara Singgah (14 Hari Terakhir)</div>
              <div class="font-bold text-slate-900 dark:text-white mt-0.5">{{ activeItem.negara_singgah_14_hari }}</div>
            </div>
          </div>

          <!-- Quarantine Questions List -->
          <div>
            <h4 class="font-bold text-sm text-slate-900 dark:text-white mb-3 flex items-center gap-2">
              <FileCheck class="w-4 h-4 text-[#00A99D]" />
              Lembar Deklarasi Kesehatan Kapal (19 Pertanyaan IHR Kemenkes)
            </h4>

            <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
              <div
                v-for="(qText, qNo) in quarantine_questions"
                :key="qNo"
                class="p-3 flex items-start justify-between gap-4 hover:bg-slate-50/50 dark:hover:bg-slate-800/20"
              >
                <div class="flex items-start gap-2.5">
                  <span class="w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">
                    {{ qNo }}
                  </span>
                  <span class="text-slate-800 dark:text-slate-200">{{ qText }}</span>
                </div>
                <div class="shrink-0 font-bold text-right max-w-[240px]">
                  <span :class="['inline-block px-2.5 py-0.5 rounded text-[11px]', getQuestionDisplay(activeItem, qNo).badgeClass]">
                    {{ getQuestionDisplay(activeItem, qNo).text }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Dokumen Upload & Verifikasi Info -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Dokumen Nahkoda -->
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
              <div class="font-bold text-slate-900 dark:text-white mb-2 flex items-center gap-1.5">
                <FileText class="w-4 h-4 text-[#00A99D]" />
                Dokumen Pernyataan Nahkoda
              </div>
              <div v-if="activeItem.surat_pernyataan_nahkoda" class="text-slate-600 dark:text-slate-400 flex items-center gap-2">
                <span>{{ activeItem.surat_pernyataan_nahkoda }}</span>
                <span class="text-[10px] text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">Tersedia</span>
              </div>
              <div v-else class="text-slate-400 italic">
                Tidak ada dokumen (Kapal Dalam Negeri)
              </div>
            </div>

            <!-- Catatan Verifikator -->
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
              <div class="font-bold text-slate-900 dark:text-white mb-2 flex items-center gap-1.5">
                <ShieldCheck class="w-4 h-4 text-emerald-600" />
                Catatan Verifikasi Karantina
              </div>
              <div class="text-slate-700 dark:text-slate-300">
                <p>{{ activeItem.catatan_verifikasi || 'Belum ada catatan verifikasi khusus.' }}</p>
                <div v-if="activeItem.verifier_name" class="mt-2 text-[10px] text-slate-400">
                  Diverifikasi oleh: <strong>{{ activeItem.verifier_name }}</strong> ({{ formatDate(activeItem.verified_at) }})
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 flex justify-between items-center">
          <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Keputusan Saat Ini:</span>
            <span :class="['px-2.5 py-0.5 rounded-full text-xs font-bold', getStatusBadgeClass(activeItem.hasil_status)]">
              {{ activeItem.hasil_status }}
            </span>
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="closeDetailModal"
              class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 transition"
            >
              Tutup
            </button>
            <button
              @click="openVerifyModal(activeItem); closeDetailModal()"
              class="px-4 py-2 text-xs font-semibold text-white bg-gradient-to-r from-[#005851] to-[#00A99D] rounded-xl hover:opacity-95 shadow transition"
            >
              Ubah Verifikasi
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL VERIFIKASI KEPUTUSAN KARANTINA -->
    <!-- ========================================== -->
    <div
      v-if="showVerifyModal && verifyTarget"
      class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
    >
      <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 bg-[#005851] text-white flex items-center justify-between">
          <div class="flex items-center gap-2">
            <ShieldCheck class="w-5 h-5 text-emerald-400" />
            <h3 class="font-bold text-sm">Verifikasi Kedatangan Kapal</h3>
          </div>
          <button @click="closeVerifyModal" class="text-white/80 hover:text-white">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitVerify" class="p-5 space-y-4 text-xs">
          <div>
            <div class="text-slate-500 font-medium mb-1">Nama Kapal:</div>
            <div class="font-bold text-sm text-slate-900 dark:text-white">{{ verifyTarget.nama_kapal }}</div>
            <div class="text-[11px] text-slate-400">{{ verifyTarget.pelabuhan }} • Asal: {{ verifyTarget.datang_dari }}</div>
          </div>

          <!-- Keputusan Status -->
          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-2">
              Keputusan Kekarantinaan Kesehatan: <span class="text-rose-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-2.5">
              <label
                :class="[
                  'flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition',
                  verifyDecision === 'SANDAR'
                    ? 'border-cyan-500 bg-cyan-50 dark:bg-cyan-950/40 text-cyan-900 dark:text-cyan-200 font-bold'
                    : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'
                ]"
              >
                <input type="radio" v-model="verifyDecision" value="SANDAR" class="text-cyan-600 focus:ring-cyan-500" />
                <span>SANDAR (Dermaga)</span>
              </label>

              <label
                :class="[
                  'flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition',
                  verifyDecision === 'ZONA KARANTINA'
                    ? 'border-rose-500 bg-rose-50 dark:bg-rose-950/40 text-rose-900 dark:text-rose-200 font-bold'
                    : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'
                ]"
              >
                <input type="radio" v-model="verifyDecision" value="ZONA KARANTINA" class="text-rose-600 focus:ring-rose-500" />
                <span>ZONA KARANTINA</span>
              </label>
            </div>
          </div>

          <!-- Catatan Verifikasi -->
          <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
              Catatan Rekomendasi / Instruksi Pengawasan:
            </label>
            <textarea
              v-model="verifyNotes"
              rows="3"
              placeholder="Contoh: Dokumen lengkap, seluruh kru sehat, izin sandar diberikan..."
              class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-2.5 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
            ></textarea>
          </div>

          <!-- Footer Buttons -->
          <div class="pt-2 flex justify-end gap-2">
            <button
              type="button"
              @click="closeVerifyModal"
              class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSubmittingVerify"
              class="px-5 py-2 rounded-xl bg-gradient-to-r from-[#005851] to-[#00A99D] font-bold text-white shadow hover:opacity-95 transition flex items-center gap-1.5"
            >
              <CheckCircle class="w-4 h-4" />
              {{ isSubmittingVerify ? 'Menyimpan...' : 'Simpan Verifikasi' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </HybridNavbar>
</template>

