<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import BaseChart from '../../Components/BaseChart.vue';
import {
  Users,
  ShieldCheck,
  History,
  Database,
  Radio,
  Activity,
  Layers,
  ArrowRight,
  Download,
  User as UserIcon,
  CheckCircle2,
  Clock,
  Sparkles,
  BookOpen,
  Code,
  Plane
} from 'lucide-vue-next';

const props = defineProps({
  metrics: {
    type: Object,
    required: true,
  },
  recent_activities: {
    type: Array,
    default: () => [],
  },
  recent_logins: {
    type: Array,
    default: () => [],
  },
  chart_data: {
    type: Object,
    default: () => ({ dates: [], counts: [] }),
  },
  system: {
    type: Object,
    required: true,
  },
});

const getUrl = (path) => {
  if (typeof window !== 'undefined' && window.location.pathname.includes('/hybridlaravel/public')) {
    return `/hybridlaravel/public${path}`;
  }
  return path;
};

// Theme Detection for ECharts Options
const isDark = ref(false);
const updateThemeState = () => {
  if (typeof document !== 'undefined') {
    isDark.value = document.documentElement.classList.contains('dark');
  }
};

let themeObserver = null;
onMounted(() => {
  updateThemeState();
  if (typeof MutationObserver !== 'undefined' && typeof document !== 'undefined') {
    themeObserver = new MutationObserver(() => updateThemeState());
    themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
  }
});

onUnmounted(() => {
  if (themeObserver) {
    themeObserver.disconnect();
    themeObserver = null;
  }
});

// Chart Colors & Styling Tokens
const textColor = computed(() => isDark.value ? '#cbd5e1' : '#475569');
const gridLineColor = computed(() => isDark.value ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)');

// Activity & Login Trend Chart Option (ECharts)
const activityChartOption = computed(() => {
  const dates = props.chart_data?.dates || [];
  const counts = props.chart_data?.counts || [];

  return {
    tooltip: {
      trigger: 'axis',
      backgroundColor: isDark.value ? '#0f172a' : '#ffffff',
      borderColor: isDark.value ? '#334155' : '#e2e8f0',
      textStyle: { color: isDark.value ? '#f8fafc' : '#0f172a', fontSize: 12 },
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '5%',
      top: '12%',
      containLabel: true,
    },
    xAxis: {
      type: 'category',
      data: dates,
      axisLabel: { color: textColor.value, fontSize: 11 },
      axisLine: { lineStyle: { color: isDark.value ? '#334155' : '#cbd5e1' } },
      boundaryGap: false,
    },
    yAxis: {
      type: 'value',
      minInterval: 1,
      axisLabel: { color: textColor.value, fontSize: 11 },
      splitLine: { lineStyle: { color: gridLineColor.value, type: 'dashed' } },
    },
    series: [
      {
        name: 'Aktivitas & Sesi',
        type: 'line',
        smooth: true,
        data: counts,
        symbolSize: 8,
        itemStyle: { color: '#6366f1' },
        lineStyle: { width: 3, color: '#6366f1' },
        areaStyle: {
          color: {
            type: 'linear',
            x: 0,
            y: 0,
            x2: 0,
            y2: 1,
            colorStops: [
              { offset: 0, color: 'rgba(99, 102, 241, 0.45)' },
              { offset: 1, color: 'rgba(99, 102, 241, 0.02)' },
            ],
          },
        },
      },
    ],
  };
});
</script>

<template>
  <Head title="Pusat Kontrol & Dashboard" />

  <HybridNavbar mode="inertia" currentRoute="dashboard">
    <main class="w-full px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">
      <!-- Welcome Hero Banner -->
      <div class="relative bg-gradient-to-r from-indigo-900/30 via-purple-900/20 to-slate-900/60 border border-indigo-700/30 dark:border-indigo-800/40 rounded-3xl p-6 sm:p-8 backdrop-blur-sm shadow-xl">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
          <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-950/80 text-indigo-300 border border-indigo-700/40 mb-3">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              <span>Sesi Aktif: {{ $page.props.auth?.user?.name || 'Administrator' }}</span>
              <span class="text-indigo-400">&bull;</span>
              <span class="font-mono">{{ ($page.props.auth?.user?.roles || ['Super Admin'])[0] }}</span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
              Selamat Datang di Pusat Kontrol Sistem
            </h1>
            <p class="mt-2 text-slate-600 dark:text-slate-300 text-sm max-w-2xl leading-relaxed">
              Platform starter kit siap pakai dengan arsitektur hybrid modern. Seluruh halaman demo telah dibersihkan, fondasi keamanan, autentikasi, dan layanan real-time telah aktif untuk membangun sistem Anda.
            </p>
          </div>

          <!-- Quick Action Buttons -->
          <div class="flex flex-wrap items-center gap-3">
            <a
              :href="getUrl('/aircraft-inspections')"
              class="px-4 py-2.5 rounded-xl font-semibold text-xs text-white bg-sky-600 hover:bg-sky-500 transition-all shadow-md shadow-sky-600/30 flex items-center gap-1.5 group cursor-pointer"
            >
              <Plane class="w-4 h-4" />
              <span>Inspeksi Pesawat BKK</span>
              <ArrowRight class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
            </a>

            <a
              :href="getUrl('/spa')"
              class="px-4 py-2.5 rounded-xl font-semibold text-xs text-white bg-emerald-600 hover:bg-emerald-500 transition-all shadow-md shadow-emerald-600/30 flex items-center gap-1.5 group cursor-pointer"
            >
              <Layers class="w-4 h-4" />
              <span>Buka SPA Mode</span>
              <ArrowRight class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
            </a>

            <a
              :href="getUrl('/exports/audit-logs/pdf')"
              class="px-4 py-2.5 rounded-xl font-semibold text-xs text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 transition-all shadow-xs flex items-center gap-1.5 cursor-pointer"
            >
              <Download class="w-4 h-4 text-rose-500" />
              <span>Unduh Audit PDF</span>
            </a>

            <a
              :href="getUrl('/profile')"
              class="px-4 py-2.5 rounded-xl font-semibold text-xs text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 transition-all shadow-xs flex items-center gap-1.5 cursor-pointer"
            >
              <UserIcon class="w-4 h-4 text-indigo-500" />
              <span>Profil Saya</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Top Metric Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Database -->
        <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
          <div class="flex items-center justify-between">
            <span class="text-xs font-mono uppercase tracking-wider text-slate-500">Basis Data Utama</span>
            <Database class="w-4 h-4 text-teal-500" />
          </div>
          <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            <span>MySQL 8.0</span>
          </div>
          <div class="text-[11px] text-slate-500 mt-1 font-mono truncate">
            DB: {{ system.database.database }}
          </div>
        </div>

        <!-- Total Users -->
        <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
          <div class="flex items-center justify-between">
            <span class="text-xs font-mono uppercase tracking-wider text-slate-500">Pengguna Terdaftar</span>
            <Users class="w-4 h-4 text-indigo-500" />
          </div>
          <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">
            {{ metrics.total_users }} Akun
          </div>
          <div class="text-[11px] text-slate-500 mt-1">
            {{ metrics.total_roles }} Role Hak Akses (RBAC)
          </div>
        </div>

        <!-- Audit Trail -->
        <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
          <div class="flex items-center justify-between">
            <span class="text-xs font-mono uppercase tracking-wider text-slate-500">Aktivitas Audit</span>
            <History class="w-4 h-4 text-purple-500" />
          </div>
          <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">
            {{ metrics.total_activities }} Log
          </div>
          <div class="text-[11px] text-slate-500 mt-1">
            Terekam otomatis via Spatie
          </div>
        </div>

        <!-- Real-Time WebSocket -->
        <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
          <div class="flex items-center justify-between">
            <span class="text-xs font-mono uppercase tracking-wider text-slate-500">Layanan Real-Time</span>
            <Radio class="w-4 h-4 text-cyan-500 animate-pulse" />
          </div>
          <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
            <span>Reverb &amp; Octane</span>
          </div>
          <div class="text-[11px] text-slate-500 mt-1">
            WebSocket port {{ system.infrastructure.reverb.port }} (Echo siap)
          </div>
        </div>
      </div>

      <!-- Interactive ECharts Section -->
      <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
          <div>
            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
              <Activity class="w-5 h-5 text-indigo-500" />
              <span>Aktivitas Sistem &amp; Sesi Login (7 Hari Terakhir)</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
              Visualisasi tren real-time ditenagai oleh mesin grafik Apache ECharts
            </p>
          </div>

          <div class="flex items-center gap-2 text-xs">
            <span class="px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 font-mono">
              ECharts Engine Aktif
            </span>
          </div>
        </div>

        <!-- BaseChart Reusable Component -->
        <div class="w-full">
          <BaseChart :option="activityChartOption" height="280px" />
        </div>
      </div>

      <!-- Two-Column Overview: Recent Activities & Recent Logins -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
        <!-- Recent Audit Activities -->
        <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-800">
              <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <History class="w-4 h-4 text-purple-500" />
                <span>Log Aktivitas Terbaru</span>
              </h3>
              <a :href="getUrl('/audit-logs')" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                Lihat Semua →
              </a>
            </div>

            <div class="space-y-3">
              <div
                v-for="item in recent_activities"
                :key="item.id"
                class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800/80 text-xs"
              >
                <div class="flex items-center gap-3 min-w-0">
                  <img :src="item.causer_avatar" alt="" class="w-8 h-8 rounded-full flex-shrink-0" />
                  <div class="min-w-0">
                    <div class="font-semibold text-slate-800 dark:text-slate-200 truncate">
                      {{ item.description }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">
                      Oleh: {{ item.causer_name }}
                    </div>
                  </div>
                </div>

                <div class="text-[11px] text-slate-400 font-mono shrink-0 ml-3">
                  {{ item.created_at }}
                </div>
              </div>

              <div v-if="recent_activities.length === 0" class="py-8 text-center text-xs text-slate-500">
                Belum ada aktivitas tercatat.
              </div>
            </div>
          </div>

          <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
            <span>Audit Trail otomatis aktif via Spatie</span>
            <a :href="getUrl('/exports/audit-logs/excel')" class="text-indigo-600 dark:text-indigo-400 hover:underline">
              Ekspor Excel (.xlsx)
            </a>
          </div>
        </div>

        <!-- Recent Login Histories -->
        <div class="bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-800">
              <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <ShieldCheck class="w-4 h-4 text-emerald-500" />
                <span>Sesi Login Terkini</span>
              </h3>
              <a :href="getUrl('/profile')" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                Kelola Sesi →
              </a>
            </div>

            <div class="space-y-3">
              <div
                v-for="login in recent_logins"
                :key="login.id"
                class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800/80 text-xs"
              >
                <div class="min-w-0">
                  <div class="font-semibold text-slate-800 dark:text-slate-200">
                    {{ login.user_name }}
                  </div>
                  <div class="text-[11px] text-slate-400 font-mono mt-0.5 truncate">
                    IP: {{ login.ip_address }} &bull; {{ login.device }}
                  </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 ml-3">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                    {{ login.status }}
                  </span>
                  <span class="text-[11px] text-slate-400 font-mono">
                    {{ login.login_at }}
                  </span>
                </div>
              </div>

              <div v-if="recent_logins.length === 0" class="py-8 text-center text-xs text-slate-500">
                Belum ada riwayat login tercatat.
              </div>
            </div>
          </div>

          <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
            <span>Sesi terenkripsi HTTP-Only Cookies</span>
            <span class="font-mono">Sanctum Stateful</span>
          </div>
        </div>
      </div>

      <!-- Developer Workspace / New System Canvas Box -->
      <div class="bg-white dark:bg-slate-900/80 border border-dashed border-slate-300 dark:border-slate-800 rounded-3xl p-8 sm:p-10 text-center">
        <div class="max-w-xl mx-auto space-y-3">
          <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto shadow-inner">
            <Code class="w-6 h-6" />
          </div>
          <h3 class="text-lg font-bold text-slate-900 dark:text-white">
            Ruang Kerja Bersih untuk Sistem Baru Anda
          </h3>
          <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
            Halaman demo telah sepenuhnya dibersihkan. Anda siap membuat migration, model, dan halaman sistem baru di folder <code class="text-xs bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded text-indigo-600 dark:text-indigo-400 font-mono">resources/js/Pages/</code>.
            Seluruh infrastruktur (ECharts, MapLibre, Reverb, Pulse, RBAC, Activitylog, Export Excel/PDF) tetap aktif dan siap dipakai.
          </p>

          <div class="pt-3 flex flex-wrap justify-center gap-3">
            <a
              :href="getUrl('/docs/api')"
              target="_blank"
              class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors flex items-center gap-1.5"
            >
              <BookOpen class="w-3.5 h-3.5 text-amber-500" />
              <span>Scramble OpenAPI Docs ↗</span>
            </a>
            <a
              :href="getUrl('/pulse')"
              target="_blank"
              class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors flex items-center gap-1.5"
            >
              <Activity class="w-3.5 h-3.5 text-rose-500" />
              <span>Laravel Pulse Monitoring ↗</span>
            </a>
          </div>
        </div>
      </div>
    </main>
  </HybridNavbar>
</template>
