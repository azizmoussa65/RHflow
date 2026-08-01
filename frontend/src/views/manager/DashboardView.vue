<template>
  <div class="page">

    <!-- Header -->
    <div class="flex items-center justify-between mb-5">
      <div>
        <h1>Tableau de bord Manager</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Vue globale de la performance RH</p>
      </div>
      <button class="btn-primary" @click="exportRapport">
        <i class="fa-solid fa-download"></i> Exporter rapport
      </button>
    </div>

    <!-- ══ FILTRES ══ -->
    <div class="card mb-5">
      <div class="flex gap-4 items-center flex-wrap">
        <div style="font-size:12px;font-weight:600;color:var(--text-muted)">
          <i class="fa-solid fa-filter" style="margin-right:6px;color:#3b82f6"></i>Filtres
        </div>
        <div class="flex items-center gap-2">
          <label style="font-size:12px;color:var(--text-muted)">Projet</label>
          <select class="form-select" style="width:auto;font-size:12px" v-model="filterProjet">
            <option value="">Tous les projets</option>
            <option v-for="p in projets" :key="p.id" :value="p.id">{{ p.nom }}</option>
          </select>
        </div>
        <div class="flex items-center gap-2">
          <label style="font-size:12px;color:var(--text-muted)">Période</label>
          <select class="form-select" style="width:auto;font-size:12px" v-model="filterPeriode">
            <option value="semaine">Cette semaine</option>
            <option value="mois">Ce mois</option>
            <option value="trimestre">Ce trimestre</option>
            <option value="annee">Cette année</option>
          </select>
        </div>
        <div class="flex items-center gap-2">
          <label style="font-size:12px;color:var(--text-muted)">Employé</label>
          <select class="form-select" style="width:auto;font-size:12px" v-model="filterEmploye">
            <option value="">Tous les employés</option>
            <option v-for="e in employes" :key="e.id" :value="e.id">{{ e.nom }}</option>
          </select>
        </div>
        <button class="btn-ghost" style="margin-left:auto;font-size:12px" @click="resetFilters">
          <i class="fa-solid fa-rotate-left"></i> Réinitialiser
        </button>
      </div>
    </div>

    <!-- ══ STAT CARDS ══ -->
    <div class="grid grid-cols-4 gap-4 mb-5">
      <StatCard label="Total Employés"    :value="stats.totalEmployes"   trend="+3 ce mois"    :trendUp="true"  icon="fa-solid fa-users"           color="blue"  />
      <StatCard label="Projets Actifs"    :value="stats.projetsActifs"   trend="2 en critique" :trendUp="false" icon="fa-solid fa-diagram-project"  color="cyan"  />
      <StatCard label="Congés en attente" :value="stats.congesEnAttente" trend="2 urgents"     :trendUp="false" icon="fa-solid fa-umbrella-beach"   color="green" />
      <StatCard label="Contrats actifs"   :value="stats.contratsActifs"  trend="3 expirent"    :trendUp="false" icon="fa-solid fa-file-contract"    color="amber" />
    </div>

    <!-- ══ ROW 1 : Évolution effectifs + Départements ══ -->
    <div class="grid grid-cols-3 gap-4 mb-5">
      <div class="card col-span-2">
        <div class="flex items-center justify-between mb-5">
          <div>
            <div class="section-title">Évolution des effectifs</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px">Recrutements par mois (12 derniers mois)</div>
          </div>
        </div>
        <div class="flex items-end gap-3" style="height:140px">
          <div v-for="(bar,i) in chartBars" :key="i" class="flex-1 flex flex-col items-center gap-1">
            <div class="chart-bar w-full" :style="{ height: bar.h+'%' }" :title="bar.label+': '+bar.value"></div>
            <span style="font-size:11px;color:var(--text-muted)">{{ bar.label }}</span>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="section-title mb-4">Répartition départements</div>
        <div v-if="depts.length === 0" style="text-align:center;padding:20px;color:var(--text-muted);font-size:13px">
          Aucun employé pour le moment.
        </div>
        <div class="space-y-3" v-else>
          <div v-for="dept in depts" :key="dept.name">
            <div class="flex justify-between" style="font-size:12px;margin-bottom:4px">
              <span style="color:var(--text-secondary)">{{ dept.name }}</span>
              <span style="font-weight:600;color:var(--text-primary)">{{ dept.pct }}%</span>
            </div>
            <div class="progress-track">
              <div class="progress-fill" :style="{ width: dept.pct+'%', background: dept.color }"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ ROW 2 : Avancement projets + Tâches par statut ══ -->
    <div class="grid grid-cols-2 gap-4 mb-5">

      <!-- Avancement des projets -->
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <div>
            <div class="section-title">Avancement des projets</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px">Progression par projet</div>
          </div>
          <span class="badge badge-blue">{{ filteredProjets.length }} projets</span>
        </div>
        <div v-if="filteredProjets.length === 0" style="text-align:center;padding:20px;color:var(--text-muted);font-size:13px">
          Aucun projet pour le moment.
        </div>
        <div class="space-y-4" v-else>
          <div v-for="p in filteredProjets" :key="p.id">
            <div class="flex items-start justify-between mb-2">
              <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ p.nom }}</div>
                <div style="font-size:11px;color:var(--text-muted)">{{ p.categorie }} · {{ p.tachesFaites }}/{{ p.tachesTotal }} tâches</div>
              </div>
              <span style="font-size:14px;font-weight:700;color:var(--text-primary);margin-left:12px;flex-shrink:0">{{ p.avancement }}%</span>
            </div>
            <div class="progress-track" style="height:8px">
              <div class="progress-fill" :style="{ width: p.avancement+'%', background: p.couleur, borderRadius:'99px' }"></div>
            </div>
            <div class="flex gap-2 mt-2">
              <span class="badge badge-slate" style="font-size:10px">{{ p.statut }}</span>
              <span v-if="p.avancement < 30" class="badge badge-red"   style="font-size:10px">En retard</span>
              <span v-else-if="p.avancement >= 80" class="badge badge-green" style="font-size:10px">Avancé</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Tâches par statut (donut CSS) -->
      <div class="card">
        <div class="section-title mb-4">Statistiques des tâches</div>
        <div class="flex items-center gap-6 mb-5">
          <div class="donut-chart" :style="{ background: donutGradient }">
            <div class="donut-hole">
              <div style="font-size:22px;font-weight:700;color:var(--text-primary)">{{ totalTaches }}</div>
              <div style="font-size:10px;color:var(--text-muted)">tâches</div>
            </div>
          </div>
          <div class="space-y-3" style="flex:1">
            <div v-for="s in tacheStatuts" :key="s.label" class="flex items-center gap-3">
              <div style="width:10px;height:10px;border-radius:3px;flex-shrink:0" :style="{ background: s.color }"></div>
              <div style="flex:1;font-size:12px;color:var(--text-secondary)">{{ s.label }}</div>
              <div style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ s.count }}</div>
              <div style="font-size:11px;color:var(--text-muted);min-width:34px;text-align:right">{{ totalTaches ? Math.round(s.count/totalTaches*100) : 0 }}%</div>
            </div>
          </div>
        </div>
        <!-- Barre récapitulative -->
        <div style="height:10px;border-radius:99px;overflow:hidden;display:flex;gap:2px">
          <div v-for="s in tacheStatuts" :key="s.label"
            :style="{ flex: s.count, background: s.color }"
            :title="s.label+': '+s.count"></div>
        </div>
      </div>
    </div>

    <!-- ══ ROW 3 : Tâches par employé ══ -->
    <div class="card mb-5">
      <div class="flex items-center justify-between mb-4">
        <div>
          <div class="section-title">Tâches par employé</div>
          <div style="font-size:11px;color:var(--text-muted);margin-top:2px">Charge de travail par membre</div>
        </div>
      </div>
      <div class="table-wrapper">
        <table>
          <thead><tr>
            <th>Employé</th>
            <th style="text-align:center">À faire</th>
            <th style="text-align:center">En cours</th>
            <th style="text-align:center">Terminé</th>
            <th style="text-align:center">Total</th>
            <th style="min-width:140px">Progression</th>
          </tr></thead>
          <tbody>
            <tr v-if="filteredTachesParEmploye.length === 0"><td colspan="6" style="text-align:center;padding:20px;color:var(--text-muted)">
              Aucune tâche assignée pour le moment.
            </td></tr>
            <tr v-for="e in filteredTachesParEmploye" :key="e.id">
              <td>
                <div class="flex items-center gap-3">
                  <div class="avatar" :style="{ background: e.color }">{{ e.initials }}</div>
                  {{ e.nom }}
                </div>
              </td>
              <td style="text-align:center"><span class="badge badge-slate">{{ e.aFaire }}</span></td>
              <td style="text-align:center"><span class="badge badge-amber">{{ e.enCours }}</span></td>
              <td style="text-align:center"><span class="badge badge-green">{{ e.termine }}</span></td>
              <td style="text-align:center;font-weight:600;color:var(--text-primary)">{{ e.total }}</td>
              <td>
                <div style="display:flex;align-items:center;gap:8px">
                  <div class="progress-track" style="flex:1;margin:0">
                    <div class="progress-fill" :style="{ width: Math.round(e.termine/e.total*100)+'%' }"></div>
                  </div>
                  <span style="font-size:11px;color:var(--text-muted);min-width:30px">{{ Math.round(e.termine/e.total*100) }}%</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══ ROW 4 : Activité + Congés ══ -->
    <div class="grid grid-cols-2 gap-4">
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <div class="section-title">Activité récente</div>
        </div>
        <div v-if="activities.length === 0" style="text-align:center;padding:20px;color:var(--text-muted);font-size:13px">
          Aucune activité récente.
        </div>
        <div class="space-y-3" v-else>
          <div v-for="act in activities" :key="act.id" class="flex gap-3">
            <div class="timeline-dot mt-1" :class="act.dotClass"></div>
            <div>
              <div style="font-size:13px;color:var(--text-primary)">{{ act.title }}</div>
              <div style="font-size:11px;color:var(--text-muted)">{{ act.sub }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <div class="section-title">Congés à traiter</div>
          <span class="badge badge-amber"><i class="fa-solid fa-clock" style="font-size:10px"></i> {{ pendingConges.length }} en attente</span>
        </div>
        <div class="space-y-2">
          <div v-for="c in pendingConges" :key="c.id"
            class="flex items-center gap-3 p-3 rounded-xl"
            style="background:var(--bg-hover);border:1px solid var(--border)">
            <div class="avatar" :style="{ background: c.color }">{{ c.initials }}</div>
            <div style="flex:1;min-width:0">
              <div style="font-size:13px;font-weight:500;color:var(--text-primary)">{{ c.nom }}</div>
              <div style="font-size:11px;color:var(--text-muted)">{{ c.jours }} jours · {{ c.dates }}</div>
            </div>
            <div class="flex gap-2">
              <button class="btn-edit"    style="padding:5px 10px;font-size:11px" @click="approuver(c)"><i class="fa-solid fa-check"></i></button>
              <button class="btn-danger"  style="padding:5px 10px;font-size:11px" @click="refuser(c)"> <i class="fa-solid fa-xmark"></i></button>
            </div>
          </div>
          <div v-if="pendingConges.length === 0" style="text-align:center;padding:20px;color:var(--text-muted);font-size:13px">
            Aucune demande en attente
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import StatCard from '@/components/shared/StatCard.vue'
import dashboardService from '@/services/dashboardService.js'
import congeService from '@/services/congeService.js'
import employeService from '@/services/employeService.js'
import projetService from '@/services/projetService.js'
import tacheService from '@/services/tacheService.js'

const toast = inject('toast')

/* ── Filtres ─────────────────────────────── */
const filterProjet  = ref('')
const filterPeriode = ref('mois')
const filterEmploye = ref('')
function resetFilters() { filterProjet.value = ''; filterPeriode.value = 'mois'; filterEmploye.value = '' }

/* ── Stats cards ─────────────────────────── */
const stats = ref({ totalEmployes:0, projetsActifs:0, congesEnAttente:0, contratsActifs:0 })

/* ── Données réelles ─────────────────────── */
const employesList = ref([])
const projets = ref([])
const congesList = ref([])
const allTaches = ref([])

const employes = computed(() => employesList.value.map(e => ({ id: e.id, nom: `${e.prenom} ${e.nom}` })))

async function loadAllTaches() {
  const lists = await Promise.all(projets.value.map(p => tacheService.getAll(p.id).catch(() => [])))
  allTaches.value = lists.flat()
}

const AVATAR_COLORS = [
  'linear-gradient(135deg,#3b82f6,#06b6d4)', 'linear-gradient(135deg,#8b5cf6,#a78bfa)',
  'linear-gradient(135deg,#10b981,#34d399)', 'linear-gradient(135deg,#f59e0b,#fbbf24)',
  'linear-gradient(135deg,#ef4444,#f87171)', 'linear-gradient(135deg,#06b6d4,#67e8f9)',
]
const DEPT_COLORS = [
  'linear-gradient(90deg,#3b82f6,#06b6d4)', 'linear-gradient(90deg,#8b5cf6,#a78bfa)',
  'linear-gradient(90deg,#06b6d4,#22d3ee)', 'linear-gradient(90deg,#10b981,#34d399)',
  'linear-gradient(90deg,#f59e0b,#fbbf24)', 'linear-gradient(90deg,#ef4444,#f87171)',
]
function colorFor(id, palette) {
  let sum = 0
  for (const ch of String(id || '')) sum += ch.charCodeAt(0)
  return palette[sum % palette.length]
}

/** Parse the API's 'dd/mm/yyyy' date strings. */
function parseFR(str) {
  if (!str) return null
  const [d, m, y] = str.split('/').map(Number)
  if (!d || !m || !y) return null
  return new Date(y, m - 1, d)
}

/* ── Évolution des effectifs (recrutements réels des 12 derniers mois) ── */
const MOIS_FR = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc']
const chartBars = computed(() => {
  const now = new Date()
  const months = []
  for (let i = 11; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
    months.push({ year: d.getFullYear(), month: d.getMonth() })
  }
  const counts = months.map(({ year, month }) =>
    employesList.value.filter(e => {
      const embauche = parseFR(e.dateEmbauche)
      return embauche && embauche.getFullYear() === year && embauche.getMonth() === month
    }).length
  )
  const max = Math.max(1, ...counts)
  return months.map(({ month }, i) => ({ label: MOIS_FR[month], value: counts[i], h: Math.round(counts[i] / max * 100) }))
})

/* ── Départements (répartition réelle) ───── */
const depts = computed(() => {
  const total = employesList.value.length
  if (total === 0) return []
  const counts = {}
  for (const e of employesList.value) {
    const dep = e.departement || 'Non renseigné'
    counts[dep] = (counts[dep] || 0) + 1
  }
  return Object.entries(counts)
    .map(([name, count], i) => ({ name, pct: Math.round(count / total * 100), color: DEPT_COLORS[i % DEPT_COLORS.length] }))
    .sort((a, b) => b.pct - a.pct)
})

/* ── Projets avec avancement (réel) ──────── */
const filteredProjets = computed(() => {
  const list = projets.value.map(p => {
    const tasksForProjet = allTaches.value.filter(t => t.projetId === p.id)
    return {
      id: p.id, nom: p.nom, categorie: p.categorie, avancement: p.avancement,
      statut: p.statut, couleur: p.couleur,
      tachesTotal: tasksForProjet.length,
      tachesFaites: tasksForProjet.filter(t => t.statut === 'Terminé').length,
    }
  })
  return filterProjet.value ? list.filter(p => p.id === filterProjet.value) : list
})

/* ── Tâches par statut (réel) ─────────────── */
const tacheStatuts = computed(() => {
  const counts = { 'À faire': 0, 'En cours': 0, 'Terminé': 0 }
  for (const t of allTaches.value) if (t.statut in counts) counts[t.statut]++
  return [
    { label: 'À faire',  count: counts['À faire'],  color: '#94a3b8' },
    { label: 'En cours', count: counts['En cours'], color: '#f59e0b' },
    { label: 'Terminé',  count: counts['Terminé'],  color: '#10b981' },
  ]
})
const totalTaches = computed(() => tacheStatuts.value.reduce((s,x) => s + x.count, 0))

const donutGradient = computed(() => {
  if (totalTaches.value === 0) return 'conic-gradient(#e2e8f0 0deg 360deg)'
  let deg = 0
  const segs = tacheStatuts.value.map(s => {
    const end = deg + (s.count / totalTaches.value * 360)
    const seg = `${s.color} ${deg}deg ${end}deg`
    deg = end
    return seg
  })
  return `conic-gradient(${segs.join(', ')})`
})

/* ── Tâches par employé (réel) ────────────── */
const tachesParEmploye = computed(() => {
  return employesList.value
    .map(e => {
      const mine = allTaches.value.filter(t => t.assigneA?.id === e.id)
      if (mine.length === 0) return null
      return {
        id: e.id, nom: `${e.prenom} ${e.nom}`, initials: e.initials, color: colorFor(e.id, AVATAR_COLORS),
        aFaire: mine.filter(t => t.statut === 'À faire').length,
        enCours: mine.filter(t => t.statut === 'En cours').length,
        termine: mine.filter(t => t.statut === 'Terminé').length,
        total: mine.length,
      }
    })
    .filter(Boolean)
})

const filteredTachesParEmploye = computed(() =>
  filterEmploye.value ? tachesParEmploye.value.filter(e => e.id === filterEmploye.value) : tachesParEmploye.value
)

/* ── Activité récente (réelle, basée sur les horodatages) ── */
function timeAgo(date) {
  const h = Math.floor((Date.now() - date.getTime()) / 3600000)
  if (h < 1) return "à l'instant"
  if (h < 24) return `il y a ${h}h`
  return `il y a ${Math.floor(h / 24)}j`
}
const activities = computed(() => {
  const events = []
  for (const e of employesList.value) {
    if (e.createdAt) events.push({ id: 'emp-' + e.id, date: new Date(e.createdAt), dotClass: 'bg-green-dot',
      title: 'Nouvel employé ajouté', label: `${e.prenom} ${e.nom} — ${e.poste || e.departement || ''}` })
  }
  for (const c of congesList.value) {
    if (c.createdAt) events.push({ id: 'conge-' + c.id, date: new Date(c.createdAt), dotClass: 'bg-amber-dot',
      title: 'Demande de congé soumise', label: `${c.employe} — ${c.nbJours} jour(s)` })
  }
  for (const p of projets.value) {
    if (p.createdAt) events.push({ id: 'proj-' + p.id, date: new Date(p.createdAt), dotClass: 'bg-purple-dot',
      title: 'Nouveau projet créé', label: p.nom })
  }
  return events
    .sort((a, b) => b.date - a.date)
    .slice(0, 6)
    .map(ev => ({ ...ev, sub: `${ev.label} · ${timeAgo(ev.date)}` }))
})

/* ── Congés en attente (réel) ─────────────── */
const pendingConges = computed(() => congesList.value
  .filter(c => c.statut === 'EN_ATTENTE')
  .map(c => ({ id: c.id, nom: c.employe, initials: c.initials, jours: c.nbJours,
    dates: `${c.dateDebut} → ${c.dateFin}`, color: colorFor(c.employeId, AVATAR_COLORS) }))
)

async function approuver(c) {
  try {
    await congeService.approve(c.id)
    const item = congesList.value.find(x => x.id === c.id)
    if (item) item.statut = 'APPROUVE'
    toast.success('Congé approuvé', `La demande de ${c.nom} a été acceptée.`)
  } catch (_) {
    toast.error('Erreur serveur', 'Impossible d\'approuver ce congé.')
  }
}
async function refuser(c) {
  try {
    await congeService.refuse(c.id)
    const item = congesList.value.find(x => x.id === c.id)
    if (item) item.statut = 'REFUSE'
    toast.info('Congé refusé', `La demande de ${c.nom} a été refusée.`)
  } catch (_) {
    toast.error('Erreur serveur', 'Impossible de refuser ce congé.')
  }
}
function exportRapport() { toast.info('Export', 'Génération du rapport en cours...') }

onMounted(async () => {
  try { stats.value = await dashboardService.getStats() } catch (_) {
    toast.error('Erreur serveur', 'Impossible de charger les statistiques.')
  }
  try { const emp = await employeService.getAll(); if (emp) employesList.value = emp } catch (_) {}
  try { const p = await projetService.getAll(); if (p) projets.value = p } catch (_) {}
  try { const c = await congeService.getAll(); if (c) congesList.value = c } catch (_) {}
  try { await loadAllTaches() } catch (_) {}
})
</script>

<style scoped>
.bg-green-dot  { background:#10b981; }
.bg-amber-dot  { background:#f59e0b; }
.bg-blue-dot   { background:#3b82f6; }
.bg-red-dot    { background:#ef4444; }
.bg-purple-dot { background:#8b5cf6; }
.w-full { width:100%; }
.flex-1 { flex:1; }

.donut-chart {
  width:110px; height:110px;
  border-radius:50%;
  position:relative;
  flex-shrink:0;
}
.donut-hole {
  position:absolute;
  inset:18px;
  background:var(--bg-card);
  border-radius:50%;
  display:flex; flex-direction:column;
  align-items:center; justify-content:center;
}
</style>
