<template>
  <div class="browser-window" :style="{ width: width + 'px', height: height + 'px' }">
    <div class="browser-tabbar">
      <div class="browser-lights">
        <span class="light" style="background:#ff5f57" />
        <span class="light" style="background:#febc2e" />
        <span class="light" style="background:#28c840" />
      </div>
      <div class="browser-tab">
        <span class="browser-tab-dot" />
        <span class="browser-tab-title">{{ title }}</span>
      </div>
    </div>
    <div class="browser-toolbar">
      <div class="browser-icon-dot" />
      <div class="browser-urlbar">
        <span class="url-dot" />
        <span class="browser-url">{{ url }}</span>
      </div>
      <div class="browser-icon-dot" />
    </div>
    <div class="browser-content">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  url: { type: String, default: 'example.com' },
  title: { type: String, default: 'Nouvel onglet' },
  width: { type: [Number, String], default: 620 },
  height: { type: [Number, String], default: 400 },
})
</script>

<style scoped>
.browser-window {
  border-radius: 10px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  background: #35363a;
  max-width: 100%;
}
.browser-tabbar {
  display: flex;
  align-items: center;
  height: 44px;
  background: #202124;
  padding-right: 8px;
  flex-shrink: 0;
}
.browser-lights { display: flex; gap: 8px; padding: 0 14px; }
.light { width: 12px; height: 12px; border-radius: 50%; display: block; }
.browser-tab {
  position: relative;
  height: 34px;
  align-self: flex-end;
  margin-left: 4px;
  padding: 0 12px;
  display: flex;
  align-items: center;
  gap: 8px;
  background: #35363a;
  border-radius: 8px 8px 0 0;
  min-width: 120px;
  max-width: 220px;
  font-size: 12px;
  color: #e8eaed;
}
.browser-tab-dot { width: 14px; height: 14px; border-radius: 50%; background: #5f6368; flex-shrink: 0; }
.browser-tab-title { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.browser-toolbar {
  height: 40px;
  background: #35363a;
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 0 8px;
  flex-shrink: 0;
}
.browser-icon-dot {
  width: 28px; height: 28px;
  display: flex; align-items: center; justify-content: center;
}
.browser-icon-dot::before {
  content: ''; width: 16px; height: 16px; border-radius: 50%;
  background: #9aa0a6; opacity: 0.4;
}
.browser-urlbar {
  flex: 1; height: 30px; border-radius: 15px; background: #282a2d;
  display: flex; align-items: center; gap: 8px; padding: 0 14px; margin: 0 6px;
}
.url-dot { width: 12px; height: 12px; border-radius: 50%; background: #9aa0a6; opacity: 0.4; flex-shrink: 0; }
.browser-url { flex: 1; color: #e8eaed; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.browser-content { flex: 1; background: #fff; overflow: hidden; }
</style>
