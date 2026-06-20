---
id: F-013
name: Suggested Modern Features
priority: Could
status: PROPOSED
parent-requirements: [BR-001]
owning_modules: [lms_badge, lms_notification, lms_analytics, lms_forum, lms_ai]
primary_source: mixed
story_anchors: []
---

# F-013 — Suggested Modern Features

## User story

As a **platform owner**, I want EverLMS to offer modern LMS capabilities comparable to leading platforms (Teachable, Thinkific, Moodle), so that the product remains competitive and learner-engaging.

## Description

This feature file catalogs suggested enhancements that would bring EverLMS up to or beyond the feature parity of top commercial LMS platforms. These capabilities are **not yet implemented** in the codebase — they represent the roadmap backlog for future development cycles. Where a partial foundation exists (e.g., `lms_badge` entity stubs, `lms_analytics` query engine), that is noted.

The suggestions are grouped by theme. Each group represents a potential Epic in the delivery backlog (see `D02-epics/`, see D).

## In scope

- Gamification: XP/level system, learning streaks, leaderboard, achievements
- Social learning: study groups, peer reviews, live Q&A, enhanced discussion boards
- Advanced content: interactive video, transcripts/subtitles, code playground
- Advanced analytics and personalization: learning paths, adaptive learning, skill assessment, predictive analytics
- Marketplace: instructor marketplace, revenue sharing, affiliate program, corporate/B2B
- Enhanced engagement: email drip campaigns, calendar integration, dark-mode toggle
- Enterprise: SSO (SAML/OAuth2), LTI, xAPI/Tin Can, multi-tenancy, API platform, webhooks, compliance training

## Out of scope

- Features already implemented as-built in other F-0xx files (AI course builder — F-014; homepage skins — F-015; wishlist/bundles/notes — F-016)
- AR/VR content — deferred indefinitely; no foundation exists
- Anthropic / Claude provider — draft mentioned it but no plugin is implemented; not in scope

## Acceptance summary

Because all items in this file are **target-state / planned**, there are no current acceptance criteria to pass. Full Given/When/Then specs will be written in `B05-acceptance-criteria/F-013.md` when each sub-feature is promoted to an approved Epic. Progress tracking lives in `E06-implementation-status.md`.

## Content ownership / editability

| Visible content family | Business owner | Editor role | Expected edit surface | Notes |
|---|---|---|---|---|
| Gamification badge content | Training manager | manager | `/manage/badges` (stub entity) | `lms_badge` entity exists; no award logic |
| Leaderboard / XP data | Platform | (computed) | No UI yet | planned |
| Email drip templates | Marketing | administrator | Drupal admin / planned module | not implemented |

## Proposed feature catalog

### 1. Gamification

| ID | Feature | Foundation in codebase | Status |
|---|---|---|---|
| GF-01 | XP and level system | `lms_badge.field_xp_reward` integer field exists | target-state |
| GF-02 | Learning streaks | None | target-state |
| GF-03 | Leaderboard (weekly/monthly rankings) | None | target-state |
| GF-04 | Achievements / unlockable badges | `lms_badge` + `lms_user_badge` entities exist, `field_criteria` JSON documented; **zero award logic** (see stub note below) | stub foundation only |
| GF-05 | Daily challenges (bonus XP mini-quizzes) | None | target-state |

> **lms_badge status (as-built):** The `lms_badge` and `lms_user_badge` entities exist with correct schemas. However, there are no services, routes, permissions, module hooks, or event subscribers. The `field_criteria` JSON schema (`{"type":"courses_completed","count":5}`) is documented in code comments but is never evaluated. This is a **near-stub** — entities only. See also risk register (`G02-risk-register.md`).

### 2. Social learning

| ID | Feature | Foundation | Status |
|---|---|---|---|
| SL-01 | Study groups (shared notes) | None | target-state |
| SL-02 | Peer reviews (student-to-student) | None | target-state |
| SL-03 | Live Q&A sessions with instructor | `lms_zoom` (live class); no dedicated Q&A mode | target-state |
| SL-04 | Enhanced discussion boards (upvoting, best answer) | `lms_forum` entity exists; `ForumController::viewTopic` has `"HARD DEBUG!"` bug — do not use in production | target-state (forum has bug) |
| SL-05 | Social sharing (progress / certificates) | None | target-state |

### 3. Advanced content

| ID | Feature | Foundation | Status |
|---|---|---|---|
| AC-01 | Interactive video (clickable hotspots, in-video quizzes) | H5P contrib module enabled | target-state |
| AC-02 | Video bookmarks and annotations | None | target-state |
| AC-03 | Auto-generated transcripts and subtitles | `lms_zoom` VTT fetch is stubbed (hardcoded simulation string) | stub foundation only |
| AC-04 | Podcast-style audio lessons | Mobile app supports `audioplayers`; no Drupal content type | target-state |
| AC-05 | AR/VR (WebXR) | None | deferred / Wont |
| AC-06 | In-browser code playground | None | target-state |
| AC-07 | Collaborative whiteboard for live sessions | None | target-state |

### 4. Analytics and personalization

| ID | Feature | Foundation | Status |
|---|---|---|---|
| AP-01 | Learning paths (guided course sequences → career goal) | None | target-state |
| AP-02 | Adaptive learning (auto-adjust difficulty) | None | target-state |
| AP-03 | Skill assessment (pre-course) | `lms_quiz` supports pre-course quizzes in structure; no dedicated skill-assessment flow | target-state |
| AP-04 | Predictive analytics (drop-off risk) | `lms_analytics` has completion-rate data; no ML layer | target-state |
| AP-05 | Engagement heat maps | None | target-state |
| AP-06 | A/B testing (layouts / pricing) | None | target-state |

### 5. Marketplace

| ID | Feature | Foundation | Status |
|---|---|---|---|
| MK-01 | Instructor marketplace (self-register to sell courses) | `instructor` role exists; no marketplace workflow | target-state |
| MK-02 | Revenue sharing (platform % + instructor %) | None | target-state |
| MK-03 | Affiliate / referral program | None | target-state |
| MK-04 | Gift cards | None | target-state |
| MK-05 | Corporate / B2B (bulk enrollment, team management) | None | target-state |
| MK-06 | White-label for enterprise clients | `open_switcher` impersonation utility + mobile `SwitchDomainScreen` provide partial foundation; see F-015 | partial — see F-015 |

### 6. Enhanced engagement

| ID | Feature | Foundation | Status |
|---|---|---|---|
| EG-01 | AI-driven notification timing | `lms_notification` in-app channel works; email and push are stubs | target-state |
| EG-02 | Email drip campaigns (welcome / nudge / completion) | None | target-state |
| EG-03 | In-app contextual messages | `lms_notification` in-app only | target-state |
| EG-04 | Calendar integration (Google/Apple sync) | `lms_zoom` lesson schedule data available; no iCal export | target-state |
| EG-05 | Dark mode toggle (per-user preference) | `lms_theme` glassmorphism/neon/luxury/creative are always dark; `light_mode.css` injected for other skins; no per-user toggle | target-state |

### 7. Enterprise

| ID | Feature | Foundation | Status |
|---|---|---|---|
| EF-01 | SSO (SAML / OAuth2) | `social_auth` contrib present; no SAML configured; social_auth.settings shows no active providers | partial stub |
| EF-02 | LTI integration | None | target-state |
| EF-03 | xAPI / Tin Can | None | target-state |
| EF-04 | Multi-tenancy (separate instances per org) | None | target-state |
| EF-05 | Full REST/GraphQL API platform | `/api/v1/*` mobile API exists; no general-purpose API | partial |
| EF-06 | Webhooks (event-driven external notifications) | **No webhook endpoints exist anywhere** — Stripe webhook signature verification is a TODO, not implemented | target-state (noted risk) |
| EF-07 | Compliance training (mandatory with deadline enforcement) | `lms_membership` expiry field exists; no compliance workflow | target-state |

## Dependencies (requirement-level only)

| Type | Reference | Reason |
|---|---|---|
| Feature | F-014 | AI quiz generation partially overlaps with ACB capabilities in lms_ai |
| Feature | F-015 | White-label enterprise (MK-06) overlaps with branding/SwitchDomain |
| Feature | F-016 | Email drip (EG-02) foundation lives in lms_notification enhancements |

## Open questions

- [ ] Which gamification items (GF-01 through GF-05) should be promoted to an approved Epic first?
- [ ] Should `lms_badge` award logic be completed as part of GF-04, or rebuilt from scratch?
- [ ] Instructor marketplace (MK-01/MK-02) requires payment gateway webhook support — currently a known gap (no webhook endpoints). Resolve dependency ordering before approving marketplace Epic.
- [ ] SSO: SAML or OAuth2 only? Which contrib module (simplesamlphp_auth vs social_auth)?
