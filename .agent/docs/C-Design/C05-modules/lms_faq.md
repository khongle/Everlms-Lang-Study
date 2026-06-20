---
module: lms_faq
service: course
route-groups: [Manage]
status: DONE
documentation-mode: as-built
---

# C05 — Module: LMS FAQ

**Directory:** `web/modules/custom/lms_faq/`
**Responsibility:** Defines the `lms_faq` ContentEntity with three display scopes (global, course, lesson); provides no routes, services, or hooks of its own — all CRUD routing and frontend rendering is delegated to `lms_course`.

---

## Route Bindings

| Route Group | Prefix | Controller / Form | Paths |
|-------------|--------|-------------------|-------|
| Manage (via AdminHtmlRouteProvider) | `/manage/faq/` | entity forms (auto-generated) | `/manage/faq/add`, `/manage/faq/{lms_faq}/edit`, `/manage/faq/{lms_faq}` |
| Workspace (declared in lms_course) | `/workspace/content/faqs/` | `WorkspaceContentController` | `/workspace/content/faqs/add`, `/workspace/content/faqs/{lms_faq}/edit`, `/workspace/content/faqs/{lms_faq}/view` |

No `lms_faq.routing.yml` exists. Auto-generated routes come from `AdminHtmlRouteProvider` via entity annotation links. Workspace CRUD routes are declared in [`lms_course.routing.yml`](./lms_course.md).

**No delete-form link is declared in the entity annotation.** Delete is only available via the auto-generated Drupal entity framework routes, not via the workspace UI.

**Frontend rendering:** FAQs have no dedicated public route. They are loaded inline by `lms_course` controllers:
- `CoursePublicController::homepage()` — loads `field_scope=global` FAQs
- `CoursePublicController::detail()` — loads `field_scope=course` FAQs filtered by `field_course`

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `lms_faq` | ContentEntity | FAQ entity; consumed by lms_course controllers for homepage and course detail pages |

No services, no plugins, no hooks, no templates.

---

## Internal Structure

```
web/modules/custom/lms_faq/
+-- lms_faq.info.yml                 # dependencies: lms_base
+-- src/
|   +-- Entity/
|   |   +-- Faq.php                  # lms_faq ContentEntity; 7 base fields
```

This is the smallest custom module in EverLMS. Only two files exist: `lms_faq.info.yml` and `src/Entity/Faq.php`.

No `.routing.yml`, `.services.yml`, `.permissions.yml`, `.module`, `.install`, `templates/`, or `config/` directories.

---

## Entity: lms_faq

| Property | Value |
|----------|-------|
| Type | ContentEntityBase (no EntityOwnerTrait) |
| Base table | `lms_faq` |
| Data table | `lms_faq_field_data` |
| Translatable | TRUE |
| Admin permission | `administer lms_course` (borrowed from lms_course — no own permissions) |
| Entity keys | id, uuid, label=question, langcode |
| Canonical URL | `/manage/faq/{lms_faq}` |
| Collection | `/manage/faq` |

**Note:** No `status` (published) field. FAQs have no publish/unpublish capability — all saved FAQs are treated as active.

**Base Fields (7):**

| Field | Type | Notes |
|-------|------|-------|
| question | string | required, max_length=512, translatable; entity label key |
| field_answer | text_long | required, translatable |
| field_scope | list_string | default=global; allowed: **global, course, lesson** |
| field_course | entity_reference→lms_course | 1; relevant when field_scope=course |
| field_category | entity_reference→taxonomy_term | 1; bundle: faq_category |
| weight | integer | default=0; sort order |

**No `field_lesson` reference field** despite the spec listing a lesson scope — FAQs scoped to a lesson (`field_scope=lesson`) have no entity reference back to the lesson. The lesson scope is declared but has no filtering mechanism: the entire `lms_faq` module contains only 2 files (`lms_faq.info.yml` and `Faq.php`) — no routing, no controller, no service. All FAQ display logic lives in `CoursePublicController` (lms_course), and inspection confirms no query ever filters on `field_scope=lesson`. The lesson scope is dead code in the current build.

---

## Access Control

No custom access handler. Uses Drupal core `EntityAccessControlHandler` defaults (no `ChapterAccessControlHandler` equivalent):

| Operation | Required Permission |
|-----------|---------------------|
| All operations | Governed by `administer lms_course` (admin_permission in annotation) |

---

## Three-Scope Consumption Pattern

| Scope value | Loaded by | Display location |
|-------------|-----------|-----------------|
| `global` | `CoursePublicController::homepage()` | Homepage FAQ section |
| `course` | `CoursePublicController::detail()` | Course detail page, filtered by `field_course` matching current course |
| `lesson` | **Dead code — no controller exists** (confirmed) | `field_scope=lesson` is never queried; lms_faq has no routing or controller; lesson FAQ rendering is not implemented |

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_base` | Internal | Base module requirement |
| `lms_course` | Internal (runtime) | Admin permission borrowed; workspace CRUD routes declared in lms_course; frontend rendering in CoursePublicController |

---

## Known Issues / Debt

- **No lesson-scope consumer (potential dead field):** `field_scope=lesson` is defined on the entity but no controller is confirmed to load FAQs with this scope. The lesson scope may be target-state / unimplemented.
- **No status field:** FAQs cannot be drafted or unpublished. Any saved FAQ is immediately visible (subject to scope filtering). Adding a `status` field would require a schema update.
- **Routing delegation to lms_course violates separation of concerns:** All FAQ workspace CRUD routes (`/workspace/content/faqs/*`) are declared inside `lms_course.routing.yml` and handled by `WorkspaceContentController`. This creates a hard coupling where lms_faq cannot function as a standalone module.
- **No delete-form link in annotation:** The entity annotation does not declare a `delete-form` link, so bulk delete and canonical delete actions are inaccessible through the workspace UI.
- **Core EntityAccessControlHandler:** Without a custom access handler, permission granularity is limited to the single `administer lms_course` check. Non-admin users with partial course permissions cannot manage FAQs independently.

---

## Cross-Links

- Workspace routes and frontend rendering: [lms_course](./lms_course.md)
- Entity detail: [lms_faq entity](../C04-entities/01-content-entities.md)
- FAQ category vocabulary: `faq_category` (one of 11 taxonomy vocabularies — see [C04 entities](../C04-entities/01-content-entities.md))
