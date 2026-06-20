# Task: Vietnamese Business Tax ID (Mã Số Thuế) Field

**ID:** TASK-155
**Unit:** U-020 (lms_order)
**Group:** G-COMMERCE
**Priority:** P1
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Bổ sung trường Mã số thuế (MST) doanh nghiệp vào form checkout cho đơn hàng B2B. Tại Việt Nam, doanh nghiệp mua khóa học cần xuất hóa đơn VAT theo MST, bắt buộc có MST hợp lệ mới phát hành được HĐĐT. MST có hai định dạng: 10 chữ số (doanh nghiệp) và 13 chữ số (chi nhánh/đơn vị trực thuộc). Cần validate checksum theo thuật toán của Bộ Tài chính.

Trường MST chỉ xuất hiện khi user tích vào checkbox "Mua hàng cho công ty / Xuất hóa đơn VAT". Khi có MST, tên công ty và địa chỉ công ty trở thành bắt buộc. MST được hiển thị trên hóa đơn PDF (TASK-126) và HĐĐT (TASK-154).

## Acceptance Criteria

- [ ] Checkbox "Xuất hóa đơn VAT cho doanh nghiệp" trong checkout form — khi check, hiện thêm 3 fields
- [ ] Field `field_buyer_tax_id` (string, max 13 chars) trên `lms_order` entity
- [ ] Field `field_buyer_company_name` (string) và `field_buyer_company_address` (string) trên `lms_order`
- [ ] `TaxIdValidator::validate(string $taxId): bool` — kiểm tra: độ dài 10 hoặc 13, toàn chữ số, checksum mod 10 hợp lệ
- [ ] Form validation: nếu checkbox checked → 3 fields bắt buộc, MST phải pass `TaxIdValidator`
- [ ] Error message bằng tiếng Việt: "Mã số thuế không hợp lệ. Vui lòng kiểm tra lại."
- [ ] MST mask trong admin order view: hiển thị đầy đủ (không mask)
- [ ] Unit test: `TaxIdValidatorTest` — valid 10-digit, valid 13-digit, invalid length, invalid checksum
- [ ] MST field exported trong order CSV (TASK-174 pattern)

## Technical Notes

- Module: `web/modules/custom/lms_order/`
- Validator: `src/Validator/TaxIdValidator.php`
- MST checksum: sum digits * [2,3,4,5,6,7,8,9,10,2,3,4] → remainder × 11 → check digit
- Form alter hook: `lms_order_form_checkout_form_alter()` — conditional fields via `#states`
- Schema update: `lms_order.install` — add 3 new fields to `{lms_order}` table
- Fields available to TASK-154 `InvoiceService` và TASK-126 PDF template
- Tham chiếu thuật toán: Tổng cục Thuế — "Cấu trúc mã số thuế" tại https://www.gdt.gov.vn

## Dependencies

- TASK-050 (order form base)
- TASK-127 (VAT — hiển thị VAT amount cùng MST)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
