# Task: Recommendations Engine ("You Might Also Like")

**ID:** TASK-177
**Unit:** — (cross-unit: U-009, U-046, new lms_recommendation)
**Group:** G-CONTENT
**Priority:** P2
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build a recommendations engine that surfaces relevant courses to users across three modes: per-course similar courses ("You might also like"), per-user homepage rail ("Recommended for you"), and per-cohort peer-based recommendations ("Peers are learning"). Strategy uses collaborative filtering on enrollment history combined with content-based signals from course tags and category. A new module `lms_recommendation` owns the scoring logic, caching layer, and REST endpoint. All modes update on a daily cron; per-course similarities also recalculate on course publish/update.

## Acceptance Criteria
- [ ] `lms_recommendation` module scaffolded with correct `.info.yml`, service definitions, schema
- [ ] `RecommendationService` computes item-item collaborative filtering score from `lms_enrollment` data
- [ ] Content-based fallback: courses sharing ≥2 tags or same category score > 0 when enrollment data is sparse
- [ ] Per-course block: up to 6 "similar course" cards rendered on course detail page
- [ ] Per-user rail: up to 8 "Recommended for you" course cards on homepage (authenticated only)
- [ ] Per-cohort rail: up to 8 "Peers are learning" cards derived from users with same organization/cohort field
- [ ] Recommendations pre-computed and cached in dedicated DB table `lms_recommendation_cache`; cache TTL 24h
- [ ] Cron hook `lms_recommendation_cron()` rebuilds all caches daily; completes in ≤5 min on 10k users × 500 courses
- [ ] Course publish/update event invalidates and rebuilds that course's similarity row immediately
- [ ] Unit test: scoring function returns deterministic ranked list for fixture enrollment data
- [ ] No recommendations shown to anonymous users (rail hidden, course block hidden)

## Technical Notes
- New module: `web/modules/custom/lms_recommendation/`
- `src/Service/RecommendationService.php` — scoring + cache rebuild
- `src/Plugin/Block/SimilarCoursesBlock.php` — course detail sidebar
- `src/Plugin/Block/RecommendedForYouBlock.php` — homepage rail
- `src/Plugin/Block/PeersLearningBlock.php` — cohort-based rail
- DB schema: `lms_recommendation_cache` (`uid`, `context` ENUM('user','course','cohort'), `entity_id`, `recommended_ids` JSON, `updated`). Add via `hook_schema()`.
- Collaborative filtering formula: Jaccard similarity on enrollment sets is acceptable for v1; cosine similarity on tag vectors as fallback
- Cron: implement `hook_cron()`; use Drupal Queue for batch processing (chunk 500 users per queue item)
- Course events: subscribe to `CoursePublishedEvent` from `lms_course`
- REST endpoint: `GET /api/v1/recommendations/{context}/{id}` for frontend consumption (extend TASK-074 OpenAPI spec)
- Template: `lms-recommendation-rail.html.twig` for both homepage rails

## Dependencies
- TASK-014 (analytics dashboards — enrollment data confirmed populated)
- TASK-060 (search index — tag fields confirmed indexed)
- TASK-074 (mobile API + OpenAPI — reuse endpoint auth pattern)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: scoring; Behat: rail visible on homepage for auth user)
- [ ] Cache rebuild time benchmarked and within 5 min bound at 10k × 500 fixture
- [ ] REST endpoint documented in OpenAPI spec (update TASK-074 artifact)
