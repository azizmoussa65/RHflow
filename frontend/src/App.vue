<template>
  <!-- Root App with Toast notifications -->
  <RouterView />
  <ToastNotification ref="toastRef" />
</template>

<script setup>
import { RouterView } from 'vue-router'
import ToastNotification from '@/components/shared/ToastNotification.vue'
import { useThemeStore } from '@/stores/theme.js'
import { provide, ref } from 'vue'

// Initialize theme on app mount
useThemeStore()

// Global toast — provide to all child components
const toastRef = ref(null)
provide('toast', {
  success: (title, msg) => toastRef.value?.show(title, msg, 'success'),
  error:   (title, msg) => toastRef.value?.show(title, msg, 'error'),
  info:    (title, msg) => toastRef.value?.show(title, msg, 'info')
})
</script>
