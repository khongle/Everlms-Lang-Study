---
module: lms_bundle
service: Commerce
route-groups: [Manage]
status: STUB
---

# C05 — Module: LMS Bundle

**Directory:** `web/modules/custom/lms_bundle/`
**Responsibility:** Defines the `lms_course_bundle` entity (data model for grouping multiple courses into a purchasable package) — no business logic, no checkout flow, no routes beyond auto-generated admin CRUD.

**Documentation mode:** as-built (scaffold-only)

---

> **SCAFFOLD ONLY.** `lms_bundle` contains only an entity class. There is no routing file, no services file, no `.module` hooks, no controllers, no templates, no install schema, and no checkout integration. The entity can be managed via the auto-generated Drupal admin UI at `/manage/bundles`, but bundle purchases cannot actually be processed. **Status: target-state / planned** for full implementation.

---

## Route Bindings

| Route Group | Prefix | Controller | Paths |
|-------------|--------|-----------|-------|
| Manage (auto) | `/manage/bundles` | `AdminHtmlRouteProvider` (auto-generated) | `/manage/bundles`, `/manage/bundles/add`, `/manage/bundles/{lms_course_bundle}`, `/manage/bundles/{lms_course_bundle}/edit`, `/manage/bundles/{lms_course_bundle}/delete` |

> Routes are auto-generated from entity links. No custom routing file exists.

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `CourseBundle` (`lms_course_bundle`) | Content Entity | Data model only; no services, controllers, or checkout integration |

---

## Internal Structure

```
web/modules/custom/lms_bundle/
+-- lms_bundle.info.yml             # deps: lms_base, lms_course; no services/routing/module file
+-- src/
    +-- Entity/
        +-- CourseBundle.php        # Only PHP file in this module
```

No `lms_bundle.services.yml`, `lms_bundle.routing.yml`, `lms_bundle.permissions.yml`, `lms_bundle.module`, `lms_bundle.install`, or `templates/` directory.

### Key Entity: `lms_course_bundle` (base table: `lms_course_bundle`)

| Field | Type | Notes |
|-------|------|-------|
| id, uuid | auto | entity keys |
| name | string(255) | required; label key |
| field_description | text_long | — |
| field_courses | entity_reference (unlimited) | target: `lms_course` |
| field_price | decimal(10,2) | default 0 |
| field_discount_percent | integer | default 0 |
| field_thumbnail | image | file_directory: `bundles/thumbnails` |
| status | boolean | default TRUE |

**Entity handlers:** `view_builder`, `views_data`, `form={default, delete}`, `route_provider=AdminHtmlRouteProvider`, `access=EntityAccessControlHandler`. No custom `list_builder`.

**Entity links:** canonical `/manage/bundles/{lms_course_bundle}`, add-form, edit-form, collection `/manage/bundles`.

**Admin permission:** `administer lms_course`.

### What Is Missing (Target-State)

| Missing piece | Notes |
|---------------|-------|
| Checkout controller/flow | `lms_order_item.field_type` accepts `bundle` value, but no code creates bundle order items |
| Discount calculation | `field_discount_percent` exists on entity but no logic reads it |
| Bundle listing page | No public-facing page listing available bundles |
| Purchase flow | No `BundleCheckoutForm`, no route, no auto-enrollment for bundle items |
| Permissions file | No custom permissions; relies entirely on `administer lms_course` |

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_base` | Internal | Required base module |
| `lms_course` | Internal | `field_courses` entity reference target |

**No circular dependencies.**

---

## Known Issues / Debt

| ID | Severity | Description |
|----|----------|-------------|
| DEBT-NO-CHECKOUT | High | No checkout integration — bundles cannot be purchased despite `lms_order_item.field_type` having a `bundle` option |
| DEBT-SCAFFOLD-ONLY | High | No controllers, hooks, services, or templates; module is data model only |
| DEBT-DISCOUNT-UNUSED | Low | `field_discount_percent` defined on entity but no code reads or applies it |
| DEBT-NO-PUBLIC-PAGE | Medium | No public listing or detail page for bundles |

---

## Cross-links

- Entity: [CourseBundle](../C04-entities/01-content-entities.md)
- Order item that references bundles: [lms_order](lms_order.md)
- Course entity: [lms_course](lms_course.md)
