-- migration_v5.sql
-- Create table for task families (categories)
CREATE TABLE IF NOT EXISTS task_families (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default families
INSERT INTO task_families (name, display_order) VALUES
('IG', 1),
('WDM', 2),
('Infra', 3),
('Hardware', 4),
('Transit', 5),
('System', 6)
ON CONFLICT (name) DO NOTHING;

-- Optionally, add foreign key constraint to tasks table?
-- For now, we keep it loose to avoid breaking existing data immediately, 
-- but eventually we should enforce it.
