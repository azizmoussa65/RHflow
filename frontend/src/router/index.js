import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'

/**
 * Route definitions with role-based meta guards.
 * meta.requiresAuth: true → user must be logged in
 * meta.roles: ['MANAGER','RH','EMPLOYE'] → allowed roles (empty = all authenticated)
 */
const routes = [
  // Public
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/',
    redirect: '/dashboard'
  },

  // Authenticated layout
  {
    path: '/',
    component: () => import('@/components/layout/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      // MANAGER routes
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/views/manager/DashboardView.vue'),
        meta: { requiresAuth: true, roles: ['MANAGER', 'RH'] }
      },
      {
        path: 'employes',
        name: 'Employes',
        component: () => import('@/views/shared/EmployesView.vue'),
        meta: { requiresAuth: true, roles: ['MANAGER', 'RH'] }
      },
      {
        path: 'evaluations',
        name: 'Evaluations',
        component: () => import('@/views/manager/EvaluationsView.vue'),
        meta: { requiresAuth: true, roles: ['MANAGER'] }
      },
      // RH routes
      {
        path: 'contrats',
        name: 'Contrats',
        component: () => import('@/views/rh/ContratsView.vue'),
        meta: { requiresAuth: true, roles: ['MANAGER', 'RH'] }
      },
      {
        path: 'dossiers',
        name: 'Dossiers',
        component: () => import('@/views/rh/DossiersView.vue'),
        meta: { requiresAuth: true, roles: ['MANAGER', 'RH'] }
      },
      {
        path: 'conges',
        name: 'Conges',
        component: () => import('@/views/rh/CongesView.vue'),
        meta: { requiresAuth: true, roles: ['MANAGER', 'RH'] }
      },
      // Shared project view (different UI per role)
      {
        path: 'projets',
        name: 'Projets',
        component: () => import('@/views/shared/ProjetsView.vue'),
        meta: { requiresAuth: true, roles: ['MANAGER', 'RH', 'EMPLOYE'] }
      },
      // Employé routes
      {
        path: 'profil',
        name: 'Profil',
        component: () => import('@/views/employe/ProfilView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'mes-conges',
        name: 'MesConges',
        component: () => import('@/views/employe/CongeEmployeView.vue'),
        meta: { requiresAuth: true, roles: ['EMPLOYE'] }
      },
      {
        path: 'mes-dossiers',
        name: 'MesDossiers',
        component: () => import('@/views/employe/DossierEmployeView.vue'),
        meta: { requiresAuth: true, roles: ['EMPLOYE'] }
      },
      {
        path: 'parametres',
        name: 'Parametres',
        component: () => import('@/views/shared/ParametresView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'calendrier',
        name: 'Calendrier',
        component: () => import('@/views/shared/CalendrierView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'dashboard-rh',
        name: 'DashboardRH',
        component: () => import('@/views/rh/DashboardRHView.vue'),
        meta: { requiresAuth: true, roles: ['RH', 'MANAGER'] }
      }
    ]
  },

  // Fallback
  {
    path: '/:pathMatch(.*)*',
    redirect: '/dashboard'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

/**
 * Navigation guard:
 * 1. If route requires auth and user is not logged in → redirect to /login
 * 2. If route has role restrictions and user's role is not allowed → redirect to /dashboard
 */
router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next('/login')
  }
  if (to.path === '/login' && auth.isAuthenticated) {
    return next(getDefaultRouteForRole(auth.role))
  }
  if (to.meta.roles && to.meta.roles.length > 0 && auth.isAuthenticated) {
    if (!to.meta.roles.includes(auth.role)) {
      return next(getDefaultRouteForRole(auth.role))
    }
  }
  next()
})

/** Returns the default page after login based on role */
function getDefaultRouteForRole(role) {
  switch (role) {
    case 'MANAGER': return '/dashboard'
    case 'RH':      return '/employes'
    case 'EMPLOYE': return '/profil'
    default:        return '/dashboard'
  }
}

export default router
