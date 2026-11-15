<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import ClinicalModal from '../../../Components/ClinicalModal.vue';
import { getModuleIcon, formatImportNumber } from './moduleIcons';
import { X, RefreshCw, CheckCircle2, XCircle, RotateCcw, Check } from 'lucide-vue-next';

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  module: {
    type: Object,
    default: null,
  },
  moduleConfigs: {
    type: Object,
    required: true,
  },
  credentials: {
    type: Object,
    required: true,
  },
  initialTestResult: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['close', 'update:moduleConfigs', 'tested']);

const form = ref({
  host: '',
  port: 3306,
  username: '',
  password: '',
  has_password: false,
  database: '',
  table: '',
});
const isTestingSingleModule = ref(false);
const singleModuleTestResult = ref(null);
const isSavingConfig = ref(false);

watch(
  () => [props.open, props.module?.id],
  ([isOpen]) => {
    if (isOpen && props.module) {
      const existing = props.moduleConfigs?.[props.module.id] || {};
      form.value = {
        host: existing.host || props.credentials.host,
        port: existing.port || props.credentials.port,
        username: existing.username ?? props.credentials.username,
        password: existing.password || '',
        has_password: Boolean(existing.has_password),
        database: existing.database || props.module.source_db,
        table: existing.table || props.module.source_table,
      };
      singleModuleTestResult.value = props.initialTestResult;
    }
  },
  { immediate: true },
);

const isCustomized = computed(() => {
  if (!props.module) return false;
  return (
    form.value.host !== props.credentials.host ||
    form.value.port !== props.credentials.port ||
    form.value.username !== props.credentials.username ||
    form.value.password !== props.credentials.password ||
    form.value.database !== props.module.source_db ||
    form.value.table !== props.module.source_table
  );
});

const closeModal = () => {
  if (isSavingConfig.value) return;
  emit('close');
};

const resetToDefault = async () => {
  if (!props.module) return;
  const resetSlice = {
    host: props.credentials.host,
    port: props.credentials.port,
    username: props.credentials.username,
    password: props.credentials.password,
    database: props.module.source_db,
    table: props.module.source_table,
  };
  form.value = { ...resetSlice, has_password: false };
  singleModuleTestResult.value = null;

  const updated = {
    ...props.moduleConfigs,
    [props.module.id]: resetSlice,
  };
  emit('update:moduleConfigs', updated);

  try {
    localStorage.setItem('sadaya_import_module_configs', JSON.stringify(updated));
    await axios.post('/database-import/save-config', { modules: updated });
  } catch (e) {}
};

const handleTestSingleModule = async () => {
  if (!props.module) return;
  isTestingSingleModule.value = true;
  singleModuleTestResult.value = null;
  const merged = {
    ...props.moduleConfigs,
    [props.module.id]: { ...form.value },
  };
  try {
    const res = await axios.post('/database-import/test-connection', {
      target_module: props.module.id,
      ...props.credentials,
      modules: merged,
    });
    const modResult = res.data.modules?.[props.module.id];
    singleModuleTestResult.value = modResult;
    emit('tested', {
      moduleId: props.module.id,
      modResult,
      sadayaCurrent: res.data.sadaya_current || null,
    });
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

const saveAndClose = async () => {
  if (!props.module) return;
  isSavingConfig.value = true;

  const nextConfigs = {
    ...props.moduleConfigs,
    [props.module.id]: { ...form.value },
  };

  try {
    const safeStorage = {};
    Object.keys(nextConfigs).forEach((k) => {
      const { password, ...rest } = nextConfigs[k] || {};
      safeStorage[k] = { ...rest, password: '' };
    });
    localStorage.setItem('sadaya_import_module_configs', JSON.stringify(safeStorage));
  } catch (e) {
    console.warn('LocalStorage error:', e);
  }

  try {
    await axios.post('/database-import/save-config', {
      modules: nextConfigs,
    });
    if (nextConfigs[props.module.id]?.password) {
      nextConfigs[props.module.id].has_password = true;
      nextConfigs[props.module.id].password = '';
    }
    emit('update:moduleConfigs', nextConfigs);
  } catch (err) {
    console.error('Gagal menyimpan konfigurasi ke server:', err);
  } finally {
    isSavingConfig.value = false;
    emit('close');
  }
};
</script>

<template>
  <ClinicalModal
    :open="Boolean(open && module)"
    :title="`Konfigurasi Basis Data: ${module?.name || ''}`"
    max-width="max-w-xl"
    :close-on-outside="!isSavingConfig"
    @close="closeModal"
  >
    <div v-if="module" class="bg-white dark:bg-slate-900 rounded-3xl w-full shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col">
      <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl flex items-center justify-center" :class="[module.bg_color, module.color]">
            <component :is="getModuleIcon(module.icon)" class="w-5 h-5" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h3 class="font-bold text-sm text-slate-900 dark:text-white">
                Konfigurasi Basis Data: {{ module.name }}
              </h3>
              <span v-if="isCustomized" class="text-[9px] px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 font-bold border border-indigo-200 dark:border-indigo-800">
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
          @click="closeModal"
          aria-label="Tutup modal konfigurasi database"
          title="Tutup"
          class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="p-5 space-y-4">
        <div class="grid grid-cols-2 gap-3 text-xs">
          <div>
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Host MySQL</label>
            <input
              type="text"
              v-model="form.host"
              placeholder="127.0.0.1"
              class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            />
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Port</label>
            <input
              type="number"
              v-model="form.port"
              placeholder="3306"
              class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            />
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Database Sumber</label>
            <input
              type="text"
              v-model="form.database"
              placeholder="bkkr3927_simply"
              class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            />
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Tabel Sumber</label>
            <input
              type="text"
              v-model="form.table"
              placeholder="tc_sanitasi_kapal"
              class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            />
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Username MySQL</label>
            <input
              type="text"
              v-model="form.username"
              placeholder="root"
              class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            />
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Password MySQL</label>
            <input
              type="password"
              v-model="form.password"
              :placeholder="form.has_password ? '•••••••• (Tersimpan di Server)' : 'Masukkan Password'"
              class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            />
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Kosongkan jika ingin tetap menggunakan password tersimpan di server.</p>
          </div>
        </div>

        <div class="pt-2">
          <div class="flex items-center justify-between pb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Status Pemeriksaan Koneksi Modul</span>
            <button
              type="button"
              @click="handleTestSingleModule"
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
                Ditemukan: <strong>{{ formatImportNumber(singleModuleTestResult.count) }}</strong> baris data pada <code class="bg-black/5 dark:bg-white/10 px-1 rounded">{{ singleModuleTestResult.database }}.{{ singleModuleTestResult.table }}</code>.
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

      <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-800/40">
        <button
          type="button"
          @click="resetToDefault"
          class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition cursor-pointer flex items-center gap-1.5"
          title="Reset modul ini ke nilai bawaan global"
        >
          <RotateCcw class="w-3.5 h-3.5 text-slate-400" />
          <span>Reset Bawaan</span>
        </button>
        <button
          type="button"
          @click="saveAndClose"
          :disabled="isSavingConfig"
          class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs disabled:opacity-50"
        >
          <RefreshCw v-if="isSavingConfig" class="w-4 h-4 animate-spin" />
          <Check v-else class="w-4 h-4" />
          <span>{{ isSavingConfig ? 'Menyimpan...' : 'Simpan &amp; Tutup' }}</span>
        </button>
      </div>
    </div>
  </ClinicalModal>
</template>
