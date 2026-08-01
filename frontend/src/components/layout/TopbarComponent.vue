<template>
  <header class="topbar">
    <!-- Barre de recherche -->
    <div class="search-bar" style="position:relative">
      <i class="fa-solid fa-search" style="font-size:13px;color:var(--text-muted)"></i>
      <input
        type="text"
        placeholder="Rechercher employé, projet, contrat..."
        v-model="searchQuery"
        @input="onSearch"
        @focus="showResults = searchQuery.length > 0"
        @keydown.escape="closeSearch"
        @keydown.enter="goFirst"
      />
      <span v-if="!searchQuery" style="font-size:11px;color:var(--text-muted);font-family:'JetBrains Mono'">⌘K</span>
      <button v-else @click="clearSearch" style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:12px;padding:0 4px">
        <i class="fa-solid fa-xmark"></i>
      </button>

      <!-- Dropdown résultats -->
      <div v-if="showResults && searchQuery.length >= 1" class="search-dropdown" @mousedown.prevent>
        <div v-if="searchResults.length === 0" style="padding:20px;text-align:center;color:var(--text-muted);font-size:13px">
          <i class="fa-solid fa-magnifying-glass" style="font-size:18px;margin-bottom:8px;display:block;opacity:0.4"></i>
          Aucun résultat pour « {{ searchQuery }} »
        </div>

        <template v-for="group in groupedResults" :key="group.label">
          <div style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;padding:8px 14px 4px">
            {{ group.label }}
          </div>
          <div v-for="r in group.items" :key="r.id"
            class="search-item"
            @click="navigateTo(r)">
            <div class="search-item-icon" :style="{ background: r.color }">
              <i :class="r.icon"></i>
            </div>
            <div style="flex:1;min-width:0">
              <div style="font-size:13px;font-weight:500;color:var(--text-primary)" v-html="highlight(r.label)"></div>
              <div style="font-size:11px;color:var(--text-muted)">{{ r.sub }}</div>
            </div>
            <i class="fa-solid fa-arrow-right" style="font-size:11px;color:var(--text-muted)"></i>
          </div>
        </template>
      </div>
    </div>

    <div class="flex items-center gap-4" style="margin-left:auto">
      <div style="font-size:11px;color:var(--text-muted);font-family:'JetBrains Mono'">{{ currentDate }}</div>

      <!-- Notifications -->
      <div style="position:relative">
        <button class="icon-btn" @click="showNotifs = !showNotifs" title="Notifications">
          <i class="fa-solid fa-bell" style="font-size:17px"></i>
          <span v-if="notifStore.unreadCount > 0" class="notif-dot">{{ notifStore.unreadCount }}</span>
          <span v-else-if="allNotifs.length > 0" class="notif-dot"></span>
        </button>

        <div v-if="showNotifs" class="notif-dropdown" @click.stop>
          <div style="font-size:13px;font-weight:600;color:var(--text-primary);padding:12px 16px;border-bottom:1px solid var(--border)">
            <i class="fa-solid fa-bell" style="margin-right:8px;color:#3b82f6"></i>Notifications
          </div>
          <div v-if="allNotifs.length === 0" style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">
            Aucune notification
          </div>
          <div v-for="n in allNotifs" :key="n.key" class="notif-item" :class="{ 'notif-urgent': n.urgent }"
            :style="n.real ? 'cursor:pointer' : ''" @click="n.real && onNotifClick(n)">
            <div style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0"
              :style="{ background: n.urgent ? 'rgba(239,68,68,0.15)' : 'rgba(59,130,246,0.15)' }">
              <i :class="n.icon" :style="{ color: n.urgent ? '#ef4444' : '#3b82f6', fontSize:'14px' }"></i>
            </div>
            <div style="flex:1;min-width:0">
              <div style="font-size:12px;font-weight:600;color:var(--text-primary)">{{ n.titre }}</div>
              <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ n.message }}</div>
            </div>
            <span v-if="n.real && !n.readState" style="width:8px;height:8px;border-radius:50%;background:#3b82f6;flex-shrink:0;margin-top:4px"></span>
          </div>
          <div style="padding:10px 16px;border-top:1px solid var(--border)">
            <RouterLink to="/calendrier" style="font-size:12px;color:#3b82f6;text-decoration:none" @click="showNotifs=false">
              Voir le calendrier →
            </RouterLink>
          </div>
        </div>
      </div>

      <!-- Messages -->
      <div style="position:relative">
        <button class="icon-btn" @click="toggleMessages" title="Messages">
          <i class="fa-solid fa-envelope" style="font-size:17px"></i>
          <span v-if="totalUnread > 0" class="notif-dot" style="background:#3b82f6">{{ totalUnread }}</span>
        </button>

        <div v-if="showMessages" class="notif-dropdown" style="width:360px" @click.stop>
          <!-- Header -->
          <div style="font-size:13px;font-weight:600;color:var(--text-primary);padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            <span><i class="fa-solid fa-envelope" style="margin-right:8px;color:#3b82f6"></i>Messages</span>
            <RouterLink to="/messages" style="font-size:11px;background:rgba(59,130,246,0.1);color:#3b82f6;border-radius:6px;padding:4px 10px;text-decoration:none" @click="showMessages=false">
              <i class="fa-solid fa-pen"></i> Nouveau
            </RouterLink>
          </div>

          <!-- Liste conversations -->
          <div v-if="conversations.length === 0" style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">
            <i class="fa-solid fa-inbox" style="font-size:24px;display:block;margin-bottom:8px;opacity:0.3"></i>
            Aucun message
          </div>
          <div v-for="c in conversations" :key="c.id"
            class="notif-item" style="cursor:pointer"
            :style="c.unreadCount > 0 ? 'background:rgba(59,130,246,0.05)' : ''"
            @click="openConversation(c)">
            <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:12px;font-weight:700;color:#fff">
              {{ c.otherUser?.initials }}
            </div>
            <div style="flex:1;min-width:0">
              <div style="display:flex;justify-content:space-between;align-items:center">
                <div style="font-size:12px;font-weight:600;color:var(--text-primary)">{{ c.otherUser?.prenom }} {{ c.otherUser?.nom }}</div>
                <div style="font-size:10px;color:var(--text-muted)">{{ timeAgo(c.lastMessageAt) }}</div>
              </div>
              <div style="font-size:11px;color:var(--text-muted);margin-top:2px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis">{{ c.lastMessageText || 'Aucun message' }}</div>
            </div>
            <span v-if="c.unreadCount > 0" style="width:8px;height:8px;border-radius:50%;background:#3b82f6;flex-shrink:0;margin-left:4px"></span>
          </div>
          <div style="padding:10px 16px;border-top:1px solid var(--border)">
            <RouterLink to="/messages" style="font-size:12px;color:#3b82f6;text-decoration:none" @click="showMessages=false">
              Voir tous les messages →
            </RouterLink>
          </div>
        </div>
      </div>

      <div class="w-px h-6" style="background:var(--border)"></div>

      <button class="theme-toggle" @click="theme.toggleTheme" :title="theme.isDark ? 'Mode clair' : 'Mode sombre'">
        <Transition name="icon-switch" mode="out-in">
          <i v-if="theme.isDark" key="sun"  class="fa-solid fa-sun"  style="font-size:15px;color:#f59e0b"></i>
          <i v-else              key="moon" class="fa-solid fa-moon" style="font-size:15px;color:#3b82f6"></i>
        </Transition>
      </button>

      <div class="user-avatar" style="cursor:pointer;overflow:hidden" :title="auth.userFullName">
        <img v-if="photoUrl" :src="photoUrl" style="width:100%;height:100%;object-fit:cover" />
        <template v-else>{{ auth.userInitials }}</template>
      </div>
    </div>
  </header>

  <!-- Overlay fermer -->
  <div v-if="showNotifs || showResults || showMessages" style="position:fixed;inset:0;z-index:99"
    @click="showNotifs=false; showResults=false; showMessages=false; showCompose=false"></div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useThemeStore } from '@/stores/theme.js'
import { useAuthStore } from '@/stores/auth.js'
import { useMessagingStore } from '@/stores/messaging.js'
import { useNotificationStore } from '@/stores/notifications.js'
import employeService from '@/services/employeService.js'
import projetService   from '@/services/projetService.js'
import { avatarUrl } from '@/utils/avatar.js'

const theme     = useThemeStore()
const auth      = useAuthStore()
const router    = useRouter()
const messaging = useMessagingStore()
const notifStore = useNotificationStore()
const photoUrl  = computed(() => avatarUrl(auth.user))

const searchQuery  = ref('')
const showNotifs   = ref(false)
const showResults  = ref(false)
const showMessages = ref(false)

const conversations = computed(() => messaging.conversations)
const totalUnread   = computed(() => messaging.totalUnread)

function toggleMessages() {
  showMessages.value = !showMessages.value
  if (showMessages.value) messaging.refresh()
}

function openConversation(c) {
  showMessages.value = false
  router.push({ path: '/messages', query: { conversationId: c.id } })
}

function timeAgo(iso) {
  if (!iso) return ''
  const diff = (Date.now() - new Date(iso).getTime()) / 1000
  if (diff < 60) return 'à l\'instant'
  if (diff < 3600) return Math.floor(diff / 60) + ' min'
  if (diff < 86400) return Math.floor(diff / 3600) + ' h'
  return Math.floor(diff / 86400) + ' j'
}

const searchResults = ref([])

// ── Pages navigables ─────────────────────────────────────────────────
const PAGES = [
  { id:'p1', label:'Tableau de bord',   sub:'Vue générale Manager',   icon:'fa-solid fa-chart-pie',         color:'rgba(59,130,246,0.15)',  iconColor:'#3b82f6', route:'/dashboard',    keywords:['dashboard','tableau','bord'] },
  { id:'p2', label:'Tableau de bord RH',sub:'Présence & congés RH',   icon:'fa-solid fa-chart-bar',         color:'rgba(99,102,241,0.15)', iconColor:'#6366f1', route:'/dashboard-rh', keywords:['rh','dashboard','presence'] },
  { id:'p3', label:'Employés',          sub:'Gestion des employés',   icon:'fa-solid fa-users',             color:'rgba(16,185,129,0.15)', iconColor:'#10b981', route:'/employes',     keywords:['employe','employes','personnel','staff'] },
  { id:'p4', label:'Contrats',          sub:'Gestion des contrats',   icon:'fa-solid fa-file-contract',     color:'rgba(245,158,11,0.15)', iconColor:'#f59e0b', route:'/contrats',     keywords:['contrat','contrats','cdi','cdd'] },
  { id:'p5', label:'Congés',            sub:'Demandes & validation',  icon:'fa-solid fa-umbrella-beach',    color:'rgba(239,68,68,0.15)',  iconColor:'#ef4444', route:'/conges',       keywords:['conge','conges','absence','vacances'] },
  { id:'p6', label:'Projets',           sub:'Projets & tâches',       icon:'fa-solid fa-diagram-project',   color:'rgba(6,182,212,0.15)', iconColor:'#06b6d4', route:'/projets',      keywords:['projet','projets','tache','taches'] },
  { id:'p7', label:'Évaluations',       sub:'Évaluations des employés',icon:'fa-solid fa-star-half-stroke', color:'rgba(139,92,246,0.15)', iconColor:'#8b5cf6', route:'/evaluations',  keywords:['evaluation','evaluations','note'] },
  { id:'p8', label:'Dossiers Admin.',   sub:'Dossiers administratifs',icon:'fa-solid fa-folder-open',       color:'rgba(245,158,11,0.15)', iconColor:'#f59e0b', route:'/dossiers',     keywords:['dossier','dossiers','admin','administratif'] },
  { id:'p9', label:'Calendrier',        sub:'Jours fériés & événements',icon:'fa-solid fa-calendar-days',  color:'rgba(16,185,129,0.15)', iconColor:'#10b981', route:'/calendrier',   keywords:['calendrier','ferie','feries','agenda'] },
  { id:'p10',label:'Mon Profil',        sub:'Informations personnelles',icon:'fa-solid fa-user-circle',     color:'rgba(59,130,246,0.15)', iconColor:'#3b82f6', route:'/profil',       keywords:['profil','moi','personnel','compte'] },
  { id:'p11',label:'Paramètres',        sub:'Configuration application',icon:'fa-solid fa-gear',            color:'rgba(100,116,139,0.15)',iconColor:'#64748b', route:'/parametres',   keywords:['parametre','parametres','settings','config'] },
  { id:'p12',label:'Mes Congés',        sub:'Mes demandes de congé',  icon:'fa-solid fa-umbrella-beach',    color:'rgba(239,68,68,0.15)', iconColor:'#ef4444', route:'/mes-conges',   keywords:['mes conges','ma demande','mon conge'] },
  { id:'p13',label:'Mes Dossiers',      sub:'Mes dossiers personnels',icon:'fa-solid fa-folder-open',       color:'rgba(245,158,11,0.15)', iconColor:'#f59e0b', route:'/mes-dossiers', keywords:['mes dossiers','mon dossier'] },
]

// Données dynamiques chargées une fois
const employesData = ref([])
const projetsData  = ref([])

onMounted(async () => {
  try { employesData.value = await employeService.getAll() } catch (_) {}
  try { const d = await projetService.getAll(); projetsData.value = Array.isArray(d) ? d : (d.data || []) } catch (_) {}
  loadNotifs()
  messaging.refresh()
  messaging.listen()
})

// ── Recherche ─────────────────────────────────────────────────────────
function onSearch() {
  showResults.value = true
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) { searchResults.value = []; return }

  const results = []

  // Pages
  PAGES.forEach(p => {
    if (
      p.label.toLowerCase().includes(q) ||
      p.sub.toLowerCase().includes(q) ||
      p.keywords.some(k => k.includes(q))
    ) {
      results.push({ ...p, type: 'page' })
    }
  })

  // Employés
  employesData.value.forEach(e => {
    const full = `${e.prenom} ${e.nom}`.toLowerCase()
    const dept = (e.departement || '').toLowerCase()
    const post = (e.poste || '').toLowerCase()
    if (full.includes(q) || dept.includes(q) || post.includes(q)) {
      results.push({
        id: 'emp_' + e.id,
        label: `${e.prenom} ${e.nom}`,
        sub: `${e.poste || 'Employé'} · ${e.departement || ''}`,
        icon: 'fa-solid fa-user',
        color: 'rgba(16,185,129,0.15)',
        iconColor: '#10b981',
        route: '/employes',
        type: 'employe',
      })
    }
  })

  // Projets
  projetsData.value.forEach(p => {
    if ((p.nom || '').toLowerCase().includes(q) || (p.categorie || '').toLowerCase().includes(q)) {
      results.push({
        id: 'proj_' + p.id,
        label: p.nom,
        sub: `${p.categorie || 'Projet'} · ${p.statut || ''}`,
        icon: 'fa-solid fa-diagram-project',
        color: 'rgba(6,182,212,0.15)',
        iconColor: '#06b6d4',
        route: '/projets',
        type: 'projet',
      })
    }
  })

  searchResults.value = results.slice(0, 12)
}

const groupedResults = computed(() => {
  const groups = {}
  searchResults.value.forEach(r => {
    const label = r.type === 'page' ? 'Pages' : r.type === 'employe' ? 'Employés' : 'Projets'
    if (!groups[label]) groups[label] = { label, items: [] }
    groups[label].items.push(r)
  })
  return Object.values(groups)
})

function highlight(text) {
  const q = searchQuery.value.trim()
  if (!q) return text
  const re = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi')
  return text.replace(re, '<mark style="background:rgba(59,130,246,0.2);color:#3b82f6;border-radius:3px;padding:0 2px">$1</mark>')
}

function navigateTo(r) {
  router.push(r.route)
  closeSearch()
}

function goFirst() {
  if (searchResults.value.length > 0) navigateTo(searchResults.value[0])
}

function clearSearch()  { searchQuery.value = ''; searchResults.value = []; showResults.value = false }
function closeSearch()  { showResults.value = false }

// ── Date ──────────────────────────────────────────────────────────────
const currentDate = computed(() =>
  new Date().toLocaleDateString('fr-FR', { weekday:'short', day:'2-digit', month:'short', year:'numeric' })
)

// ── Notifications jours fériés ────────────────────────────────────────
const JOURS_FERIES = {
  '01-01':'Nouvel An','03-20':"Fête de l'Indépendance",'04-09':'Fête des Martyrs',
  '05-01':'Fête du Travail','07-25':'Fête de la République','08-13':'Fête de la Femme',
  '10-15':"Fête de l'Évacuation",'11-07':'Fête du 7 Novembre',
}
const holidayNotifs = ref([])

function loadNotifs() {
  const today = new Date()
  const an    = today.getFullYear()
  const notifs = []
  for (const [key, nom] of Object.entries(JOURS_FERIES)) {
    const [m, d] = key.split('-').map(Number)
    let date = new Date(an, m - 1, d)
    if (date < today) date = new Date(an + 1, m - 1, d)
    const dans = Math.ceil((date - today) / 86400000)
    if (dans === 0) {
      notifs.push({ id:key, urgent:true,  icon:'fa-solid fa-flag',          titre:`Jour férié — ${nom}`, message:"Aujourd'hui est un jour férié national." })
    } else if (dans <= 7) {
      notifs.push({ id:key, urgent:true,  icon:'fa-solid fa-calendar-star', titre:`Bientôt — ${nom}`,   message:`Dans ${dans} jour${dans>1?'s':''} — ${date.toLocaleDateString('fr-FR',{day:'numeric',month:'long'})}` })
    } else if (dans <= 30) {
      notifs.push({ id:key, urgent:false, icon:'fa-solid fa-calendar',      titre:nom,                  message:`Dans ${dans} jours — ${date.toLocaleDateString('fr-FR',{day:'numeric',month:'long'})}` })
    }
  }
  holidayNotifs.value = notifs.sort((a,b) => a.urgent===b.urgent?0:a.urgent?-1:1)
}

// ── Notifications réelles (congés, etc.) + fériés fusionnées ──────────
const NOTIF_ICONS = {
  conge_submitted: 'fa-solid fa-umbrella-beach',
  conge_approved: 'fa-solid fa-circle-check',
  conge_refused: 'fa-solid fa-circle-xmark',
}
const allNotifs = computed(() => {
  const real = notifStore.items.map(n => ({
    key: 'real-' + n.id,
    id: n.id,
    real: true,
    readState: n.read,
    urgent: !n.read,
    icon: NOTIF_ICONS[n.type] || 'fa-solid fa-bell',
    titre: n.title,
    message: n.message,
    link: n.link,
  }))
  const holidays = holidayNotifs.value.map(n => ({ ...n, key: 'holiday-' + n.id, real: false }))
  return [...real, ...holidays]
})

function onNotifClick(n) {
  notifStore.markRead(n.id)
  showNotifs.value = false
  if (n.link) router.push(n.link)
}
</script>

<style scoped>
.icon-btn { background:none;border:none;cursor:pointer;color:var(--text-secondary);transition:color 0.2s;padding:4px;position:relative; }
.icon-btn:hover { color:var(--text-primary); }
.notif-dot { position:absolute;top:2px;right:2px;width:8px;height:8px;background:#ef4444;border-radius:50%;border:2px solid var(--bg-card); }

.icon-switch-enter-active,.icon-switch-leave-active { transition:all 0.2s ease; }
.icon-switch-enter-from { opacity:0;transform:rotate(90deg) scale(0.7); }
.icon-switch-leave-to   { opacity:0;transform:rotate(-90deg) scale(0.7); }

/* ── Dropdown recherche ── */
.search-dropdown {
  position:absolute; top:calc(100% + 10px); left:0;
  width:420px; max-height:400px; overflow-y:auto;
  background:var(--bg-card); border:1px solid var(--border);
  border-radius:14px; box-shadow:0 8px 32px rgba(0,0,0,0.2);
  z-index:200;
}
.search-item {
  display:flex; align-items:center; gap:10px;
  padding:10px 14px; cursor:pointer; transition:background 0.15s;
}
.search-item:hover { background:var(--bg-elevated); }
.search-item-icon {
  width:32px; height:32px; border-radius:8px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  font-size:13px;
}

/* ── Notif dropdown ── */
.notif-dropdown {
  position:absolute;top:calc(100% + 12px);right:0;
  width:320px;background:var(--bg-card);
  border:1px solid var(--border);border-radius:14px;
  box-shadow:0 8px 32px rgba(0,0,0,0.2);z-index:100;overflow:hidden;
}
.notif-item { display:flex;align-items:flex-start;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border);transition:background 0.15s; }
.notif-item:hover { background:var(--bg-elevated); }
.notif-item:last-child { border-bottom:none; }
.notif-urgent { background:rgba(239,68,68,0.04); }
</style>
