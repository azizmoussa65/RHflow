<template>
  <div class="page">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1>Mes Demandes de Congé</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Suivez et gérez vos absences</p>
      </div>
      <button class="btn-primary" @click="showModal=true"><i class="fa-solid fa-plus"></i> Nouvelle demande</button>
    </div>

    <!-- Solde congés -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div class="card-sm text-center">
        <div style="font-size:1.8rem;font-weight:700;color:#3b82f6">{{ soldeRestant }}</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Jours restants</div>
      </div>
      <div class="card-sm text-center">
        <div style="font-size:1.8rem;font-weight:700;color:#10b981">{{ joursPris }}</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Jours pris</div>
      </div>
      <div class="card-sm text-center">
        <div style="font-size:1.8rem;font-weight:700;color:#f59e0b">{{ TOTAL_ANNUEL }}</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Total annuel</div>
      </div>
    </div>

    <!-- My requests -->
    <div class="card">
      <div style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:16px">Historique des demandes</div>
      <div class="space-y-3">
        <div v-for="c in mesConges" :key="c.id"
          class="flex items-center gap-4 p-4 rounded-xl"
          style="background:var(--bg-hover);border:1px solid var(--border)">
          <div style="width:42px;height:42px;border-radius:12px;background:var(--bg-elevated);display:flex;align-items:center;justify-content:center">
            <i :class="typeIcon(c.type)" style="color:var(--brand-primary)"></i>
          </div>
          <div style="flex:1">
            <div style="font-size:13px;font-weight:500;color:var(--text-primary)">{{ c.type }}</div>
            <div style="font-size:11px;color:var(--text-muted)">{{ c.dateDebut }} → {{ c.dateFin }} · {{ c.nbJours }} jours</div>
            <div v-if="c.motif" style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ c.motif }}</div>
          </div>
          <span class="badge" :class="statutBadge(c.statut)">{{ statutLabel(c.statut) }}</span>
        </div>
        <div v-if="mesConges.length === 0" style="text-align:center;padding:32px;color:var(--text-muted)">
          Aucune demande de congé.
        </div>
      </div>
    </div>

    <!-- Request modal -->
    <ModalBase v-model="showModal" title="Nouvelle demande de congé">
      <div class="form-group"><label class="form-label">Type de congé</label>
        <select class="form-select" v-model="form.type">
          <option>Congé annuel</option><option>Maladie</option><option>Maternité/Paternité</option><option>Exceptionnel</option>
        </select></div>
      <div class="grid grid-cols-2 gap-4">
        <div class="form-group"><label class="form-label">Du</label>
          <DatePickerFerie v-model="form.dateDebut" placeholder="Choisir date début" @change="checkFeries" /></div>
        <div class="form-group"><label class="form-label">Au</label>
          <DatePickerFerie v-model="form.dateFin" placeholder="Choisir date fin" @change="checkFeries" /></div>
      </div>

      <!-- Alerte jours fériés dans la période -->
      <div v-if="feriesDansPeriode.length > 0" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);border-radius:10px;padding:12px 14px;margin-bottom:12px">
        <div style="font-size:12px;font-weight:600;color:#ef4444;margin-bottom:8px">
          <i class="fa-solid fa-triangle-exclamation" style="margin-right:6px"></i>
          {{ feriesDansPeriode.length }} jour(s) férié(s) dans votre période
        </div>
        <div v-for="f in feriesDansPeriode" :key="f.date" style="font-size:11px;color:var(--text-muted);display:flex;align-items:center;gap:6px;margin-bottom:3px">
          <i class="fa-solid fa-flag" style="color:#ef4444;font-size:10px"></i>
          <strong style="color:var(--text-primary)">{{ f.label }}</strong> — {{ f.date }}
        </div>
      </div>

<div class="form-group"><label class="form-label">Motif</label>
        <textarea class="form-input" rows="2" v-model="form.motif" placeholder="Précisez le motif..."></textarea></div>
      <div class="flex gap-3 mt-4">
        <button class="btn-primary flex-1" style="justify-content:center" @click="submit">
          <i class="fa-solid fa-paper-plane"></i> Soumettre
        </button>
        <button class="btn-ghost" @click="showModal=false">Annuler</button>
      </div>
    </ModalBase>
  </div>
</template>

<script setup>
import { ref, inject, onMounted, computed } from 'vue'
import ModalBase from '@/components/shared/ModalBase.vue'
import DatePickerFerie from '@/components/shared/DatePickerFerie.vue'
import congeService from '@/services/congeService.js'
import { useAuthStore } from '@/stores/auth.js'

const auth  = useAuthStore()
const toast = inject('toast')
const TOTAL_ANNUEL = 30
const showModal = ref(false)
const form = ref({ type:'Congé annuel', dateDebut:'', dateFin:'', motif:'' })

// ── Jours fériés ──────────────────────────────────────────────────────
const JOURS_FERIES = {
  '01-01':'Nouvel An','03-20':"Fête de l'Indépendance",'04-09':'Fête des Martyrs',
  '05-01':'Fête du Travail','07-25':'Fête de la République','08-13':'Fête de la Femme',
  '10-15':"Fête de l'Évacuation",'11-07':'Fête du 7 Novembre',
}

const prochainsFeries = computed(() => {
  const today = new Date(); const an = today.getFullYear(); const res = []
  for (const [key, nom] of Object.entries(JOURS_FERIES)) {
    const [m, d] = key.split('-').map(Number)
    let date = new Date(an, m - 1, d)
    if (date < today) date = new Date(an + 1, m - 1, d)
    res.push({ key, nom, date, dateLabel: date.toLocaleDateString('fr-FR', { day:'numeric', month:'long', year:'numeric' }) })
  }
  return res.sort((a,b) => a.date - b.date).slice(0, 5)
})

const feriesDansPeriode = ref([])
function checkFeries() {
  feriesDansPeriode.value = []
  if (!form.value.dateDebut || !form.value.dateFin) return
  const debut = new Date(form.value.dateDebut)
  const fin   = new Date(form.value.dateFin)
  if (fin < debut) return
  const an1 = debut.getFullYear(); const an2 = fin.getFullYear()
  const years = an1 === an2 ? [an1] : [an1, an2]
  for (const [key, nom] of Object.entries(JOURS_FERIES)) {
    const [m, d] = key.split('-').map(Number)
    for (const y of years) {
      const fd = new Date(y, m - 1, d)
      if (fd >= debut && fd <= fin) {
        feriesDansPeriode.value.push({ date: fd.toLocaleDateString('fr-FR', { day:'numeric', month:'long', year:'numeric' }), label: nom })
      }
    }
  }
}

const mesConges = ref([
  { id:1, type:'Congé annuel', dateDebut:'15/01/2025', dateFin:'20/01/2025', nbJours:5, motif:'Vacances familiales', statut:'APPROUVE' },
  { id:2, type:'Maladie',      dateDebut:'05/03/2025', dateFin:'07/03/2025', nbJours:3, motif:'Ordonnance médicale', statut:'APPROUVE' },
])

const joursPris    = computed(() => mesConges.value.filter(c => c.statut === 'APPROUVE').reduce((s, c) => s + (c.nbJours || 0), 0))
const soldeRestant = computed(() => Math.max(0, TOTAL_ANNUEL - joursPris.value))

const typeIcon     = (t) => ({ 'Congé annuel':'fa-solid fa-umbrella-beach', Maladie:'fa-solid fa-hospital', 'Maternité/Paternité':'fa-solid fa-baby', Exceptionnel:'fa-solid fa-star' }[t] || 'fa-solid fa-calendar')
const statutBadge  = (s) => ({ EN_ATTENTE:'badge-amber', APPROUVE:'badge-green', REFUSE:'badge-red' }[s] || 'badge-slate')
const statutLabel  = (s) => ({ EN_ATTENTE:'En attente', APPROUVE:'Approuvé', REFUSE:'Refusé' }[s] || s)

async function submit() {
  try {
    const created = await congeService.create(form.value)
    mesConges.value.unshift(created)
    toast.success('Demande envoyée', 'Votre demande a été enregistrée en base de données.')
    showModal.value = false
    form.value = { type:'Congé annuel', dateDebut:'', dateFin:'', motif:'' }
  } catch (e) {
    toast.error('Erreur', e.userMessage || 'Impossible d\'enregistrer la demande.')
  }
}

onMounted(async () => {
  try {
    const d = await congeService.getAll({ employeId: auth.user?.id })
    if (d) mesConges.value = d
  } catch (_) {}
})
</script>
