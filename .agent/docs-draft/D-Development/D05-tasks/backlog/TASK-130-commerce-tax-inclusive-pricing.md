# Task: Tax-Inclusive vs Exclusive Pricing Toggle

**ID:** TASK-130
**Unit:** U-020 (lms_order)
**Group:** G-COMMERCE (hardening)
**Priority:** P1
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Add a site-wide and per-region setting to toggle between tax-inclusive and tax-exclusive price display. Vietnamese consumer protection law requires prices displayed to end consumers to be tax-inclusive (giá đã bao gồm VAT). The toggle affects display only; order records always store both pre-tax and VAT amounts separately (per TASK-127).

## Context

- Module: `lms_order`
- Default for VN: tax-inclusive (consumer sees final price with VAT included)
- B2B admin view may prefer tax-exclusive display (for VAT reclaim purposes)
- This task is a display/settings layer on top of TASK-127's calculation engine

## Acceptance Criteria

- [ ] Admin settings at `/admin/lms/settings/tax`: radio "Display prices inclusive of VAT (consumer)" vs "Display prices exclusive of VAT (business)"
- [ ] Setting stored in `lms_order.tax_settings` config object (`display_mode: inclusive|exclusive`)
- [ ] All public-facing price displays read the setting and format accordingly
- [ ] Tax-inclusive display: show single price, add note "(VAT included)" below
- [ ] Tax-exclusive display: show price + "+ VAT X%" label; show total with VAT on hover/tooltip
- [ ] Checkout summary always shows both subtotal and VAT line regardless of display mode
- [ ] Invoice PDF always shows full breakdown (subtotal + VAT amount + total) — not affected by display toggle
- [ ] Admin product list shows prices in whichever mode is configured
- [ ] Unit test: same `VatResult` object renders correctly under both modes

## Technical Notes

- Twig extension or filter: `{{ price | lms_price_display }}` reads config and applies inclusive/exclusive formatting
- Config schema: `web/modules/custom/lms_order/config/schema/lms_order.schema.yml` — add `tax_settings` mapping
- Settings form: `web/modules/custom/lms_order/src/Form/TaxSettingsForm.php`
- Must coordinate with `VatCalculatorService` from TASK-127 (do not duplicate calculation logic)
- B2B toggle: future extension — this task only delivers site-wide setting, not per-user preference

## Dependencies

- TASK-127 (VAT calculation engine)
- TASK-050 (commerce hardening)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
