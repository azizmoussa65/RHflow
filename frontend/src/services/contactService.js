import api from './api.js'

const contactService = {
  async submit(data) {
    const r = await api.post('/contact', data); return r.data
  },
  async getAll() {
    const r = await api.get('/contact'); return r.data
  },
  async marquerTraite(id) {
    const r = await api.patch(`/contact/${id}/traiter`); return r.data
  }
}
export default contactService
