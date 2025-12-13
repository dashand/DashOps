-- Migration v6: Add created_by column to tasks
ALTER TABLE tasks ADD COLUMN IF NOT EXISTS created_by VARCHAR(50);
