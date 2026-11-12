<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useDate } from '../../../composables/useDate';
import {
  formatCurrency,
  formatNumber,
  getRiskBadgeClass,
  getStatusBadgeClass,
  getQuestionDisplay,
} from '../utils/formatters';
import {
  Ship,
  X,
  FileCheck,
  FileText,
  ShieldCheck,
  CheckCircle,
} from 'lucide-vue-next';

const props = defineProps({
  showDetailModal: {
    type: Boolean,
    default: false,
  },
  activeItem: {
    type: Object,
    default: null,
  },
  quarantineQuestions: {
    type: Object,
    default: () => ({}),
  },
  showVerifyModal: {
    type: Boolean,
    default: false,
  },
  verifyTarget: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['close-detail', 'open-verify', 'close-verify']);

const { formatDate } = useDate();

const verifyDecision = ref('SANDAR');
const verifyNotes = ref('');
const isSubmittingVerify = ref(false);

watch(
  () => props.verifyTarget,
  (target) => {
    if (target) {
      verifyDecision.value = target.hasil_status || 'SANDAR';
      verifyNotes.value = target.catatan_verifikasi || '';
    }
  },
  { immediate: true }
);

const submitVerify = () => {
  if (!props.verifyTarget) return;
  isSubmittingVerify.value = true;
  router.put(
    `/ship-surveillance/${props.verifyTarget.id}/verify`,
    {
      hasil_status: verifyDecision.value,
      catatan_verifikasi: verifyNotes.value,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        emit('close-verify');
      },
      onFinish: () => {
        isSubmittingVerify.value = false;
      },
    }
  );
};
</script>

<template>
  <!-- ============================================== -->
  <!-- MODAL DETAIL PEMERIKSAAN KARANTINA (TANGKAP)   -->
  <!-- ============================================== -->
  <div
    v-if="showDetailModal && activeItem"
    class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
  >
    <div class="bg-white rounded-xl max-w-3xl w-full max-h-[90vh] flex flex-col shadow-xl border border-slate-200 overflow-hidden">
      <!-- Modal Header -->
      <div class="px-4 py-3.5 border-b border-emerald-950/20 flex items-center justify-between bg-[#004640] text-white">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-white/10 border border-white/15 flex items-center justify-center">
            <Ship class="w-4 h-4 text-emerald-300" />
          </div>
          <div>
            <h3 class="font-bold text-sm leading-tight">{{ activeItem.nama_kapal }}</h3>
            <p class="text-[11px] text-emerald-100/80 font-mono">No Registrasi: {{ activeItem.registration_no }}</p>
          </div>
        </div>
        <button
          type="button"
          @click="emit('close-detail')"
          class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition cursor-pointer"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-5 overflow-y-auto space-y-4 text-xs text-slate-700">
        <!-- Vessel Profile Summary -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-3.5 rounded-lg border border-slate-200">
          <div>
            <div class="text-slate-500 font-medium text-[11px]">Pelabuhan Asal</div>
            <div class="font-bold text-slate-900 mt-0.5">{{ activeItem.datang_dari }}</div>
          </div>
          <div>
            <div class="text-slate-500 font-medium text-[11px]">Pelabuhan Tujuan</div>
            <div class="font-bold text-slate-900 mt-0.5">{{ activeItem.pelabuhan }}</div>
          </div>
          <div>
            <div class="text-slate-500 font-medium text-[11px]">Negara Asal / Bendera</div>
            <div class="font-bold text-slate-900 mt-0.5">{{ activeItem.origin_country || 'N/A' }}</div>
          </div>
          <div>
            <div class="text-slate-500 font-medium text-[11px]">Gross Tonnage</div>
            <div class="font-bold text-slate-900 mt-0.5 clinical-num">{{ formatNumber(activeItem.gross_tonnage) }} GT</div>
          </div>
          <div>
            <div class="text-slate-500 font-medium text-[11px]">Jumlah ABK</div>
            <div class="font-bold text-slate-900 mt-0.5 clinical-num">{{ activeItem.jml_crew }} Orang</div>
          </div>
          <div>
            <div class="text-slate-500 font-medium text-[11px]">Kategori Pelayaran</div>
            <div class="font-bold text-slate-900 mt-0.5">{{ activeItem.kategori }}</div>
          </div>
          <div>
            <div class="text-slate-500 font-medium text-[11px]">Skor &amp; Risiko</div>
            <div class="font-bold mt-0.5">
              <span :class="['px-2 py-0.5 rounded text-[10px] font-bold', getRiskBadgeClass(activeItem.kresiko)]">
                {{ activeItem.kresiko }} ({{ activeItem.skoring }})
              </span>
            </div>
          </div>
          <div>
            <div class="text-slate-500 font-medium text-[11px]">Estimasi PNBP</div>
            <div class="font-bold text-emerald-600 mt-0.5 font-mono clinical-num">{{ formatCurrency(activeItem.estimasi_pnbp) }}</div>
          </div>
          <div v-if="activeItem.negara_singgah_14_hari" class="col-span-2 sm:col-span-4 pt-2 border-t border-slate-200">
            <div class="text-slate-500 font-medium text-[11px]">Negara Singgah (14 Hari Terakhir)</div>
            <div class="font-bold text-slate-900 mt-0.5">{{ activeItem.negara_singgah_14_hari }}</div>
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
              v-for="(qText, qNo) in quarantineQuestions"
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
          <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
            <div class="font-bold text-slate-900 dark:text-white mb-2 flex items-center gap-1.5">
              <FileText class="w-4 h-4 text-[#00A99D]" />
              Dokumen Pernyataan Nahkoda
            </div>
            <div v-if="activeItem.surat_pernyataan_nahkoda" class="text-slate-600 dark:text-slate-400 flex items-center gap-2">
              <span class="truncate">{{ activeItem.surat_pernyataan_nahkoda }}</span>
              <span class="text-[10px] text-emerald-600 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded shrink-0">Tersedia</span>
            </div>
            <div v-else class="text-slate-400 italic">
              Tidak ada dokumen (Kapal Dalam Negeri)
            </div>
          </div>

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
      <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 flex flex-wrap justify-between items-center gap-3">
        <div class="flex items-center gap-2">
          <span class="text-xs font-semibold text-slate-500">Keputusan Saat Ini:</span>
          <span :class="['px-2.5 py-0.5 rounded-full text-xs font-bold', getStatusBadgeClass(activeItem.hasil_status)]">
            {{ activeItem.hasil_status }}
          </span>
        </div>
        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="emit('close-detail')"
            class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition cursor-pointer"
          >
            Tutup
          </button>
          <button
            type="button"
            @click="emit('open-verify', activeItem); emit('close-detail')"
            class="px-4 py-2 text-xs font-semibold text-white bg-gradient-to-r from-[#005851] to-[#00A99D] rounded-xl hover:opacity-95 shadow transition cursor-pointer"
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
    <div class="bg-white rounded-xl max-w-md w-full shadow-xl border border-slate-200 overflow-hidden">
      <div class="px-4 py-3.5 border-b border-emerald-950/20 bg-[#004640] text-white flex items-center justify-between">
        <div class="flex items-center gap-2">
          <ShieldCheck class="w-4 h-4 text-emerald-300" />
          <h3 class="font-bold text-sm">Verifikasi Kedatangan Kapal</h3>
        </div>
        <button
          type="button"
          @click="emit('close-verify')"
          class="text-white/80 hover:text-white cursor-pointer"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <form @submit.prevent="submitVerify" class="p-4 space-y-3.5 text-xs">
        <div>
          <div class="text-slate-500 font-medium mb-0.5 text-[11px]">Nama Kapal:</div>
          <div class="font-bold text-sm text-slate-900">{{ verifyTarget.nama_kapal }}</div>
          <div class="text-[11px] text-slate-500">{{ verifyTarget.pelabuhan }} &bull; Asal: {{ verifyTarget.datang_dari }}</div>
        </div>

        <div>
          <label class="block font-semibold text-slate-700 mb-1.5">
            Keputusan Kekarantinaan Kesehatan: <span class="text-rose-500">*</span>
          </label>
          <div class="grid grid-cols-2 gap-2">
            <label
              :class="[
                'flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition',
                verifyDecision === 'SANDAR'
                  ? 'border-[#00A99D] bg-teal-50 text-[#005851] font-bold'
                  : 'border-slate-200 hover:bg-slate-50'
              ]"
            >
              <input type="radio" v-model="verifyDecision" value="SANDAR" class="text-[#005851] focus:ring-[#00A99D]" />
              <span>SANDAR (Dermaga)</span>
            </label>

            <label
              :class="[
                'flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition',
                verifyDecision === 'ZONA KARANTINA'
                  ? 'border-rose-500 bg-rose-50 text-rose-800 font-bold'
                  : 'border-slate-200 hover:bg-slate-50'
              ]"
            >
              <input type="radio" v-model="verifyDecision" value="ZONA KARANTINA" class="text-rose-600 focus:ring-rose-500" />
              <span>ZONA KARANTINA</span>
            </label>
          </div>
        </div>

        <div>
          <label class="block font-semibold text-slate-700 mb-1">
            Catatan Rekomendasi / Instruksi Pengawasan:
          </label>
          <textarea
            v-model="verifyNotes"
            rows="3"
            placeholder="Contoh: Dokumen lengkap, seluruh kru sehat, izin sandar diberikan..."
            class="clinical-input w-full"
          ></textarea>
        </div>

        <div class="pt-2 flex justify-end gap-2">
          <button
            type="button"
            @click="emit('close-verify')"
            class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition cursor-pointer"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="isSubmittingVerify"
            class="px-5 py-2 rounded-xl bg-gradient-to-r from-[#005851] to-[#00A99D] font-bold text-white shadow hover:opacity-95 transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
          >
            <CheckCircle class="w-4 h-4" />
            {{ isSubmittingVerify ? 'Menyimpan...' : 'Simpan Verifikasi' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

