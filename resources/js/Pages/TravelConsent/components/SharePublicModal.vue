<script setup>
import { ref, watch } from 'vue';
import { Link2, X, Check, Copy, ExternalLink } from 'lucide-vue-next';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Link Publik Hasil Dokumen',
  },
  subtitle: {
    type: String,
    default: 'Dapat diakses publik tanpa login untuk melihat & membagikan dokumen.',
  },
  url: {
    type: String,
    default: '',
  },
  metaRows: {
    type: Array,
    default: () => [], // [{ label, value, valueClass }]
  },
  accent: {
    type: String,
    default: 'emerald', // 'emerald' | 'teal' | 'blue' | 'rose'
  },
});

const emit = defineEmits(['close']);
const copied = ref(false);

watch(() => props.url, () => {
  copied.value = false;
});

watch(() => props.show, (val) => {
  if (!val) copied.value = false;
});

const ACCENT_CLASSES = {
  emerald: {
    iconWrap: 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400',
    inputRing: 'focus:ring-emerald-500',
    openBtn: 'text-emerald-700 dark:text-emerald-400 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/60 border-emerald-200 dark:border-emerald-800',
  },
  teal: {
    iconWrap: 'bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400',
    inputRing: 'focus:ring-teal-500',
    openBtn: 'text-teal-700 dark:text-teal-400 bg-teal-50 hover:bg-teal-100 dark:bg-teal-950/60 dark:hover:bg-teal-900/60 border-teal-200 dark:border-teal-800',
  },
  blue: {
    iconWrap: 'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400',
    inputRing: 'focus:ring-blue-500',
    openBtn: 'text-blue-700 dark:text-blue-400 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/60 dark:hover:bg-blue-900/60 border-blue-200 dark:border-blue-800',
  },
  rose: {
    iconWrap: 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400',
    inputRing: 'focus:ring-rose-500',
    openBtn: 'text-rose-700 dark:text-rose-400 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 border-rose-200 dark:border-rose-800',
  },
};

const copyUrl = async () => {
  if (!props.url) return;
  try {
    await navigator.clipboard.writeText(props.url);
  } catch (e) {
    const input = document.createElement('input');
    input.value = props.url;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
  }
  copied.value = true;
  setTimeout(() => {
    copied.value = false;
  }, 2500);
};
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
    @click.self="emit('close')"
  >
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl p-6 relative">
      <div class="flex items-start justify-between mb-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" :class="(ACCENT_CLASSES[accent] || ACCENT_CLASSES.emerald).iconWrap">
            <Link2 class="w-5 h-5" />
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white leading-tight">
              {{ title }}
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              {{ subtitle }}
            </p>
          </div>
        </div>
        <button
          type="button"
          @click="emit('close')"
          class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <div v-if="metaRows.length" class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-3 mb-4 text-xs space-y-1.5 border border-slate-200/80 dark:border-slate-700/80">
        <div v-for="(row, idx) in metaRows" :key="idx" class="flex justify-between items-center gap-3">
          <span class="text-slate-500 shrink-0">{{ row.label }}:</span>
          <span :class="row.valueClass || 'font-medium text-slate-700 dark:text-slate-300 truncate max-w-[260px]'">
            {{ row.value }}
          </span>
        </div>
      </div>

      <div class="space-y-2 mb-5">
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
          Tautan Dokumen Publik:
        </label>
        <div class="flex items-center gap-2">
          <input
            type="text"
            readonly
            :value="url"
            class="flex-1 px-3 py-2 text-xs font-mono bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 select-all focus:outline-none focus:ring-2"
            :class="(ACCENT_CLASSES[accent] || ACCENT_CLASSES.emerald).inputRing"
            @focus="$event.target.select()"
          />
          <button
            type="button"
            @click="copyUrl"
            class="px-4 py-2 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 shrink-0 shadow-xs cursor-pointer text-white"
            :class="copied ? 'bg-emerald-600' : 'bg-slate-900 hover:bg-slate-800'"
          >
            <component :is="copied ? Check : Copy" class="w-3.5 h-3.5" />
            <span>{{ copied ? 'Tersalin!' : 'Salin Link' }}</span>
          </button>
        </div>
      </div>

      <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
        <a
          :href="url"
          target="_blank"
          class="px-3.5 py-2 rounded-xl text-xs font-semibold border transition-colors inline-flex items-center gap-1.5"
          :class="(ACCENT_CLASSES[accent] || ACCENT_CLASSES.emerald).openBtn"
        >
          <ExternalLink class="w-3.5 h-3.5" />
          <span>Buka Dokumen</span>
        </a>
        <button
          type="button"
          @click="emit('close')"
          class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
        >
          Tutup
        </button>
      </div>
    </div>
  </div>
</template>

