<script setup>
import { ref, computed } from 'vue';
import BaseChart from '../../../Components/BaseChart.vue';
import { Crosshair, Search } from 'lucide-vue-next';

const props = defineProps({
  officesList: { type: Array, default: () => [] },
  filteredOffices: { type: Array, default: () => [] },
  selectedOffice: { type: Object, default: null },
  maxOfficeTotal: { type: Number, default: 1 },
  activeOfficeFilter: { type: String, default: 'all' },
  searchOffice: { type: String, default: '' },
  sinkarkes: { type: Object, default: () => ({}) },
  kpi: { type: Object, default: () => ({}) },
  shipStats: { type: Object, default: () => ({}) },
  medicalStats: { type: Object, default: () => ({}) },
});

const emit = defineEmits([
  'update:activeOfficeFilter',
  'update:searchOffice',
  'select-office',
]);

const selectedRadarCompareMode = ref('top'); // 'top' | 'all'

const wilkerRadarOption = computed(() => {
  const comparison = props.sinkarkes?.wilker_comparison || [];
  const matrix = props.sinkarkes?.matrix || [];

  const rows = comparison.map((c, idx) => {
    const mRow = matrix.find((m) => m.wilker === c.name) || {};
    const officeMatch = props.officesList.find(
      (o) => (o.short_name && o.short_name.includes(c.short_name)) || (o.name && o.name.includes(c.short_name))
    );
    const dailyInspect = officeMatch?.total || 0;

    return {
      name: c.short_name || c.name || `Wilker ${idx + 1}`,
      alat_angkut: Number(c.total_alat_angkut || 0) + dailyInspect * 2,
      orang: Number(c.total_orang || 0) + dailyInspect * 15,
      dokumen: Number(c.total_dokumen || 0) + dailyInspect * 3,
      layanan: Number(c.total_layanan || 0) + dailyInspect * 4,
      kepatuhan: Number(mRow.compliance_percent || 85),
      daily_ops: dailyInspect,
    };
  });

  const maxAlat = Math.max(...rows.map((r) => r.alat_angkut), 100);
  const maxOrang = Math.max(...rows.map((r) => r.orang), 500);
  const maxDok = Math.max(...rows.map((r) => r.dokumen), 100);
  const maxLayanan = Math.max(...rows.map((r) => r.layanan), 100);

  const sortedRows = [...rows].sort(
    (a, b) => b.alat_angkut + b.dokumen + b.daily_ops - (a.alat_angkut + a.dokumen + a.daily_ops)
  );
  const displayRows = selectedRadarCompareMode.value === 'all' ? sortedRows : sortedRows.slice(0, 4);

  const palette = [
    { line: '#00A99D', fill: 'rgba(0, 169, 157, 0.28)' },
    { line: '#99CC33', fill: 'rgba(153, 204, 51, 0.24)' },
    { line: '#38bdf8', fill: 'rgba(56, 189, 248, 0.22)' },
    { line: '#f59e0b', fill: 'rgba(245, 158, 11, 0.20)' },
    { line: '#a855f7', fill: 'rgba(168, 85, 247, 0.18)' },
    { line: '#f43f5e', fill: 'rgba(244, 63, 94, 0.18)' },
    { line: '#10b981', fill: 'rgba(16, 185, 129, 0.18)' },
  ];

  return {
    backgroundColor: 'transparent',
    tooltip: {
      trigger: 'item',
      backgroundColor: '#002D29',
      borderColor: '#00A99D',
      borderWidth: 1,
      textStyle: { color: '#f8fafc', fontSize: 11 },
    },
    legend: {
      bottom: 0,
      data: displayRows.map((r) => r.name),
      textStyle: { color: '#475569', fontSize: 10, fontWeight: 'bold' },
      itemWidth: 10,
      itemHeight: 10,
    },
    radar: {
      shape: 'polygon',
      center: ['50%', '46%'],
      radius: '64%',
      splitNumber: 4,
      axisName: { color: '#005851', fontSize: 10, fontWeight: 'bold' },
      splitLine: {
        lineStyle: {
          color: [
            'rgba(0, 169, 157, 0.15)',
            'rgba(0, 169, 157, 0.25)',
            'rgba(0, 169, 157, 0.35)',
            'rgba(0, 88, 81, 0.45)',
          ],
        },
      },
      splitArea: {
        show: true,
        areaStyle: { color: ['rgba(230, 247, 245, 0.45)', 'rgba(255, 255, 255, 0.8)'] },
      },
      axisLine: { lineStyle: { color: 'rgba(0, 169, 157, 0.35)' } },
      indicator: [
        { name: 'Alat Angkut', max: Math.ceil(maxAlat * 1.15) },
        { name: 'Penumpang/Kru', max: Math.ceil(maxOrang * 1.15) },
        { name: 'Dokumen', max: Math.ceil(maxDok * 1.15) },
        { name: 'Layanan UKP', max: Math.ceil(maxLayanan * 1.15) },
        { name: 'Kepatuhan (%)', max: 100 },
      ],
    },
    series: [
      {
        name: 'Radar Kinerja Wilayah Kerja',
        type: 'radar',
        symbol: 'circle',
        symbolSize: 6,
        data: displayRows.map((r, idx) => {
          const col = palette[idx % palette.length];
          return {
            value: [r.alat_angkut, r.orang, r.dokumen, r.layanan, r.kepatuhan],
            name: r.name,
            itemStyle: { color: col.line },
            lineStyle: { width: 2.2, color: col.line },
            areaStyle: { color: col.fill },
          };
        }),
      },
    ],
  };
});

const modaReadinessRadarOption = computed(() => {
  const aircraftScore = Number(props.kpi?.compliance_rate || 92);
  const shipTotal = Number(props.shipStats?.total || 1);
  const shipClean = Math.max(0, shipTotal - Number(props.shipStats?.ships_with_risk || 0));
  const shipScore = shipTotal > 0 ? Math.round((shipClean / shipTotal) * 100) : 90;
  const completionScore = Number(props.kpi?.completion_rate || 88);
  const sinkarkesCompliance = Number(props.sinkarkes?.kpi?.compliance_rate || 85);
  const alkesScore = Number(props.medicalStats?.alat || 0) > 0 ? 94 : 82;
  const obatScore = Number(props.medicalStats?.obat || 0) > 0 ? 92 : 80;

  return {
    backgroundColor: 'transparent',
    tooltip: {
      trigger: 'item',
      backgroundColor: '#002D29',
      borderColor: '#99CC33',
      borderWidth: 1,
      textStyle: { color: '#f8fafc', fontSize: 11 },
    },
    legend: {
      bottom: 0,
      data: ['Kesiapsiagaan Aktual (%)', 'Target Standar IHR (%)'],
      textStyle: { color: '#475569', fontSize: 10, fontWeight: 'bold' },
      itemWidth: 10,
      itemHeight: 10,
    },
    radar: {
      shape: 'circle',
      center: ['50%', '46%'],
      radius: '64%',
      splitNumber: 4,
      axisName: { color: '#005851', fontSize: 10, fontWeight: 'bold' },
      splitLine: {
        lineStyle: {
          color: [
            'rgba(0, 169, 157, 0.2)',
            'rgba(0, 169, 157, 0.3)',
            'rgba(153, 204, 51, 0.35)',
            'rgba(0, 88, 81, 0.5)',
          ],
        },
      },
      splitArea: {
        show: true,
        areaStyle: { color: ['rgba(0, 169, 157, 0.06)', 'rgba(153, 204, 51, 0.05)'] },
      },
      axisLine: { lineStyle: { color: 'rgba(0, 169, 157, 0.35)' } },
      indicator: [
        { name: 'Laik Pesawat', max: 100 },
        { name: 'Bebas Risiko Kapal', max: 100 },
        { name: 'Kesiapan Alkes', max: 100 },
        { name: 'Stok Obat P3K', max: 100 },
        { name: 'Verifikasi Dok', max: 100 },
        { name: 'Pelaporan SINKARKES', max: 100 },
      ],
    },
    series: [
      {
        type: 'radar',
        symbol: 'circle',
        symbolSize: 6,
        data: [
          {
            value: [aircraftScore, shipScore, alkesScore, obatScore, completionScore, sinkarkesCompliance],
            name: 'Kesiapsiagaan Aktual (%)',
            itemStyle: { color: '#00A99D' },
            lineStyle: { width: 2.5, color: '#00A99D' },
            areaStyle: {
              color: {
                type: 'radial',
                x: 0.5,
                y: 0.5,
                r: 0.7,
                colorStops: [
                  { offset: 0, color: 'rgba(153, 204, 51, 0.45)' },
                  { offset: 1, color: 'rgba(0, 169, 157, 0.25)' },
                ],
              },
            },
          },
          {
            value: [95, 95, 95, 95, 95, 95],
            name: 'Target Standar IHR (%)',
            itemStyle: { color: '#99CC33' },
            lineStyle: { width: 1.5, color: '#7CA622', type: 'dashed' },
            areaStyle: { color: 'rgba(153, 204, 51, 0.06)' },
          },
        ],
      },
    ],
  };
});
</script>

<template>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 font-sans">
    <!-- Panel 1: Wilker Telemetry Dock -->
    <div class="lg:col-span-4 rounded-xl bg-white border border-slate-200/90 p-3.5 shadow-[0_1px_2px_rgba(15,23,42,0.03)] flex flex-col justify-between">
      <div>
        <div class="space-y-2 pb-2 border-b border-slate-200">
          <div class="flex items-center justify-between gap-1">
            <div class="flex items-center gap-1">
              <button
                type="button"
                @click="emit('update:activeOfficeFilter', 'all')"
                class="px-2 py-0.5 rounded text-[11px] font-semibold transition-colors cursor-pointer"
                :class="activeOfficeFilter === 'all' ? 'bg-[#005851] text-white' : 'text-slate-600 hover:text-[#005851] hover:bg-slate-50'"
              >
                Semua (7)
              </button>
              <button
                type="button"
                @click="emit('update:activeOfficeFilter', 'udara')"
                class="px-2 py-0.5 rounded text-[11px] font-semibold transition-colors cursor-pointer"
                :class="activeOfficeFilter === 'udara' ? 'bg-[#005851] text-white' : 'text-slate-600 hover:text-[#005851] hover:bg-slate-50'"
              >
                Udara (3)
              </button>
              <button
                type="button"
                @click="emit('update:activeOfficeFilter', 'laut')"
                class="px-2 py-0.5 rounded text-[11px] font-semibold transition-colors cursor-pointer"
                :class="activeOfficeFilter === 'laut' ? 'bg-[#005851] text-white' : 'text-slate-600 hover:text-[#005851] hover:bg-slate-50'"
              >
                Laut (4)
              </button>
            </div>

            <span class="text-[10px] font-mono font-semibold text-[#005851] bg-[#E6F7F5] px-1.5 py-0.2 rounded flex items-center gap-1">
              <span class="w-1.5 h-1.5 rounded-full bg-[#00A99D]"></span>
              SIAGA
            </span>
          </div>

          <div class="relative w-full">
            <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
            <input
              :value="searchOffice"
              @input="emit('update:searchOffice', $event.target.value)"
              type="text"
              placeholder="Cari wilker / alamat..."
              class="w-full pl-8 pr-2 py-1 rounded-md bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#00A99D] focus:bg-white"
            />
          </div>
        </div>

        <div class="space-y-1 overflow-y-auto max-h-[235px] mt-2 pr-1">
          <div
            v-for="office in filteredOffices"
            :key="office.no"
            @click="emit('select-office', office)"
            class="py-1.5 px-2.5 rounded-lg transition-colors cursor-pointer flex flex-col gap-1 border"
            :class="selectedOffice?.no === office.no
              ? 'bg-[#F4FBF9] border-[#00A99D] ring-1 ring-[#00A99D]'
              : 'bg-slate-50/60 hover:bg-white border-slate-200/70'"
          >
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-2 min-w-0">
                <span class="w-5 h-5 rounded-md bg-[#005851] text-white text-[9px] font-mono font-black flex items-center justify-center shrink-0">
                  {{ office.no }}
                </span>
                <div class="min-w-0">
                  <div class="text-xs font-bold text-slate-900 truncate" :title="office.name">
                    {{ office.short_name || office.name }}
                  </div>
                  <div class="text-[10px] text-slate-500 truncate flex items-center gap-1">
                    <span>{{ office.moda }}</span>
                    <span>&bull;</span>
                    <span class="text-slate-400">{{ office.zone }}</span>
                  </div>
                </div>
              </div>

              <div class="flex items-center gap-2 shrink-0">
                <div class="text-right">
                  <span class="text-xs font-black text-[#005851] font-mono">{{ office.total || 0 }}</span>
                  <span class="text-[9px] text-slate-400 ml-0.5">dok</span>
                </div>
                <button
                  type="button"
                  @click.stop="emit('select-office', office)"
                  :aria-label="`Kunci radar ke ${office.short_name || office.name}`"
                  title="Kunci radar ke wilker ini"
                  class="w-6 h-6 rounded-md bg-slate-100 hover:bg-[#00A99D] hover:text-white text-slate-400 flex items-center justify-center transition-colors cursor-pointer"
                >
                  <Crosshair class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>

            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
              <div
                class="h-full rounded-full shimmer-bar transition-all duration-500"
                :class="selectedOffice?.no === office.no ? 'bg-[#00A99D]' : 'bg-[#99CC33]/80'"
                :style="{ width: `${Math.max(10, ((office.total || 0) / maxOfficeTotal) * 100)}%` }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <div class="pt-2 mt-2 border-t border-slate-200 text-[11px] text-slate-500 flex items-center justify-between font-mono">
        <span>Target: <strong class="text-[#005851] font-sans">{{ selectedOffice?.short_name || '-' }}</strong></span>
        <span class="text-[#005851] font-bold">{{ selectedOffice?.total || 0 }} Dok</span>
      </div>
    </div>

    <!-- Panel 2: Multi-Dimensional Wilker Radar -->
    <div class="lg:col-span-4 rounded-xl bg-white border border-slate-200/90 p-3.5 shadow-[0_1px_2px_rgba(15,23,42,0.03)] flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between gap-2 mb-1">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#00A99D]"></span>
            <h3 class="text-xs sm:text-[13px] font-bold text-slate-900">
              Radar Performa 5-Dimensi Wilker
            </h3>
          </div>

          <div class="inline-flex items-center p-0.5 rounded-md bg-slate-100 border border-slate-200 text-[10px] font-semibold">
            <button
              type="button"
              @click="selectedRadarCompareMode = 'top'"
              class="px-2 py-0.5 rounded cursor-pointer transition-colors"
              :class="selectedRadarCompareMode === 'top' ? 'bg-[#005851] text-white' : 'text-slate-600'"
            >
              Top 4
            </button>
            <button
              type="button"
              @click="selectedRadarCompareMode = 'all'"
              class="px-2 py-0.5 rounded cursor-pointer transition-colors"
              :class="selectedRadarCompareMode === 'all' ? 'bg-[#005851] text-white' : 'text-slate-600'"
            >
              7 Wilker
            </button>
          </div>
        </div>
        <p class="text-[11px] text-slate-500 mb-1">
          Pemetaan poligon radar volume Alat Angkut, Orang, Dokumen, UKP &amp; Kepatuhan.
        </p>
      </div>

      <div class="w-full h-[255px]">
        <BaseChart :option="wilkerRadarOption" height="255px" />
      </div>
    </div>

    <!-- Panel 3: Biosecurity Readiness Radar -->
    <div class="lg:col-span-4 rounded-xl bg-white border border-slate-200/90 p-3.5 shadow-[0_1px_2px_rgba(15,23,42,0.03)] flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between gap-2 mb-1">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#7CA622]"></span>
            <h3 class="text-xs sm:text-[13px] font-bold text-slate-900">
              Kesiapsiagaan Biosecurity &amp; 4 Moda
            </h3>
          </div>
          <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-[#E6F7F5] text-[#005851] border border-[#99DFD6]/70">
            IHR 2005
          </span>
        </div>
        <p class="text-[11px] text-slate-500 mb-1">
          Indeks kesiapsiagaan aktual 6 parameter pengawasan vs standar IHR (95%).
        </p>
      </div>

      <div class="w-full h-[255px]">
        <BaseChart :option="modaReadinessRadarOption" height="255px" />
      </div>
    </div>
  </div>
</template>
