/**
 * Pure dashboard operator context, greeting, filter label, and view-mode helpers
 * shared across Dashboard.vue, LiveFeedSection.vue, and frontend unit tests.
 */

export const DASHBOARD_VIEW_MODE_STORAGE_KEY = 'sadaya:dashboard_view_mode:v1';

const ALLOWED_DASHBOARD_VIEW_MODES = ['all', 'sinkarkes', 'geospatial'];

const MODULE_LABELS = {
  aircraft: 'Sanitasi Pesawat',
  ship: 'Sanitasi Kapal',
  alat: 'Alkes P3K',
  obat: 'Obat P3K',
};

/**
 * Return an Indonesian time-of-day greeting for an hour in [0, 23].
 */
export const getIndonesianTimeGreeting = (hourInput = new Date().getHours()) => {
  const num = Number(hourInput);
  const hour = Number.isFinite(num) ? ((Math.floor(num) % 24) + 24) % 24 : 9;

  if (hour >= 4 && hour < 11) return 'Selamat pagi';
  if (hour >= 11 && hour < 15) return 'Selamat siang';
  if (hour >= 15 && hour < 18) return 'Selamat sore';
  return 'Selamat malam';
};

/**
 * Extract a concise first name from a user's full display name.
 */
export const getUserFirstName = (fullName = '', fallback = 'Petugas') => {
  const normalized = String(fullName || '').trim();
  if (!normalized) return fallback;
  return normalized.split(/\s+/)[0] || fallback;
};

/**
 * Validate and normalize persisted dashboard view-mode strings.
 */
export const sanitizeDashboardViewMode = (mode) => {
  return ALLOWED_DASHBOARD_VIEW_MODES.includes(mode) ? mode : 'all';
};

/**
 * Resolve a human-readable Indonesian label for a service module key.
 */
export const getDashboardModuleLabel = (moduleKey) => {
  return MODULE_LABELS[moduleKey] || 'Semua Layanan';
};

