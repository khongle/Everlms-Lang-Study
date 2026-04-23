# U08 — Admin Guide

> Administration & operations cho system administrators. Chi tiết hơn U02 Getting Started.
> Cross-reference: `H-Operations/` (runbooks, dependencies, observability), `A07-disaster-recovery.md` (Guidelines-private; xem `H-Operations/H02-runbooks/` cho ops-facing).

## System Requirements

### Production

| Component | Minimum | Recommended | Notes |
|-----------|---------|-------------|-------|
| CPU | 2 vCPU | 4 vCPU | PHP-FPM + MariaDB |
| RAM | 4 GB | 8 GB | OPcache + Redis + DB buffer |
| Disk | 40 GB SSD | 100 GB SSD | File uploads growth — media tốn lớn |
| OS | Ubuntu 22.04 LTS / Debian 12 | Ubuntu 24.04 LTS | Supported platforms |
| PHP | 8.3 | 8.3 latest patch | Extensions: gd, intl, mbstring, opcache, pdo_mysql, zip, curl |
| MariaDB | 10.11 | 10.11 latest | hoặc MySQL 8.0+ |
| Nginx | 1.24+ | Latest stable | Reverse proxy + static |
| Redis | 7+ | Latest stable | Cache backend |
| Node.js | 20 LTS | 20 LTS | Chỉ build-time (SCSS compile) |

Xem `B-Requirements/B02-technical-requirements.md` §Tech Stack.

### Staging

Mirror production size 50% (2 vCPU / 4 GB / 40 GB). Use same OS + versions.

### Local dev

DDEV (Docker-based) — xem [U02 — Getting Started](U02-getting-started.md).

## Installation & Deployment

Xem U02 cho standard install. Production-specific:

### Production Configuration (settings.php)

```php
// Trusted hosts
$settings['trusted_host_patterns'] = [
  '^lms\.example\.com$',
];

// Cookie secure + samesite
$settings['cookie_secure'] = TRUE;
$settings['cookie_httponly'] = TRUE;
$cookies['samesite'] = 'Lax';

// Cache backend: Redis
$settings['redis.connection']['interface'] = 'PhpRedis';
$settings['redis.connection']['host'] = '127.0.0.1';
$settings['redis.connection']['port'] = 6379;
$settings['cache']['default'] = 'cache.backend.redis';

// File paths
$settings['file_public_path'] = 'sites/default/files';
$settings['file_private_path'] = '/var/lms/private';
$settings['file_temp_path'] = '/tmp';

// Reverse proxy
$settings['reverse_proxy'] = TRUE;
$settings['reverse_proxy_addresses'] = ['127.0.0.1'];

// Performance
$config['system.performance']['css']['preprocess'] = TRUE;
$config['system.performance']['js']['preprocess'] = TRUE;
$config['system.performance']['cache']['page']['max_age'] = 3600;

// Environment-specific overrides
$config['lms_payment.stripe']['secret_key'] = getenv('STRIPE_SECRET_KEY');
$config['lms_payment.stripe']['webhook_secret'] = getenv('STRIPE_WEBHOOK_SECRET');
$config['lms_ai.openai']['api_key'] = getenv('OPENAI_API_KEY');
```

### Environment Variables

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `STRIPE_SECRET_KEY` | If Stripe | — | Stripe live secret |
| `STRIPE_WEBHOOK_SECRET` | If Stripe | — | Webhook signing secret |
| `PAYPAL_CLIENT_ID` | If PayPal | — | (Pending FIND-002 fix) |
| `PAYPAL_CLIENT_SECRET` | If PayPal | — | — |
| `OPENAI_API_KEY` | If AI | — | OpenAI key |
| `GEMINI_API_KEY` | If Gemini | — | Google Gemini key |
| `ZOOM_JWT_KEY` | If Zoom | — | Zoom marketplace key |
| `ZOOM_JWT_SECRET` | If Zoom | — | — |
| `SMTP_HOST` | Yes | — | Email delivery |
| `SMTP_USER` | Yes | — | — |
| `SMTP_PASS` | Yes | — | — |
| `DATABASE_URL` | Yes | — | mysql://user:pass@host:3306/db |
| `REDIS_URL` | Yes | — | tcp://127.0.0.1:6379 |
| `TRUSTED_HOST_REGEX` | Yes | — | Regex cho host allowlist |

> **Secrets management:** Không commit `.env`. Dùng Vault / AWS Secrets Manager / systemd `EnvironmentFile`. Rotate keys per `H-Operations/` policy.

## User Management

Xem [admin-01-user-role-management.md](U03-feature-guides/admin-01-user-role-management.md) cho usage flow.

### Roles (7)

| Role | Scope |
|------|-------|
| Administrator | Toàn quyền |
| Manager | Read-all, moderate, report |
| Instructor | Own content |
| Content Editor | All content (no user PII) |
| Student | Self-service |
| Authenticated | Base logged-in |
| Anonymous | Public |

Full permission matrix: `C-Design/C02-user-roles.md`.

### Bulk Operations

- `/admin/people` → checkbox → **Apply action** (block, role add/remove, delete).
- **Caution**: Delete account với content = anonymous author = irreversible.

### Access Control

- Password policy: medium+ (admin `/admin/config/people/password-strength`).
- 2FA: module `tfa` (nếu installed) — recommended cho Admin/Manager.
- Session timeout: 180 min max.

### GDPR / Data Export & Deletion

- User → **Account settings** → **Download my data** (JSON + CSV).
- Deletion request: admin `/admin/people/{uid}/cancel` → chọn "Delete account + content".
- Audit log: `/admin/reports/dblog?type=user` — retain 12 months minimum.

## Backup & Restore

### Backup Procedure

```bash
# Daily DB dump
drush sql:dump --result-file=/backups/lms-db-$(date +%Y%m%d).sql.gz --gzip

# Files rsync
rsync -avz sites/default/files/ /backups/files-$(date +%Y%m%d)/

# Upload to offsite
aws s3 sync /backups/ s3://everlms-backups/$(date +%Y-%m)/
```

### Restore Procedure

```bash
# Stop web
systemctl stop nginx php8.3-fpm

# Restore DB
drush sql:drop -y
gunzip < backup.sql.gz | drush sql:cli

# Restore files
rsync -avz backup-files/ sites/default/files/

# Clear cache + start
drush cr
systemctl start php8.3-fpm nginx
```

### Backup Schedule

| What | Frequency | Retention | Location |
|------|-----------|-----------|----------|
| Database | Daily 02:00 UTC | 30 days | Local + S3 |
| Files | Weekly Sunday | 12 weeks | S3 |
| Config (`drush cex`) | On each deploy | Keep with git | Git repo |
| Full snapshot | Monthly | 12 months | S3 Glacier |

RPO/RTO mục tiêu: xem `.agent/00-Guidelines/A07-disaster-recovery.md` (SA-private); ops-facing: `H-Operations/H02-runbooks/`.

## Monitoring & Health

### Health Checks

| Endpoint | Expected | Action on Failure |
|----------|----------|-------------------|
| `/health` | 200 OK (PROPOSED — chưa implement) | Check PHP-FPM + DB |
| `/health/db` | 200 OK + DB ping (PROPOSED) | Check DB; swap to replica |
| `/user/login` | 200 | Full runtime fail — check nginx/php logs |

Cần implement trong Phase 4 (tracked).

### Logs

| Log | Location | Rotation | Key patterns |
|-----|----------|----------|--------------|
| Drupal dblog | `/admin/reports/dblog` | DB, pruned 10k rows | `severity=3` (Error), `severity=4` (Warning) |
| Nginx access | `/var/log/nginx/access.log` | logrotate daily | 5xx, slow > 1s |
| Nginx error | `/var/log/nginx/error.log` | logrotate daily | connect failures |
| PHP-FPM | `/var/log/php8.3-fpm.log` | logrotate daily | OOM, segfault |
| Cron | `/var/log/drupal-cron.log` | daily | Task failures |

### Dashboards & Alerts

Config as code ở `H-Operations/H07-observability-as-code/`:
- `dashboards/` — Grafana JSON hoặc Datadog config.
- `alerts/` — alert rules (SLO burn rate, error rate, latency).
- `slos/` — service-level objectives.

## Maintenance

### Routine Tasks

| Task | Frequency | Procedure |
|------|-----------|-----------|
| Apply Drupal security updates | Mỗi thứ Tư | `composer update drupal/core-* --with-dependencies` → test → deploy |
| Run `drush updb` | Sau update | `drush updb -y && drush cr` |
| Prune stale notifications | Weekly | Cron task (auto) |
| Review error log | Daily | `drush ws --severity=3` |
| Backup verification | Weekly | Restore test vào staging |
| Rotate credentials | Quarterly | Stripe/PayPal/OpenAI keys |
| Audit role permissions | Quarterly | `C02-user-roles.md` review |
| Prune inactive users | Yearly | `/admin/people` filter inactive > 2 years |

### Upgrading

General: U02 §Upgrading. Admin-specific:
1. Backup full trước update (DB + files + config).
2. Test trên staging.
3. Check `F-Release/F03-changelog.md` cho breaking changes.
4. `drush cex` → commit config trước production deploy.
5. Production: maintenance mode ON → `composer install --no-dev -o` → `drush updb -y` → `drush cim -y` → `drush cr` → maintenance OFF.
6. Smoke test per U06/tutorial-03 §Phase 13.
7. Verify `F-Release/F02-release-checklist/` post-deploy items.

## Payment Gateway Setup

### Stripe

1. Stripe dashboard → **Developers** → **API keys** → get LIVE keys.
2. **Webhooks** → Add endpoint: `https://lms.example.com/payment/stripe/webhook`.
3. Events to listen: `checkout.session.completed`, `payment_intent.succeeded`, `payment_intent.payment_failed`, `invoice.payment_succeeded`, `customer.subscription.*`.
4. Get **Signing secret** → set env `STRIPE_WEBHOOK_SECRET`.
5. Set env `STRIPE_SECRET_KEY`.
6. Publishable key: `drush config:set lms_payment.stripe.publishable_key "pk_live_..." -y`.
7. **Verify signature implementation** trước khi bật (U-060). Until then, use test mode only.

### PayPal

⚠️ FIND-002 chưa resolve — skip cho đến khi TASK-003 merged.

### Bank Transfer (manual)

Admin xác nhận order thủ công ở `/admin/lms/orders` → chọn order → **Mark as paid**.

## Certificate Templates

1. `/admin/config/lms/certificate/templates` → **New template**.
2. Upload HTML template (Twig syntax) + CSS.
3. Upload font files (WOFF2 recommended).
4. Assign default fields: `{{ user.name }}`, `{{ course.title }}`, `{{ issued_at }}`, `{{ code }}`.
5. **Preview** với dummy data.
6. **Save** → available cho course attach.
7. Clear dompdf font cache: `/admin/config/lms/certificate/fonts/rebuild`.

## AI Provider Setup

### OpenAI

1. Platform → **API keys** → create key với scope AI models only.
2. Set env `OPENAI_API_KEY`.
3. `/admin/config/lms/ai/openai`:
   - `model = gpt-4o-mini` (cost balance).
   - **`max_tokens = 2000`** ⚠️ REQUIRED (FIND-003).
   - `timeout_seconds = 60`.
4. Set `daily_quota_per_user = 10`.
5. Bật `FF_FEAT_AI_BUILDER` sau khi test staging.

### Gemini

1. Google AI Studio → get key.
2. Env `GEMINI_API_KEY`.
3. `/admin/config/lms/ai/gemini`: model `gemini-1.5-flash`.
4. Pass key qua header (không query string — FIND-008 mitigation).

### Pollinations (free tier)

- Không cần API key.
- Rate limit aggressive — chỉ dùng cho demo/dev.

## SMTP Setup

1. Enable module `mailsystem` + `swiftmailer` hoặc `symfony_mailer` (recommended).
2. `/admin/config/system/mailsystem`:
   - Default formatter: `symfony_mailer`.
3. `/admin/config/system/mailsystem/transport`:
   - SMTP host + port (e.g., SendGrid, Postmark).
   - TLS on.
   - Auth username + password (env var).
4. From address: `noreply@lms.example.com` (SPF/DKIM configured tại DNS).
5. Send test email qua `/admin/config/system/mailsystem/test`.

## Moderation

### Forum Moderation

- Default: auto-publish. Change `/admin/config/lms/forum/settings` → **Require approval** nếu muốn pre-moderation.
- **Pending queue:** `/admin/lms/forum/pending`.
- **Reports:** `/admin/lms/forum/reports` — user report → admin action (delete / warn / ban).
- **Ban user:** `/admin/people/{uid}` → block.

### Course Content Moderation

- Course publish workflow: draft → review (optional) → published.
- Content Editor role có thể edit anyone's course. Instructor chỉ own.
- Revision history: Drupal core provides per-entity revisions (không enable by default cho custom entities; roadmap).

## Troubleshooting (Admin)

Xem [U05 — Troubleshooting](U05-troubleshooting.md) cho user-facing. Admin-specific:

### PHP-FPM out of memory
**Symptom:** 500 errors spike; FPM log `pm.max_children reached`.
**Solution:** Tăng `pm.max_children` trong `/etc/php/8.3/fpm/pool.d/www.conf`; restart FPM; consider Redis scaling.

### DB queries slow
**Solution:**
1. `drush sqlq "SHOW PROCESSLIST"` — xem long-running.
2. Run `EXPLAIN` trên slow query.
3. Add missing index (check entity schema).

### Redis down
**Symptom:** Slow page load, cache miss spike.
**Solution:** Restart redis; Drupal fallback sang DB cache auto, nhưng slow.

### Cron stops firing
**Solution:**
1. Check `/admin/reports/status` — cron last run timestamp.
2. `drush cron` manual.
3. Check system cron logs.

---

*End of U08-admin-guide.md*
