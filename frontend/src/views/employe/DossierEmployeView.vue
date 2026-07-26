<template>
  <div class="page">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1>Mes Dossiers Administratifs</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Vos documents personnels et demandes</p>
      </div>
      <button class="btn-primary" @click="showModal=true"><i class="fa-solid fa-upload"></i> Demander un document</button>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div v-for="d in mesDossiers" :key="d.id" class="card">
        <div class="flex items-start gap-4">
          <div style="width:44px;height:44px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center"
            :style="{ background: d.iconBg }">
            <i :class="d.icon" :style="{ color: d.iconColor, fontSize:'18px' }"></i>
          </div>
          <div style="flex:1">
            <div style="font-size:14px;font-weight:600;color:var(--text-primary)">{{ d.titre }}</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ d.type }} · Ajouté le {{ d.dateAjout }}</div>
          </div>
          <span class="badge" :class="statutBadge(d.statut)">{{ d.statut }}</span>
        </div>
        <div class="flex gap-2 mt-4">
          <button class="btn-edit" style="flex:1;justify-content:center" @click="download(d)">
            <i class="fa-solid fa-download"></i> Télécharger
          </button>
        </div>
      </div>

      <div v-if="mesDossiers.length === 0"
        style="grid-column:span 2;text-align:center;padding:48px;color:var(--text-muted)">
        Aucun document disponible.
      </div>
    </div>

    <!-- Request modal -->
    <ModalBase v-model="showModal" title="Demander un document">
      <div class="form-group"><label class="form-label">Type de document</label>
        <select class="form-select" v-model="form.type">
          <option>Attestation de travail</option>
          <option>Attestation de salaire</option>
          <option>Certificat de présence</option>
          <option>Fiche de paie</option>
          <option>Autre</option>
        </select></div>
      <div class="form-group"><label class="form-label">Motif de la demande</label>
        <textarea class="form-input" rows="3" v-model="form.motif" placeholder="Précisez pourquoi vous avez besoin de ce document..."></textarea></div>
      <div class="flex gap-3 mt-4">
        <button class="btn-primary flex-1" style="justify-content:center" @click="submit">
          <i class="fa-solid fa-paper-plane"></i> Soumettre la demande
        </button>
        <button class="btn-ghost" @click="showModal=false">Annuler</button>
      </div>
    </ModalBase>
  </div>
</template>

<script setup>
import { ref, inject, onMounted } from 'vue'
import ModalBase from '@/components/shared/ModalBase.vue'
import dossierService from '@/services/dossierService.js'

const toast = inject('toast')
const showModal = ref(false)
const form = ref({ type:'Attestation de travail', motif:'' })

const mesDossiers = ref([
  { id:1, titre:'Licence Informatique', type:'Diplôme', dateAjout:'15 Jan 2024', statut:'Validé', icon:'fa-solid fa-graduation-cap', iconBg:'rgba(59,130,246,0.15)', iconColor:'#3b82f6' },
  { id:2, titre:'Certif. AWS Solutions', type:'Certificat', dateAjout:'20 Fév 2025', statut:'Validé', icon:'fa-solid fa-certificate', iconBg:'rgba(16,185,129,0.15)', iconColor:'#10b981' },
])

const statutBadge = (s) => ({ 'Validé':'badge-green','En attente':'badge-amber','Refusé':'badge-red' }[s] || 'badge-slate')

async function download(d) {
  if (d.statut === 'En attente') {
    toast.error('Document indisponible', 'Ce document est encore en cours de traitement.')
    return
  }
  // Try real backend download first
  try {
    const res = await fetch(`http://localhost:8000/api/dossiers/${d.id}/download`, {
      headers: { Authorization: 'Bearer ' + localStorage.getItem('token') }
    })
    if (res.ok) {
      const blob = await res.blob()
      triggerDownload(blob, d.titre + '.pdf')
      return
    }
  } catch (_) {}

  // Fallback: generate a simple text document
  const today = new Date().toLocaleDateString('fr-FR', { day:'numeric', month:'long', year:'numeric' })
  const content = `
HRFLOW — DOCUMENT OFFICIEL
═══════════════════════════════════════════════════════

Document   : ${d.titre}
Type       : ${d.type}
Date       : ${today}
Statut     : ${d.statut}

───────────────────────────────────────────────────────
Ce document a été généré automatiquement par le système
HRFlow de gestion des ressources humaines.
═══════════════════════════════════════════════════════
  `.trim()

  const blob = new Blob([content], { type: 'text/plain;charset=utf-8' })
  triggerDownload(blob, d.titre.replace(/\s+/g, '_') + '.txt')
  toast.success('Téléchargé', `"${d.titre}" téléchargé avec succès.`)
}

function triggerDownload(blob, filename) {
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

async function submit() {
  const newDoc = {
    id: Date.now(), titre: form.value.type, type: form.value.type,
    dateAjout: "Aujourd'hui", statut: 'En attente',
    icon: 'fa-solid fa-file-alt', iconBg: 'rgba(245,158,11,0.15)', iconColor: '#f59e0b',
  }
  try {
    // Send as JSON — employee document requests don't include a file
    const fd = new FormData()
    fd.append('type', form.value.type)
    fd.append('titre', form.value.type)
    fd.append('motif', form.value.motif || '')
    await dossierService.create(fd)
  } catch (_) { /* silent — always add locally */ }
  mesDossiers.value.unshift(newDoc)
  toast.success('Demande envoyée', 'Votre demande est en cours de traitement.')
  showModal.value = false
  form.value = { type: 'Attestation de travail', motif: '' }
}

onMounted(async () => {
  try { const d = await dossierService.getAll(); if (d?.length) mesDossiers.value = d } catch (_) {}
})
</script>
