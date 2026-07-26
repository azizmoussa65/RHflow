import api from './api.js'

const employeService = {
  // GET /api/employes?departement=...&statut=...&search=...
  async getAll(params = {}) {
    const response = await api.get('/employes', { params })
    return response.data
  },
  // GET /api/employes/:id
  async getById(id) {
    const response = await api.get(`/employes/${id}`)
    return response.data
  },
  // POST /api/employes
  async create(data) {
    const response = await api.post('/employes', data)
    return response.data
  },
  // PUT /api/employes/:id
  async update(id, data) {
    const response = await api.put(`/employes/${id}`, data)
    return response.data
  },
  // DELETE /api/employes/:id
  async delete(id) {
    const response = await api.delete(`/employes/${id}`)
    return response.data
  }
}
export default employeService
