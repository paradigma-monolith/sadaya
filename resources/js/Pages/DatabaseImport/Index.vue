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
  ArrowRight,
  Layers,
  FileSpreadsheet,
  Check,
  X,
  ExternalLink,
  ShieldCheck,
  Clock,
  Sparkles,
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
  selected_module: {
    type: String,
    default: 'all',
  },
});

// Credentials state
const credentials = ref({
  host: props.default_config.host || '127.0.0.1',
  port: props.default_config.port || 3306,
  username: props.default_config.username || 'root',
  password: props.default_config.password || 'mysql',
});

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

// File Upload Modal State
const showUploadModal = ref(false);
const uploadTargetModule = ref(null);
const uploadFile = ref(null);
const isUploading = ref(false);
const uploadResult = ref(null);

// Live local stats
const localSadayaStats = ref({ ...props.sadaya_stats });

const iconComponent = (iconName) => {
  switch (iconName) {
    case 'Ship': return Ship;
    case 'Plane': return Plane;
    case 'Anchor': return Anchor;
    case 'PackageCheck': return PackageCheck;
    case 'Pill': return Pill;
    default: return Database;
  }
};

const formatNumber = (val) => {
  if (val === undefined || val === null) return '0';
  return Number(val).toLocaleString('id-ID');
};

const handleTestConnection = async () => {
  isTesting.value = true;
  testResults.value = null;
  migrationSummary.value = null;
  try {
    const res = await axios.post('/database-import/test-connection', credentials.value);
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
            Pindahkan dan sinkronkan seluruh basis data operasional (Pengawasan Kedatangan Kapal MDH, Travel Consent Pesawat Udara, Sanitasi Kapal, Alkes &amp; Obat Medis Haji) secara langsung dari MySQL Simply maupun berkas backup.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
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

      <!-- ==================== MYSQL CREDENTIALS & OPTIONS BAR ==================== -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
          <div class="flex items-center gap-2 text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
            <Server class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
            <span>Konfigurasi Koneksi MySQL Sumber (Simply / Local AMPPS)</span>
          </div>
          <span class="text-[11px] text-slate-400 font-mono">
            Database Terhubung: bkkr3927_cop &amp; bkkr3927_simply
          </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div>
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Host MySQL</label>
            <input
              type="text"
              v-model="credentials.host"
              class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            />
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Port</label>
            <input
              type="number"
              v-model="credentials.port"
              class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            />
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Username</label>
            <input
              type="text"
              v-model="credentials.username"
              class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            />
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Password</label>
            <input
              type="password"
              v-model="credentials.password"
              class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            />
          </div>
        </div>

        <!-- Migration Flags Checkboxes -->
        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs">
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
                <th class="py-3 px-4">Sumber Simply (CI4)</th>
                <th class="py-3 px-4">Tujuan Sadaya</th>
                <th class="py-3 px-4 text-center">Data di Sadaya</th>
                <th class="py-3 px-4 text-center">Status di Simply</th>
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
                  <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 inline-block">
                    {{ mod.source_db }}.{{ mod.source_table }}
                  </span>
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

  </HybridNavbar>
</template>

