<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import ClinicalModal from '../../../Components/ClinicalModal.vue';
import { Upload, X, Info } from 'lucide-vue-next';

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  defaultWilker: {
    type: String,
    required: true,
  },
  wilkerOptions: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['close']);

const importFile = ref(null);
const importWilker = ref(props.defaultWilker);
const isImporting = ref(false);
const importResult = ref(null);
const fileInputRef = ref(null);

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      importWilker.value = props.defaultWilker;
      importFile.value = null;
      importResult.value = null;
      if (fileInputRef.value) fileInputRef.value.value = '';
    }
  },
  { immediate: true },
);

const closeModal = () => {
  if (isImporting.value) return;
  emit('close');
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
</script>

<template>
  <ClinicalModal
    :open="open"
    title="Import Excel Rekap Harian Kapal & Pesawat"
    max-width="max-w-lg"
    overlay-class="z-50 bg-black/50 backdrop-blur-xs"
    :close-on-outside="!isImporting"
    @close="closeModal"
  >
    <div class="bg-white dark:bg-[#12161f] border border-slate-200 dark:border-slate-700 rounded-2xl w-full shadow-2xl overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Upload class="w-5 h-5 text-[#00A99D]" />
          <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Import Excel Rekap Harian Kapal &amp; Pesawat</h3>
        </div>
        <button type="button" @click="closeModal" aria-label="Tutup modal import Excel" title="Tutup" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
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
          @click="closeModal"
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
  </ClinicalModal>
</template>
