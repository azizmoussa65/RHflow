import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import authService from '@/services/authService.js'

export const useAuthStore = defineStore('auth', () => {
  // State
  const token = ref(localStorage.getItem('hrflow_token') || null)
  const user = ref(JSON.parse(localStorage.getItem('hrflow_user') || 'null'))

  // Getters
  const isAuthenticated = computed(() => !!token.value)
  const role = computed(() => user.value?.role || null)
  const isAdmin = computed(() => role.value === 'ADMIN')
  const isRH = computed(() => role.value === 'RH')
  const isEmploye = computed(() => role.value === 'EMPLOYE')
  const isStagiaire = computed(() => role.value === 'STAGIAIRE')
  const userInitials = computed(() => {
    if (!user.value) return 'U'
    return `${(user.value.prenom || '')[0] || ''}${(user.value.nom || '')[0] || ''}`.toUpperCase()
  })
  const userFullName = computed(() => {
    if (!user.value) return ''
    return `${user.value.prenom || ''} ${user.value.nom || ''}`.trim()
  })

  // ── Demo accounts (used as fallback when backend is offline) ──────────────
  const DEMO_USERS = {
    'admin@satisfyinsight.cm': {
      id: 1, email: 'admin@satisfyinsight.cm', prenom: 'Admin', nom: 'Satisfy',
      role: 'ADMIN', departement: 'Direction Générale', poste: 'Administrateur',
      initials: 'AS', statut: 'Actif'
    },
    'rh@satisfyinsight.com': {
      id: 2, email: 'rh@satisfyinsight.com', prenom: 'RH', nom: 'Satisfy',
      role: 'RH', departement: 'Ressources Humaines', poste: 'Responsable RH',
      initials: 'RS', statut: 'Actif'
    },
  }

  // Actions
  async function login(email, password) {
    try {
      const response = await authService.login(email, password)
      token.value = response.token
      user.value  = response.user
      localStorage.setItem('hrflow_token', response.token)
      localStorage.setItem('hrflow_user',  JSON.stringify(response.user))
      return { success: true }
    } catch (err) {
      const status = err.response?.status
      if (status === 401) {
        return { success: false, message: 'Email ou mot de passe incorrect.' }
      }
      // Réseau inaccessible → mode démo
    }

    // Mode démo (backend hors ligne)
    const demoUser = DEMO_USERS[email.toLowerCase()]
    if (demoUser && password === 'password123') {
      const fakeToken = 'demo_token_' + demoUser.role
      token.value = fakeToken
      user.value  = demoUser
      localStorage.setItem('hrflow_token', fakeToken)
      localStorage.setItem('hrflow_user',  JSON.stringify(demoUser))
      return { success: true }
    }

    return { success: false, message: 'Impossible de joindre le serveur. Vérifiez que le backend est lancé (python run.py).' }
  }

  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('hrflow_token')
    localStorage.removeItem('hrflow_user')
  }

  return {
    token, user,
    isAuthenticated, role, isAdmin, isRH, isEmploye, isStagiaire,
    userInitials, userFullName,
    login, logout
  }
})
