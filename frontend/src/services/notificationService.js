import api from './api.js'

const notificationService = {
  async getAll() {
    const r = await api.get('/notifications'); return r.data
  },
  async markRead(id) {
    const r = await api.patch(`/notifications/${id}/read`); return r.data
  },
  async markAllRead() {
    const r = await api.patch('/notifications/read-all'); return r.data
  }
}
export default notificationService
