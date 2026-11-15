<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import ClinicalModal from '../../../Components/ClinicalModal.vue';
import { Clock, X, Sparkles, RefreshCw, Check } from 'lucide-vue-next';

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  autoSync: {
    type: Object,
    required: true,
  },
  modules: {
    type: Array,
    required: true,
  },
  moduleConfigs: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['close', 'update:autoSync', 'synced']);

const localSync = ref({ ...props.autoSync, modules: [...(props.autoSync.modules || [])] });
const isSavingAutoSync = ref(false);
const isRunningSyncNow = ref(false);
const syncFeedback = ref(null);

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      localSync.value = {
        ...props.autoSync,
        modules: [...(props.autoSync.modules || [])],
      };
      syncFeedback.value = null;
    }
  },
  { immediate: true },
);

const handleSaveAutoSync = async () => {
  isSavingAutoSync.value = true;
  syncFeedback.value = null;
  try {
    const res = await axios.post('/database-import/save-auto-sync', {
      enabled: localSync.value.enabled,
      frequency: localSync.value.frequency,
      modules: localSync.value.modules,
    });
    if (res.data.auto_sync_config) {
      localSync.value = { ...localSync.value, ...res.data.auto_sync_config };
    }
    emit('update:autoSync', { ...localSync.value });
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
      localSync.value = { ...localSync.value, ...res.data.auto_sync_config };
      emit('update:autoSync', { ...localSync.value });
    }
    if (res.data.sadaya_current) {
      emit('synced', res.data.sadaya_current);
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
</script>

<template>
  <ClinicalModal
    :open="open"
    title="Pengaturan Sinkronisasi Otomatis"
    max-width="max-w-xl"
    :close-on-outside="!isSavingAutoSync && !isRunningSyncNow"
    @close="emit('close')"
  >
    <div class="bg-white dark:bg-slate-900 rounded-3xl w-full shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col">
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
          @click="emit('close')"
          aria-label="Tutup modal pengaturan auto-sync"
          title="Tutup"
          class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="p-5 space-y-4 text-xs max-h-[75vh] overflow-y-auto">
        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
          <div>
            <div class="font-bold text-slate-900 dark:text-white text-xs flex items-center gap-2">
              <span>Status Sinkronisasi Otomatis</span>
              <span
                class="px-2 py-0.5 rounded-md text-[10px] font-bold"
                :class="localSync.enabled ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300'"
              >
                {{ localSync.enabled ? 'AKTIF' : 'NONAKTIF' }}
              </span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
              {{ localSync.enabled ? 'Data baru akan ditarik otomatis sesuai jadwal.' : 'Sinkronisasi hanya berjalan ketika tombol ditekan manual.' }}
            </p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" v-model="localSync.enabled" class="sr-only peer" />
            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
          </label>
        </div>

        <div>
          <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
            Interval Frekuensi Sinkronisasi
          </label>
          <select
            v-model="localSync.frequency"
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

        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300">
              Pilih Modul yang Disinkronkan
            </label>
            <span class="text-[10px] text-slate-400">
              {{ localSync.modules.length }} dari {{ modules.length }} modul dipilih
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
                v-model="localSync.modules"
                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4"
              />
              <span class="font-medium text-slate-900 dark:text-white">{{ mod.name }}</span>
              <span class="text-[10px] text-slate-400 font-mono ml-auto">
                {{ moduleConfigs[mod.id]?.database || mod.source_db }}.{{ moduleConfigs[mod.id]?.table || mod.source_table }}
              </span>
            </label>
          </div>
        </div>

        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 space-y-1.5">
          <div class="flex items-center justify-between text-[11px]">
            <span class="font-bold text-slate-600 dark:text-slate-300">Eksekusi Terakhir:</span>
            <span class="font-mono text-slate-900 dark:text-white font-semibold">
              {{ localSync.last_run_at || 'Belum pernah dieksekusi' }}
            </span>
          </div>
          <div v-if="localSync.last_status" class="flex items-center justify-between text-[11px]">
            <span class="font-bold text-slate-600 dark:text-slate-300">Status &amp; Data:</span>
            <span
              class="px-2 py-0.5 rounded text-[10px] font-bold"
              :class="localSync.last_status === 'success' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400'"
            >
              {{ localSync.last_status === 'success' ? `Sukses (${localSync.last_imported_count} data baru)` : 'Ada Catatan' }}
            </span>
          </div>
          <p v-if="localSync.last_message" class="text-[11px] text-slate-500 dark:text-slate-400 pt-1 border-t border-slate-200/60 dark:border-slate-700/60 leading-relaxed">
            {{ localSync.last_message }}
          </p>
        </div>

        <div
          v-if="syncFeedback"
          class="p-3 rounded-xl text-xs border"
          :class="syncFeedback.success ? 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300'"
        >
          {{ syncFeedback.message }}
        </div>

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
            @click="emit('close')"
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
  </ClinicalModal>
</template>
