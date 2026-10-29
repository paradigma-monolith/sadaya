<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAppStore } from '../stores/appStore';
import BaseMap from '../../Components/BaseMap.vue';
import { 
  Zap, 
  Shield, 
  Radio, 
  Activity, 
  Database, 
  Users, 
  History, 
  RotateCw,
  Layers,
  ArrowRight,
  Plane,
  MapPin,
  Building2,
  Globe,
  Box,
  Navigation,
} from 'lucide-vue-next';

const store = useAppStore();

const getUrl = (path) => {
  if (typeof window !== 'undefined' && window.location.pathname.includes('/hybridlaravel/public')) {
    return `/hybridlaravel/public${path}`;
  }
  return path;
};

const fallbackOffices = [
  { no: 1, code: 'BKK-BDG-01', name: 'Balai Kekarantinaan Kesehatan Kelas I Bandung (Induk)', short_name: 'Induk BKK Bandung', type: 'Kantor Induk', moda: 'Pusat Komando', address: 'Jl. Cikapayang No.5, Tamansari, Kec. Bandung Wetan, Kota Bandung, Jawa Barat 40116', lat: -6.8989, lng: 107.6108, is_hq: true, daily_ops: 42 },
  { no: 2, code: 'POS-BDO-02', name: 'POS Bandara Husen Sastranegara', short_name: 'POS Bandara Husen', type: 'Pos Bandara', moda: 'Udara', address: 'Jl. Pajajaran Dalam, Husen Sastranegara, Kec. Cicendo, Kota Bandung, Jawa Barat 40174', lat: -6.9006, lng: 107.5763, is_hq: false, daily_ops: 31 },
  { no: 3, code: 'WLK-CBN-03', name: 'Wilayah Kerja Cirebon', short_name: 'Wilker Cirebon', type: 'Wilayah Kerja', moda: 'Laut & Pesisir', address: 'Jl. Terusan Jl. Sekar Kemuning II No. 7, Karyamulya, Kec. Kesambi, Kota Cirebon, Jawa Barat 45131', lat: -6.7320, lng: 108.5523, is_hq: false, daily_ops: 28 },
  { no: 4, code: 'WLK-IDM-04', name: 'Wilayah Kerja Indramayu', short_name: 'Wilker Indramayu', type: 'Wilayah Kerja', moda: 'Laut & Pesisir', address: 'Jl. Jend. Sudirman, Lemahmekar, Kec. Indramayu, Kabupaten Indramayu, Jawa Barat 45213', lat: -6.3276, lng: 108.3249, is_hq: false, daily_ops: 19 },
  { no: 5, code: 'WLK-KJT-05', name: 'Wilayah Kerja Kertajati', short_name: 'Wilker Kertajati', type: 'Wilayah Kerja', moda: 'Udara Internasional', address: 'Kertajati, Majalengka Regency, West Java 45457', lat: -6.6561, lng: 108.1578, is_hq: false, daily_ops: 36 },
  { no: 6, code: 'WLK-PTB-06', name: 'Wilayah Kerja Patimban', short_name: 'Wilker Patimban', type: 'Wilayah Kerja', moda: 'Pelabuhan Laut', address: 'Pelabuhan Patimban, Pusakanagara, Subang, Jawa Barat 41255', lat: -6.2415, lng: 107.9048, is_hq: false, daily_ops: 34 },
  { no: 7, code: 'WLK-PLR-07', name: 'Wilayah Kerja Pelabuharatu', short_name: 'Wilker Pelabuharatu', type: 'Wilayah Kerja', moda: 'Pelabuhan Samudera', address: 'Jl. Sirgalih, Pelabuhanratu, Kec. Pelabuhanratu, Kabupaten Sukabumi, Jawa Barat 43364', lat: -6.9875, lng: 106.5514, is_hq: false, daily_ops: 21 },
];

const nodeMetaMap = {
  1: { flag: 'BDG', node_label: 'Induk Bandung', ping: '4ms' },
  2: { flag: 'BDO', node_label: 'POS Husen', ping: '5ms' },
  3: { flag: 'CBN', node_label: 'Wilker Cirebon', ping: '8ms' },
  4: { flag: 'IDM', node_label: 'Wilker Indramayu', ping: '9ms' },
  5: { flag: 'KJT', node_label: 'Wilker Kertajati', ping: '6ms' },
  6: { flag: 'PTB', node_label: 'Wilker Patimban', ping: '7ms' },
  7: { flag: 'PLR', node_label: 'Wilker Pelabuharatu', ping: '11ms' },
};

const offices = computed(() => {
  const source = store.bkkOffices && store.bkkOffices.length ? store.bkkOffices : fallbackOffices;
  return source.map((o, idx) => {
    const no = o.no || idx + 1;
    const meta = nodeMetaMap[no] || { flag: 'BKK', node_label: o.short_name || o.name, ping: '5ms' };
    return {
      ...o,
      id: o.code || `bkk-${no}`,
      no,
      flag: meta.flag,
      shortCode: meta.flag,
      short_code: meta.flag,
      node_label: meta.node_label,
      title: o.name,
      role: `${o.type} (${o.moda})`,
      latency: meta.ping,
      ping: meta.ping,
      throughput: `${(o.daily_ops || 30) * 34} req/s`,
      connections: (o.daily_ops || 30) * 34,
      coordinates: [Number(o.lng || 107.6108), Number(o.lat || -6.8989)],
      lng: Number(o.lng || 107.6108),
      lat: Number(o.lat || -6.8989),
      location: o.address,
      status: 'ONLINE',
    };
  });
});

const selectedNo = ref(1);
const selectedOffice = computed(() => offices.value.find(o => o.no === selectedNo.value) || offices.value[0]);

const spaMapRef = ref(null);
const isOrbiting = ref(false);
const isNodeCardCollapsed = ref(false);

const mapHud = ref({
  lng: 107.6108,
  lat: -6.8989,
  zoom: 8.3,
  pitch: 35,
  bearing: 0,
});

const selectOffice = (office) => {
  if (!office) return;
  selectedNo.value = office.no;
  isNodeCardCollapsed.value = false;
  spaMapRef.value?.flyTo(office.coordinates, 11.8, 45, 0);
};

const resetGlobalView = () => {
  spaMapRef.value?.resetView();
};

const toggle3DPitch = () => {
  spaMapRef.value?.toggle3DPitch();
};

const handleToggleOrbit = () => {
  if (!spaMapRef.value) return;
  isOrbiting.value = spaMapRef.value.toggleOrbit();
};

onMounted(async () => {
  await store.fetchStats();
});
</script>

<template>
  <div class="space-y-6">
    <!-- Clean Light Hero Header -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-6 sm:p-8 shadow-xs">
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-3">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>BKK KELAS I BANDUNG • SPA MODE</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
            Dashboard SPA Mandiri — BKK Kelas I Bandung
          </h1>
          <p class="mt-1.5 text-slate-500 text-sm max-w-2xl leading-relaxed">
            Mode <strong class="text-slate-800">Single Page Application (Vue 3 + Pinia + REST API)</strong> terintegrasi penuh dengan data Induk BKK Kelas I Bandung dan 6 Wilayah Kerja.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <button
            type="button"
            @click="store.fetchStats"
            :disabled="store.loading"
            class="px-4 py-2 rounded-xl font-semibold text-xs text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 transition-all flex items-center gap-2 cursor-pointer shadow-2xs disabled:opacity-60"
          >
            <RotateCw class="w-3.5 h-3.5 text-emerald-600" :class="{ 'animate-spin': store.loading }" />
            <span>{{ store.loading ? 'Mengambil Data...' : 'Segarkan Data API' }}</span>
          </button>

          <a
            :href="getUrl('/inertia/dashboard')"
            class="px-4 py-2 rounded-xl font-semibold text-white bg-slate-900 hover:bg-slate-800 transition-all text-xs shadow-xs flex items-center gap-1.5 group"
          >
            <span>Buka Dashboard Utama</span>
            <ArrowRight class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
          </a>
        </div>
      </div>
    </div>

    <!-- Clean White Metric Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
        <div class="flex items-center justify-between">
          <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">REST API Latency</div>
          <Zap class="w-4 h-4 text-emerald-500" />
        </div>
        <div class="text-2xl font-bold text-slate-900 mt-2 flex items-baseline gap-2">
          <span>{{ store.apiLatencyMs !== null ? `${store.apiLatencyMs}ms` : '...' }}</span>
          <span class="text-[10px] font-mono text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">HTTP GET</span>
        </div>
        <p class="text-[11px] text-slate-400 mt-1">Endpoint /api/v1/stats</p>
      </div>

      <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
        <div class="flex items-center justify-between">
          <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Wilayah Kerja BKK</div>
          <Building2 class="w-4 h-4 text-emerald-600" />
        </div>
        <div class="text-2xl font-bold text-slate-900 mt-2">
          {{ offices.length }} Kantor
        </div>
        <p class="text-[11px] text-slate-400 mt-1">1 Induk Bandung &amp; 6 Wilker/Pos</p>
      </div>

      <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
        <div class="flex items-center justify-between">
          <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Inspeksi Pesawat</div>
          <Plane class="w-4 h-4 text-blue-600" />
        </div>
        <div class="text-2xl font-bold text-slate-900 mt-2">
          {{ store.metrics.total_inspections ?? 0 }}
        </div>
        <p class="text-[11px] text-emerald-600 font-medium mt-1">{{ store.metrics.compliance_rate ?? 98.4 }}% Kelaikan Terbang</p>
      </div>

      <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
        <div class="flex items-center justify-between">
          <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Basis Data &amp; Audit</div>
          <Database class="w-4 h-4 text-slate-600" />
        </div>
        <div class="text-2xl font-bold text-slate-900 mt-2 flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
          <span>{{ store.metrics.total_activities ?? 0 }} Log</span>
        </div>
        <p class="text-[11px] text-slate-400 mt-1">{{ store.metrics.total_users ?? 0 }} Akun Pengguna Aktif</p>
      </div>
    </div>

    <!-- OpenStreetMap WebGL Map — Induk BKK Kelas I Bandung & 6 Wilayah Kerja -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs space-y-3">
      <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-100">
        <div class="flex flex-wrap items-center gap-1.5">
          <span class="text-xs text-slate-400 font-medium mr-1">Fly to Node:</span>
          <button
            v-for="office in offices"
            :key="'spa-fly-' + office.no"
            type="button"
            @click="selectOffice(office)"
            class="px-2.5 py-1 rounded-full text-xs transition-all cursor-pointer flex items-center gap-1.5 border"
            :class="selectedNo === office.no
              ? 'bg-slate-900 border-slate-900 text-white font-semibold shadow-2xs'
              : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
          >
            <span class="text-[9px] font-mono font-bold" :class="selectedNo === office.no ? 'text-emerald-400' : 'text-slate-400'">{{ office.flag }}</span>
            <span>{{ office.node_label }}</span>
            <span class="text-[9px] font-mono" :class="selectedNo === office.no ? 'text-emerald-300' : 'text-emerald-600'">
              ({{ office.ping }})
            </span>
          </button>
        </div>

        <div class="flex flex-wrap items-center gap-1.5">
          <button
            type="button"
            @click="resetGlobalView"
            class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200/90 flex items-center gap-1.5 transition-colors cursor-pointer"
          >
            <Globe class="w-3.5 h-3.5 text-emerald-600" />
            <span>Tampilan Global</span>
          </button>
          <button
            type="button"
            @click="toggle3DPitch"
            class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200/90 flex items-center gap-1.5 transition-colors cursor-pointer"
          >
            <Box class="w-3.5 h-3.5 text-slate-500" />
            <span>Kemiringan 3D</span>
          </button>
          <button
            type="button"
            @click="handleToggleOrbit"
            class="px-2.5 py-1.5 rounded-lg text-xs font-medium border flex items-center gap-1.5 transition-colors cursor-pointer"
            :class="isOrbiting
              ? 'bg-emerald-600 text-white border-emerald-600'
              : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200/90'"
          >
            <RotateCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isOrbiting }" />
            <span>Auto Orbit</span>
          </button>
        </div>
      </div>

      <!-- Map Container -->
      <div class="relative rounded-xl overflow-hidden border border-slate-200">
        <BaseMap
          ref="spaMapRef"
          :markers="offices"
          :initialCenter="[107.6108, -6.68]"
          :initialZoom="7.75"
          :initialPitch="35"
          :initialBearing="0"
          height="440px"
          styleType="osm"
          @selectMarker="(node) => selectOffice(node)"
          @mapMove="(coords) => (mapHud = coords)"
        />

        <!-- Top-Left Compact Real-Time WebGL HUD -->
        <div class="pointer-events-none absolute top-2.5 left-2.5 z-10 bg-white/92 backdrop-blur-md border border-slate-200/90 rounded-xl px-2.5 py-2 text-slate-700 shadow-md w-44 font-mono text-[9px]">
          <div class="flex items-center gap-1 text-emerald-700 font-bold mb-1 text-[10px]">
            <Navigation class="w-2.5 h-2.5 text-emerald-600" />
            <span>Real-Time WebGL HUD</span>
          </div>
          <div class="space-y-0.5">
            <div class="flex items-center justify-between gap-2">
              <span class="text-slate-400">Lng/Lat:</span>
              <span class="text-slate-900 font-bold">{{ Number(mapHud.lng).toFixed(2) }}, {{ Number(mapHud.lat).toFixed(4) }}</span>
            </div>
            <div class="flex items-center justify-between gap-2">
              <span class="text-slate-400">Zoom/Pitch:</span>
              <span class="text-slate-900 font-bold">{{ Number(mapHud.zoom).toFixed(2) }}x / {{ mapHud.pitch }}°</span>
            </div>
            <div class="flex items-center justify-between gap-2">
              <span class="text-slate-400">Bearing:</span>
              <span class="text-slate-900 font-bold">{{ mapHud.bearing }}°</span>
            </div>
          </div>
        </div>

        <!-- Bottom-Right Compact Selected BKK Office Card -->
        <div class="pointer-events-auto absolute bottom-2.5 right-2.5 z-10 bg-white/95 backdrop-blur-md border border-slate-200/90 rounded-xl px-3 py-2 text-slate-800 shadow-lg max-w-[235px] transition-all">
          <div class="flex items-center justify-between gap-1.5" :class="{ 'border-b border-slate-100 pb-1.5 mb-1.5': !isNodeCardCollapsed }">
            <div class="flex items-center gap-1.5 min-w-0">
              <span class="text-[9px] font-mono font-bold text-white shrink-0 px-1.5 py-0.5 rounded bg-slate-900">
                {{ selectedOffice?.flag }}
              </span>
              <div class="min-w-0">
                <div class="text-[11px] font-bold text-slate-900 truncate leading-tight" :title="selectedOffice?.name">
                  {{ selectedOffice?.short_name || selectedOffice?.name }}
                </div>
                <div v-if="!isNodeCardCollapsed" class="text-[9px] text-slate-400 truncate leading-tight">
                  {{ selectedOffice?.type || 'Wilayah Kerja' }} ({{ selectedOffice?.moda || 'BKK Bandung' }})
                </div>
              </div>
            </div>
            <div class="flex items-center gap-1 shrink-0">
              <span class="px-1.5 py-0.5 rounded-full text-[8px] font-mono font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                ONLINE
              </span>
              <button
                type="button"
                @click="isNodeCardCollapsed = !isNodeCardCollapsed"
                class="w-4 h-4 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-[10px] font-mono leading-none cursor-pointer"
                :title="isNodeCardCollapsed ? 'Tampilkan detail' : 'Perkecil kotak'"
              >
                {{ isNodeCardCollapsed ? '+' : '−' }}
              </button>
            </div>
          </div>

          <div v-if="!isNodeCardCollapsed" class="space-y-1">
            <div class="flex items-center justify-between text-[9px] font-mono">
              <span class="text-slate-400">Ping: <strong class="text-emerald-600">{{ selectedOffice?.ping || '4ms' }}</strong></span>
              <span class="text-slate-400">Load: <strong class="text-blue-600">{{ selectedOffice?.connections || 1428 }} req/s</strong></span>
            </div>

            <div
              class="text-[9px] text-slate-500 flex items-start gap-1 pt-1 border-t border-slate-100"
              :title="selectedOffice?.address"
            >
              <span class="shrink-0 text-[9px]">📍</span>
              <span class="leading-tight line-clamp-2">{{ selectedOffice?.address }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Daftar 7 Kantor BKK Kelas I Bandung & Wilkernya -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-6 shadow-xs">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-4 border-b border-slate-100">
        <div>
          <h2 class="text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <MapPin class="w-4 h-4 text-emerald-600" />
            <span>Induk BKK Kelas I Bandung &amp; 6 Wilayah Kerja</span>
          </h2>
          <p class="text-xs text-slate-400 mt-0.5">
            Klik baris untuk terbang (Fly to Node) ke titik kantor pada peta OpenStreetMap di atas
          </p>
        </div>
        <span class="px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">
          {{ selectedOffice?.name }}
        </span>
      </div>

      <div class="overflow-x-auto rounded-xl border border-slate-200/80">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50 text-slate-700 font-semibold text-[11px] uppercase border-b border-slate-200/80">
              <th class="py-3 px-3 border-r border-slate-200/80 w-12 text-center">NO</th>
              <th class="py-3 px-4 border-r border-slate-200/80">KANTOR</th>
              <th class="py-3 px-4">ALAMAT</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            <tr
              v-for="item in offices"
              :key="item.no"
              @click="selectOffice(item)"
              class="hover:bg-slate-50 transition-colors cursor-pointer"
              :class="selectedNo === item.no ? 'bg-emerald-50/60' : ''"
            >
              <td class="py-3 px-3 border-r border-slate-100 text-center font-mono font-bold text-slate-600">
                {{ item.no }}
              </td>
              <td class="py-3 px-4 border-r border-slate-100 font-semibold text-slate-900">
                {{ item.name }}
              </td>
              <td class="py-3 px-4 text-slate-500 text-[11px]">
                {{ item.address }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
