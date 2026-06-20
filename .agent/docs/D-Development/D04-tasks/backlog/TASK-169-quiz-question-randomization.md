# Task: Quiz Question Randomization per Attempt

**ID:** TASK-169
**Unit:** U-030 (lms_quiz)
**Group:** G-ASSESSMENT
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Thêm tính năng xáo trộn câu hỏi và đáp án vào `lms_quiz` để tăng tính chống gian lận trong bài kiểm tra. Hỗ trợ hai chiều xáo trộn: (1) thứ tự câu hỏi trong quiz; (2) thứ tự các đáp án trong từng câu hỏi. Ngoài ra, hỗ trợ chọn ngẫu nhiên N câu từ pool M câu (ví dụ: quiz có 50 câu nhưng mỗi lần chỉ lấy 20 câu ngẫu nhiên).

Randomization phải reproducible: dùng seed = `{user_id}-{quiz_id}-{attempt_number}` để cùng một lần thi luôn hiển thị cùng thứ tự (cần thiết khi user refresh trang, instructor review, hoặc xử lý tranh chấp điểm). Seed dùng PHP `srand()` hoặc `shuffle()` với deterministic seed.

## Acceptance Criteria

- [ ] Config fields trên `lms_quiz` entity: `field_shuffle_questions` (boolean), `field_shuffle_answers` (boolean), `field_question_pool_size` (integer, 0 = all)
- [ ] `QuizRandomizerService::getQuestionsForAttempt(Quiz $quiz, QuizAttempt $attempt): array` — trả mảng question IDs theo thứ tự đã xáo trộn
- [ ] Seed: `crc32("{uid}-{quiz_id}-{attempt_id}")` → deterministic
- [ ] Nếu `field_question_pool_size` > 0 và < total questions: chọn N câu ngẫu nhiên từ pool trước, rồi shuffle
- [ ] Thứ tự câu hỏi và đáp án lưu vào `lms_quiz_attempt` entity (`field_question_order` JSON, `field_answer_order` JSON)
- [ ] Khi user navigate back/forward trong quiz → render đúng thứ tự đã lưu (không re-randomize)
- [ ] Grading service đọc `field_answer_order` để map đáp án user submit về đúng answer ID
- [ ] Admin quiz edit: preview "Sample question order" với seed = 1 để kiểm tra
- [ ] Unit test: cùng seed → cùng thứ tự; khác seed → thứ tự khác (probabilistic check trên 10 seeds)
- [ ] Unit test: pool size 20/50 → đúng 20 câu được chọn; reproducible

## Technical Notes

- Module: `web/modules/custom/lms_quiz/`
- Service: `src/Service/QuizRandomizerService.php`
- Algorithm: `mt_srand($seed); shuffle($array)` — PHP Mersenne Twister
- `field_question_order`: JSON array of question IDs, stored on `lms_quiz_attempt`
- `field_answer_order`: JSON map `{question_id: [answer_id, ...]}`, stored on `lms_quiz_attempt`
- Integration: `QuizAttemptManager::startAttempt()` → call randomizer → store order
- Existing G-ASSESSMENT question handlers (8 types) phải compatible — check each handler reads answer order from attempt
- Coordinate: TASK-170 (question bank — pool source), TASK-171 (attempt limits)

## Dependencies

- G-ASSESSMENT (question handler infrastructure)
- TASK-076 (kernel test pattern for quiz)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
