# D03 — Unit Spec: Static Service Call Cleanup (\Drupal:: → DI)

**Unit ID:** U-064
**Group:** G-HARDENING
**Title:** Static service call cleanup (\Drupal:: → DI)
**Status:** SPEC
**Priority:** P0
**Assigned:** unassigned

---

## Scope

Replace all `\Drupal::service()`, `\Drupal::entityTypeManager()`, `\Drupal::currentUser()`, `\Drupal::database()`, etc. in service classes with proper constructor injection. This improves testability and follows Drupal best practices.

---

## Files to Touch

| File | Action | Note |
|------|--------|------|
| All `*Service.php` files | Audit + Modify | All lms_* modules |
| All `*Manager.php` files | Audit + Modify | All lms_* modules |
| All `*.services.yml` files | Modify | Add constructor args |

---

## Approach

1. Run grep across all lms_* modules: `grep -r '\\Drupal::' web/modules/custom/lms_*/src/Service/`
2. For each static call, move service to constructor parameter
3. Update `*.services.yml` `arguments:` list accordingly
4. Static calls in controllers and forms are acceptable (Drupal convention) — focus on service classes only

---

## Acceptance Criteria

- [ ] No `\Drupal::` calls in any `src/Service/*.php` file
- [ ] No `\Drupal::` calls in any `src/Plugin/**/*.php` file
- [ ] All injected services declared in `*.services.yml` `arguments:`
- [ ] Unit tests can instantiate services with mock dependencies (testability goal)
- [ ] Existing functionality unchanged after refactor (regression tests pass)

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | PHPCS Drupal standard |
| G3 Tests | PENDING | Services instantiate cleanly with mock args |
| G4 Adversarial | PENDING | — |

---

## Dependencies

- Best run after all SPEC units are DONE (more service classes exist to audit)

---

**Feature Flag:** None

*End of U-064-di-cleanup.md*
