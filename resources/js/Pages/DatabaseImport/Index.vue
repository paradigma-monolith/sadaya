<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import {
  Database,
  Server,
  RefreshCw,
  CheckCircle2,
  XCircle,
  AlertTriangle,
  UploadCloud,
  Ship,
  Plane,
  Anchor,
  PackageCheck,
  Pill,
  Users,
  ArrowRight,
  Layers,
  FileSpreadsheet,
  Check,
  X,
  ExternalLink,
  ShieldCheck,
  Clock,
  Sparkles,
  SlidersHorizontal,
  Settings,
  Key,
  RotateCcw,
} from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
  modules: {
    type: Array,
    required: true,
  },
  sadaya_stats: {
    type: Object,
    required: true,
  },
  default_config: {
    type: Object,
    default: () => ({
      host: '127.0.0.1',
      port: 3306,
      username: 'root',
      password: 'mysql',
    }),
  },
  default_module_configs: {
    type: Object,
    default: () => ({}),
  },
  auto_sync_config: {
    type: Object,
    default: () => ({
      enabled: false,
      frequency: 'hourly',
      modules: [],
      last_run_at: null,
      last_status: null,
      last_imported_count: 0,
      last_duration: 0,
      last_message: null,
    }),
  },
  selected_module: {
    type: String,
    default: 'all',
  },
});

// Global / Default Credentials state
const credentials = ref({
  host: props.default_config.host || '127.0.0.1',
  port: props.default_config.port || 3306,
  username: props.default_config.username || 'root',
  password: props.default_config.password || 'mysql',
});

// Per-module configurations state (host, port, username, password, database, table)
const initialModuleConfigs = {};
props.modules.forEach((mod) => {
  const fromProps = props.default_module_configs?.[mod.id] || {};
  initialModuleConfigs[mod.id] = {
    host: fromProps.host || credentials.value.host,
    port: fromProps.port || credentials.value.port,
    username: fromProps.username !== undefined ? fromProps.username : credentials.value.username,
    password: fromProps.password !== undefined ? fromProps.password : credentials.value.password,
    database: fromProps.database || mod.source_db,
    table: fromProps.table || mod.source_table,
  };
});
const moduleConfigs = ref(initialModuleConfigs);

// Migration Options
const options = ref({
  skip_duplicate: true,
  recalculate_pnbp: true,
  normalize_ports: true,
  truncate_first: false,
});

// Testing connection state
const isTesting = ref(false);
const testResults = ref(null);

// Migration execution state
const isMigrating = ref(false);
const activeMigratingModule = ref(null);
const migrationSummary = ref(null);

// Per-module Config Modal State
const showConfigModal = ref(false);
const configTargetModule = ref(null);
const isTestingSingleModule = ref(false);
const singleModuleTestResult = ref(null);
const isSavingConfig = ref(false);

// File Upload Modal State
const showUploadModal = ref(false);
const uploadTargetModule = ref(null);
const uploadFile = ref(null);
const isUploading = ref(false);
const uploadResult = ref(null);

// Live local stats
const localSadayaStats = ref({ ...props.sadaya_stats });

// Auto-Sync Modal & Schedule State
const showAutoSyncModal = ref(false);
const autoSync = ref({
  enabled: props.auto_sync_config?.enabled || false,
  frequency: props.auto_sync_config?.frequency || 'hourly',
  modules: (props.auto_sync_config?.modules && props.auto_sync_config.modules.length > 0)
    ? [...props.auto_sync_config.modules]
    : props.modules.map((m) => m.id),
  last_run_at: props.auto_sync_config?.last_run_at || null,
  last_status: props.auto_sync_config?.last_status || null,
  last_imported_count: props.auto_sync_config?.last_imported_count || 0,
  last_duration: props.auto_sync_config?.last_duration || 0,
  last_message: props.auto_sync_config?.last_message || null,
});

const isSavingAutoSync = ref(false);
const isRunningSyncNow = ref(false);
const syncFeedback = ref(null);

const formatFrequency = (freq) => {
  switch (freq) {
    case 'every_15_minutes': return 'Setiap 15 Menit';
    case 'every_30_minutes': return 'Setiap 30 Menit';
    case 'hourly': return 'Setiap 1 Jam';
    case 'every_6_hours': return 'Setiap 6 Jam';
    case 'daily': return 'Setiap Hari (00:00 WIB)';
    default: return 'Berkala';
  }
};

const handleSaveAutoSync = async () => {
  isSavingAutoSync.value = true;
  syncFeedback.value = null;
  try {
    const res = await axios.post('/database-import/save-auto-sync', {
      enabled: autoSync.value.enabled,
      frequency: autoSync.value.frequency,
      modules: autoSync.value.modules,
    });
    if (res.data.auto_sync_config) {
      autoSync.value = { ...autoSync.value, ...res.data.auto_sync_config };
    }
    syncFeedback.value = {
      success: true,
      message: 'Jadwal sinkronisasi otomatis berhasil disimpan.',
    };
  } catch (err) {
    syncFeedback.value = {
      success: false,
      message: err.response?.data?.message || err.message || 'Gagal menyimpan pengaturan auto-sync.',
    };
  } finally {
    isSavingAutoSync.value = false;
  }
};

const handleRunSyncNow = async () => {
  isRunningSyncNow.value = true;
  syncFeedback.value = null;
  try {
    const res = await axios.post('/database-import/sync-now');
    if (res.data.auto_sync_config) {
      autoSync.value = { ...autoSync.value, ...res.data.auto_sync_config };
    }
    if (res.data.sadaya_current) {
      localSadayaStats.value = res.data.sadaya_current;
    }
    syncFeedback.value = {
      success: true,
      message: res.data.auto_sync_config?.last_message || 'Sinkronisasi berhasil dijalankan!',
    };
    router.reload({ only: ['sadaya_stats'] });
  } catch (err) {
    syncFeedback.value = {
      success: false,
      message: err.response?.data?.message || err.message || 'Gagal menjalankan sinkronisasi.',
    };
  } finally {
    isRunningSyncNow.value = false;
  }
};

const iconComponent = (iconName) => {
  switch (iconName) {
    case 'Ship': return Ship;
    case 'Plane': return Plane;
    case 'Anchor': return Anchor;
    case 'PackageCheck': return PackageCheck;
    case 'Pill': return Pill;
    case 'Users': return Users;
    default: return Database;
  }
};

const formatNumber = (val) => {
  if (val === undefined || val === null) return '0';
  return Number(val).toLocaleString('id-ID');
};

const isModuleCustomized = (moduleId) => {
  const m = moduleConfigs.value[moduleId];
  if (!m) return false;
  const modDef = props.modules.find(x => x.id === moduleId);
  if (!modDef) return false;
  return (
    m.host !== credentials.value.host ||
    m.port !== credentials.value.port ||
    m.username !== credentials.value.username ||
    m.password !== credentials.value.password ||
    m.database !== modDef.source_db ||
    m.table !== modDef.source_table
  );
};

// Apply top global credentials to all modules
const applyGlobalToAllModules = () => {
  props.modules.forEach((mod) => {
    if (!moduleConfigs.value[mod.id]) {
      moduleConfigs.value[mod.id] = {};
    }
    moduleConfigs.value[mod.id].host = credentials.value.host;
    moduleConfigs.value[mod.id].port = credentials.value.port;
    moduleConfigs.value[mod.id].username = credentials.value.username;
    moduleConfigs.value[mod.id].password = credentials.value.password;
  });
  alert('Kredensial host, port, username, dan password berhasil disalin ke seluruh 5 modul.');
};

// Reset single module configuration back to global & default
const resetModuleToDefault = async (moduleId) => {
  const modDef = props.modules.find(x => x.id === moduleId);
  if (!modDef) return;
  moduleConfigs.value[moduleId] = {
    host: credentials.value.host,
    port: credentials.value.port,
    username: credentials.value.username,
    password: credentials.value.password,
    database: modDef.source_db,
    table: modDef.source_table,
  };
  singleModuleTestResult.value = null;

  try {
    localStorage.setItem('sadaya_import_module_configs', JSON.stringify(moduleConfigs.value));
    await axios.post('/database-import/save-config', {
      modules: moduleConfigs.value,
    });
  } catch (e) {}
};

// Open Module Config Modal
const openConfigModal = (module) => {
  configTargetModule.value = module;
  singleModuleTestResult.value = testResults.value?.modules?.[module.id] || null;
  showConfigModal.value = true;
};

const closeConfigModal = () => {
  showConfigModal.value = false;
  configTargetModule.value = null;
  singleModuleTestResult.value = null;
};

// Save module configuration permanently to localStorage & backend database/storage
const saveModuleConfigAndClose = async () => {
  isSavingConfig.value = true;
  // 1. Simpan langsung ke browser localStorage
  try {
    localStorage.setItem('sadaya_import_module_configs', JSON.stringify(moduleConfigs.value));
  } catch (e) {
    console.warn('LocalStorage error:', e);
  }

  // 2. Simpan permanen ke storage server Sadaya
  try {
    await axios.post('/database-import/save-config', {
      modules: moduleConfigs.value,
    });
  } catch (err) {
    console.error('Gagal menyimpan konfigurasi ke server:', err);
  } finally {
    isSavingConfig.value = false;
    closeConfigModal();
  }
};

// Test single module connection
const handleTestSingleModule = async (moduleId) => {
  isTestingSingleModule.value = true;
  singleModuleTestResult.value = null;
  try {
    const payload = {
      target_module: moduleId,
      ...credentials.value,
      modules: moduleConfigs.value,
    };
    const res = await axios.post('/database-import/test-connection', payload);
    const modResult = res.data.modules?.[moduleId];
    singleModuleTestResult.value = modResult;
    if (!testResults.value) {
      testResults.value = { success: true, modules: {} };
    }
    if (!testResults.value.modules) {
      testResults.value.modules = {};
    }
    testResults.value.modules[moduleId] = modResult;
    if (res.data.sadaya_current) {
      localSadayaStats.value = res.data.sadaya_current;
    }
  } catch (err) {
    singleModuleTestResult.value = {
      connected: false,
      table_exists: false,
      count: 0,
      error: err.response?.data?.message || err.message || 'Gagal menghubungi database modul.',
    };
  } finally {
    isTestingSingleModule.value = false;
  }
};

const handleTestConnection = async () => {
  isTesting.value = true;
  testResults.value = null;
  migrationSummary.value = null;
  try {
    const payload = {
      ...credentials.value,
      modules: moduleConfigs.value,
    };
    const res = await axios.post('/database-import/test-connection', payload);
    testResults.value = res.data;
    if (res.data.sadaya_current) {
      localSadayaStats.value = res.data.sadaya_current;
    }
  } catch (err) {
    testResults.value = {
      success: false,
      message: err.response?.data?.message || err.message || 'Gagal menghubungi MySQL.',
      modules: {},
    };
  } finally {
    isTesting.value = false;
  }
};

const handleMigrate = async (moduleId) => {
  if (options.value.truncate_first) {
    const targetName = moduleId === 'all' ? 'SELURUH data dari semua modul' : `data modul ${moduleId}`;
    if (!confirm(`PERINGATAN: Anda memilih untuk mengosongkan ${targetName} di Sadaya sebelum impor. Lanjutkan?`)) {
      return;
    }
  }

  isMigrating.value = true;
  activeMigratingModule.value = moduleId;
  migrationSummary.value = null;

  try {
    const payload = {
      module: moduleId,
      ...credentials.value,
      ...options.value,
      modules: moduleConfigs.value,
      ...(moduleId !== 'all' ? (moduleConfigs.value[moduleId] || {}) : {}),
    };
    const res = await axios.post('/database-import/migrate', payload);
    migrationSummary.value = res.data;
    if (res.data.sadaya_current) {
      localSadayaStats.value = res.data.sadaya_current;
    }
    // Refresh Inertia props in background
    router.reload({ only: ['sadaya_stats'] });
  } catch (err) {
    migrationSummary.value = {
      success: false,
      message: err.response?.data?.message || err.message || 'Terjadi kesalahan saat proses migrasi.',
    };
  } finally {
    isMigrating.value = false;
    activeMigratingModule.value = null;
  }
};

const openUploadModal = (module) => {
  uploadTargetModule.value = module;
  uploadFile.value = null;
  uploadResult.value = null;
  showUploadModal.value = true;
};

const closeUploadModal = () => {
  showUploadModal.value = false;
  uploadTargetModule.value = null;
  uploadFile.value = null;
};

const handleFileSelect = (e) => {
  const file = e.target.files[0];
  if (file) {
    uploadFile.value = file;
  }
};

const handleExecuteUpload = async () => {
  if (!uploadFile.value || !uploadTargetModule.value) {
    alert('Pilih file backup terlebih dahulu.');
    return;
  }

  isUploading.value = true;
  uploadResult.value = null;

  try {
    const formData = new FormData();
    formData.append('module', uploadTargetModule.value.id);
    formData.append('file', uploadFile.value);
    formData.append('skip_duplicate', options.value.skip_duplicate ? '1' : '0');
    formData.append('truncate_first', options.value.truncate_first ? '1' : '0');

    const res = await axios.post('/database-import/import-file', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    uploadResult.value = res.data;
    if (res.data.sadaya_current) {
      localSadayaStats.value = res.data.sadaya_current;
    }
    router.reload({ only: ['sadaya_stats'] });
  } catch (err) {
    uploadResult.value = {
      success: false,
      message: err.response?.data?.message || err.message || 'Gagal memproses file.',
    };
  } finally {
    isUploading.value = false;
  }
};

onMounted(() => {
  // Pulihkan konfigurasi kustom dari browser localStorage jika ada
  try {
    const saved = localStorage.getItem('sadaya_import_module_configs');
    if (saved) {
      const parsed = JSON.parse(saved);
      if (parsed && typeof parsed === 'object') {
        Object.keys(parsed).forEach((k) => {
          if (moduleConfigs.value[k] && parsed[k]) {
            moduleConfigs.value[k] = {
              ...moduleConfigs.value[k],
              ...parsed[k],
            };
          }
        });
      }
    }
  } catch (e) {
    console.warn('Error reading stored module configs:', e);
  }

  // Test connection on initial load
  handleTestConnection();
});
</script>

<template>
  <Head title="Pusat Migrasi & Import Database • BKK Bandung" />

  <HybridNavbar mode="inertia" currentRoute="database-import">
    <main class="w-full px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

      <!-- ==================== HEADER BAR ==================== -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-semibold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 mb-2">
            <Database class="w-3.5 h-3.5" />
            <span>Pusat Satu Pintu Migrasi Data • Simply (CI4) &rarr; Sadaya (Laravel/Vue 3)</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
            Pusat Migrasi &amp; Import Database
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-3xl leading-relaxed">
            Pindahkan dan sinkronkan seluruh basis data operasional (Pengawasan Kedatangan Kapal TANGKAP, Travel Consent Pesawat Udara, Sanitasi Kapal, Alkes &amp; Obat Medis Haji) secara langsung dari MySQL Simply maupun berkas backup.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <button
            type="button"
            @click="showAutoSyncModal = true"
            class="px-4 py-2.5 rounded-xl border border-indigo-200 dark:border-indigo-800/80 bg-indigo-50/70 dark:bg-indigo-950/40 hover:bg-indigo-100/80 dark:hover:bg-indigo-900/50 text-xs font-bold text-indigo-700 dark:text-indigo-300 transition shadow-2xs flex items-center gap-2 cursor-pointer"
          >
            <Clock class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" />
            <span>Sinkronisasi Otomatis</span>
            <span
              class="w-2 h-2 rounded-full"
              :class="autoSync.enabled ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300 dark:bg-slate-600'"
              :title="autoSync.enabled ? 'Status: Aktif' : 'Status: Nonaktif'"
            />
          </button>

          <button
            type="button"
            @click="handleTestConnection"
            :disabled="isTesting"
            class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 transition shadow-xs flex items-center gap-2 cursor-pointer"
          >
            <RefreshCw class="w-3.5 h-3.5 text-slate-500" :class="{ 'animate-spin': isTesting }" />
            <span>{{ isTesting ? 'Memeriksa...' : 'Uji Semua Koneksi' }}</span>
          </button>

          <button
            type="button"
            @click="handleMigrate('all')"
            :disabled="isMigrating"
            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:opacity-95 text-xs font-bold text-white transition shadow-md flex items-center gap-2 cursor-pointer disabled:opacity-50"
          >
            <Sparkles class="w-4 h-4 text-indigo-200" :class="{ 'animate-spin': isMigrating && activeMigratingModule === 'all' }" />
            <span>{{ isMigrating && activeMigratingModule === 'all' ? 'Memigrasikan Seluruh Data...' : 'Migrasikan Semua Data (One-Click)' }}</span>
          </button>
        </div>
      </div>

      <!-- ==================== MIGRATION OPTIONS BAR ==================== -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs">
        <div class="flex flex-wrap items-center gap-x-6 gap-y-3 text-xs">
          <label class="flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer">
            <input
              type="checkbox"
              v-model="options.skip_duplicate"
              class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4"
            />
            <span class="font-medium">Lewati Duplikat (Skip Duplicate)</span>
          </label>

          <label class="flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer">
            <input
              type="checkbox"
              v-model="options.recalculate_pnbp"
              class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4"
            />
            <span class="font-medium">Kalkulasi Otomatis PNBP Sesuai Regulasi Tarif</span>
          </label>

          <label class="flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer">
            <input
              type="checkbox"
              v-model="options.normalize_ports"
              class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4"
            />
            <span class="font-medium">Normalisasi Format Nama Wilker / Pelabuhan</span>
          </label>

          <label class="flex items-center gap-2 text-rose-600 dark:text-rose-400 font-bold cursor-pointer">
            <input
              type="checkbox"
              v-model="options.truncate_first"
              class="rounded border-rose-300 text-rose-600 focus:ring-rose-500 w-4 h-4"
            />
            <span>Kosongkan Tabel Sadaya Sebelum Impor (Truncate)</span>
          </label>
        </div>

        <!-- Banner Auto-Sync Aktif -->
        <div v-if="autoSync.enabled" class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-500 dark:text-slate-400">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold text-[11px] border border-emerald-200 dark:border-emerald-800">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" />
              Auto-Sync Aktif ({{ formatFrequency(autoSync.frequency) }})
            </span>
            <span v-if="autoSync.last_run_at" class="text-[11px]">
              Terakhir berjalan: <strong class="text-slate-700 dark:text-slate-200 font-mono">{{ autoSync.last_run_at }}</strong> ({{ autoSync.last_imported_count }} data baru diimpor)
            </span>
            <span v-else class="text-[11px]">
              Menunggu jadwal eksekusi berikutnya via Laravel Scheduler.
            </span>
          </div>
          <button
            type="button"
            @click="showAutoSyncModal = true"
            class="text-[11px] text-indigo-600 dark:text-indigo-400 font-bold hover:underline cursor-pointer"
          >
            Kelola Jadwal &rarr;
          </button>
        </div>
      </div>

      <!-- ==================== MIGRATION SUMMARY NOTIFICATION ==================== -->
      <div v-if="migrationSummary" class="transition-all duration-300">
        <div
          v-if="migrationSummary.success"
          class="p-5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 space-y-3 shadow-xs"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 font-bold text-sm text-indigo-900 dark:text-indigo-200">
              <CheckCircle2 class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
              <span>{{ migrationSummary.message || 'Proses Migrasi Berhasil Diselesaikan!' }}</span>
            </div>
            <span class="text-xs font-mono font-semibold px-2.5 py-1 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300">
              Waktu: {{ migrationSummary.duration }} detik
            </span>
          </div>

          <!-- If ALL modules were migrated -->
          <div v-if="migrationSummary.details" class="overflow-x-auto pt-2">
            <table class="w-full text-left text-xs bg-white dark:bg-slate-900 rounded-xl overflow-hidden border border-indigo-100 dark:border-indigo-900/50">
              <thead class="bg-indigo-100/60 dark:bg-indigo-900/40 text-slate-700 dark:text-slate-300 text-[11px] uppercase tracking-wider font-semibold">
                <tr>
                  <th class="p-3">Modul</th>
                  <th class="p-3 text-center">Total Diproses</th>
                  <th class="p-3 text-center">Berhasil Diimpor</th>
                  <th class="p-3 text-center">Dilewati (Duplikat)</th>
                  <th class="p-3 text-center">Gagal</th>
                  <th class="p-3 text-right">Durasi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="(det, k) in migrationSummary.details" :key="k">
                  <td class="p-3 font-semibold text-slate-900 dark:text-white">{{ det.module_name }}</td>
                  <td class="p-3 text-center font-mono">{{ formatNumber(det.total_processed) }}</td>
                  <td class="p-3 text-center font-bold text-emerald-600 font-mono">{{ formatNumber(det.imported) }}</td>
                  <td class="p-3 text-center text-amber-600 font-mono">{{ formatNumber(det.skipped) }}</td>
                  <td class="p-3 text-center text-rose-600 font-mono">{{ formatNumber(det.failed) }}</td>
                  <td class="p-3 text-right font-mono text-slate-500">{{ det.duration }}s</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- If single module was migrated -->
          <div v-else class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 text-xs">
            <div class="bg-white dark:bg-slate-900 p-3 rounded-xl border border-indigo-100 dark:border-indigo-900/40">
              <span class="text-[10px] text-slate-400 block">Total Diproses</span>
              <span class="text-base font-bold text-slate-900 dark:text-white">{{ formatNumber(migrationSummary.total_processed) }}</span>
            </div>
            <div class="bg-white dark:bg-slate-900 p-3 rounded-xl border border-indigo-100 dark:border-indigo-900/40">
              <span class="text-[10px] text-slate-400 block">Berhasil Diimpor</span>
              <span class="text-base font-bold text-emerald-600">{{ formatNumber(migrationSummary.imported) }}</span>
            </div>
            <div class="bg-white dark:bg-slate-900 p-3 rounded-xl border border-indigo-100 dark:border-indigo-900/40">
              <span class="text-[10px] text-slate-400 block">Dilewati (Duplikat)</span>
              <span class="text-base font-bold text-amber-600">{{ formatNumber(migrationSummary.skipped) }}</span>
            </div>
            <div class="bg-white dark:bg-slate-900 p-3 rounded-xl border border-indigo-100 dark:border-indigo-900/40">
              <span class="text-[10px] text-slate-400 block">Gagal</span>
              <span class="text-base font-bold text-rose-600">{{ formatNumber(migrationSummary.failed) }}</span>
            </div>
          </div>
        </div>

        <div
          v-else
          class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-800 dark:text-rose-200 flex items-start gap-2.5"
        >
          <XCircle class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" />
          <div>
            <div class="font-bold text-sm">Proses Migrasi Gagal</div>
            <p class="mt-0.5 text-xs">{{ migrationSummary.message }}</p>
          </div>
        </div>
      </div>

      <!-- ==================== MODULE MIGRATION TABLE ==================== -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
              <Layers class="w-4 h-4" />
            </div>
            <div>
              <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                Daftar Modul Operasional untuk Migrasi
              </h2>
              <p class="text-[11px] text-slate-400">
                Pilih modul spesifik untuk migrasi langsung dari MySQL Simply atau unggah berkas backup
              </p>
            </div>
          </div>
          <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
            5 Modul Terintegrasi
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 text-[11px] uppercase tracking-wider font-semibold border-b border-slate-100 dark:border-slate-800">
              <tr>
                <th class="py-3 px-5">Modul Operasional</th>
                <th class="py-3 px-4">Koneksi &amp; Tabel Sumber Simply</th>
                <th class="py-3 px-4">Tujuan Sadaya</th>
                <th class="py-3 px-4 text-center">Data di Sadaya</th>
                <th class="py-3 px-4 text-center">Status</th>
                <th class="py-3 px-5 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr
                v-for="mod in modules"
                :key="mod.id"
                class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors"
              >
                <!-- Modul Info -->
                <td class="py-3.5 px-5">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-2xl flex items-center justify-center shrink-0" :class="[mod.bg_color, mod.color]">
                      <component :is="iconComponent(mod.icon)" class="w-4 h-4" />
                    </div>
                    <div class="min-w-0">
                      <div class="font-bold text-slate-900 dark:text-white text-xs">
                        {{ mod.name }}
                      </div>
                      <div class="text-[11px] text-slate-400 line-clamp-1 max-w-md">
                        {{ mod.description }}
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Sumber Simply -->
                <td class="py-3.5 px-4 font-mono text-[11px]">
                  <div class="space-y-1">
                    <div class="flex items-center gap-1.5 flex-wrap">
                      <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 inline-block font-semibold">
                        {{ moduleConfigs[mod.id]?.database || mod.source_db }}.{{ moduleConfigs[mod.id]?.table || mod.source_table }}
                      </span>
                      <span v-if="isModuleCustomized(mod.id)" class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                        Kustom
                      </span>
                    </div>
                    <div class="text-[10px] text-slate-400 font-mono">
                      {{ moduleConfigs[mod.id]?.host || credentials.host }}:{{ moduleConfigs[mod.id]?.port || credentials.port }} &bull; user: {{ moduleConfigs[mod.id]?.username || credentials.username }}
                    </div>
                  </div>
                </td>

                <!-- Tujuan Sadaya -->
                <td class="py-3.5 px-4 font-mono text-[11px]">
                  <span class="text-indigo-600 dark:text-indigo-400 font-semibold">
                    {{ mod.target_table }}
                  </span>
                </td>

                <!-- Data di Sadaya -->
                <td class="py-3.5 px-4 text-center font-mono">
                  <span class="font-extrabold text-slate-900 dark:text-white text-sm">
                    {{ formatNumber(localSadayaStats[mod.id] || 0) }}
                  </span>
                  <span class="text-[10px] text-slate-400 block font-sans">rekaman</span>
                </td>

                <!-- Status di Simply -->
                <td class="py-3.5 px-4 text-center">
                  <div v-if="testResults && testResults.modules && testResults.modules[mod.id]">
                    <div v-if="testResults.modules[mod.id].connected && testResults.modules[mod.id].table_exists">
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 font-mono">
                        <Check class="w-3 h-3 text-emerald-600" />
                        {{ formatNumber(testResults.modules[mod.id].count) }} data
                      </span>
                      <span v-if="testResults.modules[mod.id].min_date" class="text-[10px] text-slate-400 block mt-0.5 font-mono">
                        {{ testResults.modules[mod.id].min_date }} - {{ testResults.modules[mod.id].max_date }}
                      </span>
                    </div>
                    <span
                      v-else
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-medium bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700"
                      :title="testResults.modules[mod.id].error"
                    >
                      <X class="w-3 h-3 text-slate-400" />
                      Belum Ada
                    </span>
                  </div>
                  <span v-else class="text-[11px] text-slate-400 font-mono">-</span>
                </td>

                <!-- Actions -->
                <td class="py-3.5 px-5 text-right">
                  <div class="inline-flex items-center gap-1.5 justify-end">
                    <button
                      type="button"
                      @click="openConfigModal(mod)"
                      class="px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition shadow-2xs flex items-center gap-1.5 cursor-pointer"
                      title="Atur Host, Port, Database, Tabel, dan Kredensial khusus modul ini"
                    >
                      <SlidersHorizontal class="w-3.5 h-3.5 text-indigo-500" />
                      <span class="hidden sm:inline">Atur DB</span>
                    </button>

                    <button
                      type="button"
                      @click="handleMigrate(mod.id)"
                      :disabled="isMigrating"
                      class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                      title="Migrasi langsung dari MySQL Simply"
                    >
                      <RefreshCw class="w-3 h-3" :class="{ 'animate-spin': isMigrating && activeMigratingModule === mod.id }" />
                      <span>{{ isMigrating && activeMigratingModule === mod.id ? 'Memigrasikan...' : 'Migrasi MySQL' }}</span>
                    </button>

                    <button
                      type="button"
                      @click="openUploadModal(mod)"
                      title="Import dari berkas backup (.sql, .json, .csv)"
                      class="p-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition cursor-pointer"
                    >
                      <UploadCloud class="w-4 h-4 text-slate-500" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>

    <!-- ==================== MODAL UPLOAD FILE BACKUP ==================== -->
    <div
      v-if="showUploadModal && uploadTargetModule"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
    >
      <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col my-8">
        
        <!-- Header -->
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl flex items-center justify-center" :class="[uploadTargetModule.bg_color, uploadTargetModule.color]">
              <UploadCloud class="w-5 h-5" />
            </div>
            <div>
              <h3 class="font-bold text-sm text-slate-900 dark:text-white">
                Import Berkas Backup: {{ uploadTargetModule.name }}
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Upload berkas dump .sql, .json, atau .csv ke tabel {{ uploadTargetModule.target_table }}
              </p>
            </div>
          </div>
          <button
            type="button"
            @click="closeUploadModal"
            class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Body -->
        <div class="p-5 space-y-4">
          <div class="p-6 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl text-center space-y-2 bg-slate-50 dark:bg-slate-800/30">
            <UploadCloud class="w-8 h-8 text-indigo-500 mx-auto" />
            <div class="text-xs font-semibold text-slate-700 dark:text-slate-200">
              Pilih file backup database Simply
            </div>
            <p class="text-[11px] text-slate-400">
              Format didukung: .sql (Dump INSERT), .json, atau .csv (Maks. 50MB)
            </p>
            <input
              type="file"
              accept=".sql,.json,.csv"
              @change="handleFileSelect"
              class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer pt-2"
            />
            <div v-if="uploadFile" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 pt-1">
              File: {{ uploadFile.name }} ({{ Math.round(uploadFile.size / 1024) }} KB)
            </div>
          </div>

          <div v-if="uploadResult">
            <div
              v-if="uploadResult.success"
              class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-300 space-y-1"
            >
              <div class="font-bold flex items-center gap-1.5">
                <CheckCircle2 class="w-4 h-4 text-emerald-600" />
                <span>Import File Berhasil!</span>
              </div>
              <p class="text-[11px]">
                {{ uploadResult.imported }} data baru diimpor, {{ uploadResult.skipped }} dilewati, durasi {{ uploadResult.duration }}s.
              </p>
            </div>
            <div
              v-else
              class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-800 dark:text-rose-200 flex items-start gap-2"
            >
              <XCircle class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" />
              <div>
                <div class="font-bold">Import Gagal</div>
                <p class="text-[11px] mt-0.5">{{ uploadResult.message }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-800/40">
          <button
            type="button"
            @click="closeUploadModal"
            class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition cursor-pointer"
          >
            Tutup
          </button>
          <button
            type="button"
            @click="handleExecuteUpload"
            :disabled="isUploading || !uploadFile"
            class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
          >
            <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isUploading }" />
            <span>{{ isUploading ? 'Memproses Berkas...' : 'Mulai Import File' }}</span>
          </button>
        </div>

      </div>
    </div>

    <!-- ==================== MODAL KONFIGURASI DATABASE PER MODUL ==================== -->
    <div
      v-if="showConfigModal && configTargetModule"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
    >
      <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-xl w-full shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col my-8">
        
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl flex items-center justify-center" :class="[configTargetModule.bg_color, configTargetModule.color]">
              <component :is="iconComponent(configTargetModule.icon)" class="w-5 h-5" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white">
                  Konfigurasi Basis Data: {{ configTargetModule.name }}
                </h3>
                <span v-if="isModuleCustomized(configTargetModule.id)" class="text-[9px] px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 font-bold border border-indigo-200 dark:border-indigo-800">
                  Kustom
                </span>
              </div>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Atur Host, Port, Database, Tabel, dan Kredensial khusus untuk modul ini
              </p>
            </div>
          </div>
          <button
            type="button"
            @click="closeConfigModal"
            class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Body: Inputs -->
        <div class="p-5 space-y-4">
          <div class="grid grid-cols-2 gap-3 text-xs">
            <div>
              <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Host MySQL</label>
              <input
                type="text"
                v-model="moduleConfigs[configTargetModule.id].host"
                placeholder="127.0.0.1"
                class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Port</label>
              <input
                type="number"
                v-model="moduleConfigs[configTargetModule.id].port"
                placeholder="3306"
                class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Database Sumber</label>
              <input
                type="text"
                v-model="moduleConfigs[configTargetModule.id].database"
                placeholder="bkkr3927_simply"
                class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Tabel Sumber</label>
              <input
                type="text"
                v-model="moduleConfigs[configTargetModule.id].table"
                placeholder="tc_sanitasi_kapal"
                class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Username MySQL</label>
              <input
                type="text"
                v-model="moduleConfigs[configTargetModule.id].username"
                placeholder="root"
                class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Password MySQL</label>
              <input
                type="password"
                v-model="moduleConfigs[configTargetModule.id].password"
                placeholder="••••••••"
                class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              />
            </div>
          </div>

          <!-- Single Module Test Connection Box -->
          <div class="pt-2">
            <div class="flex items-center justify-between pb-2">
              <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Status Pemeriksaan Koneksi Modul</span>
              <button
                type="button"
                @click="handleTestSingleModule(configTargetModule.id)"
                :disabled="isTestingSingleModule"
                class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
              >
                <RefreshCw class="w-3 h-3 text-indigo-500" :class="{ 'animate-spin': isTestingSingleModule }" />
                <span>{{ isTestingSingleModule ? 'Menguji...' : 'Uji Koneksi Modul Ini' }}</span>
              </button>
            </div>

            <div v-if="singleModuleTestResult" class="p-3.5 rounded-xl text-xs border" :class="singleModuleTestResult.connected && singleModuleTestResult.table_exists ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 text-emerald-800 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-950/40 border-rose-200 text-rose-800 dark:text-rose-300'">
              <div class="flex items-center gap-2 font-bold">
                <CheckCircle2 v-if="singleModuleTestResult.connected && singleModuleTestResult.table_exists" class="w-4 h-4 text-emerald-600" />
                <XCircle v-else class="w-4 h-4 text-rose-600" />
                <span>{{ singleModuleTestResult.connected && singleModuleTestResult.table_exists ? 'Terkoneksi & Siap Dimigrasikan' : 'Koneksi atau Tabel Bermasalah' }}</span>
              </div>
              <div class="text-[11px] mt-1 space-y-0.5 font-mono">
                <div v-if="singleModuleTestResult.connected && singleModuleTestResult.table_exists">
                  Ditemukan: <strong>{{ formatNumber(singleModuleTestResult.count) }}</strong> baris data pada <code class="bg-black/5 dark:bg-white/10 px-1 rounded">{{ singleModuleTestResult.database }}.{{ singleModuleTestResult.table }}</code>.
                  <span v-if="singleModuleTestResult.min_date" class="block font-sans text-slate-500">Rentang tanggal: {{ singleModuleTestResult.min_date }} s/d {{ singleModuleTestResult.max_date }}</span>
                </div>
                <div v-else class="text-rose-700 dark:text-rose-400 font-sans">
                  {{ singleModuleTestResult.error || 'Tabel atau basis data tidak ditemukan.' }}
                </div>
              </div>
            </div>
            <div v-else class="text-[11px] text-slate-400 italic bg-slate-50 dark:bg-slate-800/40 p-2.5 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
              Klik "Uji Koneksi Modul Ini" untuk memverifikasi apakah host, database, dan tabel valid.
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-800/40">
          <button
            type="button"
            @click="resetModuleToDefault(configTargetModule.id)"
            class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition cursor-pointer flex items-center gap-1.5"
            title="Reset modul ini ke nilai bawaan global"
          >
            <RotateCcw class="w-3.5 h-3.5 text-slate-400" />
            <span>Reset Bawaan</span>
          </button>
          <button
            type="button"
            @click="saveModuleConfigAndClose"
            :disabled="isSavingConfig"
            class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs disabled:opacity-50"
          >
            <RefreshCw v-if="isSavingConfig" class="w-4 h-4 animate-spin" />
            <Check v-else class="w-4 h-4" />
            <span>{{ isSavingConfig ? 'Menyimpan...' : 'Simpan &amp; Tutup' }}</span>
          </button>
        </div>

      </div>
    </div>

    <!-- ==================== MODAL PENGATURAN SINKRONISASI OTOMATIS ==================== -->
    <div
      v-if="showAutoSyncModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
    >
      <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-xl w-full shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col my-8">
        
        <!-- Header -->
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
              <Clock class="w-5 h-5" />
            </div>
            <div>
              <h3 class="font-bold text-sm text-slate-900 dark:text-white">
                Pengaturan Sinkronisasi Otomatis
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Jadwalkan penarikan data berkala Simply &rarr; Sadaya secara otomatis
              </p>
            </div>
          </div>
          <button
            type="button"
            @click="showAutoSyncModal = false"
            class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Body -->
        <div class="p-5 space-y-4 text-xs max-h-[75vh] overflow-y-auto">
          
          <!-- Toggle Aktif/Nonaktif -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
              <div class="font-bold text-slate-900 dark:text-white text-xs flex items-center gap-2">
                <span>Status Sinkronisasi Otomatis</span>
                <span
                  class="px-2 py-0.5 rounded-md text-[10px] font-bold"
                  :class="autoSync.enabled ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300'"
                >
                  {{ autoSync.enabled ? 'AKTIF' : 'NONAKTIF' }}
                </span>
              </div>
              <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                {{ autoSync.enabled ? 'Data baru akan ditarik otomatis sesuai jadwal.' : 'Sinkronisasi hanya berjalan ketika tombol ditekan manual.' }}
              </p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="autoSync.enabled" class="sr-only peer" />
              <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
          </div>

          <!-- Interval Frekuensi -->
          <div>
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
              Interval Frekuensi Sinkronisasi
            </label>
            <select
              v-model="autoSync.frequency"
              class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            >
              <option value="every_15_minutes">Setiap 15 Menit</option>
              <option value="every_30_minutes">Setiap 30 Menit</option>
              <option value="hourly">Setiap 1 Jam (Rekomendasi Standar)</option>
              <option value="every_6_hours">Setiap 6 Jam</option>
              <option value="daily">Setiap Hari (Pukul 00:00 WIB)</option>
            </select>
            <span class="text-[10px] text-slate-400 block mt-1">
              Data ditarik secara cerdas dengan filter <code>skip_duplicate</code> sehingga tidak membebani server dan tidak menduplikat rekaman.
            </span>
          </div>

          <!-- Pilihan Modul -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                Pilih Modul yang Disinkronkan
              </label>
              <span class="text-[10px] text-slate-400">
                {{ autoSync.modules.length }} dari {{ modules.length }} modul dipilih
              </span>
            </div>
            <div class="space-y-1.5 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
              <label
                v-for="mod in modules"
                :key="mod.id"
                class="flex items-center gap-2.5 text-xs text-slate-700 dark:text-slate-300 cursor-pointer p-1.5 rounded-lg hover:bg-white dark:hover:bg-slate-800 transition"
              >
                <input
                  type="checkbox"
                  :value="mod.id"
                  v-model="autoSync.modules"
                  class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4"
                />
                <span class="font-medium text-slate-900 dark:text-white">{{ mod.name }}</span>
                <span class="text-[10px] text-slate-400 font-mono ml-auto">
                  {{ moduleConfigs[mod.id]?.database || mod.source_db }}.{{ moduleConfigs[mod.id]?.table || mod.source_table }}
                </span>
              </label>
            </div>
          </div>

          <!-- Status & Riwayat Eksekusi Terakhir -->
          <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 space-y-1.5">
            <div class="flex items-center justify-between text-[11px]">
              <span class="font-bold text-slate-600 dark:text-slate-300">Eksekusi Terakhir:</span>
              <span class="font-mono text-slate-900 dark:text-white font-semibold">
                {{ autoSync.last_run_at || 'Belum pernah dieksekusi' }}
              </span>
            </div>
            <div v-if="autoSync.last_status" class="flex items-center justify-between text-[11px]">
              <span class="font-bold text-slate-600 dark:text-slate-300">Status &amp; Data:</span>
              <span
                class="px-2 py-0.5 rounded text-[10px] font-bold"
                :class="autoSync.last_status === 'success' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400'"
              >
                {{ autoSync.last_status === 'success' ? `Sukses (${autoSync.last_imported_count} data baru)` : 'Ada Catatan' }}
              </span>
            </div>
            <p v-if="autoSync.last_message" class="text-[11px] text-slate-500 dark:text-slate-400 pt-1 border-t border-slate-200/60 dark:border-slate-700/60 leading-relaxed">
              {{ autoSync.last_message }}
            </p>
          </div>

          <!-- Notifikasi Feedback -->
          <div
            v-if="syncFeedback"
            class="p-3 rounded-xl text-xs border"
            :class="syncFeedback.success ? 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300'"
          >
            {{ syncFeedback.message }}
          </div>

          <!-- Panduan Cron Scheduler di Server -->
          <div class="p-3 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 text-[11px] space-y-1.5">
            <span class="font-bold text-indigo-900 dark:text-indigo-300 flex items-center gap-1.5">
              <Sparkles class="w-3.5 h-3.5" />
              Satu Baris Cron Scheduler Server (cPanel / Linux):
            </span>
            <code class="block font-mono text-[10px] p-2 bg-white dark:bg-slate-900 rounded-lg border border-indigo-100 dark:border-indigo-900 text-slate-700 dark:text-slate-300 overflow-x-auto">
              * * * * * cd /path/to/sadaya && php artisan schedule:run >> /dev/null 2>&1
            </code>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight">
              Jika scheduler server sudah aktif, sinkronisasi berjalan otomatis di latar belakang tepat sesuai interval waktu yang Anda pilih di atas.
            </p>
          </div>

        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-800/40">
          <button
            type="button"
            @click="handleRunSyncNow"
            :disabled="isRunningSyncNow"
            class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 transition cursor-pointer flex items-center gap-1.5 disabled:opacity-50"
            title="Jalankan sinkronisasi sekarang juga tanpa menunggu putaran jadwal"
          >
            <RefreshCw class="w-3.5 h-3.5 text-indigo-500" :class="{ 'animate-spin': isRunningSyncNow }" />
            <span>{{ isRunningSyncNow ? 'Menyinkronkan...' : 'Sinkronkan Sekarang' }}</span>
          </button>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="showAutoSyncModal = false"
              class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition cursor-pointer"
            >
              Tutup
            </button>
            <button
              type="button"
              @click="handleSaveAutoSync"
              :disabled="isSavingAutoSync"
              class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs disabled:opacity-50"
            >
              <Check class="w-4 h-4" />
              <span>{{ isSavingAutoSync ? 'Menyimpan...' : 'Simpan Pengaturan' }}</span>
            </button>
          </div>
        </div>

      </div>
    </div>

  </HybridNavbar>
</template>

