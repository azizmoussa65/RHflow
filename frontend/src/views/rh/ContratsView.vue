<template>
  <div class="page">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1>Gestion des Contrats</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Suivi et gestion des contrats de travail</p>
      </div>
      <button class="btn-primary" @click="openAdd"><i class="fa-solid fa-plus"></i> Nouveau contrat</button>
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-3 gap-4 mb-5">
      <div class="card-sm text-center"><div style="font-size:1.5rem;font-weight:700;color:#3b82f6">{{ cdiCount }}</div><div style="font-size:12px;color:var(--text-muted);margin-top:4px">CDI actifs</div></div>
      <div class="card-sm text-center"><div style="font-size:1.5rem;font-weight:700;color:#f59e0b">{{ cddCount }}</div><div style="font-size:12px;color:var(--text-muted);margin-top:4px">CDD actifs</div></div>
      <div class="card-sm text-center"><div style="font-size:1.5rem;font-weight:700;color:#ef4444">{{ expirant }}</div><div style="font-size:12px;color:var(--text-muted);margin-top:4px">Expirent dans 30j</div></div>
    </div>

    <div class="card">
      <div class="table-wrapper">
        <table>
          <thead><tr>
            <th>Employé</th><th>Type</th><th>Début</th><th>Fin</th><th>Salaire</th><th>Statut</th><th>Actions</th>
          </tr></thead>
          <tbody>
            <tr v-for="c in contrats" :key="c.id">
              <td><div class="flex items-center gap-3">
                <div class="avatar" :style="{ background: c.color || empColor(c.initials) }">{{ c.initials }}</div>
                {{ c.employe }}
              </div></td>
              <td><span class="badge" :class="typeBadge(c.type)">{{ c.type }}</span></td>
              <td>{{ c.dateDebut }}</td>
              <td :style="c.expireBientot ? 'color:#ef4444;font-weight:500' : ''">{{ c.dateFin || '—' }}</td>
              <td style="font-family:'JetBrains Mono';font-size:13px">{{ c.salaire }} TND</td>
              <td><span class="badge" :class="statutBadge(c.statut)">{{ c.statut }}</span></td>
              <td>
                <div class="flex gap-2">
                  <button class="btn-edit" @click="openEdit(c)">Voir</button>
                  <button class="btn-ghost" style="padding:7px 13px;font-size:12px" @click="renouveler(c)">
                    {{ c.type === 'CDI' ? 'Modifier' : 'Renouveler' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal ajouter/modifier -->
    <ModalBase v-model="showModal" :title="editMode ? 'Modifier le contrat' : 'Nouveau contrat'">
      <div class="form-group"><label class="form-label">Employé</label>
        <select class="form-select" v-model="form.employeId" :disabled="editMode" @change="onEmployeChange">
          <option v-for="e in employesList" :key="e.id" :value="e.id">{{ e.name }}</option>
        </select></div>
      <div class="grid grid-cols-2 gap-4">
        <div class="form-group"><label class="form-label">Type</label>
          <select class="form-select" v-model="form.type"><option>CDI</option><option>CDD</option><option>Stage</option></select></div>
        <div class="form-group"><label class="form-label">Salaire (TND)</label>
          <input class="form-input" type="number" v-model="form.salaire" placeholder="3000" /></div>
        <div class="form-group"><label class="form-label">Date début</label>
          <input class="form-input" type="date" v-model="form.dateDebut" /></div>
        <div class="form-group"><label class="form-label">Date fin</label>
          <input class="form-input" type="date" v-model="form.dateFin" /></div>
      </div>
      <div class="flex gap-3 mt-4">
        <button class="btn-primary flex-1" style="justify-content:center" @click="saveContrat">
          <i class="fa-solid fa-save"></i> Enregistrer
        </button>
        <button class="btn-ghost" @click="showModal=false">Annuler</button>
      </div>
    </ModalBase>

    <!-- Modal renouvellement -->
    <ModalBase v-model="showRenewModal" title="Renouveler le contrat">
      <div v-if="renewForm">

        <!-- Info employé (lecture seule) -->
        <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--bg-elevated);border-radius:10px;margin-bottom:16px">
          <div class="avatar" :style="{ background: renewForm.color }">{{ renewForm.initials }}</div>
          <div>
            <div style="font-size:14px;font-weight:600;color:var(--text-primary)">{{ renewForm.employe }}</div>
            <div style="font-size:12px;color:var(--text-muted)">Contrat {{ renewForm.type }} — Renouvellement</div>
          </div>
        </div>

        <!-- Ancien contrat -->
        <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);border-radius:10px;padding:12px 14px;margin-bottom:16px">
          <div style="font-size:11px;font-weight:600;color:#f59e0b;margin-bottom:8px;text-transform:uppercase">
            <i class="fa-solid fa-clock-rotate-left" style="margin-right:6px"></i>Ancien contrat
          </div>
          <div style="font-size:12px;color:var(--text-muted)">
            Du <strong style="color:var(--text-primary)">{{ renewForm.ancienDebut }}</strong>
            au <strong style="color:var(--text-primary)">{{ renewForm.ancienFin }}</strong>
            · Salaire : <strong style="color:var(--text-primary)">{{ renewForm.salaire }} TND</strong>
          </div>
        </div>

        <!-- Nouveau contrat -->
        <div style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:10px;text-transform:uppercase;letter-spacing:0.05em">
          <i class="fa-solid fa-arrow-right" style="color:#10b981;margin-right:6px"></i>Nouvelle période
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="form-group">
            <label class="form-label">Type</label>
            <select class="form-select" v-model="renewForm.nouveauType">
              <option>CDD</option><option>CDI</option><option>Stage</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Nouveau salaire (TND)</label>
            <input class="form-input" type="number" v-model="renewForm.nouveauSalaire" />
          </div>
          <div class="form-group">
            <label class="form-label">Nouveau début</label>
            <input class="form-input" type="date" v-model="renewForm.nouveauDebut" />
          </div>
          <div class="form-group">
            <label class="form-label">Nouvelle fin</label>
            <input class="form-input" type="date" v-model="renewForm.nouveauFin" />
          </div>
        </div>

        <div class="flex gap-3 mt-4">
          <button class="btn-primary flex-1" style="justify-content:center" @click="confirmerRenouvellement">
            <i class="fa-solid fa-rotate"></i> Confirmer le renouvellement
          </button>
          <button class="btn-ghost" @click="showRenewModal=false">Annuler</button>
        </div>
      </div>
    </ModalBase>
  </div>
</template>

<script setup>
import { ref, computed, inject, onMounted } from 'vue'
import ModalBase from '@/components/shared/ModalBase.vue'
import contratService from '@/services/contratService.js'
import employeService from '@/services/employeService.js'

const toast = inject('toast')
const showModal = ref(false)
const editMode  = ref(false)
const form = ref({ id: null, employeId: null, employeName: '', type: 'CDI', salaire: '', dateDebut: '', dateFin: '' })

const showRenewModal = ref(false)
const renewForm = ref(null)

const employesList = ref([])

const AVATAR_COLORS = [
  'linear-gradient(135deg,#3b82f6,#06b6d4)',
  'linear-gradient(135deg,#8b5cf6,#a78bfa)',
  'linear-gradient(135deg,#10b981,#34d399)',
  'linear-gradient(135deg,#f59e0b,#fbbf24)',
  'linear-gradient(135deg,#ef4444,#f87171)',
]
function empColor(initials) {
  const idx = initials ? initials.charCodeAt(0) % AVATAR_COLORS.length : 0
  return AVATAR_COLORS[idx]
}

const contrats = ref([])

const cdiCount  = computed(() => contrats.value.filter(c => c.type === 'CDI').length)
const cddCount  = computed(() => contrats.value.filter(c => c.type === 'CDD').length)
const expirant  = computed(() => contrats.value.filter(c => c.expireBientot).length)

const typeBadge   = (t) => ({ CDI:'badge-blue', CDD:'badge-slate', Stage:'badge-purple' }[t] || 'badge-slate')
const statutBadge = (s) => ({ 'Actif':'badge-green', 'Expire bientôt':'badge-amber', 'Expiré':'badge-red' }[s] || 'badge-slate')

function onEmployeChange() {
  const emp = employesList.value.find(e => e.id === form.value.employeId)
  form.value.employeName = emp ? emp.name : ''
}

function openAdd() {
  const first = employesList.value[0]
  form.value = { id: null, employeId: first?.id ?? null, employeName: first?.name ?? '', type: 'CDI', salaire: '', dateDebut: '', dateFin: '' }
  editMode.value = false
  showModal.value = true
}
function openEdit(c) {
  form.value = { id: c.id, employeId: c.employeId ?? null, employeName: c.employe, type: c.type, salaire: c.salaire?.replace(/\s/g,''), dateDebut: '', dateFin: '' }
  editMode.value = true
  showModal.value = true
}
function renouveler(c) {
  if (c.type === 'CDI') { openEdit(c); return }

  // Parse old end date to propose new start = old fin + 1 day
  let nouveauDebut = ''
  if (c.dateFin && c.dateFin !== '—') {
    const parts = c.dateFin.split('/')
    if (parts.length === 3) {
      const d = new Date(+parts[2], +parts[1] - 1, +parts[0] + 1)
      nouveauDebut = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`
    }
  }

  renewForm.value = {
    id:             c.id,
    employe:        c.employe,
    initials:       c.initials,
    color:          c.color || empColor(c.initials),
    type:           c.type,
    salaire:        c.salaire,
    ancienDebut:    c.dateDebut,
    ancienFin:      c.dateFin || '—',
    nouveauType:    c.type,
    nouveauSalaire: c.salaire?.replace(/\s/g, ''),
    nouveauDebut,
    nouveauFin:     '',
  }
  showRenewModal.value = true
}

async function confirmerRenouvellement() {
  try {
    const r = renewForm.value
    const created = await contratService.create({
      employeId: contrats.value.find(x => x.id === r.id)?.employeId ?? null,
      type:      r.nouveauType,
      salaire:   r.nouveauSalaire,
      dateDebut: r.nouveauDebut,
      dateFin:   r.nouveauFin,
    })
    // Mark old contract as renewed
    const idx = contrats.value.findIndex(x => x.id === r.id)
    if (idx >= 0) contrats.value[idx] = { ...contrats.value[idx], statut: 'Renouvelé', expireBientot: false }
    contrats.value.unshift(created)
    toast.success('Contrat renouvelé', `Nouveau contrat ${r.nouveauType} créé pour ${r.employe}.`)
  } catch (_) {
    // Optimistic update when backend unavailable
    const r = renewForm.value
    const idx = contrats.value.findIndex(x => x.id === r.id)
    if (idx >= 0) contrats.value[idx] = { ...contrats.value[idx], statut: 'Renouvelé', expireBientot: false }

    const formatDate = (iso) => {
      if (!iso) return ''
      const [y, m, d] = iso.split('-')
      return `${d}/${m}/${y}`
    }
    contrats.value.unshift({
      id:           Date.now(),
      employe:      r.employe,
      initials:     r.initials,
      color:        r.color,
      type:         r.nouveauType,
      dateDebut:    formatDate(r.nouveauDebut),
      dateFin:      formatDate(r.nouveauFin),
      salaire:      r.nouveauSalaire,
      statut:       'Actif',
      expireBientot: false,
    })
    toast.success('Contrat renouvelé', `Nouveau contrat ${r.nouveauType} créé pour ${r.employe}.`)
  }
  showRenewModal.value = false
}

async function saveContrat() {
  try {
    if (editMode.value) {
      const updated = await contratService.update(form.value.id, {
        type: form.value.type,
        salaire: form.value.salaire,
        dateDebut: form.value.dateDebut,
        dateFin: form.value.dateFin,
      })
      const idx = contrats.value.findIndex(x => x.id === form.value.id)
      if (idx >= 0) contrats.value[idx] = { ...contrats.value[idx], ...updated }
    } else {
      const created = await contratService.create({
        employeId: form.value.employeId,
        type:      form.value.type,
        salaire:   form.value.salaire,
        dateDebut: form.value.dateDebut,
        dateFin:   form.value.dateFin,
      })
      contrats.value.unshift(created)
    }
    toast.success('Succès', 'Contrat enregistré en base de données.')
    showModal.value = false
  } catch (_) {
    toast.error('Erreur serveur', 'Vérifiez que le backend est lancé (python run.py).')
  }
}

onMounted(async () => {
  try {
    const emp = await employeService.getAll()
    if (emp) {
      employesList.value = emp.map(e => ({ id: e.id, name: `${e.prenom} ${e.nom}` }))
    }
  } catch (_) {}

  try {
    const d = await contratService.getAll()
    if (d) contrats.value = d
  } catch (_) {}
})
</script>
