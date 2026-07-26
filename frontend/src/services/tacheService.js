import api from './api.js'

const tacheService = {
  async getAll(projetId) {
    const r = await api.get(`/projets/${projetId}/taches`); return r.data
  },
  async create(projetId, data) {
    const r = await api.post(`/projets/${projetId}/taches`, data); return r.data
  },
  async update(projetId, id, data) {
    const r = await api.put(`/projets/${projetId}/taches/${id}`, data); return r.data
  },
  async delete(projetId, id) {
    const r = await api.delete(`/projets/${projetId}/taches/${id}`); return r.data
  }
}
export default tacheService
