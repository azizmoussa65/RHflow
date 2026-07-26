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
        <div><div style="font-size:1.2rem;font-weight:700;color:var(--text-primary)">18</div><div style="font-size:11px;color:var(--text-muted)">Diplômes</div></div>
      </div>
      <div class="card-sm flex items-center gap-3">
        <div style="width:40px;height:40px;border-radius:12px;background:rgba(16,185,129,0.15);display:flex;align-items:center;justify-content:center">
          <i class="fa-solid fa-certificate" style="color:#10b981"></i>
        </div>
        <div><div style="font-size:1.2rem;font-weight:700;color:var(--text-primary)">32</div><div style="font-size:11px;color:var(--text-muted)">Certificats</div></div>
      </div>
      <div class="card-sm flex items-center gap-3">
        <div style="width:40px;height:40px;border-radius:12px;background:rgba(245,158,11,0.15);display:flex;align-items:center;justify-content:center">
          <i class="fa-solid fa-file-alt" style="color:#f59e0b"></i>
        </div>
        <div><div style="font-size:1.2rem;font-weight:700;color:var(--text-primary)">11</div><div style="font-size:11px;color:var(--text-muted)">Attestations</div></div>
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
            <tr v-for="d in dossiers" :key="d.id">
              <td><div class="flex items-center gap-2">
                <i :class="['fa-solid', fileIcon(d.fichier)]" :style="{ color: fileColor(d.fichier) }"></i>
                {{ d.titre }}
              </div></td>
              <td><span class="badge" :class="typeBadge(d.type)">{{ d.type }}</span></td>
              <td>{{ d.employe }}</td>
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
        <select class="form-select" v-model="form.employe">
          <option v-for="e in employesList" :key="e">{{ e }}</option>
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
        <button class="btn-primary flex-1" style="justify-content:center" @click="saveDoc">
          <i class="fa-solid fa-upload"></i> Ajouter
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

const toast = inject('toast')
const showModal = ref(false)
const fileInput = ref(null)
const file = ref(null)
const form = ref({ employe:'', type:'Diplôme', titre:'' })
const employesList = ['Karima Belhadj','Yassine Aouat','Sana Mrad','Mehdi Khelifi','Lina Bouzid','Omar Trabelsi']

const dossiers = ref([
  {id:1,titre:'Licence Informatique',    type:'Diplôme',     employe:'Karima Belhadj',fichier:'pdf',dateAjout:'15 Jan 2024',statut:'Validé'},
  {id:2,titre:'Attestation travail 2024',type:'Attestation',  employe:'Yassine Aouat', fichier:'pdf',dateAjout:'3 Mar 2025', statut:'En attente'},
  {id:3,titre:'Certif. AWS Solutions',   type:'Certificat',   employe:'Lina Bouzid',   fichier:'img',dateAjout:'20 Fév 2025',statut:'Validé'},
  {id:4,titre:'Master Finance',          type:'Diplôme',     employe:'Mehdi Khelifi',  fichier:'pdf',dateAjout:'10 Sep 2021',statut:'Validé'},
])

const enAttente = computed(() => dossiers.value.filter(d => d.statut === 'En attente').length)
const typeBadge   = (t) => ({ 'Diplôme':'badge-blue','Certificat':'badge-green','Attestation':'badge-amber','Autre':'badge-slate' }[t] || 'badge-slate')
const statutBadge = (s) => ({ 'Validé':'badge-green','En attente':'badge-amber','Refusé':'badge-red' }[s] || 'badge-slate')
const fileIcon    = (f) => f === 'pdf' ? 'fa-file-pdf'  : 'fa-file-image'
const fileColor   = (f) => f === 'pdf' ? '#ef4444'      : '#3b82f6'

function openAdd() { form.value = { employe: employesList[0], type:'Diplôme', titre:'' }; file.value = null; showModal.value = true }
function triggerUpload() { fileInput.value.click() }
function onFile(e) { file.value = e.target.files[0] || null }
function handleDrop(e) { file.value = e.dataTransfer.files[0] || null }

async function saveDoc() {
  try {
    const fd = new FormData()
    fd.append('employe', form.value.employe)
    fd.append('type', form.value.type)
    fd.append('titre', form.value.titre)
    if (file.value) fd.append('fichier', file.value)
    await dossierService.create(fd)
    dossiers.value.unshift({ ...form.value, id:Date.now(), fichier:'pdf', dateAjout:'Aujourd\'hui', statut:'En attente' })
    toast.success('Document ajouté','Le document a été soumis pour validation.')
  } catch (_) {
    dossiers.value.unshift({ ...form.value, id:Date.now(), fichier:'pdf', dateAjout:'Aujourd\'hui', statut:'En attente' })
    toast.success('Document ajouté','Sauvegardé localement.')
  }
  showModal.value = false
}

async function valider(d) {
  try { await dossierService.valider(d.id) } catch (_) {}
  d.statut = 'Validé'; toast.success('Validé', `${d.titre} a été validé.`)
}
async function refuser(d) {
  try { await dossierService.refuser(d.id) } catch (_) {}
  d.statut = 'Refusé'; toast.info('Refusé', `${d.titre} a été refusé.`)
}
async function telecharger(d) {
  if (d.statut === 'En attente') {
    toast.error('Indisponible', 'Ce document est encore en cours de traitement.')
    return
  }
  // Try real backend download
  try {
    const res = await fetch(`http://localhost:8000/api/dossiers/${d.id}/download`, {
      headers: { Authorization: 'Bearer ' + localStorage.getItem('hrflow_token') }
    })
    if (res.ok) {
      const blob = await res.blob()
      triggerDownload(blob, d.titre + '.pdf')
      return
    }
  } catch (_) {}

  // Fallback: generate text document
  const today = new Date().toLocaleDateString('fr-FR', { day:'numeric', month:'long', year:'numeric' })
  const content = `HRFLOW — DOCUMENT OFFICIEL\n${'═'.repeat(50)}\n\nDocument   : ${d.titre}\nType       : ${d.type}\nEmployé    : ${d.employe || ''}\nDate       : ${today}\nStatut     : ${d.statut}\n\n${'═'.repeat(50)}\nCe document a été généré par HRFlow.`
  const blob = new Blob([content], { type: 'text/plain;charset=utf-8' })
  triggerDownload(blob, d.titre.replace(/\s+/g, '_') + '.txt')
  toast.success('Téléchargé', `"${d.titre}" téléchargé avec succès.`)
}

function triggerDownload(blob, filename) {
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url; a.download = filename
  document.body.appendChild(a); a.click()
  document.body.removeChild(a); URL.revokeObjectURL(url)
}
function supprimer(d) {
  if (confirm(`Supprimer "${d.titre}" ?`)) {
    dossiers.value = dossiers.value.filter(x => x.id !== d.id)
    toast.success('Supprimé', 'Le document a été supprimé.')
  }
}

onMounted(async () => {
  try { const data = await dossierService.getAll(); if (data?.length) dossiers.value = data } catch (_) {}
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
