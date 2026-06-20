# Task: Vietnamese Phone Number Format Validation

**ID:** TASK-157
**Unit:** — (cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P2
**Estimate:** 3h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Thêm validation và auto-format số điện thoại Việt Nam vào `lms_base` field constraint. Số điện thoại VN hiện tại gồm 10 chữ số bắt đầu bằng `0` (đầu số đã chuyển đổi 11 → 10 số từ 2018). Người dùng nhập nhiều định dạng: `0912345678`, `+84912345678`, `84912345678` — cần chấp nhận tất cả, normalize về `0xxxxxxxxx` khi lưu, display theo định dạng đẹp `0912 345 678`.

Áp dụng constraint này cho: User profile `field_phone`, checkout form, instructor profile. Sử dụng Drupal Constraint/Validator API để tái sử dụng dễ dàng qua nhiều entity.

## Acceptance Criteria

- [ ] `VnPhoneConstraint` annotation và `VnPhoneConstraintValidator` trong `lms_base`
- [ ] Accept: `0xxxxxxxxx` (10 digits, starts 0), `84xxxxxxxxx` (11 digits), `+84xxxxxxxxx` (12 chars)
- [ ] Reject: 9-digit cũ, non-numeric chars (trừ `+` đầu), đầu số không hợp lệ (không phải 03x/05x/07x/08x/09x)
- [ ] `VnPhoneHelper::normalize(string $phone): string` → trả `0xxxxxxxxx`
- [ ] `VnPhoneHelper::format(string $phone): string` → trả `0xxx xxx xxx`
- [ ] Field `field_phone` trên User entity tự động normalize tại `entity_presave`
- [ ] Error message VI: "Số điện thoại không hợp lệ. Vui lòng nhập số điện thoại Việt Nam 10 chữ số."
- [ ] Unit test: 5 valid inputs (3 formats + 2 edge), 4 invalid inputs
- [ ] Unit test: `format()` → `0912 345 678`

## Technical Notes

- Module: `web/modules/custom/lms_base/`
- Constraint: `src/Plugin/Validation/Constraint/VnPhoneConstraint.php`
- Validator: `src/Plugin/Validation/Constraint/VnPhoneConstraintValidator.php`
- Helper: `src/VnPhoneHelper.php`
- Valid prefixes (Viettel/Vinaphone/Mobifone/Gmobile/Reddi/Itelecom): 032–039, 056, 058, 070, 076–079, 081–086, 089, 090–099
- Regex pattern: `^(0|84|\+84)(3[2-9]|5[6|8]|7[0|6-9]|8[0-9]|9[0-9])\d{7}$`
- Hook normalize trong `lms_base_entity_presave()` — filter User entity

## Dependencies

- None

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
