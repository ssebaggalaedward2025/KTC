# Deployment guide (Ubuntu LTS VPS or NITA-U hosting)

Not suitable for shared cPanel hosting: the system needs queue workers, a scheduler, Redis and ClamAV.

## 1. Server packages

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mysql-server redis-server supervisor clamav-daemon unzip git ufw
sudo add-apt-repository ppa:ondrej/php -y && sudo apt update
sudo apt install -y php8.4-fpm php8.4-cli php8.4-mysql php8.4-redis php8.4-intl php8.4-zip \
    php8.4-gd php8.4-bcmath php8.4-mbstring php8.4-xml php8.4-curl
curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer
sudo ufw allow OpenSSH && sudo ufw allow 'Nginx Full' && sudo ufw enable
```

## 2. Database users and append-only grants

```bash
# Edit the two passwords first
sudo mysql < docker/mysql/grants.sql
```

The running application uses `bptms_app`, which has **no UPDATE or DELETE on `audit_logs` or `application_status_history`**. Migrations run as `bptms_migrate`.

## 3. Code and environment

```bash
sudo mkdir -p /var/www/bptms && sudo chown $USER:www-data /var/www/bptms
git clone <repo-url> /var/www/bptms && cd /var/www/bptms
cp .env.example .env
```

Set at least the following in `.env`:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://bptms.kyengeratc.go.ug
HASH_DRIVER=argon2id
DB_HOST=127.0.0.1
DB_USERNAME=bptms_app
DB_PASSWORD=...
REDIS_HOST=127.0.0.1
SESSION_SECURE_COOKIE=true
BPTMS_SEED_DEMO_USERS=false
BPTMS_SCANNER=clamav
CLAMAV_HOST=127.0.0.1
BPTMS_CAPTCHA=turnstile
TURNSTILE_SITE_KEY=... / TURNSTILE_SECRET=...
BACKUP_ARCHIVE_PASSWORD=<long random string, stored in the council password safe>
BACKUP_PATH=/srv/bptms-backups
MAIL_* = council SMTP
```

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
npm ci && npm run build
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 750 storage
```

## 4. Migrate, then re-apply grants (every deployment)

```bash
DB_USERNAME=bptms_migrate DB_PASSWORD=... php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force     # idempotent
php artisan bptms:db-grants --user=bptms_app --host=localhost | sudo mysql
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan filament:optimize
php artisan queue:restart
```

Check the result: `SHOW GRANTS FOR 'bptms_app'@'localhost';` must show no UPDATE/DELETE on the append-only tables.

## 5. Nginx and HTTPS

Use [docker/nginx/default.conf](../docker/nginx/default.conf) as the template. Change `root` to `/var/www/bptms/public` and `fastcgi_pass` to `unix:/run/php/php8.4-fpm.sock`. Then:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d bptms.kyengeratc.go.ug
```

Only the front controller (`public/index.php`) can execute PHP. Uploaded files live in `storage/app/private`, outside the web root.

## 6. Queue workers and scheduler

```bash
sudo cp docker/supervisor/bptms.conf /etc/supervisor/conf.d/
sudo supervisorctl reread && sudo supervisorctl update
# Scheduler
echo "* * * * * www-data cd /var/www/bptms && php artisan schedule:run >> /dev/null 2>&1" | sudo tee /etc/cron.d/bptms
```

Supervisor restarts crashed workers automatically.

## 7. After go-live

- Create the real system administrator: `php artisan tinker`, then create the user and `assignRole('system_admin')`. Remove any demo accounts.
- Run a backup and a **restore test** ([BACKUP_RESTORE.md](BACKUP_RESTORE.md)).
- Run `php artisan bptms:audit-verify` (it also runs nightly).
- If on premises, put the server on a UPS.
