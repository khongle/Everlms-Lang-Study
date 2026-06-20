---
scope: content-entities
mode: as-built
entities: [lms_course, lms_chapter, lms_lesson, lms_article, lms_faq]
last_updated: 2026-06-20
---

# C04 — Content Entities

Covers the five content-layer entities: **lms_course**, **lms_chapter**, **lms_lesson**, **lms_article**, **lms_faq**. These form the primary learning content hierarchy (Course → Chapter → Lesson) plus the admin blog entity and the FAQ system.

**Mode:** as-built — specs mirror current code and DB schema as of 2026-06-20.

Cross-references:
- Owner modules: [`lms_course`](../C05-modules/lms_course.md), [`lms_chapter`](../C05-modules/lms_chapter.md), [`lms_lesson`](../C05-modules/lms_lesson.md), [`lms_blog`](../C05-modules/lms_blog.md), [`lms_faq`](../C05-modules/lms_faq.md)
- Entity-module map: [`../C03-entity-module-map.md`](../C03-entity-module-map.md)
- Assessment entities: [`02-assessment-entities.md`](02-assessment-entities.md)

---

## Entity 1 — lms_course (Course)

---
entity: lms_course
mode: as-built
owner_module: lms_course
content_kind: editorial
editor_owned: true
revisionable: false
translatable: true
moderation_workflow: null
---

**Storage:** `lms_course` (base) + `lms_course_field_data` (translatable data) + junction tables  
**Description:** The top-level learning unit. Instructors author courses; learners enroll and track progress through chapters and lessons.  
**Owner module:** [`lms_course`](../C05-modules/lms_course.md)  
**Mode:** as-built  
**Content kind:** editorial  
**Editor owned:** true  
**Management UI:** `/workspace/courses` (list), `/workspace/courses/{lms_course}/edit` (admin), `/manage/courses/{lms_course}/edit` (manager)  
**Create / Edit / Delete forms:** `lms_course.admin_course_create`, `lms_course.admin.course.edit`, entity delete form  
**Display modes:** default, teaser, card (via `lms_course_card` theme hook)  
**Form modes:** `default`, `admin_edit`, `manager_edit` (RBAC-differentiated; added via `lms_course_update_10002`)  
**Revision / moderation:** Not revisionable (no `_revision` tables); no moderation workflow  
**Translation:** Content translation; translatable fields rendered from `lms_course_field_data`; `langcode` key  
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer (auto-increment) | 1 | Yes | auto | No | — | `lms_course.id` | PK, unsigned |
| 2 | `uuid` | UUID | uuid | 1 | Yes | auto | No | — | `lms_course.uuid` | Unique, varchar(128) |
| 3 | `langcode` | Language | language | 1 | Yes | site default | No | — | `lms_course.langcode` | Translation key |
| 4 | `uid` | Owner | entity_reference → user | 1 | No | current user | No | users | `lms_course_field_data.uid` | Instructor/creator; via EntityOwnerTrait |
| 5 | `name` | Course Title | string | 1 | Yes | — | Yes | — | `lms_course_field_data.name` | max_length=255; entity label |
| 6 | `status` | Published | boolean | 1 | No | TRUE | No | — | `lms_course_field_data.status` | 1=published, 0=draft |
| 7 | `field_description` | Description | text_long | 1 | No | — | Yes | — | `lms_course_field_data.field_description__value/format` | Full HTML body |
| 8 | `field_short_description` | Short Description | text_long | 1 | No | — | Yes | — | `lms_course_field_data.field_short_description__value/format` | Listing card text |
| 9 | `field_what_learn` | What You Will Learn | text_long | 1 | No | — | Yes | — | `lms_course_field_data.field_what_learn__value/format` | Bullet list of outcomes |
| 10 | `field_prerequisites` | Prerequisites | text_long | 1 | No | — | Yes | — | `lms_course_field_data.field_prerequisites__value/format` | Free-text prerequisite description |
| 11 | `field_who_for` | Who This Course Is For | text_long | 1 | No | — | Yes | — | `lms_course_field_data.field_who_for__value/format` | Target audience description |
| 12 | `field_thumbnail` | Thumbnail | image → file | 1 | No | — | No | file_managed | `lms_course_field_data.field_thumbnail__target_id/alt/title/width/height` | file_directory: `courses/thumbnails` |
| 13 | `field_banner` | Banner | image → file | 1 | No | — | No | file_managed | `lms_course_field_data.field_banner__target_id/alt/title/width/height` | file_directory: `courses/banners` |
| 14 | `field_chapters` | Chapters | entity_reference → lms_chapter | unlimited | No | — | No | lms_chapter | `lms_course__field_chapters` junction table | Ordered curriculum; delta = display order |
| 15 | `field_category` | Category | entity_reference → taxonomy_term | 1 | No | — | No | taxonomy_term (course_category) | `lms_course_field_data.field_category` | Single primary category |
| 16 | `field_tags` | Tags | entity_reference → taxonomy_term | unlimited | No | — | No | taxonomy_term (course_tags) | `lms_course__field_tags` junction table | Currently empty in DB |
| 17 | `field_level` | Level | entity_reference → taxonomy_term | 1 | No | — | No | taxonomy_term (course_level) | `lms_course_field_data.field_level` | e.g. Beginner, Intermediate, Advanced |
| 18 | `field_language` | Language | entity_reference → taxonomy_term | 1 | No | — | No | taxonomy_term (course_language) | `lms_course_field_data.field_language` | Teaching language |
| 19 | `field_instructor` | Instructors | entity_reference → user | unlimited | No | — | No | users | `lms_course__field_instructor` junction table | Co-instructors; separate from uid (owner) |
| 20 | `field_price` | Price | decimal (10,2) | 1 | No | 0 | No | — | `lms_course_field_data.field_price` | 0 = free |
| 21 | `field_price_type` | Price Type | list_string | 1 | No | free | No | free, paid, membership | `lms_course_field_data.field_price_type` | Controls enrollment gate |
| 22 | `field_duration` | Duration | integer | 1 | No | 0 | No | — | `lms_course_field_data.field_duration` | Total minutes; auto-calculated |
| 23 | `field_rating` | Rating | decimal (3,1) | 1 | No | 5.0 | No | — | `lms_course_field_data.field_rating` | Denormalized avg from lms_rating; added via `lms_course_update_10001` |
| 24 | `field_bestseller` | Bestseller | boolean | 1 | No | FALSE | No | — | `lms_course_field_data.field_bestseller` | Badge flag for course cards |
| 25 | `field_featured` | Featured | boolean | 1 | No | FALSE | No | — | `lms_course_field_data.field_featured` | Homepage featured section |
| 26 | `field_certificate_template` | Certificate Template | entity_reference → lms_certificate_template | 1 | No | — | No | lms_certificate_template | `lms_course_field_data.field_certificate_template` | Certificate issued on quiz pass |
| 27 | `created` | Created | created (timestamp) | 1 | No | auto | No | — | `lms_course_field_data.created` | Unix timestamp; auto-set on insert |
| 28 | `changed` | Changed | changed (timestamp) | 1 | No | auto | No | — | `lms_course_field_data.changed` | Unix timestamp; auto-bumped on save |

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | Row identity |
| UNIQUE | `uuid` | B-Tree | External-facing stable ID |
| `lms_course_field_data` PK | `(id, langcode)` | B-Tree | Translation lookup |
| `field_category` | `lms_course_field_data.field_category` | B-Tree | Category filter (catalog page) |
| `field_price_type` | `lms_course_field_data.field_price_type` | B-Tree | Free/paid filter |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `user` (owner) | M:1 | `lms_course_field_data.uid` → `users.uid` | No cascade; uid nullable |
| `user` (co-instructor) | M:M | `lms_course__field_instructor.field_instructor_target_id` | No cascade |
| `lms_chapter` | 1:M (ordered) | `lms_course__field_chapters.field_chapters_target_id` | No DB cascade; application must delete chapters separately |
| `taxonomy_term` (category) | M:1 | `lms_course_field_data.field_category` | No cascade |
| `taxonomy_term` (level/language/tags) | M:1 / M:M | `lms_course_field_data.field_level/language`, `lms_course__field_tags` | No cascade |
| `lms_certificate_template` | M:1 | `lms_course_field_data.field_certificate_template` | No cascade |
| `lms_user_course` | 1:M | `lms_user_course.field_course` → `lms_course.id` | No DB cascade |
| `lms_rating` | 1:M | `lms_rating.field_course` → `lms_course.id` | No DB cascade |
| `lms_faq` | 1:M | `lms_faq_field_data.field_course` → `lms_course.id` | No DB cascade |
| `lms_order_item` | 1:M | `lms_order_item.field_course` → `lms_course.id` | No DB cascade |

### Editorial usability

| Surface | Requirement |
|---|---|
| Management UI | `/workspace/courses` (list), `/workspace/courses/{id}/edit` (admin), entity links at `/manage/courses/*` |
| Editor role / permission | `administer lms_course` (full); `edit lms_course` (any); `edit own lms_course` (own) |
| Form display | 3 form modes: `admin_edit` (all fields), `manager_edit` (subset; installed by `lms_course_update_10002`), `default` (entity API default). `LessonForm`-style `#states` not present on CourseForm — all fields always visible. |
| View displays / view modes | `default` (detail page), card (via `lms_course_card` theme hook in `CoursePublicController::buildCourseCards()`). No YAML `entity_view_display` for lms_course recorded in config sync. |
| Revision / moderation | Not revisionable. No workflow. |
| Translation | `name`, `field_description`, `field_short_description`, `field_what_learn`, `field_prerequisites`, `field_who_for` are translatable. Edit via standard Drupal content translation at `/manage/courses/{id}/translations`. |
| Preview | No dedicated preview route. `WorkspaceContentController::generatePremiumPreview()` provides a generic field-dump preview for workspace content. |
| Cache invalidation | `lms_course:{id}` cache tag invalidated on save. Catalog/homepage pages cache against entity list tags. |
| Smoke verification | Edit `name` → save → visit `/courses/{id}` → verify updated title visible. |

### Business rules

- `field_price_type = membership` gates access via `lms_course.module` `hook_entity_access()`: enrolled users must hold an active `lms_user_membership`.
- `field_duration` is denormalized; recalculated via application logic when lesson durations change (no DB trigger).
- `field_rating` is a denormalized decimal; updated when `lms_rating` entities are saved (no DB trigger; application must sync).
- Free enrollment gate: `CourseEnrollmentController::enrollFree()` checks `field_price_type == free` before creating `lms_user_course`.
- `lms_user_course` (enrollment) is independent of `lms_order` (payment); a student may be manually enrolled without an order record.

### DB scale (as of 2026-06-20)

| Table | Approx rows |
|---|---|
| `lms_course` | ~127 |
| `lms_course_field_data` | ~127 (+ translations) |
| `lms_course__field_chapters` | proportional to chapters assigned |
| `lms_course__field_instructor` | sparse |
| `lms_course__field_tags` | 0 (empty) |

---

## Entity 2 — lms_chapter (Chapter)

---
entity: lms_chapter
mode: as-built
owner_module: lms_chapter
content_kind: editorial
editor_owned: true
revisionable: false
translatable: true
moderation_workflow: null
---

**Storage:** `lms_chapter` (base) + `lms_chapter_field_data` (translatable data) + `lms_chapter__field_lessons` junction  
**Description:** An ordered section within a Course. Groups related Lessons. No independent public URL; rendered as part of course curriculum.  
**Owner module:** [`lms_chapter`](../C05-modules/lms_chapter.md)  
**Mode:** as-built  
**Content kind:** editorial  
**Editor owned:** true (managed by content editors / instructors)  
**Management UI:** `/manage/chapters` (collection), `/workspace/chapters/{id}/admin-edit` (workspace admin edit)  
**Create / Edit / Delete forms:** `entity.lms_chapter.add_form`, `lms_course.admin.chapter.edit` (admin_edit form mode), entity delete  
**Display modes:** No YAML entity_view_display defined; rendered inline in course curriculum templates  
**Revision / moderation:** Not revisionable; no moderation workflow  
**Translation:** Content translation; `name` and `field_description` translatable  
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer (auto-increment) | 1 | Yes | auto | No | — | `lms_chapter.id` | PK, unsigned |
| 2 | `uuid` | UUID | uuid | 1 | Yes | auto | No | — | `lms_chapter.uuid` | Unique |
| 3 | `langcode` | Language | language | 1 | Yes | site default | No | — | `lms_chapter.langcode` | Translation key |
| 4 | `name` | Chapter Name | string | 1 | Yes | — | Yes | — | `lms_chapter_field_data.name` | max_length=255; entity label |
| 5 | `status` | Published | boolean | 1 | No | TRUE | No | — | `lms_chapter_field_data.status` | 1=published |
| 6 | `field_description` | Description | text_long | 1 | No | — | Yes | — | `lms_chapter_field_data.field_description__value/format` | Section intro text |
| 7 | `field_lessons` | Lessons | entity_reference → lms_lesson | unlimited | No | — | No | lms_lesson | `lms_chapter__field_lessons` junction table | Ordered by delta; reflects curriculum order |
| 8 | `weight` | Weight | integer | 1 | No | 0 | No | — | `lms_chapter_field_data.weight` | Sort order within course |
| 9 | `created` | Created | created (timestamp) | 1 | No | auto | No | — | `lms_chapter_field_data.created` | Unix timestamp |
| 10 | `changed` | Changed | changed (timestamp) | 1 | No | auto | No | — | `lms_chapter_field_data.changed` | Unix timestamp |

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | Row identity |
| UNIQUE | `uuid` | B-Tree | Stable external ID |
| PK (field_data) | `(id, langcode)` | B-Tree | Translation lookup |
| `weight` | `lms_chapter_field_data.weight` | B-Tree | Sort order queries |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `lms_course` | M:1 (parent) | `lms_course__field_chapters.field_chapters_target_id` | No DB cascade; chapter is referenced by course |
| `lms_lesson` | 1:M (ordered) | `lms_chapter__field_lessons.field_lessons_target_id` | No DB cascade |

**Note:** There is no `field_course` back-reference on `lms_chapter`. The parent-child relationship is stored forward-only on `lms_course.field_chapters`. Code traverses course→chapter by loading `lms_course__field_chapters` rows.

### Editorial usability

| Surface | Requirement |
|---|---|
| Management UI | `/manage/chapters` (collection via AdminHtmlRouteProvider), `/workspace/chapters/{id}/admin-edit` |
| Editor role / permission | `administer lms_course` (inherited; no per-chapter permission) |
| Form display | `default` and `admin_edit` form modes (admin_edit registered by `lms_course.module` `hook_entity_type_alter`). No custom form class — uses `ChapterForm` extending `ContentEntityForm`. |
| View displays / view modes | No standalone view display; rendered as curriculum inline in `lms-course-detail.html.twig`. |
| Revision / moderation | Not revisionable. |
| Translation | `name` and `field_description` translatable. |
| Preview | No dedicated preview route. |
| Cache invalidation | `lms_chapter:{id}` tag. |
| Smoke verification | Edit chapter name → save → visit course detail page → verify chapter title updated. |

### Business rules

- Chapter access permissions are borrowed from `lms_course` permission set (`view lms_course`, `edit lms_course`, `create lms_course`).
- `weight` controls display order within a course's curriculum; lower weight = earlier in curriculum.
- No `status` field affects lesson visibility directly; the course's own enrollment/access gate governs lesson access.

### DB scale (as of 2026-06-20)

| Table | Approx rows |
|---|---|
| `lms_chapter` | ~478 |
| `lms_chapter_field_data` | ~478 (+ translations) |
| `lms_chapter__field_lessons` | proportional to lesson count |

---

## Entity 3 — lms_lesson (Lesson)

---
entity: lms_lesson
mode: as-built
owner_module: lms_lesson
content_kind: editorial
editor_owned: true
revisionable: false
translatable: true
moderation_workflow: null
---

**Storage:** `lms_lesson` (base) + `lms_lesson_field_data` (translatable data) + junction tables for multi-value file fields  
**Description:** The atomic learning unit within a Chapter. Supports 9 delivery types (video, text, quiz, h5p, scorm, pdf, pptx, zoom, mixed). SCORM packages are auto-extracted on save.  
**Owner module:** [`lms_lesson`](../C05-modules/lms_lesson.md)  
**Mode:** as-built  
**Content kind:** editorial  
**Editor owned:** true  
**Management UI:** `/manage/lessons` (collection), `/workspace/lessons/{id}/admin-edit` (workspace)  
**Create / Edit / Delete forms:** `entity.lms_lesson.add_form`, `lms_course.admin.lesson.edit`, entity delete  
**Display modes:** `default`, `full` (YAML entity_view_display exists for both; see entities-fields.md §6)  
**Revision / moderation:** Not revisionable; no moderation workflow  
**Translation:** Content translation; `name` and `field_content` translatable  
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer (auto-increment) | 1 | Yes | auto | No | — | `lms_lesson.id` | PK, unsigned |
| 2 | `uuid` | UUID | uuid | 1 | Yes | auto | No | — | `lms_lesson.uuid` | Unique |
| 3 | `langcode` | Language | language | 1 | Yes | site default | No | — | `lms_lesson.langcode` | Translation key |
| 4 | `name` | Lesson Name | string | 1 | Yes | — | Yes | — | `lms_lesson_field_data.name` | max_length=255; entity label |
| 5 | `status` | Published | boolean | 1 | No | TRUE | No | — | `lms_lesson_field_data.status` | 1=published |
| 6 | `field_content` | Content | text_long | 1 | No | — | Yes | — | `lms_lesson_field_data.field_content__value/format` | Main rich-text body; translatable |
| 7 | `field_type` | Lesson Type | list_string | 1 | Yes | text | No | video, text, quiz, h5p, scorm, pdf, pptx, zoom, mixed | `lms_lesson_field_data.field_type` | Controls which fields are shown in form via `#states` |
| 8 | `field_h5p_embed` | H5P Embed Code | string_long | 1 | No | — | No | — | `lms_lesson_field_data.field_h5p_embed` | Raw H5P embed HTML |
| 9 | `field_video_url` | Video URL | string | 1 | No | — | No | — | `lms_lesson_field_data.field_video_url` | YouTube / Vimeo URL; max_length=512 |
| 10 | `field_video_file` | Video File | file | 1 | No | — | No | file_managed | `lms_lesson_field_data.field_video_file__target_id/display/description` | Extensions: mp4 webm ogg; `lessons/videos` |
| 11 | `field_zoom_meeting_id` | Zoom Meeting ID | string | 1 | No | — | No | — | `lms_lesson_field_data.field_zoom_meeting_id` | max_length=64 |
| 12 | `field_zoom_schedule` | Zoom Schedule | timestamp | 1 | No | — | No | — | `lms_lesson_field_data.field_zoom_schedule` | Unix timestamp |
| 13 | `field_scorm_package` | SCORM Package | file | 1 | No | — | No | file_managed | `lms_lesson_field_data.field_scorm_package__target_id/display/description` | zip only; auto-extracted on save |
| 14 | `field_pptx_attachment` | PPTX Attachment | file | 1 | No | — | No | file_managed | `lms_lesson_field_data.field_pptx_attachment__target_id/display/description` | ppt/pptx; `lessons/pptx` |
| 15 | `field_attachments` | Attachments | file | unlimited | No | — | No | file_managed | `lms_lesson__field_attachments` junction table | pdf doc docx ppt pptx xls xlsx zip; currently empty in DB |
| 16 | `field_duration` | Duration | integer | 1 | No | 0 | No | — | `lms_lesson_field_data.field_duration` | Minutes |
| 17 | `field_free_preview` | Free Preview | boolean | 1 | No | FALSE | No | — | `lms_lesson_field_data.field_free_preview` | Allow viewing without enrollment |
| 18 | `field_drip_days` | Drip Days | integer | 1 | No | 0 | No | — | `lms_lesson_field_data.field_drip_days` | Days after enrollment before unlock; 0=immediate |
| 19 | `field_quiz` | Quiz | entity_reference → lms_quiz | 1 | No | — | No | lms_quiz | `lms_lesson_field_data.field_quiz` | Attached quiz (for type=quiz or mixed) |
| 20 | `field_comments` | Comments | comment | 1 | No | OPEN (2) | No | comment type: lesson_comment | `lms_lesson_field_data.field_comments` | 0=no, 1=closed, 2=open |
| 21 | `field_h5p_upload` | H5P Upload | h5p | 1 | No | — | No | h5p_content | `lms_lesson__field_h5p_upload` junction table | Configurable field (YAML-defined); currently empty in DB |
| 22 | `weight` | Weight | integer | 1 | No | 0 | No | — | `lms_lesson_field_data.weight` | Sort order within chapter |
| 23 | `created` | Created | created (timestamp) | 1 | No | auto | No | — | `lms_lesson_field_data.created` | Unix timestamp |
| 24 | `changed` | Changed | changed (timestamp) | 1 | No | auto | No | — | `lms_lesson_field_data.changed` | Unix timestamp |

**Note on configurable vs base fields:** All fields above are base fields (PHP-defined) except `field_h5p_upload`, which is the only YAML-defined configurable field on any custom LMS entity type. It has its own field.storage and field.field YAML config records.

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | Row identity |
| UNIQUE | `uuid` | B-Tree | Stable external ID |
| PK (field_data) | `(id, langcode)` | B-Tree | Translation lookup |
| `field_type` | `lms_lesson_field_data.field_type` | B-Tree | Filter by lesson type |
| `field_quiz` | `lms_lesson_field_data.field_quiz` | B-Tree | Lookup lesson by quiz |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `lms_chapter` | M:1 (parent) | `lms_chapter__field_lessons.field_lessons_target_id` | No DB cascade; forward-reference on chapter |
| `lms_quiz` | M:1 | `lms_lesson_field_data.field_quiz` → `lms_quiz.id` | No DB cascade |
| `file` | M:1 (video, scorm, pptx) | `field_*__target_id` → `file_managed.fid` | No DB cascade |
| `file` (attachments) | M:M | `lms_lesson__field_attachments` junction | No cascade |
| `lms_user_lesson` | 1:M | `lms_user_lesson.field_lesson` → `lms_lesson.id` | No DB cascade |
| `lms_note` | 1:M | `lms_note.field_lesson` → `lms_lesson.id` | No DB cascade |
| `comment` (lesson_comment) | 1:M | Drupal comment system, `field_comments` flag enables/disables | — |

### State machine

`field_comments` has 3 states:

```
0 (no comments/disabled) → 1 (closed) → 2 (open)
```

No formal workflow; toggled by form field on edit.

### Editorial usability

| Surface | Requirement |
|---|---|
| Management UI | `/manage/lessons` (collection), `/manage/lessons/add` (create), `/manage/lessons/{id}/edit` (edit), `/workspace/lessons/{id}/admin-edit` (workspace) |
| Editor role / permission | `administer lms_course` or `edit lms_course` |
| Form display | `LessonForm` uses Drupal Form API `#states` to show/hide fields based on `field_type` selection: video fields visible when type=video or mixed; quiz field when type=quiz or mixed; H5P when type=h5p or mixed; SCORM when type=scorm; PPTX when type=pptx; Zoom fields when type=zoom. |
| View displays / view modes | YAML `entity_view_display` config for `lms_lesson.lms_lesson.default` and `lms_lesson.lms_lesson.full` present in config/sync. |
| Revision / moderation | Not revisionable. |
| Translation | `name` and `field_content` translatable. |
| Preview | No dedicated preview. |
| Cache invalidation | `lms_lesson:{id}` tag. |
| Smoke verification | Add video URL → set type=video → save → verify field_video_url stored; verify other type-specific fields hidden. |

### Business rules

- `field_type` is the routing key for all lesson-type-specific rendering; the template and controller use it to switch display components.
- SCORM: on insert/update, `lms_lesson.module::_lms_lesson_extract_scorm()` unzips to `public://scorm_extracted/{lesson_id}/`, reads `imsmanifest.xml`, creates `index.html` redirect if no launch file detected.
- `field_drip_days > 0`: lesson is locked until N days after `lms_user_course.field_enrolled_at`. Lock enforcement is application-level.
- `field_free_preview = TRUE`: lesson is accessible without enrollment check.
- Lesson access: `LessonAccessControlHandler` grants view to `access content` holders; `lms_course.module::hook_entity_access()` enforces membership requirement for membership-type courses.

### DB scale (as of 2026-06-20)

| Table | Approx rows |
|---|---|
| `lms_lesson` | ~1,772 |
| `lms_lesson_field_data` | ~1,772 (+ translations) |
| `lms_lesson__field_attachments` | 0 (empty) |
| `lms_lesson__field_h5p_upload` | 0 (empty) |

---

## Entity 4 — lms_article (Article)

---
entity: lms_article
mode: as-built
owner_module: lms_blog
content_kind: editorial
editor_owned: true
revisionable: false
translatable: true
moderation_workflow: null
---

**Storage:** `lms_article` (base) + `lms_article_field_data` (translatable data)  
**Description:** Admin-side blog/news article entity. Used exclusively for the `/manage/blog` management CRUD. **The public-facing blog frontend reads `node:blog` (Drupal core nodes), not this entity.** This is an intentional architectural split.  
**Owner module:** [`lms_blog`](../C05-modules/lms_blog.md)  
**Mode:** as-built  
**Content kind:** editorial  
**Editor owned:** true  
**Management UI:** `/manage/blog` (list), `/manage/blog/add` (create), `/manage/blog/{id}/edit` (edit)  
**Create / Edit / Delete forms:** `entity.lms_article.add_form`, `entity.lms_article.edit_form`, `entity.lms_article.delete_form`  
**Display modes:** No YAML entity_view_display defined for lms_article  
**Revision / moderation:** Not revisionable; no moderation workflow  
**Translation:** Content translation; `title` and `content` translatable  
**Replaces / Replaced by:** N/A (dual-track with `node:blog`; see business rules)

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer (auto-increment) | 1 | Yes | auto | No | — | `lms_article.id` | PK, unsigned |
| 2 | `uuid` | UUID | uuid | 1 | Yes | auto | No | — | `lms_article.uuid` | Unique |
| 3 | `langcode` | Language | language | 1 | Yes | site default | No | — | `lms_article.langcode` | Translation key |
| 4 | `title` | Title | string | 1 | Yes | — | Yes | — | `lms_article_field_data.title` | max_length=255; entity label key |
| 5 | `status` | Published | boolean | 1 | No | TRUE | No | — | `lms_article_field_data.status` | 1=published |
| 6 | `content` | Content | text_long | 1 | No | — | Yes | — | `lms_article_field_data.content__value/format` | Full HTML body; translatable |
| 7 | `thumbnail` | Thumbnail | file | 1 | No | — | No | file_managed | `lms_article_field_data.thumbnail__target_id/display/description` | file (not image) field; extensions: png jpg jpeg webp; `blog/thumbnails`. DB uses `__display/__description` pattern (managed_file), NOT `__alt/__width/__height` (image). |
| 8 | `seo_title` | SEO Title | string | 1 | No | — | No | — | `lms_article_field_data.seo_title` | max_length=60 |
| 9 | `seo_description` | SEO Description | string | 1 | No | — | No | — | `lms_article_field_data.seo_description` | max_length=160 |
| 10 | `created` | Created | created (timestamp) | 1 | No | auto | No | — | `lms_article_field_data.created` | Unix timestamp |
| 11 | `changed` | Changed | changed (timestamp) | 1 | No | auto | No | — | `lms_article_field_data.changed` | Unix timestamp |

**Notable omissions vs draft:** No `uid` (author) field in `baseFieldDefinitions()`. No category/tag fields on lms_article. No `field_thumb` (that name is on `node:blog`). The `thumbnail` field here is a managed_file (not image) type.

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | Row identity |
| UNIQUE | `uuid` | B-Tree | Stable external ID |
| PK (field_data) | `(id, langcode)` | B-Tree | Translation lookup |
| `status` | `lms_article_field_data.status` | B-Tree | Published filter |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `file` (thumbnail) | M:1 | `lms_article_field_data.thumbnail__target_id` → `file_managed.fid` | No DB cascade |

### Editorial usability

| Surface | Requirement |
|---|---|
| Management UI | `/manage/blog` (list), `/manage/blog/add`, `/manage/blog/{id}/edit`, `/manage/blog/{id}/delete` |
| Editor role / permission | `administer lms_blog`, `create lms_article`, `edit own/any lms_article`, `delete own/any lms_article`, `publish lms_article` |
| Form display | `LmsArticleForm` includes optional AI Blog Writer widget (topic, keywords, style, AI generate button) when `lms_ai` module is enabled and user holds `administer lms_blog`. AI generates title + HTML + SEO fields via AJAX. |
| View displays / view modes | No YAML view display; `LmsArticleListBuilder` shows list columns: ID, Title, Status, Created. |
| Revision / moderation | Not revisionable. |
| Translation | `title` and `content` translatable. |
| Preview | No dedicated preview route. |
| Cache invalidation | `lms_article:{id}` tag. |
| Smoke verification | Create article → verify appears in `/manage/blog` list with status. Note: does NOT appear at `/news` (which reads `node:blog`). |

### Business rules

- **Dual-track architecture:** Public blog frontend (`/news`, `/news/{node}`) reads `node:blog` entities. `lms_article` is admin-management only. The two tracks are not linked.
- `LmsBlogController::index()` queries `node` storage for `type=blog` — no relation to `lms_article`.
- AI writer requires `lms_ai` module enabled with OpenAI provider configured (relies on `lms_ai.ai_provider_manager` service).
- No `uid`/author field: article authorship is not tracked at the entity level.

### DB scale (as of 2026-06-20)

| Table | Approx rows |
|---|---|
| `lms_article` | ~9 |
| `lms_article_field_data` | ~9 (+ translations) |

---

## Entity 5 — lms_faq (FAQ)

---
entity: lms_faq
mode: as-built
owner_module: lms_faq
content_kind: editorial
editor_owned: true
revisionable: false
translatable: true
moderation_workflow: null
---

**Storage:** `lms_faq` (base) + `lms_faq_field_data` (translatable data)  
**Description:** FAQ item with three scopes: `global` (homepage accordion), `course` (course detail page, filtered by `field_course`), `lesson` (reserved scope, not yet actively consumed). Managed via workspace FAQ routes in `lms_course`.  
**Owner module:** [`lms_faq`](../C05-modules/lms_faq.md)  
**Mode:** as-built  
**Content kind:** editorial  
**Editor owned:** true  
**Management UI:** `/workspace/content/faqs` (via `WorkspaceContentController`), `/manage/faq` (entity collection via AdminHtmlRouteProvider)  
**Create / Edit / Delete forms:** `lms_course.workspace_faq.add`, `lms_course.workspace_faq.edit` (workspace); `entity.lms_faq.add_form`, `entity.lms_faq.edit_form` (entity API)  
**Display modes:** No YAML entity_view_display defined for lms_faq  
**Revision / moderation:** Not revisionable; no moderation workflow. No `status` field — all saved FAQs are effectively always published.  
**Translation:** Content translation; `question` and `field_answer` translatable  
**Replaces / Replaced by:** N/A

### Fields

| # | Machine name | Label | Type | Cardinality | Required | Default | Translatable | Reference / Allowed values | Storage | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `id` | ID | integer (auto-increment) | 1 | Yes | auto | No | — | `lms_faq.id` | PK, unsigned |
| 2 | `uuid` | UUID | uuid | 1 | Yes | auto | No | — | `lms_faq.uuid` | Unique |
| 3 | `langcode` | Language | language | 1 | Yes | site default | No | — | `lms_faq.langcode` | Translation key |
| 4 | `question` | Question | string | 1 | Yes | — | Yes | — | `lms_faq_field_data.question` | max_length=512; entity label key; translatable |
| 5 | `field_answer` | Answer | text_long | 1 | Yes | — | Yes | — | `lms_faq_field_data.field_answer__value/format` | Rich-text answer; translatable |
| 6 | `field_scope` | Scope | list_string | 1 | No | global | No | global, course, lesson | `lms_faq_field_data.field_scope` | Determines where FAQ appears |
| 7 | `field_course` | Course | entity_reference → lms_course | 1 | No | — | No | lms_course | `lms_faq_field_data.field_course` | Required when scope=course; ignored for scope=global |
| 8 | `field_category` | Category | entity_reference → taxonomy_term | 1 | No | — | No | taxonomy_term (faq_category) | `lms_faq_field_data.field_category` | Grouping within FAQ lists |
| 9 | `weight` | Weight | integer | 1 | No | 0 | No | — | `lms_faq_field_data.weight` | Sort order within scope |

**Note:** No `status` (published/draft) field — FAQs have no publish/unpublish capability. All saved FAQs are active.

### Indexes

| Name | Columns | Type | Purpose |
|---|---|---|---|
| PRIMARY | `id` | B-Tree | Row identity |
| UNIQUE | `uuid` | B-Tree | Stable external ID |
| PK (field_data) | `(id, langcode)` | B-Tree | Translation lookup |
| `field_scope` | `lms_faq_field_data.field_scope` | B-Tree | Scope filter (global vs course) |
| `field_course` | `lms_faq_field_data.field_course` | B-Tree | Course-scoped FAQ lookup |
| `weight` | `lms_faq_field_data.weight` | B-Tree | Sort order |

### Relationships

| Related entity | Type | FK / via | Cascade |
|---|---|---|---|
| `lms_course` | M:1 | `lms_faq_field_data.field_course` → `lms_course.id` | No DB cascade |
| `taxonomy_term` (faq_category) | M:1 | `lms_faq_field_data.field_category` → `taxonomy_term_data.tid` | No cascade |

### Editorial usability

| Surface | Requirement |
|---|---|
| Management UI | `/workspace/content/faqs` (workspace list), `/workspace/content/faqs/add` (create), `/workspace/content/faqs/{id}/edit` (edit), `/workspace/content/faqs/{id}/view` (preview) |
| Editor role / permission | `administer lms_course` (borrowed; lms_faq defines no own permissions) |
| Form display | No custom form class — uses core `ContentEntityForm`. No custom access handler — uses core `EntityAccessControlHandler`. |
| View displays / view modes | No YAML view display. `WorkspaceContentController::faqView()` renders via `generatePremiumPreview()`. |
| Revision / moderation | Not revisionable. |
| Translation | `question` and `field_answer` translatable. |
| Preview | `lms_course.workspace_faq.view` → `WorkspaceContentController::faqView()`. |
| Cache invalidation | `lms_faq:{id}` tag. |
| Smoke verification | Create FAQ with scope=course + field_course set → visit course detail page → verify FAQ appears in course accordion. |

### Business rules

- `field_scope = global`: FAQ loaded by `CoursePublicController::homepage()` for the homepage accordion.
- `field_scope = course`: FAQ loaded by `CoursePublicController::detail()` filtered by `field_course == current course id`.
- `field_scope = lesson`: scope defined in entity but no active controller consumes it yet.
- No published/unpublished state: editors must delete (not unpublish) unwanted FAQs.
- Sort order: `weight` ASC within scope/course grouping.

### DB scale (as of 2026-06-20)

| Table | Approx rows |
|---|---|
| `lms_faq` | ~223 |
| `lms_faq_field_data` | ~223 (+ translations) |
