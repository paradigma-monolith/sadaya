<script setup>
import { ref, reactive, computed, watch, onMounted, onUnmounted, defineAsyncComponent } from 'vue';
import { useTheme } from '@/composables/useTheme';
import {
  computeRadarVectorMetrics,
  didSweepCrossBearing,
  getCompassSector,
  normalizeDeg,
} from './radarTelemetryUtils';
import {
  Radio,
  Globe,
  Box,
  RotateCw,
  Scan,
  Crosshair,
  Terminal,
  MapPin,
  Activity,
  ShieldCheck,
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
  sinkarkes: { type: Object, default: () => ({}) },
  maxOfficeTotal: { type: Number, default: 1 },
});

const emit = defineEmits([
  'select-office',
  'trigger-ping',
  'update:radarSweepSpeed',
  'update:activeRadarFilter',
]);

const mapComponentRef = ref(null);
const mapViewportRef = ref(null);
const dialBeamRef = ref(null);
const mapBeamRef = ref(null);
const dialAzmRef = ref(null);
const mapAzmRef = ref(null);
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

// Real-time 60fps sweep synchronization & discrete target capture engine
const blipHits = reactive({});
const interceptFeed = ref([]);
const lastInterceptedNode = ref(null);
const pingWaveTick = ref(0);
const pendingTimers = new Set();
let hitSeqCounter = 0;
let sweepRafId = null;
let currentSweepDeg = 0;
let lastRafTs = 0;
let lastHudTextTs = 0;

const polarDegreeTicks = Array.from({ length: 36 }, (_, idx) => {
  const deg = idx * 10;
  const rad = (deg * Math.PI) / 180;
  const isMajor = deg % 30 === 0;
  const rOuter = 48.2;
  const rInner = isMajor ? 44.8 : 46.5;
  return {
    deg,
    isMajor,
    x1: (50 + rOuter * Math.sin(rad)).toFixed(2),
    y1: (50 - rOuter * Math.cos(rad)).toFixed(2),
    x2: (50 + rInner * Math.sin(rad)).toFixed(2),
    y2: (50 - rInner * Math.cos(rad)).toFixed(2),
  };
});

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

const lockedRadarMetrics = computed(() => {
  const meta = lockedRadarMeta.value;
  if (!meta) return null;
  return computeRadarVectorMetrics(
    meta,
    props.sinkarkes,
    props.maxOfficeTotal,
    props.bkkMapMarkers
  );
});

const spectrumBars = computed(() => {
  const meta = lockedRadarMeta.value;
  const metrics = lockedRadarMetrics.value;
  if (!meta || !metrics) return [32, 48, 60, 44, 56, 38];

  const isHot = Boolean(blipHits[meta.no]) || props.isSonarPinging;
  const boost = isHot ? 26 : 0;
  const base = Math.max(24, Math.min(72, Math.round(metrics.signalStrengthPct * 0.68)));
  return [
    Math.min(100, base - 12 + boost),
    Math.min(100, base + 4 + boost),
    Math.min(100, base + 14 + boost),
    Math.min(100, Math.round((metrics.relativeLoadPct * 0.55) + 28 + boost * 0.8)),
    Math.min(100, base + 8 + boost),
    Math.min(100, base - 6 + boost),
  ];
});

const isBlipHot = (no) => Boolean(blipHits[no]);

const registerBlipCapture = (blip) => {
  const hitNo = blip.no;
  const seq = ++hitSeqCounter;
  blipHits[hitNo] = { seq, at: Date.now() };

  const clearTimer = setTimeout(() => {
    pendingTimers.delete(clearTimer);
    if (blipHits[hitNo]?.seq === seq) {
      delete blipHits[hitNo];
    }
  }, 1350);
  pendingTimers.add(clearTimer);

  if (!blip.is_hq) {
    lastInterceptedNode.value = {
      no: blip.no,
      shortCode: blip.shortCode,
      shortTitle: blip.shortTitle,
      bearingDeg: blip.bearingDeg,
      sector: getCompassSector(blip.bearingDeg),
      distanceKm: blip.distanceKm,
      totalDocs: Number(blip.total || 0),
    };

    const nowStr = new Date().toLocaleTimeString('id-ID', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: false,
    });

    interceptFeed.value = [
      {
        key: `${hitNo}-${seq}`,
        no: blip.no,
        shortCode: blip.shortCode,
        shortTitle: blip.shortTitle,
        bearingFormatted: `${String(blip.bearingDeg).padStart(3, '0')}°`,
        sector: getCompassSector(blip.bearingDeg),
        distanceKm: blip.distanceKm,
        totalDocs: Number(blip.total || 0),
        timeStr: nowStr,
      },
      ...interceptFeed.value,
    ].slice(0, 3);
  }
};

const runSweepFrame = (ts) => {
  if (!lastRafTs) {
    lastRafTs = ts;
    lastHudTextTs = ts;
  }

  const dtSec = Math.min(0.25, Math.max(0, (ts - lastRafTs) / 1000));
  lastRafTs = ts;

  const degPerSec = props.radarSweepSpeed === 'fast' ? 180 : 90;
  const prevDeg = currentSweepDeg;
  const nextDeg = normalizeDeg(prevDeg + dtSec * degPerSec);
  currentSweepDeg = nextDeg;

  const transformVal = `rotate(${nextDeg.toFixed(2)}deg)`;
  if (dialBeamRef.value) {
    dialBeamRef.value.style.transform = transformVal;
  }
  if (mapBeamRef.value) {
    mapBeamRef.value.style.transform = transformVal;
  }

  if (ts - lastHudTextTs >= 65) {
    lastHudTextTs = ts;
    const rounded = Math.floor(nextDeg);
    const padAzm = String(rounded).padStart(3, '0');
    const sec = getCompassSector(rounded);
    if (dialAzmRef.value) {
      dialAzmRef.value.textContent = `${padAzm}° ${sec}`;
    }
    if (mapAzmRef.value) {
      mapAzmRef.value.textContent = `${padAzm}° ${sec} ACTV`;
    }
  }

  for (const blip of props.radarBlips || []) {
    if (didSweepCrossBearing(prevDeg, nextDeg, Number(blip.bearingDeg || 0))) {
      registerBlipCapture(blip);
    }
  }

  sweepRafId = requestAnimationFrame(runSweepFrame);
};

watch(
  () => props.isSonarPinging,
  (active) => {
    if (!active) return;
    pingWaveTick.value += 1;
    // Trigger responsive active-IFF capture highlight staggered by signal travel time
    for (const blip of props.radarBlips || []) {
      const echoDelayMs = Math.min(520, Math.round(Number(blip.distanceKm || 0) * 4));
      const t = setTimeout(() => {
        pendingTimers.delete(t);
        registerBlipCapture(blip);
      }, echoDelayMs);
      pendingTimers.add(t);
    }
  }
);

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

  if (typeof requestAnimationFrame !== 'undefined') {
    currentSweepDeg = normalizeDeg(props.radarAngle || 0);
    lastRafTs = 0;
    sweepRafId = requestAnimationFrame(runSweepFrame);
  }
});

onUnmounted(() => {
  if (mapObserver) {
    mapObserver.disconnect();
    mapObserver = null;
  }
  if (sweepRafId !== null && typeof cancelAnimationFrame !== 'undefined') {
    cancelAnimationFrame(sweepRafId);
    sweepRafId = null;
  }
  for (const timerId of pendingTimers) {
    clearTimeout(timerId);
  }
  pendingTimers.clear();
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
              ref="mapBeamRef"
              class="absolute inset-0 rounded-full opacity-30 will-change-transform"
              :class="isDark ? 'radar-beam-dark' : 'radar-beam-light'"
            >
              <span class="absolute left-1/2 bottom-1/2 w-[1.5px] h-1/2 -translate-x-1/2 bg-gradient-to-t from-transparent via-[#7CA622]/70 to-[#7CA622] dark:via-[#99CC33]/75 dark:to-[#99CC33]"></span>
            </div>
            <div v-if="isSonarPinging" class="absolute inset-0 rounded-full border-2 border-[#7CA622] dark:border-[#99CC33] sonar-shockwave"></div>
            <div v-if="isSonarPinging" class="absolute inset-0 rounded-full border border-[#00A99D] dark:border-[#5eead4] sonar-shockwave sonar-shockwave-delay"></div>
          </div>
        </div>

        <!-- Top Left Telemetry HUD Overlay -->
        <div class="hidden sm:block absolute top-3 left-3 z-20 w-52 rounded-lg bg-white/92 dark:bg-[#00221F]/90 backdrop-blur-sm border border-slate-200/90 dark:border-[#00A99D]/35 px-2.5 py-2 shadow-md text-[10px] font-mono text-slate-700 dark:text-[#99DFD6] pointer-events-none">
          <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#00A99D]/25 pb-1 mb-1">
            <div class="flex items-center gap-1 font-bold text-[#005851] dark:text-[#99CC33]">
              <Terminal class="w-3 h-3 text-[#00A99D] dark:text-[#99CC33]" />
              <span>TELEMETRI GPS</span>
            </div>
            <span class="w-1.5 h-1.5 rounded-full bg-[#00A99D] dark:bg-[#99CC33] animate-pulse"></span>
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
              <span ref="mapAzmRef" class="text-[#007B72] dark:text-[#99CC33] font-semibold">{{ String(radarAngle).padStart(3, '0') }}&deg; U ACTV</span>
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
      <div class="lg:col-span-4 bg-gradient-to-b from-[#F4FBF9] to-[#E9F7F4] dark:from-[#002824] dark:to-[#001F1C] text-slate-800 dark:text-white p-3.5 flex flex-col justify-between gap-2.5 relative overflow-hidden border-t lg:border-t-0 lg:border-l border-slate-200 dark:border-[#004D44]">
        <!-- Subtle Tactical Ambient Scanline -->
        <div class="radar-panel-scanline pointer-events-none"></div>

        <!-- Radar Cockpit Header -->
        <div class="relative z-10 flex items-start justify-between gap-2">
          <div class="min-w-0">
            <div class="flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-[#00A99D] dark:bg-[#99CC33] animate-pulse shrink-0"></span>
              <h3 class="text-xs font-bold tracking-tight text-[#004640] dark:text-white uppercase truncate">
                Radar Jaringan Wilker
              </h3>
              <span
                class="px-1.5 py-[1px] rounded text-[9px] font-mono font-bold border shrink-0"
                :class="radarSweepSpeed === 'fast'
                  ? 'bg-[#99CC33]/20 text-[#557A18] dark:text-[#99CC33] border-[#99CC33]/50'
                  : 'bg-[#00A99D]/10 text-[#005851] dark:text-[#5eead4] border-[#00A99D]/30'"
              >
                {{ isSonarPinging ? 'IFF PING' : (radarSweepSpeed === 'fast' ? '2x TRACK' : 'LIVE SWEEP') }}
              </span>
            </div>
            <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[10px] text-[#007B72] dark:text-[#99DFD6] font-mono tabular-nums">
              <span>RNG 150 KM</span>
              <span class="opacity-40">&bull;</span>
              <span>AZM <strong ref="dialAzmRef" class="text-[#004640] dark:text-[#99CC33]">000&deg; U</strong></span>
              <span class="opacity-40">&bull;</span>
              <span>NODE {{ radarBlips.length }}/{{ bkkMapMarkers.length }}</span>
            </div>
          </div>

          <div class="flex items-center gap-1 shrink-0">
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
              title="Pancarkan sinyal IFF Sonar ke seluruh wilker"
            >
              PING
            </button>
          </div>
        </div>

        <!-- Circular Polar Radar Scope -->
        <div class="relative z-10 my-0.5 flex items-center justify-center">
          <div
            class="radar-dial-surface relative w-56 h-56 sm:w-64 sm:h-64 xl:w-[266px] xl:h-[266px] max-w-full aspect-square shrink-0 rounded-full border-2 border-[#00A99D]/70 shadow-[0_8px_28px_rgba(0,169,157,0.16)] dark:shadow-[0_0_40px_rgba(0,169,157,0.3)] flex items-center justify-center overflow-hidden"
            :class="isDark ? 'radar-dial-dark' : 'radar-dial-light'"
          >
            <!-- Precision SVG Polar Grid, Degree Ticks & Active Vector Telemetry Links -->
            <svg viewBox="0 0 100 100" class="absolute inset-0 w-full h-full pointer-events-none">
              <!-- Outer Degree Tick Ring -->
              <line
                v-for="tick in polarDegreeTicks"
                :key="`tick-${tick.deg}`"
                :x1="tick.x1"
                :y1="tick.y1"
                :x2="tick.x2"
                :y2="tick.y2"
                :stroke="isDark ? (tick.isMajor ? 'rgba(153, 204, 51, 0.45)' : 'rgba(0, 169, 157, 0.25)') : (tick.isMajor ? 'rgba(0, 88, 81, 0.45)' : 'rgba(0, 169, 157, 0.28)')"
                :stroke-width="tick.isMajor ? '0.45' : '0.25'"
              />

              <!-- Calibrated Geodesic Range Rings (50 / 100 / 150 KM) -->
              <circle cx="50" cy="50" r="15" fill="none" :stroke="isDark ? 'rgba(0,169,157,0.28)' : 'rgba(0,88,81,0.25)'" stroke-width="0.35" />
              <circle cx="50" cy="50" r="30" fill="none" :stroke="isDark ? 'rgba(0,169,157,0.34)' : 'rgba(0,169,157,0.4)'" stroke-width="0.35" stroke-dasharray="1.3 1.3" />
              <circle cx="50" cy="50" r="44" fill="none" :stroke="isDark ? 'rgba(0,169,157,0.28)' : 'rgba(0,88,81,0.28)'" stroke-width="0.35" />

              <!-- Cardinal & Diagonal Axes -->
              <line x1="6" y1="50" x2="94" y2="50" :stroke="isDark ? 'rgba(0,169,157,0.24)' : 'rgba(0,169,157,0.28)'" stroke-width="0.3" />
              <line x1="50" y1="6" x2="50" y2="94" :stroke="isDark ? 'rgba(0,169,157,0.24)' : 'rgba(0,169,157,0.28)'" stroke-width="0.3" />
              <line x1="18.8" y1="18.8" x2="81.2" y2="81.2" :stroke="isDark ? 'rgba(0,169,157,0.14)' : 'rgba(0,169,157,0.16)'" stroke-width="0.25" />
              <line x1="81.2" y1="18.8" x2="18.8" y2="81.2" :stroke="isDark ? 'rgba(0,169,157,0.14)' : 'rgba(0,169,157,0.16)'" stroke-width="0.25" />

              <!-- Range Micro Annotations -->
              <text x="51.2" y="34.2" class="radar-range-text" :fill="isDark ? 'rgba(153,223,214,0.55)' : 'rgba(0,88,81,0.55)'">50K</text>
              <text x="51.2" y="19.2" class="radar-range-text" :fill="isDark ? 'rgba(153,223,214,0.55)' : 'rgba(0,88,81,0.55)'">100K</text>

              <!-- Dynamic Vector Telemetry Links (Hub BDG -> Wilkers) -->
              <g v-for="blip in radarBlips" :key="`vec-${blip.id}`">
                <template v-if="!blip.is_hq">
                  <!-- Ambient Link Mesh -->
                  <line
                    x1="50"
                    y1="50"
                    :x2="blip.x"
                    :y2="blip.y"
                    :stroke="isBlipHot(blip.no) || lockedRadarTarget?.no === blip.no
                      ? (isDark ? 'rgba(153, 204, 51, 0.85)' : 'rgba(0, 123, 114, 0.82)')
                      : (isDark ? 'rgba(0, 169, 157, 0.2)' : 'rgba(0, 169, 157, 0.25)')"
                    :stroke-width="isBlipHot(blip.no) ? '0.65' : (lockedRadarTarget?.no === blip.no ? '0.5' : '0.28')"
                    :stroke-dasharray="isBlipHot(blip.no) || lockedRadarTarget?.no === blip.no ? '1.8 1.2' : '1.2 1.8'"
                    :class="isBlipHot(blip.no) || lockedRadarTarget?.no === blip.no ? 'radar-vector-flow' : ''"
                  />
                  <!-- Outbound RF Packet Trace When Intercepted or Pinged -->
                  <line
                    v-if="isBlipHot(blip.no) || isSonarPinging"
                    x1="50"
                    y1="50"
                    :x2="blip.x"
                    :y2="blip.y"
                    pathLength="100"
                    :stroke="isDark ? '#99CC33' : '#65A30D'"
                    stroke-width="0.9"
                    stroke-linecap="round"
                    class="radar-packet-shoot"
                  />
                </template>
              </g>

              <!-- Locked Target Focus Ring -->
              <circle
                v-if="lockedRadarMeta"
                :cx="lockedRadarMeta.x"
                :cy="lockedRadarMeta.y"
                r="6.2"
                fill="none"
                :stroke="isDark ? '#99CC33' : '#005851'"
                stroke-width="0.45"
                stroke-dasharray="2.1 1.4"
                class="radar-lock-svg-spin"
              />
            </svg>

            <!-- 8-Point Compass Sector Markers -->
            <span class="absolute top-1 left-1/2 -translate-x-1/2 text-[9px] font-mono font-bold text-[#557A18] dark:text-[#99CC33] bg-white/75 dark:bg-[#001715]/80 px-1 rounded">000&deg; U</span>
            <span class="absolute bottom-1 left-1/2 -translate-x-1/2 text-[9px] font-mono font-bold text-[#005851] dark:text-[#00A99D] bg-white/75 dark:bg-[#001715]/80 px-1 rounded">180&deg; S</span>
            <span class="absolute right-1.5 top-1/2 -translate-y-1/2 text-[9px] font-mono font-bold text-[#005851] dark:text-[#00A99D] bg-white/75 dark:bg-[#001715]/80 px-0.5 rounded">090&deg; T</span>
            <span class="absolute left-1.5 top-1/2 -translate-y-1/2 text-[9px] font-mono font-bold text-[#005851] dark:text-[#00A99D] bg-white/75 dark:bg-[#001715]/80 px-0.5 rounded">270&deg; B</span>
            <span class="absolute top-[14%] right-[13%] text-[7.5px] font-mono font-semibold text-[#007B72]/75 dark:text-[#99DFD6]/65">TL</span>
            <span class="absolute bottom-[14%] right-[13%] text-[7.5px] font-mono font-semibold text-[#007B72]/75 dark:text-[#99DFD6]/65">TG</span>
            <span class="absolute bottom-[14%] left-[13%] text-[7.5px] font-mono font-semibold text-[#007B72]/75 dark:text-[#99DFD6]/65">BD</span>
            <span class="absolute top-[14%] left-[13%] text-[7.5px] font-mono font-semibold text-[#007B72]/75 dark:text-[#99DFD6]/65">BL</span>

            <!-- Synchronized 60fps Conic Phosphor Sweep + Razor Leading Needle -->
            <div
              ref="dialBeamRef"
              class="absolute inset-0 rounded-full pointer-events-none will-change-transform"
              :class="isDark ? 'radar-beam-dark' : 'radar-beam-light'"
            >
              <span class="absolute left-1/2 bottom-1/2 w-[1.5px] h-1/2 -translate-x-1/2 bg-gradient-to-t from-[#00A99D]/40 via-[#7CA622] to-[#d9f99d] dark:from-[#00A99D]/50 dark:via-[#99CC33] dark:to-white shadow-[0_0_8px_rgba(153,204,51,0.85)]"></span>
            </div>

            <!-- Dual Staggered Active Sonar Ping Shockwaves -->
            <template v-if="isSonarPinging">
              <div
                :key="`dial-wave-a-${pingWaveTick}`"
                class="absolute inset-3 rounded-full border-2 border-[#7CA622] dark:border-[#99CC33] sonar-shockwave pointer-events-none"
              ></div>
              <div
                :key="`dial-wave-b-${pingWaveTick}`"
                class="absolute inset-3 rounded-full border border-[#00A99D] dark:border-[#5eead4] sonar-shockwave sonar-shockwave-delay pointer-events-none"
              ></div>
            </template>

            <!-- Interactive Multi-Animated Wilker Blips -->
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
              <!-- Capture Animations When Swept By Radar Beam -->
              <template v-if="blipHits[blip.no]">
                <span
                  :key="`cap-p-${blip.no}-${blipHits[blip.no].seq}`"
                  class="absolute inset-0 rounded-full border-2 border-[#7CA622] dark:border-[#99CC33] blip-capture-wave pointer-events-none"
                ></span>
                <span
                  :key="`cap-s-${blip.no}-${blipHits[blip.no].seq}`"
                  class="absolute inset-0 rounded-full border border-dashed border-[#00A99D] dark:border-[#5eead4] blip-capture-wave-secondary pointer-events-none"
                ></span>
                <span
                  v-if="!blip.is_hq"
                  :key="`cap-tag-${blip.no}-${blipHits[blip.no].seq}`"
                  class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 pointer-events-none"
                >
                  <span class="block px-1.5 py-[1px] rounded bg-[#004640] dark:bg-[#99CC33] text-white dark:text-[#001F1C] text-[8px] font-mono font-bold whitespace-nowrap shadow-sm blip-capture-tag">
                    {{ blip.shortCode }}&middot;{{ blip.distanceKm }}K
                  </span>
                </span>
              </template>

              <!-- Staggered Active-IFF Transponder Echo On PING -->
              <span
                v-if="isSonarPinging"
                :key="`ping-${blip.no}-${pingWaveTick}`"
                class="absolute -inset-1 rounded-full border border-[#00A99D] dark:border-[#99CC33] blip-ping-reply pointer-events-none"
                :style="{ animationDelay: `${Math.min(520, (blip.distanceKm || 0) * 4)}ms` }"
              ></span>

              <!-- Rotating Corner Targeting Reticle When Locked -->
              <span
                v-if="lockedRadarTarget?.no === blip.no"
                class="absolute -inset-2 pointer-events-none radar-lock-reticle"
              >
                <span class="absolute top-0 left-0 w-1.5 h-1.5 border-t border-l border-[#005851] dark:border-[#99CC33]"></span>
                <span class="absolute top-0 right-0 w-1.5 h-1.5 border-t border-r border-[#005851] dark:border-[#99CC33]"></span>
                <span class="absolute bottom-0 left-0 w-1.5 h-1.5 border-b border-l border-[#005851] dark:border-[#99CC33]"></span>
                <span class="absolute bottom-0 right-0 w-1.5 h-1.5 border-b border-r border-[#005851] dark:border-[#99CC33]"></span>
              </span>

              <!-- Ambient Halo -->
              <span
                class="absolute -inset-1 rounded-full transition-all duration-300"
                :class="isBlipHot(blip.no)
                  ? 'bg-[#99CC33]/65 scale-125'
                  : blip.is_hq
                  ? 'bg-[#99CC33]/40'
                  : selectedOffice?.no === blip.no
                  ? 'bg-[#00A99D]/55'
                  : 'bg-[#00A99D]/20'"
              ></span>

              <!-- Blip Core Badge -->
              <span
                class="relative flex items-center justify-center rounded-full font-mono font-black transition-transform group-hover/blip:scale-125 shadow-md"
                :class="[
                  isBlipHot(blip.no) ? 'blip-core-surge' : '',
                  blip.is_hq
                    ? 'w-5 h-5 bg-[#99CC33] text-[#001F1C] text-[8px] ring-2 ring-[#005851] dark:ring-white'
                    : selectedOffice?.no === blip.no
                    ? 'w-4.5 h-4.5 bg-[#00A99D] text-white text-[8px] ring-2 ring-[#005851] dark:ring-[#99CC33]'
                    : isBlipHot(blip.no)
                    ? 'w-4.5 h-4.5 bg-[#005851] dark:bg-[#99CC33] text-white dark:text-[#001F1C] text-[7.5px] ring-2 ring-[#99CC33] dark:ring-white'
                    : 'w-4 h-4 bg-white dark:bg-[#005851] text-[#005851] dark:text-[#99DFD6] text-[7px] border border-[#00A99D]'
                ]"
              >
                {{ blip.shortCode }}
              </span>

              <!-- Pending Verification Signal Dot -->
              <span
                v-if="(blip.pending || 0) > 0 && !blip.is_hq"
                class="absolute -top-0.5 -right-0.5 w-1.5 h-1.5 rounded-full bg-amber-500 ring-1 ring-white dark:ring-[#001715]"
                :title="`${blip.pending} dokumen menunggu verifikasi`"
              ></span>
            </button>
          </div>
        </div>

        <!-- Complex Tactical Dossier & Sweep Intercept Feed -->
        <div class="relative z-10 space-y-2">
          <!-- Moda Filter Pills + Live Sweep Intercept Chip -->
          <div class="flex flex-wrap items-center justify-between gap-1.5 text-[10px] font-mono">
            <div class="flex items-center gap-1">
              <button
                v-for="rf in radarFilterOptions"
                :key="rf.id"
                type="button"
                :aria-pressed="activeRadarFilter === rf.id"
                @click="emit('update:activeRadarFilter', rf.id)"
                class="px-2 py-0.5 rounded border transition-all cursor-pointer"
                :class="activeRadarFilter === rf.id
                  ? 'bg-[#005851] dark:bg-[#00A99D] text-white border-[#005851] dark:border-[#00A99D] font-bold shadow-xs'
                  : 'bg-white dark:bg-[#00221F] text-slate-600 dark:text-slate-300 border-slate-200 dark:border-[#004D44] hover:border-[#00A99D] hover:text-[#005851] dark:hover:text-white'"
              >
                {{ rf.label }}
              </button>
            </div>

            <div
              v-if="lastInterceptedNode"
              class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white/90 dark:bg-[#001715] border border-[#99DFD6] dark:border-[#00A99D]/40 text-[#005851] dark:text-[#99CC33] font-bold tabular-nums"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-[#7CA622] dark:bg-[#99CC33] animate-ping"></span>
              <span>SIG: {{ lastInterceptedNode.shortCode }} &middot; {{ lastInterceptedNode.distanceKm }}K</span>
            </div>
          </div>

          <!-- Target Acquisition Dossier Card -->
          <div
            v-if="lockedRadarTarget && lockedRadarMetrics"
            class="p-2.5 rounded-xl bg-white/95 dark:bg-[#001715]/95 border border-[#99DFD6] dark:border-[#00A99D]/35 shadow-xs space-y-2 text-[10px] font-mono"
          >
            <!-- Top Identity & Live RF Equalizer -->
            <div class="flex items-start justify-between gap-2 pb-1.5 border-b border-slate-100 dark:border-[#003B36]">
              <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                  <Crosshair class="w-3 h-3 text-[#00A99D] dark:text-[#99CC33] shrink-0" />
                  <span class="px-1 py-[1px] rounded bg-[#005851] dark:bg-[#00A99D] text-white text-[9px] font-bold">
                    {{ lockedRadarTarget.shortCode }}
                  </span>
                  <strong class="text-[11px] font-sans font-bold text-[#004640] dark:text-white truncate">
                    {{ lockedRadarTarget.shortTitle }}
                  </strong>
                </div>
                <div class="mt-0.5 text-[9.5px] text-slate-500 dark:text-[#99DFD6]/85 truncate tabular-nums">
                  <span>{{ lockedRadarTarget.moda }}</span>
                  <span class="mx-1">&bull;</span>
                  <span>LAT {{ lockedRadarMetrics.lat.toFixed(4) }}, {{ lockedRadarMetrics.lng.toFixed(4) }}</span>
                </div>
              </div>

              <!-- Animated 6-Bar Signal Spectrum Equalizer -->
              <div class="flex flex-col items-end shrink-0">
                <div class="flex items-end gap-[2px] h-4" aria-hidden="true">
                  <span
                    v-for="(barPct, bIdx) in spectrumBars"
                    :key="`eq-${bIdx}`"
                    class="w-[3px] rounded-t-xs transition-all duration-300"
                    :class="isBlipHot(lockedRadarTarget.no) ? 'bg-[#7CA622] dark:bg-[#99CC33]' : 'bg-[#00A99D]'"
                    :style="{ height: `${barPct}%` }"
                  ></span>
                </div>
                <span class="text-[9px] font-bold text-[#005851] dark:text-[#99CC33] tabular-nums mt-0.5">
                  {{ lockedRadarMetrics.distanceKm }} KM &middot; {{ lockedRadarMetrics.bearingFormatted }} {{ lockedRadarMetrics.sector }}
                </span>
              </div>
            </div>

            <!-- 4-Cell Complex Operational Matrix -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 tabular-nums">
              <div class="p-1.5 rounded-lg bg-[#F4FBF9] dark:bg-[#002320] border border-[#CFECE8] dark:border-[#004D44]">
                <span class="text-[8.5px] text-slate-500 dark:text-slate-400 block uppercase">Dokumen</span>
                <strong class="text-[11px] text-[#004640] dark:text-white">{{ lockedRadarMetrics.totalDocs }}</strong>
                <span class="text-[8.5px] text-[#007B72] dark:text-[#99DFD6] block">{{ lockedRadarMetrics.periodDocs }} / 30h</span>
              </div>
              <div class="p-1.5 rounded-lg bg-[#F4FBF9] dark:bg-[#002320] border border-[#CFECE8] dark:border-[#004D44]">
                <span class="text-[8.5px] text-slate-500 dark:text-slate-400 block uppercase">Armada Ops</span>
                <strong class="text-[11px] text-[#004640] dark:text-white">Ps {{ lockedRadarMetrics.aircraft }}</strong>
                <span class="text-[8.5px] text-[#007B72] dark:text-[#99DFD6] block">Kp {{ lockedRadarMetrics.ship }} kapal</span>
              </div>
              <div class="p-1.5 rounded-lg bg-[#F4FBF9] dark:bg-[#002320] border border-[#CFECE8] dark:border-[#004D44]">
                <span class="text-[8.5px] text-slate-500 dark:text-slate-400 block uppercase">Medis P3K</span>
                <strong class="text-[11px] text-[#004640] dark:text-white">Al {{ lockedRadarMetrics.alat }}</strong>
                <span class="text-[8.5px] text-[#007B72] dark:text-[#99DFD6] block">Ob {{ lockedRadarMetrics.obat }} obat</span>
              </div>
              <div class="p-1.5 rounded-lg bg-[#F4FBF9] dark:bg-[#002320] border border-[#CFECE8] dark:border-[#004D44]">
                <span class="text-[8.5px] text-slate-500 dark:text-slate-400 flex items-center gap-0.5 uppercase">
                  <ShieldCheck class="w-2.5 h-2.5 text-[#00A99D]" />
                  <span>SINKARKES</span>
                </span>
                <strong class="text-[11px] text-[#005851] dark:text-[#99CC33]">
                  {{ lockedRadarMetrics.sinkarkes.compliancePercent !== null ? `${lockedRadarMetrics.sinkarkes.compliancePercent}%` : `${lockedRadarMetrics.approved} OK` }}
                </strong>
                <span class="text-[8.5px] text-[#007B72] dark:text-[#99DFD6] block">
                  {{ lockedRadarMetrics.sinkarkes.filledMonths !== null ? `${lockedRadarMetrics.sinkarkes.filledMonths}/12 bln` : `${lockedRadarMetrics.pending} pending` }}
                </span>
              </div>
            </div>

            <!-- Network Share, Link Diagnostics & Annual SINKARKES Telemetry -->
            <div class="space-y-1 pt-0.5">
              <div class="flex items-center justify-between text-[9px] text-slate-500 dark:text-slate-300 tabular-nums">
                <span>
                  PANGSA BEBAN: <strong class="text-[#005851] dark:text-[#99DFD6]">{{ lockedRadarMetrics.loadSharePct }}%</strong>
                  <span v-if="lockedRadarMetrics.sinkarkes.matched" class="ml-1.5 text-slate-400">
                    &bull; THN: {{ lockedRadarMetrics.sinkarkes.alatAngkut.toLocaleString('id-ID') }} angkut
                  </span>
                </span>
                <span class="text-[#007B72] dark:text-[#99CC33] font-semibold">
                  SIG {{ lockedRadarMetrics.signalStrengthPct }}% &middot; {{ lockedRadarMetrics.linkLatencyMs }}ms
                </span>
              </div>
              <div class="w-full h-1.5 rounded-full bg-slate-100 dark:bg-[#002E29] overflow-hidden">
                <div
                  class="h-full rounded-full bg-gradient-to-r from-[#005851] via-[#00A99D] to-[#99CC33] transition-all duration-500"
                  :style="{ width: `${Math.max(8, lockedRadarMetrics.relativeLoadPct)}%` }"
                ></div>
              </div>
            </div>
          </div>

          <!-- Live Sweep Intercept Log (Clickable Trace History) -->
          <div class="px-2.5 py-1.5 rounded-xl bg-white/80 dark:bg-[#001715]/75 border border-slate-200/80 dark:border-[#004D44] text-[9.5px] font-mono">
            <div class="flex items-center justify-between text-[9px] font-bold text-[#005851] dark:text-[#99DFD6] uppercase mb-1">
              <span class="flex items-center gap-1">
                <Activity class="w-3 h-3 text-[#00A99D] dark:text-[#99CC33]" />
                <span>Log Tangkapan Radar</span>
              </span>
              <span class="text-slate-400 dark:text-slate-400 font-normal">{{ interceptFeed.length ? 'REAL-TIME RX' : 'STANDBY' }}</span>
            </div>
            <div v-if="interceptFeed.length > 0" class="space-y-0.5 tabular-nums">
              <button
                v-for="ev in interceptFeed"
                :key="ev.key"
                type="button"
                @click="emit('select-office', bkkMapMarkers.find((m) => m.no === ev.no) || lockedRadarTarget)"
                class="w-full flex items-center justify-between gap-1.5 px-1.5 py-0.5 rounded hover:bg-[#E6F7F5] dark:hover:bg-[#003B36] text-left transition-colors cursor-pointer"
              >
                <span class="flex items-center gap-1.5 min-w-0 truncate">
                  <span class="text-slate-400">[{{ ev.timeStr }}]</span>
                  <strong class="text-[#005851] dark:text-[#99CC33]">{{ ev.shortCode }}</strong>
                  <span class="text-slate-600 dark:text-slate-300 truncate">{{ ev.bearingFormatted }} {{ ev.sector }} &middot; {{ ev.distanceKm }} KM</span>
                </span>
                <span class="text-[#007B72] dark:text-[#5eead4] shrink-0 font-semibold">{{ ev.totalDocs }} DOK</span>
              </button>
            </div>
            <p v-else class="text-[9.5px] text-slate-400 dark:text-slate-400 italic py-0.5">
              Memindai sektor 000&deg;&ndash;359&deg; untuk menangkap respons transponder wilker...
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
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

@keyframes blip-capture-wave-anim {
  0% {
    transform: scale(0.8);
    opacity: 0.95;
  }
  100% {
    transform: scale(3.2);
    opacity: 0;
  }
}

@keyframes blip-capture-wave-sec-anim {
  0% {
    transform: scale(0.7);
    opacity: 0;
  }
  20% {
    opacity: 0.85;
  }
  100% {
    transform: scale(2.6);
    opacity: 0;
  }
}

@keyframes blip-capture-tag-anim {
  0% {
    transform: translateY(3px) scale(0.85);
    opacity: 0;
  }
  18% {
    transform: translateY(0) scale(1);
    opacity: 1;
  }
  80% {
    opacity: 1;
  }
  100% {
    transform: translateY(-2px) scale(0.95);
    opacity: 0;
  }
}

@keyframes blip-core-surge-anim {
  0% {
    transform: scale(1);
  }
  30% {
    transform: scale(1.3);
  }
  100% {
    transform: scale(1);
  }
}

@keyframes radar-vector-dash {
  to {
    stroke-dashoffset: -12;
  }
}

@keyframes radar-packet-shoot-anim {
  0% {
    stroke-dasharray: 16 84;
    stroke-dashoffset: 16;
    opacity: 1;
  }
  100% {
    stroke-dasharray: 16 84;
    stroke-dashoffset: -84;
    opacity: 0.25;
  }
}

@keyframes radar-lock-spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

@keyframes radar-panel-scan {
  0% {
    transform: translateY(-100%);
    opacity: 0;
  }
  20% {
    opacity: 0.35;
  }
  80% {
    opacity: 0.35;
  }
  100% {
    transform: translateY(600%);
    opacity: 0;
  }
}

.radar-dial-light {
  background: radial-gradient(circle at center, #ffffff 0%, #e6f7f5 68%, #cfece8 100%);
}

.radar-dial-dark {
  background: radial-gradient(circle at center, #002320 0%, #001715 70%, #000f0e 100%);
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

.sonar-shockwave-delay {
  animation-delay: 240ms;
}

.blip-capture-wave {
  animation: blip-capture-wave-anim 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.blip-capture-wave-secondary {
  animation: blip-capture-wave-sec-anim 1.3s cubic-bezier(0.16, 1, 0.3, 1) 140ms forwards;
}

.blip-capture-tag {
  animation: blip-capture-tag-anim 1.25s ease-out forwards;
}

.blip-core-surge {
  animation: blip-core-surge-anim 0.65s cubic-bezier(0.22, 1, 0.36, 1);
}

.blip-ping-reply {
  animation: blip-capture-wave-anim 1.1s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.radar-vector-flow {
  animation: radar-vector-dash 1.2s linear infinite;
}

.radar-packet-shoot {
  animation: radar-packet-shoot-anim 0.85s ease-out forwards;
}

.radar-lock-reticle {
  animation: radar-lock-spin 12s linear infinite;
}

.radar-lock-svg-spin {
  transform-box: fill-box;
  transform-origin: center;
  animation: radar-lock-spin 10s linear infinite;
}

.radar-range-text {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 2.8px;
  font-weight: 700;
}

.radar-panel-scanline {
  position: absolute;
  left: 0;
  right: 0;
  height: 44px;
  background: linear-gradient(180deg, transparent, rgba(0, 169, 157, 0.08), transparent);
  animation: radar-panel-scan 7s linear infinite;
}
</style>
