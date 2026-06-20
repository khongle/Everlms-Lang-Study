# D02 — Epic Spec: Communication

**Epic ID:** E-07
**Features contributed to:** F-008 (Forum), F-009 (Rating), F-010 (Notification)
**Owner:** SA (primary) + Agent_Implementer (executes Stories); CP4 signed off by SA
**Services affected:** lms_notification, lms_forum, lms_rating
**Dependencies:** E-01 (Foundation), E-02 (Content)
**Estimated Duration:** 1 week
**Status:** IN_PROGRESS

## Purpose

Deliver learner communication channels: in-app notifications (6 event types), course discussion forums with threaded replies and best-answer marking, and course ratings/reviews (1–5 stars with moderation). Three entity families are already as-built; the remaining work is completing email/push notification channels, fixing broken rating routes, and removing debug artifacts from forum code.

## Stories

| Story ID | Title | Type | Primary source | Owning module(s) | Service(s) | Feature/AC refs | Dependencies | Est. Effort | Priority |
|---|---|---|---|---|---|---|---|---|---|
| S-07-01 | Notification entity + in-app channel | module | [C05-modules/lms_notification.md](../../C-Design/C05-modules/lms_notification.md) | lms_notification | lms_notification | F-010 | None | 0.5d (as-built) | Must |
| S-07-02 | Notification email channel | module | [C05-modules/lms_notification.md](../../C-Design/C05-modules/lms_notification.md) | lms_notification | lms_notification | F-010 | S-07-01 | 2d | Must |
| S-07-03 | Notification push-channel foundation | module | [C05-modules/lms_notification.md](../../C-Design/C05-modules/lms_notification.md) | lms_notification | lms_notification | F-010 | S-07-01 | 2d | Should |
| S-07-04 | Forum entities + topic/reply CRUD | module | [C05-modules/lms_forum.md](../../C-Design/C05-modules/lms_forum.md) | lms_forum | lms_forum | F-008 | None | 0.5d (as-built) | Must |
| S-07-05 | Forum dead-code cleanup + debug removal | maintenance | [C05-modules/lms_forum.md](../../C-Design/C05-modules/lms_forum.md) | lms_forum | lms_forum | F-008 | S-07-04 | 0.5d | Must |
| S-07-06 | Rating entity + RatingForm + admin list | module | [C05-modules/lms_rating.md](../../C-Design/C05-modules/lms_rating.md) | lms_rating | lms_rating | F-009 | None | 0.5d (as-built) | Must |
| S-07-07 | Rating missing-route fix | maintenance | [C05-modules/lms_rating.md](../../C-Design/C05-modules/lms_rating.md) | lms_rating | lms_rating | F-009 | S-07-06 | 1d | Must |

## Build Order Within Epic

1. S-07-01 — Notification entity + in-app channel (no dependencies; as-built verification)
2. S-07-04 — Forum entities + CRUD (no dependencies; as-built verification)
3. S-07-06 — Rating entity + RatingForm (no dependencies; as-built verification)
4. S-07-05 — Forum dead-code cleanup (depends on S-07-04)
5. S-07-07 — Rating missing-route fix (depends on S-07-06)
6. S-07-02 — Notification email channel (depends on S-07-01)
7. S-07-03 — Notification push-channel foundation (depends on S-07-01)

S-07-01, S-07-04, and S-07-06 verify existing as-built code and MAY be run in parallel.

## Acceptance Criteria (from B05)

| AC ID | Scenario | Feature | Contributing Story(ies) |
|-------|----------|---------|-------------------------|
| AC-701 | In-app notification created on quiz pass | F-010 | S-07-01 |
| AC-702 | Unread count returned by `/api/lms/notifications/unread-count` | F-010 | S-07-01 |
| AC-703 | Email sent when user opts in for email channel | F-010 | S-07-02 |
| AC-704 | Broadcast form sends announcement to all active users | F-010 | S-07-01 |
| AC-705 | Forum topic created; reply increments reply_count | F-008 | S-07-04 |
| AC-706 | Owner can mark a reply as best answer; topic set to resolved | F-008 | S-07-04 |
| AC-707 | `ForumController` dead code removed; no `throw new \Exception("HARD DEBUG!")` in codebase | F-008 | S-07-05 |
| AC-708 | Debug `addMessage("FRESH CONTROLLER LOADED!")` removed from `LmsForumController::viewTopic()` | F-008 | S-07-05 |
| AC-709 | Student submits rating; appears in admin list with approved=FALSE | F-009 | S-07-06 |
| AC-710 | Admin toggles approve/feature via `/workspace/ratings/{id}/{action}`; no RouteNotFoundException | F-009 | S-07-07 |
| AC-711 | `lms_rating.collection`, `lms_rating.toggle_approve`, `lms_rating.toggle_featured` routes defined and functional | F-009 | S-07-07 |

## Integration Points (from B06)

| INT ID | Integration | Contributing Story(ies) | Feature Flag |
|--------|------------|--------------------------|-------------|
| INT-701 | lms_forum ForumReplyForm → lms_notification.manager::dispatch('forum_reply') | S-07-04, S-07-01 | — |
| INT-702 | lms_notification NotificationEventSubscriber → QuizPassedEvent (lms_quiz) | S-07-01 | — |
| INT-703 | Notification email channel → Drupal mail system (plugin.manager.mail) | S-07-02 | FF_FEAT_NOTIF_EMAIL |
| INT-704 | Notification push channel → FCM (planned, layer F — not yet materialized) | S-07-03 | FF_FEAT_NOTIF_PUSH |

## Test Requirements

- **Unit + integration tests per Story:** coverage >= 70% per Story
- **Cross-Story integration tests:** Verify forum reply triggers in-app notification (S-07-04 + S-07-01 wiring via `lms_notification.manager`)
- **Cross-Story Integration Test Owner:** S-07-04 — owner of `E03-test-definitions/cross-story/E-07-communication/`
- **Acceptance tests:** AC-701 through AC-711 above

## Feature Flag Plan

| Flag ID | Story | Description | Risk Level | Rollout Strategy |
|---------|-------|-------------|-----------|-----------------|
| FF_FEAT_NOTIF_EMAIL | S-07-02 | Enable email notification delivery via Drupal mail queue | Medium | 0%→10%→50%→100% |
| FF_FEAT_NOTIF_PUSH | S-07-03 | Enable push notification channel (FCM foundation) | High | 0%→10% only until FCM fully integrated |

## Implementation Status Notes

| Story | Status | Evidence |
|-------|--------|---------|
| S-07-01 | **as-built** | `NotificationManager::createInAppNotification()` fully implemented; in-app channel real; `NotificationBellBlock`, `NotificationController`, `BroadcastForm`, `NotificationPreferencesForm` all real |
| S-07-02 | **target-state / planned** | `NotificationManager::dispatch()` email branch has `TODO: Implement Drupal queue creation for mail` comment only; email not sent |
| S-07-03 | **stub** | Push branch in `dispatch()` is log-only; no FCM SDK integrated |
| S-07-04 | **as-built** | `lms_forum_topic` + `lms_forum_reply` entities real; `LmsForumController` fully implemented with access control handlers |
| S-07-05 | **target-state / planned** | `ForumController::viewTopic()` has `throw new \Exception("HARD DEBUG!")` — dead code but dangerous; `LmsForumController::viewTopic()` has debug `addMessage()` left in production |
| S-07-06 | **as-built** | `lms_rating` entity real; `RatingForm` and `RatingManagerController::executeAction()` functional |
| S-07-07 | **bug** | `RatingListBuilder` and `RatingController` reference 3 missing routes: `lms_rating.collection`, `lms_rating.toggle_approve`, `lms_rating.toggle_featured` — causes `RouteNotFoundException`; no `.module` file so `lms_admin_ratings` theme not registered in this module |

## CP4 Readiness Criteria

- [ ] Every Story in this Epic has status: DONE
- [ ] G1-G4 passed for every Story
- [ ] Cross-Story integration tests within Epic passing
- [ ] D07 code-map delta complete for all files touched in this Epic
- [ ] E05 traceability complete for every contributed Feature
- [ ] E07 epic retrospective written (E07-retrospectives/)
- [ ] Feature flags registered for FF_FEAT_NOTIF_EMAIL, FF_FEAT_NOTIF_PUSH
- [ ] Open G4 Medium/Low issues logged in E02-issues/ (rating missing routes, forum debug artifacts)
- [ ] Every Story that touched code/config/UI lists relevant `D00-conventions/` files in its D03 or D04 evidence
