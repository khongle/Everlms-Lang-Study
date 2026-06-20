# Task: Static Service Call Cleanup

**ID:** TASK-018
**Unit:** U-064
**Group:** G-HARDENING
**Priority:** P1
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG (10% — 310 violations)

## Audit Notes
- **310 `\Drupal::` calls** in non-.module PHP files (controllers, services, forms)
- Top offenders: lms_ai (131), lms_course (73), lms_certificate (23), lms_content_tools (17), lms_forum (17), lms_order (13), lms_notification (13), lms_zoom (8)
- Common patterns: `\Drupal::service()`, `\Drupal::config()`, `\Drupal::entityTypeManager()`, `\Drupal::logger()`, `\Drupal::formBuilder()`
- All classes should use constructor injection per Drupal best practices

## Description
Thay thế tất cả `\Drupal::service()` calls bằng Dependency Injection qua constructor. Đảm bảo testability.

## Acceptance Criteria
- [ ] Audit tất cả files có `\Drupal::` static calls
- [ ] Convert sang DI via constructor promotion
- [ ] Services registered trong `*.services.yml`
- [ ] No `\Drupal::service()` trong controllers, services, event subscribers
- [ ] `\Drupal::` chỉ allowed trong `.module` files, hooks, và procedural code

## Technical Notes
- Impact: tất cả custom modules
- Pattern: Constructor Promotion + service YAML
- Exceptions: hooks, `.module` procedural code

## Dependencies
- None (independent)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Zero static service calls trong OOP code
