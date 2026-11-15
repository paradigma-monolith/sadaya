<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import ClinicalModal from '../../../Components/ClinicalModal.vue';
import { useUnsavedGuard } from '@/composables/useUnsavedGuard';
import { ClipboardCheck, Utensils, Droplets, Building2, Bug, Save, X } from 'lucide-vue-next';
import { getRiskBadgeClass } from '../utils';

const props = defineProps({
  open: { type: Boolean, default: false },
  initialType: { type: String, default: 'tpp' },
  apiPrefix: { type: String, default: '/sanitasi-lingkungan' },
  allowSivera: { type: Boolean, default: false },
  wilkerOptions: { type: Array, default: () => [] },
  pemeriksaOptions: { type: Array, default: () => [] },
  masterTpp: { type: Array, default: () => [] },
  masterSab: { type: Array, default: () => [] },
  masterHsbu: { type: Array, default: () => [] },
});

const emit = defineEmits(['close', 'saved', 'switch-sivera']);

const todayStr = new Date().toISOString().split('T')[0];
const addInspeksiType = ref(props.initialType || 'tpp');
const isSubmitting = ref(false);
const hasEdited = ref(false);

const buildDefaultTpp = (defWilker, defPemeriksa) => ({
  wilker: defWilker,
  tanggal_inspeksi: todayStr,
  master_tpp_id: '',
  nama_tpp: '',
  penanggung_jawab_tpp: '',
  jml_karyawan_tpp: '1-2 Pekerja',
  kapasitas_produksi_tpp: '<100 kg/bulan atau <400 porsi/bulan',
  nama_pemeriksa: defPemeriksa,
  sec_a_satu: 'Tidak',
  sec_a_dua: 'Tidak',
  sec_a_tiga: 'Tidak',
  sec_b_satu: 'Tidak',
  sec_b_dua: 'Ya',
  sec_b_tiga: 'Ya',
  sec_d_nilai_inspeksi_sebelumnya: 85,
  kesimpulan: 'Memenuhi syarat laik higiene sanitasi pangan.',
  makanan: '',
  saran_rencana_tindakan: 'Pertahankan sanitasi peralatan dan penjamah makanan.',
});

const buildDefaultSab = (defWilker, defPemeriksa) => ({
  wilker: defWilker,
  tanggal_inspeksi: todayStr,
  master_sab_id: '',
  nama_sab: '',
  penanggung_jawab_sab: '',
  penanggung_jawab_no_hp_sab: '',
  sumber_air_sab: 'PDAM',
  kapasitas_penampungan_sab: '500',
  nama_pemeriksa: defPemeriksa,
  sec_b_satu: 'Tidak',
  sec_b_dua: 'Tidak',
  sec_b_tiga: 'Tidak',
  sec_b_empat: 'Tidak',
  sec_c_satu: 'Tidak',
  sec_c_dua: 'Tidak',
  sec_c_tiga: 'Tidak',
  sec_c_empat: 'Tidak',
  sec_c_lima: 'Tidak',
  sec_c_enam: 'Tidak',
  sec_c_tujuh: 'Tidak',
  sec_c_delapan: 'Tidak',
  sec_c_sembilan: 'Tidak',
  sec_c_sepuluh: 'Tidak',
  saran_rencana_tindakan: 'Lakukan pembersihan dan pengurasan tandon penampungan air secara berkala.',
});

const buildDefaultHsbu = (defWilker, defPemeriksa) => ({
  wilker: defWilker,
  tanggal_inspeksi: todayStr,
  master_hsbu_id: '',
  nama_gedung: '',
  penanggung_jawab_hsbu: '',
  jml_karyawan_hsbu: '10 Pekerja',
  nama_pemeriksa: defPemeriksa,
  halaman: 90,
  konstruksi: 180,
  air_bersih: 180,
  udara: 135,
  limbah: 135,
  pencahayaan: 90,
  kebisingan: 85,
  saran_rencana_tindakan: 'Pertahankan higiene gedung dan fasilitas sanitasi lingkungan kerja.',
  catatan: 'Kondisi bangunan terawat dengan baik.',
});

const defWilker = () => props.wilkerOptions[0] || 'Wilayah Kerja Patimban';
const defPemeriksa = () => props.pemeriksaOptions[0] || '';

const formTpp = ref(buildDefaultTpp(defWilker(), defPemeriksa()));
const formSab = ref(buildDefaultSab(defWilker(), defPemeriksa()));
const formHsbu = ref(buildDefaultHsbu(defWilker(), defPemeriksa()));

watch(
  () => props.open,
  (opened) => {
    if (opened) {
      addInspeksiType.value = props.initialType || 'tpp';
      formTpp.value.wilker = formTpp.value.wilker || defWilker();
      formTpp.value.nama_pemeriksa = formTpp.value.nama_pemeriksa || defPemeriksa();
      formSab.value.wilker = formSab.value.wilker || defWilker();
      formSab.value.nama_pemeriksa = formSab.value.nama_pemeriksa || defPemeriksa();
      formHsbu.value.wilker = formHsbu.value.wilker || defWilker();
      formHsbu.value.nama_pemeriksa = formHsbu.value.nama_pemeriksa || defPemeriksa();
      hasEdited.value = false;
    } else {
      hasEdited.value = false;
    }
  }
);

watch(() => props.initialType, (val) => {
  if (val) addInspeksiType.value = val;
});

watch(
  () => formTpp.value.master_tpp_id,
  (newId) => {
    if (!newId) return;
    const item = props.masterTpp.find((m) => m.id === Number(newId));
    if (item) {
      formTpp.value.nama_tpp = item.nama_tpp;
      formTpp.value.wilker = item.wilker || formTpp.value.wilker;
      formTpp.value.penanggung_jawab_tpp = item.penanggung_jawab || '';
      formTpp.value.jml_karyawan_tpp = item.jml_karyawan || '1-2 Pekerja';
      formTpp.value.kapasitas_produksi_tpp = item.kapasitas_produksi || '<100 kg/bulan atau <400 porsi/bulan';
    }
  }
);

watch(
  () => formSab.value.master_sab_id,
  (newId) => {
    if (!newId) return;
    const item = props.masterSab.find((m) => m.id === Number(newId));
    if (item) {
      formSab.value.nama_sab = item.nama_sab;
      formSab.value.wilker = item.wilker || formSab.value.wilker;
      formSab.value.penanggung_jawab_sab = item.penanggung_jawab || '';
      formSab.value.penanggung_jawab_no_hp_sab = item.penanggung_jawab_no_hp || '';
      formSab.value.sumber_air_sab = item.sumber_air || 'PDAM';
      formSab.value.kapasitas_penampungan_sab = item.kapasitas_penampungan || '500';
    }
  }
);

watch(
  () => formHsbu.value.master_hsbu_id,
  (newId) => {
    if (!newId) return;
    const item = props.masterHsbu.find((m) => m.id === Number(newId));
    if (item) {
      formHsbu.value.nama_gedung = item.nama_gedung;
      formHsbu.value.wilker = item.wilker || formHsbu.value.wilker;
      formHsbu.value.penanggung_jawab_hsbu = item.penanggung_jawab || '';
      formHsbu.value.jml_karyawan_hsbu = item.jml_karyawan || '10 Pekerja';
    }
  }
);

const skorTppSecA = computed(() => {
  if (formTpp.value.sec_a_satu === 'Ya') return 5;
  if (formTpp.value.sec_a_dua === 'Tidak') return 10;
  if (formTpp.value.sec_a_tiga === 'Ya') return 15;
  return 10;
});
const skorTppSecB = computed(() => {
  if (formTpp.value.sec_b_satu === 'Ya') return 15;
  if (formTpp.value.sec_b_dua === 'Ya') return 5;
  if (formTpp.value.sec_b_tiga === 'Ya') return 10;
  return 15;
});
const skorTppSecC = computed(() => {
  const jk = formTpp.value.jml_karyawan_tpp || '';
  const kp = formTpp.value.kapasitas_produksi_tpp || '';
  if (jk.includes('>100') || kp.includes('>500')) return 20;
  if (jk.includes('20-100') || kp.includes('200-500')) return 15;
  if (jk.includes('3-19') || kp.includes('100-200')) return 10;
  return 5;
});
const skorTppSecD = computed(() => {
  const val = Number(formTpp.value.sec_d_nilai_inspeksi_sebelumnya || 0);
  if (val >= 80) return 5;
  if (val >= 60) return 10;
  return 15;
});
const totalSkorTpp = computed(() => skorTppSecA.value + skorTppSecB.value + skorTppSecC.value + skorTppSecD.value);
const tingkatRisikoTpp = computed(() => {
  if (totalSkorTpp.value < 35) return 'Rendah';
  if (totalSkorTpp.value <= 50) return 'Sedang';
  return 'Tinggi';
});

const sabRiskQuestions = [
  'Apakah penampungan air ada kebocoran?',
  'Apakah penampungan air terbuka?',
  'Apakah bahan mudah korosi / tidak kuat?',
  'Apakah tutup manhole kotor / tidak rapat?',
  'Apakah lokasi sekitar tandon kotor?',
  'Apakah terdapat sumber pencemaran di radius dekat?',
  'Apakah menjadi sarang vektor / jentik?',
  'Apakah hewan / tikus dapat masuk ke penampungan?',
  'Apakah ada kebocoran pada sistem distribusi?',
  'Apakah ada keran yang rusak / bocor?',
];
const sabRiskKeys = ['satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh'];

const totalYaSab = computed(() => {
  let count = 0;
  for (const k of sabRiskKeys) {
    if (formSab.value[`sec_c_${k}`] === 'Ya') count++;
  }
  return count;
});
const totalSkorSab = computed(() => totalYaSab.value * 10);
const tingkatRisikoSab = computed(() => {
  const skor = totalSkorSab.value;
  if (skor < 25) return 'Rendah';
  if (skor <= 50) return 'Sedang';
  if (skor <= 75) return 'Tinggi';
  return 'Amat Tinggi';
});

const totalSkorHsbu = computed(() => (
  Number(formHsbu.value.halaman || 0)
  + Number(formHsbu.value.konstruksi || 0)
  + Number(formHsbu.value.air_bersih || 0)
  + Number(formHsbu.value.udara || 0)
  + Number(formHsbu.value.limbah || 0)
  + Number(formHsbu.value.pencahayaan || 0)
  + Number(formHsbu.value.kebisingan || 0)
));
const tingkatRisikoHsbu = computed(() => {
  if (totalSkorHsbu.value >= 712.5) return 'Rendah';
  if (totalSkorHsbu.value >= 475) return 'Sedang';
  return 'Tinggi';
});

const { allowNextNavigation } = useUnsavedGuard(() => props.open && hasEdited.value && !isSubmitting.value);

const submitInspeksi = async () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  try {
    if (addInspeksiType.value === 'tpp') {
      if (!formTpp.value.nama_tpp) {
        toast.warning('Nama TPP wajib diisi atau dipilih!');
        return;
      }
      const res = await axios.post(`${props.apiPrefix}/inspeksi/tpp`, {
        wilker: formTpp.value.wilker,
        tanggal_inspeksi: formTpp.value.tanggal_inspeksi,
        prl_tpp_id: formTpp.value.master_tpp_id || null,
        nama_tpp: formTpp.value.nama_tpp,
        penanggung_jawab_tpp: formTpp.value.penanggung_jawab_tpp,
        jml_karyawan_tpp: formTpp.value.jml_karyawan_tpp,
        kapasitas_produksi_tpp: formTpp.value.kapasitas_produksi_tpp,
        nama_pemeriksa: formTpp.value.nama_pemeriksa,
        sec_a_satu: formTpp.value.sec_a_satu,
        sec_a_dua: formTpp.value.sec_a_dua,
        sec_a_tiga: formTpp.value.sec_a_tiga,
        sec_a_skor: skorTppSecA.value,
        sec_b_satu: formTpp.value.sec_b_satu,
        sec_b_dua: formTpp.value.sec_b_dua,
        sec_b_tiga: formTpp.value.sec_b_tiga,
        sec_b_skor: skorTppSecB.value,
        sec_c_skor: skorTppSecC.value,
        sec_c_keterangan: formTpp.value.jml_karyawan_tpp,
        sec_d_nilai_inspeksi_sebelumnya: formTpp.value.sec_d_nilai_inspeksi_sebelumnya,
        sec_d_skor: skorTppSecD.value,
        hasil: totalSkorTpp.value,
        tingkat_risiko: tingkatRisikoTpp.value,
        kesimpulan: formTpp.value.kesimpulan,
        makanan: formTpp.value.makanan,
        saran_rencana_tindakan: formTpp.value.saran_rencana_tindakan,
      });
      toast.success(res.data.message || 'Data inspeksi TPP berhasil disimpan');
    } else if (addInspeksiType.value === 'sab') {
      if (!formSab.value.nama_sab) {
        toast.warning('Nama SAB wajib diisi atau dipilih!');
        return;
      }
      const res = await axios.post(`${props.apiPrefix}/inspeksi/sab`, {
        wilker: formSab.value.wilker,
        tanggal_inspeksi: formSab.value.tanggal_inspeksi,
        prl_sab_id: formSab.value.master_sab_id || null,
        nama_sab: formSab.value.nama_sab,
        penanggung_jawab_sab: formSab.value.penanggung_jawab_sab,
        penanggung_jawab_no_hp_sab: formSab.value.penanggung_jawab_no_hp_sab,
        sumber_air_sab: formSab.value.sumber_air_sab,
        kapasitas_penampungan_sab: formSab.value.kapasitas_penampungan_sab,
        nama_pemeriksa: formSab.value.nama_pemeriksa,
        sec_b_satu: formSab.value.sec_b_satu,
        sec_b_dua: formSab.value.sec_b_dua,
        sec_b_tiga: formSab.value.sec_b_tiga,
        sec_b_empat: formSab.value.sec_b_empat,
        sec_c_satu: formSab.value.sec_c_satu,
        sec_c_dua: formSab.value.sec_c_dua,
        sec_c_tiga: formSab.value.sec_c_tiga,
        sec_c_empat: formSab.value.sec_c_empat,
        sec_c_lima: formSab.value.sec_c_lima,
        sec_c_enam: formSab.value.sec_c_enam,
        sec_c_tujuh: formSab.value.sec_c_tujuh,
        sec_c_delapan: formSab.value.sec_c_delapan,
        sec_c_sembilan: formSab.value.sec_c_sembilan,
        sec_c_sepuluh: formSab.value.sec_c_sepuluh,
        hasil: totalSkorSab.value,
        tingkat_risiko: tingkatRisikoSab.value,
        saran_rencana_tindakan: formSab.value.saran_rencana_tindakan,
      });
      toast.success(res.data.message || 'Data inspeksi SAB berhasil disimpan');
    } else if (addInspeksiType.value === 'hsbu') {
      if (!formHsbu.value.nama_gedung) {
        toast.warning('Nama Bangunan/Gedung HSBU wajib diisi atau dipilih!');
        return;
      }
      const res = await axios.post(`${props.apiPrefix}/inspeksi/hsbu`, {
        wilker: formHsbu.value.wilker,
        tanggal_inspeksi: formHsbu.value.tanggal_inspeksi,
        prl_hsbu_id: formHsbu.value.master_hsbu_id || null,
        nama_gedung: formHsbu.value.nama_gedung,
        penanggung_jawab_hsbu: formHsbu.value.penanggung_jawab_hsbu,
        jml_karyawan_hsbu: formHsbu.value.jml_karyawan_hsbu,
        nama_pemeriksa: formHsbu.value.nama_pemeriksa,
        komponen_penilaian: {
          halaman: formHsbu.value.halaman,
          konstruksi: formHsbu.value.konstruksi,
          air_bersih: formHsbu.value.air_bersih,
          udara: formHsbu.value.udara,
          limbah: formHsbu.value.limbah,
          pencahayaan: formHsbu.value.pencahayaan,
          kebisingan: formHsbu.value.kebisingan,
        },
        total: totalSkorHsbu.value,
        tingkat_risiko: tingkatRisikoHsbu.value,
        saran_rencana_tindakan: formHsbu.value.saran_rencana_tindakan,
        catatan: formHsbu.value.catatan,
      });
      toast.success(res.data.message || 'Data inspeksi HSBU berhasil disimpan');
    }
    hasEdited.value = false;
    allowNextNavigation();
    emit('saved');
    emit('close');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan data.');
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<template>
  <ClinicalModal
    :open="open"
    title="Formulir Pencatatan Inspeksi Sanitasi"
    max-width="max-w-4xl"
    :close-on-outside="!isSubmitting"
    @close="emit('close')"
  >
    <div
      class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-h-[92vh] overflow-hidden flex flex-col shadow-2xl"
      @input="hasEdited = true"
      @change="hasEdited = true"
    >
      <div class="px-6 py-4 bg-gradient-to-r from-[#005851] to-[#00A99D] text-white flex items-center justify-between">
        <div class="flex items-center gap-2">
          <ClipboardCheck class="w-5 h-5 text-white" />
          <h3 class="text-base font-bold tracking-tight">Formulir Pencatatan Inspeksi Sanitasi</h3>
        </div>
        <button
          type="button"
          @click="emit('close')"
          aria-label="Tutup formulir inspeksi"
          title="Tutup"
          class="p-1 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="px-6 py-3 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex items-center gap-2 overflow-x-auto text-xs font-semibold">
        <button
          type="button"
          @click="addInspeksiType = 'tpp'"
          class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer shrink-0"
          :class="addInspeksiType === 'tpp' ? 'bg-white dark:bg-slate-900 text-amber-600 shadow-xs border border-slate-200 dark:border-slate-700' : 'text-slate-500 hover:text-slate-800'"
        >
          <Utensils class="w-3.5 h-3.5" />
          <span>Inspeksi TPP (Pangan)</span>
        </button>
        <button
          type="button"
          @click="addInspeksiType = 'sab'"
          class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer shrink-0"
          :class="addInspeksiType === 'sab' ? 'bg-white dark:bg-slate-900 text-cyan-600 shadow-xs border border-slate-200 dark:border-slate-700' : 'text-slate-500 hover:text-slate-800'"
        >
          <Droplets class="w-3.5 h-3.5" />
          <span>Inspeksi SAB (Air Bersih)</span>
        </button>
        <button
          type="button"
          @click="addInspeksiType = 'hsbu'"
          class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer shrink-0"
          :class="addInspeksiType === 'hsbu' ? 'bg-white dark:bg-slate-900 text-[#00A99D] shadow-xs border border-slate-200 dark:border-slate-700' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
        >
          <Building2 class="w-3.5 h-3.5" />
          <span>Inspeksi HSBU (Bangunan)</span>
        </button>
        <button
          v-if="allowSivera"
          type="button"
          @click="allowNextNavigation(); emit('switch-sivera')"
          class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer shrink-0 text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400"
        >
          <Bug class="w-3.5 h-3.5" />
          <span>Surveilans SIVERA (Vektor)</span>
        </button>
      </div>

      <datalist id="sanitasi-pemeriksa-list">
        <option v-for="p in pemeriksaOptions" :key="p" :value="p" />
      </datalist>

      <div class="p-6 max-h-[75vh] overflow-y-auto space-y-6">
        <!-- TPP FORM -->
        <div v-if="addInspeksiType === 'tpp'" class="space-y-5">
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
              <select v-model="formTpp.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Inspeksi *</label>
              <input type="date" v-model="formTpp.tanggal_inspeksi" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Sarana TPP Terdaftar</label>
              <select v-model="formTpp.master_tpp_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option value="">-- Ketik Manual atau Pilih Sarana --</option>
                <option v-for="m in masterTpp" :key="m.id" :value="m.id">{{ m.nama_tpp }} ({{ m.wilker }})</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Tempat Pengelolaan Pangan *</label>
              <input type="text" v-model="formTpp.nama_tpp" placeholder="Contoh: Kantin Dermaga 01" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pemilik / Penanggung Jawab</label>
              <input type="text" v-model="formTpp.penanggung_jawab_tpp" placeholder="Nama pemilik/penanggung jawab" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Petugas Pemeriksa Karantina</label>
              <input type="text" v-model="formTpp.nama_pemeriksa" list="sanitasi-pemeriksa-list" placeholder="Nama pemeriksa" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
          </div>

          <div class="space-y-4 pt-2 border-t border-slate-200 dark:border-slate-800">
            <div class="p-4 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40 space-y-3">
              <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold text-amber-800 dark:text-amber-400">A. Penilaian Skor Profil Pangan (pH &amp; Aw)</h4>
                <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800">Skor A: {{ skorTppSecA }}</span>
              </div>
              <div class="text-xs text-slate-600 dark:text-slate-300 space-y-2">
                <div class="flex items-center justify-between gap-2">
                  <span>1. Apakah pangan memiliki pH &lt; 4,6 atau Aw &lt; 0,85 atau mengandung gula tinggi?</span>
                  <div class="flex items-center gap-3 shrink-0">
                    <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_a_satu" value="Ya" /> Ya</label>
                    <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_a_satu" value="Tidak" /> Tidak</label>
                  </div>
                </div>
                <div v-if="formTpp.sec_a_satu === 'Tidak'" class="flex items-center justify-between gap-2 pt-1 border-t border-amber-100 dark:border-amber-900/30">
                  <span>2. Apakah pangan termasuk kategori pangan berisiko tinggi (PHF)?</span>
                  <div class="flex items-center gap-3 shrink-0">
                    <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_a_dua" value="Ya" /> Ya</label>
                    <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_a_dua" value="Tidak" /> Tidak</label>
                  </div>
                </div>
                <div v-if="formTpp.sec_a_satu === 'Tidak' && formTpp.sec_a_dua === 'Ya'" class="flex items-center justify-between gap-2 pt-1 border-t border-amber-100 dark:border-amber-900/30">
                  <span>3. Apakah pH produk merupakan kombinasi pH &gt; 4,6 dan Aw &gt; 0,85?</span>
                  <div class="flex items-center gap-3 shrink-0">
                    <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_a_tiga" value="Ya" /> Ya</label>
                    <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_a_tiga" value="Tidak" /> Tidak</label>
                  </div>
                </div>
              </div>
            </div>

            <div class="p-4 rounded-2xl bg-teal-50/50 dark:bg-teal-950/20 border border-teal-200/60 dark:border-teal-900/40 space-y-3">
              <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold text-teal-800 dark:text-teal-400">B. Penilaian Mitigasi Bahaya Pangan</h4>
                <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-full bg-teal-100 text-teal-800">Skor B: {{ skorTppSecB }}</span>
              </div>
              <div class="text-xs text-slate-600 dark:text-slate-300 space-y-2">
                <div class="flex items-center justify-between gap-2">
                  <span>1. Apakah pangan dikonsumsi dalam kondisi mentah / tanpa proses pembunuhan bahaya?</span>
                  <div class="flex items-center gap-3 shrink-0">
                    <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_b_satu" value="Ya" /> Ya</label>
                    <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_b_satu" value="Tidak" /> Tidak</label>
                  </div>
                </div>
                <div v-if="formTpp.sec_b_satu === 'Tidak'" class="flex items-center justify-between gap-2 pt-1 border-t border-teal-100 dark:border-teal-900/30">
                  <span>2. Apakah pangan langsung dikonsumsi / danger zone max 4 jam / ada sterilisasi?</span>
                  <div class="flex items-center gap-3 shrink-0">
                    <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_b_dua" value="Ya" /> Ya</label>
                    <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_b_dua" value="Tidak" /> Tidak</label>
                  </div>
                </div>
                <div v-if="formTpp.sec_b_satu === 'Tidak' && formTpp.sec_b_dua === 'Tidak'" class="flex items-center justify-between gap-2 pt-1 border-t border-teal-100 dark:border-teal-900/30">
                  <span>3. Apakah pangan dikemas tertutup rapat untuk menghindari kontaminasi?</span>
                  <div class="flex items-center gap-3 shrink-0">
                    <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_b_tiga" value="Ya" /> Ya</label>
                    <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_b_tiga" value="Tidak" /> Tidak</label>
                  </div>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-2">
                <div class="flex items-center justify-between">
                  <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">C. Ukuran Skala Usaha</h4>
                  <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700">Skor C: {{ skorTppSecC }}</span>
                </div>
                <div class="space-y-2">
                  <div>
                    <label class="block text-[11px] text-slate-500">Jumlah Tenaga Kerja</label>
                    <select v-model="formTpp.jml_karyawan_tpp" class="w-full px-2.5 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                      <option value="1-2 Pekerja">1-2 Pekerja (Mikro)</option>
                      <option value="3-19 Pekerja">3-19 Pekerja (Kecil)</option>
                      <option value="20-100 Pekerja">20-100 Pekerja (Menengah)</option>
                      <option value=">100 Pekerja">&gt;100 Pekerja (Besar)</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-[11px] text-slate-500">Kapasitas Produksi</label>
                    <select v-model="formTpp.kapasitas_produksi_tpp" class="w-full px-2.5 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                      <option value="<100 kg/bulan atau <400 porsi/bulan">&lt;100 kg atau &lt;400 porsi/bln</option>
                      <option value="100-200 kg/bulan atau 400-800 porsi/bulan">100-200 kg atau 400-800 porsi/bln</option>
                      <option value="200-500 kg/bulan atau 801-1000 porsi/bulan">200-500 kg atau 801-1000 porsi/bln</option>
                      <option value=">500 kg/bulan atau >1000 porsi/bulan">&gt;500 kg atau &gt;1000 porsi/bln</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-2">
                <div class="flex items-center justify-between">
                  <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">D. Riwayat Kepatuhan</h4>
                  <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700">Skor D: {{ skorTppSecD }}</span>
                </div>
                <div>
                  <label class="block text-[11px] text-slate-500">Nilai Inspeksi Sebelumnya (0 - 100)</label>
                  <input type="number" min="0" max="100" v-model="formTpp.sec_d_nilai_inspeksi_sebelumnya" class="w-full px-2.5 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
                </div>
              </div>
            </div>

            <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-500/10 to-teal-500/10 border border-emerald-500/30 flex items-center justify-between">
              <div>
                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300">Total Skor Inspeksi TPP:</span>
                <span class="ml-2 text-xl font-extrabold font-mono text-[#005851] dark:text-emerald-400">{{ totalSkorTpp }}</span>
              </div>
              <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getRiskBadgeClass(tingkatRisikoTpp)">
                Tingkat Risiko: {{ tingkatRisikoTpp }}
              </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kesimpulan Pemeriksaan</label>
                <textarea v-model="formTpp.kesimpulan" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Saran &amp; Rencana Tindakan</label>
                <textarea v-model="formTpp.saran_rencana_tindakan" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- SAB FORM -->
        <div v-if="addInspeksiType === 'sab'" class="space-y-5">
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
              <select v-model="formSab.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Inspeksi *</label>
              <input type="date" v-model="formSab.tanggal_inspeksi" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Sarana SAB Terdaftar</label>
              <select v-model="formSab.master_sab_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option value="">-- Ketik Manual atau Pilih Sarana --</option>
                <option v-for="m in masterSab" :key="m.id" :value="m.id">{{ m.nama_sab }} ({{ m.wilker }})</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Sarana Air Bersih *</label>
              <input type="text" v-model="formSab.nama_sab" placeholder="Contoh: Tandon Dermaga Barat" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Penanggung Jawab</label>
              <input type="text" v-model="formSab.penanggung_jawab_sab" placeholder="Nama PJ" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Sumber Air</label>
              <select v-model="formSab.sumber_air_sab" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option value="PDAM">PDAM</option>
                <option value="Air Tanah">Air Tanah (Sumur)</option>
                <option value="Mata Air">Mata Air</option>
                <option value="Air Permukaan">Air Permukaan</option>
                <option value="Air Hujan">Air Hujan</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kapasitas (Liter)</label>
              <input type="text" v-model="formSab.kapasitas_penampungan_sab" placeholder="Contoh: 1000" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
          </div>

          <div class="p-4 rounded-2xl bg-cyan-50/50 dark:bg-cyan-950/20 border border-cyan-200/60 space-y-2">
            <h4 class="text-xs font-bold text-cyan-800 dark:text-cyan-400">Parameter Kualitas Fisik Air Bersih</h4>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
              <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-900 border border-cyan-100 dark:border-cyan-900">
                <span>Keruh?</span>
                <label class="cursor-pointer"><input type="checkbox" v-model="formSab.sec_b_satu" true-value="Ya" false-value="Tidak" /> Ya</label>
              </div>
              <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-900 border border-cyan-100 dark:border-cyan-900">
                <span>Berbau?</span>
                <label class="cursor-pointer"><input type="checkbox" v-model="formSab.sec_b_dua" true-value="Ya" false-value="Tidak" /> Ya</label>
              </div>
              <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-900 border border-cyan-100 dark:border-cyan-900">
                <span>Berasa?</span>
                <label class="cursor-pointer"><input type="checkbox" v-model="formSab.sec_b_tiga" true-value="Ya" false-value="Tidak" /> Ya</label>
              </div>
              <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-900 border border-cyan-100 dark:border-cyan-900">
                <span>Berwarna?</span>
                <label class="cursor-pointer"><input type="checkbox" v-model="formSab.sec_b_empat" true-value="Ya" false-value="Tidak" /> Ya</label>
              </div>
            </div>
          </div>

          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
            <div class="flex items-center justify-between">
              <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">10 Variabel Risiko Pencemaran &amp; Perlindungan Fisik</h4>
              <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-full bg-cyan-100 text-cyan-800">Total Ya: {{ totalYaSab }} (Skor: {{ totalSkorSab }}%)</span>
            </div>
            <div class="space-y-1.5 text-xs">
              <div v-for="(q, idx) in sabRiskQuestions" :key="idx" class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                <span>{{ idx + 1 }}. {{ q }}</span>
                <div class="flex items-center gap-3 shrink-0">
                  <label class="cursor-pointer"><input type="radio" v-model="formSab['sec_c_' + sabRiskKeys[idx]]" value="Ya" /> Ya</label>
                  <label class="cursor-pointer"><input type="radio" v-model="formSab['sec_c_' + sabRiskKeys[idx]]" value="Tidak" /> Tidak</label>
                </div>
              </div>
            </div>
          </div>

          <div class="p-4 rounded-2xl bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-200 flex items-center justify-between">
            <div>
              <span class="text-xs font-bold text-cyan-900 dark:text-cyan-300">Hasil Skor Risiko Kontaminasi:</span>
              <span class="ml-2 text-xl font-extrabold font-mono text-cyan-700 dark:text-cyan-400">{{ totalSkorSab }}%</span>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getRiskBadgeClass(tingkatRisikoSab)">
              Tingkat Risiko: {{ tingkatRisikoSab }}
            </span>
          </div>
        </div>

        <!-- HSBU FORM -->
        <div v-if="addInspeksiType === 'hsbu'" class="space-y-5">
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
              <select v-model="formHsbu.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Inspeksi *</label>
              <input type="date" v-model="formHsbu.tanggal_inspeksi" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Gedung HSBU Terdaftar</label>
              <select v-model="formHsbu.master_hsbu_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option value="">-- Ketik Manual atau Pilih Gedung --</option>
                <option v-for="m in masterHsbu" :key="m.id" :value="m.id">{{ m.nama_gedung }} ({{ m.wilker }})</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Gedung / Bangunan *</label>
              <input type="text" v-model="formHsbu.nama_gedung" placeholder="Contoh: Terminal Penumpang Pelabuhan" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Penanggung Jawab Gedung</label>
              <input type="text" v-model="formHsbu.penanggung_jawab_hsbu" placeholder="Nama PJ" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Petugas Pemeriksa</label>
              <input type="text" v-model="formHsbu.nama_pemeriksa" list="sanitasi-pemeriksa-list" placeholder="Nama petugas" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
          </div>

          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
            <div class="flex items-center justify-between">
              <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Komponen Penilaian Higiene Gedung &amp; Sanitasi (Skala 1000)</h4>
              <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Total Skor: {{ totalSkorHsbu }} / 1000</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs">
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Halaman &amp; Luar (0 - 100)</label>
                <input type="number" min="0" max="100" v-model="formHsbu.halaman" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono text-xs" />
              </div>
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Konstruksi Ruang (0 - 200)</label>
                <input type="number" min="0" max="200" v-model="formHsbu.konstruksi" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono text-xs" />
              </div>
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Penyehatan Air (0 - 200)</label>
                <input type="number" min="0" max="200" v-model="formHsbu.air_bersih" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono text-xs" />
              </div>
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Udara &amp; Ventilasi (0 - 150)</label>
                <input type="number" min="0" max="150" v-model="formHsbu.udara" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono text-xs" />
              </div>
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Pengelolaan Limbah (0 - 150)</label>
                <input type="number" min="0" max="150" v-model="formHsbu.limbah" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono text-xs" />
              </div>
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Pencahayaan (0 - 100)</label>
                <input type="number" min="0" max="100" v-model="formHsbu.pencahayaan" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono text-xs" />
              </div>
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Bising &amp; Getaran (0 - 100)</label>
                <input type="number" min="0" max="100" v-model="formHsbu.kebisingan" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono text-xs" />
              </div>
            </div>
          </div>

          <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 flex items-center justify-between">
            <div>
              <span class="text-xs font-bold text-emerald-900 dark:text-emerald-300">Hasil Skor Higiene Bangunan:</span>
              <span class="ml-2 text-xl font-extrabold font-mono text-[#005851] dark:text-emerald-400">{{ totalSkorHsbu }}</span>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getRiskBadgeClass(tingkatRisikoHsbu)">
              Tingkat Risiko: {{ tingkatRisikoHsbu }}
            </span>
          </div>
        </div>
      </div>

      <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
        <button
          type="button"
          @click="emit('close')"
          class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer"
        >
          Batal
        </button>
        <button
          type="button"
          @click="submitInspeksi()"
          :disabled="isSubmitting"
          class="px-5 py-2 rounded-xl bg-gradient-to-r from-[#00A99D] to-[#005851] text-xs font-bold text-white hover:brightness-105 transition shadow-sm flex items-center gap-2 cursor-pointer disabled:opacity-50"
        >
          <Save class="w-4 h-4" />
          <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan Data Inspeksi' }}</span>
        </button>
      </div>
    </div>
  </ClinicalModal>
</template>
