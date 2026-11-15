<script setup>
import { Bug, Droplets, Plus, Trash2 } from 'lucide-vue-next';
import { getRiskBadgeClass, standardSpesiesTikus } from '../utils';

defineProps({
  activeVektor: { type: String, required: true },
  wilkerOptions: { type: Array, default: () => [] },
  formJentik: { type: Object, required: true },
  formKecoa: { type: Object, required: true },
  formTikus: { type: Object, required: true },
  formPinjal: { type: Object, required: true },
  hiJentik: { type: Number, required: true },
  ciJentik: { type: Number, required: true },
  biJentik: { type: Number, required: true },
  abjJentik: { type: Number, required: true },
  statusJentik: { type: String, required: true },
  totalKecoaTertangkap: { type: Number, required: true },
  indeksKecoa: { type: Number, required: true },
  statusKecoa: { type: String, required: true },
  totalTikusTertangkap: { type: Number, required: true },
  successTrapTikus: { type: Number, required: true },
  statusTikus: { type: String, required: true },
  indeksPinjalUmum: { type: Number, required: true },
  indeksPinjalKhusus: { type: Number, required: true },
});

const emit = defineEmits(['add-tikus-row', 'remove-tikus-row']);
</script>

<template>
  <!-- 4.1 JENTIK AEDES SP. -->
  <div v-if="activeVektor === 'Jentik Aedes sp.'" class="space-y-4">
    <div class="p-3 rounded-2xl bg-teal-50/60 dark:bg-teal-950/30 border border-teal-200/60 dark:border-teal-800/50">
      <span class="text-xs font-bold text-[#005851] dark:text-teal-300 flex items-center gap-1.5">
        <Droplets class="w-4 h-4 text-[#00A99D]" />
        <span>Surveilans Jentik Aedes sp. (Demam Berdarah Dengue)</span>
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
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi Pemantauan</label>
        <input type="text" v-model="formJentik.lokasi" placeholder="Contoh: Perimeter Bandara / Dermaga" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Bulan Pelaksanaan</label>
        <input type="month" v-model="formJentik.bulan_pelaksanaan" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kondisi Musim</label>
        <select v-model="formJentik.kondisi_musim" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
          <option value="Hujan">Hujan</option>
          <option value="Kemarau">Kemarau</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah</label>
        <select v-model="formJentik.wilayah" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
          <option value="Perimeter">Perimeter</option>
          <option value="Buffer">Buffer Area</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Mulai</label>
        <input type="date" v-model="formJentik.tanggal_mulai" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Selesai</label>
        <input type="date" v-model="formJentik.tanggal_selesai" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
      </div>
    </div>

    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
      <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Hasil Pemeriksaan Jentik Kontainer &amp; Bangunan</h4>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
        <div>
          <label class="block text-[11px] text-slate-500 mb-1">Rumah Diperiksa (A)</label>
          <input type="number" min="0" v-model="formJentik.jml_rumah_diperiksa" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 mb-1">Kontainer Diperiksa (B)</label>
          <input type="number" min="0" v-model="formJentik.jml_kontainer_diperiksa" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 mb-1">Rumah Positif (C)</label>
          <input type="number" min="0" v-model="formJentik.jml_rumah_positif" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono text-rose-600 font-bold" />
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 mb-1">Kontainer Positif (D)</label>
          <input type="number" min="0" v-model="formJentik.jml_kontainer_positif" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono text-rose-600 font-bold" />
        </div>
      </div>

      <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-teal-200/80 dark:border-teal-900 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex flex-wrap items-center gap-4">
          <div><span class="text-slate-400">House Index (HI):</span> <strong class="text-[#005851] dark:text-[#00A99D] font-mono text-sm ml-1">{{ hiJentik }}%</strong></div>
          <div><span class="text-slate-400">Container Index (CI):</span> <strong class="text-[#005851] dark:text-[#00A99D] font-mono text-sm ml-1">{{ ciJentik }}%</strong></div>
          <div><span class="text-slate-400">Breteau Index (BI):</span> <strong class="text-[#005851] dark:text-[#00A99D] font-mono text-sm ml-1">{{ biJentik }}</strong></div>
          <div><span class="text-slate-400">Angka Bebas Jentik (ABJ):</span> <strong class="text-emerald-600 font-mono text-sm ml-1">{{ abjJentik }}%</strong></div>
        </div>
        <div>
          <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getRiskBadgeClass(statusJentik)">{{ statusJentik }}</span>
        </div>
      </div>
    </div>

    <div>
      <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Jenis Kontainer Positif yang Ditemukan</label>
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
        <label v-for="cName in ['Ban Bekas', 'Ember', 'Bak Mandi', 'Tatakan Dispenser Air Minum', 'Tanaman Penyimpan Air']" :key="cName" class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 cursor-pointer">
          <input type="checkbox" :value="cName" v-model="formJentik.jenis_kontainer" class="rounded accent-[#00A99D] text-teal-600 focus:ring-[#00A99D]" />
          <span>{{ cName }}</span>
        </label>
      </div>
    </div>

    <div>
      <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tindakan Pengendalian</label>
      <textarea v-model="formJentik.tindakan_pengendalian" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
    </div>
  </div>

  <!-- 4.3 KECOA -->
  <div v-if="activeVektor === 'Kecoa'" class="space-y-4">
    <div class="p-3 rounded-2xl bg-rose-50/60 dark:bg-rose-950/30 border border-rose-200/60 dark:border-rose-800/50">
      <span class="text-xs font-bold text-rose-900 dark:text-rose-300 flex items-center gap-1.5">
        <Bug class="w-4 h-4 text-rose-600" />
        <span>Surveilans Kepadatan Kecoa (Metode Trapping / Perangkap)</span>
      </span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
        <select v-model="formKecoa.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
          <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
        <input type="date" v-model="formKecoa.tanggal" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi</label>
        <input type="text" v-model="formKecoa.lokasi" placeholder="Contoh: Terminal Penumpang" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Ruangan</label>
        <input type="text" v-model="formKecoa.nama_ruangan" placeholder="Contoh: Gudang Makanan / Dapur" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
      </div>
    </div>

    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Perangkap Terpasang</label>
          <input type="number" min="1" v-model.number="formKecoa.perangkap_terpasang" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Periplaneta americana (ekor)</label>
          <input type="number" min="0" v-model.number="formKecoa.sp_americana" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Blattella germanica (ekor)</label>
          <input type="number" min="0" v-model.number="formKecoa.sp_germanica" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Spesies Lainnya (ekor)</label>
          <input type="number" min="0" v-model.number="formKecoa.sp_lainnya_jml" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
        </div>
      </div>

      <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-rose-200 dark:border-rose-800 flex items-center justify-between text-xs">
        <div>
          <span class="text-slate-500">Total Tertangkap: <strong class="font-mono text-slate-900 dark:text-white">{{ totalKecoaTertangkap }}</strong> ekor</span>
          <span class="mx-2">&bull;</span>
          <span class="text-slate-700 dark:text-slate-300 font-semibold">Indeks Populasi Kecoa: <strong class="text-rose-600 font-mono text-base">{{ indeksKecoa }}</strong></span>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getRiskBadgeClass(statusKecoa)">
          {{ statusKecoa }} (&lt; 2 MS)
        </span>
      </div>
    </div>

    <div>
      <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tindakan Pengendalian</label>
      <textarea v-model="formKecoa.tindakan_pengendalian" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
    </div>
  </div>

  <!-- 4.4 TIKUS -->
  <div v-if="activeVektor === 'Tikus'" class="space-y-4">
    <div class="p-3 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">
      <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
        <Bug class="w-4 h-4 text-slate-600" />
        <span>Surveilans Tikus &amp; Rodensia (Perangkap &amp; Spesies Target)</span>
      </span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
        <select v-model="formTikus.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
          <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Mulai</label>
        <input type="date" v-model="formTikus.tanggal_mulai" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Selesai</label>
        <input type="date" v-model="formTikus.tanggal_selesai" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi</label>
        <input type="text" v-model="formTikus.lokasi" placeholder="Contoh: Buffer Area / Perimeter" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi Pemasangan</label>
        <input type="text" v-model="formTikus.lokasi_pemasangan" placeholder="Contoh: Dermaga 01 & Gudang Petikemas" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Umpan</label>
        <input type="text" v-model="formTikus.jenis_umpan" placeholder="Contoh: Kelapa bakar, Ikan asin" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
      </div>
    </div>

    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
      <div class="flex items-center justify-between">
        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Rincian Spesies Tikus Tertangkap</h4>
        <button type="button" @click="emit('add-tikus-row')" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-600 text-white flex items-center gap-1 cursor-pointer">
          <Plus class="w-3 h-3" />
          <span>Tambah Baris Spesies</span>
        </button>
      </div>

      <div class="space-y-2">
        <div v-for="(row, idx) in formTikus.spesies_list" :key="idx" class="flex items-center gap-3 bg-white dark:bg-slate-900 p-2 rounded-xl border border-slate-200 dark:border-slate-700">
          <select v-model="row.nama" class="flex-1 px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
            <option v-for="sp in standardSpesiesTikus" :key="sp" :value="sp">{{ sp }}</option>
          </select>
          <input v-if="row.nama === 'Lain-lain'" type="text" v-model="row.custom" placeholder="Nama spesies lainnya" class="flex-1 px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700" />
          <div class="w-24">
            <input type="number" min="0" v-model.number="row.jumlah" placeholder="Jumlah" class="w-full px-2.5 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-700" />
          </div>
          <button
            type="button"
            @click="emit('remove-tikus-row', idx)"
            aria-label="Hapus Baris Spesies Tikus"
            title="Hapus Baris"
            class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg cursor-pointer"
          >
            <Trash2 class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3 pt-2">
        <div>
          <label class="block text-[11px] text-slate-500 mb-1">Jumlah Perangkap Dipasang</label>
          <input type="number" min="1" v-model.number="formTikus.jml_perangkap_dipasang" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 mb-1">Total Tikus Tertangkap (ekor)</label>
          <input type="number" readonly :value="totalTikusTertangkap" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 font-mono font-bold" />
        </div>
      </div>

      <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 flex items-center justify-between text-xs">
        <div>
          <span class="text-slate-500">Rumus: (Tikus / Perangkap) &times; 100% &bull;</span>
          <span class="ml-1 text-slate-700 dark:text-slate-300 font-semibold">Success Trap: <strong class="text-[#005851] dark:text-[#00A99D] font-mono text-base">{{ successTrapTikus }}%</strong></span>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getRiskBadgeClass(statusTikus)">
          {{ statusTikus }} (&lt; 1% MS)
        </span>
      </div>
    </div>

    <div>
      <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tindakan Pengendalian</label>
      <textarea v-model="formTikus.tindakan_pengendalian" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
    </div>
  </div>

  <!-- 4.5 PINJAL TIKUS -->
  <div v-if="activeVektor === 'Pinjal'" class="space-y-4">
    <div class="p-3 rounded-2xl bg-cyan-50/60 dark:bg-cyan-950/30 border border-cyan-200/60 dark:border-cyan-800/50">
      <span class="text-xs font-bold text-cyan-900 dark:text-cyan-300 flex items-center gap-1.5">
        <Bug class="w-4 h-4 text-cyan-600" />
        <span>Surveilans Indeks Pinjal Tikus (Vektor Pes / Xenopsylla cheopis)</span>
      </span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Wilayah Kerja *</label>
        <select v-model="formPinjal.wilker" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
          <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
        <input type="date" v-model="formPinjal.tanggal" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-mono" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi</label>
        <input type="text" v-model="formPinjal.lokasi" placeholder="Contoh: Dermaga / Gudang" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" />
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tikus Tertangkap Diperiksa</label>
        <input type="number" min="1" v-model.number="formPinjal.jml_tikus_tertangkap" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jumlah Pinjal Umum</label>
        <input type="number" min="0" v-model.number="formPinjal.pinjal_umum" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pinjal Xenopsylla cheopis</label>
        <input type="number" min="0" v-model.number="formPinjal.pinjal_khusus" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-mono" />
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
        <div>
          <span class="text-slate-400 block text-[11px]">Indeks Pinjal Umum (Umum / Tikus):</span>
          <strong class="text-cyan-600 font-mono text-base">{{ indeksPinjalUmum }}</strong>
        </div>
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="indeksPinjalUmum < 2.0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300'">
          {{ indeksPinjalUmum < 2.0 ? 'MS (&lt; 2)' : 'TMS (&ge; 2)' }}
        </span>
      </div>
      <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
        <div>
          <span class="text-slate-400 block text-[11px]">Indeks Pinjal Khusus (Khusus / Tikus):</span>
          <strong class="text-cyan-600 font-mono text-base">{{ indeksPinjalKhusus }}</strong>
        </div>
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="indeksPinjalKhusus < 1.0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300'">
          {{ indeksPinjalKhusus < 1.0 ? 'MS (&lt; 1)' : 'TMS (&ge; 1)' }}
        </span>
      </div>
    </div>

    <div>
      <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tindakan Pengendalian</label>
      <textarea v-model="formPinjal.tindakan_pengendalian" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"></textarea>
    </div>
  </div>
</template>
