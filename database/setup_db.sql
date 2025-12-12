DO
$do$
BEGIN
   IF NOT EXISTS (
      SELECT FROM pg_catalog.pg_roles
      WHERE  rolname = 'ielo_user') THEN

      CREATE ROLE ielo_user LOGIN PASSWORD 'ielo_password';
   END IF;
END
$do$;

SELECT 'CREATE DATABASE ielo_db OWNER ielo_user'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'ielo_db')\gexec
