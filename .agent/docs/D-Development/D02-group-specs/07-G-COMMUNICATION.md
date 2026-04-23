# D02 — Group Spec: G-COMMUNICATION

**Group ID:** G-COMMUNICATION
**Group Name:** Communication
**Order:** 7
**Units in Scope:** U-039 – U-044
**Dependencies:** G-FOUNDATION, G-CONTENT
**Estimated Sprints:** 1
**Status:** PENDING (3 DONE, 3 SPEC)

---

## Purpose

Cung cấp các kênh giao tiếp trong LMS: Notification system (6 types), Forum (topic + reply threads), Rating system (1-5 stars), notification dispatch service, forum UI + permissions, và email notification integration.

---

## Units

| Unit ID | Name | Status | Gate | Priority |
|---------|------|--------|------|----------|
| U-039 | Notification entity (6 types) + controller | DONE | G4 pass | P0 |
| U-040 | ForumTopic + ForumReply entities | DONE | G4 pass | P0 |
| U-041 | Rating entity (1-5 stars) | DONE | G4 pass | P0 |
| U-042 | Notification dispatch service | SPEC | — | P1 |
| U-043 | Forum thread UI + permissions | SPEC | — | P1 |
| U-044 | Email notification integration | SPEC | — | P1 |

---

## Build Order Within Group

1. U-039, U-040, U-041 — Entities (DONE, parallel)
2. U-042 — Dispatch service (depends on U-039)
3. U-043 — Forum UI (depends on U-040)
4. U-044 — Email integration (depends on U-039, U-042)

---

## Key Modules

- `lms_notification` — U-039, U-042, U-044
- `lms_forum` — U-040, U-043
- `lms_rating` — U-041

---

## Exit Criteria

- [x] Notification entity with 6 type bundles
- [x] ForumTopic + ForumReply with thread structure
- [x] Rating entity with 1-5 star validation
- [ ] Dispatch service: create + route notifications to users (U-042)
- [ ] Forum thread UI: create/reply/edit/delete with role-based permissions (U-043)
- [ ] Email integration: send notifications via Drupal mail system (U-044)

---

## Key Risks

- Forum permissions: need careful role mapping (student cannot delete others' posts).
- Email rate limiting: bulk notifications must be queued, not sent synchronously.
- U-044 depends on Drupal mail plugin configuration (SMTP contrib or core mail).

---

*End of 07-G-COMMUNICATION.md*
