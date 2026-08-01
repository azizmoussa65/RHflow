import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import messageService from '@/services/messageService.js'
import { getSocket } from '@/services/socket.js'

// Single shared source of truth for conversations/unread counts, so the
// Topbar dropdown and the full Messagerie page never drift out of sync.
export const useMessagingStore = defineStore('messaging', () => {
  const conversations = ref([])
  const totalUnread = computed(() => conversations.value.reduce((s, c) => s + (c.unreadCount || 0), 0))

  let listening = false

  async function refresh() {
    try { conversations.value = await messageService.getConversations() } catch (_) {}
  }

  function markConversationRead(id) {
    const c = conversations.value.find(x => x.id === id)
    if (c) c.unreadCount = 0
  }

  function listen() {
    if (listening) return
    listening = true
    const socket = getSocket()
    socket.on('new_message', refresh)
    socket.on('conversation_updated', refresh)
  }

  return { conversations, totalUnread, refresh, markConversationRead, listen }
})
