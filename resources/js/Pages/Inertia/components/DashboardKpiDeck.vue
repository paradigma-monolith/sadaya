<script setup>
import { Plane, Ship, Stethoscope, Pill } from 'lucide-vue-next';

defineProps({
  selectedModuleKey: { type: String, required: true },
  kpi: { type: Object, required: true },
  aircraftStats: { type: Object, required: true },
  shipStats: { type: Object, required: true },
  medicalStats: { type: Object, required: true },
});

const emit = defineEmits(['toggle-module']);
</script>

<template>
  <!-- ========================================================================= -->
  <!-- 4 CLINICAL SERVICE KPI CARDS (ENTERPRISE COMPACT)                         -->
  <!-- ========================================================================= -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 font-sans">
    <!-- Tile 1: Sanitasi Pesawat Udara -->
    <button
      type="button"
      @click="emit('toggle-module', 'aircraft')"
      :aria-pressed="selectedModuleKey === 'aircraft'"
      class="w-full text-left rounded-xl p-3 transition-colors cursor-pointer border shadow-[0_1px_2px_rgba(15,23,42,0.03)] group"
      :class="selectedModuleKey === 'aircraft'
        ? 'border-[#00A99D] ring-1 ring-[#00A99D] bg-[#F4FBF9] dark:bg-[#0b2825]'
        : 'bg-white dark:bg-[#111820] border-slate-200/90 dark:border-slate-800 hover:border-[#00A99D]/70'"
    >
      <div class="flex items-center justify-between text-xs gap-1">
        <span class="font-semibold flex items-center gap-2 text-slate-700 dark:text-slate-200">
          <span class="w-6 h-6 rounded-md bg-[#E6F7F5] dark:bg-[#00A99D]/15 text-[#005851] dark:text-[#5eead4] flex items-center justify-center shrink-0">
            <Plane class="w-3.5 h-3.5" />
          </span>
          <span>Sanitasi Pesawat</span>
        </span>
        <span class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded bg-[#E6F7F5] dark:bg-[#00A99D]/15 text-[#005851] dark:text-[#5eead4] tabular-nums shrink-0">
          {{ kpi.compliance_rate }}% Laik
        </span>
      </div>

      <div class="flex items-baseline justify-between mt-2 gap-1">
        <div>
          <span class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight font-mono tabular-nums">
            {{ aircraftStats.total }}
          </span>
          <span class="text-[11px] text-slate-400 dark:text-slate-500 ml-1">dok</span>
        </div>
        <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400 tabular-nums">
          <span class="text-[#005851] dark:text-[#5eead4] font-semibold">{{ aircraftStats.layak }} Layak</span> &bull;
          <span :class="aircraftStats.tidak_layak > 0 ? 'text-rose-600 dark:text-rose-400 font-semibold' : 'text-slate-400 dark:text-slate-500'">{{ aircraftStats.tidak_layak }} Tindakan</span>
        </div>
      </div>

      <div class="mt-2 w-full bg-slate-100 dark:bg-slate-800 h-1 rounded-full overflow-hidden">
        <div
          class="h-full bg-[#00A99D] rounded-full transition-all duration-500"
          :style="{ width: `${Math.max(12, kpi.compliance_rate || 85)}%` }"
        ></div>
      </div>
    </button>

    <!-- Tile 2: Sanitasi Kapal Laut -->
    <button
      type="button"
      @click="emit('toggle-module', 'ship')"
      :aria-pressed="selectedModuleKey === 'ship'"
      class="w-full text-left rounded-xl p-3 transition-colors cursor-pointer border shadow-[0_1px_2px_rgba(15,23,42,0.03)] group"
      :class="selectedModuleKey === 'ship'
        ? 'border-[#005851] dark:border-[#00A99D] ring-1 ring-[#005851] dark:ring-[#00A99D] bg-[#F2F8F8] dark:bg-[#0d2428]'
        : 'bg-white dark:bg-[#111820] border-slate-200/90 dark:border-slate-800 hover:border-[#005851]/70 dark:hover:border-[#00A99D]/70'"
    >
      <div class="flex items-center justify-between text-xs gap-1">
        <span class="font-semibold flex items-center gap-2 text-slate-700 dark:text-slate-200">
          <span class="w-6 h-6 rounded-md bg-[#E0EFEF] dark:bg-[#005851]/35 text-[#005851] dark:text-[#5eead4] flex items-center justify-center shrink-0">
            <Ship class="w-3.5 h-3.5" />
          </span>
          <span>Sanitasi Kapal</span>
        </span>
        <span class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shrink-0">
          IHR 2005
        </span>
      </div>

      <div class="flex items-baseline justify-between mt-2 gap-1">
        <div>
          <span class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight font-mono tabular-nums">
            {{ shipStats.total }}
          </span>
          <span class="text-[11px] text-slate-400 dark:text-slate-500 ml-1">kapal</span>
        </div>
        <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400 tabular-nums">
          <span class="text-[#005851] dark:text-[#5eead4] font-semibold">{{ shipStats.sscec }} SSCEC</span> &bull;
          <span class="text-amber-700 dark:text-amber-400 font-semibold">{{ shipStats.sscc }} SSCC</span>
        </div>
      </div>

      <div class="mt-2 w-full bg-slate-100 dark:bg-slate-800 h-1 rounded-full overflow-hidden">
        <div
          class="h-full bg-[#005851] dark:bg-[#00A99D] rounded-full transition-all duration-500"
          :style="{ width: `${shipStats.total > 0 ? Math.max(15, Math.round((shipStats.sscec / shipStats.total) * 100)) : 75}%` }"
        ></div>
      </div>
    </button>

    <!-- Tile 3: Alkes Haji & Emergensi -->
    <button
      type="button"
      @click="emit('toggle-module', 'alat')"
      :aria-pressed="selectedModuleKey === 'alat'"
      class="w-full text-left rounded-xl p-3 transition-colors cursor-pointer border shadow-[0_1px_2px_rgba(15,23,42,0.03)] group"
      :class="selectedModuleKey === 'alat'
        ? 'border-[#7CA622] ring-1 ring-[#7CA622] bg-[#F9FCEF] dark:bg-[#192612]'
        : 'bg-white dark:bg-[#111820] border-slate-200/90 dark:border-slate-800 hover:border-[#7CA622]/70'"
    >
      <div class="flex items-center justify-between text-xs gap-1">
        <span class="font-semibold flex items-center gap-2 text-slate-700 dark:text-slate-200">
          <span class="w-6 h-6 rounded-md bg-[#F7FCE8] dark:bg-[#7CA622]/20 text-[#557A18] dark:text-[#bef264] flex items-center justify-center shrink-0">
            <Stethoscope class="w-3.5 h-3.5" />
          </span>
          <span>Alkes Haji Pesawat</span>
        </span>
        <span class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded bg-[#F7FCE8] dark:bg-[#7CA622]/20 text-[#557A18] dark:text-[#bef264] shrink-0">
          Emergensi
        </span>
      </div>

      <div class="flex items-baseline justify-between mt-2 gap-1">
        <div>
          <span class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight font-mono tabular-nums">
            {{ medicalStats.alat }}
          </span>
          <span class="text-[11px] text-slate-400 dark:text-slate-500 ml-1">dok</span>
        </div>
        <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400 tabular-nums">
          <span class="text-[#557A18] dark:text-[#bef264] font-semibold">{{ kpi.approved }} Selesai</span> &bull;
          <span class="text-amber-700 dark:text-amber-400 font-semibold">{{ kpi.pending }} Proses</span>
        </div>
      </div>

      <div class="mt-2 w-full bg-slate-100 dark:bg-slate-800 h-1 rounded-full overflow-hidden">
        <div
          class="h-full bg-[#7CA622] rounded-full transition-all duration-500"
          :style="{ width: `${Math.max(18, kpi.completion_rate || 80)}%` }"
        ></div>
      </div>
    </button>

    <!-- Tile 4: Obat P3K Pesawat -->
    <button
      type="button"
      @click="emit('toggle-module', 'obat')"
      :aria-pressed="selectedModuleKey === 'obat'"
      class="w-full text-left rounded-xl p-3 transition-colors cursor-pointer border shadow-[0_1px_2px_rgba(15,23,42,0.03)] group"
      :class="selectedModuleKey === 'obat'
        ? 'border-[#00A99D] ring-1 ring-[#00A99D] bg-[#F4FBF9] dark:bg-[#0b2825]'
        : 'bg-white dark:bg-[#111820] border-slate-200/90 dark:border-slate-800 hover:border-[#00A99D]/70'"
    >
      <div class="flex items-center justify-between text-xs gap-1">
        <span class="font-semibold flex items-center gap-2 text-slate-700 dark:text-slate-200">
          <span class="w-6 h-6 rounded-md bg-[#E6F7F5] dark:bg-[#00A99D]/15 text-[#005851] dark:text-[#5eead4] flex items-center justify-center shrink-0">
            <Pill class="w-3.5 h-3.5" />
          </span>
          <span>Obat P3K Pesawat</span>
        </span>
        <span class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 tabular-nums shrink-0">
          Total {{ kpi.total }}
        </span>
      </div>

      <div class="flex items-baseline justify-between mt-2 gap-1">
        <div>
          <span class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight font-mono tabular-nums">
            {{ medicalStats.obat }}
          </span>
          <span class="text-[11px] text-slate-400 dark:text-slate-500 ml-1">dok</span>
        </div>
        <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400 tabular-nums">
          <span class="text-[#005851] dark:text-[#5eead4] font-semibold">{{ kpi.completion_rate }}% Verifikasi</span> &bull;
          <span class="text-slate-600 dark:text-slate-300">{{ kpi.today }} Hari Ini</span>
        </div>
      </div>

      <div class="mt-2 w-full bg-slate-100 dark:bg-slate-800 h-1 rounded-full overflow-hidden">
        <div
          class="h-full bg-[#00A99D] rounded-full transition-all duration-500"
          :style="{ width: `${Math.max(15, kpi.completion_rate || 82)}%` }"
        ></div>
      </div>
    </button>
  </div>
</template>
