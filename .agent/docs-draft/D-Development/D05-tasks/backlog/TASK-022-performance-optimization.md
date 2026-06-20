# Task: Performance Optimization

**ID:** TASK-022
**Unit:** U-070
**Group:** G-HARDENING
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG (25% — analytics ok, rest poor)

## Audit Notes
- **N+1 patterns confirmed**: ProgressApiController::markComplete() loops ALL lessons with 1 query per lesson. UserPortalController::dashboard() loads all enrollments then chain-loads chapters→lessons→progress. CoursePublicController: 16 loadMultiple calls.
- **No `.install` files define custom indexes** (8 install files, none with addIndex)
- Analytics module uses efficient JOINed queries ✅ (the one bright spot)
- `loadByProperties` used extensively in ZoomMeetingManager/Controller (chain-loading)

## Description
Optimize performance: fix N+1 queries, lazy loading, query optimization, index audit.

## Acceptance Criteria
- [ ] Audit N+1 queries trong entity list views
- [ ] Entity query optimization (eager load related entities)
- [ ] Database index audit (add missing indexes)
- [ ] Lazy load heavy content (SCORM, H5P embeds)
- [ ] Image optimization (responsive images, lazy load)
- [ ] Homepage load ≤2s, Catalog ≤3s, API ≤500ms p95

## Technical Notes
- Tools: Drupal devel + webprofiler
- Focus: entity storage queries, Views queries
- Index: entity base tables + custom query columns

## Dependencies
- TASK-021 (Cache strategy)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Performance benchmarks meet NFR targets
