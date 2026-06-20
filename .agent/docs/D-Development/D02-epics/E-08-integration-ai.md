# D02 — Epic Spec: Integration & AI

**Epic ID:** E-08
**Features contributed to:** F-011 (AI Course Builder), F-012 (Zoom Live Class), F-013 (Analytics Reporting), F-014 (Mobile API)
**Owner:** SA (primary) + Agent_Implementer (executes Stories); CP4 signed off by SA
**Services affected:** lms_ai, lms_zoom, lms_analytics, lms_mobile_api
**Dependencies:** E-02 (Content), E-03 (Assessment)
**Estimated Duration:** 2 weeks
**Status:** IN_PROGRESS

## Purpose

Deliver external-service integrations and AI capabilities: AI provider plugin system (OpenAI / Gemini / Pollinations) with course-builder queue worker, Zoom meeting lifecycle management and Web SDK join flow, internal analytics reporting (course/user/quiz reports with CSV export), and the Flutter mobile student API (`/api/v1/`). Most core code is as-built; remaining work addresses interface gaps, rollback risk, AI Engine admin wizard hardening, and mobile API refactoring.

## Stories

| Story ID | Title | Type | Primary source | Owning module(s) | Service(s) | Feature/AC refs | Dependencies | Est. Effort | Priority |
|---|---|---|---|---|---|---|---|---|---|
| S-08-01 | AI provider plugin architecture (manager + 3 providers) | module | [C05-modules/lms_ai.md](../../C-Design/C05-modules/lms_ai.md) | lms_ai | lms_ai | F-011 | None | 0.5d (as-built) | Must |
| S-08-02 | AI interface completeness (generateText in interface) | maintenance | [C05-modules/lms_ai.md](../../C-Design/C05-modules/lms_ai.md) | lms_ai | lms_ai | F-011 | S-08-01 | 0.5d | Must |
| S-08-03 | AI course builder + queue worker | module | [C05-modules/lms_ai.md](../../C-Design/C05-modules/lms_ai.md) | lms_ai | lms_ai | F-011 | S-08-01 | 0.5d (as-built) | Must |
| S-08-04 | AI Engine admin wizard (AiEngineUserForm) | module | [C05-modules/lms_ai.md](../../C-Design/C05-modules/lms_ai.md) | lms_ai | lms_ai | F-011 | S-08-03 | 0.5d (as-built) | Should |
| S-08-05 | Zoom service + meeting lifecycle | module | [C05-modules/lms_zoom.md](../../C-Design/C05-modules/lms_zoom.md) | lms_zoom | lms_zoom | F-012 | S-08-01 | 0.5d (as-built) | Must |
| S-08-06 | Zoom Web SDK join + signature endpoint | module | [C05-modules/lms_zoom.md](../../C-Design/C05-modules/lms_zoom.md) | lms_zoom | lms_zoom | F-012 | S-08-05 | 0.5d (as-built) | Must |
| S-08-07 | Zoom AI summary (processSummaries VTT stub fix) | maintenance | [C05-modules/lms_zoom.md](../../C-Design/C05-modules/lms_zoom.md) | lms_zoom | lms_zoom | F-012 | S-08-05, S-08-01 | 1d | Should |
| S-08-08 | Analytics service + course/user/quiz reports | module | [C05-modules/lms_analytics.md](../../C-Design/C05-modules/lms_analytics.md) | lms_analytics | lms_analytics | F-013 | None | 0.5d (as-built) | Must |
| S-08-09 | Analytics CSV export | module | [C05-modules/lms_analytics.md](../../C-Design/C05-modules/lms_analytics.md) | lms_analytics | lms_analytics | F-013 | S-08-08 | 0.5d (as-built) | Must |
| S-08-10 | Mobile API auth (OTP + bearer token) | module | [C05-modules/lms_mobile_api.md](../../C-Design/C05-modules/lms_mobile_api.md) | lms_mobile_api | lms_mobile_api | F-014 | None | 0.5d (as-built) | Must |
| S-08-11 | Mobile API courses + learning endpoints | module | [C05-modules/lms_mobile_api.md](../../C-Design/C05-modules/lms_mobile_api.md) | lms_mobile_api | lms_mobile_api | F-014 | S-08-10 | 0.5d (as-built) | Must |
| S-08-12 | Mobile API: declare lms_quiz dependency + extract shared auth helper | maintenance | [C05-modules/lms_mobile_api.md](../../C-Design/C05-modules/lms_mobile_api.md) | lms_mobile_api | lms_mobile_api | F-014 | S-08-10, S-08-11 | 1d | Must |

## Build Order Within Epic

1. S-08-01 — AI provider plugin architecture (no intra-Epic dependencies)
2. S-08-05 — Zoom service + meeting lifecycle (no intra-Epic dependencies)
3. S-08-08 — Analytics service + reports (no intra-Epic dependencies)
4. S-08-10 — Mobile API auth (no intra-Epic dependencies)
5. S-08-02 — AI interface completeness (depends on S-08-01)
6. S-08-03 — AI course builder + queue worker (depends on S-08-01)
7. S-08-06 — Zoom Web SDK join (depends on S-08-05)
8. S-08-09 — Analytics CSV export (depends on S-08-08)
9. S-08-11 — Mobile API courses + learning (depends on S-08-10)
10. S-08-04 — AI Engine admin wizard (depends on S-08-03)
11. S-08-07 — Zoom AI summary stub fix (depends on S-08-05, S-08-01)
12. S-08-12 — Mobile API dependency + shared auth extract (depends on S-08-10, S-08-11)

S-08-01, S-08-05, S-08-08, S-08-10 have no intra-Epic dependencies and MAY be built in parallel.

## Acceptance Criteria (from B05)

| AC ID | Scenario | Feature | Contributing Story(ies) |
|-------|----------|---------|-------------------------|
| AC-801 | `plugin.manager.lms_ai_provider` discovers OpenAI, Gemini, Pollinations plugins | F-011 | S-08-01 |
| AC-802 | `AiProviderInterface` declares `generateText()` method | F-011 | S-08-02 |
| AC-803 | AI course builder creates draft course + queues lessons; queue worker populates lesson HTML | F-011 | S-08-03 |
| AC-804 | `AiEngineUserForm` AJAX pipeline runs end-to-end: users → curriculum → courses → chapters → lessons → SCORM | F-011 | S-08-04 |
| AC-805 | Zoom lesson presave creates meeting via Zoom API; field_zoom_meeting_id stored | F-012 | S-08-05 |
| AC-806 | `/api/lms/websdk/signature` returns HS256 JWT; ZAK token returned for host role | F-012 | S-08-06 |
| AC-807 | Zoom cron processSummaries fetches real VTT from Zoom API (not hardcoded simulation string) | F-012 | S-08-07 |
| AC-808 | Course report at `/admin/lms/reports/courses` shows real enrollment + revenue data | F-013 | S-08-08 |
| AC-809 | CSV export at `/admin/lms/reports/courses/export` returns UTF-8 BOM CSV | F-013 | S-08-09 |
| AC-810 | POST `/api/v1/oauth/token` returns Bearer token; GET `/api/v1/user/me` returns profile | F-014 | S-08-10 |
| AC-811 | POST `/api/v1/quiz/{id}/submit` grades all 8 question types; dispatches QuizPassedEvent on pass | F-014 | S-08-11 |
| AC-812 | `lms_mobile_api.info.yml` dependencies include `lms_quiz`; `getAuthenticatedUser()` extracted to shared trait/service | F-014 | S-08-12 |

## Integration Points (from B06)

| INT ID | Integration | Contributing Story(ies) | Feature Flag |
|--------|------------|--------------------------|-------------|
| INT-801 | lms_ai → OpenAI REST API (gpt-4o, DALL-E 3); «secret in config — see security risk» | S-08-01, S-08-03 | FF_FEAT_AI_PROVIDER |
| INT-802 | lms_ai → Gemini REST API (gemini-2.5-flash); «secret in config — see security risk» | S-08-01 | FF_FEAT_AI_PROVIDER |
| INT-803 | lms_ai → Pollinations image API (no key, free) | S-08-01 | FF_FEAT_AI_PROVIDER |
| INT-804 | lms_ai → Google Slides API via google_api_client contrib + google/apiclient; «secret in config — see security risk» | S-08-04 | FF_FEAT_AI_BUILDER |
| INT-805 | lms_zoom → Zoom Server-to-Server OAuth + Zoom REST API; «secret in config — see security risk» | S-08-05 | FF_FEAT_ZOOM |
| INT-806 | lms_zoom → Zoom Web SDK JS (firebase/php-jwt for JWT signature) | S-08-06 | FF_FEAT_ZOOM |
| INT-807 | lms_zoom → lms_ai (ZoomAiService calls openai provider) | S-08-07 | FF_FEAT_ZOOM |
| INT-808 | lms_mobile_api → lms_quiz GradingStrategyFactory (static call, undeclared dependency) | S-08-11, S-08-12 | — |
| INT-809 | lms_mobile_api → lms_wishlist storage (referenced in getCourseDetail, undeclared dependency) | S-08-11, S-08-12 | — |

## Test Requirements

- **Unit + integration tests per Story:** coverage >= 70% per Story
- **Cross-Story integration tests:** (1) AI course builder queues items; queue worker processes them and publishes lessons (S-08-01 + S-08-03). (2) Mobile quiz submit path: OTP auth → enroll → submit quiz → QuizPassedEvent (S-08-10 + S-08-11)
- **Cross-Story Integration Test Owner:** S-08-03 — owner of `E03-test-definitions/cross-story/E-08-integration-ai/`
- **Acceptance tests:** AC-801 through AC-812

## Feature Flag Plan

| Flag ID | Story | Description | Risk Level | Rollout Strategy |
|---------|-------|-------------|-----------|-----------------|
| FF_FEAT_AI_PROVIDER | S-08-01 | Enable AI provider plugin discovery + API calls | High | 0%→10%→50%→100% |
| FF_FEAT_AI_BUILDER | S-08-03, S-08-04 | Enable AI course builder and Engine wizard | High | After FF_FEAT_AI_PROVIDER stable at 50% |
| FF_FEAT_ZOOM | S-08-05, S-08-06 | Enable Zoom meeting create/join; requires live credentials | High | 0%→10%→100% |

## Implementation Status Notes

| Story | Status | Evidence |
|-------|--------|---------|
| S-08-01 | **as-built** | `AiProviderManager` + `OpenAiProvider`, `GeminiProvider`, `PollinationsProvider` all real; plugin discovery working |
| S-08-02 | **bug / target-state** | `generateText()` implemented by all 3 providers but NOT declared in `AiProviderInterface` — callers that rely on the interface cannot call it safely |
| S-08-03 | **as-built** | `CourseBuilderService::generateCourseStructure()` real; `CourseContentGenerator` queue worker real; no rollback on partial failure (RISK-003) |
| S-08-04 | **as-built** | `AiEngineUserForm` 2,361-line multi-step wizard with 14+ AJAX handlers all real; pipeline is step-by-step manual; no saga/compensation pattern |
| S-08-05 | **as-built** | `ZoomService` (OAuth + 5 API methods) + `ZoomMeetingManager` real; `hook_entity_presave/delete` sync meetings; State-based token cache |
| S-08-06 | **as-built (one stub)** | `ZoomController::joinMeeting()` + `generateSignature()` real; `ZoomAiService::processSummaries()` VTT download is hardcoded simulation string |
| S-08-07 | **target-state / planned** | VTT download in `processSummaries()` must be replaced with real `http_client` fetch from Zoom recordings API |
| S-08-08 | **as-built** | `AnalyticsService`, `CourseReportManager`, `UserReportManager`, `QuizAnalyticsManager` — all real DB queries; GA4 description in info.yml is misleading (no GA4 code exists) |
| S-08-09 | **as-built** | CSV export controllers real; UTF-8 BOM prepended; all 3 report types export |
| S-08-10 | **as-built** | `MobileAuthController` + `OtpService` real; custom bearer token via `keyvalue.expirable` (NOT Simple OAuth/JWT); no refresh token |
| S-08-11 | **as-built** | `MobileCourseController` + `MobileLearningController` real; quiz submit uses `GradingStrategyFactory` statically |
| S-08-12 | **target-state / planned** | `lms_quiz` not in `lms_mobile_api.info.yml` dependencies; `lms_wishlist` not declared; `getAuthenticatedUser()` copy-pasted in all 3 controllers — needs extraction |

## CP4 Readiness Criteria

- [ ] Every Story in this Epic has status: DONE
- [ ] G1-G4 passed for every Story
- [ ] Cross-Story integration tests within Epic passing
- [ ] D07 code-map delta complete for all files touched in this Epic
- [ ] E05 traceability complete for every contributed Feature
- [ ] E07 epic retrospective written (E07-retrospectives/)
- [ ] Feature flags registered for FF_FEAT_AI_PROVIDER, FF_FEAT_AI_BUILDER, FF_FEAT_ZOOM
- [ ] Open G4 Medium/Low issues logged in E02-issues/ (AI no-rollback RISK-003, missing interface method, mobile undeclared deps, Zoom VTT stub, GA4 description mismatch)
- [ ] Every Story that touched code/config/UI lists relevant `D00-conventions/` files in its D03 or D04 evidence
