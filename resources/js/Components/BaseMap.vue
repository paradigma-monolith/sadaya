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
      attribution: attribution || '&copy; OpenStreetMap contributors &copy; CARTO',
    },
  },
  layers: [
    {
      id: 'raster-tiles-layer',
      type: 'raster',
      source: 'raster-tiles',
      minzoom: 0,
      maxzoom: 19,
    },
  ],
});

// Style definitions using Carto and OpenStreetMap CDN raster tiles
const STYLES = {
  dark: createRasterStyle(
    ['a', 'b', 'c', 'd'],
    'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png',
    '&copy; OpenStreetMap contributors &copy; CARTO'
  ),
  light: createRasterStyle(
    ['a', 'b', 'c', 'd'],
    'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png',
    '&copy; OpenStreetMap contributors &copy; CARTO'
  ),
  voyager: createRasterStyle(
    ['a', 'b', 'c', 'd'],
    'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png',
    '&copy; OpenStreetMap contributors &copy; CARTO'
  ),
  osm: createRasterStyle(
    ['a', 'b', 'c', 'd'],
    'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png',
    '&copy; OpenStreetMap contributors &copy; CARTO'
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
    const badgeLabel = node.flag || node.short_code || node.shortCode || '📍';

    el.innerHTML = `
      <div style="position: relative; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;">
        <span style="position: absolute; width: 100%; height: 100%; border-radius: 9999px; opacity: 0.75; animation: maplibre-pulse-ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite; background-color: ${isPrimary ? '#334155' : '#10b981'};"></span>
        <div style="position: relative; z-index: 10; display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 9999px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.35); border: 2px solid #ffffff; background-color: ${isPrimary ? '#0f172a' : '#059669'}; color: #ffffff; font-size: ${badgeLabel.length > 2 ? '10px' : '12px'}; font-family: monospace; font-weight: 800; cursor: pointer; transition: transform 0.2s;">
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
      <div style="padding: 7px 9px; font-family: sans-serif; color: #0f172a; min-width: 155px; max-width: 195px;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 5px; padding-right: 12px;">
          <div style="display: flex; align-items: center; gap: 4px; font-weight: 700; font-size: 10px; color: #0f172a; line-height: 1.2;">
            <span style="font-family: monospace; background: #e2e8f0; padding: 1px 4px; border-radius: 3px; font-size: 8.5px;">${badgeLabel}</span>
            <span>${node.short_name || node.name}</span>
          </div>
          <span style="padding: 1px 4px; border-radius: 9999px; font-size: 8px; font-weight: 700; text-transform: uppercase; background-color: #d1fae5; color: #065f46; flex-shrink: 0;">
            ${node.status || 'ONLINE'}
          </span>
        </div>
        <div style="font-size: 9px; color: #475569; display: flex; flex-direction: column; gap: 2.5px;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="color: #64748b;">Peran: <strong style="color: #1e293b;">${node.type || 'Wilker'}</strong></span>
            <span style="font-weight: 700; color: #059669;">${node.ping || '4ms'}</span>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="color: #64748b;">Throughput:</span>
            <span style="font-family: monospace; font-weight: 600; color: #4f46e5;">${node.connections ? Number(node.connections).toLocaleString() : '1420'} req/s</span>
          </div>
          <div style="font-size: 8.5px; color: #475569; margin-top: 2px; line-height: 1.25; border-top: 1px dashed #e2e8f0; padding-top: 3px;">
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
      map.addLayer({
        id: 'bkk-wilker-links-layer',
        type: 'line',
        source: 'bkk-wilker-links',
        paint: {
          'line-color': '#10b981',
          'line-width': 2,
          'line-dasharray': [3, 2],
          'line-opacity': 0.85,
        },
      });
    }
  };

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
    transform: scale(0.95);
    opacity: 0.8;
  }
  70% {
    transform: scale(2.2);
    opacity: 0;
  }
  100% {
    transform: scale(2.4);
    opacity: 0;
  }
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
