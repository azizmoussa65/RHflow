<template>
  <div class="page">

    <!-- Header -->
    <div class="flex items-center justify-between mb-5">
      <div>
        <h1>Tableau de bord RH</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Présence, absences et suivi des congés</p>
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
          <label style="font-size:12px;color:var(--text-muted)">Département</label>
          <select class="form-select" style="width:auto;font-size:12px" v-model="filterDept">
            <option value="">Tous les départements</option>
            <option v-for="d in departements" :key="d" :value="d">{{ d }}</option>
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
          <label style="font-size:12px;color:var(--text-muted)">Statut congé</label>
          <select class="form-select" style="width:auto;font-size:12px" v-model="filterStatut">
            <option value="">Tous</option>
            <option value="EN_ATTENTE">En attente</option>
            <option value="APPROUVE">Approuvé</option>
            <option value="REFUSE">Refusé</option>
          </select>
        </div>
        <button class="btn-ghost" style="margin-left:auto;font-size:12px" @click="resetFilters">
          <i class="fa-solid fa-rotate-left"></i> Réinitialiser
        </button>
      </div>
    </div>

    <!-- ══ STAT CARDS ══ -->
    <div class="grid grid-cols-4 gap-4 mb-5">
      <div class="card-sm">
        <div class="flex items-center gap-3">
          <div class="stat-icon" style="background:rgba(59,130,246,0.12)"><i class="fa-solid fa-users" style="color:#3b82f6"></i></div>
          <div>
            <div style="font-size:22px;font-weight:700;color:var(--text-primary)">{{ stats.totalEmployes }}</div>
            <div style="font-size:12px;color:var(--text-muted)">Total Employés</div>
          </div>
        </div>
      </div>
      <div class="card-sm">
        <div class="flex items-center gap-3">
          <div class="stat-icon" style="background:rgba(16,185,129,0.12)"><i class="fa-solid fa-user-check" style="color:#10b981"></i></div>
          <div>
            <div style="font-size:22px;font-weight:700;color:#10b981">{{ stats.presents }}</div>
            <div style="font-size:12px;color:var(--text-muted)">Présents aujourd'hui</div>
          </div>
        </div>
      </div>
      <div class="card-sm">
        <div class="flex items-center gap-3">
          <div class="stat-icon" style="background:rgba(239,68,68,0.12)"><i class="fa-solid fa-user-slash" style="color:#ef4444"></i></div>
          <div>
            <div style="font-size:22px;font-weight:700;color:#ef4444">{{ stats.absents }}</div>
            <div style="font-size:12px;color:var(--text-muted)">Absents aujourd'hui</div>
          </div>
        </div>
      </div>
      <div class="card-sm">
        <div class="flex items-center gap-3">
          <div class="stat-icon" style="background:rgba(245,158,11,0.12)"><i class="fa-solid fa-clock" style="color:#f59e0b"></i></div>
          <div>
            <div style="font-size:22px;font-weight:700;color:#f59e0b">{{ stats.congesEnAttente }}</div>
            <div style="font-size:12px;color:var(--text-muted)">Congés en attente</div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ ROW 1 : Présence/Absence + Congés par type ══ -->
    <div class="grid grid-cols-3 gap-4 mb-5">

      <!-- Graphique présence/absence par mois -->
      <div class="card col-span-2">
        <div class="flex items-center justify-between mb-4">
          <div>
            <div class="section-title">Présence & Absence</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px">Évolution mensuelle</div>
          </div>
          <div class="flex gap-3">
            <div class="flex items-center gap-2"><div style="width:10px;height:10px;border-radius:3px;background:#10b981"></div><span style="font-size:11px;color:var(--text-muted)">Présents</span></div>
            <div class="flex items-center gap-2"><div style="width:10px;height:10px;border-radius:3px;background:#ef4444"></div><span style="font-size:11px;color:var(--text-muted)">Absents</span></div>
          </div>
        </div>
        <!-- Barres groupées -->
        <div class="flex items-end gap-4" style="height:150px">
          <div v-for="m in presenceData" :key="m.mois" class="flex-1 flex flex-col items-center gap-1">
            <div style="display:flex;align-items:flex-end;gap:2px;height:130px;width:100%">
              <div style="flex:1;border-radius:6px 6px 0 0;background:linear-gradient(180deg,#10b981,#34d399);transition:height 0.6s ease"
                :style="{ height: (m.presents/maxPresence*100)+'%' }"
                :title="'Présents: '+m.presents"></div>
              <div style="flex:1;border-radius:6px 6px 0 0;background:linear-gradient(180deg,#ef4444,#fca5a5);transition:height 0.6s ease"
                :style="{ height: (m.absents/maxPresence*100)+'%' }"
                :title="'Absents: '+m.absents"></div>
            </div>
            <span style="font-size:11px;color:var(--text-muted)">{{ m.mois }}</span>
          </div>
        </div>
      </div>

      <!-- Congés par type (donut) -->
      <div class="card">
        <div class="section-title mb-4">Congés par type</div>
        <div class="flex flex-col items-center gap-4">
          <div class="donut-chart" :style="{ background: donutConges }">
            <div class="donut-hole">
              <div style="font-size:20px;font-weight:700;color:var(--text-primary)">{{ totalConges }}</div>
              <div style="font-size:10px;color:var(--text-muted)">total</div>
            </div>
          </div>
          <div style="width:100%" class="space-y-2">
            <div v-for="t in congesParType" :key="t.type" class="flex items-center gap-2">
              <div style="width:10px;height:10px;border-radius:3px;flex-shrink:0" :style="{ background: t.color }"></div>
              <div style="flex:1;font-size:11px;color:var(--text-secondary)">{{ t.type }}</div>
              <div style="font-size:12px;font-weight:600;color:var(--text-primary)">{{ t.count }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ ROW 2 : Statistiques congés par période + Liste demandes ══ -->
    <div class="grid grid-cols-2 gap-4 mb-5">

      <!-- Stats congés par période -->
      <div class="card">
        <div class="section-title mb-4">
          <i class="fa-solid fa-calendar-check" style="color:#3b82f6;margin-right:8px"></i>
          Statistiques des congés par période
        </div>
        <div class="table-wrapper">
          <table>
            <thead><tr>
              <th>Période</th>
              <th style="text-align:center">Annuel</th>
              <th style="text-align:center">Maladie</th>
              <th style="text-align:center">Exceptionnel</th>
              <th style="text-align:center">Total</th>
            </tr></thead>
            <tbody>
              <tr v-for="p in statsParPeriode" :key="p.periode">
                <td style="font-weight:600">{{ p.periode }}</td>
                <td style="text-align:center"><span class="badge badge-blue">{{ p.annuel }}</span></td>
                <td style="text-align:center"><span class="badge badge-red">{{ p.maladie }}</span></td>
                <td style="text-align:center"><span class="badge badge-amber">{{ p.exceptionnel }}</span></td>
                <td style="text-align:center;font-weight:700;color:var(--text-primary)">{{ p.annuel + p.maladie + p.exceptionnel }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Liste des demandes filtrées -->
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <div class="section-title">
            <i class="fa-solid fa-list" style="color:#8b5cf6;margin-right:8px"></i>
            Demandes récentes
          </div>
          <span class="badge badge-slate">{{ filteredConges.length }} demandes</span>
        </div>
        <div class="space-y-2" style="max-height:280px;overflow-y:auto">
          <div v-for="c in filteredConges" :key="c.id"
            class="flex items-center gap-3 p-3 rounded-xl"
            style="background:var(--bg-hover);border:1px solid var(--border)">
            <div class="avatar" :style="{ background: c.color }">{{ c.initials }}</div>
            <div style="flex:1;min-width:0">
              <div style="font-size:13px;font-weight:500;color:var(--text-primary)">{{ c.employe }}</div>
              <div style="font-size:11px;color:var(--text-muted)">{{ c.type }} · {{ c.nbJours }} jours</div>
              <div style="font-size:10px;color:var(--text-muted)">{{ c.dept }}</div>
            </div>
            <span class="badge" :class="statutBadge(c.statut)" style="font-size:10px">{{ statutLabel(c.statut) }}</span>
          </div>
          <div v-if="filteredConges.length === 0" style="text-align:center;padding:20px;color:var(--text-muted);font-size:13px">
            Aucune demande pour ces filtres
          </div>
        </div>
      </div>
    </div>

    <!-- ══ ROW 3 : Taux de présence par département ══ -->
    <div class="card">
      <div class="section-title mb-4">
        <i class="fa-solid fa-building" style="color:#06b6d4;margin-right:8px"></i>
        Taux de présence par département
      </div>
      <div class="space-y-3">
        <div v-for="d in filteredDepts" :key="d.nom">
          <div class="flex items-center gap-4">
            <div style="width:130px;font-size:12px;font-weight:600;color:var(--text-secondary)">{{ d.nom }}</div>
            <div style="flex:1">
              <div class="progress-track" style="height:10px">
                <div class="progress-fill" :style="{ width: d.taux+'%', background: d.color, borderRadius:'99px' }"></div>
              </div>
            </div>
            <div style="min-width:50px;text-align:right;font-size:13px;font-weight:700;color:var(--text-primary)">{{ d.taux }}%</div>
            <span class="badge" :class="d.taux >= 90 ? 'badge-green' : d.taux >= 75 ? 'badge-amber' : 'badge-red'" style="font-size:10px">
              {{ d.presents }}/{{ d.total }}
            </span>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, inject, onMounted } from 'vue'
import employeService from '@/services/employeService.js'
import congeService from '@/services/congeService.js'

const toast = inject('toast')

/* ── Filtres ─────────────────────────────── */
const filterDept    = ref('')
const filterPeriode = ref('mois')
const filterStatut  = ref('')
function resetFilters() { filterDept.value = ''; filterPeriode.value = 'mois'; filterStatut.value = '' }

/* ── Données réelles ─────────────────────── */
const employesList = ref([])
const congesList   = ref([])
const loading      = ref(false)

const departements = computed(() => {
  const set = new Set(employesList.value.map(e => e.departement).filter(Boolean))
  return [...set]
})

const DEPT_COLORS = [
  'linear-gradient(90deg,#3b82f6,#06b6d4)', 'linear-gradient(90deg,#8b5cf6,#a78bfa)',
  'linear-gradient(90deg,#06b6d4,#22d3ee)', 'linear-gradient(90deg,#10b981,#34d399)',
  'linear-gradient(90deg,#ef4444,#f87171)', 'linear-gradient(90deg,#f59e0b,#fbbf24)',
]
const AVATAR_COLORS = [
  'linear-gradient(135deg,#3b82f6,#06b6d4)', 'linear-gradient(135deg,#8b5cf6,#a78bfa)',
  'linear-gradient(135deg,#10b981,#34d399)', 'linear-gradient(135deg,#f59e0b,#fbbf24)',
  'linear-gradient(135deg,#ef4444,#f87171)', 'linear-gradient(135deg,#06b6d4,#67e8f9)',
]
function colorFor(id, palette) {
  let sum = 0
  for (const ch of String(id || '')) sum += ch.charCodeAt(0)
  return palette[sum % palette.length]
}

/** Parse the API's 'dd/mm/yyyy' date strings (returns null if absent/invalid). */
function parseFR(str) {
  if (!str) return null
  const [d, m, y] = str.split('/').map(Number)
  if (!d || !m || !y) return null
  return new Date(y, m - 1, d)
}

const MOIS_FR = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc']
const MOIS_FR_LONG = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre']

/** Employees with at least one APPROVED congé covering the given date. */
function employeesOnLeave(date) {
  const ids = new Set()
  for (const c of congesList.value) {
    if (c.statut !== 'APPROUVE') continue
    const debut = parseFR(c.dateDebut)
    const fin = parseFR(c.dateFin)
    if (!debut || !fin) continue
    if (date >= debut && date <= fin) ids.add(c.employeId)
  }
  return ids
}

/* ── Stats cards ─────────────────────────── */
const stats = computed(() => {
  const total = employesList.value.length
  const absents = employeesOnLeave(new Date()).size
  return {
    totalEmployes: total,
    presents: Math.max(0, total - absents),
    absents,
    congesEnAttente: congesList.value.filter(c => c.statut === 'EN_ATTENTE').length,
  }
})

/* ── Présence/Absence par mois (approximée à partir des congés approuvés) ── */
const presenceData = computed(() => {
  const total = employesList.value.length
  const months = []
  const now = new Date()
  for (let i = 7; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
    months.push({ year: d.getFullYear(), month: d.getMonth() })
  }
  return months.map(({ year, month }) => {
    const monthStart = new Date(year, month, 1)
    const monthEnd = new Date(year, month + 1, 0)
    const absentIds = new Set()
    for (const c of congesList.value) {
      if (c.statut !== 'APPROUVE') continue
      const debut = parseFR(c.dateDebut)
      const fin = parseFR(c.dateFin)
      if (!debut || !fin) continue
      if (debut <= monthEnd && fin >= monthStart) absentIds.add(c.employeId)
    }
    const absents = absentIds.size
    return { mois: MOIS_FR[month], presents: Math.max(0, total - absents), absents }
  })
})
const maxPresence = computed(() => Math.max(1, ...presenceData.value.flatMap(m => [m.presents, m.absents])))

/* ── Congés par type (donut) ─────────────── */
const TYPE_COLORS = { 'Annuel':'#3b82f6', 'Maladie':'#ef4444', 'Maternité/Paternité':'#8b5cf6', 'Exceptionnel':'#f59e0b' }
const congesParType = computed(() => {
  const counts = {}
  for (const c of congesList.value) counts[c.type] = (counts[c.type] || 0) + 1
  return Object.entries(counts).map(([type, count]) => ({ type, count, color: TYPE_COLORS[type] || '#64748b' }))
})
const totalConges = computed(() => congesParType.value.reduce((s,x) => s + x.count, 0))
const donutConges = computed(() => {
  if (totalConges.value === 0) return 'conic-gradient(#e2e8f0 0deg 360deg)'
  let deg = 0
  const segs = congesParType.value.map(t => {
    const end = deg + (t.count / totalConges.value * 360)
    const seg = `${t.color} ${deg}deg ${end}deg`
    deg = end
    return seg
  })
  return `conic-gradient(${segs.join(', ')})`
})

/* ── Statistiques congés par période (6 derniers mois, par type) ── */
const statsParPeriode = computed(() => {
  const now = new Date()
  const buckets = []
  for (let i = 5; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
    buckets.push({ year: d.getFullYear(), month: d.getMonth(), periode: `${MOIS_FR_LONG[d.getMonth()]} ${d.getFullYear()}`, annuel: 0, maladie: 0, exceptionnel: 0 })
  }
  for (const c of congesList.value) {
    const debut = parseFR(c.dateDebut)
    if (!debut) continue
    const bucket = buckets.find(b => b.year === debut.getFullYear() && b.month === debut.getMonth())
    if (!bucket) continue
    if (c.type === 'Annuel') bucket.annuel++
    else if (c.type === 'Maladie') bucket.maladie++
    else if (c.type === 'Exceptionnel') bucket.exceptionnel++
  }
  return buckets
})

/* ── Liste des demandes de congés ────────── */
const filteredConges = computed(() => {
  const deptById = new Map(employesList.value.map(e => [e.id, e.departement]))
  return congesList.value
    .map(c => ({
      ...c,
      dept: deptById.get(c.employeId) || '—',
      color: colorFor(c.employeId, AVATAR_COLORS),
    }))
    .filter(c => {
      if (filterDept.value   && c.dept   !== filterDept.value)   return false
      if (filterStatut.value && c.statut !== filterStatut.value) return false
      return true
    })
})

const statutBadge = (s) => ({ EN_ATTENTE:'badge-amber', APPROUVE:'badge-green', REFUSE:'badge-red' }[s] || 'badge-slate')
const statutLabel = (s) => ({ EN_ATTENTE:'En attente', APPROUVE:'Approuvé', REFUSE:'Refusé' }[s] || s)

/* ── Taux de présence par département ────── */
const presenceDepts = computed(() => {
  const onLeaveToday = employeesOnLeave(new Date())
  return departements.value.map(nom => {
    const membres = employesList.value.filter(e => e.departement === nom)
    const total = membres.length
    const absents = membres.filter(e => onLeaveToday.has(e.id)).length
    const presents = total - absents
    return {
      nom, presents, total,
      taux: total > 0 ? Math.round(presents / total * 100) : 0,
      color: colorFor(nom, DEPT_COLORS),
    }
  })
})

const filteredDepts = computed(() =>
  filterDept.value ? presenceDepts.value.filter(d => d.nom === filterDept.value) : presenceDepts.value
)

function exportRapport() { toast.info('Export', 'Génération du rapport RH en cours...') }

onMounted(async () => {
  loading.value = true
  try {
    const [emp, conges] = await Promise.all([employeService.getAll(), congeService.getAll()])
    if (emp) employesList.value = emp
    if (conges) congesList.value = conges
  } catch (_) {
    toast.error('Erreur serveur', 'Impossible de charger les données du tableau de bord.')
  }
  loading.value = false
})
</script>

<style scoped>
.stat-icon {
  width:44px; height:44px; border-radius:12px;
  display:flex; align-items:center; justify-content:center;
  font-size:18px; flex-shrink:0;
}
.donut-chart {
  width:110px; height:110px;
  border-radius:50%;
  position:relative;
  flex-shrink:0;
}
.donut-hole {
  position:absolute; inset:18px;
  background:var(--bg-card);
  border-radius:50%;
  display:flex; flex-direction:column;
  align-items:center; justify-content:center;
}
.flex-1 { flex:1; }
</style>
