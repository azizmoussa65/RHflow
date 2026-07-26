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
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px">Recrutements & départs par mois</div>
          </div>
          <select class="form-select" style="width:auto;padding:6px 12px;font-size:12px">
            <option>2025</option><option>2024</option>
          </select>
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
        <div class="space-y-3">
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
        <div class="space-y-4">
          <div v-for="p in filteredProjets" :key="p.id">
            <div class="flex items-start justify-between mb-2">
              <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ p.nom }}</div>
                <div style="font-size:11px;color:var(--text-muted)">{{ p.responsable }} · {{ p.tachesFaites }}/{{ p.tachesTotal }} tâches</div>
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
              <div style="font-size:11px;color:var(--text-muted);min-width:34px;text-align:right">{{ Math.round(s.count/totalTaches*100) }}%</div>
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
        <div class="space-y-3">
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

const toast = inject('toast')

/* ── Filtres ─────────────────────────────── */
const filterProjet  = ref('')
const filterPeriode = ref('mois')
const filterEmploye = ref('')
function resetFilters() { filterProjet.value = ''; filterPeriode.value = 'mois'; filterEmploye.value = '' }

/* ── Stats cards ─────────────────────────── */
const stats = ref({ totalEmployes:0, projetsActifs:0, congesEnAttente:0, contratsActifs:0 })

/* ── Évolution effectifs ─────────────────── */
const chartBars = [
  {h:60,label:'Jan',value:18},{h:45,label:'Fév',value:14},{h:75,label:'Mar',value:22},
  {h:55,label:'Avr',value:17},{h:90,label:'Mai',value:27},{h:65,label:'Jun',value:20},
  {h:80,label:'Jul',value:24},{h:70,label:'Aoû',value:21},{h:50,label:'Sep',value:15},
  {h:85,label:'Oct',value:26},{h:40,label:'Nov',value:12},{h:100,label:'Déc',value:30},
]

/* ── Départements ────────────────────────── */
const depts = [
  {name:'Développement', pct:38, color:'linear-gradient(90deg,#3b82f6,#06b6d4)'},
  {name:'Marketing',     pct:21, color:'linear-gradient(90deg,#8b5cf6,#a78bfa)'},
  {name:'Design',        pct:17, color:'linear-gradient(90deg,#06b6d4,#22d3ee)'},
  {name:'Finance',       pct:13, color:'linear-gradient(90deg,#10b981,#34d399)'},
  {name:'RH',            pct:11, color:'linear-gradient(90deg,#f59e0b,#fbbf24)'},
]

/* ── Projets avec avancement ─────────────── */
const projets = ref([
  { id:1, nom:'ERP v3',               responsable:'Yassine Aouat',  avancement:75, tachesFaites:9,  tachesTotal:12, statut:'En cours', couleur:'linear-gradient(90deg,#3b82f6,#06b6d4)' },
  { id:2, nom:'Site Web Corporate',   responsable:'Karima Belhadj', avancement:45, tachesFaites:5,  tachesTotal:11, statut:'En cours', couleur:'linear-gradient(90deg,#8b5cf6,#a78bfa)' },
  { id:3, nom:'App Mobile RH',        responsable:'Sana Mrad',      avancement:20, tachesFaites:2,  tachesTotal:10, statut:'En retard',couleur:'linear-gradient(90deg,#ef4444,#f87171)' },
  { id:4, nom:'Migration Cloud',      responsable:'Mehdi Khelifi',  avancement:90, tachesFaites:9,  tachesTotal:10, statut:'En cours', couleur:'linear-gradient(90deg,#10b981,#34d399)' },
  { id:5, nom:'Refonte Intranet',     responsable:'Lina Bouzid',    avancement:60, tachesFaites:6,  tachesTotal:10, statut:'En cours', couleur:'linear-gradient(90deg,#f59e0b,#fbbf24)' },
])

const filteredProjets = computed(() =>
  filterProjet.value ? projets.value.filter(p => p.id === Number(filterProjet.value)) : projets.value
)

/* ── Tâches par statut ───────────────────── */
const tacheStatuts = ref([
  { label:'À faire',  count:18, color:'#94a3b8' },
  { label:'En cours', count:12, color:'#f59e0b' },
  { label:'Terminé',  count:24, color:'#10b981' },
])
const totalTaches = computed(() => tacheStatuts.value.reduce((s,x) => s + x.count, 0))

const donutGradient = computed(() => {
  let deg = 0
  const segs = tacheStatuts.value.map(s => {
    const end = deg + (s.count / totalTaches.value * 360)
    const seg = `${s.color} ${deg}deg ${end}deg`
    deg = end
    return seg
  })
  return `conic-gradient(${segs.join(', ')})`
})

/* ── Tâches par employé ──────────────────── */
const employes = ref([
  { id:1, nom:'Yassine Aouat'  },
  { id:2, nom:'Karima Belhadj' },
  { id:3, nom:'Sana Mrad'      },
  { id:4, nom:'Mehdi Khelifi'  },
  { id:5, nom:'Lina Bouzid'    },
])

const tachesParEmploye = ref([
  { id:1, nom:'Yassine Aouat',  initials:'YA', aFaire:3, enCours:2, termine:6, total:11, color:'linear-gradient(135deg,#8b5cf6,#a78bfa)' },
  { id:2, nom:'Karima Belhadj', initials:'KB', aFaire:5, enCours:3, termine:4, total:12, color:'linear-gradient(135deg,#06b6d4,#22d3ee)' },
  { id:3, nom:'Sana Mrad',      initials:'SM', aFaire:2, enCours:4, termine:8, total:14, color:'linear-gradient(135deg,#10b981,#34d399)' },
  { id:4, nom:'Mehdi Khelifi',  initials:'MK', aFaire:4, enCours:1, termine:5, total:10, color:'linear-gradient(135deg,#f59e0b,#fbbf24)' },
  { id:5, nom:'Lina Bouzid',    initials:'LB', aFaire:4, enCours:2, termine:1, total: 7, color:'linear-gradient(135deg,#ef4444,#f87171)' },
])

const filteredTachesParEmploye = computed(() =>
  filterEmploye.value ? tachesParEmploye.value.filter(e => e.id === Number(filterEmploye.value)) : tachesParEmploye.value
)

/* ── Activité récente ────────────────────── */
const activities = ref([
  { id:1, title:'Nouveau employé ajouté',    sub:'Karima Belhadj — Frontend · il y a 2h',     dotClass:'bg-green-dot'  },
  { id:2, title:'Demande de congé soumise',  sub:'Yassine Aouat — 5 jours · il y a 4h',       dotClass:'bg-amber-dot'  },
  { id:3, title:'Contrat renouvelé',         sub:'Sana Mrad — CDI · il y a 1j',               dotClass:'bg-blue-dot'   },
  { id:4, title:'Évaluation complétée',      sub:'Équipe Backend — Score: 4.2/5 · il y a 2j', dotClass:'bg-red-dot'    },
  { id:5, title:'Nouveau projet créé',       sub:'Refonte Intranet — Deadline: 30 Juin · il y a 3j', dotClass:'bg-purple-dot' },
])

/* ── Congés en attente ───────────────────── */
const pendingConges = ref([
  { id:1, nom:'Yassine Aouat',  initials:'YA', jours:5,  dates:'15–20 Mar 2025', color:'linear-gradient(135deg,#8b5cf6,#a78bfa)' },
  { id:2, nom:'Karima Belhadj', initials:'KB', jours:3,  dates:'22–24 Mar 2025', color:'linear-gradient(135deg,#06b6d4,#22d3ee)' },
  { id:3, nom:'Sana Mrad',      initials:'SM', jours:10, dates:'1–11 Avr 2025',  color:'linear-gradient(135deg,#10b981,#34d399)' },
])

async function approuver(c) {
  try { await congeService.approve(c.id) } catch (_) {}
  pendingConges.value = pendingConges.value.filter(x => x.id !== c.id)
  toast.success('Congé approuvé', `La demande de ${c.nom} a été acceptée.`)
}
async function refuser(c) {
  try { await congeService.refuse(c.id) } catch (_) {}
  pendingConges.value = pendingConges.value.filter(x => x.id !== c.id)
  toast.info('Congé refusé', `La demande de ${c.nom} a été refusée.`)
}
function exportRapport() { toast.info('Export', 'Génération du rapport en cours...') }

onMounted(async () => {
  try {
    const data = await dashboardService.getStats()
    stats.value = data
  } catch (_) {
    stats.value = { totalEmployes:24, projetsActifs:5, congesEnAttente:3, contratsActifs:21 }
  }
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
