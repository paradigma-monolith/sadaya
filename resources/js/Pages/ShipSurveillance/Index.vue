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
  red_zones_data: {
    type: Object,
    default: () => ({ countries: [], summary: { total: 0, red_count: 0, safe_count: 0 }, logs: [] }),
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

// Active Tab ('data' | 'laporan' | 'red-zones')
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

// ==============================================
// RED ZONE PDF IMPORT & COUNTRY MANAGEMENT STATE
// ==============================================
const redZonesState = ref({
  countries: props.red_zones_data?.countries || [],
  summary: props.red_zones_data?.summary || { total: 0, red_count: 0, safe_count: 0 },
  logs: props.red_zones_data?.logs || [],
});

const pdfFileInput = ref(null);
const selectedPdfFile = ref(null);
const pdfImportMode = ref('replace');
const isParsingPdf = ref(false);
const isApplyingRedZones = ref(false);
const pdfImportError = ref('');
const pdfImportSuccess = ref('');
const pdfPreview = ref(null);
const previewSelectedRed = ref(new Set());
const previewSelectedNew = ref(new Set());

const countrySearch = ref('');
const countryFilterStatus = ref('all'); // 'all' | 'red' | 'safe'
const togglingCountryId = ref(null);
const newCountryName = ref('');
const newCountryIsRed = ref(true);
const isAddingCountry = ref(false);
const isResettingCountries = ref(false);

const filteredRedZoneCountries = computed(() => {
  const q = countrySearch.value.trim().toLowerCase();
  return (redZonesState.value.countries || []).filter((c) => {
    if (countryFilterStatus.value === 'red' && !c.is_red_zone) return false;
    if (countryFilterStatus.value === 'safe' && c.is_red_zone) return false;
    if (q && !c.name.toLowerCase().includes(q) && !(c.source_label || '').toLowerCase().includes(q)) {
      return false;
    }
    return true;
  });
});

const handlePdfFileChange = (event) => {
  const file = event.target?.files?.[0] || null;
  selectedPdfFile.value = file;
  pdfImportError.value = '';
  pdfImportSuccess.value = '';
  pdfPreview.value = null;
};

const analyzePdfFile = async () => {
  if (!selectedPdfFile.value) {
    pdfImportError.value = 'Pilih berkas PDF terlebih dahulu.';
    return;
  }
  isParsingPdf.value = true;
  pdfImportError.value = '';
  pdfImportSuccess.value = '';
  try {
    const formData = new FormData();
    formData.append('file', selectedPdfFile.value);
    formData.append('mode', pdfImportMode.value);

    const res = await axios.post('/ship-surveillance/red-zones/preview-pdf', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    if (res.data?.success && res.data?.preview) {
      pdfPreview.value = res.data.preview;
      previewSelectedRed.value = new Set(res.data.preview.recommended_red_countries || []);
      previewSelectedNew.value = new Set();
    } else {
      pdfImportError.value = res.data?.message || 'Gagal menganalisis berkas PDF.';
    }
  } catch (err) {
    pdfImportError.value = err?.response?.data?.message || 'Terjadi kesalahan saat membaca berkas PDF.';
  } finally {
    isParsingPdf.value = false;
  }
};

const togglePreviewRed = (countryName) => {
  const next = new Set(previewSelectedRed.value);
  if (next.has(countryName)) {
    next.delete(countryName);
  } else {
    next.add(countryName);
  }
  previewSelectedRed.value = next;
};

const togglePreviewNewCountry = (territoryName) => {
  const next = new Set(previewSelectedNew.value);
  if (next.has(territoryName)) {
    next.delete(territoryName);
  } else {
    next.add(territoryName);
  }
  previewSelectedNew.value = next;
};

const toggleAllUnmatchedNew = (selectAll) => {
  if (!selectAll) {
    previewSelectedNew.value = new Set();
    return;
  }
  previewSelectedNew.value = new Set(pdfPreview.value?.unmatched_countries || []);
};

const excludeIndonesiaFromPreview = () => {
  const next = new Set(previewSelectedRed.value);
  next.delete('Indonesia');
  previewSelectedRed.value = next;
};

const applyPdfRedZones = async () => {
  if (!pdfPreview.value) return;
  isApplyingRedZones.value = true;
  pdfImportError.value = '';
  pdfImportSuccess.value = '';
  try {
    const sourceLabelMap = {};
    (pdfPreview.value.matched_records || []).forEach((rec) => {
      sourceLabelMap[rec.canonical_name] = rec.pdf_name;
    });

    const res = await axios.post('/ship-surveillance/red-zones/apply', {
      red_countries: Array.from(previewSelectedRed.value),
      new_countries: Array.from(previewSelectedNew.value),
      mode: pdfPreview.value.mode || pdfImportMode.value,
      source_filename: pdfPreview.value.filename,
      extracted_countries: pdfPreview.value.extracted_countries || [],
      source_label_map: sourceLabelMap,
    });

    if (res.data?.success) {
      redZonesState.value = res.data.data;
      pdfImportSuccess.value = res.data.message || 'Daftar negara Zona Merah berhasil diperbarui.';
      pdfPreview.value = null;
      selectedPdfFile.value = null;
      if (pdfFileInput.value) {
        pdfFileInput.value.value = '';
      }
    }
  } catch (err) {
    pdfImportError.value = err?.response?.data?.message || 'Gagal menyimpan pembaruan Zona Merah.';
  } finally {
    isApplyingRedZones.value = false;
  }
};

const toggleSingleCountryRedZone = async (country) => {
  if (!country?.id) return;
  togglingCountryId.value = country.id;
  pdfImportError.value = '';
  try {
    const res = await axios.patch(`/ship-surveillance/red-zones/${country.id}/toggle`, {
      is_red_zone: !country.is_red_zone,
    });
    if (res.data?.success && res.data?.data) {
      redZonesState.value = res.data.data;
      pdfImportSuccess.value = res.data.message;
    }
  } catch (err) {
    pdfImportError.value = err?.response?.data?.message || 'Gagal mengubah status negara.';
  } finally {
    togglingCountryId.value = null;
  }
};

const addManualCountry = async () => {
  const trimmed = newCountryName.value.trim();
  if (!trimmed) return;
  isAddingCountry.value = true;
  pdfImportError.value = '';
  try {
    const res = await axios.post('/ship-surveillance/red-zones/country', {
      name: trimmed,
      is_red_zone: newCountryIsRed.value,
    });
    if (res.data?.success && res.data?.data) {
      redZonesState.value = res.data.data;
      pdfImportSuccess.value = res.data.message;
      newCountryName.value = '';
    }
  } catch (err) {
    pdfImportError.value = err?.response?.data?.message || 'Gagal menambahkan negara baru.';
  } finally {
    isAddingCountry.value = false;
  }
};

const resetDefaultRedZones = async () => {
  if (!confirm('Kembalikan status Zona Merah untuk 195 negara ke data referensi awal?')) return;
  isResettingCountries.value = true;
  pdfImportError.value = '';
  try {
    const res = await axios.post('/ship-surveillance/red-zones/reset');
    if (res.data?.success && res.data?.data) {
      redZonesState.value = res.data.data;
      pdfImportSuccess.value = res.data.message;
    }
  } catch (err) {
    pdfImportError.value = err?.response?.data?.message || 'Gagal mereset daftar negara.';
  } finally {
    isResettingCountries.value = false;
  }
};
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
            <button
              type="button"
              @click="activeTab = 'red-zones'"
              :class="[
                'px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5',
                activeTab === 'red-zones'
                  ? 'bg-white dark:bg-slate-900 text-rose-600 dark:text-rose-400 shadow-sm'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
              ]"
            >
              <UploadCloud class="w-3.5 h-3.5" />
              <span>Zona Merah (PDF)</span>
              <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/15 text-rose-600 dark:text-rose-400">
                {{ redZonesState.summary?.red_count || 0 }}
              </span>
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

      <!-- ==================================================== -->
      <!-- TAB 3: UPDATE NEGARA ZONA MERAH DARI PDF KEMENKES    -->
      <!-- ==================================================== -->
      <div v-show="activeTab === 'red-zones'" class="space-y-6">

        <!-- Alerts -->
        <div v-if="pdfImportError" class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs flex items-center justify-between gap-2">
          <div class="flex items-center gap-2">
            <AlertTriangle class="w-4 h-4 shrink-0" />
            <span>{{ pdfImportError }}</span>
          </div>
          <button type="button" @click="pdfImportError = ''" class="hover:opacity-75"><X class="w-4 h-4" /></button>
        </div>

        <div v-if="pdfImportSuccess" class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs flex items-center justify-between gap-2">
          <div class="flex items-center gap-2">
            <CheckCircle2 class="w-4 h-4 shrink-0" />
            <span>{{ pdfImportSuccess }}</span>
          </div>
          <button type="button" @click="pdfImportSuccess = ''" class="hover:opacity-75"><X class="w-4 h-4" /></button>
        </div>

        <!-- Ringkasan & Aksi Cepat -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total Negara Terdaftar</div>
            <div class="mt-1.5 text-2xl font-bold text-slate-900 dark:text-white">{{ redZonesState.summary?.total || 0 }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Tersedia pada form MDH Luar Negeri</div>
          </div>
          <div class="bg-white dark:bg-slate-900 border border-rose-200 dark:border-rose-900/50 rounded-2xl p-4 shadow-sm">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-rose-600 dark:text-rose-400">Negara Zona Merah (+20)</div>
            <div class="mt-1.5 text-2xl font-bold text-rose-600 dark:text-rose-400">{{ redZonesState.summary?.red_count || 0 }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Otomatis +20 skor pada Q1 Internasional</div>
          </div>
          <div class="bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-900/50 rounded-2xl p-4 shadow-sm">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Negara Non-Red Zone (0)</div>
            <div class="mt-1.5 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ redZonesState.summary?.safe_count || 0 }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Tidak terjangkit / skor 0 pada Q1</div>
          </div>
          <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
            <div>
              <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Pembaruan PDF Terakhir</div>
              <div v-if="redZonesState.logs?.length" class="mt-1 text-xs font-semibold text-slate-800 dark:text-slate-200 line-clamp-1" :title="redZonesState.logs[0].filename">
                {{ redZonesState.logs[0].filename }}
              </div>
              <div v-else class="mt-1 text-xs text-slate-400">Belum pernah import PDF (data default)</div>
            </div>
            <div class="mt-2 flex items-center justify-between gap-2">
              <span v-if="redZonesState.logs?.length" class="text-[10px] text-slate-400">{{ formatDate(redZonesState.logs[0].created_at) }}</span>
              <button
                type="button"
                @click="resetDefaultRedZones"
                :disabled="isResettingCountries"
                class="text-[11px] font-semibold text-slate-500 hover:text-rose-600 transition inline-flex items-center gap-1 ml-auto cursor-pointer"
              >
                <RefreshCw class="w-3 h-3" :class="{ 'animate-spin': isResettingCountries }" />
                Reset Default
              </button>
            </div>
          </div>
        </div>

        <!-- Panel Upload & Preview PDF Kemenkes -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center shrink-0">
                <UploadCloud class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Import & Update Zona Merah dari PDF Kemenkes (Kolom NEGARA)</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                  Unggah dokumen PDF <em>Daftar Negara/Wilayah Terjangkit Penyakit Infeksi Emerging</em>. Sistem akan membaca kolom <strong>NEGARA</strong> secara otomatis di semua halaman.
                </p>
              </div>
            </div>
            <a
              href="/ship-surveillance/assessment/international"
              target="_blank"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition shrink-0"
            >
              <ExternalLink class="w-3.5 h-3.5 text-[#00A99D]" />
              Cek Form Luar Negeri ↗
            </a>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
            <div class="lg:col-span-5">
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Pilih File PDF Daftar Negara Terjangkit (.pdf, maks. 10 MB)
              </label>
              <input
                ref="pdfFileInput"
                type="file"
                accept="application/pdf,.pdf"
                @change="handlePdfFileChange"
                class="w-full text-xs text-slate-600 dark:text-slate-300 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#005851] file:text-white hover:file:opacity-90 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 p-1.5 cursor-pointer"
              />
            </div>

            <div class="lg:col-span-5">
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mode Pembaruan</label>
              <div class="grid grid-cols-2 gap-2">
                <label
                  :class="[
                    'flex items-center gap-2 p-2.5 rounded-xl border text-xs cursor-pointer transition',
                    pdfImportMode === 'replace'
                      ? 'border-[#00A99D] bg-teal-50 dark:bg-teal-950/30 text-[#005851] dark:text-[#00A99D] font-bold'
                      : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300'
                  ]"
                >
                  <input type="radio" v-model="pdfImportMode" value="replace" class="text-[#00A99D]" />
                  <span>Ganti Total Sesuai PDF</span>
                </label>
                <label
                  :class="[
                    'flex items-center gap-2 p-2.5 rounded-xl border text-xs cursor-pointer transition',
                    pdfImportMode === 'merge'
                      ? 'border-[#00A99D] bg-teal-50 dark:bg-teal-950/30 text-[#005851] dark:text-[#00A99D] font-bold'
                      : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300'
                  ]"
                >
                  <input type="radio" v-model="pdfImportMode" value="merge" class="text-[#00A99D]" />
                  <span>Tambahkan Saja (Merge)</span>
                </label>
              </div>
            </div>

            <div class="lg:col-span-2">
              <button
                type="button"
                @click="analyzePdfFile"
                :disabled="!selectedPdfFile || isParsingPdf"
                class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-[#005851] to-[#00A99D] text-white text-xs font-bold shadow hover:opacity-95 disabled:opacity-50 transition inline-flex items-center justify-center gap-1.5 cursor-pointer"
              >
                <RefreshCw v-if="isParsingPdf" class="w-3.5 h-3.5 animate-spin" />
                <FileText v-else class="w-3.5 h-3.5" />
                {{ isParsingPdf ? 'Menganalisis...' : 'Analisis PDF' }}
              </button>
            </div>
          </div>

          <!-- Interactive Preview Results -->
          <div v-if="pdfPreview" class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-[#00A99D]" />
                  Pratinjau Hasil Pembacaan PDF ({{ pdfPreview.pages_parsed }} halaman)
                </h4>
                <p class="text-[11px] text-slate-500 mt-0.5">
                  Periksa daftar negara sebelum menerapkan perubahan ke aplikasi TANGKAP &amp; form MDH Luar Negeri.
                </p>
              </div>

              <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold">
                <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                  Diekstrak: {{ pdfPreview.total_extracted }}
                </span>
                <span class="px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-700 dark:text-rose-300">
                  Baru Merah: +{{ pdfPreview.newly_flagged?.length || 0 }}
                </span>
                <span class="px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-700 dark:text-amber-300">
                  Tetap Merah: {{ pdfPreview.already_red?.length || 0 }}
                </span>
                <span v-if="pdfPreview.mode === 'replace'" class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-300">
                  Keluar Zona Merah: {{ pdfPreview.to_unflag?.length || 0 }}
                </span>
                <span v-if="pdfPreview.unmatched_countries?.length" class="px-2.5 py-1 rounded-full bg-cyan-500/10 text-cyan-700 dark:text-cyan-300">
                  Entitas Baru: {{ pdfPreview.unmatched_countries.length }}
                </span>
              </div>
            </div>

            <!-- Notice if Indonesia was found in Kemenkes PDF -->
            <div
              v-if="pdfPreview.indonesia_in_pdf && previewSelectedRed.has('Indonesia')"
              class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-800 dark:text-amber-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2"
            >
              <div class="flex items-start gap-2">
                <Info class="w-4 h-4 shrink-0 mt-0.5 text-amber-600" />
                <span>
                  Kolom <strong>NEGARA</strong> pada PDF mencantumkan <strong>Indonesia</strong>. Jika form Kedatangan Luar Negeri harus tetap mengecualikan Indonesia dari status Zona Merah (+20 skor), klik tombol di samping.
                </span>
              </div>
              <button
                type="button"
                @click="excludeIndonesiaFromPreview"
                class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-amber-400 text-amber-700 dark:text-amber-300 font-bold text-[11px] hover:bg-amber-50 shrink-0 cursor-pointer"
              >
                Kecualikan Indonesia (Tetap Aman)
              </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Baru Ditandai Merah -->
              <div class="rounded-xl border border-rose-200 dark:border-rose-900/50 bg-rose-50/40 dark:bg-rose-950/10 p-3.5 space-y-2">
                <div class="text-xs font-bold text-rose-700 dark:text-rose-300 flex items-center justify-between">
                  <span>Baru Ditandai Zona Merah ({{ pdfPreview.newly_flagged?.length || 0 }})</span>
                </div>
                <div v-if="!pdfPreview.newly_flagged?.length" class="text-[11px] text-slate-400 italic">
                  Tidak ada penambahan negara zona merah baru.
                </div>
                <div v-else class="max-h-52 overflow-y-auto space-y-1 pr-1">
                  <label
                    v-for="item in pdfPreview.newly_flagged"
                    :key="item.canonical_name"
                    class="flex items-center justify-between gap-2 p-1.5 rounded-lg bg-white/80 dark:bg-slate-900/60 border border-rose-100 dark:border-rose-900/40 text-xs cursor-pointer"
                  >
                    <span class="flex items-center gap-2 min-w-0">
                      <input
                        type="checkbox"
                        :checked="previewSelectedRed.has(item.canonical_name)"
                        @change="togglePreviewRed(item.canonical_name)"
                        class="rounded text-rose-600"
                      />
                      <span class="font-semibold text-slate-800 dark:text-slate-100 truncate">{{ item.canonical_name }}</span>
                    </span>
                    <span v-if="item.pdf_name !== item.canonical_name" class="text-[10px] text-slate-400 shrink-0">
                      PDF: {{ item.pdf_name }}
                    </span>
                  </label>
                </div>
              </div>

              <!-- Keluar Zona Merah / Menjadi Aman -->
              <div class="rounded-xl border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50/40 dark:bg-emerald-950/10 p-3.5 space-y-2">
                <div class="text-xs font-bold text-emerald-700 dark:text-emerald-300 flex items-center justify-between">
                  <span>
                    {{ pdfPreview.mode === 'replace' ? 'Tidak Ada di PDF → Menjadi Zona Aman' : 'Zona Merah Eksisting Dipertahankan' }}
                    ({{ pdfPreview.to_unflag?.length || 0 }})
                  </span>
                </div>
                <div v-if="!pdfPreview.to_unflag?.length" class="text-[11px] text-slate-400 italic">
                  Semua negara zona merah sebelumnya tercakup di PDF ini.
                </div>
                <div v-else class="max-h-52 overflow-y-auto space-y-1 pr-1">
                  <label
                    v-for="item in pdfPreview.to_unflag"
                    :key="item.canonical_name"
                    class="flex items-center justify-between gap-2 p-1.5 rounded-lg bg-white/80 dark:bg-slate-900/60 border border-emerald-100 dark:border-emerald-900/40 text-xs cursor-pointer"
                  >
                    <span class="flex items-center gap-2 min-w-0">
                      <input
                        type="checkbox"
                        :checked="previewSelectedRed.has(item.canonical_name)"
                        @change="togglePreviewRed(item.canonical_name)"
                        class="rounded text-rose-600"
                      />
                      <span class="font-semibold text-slate-800 dark:text-slate-100 truncate">{{ item.canonical_name }}</span>
                    </span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 shrink-0">
                      {{ previewSelectedRed.has(item.canonical_name) ? 'Tetap Merah' : 'Jadi Aman' }}
                    </span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Unmatched territories / new entities -->
            <div v-if="pdfPreview.unmatched_countries?.length" class="rounded-xl border border-cyan-200 dark:border-cyan-900/50 bg-cyan-50/40 dark:bg-cyan-950/10 p-3.5 space-y-2">
              <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-bold text-cyan-800 dark:text-cyan-300">
                  Wilayah / Entitas Baru di PDF Belum Terdaftar ({{ pdfPreview.unmatched_countries.length }})
                </span>
                <div class="flex items-center gap-2 text-[11px]">
                  <button type="button" @click="toggleAllUnmatchedNew(true)" class="text-cyan-700 dark:text-cyan-300 font-semibold hover:underline cursor-pointer">Pilih Semua</button>
                  <span class="text-slate-300">|</span>
                  <button type="button" @click="toggleAllUnmatchedNew(false)" class="text-slate-500 hover:underline cursor-pointer">Kosongkan</button>
                </div>
              </div>
              <div class="flex flex-wrap gap-2">
                <label
                  v-for="terr in pdfPreview.unmatched_countries"
                  :key="terr"
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-cyan-200 dark:border-cyan-800 text-xs cursor-pointer"
                >
                  <input
                    type="checkbox"
                    :checked="previewSelectedNew.has(terr)"
                    @change="togglePreviewNewCountry(terr)"
                    class="rounded text-cyan-600"
                  />
                  <span>{{ terr }}</span>
                </label>
              </div>
            </div>

            <!-- Action Footer -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
              <div class="text-xs text-slate-600 dark:text-slate-300">
                Total akan diset Zona Merah:
                <strong class="text-rose-600">{{ previewSelectedRed.size + previewSelectedNew.size }}</strong> negara/wilayah
              </div>
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  @click="pdfPreview = null"
                  class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="button"
                  @click="applyPdfRedZones"
                  :disabled="isApplyingRedZones"
                  class="px-5 py-2 rounded-xl bg-gradient-to-r from-rose-600 to-rose-500 text-white text-xs font-bold shadow hover:opacity-95 disabled:opacity-50 inline-flex items-center gap-1.5 cursor-pointer"
                >
                  <Save class="w-3.5 h-3.5" />
                  {{ isApplyingRedZones ? 'Menyimpan...' : 'Terapkan Update Zona Merah' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Master List Negara & Riwayat Import -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <!-- Daftar Negara Saat Ini -->
          <div class="lg:col-span-8 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
              <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                  <Globe class="w-4 h-4 text-[#00A99D]" />
                  Daftar Negara &amp; Status Zona Merah Aktif
                </h3>
                <p class="text-[11px] text-slate-500">Klik status di sebelah kanan nama negara untuk mengubah Zona Merah vs Zona Aman secara langsung.</p>
              </div>
              <div class="flex items-center gap-2">
                <div class="relative">
                  <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" />
                  <input
                    v-model="countrySearch"
                    type="text"
                    placeholder="Cari negara..."
                    class="pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-[#00A99D] focus:outline-none w-44"
                  />
                </div>
                <select
                  v-model="countryFilterStatus"
                  class="text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-2.5 py-1.5"
                >
                  <option value="all">Semua ({{ redZonesState.summary?.total || 0 }})</option>
                  <option value="red">Zona Merah ({{ redZonesState.summary?.red_count || 0 }})</option>
                  <option value="safe">Zona Aman ({{ redZonesState.summary?.safe_count || 0 }})</option>
                </select>
              </div>
            </div>

            <div class="max-h-[460px] overflow-y-auto border border-slate-100 dark:border-slate-800 rounded-xl divide-y divide-slate-100 dark:divide-slate-800">
              <div
                v-for="country in filteredRedZoneCountries"
                :key="country.id || country.name"
                class="flex items-center justify-between gap-3 px-3.5 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-xs"
              >
                <div class="min-w-0">
                  <div class="font-semibold text-slate-800 dark:text-slate-100 truncate">{{ country.name }}</div>
                  <div v-if="country.source_label && country.source_label !== country.name" class="text-[10px] text-slate-400 truncate">
                    Sumber: {{ country.source_label }}
                  </div>
                </div>
                <button
                  type="button"
                  :disabled="!country.id || togglingCountryId === country.id"
                  @click="toggleSingleCountryRedZone(country)"
                  :class="[
                    'px-2.5 py-1 rounded-full text-[11px] font-bold transition shrink-0 cursor-pointer disabled:opacity-50',
                    country.is_red_zone
                      ? 'bg-rose-500/10 text-rose-700 dark:text-rose-300 border border-rose-500/30 hover:bg-rose-500/20'
                      : 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/20'
                  ]"
                >
                  {{ togglingCountryId === country.id ? '...' : (country.is_red_zone ? 'ZONA MERAH (+20)' : 'AMAN (0)') }}
                </button>
              </div>
              <div v-if="!filteredRedZoneCountries.length" class="p-6 text-center text-xs text-slate-400">
                Tidak ada negara yang cocok dengan filter pencarian.
              </div>
            </div>
          </div>

          <!-- Tambah Manual & Riwayat Import -->
          <div class="lg:col-span-4 space-y-5">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-3">
              <h4 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                <Flag class="w-4 h-4 text-[#00A99D]" />
                Tambah Negara / Wilayah Manual
              </h4>
              <form @submit.prevent="addManualCountry" class="space-y-2.5 text-xs">
                <input
                  v-model="newCountryName"
                  type="text"
                  required
                  placeholder="Contoh: Kosovo atau French Guiana"
                  class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                />
                <label class="flex items-center gap-2 text-slate-600 dark:text-slate-300 cursor-pointer">
                  <input type="checkbox" v-model="newCountryIsRed" class="rounded text-rose-600" />
                  <span>Tandai langsung sebagai Zona Merah (+20)</span>
                </label>
                <button
                  type="submit"
                  :disabled="isAddingCountry || !newCountryName.trim()"
                  class="w-full py-2 rounded-xl bg-[#005851] hover:opacity-95 text-white font-bold transition disabled:opacity-50 cursor-pointer"
                >
                  {{ isAddingCountry ? 'Menyimpan...' : '+ Tambah Negara' }}
                </button>
              </form>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-3">
              <h4 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                <Clock class="w-4 h-4 text-[#00A99D]" />
                Riwayat Import PDF Terbaru
              </h4>
              <div v-if="!redZonesState.logs?.length" class="text-xs text-slate-400 italic py-2">
                Belum ada riwayat pembaruan PDF.
              </div>
              <div v-else class="space-y-2.5 max-h-80 overflow-y-auto pr-1">
                <div
                  v-for="log in redZonesState.logs"
                  :key="log.id"
                  class="p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 text-xs space-y-1"
                >
                  <div class="font-bold text-slate-800 dark:text-slate-100 truncate" :title="log.filename">
                    {{ log.filename }}
                  </div>
                  <div class="flex flex-wrap gap-1.5 text-[10px] font-semibold">
                    <span class="px-1.5 py-0.5 rounded bg-slate-200/70 dark:bg-slate-700 text-slate-700 dark:text-slate-200 uppercase">
                      {{ log.mode === 'replace' ? 'Ganti Total' : 'Merge' }}
                    </span>
                    <span class="px-1.5 py-0.5 rounded bg-rose-500/10 text-rose-600">+{{ log.newly_flagged_count }} Merah</span>
                    <span class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-600">-{{ log.unflagged_count }} Aman</span>
                    <span class="px-1.5 py-0.5 rounded bg-cyan-500/10 text-cyan-700">{{ log.matched_count }} Cocok</span>
                  </div>
                  <div class="text-[10px] text-slate-400">
                    {{ log.importer_name }} • {{ formatDate(log.created_at) }}
                  </div>
                </div>
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

