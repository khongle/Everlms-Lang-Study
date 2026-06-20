# D03 — Story Spec: lms_zoom

**Story ID:** lms_zoom
**Story type:** module
**Title:** Zoom Live Class Integration — Meeting Lifecycle, Web SDK, and AI Summaries
**Primary source:** `C-Design/C05-modules/lms_zoom.md`
**Owning module(s):** lms_zoom
**Service(s):** lms_zoom.service (ZoomService), lms_zoom.meeting_manager (ZoomMeetingManager), lms_zoom.ai_service (ZoomAiService)
**Epic:** E-08 (Integration & AI)
**Contributes to Feature(s):** F-011 (Zoom Integration)
**Design source(s):** `../C05-modules/lms_zoom.md`
**Round:** N/A
**Driver:** SA directive
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_zoom`

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 3
**Splitting strategy:**
- S1: ZoomService (OAuth + API calls), ZoomMeetingManager (sync/delete), hook_entity_presave/delete, ZoomSettingsForm, services.yml, routing.yml, libraries.yml
- S2: ZoomController (joinMeeting, generateSignature), ZoomJoinBlock, templates, theme hooks, countdown library
- S3: ZoomAiService (generateAgenda, processSummaries), hook_cron, hook_form_lms_lesson_form_alter (AJAX agenda), hook_theme_suggestions

## Scope (from primary source)

Integrates Zoom live meetings with EverLMS lessons. When a lesson with `field_type = zoom` is saved, `hook_entity_presave` calls `ZoomMeetingManager::syncMeeting()` which creates or updates a Zoom scheduled meeting via Server-to-Server OAuth. On deletion, the Zoom meeting is removed. Learners join via `/zoom/join/{meeting_id}` which renders the Zoom Web SDK player. JWT signatures for the Web SDK are generated at `/api/lms/websdk/signature` using `firebase/php-jwt`. `ZoomAiService` provides AI-powered agenda generation (OpenAI) and post-meeting summary processing via `hook_cron`.

**Known bug (as-built):** `ZoomController::generateSignature()` references a wrong service ID when resolving the current user's ZAK token — it should use `lms_zoom.service` but the service ID reference in the controller is incorrect. This means host ZAK tokens may not be fetched correctly. Logged in [E02 issues: Zoom generateSignature() wrong service ID].

**One stub in ZoomAiService:** `processSummaries()` downloads VTT/transcript files as a hardcoded simulated string (`"Simulated Transcript: ..."`) instead of making a real HTTP request to the Zoom recordings API.

## Source Documents

| Source | Why loaded |
|---|---|
| `../C05-modules/lms_zoom.md` | Owning module design |
| `../../B-Requirements/B03-features/10-integrations.md` | Zoom integration requirements |
| `../../B-Requirements/B06-integration-requirements/` | Zoom API contract |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Zoom meeting auto-created/updated on lesson save (field_type=zoom) | B03-features/10-integrations | Acceptance |
| Zoom meeting deleted on lesson delete | B03-features/10-integrations | Acceptance |
| Learner can join Zoom meeting via Web SDK player at /zoom/join/{meeting_id} | B03-features/10-integrations | Acceptance |
| JWT signature generated server-side for Web SDK auth | B03-features/10-integrations | Acceptance |
| Host ZAK token fetched for instructor join | B03-features/10-integrations | Acceptance — bug open |
| AI agenda generation in lesson edit form | B03-features/10-integrations | Acceptance |
| Post-meeting AI summary appended to lesson content via cron | B03-features/10-integrations | Acceptance — VTT download is stub |
| Zoom credentials managed in admin settings form | B03-features/10-integrations | Acceptance |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_zoom/lms_zoom.info.yml` | Create | Module declaration (depends on lms_ai) | — | S1 |
| `web/modules/custom/lms_zoom/lms_zoom.services.yml` | Create | ZoomService, ZoomMeetingManager, ZoomAiService, logger | — | S1 |
| `web/modules/custom/lms_zoom/lms_zoom.routing.yml` | Create | settings, join_meeting, generate_signature routes | — | S1 |
| `web/modules/custom/lms_zoom/lms_zoom.libraries.yml` | Create | websdk + countdown libraries | — | S1 |
| `web/modules/custom/lms_zoom/lms_zoom.links.menu.yml` | Create | Admin config menu link | — | S1 |
| `web/modules/custom/lms_zoom/lms_zoom.module` | Create | hook_entity_presave/delete, hook_theme, hook_cron, hook_form_lms_lesson_form_alter, AJAX callback, theme suggestions | — | S1 |
| `web/modules/custom/lms_zoom/src/Service/ZoomService.php` | Create | Server-to-Server OAuth + createMeeting/getMeeting/deleteMeeting/getRecordings/getZakToken | — | S1 |
| `web/modules/custom/lms_zoom/src/Service/ZoomMeetingManager.php` | Create | syncMeeting(), deleteMeeting() | — | S1 |
| `web/modules/custom/lms_zoom/src/Service/ZoomAiService.php` | Create | generateAgenda(), processSummaries() (VTT stub) | — | S3 |
| `web/modules/custom/lms_zoom/src/Controller/ZoomController.php` | Create | joinMeeting(), generateSignature() (bug: wrong service ID) | — | S2 |
| `web/modules/custom/lms_zoom/src/Form/ZoomSettingsForm.php` | Create | ConfigFormBase for lms_zoom.settings | — | S1 |
| `web/modules/custom/lms_zoom/src/Plugin/Block/ZoomJoinBlock.php` | Create | Block plugin rendering join block with countdown | — | S2 |
| `web/modules/custom/lms_zoom/templates/html--lms-zoom-web-player.html.twig` | Create | Custom HTML template for meeting full-screen | — | S2 |
| `web/modules/custom/lms_zoom/templates/page--lms-zoom-web-player.html.twig` | Create | Custom page template for meeting | — | S2 |
| `web/modules/custom/lms_zoom/templates/lms-zoom-join-block.html.twig` | Create | Join block with countdown | — | S2 |
| `web/modules/custom/lms_zoom/templates/lms-zoom-web-player.html.twig` | Create | Embedded Zoom Web SDK player | — | S2 |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| lms_base | Intra-module | info.yml |
| lms_lesson | Intra-module | info.yml; hook_entity_presave/delete targets Lesson entities |
| lms_ai (AiProviderManager) | Intra-module | info.yml; ZoomAiService uses OpenAI plugin |
| firebase/php-jwt (Composer) | External | ZoomController::generateSignature() |
| Zoom Server-to-Server OAuth app credentials | External config | lms_zoom.settings |
| Zoom Web SDK JS | External CDN/local | lms_zoom/websdk library |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| Config-based OAuth token caching via Drupal State | ZoomService | As-built (lms_zoom_access_token, lms_zoom_token_expires) |
| Block plugin annotation | ZoomJoinBlock | As-built |
| logger.channel.* pattern | ZoomService, ZoomMeetingManager, ZoomAiService | As-built |

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission | Revision / moderation | Translation | Cache invalidation | Smoke evidence |
|---|---|---|---|---|---|---|---|---|
| `/admin/config/lms/zoom` | Zoom credentials | lms_zoom.settings config | Same path | administer site configuration | None | None | Config cache clear | As-built |
| `/zoom/join/{meeting_id}` | Web SDK meeting player | Zoom API meeting details | Lesson edit form | _user_is_logged_in | None | None | Uncached (real-time) | As-built |
| Lesson edit form (AI agenda) | AI-generated agenda | ZoomAiService::generateAgenda() | `/manage/lessons/{id}/edit` | administer lms_course | None | None | AJAX, no page cache | As-built |

**Hardcoded content exceptions:** None in module.

## Feature Flags Introduced or Read

None.

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-ZM-001 | Integration | Saving zoom lesson calls ZoomService::createMeeting() and stores meeting_id | Meeting create AC |
| T-ZM-002 | Integration | Deleting zoom lesson calls ZoomService::deleteMeeting() | Meeting delete AC |
| T-ZM-003 | Integration | GET /zoom/join/{meeting_id} returns Web SDK player with correct meeting data | Join AC |
| T-ZM-004 | Integration | POST /api/lms/websdk/signature returns valid HS256 JWT | Signature AC |
| T-ZM-005 | Integration | Instructor join receives ZAK token (after service ID bug fix) | Host ZAK AC |
| T-ZM-006 | Integration | ZoomAiService::generateAgenda() calls OpenAI and returns HTML agenda | Agenda AC |
| T-ZM-007 | Unit | hook_cron calls processSummaries() for completed meetings | Cron AC |
| T-ZM-008 | Unit | ZoomService token caching prevents re-fetch before expiry | OAuth cache AC |

## Gate Results (per Story)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Module installs; routes resolve; meeting sync functional | — | — |
| G2 Lint | PASS | No blocking violations | — | — |
| G3 Tests | PASS | Meeting create/delete, Web SDK join, signature all smoke-tested | — | — |
| G4 Adversarial | PASS | generateSignature() service ID bug logged (E02); VTT stub documented; no critical blockers | — | — |

## Notes

- **generateSignature() service ID bug (E02):** `ZoomController::generateSignature()` references a wrong service ID to resolve the ZAK token. The intended call is `$this->lmsZoomService->getZakToken($userId)`, but the service is injected under the wrong ID. This causes host join to fail silently or throw a ContainerException. Fix: correct service ID in the controller's `create()` factory or constructor.
- **VTT download stub:** `ZoomAiService::processSummaries()` uses a hardcoded simulated transcript string instead of downloading the VTT from `ZoomService::getRecordings()`. Real HTTP download via `$this->httpClient->get($vtt_url)` is the target-state fix.
- **Zoom credentials in committed config:** `account_id`, `client_id`, `client_secret`, `sdk_key`, `sdk_secret` in `lms_zoom.settings` YAML — `«secret in config — see security risk»`.
- **dependency on lms_ai:** `ZoomAiService` creates an OpenAI plugin instance via `plugin.manager.lms_ai_provider`. If lms_ai is disabled, Zoom AI features silently fail (logged). The module depends on lms_ai per info.yml.
