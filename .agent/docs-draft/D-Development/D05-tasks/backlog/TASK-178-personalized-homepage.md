# Task: Personalized Homepage Rails for Authenticated Users

**ID:** TASK-178
**Unit:** U-009 (course catalog / homepage)
**Group:** G-CONTENT
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Replace the generic homepage with a personalized experience for logged-in users. Four dynamic rails are shown: "Continue learning" (in-progress enrollments sorted by last activity), "Recommended for you" (from recommendations engine), "Trending in your interests" (trending courses filtered by user's favorite categories), and "New from instructors you follow" (latest published courses from followed instructors). Anonymous users continue to see the existing generic homepage. The feature extends `lms_course` HomepageController; no new module is required.

## Acceptance Criteria
- [ ] Authenticated users see personalized homepage instead of generic one (via `_custom_access` route check)
- [ ] "Continue learning" rail: shows ≤6 in-progress courses, sorted by `lms_enrollment.last_accessed DESC`; empty rail hidden
- [ ] "Recommended for you" rail: pulls from `lms_recommendation` cache (TASK-177); shows ≤8 cards; hidden if cache empty
- [ ] "Trending in your interests" rail: courses from TASK-181 trending list intersected with user's followed categories; ≤6 cards
- [ ] "New from instructors you follow" rail: last 6 published courses from instructors in user's follow list; hidden if no follows
- [ ] Rail order: Continue learning → Recommended → Trending → New from instructors
- [ ] Each rail has "See all" link to appropriate filtered catalog/search page
- [ ] Anonymous homepage unchanged (no regression)
- [ ] Page cached at user level (max-age 0, must-revalidate; Drupal render cache tag `user:{uid}`)

## Technical Notes
- File: `web/modules/custom/lms_course/src/Controller/HomepageController.php` — add `personalizedHomepage()` method
- Route: `lms_course.routing.yml` — add `lms_course.homepage_personalized` route with `_custom_access: '\Drupal\lms_course\Access\HomepageAccessCheck::access'`
- `HomepageAccessCheck`: returns `AccessResult::allowed()` for authenticated, `forbidden()` for anonymous (falls through to existing route)
- Service: inject `RecommendationService` (TASK-177), `lms_analytics.trending_service` (TASK-181)
- Query for "Continue learning": `\Drupal::entityQuery('lms_enrollment')` filtered by `uid` + `status = in_progress`, sorted by `last_accessed`
- Template: `web/themes/custom/lms_theme/templates/homepage/homepage-personalized.html.twig`
- SCSS: `_homepage-personalized.scss` — carousel/rail component reusing existing card styles

## Dependencies
- TASK-177 (recommendations engine — provides "Recommended for you" data)
- TASK-181 (trending courses — provides "Trending in your interests" data)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: access check; Behat: personalized rails visible after login)
- [ ] Anonymous homepage smoke-tested (no regression)
- [ ] Render cache tags documented in inline comment
