# Task: Learning Paths Feature

**ID:** TASK-044
**Unit:** — (no unit spec yet, needs U-085+)
**Group:** G-ADDITIONAL (extension)
**Priority:** P2
**Estimate:** 20h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement learning paths feature: cho phép tạo chuỗi courses theo thứ tự, student follow path, track progress across path. Sitemap có 3 routes (`/learning-paths`, `/learning-paths/{slug}`, `/learning-paths/my`) nhưng chưa có entity hoặc unit spec.

## Context
- Sitemap C07 liệt kê 3 URLs cho learning paths
- B01 BR-014 "Learning paths (multi-course sequences)" — priority: Could
- Feature 13 (Suggested Modern) AP-01 — Learning Paths
- **Hiện tại chưa có entity, module, hay unit spec nào**

## Acceptance Criteria
- [ ] LearningPath entity (title, description, slug, courses list ordered)
- [ ] Learning path listing page (public)
- [ ] Learning path detail page (course sequence, progress)
- [ ] My learning paths page (student enrolled paths)
- [ ] Path progress tracking (derived từ course progress)
- [ ] Enroll in learning path
- [ ] Admin CRUD for learning paths

## Technical Notes
- Cần tạo module mới: `lms_learning_path`
- Entity: LearningPath (fields: title, description, slug, image, courses reference ordered)
- Routes: `/learning-paths`, `/learning-paths/{slug}`, `/learning-paths/my`
- **Cần tạo unit specs mới**: U-085 (entity), U-086 (controller + UI)

## Dependencies
- G-CONTENT (courses done)
- G-PROGRESS (progress tracking done)

## Definition of Done
- [ ] Unit specs created
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] All 3 sitemap routes functional
