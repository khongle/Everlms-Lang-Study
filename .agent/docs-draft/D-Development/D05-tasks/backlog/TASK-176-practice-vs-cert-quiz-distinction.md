# Task: Practice Quiz vs Certification Quiz Distinction

**ID:** TASK-176
**Unit:** U-030 (lms_quiz)
**Group:** G-ASSESSMENT
**Priority:** P2
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Phân biệt rõ ràng hai loại quiz trong hệ thống: Practice Quiz (luyện tập, không tính điểm chính thức) và Certification Quiz (bài thi lấy chứng chỉ, nghiêm túc). Hai loại này có behavior hoàn toàn khác nhau về: tính vào progress, ảnh hưởng đến cert eligibility, attempt limits, proctoring, và feedback mode.

Hiện tại tất cả quiz được xử lý như nhau — cần thêm field flag `field_quiz_type` và dùng nó để gate các behavior khác nhau. Practice quiz: unlimited attempts, immediate answer feedback, không count vào cert; Certification quiz: max 1-3 attempts, no answer feedback, proctoring opt-in, count vào cert.

## Acceptance Criteria

- [ ] Field `field_quiz_type` (list select) trên `lms_quiz` entity: options `practice` (default) | `certification`
- [ ] Khi `field_quiz_type = practice`: immediate answer feedback sau mỗi câu, unlimited attempts (override `field_max_attempts`), không ảnh hưởng cert eligibility, không show trong cert progress
- [ ] Khi `field_quiz_type = certification`: no answer feedback until reviewed by instructor (hoặc auto-release sau N ngày), `field_max_attempts` cố định (không bị override bởi student), count vào cert eligibility check
- [ ] Course completion logic: chỉ tính certification quizzes khi check cert eligibility (đủ điểm các bài thi chính thức)
- [ ] Practice quiz: show "Luyện tập" badge màu xanh; Certification quiz: show "Thi chứng chỉ" badge màu đỏ
- [ ] Admin quiz create/edit form: quiz type selector nổi bật ở đầu form với tooltip giải thích
- [ ] Cert quiz default settings: `field_max_attempts = 3`, `field_attempt_cooldown_hours = 24`, `field_proctoring_enabled = FALSE` (instructor bật thủ công nếu muốn)
- [ ] Migration: tất cả existing quizzes trong DB → type = `practice` (safe default)
- [ ] Unit test: practice quiz attempt → does NOT affect `CertEligibilityService::isEligible()`
- [ ] Unit test: cert quiz passed → updates cert eligibility flag

## Technical Notes

- Module: `web/modules/custom/lms_quiz/`
- Field: `field_quiz_type` — Drupal list (text) field, configurable per quiz
- `CertEligibilityService` (lms_quiz hoặc lms_certificate): filter `WHERE field_quiz_type = certification`
- Feedback suppression: cert quiz submission → return score only, no per-question breakdown; store in `lms_quiz_attempt.field_feedback_released` (boolean)
- Feedback release: instructor action "Release Feedback" → set `field_feedback_released = TRUE`, notify students
- Migration: `lms_quiz.post_update.php` — `PostUpdate::setDefaultQuizType()`
- Coordinate: TASK-171 (attempt limits default for cert quiz), TASK-173 (proctoring — opt-in cert), TASK-175 (analytics apply to both types)

## Dependencies

- G-ASSESSMENT (quiz entity base)
- TASK-171 (attempt limits — cert quiz config)
- TASK-173 (proctoring — cert quiz integration)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
