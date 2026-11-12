<script setup>
import { ref, shallowRef, onMounted, onUnmounted, watch } from 'vue';
import * as echarts from 'echarts';
import { useSidebar } from '@/composables/useSidebar';

const { isCollapsed, isOpen } = useSidebar();

const props = defineProps({
  option: {
    type: Object,
    required: true,
  },
  height: {
    type: String,
    default: '340px',
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const chartRef = ref(null);
const chartInstance = shallowRef(null);
let resizeObserver = null;
let themeObserver = null;

const isDarkMode = () => typeof document !== 'undefined' && document.documentElement.classList.contains('dark');

const DARK_INK_COLORS = new Set(['#0f172a', '#1e293b', '#334155', '#475569', '#64748b', '#000', '#000000', '#111827']);
const DARK_TEAL_COLORS = new Set(['#005851', '#004640', '#003b36', '#002b27']);

const normalizeRadar = (radar) => {
  if (!radar || typeof radar !== 'object') return radar;
  const next = { ...radar };
  next.axisName = {
    ...(next.axisName || {}),
    color: '#cbd5e1',
  };
  if (next.name && typeof next.name === 'object') {
    next.name = {
      ...next.name,
      textStyle: {
        ...(next.name.textStyle || {}),
        color: '#cbd5e1',
      },
    };
  }
  next.splitLine = {
    ...(next.splitLine || {}),
    lineStyle: {
      ...((next.splitLine && next.splitLine.lineStyle) || {}),
      color: [
        'rgba(94, 234, 212, 0.14)',
        'rgba(94, 234, 212, 0.22)',
        'rgba(94, 234, 212, 0.30)',
        'rgba(94, 234, 212, 0.38)',
      ],
    },
  };
  next.splitArea = {
    ...(next.splitArea || {}),
    show: next.splitArea?.show !== false,
    areaStyle: {
      ...((next.splitArea && next.splitArea.areaStyle) || {}),
      color: ['rgba(30, 41, 59, 0.38)', 'rgba(15, 23, 32, 0.55)'],
    },
  };
  next.axisLine = {
    ...(next.axisLine || {}),
    lineStyle: {
      ...((next.axisLine && next.axisLine.lineStyle) || {}),
      color: 'rgba(94, 234, 212, 0.30)',
    },
  };
  return next;
};

const normalizeAxis = (axis, dark) => {
  if (!axis || typeof axis !== 'object') return axis;
  const next = { ...axis };
  if (dark) {
    next.axisLabel = {
      ...(next.axisLabel || {}),
      color: '#cbd5e1',
    };
    next.nameTextStyle = {
      ...(next.nameTextStyle || {}),
      color: '#94a3b8',
    };
    next.axisLine = {
      ...(next.axisLine || {}),
      lineStyle: {
        ...((next.axisLine && next.axisLine.lineStyle) || {}),
        color: 'rgba(148, 163, 184, 0.25)',
      },
    };
    next.axisTick = {
      ...(next.axisTick || {}),
      lineStyle: {
        ...((next.axisTick && next.axisTick.lineStyle) || {}),
        color: 'rgba(148, 163, 184, 0.25)',
      },
    };
    if (!next.splitLine || next.splitLine.show !== false) {
      next.splitLine = {
        ...(next.splitLine || {}),
        lineStyle: {
          ...((next.splitLine && next.splitLine.lineStyle) || {}),
          color: 'rgba(148, 163, 184, 0.14)',
        },
      };
    }
  }
  return next;
};

const buildThemedOption = (option) => {
  if (!option || typeof option !== 'object') return option;
  const dark = isDarkMode();
  const next = {
    ...option,
    backgroundColor: 'transparent',
  };

  if (!dark) return next;

  if (next.legend) {
    const mapLegend = (lg) => ({
      ...lg,
      textStyle: { ...(lg.textStyle || {}), color: '#cbd5e1' },
      pageTextStyle: { ...(lg.pageTextStyle || {}), color: '#94a3b8' },
    });
    next.legend = Array.isArray(next.legend) ? next.legend.map(mapLegend) : mapLegend(next.legend);
  }

  if (next.xAxis) {
    next.xAxis = Array.isArray(next.xAxis)
      ? next.xAxis.map((a) => normalizeAxis(a, true))
      : normalizeAxis(next.xAxis, true);
  }

  if (next.yAxis) {
    next.yAxis = Array.isArray(next.yAxis)
      ? next.yAxis.map((a) => normalizeAxis(a, true))
      : normalizeAxis(next.yAxis, true);
  }

  if (next.radar) {
    next.radar = Array.isArray(next.radar)
      ? next.radar.map((r) => normalizeRadar(r))
      : normalizeRadar(next.radar);
  }

  if (next.tooltip) {
    const bg = typeof next.tooltip.backgroundColor === 'string' ? next.tooltip.backgroundColor.toLowerCase() : '';
    const keepCustomBg = bg && bg !== '#fff' && bg !== '#ffffff' && bg !== 'white' && !bg.includes('255, 255, 255');
    next.tooltip = {
      ...next.tooltip,
      backgroundColor: keepCustomBg ? next.tooltip.backgroundColor : '#0f172a',
      borderColor: keepCustomBg ? (next.tooltip.borderColor || '#00A99D') : '#334155',
      textStyle: {
        ...(next.tooltip.textStyle || {}),
        color: '#f1f5f9',
      },
    };
  }

  if (Array.isArray(next.series)) {
    next.series = next.series.map((s) => {
      if (!s || typeof s !== 'object') return s;
      const copy = { ...s };
      if (copy.label && typeof copy.label === 'object') {
        const lblColor = typeof copy.label.color === 'string' ? copy.label.color.toLowerCase() : '';
        if (!lblColor || DARK_INK_COLORS.has(lblColor) || DARK_TEAL_COLORS.has(lblColor)) {
          copy.label = { ...copy.label, color: '#e2e8f0' };
        }
      }
      if (copy.itemStyle && typeof copy.itemStyle.color === 'string' && DARK_TEAL_COLORS.has(copy.itemStyle.color.toLowerCase())) {
        copy.itemStyle = { ...copy.itemStyle, color: '#2dd4bf' };
      }
      if (copy.lineStyle && typeof copy.lineStyle.color === 'string' && DARK_TEAL_COLORS.has(copy.lineStyle.color.toLowerCase())) {
        copy.lineStyle = { ...copy.lineStyle, color: '#2dd4bf' };
      }
      return copy;
    });
  }

  return next;
};

const initChart = () => {
  if (!chartRef.value) return;

  if (chartInstance.value) {
    chartInstance.value.dispose();
  }

  const dark = isDarkMode();

  chartInstance.value = echarts.init(
    chartRef.value,
    dark ? 'dark' : undefined,
    {
      renderer: 'canvas',
    }
  );

  if (props.loading) {
    chartInstance.value.showLoading({
      color: '#00A99D',
      textColor: dark ? '#cbd5e1' : '#475569',
      maskColor: dark ? 'rgba(11, 17, 22, 0.75)' : 'rgba(255, 255, 255, 0.8)',
    });
  } else {
    chartInstance.value.setOption(buildThemedOption(props.option), true);
  }
};

onMounted(() => {
  initChart();

  // Responsive Auto-Resize via ResizeObserver
  if (typeof ResizeObserver !== 'undefined' && chartRef.value) {
    resizeObserver = new ResizeObserver(() => {
      if (chartInstance.value) {
        chartInstance.value.resize();
      }
    });
    resizeObserver.observe(chartRef.value);
  }

  // React to ThemeSwitcher (dark / light mode toggles)
  if (typeof MutationObserver !== 'undefined' && typeof document !== 'undefined') {
    themeObserver = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        if (mutation.attributeName === 'class') {
          initChart();
          break;
        }
      }
    });
    themeObserver.observe(document.documentElement, { attributes: true });
  }
});

watch(() => props.option, (newVal) => {
  if (chartInstance.value && newVal) {
    chartInstance.value.setOption(buildThemedOption(newVal), true);
  }
}, { deep: true });

// Smoothly resize ECharts canvas throughout the 300ms sidebar collapse/expand transition
watch([isCollapsed, isOpen], () => {
  [50, 120, 200, 280, 350].forEach((delay) => {
    setTimeout(() => {
      if (chartInstance.value) {
        chartInstance.value.resize();
      }
    }, delay);
  });
});

watch(() => props.loading, (isLoading) => {
  if (!chartInstance.value) return;
  const dark = isDarkMode();
  if (isLoading) {
    chartInstance.value.showLoading({
      color: '#6366f1',
      textColor: dark ? '#cbd5e1' : '#475569',
      maskColor: dark ? 'rgba(15, 23, 42, 0.6)' : 'rgba(255, 255, 255, 0.6)',
    });
  } else {
    chartInstance.value.hideLoading();
  }
});

onUnmounted(() => {
  if (resizeObserver) {
    resizeObserver.disconnect();
    resizeObserver = null;
  }
  if (themeObserver) {
    themeObserver.disconnect();
    themeObserver = null;
  }
  if (chartInstance.value) {
    chartInstance.value.dispose();
    chartInstance.value = null;
  }
});

defineExpose({
  getEchartInstance: () => chartInstance.value,
  resize: () => chartInstance.value?.resize(),
});
</script>

<template>
  <div class="w-full relative">
    <div ref="chartRef" :style="{ height, width: '100%' }"></div>
  </div>
</template>
