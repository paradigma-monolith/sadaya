<script setup>
import { ref, computed, onMounted, defineAsyncComponent } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import { toast } from 'vue-sonner';
import { useConfirm } from '@/composables/useConfirm';
import { getModuleIcon, formatImportNumber } from './components/moduleIcons';
import {
  Database,
  RefreshCw,
  CheckCircle2,
  XCircle,
  UploadCloud,
  Layers,
  Check,
  X,
  Clock,
  Sparkles,
  SlidersHorizontal,
} from 'lucide-vue-next';
import axios from 'axios';

const UploadBackupModal = defineAsyncComponent(() => import('./components/UploadBackupModal.vue'));
const ModuleConfigModal = defineAsyncComponent(() => import('./components/ModuleConfigModal.vue'));
const AutoSyncModal = defineAsyncComponent(() => import('./components/AutoSyncModal.vue'));

const page = usePage();
const isSuperAdmin = computed(() => {
  const roles = page.props.auth?.user?.roles || [];
  return Array.isArray(roles) ? roles.includes('Super Admin') : false;
});

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
      password: '',
      has_password: true,
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

// Global / Default Credentials state (Password is redacted/hidden from frontend props)
const credentials = ref({
  host: props.default_config.host || '127.0.0.1',
  port: props.default_config.port || 3306,
  username: props.default_config.username || 'root',
  password: '',
});

// Per-module configurations state (host, port, username, password, database, table)
const initialModuleConfigs = {};
props.modules.forEach((mod) => {
  const fromProps = props.default_module_configs?.[mod.id] || {};
  initialModuleConfigs[mod.id] = {
    host: fromProps.host || credentials.value.host,
    port: fromProps.port || credentials.value.port,
    username: fromProps.username !== undefined ? fromProps.username : credentials.value.username,
    password: '',
    has_password: Boolean(fromProps.has_password),
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
const configModalMounted = ref(false);
const configTargetModule = ref(null);

// File Upload Modal State
const showUploadModal = ref(false);
const uploadModalMounted = ref(false);
const uploadTargetModule = ref(null);

// Live local stats
const localSadayaStats = ref({ ...props.sadaya_stats });

// Auto-Sync Modal & Schedule State
const showAutoSyncModal = ref(false);
const autoSyncModalMounted = ref(false);
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

const iconComponent = getModuleIcon;
const formatNumber = formatImportNumber;

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

const { confirmDialog } = useConfirm();

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
  toast.success(`Kredensial host, port, username, dan password berhasil disalin ke seluruh ${props.modules.length} modul.`);
};

const openConfigModal = (module) => {
  configTargetModule.value = module;
  configModalMounted.value = true;
  showConfigModal.value = true;
};

const handleModuleTested = ({ moduleId, modResult, sadayaCurrent }) => {
  if (!testResults.value) {
    testResults.value = { success: true, modules: {} };
  }
  if (!testResults.value.modules) {
    testResults.value.modules = {};
  }
  testResults.value.modules[moduleId] = modResult;
  if (sadayaCurrent) {
    localSadayaStats.value = sadayaCurrent;
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
    const ok = await confirmDialog({
      title: 'Konfirmasi Pengosongan Tabel',
      message: `PERINGATAN: Anda memilih untuk mengosongkan ${targetName} di Sadaya sebelum impor. Seluruh baris lama akan dihapus. Lanjutkan?`,
      confirmText: 'Ya, Kosongkan & Impor',
      variant: 'danger',
    });
    if (!ok) return;
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
  uploadModalMounted.value = true;
  showUploadModal.value = true;
};

const openAutoSyncModal = () => {
  autoSyncModalMounted.value = true;
  showAutoSyncModal.value = true;
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
            const { password, ...rest } = parsed[k];
            moduleConfigs.value[k] = {
              ...moduleConfigs.value[k],
              ...rest,
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
    <main class="clinical-main">

      <!-- ==================== HEADER BAR ==================== -->
      <div class="clinical-header flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-9 h-9 rounded-lg bg-[#002B27] border border-[#00A99D]/40 text-[#99CC33] flex items-center justify-center shrink-0">
            <Database class="w-4 h-4" />
          </div>
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <h1 class="text-sm sm:text-[15px] font-bold text-white tracking-tight">
                Pusat Migrasi &amp; Import Database
              </h1>
              <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-[#002B27]/80 text-[#99DFD6] border border-[#00A99D]/30">
                Simply &rarr; Sadaya
              </span>
            </div>
            <p class="text-[11px] text-slate-200/85 truncate mt-0.5">
              Sinkronisasi basis data TANGKAP, Travel Consent Pesawat, Sanitasi Kapal, Alkes &amp; Obat dari MySQL atau backup.
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-1.5 shrink-0">
          <button
            type="button"
            @click="openAutoSyncModal"
            class="px-2.5 py-1.5 rounded-lg border border-white/15 bg-white/10 hover:bg-white/20 text-xs font-semibold text-white transition-colors flex items-center gap-1.5 cursor-pointer"
          >
            <Clock class="w-3.5 h-3.5 text-[#99DFD6]" />
            <span>Auto-Sync</span>
            <span
              class="w-1.5 h-1.5 rounded-full"
              :class="autoSync.enabled ? 'bg-[#99CC33]' : 'bg-slate-400'"
              :title="autoSync.enabled ? 'Status: Aktif' : 'Status: Nonaktif'"
            />
          </button>

          <button
            type="button"
            @click="handleTestConnection"
            :disabled="isTesting"
            class="px-2.5 py-1.5 rounded-lg border border-white/15 bg-white/10 hover:bg-white/20 text-xs font-semibold text-white transition-colors flex items-center gap-1.5 cursor-pointer"
          >
            <RefreshCw class="w-3.5 h-3.5 text-[#99DFD6]" :class="{ 'animate-spin': isTesting }" />
            <span>{{ isTesting ? 'Memeriksa...' : 'Uji Koneksi' }}</span>
          </button>

          <button
            type="button"
            @click="handleMigrate('all')"
            :disabled="isMigrating"
            class="px-3 py-1.5 rounded-lg bg-[#00A99D] hover:bg-[#009388] text-xs font-semibold text-white transition-colors flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
          >
            <Sparkles class="w-3.5 h-3.5" :class="{ 'animate-spin': isMigrating && activeMigratingModule === 'all' }" />
            <span>{{ isMigrating && activeMigratingModule === 'all' ? 'Memigrasikan...' : 'Migrasi Semua' }}</span>
          </button>
        </div>
      </div>

      <!-- ==================== MIGRATION OPTIONS BAR ==================== -->
      <div class="clinical-surface px-4 py-3">
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs">
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

          <label
            class="flex items-center gap-2 font-bold transition select-none"
            :class="isSuperAdmin ? 'text-rose-600 dark:text-rose-400 cursor-pointer' : 'text-slate-400 dark:text-slate-600 cursor-not-allowed opacity-60'"
            :title="isSuperAdmin ? 'Kosongkan data sebelum impor' : 'Hanya Super Admin yang diizinkan mengosongkan tabel (Truncate)'"
          >
            <input
              type="checkbox"
              v-model="options.truncate_first"
              :disabled="!isSuperAdmin"
              class="rounded border-rose-300 text-rose-600 focus:ring-rose-500 w-4 h-4 disabled:opacity-40"
            />
            <span>Kosongkan Tabel Sadaya Sebelum Impor (Truncate)</span>
            <span v-if="!isSuperAdmin" class="text-[10px] font-normal text-slate-400 dark:text-slate-500">(Khusus Super Admin)</span>
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
            @click="openAutoSyncModal"
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
          class="p-4 rounded-xl bg-teal-50/70 border border-teal-200 space-y-2.5 shadow-xs"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 font-bold text-sm text-[#004640]">
              <CheckCircle2 class="w-4 h-4 text-[#00A99D]" />
              <span>{{ migrationSummary.message || 'Proses Migrasi Berhasil Diselesaikan!' }}</span>
            </div>
            <span class="text-xs font-mono font-semibold px-2 py-0.5 rounded-md bg-white border border-teal-200 text-[#005851] clinical-num">
              Waktu: {{ migrationSummary.duration }} detik
            </span>
          </div>

          <!-- If ALL modules were migrated -->
          <div v-if="migrationSummary.details" class="pt-1">
            <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
              <div v-for="(det, k) in migrationSummary.details" :key="`mob-mig-${k}`" class="p-3 space-y-1.5 text-xs">
                <div class="flex items-center justify-between gap-2">
                  <span class="font-semibold text-slate-900 dark:text-white">{{ det.module_name }}</span>
                  <span class="font-mono text-[11px] text-slate-500 clinical-num">{{ det.duration }}s</span>
                </div>
                <div class="grid grid-cols-4 gap-1 text-[10px] font-mono pt-1 border-t border-slate-100 dark:border-slate-800">
                  <div>
                    <span class="block text-slate-400 font-sans">Proses</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-200">{{ formatNumber(det.total_processed) }}</span>
                  </div>
                  <div>
                    <span class="block text-slate-400 font-sans">Impor</span>
                    <span class="font-bold text-emerald-600">{{ formatNumber(det.imported) }}</span>
                  </div>
                  <div>
                    <span class="block text-slate-400 font-sans">Lewat</span>
                    <span class="text-amber-600">{{ formatNumber(det.skipped) }}</span>
                  </div>
                  <div>
                    <span class="block text-slate-400 font-sans">Gagal</span>
                    <span class="text-rose-600">{{ formatNumber(det.failed) }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="hidden md:block overflow-x-auto">
              <table class="w-full text-left text-xs bg-white rounded-lg overflow-hidden border border-slate-200">
                <thead class="bg-slate-50 text-slate-600 text-[11px] uppercase tracking-wider font-semibold border-b border-slate-200">
                  <tr>
                    <th class="p-2.5">Modul</th>
                    <th class="p-2.5 text-center">Total Diproses</th>
                    <th class="p-2.5 text-center">Berhasil Diimpor</th>
                    <th class="p-2.5 text-center">Dilewati (Duplikat)</th>
                    <th class="p-2.5 text-center">Gagal</th>
                    <th class="p-2.5 text-right">Durasi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="(det, k) in migrationSummary.details" :key="k" class="hover:bg-[#F4FBF9]">
                    <td class="p-2.5 font-semibold text-slate-900">{{ det.module_name }}</td>
                    <td class="p-2.5 text-center font-mono clinical-num">{{ formatNumber(det.total_processed) }}</td>
                    <td class="p-2.5 text-center font-bold text-emerald-600 font-mono clinical-num">{{ formatNumber(det.imported) }}</td>
                    <td class="p-2.5 text-center text-amber-600 font-mono clinical-num">{{ formatNumber(det.skipped) }}</td>
                    <td class="p-2.5 text-center text-rose-600 font-mono clinical-num">{{ formatNumber(det.failed) }}</td>
                    <td class="p-2.5 text-right font-mono text-slate-500 clinical-num">{{ det.duration }}s</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- If single module was migrated -->
          <div v-else class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 text-xs">
            <div class="clinical-kpi p-2.5">
              <span class="text-[10px] text-slate-400 block">Total Diproses</span>
              <span class="text-base font-bold text-slate-900 clinical-num">{{ formatNumber(migrationSummary.total_processed) }}</span>
            </div>
            <div class="clinical-kpi p-2.5">
              <span class="text-[10px] text-slate-400 block">Berhasil Diimpor</span>
              <span class="text-base font-bold text-emerald-600 clinical-num">{{ formatNumber(migrationSummary.imported) }}</span>
            </div>
            <div class="clinical-kpi p-2.5">
              <span class="text-[10px] text-slate-400 block">Dilewati (Duplikat)</span>
              <span class="text-base font-bold text-amber-600 clinical-num">{{ formatNumber(migrationSummary.skipped) }}</span>
            </div>
            <div class="clinical-kpi p-2.5">
              <span class="text-[10px] text-slate-400 block">Gagal</span>
              <span class="text-base font-bold text-rose-600 clinical-num">{{ formatNumber(migrationSummary.failed) }}</span>
            </div>
          </div>
        </div>

        <div
          v-else
          class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-start gap-2.5"
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

        <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
          <div
            v-for="mod in modules"
            :key="`mob-mod-${mod.id}`"
            class="p-4 space-y-3"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-2xl flex items-center justify-center shrink-0" :class="[mod.bg_color, mod.color]">
                  <component :is="iconComponent(mod.icon)" class="w-4 h-4" />
                </div>
                <div class="min-w-0">
                  <div class="font-bold text-slate-900 dark:text-white text-xs truncate">
                    {{ mod.name }}
                  </div>
                  <div class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2">
                    {{ mod.description }}
                  </div>
                </div>
              </div>
              <div class="text-right shrink-0 font-mono">
                <span class="font-extrabold text-slate-900 dark:text-white text-sm block">
                  {{ formatNumber(localSadayaStats[mod.id] || 0) }}
                </span>
                <span class="text-[10px] text-slate-400 font-sans">rekaman</span>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-1.5 text-[11px] bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/60 font-mono">
              <div class="flex items-center justify-between gap-2 flex-wrap">
                <span class="text-slate-500 dark:text-slate-400 font-sans">Sumber Simply</span>
                <div class="flex items-center gap-1 flex-wrap justify-end">
                  <span class="font-semibold text-slate-700 dark:text-slate-200">
                    {{ moduleConfigs[mod.id]?.database || mod.source_db }}.{{ moduleConfigs[mod.id]?.table || mod.source_table }}
                  </span>
                  <span v-if="isModuleCustomized(mod.id)" class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                    Kustom
                  </span>
                </div>
              </div>
              <div class="flex items-center justify-between gap-2 flex-wrap">
                <span class="text-slate-500 dark:text-slate-400 font-sans">Tujuan Sadaya</span>
                <span class="text-indigo-600 dark:text-indigo-400 font-semibold">{{ mod.target_table }}</span>
              </div>
              <div class="flex items-center justify-between gap-2 flex-wrap pt-1 border-t border-slate-200/70 dark:border-slate-700">
                <span class="text-slate-500 dark:text-slate-400 font-sans">Status</span>
                <div v-if="testResults && testResults.modules && testResults.modules[mod.id]">
                  <span v-if="testResults.modules[mod.id].connected && testResults.modules[mod.id].table_exists" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                    <Check class="w-3 h-3 text-emerald-600" />
                    {{ formatNumber(testResults.modules[mod.id].count) }} data
                  </span>
                  <span v-else class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                    <X class="w-3 h-3 text-slate-400" />
                    Belum Ada
                  </span>
                </div>
                <span v-else class="text-slate-400">-</span>
              </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 pt-1">
              <button
                type="button"
                @click="openConfigModal(mod)"
                aria-label="Atur database sumber modul"
                class="px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer"
              >
                <SlidersHorizontal class="w-3.5 h-3.5 text-indigo-500" />
                <span>Atur DB</span>
              </button>
              <button
                type="button"
                @click="handleMigrate(mod.id)"
                :disabled="isMigrating"
                class="flex-1 justify-center px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
              >
                <RefreshCw class="w-3 h-3" :class="{ 'animate-spin': isMigrating && activeMigratingModule === mod.id }" />
                <span>{{ isMigrating && activeMigratingModule === mod.id ? 'Memigrasikan...' : 'Migrasi MySQL' }}</span>
              </button>
              <button
                type="button"
                @click="openUploadModal(mod)"
                aria-label="Import dari berkas backup (.sql, .json, .csv)"
                title="Import dari berkas backup (.sql, .json, .csv)"
                class="p-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition cursor-pointer"
              >
                <UploadCloud class="w-4 h-4 text-slate-500" />
              </button>
            </div>
          </div>
        </div>

        <div class="hidden md:block overflow-x-auto">
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
                      aria-label="Import dari berkas backup (.sql, .json, .csv)"
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

    <!-- Lazy-Loaded Modals -->
    <UploadBackupModal
      v-if="uploadModalMounted"
      :open="showUploadModal"
      :module="uploadTargetModule"
      :skip-duplicate="options.skip_duplicate"
      :truncate-first="options.truncate_first"
      @close="showUploadModal = false"
      @uploaded="localSadayaStats = $event"
    />

    <ModuleConfigModal
      v-if="configModalMounted"
      :open="showConfigModal"
      :module="configTargetModule"
      :module-configs="moduleConfigs"
      :credentials="credentials"
      :initial-test-result="configTargetModule ? (testResults?.modules?.[configTargetModule.id] || null) : null"
      @close="showConfigModal = false"
      @update:moduleConfigs="moduleConfigs = $event"
      @tested="handleModuleTested"
    />

    <AutoSyncModal
      v-if="autoSyncModalMounted"
      :open="showAutoSyncModal"
      :auto-sync="autoSync"
      :modules="modules"
      :module-configs="moduleConfigs"
      @close="showAutoSyncModal = false"
      @update:autoSync="autoSync = $event"
      @synced="localSadayaStats = $event"
    />

  </HybridNavbar>
</template>
