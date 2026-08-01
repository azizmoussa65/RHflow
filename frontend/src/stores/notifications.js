import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import notificationService from '@/services/notificationService.js'
import { getSocket } from '@/services/socket.js'

export const useNotificationStore = defineStore('notifications', () => {
  const items = ref([])
  const unreadCount = computed(() => items.value.filter(n => !n.read).length)

  let listening = false

  async function refresh() {
    try { items.value = await notificationService.getAll() } catch (_) {}
  }

  async function markRead(id) {
    const n = items.value.find(x => x.id === id)
    if (n) n.read = true
    try { await notificationService.markRead(id) } catch (_) {}
  }

  async function markAllRead() {
    items.value.forEach(n => { n.read = true })
    try { await notificationService.markAllRead() } catch (_) {}
  }

  function listen() {
    if (listening) return
    listening = true
    getSocket().on('notification', (n) => { items.value.unshift(n) })
  }

  return { items, unreadCount, refresh, markRead, markAllRead, listen }
})
