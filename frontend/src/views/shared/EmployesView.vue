<template>
  <div class="page">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1>Gestion des Employés</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px">{{ employes.length }} employés actifs dans l'entreprise</p>
      </div>
      <button class="btn-primary" @click="openAdd">
        <i class="fa-solid fa-plus"></i> Nouvel employé
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
          <option>Actif</option><option>En congé</option><option>Inactif</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="table-wrapper">
        <table>
          <thead><tr>
            <th>Employé</th><th>Département</th><th>Poste</th>
            <th>Date d'embauche</th><th>Statut</th><th>Actions</th>
          </tr></thead>
          <tbody>
            <tr v-if="loading"><td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted)">
              <i class="fa-solid fa-spinner fa-spin" style="margin-right:8px"></i>Chargement...
            </td></tr>
            <tr v-else-if="filtered.length === 0"><td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted)">
              Aucun employé trouvé.
            </td></tr>
            <tr v-for="emp in filtered" :key="emp.id">
              <td>
                <div class="flex items-center gap-3">
                  <div class="avatar" :style="{ background: emp.color || empColor(emp.id) }">{{ emp.initials }}</div>
                  <div>
                    <div style="color:var(--text-primary);font-weight:500">{{ emp.prenom }} {{ emp.nom }}</div>
                    <div style="font-size:11px;color:var(--text-muted)">{{ emp.email }}</div>
                  </div>
                </div>
              </td>
              <td>{{ emp.departement || '—' }}</td>
              <td>{{ emp.poste || '—' }}</td>
              <td>{{ emp.dateEmbauche || '—' }}</td>
              <td><span class="badge" :class="statutBadge(emp.statut)">
                <i class="fa-solid fa-circle" style="font-size:8px"></i> {{ emp.statut || 'Actif' }}
              </span></td>
              <td>
                <div class="flex gap-2">
                  <button class="btn-edit" @click="openEdit(emp)">Modifier</button>
                  <button class="btn-danger" @click="confirmDelete(emp)">Supprimer</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <div class="flex items-center justify-between mt-4 pt-4" style="border-top:1px solid var(--border)">
        <span style="font-size:12px;color:var(--text-muted)">Affichage 1–{{ filtered.length }} sur {{ employes.length }}</span>
        <div class="flex gap-2">
          <button class="btn-ghost" style="padding:6px 12px;font-size:12px"><i class="fa-solid fa-chevron-left"></i></button>
          <button class="btn-primary" style="padding:6px 12px;font-size:12px">1</button>
          <button class="btn-ghost" style="padding:6px 12px;font-size:12px"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ModalBase v-model="showDeleteModal" title="Confirmer la suppression">
      <div style="text-align:center;padding:8px 0">
        <div style="width:56px;height:56px;border-radius:50%;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
          <i class="fa-solid fa-trash" style="color:#ef4444;font-size:22px"></i>
        </div>
        <div style="font-size:15px;font-weight:600;color:var(--text-primary);margin-bottom:8px">
          Supprimer {{ empToDelete?.prenom }} {{ empToDelete?.nom }} ?
        </div>
        <div style="font-size:13px;color:var(--text-muted);margin-bottom:24px">
          Cette action est irréversible. L'employé sera supprimé définitivement de la base de données.
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
    <ModalBase v-model="showModal" :title="editMode ? 'Modifier l\'employé' : 'Ajouter un employé'">
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
        <div class="form-group"><label class="form-label">Poste</label>
          <input class="form-input" v-model="form.poste" placeholder="Titre du poste" /></div>
        <div class="form-group"><label class="form-label">Date d'embauche</label>
          <input class="form-input" type="date" v-model="form.dateEmbauche" /></div>
        <div v-if="!editMode" class="form-group"><label class="form-label">Mot de passe</label>
          <input class="form-input" type="password" v-model="form.password" placeholder="Mot de passe initial" /></div>
      </div>
      <div class="flex gap-3 mt-4">
        <button class="btn-primary flex-1" style="justify-content:center" @click="saveEmploye" :disabled="saving">
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
import employeService from '@/services/employeService.js'

const toast = inject('toast')
const loading = ref(false)
const saving  = ref(false)
const deleting = ref(false)
const showModal = ref(false)
const showDeleteModal = ref(false)
const empToDelete = ref(null)
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
  return AVATAR_COLORS[(id || 0) % AVATAR_COLORS.length]
}

const defaultForm = () => ({ id: null, prenom: '', nom: '', email: '', telephone: '', departement: 'Développement', poste: '', dateEmbauche: '', password: 'password' })
const form = ref(defaultForm())

const employes = ref([
  {id:1,prenom:'Karima', nom:'Belhadj',initials:'KB',color:'linear-gradient(135deg,#3b82f6,#06b6d4)',email:'k.belhadj@hrflow.tn',departement:'Développement',poste:'Développeuse Frontend',dateEmbauche:'Jan 2024',statut:'Actif'},
  {id:2,prenom:'Yassine',nom:'Aouat',  initials:'YA',color:'linear-gradient(135deg,#8b5cf6,#a78bfa)',email:'y.aouat@hrflow.tn',  departement:'Marketing',    poste:'Chef de projet digital',dateEmbauche:'Mar 2023',statut:'En congé'},
  {id:3,prenom:'Sana',   nom:'Mrad',   initials:'SM',color:'linear-gradient(135deg,#10b981,#34d399)',email:'s.mrad@hrflow.tn',   departement:'Design',       poste:'UI/UX Designer',       dateEmbauche:'Jun 2024',statut:'Actif'},
  {id:4,prenom:'Mehdi',  nom:'Khelifi',initials:'MK',color:'linear-gradient(135deg,#f59e0b,#fbbf24)',email:'m.khelifi@hrflow.tn',departement:'Finance',      poste:'Comptable Senior',     dateEmbauche:'Sep 2021',statut:'Actif'},
  {id:5,prenom:'Lina',   nom:'Bouzid', initials:'LB',color:'linear-gradient(135deg,#ef4444,#f87171)',email:'l.bouzid@hrflow.tn', departement:'Développement',poste:'Développeuse Backend', dateEmbauche:'Fév 2025',statut:'Actif'},
  {id:6,prenom:'Omar',   nom:'Trabelsi',initials:'OT',color:'linear-gradient(135deg,#06b6d4,#67e8f9)',email:'o.trabelsi@hrflow.tn',departement:'RH',         poste:'Responsable RH',       dateEmbauche:'Mai 2020',statut:'Actif'},
])

const filtered = computed(() => employes.value.filter(e => {
  const q = search.value.toLowerCase()
  if (q && !`${e.prenom} ${e.nom} ${e.email}`.toLowerCase().includes(q)) return false
  if (filterDept.value   && e.departement !== filterDept.value)   return false
  if (filterStatut.value && e.statut      !== filterStatut.value) return false
  return true
}))

const statutBadge = (s) => ({ Actif:'badge-green', 'En congé':'badge-amber', Inactif:'badge-red' }[s] || 'badge-slate')

function openAdd()   { form.value = defaultForm(); editMode.value = false; showModal.value = true }
function openEdit(e) { form.value = { ...e, password: '' }; editMode.value = true; showModal.value = true }
function confirmDelete(e) {
  empToDelete.value = e
  showDeleteModal.value = true
}
async function doDelete() {
  if (!empToDelete.value) return
  deleting.value = true
  try {
    await employeService.delete(empToDelete.value.id)
    employes.value = employes.value.filter(x => x.id !== empToDelete.value.id)
    toast.success('Supprimé', `${empToDelete.value.prenom} ${empToDelete.value.nom} a été retiré.`)
  } catch (_) {
    toast.error('Erreur', 'Impossible de supprimer cet employé.')
  }
  deleting.value = false
  showDeleteModal.value = false
  empToDelete.value = null
}

async function saveEmploye() {
  saving.value = true
  try {
    if (editMode.value) {
      await employeService.update(form.value.id, form.value)
      const idx = employes.value.findIndex(x => x.id === form.value.id)
      if (idx >= 0) employes.value[idx] = { ...employes.value[idx], ...form.value }
      toast.success('Modifié', 'Employé mis à jour en base de données.')
    } else {
      const initials = `${form.value.prenom[0] || ''}${form.value.nom[0] || ''}`.toUpperCase()
      const created = await employeService.create(form.value)
      employes.value.unshift({ ...created, color: empColor(created.id) })
      toast.success('Ajouté', `${form.value.prenom} ${form.value.nom} a été enregistré en base.`)
    }
    showModal.value = false
  } catch (_) {
    toast.error('Erreur serveur', 'Vérifiez que le backend est lancé (php -S localhost:8000 -t public).')
  }
  saving.value = false
}

async function loadEmployes() {
  loading.value = true
  try {
    const data = await employeService.getAll()
    if (data && data.length > 0) {
      employes.value = data.map(e => ({ ...e, color: e.color || empColor(e.id) }))
    }
  } catch (_) {}
  loading.value = false
}

onMounted(loadEmployes)
</script>
