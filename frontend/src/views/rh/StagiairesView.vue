<template>
  <div class="page">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1>Gestion des Stagiaires</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px">{{ stagiaires.length }} stagiaires dans l'entreprise</p>
      </div>
      <button class="btn-primary" @click="openAdd">
        <i class="fa-solid fa-plus"></i> Nouveau stagiaire
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-5">
      <div class="flex items-center gap-3" style="flex-wrap:wrap">
        <div class="search-bar" style="max-width:260px;height:36px">
          <i class="fa-solid fa-search" style="font-size:12px;color:var(--text-muted)"></i>
          <input type="text" placeholder="Rechercher..." v-model="search" />
        </div>
        <select class="form-select" style="width:auto;padding:8px 12px;font-size:13px" v-model="filterDept">
          <option value="">Tous les départements</option>
          <option v-for="d in departements" :key="d">{{ d }}</option>
        </select>
        <select class="form-select" style="width:auto;padding:8px 12px;font-size:13px" v-model="filterStatut">
          <option value="">Tous les statuts</option>
          <option>Actif</option><option>Inactif</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="table-wrapper">
        <table>
          <thead><tr>
            <th>Stagiaire</th><th>Département</th><th>Poste / Mission</th>
            <th>Date d'arrivée</th><th>Statut</th><th>Actions</th>
          </tr></thead>
          <tbody>
            <tr v-if="loading"><td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted)">
              <i class="fa-solid fa-spinner fa-spin" style="margin-right:8px"></i>Chargement...
            </td></tr>
            <tr v-else-if="filtered.length === 0"><td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted)">
              Aucun stagiaire trouvé.
            </td></tr>
            <tr v-for="s in filtered" :key="s.id">
              <td>
                <div class="flex items-center gap-3">
                  <div class="avatar" :style="{ background: s.color || empColor(s.id) }">{{ s.initials }}</div>
                  <div>
                    <div style="color:var(--text-primary);font-weight:500">{{ s.prenom }} {{ s.nom }}</div>
                    <div style="font-size:11px;color:var(--text-muted)">{{ s.email }}</div>
                  </div>
                </div>
              </td>
              <td>{{ s.departement || '—' }}</td>
              <td>{{ s.poste || '—' }}</td>
              <td>{{ s.dateEmbauche || '—' }}</td>
              <td><span class="badge" :class="statutBadge(s.statut)">
                <i class="fa-solid fa-circle" style="font-size:8px"></i> {{ s.statut || 'Actif' }}
              </span></td>
              <td>
                <div class="flex gap-2">
                  <button class="btn-edit" @click="openEdit(s)">Modifier</button>
                  <button class="btn-danger" @click="confirmDelete(s)">Supprimer</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ModalBase v-model="showDeleteModal" title="Confirmer la suppression">
      <div style="text-align:center;padding:8px 0">
        <div style="width:56px;height:56px;border-radius:50%;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
          <i class="fa-solid fa-trash" style="color:#ef4444;font-size:22px"></i>
        </div>
        <div style="font-size:15px;font-weight:600;color:var(--text-primary);margin-bottom:8px">
          Supprimer {{ toDelete?.prenom }} {{ toDelete?.nom }} ?
        </div>
        <div style="font-size:13px;color:var(--text-muted);margin-bottom:24px">
          Cette action est irréversible. Le stagiaire sera supprimé définitivement de la base de données.
        </div>
        <div class="flex gap-3" style="justify-content:center">
          <button class="btn-ghost" @click="showDeleteModal=false" style="min-width:100px">Annuler</button>
          <button class="btn-danger" @click="doDelete" :disabled="deleting" style="min-width:140px;justify-content:center">
            <i v-if="deleting" class="fa-solid fa-spinner fa-spin"></i>
            <i v-else class="fa-solid fa-trash"></i>
            {{ deleting ? 'Suppression...' : 'Oui, supprimer' }}
          </button>
        </div>
      </div>
    </ModalBase>

    <!-- Add / Edit Modal -->
    <ModalBase v-model="showModal" :title="editMode ? 'Modifier le stagiaire' : 'Ajouter un stagiaire'">
      <div class="grid grid-cols-2 gap-4">
        <div class="form-group"><label class="form-label">Prénom</label>
          <input class="form-input" v-model="form.prenom" placeholder="Prénom" /></div>
        <div class="form-group"><label class="form-label">Nom</label>
          <input class="form-input" v-model="form.nom" placeholder="Nom" /></div>
        <div class="form-group"><label class="form-label">Email</label>
          <input class="form-input" type="email" v-model="form.email" placeholder="email@entreprise.tn" /></div>
        <div class="form-group"><label class="form-label">Téléphone</label>
          <input class="form-input" v-model="form.telephone" placeholder="+216 XX XXX XXX" /></div>
        <div class="form-group"><label class="form-label">Département</label>
          <select class="form-select" v-model="form.departement">
            <option v-for="d in departements" :key="d">{{ d }}</option>
          </select></div>
        <div class="form-group"><label class="form-label">Poste / Mission</label>
          <input class="form-input" v-model="form.poste" placeholder="Ex: Stagiaire développement" /></div>
        <div class="form-group"><label class="form-label">Date d'arrivée</label>
          <input class="form-input" type="date" v-model="form.dateEmbauche" /></div>
        <div v-if="!editMode" class="form-group"><label class="form-label">Mot de passe</label>
          <input class="form-input" type="password" v-model="form.password" placeholder="Mot de passe initial" /></div>
      </div>
      <div class="flex gap-3 mt-4">
        <button class="btn-primary flex-1" style="justify-content:center" @click="saveStagiaire" :disabled="saving">
          <i class="fa-solid fa-save"></i> {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
        </button>
        <button class="btn-ghost" @click="showModal=false">Annuler</button>
      </div>
    </ModalBase>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import ModalBase from '@/components/shared/ModalBase.vue'
import stagiaireService from '@/services/stagiaireService.js'

const toast = inject('toast')
const loading = ref(false)
const saving  = ref(false)
const deleting = ref(false)
const showModal = ref(false)
const showDeleteModal = ref(false)
const toDelete = ref(null)
const editMode  = ref(false)
const search = ref(''); const filterDept = ref(''); const filterStatut = ref('')
const departements = ['Développement', 'Marketing', 'Design', 'Finance', 'RH']

const AVATAR_COLORS = [
  'linear-gradient(135deg,#3b82f6,#06b6d4)',
  'linear-gradient(135deg,#8b5cf6,#a78bfa)',
  'linear-gradient(135deg,#10b981,#34d399)',
  'linear-gradient(135deg,#f59e0b,#fbbf24)',
  'linear-gradient(135deg,#ef4444,#f87171)',
  'linear-gradient(135deg,#06b6d4,#67e8f9)',
]
function empColor(id) {
  let sum = 0
  for (const ch of String(id || '')) sum += ch.charCodeAt(0)
  return AVATAR_COLORS[sum % AVATAR_COLORS.length]
}

const defaultForm = () => ({ id: null, prenom: '', nom: '', email: '', telephone: '', departement: 'Développement', poste: '', dateEmbauche: '', password: 'password' })
const form = ref(defaultForm())

const stagiaires = ref([])

const filtered = computed(() => stagiaires.value.filter(s => {
  const q = search.value.toLowerCase()
  if (q && !`${s.prenom} ${s.nom} ${s.email}`.toLowerCase().includes(q)) return false
  if (filterDept.value   && s.departement !== filterDept.value)   return false
  if (filterStatut.value && s.statut      !== filterStatut.value) return false
  return true
}))

const statutBadge = (s) => ({ Actif:'badge-green', Inactif:'badge-red' }[s] || 'badge-slate')

function openAdd()   { form.value = defaultForm(); editMode.value = false; showModal.value = true }
function openEdit(s) { form.value = { ...s, password: '' }; editMode.value = true; showModal.value = true }
function confirmDelete(s) {
  toDelete.value = s
  showDeleteModal.value = true
}
async function doDelete() {
  if (!toDelete.value) return
  deleting.value = true
  try {
    await stagiaireService.delete(toDelete.value.id)
    stagiaires.value = stagiaires.value.filter(x => x.id !== toDelete.value.id)
    toast.success('Supprimé', `${toDelete.value.prenom} ${toDelete.value.nom} a été retiré.`)
  } catch (_) {
    toast.error('Erreur', 'Impossible de supprimer ce stagiaire.')
  }
  deleting.value = false
  showDeleteModal.value = false
  toDelete.value = null
}

async function saveStagiaire() {
  saving.value = true
  try {
    if (editMode.value) {
      await stagiaireService.update(form.value.id, form.value)
      const idx = stagiaires.value.findIndex(x => x.id === form.value.id)
      if (idx >= 0) stagiaires.value[idx] = { ...stagiaires.value[idx], ...form.value }
      toast.success('Modifié', 'Stagiaire mis à jour en base de données.')
    } else {
      const created = await stagiaireService.create(form.value)
      stagiaires.value.unshift({ ...created, color: empColor(created.id) })
      toast.success('Ajouté', `${form.value.prenom} ${form.value.nom} a été enregistré en base.`)
    }
    showModal.value = false
  } catch (_) {
    toast.error('Erreur serveur', 'Vérifiez que le backend est lancé (python run.py).')
  }
  saving.value = false
}

async function loadStagiaires() {
  loading.value = true
  try {
    const data = await stagiaireService.getAll()
    if (data) {
      stagiaires.value = data.map(s => ({ ...s, color: s.color || empColor(s.id) }))
    }
  } catch (_) {}
  loading.value = false
}

onMounted(loadStagiaires)
</script>
