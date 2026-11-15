/**
 * Pure tactical radar geometry, sweep-interception, and Wilker telemetry helpers
 * shared by GeospatialDeck.vue and frontend unit tests.
 */

const COMPASS_SECTORS = ['U', 'TL', 'T', 'TG', 'S', 'BD', 'B', 'BL'];

const WILKER_KEYWORDS = [
  ['bdg', 'induk', 'bandung'],
  ['bdo', 'husen', 'husein'],
  ['cbn', 'cirebon'],
  ['idm', 'indramayu'],
  ['kjt', 'kertajati', 'bijb'],
  ['ptb', 'patimban'],
  ['plr', 'pelabuharatu', 'pelabuhanratu'],
];

/**
 * Normalize an angle in degrees to [0, 360).
 */
export const normalizeDeg = (deg) => {
  const num = Number(deg);
  if (!Number.isFinite(num)) return 0;
  return ((num % 360) + 360) % 360;
};

/**
 * Convert a 0..359 degree clockwise azimuth into an 8-wind Indonesian compass sector.
 */
export const getCompassSector = (bearingDeg) => {
  const idx = Math.round(normalizeDeg(bearingDeg) / 45) % 8;
  return COMPASS_SECTORS[idx];
};

/**
 * Detect whether a clockwise sweep transition from prevDeg -> currDeg crossed targetDeg.
 * Ignores discontinuous jumps >= 180deg (e.g. initial paint or browser tab resume).
 */
export const didSweepCrossBearing = (prevDeg, currDeg, targetDeg) => {
  const p = normalizeDeg(prevDeg);
  const c = normalizeDeg(currDeg);
  const t = normalizeDeg(targetDeg);

  const delta = (c - p + 360) % 360;
  if (delta <= 0 || delta >= 180) return false;

  const offset = (t - p + 360) % 360;
  return offset > 0 && offset <= delta;
};

const resolveWilkerBucket = (text) => {
  const hay = String(text || '').toLowerCase();
  if (!hay) return -1;
  return WILKER_KEYWORDS.findIndex((keys) => keys.some((k) => hay.includes(k)));
};

const matchesTargetText = (target, candidateText) => {
  const hay = `${target?.shortCode || ''} ${target?.code || ''} ${target?.shortTitle || ''} ${target?.short_name || ''} ${target?.title || ''} ${target?.name || ''}`;
  const bTarget = resolveWilkerBucket(hay);
  const bCand = resolveWilkerBucket(candidateText);
  if (bTarget !== -1 && bCand !== -1) return bTarget === bCand;

  const shortA = String(target?.shortTitle || target?.short_name || '').toLowerCase().trim();
  const candB = String(candidateText || '').toLowerCase().trim();
  return Boolean(shortA && candB && (candB.includes(shortA) || shortA.includes(candB)));
};

/**
 * Build complex operational telemetry metrics for a locked/hovered radar node,
 * combining spatial polar physics, real inspection counts, and SINKARKES compliance.
 */
export const computeRadarVectorMetrics = (
  target,
  sinkarkes = {},
  maxOfficeTotal = 1,
  allMarkers = []
) => {
  if (!target) {
    return null;
  }

  const distanceKm = Number(target.distanceKm || 0);
  const bearingDeg = Math.round(normalizeDeg(target.bearingDeg || 0));
  const sector = getCompassSector(bearingDeg);
  const totalDocs = Number(target.total || 0);
  const periodDocs = Number(target.period || 0);
  const networkTotalDocs = (allMarkers || []).reduce((sum, m) => sum + Number(m?.total || 0), 0);

  const linkLatencyMs = target.is_hq ? 1 : Math.round(12 + distanceKm * 0.16);
  const signalStrengthPct = target.is_hq
    ? 100
    : Math.max(74, Math.min(99, Math.round(99 - distanceKm * 0.14)));

  const loadSharePct = networkTotalDocs > 0 ? Math.round((totalDocs / networkTotalDocs) * 100) : 0;
  const relativeLoadPct = Math.min(
    100,
    Math.round((totalDocs / Math.max(1, Number(maxOfficeTotal || 1))) * 100)
  );

  const matrix = Array.isArray(sinkarkes?.matrix) ? sinkarkes.matrix : [];
  const comparison = Array.isArray(sinkarkes?.wilker_comparison) ? sinkarkes.wilker_comparison : [];

  const matrixRow = matrix.find((m) => matchesTargetText(target, m?.wilker)) || null;
  const compRow =
    comparison.find((c) => matchesTargetText(target, `${c?.name || ''} ${c?.short_name || ''}`)) || null;

  return {
    distanceKm,
    bearingDeg,
    bearingFormatted: `${String(bearingDeg).padStart(3, '0')}°`,
    sector,
    lat: Number(target.lat || 0),
    lng: Number(target.lng || 0),
    totalDocs,
    periodDocs,
    aircraft: Number(target.aircraft || 0),
    ship: Number(target.ship || 0),
    alat: Number(target.alat || 0),
    obat: Number(target.obat || 0),
    approved: Number(target.approved || 0),
    pending: Number(target.pending || 0),
    linkLatencyMs,
    signalStrengthPct,
    loadSharePct,
    relativeLoadPct,
    sinkarkes: {
      matched: Boolean(matrixRow || compRow),
      compliancePercent: matrixRow ? Number(matrixRow.compliance_percent || 0) : null,
      filledMonths: matrixRow ? Number(matrixRow.filled_months || 0) : null,
      alatAngkut: compRow ? Number(compRow.total_alat_angkut || 0) : 0,
      orang: compRow ? Number(compRow.total_orang || 0) : 0,
      dokumen: compRow ? Number(compRow.total_dokumen || 0) : 0,
      layanan: compRow ? Number(compRow.total_layanan || 0) : 0,
    },
  };
};
