-- init.sql
-- DashOps v1.0.0 Database Initialization
-- Defines the complete schema and default data.

-- ==========================================
-- 1. Table Definitions
-- ==========================================

-- USERS Table
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    auth_source VARCHAR(10) DEFAULT 'local',
    refresh_rate INTEGER DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TASK FAMILIES Table (Columns)
CREATE TABLE IF NOT EXISTS task_families (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TASKS Table
CREATE TABLE IF NOT EXISTS tasks (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(20) DEFAULT 'en_cours',
    family VARCHAR(50) DEFAULT 'IG',
    team VARCHAR(50), 
    assigned_to VARCHAR(100),
    external_link VARCHAR(255),
    created_by VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- HISTORY Table
CREATE TABLE IF NOT EXISTS history (
    id SERIAL PRIMARY KEY,
    task_id INTEGER REFERENCES tasks(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    action_type VARCHAR(255),
    details TEXT,
    old_value TEXT,
    new_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- 2. Default Data
-- ==========================================

-- Default Tables/Families
INSERT INTO task_families (name, display_order) VALUES
('IG', 1),
('WDM', 2),
('Infra', 3),
('Hardware', 4),
('Transit', 5),
('System', 6)
ON CONFLICT (name) DO NOTHING;

-- Default Admin User (Password: root)
-- Note: Change this password immediately after deployment!
INSERT INTO users (username, password_hash, role, auth_source)
VALUES ('admin', '$2y$10$nGb9IptlcluFeSTRwWIwdeRBTRRzmTC0Ocm8f3MSDMw9VbmRE.G/W', 'admin', 'local')
ON CONFLICT (username) DO NOTHING;

-- ==========================================
-- 3. Permissions
-- ==========================================

-- Grant permissions to application user (ielo_user)
-- Note: Assuming the database is named 'ielo_db' and user is 'ielo_user'
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO ielo_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO ielo_user;

-- Ensure ownership
ALTER TABLE users OWNER TO ielo_user;
ALTER TABLE tasks OWNER TO ielo_user;
ALTER TABLE history OWNER TO ielo_user;
ALTER TABLE task_families OWNER TO ielo_user;