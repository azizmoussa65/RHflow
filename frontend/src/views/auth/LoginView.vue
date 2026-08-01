<template>
  <div class="login-page">

    <!-- ══ PANNEAU GAUCHE — PHOTO ══ -->
    <div class="left-panel">
      <!-- Overlay sombre pour lisibilité -->
      <div class="overlay"></div>

      <!-- Contenu par-dessus la photo -->
      <div class="panel-content">
        <div class="brand-logo"><img src="/logo-icon.png" alt="HRFlow" style="width:70%;height:70%;object-fit:contain" /></div>
        <h2 class="brand-title">HRFlow</h2>
        <p class="brand-sub">Système de Gestion des Ressources Humaines</p>

        <div class="quote">
          <i class="fa-solid fa-quote-left"></i>
          <p>Simplifiez la gestion de votre capital humain avec des outils modernes et intuitifs.</p>
        </div>
      </div>
    </div>

    <!-- ══ PANNEAU DROIT — FORMULAIRE ══ -->
    <div class="right-panel">
      <div class="login-box">
        <div class="text-center mb-6">
          <div class="logo-mark mx-auto mb-4" style="width:52px;height:52px"><img src="/logo-icon.png" alt="HRFlow" /></div>
          <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-primary)">Bienvenue sur HRFlow</h1>
          <p style="font-size:13px;color:var(--text-muted);margin-top:6px">Connectez-vous pour accéder à votre espace</p>
        </div>

        <div class="card" style="padding:28px">
          <form @submit.prevent="handleLogin">
            <div class="form-group">
              <label class="form-label">Email</label>
              <div style="position:relative">
                <i class="fa-solid fa-envelope" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px"></i>
                <input class="form-input" type="email" placeholder="votre@email.com"
                  v-model="form.email" style="padding-left:38px" required />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Mot de passe</label>
              <div style="position:relative">
                <i class="fa-solid fa-lock" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px"></i>
                <input class="form-input" :type="showPwd ? 'text' : 'password'"
                  placeholder="••••••••" v-model="form.password"
                  style="padding-left:38px;padding-right:40px" required />
                <button type="button" @click="showPwd=!showPwd"
                  style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted)">
                  <i :class="showPwd ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" style="font-size:13px"></i>
                </button>
              </div>
            </div>

            <div class="flex items-center justify-between mb-5">
              <label class="flex items-center gap-2" style="font-size:12px;color:var(--text-muted);cursor:pointer">
                <input type="checkbox" v-model="form.remember" style="accent-color:#3b82f6" />
                Se souvenir de moi
              </label>
              <a href="#" style="font-size:12px;color:var(--brand-primary)">Mot de passe oublié ?</a>
            </div>

            <div v-if="error" class="error-msg mb-4">
              <i class="fa-solid fa-triangle-exclamation"></i> {{ error }}
            </div>

            <button type="submit" class="btn-primary w-full" style="justify-content:center" :disabled="loading">
              <i v-if="loading" class="fa-solid fa-spinner fa-spin"></i>
              <i v-else class="fa-solid fa-right-to-bracket"></i>
              {{ loading ? 'Connexion...' : 'Se connecter' }}
            </button>
          </form>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'

const router = useRouter()
const auth   = useAuthStore()

const features = [
  { icon: 'fa-solid fa-users',           label: 'Gestion des employés'  },
  { icon: 'fa-solid fa-file-contract',   label: 'Contrats & dossiers'   },
  { icon: 'fa-solid fa-umbrella-beach',  label: 'Suivi des congés'      },
  { icon: 'fa-solid fa-diagram-project', label: 'Projets & tâches'      },
  { icon: 'fa-solid fa-star-half-stroke',label: 'Évaluations'           },
  { icon: 'fa-solid fa-calendar-days',   label: 'Calendrier RH'         },
]

const form    = ref({ email: '', password: '', remember: false })
const error   = ref('')
const loading = ref(false)
const showPwd = ref(false)

async function handleLogin() {
  error.value   = ''
  loading.value = true
  const result = await auth.login(form.value.email, form.value.password)
  loading.value = false

  if (result.success) {
    const redirectMap = { ADMIN: '/dashboard', RH: '/employes', EMPLOYE: '/profil', STAGIAIRE: '/projets' }
    router.push(redirectMap[auth.role] || '/dashboard')
  } else {
    error.value = result.message
  }
}
</script>

<style scoped>
/* ─── Layout principal ──────────────────────── */
.login-page {
  min-height: 100vh;
  display: flex;
  overflow: hidden;
}

/* ─── Panneau gauche — photo ────────────────── */
.left-panel {
  flex: 1;
  position: relative;
  background-image: url('/rh-bg.jpg');
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(10,25,60,0.75) 0%, rgba(29,78,216,0.55) 100%);
}

/* Contenu par-dessus l'image */
.panel-content {
  position: relative;
  z-index: 1;
  text-align: center;
  padding: 40px 48px;
  max-width: 480px;
}

.brand-logo {
  width: 64px; height: 64px;
  background: rgba(255,255,255,0.18);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.25);
  border-radius: 18px;
  font-size: 22px; font-weight: 800;
  color: #fff;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 16px;
  animation: logoFloat 4s ease-in-out infinite;
}
@keyframes logoFloat {
  0%,100% { transform: translateY(0); }
  50%      { transform: translateY(-8px); }
}

.brand-title {
  font-size: 2rem; font-weight: 800;
  color: #fff; margin-bottom: 6px;
  text-shadow: 0 2px 12px rgba(0,0,0,0.4);
}
.brand-sub {
  font-size: 13px;
  color: rgba(255,255,255,0.75);
  margin-bottom: 36px;
}

/* Grille de fonctionnalités */
.features {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-bottom: 32px;
}
.feature-item {
  display: flex; align-items: center; gap: 10px;
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 12px;
  padding: 11px 13px;
  color: rgba(255,255,255,0.9);
  font-size: 12px; font-weight: 500;
  transition: background 0.2s;
}
.feature-item:hover { background: rgba(255,255,255,0.18); }
.feature-icon {
  width: 30px; height: 30px;
  background: rgba(59,130,246,0.35);
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; color: #93c5fd;
  flex-shrink: 0;
}

/* Citation */
.quote {
  background: rgba(255,255,255,0.08);
  backdrop-filter: blur(6px);
  border-left: 3px solid rgba(147,197,253,0.7);
  border-radius: 10px;
  padding: 14px 18px;
  text-align: left;
  cursor: pointer;
  transition: transform 0.3s ease, background 0.3s ease, box-shadow 0.3s ease;
}
.quote:hover {
  transform: scale(1.04) translateY(-4px);
  background: rgba(255,255,255,0.18);
  box-shadow: 0 8px 32px rgba(0,0,0,0.25);
}
.quote i { color: rgba(147,197,253,0.8); margin-bottom: 6px; display: block; }
.quote p  { font-size: 12px; color: rgba(255,255,255,0.75); line-height: 1.6; margin: 0; }

/* ─── Panneau droit — formulaire ────────────── */
.right-panel {
  width: 480px;
  flex-shrink: 0;
  background: var(--bg-base);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 36px;
}
.login-box { width: 100%; max-width: 390px; }

/* ─── Utilitaires ───────────────────────────── */
.error-msg {
  background: rgba(239,68,68,0.1);
  border: 1px solid rgba(239,68,68,0.2);
  color: #ef4444; font-size: 12px;
  padding: 10px 14px; border-radius: 9px;
  display: flex; align-items: center; gap: 8px;
}
.mx-auto { margin-left:auto; margin-right:auto; display:flex; align-items:center; justify-content:center; }

/* ─── Mobile : cacher le panneau gauche ─────── */
@media (max-width: 768px) {
  .left-panel { display: none; }
  .right-panel { width: 100%; padding: 32px 20px; }
}
</style>
