<script setup>
import { ref, computed, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { useDate } from '../../../composables/useDate';
import {
  formatCurrency,
  formatNumber,
  getRiskBadgeClass,
  getStatusBadgeClass,
  withTabQuery,
} from '../utils/formatters';
import {
  Filter,
  RefreshCw,
  Search,
  Ship,
  Edit2,
  Check,
  X,
  CheckCircle,
  Eye,
  ShieldCheck,
  Trash2,
} from 'lucide-vue-next';

const props = defineProps({
  surveillances: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  ports: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['open-detail', 'open-verify']);

const { formatDate } = useDate();

// Filter State
const searchInput = ref(props.filters.search || '');
const selectedPelabuhan = ref(props.filters.pelabuhan || '');
const selectedKategori = ref(props.filters.kategori || '');
const selectedKresiko = ref(props.filters.kresiko || '');
const selectedStatus = ref(props.filters.status || '');

watch(
  () => props.filters,
  (next) => {
    searchInput.value = next?.search || '';
    selectedPelabuhan.value = next?.pelabuhan || '';
    selectedKategori.value = next?.kategori || '';
    selectedKresiko.value = next?.kresiko || '';
    selectedStatus.value = next?.status || '';
  }
);

const activeFilterCount = computed(() => {
  return [
    searchInput.value,
    selectedPelabuhan.value,
    selectedKategori.value,
    selectedKresiko.value,
    selectedStatus.value,
  ].filter(Boolean).length;
});

const handleFilter = () => {
  const payload = {
    tab: 'data',
    search: searchInput.value || undefined,
    pelabuhan: selectedPelabuhan.value || undefined,
    kategori: selectedKategori.value || undefined,
    kresiko: selectedKresiko.value || undefined,
    status: selectedStatus.value || undefined,
    start_date: props.filters.start_date || undefined,
    end_date: props.filters.end_date || undefined,
    bulan: props.filters.bulan || undefined,
    triwulan: props.filters.triwulan || undefined,
    tahun: props.filters.tahun || undefined,
  };
  router.get('/ship-surveillance', payload, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

const resetFilter = () => {
  searchInput.value = '';
  selectedPelabuhan.value = '';
  selectedKategori.value = '';
  selectedKresiko.value = '';
  selectedStatus.value = '';
  router.get('/ship-surveillance', { tab: 'data' }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

// Inline Editing Gross Tonnage
const editingGtId = ref(null);
const editingGtValue = ref(0);
const isSavingGt = ref(false);

const startEditGt = (item) => {
  editingGtId.value = item.id;
  editingGtValue.value = item.gross_tonnage;
};

const cancelEditGt = () => {
  editingGtId.value = null;
  editingGtValue.value = 0;
};

const saveGt = async (item) => {
  if (editingGtValue.value < 0) return;
  isSavingGt.value = true;
  try {
    const res = await fetch(`/ship-surveillance/${item.id}/gross-tonnage`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ gross_tonnage: editingGtValue.value }),
    });
    const result = await res.json();
    if (result.success) {
      item.gross_tonnage = result.data.gross_tonnage;
      item.estimasi_pnbp = result.data.estimasi_pnbp;
      editingGtId.value = null;
    } else {
      alert(result.message || 'Gagal memperbarui Gross Tonnage');
    }
  } catch {
    alert('Terjadi kesalahan koneksi saat memperbarui Gross Tonnage');
  } finally {
    isSavingGt.value = false;
  }
};

// Delete Action
const deleteItem = (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus data pengawasan kapal "${item.nama_kapal}"?`)) {
    router.delete(`/ship-surveillance/${item.id}`, {
      preserveScroll: true,
    });
  }
};
</script>

<template>
  <div class="space-y-3">
    <!-- Filter Bar -->
    <div class="clinical-surface p-3.5 space-y-2.5">
      <div class="flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
          <Filter class="w-3.5 h-3.5 text-[#00A99D]" />
          <span>Filter Data Kedatangan Kapal</span>
          <span
            v-if="activeFilterCount > 0"
            class="px-1.5 py-0.2 rounded text-[10px] font-mono bg-[#E6F7F5] text-[#005851]"
          >
            {{ activeFilterCount }} aktif
          </span>
        </div>
        <button
          v-if="activeFilterCount > 0"
          type="button"
          @click="resetFilter"
          class="text-xs text-rose-600 hover:underline flex items-center gap-1 font-semibold cursor-pointer"
        >
          <RefreshCw class="w-3 h-3" />
          Reset Filter
        </button>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5">
        <!-- Search -->
        <div>
          <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Pencarian</label>
          <div class="relative">
            <input
              v-model="searchInput"
              @keyup.enter="handleFilter"
              type="text"
              placeholder="Nama kapal / registrasi / asal..."
              class="w-full h-8 text-xs clinical-input px-2.5 pl-7"
            />
            <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2" />
          </div>
        </div>

        <!-- Pelabuhan Tujuan -->
        <div>
          <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Pelabuhan Tujuan</label>
          <select
            v-model="selectedPelabuhan"
            @change="handleFilter"
            class="w-full h-8 text-xs clinical-input px-2.5"
          >
            <option value="">Semua Pelabuhan Wilker</option>
            <option v-for="port in ports" :key="port" :value="port">{{ port }}</option>
          </select>
        </div>

        <!-- Kategori Asal -->
        <div>
          <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Kategori Pelayaran</label>
          <select
            v-model="selectedKategori"
            @change="handleFilter"
            class="w-full h-8 text-xs clinical-input px-2.5"
          >
            <option value="">Semua Kategori</option>
            <option value="Dalam Negeri">Dalam Negeri (Domestik)</option>
            <option value="Luar Negeri">Luar Negeri (Internasional)</option>
          </select>
        </div>

        <!-- Tingkat Risiko -->
        <div>
          <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Faktor Risiko</label>
          <select
            v-model="selectedKresiko"
            @change="handleFilter"
            class="w-full h-8 text-xs clinical-input px-2.5"
          >
            <option value="">Semua Tingkat Risiko</option>
            <option value="RENDAH">Rendah (Skor &lt; 10)</option>
            <option value="SEDANG">Sedang (Skor 10 - 19)</option>
            <option value="TINGGI">Tinggi (Skor &ge; 20)</option>
          </select>
        </div>

        <!-- Status Keputusan -->
        <div>
          <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Keputusan / Status</label>
          <select
            v-model="selectedStatus"
            @change="handleFilter"
            class="w-full h-8 text-xs clinical-input px-2.5"
          >
            <option value="">Semua Status</option>
            <option value="SANDAR">Izin Sandar</option>
            <option value="ZONA KARANTINA">Zona Karantina (Lego Jangkar)</option>
            <option value="pending">Menunggu Verifikasi (Pending)</option>
            <option value="verified">Sudah Terverifikasi</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <Ship class="w-4 h-4 text-[#00A99D]" />
          <span>Daftar Kedatangan Kapal &amp; Pengawasan Kesehatan</span>
        </h2>
        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
          Menampilkan <strong class="text-slate-800 dark:text-slate-200">{{ surveillances.from || 0 }}</strong> - <strong class="text-slate-800 dark:text-slate-200">{{ surveillances.to || 0 }}</strong> dari <strong class="text-slate-800 dark:text-slate-200">{{ surveillances.total || 0 }}</strong> data
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
            <tr>
              <th class="py-3 px-3 w-12 text-center">No</th>
              <th class="py-3 px-3">Waktu Kedatangan</th>
              <th class="py-3 px-3">Identitas Kapal</th>
              <th class="py-3 px-3">Rute Pelayaran</th>
              <th class="py-3 px-3 w-36">Gross Tonnage (GT)</th>
              <th class="py-3 px-3 text-center">Kategori</th>
              <th class="py-3 px-3 text-center">Skor &amp; Risiko</th>
              <th class="py-3 px-3 text-center">Keputusan</th>
              <th class="py-3 px-3">Estimasi PNBP</th>
              <th class="py-3 px-3 text-center w-28">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
            <tr
              v-for="(item, idx) in surveillances.data"
              :key="item.id"
              class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors"
            >
              <!-- No -->
              <td class="py-3 px-3 text-center font-mono text-slate-400">
                {{ (surveillances.current_page - 1) * surveillances.per_page + idx + 1 }}
              </td>

              <!-- Waktu -->
              <td class="py-3 px-3 whitespace-nowrap">
                <div class="font-medium text-slate-800 dark:text-slate-200">
                  {{ formatDate(item.tanggal_kedatangan) }}
                </div>
                <div class="text-[11px] text-slate-400 font-mono">
                  {{ item.registration_no }}
                </div>
              </td>

              <!-- Nama & Bendera -->
              <td class="py-3 px-3">
                <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                  {{ item.nama_kapal }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5">
                  <span>Bendera: {{ item.origin_country || 'N/A' }}</span>
                  <span>&bull;</span>
                  <span>{{ item.jml_crew }} Crew</span>
                </div>
              </td>

              <!-- Rute Pelayaran -->
              <td class="py-3 px-3">
                <div class="text-slate-700 dark:text-slate-300">
                  <span class="text-slate-400 text-[10px] uppercase font-semibold">Dari:</span> {{ item.datang_dari }}
                </div>
                <div class="text-[11px] text-[#00A99D] font-medium mt-0.5">
                  <span class="text-slate-400 text-[10px] uppercase font-semibold">Tujuan:</span> {{ item.pelabuhan }}
                </div>
              </td>

              <!-- Gross Tonnage (Interactive Inline Editing) -->
              <td class="py-3 px-3">
                <div v-if="editingGtId === item.id" class="flex items-center gap-1">
                  <input
                    v-model.number="editingGtValue"
                    type="number"
                    min="0"
                    class="w-20 px-2 py-1 text-xs rounded border border-emerald-500 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none"
                  />
                  <button
                    type="button"
                    @click="saveGt(item)"
                    :disabled="isSavingGt"
                    class="p-1 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded cursor-pointer"
                    title="Simpan GT"
                  >
                    <Check class="w-3.5 h-3.5" />
                  </button>
                  <button
                    type="button"
                    @click="cancelEditGt"
                    class="p-1 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded cursor-pointer"
                    title="Batal"
                  >
                    <X class="w-3.5 h-3.5" />
                  </button>
                </div>
                <div v-else class="flex items-center justify-between gap-1 group">
                  <span class="font-mono font-semibold text-slate-800 dark:text-slate-200">
                    {{ formatNumber(item.gross_tonnage) }} GT
                  </span>
                  <button
                    type="button"
                    @click="startEditGt(item)"
                    class="opacity-60 sm:opacity-0 group-hover:opacity-100 p-1 text-slate-400 hover:text-[#00A99D] transition cursor-pointer"
                    title="Ubah Gross Tonnage"
                  >
                    <Edit2 class="w-3 h-3" />
                  </button>
                </div>
              </td>

              <!-- Kategori Pelayaran -->
              <td class="py-3 px-3 text-center whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold',
                    item.kategori === 'Luar Negeri'
                      ? 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20'
                      : 'bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-500/20'
                  ]"
                >
                  {{ item.kategori }}
                </span>
              </td>

              <!-- Skor & Kresiko -->
              <td class="py-3 px-3 text-center whitespace-nowrap">
                <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold', getRiskBadgeClass(item.kresiko)]">
                  {{ item.kresiko }} ({{ item.skoring }})
                </span>
              </td>

              <!-- Keputusan Status -->
              <td class="py-3 px-3 text-center whitespace-nowrap">
                <div class="flex flex-col items-center gap-1">
                  <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold', getStatusBadgeClass(item.hasil_status)]">
                    {{ item.hasil_status }}
                  </span>
                  <span v-if="item.status_verifikasi === 'verified'" class="text-[9px] text-emerald-600 dark:text-emerald-400 flex items-center gap-0.5">
                    <CheckCircle class="w-2.5 h-2.5" /> Terverifikasi
                  </span>
                  <span v-else class="text-[9px] text-amber-600 dark:text-amber-400">
                    Pending
                  </span>
                </div>
              </td>

              <!-- Estimasi PNBP -->
              <td class="py-3 px-3 whitespace-nowrap">
                <div class="font-mono text-emerald-600 dark:text-emerald-400 font-semibold">
                  {{ formatCurrency(item.estimasi_pnbp) }}
                </div>
                <div class="text-[10px] text-slate-400">
                  Tarif PP Kemenkes
                </div>
              </td>

              <!-- Aksi -->
              <td class="py-3 px-3 text-center whitespace-nowrap">
                <div class="flex items-center justify-center gap-1">
                  <button
                    type="button"
                    @click="emit('open-detail', item)"
                    class="p-1.5 text-slate-600 hover:text-cyan-600 dark:text-slate-400 dark:hover:text-cyan-400 hover:bg-cyan-50 dark:hover:bg-cyan-900/30 rounded-lg transition cursor-pointer"
                    title="Lihat Detail Lembar Karantina"
                  >
                    <Eye class="w-4 h-4" />
                  </button>

                  <button
                    type="button"
                    @click="emit('open-verify', item)"
                    class="p-1.5 text-slate-600 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-lg transition cursor-pointer"
                    title="Verifikasi Rekomendasi Sandar / Karantina"
                  >
                    <ShieldCheck class="w-4 h-4" />
                  </button>

                  <button
                    type="button"
                    @click="deleteItem(item)"
                    class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition cursor-pointer"
                    title="Hapus Data"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!surveillances.data || surveillances.data.length === 0">
              <td colspan="10" class="py-12 text-center text-slate-400">
                <Ship class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-700 mb-2" />
                <p class="font-medium text-sm">Tidak ada data pengawasan kedatangan kapal yang cocok dengan filter.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="surveillances.links && surveillances.links.length > 3"
        class="px-5 py-4 border-t border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400"
      >
        <span>
          Halaman <strong class="text-slate-800 dark:text-slate-200">{{ surveillances.current_page }}</strong> dari <strong class="text-slate-800 dark:text-slate-200">{{ surveillances.last_page }}</strong>
        </span>
        <div class="flex items-center gap-1 flex-wrap">
          <template v-for="(link, i) in surveillances.links" :key="i">
            <Link
              v-if="link.url"
              :href="withTabQuery(link.url, 'data')"
              v-html="link.label"
              preserve-scroll
              preserve-state
              :class="[
                'px-3 py-1.5 rounded-lg border text-xs font-medium transition-colors',
                link.active
                  ? 'bg-[#005851] text-white border-[#005851] font-bold'
                  : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'
              ]"
            />
            <span
              v-else
              v-html="link.label"
              class="px-3 py-1.5 rounded-lg border border-slate-100 dark:border-slate-800 text-slate-300 dark:text-slate-700 text-xs cursor-not-allowed"
            />
          </template>
        </div>
      </div>
    </div>
  </div>
</template>
