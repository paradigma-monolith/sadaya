<script setup>
import { ref, watch } from 'vue';
import ClinicalModal from '@/Components/ClinicalModal.vue';
import { Check, Info, Plane, ShieldCheck, X } from 'lucide-vue-next';

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  item: {
    type: Object,
    default: null,
  },
  cfg: {
    type: Object,
    required: true,
  },
  isSubmitting: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close', 'submit']);

const activeTab = ref('overview'); // 'overview' | 'p3k' | 'emergensi' | 'verify'
const statusForm = ref({
  status: 'approved',
  keterangan: '',
});

watch(
  () => [props.open, props.item?.id],
  ([isOpen]) => {
    if (isOpen && props.item) {
      statusForm.value = {
        status: props.item.status || 'submitted',
        keterangan: props.item.keterangan || '',
      };
      activeTab.value = 'overview';
    }
  },
  { immediate: true }
);

const handleClose = () => {
  emit('close');
};

const handleSubmit = () => {
  emit('submit', { ...statusForm.value });
};
</script>

<template>
  <ClinicalModal
    :open="Boolean(open && item)"
    :title="`Detail Kloter ${item?.nomor_kloter || ''}`"
    max-width="max-w-4xl"
    @close="handleClose"
  >
    <div
      v-if="item"
      class="bg-white dark:bg-slate-900 rounded-3xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden"
    >
      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-950/60">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl text-white flex items-center justify-center shadow-xs" :class="cfg.modalHeaderIcon">
            <component :is="cfg.mainIcon" class="w-5 h-5" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                {{ item.nomor_kloter }} &bull; {{ item.embarkasi }}
              </h3>
              <span class="px-2 py-0.5 rounded text-[10.5px] font-mono font-bold" :class="cfg.modalRegBadge">
                {{ item.registration_no }}
              </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Penerbangan Haji: {{ item.penerbangan_haji_h }} H / {{ item.penerbangan_haji_m }} M
            </p>
          </div>
        </div>

        <button
          type="button"
          @click="handleClose"
          aria-label="Tutup modal detail pemakaian"
          title="Tutup"
          class="p-2 rounded-xl text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800 transition-colors cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <div
        role="tablist"
        aria-label="Detail inspeksi kesehatan penerbangan"
        class="flex border-b border-slate-200 dark:border-slate-800 px-6 bg-white dark:bg-slate-900 gap-6 text-xs font-semibold overflow-x-auto"
      >
        <button
          type="button"
          role="tab"
          :aria-selected="activeTab === 'overview'"
          @click="activeTab = 'overview'"
          class="py-3 border-b-2 transition-colors cursor-pointer shrink-0"
          :class="activeTab === 'overview' ? cfg.activeModalTabClass : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'"
        >
          Informasi Kloter &amp; TTD
        </button>
        <button
          type="button"
          role="tab"
          :aria-selected="activeTab === 'p3k'"
          @click="activeTab = 'p3k'"
          class="py-3 border-b-2 transition-colors cursor-pointer flex items-center gap-1.5 shrink-0"
          :class="activeTab === 'p3k' ? cfg.activeModalTabClass : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'"
        >
          {{ cfg.modalP3kTab }}
          <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] rounded-full">
            {{ item.items_p3k ? item.items_p3k.length : 0 }}
          </span>
        </button>
        <button
          type="button"
          role="tab"
          :aria-selected="activeTab === 'emergensi'"
          @click="activeTab = 'emergensi'"
          class="py-3 border-b-2 transition-colors cursor-pointer flex items-center gap-1.5 shrink-0"
          :class="activeTab === 'emergensi' ? cfg.activeModalTabClass : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'"
        >
          {{ cfg.modalEmkTab }}
          <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] rounded-full">
            {{ item.items_emergensi ? item.items_emergensi.length : 0 }}
          </span>
        </button>
        <button
          type="button"
          role="tab"
          :aria-selected="activeTab === 'verify'"
          @click="activeTab = 'verify'"
          class="py-3 border-b-2 transition-colors cursor-pointer flex items-center gap-1 shrink-0"
          :class="activeTab === 'verify' ? cfg.activeModalTabClass : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white'"
        >
          <ShieldCheck class="w-3.5 h-3.5" />
          <span>Verifikasi Karantina</span>
        </button>
      </div>

      <div class="p-6 overflow-y-auto flex-1 space-y-6 text-xs">
        <div v-if="activeTab === 'overview'" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 space-y-2.5">
              <h4 class="font-bold text-slate-900 dark:text-white text-xs flex items-center gap-1.5">
                <Plane class="w-4 h-4" :class="cfg.iconClass" />
                <span>Data Kloter &amp; Penerbangan</span>
              </h4>
              <div class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                <div class="py-1.5 flex justify-between">
                  <span class="text-slate-500 dark:text-slate-400">Nomor Kloter:</span>
                  <strong class="text-slate-900 dark:text-white">{{ item.nomor_kloter }}</strong>
                </div>
                <div class="py-1.5 flex justify-between">
                  <span class="text-slate-500 dark:text-slate-400">Embarkasi:</span>
                  <span class="font-medium">{{ item.embarkasi }}</span>
                </div>
                <div class="py-1.5 flex justify-between">
                  <span class="text-slate-500 dark:text-slate-400">Penerbangan Haji:</span>
                  <span>{{ item.penerbangan_haji_h }} H / {{ item.penerbangan_haji_m }} M</span>
                </div>
                <div class="py-1.5 flex justify-between">
                  <span class="text-slate-500 dark:text-slate-400">Kontak Dokter/PIC:</span>
                  <span>{{ item.no_kontak || '-' }}</span>
                </div>
              </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 space-y-2.5">
              <h4 class="font-bold text-slate-900 dark:text-white text-xs flex items-center gap-1.5">
                <ShieldCheck class="w-4 h-4" :class="cfg.iconClass" />
                <span>Petugas &amp; Lokasi</span>
              </h4>
              <div class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                <div class="py-1.5 flex justify-between">
                  <span class="text-slate-500 dark:text-slate-400">Awak Kabin (Crew):</span>
                  <strong class="text-slate-900 dark:text-white">{{ item.crew_nama }}</strong>
                </div>
                <div class="py-1.5 flex justify-between">
                  <span class="text-slate-500 dark:text-slate-400">Petugas / Pelapor:</span>
                  <strong class="text-slate-900 dark:text-white">{{ item.pelapor_nama }}</strong>
                </div>
                <div class="py-1.5 flex justify-between">
                  <span class="text-slate-500 dark:text-slate-400">Lokasi Geotag:</span>
                  <span>{{ item.lokasi_geotag || '-' }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1.5">
              <h5 class="font-bold text-slate-800 dark:text-slate-200">Tanda Tangan Awak Kabin:</h5>
              <div class="p-2 border border-slate-200 rounded-xl bg-white">
                <img v-if="item.crew_ttd" :src="item.crew_ttd" alt="TTD Crew" class="h-16 object-contain mx-auto" />
                <p v-else class="text-slate-400 text-center italic py-4">Tidak ada tanda tangan</p>
              </div>
              <p class="text-[10px] text-slate-400 text-center">{{ item.crew_nama }}</p>
            </div>

            <div class="p-4 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1.5">
              <h5 class="font-bold text-slate-800 dark:text-slate-200">Tanda Tangan Petugas / Pelapor:</h5>
              <div class="p-2 border border-slate-200 rounded-xl bg-white">
                <img v-if="item.pelapor_ttd" :src="item.pelapor_ttd" alt="TTD Pelapor" class="h-16 object-contain mx-auto" />
                <p v-else class="text-slate-400 text-center italic py-4">Tidak ada tanda tangan</p>
              </div>
              <p class="text-[10px] text-slate-400 text-center">{{ item.pelapor_nama }}</p>
            </div>
          </div>

          <div class="p-4 rounded-2xl border" :class="cfg.notesBoxClass">
            <h5 class="font-bold mb-1 flex items-center gap-1.5" :class="cfg.notesTitleClass">
              <Info class="w-4 h-4" :class="cfg.iconClass" />
              <span>Catatan Pemakaian:</span>
            </h5>
            <p class="text-slate-700 dark:text-slate-300 italic">"{{ item.keterangan || 'Tidak ada catatan tambahan.' }}"</p>
          </div>
        </div>

        <div v-if="activeTab === 'p3k'" class="space-y-4">
          <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 font-bold text-[11px]">
                <tr>
                  <th class="px-4 py-2.5 w-12 text-center">No</th>
                  <th class="px-4 py-2.5">{{ cfg.modalP3kHeader }}</th>
                  <th class="px-4 py-2.5 w-24 text-center">Satuan</th>
                  <th class="px-4 py-2.5 w-24 text-center">Isi</th>
                  <th class="px-4 py-2.5 w-24 text-center">Terpakai</th>
                  <th class="px-4 py-2.5">Keterangan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="(row, idx) in item.items_p3k || []" :key="idx" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                  <td class="px-4 py-2 text-center font-mono text-slate-400">{{ idx + 1 }}</td>
                  <td class="px-4 py-2 font-medium text-slate-800 dark:text-slate-200">{{ row.nama }}</td>
                  <td class="px-4 py-2 text-center text-slate-500 dark:text-slate-400">{{ row.satuan }}</td>
                  <td class="px-4 py-2 text-center font-mono">{{ row.isi }}</td>
                  <td class="px-4 py-2 text-center font-mono font-bold" :class="parseInt(row.terpakai) > 0 ? cfg.p3kUsedClass : 'text-slate-400'">
                    {{ row.terpakai }}
                  </td>
                  <td class="px-4 py-2 text-slate-600 dark:text-slate-400">{{ row.keterangan || '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div v-if="activeTab === 'emergensi'" class="space-y-4">
          <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 font-bold text-[11px]">
                <tr>
                  <th class="px-4 py-2.5 w-12 text-center">No</th>
                  <th class="px-4 py-2.5">{{ cfg.modalEmkHeader }}</th>
                  <th class="px-4 py-2.5 w-24 text-center">Satuan</th>
                  <th class="px-4 py-2.5 w-24 text-center">Isi</th>
                  <th class="px-4 py-2.5 w-24 text-center">Terpakai</th>
                  <th class="px-4 py-2.5">Keterangan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="(row, idx) in item.items_emergensi || []" :key="idx" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                  <td class="px-4 py-2 text-center font-mono text-slate-400">{{ idx + 1 }}</td>
                  <td class="px-4 py-2 font-medium text-slate-800 dark:text-slate-200">{{ row.nama }}</td>
                  <td class="px-4 py-2 text-center text-slate-500 dark:text-slate-400">{{ row.satuan }}</td>
                  <td class="px-4 py-2 text-center font-mono">{{ row.isi }}</td>
                  <td class="px-4 py-2 text-center font-mono font-bold" :class="parseInt(row.terpakai) > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400'">
                    {{ row.terpakai }}
                  </td>
                  <td class="px-4 py-2 text-slate-600 dark:text-slate-400">{{ row.keterangan || '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div v-if="activeTab === 'verify'" class="space-y-4">
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 space-y-4">
            <h4 class="font-bold text-slate-900 dark:text-white text-sm">Verifikasi Laporan Karantina</h4>
            <div>
              <label for="fmdm-verify-status" class="block text-slate-700 dark:text-slate-300 font-semibold mb-1">Status Verifikasi:</label>
              <select
                id="fmdm-verify-status"
                v-model="statusForm.status"
                class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white font-medium focus:ring-2"
                :class="cfg.inputRing"
              >
                <option value="submitted">Submitted (Menunggu)</option>
                <option value="verified">Verified (Telah Diverifikasi)</option>
                <option value="approved">Approved (Disahkan)</option>
                <option value="rejected">Rejected (Ditolak / Perlu Perbaikan)</option>
              </select>
            </div>

            <div>
              <label for="fmdm-verify-notes" class="block text-slate-700 dark:text-slate-300 font-semibold mb-1">Catatan / Rekomendasi Resmi:</label>
              <textarea
                id="fmdm-verify-notes"
                v-model="statusForm.keterangan"
                rows="3"
                :placeholder="cfg.notesPlaceholder"
                class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white focus:ring-2"
                :class="cfg.inputRing"
              ></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 flex items-center justify-between">
        <div class="text-xs text-slate-400">
          Diverifikasi oleh: <strong class="text-slate-700 dark:text-slate-300">{{ item.verifier ? item.verifier.name : 'Belum diverifikasi' }}</strong>
        </div>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="handleClose"
            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition-colors cursor-pointer"
          >
            Tutup
          </button>

          <button
            v-if="activeTab === 'verify'"
            type="button"
            :disabled="isSubmitting"
            @click="handleSubmit"
            class="px-5 py-2 rounded-xl text-xs font-bold transition-colors shadow-xs cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1.5"
            :class="cfg.primaryBtn"
          >
            <Check class="w-4 h-4" />
            <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan Pembaruan' }}</span>
          </button>
        </div>
      </div>
    </div>
  </ClinicalModal>
</template>
