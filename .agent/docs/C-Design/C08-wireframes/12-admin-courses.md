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
