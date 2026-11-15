<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { useUnsavedGuard } from '@/composables/useUnsavedGuard';
import {
  Ship,
  Plane,
  X,
  Filter,
  Search,
  ChevronDown,
  ChevronUp,
  Save,
} from 'lucide-vue-next';

const props = defineProps({
  editingRecord: {
    type: Object,
    default: null,
  },
  initialTransportType: {
    type: String,
    default: 'kapal',
  },
  defaultDate: {
    type: String,
    required: true,
  },
  defaultWilker: {
    type: String,
    required: true,
  },
  wilkerOptions: {
    type: Array,
    default: () => [],
  },
  dailySchema: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['close', 'saved']);

const isSaving = ref(false);
const editingId = ref(null);
const formTransportType = ref('kapal');
const formTanggal = ref(props.defaultDate);
const formWilker = ref(props.defaultWilker);
const formNotes = ref('');
const formMetrics = ref({});
const showOnlyRedFields = ref(false);
const formFieldSearch = ref('');
const collapsedGroups = ref({});

watch(
  () => [props.editingRecord, props.initialTransportType, props.defaultDate, props.defaultWilker],
  () => {
    if (props.editingRecord) {
      editingId.value = props.editingRecord.id;
      formTransportType.value = props.editingRecord.transport_type;
      formTanggal.value = props.editingRecord.tanggal;
      formWilker.value = props.editingRecord.wilker;
      formNotes.value = props.editingRecord.notes || '';
      formMetrics.value = { ...(props.editingRecord.metrics || {}) };
    } else {
      editingId.value = null;
      formTransportType.value = props.initialTransportType || 'kapal';
      formTanggal.value = props.defaultDate;
      formWilker.value = props.defaultWilker;
      formNotes.value = '';
      formMetrics.value = {};
    }
    formFieldSearch.value = '';
    collapsedGroups.value = {};
  },
  { immediate: true },
);

const closeForm = () => {
  if (isSaving.value) return;
  emit('close');
};

const activeFormSchema = computed(() => {
  return props.dailySchema?.[formTransportType.value] || { groups: [], fields: [] };
});

const groupedFormSections = computed(() => {
  const groups = activeFormSchema.value.groups || [];
  const fields = activeFormSchema.value.fields || [];
  const q = formFieldSearch.value.trim().toLowerCase();

  return groups
    .map((group) => {
      const groupFields = fields.filter((f) => {
        if (f.group !== group.id) return false;
        if (showOnlyRedFields.value && !f.is_red) return false;
        if (q) {
          const matchLabel = f.label?.toLowerCase().includes(q);
          const matchCol = f.excel_col?.toLowerCase().includes(q);
          const matchCode = (f.sinkarkes_code || '').toLowerCase().includes(q);
          const matchMark = (f.sinkarkes_excel_mark || '').toLowerCase().includes(q);
          return matchLabel || matchCol || matchCode || matchMark;
        }
        return true;
      });
      return {
        ...group,
        fields: groupFields,
        redCount: groupFields.filter(f => f.is_red).length,
      };
    })
    .filter(g => g.fields.length > 0);
});

const toggleGroup = (groupId) => {
  collapsedGroups.value[groupId] = !collapsedGroups.value[groupId];
};

const sanitizeMetricValue = (key, raw) => {
  if (raw === '' || raw === null || raw === undefined) {
    delete formMetrics.value[key];
    return;
  }
  const parsed = parseInt(raw, 10);
  if (isNaN(parsed) || parsed <= 0) {
    delete formMetrics.value[key];
  } else {
    formMetrics.value[key] = parsed;
  }
};

const filledMetricCount = computed(() => Object.keys(formMetrics.value).length);

const formRedImpactPreview = computed(() => {
  const fields = (activeFormSchema.value.fields || []).filter(f => f.is_red);
  const byTarget = new Map();
  const pairs = new Map();

  fields.forEach((f) => {
    const val = Number(formMetrics.value[f.key] || 0);
    if (f.dedupe_pair) {
      const pKey = `${f.dedupe_pair}|${f.sinkarkes_target_row}`;
      const existing = pairs.get(pKey) || { field: f, values: [] };
      existing.values.push(val);
      pairs.set(pKey, existing);
    } else {
      const cur = byTarget.get(f.sinkarkes_target_row) || { field: f, total: 0 };
      cur.total += val;
      byTarget.set(f.sinkarkes_target_row, cur);
    }
  });

  pairs.forEach(({ field, values }) => {
    const maxVal = Math.max(...values, 0);
    const cur = byTarget.get(field.sinkarkes_target_row) || { field, total: 0 };
    cur.total += maxVal;
    byTarget.set(field.sinkarkes_target_row, cur);
  });

  return Array.from(byTarget.values()).filter(item => item.total > 0);
});

useUnsavedGuard(
  () => Object.keys(formMetrics.value).length > 0 || Boolean(formNotes.value?.trim()),
  {
    message: 'Formulir rekap harian SINKARKES masih terbuka dan memiliki isian yang belum disimpan. Yakin ingin meninggalkan halaman ini?',
  },
);

const saveDailyRecord = () => {
  if (isSaving.value) return;
  if (!formTanggal.value || !formWilker.value) {
    toast.error('Tanggal dan Wilayah Kerja wajib diisi');
    return;
  }

  isSaving.value = true;
  router.post('/sinkarkes-daily', {
    id: editingId.value,
    tanggal: formTanggal.value,
    wilker: formWilker.value,
    transport_type: formTransportType.value,
    metrics: formMetrics.value,
    notes: formNotes.value || null,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      isSaving.value = false;
      toast.success('Data Harian Disimpan & Diakumulasikan', {
        description: `Otomatis memperbarui Formulir SINKARKES ${formWilker.value}`,
      });
      emit('saved');
    },
    onError: (errors) => {
      isSaving.value = false;
      toast.error('Gagal menyimpan data harian', {
        description: Object.values(errors).flat().join(', ') || 'Periksa isian formulir.',
      });
    },
  });
};
</script>

<template>
  <div class="bg-white dark:bg-[#12161f] border-2 border-[#00A99D]/50 rounded-2xl shadow-md overflow-hidden">
    <div class="px-5 py-3.5 bg-gradient-to-r from-[#005851] to-[#00A99D] text-white flex flex-wrap items-center justify-between gap-3">
      <div class="flex items-center gap-2.5">
        <Ship v-if="formTransportType === 'kapal'" class="w-5 h-5" />
        <Plane v-else class="w-5 h-5" />
        <div>
          <h2 class="text-sm sm:text-base font-bold">
            {{ editingId ? 'Edit Data Harian' : 'Input Data Harian Baru' }} &mdash;
            {{ formTransportType === 'kapal' ? 'Kapal (Sinkarkes kapal mentah)' : 'Pesawat (Sinkarkes pesawat mentah)' }}
          </h2>
          <p class="text-[11px] text-white/80">
            Poin bertanda label merah otomatis terakumulasi ke Formulir Laporan SINKARKES {{ formWilker }}.
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <div v-if="!editingId" class="inline-flex bg-black/20 rounded-xl p-0.5 text-xs font-bold">
          <button
            type="button"
            @click="formTransportType = 'kapal'; formMetrics = {}"
            class="px-3 py-1 rounded-lg transition cursor-pointer"
            :class="formTransportType === 'kapal' ? 'bg-white text-[#005851]' : 'text-white/80 hover:text-white'"
          >Kapal</button>
          <button
            type="button"
            @click="formTransportType = 'pesawat'; formMetrics = {}"
            class="px-3 py-1 rounded-lg transition cursor-pointer"
            :class="formTransportType === 'pesawat' ? 'bg-white text-sky-700' : 'text-white/80 hover:text-white'"
          >Pesawat</button>
        </div>

        <button type="button" @click="closeForm" aria-label="Tutup formulir rekap harian" title="Tutup" class="p-1.5 rounded-lg hover:bg-white/20 transition cursor-pointer">
          <X class="w-4 h-4" />
        </button>
      </div>
    </div>

    <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-[#171d29] space-y-3">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Tanggal</label>
          <input
            v-model="formTanggal"
            type="date"
            class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#12161f] text-slate-900 dark:text-white focus:ring-2 focus:ring-[#00A99D] outline-none"
          />
        </div>

        <div>
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Nama Balai Karkes / Wilker</label>
          <select
            v-model="formWilker"
            class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#12161f] text-slate-900 dark:text-white focus:ring-2 focus:ring-[#00A99D] outline-none"
          >
            <option v-for="w in wilkerOptions" :key="w" :value="w">{{ w }}</option>
          </select>
        </div>

        <div>
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Catatan Harian (Opsional)</label>
          <input
            v-model="formNotes"
            type="text"
            placeholder="Misal: Keterangan shift / kejadian khusus..."
            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#12161f] text-slate-900 dark:text-white focus:ring-2 focus:ring-[#00A99D] outline-none"
          />
        </div>
      </div>

      <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
        <div class="flex flex-wrap items-center gap-2">
          <button
            type="button"
            @click="showOnlyRedFields = !showOnlyRedFields"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border transition-all cursor-pointer"
            :class="showOnlyRedFields
              ? 'bg-rose-600 text-white border-rose-600 shadow-2xs'
              : 'bg-white dark:bg-[#12161f] text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800 hover:bg-rose-50'"
          >
            <Filter class="w-3.5 h-3.5" />
            <span>
              {{ showOnlyRedFields
                ? `Menampilkan Hanya Poin Merah SINKARKES (${activeFormSchema.red_mapped_count || 0})`
                : `Tampilkan Hanya Poin Merah SINKARKES (${activeFormSchema.red_mapped_count || 0})` }}
            </span>
          </button>

          <span class="text-[11px] text-slate-500">
            Terisi: <strong class="text-slate-800 dark:text-slate-200">{{ filledMetricCount }}</strong> kolom
            (Total {{ activeFormSchema.total_metric_columns || 0 }} kolom Excel)
          </span>
        </div>

        <div class="relative w-full sm:w-64">
          <Search class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            v-model="formFieldSearch"
            type="text"
            placeholder="Cari nama kolom / kode SINKARKES..."
            class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#12161f] text-slate-900 dark:text-white focus:ring-2 focus:ring-[#00A99D] outline-none"
          />
        </div>
      </div>
    </div>

    <div class="max-h-[68vh] overflow-y-auto overflow-x-auto bg-white dark:bg-[#12161f]">
      <table class="w-full min-w-[680px] text-left text-xs border-collapse">
        <thead class="sticky top-0 z-10 bg-slate-100 dark:bg-[#161c28] text-slate-600 dark:text-slate-300 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200 dark:border-slate-800 shadow-2xs">
          <tr>
            <th class="py-2.5 px-3 w-16 text-center whitespace-nowrap">Kolom</th>
            <th class="py-2.5 px-3 sm:px-4 min-w-[260px]">Uraian Indikator Harian</th>
            <th class="py-2.5 px-3 sm:px-4 w-72 whitespace-nowrap">Masuk ke SINKARKES</th>
            <th class="py-2.5 px-3 sm:px-5 w-44 text-right whitespace-nowrap bg-slate-200/50 dark:bg-[#1a2130]">
              Input Nilai ({{ formTanggal || 'Harian' }})
            </th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70">
          <tr v-if="groupedFormSections.length === 0">
            <td colspan="4" class="px-6 py-10 text-center text-slate-400">
              Tidak ada indikator yang cocok dengan filter pencarian saat ini.
            </td>
          </tr>

          <template v-for="sec in groupedFormSections" :key="sec.id">
            <tr
              @click="toggleGroup(sec.id)"
              class="bg-slate-50/95 dark:bg-[#171d29] hover:bg-slate-100 dark:hover:bg-slate-800/80 border-t-2 border-slate-200 dark:border-slate-700 cursor-pointer select-none transition-colors"
            >
              <td colspan="4" class="py-2.5 px-3 sm:px-4">
                <div class="flex items-center justify-between gap-2">
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="w-2 h-2 rounded-full bg-[#00A99D]"></span>
                    <span class="text-xs font-extrabold uppercase tracking-wide text-slate-800 dark:text-slate-100">
                      {{ sec.title }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">
                      {{ sec.fields.length }} baris
                    </span>
                    <span
                      v-if="sec.redCount > 0"
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300"
                    >
                      {{ sec.redCount }} poin SINKARKES
                    </span>
                  </div>
                  <ChevronDown v-if="collapsedGroups[sec.id]" class="w-4 h-4 text-slate-400 shrink-0" />
                  <ChevronUp v-else class="w-4 h-4 text-slate-400 shrink-0" />
                </div>
              </td>
            </tr>

            <template v-if="!collapsedGroups[sec.id]">
              <tr
                v-for="field in sec.fields"
                :key="field.key"
                class="transition-colors"
                :class="field.is_red
                  ? 'bg-rose-50/35 dark:bg-rose-950/10 hover:bg-rose-50/70 dark:hover:bg-rose-950/25 border-l-4 border-l-rose-500'
                  : 'hover:bg-slate-50/80 dark:hover:bg-[#171d29]/50'"
              >
                <td class="py-2 px-3 text-center whitespace-nowrap">
                  <span
                    class="inline-flex items-center justify-center min-w-[2.2rem] px-1.5 py-0.5 rounded font-mono text-[11px] font-bold border"
                    :class="field.is_red
                      ? 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800'
                      : 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'"
                  >
                    {{ field.excel_col }}
                  </span>
                </td>

                <td class="py-2 px-3 sm:px-4">
                  <label :for="`daily-field-${field.key}`" class="block font-semibold text-slate-800 dark:text-slate-100 leading-snug cursor-pointer">
                    {{ field.label }}
                  </label>
                  <span v-if="field.dedupe_pair" class="inline-block mt-0.5 text-[10px] font-medium text-amber-700 dark:text-amber-400">
                    * Anti-ganda otomatis dengan pasangan kapal/dokumen
                  </span>
                </td>

                <td class="py-2 px-3 sm:px-4">
                  <div v-if="field.is_red" class="space-y-0.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-600 text-white">
                      Baris {{ field.sinkarkes_excel_mark }} &bull; {{ field.sinkarkes_code }}
                    </span>
                    <div class="text-[11px] text-rose-700 dark:text-rose-300 leading-tight">
                      {{ field.sinkarkes_label }}
                    </div>
                  </div>
                  <span v-else class="text-[11px] text-slate-400 italic">
                    Rekap harian saja
                  </span>
                </td>

                <td class="py-2 px-3 sm:px-5 text-right bg-slate-50/40 dark:bg-[#161c28]/40">
                  <input
                    :id="`daily-field-${field.key}`"
                    :value="formMetrics[field.key] ?? ''"
                    @input="(e) => sanitizeMetricValue(field.key, e.target.value)"
                    type="number"
                    min="0"
                    placeholder="0"
                    class="w-32 sm:w-36 ml-auto px-3 py-1.5 text-sm font-bold text-right rounded-lg border bg-white dark:bg-[#0e121a] text-slate-900 dark:text-white focus:ring-2 focus:ring-[#00A99D] outline-none transition"
                    :class="field.is_red ? 'border-rose-300 dark:border-rose-700' : 'border-slate-200 dark:border-slate-700'"
                  />
                </td>
              </tr>
            </template>
          </template>
        </tbody>
      </table>
    </div>

    <div class="px-5 py-3.5 bg-slate-50 dark:bg-[#171d29] border-t border-slate-200 dark:border-slate-800 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
      <div class="flex flex-wrap items-center gap-1.5 min-w-0">
        <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300 mr-1">Kontribusi ke SINKARKES dari entri ini:</span>
        <span v-if="formRedImpactPreview.length === 0" class="text-[11px] text-slate-400 italic">Belum ada poin SINKARKES terisi (&gt;0)</span>
        <span
          v-for="item in formRedImpactPreview.slice(0, 8)"
          :key="item.field.sinkarkes_target_row"
          class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800"
        >
          {{ item.field.sinkarkes_code }}: +{{ item.total }}
        </span>
        <span v-if="formRedImpactPreview.length > 8" class="text-[10px] font-semibold text-rose-600">
          +{{ formRedImpactPreview.length - 8 }} indikator lain
        </span>
      </div>

      <div class="flex items-center gap-2 shrink-0 justify-end">
        <button
          type="button"
          @click="closeForm"
          class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 transition cursor-pointer"
        >Batal</button>
        <button
          type="button"
          @click="saveDailyRecord"
          :disabled="isSaving"
          class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl text-xs font-bold text-white bg-[#00A99D] hover:bg-[#008f85] disabled:opacity-50 shadow-sm transition cursor-pointer"
        >
          <Save class="w-4 h-4" />
          <span>{{ isSaving ? 'Menyimpan...' : 'Simpan & Akumulasikan ke SINKARKES' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>
