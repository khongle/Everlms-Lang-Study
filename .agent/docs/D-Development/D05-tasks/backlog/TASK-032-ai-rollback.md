# Task: AI Rollback & Compensation Logic

**ID:** TASK-032
**Unit:** U-063
**Group:** G-HARDENING
**Priority:** P1
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Triển khai rollback và compensation logic cho AI-generated content. Khi AI course builder tạo course/chapter/lesson thất bại giữa chừng, cần cleanup entities đã tạo.

## Acceptance Criteria
- [ ] Rollback partial course creation (xóa entities đã tạo nếu builder fail)
- [ ] Compensation cho AI content generation errors
- [ ] Transaction-like behavior cho multi-entity AI operations
- [ ] User notification khi rollback xảy ra
- [ ] Log chi tiết cho AI operation failures
- [ ] Retry mechanism cho transient AI provider errors

## Technical Notes
- Module: `lms_ai`
- Liên quan: `lms_content_tools`
- Approach: Track created entity IDs, delete on failure
- Feature flag: FF_FEAT_AI_PROVIDER

## Dependencies
- TASK-012 (AI provider plugin — DONE)
- TASK-013 (AI course builder — DONE)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] No orphan entities từ failed AI operations
