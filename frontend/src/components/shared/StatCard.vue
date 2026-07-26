<template>
  <div class="stat-card" :class="color">
    <div class="flex items-center justify-between mb-3">
      <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em">
        {{ label }}
      </div>
      <div class="stat-icon" :style="{ background: iconBg }">
        <i :class="icon" :style="{ color: iconColor }"></i>
      </div>
    </div>
    <div style="font-size:2rem;font-weight:700;color:var(--text-primary);margin-bottom:4px">{{ value }}</div>
    <div class="flex items-center gap-1" style="font-size:11px" :style="{ color: trendColor }">
      <i :class="trendIcon" style="font-size:10px"></i>
      {{ trend }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({
  label: String,
  value: [String, Number],
  trend: String,
  trendUp: { type: Boolean, default: true },
  icon: { type: String, default: 'fa-solid fa-chart-bar' },
  color: { type: String, default: 'blue' }   // blue | cyan | green | amber
})
const colorMap = {
  blue:  { bg: 'rgba(59,130,246,0.15)', color: '#3b82f6' },
  cyan:  { bg: 'rgba(6,182,212,0.15)',  color: '#06b6d4' },
  green: { bg: 'rgba(16,185,129,0.15)', color: '#10b981' },
  amber: { bg: 'rgba(245,158,11,0.15)', color: '#f59e0b' },
}
const iconBg    = computed(() => colorMap[props.color]?.bg    || colorMap.blue.bg)
const iconColor = computed(() => colorMap[props.color]?.color || colorMap.blue.color)
const trendColor  = computed(() => props.trendUp ? '#10b981' : '#ef4444')
const trendIcon   = computed(() => props.trendUp ? 'fa-solid fa-arrow-up' : 'fa-solid fa-arrow-down')
</script>

<style scoped>
.stat-icon { width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:14px; }
</style>
