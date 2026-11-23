# KTC BPTMS: Building Plan Process Tracking and Management System

Kyengera Town Council, Town Engineer's Department. Every building plan application gets a digital file and a process timeline: where it is, who holds it, what was done, what happens next, and whether it is overdue. BPTMS complements the national BIMS; it does not replace it.

| | |
|---|---|
| Stack | PHP 8.4, Laravel 13, MySQL 8, Redis, Filament 5 (staff panel), Livewire 4 + Tailwind 4 (applicant portal) |
| Architecture | [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) |
| Deployment | [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) |
| Backup and restore | [docs/BACKUP_RESTORE.md](docs/BACKUP_RESTORE.md) |
| Security and data protection | [docs/SECURITY.md](docs/SECURITY.md) |
| Out of scope (Phase 2) | [PHASE2.md](PHASE2.md) |

## Local setup with Docker (recommended)

Requires Docker Desktop (Windows: with the WSL2 backend).

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app npm install
docker compose exec app npm run build
```

- App: http://localhost:8080 · Staff panel: http://localhost:8080/staff
- Mail catcher (Mailpit): http://localhost:8025
- Virus scanning: `docker compose --profile scan up -d` and set `BPTMS_SCANNER=clamav`.

## Local setup without Docker (Windows, quick start)

Requires PHP 8.3+ (extensions: intl, zip, gd, bcmath, exif, pdo_sqlite, sodium, fileinfo), Composer and Node 20+.

```bash
cp .env.example .env
# In .env set: DB_CONNECTION=sqlite, SESSION_DRIVER=database, QUEUE_CONNECTION=database,
#              CACHE_STORE=database, MAIL_MAILER=log, HASH_DRIVER=bcrypt, APP_URL=http://localhost:8000
composer install
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve          # http://localhost:8000
php artisan queue:work     # in a second terminal
```

> `HASH_DRIVER=bcrypt` is for local Windows only: the Windows PHP build hashes Argon2id very slowly (about 3 s). Production uses Argon2id.

## Demo accounts (non-production only)

`php artisan db:seed` creates one account per role, named `[DEMO] …`. Demo accounts are never seeded in production and are refused at sign-in there.

| Role | Email |
|---|---|
| System administrator | `system.admin@demo.ktc.test` |
| Town Engineer | `town.engineer@demo.ktc.test` |
| Records Officer | `records.officer@demo.ktc.test` |
| Building Control Officer | `building.control.officer@demo.ktc.test` |
| Physical Planner | `physical.planner@demo.ktc.test` |
| Building Committee member | `building.committee@demo.ktc.test` |
| Committee Secretary | `committee.secretary@demo.ktc.test` |
| Applicant | `applicant@demo.ktc.test` |

Password for all: `Demo@KTC-2026!`. The system administrator and Town Engineer must set up two-factor authentication (any TOTP app) at first sign-in.

## Everyday commands

| Task | Command |
|---|---|
| Run tests | `vendor/bin/pest` |
| Code style | `vendor/bin/pint` |
| Static analysis | `vendor/bin/phpstan analyse` |
| Dependency audit | `composer audit` |
| Verify audit log integrity | `php artisan bptms:audit-verify` |
| Print production DB grants | `php artisan bptms:db-grants` |

## Environment variables

Every variable is listed with its default in [.env.example](.env.example). BPTMS-specific ones:

| Variable | Purpose |
|---|---|
| `BPTMS_IDLE_TIMEOUT` / `SESSION_LIFETIME` | Idle minutes before sign-out (default 30) |
| `BPTMS_SEED_DEMO_USERS` | Seed demo accounts (always off in production) |
| `BPTMS_DEADLINE_DAY_TYPE` | `working` or `calendar` days for deadlines [CONFIRM] |
| `BPTMS_UPLOAD_MAX_KB` | Maximum upload size |
| `BPTMS_SCANNER` | `clamav` (production) or `null` (local only) |
| `BPTMS_CAPTCHA` | `turnstile` (production) or `none` |
| `BACKUP_ARCHIVE_PASSWORD` | Encrypts nightly backup archives |
| `BACKUP_DISK` / `BACKUP_PATH` | Where backups are written |
| `TRUSTED_PROXIES` | Only if a load balancer sits in front of Nginx |

Business settings that KTC can change at runtime (stage limits, fee rates, holidays, workflow) are managed in the staff panel, not in `.env`.

## Project status

Phase 1 is built feature by feature (see section 9 of the brief):

- [x] 1. Foundation: Docker, single sign-in with Fortify, roles and permissions, policies, TOTP 2FA (enforced for system admin and Town Engineer), lockout, password rules, idle timeout, security headers, append-only hash-chained audit log, user and role administration, CI
- [ ] 2. Application registration
- [ ] 3. Documents
- [ ] 4. Workflow, status history and timeline
- [ ] 5–14. See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)
