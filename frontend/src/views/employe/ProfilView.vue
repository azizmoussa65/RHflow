<template>
  <div class="page">
    <div class="mb-6">
      <h1>Mon Profil</h1>
      <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Gérez vos informations personnelles</p>
    </div>
    <div class="grid grid-cols-3 gap-5">
      <!-- Avatar card -->
      <div class="card text-center">
        <div class="avatar-lg mx-auto mb-3"
          style="width:72px;height:72px;border-radius:18px;background:linear-gradient(135deg,#3b82f6,#06b6d4);font-size:22px">
          {{ auth.userInitials }}
        </div>
        <div style="font-weight:700;color:var(--text-primary);margin-bottom:4px">{{ auth.userFullName }}</div>
        <div style="font-size:13px;color:var(--text-muted);margin-bottom:12px">{{ roleLabel }}</div>
        <span class="badge badge-blue">{{ auth.role }}</span>
        <div style="border-top:1px solid var(--border);margin-top:16px;padding-top:16px">
          <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px">Membre depuis</div>
          <div style="font-size:13px;font-weight:500;color:var(--text-primary)">{{ auth.user?.dateEmbauche || 'Janvier 2022' }}</div>
        </div>
        <button class="btn-ghost w-full mt-4" style="justify-content:center">
          <i class="fa-solid fa-camera"></i> Changer photo
        </button>
      </div>

      <!-- Info form -->
      <div class="card col-span-2">
        <div style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:20px">Informations personnelles</div>
        <div class="grid grid-cols-2 gap-4">
          <div class="form-group"><label class="form-label">Prénom</label>
            <input class="form-input" v-model="form.prenom" /></div>
          <div class="form-group"><label class="form-label">Nom</label>
            <input class="form-input" v-model="form.nom" /></div>
          <div class="form-group"><label class="form-label">Email</label>
            <input class="form-input" type="email" v-model="form.email" /></div>
          <div class="form-group"><label class="form-label">Téléphone</label>
            <input class="form-input" v-model="form.telephone" /></div>
          <div class="form-group"><label class="form-label">Département</label>
            <input class="form-input" v-model="form.departement" readonly style="opacity:0.6;cursor:not-allowed" /></div>
          <div class="form-group"><label class="form-label">Date d'embauche</label>
            <input class="form-input" v-model="form.dateEmbauche" readonly style="opacity:0.6;cursor:not-allowed" /></div>
        </div>

        <!-- Password change section -->
        <div style="border-top:1px solid var(--border);margin-top:20px;padding-top:20px">
          <div style="font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:12px">Changer le mot de passe</div>
          <div class="grid grid-cols-2 gap-4">
            <div class="form-group"><label class="form-label">Nouveau mot de passe</label>
              <input class="form-input" type="password" v-model="form.password" placeholder="Laisser vide pour ne pas changer" /></div>
            <div class="form-group"><label class="form-label">Confirmer</label>
              <input class="form-input" type="password" v-model="form.passwordConfirm" placeholder="••••••••" /></div>
          </div>
          <div v-if="pwdError" style="font-size:12px;color:#ef4444;margin-bottom:8px">{{ pwdError }}</div>
        </div>

        <div class="flex gap-3 mt-2">
          <button class="btn-primary" @click="save" :disabled="saving">
            <i v-if="saving" class="fa-solid fa-spinner fa-spin"></i>
            <i v-else class="fa-solid fa-save"></i>
            {{ saving ? 'Sauvegarde...' : 'Sauvegarder' }}
          </button>
          <button class="btn-ghost" @click="reset">Annuler</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, inject } from 'vue'
import { useAuthStore } from '@/stores/auth.js'
import api from '@/services/api.js'

const auth  = useAuthStore()
const toast = inject('toast')
const saving = ref(false)
const pwdError = ref('')

const roleLabel = computed(() => ({ MANAGER: 'Manager Général', RH: 'Responsable RH', EMPLOYE: 'Employé' }[auth.role] || ''))

const initial = () => ({
  prenom:       auth.user?.prenom      || '',
  nom:          auth.user?.nom         || '',
  email:        auth.user?.email       || '',
  telephone:    auth.user?.telephone   || '',
  departement:  auth.user?.departement || '',
  dateEmbauche: auth.user?.dateEmbauche || '',
  password:        '',
  passwordConfirm: '',
})
const form = ref(initial())

function reset() {
  pwdError.value = ''
  form.value = initial()
}

async function save() {
  pwdError.value = ''

  // Validate password if changing
  if (form.value.password) {
    if (form.value.password.length < 6) {
      pwdError.value = 'Le mot de passe doit contenir au moins 6 caractères.'
      return
    }
    if (form.value.password !== form.value.passwordConfirm) {
      pwdError.value = 'Les mots de passe ne correspondent pas.'
      return
    }
  }

  saving.value = true
  try {
    const payload = {
      prenom:    form.value.prenom,
      nom:       form.value.nom,
      email:     form.value.email,
      telephone: form.value.telephone,
    }
    if (form.value.password) payload.password = form.value.password

    const response = await api.patch('/auth/profile', payload)
    const updatedUser = response.data

    auth.user = { ...auth.user, ...updatedUser }
    localStorage.setItem('hrflow_user', JSON.stringify(auth.user))

    form.value.password        = ''
    form.value.passwordConfirm = ''
    toast.success('Profil mis à jour', 'Vos informations ont été sauvegardées en base.')
  } catch (_) {
    toast.error('Erreur serveur', 'Impossible de sauvegarder. Vérifiez que le backend est lancé.')
  }
  saving.value = false
}
</script>

<style scoped>
.mx-auto { margin-left:auto; margin-right:auto; display:flex; align-items:center; justify-content:center; }
.w-full { width:100%; }
</style>
