# F01-deployment - Deployment

**Last updated:** 2026-06-21

## Environments
| Env | Stack | Notes |
| --- | ----- | ----- |
| Local | DDEV (`everlms`, `everlms.ddev.site`), PHP 8.3, MariaDB 10.11, nginx-fpm | dev/QA; `ddev start` |
| Production | Drupal 11 / PHP 8.3 / MariaDB (host TBD) | self-hosted |

## Deploy sequence (config-based)
```bash
git pull
ddev composer install --no-dev   # prod: optimized autoloader
ddev drush updatedb -y            # run update hooks
ddev drush cim -y                 # import config from config/sync
ddev drush cr                     # rebuild cache
ddev drush cron                   # warm queues
```

## Fresh provisioning (from this repo)
See [A01 commands](../../A-Project-Information/A01-CLAUDE/_index.md): `ddev start` → `composer install` → `import-db database/db.sql` (or `site:install` + `cim`) → `updatedb` → `cr` → `uli`.

## Pre-deploy gates (release-blocking)
- ⚠️ **Secrets must be in Key/env, not config** (R-001) — do not deploy public with secrets in `config/sync`.
- ⚠️ **Payment webhooks present** for any live gateway (R-002).
- Latest [Z-review](../../Z-Reviews/README.md) verdict ≥ GO-WITH-FIXES with P0s addressed.

## Rollback
See [H02 runbooks/rollback](../../H-Operations/H02-runbooks/rollback.md): restore previous code tag + DB snapshot + `drush cim` of prior config; `drush cr`.

## When NOT to roll back
Không rollback (fix-forward thay vì roll back) khi:
- Sự cố nằm ở data/config đã migrate không tương thích ngược (rollback code sẽ để lại schema/config mới → vỡ). Fix-forward + `drush updb`.
- Chỉ một feature-flag lỗi: tắt flag (H05) thay vì rollback toàn deploy.
- Sự cố ở third-party (payment/zoom/AI provider) — rollback không giúp; xử lý ở integration/incident (H01).
- Đã qua mốc irreversible (email/notification/broadcast đã gửi, order đã capture): rollback không thu hồi được side-effect → fix-forward + bồi hoàn thủ công.
