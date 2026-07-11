---
id: WF-001-003
workflow: WF-001
title: Missing SCORM package renders a nested 404 iframe in lesson player
severity: high
category: functionality-ui
status: done
roles: student
source: visual QA
found: 2026-06-30
---

# WF-001-003 - Missing SCORM package renders a nested 404 iframe in lesson player

## Problem
SCORM lessons in course `88` render an iframe that points to a missing extracted package URL. Learners see a nested "Page not found" screen inside the lesson player instead of usable content or a clear unavailable-content state.

## Expected
If the extracted SCORM package exists, render the iframe. If it is missing, show a clear placeholder telling the learner that the interactive package is not available yet, while leaving lesson text/overview accessible.

## Evidence
- `001-enrollment-and-learning/004-student-lesson-before-complete.png`
- `001-enrollment-and-learning/006-student-next-lesson-progress-updated.png`
- Template branch hard-codes `file_url('public://scorm_extracted/' ~ lesson.id() ~ '/index.html')` instead of checking `scorm_url`.

## Fix Plan
- Use the controller-provided `scorm_url`.
- Render a friendly SCORM placeholder when `scorm_url` is empty.
- Retest lesson player screenshots.

## Verification
- Passed visual retest in `001-enrollment-and-learning/004-student-lesson-before-complete.png`.
- Missing SCORM package now renders an "Interactive package unavailable" placeholder instead of a nested 404 iframe.
- Progress still works: lesson `1175` was marked completed and the learner advanced to lesson `1176`.
