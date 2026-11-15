import test from 'node:test';
import assert from 'node:assert/strict';

import { useDate } from '../../resources/js/composables/useDate.js';
import { useTheme } from '../../resources/js/composables/useTheme.js';
import { useDebouncedFn } from '../../resources/js/composables/useDebouncedFn.js';
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

