<script setup>
import { ref, computed, watch, onMounted, onUnmounted, defineAsyncComponent } from 'vue';
import { useTheme } from '@/composables/useTheme';
import {
  Radio,
  Globe,
  Box,
  RotateCw,
  Scan,
  Crosshair,
  Terminal,
  MapPin,
} from 'lucide-vue-next';

const BaseMap = defineAsyncComponent(() => import('../../../Components/BaseMap.vue'));
const { isDark } = useTheme();

const props = defineProps({
  bkkMapMarkers: { type: Array, default: () => [] },
  selectedOffice: { type: Object, default: null },
  activeMapNode: { type: Object, default: null },
  radarBlips: { type: Array, default: () => [] },
  radarAngle: { type: Number, default: 0 },
  radarSweepSpeed: { type: String, default: 'normal' },
  activeRadarFilter: { type: String, default: 'all' },
  isSonarPinging: { type: Boolean, default: false },
});

const emit = defineEmits([
  'select-office',
  'trigger-ping',
  'update:radarSweepSpeed',
  'update:activeRadarFilter',
]);

const mapComponentRef = ref(null);
const mapViewportRef = ref(null);
const shouldMountMap = ref(false);
let mapObserver = null;
const activeMapLayer = ref('auto');
const showMapRadarHud = ref(true);
const mapCenter = ref([107.68, -6.74]);
const mapZoom = ref(8.05);
const mapPitch = ref(25);
const mapBearing = ref(0);
const isOrbiting = ref(false);
const isNodeCardMinimized = ref(false);
const hoveredRadarBlip = ref(null);

const radarFilterOptions = computed(() => {
  const markers = props.bkkMapMarkers || [];
  const udaraCount = markers.filter((node) => {
    const modaLower = `${node.moda || ''} ${node.type || ''}`.toLowerCase();
    return node.is_hq || modaLower.includes('udara') || modaLower.includes('bandara');
  }).length;
  const lautCount = markers.filter((node) => {
    const modaLower = `${node.moda || ''} ${node.type || ''}`.toLowerCase();
    return !node.is_hq && (modaLower.includes('laut') || modaLower.includes('pelabuhan'));
  }).length;

  return [
    { id: 'all', label: `SEMUA (${markers.length})` },
    { id: 'udara', label: `UDARA (${udaraCount})` },
    { id: 'laut', label: `LAUT (${lautCount})` },
  ];
});

const lockedRadarTarget = computed(() => hoveredRadarBlip.value || props.activeMapNode);

const lockedRadarMeta = computed(() => {
  const target = lockedRadarTarget.value;
  if (!target) return null;
  return props.radarBlips.find((b) => b.no === target.no) || target;
});

const ensureMapMounted = () => {
  shouldMountMap.value = true;
  if (mapObserver) {
    mapObserver.disconnect();
    mapObserver = null;
  }
};

const switchMapLayer = (layerKey) => {
  activeMapLayer.value = layerKey;
};

const onCameraMove = (cam) => {
  mapCenter.value = [Number(cam.lng), Number(cam.lat)];
  mapZoom.value = Number(cam.zoom);
  mapPitch.value = Number(cam.pitch);
  mapBearing.value = Number(cam.bearing);
};

const resetGlobalView = () => {
  mapComponentRef.value?.resetView();
};

const toggle3DPitch = () => {
  mapComponentRef.value?.toggle3DPitch();
};

const toggleOrbit = () => {
  if (mapComponentRef.value) {
    isOrbiting.value = mapComponentRef.value.toggleOrbit();
  }
};

const flyToNode = (target) => {
  ensureMapMounted();
  isNodeCardMinimized.value = false;
  if (target && mapComponentRef.value) {
    mapComponentRef.value.flyTo(
      [target.lng, target.lat],
      target.zoom || 12.2,
      target.pitch || 45,
      target.bearing || 0
    );
  }
};

watch(
  () => props.selectedOffice?.no,
  (no) => {
    if (!no) return;
    ensureMapMounted();
    const target = props.bkkMapMarkers.find((m) => m.no === no);
    if (target) {
      isNodeCardMinimized.value = false;
    }
  }
);

onMounted(() => {
  if (typeof IntersectionObserver !== 'undefined' && mapViewportRef.value) {
    mapObserver = new IntersectionObserver(
      (entries) => {
        if (entries.some((e) => e.isIntersecting)) {
          ensureMapMounted();
        }
      },
      { rootMargin: '150px' }
    );
    mapObserver.observe(mapViewportRef.value);
  } else {
    shouldMountMap.value = true;
  }
});

onUnmounted(() => {
  if (mapObserver) {
    mapObserver.disconnect();
    mapObserver = null;
  }
  if (isOrbiting.value && mapComponentRef.value) {
    mapComponentRef.value.toggleOrbit();
  }
});

defineExpose({ flyToNode });
</script>

<template>
  <div class="bg-white dark:bg-[#00221F] rounded-xl border border-slate-200/90 dark:border-slate-700/90 shadow-[0_1px_2px_rgba(15,23,42,0.03)] overflow-hidden font-sans">
    <!-- Centerpiece Top Command Bar -->
    <div class="px-4 py-2.5 bg-[#004640] text-white flex flex-wrap items-center justify-between gap-2.5 border-b border-[#003B36]">
      <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-md bg-[#00A99D] text-white flex items-center justify-center shrink-0">
          <Scan class="w-4 h-4" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-xs sm:text-sm font-bold tracking-tight text-white">
              Peta Geospasial Wilayah Kerja Jawa Barat
            </h2>
            <span class="px-1.5 py-0.2 rounded text-[10px] font-mono font-semibold bg-[#002D29] text-[#99DFD6] border border-[#00A99D]/30">
              {{ bkkMapMarkers.length || 7 }} NODE
            </span>
          </div>
          <p class="text-[11px] text-slate-200/85">
            Koneksi Kantor Induk Bandung &amp; {{ Math.max(0, (bkkMapMarkers.length || 7) - 1) }} Wilayah Kerja Pelabuhan/Bandara
          </p>
        </div>
      </div>

      <!-- Controls: Map Layer Switcher & WebGL Radar Actions -->
      <div class="flex flex-wrap items-center gap-1.5 text-xs">
        <div class="inline-flex items-center p-0.5 rounded-md bg-[#002D29] border border-white/15 text-[10px] font-semibold">
          <button
            type="button"
            @click="switchMapLayer('auto')"
            class="px-2 py-0.5 rounded transition-colors cursor-pointer"
            :class="activeMapLayer === 'auto' ? 'bg-[#00A99D] text-white' : 'text-slate-300 hover:text-white'"
            title="Ikuti tema Terang / Gelap"
          >
            Auto
          </button>
          <button
            type="button"
            @click="switchMapLayer('osm')"
            class="px-2 py-0.5 rounded transition-colors cursor-pointer"
            :class="activeMapLayer === 'osm' ? 'bg-[#00A99D] text-white' : 'text-slate-300 hover:text-white'"
          >
            OSM
          </button>
          <button
            type="button"
            @click="switchMapLayer('hot')"
            class="px-2 py-0.5 rounded transition-colors cursor-pointer"
            :class="activeMapLayer === 'hot' ? 'bg-[#00A99D] text-white' : 'text-slate-300 hover:text-white'"
          >
            Humanitarian
          </button>
          <button
            type="button"
            @click="switchMapLayer('osmde')"
            class="px-2 py-0.5 rounded transition-colors cursor-pointer"
            :class="activeMapLayer === 'osmde' ? 'bg-[#00A99D] text-white' : 'text-slate-300 hover:text-white'"
          >
            Topografi
          </button>
        </div>

        <div class="flex items-center gap-1">
          <button
            type="button"
            @click="showMapRadarHud = !showMapRadarHud"
            class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-semibold border transition-colors cursor-pointer"
            :class="showMapRadarHud
              ? 'bg-[#99CC33] text-[#002B27] border-[#99CC33]'
              : 'bg-white/10 hover:bg-white/20 text-white border-white/15'"
            title="Tampilkan / Sembunyikan Overlay Pemindai Radar di Peta"
          >
            <Radio class="w-3 h-3" />
            <span>Radar HUD</span>
          </button>
          <button
            type="button"
            @click="resetGlobalView"
            class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-semibold bg-white/10 hover:bg-white/20 text-white transition-colors cursor-pointer border border-white/15"
            title="Reset tampilan seluruh Jawa Barat"
          >
            <Globe class="w-3 h-3 text-[#99CC33]" />
            <span>Global</span>
          </button>
          <button
            type="button"
            @click="toggle3DPitch"
            class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-semibold border transition-colors cursor-pointer"
            :class="mapPitch > 25
              ? 'bg-[#00A99D] text-white border-[#00A99D]'
              : 'bg-white/10 hover:bg-white/20 text-white border-white/15'"
          >
            <Box class="w-3 h-3" />
            <span>3D</span>
          </button>
          <button
            type="button"
            @click="toggleOrbit"
            class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-semibold border transition-colors cursor-pointer"
            :class="isOrbiting
              ? 'bg-[#00A99D] text-white border-[#00A99D]'
              : 'bg-white/10 hover:bg-white/20 text-white border-white/15'"
          >
            <RotateCw class="w-3 h-3" :class="isOrbiting ? 'animate-spin' : ''" />
            <span>Orbit</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Fly to Node Ribbon -->
    <div class="px-3.5 py-1.5 bg-slate-50 dark:bg-[#002D29] border-b border-slate-200 dark:border-[#003B36] text-slate-700 dark:text-white flex flex-wrap items-center justify-between gap-2 text-xs">
      <div class="flex flex-wrap items-center gap-1">
        <span class="text-[10px] font-mono font-semibold tracking-wider uppercase text-[#005851] dark:text-[#99CC33] mr-1 flex items-center gap-1">
          <Crosshair class="w-3 h-3 text-[#00A99D] dark:text-[#99CC33]" />
          NODE:
        </span>
        <button
          v-for="node in bkkMapMarkers"
          :key="node.id"
          type="button"
          :aria-pressed="selectedOffice?.no === node.no"
          @click="emit('select-office', node)"
          class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] transition-colors cursor-pointer border"
          :class="selectedOffice?.no === node.no
            ? 'bg-[#00A99D] text-white border-[#00A99D] font-bold shadow-xs'
            : 'bg-white dark:bg-[#003B36]/80 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-[#004D44] hover:bg-[#E6F7F5] dark:hover:bg-[#005851] hover:border-[#00A99D]/60 dark:hover:text-white'"
        >
          <span class="text-[9px] font-mono font-bold" :class="selectedOffice?.no === node.no ? 'text-white' : 'text-[#007B72] dark:text-[#99CC33]'">
            {{ node.shortCode }}
          </span>
          <span>{{ node.shortTitle.replace('Wilker ', '').replace('Induk ', '') }}</span>
          <span
            v-if="node.total > 0"
            class="text-[9px] font-semibold px-1 rounded font-mono tabular-nums"
            :class="selectedOffice?.no === node.no ? 'bg-[#003B36] text-[#99CC33]' : 'bg-[#E6F7F5] dark:bg-[#00A99D]/20 text-[#005851] dark:text-[#E6F7F5]'"
          >
            {{ node.total }}
          </span>
        </button>
      </div>

      <div class="text-[10px] font-mono text-slate-500 dark:text-slate-300 hidden xl:flex items-center gap-1.5">
        <span class="w-1.5 h-1.5 rounded-full bg-[#7CA622] dark:bg-[#99CC33]"></span>
        <span>TELEMETRI GEOSPASIAL AKTIF</span>
      </div>
    </div>

    <!-- Centerpiece Body: Map + Tactical Wilker Telemetry Dock -->
    <div class="grid grid-cols-1 lg:grid-cols-12 lg:items-stretch divide-y lg:divide-y-0 lg:divide-x divide-slate-200 dark:divide-slate-800 bg-slate-100 dark:bg-[#00221F]">
      <!-- Large WebGL Map Area (8 Cols) -->
      <div
        ref="mapViewportRef"
        class="lg:col-span-8 relative min-w-0 bg-slate-100 dark:bg-[#00221F] overflow-hidden h-[320px] sm:h-[380px] lg:h-auto lg:min-h-[470px]"
      >
        <div class="w-full h-full lg:absolute lg:inset-0">
          <BaseMap
            v-if="shouldMountMap"
            ref="mapComponentRef"
            :initialCenter="[107.68, -6.74]"
            :initialZoom="8.05"
            :initialPitch="25"
            :initialBearing="0"
            :styleType="activeMapLayer"
            :markers="bkkMapMarkers"
            height="100%"
            @selectMarker="(m) => emit('select-office', m)"
            @mapMove="onCameraMove"
          />
          <div
            v-else
            class="w-full h-full flex flex-col items-center justify-center gap-2 text-center px-4 bg-[#F0FAF8] dark:bg-[#00221F] text-[#005851] dark:text-[#99DFD6]"
          >
            <Globe class="w-8 h-8 text-[#00A99D] animate-pulse" />
            <p class="text-xs font-semibold text-slate-800 dark:text-white">Peta Geospasial Wilayah Kerja Jawa Barat</p>
            <button
              type="button"
              @click="ensureMapMounted"
              class="px-3 py-1.5 rounded-lg bg-[#00A99D] hover:bg-[#009388] text-white text-xs font-semibold cursor-pointer"
            >
              Muat Peta Interaktif
            </button>
          </div>
        </div>

        <!-- Interactive Map Radar HUD Overlay -->
        <div v-if="showMapRadarHud" class="absolute inset-0 pointer-events-none z-10 flex items-center justify-center overflow-hidden">
          <div class="absolute top-3 left-3 w-5 h-5 border-t border-l border-[#005851]/45 dark:border-[#00A99D]/60"></div>
          <div class="absolute top-3 right-12 w-5 h-5 border-t border-r border-[#005851]/45 dark:border-[#00A99D]/60"></div>
          <div class="absolute bottom-9 left-3 w-5 h-5 border-b border-l border-[#005851]/45 dark:border-[#00A99D]/60"></div>
          <div class="absolute bottom-9 right-3 w-5 h-5 border-b border-r border-[#005851]/45 dark:border-[#00A99D]/60"></div>

          <div class="relative w-[220px] h-[220px] sm:w-[340px] sm:h-[340px] lg:w-[380px] lg:h-[380px] rounded-full border border-[#005851]/30 dark:border-[#00A99D]/30 flex items-center justify-center">
            <div class="w-[72%] h-[72%] rounded-full border border-dashed border-[#005851]/25 dark:border-[#00A99D]/25 flex items-center justify-center">
              <div class="w-[48%] h-[48%] rounded-full border border-[#7CA622]/40 dark:border-[#99CC33]/40 flex items-center justify-center">
                <div class="w-2.5 h-2.5 rounded-full border border-[#7CA622] dark:border-[#99CC33] bg-[#00A99D]/40"></div>
              </div>
            </div>
            <div class="absolute inset-x-0 top-1/2 h-px bg-gradient-to-r from-transparent via-[#00A99D]/35 to-transparent"></div>
            <div class="absolute inset-y-0 left-1/2 w-px bg-gradient-to-b from-transparent via-[#00A99D]/35 to-transparent"></div>
            <div
              class="absolute inset-0 rounded-full radar-conic-beam opacity-30"
              :class="isDark ? 'radar-beam-dark' : 'radar-beam-light'"
              :style="{ animationDuration: radarSweepSpeed === 'fast' ? '2.2s' : '4.5s' }"
            ></div>
            <div v-if="isSonarPinging" class="absolute inset-0 rounded-full border-2 border-[#7CA622] dark:border-[#99CC33] sonar-shockwave"></div>
          </div>
        </div>

        <!-- Top Left Telemetry HUD Overlay -->
        <div class="hidden sm:block absolute top-3 left-3 z-20 w-48 rounded-lg bg-white/92 dark:bg-[#00221F]/90 backdrop-blur-sm border border-slate-200/90 dark:border-[#00A99D]/35 px-2.5 py-2 shadow-md text-[10px] font-mono text-slate-700 dark:text-[#99DFD6] pointer-events-none">
          <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#00A99D]/25 pb-1 mb-1">
            <div class="flex items-center gap-1 font-bold text-[#005851] dark:text-[#99CC33]">
              <Terminal class="w-3 h-3 text-[#00A99D] dark:text-[#99CC33]" />
              <span>TELEMETRI GPS</span>
            </div>
            <span class="w-1.5 h-1.5 rounded-full bg-[#00A99D] dark:bg-[#99CC33]"></span>
          </div>
          <div class="space-y-0.5 text-slate-600 dark:text-slate-200 tabular-nums">
            <div class="flex justify-between">
              <span class="text-slate-400">KOORDINAT:</span>
              <span class="text-slate-900 dark:text-white font-semibold">{{ mapCenter[1].toFixed(4) }}, {{ mapCenter[0].toFixed(4) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-400">ZOOM / PITCH:</span>
              <span class="text-slate-900 dark:text-white font-semibold">{{ mapZoom.toFixed(1) }}x / {{ mapPitch }}&deg;</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-400">AZIMUTH:</span>
              <span class="text-[#007B72] dark:text-[#99CC33] font-semibold">{{ String(radarAngle).padStart(3, '0') }}&deg; ACTIVE</span>
            </div>
          </div>
        </div>

        <!-- Bottom Right Target Node Telemetry HUD -->
        <div
          v-if="activeMapNode"
          class="absolute bottom-2.5 right-2.5 sm:bottom-3 sm:right-3 z-20 rounded-lg bg-white/95 dark:bg-[#00221F]/95 backdrop-blur-sm border border-slate-200/90 dark:border-[#00A99D]/45 shadow-lg transition-all duration-200 text-slate-800 dark:text-white"
          :class="isNodeCardMinimized ? 'w-48 sm:w-52 px-2.5 py-1.5' : 'w-60 sm:w-[272px] px-2.5 sm:px-3 py-2 sm:py-2.5'"
        >
          <div class="flex items-start justify-between gap-1.5">
            <div class="flex items-start gap-2 min-w-0">
              <div class="w-7 h-7 rounded-md bg-[#00A99D] text-white flex items-center justify-center text-[11px] font-mono font-bold shrink-0 mt-0.5">
                {{ activeMapNode.shortCode }}
              </div>
              <div class="min-w-0">
                <h4
                  class="text-xs font-bold text-slate-900 dark:text-white leading-snug"
                  :class="isNodeCardMinimized ? 'truncate' : 'line-clamp-2'"
                >
                  {{ activeMapNode.title }}
                </h4>
                <p v-if="!isNodeCardMinimized" class="text-[10px] text-[#007B72] dark:text-[#99CC33] truncate font-mono mt-0.5 font-semibold">
                  {{ activeMapNode.type }} &bull; {{ activeMapNode.moda }}
                </p>
              </div>
            </div>
            <button
              type="button"
              @click="isNodeCardMinimized = !isNodeCardMinimized"
              :aria-label="isNodeCardMinimized ? 'Perluas kartu node wilker' : 'Perkecil kartu node wilker'"
              class="w-5 h-5 rounded bg-slate-100 dark:bg-[#003B36] hover:bg-[#E6F7F5] dark:hover:bg-[#005851] text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs leading-none cursor-pointer shrink-0 border border-slate-200/80 dark:border-transparent"
            >
              {{ isNodeCardMinimized ? '+' : '−' }}
            </button>
          </div>

          <template v-if="!isNodeCardMinimized">
            <div class="grid grid-cols-2 gap-1.5 pt-2 mt-2 border-t border-slate-100 dark:border-[#003B36] text-[10px] font-mono tabular-nums">
              <div class="p-1.5 rounded bg-slate-50 dark:bg-[#003B36]/80 border border-slate-200/80 dark:border-[#004D44]">
                <span class="text-[9px] text-slate-500 dark:text-slate-400 block uppercase">Total Dokumen</span>
                <span class="text-xs font-bold text-[#005851] dark:text-[#99CC33]">{{ activeMapNode.total || 0 }}</span>
              </div>
              <div class="p-1.5 rounded bg-slate-50 dark:bg-[#003B36]/80 border border-slate-200/80 dark:border-[#004D44]">
                <span class="text-[9px] text-slate-500 dark:text-slate-400 block uppercase">Terakhir Aktif</span>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-[#E6F7F5] truncate block">{{ activeMapNode.last_human || 'Siaga Aktif' }}</span>
              </div>
            </div>
            <div class="mt-1.5 pt-1.5 border-t border-slate-100 dark:border-[#003B36] text-[10px] text-slate-600 dark:text-slate-300 line-clamp-2 leading-tight flex items-start gap-1">
              <MapPin class="w-3 h-3 text-[#00A99D] dark:text-[#99CC33] shrink-0 mt-0.5" />
              <span>{{ activeMapNode.address }}</span>
            </div>
          </template>
        </div>
      </div>

      <!-- Side Cockpit: Interactive Circular Polar Sonar Radar Scanner (4 Cols) -->
      <div class="lg:col-span-4 bg-gradient-to-b from-[#F4FBF9] to-[#E9F7F4] dark:from-[#002824] dark:to-[#002824] text-slate-800 dark:text-white p-3.5 flex flex-col justify-between relative overflow-hidden border-t lg:border-t-0 lg:border-l border-slate-200 dark:border-[#004D44]">
        <div class="relative z-10 flex items-center justify-between gap-2">
          <div>
            <div class="flex items-center gap-1.5">
              <span class="w-1.5 h-1.5 rounded-full bg-[#00A99D] dark:bg-[#99CC33]"></span>
              <h3 class="text-xs font-bold tracking-tight text-[#004640] dark:text-white uppercase">
                Radar Jaringan Wilker
              </h3>
            </div>
            <p class="text-[10px] text-[#007B72] dark:text-[#99DFD6] font-mono">
              Radius 150 KM &bull; Bandung Hub
            </p>
          </div>

          <div class="flex items-center gap-1">
            <button
              type="button"
              @click="emit('update:radarSweepSpeed', radarSweepSpeed === 'normal' ? 'fast' : 'normal')"
              class="px-1.5 py-0.5 rounded text-[10px] font-mono font-semibold border transition-colors cursor-pointer"
              :class="radarSweepSpeed === 'fast'
                ? 'bg-[#99CC33] text-[#002B27] border-[#99CC33]'
                : 'bg-white dark:bg-[#003B36] text-[#005851] dark:text-[#99DFD6] border-[#99DFD6] dark:border-[#00A99D]/40 hover:bg-[#E6F7F5] dark:hover:text-white'"
              title="Ubah kecepatan putaran radar"
            >
              {{ radarSweepSpeed === 'fast' ? '2x FAST' : '1x SCAN' }}
            </button>
            <button
              type="button"
              @click="emit('trigger-ping')"
              class="px-1.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-[#00A99D]/15 dark:bg-[#00A99D]/25 hover:bg-[#00A99D] text-[#005851] dark:text-white hover:text-white border border-[#00A99D]/40 dark:border-[#00A99D]/50 transition-colors cursor-pointer"
            >
              PING
            </button>
          </div>
        </div>

        <div class="relative z-10 my-2 sm:my-3 flex items-center justify-center">
          <div
            class="radar-dial-surface relative w-56 h-56 sm:w-64 sm:h-64 lg:w-[276px] lg:h-[276px] xl:w-[300px] xl:h-[300px] max-w-full aspect-square shrink-0 rounded-full border-2 border-[#00A99D]/65 shadow-[0_8px_28px_rgba(0,169,157,0.16)] dark:shadow-[0_0_40px_rgba(0,169,157,0.28)] flex items-center justify-center overflow-hidden"
            :class="isDark ? 'radar-dial-dark' : 'radar-dial-light'"
          >
            <div class="absolute w-[82%] h-[82%] rounded-full border border-[#00A99D]/35 dark:border-[#00A99D]/25"></div>
            <div class="absolute w-[60%] h-[60%] rounded-full border border-dashed border-[#00A99D]/40 dark:border-[#00A99D]/30"></div>
            <div class="absolute w-[36%] h-[36%] rounded-full border border-[#00A99D]/35 dark:border-[#00A99D]/25"></div>

            <div class="absolute inset-x-0 top-1/2 h-px bg-[#00A99D]/30"></div>
            <div class="absolute inset-y-0 left-1/2 w-px bg-[#00A99D]/30"></div>
            <div class="absolute inset-0 rotate-45">
              <div class="absolute inset-x-0 top-1/2 h-px bg-[#00A99D]/15"></div>
              <div class="absolute inset-y-0 left-1/2 w-px bg-[#00A99D]/15"></div>
            </div>

            <span class="absolute top-1.5 left-1/2 -translate-x-1/2 text-[9px] font-mono font-bold text-[#557A18] dark:text-[#99CC33]">000&deg; U</span>
            <span class="absolute bottom-1.5 left-1/2 -translate-x-1/2 text-[9px] font-mono font-bold text-[#005851] dark:text-[#00A99D]">180&deg; S</span>
            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[9px] font-mono font-bold text-[#005851] dark:text-[#00A99D]">090&deg; T</span>
            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[9px] font-mono font-bold text-[#005851] dark:text-[#00A99D]">270&deg; B</span>

            <div
              class="absolute inset-0 rounded-full radar-conic-beam pointer-events-none"
              :class="isDark ? 'radar-beam-dark' : 'radar-beam-light'"
              :style="{ animationDuration: radarSweepSpeed === 'fast' ? '2s' : '4s' }"
            ></div>
            <div v-if="isSonarPinging" class="absolute inset-4 rounded-full border-2 border-[#7CA622] dark:border-[#99CC33] sonar-shockwave pointer-events-none"></div>

            <button
              v-for="blip in radarBlips"
              :key="`radar-blip-${blip.id}`"
              type="button"
              @click="emit('select-office', blip)"
              @mouseenter="hoveredRadarBlip = blip"
              @mouseleave="hoveredRadarBlip = null"
              class="absolute -translate-x-1/2 -translate-y-1/2 z-20 group/blip cursor-pointer focus:outline-none"
              :style="{ left: `${blip.x}%`, top: `${blip.y}%` }"
              :aria-label="`Pilih ${blip.shortTitle}: ${blip.distanceKm} km`"
              :title="`${blip.shortTitle}: ${blip.distanceKm} km (${blip.bearingDeg}°)`"
            >
              <span
                class="absolute -inset-1 rounded-full transition-opacity"
                :class="blip.is_hq ? 'bg-[#99CC33]/40' : (selectedOffice?.no === blip.no ? 'bg-[#00A99D]/55' : 'bg-[#00A99D]/20')"
              ></span>
              <span
                class="relative flex items-center justify-center rounded-full font-mono font-black transition-transform group-hover/blip:scale-125 shadow-md"
                :class="[
                  blip.is_hq
                    ? 'w-5 h-5 bg-[#99CC33] text-[#001F1C] text-[8px] ring-2 ring-[#005851] dark:ring-white'
                    : selectedOffice?.no === blip.no
                    ? 'w-4.5 h-4.5 bg-[#00A99D] text-white text-[8px] ring-2 ring-[#005851] dark:ring-[#99CC33]'
                    : 'w-4 h-4 bg-white dark:bg-[#005851] text-[#005851] dark:text-[#99DFD6] text-[7px] border border-[#00A99D]'
                ]"
              >
                {{ blip.shortCode }}
              </span>
            </button>
          </div>
        </div>

        <div class="relative z-10 space-y-2.5">
          <div class="flex items-center justify-center gap-1.5 text-[10px] font-mono">
            <button
              v-for="rf in radarFilterOptions"
              :key="rf.id"
              type="button"
              :aria-pressed="activeRadarFilter === rf.id"
              @click="emit('update:activeRadarFilter', rf.id)"
              class="px-2.5 py-1 rounded border transition-all cursor-pointer"
              :class="activeRadarFilter === rf.id
                ? 'bg-[#005851] dark:bg-[#00A99D] text-white border-[#005851] dark:border-[#00A99D] font-bold shadow-xs'
                : 'bg-white dark:bg-[#00221F] text-slate-600 dark:text-slate-300 border-slate-200 dark:border-[#004D44] hover:border-[#00A99D] hover:text-[#005851] dark:hover:text-white'"
            >
              {{ rf.label }}
            </button>
          </div>

          <div class="p-2.5 rounded-xl bg-white/95 dark:bg-[#001715]/90 border border-[#99DFD6] dark:border-[#00A99D]/30 shadow-xs flex items-center justify-between text-[11px] font-mono">
            <div class="truncate">
              <span class="text-slate-500 dark:text-slate-400">LOCK: </span>
              <strong class="text-[#005851] dark:text-[#99CC33]">{{ lockedRadarTarget?.shortTitle || 'Induk Bandung' }}</strong>
            </div>
            <div class="text-right shrink-0 text-[#007B72] dark:text-[#99DFD6]">
              <span>{{ lockedRadarMeta?.distanceKm || 0 }} KM</span>
              <span class="mx-1">&bull;</span>
              <span>{{ lockedRadarMeta?.bearingDeg || 0 }}&deg;</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes radar-sweep-spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

@keyframes sonar-ping-expand {
  0% {
    transform: scale(0.15);
    opacity: 0.95;
  }
  70% {
    opacity: 0.4;
  }
  100% {
    transform: scale(1.25);
    opacity: 0;
  }
}

.radar-dial-light {
  background: radial-gradient(circle at center, #ffffff 0%, #e6f7f5 68%, #cfece8 100%);
}

.radar-dial-dark {
  background: radial-gradient(circle at center, #002320 0%, #001715 70%, #000f0e 100%);
}

.radar-conic-beam {
  animation: radar-sweep-spin 4s linear infinite;
}

.radar-beam-light {
  background: conic-gradient(
    from 0deg,
    transparent 0deg,
    rgba(0, 169, 157, 0.03) 245deg,
    rgba(0, 169, 157, 0.24) 325deg,
    rgba(124, 166, 34, 0.65) 360deg
  );
}

.radar-beam-dark {
  background: conic-gradient(
    from 0deg,
    transparent 0deg,
    rgba(0, 169, 157, 0.03) 240deg,
    rgba(0, 169, 157, 0.38) 325deg,
    rgba(153, 204, 51, 0.85) 360deg
  );
}

.sonar-shockwave {
  animation: sonar-ping-expand 1.6s cubic-bezier(0, 0, 0.2, 1) forwards;
}
</style>
