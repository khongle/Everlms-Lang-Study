# I07-migration-guides - Migration Guides

## API consumers
- v1 is the first stable version; no prior API to migrate from.
- Future breaking changes → `/api/v2` with a migration note here; `v1` deprecation window announced via [I05](../I05-api-changelog/_index.md).

## Data / platform
- EverLMS is documented in place (no platform migration). Demo/seed import via `lms:import` ([F-023](../../B-Requirements/B03-features/F-023-content-tools-and-seeding.md)).
- For environment moves, see [F01 deployment](../../F-Release/F01-deployment/_index.md) + [H02 backup](../../H-Operations/H02-runbooks/backup.md).
