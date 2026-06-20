# Runbook - Backup & Restore

## Backup
```bash
ddev export-db --file=database/backup-$(date +%F).sql.gz   # DB (timestamp via shell)
ddev drush cex -y                                          # config snapshot to config/sync
# files: back up web/sites/default/files (uploads, certificate PDFs)
```

## Restore
```bash
ddev import-db --file=<backup>.sql
ddev drush cr
```

## Cadence
- Before every deploy (mandatory) and on a daily schedule in production.
- ⚠️ DB dumps are SENSITIVE (PII) — store securely, never in the shared docs tree.
