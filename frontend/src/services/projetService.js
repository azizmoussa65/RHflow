import api from './api.js'

const projetService = {
  async getAll(params = {}) {
    const r = await api.get('/projets', { params }); return r.data
  },
  async getById(id) {
    const r = await api.get(`/projets/${id}`); return r.data
  },
  async create(data) {
    const r = await api.post('/projets', data); return r.data
  },
  async update(id, data) {
    const r = await api.put(`/projets/${id}`, data); return r.data
  },
  async delete(id) {
    const r = await api.delete(`/projets/${id}`); return r.data
  }
}
export default projetService
