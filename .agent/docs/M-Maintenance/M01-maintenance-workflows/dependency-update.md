# M01 - DEPENDENCY_UPDATE workflow

For Drupal core / contrib / composer / Flutter deps.
1. Review changelog/security advisories; `ddev composer update <pkg>` (scoped).
2. `ddev drush updatedb -y && ddev drush cr`; run tests (when present).
3. Smoke key flows (catalog→learn→quiz→cert, checkout, `/api/v1`).
4. Commit lockfile; note in changelog ([F03](../../F-Release/F03-changelog/_index.md)); back up first ([H02 backup](../../H-Operations/H02-runbooks/backup.md)).
