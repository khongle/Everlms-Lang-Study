# D03 — Story Spec: AI Pipeline Rollback & Compensation

**Story ID:** ai-rollback-compensation
**Story type:** cross-cutting
**Title:** AI Pipeline Rollback & Compensation — lms_ai course builder partial failure recovery
**Primary source:** `C-Design/C05-modules/lms_ai.md`
**Owning module(s):** lms_ai
**Service(s):** CourseBuilderService (lms_ai), CourseBuilderStateMachine (lms_ai), CourseBuilderWorker (lms_ai)
**Epic:** E-10 (Quality & Hardening)
**Contributes to Feature(s):** F-014 (AI Features)
**Design source(s):** `C-Design/C05-modules/lms_ai.md`
**Round:** N/A
**Driver:** NFR gap; G02 risk register (RISK-003 — Medium, AI generation lacks rollback/compensation); FIND-003, FIND-008
**Status:** SPEC
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `story: ai-rollback-compensation` — maps to draft TASK-032, TASK-051 (spike), TASK-054

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 2-3
**Splitting strategy:**
- S1 (spike + design): TASK-051 spike — design BuilderSession entity and state machine transitions; produce decision document
- S2: Implement BuilderSession entity + `CourseBuilderService::rollback()` + queue worker modification
- S3: Integration tests (inject failure at step N; verify rollback to clean state) + notification integration

## Scope (from primary source)

Add production-grade rollback and compensation to the `lms_ai` course builder pipeline. Currently, if entity creation fails mid-way (e.g., chapter 3 of 5 fails), partially-created entities are left orphaned in the database. There is no rollback, no compensation, and no way to resume or retry from a known checkpoint.

**Sub-scope:**

1. **BuilderSession entity** (new `lms_ai` content entity):
   - Fields: `session_id` (uuid), `status` (string: `generating|review|creating|done|failed|rolled_back`), `created_entity_ids` (serialized list of `{entity_type}:{id}` tuples), `provider` (openai|gemini|pollinations), `error_message` (text), `user_id` (entity_reference → user)
   - Persisted to DB so admin can view and manually trigger rollback for `failed` sessions

2. **CourseBuilderService modifications:**
   - Before creating each entity: record the BuilderSession; append each successfully created `{entity_type}:{id}` to `created_entity_ids`
   - `rollback(BuilderSession $session): void` — iterates `session->created_entity_ids` in reverse order, deletes each entity, handles missing entities gracefully (entity may not exist if creation failed mid-way)
   - On any exception during creation: call `rollback()`, set `session->status = 'failed'`, save session, notify user via `lms_notification`
   - After rollback completes: set `session->status = 'rolled_back'`, save session

3. **CourseBuilderWorker (QueueWorker) modifications:**
   - Current: queue worker runs `CourseBuilderService::build()` synchronously; on failure it silently fails
   - After: wrap in try/catch; on failure: call `CourseBuilderService::rollback()` + dispatch notification

4. **Manual rollback admin action:**
   - Add "Rollback" action to `/manage/ai/sessions` (or equivalent admin page)
   - Available only for sessions in `failed` status
   - Accessible to `manager` and `administrator` roles

5. **Feature flag gating:**
   - `FF_FEAT_AI_PROVIDER` gates the entire AI builder code path (existing flag from U-047/U-048)
   - Both ON and OFF paths must compile and have tests

6. **FIND-008 fix:** Gemini API key is passed via URL parameter (security risk) — must be moved to HTTP `Authorization: Bearer {key}` header. Fix in `GeminiProvider::makeRequest()`.

**Real implementation status (as-built):**
- `CourseBuilderService` exists and is functional for the happy path (OpenAI + Gemini full, Pollinations image-only)
- `CourseBuilderStateMachine` exists with state transitions
- `CourseBuilderWorker` QueueWorker exists
- **No rollback logic exists anywhere.** No `BuilderSession` entity. Partial failures leave orphaned entities.
- Gemini API key is passed via URL query parameter (FIND-008) — security risk; fix in this Story
- `FF_FEAT_AI_PROVIDER` feature flag is declared but rollback code path is entirely absent

## Source Documents

| Source | Why loaded |
|---|---|
| `C-Design/C05-modules/lms_ai.md` | CourseBuilderService, state machine, plugin architecture |
| `C-Design/C05-modules/lms_notification.md` | Notification dispatch after rollback completion |
| `C-Design/C05-modules/lms_course.md` | Course entity — primary entity created by builder; rollback target |
| `C-Design/C05-modules/lms_chapter.md` | Chapter entities — rollback targets |
| `C-Design/C05-modules/lms_lesson.md` | Lesson entities — rollback targets |
| `D00-conventions/php-drupal.md` | Entity creation/deletion patterns |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| If chapter N entity creation fails, chapters 1..N-1 and course are deleted (clean state) | RISK-003 | Non-Functional |
| BuilderSession entity records all created entity IDs during creation pipeline | RISK-003 | Cross-cutting |
| `rollback()` deletes all recorded entities even if some don't exist (partial state) | RISK-003 | Non-Functional |
| After rollback, BuilderSession status → `rolled_back` | RISK-003 | Cross-cutting |
| User notified of failure + rollback completion via notification system | RISK-003 | Cross-cutting |
| Admin can trigger manual rollback for any `failed` session | RISK-003 | Cross-cutting |
| FF_FEAT_AI_PROVIDER gates the code path; both ON/OFF paths compile and have tests | D00-conventions/ | Non-Functional |
| Gemini API key moved from URL parameter to Authorization header (FIND-008) | FIND-008 | Security |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_ai/src/Entity/BuilderSession.php` | Create | BuilderSession content entity (session_id, status, created_entity_ids, etc.) | TASK-032 | — |
| `web/modules/custom/lms_ai/src/Service/CourseBuilderService.php` | Modify | Add entity ID tracking + rollback() method | TASK-032 | — |
| `web/modules/custom/lms_ai/src/Service/CourseBuilderStateMachine.php` | Modify | Add `failed` and `rolled_back` state transitions | TASK-032 | — |
| `web/modules/custom/lms_ai/src/Plugin/QueueWorker/CourseBuilderWorker.php` | Modify | Wrap in try/catch; call rollback() on failure | TASK-032 | — |
| `web/modules/custom/lms_ai/src/Plugin/Provider/GeminiProvider.php` | Modify | Move API key from URL param to Authorization: Bearer header (FIND-008) | TASK-054 | — |
| `web/modules/custom/lms_ai/lms_ai.install` | Modify | Add schema for `lms_builder_session` table | TASK-032 | — |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| Story `hardening-security` — LmsException hierarchy used in rollback error path | Intra-Epic | this D03-stories/ |
| `lms_notification` — dispatch notification after rollback | Intra-project | `C-Design/C05-modules/lms_notification.md` |
| `FF_FEAT_AI_PROVIDER` feature flag — must exist and be registered | Intra-project | `C-Design/C05-modules/lms_ai.md` |
| DEC-FIND-008 — Gemini key-in-URL is confirmed risk; fix is unblocked | Pre-condition | FIND-008 |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| `D00-conventions/php-drupal.md` | All PHP files | PHPCS Drupal standard clean |
| Feature flag: both ON and OFF paths compile | CourseBuilderService, CourseBuilderWorker | G1 + G3 |
| Constructor injection (no `\Drupal::` in service classes) | CourseBuilderService | DI cleanup pass |

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission | Smoke evidence |
|---|---|---|---|---|---|
| `/manage/ai/sessions` admin page | BuilderSession status list | BuilderSession entity | `/manage/ai/sessions` | manager, administrator | D08 session log |

## Feature Flags Introduced or Read

| Flag ID | Introduced / Read | Purpose | Rollout plan link |
|---------|-------------------|---------|-------------------|
| FF_FEAT_AI_PROVIDER | Read | Gates entire AI builder code path including rollback | H05-feature-flags.md entry |

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-AIR-001 | Unit | BuilderSession entity saves and loads correctly | TASK-032 AC |
| T-AIR-002 | Unit | rollback() with N created entities deletes all N in reverse order | TASK-032 AC |
| T-AIR-003 | Unit | rollback() with entity 3 missing (partial failure) — continues, deletes 1+2 | TASK-032 AC |
| T-AIR-004 | Unit | Session status → `rolled_back` after rollback() completes | TASK-032 AC |
| T-AIR-005 | Integration | Inject failure at chapter 3 creation → chapters 1-2 + course deleted, session=failed, user notified | TASK-032 AC |
| T-AIR-006 | Integration | Manual rollback via admin action on `failed` session → entities deleted, session=rolled_back | TASK-032 AC |
| T-AIR-007 | Unit | FF_FEAT_AI_PROVIDER OFF — rollback code path skipped; normal Drupal entity behavior | TASK-032 AC |
| T-AIR-008 | Unit | GeminiProvider sends API key in Authorization header (not URL param) | TASK-054 AC |

## Gate Results (per Story, see A02 §3)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PENDING | — | 0/3 | — |
| G2 Lint | PENDING | — | 0/3 | — |
| G3 Tests | PENDING | Coverage: — | 0/3 | — |
| G4 Adversarial | PENDING | 7 categories: rollback completeness, partial delete, notification delivery | 0/3 | — |

## Notes

- The Gemini API key in URL parameter (FIND-008) is a security vulnerability — API keys in URLs appear in server access logs, browser history, and HTTP referrer headers. The fix (move to `Authorization: Bearer`) is straightforward and should be done in S2.
- `FF_FEAT_AI_PROVIDER` OFF path: when the flag is off, `CourseBuilderService` should not be invoked at all. The OFF path test verifies that no AI-related code runs and no BuilderSession is created.
- `Pollinations` provider is image-only; it generates avatar images, not course structure. Rollback for image generation is simpler (just delete the uploaded file entity).
- OpenAI provider: `gpt-4o` (from config). Gemini provider: `gemini-2.5-flash`. Both API keys are in plaintext config YAML — `«secret in config — see security risk»` per G02.
- `curriculum_suggestion` field in `lms_ai.engine.settings` contains ~758KB of cached AI response — this is a known config bloat risk (FIND from config-inventory). This Story does not address it, but note it in the session log.
