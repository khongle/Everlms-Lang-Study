---
module: lms_lesson
service: course
route-groups: [Manage]
status: DONE
documentation-mode: as-built
---

# C05 — Module: LMS Lesson

**Directory:** `web/modules/custom/lms_lesson/`
**Responsibility:** Defines the `lms_lesson` ContentEntity — the leaf tier of the Course → Chapter → Lesson hierarchy — supporting nine content types (video, text, quiz, h5p, scorm, pdf, pptx, zoom, mixed) and implementing synchronous SCORM package extraction on save.

---

## Route Bindings

| Route Group | Prefix | Controller / Form | Paths |
|-------------|--------|-------------------|-------|
| Manage (via AdminHtmlRouteProvider) | `/manage/lessons/` | entity forms (auto-generated) | `/manage/lessons/add`, `/manage/lessons/{lms_lesson}/edit`, `/manage/lessons/{lms_lesson}/delete`, `/manage/lessons/{lms_lesson}` |
| Workspace (declared in lms_course) | `/workspace/lessons/` | `_entity_form: lms_lesson.admin_edit` | `/workspace/lessons/{lms_lesson}/admin-edit` |

No `lms_lesson.routing.yml` exists. Auto-generated routes come from `AdminHtmlRouteProvider`. The workspace admin-edit route is declared in [`lms_course.routing.yml`](./lms_course.md).

**Note:** The in-lesson learning view (`/learn/{lms_course}/{lms_lesson}`) is owned by `lms_progress` module (`UserPortalController::lessonView`), not by lms_lesson itself.

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `lms_lesson` | ContentEntity | Lesson entity; consumed via `field_lessons` on `lms_chapter` entity |
| `LessonAccessControlHandler` | Access Handler | Delegates to lms_course permissions; hook in .module also overrides view access |
| SCORM extraction | `.module` hook | Auto-extracts zip on insert/update; makes content available at `public://scorm_extracted/{lesson_id}/` |

---

## Internal Structure

```
web/modules/custom/lms_lesson/
+-- lms_lesson.info.yml              # dependencies: drupal:datetime, drupal:file, drupal:comment, lms_base, lms_chapter
+-- lms_lesson.module                # hook_entity_access, hook_entity_insert, hook_entity_update + _lms_lesson_extract_scorm()
+-- src/
|   +-- Entity/
|   |   +-- Lesson.php               # lms_lesson ContentEntity; 20 base fields; translatable
|   +-- Form/
|   |   +-- LessonForm.php           # ContentEntityForm; #states conditional field visibility; save() → edit-form
|   +-- Access/
|   |   +-- LessonAccessControlHandler.php
```

No `.routing.yml`, `.services.yml`, `.permissions.yml`, `.install`, `templates/`, or `config/` directories.

---

## Entity: lms_lesson

| Property | Value |
|----------|-------|
| Type | ContentEntityBase (no EntityOwnerTrait) |
| Base table | `lms_lesson` |
| Data table | `lms_lesson_field_data` |
| Translatable | TRUE |
| Admin permission | `administer lms_course` (borrowed from lms_course — no own permissions) |
| Entity keys | id, uuid, label=name, langcode, published=status |
| Canonical URL | `/manage/lessons/{lms_lesson}` |
| Collection | `/manage/lessons` |

**Base Fields (20):**

| Field | Type | Cardinality | Notes |
|-------|------|-------------|-------|
| name | string | 1 | required, max_length=255, translatable |
| field_content | text_long | 1 | main HTML body; translatable |
| field_type | list_string | 1 | required, default=text; allowed: **video, text, quiz, h5p, scorm, pdf, pptx, zoom, mixed** |
| field_h5p_embed | string_long | 1 | H5P embed code |
| field_video_url | string | 1 | max_length=512; YouTube/Vimeo URL |
| field_video_file | file | 1 | `lessons/videos`; extensions: mp4 webm ogg |
| field_zoom_meeting_id | string | 1 | max_length=64 |
| field_zoom_schedule | timestamp | 1 | — |
| field_scorm_package | file | 1 | `lessons/scorm`; extensions: zip |
| field_pptx_attachment | file | 1 | `lessons/pptx`; extensions: ppt pptx |
| field_attachments | file | UNLIMITED | `lessons/attachments`; extensions: pdf doc docx ppt pptx xls xlsx zip |
| field_duration | integer | 1 | unit=minutes; default=0 |
| field_free_preview | boolean | 1 | default=FALSE — allow anonymous preview |
| field_drip_days | integer | 1 | default=0 — days after enrollment before unlocking |
| field_quiz | entity_reference→lms_quiz | 1 | — |
| field_comments | comment | 1 | comment_type=lesson_comment; default=OPEN |
| weight | integer | 1 | default=0; sort order within chapter |
| status | boolean | 1 | default=TRUE |
| created / changed | timestamps | 1 | — |

**Form modes:** default, add, edit (declared in annotation); `admin_edit` registered externally by `lms_course.module` via `hook_entity_type_alter`.

**`LessonForm` — Conditional field visibility (#states):**

| Field | Visible when field_type = |
|-------|--------------------------|
| field_video_url, field_video_file | video OR mixed |
| field_quiz | quiz OR mixed |
| field_h5p_embed | h5p OR mixed |
| field_scorm_package | scorm OR mixed |
| field_pptx_attachment | pptx OR mixed |
| field_zoom_meeting_id, field_zoom_schedule | zoom |

`save()` redirects to the entity's **edit-form** (not collection) after save.

---

## Hook Implementations

| Hook | Behavior |
|------|----------|
| `hook_entity_access()` | Grants view access to `lms_lesson` for any user with `access content` permission |
| `hook_entity_insert()` | Calls `_lms_lesson_extract_scorm()` on any entity insert |
| `hook_entity_update()` | Calls `_lms_lesson_extract_scorm()` on any entity update |

### SCORM Auto-Extraction Logic (`_lms_lesson_extract_scorm`)

Triggers when: entity is `lms_lesson` AND `field_type = 'scorm'` AND `field_scorm_package` has a zip file.

Steps:
1. Determine real filesystem path of the zip file.
2. Clear `public://scorm_extracted/{lesson_id}/` (full directory wipe on update).
3. Extract zip using PHP `ZipArchive`.
4. Parse `imsmanifest.xml` to auto-detect launch file (`href` attribute).
5. If no `index.html` found at root, create an HTML redirect to the detected entry point.

SCORM content is served directly from `public://scorm_extracted/{id}/index.html` — bypasses Drupal page cache entirely.

---

## Access Control

`LessonAccessControlHandler` reuses lms_course permissions:

| Operation | Required Permission |
|-----------|---------------------|
| view | `view lms_course` (also overridden in hook_entity_access to allow `access content`) |
| update | `edit lms_course` |
| delete | `edit lms_course` |
| create | `create lms_course` |

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_base` | Internal | Base utilities |
| `lms_chapter` | Internal | Parent entity in hierarchy; lesson belongs to a chapter via `field_lessons` on chapter |
| `drupal:datetime` | Core | `field_zoom_schedule` (timestamp field) |
| `drupal:file` | Core | `field_video_file`, `field_scorm_package`, `field_pptx_attachment`, `field_attachments` |
| `drupal:comment` | Core | `field_comments` (comment field; comment_type=lesson_comment) |
| `lms_course` | Internal (runtime) | Admin permission borrowed; workspace route and admin_edit form mode registered there |

---

## Known Issues / Debt

- **Synchronous SCORM extraction (bug-risk):** `_lms_lesson_extract_scorm()` runs inline in `hook_entity_insert/update`. Large zip files will exhaust the PHP request timeout. Should be moved to a Drupal Queue Worker. Tag: **RISK**.
- **No manifest validation:** If `imsmanifest.xml` is malformed or absent, the function silently fails without logging an error or blocking the save.
- **Direct `file_put_contents` call:** The auto-generated `index.html` redirect is written with `file_put_contents` bypassing Drupal's File API and stream wrappers. Not portable to S3/remote file systems.
- **No back-reference field in baseFieldDefinitions:** `lms_course.module::hook_entity_access()` traverses `field_chapter → field_course` on `lms_lesson` to check membership access. However, `field_chapter` is not declared in `Lesson::baseFieldDefinitions()` — it may be set via a config-installed field or elsewhere. This is «unverified» and represents a potential fragility.

---

## Cross-Links

- Parent module (workspace routes + hooks): [lms_course](./lms_course.md)
- Parent entity module: [lms_chapter](./lms_chapter.md)
- Learning view (renders lesson content): `lms_progress` — `UserPortalController::lessonView` at `/learn/{lms_course}/{lms_lesson}`
- Zoom lesson type: [lms_zoom module](./lms_zoom.md)
- Entity detail: [lms_lesson entity](../C04-entities/01-content-entities.md)
