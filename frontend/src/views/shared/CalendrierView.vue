<template>
  <div class="page">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1>Calendrier</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Jours fériés et événements de l'entreprise</p>
      </div>
      <div class="flex gap-3">
        <button class="btn-ghost" @click="moisPrecedent"><i class="fa-solid fa-chevron-left"></i></button>
        <span style="font-size:14px;font-weight:600;color:var(--text-primary);min-width:160px;text-align:center;padding-top:6px">
          {{ moisLabel }} {{ annee }}
        </span>
        <button class="btn-ghost" @click="moisSuivant"><i class="fa-solid fa-chevron-right"></i></button>
        <button class="btn-ghost" @click="aujourd()">Aujourd'hui</button>
      </div>
    </div>

    <!-- Légende -->
    <div class="flex gap-4 mb-4">
      <div class="flex items-center gap-2"><div style="width:12px;height:12px;border-radius:3px;background:#ef4444"></div><span style="font-size:12px;color:var(--text-muted)">Jour férié</span></div>
      <div class="flex items-center gap-2"><div style="width:12px;height:12px;border-radius:3px;background:#3b82f6"></div><span style="font-size:12px;color:var(--text-muted)">Aujourd'hui</span></div>
      <div class="flex items-center gap-2"><div style="width:12px;height:12px;border-radius:3px;background:#10b981"></div><span style="font-size:12px;color:var(--text-muted)">Weekend</span></div>
    </div>

    <!-- Calendrier -->
    <div class="card">
      <!-- Jours de la semaine -->
      <div class="cal-grid" style="margin-bottom:8px">
        <div v-for="j in joursNoms" :key="j" style="text-align:center;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;padding:8px 0">{{ j }}</div>
      </div>
      <!-- Jours du mois -->
      <div class="cal-grid">
        <div v-for="(jour, i) in cellules" :key="i"
          class="cal-cell"
          :class="{
            'cal-vide': !jour.numero,
            'cal-ferie': jour.ferie,
            'cal-weekend': jour.weekend && !jour.ferie,
            'cal-today': jour.today,
          }"
          @click="jour.numero && selectionnerJour(jour)">
          <div v-if="jour.numero">
            <div class="cal-num">{{ jour.numero }}</div>
            <div v-if="jour.ferie" class="cal-label">{{ jour.ferie }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Prochains jours fériés -->
    <div class="card mt-5">
      <div style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:16px">
        <i class="fa-solid fa-calendar-star" style="color:#ef4444;margin-right:8px"></i>
        Prochains jours fériés
      </div>
      <div v-for="f in prochainsFeries" :key="f.date" class="flex items-center gap-4 mb-3">
        <div style="width:44px;height:44px;border-radius:12px;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <i class="fa-solid fa-flag" style="color:#ef4444"></i>
        </div>
        <div style="flex:1">
          <div style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ f.nom }}</div>
          <div style="font-size:11px;color:var(--text-muted)">{{ f.dateLabel }}</div>
        </div>
        <span class="badge" :class="f.dans <= 7 ? 'badge-red' : f.dans <= 30 ? 'badge-amber' : 'badge-slate'">
          {{ f.dans === 0 ? "Aujourd'hui" : f.dans === 1 ? 'Demain' : `Dans ${f.dans} jours` }}
        </span>
      </div>
    </div>

    <!-- Modal jour sélectionné -->
    <ModalBase v-model="showJourModal" :title="jourSelectionne?.ferie || 'Détails du jour'">
      <div v-if="jourSelectionne" style="text-align:center;padding:16px 0">
        <div style="font-size:48px;font-weight:700;color:var(--brand-primary)">{{ jourSelectionne.numero }}</div>
        <div style="font-size:16px;color:var(--text-muted);margin-bottom:16px">{{ moisLabel }} {{ annee }}</div>
        <div v-if="jourSelectionne.ferie" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);border-radius:12px;padding:16px;margin-bottom:16px">
          <i class="fa-solid fa-flag" style="color:#ef4444;font-size:24px;margin-bottom:8px;display:block"></i>
          <div style="font-size:15px;font-weight:600;color:#ef4444">{{ jourSelectionne.ferie }}</div>
          <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Jour férié national — Journée non travaillée</div>
        </div>
        <button class="btn-ghost w-full" style="justify-content:center" @click="showJourModal=false">Fermer</button>
      </div>
    </ModalBase>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import ModalBase from '@/components/shared/ModalBase.vue'

const JOURS_FERIES = {
  '01-01': 'Nouvel An',
  '03-20': 'Fête de l\'Indépendance',
  '04-09': 'Fête des Martyrs',
  '05-01': 'Fête du Travail',
  '07-25': 'Fête de la République',
  '08-13': 'Fête de la Femme',
  '10-15': 'Fête de l\'Évacuation',
  '11-07': 'Fête du 7 Novembre',
}

const today = new Date()
const moisCourant = ref(today.getMonth())
const annee = ref(today.getFullYear())
const showJourModal = ref(false)
const jourSelectionne = ref(null)

const joursNoms = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']
const moisNoms  = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre']

const moisLabel = computed(() => moisNoms[moisCourant.value])

function moisPrecedent() { if (moisCourant.value === 0) { moisCourant.value = 11; annee.value-- } else moisCourant.value-- }
function moisSuivant()   { if (moisCourant.value === 11) { moisCourant.value = 0; annee.value++ } else moisCourant.value++ }
function aujourd()       { moisCourant.value = today.getMonth(); annee.value = today.getFullYear() }

function ferieKey(d, m) {
  return `${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`
}

const cellules = computed(() => {
  const premier = new Date(annee.value, moisCourant.value, 1)
  const dernierJour = new Date(annee.value, moisCourant.value + 1, 0).getDate()
  let debut = premier.getDay() - 1
  if (debut < 0) debut = 6

  const cells = []
  for (let i = 0; i < debut; i++) cells.push({ numero: null })
  for (let d = 1; d <= dernierJour; d++) {
    const date = new Date(annee.value, moisCourant.value, d)
    const jourSemaine = date.getDay()
    const key = ferieKey(d, moisCourant.value)
    cells.push({
      numero: d,
      weekend: jourSemaine === 0 || jourSemaine === 6,
      ferie: JOURS_FERIES[key] || null,
      today: d === today.getDate() && moisCourant.value === today.getMonth() && annee.value === today.getFullYear(),
    })
  }
  return cells
})

const prochainsFeries = computed(() => {
  const result = []
  const an = today.getFullYear()
  for (const [key, nom] of Object.entries(JOURS_FERIES)) {
    const [m, d] = key.split('-').map(Number)
    let date = new Date(an, m - 1, d)
    if (date < today) date = new Date(an + 1, m - 1, d)
    const dans = Math.ceil((date - today) / 86400000)
    result.push({
      nom, date,
      dateLabel: date.toLocaleDateString('fr-FR', { day:'numeric', month:'long', year:'numeric' }),
      dans,
    })
  }
  return result.sort((a, b) => a.dans - b.dans).slice(0, 5)
})

function selectionnerJour(jour) {
  jourSelectionne.value = jour
  if (jour.ferie || jour.today) showJourModal.value = true
}
</script>

<style scoped>
.cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:4px; }
.cal-cell {
  min-height:80px; border-radius:10px; padding:8px;
  border:1px solid var(--border); cursor:pointer;
  transition:background 0.15s;
  background:var(--bg-card);
}
.cal-cell:hover { background:var(--bg-elevated); }
.cal-vide { border:none; background:transparent; cursor:default; }
.cal-vide:hover { background:transparent; }
.cal-num { font-size:14px; font-weight:600; color:var(--text-primary); }
.cal-label { font-size:9px; color:#ef4444; margin-top:4px; font-weight:600; line-height:1.2; }
.cal-ferie { background:rgba(239,68,68,0.08) !important; border-color:rgba(239,68,68,0.3) !important; }
.cal-ferie .cal-num { color:#ef4444; }
.cal-weekend .cal-num { color:var(--text-muted); }
.cal-weekend { background:rgba(16,185,129,0.05) !important; }
.cal-today { background:rgba(59,130,246,0.12) !important; border-color:rgba(59,130,246,0.4) !important; }
.cal-today .cal-num { color:#3b82f6; font-size:16px; }
.w-full { width:100%; }
</style>
