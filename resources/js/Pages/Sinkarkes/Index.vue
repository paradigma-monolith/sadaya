<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import schemaData from '../../data/sinkarkes_schema.json';
import { toast } from 'vue-sonner';
import {
  FileSpreadsheet,
  BarChart3,
  Save,
  Download,
  Calendar,
  Building2,
  ChevronLeft,
  ChevronRight,
  Search,
  CheckCircle2,
  AlertCircle,
  Calculator,
  RotateCcw,
  Sparkles,
  Layers,
  Activity,
  ShieldCheck,
  Check,
  Filter,
  Eye,
  Info,
  Clock,
  UserCheck,
  Percent,
  Upload,
  FileUp,
  X,
  CheckCheck,
  AlertTriangle
} from 'lucide-vue-next';

const props = defineProps({
  wilker: {
    type: String,
    default: 'Induk BKK Bandung',
  },
  year: {
    type: Number,
    default: 2026,
  },
  month: {
    type: Number,
    default: 1,
  },
  wilkerOptions: {
    type: Array,
    default: () => [
      'Induk BKK Bandung',
      'POS Bandara Husen',
      'Wilker Cirebon',
      'Wilker Indramayu',
      'Wilker Kertajati',
      'Wilker Patimban',
      'Wilker Pelabuharatu',
    ],
  },
  monthlyData: {
    type: Object,
    default: () => ({}),
  },
  monthlyStatus: {
    type: Object,
    default: () => ({}),
  },
});

// Active state
const selectedWilker = ref(props.wilker);
const selectedYear = ref(props.year);
const activeMonth = ref(props.month);
const activeSection = ref('KSE'); // 'KSE' | 'UKP' | 'PRL'
const searchQuery = ref('');
const filterType = ref('all'); // 'all' | 'input' | 'summary'
const isSaving = ref(false);
const isDirty = ref(false);

// Local monthly data clone for reactive editing: { 1: { [excel_row]: value }, ... }
const localMonthlyData = ref({});

// Initialize local data from props
const initLocalData = () => {
  const cloned = {};
  for (let m = 1; m <= 12; m++) {
    cloned[m] = { ...(props.monthlyData[m] || {}) };
  }
  localMonthlyData.value = cloned;
  isDirty.value = false;
};

initLocalData();

// Re-init if props update from backend navigation
watch(() => props.monthlyData, () => {
  initLocalData();
}, { deep: true });

// Available years
const availableYears = [2024, 2025, 2026, 2027, 2028, 2029, 2030];

// Month definitions from schema metadata
const monthsList = computed(() => schemaData.form_metadata?.months || [
  { id: 1, key: 'jan', label: 'JAN' },
  { id: 2, key: 'feb', label: 'FEB' },
  { id: 3, key: 'mar', label: 'MART' },
  { id: 4, key: 'apr', label: 'APRL' },
  { id: 5, key: 'mei', label: 'MEI' },
  { id: 6, key: 'jun', label: 'JUN' },
  { id: 7, key: 'jul', label: 'JULI' },
  { id: 8, key: 'agu', label: 'AGUST' },
  { id: 9, key: 'sep', label: 'SEPT' },
  { id: 10, key: 'okt', label: 'OKT' },
  { id: 11, key: 'nov', label: 'NOV' },
  { id: 12, key: 'des', label: 'DES' },
]);

// Sections list
const sections = computed(() => schemaData.sections || []);

// Active section object
const currentSectionObj = computed(() => {
  return sections.value.find(s => s.section_code === activeSection.value) || sections.value[0];
});

// Row Map for fast lookup by excel_row
const rowMap = computed(() => {
  const map = new Map();
  sections.value.forEach(sec => {
    sec.items?.forEach(item => {
      map.set(item.excel_row, item);
    });
  });
  return map;
});

// Reactive Summary Evaluation Engine for the active month
// Computes all summary rows iteratively from formula_rows
const computedActiveMonthValues = computed(() => {
  const currentMonthData = localMonthlyData.value[activeMonth.value] || {};
  const vals = { ...currentMonthData };

  let changed = true;
  let passes = 0;
  while (changed && passes < 10) {
    changed = false;
    passes++;
    sections.value.forEach(sec => {
      sec.items?.forEach(item => {
        if (item.type === 'summary' && item.formula_rows && item.formula_rows.length > 0) {
          let sum = 0;
          item.formula_rows.forEach(r => {
            const raw = vals[r];
            if (raw !== undefined && raw !== null && raw !== '') {
              const num = Number(raw);
              if (!isNaN(num)) {
                sum += num;
              }
            }
          });

          // Round cleanly to 2 decimal places if needed
          const finalSum = Math.round((sum + Number.EPSILON) * 100) / 100;
          if (vals[item.excel_row] !== finalSum) {
            vals[item.excel_row] = finalSum;
            changed = true;
          }
        }
      });
    });
  }

  return vals;
});

// Get value for a specific row in the active month
const getValue = (excelRow, type) => {
  if (type === 'summary') {
    return computedActiveMonthValues.value[excelRow] ?? 0;
  }
  return localMonthlyData.value[activeMonth.value]?.[excelRow] ?? '';
};

// Handle input change
const onInputChange = (excelRow, value, dataType) => {
  if (!localMonthlyData.value[activeMonth.value]) {
    localMonthlyData.value[activeMonth.value] = {};
  }

  let formattedValue = value;
  if (value === '' || value === null || value === undefined) {
    formattedValue = '';
  } else if (dataType === 'integer') {
    const parsed = parseInt(value, 10);
    formattedValue = isNaN(parsed) ? '' : Math.max(0, parsed);
  } else if (dataType === 'decimal') {
    const parsed = parseFloat(value);
    formattedValue = isNaN(parsed) ? '' : Math.max(0, parsed);
  }

  localMonthlyData.value[activeMonth.value][excelRow] = formattedValue;
  isDirty.value = true;
};

// Filtered items in active section based on search and type filter
const filteredItems = computed(() => {
  const items = currentSectionObj.value?.items || [];
  const q = searchQuery.value.trim().toLowerCase();

  return items.filter(item => {
    // Type filter
    if (filterType.value === 'input' && item.type !== 'input') return false;
    if (filterType.value === 'summary' && item.type !== 'summary') return false;

    // Search query filter
    if (q) {
      const matchLabel = item.label?.toLowerCase().includes(q);
      const matchNo = item.no?.toLowerCase().includes(q);
      const matchCode = item.code?.toLowerCase().includes(q);
      const matchUnit = item.unit?.toLowerCase().includes(q);
      return matchLabel || matchNo || matchCode || matchUnit;
    }

    return true;
  });
});

// Counts for each section
const sectionStats = computed(() => {
  const res = {};

  sections.value.forEach(sec => {
    const inputItems = sec.items?.filter(i => i.type === 'input') || [];
    let filled = 0;
    inputItems.forEach(i => {
      const val = localMonthlyData.value[activeMonth.value]?.[i.excel_row];
      if (val !== undefined && val !== null && val !== '') {
        filled++;
      }
    });

    res[sec.section_code] = {
      totalInputs: inputItems.length,
      filledInputs: filled,
      percent: inputItems.length > 0 ? Math.round((filled / inputItems.length) * 100) : 0,
    };
  });

  return res;
});

// Grand total filled across all sections for active month
const overallStats = computed(() => {
  let totalInputs = 0;
  let filledInputs = 0;
  Object.values(sectionStats.value).forEach(s => {
    totalInputs += s.totalInputs;
    filledInputs += s.filledInputs;
  });
  return {
    totalInputs,
    filledInputs,
    percent: totalInputs > 0 ? Math.round((filledInputs / totalInputs) * 100) : 0,
  };
});

// Month status indicators
const monthStatuses = computed(() => {
  const res = {};
  for (let m = 1; m <= 12; m++) {
    const mData = localMonthlyData.value[m] || {};
    const filledCount = Object.values(mData).filter(v => v !== '' && v !== null && v !== undefined).length;
    res[m] = {
      filledCount,
      hasData: filledCount > 0,
    };
  }
  return res;
});

// Active month meta from props
const activeMonthMeta = computed(() => {
  return props.monthlyStatus?.[activeMonth.value] || {};
});

// Save active month report via Inertia router
const saveReport = (targetMonth = null) => {
  if (isSaving.value) return;
  isSaving.value = true;
  const mToSave = targetMonth || activeMonth.value;
  const monthLabel = monthsList.value.find(m => m.id === mToSave)?.label || mToSave;

  const payload = {
    wilker: selectedWilker.value,
    year: selectedYear.value,
    month: mToSave,
    data: localMonthlyData.value[mToSave] || {},
    status: 'submitted',
  };

  router.post('/sinkarkes', payload, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isSaving.value = false;
      isDirty.value = false;
      toast.success('Data Laporan SINKARKES Tersimpan', {
        description: `${selectedWilker.value} &bull; Periode ${monthLabel} ${selectedYear.value}`,
      });
    },
    onError: (errors) => {
      isSaving.value = false;
      toast.error('Gagal menyimpan laporan', {
        description: Object.values(errors).flat().join(', ') || 'Terjadi kesalahan pada server.',
      });
    },
  });
};

// Quick Month Switching (auto-saves dirty changes)
const switchMonth = (mId) => {
  if (mId === activeMonth.value) return;
  if (isDirty.value) {
    saveReport(activeMonth.value);
  }
  activeMonth.value = mId;
};

const prevMonth = () => {
  if (activeMonth.value > 1) {
    switchMonth(activeMonth.value - 1);
  } else {
    switchMonth(12);
  }
};

const nextMonth = () => {
  if (activeMonth.value < 12) {
    switchMonth(activeMonth.value + 1);
  } else {
    switchMonth(1);
  }
};

// Period filter change (Wilker or Year)
const handlePeriodChange = () => {
  router.get('/sinkarkes', {
    wilker: selectedWilker.value,
    year: selectedYear.value,
    month: activeMonth.value,
  }, {
    preserveState: true,
    replace: true,
  });
};

// Clear active month form data
const resetActiveMonth = () => {
  const monthLabel = monthsList.value.find(m => m.id === activeMonth.value)?.label;
  if (confirm(`Apakah Anda yakin ingin mengosongkan seluruh isian data bulan ${monthLabel} ${selectedYear.value}?`)) {
    localMonthlyData.value[activeMonth.value] = {};
    isDirty.value = true;
    toast.info('Isian bulan aktif telah dikosongkan.');
  }
};

// Export to Excel
const exportExcelUrl = computed(() => {
  return `/sinkarkes/export?wilker=${encodeURIComponent(selectedWilker.value)}&year=${selectedYear.value}`;
});

// Import Modal State & Actions
const showImportModal = ref(false);
const importFile = ref(null);
const importYear = ref(props.year);
const importWilker = ref('all');
const importOverwrite = ref(true);
const isImporting = ref(false);
const importResult = ref(null);
const fileInputRef = ref(null);
const isDragging = ref(false);

const openImportModal = () => {
  importYear.value = selectedYear.value;
  importWilker.value = 'all';
  importFile.value = null;
  importResult.value = null;
  showImportModal.value = true;
};

const closeImportModal = () => {
  if (isImporting.value) return;
  showImportModal.value = false;
};

const onFileSelected = (e) => {
  const files = e.target.files;
  if (files && files[0]) {
    importFile.value = files[0];
    importResult.value = null;
  }
};

const onDragOver = (e) => {
  e.preventDefault();
  isDragging.value = true;
};

const onDragLeave = () => {
  isDragging.value = false;
};

const onDropFile = (e) => {
  e.preventDefault();
  isDragging.value = false;
  const files = e.dataTransfer.files;
  if (files && files[0]) {
    importFile.value = files[0];
    importResult.value = null;
  }
};

const triggerFileInput = () => {
  fileInputRef.value?.click();
};

const clearImportFile = () => {
  importFile.value = null;
  if (fileInputRef.value) fileInputRef.value.value = '';
};

const submitImport = async () => {
  if (!importFile.value) {
    toast.error('Pilih file Excel terlebih dahulu');
    return;
  }

  isImporting.value = true;
  importResult.value = null;

  const formData = new FormData();
  formData.append('file', importFile.value);
  formData.append('year', importYear.value);
  formData.append('wilker', importWilker.value);
  formData.append('overwrite', importOverwrite.value ? '1' : '0');

  try {
    const response = await fetch('/sinkarkes/import', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: formData,
    });

    const data = await response.json();

    if (response.ok && data.success) {
      importResult.value = data.result;
      toast.success(data.message || 'Import data SINKARKES berhasil!');
      setTimeout(() => {
        router.reload({ preserveScroll: true });
      }, 1500);
    } else {
      toast.error('Gagal mengimpor file', {
        description: data.message || 'Periksa kembali struktur file Excel.',
      });
    }
  } catch (err) {
    console.error('Import error:', err);
    toast.error('Terjadi kesalahan saat mengunggah file', {
      description: err.message,
    });
  } finally {
    isImporting.value = false;
  }
};

// Keyboard shortcut (Ctrl+S / Cmd+S to save)
const handleKeyDown = (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
    e.preventDefault();
    saveReport();
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
  <Head :title="`SINKARKES - ${selectedWilker} (${selectedYear})`" />

  <HybridNavbar mode="inertia" currentRoute="sinkarkes">
    <main class="w-full px-3 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-4 max-w-[1600px] mx-auto font-sans">

      <!-- Top Institutional Banner & Period Controls -->
      <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-xs transition-all">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <!-- Title & Meta -->
          <div class="space-y-1.5">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] sm:text-xs font-mono font-semibold bg-[#00A99D]/10 text-[#005851] dark:text-[#00A99D] border border-[#00A99D]/30">
              <Building2 class="w-3.5 h-3.5 text-[#00A99D]" />
              <span>SINKARKES &bull; BKK KELAS I BANDUNG</span>
            </div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5 flex-wrap">
              <span>Formulir Laporan SINKARKES</span>
              <span v-if="isDirty" class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-700 bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700/50 px-2 py-0.5 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                Ada perubahan belum disimpan
              </span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-3xl leading-relaxed">
              Instrumen pengumpulan data karantina &amp; surveilans epidemiologi (KSE), upaya kesehatan (UKP), dan pengendalian risiko lingkungan (PRL) wilayah kerja BKK Bandung.
            </p>
          </div>

          <!-- Controls: Wilker, Year, Export & Save -->
          <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <!-- Wilayah Kerja Dropdown -->
            <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-[#1a2130] border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1.5 shadow-2xs">
              <Building2 class="w-4 h-4 text-[#00A99D] shrink-0" />
              <select
                v-model="selectedWilker"
                @change="handlePeriodChange"
                class="bg-transparent text-xs font-bold text-slate-800 dark:text-slate-100 border-none focus:ring-0 cursor-pointer outline-none pr-2 py-0.5"
                title="Pilih Wilayah Kerja"
              >
                <option v-for="w in wilkerOptions" :key="w" :value="w">
                  {{ w }}
                </option>
              </select>
            </div>

            <!-- Tahun Dropdown -->
            <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-[#1a2130] border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1.5 shadow-2xs">
              <Calendar class="w-4 h-4 text-[#7CA622] shrink-0" />
              <select
                v-model.number="selectedYear"
                @change="handlePeriodChange"
                class="bg-transparent text-xs font-bold text-slate-800 dark:text-slate-100 border-none focus:ring-0 cursor-pointer outline-none pr-2 py-0.5"
                title="Pilih Tahun"
              >
                <option v-for="y in availableYears" :key="y" :value="y">
                  Tahun {{ y }}
                </option>
              </select>
            </div>

            <!-- Dashboard Grafis Button -->
            <Link
              :href="`/sinkarkes/dashboard?year=${selectedYear}&wilker=${encodeURIComponent(selectedWilker)}`"
              class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors shadow-2xs"
              title="Buka Dashboard Eksekutif & Diagram Grafis SINKARKES"
            >
              <BarChart3 class="w-4 h-4 text-[#00A99D]" />
              <span class="hidden sm:inline">Dashboard Grafis</span>
              <span class="sm:hidden">Grafik</span>
            </Link>

            <!-- Import Excel Button -->
            <button
              type="button"
              @click="openImportModal"
              class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-[#005851] dark:text-[#00A99D] bg-[#00A99D]/10 hover:bg-[#00A99D]/20 border border-[#00A99D]/30 transition-colors shadow-2xs cursor-pointer"
              title="Import Data SINKARKES dari File Excel Resmi"
            >
              <Upload class="w-4 h-4" />
              <span class="hidden sm:inline">Import Excel</span>
              <span class="sm:hidden">Import</span>
            </button>

            <!-- Ekspor Excel Button -->
            <a
              :href="exportExcelUrl"
              class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-300 dark:border-emerald-800/60 transition-colors shadow-2xs"
              title="Unduh Formulir Lengkap 12 Bulan (Format Excel Asli)"
            >
              <Download class="w-4 h-4" />
              <span class="hidden sm:inline">Ekspor Excel</span>
              <span class="sm:hidden">Excel</span>
            </a>

            <!-- Simpan Button -->
            <button
              type="button"
              @click="saveReport()"
              :disabled="isSaving"
              class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-[#00A99D] hover:bg-[#008f85] active:scale-95 disabled:opacity-50 transition-all shadow-sm cursor-pointer"
            >
              <Save v-if="!isSaving" class="w-4 h-4" />
              <span v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              <span>{{ isSaving ? 'Menyimpan...' : 'Simpan Data' }}</span>
              <span class="hidden md:inline-block text-[10px] bg-white/20 px-1.5 py-0.2 rounded font-mono font-normal">Ctrl+S</span>
            </button>
          </div>
        </div>

        <!-- 12-Month Pill Switcher -->
        <div class="mt-5 pt-4 border-t border-slate-200/80 dark:border-slate-800">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
              <Calendar class="w-3.5 h-3.5 text-[#00A99D]" />
              <span>Bulan Pelaporan ({{ selectedYear }})</span>
            </span>
            <div class="flex items-center gap-1">
              <button
                type="button"
                @click="prevMonth"
                class="p-1 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                title="Bulan Sebelumnya"
              >
                <ChevronLeft class="w-4 h-4" />
              </button>
              <span class="text-xs font-bold text-[#005851] dark:text-[#00A99D] min-w-[70px] text-center">
                {{ monthsList.find(m => m.id === activeMonth)?.label }} {{ selectedYear }}
              </span>
              <button
                type="button"
                @click="nextMonth"
                class="p-1 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                title="Bulan Berikutnya"
              >
                <ChevronRight class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Monthly Pills Grid -->
          <div class="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-12 gap-1.5">
            <button
              v-for="m in monthsList"
              :key="m.id"
              type="button"
              @click="switchMonth(m.id)"
              class="relative flex flex-col items-center justify-center py-2 px-1 rounded-xl text-xs font-semibold transition-all border cursor-pointer select-none"
              :class="[
                activeMonth === m.id
                  ? 'bg-[#00A99D] text-white border-[#00A99D] shadow-sm font-bold scale-[1.02]'
                  : 'bg-slate-50 dark:bg-[#161c28] text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800',
              ]"
            >
              <span class="text-[11px] sm:text-xs tracking-tight">{{ m.label }}</span>
              <div class="flex items-center gap-1 mt-0.5">
                <span
                  class="w-1.5 h-1.5 rounded-full"
                  :class="[
                    monthStatuses[m.id]?.hasData
                      ? (activeMonth === m.id ? 'bg-white' : 'bg-emerald-500')
                      : (activeMonth === m.id ? 'bg-white/40' : 'bg-slate-300 dark:bg-slate-600')
                  ]"
                ></span>
                <span
                  v-if="monthStatuses[m.id]?.hasData"
                  class="text-[9px] font-mono leading-none"
                  :class="activeMonth === m.id ? 'text-white/90' : 'text-slate-500 dark:text-slate-400'"
                >
                  {{ monthStatuses[m.id].filledCount }}
                </span>
              </div>
            </button>
          </div>
        </div>

        <!-- Progress & Status Mini Strip -->
        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
          <div class="flex items-center gap-3">
            <div class="flex items-center gap-1.5">
              <CheckCircle2 class="w-4 h-4 text-emerald-600" />
              <span>Kelengkapan Bulan Ini: <strong class="text-slate-800 dark:text-white">{{ overallStats.filledInputs }} / {{ overallStats.totalInputs }}</strong> ({{ overallStats.percent }}%)</span>
            </div>
            <div class="hidden sm:flex w-24 h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
              <div class="bg-[#00A99D] h-full rounded-full transition-all duration-300" :style="{ width: `${overallStats.percent}%` }"></div>
            </div>
          </div>

          <div class="flex items-center gap-3 text-[11px]">
            <span v-if="activeMonthMeta.updater" class="flex items-center gap-1">
              <UserCheck class="w-3.5 h-3.5 text-slate-400" />
              <span>Petugas: <strong class="text-slate-700 dark:text-slate-300">{{ activeMonthMeta.updater }}</strong></span>
            </span>
            <span class="text-slate-400 font-mono">
              Wilker: <strong>{{ selectedWilker }}</strong>
            </span>
          </div>
        </div>
      </div>

      <!-- Navigation Tabs: KSE, UKP, PRL & Filters -->
      <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-2xl p-2 sm:p-3 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
          <!-- Section Switcher Buttons -->
          <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
            <button
              v-for="sec in sections"
              :key="sec.section_code"
              type="button"
              @click="activeSection = sec.section_code"
              class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap cursor-pointer"
              :class="[
                activeSection === sec.section_code
                  ? 'bg-[#005851] text-white shadow-xs'
                  : 'bg-slate-100 dark:bg-[#1a2130] text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800'
              ]"
            >
              <span>{{ sec.section_code }}</span>
              <span class="hidden sm:inline font-medium text-[11px] opacity-80">
                &bull; {{ sec.section_title }}
              </span>
              <span
                class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold"
                :class="[
                  activeSection === sec.section_code
                    ? 'bg-white/25 text-white'
                    : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300'
                ]"
              >
                {{ sectionStats[sec.section_code]?.filledInputs || 0 }}/{{ sectionStats[sec.section_code]?.totalInputs || 0 }}
              </span>
            </button>
          </div>

          <!-- Quick Search & Type Filter -->
          <div class="flex items-center gap-2">
            <!-- Search -->
            <div class="relative flex-1 sm:w-64">
              <Search class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari indikator / no..."
                class="w-full pl-8 pr-3 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-[#1a2130] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-[#00A99D]"
              />
            </div>

            <!-- Filter Type -->
            <select
              v-model="filterType"
              class="px-2.5 py-1.5 rounded-xl text-xs font-medium bg-slate-50 dark:bg-[#1a2130] border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer"
              title="Filter Baris"
            >
              <option value="all">Semua Baris</option>
              <option value="input">Hanya Input</option>
              <option value="summary">Hanya Total (Summary)</option>
            </select>

            <!-- Reset Active Month Data -->
            <button
              type="button"
              @click="resetActiveMonth"
              class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-transparent hover:border-rose-200 transition-colors cursor-pointer"
              title="Kosongkan Isian Bulan Ini"
            >
              <RotateCcw class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Section Title Header Badge -->
      <div class="flex items-center justify-between px-1">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-[#00A99D]"></span>
          <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-tight">
            {{ currentSectionObj.section_code }} &mdash; {{ currentSectionObj.section_title }}
          </h2>
          <span class="text-xs text-slate-400 font-mono">({{ filteredItems.length }} baris)</span>
        </div>
        <div class="text-xs text-slate-500 font-mono">
          Periode: <strong class="text-[#005851] dark:text-[#00A99D]">{{ monthsList.find(m => m.id === activeMonth)?.label }} {{ selectedYear }}</strong>
        </div>
      </div>

      <!-- Main Form Data Table -->
      <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-2xl sm:rounded-3xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full min-w-[620px] text-left text-xs border-collapse">
            <!-- Table Header -->
            <thead>
              <tr class="bg-slate-100/90 dark:bg-[#161c28] text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200 dark:border-slate-800">
                <th class="py-3 px-3 sm:px-4 w-12 text-center">Row</th>
                <th class="py-3 px-2 sm:px-3 w-16 text-center">No</th>
                <th class="py-3 px-3 sm:px-4 min-w-[260px] sm:min-w-[420px]">
                  Uraian Kegiatan / Indikator
                </th>
                <th class="py-3 px-2 sm:px-3 w-20 text-center">Satuan</th>
                <th class="py-3 px-2 sm:px-3 w-24 text-center">Tipe</th>
                <th class="py-3 px-3 sm:px-5 w-44 sm:w-52 text-right bg-slate-200/50 dark:bg-[#1a2130]">
                  Nilai ({{ monthsList.find(m => m.id === activeMonth)?.label }})
                </th>
              </tr>
            </thead>

            <!-- Table Body -->
            <tbody class="divide-y divide-slate-100 dark:divide-[#1f2633]">
              <template v-for="item in filteredItems" :key="item.excel_row">

                <!-- 1. HEADER ROW (Category Divider) -->
                <tr
                  v-if="item.type === 'header'"
                  class="bg-slate-50/80 dark:bg-[#161c28]/60 font-bold text-slate-800 dark:text-slate-100 border-t-2 border-slate-200 dark:border-slate-700"
                >
                  <td class="py-2.5 px-3 sm:px-4 text-center font-mono text-[10px] text-slate-400">
                    {{ item.excel_row }}
                  </td>
                  <td class="py-2.5 px-2 sm:px-3 text-center font-mono text-xs text-[#005851] dark:text-[#00A99D]">
                    {{ item.no }}
                  </td>
                  <td
                    colspan="4"
                    class="py-2.5 px-3 sm:px-4 uppercase tracking-wide text-xs text-slate-900 dark:text-white"
                    :style="{ paddingLeft: `${(item.level || 0) * 16 + 12}px` }"
                  >
                    <div class="flex items-center gap-2">
                      <span class="w-1.5 h-1.5 rounded-full bg-[#00A99D]"></span>
                      <span>{{ item.label }}</span>
                    </div>
                  </td>
                </tr>

                <!-- 2. SUMMARY ROW (Auto Calculated Total) -->
                <tr
                  v-else-if="item.type === 'summary'"
                  class="bg-[#00A99D]/5 dark:bg-[#00A99D]/10 hover:bg-[#00A99D]/10 transition-colors border-l-4 border-l-[#00A99D]"
                >
                  <!-- Excel Row -->
                  <td class="py-2.5 px-3 sm:px-4 text-center font-mono text-[10px] text-slate-400">
                    {{ item.excel_row }}
                  </td>

                  <!-- Item No -->
                  <td class="py-2.5 px-2 sm:px-3 text-center font-mono font-bold text-xs text-[#005851] dark:text-[#00A99D]">
                    {{ item.no }}
                  </td>

                  <!-- Label with Indentation -->
                  <td
                    class="py-2.5 px-3 sm:px-4 text-slate-900 dark:text-white font-semibold"
                    :style="{ paddingLeft: `${(item.level || 0) * 18 + 12}px` }"
                  >
                    <div class="flex items-center gap-1.5 flex-wrap">
                      <span>{{ item.label }}</span>
                      <span class="text-[9px] font-mono text-[#005851] dark:text-[#00A99D] bg-white/70 dark:bg-black/30 px-1 py-0.2 rounded border border-[#00A99D]/20">
                        {{ item.code }}
                      </span>
                    </div>
                  </td>

                  <!-- Unit -->
                  <td class="py-2.5 px-2 sm:px-3 text-center text-slate-500 font-mono text-[11px]">
                    {{ item.unit || '-' }}
                  </td>

                  <!-- Tipe Badge -->
                  <td class="py-2.5 px-2 sm:px-3 text-center">
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold font-mono px-2 py-0.5 rounded-md bg-[#00A99D]/15 text-[#005851] dark:text-[#00A99D]">
                      <Calculator class="w-3 h-3" />
                      <span>Total</span>
                    </span>
                  </td>

                  <!-- Summary Value Display (Auto-calculated) -->
                  <td class="py-2.5 px-3 sm:px-5 text-right font-mono font-extrabold text-sm text-[#005851] dark:text-[#00A99D] bg-[#00A99D]/5 dark:bg-[#00A99D]/15">
                    <div class="inline-flex items-center justify-end gap-1.5 w-full">
                      <span>{{ getValue(item.excel_row, 'summary') }}</span>
                      <span class="text-[11px] font-mono text-slate-400 w-8 text-left truncate">
                        {{ item.unit || '' }}
                      </span>
                    </div>
                  </td>
                </tr>

                <!-- 3. INPUT ROW (Editable by User) -->
                <tr
                  v-else
                  class="hover:bg-slate-50 dark:hover:bg-[#161c28]/40 transition-colors group"
                >
                  <!-- Excel Row -->
                  <td class="py-2 px-3 sm:px-4 text-center font-mono text-[10px] text-slate-400 group-hover:text-slate-600">
                    {{ item.excel_row }}
                  </td>

                  <!-- Item No -->
                  <td class="py-2 px-2 sm:px-3 text-center font-mono text-xs text-slate-600 dark:text-slate-400">
                    {{ item.no }}
                  </td>

                  <!-- Label with Indentation -->
                  <td
                    class="py-2 px-3 sm:px-4 text-slate-700 dark:text-slate-300"
                    :style="{ paddingLeft: `${(item.level || 0) * 18 + 12}px` }"
                  >
                    <div class="flex items-center gap-1.5 flex-wrap">
                      <span>{{ item.label }}</span>
                      <span class="text-[9px] font-mono text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity">
                        ({{ item.code }})
                      </span>
                    </div>
                  </td>

                  <!-- Unit Badge -->
                  <td class="py-2 px-2 sm:px-3 text-center">
                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-mono font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                      {{ item.unit || '-' }}
                    </span>
                  </td>

                  <!-- Tipe Badge -->
                  <td class="py-2 px-2 sm:px-3 text-center">
                    <span class="text-[10px] font-mono text-slate-400">
                      {{ item.data_type === 'decimal' ? 'Desimal' : 'Angka' }}
                    </span>
                  </td>

                  <!-- Value Input Field -->
                  <td class="py-1.5 px-3 sm:px-5 text-right bg-slate-50/50 dark:bg-[#161c28]/30">
                    <div class="inline-flex items-center justify-end gap-1.5 w-full">
                      <input
                        type="number"
                        :step="item.data_type === 'decimal' ? '0.01' : '1'"
                        min="0"
                        placeholder="0"
                        :value="getValue(item.excel_row, 'input')"
                        @input="onInputChange(item.excel_row, $event.target.value, item.data_type)"
                        class="w-24 sm:w-28 py-1 px-2.5 text-right font-mono text-xs font-semibold rounded-lg bg-white dark:bg-[#1e2533] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-300 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-[#00A99D] focus:border-transparent transition-all shadow-2xs"
                      />
                      <span class="text-[11px] font-mono text-slate-400 w-8 text-left truncate">
                        {{ item.unit || '' }}
                      </span>
                    </div>
                  </td>
                </tr>

              </template>

              <!-- Empty Filter Results -->
              <tr v-if="filteredItems.length === 0">
                <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                  <div class="max-w-xs mx-auto space-y-2">
                    <Info class="w-8 h-8 mx-auto text-slate-300" />
                    <p class="font-medium text-slate-600 dark:text-slate-300">Tidak ada baris data yang cocok.</p>
                    <p class="text-[11px] text-slate-400">Coba ubah kata kunci pencarian atau reset filter tipe baris.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Table Footer / Quick Action Bar -->
        <div class="bg-slate-50 dark:bg-[#161c28] border-t border-slate-200 dark:border-slate-800 p-3 sm:p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
          <div class="text-slate-500 font-mono text-[11px] flex items-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Total {{ filteredItems.length }} baris indikator dalam seksi {{ activeSection }}</span>
          </div>

          <div class="flex items-center gap-2">
            <!-- Reset Button -->
            <button
              type="button"
              @click="resetActiveMonth"
              class="px-3 py-1.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 font-semibold text-xs transition-colors cursor-pointer"
            >
              Kosongkan Isian Bulan Ini
            </button>

            <!-- Simpan Button Sticky Footer -->
            <button
              type="button"
              @click="saveReport()"
              :disabled="isSaving"
              class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl text-xs font-bold text-white bg-[#00A99D] hover:bg-[#008f85] disabled:opacity-50 transition-all shadow-sm cursor-pointer"
            >
              <Save v-if="!isSaving" class="w-3.5 h-3.5" />
              <span v-else class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              <span>Simpan Data Laporan</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Import Excel Modal Dialog -->
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div
          v-if="showImportModal"
          class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs overflow-y-auto"
          @click.self="closeImportModal"
        >
          <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-3xl shadow-2xl max-w-xl w-full p-5 sm:p-6 space-y-4 my-8">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-3.5">
              <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#00A99D]/10 text-[#005851] dark:text-[#00A99D] mb-1">
                  <FileSpreadsheet class="w-3.5 h-3.5" />
                  <span>IMPORT DOKUMEN EXCEL</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                  Import Data SINKARKES BKK Bandung
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                  Unggah file Excel laporan SINKARKES untuk mengisi data bulanan (Jan-Des) secara otomatis.
                </p>
              </div>
              <button
                type="button"
                @click="closeImportModal"
                class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
              >
                <X class="w-4 h-4" />
              </button>
            </div>

            <!-- Parameters Grid (Year & Wilker Target) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <!-- Tahun -->
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Tahun Laporan
                </label>
                <select
                  v-model.number="importYear"
                  class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-[#1a2130] border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#00A99D]"
                >
                  <option v-for="y in availableYears" :key="y" :value="y">
                    Tahun {{ y }}
                  </option>
                </select>
              </div>

              <!-- Target Wilayah Kerja -->
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Target Wilayah Kerja
                </label>
                <select
                  v-model="importWilker"
                  class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-[#1a2130] border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#00A99D]"
                >
                  <option value="all">Semua Sheet / Wilker dalam File</option>
                  <option v-for="w in wilkerOptions" :key="w" :value="w">
                    Hanya {{ w }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Drag & Drop Zone -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Pilih Berkas Excel (.xlsx, .xls)
              </label>
              
              <input
                ref="fileInputRef"
                type="file"
                accept=".xlsx, .xls"
                @change="onFileSelected"
                class="hidden"
              />

              <div
                @dragover="onDragOver"
                @dragleave="onDragLeave"
                @drop="onDropFile"
                @click="triggerFileInput"
                class="border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all flex flex-col items-center justify-center gap-2"
                :class="[
                  isDragging
                    ? 'border-[#00A99D] bg-[#00A99D]/5 scale-[1.01]'
                    : (importFile ? 'border-emerald-400 bg-emerald-50/40 dark:bg-emerald-950/20' : 'border-slate-200 dark:border-slate-700 hover:border-[#00A99D]/60 hover:bg-slate-50 dark:hover:bg-[#161c28]')
                ]"
              >
                <div v-if="!importFile" class="flex flex-col items-center gap-2">
                  <div class="w-10 h-10 rounded-2xl bg-[#00A99D]/10 text-[#00A99D] flex items-center justify-center">
                    <Upload class="w-5 h-5" />
                  </div>
                  <div>
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200">
                      Klik atau seret file spreadsheet Excel ke sini
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                      Format didukung: .xlsx atau .xls (Ukuran maks: 30MB)
                    </p>
                  </div>
                </div>

                <div v-else class="flex items-center justify-between w-full max-w-sm bg-white dark:bg-[#1a2130] p-2.5 rounded-xl border border-emerald-300 dark:border-emerald-800 shadow-2xs" @click.stop>
                  <div class="flex items-center gap-2.5 min-w-0">
                    <FileSpreadsheet class="w-6 h-6 text-emerald-600 shrink-0" />
                    <div class="text-left min-w-0">
                      <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">
                        {{ importFile.name }}
                      </p>
                      <p class="text-[10px] text-slate-400 font-mono">
                        {{ (importFile.size / 1024).toFixed(1) }} KB
                      </p>
                    </div>
                  </div>
                  <button
                    type="button"
                    @click="clearImportFile"
                    class="p-1 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-50 transition-colors"
                  >
                    <X class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Overwrite Checkbox -->
            <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
              <input
                id="overwrite-toggle"
                v-model="importOverwrite"
                type="checkbox"
                class="w-4 h-4 rounded text-[#00A99D] focus:ring-[#00A99D] border-slate-300"
              />
              <label for="overwrite-toggle" class="cursor-pointer select-none">
                Timpa data yang sudah ada dengan angka dari file Excel
              </label>
            </div>

            <!-- Sheet Recognition Guide / Pill List -->
            <div class="bg-slate-50 dark:bg-[#161c28] rounded-xl p-3 border border-slate-200/80 dark:border-slate-800 text-[11px] space-y-1.5">
              <span class="font-bold text-slate-700 dark:text-slate-300 block">
                Sheet Excel yang Dikenali Otomatis:
              </span>
              <div class="flex flex-wrap gap-1.5">
                <span class="px-2 py-0.5 rounded-md bg-white dark:bg-black/30 border border-slate-200 dark:border-slate-700 font-mono text-[10px] text-slate-600 dark:text-slate-400">HUSEIN BANDUNG</span>
                <span class="px-2 py-0.5 rounded-md bg-white dark:bg-black/30 border border-slate-200 dark:border-slate-700 font-mono text-[10px] text-slate-600 dark:text-slate-400">WILKER KERTAJATI</span>
                <span class="px-2 py-0.5 rounded-md bg-white dark:bg-black/30 border border-slate-200 dark:border-slate-700 font-mono text-[10px] text-slate-600 dark:text-slate-400">WILKER CIREBON</span>
                <span class="px-2 py-0.5 rounded-md bg-white dark:bg-black/30 border border-slate-200 dark:border-slate-700 font-mono text-[10px] text-slate-600 dark:text-slate-400">WILKER INDRAMAYU</span>
                <span class="px-2 py-0.5 rounded-md bg-white dark:bg-black/30 border border-slate-200 dark:border-slate-700 font-mono text-[10px] text-slate-600 dark:text-slate-400">PATIMBAN</span>
                <span class="px-2 py-0.5 rounded-md bg-white dark:bg-black/30 border border-slate-200 dark:border-slate-700 font-mono text-[10px] text-slate-600 dark:text-slate-400">WILKER PEL. RATU</span>
              </div>
              <p class="text-[10px] text-slate-400">
                Kolom bulan: G (Jan) s/d R (Des) &bull; Baris data: 7 s/d 344 (KSE, UKP, PRL).
              </p>
            </div>

            <!-- Results Card (if completed) -->
            <div v-if="importResult" class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl space-y-2 text-xs">
              <div class="flex items-center gap-2 font-bold text-emerald-800 dark:text-emerald-300">
                <CheckCircle2 class="w-4 h-4 text-emerald-600" />
                <span>Hasil Import Selesai</span>
              </div>
              <div class="grid grid-cols-3 gap-2 text-center text-[11px]">
                <div class="bg-white/80 dark:bg-black/40 p-2 rounded-lg">
                  <span class="text-slate-400 block text-[10px]">Wilker Diproses</span>
                  <strong class="text-slate-800 dark:text-white text-sm font-mono">{{ importResult.sheets_processed }}</strong>
                </div>
                <div class="bg-white/80 dark:bg-black/40 p-2 rounded-lg">
                  <span class="text-slate-400 block text-[10px]">Bulan Tersimpan</span>
                  <strong class="text-slate-800 dark:text-white text-sm font-mono">{{ importResult.records_saved }}</strong>
                </div>
                <div class="bg-white/80 dark:bg-black/40 p-2 rounded-lg">
                  <span class="text-slate-400 block text-[10px]">Nilai Diimpor</span>
                  <strong class="text-emerald-700 dark:text-emerald-400 text-sm font-mono">{{ importResult.cells_imported }}</strong>
                </div>
              </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-800">
              <button
                type="button"
                @click="closeImportModal"
                :disabled="isImporting"
                class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
              >
                {{ importResult ? 'Tutup' : 'Batal' }}
              </button>

              <button
                type="button"
                @click="submitImport"
                :disabled="!importFile || isImporting"
                class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl text-xs font-bold text-white bg-[#00A99D] hover:bg-[#008f85] disabled:opacity-50 transition-all shadow-sm cursor-pointer"
              >
                <Upload v-if="!isImporting" class="w-4 h-4" />
                <span v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                <span>{{ isImporting ? 'Sedang Memproses...' : 'Mulai Import Data' }}</span>
              </button>
            </div>

          </div>
        </div>
      </transition>

    </main>
  </HybridNavbar>
</template>

<style scoped>
/* Chrome, Safari, Edge, Opera: Hide number spinners for ultra compact look */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}
</style>
