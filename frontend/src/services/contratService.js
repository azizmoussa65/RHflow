import api from './api.js'

const contratService = {
  async getAll(params = {}) {
    const r = await api.get('/contrats', { params }); return r.data
  },
  async getById(id) {
    const r = await api.get(`/contrats/${id}`); return r.data
  },
  async create(data) {
    const r = await api.post('/contrats', data); return r.data
  },
  async update(id, data) {
    const r = await api.put(`/contrats/${id}`, data); return r.data
  },
  async delete(id) {
    const r = await api.delete(`/contrats/${id}`); return r.data
  }
}
export default contratService
