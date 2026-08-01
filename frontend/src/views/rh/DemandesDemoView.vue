<template>
  <div class="page">
    <div class="mb-6">
      <h1>Demandes de démo</h1>
      <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Demandes envoyées depuis le formulaire de la page vitrine</p>
    </div>

    <div class="card">
      <div v-if="loading" style="text-align:center;padding:24px;color:var(--text-muted)">
        <i class="fa-solid fa-spinner fa-spin"></i>
      </div>
      <div v-else-if="demandes.length === 0" style="text-align:center;padding:24px;color:var(--text-muted);font-size:13px">
        Aucune demande pour le moment.
      </div>
      <div class="table-wrapper" v-else>
        <table>
          <thead><tr>
            <th>Nom</th><th>Email</th><th>Entreprise</th><th>Message</th><th>Date</th><th>Statut</th><th>Actions</th>
          </tr></thead>
          <tbody>
            <tr v-for="d in demandes" :key="d.id">
              <td style="font-weight:500">{{ d.nom }}</td>
              <td><a :href="'mailto:' + d.email">{{ d.email }}</a></td>
              <td>{{ d.entreprise || '—' }}</td>
              <td style="max-width:280px">{{ d.message || '—' }}</td>
              <td style="font-size:12px;color:var(--text-muted)">{{ formatDate(d.createdAt) }}</td>
              <td><span class="badge" :class="d.traite ? 'badge-green' : 'badge-amber'">{{ d.traite ? 'Traité' : 'Nouveau' }}</span></td>
              <td>
                <button v-if="!d.traite" class="btn-edit" style="padding:6px 12px;font-size:12px" @click="marquerTraite(d)">Marquer traité</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import contactService from '@/services/contactService.js'

const loading = ref(false)
const demandes = ref([])

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

async function marquerTraite(d) {
  try {
    const updated = await contactService.marquerTraite(d.id)
    Object.assign(d, updated)
  } catch (_) {}
}

onMounted(async () => {
  loading.value = true
  try { demandes.value = await contactService.getAll() } catch (_) {}
  loading.value = false
})
</script>
