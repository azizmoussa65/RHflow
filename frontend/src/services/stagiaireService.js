import api from './api.js'

const stagiaireService = {
  async getAll(params = {}) {
    const response = await api.get('/stagiaires', { params })
    return response.data
  },
  async getById(id) {
    const response = await api.get(`/stagiaires/${id}`)
    return response.data
  },
  async create(data) {
    const response = await api.post('/stagiaires', data)
    return response.data
  },
  async update(id, data) {
    const response = await api.put(`/stagiaires/${id}`, data)
    return response.data
  },
  async delete(id) {
    const response = await api.delete(`/stagiaires/${id}`)
    return response.data
  }
}
export default stagiaireService
