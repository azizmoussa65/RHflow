import api from './api.js'

const authService = {
  /**
   * POST /api/auth/login
   * Returns { token, user: { id, email, prenom, nom, role } }
   */
  async login(email, password) {
    const response = await api.post('/auth/login', { email, password })
    return response.data
  },

  /**
   * GET /api/auth/me
   * Returns current authenticated user
   */
  async me() {
    const response = await api.get('/auth/me')
    return response.data
  },

  /**
   * POST /api/auth/avatar (multipart)
   * Returns the updated user object with avatarUrl set
   */
  async uploadAvatar(file) {
    const formData = new FormData()
    formData.append('file', file)
    const response = await api.post('/auth/avatar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    return response.data
  }
}

export default authService
