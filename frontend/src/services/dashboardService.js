import api from './api.js'

const dashboardService = {
  // GET /api/dashboard/stats
  async getStats() {
    const r = await api.get('/dashboard/stats')
    return r.data
  }
}
export default dashboardService
