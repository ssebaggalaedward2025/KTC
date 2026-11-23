# Security and data protection

BPTMS follows **OWASP ASVS Level 2** and the **Uganda Data Protection and Privacy Act, 2019**.

## Controls in place (Phase 1, feature 1)

| Area | Control |
|---|---|
| Authentication | One sign-in for all users (Fortify). Argon2id hashing. Minimum 12 characters with mixed case, number and symbol; breached passwords rejected in production (HIBP k-anonymity). |
| 2FA | TOTP with recovery codes. Required for `system_admin` and `town_engineer` (cannot use the panel until enrolled); optional for others. Admin can reset a lost device, and the reset is audited. |
| Brute force | 5 attempts per email + IP per 15 minutes; 30 per IP per minute; 2FA codes 5 per minute. Failed attempts are audited (email only, never the password). |
| Sessions | 30-minute idle timeout, encrypted session payloads, HttpOnly + Secure + SameSite cookies, session regenerated at login. Deactivated accounts are signed out on their next request. |
| Authorisation | Roles and permissions (spatie/laravel-permission) checked by policies on every resource. Applicants cannot reach the staff panel; staff cannot use the applicant portal. Users and roles are never deleted. |
| Headers | CSP, HSTS (production), X-Frame-Options DENY, nosniff, Referrer-Policy, Permissions-Policy, COOP. |
| Files | Private disks only, never served by URL (`serve: false`); downloads go through authorised controllers (feature 3). |
| Audit | Every model change (old and new values), sign-in, sign-out, failed sign-in, 2FA change and permission change, with user, IP, user agent and time. Rows are hash-chained (`php artisan bptms:audit-verify`, nightly). The app DB user cannot UPDATE or DELETE audit rows. |
| Secrets | Only in `.env`; never committed. Passwords and 2FA secrets are excluded from audit logs. |
| Supply chain | `composer audit` and a Trivy image scan in CI on every push. |

## Personal data

| Data | Why it is collected | Protection |
|---|---|---|
| Name, phone, email | Contact about the application | Access limited by role; access is logged |
| National ID / company registration | Building Control Act identification | Encrypted at rest (feature 2) |
| Land title details | Proof of ownership | Encrypted at rest (feature 2) |

Retention periods are in `config/bptms.php` (`retention.*`) and are **[CONFIRM]** with KTC. Records are kept for the configured period after closure; archival or deletion is then a supervised DBA task, never automatic, because history and audit tables are append-only.

## Breach response procedure

1. **Contain** (within the hour): if an account is compromised, deactivate it in Staff panel → Users and reset its 2FA. If the server is compromised, take it offline (`php artisan down`) and preserve the disk.
2. **Assess**: run `php artisan bptms:audit-verify`. Review `audit_logs` for the affected period (logins, failed logins, downloads, permission changes). Decide what personal data was exposed and whose.
3. **Notify**: the Data Protection Officer / Town Clerk must notify the **Personal Data Protection Office (PDPO)** immediately, as required by section 23 of the Data Protection and Privacy Act, 2019. Notify affected applicants as the PDPO directs.
4. **Recover**: restore from the last clean backup if needed ([BACKUP_RESTORE.md](BACKUP_RESTORE.md)). Rotate `APP_KEY` only with a plan for re-encrypting encrypted fields. Rotate DB, mail and backup passwords.
5. **Learn**: write a short incident report covering timeline, cause, data affected and fixes, and keep it with council records.

Contacts (fill in at go-live): System administrator ______ · Town Engineer ______ · DPO / Town Clerk ______ · Hosting provider ______
