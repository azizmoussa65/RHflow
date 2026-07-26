<template>
  <div class="modal-overlay" :class="{ open: modelValue }" @click.self="$emit('update:modelValue', false)">
    <div class="modal">
      <div class="flex items-center justify-between mb-4">
        <div style="font-size:15px;font-weight:700;color:var(--text-primary)">{{ title }}</div>
        <button @click="$emit('update:modelValue', false)"
          style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:18px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({ modelValue: Boolean, title: String })
defineEmits(['update:modelValue'])
</script>
