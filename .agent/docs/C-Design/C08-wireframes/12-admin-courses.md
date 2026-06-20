# Wireframe: Admin — Course Management

## Course List Page (`/manage/courses`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ MANAGEMENT NAVBAR (mega-menu)                                        │
├─────────────────────────────────────────────────────────────────────┤
│ "Course Management"                            [+ Create Course]     │
│                                                                      │
│ Filters: [Category ▼] [Level ▼] [Status ▼]   Search: [_________ 🔍]│
├─────────────────────────────────────────────────────────────────────┤
│ DATA TABLE (full width)                                              │
│ ☐ │ # │ Course Title        │Category │Level  │Students│Revenue│Sta │
│ ─────────────────────────────────────────────────────────────────── │
│ ☐ │ 1 │ Web Dev Bootcamp    │Web Dev  │Beginner│1,234  │$12K   │✅  │
│ ☐ │ 2 │ React Masterclass   │Frontend │Intermed│567    │$8K    │✅  │
│ ☐ │ 3 │ CSS Animation       │Design   │Advanced│234    │$3K    │📝  │
│ ☐ │ 4 │ Node.js Backend     │Backend  │Intermed│123    │$2K    │✅  │
│ ─────────────────────────────────────────────────────────────────── │
│ ☑ Select All  │ Bulk: [Delete] [Unpublish] [Export]                  │
│                                                                      │
│ Showing 1-20 of 248  │  [< 1 2 3 ... 13 >]                         │
└─────────────────────────────────────────────────────────────────────┘
```

## Create Course Page (`/manage/courses/create`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ MANAGEMENT NAVBAR                                                    │
├─────────────────────────────────────────────────────────────────────┤
│ "Create New Course"                                                  │
│                                                                      │
│ Step: [① Basic Info ●]──[② Curriculum ○]──[③ Pricing ○]──[④ Settings]│
├──────────────────────────────────────────┬──────────────────────────┤
│ FORM (col-8)                             │ AI HELPER (col-4)        │
│                                          │                          │
│ Course Title *                           │ ✨ AI Course Builder     │
│ [_________________________________]     │                          │
│                                          │ Enter topic & level to   │
│ Short Description                        │ auto-generate an outline │
│ [_________________________________]     │                          │
│                                          │ [Generate with AI]       │
│ Description *                            │                          │
│ ┌─────────────────────────────────────┐ │                          │
│ │ [B] [I] [U] [H2] [📎] [🔗] [</>]  │ │                          │
│ │                                     │ │                          │
│ │  Rich text editor                   │ │                          │
│ └─────────────────────────────────────┘ │                          │
│                                          │                          │
│ Category *         Level                 │                          │
│ [Select ▼]         [Beginner ▼]         │                          │
│                                          │                          │
│ Language            Tags                 │                          │
│ [English ▼]        [tag1 ×] [tag2 ×] [+]│                          │
│                                          │                          │
│ Thumbnail    Banner Image                │                          │
│ [📤 Upload]  [📤 Upload]               │                          │
│                                          │                          │
│ [Save Draft]              [Next Step →]  │                          │
└──────────────────────────────────────────┴──────────────────────────┘
```

---

## Implementation status (as of 2026-06-20)

**as-built.**

- Course list: `/workspace/courses` → `AdminController::courseList` → `lms-admin-courses.html.twig`. AJAX table refresh via `lms-admin-courses-table.html.twig` partial.
- Course create wizard: `/workspace/courses/create` → `AdminController::courseCreateWizard` → `lms-admin-course-create.html.twig`.
- Course edit: `/workspace/courses/{lms_course}/edit` → `AdminController::courseEditForm` → `lms-admin-entity-form.html.twig` wrapper (entity form).
- Chapter/lesson edit within workspace: `/workspace/chapters/{id}/admin-edit`, `/workspace/lessons/{id}/admin-edit`.
- Workspace content (blog, FAQ): `/workspace/content/{content_type}` → `WorkspaceContentController`.
- AI course builder panel: `/manage/courses/ai-builder`.
- Categories are Drupal taxonomy terms (`course_category` vocabulary) — managed via standard Drupal admin at `/admin/structure/taxonomy`.

Related: [../C04-entities/01-content-entities.md](../C04-entities/01-content-entities.md) · [../C05-modules/lms_course.md](../C05-modules/lms_course.md)
