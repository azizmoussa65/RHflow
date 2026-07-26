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
  const isManager = computed(() => role.value === 'MANAGER')
  const isRH = computed(() => role.value === 'RH')
  const isEmploye = computed(() => role.value === 'EMPLOYE')
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
    'manager@hrflow.tn': {
      id: 1, email: 'manager@hrflow.tn', prenom: 'Ahmed', nom: 'Mansouri',
      role: 'MANAGER', departement: 'Direction Générale', poste: 'Manager Général',
      initials: 'AM', statut: 'Actif'
    },
    'rh@hrflow.tn': {
      id: 2, email: 'rh@hrflow.tn', prenom: 'Omar', nom: 'Trabelsi',
      role: 'RH', departement: 'Ressources Humaines', poste: 'Responsable RH',
      initials: 'OT', statut: 'Actif'
    },
    'employe@hrflow.tn': {
      id: 3, email: 'employe@hrflow.tn', prenom: 'Employé', nom: 'Demo',
      role: 'EMPLOYE', departement: 'Développement', poste: 'Agent RH',
      initials: 'ED', statut: 'Actif'
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
    if (demoUser && password === 'password') {
      const fakeToken = 'demo_token_' + demoUser.role
      token.value = fakeToken
      user.value  = demoUser
      localStorage.setItem('hrflow_token', fakeToken)
      localStorage.setItem('hrflow_user',  JSON.stringify(demoUser))
      return { success: true }
    }

    return { success: false, message: 'Impossible de joindre le serveur. Vérifiez que le backend est lancé (php -S localhost:8000 -t public).' }
  }

  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('hrflow_token')
    localStorage.removeItem('hrflow_user')
  }

  return {
    token, user,
    isAuthenticated, role, isManager, isRH, isEmploye,
    userInitials, userFullName,
    login, logout
  }
})
