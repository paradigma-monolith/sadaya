<script setup>
import { Bug, Clock } from 'lucide-vue-next';
import {
  getRiskBadgeClass,
  standardHabitatAnopheles,
  anophelesJamSlots,
} from '../utils';

defineProps({
  activeVektor: {
    type: String,
    required: true,
  },
  wilkerOptions: {
    type: Array,
    default: () => [],
  },
  formJentik: {
    type: Object,
    required: true,
  },
  formNyamuk: {
    type: Object,
    required: true,
  },
  indeksHabitatAnopheles: {
    type: Number,
    required: true,
  },
  statusAnophelesJentik: {
    type: String,
    required: true,
  },
  manHours: {
    type: Number,
    required: true,
  },
  totalDalamNyamuk: {
    type: Number,
    required: true,
  },
  totalLuarNyamuk: {
    type: Number,
    required: true,
  },
  totalTertangkapNyamuk: {
    type: Number,
    required: true,
  },
  mhdDalam: {
    type: Number,
    required: true,
  },
  mhdLuar: {
    type: Number,
    required: true,
  },
  mbrAnopheles: {
    type: Number,
    required: true,
  },
  adaAnopheles: {
    type: String,
    required: true,
  },
  statusAnophelesNyamuk: {
    type: String,
    required: true,
  },
});
</script>

<template>
  <!-- 4.6 JENTIK ANOPHELES -->
  <div v-if="activeVektor === 'Jentik Anopheles'" class="space-y-4">
    <div class="p-3 rounded-2xl bg-teal-50/60 dark:bg-teal-950/30 border border-teal-200/60 dark:border-teal-800/50">
      <span class="text-xs font-bold text-teal-900 dark:text-teal-300 flex items-center gap-1.5">
        <Bug class="w-4 h-4 text-teal-600" />
        <span>Surveilans Jentik Anopheles (Vektor Malaria &amp; 10 Tipe Habitat Air)</span>
      </span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
        <select v-model="formJentik.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
          <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
        <input type="date" v-model="formJentik.tanggal" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi Survei</label>
        <input type="text" v-model="formJentik.lokasi" placeholder="Contoh: PPI Karangsong Indramayu" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
      </div>
    </div>

    <div class="p-4 rounded-2xl bg-teal-50/30 dark:bg-teal-950/20 border border-teal-200 dark:border-teal-800/60 space-y-2">
      <label class="block text-xs font-bold text-teal-900 dark:text-teal-300">Tipe Habitat Perkembangbiakan Disurvei (Pilih yang sesuai):</label>
      <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
        <label
          v-for="hab in standardHabitatAnopheles"
          :key="hab"
          class="flex items-center gap-2 p-2 rounded-xl border transition cursor-pointer text-xs"
          :class="formJentik.jenis_habitat.includes(hab) ? 'bg-teal-50 dark:bg-teal-900/40 border-teal-500 font-bold text-teal-800 dark:text-teal-200' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'"
        >
          <input type="checkbox" :value="hab" v-model="formJentik.jenis_habitat" class="rounded accent-[#00A99D] text-teal-600 focus:ring-teal-500" />
          <span>{{ hab }}</span>
        </label>
      </div>
      <div class="pt-1">
        <input
          type="text"
          v-model="formJentik.jenis_habitat_lainnya"
          placeholder="Ketik tipe habitat lainnya jika ada..."
          class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900"
        />
      </div>
    </div>

    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
      <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Karakteristik &amp; Kondisi Lingkungan Perindukan Air</h4>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
        <div>
          <label class="block text-[11px] text-slate-500 mb-1">Pencahayaan</label>
          <select v-model="formJentik.kondisi_pencahayaan[0]" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
            <option value="Terkena sinar matahari">Terkena sinar matahari</option>
            <option value="Terlindung / Teduh">Terlindung / Teduh</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 mb-1">Vegetasi / Lumut</label>
          <select v-model="formJentik.kondisi_vegetasi[0]" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
            <option value="Ada vegetasi">Ada Vegetasi / Lumut</option>
            <option value="Tidak ada vegetasi">Tidak Ada Vegetasi</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 mb-1">Sifat &amp; Salinitas Air</label>
          <select v-model="formJentik.kondisi_air[0]" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
            <option value="Payau">Payau / Salin (Pesisir)</option>
            <option value="Tawar Jernih">Tawar Jernih</option>
            <option value="Keruh">Keruh / Berlumpur</option>
            <option value="Tergenang">Tergenang</option>
            <option value="Mengalir Lambat">Mengalir Lambat</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3 pt-2">
        <div>
          <label class="block text-[11px] text-slate-500 mb-1">Jumlah Habitat Disurvei</label>
          <input type="number" min="1" v-model.number="formJentik.jml_habitat_disurvei" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 mb-1">Jumlah Habitat Positif Jentik Anopheles</label>
          <input type="number" min="0" v-model.number="formJentik.jml_habitat_positif" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono text-rose-600 font-bold" />
        </div>
      </div>

      <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-teal-200 dark:border-teal-800 flex items-center justify-between text-xs">
        <div>
          <span class="text-slate-500">Rumus: (Positif / Disurvei) &times; 100% &bull;</span>
          <span class="ml-1 text-slate-700 dark:text-slate-300 font-semibold">Indeks Habitat: <strong class="text-teal-600 font-mono text-base">{{ indeksHabitatAnopheles }}%</strong></span>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getRiskBadgeClass(statusAnophelesJentik)">
          {{ statusAnophelesJentik }} (&lt; 1% MS)
        </span>
      </div>
    </div>

    <div>
      <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tindakan Pengendalian</label>
      <textarea v-model="formJentik.tindakan_pengendalian" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
    </div>
  </div>

  <!-- 4.7 NYAMUK ANOPHELES DEWASA -->
  <div v-else-if="activeVektor === 'Nyamuk Anopheles'" class="space-y-4">
    <div class="p-3 rounded-2xl bg-teal-50/60 dark:bg-teal-950/30 border border-teal-200/60 dark:border-teal-800/50">
      <span class="text-xs font-bold text-teal-900 dark:text-teal-300 flex items-center gap-1.5">
        <Bug class="w-4 h-4 text-teal-600" />
        <span>Surveilans Nyamuk Anopheles Dewasa (Penangkapan Umpan Orang Malam - Matriks 12 Jam)</span>
      </span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
        <select v-model="formNyamuk.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
          <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
        <input type="date" v-model="formNyamuk.tanggal" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi</label>
        <input type="text" v-model="formNyamuk.lokasi" placeholder="PPI Karangsong" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Spesies Target</label>
        <input type="text" v-model="formNyamuk.spesies" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
      </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs">
      <div>
        <label class="block text-[11px] text-slate-500 mb-1">Kolektor Penangkap</label>
        <input type="number" min="1" v-model.number="formNyamuk.jml_penangkap" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
      </div>
      <div>
        <label class="block text-[11px] text-slate-500 mb-1">Lama Survei (Jam)</label>
        <input type="number" min="1" v-model.number="formNyamuk.lama_penangkapan" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
      </div>
      <div>
        <label class="block text-[11px] text-slate-500 mb-1">Suhu (°C)</label>
        <input type="number" step="0.1" v-model.number="formNyamuk.suhu" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
      </div>
      <div>
        <label class="block text-[11px] text-slate-500 mb-1">Kelembaban (%)</label>
        <input type="number" step="0.1" v-model.number="formNyamuk.kelembaban" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
      </div>
      <div>
        <label class="block text-[11px] text-slate-500 mb-1">Kondisi Cuaca</label>
        <input type="text" v-model="formNyamuk.cuaca" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900" />
      </div>
    </div>

    <div class="p-4 rounded-2xl bg-teal-50/30 dark:bg-teal-950/20 border border-teal-200 dark:border-teal-800/60 space-y-3">
      <div class="flex items-center justify-between gap-2 flex-wrap">
        <h4 class="text-xs font-bold text-[#005851] dark:text-teal-300 flex items-center gap-1.5">
          <Clock class="w-4 h-4 text-[#00A99D]" />
          <span>Matriks Penangkapan Nyamuk 12 Jam (18.00 - 06.00 WIB)</span>
        </h4>
        <span class="text-xs font-mono font-bold text-slate-500">Total Man-Hours: {{ manHours }} jam-orang</span>
      </div>

      <!-- Mobile Capture Matrix Cards (sm:hidden) -->
      <div class="sm:hidden space-y-2 max-h-72 overflow-y-auto pr-1">
        <div
          v-for="slot in anophelesJamSlots"
          :key="`mob-${slot.key}`"
          class="p-2.5 rounded-xl border border-teal-200 dark:border-teal-800 bg-white dark:bg-slate-900 space-y-2"
        >
          <div class="flex items-center justify-between text-xs">
            <span class="font-bold text-slate-800 dark:text-slate-100">{{ slot.label }}</span>
            <span class="font-mono font-bold text-[#005851] dark:text-teal-300">
              Total: {{ Number(formNyamuk.tangkapan_dalam[slot.key] || 0) + Number(formNyamuk.tangkapan_luar[slot.key] || 0) }}
            </span>
          </div>
          <div class="grid grid-cols-2 gap-2 text-[11px]">
            <label class="block">
              <span class="text-slate-500 block mb-0.5">Dalam (D)</span>
              <input type="number" min="0" v-model.number="formNyamuk.tangkapan_dalam[slot.key]" class="w-full py-1 px-2 text-center font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs" />
            </label>
            <label class="block">
              <span class="text-slate-500 block mb-0.5">Luar (L)</span>
              <input type="number" min="0" v-model.number="formNyamuk.tangkapan_luar[slot.key]" class="w-full py-1 px-2 text-center font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs" />
            </label>
          </div>
        </div>
        <div class="p-2.5 rounded-xl bg-teal-50 dark:bg-teal-950/60 border border-teal-200 dark:border-teal-800 flex items-center justify-between text-xs font-bold">
          <span>TOTAL TERTANGKAP</span>
          <span class="font-mono text-[#005851] dark:text-teal-200">
            D: {{ totalDalamNyamuk }} &bull; L: {{ totalLuarNyamuk }} &bull; {{ totalTertangkapNyamuk }} ekor
          </span>
        </div>
      </div>

      <div class="hidden sm:block overflow-x-auto rounded-xl border border-teal-200 dark:border-teal-800">
        <table class="w-full text-xs text-center bg-white dark:bg-slate-900">
          <thead>
            <tr class="bg-teal-50 dark:bg-teal-950/60 text-[#005851] dark:text-teal-200 text-[11px] font-bold">
              <th class="py-2 px-3 text-left">Jam Penangkapan</th>
              <th class="py-2 px-3">Dalam Rumah (D)</th>
              <th class="py-2 px-3">Luar Rumah (L)</th>
              <th class="py-2 px-3">Total Jam (D+L)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-teal-100 dark:divide-teal-900/40 font-mono">
            <tr v-for="slot in anophelesJamSlots" :key="slot.key" class="hover:bg-teal-50/50 dark:hover:bg-teal-950/30">
              <td class="py-1.5 px-3 text-left font-sans font-semibold text-slate-700 dark:text-slate-300 text-xs">{{ slot.label }}</td>
              <td class="py-1 px-3">
                <input type="number" min="0" v-model.number="formNyamuk.tangkapan_dalam[slot.key]" class="w-16 py-1 text-center font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs" />
              </td>
              <td class="py-1 px-3">
                <input type="number" min="0" v-model.number="formNyamuk.tangkapan_luar[slot.key]" class="w-16 py-1 text-center font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs" />
              </td>
              <td class="py-1.5 px-3 font-bold text-slate-800 dark:text-slate-200">
                {{ Number(formNyamuk.tangkapan_dalam[slot.key] || 0) + Number(formNyamuk.tangkapan_luar[slot.key] || 0) }}
              </td>
            </tr>
            <tr class="bg-teal-50 dark:bg-teal-950/80 font-bold border-t-2 border-teal-200 dark:border-teal-800 text-xs">
              <td class="py-2.5 px-3 text-left font-sans">TOTAL TERTANGKAP</td>
              <td class="py-2.5 px-3 text-[#005851] dark:text-teal-300 font-extrabold">{{ totalDalamNyamuk }} ekor</td>
              <td class="py-2.5 px-3 text-[#005851] dark:text-teal-300 font-extrabold">{{ totalLuarNyamuk }} ekor</td>
              <td class="py-2.5 px-3 text-[#005851] dark:text-teal-100 font-black">{{ totalTertangkapNyamuk }} ekor</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3 rounded-xl bg-white dark:bg-slate-900 border border-teal-200 dark:border-teal-800 text-xs">
        <div>
          <span class="text-slate-500 block text-[11px]">MHD Dalam Rumah (D):</span>
          <strong class="text-[#005851] dark:text-[#00A99D] font-mono text-base">{{ mhdDalam }}</strong>
          <span class="text-[10px] text-slate-400 block">(Total D / Man-Hours)</span>
        </div>
        <div>
          <span class="text-slate-500 block text-[11px]">MHD Luar Rumah (L):</span>
          <strong class="text-[#005851] dark:text-[#00A99D] font-mono text-base">{{ mhdLuar }}</strong>
          <span class="text-[10px] text-slate-400 block">(Total L / Man-Hours)</span>
        </div>
        <div>
          <span class="text-slate-500 block text-[11px]">MBR (Man Biting Rate):</span>
          <div class="flex items-center justify-between">
            <strong class="text-[#005851] dark:text-[#00A99D] font-mono text-base">{{ mbrAnopheles }}</strong>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border" :class="getRiskBadgeClass(statusAnophelesNyamuk)">{{ statusAnophelesNyamuk }}</span>
          </div>
          <span class="text-[10px] text-slate-400 block">Temuan Nyamuk: <strong>{{ adaAnopheles }}</strong></span>
        </div>
      </div>
    </div>

    <div>
      <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tindakan Pengendalian</label>
      <textarea v-model="formNyamuk.tindakan_pengendalian" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
    </div>
  </div>
</template>
