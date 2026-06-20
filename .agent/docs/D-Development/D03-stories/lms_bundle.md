# D03 — Story Spec: lms_bundle

**Story ID:** lms_bundle
**Story type:** module
**Title:** Course Bundles — Data Model Scaffold (No Checkout Integration)
**Primary source:** `C-Design/C05-modules/lms_bundle.md`
**Owning module(s):** lms_bundle
**Service(s):** N/A
**Epic:** E-05 (Commerce & Payment)
**Contributes to Feature(s):** F-008 (Course Bundles)
**Design source(s):** `../C05-modules/lms_bundle.md`
**Round:** N/A
**Driver:** SA directive
**Status:** BLOCKED
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_bundle`

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 3 (full implementation required)
**Splitting strategy:**
- S1: Custom list builder, routing.yml, bundle catalog page, bundle detail page
- S2: Bundle checkout integration in lms_order (CheckoutController::checkoutBundle, BundleCheckoutForm, OrderItem field_type=bundle)
- S3: Auto-enrollment for all bundle courses on order completion + admin templates

## Scope (from primary source)

Intended to allow learners to purchase a discounted collection of courses in a single transaction. Currently only the `lms_course_bundle` entity definition exists. No controllers, no routes, no checkout integration, and no templates are implemented. The entity is auto-managed by Drupal's generated admin routes at `/manage/bundles`.

**STUB / dead-code status (as-built):**
- Entity scaffold only. Auto-generated Drupal admin CRUD works at `/manage/bundles`.
- `lms_order_item.field_type` includes `bundle` as an allowed value, but no code in `lms_order` creates or processes bundle order items.
- No bundle-specific checkout flow, no bundle detail page for learners, no auto-enrollment for bundle items.
- Module has no `.module` file, no routing.yml, no services.yml, no templates, no install.

## Source Documents

| Source | Why loaded |
|---|---|
| `../C05-modules/lms_bundle.md` | Owning module design |
| `../../B-Requirements/B03-features/04-order-membership.md` | Bundle feature requirements |
| `lms_order.md` | Checkout integration needed here |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Admins can create course bundles (name, courses, price, discount, thumbnail) | B03-features/04-order-membership | Acceptance — partially as-built (entity CRUD only) |
| Learners can browse and purchase bundles | B03-features/04-order-membership | target-state / planned |
| Bundle purchase auto-enrolls user in all included courses | B03-features/04-order-membership | target-state / planned |
| Bundle checkout flow in lms_order | B03-features/04-order-membership | target-state / planned |
| Bundle detail page | B03-features/04-order-membership | target-state / planned |
| Bundle listing / catalog page | B03-features/04-order-membership | target-state / planned |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_bundle/lms_bundle.info.yml` | Create | Module declaration | — | S1 |
| `web/modules/custom/lms_bundle/src/Entity/CourseBundle.php` | Create | lms_course_bundle entity (data model only) | — | S1 |

**Files to be created in future sessions (target-state):**
- `lms_bundle.routing.yml` — bundle catalog + detail routes
- `lms_bundle.module` — hook_theme
- `src/Controller/BundleController.php` — catalog + detail pages
- Modify `lms_order/CheckoutController.php` — add `checkoutBundle()` method
- Modify `lms_order/lms_order.module` — handle bundle items in `_lms_order_process_completed_order()`
- Templates for catalog, detail, and checkout

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| lms_base | Intra-module | info.yml |
| lms_course | Intra-module | info.yml; field_courses references lms_course |
| lms_order (checkout integration) | Intra-Epic | target-state |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| Entity annotation pattern | CourseBundle entity | As-built (scaffold only) |
| AdminHtmlRouteProvider | CourseBundle | As-built (auto routes only) |

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission | Revision / moderation | Translation | Cache invalidation | Smoke evidence |
|---|---|---|---|---|---|---|---|---|
| `/manage/bundles` | Bundle CRUD (admin only) | lms_course_bundle entity | `/manage/bundles/add` | administer lms_course | None | None | Default entity cache | As-built (auto routes) |
| Bundle detail page | (target-state) | — | — | — | — | — | — | — |

**Hardcoded content exceptions:** None.

## Feature Flags Introduced or Read

None.

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-BND-001 | Integration | CourseBundle entity can be created/edited via admin CRUD (current as-built) | Entity scaffold AC |
| T-BND-002 | Integration | (target-state) Bundle checkout creates OrderItem with field_type=bundle | Checkout AC |
| T-BND-003 | Integration | (target-state) Completing a bundle order enrolls user in all field_courses | Auto-enrollment AC |
| T-BND-004 | Integration | (target-state) Bundle detail page renders pricing + course list | Detail page AC |

## Gate Results (per Story)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Entity installs; admin routes work | — | — |
| G2 Lint | PENDING | Only one PHP file; not formally linted | 0/3 | — |
| G3 Tests | PENDING | No controller/checkout to test | 0/3 | — |
| G4 Adversarial | PENDING | Cannot pass without checkout implementation | 0/3 | — |

## Notes

- **Minimal scaffold (stub):** This module is a data model placeholder. The `CourseBundle` entity is the only deliverable. Everything else (checkout, enrollment, UI) is target-state.
- **`field_type = bundle` gap:** `lms_order_item` accepts `bundle` as a `field_type` value, but `_lms_order_process_completed_order()` in `lms_order.module` does not handle `bundle` items — it only handles `course` and `membership`. This means creating a bundle order item would not trigger enrollment. This must be fixed in `lms_order` before bundle checkout can work.
- **Blocked by:** lms_order checkout integration + bundle catalog/detail controller implementation.
