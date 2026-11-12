<script setup>
import { ref, computed } from 'vue';
import { 
  ChevronUp, 
  ChevronDown, 
  ChevronsUpDown, 
  Search, 
  ChevronLeft, 
  ChevronRight, 
  Inbox,
  Loader2,
  X
} from 'lucide-vue-next';

const props = defineProps({
  columns: {
    type: Array,
    required: true,
    // Format: [{ key: 'name', label: 'Nama', sortable: true, class: '...', headerClass: '...' }]
  },
  data: {
    type: [Array, Object],
    required: true,
    // Can be either an array of objects or a Laravel pagination object: { data: [...], current_page: 1, last_page: 5, total: 50, per_page: 10 }
  },
  searchable: {
    type: Boolean,
    default: true,
  },
  searchPlaceholder: {
    type: String,
    default: 'Cari data...',
  },
  loading: {
    type: Boolean,
    default: false,
  },
  emptyMessage: {
    type: String,
    default: 'Belum ada data yang dapat ditampilkan.',
  },
  // If true, sorting & searching are handled internally in client memory
  clientSide: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['sort', 'page-change', 'search']);

const searchQuery = ref('');
const sortKey = ref('');
const sortDirection = ref('asc'); // 'asc' | 'desc'
const clientCurrentPage = ref(1);
const perPage = ref(10);

// Detect if data is a Laravel pagination object or raw array
const isPaginated = computed(() => {
  return props.data && typeof props.data === 'object' && Array.isArray(props.data.data);
});

const rawRows = computed(() => {
  if (isPaginated.value) {
    return props.data.data || [];
  }
  return Array.isArray(props.data) ? props.data : [];
});

// Client-side processing if clientSide prop is true
const processedRows = computed(() => {
  let rows = [...rawRows.value];

  if (!props.clientSide) {
    return rows;
  }

  // Client search
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    rows = rows.filter(row => {
      return props.columns.some(col => {
        const val = row[col.key];
        return val !== null && val !== undefined && String(val).toLowerCase().includes(q);
      });
    });
  }

  // Client sort
  if (sortKey.value) {
    rows.sort((a, b) => {
      const aVal = a[sortKey.value] ?? '';
      const bVal = b[sortKey.value] ?? '';
      if (aVal < bVal) return sortDirection.value === 'asc' ? -1 : 1;
      if (aVal > bVal) return sortDirection.value === 'asc' ? 1 : -1;
      return 0;
    });
  }

  return rows;
});

const handleSort = (column) => {
  if (!column.sortable) return;

  if (sortKey.value === column.key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = column.key;
    sortDirection.value = 'asc';
  }

  emit('sort', { key: sortKey.value, direction: sortDirection.value });
};

const handleSearch = () => {
  emit('search', searchQuery.value);
};

const clearSearch = () => {
  searchQuery.value = '';
  emit('search', '');
};

const handlePageChange = (page) => {
  if (props.clientSide) {
    clientCurrentPage.value = page;
  } else {
    emit('page-change', page);
  }
};
</script>

<template>
  <div class="w-full clinical-surface overflow-hidden">
    <!-- Table Header Controls: Search & Top Actions Slot -->
    <div v-if="searchable || $slots.actions" class="px-3.5 py-2.5 border-b border-slate-200/90 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 bg-slate-50/50">
      <!-- Search Input -->
      <div v-if="searchable" class="relative max-w-xs w-full">
        <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="searchPlaceholder"
          @input="handleSearch"
          class="w-full pl-8 pr-7 py-1.5 clinical-input"
        />
        <button
          v-if="searchQuery"
          type="button"
          @click="clearSearch"
          class="absolute right-2 top-1/2 -translate-y-1/2 p-0.5 rounded text-slate-400 hover:text-slate-600"
        >
          <X class="w-3.5 h-3.5" />
        </button>
      </div>

      <!-- Custom Actions Slot (Buttons, Export, Filters) -->
      <div v-if="$slots.actions" class="flex items-center gap-1.5">
        <slot name="actions" />
      </div>
    </div>

    <!-- Table Container -->
    <div class="relative overflow-x-auto">
      <!-- Loading Overlay -->
      <div 
        v-if="loading" 
        class="absolute inset-0 bg-white/75 dark:bg-[#111820]/80 backdrop-blur-[1px] flex items-center justify-center z-10"
      >
        <div class="flex items-center gap-2 text-[#005851] dark:text-[#4fd1c5] text-xs font-semibold">
          <Loader2 class="w-4 h-4 animate-spin" />
          <span>Memuat data...</span>
        </div>
      </div>

      <table class="w-full text-left text-xs text-slate-700 dark:text-slate-200">
        <!-- Table Head -->
        <thead class="bg-slate-50/90 dark:bg-[#16202a] text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-300 font-semibold border-b border-slate-200/90 dark:border-slate-800">
          <tr>
            <th
              v-for="col in columns"
              :key="col.key"
              :class="[
                'px-3.5 py-2.5 select-none',
                col.sortable ? 'cursor-pointer hover:text-slate-900 dark:hover:text-white' : '',
                col.headerClass || ''
              ]"
              @click="handleSort(col)"
            >
              <div class="flex items-center gap-1">
                <span>{{ col.label }}</span>
                <span v-if="col.sortable" class="text-slate-400">
                  <ChevronUp v-if="sortKey === col.key && sortDirection === 'asc'" class="w-3.5 h-3.5 text-[#00A99D]" />
                  <ChevronDown v-else-if="sortKey === col.key && sortDirection === 'desc'" class="w-3.5 h-3.5 text-[#00A99D]" />
                  <ChevronsUpDown v-else class="w-3 h-3 opacity-60" />
                </span>
              </div>
            </th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-if="processedRows.length === 0 && !loading" class="text-center">
            <td :colspan="columns.length" class="py-10 text-slate-400">
              <Inbox class="w-7 h-7 text-slate-300 dark:text-slate-600 mx-auto mb-1.5" />
              <p class="text-xs">{{ emptyMessage }}</p>
            </td>
          </tr>

          <tr
            v-for="(row, rowIndex) in processedRows"
            :key="row.id || rowIndex"
            class="hover:bg-[#F4FBF9]/70 dark:hover:bg-[#00A99D]/10 transition-colors"
          >
            <td
              v-for="col in columns"
              :key="col.key"
              :class="['px-3.5 py-2.5 align-middle', col.class || '']"
            >
              <!-- Cell Slot with fallback -->
              <slot :name="`cell(${col.key})`" :row="row" :value="row[col.key]">
                {{ row[col.key] !== null && row[col.key] !== undefined ? row[col.key] : '-' }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Table Footer / Pagination -->
    <div 
      v-if="isPaginated && data.total > 0"
      class="px-3.5 py-2.5 border-t border-slate-200/90 bg-slate-50/40 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500 tabular-nums"
    >
      <div>
        Menampilkan <span class="font-semibold text-slate-800">{{ data.from || 1 }}</span>–<span class="font-semibold text-slate-800">{{ data.to || data.total }}</span> dari 
        <span class="font-semibold text-slate-800">{{ data.total }}</span> data
      </div>

      <!-- Pagination Buttons -->
      <div class="flex items-center gap-1">
        <button
          type="button"
          :disabled="!data.prev_page_url"
          @click="handlePageChange(data.current_page - 1)"
          class="p-1.5 rounded-md border border-slate-200 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-white transition-colors cursor-pointer"
        >
          <ChevronLeft class="w-3.5 h-3.5" />
        </button>

        <span class="px-2.5 py-1 font-mono text-[11px] font-semibold text-slate-700">
          Hal {{ data.current_page }} / {{ data.last_page }}
        </span>

        <button
          type="button"
          :disabled="!data.next_page_url"
          @click="handlePageChange(data.current_page + 1)"
          class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
        >
          <ChevronRight class="w-4 h-4" />
        </button>
      </div>
    </div>
  </div>
</template>
