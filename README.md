# ASMR Audio Online

Production PHP 8.2+ website for ASMR guides, licensed audio, creators, and affiliate equipment.

## Install

1. Create `config.php` from `config.example.php` and set database credentials and `base_url`.
2. Import `database/install.sql`, then optionally `database/content-seed.sql`.
3. Point the web root to this directory and enable PHP 8.2+ with PDO MySQL.
4. Make `uploads/image` and `uploads/audio` writable by PHP; keep `config.php` private.
5. For Nginx, add the clean URL rewrite rules documented by the deployment administrator.

## Scheduled publishing

Scheduled articles are promoted on the first visitor request after their publication time. For reliable timing, run a cron request every five minutes:

`*/5 * * * * curl -fsS https://asmraudio.online/ >/dev/null`

## Security checklist

- Use HTTPS and strong database credentials.
- Keep `config.php`, `storage/`, and database backups outside public downloads.
- Apply least-privilege roles and review admin activity.
- Back up the database and `uploads/` before migrations.
- Keep PHP and the operating system updated.
Deployment test: GitHub Actions auto-deploy is connected.
