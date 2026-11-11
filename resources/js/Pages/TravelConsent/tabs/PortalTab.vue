<script setup>
import { ref, computed } from 'vue';
import {
  Globe,
  Plane,
  Anchor,
  BriefcaseMedical,
  Pill,
  ExternalLink,
  Copy,
  Check,
  ArrowRight,
  Clock,
  FileCheck,
} from 'lucide-vue-next';

const props = defineProps({
  aircraftStats: {
    type: Object,
    default: () => ({ total: 0, pending_count: 0 }),
  },
  shipStats: {
    type: Object,
    default: () => ({ total: 0, pending_count: 0 }),
  },
  alatStats: {
    type: Object,
    default: () => ({ total: 0, submitted_count: 0 }),
  },
  obatStats: {
    type: Object,
    default: () => ({ total: 0, submitted_count: 0 }),
  },
  summary: {
    type: Object,
    default: () => ({ total_records: 0, pending_verification: 0 }),
  },
});

const emit = defineEmits(['switch-tab']);

const copiedKey = ref('');

const portalUrl = computed(() => {
  if (typeof window === 'undefined') return '/travelconsent/home';
  return `${window.location.origin}/travelconsent/home`;
});

const copyText = async (key, path) => {
  const fullUrl = typeof window === 'undefined' ? path : `${window.location.origin}${path}`;
  try {
    await navigator.clipboard.writeText(fullUrl);
  } catch (e) {
    const input = document.createElement('input');
    input.value = fullUrl;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
  }
  copiedKey.value = key;
  setTimeout(() => {
    if (copiedKey.value === key) copiedKey.value = '';
  }, 2200);
};

const portalServices = computed(() => [
  {
    key: 'ship',
    tab: 'ship',
    title: 'Sanitasi Kapal (SSCC / SSCEC)',
    subtitle: 'Sertifikat Pengendalian & Pembebasan Sanitasi Kapal WHO IHR 2005',
    moda: 'Karantina Laut',
    formUrl: '/travelconsent/public_sanitasi',
    total: props.shipStats?.total ?? 0,
    pending: props.shipStats?.pending_count ?? 0,
    icon: Anchor,
    iconWrap: 'bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 group-hover:bg-teal-600 group-hover:text-white',
    badgeClass: 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/60 dark:text-teal-300 dark:border-teal-800',
    btnClass: 'bg-teal-600 hover:bg-teal-500 text-white',
    manageText: 'Data Sanitasi Kapal',
  },
  {
    key: 'aircraft',
    tab: 'aircraft',
    title: 'Data Inspeksi Pesawat',
    subtitle: 'Aircraft Sanitation Inspection Check List & Kelaikan Terbang',
    moda: 'Karantina Udara',
    formUrl: '/travelconsent/public_aircraft',
    total: props.aircraftStats?.total ?? 0,
    pending: props.aircraftStats?.pending_count ?? 0,
    icon: Plane,
    iconWrap: 'bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 group-hover:bg-orange-500 group-hover:text-white',
    badgeClass: 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-950/60 dark:text-orange-300 dark:border-orange-800',
    btnClass: 'bg-orange-500 hover:bg-orange-400 text-slate-950',
    manageText: 'Data Inspeksi Pesawat',
  },
  {
    key: 'alat',
    tab: 'alat',
    title: 'Pemakaian Alkes P3K',
    subtitle: 'First Aid Kit & Emergency Medical Kit Alat Kesehatan Penerbangan',
    moda: 'Penerbangan & Haji',
    formUrl: '/travelconsent/public_alat',
    total: props.alatStats?.total ?? 0,
    pending: props.alatStats?.submitted_count ?? 0,
    icon: BriefcaseMedical,
    iconWrap: 'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 group-hover:bg-blue-600 group-hover:text-white',
    badgeClass: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800',
    btnClass: 'bg-blue-600 hover:bg-blue-500 text-white',
    manageText: 'Data Alkes P3K',
  },
  {
    key: 'obat',
    tab: 'obat',
    title: 'Pemakaian Obat P3K',
    subtitle: 'First Aid Medications & Sediaan Farmasi Emergensi Pesawat',
    moda: 'Penerbangan & Haji',
    formUrl: '/travelconsent/public_obat',
    total: props.obatStats?.total ?? 0,
    pending: props.obatStats?.submitted_count ?? 0,
    icon: Pill,
    iconWrap: 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 group-hover:bg-rose-600 group-hover:text-white',
    badgeClass: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
    btnClass: 'bg-rose-600 hover:bg-rose-500 text-white',
    manageText: 'Data Obat P3K',
  },
]);
</script>

<template>
  <div class="space-y-6">
    <!-- Top Portal Summary & Shareable Public Link Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs space-y-5">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#00A99D] to-[#005851] text-white flex items-center justify-center shrink-0 shadow-sm">
            <Globe class="w-6 h-6" />
          </div>
          <div>
            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white">
              Portal Travel Consent &amp; Formulir Layanan Publik
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 max-w-2xl">
              Pusat akses formulir publik persetujuan perjalanan &amp; inspeksi sanitasi alat angkut. Bagikan tautan formulir kepada petugas lapangan, maskapai, atau agen kapal, lalu verifikasi hasilnya pada tab manajemen di atas.
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
          <div class="px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs flex items-center gap-2">
            <FileCheck class="w-4 h-4 text-[#00A99D]" />
            <span class="text-slate-500 dark:text-slate-400">Total Rekam:</span>
            <strong class="font-mono text-slate-900 dark:text-white">{{ summary.total_records }}</strong>
          </div>
          <div class="px-3.5 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs flex items-center gap-2">
            <Clock class="w-4 h-4 text-amber-600 dark:text-amber-400" />
            <span class="text-amber-700 dark:text-amber-300">Menunggu Verifikasi:</span>
            <strong class="font-mono text-amber-900 dark:text-amber-200">{{ summary.pending_verification }}</strong>
          </div>
        </div>
      </div>

      <!-- Public Portal URL Bar -->
      <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="text-xs font-semibold text-slate-600 dark:text-slate-300 shrink-0">
          Tautan Halaman Utama Portal Publik:
        </div>
        <input
          type="text"
          readonly
          :value="portalUrl"
          @focus="$event.target.select()"
          class="flex-1 px-3 py-2 text-xs font-mono bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#00A99D]"
        />
        <div class="flex items-center gap-2 shrink-0">
          <button
            type="button"
            @click="copyText('portal', '/travelconsent/home')"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer text-white"
            :class="copiedKey === 'portal' ? 'bg-emerald-600' : 'bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600'"
          >
            <component :is="copiedKey === 'portal' ? Check : Copy" class="w-3.5 h-3.5" />
            <span>{{ copiedKey === 'portal' ? 'Tersalin!' : 'Salin Link' }}</span>
          </button>
          <a
            href="/travelconsent/home"
            target="_blank"
            class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-[#005851] hover:bg-[#004640] transition-colors inline-flex items-center gap-1.5"
          >
            <ExternalLink class="w-3.5 h-3.5" />
            <span>Buka Portal Publik</span>
          </a>
        </div>
      </div>
    </div>

    <!-- 4 Portal Layanan Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
      <div
        v-for="svc in portalServices"
        :key="svc.key"
        class="group bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700 transition-all flex flex-col justify-between gap-5"
      >
        <div class="space-y-4">
          <div class="flex items-start justify-between gap-3">
            <div class="w-13 h-13 rounded-2xl flex items-center justify-center transition-colors shrink-0" :class="svc.iconWrap">
              <component :is="svc.icon" class="w-6 h-6" />
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10.5px] font-semibold border" :class="svc.badgeClass">
              {{ svc.moda }}
            </span>
          </div>

          <div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white leading-snug">
              {{ svc.title }}
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
              {{ svc.subtitle }}
            </p>
          </div>

          <!-- Stats Row -->
          <div class="grid grid-cols-2 gap-2 pt-1">
            <div class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/70 border border-slate-100 dark:border-slate-800">
              <div class="text-[10px] font-mono text-slate-400 uppercase">Total Data</div>
              <div class="text-lg font-extrabold text-slate-900 dark:text-white">{{ svc.total }}</div>
            </div>
            <div class="px-3 py-2 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900/50">
              <div class="text-[10px] font-mono text-amber-600 dark:text-amber-400 uppercase">Pending</div>
              <div class="text-lg font-extrabold text-amber-600 dark:text-amber-400">{{ svc.pending }}</div>
            </div>
          </div>
        </div>

        <div class="space-y-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <div class="flex items-center gap-2">
            <a
              :href="svc.formUrl"
              target="_blank"
              class="flex-1 px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-2xs"
              :class="svc.btnClass"
            >
              <ExternalLink class="w-3.5 h-3.5" />
              <span>Buka Form Publik</span>
            </a>
            <button
              type="button"
              @click="copyText(svc.key, svc.formUrl)"
              class="px-3 py-2 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors cursor-pointer flex items-center gap-1"
              :title="'Salin Link Form ' + svc.title"
            >
              <component :is="copiedKey === svc.key ? Check : Copy" class="w-3.5 h-3.5" :class="copiedKey === svc.key ? 'text-emerald-600' : ''" />
              <span>{{ copiedKey === svc.key ? 'Disalin' : 'Salin' }}</span>
            </button>
          </div>

          <button
            type="button"
            @click="emit('switch-tab', svc.tab)"
            class="w-full px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200/80 dark:hover:bg-slate-700 transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
          >
            <span>Kelola {{ svc.manageText }}</span>
            <ArrowRight class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

