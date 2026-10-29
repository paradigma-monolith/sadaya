<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useAppStore } from '../stores/appStore';
import BaseChart from '../../Components/BaseChart.vue';
import { 
  RotateCw,
  MoreHorizontal,
  ExternalLink,
  Compass,
  MessageSquare,
  ThumbsUp,
  ClipboardCheck
} from 'lucide-vue-next';

const store = useAppStore();

const getUrl = (path) => {
  if (typeof window !== 'undefined' && window.location.pathname.includes('/hybridlaravel/public')) {
    return `/hybridlaravel/public${path}`;
  }
  return path;
};

const isDark = ref(false);
const updateThemeState = () => {
  if (typeof document !== 'undefined') {
    isDark.value = document.documentElement.classList.contains('dark');
  }
};

let themeObserver = null;
onMounted(async () => {
  updateThemeState();
  if (typeof MutationObserver !== 'undefined' && typeof document !== 'undefined') {
    themeObserver = new MutationObserver(() => updateThemeState());
    themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
  }
  await store.fetchStats();
});

onUnmounted(() => {
  if (themeObserver) {
    themeObserver.disconnect();
    themeObserver = null;
  }
});

const heroWaveOption = computed(() => {
  return {
    grid: { left: 0, right: 0, top: 10, bottom: 10 },
    xAxis: { type: 'category', show: false, boundaryGap: false, data: ['M', 'T', 'W', 'T', 'F', 'S', 'S'] },
    yAxis: { type: 'value', show: false },
    series: [
      { type: 'line', smooth: 0.55, symbol: 'none', data: [14, 22, 16, 26, 19, 28, 24], lineStyle: { width: 2, color: '#FFFFFF' } },
      { type: 'line', smooth: 0.55, symbol: 'none', data: [10, 16, 21, 15, 24, 18, 22], lineStyle: { width: 2, color: '#FFD166' } },
      { type: 'line', smooth: 0.55, symbol: 'none', data: [8, 12, 11, 18, 14, 20, 17], lineStyle: { width: 2, color: '#7B2CBF' } },
    ],
  };
});

const donutOption = computed(() => {
  return {
    series: [
      {
        type: 'pie',
        radius: ['84%', '94%'],
        silent: true,
        label: { show: false },
        data: [{ value: 100, itemStyle: { color: isDark.value ? 'rgba(255,255,255,0.04)' : '#F5F4F0' } }],
      },
      {
        type: 'pie',
        radius: ['66%', '79%'],
        padAngle: 4,
        itemStyle: { borderRadius: 12 },
        label: { show: false },
        data: [
          { value: 45, name: 'REST API', itemStyle: { color: isDark.value ? '#F8FAFC' : '#111322' } },
          { value: 30, name: 'Pinia Store', itemStyle: { color: '#FF7556' } },
          { value: 15, name: 'Sanctum', itemStyle: { color: '#F84593' } },
          { value: 10, name: 'Cache', itemStyle: { color: '#FFD166' } },
        ],
      },
    ],
  };
});

const postStatsOption = computed(() => {
  const vals = [38, 52, 46, 88, 54, 64, 50];
  const barData = vals.map((v, idx) => ({
    value: v,
    itemStyle: {
      borderRadius: [16, 16, 16, 16],
      color: idx === 3
        ? {
            type: 'linear',
            x: 0, y: 0, x2: 0, y2: 1,
            colorStops: [
              { offset: 0, color: '#FF7E55' },
              { offset: 1, color: '#F84593' },
            ],
          }
        : (isDark.value ? '#262A40' : '#D9D8D4'),
    },
  }));

  return {
    grid: { left: '4%', right: '4%', top: '10%', bottom: '14%' },
    xAxis: {
      type: 'category',
      data: ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
      axisLine: { show: false },
      axisTick: { show: false },
      axisLabel: { color: isDark.value ? '#94A3B8' : '#A0A09C', fontSize: 11, fontWeight: 600, margin: 14 },
    },
    yAxis: { type: 'value', show: false },
    series: [
      { type: 'bar', barWidth: 14, data: barData, z: 2 },
      { type: 'line', smooth: 0.5, symbol: 'none', data: [22, 30, 25, 38, 28, 34, 29], lineStyle: { width: 1.5, color: isDark.value ? '#F8FAFC' : '#111322' }, z: 3 },
      { type: 'line', smooth: 0.5, symbol: 'none', data: [18, 24, 29, 26, 33, 25, 31], lineStyle: { width: 1.5, color: '#FF7556' }, z: 3 },
    ],
  };
});

const spaRows = computed(() => [
  {
    id: 1,
    title: 'Sinkronisasi State Pinia & REST API v1',
    subtitle: 'Endpoint /api/v1/stats • Sanctum Stateful',
    category: 'SPA Client',
    metric: `${store.apiLatencyMs ?? 12}ms`,
    trend: { label: '↑ 99%', up: true },
    avatar: 'https://ui-avatars.com/api/?name=SP&background=FF7556&color=fff',
    href: getUrl('/inertia/dashboard'),
  },
  {
    id: 2,
    title: 'Dashboard Inspeksi Sanitasi Pesawat BKK',
    subtitle: 'Beralih ke Mode Inertia Bridge',
    category: 'BKK Aviation',
    metric: '7.k',
    trend: { label: '↑ 5%', up: true },
    avatar: 'https://ui-avatars.com/api/?name=BK&background=FFD166&color=111322',
    href: getUrl('/aircraft-inspections/dashboard'),
  },
  {
    id: 3,
    title: 'Dokumentasi OpenAPI Scramble Interaktif',
    subtitle: 'Spesifikasi Endpoint REST API Otomatis',
    category: 'OpenAPI',
    metric: '3.k',
    trend: { label: '↑ 8%', up: true },
    avatar: 'https://ui-avatars.com/api/?name=AP&background=F84593&color=fff',
    href: getUrl('/docs/api'),
  },
]);
</script>

<template>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
    <!-- Left 8 Columns -->
    <div class="lg:col-span-8 space-y-7">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Coral-Pink Gradient Card -->
        <div class="relative overflow-hidden rounded-[34px] bg-gradient-to-br from-[#FF7E55] via-[#FF5A76] to-[#F74292] p-6 sm:p-7 text-white shadow-[0_20px_45px_-15px_rgba(255,90,118,0.5)] flex flex-col justify-between min-h-[290px]">
          <div>
            <div class="flex items-center justify-between">
              <span class="text-xs sm:text-sm font-medium text-white/90">SPA Telemetry Latency</span>
              <button type="button" @click="store.fetchStats" class="p-1.5 rounded-lg hover:bg-white/15 transition-colors cursor-pointer" title="Segarkan Data API">
                <RotateCw class="w-4 h-4" :class="{ 'animate-spin': store.loading }" />
              </button>
            </div>
            <div class="text-3xl sm:text-[34px] font-extrabold tracking-tight mt-1.5">
              {{ store.apiLatencyMs !== null ? `${store.apiLatencyMs}.0ms` : '14.0ms' }}
            </div>
          </div>

          <div class="my-2 -mx-2">
            <BaseChart :option="heroWaveOption" height="105px" />
          </div>

          <div class="grid grid-cols-3 pt-2">
            <div class="pr-3">
              <span class="block text-[11px] text-white/80">Vue Router</span>
              <span class="block text-lg font-bold mt-1">%20</span>
            </div>
            <div class="px-4 border-x border-dashed border-white/35">
              <span class="block text-[11px] text-white/80">Pinia Store</span>
              <span class="block text-lg font-bold mt-1">%50</span>
            </div>
            <div class="pl-4">
              <span class="block text-[11px] text-white/80">REST API</span>
              <span class="block text-lg font-bold mt-1">%30</span>
            </div>
          </div>
        </div>

        <!-- Clean White Donut Card -->
        <div class="rounded-[34px] bg-white dark:bg-[#16192B] p-6 sm:p-7 shadow-[0_12px_35px_rgba(0,0,0,0.03)] flex flex-col justify-between min-h-[290px]">
          <div class="flex items-center justify-between">
            <span class="text-xs sm:text-sm font-bold text-[#111322] dark:text-white">Total API Records</span>
            <MoreHorizontal class="w-5 h-5 text-slate-400" />
          </div>

          <div class="flex items-center justify-between gap-2 my-auto pt-2">
            <div class="space-y-5">
              <div class="text-3xl sm:text-[34px] font-extrabold text-[#111322] dark:text-white">
                {{ store.metrics.total_activities ?? 20 }}.k
              </div>
              <div class="space-y-3">
                <div>
                  <div class="flex items-center gap-2 text-xs text-slate-400">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#FF7556]"></span>
                    <span>Authenticated</span>
                  </div>
                  <div class="text-sm font-extrabold text-[#111322] dark:text-white mt-0.5">%80</div>
                </div>
                <div>
                  <div class="flex items-center gap-2 text-xs text-slate-400">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#FFD166]"></span>
                    <span>Public</span>
                  </div>
                  <div class="text-sm font-extrabold text-[#111322] dark:text-white mt-0.5">%20</div>
                </div>
              </div>
            </div>

            <div class="relative w-40 h-40 sm:w-44 sm:h-44 shrink-0 flex items-center justify-center">
              <BaseChart :option="donutOption" height="176px" />
              <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <svg viewBox="0 0 64 64" class="w-11 h-11 drop-shadow-[0_6px_10px_rgba(217,83,79,0.35)]" fill="none">
                  <rect x="26" y="10" width="12" height="44" rx="6" transform="rotate(-35 32 32)" fill="#FF7556" />
                  <rect x="26" y="10" width="12" height="44" rx="6" transform="rotate(55 32 32)" fill="#C84B31" />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Floating Pill Rows -->
      <div class="space-y-4">
        <div class="flex items-center gap-7 border-b border-black/[0.06] dark:border-white/10 pb-3">
          <span class="relative text-xs sm:text-sm font-bold text-[#111322] dark:text-white after:content-[''] after:absolute after:left-0 after:right-0 after:-bottom-[13px] after:h-[2.5px] after:bg-[#111322] dark:after:bg-white after:rounded-full">
            SPA Activity
          </span>
        </div>

        <div class="space-y-3">
          <a
            v-for="row in spaRows"
            :key="row.id"
            :href="row.href"
            class="group bg-white dark:bg-[#16192B] rounded-[22px] px-4 sm:px-5 py-3.5 shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-md transition-all flex items-center justify-between gap-4"
          >
            <div class="flex items-center gap-3.5 min-w-0 flex-1">
              <img :src="row.avatar" alt="" class="w-11 h-11 rounded-2xl object-cover shrink-0" />
              <div class="min-w-0">
                <div class="flex items-center gap-2">
                  <span class="text-xs sm:text-[13px] font-bold text-[#111322] dark:text-white truncate group-hover:text-[#FF5C77]">
                    {{ row.title }}
                  </span>
                  <ExternalLink class="w-3.5 h-3.5 text-slate-300 shrink-0" />
                </div>
                <span class="block text-[11px] text-slate-400 mt-0.5 truncate">{{ row.subtitle }}</span>
              </div>
            </div>

            <div class="hidden sm:block w-28 text-center">
              <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ row.category }}</span>
            </div>

            <div class="flex items-center gap-3 shrink-0">
              <span class="w-7 h-7 rounded-full bg-[#111322] text-white flex items-center justify-center">
                <Compass class="w-3.5 h-3.5" />
              </span>
              <span class="text-xs font-bold text-[#111322] dark:text-white">{{ row.metric }}</span>
              <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-[#E6F8F0] text-[#10B981]">
                {{ row.trend.label }}
              </span>
            </div>
          </a>
        </div>
      </div>
    </div>

    <!-- Right 4 Columns -->
    <div class="lg:col-span-4 space-y-6">
      <div class="bg-white dark:bg-[#16192B] rounded-[30px] p-4 sm:p-5 shadow-[0_12px_35px_rgba(0,0,0,0.03)] flex items-center gap-5">
        <div class="w-20 h-20 rounded-[22px] bg-[#FADCD9] dark:bg-[#FF5C77]/20 flex flex-col items-center justify-center shrink-0 text-[#111322] dark:text-white">
          <span class="text-lg font-extrabold leading-none">{{ store.metrics.total_users ?? 3 }}.k</span>
          <MessageSquare class="w-4 h-4 mt-2 text-[#111322] dark:text-[#FF7556]" />
        </div>
        <div class="flex-1 min-w-0 pr-2">
          <div class="text-sm font-bold text-[#111322] dark:text-white">Total Users</div>
          <div class="w-full h-2.5 rounded-full bg-[#F4F3EF] dark:bg-slate-800 mt-3 overflow-hidden">
            <div class="h-full w-2/5 rounded-full bg-gradient-to-r from-[#F84593] to-[#FF6B4A]"></div>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-[#16192B] rounded-[34px] p-6 sm:p-7 shadow-[0_12px_35px_rgba(0,0,0,0.03)]">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-bold text-[#111322] dark:text-white">API Throughput</span>
          <MoreHorizontal class="w-5 h-5 text-slate-400" />
        </div>
        <BaseChart :option="postStatsOption" height="235px" />
        <div class="mt-3 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-[#FADCD9] dark:bg-[#FF5C77]/20 flex items-center justify-center text-[#111322] dark:text-[#FF7556]">
              <ClipboardCheck class="w-5 h-5" />
            </div>
            <div>
              <span class="block text-xs font-bold text-[#111322] dark:text-white">Completed Requests</span>
              <span class="block text-[11px] text-slate-400 mt-0.5">Current Week</span>
            </div>
          </div>
          <div class="text-xl font-extrabold text-[#111322] dark:text-white">874</div>
        </div>
      </div>

      <div class="bg-white dark:bg-[#16192B] rounded-[30px] p-4 sm:p-5 shadow-[0_12px_35px_rgba(0,0,0,0.03)] flex items-center gap-5">
        <div class="w-20 h-20 rounded-[22px] bg-[#FADCD9] dark:bg-[#FF5C77]/20 flex flex-col items-center justify-center shrink-0 text-[#111322] dark:text-white">
          <span class="text-lg font-extrabold leading-none">10.k</span>
          <ThumbsUp class="w-4 h-4 mt-2 text-[#111322] dark:text-[#FF7556]" />
        </div>
        <div class="flex-1 min-w-0 pr-2">
          <div class="text-sm font-bold text-[#111322] dark:text-white">Sanctum Tokens</div>
          <div class="w-full h-2.5 rounded-full bg-[#F4F3EF] dark:bg-slate-800 mt-3 overflow-hidden">
            <div class="h-full w-3/5 rounded-full bg-[#111322] dark:bg-white"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
