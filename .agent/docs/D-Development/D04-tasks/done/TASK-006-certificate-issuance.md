# Task: Certificate Issuance Service

**ID:** TASK-006
**Unit:** U-036
**Group:** G-CERTIFICATE
**Priority:** P0
**Estimate:** 10h
**Assigned:** unassigned
**Status:** DONE (85%)
**Completed:** 2026-04-15

## Audit Notes
- CertificateManager service: `checkAndIssueCertificate()` — checks duplicate, finds template, generates code, saves entity ✅
- CertificateEventListener subscribes to `lms_quiz.passed` → traverses Quiz→Lesson→Chapter→Course ✅
- Admin dashboard, CRUD, revoke form, 5 permissions ✅
- **Known gaps**: Only triggers on quiz pass (no CourseCompletedSubscriber). Eligibility check only verifies "not already issued" (no score threshold/course % check). Certificate code uses weak `md5(uid-cid-time)`.

## Description
Service tự động cấp certificate khi student đạt điều kiện (quiz pass / course completion). Listen events và trigger issuance.

## Acceptance Criteria
- [ ] Auto-issue certificate khi QuizCompletedEvent (passed=true) hoặc CourseCompletedEvent
- [ ] Kiểm tra điều kiện cấp (template active, chưa cấp cho user này)
- [ ] Tạo UserCertificate entity với unique verify code
- [ ] Dispatch CertificateIssuedEvent (cho notification)
- [ ] Không duplicate issue cho cùng user + course/quiz

## Technical Notes
- Module: `lms_certificate`
- Service: `CertificateIssuer::issue()`, `::checkEligibility()`
- Event subscribers: `QuizCompletedSubscriber`, `CourseCompletedSubscriber`
- Generate unique verification code (UUID or hash)

## Dependencies
- U-035 (Certificate entities) ✅ DONE
- U-022 (Progress service) ✅ DONE

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Unit tests cho eligibility check
- [ ] Integration test cho auto-issuance flow
