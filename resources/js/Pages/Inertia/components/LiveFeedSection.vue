<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
  Activity,
  ChevronRight,
  ClipboardCheck,
  Layers,
  LogIn,
  MapPin,
  Search,
} from 'lucide-vue-next';

const props = defineProps({
  recentInspections: { type: Array, default: () => [] },
  recentActivities: { type: Array, default: () => [] },
  recentLogins: { type: Array, default: () => [] },
  selectedModuleKey: { type: String, default: 'all' },
  moduleIconMap: { type: Object, default: () => ({}) },
});

const getUrl = (path) => {
  if (typeof window !== 'undefined' && window.location.pathname.includes('/hybridlaravel/public')) {
    return `/hybridlaravel/public${path}`;
  }
  return path;
};

const activeFeedTab = ref('inspections'); // 'inspections' | 'activities' | 'logins'
const searchFeed = ref('');
const statusFilterFeed = ref('all');

const filteredInspections = computed(() => {
  let list = props.recentInspections || [];
  if (props.selectedModuleKey !== 'all') {
    list = list.filter((r) => r.module === props.selectedModuleKey);
  }
  if (statusFilterFeed.value !== 'all') {
    list = list.filter((r) => r.status === statusFilterFeed.value);
  }
  const q = searchFeed.value.trim().toLowerCase();
  if (!q) return list;
  return list.filter((r) =>
    r.registration_no.toLowerCase().includes(q) ||
    r.subject.toLowerCase().includes(q) ||
    r.officer.toLowerCase().includes(q) ||
    r.office.toLowerCase().includes(q)
  );
});

const moduleBadgeClass = (mod) => ({
  'bg-[#E6F7F5] dark:bg-[#00A99D]/15 text-[#005851] dark:text-[#5eead4] border border-[#99DFD6] dark:border-[#00A99D]/40': mod === 'aircraft',
  'bg-[#E0EFEF] dark:bg-cyan-500/15 text-[#005851] dark:text-cyan-300 border border-[#99DFD6] dark:border-cyan-500/40': mod === 'ship',
  'bg-[#F7FCE8] dark:bg-lime-500/15 text-[#557A18] dark:text-lime-300 border border-[#D6EC8E] dark:border-lime-500/40': mod === 'alat',
  'bg-[#E6F7F5] dark:bg-emerald-500/15 text-[#005851] dark:text-emerald-300 border border-[#99DFD6] dark:border-emerald-500/40': mod === 'obat',
});

const statusBadgeClass = (status) => ({
  'bg-[#E6F7F5] dark:bg-[#00A99D]/15 text-[#005851] dark:text-[#5eead4] border border-[#99DFD6] dark:border-[#00A99D]/40': status === 'approved',
  'bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/40': status === 'submitted',
  'bg-[#F7FCE8] dark:bg-lime-500/15 text-[#557A18] dark:text-lime-300 border border-[#D6EC8E] dark:border-lime-500/40': status === 'verified',
  'bg-rose-50 dark:bg-rose-500/15 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-500/40': status === 'rejected',
});
</script>

<template>
  <div class="clinical-surface overflow-hidden font-sans">
    <!-- Header & Tabs -->
    <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2.5 text-xs">
      <div class="flex flex-wrap items-center gap-1.5">
        <button
          type="button"
          @click="activeFeedTab = 'inspections'"
          class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer flex items-center gap-1.5"
          :class="activeFeedTab === 'inspections' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-[#005851] dark:hover:text-[#5eead4] bg-slate-100 dark:bg-[#16202a]'"
        >
          <ClipboardCheck class="w-3.5 h-3.5" />
          <span>Dokumen Pemeriksaan Terkini</span>
          <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-[#99CC33] text-[#003B36] font-black font-mono">
            {{ recentInspections.length }}
          </span>
        </button>

        <button
          type="button"
          @click="activeFeedTab = 'activities'"
          class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer flex items-center gap-1.5"
          :class="activeFeedTab === 'activities' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-[#005851] dark:hover:text-[#5eead4] bg-slate-100 dark:bg-[#16202a]'"
        >
          <Activity class="w-3.5 h-3.5" />
          <span>Audit Log ({{ recentActivities.length }})</span>
        </button>

        <button
          type="button"
          @click="activeFeedTab = 'logins'"
          class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer flex items-center gap-1.5"
          :class="activeFeedTab === 'logins' ? 'bg-[#005851] text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-[#005851] dark:hover:text-[#5eead4] bg-slate-100 dark:bg-[#16202a]'"
        >
          <LogIn class="w-3.5 h-3.5" />
          <span>Sesi Login ({{ recentLogins.length }})</span>
        </button>
      </div>

      <div v-if="activeFeedTab === 'inspections'" class="flex items-center gap-2">
        <select
          v-model="statusFilterFeed"
          class="px-2 py-1 rounded-lg bg-slate-50 dark:bg-[#16202a] border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 font-medium focus:outline-none focus:border-[#00A99D]"
        >
          <option value="all">Semua Status</option>
          <option value="submitted">Diajukan</option>
          <option value="approved">Disetujui</option>
          <option value="verified">Diverifikasi</option>
        </select>

        <div class="relative w-44 sm:w-52">
          <Search class="w-3 h-3 text-slate-400 absolute left-2 top-1/2 -translate-y-1/2" />
          <input
            v-model="searchFeed"
            type="text"
            placeholder="Cari no reg / armada..."
            class="w-full pl-6 pr-2 py-1 rounded-lg bg-slate-50 dark:bg-[#16202a] border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:bg-white dark:focus:bg-[#192430] focus:border-[#00A99D]"
          />
        </div>
      </div>
    </div>

    <!-- Inspections View -->
    <div v-if="activeFeedTab === 'inspections'">
      <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
        <div v-if="filteredInspections.length === 0" class="py-8 text-center text-slate-500 dark:text-slate-400 text-xs">
          Tidak ada dokumen pemeriksaan yang cocok dengan filter yang dipilih.
        </div>
        <div
          v-for="item in filteredInspections"
          :key="`mob-insp-${item.registration_no}`"
          class="p-3.5 space-y-2 text-xs"
        >
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0 space-y-1">
              <div class="flex items-center gap-1.5 flex-wrap">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold" :class="moduleBadgeClass(item.module)">
                  <component :is="moduleIconMap[item.module] || Layers" class="w-3 h-3" />
                  {{ item.module_label }}
                </span>
                <span class="font-mono font-bold text-[#005851] dark:text-[#5eead4]">{{ item.registration_no }}</span>
              </div>
              <div class="font-semibold text-slate-800 dark:text-slate-100 truncate">{{ item.subject }}</div>
            </div>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase font-mono shrink-0" :class="statusBadgeClass(item.status)">
              {{ item.status_label }}
            </span>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500 dark:text-slate-400">
            <span class="inline-flex items-center gap-1">
              <MapPin class="w-3 h-3 text-[#00A99D]" />
              {{ item.office }} &bull; {{ item.officer }}
            </span>
            <span class="font-mono">{{ item.date }}</span>
          </div>
          <div class="flex items-center justify-between pt-1">
            <span class="text-[11px] font-bold" :class="item.result_ok ? 'text-[#00A99D]' : 'text-rose-600 dark:text-rose-400'">
              {{ item.result }}
            </span>
            <Link
              :href="getUrl(item.url)"
              class="inline-flex items-center gap-0.5 px-2.5 py-1 rounded text-[11px] font-semibold bg-[#E6F7F5] dark:bg-[#00A99D]/15 hover:bg-[#00A99D] text-[#005851] dark:text-[#5eead4] hover:text-white transition-all border border-[#99DFD6]/60 dark:border-[#00A99D]/40"
            >
              <span>Buka</span>
              <ChevronRight class="w-3 h-3" />
            </Link>
          </div>
        </div>
      </div>

      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50 dark:bg-[#16202a] text-slate-900 dark:text-slate-200 font-bold border-b border-slate-100 dark:border-slate-800 text-[11px]">
              <th class="py-2.5 px-3">Layanan</th>
              <th class="py-2.5 px-3">No. Registrasi</th>
              <th class="py-2.5 px-3">Armada / Subjek</th>
              <th class="py-2.5 px-3">Petugas BKK</th>
              <th class="py-2.5 px-3">Lokasi / Wilker</th>
              <th class="py-2.5 px-3">Waktu</th>
              <th class="py-2.5 px-3">Status &amp; Hasil</th>
              <th class="py-2.5 px-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-for="item in filteredInspections" :key="item.registration_no" class="hover:bg-[#E6F7F5]/30 dark:hover:bg-[#00A99D]/10 transition-colors">
              <td class="py-2 px-3 whitespace-nowrap">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold" :class="moduleBadgeClass(item.module)">
                  <component :is="moduleIconMap[item.module] || Layers" class="w-3 h-3" />
                  {{ item.module_label }}
                </span>
              </td>
              <td class="py-2 px-3 whitespace-nowrap font-mono font-bold text-[#005851] dark:text-[#5eead4]">{{ item.registration_no }}</td>
              <td class="py-2 px-3 text-slate-800 dark:text-slate-100 font-semibold max-w-xs truncate" :title="item.subject">{{ item.subject }}</td>
              <td class="py-2 px-3 whitespace-nowrap text-slate-600 dark:text-slate-300">{{ item.officer }}</td>
              <td class="py-2 px-3 whitespace-nowrap text-slate-600 dark:text-slate-300">
                <span class="inline-flex items-center gap-1">
                  <MapPin class="w-3 h-3 text-[#00A99D]" />
                  {{ item.office }}
                </span>
              </td>
              <td class="py-2 px-3 whitespace-nowrap text-slate-500 dark:text-slate-400 text-[11px] font-mono">{{ item.date }}</td>
              <td class="py-2 px-3 whitespace-nowrap">
                <div class="flex items-center gap-1.5">
                  <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider font-mono" :class="statusBadgeClass(item.status)">
                    {{ item.status_label }}
                  </span>
                  <span class="text-[11px] font-semibold" :class="item.result_ok ? 'text-[#00A99D] font-bold' : 'text-rose-600 dark:text-rose-400 font-bold'">
                    {{ item.result }}
                  </span>
                </div>
              </td>
              <td class="py-2 px-3 whitespace-nowrap text-right">
                <Link
                  :href="getUrl(item.url)"
                  class="inline-flex items-center gap-0.5 px-2.5 py-0.5 rounded text-[11px] font-semibold bg-[#E6F7F5] dark:bg-[#00A99D]/15 hover:bg-[#00A99D] text-[#005851] dark:text-[#5eead4] hover:text-white transition-all border border-[#99DFD6]/60 dark:border-[#00A99D]/40"
                >
                  <span>Buka</span>
                  <ChevronRight class="w-3 h-3" />
                </Link>
              </td>
            </tr>
            <tr v-if="filteredInspections.length === 0">
              <td colspan="8" class="py-6 text-center text-slate-500 dark:text-slate-400 text-xs">
                Tidak ada dokumen pemeriksaan yang cocok dengan filter yang dipilih.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Audit Log View -->
    <div v-else-if="activeFeedTab === 'activities'" class="p-3.5 space-y-2">
      <div v-for="act in recentActivities" :key="act.id" class="flex items-start justify-between p-2 rounded-lg bg-slate-50 dark:bg-[#16202a] border border-slate-100 dark:border-slate-800 text-xs">
        <div class="flex items-start gap-2">
          <div class="w-6 h-6 rounded-full bg-[#E6F7F5] dark:bg-[#00A99D]/15 text-[#005851] dark:text-[#5eead4] flex items-center justify-center font-bold text-[10px] shrink-0 font-mono">
            {{ act.causer_name.charAt(0) }}
          </div>
          <div>
            <div class="font-bold text-slate-900 dark:text-slate-100">
              {{ act.causer_name }}
              <span class="font-normal text-slate-500 dark:text-slate-400">melakukan</span>
              <span class="font-semibold text-[#005851] dark:text-[#5eead4]">{{ act.event || 'aktivitas' }}</span>
            </div>
            <div class="text-slate-600 dark:text-slate-300 text-[11px]">{{ act.description }}</div>
          </div>
        </div>
        <span class="text-[10px] text-slate-400 dark:text-slate-500 shrink-0 ml-2 font-mono">{{ act.created_at }}</span>
      </div>
    </div>

    <!-- Login History View -->
    <div v-else-if="activeFeedTab === 'logins'" class="p-3.5 space-y-2">
      <div v-for="lh in recentLogins" :key="lh.id" class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-[#16202a] border border-slate-100 dark:border-slate-800 text-xs">
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 rounded-full bg-[#E6F7F5] dark:bg-[#00A99D]/15 text-[#005851] dark:text-[#5eead4] flex items-center justify-center shrink-0">
            <LogIn class="w-3 h-3" />
          </div>
          <div>
            <div class="font-bold text-slate-900 dark:text-slate-100">{{ lh.user_name }} ({{ lh.email }})</div>
            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">IP: {{ lh.ip_address }} &bull; Perangkat: {{ lh.device }}</div>
          </div>
        </div>
        <div class="text-right">
          <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-[#E6F7F5] dark:bg-[#00A99D]/15 text-[#005851] dark:text-[#5eead4] border border-[#99DFD6] dark:border-[#00A99D]/40 font-mono">
            {{ lh.status }}
          </span>
          <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 font-mono">{{ lh.login_at }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
