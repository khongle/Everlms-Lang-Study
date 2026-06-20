---
module: lms_zoom
service: zoom
route-groups: [Admin, API, Public]
status: DONE
documentation-mode: as-built
---

# C05 — Module: LMS Zoom

**Directory:** `web/modules/custom/lms_zoom/`
**Responsibility:** Integrates Zoom's Server-to-Server OAuth API for live class sessions tied to `lms_lesson` entities with `field_type=zoom`, including meeting lifecycle management, Web SDK embedding, and AI-powered transcript summarization.

---

## Route Bindings

| Route Group | Prefix | Controller / Form | Paths |
|---|---|---|---|
| Admin | `/admin/config/lms` | `ZoomSettingsForm` | `/zoom` — Zoom credentials config |
| Public (authenticated) | `/zoom` | `ZoomController` | `/join/{meeting_id}` — full-screen Web SDK player |
| API (legacy `/api/lms`) | `/api/lms/websdk` | `ZoomController` | `/signature` [POST] — HS256 JWT for Web SDK |

---

## Public Interface

| Export | Type | Description |
|---|---|---|
| `lms_zoom.service` | Service (`ZoomService`) | Core Zoom REST API client: S2S OAuth token caching, meeting CRUD, ZAK token fetch |
| `lms_zoom.meeting_manager` | Service (`ZoomMeetingManager`) | Higher-level lifecycle: create or update meeting on lesson save; delete on lesson delete |
| `lms_zoom.ai_service` | Service (`ZoomAiService`) | AI-powered agenda generation and cron-driven transcript summarization |
| `logger.channel.lms_zoom` | Logger channel | Dedicated Drupal logger channel `lms_zoom` |

---

## Internal Structure

```
web/modules/custom/lms_zoom/
+-- lms_zoom.info.yml          # deps: lms_base, lms_lesson, lms_ai; lifecycle: stable
+-- lms_zoom.routing.yml       # 3 routes (settings, join_meeting, generate_signature)
+-- lms_zoom.services.yml      # 4 services
+-- lms_zoom.module            # 7 hooks (see Hook Implementations)
+-- lms_zoom.links.menu.yml    # Zoom Integration under system.admin_config
+-- lms_zoom.libraries.yml     # websdk (zoom-websdk-runner.js), countdown (zoom-countdown.js)
+-- src/
|   +-- Service/
|   |   +-- ZoomService.php           # S2S OAuth, token state cache, REST API methods
|   |   +-- ZoomMeetingManager.php    # syncMeeting(), deleteMeeting()
|   |   +-- ZoomAiService.php         # generateAgenda(), processSummaries()
|   +-- Controller/
|   |   +-- ZoomController.php        # joinMeeting(), generateSignature()
|   +-- Form/
|   |   +-- ZoomSettingsForm.php      # ConfigFormBase: account_id, client_id/secret, sdk_key/secret
|   +-- Plugin/
|       +-- Block/
|           +-- ZoomJoinBlock.php     # Block plugin id: lms_zoom_join_block
+-- templates/
|   +-- html--lms-zoom-web-player.html.twig
|   +-- page--lms-zoom-web-player.html.twig
|   +-- lms-zoom-join-block.html.twig
|   +-- lms-zoom-web-player.html.twig
+-- js/
    +-- zoom-websdk-runner.js
    +-- zoom-countdown.js
```

---

## Service Details

### `ZoomService` (as-built)

- **OAuth flow:** Server-to-Server (Account Credentials grant). Token cached in Drupal State keys `lms_zoom_access_token` + `lms_zoom_token_expires`. Refreshed automatically when expired.
- **Config:** `lms_zoom.settings` → `account_id`, `client_id`, `client_secret`, `sdk_key`, `sdk_secret`. All stored in config YAML — «secret in config — see security risk».
- **Methods:**

| Method | HTTP | Endpoint | Notes |
|---|---|---|---|
| `createMeeting(topic, startTime, durationMinutes, ?instructorEmail)` | POST | `/users/{userId}/meetings` | type=2 (scheduled). userId = instructorEmail or 'me' |
| `getMeeting(meetingId)` | GET | `/meetings/{id}` | |
| `deleteMeeting(meetingId)` | DELETE | `/meetings/{id}` | |
| `getRecordings(meetingId)` | GET | `/meetings/{id}/recordings` | |
| `getZakToken(userId)` | GET | `/users/{userId}/token?type=zak` | Host start token for Web SDK |

### `ZoomMeetingManager` (as-built)

- `syncMeeting(Lesson)`: Resolves instructor email via Lesson → Chapter → Course → `field_instructor`. Creates meeting if `field_zoom_meeting_id` is empty. Falls back to 'me' if instructor-specific creation fails.
- `deleteMeeting(Lesson)`: Deletes by `field_zoom_meeting_id`; errors caught and logged.

### `ZoomAiService` (mostly implemented — one stub)

- `generateAgenda(string $topic): ?string`: Calls OpenAI provider via `plugin.manager.lms_ai_provider` (`createInstance('openai')`); returns HTML agenda string.
- `processSummaries(): void`: Loads zoom-type lessons past 1 hour (batch 5). Skips lessons with `<!-- AI_SUMMARY_DONE -->` marker. Fetches recordings via `ZoomService::getRecordings()`, looks for TRANSCRIPT/TIMELINE file type. **STUB: VTT content is a hardcoded simulation string** (`"Simulated Transcript: ..."`) instead of a real HTTP download. Calls OpenAI to summarize; appends to `field_content` with `<!-- AI_SUMMARY_DONE -->` marker.

### `ZoomController` (as-built)

- `joinMeeting(string $meeting_id)`: Gets meeting from Zoom API; renders `lms_zoom_web_player` theme with meeting_id, passcode, user info; attaches `lms_zoom/websdk` library.
- `generateSignature(Request): JsonResponse`: POST endpoint. Resolves current user's role (host vs attendee) by traversing Lesson → Chapter → Course → `field_instructor`. If host, fetches ZAK token via `ZoomService::getZakToken()`. Generates HS256 JWT using `firebase/php-jwt`. **BUG: references wrong service id for sdk_key lookup** — see Known Issues.

---

## Hook Implementations

| Hook | What it does |
|---|---|
| `hook_entity_presave()` | If entity is Lesson with `field_type='zoom'`, calls `ZoomMeetingManager::syncMeeting()` |
| `hook_entity_delete()` | If entity is Lesson with `field_type='zoom'`, calls `ZoomMeetingManager::deleteMeeting()`; errors caught/logged |
| `hook_theme()` | Registers 4 templates: `html__lms_zoom_web_player`, `page__lms_zoom_web_player`, `lms_zoom_join_block`, `lms_zoom_web_player` |
| `hook_cron()` | Calls `ZoomAiService::processSummaries()` |
| `hook_form_lms_lesson_form_alter()` | Injects "Generate Zoom Agenda with AI" collapsible section with AJAX button into lesson edit form; only visible when `field_type=zoom` |
| `hook_theme_suggestions_html_alter()` | Adds `html__lms_zoom_web_player` suggestion for `lms_zoom.join_meeting` route |
| `hook_theme_suggestions_page_alter()` | Adds `page__lms_zoom_web_player` suggestion for `lms_zoom.join_meeting` route |
| `lms_zoom_agenda_ajax_callback()` | AJAX function: calls `ZoomAiService::generateAgenda()`, pushes result to `field_content` textarea |

---

## Plugins

### Block: `ZoomJoinBlock` (plugin id: `lms_zoom_join_block`)

- Context: `entity:lms_lesson` (optional).
- `build()`: Gets lesson from context or route param; verifies `field_type='zoom'`; renders `lms_zoom_join_block` theme with `meeting_id` and `schedule_time`. Attaches `lms_zoom/countdown` library.

---

## Dependencies

| Dependency | Type | Why |
|---|---|---|
| `lms_base` | Internal | Base entity traits, shared services |
| `lms_lesson` | Internal | Lesson entity (`field_zoom_meeting_id`, `field_type='zoom'`) |
| `lms_ai` | Internal | Provides `plugin.manager.lms_ai_provider` consumed by `ZoomAiService` |
| `firebase/php-jwt` | External (Composer) | HS256 JWT generation in `generateSignature()` |
| Zoom Web SDK JS | External (CDN/local) | Embedded via `lms_zoom/websdk` library |

---

## Config

Config object: `lms_zoom.settings`

| Key | Description |
|---|---|
| `account_id` | Zoom Server-to-Server OAuth account ID |
| `client_id` | OAuth client ID |
| `client_secret` | OAuth client secret — «secret in config — see security risk» |
| `sdk_key` | Web SDK key |
| `sdk_secret` | Web SDK secret — «secret in config — see security risk» |

Passwords only updated from form when non-empty on submit.

---

## Known Issues / Debt

| Ref | Severity | Description |
|---|---|---|
| BUG | High | `ZoomController::generateSignature()` references the **wrong service ID** when resolving `sdk_key` for JWT generation — confirmed live bug. Signature generation may fail or produce invalid tokens. |
| STUB | Medium | `ZoomAiService::processSummaries()` uses a hardcoded simulation string (`"Simulated Transcript: ..."`) instead of fetching the actual VTT file over HTTP. AI summaries are generated from fake transcripts. |
| SECURITY | Critical | `client_secret` and `sdk_secret` are stored in plaintext in committed config YAML — «secret in config — see security risk». |
| DEBT | Low | No fallback or circuit breaker if the Zoom REST API is unavailable; `hook_entity_presave()` failures propagate and can block lesson saves. |
| DEBT | Low | Meeting recording/transcript summarization requires Zoom Pro plan; not documented in settings form. |
