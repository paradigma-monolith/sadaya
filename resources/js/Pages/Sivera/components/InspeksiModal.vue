<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import ClinicalModal from '../../../Components/ClinicalModal.vue';
import InspeksiBasicVectorsSection from './InspeksiBasicVectorsSection.vue';
import InspeksiLalatSection from './InspeksiLalatSection.vue';
import InspeksiAnophelesSection from './InspeksiAnophelesSection.vue';
import { useUnsavedGuard } from '@/composables/useUnsavedGuard';
import { useFormDraft } from '@/composables/useFormDraft';
import { useDebouncedFn } from '@/composables/useDebouncedFn';
import {
  Bug,
  Droplets,
  Save,
  Utensils,
  Building2,
  X,
} from 'lucide-vue-next';
import {
  getLokasiLalatTop5,
  getLokasiLalatTotalTop5,
  getLokasiLalatRata,
  getLokasiLalatStatus,
} from '../utils';

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  wilkerOptions: {
    type: Array,
    default: () => [],
  },
  initialVektor: {
    type: String,
    default: 'Jentik Aedes sp.',
  },
  editItem: {
    type: Object,
    default: null,
  },
  allowSanitasiTabs: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close', 'saved', 'switch-sanitasi']);

const todayStr = new Date().toISOString().split('T')[0];
const currentMonthStr = todayStr.substring(0, 7);

const isSubmittingInspeksi = ref(false);
const siveraActiveVektor = ref('Jentik Aedes sp.');

const createEmptyTangkapan = () => ({
  '18_19': 0, '19_20': 0, '20_21': 0, '21_22': 0, '22_23': 0, '23_24': 0,
  '24_01': 0, '01_02': 0, '02_03': 0, '03_04': 0, '04_05': 0, '05_06': 0,
});

const createDefaultForms = (defWilker) => ({
  jentik: {
    wilker: defWilker,
    lokasi: '',
    bulan_pelaksanaan: currentMonthStr,
    kondisi_musim: 'Hujan',
    tanggal_mulai: todayStr,
    tanggal_selesai: todayStr,
    wilayah: 'Perimeter',
    jml_rumah_diperiksa: 50,
    jml_kontainer_diperiksa: 150,
    jml_rumah_positif: 0,
    jml_kontainer_positif: 0,
    jenis_kontainer: ['Ember', 'Bak Mandi'],
    jenis_kontainer_lainnya: '',
    tindakan_pengendalian: 'Abatisasi / larvasidasi selektif dan pembersihan genangan air.',
  },
  lalat: {
    wilker: defWilker,
    tanggal: todayStr,
    musim: 'Hujan',
    temperatur: 28.5,
    kelembapan: 70,
    kecepatan_angin: 0.5,
    lokasi_items: [
      { lokasi: 'Dapur & Kantin Dermaga', t: [1, 0, 2, 1, 0, 1, 0, 2, 1, 0] },
    ],
    tindakan_pengendalian: 'Pemasangan fly trap dan sanitasi tempat pembuangan sampah.',
  },
  kecoa: {
    wilker: defWilker,
    tanggal: todayStr,
    lokasi: 'Perimeter Dermaga',
    nama_ruangan: 'Gudang Logistik & Dapur',
    perangkap_terpasang: 10,
    sp_americana: 0,
    sp_germanica: 0,
    sp_lainnya_nama: '',
    sp_lainnya_jml: 0,
    tindakan_pengendalian: 'Aplikasi baiting gel kecoa dan penutupan celah sanitasi.',
  },
  tikus: {
    wilker: defWilker,
    tanggal_mulai: todayStr,
    tanggal_selesai: todayStr,
    lokasi: 'Buffer Area / Perimeter Pelabuhan',
    lokasi_pemasangan: 'Dermaga 01 & Gudang Petikemas',
    jenis_umpan: 'Kelapa Bakar & Ikan Asin',
    spesies_list: [{ nama: 'Rattus tanezumi', custom: '', jumlah: 0 }],
    jml_perangkap_dipasang: 50,
    jml_perangkap_hilang: 0,
    tindakan_pengendalian: 'Rat proofing kapal, pemasangan trap permanen, dan sanitasi sampah dermaga.',
  },
  pinjal: {
    wilker: defWilker,
    tanggal: todayStr,
    lokasi: 'Dermaga Barat & Gudang Kargo',
    jml_tikus_tertangkap: 1,
    pinjal_umum: 0,
    pinjal_khusus: 0,
    tindakan_pengendalian: 'Dusting insektisida pada liang dan perimeter sarang tikus.',
  },
  anophelesJentik: {
    wilker: defWilker,
    tanggal: todayStr,
    lokasi: 'PPI Karangsong Indramayu',
    jenis_habitat: ['Tambak', 'Saluran Air'],
    jenis_habitat_lainnya: '',
    kondisi_pencahayaan: ['Terkena sinar matahari'],
    kondisi_vegetasi: ['Ada vegetasi'],
    kondisi_air: ['Payau', 'Tergenang'],
    jml_habitat_disurvei: 10,
    jml_habitat_positif: 0,
    keterangan: 'Survei habitat perkembangbiakan malaria di pesisir.',
    tindakan_pengendalian: 'Pembersihan lumut dan aplikasi larvasida biologi (BTI).',
  },
  anophelesNyamuk: {
    wilker: defWilker,
    tanggal: todayStr,
    lokasi: 'PPI Karangsong Indramayu',
    spesies: 'Anopheles sp',
    jml_penangkap: 5,
    lama_penangkapan: 12,
    suhu: 27,
    kelembaban: 78,
    kecepatan_angin: 1.2,
    cuaca: 'Cerah Berawan',
    tangkapan_dalam: createEmptyTangkapan(),
    tangkapan_luar: createEmptyTangkapan(),
    keterangan: 'Penangkapan malam hari 18.00 - 06.00 WIB.',
    interpretasi: 'Kepadatan vektor dalam batas aman.',
    tindakan_pengendalian: 'Indoor Residual Spraying (IRS) dan pembagian kelambu berinsektisida.',
  },
});

const defaults = createDefaultForms(props.wilkerOptions?.[0] || 'Wilayah Kerja Patimban');
const formSiveraJentik = ref(defaults.jentik);
const formSiveraLalat = ref(defaults.lalat);
const formSiveraKecoa = ref(defaults.kecoa);
const formSiveraTikus = ref(defaults.tikus);
const formSiveraPinjal = ref(defaults.pinjal);
const formSiveraAnophelesJentik = ref(defaults.anophelesJentik);
const formSiveraAnophelesNyamuk = ref(defaults.anophelesNyamuk);

const initialSnapshot = ref('');
const hasRestoredDraft = ref(false);
const draftStore = useFormDraft('sadaya:sivera:inspeksi-draft');

const serializeForms = () =>
  JSON.stringify({
    vektor: siveraActiveVektor.value,
    jentik: formSiveraJentik.value,
    lalat: formSiveraLalat.value,
    kecoa: formSiveraKecoa.value,
    tikus: formSiveraTikus.value,
    pinjal: formSiveraPinjal.value,
    anophelesJentik: formSiveraAnophelesJentik.value,
    anophelesNyamuk: formSiveraAnophelesNyamuk.value,
  });

const persistDraftDebounced = useDebouncedFn(() => {
  if (!props.open || props.editItem?.id) return;
  draftStore.save({
    vektor: siveraActiveVektor.value,
    jentik: formSiveraJentik.value,
    lalat: formSiveraLalat.value,
    kecoa: formSiveraKecoa.value,
    tikus: formSiveraTikus.value,
    pinjal: formSiveraPinjal.value,
    anophelesJentik: formSiveraAnophelesJentik.value,
    anophelesNyamuk: formSiveraAnophelesNyamuk.value,
  });
}, 350);

const discardDraft = () => {
  const defWilker = props.wilkerOptions?.[0] || 'Wilayah Kerja Patimban';
  const fresh = createDefaultForms(defWilker);
  formSiveraJentik.value = fresh.jentik;
  formSiveraLalat.value = fresh.lalat;
  formSiveraKecoa.value = fresh.kecoa;
  formSiveraTikus.value = fresh.tikus;
  formSiveraPinjal.value = fresh.pinjal;
  formSiveraAnophelesJentik.value = fresh.anophelesJentik;
  formSiveraAnophelesNyamuk.value = fresh.anophelesNyamuk;
  draftStore.clear();
  hasRestoredDraft.value = false;
  initialSnapshot.value = serializeForms();
};

watch(
  () => [props.open, props.editItem, props.initialVektor],
  ([isOpen]) => {
    if (!isOpen) return;
    const defWilker = props.wilkerOptions?.[0] || 'Wilayah Kerja Patimban';
    const fresh = createDefaultForms(defWilker);
    formSiveraJentik.value = fresh.jentik;
    formSiveraLalat.value = fresh.lalat;
    formSiveraKecoa.value = fresh.kecoa;
    formSiveraTikus.value = fresh.tikus;
    formSiveraPinjal.value = fresh.pinjal;
    formSiveraAnophelesJentik.value = fresh.anophelesJentik;
    formSiveraAnophelesNyamuk.value = fresh.anophelesNyamuk;

    const item = props.editItem;
    if (item) {
      const jv = item.jenis_vektor || '';
      const d = item.detail_data || {};
      if (jv.includes('Jentik Aedes')) {
        siveraActiveVektor.value = 'Jentik Aedes sp.';
        formSiveraJentik.value = {
          wilker: item.wilker || defWilker,
          lokasi: item.lokasi || '',
          bulan_pelaksanaan: d.bulan_pelaksanaan || currentMonthStr,
          kondisi_musim: d.kondisi_musim || 'Hujan',
          tanggal_mulai: d.tanggal_mulai || item.tanggal || todayStr,
          tanggal_selesai: d.tanggal_selesai || item.tanggal || todayStr,
          wilayah: d.wilayah || 'Perimeter',
          jml_rumah_diperiksa: Number(d.jml_rumah_diperiksa || 50),
          jml_kontainer_diperiksa: Number(d.jml_kontainer_diperiksa || 150),
          jml_rumah_positif: Number(d.jml_rumah_positif || 0),
          jml_kontainer_positif: Number(d.jml_kontainer_positif || 0),
          jenis_kontainer: Array.isArray(d.jenis_kontainer)
            ? d.jenis_kontainer
            : (d.jenis_kontainer ? d.jenis_kontainer.split(',') : ['Ember']),
          jenis_kontainer_lainnya: d.jenis_kontainer_lainnya || '',
          tindakan_pengendalian: item.tindakan_pengendalian || '',
        };
      } else if (jv.includes('Lalat')) {
        siveraActiveVektor.value = 'Lalat';
        const lokItems = d.detail_lokasi && Array.isArray(d.detail_lokasi) && d.detail_lokasi.length > 0
          ? d.detail_lokasi.map((dl) => ({ lokasi: dl.lokasi || 'Lokasi', t: dl.t || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0] }))
          : [{ lokasi: item.lokasi || 'Dapur / Kantin', t: d.t || [1, 0, 2, 1, 0, 1, 0, 2, 1, 0] }];
        formSiveraLalat.value = {
          wilker: item.wilker || defWilker,
          tanggal: item.tanggal || todayStr,
          musim: d.musim || 'Hujan',
          temperatur: Number(d.temperatur || 28.5),
          kelembapan: Number(d.kelembapan || 70),
          kecepatan_angin: Number(d.kecepatan_angin || 0.5),
          lokasi_items: lokItems,
          tindakan_pengendalian: item.tindakan_pengendalian || '',
        };
      } else if (jv.includes('Kecoa')) {
        siveraActiveVektor.value = 'Kecoa';
        formSiveraKecoa.value = {
          wilker: item.wilker || defWilker,
          tanggal: item.tanggal || todayStr,
          lokasi: item.lokasi || '',
          nama_ruangan: d.nama_ruangan || '',
          perangkap_terpasang: Number(d.perangkap_terpasang || 10),
          sp_americana: Number(d.sp_americana || 0),
          sp_germanica: Number(d.sp_germanica || 0),
          sp_lainnya_nama: d.sp_lainnya_nama || '',
          sp_lainnya_jml: Number(d.sp_lainnya_jml || 0),
          tindakan_pengendalian: item.tindakan_pengendalian || '',
        };
      } else if (jv.includes('Tikus')) {
        siveraActiveVektor.value = 'Tikus';
        let spList = [{ nama: 'Rattus tanezumi', custom: '', jumlah: 0 }];
        if (d.spesies_list && Array.isArray(d.spesies_list)) {
          spList = d.spesies_list;
        } else if (d.spesies_tertangkap && typeof d.spesies_tertangkap === 'object') {
          spList = Object.entries(d.spesies_tertangkap).map(([k, v]) => ({
            nama: k,
            custom: '',
            jumlah: Number(v),
          }));
        }
        formSiveraTikus.value = {
          wilker: item.wilker || defWilker,
          tanggal_mulai: d.tanggal_mulai || item.tanggal || todayStr,
          tanggal_selesai: d.tanggal_selesai || item.tanggal || todayStr,
          lokasi: item.lokasi || '',
          lokasi_pemasangan: d.lokasi_pemasangan || '',
          jenis_umpan: d.jenis_umpan || 'Kelapa bakar',
          jml_perangkap_dipasang: Number(d.jml_perangkap_dipasang || 50),
          jml_perangkap_hilang: Number(d.jml_perangkap_hilang || 0),
          spesies_list: spList,
          tindakan_pengendalian: item.tindakan_pengendalian || '',
        };
      } else if (jv.includes('Pinjal')) {
        siveraActiveVektor.value = 'Pinjal';
        formSiveraPinjal.value = {
          wilker: item.wilker || defWilker,
          tanggal: item.tanggal || todayStr,
          lokasi: item.lokasi || '',
          jml_tikus_tertangkap: Number(d.jml_tikus_tertangkap || 1),
          pinjal_umum: Number(d.pinjal_umum || 0),
          pinjal_khusus: Number(d.pinjal_khusus || 0),
          tindakan_pengendalian: item.tindakan_pengendalian || '',
        };
      } else if (jv.includes('Jentik Anopheles')) {
        siveraActiveVektor.value = 'Jentik Anopheles';
        formSiveraAnophelesJentik.value = {
          wilker: item.wilker || defWilker,
          tanggal: item.tanggal || todayStr,
          lokasi: item.lokasi || '',
          jenis_habitat: Array.isArray(d.jenis_habitat) ? d.jenis_habitat : (d.jenis_habitat ? d.jenis_habitat.split(',') : ['Tambak']),
          jenis_habitat_lainnya: d.jenis_habitat_lainnya || '',
          kondisi_pencahayaan: Array.isArray(d.kondisi_pencahayaan) ? d.kondisi_pencahayaan : [d.kondisi_pencahayaan || 'Terkena sinar matahari'],
          kondisi_vegetasi: Array.isArray(d.kondisi_vegetasi) ? d.kondisi_vegetasi : [d.kondisi_vegetasi || 'Ada vegetasi'],
          kondisi_air: Array.isArray(d.kondisi_air) ? d.kondisi_air : [d.kondisi_air || 'Jernih'],
          jml_habitat_disurvei: Number(d.jml_habitat_disurvei || 10),
          jml_habitat_positif: Number(d.jml_habitat_positif || 0),
          keterangan: d.keterangan || '',
          tindakan_pengendalian: item.tindakan_pengendalian || '',
        };
      } else if (jv.includes('Nyamuk Anopheles')) {
        siveraActiveVektor.value = 'Nyamuk Anopheles';
        formSiveraAnophelesNyamuk.value = {
          wilker: item.wilker || defWilker,
          tanggal: item.tanggal || todayStr,
          lokasi: item.lokasi || '',
          spesies: d.spesies || 'Anopheles sp',
          jml_penangkap: Number(d.jml_penangkap || 5),
          lama_penangkapan: Number(d.lama_penangkapan || 12),
          suhu: Number(d.suhu || 27),
          kelembaban: Number(d.kelembaban || 78),
          kecepatan_angin: Number(d.kecepatan_angin || 1.2),
          cuaca: d.cuaca || 'Cerah Berawan',
          tangkapan_dalam: { ...createEmptyTangkapan(), ...(d.tangkapan_dalam || {}) },
          tangkapan_luar: { ...createEmptyTangkapan(), ...(d.tangkapan_luar || {}) },
          keterangan: d.keterangan || '',
          interpretasi: d.interpretasi || '',
          tindakan_pengendalian: item.tindakan_pengendalian || '',
        };
      }
    } else {
      const savedDraft = draftStore.restore();
      if (savedDraft) {
        if (savedDraft.vektor) siveraActiveVektor.value = savedDraft.vektor;
        if (savedDraft.jentik) formSiveraJentik.value = { ...fresh.jentik, ...savedDraft.jentik };
        if (savedDraft.lalat) formSiveraLalat.value = { ...fresh.lalat, ...savedDraft.lalat };
        if (savedDraft.kecoa) formSiveraKecoa.value = { ...fresh.kecoa, ...savedDraft.kecoa };
        if (savedDraft.tikus) formSiveraTikus.value = { ...fresh.tikus, ...savedDraft.tikus };
        if (savedDraft.pinjal) formSiveraPinjal.value = { ...fresh.pinjal, ...savedDraft.pinjal };
        if (savedDraft.anophelesJentik) formSiveraAnophelesJentik.value = { ...fresh.anophelesJentik, ...savedDraft.anophelesJentik };
        if (savedDraft.anophelesNyamuk) formSiveraAnophelesNyamuk.value = { ...fresh.anophelesNyamuk, ...savedDraft.anophelesNyamuk };
        hasRestoredDraft.value = true;
      } else {
        const target = props.initialVektor && props.initialVektor !== 'sivera' ? props.initialVektor : 'Jentik Aedes sp.';
        siveraActiveVektor.value = target === 'Jentik Aedes' ? 'Jentik Aedes sp.' : target;
        hasRestoredDraft.value = false;
      }
    }

    initialSnapshot.value = serializeForms();
  },
  { immediate: true }
);

watch(
  [
    siveraActiveVektor,
    formSiveraJentik,
    formSiveraLalat,
    formSiveraKecoa,
    formSiveraTikus,
    formSiveraPinjal,
    formSiveraAnophelesJentik,
    formSiveraAnophelesNyamuk,
  ],
  () => {
    persistDraftDebounced();
  },
  { deep: true }
);

const isDirty = computed(() => props.open && initialSnapshot.value !== '' && serializeForms() !== initialSnapshot.value);
const { allowNextNavigation } = useUnsavedGuard(isDirty);

// Jentik Aedes calculations
const hiJentik = computed(() => {
  const rd = Number(formSiveraJentik.value.jml_rumah_diperiksa || 0);
  const rp = Number(formSiveraJentik.value.jml_rumah_positif || 0);
  return rd > 0 ? Number(((rp / rd) * 100).toFixed(2)) : 0;
});
const ciJentik = computed(() => {
  const kd = Number(formSiveraJentik.value.jml_kontainer_diperiksa || 0);
  const kp = Number(formSiveraJentik.value.jml_kontainer_positif || 0);
  return kd > 0 ? Number(((kp / kd) * 100).toFixed(2)) : 0;
});
const biJentik = computed(() => {
  const rd = Number(formSiveraJentik.value.jml_rumah_diperiksa || 0);
  const kp = Number(formSiveraJentik.value.jml_kontainer_positif || 0);
  return rd > 0 ? Number(((kp / rd) * 100).toFixed(2)) : 0;
});
const abjJentik = computed(() => Number((100 - hiJentik.value).toFixed(2)));
const statusJentik = computed(() =>
  hiJentik.value === 0 && ciJentik.value === 0 && biJentik.value === 0 ? 'Memenuhi Syarat' : 'Tidak Memenuhi Syarat'
);

// Lalat helpers & computations
const addLokasiLalat = () => {
  formSiveraLalat.value.lokasi_items.push({
    lokasi: `Lokasi ${formSiveraLalat.value.lokasi_items.length + 1}`,
    t: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
  });
};
const removeLokasiLalat = (idx) => {
  if (formSiveraLalat.value.lokasi_items.length > 1) {
    formSiveraLalat.value.lokasi_items.splice(idx, 1);
  }
};
const flyIndexRata = computed(() => {
  const items = formSiveraLalat.value.lokasi_items;
  if (!items || items.length === 0) return 0;
  const sum = items.reduce((acc, it) => acc + getLokasiLalatRata(it), 0);
  return Number((sum / items.length).toFixed(2));
});
const flyIndexMax = computed(() => {
  const items = formSiveraLalat.value.lokasi_items;
  if (!items || items.length === 0) return 0;
  return Math.max(...items.map((it) => getLokasiLalatRata(it)));
});
const statusLalat = computed(() => {
  const anyTms = formSiveraLalat.value.lokasi_items.some((it) => getLokasiLalatRata(it) >= 2.0);
  return anyTms || flyIndexRata.value >= 2.0 ? 'Tidak Memenuhi Syarat' : 'Memenuhi Syarat';
});

// Kecoa computations
const totalKecoaTertangkap = computed(() =>
  Number(formSiveraKecoa.value.sp_americana || 0) +
  Number(formSiveraKecoa.value.sp_germanica || 0) +
  Number(formSiveraKecoa.value.sp_lainnya_jml || 0)
);
const indeksKecoa = computed(() => {
  const trap = Number(formSiveraKecoa.value.perangkap_terpasang || 0);
  return trap > 0 ? Number((totalKecoaTertangkap.value / trap).toFixed(2)) : 0;
});
const statusKecoa = computed(() => (indeksKecoa.value < 2.0 ? 'Memenuhi Syarat' : 'Tidak Memenuhi Syarat'));

// Tikus computations
const addSpesiesTikusRow = () => {
  formSiveraTikus.value.spesies_list.push({ nama: 'Rattus norvegicus', custom: '', jumlah: 0 });
};
const removeSpesiesTikusRow = (idx) => {
  formSiveraTikus.value.spesies_list.splice(idx, 1);
};
const totalTikusTertangkap = computed(() =>
  formSiveraTikus.value.spesies_list.reduce((acc, row) => acc + Number(row.jumlah || 0), 0)
);
const successTrapTikus = computed(() => {
  const dipasang = Number(formSiveraTikus.value.jml_perangkap_dipasang || 0);
  return dipasang > 0 ? Number(((totalTikusTertangkap.value / dipasang) * 100).toFixed(2)) : 0;
});
const statusTikus = computed(() => (successTrapTikus.value < 1.0 ? 'Memenuhi Syarat' : 'Tidak Memenuhi Syarat'));

// Pinjal computations
const indeksPinjalUmum = computed(() => {
  const tk = Number(formSiveraPinjal.value.jml_tikus_tertangkap || 0);
  return tk > 0 ? Number((Number(formSiveraPinjal.value.pinjal_umum || 0) / tk).toFixed(2)) : 0;
});
const indeksPinjalKhusus = computed(() => {
  const tk = Number(formSiveraPinjal.value.jml_tikus_tertangkap || 0);
  return tk > 0 ? Number((Number(formSiveraPinjal.value.pinjal_khusus || 0) / tk).toFixed(2)) : 0;
});
const statusPinjal = computed(() =>
  indeksPinjalUmum.value < 2.0 && indeksPinjalKhusus.value < 1.0 ? 'Memenuhi Syarat' : 'Tidak Memenuhi Syarat'
);

// Anopheles Jentik computations
const indeksHabitatAnopheles = computed(() => {
  const disurvei = Number(formSiveraAnophelesJentik.value.jml_habitat_disurvei || 0);
  const positif = Number(formSiveraAnophelesJentik.value.jml_habitat_positif || 0);
  return disurvei > 0 ? Number(((positif / disurvei) * 100).toFixed(2)) : 0;
});
const statusAnophelesJentik = computed(() =>
  indeksHabitatAnopheles.value < 1.0 ? 'Memenuhi Syarat' : 'Tidak Memenuhi Syarat'
);

// Anopheles Nyamuk Dewasa computations
const totalDalamNyamuk = computed(() =>
  Object.values(formSiveraAnophelesNyamuk.value.tangkapan_dalam || {}).reduce((a, b) => Number(a) + Number(b), 0)
);
const totalLuarNyamuk = computed(() =>
  Object.values(formSiveraAnophelesNyamuk.value.tangkapan_luar || {}).reduce((a, b) => Number(a) + Number(b), 0)
);
const totalTertangkapNyamuk = computed(() => totalDalamNyamuk.value + totalLuarNyamuk.value);
const manHours = computed(() =>
  Number(formSiveraAnophelesNyamuk.value.jml_penangkap || 1) * Number(formSiveraAnophelesNyamuk.value.lama_penangkapan || 12)
);
const mhdDalam = computed(() =>
  manHours.value > 0 ? Number((totalDalamNyamuk.value / manHours.value).toFixed(4)) : 0
);
const mhdLuar = computed(() =>
  manHours.value > 0 ? Number((totalLuarNyamuk.value / manHours.value).toFixed(4)) : 0
);
const mbrAnopheles = computed(() =>
  manHours.value > 0 ? Number((totalTertangkapNyamuk.value / manHours.value).toFixed(4)) : 0
);
const adaAnopheles = computed(() => (totalTertangkapNyamuk.value > 0 ? 'Ya' : 'Tidak'));
const statusAnophelesNyamuk = computed(() =>
  totalTertangkapNyamuk.value === 0 ? 'Memenuhi Syarat' : 'Tidak Memenuhi Syarat'
);

const submitInspeksi = async () => {
  if (isSubmittingInspeksi.value) return;
  isSubmittingInspeksi.value = true;
  try {
    let payload = {};
    const activeV = siveraActiveVektor.value;

    if (activeV === 'Jentik Aedes sp.' || activeV === 'Jentik Aedes') {
      payload = {
        jenis_vektor: 'Jentik Aedes',
        wilker: formSiveraJentik.value.wilker,
        lokasi: formSiveraJentik.value.lokasi || 'Perimeter',
        tanggal: formSiveraJentik.value.tanggal_mulai,
        parameter_utama: `HI: ${hiJentik.value}%, CI: ${ciJentik.value}%, BI: ${biJentik.value} (ABJ: ${abjJentik.value}%)`,
        nilai_indeks: abjJentik.value,
        baku_mutu_status: statusJentik.value,
        tindakan_pengendalian: formSiveraJentik.value.tindakan_pengendalian,
        detail_data: {
          ...formSiveraJentik.value,
          abj: abjJentik.value,
          hi: hiJentik.value,
          ci: ciJentik.value,
          bi: biJentik.value,
        },
      };
    } else if (activeV === 'Lalat') {
      const formattedLokasiItems = formSiveraLalat.value.lokasi_items.map((it) => ({
        lokasi: it.lokasi,
        t: (it.t || []).map(Number),
        top_5: getLokasiLalatTop5(it),
        total_n: getLokasiLalatTotalTop5(it),
        rata_rata: getLokasiLalatRata(it),
        status: getLokasiLalatStatus(it),
      }));
      const summaryLokasiNames = formSiveraLalat.value.lokasi_items.map((it) => it.lokasi).filter(Boolean).join(', ');
      payload = {
        jenis_vektor: 'Lalat',
        wilker: formSiveraLalat.value.wilker,
        lokasi: summaryLokasiNames || 'Dapur / Kantin',
        tanggal: formSiveraLalat.value.tanggal,
        parameter_utama: `Fly Index: ${flyIndexRata.value} (${formattedLokasiItems.length} Titik)`,
        nilai_indeks: flyIndexRata.value,
        baku_mutu_status: statusLalat.value,
        tindakan_pengendalian: formSiveraLalat.value.tindakan_pengendalian,
        detail_data: {
          ...formSiveraLalat.value,
          detail_lokasi: formattedLokasiItems,
          fly_index_rata: flyIndexRata.value,
          fly_index_max: flyIndexMax.value,
        },
      };
    } else if (activeV === 'Kecoa') {
      payload = {
        jenis_vektor: 'Kecoa',
        wilker: formSiveraKecoa.value.wilker,
        lokasi: formSiveraKecoa.value.lokasi || 'Kantin / Gudang',
        tanggal: formSiveraKecoa.value.tanggal,
        parameter_utama: `Indeks: ${indeksKecoa.value} (${totalKecoaTertangkap.value} ekor / ${formSiveraKecoa.value.perangkap_terpasang || 0} trap)`,
        nilai_indeks: indeksKecoa.value,
        baku_mutu_status: statusKecoa.value,
        tindakan_pengendalian: formSiveraKecoa.value.tindakan_pengendalian,
        detail_data: {
          ...formSiveraKecoa.value,
          total_tertangkap: totalKecoaTertangkap.value,
          indeks: indeksKecoa.value,
        },
      };
    } else if (activeV === 'Tikus') {
      payload = {
        jenis_vektor: 'Tikus',
        wilker: formSiveraTikus.value.wilker,
        lokasi: formSiveraTikus.value.lokasi || 'Buffer Area',
        tanggal: formSiveraTikus.value.tanggal_mulai,
        parameter_utama: `Success Trap: ${successTrapTikus.value}% (${totalTikusTertangkap.value} ekor / ${formSiveraTikus.value.jml_perangkap_dipasang || 0} trap)`,
        nilai_indeks: successTrapTikus.value,
        baku_mutu_status: statusTikus.value,
        tindakan_pengendalian: formSiveraTikus.value.tindakan_pengendalian,
        detail_data: {
          ...formSiveraTikus.value,
          total_tertangkap: totalTikusTertangkap.value,
          success_trap: successTrapTikus.value,
        },
      };
    } else if (activeV === 'Pinjal') {
      payload = {
        jenis_vektor: 'Pinjal',
        wilker: formSiveraPinjal.value.wilker,
        lokasi: formSiveraPinjal.value.lokasi || 'Kandang / Perimeter',
        tanggal: formSiveraPinjal.value.tanggal,
        parameter_utama: `IP Umum: ${indeksPinjalUmum.value} | Khusus: ${indeksPinjalKhusus.value}`,
        nilai_indeks: indeksPinjalUmum.value,
        baku_mutu_status: statusPinjal.value,
        tindakan_pengendalian: formSiveraPinjal.value.tindakan_pengendalian,
        detail_data: {
          ...formSiveraPinjal.value,
          ip_umum: indeksPinjalUmum.value,
          ip_khusus: indeksPinjalKhusus.value,
        },
      };
    } else if (activeV === 'Jentik Anopheles') {
      payload = {
        jenis_vektor: 'Jentik Anopheles',
        wilker: formSiveraAnophelesJentik.value.wilker,
        lokasi: formSiveraAnophelesJentik.value.lokasi || 'Rawa / Saluran Terbuka',
        tanggal: formSiveraAnophelesJentik.value.tanggal,
        parameter_utama: `Indeks Habitat: ${indeksHabitatAnopheles.value} (${formSiveraAnophelesJentik.value.jml_habitat_positif || 0}/${formSiveraAnophelesJentik.value.jml_habitat_disurvei || 0} habitat)`,
        nilai_indeks: indeksHabitatAnopheles.value,
        baku_mutu_status: statusAnophelesJentik.value,
        tindakan_pengendalian: formSiveraAnophelesJentik.value.tindakan_pengendalian,
        detail_data: {
          ...formSiveraAnophelesJentik.value,
          indeks_habitat: indeksHabitatAnopheles.value,
        },
      };
    } else if (activeV === 'Nyamuk Anopheles') {
      payload = {
        jenis_vektor: 'Nyamuk Anopheles',
        wilker: formSiveraAnophelesNyamuk.value.wilker || formSiveraAnophelesNyamuk.value.lokasi || 'Pesisir',
        lokasi: formSiveraAnophelesNyamuk.value.lokasi || 'Pesisir',
        tanggal: formSiveraAnophelesNyamuk.value.tanggal,
        parameter_utama: `MBR: ${mbrAnopheles.value} | MHD D: ${mhdDalam.value}, L: ${mhdLuar.value} (${totalTertangkapNyamuk.value} ekor)`,
        nilai_indeks: mbrAnopheles.value,
        baku_mutu_status: statusAnophelesNyamuk.value,
        tindakan_pengendalian: formSiveraAnophelesNyamuk.value.tindakan_pengendalian,
        detail_data: {
          ...formSiveraAnophelesNyamuk.value,
          total_dalam: totalDalamNyamuk.value,
          total_luar: totalLuarNyamuk.value,
          total_tertangkap: totalTertangkapNyamuk.value,
          mbr: mbrAnopheles.value,
          mhd_dalam: mhdDalam.value,
          mhd_luar: mhdLuar.value,
        },
      };
    }

    const res = props.editItem?.id
      ? await axios.put(`/sivera/${props.editItem.id}`, payload)
      : await axios.post('/sivera', payload);
    toast.success(res.data.message || 'Data surveilans berhasil disimpan');
    if (!props.editItem?.id) {
      draftStore.clear();
      hasRestoredDraft.value = false;
    }
    allowNextNavigation();
    initialSnapshot.value = serializeForms();
    emit('saved');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan data.');
  } finally {
    isSubmittingInspeksi.value = false;
  }
};
</script>

<template>
  <ClinicalModal
    :open="open"
    :title="editItem?.id ? 'Perbarui Data Surveilans SIVERA' : 'Catat Data Surveilans Vektor (SIVERA)'"
    max-width="max-w-4xl"
    :close-on-outside="!isSubmittingInspeksi"
    @close="!isSubmittingInspeksi && emit('close')"
  >
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-h-[92vh] overflow-hidden flex flex-col shadow-2xl animate-in fade-in zoom-in-95 duration-200">
      <!-- Modal Header -->
      <div class="px-6 py-4 bg-gradient-to-r from-[#005851] to-[#00A99D] text-white flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Bug class="w-5 h-5 text-white" />
          <h3 class="text-base font-bold tracking-tight">{{ editItem?.id ? 'Perbarui Data Surveilans SIVERA' : 'Catat Data Surveilans Vektor (SIVERA)' }}</h3>
        </div>
        <button
          type="button"
          @click="!isSubmittingInspeksi && emit('close')"
          aria-label="Tutup Modal Input Surveilans"
          title="Tutup"
          class="p-1 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <div
        v-if="allowSanitasiTabs && !editItem?.id"
        class="flex border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 p-2 gap-2 overflow-x-auto"
      >
        <button
          type="button"
          @click="allowNextNavigation(); emit('switch-sanitasi', 'tpp')"
          class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer shrink-0 text-slate-500 hover:text-amber-600 dark:hover:text-amber-400"
        >
          <Utensils class="w-4 h-4" />
          <span>Inspeksi TPP (Pangan)</span>
        </button>
        <button
          type="button"
          @click="allowNextNavigation(); emit('switch-sanitasi', 'sab')"
          class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer shrink-0 text-slate-500 hover:text-cyan-600 dark:hover:text-cyan-400"
        >
          <Droplets class="w-4 h-4" />
          <span>Inspeksi SAB (Air Bersih)</span>
        </button>
        <button
          type="button"
          @click="allowNextNavigation(); emit('switch-sanitasi', 'hsbu')"
          class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer shrink-0 text-slate-500 hover:text-[#00A99D]"
        >
          <Building2 class="w-4 h-4" />
          <span>Inspeksi HSBU (Gedung)</span>
        </button>
        <button
          type="button"
          class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer shrink-0 bg-white dark:bg-slate-900 text-[#005851] dark:text-[#00A99D] shadow-xs border border-slate-200 dark:border-slate-700"
        >
          <Bug class="w-4 h-4" />
          <span>Surveilans SIVERA (Vektor)</span>
        </button>
      </div>

      <!-- Modal Body Form -->
      <div class="p-6 max-h-[75vh] overflow-y-auto space-y-6">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-2 border-b border-slate-200 dark:border-slate-800">
          <button
            v-for="vName in ['Jentik Aedes sp.', 'Lalat', 'Kecoa', 'Tikus', 'Pinjal', 'Jentik Anopheles', 'Nyamuk Anopheles']"
            :key="vName"
            type="button"
            @click="siveraActiveVektor = vName"
            class="px-3 py-1.5 rounded-full text-xs font-bold transition shrink-0 cursor-pointer flex items-center gap-1.5"
            :class="siveraActiveVektor === vName ? 'bg-gradient-to-r from-[#005851] to-[#00A99D] text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
          >
            <Bug class="w-3.5 h-3.5" />
            <span>{{ vName }}</span>
          </button>
        </div>

        <div
          v-if="hasRestoredDraft && !editItem?.id"
          class="flex items-center justify-between gap-3 px-3.5 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-xs text-amber-800 dark:text-amber-300"
        >
          <span>Draf lokal terakhir dipulihkan otomatis agar data lapangan tidak hilang.</span>
          <button
            type="button"
            @click="discardDraft"
            class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-700 font-semibold text-amber-800 dark:text-amber-200 hover:bg-amber-100/70 transition cursor-pointer shrink-0"
          >
            Reset Draf
          </button>
        </div>

        <!-- 4.1 Jentik Aedes, 4.3 Kecoa, 4.4 Tikus, 4.5 Pinjal -->
        <InspeksiBasicVectorsSection
          v-if="['Jentik Aedes sp.', 'Kecoa', 'Tikus', 'Pinjal'].includes(siveraActiveVektor)"
          :active-vektor="siveraActiveVektor"
          :wilker-options="wilkerOptions"
          :form-jentik="formSiveraJentik"
          :form-kecoa="formSiveraKecoa"
          :form-tikus="formSiveraTikus"
          :form-pinjal="formSiveraPinjal"
          :hi-jentik="hiJentik"
          :ci-jentik="ciJentik"
          :bi-jentik="biJentik"
          :abj-jentik="abjJentik"
          :status-jentik="statusJentik"
          :total-kecoa-tertangkap="totalKecoaTertangkap"
          :indeks-kecoa="indeksKecoa"
          :status-kecoa="statusKecoa"
          :total-tikus-tertangkap="totalTikusTertangkap"
          :success-trap-tikus="successTrapTikus"
          :status-tikus="statusTikus"
          :indeks-pinjal-umum="indeksPinjalUmum"
          :indeks-pinjal-khusus="indeksPinjalKhusus"
          @add-tikus-row="addSpesiesTikusRow()"
          @remove-tikus-row="removeSpesiesTikusRow"
        />

        <!-- 4.2 LALAT -->
        <InspeksiLalatSection
          v-if="siveraActiveVektor === 'Lalat'"
          :form="formSiveraLalat"
          :wilker-options="wilkerOptions"
          :fly-index-rata="flyIndexRata"
          :status-lalat="statusLalat"
          @add-lokasi="addLokasiLalat"
          @remove-lokasi="removeLokasiLalat"
        />

        <!-- 4.6 JENTIK ANOPHELES & 4.7 NYAMUK ANOPHELES DEWASA -->
        <InspeksiAnophelesSection
          v-if="siveraActiveVektor === 'Jentik Anopheles' || siveraActiveVektor === 'Nyamuk Anopheles'"
          :active-vektor="siveraActiveVektor"
          :wilker-options="wilkerOptions"
          :form-jentik="formSiveraAnophelesJentik"
          :form-nyamuk="formSiveraAnophelesNyamuk"
          :indeks-habitat-anopheles="indeksHabitatAnopheles"
          :status-anopheles-jentik="statusAnophelesJentik"
          :man-hours="manHours"
          :total-dalam-nyamuk="totalDalamNyamuk"
          :total-luar-nyamuk="totalLuarNyamuk"
          :total-tertangkap-nyamuk="totalTertangkapNyamuk"
          :mhd-dalam="mhdDalam"
          :mhd-luar="mhdLuar"
          :mbr-anopheles="mbrAnopheles"
          :ada-anopheles="adaAnopheles"
          :status-anopheles-nyamuk="statusAnophelesNyamuk"
        />
      </div>

      <!-- Modal Footer -->
      <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
        <button
          type="button"
          @click="!isSubmittingInspeksi && emit('close')"
          class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer"
        >
          Batal
        </button>
        <button
          type="button"
          @click="submitInspeksi()"
          :disabled="isSubmittingInspeksi"
          class="px-5 py-2 rounded-xl bg-gradient-to-r from-[#00A99D] to-[#005851] text-xs font-bold text-white hover:brightness-105 transition shadow-sm flex items-center gap-2 cursor-pointer disabled:opacity-50"
        >
          <Save class="w-4 h-4" />
          <span>{{ isSubmittingInspeksi ? 'Menyimpan...' : (editItem?.id ? 'Simpan Perubahan' : 'Simpan Data Surveilans') }}</span>
        </button>
      </div>
    </div>
  </ClinicalModal>
</template>
