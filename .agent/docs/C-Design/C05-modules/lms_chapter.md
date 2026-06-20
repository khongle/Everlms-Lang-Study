---
module: lms_chapter
service: course
route-groups: [Manage]
status: DONE
documentation-mode: as-built
---

# C05 — Module: LMS Chapter

**Directory:** `web/modules/custom/lms_chapter/`
**Responsibility:** Defines the `lms_chapter` ContentEntity — the middle tier of the Course → Chapter → Lesson curriculum hierarchy — providing entity schema, form, and access handler with no routing or services of its own.

---

## Route Bindings

| Route Group | Prefix | Controller / Form | Paths |
|-------------|--------|-------------------|-------|
| Manage (via AdminHtmlRouteProvider) | `/manage/chapters/` | entity forms (auto-generated) | `/manage/chapters/add`, `/manage/chapters/{lms_chapter}/edit`, `/manage/chapters/{lms_chapter}/delete`, `/manage/chapters/{lms_chapter}` |
| Workspace (declared in lms_course) | `/workspace/chapters/` | `_entity_form: lms_chapter.admin_edit` | `/workspace/chapters/{lms_chapter}/admin-edit` |

No `lms_chapter.routing.yml` exists. Auto-generated routes come from `AdminHtmlRouteProvider` wired in entity annotation links. The workspace admin-edit route is declared in [`lms_course.routing.yml`](./lms_course.md).

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `lms_chapter` | ContentEntity | Chapter entity; consumed via `field_chapters` on `lms_course` entity |
| `ChapterAccessControlHandler` | Access Handler | Delegates all access decisions to lms_course permissions (see below) |

---

## Internal Structure

```
web/modules/custom/lms_chapter/
+-- lms_chapter.info.yml             # dependencies: lms_base, lms_course
+-- src/
|   +-- Entity/
|   |   +-- Chapter.php              # lms_chapter ContentEntity; 8 base fields; translatable
|   +-- Form/
|   |   +-- ChapterForm.php          # ContentEntityForm; save() → redirects to collection
|   +-- Access/
|   |   +-- ChapterAccessControlHandler.php
```

No `.routing.yml`, `.services.yml`, `.permissions.yml`, `.module`, `.install`, `templates/`, or `config/` directories.

---

## Entity: lms_chapter

| Property | Value |
|----------|-------|
| Type | ContentEntityBase (no EntityOwnerTrait) |
| Base table | `lms_chapter` |
| Data table | `lms_chapter_field_data` |
| Translatable | TRUE |
| Admin permission | `administer lms_course` (borrowed from lms_course — no own permissions) |
| Entity keys | id, uuid, label=name, langcode, published=status |
| Canonical URL | `/manage/chapters/{lms_chapter}` |
| Collection | `/manage/chapters` |

**Base Fields (8):**

| Field | Type | Notes |
|-------|------|-------|
| name | string | required, max_length=255, translatable; entity label |
| field_description | text_long | translatable |
| field_lessons | entity_reference→lms_lesson | UNLIMITED; forward ref (chapter stores children) |
| weight | integer | default=0; sort order within course |
| status | boolean | default=TRUE |
| created / changed | timestamps | — |

**Form modes:** default, add, edit (declared in annotation); `admin_edit` registered externally by `lms_course.module` via `hook_entity_type_alter`.

**No `list_builder` defined in annotation.** No `admin_edit` form mode in annotation (registered by lms_course at runtime).

**No `uid`/owner field.** Chapter has no author concept; parent-child relationship is stored as a forward reference field (`field_chapters`) on `lms_course`.

---

## Access Control

`ChapterAccessControlHandler` reuses lms_course permissions:

| Operation | Required Permission |
|-----------|---------------------|
| view | `view lms_course` |
| update | `edit lms_course` |
| delete | `edit lms_course` |
| create | `create lms_course` |

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_base` | Internal | Base module requirement |
| `lms_course` | Internal | Borrows `administer lms_course` admin permission; workspace admin-edit route defined there; `hook_entity_type_alter` registers admin_edit form mode |

---

## Known Issues / Debt

- **Thin entity module by design:** Only 3 PHP classes. No ordering/prerequisite chapter logic exists.
- **No back-reference to course:** `lms_chapter` has no `field_course` field. The Course→Chapter relationship is stored only as `field_chapters` (forward ref on Course). Traversal from chapter to course requires querying course entities that reference a given chapter.
- **admin_edit form mode registered externally:** Coupling to `lms_course.module` means chapter's edit form behavior depends on lms_course being installed.

---

## Cross-Links

- Parent module (workspace routes + hooks): [lms_course](./lms_course.md)
- Child entity module: [lms_lesson](./lms_lesson.md)
- Entity detail: [lms_chapter entity](../C04-entities/01-content-entities.md)
