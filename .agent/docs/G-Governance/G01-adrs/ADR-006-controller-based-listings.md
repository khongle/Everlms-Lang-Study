# ADR-006 - Controller-based listings instead of Drupal Views

**Status:** Accepted (as-built) · **Date:** 2026-07-11

## Context
The framework's Drupal solution conventions default list-behavior pages to Views (`entity.view_mode` + Views UI), reserving hand-rolled controllers for cases a View cannot express. As-built, EverLMS has **zero project-authored `views.view.*.yml`** in `config/sync/` (every shipped View is a core/contrib default — `content`, `frontpage`, `archive`, `who_s_online`, etc. — none references an `lms_*` entity type). All 14 list-behavior C11 pages (`listing` / `workspace-list` archetypes: course catalog P002, categories P004, category detail P005, blog P008, forum index P013, my-courses P018, my quiz results P022, my certificates P023, my notes P024, my wishlist P025, manage courses P032, manage orders P036, manage content P037, manage pricing P038) are served by hand-rolled Controllers (`CoursePublicController`, `ForumController`, `LmsBlogController`, `WishlistController`, `NoteController`, `UserPortalController`, `WorkspaceContentController`, `AdminController`, etc.), not Views.

## Decision
Accept **Controller-based listings** as the as-built pattern for this project, not a per-page oversight:
- Public catalog-style lists (course catalog, categories, blog, forum) mix cross-entity aggregation, computed fields (aggregate rating, popularity/enrollment count), and AJAX-driven filter chips that go beyond a single View's exposed-filter model — a controller composing services (search/filter, cache tags) was simpler than one or more Views + custom Views plugins.
- "My *" learner-scoped lists (my-courses, my-notes, my-wishlist, my-certificates, my-quiz-results) are per-user, AJAX-toggle-heavy (wishlist heart, note CRUD) and reuse the same services the Flutter app's `lms_mobile_api` controllers call — one controller serves both surfaces instead of a View plus a parallel REST resource.
- Manage-area lists (manage-courses, manage-orders, manage-content, manage-pricing) aggregate multiple entity types per row (e.g. manage-content spans page/FAQ/taxonomy/glossary counts) and carry row actions/bulk operations wired to workflow transitions (`GradingStrategyFactory`, order reconcile) that a stock Views bulk-operations field does not cover.

Going forward, a new list-behavior page defaults to a View per the framework convention; a controller is chosen only when it needs cross-entity aggregation, computed/service-backed fields, or AJAX row-level mutation that a View + exposed filters cannot express — and the page's C11 spec must reference this ADR (`ADR-006`) in that case, per the Gate 6.C1 rule.

## Consequences
- Fewer Views/contrib Views plugins to maintain; listing logic lives with its domain module (testable in PHPUnit) instead of split across Views UI config + preprocess hooks.
- No Views UI self-service for editors — changing a list's filters/sort requires a code change and deploy, not a config export.
- Listing pages must hand-implement paging, exposed-filter validation and cache contexts/tags that Views provides for free — verified per page in `## Drupal 11 Page Definition` (Cache row) and `## Listing behavior`.
- Every list-behavior C11 page spec references `ADR-006` in its Source Evidence / Drupal 11 Page Definition section, closing Gate 6.C1 (list-behavior page hand-rolled controller requires an ADR link).
