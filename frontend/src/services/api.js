import axios from 'axios'
import { useAuthStore } from '@/stores/auth.js'
import router from '@/router/index.js'

const api = axios.create({
  baseURL: '/api',
  headers: { 'Content-Type': 'application/json' },
  timeout: 10000
})

// Attach JWT token to every request
api.interceptors.request.use(config => {
  const token = localStorage.getItem('hrflow_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
}, error => Promise.reject(error))

// Global error handling
api.interceptors.response.use(
  response => response,
  error => {
    const status = error.response?.status

    if (status === 401) {
      // Token invalid or expired — logout and redirect
      const auth = useAuthStore()
      auth.logout()
      router.push('/login')
    }

    // Enrich error with a human-readable message
    if (!error.userMessage) {
      if (!error.response) {
        error.userMessage = 'Serveur hors ligne. Lancez le fichier start.bat sur le bureau pour démarrer l\'application.'
      } else if (status === 403) {
        error.userMessage = 'Accès refusé. Vous n\'avez pas les droits pour cette action.'
      } else if (status === 404) {
        error.userMessage = 'Ressource introuvable.'
      } else if (status === 422 || status === 400) {
        error.userMessage = error.response?.data?.message || 'Données invalides.'
      } else {
        error.userMessage = error.response?.data?.message || 'Erreur serveur inattendue.'
      }
    }

    return Promise.reject(error)
  }
)

export default api
