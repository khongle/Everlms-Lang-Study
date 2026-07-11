# F-023 Acceptance Criteria - Content tools & seeding

## Core criteria
1. **Given** một fresh install, **when** admin chạy `lms:import` (từ `.agent/generated/*.json`), **then** catalog đã tài liệu hóa (course/quiz/user/v.v.) được tạo.
2. **Given** content đã seed, **when** `lms:clean` chạy, **then** content được sinh ra bị xóa mà không làm hỏng install.
3. **Given** `lms:generate-demo`, **when** được chạy, **then** course/chapter/lesson demo dạng procedural được tạo.
4. **Given** UI `SeedInputForm`, **when** admin submit seed input, **then** input được xử lý bởi `ContentToolsManager`.

## Evidence expected
- Module: [lms_content_tools](../../C-Design/C05-modules/lms_content_tools.md); inputs SRC-10/11.
- Test: import→count assertion so với live DB (40 course/600 lesson); clean idempotency test (E03).
