import api from './api.js'

const dossierService = {
  async getAll(params = {}) {
    const r = await api.get('/dossiers', { params }); return r.data
  },
  async getById(id) {
    const r = await api.get(`/dossiers/${id}`); return r.data
  },
  // Upload uses FormData (multipart)
  async create(formData) {
    const r = await api.post('/dossiers', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    return r.data
  },
  async update(id, data) {
    const r = await api.put(`/dossiers/${id}`, data); return r.data
  },
  async delete(id) {
    const r = await api.delete(`/dossiers/${id}`); return r.data
  },
  // Validate / Refuse for RH
  async valider(id) {
    const r = await api.patch(`/dossiers/${id}/valider`); return r.data
  },
  async refuser(id) {
    const r = await api.patch(`/dossiers/${id}/refuser`); return r.data
  }
}
export default dossierService
