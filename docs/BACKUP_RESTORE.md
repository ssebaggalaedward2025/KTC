# Backup and restore

Targets: **recovery point ≤ 24 hours, recovery time ≤ 4 hours.**

## What is backed up

`spatie/laravel-backup` runs nightly (01:30, see `routes/console.php`):

- MySQL database dump (gzip)
- `storage/app/private`: all uploaded plan documents, revisions and permits

The archive is **encrypted** with `BACKUP_ARCHIVE_PASSWORD` and written to the `backups` disk (`BACKUP_PATH`). Code is not backed up; it is restored from git. Old backups are pruned at 01:00 (keep all for 7 days, then daily for 16 days, then weekly and monthly; see `config/backup.php`). Failures are emailed to `BACKUP_NOTIFY_EMAIL`.

**Off-site copy:** `BACKUP_PATH` must be a separate disk, and a nightly job must copy it off the server (another council server or an encrypted cloud bucket). A backup on the same disk does not protect against disk failure.

Manual commands:

```bash
php artisan backup:run          # take a backup now
php artisan backup:list         # list backups and their health
```

## Restore procedure (practise this on a clean server)

1. Build a clean server following [DEPLOYMENT.md](DEPLOYMENT.md) steps 1–3. Do not run migrations.
2. Copy the latest archive from the off-site store, then extract it with the archive password:
   ```bash
   7z x KTC-BPTMS-2026-11-24-01-30-00.zip -p"$BACKUP_ARCHIVE_PASSWORD" -o/tmp/restore
   ```
3. Restore the database (as root or `bptms_migrate`):
   ```bash
   gunzip < /tmp/restore/db-dumps/mysql-bptms.sql.gz | mysql -u root -p bptms
   ```
4. Restore files:
   ```bash
   rsync -a /tmp/restore/var/www/bptms/storage/app/private/ /var/www/bptms/storage/app/private/
   sudo chown -R www-data:www-data /var/www/bptms/storage
   ```
5. Re-apply grants: `php artisan bptms:db-grants | sudo mysql`
6. Check:
   - `php artisan bptms:audit-verify` reports an intact chain
   - sign in as an officer, open an application, download a document
   - row counts of `applications` and `audit_logs` match the source
7. Securely delete `/tmp/restore`.

## Restore test log

Record every restore test here. Phase 1 is not done until one has succeeded.

| Date | Backup used | Restored by | Time taken | Result / issues |
|---|---|---|---|---|
| | | | | |
