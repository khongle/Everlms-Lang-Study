# Task: FIND-009 — Deduplicate `PayPalGateway` Class Across `lms_payment` and `lms_paypal`

**ID:** TASK-055
**Unit:** U-029 + U-061 (cleanup before webhook implementation)
**Group:** G-HARDENING
**Priority:** P1 (Code Quality / Commerce — High severity)
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
`PayPalGateway` class is duplicated under both `lms_payment/src/Plugin/PaymentGateway/PayPalGateway.php` and `lms_paypal/src/Plugin/PaymentGateway/PayPalGateway.php`. Composer autoload may resolve either depending on namespace order, leading to silent divergent behavior between dev environments. Per ADR-007, only the `lms_paypal` module should own this gateway adapter.

## Acceptance Criteria
- [ ] `git grep -c "class PayPalGateway"` in `web/modules/custom/` returns exactly 1
- [ ] The remaining canonical copy lives at `lms_paypal/src/Plugin/PaymentGateway/PayPalGateway.php`
- [ ] `lms_payment.services.yml` (and any other YAML referencing the removed class) updated; `drush cr` succeeds without "class not found" warnings
- [ ] PHPStan / Drupal coding standards run cleanly
- [ ] Plugin Manager discovery returns 1 PayPal gateway plugin definition (verify in admin payment config UI dropdown)
- [ ] No breaking change to existing order flows that reference PayPal — manual smoke test on staging order

## Technical Notes
- Compare both files first: `diff -u web/modules/custom/lms_payment/src/Plugin/PaymentGateway/PayPalGateway.php web/modules/custom/lms_paypal/src/Plugin/PaymentGateway/PayPalGateway.php`
- If `lms_payment` copy is newer / has features missing in `lms_paypal` → port differences before deletion
- Update `lms_payment.info.yml` if it lists `lms_paypal` as a sibling (should not depend on it)
- Run `composer dump-autoload` after delete

## Dependencies
- Should land BEFORE TASK-030 (PayPal webhook implementation) and TASK-050 (PROTO-002 spike) so the spike measures the canonical class
- ADR-007 ACCEPTED

## Definition of Done
- [ ] Duplicate file removed
- [ ] No regression in order placement / membership purchase flows on staging
- [ ] FIND-009 status in G02-risk-register.md → CLOSED with date and commit ref
