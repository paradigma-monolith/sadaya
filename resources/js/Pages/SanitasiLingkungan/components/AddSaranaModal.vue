<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import ClinicalModal from '../../../Components/ClinicalModal.vue';
import { useUnsavedGuard } from '@/composables/useUnsavedGuard';
import { Plus, Check, X } from 'lucide-vue-next';

const props = defineProps({
  open: { type: Boolean, default: false },
  initialType: { type: String, default: 'tpp' },
  wilkerOptions: { type: Array, default: () => [] },
});

const emit = defineEmits(['close', 'saved']);

const addSaranaType = ref(props.initialType || 'tpp');
const isSubmitting = ref(false);
const hasEdited = ref(false);

const { allowNextNavigation } = useUnsavedGuard(() => props.open && hasEdited.value && !isSubmitting.value);

const buildDefaultForm = (type) => ({
  wilker: props.wilkerOptions[0] || 'Wilayah Kerja Patimban',
  nama: '',
  penanggung_jawab: '',
  no_hp: '',
  jml_karyawan: type === 'tpp' ? '1-2 Pekerja' : '1-5 Pekerja',
  kapasitas: type === 'tpp' ? '<100 kg/bulan atau <400 porsi/bulan' : '500',
  sumber_air: 'PDAM',
});

const saranaForm = ref(buildDefaultForm(addSaranaType.value));

watch(
  () => props.open,
  (opened) => {
    hasEdited.value = false;
    if (opened) {
      addSaranaType.value = props.initialType || 'tpp';
      saranaForm.value = buildDefaultForm(addSaranaType.value);
    }
  }
);

watch(
  () => props.initialType,
  (val) => {
    if (val) addSaranaType.value = val;
  }
);

const submitSarana = async () => {
  if (isSubmitting.value) return;
  if (!saranaForm.value.nama) {
    toast.warning('Nama sarana / gedung wajib diisi!');
    return;
  }
  isSubmitting.value = true;
  try {
    let endpoint = '/prl/tpp';
    let payload = {
      wilker: saranaForm.value.wilker,
      nama_tpp: saranaForm.value.nama,
      penanggung_jawab: saranaForm.value.penanggung_jawab,
      jml_karyawan: saranaForm.value.jml_karyawan,
      kapasitas_produksi: saranaForm.value.kapasitas,
    };

    if (addSaranaType.value === 'sab') {
      endpoint = '/prl/sab';
      payload = {
        wilker: saranaForm.value.wilker,
        nama_sab: saranaForm.value.nama,
        penanggung_jawab: saranaForm.value.penanggung_jawab,
        penanggung_jawab_no_hp: saranaForm.value.no_hp,
        sumber_air: saranaForm.value.sumber_air,
        kapasitas_penampungan: saranaForm.value.kapasitas,
      };
    } else if (addSaranaType.value === 'hsbu') {
      endpoint = '/prl/hsbu';
      payload = {
        wilker: saranaForm.value.wilker,
        nama_gedung: saranaForm.value.nama,
        penanggung_jawab: saranaForm.value.penanggung_jawab,
        jml_karyawan: saranaForm.value.jml_karyawan,
      };
    }

    const res = await axios.post(endpoint, payload);
    if (res.data.success) {
      hasEdited.value = false;
      allowNextNavigation();
      toast.success(res.data.message || 'Data sarana berhasil disimpan');
      emit('saved');
      emit('close');
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan data sarana.');
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<template>
  <ClinicalModal
    :open="open"
    title="Pendaftaran Sarana Sanitasi Baru"
    max-width="max-w-lg"
    :close-on-outside="!isSubmitting"
    @close="emit('close')"
  >
    <div
      class="relative w-full bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden"
      @input="hasEdited = true"
      @change="hasEdited = true"
    >
      <div class="px-6 py-4 bg-gradient-to-r from-[#00A99D] to-[#005851] text-white flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Plus class="w-5 h-5" />
          <h3 class="text-sm font-bold">Pendaftaran Sarana Sanitasi Baru</h3>
        </div>
        <button
          type="button"
          @click="emit('close')"
          aria-label="Tutup modal tambah sarana"
          title="Tutup"
          class="p-1 rounded-lg hover:bg-white/20 transition cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="flex border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 p-2 gap-2">
        <button
          type="button"
          @click="addSaranaType = 'tpp'"
          class="flex-1 py-1.5 text-xs font-bold rounded-lg transition text-center cursor-pointer"
          :class="addSaranaType === 'tpp' ? 'bg-white dark:bg-slate-900 text-[#005851] dark:text-[#99DFD6] shadow-xs' : 'text-slate-500'"
        >
          TPP (Pangan)
        </button>
        <button
          type="button"
          @click="addSaranaType = 'sab'"
          class="flex-1 py-1.5 text-xs font-bold rounded-lg transition text-center cursor-pointer"
          :class="addSaranaType === 'sab' ? 'bg-white dark:bg-slate-900 text-[#005851] dark:text-[#99DFD6] shadow-xs' : 'text-slate-500'"
        >
          SAB (Air)
        </button>
        <button
          type="button"
          @click="addSaranaType = 'hsbu'"
          class="flex-1 py-1.5 text-xs font-bold rounded-lg transition text-center cursor-pointer"
          :class="addSaranaType === 'hsbu' ? 'bg-white dark:bg-slate-900 text-[#005851] dark:text-[#99DFD6] shadow-xs' : 'text-slate-500'"
        >
          HSBU (Gedung)
        </button>
      </div>

      <div class="p-6 space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
          <select v-model="saranaForm.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
            <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
            {{ addSaranaType === 'tpp' ? 'Nama TPP (Kantin / Rumah Makan) *' : (addSaranaType === 'sab' ? 'Nama Sarana Air Bersih *' : 'Nama Gedung / Bangunan / Tempat Usaha *') }}
          </label>
          <input type="text" v-model="saranaForm.nama" placeholder="Masukkan nama sarana..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Penanggung Jawab / Pemilik</label>
          <input type="text" v-model="saranaForm.penanggung_jawab" placeholder="Nama pemilik / penanggung jawab" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
        </div>

        <div v-if="addSaranaType === 'sab'" class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">No. HP Penanggung Jawab</label>
            <input type="text" v-model="saranaForm.no_hp" placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Sumber Air</label>
            <select v-model="saranaForm.sumber_air" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
              <option value="PDAM">PDAM</option>
              <option value="Air Tanah">Air Tanah (Sumur)</option>
              <option value="Mata Air">Mata Air</option>
              <option value="Air Permukaan">Air Permukaan</option>
              <option value="Air Hujan">Air Hujan</option>
            </select>
          </div>
        </div>

        <div v-if="addSaranaType === 'tpp' || addSaranaType === 'hsbu'" class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jumlah Karyawan</label>
            <select v-model="saranaForm.jml_karyawan" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
              <option value="1-2 Pekerja">1-2 Pekerja (Mikro)</option>
              <option value="3-19 Pekerja">3-19 Pekerja (Kecil)</option>
              <option value="20-100 Pekerja">20-100 Pekerja (Menengah)</option>
              <option value=">100 Pekerja">&gt;100 Pekerja (Besar)</option>
            </select>
          </div>
          <div v-if="addSaranaType === 'tpp'">
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kapasitas Produksi</label>
            <select v-model="saranaForm.kapasitas" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
              <option value="<100 kg/bulan atau <400 porsi/bulan">&lt;100 kg atau &lt;400 porsi/bln</option>
              <option value="100-200 kg/bulan atau 400-800 porsi/bulan">100-200 kg atau 400-800 porsi/bln</option>
              <option value="200-500 kg/bulan atau 801-1000 porsi/bulan">200-500 kg atau 801-1000 porsi/bln</option>
              <option value=">500 kg/bulan atau >1000 porsi/bulan">&gt;500 kg atau &gt;1000 porsi/bln</option>
            </select>
          </div>
        </div>

        <div v-if="addSaranaType === 'sab'">
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kapasitas Penampungan (Liter)</label>
          <input type="text" v-model="saranaForm.kapasitas" placeholder="Contoh: 1000" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
        </div>
      </div>

      <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
        <button type="button" @click="emit('close')" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-700 transition cursor-pointer">
          Batal
        </button>
        <button type="button" @click="submitSarana()" :disabled="isSubmitting" class="px-5 py-2 text-xs font-bold rounded-xl bg-gradient-to-r from-[#00A99D] to-[#005851] text-white hover:brightness-105 transition shadow-sm flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
          <Check class="w-4 h-4" />
          <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan Sarana' }}</span>
        </button>
      </div>
    </div>
  </ClinicalModal>
</template>
