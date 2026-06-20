# Task: Author E03 Test Definitions for All 16 Acceptance Criteria Files

**ID:** TASK-067
**Unit:** — (Quality docs: E03-test-definitions + E05-traceability)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Hiện tại `E-Quality/E03-test-definitions/` chỉ có TEST-001..005 covering một phần nhỏ acceptance criteria. Cần author TEST-006..TEST-NN covering TẤT CẢ Must và Should ACs từ 16 file trong `B-Requirements/B05-acceptance-criteria/AC-01..16-*.md`. Mỗi TEST file theo đúng format hiện tại. Sau đó update `E05-traceability.md` matrix để link mỗi AC → TEST-ID → TASK-ID.

Đây là prerequisite để CI pipeline (TASK-069) có thể verify traceability coverage và block merge khi có AC không có test.

## Acceptance Criteria
- [ ] Đọc tất cả 16 AC files trong `B-Requirements/B05-acceptance-criteria/` và extract Must + Should criteria
- [ ] Tạo TEST-006..TEST-NN (số lượng TBD based on AC count, ước tính 30–50 test files)
- [ ] Mỗi TEST file có: Test ID, AC reference, Description, Preconditions, Steps, Expected Result, Automation tag (unit/integration/e2e/manual)
- [ ] Format nhất quán với TEST-001..005 hiện tại
- [ ] `E05-traceability.md` matrix updated: AC → TEST-ID → TASK-ID → Status (Passing/Pending/Failing)
- [ ] 100% Must ACs có ít nhất 1 TEST file
- [ ] Should ACs có TEST file hoặc explicit "deferred to v2" note trong traceability
- [ ] Cross-reference check: không có TEST file nào reference AC không tồn tại

## Technical Notes
- Input files: `B-Requirements/B05-acceptance-criteria/AC-01-*.md` đến `AC-16-*.md`
- Output: `E-Quality/E03-test-definitions/TEST-006.md` .. `TEST-0NN.md`
- Existing format reference: `E-Quality/E03-test-definitions/TEST-001.md` (read trước khi write)
- Traceability matrix: `E-Quality/E05-traceability.md`
- Automation tag guide: `unit` = PHPUnit; `integration` = Behat; `e2e` = Playwright (TASK-068); `manual` = human QA
- Phân bổ ước tính: AC-01 Course (10 tests), AC-02 Lesson (8), AC-03 Quiz (12), AC-04 Progress (6), AC-05 Commerce (10), AC-06..16 mỗi file ~5 tests

## Dependencies
- TEST-001..005 tồn tại (baseline format)
- TASK-068 (E2E setup) nên hoàn thành trước để tag automation correctly; nhưng TASK-067 có thể chạy trước và mark `e2e` tags as "pending automation"

## Definition of Done
- [ ] Tất cả TEST-006..TEST-NN files tạo thành công
- [ ] E05 traceability matrix updated với 100% Must AC coverage
- [ ] No broken AC references trong TEST files
- [ ] SA review traceability matrix và sign-off
- [ ] Unblocks TASK-069 (CI pipeline traceability gate)
