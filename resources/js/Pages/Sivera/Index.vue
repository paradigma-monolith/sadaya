
<script setup>
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import {
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
  BarChart3,
  Table,
  Rat,
  Save,
} from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
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
  sivera_dashboard_metrics: {
    type: Object,
    default: () => ({}),
  },
  sivera_dashboard_data: {
    type: Object,
    default: () => ({
      labels: [],
      summary: {},
      charts: {
        kecoa: {},
        lalat_status: { MS: [], TMS: [] },
        jentik_kontainer: [],
        spesies_tikus: {},
      },
      wilker_detail: [],
    }),
  },
  sivera_list: {
    type: Object,
    default: () => ({ data: [] }),
  },
});

const currentTab = ref('sivera');
const siveraFilterVektor = ref(props.filters.sivera_vektor || 'Dashboard');
const perPageSivera = ref(props.filters.per_page_sivera || 25);

// Filter States
const search = ref(props.filters.search || '');
const selectedWilker = ref(props.filters.wilker || '');
const selectedRisiko = ref(props.filters.tingkat_risiko || '');
const tglMulai = ref(props.filters.tgl_mulai || '');
const tglSelesai = ref(props.filters.tgl_selesai || '');
const selectedTahun = ref(props.filters.tahun || '');
const selectedBulan = ref(props.filters.bulan || '');

const switchSiveraVektor = (vTab) => {
  siveraFilterVektor.value = vTab;
  router.get('/sivera', {
    sivera_vektor: vTab !== 'Dashboard' ? vTab : undefined,
    search: search.value || undefined,
    wilker: selectedWilker.value || undefined,
    tingkat_risiko: selectedRisiko.value || undefined,
    tgl_mulai: tglMulai.value || undefined,
    tgl_selesai: tglSelesai.value || undefined,
    tahun: selectedTahun.value || undefined,
    bulan: selectedBulan.value || undefined,
    per_page_sivera: perPageSivera.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const applyFilter = () => {
  router.get('/sivera', {
    sivera_vektor: siveraFilterVektor.value !== 'Dashboard' ? siveraFilterVektor.value : undefined,
    search: search.value || undefined,
    wilker: selectedWilker.value || undefined,
    tingkat_risiko: selectedRisiko.value || undefined,
    tgl_mulai: tglMulai.value || undefined,
    tgl_selesai: tglSelesai.value || undefined,
    tahun: selectedTahun.value || undefined,
    bulan: selectedBulan.value || undefined,
    per_page_sivera: perPageSivera.value !== 25 ? perPageSivera.value : undefined,
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
  siveraFilterVektor.value = 'Dashboard';
  router.get('/sivera', {}, { preserveState: true, replace: true });
};

const changePerPage = (type, val) => {
  perPageSivera.value = Number(val);
  router.get('/sivera', {
    sivera_vektor: siveraFilterVektor.value !== 'Dashboard' ? siveraFilterVektor.value : undefined,
    search: search.value || undefined,
    wilker: selectedWilker.value || undefined,
    tingkat_risiko: selectedRisiko.value || undefined,
    tgl_mulai: tglMulai.value || undefined,
    tgl_selesai: tglSelesai.value || undefined,
    tahun: selectedTahun.value || undefined,
    bulan: selectedBulan.value || undefined,
    per_page_sivera: perPageSivera.value,
  }, {
    preserveState: true,
    replace: true,
  });
};

// Filtered Sivera List based on vector sub-filter
const filteredSiveraData = computed(() => {
  const data = props.sivera_list?.data || [];
  if (siveraFilterVektor.value === 'Dashboard' || siveraFilterVektor.value === 'Semua Data') return data;
  const filter = siveraFilterVektor.value.toLowerCase();
  return data.filter((item) => {
    const jv = (item.jenis_vektor || '').toLowerCase();
    if (filter.includes('anopheles')) {
      if (filter.includes('jentik')) return jv.includes('anopheles') && jv.includes('jentik');
      if (filter.includes('nyamuk')) return jv.includes('anopheles') && jv.includes('nyamuk');
      return jv.includes('anopheles');
    }
    return jv.includes(filter) || filter.includes(jv);
  });
});

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

// SIVERA Dashboard Metrics and Charts calculations
const siveraDashboardMetrics = computed(() => {
  if (props.sivera_dashboard_metrics && props.sivera_dashboard_metrics.totalSurveilans !== undefined) {
    return props.sivera_dashboard_metrics;
  }
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

// SIVERA Card View Modes ('chart' | 'table')
const siveraCardViews = ref({
  kecoa: 'chart',
  lalat: 'chart',
  jentik: 'chart',
  tikus: 'chart',
});

// SIVERA Master Detail Active Tab ('semua' | 'jentik' | 'lalat' | 'kecoa' | 'tikus' | 'anopheles')
const activeSiveraDetailTab = ref('semua');

// SIVERA Wilker search filter in master detail table
const siveraWilkerSearch = ref('');

// SIVERA Unified Dashboard Data with fallback
const siveraDashData = computed(() => {
  if (props.sivera_dashboard_data && props.sivera_dashboard_data.summary) {
    return props.sivera_dashboard_data;
  }
  return {
    labels: [],
    summary: {
      total_surveilans: 0,
      jentik: 0,
      kontainer_positif: 0,
      abj_avg: 100,
      lalat: 0,
      avg_lalat: 0,
      lalat_ms: 0,
      lalat_tms: 0,
      kecoa: 0,
      kecoa_tertangkap: 0,
      avg_kecoa: 0,
      tikus: 0,
      tikus_tertangkap: 0,
      avg_success_trap: 0,
      pinjal: 0,
      pinjal_umum: 0,
      pinjal_khusus: 0,
      anopheles_jentik: 0,
      anopheles_nyamuk: 0,
      avg_indeks_habitat: 0,
      avg_mbr: 0,
      kepatuhan_pct: 100,
    },
    charts: {
      kecoa: {},
      lalat_status: { MS: [], TMS: [] },
      jentik_kontainer: [],
      spesies_tikus: {},
    },
    wilker_detail: [],
  };
});

// Species colors
const kecoaSpeciesColors = {
  'P. americana': { bar: 'bg-amber-500', fill: '#f59e0b', text: 'text-amber-700 dark:text-amber-400', badge: 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300' },
  'B. germanica': { bar: 'bg-rose-500', fill: '#ef4444', text: 'text-rose-700 dark:text-rose-400', badge: 'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300' },
  'S. longipalpa': { bar: 'bg-purple-500', fill: '#8b5cf6', text: 'text-purple-700 dark:text-purple-400', badge: 'bg-purple-100 text-purple-800 dark:bg-purple-950/40 dark:text-purple-300' },
  'N. rhombifolia': { bar: 'bg-cyan-500', fill: '#06b6d4', text: 'text-cyan-700 dark:text-cyan-400', badge: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950/40 dark:text-cyan-300' },
  'Lain-Lain': { bar: 'bg-slate-400', fill: '#94a3b8', text: 'text-slate-700 dark:text-slate-400', badge: 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300' },
};

const tikusSpeciesColors = {
  'R. norvegicus': { bar: 'bg-blue-500', fill: '#3b82f6', text: 'text-blue-700 dark:text-blue-400', badge: 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300' },
  'R. tanezumi': { bar: 'bg-indigo-500', fill: '#6366f1', text: 'text-indigo-700 dark:text-indigo-400', badge: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/40 dark:text-indigo-300' },
  'M. musculus': { bar: 'bg-amber-500', fill: '#f59e0b', text: 'text-amber-700 dark:text-amber-400', badge: 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300' },
  'S. murinus': { bar: 'bg-emerald-500', fill: '#10b981', text: 'text-emerald-700 dark:text-emerald-400', badge: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300' },
  'Lain-Lain': { bar: 'bg-slate-400', fill: '#94a3b8', text: 'text-slate-700 dark:text-slate-400', badge: 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300' },
};

// Filtered Wilker Detail for Search
const filteredWilkerDetail = computed(() => {
  const list = siveraDashData.value.wilker_detail || [];
  if (!siveraWilkerSearch.value.trim()) return list;
  const q = siveraWilkerSearch.value.toLowerCase().trim();
  return list.filter((item) => (item.wilker || '').toLowerCase().includes(q));
});

// Master Detail Totals
const wilkerDetailTotals = computed(() => {
  const rows = filteredWilkerDetail.value;
  const t = {
    jentik_survei: 0,
    jentik_kontainer_diperiksa: 0,
    jentik_kontainer_positif: 0,
    jentik_rumah_diperiksa: 0,
    jentik_rumah_positif: 0,
    lalat_survei: 0,
    lalat_rata_sum: 0,
    lalat_ms: 0,
    lalat_tms: 0,
    kecoa_survei: 0,
    kecoa_perangkap: 0,
    kecoa_tertangkap: 0,
    tikus_survei: 0,
    tikus_perangkap: 0,
    tikus_tertangkap: 0,
    tikus_trap_sum: 0,
    pinjal_survei: 0,
    pinjal_umum: 0,
    pinjal_khusus: 0,
    anopheles_jentik_survei: 0,
    anopheles_habitat_sum: 0,
    anopheles_nyamuk_survei: 0,
    anopheles_mbr_sum: 0,
  };
  for (const r of rows) {
    t.jentik_survei += (r.jentik_survei || 0);
    t.jentik_kontainer_diperiksa += (r.jentik_kontainer_diperiksa || 0);
    t.jentik_kontainer_positif += (r.jentik_kontainer_positif || 0);
    t.jentik_rumah_diperiksa += (r.jentik_rumah_diperiksa || 0);
    t.jentik_rumah_positif += (r.jentik_rumah_positif || 0);
    t.lalat_survei += (r.lalat_survei || 0);
    t.lalat_rata_sum += (r.lalat_rata_sum || 0);
    t.lalat_ms += (r.lalat_ms || 0);
    t.lalat_tms += (r.lalat_tms || 0);
    t.kecoa_survei += (r.kecoa_survei || 0);
    t.kecoa_perangkap += (r.kecoa_perangkap || 0);
    t.kecoa_tertangkap += (r.kecoa_tertangkap || 0);
    t.tikus_survei += (r.tikus_survei || 0);
    t.tikus_perangkap += (r.tikus_perangkap || 0);
    t.tikus_tertangkap += (r.tikus_tertangkap || 0);
    t.tikus_trap_sum += (r.tikus_success_trap_sum || 0);
    t.pinjal_survei += (r.pinjal_survei || 0);
    t.pinjal_umum += (r.pinjal_umum || 0);
    t.pinjal_khusus += (r.pinjal_khusus || 0);
    t.anopheles_jentik_survei += (r.anopheles_jentik_survei || 0);
    t.anopheles_habitat_sum += (r.anopheles_habitat_sum || 0);
    t.anopheles_nyamuk_survei += (r.anopheles_nyamuk_survei || 0);
    t.anopheles_mbr_sum += (r.anopheles_mbr_sum || 0);
  }
  return {
    ...t,
    abj_avg: t.jentik_rumah_diperiksa > 0
      ? (((t.jentik_rumah_diperiksa - t.jentik_rumah_positif) / t.jentik_rumah_diperiksa) * 100).toFixed(1)
      : (t.jentik_kontainer_diperiksa > 0 ? (((t.jentik_kontainer_diperiksa - t.jentik_kontainer_positif) / t.jentik_kontainer_diperiksa) * 100).toFixed(1) : '100.0'),
    lalat_avg: t.lalat_survei > 0 ? (t.lalat_rata_sum / t.lalat_survei).toFixed(2) : '0.00',
    kecoa_kepadatan: t.kecoa_perangkap > 0 ? (t.kecoa_tertangkap / t.kecoa_perangkap).toFixed(2) : '0.00',
    tikus_trap_avg: t.tikus_survei > 0 ? (t.tikus_trap_sum / t.tikus_survei).toFixed(2) : '0.00',
    anopheles_habitat_avg: t.anopheles_jentik_survei > 0 ? (t.anopheles_habitat_sum / t.anopheles_jentik_survei).toFixed(1) : '0.0',
    anopheles_mbr_avg: t.anopheles_nyamuk_survei > 0 ? (t.anopheles_mbr_sum / t.anopheles_nyamuk_survei).toFixed(2) : '0.00',
  };
});

// Chart aggregators
const kecoaChartAgg = computed(() => {
  const chart = siveraDashData.value.charts?.kecoa || {};
  const labels = siveraDashData.value.labels || [];
  const species = Object.keys(chart);
  const speciesTotals = {};
  let grandTotal = 0;
  for (const sp of species) {
    const sum = (chart[sp] || []).reduce((a, b) => a + Number(b || 0), 0);
    speciesTotals[sp] = sum;
    grandTotal += sum;
  }
  const wilkerTotals = labels.map((_, idx) => {
    return species.reduce((acc, sp) => acc + Number((chart[sp] && chart[sp][idx]) || 0), 0);
  });
  const maxWilkerTotal = Math.max(...wilkerTotals, 1);
  return { chart, labels, species, speciesTotals, grandTotal, wilkerTotals, maxWilkerTotal };
});

const lalatChartAgg = computed(() => {
  const chart = siveraDashData.value.charts?.lalat_status || { MS: [], TMS: [] };
  const labels = siveraDashData.value.labels || [];
  const totalMs = (chart.MS || []).reduce((a, b) => a + Number(b || 0), 0);
  const totalTms = (chart.TMS || []).reduce((a, b) => a + Number(b || 0), 0);
  const grandTotal = totalMs + totalTms;
  const wilkerTotals = labels.map((_, idx) => {
    return Number((chart.MS && chart.MS[idx]) || 0) + Number((chart.TMS && chart.TMS[idx]) || 0);
  });
  const maxWilkerTotal = Math.max(...wilkerTotals, 1);
  return { chart, labels, totalMs, totalTms, grandTotal, pctMs: grandTotal > 0 ? ((totalMs / grandTotal) * 100).toFixed(1) : '100.0', wilkerTotals, maxWilkerTotal };
});

const tikusChartAgg = computed(() => {
  const chart = siveraDashData.value.charts?.spesies_tikus || {};
  const labels = siveraDashData.value.labels || [];
  const species = Object.keys(chart);
  const speciesTotals = {};
  let grandTotal = 0;
  for (const sp of species) {
    const sum = (chart[sp] || []).reduce((a, b) => a + Number(b || 0), 0);
    speciesTotals[sp] = sum;
    grandTotal += sum;
  }
  const wilkerTotals = labels.map((_, idx) => {
    return species.reduce((acc, sp) => acc + Number((chart[sp] && chart[sp][idx]) || 0), 0);
  });
  const maxWilkerTotal = Math.max(...wilkerTotals, 1);
  return { chart, labels, species, speciesTotals, grandTotal, wilkerTotals, maxWilkerTotal };
});

const jentikKontainerData = computed(() => {
  const list = siveraDashData.value.charts?.jentik_kontainer || [];
  const total = list.reduce((a, b) => a + Number(b.count || 0), 0);
  const top = list.length > 0 ? `${list[0].name} (${list[0].count})` : '-';
  return { list, total, top };
});

// Modal Detail State

// Modal states for SIVERA
const showAddInspeksiModal = ref(false);
const addInspeksiType = ref('sivera');
const isSubmittingInspeksi = ref(false);
const isEditMode = ref(false);
const editId = ref(null);

const todayStr = new Date().toISOString().split('T')[0];
const currentMonthStr = todayStr.substring(0, 7);

// SIVERA Vector Forms & Formulas
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

// Open Add Sivera Modal
const openAddInspeksi = (typeOrVektor = 'Jentik Aedes sp.', vectorSub = null) => {
  isEditMode.value = false;
  editId.value = null;
  addInspeksiType.value = 'sivera';
  
  let targetVektor = 'Jentik Aedes sp.';
  if (vectorSub) {
    targetVektor = vectorSub;
  } else if (typeOrVektor && typeOrVektor !== 'sivera') {
    targetVektor = typeOrVektor;
  }
  siveraActiveVektor.value = targetVektor;

  const defWilker = props.wilker_options?.[0] || 'Wilayah Kerja Patimban';

  formSiveraJentik.value.wilker = defWilker;
  formSiveraLalat.value.wilker = defWilker;
  formSiveraKecoa.value.wilker = defWilker;
  formSiveraTikus.value.wilker = defWilker;
  formSiveraPinjal.value.wilker = defWilker;
  formSiveraAnophelesJentik.value.wilker = defWilker;
  formSiveraAnophelesNyamuk.value.wilker = defWilker;

  showAddInspeksiModal.value = true;
};

// Open Edit Sivera Modal
const openEditSivera = (item) => {
  isEditMode.value = true;
  editId.value = item.id;
  addInspeksiType.value = 'sivera';
  siveraActiveVektor.value = item.jenis_vektor;
  
  if (item.jenis_vektor?.includes('Jentik Aedes') && item.detail_data) {
    formSiveraJentik.value = { ...formSiveraJentik.value, ...item.detail_data };
  } else if (item.jenis_vektor?.includes('Lalat') && item.detail_data) {
    formSiveraLalat.value = { ...formSiveraLalat.value, ...item.detail_data };
  } else if (item.jenis_vektor?.includes('Kecoa') && item.detail_data) {
    formSiveraKecoa.value = { ...formSiveraKecoa.value, ...item.detail_data };
  } else if (item.jenis_vektor?.includes('Tikus') && item.detail_data) {
    formSiveraTikus.value = { ...formSiveraTikus.value, ...item.detail_data };
  } else if (item.jenis_vektor?.includes('Pinjal') && item.detail_data) {
    formSiveraPinjal.value = { ...formSiveraPinjal.value, ...item.detail_data };
  } else if (item.jenis_vektor?.includes('Anopheles') && item.jenis_vektor?.includes('Jentik') && item.detail_data) {
    formSiveraAnophelesJentik.value = { ...formSiveraAnophelesJentik.value, ...item.detail_data };
  } else if (item.jenis_vektor?.includes('Anopheles') && item.jenis_vektor?.includes('Nyamuk') && item.detail_data) {
    formSiveraAnophelesNyamuk.value = { ...formSiveraAnophelesNyamuk.value, ...item.detail_data };
  }
  
  showAddInspeksiModal.value = true;
};

// SIVERA Submit
const submitInspeksi = async () => {
  isSubmittingInspeksi.value = true;
  try {
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
          abj: abjJentik.value,
          hi: hiJentik.value,
          ci: ciJentik.value,
          bi: biJentik.value,
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
          ...formSiveraLalat.value,
          detail_lokasi: formattedLokasiItems,
          fly_index_rata: flyIndexRata.value,
          fly_index_max: flyIndexMax.value,
        },
      };
    } else if (siveraActiveVektor.value === 'Kecoa') {
      payload = {
        jenis_vektor: 'Kecoa',
        wilker: formSiveraKecoa.value.wilker,
        lokasi: formSiveraKecoa.value.lokasi || 'Kantin / Gudang',
        tanggal: formSiveraKecoa.value.tanggal,
        parameter_utama: `Indeks: ${indeksKecoa.value} (${totalKecoaTertangkap.value} ekor / ${formSiveraKecoa.value.jumlah_perangkap || 0} trap)`,
        nilai_indeks: indeksKecoa.value,
        baku_mutu_status: statusKecoa.value,
        tindakan_pengendalian: formSiveraKecoa.value.tindakan_pengendalian,
        detail_data: {
          ...formSiveraKecoa.value,
          total_tertangkap: totalKecoaTertangkap.value,
          indeks: indeksKecoa.value,
        },
      };
    } else if (siveraActiveVektor.value === 'Tikus') {
      payload = {
        jenis_vektor: 'Tikus',
        wilker: formSiveraTikus.value.wilker,
        lokasi: formSiveraTikus.value.lokasi || 'Buffer Area',
        tanggal: formSiveraTikus.value.tanggal_mulai,
        parameter_utama: `Success Trap: ${successTrapTikus.value}% (${totalTikusTertangkap.value} ekor / ${formSiveraTikus.value.jumlah_perangkap || 0} trap)`,
        nilai_indeks: successTrapTikus.value,
        baku_mutu_status: statusTikus.value,
        tindakan_pengendalian: formSiveraTikus.value.tindakan_pengendalian,
        detail_data: {
          ...formSiveraTikus.value,
          total_tertangkap: totalTikusTertangkap.value,
          success_trap: successTrapTikus.value,
        },
      };
    } else if (siveraActiveVektor.value === 'Pinjal') {
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
    } else if (siveraActiveVektor.value === 'Jentik Anopheles') {
      payload = {
        jenis_vektor: 'Jentik Anopheles',
        wilker: formSiveraAnophelesJentik.value.wilker,
        lokasi: formSiveraAnophelesJentik.value.lokasi || 'Rawa / Saluran Terbuka',
        tanggal: formSiveraAnophelesJentik.value.tanggal,
        parameter_utama: `Indeks Habitat: ${indeksHabitatAnopheles.value} (${formSiveraAnophelesJentik.value.habitat_positif || 0}/${formSiveraAnophelesJentik.value.habitat_diperiksa || 0} habitat)`,
        nilai_indeks: indeksHabitatAnopheles.value,
        baku_mutu_status: statusAnophelesJentik.value,
        tindakan_pengendalian: formSiveraAnophelesJentik.value.tindakan_pengendalian,
        detail_data: {
          ...formSiveraAnophelesJentik.value,
          indeks_habitat: indeksHabitatAnopheles.value,
        },
      };
    } else if (siveraActiveVektor.value === 'Nyamuk Anopheles') {
      payload = {
        jenis_vektor: 'Nyamuk Anopheles',
        wilker: formSiveraAnophelesNyamuk.value.lokasi || 'Pesisir',
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

    let res;
    if (isEditMode.value && editId.value) {
      res = await axios.put(`/sivera/${editId.value}`, payload);
    } else {
      res = await axios.post('/sivera', payload);
    }
    alert(res.data.message);

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

// Delete Sivera Item
const deleteInspection = async (item) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus catatan ${item.jenis_vektor} di ${item.wilker}?`)) return;
  try {
    const res = await axios.delete(`/sivera/${item.id}`);
    alert(res.data.message || 'Data surveilans berhasil dihapus');
    router.reload();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menghapus data.');
  }
};

// Detail Modal State
const showDetailModal = ref(false);
const detailItem = ref(null);
const detailType = ref('sivera');

const openDetail = (item, type = 'sivera') => {
  detailItem.value = item;
  detailType.value = type;
  showDetailModal.value = true;
};

const closeDetail = () => {
  showDetailModal.value = false;
  detailItem.value = null;
};

</script>


<template>
  <Head title="Surveilans Vektor dan Reservoar (SIVERA) &bull; BKK Bandung" />

  <HybridNavbar mode="inertia" currentRoute="sivera">
    <main class="w-full px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

      <!-- ==================== SIVERA TAB / SYSTEM CONTENT ==================== -->
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
              @click="switchSiveraVektor(vTab)"
              class="px-3 py-1.5 rounded-full font-semibold transition shrink-0 cursor-pointer flex items-center gap-1.5"
              :class="siveraFilterVektor === vTab ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
            >
              <Activity v-if="vTab === 'Dashboard'" class="w-3.5 h-3.5" />
              <span>{{ vTab }}</span>
            </button>
          </div>

          <!-- FILTER KHUSUS MENU SIVERA -->
          <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/60 my-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
              <!-- Search -->
              <div class="lg:col-span-4">
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Cari Lokasi / Titik Survei / Parameter SIVERA</label>
                <div class="relative">
                  <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                  <input
                    type="text"
                    v-model="search"
                    @keyup.enter="applyFilter()"
                    placeholder="Ketik lokasi, titik survei, parameter..."
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

              <!-- Status Baku Mutu -->
              <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Status Baku Mutu</label>
                <select
                  v-model="selectedRisiko"
                  @change="applyFilter()"
                  class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
                >
                  <option value="">Semua Status</option>
                  <option value="Memenuhi Syarat">Memenuhi Syarat (MS)</option>
                  <option value="Di Atas Baku Mutu">Di Atas Baku Mutu (TMS)</option>
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
              <span v-if="search" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-800 border border-indigo-200 text-[11px]">
                Kata Kunci: "{{ search }}"
                <button type="button" @click="search = ''; applyFilter()" class="hover:text-rose-600 font-bold">&times;</button>
              </span>
              <span v-if="selectedWilker" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-teal-50 text-[#005851] border border-teal-200 text-[11px]">
                Wilker: {{ selectedWilker }}
                <button type="button" @click="selectedWilker = ''; applyFilter()" class="hover:text-rose-600 font-bold">&times;</button>
              </span>
              <span v-if="selectedRisiko" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 text-[11px]">
                Status: {{ selectedRisiko }}
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

          <!-- 1. SIVERA DASHBOARD VIEW -->
          <div v-if="siveraFilterVektor === 'Dashboard'" class="space-y-6 mt-4">
            <!-- Modern Hero Banner -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 border border-teal-500/30 text-white p-6 md:p-7 shadow-xl">
              <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
              <div class="absolute -left-12 -top-12 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

              <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div class="max-w-2xl">
                  <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/20 border border-teal-400/30 text-teal-300 text-xs font-semibold mb-3">
                    <Sparkles class="w-3.5 h-3.5" />
                    <span>Sistem Informasi Surveilans Vektor &amp; Reservoar (SIVERA)</span>
                  </div>
                  <h2 class="text-xl md:text-2xl font-black text-white tracking-tight">
                    Dashboard Surveilans Vektor dan Reservoar
                  </h2>
                  <p class="text-xs md:text-sm text-slate-300 mt-1 leading-relaxed">
                    Ringkasan surveilans kepadatan jentik, lalat, kecoa, tikus, pinjal, dan anopheles di wilayah perimeter &amp; buffer dalam tampilan analitik interaktif, terpadu, dan profesional.
                  </p>
                </div>

                <!-- Right Quick Metrics Badges -->
                <div class="flex flex-wrap items-center gap-2.5">
                  <div class="px-3.5 py-2 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 text-center min-w-[110px]">
                    <div class="text-[10px] text-teal-200 uppercase tracking-wider font-bold">Wilker Terpantau</div>
                    <div class="text-lg font-black text-white font-mono mt-0.5">{{ siveraDashData.labels.length }}</div>
                  </div>
                  <div class="px-3.5 py-2 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 text-center min-w-[110px]">
                    <div class="text-[10px] text-teal-200 uppercase tracking-wider font-bold">Total Pemantauan</div>
                    <div class="text-lg font-black text-white font-mono mt-0.5">{{ siveraDashData.summary.total_surveilans }}</div>
                  </div>
                  <div class="px-3.5 py-2 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 text-center min-w-[120px]">
                    <div class="text-[10px] text-emerald-300 uppercase tracking-wider font-bold">Baku Mutu Aman</div>
                    <div class="text-lg font-black text-emerald-300 font-mono mt-0.5">{{ siveraDashData.summary.kepatuhan_pct }}%</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- 6 KPI Metric Cards Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3.5">
              <!-- 1. Jentik -->
              <div class="p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between">
                <div>
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Jentik Aedes</span>
                    <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                      <Droplets class="w-4 h-4" />
                    </div>
                  </div>
                  <div class="text-2xl font-black text-slate-900 dark:text-white font-mono">
                    {{ siveraDashData.summary.jentik }}
                    <span class="text-xs font-normal text-slate-400">survei</span>
                  </div>
                  <div class="text-[11px] text-slate-500 mt-1">
                    Positif: <strong class="text-rose-600 font-mono">{{ siveraDashData.summary.kontainer_positif }}</strong> kontainer
                  </div>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                  <span class="text-[10px] font-semibold text-slate-400">ABJ Rata-rata</span>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono" :class="Number(siveraDashData.summary.abj_avg) >= 95 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300'">
                    {{ siveraDashData.summary.abj_avg }}%
                  </span>
                </div>
              </div>

              <!-- 2. Lalat -->
              <div class="p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between">
                <div>
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Lalat (Fly Index)</span>
                    <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                      <Bug class="w-4 h-4" />
                    </div>
                  </div>
                  <div class="text-2xl font-black text-slate-900 dark:text-white font-mono">
                    {{ Number(siveraDashData.summary.avg_lalat).toFixed(2) }}
                  </div>
                  <div class="text-[11px] text-slate-500 mt-1">
                    Total: <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ siveraDashData.summary.lalat }}</strong> survei
                  </div>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                  <span class="text-[10px] font-semibold text-slate-400">Kepadatan MS/TMS</span>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                    {{ siveraDashData.summary.lalat_ms }} MS / {{ siveraDashData.summary.lalat_tms }} TMS
                  </span>
                </div>
              </div>

              <!-- 3. Kecoa -->
              <div class="p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between">
                <div>
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kecoa Tertangkap</span>
                    <div class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                      <Activity class="w-4 h-4" />
                    </div>
                  </div>
                  <div class="text-2xl font-black text-slate-900 dark:text-white font-mono">
                    {{ siveraDashData.summary.kecoa_tertangkap }}
                    <span class="text-xs font-normal text-slate-400">ekor</span>
                  </div>
                  <div class="text-[11px] text-slate-500 mt-1">
                    Dari <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ siveraDashData.summary.kecoa_perangkap }}</strong> perangkap
                  </div>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                  <span class="text-[10px] font-semibold text-slate-400">Kepadatan</span>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">
                    {{ Number(siveraDashData.summary.avg_kecoa).toFixed(2) }} /trap
                  </span>
                </div>
              </div>

              <!-- 4. Tikus -->
              <div class="p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between">
                <div>
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Success Trap Tikus</span>
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                      <Rat class="w-4 h-4" />
                    </div>
                  </div>
                  <div class="text-2xl font-black text-slate-900 dark:text-white font-mono">
                    {{ Number(siveraDashData.summary.avg_success_trap).toFixed(2) }}%
                  </div>
                  <div class="text-[11px] text-slate-500 mt-1">
                    <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ siveraDashData.summary.tikus_tertangkap }}</strong> ekor / <span class="font-mono">{{ siveraDashData.summary.tikus_perangkap }}</span> trap
                  </div>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                  <span class="text-[10px] font-semibold text-slate-400">Baku Mutu (&lt;1%)</span>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono" :class="Number(siveraDashData.summary.avg_success_trap) < 1 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300'">
                    {{ Number(siveraDashData.summary.avg_success_trap) < 1 ? 'Memenuhi' : 'Waspada' }}
                  </span>
                </div>
              </div>

              <!-- 5. Pinjal -->
              <div class="p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between">
                <div>
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pinjal Tikus</span>
                    <div class="w-7 h-7 rounded-lg bg-cyan-50 dark:bg-cyan-950/40 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                      <Layers class="w-4 h-4" />
                    </div>
                  </div>
                  <div class="text-2xl font-black text-slate-900 dark:text-white font-mono">
                    {{ siveraDashData.summary.pinjal_umum }}
                  </div>
                  <div class="text-[11px] text-slate-500 mt-1">
                    Indeks Khusus: <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ siveraDashData.summary.pinjal_khusus }}</strong>
                  </div>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                  <span class="text-[10px] font-semibold text-slate-400">Total Survei</span>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-cyan-50 text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300">
                    {{ siveraDashData.summary.pinjal }} kegiatan
                  </span>
                </div>
              </div>

              <!-- 6. Anopheles -->
              <div class="p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between">
                <div>
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Anopheles</span>
                    <div class="w-7 h-7 rounded-lg bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                      <Wind class="w-4 h-4" />
                    </div>
                  </div>
                  <div class="text-2xl font-black text-slate-900 dark:text-white font-mono">
                    {{ Number(siveraDashData.summary.anopheles_jentik || 0) + Number(siveraDashData.summary.anopheles_nyamuk || 0) }}
                    <span class="text-xs font-normal text-slate-400">survei</span>
                  </div>
                  <div class="text-[11px] text-slate-500 mt-1">
                    Indeks Habitat: <strong class="text-purple-600 font-mono">{{ siveraDashData.summary.avg_indeks_habitat }}%</strong>
                  </div>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                  <span class="text-[10px] font-semibold text-slate-400">Rata MBR</span>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300">
                    {{ Number(siveraDashData.summary.avg_mbr).toFixed(2) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- 4 Interactive Visualization Cards Grid with Toggle [Grafik] / [Tabel] -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
              <!-- 1. Card Spesies Kecoa per Wilayah Kerja -->
              <div class="p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex flex-col justify-between">
                <!-- Header -->
                <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
                  <div>
                    <div class="flex items-center gap-2">
                      <h3 class="text-sm font-bold text-slate-900 dark:text-white">Spesies Kecoa per Wilker</h3>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800">Kecoa</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Perbandingan spesies tertangkap pada masing-masing wilayah kerja.</p>
                  </div>
                  <!-- Switcher -->
                  <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 text-xs">
                    <button
                      type="button"
                      @click="siveraCardViews.kecoa = 'chart'"
                      class="flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer"
                      :class="siveraCardViews.kecoa === 'chart' ? 'bg-white dark:bg-slate-700 text-teal-700 dark:text-teal-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    >
                      <BarChart3 class="w-3.5 h-3.5" />
                      <span>Grafik</span>
                    </button>
                    <button
                      type="button"
                      @click="siveraCardViews.kecoa = 'table'"
                      class="flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer"
                      :class="siveraCardViews.kecoa === 'table' ? 'bg-white dark:bg-slate-700 text-teal-700 dark:text-teal-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    >
                      <Table class="w-3.5 h-3.5" />
                      <span>Tabel</span>
                    </button>
                  </div>
                </div>

                <!-- Body (Min height 260px) -->
                <div class="mt-4 min-h-[260px] flex flex-col justify-center">
                  <!-- Mode Grafik -->
                  <div v-if="siveraCardViews.kecoa === 'chart'" class="space-y-4">
                    <!-- Species Legend Strip -->
                    <div class="flex flex-wrap items-center gap-2.5 text-[11px] pb-2 border-b border-dashed border-slate-100 dark:border-slate-800">
                      <div v-for="sp in kecoaChartAgg.species" :key="sp" class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full" :class="kecoaSpeciesColors[sp]?.bar || 'bg-slate-400'"></span>
                        <span class="text-slate-600 dark:text-slate-400 font-medium">{{ sp }}:</span>
                        <strong class="font-mono text-slate-800 dark:text-slate-200">{{ kecoaChartAgg.speciesTotals[sp] || 0 }}</strong>
                      </div>
                    </div>

                    <!-- Bars per Wilker -->
                    <div class="space-y-3.5 max-h-[220px] overflow-y-auto pr-1">
                      <div v-for="(wilker, idx) in kecoaChartAgg.labels" :key="wilker" class="space-y-1">
                        <div class="flex items-center justify-between text-xs">
                          <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[240px]">{{ wilker }}</span>
                          <span class="font-mono text-xs font-bold text-slate-600 dark:text-slate-400">
                            Total: {{ kecoaChartAgg.wilkerTotals[idx] || 0 }} ekor
                          </span>
                        </div>
                        <!-- Stacked Bar -->
                        <div class="h-4 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden flex shadow-inner">
                          <template v-if="(kecoaChartAgg.wilkerTotals[idx] || 0) > 0">
                            <div
                              v-for="sp in kecoaChartAgg.species"
                              :key="sp"
                              v-show="((kecoaChartAgg.chart[sp] && kecoaChartAgg.chart[sp][idx]) || 0) > 0"
                              class="h-full transition-all duration-300 relative group"
                              :class="kecoaSpeciesColors[sp]?.bar || 'bg-slate-400'"
                              :style="{ width: (((kecoaChartAgg.chart[sp][idx] || 0) / kecoaChartAgg.wilkerTotals[idx]) * 100) + '%' }"
                              :title="`${sp}: ${kecoaChartAgg.chart[sp][idx]} ekor`"
                            ></div>
                          </template>
                          <div v-else class="h-full w-full flex items-center justify-center text-[10px] text-slate-400 italic">
                            0 ekor
                          </div>
                        </div>
                      </div>
                      <div v-if="kecoaChartAgg.labels.length === 0" class="text-center py-8 text-xs text-slate-400">
                        Belum ada data wilayah kerja surveilans kecoa.
                      </div>
                    </div>
                  </div>

                  <!-- Mode Tabel -->
                  <div v-else class="overflow-y-auto max-h-[260px] rounded-xl border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-xs text-center border-collapse">
                      <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 z-10 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                          <th class="py-2 px-3 text-left font-bold">Wilayah Kerja</th>
                          <th v-for="sp in kecoaChartAgg.species" :key="sp" class="py-2 px-2 font-bold">{{ sp }}</th>
                          <th class="py-2 px-2.5 font-bold">Total</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="(wilker, idx) in kecoaChartAgg.labels" :key="wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                          <td class="py-2 px-3 text-left font-semibold text-slate-800 dark:text-slate-200">{{ wilker }}</td>
                          <td v-for="sp in kecoaChartAgg.species" :key="sp" class="py-2 px-2 font-mono">
                            <span :class="((kecoaChartAgg.chart[sp] && kecoaChartAgg.chart[sp][idx]) || 0) > 0 ? 'font-bold text-slate-900 dark:text-white' : 'text-slate-400'">
                              {{ (kecoaChartAgg.chart[sp] && kecoaChartAgg.chart[sp][idx]) || 0 }}
                            </span>
                          </td>
                          <td class="py-2 px-2.5 font-mono font-bold">
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300">
                              {{ kecoaChartAgg.wilkerTotals[idx] || 0 }}
                            </span>
                          </td>
                        </tr>
                        <tr v-if="kecoaChartAgg.labels.length === 0">
                          <td :colspan="kecoaChartAgg.species.length + 2" class="py-6 text-center text-slate-400">Belum ada data surveilans kecoa</td>
                        </tr>
                      </tbody>
                      <tfoot class="sticky bottom-0 bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
                        <tr>
                          <td class="py-2 px-3 text-left">Total Keseluruhan</td>
                          <td v-for="sp in kecoaChartAgg.species" :key="sp" class="py-2 px-2 font-mono">
                            {{ kecoaChartAgg.speciesTotals[sp] || 0 }}
                          </td>
                          <td class="py-2 px-2.5 font-mono">
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-900 text-white dark:bg-white dark:text-slate-900">
                              {{ kecoaChartAgg.grandTotal }}
                            </span>
                          </td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>

              <!-- 2. Card Status Kepadatan Lalat -->
              <div class="p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex flex-col justify-between">
                <!-- Header -->
                <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
                  <div>
                    <div class="flex items-center gap-2">
                      <h3 class="text-sm font-bold text-slate-900 dark:text-white">Status Kepadatan Lalat</h3>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">Lalat</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Perbandingan kategori MS (&lt; 2) dan TMS (&ge; 2) berdasarkan standar fly grill.</p>
                  </div>
                  <!-- Switcher -->
                  <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 text-xs">
                    <button
                      type="button"
                      @click="siveraCardViews.lalat = 'chart'"
                      class="flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer"
                      :class="siveraCardViews.lalat === 'chart' ? 'bg-white dark:bg-slate-700 text-teal-700 dark:text-teal-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    >
                      <BarChart3 class="w-3.5 h-3.5" />
                      <span>Grafik</span>
                    </button>
                    <button
                      type="button"
                      @click="siveraCardViews.lalat = 'table'"
                      class="flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer"
                      :class="siveraCardViews.lalat === 'table' ? 'bg-white dark:bg-slate-700 text-teal-700 dark:text-teal-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    >
                      <Table class="w-3.5 h-3.5" />
                      <span>Tabel</span>
                    </button>
                  </div>
                </div>

                <!-- Body (Min height 260px) -->
                <div class="mt-4 min-h-[260px] flex flex-col justify-center">
                  <!-- Mode Grafik -->
                  <div v-if="siveraCardViews.lalat === 'chart'" class="space-y-4">
                    <!-- Summary Strip -->
                    <div class="grid grid-cols-4 gap-2 text-center text-xs pb-2 border-b border-dashed border-slate-100 dark:border-slate-800">
                      <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800">
                        <span class="text-[10px] text-slate-400 font-semibold block">Total Survei</span>
                        <strong class="font-mono text-slate-800 dark:text-slate-200 text-sm">{{ lalatChartAgg.grandTotal }}</strong>
                      </div>
                      <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300">
                        <span class="text-[10px] font-semibold block">MS (&lt; 2)</span>
                        <strong class="font-mono text-sm">{{ lalatChartAgg.totalMs }}</strong>
                      </div>
                      <div class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-300">
                        <span class="text-[10px] font-semibold block">TMS (&ge; 2)</span>
                        <strong class="font-mono text-sm">{{ lalatChartAgg.totalTms }}</strong>
                      </div>
                      <div class="p-2 rounded-xl bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-300">
                        <span class="text-[10px] font-semibold block">% Kepatuhan</span>
                        <strong class="font-mono text-sm">{{ lalatChartAgg.pctMs }}%</strong>
                      </div>
                    </div>

                    <!-- Bars per Wilker -->
                    <div class="space-y-3.5 max-h-[190px] overflow-y-auto pr-1">
                      <div v-for="(wilker, idx) in lalatChartAgg.labels" :key="wilker" class="space-y-1">
                        <div class="flex items-center justify-between text-xs">
                          <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[240px]">{{ wilker }}</span>
                          <div class="flex items-center gap-2">
                            <span class="text-[11px] text-emerald-600 font-bold font-mono">{{ lalatChartAgg.chart.MS[idx] || 0 }} MS</span>
                            <span class="text-slate-300">&bull;</span>
                            <span class="text-[11px] text-rose-600 font-bold font-mono">{{ lalatChartAgg.chart.TMS[idx] || 0 }} TMS</span>
                          </div>
                        </div>
                        <!-- Split Bar -->
                        <div class="h-4 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden flex shadow-inner">
                          <template v-if="(lalatChartAgg.wilkerTotals[idx] || 0) > 0">
                            <div
                              class="h-full bg-emerald-500 transition-all duration-300"
                              :style="{ width: (((lalatChartAgg.chart.MS[idx] || 0) / lalatChartAgg.wilkerTotals[idx]) * 100) + '%' }"
                              :title="`MS: ${lalatChartAgg.chart.MS[idx] || 0}`"
                            ></div>
                            <div
                              class="h-full bg-rose-500 transition-all duration-300"
                              :style="{ width: (((lalatChartAgg.chart.TMS[idx] || 0) / lalatChartAgg.wilkerTotals[idx]) * 100) + '%' }"
                              :title="`TMS: ${lalatChartAgg.chart.TMS[idx] || 0}`"
                            ></div>
                          </template>
                          <div v-else class="h-full w-full flex items-center justify-center text-[10px] text-slate-400 italic">
                            0 survei
                          </div>
                        </div>
                      </div>
                      <div v-if="lalatChartAgg.labels.length === 0" class="text-center py-8 text-xs text-slate-400">
                        Belum ada data wilayah kerja surveilans lalat.
                      </div>
                    </div>
                  </div>

                  <!-- Mode Tabel -->
                  <div v-else class="overflow-y-auto max-h-[260px] rounded-xl border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-xs text-center border-collapse">
                      <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 z-10 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                          <th class="py-2 px-3 text-left font-bold">Wilayah Kerja</th>
                          <th class="py-2 px-2.5 font-bold text-emerald-700 dark:text-emerald-400">MS (&lt; 2)</th>
                          <th class="py-2 px-2.5 font-bold text-rose-700 dark:text-rose-400">TMS (&ge; 2)</th>
                          <th class="py-2 px-2.5 font-bold">Total Survei</th>
                          <th class="py-2 px-2.5 font-bold">Persentase MS</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="(wilker, idx) in lalatChartAgg.labels" :key="wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                          <td class="py-2 px-3 text-left font-semibold text-slate-800 dark:text-slate-200">{{ wilker }}</td>
                          <td class="py-2 px-2.5 font-mono">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300">
                              {{ lalatChartAgg.chart.MS[idx] || 0 }}
                            </span>
                          </td>
                          <td class="py-2 px-2.5 font-mono">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300">
                              {{ lalatChartAgg.chart.TMS[idx] || 0 }}
                            </span>
                          </td>
                          <td class="py-2 px-2.5 font-mono font-bold text-slate-900 dark:text-white">
                            {{ lalatChartAgg.wilkerTotals[idx] || 0 }}
                          </td>
                          <td class="py-2 px-2.5 font-mono font-bold">
                            <span
                              class="px-2 py-0.5 rounded-full text-[10px]"
                              :class="(lalatChartAgg.wilkerTotals[idx] || 0) > 0 && (((lalatChartAgg.chart.MS[idx] || 0) / lalatChartAgg.wilkerTotals[idx]) * 100) >= 80 ? 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300'"
                            >
                              {{ (lalatChartAgg.wilkerTotals[idx] || 0) > 0 ? (((lalatChartAgg.chart.MS[idx] || 0) / lalatChartAgg.wilkerTotals[idx]) * 100).toFixed(1) : 100 }}%
                            </span>
                          </td>
                        </tr>
                        <tr v-if="lalatChartAgg.labels.length === 0">
                          <td colspan="5" class="py-6 text-center text-slate-400">Belum ada data surveilans lalat</td>
                        </tr>
                      </tbody>
                      <tfoot class="sticky bottom-0 bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
                        <tr>
                          <td class="py-2 px-3 text-left">Total Keseluruhan</td>
                          <td class="py-2 px-2.5 font-mono text-emerald-700 dark:text-emerald-400">{{ lalatChartAgg.totalMs }}</td>
                          <td class="py-2 px-2.5 font-mono text-rose-700 dark:text-rose-400">{{ lalatChartAgg.totalTms }}</td>
                          <td class="py-2 px-2.5 font-mono">{{ lalatChartAgg.grandTotal }}</td>
                          <td class="py-2 px-2.5 font-mono">
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-900 text-white dark:bg-white dark:text-slate-900">
                              {{ lalatChartAgg.pctMs }}%
                            </span>
                          </td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>

              <!-- 3. Card Kontainer Positif Jentik -->
              <div class="p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex flex-col justify-between">
                <!-- Header -->
                <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
                  <div>
                    <div class="flex items-center gap-2">
                      <h3 class="text-sm font-bold text-slate-900 dark:text-white">Kontainer Positif Jentik</h3>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">Jentik</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Jenis kontainer penampungan air yang ditemukan positif jentik Aedes sp.</p>
                  </div>
                  <!-- Switcher -->
                  <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 text-xs">
                    <button
                      type="button"
                      @click="siveraCardViews.jentik = 'chart'"
                      class="flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer"
                      :class="siveraCardViews.jentik === 'chart' ? 'bg-white dark:bg-slate-700 text-teal-700 dark:text-teal-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    >
                      <BarChart3 class="w-3.5 h-3.5" />
                      <span>Grafik</span>
                    </button>
                    <button
                      type="button"
                      @click="siveraCardViews.jentik = 'table'"
                      class="flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer"
                      :class="siveraCardViews.jentik === 'table' ? 'bg-white dark:bg-slate-700 text-teal-700 dark:text-teal-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    >
                      <Table class="w-3.5 h-3.5" />
                      <span>Tabel</span>
                    </button>
                  </div>
                </div>

                <!-- Body (Min height 260px) -->
                <div class="mt-4 min-h-[260px] flex flex-col justify-center">
                  <!-- Mode Grafik -->
                  <div v-if="siveraCardViews.jentik === 'chart'" class="space-y-3.5">
                    <div class="flex items-center justify-between text-xs pb-2 border-b border-dashed border-slate-100 dark:border-slate-800">
                      <span class="text-slate-500">Total Temuan Positif: <strong class="text-slate-900 dark:text-white font-mono">{{ jentikKontainerData.total }}</strong></span>
                      <span class="text-xs font-semibold text-teal-600 dark:text-teal-400">{{ jentikKontainerData.top }}</span>
                    </div>

                    <div class="space-y-3 max-h-[210px] overflow-y-auto pr-1">
                      <div v-for="(item, idx) in jentikKontainerData.list" :key="item.name" class="space-y-1">
                        <div class="flex items-center justify-between text-xs">
                          <div class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded-full bg-teal-100 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 text-[10px] font-bold flex items-center justify-center">
                              {{ idx + 1 }}
                            </span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ item.name }}</span>
                          </div>
                          <div class="flex items-center gap-2">
                            <span class="font-mono text-slate-900 dark:text-white font-bold">{{ item.count }} temuan</span>
                            <span class="text-[11px] font-mono text-slate-400">({{ item.pct }}%)</span>
                          </div>
                        </div>
                        <div class="h-3 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden shadow-inner">
                          <div
                            class="h-full bg-gradient-to-r from-teal-500 to-emerald-400 rounded-full transition-all duration-300"
                            :style="{ width: item.pct + '%' }"
                          ></div>
                        </div>
                      </div>

                      <div v-if="jentikKontainerData.list.length === 0" class="text-center py-10 text-xs text-slate-400">
                        Belum ada temuan kontainer positif jentik tercatat.
                      </div>
                    </div>
                  </div>

                  <!-- Mode Tabel -->
                  <div v-else class="overflow-y-auto max-h-[260px] rounded-xl border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-xs text-center border-collapse">
                      <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 z-10 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                          <th class="py-2 px-2.5 w-12 font-bold">No</th>
                          <th class="py-2 px-3 text-left font-bold">Jenis Kontainer</th>
                          <th class="py-2 px-3 font-bold">Jumlah Temuan</th>
                          <th class="py-2 px-3 font-bold">Persentase (%)</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="(item, idx) in jentikKontainerData.list" :key="item.name" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                          <td class="py-2 px-2.5 text-slate-400 font-mono">{{ idx + 1 }}</td>
                          <td class="py-2 px-3 text-left font-semibold text-slate-800 dark:text-slate-200">{{ item.name }}</td>
                          <td class="py-2 px-3 font-mono">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300">
                              {{ item.count }}
                            </span>
                          </td>
                          <td class="py-2 px-3 font-mono font-semibold text-slate-700 dark:text-slate-300">{{ item.pct }}%</td>
                        </tr>
                        <tr v-if="jentikKontainerData.list.length === 0">
                          <td colspan="4" class="py-6 text-center text-slate-400">Belum ada temuan kontainer positif</td>
                        </tr>
                      </tbody>
                      <tfoot class="sticky bottom-0 bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
                        <tr>
                          <td colspan="2" class="py-2 px-3 text-left">Total Temuan Positif</td>
                          <td class="py-2 px-3 font-mono text-blue-700 dark:text-blue-400">{{ jentikKontainerData.total }}</td>
                          <td class="py-2 px-3 font-mono">100%</td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>

              <!-- 4. Card Sebaran Spesies Tikus per Wilayah Kerja -->
              <div class="p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm flex flex-col justify-between">
                <!-- Header -->
                <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
                  <div>
                    <div class="flex items-center gap-2">
                      <h3 class="text-sm font-bold text-slate-900 dark:text-white">Sebaran Spesies Tikus</h3>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">Tikus &amp; Pinjal</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Distribusi spesies tikus tertangkap pada masing-masing wilayah kerja.</p>
                  </div>
                  <!-- Switcher -->
                  <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/60 text-xs">
                    <button
                      type="button"
                      @click="siveraCardViews.tikus = 'chart'"
                      class="flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer"
                      :class="siveraCardViews.tikus === 'chart' ? 'bg-white dark:bg-slate-700 text-teal-700 dark:text-teal-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    >
                      <BarChart3 class="w-3.5 h-3.5" />
                      <span>Grafik</span>
                    </button>
                    <button
                      type="button"
                      @click="siveraCardViews.tikus = 'table'"
                      class="flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold transition cursor-pointer"
                      :class="siveraCardViews.tikus === 'table' ? 'bg-white dark:bg-slate-700 text-teal-700 dark:text-teal-300 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    >
                      <Table class="w-3.5 h-3.5" />
                      <span>Tabel</span>
                    </button>
                  </div>
                </div>

                <!-- Body (Min height 260px) -->
                <div class="mt-4 min-h-[260px] flex flex-col justify-center">
                  <!-- Mode Grafik -->
                  <div v-if="siveraCardViews.tikus === 'chart'" class="space-y-4">
                    <!-- Species Legend Strip -->
                    <div class="flex flex-wrap items-center gap-2.5 text-[11px] pb-2 border-b border-dashed border-slate-100 dark:border-slate-800">
                      <div v-for="sp in tikusChartAgg.species" :key="sp" class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full" :class="tikusSpeciesColors[sp]?.bar || 'bg-slate-400'"></span>
                        <span class="text-slate-600 dark:text-slate-400 font-medium">{{ sp }}:</span>
                        <strong class="font-mono text-slate-800 dark:text-slate-200">{{ tikusChartAgg.speciesTotals[sp] || 0 }}</strong>
                      </div>
                    </div>

                    <!-- Bars per Wilker -->
                    <div class="space-y-3.5 max-h-[220px] overflow-y-auto pr-1">
                      <div v-for="(wilker, idx) in tikusChartAgg.labels" :key="wilker" class="space-y-1">
                        <div class="flex items-center justify-between text-xs">
                          <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[240px]">{{ wilker }}</span>
                          <span class="font-mono text-xs font-bold text-slate-600 dark:text-slate-400">
                            Total: {{ tikusChartAgg.wilkerTotals[idx] || 0 }} ekor
                          </span>
                        </div>
                        <!-- Stacked Bar -->
                        <div class="h-4 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden flex shadow-inner">
                          <template v-if="(tikusChartAgg.wilkerTotals[idx] || 0) > 0">
                            <div
                              v-for="sp in tikusChartAgg.species"
                              :key="sp"
                              v-show="((tikusChartAgg.chart[sp] && tikusChartAgg.chart[sp][idx]) || 0) > 0"
                              class="h-full transition-all duration-300 relative group"
                              :class="tikusSpeciesColors[sp]?.bar || 'bg-slate-400'"
                              :style="{ width: (((tikusChartAgg.chart[sp][idx] || 0) / tikusChartAgg.wilkerTotals[idx]) * 100) + '%' }"
                              :title="`${sp}: ${tikusChartAgg.chart[sp][idx]} ekor`"
                            ></div>
                          </template>
                          <div v-else class="h-full w-full flex items-center justify-center text-[10px] text-slate-400 italic">
                            0 ekor
                          </div>
                        </div>
                      </div>
                      <div v-if="tikusChartAgg.labels.length === 0" class="text-center py-8 text-xs text-slate-400">
                        Belum ada data wilayah kerja surveilans tikus.
                      </div>
                    </div>
                  </div>

                  <!-- Mode Tabel -->
                  <div v-else class="overflow-y-auto max-h-[260px] rounded-xl border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-xs text-center border-collapse">
                      <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 z-10 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                          <th class="py-2 px-3 text-left font-bold">Wilayah Kerja</th>
                          <th v-for="sp in tikusChartAgg.species" :key="sp" class="py-2 px-2 font-bold">{{ sp }}</th>
                          <th class="py-2 px-2.5 font-bold">Total</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="(wilker, idx) in tikusChartAgg.labels" :key="wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                          <td class="py-2 px-3 text-left font-semibold text-slate-800 dark:text-slate-200">{{ wilker }}</td>
                          <td v-for="sp in tikusChartAgg.species" :key="sp" class="py-2 px-2 font-mono">
                            <span :class="((tikusChartAgg.chart[sp] && tikusChartAgg.chart[sp][idx]) || 0) > 0 ? 'font-bold text-slate-900 dark:text-white' : 'text-slate-400'">
                              {{ (tikusChartAgg.chart[sp] && tikusChartAgg.chart[sp][idx]) || 0 }}
                            </span>
                          </td>
                          <td class="py-2 px-2.5 font-mono font-bold">
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300">
                              {{ tikusChartAgg.wilkerTotals[idx] || 0 }}
                            </span>
                          </td>
                        </tr>
                        <tr v-if="tikusChartAgg.labels.length === 0">
                          <td :colspan="tikusChartAgg.species.length + 2" class="py-6 text-center text-slate-400">Belum ada data surveilans tikus</td>
                        </tr>
                      </tbody>
                      <tfoot class="sticky bottom-0 bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
                        <tr>
                          <td class="py-2 px-3 text-left">Total Keseluruhan</td>
                          <td v-for="sp in tikusChartAgg.species" :key="sp" class="py-2 px-2 font-mono">
                            {{ tikusChartAgg.speciesTotals[sp] || 0 }}
                          </td>
                          <td class="py-2 px-2.5 font-mono">
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-900 text-white dark:bg-white dark:text-slate-900">
                              {{ tikusChartAgg.grandTotal }}
                            </span>
                          </td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <!-- Master Detail Matrix: Rekapitulasi Rinci Data Surveilans per Wilayah Kerja -->
            <div class="rounded-3xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
              <!-- Card Header with Filter and Tabs -->
              <div class="p-5 bg-gradient-to-r from-slate-50 via-slate-50/70 to-white dark:from-slate-800/60 dark:to-slate-900 border-b border-slate-200 dark:border-slate-800">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                  <div>
                    <h3 class="text-sm md:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                      <Table class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                      <span>Rekapitulasi Rinci Data Surveilans per Wilayah Kerja</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                      Tabel detail hasil surveilans lengkap untuk seluruh vektor dan reservoar penyakit.
                    </p>
                  </div>

                  <!-- Search Wilker in Matrix -->
                  <div class="flex items-center gap-2.5">
                    <div class="relative min-w-[200px]">
                      <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
                      <input
                        v-model="siveraWilkerSearch"
                        type="text"
                        placeholder="Cari wilayah kerja..."
                        class="w-full pl-8 pr-3 py-1.5 rounded-xl text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-teal-500"
                      />
                      <button
                        v-if="siveraWilkerSearch"
                        type="button"
                        @click="siveraWilkerSearch = ''"
                        class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600"
                      >
                        <X class="w-3 h-3" />
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Sub-tab Pills -->
                <div class="flex flex-wrap items-center gap-2 mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-800">
                  <button
                    type="button"
                    @click="activeSiveraDetailTab = 'semua'"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                    :class="activeSiveraDetailTab === 'semua' ? 'bg-teal-600 text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-100'"
                  >
                    <Layers class="w-3.5 h-3.5" />
                    <span>Semua Vektor</span>
                  </button>

                  <button
                    type="button"
                    @click="activeSiveraDetailTab = 'jentik'"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                    :class="activeSiveraDetailTab === 'jentik' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-100'"
                  >
                    <Droplets class="w-3.5 h-3.5" />
                    <span>Jentik Aedes</span>
                  </button>

                  <button
                    type="button"
                    @click="activeSiveraDetailTab = 'lalat'"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                    :class="activeSiveraDetailTab === 'lalat' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-100'"
                  >
                    <Bug class="w-3.5 h-3.5" />
                    <span>Lalat</span>
                  </button>

                  <button
                    type="button"
                    @click="activeSiveraDetailTab = 'kecoa'"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                    :class="activeSiveraDetailTab === 'kecoa' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-100'"
                  >
                    <Activity class="w-3.5 h-3.5" />
                    <span>Kecoa</span>
                  </button>

                  <button
                    type="button"
                    @click="activeSiveraDetailTab = 'tikus'"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                    :class="activeSiveraDetailTab === 'tikus' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-100'"
                  >
                    <Rat class="w-3.5 h-3.5" />
                    <span>Tikus &amp; Pinjal</span>
                  </button>

                  <button
                    type="button"
                    @click="activeSiveraDetailTab = 'anopheles'"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                    :class="activeSiveraDetailTab === 'anopheles' ? 'bg-purple-600 text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-100'"
                  >
                    <Wind class="w-3.5 h-3.5" />
                    <span>Anopheles</span>
                  </button>
                </div>
              </div>

              <!-- Table Body -->
              <div class="overflow-x-auto">
                <!-- 1. Tab Semua Vektor (2-tier Header Table) -->
                <table v-if="activeSiveraDetailTab === 'semua'" class="w-full text-xs text-center border-collapse">
                  <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold">
                      <th rowspan="2" class="py-2.5 px-2.5 w-12 border-r border-slate-200 dark:border-slate-700">No</th>
                      <th rowspan="2" class="py-2.5 px-3.5 text-left min-w-[200px] border-r border-slate-200 dark:border-slate-700">Wilayah Kerja</th>
                      <th colspan="3" class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700 bg-blue-50/50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400">Jentik Aedes</th>
                      <th colspan="3" class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700 bg-amber-50/50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400">Lalat</th>
                      <th colspan="2" class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700 bg-rose-50/50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400">Kecoa</th>
                      <th colspan="3" class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700 bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400">Tikus</th>
                      <th colspan="2" class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700 bg-cyan-50/50 dark:bg-cyan-950/20 text-cyan-700 dark:text-cyan-400">Pinjal</th>
                      <th colspan="2" class="py-1.5 px-2 bg-purple-50/50 dark:bg-purple-950/20 text-purple-700 dark:text-purple-400">Anopheles</th>
                    </tr>
                    <tr class="bg-slate-100/70 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 text-[10px] font-semibold">
                      <!-- Jentik -->
                      <th class="py-1.5 px-2">Survei</th>
                      <th class="py-1.5 px-2">Kontainer (+)</th>
                      <th class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700">ABJ (%)</th>
                      <!-- Lalat -->
                      <th class="py-1.5 px-2">Survei</th>
                      <th class="py-1.5 px-2">Rata Fly Idx</th>
                      <th class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700">Status</th>
                      <!-- Kecoa -->
                      <th class="py-1.5 px-2">Survei</th>
                      <th class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700">Tertangkap</th>
                      <!-- Tikus -->
                      <th class="py-1.5 px-2">Perangkap</th>
                      <th class="py-1.5 px-2">Tertangkap</th>
                      <th class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700">Trap (%)</th>
                      <!-- Pinjal -->
                      <th class="py-1.5 px-2">Koleksi</th>
                      <th class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700">Pinjal</th>
                      <!-- Anopheles -->
                      <th class="py-1.5 px-2">Habitat (%)</th>
                      <th class="py-1.5 px-2">MBR</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="(row, idx) in filteredWilkerDetail" :key="row.wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                      <td class="py-2.5 px-2.5 text-slate-400 font-mono border-r border-slate-100 dark:border-slate-800">{{ idx + 1 }}</td>
                      <td class="py-2.5 px-3.5 text-left font-semibold text-slate-900 dark:text-slate-100 border-r border-slate-100 dark:border-slate-800">{{ row.wilker }}</td>
                      <!-- Jentik -->
                      <td class="py-2.5 px-2 font-mono">{{ row.jentik_survei }}</td>
                      <td class="py-2.5 px-2 font-mono">
                        <span class="px-1.5 py-0.5 rounded text-[10px]" :class="row.jentik_kontainer_positif > 0 ? 'bg-rose-50 text-rose-700 font-bold' : 'text-slate-400'">
                          {{ row.jentik_kontainer_positif }}
                        </span>
                      </td>
                      <td class="py-2.5 px-2 font-mono font-bold border-r border-slate-100 dark:border-slate-800" :class="row.abj >= 95 ? 'text-emerald-600' : 'text-rose-600'">
                        {{ row.abj }}%
                      </td>
                      <!-- Lalat -->
                      <td class="py-2.5 px-2 font-mono">{{ row.lalat_survei }}</td>
                      <td class="py-2.5 px-2 font-mono">{{ Number(row.lalat_avg).toFixed(2) }}</td>
                      <td class="py-2.5 px-2 border-r border-slate-100 dark:border-slate-800">
                        <span v-if="row.lalat_survei > 0" class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="row.lalat_avg < 2 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'">
                          {{ row.lalat_avg < 2 ? 'MS' : 'TMS' }}
                        </span>
                        <span v-else class="text-slate-400">-</span>
                      </td>
                      <!-- Kecoa -->
                      <td class="py-2.5 px-2 font-mono">{{ row.kecoa_survei }}</td>
                      <td class="py-2.5 px-2 font-mono border-r border-slate-100 dark:border-slate-800">
                        <span class="px-1.5 py-0.5 rounded text-[10px]" :class="row.kecoa_tertangkap > 0 ? 'bg-rose-50 text-rose-700 font-bold' : 'text-slate-400'">
                          {{ row.kecoa_tertangkap }}
                        </span>
                      </td>
                      <!-- Tikus -->
                      <td class="py-2.5 px-2 font-mono">{{ row.tikus_perangkap }}</td>
                      <td class="py-2.5 px-2 font-mono">
                        <span class="px-1.5 py-0.5 rounded text-[10px]" :class="row.tikus_tertangkap > 0 ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-400'">
                          {{ row.tikus_tertangkap }}
                        </span>
                      </td>
                      <td class="py-2.5 px-2 font-mono font-bold border-r border-slate-100 dark:border-slate-800">{{ Number(row.tikus_success_trap_avg).toFixed(2) }}%</td>
                      <!-- Pinjal -->
                      <td class="py-2.5 px-2 font-mono">{{ row.pinjal_survei }}</td>
                      <td class="py-2.5 px-2 font-mono border-r border-slate-100 dark:border-slate-800">
                        {{ row.pinjal_umum + row.pinjal_khusus }}
                      </td>
                      <!-- Anopheles -->
                      <td class="py-2.5 px-2 font-mono">{{ row.anopheles_habitat_avg }}%</td>
                      <td class="py-2.5 px-2 font-mono">{{ Number(row.anopheles_mbr_avg).toFixed(2) }}</td>
                    </tr>
                    <tr v-if="filteredWilkerDetail.length === 0">
                      <td colspan="17" class="py-8 text-center text-slate-400">Tidak ada data wilayah kerja yang sesuai dengan pencarian.</td>
                    </tr>
                  </tbody>
                  <tfoot class="bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t-2 border-slate-200 dark:border-slate-700">
                    <tr>
                      <td colspan="2" class="py-2.5 px-3.5 text-left border-r border-slate-200 dark:border-slate-700">Total / Rata-rata</td>
                      <!-- Jentik -->
                      <td class="py-2.5 px-2 font-mono">{{ wilkerDetailTotals.jentik_survei }}</td>
                      <td class="py-2.5 px-2 font-mono text-rose-600">{{ wilkerDetailTotals.jentik_kontainer_positif }}</td>
                      <td class="py-2.5 px-2 font-mono border-r border-slate-200 dark:border-slate-700 text-teal-600">{{ wilkerDetailTotals.abj_avg }}%</td>
                      <!-- Lalat -->
                      <td class="py-2.5 px-2 font-mono">{{ wilkerDetailTotals.lalat_survei }}</td>
                      <td class="py-2.5 px-2 font-mono">{{ wilkerDetailTotals.lalat_avg }}</td>
                      <td class="py-2.5 px-2 border-r border-slate-200 dark:border-slate-700">-</td>
                      <!-- Kecoa -->
                      <td class="py-2.5 px-2 font-mono">{{ wilkerDetailTotals.kecoa_survei }}</td>
                      <td class="py-2.5 px-2 font-mono border-r border-slate-200 dark:border-slate-700 text-rose-600">{{ wilkerDetailTotals.kecoa_tertangkap }}</td>
                      <!-- Tikus -->
                      <td class="py-2.5 px-2 font-mono">{{ wilkerDetailTotals.tikus_perangkap }}</td>
                      <td class="py-2.5 px-2 font-mono text-indigo-600">{{ wilkerDetailTotals.tikus_tertangkap }}</td>
                      <td class="py-2.5 px-2 font-mono border-r border-slate-200 dark:border-slate-700">{{ wilkerDetailTotals.tikus_trap_avg }}%</td>
                      <!-- Pinjal -->
                      <td class="py-2.5 px-2 font-mono">{{ wilkerDetailTotals.pinjal_survei }}</td>
                      <td class="py-2.5 px-2 font-mono border-r border-slate-200 dark:border-slate-700">{{ wilkerDetailTotals.pinjal_umum + wilkerDetailTotals.pinjal_khusus }}</td>
                      <!-- Anopheles -->
                      <td class="py-2.5 px-2 font-mono">{{ wilkerDetailTotals.anopheles_habitat_avg }}%</td>
                      <td class="py-2.5 px-2 font-mono">{{ wilkerDetailTotals.anopheles_mbr_avg }}</td>
                    </tr>
                  </tfoot>
                </table>

                <!-- 2. Tab Jentik Aedes -->
                <table v-else-if="activeSiveraDetailTab === 'jentik'" class="w-full text-xs text-center border-collapse">
                  <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold">
                      <th class="py-2.5 px-3 w-12">No</th>
                      <th class="py-2.5 px-3 text-left">Wilayah Kerja</th>
                      <th class="py-2.5 px-3">Total Survei</th>
                      <th class="py-2.5 px-3">Rumah Diperiksa</th>
                      <th class="py-2.5 px-3">Rumah Positif</th>
                      <th class="py-2.5 px-3">Kontainer Diperiksa</th>
                      <th class="py-2.5 px-3">Kontainer Positif</th>
                      <th class="py-2.5 px-3">ABJ (%)</th>
                      <th class="py-2.5 px-3">Status Baku Mutu (&ge; 95%)</th>
                      <th class="py-2.5 px-3">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="(row, idx) in filteredWilkerDetail" :key="row.wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                      <td class="py-2.5 px-3 text-slate-400 font-mono">{{ idx + 1 }}</td>
                      <td class="py-2.5 px-3 text-left font-semibold text-slate-900 dark:text-slate-100">{{ row.wilker }}</td>
                      <td class="py-2.5 px-3 font-mono font-bold">{{ row.jentik_survei }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ row.jentik_rumah_diperiksa }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ row.jentik_rumah_positif }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ row.jentik_kontainer_diperiksa }}</td>
                      <td class="py-2.5 px-3 font-mono font-bold text-rose-600">{{ row.jentik_kontainer_positif }}</td>
                      <td class="py-2.5 px-3 font-mono font-black" :class="row.abj >= 95 ? 'text-emerald-600' : 'text-rose-600'">{{ row.abj }}%</td>
                      <td class="py-2.5 px-3">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="row.abj >= 95 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'">
                          {{ row.abj >= 95 ? 'Memenuhi Syarat' : 'Tidak Memenuhi' }}
                        </span>
                      </td>
                      <td class="py-2.5 px-3">
                        <button type="button" @click="switchSiveraVektor('Jentik Aedes')" class="text-teal-600 hover:text-teal-700 font-semibold text-[11px] hover:underline cursor-pointer">
                          Lihat Data
                        </button>
                      </td>
                    </tr>
                    <tr v-if="filteredWilkerDetail.length === 0">
                      <td colspan="10" class="py-8 text-center text-slate-400">Belum ada data surveilans jentik.</td>
                    </tr>
                  </tbody>
                  <tfoot class="bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
                    <tr>
                      <td colspan="2" class="py-2.5 px-3 text-left">Total Keseluruhan</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.jentik_survei }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.jentik_rumah_diperiksa }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.jentik_rumah_positif }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.jentik_kontainer_diperiksa }}</td>
                      <td class="py-2.5 px-3 font-mono text-rose-600">{{ wilkerDetailTotals.jentik_kontainer_positif }}</td>
                      <td class="py-2.5 px-3 font-mono text-teal-600">{{ wilkerDetailTotals.abj_avg }}%</td>
                      <td colspan="2">-</td>
                    </tr>
                  </tfoot>
                </table>

                <!-- 3. Tab Lalat -->
                <table v-else-if="activeSiveraDetailTab === 'lalat'" class="w-full text-xs text-center border-collapse">
                  <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold">
                      <th class="py-2.5 px-3 w-12">No</th>
                      <th class="py-2.5 px-3 text-left">Wilayah Kerja</th>
                      <th class="py-2.5 px-3">Total Survei</th>
                      <th class="py-2.5 px-3">Rata Fly Index</th>
                      <th class="py-2.5 px-3 text-emerald-700 dark:text-emerald-400">Jumlah MS (&lt; 2)</th>
                      <th class="py-2.5 px-3 text-rose-700 dark:text-rose-400">Jumlah TMS (&ge; 2)</th>
                      <th class="py-2.5 px-3">Kepatuhan (%)</th>
                      <th class="py-2.5 px-3">Status Baku Mutu</th>
                      <th class="py-2.5 px-3">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="(row, idx) in filteredWilkerDetail" :key="row.wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                      <td class="py-2.5 px-3 text-slate-400 font-mono">{{ idx + 1 }}</td>
                      <td class="py-2.5 px-3 text-left font-semibold text-slate-900 dark:text-slate-100">{{ row.wilker }}</td>
                      <td class="py-2.5 px-3 font-mono font-bold">{{ row.lalat_survei }}</td>
                      <td class="py-2.5 px-3 font-mono font-bold text-slate-900 dark:text-white">{{ Number(row.lalat_avg).toFixed(2) }}</td>
                      <td class="py-2.5 px-3 font-mono text-emerald-600 font-bold">{{ row.lalat_ms }}</td>
                      <td class="py-2.5 px-3 font-mono text-rose-600 font-bold">{{ row.lalat_tms }}</td>
                      <td class="py-2.5 px-3 font-mono font-bold">
                        {{ row.lalat_survei > 0 ? ((row.lalat_ms / row.lalat_survei) * 100).toFixed(1) : 100 }}%
                      </td>
                      <td class="py-2.5 px-3">
                        <span v-if="row.lalat_survei > 0" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="row.lalat_avg < 2 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'">
                          {{ row.lalat_avg < 2 ? 'Memenuhi Syarat' : 'Di Atas Baku Mutu' }}
                        </span>
                        <span v-else class="text-slate-400">-</span>
                      </td>
                      <td class="py-2.5 px-3">
                        <button type="button" @click="switchSiveraVektor('Lalat')" class="text-teal-600 hover:text-teal-700 font-semibold text-[11px] hover:underline cursor-pointer">
                          Lihat Data
                        </button>
                      </td>
                    </tr>
                    <tr v-if="filteredWilkerDetail.length === 0">
                      <td colspan="9" class="py-8 text-center text-slate-400">Belum ada data surveilans lalat.</td>
                    </tr>
                  </tbody>
                  <tfoot class="bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
                    <tr>
                      <td colspan="2" class="py-2.5 px-3 text-left">Total Keseluruhan</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.lalat_survei }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.lalat_avg }}</td>
                      <td class="py-2.5 px-3 font-mono text-emerald-600">{{ wilkerDetailTotals.lalat_ms }}</td>
                      <td class="py-2.5 px-3 font-mono text-rose-600">{{ wilkerDetailTotals.lalat_tms }}</td>
                      <td colspan="3">-</td>
                    </tr>
                  </tfoot>
                </table>

                <!-- 4. Tab Kecoa -->
                <table v-else-if="activeSiveraDetailTab === 'kecoa'" class="w-full text-xs text-center border-collapse">
                  <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold">
                      <th class="py-2.5 px-3 w-12">No</th>
                      <th class="py-2.5 px-3 text-left">Wilayah Kerja</th>
                      <th class="py-2.5 px-3">Total Survei</th>
                      <th class="py-2.5 px-3">Perangkap Dipasang</th>
                      <th class="py-2.5 px-3">Kecoa Tertangkap</th>
                      <th class="py-2.5 px-3">Indeks Kepadatan (ekor/trap)</th>
                      <th class="py-2.5 px-3">Status Baku Mutu (&lt; 2)</th>
                      <th class="py-2.5 px-3">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="(row, idx) in filteredWilkerDetail" :key="row.wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                      <td class="py-2.5 px-3 text-slate-400 font-mono">{{ idx + 1 }}</td>
                      <td class="py-2.5 px-3 text-left font-semibold text-slate-900 dark:text-slate-100">{{ row.wilker }}</td>
                      <td class="py-2.5 px-3 font-mono font-bold">{{ row.kecoa_survei }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ row.kecoa_perangkap }}</td>
                      <td class="py-2.5 px-3 font-mono font-bold text-rose-600">{{ row.kecoa_tertangkap }}</td>
                      <td class="py-2.5 px-3 font-mono font-bold">{{ Number(row.kecoa_kepadatan).toFixed(2) }}</td>
                      <td class="py-2.5 px-3">
                        <span v-if="row.kecoa_survei > 0" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="row.kecoa_kepadatan < 2 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'">
                          {{ row.kecoa_kepadatan < 2 ? 'Memenuhi Syarat' : 'Di Atas Baku Mutu' }}
                        </span>
                        <span v-else class="text-slate-400">-</span>
                      </td>
                      <td class="py-2.5 px-3">
                        <button type="button" @click="switchSiveraVektor('Kecoa')" class="text-teal-600 hover:text-teal-700 font-semibold text-[11px] hover:underline cursor-pointer">
                          Lihat Data
                        </button>
                      </td>
                    </tr>
                    <tr v-if="filteredWilkerDetail.length === 0">
                      <td colspan="8" class="py-8 text-center text-slate-400">Belum ada data surveilans kecoa.</td>
                    </tr>
                  </tbody>
                  <tfoot class="bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
                    <tr>
                      <td colspan="2" class="py-2.5 px-3 text-left">Total Keseluruhan</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.kecoa_survei }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.kecoa_perangkap }}</td>
                      <td class="py-2.5 px-3 font-mono text-rose-600">{{ wilkerDetailTotals.kecoa_tertangkap }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.kecoa_kepadatan }}</td>
                      <td colspan="2">-</td>
                    </tr>
                  </tfoot>
                </table>

                <!-- 5. Tab Tikus & Pinjal -->
                <table v-else-if="activeSiveraDetailTab === 'tikus'" class="w-full text-xs text-center border-collapse">
                  <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold">
                      <th class="py-2.5 px-3 w-12">No</th>
                      <th class="py-2.5 px-3 text-left">Wilayah Kerja</th>
                      <th class="py-2.5 px-3">Survei Tikus</th>
                      <th class="py-2.5 px-3">Perangkap</th>
                      <th class="py-2.5 px-3">Tikus Tertangkap</th>
                      <th class="py-2.5 px-3">Success Trap (%)</th>
                      <th class="py-2.5 px-3">Survei Pinjal</th>
                      <th class="py-2.5 px-3">Pinjal Umum</th>
                      <th class="py-2.5 px-3">Pinjal Khusus</th>
                      <th class="py-2.5 px-3">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="(row, idx) in filteredWilkerDetail" :key="row.wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                      <td class="py-2.5 px-3 text-slate-400 font-mono">{{ idx + 1 }}</td>
                      <td class="py-2.5 px-3 text-left font-semibold text-slate-900 dark:text-slate-100">{{ row.wilker }}</td>
                      <td class="py-2.5 px-3 font-mono font-bold">{{ row.tikus_survei }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ row.tikus_perangkap }}</td>
                      <td class="py-2.5 px-3 font-mono font-bold text-indigo-600">{{ row.tikus_tertangkap }}</td>
                      <td class="py-2.5 px-3 font-mono font-black" :class="row.tikus_success_trap_avg < 1 ? 'text-emerald-600' : 'text-amber-600'">
                        {{ Number(row.tikus_success_trap_avg).toFixed(2) }}%
                      </td>
                      <td class="py-2.5 px-3 font-mono">{{ row.pinjal_survei }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ row.pinjal_umum }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ row.pinjal_khusus }}</td>
                      <td class="py-2.5 px-3">
                        <button type="button" @click="switchSiveraVektor('Tikus')" class="text-teal-600 hover:text-teal-700 font-semibold text-[11px] hover:underline cursor-pointer">
                          Lihat Data
                        </button>
                      </td>
                    </tr>
                    <tr v-if="filteredWilkerDetail.length === 0">
                      <td colspan="10" class="py-8 text-center text-slate-400">Belum ada data surveilans tikus &amp; pinjal.</td>
                    </tr>
                  </tbody>
                  <tfoot class="bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
                    <tr>
                      <td colspan="2" class="py-2.5 px-3 text-left">Total Keseluruhan</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.tikus_survei }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.tikus_perangkap }}</td>
                      <td class="py-2.5 px-3 font-mono text-indigo-600">{{ wilkerDetailTotals.tikus_tertangkap }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.tikus_trap_avg }}%</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.pinjal_survei }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.pinjal_umum }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.pinjal_khusus }}</td>
                      <td>-</td>
                    </tr>
                  </tfoot>
                </table>

                <!-- 6. Tab Anopheles -->
                <table v-else-if="activeSiveraDetailTab === 'anopheles'" class="w-full text-xs text-center border-collapse">
                  <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold">
                      <th class="py-2.5 px-3 w-12">No</th>
                      <th class="py-2.5 px-3 text-left">Wilayah Kerja</th>
                      <th class="py-2.5 px-3">Survei Jentik</th>
                      <th class="py-2.5 px-3">Indeks Habitat (%)</th>
                      <th class="py-2.5 px-3">Status Jentik (&lt; 1%)</th>
                      <th class="py-2.5 px-3">Survei Nyamuk Dewasa</th>
                      <th class="py-2.5 px-3">Rata MBR (ekor/org/mlm)</th>
                      <th class="py-2.5 px-3">Status Nyamuk (&lt; 0.025)</th>
                      <th class="py-2.5 px-3">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="(row, idx) in filteredWilkerDetail" :key="row.wilker" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                      <td class="py-2.5 px-3 text-slate-400 font-mono">{{ idx + 1 }}</td>
                      <td class="py-2.5 px-3 text-left font-semibold text-slate-900 dark:text-slate-100">{{ row.wilker }}</td>
                      <td class="py-2.5 px-3 font-mono font-bold">{{ row.anopheles_jentik_survei }}</td>
                      <td class="py-2.5 px-3 font-mono font-bold">{{ row.anopheles_habitat_avg }}%</td>
                      <td class="py-2.5 px-3">
                        <span v-if="row.anopheles_jentik_survei > 0" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="row.anopheles_habitat_avg < 1 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'">
                          {{ row.anopheles_habitat_avg < 1 ? 'Memenuhi Syarat' : 'Di Atas Baku Mutu' }}
                        </span>
                        <span v-else class="text-slate-400">-</span>
                      </td>
                      <td class="py-2.5 px-3 font-mono font-bold">{{ row.anopheles_nyamuk_survei }}</td>
                      <td class="py-2.5 px-3 font-mono font-bold">{{ Number(row.anopheles_mbr_avg).toFixed(2) }}</td>
                      <td class="py-2.5 px-3">
                        <span v-if="row.anopheles_nyamuk_survei > 0" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="row.anopheles_mbr_avg < 0.025 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'">
                          {{ row.anopheles_mbr_avg < 0.025 ? 'Memenuhi Syarat' : 'Di Atas Baku Mutu' }}
                        </span>
                        <span v-else class="text-slate-400">-</span>
                      </td>
                      <td class="py-2.5 px-3">
                        <button type="button" @click="switchSiveraVektor('Nyamuk Anopheles')" class="text-teal-600 hover:text-teal-700 font-semibold text-[11px] hover:underline cursor-pointer">
                          Lihat Data
                        </button>
                      </td>
                    </tr>
                    <tr v-if="filteredWilkerDetail.length === 0">
                      <td colspan="9" class="py-8 text-center text-slate-400">Belum ada data surveilans Anopheles.</td>
                    </tr>
                  </tbody>
                  <tfoot class="bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200 border-t border-slate-200 dark:border-slate-700">
                    <tr>
                      <td colspan="2" class="py-2.5 px-3 text-left">Total Keseluruhan</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.anopheles_jentik_survei }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.anopheles_habitat_avg }}%</td>
                      <td>-</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.anopheles_nyamuk_survei }}</td>
                      <td class="py-2.5 px-3 font-mono">{{ wilkerDetailTotals.anopheles_mbr_avg }}</td>
                      <td colspan="2">-</td>
                    </tr>
                  </tfoot>
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
                      <div
                        v-if="item.tindakan_pengendalian"
                        class="text-[11px] text-slate-700 dark:text-slate-300"
                        :title="formatTindakanPengendalian(item.tindakan_pengendalian)"
                      >
                        <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-[10.5px] leading-tight font-medium text-slate-700 dark:text-slate-200 line-clamp-2">
                          {{ formatTindakanPengendalian(item.tindakan_pengendalian) }}
                        </span>
                      </div>
                      <p
                        v-else
                        class="text-[10px] text-slate-400 italic"
                      >
                        {{ (item.baku_mutu_status || '').toLowerCase().includes('tidak') || (item.baku_mutu_status || '').toLowerCase().includes('atas') ? 'Menunggu intervensi petugas' : 'Kondisi vektor terkendali' }}
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

          <!-- Pagination & Per-Page Controls (SIVERA) -->
          <div
            v-if="sivera_list?.total > 0 && siveraFilterVektor !== 'Dashboard'"
            class="px-4 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 rounded-b-3xl flex flex-col sm:flex-row items-center justify-between gap-3 text-xs"
          >
            <div class="text-slate-500 dark:text-slate-400 flex items-center gap-2 flex-wrap">
              <span>Menampilkan <strong class="text-slate-700 dark:text-slate-200">{{ sivera_list.from || 0 }}</strong> - <strong class="text-slate-700 dark:text-slate-200">{{ sivera_list.to || 0 }}</strong> dari <strong class="text-slate-700 dark:text-slate-200">{{ sivera_list.total || 0 }}</strong> data</span>
              <span class="text-slate-300 dark:text-slate-600">|</span>
              <div class="flex items-center gap-1.5">
                <span class="text-[11px] text-slate-400">Tampilkan:</span>
                <select
                  :value="perPageSivera"
                  @change="changePerPage('sivera', $event.target.value)"
                  class="py-1 px-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                >
                  <option :value="10">10 / hal</option>
                  <option :value="25">25 / hal</option>
                  <option :value="50">50 / hal</option>
                  <option :value="100">100 / hal</option>
                  <option :value="1000">Semua</option>
                </select>
              </div>
            </div>
            <div v-if="sivera_list.links && sivera_list.links.length > 3" class="flex items-center gap-1 flex-wrap">
              <template v-for="(link, i) in sivera_list.links" :key="i">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  v-html="link.label"
                  preserve-state
                  class="px-2.5 py-1 rounded-lg text-xs font-medium transition cursor-pointer"
                  :class="link.active ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
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
      <!-- MODAL INPUT SURVEILANS VEKTOR SIVERA                           -->
      <!-- ============================================================== -->
      <div
        v-if="showAddInspeksiModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
      >
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-4xl max-h-[92vh] overflow-hidden flex flex-col shadow-2xl animate-in fade-in zoom-in-95 duration-200">
          <!-- Modal Header -->
          <div class="px-6 py-4 bg-gradient-to-r from-indigo-700 via-indigo-600 to-[#00A99D] text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Bug class="w-5 h-5 text-indigo-200" />
              <h3 class="text-base font-bold tracking-tight">{{ editId ? 'Perbarui Data Surveilans SIVERA' : 'Catat Data Surveilans Vektor (SIVERA)' }}</h3>
            </div>
            <button
              type="button"
              @click="showAddInspeksiModal = false"
              class="p-1 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <!-- Modal Body Form -->
          <div class="p-6 max-h-[75vh] overflow-y-auto space-y-6">
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
              class="px-5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-[#00A99D] text-xs font-bold text-white hover:brightness-105 transition shadow-sm flex items-center gap-2 cursor-pointer disabled:opacity-50"
            >
              <Save class="w-4 h-4" />
              <span>{{ isSubmittingInspeksi ? 'Menyimpan...' : (editId ? 'Simpan Perubahan' : 'Simpan Data Surveilans') }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- MODAL TINDAKAN PENGENDALIAN VEKTOR (SIMPLY CI4 PARITY)          -->
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
      <!-- MODAL DETAIL RINCIAN SIVERA                                    -->
      <!-- ============================================================== -->
      <div
        v-if="showDetailModal && detailItem"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
      >
        <div class="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
          <!-- Modal Header -->
          <div class="px-6 py-4 bg-gradient-to-r from-indigo-700 to-[#00A99D] text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Bug class="w-5 h-5" />
              <h3 class="text-sm font-bold">Rincian Surveilans Vektor: {{ detailItem.jenis_vektor }}</h3>
            </div>
            <button type="button" @click="closeDetail()" class="p-1 rounded-lg hover:bg-white/20 transition cursor-pointer">
              <X class="w-5 h-5" />
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6 max-h-[75vh] overflow-y-auto space-y-4 text-xs">
            <div class="grid grid-cols-2 gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
              <div>
                <span class="text-slate-400 block text-[11px]">Nama Objek / Vektor:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                  {{ detailItem.jenis_vektor }}
                </span>
              </div>
              <div>
                <span class="text-slate-400 block text-[11px]">Wilayah Kerja:</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ detailItem.wilker }}</span>
              </div>
              <div>
                <span class="text-slate-400 block text-[11px]">Tanggal Survei:</span>
                <span class="font-mono text-slate-700 dark:text-slate-300">{{ formatDate(detailItem.tanggal) }}</span>
              </div>
              <div>
                <span class="text-slate-400 block text-[11px]">Lokasi / Titik:</span>
                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ detailItem.lokasi || '-' }}</span>
              </div>
            </div>

            <!-- Scoring / Status Badge -->
            <div class="p-4 rounded-2xl border flex items-center justify-between" :class="getRiskBadgeClass(detailItem.baku_mutu_status)">
              <div>
                <span class="font-bold text-sm">
                  Parameter Utama: {{ detailItem.parameter_utama }}
                </span>
                <p class="text-[11px] opacity-80 mt-0.5">Baku Mutu: {{ detailItem.baku_mutu_status }}</p>
              </div>
              <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border bg-white/50 dark:bg-black/20">
                {{ detailItem.baku_mutu_status }}
              </span>
            </div>

            <!-- Notes & Detail Data -->
            <div class="space-y-3">
              <div v-if="detailItem.saran_rencana_tindakan || detailItem.tindakan_pengendalian">
                <span class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Tindakan Pengendalian:</span>
                <p class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">
                  {{ formatTindakanPengendalian(detailItem.tindakan_pengendalian || detailItem.saran_rencana_tindakan) }}
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

          <!-- Modal Footer -->
          <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div class="text-[11px] text-slate-400 font-mono">
              Status ID: #{{ detailItem.id }}
            </div>
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
