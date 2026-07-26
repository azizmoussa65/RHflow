<template>
  <div class="page">
    <div class="mb-6">
      <h1>Paramètres</h1>
      <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Configuration du système</p>
    </div>
    <div class="grid grid-cols-2 gap-5">
      <!-- Notifications -->
      <div class="card">
        <div style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:16px">
          <i class="fa-solid fa-bell mr-2" style="color:#3b82f6"></i>Notifications
        </div>
        <div class="space-y-4">
          <div v-for="n in notifs" :key="n.id" class="flex items-center justify-between">
            <div>
              <div style="font-size:13px;color:var(--text-primary)">{{ n.label }}</div>
              <div style="font-size:11px;color:var(--text-muted)">{{ n.desc }}</div>
            </div>
            <div class="toggle" :class="{ on: n.active }" @click="n.active = !n.active">
              <div class="toggle-thumb"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sécurité -->
      <div class="card">
        <div style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:16px">
          <i class="fa-solid fa-shield-halved mr-2" style="color:#10b981"></i>Sécurité
        </div>
        <div class="space-y-3">
          <div class="form-group"><label class="form-label">Mot de passe actuel</label>
            <input class="form-input" type="password" placeholder="••••••••" v-model="pwd.current" /></div>
          <div class="form-group"><label class="form-label">Nouveau mot de passe</label>
            <input class="form-input" type="password" placeholder="••••••••" v-model="pwd.new" /></div>
          <div class="form-group"><label class="form-label">Confirmer</label>
            <input class="form-input" type="password" placeholder="••••••••" v-model="pwd.confirm" /></div>
          <div v-if="pwdError" style="font-size:12px;color:#ef4444;margin-bottom:8px">{{ pwdError }}</div>
          <button class="btn-primary" @click="changerMdp"><i class="fa-solid fa-lock"></i> Mettre à jour</button>
        </div>
      </div>

      <!-- Apparence -->
      <div class="card">
        <div style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:16px">
          <i class="fa-solid fa-palette mr-2" style="color:#8b5cf6"></i>Apparence
        </div>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <div style="font-size:13px;color:var(--text-primary)">Mode sombre</div>
              <div style="font-size:11px;color:var(--text-muted)">Interface en mode nuit</div>
            </div>
            <div class="toggle" :class="{ on: theme.isDark }" @click="theme.toggleTheme">
              <div class="toggle-thumb"></div>
            </div>
          </div>
          <div>
            <div style="font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.04em">Couleur d'accent</div>
            <div class="flex gap-2">
              <div v-for="c in accentColors" :key="c.value" class="color-dot"
                :style="{ background: c.bg }" :class="{ active: accent === c.value }"
                @click="accent = c.value" :title="c.name"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Locale -->
      <div class="card">
        <div style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:16px">
          <i class="fa-solid fa-globe mr-2" style="color:#06b6d4"></i>Langue & Région
        </div>
        <div class="form-group"><label class="form-label">Langue</label>
          <select class="form-select"><option>Français</option><option>English</option><option>العربية</option></select></div>
        <div class="form-group"><label class="form-label">Fuseau horaire</label>
          <select class="form-select"><option>Africa/Tunis (UTC+1)</option><option>Europe/Paris (UTC+1)</option></select></div>
        <div class="form-group"><label class="form-label">Format de date</label>
          <select class="form-select"><option>DD/MM/YYYY</option><option>MM/DD/YYYY</option><option>YYYY-MM-DD</option></select></div>
        <button class="btn-primary" @click="saveLocale"><i class="fa-solid fa-save"></i> Sauvegarder</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, inject } from 'vue'
import { useThemeStore } from '@/stores/theme.js'

const theme = useThemeStore()
const toast = inject('toast')

const notifs = ref([
  { id:1, label:'Nouvelles demandes de congé',  desc:'Recevoir une alerte email', active:true },
  { id:2, label:'Contrats expirant bientôt',    desc:'Alerte 30 jours avant',     active:true },
  { id:3, label:'Mises à jour système',         desc:'Notifications in-app',      active:false },
  { id:4, label:'Nouvelles évaluations',        desc:'Notification par email',     active:true },
])

const pwd = ref({ current:'', new:'', confirm:'' })
const pwdError = ref('')
const accent = ref('blue')
const accentColors = [
  { value:'blue',   name:'Bleu',    bg:'#3b82f6' },
  { value:'cyan',   name:'Cyan',    bg:'#06b6d4' },
  { value:'purple', name:'Violet',  bg:'#8b5cf6' },
  { value:'green',  name:'Vert',    bg:'#10b981' },
  { value:'amber',  name:'Ambre',   bg:'#f59e0b' },
]

function changerMdp() {
  pwdError.value = ''
  if (!pwd.value.current) { pwdError.value = 'Le mot de passe actuel est requis.'; return }
  if (pwd.value.new.length < 6) { pwdError.value = 'Le nouveau mot de passe doit contenir au moins 6 caractères.'; return }
  if (pwd.value.new !== pwd.value.confirm) { pwdError.value = 'Les mots de passe ne correspondent pas.'; return }
  toast.success('Mot de passe mis à jour', 'Votre mot de passe a été changé.')
  pwd.value = { current:'', new:'', confirm:'' }
}

function saveLocale() { toast.success('Paramètres sauvegardés','Vos préférences ont été mises à jour.') }
</script>

<style scoped>
.toggle {
  position:relative; width:44px; height:24px;
  border-radius:99px; transition:background 0.2s; cursor:pointer;
  background:var(--border);
}
.toggle.on { background:#3b82f6; }
.toggle-thumb {
  position:absolute; top:3px; left:3px;
  width:18px; height:18px; border-radius:50%;
  background:white; transition:transform 0.2s; box-shadow:0 1px 4px rgba(0,0,0,0.25);
}
.toggle.on .toggle-thumb { transform:translateX(20px); }

.color-dot {
  width:28px; height:28px; border-radius:50%; cursor:pointer;
  transition:transform 0.15s; border:3px solid transparent;
}
.color-dot:hover { transform:scale(1.15); }
.color-dot.active { border-color:var(--text-primary); }
.mr-2 { margin-right:8px; }
</style>
