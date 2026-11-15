export const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return isNaN(d.getTime())
    ? dateStr
    : d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

export const getRiskBadgeClass = (risiko) => {
  if (!risiko) {
    return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
  }
  const r = risiko.toLowerCase();
  if (r.includes('tidak memenuhi') || r.includes('di atas') || r.includes('tinggi') || r === 'tms') {
    return 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-800';
  }
  if (r.includes('sedang')) {
    return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-800';
  }
  if (r.includes('rendah') || r.includes('memenuhi syarat') || r === 'ms') {
    return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800';
  }
  return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
};

export const formatTindakanPengendalian = (txt) => {
  if (!txt) return '-';
  return txt;
};

export const getLokasiLalatTop5 = (item) => {
  const sorted = [...(item?.t || [])].map(Number).sort((a, b) => b - a);
  return sorted.slice(0, 5);
};

export const getLokasiLalatTotalTop5 = (item) =>
  getLokasiLalatTop5(item).reduce((acc, curr) => acc + curr, 0);

export const getLokasiLalatRata = (item) =>
  Number((getLokasiLalatTotalTop5(item) / 5).toFixed(2));

export const getLokasiLalatStatus = (item) =>
  getLokasiLalatRata(item) < 2.0 ? 'Memenuhi Syarat' : 'Tidak Memenuhi Syarat';

export const kecoaSpeciesColors = {
  'P. americana': {
    bar: 'bg-amber-500',
    fill: '#f59e0b',
    text: 'text-amber-700 dark:text-amber-400',
    badge: 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300',
  },
  'B. germanica': {
    bar: 'bg-rose-500',
    fill: '#ef4444',
    text: 'text-rose-700 dark:text-rose-400',
    badge: 'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300',
  },
  'S. longipalpa': {
    bar: 'bg-purple-500',
    fill: '#8b5cf6',
    text: 'text-purple-700 dark:text-purple-400',
    badge: 'bg-purple-100 text-purple-800 dark:bg-purple-950/40 dark:text-purple-300',
  },
  'N. rhombifolia': {
    bar: 'bg-cyan-500',
    fill: '#06b6d4',
    text: 'text-cyan-700 dark:text-cyan-400',
    badge: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950/40 dark:text-cyan-300',
  },
  'Lain-Lain': {
    bar: 'bg-slate-400',
    fill: '#94a3b8',
    text: 'text-slate-700 dark:text-slate-400',
    badge: 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300',
  },
};

export const tikusSpeciesColors = {
  'R. norvegicus': {
    bar: 'bg-blue-500',
    fill: '#3b82f6',
    text: 'text-blue-700 dark:text-blue-400',
    badge: 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300',
  },
  'R. tanezumi': {
    bar: 'bg-indigo-500',
    fill: '#6366f1',
    text: 'text-indigo-700 dark:text-indigo-400',
    badge: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/40 dark:text-indigo-300',
  },
  'M. musculus': {
    bar: 'bg-amber-500',
    fill: '#f59e0b',
    text: 'text-amber-700 dark:text-amber-400',
    badge: 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300',
  },
  'S. murinus': {
    bar: 'bg-emerald-500',
    fill: '#10b981',
    text: 'text-emerald-700 dark:text-emerald-400',
    badge: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300',
  },
  'Lain-Lain': {
    bar: 'bg-slate-400',
    fill: '#94a3b8',
    text: 'text-slate-700 dark:text-slate-400',
    badge: 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300',
  },
};

export const standardSpesiesTikus = [
  'Rattus tanezumi',
  'Rattus norvegicus',
  'Bandicota indica',
  'Mus musculus',
  'Rattus argentiventer',
  'Rattus exulans',
  'Rattus tiomanicus',
  'Lain-lain',
];

export const standardHabitatAnopheles = [
  'Lagun',
  'Tambak',
  'Sawah',
  'Rawa',
  'Genangan',
  'Kolam',
  'Sungai',
  'Saluran Air',
  'Muara',
  'Tambak Terbengkalai',
];

export const anophelesJamSlots = [
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
