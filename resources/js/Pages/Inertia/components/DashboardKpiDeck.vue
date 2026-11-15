<script setup>
import { computed } from 'vue';
import { Plane, Ship, Stethoscope, Pill } from 'lucide-vue-next';

const props = defineProps({
  selectedModuleKey: { type: String, required: true },
  kpi: { type: Object, required: true },
  aircraftStats: { type: Object, required: true },
  shipStats: { type: Object, required: true },
  medicalStats: { type: Object, required: true },
});

const emit = defineEmits(['toggle-module']);

const cards = computed(() => {
  const shipTotal = props.shipStats.total || 0;
  const shipProgress = shipTotal > 0 ? Math.max(15, Math.round((props.shipStats.sscec / shipTotal) * 100)) : 75;

  return [
    {
      key: 'aircraft',
      title: 'Sanitasi Pesawat',
      icon: Plane,
      badge: `${props.kpi.compliance_rate}% Laik`,
      badgeClass: 'bg-[#E6F7F5] dark:bg-[#00A99D]/15 text-[#005851] dark:text-[#5eead4]',
      value: props.aircraftStats.total,
      unit: 'dok',
      leftDetail: `${props.aircraftStats.layak} Layak`,
      leftDetailClass: 'text-[#005851] dark:text-[#5eead4] font-semibold',
      rightDetail: `${props.aircraftStats.tidak_layak} Tindakan`,
      rightDetailClass: props.aircraftStats.tidak_layak > 0 ? 'text-rose-600 dark:text-rose-400 font-semibold' : 'text-slate-400 dark:text-slate-500',
      progress: Math.max(12, props.kpi.compliance_rate || 85),
      barClass: 'bg-gradient-to-r from-[#005851] to-[#00A99D]',
      accentClass: 'from-[#005851] via-[#00A99D] to-[#5eead4]',
      activeClass: 'border-[#00A99D] ring-1 ring-[#00A99D] bg-[#F4FBF9] dark:bg-[#0b2825] shadow-md',
      idleHoverClass: 'hover:border-[#00A99D]/70',
      iconWrapClass: 'bg-[#E6F7F5] dark:bg-[#00A99D]/15 text-[#005851] dark:text-[#5eead4]',
    },
    {
      key: 'ship',
      title: 'Sanitasi Kapal',
      icon: Ship,
      badge: 'IHR 2005',
      badgeClass: 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300',
      value: props.shipStats.total,
      unit: 'kapal',
      leftDetail: `${props.shipStats.sscec} SSCEC`,
      leftDetailClass: 'text-[#005851] dark:text-[#5eead4] font-semibold',
      rightDetail: `${props.shipStats.sscc} SSCC`,
      rightDetailClass: 'text-amber-700 dark:text-amber-400 font-semibold',
      progress: shipProgress,
      barClass: 'bg-gradient-to-r from-[#005851] to-[#0284c7]',
      accentClass: 'from-[#005851] via-[#007D74] to-[#38bdf8]',
      activeClass: 'border-[#005851] dark:border-[#00A99D] ring-1 ring-[#005851] dark:ring-[#00A99D] bg-[#F2F8F8] dark:bg-[#0d2428] shadow-md',
      idleHoverClass: 'hover:border-[#005851]/70 dark:hover:border-[#00A99D]/70',
      iconWrapClass: 'bg-[#E0EFEF] dark:bg-[#005851]/35 text-[#005851] dark:text-[#5eead4]',
    },
    {
      key: 'alat',
      title: 'Alkes Haji Pesawat',
      icon: Stethoscope,
      badge: 'Emergensi',
      badgeClass: 'bg-[#F7FCE8] dark:bg-[#7CA622]/20 text-[#557A18] dark:text-[#bef264]',
      value: props.medicalStats.alat,
      unit: 'dok',
      leftDetail: `${props.kpi.approved} Selesai`,
      leftDetailClass: 'text-[#557A18] dark:text-[#bef264] font-semibold',
      rightDetail: `${props.kpi.pending} Proses`,
      rightDetailClass: 'text-amber-700 dark:text-amber-400 font-semibold',
      progress: Math.max(18, props.kpi.completion_rate || 80),
      barClass: 'bg-gradient-to-r from-[#65a30d] to-[#99CC33]',
      accentClass: 'from-[#65a30d] via-[#99CC33] to-[#bef264]',
      activeClass: 'border-[#7CA622] ring-1 ring-[#7CA622] bg-[#F9FCEF] dark:bg-[#192612] shadow-md',
      idleHoverClass: 'hover:border-[#7CA622]/70',
      iconWrapClass: 'bg-[#F7FCE8] dark:bg-[#7CA622]/20 text-[#557A18] dark:text-[#bef264]',
    },
    {
      key: 'obat',
      title: 'Obat P3K Pesawat',
      icon: Pill,
      badge: `Total ${props.kpi.total}`,
      badgeClass: 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300',
      value: props.medicalStats.obat,
      unit: 'dok',
      leftDetail: `${props.kpi.completion_rate}% Verifikasi`,
      leftDetailClass: 'text-[#005851] dark:text-[#5eead4] font-semibold',
      rightDetail: `${props.kpi.today} Hari Ini`,
      rightDetailClass: 'text-slate-600 dark:text-slate-300',
      progress: Math.max(15, props.kpi.completion_rate || 82),
      barClass: 'bg-gradient-to-r from-[#00A99D] to-[#2dd4bf]',
      accentClass: 'from-[#00A99D] via-[#14b8a6] to-[#99CC33]',
      activeClass: 'border-[#00A99D] ring-1 ring-[#00A99D] bg-[#F4FBF9] dark:bg-[#0b2825] shadow-md',
      idleHoverClass: 'hover:border-[#00A99D]/70',
      iconWrapClass: 'bg-[#E6F7F5] dark:bg-[#00A99D]/15 text-[#005851] dark:text-[#5eead4]',
    },
  ];
});
</script>

<template>
  <!-- ========================================================================= -->
  <!-- 4 CLINICAL SERVICE KPI CARDS (ENTERPRISE COMPACT)                         -->
  <!-- ========================================================================= -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 font-sans">
    <button
      v-for="card in cards"
      :key="card.key"
      type="button"
      @click="emit('toggle-module', card.key)"
      :aria-pressed="selectedModuleKey === card.key"
      :title="`Klik untuk memfilter live feed modul ${card.title}`"
      class="relative overflow-hidden w-full text-left rounded-xl p-3 pt-3.5 transition-all duration-200 cursor-pointer border shadow-[0_1px_2px_rgba(15,23,42,0.03)] hover:-translate-y-0.5 hover:shadow-md group"
      :class="selectedModuleKey === card.key
        ? card.activeClass
        : `bg-white dark:bg-[#111820] border-slate-200/90 dark:border-slate-800 ${card.idleHoverClass}`"
    >
      <!-- Top Micro Accent Line -->
      <span
        class="absolute top-0 inset-x-0 h-[2px] bg-gradient-to-r transition-opacity"
        :class="[card.accentClass, selectedModuleKey === card.key ? 'opacity-100' : 'opacity-45 group-hover:opacity-100']"
      ></span>

      <div class="flex items-center justify-between text-xs gap-1">
        <span class="font-semibold flex items-center gap-2 text-slate-700 dark:text-slate-200 min-w-0">
          <span
            class="w-6 h-6 rounded-md flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-105"
            :class="card.iconWrapClass"
          >
            <component :is="card.icon" class="w-3.5 h-3.5" />
          </span>
          <span class="truncate">{{ card.title }}</span>
        </span>
        <span class="inline-flex items-center gap-1 text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded tabular-nums shrink-0" :class="card.badgeClass">
          <span v-if="selectedModuleKey === card.key" class="w-1.5 h-1.5 rounded-full bg-[#00A99D] dark:bg-[#99CC33] animate-pulse"></span>
          <span>{{ card.badge }}</span>
        </span>
      </div>

      <div class="flex items-baseline justify-between mt-2 gap-1">
        <div>
          <span class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight font-mono tabular-nums">
            {{ card.value }}
          </span>
          <span class="text-[11px] text-slate-400 dark:text-slate-500 ml-1">{{ card.unit }}</span>
        </div>
        <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400 tabular-nums truncate">
          <span :class="card.leftDetailClass">{{ card.leftDetail }}</span> &bull;
          <span :class="card.rightDetailClass">{{ card.rightDetail }}</span>
        </div>
      </div>

      <div class="mt-2 w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
        <div
          class="h-full rounded-full transition-all duration-500"
          :class="card.barClass"
          :style="{ width: `${card.progress}%` }"
        ></div>
      </div>
    </button>
  </div>
</template>
