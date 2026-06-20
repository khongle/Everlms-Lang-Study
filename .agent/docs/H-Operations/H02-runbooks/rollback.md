# Runbook - Rollback

## When
Deploy verification fails, or a release causes a P0 incident.

## Steps
1. Re-deploy the previous code tag: `git checkout <prev-tag>` + `ddev composer install --no-dev`.
2. Restore the pre-deploy DB snapshot: `ddev import-db --file=<backup>.sql`.
3. Import prior config: `ddev drush cim -y` (from the prior `config/sync`).
4. `ddev drush updatedb -y && ddev drush cr`.

## Verify
Same checks as [deploy.md](deploy.md). Log the incident in [H01](../H01-incident-log/README.md).
