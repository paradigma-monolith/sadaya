<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import ClinicalModal from '../../../Components/ClinicalModal.vue';
import { UploadCloud, X, CheckCircle2, XCircle, RefreshCw } from 'lucide-vue-next';

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  module: {
    type: Object,
    default: null,
  },
  skipDuplicate: {
    type: Boolean,
    default: true,
  },
  truncateFirst: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close', 'uploaded']);

const uploadFile = ref(null);
const isUploading = ref(false);
const uploadResult = ref(null);

watch(
  () => [props.open, props.module?.id],
  ([isOpen]) => {
    if (isOpen) {
      uploadFile.value = null;
      uploadResult.value = null;
    }
  },
);

const closeModal = () => {
  if (isUploading.value) return;
  uploadFile.value = null;
  emit('close');
};

const handleFileSelect = (e) => {
  const file = e.target.files?.[0];
  if (file) {
    uploadFile.value = file;
  }
};

const handleExecuteUpload = async () => {
  if (!uploadFile.value || !props.module) {
    toast.warning('Pilih file backup terlebih dahulu.');
    return;
  }

  isUploading.value = true;
  uploadResult.value = null;

  try {
    const formData = new FormData();
    formData.append('module', props.module.id);
    formData.append('file', uploadFile.value);
    formData.append('skip_duplicate', props.skipDuplicate ? '1' : '0');
    formData.append('truncate_first', props.truncateFirst ? '1' : '0');

    const res = await axios.post('/database-import/import-file', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    uploadResult.value = res.data;
    if (res.data.sadaya_current) {
      emit('uploaded', res.data.sadaya_current);
    }
    router.reload({ only: ['sadaya_stats'] });
  } catch (err) {
    uploadResult.value = {
      success: false,
      message: err.response?.data?.message || err.message || 'Gagal memproses file.',
    };
  } finally {
    isUploading.value = false;
  }
};
</script>

<template>
  <ClinicalModal
    :open="Boolean(open && module)"
    :title="`Import Berkas Backup: ${module?.name || ''}`"
    max-width="max-w-lg"
    :close-on-outside="!isUploading"
    @close="closeModal"
  >
    <div v-if="module" class="bg-white dark:bg-slate-900 rounded-3xl w-full shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col">
      <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl flex items-center justify-center" :class="[module.bg_color, module.color]">
            <UploadCloud class="w-5 h-5" />
          </div>
          <div>
            <h3 class="font-bold text-sm text-slate-900 dark:text-white">
              Import Berkas Backup: {{ module.name }}
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Upload berkas dump .sql, .json, atau .csv ke tabel {{ module.target_table }}
            </p>
          </div>
        </div>
        <button
          type="button"
          @click="closeModal"
          aria-label="Tutup modal upload backup"
          title="Tutup"
          class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="p-5 space-y-4">
        <div class="p-6 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl text-center space-y-2 bg-slate-50 dark:bg-slate-800/30">
          <UploadCloud class="w-8 h-8 text-indigo-500 mx-auto" />
          <div class="text-xs font-semibold text-slate-700 dark:text-slate-200">
            Pilih file backup database Simply
          </div>
          <p class="text-[11px] text-slate-400">
            Format didukung: .sql (Dump INSERT), .json, atau .csv (Maks. 50MB)
          </p>
          <input
            type="file"
            accept=".sql,.json,.csv"
            @change="handleFileSelect"
            class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer pt-2"
          />
          <div v-if="uploadFile" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 pt-1">
            File: {{ uploadFile.name }} ({{ Math.round(uploadFile.size / 1024) }} KB)
          </div>
        </div>

        <div v-if="uploadResult">
          <div
            v-if="uploadResult.success"
            class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-300 space-y-1"
          >
            <div class="font-bold flex items-center gap-1.5">
              <CheckCircle2 class="w-4 h-4 text-emerald-600" />
              <span>Import File Berhasil!</span>
            </div>
            <p class="text-[11px]">
              {{ uploadResult.imported }} data baru diimpor, {{ uploadResult.skipped }} dilewati, durasi {{ uploadResult.duration }}s.
            </p>
          </div>
          <div
            v-else
            class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-800 dark:text-rose-200 flex items-start gap-2"
          >
            <XCircle class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" />
            <div>
              <div class="font-bold">Import Gagal</div>
              <p class="text-[11px] mt-0.5">{{ uploadResult.message }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-800/40">
        <button
          type="button"
          @click="closeModal"
          class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition cursor-pointer"
        >
          Tutup
        </button>
        <button
          type="button"
          @click="handleExecuteUpload"
          :disabled="isUploading || !uploadFile"
          class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
        >
          <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isUploading }" />
          <span>{{ isUploading ? 'Memproses Berkas...' : 'Mulai Import File' }}</span>
        </button>
      </div>
    </div>
  </ClinicalModal>
</template>
