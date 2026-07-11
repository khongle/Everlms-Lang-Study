---
page: Course editor
drupal_concept: entity form
mode: as-built
owner_module: lms_course
route_group: manage
route_name: lms_course.workspace_course_edit
form_mode: manage_edit
prototype_type: form
adequacy: PASS
---

# C11 Page - Course editor

## Purpose
Tạo/chỉnh sửa một khoá học và curriculum của nó tại `/workspace/courses/{lms_course}/edit`: metadata, chapters, lessons, pricing. Đây là bề mặt soạn thảo (authoring) chính (F-002/WF-006).

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/workspace/courses/{lms_course}/edit` (create `/workspace/courses/create`) |
| Route name | `lms_course.workspace_course_edit` |
| Drupal page primitive | entity form (course) + inline chapter/lesson management |
| Owner module | lms_course |
| Access | instructor (own) / content_editor / manager |
| Cache | uncacheable (form) |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| course_curriculum (editable) | Curriculum builder | content | add/reorder chapters/lessons |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Fields | name, descriptions, thumbnail/banner, category/level/language/tags, price/type, marketing sections, instructor |
| Curriculum | manage `field_chapters` → chapters → `field_lessons` |
| AI assist | link to AI builder (P044) |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Course fields | `lms_course` | manage courses | `lms_course:{id}` | save → detail updates |
| Chapters/lessons | `lms_chapter`/`lms_lesson` | manage courses | course tag | add lesson → appears in player |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| Field labels | form chrome (i18n) | interface translation |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | form + curriculum builder |
| Empty | new course → blank form |
| Access denied | not owner/staff → 403 |
| Validation error | required title/price → inline errors |
| Loading | autosave/AJAX |

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Validation error — Title bỏ trống | "Title field is required." | Drupal core required-field message (`web/core/lib/Drupal/Core/Form/FormValidator.php` — `@name field is required.`) áp cho field `name` (`setRequired(TRUE)` trong the module/src/Entity/Course.php`, hiển thị label "Title" trên form) |
| Success — tạo course mới | "Course %label has been created." | the module/src/Form/CourseForm.php` `save()` (`SAVED_NEW` branch) |
| Success — cập nhật course | "Course %label has been updated." | the module/src/Form/CourseForm.php` `save()` (branch khác `SAVED_NEW`) |
| Status badge — course chưa publish | "Draft" | field `status` (Published, `setDefaultValue(FALSE)`) trong the module/src/Entity/Course.php`; badge `.status--warning` trong prototype |
| CTA phụ — lưu không publish | "Save draft" | `.pagehead__actions` trong prototype (`instructor/P033-course-editor.html`); chưa có `actions()` override wire vào field `status` trong `CourseForm.php` hiện tại |
| CTA chính — publish course | "Publish" | như trên — cần bổ sung action wiring vào field `status` khi hiện thực hoá |
| Empty curriculum (course mới, chưa có chapter/lesson) | "Add section" | block course curriculum (owner module the module), nút thêm section đầu tiên trong `.curri` |
| Cảnh báo — unpublish course | "Unpublishing removes the course from the catalog immediately; learners keep access." | hint text trong panel "Danger zone" (tab Settings) của prototype |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (≤575.98px) | `.container` co về 100% chiều rộng; thanh `.tabs` (Details/Curriculum/Pricing/Settings) cuộn ngang (`overflow-x:auto`) thay vì xuống dòng; `.editor` (form chính + `.editor__aside`) đã gập 1 cột từ mốc ≤991.98px nên tiếp tục 1 cột; `.formgrid--2` (Category/Level; Price/Compare-at price) gập 1 cột (mốc ≤639.98px); toolbar curriculum ("4 sections · 28 lessons..." + nút "Add section") xuống dòng nhờ `flex-wrap`. |
| tablet (768–991px) | `.container` giới hạn theo `--container-sm`/`--container-md`; `.editor` vẫn 1 cột (form chính xếp trên, `.editor__aside` — Thumbnail/Status/Visibility ở tab Details, Membership ở Pricing, Danger zone ở Settings — xếp dưới) vì mốc gập của `.editor` là ≤991.98px; `.formgrid--2` vẫn giữ 2 cột vì mốc gập riêng của nó (≤639.98px) thấp hơn dải tablet. |
| desktop (992–1199px) | `.editor` trở lại hai cột (`grid-template-columns: minmax(0,1fr) 340px`, gap `clamp(1.4rem,3vw,2.4rem)`) — form chính bên trái, `.editor__aside` sticky (`position:sticky; top:90px`) bên phải; `.container` giới hạn theo `--container-lg`. |
| wide (≥1200px) | Bố cục hai cột như desktop; `.container` mở rộng theo `--container-xl` (đến ≤1399.98px) rồi full width mặc định từ 1400px trở lên; gap giữa hai cột nới dần theo `clamp()` tới tối đa 2.4rem. |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Authoring form + curriculum | done | editorial-usability anchor |
