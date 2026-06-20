# Runbook - Deploy

## Pre
- [F02 release checklist](../../F-Release/F02-release-checklist/README.md) green (P0s closed).
- DB backup (see [backup.md](backup.md)).

## Steps
```bash
git pull
ddev composer install --no-dev
ddev drush updatedb -y
ddev drush cim -y
ddev drush cr
ddev drush cron
```

## Verify
- `ddev drush status` healthy; homepage + `/courses` load; a test enroll + quiz works; `/api/v1/courses/search` returns JSON.

## On failure
→ [rollback.md](rollback.md).
