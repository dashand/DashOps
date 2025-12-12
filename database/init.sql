-- init.sql
-- Combined schema and migrations for Docker initialization

-- Connect to the database (if needed, but usually postgres container does this on init)
-- \c ielo_db 

-- ==========================================
-- 1. Base Schema (schema.sql)
-- ==========================================

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tasks (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(20) DEFAULT 'en_cours', -- Updated default from schema.sql
    team VARCHAR(50), 
    assigned_to VARCHAR(100),
    external_link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS history (
    id SERIAL PRIMARY KEY,
    task_id INTEGER REFERENCES tasks(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(255),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Default Admin (password: root)
INSERT INTO users (username, password_hash, role)
VALUES ('admin', '$2y$10$nGb9IptlcluFeSTRwWIwdeRBTRRzmTC0Ocm8f3MSDMw9VbmRE.G/W', 'admin')
ON CONFLICT (username) DO NOTHING;

-- ==========================================
-- 2. Migrations
-- ==========================================

-- Migration v2: Family & Status
ALTER TABLE tasks ADD COLUMN IF NOT EXISTS family VARCHAR(50);
-- Set default family
UPDATE tasks SET family = 'IG' WHERE family IS NULL;

-- Migration v3: Refresh Rate
ALTER TABLE users ADD COLUMN IF NOT EXISTS refresh_rate INTEGER DEFAULT 10;

-- Migration v4: Auth Source
ALTER TABLE users ADD COLUMN IF NOT EXISTS auth_source VARCHAR(10) DEFAULT 'local';
UPDATE users SET auth_source = 'local' WHERE auth_source IS NULL;

-- Migration v5: Task Families Table
CREATE TABLE IF NOT EXISTS task_families (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO task_families (name, display_order) VALUES
('IG', 1),
('WDM', 2),
('Infra', 3),
('Hardware', 4),
('Transit', 5),
('System', 6)
ON CONFLICT (name) DO NOTHING;
