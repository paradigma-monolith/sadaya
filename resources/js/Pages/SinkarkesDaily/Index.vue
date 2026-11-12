<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import { toast } from 'vue-sonner';
import {
  Ship,
  Plane,
  Calendar,
  Building2,
  Plus,
  Edit3,
  Trash2,
  Upload,
  Download,
  FileSpreadsheet,
  CheckCircle2,
  AlertCircle,
  RefreshCw,
  Search,
  X,
  Save,
  Layers,
  Filter,
  Calculator,
  ChevronDown,
  ChevronUp,
  Info,
  Sparkles,
  ExternalLink,
} from 'lucide-vue-next';

const props = defineProps({
  active_tab: { type: String, default: 'kapal' },
  wilker: { type: String, required: true },
  year: { type: Number, required: true },
  month: { type: Number, required: true },
  wilkerOptions: { type: Array, default: () => [] },
  kapalRecords: { type: Array, default: () => [] },
  pesawatRecords: { type: Array, default: () => [] },
  accumulation: { type: Object, required: true },
  dailySchema: { type: Object, required: true },
});

const MONTH_OPTIONS = [
  { id: 1, label: 'Januari' },
  { id: 2, label: 'Februari' },
  { id: 3, label: 'Maret' },
  { id: 4, label: 'April' },
  { id: 5, label: 'Mei' },
  { id: 6, label: 'Juni' },
  { id: 7, label: 'Juli' },
  { id: 8, label: 'Agustus' },
  { id: 9, label: 'September' },
  { id: 10, label: 'Oktober' },
  { id: 11, label: 'November' },
  { id: 12, label: 'Desember' },
];

const YEAR_OPTIONS = [2024, 2025, 2026, 2027, 2028, 2029, 2030];

const activeTab = ref(props.active_tab || 'kapal');
const selectedWilker = ref(props.wilker);
const selectedYear = ref(props.year);
const selectedMonth = ref(props.month);
const searchQuery = ref('');

watch(() => props.active_tab, (v) => {
  if (v) activeTab.value = v;
});
watch(() => props.wilker, (v) => { selectedWilker.value = v; });
watch(() => props.year, (v) => { selectedYear.value = v; });
watch(() => props.month, (v) => { selectedMonth.value = v; });

const activeMonthLabel = computed(() => {
  return MONTH_OPTIONS.find(m => m.id === Number(selectedMonth.value))?.label || selectedMonth.value;
});

const navigateFilters = (overrideTab = null) => {
  const tabToUse = overrideTab || activeTab.value;
  router.get('/sinkarkes-daily', {
    tab: tabToUse,
    wilker: selectedWilker.value,
    year: selectedYear.value,
    month: selectedMonth.value,
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

const switchTab = (tab) => {
  activeTab.value = tab;
  if (tab === 'kapal' || tab === 'pesawat') {
    showForm.value = false;
  }
  const url = new URL(window.location.href);
  url.searchParams.set('tab', tab);
  window.history.replaceState({}, '', url.toString());
};

// ---------------- KPI / Records ----------------
const kapalTotalArmada = computed(() =>
  props.kapalRecords.reduce((sum, r) => sum + (Number(r.total_armada) || 0), 0)
);
const kapalTotalOrang = computed(() =>
  props.kapalRecords.reduce((sum, r) => sum + (Number(r.total_kru) || 0) + (Number(r.total_penumpang) || 0), 0)
);
const pesawatTotalArmada = computed(() =>
  props.pesawatRecords.reduce((sum, r) => sum + (Number(r.total_armada) || 0), 0)
);
const pesawatTotalOrang = computed(() =>
  props.pesawatRecords.reduce((sum, r) => sum + (Number(r.total_kru) || 0) + (Number(r.total_penumpang) || 0), 0)
);

const currentTypeRecords = computed(() => {
  const list = activeTab.value === 'pesawat' ? props.pesawatRecords : props.kapalRecords;
  const q = searchQuery.value.trim().toLowerCase();
  if (!q) return list;
  return list.filter(r =>
    (r.tanggal || '').toLowerCase().includes(q) ||
    (r.wilker || '').toLowerCase().includes(q) ||
    (r.notes || '').toLowerCase().includes(q) ||
    (r.updater_name || '').toLowerCase().includes(q)
  );
});

// ---------------- Daily Input Form ----------------
const showForm = ref(false);
const isSaving = ref(false);
const editingId = ref(null);
const formTransportType = ref('kapal');
const formTanggal = ref('');
const formWilker = ref(props.wilker);
const formNotes = ref('');
const formMetrics = ref({});
const showOnlyRedFields = ref(false);
const formFieldSearch = ref('');
const collapsedGroups = ref({});

const buildDefaultDate = () => {
  const now = new Date();
  if (now.getFullYear() === Number(selectedYear.value) && (now.getMonth() + 1) === Number(selectedMonth.value)) {
    return now.toISOString().slice(0, 10);
  }
  const mm = String(selectedMonth.value).padStart(2, '0');
  return `${selectedYear.value}-${mm}-01`;
};

const openCreateForm = (type = null) => {
  const targetType = type || (activeTab.value === 'pesawat' ? 'pesawat' : 'kapal');
  editingId.value = null;
  formTransportType.value = targetType;
  formTanggal.value = buildDefaultDate();
  formWilker.value = selectedWilker.value;
  formNotes.value = '';
  formMetrics.value = {};
  formFieldSearch.value = '';
  collapsedGroups.value = {};
  showForm.value = true;
};

const openEditForm = (record) => {
  editingId.value = record.id;
  formTransportType.value = record.transport_type;
  formTanggal.value = record.tanggal;
  formWilker.value = record.wilker;
  formNotes.value = record.notes || '';
  formMetrics.value = { ...(record.metrics || {}) };
  formFieldSearch.value = '';
  collapsedGroups.value = {};
  showForm.value = true;
};

const closeForm = () => {
  if (isSaving.value) return;
  showForm.value = false;
  editingId.value = null;
};

const activeFormSchema = computed(() => {
  return props.dailySchema?.[formTransportType.value] || { groups: [], fields: [] };
});

const groupedFormSections = computed(() => {
  const groups = activeFormSchema.value.groups || [];
  const fields = activeFormSchema.value.fields || [];
  const q = formFieldSearch.value.trim().toLowerCase();

  return groups
    .map((group) => {
      const groupFields = fields.filter((f) => {
        if (f.group !== group.id) return false;
        if (showOnlyRedFields.value && !f.is_red) return false;
        if (q) {
          const matchLabel = f.label?.toLowerCase().includes(q);
          const matchCol = f.excel_col?.toLowerCase().includes(q);
          const matchCode = (f.sinkarkes_code || '').toLowerCase().includes(q);
          const matchMark = (f.sinkarkes_excel_mark || '').toLowerCase().includes(q);
          return matchLabel || matchCol || matchCode || matchMark;
        }
        return true;
      });
      return {
        ...group,
        fields: groupFields,
        redCount: groupFields.filter(f => f.is_red).length,
      };
    })
    .filter(g => g.fields.length > 0);
});

const toggleGroup = (groupId) => {
  collapsedGroups.value[groupId] = !collapsedGroups.value[groupId];
};

const sanitizeMetricValue = (key, raw) => {
  if (raw === '' || raw === null || raw === undefined) {
    delete formMetrics.value[key];
    return;
  }
  const parsed = parseInt(raw, 10);
  if (isNaN(parsed) || parsed <= 0) {
    delete formMetrics.value[key];
  } else {
    formMetrics.value[key] = parsed;
  }
};

const filledMetricCount = computed(() => Object.keys(formMetrics.value).length);

const formRedImpactPreview = computed(() => {
  const fields = (activeFormSchema.value.fields || []).filter(f => f.is_red);
  const byTarget = new Map();
  const pairs = new Map();

  fields.forEach((f) => {
    const val = Number(formMetrics.value[f.key] || 0);
    if (f.dedupe_pair) {
      const pKey = `${f.dedupe_pair}|${f.sinkarkes_target_row}`;
      const existing = pairs.get(pKey) || { field: f, values: [] };
      existing.values.push(val);
      pairs.set(pKey, existing);
    } else {
      const cur = byTarget.get(f.sinkarkes_target_row) || { field: f, total: 0 };
      cur.total += val;
      byTarget.set(f.sinkarkes_target_row, cur);
    }
  });

  pairs.forEach(({ field, values }) => {
    const maxVal = Math.max(...values, 0);
    const cur = byTarget.get(field.sinkarkes_target_row) || { field, total: 0 };
    cur.total += maxVal;
    byTarget.set(field.sinkarkes_target_row, cur);
  });

  return Array.from(byTarget.values()).filter(item => item.total > 0);
});

const saveDailyRecord = () => {
  if (isSaving.value) return;
  if (!formTanggal.value || !formWilker.value) {
    toast.error('Tanggal dan Wilayah Kerja wajib diisi');
    return;
  }

  isSaving.value = true;
  router.post('/sinkarkes-daily', {
    id: editingId.value,
    tanggal: formTanggal.value,
    wilker: formWilker.value,
    transport_type: formTransportType.value,
    metrics: formMetrics.value,
    notes: formNotes.value || null,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      isSaving.value = false;
      showForm.value = false;
      editingId.value = null;
      toast.success('Data Harian Disimpan & Diakumulasikan', {
        description: `Otomatis memperbarui Formulir SINKARKES ${formWilker.value}`,
      });
    },
    onError: (errors) => {
      isSaving.value = false;
      toast.error('Gagal menyimpan data harian', {
        description: Object.values(errors).flat().join(', ') || 'Periksa isian formulir.',
      });
    },
  });
};

const deleteRecord = (record) => {
  if (!confirm(`Hapus entri harian ${record.transport_type.toUpperCase()} tanggal ${record.tanggal} (${record.wilker})? Nilai di SINKARKES akan otomatis dikurangi.`)) {
    return;
  }
  router.delete(`/sinkarkes-daily/${record.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Entri harian dihapus', {
        description: 'Akumulasi pada Formulir SINKARKES ikut diperbarui.',
      });
    },
    onError: () => {
      toast.error('Gagal menghapus data harian.');
    },
  });
};

// ---------------- Manual Resync ----------------
const isSyncing = ref(false);
const triggerSync = () => {
  if (isSyncing.value) return;
  isSyncing.value = true;
  router.post('/sinkarkes-daily/sync', {
    wilker: selectedWilker.value,
    year: selectedYear.value,
    month: selectedMonth.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      isSyncing.value = false;
      toast.success('Sinkronisasi SINKARKES Selesai', {
        description: `${selectedWilker.value} • ${activeMonthLabel.value} ${selectedYear.value}`,
      });
    },
    onError: () => {
      isSyncing.value = false;
      toast.error('Gagal menyinkronkan ke SINKARKES.');
    },
  });
};

// ---------------- Excel Export & Import ----------------
const exportDataUrl = computed(() =>
  `/sinkarkes-daily/export?wilker=${encodeURIComponent(selectedWilker.value)}&year=${selectedYear.value}&month=${selectedMonth.value}`
);
const exportTemplateUrl = computed(() =>
  `/sinkarkes-daily/export?wilker=${encodeURIComponent(selectedWilker.value)}&year=${selectedYear.value}&month=${selectedMonth.value}&template=1`
);
const sinkarkesFormUrl = computed(() =>
  `/sinkarkes?wilker=${encodeURIComponent(selectedWilker.value)}&year=${selectedYear.value}&month=${selectedMonth.value}`
);

const showImportModal = ref(false);
const importFile = ref(null);
const importWilker = ref(props.wilker);
const isImporting = ref(false);
const importResult = ref(null);
const fileInputRef = ref(null);

const openImportModal = () => {
  importWilker.value = selectedWilker.value;
  importFile.value = null;
  importResult.value = null;
  showImportModal.value = true;
};

const closeImportModal = () => {
  if (isImporting.value) return;
  showImportModal.value = false;
};

const onFileChange = (e) => {
  const f = e.target.files?.[0];
  if (f) {
    importFile.value = f;
    importResult.value = null;
  }
};

const submitImport = async () => {
  if (!importFile.value) {
    toast.error('Pilih file Excel (.xlsx/.xls) terlebih dahulu');
    return;
  }

  isImporting.value = true;
  importResult.value = null;

  const formData = new FormData();
  formData.append('file', importFile.value);
  formData.append('wilker', importWilker.value);

  try {
    const res = await fetch('/sinkarkes-daily/import', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: formData,
    });
    const data = await res.json();
    if (res.ok && data.success) {
      importResult.value = data.result;
      toast.success(data.message || 'Import Excel harian berhasil!');
      setTimeout(() => {
        router.reload({ preserveScroll: true });
      }, 900);
    } else {
      toast.error('Import gagal', { description: data.message || 'Format Excel tidak sesuai.' });
    }
  } catch (err) {
    toast.error('Terjadi kesalahan saat upload Excel', { description: err.message });
  } finally {
    isImporting.value = false;
  }
};

// ---------------- Table Helpers ----------------
const sumKeys = (metrics, keys) =>
  keys.reduce((acc, k) => acc + (Number(metrics?.[k] || 0)), 0);

const formatDateId = (isoDate) => {
  if (!isoDate) return '-';
  try {
    const [y, m, d] = isoDate.split('-').map(Number);
    const monthsShort = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return `${String(d).padStart(2, '0')} ${monthsShort[(m || 1) - 1]} ${y}`;
  } catch (e) {
    return isoDate;
  }
};
</script>

<template>
  <Head :title="`Rekap Harian SINKARKES - ${selectedWilker}`" />

  <HybridNavbar mode="inertia" currentRoute="sinkarkes-daily">
    <main class="clinical-main font-sans">

      <!-- Institutional Top Banner -->
      <div class="clinical-header flex flex-col xl:flex-row xl:items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0 flex-1">
          <div class="w-9 h-9 rounded-lg bg-[#002B27] border border-[#00A99D]/40 text-[#99CC33] flex items-center justify-center shrink-0">
            <Sparkles class="w-4 h-4" />
          </div>
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <h1 class="text-sm sm:text-[15px] font-bold text-white tracking-tight">
                Rekap Data Harian Kapal &amp; Pesawat
              </h1>
              <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-rose-500/20 text-rose-200 border border-rose-400/35">
                Auto-Akumulasi Poin Merah
              </span>
            </div>
            <p class="text-[11px] text-slate-200/85 truncate mt-0.5">
              Inputan harian kapal &amp; pesawat otomatis terakumulasi per Wilayah Kerja &amp; Bulan ke Formulir Laporan SINKARKES.
            </p>
          </div>
        </div>

        <!-- Period & Action Bar -->
        <div class="flex flex-wrap items-center gap-1.5 shrink-0">
          <!-- Wilker -->
          <div class="flex items-center gap-1.5 bg-[#002D29] border border-white/15 rounded-lg px-2.5 py-1 text-white">
            <Building2 class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
            <select
              v-model="selectedWilker"
              @change="navigateFilters()"
              class="bg-transparent text-xs font-semibold text-white border-none focus:ring-0 cursor-pointer outline-none pr-1 py-0.5"
            >
              <option v-for="w in wilkerOptions" :key="w" :value="w" class="text-slate-900">{{ w }}</option>
            </select>
          </div>

          <!-- Bulan -->
          <div class="flex items-center gap-1.5 bg-[#002D29] border border-white/15 rounded-lg px-2.5 py-1 text-white">
            <Calendar class="w-3.5 h-3.5 text-[#99DFD6] shrink-0" />
            <select
              v-model.number="selectedMonth"
              @change="navigateFilters()"
              class="bg-transparent text-xs font-semibold text-white border-none focus:ring-0 cursor-pointer outline-none pr-1 py-0.5"
            >
              <option v-for="m in MONTH_OPTIONS" :key="m.id" :value="m.id" class="text-slate-900">{{ m.label }}</option>
            </select>
          </div>

          <!-- Tahun -->
          <div class="flex items-center gap-1 bg-[#002D29] border border-white/15 rounded-lg px-2 py-1 text-white">
            <select
              v-model.number="selectedYear"
              @change="navigateFilters()"
              class="bg-transparent text-xs font-semibold text-white border-none focus:ring-0 cursor-pointer outline-none pr-1 py-0.5"
            >
              <option v-for="y in YEAR_OPTIONS" :key="y" :value="y" class="text-slate-900">{{ y }}</option>
            </select>
          </div>

          <Link
            :href="sinkarkesFormUrl"
            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-[#99DFD6] bg-white/10 hover:bg-white/20 border border-white/15 transition-colors"
            title="Buka Formulir Laporan SINKARKES untuk melihat akumulasi bulan ini"
          >
            <FileSpreadsheet class="w-3.5 h-3.5" />
            <span>Form SINKARKES</span>
            <ExternalLink class="w-3 h-3 opacity-75" />
          </Link>

          <button
            type="button"
            @click="openImportModal"
            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-white/10 hover:bg-white/20 border border-white/15 transition-colors cursor-pointer"
          >
            <Upload class="w-3.5 h-3.5 text-[#99DFD6]" />
            <span>Import</span>
          </button>

          <a
            :href="exportDataUrl"
            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-white/10 hover:bg-white/20 border border-white/15 transition-colors"
          >
            <Download class="w-3.5 h-3.5 text-[#99CC33]" />
            <span>Ekspor</span>
          </a>

          <button
            type="button"
            @click="openCreateForm(activeTab === 'pesawat' ? 'pesawat' : 'kapal')"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-[#00A99D] hover:bg-[#009388] transition-colors cursor-pointer"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>Input Data Harian</span>
          </button>
        </div>
      </div>

      <!-- KPI Overview Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-2.5">
        <div class="clinical-kpi flex items-center justify-between">
          <div>
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Rekap Harian Kapal</p>
            <p class="text-xl font-bold clinical-num text-slate-900 mt-0.5">{{ kapalRecords.length }} <span class="text-xs font-medium text-slate-500">hari</span></p>
            <p class="text-[11px] text-slate-500 mt-0.5 clinical-num">{{ kapalTotalArmada.toLocaleString('id-ID') }} kapal &bull; {{ kapalTotalOrang.toLocaleString('id-ID') }} org</p>
          </div>
          <div class="w-9 h-9 rounded-lg bg-teal-50 border border-teal-200 flex items-center justify-center text-[#00A99D]">
            <Ship class="w-4 h-4" />
          </div>
        </div>

        <div class="clinical-kpi flex items-center justify-between">
          <div>
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Rekap Harian Pesawat</p>
            <p class="text-xl font-bold clinical-num text-slate-900 mt-0.5">{{ pesawatRecords.length }} <span class="text-xs font-medium text-slate-500">hari</span></p>
            <p class="text-[11px] text-slate-500 mt-0.5 clinical-num">{{ pesawatTotalArmada.toLocaleString('id-ID') }} pesawat &bull; {{ pesawatTotalOrang.toLocaleString('id-ID') }} org</p>
          </div>
          <div class="w-9 h-9 rounded-lg bg-sky-50 border border-sky-200 flex items-center justify-center text-sky-600">
            <Plane class="w-4 h-4" />
          </div>
        </div>

        <div class="clinical-kpi flex items-center justify-between">
          <div>
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Poin Merah SINKARKES</p>
            <p class="text-xl font-bold clinical-num text-rose-600 mt-0.5">
              {{ accumulation.indicators?.filter(i => i.daily_total > 0).length || 0 }}
              <span class="text-xs font-medium text-slate-500">/ {{ accumulation.indicators?.length || 0 }} aktif</span>
            </p>
            <p class="text-[11px] text-slate-500 mt-0.5">{{ activeMonthLabel }} {{ selectedYear }} &bull; {{ selectedWilker }}</p>
          </div>
          <div class="w-9 h-9 rounded-lg bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600">
            <Layers class="w-4 h-4" />
          </div>
        </div>

        <div class="clinical-kpi flex items-center justify-between">
          <div>
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Sinkron SINKARKES</p>
            <div class="mt-1 flex items-center gap-1.5">
              <span
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold"
                :class="accumulation.in_sync ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
              >
                <CheckCircle2 v-if="accumulation.in_sync" class="w-3 h-3" />
                <AlertCircle v-else class="w-3 h-3" />
                {{ accumulation.in_sync ? 'Tersinkron Penuh' : 'Perlu Sinkron' }}
              </span>
            </div>
            <button
              type="button"
              @click="triggerSync"
              :disabled="isSyncing"
              class="mt-1 inline-flex items-center gap-1 text-[11px] font-semibold text-[#005851] hover:underline cursor-pointer disabled:opacity-50"
            >
              <RefreshCw class="w-3 h-3" :class="{ 'animate-spin': isSyncing }" />
              <span>Sinkronkan Ulang Sekarang</span>
            </button>
          </div>
          <div class="w-9 h-9 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
            <Calculator class="w-4 h-4" />
          </div>
        </div>
      </div>

      <!-- Unified Multi-Tab Switcher -->
      <div class="clinical-tabs w-full flex-wrap justify-between gap-2">
        <div class="flex flex-wrap items-center gap-1">
          <button
            type="button"
            @click="switchTab('kapal')"
            class="clinical-tab cursor-pointer"
            :class="activeTab === 'kapal'
              ? 'clinical-tab-active'
              : 'clinical-tab-inactive'"
          >
            <Ship class="w-3.5 h-3.5" />
            <span>Rekap Harian Kapal</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] clinical-num" :class="activeTab === 'kapal' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">
              {{ kapalRecords.length }}
            </span>
          </button>

          <button
            type="button"
            @click="switchTab('pesawat')"
            class="clinical-tab cursor-pointer"
            :class="activeTab === 'pesawat'
              ? 'clinical-tab-active'
              : 'clinical-tab-inactive'"
          >
            <Plane class="w-3.5 h-3.5" />
            <span>Rekap Harian Pesawat</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="activeTab === 'pesawat' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200'">
              {{ pesawatRecords.length }}
            </span>
          </button>

          <button
            type="button"
            @click="switchTab('akumulasi')"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer"
            :class="activeTab === 'akumulasi'
              ? 'bg-rose-600 text-white shadow-sm'
              : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
          >
            <Calculator class="w-4 h-4" />
            <span>Akumulasi &rarr; Formulir SINKARKES</span>
          </button>
        </div>

        <div class="flex items-center gap-2">
          <a
            :href="exportTemplateUrl"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[11px] font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition-colors"
            title="Unduh template Excel kosong sesuai format rekap data harian kapal_pesawat.xlsx"
          >
            <Download class="w-3.5 h-3.5" />
            <span>Unduh Template Excel</span>
          </a>
        </div>
      </div>

      <!-- Daily Form Drawer / Card (when adding or editing Kapal / Pesawat) -->
      <div
        v-if="showForm"
        class="bg-white dark:bg-[#12161f] border-2 border-[#00A99D]/50 rounded-2xl shadow-md overflow-hidden"
      >
        <!-- Form Header -->
        <div class="px-5 py-3.5 bg-gradient-to-r from-[#005851] to-[#00A99D] text-white flex flex-wrap items-center justify-between gap-3">
          <div class="flex items-center gap-2.5">
            <Ship v-if="formTransportType === 'kapal'" class="w-5 h-5" />
            <Plane v-else class="w-5 h-5" />
            <div>
              <h2 class="text-sm sm:text-base font-bold">
                {{ editingId ? 'Edit Data Harian' : 'Input Data Harian Baru' }} &mdash;
                {{ formTransportType === 'kapal' ? 'Kapal (Sinkarkes kapal mentah)' : 'Pesawat (Sinkarkes pesawat mentah)' }}
              </h2>
              <p class="text-[11px] text-white/80">
                Poin bertanda label merah otomatis terakumulasi ke Formulir Laporan SINKARKES {{ formWilker }}.
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <!-- Switch Moda if creating new -->
            <div v-if="!editingId" class="inline-flex bg-black/20 rounded-xl p-0.5 text-xs font-bold">
              <button
                type="button"
                @click="formTransportType = 'kapal'; formMetrics = {}"
                class="px-3 py-1 rounded-lg transition cursor-pointer"
                :class="formTransportType === 'kapal' ? 'bg-white text-[#005851]' : 'text-white/80 hover:text-white'"
              >Kapal</button>
              <button
                type="button"
                @click="formTransportType = 'pesawat'; formMetrics = {}"
                class="px-3 py-1 rounded-lg transition cursor-pointer"
                :class="formTransportType === 'pesawat' ? 'bg-white text-sky-700' : 'text-white/80 hover:text-white'"
              >Pesawat</button>
            </div>

            <button type="button" @click="closeForm" class="p-1.5 rounded-lg hover:bg-white/20 transition cursor-pointer">
              <X class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Identity & Filter Bar -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-[#171d29] space-y-3">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Tanggal</label>
              <input
                v-model="formTanggal"
                type="date"
                class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#12161f] text-slate-900 dark:text-white focus:ring-2 focus:ring-[#00A99D] outline-none"
              />
            </div>

            <div>
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Nama Balai Karkes / Wilker</label>
              <select
                v-model="formWilker"
                class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#12161f] text-slate-900 dark:text-white focus:ring-2 focus:ring-[#00A99D] outline-none"
              >
                <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
              </select>
            </div>

            <div>
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Catatan Harian (Opsional)</label>
              <input
                v-model="formNotes"
                type="text"
                placeholder="Misal: Keterangan shift / kejadian khusus..."
                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#12161f] text-slate-900 dark:text-white focus:ring-2 focus:ring-[#00A99D] outline-none"
              />
            </div>
          </div>

          <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                @click="showOnlyRedFields = !showOnlyRedFields"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border transition-all cursor-pointer"
                :class="showOnlyRedFields
                  ? 'bg-rose-600 text-white border-rose-600 shadow-2xs'
                  : 'bg-white dark:bg-[#12161f] text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800 hover:bg-rose-50'"
              >
                <Filter class="w-3.5 h-3.5" />
                <span>
                  {{ showOnlyRedFields
                    ? `Menampilkan Hanya Poin Merah SINKARKES (${activeFormSchema.red_mapped_count || 0})`
                    : `Tampilkan Hanya Poin Merah SINKARKES (${activeFormSchema.red_mapped_count || 0})` }}
                </span>
              </button>

              <span class="text-[11px] text-slate-500">
                Terisi: <strong class="text-slate-800 dark:text-slate-200">{{ filledMetricCount }}</strong> kolom
                (Total {{ activeFormSchema.total_metric_columns || 0 }} kolom Excel)
              </span>
            </div>

            <div class="relative w-full sm:w-64">
              <Search class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input
                v-model="formFieldSearch"
                type="text"
                placeholder="Cari nama kolom / kode SINKARKES..."
                class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#12161f] text-slate-900 dark:text-white focus:ring-2 focus:ring-[#00A99D] outline-none"
              />
            </div>
          </div>
        </div>

        <!-- Form Metric Rows (Table / Row Layout) -->
        <div class="max-h-[68vh] overflow-y-auto overflow-x-auto bg-white dark:bg-[#12161f]">
          <table class="w-full min-w-[680px] text-left text-xs border-collapse">
            <thead class="sticky top-0 z-10 bg-slate-100 dark:bg-[#161c28] text-slate-600 dark:text-slate-300 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200 dark:border-slate-800 shadow-2xs">
              <tr>
                <th class="py-2.5 px-3 w-16 text-center whitespace-nowrap">Kolom</th>
                <th class="py-2.5 px-3 sm:px-4 min-w-[260px]">Uraian Indikator Harian</th>
                <th class="py-2.5 px-3 sm:px-4 w-72 whitespace-nowrap">Masuk ke SINKARKES</th>
                <th class="py-2.5 px-3 sm:px-5 w-44 text-right whitespace-nowrap bg-slate-200/50 dark:bg-[#1a2130]">
                  Input Nilai ({{ formTanggal || 'Harian' }})
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70">
              <tr v-if="groupedFormSections.length === 0">
                <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                  Tidak ada indikator yang cocok dengan filter pencarian saat ini.
                </td>
              </tr>

              <template v-for="sec in groupedFormSections" :key="sec.id">
                <!-- Category Group Divider Row (Collapsible) -->
                <tr
                  @click="toggleGroup(sec.id)"
                  class="bg-slate-50/95 dark:bg-[#171d29] hover:bg-slate-100 dark:hover:bg-slate-800/80 border-t-2 border-slate-200 dark:border-slate-700 cursor-pointer select-none transition-colors"
                >
                  <td colspan="4" class="py-2.5 px-3 sm:px-4">
                    <div class="flex items-center justify-between gap-2">
                      <div class="flex items-center gap-2 flex-wrap">
                        <span class="w-2 h-2 rounded-full bg-[#00A99D]"></span>
                        <span class="text-xs font-extrabold uppercase tracking-wide text-slate-800 dark:text-slate-100">
                          {{ sec.title }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">
                          {{ sec.fields.length }} baris
                        </span>
                        <span
                          v-if="sec.redCount > 0"
                          class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300"
                        >
                          {{ sec.redCount }} poin SINKARKES
                        </span>
                      </div>
                      <ChevronDown v-if="collapsedGroups[sec.id]" class="w-4 h-4 text-slate-400 shrink-0" />
                      <ChevronUp v-else class="w-4 h-4 text-slate-400 shrink-0" />
                    </div>
                  </td>
                </tr>

                <!-- Metric Input Rows -->
                <template v-if="!collapsedGroups[sec.id]">
                  <tr
                    v-for="field in sec.fields"
                    :key="field.key"
                    class="transition-colors"
                    :class="field.is_red
                      ? 'bg-rose-50/35 dark:bg-rose-950/10 hover:bg-rose-50/70 dark:hover:bg-rose-950/25 border-l-4 border-l-rose-500'
                      : 'hover:bg-slate-50/80 dark:hover:bg-[#171d29]/50'"
                  >
                    <!-- Excel Column Letter -->
                    <td class="py-2 px-3 text-center whitespace-nowrap">
                      <span
                        class="inline-flex items-center justify-center min-w-[2.2rem] px-1.5 py-0.5 rounded font-mono text-[11px] font-bold border"
                        :class="field.is_red
                          ? 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800'
                          : 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'"
                      >
                        {{ field.excel_col }}
                      </span>
                    </td>

                    <!-- Indicator Name -->
                    <td class="py-2 px-3 sm:px-4">
                      <label :for="`daily-field-${field.key}`" class="block font-semibold text-slate-800 dark:text-slate-100 leading-snug cursor-pointer">
                        {{ field.label }}
                      </label>
                      <span v-if="field.dedupe_pair" class="inline-block mt-0.5 text-[10px] font-medium text-amber-700 dark:text-amber-400">
                        * Anti-ganda otomatis dengan pasangan kapal/dokumen
                      </span>
                    </td>

                    <!-- Target SINKARKES Mapping -->
                    <td class="py-2 px-3 sm:px-4">
                      <div v-if="field.is_red" class="space-y-0.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-600 text-white">
                          Baris {{ field.sinkarkes_excel_mark }} &bull; {{ field.sinkarkes_code }}
                        </span>
                        <div class="text-[11px] text-rose-700 dark:text-rose-300 leading-tight">
                          {{ field.sinkarkes_label }}
                        </div>
                      </div>
                      <span v-else class="text-[11px] text-slate-400 italic">
                        Rekap harian saja
                      </span>
                    </td>

                    <!-- Numeric Input Box -->
                    <td class="py-2 px-3 sm:px-5 text-right bg-slate-50/40 dark:bg-[#161c28]/40">
                      <input
                        :id="`daily-field-${field.key}`"
                        :value="formMetrics[field.key] ?? ''"
                        @input="(e) => sanitizeMetricValue(field.key, e.target.value)"
                        type="number"
                        min="0"
                        placeholder="0"
                        class="w-32 sm:w-36 ml-auto px-3 py-1.5 text-sm font-bold text-right rounded-lg border bg-white dark:bg-[#0e121a] text-slate-900 dark:text-white focus:ring-2 focus:ring-[#00A99D] outline-none transition"
                        :class="field.is_red ? 'border-rose-300 dark:border-rose-700' : 'border-slate-200 dark:border-slate-700'"
                      />
                    </td>
                  </tr>
                </template>
              </template>
            </tbody>
          </table>
        </div>

        <!-- Live Impact Preview + Save Footer -->
        <div class="px-5 py-3.5 bg-slate-50 dark:bg-[#171d29] border-t border-slate-200 dark:border-slate-800 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
          <div class="flex flex-wrap items-center gap-1.5 min-w-0">
            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300 mr-1">Kontribusi ke SINKARKES dari entri ini:</span>
            <span v-if="formRedImpactPreview.length === 0" class="text-[11px] text-slate-400 italic">Belum ada poin SINKARKES terisi (&gt;0)</span>
            <span
              v-for="item in formRedImpactPreview.slice(0, 8)"
              :key="item.field.sinkarkes_target_row"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800"
            >
              {{ item.field.sinkarkes_code }}: +{{ item.total }}
            </span>
            <span v-if="formRedImpactPreview.length > 8" class="text-[10px] font-semibold text-rose-600">
              +{{ formRedImpactPreview.length - 8 }} indikator lain
            </span>
          </div>

          <div class="flex items-center gap-2 shrink-0 justify-end">
            <button
              type="button"
              @click="closeForm"
              class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 transition cursor-pointer"
            >Batal</button>
            <button
              type="button"
              @click="saveDailyRecord"
              :disabled="isSaving"
              class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl text-xs font-bold text-white bg-[#00A99D] hover:bg-[#008f85] disabled:opacity-50 shadow-sm transition cursor-pointer"
            >
              <Save class="w-4 h-4" />
              <span>{{ isSaving ? 'Menyimpan...' : 'Simpan & Akumulasikan ke SINKARKES' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- TAB 1 & TAB 2: KAPAL OR PESAWAT TABLE -->
      <div
        v-if="activeTab === 'kapal' || activeTab === 'pesawat'"
        class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-2xl shadow-xs overflow-hidden"
      >
        <div class="px-4 sm:px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
          <div class="flex items-center gap-2">
            <Ship v-if="activeTab === 'kapal'" class="w-4 h-4 text-[#00A99D]" />
            <Plane v-else class="w-4 h-4 text-sky-600" />
            <h2 class="text-sm font-extrabold text-slate-900 dark:text-white">
              Daftar Input Harian {{ activeTab === 'kapal' ? 'Kapal' : 'Pesawat' }} &mdash; {{ activeMonthLabel }} {{ selectedYear }} ({{ selectedWilker }})
            </h2>
          </div>

          <div class="flex items-center gap-2">
            <div class="relative w-56">
              <Search class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Filter tanggal / catatan..."
                class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-[#1a2130] text-slate-800 dark:text-slate-100 outline-none focus:ring-2 focus:ring-[#00A99D]"
              />
            </div>

            <button
              type="button"
              @click="openCreateForm(activeTab)"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-[#00A99D] hover:bg-[#008f85] transition cursor-pointer"
            >
              <Plus class="w-3.5 h-3.5" />
              <span>Tambah {{ activeTab === 'kapal' ? 'Kapal' : 'Pesawat' }}</span>
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50 dark:bg-[#171d29] border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3">Kedatangan (DN / LN / Terjangkit)</th>
                <th class="px-4 py-3">Keberangkatan (DN / LN / Terjangkit)</th>
                <th class="px-4 py-3">Kru &amp; Penumpang</th>
                <th class="px-4 py-3">Dokumen &amp; Tindakan SINKARKES</th>
                <th class="px-4 py-3">Sinkron</th>
                <th class="px-4 py-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70 text-xs">
              <tr v-if="currentTypeRecords.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                  <div class="flex flex-col items-center gap-2">
                    <Ship v-if="activeTab === 'kapal'" class="w-8 h-8 opacity-40" />
                    <Plane v-else class="w-8 h-8 opacity-40" />
                    <p class="font-semibold text-slate-600 dark:text-slate-300">
                      Belum ada inputan harian {{ activeTab === 'kapal' ? 'kapal' : 'pesawat' }} pada {{ activeMonthLabel }} {{ selectedYear }} untuk {{ selectedWilker }}.
                    </p>
                    <p class="text-[11px]">Klik tombol Tambah di atas atau Import Excel untuk mengisi data.</p>
                  </div>
                </td>
              </tr>

              <tr
                v-for="rec in currentTypeRecords"
                :key="rec.id"
                class="hover:bg-slate-50/70 dark:hover:bg-[#171d29]/60 transition-colors"
              >
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="font-bold text-slate-900 dark:text-white">{{ formatDateId(rec.tanggal) }}</div>
                  <div class="text-[10px] text-slate-400">{{ rec.updater_name }}</div>
                </td>

                <!-- Kedatangan -->
                <td class="px-4 py-3 whitespace-nowrap">
                  <template v-if="activeTab === 'kapal'">
                    <div class="flex items-center gap-1.5 font-mono">
                      <span class="px-1.5 py-0.5 rounded bg-cyan-50 text-cyan-800 border border-cyan-200 font-bold" title="Dalam Negeri (Baris 13)">DN: {{ rec.metrics?.kd_dn_kapal || 0 }}</span>
                      <span class="px-1.5 py-0.5 rounded bg-sky-50 text-sky-800 border border-sky-200 font-bold" title="Luar Negeri (Baris 10)">LN: {{ rec.metrics?.kd_ln_kapal || 0 }}</span>
                      <span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 font-bold" title="LN Terjangkit (Baris 9)">TJK: {{ rec.metrics?.kd_lnt_kapal || 0 }}</span>
                    </div>
                  </template>
                  <template v-else>
                    <div class="flex items-center gap-1.5 font-mono">
                      <span class="px-1.5 py-0.5 rounded bg-cyan-50 text-cyan-800 border border-cyan-200 font-bold" title="Dalam Negeri (Baris 29)">DN: {{ rec.metrics?.kd_dn_pesawat || 0 }}</span>
                      <span class="px-1.5 py-0.5 rounded bg-sky-50 text-sky-800 border border-sky-200 font-bold" title="Luar Negeri (Baris 24)">LN: {{ rec.metrics?.kd_ln_pesawat || 0 }}</span>
                      <span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 font-bold" title="LN Terjangkit (Baris 23)">TJK: {{ rec.metrics?.kd_lnt_pesawat || 0 }}</span>
                    </div>
                  </template>
                </td>

                <!-- Keberangkatan -->
                <td class="px-4 py-3 whitespace-nowrap">
                  <template v-if="activeTab === 'kapal'">
                    <div class="flex items-center gap-1.5 font-mono">
                      <span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 font-bold" title="Dalam Negeri (Baris 20)">DN: {{ rec.metrics?.kb_dn_kapal || 0 }}</span>
                      <span class="px-1.5 py-0.5 rounded bg-orange-50 text-orange-800 border border-orange-200 font-bold" title="Luar Negeri (Baris 17)">LN: {{ rec.metrics?.kb_ln_kapal || 0 }}</span>
                      <span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 font-bold" title="LN Terjangkit (Baris 16)">TJK: {{ rec.metrics?.kb_lnt_kapal || 0 }}</span>
                    </div>
                  </template>
                  <template v-else>
                    <div class="flex items-center gap-1.5 font-mono">
                      <span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 font-bold" title="Dalam Negeri (Baris 36)">DN: {{ rec.metrics?.kb_dn_pesawat || 0 }}</span>
                      <span class="px-1.5 py-0.5 rounded bg-orange-50 text-orange-800 border border-orange-200 font-bold" title="Luar Negeri (Baris 33)">LN: {{ rec.metrics?.kb_ln_pesawat || 0 }}</span>
                      <span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 font-bold" title="LN Terjangkit (Baris 32)">TJK: {{ rec.metrics?.kb_lnt_pesawat || 0 }}</span>
                    </div>
                  </template>
                </td>

                <!-- Kru & Penumpang -->
                <td class="px-4 py-3 whitespace-nowrap">
                  <div class="font-bold text-slate-800 dark:text-slate-200">
                    {{ (rec.total_penumpang || 0).toLocaleString('id-ID') }} Pnp
                  </div>
                  <div class="text-[11px] text-slate-500">
                    {{ (rec.total_kru || 0).toLocaleString('id-ID') }} Kru
                  </div>
                </td>

                <!-- Key SINKARKES Document Pills -->
                <td class="px-4 py-3">
                  <div v-if="activeTab === 'kapal'" class="flex flex-wrap gap-1 max-w-sm">
                    <span v-if="rec.metrics?.dok_sanitasi_sscc" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">SSCC: {{ rec.metrics.dok_sanitasi_sscc }}</span>
                    <span v-if="rec.metrics?.dok_sanitasi_sscec" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">SSCEC: {{ rec.metrics.dok_sanitasi_sscec }}</span>
                    <span v-if="rec.metrics?.dok_sanitasi_sertifikat_p3k_kapal" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">P3K: {{ rec.metrics.dok_sanitasi_sertifikat_p3k_kapal }}</span>
                    <span v-if="rec.metrics?.dok_sanitasi_buku_kesehatan_kapal" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">Buku Kes: {{ rec.metrics.dok_sanitasi_buku_kesehatan_kapal }}</span>
                    <span v-if="rec.metrics?.dok_lainnya_sertifikat_omka_eksport" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">OMKA: {{ rec.metrics.dok_lainnya_sertifikat_omka_eksport }}</span>
                    <span v-if="sumKeys(rec.metrics, ['tindakan_tindakan_fumigasi', 'tindakan_tindakan_desinseksi']) > 0" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Desinseksi/Fum: {{ sumKeys(rec.metrics, ['tindakan_tindakan_fumigasi', 'tindakan_tindakan_desinseksi']) }}</span>
                    <span v-if="rec.metrics?.tindakan_tindakan_desinfeksi" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Desinfeksi: {{ rec.metrics.tindakan_tindakan_desinfeksi }}</span>
                    <span v-if="rec.metrics?.dok_ukp_sertifikat_angkut_jenazah" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300">SIAJ: {{ rec.metrics.dok_ukp_sertifikat_angkut_jenazah }}</span>
                    <span v-if="rec.metrics?.dok_ukp_sertifikat_angkut_orang_sakit" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300">SIAOS: {{ rec.metrics.dok_ukp_sertifikat_angkut_orang_sakit }}</span>
                    <span v-if="Object.keys(rec.sinkarkes_contribution || {}).length === 0" class="text-[11px] text-slate-400">-</span>
                  </div>
                  <div v-else class="flex flex-wrap gap-1 max-w-sm">
                    <span v-if="rec.metrics?.dok_ukp_surat_ket_laik_terbang_sklt" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">SKLT: {{ rec.metrics.dok_ukp_surat_ket_laik_terbang_sklt }}</span>
                    <span v-if="rec.metrics?.dok_ukp_sertifikat_angkut_jenazah_siaj" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">SIAJ: {{ rec.metrics.dok_ukp_sertifikat_angkut_jenazah_siaj }}</span>
                    <span v-if="rec.metrics?.dok_ukp_sertifikat_angkut_orang_sakit_siaos" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">SIAOS: {{ rec.metrics.dok_ukp_sertifikat_angkut_orang_sakit_siaos }}</span>
                    <span v-if="sumKeys(rec.metrics, ['dok_dc_dec_dc_dalam_negeri', 'dok_dc_dec_dc_luar_negeri', 'dok_dc_dec_dec_dalam_negeri', 'dok_dc_dec_dec_luar_negeri']) > 0" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">DC/DEC: {{ sumKeys(rec.metrics, ['dok_dc_dec_dc_dalam_negeri', 'dok_dc_dec_dc_luar_negeri', 'dok_dc_dec_dec_dalam_negeri', 'dok_dc_dec_dec_luar_negeri']) }}</span>
                    <span v-if="Object.keys(rec.sinkarkes_contribution || {}).length === 0" class="text-[11px] text-slate-400">-</span>
                  </div>
                </td>

                <!-- Sync status -->
                <td class="px-4 py-3 whitespace-nowrap">
                  <span
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"
                    :title="rec.sinkarkes_synced_at ? `Disinkron pada ${rec.sinkarkes_synced_at}` : ''"
                  >
                    <CheckCircle2 class="w-3 h-3" />
                    SINKARKES
                  </span>
                </td>

                <!-- Actions -->
                <td class="px-4 py-3 whitespace-nowrap text-right">
                  <div class="inline-flex items-center gap-1">
                    <button
                      type="button"
                      @click="openEditForm(rec)"
                      class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 cursor-pointer"
                    >
                      <Edit3 class="w-3.5 h-3.5" />
                      <span>Edit</span>
                    </button>
                    <button
                      type="button"
                      @click="deleteRecord(rec)"
                      class="inline-flex items-center p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/60 cursor-pointer"
                      title="Hapus Entri Harian"
                    >
                      <Trash2 class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 3: AKUMULASI -> SINKARKES COMPARISON -->
      <div
        v-if="activeTab === 'akumulasi'"
        class="space-y-4"
      >
        <!-- Summary Row Rollups -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <div class="bg-white dark:bg-[#12161f] border border-rose-200 dark:border-rose-800/60 rounded-2xl p-4 shadow-2xs">
            <div class="text-[10px] font-mono font-bold text-rose-600">BARIS 99 (KSE.11)</div>
            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 mt-0.5">Sertifikat Desinseksi Total</div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ (accumulation.summary_totals?.[99] || 0).toLocaleString('id-ID') }}</div>
            <div class="text-[10px] text-slate-500 mt-0.5">Akumulasi baris 101 Kapal + baris 100 Pesawat</div>
          </div>

          <div class="bg-white dark:bg-[#12161f] border border-rose-200 dark:border-rose-800/60 rounded-2xl p-4 shadow-2xs">
            <div class="text-[10px] font-mono font-bold text-rose-600">BARIS 102 (KSE.12)</div>
            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 mt-0.5">Sertifikat Desinfeksi Total</div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ (accumulation.summary_totals?.[102] || 0).toLocaleString('id-ID') }}</div>
            <div class="text-[10px] text-slate-500 mt-0.5">Akumulasi baris 104 Kapal + baris 103 Pesawat</div>
          </div>

          <div class="bg-white dark:bg-[#12161f] border border-rose-200 dark:border-rose-800/60 rounded-2xl p-4 shadow-2xs">
            <div class="text-[10px] font-mono font-bold text-rose-600">BARIS 123 (KSE.18.A)</div>
            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 mt-0.5">OMKA Eksport Total</div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ (accumulation.summary_totals?.[123] || 0).toLocaleString('id-ID') }}</div>
            <div class="text-[10px] text-slate-500 mt-0.5">Masuk ke baris 124 (KSE.18.A1 Sesuai syarat)</div>
          </div>

          <div class="bg-white dark:bg-[#12161f] border border-rose-200 dark:border-rose-800/60 rounded-2xl p-4 shadow-2xs">
            <div class="text-[10px] font-mono font-bold text-rose-600">BARIS 198 (UKP.7)</div>
            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 mt-0.5">Ijin Angkut Jenazah (SIAJ)</div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ (accumulation.summary_totals?.[198] || 0).toLocaleString('id-ID') }}</div>
            <div class="text-[10px] text-slate-500 mt-0.5">Gabungan Kapal &amp; Pesawat &rarr; baris 199 (UKP.7.A)</div>
          </div>
        </div>

        <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-2xl shadow-xs overflow-hidden">
          <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
            <div>
              <h2 class="text-sm font-extrabold text-slate-900 dark:text-white">
                Matriks Akumulasi Harian &rarr; Formulir Laporan SINKARKES ({{ activeMonthLabel }} {{ selectedYear }} &bull; {{ selectedWilker }})
              </h2>
              <p class="text-[11px] text-slate-500 mt-0.5">
                Menampilkan seluruh poin bertanda merah di Excel beserta nilai akumulasi yang tertulis pada Formulir Laporan SINKARKES.
              </p>
            </div>

            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="triggerSync"
                :disabled="isSyncing"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-[#00A99D] hover:bg-[#008f85] transition cursor-pointer disabled:opacity-50"
              >
                <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isSyncing }" />
                <span>Sinkronkan Ulang</span>
              </button>

              <Link
                :href="sinkarkesFormUrl"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-[#005851] bg-[#00A99D]/10 hover:bg-[#00A99D]/20 border border-[#00A99D]/30 transition"
              >
                <FileSpreadsheet class="w-3.5 h-3.5" />
                <span>Verifikasi di Form SINKARKES</span>
              </Link>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50 dark:bg-[#171d29] border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                  <th class="px-4 py-3">Row Excel</th>
                  <th class="px-4 py-3">Kode</th>
                  <th class="px-4 py-3">Indikator Formulir Laporan SINKARKES</th>
                  <th class="px-4 py-3 text-right">Kapal</th>
                  <th class="px-4 py-3 text-right">Pesawat</th>
                  <th class="px-4 py-3 text-right">Total Harian</th>
                  <th class="px-4 py-3 text-right">Di Form SINKARKES</th>
                  <th class="px-4 py-3 text-center">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70 text-xs">
                <tr
                  v-for="ind in accumulation.indicators"
                  :key="ind.excel_row"
                  :class="ind.daily_total > 0 ? 'bg-rose-50/20 dark:bg-rose-950/10' : ''"
                >
                  <td class="px-4 py-2.5 font-mono font-bold text-rose-600 whitespace-nowrap">
                    Baris {{ ind.excel_row }}
                    <span v-if="ind.summary_row" class="block text-[10px] text-slate-400 font-normal">
                      &rarr; Rollup Row {{ ind.summary_row }}
                    </span>
                  </td>
                  <td class="px-4 py-2.5 font-mono text-[11px] font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">
                    {{ ind.code }}
                  </td>
                  <td class="px-4 py-2.5 text-slate-800 dark:text-slate-100">
                    {{ ind.label }}
                    <span v-if="ind.unit" class="text-[10px] text-slate-400 ml-1">({{ ind.unit }})</span>
                  </td>
                  <td class="px-4 py-2.5 text-right font-mono">{{ ind.kapal_total.toLocaleString('id-ID') }}</td>
                  <td class="px-4 py-2.5 text-right font-mono">{{ ind.pesawat_total.toLocaleString('id-ID') }}</td>
                  <td class="px-4 py-2.5 text-right font-mono font-extrabold text-slate-900 dark:text-white">{{ ind.daily_total.toLocaleString('id-ID') }}</td>
                  <td class="px-4 py-2.5 text-right font-mono font-bold text-[#005851] dark:text-[#00A99D]">{{ ind.saved_sinkarkes_value.toLocaleString('id-ID') }}</td>
                  <td class="px-4 py-2.5 text-center whitespace-nowrap">
                    <span
                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                      :class="ind.in_sync ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                    >
                      <CheckCircle2 v-if="ind.in_sync" class="w-3 h-3" />
                      <AlertCircle v-else class="w-3 h-3" />
                      {{ ind.in_sync ? 'Sinkron' : 'Selisih' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Import Modal -->
      <div
        v-if="showImportModal"
        class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
      >
        <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-slate-700 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
          <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Upload class="w-5 h-5 text-[#00A99D]" />
              <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Import Excel Rekap Harian Kapal &amp; Pesawat</h3>
            </div>
            <button type="button" @click="closeImportModal" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
              <X class="w-4 h-4 text-slate-500" />
            </button>
          </div>

          <div class="p-5 space-y-4 text-xs">
            <div class="p-3 rounded-xl bg-teal-50/70 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-800 text-teal-800 dark:text-teal-200 flex items-start gap-2">
              <Info class="w-4 h-4 shrink-0 mt-0.5" />
              <p>
                Unggah file Excel dengan sheet <strong>Sinkarkes kapal mentah</strong> dan/atau <strong>Sinkarkes pesawat mentah</strong>. Data harian yang diimpor otomatis diakumulasikan ke Formulir Laporan SINKARKES sesuai periode bulan &amp; Wilker.
              </p>
            </div>

            <div>
              <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Wilker Default (jika kolom B kosong)</label>
              <select
                v-model="importWilker"
                class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#1a2130] text-slate-900 dark:text-white outline-none"
              >
                <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Pilih File Excel (.xlsx / .xls)</label>
              <input
                ref="fileInputRef"
                type="file"
                accept=".xlsx,.xls"
                @change="onFileChange"
                class="w-full text-xs text-slate-600 dark:text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#00A99D]/10 file:text-[#005851] hover:file:bg-[#00A99D]/20 cursor-pointer"
              />
            </div>

            <div v-if="importResult" class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 space-y-1">
              <p class="font-bold">Import Berhasil!</p>
              <p>Total Entri: {{ importResult.records_imported }} &bull; Periode Tersinkron: {{ importResult.periods_synced }}</p>
            </div>
          </div>

          <div class="px-5 py-3.5 bg-slate-50 dark:bg-[#171d29] border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="closeImportModal"
              class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 cursor-pointer"
            >Tutup</button>
            <button
              type="button"
              @click="submitImport"
              :disabled="isImporting || !importFile"
              class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-[#00A99D] hover:bg-[#008f85] disabled:opacity-50 cursor-pointer"
            >
              <Upload class="w-3.5 h-3.5" />
              <span>{{ isImporting ? 'Mengimpor...' : 'Proses Import & Sinkron SINKARKES' }}</span>
            </button>
          </div>
        </div>
      </div>

    </main>
  </HybridNavbar>
</template>

