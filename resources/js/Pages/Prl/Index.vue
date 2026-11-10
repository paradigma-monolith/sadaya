<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import {
  Leaf,
  Utensils,
  Droplets,
  Building2,
  Bug,
  Search,
  Filter,
  RotateCcw,
  Download,
  Printer,
  Eye,
  Plus,
  CheckCircle2,
  AlertTriangle,
  XCircle,
  ShieldCheck,
  ShieldAlert,
  Calendar,
  MapPin,
  User,
  Activity,
  Layers,
  ChevronRight,
  X,
  FileSpreadsheet,
  Check,
  TrendingUp,
  ClipboardCheck,
  FileText,
  Trash2,
  Edit,
  ArrowRight,
  Info,
  Sliders,
  Wind,
  Thermometer,
  CloudSun,
  Radio,
  Clock,
  Sparkles,
  Database,
  Play,
  Save,
  EyeOff,
} from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
  active_tab: {
    type: String,
    default: 'overview',
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  wilker_options: {
    type: Array,
    default: () => [],
  },
  pemeriksa_options: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
  inspeksi_tpp: {
    type: Object,
    default: () => ({ data: [] }),
  },
  inspeksi_sab: {
    type: Object,
    default: () => ({ data: [] }),
  },
  inspeksi_hsbu: {
    type: Object,
    default: () => ({ data: [] }),
  },
  sivera_list: {
    type: Object,
    default: () => ({ data: [] }),
  },
  master_tpp: {
    type: Array,
    default: () => [],
  },
  master_sab: {
    type: Array,
    default: () => [],
  },
  master_hsbu: {
    type: Array,
    default: () => [],
  },
});

// Active Tab State
const currentTab = ref(props.active_tab || 'overview');
const siveraFilterVektor = ref('Dashboard');

// Filter States
const search = ref(props.filters.search || '');
const selectedWilker = ref(props.filters.wilker || '');
const selectedRisiko = ref(props.filters.tingkat_risiko || '');
const selectedTahun = ref(props.filters.tahun || '');
const selectedBulan = ref(props.filters.bulan || '');

const switchTab = (tab) => {
  currentTab.value = tab;
  applyFilter(tab);
};

const applyFilter = (targetTab = currentTab.value) => {
  router.get('/prl', {
    tab: targetTab,
    search: search.value || undefined,
    wilker: selectedWilker.value || undefined,
    tingkat_risiko: selectedRisiko.value || undefined,
    tahun: selectedTahun.value || undefined,
    bulan: selectedBulan.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const resetFilter = () => {
  search.value = '';
  selectedWilker.value = '';
  selectedRisiko.value = '';
  selectedTahun.value = '';
  selectedBulan.value = '';
  siveraFilterVektor.value = 'Dashboard';
  router.get('/prl', { tab: currentTab.value }, { preserveState: true, replace: true });
};

// Filtered Sivera List based on vector sub-filter
const filteredSiveraData = computed(() => {
  const data = props.sivera_list?.data || [];
  if (siveraFilterVektor.value === 'Dashboard' || siveraFilterVektor.value === 'Semua Data') return data;
  return data.filter((item) => {
    const jv = (item.jenis_vektor || '').toLowerCase();
    const filter = siveraFilterVektor.value.toLowerCase();
    return jv.includes(filter) || filter.includes(jv);
  });
});

// SIVERA Dashboard Metrics
const siveraDashboardMetrics = computed(() => {
  const all = props.sivera_list?.data || [];
  const jentik = all.filter((x) => (x.jenis_vektor || '').includes('Jentik Aedes'));
  const lalat = all.filter((x) => (x.jenis_vektor || '').includes('Lalat'));
  const kecoa = all.filter((x) => (x.jenis_vektor || '').includes('Kecoa'));
  const tikus = all.filter((x) => (x.jenis_vektor || '').includes('Tikus'));
  const pinjal = all.filter((x) => (x.jenis_vektor || '').includes('Pinjal'));
  const anophelesJentik = all.filter((x) => (x.jenis_vektor || '').includes('Jentik Anopheles'));
  const anophelesNyamuk = all.filter((x) => (x.jenis_vektor || '').includes('Nyamuk Anopheles'));

  const totalMs = all.filter((x) => (x.baku_mutu_status || '').toLowerCase().includes('memenuhi')).length;
  const totalTms = all.length - totalMs;
  const kepatuhanPct = all.length > 0 ? ((totalMs / all.length) * 100).toFixed(1) : '100.0';

  const lalatMs = lalat.filter((x) => (x.baku_mutu_status || '').toLowerCase().includes('memenuhi')).length;
  const lalatTms = lalat.length - lalatMs;
  const lalatAvg = lalat.length > 0 ? (lalat.reduce((a, b) => a + Number(b.nilai_indeks || 0), 0) / lalat.length).toFixed(2) : '0.00';

  const jentikMs = jentik.filter((x) => (x.baku_mutu_status || '').toLowerCase().includes('memenuhi')).length;
  const jentikAbjAvg = jentik.length > 0 ? (jentik.reduce((a, b) => a + Number(b.nilai_indeks || 0), 0) / jentik.length).toFixed(2) : '0.00';

  const kecoaMs = kecoa.filter((x) => (x.baku_mutu_status || '').toLowerCase().includes('memenuhi')).length;
  const tikusMs = tikus.filter((x) => (x.baku_mutu_status || '').toLowerCase().includes('memenuhi')).length;
  const tikusTrapAvg = tikus.length > 0 ? (tikus.reduce((a, b) => a + Number(b.nilai_indeks || 0), 0) / tikus.length).toFixed(2) : '0.00';

  const pinjalMs = pinjal.filter((x) => (x.baku_mutu_status || '').toLowerCase().includes('memenuhi')).length;
  const ajMs = anophelesJentik.filter((x) => (x.baku_mutu_status || '').toLowerCase().includes('memenuhi')).length;
  const anMs = anophelesNyamuk.filter((x) => (x.baku_mutu_status || '').toLowerCase().includes('memenuhi')).length;

  const wilkerMap = {};
  all.forEach((item) => {
    const w = item.wilker || 'Wilayah Kerja';
    if (!wilkerMap[w]) wilkerMap[w] = { wilker: w, total: 0, ms: 0, tms: 0 };
    wilkerMap[w].total += 1;
    if ((item.baku_mutu_status || '').toLowerCase().includes('memenuhi')) {
      wilkerMap[w].ms += 1;
    } else {
      wilkerMap[w].tms += 1;
    }
  });
  const wilkerBreakdown = Object.values(wilkerMap);

  return {
    totalSurveilans: all.length,
    totalMs,
    totalTms,
    kepatuhanPct,
    jentikCount: jentik.length,
    jentikAbjAvg,
    jentikMs,
    lalatCount: lalat.length,
    lalatAvg,
    lalatMs,
    lalatTms,
    kecoaCount: kecoa.length,
    kecoaMs,
    tikusCount: tikus.length,
    tikusTrapAvg,
    tikusMs,
    pinjalCount: pinjal.length,
    pinjalMs,
    anophelesJentikCount: anophelesJentik.length,
    anophelesJentikMs: ajMs,
    anophelesNyamukCount: anophelesNyamuk.length,
    anophelesNyamukMs: anMs,
    wilkerBreakdown,
  };
});

// Modal Detail State
const showDetailModal = ref(false);
const detailItem = ref(null);
const detailType = ref('');

const openDetail = (item, type) => {
  detailItem.value = item;
  detailType.value = type;
  showDetailModal.value = true;
};

const closeDetail = () => {
  showDetailModal.value = false;
  detailItem.value = null;
  detailType.value = '';
};

// ==========================================
// 1. MODAL TAMBAH SARANA BARU (TPP / SAB / HSBU)
// ==========================================
const showAddSaranaModal = ref(false);
const addSaranaType = ref('tpp');
const isSubmittingSarana = ref(false);
const saranaForm = ref({
  wilker: '',
  nama: '',
  penanggung_jawab: '',
  no_hp: '',
  jml_karyawan: '1-2 Pekerja',
  kapasitas: '<100 kg/bulan atau <400 porsi/bulan',
  sumber_air: 'PDAM',
});

const openAddSarana = (type = 'tpp') => {
  addSaranaType.value = type;
  saranaForm.value = {
    wilker: props.wilker_options[0] || 'Wilayah Kerja Patimban',
    nama: '',
    penanggung_jawab: '',
    no_hp: '',
    jml_karyawan: type === 'tpp' ? '1-2 Pekerja' : '1-5 Pekerja',
    kapasitas: type === 'tpp' ? '<100 kg/bulan atau <400 porsi/bulan' : '500',
    sumber_air: 'PDAM',
  };
  showAddSaranaModal.value = true;
};

const submitSarana = async () => {
  if (!saranaForm.value.nama) {
    alert('Nama sarana / gedung wajib diisi!');
    return;
  }
  isSubmittingSarana.value = true;
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
      alert(res.data.message);
      showAddSaranaModal.value = false;
      router.reload();
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan data sarana.');
  } finally {
    isSubmittingSarana.value = false;
  }
};

// ==========================================
// 2. MODAL INPUT INSPEKSI SANITASI & SIVERA
// ==========================================
const showAddInspeksiModal = ref(false);
const addInspeksiType = ref('tpp');
const isSubmittingInspeksi = ref(false);

const todayStr = new Date().toISOString().split('T')[0];
const currentMonthStr = todayStr.substring(0, 7);

// FORM INSPEKSI TPP
const formTpp = ref({
  wilker: '',
  tanggal_inspeksi: todayStr,
  master_tpp_id: '',
  nama_tpp: '',
  penanggung_jawab_tpp: '',
  jml_karyawan_tpp: '1-2 Pekerja',
  kapasitas_produksi_tpp: '<100 kg/bulan atau <400 porsi/bulan',
  nama_pemeriksa: '',
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

watch(() => formTpp.value.master_tpp_id, (newId) => {
  if (newId) {
    const item = props.master_tpp.find((m) => m.id === Number(newId));
    if (item) {
      formTpp.value.nama_tpp = item.nama_tpp;
      formTpp.value.wilker = item.wilker || formTpp.value.wilker;
      formTpp.value.penanggung_jawab_tpp = item.penanggung_jawab || '';
      formTpp.value.jml_karyawan_tpp = item.jml_karyawan || '1-2 Pekerja';
      formTpp.value.kapasitas_produksi_tpp = item.kapasitas_produksi || '<100 kg/bulan atau <400 porsi/bulan';
    }
  }
});

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

const totalSkorTpp = computed(() => {
  return skorTppSecA.value + skorTppSecB.value + skorTppSecC.value + skorTppSecD.value;
});

const tingkatRisikoTpp = computed(() => {
  if (totalSkorTpp.value < 35) return 'Rendah';
  if (totalSkorTpp.value <= 50) return 'Sedang';
  return 'Tinggi';
});

// FORM INSPEKSI SAB
const formSab = ref({
  wilker: '',
  tanggal_inspeksi: todayStr,
  master_sab_id: '',
  nama_sab: '',
  penanggung_jawab_sab: '',
  penanggung_jawab_no_hp_sab: '',
  sumber_air_sab: 'PDAM',
  kapasitas_penampungan_sab: '500',
  nama_pemeriksa: '',
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

watch(() => formSab.value.master_sab_id, (newId) => {
  if (newId) {
    const item = props.master_sab.find((m) => m.id === Number(newId));
    if (item) {
      formSab.value.nama_sab = item.nama_sab;
      formSab.value.wilker = item.wilker || formSab.value.wilker;
      formSab.value.penanggung_jawab_sab = item.penanggung_jawab || '';
      formSab.value.penanggung_jawab_no_hp_sab = item.penanggung_jawab_no_hp || '';
      formSab.value.sumber_air_sab = item.sumber_air || 'PDAM';
      formSab.value.kapasitas_penampungan_sab = item.kapasitas_penampungan || '500';
    }
  }
});

const totalYaSab = computed(() => {
  const keys = ['sec_c_satu', 'sec_c_dua', 'sec_c_tiga', 'sec_c_empat', 'sec_c_lima', 'sec_c_enam', 'sec_c_tujuh', 'sec_c_delapan', 'sec_c_sembilan', 'sec_c_sepuluh'];
  let count = 0;
  for (const k of keys) {
    if (formSab.value[k] === 'Ya') count++;
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

// FORM INSPEKSI HSBU
const formHsbu = ref({
  wilker: '',
  tanggal_inspeksi: todayStr,
  master_hsbu_id: '',
  nama_gedung: '',
  penanggung_jawab_hsbu: '',
  jml_karyawan_hsbu: '10 Pekerja',
  nama_pemeriksa: '',
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

watch(() => formHsbu.value.master_hsbu_id, (newId) => {
  if (newId) {
    const item = props.master_hsbu.find((m) => m.id === Number(newId));
    if (item) {
      formHsbu.value.nama_gedung = item.nama_gedung;
      formHsbu.value.wilker = item.wilker || formHsbu.value.wilker;
      formHsbu.value.penanggung_jawab_hsbu = item.penanggung_jawab || '';
      formHsbu.value.jml_karyawan_hsbu = item.jml_karyawan || '10 Pekerja';
    }
  }
});

const totalSkorHsbu = computed(() => {
  return (
    Number(formHsbu.value.halaman || 0) +
    Number(formHsbu.value.konstruksi || 0) +
    Number(formHsbu.value.air_bersih || 0) +
    Number(formHsbu.value.udara || 0) +
    Number(formHsbu.value.limbah || 0) +
    Number(formHsbu.value.pencahayaan || 0) +
    Number(formHsbu.value.kebisingan || 0)
  );
});

const tingkatRisikoHsbu = computed(() => {
  if (totalSkorHsbu.value >= 712.5) return 'Rendah';
  if (totalSkorHsbu.value >= 475) return 'Sedang';
  return 'Tinggi';
});

// ==========================================
// 3. SIVERA VECTOR-SPECIFIC FORMS & FORMULAS
// ==========================================
const siveraActiveVektor = ref('Jentik Aedes sp.');

// 1. SIVERA JENTIK AEDES
const formSiveraJentik = ref({
  wilker: '',
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
});

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
const statusJentik = computed(() => {
  return (hiJentik.value === 0 && ciJentik.value === 0 && biJentik.value === 0) ? 'Memenuhi Syarat' : 'Tidak Memenuhi Syarat';
});

// 2. SIVERA LALAT (Multi-Lokasi Sesuai Simply CI4)
const formSiveraLalat = ref({
  wilker: '',
  tanggal: todayStr,
  musim: 'Hujan',
  temperatur: 28.5,
  kelembapan: 70,
  kecepatan_angin: 0.5,
  lokasi_items: [
    {
      lokasi: 'Dapur & Kantin Dermaga',
      t: [1, 0, 2, 1, 0, 1, 0, 2, 1, 0],
    },
  ],
  tindakan_pengendalian: 'Pemasangan fly trap dan sanitasi tempat pembuangan sampah.',
});

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

const getLokasiLalatTop5 = (item) => {
  const sorted = [...(item.t || [])].map(Number).sort((a, b) => b - a);
  return sorted.slice(0, 5);
};

const getLokasiLalatTotalTop5 = (item) => {
  return getLokasiLalatTop5(item).reduce((acc, curr) => acc + curr, 0);
};

const getLokasiLalatRata = (item) => {
  return Number((getLokasiLalatTotalTop5(item) / 5).toFixed(2));
};

const getLokasiLalatStatus = (item) => {
  return getLokasiLalatRata(item) < 2.0 ? 'Memenuhi Syarat' : 'Tidak Memenuhi Syarat';
};

const flyIndexRata = computed(() => {
  const items = formSiveraLalat.value.lokasi_items;
  if (!items || items.length === 0) return 0;
  const sum = items.reduce((acc, it) => acc + getLokasiLalatRata(it), 0);
  return Number((sum / items.length).toFixed(2));
});

const statusLalat = computed(() => {
  const anyTms = formSiveraLalat.value.lokasi_items.some((it) => getLokasiLalatRata(it) >= 2.0);
  return anyTms || flyIndexRata.value >= 2.0 ? 'Tidak Memenuhi Syarat' : 'Memenuhi Syarat';
});

// 3. SIVERA KECOA
const formSiveraKecoa = ref({
  wilker: '',
  tanggal: todayStr,
  lokasi: 'Perimeter Dermaga',
  nama_ruangan: 'Gudang Logistik & Dapur',
  perangkap_terpasang: 10,
  sp_americana: 0,
  sp_germanica: 0,
  sp_lainnya_nama: '',
  sp_lainnya_jml: 0,
  tindakan_pengendalian: 'Aplikasi baiting gel kecoa dan penutupan celah sanitasi.',
});

const totalKecoaTertangkap = computed(() => {
  return (
    Number(formSiveraKecoa.value.sp_americana || 0) +
    Number(formSiveraKecoa.value.sp_germanica || 0) +
    Number(formSiveraKecoa.value.sp_lainnya_jml || 0)
  );
});
const indeksKecoa = computed(() => {
  const trap = Number(formSiveraKecoa.value.perangkap_terpasang || 0);
  return trap > 0 ? Number((totalKecoaTertangkap.value / trap).toFixed(2)) : 0;
});
const statusKecoa = computed(() => (indeksKecoa.value < 2.0 ? 'Memenuhi Syarat' : 'Tidak Memenuhi Syarat'));

// 4. SIVERA TIKUS
const standardSpesiesTikus = [
  'Rattus tanezumi',
  'Rattus norvegicus',
  'Bandicota indica',
  'Mus musculus',
  'Rattus argentiventer',
  'Rattus exulans',
  'Rattus tiomanicus',
  'Lain-lain',
];

const formSiveraTikus = ref({
  wilker: '',
  tanggal_mulai: todayStr,
  tanggal_selesai: todayStr,
  lokasi: 'Buffer Area / Perimeter Pelabuhan',
  lokasi_pemasangan: 'Dermaga 01 & Gudang Petikemas',
  jenis_umpan: 'Kelapa Bakar & Ikan Asin',
  spesies_list: [
    { nama: 'Rattus tanezumi', custom: '', jumlah: 0 },
  ],
  jml_perangkap_dipasang: 50,
  jml_perangkap_hilang: 0,
  tindakan_pengendalian: 'Rat proofing kapal, pemasangan trap permanen, dan sanitasi sampah dermaga.',
});

const addSpesiesTikusRow = () => {
  formSiveraTikus.value.spesies_list.push({ nama: 'Rattus norvegicus', custom: '', jumlah: 0 });
};
const removeSpesiesTikusRow = (idx) => {
  formSiveraTikus.value.spesies_list.splice(idx, 1);
};

const totalTikusTertangkap = computed(() => {
  return formSiveraTikus.value.spesies_list.reduce((acc, row) => acc + Number(row.jumlah || 0), 0);
});
const successTrapTikus = computed(() => {
  const dipasang = Number(formSiveraTikus.value.jml_perangkap_dipasang || 0);
  return dipasang > 0 ? Number(((totalTikusTertangkap.value / dipasang) * 100).toFixed(2)) : 0;
});
const statusTikus = computed(() => (successTrapTikus.value < 1.0 ? 'Memenuhi Syarat' : 'Tidak Memenuhi Syarat'));

// 5. SIVERA PINJAL TIKUS
const formSiveraPinjal = ref({
  wilker: '',
  tanggal: todayStr,
  lokasi: 'Dermaga Barat & Gudang Kargo',
  jml_tikus_tertangkap: 1,
  pinjal_umum: 0,
  pinjal_khusus: 0,
  tindakan_pengendalian: 'Dusting insektisida pada liang dan perimeter sarang tikus.',
});

const indeksPinjalUmum = computed(() => {
  const tk = Number(formSiveraPinjal.value.jml_tikus_tertangkap || 0);
  return tk > 0 ? Number((Number(formSiveraPinjal.value.pinjal_umum || 0) / tk).toFixed(2)) : 0;
});
const indeksPinjalKhusus = computed(() => {
  const tk = Number(formSiveraPinjal.value.jml_tikus_tertangkap || 0);
  return tk > 0 ? Number((Number(formSiveraPinjal.value.pinjal_khusus || 0) / tk).toFixed(2)) : 0;
});
const statusPinjal = computed(() => {
  return (indeksPinjalUmum.value < 2.0 && indeksPinjalKhusus.value < 1.0) ? 'Memenuhi Syarat' : 'Tidak Memenuhi Syarat';
});

// 6. SIVERA JENTIK ANOPHELES
const standardHabitatAnopheles = [
  'Lagun', 'Tambak', 'Sawah', 'Rawa', 'Genangan',
  'Kolam', 'Sungai', 'Saluran Air', 'Muara', 'Tambak Terbengkalai'
];

const formSiveraAnophelesJentik = ref({
  wilker: '',
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
});

const indeksHabitatAnopheles = computed(() => {
  const disurvei = Number(formSiveraAnophelesJentik.value.jml_habitat_disurvei || 0);
  const positif = Number(formSiveraAnophelesJentik.value.jml_habitat_positif || 0);
  return disurvei > 0 ? Number(((positif / disurvei) * 100).toFixed(2)) : 0;
});
const statusAnophelesJentik = computed(() => (indeksHabitatAnopheles.value < 1.0 ? 'Memenuhi Syarat' : 'Tidak Memenuhi Syarat'));

// 7. SIVERA NYAMUK ANOPHELES (DEWASA - MATRIX 12 JAM)
const anophelesJamSlots = [
  { key: '18_19', label: '18⁰⁰-19⁰⁰' },
  { key: '19_20', label: '19⁰⁰-20⁰⁰' },
  { key: '20_21', label: '20⁰⁰-21⁰⁰' },
  { key: '21_22', label: '21⁰⁰-22⁰⁰' },
  { key: '22_23', label: '22⁰⁰-23⁰⁰' },
  { key: '23_24', label: '23⁰⁰-24⁰⁰' },
  { key: '24_01', label: '24⁰⁰-01⁰⁰' },
  { key: '01_02', label: '01⁰⁰-02⁰⁰' },
  { key: '02_03', label: '02⁰⁰-03⁰⁰' },
  { key: '03_04', label: '03⁰⁰-04⁰⁰' },
  { key: '04_05', label: '04⁰⁰-05⁰⁰' },
  { key: '05_06', label: '05⁰⁰-06⁰⁰' },
];

const formSiveraAnophelesNyamuk = ref({
  wilker: '',
  tanggal: todayStr,
  lokasi: 'PPI Karangsong Indramayu',
  spesies: 'Anopheles sp',
  jml_penangkap: 5,
  lama_penangkapan: 12,
  suhu: 27,
  kelembaban: 78,
  kecepatan_angin: 1.2,
  cuaca: 'Cerah Berawan',
  tangkapan_dalam: {
    '18_19': 0, '19_20': 0, '20_21': 0, '21_22': 0, '22_23': 0, '23_24': 0,
    '24_01': 0, '01_02': 0, '02_03': 0, '03_04': 0, '04_05': 0, '05_06': 0
  },
  tangkapan_luar: {
    '18_19': 0, '19_20': 0, '20_21': 0, '21_22': 0, '22_23': 0, '23_24': 0,
    '24_01': 0, '01_02': 0, '02_03': 0, '03_04': 0, '04_05': 0, '05_06': 0
  },
  keterangan: 'Penangkapan malam hari 18.00 - 06.00 WIB.',
  interpretasi: 'Kepadatan vektor dalam batas aman.',
  tindakan_pengendalian: 'Indoor Residual Spraying (IRS) dan pembagian kelambu berinsektisida.',
});

const totalDalamNyamuk = computed(() => {
  return Object.values(formSiveraAnophelesNyamuk.value.tangkapan_dalam || {}).reduce((a, b) => Number(a) + Number(b), 0);
});

const totalLuarNyamuk = computed(() => {
  return Object.values(formSiveraAnophelesNyamuk.value.tangkapan_luar || {}).reduce((a, b) => Number(a) + Number(b), 0);
});

const totalTertangkapNyamuk = computed(() => {
  return totalDalamNyamuk.value + totalLuarNyamuk.value;
});

const manHours = computed(() => {
  return Number(formSiveraAnophelesNyamuk.value.jml_penangkap || 1) * Number(formSiveraAnophelesNyamuk.value.lama_penangkapan || 12);
});

const mhdDalam = computed(() => {
  return manHours.value > 0 ? Number((totalDalamNyamuk.value / manHours.value).toFixed(4)) : 0;
});

const mhdLuar = computed(() => {
  return manHours.value > 0 ? Number((totalLuarNyamuk.value / manHours.value).toFixed(4)) : 0;
});

const mbrAnopheles = computed(() => {
  return manHours.value > 0 ? Number((totalTertangkapNyamuk.value / manHours.value).toFixed(4)) : 0;
});

const adaAnopheles = computed(() => {
  return totalTertangkapNyamuk.value > 0 ? 'Ya' : 'Tidak';
});

const statusAnophelesNyamuk = computed(() => (totalTertangkapNyamuk.value === 0 ? 'Memenuhi Syarat' : 'Tidak Memenuhi Syarat'));

// Edit Mode State
const isEditMode = ref(false);
const editId = ref(null);

const editSivera = (item) => {
  isEditMode.value = true;
  editId.value = item.id;
  addInspeksiType.value = 'sivera';

  const jv = item.jenis_vektor || '';
  if (jv.includes('Jentik Aedes')) {
    siveraActiveVektor.value = 'Jentik Aedes sp.';
    const d = item.detail_data || {};
    formSiveraJentik.value = {
      wilker: item.wilker || '',
      lokasi: item.lokasi || '',
      bulan_pelaksanaan: d.bulan_pelaksanaan || currentMonthStr,
      kondisi_musim: d.kondisi_musim || 'Hujan',
      tanggal_mulai: d.tanggal_mulai || item.tanggal,
      tanggal_selesai: d.tanggal_selesai || item.tanggal,
      wilayah: d.wilayah || 'Perimeter',
      jml_rumah_diperiksa: Number(d.jml_rumah_diperiksa || 50),
      jml_kontainer_diperiksa: Number(d.jml_kontainer_diperiksa || 150),
      jml_rumah_positif: Number(d.jml_rumah_positif || 0),
      jml_kontainer_positif: Number(d.jml_kontainer_positif || 0),
      jenis_kontainer: Array.isArray(d.jenis_kontainer) ? d.jenis_kontainer : (d.jenis_kontainer ? d.jenis_kontainer.split(',') : ['Ember']),
      jenis_kontainer_lainnya: d.jenis_kontainer_lainnya || '',
      tindakan_pengendalian: item.tindakan_pengendalian || '',
    };
  } else if (jv.includes('Lalat')) {
    siveraActiveVektor.value = 'Lalat';
    const d = item.detail_data || {};
    let lokItems = [];
    if (d.detail_lokasi && Array.isArray(d.detail_lokasi) && d.detail_lokasi.length > 0) {
      lokItems = d.detail_lokasi.map((dl) => ({
        lokasi: dl.lokasi || 'Lokasi',
        t: dl.t || [0,0,0,0,0,0,0,0,0,0],
      }));
    } else {
      lokItems = [{
        lokasi: item.lokasi || 'Dapur / Kantin',
        t: d.t || [1,0,2,1,0,1,0,2,1,0],
      }];
    }
    formSiveraLalat.value = {
      wilker: item.wilker || '',
      tanggal: item.tanggal,
      musim: d.musim || 'Hujan',
      temperatur: Number(d.temperatur || 28.5),
      kelembapan: Number(d.kelembapan || 70),
      kecepatan_angin: Number(d.kecepatan_angin || 0.5),
      lokasi_items: lokItems,
      tindakan_pengendalian: item.tindakan_pengendalian || '',
    };
  } else if (jv.includes('Kecoa')) {
    siveraActiveVektor.value = 'Kecoa';
    const d = item.detail_data || {};
    formSiveraKecoa.value = {
      wilker: item.wilker || '',
      tanggal: item.tanggal,
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
    const d = item.detail_data || {};
    let spList = [];
    if (d.spesies_list && Array.isArray(d.spesies_list)) {
      spList = d.spesies_list;
    } else if (d.spesies_tertangkap && typeof d.spesies_tertangkap === 'object') {
      spList = Object.entries(d.spesies_tertangkap).map(([k, v]) => ({
        nama: k,
        custom: '',
        jumlah: Number(v),
      }));
    } else {
      spList = [{ nama: 'Rattus tanezumi', custom: '', jumlah: 0 }];
    }
    formSiveraTikus.value = {
      wilker: item.wilker || '',
      tanggal_mulai: d.tanggal_mulai || item.tanggal,
      tanggal_selesai: d.tanggal_selesai || item.tanggal,
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
    const d = item.detail_data || {};
    formSiveraPinjal.value = {
      wilker: item.wilker || '',
      tanggal: item.tanggal,
      lokasi: item.lokasi || '',
      jml_tikus_tertangkap: Number(d.jml_tikus_tertangkap || 1),
      pinjal_umum: Number(d.pinjal_umum || 0),
      pinjal_khusus: Number(d.pinjal_khusus || 0),
      tindakan_pengendalian: item.tindakan_pengendalian || '',
    };
  } else if (jv.includes('Jentik Anopheles')) {
    siveraActiveVektor.value = 'Jentik Anopheles';
    const d = item.detail_data || {};
    formSiveraAnophelesJentik.value = {
      wilker: item.wilker || '',
      tanggal: item.tanggal,
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
    const d = item.detail_data || {};
    formSiveraAnophelesNyamuk.value = {
      wilker: item.wilker || '',
      tanggal: item.tanggal,
      lokasi: item.lokasi || '',
      spesies: d.spesies || 'Anopheles sp',
      jml_penangkap: Number(d.jml_penangkap || 5),
      lama_penangkapan: Number(d.lama_penangkapan || 12),
      suhu: Number(d.suhu || 27),
      kelembaban: Number(d.kelembaban || 78),
      kecepatan_angin: Number(d.kecepatan_angin || 1.2),
      cuaca: d.cuaca || 'Cerah Berawan',
      tangkapan_dalam: d.tangkapan_dalam || {},
      tangkapan_luar: d.tangkapan_luar || {},
      keterangan: d.keterangan || '',
      interpretasi: d.interpretasi || '',
      tindakan_pengendalian: item.tindakan_pengendalian || '',
    };
  }

  showAddInspeksiModal.value = true;
};

// Modal Tindakan Pengendalian
const showPengendalianModal = ref(false);
const pengendalianItem = ref(null);
const isSubmittingPengendalian = ref(false);
const activePengendalianTab = ref('jentik');

const pengendalianForm = ref({
  tanggal: todayStr,
  persiapan: '',
  jentik_kimia: false,
  jentik_kimia_gram: '',
  jentik_kimia_sachet: '',
  jentik_fisik: false,
  jentik_biologi: false,
  nyamuk_fogging: false,
  nyamuk_fogging_mesin: '',
  nyamuk_fogging_ml: '',
  nyamuk_fogging_golongan: 'Sintetik Piretroid',
  nyamuk_fogging_bahan: '',
  nyamuk_fogging_merk: '',
  nyamuk_fisik: false,
  nyamuk_lokasi: '',
  lalat_spraying: false,
  lalat_spraying_ml: '',
  lalat_granule: false,
  lalat_granule_gram: '',
  lalat_lokasi: '',
  kecoa_retrapping: false,
  kecoa_retrapping_trap: '',
  kecoa_gel_bait: false,
  kecoa_gel_bait_gram: '',
  kecoa_lokasi: '',
  catatan: '',
});

const openPengendalian = (item) => {
  pengendalianItem.value = item;
  activePengendalianTab.value = 'jentik';

  let d = item.detail_data?.pengendalian || {};
  if (typeof d === 'string') {
    try { d = JSON.parse(d); } catch (e) { d = {}; }
  }
  pengendalianForm.value = {
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
    nyamuk_lokasi: d.nyamuk_lokasi || item.lokasi || '',
    lalat_spraying: Boolean(d.spraying),
    lalat_spraying_ml: d.spraying_ml || '',
    lalat_granule: Boolean(d.granule),
    lalat_granule_gram: d.granule_gram || '',
    lalat_lokasi: d.lokasi || item.lokasi || '',
    kecoa_retrapping: Boolean(d.retrapping),
    kecoa_retrapping_trap: d.retrapping_trap || '',
    kecoa_gel_bait: Boolean(d.gel_bait),
    kecoa_gel_bait_gram: d.gel_bait_gram || '',
    kecoa_lokasi: d.lokasi || item.lokasi || '',
    catatan: formatTindakanPengendalian(item.tindakan_pengendalian) || '',
  };

  showPengendalianModal.value = true;
};

const submitPengendalian = async () => {
  if (!pengendalianItem.value) return;
  isSubmittingPengendalian.value = true;
  try {
    const pf = pengendalianForm.value;
    const jv = pengendalianItem.value.jenis_vektor || '';
    let summaryParts = [];
    let detailObj = { tanggal: pf.tanggal };

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

    const res = await axios.post(`/prl/sivera/${pengendalianItem.value.id}/pengendalian`, payload);
    alert(res.data.message);
    showPengendalianModal.value = false;
    router.reload();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan tindakan pengendalian.');
  } finally {
    isSubmittingPengendalian.value = false;
  }
};

// Open Add Inspection Modal with specified or active tab
const openAddInspeksi = (type = null, vectorSub = null) => {
  isEditMode.value = false;
  editId.value = null;
  const chosenType = type || (currentTab.value === 'overview' ? 'tpp' : currentTab.value);
  addInspeksiType.value = chosenType;
  if (vectorSub) {
    siveraActiveVektor.value = vectorSub;
  }
  
  const defWilker = props.wilker_options[0] || 'Wilayah Kerja Patimban';
  const defPemeriksa = props.pemeriksa_options[0] || '';

  formTpp.value.wilker = defWilker;
  formTpp.value.nama_pemeriksa = defPemeriksa;
  formSab.value.wilker = defWilker;
  formSab.value.nama_pemeriksa = defPemeriksa;
  formHsbu.value.wilker = defWilker;
  formHsbu.value.nama_pemeriksa = defPemeriksa;

  formSiveraJentik.value.wilker = defWilker;
  formSiveraLalat.value.wilker = defWilker;
  formSiveraKecoa.value.wilker = defWilker;
  formSiveraTikus.value.wilker = defWilker;
  formSiveraPinjal.value.wilker = defWilker;
  formSiveraAnophelesJentik.value.wilker = defWilker;
  formSiveraAnophelesNyamuk.value.wilker = defWilker;

  showAddInspeksiModal.value = true;
};

// Submit Inspeksi / SIVERA
const submitInspeksi = async () => {
  isSubmittingInspeksi.value = true;
  try {
    if (addInspeksiType.value === 'tpp') {
      if (!formTpp.value.nama_tpp) {
        alert('Nama TPP wajib diisi atau dipilih!');
        isSubmittingInspeksi.value = false;
        return;
      }
      const payload = {
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
      };
      const res = await axios.post('/prl/inspeksi/tpp', payload);
      alert(res.data.message);
    } else if (addInspeksiType.value === 'sab') {
      if (!formSab.value.nama_sab) {
        alert('Nama SAB wajib diisi atau dipilih!');
        isSubmittingInspeksi.value = false;
        return;
      }
      const payload = {
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
      };
      const res = await axios.post('/prl/inspeksi/sab', payload);
      alert(res.data.message);
    } else if (addInspeksiType.value === 'hsbu') {
      if (!formHsbu.value.nama_gedung) {
        alert('Nama Gedung HSBU wajib diisi atau dipilih!');
        isSubmittingInspeksi.value = false;
        return;
      }
      const payload = {
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
      };
      const res = await axios.post('/prl/inspeksi/hsbu', payload);
      alert(res.data.message);
    } else if (addInspeksiType.value === 'sivera') {
      let payload = {};

      if (siveraActiveVektor.value === 'Jentik Aedes sp.' || siveraActiveVektor.value === 'Jentik Aedes') {
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
            hi: hiJentik.value,
            ci: ciJentik.value,
            bi: biJentik.value,
            abj: abjJentik.value,
            status: statusJentik.value,
          },
        };
      } else if (siveraActiveVektor.value === 'Lalat') {
        const formattedLokasiItems = formSiveraLalat.value.lokasi_items.map((it) => {
          const top5 = getLokasiLalatTop5(it);
          const total5 = getLokasiLalatTotalTop5(it);
          const rata = getLokasiLalatRata(it);
          const st = getLokasiLalatStatus(it);
          return {
            lokasi: it.lokasi,
            t: (it.t || []).map(Number),
            top_5: top5,
            total_n: total5,
            rata_rata: rata,
            status: st,
          };
        });
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
            wilker: formSiveraLalat.value.wilker,
            tanggal: formSiveraLalat.value.tanggal,
            musim: formSiveraLalat.value.musim,
            temperatur: formSiveraLalat.value.temperatur,
            kelembapan: formSiveraLalat.value.kelembapan,
            kecepatan_angin: formSiveraLalat.value.kecepatan_angin,
            lokasi_items: formattedLokasiItems,
            detail_lokasi: formattedLokasiItems,
            fly_index_rata: flyIndexRata.value,
            status: statusLalat.value,
            tindakan_pengendalian: formSiveraLalat.value.tindakan_pengendalian,
          },
        };
      } else if (siveraActiveVektor.value === 'Kecoa') {
        payload = {
          jenis_vektor: 'Kecoa',
          wilker: formSiveraKecoa.value.wilker,
          lokasi: `${formSiveraKecoa.value.lokasi || 'Perimeter'} (${formSiveraKecoa.value.nama_ruangan || '-'})`,
          tanggal: formSiveraKecoa.value.tanggal,
          parameter_utama: `Indeks: ${indeksKecoa.value} (Tertangkap: ${totalKecoaTertangkap.value} ekor)`,
          nilai_indeks: indeksKecoa.value,
          baku_mutu_status: statusKecoa.value,
          tindakan_pengendalian: formSiveraKecoa.value.tindakan_pengendalian,
          detail_data: {
            ...formSiveraKecoa.value,
            total_tertangkap: totalKecoaTertangkap.value,
            rata_rata: indeksKecoa.value,
            status: statusKecoa.value,
          },
        };
      } else if (siveraActiveVektor.value === 'Tikus') {
        const spesiesMap = {};
        formSiveraTikus.value.spesies_list.forEach((row) => {
          const sp = row.nama === 'Lain-lain' ? (row.custom || 'Lain-lain') : row.nama;
          if (sp && Number(row.jumlah) > 0) {
            spesiesMap[sp] = Number(row.jumlah);
          }
        });
        payload = {
          jenis_vektor: 'Tikus',
          wilker: formSiveraTikus.value.wilker,
          lokasi: `${formSiveraTikus.value.lokasi || 'Buffer'} (${formSiveraTikus.value.lokasi_pemasangan || 'Dermaga'})`,
          tanggal: formSiveraTikus.value.tanggal_mulai,
          parameter_utama: `Success Trap: ${successTrapTikus.value}% (${totalTikusTertangkap.value} ekor / ${formSiveraTikus.value.jml_perangkap_dipasang} trap)`,
          nilai_indeks: successTrapTikus.value,
          baku_mutu_status: statusTikus.value,
          tindakan_pengendalian: formSiveraTikus.value.tindakan_pengendalian,
          detail_data: {
            ...formSiveraTikus.value,
            spesies_tertangkap: spesiesMap,
            total_tertangkap: totalTikusTertangkap.value,
            success_trap: successTrapTikus.value,
            status: statusTikus.value,
          },
        };
      } else if (siveraActiveVektor.value === 'Pinjal') {
        payload = {
          jenis_vektor: 'Pinjal',
          wilker: formSiveraPinjal.value.wilker,
          lokasi: formSiveraPinjal.value.lokasi || 'Perimeter',
          tanggal: formSiveraPinjal.value.tanggal,
          parameter_utama: `Indeks Umum: ${indeksPinjalUmum.value}, Khusus: ${indeksPinjalKhusus.value}`,
          nilai_indeks: indeksPinjalUmum.value,
          baku_mutu_status: statusPinjal.value,
          tindakan_pengendalian: formSiveraPinjal.value.tindakan_pengendalian,
          detail_data: {
            ...formSiveraPinjal.value,
            indeks_pinjal_umum: indeksPinjalUmum.value,
            indeks_pinjal_khusus: indeksPinjalKhusus.value,
            status: statusPinjal.value,
          },
        };
      } else if (siveraActiveVektor.value === 'Jentik Anopheles') {
        payload = {
          jenis_vektor: 'Jentik Anopheles',
          wilker: formSiveraAnophelesJentik.value.wilker,
          lokasi: formSiveraAnophelesJentik.value.lokasi || 'Pesisir / Tambak',
          tanggal: formSiveraAnophelesJentik.value.tanggal,
          parameter_utama: `Indeks Habitat: ${indeksHabitatAnopheles.value}% (${formSiveraAnophelesJentik.value.jml_habitat_positif}/${formSiveraAnophelesJentik.value.jml_habitat_disurvei})`,
          nilai_indeks: indeksHabitatAnopheles.value,
          baku_mutu_status: statusAnophelesJentik.value,
          tindakan_pengendalian: formSiveraAnophelesJentik.value.tindakan_pengendalian,
          detail_data: {
            ...formSiveraAnophelesJentik.value,
            indeks_habitat: indeksHabitatAnopheles.value,
            status: statusAnophelesJentik.value,
          },
        };
      } else if (siveraActiveVektor.value === 'Nyamuk Anopheles') {
        payload = {
          jenis_vektor: 'Nyamuk Anopheles',
          wilker: formSiveraAnophelesNyamuk.value.wilker,
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
            man_hours: manHours.value,
            mhd_dalam: mhdDalam.value,
            mhd_luar: mhdLuar.value,
            mbr: mbrAnopheles.value,
            ada_vektor: adaAnopheles.value,
            status: statusAnophelesNyamuk.value,
          },
        };
      }

      let res;
      if (isEditMode.value && editId.value) {
        res = await axios.put(`/prl/sivera/${editId.value}`, payload);
      } else {
        res = await axios.post('/prl/sivera', payload);
      }
      alert(res.data.message);
    }

    showAddInspeksiModal.value = false;
    isEditMode.value = false;
    editId.value = null;
    router.reload();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan data.');
  } finally {
    isSubmittingInspeksi.value = false;
  }
};

// Delete Record
const deleteInspection = async (item, type) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus catatan ${item.nama_tpp || item.nama_sab || item.nama_gedung || item.jenis_vektor}?`)) {
    return;
  }
  try {
    const res = await axios.delete(`/prl/inspeksi/${type}/${item.id}`);
    alert(res.data.message);
    router.reload();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menghapus data.');
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const getRiskBadgeClass = (risk) => {
  switch (risk) {
    case 'Rendah':
    case 'Memenuhi Syarat':
    case 'MS':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800';
    case 'Sedang':
      return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800';
    case 'Tinggi':
    case 'Amat Tinggi':
    case 'Tidak Memenuhi Syarat':
    case 'Di Atas Baku Mutu':
    case 'TMS':
      return 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800';
    default:
      return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
  }
};

const summarizePengendalianObj = (data) => {
  if (!data || typeof data !== 'object') return '';
  const parts = [];

  // Jentik
  if (data.jentik_kimia || data.jentik_kimia_gram || data.jentik_kimia_sachet) {
    const p = [];
    if (data.jentik_kimia_gram) p.push(`${data.jentik_kimia_gram} gr`);
    if (data.jentik_kimia_sachet) p.push(`${data.jentik_kimia_sachet} sachet`);
    parts.push(`Larvasidasi${p.length ? ' (' + p.join(', ') + ')' : ''}`);
  }
  if (data.jentik_fisik) parts.push('Fisik (3M Plus/Kuras)');
  if (data.jentik_biologi) parts.push('Biologi (BTI/Ikan)');

  // Nyamuk / Fogging
  if (data.nyamuk_fogging || data.nyamuk_fogging_ml || data.fogging_ml) {
    const f = [];
    const ml = data.nyamuk_fogging_ml || data.fogging_ml;
    if (ml) f.push(`${ml} ml`);
    const merk = data.nyamuk_fogging_merk || data.fogging_insektisida;
    if (merk) f.push(merk);
    parts.push(`Fogging${f.length ? ' (' + f.join(' ') + ')' : ''}`);
  }
  if (data.nyamuk_fisik) parts.push('Fisik Nyamuk (Trap/Raket)');

  // Lalat / Kecoa
  if (data.spraying || data.spraying_ml) parts.push(`Spraying${data.spraying_ml ? ' (' + data.spraying_ml + ' ml)' : ''}`);
  if (data.granule || data.granule_gram) parts.push(`Granule Bait${data.granule_gram ? ' (' + data.granule_gram + ' gr)' : ''}`);
  if (data.retrapping || data.retrapping_trap) parts.push(`Retrapping${data.retrapping_trap ? ' (' + data.retrapping_trap + ' trap)' : ''}`);
  if (data.gel_bait || data.gel_bait_gram) parts.push(`Gel Bait${data.gel_bait_gram ? ' (' + data.gel_bait_gram + ' gr)' : ''}`);

  // Tikus / Pinjal
  if (data.perangkap || data.trap) parts.push(`Pemasangan Perangkap (${data.perangkap || data.trap} unit)`);
  if (data.dusting || data.dusting_gram) parts.push(`Dusting Insektisida${data.dusting_gram ? ' (' + data.dusting_gram + ' gr)' : ''}`);
  if (data.rodentisida || data.umpan_gram) parts.push(`Rodentisida${data.umpan_gram ? ' (' + data.umpan_gram + ' gr)' : ''}`);

  // Lokasi note if present
  if (data.lokasi) parts.push(`Lokasi: ${data.lokasi}`);
  if (data.catatan) parts.push(data.catatan);

  if (parts.length > 0) {
    return parts.join('; ');
  }

  // Fallback if none of the specific keys matched: format key-value pairs cleanly
  const fallbackParts = [];
  for (const [k, v] of Object.entries(data)) {
    if (v && typeof v !== 'object') {
      const cleanKey = k.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
      fallbackParts.push(`${cleanKey}: ${v}`);
    }
  }
  return fallbackParts.length > 0 ? fallbackParts.join(', ') : '';
};

const formatTindakanPengendalian = (val) => {
  if (!val) return '';
  if (typeof val === 'object') {
    return summarizePengendalianObj(val);
  }
  if (typeof val === 'string') {
    const trimmed = val.trim();
    if (trimmed.startsWith('{') && trimmed.endsWith('}')) {
      try {
        const parsed = JSON.parse(trimmed);
        return summarizePengendalianObj(parsed);
      } catch (e) {
        return val;
      }
    }
  }
  return val;
};

// ==========================================
// 6. MIGRATION & SYNC MODAL STATE & ACTIONS
// ==========================================
const showSyncModal = ref(false);
const showSyncPassword = ref(false);
const isTestingSync = ref(false);
const isSyncingData = ref(false);
const syncTestResult = ref(null);
const syncResult = ref(null);

const syncForm = ref({
  host: '127.0.0.1',
  port: 3306,
  username: 'root',
  password: '',
  database: 'bkkr3927_simply',
  modules: ['prl_tpp', 'prl_sab', 'prl_hsbu', 'prl_sivera'],
  skip_duplicate: true,
  normalize_ports: true,
  truncate_first: false,
});

const openSyncModal = () => {
  syncTestResult.value = null;
  syncResult.value = null;
  showSyncModal.value = true;
};

const closeSyncModal = () => {
  showSyncModal.value = false;
};

const toggleAllSyncModules = () => {
  const allMods = ['prl_tpp', 'prl_sab', 'prl_hsbu', 'prl_sivera'];
  if (syncForm.value.modules.length === allMods.length) {
    syncForm.value.modules = [];
  } else {
    syncForm.value.modules = [...allMods];
  }
};

const handleTestSyncConnection = async () => {
  isTestingSync.value = true;
  syncTestResult.value = null;
  try {
    const res = await axios.post('/prl/test-connection', {
      ...syncForm.value,
      target_module: syncForm.value.modules[0] || 'prl_sivera',
    });
    syncTestResult.value = res.data;
  } catch (err) {
    syncTestResult.value = {
      success: false,
      message: err.response?.data?.message || err.message || 'Koneksi ke server database sumber gagal.',
    };
  } finally {
    isTestingSync.value = false;
  }
};

const handleSaveSyncConfig = () => {
  try {
    const saved = {
      host: syncForm.value.host,
      port: syncForm.value.port,
      username: syncForm.value.username,
      database: syncForm.value.database,
      modules: syncForm.value.modules,
    };
    localStorage.setItem('prl_sync_config', JSON.stringify(saved));
    alert('Pengaturan koneksi sumber PRL berhasil disimpan.');
  } catch (e) {}
};

const handleRunSync = async () => {
  if (syncForm.value.modules.length === 0) {
    alert('Pilih setidaknya 1 modul yang akan disinkronkan.');
    return;
  }

  if (syncForm.value.truncate_first) {
    const confirmed = confirm('PERINGATAN: Anda memilih opsi Kosongkan Tabel (Truncate). Seluruh data lokal modul terpilih akan dihapus sebelum sinkronisasi. Lanjutkan?');
    if (!confirmed) return;
  }

  isSyncingData.value = true;
  syncResult.value = null;

  try {
    const res = await axios.post('/prl/sync', syncForm.value);
    syncResult.value = res.data;
    if (res.data.success) {
      router.reload({
        only: ['stats', 'inspeksi_tpp', 'inspeksi_sab', 'inspeksi_hsbu', 'sivera_vektor', 'master_tpp', 'master_sab', 'master_hsbu'],
      });
    }
  } catch (err) {
    syncResult.value = {
      success: false,
      message: err.response?.data?.message || err.message || 'Terjadi kesalahan saat sinkronisasi data.',
    };
  } finally {
    isSyncingData.value = false;
  }
};
</script>

<template>
  <Head title="Pengendalian Risiko Lingkungan (PRL) &bull; BKK Bandung" />

  <HybridNavbar mode="inertia" currentRoute="prl">
    <main class="w-full px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

      <!-- ==================== HEADER BAR ==================== -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 mb-2">
            <Leaf class="w-3.5 h-3.5 text-emerald-600" />
            <span>Substansi Pengendalian Risiko Lingkungan (PRL) &bull; BKK Kelas I Bandung</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
            Sanitasi Lingkungan Pelabuhan &amp; Bandara
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-3xl leading-relaxed">
            Pusat terpadu pengawasan Tempat Pengelolaan Pangan (TPP), Sarana Air Bersih (SAB), Higiene Sanitasi Bangunan dan Usaha (HSBU), serta Surveilans Vektor &amp; Reservoar (SIVERA) di seluruh wilayah kerja.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <button
            type="button"
            @click="openSyncModal"
            class="px-3.5 py-2.5 rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 text-xs font-bold text-indigo-700 dark:text-indigo-300 transition shadow-xs flex items-center gap-1.5 cursor-pointer"
            title="Konfigurasi &amp; Sinkronisasi Data dari Simply CI4"
          >
            <Database class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" />
            <span>Migrasi Data Simply</span>
          </button>

          <button
            type="button"
            @click="openAddInspeksi()"
            class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#00A99D] to-[#005851] hover:brightness-105 text-xs font-bold text-white transition shadow-sm flex items-center gap-2 cursor-pointer"
          >
            <ClipboardCheck class="w-4 h-4" />
            <span>+ Input Inspeksi Baru</span>
          </button>

          <button
            type="button"
            @click="openAddSarana('tpp')"
            class="px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-xs font-bold text-slate-700 dark:text-slate-200 transition shadow-xs flex items-center gap-1.5 cursor-pointer"
          >
            <Plus class="w-3.5 h-3.5 text-emerald-600" />
            <span>+ Tambah Sarana Baru</span>
          </button>

          <a
            :href="`/prl/export/${currentTab === 'overview' ? 'tpp' : currentTab}`"
            class="px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-xs font-bold text-slate-700 dark:text-slate-200 transition shadow-xs flex items-center gap-1.5 cursor-pointer"
            title="Export CSV / Excel"
          >
            <Download class="w-3.5 h-3.5 text-slate-500" />
            <span>Export CSV</span>
          </a>
        </div>
      </div>

      <!-- ==================== KPI STATS CARDS ==================== -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Sarana -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Sarana Terdaftar</span>
            <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-[#00A99D]">
              <Layers class="w-5 h-5" />
            </div>
          </div>
          <div class="mt-2 flex items-baseline gap-2">
            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white font-mono">{{ stats.total_sarana }}</span>
            <span class="text-xs text-slate-400">Unit Sarana</span>
          </div>
          <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2">
            <span>{{ stats.total_tpp }} TPP</span> &bull; 
            <span>{{ stats.total_sab }} SAB</span> &bull; 
            <span>{{ stats.total_hsbu }} HSBU</span>
          </div>
        </div>

        <!-- Card 2: Total Riwayat Inspeksi -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Riwayat Inspeksi</span>
            <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/50 flex items-center justify-center text-[#005851] dark:text-teal-400">
              <ShieldCheck class="w-5 h-5" />
            </div>
          </div>
          <div class="mt-2 flex items-baseline gap-2">
            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white font-mono">{{ stats.total_inspeksi }}</span>
            <span class="text-xs text-slate-400">Inspeksi Sanitasi</span>
          </div>
          <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2">
            <span>{{ stats.total_insp_tpp }} TPP</span> &bull; 
            <span>{{ stats.total_insp_sab }} SAB</span> &bull; 
            <span>{{ stats.total_insp_hsbu }} HSBU</span>
          </div>
        </div>

        <!-- Card 3: Kepatuhan Sanitasi -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Kepatuhan Sanitasi</span>
            <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center text-amber-600">
              <TrendingUp class="w-5 h-5" />
            </div>
          </div>
          <div class="mt-2 flex items-baseline gap-2">
            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white font-mono">{{ stats.kepatuhan_persen }}%</span>
            <span class="text-xs text-emerald-600 font-semibold font-mono">Risiko Rendah</span>
          </div>
          <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2">
            <span class="text-emerald-600 font-semibold">{{ stats.risiko_rendah }} Rendah</span> &bull; 
            <span class="text-amber-600 font-semibold">{{ stats.risiko_sedang }} Sedang</span> &bull; 
            <span class="text-rose-600 font-semibold">{{ stats.risiko_tinggi }} Tinggi</span>
          </div>
        </div>

        <!-- Card 4: SIVERA Vektor -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Surveilans Vektor (SIVERA)</span>
            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600">
              <Bug class="w-5 h-5" />
            </div>
          </div>
          <div class="mt-2 flex items-baseline gap-2">
            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white font-mono">{{ stats.sivera_total }}</span>
            <span class="text-xs text-slate-400">Pemantauan</span>
          </div>
          <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2">
            <span class="text-emerald-600 font-semibold">{{ stats.sivera_memenuhi }} Sesuai Baku</span> &bull; 
            <span class="text-rose-600 font-semibold">{{ stats.sivera_diatas_baku }} Di Atas Baku</span>
          </div>
        </div>
      </div>

      <!-- ==================== SUB-TABS NAVIGATION ==================== -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200 dark:border-slate-800">
        <button
          type="button"
          @click="switchTab('overview')"
          class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
          :class="currentTab === 'overview' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
        >
          <Activity class="w-3.5 h-3.5" />
          <span>Ringkasan &amp; Analitik PRL</span>
        </button>

        <button
          type="button"
          @click="switchTab('tpp')"
          class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
          :class="currentTab === 'tpp' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
        >
          <Utensils class="w-3.5 h-3.5" />
          <span>Tempat Pengelolaan Pangan (TPP)</span>
          <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20 font-mono">{{ stats.total_insp_tpp }}</span>
        </button>

        <button
          type="button"
          @click="switchTab('sab')"
          class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
          :class="currentTab === 'sab' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
        >
          <Droplets class="w-3.5 h-3.5" />
          <span>Sarana Air Bersih (SAB)</span>
          <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20 font-mono">{{ stats.total_insp_sab }}</span>
        </button>

        <button
          type="button"
          @click="switchTab('hsbu')"
          class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
          :class="currentTab === 'hsbu' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
        >
          <Building2 class="w-3.5 h-3.5" />
          <span>Higiene Bangunan &amp; Usaha (HSBU)</span>
          <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20 font-mono">{{ stats.total_insp_hsbu }}</span>
        </button>

        <button
          type="button"
          @click="switchTab('sivera')"
          class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
          :class="currentTab === 'sivera' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
        >
          <Bug class="w-3.5 h-3.5" />
          <span>Surveilans Vektor (SIVERA)</span>
          <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20 font-mono">{{ stats.sivera_total }}</span>
        </button>
      </div>

      <!-- ==================== FILTER BAR ==================== -->
      <div v-if="currentTab !== 'overview'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
          <div class="md:col-span-2">
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Cari Sarana / Pemeriksa / Lokasi</label>
            <div class="relative">
              <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
              <input
                type="text"
                v-model="search"
                @keyup.enter="applyFilter()"
                placeholder="Ketik nama sarana, gedung, pemilik, lokasi..."
                class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
              />
            </div>
          </div>

          <div>
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Wilayah Kerja</label>
            <select
              v-model="selectedWilker"
              @change="applyFilter()"
              class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
            >
              <option value="">Semua Wilker</option>
              <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Tingkat Risiko / Status</label>
            <select
              v-model="selectedRisiko"
              @change="applyFilter()"
              class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
            >
              <option value="">Semua Kategori</option>
              <option value="Rendah">Risiko Rendah / MS</option>
              <option value="Sedang">Risiko Sedang</option>
              <option value="Tinggi">Risiko Tinggi / TMS</option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="applyFilter()"
              class="w-full py-2 px-3 rounded-xl bg-[#005851] hover:bg-[#004842] text-white text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs"
            >
              <Filter class="w-3.5 h-3.5" />
              <span>Saring</span>
            </button>
            <button
              type="button"
              @click="resetFilter()"
              class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-500 transition cursor-pointer"
              title="Reset Filter"
            >
              <RotateCcw class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- ==================== TAB CONTENT: OVERVIEW ==================== -->
      <div v-if="currentTab === 'overview'" class="space-y-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
          <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <MapPin class="w-4 h-4 text-[#00A99D]" />
                <span>Sebaran Sarana &amp; Riwayat Inspeksi per Wilayah Kerja</span>
              </h2>
              <p class="text-xs text-slate-400 mt-0.5">Pemantauan kepatuhan higiene sanitasi pelabuhan dan bandara di bawah BKK Bandung.</p>
            </div>
          </div>

          <div class="overflow-x-auto mt-4">
            <table class="w-full text-xs text-left">
              <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                  <th class="py-3 px-3">Wilayah Kerja</th>
                  <th class="py-3 px-3 text-center">Total Sarana</th>
                  <th class="py-3 px-3 text-center">Total Inspeksi</th>
                  <th class="py-3 px-3 text-center">TPP</th>
                  <th class="py-3 px-3 text-center">SAB</th>
                  <th class="py-3 px-3 text-center">HSBU</th>
                  <th class="py-3 px-3 text-center">Aksi Cepat</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="w in stats.wilker_stats" :key="w.wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                  <td class="py-3 px-3 font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>{{ w.wilker }}</span>
                  </td>
                  <td class="py-3 px-3 text-center font-mono font-bold text-emerald-600">{{ w.total_sarana }}</td>
                  <td class="py-3 px-3 text-center font-mono font-bold text-slate-700 dark:text-slate-300">{{ w.total_inspeksi }}</td>
                  <td class="py-3 px-3 text-center font-mono text-slate-500">{{ w.tpp }}</td>
                  <td class="py-3 px-3 text-center font-mono text-slate-500">{{ w.sab }}</td>
                  <td class="py-3 px-3 text-center font-mono text-slate-500">{{ w.hsbu }}</td>
                  <td class="py-3 px-3 text-center">
                    <button
                      type="button"
                      @click="selectedWilker = w.wilker; switchTab('tpp')"
                      class="px-2.5 py-1 rounded-lg text-[11px] font-semibold border border-slate-200 hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800 transition cursor-pointer"
                    >
                      Buka Data
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ==================== TAB CONTENT: TPP ==================== -->
      <div v-if="currentTab === 'tpp'" class="space-y-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <Utensils class="w-4 h-4 text-amber-500" />
                <span>Riwayat Inspeksi Tempat Pengelolaan Pangan (TPP)</span>
              </h2>
              <p class="text-xs text-slate-400 mt-0.5">Daftar pemeriksaan higiene sanitasi pangan berbasis risiko di pelabuhan dan bandara.</p>
            </div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="openAddInspeksi('tpp')"
                class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:brightness-105 text-white text-xs font-bold flex items-center gap-1.5 cursor-pointer shadow-xs"
              >
                <Plus class="w-3.5 h-3.5" />
                <span>+ Input Inspeksi TPP Baru</span>
              </button>
              <button
                type="button"
                @click="openAddSarana('tpp')"
                class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-200 text-xs font-bold flex items-center gap-1 cursor-pointer"
              >
                <span>+ Tambah Sarana TPP</span>
              </button>
            </div>
          </div>

          <div class="overflow-x-auto mt-4">
            <table class="w-full text-xs text-left">
              <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                  <th class="py-3 px-3">No</th>
                  <th class="py-3 px-3">Wilayah Kerja</th>
                  <th class="py-3 px-3">Tanggal</th>
                  <th class="py-3 px-3">Nama TPP</th>
                  <th class="py-3 px-3">Penanggung Jawab</th>
                  <th class="py-3 px-3">Petugas Pemeriksa</th>
                  <th class="py-3 px-3 text-center">Skor &amp; Risiko</th>
                  <th class="py-3 px-3 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="(item, idx) in inspeksi_tpp.data" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                  <td class="py-3 px-3 text-slate-400 font-mono">{{ ((inspeksi_tpp.current_page - 1) * inspeksi_tpp.per_page) + idx + 1 }}</td>
                  <td class="py-3 px-3 font-semibold text-slate-800 dark:text-slate-200">{{ item.wilker }}</td>
                  <td class="py-3 px-3 font-mono text-slate-500">{{ formatDate(item.tanggal_inspeksi) }}</td>
                  <td class="py-3 px-3 font-bold text-[#005851] dark:text-emerald-400">{{ item.nama_tpp }}</td>
                  <td class="py-3 px-3 text-slate-600 dark:text-slate-300">{{ item.penanggung_jawab_tpp || '-' }}</td>
                  <td class="py-3 px-3 text-slate-500 max-w-[200px] truncate" :title="item.nama_pemeriksa">{{ item.nama_pemeriksa }}</td>
                  <td class="py-3 px-3 text-center">
                    <span
                      class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                      :class="getRiskBadgeClass(item.tingkat_risiko)"
                    >
                      <span>Skor: {{ item.hasil }}</span> &bull; <span>{{ item.tingkat_risiko }}</span>
                    </span>
                  </td>
                  <td class="py-3 px-3 text-center">
                    <div class="inline-flex items-center gap-1.5">
                      <button
                        type="button"
                        @click="openDetail(item, 'tpp')"
                        class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 text-slate-600 dark:text-slate-300 transition cursor-pointer"
                        title="Lihat Rincian Inspeksi"
                      >
                        <Eye class="w-3.5 h-3.5" />
                      </button>
                      <a
                        :href="`/prl/inspeksi/tpp/${item.id}/pdf`"
                        target="_blank"
                        class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 text-slate-600 dark:text-slate-300 transition cursor-pointer"
                        title="Cetak Lembar Pengawasan PDF"
                      >
                        <Printer class="w-3.5 h-3.5" />
                      </a>
                      <button
                        type="button"
                        @click="deleteInspection(item, 'tpp')"
                        class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-rose-50 text-rose-500 transition cursor-pointer"
                        title="Hapus Catatan"
                      >
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ==================== TAB CONTENT: SAB ==================== -->
      <div v-if="currentTab === 'sab'" class="space-y-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <Droplets class="w-4 h-4 text-cyan-500" />
                <span>Riwayat Inspeksi Kualitas Sanitasi Sarana Air Bersih (SAB)</span>
              </h2>
              <p class="text-xs text-slate-400 mt-0.5">Pemeriksaan kualitas fisik air dan 10 variabel risiko perlindungan fisik penampungan.</p>
            </div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="openAddInspeksi('sab')"
                class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-cyan-600 to-cyan-700 hover:brightness-105 text-white text-xs font-bold flex items-center gap-1.5 cursor-pointer shadow-xs"
              >
                <Plus class="w-3.5 h-3.5" />
                <span>+ Input Inspeksi SAB Baru</span>
              </button>
              <button
                type="button"
                @click="openAddSarana('sab')"
                class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-200 text-xs font-bold flex items-center gap-1 cursor-pointer"
              >
                <span>+ Tambah Sarana SAB</span>
              </button>
            </div>
          </div>

          <div class="overflow-x-auto mt-4">
            <table class="w-full text-xs text-left">
              <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                  <th class="py-3 px-3">No</th>
                  <th class="py-3 px-3">Wilayah Kerja</th>
                  <th class="py-3 px-3">Tanggal</th>
                  <th class="py-3 px-3">Nama SAB</th>
                  <th class="py-3 px-3">Sumber &amp; Kapasitas</th>
                  <th class="py-3 px-3">Petugas Pemeriksa</th>
                  <th class="py-3 px-3 text-center">Skor &amp; Risiko</th>
                  <th class="py-3 px-3 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="(item, idx) in inspeksi_sab.data" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                  <td class="py-3 px-3 text-slate-400 font-mono">{{ ((inspeksi_sab.current_page - 1) * inspeksi_sab.per_page) + idx + 1 }}</td>
                  <td class="py-3 px-3 font-semibold text-slate-800 dark:text-slate-200">{{ item.wilker }}</td>
                  <td class="py-3 px-3 font-mono text-slate-500">{{ formatDate(item.tanggal_inspeksi) }}</td>
                  <td class="py-3 px-3 font-bold text-[#005851] dark:text-emerald-400">{{ item.nama_sab }}</td>
                  <td class="py-3 px-3 text-slate-600 dark:text-slate-300">
                    {{ item.sumber_air_sab || '-' }} ({{ item.kapasitas_penampungan_sab || 0 }} L)
                  </td>
                  <td class="py-3 px-3 text-slate-500 max-w-[200px] truncate" :title="item.nama_pemeriksa">{{ item.nama_pemeriksa }}</td>
                  <td class="py-3 px-3 text-center">
                    <span
                      class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                      :class="getRiskBadgeClass(item.tingkat_risiko)"
                    >
                      <span>Skor: {{ item.hasil }}</span> &bull; <span>{{ item.tingkat_risiko }}</span>
                    </span>
                  </td>
                  <td class="py-3 px-3 text-center">
                    <div class="inline-flex items-center gap-1.5">
                      <button
                        type="button"
                        @click="openDetail(item, 'sab')"
                        class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 text-slate-600 dark:text-slate-300 transition cursor-pointer"
                        title="Lihat Rincian Inspeksi"
                      >
                        <Eye class="w-3.5 h-3.5" />
                      </button>
                      <a
                        :href="`/prl/inspeksi/sab/${item.id}/pdf`"
                        target="_blank"
                        class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 text-slate-600 dark:text-slate-300 transition cursor-pointer"
                        title="Cetak Lembar Pengawasan PDF"
                      >
                        <Printer class="w-3.5 h-3.5" />
                      </a>
                      <button
                        type="button"
                        @click="deleteInspection(item, 'sab')"
                        class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-rose-50 text-rose-500 transition cursor-pointer"
                        title="Hapus Catatan"
                      >
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ==================== TAB CONTENT: HSBU ==================== -->
      <div v-if="currentTab === 'hsbu'" class="space-y-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <Building2 class="w-4 h-4 text-[#00A99D]" />
                <span>Riwayat Inspeksi Higiene Sanitasi Bangunan dan Usaha (HSBU)</span>
              </h2>
              <p class="text-xs text-slate-400 mt-0.5">Pemeriksaan gedung, terminal, ruang kerja, pencahayaan, limbah, dan rekomendasi kelaikan sehat.</p>
            </div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="openAddInspeksi('hsbu')"
                class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-[#00A99D] to-[#005851] hover:brightness-105 text-white text-xs font-bold flex items-center gap-1.5 cursor-pointer shadow-xs"
              >
                <Plus class="w-3.5 h-3.5" />
                <span>+ Input Inspeksi HSBU Baru</span>
              </button>
              <button
                type="button"
                @click="openAddSarana('hsbu')"
                class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-200 text-xs font-bold flex items-center gap-1 cursor-pointer"
              >
                <span>+ Tambah Gedung HSBU</span>
              </button>
            </div>
          </div>

          <div class="overflow-x-auto mt-4">
            <table class="w-full text-xs text-left">
              <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                  <th class="py-3 px-3">No</th>
                  <th class="py-3 px-3">Wilayah Kerja</th>
                  <th class="py-3 px-3">Tanggal</th>
                  <th class="py-3 px-3">Nama Gedung/Bangunan</th>
                  <th class="py-3 px-3">Penanggung Jawab</th>
                  <th class="py-3 px-3">Petugas Pemeriksa</th>
                  <th class="py-3 px-3 text-center">Skor &amp; Risiko</th>
                  <th class="py-3 px-3 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="(item, idx) in inspeksi_hsbu.data" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                  <td class="py-3 px-3 text-slate-400 font-mono">{{ ((inspeksi_hsbu.current_page - 1) * inspeksi_hsbu.per_page) + idx + 1 }}</td>
                  <td class="py-3 px-3 font-semibold text-slate-800 dark:text-slate-200">{{ item.wilker }}</td>
                  <td class="py-3 px-3 font-mono text-slate-500">{{ formatDate(item.tanggal_inspeksi) }}</td>
                  <td class="py-3 px-3 font-bold text-[#005851] dark:text-emerald-400">{{ item.nama_gedung }}</td>
                  <td class="py-3 px-3 text-slate-600 dark:text-slate-300">{{ item.penanggung_jawab_hsbu || '-' }}</td>
                  <td class="py-3 px-3 text-slate-500 max-w-[200px] truncate" :title="item.nama_pemeriksa">{{ item.nama_pemeriksa }}</td>
                  <td class="py-3 px-3 text-center">
                    <span
                      class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                      :class="getRiskBadgeClass(item.tingkat_risiko)"
                    >
                      <span>Skor: {{ item.total }}</span> &bull; <span>{{ item.tingkat_risiko }}</span>
                    </span>
                  </td>
                  <td class="py-3 px-3 text-center">
                    <div class="inline-flex items-center gap-1.5">
                      <button
                        type="button"
                        @click="openDetail(item, 'hsbu')"
                        class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 text-slate-600 dark:text-slate-300 transition cursor-pointer"
                        title="Lihat Rincian Inspeksi"
                      >
                        <Eye class="w-3.5 h-3.5" />
                      </button>
                      <a
                        :href="`/prl/inspeksi/hsbu/${item.id}/pdf`"
                        target="_blank"
                        class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 text-slate-600 dark:text-slate-300 transition cursor-pointer"
                        title="Cetak Lembar Pengawasan PDF"
                      >
                        <Printer class="w-3.5 h-3.5" />
                      </a>
                      <button
                        type="button"
                        @click="deleteInspection(item, 'hsbu')"
                        class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-rose-50 text-rose-500 transition cursor-pointer"
                        title="Hapus Catatan"
                      >
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ==================== TAB CONTENT: SIVERA ==================== -->
      <div v-if="currentTab === 'sivera'" class="space-y-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
          <!-- Header SIVERA -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <Bug class="w-4 h-4 text-indigo-500" />
                <span>Surveilans Vektor dan Reservoar (SIVERA)</span>
              </h2>
              <p class="text-xs text-slate-400 mt-0.5">Pemantauan 7 jenis vektor &amp; binatang pembawa penyakit (Jentik Aedes, Lalat, Kecoa, Tikus, Pinjal, Anopheles) di perimeter dan buffer.</p>
            </div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="openAddInspeksi('sivera', siveraFilterVektor !== 'Semua Data' && siveraFilterVektor !== 'Dashboard' ? siveraFilterVektor : 'Jentik Aedes sp.')"
                class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:brightness-105 text-white text-xs font-bold flex items-center gap-1.5 cursor-pointer shadow-xs"
              >
                <Plus class="w-3.5 h-3.5" />
                <span>+ Catat Data Surveilans {{ siveraFilterVektor !== 'Semua Data' && siveraFilterVektor !== 'Dashboard' ? siveraFilterVektor : 'Baru' }}</span>
              </button>
            </div>
          </div>

          <!-- SIVERA Sub-Vektor Filter Navigation Pills -->
          <div class="flex items-center gap-1.5 overflow-x-auto py-3 border-b border-slate-100 dark:border-slate-800 text-xs">
            <button
              v-for="vTab in ['Dashboard', 'Semua Data', 'Jentik Aedes', 'Lalat', 'Kecoa', 'Tikus', 'Pinjal', 'Jentik Anopheles', 'Nyamuk Anopheles']"
              :key="vTab"
              type="button"
              @click="siveraFilterVektor = vTab"
              class="px-3 py-1.5 rounded-full font-semibold transition shrink-0 cursor-pointer flex items-center gap-1.5"
              :class="siveraFilterVektor === vTab ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
            >
              <Activity v-if="vTab === 'Dashboard'" class="w-3.5 h-3.5" />
              <span>{{ vTab }}</span>
            </button>
          </div>

          <!-- 1. SIVERA DASHBOARD VIEW -->
          <div v-if="siveraFilterVektor === 'Dashboard'" class="space-y-6 mt-4">
            <!-- Top Metric Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
              <div class="p-4 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-800/50">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-bold text-indigo-700 dark:text-indigo-400">Total Surveilans</span>
                  <Bug class="w-4 h-4 text-indigo-500" />
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white mt-2 font-mono">
                  {{ siveraDashboardMetrics.totalSurveilans }}
                </div>
                <span class="text-[11px] text-slate-500">Seluruh data surveilans vektor</span>
              </div>

              <div class="p-4 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/50">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">Memenuhi Syarat (MS)</span>
                  <CheckCircle2 class="w-4 h-4 text-emerald-500" />
                </div>
                <div class="text-2xl font-black text-emerald-700 dark:text-emerald-400 mt-2 font-mono">
                  {{ siveraDashboardMetrics.totalMs }}
                </div>
                <span class="text-[11px] text-slate-500">Indeks di bawah batas baku mutu</span>
              </div>

              <div class="p-4 rounded-2xl bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800/50">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-bold text-rose-700 dark:text-rose-400">Tidak Memenuhi Syarat</span>
                  <AlertTriangle class="w-4 h-4 text-rose-500" />
                </div>
                <div class="text-2xl font-black text-rose-700 dark:text-rose-400 mt-2 font-mono">
                  {{ siveraDashboardMetrics.totalTms }}
                </div>
                <span class="text-[11px] text-slate-500">Perlu tindakan pengendalian</span>
              </div>

              <div class="p-4 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/50">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-bold text-amber-700 dark:text-amber-400">Kepatuhan Baku Mutu</span>
                  <TrendingUp class="w-4 h-4 text-amber-500" />
                </div>
                <div class="text-2xl font-black text-amber-700 dark:text-amber-400 mt-2 font-mono">
                  {{ siveraDashboardMetrics.kepatuhanPct }}%
                </div>
                <span class="text-[11px] text-slate-500">Persentase status aman</span>
              </div>
            </div>

            <!-- 7 Vectors Breakdown Grid -->
            <div>
              <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <ShieldCheck class="w-4 h-4 text-indigo-500" />
                <span>Rangkuman 7 Vektor &amp; Binatang Pembawa Penyakit</span>
              </h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- 1. Jentik Aedes -->
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:border-indigo-300 transition flex flex-col justify-between">
                  <div>
                    <div class="flex items-center justify-between mb-1.5">
                      <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Jentik Aedes sp.</span>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">{{ siveraDashboardMetrics.jentikCount }} Survei</span>
                    </div>
                    <p class="text-[11px] text-slate-500">Rata-rata ABJ: <strong class="text-indigo-600 font-mono">{{ siveraDashboardMetrics.jentikAbjAvg }}%</strong> (Baku Mutu &ge; 95%)</p>
                    <div class="mt-2 text-[11px] text-emerald-600 font-semibold">{{ siveraDashboardMetrics.jentikMs }} Lokasi Memenuhi Syarat</div>
                  </div>
                  <button
                    type="button"
                    @click="siveraFilterVektor = 'Jentik Aedes'"
                    class="mt-3 w-full py-1.5 rounded-xl bg-slate-50 hover:bg-indigo-50 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer"
                  >
                    <span>Lihat Rincian</span>
                    <ChevronRight class="w-3.5 h-3.5" />
                  </button>
                </div>

                <!-- 2. Lalat -->
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:border-amber-300 transition flex flex-col justify-between">
                  <div>
                    <div class="flex items-center justify-between mb-1.5">
                      <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Lalat</span>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">{{ siveraDashboardMetrics.lalatCount }} Survei</span>
                    </div>
                    <p class="text-[11px] text-slate-500">Rata Fly Index: <strong class="text-amber-600 font-mono">{{ siveraDashboardMetrics.lalatAvg }}</strong> (Baku Mutu &lt; 2)</p>
                    <div class="mt-2 text-[11px] text-emerald-600 font-semibold">{{ siveraDashboardMetrics.lalatMs }} Lokasi MS &bull; <span class="text-rose-600">{{ siveraDashboardMetrics.lalatTms }} TMS</span></div>
                  </div>
                  <button
                    type="button"
                    @click="siveraFilterVektor = 'Lalat'"
                    class="mt-3 w-full py-1.5 rounded-xl bg-slate-50 hover:bg-amber-50 dark:bg-slate-800 text-amber-600 dark:text-amber-400 text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer"
                  >
                    <span>Lihat Rincian</span>
                    <ChevronRight class="w-3.5 h-3.5" />
                  </button>
                </div>

                <!-- 3. Kecoa -->
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:border-rose-300 transition flex flex-col justify-between">
                  <div>
                    <div class="flex items-center justify-between mb-1.5">
                      <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Kecoa</span>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">{{ siveraDashboardMetrics.kecoaCount }} Survei</span>
                    </div>
                    <p class="text-[11px] text-slate-500">Metode: Sticky Trap (Baku Mutu &lt; 2 ekor/trap)</p>
                    <div class="mt-2 text-[11px] text-emerald-600 font-semibold">{{ siveraDashboardMetrics.kecoaMs }} Lokasi Memenuhi Syarat</div>
                  </div>
                  <button
                    type="button"
                    @click="siveraFilterVektor = 'Kecoa'"
                    class="mt-3 w-full py-1.5 rounded-xl bg-slate-50 hover:bg-rose-50 dark:bg-slate-800 text-rose-600 dark:text-rose-400 text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer"
                  >
                    <span>Lihat Rincian</span>
                    <ChevronRight class="w-3.5 h-3.5" />
                  </button>
                </div>

                <!-- 4. Tikus -->
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:border-slate-400 transition flex flex-col justify-between">
                  <div>
                    <div class="flex items-center justify-between mb-1.5">
                      <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Tikus</span>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">{{ siveraDashboardMetrics.tikusCount }} Survei</span>
                    </div>
                    <p class="text-[11px] text-slate-500">Rata Success Trap: <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ siveraDashboardMetrics.tikusTrapAvg }}%</strong> (&lt; 1% MS)</p>
                    <div class="mt-2 text-[11px] text-emerald-600 font-semibold">{{ siveraDashboardMetrics.tikusMs }} Lokasi Memenuhi Syarat</div>
                  </div>
                  <button
                    type="button"
                    @click="siveraFilterVektor = 'Tikus'"
                    class="mt-3 w-full py-1.5 rounded-xl bg-slate-50 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer"
                  >
                    <span>Lihat Rincian</span>
                    <ChevronRight class="w-3.5 h-3.5" />
                  </button>
                </div>

                <!-- 5. Pinjal -->
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:border-cyan-300 transition flex flex-col justify-between">
                  <div>
                    <div class="flex items-center justify-between mb-1.5">
                      <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Pinjal Tikus</span>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">{{ siveraDashboardMetrics.pinjalCount }} Survei</span>
                    </div>
                    <p class="text-[11px] text-slate-500">Indeks Umum (&lt; 2) &amp; Khusus X. cheopis (&lt; 1)</p>
                    <div class="mt-2 text-[11px] text-emerald-600 font-semibold">{{ siveraDashboardMetrics.pinjalMs }} Lokasi Memenuhi Syarat</div>
                  </div>
                  <button
                    type="button"
                    @click="siveraFilterVektor = 'Pinjal'"
                    class="mt-3 w-full py-1.5 rounded-xl bg-slate-50 hover:bg-cyan-50 dark:bg-slate-800 text-cyan-600 dark:text-cyan-400 text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer"
                  >
                    <span>Lihat Rincian</span>
                    <ChevronRight class="w-3.5 h-3.5" />
                  </button>
                </div>

                <!-- 6. Jentik Anopheles -->
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:border-teal-300 transition flex flex-col justify-between">
                  <div>
                    <div class="flex items-center justify-between mb-1.5">
                      <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Jentik Anopheles</span>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">{{ siveraDashboardMetrics.anophelesJentikCount }} Survei</span>
                    </div>
                    <p class="text-[11px] text-slate-500">Indeks Habitat Pesisir &amp; Payau (Baku Mutu &lt; 1%)</p>
                    <div class="mt-2 text-[11px] text-emerald-600 font-semibold">{{ siveraDashboardMetrics.anophelesJentikMs }} Lokasi Memenuhi Syarat</div>
                  </div>
                  <button
                    type="button"
                    @click="siveraFilterVektor = 'Jentik Anopheles'"
                    class="mt-3 w-full py-1.5 rounded-xl bg-slate-50 hover:bg-teal-50 dark:bg-slate-800 text-teal-600 dark:text-teal-400 text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer"
                  >
                    <span>Lihat Rincian</span>
                    <ChevronRight class="w-3.5 h-3.5" />
                  </button>
                </div>

                <!-- 7. Nyamuk Anopheles Dewasa -->
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:border-purple-300 transition flex flex-col justify-between">
                  <div>
                    <div class="flex items-center justify-between mb-1.5">
                      <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Nyamuk Anopheles</span>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">{{ siveraDashboardMetrics.anophelesNyamukCount }} Survei</span>
                    </div>
                    <p class="text-[11px] text-slate-500">Matriks 12 Jam (18.00-06.00) &bull; MBR / MHD</p>
                    <div class="mt-2 text-[11px] text-emerald-600 font-semibold">{{ siveraDashboardMetrics.anophelesNyamukMs }} Lokasi Memenuhi Syarat</div>
                  </div>
                  <button
                    type="button"
                    @click="siveraFilterVektor = 'Nyamuk Anopheles'"
                    class="mt-3 w-full py-1.5 rounded-xl bg-slate-50 hover:bg-purple-50 dark:bg-slate-800 text-purple-600 dark:text-purple-400 text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer"
                  >
                    <span>Lihat Rincian</span>
                    <ChevronRight class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Wilayah Kerja Summary Table -->
            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
              <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <MapPin class="w-4 h-4 text-emerald-600" />
                <span>Distribusi Pemantauan Vektor Berdasarkan Wilayah Kerja (Wilker)</span>
              </h3>
              <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                  <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                      <th class="py-2.5 px-3">Wilayah Kerja</th>
                      <th class="py-2.5 px-3 text-center">Total Surveilans</th>
                      <th class="py-2.5 px-3 text-center">Memenuhi Syarat (MS)</th>
                      <th class="py-2.5 px-3 text-center">Tidak Memenuhi Syarat (TMS)</th>
                      <th class="py-2.5 px-3 text-center">Persentase Kepatuhan</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="w in siveraDashboardMetrics.wilkerBreakdown" :key="w.wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                      <td class="py-2.5 px-3 font-semibold text-slate-800 dark:text-slate-200">{{ w.wilker }}</td>
                      <td class="py-2.5 px-3 text-center font-mono font-bold">{{ w.total }}</td>
                      <td class="py-2.5 px-3 text-center font-mono text-emerald-600 font-semibold">{{ w.ms }}</td>
                      <td class="py-2.5 px-3 text-center font-mono text-rose-600 font-semibold">{{ w.tms }}</td>
                      <td class="py-2.5 px-3 text-center font-mono font-bold">
                        <span class="px-2 py-0.5 rounded-full text-[10px]" :class="w.tms === 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'">
                          {{ w.total > 0 ? ((w.ms / w.total) * 100).toFixed(0) : 100 }}%
                        </span>
                      </td>
                    </tr>
                    <tr v-if="siveraDashboardMetrics.wilkerBreakdown.length === 0">
                      <td colspan="5" class="py-4 text-center text-slate-400">Belum ada data surveilans vektor SIVERA yang tercatat.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- 2. SIVERA RECORDS TABLE VIEW -->
          <div v-else class="overflow-x-auto mt-4">
            <table class="w-full text-xs text-left">
              <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                  <th class="py-3 px-3">No</th>
                  <th class="py-3 px-3">Wilayah Kerja</th>
                  <th class="py-3 px-3">Tanggal</th>
                  <th class="py-3 px-3">Jenis Vektor</th>
                  <th class="py-3 px-3">Lokasi / Titik</th>
                  <th class="py-3 px-3">Hasil Indeks &amp; Parameter</th>
                  <th class="py-3 px-3 text-center">Baku Mutu</th>
                  <th class="py-3 px-3">Status Pengendalian</th>
                  <th class="py-3 px-3 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="(item, idx) in filteredSiveraData" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                  <td class="py-3 px-3 text-slate-400 font-mono">{{ idx + 1 }}</td>
                  <td class="py-3 px-3 font-semibold text-slate-800 dark:text-slate-200">{{ item.wilker }}</td>
                  <td class="py-3 px-3 font-mono text-slate-500">{{ formatDate(item.tanggal) }}</td>
                  <td class="py-3 px-3 font-bold text-indigo-600 dark:text-indigo-400">{{ item.jenis_vektor }}</td>
                  <td class="py-3 px-3 text-slate-600 dark:text-slate-300">{{ item.lokasi || '-' }}</td>
                  <td class="py-3 px-3 font-mono">
                    <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ item.parameter_utama }}</span>
                  </td>
                  <td class="py-3 px-3 text-center">
                    <span
                      class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                      :class="getRiskBadgeClass(item.baku_mutu_status)"
                    >
                      {{ item.baku_mutu_status }}
                    </span>
                  </td>
                  <td class="py-3 px-3">
                    <div class="flex flex-col gap-1 min-w-[190px] max-w-[240px]">
                      <!-- Status Pill -->
                      <div v-if="(item.baku_mutu_status || '').toLowerCase().includes('tidak') || (item.baku_mutu_status || '').toLowerCase().includes('atas')">
                        <span
                          v-if="item.tindakan_pengendalian"
                          class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800"
                        >
                          <CheckCircle2 class="w-3 h-3 text-emerald-600" />
                          <span>Sudah Dikendalikan</span>
                        </span>
                        <span
                          v-else
                          class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800"
                        >
                          <AlertTriangle class="w-3 h-3 text-rose-600" />
                          <span>Perlu Pengendalian</span>
                        </span>
                      </div>
                      <div v-else>
                        <span
                          v-if="item.tindakan_pengendalian"
                          class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200 dark:bg-teal-950/40 dark:text-teal-300 dark:border-teal-800"
                        >
                          <ShieldCheck class="w-3 h-3 text-teal-600" />
                          <span>Pengendalian Rutin</span>
                        </span>
                        <span
                          v-else
                          class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700"
                        >
                          <Check class="w-3 h-3 text-slate-500" />
                          <span>Baku Mutu Aman</span>
                        </span>
                      </div>

                      <!-- Action Description Snippet -->
                      <p
                        v-if="item.tindakan_pengendalian"
                        class="text-[11px] text-slate-600 dark:text-slate-300 truncate"
                        :title="formatTindakanPengendalian(item.tindakan_pengendalian)"
                      >
                        {{ formatTindakanPengendalian(item.tindakan_pengendalian) }}
                      </p>
                      <p
                        v-else
                        class="text-[10px] text-slate-400 italic"
                      >
                        {{ (item.baku_mutu_status || '').toLowerCase().includes('tidak') ? 'Menunggu intervensi petugas' : 'Kondisi vektor terkendali' }}
                      </p>
                    </div>
                  </td>
                  <td class="py-3 px-3 text-center">
                    <div class="inline-flex items-center gap-1">
                      <!-- PDF Download / Preview Button (Sesuai Simply CI4) -->
                      <div v-if="(item.jenis_vektor || '').includes('Jentik Aedes')" class="inline-flex items-center gap-0.5">
                        <a
                          :href="`/prl/sivera/${item.id}/pdf/jentik`"
                          target="_blank"
                          class="p-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 dark:border-rose-900/40 dark:bg-rose-950/40 dark:text-rose-400 transition cursor-pointer"
                          title="Cetak PDF Form Pengendalian Jentik"
                        >
                          <Printer class="w-3.5 h-3.5" />
                        </a>
                        <a
                          :href="`/prl/sivera/${item.id}/pdf/fogging`"
                          target="_blank"
                          class="px-1.5 py-1 rounded-lg border border-orange-200 bg-orange-50 hover:bg-orange-100 text-orange-600 dark:border-orange-900/40 dark:bg-orange-950/40 dark:text-orange-400 text-[10px] font-bold transition cursor-pointer"
                          title="Cetak PDF Form Pengendalian Fogging"
                        >
                          Fogging
                        </a>
                      </div>
                      <a
                        v-else
                        :href="`/prl/sivera/${item.id}/pdf`"
                        target="_blank"
                        class="p-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 dark:border-rose-900/40 dark:bg-rose-950/40 dark:text-rose-400 transition cursor-pointer"
                        title="Cetak Laporan PDF Pengendalian & Surveilans"
                      >
                        <Printer class="w-3.5 h-3.5" />
                      </a>

                      <!-- Kendalikan Action Button -->
                      <button
                        type="button"
                        @click="openPengendalian(item)"
                        class="px-2 py-1 rounded-lg border border-teal-200 bg-teal-50 hover:bg-teal-100 text-teal-700 dark:border-teal-800 dark:bg-teal-950/40 dark:text-teal-300 text-[10px] font-bold flex items-center gap-1 transition cursor-pointer"
                        title="Input / Update Tindakan Pengendalian"
                      >
                        <ShieldCheck class="w-3 h-3" />
                        <span>Kendalikan</span>
                      </button>
                      <button
                        type="button"
                        @click="editSivera(item)"
                        class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 text-indigo-600 dark:text-indigo-400 transition cursor-pointer"
                        title="Edit Data"
                      >
                        <Edit class="w-3.5 h-3.5" />
                      </button>
                      <button
                        type="button"
                        @click="openDetail(item, 'sivera')"
                        class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 text-slate-600 dark:text-slate-300 transition cursor-pointer"
                        title="Lihat Detail"
                      >
                        <Eye class="w-3.5 h-3.5" />
                      </button>
                      <button
                        type="button"
                        @click="deleteInspection(item, 'sivera')"
                        class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-rose-50 text-rose-500 transition cursor-pointer"
                        title="Hapus Catatan"
                      >
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredSiveraData.length === 0">
                  <td colspan="9" class="text-center py-8 text-xs text-slate-400">Tidak ada data surveilans vektor SIVERA yang sesuai dengan filter.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- MODAL INPUT INSPEKSI SANITASI & SIVERA                         -->
      <!-- ============================================================== -->
      <div
        v-if="showAddInspeksiModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
      >
        <div class="relative w-full max-w-4xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden my-8">
          <!-- Modal Header -->
          <div class="px-6 py-4 bg-gradient-to-r from-[#005851] to-[#00A99D] text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
              <ClipboardCheck class="w-5 h-5" />
              <h3 class="text-base font-bold">{{ isEditMode ? 'Edit Catatan Surveilans Vektor (SIVERA)' : 'Input Inspeksi Sanitasi &amp; Surveilans Baru' }}</h3>
            </div>
            <button
              type="button"
              @click="showAddInspeksiModal = false"
              class="p-1 rounded-lg hover:bg-white/20 transition cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <!-- Main Sub-module Switcher Tabs inside Modal -->
          <div class="flex border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 p-2 gap-2 overflow-x-auto">
            <button
              type="button"
              @click="addInspeksiType = 'tpp'"
              class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer shrink-0"
              :class="addInspeksiType === 'tpp' ? 'bg-white dark:bg-slate-900 text-amber-600 shadow-xs border border-slate-200 dark:border-slate-700' : 'text-slate-500 hover:text-slate-800'"
            >
              <Utensils class="w-4 h-4" />
              <span>Inspeksi TPP (Pangan)</span>
            </button>
            <button
              type="button"
              @click="addInspeksiType = 'sab'"
              class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer shrink-0"
              :class="addInspeksiType === 'sab' ? 'bg-white dark:bg-slate-900 text-cyan-600 shadow-xs border border-slate-200 dark:border-slate-700' : 'text-slate-500 hover:text-slate-800'"
            >
              <Droplets class="w-4 h-4" />
              <span>Inspeksi SAB (Air Bersih)</span>
            </button>
            <button
              type="button"
              @click="addInspeksiType = 'hsbu'"
              class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer shrink-0"
              :class="addInspeksiType === 'hsbu' ? 'bg-white dark:bg-slate-900 text-[#00A99D] shadow-xs border border-slate-200 dark:border-slate-700' : 'text-slate-500 hover:text-slate-800'"
            >
              <Building2 class="w-4 h-4" />
              <span>Inspeksi HSBU (Gedung)</span>
            </button>
            <button
              type="button"
              @click="addInspeksiType = 'sivera'"
              class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer shrink-0"
              :class="addInspeksiType === 'sivera' ? 'bg-white dark:bg-slate-900 text-indigo-600 shadow-xs border border-slate-200 dark:border-slate-700' : 'text-slate-500 hover:text-slate-800'"
            >
              <Bug class="w-4 h-4" />
              <span>Surveilans SIVERA (Vektor)</span>
            </button>
          </div>

          <!-- Modal Body Form -->
          <div class="p-6 max-h-[75vh] overflow-y-auto space-y-6">

            <!-- ================= FORM 1: INSPEKSI TPP ================= -->
            <div v-if="addInspeksiType === 'tpp'" class="space-y-5">
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
                  <select
                    v-model="formTpp.wilker"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"
                  >
                    <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Inspeksi *</label>
                  <input
                    type="date"
                    v-model="formTpp.tanggal_inspeksi"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Sarana TPP Terdaftar</label>
                  <select
                    v-model="formTpp.master_tpp_id"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"
                  >
                    <option value="">-- Ketik Manual atau Pilih Sarana --</option>
                    <option v-for="m in master_tpp" :key="m.id" :value="m.id">{{ m.nama_tpp }} ({{ m.wilker }})</option>
                  </select>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Tempat Pengelolaan Pangan *</label>
                  <input
                    type="text"
                    v-model="formTpp.nama_tpp"
                    placeholder="Contoh: Kantin Dermaga 01"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pemilik / Penanggung Jawab</label>
                  <input
                    type="text"
                    v-model="formTpp.penanggung_jawab_tpp"
                    placeholder="Nama pemilik/penanggung jawab"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Petugas Pemeriksa Karantina</label>
                  <input
                    type="text"
                    v-model="formTpp.nama_pemeriksa"
                    list="pemeriksa-list"
                    placeholder="Nama pemeriksa (dapat pilih dropdown)"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"
                  />
                  <datalist id="pemeriksa-list">
                    <option v-for="p in pemeriksa_options" :key="p" :value="p" />
                  </datalist>
                </div>
              </div>

              <!-- Questionnaire Sections -->
              <div class="space-y-4 pt-2 border-t border-slate-200 dark:border-slate-800">
                <!-- Section A -->
                <div class="p-4 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40 space-y-3">
                  <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-amber-800 dark:text-amber-400">A. Penilaian Skor Profil Pangan (pH &amp; Aw)</h4>
                    <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800">Skor A: {{ skorTppSecA }}</span>
                  </div>
                  <div class="text-xs text-slate-600 dark:text-slate-300 space-y-2">
                    <div class="flex items-center justify-between">
                      <span>1. Apakah pangan memiliki pH &lt; 4,6 atau Aw &lt; 0,85 atau mengandung gula tinggi?</span>
                      <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_a_satu" value="Ya" /> Ya</label>
                        <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_a_satu" value="Tidak" /> Tidak</label>
                      </div>
                    </div>
                    <div v-if="formTpp.sec_a_satu === 'Tidak'" class="flex items-center justify-between pt-1 border-t border-amber-100 dark:border-amber-900/30">
                      <span>2. Apakah pangan termasuk kategori pangan berisiko tinggi (PHF)?</span>
                      <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_a_dua" value="Ya" /> Ya</label>
                        <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_a_dua" value="Tidak" /> Tidak</label>
                      </div>
                    </div>
                    <div v-if="formTpp.sec_a_satu === 'Tidak' && formTpp.sec_a_dua === 'Ya'" class="flex items-center justify-between pt-1 border-t border-amber-100 dark:border-amber-900/30">
                      <span>3. Apakah pH produk merupakan kombinasi pH &gt; 4,6 dan Aw &gt; 0,85?</span>
                      <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_a_tiga" value="Ya" /> Ya</label>
                        <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_a_tiga" value="Tidak" /> Tidak</label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Section B -->
                <div class="p-4 rounded-2xl bg-teal-50/50 dark:bg-teal-950/20 border border-teal-200/60 dark:border-teal-900/40 space-y-3">
                  <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-teal-800 dark:text-teal-400">B. Penilaian Mitigasi Bahaya Pangan</h4>
                    <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-full bg-teal-100 text-teal-800">Skor B: {{ skorTppSecB }}</span>
                  </div>
                  <div class="text-xs text-slate-600 dark:text-slate-300 space-y-2">
                    <div class="flex items-center justify-between">
                      <span>1. Apakah pangan dikonsumsi dalam kondisi mentah / tanpa proses pembunuhan bahaya?</span>
                      <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_b_satu" value="Ya" /> Ya</label>
                        <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_b_satu" value="Tidak" /> Tidak</label>
                      </div>
                    </div>
                    <div v-if="formTpp.sec_b_satu === 'Tidak'" class="flex items-center justify-between pt-1 border-t border-teal-100 dark:border-teal-900/30">
                      <span>2. Apakah pangan langsung dikonsumsi / danger zone max 4 jam / ada sterilisasi?</span>
                      <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_b_dua" value="Ya" /> Ya</label>
                        <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_b_dua" value="Tidak" /> Tidak</label>
                      </div>
                    </div>
                    <div v-if="formTpp.sec_b_satu === 'Tidak' && formTpp.sec_b_dua === 'Tidak'" class="flex items-center justify-between pt-1 border-t border-teal-100 dark:border-teal-900/30">
                      <span>3. Apakah pangan dikemas tertutup rapat untuk menghindari kontaminasi?</span>
                      <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_b_tiga" value="Ya" /> Ya</label>
                        <label class="flex items-center gap-1 cursor-pointer"><input type="radio" v-model="formTpp.sec_b_tiga" value="Tidak" /> Tidak</label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Section C & D -->
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
                          <option value=">100 Pekerja">>100 Pekerja (Besar)</option>
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
                      <input
                        type="number"
                        min="0"
                        max="100"
                        v-model="formTpp.sec_d_nilai_inspeksi_sebelumnya"
                        class="w-full px-2.5 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono"
                      />
                    </div>
                  </div>
                </div>

                <!-- Live Result Score Box -->
                <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-500/10 to-teal-500/10 border border-emerald-500/30 flex items-center justify-between">
                  <div>
                    <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300">Total Skor Inspeksi TPP:</span>
                    <span class="ml-2 text-xl font-extrabold font-mono text-[#005851] dark:text-emerald-400">{{ totalSkorTpp }}</span>
                  </div>
                  <span
                    class="px-3 py-1 rounded-full text-xs font-bold border"
                    :class="getRiskBadgeClass(tingkatRisikoTpp)"
                  >
                    Tingkat Risiko: {{ tingkatRisikoTpp }}
                  </span>
                </div>

                <!-- Kesimpulan & Saran -->
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

            <!-- ================= FORM 2: INSPEKSI SAB ================= -->
            <div v-if="addInspeksiType === 'sab'" class="space-y-5">
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
                  <select v-model="formSab.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                    <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
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
                    <option v-for="m in master_sab" :key="m.id" :value="m.id">{{ m.nama_sab }} ({{ m.wilker }})</option>
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

              <!-- Parameter Fisik & 10 Titik Risiko -->
              <div class="p-4 rounded-2xl bg-cyan-50/50 dark:bg-cyan-950/20 border border-cyan-200/60 space-y-2">
                <h4 class="text-xs font-bold text-cyan-800 dark:text-cyan-400">Parameter Kualitas Fisik Air Bersih</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                  <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-900 border border-cyan-100">
                    <span>Keruh?</span>
                    <label class="cursor-pointer"><input type="checkbox" v-model="formSab.sec_b_satu" true-value="Ya" false-value="Tidak" /> Ya</label>
                  </div>
                  <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-900 border border-cyan-100">
                    <span>Berbau?</span>
                    <label class="cursor-pointer"><input type="checkbox" v-model="formSab.sec_b_dua" true-value="Ya" false-value="Tidak" /> Ya</label>
                  </div>
                  <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-900 border border-cyan-100">
                    <span>Berasa?</span>
                    <label class="cursor-pointer"><input type="checkbox" v-model="formSab.sec_b_tiga" true-value="Ya" false-value="Tidak" /> Ya</label>
                  </div>
                  <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-900 border border-cyan-100">
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
                  <div v-for="(q, idx) in [
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
                  ]" :key="idx" class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                    <span>{{ idx + 1 }}. {{ q }}</span>
                    <div class="flex items-center gap-3">
                      <label class="cursor-pointer"><input type="radio" v-model="formSab['sec_c_' + (['satu','dua','tiga','empat','lima','enam','tujuh','delapan','sembilan','sepuluh'][idx])]" value="Ya" /> Ya</label>
                      <label class="cursor-pointer"><input type="radio" v-model="formSab['sec_c_' + (['satu','dua','tiga','empat','lima','enam','tujuh','delapan','sembilan','sepuluh'][idx])]" value="Tidak" /> Tidak</label>
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

            <!-- ================= FORM 3: INSPEKSI HSBU ================= -->
            <div v-if="addInspeksiType === 'hsbu'" class="space-y-5">
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
                  <select v-model="formHsbu.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                    <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
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
                    <option v-for="m in master_hsbu" :key="m.id" :value="m.id">{{ m.nama_gedung }} ({{ m.wilker }})</option>
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
                  <input type="text" v-model="formHsbu.nama_pemeriksa" list="pemeriksa-list" placeholder="Nama petugas" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
                </div>
              </div>

              <!-- Komponen Penilaian Gedung -->
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

            <!-- ========================================================= -->
            <!-- FORM 4: SURVEILANS SIVERA (AUTHENTIC VECTOR-SPECIFIC UI)  -->
            <!-- ========================================================= -->
            <div v-if="addInspeksiType === 'sivera'" class="space-y-5">
              <!-- SIVERA Vector Selector Pills inside Modal -->
              <div class="flex items-center gap-1.5 overflow-x-auto pb-2 border-b border-slate-200 dark:border-slate-800">
                <button
                  v-for="vName in ['Jentik Aedes sp.', 'Lalat', 'Kecoa', 'Tikus', 'Pinjal', 'Jentik Anopheles', 'Nyamuk Anopheles']"
                  :key="vName"
                  type="button"
                  @click="siveraActiveVektor = vName"
                  class="px-3 py-1.5 rounded-full text-xs font-bold transition shrink-0 cursor-pointer flex items-center gap-1.5"
                  :class="siveraActiveVektor === vName ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
                >
                  <Bug class="w-3.5 h-3.5" />
                  <span>{{ vName }}</span>
                </button>
              </div>

              <!-- 4.1 SIVERA FORM: JENTIK AEDES SP. -->
              <div v-if="siveraActiveVektor === 'Jentik Aedes sp.'" class="space-y-4">
                <div class="p-3 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-200/60">
                  <span class="text-xs font-bold text-indigo-900 dark:text-indigo-300 flex items-center gap-1.5">
                    <Droplets class="w-4 h-4 text-indigo-600" />
                    <span>Surveilans Jentik Aedes sp. (Demam Berdarah Dengue)</span>
                  </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
                    <select v-model="formSiveraJentik.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                      <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
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

                <!-- Parameters A B C D -->
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

                  <!-- Live Calculation Box -->
                  <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-indigo-100 dark:border-indigo-950 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-4">
                      <div><span class="text-slate-400">House Index (HI):</span> <strong class="text-indigo-600 font-mono text-sm ml-1">{{ hiJentik }}%</strong></div>
                      <div><span class="text-slate-400">Container Index (CI):</span> <strong class="text-indigo-600 font-mono text-sm ml-1">{{ ciJentik }}%</strong></div>
                      <div><span class="text-slate-400">Breteau Index (BI):</span> <strong class="text-indigo-600 font-mono text-sm ml-1">{{ biJentik }}</strong></div>
                      <div><span class="text-slate-400">Angka Bebas Jentik (ABJ):</span> <strong class="text-emerald-600 font-mono text-sm ml-1">{{ abjJentik }}%</strong></div>
                    </div>
                    <div>
                      <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getRiskBadgeClass(statusJentik)">
                        {{ statusJentik }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Jenis Kontainer Positif Checkboxes -->
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

              <!-- 4.2 SIVERA FORM: LALAT (MULTI-LOKASI DENGAN METODE FLY GRILL) -->
              <div v-if="siveraActiveVektor === 'Lalat'" class="space-y-4">
                <div class="p-3 rounded-2xl bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200/60">
                  <span class="text-xs font-bold text-amber-900 dark:text-amber-300 flex items-center gap-1.5">
                    <Bug class="w-4 h-4 text-amber-600" />
                    <span>Surveilans Kepadatan Lalat (Metode Fly Grill - Multi-Lokasi &amp; Top 5 Tertinggi)</span>
                  </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
                    <select v-model="formSiveraLalat.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                      <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
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

                <!-- Dynamic Multi-Location Cards -->
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
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
                      <span>+ Tambah Titik Lokasi</span>
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
                        <span class="text-xs font-mono font-bold text-slate-500 hidden sm:inline">
                          Top 5: {{ getLokasiLalatTop5(locItem).join(', ') }}
                        </span>
                        <button
                          v-if="formSiveraLalat.lokasi_items.length > 1"
                          type="button"
                          @click="removeLokasiLalat(lIdx)"
                          class="p-1 rounded-lg hover:bg-rose-100 text-rose-500 transition cursor-pointer"
                          title="Hapus Titik Ini"
                        >
                          <Trash2 class="w-4 h-4" />
                        </button>
                      </div>
                    </div>

                    <!-- 10 Fly Grill Inputs -->
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

                    <!-- Location Sub-Calculation Box -->
                    <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-200/80 dark:border-amber-900 flex items-center justify-between text-xs">
                      <div>
                        <span class="text-slate-500">Total Top 5: <strong class="font-mono text-dark">{{ getLokasiLalatTotalTop5(locItem) }}</strong></span>
                        <span class="mx-2">&bull;</span>
                        <span class="text-slate-700 dark:text-slate-300 font-semibold">Fly Index: <strong class="text-amber-600 font-mono text-sm">{{ getLokasiLalatRata(locItem) }}</strong></span>
                      </div>
                      <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="getRiskBadgeClass(getLokasiLalatStatus(locItem))">
                        {{ getLokasiLalatStatus(locItem) }} (&lt; 2 MS)
                      </span>
                    </div>
                  </div>

                  <!-- Combined Calculation Summary Box -->
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

              <!-- 4.3 SIVERA FORM: KECOA -->
              <div v-if="siveraActiveVektor === 'Kecoa'" class="space-y-4">
                <div class="p-3 rounded-2xl bg-rose-50/60 dark:bg-rose-950/30 border border-rose-200/60">
                  <span class="text-xs font-bold text-rose-900 dark:text-rose-300 flex items-center gap-1.5">
                    <Bug class="w-4 h-4 text-rose-600" />
                    <span>Surveilans Kepadatan Kecoa (Metode Trapping / Perangkap)</span>
                  </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
                    <select v-model="formSiveraKecoa.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                      <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
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

                  <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-rose-200 flex items-center justify-between text-xs">
                    <div>
                      <span class="text-slate-500">Total Tertangkap: <strong class="font-mono text-dark">{{ totalKecoaTertangkap }}</strong> ekor</span>
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

              <!-- 4.4 SIVERA FORM: TIKUS -->
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
                      <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
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

                <!-- Multi-spesies tikus container -->
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
                      <button type="button" @click="removeSpesiesTikusRow(idx)" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg cursor-pointer">
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>

                  <!-- Perangkap & Success Trap Calculation -->
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

                  <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 flex items-center justify-between text-xs">
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

              <!-- 4.5 SIVERA FORM: PINJAL TIKUS -->
              <div v-if="siveraActiveVektor === 'Pinjal'" class="space-y-4">
                <div class="p-3 rounded-2xl bg-cyan-50/60 dark:bg-cyan-950/30 border border-cyan-200/60">
                  <span class="text-xs font-bold text-cyan-900 dark:text-cyan-300 flex items-center gap-1.5">
                    <Bug class="w-4 h-4 text-cyan-600" />
                    <span>Surveilans Indeks Pinjal Tikus (Vektor Pes / Xenopsylla cheopis)</span>
                  </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
                    <select v-model="formSiveraPinjal.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                      <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
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

                <div class="grid grid-cols-3 gap-3 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
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

                <div class="grid grid-cols-2 gap-3">
                  <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 flex items-center justify-between text-xs">
                    <div>
                      <span class="text-slate-400 block text-[11px]">Indeks Pinjal Umum (Rumus: Umum / Tikus):</span>
                      <strong class="text-cyan-600 font-mono text-base">{{ indeksPinjalUmum }}</strong>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="indeksPinjalUmum < 2.0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'">
                      {{ indeksPinjalUmum < 2.0 ? 'MS (< 2)' : 'TMS (≥ 2)' }}
                    </span>
                  </div>
                  <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 flex items-center justify-between text-xs">
                    <div>
                      <span class="text-slate-400 block text-[11px]">Indeks Pinjal Khusus (Khusus / Tikus):</span>
                      <strong class="text-cyan-600 font-mono text-base">{{ indeksPinjalKhusus }}</strong>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="indeksPinjalKhusus < 1.0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'">
                      {{ indeksPinjalKhusus < 1.0 ? 'MS (< 1)' : 'TMS (≥ 1)' }}
                    </span>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tindakan Pengendalian</label>
                  <textarea v-model="formSiveraPinjal.tindakan_pengendalian" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
                </div>
              </div>

              <!-- 4.6 SIVERA FORM: JENTIK ANOPHELES (10 HABITAT & KONDISI AIR) -->
              <div v-if="siveraActiveVektor === 'Jentik Anopheles'" class="space-y-4">
                <div class="p-3 rounded-2xl bg-teal-50/60 dark:bg-teal-950/30 border border-teal-200/60">
                  <span class="text-xs font-bold text-teal-900 dark:text-teal-300 flex items-center gap-1.5">
                    <Bug class="w-4 h-4 text-teal-600" />
                    <span>Surveilans Jentik Anopheles (Vektor Malaria &amp; 10 Tipe Habitat Air)</span>
                  </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
                    <select v-model="formSiveraAnophelesJentik.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                      <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
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

                <!-- 10 Habitat Types Multi-select Checkboxes -->
                <div class="p-4 rounded-2xl bg-teal-50/30 dark:bg-teal-950/20 border border-teal-200 dark:border-teal-800/60 space-y-2">
                  <label class="block text-xs font-bold text-teal-900 dark:text-teal-300">Tipe Habitat Perkembangbiakan Disurvei (Pilih yang sesuai):</label>
                  <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                    <label
                      v-for="hab in standardHabitatAnopheles"
                      :key="hab"
                      class="flex items-center gap-2 p-2 rounded-xl border transition cursor-pointer text-xs"
                      :class="formSiveraAnophelesJentik.jenis_habitat.includes(hab) ? 'bg-teal-50 dark:bg-teal-900/40 border-teal-500 font-bold text-teal-800 dark:text-teal-200' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'"
                    >
                      <input
                        type="checkbox"
                        :value="hab"
                        v-model="formSiveraAnophelesJentik.jenis_habitat"
                        class="rounded text-teal-600 focus:ring-teal-500"
                      />
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

                <!-- Environmental Characteristics -->
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

                  <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-teal-200 flex items-center justify-between text-xs">
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

              <!-- 4.7 SIVERA FORM: NYAMUK ANOPHELES DEWASA (MATRIKS 12 JAM 18.00 - 06.00 WIB) -->
              <div v-if="siveraActiveVektor === 'Nyamuk Anopheles'" class="space-y-4">
                <div class="p-3 rounded-2xl bg-purple-50/60 dark:bg-purple-950/30 border border-purple-200/60">
                  <span class="text-xs font-bold text-purple-900 dark:text-purple-300 flex items-center gap-1.5">
                    <Bug class="w-4 h-4 text-purple-600" />
                    <span>Surveilans Nyamuk Anopheles Dewasa (Penangkapan Umpan Orang Malam - Matriks 12 Jam)</span>
                  </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
                    <select v-model="formSiveraAnophelesNyamuk.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                      <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
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

                <!-- 12-Hour Penangkapan Matrix Table -->
                <div class="p-4 rounded-2xl bg-purple-50/40 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-800 space-y-3">
                  <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-purple-900 dark:text-purple-300 flex items-center gap-1.5">
                      <Clock class="w-4 h-4 text-purple-600" />
                      <span>Matriks Penangkapan Nyamuk 12 Jam (18.00 - 06.00 WIB)</span>
                    </h4>
                    <span class="text-xs font-mono font-bold text-slate-500">
                      Total Man-Hours: {{ manHours }} jam-orang
                    </span>
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
                            <input
                              type="number"
                              min="0"
                              v-model.number="formSiveraAnophelesNyamuk.tangkapan_dalam[slot.key]"
                              class="w-16 py-1 text-center font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs"
                            />
                          </td>
                          <td class="py-1 px-3">
                            <input
                              type="number"
                              min="0"
                              v-model.number="formSiveraAnophelesNyamuk.tangkapan_luar[slot.key]"
                              class="w-16 py-1 text-center font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs"
                            />
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

                  <!-- Live Calculation Box: MHD Dalam, MHD Luar, MBR -->
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3 rounded-xl bg-white dark:bg-slate-900 border border-purple-200 text-xs">
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
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border" :class="getRiskBadgeClass(statusAnophelesNyamuk)">
                          {{ statusAnophelesNyamuk }}
                        </span>
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

          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
            <button
              type="button"
              @click="showAddInspeksiModal = false"
              class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-700 transition cursor-pointer"
            >
              Batal
            </button>
            <button
              type="button"
              @click="submitInspeksi()"
              :disabled="isSubmittingInspeksi"
              class="px-5 py-2 text-xs font-bold rounded-xl bg-gradient-to-r from-[#00A99D] to-[#005851] text-white hover:brightness-105 transition shadow-sm flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <Check class="w-4 h-4" />
              <span>{{ isSubmittingInspeksi ? 'Menyimpan...' : 'Simpan Data' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- MODAL TAMBAH SARANA BARU (TPP / SAB / HSBU)                    -->
      <!-- ============================================================== -->
      <div
        v-if="showAddSaranaModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
      >
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-[#00A99D] to-[#005851] text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Plus class="w-5 h-5" />
              <h3 class="text-sm font-bold">Pendaftaran Sarana Sanitasi Baru</h3>
            </div>
            <button
              type="button"
              @click="showAddSaranaModal = false"
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
              :class="addSaranaType === 'tpp' ? 'bg-white dark:bg-slate-900 text-[#005851] shadow-xs' : 'text-slate-500'"
            >
              TPP (Pangan)
            </button>
            <button
              type="button"
              @click="addSaranaType = 'sab'"
              class="flex-1 py-1.5 text-xs font-bold rounded-lg transition text-center cursor-pointer"
              :class="addSaranaType === 'sab' ? 'bg-white dark:bg-slate-900 text-[#005851] shadow-xs' : 'text-slate-500'"
            >
              SAB (Air)
            </button>
            <button
              type="button"
              @click="addSaranaType = 'hsbu'"
              class="flex-1 py-1.5 text-xs font-bold rounded-lg transition text-center cursor-pointer"
              :class="addSaranaType === 'hsbu' ? 'bg-white dark:bg-slate-900 text-[#005851] shadow-xs' : 'text-slate-500'"
            >
              HSBU (Gedung)
            </button>
          </div>

          <div class="p-6 space-y-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
              <select v-model="saranaForm.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
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
                  <option value=">100 Pekerja">>100 Pekerja (Besar)</option>
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
            <button type="button" @click="showAddSaranaModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-700 transition cursor-pointer">
              Batal
            </button>
            <button type="button" @click="submitSarana()" :disabled="isSubmittingSarana" class="px-5 py-2 text-xs font-bold rounded-xl bg-gradient-to-r from-[#00A99D] to-[#005851] text-white hover:brightness-105 transition shadow-sm flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
              <Check class="w-4 h-4" />
              <span>{{ isSubmittingSarana ? 'Menyimpan...' : 'Simpan Sarana' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- MODAL TINDAKAN PENGENDALIAN VEKTOR (SIMPLY CI4 PARITY)          -->
      <!-- ============================================================== -->
      <div
        v-if="showPengendalianModal && pengendalianItem"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
      >
        <div class="relative w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden my-8">
          <div class="px-6 py-4 bg-gradient-to-r from-teal-600 to-[#005851] text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
              <ShieldCheck class="w-5 h-5" />
              <div>
                <h3 class="text-sm font-bold">Catat Tindakan Pengendalian Vektor</h3>
                <p class="text-[11px] text-teal-100">{{ pengendalianItem.jenis_vektor }} &bull; {{ pengendalianItem.wilker }} &bull; {{ pengendalianItem.lokasi || '-' }}</p>
              </div>
            </div>
            <button
              type="button"
              @click="showPengendalianModal = false"
              class="p-1 rounded-lg hover:bg-white/20 transition cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <div class="p-6 space-y-4 text-xs max-h-[75vh] overflow-y-auto">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Tindakan</label>
                <input
                  type="date"
                  v-model="pengendalianForm.tanggal"
                  class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Hasil Surveilans</label>
                <div class="px-3 py-2 rounded-xl border font-bold text-xs" :class="getRiskBadgeClass(pengendalianItem.baku_mutu_status)">
                  {{ pengendalianItem.baku_mutu_status }} ({{ pengendalianItem.parameter_utama }})
                </div>
              </div>
            </div>

            <!-- 1. JENTIK AEDES & NYAMUK ANOPHELES -->
            <div v-if="(pengendalianItem.jenis_vektor || '').includes('Jentik') || (pengendalianItem.jenis_vektor || '').includes('Nyamuk')" class="space-y-3">
              <div class="p-3.5 rounded-2xl bg-teal-50/50 dark:bg-teal-950/20 border border-teal-200 dark:border-teal-800 space-y-3">
                <h4 class="text-xs font-bold text-teal-900 dark:text-teal-200 flex items-center gap-1.5">
                  <Bug class="w-4 h-4 text-teal-600" />
                  <span>Pengendalian Jentik / Nyamuk (Sesuai Permenkes 50/2017)</span>
                </h4>

                <div class="space-y-2">
                  <!-- Kimia / Larvasidasi -->
                  <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 dark:text-slate-200">
                      <input type="checkbox" v-model="pengendalianForm.jentik_kimia" class="rounded text-teal-600" />
                      <span>Larvasidasi Kimia (Abatisasi / Temephos)</span>
                    </label>
                    <div v-if="pengendalianForm.jentik_kimia" class="grid grid-cols-2 gap-2 pl-6 pt-1">
                      <div>
                        <label class="block text-[10px] text-slate-400">Jumlah Gram</label>
                        <input type="number" v-model="pengendalianForm.jentik_kimia_gram" placeholder="Gram" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
                      </div>
                      <div>
                        <label class="block text-[10px] text-slate-400">Jumlah Sachet</label>
                        <input type="number" v-model="pengendalianForm.jentik_kimia_sachet" placeholder="Sachet" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
                      </div>
                    </div>
                  </div>

                  <!-- Fisik & Biologi -->
                  <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 cursor-pointer font-semibold text-slate-800 dark:text-slate-200">
                      <input type="checkbox" v-model="pengendalianForm.jentik_fisik" class="rounded text-teal-600" />
                      <span>Pengendalian Fisik (3M Plus / Kuras / Tutup)</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 cursor-pointer font-semibold text-slate-800 dark:text-slate-200">
                      <input type="checkbox" v-model="pengendalianForm.jentik_biologi" class="rounded text-teal-600" />
                      <span>Biologi (BTI / Ikan Pemakan Jentik)</span>
                    </label>
                  </div>

                  <!-- Fogging / ULV -->
                  <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 dark:text-slate-200">
                      <input type="checkbox" v-model="pengendalianForm.nyamuk_fogging" class="rounded text-teal-600" />
                      <span>Pengasapan / Fogging / ULV Nyamuk Dewasa</span>
                    </label>
                    <div v-if="pengendalianForm.nyamuk_fogging" class="grid grid-cols-3 gap-2 pl-6 pt-1">
                      <div>
                        <label class="block text-[10px] text-slate-400">Dosis Insektisida (ml)</label>
                        <input type="number" v-model="pengendalianForm.nyamuk_fogging_ml" placeholder="ml" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
                      </div>
                      <div>
                        <label class="block text-[10px] text-slate-400">Merk Insektisida</label>
                        <input type="text" v-model="pengendalianForm.nyamuk_fogging_merk" placeholder="Merk" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
                      </div>
                      <div>
                        <label class="block text-[10px] text-slate-400">Golongan</label>
                        <input type="text" v-model="pengendalianForm.nyamuk_fogging_golongan" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- 2. LALAT -->
            <div v-else-if="(pengendalianItem.jenis_vektor || '').includes('Lalat')" class="space-y-3">
              <div class="p-3.5 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800 space-y-3">
                <h4 class="text-xs font-bold text-amber-900 dark:text-amber-200 flex items-center gap-1.5">
                  <Bug class="w-4 h-4 text-amber-600" />
                  <span>Pengendalian Kepadatan Lalat</span>
                </h4>

                <div class="space-y-2">
                  <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 dark:text-slate-200">
                      <input type="checkbox" v-model="pengendalianForm.lalat_spraying" class="rounded text-amber-600" />
                      <span>Residual Spraying Lalat</span>
                    </label>
                    <div v-if="pengendalianForm.lalat_spraying" class="pl-6 pt-1">
                      <label class="block text-[10px] text-slate-400">Dosis Insektisida (ml)</label>
                      <input type="number" v-model="pengendalianForm.lalat_spraying_ml" placeholder="Jumlah ml" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
                    </div>
                  </div>

                  <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 dark:text-slate-200">
                      <input type="checkbox" v-model="pengendalianForm.lalat_granule" class="rounded text-amber-600" />
                      <span>Umpan Granule / Fly Bait</span>
                    </label>
                    <div v-if="pengendalianForm.lalat_granule" class="pl-6 pt-1">
                      <label class="block text-[10px] text-slate-400">Dosis Granule (gram)</label>
                      <input type="number" v-model="pengendalianForm.lalat_granule_gram" placeholder="Jumlah gram" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- 3. KECOA -->
            <div v-else-if="(pengendalianItem.jenis_vektor || '').includes('Kecoa')" class="space-y-3">
              <div class="p-3.5 rounded-2xl bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800 space-y-3">
                <h4 class="text-xs font-bold text-rose-900 dark:text-rose-200 flex items-center gap-1.5">
                  <Bug class="w-4 h-4 text-rose-600" />
                  <span>Pengendalian Kepadatan Kecoa</span>
                </h4>

                <div class="space-y-2">
                  <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 dark:text-slate-200">
                      <input type="checkbox" v-model="pengendalianForm.kecoa_retrapping" class="rounded text-rose-600" />
                      <span>Pemasangan Retrapping Perangkap Kecoa Baru</span>
                    </label>
                    <div v-if="pengendalianForm.kecoa_retrapping" class="pl-6 pt-1">
                      <label class="block text-[10px] text-slate-400">Jumlah Perangkap (Trap)</label>
                      <input type="number" v-model="pengendalianForm.kecoa_retrapping_trap" placeholder="Jumlah trap" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
                    </div>
                  </div>

                  <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 dark:text-slate-200">
                      <input type="checkbox" v-model="pengendalianForm.kecoa_gel_bait" class="rounded text-rose-600" />
                      <span>Aplikasi Gel Baiting Kecoa</span>
                    </label>
                    <div v-if="pengendalianForm.kecoa_gel_bait" class="pl-6 pt-1">
                      <label class="block text-[10px] text-slate-400">Dosis Gel Bait (gram)</label>
                      <input type="number" v-model="pengendalianForm.kecoa_gel_bait_gram" placeholder="Jumlah gram" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Catatan & Rangkuman Pengendalian -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan / Rekomendasi Khusus</label>
              <textarea
                v-model="pengendalianForm.catatan"
                rows="2"
                placeholder="Deskripsi langkah pengendalian yang dilakukan petugas..."
                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"
              ></textarea>
            </div>
          </div>

          <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
            <button
              type="button"
              @click="showPengendalianModal = false"
              class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-700 transition cursor-pointer"
            >
              Batal
            </button>
            <button
              type="button"
              @click="submitPengendalian()"
              :disabled="isSubmittingPengendalian"
              class="px-5 py-2 text-xs font-bold rounded-xl bg-gradient-to-r from-teal-600 to-[#005851] text-white hover:brightness-105 transition shadow-sm flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <Check class="w-4 h-4" />
              <span>{{ isSubmittingPengendalian ? 'Menyimpan...' : 'Simpan Tindakan Pengendalian' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- MODAL DETAIL RINCIAN INSPEKSI / SIVERA                         -->
      <!-- ============================================================== -->
      <div
        v-if="showDetailModal && detailItem"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
      >
        <div class="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-[#005851] to-[#00A99D] text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
              <FileText class="w-5 h-5" />
              <h3 class="text-sm font-bold">Rincian Data {{ detailType === 'sivera' ? 'Surveilans SIVERA' : 'Inspeksi Sanitasi' }}</h3>
            </div>
            <button type="button" @click="closeDetail()" class="p-1 rounded-lg hover:bg-white/20 transition cursor-pointer">
              <X class="w-5 h-5" />
            </button>
          </div>

          <div class="p-6 max-h-[75vh] overflow-y-auto space-y-4 text-xs">
            <div class="grid grid-cols-2 gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
              <div>
                <span class="text-slate-400 block text-[11px]">Nama Objek / Vektor:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                  {{ detailItem.nama_tpp || detailItem.nama_sab || detailItem.nama_gedung || detailItem.jenis_vektor }}
                </span>
              </div>
              <div>
                <span class="text-slate-400 block text-[11px]">Wilayah Kerja:</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ detailItem.wilker }}</span>
              </div>
              <div>
                <span class="text-slate-400 block text-[11px]">Tanggal:</span>
                <span class="font-mono text-slate-700 dark:text-slate-300">{{ formatDate(detailItem.tanggal_inspeksi || detailItem.tanggal) }}</span>
              </div>
              <div>
                <span class="text-slate-400 block text-[11px]">Petugas Pemeriksa / Lokasi:</span>
                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ detailItem.nama_pemeriksa || detailItem.lokasi || '-' }}</span>
              </div>
            </div>

            <!-- Scoring / Status Badge -->
            <div class="p-4 rounded-2xl border flex items-center justify-between" :class="getRiskBadgeClass(detailItem.tingkat_risiko || detailItem.baku_mutu_status)">
              <div>
                <span class="font-bold text-sm">
                  {{ detailItem.parameter_utama || ('Skor Hasil: ' + (detailItem.hasil ?? detailItem.total)) }}
                </span>
                <p class="text-[11px] opacity-80 mt-0.5">Status: {{ detailItem.tingkat_risiko || detailItem.baku_mutu_status }}</p>
              </div>
              <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border bg-white/50 dark:bg-black/20">
                {{ detailItem.tingkat_risiko || detailItem.baku_mutu_status }}
              </span>
            </div>

            <!-- Notes & Detail Data -->
            <div class="space-y-3">
              <div v-if="detailItem.kesimpulan">
                <span class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Kesimpulan:</span>
                <p class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">
                  {{ detailItem.kesimpulan }}
                </p>
              </div>

              <div v-if="detailItem.saran_rencana_tindakan || detailItem.tindakan_pengendalian">
                <span class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Saran &amp; Tindakan Pengendalian:</span>
                <p class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">
                  {{ formatTindakanPengendalian(detailItem.saran_rencana_tindakan || detailItem.tindakan_pengendalian) }}
                </p>
              </div>

              <!-- SIVERA Lalat Multi-Lokasi Detail Display -->
              <div v-if="detailType === 'sivera' && (detailItem.jenis_vektor || '').includes('Lalat') && detailItem.detail_data?.detail_lokasi" class="space-y-3">
                <span class="font-bold text-slate-700 dark:text-slate-300 block">Rincian Titik Survei Fly Grill (10 Pengamatan x 30 Detik):</span>
                <div class="grid grid-cols-1 gap-2.5">
                  <div
                    v-for="(loc, idx) in detailItem.detail_data.detail_lokasi"
                    :key="idx"
                    class="p-3 rounded-xl bg-amber-50/50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60"
                  >
                    <div class="flex items-center justify-between mb-2">
                      <span class="font-bold text-slate-800 dark:text-slate-200 text-xs">{{ idx + 1 }}. {{ loc.lokasi || 'Titik ' + (idx + 1) }}</span>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border" :class="getRiskBadgeClass(loc.status || (loc.rata_rata < 2 ? 'MS' : 'TMS'))">
                        Fly Index: {{ loc.rata_rata }} ({{ loc.status || (loc.rata_rata < 2 ? 'MS' : 'TMS') }})
                      </span>
                    </div>
                    <div class="flex items-center gap-1.5 overflow-x-auto text-[11px] font-mono">
                      <span class="text-slate-400 text-[10px] mr-1">T1-T10:</span>
                      <span v-for="(val, tI) in (loc.t || [])" :key="tI" class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">
                        {{ val }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- SIVERA Nyamuk Anopheles 12-Hour Matrix Detail Display -->
              <div v-if="detailType === 'sivera' && (detailItem.jenis_vektor || '').includes('Nyamuk') && detailItem.detail_data?.tangkapan_dalam" class="space-y-2">
                <span class="font-bold text-slate-700 dark:text-slate-300 block">Matriks Penangkapan Umpan Orang Malam (18.00 - 06.00 WIB):</span>
                <div class="overflow-x-auto rounded-xl border border-purple-200 dark:border-purple-800">
                  <table class="w-full text-center text-xs bg-white dark:bg-slate-900 font-mono">
                    <thead>
                      <tr class="bg-purple-100/70 dark:bg-purple-950/60 font-sans font-bold text-[11px] text-purple-900 dark:text-purple-200">
                        <th class="py-1.5 px-2 text-left">Jam</th>
                        <th class="py-1.5 px-2">Dalam (D)</th>
                        <th class="py-1.5 px-2">Luar (L)</th>
                        <th class="py-1.5 px-2">Total</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-purple-100 dark:divide-purple-900/40">
                      <tr v-for="slot in anophelesJamSlots" :key="slot.key">
                        <td class="py-1 px-2 text-left font-sans text-slate-600 dark:text-slate-300">{{ slot.label }}</td>
                        <td class="py-1 px-2">{{ detailItem.detail_data.tangkapan_dalam[slot.key] || 0 }}</td>
                        <td class="py-1 px-2">{{ detailItem.detail_data.tangkapan_luar[slot.key] || 0 }}</td>
                        <td class="py-1 px-2 font-bold">{{ Number(detailItem.detail_data.tangkapan_dalam[slot.key] || 0) + Number(detailItem.detail_data.tangkapan_luar[slot.key] || 0) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- SIVERA Detail Parameters JSON if any -->
              <div v-if="detailItem.detail_data" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                <span class="font-bold text-slate-700 dark:text-slate-300 block mb-2">Rincian Variabel Teknis Survei:</span>
                <div class="grid grid-cols-2 gap-2 text-[11px] font-mono">
                  <div v-for="(v, k) in detailItem.detail_data" :key="k" class="bg-white dark:bg-slate-900 p-2 rounded-lg border border-slate-100 dark:border-slate-800">
                    <span class="text-slate-400 block">{{ k }}:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ typeof v === 'object' ? JSON.stringify(v) : v }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <a
              v-if="detailType !== 'sivera'"
              :href="`/prl/inspeksi/${detailType}/${detailItem.id}/pdf`"
              target="_blank"
              class="px-4 py-2 text-xs font-bold rounded-xl bg-[#005851] text-white hover:bg-[#004842] transition shadow-xs flex items-center gap-2 cursor-pointer"
            >
              <Printer class="w-4 h-4" />
              <span>Cetak PDF Lembar Pengawasan</span>
            </a>
            <div v-else class="flex items-center gap-2">
              <a
                :href="`/prl/sivera/${detailItem.id}/pdf`"
                target="_blank"
                class="px-4 py-2 text-xs font-bold rounded-xl bg-rose-600 text-white hover:bg-rose-700 transition shadow-xs flex items-center gap-2 cursor-pointer"
              >
                <Printer class="w-4 h-4" />
                <span>Cetak Laporan PDF SIVERA</span>
              </a>
              <a
                v-if="(detailItem.jenis_vektor || '').includes('Jentik Aedes')"
                :href="`/prl/sivera/${detailItem.id}/pdf/fogging`"
                target="_blank"
                class="px-3.5 py-2 text-xs font-bold rounded-xl bg-orange-600 text-white hover:bg-orange-700 transition shadow-xs flex items-center gap-1.5 cursor-pointer"
              >
                <Printer class="w-4 h-4" />
                <span>PDF Form Fogging</span>
              </a>
            </div>

            <button type="button" @click="closeDetail()" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-700 transition cursor-pointer">
              Tutup
            </button>
          </div>
        </div>
      </div>

      <!-- ==================== MODAL SINKRONISASI / MIGRASI DATA SIMPLY ==================== -->
      <div
        v-if="showSyncModal"
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
      >
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl max-w-2xl w-full border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col my-8 animate-in fade-in zoom-in-95 duration-150">
          <!-- Modal Header -->
          <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800 flex items-start justify-between gap-4 bg-slate-50/50 dark:bg-slate-800/40">
            <div class="flex items-start gap-3">
              <div class="w-11 h-11 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shrink-0 shadow-md shadow-indigo-500/20">
                <Database class="w-5 h-5" />
              </div>
              <div>
                <div class="flex items-center gap-2 flex-wrap">
                  <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white">
                    Konfigurasi Database Sumber Sebelum Sinkronisasi
                  </h3>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800">
                    Pra-Sinkronisasi
                  </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                  Pastikan kredensial MySQL, host, port, dan basis data sumber sudah sesuai sebelum proses migrasi dijalankan.
                </p>
              </div>
            </div>
            <button
              type="button"
              @click="closeSyncModal"
              class="p-1.5 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-5 sm:p-6 space-y-5 max-h-[75vh] overflow-y-auto text-xs">
            
            <!-- 1. Kredensial MySQL Global -->
            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 space-y-3">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 font-bold text-slate-800 dark:text-slate-200 text-xs">
                  <Server class="w-4 h-4 text-indigo-600" />
                  <span>Kredensial Server MySQL Sumber (Global)</span>
                </div>
                <span class="text-[10px] text-slate-400">Koneksi default ke database sumber</span>
              </div>

              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                  <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Host MySQL</label>
                  <input
                    v-model="syncForm.host"
                    type="text"
                    placeholder="127.0.0.1"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-mono"
                  />
                </div>
                <div>
                  <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Port</label>
                  <input
                    v-model.number="syncForm.port"
                    type="number"
                    placeholder="3306"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-mono"
                  />
                </div>
                <div>
                  <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Username</label>
                  <input
                    v-model="syncForm.username"
                    type="text"
                    placeholder="root"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-mono"
                  />
                </div>
                <div>
                  <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Password</label>
                  <div class="relative">
                    <input
                      v-model="syncForm.password"
                      :type="showSyncPassword ? 'text' : 'password'"
                      placeholder="•••••"
                      class="w-full pl-3 pr-8 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-mono"
                    />
                    <button
                      type="button"
                      @click="showSyncPassword = !showSyncPassword"
                      class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 transition cursor-pointer"
                    >
                      <Eye v-if="!showSyncPassword" class="w-3.5 h-3.5" />
                      <EyeOff v-else class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- 2. Pemetaan Basis Data Sumber -->
            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 space-y-2">
              <div class="flex items-center gap-2 font-bold text-slate-800 dark:text-slate-200 text-xs">
                <Database class="w-4 h-4 text-emerald-600" />
                <span>Pemetaan Basis Data Sumber (Simply &amp; COP)</span>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                <div>
                  <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Basis Data Simply (Aplikasi Utama)</label>
                  <input
                    v-model="syncForm.database"
                    type="text"
                    placeholder="bkkr3927_simply"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-mono font-bold text-emerald-700 dark:text-emerald-400"
                  />
                  <span class="text-[10px] text-slate-400 mt-1 block">Digunakan untuk data TPP, SAB, HSBU, dan SIVERA.</span>
                </div>
                <div>
                  <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Pusat Import Database Lain</label>
                  <a
                    href="/database-import"
                    target="_blank"
                    class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium flex items-center justify-between transition cursor-pointer mt-0.5"
                  >
                    <span>Buka Pusat Import 10 Modul</span>
                    <ArrowRight class="w-3.5 h-3.5 text-indigo-600" />
                  </a>
                  <span class="text-[10px] text-slate-400 mt-1 block">Untuk migrasi data pengawasan kapal, pesawat, alkes, &amp; user.</span>
                </div>
              </div>
            </div>

            <!-- 3. Modul yang Akan Disinkronkan -->
            <div class="space-y-2.5">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 font-bold text-slate-800 dark:text-slate-200 text-xs">
                  <Layers class="w-4 h-4 text-purple-600" />
                  <span>Pilih Modul yang Akan Disinkronkan:</span>
                </div>
                <button
                  type="button"
                  @click="toggleAllSyncModules"
                  class="text-[11px] font-bold text-indigo-600 hover:underline cursor-pointer"
                >
                  {{ syncForm.modules.length === 4 ? 'Batal Pilih Semua' : 'Pilih Semua' }}
                </button>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <!-- Modul 1: TPP -->
                <label
                  class="p-3 rounded-2xl border transition cursor-pointer flex items-start gap-3 select-none"
                  :class="syncForm.modules.includes('prl_tpp') ? 'bg-indigo-50/50 dark:bg-indigo-950/30 border-indigo-300 dark:border-indigo-800' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700'"
                >
                  <input
                    type="checkbox"
                    value="prl_tpp"
                    v-model="syncForm.modules"
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 mt-0.5"
                  />
                  <div class="flex-1">
                    <div class="flex items-center gap-1.5 font-bold text-slate-800 dark:text-slate-200 text-xs">
                      <Utensils class="w-3.5 h-3.5 text-emerald-600" />
                      <span>Inspeksi Tempat Pengolahan Pangan (TPP)</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono block mt-0.5">bkkr3927_simply.prl_inspeksi_tpp &amp; prl_tpp</span>
                  </div>
                </label>

                <!-- Modul 2: SAB -->
                <label
                  class="p-3 rounded-2xl border transition cursor-pointer flex items-start gap-3 select-none"
                  :class="syncForm.modules.includes('prl_sab') ? 'bg-indigo-50/50 dark:bg-indigo-950/30 border-indigo-300 dark:border-indigo-800' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700'"
                >
                  <input
                    type="checkbox"
                    value="prl_sab"
                    v-model="syncForm.modules"
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 mt-0.5"
                  />
                  <div class="flex-1">
                    <div class="flex items-center gap-1.5 font-bold text-slate-800 dark:text-slate-200 text-xs">
                      <Droplets class="w-3.5 h-3.5 text-blue-600" />
                      <span>Inspeksi Sarana Air Bersih (SAB)</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono block mt-0.5">bkkr3927_simply.prl_inspeksi_sab &amp; prl_sab</span>
                  </div>
                </label>

                <!-- Modul 3: HSBU -->
                <label
                  class="p-3 rounded-2xl border transition cursor-pointer flex items-start gap-3 select-none"
                  :class="syncForm.modules.includes('prl_hsbu') ? 'bg-indigo-50/50 dark:bg-indigo-950/30 border-indigo-300 dark:border-indigo-800' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700'"
                >
                  <input
                    type="checkbox"
                    value="prl_hsbu"
                    v-model="syncForm.modules"
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 mt-0.5"
                  />
                  <div class="flex-1">
                    <div class="flex items-center gap-1.5 font-bold text-slate-800 dark:text-slate-200 text-xs">
                      <Building2 class="w-3.5 h-3.5 text-purple-600" />
                      <span>Higiene Sanitasi Bangunan &amp; Usaha (HSBU)</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono block mt-0.5">bkkr3927_simply.prl_inspeksi_hsbu &amp; prl_hsbu</span>
                  </div>
                </label>

                <!-- Modul 4: SIVERA -->
                <label
                  class="p-3 rounded-2xl border transition cursor-pointer flex items-start gap-3 select-none"
                  :class="syncForm.modules.includes('prl_sivera') ? 'bg-indigo-50/50 dark:bg-indigo-950/30 border-indigo-300 dark:border-indigo-800' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700'"
                >
                  <input
                    type="checkbox"
                    value="prl_sivera"
                    v-model="syncForm.modules"
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 mt-0.5"
                  />
                  <div class="flex-1">
                    <div class="flex items-center gap-1.5 font-bold text-slate-800 dark:text-slate-200 text-xs">
                      <Bug class="w-3.5 h-3.5 text-teal-600" />
                      <span>Surveilans Vektor SIVERA (7 Jenis)</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono block mt-0.5">Jentik, Lalat, Kecoa, Tikus, Pinjal, Anopheles</span>
                  </div>
                </label>
              </div>
            </div>

            <!-- 4. Opsi Penanganan Data -->
            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40 space-y-2.5">
              <span class="font-bold text-slate-800 dark:text-slate-200 text-xs block">Opsi Penanganan Data:</span>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                <label class="flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="syncForm.skip_duplicate"
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4"
                  />
                  <span class="font-medium">Lewati Duplikat (Skip Duplicate)</span>
                </label>

                <label class="flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="syncForm.normalize_ports"
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4"
                  />
                  <span class="font-medium">Normalisasi Format Wilker / Pelabuhan</span>
                </label>

                <label class="flex items-center gap-2 text-rose-600 dark:text-rose-400 cursor-pointer col-span-full font-bold">
                  <input
                    type="checkbox"
                    v-model="syncForm.truncate_first"
                    class="rounded border-rose-300 text-rose-600 focus:ring-rose-500 w-4 h-4"
                  />
                  <span>Kosongkan Tabel Sadaya Sebelum Impor (Truncate)</span>
                  <span class="text-[10px] font-normal text-rose-500">(Gunakan dengan hati-hati)</span>
                </label>
              </div>
            </div>

            <!-- 5. Status Feedback Alert -->
            <div v-if="syncTestResult" class="p-3.5 rounded-2xl border transition text-xs" :class="syncTestResult.success ? 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800'">
              <div class="flex items-center gap-2 font-bold">
                <CheckCircle2 v-if="syncTestResult.success" class="w-4 h-4 text-emerald-600" />
                <AlertTriangle v-else class="w-4 h-4 text-rose-600" />
                <span>{{ syncTestResult.message }}</span>
              </div>
            </div>

            <div v-if="syncResult" class="p-4 rounded-2xl border transition text-xs space-y-2" :class="syncResult.success ? 'bg-indigo-50 text-indigo-900 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-200 dark:border-indigo-800' : 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800'">
              <div class="flex items-center gap-2 font-bold text-sm">
                <CheckCircle2 v-if="syncResult.success" class="w-4 h-4 text-indigo-600" />
                <AlertTriangle v-else class="w-4 h-4 text-rose-600" />
                <span>{{ syncResult.message }}</span>
              </div>
              <div v-if="syncResult.success" class="grid grid-cols-3 gap-2 font-mono text-[11px] pt-1">
                <div class="p-2 rounded-xl bg-white/70 dark:bg-slate-900/60 border border-indigo-100 dark:border-indigo-900">
                  <span class="text-slate-400 block text-[10px]">Total Terproses:</span>
                  <strong class="text-indigo-700 dark:text-indigo-300 text-sm">{{ syncResult.total_processed }}</strong>
                </div>
                <div class="p-2 rounded-xl bg-white/70 dark:bg-slate-900/60 border border-indigo-100 dark:border-indigo-900">
                  <span class="text-slate-400 block text-[10px]">Data Diimpor:</span>
                  <strong class="text-emerald-700 dark:text-emerald-300 text-sm">{{ syncResult.total_imported }}</strong>
                </div>
                <div class="p-2 rounded-xl bg-white/70 dark:bg-slate-900/60 border border-indigo-100 dark:border-indigo-900">
                  <span class="text-slate-400 block text-[10px]">Duplikat Dilewati:</span>
                  <strong class="text-amber-700 dark:text-amber-300 text-sm">{{ syncResult.total_skipped }}</strong>
                </div>
              </div>
            </div>

          </div>

          <!-- Modal Footer -->
          <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
            <button
              type="button"
              @click="handleTestSyncConnection"
              :disabled="isTestingSync"
              class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 transition shadow-xs flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <RotateCcw class="w-3.5 h-3.5 text-slate-500" :class="{ 'animate-spin': isTestingSync }" />
              <span>{{ isTestingSync ? 'Menguji...' : 'Uji Koneksi Database' }}</span>
            </button>

            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="closeSyncModal"
                class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-700 transition cursor-pointer"
              >
                Batal
              </button>

              <button
                type="button"
                @click="handleSaveSyncConfig"
                class="px-3.5 py-2 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-200 transition shadow-xs flex items-center gap-1.5 cursor-pointer"
                title="Simpan konfigurasi ke browser"
              >
                <Save class="w-3.5 h-3.5 text-indigo-600" />
                <span>Simpan Pengaturan</span>
              </button>

              <button
                type="button"
                @click="handleRunSync"
                :disabled="isSyncingData"
                class="px-5 py-2 text-xs font-bold rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:opacity-95 text-white transition shadow-md flex items-center gap-2 cursor-pointer disabled:opacity-50"
              >
                <Play class="w-3.5 h-3.5 text-indigo-200" :class="{ 'animate-spin': isSyncingData }" />
                <span>{{ isSyncingData ? 'Menyinkronkan...' : 'Mulai Sinkronisasi Sekarang' }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>

    </main>
  </HybridNavbar>
</template>
