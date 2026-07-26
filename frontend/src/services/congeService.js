import api from './api.js'

const congeService = {
  async getAll(params = {}) {
    const r = await api.get('/conges', { params }); return r.data
  },
  async getById(id) {
    const r = await api.get(`/conges/${id}`); return r.data
  },
  async create(data) {
    const r = await api.post('/conges', data); return r.data
  },
  async update(id, data) {
    const r = await api.put(`/conges/${id}`, data); return r.data
  },
  async delete(id) {
    const r = await api.delete(`/conges/${id}`); return r.data
  },
  // PATCH /api/conges/:id/approve
  async approve(id) {
    const r = await api.patch(`/conges/${id}/approve`); return r.data
  },
  // PATCH /api/conges/:id/refuse
  async refuse(id) {
    const r = await api.patch(`/conges/${id}/refuse`); return r.data
  }
}
export default congeService
