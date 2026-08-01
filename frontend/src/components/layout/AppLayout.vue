<template>
  <!-- Full app layout: sidebar + topbar + page content -->
  <aside class="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo flex items-center gap-3">
      <div class="logo-mark"><img src="/logo-icon.png" alt="HRFlow" /></div>
      <div>
        <div style="font-size:14px;font-weight:700;color:var(--text-primary)">HRFlow</div>
        <div style="font-size:11px;color:var(--text-muted);font-family:'JetBrains Mono'">v2.4.0</div>
      </div>
    </div>

    <!-- Navigation -->
    <div style="flex:1;overflow-y:auto;padding:12px 0">
      <!-- Admin section -->
      <template v-if="auth.isAdmin">
        <div class="nav-section">
          <div class="nav-label">Vue générale</div>
          <RouterLink class="nav-item" to="/dashboard">
            <span class="icon"><i class="fa-solid fa-chart-pie"></i></span>
            Tableau de bord
          </RouterLink>
        </div>
        <div class="nav-section">
          <div class="nav-label">Ressources Humaines</div>
          <RouterLink class="nav-item" to="/employes">
            <span class="icon"><i class="fa-solid fa-users"></i></span>
            Employés
          </RouterLink>
          <RouterLink class="nav-item" to="/contrats">
            <span class="icon"><i class="fa-solid fa-file-contract"></i></span>
            Contrats
          </RouterLink>
          <RouterLink class="nav-item" to="/conges">
            <span class="icon"><i class="fa-solid fa-umbrella-beach"></i></span>
            Congés
            <span v-if="pendingConges > 0" class="nav-badge">{{ pendingConges }}</span>
          </RouterLink>
          <RouterLink class="nav-item" to="/stagiaires">
            <span class="icon"><i class="fa-solid fa-user-graduate"></i></span>
            Stagiaires
          </RouterLink>
          <RouterLink class="nav-item" to="/recrutement">
            <span class="icon"><i class="fa-solid fa-file-import"></i></span>
            Recrutement
          </RouterLink>
          <RouterLink class="nav-item" to="/demandes-demo">
            <span class="icon"><i class="fa-solid fa-envelope-open-text"></i></span>
            Demandes de démo
          </RouterLink>
        </div>
        <div class="nav-section">
          <div class="nav-label">Projets & Performance</div>
          <RouterLink class="nav-item" to="/projets">
            <span class="icon"><i class="fa-solid fa-diagram-project"></i></span>
            Projets
          </RouterLink>
          <RouterLink class="nav-item" to="/evaluations">
            <span class="icon"><i class="fa-solid fa-star-half-stroke"></i></span>
            Évaluations
          </RouterLink>
        </div>
      </template>

      <!-- RH section -->
      <template v-if="auth.isRH">
        <div class="nav-section">
          <div class="nav-label">Vue générale</div>
          <RouterLink class="nav-item" to="/dashboard-rh">
            <span class="icon"><i class="fa-solid fa-chart-pie"></i></span>
            Tableau de bord
          </RouterLink>
        </div>
        <div class="nav-section">
          <div class="nav-label">Gestion RH</div>
          <RouterLink class="nav-item" to="/employes">
            <span class="icon"><i class="fa-solid fa-users"></i></span>
            Gérer Employés
          </RouterLink>
          <RouterLink class="nav-item" to="/contrats">
            <span class="icon"><i class="fa-solid fa-file-contract"></i></span>
            Gérer Contrats
          </RouterLink>
          <RouterLink class="nav-item" to="/dossiers">
            <span class="icon"><i class="fa-solid fa-folder-open"></i></span>
            Dossiers Admin.
          </RouterLink>
          <RouterLink class="nav-item" to="/conges">
            <span class="icon"><i class="fa-solid fa-umbrella-beach"></i></span>
            Gérer Congés
            <span v-if="pendingConges > 0" class="nav-badge" style="background:#f59e0b">{{ pendingConges }}</span>
          </RouterLink>
          <RouterLink class="nav-item" to="/projets">
            <span class="icon"><i class="fa-solid fa-diagram-project"></i></span>
            Projets
          </RouterLink>
          <RouterLink class="nav-item" to="/stagiaires">
            <span class="icon"><i class="fa-solid fa-user-graduate"></i></span>
            Stagiaires
          </RouterLink>
          <RouterLink class="nav-item" to="/recrutement">
            <span class="icon"><i class="fa-solid fa-file-import"></i></span>
            Recrutement
          </RouterLink>
          <RouterLink class="nav-item" to="/demandes-demo">
            <span class="icon"><i class="fa-solid fa-envelope-open-text"></i></span>
            Demandes de démo
          </RouterLink>
        </div>
      </template>

      <!-- Employé section -->
      <template v-if="auth.isEmploye">
        <div class="nav-section">
          <div class="nav-label">Mon espace</div>
          <RouterLink class="nav-item" to="/profil">
            <span class="icon"><i class="fa-solid fa-user-circle"></i></span>
            Mon Profil
          </RouterLink>
          <RouterLink class="nav-item" to="/mes-dossiers">
            <span class="icon"><i class="fa-solid fa-folder-open"></i></span>
            Mes Dossiers
          </RouterLink>
          <RouterLink class="nav-item" to="/mes-conges">
            <span class="icon"><i class="fa-solid fa-umbrella-beach"></i></span>
            Mes Congés
          </RouterLink>
          <RouterLink class="nav-item" to="/projets">
            <span class="icon"><i class="fa-solid fa-diagram-project"></i></span>
            Mes Projets
          </RouterLink>
        </div>
      </template>

      <!-- Stagiaire section -->
      <template v-if="auth.isStagiaire">
        <div class="nav-section">
          <div class="nav-label">Mon espace</div>
          <RouterLink class="nav-item" to="/profil">
            <span class="icon"><i class="fa-solid fa-user-circle"></i></span>
            Mon Profil
          </RouterLink>
          <RouterLink class="nav-item" to="/projets">
            <span class="icon"><i class="fa-solid fa-diagram-project"></i></span>
            Mes Projets & Avancement
          </RouterLink>
        </div>
      </template>

      <div class="nav-section">
        <div class="nav-label">Compte</div>
        <RouterLink class="nav-item" to="/messages">
          <span class="icon"><i class="fa-solid fa-comments"></i></span>
          Messagerie
        </RouterLink>
        <RouterLink class="nav-item" to="/profil">
          <span class="icon"><i class="fa-solid fa-user-circle"></i></span>
          Mon Profil
        </RouterLink>
        <RouterLink class="nav-item" to="/parametres">
          <span class="icon"><i class="fa-solid fa-gear"></i></span>
          Paramètres
        </RouterLink>
      </div>
    </div>

    <!-- Footer user info -->
    <div class="sidebar-footer">
      <div class="flex items-center gap-3">
        <div class="user-avatar" style="overflow:hidden">
          <img v-if="photoUrl" :src="photoUrl" style="width:100%;height:100%;object-fit:cover" />
          <template v-else>{{ auth.userInitials }}</template>
        </div>
        <div style="flex:1;min-width:0">
          <div class="truncate" style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ auth.userFullName }}</div>
          <div style="font-size:11px;color:var(--text-muted)">{{ roleLabel }}</div>
        </div>
        <button @click="handleLogout" style="color:var(--text-muted);background:none;border:none;cursor:pointer;font-size:14px" title="Déconnexion">
          <i class="fa-solid fa-right-from-bracket"></i>
        </button>
      </div>
    </div>
  </aside>

  <!-- Main content -->
  <div class="main-content">
    <TopbarComponent />
    <RouterView v-slot="{ Component }">
      <Transition name="fade-up" mode="out-in">
        <component :is="Component" />
      </Transition>
    </RouterView>
  </div>

  <!-- Chatbot flottant -->
  <ChatbotWidget />
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import TopbarComponent from './TopbarComponent.vue'
import ChatbotWidget from '@/components/shared/ChatbotWidget.vue'
import congeService from '@/services/congeService.js'
import { getSocket, disconnectSocket } from '@/services/socket.js'
import { avatarUrl } from '@/utils/avatar.js'
import { useNotificationStore } from '@/stores/notifications.js'

const notifications = useNotificationStore()

const auth = useAuthStore()
const router = useRouter()
const pendingConges = ref(0)
const photoUrl = computed(() => avatarUrl(auth.user))

const roleLabel = computed(() => {
  const map = { ADMIN: 'Administrateur', RH: 'Responsable RH', EMPLOYE: 'Employé', STAGIAIRE: 'Stagiaire' }
  return map[auth.role] || ''
})

async function handleLogout() {
  auth.logout()
  router.push('/login')
}

onMounted(async () => {
  // Fetch pending congés count for badge (only RH and Admin)
  if (auth.isAdmin || auth.isRH) {
    try {
      const data = await congeService.getAll({ statut: 'EN_ATTENTE' })
      pendingConges.value = Array.isArray(data) ? data.length : (data.total || 0)
    } catch (_) { /* silent */ }
  }

  // Socket.IO connects once for the whole authenticated session (Topbar and
  // MessagesView attach/detach their own listeners on this shared instance).
  getSocket().connect()

  notifications.refresh()
  notifications.listen()
})

onBeforeUnmount(() => {
  disconnectSocket()
})
</script>

<style scoped>
.fade-up-enter-active { animation: fadeUp 0.3s ease; }
.fade-up-leave-active { animation: fadeUp 0.2s ease reverse; }
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: none; }
}
</style>
