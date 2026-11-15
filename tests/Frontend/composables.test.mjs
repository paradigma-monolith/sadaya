import test from 'node:test';
import assert from 'node:assert/strict';

import { useDate } from '../../resources/js/composables/useDate.js';
import { useTheme } from '../../resources/js/composables/useTheme.js';
import { useDebouncedFn } from '../../resources/js/composables/useDebouncedFn.js';
import { useFormDraft } from '../../resources/js/composables/useFormDraft.js';
import {
  formatDate as formatSanitasiDate,
  getRiskBadgeClass as getSanitasiRiskBadgeClass,
} from '../../resources/js/Pages/SanitasiLingkungan/utils.js';
import {
  formatDate as formatSiveraDate,
  getRiskBadgeClass as getSiveraRiskBadgeClass,
  getLokasiLalatTop5,
  getLokasiLalatTotalTop5,
  getLokasiLalatRata,
  getLokasiLalatStatus,
} from '../../resources/js/Pages/Sivera/utils.js';
import {
  normalizeDeg,
  getCompassSector,
  didSweepCrossBearing,
  computeRadarVectorMetrics,
  findMostRecentSweptBlip,
  resolveActiveRadarTarget,
} from '../../resources/js/Pages/Inertia/components/radarTelemetryUtils.js';
import {
  getIndonesianTimeGreeting,
  getUserFirstName,
  sanitizeDashboardViewMode,
  getDashboardModuleLabel,
} from '../../resources/js/Pages/Inertia/components/dashboardTelemetryUtils.js';

test('useDate formats valid dates and handles empty input', () => {
  const { formatDate, formatDateTime, fromNow } = useDate();
  assert.equal(formatDate(null), '-');
  assert.equal(formatDate(''), '-');
  assert.equal(formatDateTime(undefined), '-');
  assert.equal(fromNow(''), '-');

  const formatted = formatDate('2026-08-17');
  assert.match(formatted, /17\s+Agustus\s+2026/);
});

test('useDebouncedFn batches rapid invocations and supports cancel()', async () => {
  const calls = [];
  const fn = useDebouncedFn((val) => calls.push(val), 25);

  fn('first');
  fn('second');
  fn('third');
  assert.deepEqual(calls, []);

  await new Promise((r) => setTimeout(r, 60));
  assert.deepEqual(calls, ['third']);

  fn('cancelled');
  fn.cancel();
  await new Promise((r) => setTimeout(r, 60));
  assert.deepEqual(calls, ['third']);
});

test('SanitasiLingkungan utils format dates and risk badges deterministically', () => {
  assert.equal(formatSanitasiDate(''), '-');
  assert.equal(formatSanitasiDate('not-a-date'), 'not-a-date');
  assert.match(formatSanitasiDate('2026-05-01'), /2026/);

  assert.match(getSanitasiRiskBadgeClass('Rendah'), /emerald/);
  assert.match(getSanitasiRiskBadgeClass('Memenuhi Syarat'), /emerald/);
  assert.match(getSanitasiRiskBadgeClass('Sedang'), /amber/);
  assert.match(getSanitasiRiskBadgeClass('Tinggi'), /rose/);
  assert.match(getSanitasiRiskBadgeClass('Tidak Memenuhi Syarat'), /rose/);
  assert.match(getSanitasiRiskBadgeClass(''), /slate/);
});

test('Sivera utils compute top-5 Fly Grill index and baku mutu status accurately', () => {
  const sample = { t: [1, 4, 2, 0, 5, 3, 2, 1, 6, 0] };
  assert.deepEqual(getLokasiLalatTop5(sample), [6, 5, 4, 3, 2]);
  assert.equal(getLokasiLalatTotalTop5(sample), 20);
  assert.equal(getLokasiLalatRata(sample), 4);
  assert.equal(getLokasiLalatStatus(sample), 'Tidak Memenuhi Syarat');

  const lowDensity = { t: [1, 2, 1, 0, 1, 1, 0, 2, 1, 0] };
  assert.equal(getLokasiLalatRata(lowDensity), 1.4);
  assert.equal(getLokasiLalatStatus(lowDensity), 'Memenuhi Syarat');

  assert.equal(formatSiveraDate(null), '-');
  assert.match(getSiveraRiskBadgeClass('MS'), /emerald/);
  assert.match(getSiveraRiskBadgeClass('TMS'), /rose/);
});

test('useTheme toggles and sets explicit dark/light state safely in non-DOM runtimes', () => {
  const { isDark, toggleTheme, setTheme } = useTheme();
  setTheme('light');
  assert.equal(isDark.value, false);

  toggleTheme();
  assert.equal(isDark.value, true);

  setTheme('dark');
  assert.equal(isDark.value, true);

  setTheme('light');
  assert.equal(isDark.value, false);
});

test('useFormDraft saves, restores, expires stale entries, and clears corrupted drafts', () => {
  const store = new Map();
  const mockStorage = {
    getItem: (k) => (store.has(k) ? store.get(k) : null),
    setItem: (k, v) => store.set(k, v),
    removeItem: (k) => store.delete(k),
  };

  const draft = useFormDraft('test:draft', { storage: mockStorage, maxAgeMs: 5000 });
  assert.equal(draft.hasDraft(), false);
  assert.equal(draft.restore(), null);

  assert.equal(draft.save({ wilker: 'Cirebon', count: 4 }), true);
  assert.equal(draft.hasDraft(), true);
  assert.deepEqual(draft.restore(), { wilker: 'Cirebon', count: 4 });

  // Corrupted JSON clears cleanly without throwing
  mockStorage.setItem('test:draft', '{invalid-json');
  assert.equal(draft.restore(), null);
  assert.equal(draft.hasDraft(), false);

  // Expired draft clears automatically
  mockStorage.setItem('test:draft', JSON.stringify({ updatedAt: Date.now() - 10000, data: { stale: true } }));
  assert.equal(draft.restore(), null);
  assert.equal(draft.hasDraft(), false);

  draft.save({ fresh: true });
  draft.clear();
  assert.equal(draft.hasDraft(), false);
});

test('radarTelemetryUtils detects sweep crossings, compass sectors, and matches SINKARKES dossiers', () => {
  assert.equal(normalizeDeg(-15), 345);
  assert.equal(normalizeDeg(365), 5);
  assert.equal(getCompassSector(0), 'U');
  assert.equal(getCompassSector(46), 'TL');
  assert.equal(getCompassSector(90), 'T');
  assert.equal(getCompassSector(225), 'BD');
  assert.equal(getCompassSector(315), 'BL');

  // Sweep crossing including 358 -> 3 wrap-around and large-jump suppression
  assert.equal(didSweepCrossBearing(88, 92, 90), true);
  assert.equal(didSweepCrossBearing(92, 96, 90), false);
  assert.equal(didSweepCrossBearing(358, 3, 0), true);
  assert.equal(didSweepCrossBearing(358, 3, 2), true);
  assert.equal(didSweepCrossBearing(0, 210, 90), false);

  const markers = [
    { no: 1, shortCode: 'BDG', total: 20, is_hq: true },
    {
      no: 5,
      code: 'WLK-KJT-05',
      shortCode: 'KJT',
      shortTitle: 'Wilker Kertajati (BIJB)',
      moda: 'Udara Internasional',
      lat: -6.6561,
      lng: 108.1578,
      distanceKm: 66,
      bearingDeg: 66,
      total: 30,
      period: 18,
      aircraft: 22,
      ship: 0,
      alat: 5,
      obat: 3,
      approved: 28,
      pending: 2,
    },
  ];
  const sinkarkes = {
    matrix: [{ wilker: 'Wilker Kertajati', compliance_percent: 92, filled_months: 11 }],
    wilker_comparison: [{ name: 'Wilker Kertajati', short_name: 'Kertajati', total_alat_angkut: 145, total_orang: 9800 }],
  };

  const dossier = computeRadarVectorMetrics(markers[1], sinkarkes, 30, markers);
  assert.equal(dossier.sector, 'TL');
  assert.equal(dossier.bearingFormatted, '066°');
  assert.equal(dossier.loadSharePct, 60);
  assert.equal(dossier.relativeLoadPct, 100);
  assert.equal(dossier.linkLatencyMs, 23);
  assert.equal(dossier.signalStrengthPct, 90);
  assert.equal(dossier.sinkarkes.matched, true);
  assert.equal(dossier.sinkarkes.compliancePercent, 92);
  assert.equal(dossier.sinkarkes.filledMonths, 11);
  assert.equal(dossier.sinkarkes.alatAngkut, 145);
});

test('radarTelemetryUtils tracks active swept target by priority and handles wrap-around seeding', () => {
  const hq = { no: 1, shortCode: 'BDG', is_hq: true, bearingDeg: 0, distanceKm: 0 };
  const ptb = { no: 6, shortCode: 'PTB', is_hq: false, bearingDeg: 21, distanceKm: 80 };
  const cbn = { no: 3, shortCode: 'CBN', is_hq: false, bearingDeg: 80, distanceKm: 106 };
  const bdo = { no: 2, shortCode: 'BDO', is_hq: false, bearingDeg: 282, distanceKm: 4 };
  const blips = [hq, bdo, cbn, ptb];

  // Wrap-around seed at 0deg picks BDO (282deg, 78deg behind beam) over HQ
  assert.equal(findMostRecentSweptBlip(blips, 0)?.no, 2);
  assert.equal(findMostRecentSweptBlip(blips, 85)?.no, 3);
  assert.equal(findMostRecentSweptBlip([hq], 45), null);

  // Sweep mode automatically tracks lastInterceptedNo instead of staying stuck on HQ
  const swept = resolveActiveRadarTarget({
    radarBlips: blips,
    hoveredBlip: null,
    isManualLock: false,
    pinnedNodeNo: null,
    lastInterceptedNo: 3,
    fallbackNode: hq,
  });
  assert.equal(swept.mode, 'sweep');
  assert.equal(swept.target?.shortCode, 'CBN');

  // Hover overrides manual pin & sweep
  const hovered = resolveActiveRadarTarget({
    radarBlips: blips,
    hoveredBlip: ptb,
    isManualLock: true,
    pinnedNodeNo: 3,
    lastInterceptedNo: 2,
    fallbackNode: hq,
  });
  assert.equal(hovered.mode, 'hover');
  assert.equal(hovered.target?.shortCode, 'PTB');

  // Manual pin holds target until filtered out
  const pinned = resolveActiveRadarTarget({
    radarBlips: blips,
    isManualLock: true,
    pinnedNodeNo: 6,
    lastInterceptedNo: 2,
    fallbackNode: hq,
  });
  assert.equal(pinned.mode, 'pinned');
  assert.equal(pinned.target?.shortCode, 'PTB');

  // When pinned node is filtered out (e.g. mode filter), falls back cleanly to live sweep
  const filteredSweep = resolveActiveRadarTarget({
    radarBlips: [hq, cbn, bdo],
    isManualLock: true,
    pinnedNodeNo: 6,
    lastInterceptedNo: 3,
    fallbackNode: hq,
  });
  assert.equal(filteredSweep.mode, 'sweep');
  assert.equal(filteredSweep.target?.shortCode, 'CBN');
});

test('dashboardTelemetryUtils computes greetings, operator first name, view modes, and labels', () => {
  assert.equal(getIndonesianTimeGreeting(7), 'Selamat pagi');
  assert.equal(getIndonesianTimeGreeting(13), 'Selamat siang');
  assert.equal(getIndonesianTimeGreeting(16), 'Selamat sore');
  assert.equal(getIndonesianTimeGreeting(21), 'Selamat malam');
  assert.equal(getIndonesianTimeGreeting(2), 'Selamat malam');

  assert.equal(getUserFirstName('Dr. Budi Santoso'), 'Dr.');
  assert.equal(getUserFirstName('Rizky Pratama'), 'Rizky');
  assert.equal(getUserFirstName('   '), 'Petugas');
  assert.equal(getUserFirstName(null, 'Admin'), 'Admin');

  assert.equal(sanitizeDashboardViewMode('sinkarkes'), 'sinkarkes');
  assert.equal(sanitizeDashboardViewMode('geospatial'), 'geospatial');
  assert.equal(sanitizeDashboardViewMode('unknown-mode'), 'all');
  assert.equal(sanitizeDashboardViewMode(null), 'all');

  assert.equal(getDashboardModuleLabel('aircraft'), 'Sanitasi Pesawat');
  assert.equal(getDashboardModuleLabel('obat'), 'Obat P3K');
  assert.equal(getDashboardModuleLabel('all'), 'Semua Layanan');
});



