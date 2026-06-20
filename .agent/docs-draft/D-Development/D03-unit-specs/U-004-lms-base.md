# D03 — Unit Spec: lms_base

**Unit ID:** U-004
**Group:** G-FOUNDATION
**Title:** lms_base (taxonomies + LmsHelper)
**Status:** DONE
**Priority:** P0

---

## Scope

Provides shared infrastructure for all LMS modules: creates 8 taxonomy vocabularies with default seed terms on install, exposes the `LmsHelper` service with common utility methods (user role check, term lookup, slug generation, duration formatting), and registers a Drush command for demo content generation.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_base/lms_base.info.yml` | Module declaration |
| `web/modules/custom/lms_base/lms_base.install` | Taxonomy vocab + seed term creation |
| `web/modules/custom/lms_base/lms_base.module` | Hook implementations |
| `web/modules/custom/lms_base/lms_base.services.yml` | Service registration |
| `web/modules/custom/lms_base/drush.services.yml` | Drush command registration |
| `web/modules/custom/lms_base/src/Service/LmsHelper.php` | Shared utility service |
| `web/modules/custom/lms_base/src/Commands/LmsDemoContentCommand.php` | Demo content Drush command |

---

## Entities / Services / Controllers / Templates

**Taxonomies created on install:**
- `course_category` (hierarchical: Programming → Frontend/Backend/Mobile, Business, Design)
- `course_level` (Beginner, Intermediate, Advanced)
- `course_language` (English, Vietnamese, Japanese)
- `course_tags`
- `quiz_difficulty` (Easy, Medium, Hard)
- `quiz_results` (Passed, Failed)
- `faq_category` (General, Payment, Technical)
- `badge_type` (Completion, Streak, Quiz Master, Community Helper)

**Service: `lms_base.lms_helper` → `LmsHelper`**
- `getCurrentUserId(): int`
- `currentUserHasRole(string $role): bool`
- `getTermsByVocabulary(string $vid): array`
- `getTermName(int $tid): string`
- `formatDuration(int $minutes): string`
- `generateSlug(string $string): string`

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Module installs; vocabularies created |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | LmsHelper methods return expected values |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] All 8 vocabularies exist after `drush en lms_base`
- [x] Default seed terms seeded correctly on install
- [x] `LmsHelper` injectable via constructor DI in other modules
- [x] `generateSlug()` produces lowercase kebab-case output
- [x] `formatDuration()` converts minutes to human-readable string
- [x] Drush demo-content command executes without errors

---

## Known Debt / Issues

- `\Drupal::` static calls observed in `ProgressApiController` (tracked as A10 U-064 debt).

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-001 | Environment prerequisite |
| U-002 | Taxonomy contrib API required |
| U-005 – U-056 | All content/feature modules depend on LmsHelper and taxonomies |

---

*End of U-004-lms-base.md*
