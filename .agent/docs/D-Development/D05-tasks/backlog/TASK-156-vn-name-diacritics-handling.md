# Task: Vietnamese Name Diacritics Handling

**ID:** TASK-156
**Unit:** — (cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P2
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Xử lý đúng tên người Việt Nam trong toàn bộ hệ thống. Tên Việt có đặc thù: 3–4 từ (họ + đệm + tên), dấu diacritics Unicode tổ hợp (NFD) và dựng sẵn (NFC) có thể lẫn lộn trong DB, sort theo "họ tên" (họ trước) thay vì "firstName lastName" kiểu phương Tây. Vấn đề hiện tại: tên "Nguyễn Hoàng Hải Anh" bị cắt/lưu sai encoding khi đến từ form.

Giải pháp chuẩn hóa: tất cả tên normalize về NFC (UTF-8 composed) trước khi lưu. Display sort dùng `họ + tên` (word[0] + last word). DB collation đã là `utf8mb4_unicode_ci` (MariaDB 10.11); cần đảm bảo tất cả field definitions dùng đúng collation. `LmsHelper` trong `lms_base` thêm các static methods tiện ích.

## Acceptance Criteria

- [ ] `LmsHelper::normalizeName(string $name): string` — Unicode NFC normalize, trim whitespace thừa, collapse spaces
- [ ] `LmsHelper::sortableName(string $fullName): string` — trả `{lastName} {firstName...}` để dùng làm sort key
- [ ] `LmsHelper::splitVietnameseName(string $full): array` — trả `['family' => ..., 'middle' => ..., 'given' => ...]`
- [ ] Tất cả `field_full_name` / `field_display_name` trên User entity qua `normalizeName()` khi save (hook_entity_presave)
- [ ] Admin user list sort theo `sortableName` thay vì raw `field_full_name`
- [ ] PDF/cert PROTO-005: dùng tên đã normalize — kiểm tra diacritics render đúng font
- [ ] DB schema: xác nhận `lms_*` name fields dùng `utf8mb4_unicode_ci` collation (check via `SHOW FULL COLUMNS`)
- [ ] Unit test: "Nguyễn Hoàng Hải Anh" → family "Nguyễn", given "Hải Anh"; NFD input → NFC output
- [ ] Unit test: sort key "Nguyễn Hoàng Hải Anh" → "Nguyễn Hải Anh"

## Technical Notes

- Module: `web/modules/custom/lms_base/src/LmsHelper.php`
- NFC normalize: `\Normalizer::normalize($name, \Normalizer::NFC)` — PHP `intl` extension required
- Hook: `lms_base_entity_presave(EntityInterface $entity)` — filter chỉ User entity
- Sort field: có thể dùng computed field hoặc update `field_sort_name` khi presave
- Cert template (PROTO-005): tham chiếu TASK-158 để font có đầy đủ VN glyphs
- Không thay đổi raw data đã lưu trong DB — chỉ normalize tại write-path

## Dependencies

- TASK-158 (Vietnamese fonts — cert rendering)
- None (cross-cutting utility)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
