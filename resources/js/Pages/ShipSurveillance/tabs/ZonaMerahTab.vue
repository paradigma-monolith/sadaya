<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import { useDate } from '../../../composables/useDate';
import {
  AlertTriangle,
  CheckCircle2,
  X,
  RefreshCw,
  UploadCloud,
  ExternalLink,
  FileText,
  Info,
  Save,
  Globe,
  Search,
  Flag,
  Clock,
} from 'lucide-vue-next';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ countries: [], summary: { total: 0, red_count: 0, safe_count: 0 }, logs: [] }),
  },
});

const emit = defineEmits(['update:modelValue']);

const { formatDate } = useDate();

const redZonesState = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
});

const pdfFileInput = ref(null);
const selectedPdfFile = ref(null);
const pdfImportMode = ref('replace');
const isParsingPdf = ref(false);
const isApplyingRedZones = ref(false);
const pdfImportError = ref('');
const pdfImportSuccess = ref('');
const pdfPreview = ref(null);
const previewSelectedRed = ref(new Set());
const previewSelectedNew = ref(new Set());

const countrySearch = ref('');
const countryFilterStatus = ref('all'); // 'all' | 'red' | 'safe'
const togglingCountryId = ref(null);
const newCountryName = ref('');
const newCountryIsRed = ref(true);
const isAddingCountry = ref(false);
const isResettingCountries = ref(false);

const filteredRedZoneCountries = computed(() => {
  const q = countrySearch.value.trim().toLowerCase();
  return (redZonesState.value.countries || []).filter((c) => {
    if (countryFilterStatus.value === 'red' && !c.is_red_zone) return false;
    if (countryFilterStatus.value === 'safe' && c.is_red_zone) return false;
    if (q && !c.name.toLowerCase().includes(q) && !(c.source_label || '').toLowerCase().includes(q)) {
      return false;
    }
    return true;
  });
});

const handlePdfFileChange = (event) => {
  const file = event.target?.files?.[0] || null;
  selectedPdfFile.value = file;
  pdfImportError.value = '';
  pdfImportSuccess.value = '';
  pdfPreview.value = null;
};

const analyzePdfFile = async () => {
  if (!selectedPdfFile.value) {
    pdfImportError.value = 'Pilih berkas PDF terlebih dahulu.';
    return;
  }
  isParsingPdf.value = true;
  pdfImportError.value = '';
  pdfImportSuccess.value = '';
  try {
    const formData = new FormData();
    formData.append('file', selectedPdfFile.value);
    formData.append('mode', pdfImportMode.value);

    const res = await axios.post('/ship-surveillance/red-zones/preview-pdf', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    if (res.data?.success && res.data?.preview) {
      pdfPreview.value = res.data.preview;
      previewSelectedRed.value = new Set(res.data.preview.recommended_red_countries || []);
      previewSelectedNew.value = new Set();
    } else {
      pdfImportError.value = res.data?.message || 'Gagal menganalisis berkas PDF.';
    }
  } catch (err) {
    pdfImportError.value = err?.response?.data?.message || 'Terjadi kesalahan saat membaca berkas PDF.';
  } finally {
    isParsingPdf.value = false;
  }
};

const togglePreviewRed = (countryName) => {
  const next = new Set(previewSelectedRed.value);
  if (next.has(countryName)) {
    next.delete(countryName);
  } else {
    next.add(countryName);
  }
  previewSelectedRed.value = next;
};

const togglePreviewNewCountry = (territoryName) => {
  const next = new Set(previewSelectedNew.value);
  if (next.has(territoryName)) {
    next.delete(territoryName);
  } else {
    next.add(territoryName);
  }
  previewSelectedNew.value = next;
};

const toggleAllUnmatchedNew = (selectAll) => {
  if (!selectAll) {
    previewSelectedNew.value = new Set();
    return;
  }
  previewSelectedNew.value = new Set(pdfPreview.value?.unmatched_countries || []);
};

const excludeIndonesiaFromPreview = () => {
  const next = new Set(previewSelectedRed.value);
  next.delete('Indonesia');
  previewSelectedRed.value = next;
};

const applyPdfRedZones = async () => {
  if (!pdfPreview.value) return;
  isApplyingRedZones.value = true;
  pdfImportError.value = '';
  pdfImportSuccess.value = '';
  try {
    const sourceLabelMap = {};
    (pdfPreview.value.matched_records || []).forEach((rec) => {
      sourceLabelMap[rec.canonical_name] = rec.pdf_name;
    });

    const res = await axios.post('/ship-surveillance/red-zones/apply', {
      red_countries: Array.from(previewSelectedRed.value),
      new_countries: Array.from(previewSelectedNew.value),
      mode: pdfPreview.value.mode || pdfImportMode.value,
      source_filename: pdfPreview.value.filename,
      extracted_countries: pdfPreview.value.extracted_countries || [],
      source_label_map: sourceLabelMap,
    });

    if (res.data?.success) {
      redZonesState.value = res.data.data;
      pdfImportSuccess.value = res.data.message || 'Daftar negara Zona Merah berhasil diperbarui.';
      pdfPreview.value = null;
      selectedPdfFile.value = null;
      if (pdfFileInput.value) {
        pdfFileInput.value.value = '';
      }
    }
  } catch (err) {
    pdfImportError.value = err?.response?.data?.message || 'Gagal menyimpan pembaruan Zona Merah.';
  } finally {
    isApplyingRedZones.value = false;
  }
};

const toggleSingleCountryRedZone = async (country) => {
  if (!country?.id) return;
  togglingCountryId.value = country.id;
  pdfImportError.value = '';
  try {
    const res = await axios.patch(`/ship-surveillance/red-zones/${country.id}/toggle`, {
      is_red_zone: !country.is_red_zone,
    });
    if (res.data?.success && res.data?.data) {
      redZonesState.value = res.data.data;
      pdfImportSuccess.value = res.data.message;
    }
  } catch (err) {
    pdfImportError.value = err?.response?.data?.message || 'Gagal mengubah status negara.';
  } finally {
    togglingCountryId.value = null;
  }
};

const addManualCountry = async () => {
  const trimmed = newCountryName.value.trim();
  if (!trimmed) return;
  isAddingCountry.value = true;
  pdfImportError.value = '';
  try {
    const res = await axios.post('/ship-surveillance/red-zones/country', {
      name: trimmed,
      is_red_zone: newCountryIsRed.value,
    });
    if (res.data?.success && res.data?.data) {
      redZonesState.value = res.data.data;
      pdfImportSuccess.value = res.data.message;
      newCountryName.value = '';
    }
  } catch (err) {
    pdfImportError.value = err?.response?.data?.message || 'Gagal menambahkan negara baru.';
  } finally {
    isAddingCountry.value = false;
  }
};

const resetDefaultRedZones = async () => {
  if (!confirm('Kembalikan status Zona Merah untuk 195 negara ke data referensi awal?')) return;
  isResettingCountries.value = true;
  pdfImportError.value = '';
  try {
    const res = await axios.post('/ship-surveillance/red-zones/reset');
    if (res.data?.success && res.data?.data) {
      redZonesState.value = res.data.data;
      pdfImportSuccess.value = res.data.message;
    }
  } catch (err) {
    pdfImportError.value = err?.response?.data?.message || 'Gagal mereset daftar negara.';
  } finally {
    isResettingCountries.value = false;
  }
};
</script>

<template>
  <div class="space-y-3">
    <!-- Alerts -->
    <div v-if="pdfImportError" class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <AlertTriangle class="w-4 h-4 shrink-0" />
        <span>{{ pdfImportError }}</span>
      </div>
      <button type="button" @click="pdfImportError = ''" class="hover:opacity-75 cursor-pointer"><X class="w-4 h-4" /></button>
    </div>

    <div v-if="pdfImportSuccess" class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs flex items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <CheckCircle2 class="w-4 h-4 shrink-0" />
        <span>{{ pdfImportSuccess }}</span>
      </div>
      <button type="button" @click="pdfImportSuccess = ''" class="hover:opacity-75 cursor-pointer"><X class="w-4 h-4" /></button>
    </div>

    <!-- Ringkasan & Aksi Cepat -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
      <div class="clinical-kpi">
        <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total Negara Terdaftar</div>
        <div class="mt-1 text-xl font-bold clinical-num text-slate-900">{{ redZonesState.summary?.total || 0 }}</div>
        <div class="text-[11px] text-slate-400 mt-0.5">Tersedia pada form MDH Luar Negeri</div>
      </div>
      <div class="clinical-kpi border-rose-200 bg-rose-50/40">
        <div class="text-[11px] font-semibold uppercase tracking-wider text-rose-600">Negara Zona Merah (+20)</div>
        <div class="mt-1 text-xl font-bold clinical-num text-rose-600">{{ redZonesState.summary?.red_count || 0 }}</div>
        <div class="text-[11px] text-slate-500 mt-0.5">Otomatis +20 skor pada Q1 Internasional</div>
      </div>
      <div class="clinical-kpi border-emerald-200 bg-emerald-50/40">
        <div class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600">Negara Non-Red Zone (0)</div>
        <div class="mt-1 text-xl font-bold clinical-num text-emerald-600">{{ redZonesState.summary?.safe_count || 0 }}</div>
        <div class="text-[11px] text-slate-500 mt-0.5">Tidak terjangkit / skor 0 pada Q1</div>
      </div>
      <div class="clinical-kpi flex flex-col justify-between">
        <div>
          <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Pembaruan PDF Terakhir</div>
          <div v-if="redZonesState.logs?.length" class="mt-1 text-xs font-semibold text-slate-800 line-clamp-1" :title="redZonesState.logs[0].filename">
            {{ redZonesState.logs[0].filename }}
          </div>
          <div v-else class="mt-1 text-xs text-slate-400">Belum pernah import PDF (data default)</div>
        </div>
        <div class="mt-1.5 flex items-center justify-between gap-2">
          <span v-if="redZonesState.logs?.length" class="text-[10px] text-slate-400 clinical-num">{{ formatDate(redZonesState.logs[0].created_at) }}</span>
          <button
            type="button"
            @click="resetDefaultRedZones"
            :disabled="isResettingCountries"
            class="text-[11px] font-semibold text-slate-500 hover:text-rose-600 transition inline-flex items-center gap-1 ml-auto cursor-pointer"
          >
            <RefreshCw class="w-3 h-3" :class="{ 'animate-spin': isResettingCountries }" />
            Reset Default
          </button>
        </div>
      </div>
    </div>

    <!-- Panel Upload & Preview PDF Kemenkes -->
    <div class="clinical-surface p-4 space-y-3.5">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 pb-3">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center shrink-0">
            <UploadCloud class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-sm font-bold text-slate-900">Import &amp; Update Zona Merah dari PDF Kemenkes (Kolom NEGARA)</h3>
            <p class="text-xs text-slate-500">
              Unggah dokumen PDF <em>Daftar Negara/Wilayah Terjangkit Penyakit Infeksi Emerging</em>. Sistem akan membaca kolom <strong>NEGARA</strong> secara otomatis di semua halaman.
            </p>
          </div>
        </div>
        <a
          href="/ship-surveillance/assessment/international"
          target="_blank"
          class="clinical-btn-secondary py-1.5 px-2.5 text-xs shrink-0"
        >
          <ExternalLink class="w-3.5 h-3.5 text-[#00A99D]" />
          Cek Form Luar Negeri ↗
        </a>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
        <div class="lg:col-span-5">
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
            Pilih File PDF Daftar Negara Terjangkit (.pdf, maks. 10 MB)
          </label>
          <input
            ref="pdfFileInput"
            type="file"
            accept="application/pdf,.pdf"
            @change="handlePdfFileChange"
            class="w-full text-xs text-slate-600 dark:text-slate-300 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#005851] file:text-white hover:file:opacity-90 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 p-1.5 cursor-pointer"
          />
        </div>

        <div class="lg:col-span-5">
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mode Pembaruan</label>
          <div class="grid grid-cols-2 gap-2">
            <label
              :class="[
                'flex items-center gap-2 p-2.5 rounded-xl border text-xs cursor-pointer transition',
                pdfImportMode === 'replace'
                  ? 'border-[#00A99D] bg-teal-50 dark:bg-teal-950/30 text-[#005851] dark:text-[#00A99D] font-bold'
                  : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300'
              ]"
            >
              <input type="radio" v-model="pdfImportMode" value="replace" class="text-[#00A99D]" />
              <span>Ganti Total Sesuai PDF</span>
            </label>
            <label
              :class="[
                'flex items-center gap-2 p-2.5 rounded-xl border text-xs cursor-pointer transition',
                pdfImportMode === 'merge'
                  ? 'border-[#00A99D] bg-teal-50 dark:bg-teal-950/30 text-[#005851] dark:text-[#00A99D] font-bold'
                  : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300'
              ]"
            >
              <input type="radio" v-model="pdfImportMode" value="merge" class="text-[#00A99D]" />
              <span>Tambahkan Saja (Merge)</span>
            </label>
          </div>
        </div>

        <div class="lg:col-span-2">
          <button
            type="button"
            @click="analyzePdfFile"
            :disabled="!selectedPdfFile || isParsingPdf"
            class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-[#005851] to-[#00A99D] text-white text-xs font-bold shadow hover:opacity-95 disabled:opacity-50 transition inline-flex items-center justify-center gap-1.5 cursor-pointer"
          >
            <RefreshCw v-if="isParsingPdf" class="w-3.5 h-3.5 animate-spin" />
            <FileText v-else class="w-3.5 h-3.5" />
            {{ isParsingPdf ? 'Menganalisis...' : 'Analisis PDF' }}
          </button>
        </div>
      </div>

      <!-- Interactive Preview Results -->
      <div v-if="pdfPreview" class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-800 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 flex items-center gap-2">
              <CheckCircle2 class="w-4 h-4 text-[#00A99D]" />
              Pratinjau Hasil Pembacaan PDF ({{ pdfPreview.pages_parsed }} halaman)
            </h4>
            <p class="text-[11px] text-slate-500 mt-0.5">
              Periksa daftar negara sebelum menerapkan perubahan ke aplikasi TANGKAP &amp; form MDH Luar Negeri.
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold">
            <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
              Diekstrak: {{ pdfPreview.total_extracted }}
            </span>
            <span class="px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-700 dark:text-rose-300">
              Baru Merah: +{{ pdfPreview.newly_flagged?.length || 0 }}
            </span>
            <span class="px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-700 dark:text-amber-300">
              Tetap Merah: {{ pdfPreview.already_red?.length || 0 }}
            </span>
            <span v-if="pdfPreview.mode === 'replace'" class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-300">
              Keluar Zona Merah: {{ pdfPreview.to_unflag?.length || 0 }}
            </span>
            <span v-if="pdfPreview.unmatched_countries?.length" class="px-2.5 py-1 rounded-full bg-cyan-500/10 text-cyan-700 dark:text-cyan-300">
              Entitas Baru: {{ pdfPreview.unmatched_countries.length }}
            </span>
          </div>
        </div>

        <!-- Notice if Indonesia was found in Kemenkes PDF -->
        <div
          v-if="pdfPreview.indonesia_in_pdf && previewSelectedRed.has('Indonesia')"
          class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-800 dark:text-amber-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2"
        >
          <div class="flex items-start gap-2">
            <Info class="w-4 h-4 shrink-0 mt-0.5 text-amber-600" />
            <span>
              Kolom <strong>NEGARA</strong> pada PDF mencantumkan <strong>Indonesia</strong>. Jika form Kedatangan Luar Negeri harus tetap mengecualikan Indonesia dari status Zona Merah (+20 skor), klik tombol di samping.
            </span>
          </div>
          <button
            type="button"
            @click="excludeIndonesiaFromPreview"
            class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-amber-400 text-amber-700 dark:text-amber-300 font-bold text-[11px] hover:bg-amber-50 shrink-0 cursor-pointer"
          >
            Kecualikan Indonesia (Tetap Aman)
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Baru Ditandai Merah -->
          <div class="rounded-xl border border-rose-200 dark:border-rose-900/50 bg-rose-50/40 dark:bg-rose-950/10 p-3.5 space-y-2">
            <div class="text-xs font-bold text-rose-700 dark:text-rose-300 flex items-center justify-between">
              <span>Baru Ditandai Zona Merah ({{ pdfPreview.newly_flagged?.length || 0 }})</span>
            </div>
            <div v-if="!pdfPreview.newly_flagged?.length" class="text-[11px] text-slate-400 italic">
              Tidak ada penambahan negara zona merah baru.
            </div>
            <div v-else class="max-h-52 overflow-y-auto space-y-1 pr-1">
              <label
                v-for="item in pdfPreview.newly_flagged"
                :key="item.canonical_name"
                class="flex items-center justify-between gap-2 p-1.5 rounded-lg bg-white/80 dark:bg-slate-900/60 border border-rose-100 dark:border-rose-900/40 text-xs cursor-pointer"
              >
                <span class="flex items-center gap-2 min-w-0">
                  <input
                    type="checkbox"
                    :checked="previewSelectedRed.has(item.canonical_name)"
                    @change="togglePreviewRed(item.canonical_name)"
                    class="rounded text-rose-600"
                  />
                  <span class="font-semibold text-slate-800 dark:text-slate-100 truncate">{{ item.canonical_name }}</span>
                </span>
                <span v-if="item.pdf_name !== item.canonical_name" class="text-[10px] text-slate-400 shrink-0">
                  PDF: {{ item.pdf_name }}
                </span>
              </label>
            </div>
          </div>

          <!-- Keluar Zona Merah / Menjadi Aman -->
          <div class="rounded-xl border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50/40 dark:bg-emerald-950/10 p-3.5 space-y-2">
            <div class="text-xs font-bold text-emerald-700 dark:text-emerald-300 flex items-center justify-between">
              <span>
                {{ pdfPreview.mode === 'replace' ? 'Tidak Ada di PDF → Menjadi Zona Aman' : 'Zona Merah Eksisting Dipertahankan' }}
                ({{ pdfPreview.to_unflag?.length || 0 }})
              </span>
            </div>
            <div v-if="!pdfPreview.to_unflag?.length" class="text-[11px] text-slate-400 italic">
              Semua negara zona merah sebelumnya tercakup di PDF ini.
            </div>
            <div v-else class="max-h-52 overflow-y-auto space-y-1 pr-1">
              <label
                v-for="item in pdfPreview.to_unflag"
                :key="item.canonical_name"
                class="flex items-center justify-between gap-2 p-1.5 rounded-lg bg-white/80 dark:bg-slate-900/60 border border-emerald-100 dark:border-emerald-900/40 text-xs cursor-pointer"
              >
                <span class="flex items-center gap-2 min-w-0">
                  <input
                    type="checkbox"
                    :checked="previewSelectedRed.has(item.canonical_name)"
                    @change="togglePreviewRed(item.canonical_name)"
                    class="rounded text-rose-600"
                  />
                  <span class="font-semibold text-slate-800 dark:text-slate-100 truncate">{{ item.canonical_name }}</span>
                </span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 shrink-0">
                  {{ previewSelectedRed.has(item.canonical_name) ? 'Tetap Merah' : 'Jadi Aman' }}
                </span>
              </label>
            </div>
          </div>
        </div>

        <!-- Unmatched territories / new entities -->
        <div v-if="pdfPreview.unmatched_countries?.length" class="rounded-xl border border-cyan-200 dark:border-cyan-900/50 bg-cyan-50/40 dark:bg-cyan-950/10 p-3.5 space-y-2">
          <div class="flex items-center justify-between gap-2">
            <span class="text-xs font-bold text-cyan-800 dark:text-cyan-300">
              Wilayah / Entitas Baru di PDF Belum Terdaftar ({{ pdfPreview.unmatched_countries.length }})
            </span>
            <div class="flex items-center gap-2 text-[11px]">
              <button type="button" @click="toggleAllUnmatchedNew(true)" class="text-cyan-700 dark:text-cyan-300 font-semibold hover:underline cursor-pointer">Pilih Semua</button>
              <span class="text-slate-300">|</span>
              <button type="button" @click="toggleAllUnmatchedNew(false)" class="text-slate-500 hover:underline cursor-pointer">Kosongkan</button>
            </div>
          </div>
          <div class="flex flex-wrap gap-2">
            <label
              v-for="terr in pdfPreview.unmatched_countries"
              :key="terr"
              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-cyan-200 dark:border-cyan-800 text-xs cursor-pointer"
            >
              <input
                type="checkbox"
                :checked="previewSelectedNew.has(terr)"
                @change="togglePreviewNewCountry(terr)"
                class="rounded text-cyan-600"
              />
              <span>{{ terr }}</span>
            </label>
          </div>
        </div>

        <!-- Action Footer -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
          <div class="text-xs text-slate-600 dark:text-slate-300">
            Total akan diset Zona Merah:
            <strong class="text-rose-600">{{ previewSelectedRed.size + previewSelectedNew.size }}</strong> negara/wilayah
          </div>
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="pdfPreview = null"
              class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer"
            >
              Batal
            </button>
            <button
              type="button"
              @click="applyPdfRedZones"
              :disabled="isApplyingRedZones"
              class="px-5 py-2 rounded-xl bg-gradient-to-r from-rose-600 to-rose-500 text-white text-xs font-bold shadow hover:opacity-95 disabled:opacity-50 inline-flex items-center gap-1.5 cursor-pointer"
            >
              <Save class="w-3.5 h-3.5" />
              {{ isApplyingRedZones ? 'Menyimpan...' : 'Terapkan Update Zona Merah' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Master List Negara & Riwayat Import -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Daftar Negara Saat Ini -->
      <div class="lg:col-span-8 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
              <Globe class="w-4 h-4 text-[#00A99D]" />
              Daftar Negara &amp; Status Zona Merah Aktif
            </h3>
            <p class="text-[11px] text-slate-500">Klik status di sebelah kanan nama negara untuk mengubah Zona Merah vs Zona Aman secara langsung.</p>
          </div>
          <div class="flex items-center gap-2">
            <div class="relative">
              <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" />
              <input
                v-model="countrySearch"
                type="text"
                placeholder="Cari negara..."
                class="pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-[#00A99D] focus:outline-none w-44"
              />
            </div>
            <select
              v-model="countryFilterStatus"
              class="text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-2.5 py-1.5"
            >
              <option value="all">Semua ({{ redZonesState.summary?.total || 0 }})</option>
              <option value="red">Zona Merah ({{ redZonesState.summary?.red_count || 0 }})</option>
              <option value="safe">Zona Aman ({{ redZonesState.summary?.safe_count || 0 }})</option>
            </select>
          </div>
        </div>

        <div class="max-h-[460px] overflow-y-auto border border-slate-100 dark:border-slate-800 rounded-xl divide-y divide-slate-100 dark:divide-slate-800">
          <div
            v-for="country in filteredRedZoneCountries"
            :key="country.id || country.name"
            class="flex items-center justify-between gap-3 px-3.5 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-xs"
          >
            <div class="min-w-0">
              <div class="font-semibold text-slate-800 dark:text-slate-100 truncate">{{ country.name }}</div>
              <div v-if="country.source_label && country.source_label !== country.name" class="text-[10px] text-slate-400 truncate">
                Sumber: {{ country.source_label }}
              </div>
            </div>
            <button
              type="button"
              :disabled="!country.id || togglingCountryId === country.id"
              @click="toggleSingleCountryRedZone(country)"
              :class="[
                'px-2.5 py-1 rounded-full text-[11px] font-bold transition shrink-0 cursor-pointer disabled:opacity-50',
                country.is_red_zone
                  ? 'bg-rose-500/10 text-rose-700 dark:text-rose-300 border border-rose-500/30 hover:bg-rose-500/20'
                  : 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/20'
              ]"
            >
              {{ togglingCountryId === country.id ? '...' : (country.is_red_zone ? 'ZONA MERAH (+20)' : 'AMAN (0)') }}
            </button>
          </div>
          <div v-if="!filteredRedZoneCountries.length" class="p-6 text-center text-xs text-slate-400">
            Tidak ada negara yang cocok dengan filter pencarian.
          </div>
        </div>
      </div>

      <!-- Tambah Manual & Riwayat Import -->
      <div class="lg:col-span-4 space-y-5">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-3">
          <h4 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
            <Flag class="w-4 h-4 text-[#00A99D]" />
            Tambah Negara / Wilayah Manual
          </h4>
          <form @submit.prevent="addManualCountry" class="space-y-2.5 text-xs">
            <input
              v-model="newCountryName"
              type="text"
              required
              placeholder="Contoh: Kosovo atau French Guiana"
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 focus:ring-2 focus:ring-[#00A99D] focus:outline-none"
            />
            <label class="flex items-center gap-2 text-slate-600 dark:text-slate-300 cursor-pointer">
              <input type="checkbox" v-model="newCountryIsRed" class="rounded text-rose-600" />
              <span>Tandai langsung sebagai Zona Merah (+20)</span>
            </label>
            <button
              type="submit"
              :disabled="isAddingCountry || !newCountryName.trim()"
              class="w-full py-2 rounded-xl bg-[#005851] hover:opacity-95 text-white font-bold transition disabled:opacity-50 cursor-pointer"
            >
              {{ isAddingCountry ? 'Menyimpan...' : '+ Tambah Negara' }}
            </button>
          </form>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-3">
          <h4 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
            <Clock class="w-4 h-4 text-[#00A99D]" />
            Riwayat Import PDF Terbaru
          </h4>
          <div v-if="!redZonesState.logs?.length" class="text-xs text-slate-400 italic py-2">
            Belum ada riwayat pembaruan PDF.
          </div>
          <div v-else class="space-y-2.5 max-h-80 overflow-y-auto pr-1">
            <div
              v-for="log in redZonesState.logs"
              :key="log.id"
              class="p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 text-xs space-y-1"
            >
              <div class="font-bold text-slate-800 dark:text-slate-100 truncate" :title="log.filename">
                {{ log.filename }}
              </div>
              <div class="flex flex-wrap gap-1.5 text-[10px] font-semibold">
                <span class="px-1.5 py-0.5 rounded bg-slate-200/70 dark:bg-slate-700 text-slate-700 dark:text-slate-200 uppercase">
                  {{ log.mode === 'replace' ? 'Ganti Total' : 'Merge' }}
                </span>
                <span class="px-1.5 py-0.5 rounded bg-rose-500/10 text-rose-600">+{{ log.newly_flagged_count }} Merah</span>
                <span class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-600">-{{ log.unflagged_count }} Aman</span>
                <span class="px-1.5 py-0.5 rounded bg-cyan-500/10 text-cyan-700">{{ log.matched_count }} Cocok</span>
              </div>
              <div class="text-[10px] text-slate-400">
                {{ log.importer_name }} &bull; {{ formatDate(log.created_at) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

