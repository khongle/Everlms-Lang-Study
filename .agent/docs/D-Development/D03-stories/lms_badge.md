# D03 — Story Spec: lms_badge

**Story ID:** lms_badge
**Story type:** module
**Title:** Gamification Badges — Entity Scaffold (No Award Logic)
**Primary source:** `C-Design/C05-modules/lms_badge.md`
**Owning module(s):** lms_badge
**Service(s):** N/A
**Epic:** E-06 (Certificate & Badge System)
**Contributes to Feature(s):** F-010 (Badges / Gamification)
**Design source(s):** `../C05-modules/lms_badge.md`
**Round:** N/A
**Driver:** SA directive
**Status:** BLOCKED
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_badge`

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 3 (full implementation required)
**Splitting strategy:**
- S1: BadgeAwardService (criteria evaluator), BadgeCriteriaPlugin system or service-based strategy
- S2: Routes for badge catalog + user badge portfolio page; admin badge management controller; templates
- S3: Award triggers (event subscribers for course completion, quiz pass, login streaks); leaderboard; tests

## Scope (from primary source)

Intended to provide gamification through achievement badges. Currently only `lms_badge` and `lms_user_badge` entity definitions exist. No service evaluates `field_criteria` JSON, no event subscriber awards badges on any trigger, no routes display badges to learners, no module file or install file exist.

**Stub / dead-code status (as-built):**
- Two entity classes (`Badge`, `UserBadge`) exist with admin CRUD auto-generated at `/manage/badges`.
- `field_criteria` JSON format is documented in code (`{"type":"courses_completed","count":5}`) but is never read or evaluated.
- `field_xp_reward` is defined but no XP tracking system exists in any module.
- No `lms_badge.module`, `lms_badge.services.yml`, `lms_badge.routing.yml`, `lms_badge.permissions.yml`, or `lms_badge.install`.

## Source Documents

| Source | Why loaded |
|---|---|
| `../C05-modules/lms_badge.md` | Owning module design |
| `../../B-Requirements/B03-features/06-certification-badges.md` | Badge feature requirements |
| `../../B-Requirements/B03-features/13-suggested-modern-features.md` | Gamification requirements |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Admin can create/edit badges with icon, type, criteria, XP reward | B03-features/06-certification-badges | Acceptance — partially as-built (entity CRUD only) |
| System evaluates badge criteria and awards badges automatically | B03-features/06-certification-badges | target-state / planned |
| Supported criteria: courses_completed (count), quiz_passed, login_streak (days) | B03-features/06-certification-badges | target-state / planned |
| User badge portfolio page | B03-features/13-suggested-modern-features | target-state / planned |
| Badge catalog page | B03-features/13-suggested-modern-features | target-state / planned |
| XP reward tracking system | B03-features/13-suggested-modern-features | target-state / planned |
| Leaderboard | B03-features/13-suggested-modern-features | target-state / planned |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_badge/lms_badge.info.yml` | Create | Module declaration | — | S1 |
| `web/modules/custom/lms_badge/src/Entity/Badge.php` | Create | Badge entity (field_criteria JSON, field_xp_reward) | — | S1 |
| `web/modules/custom/lms_badge/src/Entity/UserBadge.php` | Create | UserBadge entity (EntityOwnerInterface, field_badge, field_awarded_at) | — | S1 |

**Files to be created in future sessions (target-state):**
- `lms_badge.services.yml` — BadgeAwardService registration
- `lms_badge.routing.yml` — badge catalog, user portfolio
- `lms_badge.permissions.yml` — award badge, view badges
- `lms_badge.module` — hook_theme, event subscriber hook_user_login (streak)
- `src/Service/BadgeAwardService.php` — criteria evaluator
- `src/EventSubscriber/BadgeAwardSubscriber.php` — listens to QuizPassedEvent, course completion
- `src/Controller/BadgeController.php` — catalog + portfolio pages
- Templates

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| lms_base | Intra-module | info.yml |
| lms_certificate (QuizPassedEvent trigger for badge award) | Intra-Epic | target-state |
| lms_progress (course completion trigger) | Cross-Epic | target-state |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| Entity annotation pattern | Badge, UserBadge entity classes | As-built |
| EntityOwnerTrait | UserBadge | As-built |

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission | Revision / moderation | Translation | Cache invalidation | Smoke evidence |
|---|---|---|---|---|---|---|---|---|
| `/manage/badges` | Badge CRUD (admin only) | lms_badge entity | `/manage/badges/add` | administer lms_course | None | None | Default entity | As-built (auto routes) |
| Badge catalog (target-state) | — | — | — | — | — | — | — | — |
| User portfolio (target-state) | — | — | — | — | — | — | — | — |

**Hardcoded content exceptions:** None.

## Feature Flags Introduced or Read

None.

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-BGE-001 | Integration | Badge entity can be created/edited via admin CRUD (as-built) | Entity scaffold AC |
| T-BGE-002 | Unit | field_criteria JSON format validated on save (target-state) | Criteria AC |
| T-BGE-003 | Integration | (target-state) BadgeAwardService awards badge when criteria met on course completion | Award AC |
| T-BGE-004 | Integration | (target-state) QuizPassedEvent triggers badge award check | Award trigger AC |
| T-BGE-005 | Unit | No duplicate UserBadge created for same user+badge | De-dup AC |

## Gate Results (per Story)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Entity installs; admin routes work | — | — |
| G2 Lint | PENDING | Only two entity files; not formally linted | 0/3 | — |
| G3 Tests | PENDING | No award logic to test | 0/3 | — |
| G4 Adversarial | PENDING | Cannot pass without award implementation | 0/3 | — |

## Notes

- **Near-stub (as-built):** `lms_badge` provides data models only. This is the most incomplete module in the certification Epic.
- **field_criteria JSON documented but never evaluated:** The comment in `Badge.php` documents `{"type":"courses_completed","count":5}` as the expected format, but no code reads or validates this field. A `BadgeAwardService` with a strategy pattern (one class per criterion type) is the recommended next step.
- **field_xp_reward with no XP system:** The XP reward field is defined, but no XP accumulation, display, or leaderboard system exists anywhere in the codebase. XP is currently a dead field.
- **badge_type vocabulary:** Taxonomy vocabulary `badge_type` is defined in the system (11 vocabularies). Badge entity `field_type` references it. Terms must be seeded separately.
- **Blocked by:** BadgeAwardService implementation and event subscriber wiring. This Story cannot progress until a design decision is made on the criteria evaluation architecture.
