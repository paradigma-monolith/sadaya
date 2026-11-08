<script setup>
import { ref, shallowRef, onMounted, onUnmounted, watch } from 'vue';
import * as maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';
import { useSidebar } from '@/composables/useSidebar';

const { isCollapsed, isOpen } = useSidebar();

const props = defineProps({
  markers: {
    type: Array,
    default: () => [],
  },
  initialCenter: {
    type: Array,
    default: () => [106.8272, -6.1751], // Jakarta [lng, lat]
  },
  initialZoom: {
    type: Number,
    default: 3.5,
  },
  initialPitch: {
    type: Number,
    default: 35,
  },
  initialBearing: {
    type: Number,
    default: 0,
  },
  height: {
    type: String,
    default: '540px',
  },
  styleType: {
    type: String,
    default: 'osm', // 'auto' | 'dark' | 'light' | 'voyager' | 'osm'
  },
});

const emit = defineEmits(['selectMarker', 'mapMove', 'mapLoaded']);

const mapContainer = ref(null);
const mapInstance = shallowRef(null);
const currentStyleKey = ref('osm');
const activeMarkerId = ref(null);
let markerInstances = [];
let resizeObserver = null;
let themeObserver = null;
let orbitInterval = null;

// Helper to create reliable raster style specifications (Fast, zero glyph/worker network failures)
const createRasterStyle = (subdomains, tileTemplate, attribution) => ({
  version: 8,
  sources: {
    'raster-tiles': {
      type: 'raster',
      tiles: subdomains.map(s => tileTemplate.replace('{s}', s)),
      tileSize: 256,
      attribution: attribution || '&copy; OpenStreetMap contributors',
    },
  },
  layers: [
    {
      id: 'raster-tiles-layer',
      type: 'raster',
      source: 'raster-tiles',
      minzoom: 0,
      maxzoom: 20,
    },
  ],
});

// Style definitions using genuine, watermark-free OpenStreetMap CDN raster tiles (No API key required)
const STYLES = {
  osm: createRasterStyle(
    ['a', 'b', 'c'],
    'https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png',
    '&copy; OpenStreetMap contributors'
  ),
  osmde: createRasterStyle(
    ['a', 'b', 'c'],
    'https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png',
    '&copy; OpenStreetMap contributors'
  ),
  hot: createRasterStyle(
    ['a', 'b', 'c'],
    'https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
    '&copy; OpenStreetMap contributors'
  ),
  light: createRasterStyle(
    ['a', 'b', 'c'],
    'https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png',
    '&copy; OpenStreetMap contributors'
  ),
  dark: createRasterStyle(
    ['a', 'b', 'c'],
    'https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png',
    '&copy; OpenStreetMap contributors'
  ),
  voyager: createRasterStyle(
    ['a', 'b', 'c'],
    'https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png',
    '&copy; OpenStreetMap contributors'
  ),
};

const isDarkMode = () => {
  if (typeof document === 'undefined') return false;
  return document.documentElement.classList.contains('dark');
};

const resolveStyle = (styleKey) => {
  if (styleKey === 'auto') {
    return isDarkMode() ? STYLES.dark : STYLES.osm;
  }
  return STYLES[styleKey] || STYLES.osm;
};

// Clear all markers from map canvas
const clearMarkers = () => {
  markerInstances.forEach(marker => marker.remove());
  markerInstances = [];
};

// Render custom HTML Markers with glowing animated radar rings & interactive popups
const renderMarkers = () => {
  if (!mapInstance.value) return;
  clearMarkers();

  props.markers.forEach((node) => {
    if (!node.coordinates || node.coordinates.length < 2) return;

    // Marker DOM Element
    const el = document.createElement('div');
    el.className = 'maplibre-custom-marker';
    el.style.width = '38px';
    el.style.height = '38px';
    el.style.cursor = 'pointer';
    el.style.zIndex = '15';
    el.setAttribute('data-node-id', node.id);

    const isPrimary = Boolean(node.is_hq || node.no === 1 || node.id === 'jkt-01');
    const isSelected = activeMarkerId.value ? activeMarkerId.value === node.id : isPrimary;
    const badgeLabel = node.flag || node.short_code || node.shortCode || '📍';
    const ringColor = isPrimary ? '#00A99D' : '#99CC33';

    el.innerHTML = `
      <div class="bkk-radar-marker" style="position: relative; width: 46px; height: 46px; display: flex; align-items: center; justify-content: center;">
        <span style="position: absolute; width: 100%; height: 100%; border-radius: 9999px; opacity: 0.75; animation: maplibre-pulse-ping 1.8s cubic-bezier(0, 0, 0.2, 1) infinite; background-color: ${ringColor};"></span>
        <span style="position: absolute; width: 100%; height: 100%; border-radius: 9999px; opacity: 0.5; animation: maplibre-pulse-ping 1.8s cubic-bezier(0, 0, 0.2, 1) 0.7s infinite; border: 1.5px solid ${ringColor};"></span>
        <span style="position: absolute; width: 42px; height: 42px; border-radius: 9999px; border: 1.5px dashed ${isSelected ? '#99CC33' : 'rgba(0, 169, 157, 0.65)'}; animation: maplibre-radar-spin 6s linear infinite;"></span>
        <div style="position: relative; z-index: 10; display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 9999px; box-shadow: 0 0 14px ${ringColor}, 0 4px 6px -1px rgba(0, 0, 0, 0.45); border: 2px solid ${isPrimary ? '#99CC33' : '#ffffff'}; background: ${isPrimary ? 'linear-gradient(135deg, #003B36, #00A99D)' : 'linear-gradient(135deg, #005851, #00A99D)'}; color: #ffffff; font-size: ${badgeLabel.length > 2 ? '9.5px' : '11px'}; font-family: monospace; font-weight: 900; cursor: pointer; transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);">
          ${badgeLabel}
        </div>
      </div>
    `;

    // Interactive Compact HTML Popup
    const popup = new maplibregl.Popup({
      offset: 18,
      closeButton: true,
      closeOnClick: true,
      maxWidth: '205px',
      className: 'custom-maplibre-popup',
    }).setHTML(`
      <div style="padding: 7px 9px; font-family: 'Segoe UI', sans-serif; color: #005851; min-width: 155px; max-width: 195px;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px; border-bottom: 1px solid #E6F7F5; padding-bottom: 4px; margin-bottom: 5px; padding-right: 12px;">
          <div style="display: flex; align-items: center; gap: 4px; font-weight: 700; font-size: 10px; color: #005851; line-height: 1.2;">
            <span style="font-family: monospace; background: #E6F7F5; color: #005851; border: 1px solid #99DFD6; padding: 1px 4px; border-radius: 3px; font-size: 8.5px;">${badgeLabel}</span>
            <span>${node.short_name || node.name}</span>
          </div>
          <span style="padding: 1px 4px; border-radius: 9999px; font-size: 8px; font-weight: 700; text-transform: uppercase; background-color: #E6F7F5; color: #005851; border: 1px solid #99DFD6; flex-shrink: 0;">
            ${node.status || 'ONLINE'}
          </span>
        </div>
        <div style="font-size: 9px; color: #475569; display: flex; flex-direction: column; gap: 2.5px;">
          ${Array.isArray(node.popupRows) && node.popupRows.length
            ? node.popupRows.map((row) => `
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="color: #64748b;">${row.label}</span>
            <span style="font-family: monospace; font-weight: 700; color: ${row.color || '#005851'};">${row.value}</span>
          </div>`).join('')
            : `
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="color: #64748b;">Peran: <strong style="color: #005851;">${node.type || 'Wilker'}</strong></span>
            <span style="font-weight: 700; color: #00A99D;">${node.ping || '4ms'}</span>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="color: #64748b;">Throughput:</span>
            <span style="font-family: monospace; font-weight: 600; color: #005851;">${node.connections ? Number(node.connections).toLocaleString() : '1420'} req/s</span>
          </div>`}
          <div style="font-size: 8.5px; color: #475569; margin-top: 2px; line-height: 1.25; border-top: 1px dashed #E6F7F5; padding-top: 3px;">
            📍 ${node.address || node.location || ''}
          </div>
        </div>
      </div>
    `);

    const marker = new maplibregl.Marker({ element: el })
      .setLngLat(node.coordinates)
      .setPopup(popup)
      .addTo(mapInstance.value);

    el.addEventListener('click', () => {
      activeMarkerId.value = node.id;
      emit('selectMarker', node);
    });

    markerInstances.push(marker);
  });
};

const renderNetworkLines = (targetMap) => {
  const map = targetMap || mapInstance.value;
  if (!map || !props.markers || props.markers.length < 2) return;
  const hq = props.markers.find(m => m.is_hq || m.no === 1) || props.markers[0];
  if (!hq || !hq.coordinates) return;

  const features = props.markers
    .filter(m => m.id !== hq.id && m.coordinates && m.coordinates.length >= 2)
    .map(w => ({
      type: 'Feature',
      geometry: {
        type: 'LineString',
        coordinates: [hq.coordinates, w.coordinates],
      },
    }));

  if (map.getSource('bkk-wilker-links')) {
    map.getSource('bkk-wilker-links').setData({
      type: 'FeatureCollection',
      features,
    });
  } else {
    map.addSource('bkk-wilker-links', {
      type: 'geojson',
      data: {
        type: 'FeatureCollection',
        features,
      },
    });
    if (!map.getLayer('bkk-wilker-links-glow')) {
      map.addLayer({
        id: 'bkk-wilker-links-glow',
        type: 'line',
        source: 'bkk-wilker-links',
        paint: {
          'line-color': '#99CC33',
          'line-width': 6,
          'line-opacity': 0.45,
          'line-blur': 2,
        },
      });
    }
    if (!map.getLayer('bkk-wilker-links-layer')) {
      map.addLayer({
        id: 'bkk-wilker-links-layer',
        type: 'line',
        source: 'bkk-wilker-links',
        paint: {
          'line-color': '#00A99D',
          'line-width': 2.2,
          'line-dasharray': [3, 2],
          'line-opacity': 0.95,
        },
      });
    }
  }
};

const initMap = () => {
  if (!mapContainer.value) return;

  if (mapInstance.value) {
    mapInstance.value.remove();
    mapInstance.value = null;
  }

  const initialStyle = resolveStyle(props.styleType);
  currentStyleKey.value = props.styleType === 'auto' ? (isDarkMode() ? 'dark' : 'light') : props.styleType;

  const map = new maplibregl.Map({
    container: mapContainer.value,
    style: initialStyle,
    center: props.initialCenter,
    zoom: props.initialZoom,
    pitch: props.initialPitch,
    bearing: props.initialBearing,
    attributionControl: false,
  });

  // Controls
  map.addControl(new maplibregl.NavigationControl({ visualizePitch: true }), 'top-right');
  map.addControl(new maplibregl.FullscreenControl(), 'top-right');
  map.addControl(new maplibregl.ScaleControl({ unit: 'metric' }), 'bottom-left');

  map.on('load', () => {
    mapInstance.value = map;
    renderNetworkLines(map);
    renderMarkers();
    const center = map.getCenter();
    emit('mapMove', {
      lng: Number(center.lng.toFixed(4)),
      lat: Number(center.lat.toFixed(4)),
      zoom: Number(map.getZoom().toFixed(2)),
      pitch: Number(map.getPitch().toFixed(0)),
      bearing: Number(map.getBearing().toFixed(0)),
    });
    emit('mapLoaded', map);
  });

  map.on('move', () => {
    const center = map.getCenter();
    emit('mapMove', {
      lng: Number(center.lng.toFixed(4)),
      lat: Number(center.lat.toFixed(4)),
      zoom: Number(map.getZoom().toFixed(2)),
      pitch: Number(map.getPitch().toFixed(0)),
      bearing: Number(map.getBearing().toFixed(0)),
    });
  });

  mapInstance.value = map;
  // Render markers immediately
  renderMarkers();
};

// API Exposes for Parent Component
const flyTo = (coords, zoom = 12, pitch = 45, bearing = 0) => {
  if (!mapInstance.value) return;
  mapInstance.value.flyTo({
    center: coords,
    zoom,
    pitch,
    bearing,
    essential: true,
    speed: 1.2,
    curve: 1.4,
  });
};

const setStyle = (styleKey) => {
  if (!mapInstance.value) return;
  currentStyleKey.value = styleKey;
  const style = resolveStyle(styleKey);
  mapInstance.value.setStyle(style);
  mapInstance.value.once('style.load', () => {
    renderMarkers();
    renderNetworkLines(mapInstance.value);
  });
};

const fitAllMarkers = (padding = 50) => {
  if (!mapInstance.value || !props.markers || props.markers.length === 0) return;
  const bounds = new maplibregl.LngLatBounds();
  props.markers.forEach((m) => {
    if (m.coordinates && m.coordinates.length >= 2) {
      bounds.extend(m.coordinates);
    }
  });
  if (!bounds.isEmpty()) {
    mapInstance.value.fitBounds(bounds, {
      padding,
      maxZoom: 10.5,
      pitch: 25,
      bearing: 0,
      duration: 900,
    });
  }
};

const resetView = () => {
  if (props.markers && props.markers.length > 0) {
    fitAllMarkers(55);
  } else {
    flyTo(props.initialCenter, props.initialZoom, props.initialPitch, props.initialBearing);
  }
};

const toggle3DPitch = () => {
  if (!mapInstance.value) return;
  const currentPitch = mapInstance.value.getPitch();
  const nextPitch = currentPitch > 20 ? 0 : 60;
  mapInstance.value.easeTo({ pitch: nextPitch, duration: 600 });
};

let isOrbiting = false;
const toggleOrbit = () => {
  if (!mapInstance.value) return false;
  if (isOrbiting) {
    if (orbitInterval) clearInterval(orbitInterval);
    orbitInterval = null;
    isOrbiting = false;
    return false;
  } else {
    isOrbiting = true;
    orbitInterval = setInterval(() => {
      if (!mapInstance.value) return;
      const currentBearing = mapInstance.value.getBearing();
      mapInstance.value.setBearing((currentBearing + 1) % 360);
    }, 50);
    return true;
  }
};

onMounted(() => {
  initMap();

  // ResizeObserver for clean auto-resizing
  if (typeof ResizeObserver !== 'undefined' && mapContainer.value) {
    resizeObserver = new ResizeObserver(() => {
      if (mapInstance.value) {
        mapInstance.value.resize();
      }
    });
    resizeObserver.observe(mapContainer.value);
  }

  // Theme Observer for Dark / Light Mode
  if (typeof MutationObserver !== 'undefined' && typeof document !== 'undefined') {
    themeObserver = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        if (mutation.attributeName === 'class' && props.styleType === 'auto') {
          const targetKey = isDarkMode() ? 'dark' : 'light';
          if (targetKey !== currentStyleKey.value) {
            setStyle('auto');
          }
          break;
        }
      }
    });
    themeObserver.observe(document.documentElement, { attributes: true });
  }
});

watch(() => props.markers, () => {
  renderMarkers();
  renderNetworkLines(mapInstance.value);
}, { deep: true });

watch(() => props.styleType, (newVal) => {
  setStyle(newVal);
});

// Smoothly resize WebGL map canvas throughout the 300ms sidebar collapse/expand transition
watch([isCollapsed, isOpen], () => {
  [50, 120, 200, 280, 350].forEach((delay) => {
    setTimeout(() => {
      if (mapInstance.value) {
        mapInstance.value.resize();
      }
    }, delay);
  });
});

onUnmounted(() => {
  if (orbitInterval) {
    clearInterval(orbitInterval);
    orbitInterval = null;
  }
  if (resizeObserver) {
    resizeObserver.disconnect();
    resizeObserver = null;
  }
  if (themeObserver) {
    themeObserver.disconnect();
    themeObserver = null;
  }
  clearMarkers();
  if (mapInstance.value) {
    mapInstance.value.remove();
    mapInstance.value = null;
  }
});

defineExpose({
  getMapInstance: () => mapInstance.value,
  flyTo,
  setStyle,
  resetView,
  fitAllMarkers,
  toggle3DPitch,
  toggleOrbit,
  renderMarkers,
  renderNetworkLines,
});
</script>

<template>
  <div class="relative w-full h-full overflow-hidden">
    <div ref="mapContainer" :style="{ height, width: '100%' }" class="w-full"></div>
    <slot name="overlay"></slot>
  </div>
</template>

<style>
@keyframes maplibre-pulse-ping {
  0% {
    transform: scale(0.85);
    opacity: 0.85;
  }
  70% {
    transform: scale(2.35);
    opacity: 0;
  }
  100% {
    transform: scale(2.5);
    opacity: 0;
  }
}

@keyframes maplibre-radar-spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.bkk-radar-marker:hover > div {
  transform: scale(1.18);
}

.maplibregl-marker {
  position: absolute;
  top: 0;
  left: 0;
  pointer-events: auto;
  cursor: pointer;
  z-index: 10;
}

.custom-maplibre-popup .maplibregl-popup-content {
  border-radius: 0.65rem;
  box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.22), 0 4px 8px -4px rgba(0, 0, 0, 0.18);
  padding: 0;
  border: 1px solid rgba(226, 232, 240, 0.85);
  backdrop-filter: blur(8px);
}
.maplibregl-popup-close-button {
  padding: 2px 6px;
  color: #64748b;
  font-size: 12px;
  line-height: 1;
}
.maplibregl-popup-close-button:hover {
  color: #0f172a;
}
</style>
