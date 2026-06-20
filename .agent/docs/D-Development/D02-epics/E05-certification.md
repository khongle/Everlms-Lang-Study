# E05 - Certification & badges

**Status:** partial (certificates done; **badges blocked** — BLK-003)

## Goal
Auto-issue PDF certificates on completion; award gamification badges.

## Scope
- Modules: [lms_certificate](../../C-Design/C05-modules/lms_certificate.md) (done), [lms_badge](../../C-Design/C05-modules/lms_badge.md) (blocked)
- Entities: lms_certificate_template, lms_user_certificate, lms_badge, lms_user_badge
- Feature: F-007 (done), F-008 (stub) · Workflow: WF-003

## Stories
- [E05S01](../D03-stories/E05S01-lms_certificate.md), [E05S02](../D03-stories/E05S02-lms_badge.md)

## Dependencies
E04.

## Open work
- ⚠️ Badge award trigger absent (BLK-003 / R-003) — implement event subscriber to grant `lms_user_badge`. Task [E05S02T01](../D04-tasks/todo/E05S02T01-implement-badge-award-subscriber.md).
