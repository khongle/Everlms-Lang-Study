# D03 — Story Spec: LMS Lesson

**Story ID:** lms_lesson
**Story type:** module
**Title:** Lesson entity — 9-type content leaf with SCORM auto-extraction and conditional form UI
**Primary source:** `../../C-Design/C05-modules/lms_lesson.md`
**Owning module(s):** lms_lesson
**Service(s):** _(none)_
**Epic:** E-02 (Content Layer)
**Contributes to Feature(s):** F-001 (Course Management — lesson content), F-005 (Content/Media — multi-type delivery)
**Design source(s):** `../../C-Design/C05-modules/lms_lesson.md`
**Round:** N/A
**Driver:** SA directive — content delivery leaf entity
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** pre-2026-04-15 (pre-framework)
**Tasks:** see `../D04-tasks/` filtered by `Story ID: lms_lesson`

---

## Session Strategy

**Estimated session count:** 1–2 (medium — 20 base fields, 9 lesson types, SCORM extraction hook, conditional form states)
**Splitting strategy:**
- S1: `Lesson` entity + `LessonAccessControlHandler` + basic `LessonForm` + permissions hook
- S2: `LessonForm` conditional field visibility (`#states`), SCORM extraction hooks (`hook_entity_insert/update + _lms_lesson_extract_scorm`)

---

## Scope (from primary source)

Entity module defining the `lms_lesson` ContentEntity — the leaf tier of the Course → Chapter → Lesson hierarchy, supporting 9 content types:

1. **`lms_lesson` entity** — ContentEntityBase (no EntityOwnerTrait); 20 base fields; translatable; `field_type` list discriminator with 9 values: `video`, `text`, `quiz`, `h5p`, `scorm`, `pdf`, `pptx`, `zoom`, `mixed`.
2. **`LessonForm`** — ContentEntityForm with Form API `#states` for conditional field visibility based on `field_type`. Shows only relevant fields per type (e.g., `field_video_url` + `field_video_file` for video/mixed, `field_quiz` for quiz/mixed, etc.). `save()` redirects to the entity's `edit-form` (not collection).
3. **`LessonAccessControlHandler`** — delegates to `lms_course` permissions; view access also granted via `hook_entity_access` to any user with `access content`.
4. **SCORM auto-extraction** — `hook_entity_insert` + `hook_entity_update` call `_lms_lesson_extract_scorm()`: extracts zip to `public://scorm_extracted/{lesson_id}/`, parses `imsmanifest.xml` to detect launch file, creates `index.html` redirect if none found.
5. **admin_edit form mode** — registered externally by `lms_course.module::hook_entity_type_alter`.
6. **Workspace admin-edit route** — `/workspace/lessons/{lms_lesson}/admin-edit` declared in `lms_course.routing.yml`.

No routing.yml, no services.yml, no permissions.yml, no config directories.

---

## Source Documents

| Source | Why loaded |
|--------|-----------|
| `../../C-Design/C05-modules/lms_lesson.md` | Owning module design (as-built) |
| `../../C-Design/C05-modules/lms_chapter.md` | Parent entity (field_lessons on lms_chapter) |
| `../../C-Design/C05-modules/lms_course.md` | Host of workspace route and hook_entity_type_alter |
| `../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md` | Lesson content delivery AC |
| `../../B-Requirements/B05-acceptance-criteria/AC-05-content-media.md` | Multi-type media delivery AC |

---

## Requirements

| Requirement | Source | Type |
|-------------|--------|------|
| 9 lesson types selectable via field_type | AC-05-content-media.md | Acceptance |
| Video lessons store URL or uploaded file | AC-05-content-media.md | Acceptance |
| Quiz lessons reference a lms_quiz entity | AC-01-course-management.md | Acceptance |
| SCORM lessons auto-extract zip on save | AC-05-content-media.md | Acceptance |
| field_free_preview allows lesson preview without enrollment | AC-01-course-management.md | Acceptance |
| field_drip_days controls unlock timing post-enrollment | AC-01-course-management.md | Acceptance |
| field_comments enables lesson discussion (comment_type=lesson_comment) | AC-08-communication.md | Acceptance |

---

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_lesson/lms_lesson.info.yml` | Create | deps: drupal:datetime, drupal:file, drupal:comment, lms_base, lms_chapter | U-007 | pre-2026-04-15 |
| `web/modules/custom/lms_lesson/lms_lesson.module` | Create | hook_entity_access, hook_entity_insert, hook_entity_update, _lms_lesson_extract_scorm() | U-007 | pre-2026-04-15 |
| `web/modules/custom/lms_lesson/src/Entity/Lesson.php` | Create | lms_lesson ContentEntity; 20 base fields; 9 field_type values | U-007 | pre-2026-04-15 |
| `web/modules/custom/lms_lesson/src/Form/LessonForm.php` | Create | ContentEntityForm with #states conditional field visibility | U-007 | pre-2026-04-15 |
| `web/modules/custom/lms_lesson/src/Access/LessonAccessControlHandler.php` | Create | Delegates to lms_course permissions; view: access content | U-007 | pre-2026-04-15 |

---

## Dependencies

| Dependency | Type | Source |
|------------|------|--------|
| `lms_base` | Intra-Epic | Base utilities; declared in info.yml |
| `lms_chapter` | Cross-Story | Parent entity; lessons referenced via field_lessons on lms_chapter |
| `drupal:datetime` | External (Drupal core) | field_zoom_schedule (timestamp field) |
| `drupal:file` | External (Drupal core) | field_video_file, field_scorm_package, field_pptx_attachment, field_attachments |
| `drupal:comment` | External (Drupal core) | field_comments (comment_type=lesson_comment) |
| `lms_course` | Cross-Story (runtime) | Admin permission borrowed; workspace route and admin_edit form mode registered there |
| `lms_quiz` | Cross-Epic (runtime) | field_quiz entity_reference on lms_lesson |

---

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|------------|-----------|---------|
| #states conditional field visibility | LessonForm — 7 conditional groups | Verified in facts base |
| PHP ZipArchive for SCORM extraction | _lms_lesson_extract_scorm() | Confirmed in module facts |
| save() → edit-form redirect | LessonForm::save() | Confirmed (not collection — intentional for iterative editing) |

---

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission |
|--------------------|----------------|-----------------|-----------|-------------------|
| Lesson view in `/learn/{course}/{lesson}` (lms_progress) | Lesson content (video/text/H5P/SCORM/PDF/etc.) | lms_lesson entity (field_content, field_video_url, field_h5p_embed, etc.) | `/workspace/lessons/{id}/admin-edit` | administer lms_course |
| SCORM player | Interactive SCORM package | Extracted at `public://scorm_extracted/{id}/index.html` | Upload via field_scorm_package | administer lms_course |

**Hardcoded content exceptions:** None. All content is entity-field driven.

---

## Feature Flags Introduced or Read

None.

---

## What Was Built (from facts base)

All code is **as-built**. Status confirmed by `modules-content.md`:

- `Lesson.php` — 20 base fields verified; 9 field_type values confirmed; translatable.
- `LessonForm.php` — conditional field visibility via `#states` verified for all 7 condition groups; save() → edit-form redirect confirmed.
- `LessonAccessControlHandler.php` — permission delegation + `access content` view override confirmed.
- SCORM extraction — PHP ZipArchive, imsmanifest.xml parsing, index.html redirect creation all confirmed.
- `hook_entity_access` grants view to `access content` holders — intentional (lessons visible without workspace permission).

**Known issues / risks:**
- **RISK — Synchronous SCORM extraction (timeout risk):** `_lms_lesson_extract_scorm()` runs inline in `hook_entity_insert/update`. Large zip files may exhaust PHP request timeout. Should be moved to a Drupal Queue Worker. See [`../../C-Design/C05-modules/lms_lesson.md`](../../C-Design/C05-modules/lms_lesson.md) §Known Issues.
- **No manifest validation:** Malformed/absent `imsmanifest.xml` causes silent failure (no error log, no save block).
- **Direct `file_put_contents` call:** `index.html` redirect created outside Drupal's File API — not portable to remote file systems (S3 etc.).
- **`field_chapter` not in baseFieldDefinitions:** `lms_course.module::hook_entity_access()` traverses `field_chapter → field_course` on lms_lesson, but `field_chapter` is not declared in `Lesson::baseFieldDefinitions()`. Status: «unverified» — may be a config-installed field or a latent bug.

---

## Tasks (draft unit references)

| Task / Draft ID | Description | Status |
|----------------|-------------|--------|
| U-007 | Lesson entity (9 types) + handlers (entity, form with #states, access handler, SCORM hook) | DONE |

---

## Acceptance Link

See [`../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md`](../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md) and [`../../B-Requirements/B05-acceptance-criteria/AC-05-content-media.md`](../../B-Requirements/B05-acceptance-criteria/AC-05-content-media.md).

---

## DONE Evidence

| Check | Result |
|-------|--------|
| lms_lesson entity type registered | PASS — entity class verified |
| 9 lesson types selectable | PASS — field_type values confirmed |
| Video lessons store URL or file | PASS — field_video_url + field_video_file verified |
| Quiz lessons reference lms_quiz via field_quiz | PASS — entity_reference field verified |
| SCORM auto-extract on save | PASS — _lms_lesson_extract_scorm() verified |
| LessonForm #states conditional field visibility | PASS — 7 condition groups verified |
| field_free_preview and field_drip_days present | PASS — base fields verified |
| field_comments for lesson discussion | PASS — comment field (lesson_comment type) verified |
| Access handler delegates to lms_course permissions | PASS — confirmed |

---

## Gate Results (per Story, see A02 §3)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Entity schema installs; SCORM extraction functional | 1/3 | pre-2026-04-15 |
| G2 Lint | PASS | No violations | 1/3 | pre-2026-04-15 |
| G3 Tests | PASS | Lesson create/edit for each type functional; SCORM extract verified | 1/3 | pre-2026-04-15 |
| G4 Adversarial | PASS | Synchronous SCORM RISK noted; field_chapter gap noted | 1/3 | pre-2026-04-15 |

---

## Notes

- The learning view that renders lesson content (`/learn/{lms_course}/{lms_lesson}`) is owned by `lms_progress` module (`UserPortalController::lessonView`), not by lms_lesson itself.
- Zoom lesson type integration depends on `lms_zoom` module; field_zoom_meeting_id and field_zoom_schedule are stored on lms_lesson but Zoom signature/JWT generation is in lms_zoom.
- SCORM content is served directly from `public://scorm_extracted/{id}/index.html` — Drupal page cache is bypassed entirely for SCORM sessions.
