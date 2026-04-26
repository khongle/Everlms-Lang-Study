# Task: User Report Functionality

**ID:** TASK-203
**Unit:** — (lms_moderation)
**Group:** G-COMMUNICATION
**Priority:** P1
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Allow any authenticated user to flag content (forum post, course review, lesson comment) as inappropriate. A report form collects the reason and optional context. Submitted reports land in the moderation queue (TASK-202) for moderator review. Repeated reports on the same item from the same user are deduplicated.

## Acceptance Criteria

- [ ] **Report form:**
  - [ ] Accessible via "Report" link/icon on every forum post, course review, and lesson comment
  - [ ] Reason dropdown: Spam, Harassment, Copyright violation, Off-topic, Other
  - [ ] Optional free-text "Additional context" field (max 500 chars)
  - [ ] Submit button; form submitted via AJAX (no page reload)
  - [ ] Success message: "Your report has been submitted. Our team will review it shortly."
- [ ] **Deduplication:**
  - [ ] A single user can only submit one report per content item
  - [ ] Subsequent attempt shows "You have already reported this content"
  - [ ] Multiple different users reporting same item aggregates into one queue entry; report count displayed to moderator
- [ ] **Queue integration:**
  - [ ] Report creates or increments `UserReport` entity in `lms_moderation`
  - [ ] If report count exceeds threshold (configurable, default 3), item auto-escalated to top of queue with `escalated` flag
  - [ ] Moderator queue (TASK-202) displays report count + reasons summary per item
- [ ] **`UserReport` entity fields:** `target_type`, `target_id`, `reporter_uid`, `reason`, `context`, `created`
- [ ] Guests (anonymous) cannot report (redirect to login)
- [ ] PHPUnit test: report submitted, dedup on second submit by same user, escalation threshold trigger

## Technical Notes

- Module: `lms_moderation` (extends TASK-202)
- Entity: `UserReport` content entity, `lms_user_reports` table
- Report link: Twig macro `{{ lms_moderation_report_link(entity_type, entity_id) }}` injected via hook_node_view / hook_comment_view / custom render element
- AJAX endpoint: `POST /api/lms/moderation/report` (JSON)
- Escalation service: `ReportEscalationService::checkThreshold($target_type, $target_id)` called after each report save
- Report count stored as field on `ModerationQueueItem` (TASK-202 entity); `UserReport` references the queue item

## Dependencies

- TASK-202 (moderation queue — reports surface here)
- TASK-039 (lms_forum — forum post render hooks)
- TASK-137 (lesson comments — comment render hooks)

## Definition of Done

- [ ] Code complete — UserReport entity, AJAX endpoint, report link macro, escalation service
- [ ] G1-G4 gates passed
- [ ] Report link appears on forum posts, reviews, lesson comments
- [ ] Deduplication enforced server-side (not just client-side)
- [ ] Tests written + passing
- [ ] No new debt introduced
