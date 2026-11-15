<script setup>
import { Bug, MapPin, Plus, Trash2 } from 'lucide-vue-next';
import {
  getRiskBadgeClass,
  getLokasiLalatTop5,
  getLokasiLalatTotalTop5,
  getLokasiLalatRata,
  getLokasiLalatStatus,
} from '../utils';

defineProps({
  form: {
    type: Object,
    required: true,
  },
  wilkerOptions: {
    type: Array,
    default: () => [],
  },
  flyIndexRata: {
    type: Number,
    required: true,
  },
  statusLalat: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(['add-lokasi', 'remove-lokasi']);
</script>

<template>
  <div class="space-y-4">
    <div class="p-3 rounded-2xl bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-800/50">
      <span class="text-xs font-bold text-amber-900 dark:text-amber-300 flex items-center gap-1.5">
        <Bug class="w-4 h-4 text-amber-600" />
        <span>Surveilans Kepadatan Lalat (Metode Fly Grill - Multi-Lokasi &amp; Top 5 Tertinggi)</span>
      </span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
        <select v-model="form.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
          <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
        <input type="date" v-model="form.tanggal" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Musim</label>
        <select v-model="form.musim" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
          <option value="Hujan">Hujan</option>
          <option value="Kemarau">Kemarau</option>
        </select>
      </div>
    </div>

    <div class="grid grid-cols-3 gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs">
      <div>
        <label class="block text-[11px] text-slate-500 mb-1">Temperatur (°C)</label>
        <input type="number" step="0.1" v-model="form.temperatur" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
      </div>
      <div>
        <label class="block text-[11px] text-slate-500 mb-1">Kelembapan (%)</label>
        <input type="number" step="0.1" v-model="form.kelembapan" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
      </div>
      <div>
        <label class="block text-[11px] text-slate-500 mb-1">Kecepatan Angin (m/s)</label>
        <input type="number" step="0.1" v-model="form.kecepatan_angin" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
      </div>
    </div>

    <div class="space-y-4">
      <div class="flex items-center justify-between gap-2 flex-wrap">
        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
          <MapPin class="w-4 h-4 text-amber-500" />
          <span>Titik Lokasi Survei Fly Grill (10 Hitungan x 30 Detik)</span>
        </h4>
        <button
          type="button"
          @click="emit('add-lokasi')"
          class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold flex items-center gap-1 shadow-xs cursor-pointer"
        >
          <Plus class="w-3.5 h-3.5" />
          <span>Tambah Titik Lokasi</span>
        </button>
      </div>

      <div
        v-for="(locItem, lIdx) in form.lokasi_items"
        :key="lIdx"
        class="p-4 rounded-2xl bg-amber-50/40 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/60 space-y-3"
      >
        <div class="flex items-center justify-between gap-2">
          <div class="flex items-center gap-2 flex-1 max-w-sm">
            <span class="w-6 h-6 rounded-full bg-amber-500 text-white font-bold text-xs flex items-center justify-center shrink-0">{{ lIdx + 1 }}</span>
            <input
              type="text"
              v-model="locItem.lokasi"
              placeholder="Nama Titik Lokasi (misal: Dapur, TPS, Kantin)"
              class="w-full px-3 py-1.5 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900"
            />
          </div>
          <div class="flex items-center gap-2">
            <span class="text-xs font-mono font-bold text-slate-500 hidden sm:inline">Top 5: {{ getLokasiLalatTop5(locItem).join(', ') }}</span>
            <button
              v-if="form.lokasi_items.length > 1"
              type="button"
              @click="emit('remove-lokasi', lIdx)"
              aria-label="Hapus Titik Lokasi Lalat"
              class="p-1 rounded-lg hover:bg-rose-100 text-rose-500 transition cursor-pointer"
              title="Hapus Titik Ini"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>

        <div class="grid grid-cols-5 sm:grid-cols-10 gap-2">
          <div v-for="tIdx in 10" :key="tIdx" class="text-center">
            <label class="block text-[10px] text-slate-500 mb-0.5">T-{{ tIdx }}</label>
            <input
              type="number"
              min="0"
              v-model.number="locItem.t[tIdx - 1]"
              class="w-full py-1 text-center font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs"
            />
          </div>
        </div>

        <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-200/80 dark:border-amber-900 flex items-center justify-between text-xs">
          <div>
            <span class="text-slate-500">Total Top 5: <strong class="font-mono text-slate-900 dark:text-white">{{ getLokasiLalatTotalTop5(locItem) }}</strong></span>
            <span class="mx-2">&bull;</span>
            <span class="text-slate-700 dark:text-slate-300 font-semibold">Fly Index: <strong class="text-amber-600 font-mono text-sm">{{ getLokasiLalatRata(locItem) }}</strong></span>
          </div>
          <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="getRiskBadgeClass(getLokasiLalatStatus(locItem))">
            {{ getLokasiLalatStatus(locItem) }} (&lt; 2 MS)
          </span>
        </div>
      </div>

      <div class="p-3.5 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/40 dark:to-orange-950/30 border border-amber-300 dark:border-amber-800 flex items-center justify-between text-xs">
        <div>
          <span class="text-slate-600 dark:text-slate-400 font-semibold">Total {{ form.lokasi_items.length }} Titik Disurvei &bull; Rata-Rata Fly Index Gabungan:</span>
          <div class="text-lg font-mono font-extrabold text-amber-700 dark:text-amber-400 mt-0.5">
            {{ flyIndexRata }} <span class="text-xs font-normal text-slate-500">ekor / block grill</span>
          </div>
        </div>
        <div class="text-right">
          <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getRiskBadgeClass(statusLalat)">
            Status Keseluruhan: {{ statusLalat }}
          </span>
        </div>
      </div>
    </div>

    <div>
      <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tindakan Pengendalian Lalat</label>
      <textarea v-model="form.tindakan_pengendalian" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
    </div>
  </div>
</template>
