<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import ClinicalModal from '../../../Components/ClinicalModal.vue';
import { useUnsavedGuard } from '@/composables/useUnsavedGuard';
import {
  Bug,
  Droplets,
  MapPin,
  Plus,
  Trash2,
  Clock,
  Save,
  Utensils,
  Building2,
  X,
} from 'lucide-vue-next';
import {
  getRiskBadgeClass,
  getLokasiLalatTop5,
  getLokasiLalatTotalTop5,
  getLokasiLalatRata,
  getLokasiLalatStatus,
  standardSpesiesTikus,
  standardHabitatAnopheles,
  anophelesJamSlots,
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
      const target = props.initialVektor && props.initialVektor !== 'sivera' ? props.initialVektor : 'Jentik Aedes sp.';
      siveraActiveVektor.value = target === 'Jentik Aedes' ? 'Jentik Aedes sp.' : target;
    }

    initialSnapshot.value = serializeForms();
  },
  { immediate: true }
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
      <div class="px-6 py-4 bg-gradient-to-r from-indigo-700 via-indigo-600 to-[#00A99D] text-white flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Bug class="w-5 h-5 text-indigo-200" />
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
          class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer shrink-0 bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-xs border border-slate-200 dark:border-slate-700"
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
            :class="siveraActiveVektor === vName ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
          >
            <Bug class="w-3.5 h-3.5" />
            <span>{{ vName }}</span>
          </button>
        </div>

        <!-- 4.1 JENTIK AEDES SP. -->
        <div v-if="siveraActiveVektor === 'Jentik Aedes sp.'" class="space-y-4">
          <div class="p-3 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-200/60 dark:border-indigo-800/50">
            <span class="text-xs font-bold text-indigo-900 dark:text-indigo-300 flex items-center gap-1.5">
              <Droplets class="w-4 h-4 text-indigo-600" />
              <span>Surveilans Jentik Aedes sp. (Demam Berdarah Dengue)</span>
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
              <select v-model="formSiveraJentik.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi Pemantauan</label>
              <input type="text" v-model="formSiveraJentik.lokasi" placeholder="Contoh: Perimeter Bandara / Dermaga" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Bulan Pelaksanaan</label>
              <input type="month" v-model="formSiveraJentik.bulan_pelaksanaan" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kondisi Musim</label>
              <select v-model="formSiveraJentik.kondisi_musim" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option value="Hujan">Hujan</option>
                <option value="Kemarau">Kemarau</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah</label>
              <select v-model="formSiveraJentik.wilayah" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option value="Perimeter">Perimeter</option>
                <option value="Buffer">Buffer Area</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Mulai</label>
              <input type="date" v-model="formSiveraJentik.tanggal_mulai" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Selesai</label>
              <input type="date" v-model="formSiveraJentik.tanggal_selesai" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
            </div>
          </div>

          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Hasil Pemeriksaan Jentik Kontainer &amp; Bangunan</h4>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Rumah Diperiksa (A)</label>
                <input type="number" min="0" v-model="formSiveraJentik.jml_rumah_diperiksa" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
              </div>
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Kontainer Diperiksa (B)</label>
                <input type="number" min="0" v-model="formSiveraJentik.jml_kontainer_diperiksa" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
              </div>
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Rumah Positif (C)</label>
                <input type="number" min="0" v-model="formSiveraJentik.jml_rumah_positif" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono text-rose-600 font-bold" />
              </div>
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Kontainer Positif (D)</label>
                <input type="number" min="0" v-model="formSiveraJentik.jml_kontainer_positif" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono text-rose-600 font-bold" />
              </div>
            </div>

            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-indigo-100 dark:border-indigo-950 flex flex-wrap items-center justify-between gap-3 text-xs">
              <div class="flex flex-wrap items-center gap-4">
                <div><span class="text-slate-400">House Index (HI):</span> <strong class="text-indigo-600 font-mono text-sm ml-1">{{ hiJentik }}%</strong></div>
                <div><span class="text-slate-400">Container Index (CI):</span> <strong class="text-indigo-600 font-mono text-sm ml-1">{{ ciJentik }}%</strong></div>
                <div><span class="text-slate-400">Breteau Index (BI):</span> <strong class="text-indigo-600 font-mono text-sm ml-1">{{ biJentik }}</strong></div>
                <div><span class="text-slate-400">Angka Bebas Jentik (ABJ):</span> <strong class="text-emerald-600 font-mono text-sm ml-1">{{ abjJentik }}%</strong></div>
              </div>
              <div>
                <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getRiskBadgeClass(statusJentik)">{{ statusJentik }}</span>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Jenis Kontainer Positif yang Ditemukan</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
              <label v-for="cName in ['Ban Bekas', 'Ember', 'Bak Mandi', 'Tatakan Dispenser Air Minum', 'Tanaman Penyimpan Air']" :key="cName" class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 cursor-pointer">
                <input type="checkbox" :value="cName" v-model="formSiveraJentik.jenis_kontainer" />
                <span>{{ cName }}</span>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tindakan Pengendalian</label>
            <textarea v-model="formSiveraJentik.tindakan_pengendalian" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
          </div>
        </div>

        <!-- 4.2 LALAT -->
        <div v-if="siveraActiveVektor === 'Lalat'" class="space-y-4">
          <div class="p-3 rounded-2xl bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-800/50">
            <span class="text-xs font-bold text-amber-900 dark:text-amber-300 flex items-center gap-1.5">
              <Bug class="w-4 h-4 text-amber-600" />
              <span>Surveilans Kepadatan Lalat (Metode Fly Grill - Multi-Lokasi &amp; Top 5 Tertinggi)</span>
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
              <select v-model="formSiveraLalat.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
              <input type="date" v-model="formSiveraLalat.tanggal" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Musim</label>
              <select v-model="formSiveraLalat.musim" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option value="Hujan">Hujan</option>
                <option value="Kemarau">Kemarau</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs">
            <div>
              <label class="block text-[11px] text-slate-500 mb-1">Temperatur (°C)</label>
              <input type="number" step="0.1" v-model="formSiveraLalat.temperatur" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
            </div>
            <div>
              <label class="block text-[11px] text-slate-500 mb-1">Kelembapan (%)</label>
              <input type="number" step="0.1" v-model="formSiveraLalat.kelembapan" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
            </div>
            <div>
              <label class="block text-[11px] text-slate-500 mb-1">Kecepatan Angin (m/s)</label>
              <input type="number" step="0.1" v-model="formSiveraLalat.kecepatan_angin" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
            </div>
          </div>

          <div class="space-y-4">
            <div class="flex items-center justify-between gap-2 flex-wrap">
              <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                <MapPin class="w-4 h-4 text-amber-500" />
                <span>Titik Lokasi Survei Fly Grill (10 Hitungan x 30 Detik)</span>
              </h4>
              <button
                type="button"
                @click="addLokasiLalat"
                class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold flex items-center gap-1 shadow-xs cursor-pointer"
              >
                <Plus class="w-3.5 h-3.5" />
                <span>Tambah Titik Lokasi</span>
              </button>
            </div>

            <div
              v-for="(locItem, lIdx) in formSiveraLalat.lokasi_items"
              :key="lIdx"
              class="p-4 rounded-2xl bg-amber-50/40 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/60 space-y-3"
            >
              <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 flex-1 max-w-sm">
                  <span class="w-6 h-6 rounded-full bg-amber-500 text-white font-bold text-xs flex items-center justify-center shrink-0">{{ lIdx + 1 }}</span>
                  <input
                    type="text"
                    v-model="locItem.lokasi"
                    placeholder="Nama Titik Lokasi (misal: Dapur, TPS, Kantin)"
                    class="w-full px-3 py-1.5 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900"
                  />
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-xs font-mono font-bold text-slate-500 hidden sm:inline">Top 5: {{ getLokasiLalatTop5(locItem).join(', ') }}</span>
                  <button
                    v-if="formSiveraLalat.lokasi_items.length > 1"
                    type="button"
                    @click="removeLokasiLalat(lIdx)"
                    aria-label="Hapus Titik Lokasi Lalat"
                    class="p-1 rounded-lg hover:bg-rose-100 text-rose-500 transition cursor-pointer"
                    title="Hapus Titik Ini"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </div>

              <div class="grid grid-cols-5 sm:grid-cols-10 gap-2">
                <div v-for="tIdx in 10" :key="tIdx" class="text-center">
                  <label class="block text-[10px] text-slate-500 mb-0.5">T-{{ tIdx }}</label>
                  <input
                    type="number"
                    min="0"
                    v-model.number="locItem.t[tIdx - 1]"
                    class="w-full py-1 text-center font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs"
                  />
                </div>
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-200/80 dark:border-amber-900 flex items-center justify-between text-xs">
                <div>
                  <span class="text-slate-500">Total Top 5: <strong class="font-mono text-slate-900 dark:text-white">{{ getLokasiLalatTotalTop5(locItem) }}</strong></span>
                  <span class="mx-2">&bull;</span>
                  <span class="text-slate-700 dark:text-slate-300 font-semibold">Fly Index: <strong class="text-amber-600 font-mono text-sm">{{ getLokasiLalatRata(locItem) }}</strong></span>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="getRiskBadgeClass(getLokasiLalatStatus(locItem))">
                  {{ getLokasiLalatStatus(locItem) }} (&lt; 2 MS)
                </span>
              </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/40 dark:to-orange-950/30 border border-amber-300 dark:border-amber-800 flex items-center justify-between text-xs">
              <div>
                <span class="text-slate-600 dark:text-slate-400 font-semibold">Total {{ formSiveraLalat.lokasi_items.length }} Titik Disurvei &bull; Rata-Rata Fly Index Gabungan:</span>
                <div class="text-lg font-mono font-extrabold text-amber-700 dark:text-amber-400 mt-0.5">
                  {{ flyIndexRata }} <span class="text-xs font-normal text-slate-500">ekor / block grill</span>
                </div>
              </div>
              <div class="text-right">
                <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getRiskBadgeClass(statusLalat)">
                  Status Keseluruhan: {{ statusLalat }}
                </span>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tindakan Pengendalian Lalat</label>
            <textarea v-model="formSiveraLalat.tindakan_pengendalian" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
          </div>
        </div>

        <!-- 4.3 KECOA -->
        <div v-if="siveraActiveVektor === 'Kecoa'" class="space-y-4">
          <div class="p-3 rounded-2xl bg-rose-50/60 dark:bg-rose-950/30 border border-rose-200/60 dark:border-rose-800/50">
            <span class="text-xs font-bold text-rose-900 dark:text-rose-300 flex items-center gap-1.5">
              <Bug class="w-4 h-4 text-rose-600" />
              <span>Surveilans Kepadatan Kecoa (Metode Trapping / Perangkap)</span>
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
              <select v-model="formSiveraKecoa.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
              <input type="date" v-model="formSiveraKecoa.tanggal" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi</label>
              <input type="text" v-model="formSiveraKecoa.lokasi" placeholder="Contoh: Terminal Penumpang" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Ruangan</label>
              <input type="text" v-model="formSiveraKecoa.nama_ruangan" placeholder="Contoh: Gudang Makanan / Dapur" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
          </div>

          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Perangkap Terpasang</label>
                <input type="number" min="1" v-model.number="formSiveraKecoa.perangkap_terpasang" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Periplaneta americana (ekor)</label>
                <input type="number" min="0" v-model.number="formSiveraKecoa.sp_americana" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Blattella germanica (ekor)</label>
                <input type="number" min="0" v-model.number="formSiveraKecoa.sp_germanica" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Spesies Lainnya (ekor)</label>
                <input type="number" min="0" v-model.number="formSiveraKecoa.sp_lainnya_jml" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
              </div>
            </div>

            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-rose-200 dark:border-rose-800 flex items-center justify-between text-xs">
              <div>
                <span class="text-slate-500">Total Tertangkap: <strong class="font-mono text-slate-900 dark:text-white">{{ totalKecoaTertangkap }}</strong> ekor</span>
                <span class="mx-2">&bull;</span>
                <span class="text-slate-700 dark:text-slate-300 font-semibold">Indeks Populasi Kecoa: <strong class="text-rose-600 font-mono text-base">{{ indeksKecoa }}</strong></span>
              </div>
              <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getRiskBadgeClass(statusKecoa)">
                {{ statusKecoa }} (&lt; 2 MS)
              </span>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tindakan Pengendalian</label>
            <textarea v-model="formSiveraKecoa.tindakan_pengendalian" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
          </div>
        </div>

        <!-- 4.4 TIKUS -->
        <div v-if="siveraActiveVektor === 'Tikus'" class="space-y-4">
          <div class="p-3 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">
            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
              <Bug class="w-4 h-4 text-slate-600" />
              <span>Surveilans Tikus &amp; Rodensia (Perangkap &amp; Spesies Target)</span>
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
              <select v-model="formSiveraTikus.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Mulai</label>
              <input type="date" v-model="formSiveraTikus.tanggal_mulai" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Selesai</label>
              <input type="date" v-model="formSiveraTikus.tanggal_selesai" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi</label>
              <input type="text" v-model="formSiveraTikus.lokasi" placeholder="Contoh: Buffer Area / Perimeter" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi Pemasangan</label>
              <input type="text" v-model="formSiveraTikus.lokasi_pemasangan" placeholder="Contoh: Dermaga 01 & Gudang Petikemas" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Umpan</label>
              <input type="text" v-model="formSiveraTikus.jenis_umpan" placeholder="Contoh: Kelapa bakar, Ikan asin" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
          </div>

          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
            <div class="flex items-center justify-between">
              <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Rincian Spesies Tikus Tertangkap</h4>
              <button type="button" @click="addSpesiesTikusRow()" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-600 text-white flex items-center gap-1 cursor-pointer">
                <Plus class="w-3 h-3" />
                <span>Tambah Baris Spesies</span>
              </button>
            </div>

            <div class="space-y-2">
              <div v-for="(row, idx) in formSiveraTikus.spesies_list" :key="idx" class="flex items-center gap-3 bg-white dark:bg-slate-900 p-2 rounded-xl border border-slate-200 dark:border-slate-700">
                <select v-model="row.nama" class="flex-1 px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                  <option v-for="sp in standardSpesiesTikus" :key="sp" :value="sp">{{ sp }}</option>
                </select>
                <input v-if="row.nama === 'Lain-lain'" type="text" v-model="row.custom" placeholder="Nama spesies lainnya" class="flex-1 px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700" />
                <div class="w-24">
                  <input type="number" min="0" v-model.number="row.jumlah" placeholder="Jumlah" class="w-full px-2.5 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-700" />
                </div>
                <button
                  type="button"
                  @click="removeSpesiesTikusRow(idx)"
                  aria-label="Hapus Baris Spesies Tikus"
                  title="Hapus Baris"
                  class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg cursor-pointer"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Jumlah Perangkap Dipasang</label>
                <input type="number" min="1" v-model.number="formSiveraTikus.jml_perangkap_dipasang" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
              </div>
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Total Tikus Tertangkap (ekor)</label>
                <input type="number" readonly :value="totalTikusTertangkap" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 font-mono font-bold" />
              </div>
            </div>

            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 flex items-center justify-between text-xs">
              <div>
                <span class="text-slate-500">Rumus: (Tikus / Perangkap) &times; 100% &bull;</span>
                <span class="ml-1 text-slate-700 dark:text-slate-300 font-semibold">Success Trap: <strong class="text-indigo-600 font-mono text-base">{{ successTrapTikus }}%</strong></span>
              </div>
              <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getRiskBadgeClass(statusTikus)">
                {{ statusTikus }} (&lt; 1% MS)
              </span>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tindakan Pengendalian</label>
            <textarea v-model="formSiveraTikus.tindakan_pengendalian" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
          </div>
        </div>

        <!-- 4.5 PINJAL TIKUS -->
        <div v-if="siveraActiveVektor === 'Pinjal'" class="space-y-4">
          <div class="p-3 rounded-2xl bg-cyan-50/60 dark:bg-cyan-950/30 border border-cyan-200/60 dark:border-cyan-800/50">
            <span class="text-xs font-bold text-cyan-900 dark:text-cyan-300 flex items-center gap-1.5">
              <Bug class="w-4 h-4 text-cyan-600" />
              <span>Surveilans Indeks Pinjal Tikus (Vektor Pes / Xenopsylla cheopis)</span>
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
              <select v-model="formSiveraPinjal.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
              <input type="date" v-model="formSiveraPinjal.tanggal" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi</label>
              <input type="text" v-model="formSiveraPinjal.lokasi" placeholder="Contoh: Dermaga / Gudang" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tikus Tertangkap Diperiksa</label>
              <input type="number" min="1" v-model.number="formSiveraPinjal.jml_tikus_tertangkap" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jumlah Pinjal Umum</label>
              <input type="number" min="0" v-model.number="formSiveraPinjal.pinjal_umum" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pinjal Xenopsylla cheopis</label>
              <input type="number" min="0" v-model.number="formSiveraPinjal.pinjal_khusus" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
              <div>
                <span class="text-slate-400 block text-[11px]">Indeks Pinjal Umum (Umum / Tikus):</span>
                <strong class="text-cyan-600 font-mono text-base">{{ indeksPinjalUmum }}</strong>
              </div>
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="indeksPinjalUmum < 2.0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300'">
                {{ indeksPinjalUmum < 2.0 ? 'MS (< 2)' : 'TMS (≥ 2)' }}
              </span>
            </div>
            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
              <div>
                <span class="text-slate-400 block text-[11px]">Indeks Pinjal Khusus (Khusus / Tikus):</span>
                <strong class="text-cyan-600 font-mono text-base">{{ indeksPinjalKhusus }}</strong>
              </div>
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="indeksPinjalKhusus < 1.0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300'">
                {{ indeksPinjalKhusus < 1.0 ? 'MS (< 1)' : 'TMS (≥ 1)' }}
              </span>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tindakan Pengendalian</label>
            <textarea v-model="formSiveraPinjal.tindakan_pengendalian" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
          </div>
        </div>

        <!-- 4.6 JENTIK ANOPHELES -->
        <div v-if="siveraActiveVektor === 'Jentik Anopheles'" class="space-y-4">
          <div class="p-3 rounded-2xl bg-teal-50/60 dark:bg-teal-950/30 border border-teal-200/60 dark:border-teal-800/50">
            <span class="text-xs font-bold text-teal-900 dark:text-teal-300 flex items-center gap-1.5">
              <Bug class="w-4 h-4 text-teal-600" />
              <span>Surveilans Jentik Anopheles (Vektor Malaria &amp; 10 Tipe Habitat Air)</span>
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
              <select v-model="formSiveraAnophelesJentik.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
              <input type="date" v-model="formSiveraAnophelesJentik.tanggal" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi Survei</label>
              <input type="text" v-model="formSiveraAnophelesJentik.lokasi" placeholder="Contoh: PPI Karangsong Indramayu" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
          </div>

          <div class="p-4 rounded-2xl bg-teal-50/30 dark:bg-teal-950/20 border border-teal-200 dark:border-teal-800/60 space-y-2">
            <label class="block text-xs font-bold text-teal-900 dark:text-teal-300">Tipe Habitat Perkembangbiakan Disurvei (Pilih yang sesuai):</label>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
              <label
                v-for="hab in standardHabitatAnopheles"
                :key="hab"
                class="flex items-center gap-2 p-2 rounded-xl border transition cursor-pointer text-xs"
                :class="formSiveraAnophelesJentik.jenis_habitat.includes(hab) ? 'bg-teal-50 dark:bg-teal-900/40 border-teal-500 font-bold text-teal-800 dark:text-teal-200' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'"
              >
                <input type="checkbox" :value="hab" v-model="formSiveraAnophelesJentik.jenis_habitat" class="rounded text-teal-600 focus:ring-teal-500" />
                <span>{{ hab }}</span>
              </label>
            </div>
            <div class="pt-1">
              <input
                type="text"
                v-model="formSiveraAnophelesJentik.jenis_habitat_lainnya"
                placeholder="Ketik tipe habitat lainnya jika ada..."
                class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900"
              />
            </div>
          </div>

          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Karakteristik &amp; Kondisi Lingkungan Perindukan Air</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Pencahayaan</label>
                <select v-model="formSiveraAnophelesJentik.kondisi_pencahayaan[0]" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                  <option value="Terkena sinar matahari">Terkena sinar matahari</option>
                  <option value="Terlindung / Teduh">Terlindung / Teduh</option>
                </select>
              </div>
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Vegetasi / Lumut</label>
                <select v-model="formSiveraAnophelesJentik.kondisi_vegetasi[0]" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                  <option value="Ada vegetasi">Ada Vegetasi / Lumut</option>
                  <option value="Tidak ada vegetasi">Tidak Ada Vegetasi</option>
                </select>
              </div>
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Sifat &amp; Salinitas Air</label>
                <select v-model="formSiveraAnophelesJentik.kondisi_air[0]" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                  <option value="Payau">Payau / Salin (Pesisir)</option>
                  <option value="Tawar Jernih">Tawar Jernih</option>
                  <option value="Keruh">Keruh / Berlumpur</option>
                  <option value="Tergenang">Tergenang</option>
                  <option value="Mengalir Lambat">Mengalir Lambat</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Jumlah Habitat Disurvei</label>
                <input type="number" min="1" v-model.number="formSiveraAnophelesJentik.jml_habitat_disurvei" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
              </div>
              <div>
                <label class="block text-[11px] text-slate-500 mb-1">Jumlah Habitat Positif Jentik Anopheles</label>
                <input type="number" min="0" v-model.number="formSiveraAnophelesJentik.jml_habitat_positif" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono text-rose-600 font-bold" />
              </div>
            </div>

            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-teal-200 dark:border-teal-800 flex items-center justify-between text-xs">
              <div>
                <span class="text-slate-500">Rumus: (Positif / Disurvei) &times; 100% &bull;</span>
                <span class="ml-1 text-slate-700 dark:text-slate-300 font-semibold">Indeks Habitat: <strong class="text-teal-600 font-mono text-base">{{ indeksHabitatAnopheles }}%</strong></span>
              </div>
              <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getRiskBadgeClass(statusAnophelesJentik)">
                {{ statusAnophelesJentik }} (&lt; 1% MS)
              </span>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tindakan Pengendalian</label>
            <textarea v-model="formSiveraAnophelesJentik.tindakan_pengendalian" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
          </div>
        </div>

        <!-- 4.7 NYAMUK ANOPHELES DEWASA -->
        <div v-if="siveraActiveVektor === 'Nyamuk Anopheles'" class="space-y-4">
          <div class="p-3 rounded-2xl bg-purple-50/60 dark:bg-purple-950/30 border border-purple-200/60 dark:border-purple-800/50">
            <span class="text-xs font-bold text-purple-900 dark:text-purple-300 flex items-center gap-1.5">
              <Bug class="w-4 h-4 text-purple-600" />
              <span>Surveilans Nyamuk Anopheles Dewasa (Penangkapan Umpan Orang Malam - Matriks 12 Jam)</span>
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
              <select v-model="formSiveraAnophelesNyamuk.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
              <input type="date" v-model="formSiveraAnophelesNyamuk.tanggal" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi</label>
              <input type="text" v-model="formSiveraAnophelesNyamuk.lokasi" placeholder="PPI Karangsong" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Spesies Target</label>
              <input type="text" v-model="formSiveraAnophelesNyamuk.spesies" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
            </div>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs">
            <div>
              <label class="block text-[11px] text-slate-500 mb-1">Kolektor Penangkap</label>
              <input type="number" min="1" v-model.number="formSiveraAnophelesNyamuk.jml_penangkap" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
            </div>
            <div>
              <label class="block text-[11px] text-slate-500 mb-1">Lama Survei (Jam)</label>
              <input type="number" min="1" v-model.number="formSiveraAnophelesNyamuk.lama_penangkapan" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
            </div>
            <div>
              <label class="block text-[11px] text-slate-500 mb-1">Suhu (°C)</label>
              <input type="number" step="0.1" v-model.number="formSiveraAnophelesNyamuk.suhu" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
            </div>
            <div>
              <label class="block text-[11px] text-slate-500 mb-1">Kelembaban (%)</label>
              <input type="number" step="0.1" v-model.number="formSiveraAnophelesNyamuk.kelembaban" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
            </div>
            <div>
              <label class="block text-[11px] text-slate-500 mb-1">Kondisi Cuaca</label>
              <input type="text" v-model="formSiveraAnophelesNyamuk.cuaca" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900" />
            </div>
          </div>

          <div class="p-4 rounded-2xl bg-purple-50/40 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-800 space-y-3">
            <div class="flex items-center justify-between gap-2 flex-wrap">
              <h4 class="text-xs font-bold text-purple-900 dark:text-purple-300 flex items-center gap-1.5">
                <Clock class="w-4 h-4 text-purple-600" />
                <span>Matriks Penangkapan Nyamuk 12 Jam (18.00 - 06.00 WIB)</span>
              </h4>
              <span class="text-xs font-mono font-bold text-slate-500">Total Man-Hours: {{ manHours }} jam-orang</span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-purple-200 dark:border-purple-800">
              <table class="w-full text-xs text-center bg-white dark:bg-slate-900">
                <thead>
                  <tr class="bg-purple-100/70 dark:bg-purple-950/60 text-purple-900 dark:text-purple-200 text-[11px] font-bold">
                    <th class="py-2 px-3 text-left">Jam Penangkapan</th>
                    <th class="py-2 px-3">Dalam Rumah (D)</th>
                    <th class="py-2 px-3">Luar Rumah (L)</th>
                    <th class="py-2 px-3">Total Jam (D+L)</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-purple-100 dark:divide-purple-900/40 font-mono">
                  <tr v-for="slot in anophelesJamSlots" :key="slot.key" class="hover:bg-purple-50/50 dark:hover:bg-purple-950/30">
                    <td class="py-1.5 px-3 text-left font-sans font-semibold text-slate-700 dark:text-slate-300 text-xs">{{ slot.label }}</td>
                    <td class="py-1 px-3">
                      <input type="number" min="0" v-model.number="formSiveraAnophelesNyamuk.tangkapan_dalam[slot.key]" class="w-16 py-1 text-center font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs" />
                    </td>
                    <td class="py-1 px-3">
                      <input type="number" min="0" v-model.number="formSiveraAnophelesNyamuk.tangkapan_luar[slot.key]" class="w-16 py-1 text-center font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs" />
                    </td>
                    <td class="py-1.5 px-3 font-bold text-slate-800 dark:text-slate-200">
                      {{ Number(formSiveraAnophelesNyamuk.tangkapan_dalam[slot.key] || 0) + Number(formSiveraAnophelesNyamuk.tangkapan_luar[slot.key] || 0) }}
                    </td>
                  </tr>
                  <tr class="bg-purple-50 dark:bg-purple-950/80 font-bold border-t-2 border-purple-200 dark:border-purple-800 text-xs">
                    <td class="py-2.5 px-3 text-left font-sans">TOTAL TERTANGKAP</td>
                    <td class="py-2.5 px-3 text-purple-700 dark:text-purple-300 font-extrabold">{{ totalDalamNyamuk }} ekor</td>
                    <td class="py-2.5 px-3 text-purple-700 dark:text-purple-300 font-extrabold">{{ totalLuarNyamuk }} ekor</td>
                    <td class="py-2.5 px-3 text-purple-900 dark:text-purple-100 font-black">{{ totalTertangkapNyamuk }} ekor</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3 rounded-xl bg-white dark:bg-slate-900 border border-purple-200 dark:border-purple-800 text-xs">
              <div>
                <span class="text-slate-500 block text-[11px]">MHD Dalam Rumah (D):</span>
                <strong class="text-purple-700 dark:text-purple-300 font-mono text-base">{{ mhdDalam }}</strong>
                <span class="text-[10px] text-slate-400 block">(Total D / Man-Hours)</span>
              </div>
              <div>
                <span class="text-slate-500 block text-[11px]">MHD Luar Rumah (L):</span>
                <strong class="text-purple-700 dark:text-purple-300 font-mono text-base">{{ mhdLuar }}</strong>
                <span class="text-[10px] text-slate-400 block">(Total L / Man-Hours)</span>
              </div>
              <div>
                <span class="text-slate-500 block text-[11px]">MBR (Man Biting Rate):</span>
                <div class="flex items-center justify-between">
                  <strong class="text-purple-700 dark:text-purple-300 font-mono text-base">{{ mbrAnopheles }}</strong>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border" :class="getRiskBadgeClass(statusAnophelesNyamuk)">{{ statusAnophelesNyamuk }}</span>
                </div>
                <span class="text-[10px] text-slate-400 block">Temuan Nyamuk: <strong>{{ adaAnopheles }}</strong></span>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tindakan Pengendalian</label>
            <textarea v-model="formSiveraAnophelesNyamuk.tindakan_pengendalian" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
          </div>
        </div>
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
          class="px-5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-[#00A99D] text-xs font-bold text-white hover:brightness-105 transition shadow-sm flex items-center gap-2 cursor-pointer disabled:opacity-50"
        >
          <Save class="w-4 h-4" />
          <span>{{ isSubmittingInspeksi ? 'Menyimpan...' : (editItem?.id ? 'Simpan Perubahan' : 'Simpan Data Surveilans') }}</span>
        </button>
      </div>
    </div>
  </ClinicalModal>
</template>
