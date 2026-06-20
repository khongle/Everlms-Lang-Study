# ISSUE-001 — API Input Validation Gaps

| Field | Value |
|-------|-------|
| ID | ISSUE-001 |
| Severity | **High** |
| Status | OPEN |
| Source | RISK-001 (A-Project-Information/A03-project-context.md) |
| Related Unit | U-057 — API input validation — all write endpoints |
| Related Decision | DEC-001 — per-route vs global middleware |
| Found | 2026-04-15 |
| Owner | Unassigned |

## Mô tả

Các write endpoint (POST/PATCH/DELETE) trong lms_course, lms_quiz, lms_progress, lms_payment chưa có validation layer đồng nhất. Input nhận trực tiếp từ request mà không qua schema validation, dẫn đến nguy cơ:

- Inject dữ liệu không hợp lệ vào DB
- Bypass business rule (ví dụ: enroll vào course đã xoá)
- Mass assignment nếu entity field không được whitelist

## Cách tái hiện (Reproduction)

1. Gửi PATCH request đến `/api/lms/course/{id}` với field bất kỳ trong body
2. Không có HTTP 422 / validation error trả về
3. Giá trị được lưu vào DB ngay cả khi sai kiểu hoặc vi phạm constraint

## Root Cause Hypothesis

Drupal form validation chỉ chạy khi submit qua Form API. Các route xử lý REST/JSON request trong controller (`lms_course`, `lms_progress`) sử dụng `$request->getContent()` trực tiếp mà không gọi constraint validator. Chưa có global event subscriber validate input.

## Tác động

- **Security** (G4-Security): SQL injection / type confusion thông qua Drupal entity API
- **Data Integrity** (G4-Data Integrity): Orphan records, sai state machine nếu truyền status bất hợp lệ

## Phương án khắc phục đề xuất

- Implement Symfony Validator constraint per entity bundle
- Hoặc: global `RequestSubscriber` validate schema JSON trước khi routing (DEC-001)
- Unit hardening: U-057

## Traceability

- Risk: RISK-001
- Unit spec: D03-unit-specs/ (U-057)
- Test sẽ cover: TEST-001, TEST-004 trong E03-test-definitions/
