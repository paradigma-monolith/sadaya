<script setup>
import ClinicalModal from '../../../Components/ClinicalModal.vue';
import { FileText, Printer, X } from 'lucide-vue-next';
import { formatDate, getRiskBadgeClass } from '../utils';

defineProps({
  open: { type: Boolean, default: false },
  item: { type: Object, default: null },
  detailType: { type: String, default: '' },
});

const emit = defineEmits(['close']);
</script>

<template>
  <ClinicalModal
    :open="Boolean(open && item)"
    title="Rincian Data Inspeksi Sanitasi"
    max-width="max-w-2xl"
    @close="emit('close')"
  >
    <div v-if="item" class="relative w-full bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
      <div class="px-6 py-4 bg-gradient-to-r from-[#005851] to-[#00A99D] text-white flex items-center justify-between">
        <div class="flex items-center gap-2">
          <FileText class="w-5 h-5" />
          <h3 class="text-sm font-bold">Rincian Data Inspeksi Sanitasi</h3>
        </div>
        <button
          type="button"
          @click="emit('close')"
          aria-label="Tutup rincian inspeksi"
          title="Tutup"
          class="p-1 rounded-lg hover:bg-white/20 transition cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="p-6 max-h-[75vh] overflow-y-auto space-y-4 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
          <div>
            <span class="text-slate-400 block text-[11px]">Nama Sarana:</span>
            <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">
              {{ item.nama_tpp || item.nama_sab || item.nama_gedung }}
            </span>
          </div>
          <div>
            <span class="text-slate-400 block text-[11px]">Wilayah Kerja:</span>
            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ item.wilker }}</span>
          </div>
          <div>
            <span class="text-slate-400 block text-[11px]">Tanggal Inspeksi:</span>
            <span class="font-mono text-slate-700 dark:text-slate-300">{{ formatDate(item.tanggal_inspeksi) }}</span>
          </div>
          <div>
            <span class="text-slate-400 block text-[11px]">Petugas Pemeriksa:</span>
            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ item.nama_pemeriksa || '-' }}</span>
          </div>
        </div>

        <div class="p-4 rounded-2xl border flex items-center justify-between gap-2" :class="getRiskBadgeClass(item.tingkat_risiko)">
          <div>
            <span class="font-bold text-sm">
              Skor Hasil: {{ item.hasil ?? item.total }}
            </span>
            <p class="text-[11px] opacity-80 mt-0.5">Tingkat Risiko: {{ item.tingkat_risiko }}</p>
          </div>
          <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border bg-white/50 dark:bg-black/20">
            {{ item.tingkat_risiko }}
          </span>
        </div>

        <div class="space-y-3">
          <div v-if="item.kesimpulan">
            <span class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Kesimpulan:</span>
            <p class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">
              {{ item.kesimpulan }}
            </p>
          </div>

          <div v-if="item.saran_rencana_tindakan">
            <span class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Saran / Rencana Tindakan:</span>
            <p class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">
              {{ item.saran_rencana_tindakan || '-' }}
            </p>
          </div>
        </div>
      </div>

      <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-2 flex-wrap">
        <a
          :href="`/sanitasi-lingkungan/inspeksi/${detailType}/${item.id}/pdf`"
          target="_blank"
          class="px-4 py-2 text-xs font-bold rounded-xl bg-[#005851] text-white hover:bg-[#004842] transition shadow-xs flex items-center gap-2 cursor-pointer"
        >
          <Printer class="w-4 h-4" />
          <span>Cetak PDF Lembar Pengawasan</span>
        </a>
        <button
          type="button"
          @click="emit('close')"
          class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-600 transition cursor-pointer"
        >
          Tutup
        </button>
      </div>
    </div>
  </ClinicalModal>
</template>
