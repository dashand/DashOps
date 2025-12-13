# DashOps 🚀

**DashOps** (Dashboard Ops) is a lightweight, self-hosted task management dashboard designed for Operations and IT teams. It provides a simple Kanban view to track incidents, maintenance tasks, and daily operations with a focus on speed and simplicity.

![DashOps Screenshot](https://via.placeholder.com/800x400?text=DashOps+Dashboard+Preview) 
*(Add a real screenshot here)*

## ✨ Features

*   **Kanban Dashboard**: Visualize tasks by status (To Do, In Progress, Blocked, Done).
*   **Real-time status**: Tasks are color-coded for quick status recognition.
*   **Categories & Families**: Organize tasks by IT domain (System, Network, Hardware, etc.).
*   **Global & Per-Task History**: Track every action, update, and movement with a detailed audit log.
*   **User Management**: Built-in simple auth with Admin/User roles.
*   **Docker Ready**: Deploys in seconds with a fully containerized stack (PHP + PostgreSQL).
*   **Dark Mode**: Built-in theme toggle.
*   **Mobile Friendly**: Responsive design for on-the-go checks.

## 🛠️ Installation

The easiest way to run DashOps is with **Docker**.

### Prerequisites
*   Docker
*   Docker Compose

### Quick Start

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/dashand/DashOps.git
    cd DashOps
    ```

2.  **Start the application:**
    ```bash
    docker-compose up -d --build
    ```

3.  **Access the dashboard:**
    Open your browser at `https://localhost` (or your server IP).
    *   *Note: Self-signed certificate is used by default, accept the security warning.*

4.  **Login:**
    *   **Username:** `admin`
    *   **Password:** `root`

## ⚙️ Configuration

DashOps is configured via environment variables in `docker-compose.yml`.

| Variable | Description | Default |
| :--- | :--- | :--- |
| `DB_HOST` | Database hostname | `db` |
| `DB_NAME` | Database name | `ielo_db` |
| `DB_USER` | Database user | `ielo_user` |
| `DB_PASS` | Database password | `ielo_password` |
| `LDAP_ENABLED` | Enable LDAP Auth | `false` |
| `LDAP_HOST` | LDAP Server IP/Hostname | - |
| `LDAP_PORT` | LDAP Server Port | `389` |
| `LDAP_BASE_DN` | Base DN for search | - |
| `LDAP_BIND_DN` | User DN to bind with | - |
| `LDAP_BIND_PASS` | Password for Bind DN | - |

### Database
The database is automatically initialized on the first run using `database/init.sql`.

## 🎨 Customization

You can customize the application directly from the **Admin Panel** (logged in as admin):

*   **Manage Columns/Families**: Rename, add, or delete dashboard columns to match your workflow.
*   **Manage Users**: Create accounts, reset passwords, and assign roles.

## 🔒 Security

*   **Native HTTPS**: The container enforces HTTPS on port 443 using a self-signed certificate generated at build time.
*   **Network Isolation**: The application and database communicate over an internal Docker network (`dashops_net`).

## 📄 License

[MIT](LICENSE)

---

# 🇫🇷 Version Française

**DashOps** (Dashboard Ops)est un tableau de bord de gestion de tâches léger et auto-hébergé, conçu pour les équipes Opérations et IT. Il offre une vue Kanban simple pour suivre les incidents, les tâches de maintenance et les opérations quotidiennes, en mettant l'accent sur la rapidité et la simplicité.

## ✨ Fonctionnalités

*   **Tableau de bord Kanban** : Visualisez les tâches par statut (À faire, En cours, Bloqué, Terminé).
*   **Statut en temps réel** : Les tâches ont un code couleur pour une reconnaissance rapide.
*   **Catégories & Familles** : Organisez les tâches par domaine IT (Système, Réseau, Matériel, etc.).
*   **Historique Global & par Tâche** : Suivez chaque action, mise à jour et mouvement grâce à un journal d'audit détaillé.
*   **Gestion des Utilisateurs** : Authentification simple intégrée avec rôles Admin/Utilisateur.
*   **Prêt pour Docker** : Se déploie en quelques secondes avec une stack entièrement conteneurisée (PHP + PostgreSQL).
*   **Mode Sombre** : Bascule de thème intégrée.
*   **Mobile Friendly** : Design responsive pour les vérifications en déplacement.

## 🛠️ Installation

La façon la plus simple de lancer DashOps est avec **Docker**.

### Prérequis
*   Docker
*   Docker Compose

### Démarrage Rapide

1.  **Cloner le dépôt :**
    ```bash
    git clone https://github.com/dashand/DashOps.git
    cd DashOps
    ```

2.  **Lancer l'application :**
    ```bash
    docker-compose up -d --build
    ```

3.  **Accéder au tableau de bord :**
    Ouvrez votre navigateur sur `https://localhost` (ou l'IP de votre serveur).
    *   *Note : Un certificat auto-signé est utilisé par défaut, acceptez l'avertissement de sécurité.*

4.  **Connexion :**
    *   **Utilisateur :** `admin`
    *   **Mot de passe :** `root`

## ⚙️ Configuration

DashOps se configure via des variables d'environnement dans le fichier `docker-compose.yml`.

| Variable | Description | Défaut |
| :--- | :--- | :--- |
| `DB_HOST` | Nom d'hôte de la base de données | `db` |
| `DB_NAME` | Nom de la base de données | `ielo_db` |
| `DB_USER` | Utilisateur de la base de données | `ielo_user` |
| `DB_PASS` | Mot de passe de la base de données | `ielo_password` |
| `LDAP_ENABLED` | Activer l'authentification LDAP | `false` |
| `LDAP_HOST` | IP/Nom d'hôte du serveur LDAP | - |
| `LDAP_PORT` | Port du serveur LDAP | `389` |
| `LDAP_BASE_DN` | Base DN pour la recherche | - |
| `LDAP_BIND_DN` | User DN pour le bind (connexion) | - |
| `LDAP_BIND_PASS` | Mot de passe pour le Bind DN | - |

### Base de données
La base de données est automatiquement initialisée au premier lancement grâce au fichier `database/init.sql`.

## 🎨 Personnalisation

Vous pouvez personnaliser l'application directement depuis le **Panneau Admin** (en étant connecté en tant qu'admin) :

*   **Gérer les Colonnes/Familles** : Renommez, ajoutez ou supprimez des colonnes du tableau de bord pour correspondre à votre flux de travail.
*   **Gérer les Utilisateurs** : Créez des comptes, réinitialisez les mots de passe et attribuez des rôles.

## 🔒 Sécurité

*   **HTTPS Natif** : Le conteneur force l'utilisation du HTTPS sur le port 443 en utilisant un certificat auto-signé généré lors de la construction.
*   **Isolation Réseau** : L'application et la base de données communiquent sur un réseau Docker interne (`dashops_net`).

## 📄 Licence

[MIT](LICENSE)
