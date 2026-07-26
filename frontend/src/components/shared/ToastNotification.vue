<template>
  <Transition name="toast-slide">
    <div v-if="visible" class="toast" :class="type">
      <div class="toast-icon">
        <i :class="iconClass"></i>
      </div>
      <div>
        <div class="toast-title">{{ title }}</div>
        <div class="toast-msg">{{ message }}</div>
      </div>
      <button class="toast-close" @click="visible=false"><i class="fa-solid fa-xmark"></i></button>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed } from 'vue'

const visible  = ref(false)
const title    = ref('')
const message  = ref('')
const type     = ref('success')

const iconClass = computed(() => ({
  success: 'fa-solid fa-circle-check',
  error:   'fa-solid fa-circle-xmark',
  info:    'fa-solid fa-circle-info'
}[type.value]))

let timer = null

function show(t, msg, tp = 'success') {
  title.value   = t
  message.value = msg
  type.value    = tp
  visible.value = true
  clearTimeout(timer)
  timer = setTimeout(() => { visible.value = false }, 3500)
}

defineExpose({ show })
</script>

<style scoped>
.toast {
  position:fixed; bottom:24px; right:24px;
  background:var(--bg-card); border:1px solid var(--border);
  border-radius:14px; padding:14px 20px;
  display:flex; align-items:center; gap:12px;
  z-index:9999; min-width:290px; max-width:380px;
  box-shadow:0 8px 32px rgba(0,0,0,0.3);
}
.toast.success { border-left:3px solid #10b981; }
.toast.error   { border-left:3px solid #ef4444; }
.toast.info    { border-left:3px solid #3b82f6; }

.toast-icon { font-size:20px; }
.toast.success .toast-icon { color:#10b981; }
.toast.error   .toast-icon { color:#ef4444; }
.toast.info    .toast-icon { color:#3b82f6; }

.toast-title { font-size:13px; font-weight:600; color:var(--text-primary); }
.toast-msg   { font-size:11.5px; color:var(--text-muted); margin-top:2px; }
.toast-close {
  margin-left:auto; background:none; border:none;
  color:var(--text-muted); cursor:pointer; font-size:14px;
  padding:2px; transition:color 0.2s;
}
.toast-close:hover { color:var(--text-primary); }

.toast-slide-enter-active { animation:slideIn 0.3s ease; }
.toast-slide-leave-active { animation:slideIn 0.25s ease reverse; }
@keyframes slideIn {
  from { opacity:0; transform:translateX(60px); }
  to   { opacity:1; transform:none; }
}
</style>
