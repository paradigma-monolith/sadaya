/**
 * Shared formatting and status/risk badge helpers for TANGKAP Ship Surveillance.
 */

export const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val || 0);
};

export const formatNumber = (val) => {
  return new Intl.NumberFormat('id-ID').format(val || 0);
};

export const getRiskBadgeClass = (kresiko) => {
  switch (kresiko) {
    case 'TINGGI':
      return 'bg-red-500/10 text-red-700 dark:text-red-400 border border-red-500/30';
    case 'SEDANG':
      return 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/30';
    default:
      return 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30';
  }
};

export const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'ZONA KARANTINA':
      return 'bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-500/30';
    case 'SANDAR':
      return 'bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border border-cyan-500/30';
    default:
      return 'bg-slate-500/10 text-slate-700 dark:text-slate-400 border border-slate-500/30';
  }
};

export const getQuestionDisplay = (item, qNo) => {
  const no = Number(qNo);
  const questions = item?.questions || {};
  const entry = questions[no] ?? questions[String(no)] ?? null;

  if (entry && typeof entry === 'object' && 'answer' in entry) {
    const score = Number(entry.score || 0);
    const isNeutral = no === 1 && (entry.answer === 'Tidak Berlaku (Domestik)' || entry.answer === '-');
    const isRisk = !isNeutral && (score > 0 || entry.answer === 'Ya' || (no === 19 && entry.answer === 'Tidak'));
    return {
      text: entry.answer || '-',
      badgeClass: isRisk
        ? 'bg-red-500/10 text-red-600 border border-red-500/30'
        : isNeutral
          ? 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700'
          : 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/30',
    };
  }

  if (no === 1) {
    if (item?.kategori === 'Dalam Negeri' && !item?.negara_singgah_14_hari) {
      return {
        text: 'Tidak Berlaku (Domestik)',
        badgeClass: 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700',
      };
    }
    if (item?.negara_singgah_14_hari) {
      const legacyScore = Number(questions.Q1 ?? questions.q1 ?? 0);
      return {
        text: item.negara_singgah_14_hari,
        badgeClass: legacyScore > 0
          ? 'bg-red-500/10 text-red-600 border border-red-500/30'
          : 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/30',
      };
    }
  }

  const rawVal = questions[`Q${no}`] ?? questions[`q${no}`];
  if (rawVal !== undefined && rawVal !== null) {
    const numVal = Number(rawVal);
    const isRisk = numVal > 0;
    const text = no === 19 ? (numVal === 0 ? 'Ya' : 'Tidak') : (numVal > 0 ? 'Ya' : 'Tidak');
    return {
      text,
      badgeClass: isRisk
        ? 'bg-red-500/10 text-red-600 border border-red-500/30'
        : 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/30',
    };
  }

  return {
    text: no === 1 && item?.kategori === 'Dalam Negeri' ? 'Tidak Berlaku (Domestik)' : 'Tidak',
    badgeClass: 'bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700',
  };
};

export const withTabQuery = (rawUrl, tab = 'data') => {
  if (!rawUrl) return '#';
  try {
    const base = typeof window !== 'undefined' ? window.location.origin : 'http://localhost';
    const url = new URL(rawUrl, base);
    url.searchParams.set('tab', tab);
    return url.pathname + url.search + url.hash;
  } catch {
    return rawUrl;
  }
};

