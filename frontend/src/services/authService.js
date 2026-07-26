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
  }
}

export default authService
