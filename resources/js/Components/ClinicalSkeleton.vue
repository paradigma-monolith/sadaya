<script setup>
defineProps({
  variant: {
    type: String,
    default: 'table', // 'table' | 'cards' | 'lines' | 'chart'
  },
  rows: {
    type: Number,
    default: 5,
  },
  cols: {
    type: Number,
    default: 4,
  },
});
</script>

<template>
  <div v-if="variant === 'cards'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 animate-pulse">
    <div
      v-for="i in cols"
      :key="i"
      class="clinical-surface p-4 space-y-2.5"
    >
      <div class="flex items-center justify-between">
        <div class="h-3 w-24 rounded bg-slate-200 dark:bg-slate-800"></div>
        <div class="h-7 w-7 rounded-lg bg-slate-200 dark:bg-slate-800"></div>
      </div>
      <div class="h-6 w-20 rounded bg-slate-200 dark:bg-slate-800"></div>
      <div class="h-2.5 w-32 rounded bg-slate-100 dark:bg-slate-800/70"></div>
    </div>
  </div>

  <div v-else-if="variant === 'chart'" class="clinical-surface p-4 space-y-3 animate-pulse">
    <div class="flex items-center justify-between">
      <div class="h-4 w-40 rounded bg-slate-200 dark:bg-slate-800"></div>
      <div class="h-6 w-20 rounded-md bg-slate-100 dark:bg-slate-800"></div>
    </div>
    <div class="h-48 w-full rounded-lg bg-slate-100 dark:bg-slate-800/70 flex items-end gap-3 p-4">
      <div class="flex-1 bg-slate-200 dark:bg-slate-700/70 rounded-t h-1/2"></div>
      <div class="flex-1 bg-slate-200 dark:bg-slate-700/70 rounded-t h-3/4"></div>
      <div class="flex-1 bg-slate-200 dark:bg-slate-700/70 rounded-t h-2/3"></div>
      <div class="flex-1 bg-slate-200 dark:bg-slate-700/70 rounded-t h-4/5"></div>
      <div class="flex-1 bg-slate-200 dark:bg-slate-700/70 rounded-t h-2/5"></div>
      <div class="flex-1 bg-slate-200 dark:bg-slate-700/70 rounded-t h-3/5"></div>
    </div>
  </div>

  <div v-else-if="variant === 'lines'" class="space-y-2.5 animate-pulse">
    <div
      v-for="r in rows"
      :key="r"
      class="p-2.5 rounded-lg border border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-[#16202a] space-y-1.5"
    >
      <div class="h-3 w-3/4 rounded bg-slate-200 dark:bg-slate-800"></div>
      <div class="h-2.5 w-full rounded bg-slate-200/80 dark:bg-slate-800/70"></div>
      <div class="h-2 w-1/3 rounded bg-slate-200/70 dark:bg-slate-800/60"></div>
    </div>
  </div>

  <div v-else class="w-full clinical-surface overflow-hidden animate-pulse">
    <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#16202a] flex items-center gap-3">
      <div v-for="c in cols" :key="c" class="h-3 flex-1 rounded bg-slate-200 dark:bg-slate-800"></div>
    </div>
    <div class="divide-y divide-slate-100 dark:divide-slate-800">
      <div v-for="r in rows" :key="r" class="px-4 py-3 flex items-center gap-3">
        <div
          v-for="c in cols"
          :key="c"
          class="h-3 flex-1 rounded bg-slate-100 dark:bg-slate-800/80"
          :class="c === 1 ? 'max-w-[70px]' : ''"
        ></div>
      </div>
    </div>
  </div>
</template>
