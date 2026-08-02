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
      <div v-if="loading" style="grid-column:span 2;text-align:center;padding:48px;color:var(--text-muted)">
        <i class="fa-solid fa-spinner fa-spin"></i>
      </div>
      <div v-for="d in mesDossiers" :key="d.id" class="card" v-else>
        <div class="flex items-start gap-4">
          <div style="width:44px;height:44px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center"
            :style="{ background: iconBg(d.type) }">
            <i :class="iconFor(d.type)" :style="{ color: iconColor(d.type), fontSize:'18px' }"></i>
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

      <div v-if="!loading && mesDossiers.length === 0"
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
        <button class="btn-primary flex-1" style="justify-content:center" @click="submit" :disabled="submitting">
          <i v-if="submitting" class="fa-solid fa-spinner fa-spin"></i>
          <i v-else class="fa-solid fa-paper-plane"></i>
          {{ submitting ? 'Envoi...' : 'Soumettre la demande' }}
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
import { useAuthStore } from '@/stores/auth.js'
import { BACKEND_ORIGIN } from '@/utils/env.js'

const auth = useAuthStore()
const toast = inject('toast')
const loading = ref(false)
const submitting = ref(false)
const showModal = ref(false)
const form = ref({ type:'Attestation de travail', motif:'' })

const mesDossiers = ref([])

const ICONS = {
  'Diplôme':      { icon: 'fa-solid fa-graduation-cap', bg: 'rgba(59,130,246,0.15)',  color: '#3b82f6' },
  'Certificat':   { icon: 'fa-solid fa-certificate',    bg: 'rgba(16,185,129,0.15)',  color: '#10b981' },
  'Attestation':  { icon: 'fa-solid fa-file-alt',       bg: 'rgba(245,158,11,0.15)',  color: '#f59e0b' },
}
const DEFAULT_ICON = { icon: 'fa-solid fa-file', bg: 'rgba(100,116,139,0.15)', color: '#64748b' }
const iconFor   = (t) => (ICONS[t] || DEFAULT_ICON).icon
const iconBg    = (t) => (ICONS[t] || DEFAULT_ICON).bg
const iconColor = (t) => (ICONS[t] || DEFAULT_ICON).color

const statutBadge = (s) => ({ 'Validé':'badge-green','En attente':'badge-amber','Refusé':'badge-red' }[s] || 'badge-slate')

function download(d) {
  if (d.statut === 'En attente') {
    toast.error('Document indisponible', 'Ce document est encore en cours de traitement.')
    return
  }
  if (!d.fichierPath) {
    toast.error('Indisponible', "Aucun fichier n'a été joint à ce document.")
    return
  }
  window.open(`${BACKEND_ORIGIN}/${d.fichierPath}`, '_blank')
}

async function submit() {
  submitting.value = true
  try {
    const fd = new FormData()
    fd.append('type', form.value.type)
    fd.append('titre', form.value.type)
    fd.append('motif', form.value.motif || '')
    const created = await dossierService.create(fd)
    mesDossiers.value.unshift(created)
    toast.success('Demande envoyée', 'Votre demande est en cours de traitement.')
    showModal.value = false
    form.value = { type: 'Attestation de travail', motif: '' }
  } catch (_) {
    toast.error('Erreur serveur', "Impossible d'envoyer la demande.")
  }
  submitting.value = false
}

onMounted(async () => {
  loading.value = true
  try {
    const d = await dossierService.getAll({ employeId: auth.user?.id })
    if (d) mesDossiers.value = d
  } catch (_) {}
  loading.value = false
})
</script>
