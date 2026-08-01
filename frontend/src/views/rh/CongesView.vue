<template>
  <div class="page">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1>{{ onglet === 'gestion' ? 'Gestion des Congés' : 'Mes Congés' }}</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px">
          {{ onglet === 'gestion' ? 'Demandes, validation et suivi des absences' : 'Vos demandes personnelles de congé' }}
        </p>
      </div>
      <div class="flex gap-3">
        <!-- Tabs -->
        <div style="display:flex;background:var(--bg-elevated);border-radius:10px;padding:3px;gap:2px">
          <button :class="onglet==='gestion' ? 'btn-primary' : 'btn-ghost'"
            style="padding:6px 16px;font-size:13px"
            @click="onglet='gestion'">
            <i class="fa-solid fa-users"></i> Gestion
          </button>
          <button :class="onglet==='perso' ? 'btn-primary' : 'btn-ghost'"
            style="padding:6px 16px;font-size:13px"
            @click="onglet='perso'">
            <i class="fa-solid fa-user"></i> Mes congés
          </button>
        </div>
        <button v-if="onglet==='gestion'" class="btn-primary" @click="openAdd">
          <i class="fa-solid fa-plus"></i> Nouvelle demande
        </button>
        <button v-else class="btn-primary" @click="showModalPerso=true">
          <i class="fa-solid fa-plus"></i> Demander un congé
        </button>
      </div>
    </div>

    <!-- ═══════════ ONGLET GESTION ═══════════ -->
    <template v-if="onglet==='gestion'">
      <div class="grid grid-cols-4 gap-4 mb-5">
        <div class="card-sm text-center"><div style="font-size:1.5rem;font-weight:700;color:#f59e0b">{{ cnt('EN_ATTENTE') }}</div><div style="font-size:12px;color:var(--text-muted);margin-top:4px">En attente</div></div>
        <div class="card-sm text-center"><div style="font-size:1.5rem;font-weight:700;color:#10b981">{{ cnt('APPROUVE') }}</div><div style="font-size:12px;color:var(--text-muted);margin-top:4px">Approuvés</div></div>
        <div class="card-sm text-center"><div style="font-size:1.5rem;font-weight:700;color:#ef4444">{{ cnt('REFUSE') }}</div><div style="font-size:12px;color:var(--text-muted);margin-top:4px">Refusés</div></div>
        <div class="card-sm text-center"><div style="font-size:1.5rem;font-weight:700;color:#3b82f6">{{ cnt('EN_COURS') }}</div><div style="font-size:12px;color:var(--text-muted);margin-top:4px">En cours</div></div>
      </div>

      <div class="card">
        <div class="table-wrapper">
          <table>
            <thead><tr>
              <th>Employé</th><th>Type</th><th>Du</th><th>Au</th><th>Durée</th><th>Motif</th><th>Statut</th><th>Actions</th>
            </tr></thead>
            <tbody>
              <tr v-for="c in conges" :key="c.id">
                <td><div class="flex items-center gap-3">
                  <div class="avatar" :style="{ background: empColor(c.initials) }">{{ c.initials }}</div>
                  <div>
                    {{ c.employe }}
                    <span v-if="c.isRH" style="font-size:10px;background:rgba(139,92,246,0.15);color:#8b5cf6;border-radius:4px;padding:1px 5px;margin-left:4px">RH</span>
                  </div>
                </div></td>
                <td>{{ c.type }}</td>
                <td>{{ c.dateDebut }}</td>
                <td>{{ c.dateFin }}</td>
                <td>{{ c.nbJours }} jours</td>
                <td>{{ c.motif }}</td>
                <td><span class="badge" :class="statutBadge(c.statut)">{{ statutLabel(c.statut) }}</span></td>
                <td>
                  <!-- RH can approve employee leaves but not their own; Admin can approve all -->
                  <div class="flex gap-2" v-if="c.statut === 'EN_ATTENTE' && (auth.isAdmin || !c.isRH)">
                    <button class="btn-edit" style="padding:5px 10px;font-size:11px" @click="approuver(c)"><i class="fa-solid fa-check"></i> Approuver</button>
                    <button class="btn-danger" style="padding:5px 10px;font-size:11px" @click="refuser(c)"><i class="fa-solid fa-xmark"></i> Refuser</button>
                  </div>
                  <span v-else-if="c.statut === 'EN_ATTENTE' && c.isRH" style="font-size:11px;color:var(--text-muted)">
                    <i class="fa-solid fa-clock" style="margin-right:4px"></i>Attente admin
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- ═══════════ ONGLET MES CONGÉS (RH personnel) ═══════════ -->
    <template v-else>
      <!-- Solde -->
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
          <div style="font-size:1.8rem;font-weight:700;color:#f59e0b">30</div>
          <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Total annuel</div>
        </div>
      </div>

      <!-- Historique personnel -->
      <div class="card">
        <div style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:16px">Historique de mes demandes</div>
        <div class="space-y-3">
          <div v-for="c in mesCongesPerso" :key="c.id"
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
          <div v-if="mesCongesPerso.length === 0" style="text-align:center;padding:32px;color:var(--text-muted)">
            Aucune demande personnelle.
          </div>
        </div>
      </div>
    </template>

    <!-- Details modal -->
    <ModalBase v-model="showDetailsModal" title="Détails du congé">
      <div v-if="congeActif" style="display:flex;flex-direction:column;gap:16px">
        <div class="flex items-center gap-3" style="padding-bottom:16px;border-bottom:1px solid var(--border)">
          <div class="avatar-lg" :style="{ background: empColor(congeActif.initials) }">{{ congeActif.initials }}</div>
          <div>
            <div style="font-size:16px;font-weight:700;color:var(--text-primary)">{{ congeActif.employe }}</div>
            <span class="badge" :class="statutBadge(congeActif.statut)">{{ statutLabel(congeActif.statut) }}</span>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div><div style="font-size:11px;color:var(--text-muted);margin-bottom:4px">TYPE</div>
            <div style="font-size:14px;font-weight:600;color:var(--text-primary)">{{ congeActif.type }}</div></div>
          <div><div style="font-size:11px;color:var(--text-muted);margin-bottom:4px">DURÉE</div>
            <div style="font-size:14px;font-weight:600;color:var(--text-primary)">{{ congeActif.nbJours }} jours</div></div>
          <div><div style="font-size:11px;color:var(--text-muted);margin-bottom:4px">DATE DÉBUT</div>
            <div style="font-size:14px;font-weight:600;color:var(--text-primary)">{{ congeActif.dateDebut }}</div></div>
          <div><div style="font-size:11px;color:var(--text-muted);margin-bottom:4px">DATE FIN</div>
            <div style="font-size:14px;font-weight:600;color:var(--text-primary)">{{ congeActif.dateFin }}</div></div>
        </div>
        <div v-if="congeActif.motif">
          <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px">MOTIF</div>
          <div style="font-size:14px;color:var(--text-primary);background:var(--bg-elevated);padding:10px 14px;border-radius:8px">{{ congeActif.motif }}</div>
        </div>
        <button class="btn-ghost" style="justify-content:center" @click="showDetailsModal=false">Fermer</button>
      </div>
    </ModalBase>

    <!-- Modal Nouvelle demande (pour un employé) -->
    <ModalBase v-model="showModal" title="Nouvelle demande de congé">
      <div class="form-group"><label class="form-label">Employé</label>
        <select class="form-select" v-model="form.employeId" @change="onEmployeChange">
          <option v-for="e in employesList" :key="e.id" :value="e.id">{{ e.name }}</option>
        </select></div>
      <div class="form-group"><label class="form-label">Type de congé</label>
        <select class="form-select" v-model="form.type">
          <option>Annuel</option><option>Maladie</option><option>Maternité/Paternité</option><option>Exceptionnel</option>
        </select></div>
      <div class="grid grid-cols-2 gap-4">
        <div class="form-group"><label class="form-label">Du</label>
          <DatePickerFerie v-model="form.dateDebut" placeholder="Choisir date début" @change="checkFeries" /></div>
        <div class="form-group"><label class="form-label">Au</label>
          <DatePickerFerie v-model="form.dateFin" placeholder="Choisir date fin" @change="checkFeries" /></div>
      </div>
      <div v-if="feriesDansPeriode.length > 0" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);border-radius:10px;padding:12px 14px;margin-bottom:12px">
        <div style="font-size:12px;font-weight:600;color:#ef4444;margin-bottom:8px">
          <i class="fa-solid fa-triangle-exclamation" style="margin-right:6px"></i>
          {{ feriesDansPeriode.length }} jour(s) férié(s) dans la période sélectionnée
        </div>
        <div v-for="f in feriesDansPeriode" :key="f.date" style="font-size:11px;color:var(--text-muted);display:flex;align-items:center;gap:6px;margin-bottom:3px">
          <i class="fa-solid fa-flag" style="color:#ef4444;font-size:10px"></i>
          <strong style="color:var(--text-primary)">{{ f.label }}</strong> — {{ f.date }}
        </div>
      </div>
      <div class="form-group"><label class="form-label">Motif</label>
        <textarea class="form-input" rows="2" v-model="form.motif" placeholder="Décrivez le motif..."></textarea></div>
      <div class="flex gap-3 mt-4">
        <button class="btn-primary flex-1" style="justify-content:center" @click="saveConge">
          <i class="fa-solid fa-paper-plane"></i> Soumettre
        </button>
        <button class="btn-ghost" @click="showModal=false">Annuler</button>
      </div>
    </ModalBase>

    <!-- Modal Ma demande personnelle (RH pour lui-même) -->
    <ModalBase v-model="showModalPerso" title="Demander un congé personnel">
      <div style="background:rgba(139,92,246,0.08);border:1px solid rgba(139,92,246,0.25);border-radius:10px;padding:10px 14px;margin-bottom:16px;font-size:12px;color:#8b5cf6">
        <i class="fa-solid fa-info-circle" style="margin-right:6px"></i>
        Cette demande sera soumise à l'<strong>Administrateur</strong> pour approbation.
      </div>
      <div class="form-group"><label class="form-label">Type de congé</label>
        <select class="form-select" v-model="formPerso.type">
          <option>Annuel</option><option>Maladie</option><option>Maternité/Paternité</option><option>Exceptionnel</option>
        </select></div>
      <div class="grid grid-cols-2 gap-4">
        <div class="form-group"><label class="form-label">Du</label>
          <DatePickerFerie v-model="formPerso.dateDebut" placeholder="Date début" @change="checkFeriesPerso" /></div>
        <div class="form-group"><label class="form-label">Au</label>
          <DatePickerFerie v-model="formPerso.dateFin" placeholder="Date fin" @change="checkFeriesPerso" /></div>
      </div>
      <div v-if="feriesPerso.length > 0" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);border-radius:10px;padding:12px 14px;margin-bottom:12px">
        <div style="font-size:12px;font-weight:600;color:#ef4444;margin-bottom:6px">
          <i class="fa-solid fa-triangle-exclamation" style="margin-right:6px"></i>
          {{ feriesPerso.length }} jour(s) férié(s) dans la période
        </div>
        <div v-for="f in feriesPerso" :key="f.date" style="font-size:11px;color:var(--text-muted)">
          <i class="fa-solid fa-flag" style="color:#ef4444;font-size:10px;margin-right:4px"></i>
          <strong style="color:var(--text-primary)">{{ f.label }}</strong> — {{ f.date }}
        </div>
      </div>
      <div class="form-group"><label class="form-label">Motif</label>
        <textarea class="form-input" rows="2" v-model="formPerso.motif" placeholder="Précisez le motif..."></textarea></div>
      <div class="flex gap-3 mt-4">
        <button class="btn-primary flex-1" style="justify-content:center" @click="soumettreDemande">
          <i class="fa-solid fa-paper-plane"></i> Soumettre à l'Administrateur
        </button>
        <button class="btn-ghost" @click="showModalPerso=false">Annuler</button>
      </div>
    </ModalBase>
  </div>
</template>

<script setup>
import { ref, inject, onMounted, computed } from 'vue'
import ModalBase from '@/components/shared/ModalBase.vue'
import DatePickerFerie from '@/components/shared/DatePickerFerie.vue'
import congeService from '@/services/congeService.js'
import employeService from '@/services/employeService.js'
import { useAuthStore } from '@/stores/auth.js'

const auth  = useAuthStore()
const toast = inject('toast')

const onglet         = ref('gestion')
const showModal      = ref(false)
const showModalPerso = ref(false)
const showDetailsModal = ref(false)
const congeActif     = ref(null)

// ── Jours fériés ──────────────────────────────────────────────────────
const JOURS_FERIES = {
  '01-01':'Nouvel An','03-20':"Fête de l'Indépendance",'04-09':'Fête des Martyrs',
  '05-01':'Fête du Travail','07-25':'Fête de la République','08-13':'Fête de la Femme',
  '10-15':"Fête de l'Évacuation",'11-07':'Fête du 7 Novembre',
}

function feriesInRange(d1, d2) {
  const result = []
  if (!d1 || !d2) return result
  const debut = new Date(d1); const fin = new Date(d2)
  if (fin < debut) return result
  const years = debut.getFullYear() === fin.getFullYear()
    ? [debut.getFullYear()]
    : [debut.getFullYear(), fin.getFullYear()]
  for (const [key, nom] of Object.entries(JOURS_FERIES)) {
    const [m, d] = key.split('-').map(Number)
    for (const y of years) {
      const fd = new Date(y, m - 1, d)
      if (fd >= debut && fd <= fin)
        result.push({ date: fd.toLocaleDateString('fr-FR', { day:'numeric', month:'long', year:'numeric' }), label: nom })
    }
  }
  return result
}

// Fériés pour le modal gestion
const feriesDansPeriode = ref([])
const form = ref({ employeId: null, employeName: '', type: 'Annuel', dateDebut: '', dateFin: '', motif: '' })
function checkFeries() { feriesDansPeriode.value = feriesInRange(form.value.dateDebut, form.value.dateFin) }

// Fériés pour le modal personnel
const feriesPerso = ref([])
const formPerso = ref({ type: 'Annuel', dateDebut: '', dateFin: '', motif: '' })
function checkFeriesPerso() { feriesPerso.value = feriesInRange(formPerso.value.dateDebut, formPerso.value.dateFin) }

const employesList = ref([])

const AVATAR_COLORS = [
  'linear-gradient(135deg,#3b82f6,#06b6d4)',
  'linear-gradient(135deg,#8b5cf6,#a78bfa)',
  'linear-gradient(135deg,#10b981,#34d399)',
  'linear-gradient(135deg,#f59e0b,#fbbf24)',
  'linear-gradient(135deg,#ef4444,#f87171)',
  'linear-gradient(135deg,#06b6d4,#67e8f9)',
]
function empColor(initials) {
  const idx = initials ? initials.charCodeAt(0) % AVATAR_COLORS.length : 0
  return AVATAR_COLORS[idx]
}

const conges = ref([])

// Congés personnels du RH (les siens, identifiés par employeId, avec fallback sur le flag de démo isRH)
const mesCongesPerso = computed(() => conges.value.filter(c => c.isRH || (auth.user?.id && c.employeId === auth.user.id)))
const joursPris      = computed(() => mesCongesPerso.value.filter(c => c.statut === 'APPROUVE').reduce((s,c) => s + (c.nbJours||0), 0))
const soldeRestant   = computed(() => Math.max(0, 30 - joursPris.value))

const cnt = (s) => conges.value.filter(c => c.statut === s).length
const statutBadge = (s) => ({ EN_ATTENTE:'badge-amber', APPROUVE:'badge-green', REFUSE:'badge-red', EN_COURS:'badge-blue' }[s] || 'badge-slate')
const statutLabel = (s) => ({ EN_ATTENTE:'En attente', APPROUVE:'Approuvé', REFUSE:'Refusé', EN_COURS:'En cours' }[s] || s)
const typeIcon    = (t) => ({ 'Annuel':'fa-solid fa-umbrella-beach', 'Maladie':'fa-solid fa-hospital', 'Maternité/Paternité':'fa-solid fa-baby', 'Exceptionnel':'fa-solid fa-star' }[t] || 'fa-solid fa-calendar')

function onEmployeChange() {
  const emp = employesList.value.find(e => e.id === form.value.employeId)
  form.value.employeName = emp ? emp.name : ''
}

async function approuver(c) {
  try { await congeService.approve(c.id) } catch (_) {}
  c.statut = 'APPROUVE'
  toast.success('Congé approuvé', `La demande de ${c.employe} a été acceptée.`)
}
async function refuser(c) {
  try { await congeService.refuse(c.id) } catch (_) {}
  c.statut = 'REFUSE'
  toast.info('Congé refusé', `La demande de ${c.employe} a été refusée.`)
}

function openAdd() {
  const first = employesList.value[0]
  form.value = { employeId: first?.id ?? null, employeName: first?.name ?? '', type: 'Annuel', dateDebut: '', dateFin: '', motif: '' }
  feriesDansPeriode.value = []
  showModal.value = true
}

function computeNbJours(d1, d2) {
  if (!d1 || !d2) return 1
  return Math.max(1, Math.ceil((new Date(d2) - new Date(d1)) / 86400000))
}

async function saveConge() {
  const payload = { employeId: form.value.employeId, type: form.value.type, dateDebut: form.value.dateDebut, dateFin: form.value.dateFin, motif: form.value.motif }
  try {
    const created = await congeService.create(payload)
    conges.value.unshift(created)
    toast.success('Demande soumise', 'La demande de congé a été enregistrée en base de données.')
    showModal.value = false
  } catch (e) {
    toast.error('Erreur', e.userMessage || 'Impossible d\'enregistrer la demande.')
  }
}

async function soumettreDemande() {
  if (!formPerso.value.dateDebut || !formPerso.value.dateFin) {
    toast.error('Dates manquantes', 'Veuillez choisir les dates de début et de fin.')
    return
  }
  const nbJours  = computeNbJours(formPerso.value.dateDebut, formPerso.value.dateFin)
  const userName = auth.user?.prenom && auth.user?.nom
    ? `${auth.user.prenom} ${auth.user.nom}`
    : 'Responsable RH'
  const initials = userName.split(' ').map(w => w[0] || '').join('').toUpperCase().slice(0, 2)

  const formatDate = (iso) => {
    if (!iso) return ''
    const [y, m, d] = iso.split('-')
    return `${d}/${m}/${y}`
  }

  const newConge = {
    id:        Date.now(),
    employe:   userName,
    initials,
    type:      formPerso.value.type,
    dateDebut: formatDate(formPerso.value.dateDebut),
    dateFin:   formatDate(formPerso.value.dateFin),
    nbJours,
    motif:     formPerso.value.motif,
    statut:    'EN_ATTENTE',
    isRH:      true,
  }

  try {
    const payload = { type: formPerso.value.type, dateDebut: formPerso.value.dateDebut, dateFin: formPerso.value.dateFin, motif: formPerso.value.motif }
    const created = await congeService.create(payload)
    conges.value.unshift({ ...created, isRH: true })
    toast.success('Demande envoyée', "Votre demande a été soumise à l'administrateur et enregistrée en base.")
    showModalPerso.value = false
    formPerso.value = { type: 'Annuel', dateDebut: '', dateFin: '', motif: '' }
    feriesPerso.value = []
  } catch (e) {
    toast.error('Erreur', e.userMessage || 'Impossible d\'enregistrer la demande.')
  }
}

onMounted(async () => {
  try {
    const data = await employeService.getAll()
    if (data) employesList.value = data.map(e => ({ id: e.id, name: `${e.prenom} ${e.nom}` }))
  } catch (_) {}
  try {
    const d = await congeService.getAll()
    if (d) conges.value = d
  } catch (_) {}
})
</script>
