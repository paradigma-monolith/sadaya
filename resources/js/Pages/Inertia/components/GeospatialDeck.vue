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
const wingAzmRef = ref(null);
const wingBarRef = ref(null);
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
const lastInterceptedNode = ref(null);
const pingWaveTick = ref(0);
const pendingTimers = new Set();
let hitSeqCounter = 0;
let sweepRafId = null;
let currentSweepDeg = 0;
let lastRafTs = 0;
let lastHudTextTs = 0;

const polarDegreeTicks = Array.from({ length: 72 }, (_, idx) => {
  const deg = idx * 5;
  const rad = (deg * Math.PI) / 180;
  const isCardinal = deg % 90 === 0;
  const isMajor = deg % 30 === 0;
  const rOuter = 48.8;
  const rInner = isCardinal ? 44.0 : (isMajor ? 45.2 : 47.0);
  return {
    deg,
    isCardinal,
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

const wingTransponders = computed(() => {
  return (props.radarBlips || [])
    .filter((b) => !b.is_hq)
    .slice()
    .sort((a, b) => Number(a.bearingDeg || 0) - Number(b.bearingDeg || 0))
    .slice(0, 6);
});

const networkStats = computed(() => {
  const markers = props.bkkMapMarkers || [];
  let air = 0;
  let sea = 0;
  let pending = 0;
  for (const m of markers) {
    const ml = `${m?.moda || ''} ${m?.type || ''}`.toLowerCase();
    if (m?.is_hq || ml.includes('udara') || ml.includes('bandara')) air += 1;
    else if (ml.includes('laut') || ml.includes('pelabuhan')) sea += 1;
    pending += Number(m?.pending || 0);
  }
  return {
    totalNodes: markers.length || 0,
    airCount: air,
    seaCount: sea,
    totalPending: pending,
  };
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
  if (wingBarRef.value) {
    wingBarRef.value.style.transform = `scaleX(${Math.max(0.02, nextDeg / 360).toFixed(3)})`;
  }

  if (ts - lastHudTextTs >= 65) {
    lastHudTextTs = ts;
    const rounded = Math.floor(nextDeg);
    const padAzm = String(rounded).padStart(3, '0');
    const sec = getCompassSector(rounded);
    if (dialAzmRef.value) {
      dialAzmRef.value.textContent = `${padAzm}° ${sec}`;
    }
    if (wingAzmRef.value) {
      wingAzmRef.value.textContent = `${padAzm}° ${sec}`;
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
      <!-- Large WebGL Map Area -->
      <div
        ref="mapViewportRef"
        class="lg:col-span-7 2xl:col-span-8 relative min-w-0 bg-slate-100 dark:bg-[#00221F] overflow-hidden h-[320px] sm:h-[380px] lg:h-auto lg:min-h-[470px]"
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

      <!-- Side Cockpit: 3-Zone Sci-Fi Holographic Polar Radar Command -->
      <div
        class="lg:col-span-5 2xl:col-span-4 radar-holo-deck text-slate-800 dark:text-white p-3 sm:p-3.5 flex flex-col justify-between gap-2.5 relative overflow-hidden border-t lg:border-t-0 lg:border-l border-[#99DFD6]/80 dark:border-[#00A99D]/35"
        :class="isDark ? 'radar-deck-dark' : 'radar-deck-light'"
      >
        <!-- Holographic Micro-Grid & Laser Scanline -->
        <div class="radar-holo-grid pointer-events-none"></div>
        <div class="radar-panel-scanline pointer-events-none"></div>

        <!-- Radar Cockpit Header -->
        <div class="relative z-10 flex items-start justify-between gap-2 pb-1.5 border-b border-[#00A99D]/20 dark:border-[#00A99D]/30">
          <div class="min-w-0">
            <div class="flex items-center gap-1.5">
              <span class="relative flex h-2 w-2 shrink-0">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#7CA622] dark:bg-[#99CC33] opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-[#00A99D] dark:bg-[#99CC33]"></span>
              </span>
              <h3 class="text-xs font-extrabold tracking-wide text-[#004640] dark:text-white uppercase truncate">
                Radar Jaringan Wilker
              </h3>
              <span
                class="sci-fi-chip px-1.5 py-[1px] text-[8.5px] font-mono font-bold tracking-wider border shrink-0"
                :class="radarSweepSpeed === 'fast'
                  ? 'bg-[#99CC33]/20 text-[#4c6f10] dark:text-[#99CC33] border-[#99CC33]/60'
                  : 'bg-[#00A99D]/12 text-[#005851] dark:text-[#5eead4] border-[#00A99D]/45'"
              >
                {{ isSonarPinging ? 'IFF//PING' : (radarSweepSpeed === 'fast' ? '2x//HYPER' : 'LIVE//SCAN') }}
              </span>
            </div>
            <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[9.5px] text-[#007B72] dark:text-[#99DFD6] font-mono tabular-nums tracking-tight">
              <span class="text-[#00A99D] dark:text-[#5eead4] font-bold">SYS//BKK-07</span>
              <span class="opacity-35">|</span>
              <span>RNG 150K</span>
              <span class="opacity-35">&bull;</span>
              <span>AZM <strong ref="dialAzmRef" class="text-[#004640] dark:text-[#99CC33]">000&deg; U</strong></span>
              <span class="opacity-35">&bull;</span>
              <span>ND {{ radarBlips.length }}/{{ bkkMapMarkers.length }}</span>
            </div>
          </div>

          <div class="flex items-center gap-1 shrink-0">
            <button
              type="button"
              @click="emit('update:radarSweepSpeed', radarSweepSpeed === 'normal' ? 'fast' : 'normal')"
              class="sci-fi-chip px-2 py-0.5 text-[9.5px] font-mono font-bold border transition-all cursor-pointer"
              :class="radarSweepSpeed === 'fast'
                ? 'bg-[#99CC33] text-[#002B27] border-[#99CC33] shadow-[0_0_10px_rgba(153,204,51,0.4)]'
                : 'bg-white/90 dark:bg-[#002E29] text-[#005851] dark:text-[#99DFD6] border-[#00A99D]/45 hover:bg-[#E6F7F5] dark:hover:text-white'"
              title="Ubah kecepatan putaran radar"
            >
              {{ radarSweepSpeed === 'fast' ? '2x FAST' : '1x SCAN' }}
            </button>
            <button
              type="button"
              @click="emit('trigger-ping')"
              class="sci-fi-chip px-2 py-0.5 text-[9.5px] font-mono font-bold bg-[#005851] dark:bg-[#007B72] hover:bg-[#00A99D] dark:hover:bg-[#00A99D] text-white border border-[#00A99D]/70 dark:border-[#5eead4]/60 shadow-[0_0_8px_rgba(0,169,157,0.28)] transition-all cursor-pointer"
              title="Pancarkan sinyal IFF Sonar ke seluruh wilker"
            >
              PING
            </button>
          </div>
        </div>

        <!-- 3-Zone Sci-Fi Holographic Stage -->
        <div class="radar-cockpit-stage-wrap relative z-10 my-auto">
          <!-- Optical HUD Conduit Traces Linking Left Wing <-> Gyro Gimbal <-> Right Wing -->
          <svg viewBox="0 0 400 200" preserveAspectRatio="none" class="radar-hud-conduit pointer-events-none" aria-hidden="true">
            <line x1="65" y1="62" x2="145" y2="62" stroke="rgba(0,169,157,0.28)" stroke-width="0.7" stroke-dasharray="3 2" />
            <line x1="65" y1="138" x2="145" y2="138" stroke="rgba(0,169,157,0.28)" stroke-width="0.7" stroke-dasharray="3 2" />
            <line x1="255" y1="62" x2="335" y2="62" stroke="rgba(0,169,157,0.28)" stroke-width="0.7" stroke-dasharray="3 2" />
            <line x1="255" y1="138" x2="335" y2="138" stroke="rgba(0,169,157,0.28)" stroke-width="0.7" stroke-dasharray="3 2" />
            <circle cx="110" cy="62" r="2.2" fill="#00A99D" opacity="0.65" />
            <circle cx="290" cy="138" r="2.2" fill="#99CC33" opacity="0.7" />
          </svg>

          <div class="radar-cockpit-stage">
            <!-- LEFT WING: Sweep Waveform, Range Vector & Moda Sensor -->
            <div class="radar-wing-left flex flex-col justify-between gap-1.5 text-[9px] font-mono tabular-nums">
              <!-- SYS//01: Live Azimuth & Carrier Waveform -->
              <div class="sci-fi-frame">
                <div class="sci-fi-frame-inner bg-white/92 dark:bg-[#001614]/92 px-2 py-1.5 space-y-1">
                  <div class="flex items-center justify-between gap-1 text-[7.5px] font-bold tracking-wider text-[#007B72] dark:text-[#5eead4] uppercase">
                    <span>SYS//01 AZM</span>
                    <span class="px-1 bg-[#E6F7F5] dark:bg-[#003B36] text-[#005851] dark:text-[#99CC33] rounded-xs">
                      {{ radarSweepSpeed === 'fast' ? '30R' : '15R' }}
                    </span>
                  </div>
                  <div ref="wingAzmRef" class="text-[11px] font-black text-[#004640] dark:text-[#99CC33] leading-none tracking-tight">
                    000&deg; U
                  </div>

                  <!-- Live Scrolling Dual-Harmonic Carrier Waveform -->
                  <div class="relative w-full h-3.5 overflow-hidden rounded-xs bg-[#F0FAF8] dark:bg-[#002420] border border-[#00A99D]/20" aria-hidden="true">
                    <div class="sci-fi-wave-track h-full flex">
                      <svg viewBox="0 0 120 20" preserveAspectRatio="none" class="w-1/2 h-full shrink-0">
                        <path
                          d="M0 10 Q 7.5 2, 15 10 T 30 10 T 45 10 T 60 10 T 75 10 T 90 10 T 105 10 T 120 10"
                          fill="none"
                          :stroke="isDark ? '#5eead4' : '#00A99D'"
                          stroke-width="1.3"
                        />
                        <path
                          d="M0 10 Q 10 17, 20 10 T 40 10 T 60 10 T 80 10 T 100 10 T 120 10"
                          fill="none"
                          :stroke="isDark ? 'rgba(153,204,51,0.65)' : 'rgba(124,166,34,0.65)'"
                          stroke-width="0.9"
                        />
                      </svg>
                      <svg viewBox="0 0 120 20" preserveAspectRatio="none" class="w-1/2 h-full shrink-0">
                        <path
                          d="M0 10 Q 7.5 2, 15 10 T 30 10 T 45 10 T 60 10 T 75 10 T 90 10 T 105 10 T 120 10"
                          fill="none"
                          :stroke="isDark ? '#5eead4' : '#00A99D'"
                          stroke-width="1.3"
                        />
                        <path
                          d="M0 10 Q 10 17, 20 10 T 40 10 T 60 10 T 80 10 T 100 10 T 120 10"
                          fill="none"
                          :stroke="isDark ? 'rgba(153,204,51,0.65)' : 'rgba(124,166,34,0.65)'"
                          stroke-width="0.9"
                        />
                      </svg>
                    </div>
                  </div>

                  <div class="w-full h-1 rounded-xs bg-slate-200/80 dark:bg-[#002E29] overflow-hidden">
                    <div
                      ref="wingBarRef"
                      class="w-full h-full origin-left bg-gradient-to-r from-[#005851] via-[#00A99D] to-[#99CC33] will-change-transform"
                    ></div>
                  </div>
                </div>
              </div>

              <!-- RNG//02: Target Vector Distance & Link Quality -->
              <div v-if="lockedRadarMetrics" class="sci-fi-frame">
                <div class="sci-fi-frame-inner bg-white/92 dark:bg-[#001614]/92 px-2 py-1.5 space-y-1">
                  <div class="flex items-center justify-between text-[7.5px] font-bold tracking-wider text-[#007B72] dark:text-[#5eead4] uppercase">
                    <span>RNG//02</span>
                    <strong class="text-[#004640] dark:text-[#99CC33]">{{ lockedRadarTarget?.shortCode || 'BDG' }}</strong>
                  </div>
                  <div class="flex items-center justify-between text-[8.5px]">
                    <span class="text-slate-500 dark:text-slate-400">JARAK</span>
                    <strong class="text-[#004640] dark:text-white">{{ lockedRadarMetrics.distanceKm }} KM</strong>
                  </div>
                  <div class="w-full h-1 rounded-xs bg-slate-200/80 dark:bg-[#002E29] overflow-hidden">
                    <div
                      class="h-full bg-gradient-to-r from-[#00A99D] to-[#99CC33] transition-all duration-300"
                      :style="{ width: `${Math.max(8, Math.min(100, Math.round((lockedRadarMetrics.distanceKm / 150) * 100)))}%` }"
                    ></div>
                  </div>
                  <div class="flex items-center justify-between text-[8px] pt-0.5">
                    <span class="text-slate-500 dark:text-slate-400">SIG</span>
                    <strong class="text-[#007B72] dark:text-[#99CC33]">
                      {{ lockedRadarMetrics.signalStrengthPct }}% &middot; {{ lockedRadarMetrics.linkLatencyMs }}ms
                    </strong>
                  </div>
                </div>
              </div>

              <!-- MOD//03: Sensor Sector Composition -->
              <div class="sci-fi-frame">
                <div class="sci-fi-frame-inner bg-white/92 dark:bg-[#001614]/92 px-2 py-1.5 space-y-1 text-[8px]">
                  <div class="text-[7.5px] font-bold tracking-wider text-[#007B72] dark:text-[#5eead4] uppercase">
                    MOD//03 GRID
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400">UDARA</span>
                    <strong class="text-[#005851] dark:text-[#5eead4]">{{ networkStats.airCount }} ND</strong>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400">LAUT</span>
                    <strong class="text-[#005851] dark:text-[#99DFD6]">{{ networkStats.seaCount }} ND</strong>
                  </div>
                  <div class="flex items-center justify-between pt-0.5 border-t border-slate-100 dark:border-[#003B36]">
                    <span class="text-slate-500 dark:text-slate-400">VERIF</span>
                    <strong :class="networkStats.totalPending > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-[#007B72] dark:text-[#99CC33]'">
                      {{ networkStats.totalPending > 0 ? `${networkStats.totalPending} ANTRE` : 'OK 0' }}
                    </strong>
                  </div>
                </div>
              </div>
            </div>

            <!-- CENTER: Holographic Multi-Gimbal Polar Radar Scope -->
            <div class="radar-center-scope">
              <div class="relative p-1.5">
                <!-- Outer Glowing Sci-Fi Targeting Brackets -->
                <span class="absolute top-0 left-0 w-3.5 h-3.5 border-t-2 border-l-2 border-[#00A99D] dark:border-[#99CC33] pointer-events-none"></span>
                <span class="absolute top-0 right-0 w-3.5 h-3.5 border-t-2 border-r-2 border-[#00A99D] dark:border-[#99CC33] pointer-events-none"></span>
                <span class="absolute bottom-0 left-0 w-3.5 h-3.5 border-b-2 border-l-2 border-[#00A99D] dark:border-[#99CC33] pointer-events-none"></span>
                <span class="absolute bottom-0 right-0 w-3.5 h-3.5 border-b-2 border-r-2 border-[#00A99D] dark:border-[#99CC33] pointer-events-none"></span>

                <!-- Outer Gyroscopic Azimuth Bezel -->
                <div
                  class="radar-bezel-dial-size relative aspect-square shrink-0 rounded-full border border-[#00A99D]/65 bg-[#E4F6F3]/85 dark:bg-[#001311]/95 shadow-[0_0_32px_rgba(0,169,157,0.2)] dark:shadow-[0_0_46px_rgba(0,169,157,0.34)] flex items-center justify-center"
                >
                  <!-- Multi-Layer Counter-Rotating Orbital Gimbal Rings -->
                  <svg viewBox="0 0 100 100" class="absolute inset-0 w-full h-full pointer-events-none">
                    <circle
                      cx="50"
                      cy="50"
                      r="49.1"
                      fill="none"
                      :stroke="isDark ? 'rgba(94,234,212,0.45)' : 'rgba(0,169,157,0.48)'"
                      stroke-width="0.45"
                      stroke-dasharray="4.2 2.2 1.1 2.2"
                      class="radar-outer-bezel-spin"
                    />
                    <circle
                      cx="50"
                      cy="50"
                      r="46.2"
                      fill="none"
                      :stroke="isDark ? 'rgba(153,204,51,0.52)' : 'rgba(124,166,34,0.48)'"
                      stroke-width="0.55"
                      stroke-dasharray="14 22.3"
                      class="radar-inner-gimbal-spin"
                    />
                  </svg>

                  <!-- Cardinal & Diagonal Compass Markers Outside Plot Glass -->
                  <span class="absolute top-[2px] left-1/2 -translate-x-1/2 text-[8.5px] font-mono font-extrabold text-[#557A18] dark:text-[#99CC33] leading-none">000&deg;U</span>
                  <span class="absolute bottom-[2px] left-1/2 -translate-x-1/2 text-[8.5px] font-mono font-extrabold text-[#005851] dark:text-[#5eead4] leading-none">180&deg;S</span>
                  <span class="absolute right-[3px] top-1/2 -translate-y-1/2 text-[8.5px] font-mono font-extrabold text-[#005851] dark:text-[#5eead4] leading-none">090T</span>
                  <span class="absolute left-[3px] top-1/2 -translate-y-1/2 text-[8.5px] font-mono font-extrabold text-[#005851] dark:text-[#5eead4] leading-none">270B</span>
                  <span class="absolute top-[13%] right-[13%] text-[7px] font-mono font-bold text-[#007B72]/85 dark:text-[#99DFD6]/75">TL</span>
                  <span class="absolute bottom-[13%] right-[13%] text-[7px] font-mono font-bold text-[#007B72]/85 dark:text-[#99DFD6]/75">TG</span>
                  <span class="absolute bottom-[13%] left-[13%] text-[7px] font-mono font-bold text-[#007B72]/85 dark:text-[#99DFD6]/75">BD</span>
                  <span class="absolute top-[13%] left-[13%] text-[7px] font-mono font-bold text-[#007B72]/85 dark:text-[#99DFD6]/75">BL</span>

                  <!-- Inner Holographic Polar Glass Scope -->
                  <div
                    class="radar-dial-surface absolute inset-[16px] sm:inset-[17px] rounded-full border border-[#00A99D]/80 overflow-hidden flex items-center justify-center"
                    :class="isDark ? 'radar-dial-dark' : 'radar-dial-light'"
                  >
                    <!-- Precision SVG Polar Grid, Holographic Target Lasers & Vector Links -->
                    <svg viewBox="0 0 100 100" class="absolute inset-0 w-full h-full pointer-events-none">
                      <!-- 72 Degree Tick Ring (5° / 30° / 90°) -->
                      <line
                        v-for="tick in polarDegreeTicks"
                        :key="`tick-${tick.deg}`"
                        :x1="tick.x1"
                        :y1="tick.y1"
                        :x2="tick.x2"
                        :y2="tick.y2"
                        :stroke="isDark ? (tick.isCardinal ? 'rgba(153, 204, 51, 0.78)' : tick.isMajor ? 'rgba(153, 204, 51, 0.44)' : 'rgba(0, 169, 157, 0.24)') : (tick.isCardinal ? 'rgba(0, 88, 81, 0.7)' : tick.isMajor ? 'rgba(0, 88, 81, 0.4)' : 'rgba(0, 169, 157, 0.26)')"
                        :stroke-width="tick.isCardinal ? '0.62' : tick.isMajor ? '0.4' : '0.22'"
                      />

                      <!-- Calibrated Geodesic Range Rings (50 / 100 / 150 KM) -->
                      <circle cx="50" cy="50" r="15" fill="none" :stroke="isDark ? 'rgba(0,169,157,0.34)' : 'rgba(0,88,81,0.28)'" stroke-width="0.38" />
                      <circle cx="50" cy="50" r="30" fill="none" :stroke="isDark ? 'rgba(0,169,157,0.4)' : 'rgba(0,169,157,0.45)'" stroke-width="0.38" stroke-dasharray="1.4 1.2" />
                      <circle cx="50" cy="50" r="44" fill="none" :stroke="isDark ? 'rgba(0,169,157,0.34)' : 'rgba(0,88,81,0.32)'" stroke-width="0.38" />

                      <!-- Center Ion Beacon Pulse Ring -->
                      <circle
                        cx="50"
                        cy="50"
                        r="8"
                        fill="none"
                        :stroke="isDark ? 'rgba(153,204,51,0.65)' : 'rgba(0,169,157,0.5)'"
                        stroke-width="0.45"
                        class="radar-hub-ion-pulse"
                      />

                      <!-- Cardinal & Diagonal Axes -->
                      <line x1="6" y1="50" x2="94" y2="50" :stroke="isDark ? 'rgba(0,169,157,0.26)' : 'rgba(0,169,157,0.3)'" stroke-width="0.3" />
                      <line x1="50" y1="6" x2="50" y2="94" :stroke="isDark ? 'rgba(0,169,157,0.26)' : 'rgba(0,169,157,0.3)'" stroke-width="0.3" />
                      <line x1="18.8" y1="18.8" x2="81.2" y2="81.2" :stroke="isDark ? 'rgba(0,169,157,0.16)' : 'rgba(0,169,157,0.18)'" stroke-width="0.25" />
                      <line x1="81.2" y1="18.8" x2="18.8" y2="81.2" :stroke="isDark ? 'rgba(0,169,157,0.16)' : 'rgba(0,169,157,0.18)'" stroke-width="0.25" />

                      <!-- Laser Axis Lock Crosshairs Intersecting Locked Target -->
                      <template v-if="lockedRadarMeta && !lockedRadarMeta.is_hq">
                        <line
                          x1="4"
                          :y1="lockedRadarMeta.y"
                          x2="96"
                          :y2="lockedRadarMeta.y"
                          :stroke="isDark ? 'rgba(153,204,51,0.35)' : 'rgba(0,123,114,0.3)'"
                          stroke-width="0.28"
                          stroke-dasharray="1.2 1.6"
                        />
                        <line
                          :x1="lockedRadarMeta.x"
                          y1="4"
                          :x2="lockedRadarMeta.x"
                          y2="96"
                          :stroke="isDark ? 'rgba(153,204,51,0.35)' : 'rgba(0,123,114,0.3)'"
                          stroke-width="0.28"
                          stroke-dasharray="1.2 1.6"
                        />
                      </template>

                      <!-- Range Micro Annotations -->
                      <text x="51.2" y="34.2" class="radar-range-text" :fill="isDark ? 'rgba(153,223,214,0.65)' : 'rgba(0,88,81,0.65)'">50K</text>
                      <text x="51.2" y="19.2" class="radar-range-text" :fill="isDark ? 'rgba(153,223,214,0.65)' : 'rgba(0,88,81,0.65)'">100K</text>

                      <!-- Dynamic Vector Telemetry Links (Hub BDG -> Wilkers) -->
                      <g v-for="blip in radarBlips" :key="`vec-${blip.id}`">
                        <template v-if="!blip.is_hq">
                          <line
                            x1="50"
                            y1="50"
                            :x2="blip.x"
                            :y2="blip.y"
                            :stroke="isBlipHot(blip.no) || lockedRadarTarget?.no === blip.no
                              ? (isDark ? 'rgba(153, 204, 51, 0.9)' : 'rgba(0, 123, 114, 0.85)')
                              : (isDark ? 'rgba(0, 169, 157, 0.24)' : 'rgba(0, 169, 157, 0.28)')"
                            :stroke-width="isBlipHot(blip.no) ? '0.72' : (lockedRadarTarget?.no === blip.no ? '0.55' : '0.28')"
                            :stroke-dasharray="isBlipHot(blip.no) || lockedRadarTarget?.no === blip.no ? '1.8 1.2' : '1.2 1.8'"
                            :class="isBlipHot(blip.no) || lockedRadarTarget?.no === blip.no ? 'radar-vector-flow' : ''"
                          />
                          <line
                            v-if="isBlipHot(blip.no) || isSonarPinging"
                            x1="50"
                            y1="50"
                            :x2="blip.x"
                            :y2="blip.y"
                            pathLength="100"
                            :stroke="isDark ? '#99CC33' : '#65A30D'"
                            stroke-width="1.0"
                            stroke-linecap="round"
                            class="radar-packet-shoot"
                          />
                        </template>
                      </g>

                      <!-- Dual Counter-Rotating Sci-Fi Target Lock Rings -->
                      <g v-if="lockedRadarMeta">
                        <circle
                          :cx="lockedRadarMeta.x"
                          :cy="lockedRadarMeta.y"
                          r="6.4"
                          fill="none"
                          :stroke="isDark ? '#99CC33' : '#005851'"
                          stroke-width="0.5"
                          stroke-dasharray="2.4 1.6"
                          class="radar-lock-svg-spin"
                        />
                        <rect
                          :x="lockedRadarMeta.x - 4.4"
                          :y="lockedRadarMeta.y - 4.4"
                          width="8.8"
                          height="8.8"
                          fill="none"
                          :stroke="isDark ? 'rgba(94,234,212,0.75)' : 'rgba(0,169,157,0.75)'"
                          stroke-width="0.4"
                          stroke-dasharray="2.2 6.6"
                          class="radar-lock-svg-ccw"
                        />
                      </g>
                    </svg>

                    <!-- Synchronized 60fps Conic Phosphor Sweep + Plasma Laser Needle -->
                    <div
                      ref="dialBeamRef"
                      class="absolute inset-0 rounded-full pointer-events-none will-change-transform"
                      :class="isDark ? 'radar-beam-dark' : 'radar-beam-light'"
                    >
                      <span class="absolute left-1/2 bottom-1/2 w-[2px] h-1/2 -translate-x-1/2 bg-gradient-to-t from-[#00A99D]/45 via-[#7CA622] to-[#ecfccb] dark:from-[#00A99D]/55 dark:via-[#99CC33] dark:to-white shadow-[0_0_12px_rgba(153,204,51,0.95)]"></span>
                    </div>

                    <!-- Dual Staggered Active Sonar Ping Shockwaves -->
                    <template v-if="isSonarPinging">
                      <div
                        :key="`dial-wave-a-${pingWaveTick}`"
                        class="absolute inset-2 rounded-full border-2 border-[#7CA622] dark:border-[#99CC33] sonar-shockwave pointer-events-none"
                      ></div>
                      <div
                        :key="`dial-wave-b-${pingWaveTick}`"
                        class="absolute inset-2 rounded-full border border-[#00A99D] dark:border-[#5eead4] sonar-shockwave sonar-shockwave-delay pointer-events-none"
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
                        <!-- Single Non-Overlapping Sci-Fi Callout Tag -->
                        <span
                          v-if="!blip.is_hq && (hoveredRadarBlip?.no === blip.no || lastInterceptedNode?.no === blip.no)"
                          :key="`cap-tag-${blip.no}-${blipHits[blip.no].seq}`"
                          class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 pointer-events-none z-30"
                        >
                          <span class="sci-fi-chip block px-1.5 py-[1px] bg-[#004640] dark:bg-[#99CC33] text-white dark:text-[#001F1C] border border-[#99CC33]/70 text-[7.5px] font-mono font-bold whitespace-nowrap shadow-[0_0_8px_rgba(0,169,157,0.45)] blip-capture-tag">
                            {{ blip.shortCode }}//{{ blip.distanceKm }}K
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
                          ? 'bg-[#99CC33]/70 scale-125'
                          : blip.is_hq
                          ? 'bg-[#99CC33]/45'
                          : selectedOffice?.no === blip.no
                          ? 'bg-[#00A99D]/60'
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
              </div>
            </div>

            <!-- RIGHT WING: Clockwise Live Transponder RX Array & Target Status -->
            <div class="radar-wing-right flex flex-col justify-between gap-1.5 text-[9px] font-mono tabular-nums">
              <div class="sci-fi-frame flex-1 flex flex-col">
                <div class="sci-fi-frame-inner bg-white/92 dark:bg-[#001614]/92 px-2 py-1.5 flex-1 flex flex-col justify-start gap-1.5">
                  <div class="flex items-center justify-between pb-1 border-b border-[#00A99D]/20 dark:border-[#00A99D]/25 text-[7.5px] font-bold tracking-wider text-[#007B72] dark:text-[#5eead4] uppercase">
                    <span>RX//ARRAY</span>
                    <span class="text-[#005851] dark:text-[#99CC33]">{{ wingTransponders.length }} CH</span>
                  </div>
                  <div class="space-y-1">
                    <button
                      v-for="node in wingTransponders"
                      :key="`wing-rx-${node.id}`"
                      type="button"
                      @click="emit('select-office', node)"
                      @mouseenter="hoveredRadarBlip = node"
                      @mouseleave="hoveredRadarBlip = null"
                      class="w-full flex items-center justify-between gap-1 px-1.5 py-[3px] rounded-xs border text-left transition-all cursor-pointer"
                      :class="isBlipHot(node.no)
                        ? 'bg-[#99CC33]/24 dark:bg-[#99CC33]/22 border-[#7CA622] dark:border-[#99CC33] text-[#004640] dark:text-white shadow-[0_0_8px_rgba(153,204,51,0.3)]'
                        : selectedOffice?.no === node.no
                        ? 'bg-[#00A99D]/18 border-[#00A99D] text-[#004640] dark:text-white font-bold'
                        : 'bg-slate-50/80 dark:bg-[#002320]/75 border-slate-200/75 dark:border-[#003B36] text-slate-600 dark:text-slate-300 hover:border-[#00A99D]/75'"
                      :title="`${node.shortTitle} (${node.bearingDeg}° · ${node.distanceKm} KM)`"
                    >
                      <span class="flex items-center gap-1 min-w-0">
                        <span
                          class="w-1.5 h-1.5 rounded-full shrink-0"
                          :class="isBlipHot(node.no)
                            ? 'bg-[#7CA622] dark:bg-[#99CC33] animate-ping'
                            : selectedOffice?.no === node.no
                            ? 'bg-[#00A99D]'
                            : 'bg-slate-300 dark:bg-slate-600'"
                        ></span>
                        <strong class="text-[8.5px] font-bold truncate">{{ node.shortCode }}</strong>
                      </span>

                      <span class="flex items-center gap-1 shrink-0">
                        <!-- 3-Bar Segmented Micro Link Strength -->
                        <span class="flex items-end gap-[1px] h-2" aria-hidden="true">
                          <span class="w-[1.5px] h-1 bg-[#00A99D]"></span>
                          <span class="w-[1.5px] h-1.5" :class="Number(node.distanceKm || 0) <= 115 ? 'bg-[#00A99D]' : 'bg-slate-300 dark:bg-slate-600'"></span>
                          <span class="w-[1.5px] h-2" :class="Number(node.distanceKm || 0) <= 85 ? (isBlipHot(node.no) ? 'bg-[#7CA622] dark:bg-[#99CC33]' : 'bg-[#00A99D]') : 'bg-slate-300 dark:bg-slate-600'"></span>
                        </span>
                        <span class="text-[7.5px] text-[#007B72] dark:text-[#99DFD6]">
                          {{ node.distanceKm }}K
                        </span>
                      </span>
                    </button>
                  </div>
                  <div class="mt-auto pt-1 border-t border-[#00A99D]/15 dark:border-[#00A99D]/25 flex items-center justify-between text-[7px] font-bold tracking-wider uppercase">
                    <span class="text-slate-400 dark:text-[#99DFD6]/70">IFF//RX</span>
                    <span class="text-[#007B72] dark:text-[#99CC33] truncate">
                      {{ lastInterceptedNode ? `${lastInterceptedNode.shortCode} SYNC` : 'STANDBY' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Quick Target Status Cell -->
              <div v-if="lockedRadarMetrics" class="sci-fi-frame">
                <div class="sci-fi-frame-inner bg-white/92 dark:bg-[#001614]/92 px-2 py-1.5 flex items-center justify-between gap-1 text-[8px]">
                  <span class="text-slate-500 dark:text-slate-400">
                    DOK <strong class="text-[#004640] dark:text-white">{{ lockedRadarMetrics.totalDocs }}</strong>
                  </span>
                  <span class="text-[#007B72] dark:text-[#99CC33] font-bold">
                    {{ lockedRadarMetrics.sinkarkes.compliancePercent !== null ? `SK ${lockedRadarMetrics.sinkarkes.compliancePercent}%` : `${lockedRadarMetrics.approved} OK` }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Radar Moda Filter & Sci-Fi Target Lock Footer -->
        <div class="relative z-10 space-y-2">
          <div class="flex flex-wrap items-center justify-between gap-1.5 text-[10px] font-mono">
            <div class="flex items-center gap-1">
              <button
                v-for="rf in radarFilterOptions"
                :key="rf.id"
                type="button"
                :aria-pressed="activeRadarFilter === rf.id"
                @click="emit('update:activeRadarFilter', rf.id)"
                class="sci-fi-chip px-2 py-0.5 border transition-all cursor-pointer"
                :class="activeRadarFilter === rf.id
                  ? 'bg-[#005851] dark:bg-[#00A99D] text-white border-[#005851] dark:border-[#00A99D] font-bold shadow-[0_0_10px_rgba(0,169,157,0.28)]'
                  : 'bg-white/90 dark:bg-[#001D1A] text-slate-600 dark:text-slate-300 border-slate-200 dark:border-[#004D44] hover:border-[#00A99D] hover:text-[#005851] dark:hover:text-white'"
              >
                {{ rf.label }}
              </button>
            </div>

            <div
              v-if="lastInterceptedNode"
              class="sci-fi-chip inline-flex items-center gap-1 px-2 py-0.5 bg-white/90 dark:bg-[#001715] border border-[#00A99D]/45 text-[#005851] dark:text-[#99CC33] font-bold tabular-nums shadow-xs"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-[#7CA622] dark:bg-[#99CC33] animate-ping"></span>
              <span>SIG//{{ lastInterceptedNode.shortCode }} &middot; {{ lastInterceptedNode.distanceKm }}K</span>
            </div>
          </div>

          <!-- Sci-Fi Chamfered Target Lock Identity Bar -->
          <div v-if="lockedRadarTarget && lockedRadarMetrics" class="sci-fi-frame">
            <div class="sci-fi-frame-inner bg-white/95 dark:bg-[#001614]/95 px-2.5 py-2 text-[10px] font-mono">
              <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                  <div class="flex items-center gap-1.5">
                    <Crosshair class="w-3 h-3 text-[#00A99D] dark:text-[#99CC33] shrink-0" />
                    <span class="sci-fi-chip px-1.5 py-[1px] bg-[#005851] dark:bg-[#00A99D] text-white text-[8.5px] font-bold tracking-wider">
                      {{ lockedRadarTarget.shortCode }}
                    </span>
                    <strong class="text-[11px] font-sans font-bold text-[#004640] dark:text-white truncate">
                      {{ lockedRadarTarget.shortTitle }}
                    </strong>
                  </div>
                  <div class="mt-0.5 text-[9px] text-slate-500 dark:text-[#99DFD6]/85 truncate tabular-nums">
                    <span class="text-[#007B72] dark:text-[#5eead4] font-semibold">TGT LOCK</span>
                    <span class="mx-1">&bull;</span>
                    <span>{{ lockedRadarTarget.moda }}</span>
                    <span class="mx-1">&bull;</span>
                    <span>{{ lockedRadarMetrics.lat.toFixed(4) }}, {{ lockedRadarMetrics.lng.toFixed(4) }}</span>
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
            </div>
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

.radar-cockpit-stage-wrap {
  container-type: inline-size;
  container-name: radar-stage;
}

.radar-cockpit-stage {
  position: relative;
  z-index: 1;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  grid-template-areas:
    'dial dial'
    'left right';
  gap: 0.625rem;
  align-items: stretch;
}

.radar-hud-conduit {
  display: none;
}

.radar-wing-left {
  grid-area: left;
  min-width: 0;
}

.radar-center-scope {
  grid-area: dial;
  display: flex;
  align-items: center;
  justify-content: center;
}

.radar-wing-right {
  grid-area: right;
  min-width: 0;
}

.radar-bezel-dial-size {
  width: clamp(206px, 68cqw, 248px);
  height: clamp(206px, 68cqw, 248px);
}

@container radar-stage (min-width: 370px) {
  .radar-cockpit-stage {
    grid-template-columns: minmax(78px, 1fr) auto minmax(78px, 1fr);
    grid-template-areas: 'left dial right';
    gap: 0.5rem;
  }

  .radar-hud-conduit {
    display: block;
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
  }

  .radar-bezel-dial-size {
    width: clamp(190px, 46cqw, 248px);
    height: clamp(190px, 46cqw, 248px);
  }
}

.radar-deck-light.radar-holo-deck {
  background: radial-gradient(circle at 50% 42%, #f7fdfb 0%, #eaf8f5 60%, #dcf2ee 100%);
}

.radar-deck-dark.radar-holo-deck {
  background: radial-gradient(circle at 50% 42%, #002b26 0%, #001c19 58%, #001210 100%);
}

.radar-holo-grid {
  position: absolute;
  inset: 0;
  background-image:
    radial-gradient(circle, rgba(0, 169, 157, 0.16) 1px, transparent 1px),
    linear-gradient(to right, rgba(0, 169, 157, 0.04) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(0, 169, 157, 0.04) 1px, transparent 1px);
  background-size: 18px 18px, 36px 36px, 36px 36px;
}

.radar-deck-dark .radar-holo-grid {
  background-image:
    radial-gradient(circle, rgba(94, 234, 212, 0.14) 1px, transparent 1px),
    linear-gradient(to right, rgba(0, 169, 157, 0.06) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(0, 169, 157, 0.06) 1px, transparent 1px);
}

.sci-fi-frame {
  padding: 1px;
  background: linear-gradient(135deg, rgba(0, 169, 157, 0.65), rgba(153, 204, 51, 0.35), rgba(0, 88, 81, 0.45));
  clip-path: polygon(0 6px, 6px 0, 100% 0, 100% calc(100% - 6px), calc(100% - 6px) 100%, 0 100%);
  filter: drop-shadow(0 2px 6px rgba(0, 169, 157, 0.1));
}

.radar-deck-dark .sci-fi-frame {
  background: linear-gradient(135deg, rgba(94, 234, 212, 0.55), rgba(153, 204, 51, 0.45), rgba(0, 169, 157, 0.4));
  filter: drop-shadow(0 0 10px rgba(0, 169, 157, 0.18));
}

.sci-fi-frame-inner {
  clip-path: polygon(0 6px, 6px 0, 100% 0, 100% calc(100% - 6px), calc(100% - 6px) 100%, 0 100%);
}

.sci-fi-chip {
  clip-path: polygon(0 4px, 4px 0, 100% 0, 100% calc(100% - 4px), calc(100% - 4px) 100%, 0 100%);
}

@keyframes sci-fi-wave-scroll {
  from {
    transform: translateX(0%);
  }
  to {
    transform: translateX(-50%);
  }
}

.sci-fi-wave-track {
  width: 200%;
  animation: sci-fi-wave-scroll 3.4s linear infinite;
}

@keyframes radar-hub-pulse {
  0% {
    transform: scale(0.75);
    opacity: 0.85;
  }
  100% {
    transform: scale(2.2);
    opacity: 0;
  }
}

.radar-hub-ion-pulse {
  transform-box: fill-box;
  transform-origin: center;
  animation: radar-hub-pulse 2.4s cubic-bezier(0.16, 1, 0.3, 1) infinite;
}

.radar-outer-bezel-spin {
  transform-box: view-box;
  transform-origin: 50% 50%;
  animation: radar-lock-spin 28s linear infinite reverse;
}

.radar-inner-gimbal-spin {
  transform-box: view-box;
  transform-origin: 50% 50%;
  animation: radar-lock-spin 22s linear infinite;
}

.radar-lock-svg-ccw {
  transform-box: fill-box;
  transform-origin: center;
  animation: radar-lock-spin 8s linear infinite reverse;
}
</style>
