# HRFlow — Guide d'installation & démarrage

## Structure du projet
```
HRFLOW/
├── frontend/     ← Vue 3 + Vite (port 5173)
├── backend/      ← Flask + MongoDB (port 8000)
│   ├── app/            ← code de l'application (routes, modèles, config)
│   ├── seed.py          ← crée les comptes admin/rh de base
│   └── requirements.txt
└── start.bat     ← lance tout (Mongo + backend + frontend)
```

---

## 🖥️ Prérequis

| Outil        | Version minimale |
|--------------|-------------------|
| Node.js      | 18+               |
| Python       | 3.10+              |
| MongoDB      | service local (ex: MongoDB Community Server) sur le port 27017 |

---

## 🚀 Frontend — Vue 3 + Vite

```bash
cd frontend
npm install
npm run dev
# ► http://localhost:5173
```

---

## ⚙️ Backend — Flask + MongoDB

### 1. MongoDB
Assurez-vous qu'un serveur MongoDB tourne sur `localhost:27017` (service Windows,
ou toute autre installation locale). Rien à lancer manuellement si c'est déjà un service.

### 2. Créer l'environnement virtuel et installer les dépendances
```bash
python -m venv .venv
.venv\Scripts\activate        # Windows
pip install -r requirements.txt
```

### 3. Configurer `.env`
```bash
copy .env.example .env
```
Le fichier `.env.example` contient déjà des valeurs de développement fonctionnelles
(`MONGO_URI=mongodb://localhost:27017/hrflow`). Renseignez `GROQ_API_KEY` si vous
utilisez le chatbot.

### 4. Initialiser les comptes de base (aucun employé)
```bash
python seed.py
```

### 5. Lancer le serveur Flask
```bash
python run.py
# ► http://localhost:8000
```

---

## 🔑 Comptes de démonstration

| Rôle           | Email                          | Mot de passe  |
|----------------|---------------------------------|---------------|
| Admin          | admin@satisfyinsight.cm         | password123   |
| Responsable RH | rh@satisfyinsight.com           | password123   |

Aucun employé n'existe par défaut : l'Admin (ou le RH) doit ajouter les employés
depuis l'écran **Employés** de l'application.

⚠️ Changez ces mots de passe après la première connexion (page Profil).

---

## 🌙 Dark / Light Mode

> Cliquer sur le bouton **☀️ / 🌙** dans la barre de navigation en haut à droite pour basculer entre les modes.

---

## 📌 API Endpoints principaux

| Méthode | Route                      | Accès           |
|---------|----------------------------|-----------------|
| POST    | /api/auth/login            | PUBLIC          |
| GET     | /api/auth/me               | Authentifié     |
| GET     | /api/employes              | Admin, RH       |
| POST    | /api/employes              | Admin, RH       |
| PATCH   | /api/conges/{id}/approve   | Admin, RH       |
| PATCH   | /api/conges/{id}/refuse    | Admin, RH       |
| GET     | /api/dashboard/stats       | Admin, RH       |
| POST    | /api/conges                | Tous            |
| POST    | /api/dossiers               | Tous            |

---

## 🏗️ Architecture Frontend (Vue 3)

```
src/
├── assets/main.css        ← Système de design (dark + light mode)
├── stores/
│   ├── auth.js            ← JWT, rôle, login/logout
│   └── theme.js            ← Bascule dark/light
├── services/                ← Appels API Axios (1 fichier par module)
├── router/index.js          ← Guards basés sur le rôle
├── views/
│   ├── admin/                ← Dashboard, Évaluations
│   ├── rh/                   ← Contrats, Congés (approbation), Dossiers
│   ├── employe/               ← Congés perso, Dossiers perso
│   └── shared/                ← Employés, Projets, Profil, Paramètres
└── components/
    ├── layout/                ← AppLayout (sidebar rôle), Topbar
    └── shared/                 ← StatCard, ModalBase, ToastNotification
```

## 🏗️ Architecture Backend (Flask + MongoDB)

```
backend/app/
├── config.py           ← Variables d'environnement
├── extensions.py        ← Connexion MongoDB, JWT
├── serializers.py        ← Conversion des documents Mongo → JSON
├── utils.py               ← ObjectId, dates, décorateur de rôles
└── routes/
    ├── auth.py             ← login, /me, profil
    ├── employes.py          ← CRUD employés (rôle EMPLOYE)
    ├── conges.py             ← demandes de congé + approbation
    ├── contrats.py            ← contrats
    ├── dossiers.py             ← dossiers administratifs + upload fichier
    ├── evaluations.py          ← évaluations de performance
    ├── projets.py                ← projets
    ├── taches.py                  ← tâches liées à un projet
    ├── dashboard.py                ← statistiques
    └── chatbot.py                  ← assistant RH (RAG + Groq)
```

Collections MongoDB : `users`, `conges`, `contrats`, `dossiers`, `evaluations`, `projets`, `taches`.
