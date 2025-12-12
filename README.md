# DashOps 🚀

**DashOps** is a lightweight, self-hosted task management dashboard designed for Operations and IT teams. It provides a simple Kanban view to track incidents, maintenance tasks, and daily operations with a focus on speed and simplicity.

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
