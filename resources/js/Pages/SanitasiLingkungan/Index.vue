
<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import {
  Leaf,
  Utensils,
  Droplets,
  Building2,
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
  Save,
} from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
  active_tab: {
    type: String,
    default: 'tpp',
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
const currentTab = ref(props.active_tab === 'overview' || !props.active_tab || props.active_tab === 'sivera' ? 'tpp' : props.active_tab);

// Per-page settings from filters
const perPageTpp = ref(props.filters.per_page_tpp || 25);
const perPageSab = ref(props.filters.per_page_sab || 25);
const perPageHsbu = ref(props.filters.per_page_hsbu || 25);

// Filter States
const search = ref(props.filters.search || '');
const selectedWilker = ref(props.filters.wilker || '');
const selectedRisiko = ref(props.filters.tingkat_risiko || '');
const tglMulai = ref(props.filters.tgl_mulai || '');
const tglSelesai = ref(props.filters.tgl_selesai || '');
const selectedTahun = ref(props.filters.tahun || '');
const selectedBulan = ref(props.filters.bulan || '');

const switchTab = (tab) => {
  currentTab.value = tab;
  applyFilter(tab);
};

const applyFilter = (targetTab = currentTab.value) => {
  router.get('/sanitasi-lingkungan', {
    tab: targetTab,
    search: search.value || undefined,
    wilker: selectedWilker.value || undefined,
    tingkat_risiko: selectedRisiko.value || undefined,
    tgl_mulai: tglMulai.value || undefined,
    tgl_selesai: tglSelesai.value || undefined,
    tahun: selectedTahun.value || undefined,
    bulan: selectedBulan.value || undefined,
    per_page_tpp: perPageTpp.value !== 25 ? perPageTpp.value : undefined,
    per_page_sab: perPageSab.value !== 25 ? perPageSab.value : undefined,
    per_page_hsbu: perPageHsbu.value !== 25 ? perPageHsbu.value : undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const resetFilter = () => {
  search.value = '';
  selectedWilker.value = '';
  selectedRisiko.value = '';
  tglMulai.value = '';
  tglSelesai.value = '';
  selectedTahun.value = '';
  selectedBulan.value = '';
  router.get('/sanitasi-lingkungan', { tab: currentTab.value }, { preserveState: true, replace: true });
};

const changePerPage = (type, val) => {
  const p = Number(val);
  const params = {
    tab: currentTab.value,
    search: search.value || undefined,
    wilker: selectedWilker.value || undefined,
    tingkat_risiko: selectedRisiko.value || undefined,
    tgl_mulai: tglMulai.value || undefined,
    tgl_selesai: tglSelesai.value || undefined,
    tahun: selectedTahun.value || undefined,
    bulan: selectedBulan.value || undefined,
  };
  if (type === 'tpp') {
    perPageTpp.value = p;
    params.per_page_tpp = p;
  } else if (type === 'sab') {
    perPageSab.value = p;
    params.per_page_sab = p;
  } else if (type === 'hsbu') {
    perPageHsbu.value = p;
    params.per_page_hsbu = p;
  }
  router.get('/sanitasi-lingkungan', params, {
    preserveState: true,
    replace: true,
  });
};

// Helpers
const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return isNaN(d.getTime()) ? dateStr : d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const getRiskBadgeClass = (risiko) => {
  if (!risiko) return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
  const r = risiko.toLowerCase();
  if (r.includes('rendah') || r.includes('memenuhi syarat') || r === 'ms') {
    return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800';
  } else if (r.includes('sedang')) {
    return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-800';
  } else if (r.includes('tinggi') || r.includes('tidak memenuhi') || r === 'tms') {
    return 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-800';
  }
  return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
};

const formatTindakanPengendalian = (txt) => {
  if (!txt) return '-';
  return txt;
};

// Detail Modal State
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
};

// Modal Tambah Sarana (TPP, SAB, HSBU)
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


// Form Inspeksi Sanitasi (TPP, SAB, HSBU)
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


const openAddInspeksi = (type = null) => {
  addInspeksiType.value = type || currentTab.value || 'tpp';
  const defWilker = props.wilker_options[0] || 'Wilayah Kerja Patimban';
  const defPemeriksa = props.pemeriksa_options[0] || '';

  formTpp.value.wilker = defWilker;
  formTpp.value.nama_pemeriksa = defPemeriksa;
  formSab.value.wilker = defWilker;
  formSab.value.nama_pemeriksa = defPemeriksa;
  formHsbu.value.wilker = defWilker;
  formHsbu.value.nama_pemeriksa = defPemeriksa;

  showAddInspeksiModal.value = true;
};

// Submit Inspeksi Sanitasi (TPP, SAB, HSBU)
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
        sec_b_satu: formTpp.value.sec_b_satu,
        sec_b_dua: formTpp.value.sec_b_dua,
        sec_b_tiga: formTpp.value.sec_b_tiga,
        sec_b_empat: formTpp.value.sec_b_empat,
        sec_b_lima: formTpp.value.sec_b_lima,
        sec_b_enam: formTpp.value.sec_b_enam,
        sec_b_tujuh: formTpp.value.sec_b_tujuh,
        sec_c_satu: formTpp.value.sec_c_satu,
        sec_c_dua: formTpp.value.sec_c_dua,
        sec_c_tiga: formTpp.value.sec_c_tiga,
        sec_c_empat: formTpp.value.sec_c_empat,
        sec_c_lima: formTpp.value.sec_c_lima,
        sec_c_enam: formTpp.value.sec_c_enam,
        sec_c_tujuh: formTpp.value.sec_c_tujuh,
        sec_c_delapan: formTpp.value.sec_c_delapan,
        sec_c_sembilan: formTpp.value.sec_c_sembilan,
        sec_c_sepuluh: formTpp.value.sec_c_sepuluh,
        sec_c_sebelas: formTpp.value.sec_c_sebelas,
        sec_c_duabelas: formTpp.value.sec_c_duabelas,
        sec_c_tigabelas: formTpp.value.sec_c_tigabelas,
        sec_d_satu: formTpp.value.sec_d_satu,
        sec_d_dua: formTpp.value.sec_d_dua,
        sec_d_tiga: formTpp.value.sec_d_tiga,
        sec_d_empat: formTpp.value.sec_d_empat,
        sec_d_lima: formTpp.value.sec_d_lima,
        sec_d_enam: formTpp.value.sec_d_enam,
        sec_d_tujuh: formTpp.value.sec_d_tujuh,
        sec_d_delapan: formTpp.value.sec_d_delapan,
        hasil: totalSkorTpp.value,
        tingkat_risiko: tingkatRisikoTpp.value,
        kesimpulan: formTpp.value.kesimpulan,
        saran_rencana_tindakan: formTpp.value.saran_rencana_tindakan,
      };
      const res = await axios.post('/sanitasi-lingkungan/inspeksi/tpp', payload);
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
        q1: formSab.value.q1,
        q2: formSab.value.q2,
        q3: formSab.value.q3,
        q4: formSab.value.q4,
        q5: formSab.value.q5,
        q6: formSab.value.q6,
        q7: formSab.value.q7,
        q8: formSab.value.q8,
        q9: formSab.value.q9,
        q10: formSab.value.q10,
        q11: formSab.value.q11,
        q12: formSab.value.q12,
        q13: formSab.value.q13,
        q14: formSab.value.q14,
        q15: formSab.value.q15,
        total: totalSkorSab.value,
        tingkat_risiko: tingkatRisikoSab.value,
        saran_rencana_tindakan: formSab.value.saran_rencana_tindakan,
      };
      const res = await axios.post('/sanitasi-lingkungan/inspeksi/sab', payload);
      alert(res.data.message);
    } else if (addInspeksiType.value === 'hsbu') {
      if (!formHsbu.value.nama_gedung) {
        alert('Nama Bangunan/Gedung HSBU wajib diisi atau dipilih!');
        isSubmittingInspeksi.value = false;
        return;
      }
      const payload = {
        wilker: formHsbu.value.wilker,
        tanggal_inspeksi: formHsbu.value.tanggal_inspeksi,
        prl_hsbu_id: formHsbu.value.master_hsbu_id || null,
        nama_gedung: formHsbu.value.nama_gedung,
        penanggung_jawab_hsbu: formHsbu.value.penanggung_jawab_hsbu,
        penanggung_jawab_no_hp_hsbu: formHsbu.value.penanggung_jawab_no_hp_hsbu,
        nama_pemeriksa: formHsbu.value.nama_pemeriksa,
        halaman: formHsbu.value.halaman,
        konstruksi: formHsbu.value.konstruksi,
        air_bersih: formHsbu.value.air_bersih,
        toilet: formHsbu.value.toilet,
        pembuangan_air_limbah: formHsbu.value.pembuangan_air_limbah,
        tempat_sampah: formHsbu.value.tempat_sampah,
        vektor: formHsbu.value.vektor,
        pencahayaan: formHsbu.value.pencahayaan,
        ventilasi: formHsbu.value.ventilasi,
        total: totalSkorHsbu.value,
        tingkat_risiko: tingkatRisikoHsbu.value,
        kesimpulan: formHsbu.value.kesimpulan,
        saran_rencana_tindakan: formHsbu.value.saran_rencana_tindakan,
      };
      const res = await axios.post('/sanitasi-lingkungan/inspeksi/hsbu', payload);
      alert(res.data.message);
    }
    showAddInspeksiModal.value = false;
    router.reload();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan data.');
  } finally {
    isSubmittingInspeksi.value = false;
  }
};

// Delete Sarana / Inspeksi Item
const deleteInspection = async (item, type) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus catatan ${item.nama_tpp || item.nama_sab || item.nama_gedung}?`)) return;
  try {
    const res = await axios.delete(`/sanitasi-lingkungan/inspeksi/${type}/${item.id}`);
    alert(res.data.message || 'Data berhasil dihapus');
    router.reload();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menghapus data.');
  }
};

</script>


<template>
  <Head title="Sanitasi Lingkungan (TPP, SAB, HSBU) &bull; BKK Bandung" />

  <HybridNavbar mode="inertia" currentRoute="sanitasi">
    <main class="w-full px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

      <!-- ==================== HEADER BAR ==================== -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 mb-2">
            <Leaf class="w-3.5 h-3.5 text-emerald-600" />
            <span>Pengawasan Faktor Risiko Kesehatan Lingkungan &bull; BKK Kelas I Bandung</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
            <ClipboardCheck class="w-7 h-7 sm:w-8 sm:h-8 text-emerald-600 shrink-0" />
            <span>Sanitasi Lingkungan</span>
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-3xl leading-relaxed">
            Pusat terpadu pengawasan Tempat Pengelolaan Pangan (TPP), Sarana Air Bersih (SAB), serta Higiene Sanitasi Bangunan dan Usaha (HSBU) di seluruh wilayah kerja.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
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
            :href="`/sanitasi-lingkungan/export/${currentTab}`"
            class="px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-xs font-bold text-slate-700 dark:text-slate-200 transition shadow-xs flex items-center gap-1.5 cursor-pointer"
            title="Export CSV / Excel"
          >
            <Download class="w-3.5 h-3.5 text-slate-500" />
            <span>Export CSV</span>
          </a>
        </div>
      </div>

      <!-- ==================== SUB-TABS NAVIGATION (TPP, SAB, HSBU) ==================== -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200 dark:border-slate-800">
        <button
          type="button"
          @click="switchTab('tpp')"
          class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
          :class="currentTab === 'tpp' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
        >
          <Utensils class="w-3.5 h-3.5" />
          <span>Tempat Pengelolaan Pangan (TPP)</span>
          <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20 font-mono">{{ stats.total_insp_tpp || inspeksi_tpp?.total || 0 }}</span>
        </button>

        <button
          type="button"
          @click="switchTab('sab')"
          class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
          :class="currentTab === 'sab' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
        >
          <Droplets class="w-3.5 h-3.5" />
          <span>Sarana Air Bersih (SAB)</span>
          <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20 font-mono">{{ stats.total_insp_sab || inspeksi_sab?.total || 0 }}</span>
        </button>

        <button
          type="button"
          @click="switchTab('hsbu')"
          class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0 cursor-pointer"
          :class="currentTab === 'hsbu' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
        >
          <Building2 class="w-3.5 h-3.5" />
          <span>Higiene Bangunan &amp; Usaha (HSBU)</span>
          <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20 font-mono">{{ stats.total_insp_hsbu || inspeksi_hsbu?.total || 0 }}</span>
        </button>
      </div>

      <!-- ==================== TPP, SAB, HSBU CONTENTS ==================== -->
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

          <!-- FILTER KHUSUS MENU TPP -->
          <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/60 mt-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
              <!-- Search -->
              <div class="lg:col-span-4">
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Cari Sarana TPP / Penanggung Jawab / Pemeriksa</label>
                <div class="relative">
                  <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                  <input
                    type="text"
                    v-model="search"
                    @keyup.enter="applyFilter()"
                    placeholder="Ketik nama TPP, penanggung jawab, pemeriksa..."
                    class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                  />
                </div>
              </div>

              <!-- Wilker -->
              <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Wilayah Kerja</label>
                <select
                  v-model="selectedWilker"
                  @change="applyFilter()"
                  class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                >
                  <option value="">Semua Wilker</option>
                  <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
                </select>
              </div>

              <!-- Tingkat Risiko -->
              <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tingkat Risiko</label>
                <select
                  v-model="selectedRisiko"
                  @change="applyFilter()"
                  class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                >
                  <option value="">Semua Risiko</option>
                  <option value="Rendah">Rendah (Skor &le; 5)</option>
                  <option value="Sedang">Sedang (Skor 6 - 13)</option>
                  <option value="Tinggi">Tinggi (Skor &gt; 13)</option>
                </select>
              </div>

              <!-- Tanggal Mulai -->
              <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Mulai</label>
                <input
                  type="date"
                  v-model="tglMulai"
                  @change="applyFilter()"
                  class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                />
              </div>

              <!-- Tanggal Selesai & Actions -->
              <div class="lg:col-span-2 flex items-end gap-1.5">
                <div class="flex-1">
                  <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Selesai</label>
                  <input
                    type="date"
                    v-model="tglSelesai"
                    @change="applyFilter()"
                    class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                  />
                </div>
                <button
                  type="button"
                  @click="applyFilter()"
                  class="p-2 rounded-xl bg-[#005851] hover:bg-[#004842] text-white transition cursor-pointer shadow-xs"
                  title="Terapkan Filter"
                >
                  <Filter class="w-4 h-4" />
                </button>
                <button
                  type="button"
                  @click="resetFilter()"
                  class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition cursor-pointer"
                  title="Reset Semua Filter"
                >
                  <RotateCcw class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Active filter pills -->
            <div v-if="search || selectedWilker || selectedRisiko || tglMulai || tglSelesai" class="mt-3 pt-2.5 border-t border-slate-200/60 dark:border-slate-700/50 flex items-center gap-2 flex-wrap text-xs">
              <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Filter Aktif:</span>
              <span v-if="search" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 text-[11px]">
                Kata Kunci: "{{ search }}"
                <button type="button" @click="search = ''; applyFilter()" class="hover:text-rose-600 font-bold">&times;</button>
              </span>
              <span v-if="selectedWilker" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-teal-50 text-[#005851] border border-teal-200 text-[11px]">
                Wilker: {{ selectedWilker }}
                <button type="button" @click="selectedWilker = ''; applyFilter()" class="hover:text-rose-600 font-bold">&times;</button>
              </span>
              <span v-if="selectedRisiko" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 text-[11px]">
                Risiko: {{ selectedRisiko }}
                <button type="button" @click="selectedRisiko = ''; applyFilter()" class="hover:text-rose-600 font-bold">&times;</button>
              </span>
              <span v-if="tglMulai || tglSelesai" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-[11px]">
                Periode: {{ tglMulai || '...' }} s/d {{ tglSelesai || '...' }}
                <button type="button" @click="tglMulai = ''; tglSelesai = ''; applyFilter()" class="hover:text-rose-600 font-bold">&times;</button>
              </span>
              <button type="button" @click="resetFilter()" class="text-[11px] text-rose-600 hover:underline font-semibold ml-auto cursor-pointer">
                Reset Semua
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

          <!-- Pagination & Per-Page Controls (TPP) -->
          <div
            v-if="inspeksi_tpp?.total > 0"
            class="px-4 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 rounded-b-3xl flex flex-col sm:flex-row items-center justify-between gap-3 text-xs"
          >
            <div class="text-slate-500 dark:text-slate-400 flex items-center gap-2 flex-wrap">
              <span>Menampilkan <strong class="text-slate-700 dark:text-slate-200">{{ inspeksi_tpp.from || 0 }}</strong> - <strong class="text-slate-700 dark:text-slate-200">{{ inspeksi_tpp.to || 0 }}</strong> dari <strong class="text-slate-700 dark:text-slate-200">{{ inspeksi_tpp.total || 0 }}</strong> data</span>
              <span class="text-slate-300 dark:text-slate-600">|</span>
              <div class="flex items-center gap-1.5">
                <span class="text-[11px] text-slate-400">Tampilkan:</span>
                <select
                  :value="perPageTpp"
                  @change="changePerPage('tpp', $event.target.value)"
                  class="py-1 px-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-[#00A99D]"
                >
                  <option :value="10">10 / hal</option>
                  <option :value="25">25 / hal</option>
                  <option :value="50">50 / hal</option>
                  <option :value="100">100 / hal</option>
                  <option :value="1000">Semua</option>
                </select>
              </div>
            </div>
            <div v-if="inspeksi_tpp.links && inspeksi_tpp.links.length > 3" class="flex items-center gap-1 flex-wrap">
              <template v-for="(link, i) in inspeksi_tpp.links" :key="i">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  v-html="link.label"
                  preserve-state
                  class="px-2.5 py-1 rounded-lg text-xs font-medium transition cursor-pointer"
                  :class="link.active ? 'bg-[#00A99D] text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                />
                <span
                  v-else
                  v-html="link.label"
                  class="px-2.5 py-1 text-slate-300 dark:text-slate-600 text-xs select-none"
                />
              </template>
            </div>
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

          <!-- FILTER KHUSUS MENU SAB -->
          <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/60 mt-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
              <!-- Search -->
              <div class="lg:col-span-4">
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Cari Sarana SAB / Penanggung Jawab / Sumber Air / Pemeriksa</label>
                <div class="relative">
                  <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                  <input
                    type="text"
                    v-model="search"
                    @keyup.enter="applyFilter()"
                    placeholder="Ketik nama SAB, sumber air, penanggung jawab, pemeriksa..."
                    class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                  />
                </div>
              </div>

              <!-- Wilker -->
              <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Wilayah Kerja</label>
                <select
                  v-model="selectedWilker"
                  @change="applyFilter()"
                  class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                >
                  <option value="">Semua Wilker</option>
                  <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
                </select>
              </div>

              <!-- Tingkat Risiko -->
              <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tingkat Risiko</label>
                <select
                  v-model="selectedRisiko"
                  @change="applyFilter()"
                  class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                >
                  <option value="">Semua Risiko</option>
                  <option value="Rendah">Rendah (Skor &le; 2)</option>
                  <option value="Sedang">Sedang (Skor 3 - 5)</option>
                  <option value="Tinggi">Tinggi (Skor &gt; 5)</option>
                </select>
              </div>

              <!-- Tanggal Mulai -->
              <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Mulai</label>
                <input
                  type="date"
                  v-model="tglMulai"
                  @change="applyFilter()"
                  class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                />
              </div>

              <!-- Tanggal Selesai & Actions -->
              <div class="lg:col-span-2 flex items-end gap-1.5">
                <div class="flex-1">
                  <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Selesai</label>
                  <input
                    type="date"
                    v-model="tglSelesai"
                    @change="applyFilter()"
                    class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                  />
                </div>
                <button
                  type="button"
                  @click="applyFilter()"
                  class="p-2 rounded-xl bg-[#005851] hover:bg-[#004842] text-white transition cursor-pointer shadow-xs"
                  title="Terapkan Filter"
                >
                  <Filter class="w-4 h-4" />
                </button>
                <button
                  type="button"
                  @click="resetFilter()"
                  class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition cursor-pointer"
                  title="Reset Semua Filter"
                >
                  <RotateCcw class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Active filter pills -->
            <div v-if="search || selectedWilker || selectedRisiko || tglMulai || tglSelesai" class="mt-3 pt-2.5 border-t border-slate-200/60 dark:border-slate-700/50 flex items-center gap-2 flex-wrap text-xs">
              <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Filter Aktif:</span>
              <span v-if="search" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-cyan-50 text-cyan-800 border border-cyan-200 text-[11px]">
                Kata Kunci: "{{ search }}"
                <button type="button" @click="search = ''; applyFilter()" class="hover:text-rose-600 font-bold">&times;</button>
              </span>
              <span v-if="selectedWilker" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-teal-50 text-[#005851] border border-teal-200 text-[11px]">
                Wilker: {{ selectedWilker }}
                <button type="button" @click="selectedWilker = ''; applyFilter()" class="hover:text-rose-600 font-bold">&times;</button>
              </span>
              <span v-if="selectedRisiko" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 text-[11px]">
                Risiko: {{ selectedRisiko }}
                <button type="button" @click="selectedRisiko = ''; applyFilter()" class="hover:text-rose-600 font-bold">&times;</button>
              </span>
              <span v-if="tglMulai || tglSelesai" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-[11px]">
                Periode: {{ tglMulai || '...' }} s/d {{ tglSelesai || '...' }}
                <button type="button" @click="tglMulai = ''; tglSelesai = ''; applyFilter()" class="hover:text-rose-600 font-bold">&times;</button>
              </span>
              <button type="button" @click="resetFilter()" class="text-[11px] text-rose-600 hover:underline font-semibold ml-auto cursor-pointer">
                Reset Semua
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

          <!-- Pagination & Per-Page Controls (SAB) -->
          <div
            v-if="inspeksi_sab?.total > 0"
            class="px-4 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 rounded-b-3xl flex flex-col sm:flex-row items-center justify-between gap-3 text-xs"
          >
            <div class="text-slate-500 dark:text-slate-400 flex items-center gap-2 flex-wrap">
              <span>Menampilkan <strong class="text-slate-700 dark:text-slate-200">{{ inspeksi_sab.from || 0 }}</strong> - <strong class="text-slate-700 dark:text-slate-200">{{ inspeksi_sab.to || 0 }}</strong> dari <strong class="text-slate-700 dark:text-slate-200">{{ inspeksi_sab.total || 0 }}</strong> data</span>
              <span class="text-slate-300 dark:text-slate-600">|</span>
              <div class="flex items-center gap-1.5">
                <span class="text-[11px] text-slate-400">Tampilkan:</span>
                <select
                  :value="perPageSab"
                  @change="changePerPage('sab', $event.target.value)"
                  class="py-1 px-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-[#00A99D]"
                >
                  <option :value="10">10 / hal</option>
                  <option :value="25">25 / hal</option>
                  <option :value="50">50 / hal</option>
                  <option :value="100">100 / hal</option>
                  <option :value="1000">Semua</option>
                </select>
              </div>
            </div>
            <div v-if="inspeksi_sab.links && inspeksi_sab.links.length > 3" class="flex items-center gap-1 flex-wrap">
              <template v-for="(link, i) in inspeksi_sab.links" :key="i">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  v-html="link.label"
                  preserve-state
                  class="px-2.5 py-1 rounded-lg text-xs font-medium transition cursor-pointer"
                  :class="link.active ? 'bg-[#00A99D] text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                />
                <span
                  v-else
                  v-html="link.label"
                  class="px-2.5 py-1 text-slate-300 dark:text-slate-600 text-xs select-none"
                />
              </template>
            </div>
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

          <!-- FILTER KHUSUS MENU HSBU -->
          <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/60 mt-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
              <!-- Search -->
              <div class="lg:col-span-4">
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Cari Gedung HSBU / Usaha / Penanggung Jawab / Pemeriksa</label>
                <div class="relative">
                  <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                  <input
                    type="text"
                    v-model="search"
                    @keyup.enter="applyFilter()"
                    placeholder="Ketik nama gedung, jenis usaha, penanggung jawab, pemeriksa..."
                    class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                  />
                </div>
              </div>

              <!-- Wilker -->
              <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Wilayah Kerja</label>
                <select
                  v-model="selectedWilker"
                  @change="applyFilter()"
                  class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                >
                  <option value="">Semua Wilker</option>
                  <option v-for="w in wilker_options" :key="w" :value="w">{{ w }}</option>
                </select>
              </div>

              <!-- Tingkat Risiko -->
              <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tingkat Risiko</label>
                <select
                  v-model="selectedRisiko"
                  @change="applyFilter()"
                  class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                >
                  <option value="">Semua Risiko</option>
                  <option value="Rendah">Rendah / Sehat (Skor &ge; 70)</option>
                  <option value="Sedang">Sedang / Cukup (Skor 60 - 69)</option>
                  <option value="Tinggi">Tinggi / Kurang (Skor &lt; 60)</option>
                </select>
              </div>

              <!-- Tanggal Mulai -->
              <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Mulai</label>
                <input
                  type="date"
                  v-model="tglMulai"
                  @change="applyFilter()"
                  class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                />
              </div>

              <!-- Tanggal Selesai & Actions -->
              <div class="lg:col-span-2 flex items-end gap-1.5">
                <div class="flex-1">
                  <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Selesai</label>
                  <input
                    type="date"
                    v-model="tglSelesai"
                    @change="applyFilter()"
                    class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                  />
                </div>
                <button
                  type="button"
                  @click="applyFilter()"
                  class="p-2 rounded-xl bg-[#005851] hover:bg-[#004842] text-white transition cursor-pointer shadow-xs"
                  title="Terapkan Filter"
                >
                  <Filter class="w-4 h-4" />
                </button>
                <button
                  type="button"
                  @click="resetFilter()"
                  class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition cursor-pointer"
                  title="Reset Semua Filter"
                >
                  <RotateCcw class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Active filter pills -->
            <div v-if="search || selectedWilker || selectedRisiko || tglMulai || tglSelesai" class="mt-3 pt-2.5 border-t border-slate-200/60 dark:border-slate-700/50 flex items-center gap-2 flex-wrap text-xs">
              <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Filter Aktif:</span>
              <span v-if="search" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-800 border border-purple-200 text-[11px]">
                Kata Kunci: "{{ search }}"
                <button type="button" @click="search = ''; applyFilter()" class="hover:text-rose-600 font-bold">&times;</button>
              </span>
              <span v-if="selectedWilker" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-teal-50 text-[#005851] border border-teal-200 text-[11px]">
                Wilker: {{ selectedWilker }}
                <button type="button" @click="selectedWilker = ''; applyFilter()" class="hover:text-rose-600 font-bold">&times;</button>
              </span>
              <span v-if="selectedRisiko" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 text-[11px]">
                Risiko: {{ selectedRisiko }}
                <button type="button" @click="selectedRisiko = ''; applyFilter()" class="hover:text-rose-600 font-bold">&times;</button>
              </span>
              <span v-if="tglMulai || tglSelesai" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-[11px]">
                Periode: {{ tglMulai || '...' }} s/d {{ tglSelesai || '...' }}
                <button type="button" @click="tglMulai = ''; tglSelesai = ''; applyFilter()" class="hover:text-rose-600 font-bold">&times;</button>
              </span>
              <button type="button" @click="resetFilter()" class="text-[11px] text-rose-600 hover:underline font-semibold ml-auto cursor-pointer">
                Reset Semua
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

          <!-- Pagination & Per-Page Controls (HSBU) -->
          <div
            v-if="inspeksi_hsbu?.total > 0"
            class="px-4 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 rounded-b-3xl flex flex-col sm:flex-row items-center justify-between gap-3 text-xs"
          >
            <div class="text-slate-500 dark:text-slate-400 flex items-center gap-2 flex-wrap">
              <span>Menampilkan <strong class="text-slate-700 dark:text-slate-200">{{ inspeksi_hsbu.from || 0 }}</strong> - <strong class="text-slate-700 dark:text-slate-200">{{ inspeksi_hsbu.to || 0 }}</strong> dari <strong class="text-slate-700 dark:text-slate-200">{{ inspeksi_hsbu.total || 0 }}</strong> data</span>
              <span class="text-slate-300 dark:text-slate-600">|</span>
              <div class="flex items-center gap-1.5">
                <span class="text-[11px] text-slate-400">Tampilkan:</span>
                <select
                  :value="perPageHsbu"
                  @change="changePerPage('hsbu', $event.target.value)"
                  class="py-1 px-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-[#00A99D]"
                >
                  <option :value="10">10 / hal</option>
                  <option :value="25">25 / hal</option>
                  <option :value="50">50 / hal</option>
                  <option :value="100">100 / hal</option>
                  <option :value="1000">Semua</option>
                </select>
              </div>
            </div>
            <div v-if="inspeksi_hsbu.links && inspeksi_hsbu.links.length > 3" class="flex items-center gap-1 flex-wrap">
              <template v-for="(link, i) in inspeksi_hsbu.links" :key="i">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  v-html="link.label"
                  preserve-state
                  class="px-2.5 py-1 rounded-lg text-xs font-medium transition cursor-pointer"
                  :class="link.active ? 'bg-[#00A99D] text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                />
                <span
                  v-else
                  v-html="link.label"
                  class="px-2.5 py-1 text-slate-300 dark:text-slate-600 text-xs select-none"
                />
              </template>
            </div>
          </div>
        </div>
      </div>


      <!-- ============================================================== -->
      <!-- MODAL INPUT INSPEKSI SANITASI (TPP / SAB / HSBU)               -->
      <!-- ============================================================== -->
      <div
        v-if="showAddInspeksiModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
      >
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-4xl max-h-[92vh] overflow-hidden flex flex-col shadow-2xl animate-in fade-in zoom-in-95 duration-200">
          <!-- Modal Header -->
          <div class="px-6 py-4 bg-gradient-to-r from-[#005851] to-[#00A99D] text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
              <ClipboardCheck class="w-5 h-5 text-white" />
              <h3 class="text-base font-bold tracking-tight">Formulir Pencatatan Inspeksi Sanitasi</h3>
            </div>
            <button
              type="button"
              @click="showAddInspeksiModal = false"
              class="p-1 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <!-- Module Selector Tabs inside Modal (TPP, SAB, HSBU) -->
          <div class="px-6 py-3 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex items-center gap-2 overflow-x-auto text-xs font-semibold">
            <button
              type="button"
              @click="addInspeksiType = 'tpp'"
              class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer"
              :class="addInspeksiType === 'tpp' ? 'bg-white dark:bg-slate-900 text-amber-600 shadow-xs border border-slate-200 dark:border-slate-700' : 'text-slate-500 hover:text-slate-800'"
            >
              <Utensils class="w-3.5 h-3.5" />
              <span>Inspeksi TPP (Pangan)</span>
            </button>
            <button
              type="button"
              @click="addInspeksiType = 'sab'"
              class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer"
              :class="addInspeksiType === 'sab' ? 'bg-white dark:bg-slate-900 text-cyan-600 shadow-xs border border-slate-200 dark:border-slate-700' : 'text-slate-500 hover:text-slate-800'"
            >
              <Droplets class="w-3.5 h-3.5" />
              <span>Inspeksi SAB (Air Bersih)</span>
            </button>
            <button
              type="button"
              @click="addInspeksiType = 'hsbu'"
              class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer"
              :class="addInspeksiType === 'hsbu' ? 'bg-white dark:bg-slate-900 text-[#00A99D] shadow-xs border border-slate-200 dark:border-slate-700' : 'text-slate-500 hover:text-slate-800'"
            >
              <Building2 class="w-3.5 h-3.5" />
              <span>Inspeksi HSBU (Bangunan)</span>
            </button>
          </div>

          <!-- Modal Body Form -->
          <div class="p-6 max-h-[75vh] overflow-y-auto space-y-6">
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

          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
            <button
              type="button"
              @click="showAddInspeksiModal = false"
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
              <span>{{ isSubmittingInspeksi ? 'Menyimpan...' : 'Simpan Data Inspeksi' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- MODAL TAMBAH SARANA BARU (TPP / SAB / HSBU)                    -->
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
      <!-- MODAL DETAIL RINCIAN INSPEKSI                                  -->
      <!-- ============================================================== -->
      <div
        v-if="showDetailModal && detailItem"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
      >
        <div class="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
          <!-- Modal Header -->
          <div class="px-6 py-4 bg-gradient-to-r from-[#005851] to-[#00A99D] text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
              <FileText class="w-5 h-5" />
              <h3 class="text-sm font-bold">Rincian Data Inspeksi Sanitasi</h3>
            </div>
            <button type="button" @click="closeDetail()" class="p-1 rounded-lg hover:bg-white/20 transition cursor-pointer">
              <X class="w-5 h-5" />
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6 max-h-[75vh] overflow-y-auto space-y-4 text-xs">
            <div class="grid grid-cols-2 gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
              <div>
                <span class="text-slate-400 block text-[11px]">Nama Sarana:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                  {{ detailItem.nama_tpp || detailItem.nama_sab || detailItem.nama_gedung }}
                </span>
              </div>
              <div>
                <span class="text-slate-400 block text-[11px]">Wilayah Kerja:</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ detailItem.wilker }}</span>
              </div>
              <div>
                <span class="text-slate-400 block text-[11px]">Tanggal Inspeksi:</span>
                <span class="font-mono text-slate-700 dark:text-slate-300">{{ formatDate(detailItem.tanggal_inspeksi) }}</span>
              </div>
              <div>
                <span class="text-slate-400 block text-[11px]">Petugas Pemeriksa:</span>
                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ detailItem.nama_pemeriksa || '-' }}</span>
              </div>
            </div>

            <!-- Scoring / Status Badge -->
            <div class="p-4 rounded-2xl border flex items-center justify-between" :class="getRiskBadgeClass(detailItem.tingkat_risiko)">
              <div>
                <span class="font-bold text-sm">
                  Skor Hasil: {{ detailItem.hasil ?? detailItem.total }}
                </span>
                <p class="text-[11px] opacity-80 mt-0.5">Tingkat Risiko: {{ detailItem.tingkat_risiko }}</p>
              </div>
              <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border bg-white/50 dark:bg-black/20">
                {{ detailItem.tingkat_risiko }}
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

              <div v-if="detailItem.saran_rencana_tindakan">
                <span class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Saran / Rencana Tindakan:</span>
                <p class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">
                  {{ formatTindakanPengendalian(detailItem.saran_rencana_tindakan) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <a
              :href="`/sanitasi-lingkungan/inspeksi/${detailType}/${detailItem.id}/pdf`"
              target="_blank"
              class="px-4 py-2 text-xs font-bold rounded-xl bg-[#005851] text-white hover:bg-[#004842] transition shadow-xs flex items-center gap-2 cursor-pointer"
            >
              <Printer class="w-4 h-4" />
              <span>Cetak PDF Lembar Pengawasan</span>
            </a>
            <button
              type="button"
              @click="closeDetail()"
              class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-600 transition cursor-pointer"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>

    </main>
  </HybridNavbar>
</template>
