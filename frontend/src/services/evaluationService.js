import api from './api.js'

const evaluationService = {
  async getAll(params = {}) {
    const r = await api.get('/evaluations', { params }); return r.data
  },
  async getById(id) {
    const r = await api.get(`/evaluations/${id}`); return r.data
  },
  async create(data) {
    const r = await api.post('/evaluations', data); return r.data
  },
  async update(id, data) {
    const r = await api.put(`/evaluations/${id}`, data); return r.data
  },
  async delete(id) {
    const r = await api.delete(`/evaluations/${id}`); return r.data
  }
}
export default evaluationService
