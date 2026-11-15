<script setup>
import PaginationBar from '../../../Components/PaginationBar.vue';
import {
  Printer,
  ShieldCheck,
  Edit,
  Eye,
  Trash2,
  CheckCircle2,
  AlertTriangle,
  Check,
} from 'lucide-vue-next';
import { formatDate, getRiskBadgeClass, formatTindakanPengendalian } from '../utils';

defineProps({
  items: {
    type: Array,
    default: () => [],
  },
  meta: {
    type: Object,
    default: () => ({}),
  },
  perPage: {
    type: [Number, String],
    default: 25,
  },
});

const emit = defineEmits(['pengendalian', 'edit', 'detail', 'delete', 'change-per-page']);

const needsControl = (item) => {
  const s = (item?.baku_mutu_status || '').toLowerCase();
  return s.includes('tidak') || s.includes('atas');
};
</script>

<template>
  <div class="mt-4">
    <!-- Mobile Card View -->
    <div class="md:hidden space-y-3">
      <div
        v-for="(item, idx) in items"
        :key="`mob-siv-${item.id}`"
        class="p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs space-y-2.5 text-xs"
      >
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <div class="flex items-center gap-1.5 flex-wrap">
              <span class="text-[10px] font-mono text-slate-400">#{{ idx + 1 }}</span>
              <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ item.jenis_vektor }}</span>
              <span class="text-slate-300 dark:text-slate-600">&bull;</span>
              <span class="font-mono text-[11px] text-slate-500">{{ formatDate(item.tanggal) }}</span>
            </div>
            <p class="font-semibold text-slate-900 dark:text-slate-100 mt-0.5 truncate">{{ item.wilker }}</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ item.lokasi || '-' }}</p>
          </div>
          <span
            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border shrink-0"
            :class="getRiskBadgeClass(item.baku_mutu_status)"
          >
            {{ item.baku_mutu_status }}
          </span>
        </div>

        <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700 font-mono text-[11px] text-slate-700 dark:text-slate-300">
          {{ item.parameter_utama }}
        </div>

        <div class="flex flex-col gap-1">
          <div v-if="needsControl(item)">
            <span
              v-if="item.tindakan_pengendalian"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800"
            >
              <CheckCircle2 class="w-3 h-3 text-emerald-600" />
              <span>Sudah Dikendalikan</span>
            </span>
            <span
              v-else
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800"
            >
              <AlertTriangle class="w-3 h-3 text-rose-600" />
              <span>Perlu Pengendalian</span>
            </span>
          </div>
          <div v-else>
            <span
              v-if="item.tindakan_pengendalian"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200 dark:bg-teal-950/40 dark:text-teal-300 dark:border-teal-800"
            >
              <ShieldCheck class="w-3 h-3 text-teal-600" />
              <span>Pengendalian Rutin</span>
            </span>
            <span
              v-else
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700"
            >
              <Check class="w-3 h-3 text-slate-500" />
              <span>Baku Mutu Aman</span>
            </span>
          </div>
          <p v-if="item.tindakan_pengendalian" class="text-[11px] text-slate-600 dark:text-slate-300 line-clamp-2">
            {{ formatTindakanPengendalian(item.tindakan_pengendalian) }}
          </p>
        </div>

        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2 flex-wrap">
          <div class="flex items-center gap-1.5">
            <template v-if="(item.jenis_vektor || '').includes('Jentik Aedes')">
              <a
                :href="`/prl/sivera/${item.id}/pdf/jentik`"
                target="_blank"
                aria-label="Cetak PDF Form Pengendalian Jentik"
                title="Cetak PDF Form Pengendalian Jentik"
                class="p-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 dark:border-rose-900/40 dark:bg-rose-950/40 dark:text-rose-400 transition cursor-pointer"
              >
                <Printer class="w-3.5 h-3.5" />
              </a>
              <a
                :href="`/prl/sivera/${item.id}/pdf/fogging`"
                target="_blank"
                title="Cetak PDF Form Pengendalian Fogging"
                class="px-2 py-1 rounded-lg border border-orange-200 bg-orange-50 hover:bg-orange-100 text-orange-600 dark:border-orange-900/40 dark:bg-orange-950/40 dark:text-orange-400 text-[10px] font-bold transition cursor-pointer"
              >
                Fogging
              </a>
            </template>
            <a
              v-else
              :href="`/prl/sivera/${item.id}/pdf`"
              target="_blank"
              aria-label="Cetak Laporan PDF Pengendalian dan Surveilans"
              title="Cetak Laporan PDF Pengendalian & Surveilans"
              class="p-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 dark:border-rose-900/40 dark:bg-rose-950/40 dark:text-rose-400 transition cursor-pointer"
            >
              <Printer class="w-3.5 h-3.5" />
            </a>
            <button
              type="button"
              @click="emit('pengendalian', item)"
              class="px-2 py-1 rounded-lg border border-teal-200 bg-teal-50 hover:bg-teal-100 text-teal-700 dark:border-teal-800 dark:bg-teal-950/40 dark:text-teal-300 text-[10px] font-bold inline-flex items-center gap-1 transition cursor-pointer"
            >
              <ShieldCheck class="w-3 h-3" />
              <span>Kendalikan</span>
            </button>
          </div>
          <div class="flex items-center gap-1">
            <button
              type="button"
              @click="emit('edit', item)"
              aria-label="Edit Data Surveilans"
              title="Edit Data"
              class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-indigo-600 dark:text-indigo-400 transition cursor-pointer"
            >
              <Edit class="w-3.5 h-3.5" />
            </button>
            <button
              type="button"
              @click="emit('detail', item)"
              aria-label="Lihat Detail Surveilans"
              title="Lihat Detail"
              class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition cursor-pointer"
            >
              <Eye class="w-3.5 h-3.5" />
            </button>
            <button
              type="button"
              @click="emit('delete', item)"
              aria-label="Hapus Catatan Surveilans"
              title="Hapus Catatan"
              class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-500 transition cursor-pointer"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </div>

      <div v-if="items.length === 0" class="py-8 text-center text-xs text-slate-400 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
        Tidak ada data surveilans vektor SIVERA yang sesuai dengan filter.
      </div>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-xs text-left">
        <thead>
          <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
            <th class="py-3 px-3">No</th>
            <th class="py-3 px-3">Wilayah Kerja</th>
            <th class="py-3 px-3">Tanggal</th>
            <th class="py-3 px-3">Jenis Vektor</th>
            <th class="py-3 px-3">Lokasi / Titik</th>
            <th class="py-3 px-3">Hasil Indeks &amp; Parameter</th>
            <th class="py-3 px-3 text-center">Baku Mutu</th>
            <th class="py-3 px-3">Status Pengendalian</th>
            <th class="py-3 px-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-for="(item, idx) in items" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
            <td class="py-3 px-3 text-slate-400 font-mono">{{ idx + 1 }}</td>
            <td class="py-3 px-3 font-semibold text-slate-800 dark:text-slate-200">{{ item.wilker }}</td>
            <td class="py-3 px-3 font-mono text-slate-500">{{ formatDate(item.tanggal) }}</td>
            <td class="py-3 px-3 font-bold text-indigo-600 dark:text-indigo-400">{{ item.jenis_vektor }}</td>
            <td class="py-3 px-3 text-slate-600 dark:text-slate-300">{{ item.lokasi || '-' }}</td>
            <td class="py-3 px-3 font-mono">
              <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ item.parameter_utama }}</span>
            </td>
            <td class="py-3 px-3 text-center">
              <span
                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                :class="getRiskBadgeClass(item.baku_mutu_status)"
              >
                {{ item.baku_mutu_status }}
              </span>
            </td>
            <td class="py-3 px-3">
              <div class="flex flex-col gap-1 min-w-[190px] max-w-[240px]">
                <div v-if="needsControl(item)">
                  <span
                    v-if="item.tindakan_pengendalian"
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800"
                  >
                    <CheckCircle2 class="w-3 h-3 text-emerald-600" />
                    <span>Sudah Dikendalikan</span>
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800"
                  >
                    <AlertTriangle class="w-3 h-3 text-rose-600" />
                    <span>Perlu Pengendalian</span>
                  </span>
                </div>
                <div v-else>
                  <span
                    v-if="item.tindakan_pengendalian"
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200 dark:bg-teal-950/40 dark:text-teal-300 dark:border-teal-800"
                  >
                    <ShieldCheck class="w-3 h-3 text-teal-600" />
                    <span>Pengendalian Rutin</span>
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700"
                  >
                    <Check class="w-3 h-3 text-slate-500" />
                    <span>Baku Mutu Aman</span>
                  </span>
                </div>

                <div
                  v-if="item.tindakan_pengendalian"
                  class="text-[11px] text-slate-700 dark:text-slate-300"
                  :title="formatTindakanPengendalian(item.tindakan_pengendalian)"
                >
                  <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-[10.5px] leading-tight font-medium text-slate-700 dark:text-slate-200 line-clamp-2">
                    {{ formatTindakanPengendalian(item.tindakan_pengendalian) }}
                  </span>
                </div>
                <p v-else class="text-[10px] text-slate-400 italic">
                  {{ needsControl(item) ? 'Menunggu intervensi petugas' : 'Kondisi vektor terkendali' }}
                </p>
              </div>
            </td>
            <td class="py-3 px-3 text-center">
              <div class="inline-flex items-center gap-1">
                <div v-if="(item.jenis_vektor || '').includes('Jentik Aedes')" class="inline-flex items-center gap-0.5">
                  <a
                    :href="`/prl/sivera/${item.id}/pdf/jentik`"
                    target="_blank"
                    aria-label="Cetak PDF Form Pengendalian Jentik"
                    class="p-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 dark:border-rose-900/40 dark:bg-rose-950/40 dark:text-rose-400 transition cursor-pointer"
                    title="Cetak PDF Form Pengendalian Jentik"
                  >
                    <Printer class="w-3.5 h-3.5" />
                  </a>
                  <a
                    :href="`/prl/sivera/${item.id}/pdf/fogging`"
                    target="_blank"
                    class="px-1.5 py-1 rounded-lg border border-orange-200 bg-orange-50 hover:bg-orange-100 text-orange-600 dark:border-orange-900/40 dark:bg-orange-950/40 dark:text-orange-400 text-[10px] font-bold transition cursor-pointer"
                    title="Cetak PDF Form Pengendalian Fogging"
                  >
                    Fogging
                  </a>
                </div>
                <a
                  v-else
                  :href="`/prl/sivera/${item.id}/pdf`"
                  target="_blank"
                  aria-label="Cetak Laporan PDF Pengendalian & Surveilans"
                  class="p-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 dark:border-rose-900/40 dark:bg-rose-950/40 dark:text-rose-400 transition cursor-pointer"
                  title="Cetak Laporan PDF Pengendalian & Surveilans"
                >
                  <Printer class="w-3.5 h-3.5" />
                </a>

                <button
                  type="button"
                  @click="emit('pengendalian', item)"
                  class="px-2 py-1 rounded-lg border border-teal-200 bg-teal-50 hover:bg-teal-100 text-teal-700 dark:border-teal-800 dark:bg-teal-950/40 dark:text-teal-300 text-[10px] font-bold flex items-center gap-1 transition cursor-pointer"
                  title="Input / Update Tindakan Pengendalian"
                >
                  <ShieldCheck class="w-3 h-3" />
                  <span>Kendalikan</span>
                </button>
                <button
                  type="button"
                  @click="emit('edit', item)"
                  aria-label="Edit Data Surveilans"
                  class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-indigo-600 dark:text-indigo-400 transition cursor-pointer"
                  title="Edit Data"
                >
                  <Edit class="w-3.5 h-3.5" />
                </button>
                <button
                  type="button"
                  @click="emit('detail', item)"
                  aria-label="Lihat Detail Surveilans"
                  class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition cursor-pointer"
                  title="Lihat Detail"
                >
                  <Eye class="w-3.5 h-3.5" />
                </button>
                <button
                  type="button"
                  @click="emit('delete', item)"
                  aria-label="Hapus Catatan Surveilans"
                  class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-500 transition cursor-pointer"
                  title="Hapus Catatan"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="items.length === 0">
            <td colspan="9" class="text-center py-8 text-xs text-slate-400">Tidak ada data surveilans vektor SIVERA yang sesuai dengan filter.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination & Per-Page Controls -->
    <div
      v-if="meta?.total > 0"
      class="px-4 py-3.5 mt-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 rounded-b-3xl flex flex-col sm:flex-row items-center justify-between gap-3 text-xs"
    >
      <div class="text-slate-500 dark:text-slate-400 flex items-center gap-2 flex-wrap">
        <span>Menampilkan <strong class="text-slate-700 dark:text-slate-200">{{ meta.from || 0 }}</strong> - <strong class="text-slate-700 dark:text-slate-200">{{ meta.to || 0 }}</strong> dari <strong class="text-slate-700 dark:text-slate-200">{{ meta.total || 0 }}</strong> data</span>
        <span class="text-slate-300 dark:text-slate-600">|</span>
        <div class="flex items-center gap-1.5">
          <span class="text-[11px] text-slate-400">Tampilkan:</span>
          <select
            :value="perPage"
            @change="emit('change-per-page', $event.target.value)"
            class="py-1 px-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
          >
            <option :value="10">10 / hal</option>
            <option :value="25">25 / hal</option>
            <option :value="50">50 / hal</option>
            <option :value="100">100 / hal</option>
            <option :value="1000">Semua</option>
          </select>
        </div>
      </div>
      <PaginationBar
        :meta="meta"
        compact
        :preserve-scroll="false"
        active-class="bg-indigo-600 text-white font-bold shadow-xs"
      />
    </div>
  </div>
</template>
