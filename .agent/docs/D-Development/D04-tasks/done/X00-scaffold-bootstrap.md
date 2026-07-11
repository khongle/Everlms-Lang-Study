---
task: X00 scaffold bootstrap (W00)
task_type: scaffold
status: done
wave: W00
modules: [all]
difficulty: 1
---

# X00 - Repo scaffold & one-command bootstrap (W00)

## Goal
Thiết lập scaffold repo + one-command bootstrap theo hợp đồng [D10-scaffold-contract](../../D10-scaffold-contract/_index.md). As-built: DDEV + Drupal 11 + config/sync + DB dump đã tồn tại và chạy được.

## Source Trace
- Hợp đồng: [D10-scaffold-contract](../../D10-scaffold-contract/_index.md) (Repo scaffold, One-command bootstrap, Toolchain, CI jobs, Seed & fixtures, Definition of ready).
- CP: CP3 (framework setup) — DoR mở CP4 dev.

## Steps
- `ddev start && ddev composer install && ddev import-db --file=database/db.sql`
- `ddev drush cim -y && ddev drush updb -y && ddev drush cr`
- Seed demo: `ddev drush lms:generate-demo && ddev drush lms:mock-certs`.

## Evidence
Scaffold artifacts hiện diện trong repo và bootstrap resolve tới site chạy được. Xem hợp đồng
[D10-scaffold-contract](../../D10-scaffold-contract/_index.md) và [A01-CLAUDE commands](../../../A-Project-Information/A01-CLAUDE/_index.md).

Pasted gate output:
```
$ ddev drush config:status
 No differences.  (config-check: OK)
$ ddev drush pm:list --status=enabled --type=module | grep -c '^ *lms_\|open_switcher'
 28
$ ls config/sync/*.yml | wc -l
 396
```
Toolchain: PHP 8.3 / Drupal ^11.3 / Drush ^13.7 / MariaDB 10.11 (khớp A01-CLAUDE target stack).

## Close-gate
SA sign-off: scaffold as-built đã verify (build/lint/config-check xanh); DoR đạt → CP3 cleared, mở CP4 dev.

## Links
- [D10-scaffold-contract](../../D10-scaffold-contract/_index.md) · [D09 W00](../../D09-queue/_index.md) · [A01-CLAUDE commands](../../../A-Project-Information/A01-CLAUDE/_index.md)
