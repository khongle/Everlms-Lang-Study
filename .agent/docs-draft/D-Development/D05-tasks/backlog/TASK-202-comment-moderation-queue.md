# Task: Comment Moderation Queue

**ID:** TASK-202
**Unit:** — (lms_moderation service)
**Group:** G-COMMUNICATION
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Build a centralised moderation queue for user-generated text content: forum posts and replies (`lms_forum`), course ratings and reviews (`lms_rating`), and lesson comments (`lms_lesson`, see TASK-137). Moderators can approve, reject, or mark items as spam. An auto-publish toggle allows trusted users to bypass the queue. This task creates the new `lms_moderation` module with a `ModerationQueueItem` entity and supporting services.

## Acceptance Criteria

- [ ] **ModerationQueueItem entity** (content entity):
  - [ ] Fields: `source_type` (forum_post/rating/lesson_comment), `source_id`, `author_uid`, `content_snapshot` (text), `status` (pending/approved/rejected/spam), `moderator_uid`, `moderated_at`, `created`
  - [ ] Status field uses Drupal state_machine or hook-based transition validation
- [ ] **Queue UI** (`/manage/moderation/queue`):
  - [ ] Lists pending items; columns: Type, Author, Content preview, Submitted, Actions
  - [ ] Actions per row: Approve, Reject (requires reason), Mark as Spam
  - [ ] Bulk action: Approve selected, Reject selected, Mark as Spam selected
  - [ ] Filter: by source type, by date range
- [ ] **Auto-publish toggle:**
  - [ ] Admin configures trust-level threshold (e.g., users with 10+ approved posts auto-publish)
  - [ ] `AutoPublishService::check($uid)` returns bool; bypasses queue insertion for trusted users
  - [ ] Config stored in `lms_moderation.settings` (`auto_publish_threshold`)
- [ ] **Content integration:**
  - [ ] Hook `hook_lms_forum_post_presave` / event subscriber → creates queue item if not auto-publish; post saved with `status = unpublished` until approved
  - [ ] Same for `lms_rating` review entity and `lms_lesson` comment entity
  - [ ] On approval: source entity published; author notified via `lms_notification`
  - [ ] On rejection: source entity deleted or kept unpublished; author notified with reason
- [ ] PHPUnit test: queue insertion, approve transitions source to published, spam transitions source to deleted

## Technical Notes

- New module: `web/modules/custom/lms_moderation/`
- Entity class: `web/modules/custom/lms_moderation/src/Entity/ModerationQueueItem.php`
- Services: `ModerationQueueService` (insert/update items), `AutoPublishService` (trust check), `ModerationNotifier` (wraps lms_notification)
- Admin route: `lms_moderation.routing.yml` → `/manage/moderation/queue` (permission: `administer lms moderation`)
- Bulk form: extend `EntityListBuilder` with VBO-style checkboxes using Drupal Form API
- DB table: `lms_moderation_queue` (auto-created via entity schema)
- Avoid Drupal core Content Moderation module to keep dependency minimal

## Dependencies

- TASK-039 (lms_forum polish — forum post presave hook)
- TASK-137 (lesson comments — comment entity hook)
- TASK-206 (audit log — moderator actions logged here)

## Definition of Done

- [ ] Code complete — entity, services, queue UI, content integration hooks
- [ ] G1-G4 gates passed
- [ ] Forum post created by non-trusted user: appears in queue, not publicly visible
- [ ] Approve action: post becomes visible; author gets notification
- [ ] Tests written + passing
- [ ] No new debt introduced
