<template>
  <div class="page">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1>Évaluations des Employés</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Suivi des performances et compétences</p>
      </div>
      <button class="btn-primary" @click="openAdd"><i class="fa-solid fa-plus"></i> Nouvelle évaluation</button>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div v-for="ev in evaluations" :key="ev.id" class="card">
        <div class="flex items-center gap-4 mb-4">
          <div class="avatar-lg" :style="{ background: ev.color || empColor(ev.initials) }">{{ ev.initials }}</div>
          <div style="flex:1">
            <div style="font-weight:600;color:var(--text-primary)">{{ ev.nom }}</div>
            <div style="font-size:11px;color:var(--text-muted)">{{ ev.poste }} · {{ ev.periode }}</div>
          </div>
          <div style="text-align:center">
            <div style="font-size:1.5rem;font-weight:700" :style="{ color: ev.scoreColor || scoreColor(ev.noteGlobale) }">{{ ev.noteGlobale }}</div>
            <div style="font-size:11px;color:var(--text-muted)">/5.0</div>
          </div>
        </div>
        <div class="space-y-2">
          <div v-for="crit in (ev.criteres || buildCriteres(ev))" :key="crit.label">
            <div class="flex justify-between" style="font-size:12px;margin-bottom:3px">
              <span style="color:var(--text-muted)">{{ crit.label }}</span>
              <span style="font-weight:600;color:var(--text-primary)">{{ crit.score }}</span>
            </div>
            <div class="progress-track">
              <div class="progress-fill" :style="{ width: (crit.score/5*100)+'%', background: crit.color }"></div>
            </div>
          </div>
        </div>
        <div class="flex gap-2 mt-4">
          <button class="btn-edit" style="flex:1;justify-content:center" @click="openEdit(ev)">Modifier</button>
          <button class="btn-ghost" style="flex:1;justify-content:center" @click="download(ev)">Télécharger</button>
        </div>
      </div>

      <!-- Add card -->
      <div class="card flex items-center justify-center"
        style="border-style:dashed;min-height:220px;cursor:pointer" @click="openAdd">
        <div style="text-align:center">
          <div style="width:48px;height:48px;border-radius:50%;border:2px dashed rgba(59,130,246,0.3);background:rgba(59,130,246,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
            <i class="fa-solid fa-plus" style="color:#3b82f6"></i>
          </div>
          <div style="font-size:13px;color:var(--text-muted)">Ajouter une évaluation</div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <ModalBase v-model="showModal" :title="editMode ? 'Modifier l\'évaluation' : 'Nouvelle évaluation'">
      <div class="form-group"><label class="form-label">Employé</label>
        <select class="form-select" v-model="form.employeId" :disabled="editMode" @change="onEmployeChange">
          <option v-for="e in employesList" :key="e.id" :value="e.id">{{ e.name }}</option>
        </select></div>
      <div class="form-group"><label class="form-label">Période</label>
        <input class="form-input" v-model="form.periode" placeholder="ex: Trimestre 1 2025" /></div>
      <div class="space-y-2">
        <div class="form-group"><label class="form-label">Compétences techniques (0-5)</label>
          <input class="form-input" type="number" min="0" max="5" step="0.1" v-model.number="form.competences" /></div>
        <div class="form-group"><label class="form-label">Travail en équipe (0-5)</label>
          <input class="form-input" type="number" min="0" max="5" step="0.1" v-model.number="form.teamwork" /></div>
        <div class="form-group"><label class="form-label">Initiative (0-5)</label>
          <input class="form-input" type="number" min="0" max="5" step="0.1" v-model.number="form.initiative" /></div>
      </div>
      <div class="form-group mt-2"><label class="form-label">Commentaire</label>
        <textarea class="form-input" rows="2" v-model="form.commentaire" placeholder="Observations..."></textarea></div>
      <div class="flex gap-3 mt-4">
        <button class="btn-primary flex-1" style="justify-content:center" @click="saveEval">
          <i class="fa-solid fa-save"></i> Enregistrer
        </button>
        <button class="btn-ghost" @click="showModal=false">Annuler</button>
      </div>
    </ModalBase>
  </div>
</template>

<script setup>
import { ref, inject, onMounted } from 'vue'
import ModalBase from '@/components/shared/ModalBase.vue'
import evaluationService from '@/services/evaluationService.js'
import employeService from '@/services/employeService.js'

const toast = inject('toast')
const showModal = ref(false)
const editMode  = ref(false)
const form = ref({ id: null, employeId: null, employeName: '', periode: '', competences: 4, teamwork: 4, initiative: 4, commentaire: '' })

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
function scoreColor(n) {
  if (n >= 4.5) return '#10b981'
  if (n >= 3.5) return '#3b82f6'
  if (n >= 2.5) return '#f59e0b'
  return '#ef4444'
}

const CRIT_COLORS = [
  'linear-gradient(90deg,#3b82f6,#06b6d4)',
  'linear-gradient(90deg,#10b981,#34d399)',
  'linear-gradient(90deg,#8b5cf6,#a78bfa)',
  'linear-gradient(90deg,#f59e0b,#fbbf24)',
]
function buildCriteres(ev) {
  return [
    { label: 'Compétences techniques', score: ev.competences ?? 0, color: CRIT_COLORS[0] },
    { label: 'Travail en équipe',      score: ev.teamwork    ?? 0, color: CRIT_COLORS[1] },
    { label: 'Initiative',             score: ev.initiative  ?? 0, color: CRIT_COLORS[2] },
  ]
}

const evaluations = ref([])

function onEmployeChange() {
  const emp = employesList.value.find(e => e.id === form.value.employeId)
  form.value.employeName = emp ? emp.name : ''
}

function openAdd() {
  const first = employesList.value[0]
  form.value = { id: null, employeId: first?.id ?? null, employeName: first?.name ?? '', periode: '', competences: 4, teamwork: 4, initiative: 4, commentaire: '' }
  editMode.value = false
  showModal.value = true
}
function openEdit(ev) {
  form.value = {
    id: ev.id, employeId: ev.employeId ?? null, employeName: ev.nom,
    periode: ev.periode, competences: ev.competences ?? 0,
    teamwork: ev.teamwork ?? 0, initiative: ev.initiative ?? 0,
    commentaire: ev.commentaire ?? '',
  }
  editMode.value = true
  showModal.value = true
}
function download(ev) { toast.info('Export', 'Rapport PDF en préparation...') }

async function saveEval() {
  const note = +((form.value.competences + form.value.teamwork + form.value.initiative) / 3).toFixed(1)
  const payload = {
    employeId:   form.value.employeId,
    periode:     form.value.periode,
    competences: form.value.competences,
    teamwork:    form.value.teamwork,
    initiative:  form.value.initiative,
    commentaire: form.value.commentaire,
  }
  try {
    if (editMode.value) {
      const updated = await evaluationService.update(form.value.id, payload)
      const idx = evaluations.value.findIndex(x => x.id === form.value.id)
      if (idx >= 0) evaluations.value[idx] = { ...evaluations.value[idx], ...updated, noteGlobale: note }
    } else {
      const created = await evaluationService.create(payload)
      const emp = employesList.value.find(e => e.id === form.value.employeId)
      evaluations.value.unshift({
        ...created,
        nom: emp?.name ?? form.value.employeName,
        initials: (emp?.name ?? '??').split(' ').map(w => w[0]||'').join('').toUpperCase().slice(0,2),
        criteres: buildCriteres({ competences: form.value.competences, teamwork: form.value.teamwork, initiative: form.value.initiative }),
      })
    }
    toast.success('Évaluation enregistrée', 'Les notes ont été sauvegardées.')
  } catch (_) {
    if (!editMode.value) {
      const emp = employesList.value.find(e => e.id === form.value.employeId)
      const name = emp?.name ?? form.value.employeName
      const initials = name.split(' ').map(w => w[0]||'').join('').toUpperCase().slice(0,2)
      evaluations.value.unshift({
        id: Date.now(), nom: name, initials,
        periode: form.value.periode, noteGlobale: note,
        competences: form.value.competences, teamwork: form.value.teamwork, initiative: form.value.initiative,
        criteres: buildCriteres(form.value),
      })
    }
    toast.success('Évaluation enregistrée', 'Sauvegardée localement.')
  }
  showModal.value = false
}

onMounted(async () => {
  try {
    const emp = await employeService.getAll()
    if (emp) {
      employesList.value = emp.map(e => ({ id: e.id, name: `${e.prenom} ${e.nom}` }))
    }
  } catch (_) {}

  try {
    const d = await evaluationService.getAll()
    if (d) {
      evaluations.value = d.map(ev => ({
        ...ev,
        nom: ev.employe ?? '',
        criteres: buildCriteres(ev),
      }))
    }
  } catch (_) {}
})
</script>
