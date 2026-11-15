<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import ClinicalModal from '../../../Components/ClinicalModal.vue';
import { useUnsavedGuard } from '@/composables/useUnsavedGuard';
import { ShieldCheck, Bug, Check, X } from 'lucide-vue-next';
import { getRiskBadgeClass, formatTindakanPengendalian } from '../utils';

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  item: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['close', 'saved']);

const todayStr = new Date().toISOString().split('T')[0];
const isSubmitting = ref(false);
const initialSnapshot = ref('');

const createInitialForm = (item) => {
  let d = item?.detail_data?.pengendalian || {};
  if (typeof d === 'string') {
    try { d = JSON.parse(d); } catch { d = {}; }
  }
  return {
    tanggal: d.tanggal || todayStr,
    persiapan: d.persiapan || '',
    jentik_kimia: Boolean(d.jentik_kimia),
    jentik_kimia_gram: d.jentik_kimia_gram || '',
    jentik_kimia_sachet: d.jentik_kimia_sachet || '',
    jentik_fisik: Boolean(d.jentik_fisik),
    jentik_biologi: Boolean(d.jentik_biologi),
    nyamuk_fogging: Boolean(d.nyamuk_fogging),
    nyamuk_fogging_mesin: d.nyamuk_fogging_mesin || '',
    nyamuk_fogging_ml: d.nyamuk_fogging_ml || '',
    nyamuk_fogging_golongan: d.nyamuk_fogging_golongan || 'Sintetik Piretroid',
    nyamuk_fogging_bahan: d.nyamuk_fogging_bahan || '',
    nyamuk_fogging_merk: d.nyamuk_fogging_merk || '',
    nyamuk_fisik: Boolean(d.nyamuk_fisik),
    nyamuk_lokasi: d.nyamuk_lokasi || item?.lokasi || '',
    lalat_spraying: Boolean(d.spraying),
    lalat_spraying_ml: d.spraying_ml || '',
    lalat_granule: Boolean(d.granule),
    lalat_granule_gram: d.granule_gram || '',
    lalat_lokasi: d.lokasi || item?.lokasi || '',
    kecoa_retrapping: Boolean(d.retrapping),
    kecoa_retrapping_trap: d.retrapping_trap || '',
    kecoa_gel_bait: Boolean(d.gel_bait),
    kecoa_gel_bait_gram: d.gel_bait_gram || '',
    kecoa_lokasi: d.lokasi || item?.lokasi || '',
    catatan: item?.tindakan_pengendalian ? formatTindakanPengendalian(item.tindakan_pengendalian) : '',
  };
};

const form = ref(createInitialForm(props.item));

watch(
  () => [props.open, props.item],
  ([isOpen, newItem]) => {
    if (!isOpen || !newItem) return;
    form.value = createInitialForm(newItem);
    initialSnapshot.value = JSON.stringify(form.value);
  },
  { immediate: true }
);

const isDirty = computed(() => Boolean(props.open && props.item && initialSnapshot.value && JSON.stringify(form.value) !== initialSnapshot.value));
const { allowNextNavigation } = useUnsavedGuard(isDirty);

const submitPengendalian = async () => {
  if (!props.item || isSubmitting.value) return;
  isSubmitting.value = true;
  try {
    const pf = form.value;
    const jv = props.item.jenis_vektor || '';
    const summaryParts = [];
    const detailObj = { tanggal: pf.tanggal };

    if (jv.includes('Jentik')) {
      detailObj.persiapan = pf.persiapan;
      if (pf.jentik_kimia) {
        detailObj.jentik_kimia = '1';
        detailObj.jentik_kimia_gram = pf.jentik_kimia_gram;
        detailObj.jentik_kimia_sachet = pf.jentik_kimia_sachet;
        summaryParts.push(`Larvasidasi (${pf.jentik_kimia_gram || 0} gr, ${pf.jentik_kimia_sachet || 0} sachet)`);
      }
      if (pf.jentik_fisik) {
        detailObj.jentik_fisik = '1';
        summaryParts.push('Fisik (Kuras/Buang Air)');
      }
      if (pf.jentik_biologi) {
        detailObj.jentik_biologi = '1';
        summaryParts.push('Biologi (Ikan/BTI)');
      }
      if (pf.nyamuk_fogging) {
        detailObj.nyamuk_fogging = '1';
        detailObj.nyamuk_fogging_mesin = pf.nyamuk_fogging_mesin;
        detailObj.nyamuk_fogging_ml = pf.nyamuk_fogging_ml;
        detailObj.nyamuk_fogging_golongan = pf.nyamuk_fogging_golongan;
        detailObj.nyamuk_fogging_bahan = pf.nyamuk_fogging_bahan;
        detailObj.nyamuk_fogging_merk = pf.nyamuk_fogging_merk;
        summaryParts.push(`Fogging (${pf.nyamuk_fogging_ml || 0} ml ${pf.nyamuk_fogging_merk || ''})`);
      }
      if (pf.nyamuk_fisik) {
        detailObj.nyamuk_fisik = '1';
        summaryParts.push('Fisik Nyamuk (Trap/Raket)');
      }
    } else if (jv.includes('Lalat')) {
      detailObj.lokasi = pf.lalat_lokasi;
      if (pf.lalat_spraying) {
        detailObj.spraying = '1';
        detailObj.spraying_ml = pf.lalat_spraying_ml;
        summaryParts.push(`Spraying (${pf.lalat_spraying_ml || 0} ml)`);
      }
      if (pf.lalat_granule) {
        detailObj.granule = '1';
        detailObj.granule_gram = pf.lalat_granule_gram;
        summaryParts.push(`Granule (${pf.lalat_granule_gram || 0} gr)`);
      }
    } else if (jv.includes('Kecoa')) {
      detailObj.lokasi = pf.kecoa_lokasi;
      if (pf.kecoa_retrapping) {
        detailObj.retrapping = '1';
        detailObj.retrapping_trap = pf.kecoa_retrapping_trap;
        summaryParts.push(`Retrapping (${pf.kecoa_retrapping_trap || 0} trap)`);
      }
      if (pf.kecoa_gel_bait) {
        detailObj.gel_bait = '1';
        detailObj.gel_bait_gram = pf.kecoa_gel_bait_gram;
        summaryParts.push(`Gel Bait (${pf.kecoa_gel_bait_gram || 0} gr)`);
      }
    } else {
      summaryParts.push(pf.catatan || 'Pengendalian terpadu');
    }

    const payload = {
      tindakan_pengendalian: summaryParts.length > 0 ? summaryParts.join('; ') : (pf.catatan || 'Pengendalian surveilans'),
      pengendalian_detail: detailObj,
    };

    const res = await axios.post(`/prl/sivera/${props.item.id}/pengendalian`, payload);
    toast.success(res.data.message || 'Tindakan pengendalian disimpan');
    allowNextNavigation();
    initialSnapshot.value = JSON.stringify(form.value);
    emit('saved');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan tindakan pengendalian.');
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<template>
  <ClinicalModal
    :open="Boolean(open && item)"
    title="Catat Tindakan Pengendalian Vektor"
    max-width="max-w-xl"
    :close-on-outside="!isSubmitting"
    @close="!isSubmitting && emit('close')"
  >
    <div v-if="item" class="relative w-full bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
      <div class="px-6 py-4 bg-gradient-to-r from-teal-600 to-[#005851] text-white flex items-center justify-between">
        <div class="flex items-center gap-2 min-w-0">
          <ShieldCheck class="w-5 h-5 shrink-0" />
          <div class="min-w-0">
            <h3 class="text-sm font-bold">Catat Tindakan Pengendalian Vektor</h3>
            <p class="text-[11px] text-teal-100 truncate">{{ item.jenis_vektor }} &bull; {{ item.wilker }} &bull; {{ item.lokasi || '-' }}</p>
          </div>
        </div>
        <button
          type="button"
          @click="!isSubmitting && emit('close')"
          aria-label="Tutup Modal Pengendalian Vektor"
          title="Tutup"
          class="p-1 rounded-lg hover:bg-white/20 transition cursor-pointer shrink-0"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="p-6 space-y-4 text-xs max-h-[75vh] overflow-y-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Tindakan</label>
            <input
              type="date"
              v-model="form.tanggal"
              class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono"
            />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Hasil Surveilans</label>
            <div class="px-3 py-2 rounded-xl border font-bold text-xs" :class="getRiskBadgeClass(item.baku_mutu_status)">
              {{ item.baku_mutu_status }} ({{ item.parameter_utama }})
            </div>
          </div>
        </div>

        <!-- 1. JENTIK AEDES & NYAMUK ANOPHELES -->
        <div v-if="(item.jenis_vektor || '').includes('Jentik') || (item.jenis_vektor || '').includes('Nyamuk')" class="space-y-3">
          <div class="p-3.5 rounded-2xl bg-teal-50/50 dark:bg-teal-950/20 border border-teal-200 dark:border-teal-800 space-y-3">
            <h4 class="text-xs font-bold text-teal-900 dark:text-teal-200 flex items-center gap-1.5">
              <Bug class="w-4 h-4 text-teal-600" />
              <span>Pengendalian Jentik / Nyamuk (Sesuai Permenkes 50/2017)</span>
            </h4>

            <div class="space-y-2">
              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-2">
                <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 dark:text-slate-200">
                  <input type="checkbox" v-model="form.jentik_kimia" class="rounded text-teal-600" />
                  <span>Larvasidasi Kimia (Abatisasi / Temephos)</span>
                </label>
                <div v-if="form.jentik_kimia" class="grid grid-cols-2 gap-2 pl-6 pt-1">
                  <div>
                    <label class="block text-[10px] text-slate-400">Jumlah Gram</label>
                    <input type="number" v-model="form.jentik_kimia_gram" placeholder="Gram" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
                  </div>
                  <div>
                    <label class="block text-[10px] text-slate-400">Jumlah Sachet</label>
                    <input type="number" v-model="form.jentik_kimia_sachet" placeholder="Sachet" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <label class="flex items-center gap-2 p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 cursor-pointer font-semibold text-slate-800 dark:text-slate-200">
                  <input type="checkbox" v-model="form.jentik_fisik" class="rounded text-teal-600" />
                  <span>Pengendalian Fisik (3M Plus / Kuras / Tutup)</span>
                </label>
                <label class="flex items-center gap-2 p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 cursor-pointer font-semibold text-slate-800 dark:text-slate-200">
                  <input type="checkbox" v-model="form.jentik_biologi" class="rounded text-teal-600" />
                  <span>Biologi (BTI / Ikan Pemakan Jentik)</span>
                </label>
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-2">
                <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 dark:text-slate-200">
                  <input type="checkbox" v-model="form.nyamuk_fogging" class="rounded text-teal-600" />
                  <span>Pengasapan / Fogging / ULV Nyamuk Dewasa</span>
                </label>
                <div v-if="form.nyamuk_fogging" class="grid grid-cols-1 sm:grid-cols-3 gap-2 pl-6 pt-1">
                  <div>
                    <label class="block text-[10px] text-slate-400">Dosis Insektisida (ml)</label>
                    <input type="number" v-model="form.nyamuk_fogging_ml" placeholder="ml" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
                  </div>
                  <div>
                    <label class="block text-[10px] text-slate-400">Merk Insektisida</label>
                    <input type="text" v-model="form.nyamuk_fogging_merk" placeholder="Merk" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
                  </div>
                  <div>
                    <label class="block text-[10px] text-slate-400">Golongan</label>
                    <input type="text" v-model="form.nyamuk_fogging_golongan" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. LALAT -->
        <div v-else-if="(item.jenis_vektor || '').includes('Lalat')" class="space-y-3">
          <div class="p-3.5 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800 space-y-3">
            <h4 class="text-xs font-bold text-amber-900 dark:text-amber-200 flex items-center gap-1.5">
              <Bug class="w-4 h-4 text-amber-600" />
              <span>Pengendalian Kepadatan Lalat</span>
            </h4>

            <div class="space-y-2">
              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-2">
                <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 dark:text-slate-200">
                  <input type="checkbox" v-model="form.lalat_spraying" class="rounded text-amber-600" />
                  <span>Residual Spraying Lalat</span>
                </label>
                <div v-if="form.lalat_spraying" class="pl-6 pt-1">
                  <label class="block text-[10px] text-slate-400">Dosis Insektisida (ml)</label>
                  <input type="number" v-model="form.lalat_spraying_ml" placeholder="Jumlah ml" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
                </div>
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-2">
                <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 dark:text-slate-200">
                  <input type="checkbox" v-model="form.lalat_granule" class="rounded text-amber-600" />
                  <span>Umpan Granule / Fly Bait</span>
                </label>
                <div v-if="form.lalat_granule" class="pl-6 pt-1">
                  <label class="block text-[10px] text-slate-400">Dosis Granule (gram)</label>
                  <input type="number" v-model="form.lalat_granule_gram" placeholder="Jumlah gram" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. KECOA -->
        <div v-else-if="(item.jenis_vektor || '').includes('Kecoa')" class="space-y-3">
          <div class="p-3.5 rounded-2xl bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800 space-y-3">
            <h4 class="text-xs font-bold text-rose-900 dark:text-rose-200 flex items-center gap-1.5">
              <Bug class="w-4 h-4 text-rose-600" />
              <span>Pengendalian Kepadatan Kecoa</span>
            </h4>

            <div class="space-y-2">
              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-2">
                <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 dark:text-slate-200">
                  <input type="checkbox" v-model="form.kecoa_retrapping" class="rounded text-rose-600" />
                  <span>Pemasangan Retrapping Perangkap Kecoa Baru</span>
                </label>
                <div v-if="form.kecoa_retrapping" class="pl-6 pt-1">
                  <label class="block text-[10px] text-slate-400">Jumlah Perangkap (Trap)</label>
                  <input type="number" v-model="form.kecoa_retrapping_trap" placeholder="Jumlah trap" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
                </div>
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-2">
                <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 dark:text-slate-200">
                  <input type="checkbox" v-model="form.kecoa_gel_bait" class="rounded text-rose-600" />
                  <span>Aplikasi Gel Baiting Kecoa</span>
                </label>
                <div v-if="form.kecoa_gel_bait" class="pl-6 pt-1">
                  <label class="block text-[10px] text-slate-400">Dosis Gel Bait (gram)</label>
                  <input type="number" v-model="form.kecoa_gel_bait_gram" placeholder="Jumlah gram" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan / Rekomendasi Khusus</label>
          <textarea
            v-model="form.catatan"
            rows="2"
            placeholder="Deskripsi langkah pengendalian yang dilakukan petugas..."
            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"
          ></textarea>
        </div>
      </div>

      <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
        <button
          type="button"
          @click="!isSubmitting && emit('close')"
          class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-700 transition cursor-pointer"
        >
          Batal
        </button>
        <button
          type="button"
          @click="submitPengendalian()"
          :disabled="isSubmitting"
          class="px-5 py-2 text-xs font-bold rounded-xl bg-gradient-to-r from-teal-600 to-[#005851] text-white hover:brightness-105 transition shadow-sm flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
        >
          <Check class="w-4 h-4" />
          <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan Tindakan Pengendalian' }}</span>
        </button>
      </div>
    </div>
  </ClinicalModal>
</template>
