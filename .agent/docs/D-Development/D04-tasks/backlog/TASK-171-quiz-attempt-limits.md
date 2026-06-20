# Task: Quiz Attempt Limits, Cooldown & Lockout

**ID:** TASK-171
**Unit:** U-030 (lms_quiz)
**Group:** G-ASSESSMENT
**Priority:** P2
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Thêm giới hạn số lần làm bài và thời gian chờ giữa các lần vào `lms_quiz`. Hiện tại quiz cho phép làm lại không giới hạn — cần có cơ chế: (1) max attempts per user (sau đó locked); (2) cooldown giữa các lần (ví dụ: phải chờ 24h); (3) instructor override per-user để mở khóa ngoại lệ. Tính năng này đặc biệt quan trọng cho certification quiz (TASK-176).

State machine: `available → in_progress → completed → (cooldown) → available → ... → locked`. Khi locked, student thấy lý do rõ ràng và thông tin liên hệ instructor để xin mở khóa.

## Acceptance Criteria

- [ ] Config fields trên `lms_quiz`: `field_max_attempts` (integer, 0 = unlimited), `field_attempt_cooldown_hours` (integer, 0 = no cooldown)
- [ ] `QuizAccessService::canAttempt(int $uid, Quiz $quiz): QuizAccessResult` — trả `{ allowed: bool, reason: string, next_available_at: ?DateTime }`
- [ ] Reason codes: `NO_LIMIT`, `MAX_ATTEMPTS_REACHED`, `COOLDOWN_ACTIVE`, `INSTRUCTOR_OVERRIDE`
- [ ] UI: nếu `MAX_ATTEMPTS_REACHED` → show "Bạn đã hết lượt làm bài. Liên hệ giảng viên để được hỗ trợ."
- [ ] UI: nếu `COOLDOWN_ACTIVE` → show "Bạn có thể làm lại vào {next_available_at}" với countdown timer
- [ ] Instructor override: `/admin/lms/quiz-attempt-override` — form: select user + quiz, set extra attempts (integer) + reason note
- [ ] Override entity `QuizAttemptOverride`: uid, quiz_id, extra_attempts, granted_by, reason, created
- [ ] `QuizAccessService` kiểm tra override trước khi kết luận `MAX_ATTEMPTS_REACHED`
- [ ] Unit test: 3/3 attempts used, no override → locked; 3/3 with override +1 → allowed
- [ ] Unit test: cooldown 24h, last attempt 2h ago → blocked with correct next_available_at

## Technical Notes

- Module: `web/modules/custom/lms_quiz/`
- Service: `src/Service/QuizAccessService.php`
- Override entity: `src/Entity/QuizAttemptOverride.php`
- Cooldown check: `lastAttempt.completed_at + cooldown_hours * 3600 > now()`
- Attempt count: `SELECT COUNT(*) FROM lms_quiz_attempt WHERE uid=? AND quiz_id=? AND status=completed`
- Integration point: `QuizAttemptManager::startAttempt()` → check `QuizAccessService::canAttempt()` first
- Coordinate: TASK-172 (time accommodation — extra time, not extra attempts), TASK-176 (cert quiz uses max=1)

## Dependencies

- G-ASSESSMENT (quiz attempt infrastructure)
- TASK-076 (kernel test pattern)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
