-- Migration v7: Update history table schema to match code
-- Rename action to action_type
ALTER TABLE history RENAME COLUMN action TO action_type;

-- Add missing columns for value tracking
ALTER TABLE history ADD COLUMN IF NOT EXISTS old_value TEXT;
ALTER TABLE history ADD COLUMN IF NOT EXISTS new_value TEXT;
