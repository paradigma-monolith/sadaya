<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import HybridNavbar from '../../Components/HybridNavbar.vue';
import { 
  History, 
  ShieldAlert, 
  CheckCircle2, 
  Clock, 
  User as UserIcon, 
  Globe, 
  Smartphone, 
  Tablet, 
  Laptop,
  ShieldCheck,
  Activity
} from 'lucide-vue-next';

const props = defineProps({
  activities: {
    type: Object,
    required: true,
  },
  login_histories: {
    type: Object,
    default: () => ({ data: [] }),
  },
  current_ip: {
    type: String,
    default: '127.0.0.1',
  },
});

const activeTab = ref('activities'); // 'activities' | 'logins'

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  try {
    const d = new Date(dateStr);
    return d.toLocaleString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  } catch (e) {
    return dateStr;
  }
};

const getUrl = (path) => {
  if (typeof window !== 'undefined' && window.location.pathname.includes('/hybridlaravel/public')) {
    return `/hybridlaravel/public${path}`;
  }
  return path;
};
</script>

<template>
  <Head title="Audit Trail & Riwayat Keamanan - BKK Kelas I Bandung" />

  <HybridNavbar mode="inertia" currentRoute="audit">
    <main class="clinical-main">
      <!-- Header -->
      <div class="clinical-header flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-9 h-9 rounded-lg bg-[#002B27] border border-[#00A99D]/40 text-[#99CC33] flex items-center justify-center shrink-0">
            <History class="w-4 h-4" />
          </div>
          <div class="min-w-0">
            <h1 class="text-sm sm:text-[15px] font-bold text-white tracking-tight">
              Pusat Audit &amp; Keamanan Sistem
            </h1>
            <p class="text-[11px] text-slate-200/85 truncate mt-0.5">
              Rekam jejak mutasi database, aktivitas operasional, serta riwayat login seluruh akun pengguna.
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-1.5 shrink-0">
          <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#002B27] border border-white/15 text-[11px] font-mono tabular-nums">
            <span class="w-1.5 h-1.5 rounded-full bg-[#99CC33]"></span>
            <span class="text-slate-300">IP:</span>
            <span class="font-semibold text-[#99DFD6]">{{ current_ip }}</span>
          </div>

          <a :href="getUrl('/api/v1/audit-logs')" target="_blank" class="px-2.5 py-1 rounded-lg font-mono text-[11px] text-white bg-white/10 hover:bg-white/20 border border-white/15 flex items-center gap-1 transition-colors">
            <span>JSON Feed</span>
            <span class="text-[#99CC33]">↗</span>
          </a>
        </div>
      </div>

      <!-- Tab Switcher Navigation -->
      <div class="clinical-tabs sm:inline-flex">
        <button
          type="button"
          @click="activeTab = 'activities'"
          class="clinical-tab"
          :class="activeTab === 'activities' ? 'bg-[#005851] text-white' : 'text-slate-600 hover:text-[#005851] hover:bg-slate-50'"
        >
          <Activity class="w-3.5 h-3.5 shrink-0" />
          <span>Log Aktivitas Data</span>
          <span class="text-[10px] px-1.5 py-0.2 rounded font-mono clinical-num" :class="activeTab === 'activities' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
            {{ activities.total || activities.data.length }}
          </span>
        </button>

        <button
          type="button"
          @click="activeTab = 'logins'"
          class="clinical-tab"
          :class="activeTab === 'logins' ? 'bg-[#005851] text-white' : 'text-slate-600 hover:text-[#005851] hover:bg-slate-50'"
        >
          <Globe class="w-3.5 h-3.5 shrink-0" />
          <span>Riwayat Login IP</span>
          <span class="text-[10px] px-1.5 py-0.2 rounded font-mono clinical-num" :class="activeTab === 'logins' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
            {{ login_histories.total || login_histories.data.length }}
          </span>
        </button>
      </div>

      <!-- Tab 1: Activity Log Table (Spatie) -->
      <div v-if="activeTab === 'activities'" class="clinical-surface p-3 sm:p-4">
        <!-- Desktop/Tablet View -->
        <div class="hidden md:block overflow-x-auto rounded-lg border border-slate-200/90">
          <table class="w-full text-left text-xs text-slate-700">
            <thead class="text-[11px] uppercase tracking-wider bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
              <tr>
                <th class="px-3.5 py-2.5">Waktu</th>
                <th class="px-3.5 py-2.5">Event</th>
                <th class="px-3.5 py-2.5">Pelaku (Causer)</th>
                <th class="px-3.5 py-2.5">Objek / Modul</th>
                <th class="px-3.5 py-2.5">Deskripsi</th>
                <th class="px-3.5 py-2.5">Perubahan (Properties)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-mono text-[11px] clinical-num">
              <tr v-for="item in activities.data" :key="item.id" class="hover:bg-[#F4FBF9]/70 transition-colors">
                <td class="px-3.5 py-2 text-slate-500 whitespace-nowrap">
                  {{ formatDate(item.created_at) }}
                </td>
                <td class="px-3.5 py-2">
                  <span class="px-2 py-0.5 rounded text-[10px] font-semibold border"
                    :class="{
                      'bg-emerald-50 text-emerald-700 border-emerald-200': item.event === 'created',
                      'bg-amber-50 text-amber-700 border-amber-200': item.event === 'updated',
                      'bg-rose-50 text-rose-700 border-rose-200': item.event === 'deleted',
                      'bg-slate-100 text-slate-700 border-slate-200': !item.event
                    }">
                    {{ item.event || 'action' }}
                  </span>
                </td>
                <td class="px-3.5 py-2 text-slate-800 font-sans">
                  <div class="flex items-center gap-1.5">
                    <UserIcon class="w-3.5 h-3.5 text-[#00A99D]" />
                    <span>{{ item.causer?.name || 'Sistem / Anonim' }}</span>
                  </div>
                </td>
                <td class="px-3.5 py-2 text-[#005851] font-sans font-medium">
                  {{ item.subject_type ? item.subject_type.split('\\').pop() : 'System' }} #{{ item.subject_id || '-' }}
                </td>
                <td class="px-3.5 py-2 text-slate-700 font-sans">
                  {{ item.description }}
                </td>
                <td class="px-3.5 py-2 text-slate-500 max-w-xs truncate">
                  <span v-if="item.properties && Object.keys(item.properties).length" class="text-slate-600 font-mono text-[10px]">
                    {{ JSON.stringify(item.properties) }}
                  </span>
                  <span v-else class="text-slate-400">-</span>
                </td>
              </tr>

              <tr v-if="activities.data.length === 0">
                <td colspan="6" class="px-4 py-8 text-center text-slate-500 font-sans text-xs">
                  Belum ada log aktivitas tercatat.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile View: Activity Card Feed -->
        <div class="block md:hidden space-y-2.5">
          <div 
            v-for="item in activities.data" 
            :key="'mob-act-' + item.id"
            class="p-3 rounded-lg bg-slate-50 border border-slate-200/80 space-y-2"
          >
            <div class="flex items-center justify-between gap-2">
              <span class="text-[11px] text-slate-500 font-mono clinical-num">
                {{ formatDate(item.created_at) }}
              </span>
              <span class="px-2 py-0.5 rounded text-[10px] font-semibold border"
                :class="{
                  'bg-emerald-50 text-emerald-700 border-emerald-200': item.event === 'created',
                  'bg-amber-50 text-amber-700 border-amber-200': item.event === 'updated',
                  'bg-rose-50 text-rose-700 border-rose-200': item.event === 'deleted',
                  'bg-slate-100 text-slate-700 border-slate-200': !item.event
                }">
                {{ item.event || 'action' }}
              </span>
            </div>

            <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-900">
              <UserIcon class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
              <span>{{ item.causer?.name || 'Sistem / Anonim' }}</span>
              <span class="text-slate-400">&bull;</span>
              <span class="text-[#005851] font-mono text-[11px]">
                {{ item.subject_type ? item.subject_type.split('\\').pop() : 'System' }} #{{ item.subject_id || '-' }}
              </span>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
              {{ item.description }}
            </p>

            <div v-if="item.properties && Object.keys(item.properties).length" class="p-2 rounded bg-white border border-slate-200 text-[10px] font-mono text-slate-500 break-all">
              {{ JSON.stringify(item.properties) }}
            </div>
          </div>

          <div v-if="activities.data.length === 0" class="py-6 text-center text-xs text-slate-500">
            Belum ada log aktivitas tercatat.
          </div>
        </div>
      </div>

      <!-- Tab 2: Login Histories (All Accounts) -->
      <div v-else class="clinical-surface p-3 sm:p-4">
        <!-- Desktop/Tablet View -->
        <div class="hidden md:block overflow-x-auto rounded-lg border border-slate-200/90">
          <table class="w-full text-left text-xs font-mono clinical-num">
            <thead class="uppercase bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 text-[11px]">
              <tr>
                <th class="px-3.5 py-2.5 whitespace-nowrap">Waktu</th>
                <th class="px-3.5 py-2.5 whitespace-nowrap">Status</th>
                <th class="px-3.5 py-2.5 whitespace-nowrap font-sans">Akun / Pengguna</th>
                <th class="px-3.5 py-2.5 whitespace-nowrap">Alamat IP</th>
                <th class="px-3.5 py-2.5 whitespace-nowrap">Perangkat &amp; Platform</th>
                <th class="px-3.5 py-2.5 whitespace-nowrap">Peramban</th>
                <th class="px-3.5 py-2.5 whitespace-nowrap">Lokasi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              <tr v-for="item in login_histories.data" :key="item.id" class="hover:bg-[#F4FBF9]/70 transition-colors">
                <!-- Waktu -->
                <td class="px-3.5 py-2 whitespace-nowrap text-slate-600">
                  <div class="flex items-center gap-1.5">
                    <Clock class="w-3.5 h-3.5 text-slate-400" />
                    <span>{{ formatDate(item.login_at) }}</span>
                  </div>
                </td>

                <!-- Status -->
                <td class="px-3.5 py-2 whitespace-nowrap">
                  <span v-if="item.status === 'success'" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <CheckCircle2 class="w-3 h-3 text-emerald-600" />
                    <span>Berhasil</span>
                  </span>
                  <span v-else class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                    <ShieldAlert class="w-3 h-3 text-rose-600" />
                    <span>Gagal</span>
                  </span>
                </td>

                <!-- Akun / Pengguna -->
                <td class="px-3.5 py-2 whitespace-nowrap font-sans">
                  <div v-if="item.user" class="flex items-center gap-2">
                    <img
                      :src="item.user.avatar_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(item.user.name)"
                      class="w-6 h-6 rounded-full object-cover border border-slate-200 shrink-0"
                      :alt="item.user.name"
                    />
                    <div class="min-w-0">
                      <div class="font-semibold text-slate-900 text-xs leading-tight truncate">{{ item.user.name }}</div>
                      <div class="text-[10px] text-slate-500 font-mono leading-tight truncate">{{ item.user.email }}</div>
                    </div>
                  </div>
                  <div v-else class="flex items-center gap-1.5 text-rose-600">
                    <ShieldAlert class="w-3.5 h-3.5 shrink-0" />
                    <span class="text-xs font-mono font-semibold truncate">{{ item.email || 'Anonim' }}</span>
                  </div>
                </td>

                <!-- Alamat IP -->
                <td class="px-3.5 py-2 whitespace-nowrap">
                  <div class="flex items-center gap-1.5">
                    <span class="font-semibold text-slate-900">{{ item.ip_address }}</span>
                    <span v-if="item.ip_address === current_ip" class="px-1.5 py-0.2 text-[9px] rounded bg-[#E6F7F5] text-[#005851] border border-[#99DFD6] font-semibold">
                      Sesi Ini
                    </span>
                  </div>
                </td>

                <!-- Perangkat & Platform -->
                <td class="px-3.5 py-2 whitespace-nowrap">
                  <div class="flex items-center gap-1.5 text-slate-700">
                    <Smartphone v-if="item.device === 'Mobile'" class="w-3.5 h-3.5 text-[#00A99D]" />
                    <Tablet v-else-if="item.device === 'Tablet'" class="w-3.5 h-3.5 text-[#00A99D]" />
                    <Laptop v-else class="w-3.5 h-3.5 text-[#00A99D]" />
                    <span>{{ item.device || 'Desktop' }} &bull; {{ item.platform || 'OS' }}</span>
                  </div>
                </td>

                <!-- Peramban -->
                <td class="px-3.5 py-2 whitespace-nowrap text-slate-600">
                  {{ item.browser || 'Web Browser' }}
                </td>

                <!-- Lokasi -->
                <td class="px-3.5 py-2 whitespace-nowrap text-slate-600">
                  {{ item.location || 'Jaringan Lokal' }}
                </td>
              </tr>

              <tr v-if="login_histories.data.length === 0">
                <td colspan="7" class="px-4 py-8 text-center text-slate-500 font-sans text-xs">
                  Belum ada log riwayat login tercatat.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile View: Login History Card Feed -->
        <div class="block md:hidden space-y-2.5">
          <div 
            v-for="item in login_histories.data" 
            :key="'mob-log-' + item.id"
            class="p-3 rounded-lg bg-slate-50 border border-slate-200/80 space-y-2"
          >
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-1.5 text-[11px] text-slate-600 font-mono">
                <Clock class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                <span>{{ formatDate(item.login_at) }}</span>
              </div>
              <span v-if="item.status === 'success'" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <CheckCircle2 class="w-3 h-3 text-emerald-600" />
                <span>Berhasil</span>
              </span>
              <span v-else class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                <ShieldAlert class="w-3 h-3 text-rose-600" />
                <span>Gagal</span>
              </span>
            </div>

            <div class="p-2 rounded-lg bg-white border border-slate-200 flex items-center gap-2">
              <template v-if="item.user">
                <img
                  :src="item.user.avatar_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(item.user.name)"
                  class="w-6 h-6 rounded-full object-cover border border-slate-200 shrink-0"
                  :alt="item.user.name"
                />
                <div class="min-w-0 flex-1">
                  <div class="font-semibold text-slate-900 text-xs truncate leading-tight">{{ item.user.name }}</div>
                  <div class="text-[10px] text-slate-500 font-mono truncate leading-tight">{{ item.user.email }}</div>
                </div>
              </template>
              <template v-else>
                <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                  <ShieldAlert class="w-3.5 h-3.5" />
                </div>
                <div class="min-w-0 flex-1">
                  <div class="font-semibold text-rose-600 text-xs truncate leading-tight">Upaya Login Anonim</div>
                  <div class="text-[10px] text-slate-500 font-mono truncate leading-tight">{{ item.email || 'Email tidak dikenal' }}</div>
                </div>
              </template>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs font-mono">
              <div class="p-2 rounded bg-white border border-slate-200/80">
                <div class="text-[9px] uppercase tracking-wider text-slate-400 font-semibold">Alamat IP</div>
                <div class="flex items-center gap-1 flex-wrap mt-0.5">
                  <span class="font-semibold text-slate-900 text-[11px]">{{ item.ip_address }}</span>
                  <span v-if="item.ip_address === current_ip" class="px-1 py-0.2 text-[8px] rounded bg-[#E6F7F5] text-[#005851] font-semibold">
                    Sesi Ini
                  </span>
                </div>
              </div>

              <div class="p-2 rounded bg-white border border-slate-200/80">
                <div class="text-[9px] uppercase tracking-wider text-slate-400 font-semibold">Lokasi</div>
                <div class="text-slate-700 text-[11px] truncate mt-0.5">{{ item.location || 'Jaringan Lokal' }}</div>
              </div>
            </div>

            <div class="flex items-center justify-between gap-2 pt-1.5 border-t border-slate-200/80 text-xs">
              <div class="flex items-center gap-1.5 text-slate-700 font-mono text-[11px]">
                <Smartphone v-if="item.device === 'Mobile'" class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
                <Tablet v-else-if="item.device === 'Tablet'" class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
                <Laptop v-else class="w-3.5 h-3.5 text-[#00A99D] shrink-0" />
                <span>{{ item.device || 'Desktop' }} &bull; {{ item.platform || 'OS' }}</span>
              </div>
              <div class="text-[10px] font-mono text-slate-500 px-1.5 py-0.5 rounded bg-slate-100 truncate max-w-[130px]">
                {{ item.browser || 'Web' }}
              </div>
            </div>
          </div>

          <div v-if="login_histories.data.length === 0" class="py-6 text-center text-xs text-slate-500">
            Belum ada log riwayat login tercatat.
          </div>
        </div>
      </div>
    </main>
  </HybridNavbar>
</template>
