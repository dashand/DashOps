-- migration_v4.sql
-- Prepare users table for LDAP integration

-- Add auth_source column to distinguish between 'local' and 'ldap' users
ALTER TABLE users ADD COLUMN auth_source VARCHAR(10) DEFAULT 'local';

-- Ensure existing users are marked as local
UPDATE users SET auth_source = 'local' WHERE auth_source IS NULL;
