<template>
  <div class="landing">

    <!-- NAV -->
    <div class="nav">
      <div class="nav-brand">
        <div class="logo-mark"><img src="/logo-icon.png" alt="HRFlow" /></div>
        <div class="brand-name">HRFlow</div>
      </div>
      <div class="nav-links">
        <a href="#features-rh">Ressources Humaines</a>
        <a href="#features-projet">Gestion de projet</a>
      </div>
      <div class="nav-actions">
        <RouterLink to="/login" class="link-connexion">Se connecter</RouterLink>
        <a href="#demo" class="btn-solid">Réserver une démo</a>
      </div>
    </div>

    <!-- HERO -->
    <div class="hero">
      <div class="blob blob-1" />
      <div class="blob blob-2" />
      <div class="hero-left">
        <h1>HRFlow, la plateforme qui connecte toute votre équipe</h1>
        <p class="hero-sub">Transformez votre gestion RH : congés, contrats, dossiers et projets, réunis dans une plateforme unique.</p>
        <div class="hero-ctas">
          <a href="#demo" class="btn-solid btn-lg">Réserver une démo</a>
          <a href="#screenshots" class="btn-outline btn-lg">Voir les captures →</a>
        </div>
        <div class="hero-reassurance">
          <div>✓ Sans engagement</div>
          <div>✓ Données synchronisées en temps réel</div>
        </div>
      </div>
      <div class="hero-right">
        <div class="hero-image-wrap" @mousemove="onHeroMouseMove" @mouseleave="onHeroMouseLeave" :style="heroTiltStyle">
          <img src="/landing/hero-team.png" class="hero-image" alt="Équipe utilisant HRFlow en réunion" />
          <div class="badge badge-ai">
            <div class="badge-ai-icon">
              <div class="pulse-ring" />
              <div class="badge-ai-dot">✦</div>
            </div>
            <div>
              <div class="badge-title">Assistant IA</div>
              <div class="badge-sub">Recommandations RH auto</div>
            </div>
          </div>
          <div class="badge badge-sync">
            <div class="badge-sync-icon">⏳</div>
            <div class="badge-title-dark">Synchronisé en temps réel</div>
          </div>
        </div>
      </div>
    </div>

    <!-- FEATURES RH -->
    <div id="features-rh" ref="featRhEl" class="section" :class="{ visible: visible.featRh }">
      <div class="section-head">
        <div class="eyebrow">Ressources Humaines</div>
        <h2>Toute la gestion RH, simplifiée</h2>
        <p>Des congés aux dossiers administratifs, chaque process RH devient fluide et centralisé.</p>
      </div>
      <div class="rh-grid">
        <div class="feature-col">
          <div v-for="f in featuresRhLeft" :key="f.title" class="feature-card">
            <div class="feature-icon">{{ f.initial }}</div>
            <div class="feature-title">{{ f.title }}</div>
            <div class="feature-desc">{{ f.desc }}</div>
          </div>
        </div>
        <div class="rh-photo-wrap">
          <img src="/landing/rh-recrutement.jpg" class="rh-photo" alt="Recrutement HRFlow" />
        </div>
        <div class="feature-col">
          <div v-for="f in featuresRhRight" :key="f.title" class="feature-card">
            <div class="feature-icon">{{ f.initial }}</div>
            <div class="feature-title">{{ f.title }}</div>
            <div class="feature-desc">{{ f.desc }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- FEATURES PROJET -->
    <div id="features-projet" ref="featProjEl" class="section section-projet" :class="{ visible: visible.featProj }">
      <div class="section-head">
        <div class="eyebrow">Gestion de projet</div>
        <h2>Pilotez vos projets et vos équipes ensemble</h2>
        <p>Les mêmes équipes, les mêmes plannings, un seul outil pour les RH et les projets.</p>
      </div>
      <div class="projet-grid">
        <div class="projet-illustration-wrap">
          <img src="/landing/projet-illustration.png" class="projet-illustration" alt="Équipe collaborant sur un projet" />
        </div>
        <div class="projet-cards">
          <div v-for="f in featuresProj" :key="f.title" class="feature-card feature-card-white">
            <div class="feature-icon">{{ f.initial }}</div>
            <div class="feature-title">{{ f.title }}</div>
            <div class="feature-desc">{{ f.desc }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- SCREENSHOTS -->
    <div id="screenshots" ref="screensEl" class="section" :class="{ visible: visible.screenshots }">
      <div class="section-head">
        <div class="eyebrow">Aperçu produit</div>
        <h2>Une interface claire, du premier au dernier clic</h2>
        <p>De la connexion au suivi des congés, tout est pensé pour aller vite.</p>
      </div>
      <div class="screens-grid">
        <div v-for="s in screenshots" :key="s.caption" class="screen-item">
          <div class="screen-frame">
            <BrowserWindow :url="s.url" :width="360" :height="230">
              <img :src="s.img" class="screen-img" :alt="s.caption" />
            </BrowserWindow>
            <div class="chip" :style="{ background: s.chipColor, animationDelay: s.delay }">{{ s.chip }}</div>
          </div>
          <div class="screen-caption">{{ s.caption }}</div>
        </div>
      </div>
    </div>

    <!-- MOBILE / TEMPS REEL -->
    <div id="mobile" ref="mobileEl" class="section mobile-section" :class="{ visible: visible.mobile }">
      <div class="mobile-grid">
        <div>
          <div class="eyebrow eyebrow-light">Mobile &amp; temps réel</div>
          <h2 class="h2-light">Toujours connecté, même en déplacement</h2>
          <p class="mobile-p">L'application mobile HRFlow partage la même base de données que le web : une demande de congé validée sur ordinateur apparaît instantanément sur le téléphone de l'employé.</p>
          <div class="mobile-points">
            <div v-for="p in mobilePoints" :key="p" class="mobile-point">
              <div class="check-dot">✓</div>
              <div>{{ p }}</div>
            </div>
          </div>
        </div>
        <div class="phone-wrap">
          <div class="phone-halo" />
          <div class="phone">
            <div class="phone-notch" />
            <div class="phone-screen">
              <div class="phone-banner">✓ Congé approuvé</div>
              <div class="phone-card">
                <div class="phone-card-label">MES TÂCHES</div>
                <div class="phone-task"><span class="dot" style="background:#60a5fa" />Maquettes Sprint</div>
                <div class="phone-task"><span class="dot" style="background:#fbbf24" />Entretien recrutement</div>
                <div class="phone-task"><span class="dot" style="background:#34d399" />Revue budget</div>
              </div>
              <div class="phone-chart">
                <div class="bar" style="height:40%;background:#3b82f6" />
                <div class="bar" style="height:70%;background:#60a5fa" />
                <div class="bar" style="height:55%;background:#3b82f6" />
                <div class="bar" style="height:90%;background:#93c5fd" />
                <div class="bar" style="height:65%;background:#60a5fa" />
              </div>
            </div>
          </div>
          <div class="badge badge-notif">🔔 Nouvelle notification</div>
        </div>
      </div>
    </div>

    <!-- DEMO FORM -->
    <div id="demo" ref="ctaEl" class="cta-final" :class="{ visible: visible.cta }">
      <h2>Réservez votre démo personnalisée</h2>
      <p>Un membre de notre équipe vous présente HRFlow et répond à vos questions, sans engagement.</p>

      <form v-if="!demoSent" class="demo-form" @submit.prevent="submitDemo">
        <div class="demo-form-row">
          <input class="demo-input" v-model="demoForm.nom" placeholder="Nom complet *" required />
          <input class="demo-input" v-model="demoForm.email" type="email" placeholder="Email professionnel *" required />
        </div>
        <input class="demo-input" v-model="demoForm.entreprise" placeholder="Entreprise" />
        <textarea class="demo-input" rows="3" v-model="demoForm.message" placeholder="Votre besoin (optionnel)"></textarea>
        <div v-if="demoError" class="demo-error">{{ demoError }}</div>
        <button class="btn-white demo-submit" type="submit" :disabled="submittingDemo">
          {{ submittingDemo ? 'Envoi...' : 'Réserver ma démo' }}
        </button>
      </form>
      <div v-else class="demo-success">
        <div class="demo-success-icon">✓</div>
        <div style="font-weight:700;font-size:17px;margin-bottom:6px">Merci, votre demande a bien été envoyée !</div>
        <div style="color:#dbeafe;font-size:14px">Notre équipe vous recontacte très prochainement.</div>
      </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
      <div class="footer-grid">
        <div>
          <div class="nav-brand" style="margin-bottom:14px">
            <div class="logo-mark logo-mark-sm"><img src="/logo-icon.png" alt="HRFlow" /></div>
            <div class="brand-name" style="font-size:17px">HRFlow</div>
          </div>
          <div class="footer-baseline">La plateforme RH et gestion de projet pour les PME, tous secteurs.</div>
        </div>
        <div>
          <div class="footer-col-title">Produit</div>
          <a href="#features-rh">Ressources Humaines</a>
          <a href="#features-projet">Gestion de projet</a>
        </div>
        <div>
          <div class="footer-col-title">Entreprise</div>
          <a href="#">À propos</a>
          <a href="#demo">Contact</a>
        </div>
        <div>
          <div class="footer-col-title">Légal</div>
          <a href="#">Confidentialité</a>
          <a href="#">CGU</a>
        </div>
      </div>
      <div class="footer-copyright">© 2026 HRFlow. Tous droits réservés.</div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount } from 'vue'
import BrowserWindow from '@/components/landing/BrowserWindow.vue'
import contactService from '@/services/contactService.js'

const featRhEl = ref(null)
const featProjEl = ref(null)
const screensEl = ref(null)
const mobileEl = ref(null)
const ctaEl = ref(null)

const visible = reactive({ featRh: false, featProj: false, screenshots: false, mobile: false, cta: false })
const observers = []

onMounted(() => {
  const targets = [
    ['featRh', featRhEl],
    ['featProj', featProjEl],
    ['screenshots', screensEl],
    ['mobile', mobileEl],
    ['cta', ctaEl],
  ]
  targets.forEach(([key, elRef]) => {
    if (!elRef.value) return
    const obs = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          visible[key] = true
          obs.disconnect()
        }
      })
    }, { threshold: 0.12 })
    obs.observe(elRef.value)
    observers.push(obs)
  })
})

onBeforeUnmount(() => {
  observers.forEach((o) => o.disconnect())
})

// ── Tilt 3D du hero ──────────────────────────────────────────────────
const tiltX = ref(0)
const tiltY = ref(0)
const heroTiltStyle = ref({ transform: 'perspective(1400px) rotateX(0deg) rotateY(0deg)', transition: 'transform .3s ease-out' })

function onHeroMouseMove(e) {
  const rect = e.currentTarget.getBoundingClientRect()
  const px = (e.clientX - rect.left) / rect.width - 0.5
  const py = (e.clientY - rect.top) / rect.height - 0.5
  tiltX.value = py * -10
  tiltY.value = px * 14
  heroTiltStyle.value = {
    transform: `perspective(1400px) rotateX(${tiltX.value}deg) rotateY(${tiltY.value}deg)`,
    transition: 'transform .1s ease-out',
  }
}
function onHeroMouseLeave() {
  heroTiltStyle.value = {
    transform: 'perspective(1400px) rotateX(0deg) rotateY(0deg)',
    transition: 'transform .3s ease-out',
  }
}

// ── Contenu ──────────────────────────────────────────────────────────
const featuresRhLeft = [
  { initial: 'C', title: 'Gestion des congés', desc: 'Demandes, validations et suivi des absences en quelques clics.' },
  { initial: 'D', title: 'Contrats & dossiers', desc: 'Centralisez contrats, documents administratifs et historique RH.' },
]
const featuresRhRight = [
  { initial: 'E', title: 'Annuaire employés', desc: "Une fiche complète par collaborateur, accessible à toute l'équipe." },
  { initial: 'M', title: 'Messagerie interne', desc: 'Échangez avec vos équipes sans quitter la plateforme.' },
]
const featuresProj = [
  { initial: 'S', title: 'Suivi de projets', desc: "Visualisez l'avancement de chaque projet en temps réel." },
  { initial: 'T', title: 'Attribution de tâches', desc: 'Répartissez la charge de travail et suivez les échéances.' },
  { initial: 'B', title: 'Tableaux de bord', desc: 'Indicateurs clés pour piloter équipes et ressources.' },
  { initial: 'C', title: 'Collaboration', desc: 'Fichiers partagés, commentaires et notifications centralisées.' },
]
const screenshots = [
  { url: 'app.hrflow.io/connexion', img: '/landing/screenshot-connexion.png', chip: '🔒 Connexion sécurisée', chipColor: '#0f1f3d', caption: 'Connexion sécurisée', delay: '0s' },
  { url: 'app.hrflow.io/conges', img: '/landing/screenshot-conges.png', chip: '✅ Validation instantanée', chipColor: '#2563eb', caption: "Suivi des congés en un coup d'œil", delay: '0.6s' },
  { url: 'app.hrflow.io/recrutement', img: '/landing/screenshot-recrutement.png', chip: '📊 Pilotage en direct', chipColor: '#7c3aed', caption: 'Suivi du recrutement', delay: '1.2s' },
]
const mobilePoints = [
  'Synchronisation en temps réel entre web et mobile',
  'Notifications push pour congés, tâches et messages',
  'Consultation hors-ligne des plannings et dossiers',
  'Authentification unique (SSO) sur toutes les plateformes',
]

// ── Formulaire de demande de démo ───────────────────────────────────
const demoForm = ref({ nom: '', email: '', entreprise: '', message: '' })
const submittingDemo = ref(false)
const demoSent = ref(false)
const demoError = ref('')

async function submitDemo() {
  demoError.value = ''
  if (!demoForm.value.nom.trim() || !demoForm.value.email.trim()) {
    demoError.value = 'Merci de renseigner votre nom et votre email.'
    return
  }
  submittingDemo.value = true
  try {
    await contactService.submit(demoForm.value)
    demoSent.value = true
  } catch (_) {
    demoError.value = 'Une erreur est survenue. Merci de réessayer dans un instant.'
  }
  submittingDemo.value = false
}
</script>

<style scoped>
.landing {
  font-family: 'Inter', Helvetica, Arial, sans-serif;
  color: #0f1f3d;
  background: #eef4ff;
  overflow-x: hidden;
}
a { color: #2563eb; text-decoration: none; }

/* ── Nav ── */
.nav {
  position: sticky; top: 0; z-index: 50;
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 48px;
  background: rgba(238,244,255,0.85);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid #d5e2fa;
  flex-wrap: wrap; gap: 12px;
}
.nav-brand { display: flex; align-items: center; gap: 10px; }
.logo-mark {
  width: 36px; height: 36px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.logo-mark img { width: 100%; height: 100%; object-fit: contain; }
.logo-mark-sm { width: 32px; height: 32px; }
.brand-name { font-weight: 700; font-size: 19px; letter-spacing: -0.01em; }
.nav-links { display: flex; align-items: center; gap: 22px; font-size: 14px; font-weight: 500; color: #334155; }
.nav-links a { color: #334155; white-space: nowrap; }
.nav-actions { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
.link-connexion { font-size: 14px; font-weight: 600; color: #1d4ed8; white-space: nowrap; }

.btn-solid {
  background: #2563eb; color: #fff; padding: 10px 18px; border-radius: 9px;
  font-size: 14px; font-weight: 600; white-space: nowrap; display: inline-block;
  transition: background 0.15s;
}
.btn-solid:hover { background: #1d4ed8; color: #fff; }
.btn-lg { padding: 14px 26px; border-radius: 10px; font-size: 15.5px; font-weight: 700; }
.btn-outline {
  background: #fff; color: #0f1f3d; padding: 14px 26px; border-radius: 10px;
  font-size: 15.5px; font-weight: 700; border: 1.5px solid #e2e8f0; transition: border-color 0.15s;
}
.btn-outline:hover { border-color: #94a3b8; color: #0f1f3d; }

/* ── Hero ── */
.hero {
  position: relative; padding: 96px 48px 80px; max-width: 1360px; margin: 0 auto;
  display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 56px; align-items: center;
  overflow: hidden;
}
.blob { position: absolute; border-radius: 50%; z-index: 0; }
.blob-1 {
  top: -140px; right: -160px; width: 560px; height: 560px;
  background: radial-gradient(circle, rgba(59,130,246,0.18), rgba(59,130,246,0) 70%);
  animation: blobDrift 14s ease-in-out infinite;
}
.blob-2 {
  bottom: -160px; left: -120px; width: 420px; height: 420px;
  background: radial-gradient(circle, rgba(99,102,241,0.14), rgba(99,102,241,0) 70%);
  animation: blobDrift2 18s ease-in-out infinite;
}
.hero-left { position: relative; z-index: 1; }
.hero-left h1 {
  font-size: clamp(30px, 3.4vw, 52px); line-height: 1.12; font-weight: 800;
  letter-spacing: -0.02em; margin: 0 0 22px; color: #0f1f3d;
}
.hero-sub { font-size: 18px; line-height: 1.6; color: #475569; max-width: 520px; margin: 0 0 34px; }
.hero-ctas { display: flex; gap: 14px; flex-wrap: wrap; }
.hero-reassurance { display: flex; gap: 28px; margin-top: 40px; color: #64748b; font-size: 13.5px; font-weight: 500; flex-wrap: wrap; }

.hero-right { position: relative; z-index: 1; display: flex; justify-content: center; perspective: 1400px; }
.hero-image-wrap {
  box-shadow: 0 50px 90px -24px rgba(15,31,61,0.4); border-radius: 18px; position: relative; max-width: 560px;
}
.hero-image { width: 100%; max-width: 560px; height: auto; display: block; border-radius: 18px; }

.badge { position: absolute; border-radius: 14px; display: flex; align-items: center; gap: 10px; }
.badge-ai {
  top: -22px; right: -34px; background: #0f1f3d; color: #fff; padding: 12px 16px;
  box-shadow: 0 18px 34px -12px rgba(15,31,61,0.5); animation: floaty2 5s ease-in-out infinite;
}
.badge-ai-icon { position: relative; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.pulse-ring { position: absolute; inset: 0; border-radius: 50%; background: #60a5fa; animation: pulseRing 2.2s ease-out infinite; }
.badge-ai-dot {
  position: relative; width: 26px; height: 26px; border-radius: 50%;
  background: linear-gradient(135deg,#60a5fa,#2563eb); display: flex; align-items: center; justify-content: center;
  font-size: 12px; font-weight: 800;
}
.badge-title { font-size: 12.5px; font-weight: 700; line-height: 1.2; }
.badge-sub { font-size: 10.5px; color: #93c5fd; line-height: 1.2; }
.badge-sync {
  bottom: -18px; left: -30px; background: #fff; color: #0f1f3d; padding: 11px 15px;
  box-shadow: 0 18px 34px -14px rgba(15,31,61,0.28); animation: floaty3 6.5s ease-in-out infinite;
}
.badge-sync-icon {
  width: 24px; height: 24px; border-radius: 50%; background: #dcfce7; color: #16a34a;
  display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;
}
.badge-title-dark { font-size: 12px; font-weight: 700; }

/* ── Sections communes ── */
.section {
  padding: 88px 48px; max-width: 1240px; margin: 0 auto;
  opacity: 0; transform: translateY(28px); transition: opacity .7s ease, transform .7s ease;
}
.section.visible { opacity: 1; transform: translateY(0); }
.section-head { max-width: 640px; margin: 0 auto 52px; text-align: center; }
.eyebrow {
  color: #1d4ed8; font-weight: 700; font-size: 13.5px; letter-spacing: 0.04em;
  text-transform: uppercase; margin-bottom: 10px;
}
.section-head h2 { font-size: 34px; font-weight: 800; letter-spacing: -0.015em; margin: 0 0 14px; color: #0f1f3d; }
.section-head p { color: #64748b; font-size: 16.5px; line-height: 1.6; margin: 0; }

/* ── RH ── */
.rh-grid { display: grid; grid-template-columns: 1fr 1.1fr 1fr; gap: 24px; align-items: center; }
.feature-col { display: flex; flex-direction: column; gap: 20px; }
.feature-card {
  background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 22px 20px;
  transition: border-color 0.15s, background 0.15s;
}
.feature-card:hover { border-color: #93c5fd; background: #f0f6ff; }
.feature-card-white { background: #fff; border-color: #dbe6fb; transition: border-color 0.15s, transform 0.15s; }
.feature-card-white:hover { border-color: #93c5fd; transform: translateY(-3px); }
.feature-icon {
  width: 34px; height: 34px; border-radius: 10px; background: #dbeafe; color: #1d4ed8;
  display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13.5px; margin-bottom: 12px;
}
.feature-title { font-weight: 700; font-size: 15px; margin-bottom: 6px; }
.feature-desc { color: #64748b; font-size: 13.5px; line-height: 1.5; }
.rh-photo-wrap { display: flex; justify-content: center; perspective: 1200px; }
.rh-photo {
  width: 100%; max-width: 340px; border-radius: 18px; animation: drift3d 6.5s ease-in-out infinite;
  box-shadow: 0 30px 60px -20px rgba(15,31,61,0.35); transform-style: preserve-3d;
}

/* ── Projet ── */
.section-projet {
  background: linear-gradient(180deg,#e3edff,#d9e8ff); border-radius: 28px; overflow: hidden;
}
.projet-grid { display: grid; grid-template-columns: 0.9fr 1.1fr; gap: 44px; align-items: center; }
.projet-illustration-wrap { display: flex; justify-content: center; }
.projet-illustration {
  width: 100%; max-width: 400px; animation: drift3d 7s ease-in-out infinite;
  filter: drop-shadow(0 30px 40px rgba(29,78,216,0.22));
}
.projet-cards { display: grid; grid-template-columns: repeat(2,1fr); gap: 18px; }

/* ── Screenshots ── */
.screens-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px,1fr)); gap: 32px; align-items: start; }
.screen-frame { position: relative; box-shadow: 0 24px 50px -18px rgba(15,31,61,0.28); border-radius: 12px; }
.screen-img { width: 100%; height: 100%; object-fit: cover; object-position: top; display: block; }
.chip {
  position: absolute; top: -16px; right: 24px; color: #fff; padding: 9px 14px; border-radius: 10px;
  font-size: 12.5px; font-weight: 700; display: flex; align-items: center; gap: 7px;
  box-shadow: 0 14px 26px -10px rgba(15,31,61,0.5); animation: chipPop 4.5s ease-in-out infinite;
  white-space: nowrap;
}
.screen-caption { text-align: center; margin-top: 26px; font-weight: 600; color: #334155; }

/* ── Mobile ── */
.mobile-section {
  background: linear-gradient(135deg,#0f1f3d,#1d3a6e); border-radius: 28px;
  max-width: 1320px; margin-left: auto; margin-right: auto; color: #fff;
}
.mobile-grid { display: grid; grid-template-columns: 1fr 0.8fr; gap: 56px; align-items: center; max-width: 1200px; margin: 0 auto; }
.eyebrow-light { color: #93c5fd; }
.h2-light { font-size: 32px; font-weight: 800; letter-spacing: -0.015em; margin: 0 0 18px; color: #fff; }
.mobile-p { color: #cbd5e1; font-size: 16px; line-height: 1.65; margin: 0 0 28px; max-width: 480px; }
.mobile-points { display: flex; flex-direction: column; gap: 18px; }
.mobile-point { display: flex; gap: 12px; align-items: flex-start; font-size: 14.5px; color: #e2e8f0; line-height: 1.5; }
.check-dot {
  width: 22px; height: 22px; border-radius: 50%; background: rgba(59,130,246,0.25); color: #93c5fd;
  display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0; margin-top: 1px;
}
.phone-wrap { display: flex; justify-content: center; position: relative; }
.phone-halo {
  position: absolute; width: 300px; height: 300px; border-radius: 50%;
  background: radial-gradient(circle, rgba(59,130,246,0.35), rgba(59,130,246,0) 70%); z-index: 0;
}
.phone {
  width: 240px; height: 490px; border-radius: 34px; background: #0b1730; border: 6px solid #16264a;
  box-shadow: 0 30px 70px -20px rgba(0,0,0,0.55); overflow: hidden; position: relative; z-index: 1;
}
.phone-notch {
  position: absolute; top: 0; left: 50%; transform: translateX(-50%);
  width: 90px; height: 18px; background: #16264a; border-radius: 0 0 12px 12px; z-index: 2;
}
.phone-screen {
  padding: 34px 14px 14px; display: flex; flex-direction: column; gap: 12px; height: 100%;
  background: linear-gradient(180deg,#0f2050,#0b1730); box-sizing: border-box;
}
.phone-banner { background: #16a34a; border-radius: 10px; padding: 10px 12px; font-size: 11.5px; font-weight: 700; color: #fff; }
.phone-card { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 12px; }
.phone-card-label { font-size: 10.5px; color: #93c5fd; font-weight: 700; margin-bottom: 8px; }
.phone-task { display: flex; align-items: center; gap: 8px; font-size: 11px; color: #e2e8f0; margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.phone-task .dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.phone-chart {
  background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 12px;
  display: flex; align-items: end; gap: 6px; height: 80px;
}
.phone-chart .bar { width: 16px; border-radius: 4px; }
.badge-notif {
  top: -26px; right: -24px; background: #fff; color: #0f1f3d; padding: 10px 14px;
  font-size: 11.5px; font-weight: 700; white-space: nowrap;
  box-shadow: 0 16px 30px -12px rgba(0,0,0,0.4); animation: floaty2 5s ease-in-out infinite; z-index: 2;
}

/* ── CTA final ── */
.cta-final {
  margin: 32px 48px 96px; padding: 72px 48px; border-radius: 28px;
  background: linear-gradient(135deg,#1d4ed8,#2563eb); text-align: center; color: #fff;
  opacity: 0; transform: translateY(28px); transition: opacity .7s ease, transform .7s ease;
}
.cta-final.visible { opacity: 1; transform: translateY(0); }
.cta-final h2 { font-size: 34px; font-weight: 800; letter-spacing: -0.015em; margin: 0 0 16px; color: #fff; }
.cta-final p { font-size: 16.5px; color: #dbeafe; margin: 0 0 30px; max-width: 520px; margin-left: auto; margin-right: auto; }
.cta-buttons { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
.btn-white { background: #fff; color: #1d4ed8; padding: 14px 28px; border-radius: 10px; font-weight: 700; font-size: 15.5px; transition: background 0.15s; }
.btn-white:hover { background: #eff6ff; color: #1d4ed8; }
.btn-outline-white {
  background: transparent; color: #fff; padding: 14px 28px; border-radius: 10px; font-weight: 700;
  font-size: 15.5px; border: 1.5px solid rgba(255,255,255,0.5); transition: border-color 0.15s;
}
.btn-outline-white:hover { border-color: #fff; color: #fff; }

/* ── Formulaire de démo ── */
.demo-form { max-width: 480px; margin: 0 auto; text-align: left; }
.demo-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.demo-input {
  width: 100%; box-sizing: border-box; background: rgba(255,255,255,0.12);
  border: 1.5px solid rgba(255,255,255,0.3); border-radius: 10px;
  padding: 12px 14px; font-size: 14px; color: #fff; margin-bottom: 12px;
  font-family: inherit; outline: none; transition: border-color 0.15s;
}
.demo-input::placeholder { color: rgba(255,255,255,0.65); }
.demo-input:focus { border-color: #fff; }
.demo-submit { width: 100%; justify-content: center; display: flex; align-items: center; }
.demo-submit:disabled { opacity: 0.7; cursor: default; }
.demo-error { color: #fecaca; font-size: 13px; margin: -4px 0 12px; }
.demo-success { max-width: 420px; margin: 0 auto; text-align: center; }
.demo-success-icon {
  width: 48px; height: 48px; border-radius: 50%; background: rgba(255,255,255,0.15);
  display: flex; align-items: center; justify-content: center; font-size: 22px;
  color: #fff; margin: 0 auto 14px;
}

/* ── Footer ── */
.footer { padding: 56px 48px 40px; border-top: 1px solid #d5e2fa; background: #e3edff; }
.footer-grid { max-width: 1240px; margin: 0 auto; display: grid; grid-template-columns: 1.4fr 1fr 1fr 1fr; gap: 32px; }
.footer-baseline { color: #64748b; font-size: 13.5px; line-height: 1.6; max-width: 260px; }
.footer-col-title { font-weight: 700; font-size: 13.5px; margin-bottom: 14px; }
.footer-grid a { display: block; color: #64748b; font-size: 13.5px; margin-bottom: 10px; }
.footer-copyright {
  max-width: 1240px; margin: 40px auto 0; padding-top: 24px; border-top: 1px solid #e2e8f0;
  color: #94a3b8; font-size: 12.5px;
}

/* ── Animations ── */
@keyframes drift3d { 0%,100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-16px) rotate(2deg); } }
@keyframes chipPop { 0%,100% { transform: translateY(0) scale(1); } 50% { transform: translateY(-8px) scale(1.03); } }
@keyframes floaty2 { 0%,100% { transform: translateY(0px) rotate(-3deg); } 50% { transform: translateY(-10px) rotate(2deg); } }
@keyframes floaty3 { 0%,100% { transform: translateY(0px); } 50% { transform: translateY(12px); } }
@keyframes pulseRing { 0% { transform: scale(0.9); opacity: 0.7; } 70% { transform: scale(1.6); opacity: 0; } 100% { transform: scale(1.6); opacity: 0; } }
@keyframes blobDrift { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(30px,-24px) scale(1.08); } 66% { transform: translate(-22px,18px) scale(0.95); } }
@keyframes blobDrift2 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-34px,26px) scale(1.12); } }

/* ── Responsive ── */
@media (max-width: 900px) {
  .hero { grid-template-columns: 1fr; padding: 64px 24px 48px; }
  .rh-grid { grid-template-columns: 1fr; }
  .projet-grid { grid-template-columns: 1fr; }
  .mobile-grid { grid-template-columns: 1fr; }
  .footer-grid { grid-template-columns: 1fr 1fr; }
  .nav { padding: 14px 20px; }
  .nav-links { display: none; }
  .section { padding: 56px 20px; }
  .demo-form-row { grid-template-columns: 1fr; }
}
</style>
