import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useThemeStore = defineStore('theme', () => {
  // Initialize from localStorage (default: dark)
  const isDark = ref(localStorage.getItem('hrflow_theme') !== 'light')

  function applyTheme() {
    if (isDark.value) {
      document.documentElement.classList.remove('light')
    } else {
      document.documentElement.classList.add('light')
    }
    localStorage.setItem('hrflow_theme', isDark.value ? 'dark' : 'light')
  }

  function toggleTheme() {
    isDark.value = !isDark.value
    applyTheme()
  }

  // Apply on store init
  applyTheme()

  return { isDark, toggleTheme }
})
