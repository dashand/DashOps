\c ielo_db

-- Add family column
ALTER TABLE tasks ADD COLUMN IF NOT EXISTS family VARCHAR(50);

-- Set default family for existing tasks
UPDATE tasks SET family = 'IG' WHERE family IS NULL;

-- Migrate old statuses to new values
-- Old: todo, in_progress, done, blocked
-- New: en_cours, termine, inconnu, bloque
UPDATE tasks SET status = 'en_cours' WHERE status IN ('todo', 'in_progress');
UPDATE tasks SET status = 'termine' WHERE status = 'done';
UPDATE tasks SET status = 'bloque' WHERE status = 'blocked';
-- 'inconnu' is new, no mapping needed.
