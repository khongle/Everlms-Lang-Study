# ISSUE-015 — lms_user_certificate.field_expiry_date Stored as varchar(20) Not Integer; Date Comparisons Fail

| Field | Value |
|---|---|
| ID | `ISSUE-015` |
| Title | lms_user_certificate.field_expiry_date is varchar(20) not int; timestamp-based date comparisons silently fail |
| Source | G4-Code-Quality / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `MEDIUM` |
| Affected page / route | Certificate expiry checks (server-side); `/certificate/verify` (`lms_certificate.verify`); `/workspace/certificates` (`lms_certificate.admin_dashboard`) |
| Owning Epic | E-07 (Certification) — |
| Owning Story | [`lms_certificate`](../../../D-Development/D03-stories/lms_certificate.md) |
| Owning module(s) | `lms_certificate` |

## Summary

`lms_user_certificate.field_expiry_date` is declared in the entity's `baseFieldDefinitions()` as a `datetime` field with date-only precision. However, the `lms_certificate_update_10001()` schema update that adds this field to the database stores the value as `varchar(20)` (a human-readable date string such as `"2027-06-20"`) rather than as a Unix timestamp integer as Drupal's entity storage layer expects for timestamp-family fields.

The mismatch has two consequences:

1. **Server-side comparisons fail silently.** Any PHP code comparing `field_expiry_date` to `REQUEST_TIME` (an integer) against a `varchar(20)` column receives a non-numeric string. PHP type coercion may not raise an error, but the comparison produces an incorrect result (the string `"2027-06-20"` coerces to `2027` as an integer, which is less than any realistic Unix timestamp, so all certificates appear expired). Any future SQL query doing `WHERE field_expiry_date <= UNIX_TIMESTAMP()` returns semantically wrong results.

2. **VerificationForm expiry check is unreliable.** `VerificationForm` loads the certificate and checks `field_revoked` for revocation status — it does not appear to check `field_expiry_date` directly — but if expiry checking is added later it will inherit the broken storage type.

The `lms_certificate_update_10001()` hook adds the field via `entityDefinitionUpdateManager`, which should produce the correct Drupal-managed column type for `datetime` storage. The varchar issue likely manifests when the field is populated outside of Drupal's entity save layer (e.g., raw SQL inserts in `ContentToolsCommands::importUserCertificates()`) or when the update hook was applied against a schema that already had the column in a different form.

The DB schema facts confirm `lms_user_certificate` exists with ~52 rows of data, meaning live records are potentially stored with the wrong type.

## Reproduction

1. Issue a certificate with an expiry date set.
2. Query the database directly:
   ```sql
   DESCRIBE lms_user_certificate;
   -- Check field_expiry_date column type
   SELECT id, field_expiry_date FROM lms_user_certificate LIMIT 5;
   -- If value looks like "2027-06-20" (string), the bug is present
   ```
3. In PHP, load the certificate entity and compare `$cert->get('field_expiry_date')->value` to `\Drupal::time()->getRequestTime()`:
   ```php
   $expiry = $cert->get('field_expiry_date')->value;
   var_dump($expiry > \Drupal::time()->getRequestTime());
   // Likely returns FALSE incorrectly (string "2027-..." vs int timestamp)
   ```

## Root Cause

`field_expiry_date` is declared as a `datetime` field type in entity `baseFieldDefinitions()`, which Drupal stores as a ISO8601 string in the database (e.g. `"2027-06-20T00:00:00"`). This is distinct from a `timestamp` type (stored as Unix integer). The issue arises when code that reads `field_expiry_date` expects a Unix integer and compares it to `REQUEST_TIME`, or when the column was created with the wrong SQL type during the update hook execution.

The facts base records: `field_expiry_date | datetime | date-only` in `CertificateTemplate` and `lms_user_certificate`. The `lms_certificate_update_10001()` adds it via `entityDefinitionUpdateManager`. If existing rows were populated with varchar strings outside of entity save (e.g., via importer raw SQL), the values may be in an inconsistent format.

Source files:
- `web/modules/custom/lms_certificate/lms_certificate.install` — `lms_certificate_update_10001()` — adds `field_expiry_date`
- `web/modules/custom/lms_certificate/src/Entity/UserCertificate.php` — `baseFieldDefinitions()` — `field_expiry_date` declared as `datetime`
- `web/modules/custom/lms_content_tools/src/Commands/ContentToolsCommands.php` — `importUserCertificates()` — potential source of non-entity-API writes

## Resolution (filled at RESOLVED)

1. **Confirm actual column type** by inspecting the live database (`DESCRIBE lms_user_certificate`).
2. If the column is `varchar`: write a `lms_certificate_update_10002()` (or 10003 since 10002 already exists for `field_seal`) that:
   a. ALTERs the column to the correct Drupal datetime storage type (or runs `entityDefinitionUpdateManager->updateEntityType()`).
   b. Migrates existing string values to the correct format.
3. Audit all code paths that read `field_expiry_date` and ensure comparisons use `DrupalDateTime` objects or the Drupal date formatter service, not raw integer comparisons.
4. Add a unit test for certificate expiry comparison: create a certificate with `field_expiry_date` one day in the past; assert it is treated as expired.

## Trace links

- **Story:** `D-Development/D03-stories/lms_certificate.md`
- **C05 module spec:** `C-Design/C05-modules/lms_certificate.md`
- **Facts base:** `modules-cert-integration.md` §lms_certificate §lms_certificate.install — `field_expiry_date (datetime, date-only)`; `db-schema.md` §lms_user_certificate — table present with ~52 rows
- **K04:** Reusable rule — when adding date/time fields to existing entities via update hooks, verify the resulting SQL column type with `DESCRIBE` after the hook runs; `datetime` and `timestamp` field types produce different column storage and require different comparison semantics.
