# Task: Kernel Test Suite — G-FOUNDATION + G-CONTENT Entities

**ID:** TASK-075
**Unit:** — (cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Xây dựng PHPUnit kernel test suite cho các entity thuộc nhóm G-FOUNDATION và G-CONTENT: `lms_base`, `lms_course`, `lms_chapter`, `lms_lesson`. Suite này cover entity CRUD, field validation, và access control cho từng bundle. Mục tiêu coverage ≥80% theo line coverage trên các service class và entity hooks liên quan.

Test chạy trong môi trường DDEV với `phpunit --group kernel`. Kết quả được tích hợp vào CI pipeline (TASK-069) ở gate G2 — không merge nếu coverage xuống dưới ngưỡng hoặc có test fail.

## Acceptance Criteria
- [ ] `lms_base`: Kernel test CRUD cho User-profile entity + field validation (required fields, max length)
- [ ] `lms_course`: CRUD test cho Course entity; test field `status`, `visibility`, `price`; access control (Student không thể tạo Course)
- [ ] `lms_chapter`: CRUD + parent-course relationship validation; test orphan prevention (không thể tạo Chapter nếu Course không tồn tại)
- [ ] `lms_lesson`: CRUD + parent-chapter relationship; test `type` bundle field, `duration` validation
- [ ] Access control matrix test: Admin/Manager/Instructor/Student đúng với entity permissions
- [ ] Line coverage ≥80% trên service + entity hook classes của 4 module trên
- [ ] Test runner command documented trong `web/modules/custom/lms_base/tests/README.md`
- [ ] Tất cả test pass trong DDEV (`ddev phpunit --group kernel-foundation`)

## Technical Notes
- Modules: `lms_base`, `lms_course`, `lms_chapter`, `lms_lesson`
- Test namespace: `Drupal\Tests\lms_*/Kernel/`
- Base class: `Drupal\KernelTests\KernelTestBase`
- File locations:
  - `web/modules/custom/lms_base/tests/src/Kernel/`
  - `web/modules/custom/lms_course/tests/src/Kernel/`
  - `web/modules/custom/lms_chapter/tests/src/Kernel/`
  - `web/modules/custom/lms_lesson/tests/src/Kernel/`
- Access control: kiểm tra `lms_course.permissions.yml` và entity access handler
- Coverage report: `--coverage-html .agent/generated/coverage/kernel-foundation/`
- Cross-link với E03 TEST-001 (entity spec definitions)

## Dependencies
- TASK-067 (test catalog — xác định test IDs)
- TASK-069 (CI pipeline — integrate test gate G2)

## Definition of Done
- [ ] Code complete — tất cả test classes được tạo
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (`ddev phpunit --group kernel-foundation` = 0 failures)
- [ ] Coverage ≥80% confirmed
- [ ] CI pipeline chạy tự động khi có MR
