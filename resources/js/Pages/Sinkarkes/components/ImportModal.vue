<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import ClinicalModal from '../../../Components/ClinicalModal.vue';
import { toast } from 'vue-sonner';
import { FileSpreadsheet, Upload, X, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  defaultYear: {
    type: Number,
    default: 2026,
  },
  availableYears: {
    type: Array,
    default: () => [2024, 2025, 2026, 2027, 2028, 2029, 2030],
  },
  wilkerOptions: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['close']);

const importFile = ref(null);
const importYear = ref(props.defaultYear);
const importWilker = ref('all');
const importOverwrite = ref(true);
const isImporting = ref(false);
const importResult = ref(null);
const fileInputRef = ref(null);
const isDragging = ref(false);

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      importYear.value = props.defaultYear;
      importWilker.value = 'all';
      importFile.value = null;
      importResult.value = null;
      if (fileInputRef.value) fileInputRef.value.value = '';
    }
  },
  { immediate: true },
);

const closeImportModal = () => {
  if (isImporting.value) return;
  emit('close');
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
</script>

<template>
  <ClinicalModal
    :open="open"
    title="Import Data SINKARKES BKK Bandung"
    max-width="max-w-xl"
    overlay-class="z-50 bg-slate-950/50 backdrop-blur-xs"
    :close-on-outside="!isImporting"
    @close="closeImportModal"
  >
    <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-[#232a36] rounded-3xl shadow-2xl w-full p-5 sm:p-6 space-y-4">
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
          aria-label="Tutup modal import SINKARKES"
          title="Tutup"
          class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
              aria-label="Hapus file yang dipilih"
              title="Hapus file"
              class="p-1 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-50 transition-colors"
            >
              <X class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

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
  </ClinicalModal>
</template>
