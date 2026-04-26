# Task: Kernel Test Suite — G-ASSESSMENT (Quiz, Question, Result)

**ID:** TASK-076
**Unit:** — (cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Xây dựng PHPUnit kernel test suite cho toàn bộ G-ASSESSMENT group: `lms_quiz`, `lms_question` (8 bundles), `lms_quiz_result`. Trọng tâm là kiểm tra từng grading path của 8 loại question handler — mỗi handler cần ít nhất 2 test case (correct answer + incorrect answer). Cross-link với E03 TEST-002.

Suite này phải cover cả quiz lifecycle: tạo quiz → attach questions → student submit → grading → kết quả được lưu vào `lms_quiz_result`. Đặc biệt chú ý xử lý partial credit cho câu hỏi multi-choice và essay grading pending state.

## Acceptance Criteria
- [ ] `lms_quiz`: CRUD test; test `time_limit`, `max_attempts`, `passing_score` validation
- [ ] `lms_question` — 8 bundle handlers tested:
  - [ ] `multiple_choice` — single correct answer grading
  - [ ] `multiple_response` — partial credit grading
  - [ ] `true_false` — binary grading
  - [ ] `fill_blank` — case-insensitive match + exact match modes
  - [ ] `matching` — pair scoring
  - [ ] `ordering` — sequence scoring
  - [ ] `short_answer` — pending/manual grade state
  - [ ] `essay` — pending/manual grade state, instructor assignment
- [ ] `lms_quiz_result`: score persistence, `passed` flag calculation, `attempt_number` increment
- [ ] `max_attempts` enforcement test (BlockedException on exceed)
- [ ] Cross-link: test IDs annotated `@group TEST-002`
- [ ] Coverage ≥80% trên grading service classes
- [ ] Pass trong DDEV (`ddev phpunit --group kernel-assessment`)

## Technical Notes
- Modules: `lms_quiz`, `lms_question`, `lms_quiz_result`
- Test paths:
  - `web/modules/custom/lms_quiz/tests/src/Kernel/`
  - `web/modules/custom/lms_question/tests/src/Kernel/`
  - `web/modules/custom/lms_quiz_result/tests/src/Kernel/`
- Grading handlers nằm tại `lms_question/src/Plugin/QuestionHandler/` (8 plugin classes)
- `lms_quiz_result` entity có fields: `score`, `passed`, `attempt_number`, `answers` (serialized)
- Essay/ShortAnswer cần test trạng thái `PENDING_REVIEW` và transition sang `GRADED`
- Coverage: `--coverage-html .agent/generated/coverage/kernel-assessment/`

## Dependencies
- TASK-067 (test catalog — TEST-002 spec)
- TASK-069 (CI gate G2)

## Definition of Done
- [ ] Code complete — 8 handler test classes + Quiz/Result tests
- [ ] G1-G4 gates passed
- [ ] 0 failures trong DDEV kernel test run
- [ ] Coverage ≥80% on grading plugin layer
- [ ] E03 TEST-002 annotated + linked
