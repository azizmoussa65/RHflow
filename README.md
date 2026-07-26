# HRFlow — Guide d'installation & démarrage

## Structure du projet
```
PFE/
├── frontend/     ← Vue 3 + Vite (port 5173)
├── backend/      ← Symfony 7 (port 8000)
│   └── database/
│       └── schema.sql  ← Schéma MySQL complet
└── index.html    ← Design original (référence)
```

---

## 🖥️ Prérequis

| Outil        | Version minimale |
|--------------|-----------------|
| Node.js      | 18+             |
| PHP          | 8.1+            |
| Composer     | 2+              |
| Symfony CLI  | 5+              |
| MySQL        | 8.0+            |

---

## 🚀 Frontend — Vue 3 + Vite

```bash
cd frontend

# 1. Installer les dépendances
npm install

# 2. Lancer le serveur de développement
npm run dev
# ► http://localhost:5173
```

---

## ⚙️ Backend — Symfony

### 1. Installer les dépendances PHP
```bash
cd backend
composer install
```

### 2. Créer la base de données MySQL
```sql
-- Dans phpMyAdmin ou MySQL Workbench :
CREATE DATABASE hrflow_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- Puis importer : backend/database/schema.sql
```

### 3. Configurer `.env` (si besoin)
```env
# Modifier si votre MySQL n'est pas root sans mot de passe :
DATABASE_URL="mysql://root:VOTRE_MDP@127.0.0.1:3306/hrflow_db?serverVersion=8.0"
```

### 4. Générer les migrations et les tables
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### 5. Générer les clés JWT
```bash
php bin/console lexik:jwt:generate-keypair
```

### 6. Charger les données de démonstration
```bash
php bin/console doctrine:fixtures:load
```

### 7. Lancer le serveur Symfony
```bash
symfony server:start
# ► http://localhost:8000
```

---

## 🔑 Comptes de démonstration

| Rôle           | Email                  | Mot de passe |
|----------------|------------------------|--------------|
| Manager        | manager@hrflow.tn      | password     |
| Responsable RH | rh@hrflow.tn           | password     |
| Employé        | employe@hrflow.tn      | password     |

---

## 🌙 Dark / Light Mode

> Cliquer sur le bouton **☀️ / 🌙** dans la barre de navigation en haut à droite pour basculer entre les modes.

---

## 📌 API Endpoints principaux

| Méthode | Route                      | Accès           |
|---------|----------------------------|-----------------|
| POST    | /api/auth/login            | PUBLIC          |
| GET     | /api/auth/me               | Authentifié     |
| GET     | /api/employes              | Manager, RH     |
| POST    | /api/employes              | Manager, RH     |
| PATCH   | /api/conges/{id}/approve   | Manager, RH     |
| PATCH   | /api/conges/{id}/refuse    | Manager, RH     |
| GET     | /api/dashboard/stats       | Manager, RH     |
| POST    | /api/conges                | Tous            |
| POST    | /api/dossiers              | Tous            |

---

## 🏗️ Architecture Frontend (Vue 3)

```
src/
├── assets/main.css        ← Système de design (dark + light mode)
├── stores/
│   ├── auth.js            ← JWT, rôle, login/logout
│   └── theme.js           ← Bascule dark/light
├── services/              ← Appels API Axios (1 fichier par module)
├── router/index.js        ← Guards basés sur le rôle
├── views/
│   ├── auth/LoginView.vue ← 3 onglets rôle + remplissage démo
│   ├── manager/           ← Dashboard, Évaluations
│   ├── rh/                ← Contrats, Congés (approbation), Dossiers
│   ├── employe/           ← Congés perso, Dossiers perso
│   └── shared/            ← Employés, Projets, Profil, Paramètres
└── components/
    ├── layout/            ← AppLayout (sidebar rôle), Topbar
    └── shared/            ← StatCard, ModalBase, ToastNotification
```
