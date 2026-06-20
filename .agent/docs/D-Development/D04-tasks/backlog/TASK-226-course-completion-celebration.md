# Task: Course Completion Celebration

**ID:** TASK-226
**Unit:** U-043 (progress)
**Group:** G-ENGAGEMENT
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
When a user completes a course (i.e., `CourseCompletedEvent` fires), display a celebration experience: a full-screen confetti animation, a congratulations modal showing the certificate preview, social share buttons (including LinkedIn auto-post), and a "What next?" section with 2–3 recommended follow-up courses.

## Context
- `lms_progress` module dispatches `CourseCompletedEvent`; completion detection already implemented.
- Certificate entity exists (G-CONTENT done).
- Recommended courses can be sourced from TASK-177 (recommendations engine) if available; otherwise fall back to same-category popular courses.

## Acceptance Criteria
- [ ] On `CourseCompletedEvent`, the next page load (or AJAX response) triggers the celebration modal
- [ ] Confetti animation runs for 4s on modal open; respects `prefers-reduced-motion` (TASK-219) — no animation if reduced motion active
- [ ] Modal shows: course title, "Congratulations [name]!" headline, certificate thumbnail preview (link to `/certificates/{id}`), share buttons, "What's next?" course cards (2–3)
- [ ] Share buttons: Copy link, Twitter/X, Facebook, LinkedIn. LinkedIn share uses pre-populated share URL with course name and certificate link
- [ ] "What's next?" cards use TASK-177 recommendations if available; fallback: same category, sort by popularity, exclude completed courses
- [ ] Modal is keyboard accessible: Esc to close, Tab cycles through interactive elements
- [ ] Completion flag stored in session so modal does not re-trigger on page refresh
- [ ] Celebration works for both standard and cohort-based courses

## Technical Notes
- Modules: `lms_progress` (event subscriber sets session flag), `lms_theme` (modal + confetti JS/SCSS)
- Event subscriber: `CourseCompletionCelebrationSubscriber` listens to `CourseCompletedEvent`; sets `$_SESSION['lms_celebrate_course_{cid}'] = true`
- Front-end: `lms_theme/js/course-celebration.js` checks `drupalSettings.lmsCelebration.courseId`; opens modal via custom event
- Confetti: use `canvas-confetti` (MIT) bundled as local vendor asset
- Modal SCSS: `lms_theme/scss/components/_celebration-modal.scss`
- Certificate thumbnail: rendered via `CertificateViewBuilder` compact view mode; passed to modal template via JSON:API or Drupal Ajax callback

## Dependencies
- TASK-219 (reduced motion — confetti must be disabled when active)
- TASK-177 (recommendations engine — "What's next?" cards; task can ship without it using fallback)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: event subscriber; FE: modal render, confetti skip in reduced-motion)
- [ ] No new debt introduced
