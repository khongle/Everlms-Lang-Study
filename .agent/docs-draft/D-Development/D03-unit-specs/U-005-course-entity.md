# D03 — Unit Spec: Course Entity + Handlers

**Unit ID:** U-005
**Group:** G-CONTENT
**Title:** Course entity + handlers
**Status:** DONE
**Priority:** P0

---

## Scope

Implements the `lms_course` custom content entity with all base fields (title, description, thumbnail, price, duration, level, category, etc.), access control handler, list builder, and entity form. Provides the core data model that all content, commerce, and progress modules reference.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_course/lms_course.info.yml` | Module declaration |
| `web/modules/custom/lms_course/lms_course.install` | Schema install |
| `web/modules/custom/lms_course/lms_course.module` | Hook implementations |
| `web/modules/custom/lms_course/lms_course.permissions.yml` | CRUD permissions |
| `web/modules/custom/lms_course/lms_course.routing.yml` | Entity routes |
| `web/modules/custom/lms_course/lms_course.services.yml` | Service definitions |
| `web/modules/custom/lms_course/src/Entity/Course.php` | Entity class |
| `web/modules/custom/lms_course/src/Access/CourseAccessControlHandler.php` | Access handler |
| `web/modules/custom/lms_course/src/CourseListBuilder.php` | Admin list builder |
| `web/modules/custom/lms_course/src/Form/CourseForm.php` | Entity CRUD form |
| `web/modules/custom/lms_course/src/Breadcrumb/LmsCourseBreadcrumbBuilder.php` | Breadcrumb builder |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_course` (`Course extends ContentEntityBase implements EntityOwnerInterface`)
- Key fields: `title`, `field_description`, `field_thumbnail`, `field_price`, `field_duration`, `field_level` (ref: `course_level`), `field_category` (ref: `course_category`), `field_chapters` (entity_reference to `lms_chapter`), `status`, `uid`

**Permissions:** view, create, edit own/any, delete own/any course.

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Entity schema installs; CRUD works |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Create/edit/delete cycle verified |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_course` entity type registered in Drupal
- [x] CRUD operations functional via admin form
- [x] Access handler enforces view/edit/delete permissions
- [x] Entity owner (instructor) tracked via `EntityOwnerInterface`
- [x] List builder renders course table in admin UI

---

## Known Debt / Issues

None identified.

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-004 | Taxonomies (level, category) required |
| U-006 | Chapter entity referenced by Course |
| U-008 | CRUD controller uses this entity |
| U-021 | UserCourse progress references Course |

---

*End of U-005-course-entity.md*
