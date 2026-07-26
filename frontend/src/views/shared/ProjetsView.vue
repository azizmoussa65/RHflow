<template>
  <div class="page">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1>Gestion des Projets</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Suivi de l'avancement des équipes</p>
      </div>
      <div class="flex gap-3">
        <button :class="vue === 'liste' ? 'btn-primary' : 'btn-ghost'" @click="vue='liste'"><i class="fa-solid fa-table-list"></i> Liste</button>
        <button :class="vue === 'kanban' ? 'btn-primary' : 'btn-ghost'" @click="vue='kanban'"><i class="fa-solid fa-columns"></i> Colonnes</button>
        <button class="btn-primary" v-if="auth.isManager" @click="openAdd"><i class="fa-solid fa-plus"></i> Nouveau projet</button>
      </div>
    </div>

    <!-- Vue Liste (tableau) -->
    <div v-if="vue === 'liste'" class="card mb-6">
      <div class="table-wrapper">
        <table>
          <thead><tr>
            <th>Projet</th><th>Catégorie</th><th>Statut</th><th>Avancement</th><th>Deadline</th><th>Membres</th><th>Actions</th>
          </tr></thead>
          <tbody>
            <tr v-for="p in projets" :key="p.id">
              <td>
                <div style="display:flex;align-items:center;gap:10px">
                  <div style="width:10px;height:10px;border-radius:50%;flex-shrink:0" :style="{ background: p.couleur }"></div>
                  <div>
                    <div style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ p.nom }}</div>
                    <div style="font-size:11px;color:var(--text-muted)">{{ p.description }}</div>
                  </div>
                </div>
              </td>
              <td style="font-size:12px;color:var(--text-muted)">{{ p.categorie }}</td>
              <td><span class="badge" :class="statutBadge(p.statut)">{{ p.statut }}</span></td>
              <td style="min-width:140px">
                <div class="flex items-center gap-2">
                  <div class="progress-track" style="flex:1">
                    <div class="progress-fill" :style="{ width: p.avancement+'%', background: p.gradient || p.couleur }"></div>
                  </div>
                  <span style="font-size:12px;font-weight:600;min-width:32px" :style="{ color: p.couleur }">{{ p.avancement }}%</span>
                </div>
              </td>
              <td style="font-size:12px;color:var(--text-muted)"><i class="fa-solid fa-calendar-alt" style="margin-right:4px"></i>{{ p.deadline }}</td>
              <td>
                <div style="display:flex;margin-left:-4px">
                  <div v-for="m in p.membres" :key="m.initials" class="avatar"
                    :style="{ background: m.color, width:'26px', height:'26px', fontSize:'10px', border:'2px solid var(--bg-card)', marginLeft:'-4px' }">
                    {{ m.initials }}
                  </div>
                </div>
              </td>
              <td>
                <div class="flex gap-2">
                  <button class="btn-ghost" style="padding:6px 12px;font-size:12px" @click="openTaches(p)"><i class="fa-solid fa-list-check"></i> Tâches</button>
                  <button v-if="auth.isManager" class="btn-edit" style="justify-content:center" @click="openEdit(p)"><i class="fa-solid fa-pen"></i></button>
                  <button v-if="auth.isManager" class="btn-danger" style="justify-content:center" @click="supprimer(p)"><i class="fa-solid fa-trash"></i></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Project cards grid -->
    <div v-if="vue === 'cards'" class="grid grid-cols-3 gap-4 mb-6">
      <div v-for="p in projets" :key="p.id" class="card" :style="{ borderLeft: '3px solid ' + p.couleur }">
        <div class="flex items-start justify-between mb-3">
          <div>
            <div style="font-size:14px;font-weight:700;color:var(--text-primary)">{{ p.nom }}</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ p.categorie }}</div>
          </div>
          <span class="badge" :class="statutBadge(p.statut)">{{ p.statut }}</span>
        </div>
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px">{{ p.description }}</div>
        <div class="mb-3">
          <div class="flex justify-between" style="font-size:12px;margin-bottom:4px">
            <span style="color:var(--text-muted)">Avancement</span>
            <span style="font-weight:600" :style="{ color: p.couleur }">{{ p.avancement }}%</span>
          </div>
          <div class="progress-track">
            <div class="progress-fill" :style="{ width: p.avancement+'%', background: p.gradient }"></div>
          </div>
        </div>
        <div class="flex items-center justify-between">
          <div style="display:flex;margin-left:-4px">
            <div v-for="m in p.membres" :key="m" class="avatar"
              :style="{ background:m.color, width:'26px', height:'26px', fontSize:'10px', border:'2px solid var(--bg-card)', marginLeft:'-4px' }">
              {{ m.initials }}
            </div>
          </div>
          <div style="font-size:11px;color:var(--text-muted)">
            <i class="fa-solid fa-calendar-alt" style="margin-right:4px"></i>{{ p.deadline }}
          </div>
        </div>
        <div class="flex gap-2 mt-3">
          <button class="btn-ghost" style="flex:1;justify-content:center" @click="openTaches(p)">
            <i class="fa-solid fa-list-check"></i> Tâches
          </button>
          <button v-if="auth.isManager" class="btn-edit" style="justify-content:center" @click="openEdit(p)"><i class="fa-solid fa-pen"></i></button>
          <button v-if="auth.isManager" class="btn-danger" style="justify-content:center" @click="supprimer(p)"><i class="fa-solid fa-trash"></i></button>
        </div>
      </div>

      <!-- Add card (manager only) -->
      <div v-if="auth.isManager" class="card flex items-center justify-center"
        style="border-style:dashed;min-height:180px;cursor:pointer" @click="openAdd">
        <div class="text-center">
          <div style="width:48px;height:48px;border-radius:50%;border:2px dashed rgba(59,130,246,0.3);background:rgba(59,130,246,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
            <i class="fa-solid fa-plus" style="color:#3b82f6"></i>
          </div>
          <div style="font-size:13px;color:var(--text-muted)">Ajouter un projet</div>
        </div>
      </div>
    </div>

    <!-- Kanban view (ERP v3.0) -->
    <div class="card" v-if="vue === 'kanban'">
      <div style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:16px">Vue par colonnes — ERP v3.0</div>
      <div class="flex gap-4 overflow-x-auto" style="padding-bottom:8px">
        <div v-for="col in kanban" :key="col.title" class="kanban-col">
          <div class="flex items-center justify-between mb-2">
            <span style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em">{{ col.title }}</span>
            <span class="badge" :class="col.badgeClass">{{ col.cards.length }}</span>
          </div>
          <div v-for="card in col.cards" :key="card.title" class="kanban-card">
            <div style="font-size:13px;font-weight:500;color:var(--text-primary);margin-bottom:4px">{{ card.title }}</div>
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:8px">{{ card.sub }}</div>
            <span v-if="card.badge" class="badge" :class="card.badgeClass">{{ card.badge }}</span>
            <div v-if="card.progress" class="progress-track" style="margin-top:8px">
              <div class="progress-fill" :style="{ width: card.progress+'%', background:'#3b82f6' }"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tâches Modal -->
    <ModalBase v-model="showTachesModal" :title="'Tâches — ' + (projetActif?.nom || '')">
      <div class="flex items-center justify-between mb-4">
        <span style="font-size:13px;color:var(--text-muted)">{{ taches.length }} tâche(s)</span>
        <button class="btn-primary" @click="openAddTache"><i class="fa-solid fa-plus"></i> Nouvelle tâche</button>
      </div>

      <!-- Liste des tâches -->
      <div v-if="taches.length === 0" style="text-align:center;padding:32px;color:var(--text-muted);font-size:13px">
        Aucune tâche pour ce projet.
      </div>
      <div v-for="t in taches" :key="t.id" class="card mb-2" style="padding:12px 16px">
        <div class="flex items-center gap-3">
          <div style="flex:1">
            <div style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ t.titre }}</div>
            <div v-if="t.description" style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ t.description }}</div>
            <div class="flex gap-2 mt-2">
              <span class="badge" :class="statutTacheBadge(t.statut)">{{ t.statut }}</span>
              <span class="badge" :class="prioriteBadge(t.priorite)">{{ t.priorite }}</span>
              <span v-if="t.assigneA" style="font-size:11px;color:var(--text-muted);display:flex;align-items:center;gap:4px">
                <div class="avatar" :style="{width:'18px',height:'18px',fontSize:'8px',background:'linear-gradient(135deg,#3b82f6,#06b6d4)'}">{{ t.assigneA.initials }}</div>
                {{ t.assigneA.nom }}
              </span>
            </div>
          </div>
          <div class="flex gap-2">
            <button class="btn-edit" @click="openEditTache(t)"><i class="fa-solid fa-pen"></i></button>
            <button class="btn-danger" @click="supprimerTache(t)"><i class="fa-solid fa-trash"></i></button>
          </div>
        </div>
      </div>

      <!-- Formulaire ajouter/modifier tâche -->
      <div v-if="showFormTache" style="border-top:1px solid var(--border);margin-top:16px;padding-top:16px">
        <div style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:12px">
          {{ editTacheMode ? 'Modifier la tâche' : 'Nouvelle tâche' }}
        </div>
        <div class="form-group"><label class="form-label">Titre</label>
          <input class="form-input" v-model="formTache.titre" placeholder="Titre de la tâche" /></div>
        <div class="form-group"><label class="form-label">Description</label>
          <input class="form-input" v-model="formTache.description" placeholder="Description (optionnel)" /></div>
        <div class="grid grid-cols-2 gap-4">
          <div class="form-group"><label class="form-label">Statut</label>
            <select class="form-select" v-model="formTache.statut">
              <option>À faire</option><option>En cours</option><option>Terminé</option>
            </select></div>
          <div class="form-group"><label class="form-label">Priorité</label>
            <select class="form-select" v-model="formTache.priorite">
              <option>Basse</option><option>Normale</option><option>Haute</option>
            </select></div>
        </div>
        <div class="form-group"><label class="form-label">Assigner à</label>
          <select class="form-select" v-model="formTache.assigneAId">
            <option :value="null">— Non assigné —</option>
            <option v-for="e in employesList" :key="e.id" :value="e.id">{{ e.name }} ({{ e.poste }})</option>
          </select></div>
        <div class="flex gap-3 mt-3">
          <button class="btn-primary flex-1" style="justify-content:center" @click="saveTache">
            <i class="fa-solid fa-save"></i> {{ editTacheMode ? 'Modifier' : 'Ajouter' }}
          </button>
          <button class="btn-ghost" @click="showFormTache=false">Annuler</button>
        </div>
      </div>
    </ModalBase>

    <!-- Add/Edit modal -->
    <ModalBase v-model="showModal" :title="editMode ? 'Modifier le projet' : 'Nouveau projet'">
      <div class="form-group"><label class="form-label">Nom du projet</label>
        <input class="form-input" v-model="form.nom" placeholder="Nom du projet" /></div>
      <div class="form-group"><label class="form-label">Catégorie</label>
        <input class="form-input" v-model="form.categorie" placeholder="ex: Développement" /></div>
      <div class="form-group"><label class="form-label">Description</label>
        <textarea class="form-input" rows="3" v-model="form.description" placeholder="Description..."></textarea></div>
      <div class="grid grid-cols-2 gap-4">
        <div class="form-group"><label class="form-label">Statut</label>
          <select class="form-select" v-model="form.statut">
            <option>En cours</option><option>En pause</option><option>Terminé</option>
          </select></div>
        <div class="form-group"><label class="form-label">Avancement (%)</label>
          <input class="form-input" type="number" min="0" max="100" v-model.number="form.avancement" /></div>
        <div class="form-group"><label class="form-label">Deadline</label>
          <input class="form-input" type="date" v-model="form.deadline" /></div>
      </div>
      <div class="flex gap-3 mt-4">
        <button class="btn-primary flex-1" style="justify-content:center" @click="saveProjet">
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
import { useAuthStore } from '@/stores/auth.js'
import projetService from '@/services/projetService.js'
import tacheService from '@/services/tacheService.js'
import employeService from '@/services/employeService.js'

const auth  = useAuthStore()
const toast = inject('toast')
const vue   = ref('liste')
const showModal = ref(false)
const editMode  = ref(false)
const form = ref({ nom:'', categorie:'', description:'', statut:'En cours', avancement:0, deadline:'' })

// Tâches
const showTachesModal = ref(false)
const showFormTache   = ref(false)
const editTacheMode   = ref(false)
const projetActif     = ref(null)
const taches          = ref([])
const employesList    = ref([])
const formTache = ref({ id: null, titre: '', description: '', statut: 'À faire', priorite: 'Normale', assigneAId: null })

const statutTacheBadge = (s) => ({ 'À faire':'badge-slate', 'En cours':'badge-blue', 'Terminé':'badge-green' }[s] || 'badge-slate')
const prioriteBadge    = (p) => ({ 'Basse':'badge-slate', 'Normale':'badge-amber', 'Haute':'badge-red' }[p] || 'badge-slate')

async function openTaches(p) {
  projetActif.value   = p
  showTachesModal.value = true
  showFormTache.value   = false
  taches.value = []
  try { taches.value = await tacheService.getAll(p.id) } catch (_) {}
  try {
    const emp = await employeService.getAll()
    employesList.value = emp.map(e => ({ id: e.id, name: `${e.prenom} ${e.nom}`, poste: e.poste || '' }))
  } catch (_) {}
}
function openAddTache() {
  formTache.value = { id: null, titre: '', description: '', statut: 'À faire', priorite: 'Normale', assigneAId: null }
  editTacheMode.value = false
  showFormTache.value = true
}
function openEditTache(t) {
  formTache.value = { id: t.id, titre: t.titre, description: t.description || '', statut: t.statut, priorite: t.priorite, assigneAId: t.assigneA?.id || null }
  editTacheMode.value = true
  showFormTache.value = true
}
async function saveTache() {
  if (!formTache.value.titre.trim()) { toast.error('Erreur', 'Le titre est obligatoire.'); return }
  try {
    if (editTacheMode.value) {
      const updated = await tacheService.update(projetActif.value.id, formTache.value.id, formTache.value)
      const idx = taches.value.findIndex(x => x.id === formTache.value.id)
      if (idx >= 0) taches.value[idx] = updated
      toast.success('Modifié', 'Tâche mise à jour.')
    } else {
      const created = await tacheService.create(projetActif.value.id, formTache.value)
      taches.value.push(created)
      toast.success('Ajouté', 'Tâche créée et assignée.')
    }
    showFormTache.value = false
  } catch (_) {
    toast.error('Erreur serveur', 'Vérifiez que le backend est lancé.')
  }
}
async function supprimerTache(t) {
  if (!confirm(`Supprimer "${t.titre}" ?`)) return
  try {
    await tacheService.delete(projetActif.value.id, t.id)
    taches.value = taches.value.filter(x => x.id !== t.id)
    toast.success('Supprimé', 'Tâche supprimée.')
  } catch (_) {
    toast.error('Erreur serveur', 'Impossible de supprimer.')
  }
}

const projets = ref([
  {id:1,nom:'ERP v3.0',couleur:'#3b82f6',gradient:'linear-gradient(90deg,#3b82f6,#06b6d4)',categorie:'Développement',statut:'En cours',description:'Refonte complète du système ERP interne.',avancement:68,deadline:'30 Avr',membres:[{initials:'KB',color:'linear-gradient(135deg,#3b82f6,#06b6d4)'},{initials:'LB',color:'linear-gradient(135deg,#ef4444,#f87171)'},{initials:'YA',color:'linear-gradient(135deg,#8b5cf6,#a78bfa)'}]},
  {id:2,nom:'App Mobile RH',couleur:'#f59e0b',gradient:'linear-gradient(90deg,#f59e0b,#fbbf24)',categorie:'Mobile / Design',statut:'En pause',description:'Application mobile pour la gestion des congés.',avancement:34,deadline:'15 Jun',membres:[{initials:'SM',color:'linear-gradient(135deg,#10b981,#34d399)'},{initials:'YA',color:'linear-gradient(135deg,#8b5cf6,#a78bfa)'}]},
  {id:3,nom:'Dashboard Analytics',couleur:'#10b981',gradient:'linear-gradient(90deg,#10b981,#34d399)',categorie:'Data / Dev',statut:'Terminé',description:'Tableau de bord pour le suivi des KPIs RH.',avancement:100,deadline:'Livré',membres:[{initials:'KB',color:'linear-gradient(135deg,#3b82f6,#06b6d4)'},{initials:'MK',color:'linear-gradient(135deg,#f59e0b,#fbbf24)'}]},
])

const kanban = [
  {title:'À faire',    badgeClass:'badge-slate', cards:[{title:'Design API Gateway',sub:'Architecture backend',badge:'Haute priorité',badgeClass:'badge-red'},{title:'Tests unitaires module RH',sub:'QA / Tests'},{title:'Documentation technique',sub:'Rédaction'}]},
  {title:'En cours',   badgeClass:'badge-blue',  cards:[{title:'Développement UI Employés',sub:'Frontend · Karima B.',progress:70},{title:'Intégration SMTP email',sub:'Backend · Lina B.',progress:45}]},
  {title:'En révision',badgeClass:'badge-amber', cards:[{title:'Module authentification JWT',sub:'Security · Review',badge:'En revue',badgeClass:'badge-amber'}]},
  {title:'Terminé',    badgeClass:'badge-green',  cards:[{title:'Setup base de données',sub:'PostgreSQL + Redis'},{title:'Maquettes Figma',sub:'Design · Sana M.'}]},
]

const statutBadge = (s) => ({ 'En cours':'badge-blue','En pause':'badge-amber','Terminé':'badge-green' }[s] || 'badge-slate')

function openAdd()   { form.value = { nom:'', categorie:'', description:'', statut:'En cours', avancement:0, deadline:'' }; editMode.value=false; showModal.value=true }
function openEdit(p) { form.value = { ...p }; editMode.value=true; showModal.value=true }
async function supprimer(p) {
  if (confirm(`Supprimer "${p.nom}" ?`)) {
    try {
      await projetService.delete(p.id)
      projets.value = projets.value.filter(x => x.id !== p.id)
      toast.success('Supprimé', `${p.nom} a été supprimé de la base.`)
    } catch (_) {
      toast.error('Erreur serveur', 'Vérifiez que le backend est lancé.')
    }
  }
}
async function saveProjet() {
  try {
    if (editMode.value) {
      await projetService.update(form.value.id, form.value)
      const idx = projets.value.findIndex(x => x.id === form.value.id)
      if (idx >= 0) Object.assign(projets.value[idx], form.value)
    } else {
      const created = await projetService.create(form.value)
      projets.value.unshift({ ...created, couleur: created.couleur || '#3b82f6', gradient:'linear-gradient(90deg,#3b82f6,#06b6d4)', membres:[] })
    }
    toast.success('Succès', 'Projet enregistré en base de données.')
    showModal.value = false
  } catch (_) {
    toast.error('Erreur serveur', 'Vérifiez que le backend est lancé (php -S localhost:8000 -t public).')
  }
}

onMounted(async () => {
  try { const d = await projetService.getAll(); if (d?.length) projets.value = d } catch (_) {}
})
</script>
