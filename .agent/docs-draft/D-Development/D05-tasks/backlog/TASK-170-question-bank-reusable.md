# Task: Question Bank — Reusable Questions Across Quizzes

**ID:** TASK-170
**Unit:** U-031 (lms_question)
**Group:** G-ASSESSMENT
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Tách rời câu hỏi khỏi quiz để có thể tái sử dụng cùng một câu hỏi trong nhiều quiz khác nhau. Hiện tại câu hỏi (`lms_question`) đã là entity độc lập, nhưng chưa có giao diện "ngân hàng câu hỏi" (question bank) để instructor browse, filter, và import vào quiz. Cần thêm: tag system (topic, difficulty, type), search/filter UI, và cơ chế "include questions from bank" vào quiz theo filter criteria.

Instructor tạo câu hỏi một lần trong bank, gắn tags, rồi quiz có thể "kéo" câu hỏi từ bank theo filter (ví dụ: "20 câu ngẫu nhiên từ topic=Grammar, difficulty=medium"). Kết hợp với TASK-169 (randomization), câu hỏi pool sẽ đến từ bank query.

## Acceptance Criteria

- [ ] Taxonomy: `question_topic` và `question_difficulty` (easy/medium/hard) vocabulary, apply cho `lms_question`
- [ ] `lms_question` entity: thêm `field_topic` (entity_ref taxonomy, multiple), `field_difficulty` (list select), `field_is_shared` (boolean — có publish ra bank hay không)
- [ ] Admin bank UI: `/admin/lms/question-bank` — Views page với filter: type, topic, difficulty, keyword search, course filter
- [ ] Bulk operations trong bank: tag update, duplicate, archive
- [ ] Quiz edit: tab "Add from Bank" — open modal với same filter UI, checkbox select, "Import N questions"
- [ ] Quiz entity: thêm `field_bank_query` (JSON) lưu filter criteria để auto-pull khi pool-based
- [ ] `QuestionBankService::query(array $filters): QuestionInterface[]` — filterable, cacheable
- [ ] `QuestionBankService::importToQuiz(Quiz $quiz, array $questionIds)` — tạo relationship records
- [ ] Question usage report: hiển thị "Câu hỏi này được dùng trong 3 quiz" trong bank UI
- [ ] Unit test: `QuestionBankServiceTest` — filter by topic + difficulty returns correct subset
- [ ] Kernel test: import 5 questions from bank → quiz has 5 question refs

## Technical Notes

- Module: `web/modules/custom/lms_question/` (extend entity + add bank UI)
- Question–Quiz relationship: `lms_quiz_question` table: `quiz_id`, `question_id`, `weight`, `source` (manual|bank)
- `QuestionBankService`: `src/Service/QuestionBankService.php` trong `lms_question`
- Filter cache: cache tags `lms_question_list` — invalidate on question save/delete
- Modal: Drupal dialog AJAX — `/admin/lms/question-bank/select-modal?quiz={id}`
- Coordinate: TASK-169 (pool size draws from bank), TASK-175 (analytics on bank questions)

## Dependencies

- G-ASSESSMENT (question entity infrastructure)
- TASK-169 (question randomization — pool source)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
