<template>
  <div class="page">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1>Dossiers Administratifs</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Diplômes, certificats et documents officiels</p>
      </div>
      <button class="btn-primary" @click="openAdd"><i class="fa-solid fa-upload"></i> Ajouter document</button>
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-4 gap-4 mb-5">
      <div class="card-sm flex items-center gap-3">
        <div style="width:40px;height:40px;border-radius:12px;background:rgba(59,130,246,0.15);display:flex;align-items:center;justify-content:center">
          <i class="fa-solid fa-graduation-cap" style="color:#3b82f6"></i>
        </div>
        <div><div style="font-size:1.2rem;font-weight:700;color:var(--text-primary)">{{ countByType('Diplôme') }}</div><div style="font-size:11px;color:var(--text-muted)">Diplômes</div></div>
      </div>
      <div class="card-sm flex items-center gap-3">
        <div style="width:40px;height:40px;border-radius:12px;background:rgba(16,185,129,0.15);display:flex;align-items:center;justify-content:center">
          <i class="fa-solid fa-certificate" style="color:#10b981"></i>
        </div>
        <div><div style="font-size:1.2rem;font-weight:700;color:var(--text-primary)">{{ countByType('Certificat') }}</div><div style="font-size:11px;color:var(--text-muted)">Certificats</div></div>
      </div>
      <div class="card-sm flex items-center gap-3">
        <div style="width:40px;height:40px;border-radius:12px;background:rgba(245,158,11,0.15);display:flex;align-items:center;justify-content:center">
          <i class="fa-solid fa-file-alt" style="color:#f59e0b"></i>
        </div>
        <div><div style="font-size:1.2rem;font-weight:700;color:var(--text-primary)">{{ countByType('Attestation') }}</div><div style="font-size:11px;color:var(--text-muted)">Attestations</div></div>
      </div>
      <div class="card-sm flex items-center gap-3">
        <div style="width:40px;height:40px;border-radius:12px;background:rgba(139,92,246,0.15);display:flex;align-items:center;justify-content:center">
          <i class="fa-solid fa-hourglass-half" style="color:#a78bfa"></i>
        </div>
        <div><div style="font-size:1.2rem;font-weight:700;color:var(--text-primary)">{{ enAttente }}</div><div style="font-size:11px;color:var(--text-muted)">En attente</div></div>
      </div>
    </div>

    <div class="card">
      <div class="table-wrapper">
        <table>
          <thead><tr>
            <th>Document</th><th>Type</th><th>Employé</th><th>Date d'ajout</th><th>Statut</th><th>Actions</th>
          </tr></thead>
          <tbody>
            <tr v-if="loading"><td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted)">
              <i class="fa-solid fa-spinner fa-spin" style="margin-right:8px"></i>Chargement...
            </td></tr>
            <tr v-else-if="dossiers.length === 0"><td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted)">
              Aucun document.
            </td></tr>
            <tr v-for="d in dossiers" :key="d.id">
              <td><div class="flex items-center gap-2">
                <i :class="['fa-solid', fileIcon(d.fichier)]" :style="{ color: fileColor(d.fichier) }"></i>
                {{ d.titre }}
              </div></td>
              <td><span class="badge" :class="typeBadge(d.type)">{{ d.type }}</span></td>
              <td>{{ d.employe || '—' }}</td>
              <td>{{ d.dateAjout }}</td>
              <td><span class="badge" :class="statutBadge(d.statut)">{{ d.statut }}</span></td>
              <td>
                <div class="flex gap-2" v-if="d.statut === 'En attente'">
                  <button class="btn-edit" style="padding:5px 10px;font-size:11px" @click="valider(d)">Valider</button>
                  <button class="btn-danger" style="padding:5px 10px;font-size:11px" @click="refuser(d)">Refuser</button>
                </div>
                <div class="flex gap-2" v-else>
                  <button class="btn-edit" @click="telecharger(d)">Télécharger</button>
                  <button class="btn-danger" @click="supprimer(d)">Supprimer</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add Modal -->
    <ModalBase v-model="showModal" title="Ajouter un document">
      <div class="form-group"><label class="form-label">Employé</label>
        <select class="form-select" v-model="form.employeId">
          <option v-if="employesList.length === 0" :value="null" disabled>Aucun employé disponible</option>
          <option v-for="e in employesList" :key="e.id" :value="e.id">{{ e.prenom }} {{ e.nom }}</option>
        </select></div>
      <div class="form-group"><label class="form-label">Type de document</label>
        <select class="form-select" v-model="form.type">
          <option>Diplôme</option><option>Certificat</option><option>Attestation</option><option>Autre</option>
        </select></div>
      <div class="form-group"><label class="form-label">Titre du document</label>
        <input class="form-input" v-model="form.titre" placeholder="ex: Licence Informatique" /></div>
      <div class="form-group"><label class="form-label">Fichier</label>
        <div class="upload-zone" @click="triggerUpload" @dragover.prevent @drop.prevent="handleDrop">
          <i class="fa-solid fa-cloud-arrow-up" style="font-size:28px;color:var(--text-muted)"></i>
          <div style="font-size:13px;color:var(--text-muted);margin-top:8px">Cliquer pour uploader ou glisser-déposer</div>
          <div style="font-size:11px;color:var(--text-muted);margin-top:4px">PDF, PNG, JPG jusqu'à 10MB</div>
          <div v-if="file" style="margin-top:8px;font-size:12px;color:var(--brand-primary)"><i class="fa-solid fa-check"></i> {{ file.name }}</div>
          <input ref="fileInput" type="file" style="display:none" accept=".pdf,.png,.jpg,.jpeg" @change="onFile" />
        </div>
      </div>
      <div class="flex gap-3 mt-4">
        <button class="btn-primary flex-1" style="justify-content:center" @click="saveDoc" :disabled="saving">
          <i v-if="saving" class="fa-solid fa-spinner fa-spin"></i>
          <i v-else class="fa-solid fa-upload"></i>
          {{ saving ? 'Envoi...' : 'Ajouter' }}
        </button>
        <button class="btn-ghost" @click="showModal=false">Annuler</button>
      </div>
    </ModalBase>
  </div>
</template>

<script setup>
import { ref, computed, inject, onMounted } from 'vue'
import ModalBase from '@/components/shared/ModalBase.vue'
import dossierService from '@/services/dossierService.js'
import employeService from '@/services/employeService.js'

const toast = inject('toast')
const loading = ref(false)
const saving = ref(false)
const showModal = ref(false)
const fileInput = ref(null)
const file = ref(null)
const form = ref({ employeId: null, type: 'Diplôme', titre: '' })
const employesList = ref([])
const dossiers = ref([])

const enAttente = computed(() => dossiers.value.filter(d => d.statut === 'En attente').length)
const countByType = (t) => dossiers.value.filter(d => d.type === t).length
const typeBadge   = (t) => ({ 'Diplôme':'badge-blue','Certificat':'badge-green','Attestation':'badge-amber','Autre':'badge-slate' }[t] || 'badge-slate')
const statutBadge = (s) => ({ 'Validé':'badge-green','En attente':'badge-amber','Refusé':'badge-red' }[s] || 'badge-slate')
const fileIcon    = (f) => f === 'pdf' ? 'fa-file-pdf'  : 'fa-file-image'
const fileColor   = (f) => f === 'pdf' ? '#ef4444'      : '#3b82f6'

function openAdd() { form.value = { employeId: employesList.value[0]?.id || null, type:'Diplôme', titre:'' }; file.value = null; showModal.value = true }
function triggerUpload() { fileInput.value.click() }
function onFile(e) { file.value = e.target.files[0] || null }
function handleDrop(e) { file.value = e.dataTransfer.files[0] || null }

async function saveDoc() {
  if (!form.value.employeId) { toast.error('Erreur', 'Sélectionnez un employé.'); return }
  if (!form.value.titre.trim()) { toast.error('Erreur', 'Le titre est obligatoire.'); return }

  saving.value = true
  try {
    const fd = new FormData()
    fd.append('employeId', form.value.employeId)
    fd.append('type', form.value.type)
    fd.append('titre', form.value.titre)
    if (file.value) fd.append('fichier', file.value)
    const created = await dossierService.create(fd)
    dossiers.value.unshift(created)
    toast.success('Document ajouté', 'Le document a été soumis pour validation.')
    showModal.value = false
  } catch (_) {
    toast.error('Erreur serveur', "Impossible d'ajouter le document. Vérifiez que le backend est lancé.")
  }
  saving.value = false
}

async function valider(d) {
  try {
    const updated = await dossierService.valider(d.id)
    Object.assign(d, updated)
    toast.success('Validé', `${d.titre} a été validé.`)
  } catch (_) {
    toast.error('Erreur serveur', 'Impossible de valider ce document.')
  }
}
async function refuser(d) {
  try {
    const updated = await dossierService.refuser(d.id)
    Object.assign(d, updated)
    toast.info('Refusé', `${d.titre} a été refusé.`)
  } catch (_) {
    toast.error('Erreur serveur', 'Impossible de refuser ce document.')
  }
}

function telecharger(d) {
  if (!d.fichierPath) {
    toast.error('Indisponible', "Aucun fichier n'a été joint à ce document.")
    return
  }
  window.open(`http://localhost:8000/${d.fichierPath}`, '_blank')
}

async function supprimer(d) {
  if (!confirm(`Supprimer "${d.titre}" ?`)) return
  try {
    await dossierService.delete(d.id)
    dossiers.value = dossiers.value.filter(x => x.id !== d.id)
    toast.success('Supprimé', 'Le document a été supprimé.')
  } catch (_) {
    toast.error('Erreur serveur', 'Impossible de supprimer ce document.')
  }
}

onMounted(async () => {
  loading.value = true
  try { const emp = await employeService.getAll(); if (emp) employesList.value = emp } catch (_) {}
  try { const data = await dossierService.getAll(); if (data) dossiers.value = data } catch (_) {}
  loading.value = false
})
</script>

<style scoped>
.upload-zone {
  border:2px dashed var(--border); border-radius:10px;
  padding:24px; text-align:center; cursor:pointer;
  transition:border-color 0.2s;
}
.upload-zone:hover { border-color:var(--brand-primary); }
</style>
