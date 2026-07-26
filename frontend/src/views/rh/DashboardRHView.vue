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
import { ref, computed, inject } from 'vue'

const toast = inject('toast')

/* ── Filtres ─────────────────────────────── */
const filterDept    = ref('')
const filterPeriode = ref('mois')
const filterStatut  = ref('')
const departements  = ['Développement','Marketing','Design','Finance','RH']
function resetFilters() { filterDept.value = ''; filterPeriode.value = 'mois'; filterStatut.value = '' }

/* ── Stats cards ─────────────────────────── */
const stats = ref({ totalEmployes:24, presents:18, absents:3, congesEnAttente:5 })

/* ── Présence/Absence par mois ───────────── */
const presenceData = [
  { mois:'Jan', presents:21, absents:3 },
  { mois:'Fév', presents:19, absents:5 },
  { mois:'Mar', presents:20, absents:4 },
  { mois:'Avr', presents:22, absents:2 },
  { mois:'Mai', presents:18, absents:6 },
  { mois:'Jun', presents:20, absents:4 },
  { mois:'Jul', presents:17, absents:7 },
  { mois:'Aoû', presents:15, absents:9 },
]
const maxPresence = computed(() => Math.max(...presenceData.map(m => m.presents)))

/* ── Congés par type (donut) ─────────────── */
const congesParType = ref([
  { type:'Annuel',             count:12, color:'#3b82f6' },
  { type:'Maladie',            count: 7, color:'#ef4444' },
  { type:'Maternité/Paternité',count: 3, color:'#8b5cf6' },
  { type:'Exceptionnel',       count: 4, color:'#f59e0b' },
])
const totalConges = computed(() => congesParType.value.reduce((s,x) => s + x.count, 0))
const donutConges = computed(() => {
  let deg = 0
  const segs = congesParType.value.map(t => {
    const end = deg + (t.count / totalConges.value * 360)
    const seg = `${t.color} ${deg}deg ${end}deg`
    deg = end
    return seg
  })
  return `conic-gradient(${segs.join(', ')})`
})

/* ── Statistiques congés par période ────── */
const statsParPeriode = [
  { periode:'Janvier 2025',  annuel:4, maladie:2, exceptionnel:1 },
  { periode:'Février 2025',  annuel:3, maladie:3, exceptionnel:0 },
  { periode:'Mars 2025',     annuel:5, maladie:1, exceptionnel:2 },
  { periode:'Avril 2025',    annuel:6, maladie:2, exceptionnel:1 },
  { periode:'Mai 2025',      annuel:4, maladie:3, exceptionnel:0 },
]

/* ── Liste des demandes de congés ────────── */
const toutesConges = ref([
  { id:1, employe:'Yassine Aouat',  initials:'YA', type:'Annuel',  nbJours:5,  dept:'Développement', statut:'EN_ATTENTE', color:'linear-gradient(135deg,#8b5cf6,#a78bfa)' },
  { id:2, employe:'Karima Belhadj', initials:'KB', type:'Maladie', nbJours:3,  dept:'Marketing',     statut:'EN_ATTENTE', color:'linear-gradient(135deg,#06b6d4,#22d3ee)' },
  { id:3, employe:'Sana Mrad',      initials:'SM', type:'Annuel',  nbJours:10, dept:'Design',        statut:'APPROUVE',   color:'linear-gradient(135deg,#10b981,#34d399)' },
  { id:4, employe:'Mehdi Khelifi',  initials:'MK', type:'Exceptionnel', nbJours:3, dept:'Finance',   statut:'REFUSE',     color:'linear-gradient(135deg,#f59e0b,#fbbf24)' },
  { id:5, employe:'Lina Bouzid',    initials:'LB', type:'Annuel',  nbJours:7,  dept:'Développement', statut:'APPROUVE',   color:'linear-gradient(135deg,#ef4444,#f87171)' },
  { id:6, employe:'Omar Trabelsi',  initials:'OT', type:'Maladie', nbJours:2,  dept:'RH',            statut:'EN_ATTENTE', color:'linear-gradient(135deg,#3b82f6,#60a5fa)' },
])

const filteredConges = computed(() => {
  return toutesConges.value.filter(c => {
    if (filterDept.value   && c.dept   !== filterDept.value)   return false
    if (filterStatut.value && c.statut !== filterStatut.value) return false
    return true
  })
})

const statutBadge = (s) => ({ EN_ATTENTE:'badge-amber', APPROUVE:'badge-green', REFUSE:'badge-red' }[s] || 'badge-slate')
const statutLabel = (s) => ({ EN_ATTENTE:'En attente', APPROUVE:'Approuvé', REFUSE:'Refusé' }[s] || s)

/* ── Taux de présence par département ────── */
const presenceDepts = ref([
  { nom:'Développement', presents:7, total:8,  taux:88, color:'linear-gradient(90deg,#3b82f6,#06b6d4)' },
  { nom:'Marketing',     presents:4, total:5,  taux:80, color:'linear-gradient(90deg,#8b5cf6,#a78bfa)' },
  { nom:'Design',        presents:3, total:4,  taux:75, color:'linear-gradient(90deg,#06b6d4,#22d3ee)' },
  { nom:'Finance',       presents:3, total:3,  taux:100,color:'linear-gradient(90deg,#10b981,#34d399)' },
  { nom:'RH',            presents:1, total:3,  taux:33, color:'linear-gradient(90deg,#ef4444,#f87171)' },
])

const filteredDepts = computed(() =>
  filterDept.value ? presenceDepts.value.filter(d => d.nom === filterDept.value) : presenceDepts.value
)

function exportRapport() { toast.info('Export', 'Génération du rapport RH en cours...') }
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
