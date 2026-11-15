<script setup>
import ClinicalModal from '../../../Components/ClinicalModal.vue';
import { Bug, Printer, X } from 'lucide-vue-next';
import {
  formatDate,
  getRiskBadgeClass,
  formatTindakanPengendalian,
  anophelesJamSlots,
} from '../utils';

defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  item: {
    type: Object,
    default: null,
  },
  detailType: {
    type: String,
    default: 'sivera',
  },
});

const emit = defineEmits(['close']);
</script>

<template>
  <ClinicalModal
    :open="Boolean(open && item)"
    :title="`Rincian Surveilans Vektor: ${item?.jenis_vektor || ''}`"
    max-width="max-w-2xl"
    @close="emit('close')"
  >
    <div v-if="item" class="relative w-full bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
      <!-- Modal Header -->
      <div class="px-6 py-4 bg-gradient-to-r from-indigo-700 to-[#00A99D] text-white flex items-center justify-between">
        <div class="flex items-center gap-2 min-w-0">
          <Bug class="w-5 h-5 shrink-0" />
          <h3 class="text-sm font-bold truncate">Rincian Surveilans Vektor: {{ item.jenis_vektor }}</h3>
        </div>
        <button
          type="button"
          @click="emit('close')"
          aria-label="Tutup Rincian Surveilans Vektor"
          title="Tutup"
          class="p-1 rounded-lg hover:bg-white/20 transition cursor-pointer shrink-0"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 max-h-[75vh] overflow-y-auto space-y-4 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
          <div>
            <span class="text-slate-400 block text-[11px]">Nama Objek / Vektor:</span>
            <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ item.jenis_vektor }}</span>
          </div>
          <div>
            <span class="text-slate-400 block text-[11px]">Wilayah Kerja:</span>
            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ item.wilker }}</span>
          </div>
          <div>
            <span class="text-slate-400 block text-[11px]">Tanggal Survei:</span>
            <span class="font-mono text-slate-700 dark:text-slate-300">{{ formatDate(item.tanggal) }}</span>
          </div>
          <div>
            <span class="text-slate-400 block text-[11px]">Lokasi / Titik:</span>
            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ item.lokasi || '-' }}</span>
          </div>
        </div>

        <div class="p-4 rounded-2xl border flex flex-wrap items-center justify-between gap-2" :class="getRiskBadgeClass(item.baku_mutu_status)">
          <div>
            <span class="font-bold text-sm">Parameter Utama: {{ item.parameter_utama }}</span>
            <p class="text-[11px] opacity-80 mt-0.5">Baku Mutu: {{ item.baku_mutu_status }}</p>
          </div>
          <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border bg-white/50 dark:bg-black/20">
            {{ item.baku_mutu_status }}
          </span>
        </div>

        <div class="space-y-3">
          <div v-if="item.saran_rencana_tindakan || item.tindakan_pengendalian">
            <span class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Tindakan Pengendalian:</span>
            <p class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">
              {{ formatTindakanPengendalian(item.tindakan_pengendalian || item.saran_rencana_tindakan) }}
            </p>
          </div>

          <div v-if="detailType === 'sivera' && (item.jenis_vektor || '').includes('Lalat') && item.detail_data?.detail_lokasi" class="space-y-3">
            <span class="font-bold text-slate-700 dark:text-slate-300 block">Rincian Titik Survei Fly Grill (10 Pengamatan x 30 Detik):</span>
            <div class="grid grid-cols-1 gap-2.5">
              <div
                v-for="(loc, idx) in item.detail_data.detail_lokasi"
                :key="idx"
                class="p-3 rounded-xl bg-amber-50/50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60"
              >
                <div class="flex items-center justify-between mb-2 gap-2">
                  <span class="font-bold text-slate-800 dark:text-slate-200 text-xs">{{ idx + 1 }}. {{ loc.lokasi || 'Titik ' + (idx + 1) }}</span>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border" :class="getRiskBadgeClass(loc.status || (loc.rata_rata < 2 ? 'MS' : 'TMS'))">
                    Fly Index: {{ loc.rata_rata }} ({{ loc.status || (loc.rata_rata < 2 ? 'MS' : 'TMS') }})
                  </span>
                </div>
                <div class="flex items-center gap-1.5 overflow-x-auto text-[11px] font-mono">
                  <span class="text-slate-400 text-[10px] mr-1">T1-T10:</span>
                  <span v-for="(val, tI) in (loc.t || [])" :key="tI" class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">
                    {{ val }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div v-if="detailType === 'sivera' && (item.jenis_vektor || '').includes('Nyamuk') && item.detail_data?.tangkapan_dalam" class="space-y-2">
            <span class="font-bold text-slate-700 dark:text-slate-300 block">Matriks Penangkapan Umpan Orang Malam (18.00 - 06.00 WIB):</span>
            <div class="overflow-x-auto rounded-xl border border-purple-200 dark:border-purple-800">
              <table class="w-full text-center text-xs bg-white dark:bg-slate-900 font-mono">
                <thead>
                  <tr class="bg-purple-100/70 dark:bg-purple-950/60 font-sans font-bold text-[11px] text-purple-900 dark:text-purple-200">
                    <th class="py-1.5 px-2 text-left">Jam</th>
                    <th class="py-1.5 px-2">Dalam (D)</th>
                    <th class="py-1.5 px-2">Luar (L)</th>
                    <th class="py-1.5 px-2">Total</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-purple-100 dark:divide-purple-900/40">
                  <tr v-for="slot in anophelesJamSlots" :key="slot.key">
                    <td class="py-1 px-2 text-left font-sans text-slate-600 dark:text-slate-300">{{ slot.label }}</td>
                    <td class="py-1 px-2">{{ item.detail_data.tangkapan_dalam[slot.key] || 0 }}</td>
                    <td class="py-1 px-2">{{ item.detail_data.tangkapan_luar[slot.key] || 0 }}</td>
                    <td class="py-1 px-2 font-bold">{{ Number(item.detail_data.tangkapan_dalam[slot.key] || 0) + Number(item.detail_data.tangkapan_luar[slot.key] || 0) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div v-if="item.detail_data" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
            <span class="font-bold text-slate-700 dark:text-slate-300 block mb-2">Rincian Variabel Teknis Survei:</span>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px] font-mono">
              <div v-for="(v, k) in item.detail_data" :key="k" class="bg-white dark:bg-slate-900 p-2 rounded-lg border border-slate-100 dark:border-slate-800 break-words">
                <span class="text-slate-400 block">{{ k }}:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ typeof v === 'object' ? JSON.stringify(v) : v }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
        <div class="flex flex-wrap items-center gap-2">
          <a
            :href="`/prl/sivera/${item.id}/pdf`"
            target="_blank"
            class="px-3.5 py-2 text-xs font-bold rounded-xl bg-rose-600 text-white hover:bg-rose-700 transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer"
          >
            <Printer class="w-4 h-4" />
            <span>Cetak Laporan PDF SIVERA</span>
          </a>
          <a
            v-if="(item.jenis_vektor || '').includes('Jentik Aedes')"
            :href="`/prl/sivera/${item.id}/pdf/fogging`"
            target="_blank"
            class="px-3.5 py-2 text-xs font-bold rounded-xl bg-orange-600 text-white hover:bg-orange-700 transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer"
          >
            <Printer class="w-4 h-4" />
            <span>PDF Form Fogging</span>
          </a>
        </div>
        <button
          type="button"
          @click="emit('close')"
          class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-600 transition cursor-pointer"
        >
          Tutup
        </button>
      </div>
    </div>
  </ClinicalModal>
</template>
