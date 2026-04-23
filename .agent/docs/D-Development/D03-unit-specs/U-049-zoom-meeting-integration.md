# D03 — Unit Spec: Zoom Meeting Integration

**Unit ID:** U-049
**Group:** G-INTEGRATION
**Title:** Zoom meeting integration
**Status:** SPEC
**Priority:** P1
**Assigned:** unassigned
**Feature Flag:** FF_FEAT_ZOOM

---

## Scope

Full Zoom integration: create meetings for live lessons, generate join links for enrolled students, handle Zoom webhooks (meeting started/ended), and store meeting recordings link. Builds on ZoomService stub (U-045).

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Service/ZoomService.php` | Modify | lms_zoom |
| `src/Controller/ZoomWebhookController.php` | Create | lms_zoom |
| `src/Form/ZoomMeetingForm.php` | Create | lms_zoom |
| `src/Entity/ZoomMeeting.php` | Create | lms_zoom |
| `templates/zoom-meeting-info.html.twig` | Create | lms_zoom |
| `lms_zoom.routing.yml` | Modify | lms_zoom |
| `config/install/lms_zoom.settings.yml` | Create | lms_zoom |

---

## Interfaces / Services

- `ZoomService::createMeeting(string $topic, \DateTime $startTime, int $duration): ZoomMeeting`
  - Uses Zoom API v2 with OAuth2 Server-to-Server (NOT JWT — deprecated)
- `ZoomService::generateJoinLink(ZoomMeeting $meeting, int $userId): string`
- `ZoomWebhookController`: handles `meeting.started`, `meeting.ended`, `recording.completed`
- OAuth token cached in Drupal cache (TTL = token expiry)

---

## Acceptance Criteria

- [ ] Instructor can create a Zoom meeting from lesson edit form
- [ ] Enrolled students see join link on lesson page (active when meeting is live)
- [ ] Meeting create/join uses OAuth2 Server-to-Server credentials from config (not hardcoded)
- [ ] Zoom webhook `recording.completed` saves recording URL to lesson
- [ ] Join link is personalized per user (user_name + email passed to Zoom)
- [ ] Feature flag `FF_FEAT_ZOOM` gates all Zoom UI and routes
- [ ] Webhook endpoint verifies Zoom webhook token before processing

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Mock Zoom API responses |
| G4 Adversarial | PENDING | Webhook token verification test |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-045 | ZoomService stub — extend |
| U-007 | Lesson entity — Zoom meeting linked to live lesson type |

---

**Feature Flag:** FF_FEAT_ZOOM
**Note:** Zoom JWT auth deprecated March 2023. Use Server-to-Server OAuth. Credentials stored in `lms_zoom.settings:account_id`, `client_id`, `client_secret`.

*End of U-049-zoom-meeting-integration.md*
