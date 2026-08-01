<template>
  <div class="page">
    <div class="mb-6">
      <h1>Recrutement — Analyseur de CV</h1>
      <p style="font-size:13px;color:var(--text-muted);margin-top:4px">
        Déposez un CV, indiquez le poste visé : l'IA extrait le profil du candidat et évalue son adéquation.
      </p>
    </div>

    <!-- Formulaire d'analyse -->
    <div class="card mb-6">
      <div style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:16px">Nouvelle analyse</div>
      <div class="grid grid-cols-2 gap-4">
        <div class="form-group">
          <label class="form-label">Poste visé</label>
          <input class="form-input" v-model="form.poste" placeholder="Ex: Développeur Backend Python" />
        </div>
        <div class="form-group">
          <label class="form-label">CV du candidat (PDF ou image)</label>
          <input ref="fileInput" type="file" class="form-input" accept=".pdf,image/png,image/jpeg,image/webp" @change="onFileSelected" />
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Exigences du poste</label>
        <textarea class="form-input" rows="3" v-model="form.description"
          placeholder="Ex: 2 ans d'expérience minimum en Python, Flask ou Django, MongoDB, Docker..."></textarea>
      </div>
      <div v-if="selectedFileName" style="font-size:12px;color:var(--text-muted);margin-bottom:12px">
        <i class="fa-solid fa-paperclip"></i> {{ selectedFileName }}
      </div>
      <button class="btn-primary" @click="analyser" :disabled="analysing || !canAnalyse">
        <i v-if="analysing" class="fa-solid fa-spinner fa-spin"></i>
        <i v-else class="fa-solid fa-magnifying-glass-chart"></i>
        {{ analysing ? 'Analyse en cours (OCR + IA)...' : 'Analyser le CV' }}
      </button>
    </div>

    <!-- Résultat de la dernière analyse -->
    <div v-if="lastResult" class="card mb-6">
      <div class="flex items-center justify-between mb-4">
        <div class="section-title">Résultat de l'analyse</div>
        <span v-if="lastResult.error" class="badge badge-red">Erreur</span>
        <span v-else class="badge" :class="lastResult.adequation?.correspond ? 'badge-green' : 'badge-red'">
          {{ lastResult.adequation?.correspond ? 'Correspond au poste' : 'Ne correspond pas' }}
        </span>
      </div>

      <div v-if="lastResult.error" style="color:#ef4444;font-size:13px;padding:12px;background:rgba(239,68,68,0.08);border-radius:10px">
        <i class="fa-solid fa-triangle-exclamation" style="margin-right:6px"></i>{{ lastResult.error }}
      </div>

      <div v-else class="grid grid-cols-2 gap-5">
        <div>
          <div style="font-size:11px;color:var(--text-muted);margin-bottom:10px;text-transform:uppercase;letter-spacing:0.04em">Profil du candidat</div>
          <div style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:4px">{{ lastResult.candidat?.nom || '—' }}</div>
          <div style="font-size:12px;color:var(--text-muted);margin-bottom:2px"><i class="fa-solid fa-envelope" style="width:16px"></i> {{ lastResult.candidat?.email || '—' }}</div>
          <div style="font-size:12px;color:var(--text-muted);margin-bottom:10px"><i class="fa-solid fa-phone" style="width:16px"></i> {{ lastResult.candidat?.telephone || '—' }}</div>
          <div style="font-size:12px;color:var(--text-secondary);margin-bottom:4px"><strong>Formation :</strong> {{ lastResult.candidat?.formation || '—' }}</div>
          <div style="font-size:12px;color:var(--text-secondary);margin-bottom:10px"><strong>Expérience :</strong> {{ lastResult.candidat?.experienceAnnees ?? '—' }} an(s)</div>
          <div class="flex gap-2" style="flex-wrap:wrap">
            <span v-for="c in lastResult.candidat?.competences || []" :key="c" class="badge badge-blue">{{ c }}</span>
          </div>
        </div>
        <div>
          <div style="font-size:11px;color:var(--text-muted);margin-bottom:10px;text-transform:uppercase;letter-spacing:0.04em">Adéquation au poste</div>
          <div class="flex items-center gap-3 mb-3">
            <div class="progress-track" style="flex:1;height:10px">
              <div class="progress-fill" :style="{ width: (lastResult.adequation?.score || 0) + '%', background: scoreColor(lastResult.adequation?.score) }"></div>
            </div>
            <span style="font-size:14px;font-weight:700;color:var(--text-primary)">{{ lastResult.adequation?.score ?? 0 }}%</span>
          </div>
          <div style="font-size:13px;color:var(--text-secondary);line-height:1.6">{{ lastResult.adequation?.justification || '—' }}</div>
          <div style="font-size:12px;color:var(--text-muted);margin-top:14px">
            <i class="fa-solid fa-briefcase" style="margin-right:6px"></i>Poste : {{ lastResult.poste }}
          </div>
        </div>
      </div>
    </div>

    <!-- Historique -->
    <div class="card">
      <div class="flex items-center justify-between mb-4">
        <div class="section-title">Analyses précédentes</div>
        <span class="badge badge-slate">{{ candidatures.length }}</span>
      </div>
      <div v-if="loading" style="text-align:center;padding:24px;color:var(--text-muted)">
        <i class="fa-solid fa-spinner fa-spin"></i>
      </div>
      <div v-else-if="candidatures.length === 0" style="text-align:center;padding:24px;color:var(--text-muted);font-size:13px">
        Aucune analyse pour le moment.
      </div>
      <div class="table-wrapper" v-else>
        <table>
          <thead><tr>
            <th>Candidat</th><th>Poste</th><th>Score</th><th>Résultat</th><th>Date</th><th>Actions</th>
          </tr></thead>
          <tbody>
            <tr v-for="c in candidatures" :key="c.id">
              <td>{{ c.candidat?.nom || c.filename }}</td>
              <td>{{ c.poste }}</td>
              <td>{{ c.error ? '—' : (c.adequation?.score ?? '—') + '%' }}</td>
              <td>
                <span v-if="c.error" class="badge badge-red">Erreur</span>
                <span v-else class="badge" :class="c.adequation?.correspond ? 'badge-green' : 'badge-red'">
                  {{ c.adequation?.correspond ? 'Correspond' : 'Ne correspond pas' }}
                </span>
              </td>
              <td style="font-size:12px;color:var(--text-muted)">{{ formatDate(c.createdAt) }}</td>
              <td>
                <div class="flex gap-2">
                  <button class="btn-ghost" style="padding:6px 12px;font-size:12px" @click="lastResult = c">Voir</button>
                  <button class="btn-danger" style="padding:6px 12px;font-size:12px" @click="supprimer(c)">Supprimer</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import recrutementService from '@/services/recrutementService.js'

const toast = inject('toast')

const form = ref({ poste: '', description: '' })
const fileInput = ref(null)
const selectedFile = ref(null)
const selectedFileName = ref('')
const analysing = ref(false)
const loading = ref(false)
const lastResult = ref(null)
const candidatures = ref([])

const canAnalyse = computed(() => !!form.value.poste && !!selectedFile.value)

function onFileSelected(e) {
  const file = e.target.files[0]
  selectedFile.value = file || null
  selectedFileName.value = file?.name || ''
}

function scoreColor(score) {
  const s = score || 0
  if (s >= 70) return '#10b981'
  if (s >= 40) return '#f59e0b'
  return '#ef4444'
}

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
}

async function analyser() {
  if (!canAnalyse.value) return
  analysing.value = true
  try {
    const result = await recrutementService.analyser(selectedFile.value, form.value.poste, form.value.description)
    lastResult.value = result
    candidatures.value.unshift(result)
    if (result.error) {
      toast.error('Analyse incomplète', result.error)
    } else {
      toast.success('Analyse terminée', `${result.candidat?.nom || 'Le candidat'} a été analysé.`)
    }
    selectedFile.value = null
    selectedFileName.value = ''
    if (fileInput.value) fileInput.value.value = ''
  } catch (e) {
    toast.error('Erreur', e.userMessage || "Impossible d'analyser ce CV.")
  }
  analysing.value = false
}

async function supprimer(c) {
  if (!confirm(`Supprimer l'analyse de "${c.candidat?.nom || c.filename}" ?`)) return
  try {
    await recrutementService.delete(c.id)
    candidatures.value = candidatures.value.filter(x => x.id !== c.id)
    if (lastResult.value?.id === c.id) lastResult.value = null
    toast.success('Supprimé', 'Analyse supprimée.')
  } catch (_) {
    toast.error('Erreur', 'Impossible de supprimer cette analyse.')
  }
}

onMounted(async () => {
  loading.value = true
  try { candidatures.value = await recrutementService.getAll() } catch (_) {}
  loading.value = false
})
</script>
