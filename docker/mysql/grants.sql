-- BPTMS production database users. Run once as MySQL root:
--   mysql -u root -p < docker/mysql/grants.sql
-- Replace both passwords first.
--
--   bptms_migrate : owns the schema; used ONLY for `php artisan migrate` during deployment.
--   bptms_app     : used by the running application (DB_USERNAME in .env).
--
-- After EVERY deployment migration, apply the application grants generated from
-- the live schema (append-only tables get no UPDATE/DELETE):
--
--   php artisan bptms:db-grants --user=bptms_app --host=localhost | mysql -u root -p
--
-- See docs/DEPLOYMENT.md.

CREATE DATABASE IF NOT EXISTS bptms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'bptms_migrate'@'localhost' IDENTIFIED BY 'CHANGE_ME_MIGRATE';
CREATE USER IF NOT EXISTS 'bptms_app'@'localhost' IDENTIFIED BY 'CHANGE_ME_APP';

GRANT ALL PRIVILEGES ON bptms.* TO 'bptms_migrate'@'localhost';

FLUSH PRIVILEGES;
