<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  meta: {
    type: Object,
    required: true,
  },
  noun: {
    type: String,
    default: 'data',
  },
  activeClass: {
    type: String,
    default: 'bg-[#005851] text-white border-[#005851] font-bold',
  },
});
</script>

<template>
  <div
    v-if="meta?.links && meta.links.length > 3"
    class="px-5 py-4 border-t border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400"
  >
    <div>
      Menampilkan <strong class="text-slate-800 dark:text-slate-200">{{ meta.from || 0 }}</strong> - <strong class="text-slate-800 dark:text-slate-200">{{ meta.to || 0 }}</strong> dari <strong class="text-slate-800 dark:text-slate-200">{{ meta.total }}</strong> {{ noun }}
    </div>

    <div class="flex items-center gap-1 flex-wrap">
      <template v-for="(link, idx) in meta.links" :key="idx">
        <Link
          v-if="link.url"
          :href="link.url"
          v-html="link.label"
          class="px-3 py-1.5 rounded-lg border text-xs font-medium transition-colors"
          :class="link.active ? activeClass : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'"
          preserve-scroll
          preserve-state
        />
        <span
          v-else
          v-html="link.label"
          class="px-3 py-1.5 rounded-lg border border-slate-100 dark:border-slate-800 text-slate-300 dark:text-slate-700 text-xs cursor-not-allowed"
        />
      </template>
    </div>
  </div>
</template>

