import api from './api.js'

const messageService = {
  async directory() {
    const r = await api.get('/messages/directory'); return r.data
  },
  async getConversations() {
    const r = await api.get('/messages/conversations'); return r.data
  },
  async startConversation(userId) {
    const r = await api.post('/messages/conversations', { userId }); return r.data
  },
  async getMessages(conversationId) {
    const r = await api.get(`/messages/conversations/${conversationId}/messages`); return r.data
  },
  async upload(file) {
    const formData = new FormData()
    formData.append('file', file)
    const r = await api.post('/messages/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    return r.data
  }
}
export default messageService
