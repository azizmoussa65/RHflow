import api from './api.js'

const recrutementService = {
  async analyser(file, poste, description) {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('poste', poste)
    formData.append('description', description)
    const r = await api.post('/recrutement/analyser', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      timeout: 60000
    })
    return r.data
  },
  async getAll() {
    const r = await api.get('/recrutement/candidatures'); return r.data
  },
  async getById(id) {
    const r = await api.get(`/recrutement/candidatures/${id}`); return r.data
  },
  async delete(id) {
    const r = await api.delete(`/recrutement/candidatures/${id}`); return r.data
  }
}
export default recrutementService
