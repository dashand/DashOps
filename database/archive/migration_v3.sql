-- migration_v3.sql
-- Add refresh_rate column to users table

\c ielo_db

ALTER TABLE users ADD COLUMN IF NOT EXISTS refresh_rate INTEGER DEFAULT 10;
