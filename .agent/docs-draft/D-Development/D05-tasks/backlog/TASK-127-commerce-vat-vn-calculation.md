# Task: VAT Calculation for Vietnam (10%/5%/0%)

**ID:** TASK-127
**Unit:** U-020 (lms_order), U-021 (lms_payment)
**Group:** G-COMMERCE (hardening)
**Priority:** P1
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Implement configurable VAT calculation for the Vietnamese market. Standard rate is 10%, reduced rate is 5% for certain educational categories, and 0% for exports. VAT rate is configurable per course/product. Supports both tax-inclusive and tax-exclusive display modes. All order totals store both pre-tax and tax amounts separately.

## Context

- Module: `lms_order`, `lms_payment`
- Vietnamese regulation: Circular 219/2013/TT-BTC; online education services typically taxed at 10%
- Tax-inclusive is the VN consumer convention (giá đã bao gồm VAT)
- Related: TASK-130 handles the UI toggle between inclusive/exclusive display

## Acceptance Criteria

- [ ] `field_vat_rate` field on `lms_course` and `lms_membership` (select: 0%, 5%, 10%; default 10%)
- [ ] `VatCalculatorService` computes: `vat_amount = price_incl * rate / (1 + rate)` (inclusive) or `vat_amount = price_excl * rate` (exclusive)
- [ ] Order entity stores: `field_subtotal_excl_vat`, `field_vat_amount`, `field_total_incl_vat` (all in VND integer)
- [ ] Order summary UI shows line breakdown: subtotal, VAT (rate%), grand total
- [ ] Admin product edit: VAT rate selector with inline explanation of each rate category
- [ ] VAT rate change on a course does not retroactively alter completed order records
- [ ] Export report: order CSV includes VAT columns
- [ ] Unit test: 100,000 VND at 10% inclusive → subtotal 90,909 VND + VAT 9,091 VND = 100,000 VND
- [ ] Unit test: 100,000 VND at 10% exclusive → subtotal 100,000 VND + VAT 10,000 VND = 110,000 VND

## Technical Notes

- New service: `web/modules/custom/lms_order/src/VatCalculatorService.php`
- Methods: `calculateInclusive(int $price, float $rate): VatResult` and `calculateExclusive(int $price, float $rate): VatResult`
- `VatResult` value object: `{ subtotal, vat_amount, total }`
- All VND amounts stored as integers (no float storage); rounding: PHP `intdiv` or `round` with `PHP_ROUND_HALF_UP`
- Integration: called from `OrderManager::createOrder()` before persisting order totals
- Config: `lms_order.vat_settings` — default rate, inclusive/exclusive mode
- Coordinate with TASK-126 (invoice PDF) and TASK-130 (display toggle)

## Dependencies

- TASK-050 (commerce hardening)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
