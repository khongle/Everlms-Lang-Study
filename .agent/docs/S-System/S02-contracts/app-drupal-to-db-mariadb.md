# Service Contract - app-drupal → db-mariadb

| Aspect | Contract |
| ------ | -------- |
| Protocol | MySQL wire (PDO) |
| Driver/config | `settings.php` databases default; DDEV host `db` user/pass `db` |
| Schema | 202 tables (52 `lms_*`); entities via Drupal schema API |
| Transactions | order→enrollment→certificate consistency relies on shared-DB transactions |
| Migrations | `drush updatedb` (update hooks); config via `drush cim` |
| Backup | `ddev export-db` (SENSITIVE — PII) |
| Failure | DB down → app 500; no read replica configured |
